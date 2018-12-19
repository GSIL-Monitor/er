DROP PROCEDURE IF EXISTS `I_DL_PREORDER_SPLIT_TO_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_PREORDER_SPLIT_TO_ORDER`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_ToStatus` INT,INOUT `P_bAllSplit` INT, INOUT `P_Split` INT)
    SQL SECURITY INVOKER
    COMMENT '预订单拆分转审核'
MAIN_LABEL:BEGIN
	DECLARE V_TradeID,V_OrderID,V_IsNew,V_SpecID,V_PlatformID,V_IsPreorder,V_NOT_FOUND,V_IsLack,V_WarehouseID,V_WarehouseType,
			V_IsHoldEnabled,V_GoodsTypeCount,V_GoodsLineCount,V_WarehouseSubType,V_ShopID ,V_IsNeedLeavePreorder,V_CurTime,V_IsOuterStock INT DEFAULT(0);
	DECLARE V_SrcOid,V_BindOid,V_TradeNo VARCHAR(40);
	DECLARE V_Num,V_StockNum DECIMAL(19,4) DEFAULT '0.0'; 
	DECLARE V_TradeTime,V_PayTime DATETIME;
	
	-- 遍历可以拆分的货品
	DECLARE goods_cursor1 CURSOR FOR 
		SELECT sto.rec_id,sto.spec_id,sto.actual_num,sto.src_oid,sto.platform_id
		FROM sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND actual_num>0 AND is_print_suite = 0;
		
	DECLARE goods_cursor2 CURSOR FOR
		SELECT DISTINCT sto.bind_oid,sto.platform_id,sto.src_oid
		FROM sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND actual_num>0 AND is_print_suite>0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;


	SELECT shop_id,trade_no,warehouse_id,warehouse_type,goods_type_count,trade_time,pay_time INTO	V_ShopID,V_TradeNo,V_WarehouseID,V_WarehouseType,V_GoodsTypeCount,V_TradeTime,V_PayTime FROM sales_trade WHERE trade_id = P_TradeID;
	IF V_NOT_FOUND THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	/*
		如果设置了激活时间且未到激活时间不应该转入审核
	*/
	/*SET V_CurTime = UNIX_TIMESTAMP();
	IF EXISTS(SELECT 1 FROM sales_trade WHERE trade_id = P_TradeID AND delay_to_time > V_CurTime)THEN
		SET P_bAllSplit = 0;
		SET P_Split = 0;
		LEAVE MAIN_LABEL;
	END IF;*/
	-- 如果所有的都需要拆分
	SET V_IsNeedLeavePreorder = 0;
	IF @cfg_preorder_split_leave_gift AND EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = P_TradeID AND gift_type >0) THEN
		-- 如果拆分过或者全部都是赠品了,全部都转入审核是不允许的
		IF EXISTS(SELECT 1 FROM sales_trade WHERE trade_id = P_TradeID AND split_from_trade_id <> 0) AND NOT
					EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = P_TradeID AND gift_type =0 AND actual_num > 0)THEN
			SET V_IsNeedLeavePreorder = 1;
		END IF;
	END IF;
	
	SELECT COUNT(1) INTO V_GoodsLineCount FROM sales_trade_order where trade_id = P_TradeID AND actual_num > 0 ;

	IF V_NOT_FOUND THEN
		SET V_NOT_FOUND = 0;
		SET V_GoodsLineCount = 0;
		SET P_bAllSplit = 0;
		LEAVE MAIN_LABEL;
	END IF;

	-- 配置不是只不包含关键词
	IF @cfg_preorder_split_to_order_condition <> 3 THEN
		IF V_GoodsLineCount > V_GoodsTypeCount THEN
			SELECT 1 INTO V_IsLack FROM 
				(SELECT spec_id,SUM(actual_num) actual_num FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num > 0 GROUP BY spec_id) sto
			LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id AND ss.warehouse_id = V_WarehouseID
			WHERE IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)<sto.actual_num LIMIT 1;
		ELSE
			SELECT 1 INTO V_IsLack FROM sales_trade_order sto
				LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id AND ss.warehouse_id = V_WarehouseID
				WHERE sto.trade_id = P_TradeID AND sto.actual_num >0 and IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)<sto.actual_num LIMIT 1;
		END IF;
	END IF;

	-- 配置不是只校验库存充足
	IF @cfg_preorder_split_to_order_condition <> 2 THEN
		SELECT 1 INTO  V_IsPreorder FROM  api_trade_order ato, cfg_preorder_goods_keyword cpgk,sales_trade_order sto 
			WHERE sto.trade_id = P_TradeID AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND 
			IF(cpgk.good_keyword<>'',LOCATE(cpgk.good_keyword,ato.goods_name)>0,TRUE) AND
			IF(cpgk.spec_keyword<>'',LOCATE(cpgk.spec_keyword,ato.spec_name)>0,TRUE) AND
			IF(cpgk.api_code_keyword<>'',(LOCATE(cpgk.api_code_keyword,ato.goods_no)>0) OR 
				(LOCATE(cpgk.api_code_keyword,ato.spec_no)>0) ,TRUE) LIMIT 1;
	END IF;


	IF @cfg_preorder_split_to_order_condition = 1 THEN
		-- 校验不存在 库存不足而且包含关键词的。
		IF !V_IsLack AND !V_IsPreorder THEN
			SET V_NOT_FOUND = 0;
			
			IF V_IsNeedLeavePreorder = 0 THEN
				SET P_bAllSplit = 1;
				SET P_Split = 1;
			END IF;
			
			LEAVE MAIN_LABEL;
		END IF;
	ELSEIF @cfg_preorder_split_to_order_condition = 2 THEN
		-- 校验不存在库存不足
		IF !V_IsLack THEN
			SET V_NOT_FOUND = 0;
			
			IF V_IsNeedLeavePreorder = 0 THEN
				SET P_bAllSplit = 1;
				SET P_Split = 1;
			END IF;
			
			LEAVE MAIN_LABEL;
		END IF;
		
	ELSEIF @cfg_preorder_split_to_order_condition = 3 THEN
		-- 校验不存在包含关键词的。
		IF !V_IsPreorder THEN		
			SET V_NOT_FOUND = 0;
			
			IF V_IsNeedLeavePreorder = 0 THEN
				SET P_bAllSplit = 1;
				SET P_Split = 1;
			END IF;
			
			LEAVE MAIN_LABEL;
		END IF;
	ELSEIF @cfg_preorder_split_to_order_condition = 4 THEN
		-- 部分拆分库存充足的货品
		IF !V_IsLack OR !V_IsPreorder THEN
			SET V_NOT_FOUND = 0;
			
			IF V_IsNeedLeavePreorder = 0 THEN
				SET P_bAllSplit = 1;
				SET P_Split = 1;
			END IF;
			
			LEAVE MAIN_LABEL;
		END IF;		
	END IF;

	IF V_GoodsLineCount<=1 THEN
		SET P_bAllSplit = 0;
		LEAVE MAIN_LABEL;
	END IF;
	SET V_NOT_FOUND = 0;
	-- 部分拆分库存充足的货品
	
	SELECT is_outer_stock  INTO  V_IsOuterStock  FROM  cfg_warehouse WHERE warehouse_id = V_WarehouseID;
	
	OPEN goods_cursor1;
		GOODS_ENOUGH_LABEL:LOOP
			FETCH goods_cursor1 INTO V_OrderID,V_SpecID,V_Num,V_SrcOid,V_PlatformID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
				LEAVE GOODS_ENOUGH_LABEL;
			END IF;
			SET V_IsPreorder = 0;
			SET V_IsLack = 0;

			-- 校验关键词
			IF @cfg_preorder_split_to_order_condition <> 2 THEN 
				SELECT 1 INTO V_IsPreorder FROM api_trade_order ato, cfg_preorder_goods_keyword cpgk 
				WHERE ato.platform_id=V_PlatformID AND ato.oid=V_SrcOid AND 
				IF(cpgk.good_keyword<>'',LOCATE(cpgk.good_keyword,ato.goods_name)>0,TRUE) AND
				IF(cpgk.spec_keyword<>'',LOCATE(cpgk.spec_keyword,ato.spec_name)>0,TRUE) AND
				IF(cpgk.api_code_keyword<>'',(LOCATE(cpgk.api_code_keyword,ato.goods_no)>0) OR 
							(LOCATE(cpgk.api_code_keyword,ato.spec_no)>0) ,TRUE) LIMIT 1;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND = 0;
				END IF;
			END IF;

			-- 计算库存（处理一个货品多条记录的情况）
			IF V_GoodsLineCount > V_GoodsTypeCount AND @cfg_preorder_split_to_order_condition <> 3 THEN
				SELECT SUM(actual_num) INTO V_Num FROM sales_trade_order WHERE trade_id = P_TradeID AND spec_id = V_SpecID AND actual_num >0;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND = 0;
					ITERATE GOODS_ENOUGH_LABEL;
				END IF;
			END IF;


			IF @cfg_preorder_split_to_order_condition = 1 THEN
				IF !V_IsPreorder THEN
					-- SELECT IFNULL(stock_num-sending_num-order_num,0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
					-- SELECT ifnull(IF(V_IsOuterStock,IFNULL(stock_num+wms_stock_diff,0),IFNULL(stock_num,0))-IFNULL(sending_num,0)-IFNULL(order_num,0)-IFNULL(lock_num,0)-IF(@cfg_order_check_stock_sub_to_transfer,IFNULL(to_transfer_num,0),0),0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
					SELECT ifnull(IF(V_IsOuterStock,IFNULL(stock_num+wms_stock_diff,0),IFNULL(stock_num,0))-IFNULL(sending_num,0)-IFNULL(order_num,0)-IFNULL(lock_num,0),0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
						ITERATE GOODS_ENOUGH_LABEL;
					END IF;
					IF V_StockNum >= V_Num THEN
						IF V_IsNew = 0 THEN
							INSERT INTO sales_trade(
								trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
								trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
								receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
								receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
								logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
							(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
								trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
								receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
								receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
								logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
							FROM sales_trade
							WHERE trade_id=P_TradeID);
							SET V_TradeID = LAST_INSERT_ID();
							SET V_IsNew = 1;
						END IF;	
						UPDATE sales_trade_order SET trade_id = V_TradeID WHERE rec_id = V_OrderID;
					END IF;
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 2 THEN
				-- SELECT IFNULL(stock_num-sending_num-order_num,0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
				-- SELECT ifnull(IF(V_IsOuterStock,IFNULL(stock_num+wms_stock_diff,0),IFNULL(stock_num,0))-IFNULL(sending_num,0)-IFNULL(order_num,0)-IFNULL(lock_num,0)-IF(@cfg_order_check_stock_sub_to_transfer,IFNULL(to_transfer_num,0),0),0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
				SELECT ifnull(IF(V_IsOuterStock,IFNULL(stock_num+wms_stock_diff,0),IFNULL(stock_num,0))-IFNULL(sending_num,0)-IFNULL(order_num,0)-IFNULL(lock_num,0),0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND = 0;
					ITERATE GOODS_ENOUGH_LABEL;
				END IF;
				IF V_StockNum >= V_Num THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE rec_id = V_OrderID;
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 3 THEN
				IF !V_IsPreorder THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE rec_id = V_OrderID;
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 4 THEN
				IF V_IsPreorder THEN
					-- SELECT IFNULL(stock_num-sending_num-order_num,0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
					SELECT ifnull(IF(V_IsOuterStock,IFNULL(stock_num+wms_stock_diff,0),IFNULL(stock_num,0))-IFNULL(sending_num,0)-IFNULL(order_num,0)-IFNULL(lock_num,0),0) num INTO V_StockNum FROM stock_spec WHERE spec_id = V_SpecID AND warehouse_id = V_WarehouseID;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
						SET V_StockNum = 0;
					END IF;
				END IF;
				IF !V_IsPreorder OR V_StockNum >= V_Num THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE rec_id = V_OrderID;
				END IF;
			END IF;
		END LOOP;
	CLOSE goods_cursor1;
	
	OPEN goods_cursor2;
		GOODS_LABEL2:LOOP
			FETCH goods_cursor2 INTO V_BindOid,V_PlatformID,V_SrcOid;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0 ;
				LEAVE GOODS_LABEL2;
			END IF;
			SET V_IsPreorder = 0;
			SET V_IsLack = 0;
			IF @cfg_preorder_split_to_order_condition <> 2 THEN
				SELECT COUNT(1) INTO V_IsPreorder FROM sales_trade_order sto, api_trade_order ato, cfg_preorder_goods_keyword cpgk 
				WHERE sto.platform_id = ato.platform_id AND ato.platform_id=V_PlatformID 
					AND ato.oid=sto.src_oid AND sto.bind_oid = V_BindOid AND sto.src_oid = V_SrcOid AND
					IF(cpgk.good_keyword<>'',LOCATE(cpgk.good_keyword,ato.goods_name)>0,TRUE) AND
					IF(cpgk.spec_keyword<>'',LOCATE(cpgk.spec_keyword,ato.spec_name)>0,TRUE) AND
					IF(cpgk.api_code_keyword<>'',(LOCATE(cpgk.api_code_keyword,ato.goods_no)>0) OR 
							(LOCATE(cpgk.api_code_keyword,ato.spec_no)>0) ,TRUE) LIMIT 1;
			END IF;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
			END IF;
			IF @cfg_preorder_split_to_order_condition = 1 THEN
				IF !V_IsPreorder THEN
					SELECT 1 INTO V_IsLack FROM sales_trade_order sto,stock_spec ss
						WHERE sto.trade_id = P_TradeID AND sto.spec_id = ss.spec_id AND ss.warehouse_id = V_WarehouseID AND sto.bind_oid = V_BindOid 
						AND sto.platform_id = V_PlatformID AND IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)<sto.actual_num LIMIT 1;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
					END IF;
					IF !V_IsLack THEN
						IF V_IsNew = 0 THEN
							INSERT INTO sales_trade(
								trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
								trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
								receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
								receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
								logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
							(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
								trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
								receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
								receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
								logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
							FROM sales_trade
							WHERE trade_id=P_TradeID);
							SET V_TradeID = LAST_INSERT_ID();
							SET V_IsNew = 1;
						END IF;	
						UPDATE sales_trade_order SET trade_id = V_TradeID WHERE bind_oid = V_BindOid 
						AND src_oid = V_SrcOid AND platform_id = V_PlatformID;	
					END IF;
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 2 THEN
				SELECT 1 INTO V_IsLack FROM sales_trade_order sto,stock_spec ss
						WHERE sto.trade_id = P_TradeID AND sto.spec_id = ss.spec_id AND ss.warehouse_id = V_WarehouseID AND sto.bind_oid = V_BindOid 
						AND sto.platform_id = V_PlatformID AND IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)<sto.actual_num LIMIT 1;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND = 0;
				END IF;
				IF  !V_IsLack THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE bind_oid = V_BindOid 
						AND src_oid = V_SrcOid AND platform_id = V_PlatformID;	
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 3 THEN
				IF !V_IsPreorder THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE bind_oid = V_BindOid 
						AND src_oid = V_SrcOid AND platform_id = V_PlatformID;	
				END IF;
			ELSEIF @cfg_preorder_split_to_order_condition = 4 THEN
				IF V_IsPreorder THEN
					SELECT 1 INTO V_IsLack FROM sales_trade_order sto,stock_spec ss
							WHERE sto.trade_id = P_TradeID AND sto.spec_id = ss.spec_id AND ss.warehouse_id = V_WarehouseID AND sto.bind_oid = V_BindOid 
							AND sto.platform_id = V_PlatformID AND IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)<sto.actual_num LIMIT 1;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND = 0;
					END IF;				
				END IF;
				IF !V_IsPreorder OR !V_IsLack THEN
					IF V_IsNew = 0 THEN
						INSERT INTO sales_trade(
							trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
						(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
							trade_time,pay_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
							receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
							receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
							logistics_id,to_deliver_time,-P_TradeID,NOW(),id_card,trade_type
						FROM sales_trade
						WHERE trade_id=P_TradeID);
						SET V_TradeID = LAST_INSERT_ID();
						SET V_IsNew = 1;
					END IF;	
					UPDATE sales_trade_order SET trade_id = V_TradeID WHERE bind_oid = V_BindOid 
						AND src_oid = V_SrcOid AND platform_id = V_PlatformID;	
				END IF;
			END IF;
		END LOOP;
	CLOSE goods_cursor2;
	
	IF V_TradeID THEN
	
		-- 把赠品留在预定单
		IF @cfg_preorder_split_leave_gift AND EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND gift_type >0)  THEN
			UPDATE sales_trade_order SET trade_id = P_TradeID WHERE trade_id = V_TradeID  AND gift_type > 0;
			-- 如果把赠品除掉都没有 有效的货品,拆分？？？
			IF NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id= V_TradeID AND gift_type = 0 AND actual_num >0 )THEN
				-- 删除新生成的单子
				UPDATE sales_trade_order SET trade_id = P_TradeID WHERE trade_id = V_TradeID; 
				DELETE FROM sales_trade WHERE trade_id = V_TradeID;
				SET P_Split = 0;
				LEAVE MAIN_LABEL;
			END IF;

		END IF;

		IF @cfg_order_warehouse_split_check_gift THEN
			IF(NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND gift_type = 0 AND actual_num > 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type>=0,1))
			OR 
			NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = P_TradeID AND gift_type = 0 AND actual_num > 0 AND IF(@cfg_order_warehouse_split_check_large_type,large_type>=0,1))
			) THEN
				-- 拆分不成功
				UPDATE sales_trade_order SET trade_id = P_TradeID WHERE trade_id = V_TradeID;				
				-- 删除被拆分的订单
				DELETE FROM sales_trade WHERE trade_id = V_TradeID;
				SET P_Split = 0;
				LEAVE MAIN_LABEL;
			END IF;
		ELSEIF @cfg_order_warehouse_split_check_large_type THEN
			IF(NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND actual_num > 0 AND large_type>=0) OR
			NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num > 0 AND large_type>=0)) THEN
				-- 拆分不成功
				UPDATE sales_trade_order SET trade_id = P_TradeID WHERE trade_id = V_TradeID;				
				-- 删除被拆分的订单
				DELETE FROM sales_trade WHERE trade_id = V_TradeID;
				SET P_Split = 0;
				LEAVE MAIN_LABEL;
			END IF;
		ELSE
			IF NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND actual_num > 0) OR
			NOT EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id = P_TradeID AND actual_num > 0) THEN
				-- 拆分不成功
				UPDATE sales_trade_order SET trade_id = P_TradeID WHERE trade_id = V_TradeID;				
				-- 删除被拆分的订单
				DELETE FROM sales_trade WHERE trade_id = V_TradeID;
				SET P_Split = 0;
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
		CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,32|IF(@cfg_sales_split_record_package_num,16,0)|IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, 0);
		CALL I_RESERVE_STOCK(V_TradeID, 3, V_WarehouseID, V_WarehouseID);
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message,created) VALUES(V_TradeID,P_OperatorID,1,0,'客户下单', V_TradeTime);
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message,created) VALUES(V_TradeID,P_OperatorID,2,0,'客户付款', V_PayTime);
		-- INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,3,3,CONCAT('预订单自动拆分,拆自订单',V_TradeNo,',拆分原因:',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核')));
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,3,3,CONCAT('预订单自动拆分,拆自订单',V_TradeNo,',拆分原因:','库存充足的订单转审核'));
		SET P_Split=1;	
		-- 原订单拆分标记
		UPDATE sales_trade SET split_from_trade_id=-P_TradeID WHERE trade_id=P_TradeID;
		IF V_TradeID THEN
			-- INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message) VALUES(P_TradeID,P_OperatorID,21,3,CONCAT('预订单自动拆分,拆分原因:',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核')));
			INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message) VALUES(P_TradeID,P_OperatorID,21,3,CONCAT('预订单自动拆分,拆分原因:','库存充足的订单转审核'));
		END IF;
		SET V_WarehouseSubType = 0;
		
		/*SELECT is_hold_enabled INTO V_IsHoldEnabled FROM cfg_shop WHERE shop_id = V_ShopID;
		
		IF V_WarehouseType = 127 THEN
			-- 查询当前仓库是否是待抢单仓库
			SELECT sub_type INTO V_WarehouseSubType FROM cfg_warehouse WHERE warehouse_id = V_WarehouseID;
		ELSE
			SET V_WarehouseSubType = 0;
		END IF;
		-- 按仓库地址库存进行拆分 @tmp_trade_changed指是否按货品指定仓库拆分过
		IF @cfg_order_auto_split_by_ware_addr_stock AND @tmp_trade_changed=0  THEN
			CALL I_DL_SPLIT_GOODS_BY_WARE_ADDR_STOCK(P_OperatorID,V_TradeID,P_Split,V_WarehouseID,V_WarehouseSubType);
		END IF;
		
		IF V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 AND NOT EXISTS(SELECT 1 FROM sales_trade_warehouse WHERE trade_id = V_TradeID) THEN
			CALL I_DL_DECIDE_WAREHOUSE_LIST(V_TradeID,V_WarehouseID);
		END IF;
		IF V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 THEN
			UPDATE sales_trade SET trade_status = 27 WHERE trade_id = V_TradeID;
			INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,14,'进入待抢单');
		ELSEIF @cfg_auto_check_is_open THEN
			INSERT INTO tbl_deliver_auto_check(trade_id) VALUES(V_TradeID);
		END IF;*/

	END IF;

END//
DELIMITER ;