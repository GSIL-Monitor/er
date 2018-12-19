<?php
namespace Platform\Manager;
use Think\Exception;

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
class LogisticsTaobaoManager extends Manager{
    public static function register() {
        registerHandle('logisitics_merchant', array('\\Platform\\Manager\\LogisticsTaobaoManager', 'get_stockout_trades'));
        registerHandle('logistics_send', array('\\Platform\\Manager\\LogisticsTaobaoManager', 'sync_logistics'));
        registerExit(array('\\Platform\\Manager\\LogisticsTaobaoManager', 'logisticsComplete'));
    }
    public static function LogisticsTaobao_main() {
        return enumAllMerchant('logisitics_merchant');
    }

    public static function logisticsComplete($tube, $complete) {
        //logx('Process Exit');
    }
    public static function get_stockout_trades($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("ERROR $sid Taobao_get_stockout_trades getUserDb failed!!", $sid . "/Logistics",'error');
            return TASK_OK;
        }
        $auto_sync = getSysCfg($db, 'logistics_auto_sync', 0);
        if (!$auto_sync) {
            releaseDb($db);
            return TASK_OK;
        }

        $now      = time();
        $interval = 30;

        //保存最后一次同步时间，控制同步频率
        //todo :not save sync time
        $last_sync_time = (int)getSysCfg($db, "Toplogistics_last_sync_time", 0);

        if ($now <= $last_sync_time + $interval) {
            releaseDb($db);
            return TASK_OK;
        }

        setSysCfg($db, "Toplogistics_last_sync_time", $now);

        $result = $db->query("select * from v_logistics_sync");
        if (!$result) {
            releaseDb($db);
            logx("$sid query trades failed", $sid . "/Logistics",'error');
            return TASK_OK;
        }

        //$online = (int)getSysCfg($db, 'logistics_sync_online', 0);

        $trade_count = 0;
        while ($row = $db->fetch_array($result)) {
            if (!checkAppKey($row))
                continue;

            $row->sid = $sid;
            //$row->online = $online;
            if($row->platform_id ==1 or $row->platform_id ==2){
                ++$trade_count;
                pushTask('logistics_send', $row);
            }

        }

        $db->free_result($result);
        releaseDb($db);

        if ($trade_count) {
            logx("Taobao sync_trade_count $trade_count", $sid . "/Logistics");
        }

        return TASK_OK;
    }

    public static function sync_logistics($trade) {
        deleteJob();

        $sid = $trade->sid;
        $db  = getUserDb($sid);

        if (!$db) {
            logx("ERROR $sid Taobao sync_logistics getUserDb failed!!", $sid . "/Logistics",'error');
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
    static function logisticsSyncImpl($trade,$db)
    {
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
                    $res = top_online_reachable_logistics($db, $trade, $sid, $error_msg, $error_code);
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
        }else{
            $res = false;
        }
        return $res;
    }
}