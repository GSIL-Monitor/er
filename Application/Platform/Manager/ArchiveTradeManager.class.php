<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/Archive/ApiTrade.php");
require_once(ROOT_DIR . "/Archive/SalesTrade.php");

class ArchiveTradeManager extends Manager {
    protected static $limit = 3000;
    protected static $file_interval = 7; //归档间隔时间

    public static function ArchiveTrade_main() {
        enumAllMerchant("archive_trade_task");
    }

    public static function archiveTradeTask($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveTask!", $sid . "/ArchiveTrade");
            return false;
        }
        pushTask("archive_originaltrade", $sid, 0, 1024, 600, 300);
        pushTask("archive_salestrade", $sid, 0, 1024, 600, 300);
        releaseDb($db);
        return TASK_OK;
    }

    public static function archiveApiTrade($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveApiTrade", $sid . "/ArchiveTrade");
            return false;
        }
        archiveApiTrade($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }

    public static function archiveSalesTrade($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveSalesTrade", $sid . "/ArchiveTrade");
            return false;
        }
        archiveSalesTrade($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
    public static function register() {
        registerHandle("archive_merchant", array("\\Platform\\Manager\\ArchiveTradeManager", "ArchiveTrade_main"));
        registerHandle("archive_trade_task", array("\\Platform\\Manager\\ArchiveTradeManager", "archiveTradeTask"));
        registerHandle("archive_originaltrade", array("\\Platform\\Manager\\ArchiveTradeManager", "archiveApiTrade"));
        registerHandle("archive_salestrade", array("\\Platform\\Manager\\ArchiveTradeManager", "archiveSalesTrade"));
    }

}