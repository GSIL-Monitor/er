<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/Archive/StockSpecLog.php");
require_once(ROOT_DIR . "/Archive/ApiStockSync.php");

class ArchiveStockManager extends Manager {
    protected static $limit = 3000;
    protected static $file_interval = 7; //归档间隔时间

    public static function ArchiveStock_main() {
        enumAllMerchant("archive_stock_task");
    }

    public static function archiveStockTask($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveTask!", $sid . "/ArchiveStock");
            return false;
        }
        pushTask("archive_stockspeclog", $sid, 0, 1024, 600, 300);
        pushTask("archive_api_stock_sync", $sid, 0, 1024, 600, 300);
        releaseDb($db);
        return TASK_OK;
    }

    public static function archiveStockSpecLog($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveStockSpecLog", $sid . "/ArchiveStock");
            return false;
        }
        archiveStockSpecLog($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
    public static function archiveApiStockSync($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveApiStockSync", $sid . "/ArchiveStock");
            return false;
        }
        archiveApiStockSync($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
    public static function register() {
        registerHandle("archive_merchant", array("\\Platform\\Manager\\ArchiveStockManager", "ArchiveStock_main"));
        registerHandle("archive_stock_task", array("\\Platform\\Manager\\ArchiveStockManager", "archiveStockTask"));
        registerHandle("archive_stockspeclog", array("\\Platform\\Manager\\ArchiveStockManager", "archiveStockSpecLog"));
        registerHandle("archive_api_stock_sync", array("\\Platform\\Manager\\ArchiveStockManager", "archiveApiStockSync"));
    }

}
