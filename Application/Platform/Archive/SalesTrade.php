<?php
function archiveSalesTrade($sid, $db,$interval) {
    try{
        $date = date('Y-m-d H:i:s', time());
        $result = $db->query_result("SELECT created FROM (
        SELECT created FROM sales_trade WHERE (trade_status=5 OR trade_status=95 OR trade_status=100 OR trade_status=110) AND created<date_add('{$date}',INTERVAL '-3' MONTH)
        ORDER BY created ASC LIMIT 3000) st ORDER BY st.created DESC LIMIT 1");
        if (!$result) {
            logx("$sid 查询订单数据失败", $sid . '/Archive');
            return false;
        }elseif($result['created'] == '') {
            logx("$sid 系统订单无可归档数据", $sid . '/Archive');
            return TASK_OK;
        } else {
            $archive_date = $result['created'];
        }
        $db->execute('BEGIN');
        // $date = date('Y-m-d H:i:s',time());
        logx('ST1:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("INSERT IGNORE INTO sales_trade_history SELECT * from sales_trade WHERE created<'{$archive_date}' AND (trade_status=5 OR trade_status=95 OR trade_status=100 OR trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 历史订单归档失败",$sid.'/Archive');
            return false;
        }
        logx('ST2:'.print_r(time(),true),$sid.'/Archive');

        $result = $db->execute("INSERT IGNORE INTO sales_trade_order_history SELECT sto.* FROM sales_trade_order sto INNER JOIN sales_trade st ON sto.trade_id=st.trade_id WHERE st.created<'{$archive_date}' AND (st.trade_status=5 OR st.trade_status=95 OR st.trade_status=100 OR st.trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 历史子订单归档失败",$sid.'/Archive');
            return false;
        }
        logx('ST3:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("INSERT IGNORE INTO sales_trade_log_history(trade_id,operator_id,type,`data`,message,created) SELECT stl.trade_id,operator_id,type,data,message,stl.created from sales_trade_log stl INNER JOIN sales_trade st ON st.trade_id=stl.trade_id WHERE st.created<'{$archive_date}' AND (st.trade_status=5 OR st.trade_status=95 OR st.trade_status=100 OR st.trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 历史订单日志归档失败",$sid.'/Archive');
            return false;
        }
        logx('ST4:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("DELETE stl.* FROM sales_trade_log stl INNER JOIN sales_trade st ON st.trade_id = stl.trade_id WHERE st.created<'{$archive_date}' AND (st.trade_status=5 OR st.trade_status=95 OR st.trade_status=100 OR st.trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 旧订单日志删除失败",$sid.'/Archive');
            return false;
        }
        logx('ST5:'.print_r(time(),true),$sid.'/Archive');
        //订单已归档、子订单未归档
        $result = $db->execute("INSERT IGNORE INTO sales_trade_order_history SELECT sto.* FROM sales_trade_order sto INNER JOIN sales_trade_history st ON sto.trade_id=st.trade_id");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 未归档子订单归档失败",$sid.'/Archive');
            return false;
        }
        logx('ST6:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("DELETE sto.* FROM sales_trade_order sto INNER JOIN sales_trade_history sth ON sth.trade_id = sto.trade_id");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 未归档子订单删除失败",$sid.'/Archive');
            return false;
        }
        logx('ST7:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("DELETE sto.* FROM sales_trade_order sto INNER JOIN sales_trade st ON st.trade_id = sto.trade_id WHERE sto.created<'{$archive_date}' AND (st.trade_status=5 OR st.trade_status=95 OR st.trade_status=100 OR st.trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 旧子订单删除失败",$sid.'/Archive');
            return false;
        }
        logx('ST8:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("DELETE FROM sales_trade WHERE created<'{$archive_date}' AND (trade_status=5 OR trade_status=95 OR trade_status=100 OR trade_status=110)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 旧订单删除失败",$sid.'/Archive');
            return false;
        }
        logx('STEND:'.print_r(time(),true),$sid.'/Archive');
        $db->execute("COMMIT");
        logx("$sid 订单归档成功!",$sid.'/Archive');
        return TASK_OK;
    }catch(\Exception $e){
        $db->execute("ROLLBACK");
        logx("订单归档错误：" . $e->getMessage(), $sid . '/Archive', 'error');
        return false;
    }
}