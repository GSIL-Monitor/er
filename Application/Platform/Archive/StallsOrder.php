<?php
function archiveStallsOrder($sid,$db,$interval){
	try {
		$date = date('Y-m-d H:i:s',time());
		$last_date = getSysCfg($db,'last_stalls',0);
		//没有上次归档时间或者上次归档时间在六个月之前的就重新计算一下最早的数据
		if ($last_date == 0 || strtotime($last_date) < strtotime("-6 MONTH")) {
			$result = $db->query_result("select min(created) as created from stalls_order where (status = 10 or status =90 ) and created < date_add('{$date}',INTERVAL '-1' MONTH)");
			if(!$result){
				logx("$sid 查询档口单数据失败",$sid.'/Archive');
				return false;
			}elseif($result['created'] == '') {
				logx("$sid 档口单无可归档数据", $sid . '/Archive');
				return TASK_OK;
			}else{
				$last_date = $result['created'];
			}
		}
		$history_date = strtotime('-1 MONTH');
		$last_file_date = strtotime($last_date);
		//如果上次归档时间与三个月前间隔大于归档时间间隔，则按间隔时间归档，否则直接归档三个月前
		if(floor($history_date - $last_file_date)/86400 > $interval){
			$date_sql = "'{$last_date}',INTERVAL '{$interval}' DAY";
			$tmp = strtotime($interval .' days',$last_file_date);
			$last_date = date('Y-m-d',$tmp);
		}else{
			$date_sql = "'{$date}',INTERVAL '-1' MONTH";
			$last_date = date('Y-m-d',$history_date);
		}
		$db->execute('BEGIN');
		$result = $db->execute("insert ignore into stalls_order_history select * from stalls_order where (status = 10 or status = 90) and created < date_add({$date_sql})");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口单归档失败",$sid.'/Archive');
			return false;
		}
		$result = $db->execute("insert ignore into stalls_less_goods_detail_history select sod.* from stalls_less_goods_detail sod inner join stalls_order so on so.stalls_id = sod.stalls_id where so.status = 90 and so.created < date_add({$date_sql})");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口缺货明细归档失败",$sid.'/Archive');
			return false;
		}
		$result = $db->execute("insert IGNORE into operator_stalls_pickup_log_history select ospl.* from operator_stalls_pickup_log ospl
										INNER JOIN stalls_less_goods_detail_history sgd ON sgd.unique_code = ospl.unique_code
										INNER JOIN stalls_order_history so ON sgd.stalls_id = so.stalls_id");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口取货日志归档失败",$sid .'/Archive');
			return false;
		}
		/*$result = $db->execute("delete ospl.* from operator_stalls_pickup_log ospl
										INNER JOIN stalls_less_goods_detail_history sgd ON sgd.unique_code = ospl.unique_code
										INNER JOIN stalls_order_history so ON sgd.stalls_id = so.stalls_id");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口取货日志删除失败",$sid .'/Archive');
			return false;
		}
		$result = $db->execute("delete sod.* from stalls_order so inner join stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id where so.status = 90 and so.created < date_add({$date_sql})");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口缺货明细删除失败",$sid .'/Archive');
			return false;
		}
		$result = $db->execute("delete from stalls_order where (status = 10 or status=90) and created<date_add({$date_sql})");
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 档口单删除失败",$sid.'/Archive');
			return false;
		}*/
		$result = setSysCfg($db,'last_stalls',$last_date);
		if(!$result){
			$db->execute('ROLLBACK');
			logx("$sid 更新归档时间失败",$sid .'/Archive');
			return false;
		}
		$db->execute('COMMIT');
		logx("$sid 档口单归档成功",$sid .'/Archive');
		return TASK_OK;
	} catch (\Exception $e) {
		$db->execute("ROLLBACK");
		logx("档口单归档错误：" . $e->getMessage(), $sid . '/Archive', 'error');
		return false;
	}
}