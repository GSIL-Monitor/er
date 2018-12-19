<?php
function archiveLogisticsSync($sid,$db,$interval){
    try {
        $date = date('Y-m-d H:i:s',time());
        $result = $db->query_result("SELECT created FROM (
        SELECT created FROM api_logistics_sync 
        WHERE is_need_sync =0 AND created<date_add('{$date}',INTERVAL '-3' MONTH) 
        ORDER BY created ASC LIMIT 3000) als ORDER BY als.created DESC LIMIT 1");
        if(!$result){
            logx("$sid 查询物流同步记录失败", $sid . '/Archive');
            return false;
        }elseif($result['created'] == '') {
            logx("$sid 物流同步无可归档数据", $sid . '/Archive');
            return TASK_OK;
        }else{
            $archive_date = $result['created'];
        }
        
        $db->execute('BEGIN');

        $result = $db->execute("INSERT IGNORE INTO api_logistics_sync_history SELECT * FROM api_logistics_sync WHERE is_need_sync =0 AND created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 历史物流同步记录归档失败", $sid . '/Archive');
            return false;
        }
        $result = $db->execute("DELETE FROM api_logistics_sync WHERE is_need_sync =0 AND created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 物流同步记录删除失败", $sid . '/Archive');
            return false;
        }
        $db->execute('COMMIT');
        logx("$sid 物流同步记录归档成功", $sid . '/Archive');

        return TASK_OK;

    } catch (\Exception $e) {
        $db->execute("ROLLBACK");
        logx("物流同步归档错误：".$e->getMessage(), $sid . '/Archive', 'error');
        return false;
    }
}