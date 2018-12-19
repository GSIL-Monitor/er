<?php
function archiveApiStockSync($sid,$db,$interval){
    try {
        $date = date('Y-m-d H:i:s',time());
        $last_date = getSysCfg($db, 'last_stock_sync_file_time', 0);
        //没有上次归档时间或者上次归档时间在六个月之前的就重新计算一下最早的数据
        if ($last_date == 0 || strtotime($last_date) < strtotime("-6 MONTH")) {
            $result = $db->query_result("SELECT min(created) as created FROM api_stock_sync_record WHERE created<date_add('{$date}',INTERVAL '-3' MONTH)");
            if(!$result){
                logx("$sid 查询库存同步日志记录失败", $sid . '/Archive');
                return false;
            }elseif($result['created'] == '') {
                logx("$sid 库存同步日志无可归档数据", $sid . '/Archive');
                return TASK_OK;
            }else{
                $last_date = $result['created'];
            }
        }
        $history_date = strtotime("-3 MONTH");
        $last_file_date = strtotime($last_date);
        //如果上次归档时间与三个月前间隔大于归档时间间隔，则按间隔时间归档，否则直接归档三个月前
        if(floor(($history_date - $last_file_date)/86400) > $interval){

            $date_sql = "'{$last_date}',INTERVAL '{$interval}' DAY";
            $tmp_date = strtotime($interval." days", $last_file_date);
            $last_stock_sync = date('Y-m-d',$tmp_date); //上次归档时间

        }else{

            $date_sql = "'{$date}',INTERVAL '-3' MONTH ";
            $last_stock_sync = date('Y-m-d',$history_date);

        }

        $result = $db->execute("DELETE FROM api_stock_sync_history WHERE created<date_add('{$date}',INTERVAL '-6' MONTH )");
        if (!$result) {
            logx("$sid 历史库存同步日志删除6个月前失败", $sid . "/Archive");
            return false;
        }
        $end_time = $last_stock_sync;
        $current_time = $last_date;
        while ($current_time < $end_time) {
            $result = $db->query_result("SELECT created FROM
			(SELECT created FROM api_stock_sync_record
			WHERE created>='{$current_time}' AND created<'{$end_time}' ORDER BY created LIMIT 5000) `assr`
			ORDER BY `assr`.created DESC LIMIT 1");

            if (!$result || $result['created'] == '') {
                $result = setSysCfg($db, 'last_stock_sync_file_time', date('Y-m-d', strtotime($end_time)));
                if (!$result) {
                    logx("$sid 更新上次库存同步日志归档时间失败1", $sid . '/Archive');
                    return false;
                }
                break;
            }else{
                $max_time = $result['created'];
            }
            $db->execute('BEGIN');

            $result = $db->execute("INSERT IGNORE INTO api_stock_sync_history SELECT * FROM api_stock_sync_record `assr` WHERE  `assr`.created>='{$current_time}' AND `assr`.created<='{$max_time}'");
            if (!$result) {
                $db->execute("ROLLBACK");
                logx("$sid 历史库存同步日志归档失败", $sid . '/Archive');
                return false;
            }
            $result = $db->execute("DELETE FROM api_stock_sync_record WHERE created<='{$max_time}'");
            if (!$result) {
                $db->execute("ROLLBACK");
                logx("$sid 库存同步日志删除失败", $sid . '/Archive');
                return false;
            }
            $result = setSysCfg($db, 'last_stock_sync_file_time', date('Y-m-d', strtotime($max_time)));
            if (!$result) {
                $db->execute("ROLLBACK");
                logx("$sid 更新上次库存同步归档时间失败", $sid . '/Archive');
                return false;
            }

            $db->execute('COMMIT');
            $current_time = date('Y-m-d H:i:s', strtotime($max_time)+1);
        }
        logx("$sid 库存同步日志归档成功", $sid . '/Archive');
        return TASK_OK;
    } catch (\Exception $e) {
            $db->execute("ROLLBACK");
            logx("库存同步日志归档错误：".$e->getMessage(), $sid . '/Archive', 'error');
            return false;
        }

}