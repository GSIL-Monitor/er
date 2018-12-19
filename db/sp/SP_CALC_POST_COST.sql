
DROP PROCEDURE IF EXISTS `SP_CALC_POST_COST`;
DELIMITER //
CREATE PROCEDURE `SP_CALC_POST_COST`(out `P_PostCost` DECIMAL(19,4),in `P_StockoutId` int,in `P_Weight` decimal(19,4))
    SQL SECURITY INVOKER
    COMMENT '计算邮费'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ReceiverCountry,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_LogisticsId,V_WarehouseId,V_ShopId int default(0);
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SELECT so.warehouse_id,st.shop_id,so.logistics_id,so.receiver_country,so.receiver_province,so.receiver_city,so.receiver_district
	INTO V_WarehouseId,V_ShopId,V_LogisticsId,V_ReceiverCountry,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict
	FROM stockout_order so LEFT JOIN sales_trade st ON(st.trade_id=so.src_order_id AND so.src_order_type=1)
	WHERE so.stockout_id=P_StockoutId;
	
	IF V_NOT_FOUND<>0 THEN
		SET P_PostCost=0;
	END IF;
	
	CALL SP_UTILS_GET_CFG('logistics_match_mode', @cfg_logistics_match_mode, 0);
	CALL I_DL_DECIDE_LOGISTICS_FEE(P_PostCost,P_Weight,V_LogisticsId,V_ShopId,V_WarehouseId,V_ReceiverCountry,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict);
END//
DELIMITER ;
