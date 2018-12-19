<?php
function RefreshStatPerSales(&$db, $sid, &$msg = ""){
    //获取上次统计日期，如果日期为0，则设为当前时间统计日期前10天
    $V_StatSalesTimeBegin = getSysCfg($db, "cfg_statsales_per_spec_date", 0);
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
            //创建退换临时表
            $creTmpSql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sales_refund_amount(
		                  refund_id INT(11) NOT NULL DEFAULT 0,
		                  shop_id INT(11) NOT NULL DEFAULT 0,
		                  TYPE TINYINT(4) NOT NULL DEFAULT 0,
		                  warehouse_id INT(11) NOT NULL DEFAULT 0,
		                  refund_total_amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
		                  refund_amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
		                  guarante_refund_amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
		                  sales_date DATE NOT NULL DEFAULT '0000-00-00',
		                  PRIMARY KEY(refund_id)
	                      )ENGINE = MEMORY,DEFAULT CHARSET =utf8";
            $db->execute($creTmpSql);
            //创建临时表时清下表里数据
            $db->execute("DELETE FROM tmp_sales_refund_amount");
            $db->execute("START TRANSACTION");
            //按照已发货、店铺来统计
            //更新销售数量，赠品数量，退款数量，销售金额，退款金额，赠品金额，邮费收入
            /**
             * trade_type 1网店销售2线下零售3售后换货4批发业务
             * commission 佣金成本
            */
            $sql = "INSERT INTO
            stat_daily_sales_spec_shop
            (sales_date,shop_id,spec_id,num,gift_num,amount,unknown_goods_amount,commission,goods_cost,gift_cost,
            post_amount,swap_num,swap_amount)
            SELECT slt.sales_date,slt.shop_id,slt.spec_id,slt.num,slt.gift_num,slt.amount,slt.unknown_goods_amount,slt.commission,slt.goods_cost,slt.gift_cost,
            slt.post_amount,slt.swap_num,slt.swap_amount
            FROM (
                SELECT DATE(so.consign_time) sales_date,st.shop_id,sto.spec_id,sto.trade_id,sto.platform_id,sto.src_oid,
                sto.suite_id,sto.flag,sto.num,IF(sto.gift_type,sto.actual_num,0) AS gift_num,
                IF(sto.num>0 && sto.actual_num = 0,sto.num,0) refund_num,IF((sod.is_allow_zero_cost=0 AND sod.is_package=0 AND (IF(sod.cost_price=0.0 OR sod.cost_price IS NULL,0.0,sod.cost_price)=0.0)),sod.price*sod.num,0) unknown_goods_amount,
                IF(sto.platform_id>0,sto.commission,0) commission,
                sto.share_amount AS amount,
                IF(sto.num>0 && sto.actual_num=0,sto.share_price*sto.num,0) refund_amount,
                IF(sto.gift_type,sod.cost_price*sod.num,0) gift_cost,
                sod.cost_price*sod.num AS goods_cost,sto.share_post AS post_amount,
                IF(st.trade_type=3,sto.actual_num,0) swap_num,IF(st.trade_type=3,sto.share_amount,0) swap_amount
                FROM stockout_order so
                LEFT JOIN sales_trade st ON st.trade_id = so.`src_order_id` AND so.src_order_type = 1
                LEFT JOIN sales_trade_order sto ON st.`trade_id` = sto.`trade_id`
                LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.`stockout_id`
                WHERE so.src_order_type = 1 AND so.`status`>=95 AND so.warehouse_type <>127 AND sod.`src_order_detail_id` = sto.`rec_id`
                AND so.consign_time>='{$V_StatSalesTimeBegin}' AND so.consign_time <= '{$V_StatSalesTimeEnd}'
                ORDER BY DATE(so.consign_time)) slt

                ON DUPLICATE KEY
                UPDATE stat_daily_sales_spec_shop.`num` = stat_daily_sales_spec_shop.`num` + VALUES(num),
                stat_daily_sales_spec_shop.`gift_num` = stat_daily_sales_spec_shop.`gift_num` +VALUES(gift_num),
                stat_daily_sales_spec_shop.`amount` = stat_daily_sales_spec_shop.`amount` + VALUES(amount),
                stat_daily_sales_spec_shop.`unknown_goods_amount` = stat_daily_sales_spec_shop.`unknown_goods_amount` + VALUES(unknown_goods_amount),
                stat_daily_sales_spec_shop.`commission` = stat_daily_sales_spec_shop.`commission` + VALUES(commission),
                stat_daily_sales_spec_shop.`gift_cost` = stat_daily_sales_spec_shop.`gift_cost` + VALUES(gift_cost),
                stat_daily_sales_spec_shop.`goods_cost` = stat_daily_sales_spec_shop.`goods_cost` + VALUES(goods_cost),
                stat_daily_sales_spec_shop.`post_amount` = stat_daily_sales_spec_shop.`post_amount` + VALUES(post_amount),
                stat_daily_sales_spec_shop.`swap_num` = stat_daily_sales_spec_shop.`swap_num` + VALUES(swap_num),
                stat_daily_sales_spec_shop.`swap_amount` = stat_daily_sales_spec_shop.`swap_amount` + VALUES(swap_amount)";
                $db->execute($sql);
            //按照已发货、仓库来统计
            //更新销售数量，赠品数量，退款数量，销售金额，退款金额，赠品金额，邮费收入
            $sql = "INSERT INTO stat_daily_sales_spec_warehouse(sales_date,warehouse_id,spec_id,num,gift_num,amount,unknown_goods_amount,commission,goods_cost,gift_cost,post_amount,swap_num,swap_amount)
			SELECT slt.sales_date,slt.warehouse_id,slt.spec_id,slt.num,slt.gift_num,slt.amount,slt.unknown_goods_amount,slt.commission,slt.goods_cost,slt.gift_cost,slt.post_amount,swap_num,swap_amount
            FROM (
                SELECT DATE(so.consign_time) sales_date,st.shop_id,sto.spec_id,sto.trade_id,sto.platform_id,sto.src_oid,
                sto.suite_id,sto.flag,sto.num,IF(sto.gift_type,sto.actual_num,0) AS gift_num,
                IF(sto.num>0 && sto.actual_num = 0,sto.num,0) refund_num,IF((sod.is_allow_zero_cost=0 AND sod.is_package=0 AND (IF(sod.cost_price=0.0 OR sod.cost_price IS NULL,0.0,sod.cost_price)=0.0)),sod.price*sod.num,0) unknown_goods_amount,
                IF(sto.platform_id>0,sto.commission,0) commission,
                sto.share_amount AS amount,IF(sto.num>0 && sto.actual_num=0,sto.share_price*sto.num,0) refund_amount,
                IF(sto.gift_type,sod.cost_price*sod.num,0) gift_cost,
                sod.cost_price*sod.num AS goods_cost,sto.share_post AS post_amount,so.warehouse_id,
                IF(st.trade_type=3,sto.actual_num,0) swap_num,IF(st.trade_type=3,sto.share_amount,0) swap_amount
                FROM stockout_order so
                LEFT JOIN sales_trade st ON st.trade_id = so.`src_order_id` AND so.src_order_type = 1
                LEFT JOIN sales_trade_order sto ON st.`trade_id` = sto.`trade_id`
                LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.`stockout_id`
                WHERE so.src_order_type = 1 AND so.`status`>=95 AND so.warehouse_type <>127 AND sod.`src_order_detail_id` = sto.`rec_id`
                 AND so.consign_time>='{$V_StatSalesTimeBegin}' AND so.consign_time <= '{$V_StatSalesTimeEnd}'
                ORDER BY DATE(so.consign_time)) slt
			ON DUPLICATE KEY
			UPDATE stat_daily_sales_spec_warehouse.num = stat_daily_sales_spec_warehouse.num + VALUES(num),
			stat_daily_sales_spec_warehouse.`gift_num` = stat_daily_sales_spec_warehouse.`gift_num` +VALUES(gift_num),
			stat_daily_sales_spec_warehouse.`amount` = stat_daily_sales_spec_warehouse.`amount` + VALUES(amount),
			stat_daily_sales_spec_warehouse.`unknown_goods_amount` = stat_daily_sales_spec_warehouse.`unknown_goods_amount` + VALUES(unknown_goods_amount),
			stat_daily_sales_spec_warehouse.`commission` = stat_daily_sales_spec_warehouse.`commission` + VALUES(commission),
			stat_daily_sales_spec_warehouse.`gift_cost` = stat_daily_sales_spec_warehouse.`gift_cost` + VALUES(gift_cost),
			stat_daily_sales_spec_warehouse.`goods_cost` = stat_daily_sales_spec_warehouse.`goods_cost` + VALUES(goods_cost),
			stat_daily_sales_spec_warehouse.`post_amount` = stat_daily_sales_spec_warehouse.`post_amount` + VALUES(post_amount),
			stat_daily_sales_spec_warehouse.`swap_num` = stat_daily_sales_spec_warehouse.`swap_num` + VALUES(swap_num),
			stat_daily_sales_spec_warehouse.`swap_amount` = stat_daily_sales_spec_warehouse.`swap_amount` + VALUES(swap_amount)";

            $db->execute($sql);
            //查找已同意的退换单日志得到对应的单据
            //如果多次同意会造成重复,去重data=0判断
            $refSql = "INSERT INTO tmp_sales_refund_amount(sales_date,refund_id,TYPE,refund_total_amount,shop_id,warehouse_id,refund_amount,guarante_refund_amount)
		    (
			SELECT DATE(srl.created) sales_date,sr.refund_id,sr.type,SUM(sro.total_amount) refund_total_amount,sr.shop_id,warehouse_id,sr.refund_amount,sr.guarante_refund_amount
			FROM sales_refund_log srl
			LEFT JOIN sales_refund_order sro ON sro.refund_id = srl.refund_id
			LEFT JOIN sales_refund sr ON sr.refund_id = sro.refund_id
			WHERE srl.type = 2 AND srl.data = 0 AND srl.created>='{$V_StatSalesTimeBegin}' AND srl.created<='{$V_StatSalesTimeEnd}' AND sr.warehouse_type <> 127
			GROUP BY sr.refund_id
		    )";
            $db->execute($refSql);
            //更新退款量，退款金额，退货金额(加的也就是说是正的)
            $sql = "INSERT INTO stat_daily_sales_spec_shop(sales_date,shop_id,spec_id,refund_num,refund_amount,return_amount,return_num,return_cost,guarante_refund_amount)
		    (
			SELECT DATE(tsra.sales_date) sales_trade,tsra.shop_id,sro.spec_id,IF(tsra.type=1,sro.refund_num,0) AS refund_num,IF(tsra.type = 1,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),0) refund_amount,
			IF(tsra.type = 4,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),IF(tsra.type = 2 OR tsra.type = 3,sro.total_amount,0)) AS return_amount,
			IF(tsra.type = 2 OR tsra.type = 3,sro.refund_num,0 ) return_num,
			IF(tsra.type = 2 OR tsra.type = 3 , sro.cost_price * sro.refund_num,0) return_cost,tsra.guarante_refund_amount
				FROM sales_refund_order sro, tmp_sales_refund_amount tsra
			    WHERE tsra.refund_id = sro.refund_id
		    )ON DUPLICATE KEY
		    UPDATE  stat_daily_sales_spec_shop.refund_num = stat_daily_sales_spec_shop.refund_num + VALUES(refund_num),
			        stat_daily_sales_spec_shop.refund_amount = stat_daily_sales_spec_shop.refund_amount + VALUES(refund_amount),
			        stat_daily_sales_spec_shop.return_amount = stat_daily_sales_spec_shop.return_amount + VALUES(return_amount),
			        stat_daily_sales_spec_shop.return_num = stat_daily_sales_spec_shop.return_num + VALUES(return_num),
			        stat_daily_sales_spec_shop.return_cost = stat_daily_sales_spec_shop.return_cost + VALUES(return_cost),
			        stat_daily_sales_spec_shop.guarante_refund_amount = stat_daily_sales_spec_shop.guarante_refund_amount + VALUES(guarante_refund_amount)";
            $db->execute($sql);
            // 更新仓库的统计
            $sql = "INSERT INTO stat_daily_sales_spec_warehouse(sales_date,warehouse_id,spec_id,refund_num,refund_amount,return_amount,return_num,return_cost,guarante_refund_amount)
		(
			SELECT DATE(tsra.sales_date) sales_trade,tsra.warehouse_id,sro.spec_id,IF(tsra.type=1,sro.refund_num,0) AS refund_num,IF(tsra.type = 1,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),0) refund_amount,
				IF(tsra.type = 4,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),IF(tsra.type = 2 OR tsra.type = 3,sro.total_amount,0)) AS return_amount, IF(tsra.type = 2 OR tsra.type = 3,sro.refund_num,0 ) return_num,
				IF(tsra.type = 2 OR tsra.type = 3,sro.cost_price*sro.refund_num,0) return_cost,tsra.guarante_refund_amount
				FROM sales_refund_order sro, tmp_sales_refund_amount tsra 
			WHERE tsra.refund_id = sro.refund_id
		)ON DUPLICATE KEY
		UPDATE stat_daily_sales_spec_warehouse.refund_num = stat_daily_sales_spec_warehouse.refund_num + VALUES(refund_num),
			   stat_daily_sales_spec_warehouse.refund_amount = stat_daily_sales_spec_warehouse.refund_amount + VALUES(refund_amount),
			   stat_daily_sales_spec_warehouse.return_amount = stat_daily_sales_spec_warehouse.return_amount + VALUES(return_amount),
			   stat_daily_sales_spec_warehouse.return_num = stat_daily_sales_spec_warehouse.return_num + VALUES(return_num),
			   stat_daily_sales_spec_warehouse.return_cost = stat_daily_sales_spec_warehouse.return_cost + VALUES(return_cost),
			   stat_daily_sales_spec_warehouse.guarante_refund_amount = stat_daily_sales_spec_warehouse.guarante_refund_amount + VALUES(guarante_refund_amount);";
            $db->execute($sql);
            //删除临时表数据
            $db->execute("DELETE FROM tmp_sales_refund_amount");
            // 已同意的被驳回减去
            $sql = "
            INSERT INTO tmp_sales_refund_amount(refund_id,sales_date,TYPE,refund_total_amount,shop_id,warehouse_id,refund_amount,guarante_refund_amount)
            (
                SELECT sr.refund_id,DATE(srl.created) AS sales_date,sr.type,SUM(sro.total_amount) refund_total_amount,sr.shop_id,sr.warehouse_id,sr.refund_amount,sr.guarante_refund_amount
                FROM sales_refund_log srl
                LEFT JOIN sales_refund_order sro ON sro.refund_id = srl.refund_id
                LEFT JOIN sales_refund sr ON sr.refund_id = sro.refund_id
                WHERE srl.type = 8 AND srl.data = 0 AND srl.created>='{$V_StatSalesTimeBegin}' AND srl.created<='{$V_StatSalesTimeEnd}' AND sr.warehouse_type <> 127
                AND sr.process_status < 30
                GROUP BY sr.refund_id
            )";
            $db->execute($sql);
            $sql = "INSERT INTO stat_daily_sales_spec_shop(sales_date,shop_id,spec_id,refund_num,refund_amount,return_amount,return_num,return_cost,guarante_refund_amount)
		    (
			SELECT DATE(tsra.sales_date) sales_trade,tsra.shop_id,sro.spec_id,-IF(tsra.type=1,sro.refund_num,0) AS refund_num,-IF(tsra.type = 1,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),0) refund_amount,
				-IF(tsra.type =4,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),-IF(tsra.type = 2 OR tsra.type = 3 ,sro.total_amount,0)) AS return_amount,-IF(tsra.type = 2 OR tsra.type = 3,sro.refund_num,0 ) return_num,
				-IF(tsra.type = 2 OR tsra.type = 3, sro.refund_num*sro.cost_price,0) return_cost,-(tsra.guarante_refund_amount) AS guarante_refund_amount
				FROM sales_refund_order sro, tmp_sales_refund_amount tsra
			    WHERE tsra.refund_id = sro.refund_id
		    )ON DUPLICATE KEY
		    UPDATE  stat_daily_sales_spec_shop.refund_num = stat_daily_sales_spec_shop.refund_num + VALUES(refund_num),
			        stat_daily_sales_spec_shop.refund_amount = stat_daily_sales_spec_shop.refund_amount + VALUES(refund_amount),
			        stat_daily_sales_spec_shop.return_amount = stat_daily_sales_spec_shop.return_amount + VALUES(return_amount),
			        stat_daily_sales_spec_shop.return_num = stat_daily_sales_spec_shop.return_num + VALUES(return_num),
			        stat_daily_sales_spec_shop.return_cost = stat_daily_sales_spec_shop.return_cost + VALUES(return_cost),
			        stat_daily_sales_spec_shop.guarante_refund_amount = stat_daily_sales_spec_shop.guarante_refund_amount + VALUES(guarante_refund_amount)";
            $db->execute($sql);
            $sql = "INSERT INTO stat_daily_sales_spec_warehouse(sales_date,warehouse_id,spec_id,refund_num,refund_amount,return_amount,return_num,return_cost,guarante_refund_amount)
		(
			SELECT DATE(tsra.sales_date) sales_trade,tsra.warehouse_id,sro.spec_id,-IF(tsra.type=1,sro.refund_num,0) AS refund_num,-IF(tsra.type = 1,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),0) refund_amount,
				-IF(tsra.type =4,IF(tsra.refund_total_amount>0,tsra.refund_amount*sro.total_amount/tsra.refund_total_amount,0),-IF(tsra.type = 2 OR tsra.type = 3 ,sro.total_amount,0)) AS return_amount,-IF(tsra.type = 2 OR tsra.type = 3,sro.refund_num,0 ) return_num,
				-IF(tsra.type = 2 OR tsra.type = 3, sro.refund_num*sro.cost_price,0) return_cost,-(tsra.guarante_refund_amount) AS guarante_refund_amount
				FROM sales_refund_order sro, tmp_sales_refund_amount tsra 
			WHERE tsra.refund_id = sro.refund_id
		)ON DUPLICATE KEY
		UPDATE stat_daily_sales_spec_warehouse.refund_num = stat_daily_sales_spec_warehouse.refund_num + VALUES(refund_num),
			   stat_daily_sales_spec_warehouse.refund_amount = stat_daily_sales_spec_warehouse.refund_amount + VALUES(refund_amount),
			   stat_daily_sales_spec_warehouse.return_amount = stat_daily_sales_spec_warehouse.return_amount + VALUES(return_amount),
			   stat_daily_sales_spec_warehouse.return_num = stat_daily_sales_spec_warehouse.return_num + VALUES(return_num),
			   stat_daily_sales_spec_warehouse.return_cost = stat_daily_sales_spec_warehouse.return_cost + VALUES(return_cost),
			   stat_daily_sales_spec_warehouse.guarante_refund_amount = stat_daily_sales_spec_warehouse.guarante_refund_amount + VALUES(guarante_refund_amount);";
            $db->execute($sql);
            //更新均价
            $sql = "UPDATE stat_daily_sales_spec_shop SET stat_daily_sales_spec_shop.avg_price = IF((stat_daily_sales_spec_shop.`num`-stat_daily_sales_spec_shop.`refund_num`)<>0, stat_daily_sales_spec_shop.amount/(stat_daily_sales_spec_shop.`num`-stat_daily_sales_spec_shop.`refund_num`),0)";
            $db->execute($sql);
            $sql = "UPDATE stat_daily_sales_spec_warehouse SET stat_daily_sales_spec_warehouse.avg_price =IF((stat_daily_sales_spec_warehouse.`num`-stat_daily_sales_spec_warehouse.`refund_num`)<>0, stat_daily_sales_spec_warehouse.amount/(stat_daily_sales_spec_warehouse.`num`-stat_daily_sales_spec_warehouse.`refund_num`),0);";
            $db->execute($sql);
            //更新配置表中上次统计的时间
            //$V_CurDateTime = $V_StatSalesTimeEnd;
            $db->execute("INSERT INTO cfg_setting (`key`,`value`,`class`)
	                        VALUES('cfg_statsales_per_spec_date',CURDATE(),'system')
	                        ON DUPLICATE KEY
	                        UPDATE `value` = VALUES(`value`)");
            //删除临时表数据
            $db->execute("DELETE FROM tmp_sales_refund_amount");
            //删除临时表
            $db->execute("DROP TEMPORARY TABLE tmp_sales_refund_amount");
            $db->execute("COMMIT");
        }
        logx("单品销售统计成功", $sid . "/SalesAmountStat");
    }catch (Exception $e) {
        $db->execute("rollback");
        $msg = $e->getMessage();
        logx($msg, $sid . "/SalesAmountStat");
        logx("单品销售统计失败", $sid . "/SalesAmountStat");
    }




}