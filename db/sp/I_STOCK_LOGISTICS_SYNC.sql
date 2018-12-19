
DROP PROCEDURE IF EXISTS I_STOCK_LOGISTICS_SYNC;
DELIMITER $$
CREATE PROCEDURE I_STOCK_LOGISTICS_SYNC(IN P_StockoutID INT)
	SQL SECURITY INVOKER 
	COMMENT 'wms物流同步'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_LogisticsId,V_LogisticsType,V_BillType,V_LogisticsNoStatus,V_SrcOrderType,V_SrcOrderId,
		V_ShopID,V_WarehouseID,V_DeliveryTerm,V_WarehouseType INT DEFAULT 0;
	DECLARE V_LogisticsNo VARCHAR(40);
	DECLARE V_CodAmount DECIMAL(19,4);
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	SELECT so.logistics_id,so.logistics_no,cl.logistics_type,cl.bill_type,so.src_order_type,so.src_order_id,so.warehouse_id,so.warehouse_type
		INTO V_LogisticsId,V_LogisticsNo,V_LogisticsType,V_BillType,V_SrcOrderType,V_SrcOrderId,V_WarehouseID,V_WarehouseType
	FROM stockout_order so LEFT JOIN cfg_logistics cl ON cl.logistics_id=so.logistics_id
	WHERE  so.stockout_id=P_StockoutId ;
	
	SET @sys_code=0;
	SET @sys_message='OK';

	/*处理物流公司单号*/
	IF V_BillType=1 OR V_BillType=5 OR V_BillType=8 THEN
		IF V_LogisticsNo='' THEN
			SET @sys_code=1;
			SET @sys_message='物流单号不能为空';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF (V_LogisticsType=3 OR V_LogisticsType=4 OR V_LogisticsType=5 OR V_LogisticsType=1311 OR V_LogisticsType=1308 OR V_LogisticsType=1306 OR V_LogisticsType=16) THEN
			SELECT status INTO V_LogisticsNoStatus FROM stock_logistics_no
			WHERE logistics_type=V_LogisticsType AND logistics_no = V_LogisticsNo AND logistics_id=V_LogisticsId;
			
			IF V_NOT_FOUND<>0 THEN
				SET @sys_code=1;
				SET @sys_message='物流单号在单号池不存在';
				LEAVE MAIN_LABEL;
			ELSE
				/*  以后考虑单后使用校验
				IF V_LogisticsNoStatus = 2 THEN
					SET @sys_code=1;
					SET @sys_message='物流单号已使用';
					LEAVE MAIN_LABEL;
				ELSE
					UPDATE stock_logistics_no SET status=2 WHERE logistics_id=V_LogisticsId AND logistics_no = V_LogisticsNo;
				END IF;
				*/
				UPDATE stock_logistics_no SET status=2 WHERE logistics_type=V_LogisticsType AND logistics_no = V_LogisticsNo;
			END IF;	
			
			IF V_SrcOrderType=1 THEN
				SELECT shop_id,delivery_term,cod_amount INTO V_ShopID,V_DeliveryTerm,V_CodAmount FROM sales_trade WHERE trade_id=V_SrcOrderId;
			ELSE
				SET V_DeliveryTerm=1;
			END IF;
			
			REPLACE INTO stock_logistics_sync(stockout_id,logistics_id,logistics_type,logistics_no,
				shop_id,warehouse_id,delivery_term,cod_amount,created) 
			VALUES(P_StockoutId,V_LogisticsId,V_LogisticsType,V_LogisticsNo,V_ShopID,V_WarehouseID,V_DeliveryTerm,V_CodAmount,NOW());
		ELSEIF V_SrcOrderType=1 THEN -- 销售订单
			CALL I_SALES_CREATE_LOGISTICS_SYNC(V_SrcOrderId, P_StockoutId, 0);
		END IF;
	ELSEIF V_SrcOrderType=1 THEN -- 销售订单
		CALL I_SALES_CREATE_LOGISTICS_SYNC(V_SrcOrderId, P_StockoutId, 0);
	END IF;
	
	-- 更新预支多物流单号状态
	UPDATE stock_logistics_no sln,cfg_logistics cl SET sln.`status`=2 WHERE sln.logistics_id=cl.logistics_id AND cl.bill_type=1 AND (cl.logistics_type=3 OR cl.logistics_type=4 OR cl.logistics_type=5 OR cl.logistics_type=1311) AND sln.stockout_id = P_StockoutId AND sln.`status` = 1 AND sln.`type` = 1;
	REPLACE INTO stock_logistics_sync(stockout_id,logistics_id,logistics_type,logistics_no,shop_id,warehouse_id,created) 
	SELECT P_StockoutId,sln.logistics_id,sln.logistics_type,sln.logistics_no,V_ShopID,V_WarehouseID,NOW()
	FROM stock_logistics_no sln
	LEFT JOIN cfg_logistics cl ON sln.logistics_id=cl.logistics_id
	WHERE cl.bill_type=1 AND (cl.logistics_type=3 OR cl.logistics_type=4 OR cl.logistics_type=5 OR cl.logistics_type=1311) AND sln.status = 2 AND sln.stockout_id = P_StockoutId AND sln.type=1;

END$$
DELIMITER ;
