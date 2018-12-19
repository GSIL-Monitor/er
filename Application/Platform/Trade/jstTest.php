<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Trade/top.php');

function jstTopDownloadTradeList(&$db, $first_time, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg) {
    $cb = function (&$trades, &$trade_list, &$order_list, &$discount_list, &$modified, &$jst_db) use (&$db, $appkey, $appsecret, $shop, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg) {
        $detail_new_trade_count   = 0;
        $detail_chg_trade_count   = 0;
        $detail_total_trade_count = 0;

        if (!jstDownTopTradesDetail($appkey, $appsecret, $shop, $trades, $trade_list, $order_list, $discount_list, $jst_db, $db, $detail_total_trade_count, $detail_new_trade_count, $detail_chg_trade_count, $modified, $error_msg))
            return false;

        $new_trade_count += $detail_new_trade_count;
        $chg_trade_count += $detail_chg_trade_count;
        $total_trade_count += $detail_total_trade_count;

        return $detail_total_trade_count;
    };

    return jstTopDownloadTradeListImpl($db, $first_time, $shop, $start_time, $end_time, $save_time, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg, $cb);
}

function jstTopDownloadTradeListImpl(&$db, $first_time, $shop, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg, $cb) {
    global $g_jst_hch_enable, $rds_name_list, $current_front_host;

    $new_trade_count   = 0;
    $chg_trade_count   = 0;
    $total_trade_count = 0;

    $sid    = $shop->sid;
    $shopId = $shop->shop_id;

    $jst_db = new MySQLdb('rm-vy1b499n9kgw9101f.mysql.rds.aliyuncs.com', 'jptbs', '8VQezi', false, 'jst_info');
    if (!$jst_db) {
        logx("ERROR $sid getJstDb", $sid . "/TradeTest");
        return TASK_OK;
    }

    logx("jstTopDownloadShop". $shopId , $sid . "/TradeTest");

    $offset        = 0;
    $trade_list    = array();
    $order_list    = array();
    $discount_list = array();
    $total_results = 0;
    $once_get      = 1000;
    $start_time =strtotime('2016-08-24 15:08:15');
    $end_time =strtotime('2016-08-25 11:32:50');
    $incr_end_time = $start_time;
    while ($incr_end_time < $end_time) {
        // at most one day in case of too much data got
            $incr_end_time = $end_time;

        // if the time is more than one day, it's needed to cost more than the default timeout, so we reset the timeout clock
        resetAlarm();

        $time_from = date('Y-m-d H:i:s', $start_time - 1);
        $time_to   = date('Y-m-d H:i:s', $incr_end_time + 1);

        $trade_calc_sql = " from jdp_tb_trade WHERE TRUE";

        $trade_calc_sql .= "' and modified>='{$time_from}' and modified<='{$time_to}'";

        $total_results = $jst_db->query_result_single("select count(1) $trade_calc_sql");
        if ($total_results === FALSE) //database error occur
        {
            $error_msg = '数据库错误';
            logx("ERROR $sid db_fail1", $sid . "/TradeTest",'error');
            return TASK_OK;
        }

        if ($total_results == 0) //无订单
        {
        } else if ($total_results <= $once_get) {
            $trade_calc_sql = "select jdp_response,modified $trade_calc_sql";
            $trades         = $jst_db->query($trade_calc_sql);
            if (!$trades) //database error occur
            {
                $error_msg = '数据库错误';
                logx("ERROR $sid db_fail2", $sid . "/TradeTaobao",'error');
                return TASK_OK;
            }
            /*
            //hch日志
            if ($g_jst_hch_enable) {
                $instance = $rds_name_list[ $shop->push_rds_id - 1 ];
                $params   = array('url' => "http://$current_front_host", "db" => $instance, 'sql' => $trade_calc_sql);
                hchRequest('http://gw.ose.aliyun.com/event/sql', $params);
            }
            */
            $count = $cb($trades, $trade_list, $order_list, $discount_list, $modified, $jst_db);
            if ($count === FALSE) {
                $error_msg = '数据库错误';
                logx("ERROR $sid db_fail2", $sid . "/TradeTest");
                return TASK_OK;
            }
        } else {
                $trade_sql = "select jdp_response,modified from jdp_tb_trade where true " ;
            // get from last in case of missing trades
            while (true) {
                $sql = "$trade_sql and modified>='{$time_from}' and modified<='{$time_to}' order by modified asc limit $once_get";

                $trades = $jst_db->query($sql);
                if (!$trades) //database error occur
                {
                    $error_msg = '数据库错误';
                    logx("ERROR $sid db_fail3", $sid . "/TradeTest");
                    return TASK_OK;
                }
                /*
                //hch日志
                if ($g_jst_hch_enable) {
                    $instance = $rds_name_list[ $shop->push_rds_id - 1 ];
                    $params   = array('url' => "http://$current_front_host", "db" => $instance, 'sql' => $sql);
                    hchRequest('http://gw.ose.aliyun.com/event/sql', $params);
                }
                */
                $count = $cb($trades, $trade_list, $order_list, $discount_list, $modified, $jst_db);
                if ($count === FALSE) {
                    $error_msg = '数据库错误';
                    logx("ERROR $sid db_fail4", $sid . "/TradeTest");
                    return TASK_OK;
                }

                if ($count < $once_get) //抓完了
                    break;

                if ($modified == $time_from) //同一时间点出现订单超过一页条?
                {
                    if ($once_get >= 8000) //1秒有单子
                    {
                        logx("ERROR $sid too many trades...", $sid . "/TradeTest",'error');
                        return TASK_OK;
                    } else {
                        $once_get = $once_get * 2;
                    }
                } else {
                    //后移开始时间点
                    $time_from = $modified;
                    $once_get  = 1000;
                }

                // insert into db may cost too much time
                resetAlarm();
            }
        }

        if ($save_time) {
            if ($first_time) $save_time = $incr_end_time - 600;
            else $save_time = $incr_end_time;

            setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
        }

        $start_time = $incr_end_time;
    }

    return TASK_OK;
}

function jstDownTopTradesDetail($appkey, $appsecret, $shop, &$trades, &$trade_list, &$order_list, &$discount_list, &$jst_db, &$db, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$modified, &$error_msg) {
    $sid = $shop->sid;

    while ($trade = $jst_db->fetch_array($trades)) {
        ++$total_trade_count;

        $modified = $trade['modified'];

        $response_trade = json_decode_safe($trade['jdp_response']);
        $trade_info     = $response_trade->trade_fullinfo_get_response;
        if (!is_object($trade_info)) {
            logx("invalid json:" . print_r($trade, true), $sid . "/TradeTest");
            continue;
        }

        if (!loadTradeImpl($db, $appkey, $appsecret, $shop, $trade_info, $trade_list, $order_list, $discount_list))
            continue;

        if (count($order_list) >= 100) {
            if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
                $jst_db->free_result($trades);
                return false;
            }
        }
    }

    $jst_db->free_result($trades);

    if (count($order_list) > 0) {
        if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
            return false;
        }
    }

    return true;

}

function jstDownloadOneTrade(&$db, $sid, $nick, $tid) {
    $shop = $db->query_result("select shop_id,platform_id,sub_platform_id,shop_name,account_nick,push_rds_id,app_key,wms_check,auth_state from cfg_shop where account_nick='" .
        addslashes($nick) . "' and is_disabled=0 and platform_id=1");

    if (!$shop) {
        logx("jstDownloadOneTrade nick_not_found $nick", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    if ($shop['auth_state'] != 1) {
        logx("jstDownloadOneTrade shop not_auth $nick", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    if (!checkAppKey($shop)) {
        logx("jstDownloadOneTrade not_auth1", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    $shop->sid = $sid;

    if (!getAppSecret($shop, $appkey, $appsecret)) {
        logx("jstDownloadOneTrade not_auth12!", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    $shop->key    = $appkey;
    $shop->secret = $appsecret;

    if (!$shop->push_rds_id) {
        logx("jstDownloadOneTrade rds_not_found $tid $nick", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    $jst_db = getJstDb($sid, $shop->push_rds_id);
    if (!$jst_db) {
        logx("jstDownloadOneTrade getJstDb fail", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    $trade = $jst_db->query_result("select seller_nick,jdp_response from jdp_tb_trade where tid='" . addslashes($tid) . "'");
    if (!$trade) {
        logx("jstDownloadOneTrade tid_not_found $tid $nick", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    if ($trade['seller_nick'] != $nick) {
        logx("jstDownloadOneTrade nick_not_match {$trade['seller_nick']} $nick", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    $response_trade = json_decode_safe($trade['jdp_response']);
    $trade_info     = $response_trade->trade_fullinfo_get_response;

    $trade_list    = array();
    $order_list    = array();
    $discount_list = array();
    if (!loadTradeImpl($db, $appkey, $appsecret, $shop, $trade_info, $trade_list, $order_list, $discount_list)) {
        logx("jstDownloadOneTrade load fail: $response_trade", $sid . "/TradeTaobao",'error');
        return FALSE;
    }

    if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
        return FALSE;
    }

    logx("jstDownloadOneTrade update $nick tid $tid", $sid . "/TradeTaobao");

    return TRUE;

}


?>