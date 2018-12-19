<?php
function ReflashSalesDaySell(&$db, $sid, &$msg = "") {
    //获取上次统计日期，如果日期为0，则设为当前时间统计日期前10天
    $V_StatSalesTimeBegin = getSysCfg($db, "cfg_statsales_date_time", 0);
    //获取配置  订单毛利是否扣减零成本
    $stat_unknow_goods_amount = getSysCfg($db,'stat_unknow_goods_amount',0);
    if (!$V_StatSalesTimeBegin) {
        $V_StatSalesTimeBegin = date_create(date("Y-m-d"));
        date_add($V_StatSalesTimeBegin, date_interval_create_from_date_string('-10 days'));
        $V_StatSalesTimeBegin = date_format($V_StatSalesTimeBegin, "Y-m-d G:i:s");
    }
    $V_StatSalesTimeBegin = $V_StatSalesTimeBegin;
    //在统计之前设定初始时间为上次统计时间
    $V_CurDateTime = $V_StatSalesTimeBegin;
    //月统计结束时间
    $V_StatSalesDateEnd = date("Y-m-d", time());
    //日统计结束时间
    $V_StatSalesTimeEnd = $V_StatSalesDateEnd . " 00:00:00";
    try {
        //开始日统计，每次统计5000条
        while ($V_CurDateTime < $V_StatSalesTimeEnd) {
            $db->execute("START TRANSACTION");
            //获取日志中前5000条在上次统计之后新建的数据，拿到其中最新创建数据的时间
            $sql           = "SELECT created FROM
			(SELECT created FROM sales_trade_log
			WHERE (`type`=3 OR `type` = 9 OR `type` = 10 OR `type` = 12 OR `type` = 105) AND created > '{$V_CurDateTime}' AND created < '{$V_StatSalesTimeEnd}'  AND `data` <> 99 ORDER BY created LIMIT 5000) stl
			ORDER BY stl.created DESC LIMIT 1";
            $V_MaxDateTime = $db->multi_query($sql);
            $V_MaxDateTime = $db->fetch_array($V_MaxDateTime)["created"];
            //如果数据未查到……
            if (!$V_MaxDateTime || $V_CurDateTime == $V_MaxDateTime) {
                $V_MaxDateTime = $V_StatSalesTimeEnd;
            }
            //先统计递交的订单
            $sql = "INSERT INTO stat_daily_sales_amount(sales_date,shop_id,warehouse_id,new_trades,new_trades_amount)
				SELECT slt.sales_date,slt.shop_id,slt.warehouse_id,slt.new_trades,slt.new_trades_amount
				FROM
					(SELECT DISTINCT DATE(stl.created) AS sales_date,st.shop_id,st.warehouse_id,st.trade_id,
					1 AS new_trades, st.receivable AS new_trades_amount
					FROM sales_trade_log stl
					INNER JOIN sales_trade st ON st.trade_id= stl.trade_id
					WHERE `type`=3 AND stl.created >= '{$V_CurDateTime}'
					AND stl.created < '{$V_MaxDateTime}'  AND `data` <> 99 ) slt
				ON DUPLICATE KEY
				UPDATE stat_daily_sales_amount.new_trades = stat_daily_sales_amount.new_trades + VALUES(new_trades),
				stat_daily_sales_amount.new_trades_amount = stat_daily_sales_amount.new_trades_amount + VALUES(new_trades_amount)";
            logx('deliver_sql:'.print_r($sql,true),$sid.'/SalesAmountStat');
            $db->execute($sql);
            //统计已审核订单
            $sql = "INSERT INTO stat_daily_sales_amount(sales_date,shop_id,warehouse_id,check_trades,check_trades_amount)
				SELECT slt.sales_date,slt.shop_id,slt.warehouse_id,slt.check_trades,slt.check_trades_amount
				FROM 
					(SELECT DISTINCT DATE(stl.created) AS sales_date,st.shop_id,st.warehouse_id,st.trade_id,
					1 AS check_trades, st.receivable AS check_trades_amount 
					FROM sales_trade_log stl
					INNER JOIN sales_trade st ON st.trade_id= stl.trade_id
					WHERE (`type` = 9 OR `type` = 10 OR `type` = 12) AND stl.created >= '{$V_CurDateTime}'
					AND stl.created < '{$V_MaxDateTime}' AND `data` <> 99) slt
				ON DUPLICATE KEY 
				UPDATE 
				stat_daily_sales_amount.check_trades = stat_daily_sales_amount.check_trades +VALUES(check_trades),
				stat_daily_sales_amount.check_trades_amount = stat_daily_sales_amount.check_trades_amount + VALUES(check_trades_amount)";
            logx('check_sql:'.print_r($sql,true),$sid.'/SalesAmountStat');
            $db->execute($sql);
            //统计邮资成本
            $sql = "INSERT INTO stat_daily_sales_amount(sales_date,shop_id,warehouse_id,send_trades,send_trades_amount,post_amount,
				send_trade_profit,send_unknown_goods_amount,commission,other_cost,send_goods_cost,post_cost,package_cost)
				SELECT slt.sales_date,slt.shop_id,slt.warehouse_id,slt.send_trades,slt.send_trades_amount,slt.post_amount,
				slt.send_trade_profit,slt.send_unknown_goods_amount,slt.commission,slt.other_cost,slt.send_goods_cost,slt.post_cost,slt.package_cost
				FROM
				(SELECT DISTINCT DATE(so.consign_time) AS sales_date,so.src_order_id,st.shop_id,st.warehouse_id,
				1 AS send_trades,st.receivable AS send_trades_amount,st.post_amount AS post_amount,
				IF($stat_unknow_goods_amount=0,st.receivable-so.goods_total_cost-so.post_cost-so.unknown_goods_amount,st.receivable-so.goods_total_cost-so.post_cost) AS send_trade_profit ,
				so.unknown_goods_amount AS send_unknown_goods_amount,st.commission,st.other_cost,so.goods_total_cost AS send_goods_cost,so.post_cost,so.package_cost
				FROM stockout_order so
				INNER JOIN sales_trade st ON st.trade_id = so.src_order_id AND st.trade_no = so.src_order_no
				WHERE so.consign_time>= '{$V_CurDateTime}' AND so.consign_time< '{$V_MaxDateTime}'
				AND so.src_order_type = 1 AND so.status >=95 ) slt
				ON DUPLICATE KEY
				UPDATE
				stat_daily_sales_amount.send_trades = stat_daily_sales_amount.send_trades + VALUES(send_trades),
				stat_daily_sales_amount.send_trades_amount = stat_daily_sales_amount.send_trades_amount + VALUES(send_trades_amount),
				stat_daily_sales_amount.post_amount = stat_daily_sales_amount.post_amount +VALUES(post_amount),
				stat_daily_sales_amount.send_trade_profit = stat_daily_sales_amount.send_trade_profit + VALUES(send_trade_profit),
				stat_daily_sales_amount.send_unknown_goods_amount = stat_daily_sales_amount.send_unknown_goods_amount + VALUES(send_unknown_goods_amount),
				stat_daily_sales_amount.commission = stat_daily_sales_amount.commission +VALUES(commission),
				stat_daily_sales_amount.other_cost = stat_daily_sales_amount.other_cost +VALUES(other_cost),
				stat_daily_sales_amount.`send_goods_cost` = stat_daily_sales_amount.`send_goods_cost` + VALUES(send_goods_cost),
				stat_daily_sales_amount.post_cost = stat_daily_sales_amount.post_cost + VALUES(post_cost),
				stat_daily_sales_amount.package_cost = stat_daily_sales_amount.package_cost + VALUES(package_cost)";
            logx('amount_sql:'.print_r($sql,true),$sid.'/SalesAmountStat');
            $db->execute($sql);
            $V_CurDateTime = $V_MaxDateTime;
            //更新数据库上次日统计时间
            $sql = "INSERT INTO cfg_setting(`key`,`value`,`class`) VALUES('cfg_statsales_date_time','{$V_MaxDateTime}','system')
				ON DUPLICATE KEY UPDATE `value` = '{$V_MaxDateTime}'";
            $db->execute($sql);
            $db->execute("commit");
        }
        logx("销售日统计成功", $sid . "/SalesAmountStat");
    } catch (\Exception $e) {
        $db->execute("rollback");
        $msg = $e->getMessage();
        logx($msg, $sid . "/SalesAmountStat");
        logx("销售日统计失败", $sid . "/SalesAmountStat");
    }
}