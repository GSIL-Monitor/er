<?php
namespace Platform\Manager;
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Manager/Manager.class.php');

//保存需要执行递交的sid
$trade_handle_merchant = array();

class TradeManager extends Manager {
    public static function Trade_main() {
        return enumAllMerchant('trade_merchant');
    }

    public static function tradeBeforeComplete($tube, $complete) {
        if ($tube != 'Trade')
            return;

        deleteJob();

        global $trade_handle_merchant;
        foreach ($trade_handle_merchant as $sid => $v) {
            pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
        }
    }

    public static function listTradeShops($sid) {
        global $g_use_jst_sync;
        deleteJob();
        $db = getUserDb($sid);

        if (!$db) {
            logx("listTradeShops getUserDb failed!!", $sid . "/Trade");
            return TASK_OK;
        }

        $is_updating = $db->query_result_single("SELECT NOT IS_FREE_LOCK(CONCAT('sys_update_', DATABASE()))");
        if ($is_updating) {
            releaseDb($db);
            logx("merchant is updating", $sid . "/Trade");
            return TASK_OK;
        }

        $autoDownload = getSysCfg($db, 'order_auto_download', 0);
        if (!$autoDownload) {
            releaseDb($db);
            logx("not auto download!", $sid . "/Trade");
            return TASK_OK;
        }

        /*上次有未递交成功的*/
        $hasTradeToDeliver = getSysCfg($db, 'order_should_deliver', 0);
        if ($hasTradeToDeliver) {
            logx("Redeliver trades!", $sid . "/Trade");
            pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
            setSysCfg($db, 'order_should_deliver', 0);
        }

        $result = $db->query("select * " .
            " from cfg_shop " .
            " where auth_state=1 and is_disabled=0 and platform_id in (3,6,7,8,13,14,20,22,24,25,27,28,29,31,36,50)");
        if (!$result) {
            releaseDb($db);
            logx("query shop failed!", $sid . "/Trade");
            return TASK_OK;
        }

        while ($row = $db->fetch_array($result)) {
            //过滤掉不抓单的店铺
            if (isset($row['is_undownload_trade']) && $row['is_undownload_trade'] == 1)
                continue;
            if (!checkAppKey($row))
                continue;
            $row->sid = $sid;

            pushTask('trade_shop', $row, 0, 1024, 600, 300);
            if ($row->platform_id == 4) //拍拍
            {
                $row->order_type = 8;
                pushTask('trade_shop', $row, 0, 1024, 600, 300);
            } else if ($row->platform_id == 2) //淘宝分销
            {
                $row->order_type = 1;
                pushTask('trade_shop', $row, 0, 1024, 600, 300);
            } 
            }


        $db->free_result($result);
        releaseDb($db);
        return TASK_OK;

    }

    //下载店铺订单列表
    public static function downloadTradeList($shop) {
        global $g_use_jst_sync;

        deleteJob();

        $sid    = $shop->sid;
        $shopId = $shop->shop_id;

        $db = getUserDb($sid);
        if (!$db) {
            logx("downloadTradeList getUserDb failed!!", $sid . "/Trade");
            return TASK_OK;
        }

        $now = time();

        $interval = (int)getSysCfg($db, 'order_sync_interval', 10);
        $delay = (int)getSysCfg($db, 'order_delay_interval', 2);
        //是否延时下载
        //淘宝JST可以减少延时
        if ($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick) && ($shop->platform_id == 1 || $shop->platform_id == 2)) {
            $delayMinite = $delay>2?$delay:2;
            $interval    = 5;
        } else {
            //其它平台10分钟
            $delayMinite = $delay>10?$delay:10;

            //夜间延时加长
            $da = getdate($now);
            if ($da['hours'] >= 2 && $da['hours'] <= 7)
                $delayMinite = $delay>30?$delay:30;
        }

        $endTime = $now - $delayMinite * 60;

        $postfix = '';
        if (isset($shop->order_type)) $postfix = "_{$shop->order_type}";

        $startTime = (int)getSysCfg($db, "order_last_synctime_{$shopId}{$postfix}", 0);

        //检查有没到时间间隔
        if ($startTime > 0) {
            if ($now - $startTime > 2592000) //最长下载30days
                $startTime = $now - 2592000;
            else
                $startTime -= 1;

            if ($interval < 5) $interval = 5;
            if ($interval > 30) $interval = 30;

            $lastTime = $startTime + $delayMinite * 60;

            if ($lastTime + $interval * 60 > $now) {
                releaseDb($db);
                return TASK_OK;
            }
            $authTime = strtotime($shop->auth_time);
            if ($startTime < $authTime)
                $firstTime = true;
            else
                $firstTime = false;
        } else {
            //最后下载时间没设置的话，下载最近三天
            $startTime = $now - 259200;
            $firstTime = true;
        }

        //无需下载
        if ($startTime >= $endTime) {
            releaseDb($db);
            logx("Need not scan trade!! {$shopId}", $sid . "/Trade");
            return TASK_OK;
        }


        $result = self::startDownloadTradeList($db, $sid, $shop, $startTime, $endTime, $firstTime, true);
        releaseDb($db);

        return $result;
    }

    //下载一个时间段
    /*function tradeDownloadSpan($task)
    {
        $sid = $task->sid;
        $startTime = $task->StartTime;
        $endTime = $task->EndTime;
        $shopType = $task->ShopType;
        $nickName = @$task->NickName;

        logx("准备手动下载 StartTime:" . date('Y-m-d H:i:s',$startTime) . " LastTime: " . date('Y-m-d H:i:s', $endTime), $sid);

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("tradeDownloadSpan getUserDb failed", $sid);
            return TASK_OK;
        }
        $db->execute("update g_api_sync_task set Status=2 where Status=1 and Type=1");

        if($shopType == 1 && !empty($nickName))
        {
            $shopid = $task->shop_id;

            //取得appsecret
            getAppSecret($task, $appkey, $appsecret);

            require_once(ROOT_DIR . '/modules/trade_sync/top.php');
            $result = topDownloadTradeListByNickname(
                    $db,
                    $appkey,
                    $appsecret,
                    $task,
                    $nickName,
                    $total_new,
                    $total_chg,
                    $error_msg);
        }
        else
        {
            $result = startDownloadTradeList($db, $sid, $task, $startTime, $endTime, true, false);
        }
        releaseDb($db);

        return $result;
    }*/
    
    public static function startDownloadTradeList($db, $sid, &$shop, $startTime, $endTime, $firstTime, $saveTime) {
        global $trade_handle_merchant, $g_use_jst_sync;

        $trade_handle_merchant[ $sid ] = 1;

        $shopId = $shop->shop_id;

        //取得appsecret
        getAppSecret($shop, $appkey, $appsecret);
        //获取卖家账号
        $shop->sid = $sid;
        $total_trade_count = 0;
        $new_trade_count = 0;
        $chg_trade_count = 0;
        $error_msg = "";
        $type='auto';
        //开始下载
        switch ($shop->platform_id) {
            case 1: //淘宝天猫
            {
                if ($endTime - $startTime > 3600) {
                    logx("top sid: ".$sid, 'SY');
                }
                if ($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick)) //使用聚石塔
                {
                    require_once(ROOT_DIR . '/Trade/jst.php');
                    $result = jstTopDownloadTradeList($db,
                                                      $firstTime,
                                                      $appkey,
                                                      $appsecret,
                                                      $shop,
                                                      $startTime,
                                                      $endTime,
                                                      $saveTime,
                                                      $total_trade_count,
                                                      $new_trade_count,
                                                      $chg_trade_count,
                                                      $error_msg);
                    if (TASK_OK == $result) {
                        logx("jst_top {$shopId} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                    }
                } else {
                    require_once(ROOT_DIR . '/Trade/top.php');

                    $result = topDownloadTradeList(
                        $db,
                        $appkey,
                        $appsecret,
                        $shop,
                        $startTime,
                        $endTime,
                        $saveTime,
                        'trade_get',
                        $total_count,
                        $error_msg);
                }
                break;
            }
            case 3: //京东
            {
                if (3 == $shop->sub_platform_id) {
                    $shop->key    = $appkey;
                    $shop->secret = $appsecret;
                    require_once(ROOT_DIR . '/Trade/jos_fbp.php');
                    $result = josFbpDownloadTradeList($db, $shop, 0, $startTime,
                                                      $endTime, true, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                    if (TASK_OK == $result) {
                        logx("log_josFbp {$shopId} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                    }
                } else {
                    require_once(ROOT_DIR . '/Trade/jos.php');
                    $result = josDownloadTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, true, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                    if (TASK_OK == $result) {
                        logx("log_jd {$shopId} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                    }
                    /*
                    * 京东接口不稳定 有时会漏单
                    * 作为补充 增加非增量抓单
                    * 6-23点期间 整点前10分钟调用
                    */
                    if (intval(date('H', time())) > 6 && intval(date('H', time())) < 23 && intval(date('i', time())) < 11) {
                        $startTime = $endTime - 3600 * 12;
                        $saveTime  = false;
                        josDownloadTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, $saveTime, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                    }
                }
                break;
            }
            case 5: //亚马逊
            {
                require_once(ROOT_DIR . '/Trade/amazon.php');

                if($endTime - $startTime > 24*3600) $startTime = $endTime-24*3600;
                if($endTime - $startTime > 60*10) $endTime = $startTime+60*10;

                $result = amazonDownloadTradeList(
                    $db,
                    $appkey,
                    $appsecret,
                    $shop,
                    $startTime,
                    $endTime,
                    $saveTime,
                    'trade_get',
                    $error_msg);
                break;
            }
            case 6: //一号店
            {
                require_once(ROOT_DIR . '/Trade/yhd.php');

                if(!empty($shop->expire_time) && strtotime($shop->expire_time) - time() <60)//session refresh
                {
                    logx("yhd ".$shop->shop_id." need refersh token",$shop->sid. "/Trade");
                    refreshYhdToken($db, $shop);

                    $yhd_result = $db->query("select app_key from cfg_shop where shop_id=%d", $shop->shop_id);

                    if(!$yhd_result)
                    {
                        releaseDb($db);
                        logx("yhd query app_key failed!", $sid. "/Trade");
                        return TASK_OK;
                    }

                    while($yhd_row = $db->fetch_array($yhd_result))
                    {
                        $res = json_decode($yhd_row['app_key'],true);
                        $shop->session = $res['session'];
                    }
                }

                $result = yhdDownloadTradeList(
                    $db,
                    $appkey,
                    $appsecret,
                    $shop,
                    $startTime,
                    $endTime,
                    $saveTime,
                    'trade_get',
                    $total_count,
                    $error_msg);
                break;
            }
            case 7: //当当
            {
                require_once(ROOT_DIR . '/Trade/dangdang.php');
                    
                $result = ddDownloadTradeList(
                    $db,
                        $appkey, 
                    $appsecret,
                        $shop, 
                        $startTime, 
                        $endTime, 
                        $saveTime, 
                        'trade_get', 
                    $total_count,
                    $error_msg);
                break;
            }
            case 8:  //国美
            {
                require_once(ROOT_DIR . '/Trade/coo8.php');

                $startTime = $endTime - 3600 * 24 * 3;
                $result =  coo8DownloadTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, $saveTime, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                if (TASK_OK == $result) {
                    logx("log_coo8 {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                }

                return $result;
            }
            case 9: //阿里巴巴
            {
                require_once(ROOT_DIR . '/Trade/alibaba.php');
                /*
                * 阿里巴巴接口不稳定 有时会漏单
                * 作为补充 增加非增量抓单
                * 8-20点期间 每4个小时调用
                */
                $h = intval(date('H',time()));
                if($h == 8 || $h == 12 || $h == 16 || $h == 20)
                {
                    $startTime = $endTime - 3600*24*2;
                    $result = aliDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, 'trade_get', $new_trade_count, $chg_trade_count, $error_msg);
                }
                else
                {
                    $result = aliDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, 'trade_get', $new_trade_count, $chg_trade_count, $error_msg);
                }
                break;
            }
            case 13: //sos
            {
                require_once(ROOT_DIR . '/Trade/sos.php');

                //无增量接口
                //抓3天
                $startTime = $endTime - 3600*24*3;
                $result = sosDownloadTradeList(
                    $db,
                    $appkey,
                    $appsecret,
                    $shop,
                    0,
                    $startTime,
                    $endTime,
                    true,
                    $total_trade_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_sos {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid. "/Trade");
                }

                return $result;
            }
            case 17: //kdt
            {
                require_once(ROOT_DIR . '/Trade/kdt.php');
                $startTime = $endTime - 3600*24*3;
                $result = kdtDownloadTradeList(
                    $db,
                    $appkey,
                    $appsecret,
                    $shop,
                    0,
                    $startTime,
                    $endTime,
                    $saveTime,
                    $total_trade_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg
                );

                if(TASK_OK == $result)
                {
                    logx("log_kdt {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                }

                return $result;
            }
            case 20:
            {
                require_once(ROOT_DIR . '/Trade/mls.php');
                $startTime = $endTime - 3600 * 24 * 3;
                $result    = meilishuoTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, $saveTime, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                if (TASK_OK == $result) {
                    logx("log_mls {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                }

                return $result;

            }
            case 22: //bbw
            {
                require_once(ROOT_DIR . '/Trade/bbw.php');

                $result = bbwDownloadTradeList($db,$appkey,$appsecret, $shop,0,$startTime, $endTime, $saveTime,$total_trade_count,$new_trade_count, $chg_trade_count, $error_msg);
                if(TASK_OK == $result)
                {
                    logx("log_bbw {$shopId} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                }
                break;
            }
            case 24: //折800
            {
                require_once(ROOT_DIR . '/Trade/zhe800.php');
                //异步下载
                $result = zheDownloadTradeList(
                    $db,
                    $appkey,
                    $appsecret,
                    $shop,
                    $startTime,
                    $endTime,
                    $saveTime,
                    'trade_get',
                    $total_count,
                    $error_msg);
                if(TASK_OK == $result)
                {
                    logx("zhe800 {$shopId} new $new_trade_count chg $chg_trade_count", $sid."/Trade");
                }
                break;
            }
            case 25 : // icbc融e购
            {
                require_once(ROOT_DIR . '/Trade/icbc.php');
                $result = icbcDownloadTradeList ( 
                    $db, 
                    $appkey,
                    $appsecret, 
                    $shop, 
                    $startTime, 
                    $endTime, 
                    $saveTime, 
                    'trade_get', 
                    $total_count, 
                    $error_msg );
                if(TASK_OK == $result)
                {
                    logx("icbc {$shopId} ok ", $sid."/Trade");
                }
                break;
            }
            case 27: //楚楚街
            {
                require_once(ROOT_DIR . '/Trade/ccj.php');
                $startTime = $endTime - 3600*24*3;
                $result = ccjDownloadTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, true, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                if(TASK_OK == $result)
                {
                    logx("log_ccj {$shopId} new $new_trade_count chg $chg_trade_count", $sid."/Trade");
                }
                break;
            }
            case 28://微盟
            {
                require_once(ROOT_DIR . '/Trade/weimo.php');
                if(!empty($shop->expire_time) && strtotime($shop->expire_time) <= time()-120)
                {
                    refreshWeimoToken($db, $shop);
                }
                logx('weimo',$sid.'/Trade');
                $result = weimoDownloadTradeList($db, $shop, $startTime, $endTime, true, 'trade_get',$total_trade_count, $error_msg);

                break;
            }
            case 31: //飞牛
            {
                require_once(ROOT_DIR . '/Trade/fn.php');

                if(!empty($shop->expire_time) && strtotime($shop->expire_time) - time() < 5000)
                {
                    $shopid = $shop->shop_id;
                    $result_db = $db->query_result("select refresh_token,auth_time,expire_time from cfg_shop where shop_id=%d", (int)$shopId);
                    if($result_db)
                    {
                        $expire_time = ( !empty($result_db['expire_time']) && strtotime($result_db['expire_time']) > 2592000 )?strtotime($result_db['expire_time']) : strtotime('+1 day',strtotime($result_db['auth_time']));

                        $times_out = $expire_time - time();

                        if($times_out > 60 && $times_out <= 5000)
                        {
                            $shop->key = $appkey;
                            $shop->secret = $appsecret;
                            $shop->refresh_token = $result_db['refresh_token'];
                            referFnToken($shop, $db);
                        }elseif($times_out < 0)
                        {
                            logx("log_fn_token_expire {$shopid} token has expire $times_out", $sid.'/Trade');
                        }
                    }
                }


                $result = fnDownloadTradeList($db,$appkey,$appsecret, $shop,0,$startTime, $endTime, $saveTime,$total_trade_count,$new_trade_count, $chg_trade_count, $error_msg);
                if(TASK_OK == $result)
                {
                    logx("log_fn {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/Trade');
                }
                break;
            }
            case 36://善融商城
            {
                require_once(ROOT_DIR . '/Trade/ccb.php');
                $shop->key = $appkey;
                $shop->secret = $appsecret;

                $result = ccbDownloadTradeList(
                    $db,
                    $shop,
                    $startTime,
                    $endTime,
                    $saveTime,
                    'trade_get',
                    $total_count,
                    $error_msg);
                break;
            }
            case 47: //人人店
            {
                require_once(ROOT_DIR . '/Trade/rrd.php');
                if(!empty($shop->expire_time) && strtotime($shop->expire_time) - time() < 5000)
                {
                    $shopid = $shop->shop_id;
                    $result_db = $db->query_result("select refresh_token,auth_time,expire_time from cfg_shop where shop_id=%d", (int)$shopId);
                    if($result_db)
                    {
                        $expire_time = ( !empty($result_db['expire_time']) && strtotime($result_db['expire_time']) > 604800 )?strtotime($result_db['expire_time']) : strtotime('+1 day',strtotime($result_db['auth_time']));

                        $times_out = $expire_time - time();

                        if($times_out > 60 && $times_out <= 5000)
                        {
                            $shop->key = $appkey;
                            $shop->secret = $appsecret;
                            $shop->refresh_token = $result_db['refresh_token'];
                            referFnToken($shop, $db);
                        }elseif($times_out < 0)
                        {
                            logx("log_fn_token_expire {$shopid} token has expire $times_out", $sid.'/Trade');
                        }
                    }
                }
                $result    = rrdDownloadTradeList($db,$appkey,$appsecret,$shop,$startTime,$endTime,$saveTime,'trade_get',$new_trade_count,$chg_trade_count,$error_msg);
                if (TASK_OK == $result) {
                    logx("log_rrd {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
                }
                break;
            }
            default: {
                $result = TASK_OK;
            }
        }

        return $result;
    }

    public static function tradeTradesDetail($trades) {
        $sid = $trades->sid;
        $db  = getUserDb($sid);
        if (!$db) {
            logx("tradeTrade getUserDb failed!!", $sid . "/Trade");
            return TASK_SUSPEND;
        }

        //取得appsecret
        getAppSecret($trades, $appkey, $appsecret);

        $scan_count = 0;
        switch ($trades->platform_id) {
            case 1: //淘宝
            {
                require_once(ROOT_DIR . '/Trade/top.php');

                $result = downTopTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if (TASK_OK == $result) {
                    logx("log_top {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid . "/Trade");
                }
                break;
            }
            case 3: //京东
            {
                require_once(ROOT_DIR . '/Trade/jos.php');

                $result = downJosTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_jd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 5: //亚马逊
            {
                require_once(ROOT_DIR . '/Trade/amazon.php');

                $result = downAmazonTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_amazon {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }

                break;
            }
            case 6: //一号店
            {
                require_once(ROOT_DIR . '/Trade/yhd.php');

                $result = downYhdTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_yhd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 7: //当当
            {
                require_once(ROOT_DIR . '/Trade/dangdang.php');
                $result = downDdTradesDetail(
                    $db,
                    $appkey, 
                    $appsecret, 
                    $trades, 
                    $scan_count,
                    $new_trade_count, 
                    $chg_trade_count, 
                    $error_msg);
                
                if(TASK_OK == $result)
                {
                    logx("log_dd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 8: //国美
            {
                require_once(ROOT_DIR . '/Trade/coo8.php');

                $result = downcoo8TradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);
                if(TASK_OK == $result)
                {
                    logx("log_coo8 {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid);
                }

                break;
            }
            case 9: //阿里
            {
                require_once(ROOT_DIR . '/Trade/alibaba.php');

                $result = downAliTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_ali {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 17: //kdt
            {
                require_once(ROOT_DIR . '/Trade/kdt.php');

                $result = downKdtTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_kdt {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 20: //美丽说
            {
                require_once(ROOT_DIR . '/Trade/mls.php');

                $result = downmeilishuoTradesDetail($db,$shop,$appkey,$appsecret,$scan_count,$new_trade_count,$chg_trade_count,$error_msg);
                if (TASK_OK == $result) {
                    logx("log_mls {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 22: //贝贝网
            {
                require_once(ROOT_DIR . '/Trade/bbw.php');

                $result = bbwTradesDetail($db,$appkey,$appsecret,$shop,$new_trade_count,$chg_trade_count,$error_msg);
                if (TASK_OK == $result) {
                    logx("log_mls {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 24: //折800
            {
                require_once(ROOT_DIR . '/Trade/zhe800.php');


                $result = downZheTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_zhe800 {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid,'/Trade');
                }
                break;
            }
            case 25: //icbc 融e购
            {
                require_once(ROOT_DIR . '/Trade/icbc.php');
                $result = downIcbcTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_icbc {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 28: //微盟旺店
            {
                require_once(ROOT_DIR . '/Trade/weimo.php');

                $result = downWeimoTradesDetail ( $db, $trades, $scan_count, $new_trade_count, $chg_trade_count, $message );
                if(TASK_OK == $result)
                {
                    logx("log_weimo {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $sid.'/Trade');
                }
                break;
            }
            case 36: //善融商城
            {
                require_once(ROOT_DIR . '/Trade/ccb.php');
                $trades->key = $appkey;
                $trades->secret = $appsecret;
                $result = ccbDownloadTradeDetail(
                    $db,
                    $trades,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_ccb {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            case 47: //人人店
            {
                require_once(ROOT_DIR . '/Trade/rrd.php');
                $result = downRrdTradesDetail($db,$appkey,$appsecret,$shop,$scan_count,$new_trade_count,$chg_trade_count,$error_msg);
                if (TASK_OK == $result) {
                    logx("log_rrd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
                }
                break;
            }
            default: 
            {
                $result = TASK_OK;
            }
        }

        releaseDb($db);
        return $result;
    }

    public static function tradeDeliverTrade($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("tradeDeliverTrade getUserDb failed!!", $sid . "/Trade");
            return TASK_OK;
        }
        $hasTradeToDeliver = getSysCfg($db, 'order_auto_submit', 0);
        if(!$hasTradeToDeliver){
            logx('未开启自动递交',$sid.'/Trade');
            releaseDb($db);
            return TASK_OK;
        }

        deliverMerchantTrades($db, $error_msg, $sid);
        releaseDb($db);

        return TASK_OK;
    }

    //自动调用的时候应该首先调用该方法,注册类内部的方法
    public static function register() {
        registerHandle('trade_merchant', array('\\Platform\\Manager\\TradeManager', 'listTradeShops'));
        registerHandle('trade_shop', array('\\Platform\\Manager\\TradeManager', 'downloadTradeList'));
        registerHandle('trade_get', array('\\Platform\\Manager\\TradeManager', 'tradeTradesDetail'));
        registerHandle('trade_deliver', array('\\Platform\\Manager\\TradeManager', 'tradeDeliverTrade'));
        registerHandle('trade_down_span', array('\\Platform\\Manager\\TradeManager', 'tradeDownloadSpan'));
        registerBeforeExit(array('\\Platform\\Manager\\TradeManager', 'tradeBeforeComplete'));
    }

    /**
     * @param integer $shopId
     * @param array   $message
     * @param array   $conditions
     * 手动下载订单的入口
     */
    public function manualSync($shopId, &$message, $conditions = null) {
        $shop = array();
        $res = parent::sync_auth_check($shopId, $shop, $message);
        if($res['status']==0 && $res['info']!=''){
            E($res["info"]);
            return;
        }
        //获取店铺的appkey和appsecret
        getAppSecret($shop, $key, $secret);
        $shop->key    = $key;
        $shop->secret = $secret;
        //获取卖家账号
        $sid       = get_sid();
        $shop->sid = $sid;
        $db        = getUserDb($sid);
        switch (intval($conditions['radio'])) {
            case 1: {//按照单号
                $this->sync_byTid($db, $shop, $conditions, $message);
                break;
            }
            case 2: {//按照买家名称
                $this->sync_byNickname($db, $shop, $conditions, $message);
                break;
            }
            case 3: {//按照时间段
                $this->sync_byTime($db, $shop, $conditions, $message);
                break;
            }
            default: {
                E("未知条件下载订单");
                return;
            }
        }
    }

    //根据订单tid下载
    private function sync_byTid(&$db, $shop, $conditions, &$message) {
        if (!$db) {
            logx("sync_bytid getUserDb failed!!", get_sid() . "/Trade");
            $message["status"] = 0;
            $message["info"]   = "未知错误，请联系管理员";
            return;
        }
        $shop->tids = array($conditions["trade_id"]);
        $tid = array($conditions['trade_id']);
        $scan_count = 0;
        $total_chg  = 0;
        $total_new  = 0;
        $sid = get_sid();
        try {
            $platform_id = $shop->platform_id;
            switch ($platform_id) {
                case 1: //淘宝
                    require_once(ROOT_DIR . "/Trade/top.php");
                    downTopTradesDetail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 2: //淘宝分销
                {
                    require_once(ROOT_DIR . '/Trade/top_fx.php');
                    top_fenxiao_trades_detail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 3: //京东
                    require_once(ROOT_DIR . "/Trade/jos.php");
                    downJosTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                case 5: //亚马逊
                {
                    require_once(ROOT_DIR . '/Trade/amazon.php');
                    $shop->tids = array(array('tid'=>$tid));
                    downAmazonTradeByTid($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 6: //一号店
                {
                    require_once(ROOT_DIR . '/Trade/yhd.php');
                    downYhdTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                case 7: //当当网
                {
                    require_once(ROOT_DIR . '/Trade/dangdang.php');
                    downDdTradesDetail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 8: //国美
                {
                    require_once(ROOT_DIR . '/Trade/coo8.php');
                    downcoo8TradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                case 9: //阿里巴巴
                {
                    require_once(ROOT_DIR . '/Trade/alibaba.php');
                    downalibabaTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                case 13: //suning
                {
                    require_once(ROOT_DIR . '/Trade/sos.php');

                    downsosTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 14: //唯品会
                {
                    $message["status"] = 0;
                    $message["info"]   = "唯品会不支持按单号下载";
                    break;
                }
                case 17: //kdt
                {
                    require_once(ROOT_DIR . '/Trade/kdt.php');

                    downKdtTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 20: //mls
                {
                    require_once(ROOT_DIR . '/Trade/mls.php');

                    downmeilishuoTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 22: //bbw
                {
                    require_once(ROOT_DIR . '/Trade/bbw.php');

                    bbwTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 24: //zhe800
                {
                    require_once(ROOT_DIR . '/Trade/zhe800.php');

                    downZheTradesDetail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);

                    break;
                }
                case 25: //融e购
                {
                    require_once(ROOT_DIR . '/Trade/icbc.php');

                    downIcbcTradesDetail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);

                    break;
                }
                case 27: //楚楚街
                {
                    require_once(ROOT_DIR . '/Trade/ccj.php');
                    downCcjTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $error_msg);
                    break;
                }
                case 28: //微盟旺店
                {
                    require_once(ROOT_DIR . '/Trade/weimo.php');
                    downweimoTradesDetail($db, $shop, $scan_count, $total_new, $total_chg, $message );
                    break;
                }
                case 29: //卷皮网
                {
                    require_once(ROOT_DIR . '/Trade/jpw.php');
                    downJpwTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                case 31://飞牛
                {
                    require_once(ROOT_DIR . '/Trade/fn.php');
                    downFnTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new,$total_chg, $message);
                    break;
                }
                case 32: //微店
                {
                    require_once(ROOT_DIR . '/Trade/vdian.php');
                    vdianDownloadTradeDetail($db, $shop, $scan_count, $total_new, $total_chg, $message);

                    if($shop->sub_platform_id == 1){
                        if($message['info'] != '')
                            logx("vdianwei sync_bytid error_msg1:".$message['info'],$sid.'/TradeSlow');
                        $tmp1=$scan_count;	$tmp2=$total_new;	$tmp3=$total_chg;
                        $message['info'] = '';

                        require_once(ROOT_DIR . '/Trade/vdian_wei.php');
                        vdianweiDownloadTradeDetail($db, $shop, $scan_count, $total_new, $total_chg, $message);
                        if($message['info'] != '')
                            logx("vdianwei sync_bytid error_msg2:".$message['info'],$sid.'/TradeSlow');
                        $scan_count+=$tmp1;		$total_new+=$tmp2;	$total_chg+=$tmp3;
                        $message['info'] = '';
                    }

                    break;
                }
                case 33: //pdd
                {
                    require_once(ROOT_DIR . '/Trade/pdd.php');
                    pddTradeDetail($db,$shop,$shop->key,$shop->secret,$scan_count,$total_new,$total_chg,$message);
                    break;
                }
                case 34://蜜芽宝贝
                {
                    require_once(ROOT_DIR .'/Trade/mia.php');
                    downMiaTradesDetail ( $db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $error_msg );
                    break;
                }
                case 36: //善融商城
                {
                    require_once(ROOT_DIR . '/Trade/ccb.php');
                    ccbDownloadTradeDetail($db, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 37: //速卖通
                {
                    require_once(ROOT_DIR . '/Trade/smt.php');

                    downSmtTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 47: //人人店
                {
                    require_once(ROOT_DIR . '/Trade/rrd.php');

                    downRrdTradesDetail($db,$shop->key,$shop->secret,$shop,$scan_count,$total_new,$total_chg,$message);
                    break;
                }
                case 50: //考拉
                {
                    require_once(ROOT_DIR . '/Trade/kl.php');

                    downKlTradesDetail($db,$shop->key,$shop->secret,$shop,$scan_count,$total_new,$total_chg,$message);
                    break;
                }
                case 53://楚楚街拼团
                {
                    require_once(ROOT_DIR . '/Trade/ccjpt.php');
                    downCcjptTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                case 56: //小红书
                {
                    require_once(ROOT_DIR . '/Trade/xhs.php');

                    downXhsTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);

                    break;
                }
                case 60: //返利网
                {
                    require_once(ROOT_DIR.'/Trade/flw.php');

                    $result = downflwTradesDetail($db, $shop->key, $shop->secret, $shop, $total_new, $total_chg, $message);
                    break;
                }
                default:
                    $message["status"] = 0;
                    $message["info"]   = "未知条件下载订单";
                    break;
            }


            //递交
            $open_deliver_cfg = getSysCfg($db, 'order_auto_submit', 0);
            if($open_deliver_cfg){
                if ($total_new > 0 || $total_chg > 0) {
                    $row = $db->query_result('SELECT IF(@cur_uid:=0,rec_id,rec_id) rec_ids FROM api_trade WHERE tid=%s AND platform_id=%d', $shop->tids["0"], (int)$shop->platform_id);
                    deliverSomeTrade($db, $error_msg, $row['rec_ids'], get_sid());
                }
            }

            if (@$message["status"] == 0 && @$message["info"] != "") {
                \Think\Log::write($message["info"]);
                /*$message["status"] = 0;
                $message["info"]   = "未知错误，请联系管理员";*/
            } else {
                $message["status"] = 1;
                $message["info"]   = "扫描订单-1条　新增订单-{$total_new}条　更新订单-{$total_chg}条";
            }
        } catch (\Exception $e) {
            $message["status"] = 0;
            $message["info"]   = $e->getMessage();
        }
    }

    //按照买家昵称下载
    private function sync_byNickname(&$db, $shop, $conditions, &$message) {
        $nickName = trim($conditions["buyer_nick"]);
        if (empty($nickName)) {
            $message["status"] = 0;
            $message["info"]   = "客户网名不能为空";
        }
        if (!$db) {
            logx("sync_bynick getUserDb failed!!", get_sid() . "/Trade");
            $message["status"] = 0;
            $message["info"]   = "未知错误，请联系管理员";
            return;
        }
        $scan_count = 0;
        $total_new  = 0;
        $total_chg  = 0;
        try {
            $platform_id = $shop->platform_id;
            switch ($platform_id) {
                case 1:
                    require_once(ROOT_DIR . "/Trade/top.php");
                    topDownloadTradeListByNickname($db, $shop->key, $shop->secret, $shop, $nickName, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 3:
                    require_once(ROOT_DIR . "/Trade/jos.php");
                    E("该平台不支持按照昵称下载");
                    break;
                default:
                    $message["status"] = 0;
                    $message["info"]   = "该平台不支持按照昵称下载";
                    break;
            }

            $open_deliver_cfg = getSysCfg($db, 'order_auto_submit', 0);
            if($open_deliver_cfg){
                if ($total_new > 0 || $total_chg > 0) {
                    $row = $db->query_result('SELECT (@cur_uid:=0) uid,GROUP_CONCAT(rec_id) rec_ids FROM api_trade WHERE buyer_nick=%s AND process_status=10 AND platform_id=%d',
                        $nickName, (int)$shop->platform_id);
                    deliverSomeTrade($db, $error_msg, $row['rec_ids'], get_sid());
                }
            }

            if (@$message["status"] == 0 && @$message["info"] != "") {
                \Think\Log::write($message["info"]);
                /*$message["status"] = 0;
                $message["info"]   = "未知错误，请联系管理员";*/
            } else {
                $message["status"] = 1;
                $message["info"]   = "扫描订单-{$scan_count}条　新增订单-{$total_new}条　更新订单-{$total_chg}条";
            }
        } catch (\Exception $e) {
            $message["status"] = 0;
            $message["info"]   = $e->getMessage();
        }
    }

    //按照时间下载
    private function sync_byTime(&$db, $shop, $conditions, &$message) {

        $sid       = $shop->sid;
        $now       = time();
        $startTime = strtotime($conditions["start"]);
        $endTime   = strtotime($conditions["end"]);
        /*if ($startTime > $endTime) {
            E('开始时间不能大于结束时间');
            return;
        }
        if ($endTime - $startTime > 3600 * 24) {
            E('时间跨度不能超过24小时');
            return;
        }*/
        if ($endTime > $now ) {
            E('结束时间不能大于当前时间');
            return;
        }
        if ($now - $startTime > 3600 * 24 * 90) {
            E('不能下载三个月之前的订单');
            return;
        }

        if (!$db) {
            \Think\Log::write("sync_bytime getUserDb failed!!");
            E("服务器内部错误");
            return;
        }
        $countLimit = 500;
        $scan_count = 0;
        $total_new  = 0;
        $total_chg  = 0;
        $type='manual';
        try {
            $platform_id = $shop->platform_id;
            switch ($platform_id) {
                case 1: //淘宝
                    require_once(ROOT_DIR . "/Trade/top.php");
                    topSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 2: //分销
                    require_once(ROOT_DIR . '/Trade/top_fx.php');
                    top_fenxiao_download_tradelist($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 3: //京东
                    require_once(ROOT_DIR . "/Trade/jos.php");
                    josDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 5: //亚马逊
                {
                    $message["status"] = 0;
                    $message["info"]   = "亚马逊平台接口调用限制严格，按时间下载禁用";
                    break;
                    /*require_once(ROOT_DIR . '/Trade/amazon.php');
                    amazonSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;*/
                }
                case 6:	//一号店
                {
                    require_once(ROOT_DIR . '/Trade/yhd.php');
                    yhdSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 7: //当当网
                {
                    require_once(ROOT_DIR . '/Trade/dangdang.php');
                    ddSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop,$countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 8: //国美
                    require_once(ROOT_DIR . '/Trade/coo8.php');
                    coo8DownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime,false, $scan_count, $total_new, $total_chg, $message);
                    break;
                case 9: //阿里巴巴
                {
                    require_once(ROOT_DIR . '/Trade/alibaba.php');
                    aliDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false,'', $total_new, $total_chg, $message);
                    break;
                }
                case 13:	//苏宁
                {
                    require_once(ROOT_DIR . '/Trade/sos.php');

                    sosDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 14:	//唯品会
                {
                    require_once(ROOT_DIR . '/Trade/vipshop.php');
                    vipshopDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 17://kdt
                {
                    require_once(ROOT_DIR . '/Trade/kdt.php');
                    kdtDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit,$startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 20://mls
                {
                    require_once(ROOT_DIR . '/Trade/mls.php');
                    meilishuoTradeList($db, $shop->key, $shop->secret, $shop, $countLimit,$startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 22://bbw
                {
                    require_once(ROOT_DIR . '/Trade/bbw.php');
                    bbwDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit,$startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 24://zhe800
                {
                    //同步下载
                    require_once(ROOT_DIR . '/Trade/zhe800.php');
                    zheSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit,$startTime, $endTime,$scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 25 : // icbc融e购
                {
                    require_once (ROOT_DIR . '/Trade/icbc.php');
                    icbcSyncDownloadTradeList ( $db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message );
                    break;
                }
                case 27:    //楚楚街
                {
                    require_once(ROOT_DIR . '/Trade/ccj.php');
                    ccjDownloadTradeList($db, $shop->key, $shop->secret, $shop, 
                        $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 28:    //微盟旺店
                {
                    require_once(ROOT_DIR . '/Trade/weimo.php');
                    weimoSyncDownloadTradeList($db, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 29:    //卷皮网
                {
                    require_once(ROOT_DIR . '/Trade/jpw.php');
                    jpwDownloadTradeList($db, $shop->key, $shop->secret, $shop,
                        $countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 31://飞牛
                {
                    require_once(ROOT_DIR . '/Trade/fn.php');
                    fnDownloadTradeList($db, $shop->key, $shop->secret, $shop,$countLimit, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 32: //微店
                {
                    $countLimit = 100000;
                    require_once(ROOT_DIR . '/Trade/vdian.php');
                    vdianSyncDownloadTradeList($db, $shop, $countLimit, $startTime, $endTime,
                        $scan_count, $total_new, $total_chg, $message);

                    if($shop->sub_platform_id == 1){
                        if($message['status'] == 0)
                            logx($sid." vdian sync_bytime error_msg:".$message['info'],$sid.'/TradeSlow');
                        $tmp1=$scan_count;      $tmp2=$total_new;       $tmp3=$total_chg;
                        $message['info'] = '';

                        require_once(ROOT_DIR . '/Trade/vdian_wei.php');
                        vdianweiSyncDownloadTradeList($db, $shop, $countLimit, $startTime, $endTime,$scan_count, $total_new, $total_chg, $message);

                        if($message['status'] == 0) logx($sid."vdian sync_bytime error_msg2:".$message['info'],$sid.'/TradeSlow');
                        $scan_count+=$tmp1;     $total_new+=$tmp2;      $total_chg+=$tmp3;
                        $message['info'] = '';
                    }
                    break;
                }
                case 33: //pdd
                {
                    require_once(ROOT_DIR . '/Trade/pdd.php');
                    pddTradeListSync($db,$shop,$shop->key,$shop->secret,$countLimit,$startTime,$endTime, $scan_count,$total_new, $total_chg,$message,'manual');
                    break;
                }
                case 34://蜜芽宝贝
                {
                    require_once(ROOT_DIR .'/Trade/mia.php');
                    miaDownloadTradeList ( $db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime,false, $scan_count, $total_new, $total_chg, $message );
                    break;
                }
                case 36: //善融商城
                {
                    require_once(ROOT_DIR . '/Trade/ccb.php');
                    ccbSyncDownloadTradeList($db, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 37://速卖通
                {
                    require_once(ROOT_DIR . '/Trade/smt.php');
                    smtDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, false,'',$scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 47: //人人店
                {
                    require_once(ROOT_DIR . '/Trade/rrd.php');
                    rrdSyncDownloadTradeList($db,$shop->key,$shop->secret,$shop,$countLimit,$startTime,$endTime, $scan_count,$total_new, $total_chg,$message);
                    break;
                }
                case 50: //考拉
                {
                    require_once(ROOT_DIR . '/Trade/kl.php');
                    klDownloadTradeList($db,$shop->key,$shop->secret,$shop,$countLimit,$startTime,$endTime, false,$scan_count,$total_new, $total_chg,$message);
                    break;
                }
                case 53://楚楚街拼团
                {
                    require_once(ROOT_DIR.'/Trade/ccjpt.php');
                    ccjptDownloadTradeList($db, $shop->key, $shop->secret, $shop, $startTime, $endTime, false, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 56://小红书
                {
                    require_once(ROOT_DIR . '/Trade/xhs.php');

                    xhsSyncDownloadTradeList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                }
                case 60: //返利网
                {
                    require_once(ROOT_DIR . '/Trade/flw.php');
                    flwSyncDownloadTradeList($db,$shop->key,$shop->secret,$shop,$countLimit,$startTime,$endTime,$scan_count,$total_new, $total_chg,$message);
                    break;
                }
                default:
                    $message["status"] = 0;
                    $message["info"]   = "未知条件下载订单";
                    break;
            }
            //递交
            $open_deliver_cfg = getSysCfg($db, 'order_auto_submit', 0);
            if($open_deliver_cfg){
                if ($total_new > 0 || $total_chg > 0) {
                    deliverMerchantTrades($db, $message, get_sid());
                }
            }
            if (@$message["status"] == 0 && @$message["info"] != "") {
                \Think\Log::write($message["info"]);
                /*$message["status"] = 0;
                $message["info"]   = "未知错误，请联系管理员";*/
            } else {
                $message["status"] = 1;
                $message["info"]   = "扫描订单-{$scan_count}条　新增订单-{$total_new}条　更新订单-{$total_chg}条";
            }
        } catch (\Exception $e) {
            $message["status"] = 0;
            $message["info"]   = $e->getMessage();
        }
    }

    //回传备注与旗帜
    /*
     * $sid 卖家账号
     * $uid operator_id 操作员id 记录日志使用
     * $trade_ids 系统订单号  字符串形式，逗号分隔
     * $flag  标旗   -1 '不回写',0  => '灰色',1  => '红色',2  => '黄色',3  => '绿色', 4  => '蓝色',5  => '紫色',
     * $remark 备注
     * $type 备注回写形式  1是在原有备注后追加方式。
     * */
    public function manual_upload_remark($sid, $uid, $trade_ids,$flag,$remark,&$message,&$error_list,$type=1)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("manual_upload_remark getUserDb failed!", $sid,'error');
            $message['status'] = 0;
            $message['info'] = '服务器内部错误';
            return;
        }

        $trade_id_arr = explode(",", $trade_ids);

        $tradeNum = count($trade_id_arr);

        for($i=0; $i<$tradeNum; $i++)
        {
            $trade_id = (int)$trade_id_arr[$i];

            $sale_trade = $db->query_result("SELECT `platform_id`, `src_tids` from sales_trade where `trade_id` = %d;" , $trade_id);

            if(!$sale_trade || $sale_trade['src_tids'] == "")
            {
                releaseDb($db);
                logx("manual_upload_remark sale_trade order error!!", $sid.'/TradeTaobao');
                $error_list[] = array('trade_id'=>'','info'=>'系统订单有误');
                continue;
            }

            $tids = explode("," , $sale_trade['src_tids']);

            foreach($tids as $tid)
            {
                $trade = $db->query_result(sprintf("SELECT `tid`,`platform_id`, `shop_id`, `remark` as old_remark from api_trade where `tid` = '%s' and `platform_id` = %d;"  , $tid , (int)$sale_trade['platform_id']));

                if(!$trade || $trade['tid'] == "")
                {
                    releaseDb($db);
                    logx("manual_upload_remark api_trade order error!!", $sid.'/TradeTaobao');
                    $error_list[] = array('trade_id'=>$tid,'info'=>'原始订单订单有误');
                    break;
                }

                $shop = getShopAuth($sid, $db, $trade['shop_id']);
                if(!$shop)
                {
                    releaseDb($db);
                    logx("manual_upload_remark shop not auth !!", $sid.'/TradeTaobao');
                    $error_list[] =  array('trade_id'=>$tid,'info'=>'店铺未授权或授权失效');
                    break;
                }

                $trade = (object)$trade;
                $appkey = $shop->key ;
                $appsecret = $shop->secret ;

                $trade->trade_id = $trade_id;
                $trade->flag = $flag;
                $trade->uid = $uid;
                $remark = trim($remark);

                if($type ==1){
                    $trade->memo = empty($remark)?$remark:$trade->old_remark ." ". $remark; //追加模式更新
                }else{
                    $trade->memo = $remark; //非追加模式更新
                }

                $trade->cs_remark = $remark;

                $shop->trade = $trade;

                switch($shop->platform_id)
                {
                    case 1: //淘宝
                    {
                        require_once(ROOT_DIR . '/Trade/top.php');
                        topUpdateTradeMemo($db, $appkey, $appsecret, $shop, $error_msg);
                        break;
                    }
                    default:
                    {
                        logx("manual_upload_remark  {$trade_id}  平台不支持", $sid.'/TradeTaobao');
                        $error_list[] =  array('trade_id'=>$tid,'info'=>'该平台不支持回传');
                        break;
                    }
                }
            }
        }

        releaseDb($db);

        logx("manual_upload_remark 批量回传备注操作成功，请查看操作记录!!", $sid.'/TradeTaobao');

        $message['status'] = 1;
        $message['info'] = '回传备注操作成功';
        return;
    }

}

?>