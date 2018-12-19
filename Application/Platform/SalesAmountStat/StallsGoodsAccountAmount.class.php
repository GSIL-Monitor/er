<?php
function ReflashStallsGoods(&$db, $sid, &$msg = "") {
    //获取上次统计日期，如果日期为0，则设为当前时间统计日期前10天
    $V_StatSalesTimeBegin = getSysCfg($db, "cfg_stalls_goods_date_time", 0);
    if (!$V_StatSalesTimeBegin) {
        $V_StatSalesTimeBegin = date_create(date("Y-m-d"));
        date_add($V_StatSalesTimeBegin, date_interval_create_from_date_string('-10 days'));
        $V_StatSalesTimeBegin = date_format($V_StatSalesTimeBegin, "Y-m-d G:i:s");
    }
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
			(SELECT stockin_time AS created FROM stalls_less_goods_detail slgd
			LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = slgd.warehouse_id
			WHERE slgd.stockin_status=1  AND slgd.stockin_time > '{$V_CurDateTime}' AND slgd.stockin_time < '{$V_StatSalesTimeEnd}' AND cw.type != 127 ORDER BY slgd.stockin_time LIMIT 5000) stl
			ORDER BY stl.created DESC LIMIT 1";
            $V_MaxDateTime = $db->multi_query($sql);
            $V_MaxDateTime = $db->fetch_array($V_MaxDateTime)["created"];
            //如果数据未查到……
            if (!$V_MaxDateTime || $V_CurDateTime == $V_MaxDateTime) {
                $V_MaxDateTime = $V_StatSalesTimeEnd;
            }

            $sql = "INSERT INTO stat_stalls_goods_amount(sales_date,goods_id,spec_id,provider_id,num,price,warehouse_id)
				SELECT slt.sales_date,slt.goods_id,slt.spec_id,slt.provider_id,slt.num,slt.price,slt.warehouse_id
				FROM
					(SELECT DATE(sgd.stockin_time) AS sales_date,sgd.spec_id,gs.goods_id,sgd.provider_id,
					SUM(sgd.num) AS num,SUM(sgd.num*sgd.price) AS price,sgd.warehouse_id
					FROM stalls_less_goods_detail sgd
					LEFT JOIN goods_spec gs ON gs.spec_id= sgd.spec_id
					LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
					WHERE sgd.stockin_status=1 AND sgd.stockin_time >= '{$V_CurDateTime}'
					AND sgd.stockin_time < '{$V_MaxDateTime}' AND cw.type != 127 GROUP BY sgd.spec_id,sales_date) slt
				ON DUPLICATE KEY
				UPDATE stat_stalls_goods_amount.num = stat_stalls_goods_amount.num + VALUES(num),
				stat_stalls_goods_amount.price = stat_stalls_goods_amount.price + VALUES(price)";
            $db->execute($sql);
            $V_CurDateTime = $V_MaxDateTime;
            //更新数据库上次日统计时间

            $sql = "INSERT INTO cfg_setting(`key`,`value`,`class`) VALUES('cfg_stalls_goods_date_time','{$V_MaxDateTime}','system')
				ON DUPLICATE KEY UPDATE `value` = '{$V_MaxDateTime}'";
            $db->execute($sql);
            $db->execute("commit");
        }
        logx("档口货品列表统计成功", $sid . "/SalesAmountStat");
    } catch (\Exception $e) {
        $db->execute("rollback");
        $msg = $e->getMessage();
        logx($msg, $sid . "/SalesAmountStat");
        logx("档口货品列表统计失败", $sid . "/SalesAmountStat");
    }
}