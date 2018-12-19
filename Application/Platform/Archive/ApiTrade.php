<?php
function archiveApiTrade($sid, $db,$interval)
{
    try {
        $date = date('Y-m-d H:i:s', time());
        //查询到3000条记录限制的时间段。
        $result = $db->query_result("SELECT created FROM
        (SELECT created FROM api_trade WHERE (process_status=60 OR process_status=70) AND created<date_add('{$date}',INTERVAL '-3' MONTH)
        ORDER BY rec_id ASC LIMIT 3000) att ORDER BY att.created DESC LIMIT 1");
        if (!$result) {
            logx("$sid 查询原始订单数据失败", $sid . '/Archive');
            return false;
        }elseif($result['created'] == '') {
            logx("$sid 原始订单无可归档数据", $sid . '/Archive');
            return TASK_OK;
        } else {
            $archive_date = $result['created'];
        }

        $db->execute("BEGIN");
        logx('AT1:' . print_r(time(), true), $sid . '/Archive');
        $result = $db->execute("INSERT IGNORE INTO api_trade_history SELECT * FROM api_trade WHERE (process_status=60 OR process_status=70) AND created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 原始订单归档失败", $sid . "/Archive");
            return false;
        }
        logx('AT2:' . print_r(time(), true), $sid . '/Archive');
        $result = $db->execute("INSERT IGNORE INTO api_trade_order_history SELECT ato.* FROM api_trade_order ato INNER JOIN api_trade at1 ON at1.tid=ato.tid WHERE (at1.process_status=60 OR at1.process_status=70) AND at1.created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 原始子订单归档失败", $sid . "/Archive");
            return false;
        }
        /*$result = $db->execute("INSERT INTO api_trade_discount_history SELECT atd.* FROM api_trade_discount atd  INNER JOIN api_trade at1 ON at1.tid=atd.tid WHERE (at1.process_status=60 OR at1.process_status=70) AND at1.created<date_add('{$date}',INTERVAL '-3' MONTH)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 原始订单优惠归档失败",$sid.'/Archive');
            return false;
        }
        $result = $db->execute("DELETE atd.* FROM api_trade_discount atd INNER JOIN api_trade at1 ON at1.tid=atd.tid WHERE (at1.process_status=60 OR at1.process_status=70) AND at1.created<date_add('{$date}',INTERVAL '-3' MONTH)");
        if(!$result){
            $db->execute("ROLLBACK");
            logx("$sid 旧原始优惠单删除失败",$sid.'/Archive');
            return false;
        }*/

        //有子订单未归档数据进行归档
        logx('AT3:' . print_r(time(), true), $sid . '/Archive');
        $result = $db->execute("INSERT IGNORE INTO api_trade_order_history SELECT ato.* FROM api_trade_order ato INNER JOIN api_trade_history at1 ON at1.shop_id=ato.shop_id AND at1.tid=ato.tid");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 未归档子订单归档失败", $sid . "/Archive");
            return false;
        }
        logx('AT4:' . print_r(time(), true), $sid . '/Archive');
        $result = $db->execute("DELETE ato.* FROM api_trade_order ato INNER JOIN api_trade_history ah ON ah.shop_id=ato.shop_id AND ah.tid=ato.tid");
         if (!$result) {
             $db->execute("ROLLBACK");
             logx("$sid 未归档子订单删除失败", $sid . "/Archive");
             return false;
         }

        logx('AT5:' . print_r(time(), true), $sid . '/Archive');

        $result = $db->execute("DELETE ato.* FROM api_trade_order ato INNER JOIN api_trade at1  ON at1.tid=ato.tid WHERE (at1.process_status=60 OR at1.process_status=70) AND at1.created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 旧原始订单子订单删除失败", $sid . "/Archive");
            return false;
        }
        logx('AT6:'.print_r(time(),true),$sid.'/Archive');
        $result = $db->execute("DELETE FROM api_trade WHERE (process_status=60 OR process_status=70) AND created<'{$archive_date}'");
        if (!$result) {
            $db->execute("ROLLBACK");
            logx("$sid 旧原始订单删除失败", $sid . "/Archive");
            return false;
        }
        logx('ATEND:' . print_r(time(), true), $sid . '/Archive');
        $db->execute("COMMIT");
        logx("$sid 原始订单数据归档成功", $sid . "/Archive");

        return TASK_OK;
    } catch (\Exception $e) {
        $db->execute("ROLLBACK");
        logx("原始订单归档错误：" . $e->getMessage(), $sid . '/Archive', 'error');
        return false;
    }
}