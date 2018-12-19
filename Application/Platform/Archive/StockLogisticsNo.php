<?php
function archiveStockLogisticsNo($sid,$db,$interval){
    $date = date('Y-m-d H:i:s', time());
        $last_date = getSysCfg($db, 'last_stocklogistics_no', 0);
        //没有上次归档时间或者上次归档时间在六个月之前的就重新计算一下最早的数据
        if ($last_date == 0 || strtotime($last_date) < strtotime("-6 MONTH")) {
            $result = $db->query_result("SELECT min(sln.created) as created FROM stock_logistics_no sln WHERE sln.status IN (3,4,6,7) AND sln.created<date_add('{$date}',INTERVAL '-3' MONTH)");
            if (!$result) {
                logx("$sid 查询物流单号数据失败", $sid . '/Archive');
                return false;
            }elseif($result['created'] == '') {
                logx("$sid 物流单号无可归档数据", $sid . '/Archive');
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
	$db->execute('BEGIN');
    $result = $db->execute("INSERT INTO stock_logistics_no_history (SELECT sln.* FROM stock_logistics_no sln WHERE sln.status IN (3,4,6,7) AND sln.created<DATE_ADD({$date_sql}))");
    if(!$result){
        $db->execute("ROLLBACK");
        logx("$sid 历史物流单号记录归档失败",$sid.'/Archive');
        return false;
    }
    $result = $db->execute("DELETE sln.* FROM stock_logistics_no sln WHERE sln.status IN (3,4,6,7) AND sln.created<DATE_ADD({$date_sql})");
    if(!$result){
        $db->execute("ROLLBACK");
        logx("$sid 历史物流单号记录删除失败",$sid.'/Archive');
        return false;
    }
	$result = setSysCfg($db, 'last_stocklogistics_no', $last_date);
	if (!$result) {
		$db->execute("ROLLBACK");
		logx("$sid 更新上次物流单号归档时间失败", $sid . '/Archive');
		return false;
	}
    $db->execute('COMMIT');
    logx("$sid 历史物流单号记录归档成功",$sid.'/Archive');
    return TASK_OK;
}