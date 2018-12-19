<?php
namespace Platform\Manager;
require_once(ROOT_DIR . '/Common/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/TradeFullinfoGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TmcMessageProduceRequest.php');

class TradeTraceManager extends Manager {

    public static function register() {
        registerHandle('tradetrace_merchant', array('\\Platform\\Manager\\TradeTraceManager', 'traceTrace'));
    }

    public static function TradeTrace_main() {
        return enumAllMerchant('tradetrace_merchant');
    }

    public static function traceTrace($sid) {
        global $g_jst_hch_enable;
        deleteJob();
        $db = getUserDb($sid);
        if (!$db) {
            logx("traceTrace getUserDb failed!!", $sid . "/TradeTrace");
            return TASK_OK;
        }

        $traceEnable = getSysCfg($db, 'sales_trade_trace_enable', 1);
        if (!$traceEnable) {
            releaseDb($db);
            return TASK_OK;
        }

        $traceOperator = getSysCfg($db, 'sales_trade_trace_operator', 0);

        $statusMap = array(
            '',
            'X_TO_SYSTEM', // 1(系统已接单)
            'X_SERVICE_AUDITED', // 2(已客审)
            'X_FINANCE_AUDITED', // 3(已财审)
            'X_ALLOCATION_NOTIFIED', // 4(已通知配货)
            'X_WAIT_ALLOCATION', // 5(待配货)
            'X_SORT_PRINTED', // 6(已打拣货单)
            'X_SEND_PRINTED', // 7(已打发货单)
            'X_LOGISTICS_PRINTED', // 8(已打物流单)
            'X_SORTED', // 9(已拣货)
            'X_EXAMINED', // 10(已验货)
            'X_PACKAGED', // 11(已打包)
            'X_WEIGHED', // 12(已称重)
            'X_OUT_WAREHOUSE', // 13(已出库)
            'T_WAIT_BUYER_CONFIRM_GOODS'// 14(已发货)
        );

        //护城河订单日志
        $logMap = array(
            'X_TO_SYSTEM' => '已接单', // 1(系统已接单)
			'X_SERVICE_AUDITED'   => '客审',        //2(已客审)
			'X_ALLOCATION_NOTIFIED'   => '通知配货',  //4(已通知配货)
            'X_SORT_PRINTED'      => '打印拣货单',    //6(已打拣货单)
            'X_SEND_PRINTED'      => '打印物流单',    //7(已打发货单)
            'X_LOGISTICS_PRINTED' => '打印物流单', //8(已打物流单)
            'X_SORTED'            => '拣货',                    //9(已拣货)
            'X_EXAMINED'          => '验货',                //10(已验货)
            'X_WEIGHED'           => '称重',                //12(已称重)
			'T_WAIT_BUYER_CONFIRM_GOODS' => '发货',// 14(已发货)
        );

        $shopAuth = array();
        while (true) {
            //每次取500条
            $result = $db->query("select * from sales_trade_trace order by rec_id asc limit 500");
            if (!$result) {
                releaseDb($db);
                logx("query trade_trade failed!", $sid . "/TradeTrace");
                return TASK_OK;
            }
            $maxId = 0;
            $count = 0;
            while ($row = $db->fetch_array($result)) {
                $maxId = $row['rec_id'];
                ++$count;
                $shopId = (int)$row['shop_id'];
                if (isset($shopAuth[$shopId])) {
                    $shop = $shopAuth[$shopId];
                } else {
                    $message = '';
                    $shop    = $db->query_result("SELECT * FROM cfg_shop WHERE shop_id={$shopId}");
                    if (!checkAppKey($shop)) {
                        logx("trade_trade shop not auth {$shopId} {$message}", $sid . "/TradeTrace");
                        continue;
                    }
                    $appkey    = '';
                    $appsecret = '';
                    getAppSecret($shop, $appkey, $appsecret);
                    $shop->key    = $appkey;
                    $shop->secret = $appsecret;

                    if ($shop->platform_id != 1) {
                        logx("trade_trade shop {$shopId} invalid platform {$shop->platform_id}", $sid . "/TradeTrace");
                        continue;
                    }
                    $shopAuth[$shopId] = $shop;
                }
                if (time() - strtotime($row['action_time']) > 10800) //3hours
                {
                    logx("trade_trade tid {$row['tid']} expire", $sid . "/TradeTrace");
                    continue;
                }

                $content = array(
                    'tid'         => intval($row['tid']),
                    'seller_nick' => $shop->account_nick,
                    'status'      => $statusMap[$row['status']],
                    'action_time' => $row['action_time'],
                    'remark'      => $row['remark']
                );
                if (!empty($row['oids'])) {
                    $content['order_ids'] = $row['oids'];
                }

                if (!empty($row['operator'])) {
                    if ($traceOperator) {
                        $content['operator'] = $row['operator'];
                    }
                    //淘宝日志
                    $operation = $statusMap[$row['status']];
                    if ($g_jst_hch_enable && isset($logMap[$operation])) {
                        $params = array('tradeIds' => $content['tid'], 
										'url' => $logMap[$operation], 
										'operation' => $logMap[$operation],
										'userId' => $row['operator'],
										'ati' => $row['ati'],
										);
                        hchRequest('http://gw.ose.aliyun.com/event/order', $params);
                    }
                }

                /* if (!self::tmcTradeTrace($sid, $content)) {
                    logx("put job fail", $sid);

                    $db->free_result($result);
                    releaseDb($db);
                    return TASK_OK;
                }

                if ($row['status'] == 4) {
                    $content['status'] = 'X_WAIT_ALLOCATION';
                    if (!self::tmcTradeTrace($sid, $content)) {
                        logx("put job fail", $sid);

                        $db->free_result($result);
                        releaseDb($db);
                        return TASK_OK;
                    }
                } */
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                if (!self::pushTradeTrace($sid, $db, $shop, $content)) {
                    continue;
                }

            }

            $db->free_result($result);

            if ($maxId)
                $db->query("delete from sales_trade_trace where rec_id<=$maxId");

            if ($count < 500)
                break;
        }

        releaseDb($db);

        return TASK_OK;
    }

    public static function pushTradeTrace($sid, &$db, $shop, $content) {
        $top            = new \TopClient();
        $top->format    = 'json';
        $top->appkey    = $shop->key;
        $top->secretKey = $shop->secret;
        $req            = new \TmcMessageProduceRequest();
        $req->setTopic("taobao_jds_TradeTrace");
        $req->setContent($content);
        $result = $top->execute($req, $shop->session);
        if (API_RESULT_OK != topErrorTest($result, $db, $shop->shop_id)) {
            logx("pushTradeTrace fail error: {$result->error_msg}" . "  错误信息：" . $content, $sid . "/TradeTrace");
            /* if($result->sub_code == 'session-expired')//如果是店铺授权过期的错误,则删除所有本次需要同步的记录
            {
                $session_failed = 1;
            } */
            return false;
        }
        logx("pushTradeTrace success:" . $content, $sid . "/TradeTrace");
        return true;
    }

    /* public function tmcTradeTrace($sid, $content) {
        $job = array(
            'topic'   => 'taobao_jds_TradeTrace',
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'expire'  => time() + 600
        );

        return self::tmcSendOut($job, $sid);
    }

    public function tmcSendOut($job, $sid) {
        global $g_tmc_bt, $g_tmc_bt_config;
        if (!$g_tmc_bt) {
            try {
                $g_tmc_bt = new \Pheanstalk($g_tmc_bt_config['host'], $g_tmc_bt_config['port'], $g_tmc_bt_config['connect_timeout']);
                $g_tmc_bt->useTube($g_tmc_bt_config['tube']);
            } catch (\Pheanstalk_Exception $e) {
                logx('Pheanstalk START Failed: ' . $e->getMessage(), $sid);
                return false;
            }
        }

        try {
            $g_tmc_bt->put(json_encode($job, JSON_UNESCAPED_UNICODE), 1024, 0, 60);
        } catch (\Pheanstalk_Exception $e) {
            logx('Pheanstalk PUT Failed: ' . $e->getMessage(), $sid);
            return false;
        }

        return true;
    } */
}

?>