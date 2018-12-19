DROP PROCEDURE IF EXISTS `I_DL_DECIDE_LOGISTICS_ASSIGN`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_LOGISTICS_ASSIGN`(OUT `P_LogisticsID` INT,IN `V_LogisticsType` INT,IN `P_DeliveryTerm` INT,IN `P_ShopID` INT, IN `P_WarehouseID` INT, IN `P_ReceiverProvince` INT,OUT `P_SelectLogisticsByGoods` VARCHAR(256))
SQL SECURITY INVOKER
COMMENT '根据单品/组合装的指定选择物流'
LOGISTICS_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_IsSupportCod,V_LogistisID,V_RecId INT DEFAULT(0);
	
	DECLARE logistics_suite_cursor CURSOR FOR
		SELECT cgl.rec_id,cgl.logistics_id FROM tmp_sales_trade_order tsto,cfg_goods_logistics cgl 
		WHERE cgl.type = 1 AND cgl.spec_id = tsto.suite_id AND (cgl.shop_id = P_ShopID OR cgl.shop_id = 0) AND (cgl.warehouse_id = P_WarehouseID OR cgl.warehouse_id = 0) 
		ORDER BY cgl.priority DESC,cgl.shop_id DESC,cgl.warehouse_id DESC;
		
	DECLARE logistics_spec_cursor CURSOR FOR 
		SELECT cgl.rec_id,cgl.logistics_id FROM tmp_sales_trade_order tsto,cfg_goods_logistics cgl 
		WHERE cgl.type = 0 AND cgl.spec_id = tsto.spec_id AND (cgl.shop_id = P_ShopID OR cgl.shop_id = 0) AND (cgl.warehouse_id = P_WarehouseID OR cgl.warehouse_id = 0)
		ORDER BY cgl.priority DESC,cgl.shop_id DESC,cgl.warehouse_id DESC;
		
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;

	SET	P_LogisticsID = 0;
	-- 判断是否开启了物流类别,若开启了直接退出
	IF V_LogisticsType <> -1 THEN
		LEAVE LOGISTICS_LABEL;
	END IF;
	
	-- 判断订单的货品是否存在指定物流
	IF NOT EXISTS(SELECT 1 FROM cfg_goods_logistics cgl,tmp_sales_trade_order ts1 WHERE cgl.type = 0 AND cgl.spec_id = ts1.spec_id AND ts1.suite_id = 0) THEN
		IF NOT EXISTS(SELECT 1 FROM cfg_goods_logistics cgl,tmp_sales_trade_order ts2 WHERE cgl.type = 1 AND cgl.spec_id =  ts2.suite_id) THEN	
			LEAVE LOGISTICS_LABEL;
		END IF;
	END IF;
	
	SET V_NOT_FOUND = 0;
	-- 启用按货品选物流策略
	
	-- 判断组合装物流
	OPEN logistics_suite_cursor;
	LOGISTICS_SUITE_LABEL: LOOP
		FETCH logistics_suite_cursor INTO V_RecId,V_LogistisID;
		IF V_NOT_FOUND THEN -- 不存在组合装或没有指定组合装物流
			SET V_NOT_FOUND = 0;
			LEAVE LOGISTICS_SUITE_LABEL;
		END IF;
		/*IF @cfg_sales_trade_logistics_bygoods =2 AND NOT EXISTS(SELECT 1 FROM  cfg_goods_logistics_coverage WHERE depend_id = V_RecId AND province_id IN(0,P_ReceiverProvince)) THEN
			ITERATE LOGISTICS_SUITE_LABEL;
		END IF;*/
		IF P_DeliveryTerm = 2 THEN -- 若为货到付款的订单
			SELECT is_support_cod INTO V_IsSupportCod FROM cfg_logistics WHERE logistics_id = V_LogistisID;
			IF V_IsSupportCod THEN -- 该物流支持货到付款
				SET P_LogisticsID = V_LogistisID;
				SET P_SelectLogisticsByGoods='货到付款订单按组合装使用物流';
				LEAVE LOGISTICS_LABEL;
			END IF;
		ELSE
			SET P_LogisticsID = V_LogistisID;
			SET P_SelectLogisticsByGoods='按组合装使用物流';
			LEAVE LOGISTICS_LABEL;
		END IF;
	END LOOP;
	CLOSE logistics_suite_cursor;
	
	SET V_NOT_FOUND = 0;
	-- 判断单品物流
	OPEN logistics_spec_cursor;			
	LOGISTICS_SPEC_LABEL: LOOP
		FETCH logistics_spec_cursor INTO V_RecId,V_LogistisID;
		
		IF V_NOT_FOUND THEN -- 没有指定的物流
			SET V_NOT_FOUND = 0;
			LEAVE LOGISTICS_SPEC_LABEL;
		END IF;
		-- 覆盖范围
		/*IF @cfg_sales_trade_logistics_bygoods =2 AND NOT EXISTS(SELECT 1 FROM  cfg_goods_logistics_coverage WHERE depend_id = V_RecId AND province_id IN(0,P_ReceiverProvince)) THEN
			ITERATE LOGISTICS_SPEC_LABEL;
		END IF;*/
		
		IF P_DeliveryTerm = 2 THEN -- 若为货到付款的订单
			SELECT is_support_cod INTO V_IsSupportCod FROM cfg_logistics WHERE logistics_id = V_LogistisID;
			IF V_IsSupportCod THEN -- 该物流支持货到付款
				SET P_LogisticsID = V_LogistisID;
				SET P_SelectLogisticsByGoods='货到付款订单按单品使用物流';
				LEAVE LOGISTICS_LABEL;
			END IF;
		ELSE
			SET P_LogisticsID = V_LogistisID;
			SET P_SelectLogisticsByGoods='按单品使用物流';
			LEAVE LOGISTICS_LABEL;
		END IF;
	END LOOP;
	CLOSE logistics_spec_cursor;
END//
DELIMITER ;