<?php
function RefreshStatSalesmanPerformance(&$db,$sid,&$mag=""){
	//获取上次统计日期，如果日期为0，则设为当前时间统计日期前10天
	$V_StatSalesmanTimeBegin = getSysCfg($db, "cfg_stat_salesman_performance", 0);
	if (!$V_StatSalesmanTimeBegin) {
		$V_StatSalesmanTimeBegin = date_create(date("Y-m-d"));
		date_add($V_StatSalesmanTimeBegin, date_interval_create_from_date_string('-10 days'));
		$V_StatSalesmanTimeBegin = date_format($V_StatSalesmanTimeBegin, "Y-m-d G:i:s");
	}
	//在统计之前设定初始时间为上次统计时间
	$V_CurDateTime = $V_StatSalesmanTimeBegin;
	//统计结束时间
	$V_StatSalesmanDateEnd = date("Y-m-d", strtotime('-1 days'));
	$V_StatSalesmanTimeEnd = $V_StatSalesmanDateEnd . " 23:59:59";
	try{
		if($V_CurDateTime<$V_StatSalesmanTimeEnd){
			$db->execute("START TRANSACTION");
			//删除新增的驳回订单记录
			$sql_delete="DELETE FROM stat_salesman_performance WHERE trade_id IN
					(SELECT trade_id FROM sales_trade_log WHERE type=30 AND created >='{$V_StatSalesmanTimeBegin}' AND created<='{$V_StatSalesmanTimeEnd}')";
			$db->execute($sql_delete);
			//新增和更新审核订单记录
			$sql="INSERT INTO stat_salesman_performance (trade_id,salesman_id,created) 
					SELECT st.trade_id,st.salesman_id,stl.created FROM sales_trade_log stl 
					LEFT JOIN sales_trade st ON st.trade_id =stl.trade_id 
					WHERE (stl.type=9 OR stl.type=45) AND stl.created>='{$V_StatSalesmanTimeBegin}' AND stl.created<='{$V_StatSalesmanTimeEnd}' AND st.trade_status>=55  
					ON DUPLICATE KEY 
					UPDATE stat_salesman_performance.salesman_id=stat_salesman_performance.salesman_id, stat_salesman_performance.created=stat_salesman_performance.created ";
			$db->execute($sql);
			$db->execute("INSERT INTO cfg_setting (`key`,`value`,`class`)
	                        VALUES('cfg_stat_salesman_performance',CURDATE(),'system')
	                        ON DUPLICATE KEY
	                        UPDATE `value` = VALUES(`value`)");
			$db->execute("COMMIT");
		}
		logx("业务员绩效统计成功", $sid . "/SalesAmountStat");
	}catch (Exception $e){
		$db->execute("rollback");
		$msg = $e->getMessage();
		logx($msg, $sid . "/SalesAmountStat");
		logx("业务员绩效统计失败", $sid . "/SalesAmountStat");
	}
}