<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/5/27
 * Time: 13:47
 */
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
//require_once(ROOT_DIR . "/Archive/ApiTrade.php");
//require_once(ROOT_DIR . "/Archive/SalesTrade.php");
require_once(ROOT_DIR . "/Archive/Stockout.php");
//require_once(ROOT_DIR . "/Archive/StockSpecLog.php");
require_once(ROOT_DIR . "/Archive/ApiLogisticsSync.php");
//require_once(ROOT_DIR . "/Archive/ApiStockSync.php");
require_once(ROOT_DIR . "/Archive/StockLogisticsNo.php");
require_once(ROOT_DIR."/Archive/StallsOrder.php");

class ArchiveManager extends Manager {
    protected static $limit = 3000;
    protected static $file_interval = 7; //归档间隔时间

    public static function Archive_main() {
        enumAllMerchant("archive_task");
    }

    public static function archiveTask($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveTask!", $sid . "/Archive");
            return false;
        }
//        pushTask("archive_originaltrade", $sid, 0, 1024, 600, 300);
//        pushTask("archive_salestrade", $sid, 0, 1024, 600, 300);
//        pushTask("archive_stockspeclog", $sid, 0, 1024, 600, 300);
        pushTask("archive_api_logistics_sync", $sid, 0, 1024, 600, 300);
//        pushTask("archive_api_stock_sync", $sid, 0, 1024, 600, 300);
		pushTask("archive_stockout", $sid, 0, 1024, 600, 300);
		pushTask("archive_stallsorder",$sid,0,1024,600,300);
		pushTask("archive_stock_logistics_no", $sid, 0, 1024, 600, 300);

        releaseDb($db);
        return TASK_OK;
    }
/*
    public static function archiveApiTrade($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveApiTrade", $sid . "/Archive");
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
            logx("$sid getUserDb failed in archiveSalesTrade", $sid . "/Archive");
            return false;
        }
        archiveSalesTrade($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
*/
    public static function archiveStockout($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveStockout", $sid . "/Archive");
            return false;
        }
        archiveStockout($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
	public static function  archiveStallsOrder($sid){
		$db = getUserDb($sid);
		if(!$db){
			logx("$sid getUserDb failed in archiveStallsOrder",$sid."/Archive");
			return false;
		}
		archiveStallsOrder($sid,$db,self::$file_interval);
		resetAlarm();
		releaseDb($db);
		return TASK_OK;
	}
/*
    public static function archiveStockSpecLog($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveStockSpecLog", $sid . "/Archive");
            return false;
        }
        archiveStockSpecLog($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
*/
    public static function archiveLogisticsSync($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveLogisticsSync", $sid . "/Archive");
            return false;
        }
        archiveLogisticsSync($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
    public static function archiveStockLogisticsNo($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveStockLogisticsNo", $sid . "/Archive");
            return false;
        }
        archiveStockLogisticsNo($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
/*
    public static function archiveApiStockSync($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in archiveApiStockSync", $sid . "/Archive");
            return false;
        }
        archiveApiStockSync($sid, $db, self::$file_interval);
        resetAlarm();
        releaseDb($db);
        return TASK_OK;
    }
*/

    public static function register() {
        registerHandle("archive_merchant", array("\\Platform\\Manager\\ArchiveManager", "Archive_main"));
        registerHandle("archive_task", array("\\Platform\\Manager\\ArchiveManager", "archiveTask"));
//        registerHandle("archive_originaltrade", array("\\Platform\\Manager\\ArchiveManager", "archiveApiTrade"));
//        registerHandle("archive_salestrade", array("\\Platform\\Manager\\ArchiveManager", "archiveSalesTrade"));
//        registerHandle("archive_stockspeclog", array("\\Platform\\Manager\\ArchiveManager", "archiveStockSpecLog"));
        registerHandle("archive_api_logistics_sync", array("\\Platform\\Manager\\ArchiveManager", "archiveLogisticsSync"));
//        registerHandle("archive_api_stock_sync", array("\\Platform\\Manager\\ArchiveManager", "archiveApiStockSync"));
		registerHandle("archive_stallsorder",array("\\Platform\\Manager\\ArchiveManager","archiveStallsOrder"));
        registerHandle("archive_stockout", array("\\Platform\\Manager\\ArchiveManager", "archiveStockout"));
        registerHandle("archive_stock_logistics_no", array("\\Platform\\Manager\\ArchiveManager", "archiveStockLogisticsNo"));

    }

}