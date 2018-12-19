<?php
namespace Platform\Manager;
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
//保存需要执行递交的sid
$trade_handle_merchant = array();

class TradePddManager extends Manager{
    public static function register(){
        registerHandle('trade_pdd_merchant', array('\\Platform\\Manager\\TradePddManager', 'listTradeShops'));
        registerHandle('trade_shop', array('\\Platform\\Manager\\TradePddManager', 'downloadTradeList'));
        registerHandle('trade_get', array('\\Platform\\Manager\\TradePddManager', 'tradeTradesDetail'));
        registerHandle('trade_deliver', array('\\Platform\\Manager\\TradePddManager', 'tradeDeliverTrade'));
        registerHandle('trade_down_span', array('\\Platform\\Manager\\TradePddManager', 'tradeDownloadSpan'));
        registerBeforeExit(array('\\Platform\\Manager\\TradePddManager', 'tradeBeforeComplete'));
    }

    public static function TradePdd_main(){
        return enumAllMerchant('trade_pdd_merchant');
    }

    public static function tradeBeforeComplete($tube, $complete){
        if($tube != 'TradePdd')
            return;

        deleteJob();

        global $trade_handle_merchant;

        foreach($trade_handle_merchant as $sid => $v) {
            pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
        }
    }

    public static function listTradeShops($sid){
        deleteJob();

        $db = getUserDb($sid);

        if(!$db) {
            logx("TradeSlow getShops getUserDb failed!!", $sid. "/TradeSlow");
            return TASK_OK;
        }

        $is_updating = $db->query_result_single("SELECT NOT IS_FREE_LOCK(CONCAT('sys_update_', DATABASE()))");
        if($is_updating) {
            releaseDb($db);
            logx("merchant is updating", $sid. "/TradeSlow");
            return TASK_OK;
        }

        $autoDownload =getSysCfg($db, 'order_auto_download', 0);

        if(!$autoDownload) {
            releaseDb($db);
            logx("not auto download!", $sid . "/TradeSlow");
            return TASK_OK;
        }

        /*上次有未递交成功的*/
        $hasTradeToDeliver = getSysCfg($db, 'order_should_deliver', 0);
        if($hasTradeToDeliver) {
            logx("Redeliver trades!", $sid. "/TradeSlow");
            pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
            setSysCfg($db, 'order_should_deliver', 0);
        }
        //amazon alibaba ECShop vipshop mls ECstore jpw flw
        $result = $db->query("select * ".
            " from cfg_shop ".
            " where auth_state=1 and is_disabled=0 and platform_id =33 ");
        if(!$result) {
            releaseDb($db);
            logx("query shop failed!", $sid. "/TradeSlow");
            return TASK_OK;
        }
        while($row = $db->fetch_array($result)) {
            //过滤掉不抓单的店铺
            if(isset($row['is_undownload_trade']) && $row['is_undownload_trade'] == 1) {
                continue;
            }
            if(!checkAppKey($row)) {
                continue;
            }
            $row->sid = $sid;
            pushTask('trade_shop', $row, 0, 1024, 600, 300);
        }
        $db->free_result($result);
        releaseDb($db);

        return TASK_OK;
    }

    public static function downloadTradeList($shop) {
        global $g_use_jst_sync;
        deleteJob();

        $sid = $shop->sid;
        $shopId = $shop->shop_id;

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("downloadTradeList getUserDb failed!!", $sid.'/TradeSlow');
            return TASK_OK;
        }

        $now = time();

        $interval = (int)getSysCfg($db, 'order_sync_interval', 10);
        $delay = (int)getSysCfg($db, 'order_delay_interval', 2);
        //是否延时下载
        //淘宝JST可以减少延时
        if($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick) && ($shop->platform_id==1 || $shop->platform_id==2)) {
            $delayMinite = 1;
            $interval = 5;
        } else {
            //其它平台10分钟
            $delayMinite = $delay>10?$delay:10;

            //夜间延时加长
            $da = getdate($now);
            if($da['hours'] >= 2 && $da['hours'] <= 7)
                $delayMinite = $delay>30?$delay:30;
        }
        //京东到家O2O外卖
        /*if($shop->platform_id==40){
            $delayMinite = 0;
        }*/

        //隐藏配置，为特殊卖家开启(需要缩短抓单时间)
        /*$api_slow_trade_hide_cfg = getSysCfg($db, 'api_slow_trade_hide_cfg', 0);
        if($api_slow_trade_hide_cfg){
            $delayMinite = 5;
            if(in_array($shop->platform_id,array(127,10)))
                $delayMinite = 1;
        }*/

        $endTime = $now - $delayMinite*60;

        $postfix = '';
        if(isset($shop->order_type)) $postfix = "_{$shop->order_type}";
        $startTime = (int)getSysCfg($db, "order_last_synctime_{$shopId}{$postfix}", 0);

        //检查有没到时间间隔
        if($startTime>0) {
            if($now - $startTime > 2592000) //最长下载30days
                $startTime = $now - 2592000;
            else
                $startTime -= 1;

            if ($interval < 10) $interval = 10;
            if ($interval > 60) $interval = 60;

            //隐藏配置，为特殊卖家开启(需要缩短抓单时间)
            /*if($api_slow_trade_hide_cfg && in_array($shop->platform_id,array(127,9,10,47))){
                $interval = 5;
            }
            //京东到家外卖订单
            if($shop->platform_id==40){
                $interval = 1;
            }*/
            $lastTime = $startTime + $delayMinite*60;

            if($lastTime + $interval*60 > $now)
            {
                releaseDb($db);
                return TASK_OK;
            }
            $authTime = strtotime($shop->auth_time);
            if($startTime<$authTime)
                $firstTime = true;
            else
                $firstTime = false;
        } else {
            //最后下载时间没设置的话，下载最近三天
            $startTime = $now - 259200;
            $firstTime = true;
        }

        //无需下载
        if($startTime >= $endTime) {
            releaseDb($db);
            logx("Need not scan trade!! {$shopId}", $sid.'/TradeSlow');
            return TASK_OK;
        }

        $result = self::startDownloadTradeList($db, $sid, $shop, $startTime, $endTime, $firstTime, true);
        releaseDb($db);

        return $result;
    }

    public static function startDownloadTradeList($db, $sid, &$shop, $startTime, $endTime, $firstTime, $saveTime)
    {
        global $trade_handle_merchant, $g_use_jst_sync;

        $trade_handle_merchant[$sid] = 1;

        $shopId = $shop->shop_id;
        $type='auto';
        //取得appsecret
        logx($sid.'开始下载订单 shop_id:'.$shopId,$sid.'/TradeSlow');
        getAppSecret($shop, $appkey, $appsecret);
        $error_msg ='';
        if ($endTime - $startTime > 3600) {
            logx(" tradeSlow :platform_id: ".$shop->platform_id."shop_id: ".$shopId." sid: ".$sid, 'SY');
        }
        //开始下载
        switch($shop->platform_id)
        {
            case 33: //拼多多
            {
                require_once(ROOT_DIR . '/Trade/pdd.php');

                if($firstTime)
                {
                    $result = pddDownloadTradeList($db, $shop, $appkey, $appsecret, $startTime, $endTime, $saveTime,$total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
                    if(TASK_OK == $result)
                    {
                        logx("log_pdd $shopId scan $total_trade_count new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
                    }
                }else{
                    $result = pddTradeList(
                        $db,
                        $shop,
                        $appkey,
                        $appsecret,
                        $startTime,
                        $endTime,
                        $saveTime,
                        'trade_get',
                        $total_count,
                        $error_msg,
                        $type);
                }
                break;
            }

            default:
            {
                $result = TASK_OK;
            }
        }

        return $result;
    }

    public static function tradeTradesDetail($trades){
        //logx($trades->platform_id);
        $sid = $trades->sid;
        $db = getUserDb($sid);
        if(!$db) {
            logx("tradeTrade getUserDb failed!!", $sid.'/TradeSlow');
            return TASK_SUSPEND;
        }

        //取得appsecret
        getAppSecret($trades, $appkey, $appsecret);

        $scan_count = 0;
        switch($trades->platform_id) {
            case 33://拼多多
            {
                require_once(ROOT_DIR . '/Trade/pdd.php');
                $result = pddTradeDetail(
                    $db,
                    $trades,
                    $appkey,
                    $appsecret,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_pdd {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
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

    public static function tradeDeliverTrade($sid)
    {
        $db = getUserDb($sid);
        $error_msg = '';
        if(!$db)
        {
            logx("tradeDeliverTrade getUserDb failed!!", $sid.'/TradeSlow');
            return TASK_OK;
        }

        $hasTradeToDeliver = getSysCfg($db, 'order_auto_submit', 0);
        if(!$hasTradeToDeliver){
            logx('未开启自动递交',$sid.'/TradeSlow');
            releaseDb($db);
            return TASK_OK;
        }

        logx($sid.'开始执行慢抓单递交',$sid.'/TradeSlow');
        deliverMerchantTrades($db, $error_msg, $sid);
        releaseDb($db);

        return TASK_OK;
    }

}

