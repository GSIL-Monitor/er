<?php
function archiveStockout($sid, $db,$interval) {
	$date = date('Y-m-d H:i:s', time());
        $last_date = getSysCfg($db, 'last_stockout', 0);
        //没有上次归档时间或者上次归档时间在六个月之前的就重新计算一下最早的数据
        if ($last_date == 0 || strtotime($last_date) < strtotime("-6 MONTH")) {
            $result = $db->query_result("SELECT min(so.created) as created FROM stockout_order so WHERE (status=5 OR status=95 OR status=100 OR status=105 OR status=110) and so.created<date_add('{$date}',INTERVAL '-3' MONTH)");
            if (!$result) {
                logx("$sid 查询出库单数据失败", $sid . '/Archive');
                return false;
            }elseif($result['created'] == '') {
                logx("$sid 出库单无可归档数据", $sid . '/Archive');
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
            $last_date = date('Y-m-d', $tmp_date); //上次归档时间

        } else {

            $date_sql = "'{$date}',INTERVAL '-3' MONTH ";
            $last_date = date('Y-m-d', $history_date);

    }
    $db->execute("BEGIN");
    $result = $db->execute("INSERT IGNORE INTO stockout_order_history SELECT * FROM stockout_order so WHERE (status=5 OR status=95 OR status=100 OR status=105 OR status=110) and so.created<date_add({$date_sql})");
    if (!$result) {
        $db->execute("ROLLBACK");
        logx("$sid 原始出库单归档失败", $sid . "/Archive");
        return false;
    }
    $result = $db->execute("INSERT IGNORE INTO stockout_order_detail_history SELECT sod.* FROM stockout_order_detail sod INNER JOIN stockout_order so ON so.stockout_id=sod.stockout_id  WHERE (so.status=5 OR so.status=95 OR so.status=100 OR so.status=105 OR so.status=110) and so.created<date_add({$date_sql})");
    if (!$result) {
        $db->execute("ROLLBACK");
        logx("$sid 原始出库单详情归档失败", $sid . "/Archive");
        return false;
    }
    $result = $db->execute("DELETE sod.* FROM stockout_order_detail sod INNER JOIN stockout_order so ON so.stockout_id=sod.stockout_id WHERE (so.status=5 OR so.status=95 OR so.status=100 OR so.status=105 OR so.status=110) and  so.created<date_add({$date_sql})");
    if (!$result) {
        $db->execute("ROLLBACK");
        logx("$sid 旧原始出库单详删除失败", $sid . "/Archive");
        return false;
    }
    $db->execute("SET FOREIGN_KEY_CHECKS=0");
    $result = $db->execute("DELETE FROM stockout_order WHERE (status=5 OR status=95 OR status=100 OR status=105 OR status=110) and created<date_add({$date_sql})");
    if (!$result) {
        $db->execute("ROLLBACK");
        logx("$sid 旧原始出库单删除失败", $sid . "/Archive");
        return false;
    }
    $db->execute("SET FOREIGN_KEY_CHECKS=1");

	$result = setSysCfg($db, 'last_stockout', $last_date);
	if (!$result) {
		$db->execute("ROLLBACK");
		logx("$sid 更新上次出库单归档时间失败", $sid . '/Archive');
		return false;
	}
    $db->execute("COMMIT");
    logx("$sid 原始出库单数据归档成功", $sid . "/Archive");
    return TASK_OK;
}