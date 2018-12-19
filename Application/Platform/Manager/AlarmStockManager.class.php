<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/AlarmStock/AlarmStock.php");
class AlarmStockManager extends Manager {

    public static function register() {
        registerHandle("alarm_stock_merchant", array("\\Platform\\Manager\\AlarmStockManager", "AlarmStock_main"));
        registerHandle("alarm_stock_task", array("\\Platform\\Manager\\AlarmStockManager", "AlarmStockTask"));
        registerHandle("alarm_stock_refresh", array("\\Platform\\Manager\\AlarmStockManager", "refreshAlarmStock"));
    }

    public static function AlarmStock_main() {
        enumAllMerchant("alarm_stock_task");
    }

    public static function AlarmStockTask($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in alarmstockTask!", $sid . "/AlarmStock");
            return false;
        }
        $is_auto = getSysCfg($db,'purchase_auto_alarmstock',0);
        if(!$is_auto){
            releaseDb($db);
            logx("not auto refreshAlarmStock!", $sid . "/AlarmStock");
            return TASK_OK;
        }
        pushTask("alarm_stock_refresh", $sid, 0, 1024, 600, 300);
        releaseDb($db);
    }

    public static function refreshAlarmStock($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in refreshAlarmStock", $sid . "/AlarmStock");
            return false;
        }
        refreshAlarmStock($sid, $db);
        resetAlarm(360);
        releaseDb($db);
    }
    public static function manualRefreshAlarmStock($sid,$operator_id=0) {
        $result = array('status'=>0,'info'=>'成功');
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in manualRefreshAlarmStock", $sid . "/AlarmStock");
            return array('status'=>2,'info'=>"$sid getUserDb failed in manualRefreshAlarmStock");
        }
        $result = refreshAlarmStock($sid, $db,$operator_id);
        releaseDb($db);
        return $result;
    }


}