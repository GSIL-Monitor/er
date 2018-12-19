DROP PROCEDURE IF EXISTS `I_STOCKIN_CHANGE_API_GOODSSPEC`;
DELIMITER //
CREATE PROCEDURE `I_STOCKIN_CHANGE_API_GOODSSPEC`(IN `P_StockinId` INT)
    SQL SECURITY INVOKER
    COMMENT '根据入库单标记平台库存变化'
MAIN_LABEL:BEGIN
	CALL SP_UTILS_GET_CFG('stock_auto_sync', @cfg_stock_auto_sync, 0);
	IF @cfg_stock_auto_sync <>0 THEN
		-- 单品
	INSERT INTO sys_process_background(`type`,object_id)
		SELECT 1,spec_id FROM stockin_order_detail WHERE stockin_id=P_StockinId;
		
		-- 组合装
		INSERT INTO sys_process_background(`type`,object_id)
			SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,stockin_order_detail sod 
			WHERE sod.stockin_id=P_StockinId AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sod.spec_id;
	END IF;
END//
DELIMITER ;