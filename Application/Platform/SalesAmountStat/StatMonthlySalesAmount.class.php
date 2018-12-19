<?php
function ReflashSalesMonthSell(&$db, $sid, &$msg = '') {
    try {
        //获取上次月统计时间作为本次统计开始时间
        $V_StatSalesDateBegin = getSysCfg($db, "cfg_statsales_date", 0);
        //如果上次统计时间为空，则将统计开始时间设为当前时间十天前
        if (!$V_StatSalesDateBegin) {
            $V_StatSalesDateBegin = date_create(date("Y-m-d"));
            date_add($V_StatSalesDateBegin, date_interval_create_from_date_string('-10 days'));
            $V_StatSalesDateBegin = date_format($V_StatSalesDateBegin, "Y-m-d G:i:s");
        }
        //本次统计结束时间
        $V_StatSalesDateEnd = date("Y-m-d");
        $V_StatSalesDateEnd = $V_StatSalesDateEnd . " 00:00:00";
        //月销售额统计
        $db->execute("START TRANSACTION");
        //从日统计结果中获取月统计数据并更新
        $sql = "INSERT INTO stat_monthly_sales_amount(sales_date,shop_id,warehouse_id,new_trades,new_trades_amount,check_trades,
		check_trades_amount,send_trades,send_trades_amount,send_goods_cost,send_trade_profit,send_unknown_goods_amount,post_amount,post_cost,commission,
		other_cost,package_cost,sales_drawback)
		SELECT DATE_FORMAT(sales_date,'%Y-%m'),shop_id,warehouse_id,new_trades,new_trades_amount,check_trades,
		check_trades_amount,send_trades,send_trades_amount,send_goods_cost,
		send_trade_profit,send_unknown_goods_amount,post_amount,post_cost,commission,other_cost,package_cost,sales_drawback
		FROM stat_daily_sales_amount
		WHERE sales_date >= '{$V_StatSalesDateBegin}' AND sales_date < '{$V_StatSalesDateEnd}'
		ON DUPLICATE KEY
		UPDATE stat_monthly_sales_amount.new_trades = stat_monthly_sales_amount.new_trades + VALUES(new_trades),
		stat_monthly_sales_amount.new_trades_amount = stat_monthly_sales_amount.new_trades_amount + VALUES(new_trades_amount),
		stat_monthly_sales_amount.check_trades = stat_monthly_sales_amount.check_trades + VALUES(check_trades),
		stat_monthly_sales_amount.check_trades_amount = stat_monthly_sales_amount.check_trades_amount + VALUES(check_trades_amount),
		stat_monthly_sales_amount.send_trades = stat_monthly_sales_amount.send_trades +VALUES(send_trades),
		stat_monthly_sales_amount.send_trades_amount = stat_monthly_sales_amount.send_trades_amount + VALUES(send_trades_amount),
		stat_monthly_sales_amount.send_goods_cost = stat_monthly_sales_amount.send_goods_cost + VALUES(send_goods_cost),
		stat_monthly_sales_amount.send_trade_profit = stat_monthly_sales_amount.send_trade_profit + VALUES(send_trade_profit),
		stat_monthly_sales_amount.send_unknown_goods_amount = stat_monthly_sales_amount.send_unknown_goods_amount + VALUES(send_unknown_goods_amount),
		stat_monthly_sales_amount.post_amount = stat_monthly_sales_amount.post_amount + VALUES(post_amount),
		stat_monthly_sales_amount.post_cost = stat_monthly_sales_amount.post_cost + VALUES(post_cost),
		stat_monthly_sales_amount.commission = stat_monthly_sales_amount.commission + VALUES(commission),
		stat_monthly_sales_amount.other_cost = stat_monthly_sales_amount.other_cost + VALUES(other_cost),
		stat_monthly_sales_amount.package_cost = stat_monthly_sales_amount.package_cost + VALUES(package_cost),
		stat_monthly_sales_amount.sales_drawback = stat_monthly_sales_amount.sales_drawback + VALUES(sales_drawback);";
        $db->execute($sql);
        //更新统计时间
        $sql = "INSERT INTO cfg_setting(`key`,`value`,`class`)
			VALUES('cfg_statsales_date',CURDATE(),'system')
			ON DUPLICATE KEY UPDATE `value` = CURDATE();";
        $db->execute($sql);
        $db->execute("commit");
        logx("销售月统计成功", $sid . "/SalesAmountStat");
    } catch (\Exception $e) {
        $db->execute("rollback");
        $msg = $e->getMessage();
        logx($msg, $sid . "/SalesAmountStat");
        logx("销售月统计失败", $sid . "/SalesAmountStat");
    }
}