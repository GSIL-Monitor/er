<?php
/**
 * 淘宝、天猫和淘宝分销 抓单单独完成 与其他平台相分离
 *
 */
namespace Platform\Manager;
require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Trade/util.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");

//保存需要执行递交的sid
$trade_handle_merchant = array();

class TradeTestManager extends Manager {

    public static function register() {
        registerHandle("trade_merchant", array("\\Platform\\Manager\\TradeTestManager", "listTradeShops"));
        registerHandle("trade_shop", array("\\Platform\\Manager\\TradeTestManager", "downloadTradeList"));
        registerHandle("trade_get", array("\\Platform\\Manager\\TradeTestManager", "tradeTradesDetail"));
        registerHandle("trade_deliver", array("\\Platform\\Manager\\TradeTestManager", "tradeDeliverTrade"));
        registerHandle("trade_down_span", array("\\Platform\\Manager\\TradeTestManager", "tradeDownloadSpan"));
        registerBeforeExit(array("\\Platform\\Manager\\TradeTestManager", "tradeBeforeComplete"));
    }

    public static function TradeTest_main() {
        return enumAllMerchant("trade_merchant");
    }

    public static function tradeBeforeComplete($tube, $complete) {
        if ($tube != "trade")
            return;

        deleteJob();

        global $trade_handle_merchant;

        foreach ($trade_handle_merchant as $sid => $v) {
            pushTask("trade_deliver", $sid, 0, 2048, 600, 300);
        }
    }

    public static function listTradeShops($sid) {
        global $g_use_jst_sync;
        deleteJob();

        $db = getUserDb($sid);

        if (!$db) {
            logx("listTradeShops getUserDb failed!!", $sid . "/TradeTest");
            return TASK_OK;
        }

        $is_updating = $db->query_result_single("SELECT NOT IS_FREE_LOCK(CONCAT('sys_update_', DATABASE()))");
        if ($is_updating) {
            releaseDb($db);
            logx("merchant is updating", $sid . "/TradeTest",'error');
            return TASK_OK;
        }

        $autoDownload = getSysCfg($db, "order_auto_download", 0);
        if (!$autoDownload) {
            releaseDb($db);
            return TASK_OK;
        }

        /*上次有未递交成功的*/
        $hasTradeToDeliver = getSysCfg($db, "order_should_deliver", 0);
        if ($hasTradeToDeliver) {
            logx("Redeliver trades!", $sid . "/TradeTest");
            pushTask("trade_deliver", $sid, 0, 2048, 600, 300);
            setSysCfg($db, "order_should_deliver", 0);
        }

        $result = $db->query("select * " .
            " from cfg_shop " .
            " where auth_state=1 and is_disabled=0 and platform_id in (1) ");

        if (!$result) {
            releaseDb($db);
            logx("query shop failed!", $sid . "/TradeTest",'error');
            return TASK_OK;
        }

        while ($row = $db->fetch_array($result)) {
            //过滤掉不抓单的店铺
            if (isset($row["is_undownload_trade"]) && $row["is_undownload_trade"] == 1)
                continue;

            if (!checkAppKey($row))
                continue;

            $row->sid = $sid;
            pushTask("trade_shop", $row, 0, 1024, 600, 300);

            if ($row->platform_id == 2) //淘宝分销
            {
                $row->order_type = 1;
                pushTask("trade_shop", $row, 0, 1024, 600, 300);
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
            logx("downloadTradeList getUserDb failed!!", $sid . "/TradeTest",'error');
            return TASK_OK;
        }

        $now = time();

        $interval = (int)getSysCfg($db, "order_sync_interval", 10);
        //是否延时下载
        //淘宝JST可以减少延时
        if ($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick) && ($shop->platform_id == 1 || $shop->platform_id == 2)) {
            $delayMinite = 1;
            $interval    = 5;
        } else {
            //其它平台10分钟
            $delayMinite = 10;

            //夜间延时加长
            $da = getdate($now);
            if ($da["hours"] >= 2 && $da["hours"] <= 7)
                $delayMinite = 30;
        }

        $endTime = $now - $delayMinite * 60;

        $postfix = "";
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
            logx("Need not scan trade!! {$shopId}", $sid . "/TradeTest");
            return TASK_OK;
        }


        $result = self::startDownloadTradeList($db, $sid, $shop, $startTime, $endTime, $firstTime, true);
        releaseDb($db);

        return $result;
    }

    public static function startDownloadTradeList($db, $sid, &$shop, $startTime, $endTime, $firstTime, $saveTime) {

        global $trade_handle_merchant, $g_use_jst_sync;

        $trade_handle_merchant[$sid] = 1;

        $shopId = $shop->shop_id;
        //取得appsecret
        getAppSecret($shop, $appkey, $appsecret);
        $shop->appkey    = $appkey;
        $shop->appsecret = $appsecret;

        $total_trade_count = 0;
        $new_trade_count   = 0;
        $chg_trade_count   = 0;

        //开始下载
        switch ($shop->platform_id) {
            case 1: //淘宝天猫
            {
                require_once(ROOT_DIR . "/Trade/jstTest.php");

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
                    logx("jst_top {$shopId} new $new_trade_count chg $chg_trade_count", $sid . "/TradeTest");
                }
                break;
            }
        }

        return $result;
    }

    public static function tradeTradesDetail($trades) {
        $sid = $trades->sid;
        $db  = getUserDb($sid);
        if (!$db) {
            logx("tradeDeliverTrade getUserDb failed!!", $sid . "/TradeTest",'error');
            return TASK_SUSPEND;
        }

        //取得appsecret
        getAppSecret($trades, $appkey, $appsecret);

        $scan_count = 0;
        switch ($trades->platform_id) {
            case 1: //淘宝
            {
                require_once(ROOT_DIR . "/Trade/top.php");

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
                    logx("log_top {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid . "/TradeTest");
                }
                break;
            }
        }

        releaseDb($db);
        return $result;
    }

    public static function tradeDeliverTrade($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("tradeDeliverTrade getUserDb failed!!", $sid . "/TradeTest",'error');
            return TASK_OK;
        }

        deliverMerchantTrades($db, $error_msg, $sid);
        releaseDb($db);

        return TASK_OK;
    }

}