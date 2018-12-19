<?php
namespace Platform\Manager;
use Think\Exception;


require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Refund/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
//保存需要执行递交的sid
$refund_handle_merchant = array();
class RefundManager extends Manager{
    public static function register() {
        registerHandle('refund_merchant',array('\\Platform\\Manager\\RefundManager', 'listRefundShops'));
        registerHandle('refund_shop',array('\\Platform\\Manager\\RefundManager', 'downloadRefundList'));
        registerHandle('refund_get', array('\\Platform\\Manager\\RefundManager','tradeTradesDetail'));

        registerHandle('refund_deliver', array('\\Platform\\Manager\\RefundManager','refundDeliver'));

        registerBeforeExit(array('\\Platform\\Manager\\RefundManager','refundBeforeComplete'));

    }


    public static function refund_main()
    {
        return enumAllMerchant('refund_merchant');
    }

    public static function refundBeforeComplete($tube, $complete)
    {
        if($tube != 'refund')
            return;

        deleteJob();

        global $refund_handle_merchant;

        foreach($refund_handle_merchant as $sid => $v)
        {
            pushTask('refund_deliver', $sid, 0, 2048, 600, 300);
        }
    }

    public static function listRefundShops($sid)
    {
        global $g_use_jst_sync;
        deleteJob();

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getShops getUserDb failed!!", $sid);
            return TASK_OK;
        }

        $autoDownload =getSysCfg($db, 'order_auto_download', 0);
        if(!$autoDownload)
        {
            releaseDb($db);
            return TASK_OK;
        }

        /*上次有未递交成功的*/
        $hasRefundToDeliver = getSysCfg($db, 'refund_should_deliver_open', 0);
        if($hasRefundToDeliver)
        {
            logx("Redeliver refunds!", $sid.'/Refund');
            pushTask('refund_deliver', $sid, 0, 2048, 600, 300);
            setSysCfg($db, 'refund_should_deliver_open', 0);
        }

        $result = $db->query("select * from cfg_shop where auth_state=1 and is_disabled=0 and platform_id=1");
        if(!$result)
        {
            releaseDb($db);
            logx("query shop failed!", $sid.'/Refund');
            return TASK_OK;
        }

        while($row = $db->fetch_array($result))
        {
            if(!checkAppKey($row))
                continue;

            $row->sid = $sid;
            pushTask('refund_shop', $row, 0, 1024, 600, 300);
        }

        $db->free_result($result);
        releaseDb($db);

        return TASK_OK;
    }

//下载店铺订单列表
    public static function downloadRefundList($shop)
    {
        global $g_use_jst_sync;

        deleteJob();

        $sid = $shop->sid;
        $shopId = $shop->shop_id;

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("downloadRefundTradeList getUserDb failed!!", $sid.'/Refund');
            return TASK_OK;
        }

        $now = time();

        //是否延时下载
        //淘宝JST可以减少延时
        if($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick) && ($shop->platform_id==1 || $shop->platform_id==2))
        {
            $delayMinite = 5;
        }
        else
        {
            //其它平台10分钟
            $delayMinite = 5;

            //夜间延时加长
            $da = getdate($now);
            if($da['hours'] >= 2 && $da['hours'] <= 7)
                $delayMinite = 30;
        }

        $endTime = $now - $delayMinite*60;

        $startTime = (int)getSysCfg($db, "refund_last_synctime_{$shopId}", 0);

        //检查有没到时间间隔
        if($startTime>0)
        {
            if($now - $startTime > 2592000) //最长30days
                $startTime = $now - 2592000;
            else
                $startTime -= 1;

            $interval = (int)getSysCfg($db, 'refund_sync_interval', 5);
            if($interval < 5) $interval = 5;
            if($interval > 30) $interval = 30;

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
        }
        else
        {
            //最后下载时间没设置的话，下载最近三天
            $startTime = $now - 259200;
            $firstTime = true;
        }

        //无需下载
        if($startTime >= $endTime)
        {
            releaseDb($db);
            logx("Need not scan Refund trade!! {$shopId}", $sid.'/Refund');
            return TASK_OK;
        }

        $result = self :: startDownloadRefundList($db, $sid, $shop, $startTime, $endTime, $firstTime, true);
        releaseDb($db);

        return $result;
    }

    public static function startDownloadRefundList($db, $sid, &$shop, $startTime, $endTime, $firstTime, $saveTime)
    {
        global $refund_handle_merchant, $g_use_jst_sync;

        $refund_handle_merchant[$sid] = 1;

        $shopId = $shop->shop_id;

        //取得appsecret
        getAppSecret($shop, $appkey, $appsecret);

        /*$db_version = (int)getSysCfg($db, 'sys_db_version', 0); //版本判断
        if($db_version < 2212)
        {
            logx("Downloadrefund shopid: {$shopId} sid: {$sid}  卖家客户端版本太低不支持下载退款单", $sid);

            return TASK_OK;
        }*/

        //开始下载
        switch($shop->platform_id)
        {
            case 1: //淘宝天猫
            {
                if($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick)) //使用聚石塔
                {
                    require_once(ROOT_DIR . '/Refund/jst_tb.php');

                    $result = jstDownloadTbRefundList($db,
                        $firstTime,
                        $shop,
                        $startTime,
                        $endTime,
                        $saveTime,
                        $total_trade_count,
                        $new_trade_count,
                        $chg_trade_count,
                        $error_msg);

                    if(TASK_OK == $result)
                    {
                        logx("jst_refund {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/Refund');
                    }

                }else{
                    require_once(ROOT_DIR . '/Refund/top_tb.php');

                    $result = topDownloadTbRefundList(
                        $db,
                        $appkey,
                        $appsecret,
                        $shop,
                        $startTime,
                        $endTime,
                        $saveTime,
                        $total_count,
                        $error_msg);

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

    public static function refundDeliver($sid)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("refundDeliver getUserDb failed!!", $sid.'/Refund');
            return TASK_OK;
        }
        //系统配置里是refund_should_deliver 开关
        $hasTradeToDeliver = getSysCfg($db,'refund_should_deliver',0);
        if(!$hasTradeToDeliver){
            logx('未开启自动递交',$sid.'/Refund');
            releaseDb($db);
            return TASK_OK;
        }
        deliverMerchantRefunds($db, $error_msg, $sid);
        releaseDb($db);

        return TASK_OK;
    }

    /**
     * @卖家拒绝退款接口 手动
     * @refuse_msg 拒绝退款说明
     * @refuse_proof 拒绝退款时的退款凭证
     */
    public function manual_refund_refuse($sid, $uid, $refund_ids, $refuse_msg, $refuse_proof='',&$ack_rows)
    {
        if(empty($refuse_msg))
        {
            logx("sync_refund_refuse refuse_msg is empty in {$refund_ids}!!", $sid.'/Refund');
            SE('拒绝退款说明理由为空！');
            return;
        }

        $ack_rows = array();

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("manual_refund_refuse getUserDb failed!", $sid.'/Refund');
            return;
        }

        $refund_ids = explode(",", $refund_ids);
        for($i=0; $i<count($refund_ids); $i++)
        {
            $refund_id = $refund_ids[$i];

            $result = $db->query_result("select refund_no,shop_id from api_refund where refund_id = {$refund_id}");
            $shop = getShopAuth($sid, $db, $result['shop_id']);
            if(!$shop)
            {
                releaseDb($db);
                logx("topSyncRefundRefuse shop not auth !!", $sid.'/Refund');
                SE('店铺未授权') ;
                return;
            }
            getAppSecret($shop, $appkey, $appsecret);

            switch($shop->platform_id)
            {
                case 1: //淘宝
                {
                    require_once(ROOT_DIR . '/Refund/top_tb.php');
                    topSyncRefundRefuse($db, $appkey, $appsecret, $shop, $refund_id, $refuse_msg, $refuse_proof, $error_msg);
                    $ack_rows[]=array("{$result['refund_no']}", "{$error_msg}");
                    break;
                }
                default:
                {
                    $ack_rows[]=array("{$result['refund_no']}", '功能未实现!');
                }
            }
        }

        releaseDb($db);

        logx("sync_refund_refuse 批量拒绝退款操作成功，请查看操作记录!!", $sid.'/Refund');
        return;
    }

    /**
     * @同意退款
     * @refund_ids
     */
    function manual_refund_agree($sid, $uid, $refund_ids,$code='',&$error_message)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("manual_refund_agree getUserDb failed!", $sid.'/Refund');
            SE('系统错误,请联系管理员');
        }

        $refund_ids = is_array($refund_ids)?$refund_ids:explode(",", $refund_ids);

        $refund_ids = array_unique($refund_ids); //去重复

        // 按店铺筛选退款单
        $results = array();
        for($i=0; $i<count($refund_ids); $i++)
        {
            $refund_id = $refund_ids[$i];
            $result = $db->query_result("select refund_no,shop_id from api_refund where refund_id = {$refund_id}");
            $results[$result['shop_id']][] = $result['refund_no'];
        }

        foreach ($results as $shop_id=>$refund_nos)
        {
            if(count($refund_nos) > 20)
            {
                releaseDb($db);
                logx("sync_refund_agree shop not auth !!", $sid.'/Refund');
                SE('店铺批量一次最多20退款单哦');
                return;
            }

            $shop = getShopAuth($sid, $db, $shop_id);
            if(!$shop)
            {
                releaseDb($db);
                logx("sync_refund_agree shop not auth !!", $sid.'/Refund');
                SE('店铺未授权');
                return;
            }
            getAppSecret($shop, $appkey, $appsecret);
            //$shop->uid = $uid;  子账号授权暂时不绑定员工id
            $refund_no = implode(',', $refund_nos);

            switch($shop->platform_id)
            {

                case 1: //淘宝
                {
                    require_once(ROOT_DIR . '/Refund/top_tb.php');
                    topSyncRefundAgreen($db, $appkey, $appsecret, $shop, $refund_no, $code,$error_msg);

                    if(is_object($error_msg) || is_array($error_msg))
                    {
                        foreach($error_msg as $value)
                        {
                            $error_message[]=array('refund_no'=>$value->refund_no, 'info'=>$value->message);
                        }
                    }else//还未到开始调用接口 直接返回的 逻辑错误。抛出业务逻辑异常处理即可
                    {
                        SE($error_msg);
                        //$error_message[]=array("{$refund_no}", "{$error_msg}");
                    }
                    logx("error_data:".print_r($error_message,true),$sid.'/Refund');
                    break;
                }
                default:
                {
                    SE('该平台不支持此功能!');
                }
            }
        }

        releaseDb($db);

        logx("sync_refund_agreen 批量同意退款操作成功，请查看操作记录!!", $sid.'/Refund');
        return;
    }

    //同意退货
    function manual_refund_agree_back($sid, $uid, $refund_ids ,$remark = '',&$ack_rows)
    {
        $ack_rows = array('refund_no'=>'','msg'=>'');

        $db = getUserDb($sid);
        if (! $db) {
            releaseDb($db);
            logx("manual_refund_agree_back getUserDb failed!", $sid.'/Refund');
            return;
        }

        $refund_ids = explode(",", $refund_ids);
        for ($i = 0; $i < count($refund_ids); $i ++)
        {
            $refund_id = $refund_ids[$i];

            $result = $db->query_result("select refund_no,shop_id from api_refund where refund_id = {$refund_id}");
            $shop = getShopAuth($sid, $db, $result['shop_id']);
            if (! $shop) {
                releaseDb($db);
                logx("sync_refund_agree_back shop not auth !!", $sid.'/Refund');
                SE ('店铺未授权');
                return;
            }
            getAppSecret($shop, $appkey, $appsecret);

            switch ($shop->platform_id)
            {
                case 1: // 淘宝
                {
                    require_once (ROOT_DIR . '/Refund/top_tb.php');

                    $shop->remark = $remark;

                    topSyncRefundGoodsAgree($db, $appkey, $appsecret, $shop, $refund_id, $error_msg);

                    $ack_rows[] = array( "{$result['refund_no']}","{$error_msg}");
                    break;
                }
                default:
                {
                    $ack_rows[] = array( "{$result['refund_no']}","功能未实现!" );
                }
            }
        }

        releaseDb($db);

        logx("sync_refund_agree_back 批量同意退货操作成功，请查看操作记录!!", $sid.'/Refund');
        return;
    }

// 时间段抓取退款单
    function manual_download_refund($sid, $uid, $shop_id, $startTime = '', $endTime = '')
    {
        $db = getUserDb($sid);
        if (! $db) {
            logx("manual_download_refund getUserDb failed!", $sid.'/Refund');
            return;
        }

        $shopId = (int) $shop_id;

        $shop = getShopAuth($sid, $db, $shopId);
        if(!$shop)
        {
            releaseDb($db);
            logx("sync_down_refund shop not auth {$shopId}!!", $sid.'/Refund');
            return;
        }

        getAppSecret($shop, $appkey, $appsecret);

        try{
            switch($shop->platform_id)
            {
                case 1: //淘宝天猫
                {
                    if($shop->push_rds_id && !empty($shop->account_nick)) //使用聚石塔
                    {
                        require_once(ROOT_DIR . '/Refund/jst_tb.php');

                        $result = jstDownloadTbRefundList($db,
                            false,
                            $shop,
                            $startTime,
                            $endTime,
                            false,
                            $total_trade_count,
                            $new_trade_count,
                            $chg_trade_count,
                            $error_msg);

                        if(TASK_OK == $result)
                        {
                            logx("jst_refund {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/Refund');
                        }

                    }else{
                        require_once(ROOT_DIR . '/Refund/top_tb.php');

                        $result = topDownloadTbRefundList(
                            $db,
                            $appkey,
                            $appsecret,
                            $shop,
                            $startTime,
                            $endTime,
                            false,
                            $total_count,
                            $error_msg);

                        if(TASK_OK == $result)
                        {
                            logx("top_refund {$shopId} total_count $total_count", $sid.'/Refund');
                        }

                    }
                    break;
                }
                default:
                {
                    $message['status'] = 0;
                    $message['info'] = '该平台不支持手动下载退款订单';
                }

            }
        }catch (\Exception $e)
        {
            $message['status'] = 0;
            $message['info'] = $e->getMessage();
        }
    }


    /**
     * @param integer $shopId
     * @param array   $message
     * @param array   $conditions
     * 手动下载订单的入口
     */
    public function manualSyncRefund($shopId, &$message, $conditions = null) {
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
                E('退款单不支持按卖家昵称下载');
                return;
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
            logx("sync_bytid getUserDb failed!!", get_sid() . "/Refund");
            $message["status"] = 0;
            $message["info"]   = "未知错误，请联系管理员";
            return;
        }
        $shop->tids = array($conditions["trade_id"]);
        $tid = array($conditions['trade_id']);
        $scan_count = 0;
        $total_chg  = 0;
        $total_new  = 0;
        try {
            $platform_id = $shop->platform_id;
            switch ($platform_id) {
                case 1: //淘宝
                    require_once(ROOT_DIR . "/Refund/top_tb.php");
                    topDownloadTbRefundDetail($db, $shop->key, $shop->secret, $shop, $scan_count, $total_new, $total_chg, $message);
                    break;
                default:
                    $message["status"] = 0;
                    $message["info"]   = "该平台不支持手动下载退款单";
                    break;
            }
            //手动下载退款单暂时不自动递交。

            if (@$message["status"] == 0 && @$message["info"] != "") {
                \Think\Log::write($message["info"]);
                /*$message["status"] = 0;
                $message["info"]   = "未知错误，请联系管理员";*/
            } else {
                $message["status"] = 1;
                $message["info"]   = "扫描退款订单-1条　新增退款订单-{$total_new}条　更新退款订单-{$total_chg}条";
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
        try {
            $platform_id = $shop->platform_id;
            switch ($platform_id) {
                case 1: //淘宝
                    require_once(ROOT_DIR . "/Refund/top_tb.php");
                    topSyncDownloadTbRefundList($db, $shop->key, $shop->secret, $shop, $countLimit, $startTime, $endTime, $scan_count, $total_new, $total_chg, $message);
                    break;
                default:
                    $message["status"] = 0;
                    $message["info"]   = "该平台不支持下载退款订单";
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

}

