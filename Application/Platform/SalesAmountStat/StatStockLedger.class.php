<?php
function RefreshStatStockLedger(&$db, $sid, &$msg = ""){
	//获取上次统计日期，如果日期为0，则设为当前时间统计日期前10天
	$V_StatSalesTimeBegin = getSysCfg($db, "cfg_stat_stock_ledger_date", 0);
	if (!$V_StatSalesTimeBegin) {
		$V_StatSalesTimeBegin = date_create(date("Y-m-d"));
		date_add($V_StatSalesTimeBegin, date_interval_create_from_date_string('-10 days'));
		$V_StatSalesTimeBegin = date_format($V_StatSalesTimeBegin, "Y-m-d G:i:s");
	}
	//在统计之前设定初始时间为上次统计时间
	$V_CurDateTime = $V_StatSalesTimeBegin;

	//统计结束时间
	$V_StatSalesDateEnd = date("Y-m-d", strtotime('-1 days'));
	$V_StatSalesTimeEnd = $V_StatSalesDateEnd . " 23:59:59";
	try{
		if ($V_CurDateTime < $V_StatSalesTimeEnd) {
			$db->execute("START TRANSACTION");
			//整理入库单数据，导入
			$get_in_data_sql="insert into stock_ledger(spec_id,warehouse_id,type,src_order_type,num,cost,created)
select sod.spec_id,so.warehouse_id,1 as type,so.src_order_type,sum(sod.num) as num, sum(sod.total_cost) as cost,sod.created from stockin_order_detail sod left join stockin_order so on so.stockin_id=sod.stockin_id where so.status=80 and sod.modified>='{$V_CurDateTime}' and sod.modified<'{$V_StatSalesTimeEnd}' group by date(sod.created),so.warehouse_id,sod.spec_id,so.src_order_type;";
			//整理出库单数据，导入
			$get_out_data_sql="insert into stock_ledger(spec_id,warehouse_id,type,src_order_type,num,cost,money,created)
select sod.spec_id,so.warehouse_id,2 as type,so.src_order_type,sum(sod.num) as num, sum(sod.total_amount) as cost,sod.cost_price as money,sod.created from stockout_order_detail sod left join stockout_order so on so.stockout_id=sod.stockout_id where so.status>=95 and sod.modified>='{$V_CurDateTime}' and sod.modified<'{$V_StatSalesTimeEnd}' group by date(sod.created),so.warehouse_id,sod.spec_id,so.src_order_type;";
			$db->execute($get_in_data_sql);
			$db->execute($get_out_data_sql);
			//更新统计时间
			$db->execute("INSERT INTO cfg_setting (`key`,`value`,`class`)
	                        VALUES('cfg_stat_stock_ledger_date',CURDATE(),'system')
	                        ON DUPLICATE KEY
	                        UPDATE `value` = VALUES(`value`)");
			$db->execute("COMMIT");
		}
		logx("库存台账统计成功", $sid . "/SalesAmountStat");
	}catch (Exception $e) {
		$db->execute("rollback");
		$msg = $e->getMessage();
		logx($msg, $sid . "/SalesAmountStat");
		logx("库存台账统计失败", $sid . "/SalesAmountStat");
	}




}