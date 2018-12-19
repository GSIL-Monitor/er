<?php
namespace Platform\Manager;
use Think\Exception;

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");

class LogisticsManager extends Manager {

    public static function Logistics_main() {
        return enumAllMerchant('logisitics_merchant');
    }

    public static function logisticsComplete($tube, $complete) {
        //logx('Process Exit');
    }

    public static function get_stockout_trades($sid) {
        $db = getUserDb($sid);
//         logx('changtao  get_stockout_trades:'.print_r($db,true),$sid);
        if (!$db) {
            logx("ERROR $sid get_stockout_trades getUserDb failed!!", $sid . "/Logistics",'error');
            return TASK_OK;
        }
        $auto_sync = getSysCfg($db, 'logistics_auto_sync', 0);
//         logx('changtao  get_stockout_trades  $auto_sync:'.print_r($auto_sync,true),$sid);
        if (!$auto_sync) {
            releaseDb($db);
            return TASK_OK;
        }

        $now      = time();
        $interval = 30;

        //保存最后一次同步时间，控制同步频率
        //todo :not save sync time
        $last_sync_time = (int)getSysCfg($db, "logistics_last_sync_time", 0);
//         logx('changtao  get_stockout_trades  $$last_sync_time:'.print_r($last_sync_time,true),$sid);

        if ($now <= $last_sync_time + $interval) {
            releaseDb($db);
            return TASK_OK;
        }

        setSysCfg($db, "logistics_last_sync_time", $now);

        $result = $db->query("select * from v_logistics_sync");
//         logx('changtao  get_stockout_trades v_logistics_sync $result  :'.print_r($result,true),$sid);
        if (!$result) {
            releaseDb($db);
            logx("$sid query trades failed", $sid . "/Logistics",'error');
            return TASK_OK;
        }

        //$online = (int)getSysCfg($db, 'logistics_sync_online', 0);

        $trade_count = 0;
        $amazon_count = 6;

        while ($row = $db->fetch_array($result)) {
            if (!checkAppKey($row))
                continue;

            $row->sid = $sid;
            //$row->online = $online;
            if($row->platform_id ==1 or $row->platform_id ==2){
                continue;
            }

            //对亚马逊平台限制一次队列处理数量
            if($row->platform_id ==5 ){
                if ($amazon_count<1){
                    logx("amazon_sync_out_send_limt: 超出限制被推迟 tid {$row->tid}", $sid. "/Logistics");
                    continue;
                }else{
                    $amazon_count--;
                }
            }


            ++$trade_count;
            pushTask('logistics_send', $row);
        }

        $db->free_result($result);
        releaseDb($db);

        if ($trade_count) {
            logx("sync_trade_count except Taobao $trade_count", $sid . "/Logistics");
        }

        return TASK_OK;
    }

    /*public static function send_waybill(&$trade, &$db, &$error_msg) {
        if ('1311' == @$trade->logistics_type) // 京邦达
        {
            require_once(ROOT_DIR . '/Logistics/jos.php');

            return jos_send_waybill($trade, $db, $error_msg);
        }

        return true;
    }*/

    public static function sync_logistics($trade) {
        deleteJob();

        $sid = $trade->sid;
        $db  = getUserDb($sid);
        
        if (!$db) {
            logx("ERROR $sid sync_logistics getUserDb failed!!", $sid . "/Logistics",'error');
            return TASK_OK;
        }

        self::logisticsSyncImpl($trade,$db);
        //判断是否是电子面单
        //switch(bill_type)
        //{
        //}

        /*
        if ($trade->sync_status < 2 && !send_waybill($trade, $db, $error_msg))
        {
            set_sync_fail($db, $sid, $trade->rec_id, 1, $error_msg);
        }
        */
		update_als_status($trade,$db,$sid);
	
        releaseDb($db);

        return TASK_OK;
    }

    static function logisticsSyncImpl($trade,$db){
        $sid = $trade->sid;
        $res = false;
        if ($trade->platform_id == 1 || $trade->platform_id == 2) //淘宝或淘宝分销
        {
            require_once(ROOT_DIR . '/Logistics/top.php');

            if (1 == $trade->is_online) {//是否淘宝在线发货
                //if already accessed, the 'isv.logistics-online-service-error:B04' error message may be returned
                if (!top_online_reachable_logistics($db, $trade, $sid, $error_msg, $error_code) &&
                    'isv.logistics-online-service-error:B04' == $error_code &&
                    top_online_cancel_logistics($db, $trade, $sid)
                ) {
                   $res= top_online_reachable_logistics($db, $trade, $sid, $error_msg, $error_code);
                }
                // if already sent
            } else if (3 == $trade->sync_status) {
                $res = top_resync_logistics($db, $trade, $sid);
            } else if (0 == $trade->sync_status || 2 == $trade->sync_status || 4 == $trade->sync_status || 5 == $trade->sync_status) {
                if (!empty($trade->send_type)) {
                    $res = top_shengxian_sync_logistics($db, $trade, $sid);
                } else if (!empty($trade->online)) {
                    $res = top_online_confirm_logistics($db, $trade, $sid);
                } else if (2 == $trade->delivery_term) {
                    $res = top_online_sync_logistics($db, $trade, $sid);
                } else {
                    $res = top_offline_sync_logistics($db, $trade, $sid);
                    /* if already sent(firstly on-line sent succeeded)
                       then rejected and if resync called (when the changed logistics company is not reachable, the call is failed),
                       if tried again , this time sysn is called( but resync should called actually)
                     */
                }
            }

        } else if (3 == $trade->platform_id) //京东
        {
            require_once(ROOT_DIR . '/Logistics/jos.php');

            if (3 == $trade->sync_status) {
                $res = jos_resync_logistics($db, $trade, $sid);
            } else {
                $res = jos_sync_logistics($db, $trade, $sid);
            }
        } else if($trade->platform_id == 5) //亚马逊
        {
            require_once(ROOT_DIR . '/Logistics/amazon.php');
            amazon_sync_logistics($db, $trade, $sid);
        }else if (6 == $trade->platform_id) //一号店
        {
            require_once(ROOT_DIR . '/Logistics/yhd.php');

            $res = yhd_sync_logistics($db,$trade,$sid);

        } else if(7 == $trade ->platform_id) //当当
        {
            require_once(ROOT_DIR . '/Logistics/dangdang.php');
        
            $dd_orderMode = $db->query_result ( "select cust_data from api_trade where platform_id = {$trade->platform_id} and shop_id = {$trade->shop_id} and tid = '{$trade->tid}'" );
        
            if(isset($dd_orderMode) && $dd_orderMode['cust_data'] == 2)
            {
                $res = ddSyncOnlineLogistics($db, $trade, $sid);
            }
            else
            {
                if((!$res = dd_sync_logistics($db, $trade, $sid,$code_msg))&&$code_msg==32)
                {
                    logx("dd_sync_logistics 超频重试：{$trade->tid}",$sid.'/Logistics');
                    $db->execute("UPDATE api_logistics_sync SET is_need_sync=1 WHERE rec_id={$trade->rec_id}");
                }
            }   
        } else if (8 == $trade->platform_id) //国美
        {
            require_once(ROOT_DIR . '/Logistics/coo8.php');
            $res = coo8_sync_logistics($db,$trade,$sid);
        } else if ($trade->platform_id == 9) //阿里
        {
            require_once(ROOT_DIR . '/Logistics/alibaba.php');
            $res = alibaba_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 13) //Sos
        {
            require_once(ROOT_DIR . '/Logistics/sos.php');
            if($trade->sync_status == 3)
                $res = sos_resync_logistics($db, $trade, $sid);
            else
                $res = sos_sync_logistics($db, $trade, $sid);
        }else if (14 == $trade->platform_id) //唯品会
        {
            require_once(ROOT_DIR . '/Logistics/vipshop.php');
            if(3 == $trade->sync_status)
                $res = vipshop_resync_logistics($db, $trade, $sid);
            else
                $res = vipshop_sync_logistics($db, $trade, $sid);
        }else if (17 == $trade->platform_id) //kdt
        {
            require_once(ROOT_DIR . '/Logistics/kdt.php');

            $res = kdt_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 20) //美丽说
        {
            require_once(ROOT_DIR.'/Logistics/mls.php');

            $res=meilishuo_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 22) //贝贝网
        {
            require_once(ROOT_DIR.'/Logistics/bbw.php');

            $res=bbw_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 24) //折800
        {
            require_once(ROOT_DIR . '/Logistics/zhe800.php');

            $res=zhe_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 25) //融e购
        {
            require_once(ROOT_DIR . '/Logistics/icbc.php');

            $res=icbc_sync_logistics($db, $trade, $sid);
        }else if ($trade -> platform_id == 27) //楚楚街
        {
            require_once(ROOT_DIR . '/Logistics/ccj.php');

            $res = ccj_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 28) //微盟
        {
            require_once(ROOT_DIR . '/Logistics/weimo.php');

            weimo_sync_logistics($db, $trade, $sid);
        }else if ($trade -> platform_id == 29) //卷皮网
        {
            require_once(ROOT_DIR.'/Logistics/jpw.php');

            $res=jpw_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 31) //飞牛
        {
            require_once(ROOT_DIR.'/Logistics/fn.php');

            $res=fn_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 32) //微店
        {
            require_once(ROOT_DIR.'/Logistics/vdian.php');
            $res = vdian_sync_logistics($db, $trade, $sid);
        }
        else if(33 == $trade->platform_id)  //pdd
        {
            require_once(ROOT_DIR . '/Logistics/pdd.php');
            $res = pdd_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 34) //蜜芽宝贝
        {
            require_once(ROOT_DIR . '/Logistics/mia.php');

            $res=mia_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 36) //善融商城
        {
            require_once(ROOT_DIR . '/Logistics/ccb.php');

            $res = ccb_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 37) //速卖通
        {
            require_once(ROOT_DIR . '/Logistics/smt.php');
            $res = smt_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 47)//微吧人人店
        {
            require_once(ROOT_DIR . '/Logistics/rrd.php');
            rrd_sync_logistics($db, $trade, $sid);
        }else if(50 == $trade->platform_id)  //考拉
        {
            require_once(ROOT_DIR . '/Logistics/kl.php');
            $res = kl_sync_logistics($db, $trade, $sid);
        }else if(53 == $trade->platform_id)  //楚楚街拼团
        {
            require_once(ROOT_DIR . '/Logistics/ccjpt.php');
            $res = ccjpt_sync_logistics($db, $trade, $sid);
        }else if ($trade->platform_id == 56) {//小红书
            require_once(ROOT_DIR.'/Logistics/xhs.php');
            xhs_sync_logistics($db, $trade, $sid);
        }
        else if($trade->platform_id == 60) //返利网
        {
            require_once(ROOT_DIR . '/Logistics/flw.php');

            $res = flw_sync_logistics($db, $trade, $sid);
        }
        return $res;
    }

    //自动调用的时候应该首先调用该方法,注册类内部的方法
    public static function register() {
        registerHandle('logisitics_merchant', array('\\Platform\\Manager\\LogisticsManager', 'get_stockout_trades'));
        registerHandle('logistics_send', array('\\Platform\\Manager\\LogisticsManager', 'sync_logistics'));
        registerExit(array('\\Platform\\Manager\\LogisticsManager', 'logisticsComplete'));
    }

    //手动物流同步接口
    public function manualSyncLogistics($sid,&$result,$ids){
        $db = getUserDb($sid);
        if(!empty($ids)){
            $ids = ' rec_id in('.implode(',',$ids).')';
        }else{
            $ids = " true";
        }
        if (!$db) {
            logx("$sid manual_Sync_logistics getUserDb failed!!", $sid . "/Logistics",'error');
            $result = array ('status'=>1,'info'=>'未知错误，请联系管理员');
            return false;
        }

        $sql = "UPDATE api_logistics_sync SET is_need_sync=1 WHERE sync_status<>3 AND sync_status<>4 AND sync_status<>5 AND {$ids}";
        $res=$db->execute($sql);
        if(!$res){
            releaseDb($db);
            logx("$sid update api_logistics_sync failed",$sid."/Logisitcs",'error');
            $result = array ('status'=>1,'info'=>'未知错误，请联系管理员');
        }
        $logistics_res = $db->query("select * from v_logistics_sync where {$ids}");
        if (!$logistics_res) {
            releaseDb($db);
            logx("$sid manual query trades failed", $sid . "/Logistics",'error');
            $result = array ('status'=>1,'info'=>'未知错误，请联系管理员');
            return false;
        }
        if($logistics_res->num_rows == 0){
            $result = array('status'=>1,'info'=>'没有满足物流同步条件的订单');
            return false;
        }
        $trade_count = 0;
        $fail_count = 0;
        while ($row = $db->fetch_array($logistics_res)) {
            if (!checkAppKey($row)){
                continue;
            }

            $row->sid = $sid;
            //$row->online = $online;

            ++$trade_count;
            if(!self::logisticsSyncImpl($row,$db)){
				update_als_status($row,$db,$sid);
                ++$fail_count;
            };
        }
        $success_count = $logistics_res->num_rows-$fail_count;

        if ($fail_count == $logistics_res->num_rows) {
            logx("manual_sync_trade_count fail", $sid . "/Logistics");
            $result = array('status'=>0,'info'=>'物流同步失败，请查看结果');
        }else{
            logx("manual_sync_trade_count $trade_count", $sid . "/Logistics");
            logx("手动物流同步成功, $success_count 个", $sid . "/Logistics");
            $result = array('status'=>0,'info'=>"物流同步成功 $success_count 个，失败 $fail_count 个，请查看结果");

        }
        $db->free_result($logistics_res);
        releaseDb($db);

        return;

    }

    public function downloadShopLogistics($db, $sid, $uid, $shopID, &$msg) {
        //$shop_id = intval($data['shopID']);
        //$model = new Model('', '', $connection);
        //$model = getUserDb($sid);
        $model = $db;
        if (!$model) {
            $msg["status"] = 0;
            $msg["info"]   = "数据库获取失败，请联系管理员";
            logx("$sid downloadShopLogistics:获取数据库失败", $sid . "/Logistics",'error');
            return;
        }
        //todo-ken 需要修改table值后上传
        $shopID = addslashes($shopID);
        $shop   = $model->query("SELECT shop_id,platform_id,app_key FROM cfg_shop where shop_id ={$shopID} and is_disabled = 0 and auth_state = 1");

        $shop = $shop[0]["shop_id"];
        //get_shop_auth($shop);
        $res = parent::sync_auth_check($shopID, $shop, $msg);
        if($res['status']==0 && $res['info']!=''){
            E($res["info"]);
            return;
        }
        getAppSecret($shop, $appkey, $secret);
        $shop->key     = $appkey;
        $shop->secret  = $secret;
        $platform_id   = intval($shop->platform_id);
        $shop->sid     = $sid;
        $companies     = array();
        $msg['status'] = 1;
        $msg["info"]   = "操作成功";


        switch ($platform_id) {
            case 1 : // 淘宝天猫
            case 2: //淘宝分销
            {
                require_once(APP_PATH . 'Platform/Logistics/top.php');
                top_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 3 : { // 京东商城
                require_once(APP_PATH . 'Platform/Logistics/jos.php');
                jos_get_logistics_companies($model, $shop, $companies, $msg);
                break;
            }
            case 5 ://amazon
            {
                require_once (APP_PATH . '/Platform/Logistics/amazon.php');
                amazon_get_logistics_companies( $db, $shop, $companies, $msg );
                break;
            }
            case 6 : { //一号店
                require_once(APP_PATH . 'Platform/Logistics/yhd.php');
                yhd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 8 :{ //国美
                require_once(APP_PATH . 'Platform/Logistics/coo8.php');
                coo8_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 9 : // ali
            {
                require_once (APP_PATH . 'Platform/Logistics/alibaba.php');
                alibaba_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 13 : // sos
            {
                require_once (APP_PATH . 'Platform/Logistics/sos.php');
                sos_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 17 : // kdt
            {
                require_once (APP_PATH . 'Platform/Logistics/kdt.php');
                kdt_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 20 : // mls
            {
                require_once (APP_PATH . 'Platform/Logistics/mls.php');
                meilishuo_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 22 : // bbw
            {
                require_once (APP_PATH . 'Platform/Logistics/bbw.php');
                bbw_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 24 : // zhe800
            {
                require_once (APP_PATH . 'Platform/Logistics/zhe800.php');
                zhe_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 27 : // 楚楚街
            {
                require_once (APP_PATH . 'Platform/Logistics/ccj.php');
                ccj_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 28 : // 微盟
            {
                require_once (APP_PATH . 'Platform/Logistics/weimo.php');
                weimo_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 31://飞牛
            {
                require_once(APP_PATH . 'Platform/Logistics/fn.php');
                fn_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 32://微店
            {
                require_once(APP_PATH . 'Platform/Logistics/vdian.php');
                vdian_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 33: //pdd
            {
                require_once (APP_PATH . 'Platform/Logistics/pdd.php');
                pdd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 36 : // 善融商城
            {
                require_once (ROOT_DIR . 'Platform/Logistics/ccb.php');
                ccb_get_logistics_companies ($db,$shop,$companies,$msg);
                break;
            }
            case 37: //速卖通
            {
                require_once (APP_PATH . 'Platform/Logistics/smt.php');
                smt_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 47: //人人店
            {
                require_once(APP_PATH . 'Platform/Logistics/rrd.php');
                rrd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 56://小红书
            {
                require_once(APP_PATH . 'Platform/Logistics/xhs.php');
                xhs_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 60: //返利网
            {
                require_once(APP_PATH . 'Platform/Logistics/flw.php');
                flw_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            default : {
                throw new \Exception("店铺不支持物流公司下载");
                break;
            }
        }
        //$api_model = new Model('api_logistics_shop');
        //false === $model->addAll($companies, array(), array('flag' => 0)) ||
        //false === $model->execute("COMMIT")
        $model->execute("BEGIN");
        if (1 == $msg['status'] && count($companies) > 0) {
            /*if (false === $model->execute("UPDATE api_logistics_shop SET flag=1 WHERE shop_id={$shopID}") ||
                false === $model->execute('DELETE cls FROM cfg_logistics_shop cls,api_logistics_shop als' .
                                          ' WHERE als.flag=1 AND cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code') ||
                false === $model->execute("DELETE FROM api_logistics_shop WHERE flag=1")
            ) {
                $bool = false;
                foreach ($companies as $v) {
                    $v["created"] = date("Y-m-d H:i:s", time());
                    $value_str    = '';
                    foreach ($v as $key => $val) {
                        $value_str [] = "'" . $key . "','" . $val . "'";
                    }
                    $sql = "insert into api_logistics_shop (`shop_id`,`logistics_code`,`name`,`code_support`,``created) values ("
                        . implode("),(", $value_str) . ")";
                    if (false == $model->execute($sql)) {
                        $bool = true;
                    }
                }
                $model->execute("ROLLBACK");
                $res['status'] = 1;
                $res['info']   = "保存物流公司失败";
                $this->ajaxReturn($res);
            }*/
            $bool = false;
            if (false === $model->execute("UPDATE api_logistics_shop SET flag=1 WHERE shop_id={$shopID}") ||
                false === $model->execute('DELETE cls FROM cfg_logistics_shop cls,api_logistics_shop als' . ' WHERE als.flag=1 AND cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code') ||
                false === $model->execute("DELETE FROM api_logistics_shop WHERE flag=1")
            ) {
                $bool = true;
            }
            if (!$bool) {
                try {
                    foreach ($companies as $v) {
                        $v["created"] = date("Y-m-d H:i:s", time());
                        $v['name'] = empty($v['name'])?$v['logistics_code']:$v['name'];
                        /*$value_str    = '';
                        foreach ($v as $key => $val) {
                            //$value_str = "'" . $key . "','" . $val . "'";
                            $value_str = $value_str . "'" . $val . "'" . ",";
                        }
                        $value_str = substr($value_str, 0, strlen($value_str) - 1);
                        $sql       = "insert into api_logistics_shop (`shop_id`,`logistics_id`,`logistics_code`,`name`,`cod_support`,`created`) values ("
                            . $value_str . ")";*/
                        /*\Think\Log::write($sql);
                        $res = $model->execute($sql);
                        \Think\Log::write($res);*/
                        $res = $model->data($v)->add();
                        if (false === $res) {
                            $bool = true;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
            if ($bool) {
                $model->execute("ROLLBACK");
                $msg['status'] = 0;
                $msg['info']   = "保存物流公司失败";
                //$this->ajaxReturn($res);
                return;
            } else {
                $model->execute("commit");
            }
            // 刷新物流映射
            $platform_id = addslashes($platform_id);
            $model->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                            "SELECT {$shopID},lc.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                            "FROM api_logistics_shop als LEFT JOIN dict_logistics_code lc ON (lc.platform_id={$platform_id} and lc.type = 1 and als.logistics_code=lc.logistics_code) " .
                            "WHERE als.shop_id={$shopID}");

            $model->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                            "SELECT {$shopID},dl.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                            "FROM api_logistics_shop als LEFT JOIN dict_logistics dl ON (dl.logistics_name=als.name) " .
                            "WHERE als.shop_id={$shopID}");
        }
    }

    public function top_address_reachable($partnerID, $address,&$error_msg)
    {
        require_once (APP_PATH . 'Platform/Logistics/top.php');
        $res = top_address_reachable($partnerID, $address, $error_msg);
        return $res;
    }

    /*
    // on logistics confirm
    function topSyncOnlineConfirmLogistics(&$db, &$trade, $sid)
    {
        getAppSecret($trade, $appkey, $appsecret);

        $session = $trade->SessionKey;

        $top = new TopClient();
        $top->format = 'json';
        $top->appkey = $appkey;
        $top->secretKey = $appsecret;
        $req = new LogisticsOnlineConfirmRequest();

        $req->setTid($trade->TradeNO);
        $req->setOutSid($trade->PostID);

        $retval = $top->execute($req, $session);
        if(API_RESULT_OK != topErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);

            logx("top_ol_confirm_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid top_ol_confirm_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');

            return false;
        }

        set_sync_succ($db, $trade->BillID);
        logx("top_ol_confirm_sync_ok: {$trade->TradeNO}", $sid);

        return true;
    }

    */

    /*
    //酷巴
    include_once(TOP_SDK_DIR . 'coo8/Coo8Client.php');

    function coo8SyncLogistics(&$db, &$trade, $sid)
    {
        //待订
        //getAppSecret($trade, $appkey, $appsecret);

        //API参数
        $params = array(
            'venderId' => $trade->PlatformAccount,
            'method' => 'coo8.order.send',
            'orderid' => $trade->TradeNO,
            'carriersName' => $trade->CompanyCode,
            'logisticsNumber' => $trade->PostID
        );
        $coo8 = new Coo8Client();
        $retval = $coo8->sendByPost(COO8_API_URL, $params, $trade->AppSecret);
        if(API_RESULT_OK != coo8ErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);

            logx("coo8_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid coo8_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }

        set_sync_succ($db, $trade->BillID);
        logx("coo8_sync_ok: {$trade->TradeNO}", $sid);

        return true;
    }

    require_once(TOP_SDK_DIR . 'alibaba/AlibabaApi.class.php');

    function alibabaSyncLogistics(&$db, &$trade, $sid)
    {
        getAppSecret($trade, $appkey, $appsecret);

        //取出子订单ID
        $sql = "select GROUP_CONCAT(SUBSTR(OrderNO,4)) from g_api_tradegoods where BillID=" . $trade->BillID;
        $orderIds = $db->query_result_single($sql, 0);

        $retval = AlibabaApi::sync_logistics($appkey,
            $appsecret,
            $trade->SessionKey,
            $trade->PlatformAccount,
            $trade->TradeNO,
            $orderIds,
            $trade->PostID,
            $trade->CompanyCode,
            $trade->LogisticName
            );

        if(API_RESULT_OK != alibabaErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);
            logx("ali_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid ali_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');

            return false;
        }

        set_sync_succ($db, $trade->BillID);
        logx("ali_sync_ok: {$trade->TradeNO}", $sid);

        return true;
    }

    function ecshopSyncLogistics(&$db, &$trade, $sid)
    {
        include_once (TOP_SDK_DIR . 'ecshop/ECShopApi.class.php');
        $shopuri = $trade->SessionKey;
        $adminusername = $trade->AppKey;
        $adminpassword = $trade->AppSecret;
        $ret = ECShopApi::sync_logistics(
                                        $shopuri,
                                        $adminusername,
                                        $adminpassword,
                                        $trade->TradeNO,
                                        $trade->LogisticName,
                                        $trade->PostID
                                        );
        if($ret->code == -1)
        {
            set_sync_fail($db, $trade->BillID, $ret->error_msg);
            logx("ecs_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);

            logx("WARNING $sid ecs_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');

            return false;
        }

        set_sync_succ($db, $trade->BillID);
        logx("ecs_sync_ok: {$trade->TradeNO}", $sid);

        return true;
    }
    function mklSyncLogistics(&$db,&$trade,$sid)
    {
        include_once(TOP_SDK_DIR . 'mkl/MklClient.php');
        $appkey = $trade->appKey;
        $appsecret = $trade->appSecret;
        $sessionkey = $trade->SessionKey;

        $mkl = new MklClient($appkey,$appsecret,$sessionkey);
        $retval = $mkl->sync_logistics($trade->TradeNO,$trade->LogisticName,$trade->PostID);
        if(API_RESULT_OK != mklErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->ResultMsg);

            logx("mkl_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("mkl_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}",'error');
            return false;
        }
        set_sync_succ($db,$trade->BillID);
        logx("mkl_sync_ok: {$trade->TradeNO}",$sid);

        return true;
    }
    function sosSyncLogistics(&$db, &$trade, $sid)
    {
        include_once(TOP_SDK_DIR . 'sos/SosClient.php');
        getAppSecret($trade, $appkey, $appsecret);
        $sos = new SosClient();
        $sos->setAppKey($appkey);
        $sos->setAppSecret($appsecret);
        $sos->setAccessToken($trade->SessionKey);
        $sos->setAppMethod("suning.custom.orderdelivery.add");
        $params->sn_request->sn_body->orderDelivery->orderCode = $trade->TradeNO;
        $params->sn_request->sn_body->orderDelivery->expressNo = $trade->PostID;
        $params->sn_request->sn_body->orderDelivery->expressCompanyCode = $trade->CompanyCode;

        $goods_info = $db->query("SELECT NumIID FROM g_api_tradegoods WHERE TradeNO = {$trade->TradeNO} ");
        if(!$goods_info)
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);
            logx("sos_sync_fail, cannot get NumIID: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid sos_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }

        while($row = $db->fetch_array($goods_info))
        {
            $params->sn_request->sn_body->orderDelivery->sendDetail->productCode[] = $row['NumIID'];
        }

        $params->sn_request->sn_body->orderDelivery->orderLineNumbers->orderLineNumber = new stdClass();

        $params = json_encode($params);
        $retval = $sos->execute($params);
        if(API_RESULT_OK != sosErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);
            logx("sos_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid sos_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        set_sync_succ($db, $trade->BillID);
        logx("sos_sync_ok: {$trade->TradeNO}", $sid);
        return true;
    }
    function sosResyncLogistics(&$db, &$trade, $sid)
    {
        include_once(TOP_SDK_DIR . 'sos/SosClient.php');
        getAppSecret($trade, $appkey, $appsecret);
        $sos = new SosClient();
        $sos->setAppKey($appkey);
        $sos->setAppSecret($appsecret);
        $sos->setAccessToken($trade->SessionKey);
        $sos->setAppMethod("suning.custom.orderdelivery.modify");
        $params->sn_request->sn_body->orderDelivery->orderCode = $trade->TradeNO;
        $params->sn_request->sn_body->orderDelivery->expressNo = $trade->PostID;
        $params->sn_request->sn_body->orderDelivery->expressCompanyCode = $trade->CompanyCode;
        $goods_info = $db->query("SELECT NumIID FROM g_api_tradegoods WHERE TradeNO = {$trade->TradeNO} ");
        if(!$goods_info)
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);
            logx("sos_resync_faill, cannot get NumIID: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid sos_resync_faill TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        while($row = $db->fetch_array($goods_info))
        {
            $params->sn_request->sn_body->orderDelivery->sendDetail->productCode[] = $row['NumIID'];
        }

        $params->sn_request->sn_body->orderDelivery->orderLineNumbers->orderLineNumber = new stdClass();

        $params = json_encode($params);
        $retval = $sos->execute($params);
        if(API_RESULT_OK != sosErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->error_msg);
            logx("sos_resync_faill: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid sos_resync_faill TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        set_sync_succ($db, $trade->BillID);
        logx("sos_resync_ok: {$trade->TradeNO}", $sid);
        return true;
    }
    function vipshopSyncLogistics(&$db, &$trade, $sid)
    {
        require_once(TOP_SDK_DIR . 'vipshop/vipshopOpenApiHandler.php');
        $api_name = "pop/export";
        $api_url = "http://visopen.vipshop.com/api/scm/pop/export.php";
        //$api_url = "http://visopentest.vipshop.com/api/scm/pop/export.php";
        $params = array("order_sn"=> $trade->TradeNO);
        getAppSecret($trade, $appkey, $appsecret);
        $retval = vipshopOpenApiHandler::get($appkey, $appsecret, $api_name, $api_url, $params);
        $retval = json_decode_safe($retval);
        if(API_RESULT_OK != vipshopErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->data->error_msg);
            logx("vipshop_export_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid vipshop_export_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        global $vipshop_logistics_name_map;
        $api_name = "pop/ship";
        $api_url = "http://visopen.vipshop.com/api/scm/pop/ship.php";
        //$api_url = "http://visopentest.vipshop.com/api/scm/pop/ship.php";
        $order_list[] = array("carriers_code"=> $trade->CompanyCode,
                              "transport_no"=> $trade->PostID,
                              "carrier"=> $vipshop_logistics_name_map[$trade->CompanyCode],
                              "order_sn"=> $trade->TradeNO);
        $params = array("order_list"=> json_encode($order_list));
        $retval = vipshopOpenApiHandler::get($appkey, $appsecret, $api_name, $api_url, $params);
        $retval = json_decode_safe($retval);
        //检查返回结果
        if(API_RESULT_OK != vipshopErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->data->error_msg);
            logx("vipshop_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid vipshop_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        set_sync_succ($db, $trade->BillID);
        logx("vipshop_sync_ok: {$trade->TradeNO}", $sid);
        return true;
    }
    function vipshopResyncLogistics(&$db, &$trade, $sid)
    {
        require_once(TOP_SDK_DIR . 'vipshop/vipshopOpenApiHandler.php');
        global $vipshop_logistics_name_map;
        $api_name = "pop/edit_transport_no";
        $api_url = "http://visopen.vipshop.com/api/scm/pop/edit_transport_no.php";
        //$api_url = "http://visopentest.vipshop.com/api/scm/pop/edit_transport_no.php";
        $order_list[] = array("carriers_code"=> $trade->CompanyCode,
                              "transport_no"=> $trade->PostID,
                              "carrier"=> $vipshop_logistics_name_map[$trade->CompanyCode],
                              "order_sn"=> $trade->TradeNO);
        $params = array("order_list"=> json_encode($order_list));
        getAppSecret($trade, $appkey, $appsecret);
        $retval = vipshopOpenApiHandler::get($appkey, $appsecret, $api_name, $api_url, $params);
        $retval = json_decode_safe($retval);
        //检查返回结果
        if(API_RESULT_OK != vipshopErrorTest($retval))
        {
            set_sync_fail($db, $trade->BillID, $retval->data->error_msg);
            logx("vipshop_resync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid vipshop_resync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');
            return false;
        }
        set_sync_succ($db, $trade->BillID);
        logx("vipshop_resync_ok: {$trade->TradeNO}", $sid);
        return true;
    }

    function amazonSyncLogistics(&$db, &$trade, $sid)
    {
        getAppSecret($trade, $appkey, $appsecret);

        include_once (TOP_SDK_DIR . 'amazon/Model.php');
        include_once (TOP_SDK_DIR . 'amazon/Client.php');
        include_once (TOP_SDK_DIR . 'amazon/RequestType.php');
        include_once (TOP_SDK_DIR . 'amazon/Model/SubmitFeedRequest.php');
        include_once (TOP_SDK_DIR . 'amazon/Model/SubmitFeedResponse.php');
        include_once (TOP_SDK_DIR . 'amazon/Model/SubmitFeedResult.php');
        include_once (TOP_SDK_DIR . 'amazon/Model/FeedSubmissionInfo.php');
        include_once (TOP_SDK_DIR . 'amazon/Model/MarketplaceIdList.php');

        $result = $db->query('select OrderNO,CAST(GoodsCount AS UNSIGNED) GoodsCount from g_api_tradegoods where BillID=' . $trade->BillID);
        $goods = '';
        while($row = $db->fetch_array($result))
        {
            //去掉订单编号前面的A
            $orderNo = substr($row['OrderNO'], 1);

            $goods .= '<Item><AmazonOrderItemCode>' . $orderNo . '</AmazonOrderItemCode><Quantity>' . $row['GoodsCount'] . '</Quantity></Item>';
        }
        $db->free_result($result);

    $feed = <<<EOD
    <?xml version="1.0" encoding="UTF-8"?>
    <AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Header>
            <DocumentVersion>1.01</DocumentVersion>
            <MerchantIdentifier>%s</MerchantIdentifier>
        </Header>
        <MessageType>OrderFulfillment</MessageType>
        <Message>
            <MessageID>1</MessageID>
            <OperationType>Update</OperationType>
            <OrderFulfillment>
                <AmazonOrderID>%s</AmazonOrderID>
                <FulfillmentDate>%s</FulfillmentDate>
                <FulfillmentData>
                    <CarrierName>%s</CarrierName>
                    <ShippingMethod>快速</ShippingMethod>
                    <ShipperTrackingNumber>%s</ShipperTrackingNumber>
                </FulfillmentData>
                %s
            </OrderFulfillment>
        </Message>
    </AmazonEnvelope>
    EOD;

        $config = array (
            'ServiceURL' => 'https://mws.amazonservices.com.cn',
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'MaxErrorRetry' => 1,
            );

        $service = new MarketplaceWebServiceOrders_Client(
            $appkey,
            $trade->SessionKey,
            'WdtERP',
            '1.0',
            $config);

        $account = explode(',', $trade->PlatformAccount);
        $merchantID = $account[0];
        $marketID = $account[1];

        $date = date('Y-m-d\TH:i:s\Z', time() - 8*3600 - 60); //东8区, 同时向前移一分钟
        $feed = sprintf($feed, $merchantID, $trade->TradeNO, $date, $trade->CompanyCode, $trade->PostID, $goods);

        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $request->setMerchant($merchantID);
        $request->setMarketplaceIdList(array("Id" => array($marketID)));

        $request->setFeedType('_POST_ORDER_FULFILLMENT_DATA_');
        $request->setContentMd5(base64_encode(md5($feed, true)));

        $request->setPurgeAndReplace(false);
        $request->setFeedContent($feedHandle);

        try
        {
            $response = $service->submitFeed($request);

            set_sync_succ($db, $trade->BillID);
            logx("amazon_sync_ok: {$trade->TradeNO}", $sid);
        }
        catch (MarketplaceWebServiceOrders_Exception $ex)
        {
            $error_msg = $ex->getMessage();

            logx("amazon_sync_failed: msg=$error_msg code=". $ex->getErrorCode());

            logx("amazon_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", $sid);
            logx("WARNING $sid amazon_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyCode {$trade->CompanyCode}", 'error');

            return false;
        }

        return true;
    }

    function vjiaSyncLogistics(&$db, &$trade, $sid)
    {
        include_once (TOP_SDK_DIR . 'vjia/VjiaClient.php');

        $cypher = explode(',', $trade->SessionKey);
        $method = 'SendGoodsConfirm';
        $wsdl = 'http://sws2.vjia.com/swsms/SupplierSendGoodsConfirm.asmx?wsdl';
        $supplierID = $trade->PlatformAccount;

        $vjia = new VjiaClient($trade->AppKey, $trade->AppSecret, $cypher[0], $cypher[1]);

        $params['SWSsupplierId'] = $supplierID;
        $params['DECformCode'] = $vjia->encrypt($trade->TradeNO);
        $params['ExpressCompanyName'] = $trade->CompanyCode;
        $params['DispatchNo']  = $trade->PostID;


        $retval = $vjia->sync_logistics($wsdl, $method, $params);

        if ($vjia->errorTest($retval) != API_RESULT_OK)
        {
            set_sync_fail($db, $trade->BillID, $ret->error_msg);
            logx("vjia_sync_fail: TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyName {$trade->LogisticName}", $sid);

            logx("WARNING $sid vjia_sync_fail TradeNO {$trade->TradeNO} PostID {$trade->PostID} CompanyName {$trade->LogisticName}", 'error');

            return false;
        }

        set_sync_succ($db, $trade->BillID);
        logx("vjia_sync_ok: {$trade->TradeNO}", $sid);

        return true;
    }

    */

    function download_top_logistics_install($sid,$shopid,$serverType,&$msg)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("download_top_logistics_install getUserDb fail!!",$sid.'/Logistics');
            return ;
        }

        $shopid = (int)$shopid;
        $shop = getShopAuth($sid,$db,$shopid);
        if(!$shop)
        {
            releaseDb($db);
            logx("download_top_logistics_install shop not auth {$shopid}",$sid.'/Logistics');
            ackError('店铺未授权');
            return;
        }

        $shop->shop_id = $shopid;
        $shop->sid = $sid;
        $shop->server_type = $serverType;
        $error_msg = '';

        getAppSecret($shop,$appkey,$appsecret);
        switch((int)$shop->platform_id)
        {
            case 1 ://淘宝天猫
            {
                require_once(APP_PATH . 'Platform/Logistics/top.php');
                $success = download_top_logistics_insall($db,$shop,$error_msg);
                if(!$success)
                {
                    $msg['status'] = 1;
                    $msg['info'] = "下载失败:$error_msg";
                }
                break;
            }
            default :
            {
                $msg['status'] = 1;
                $msg['info'] = "平台不支持下载安装服务商";
                break;
            }
        }

        releaseDb($db);
        return;

    }

}

?>
