DROP PROCEDURE IF EXISTS `I_DL_SPLIT_GOODS_BY_WAREHOUSE`;
DELIMITER //
CREATE PROCEDURE `I_DL_SPLIT_GOODS_BY_WAREHOUSE`(IN P_OperatorID INT,IN P_TradeID INT,INOUT P_TradeChanged INT,INOUT P_WarehouseID INT)
    SQL SECURITY INVOKER
    COMMENT '根据仓库不同进行自定义拆分'
MAIN_LABEL:BEGIN
	DECLARE  V_WarehouseID,V_WarehouseType,V_NOT_FOUND,V_RecID,V_bOuterStock,V_TradeID,V_SpecID,
		  V_Count1,V_Count2,V_TradeStatus,V_OldWarehouseID,V_OldWarehouseType,V_IsLarge,V_IsSealed,V_DeliveryTerm,
		  V_TradeChanged,V_bAllSplit,V_ShopID,V_WarehouseID1,V_WarehouseType1,V_WmsType,V_IsHoldEnabled,V_SplitFromTradeID INT(11) DEFAULT 0;
	DECLARE V_ActualNum,V_LeftNum DECIMAL(19,4);
	DECLARE V_TradeTime,V_PayTime DATETIME ;
	DECLARE V_TradeNo VARCHAR(40) DEFAULT '';
	
	DECLARE goods_cursor CURSOR FOR SELECT sto.rec_id, sto.spec_id,sto.actual_num,cgw.warehouse_id,sw.type,sw.is_outer_stock 
		FROM sales_trade_order sto,
		api_trade t,cfg_goods_warehouse cgw,cfg_warehouse sw
		WHERE sto.trade_id = P_TradeID AND sto.is_print_suite = 0 /*AND sto.bind_oid = ''*/ AND sto.actual_num > 0 AND  
		sto.`platform_id` = t.platform_id AND sto.`src_tid` = t.tid AND sto.`spec_id` = cgw.spec_id AND
		(cgw.shop_id = 0 OR cgw.shop_id = V_ShopID) AND cgw.warehouse_id = sw.warehouse_id AND sw.is_disabled = 0 AND 
		IF(t.wms_type > 0, sw.type = t.wms_type, sw.type<(128+t.wms_type))
		ORDER BY cgw.priority;
	
	-- 单条货品选择仓库
	DECLARE goods_cursor1 CURSOR FOR
		SELECT sto.spec_id,sto.actual_num,cgw.warehouse_id,sw.type,sw.is_outer_stock 
		FROM sales_trade_order sto,
		api_trade t,cfg_goods_warehouse cgw,cfg_warehouse sw
		WHERE sto.trade_id = P_TradeID AND sto.is_print_suite = 0 /*AND sto.bind_oid = ''*/ AND sto.actual_num > 0 AND  
		sto.`platform_id` = t.platform_id AND sto.`src_tid` = t.tid AND sto.`spec_id` = cgw.spec_id AND
		(cgw.shop_id = 0 OR cgw.shop_id = V_ShopID) AND cgw.warehouse_id = sw.warehouse_id AND sw.is_disabled = 0 AND 
		IF(t.wms_type > 0, sw.type = t.wms_type, sw.type<(128+t.wms_type))
		ORDER BY cgw.priority DESC;
	
	DECLARE warehouse_cursor CURSOR FOR SELECT warehouse_id,warehouse_type FROM tmp_sales_trade_order_warehouse GROUP BY warehouse_id;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DELETE FROM  tmp_sales_trade_order_warehouse;
	
	-- 获取原订单信息
	SELECT warehouse_id,warehouse_type,shop_id,trade_time,pay_time,trade_no,trade_status,is_sealed,delivery_term,split_from_trade_id
		INTO V_OldWarehouseID,V_OldWarehouseType,V_ShopID,V_TradeTime,V_PayTime,V_TradeNo,V_TradeStatus,V_IsSealed,V_DeliveryTerm,V_SplitFromTradeID 
		FROM sales_trade WHERE trade_id = P_TradeID;
	
	IF V_NOT_FOUND OR !V_OldWarehouseID THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	-- SELECT is_hold_enabled INTO V_IsHoldEnabled FROM cfg_shop WHERE shop_id = V_ShopID;
	-- 开启不允许拆分后的订单全是赠品。
	IF @cfg_order_warehouse_split_check_gift THEN
		SELECT COUNT(1) INTO V_Count2 FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num > 0 AND gift_type = 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1);
	ELSE
		SELECT COUNT(1) INTO V_Count2 FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num > 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1);
	END IF;
	-- 若订单只剩下一种数据(不需要拆分新订单)
	IF V_Count2 = 1 THEN
		SELECT IFNULL(cgw.warehouse_id,0),IFNULL(sw.type,0),COUNT(1) INTO  V_WarehouseID,V_WarehouseType,V_Count1
		FROM sales_trade_order sto,
		api_trade t,cfg_goods_warehouse cgw,cfg_warehouse sw
		WHERE sto.trade_id = P_TradeID AND sto.is_print_suite = 0 /*AND sto.bind_oid = ''*/ AND sto.actual_num > 0 AND  
		sto.`platform_id` = t.platform_id AND sto.`src_tid` = t.tid AND sto.`spec_id` = cgw.spec_id AND
		(cgw.shop_id = 0 OR cgw.shop_id = V_ShopID) AND cgw.warehouse_id = sw.warehouse_id AND sw.is_disabled = 0 AND 
		IF(t.wms_type > 0, sw.type = t.wms_type, sw.type<(128+t.wms_type)) AND IF(@cfg_order_warehouse_split_check_gift>0, sto.gift_type=0,1) 
		ORDER BY cgw.priority DESC;
		-- 货品指定仓库的有一个,且指定仓库和订单仓库不一致
		IF V_Count1 = V_Count2 AND V_WarehouseID <> V_OldWarehouseID THEN
			UPDATE sales_trade  SET warehouse_id = V_WarehouseID ,warehouse_type = V_WarehouseType  WHERE trade_id = P_TradeID;
			IF V_SplitFromTradeID = 0 THEN
				UPDATE api_trade at,sales_trade_order sto SET at.x_warehouse_id = V_WarehouseID 
				WHERE at.platform_id = sto.platform_id AND at.tid = sto.src_tid AND sto.trade_id = P_TradeID AND sto.platform_id >0; 
			END IF;
			CALL I_RESERVE_STOCK(P_TradeID, IF(V_TradeStatus=30,3,5), V_WarehouseID, V_OldWarehouseID);	
			-- CALL I_REFRESH_SELLCOUNT(1,P_TradeID,V_WarehouseID,V_OldWarehouseID);
			IF V_OldWarehouseType = 127 AND V_TradeStatus = 30 THEN
				DELETE FROM sales_trade_warehouse WHERE trade_id = P_TradeID;
			END IF;
			/*IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 AND V_TradeStatus = 30 THEN
				SELECT sub_type INTO V_WmsType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
				IF V_WmsType =1 THEN
					CALL I_DL_DECIDE_WAREHOUSE_LIST(P_TradeID,V_WarehouseID);
				END IF;
			END IF;*/
			SET P_WarehouseID = V_WarehouseID;
			-- SET P_WarehouseSubType = V_WmsType;
			SET P_TradeChanged = 1;
			SET @tmp_trade_changed=1;
			LEAVE MAIN_LABEL;
		ELSEIF V_Count1 <= V_Count2 THEN
			LEAVE MAIN_LABEL;
		ELSE
			SET V_WarehouseID = 0;
			OPEN goods_cursor1;
				SET V_NOT_FOUND = 0;
				WAREHOUSE_LABEL:LOOP
					FETCH goods_cursor1 INTO V_SpecID,V_ActualNum,V_WarehouseID1,V_WarehouseType1,V_bOuterStock;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
						LEAVE WAREHOUSE_LABEL;
					END IF;
					IF V_WarehouseID = 0 THEN
						SET V_WarehouseID = V_WarehouseID1;
					END IF;
					SELECT IF(V_bOuterStock,IFNULL(stock_num + wms_stock_diff - sending_num - order_num,0),IFNULL(stock_num - sending_num - order_num,0)) INTO V_LeftNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID1;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
						ITERATE WAREHOUSE_LABEL;
					END IF;
					IF V_LeftNum >= V_ActualNum THEN
						SET V_WarehouseID = V_WarehouseID1;
						SET V_WarehouseType = V_WarehouseType1;
						LEAVE WAREHOUSE_LABEL;
					END IF;
				END LOOP;
			CLOSE goods_cursor1;
			IF V_WarehouseID AND V_WarehouseID <> V_OldWarehouseID THEN
				UPDATE sales_trade  SET warehouse_id = V_WarehouseID ,warehouse_type = V_WarehouseType  WHERE trade_id = P_TradeID;
				CALL I_RESERVE_STOCK(P_TradeID, IF(V_TradeStatus=30,3,5), V_WarehouseID, V_OldWarehouseID);	
				-- CALL I_REFRESH_SELLCOUNT(1,P_TradeID,V_WarehouseID,V_OldWarehouseID);
				IF V_SplitFromTradeID = 0 THEN
					UPDATE api_trade at,sales_trade_order sto SET at.x_warehouse_id = V_WarehouseID 
					WHERE at.platform_id = sto.platform_id AND at.tid = sto.src_tid AND sto.trade_id = P_TradeID AND sto.platform_id >0; 
				END IF;
				/*IF V_IsHoldEnabled = 1 AND V_OldWarehouseType = 127 AND V_TradeStatus = 30 THEN
					DELETE FROM sales_trade_warehouse WHERE trade_id = P_TradeID;
				END IF;
				IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 AND V_TradeStatus = 30 THEN
					SELECT sub_type INTO V_WmsType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
					IF V_WmsType =1 THEN
						CALL I_DL_DECIDE_WAREHOUSE_LIST(P_TradeID,V_WarehouseID);
					END IF;
				END IF;*/
				SET P_WarehouseID = V_WarehouseID;
				-- SET P_WarehouseSubType = V_WmsType;
				SET P_TradeChanged = 1;
				SET @tmp_trade_changed=1;
			END IF;
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	SET V_Count1 = 0;
	INSERT INTO tmp_sales_trade_order_warehouse(rec_id,spec_id,gift_type,large_type,warehouse_id,warehouse_type)
	SELECT sto.rec_id, sto.spec_id,sto.gift_type,sto.large_type,cgw.warehouse_id,sw.type
	FROM sales_trade_order sto ,api_trade t,cfg_goods_warehouse cgw,cfg_warehouse sw
	WHERE sto.trade_id = P_TradeID AND sto.is_print_suite = 0 /*AND sto.bind_oid = ''*/ AND sto.actual_num > 0 AND  sto.actual_num > 0
	AND sto.`src_tid` = t.tid AND sto.`platform_id` = t.platform_id AND sto.`spec_id` = cgw.spec_id AND (cgw.shop_id = 0 OR cgw.shop_id = V_ShopID)  
	AND cgw.warehouse_id = sw.warehouse_id AND sw.is_disabled = 0 AND IF(t.wms_type > 0, sw.type = t.wms_type, sw.type<(128+t.wms_type))
	GROUP BY sto.rec_id ORDER BY cgw.priority DESC;
	
	OPEN goods_cursor;
		ID_LABEL:LOOP
			FETCH goods_cursor INTO V_RecID,V_SpecID,V_ActualNum,V_WarehouseID,V_WarehouseType,V_bOuterStock;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
				LEAVE ID_LABEL;
			END IF;
			IF EXISTS(SELECT 1 FROM stock_spec WHERE warehouse_id = V_WarehouseID AND spec_id = V_SpecID AND IF(V_bOuterStock,stock_num + wms_stock_diff - sending_num - order_num >= V_ActualNum,stock_num - sending_num - order_num >= V_ActualNum) ) THEN
				UPDATE tmp_sales_trade_order_warehouse SET  warehouse_id = V_WarehouseID,warehouse_type = V_WarehouseType WHERE rec_id = V_RecID;
			END IF;
		END LOOP;
	CLOSE goods_cursor;
	DELETE FROM tmp_sales_trade_order_warehouse WHERE warehouse_id = V_OldWarehouseID;
	-- 开启不允许拆分后的订单全是赠品。
	IF @cfg_order_warehouse_split_check_gift THEN
		SELECT COUNT(1) INTO V_Count1 FROM tmp_sales_trade_order_warehouse WHERE gift_type = 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1) ;
	ELSE
		SELECT COUNT(1) INTO V_Count1 FROM tmp_sales_trade_order_warehouse WHERE IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1);
	END IF;
	IF V_Count1 = 0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	-- 订单中全部都需要调整仓库信息
	IF  V_Count1 >= V_Count2 THEN
		IF @cfg_order_warehouse_split_check_gift THEN
			SELECT warehouse_id,warehouse_type INTO V_WarehouseID,V_WarehouseType  FROM tmp_sales_trade_order_warehouse  WHERE gift_type = 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1)  LIMIT 1;
		ELSE
			SELECT warehouse_id,warehouse_type INTO V_WarehouseID,V_WarehouseType  FROM tmp_sales_trade_order_warehouse  WHERE IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1) LIMIT 1;
		END IF;
		
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE MAIN_LABEL;
		END IF;
		
		UPDATE sales_trade  SET warehouse_id = V_WarehouseID ,warehouse_type = V_WarehouseType  WHERE trade_id = P_TradeID;
		IF V_SplitFromTradeID = 0 THEN
			UPDATE api_trade at,sales_trade_order sto SET at.x_warehouse_id = V_WarehouseID 
			WHERE at.platform_id = sto.platform_id AND at.tid = sto.src_tid AND sto.trade_id = P_TradeID AND sto.platform_id >0; 
		END IF;
		CALL I_RESERVE_STOCK(P_TradeID, IF(V_TradeStatus=30,3,5), V_WarehouseID, V_OldWarehouseID);	
		-- CALL I_REFRESH_SELLCOUNT(1,P_TradeID,V_WarehouseID,V_OldWarehouseID);
		
		SET P_TradeChanged = 1;
		-- 处理线下仓库
		/*IF V_IsHoldEnabled = 1 AND V_OldWarehouseType = 127 AND V_TradeStatus = 30 THEN
			DELETE FROM sales_trade_warehouse WHERE trade_id = P_TradeID;
		END IF;
		IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 AND V_TradeStatus = 30 THEN
			SELECT sub_type INTO V_WmsType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
			IF V_WmsType =1 THEN
				CALL I_DL_DECIDE_WAREHOUSE_LIST(P_TradeID,V_WarehouseID);
			END IF;
		END IF;*/
		SET P_WarehouseID = V_WarehouseID;
		-- SET P_WarehouseSubType = V_WmsType;
		SET V_OldWarehouseID = V_WarehouseID;
		SET @tmp_trade_changed=1;
		DELETE FROM tmp_sales_trade_order_warehouse WHERE  warehouse_id = V_WarehouseID;
		
		-- 开启不允许拆分后的订单全是赠品。
		IF @cfg_order_warehouse_split_check_gift THEN
			SELECT COUNT(1) INTO V_Count1 FROM tmp_sales_trade_order_warehouse WHERE gift_type = 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1);
		ELSEIF @cfg_order_warehouse_split_check_large_type THEN
			SELECT COUNT(1) INTO V_Count1 FROM tmp_sales_trade_order_warehouse WHERE large_type >=0;
		ELSE
			SELECT COUNT(1) INTO V_Count1 FROM tmp_sales_trade_order_warehouse;
		END IF;
		
		IF V_Count1 = 0 THEN
			LEAVE MAIN_LABEL;
		END IF;
		
	END IF;
	
	-- 拆分
	OPEN warehouse_cursor;
		WH_LABEL:LOOP
			FETCH warehouse_cursor INTO V_WarehouseID,V_WarehouseType;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
				LEAVE WH_LABEL;
			END IF;
			IF @cfg_order_warehouse_split_check_gift THEN
				IF NOT EXISTS (SELECT 1 FROM tmp_sales_trade_order_warehouse WHERE gift_type = 0 AND warehouse_id = V_WarehouseID AND IF(@cfg_order_warehouse_split_check_large_type,large_type >=0,1)) THEN
					SET V_NOT_FOUND = 0;
					ITERATE WH_LABEL;
				END IF;
			ELSEIF @cfg_order_warehouse_split_check_large_type THEN
				IF NOT EXISTS(SELECT 1 FROM tmp_sales_trade_order_warehouse WHERE warehouse_id = V_WarehouseID AND large_type>=0) THEN
					SET V_NOT_FOUND = 0;
					ITERATE WH_LABEL;
				END IF;
			END IF;
			-- 创建新的订单
			INSERT INTO sales_trade(
				trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
				trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
				receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
				receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
				logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
			(SELECT FN_SYS_NO('sales'),platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
				trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
				receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
				receiver_area,receiver_ring,receiver_dtb,V_WarehouseType,V_WarehouseID,invoice_type,invoice_title,invoice_content,
				logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
			FROM sales_trade
			WHERE trade_id=P_TradeID);
			SET V_TradeID = LAST_INSERT_ID();
			-- 拆分子单
			UPDATE  sales_trade_order sto,tmp_sales_trade_order_warehouse tso SET sto.trade_id = V_TradeID 
				WHERE sto.trade_id = P_TradeID AND sto.rec_id = tso.rec_id AND tso.warehouse_id = V_WarehouseID;
			-- 记录日志
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message,created) VALUES(V_TradeID,P_OperatorID,1,0,'客户下单', V_TradeTime);
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message,created) VALUES(V_TradeID,P_OperatorID,2,0,'客户付款', V_PayTime);
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,data,message) 
			VALUES(V_TradeID,P_OperatorID,3,2,CONCAT('订单自动拆分,拆自订单',V_TradeNo,',拆分原因:按货品出库仓库拆分'));
			-- 刷新订单的库存占用量
			CALL I_RESERVE_STOCK(V_TradeID, IF(V_TradeStatus=30,3,5), V_WarehouseID, V_OldWarehouseID);
			-- CALL I_REFRESH_SELLCOUNT(1,V_TradeID,V_WarehouseID,V_OldWarehouseID);
			-- 如果选中线下仓库
			/*IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 AND V_TradeStatus = 30 THEN
				SELECT sub_type INTO V_WmsType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
				IF V_WmsType =1 THEN
					CALL I_DL_DECIDE_WAREHOUSE_LIST(V_TradeID,V_WarehouseID);
				END IF;
			END IF;
			IF V_IsHoldEnabled = 1 AND V_TradeStatus = 30 AND V_WarehouseType = 127 AND V_WmsType =1 THEN 
				UPDATE sales_trade SET trade_status = 27 WHERE trade_id = V_TradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,14,'进入待抢单');
			ELSEIF V_TradeStatus = 30 AND @cfg_auto_check_is_open THEN
				INSERT INTO tbl_deliver_auto_check(trade_id) VALUES(V_TradeID);
			END IF;*/
			SET V_TradeChanged = 1;
			/*
			SET V_IsLarge=0;
			-- 拆分独立大件
			IF @cfg_order_auto_split AND V_IsSealed=0 AND V_DeliveryTerm=1 THEN
				CALL I_DL_SPLIT_LARGE_GOODS(P_OperatorID, V_TradeID, V_TradeStatus, V_IsLarge, V_TradeChanged);
			END IF;
			*/
			-- 预订单转审核
			SET V_bAllSplit = 0;
			IF @cfg_preorder_split_to_order_condition AND V_TradeStatus = 25 THEN
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,32|IF(@cfg_sales_split_record_package_num,16,0)|IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight|IF(@cfg_order_auto_split_by_warehouse AND @cfg_logistics_match_mode=1,1,0), 0);
				SET V_TradeChanged = 0;
				CALL I_DL_PREORDER_SPLIT_TO_ORDER(P_OperatorID, V_TradeID, 30,V_bAllSplit, V_TradeChanged);
				IF V_bAllSplit THEN
					UPDATE sales_trade SET trade_status = 30 WHERE trade_id = V_TradeID;
					CALL I_RESERVE_STOCK(V_TradeID, 3, V_WarehouseID, V_WarehouseID);
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
					-- 处理线下仓库
					/*IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 THEN
						SELECT sub_type INTO V_WmsType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
						IF V_WmsType =1 THEN
							CALL I_DL_DECIDE_WAREHOUSE_LIST(V_TradeID,V_WarehouseID);
						END IF;
					END IF;
					-- 线下仓库进入待抢单，其他进入判断自动审核
					IF V_IsHoldEnabled = 1 AND V_WarehouseType = 127 AND V_WmsType =1 THEN 
						UPDATE sales_trade SET trade_status = 27 WHERE trade_id = V_TradeID;
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,14,'进入待抢单');
					ELSEIF @cfg_auto_check_is_open THEN
						INSERT INTO tbl_deliver_auto_check(trade_id) VALUES(V_TradeID);
					END IF;*/
				END IF;
			END IF;	
			IF V_TradeChanged  THEN
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,32|IF(@cfg_sales_split_record_package_num,16,0)|IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight|IF(@cfg_order_auto_split_by_warehouse AND @cfg_logistics_match_mode=1,1,0), 0);
				SET V_TradeChanged = 0;
			END IF;
			SET P_TradeChanged =1;
			SET @tmp_trade_changed=1;
			-- 原订单拆分标记
			UPDATE sales_trade SET split_from_trade_id=P_TradeID WHERE trade_id=P_TradeID;
		END LOOP;
	CLOSE warehouse_cursor;
END
//
DELIMITER ;
