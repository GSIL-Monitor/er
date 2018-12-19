DROP PROCEDURE IF EXISTS I_DL_AUTO_WMS_CONSIGN;
DELIMITER //
CREATE PROCEDURE I_DL_AUTO_WMS_CONSIGN(IN P_TradeID INT,IN P_OperatorID INT)
    SQL SECURITY INVOKER
    COMMENT '自动流入委外的订单自动发货'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_TradeStatus,V_PlatformID,V_StockoutID,V_LogisticsType,V_LogisticsID,V_ShopID,
			V_WarehouseID,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_IsAutoWms INT DEFAULT(0);
	DECLARE V_LogisticsNo,V_AreaAlias,V_OuterNo,V_StockoutNO VARCHAR(40) DEFAULT '';
	DECLARE V_SrcTids,V_LogisticsFeeLog,V_AreaAliasLog VARCHAR(255) DEFAULT '';
	DECLARE V_PostCost,V_TotalWeight DECIMAL(19,4) DEFAULT '0.00';
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	SET @sys_code=0;
	SELECT trade_status,platform_id,src_tids,shop_id,warehouse_id,receiver_province,receiver_city,receiver_district,weight
	INTO V_TradeStatus,V_PlatformID,V_SrcTids,V_ShopID,V_WarehouseID,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_TotalWeight 
	FROM sales_trade WHERE trade_id=P_TradeID ;
	IF V_TradeStatus <> 21	OR V_NOT_FOUND THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	SELECT is_auto_wms,logistics_type,logistics_no,stockout_no INTO V_IsAutoWms,V_LogisticsType,V_LogisticsNo,V_OuterNo 
	FROM api_trade WHERE platform_id =V_PlatformID AND tid = V_SrcTids;
	IF V_NOT_FOUND OR V_IsAutoWms<>1 THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	-- 重新处理物流
	IF V_LogisticsType>1 AND V_LogisticsNo<>'' THEN
		SELECT logistics_id INTO V_LogisticsID FROM cfg_logistics WHERE logistics_type = V_LogisticsType LIMIT 1;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
		END IF;
		IF V_LogisticsID >0 THEN
			-- 估算邮费
			CALL SP_UTILS_GET_CFG('logistics_match_mode', @cfg_logistics_match_mode, 0);
		  CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID, 
		0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsFeeLog);
			-- 大头笔
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
			UPDATE sales_trade SET logistics_id = V_LogisticsID,logistics_no = V_LogisticsNO,post_cost = V_PostCost,profit = receivable-goods_cost-post_cost-commission,
			receiver_dtb = V_AreaAlias WHERE trade_id = P_TradeID;
		END IF;
	ELSE
		-- 估算邮费
		CALL SP_UTILS_GET_CFG('logistics_match_mode', @cfg_logistics_match_mode, 0);
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID, 
			0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsFeeLog);
		UPDATE sales_trade SET logistics_no = V_LogisticsNO,post_cost = V_PostCost,profit = receivable-goods_cost-post_cost-commission WHERE trade_id = P_TradeID;
	END IF;
	
	-- 生成出库单 
	SET V_StockoutNO = FN_SYS_NO('stockout');
	INSERT INTO stockout_order(stockout_no,src_order_type,src_order_id,src_order_no,`status`,warehouse_type,warehouse_id,customer_id,
		goods_count,goods_type_count,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_address,
		receiver_mobile,receiver_telno,receiver_zip,receiver_area,receiver_dtb,to_deliver_time,logistics_id,logistics_no,package_id,
		goods_total_amount,goods_total_cost,calc_post_cost,calc_weight,has_invoice,operator_id,flag_id,outer_no,created)
	SELECT V_StockoutNO,1,trade_id,trade_no,55,warehouse_type,warehouse_id,customer_id,
		goods_count,goods_type_count,receiver_name,receiver_country,receiver_province,receiver_city,
		receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,receiver_area,receiver_dtb,to_deliver_time,
		logistics_id,logistics_no,package_id,goods_amount,goods_cost,post_cost,weight,invoice_type,P_OperatorID,flag_id,V_OuterNo,NOW()
	FROM sales_trade WHERE trade_id = P_TradeID;
	SET V_StockoutID = LAST_INSERT_ID();
	-- 生成出库单货品
	INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,num,price,total_amount,goods_name,
		goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,weight,is_allow_zero_cost,remark,created)
	SELECT V_StockoutID, 1,rec_id,actual_num,share_price,share_amount,goods_name,goods_id,goods_no,
		spec_name,spec_id,spec_no,spec_code,weight,is_allow_zero_cost,remark,NOW()
	FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num>0;
	UPDATE sales_trade set trade_status=55,stockout_no = V_StockoutNO,version_id = version_id+1 WHERE trade_id = P_TradeID;
	SET @cur_uid = P_OperatorID;
	-- 发货
	CALL I_STOCKOUT_SALES_CONSIGN(V_StockoutID,1,1);
END//
DELIMITER ;
