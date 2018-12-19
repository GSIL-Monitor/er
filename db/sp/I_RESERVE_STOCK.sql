DROP PROCEDURE IF EXISTS `I_RESERVE_STOCK`;
DELIMITER //
CREATE PROCEDURE `I_RESERVE_STOCK`(IN `P_TradeID` INT, IN `P_Type` INT, IN `P_NewWarehouseID` INT, IN `P_OldWarehouseID` INT)
    SQL SECURITY INVOKER
    COMMENT '占用库存'
MAIN_LABEL:BEGIN
	IF P_OldWarehouseID THEN
		INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
		(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'释放库存' FROM sales_trade_order sto 
		LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=P_OldWarehouseID WHERE sto.stock_reserved>=2 AND sto.trade_id=P_TradeID);
	
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT P_OldWarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
			IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
			sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
		
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=P_TradeID;
	END IF;
	IF P_NewWarehouseID THEN
		IF P_Type = 2 THEN	-- 未付款库存
			INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
			(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,1,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待付款库存' FROM sales_trade_order sto 
			 LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=P_NewWarehouseID WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.stock_reserved<2);
			
			INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num);
			
			UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 3 THEN	-- 已保留待审核
			INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
			(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,2,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待审核库存' FROM sales_trade_order sto 
			 LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=P_NewWarehouseID WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.stock_reserved<2);

			INSERT INTO stock_spec(warehouse_id,spec_id,order_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num 
			FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num);
			
			UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 4 THEN	-- 待发货
			INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
			(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,3,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待发货库存' FROM sales_trade_order sto 
			 LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=P_NewWarehouseID WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.stock_reserved<2);

			INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status)
			(SELECT P_NewWarehouseID,spec_id,actual_num,1 FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1;
			
			UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 5 THEN	-- 预订单库存
			INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
			(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,4,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用预订单库存' FROM sales_trade_order sto 
			 LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=P_NewWarehouseID WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.stock_reserved<2);

			INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num);
			
			UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		END IF;
	END IF;
	
	-- 更新平台货品库存变化
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0;
	
	-- 组合装
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
	
END//
DELIMITER ;