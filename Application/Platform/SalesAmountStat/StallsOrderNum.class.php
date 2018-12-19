<?php
function ReflashStallsOrderNum(&$db, $sid, &$msg = ''){
	try{
		$oldtime = getSysCfg($db, "cfg_order_cost_old_time", 0);
		$newtime = date("Y-m-d", time());;
		if($oldtime < $newtime){
			$is_stalls = $db->query_result_single('select value from cfg_setting where `key` = "order_cost" ',0);
			if(!$is_stalls){
				logx("不是按单收费模式",$sid."/SalesAmountStat");
				return false;
			}
			$sql = "select count(stockout_id) as num from stockout_order where status >= 95";
			$data = $db->query_result($sql);
			$history_sql = "select count(stockout_id) as history_num from stockout_order_history where status >=95";
			$history_data = $db->query_result($history_sql);
			$num = empty($data)?0:$data['num'];
			$history_num = empty($history_data)?0:$history_data['history_num'];
			$order_num = (int)$num + (int)$history_num;
			$add_sql = "insert into cfg_user_data(`user_id`,`type`,`code`,`tag`,`data`) values(0,7,'stalls_order_num',0,".$order_num.") ON DUPLICATE KEY UPDATE data=".$order_num;
			$db->execute($add_sql);
			
			$order_balance_num = $db->query_result_single('select value from cfg_setting where `key` = "order_total_num" ',0);
			$date = date('Y-m-d',strtotime("-1 day"));
			$date_now = $date." 00:00:00";
			$last_order_num = $db->query_result("select count(stockout_id) as num from stockout_order where status >= 95 and consign_time >= '{$date_now}'");
			$order_surplus_order = (int)$order_balance_num - (int)$order_num;
			$add_log_sql = "insert into order_surplus_log(`type`,`operator_id`,`put_num`,`data`,`message`,`created`)values(0,0,{$last_order_num['num']},{$order_surplus_order},concat('自动扣费',{$last_order_num['num']},'单'),'{$date}')";
			$db->execute($add_log_sql);
			$sql = "INSERT INTO cfg_setting(`key`,`value`,`class`) VALUES('cfg_order_cost_old_time','{$newtime}','system')
					ON DUPLICATE KEY UPDATE `value` = '{$newtime}'";
			$db->execute($sql);
			logx('订单余额日志记录成功：',$sid."/SalesAmountStat");
			logx('发货数量统计成功',$sid."/SalesAmountStat");
		}
	}catch(\Exception $e){
		$msg = $e->getMessage();
		logx('发货数量统计失败--订单余额日志记录失败：'.$msg,$sid."/SalesAmountStat");
	}
}