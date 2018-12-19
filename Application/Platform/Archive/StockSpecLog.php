<?php
function archiveStockSpecLog($sid, $db,$interval) {

	$date = date('Y-m-d H:i:s', time());
    $last_date = getSysCfg($db, 'last_stockspec_log', 0);
    //没有上次归档时间或者上次归档时间在六个月之前的就重新计算一下最早的数据
    if ($last_date == 0 || strtotime($last_date) < strtotime("-6 MONTH")) {
        $result = $db->query_result("SELECT min(created) as created FROM stock_spec_log WHERE  created<date_add('{$date}',INTERVAL '-3' MONTH)");
        if (!$result || $result['created'] == '') {
            logx("$sid 库存日志无可归档数据", $sid . '/Archive');
            return TASK_OK;
        } else {
            $last_date = $result['created'];
        }
    }

    $history_date = strtotime("-3 MONTH");
    $last_file_date = strtotime($last_date);
    //如果上次归档时间与三个月前间隔大于归档时间间隔，则按间隔时间归档，否则直接归档三个月前
    if (floor(($history_date - $last_file_date) / 86400) > $interval) {

        $date_sql = "'{$last_date}',INTERVAL '{$interval}' DAY";
        $tmp_date = strtotime($interval . " days", $last_file_date);
        $last_stock_spec_log = date('Y-m-d', $tmp_date); //上次归档时间

    } else {

        $date_sql = "'{$date}',INTERVAL '-3' MONTH ";
        $last_stock_spec_log = date('Y-m-d', $history_date);

    }
    $result = $db->execute("DELETE FROM stock_spec_log_history WHERE stock_spec_log_history.created<date_add('{$date}',INTERVAL '-6' MONTH )");
    if (!$result) {
        logx("$sid 历史库存日志删除6个月前失败", $sid . "/Archive");
        return false;
    }
    $result = $db->execute("DELETE FROM stock_spec_log  WHERE stock_spec_log.created<date_add('{$date}',INTERVAL '-7' DAY ) AND stock_spec_log.operator_type = 1 AND  stock_spec_log.`data` =0 ");
    if (!$result) {
        logx("$sid 库存日志警戒库存刷新日志删除失败", $sid . "/Archive");
        return false;
    }
    $end_time = $last_stock_spec_log;
    $current_time = $last_date;
    while($current_time < $end_time) {
        $result = $db->query_result("SELECT created FROM
			(SELECT created FROM stock_spec_log
			WHERE created>='{$current_time}' AND created<'{$end_time}' ORDER BY created LIMIT 5000) `ssl`
			ORDER BY `ssl`.created DESC LIMIT 1");
        if (!$result || $result['created'] == '') {
            $result = setSysCfg($db, 'last_stockspec_log', date('Y-m-d', strtotime($end_time)));
            if (!$result) {
                logx("$sid 更新上次库存日志归档时间失败1", $sid . '/Archive');
                return false;
            }
            break;
        }else{
            $max_time = $result['created'];
        }
        $db->execute("BEGIN");
        $result = $db->execute("INSERT INTO stock_spec_log_history(operator_id,stock_spec_id,operator_type,`data`,num,stock_num,message,created) SELECT operator_id,stock_spec_id,operator_type,data,num,stock_num,message,created FROM stock_spec_log `ssl` WHERE  `ssl`.created>='{$current_time}' AND `ssl`.created<='{$max_time}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 库存日志归档失败", $sid . "/Archive");
            return false;
        }
        $result = $db->execute("DELETE FROM stock_spec_log  WHERE created<='{$max_time}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 库存日志删除失败", $sid . "/Archive");
            return false;
        }
        $result = setSysCfg($db, 'last_stockspec_log', date('Y-m-d', strtotime($max_time)));
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 更新上次库存日志归档时间失败", $sid . '/Archive');
            return false;
        }
        $db->execute("COMMIT");
        $current_time = date('Y-m-d H:i:s', strtotime($max_time)+1);
    }
    $result = $db->execute("DELETE FROM stock_spec_log_history  WHERE stock_spec_log_history.created<date_add('{$date}',INTERVAL '-7' DAY ) AND stock_spec_log_history.operator_type = 1 AND  stock_spec_log_history.`data` =0 ");
    if (!$result) {
        logx("$sid 历史库存日志警戒库存刷新日志删除失败", $sid . "/Archive");
        return false;
    }
    logx("$sid 库存日志数据归档成功", $sid . "/Archive");
    return TASK_OK;
}