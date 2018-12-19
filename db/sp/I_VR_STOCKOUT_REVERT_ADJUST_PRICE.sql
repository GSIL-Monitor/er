DROP PROCEDURE IF EXISTS `I_VR_STOCKOUT_REVERT_ADJUST_PRICE`;
DELIMITER $$
CREATE PROCEDURE `I_VR_STOCKOUT_REVERT_ADJUST_PRICE`(IN `P_StockoutID` INT, `P_WarehouseId` INT, `P_SrcOrderID` INT, `P_SrcOrderNO` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '销售出库驳回调价'
MAIN_LABEL:BEGIN
	DECLARE V_VoucherID,V_Count,V_ShopId,V_AccSalesStockoutGoodsCost,V_AccMerchandiseInventory INT DEFAULT(0);
	DECLARE V_VoucherNO, V_VoucherPeriod VARCHAR(40);
	
	SET @sys_code=0,@sys_message='OK';
	
	-- 看成本价有没变化
	SELECT COUNT(1) INTO V_Count FROM stockout_order_detail sod 
		LEFT JOIN stock_spec ss ON sod.spec_id=ss.spec_id AND ss.warehouse_id=P_WarehouseId 
	WHERE sod.stockout_id=P_StockoutId AND sod.cost_price<>ss.cost_price;
	IF V_Count=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT shop_id INTO V_ShopId FROM sales_trade WHERE trade_id=P_SrcOrderID;
	
	SET V_AccSalesStockoutGoodsCost = FN_DEFAULT_ACCOUNT("sales_stockout_goods_cost", V_ShopID);
	SET V_AccMerchandiseInventory = FN_DEFAULT_ACCOUNT("merchandise_inventory", 0);
	
	IF V_AccSalesStockoutGoodsCost=0 THEN
		SET @sys_code=4;
		SET @sys_message='没有给订单出库货品成本设置默认科目';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_AccMerchandiseInventory=0 THEN
		SET @sys_code=5;
		SET @sys_message='没有给库存商品设置默认科目';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 生成凭证
	SELECT DATE_FORMAT(NOW(), "%Y%m") INTO V_VoucherPeriod;
	SET V_VoucherNO = FN_SYS_NO("voucher");
	
	INSERT INTO fa_voucher(voucher_no, period_id, voucher_date, business_date, title, `type`, `status`, make_oper_id, check_oper_id, check_time,
		src_order_type, src_order_subtype, src_order_id, src_order_no, created)
	VALUES(V_VoucherNO, V_VoucherPeriod, NOW(), NOW(), '销售出库驳回成本调整', 0, 1, @cur_uid, @cur_uid, NOW(), 
		1, 1, P_SrcOrderID, P_SrcOrderNO, NOW());
	
	SELECT LAST_INSERT_ID() INTO V_VoucherID;
	
	INSERT INTO fa_voucher_detail(voucher_id, account_id, period_id, dr, cr, title, 
		obj_type, obj_id, obj_name, spec_id, shop_id, warehouse_id, created)
	SELECT V_VoucherID, V_AccSalesStockoutGoodsCost, V_VoucherPeriod, (sod.cost_price-ss.cost_price)*sod.num, 0,
		LEFT(CONCAT('销售驳回成本调整:',sod.goods_name),40), 
		0, 0, '', sod.spec_id, V_ShopId, P_WarehouseID, NOW()
	FROM stockout_order_detail sod 
		LEFT JOIN stock_spec ss ON sod.spec_id=ss.spec_id AND ss.warehouse_id=P_WarehouseID 
	WHERE sod.stockout_id=P_StockoutId AND sod.cost_price<>ss.cost_price;
	
	INSERT INTO fa_voucher_detail(voucher_id, account_id, period_id, dr, cr, title, 
		obj_type, obj_id, obj_name, spec_id, shop_id, warehouse_id, created)
	SELECT V_VoucherID, V_AccMerchandiseInventory, V_VoucherPeriod, 0, (sod.cost_price-ss.cost_price)*sod.num,
		LEFT(CONCAT('销售驳回库存商品调整:',sod.goods_name),40), 
		0, 0, '', sod.spec_id, V_ShopId, P_WarehouseID, NOW()
	FROM stockout_order_detail sod 
		LEFT JOIN stock_spec ss ON sod.spec_id=ss.spec_id AND ss.warehouse_id=P_WarehouseID 
	WHERE sod.stockout_id=P_StockoutId AND sod.cost_price<>ss.cost_price;
	
END$$
DELIMITER ;
