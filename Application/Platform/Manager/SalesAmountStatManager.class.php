<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/2/17
 * Time: 17:01
 */

namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatDailySalesAmount.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatMonthlySalesAmount.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatGoodsSpecAmount.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatSalesmanPerformance.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatStockLedger.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StatUserInfo.class.php");
require_once(ROOT_DIR . "/SalesAmountStat/StallsGoodsAccountAmount.class.php");

class SalesAmountStatManager extends Manager {

    //调用该方法，给所有卖家添加listStatMerchant方法
    public static function SalesAmountStat_Main() {
        return enumAllMerchant('stat_merchant');
    }

    //枚举需要统计的卖家
    public static function listStatMerchant($sid) {
        //删除该任务
        deleteJob();
        $db = getUserDb($sid);
        //将日统计加入任务队列
        pushTask("stat_daily", $sid, 0, 1024, 600, 300);
        //将月统计加入任务队列
        pushTask("stat_month", $sid, 0, 1024, 600, 300);
        //将单品统计加入任务队列
        pushTask("stat_goods_spec", $sid, 0, 1024, 600, 300);
        //将业务员绩效统计加入队列
        pushTask("stat_salesman_performance", $sid, 0, 1024, 600, 300);
        //档口供应商队长任务
        pushTask("stat_stalls_goods", $sid, 0, 1024, 600, 300);
        //将库存台账统计加入队列
        pushTask("stat_stock_ledger", $sid, 0, 1024, 600, 300);
        //统计每日单量
        pushTask("stat_user_trade", $sid, 0, 1024, 600, 300);
        //统计每日操作数量
        pushTask("stat_use", $sid, 0, 1024, 600, 300);

        releaseDb($db);
    }

    //按日统计
    public static function SalesAmountDailyStat($sid) {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in listStatMerchant!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        //执行日统计
        resetAlarm(480);
        ReflashSalesDaySell($db, $sid);
        releaseDb($db);
        return TASK_OK;
    }

    //按月统计
    public static function SalesAmountMonthStat($sid) {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in listStatMerchant!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        //执行月统计
        resetAlarm(480);
        ReflashSalesMonthSell($db, $sid);
        releaseDb($db);
        return TASK_OK;
    }
    //单品销售统计
    public static function SalesAmountGoodsSpecStat($sid) {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in listGoodsSpecStatMerchant!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        resetAlarm(480);
        RefreshStatPerSales($db, $sid);
        releaseDb($db);
        return TASK_OK;
    }
    //业务员绩效统计
    public static function SalesmanPerformanceStat($sid) {
    	//删除该任务
    	deleteJob();
    	//获取数据库连接
    	$db = getUserDb($sid);
    	//获取链接失败，记录错误日志
    	if (!$db) {
    		logx("getUserDb failed in listSalesmanPerformanceStatMerchant!!", $sid . "/SalesAmountStat");
    		return TASK_OK;
    	}
        resetAlarm(480);
        RefreshStatSalesmanPerformance($db, $sid);
    	releaseDb($db);
        return TASK_OK;
    }

     //库存台账统计
    public static function StockLedgerStat($sid) {
    	//删除该任务
    	deleteJob();
    	//获取数据库连接
    	$db = getUserDb($sid);
    	//获取链接失败，记录错误日志
    	if (!$db) {
    		logx("getUserDb failed in listStcokLedgerStatMerchant!!", $sid . "/SalesAmountStat");
    		return TASK_OK;
    	}
        resetAlarm(480);
        RefreshStatStockLedger($db, $sid);
    	releaseDb($db);
        return TASK_OK;
    }

    //卖家单量统计
    public static function UserTradeStat($sid) {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in UserTradeStat!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        try{
            resetAlarm(480);
            userTradeStastics($db, $sid);
        }catch (\Exception $e)
        {
            logx($sid.'卖家单量统计错误:'.print_r($e->getMessage()));
            releaseDb($db);
            return TASK_OK;
        }
        releaseDb($db);
        return TASK_OK;
    }

    //卖家单量统计
    public static function UserUseStat($sid) {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in UserUseStat!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        try{
            resetAlarm(480);
            UserUseStat($db, $sid);
        }catch (\Exception $e)
        {
            logx($sid.'卖家操作量统计错误:'.print_r($e->getMessage()));
            releaseDb($db);
            return TASK_OK;
        }
        releaseDb($db);
        return TASK_OK;
    }

    public static function StallsGoodsStat($sid)
    {
        //删除该任务
        deleteJob();
        //获取数据库连接
        $db = getUserDb($sid);
        //获取链接失败，记录错误日志
        if (!$db) {
            logx("getUserDb failed in StallsGoodsStat!!", $sid . "/SalesAmountStat");
            return TASK_OK;
        }
        try{
            resetAlarm(480);
            ReflashStallsGoods($db, $sid);
        }catch (\Exception $e)
        {
            logx($sid.'档口供应商货品统计错误:'.print_r($e->getMessage()));
            releaseDb($db);
            return TASK_OK;
        }
        releaseDb($db);
        return TASK_OK;
    }

    //自动执行之前首先调用该方法，注册需要调用的方法
    public static function register() {
        registerHandle("stat_merchant", array("\\Platform\\Manager\\SalesAmountStatManager", "listStatMerchant"));
        registerHandle("stat_daily", array("\\Platform\\Manager\\SalesAmountStatManager", "SalesAmountDailyStat"));
        registerHandle("stat_month", array("\\Platform\\Manager\\SalesAmountStatManager", "SalesAmountMonthStat"));
        registerHandle("stat_goods_spec", array("\\Platform\\Manager\\SalesAmountStatManager", "SalesAmountGoodsSpecStat"));
        registerHandle("stat_salesman_performance", array("\\Platform\\Manager\\SalesAmountStatManager", "SalesmanPerformanceStat"));
        registerHandle("stat_stock_ledger", array("\\Platform\\Manager\\SalesAmountStatManager", "StockLedgerStat"));
        registerHandle("stat_user_trade", array("\\Platform\\Manager\\SalesAmountStatManager", "UserTradeStat"));
        registerHandle("stat_use", array("\\Platform\\Manager\\SalesAmountStatManager", "UserUseStat"));
        registerHandle("stat_stalls_goods", array("\\Platform\\Manager\\SalesAmountStatManager", "StallsGoodsStat"));
    }

}