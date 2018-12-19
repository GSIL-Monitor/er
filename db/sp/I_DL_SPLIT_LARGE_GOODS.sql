DROP PROCEDURE IF EXISTS `I_DL_SPLIT_LARGE_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_SPLIT_LARGE_GOODS`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_ToStatus` INT, INOUT `P_IsLarge` INT, INOUT `P_Split` INT)
    SQL SECURITY INVOKER
    COMMENT '大件拆分'
MAIN_LABEL:BEGIN
	DECLARE V_TradeID2,V_LoopCount,V_OrderID,V_I,V_Num2,V_WarehouseID,V_WarehouseType,V_OldWarehouseID,V_OldWarehouseSubType,V_ShopID,V_IsSetWareByGoods,
	V_NOT_FOUND,V_bAllSplit,V_WarehouseSubType,V_TradeStatus,V_IsHoldEnabled,V_OrderIdTmp INT DEFAULT(0);
	DECLARE V_GoodsCount,V_LargeCount1,V_LargeCount2,V_Num,V_ODiscount,V_OShareAmount,V_OSharePost,V_OPaid,V_OWeight,V_OCommission,
		V_SDiscount,V_SShareAmount,V_SSharePost,V_SPaid,V_SWeight,V_SCommission DECIMAL(19,4);
	DECLARE V_TradeTime,V_PayTime DATETIME;
	
	-- 大件
	DECLARE large_goods_cursor1 CURSOR FOR 
		SELECT rec_id,actual_num,discount,share_amount,share_post,paid,weight,commission
		FROM sales_trade_order sto
		WHERE trade_id=P_TradeID AND is_print_suite=0 AND large_type=1 AND actual_num>0/*AND bind_oid=''*/;
	
	DECLARE large_goods_cursor2 CURSOR FOR 
		SELECT rec_id,actual_num,discount,share_amount,share_post,paid,weight,commission
		FROM sales_trade_order
		WHERE trade_id=P_TradeID AND is_print_suite=0 AND large_type=2 AND actual_num>0/*AND bind_oid=''*/;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	
	-- 货品数，货品种类，普通大件数，独立大件数
	SELECT SUM(actual_num),
		SUM(IF(large_type=1 AND is_print_suite=0/*AND bind_oid=''*/,actual_num,0)),	-- 普通大件数
		SUM(IF(large_type=2 AND is_print_suite=0/*AND bind_oid=''*/,actual_num,0)) 	-- 独立大件数
	INTO V_GoodsCount,V_LargeCount1,V_LargeCount2
	FROM sales_trade_order
	WHERE trade_id=P_TradeID AND actual_num>0/*AND bind_oid=''*/;
	
	IF V_LargeCount2+V_LargeCount1 >= @cfg_sales_split_large_goods_max_num THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 独立大件
	IF V_LargeCount2>0 AND V_GoodsCount>1 THEN
		SELECT trade_time,pay_time,warehouse_id,warehouse_type,shop_id INTO V_TradeTime,V_PayTime,V_OldWarehouseID,V_WarehouseType,V_ShopID FROM sales_trade WHERE trade_id=P_TradeID;
		/*IF @cfg_order_auto_split_by_warehouse OR @cfg_order_auto_split_by_ware_addr_stock OR V_WarehouseType = 127 THEN
			SELECT is_setwarebygoods,is_hold_enabled INTO V_IsSetWareByGoods,V_IsHoldEnabled FROM cfg_shop WHERE shop_id = V_ShopID;
			IF V_WarehouseType = 127 THEN
				-- 查询当前仓库是否是待抢单仓库
				SELECT sub_type INTO V_OldWarehouseSubType FROM cfg_warehouse WHERE warehouse_id = V_OldWarehouseID;
			ELSE
				SET V_OldWarehouseSubType = 0;
			END IF;
		END IF;*/
		-- 拆分次数
		SET V_LoopCount=IF(V_LargeCount2=V_GoodsCount,V_LargeCount2-1,V_LargeCount2);
		
		OPEN large_goods_cursor2;
		LARGE_GOODS_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH large_goods_cursor2 INTO V_OrderID, V_Num, V_ODiscount,V_OShareAmount,V_OSharePost,V_OPaid,V_OWeight,V_OCommission;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE LARGE_GOODS_LABEL;
			END IF;
			SET V_WarehouseID = V_OldWarehouseID;
			SET V_WarehouseSubType = V_OldWarehouseSubType;
			SET V_I=1, V_Num2=CEIL(V_Num);
			WHILE V_I<=V_Num2 AND V_LoopCount>0 DO
				-- 执行拆分
				-- 创建订单
				INSERT INTO sales_trade(
					trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
					trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
					receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
					receiver_area,receiver_dtb,receiver_ring,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
					logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_type)
				SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,trade_from,
					trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
					receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
					receiver_area,receiver_dtb,receiver_ring,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
					logistics_id,to_deliver_time,P_TradeID,NOW(),id_card,trade_type
				FROM sales_trade
				WHERE trade_id=P_TradeID;
				
				SET V_TradeID2 = LAST_INSERT_ID();
				
				IF V_Num2=1 THEN -- 只有一件
					UPDATE sales_trade_order SET trade_id=V_TradeID2 WHERE rec_id=V_OrderID;
				ELSEIF V_Num2=V_I THEN	-- 最后一件
					SET V_SDiscount=V_ODiscount-TRUNCATE((V_ODiscount/V_Num),4)*(V_I-1);
					SET V_SShareAmount=V_OShareAmount-TRUNCATE((V_OShareAmount/V_Num),4)*(V_I-1);
					SET V_SSharePost=V_OSharePost-TRUNCATE((V_OSharePost/V_Num),4)*(V_I-1);
					SET V_SPaid=V_OPaid-TRUNCATE((V_OPaid/V_Num),4)*(V_I-1);
					SET V_SWeight=V_OWeight-TRUNCATE((V_OWeight/V_Num),4)*(V_I-1);
					SET V_SCommission=V_OCommission-TRUNCATE((V_OCommission/V_Num),4)*(V_I-1);

					UPDATE sales_trade_order SET trade_id=V_TradeID2,num=GREATEST(num-V_I+1,0),actual_num=(V_Num-V_I+1),
						discount=V_SDiscount,share_amount=V_SShareAmount, share_post=V_SSharePost,
						paid=V_SPaid,weight=V_SWeight,commission=V_SCommission
					WHERE rec_id=V_OrderID;
				ELSE
					SET V_SDiscount=TRUNCATE(V_ODiscount/V_Num,4);
					SET V_SShareAmount=TRUNCATE(V_OShareAmount/V_Num,4);
					SET V_SSharePost=TRUNCATE(V_OSharePost/V_Num,4);
					SET V_SPaid=TRUNCATE(V_OPaid/V_Num,4);
					SET V_SWeight=TRUNCATE(V_OWeight/V_Num,4);
					SET V_SCommission=TRUNCATE(V_OCommission/V_Num,4);
				
					-- INSERT
					INSERT INTO sales_trade_order(trade_id,spec_id,platform_id,shop_id,src_oid,src_tid,bind_oid,suite_id,gift_type,
						refund_status,guarantee_mode,num,actual_num,price,order_price,share_price,discount,share_amount,
						share_post,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_amount,
						suite_num,is_print_suite,weight,goods_type,flag,stock_reserved,large_type,invoice_type,invoice_content,
						delivery_term,tax_rate,from_mask,is_allow_zero_cost,commission,remark,created)
					SELECT V_TradeID2,spec_id,platform_id,shop_id,src_oid,src_tid,bind_oid,suite_id,gift_type,
						refund_status,guarantee_mode,1,1,price,order_price,share_price,
						V_SDiscount,V_SShareAmount,V_SSharePost,V_SPaid,
						goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_amount,
						suite_num,is_print_suite,V_SWeight,goods_type,flag,stock_reserved,2,invoice_type,invoice_content,
						delivery_term,tax_rate,from_mask,is_allow_zero_cost,V_SCommission,remark,NOW()
					FROM sales_trade_order
					WHERE rec_id=V_OrderID;

					SELECT LAST_INSERT_ID() INTO V_OrderIdTmp;

					/*(INSERT IGNORE INTO sales_trade_order_ext(order_id,batch_id,created)									
					SELECT V_OrderIdTmp,batch_id,NOW()
					FROM sales_trade_order_ext
					WHERE order_id = V_OrderID;*/



				END IF;
				
				-- 新订单执行完整性更新
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID2, 32|IF(@cfg_sales_split_record_package_num,16,0)|IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, 0);
				
				-- 新订单刷新库存  这里严格来说是没有发生变化的，没有修改仓库，那里来的变化
				-- CALL I_RESERVE_STOCK(V_TradeID2,IF(P_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);
				
				-- 日志
				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message,created) VALUES(V_TradeID2,P_OperatorID,1,0,'客户下单', V_TradeTime);
				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message,created) VALUES(V_TradeID2,P_OperatorID,2,0,'客户付款', V_PayTime);
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID2,P_OperatorID,21,'自动拆分大件');

				SET V_TradeStatus = P_ToStatus;

				-- 开启按仓库拆分订单配置
				IF V_WarehouseSubType=0 AND @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 THEN
					IF EXISTS (SELECT 1 FROM sales_trade_order sto,cfg_goods_warehouse cgw WHERE sto.trade_id = V_TradeID2 AND sto.actual_num > 0 
						AND cgw.spec_id=sto.spec_id AND (cgw.shop_id = 0 OR cgw.shop_id = V_ShopID)) THEN
						
						CALL I_DL_SPLIT_GOODS_BY_WAREHOUSE(P_OperatorID,V_TradeID2,V_bAllSplit,V_WarehouseID,V_WarehouseSubType); 
					END IF;
				END IF;
				
				-- 开启预订单拆分转审核配置
				/*SET V_bAllSplit = 0;
				IF @cfg_preorder_split_to_order_condition AND P_ToStatus = 25 THEN
					CALL I_DL_PREORDER_SPLIT_TO_ORDER(P_OperatorID, V_TradeID2, 30,V_bAllSplit, P_Split);
					IF V_bAllSplit THEN
						UPDATE sales_trade SET trade_status = 30 WHERE trade_id = V_TradeID2;
						-- 新订单刷新库存
						CALL I_RESERVE_STOCK(V_TradeID2,3, V_WarehouseID, V_WarehouseID);
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID2,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
						SET V_TradeStatus = 30;
					END IF;
				END IF;*/
				
				-- 按仓库地址库存进行拆分
				/*SET V_bAllSplit = 0;
				IF V_TradeStatus = 30 AND @cfg_order_auto_split_by_ware_addr_stock AND @tmp_trade_changed=0 THEN
					CALL I_DL_SPLIT_GOODS_BY_WARE_ADDR_STOCK(P_OperatorID,V_TradeID2,V_bAllSplit,V_WarehouseID,V_WarehouseSubType);
				END IF;
				
				IF V_TradeStatus = 30 AND V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 AND NOT EXISTS(SELECT 1 FROM sales_trade_warehouse WHERE trade_id = V_TradeID2) THEN
					CALL I_DL_DECIDE_WAREHOUSE_LIST(V_TradeID2,V_WarehouseID);
				END IF;
				IF V_TradeStatus = 30 AND V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 THEN
					UPDATE sales_trade SET trade_status = 27 WHERE trade_id = V_TradeID2;
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID2,P_OperatorID,14,'进入待抢单');
				ELSEIF V_TradeStatus = 30 AND @cfg_auto_check_is_open THEN
					INSERT INTO tbl_deliver_auto_check(trade_id) VALUES(V_TradeID2);
				END IF;*/
			
				SET V_WarehouseID = V_OldWarehouseID;
				SET V_WarehouseSubType = V_OldWarehouseSubType;
				SET P_Split=1;
				
				SET V_I=V_I+1;
				SET V_LoopCount=V_LoopCount-1;
				-- 还有一个货品在主订单
				IF V_LoopCount<=0 AND V_Num2=V_I AND V_Num>V_I-1 THEN
					SET V_SDiscount=V_ODiscount-TRUNCATE((V_ODiscount/V_Num),4)*(V_I-1);
					SET V_SShareAmount=V_OShareAmount-TRUNCATE((V_OShareAmount/V_Num),4)*(V_I-1);
					SET V_SSharePost=V_OSharePost-TRUNCATE((V_OSharePost/V_Num),4)*(V_I-1);
					SET V_SPaid=V_OPaid-TRUNCATE((V_OPaid/V_Num),4)*(V_I-1);
					SET V_SWeight=V_OWeight-TRUNCATE((V_OWeight/V_Num),4)*(V_I-1);

					UPDATE sales_trade_order SET num=GREATEST(num-V_I+1,0),actual_num=(V_Num-V_I+1),
						discount=V_SDiscount,share_amount=V_SShareAmount, share_post=V_SSharePost,
						paid=V_SPaid,weight=V_SWeight
					WHERE rec_id=V_OrderID;
					
					SET P_IsLarge = 2;
				END IF;

			END WHILE;
			
			IF V_LoopCount=0 THEN
				LEAVE LARGE_GOODS_LABEL;
			END IF;
		END LOOP;
		CLOSE large_goods_cursor2;
		-- 原订单拆分标记
		UPDATE sales_trade SET split_from_trade_id=P_TradeID WHERE trade_id=P_TradeID;
	ELSEIF V_LargeCount2=1 THEN
		SET P_IsLarge = 2;
	END IF;
	
	-- 拆分普通大件
	IF V_LargeCount1>1 THEN
		SELECT trade_time,pay_time,warehouse_id,warehouse_type,shop_id INTO V_TradeTime,V_PayTime,V_OldWarehouseID,V_WarehouseType,V_ShopID FROM sales_trade WHERE trade_id=P_TradeID;
		/*IF @cfg_order_auto_split_by_warehouse OR @cfg_order_auto_split_by_ware_addr_stock  OR V_WarehouseType = 127 THEN
			SELECT is_setwarebygoods,is_hold_enabled INTO V_IsSetWareByGoods,V_IsHoldEnabled FROM cfg_shop WHERE shop_id = V_ShopID;
			IF V_WarehouseType = 127 THEN
				-- 查询当前仓库是否是待抢单仓库
				SELECT sub_type INTO V_OldWarehouseSubType FROM cfg_warehouse WHERE warehouse_id = V_OldWarehouseID;
			ELSE
				SET V_OldWarehouseSubType = 0;
			END IF;
		END IF;*/
		-- 拆分次数
		SET V_LoopCount=V_LargeCount1-1;
		
		OPEN large_goods_cursor1;
		LARGE_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH large_goods_cursor1 INTO V_OrderID, V_Num, V_ODiscount,V_OShareAmount,V_OSharePost,V_OPaid,V_OWeight,V_OCommission;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE LARGE_GOODS_LABEL;
			END IF;
			SET V_WarehouseID = V_OldWarehouseID;
			SET V_WarehouseSubType = V_OldWarehouseSubType;
			SET V_I=1,V_Num2=CEIL(V_Num);
			WHILE V_I<=V_Num2 AND V_LoopCount>0 DO
				-- 创建订单
				INSERT INTO sales_trade(
					trade_no,platform_id,shop_id,trade_status,freeze_reason,fenxiao_type,fenxiao_nick,
					trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
					receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
					receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
					logistics_id,to_deliver_time,split_from_trade_id,created,id_card,trade_from,trade_type)
				(SELECT FN_SYS_NO('sales'),platform_id,shop_id,P_ToStatus,freeze_reason,fenxiao_type,fenxiao_nick,
					trade_time,pay_time,delay_to_time,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,
					receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip,
					receiver_area,receiver_ring,receiver_dtb,warehouse_type,warehouse_id,invoice_type,invoice_title,invoice_content,
					logistics_id,to_deliver_time,P_TradeID,NOW(),id_card,trade_from,trade_type
				FROM sales_trade
				WHERE trade_id=P_TradeID);
				
				SET V_TradeID2 = LAST_INSERT_ID();
				
				--  num,actual_num,discount,share_amount,share_post,paid,weight
				IF V_Num2=1 THEN -- 只有一件
					UPDATE sales_trade_order SET trade_id=V_TradeID2 WHERE rec_id=V_OrderID;
				ELSEIF V_Num=V_I THEN	-- 最后一件, 直接移到新订单中
					SET V_SDiscount=V_ODiscount-TRUNCATE((V_ODiscount/V_Num),4)*(V_I-1);
					SET V_SShareAmount=V_OShareAmount-TRUNCATE((V_OShareAmount/V_Num),4)*(V_I-1);
					SET V_SSharePost=V_OSharePost-TRUNCATE((V_OSharePost/V_Num),4)*(V_I-1);
					SET V_SPaid=V_OPaid-TRUNCATE((V_OPaid/V_Num),4)*(V_I-1);
					SET V_SWeight=V_OWeight-TRUNCATE((V_OWeight/V_Num),4)*(V_I-1);
					SET V_SCommission=V_OCommission-TRUNCATE((V_OCommission/V_Num),4)*(V_I-1);
					
					UPDATE sales_trade_order SET trade_id=V_TradeID2,num=GREATEST(num-V_I+1,0),actual_num=(V_Num-V_I+1),
						discount=V_SDiscount,share_amount=V_SShareAmount, share_post=V_SSharePost,
						paid=V_SPaid,weight=V_SWeight,commission=V_SCommission
					WHERE rec_id=V_OrderID;
				ELSE
					SET V_SDiscount=TRUNCATE(V_ODiscount/V_Num,4);
					SET V_SShareAmount=TRUNCATE(V_OShareAmount/V_Num,4);
					SET V_SSharePost=TRUNCATE(V_OSharePost/V_Num,4);
					SET V_SPaid=TRUNCATE(V_OPaid/V_Num,4);
					SET V_SWeight=TRUNCATE(V_OWeight/V_Num,4);
					SET V_SCommission=TRUNCATE(V_OCommission/V_Num,4);

					-- INSERT
					INSERT INTO sales_trade_order(trade_id,spec_id,platform_id,shop_id,src_oid,src_tid,bind_oid,suite_id,gift_type,
						refund_status,guarantee_mode,num,actual_num,price,order_price,share_price,discount,share_amount,
						share_post,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_amount,
						suite_num,is_print_suite,api_goods_name,api_spec_name,weight,goods_type,flag,stock_reserved,large_type,invoice_type,invoice_content,
						delivery_term,tax_rate,from_mask,is_allow_zero_cost,commission,remark,created)
					SELECT V_TradeID2,spec_id,platform_id,shop_id,src_oid,src_tid,bind_oid,suite_id,gift_type,
						refund_status,guarantee_mode,1,1,price,order_price,share_price,
						V_SDiscount,V_SShareAmount,V_SSharePost,V_SPaid,
						goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_amount,
						suite_num,is_print_suite,api_goods_name,api_spec_name,V_SWeight,goods_type,flag,stock_reserved,2,invoice_type,invoice_content,
						delivery_term,tax_rate,from_mask,is_allow_zero_cost,V_SCommission,remark,NOW()
					FROM sales_trade_order
					WHERE rec_id=V_OrderID;

					SELECT LAST_INSERT_ID() INTO V_OrderIdTmp;
		
					/*INSERT IGNORE INTO sales_trade_order_ext(order_id,batch_id,created)									
					SELECT V_OrderIdTmp,batch_id,NOW()
					FROM sales_trade_order_ext
					WHERE order_id = V_OrderID;*/
					
				END IF;
				
				-- 新订单执行完整性更新
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID2,32|IF(@cfg_sales_split_record_package_num,16,0)|IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, 0);

				-- 新订单刷新库存
				-- CALL I_RESERVE_STOCK(V_TradeID2,IF(P_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);

				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message,created) VALUES(V_TradeID2,P_OperatorID,1,0,'客户下单', V_TradeTime);
				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message,created) VALUES(V_TradeID2,P_OperatorID,2,0,'客户付款', V_PayTime);
				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message) VALUES(V_TradeID2,P_OperatorID,3,4,'自动拆分大件');

				SET V_TradeStatus = P_ToStatus;
				
				-- 按货品指定仓库拆分
				IF V_WarehouseSubType=0 AND @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 THEN
					IF EXISTS (SELECT 1 FROM sales_trade_order sto,cfg_goods_warehouse cgw WHERE sto.trade_id = V_TradeID2 AND sto.actual_num > 0 
						AND cgw.spec_id=sto.spec_id AND (cgw.shop_id = 0 OR cgw.shop_id = V_ShopID)) THEN
						
						CALL I_DL_SPLIT_GOODS_BY_WAREHOUSE(P_OperatorID,V_TradeID2,V_bAllSplit,V_WarehouseID,V_WarehouseSubType); 

					END IF;
				END IF;
				-- 开启预订单转审核配置
				/*SET V_bAllSplit = 0;
				IF @cfg_preorder_split_to_order_condition AND P_ToStatus = 25 THEN
					CALL I_DL_PREORDER_SPLIT_TO_ORDER(P_OperatorID, V_TradeID2, 30,V_bAllSplit, P_Split);
					IF V_bAllSplit THEN
						UPDATE sales_trade SET trade_status = 30 WHERE trade_id = V_TradeID2;
						-- 新订单刷新库存
						CALL I_RESERVE_STOCK(V_TradeID2,3, V_WarehouseID, V_WarehouseID);
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID2,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核')));
						SET V_TradeStatus  = 30;
					END IF;
				END IF;

				
				-- 按仓库地址库存进行拆分 @tmp_trade_changed 指订单是否按指定货品仓库拆分过
				SET V_bAllSplit = 0;
				IF V_TradeStatus = 30 AND @cfg_order_auto_split_by_ware_addr_stock AND @tmp_trade_changed=0 THEN
					CALL I_DL_SPLIT_GOODS_BY_WARE_ADDR_STOCK(P_OperatorID,V_TradeID2,V_bAllSplit,V_WarehouseID,V_WarehouseSubType);
				END IF;
				
				IF V_TradeStatus = 30 AND V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 AND NOT EXISTS(SELECT 1 FROM sales_trade_warehouse WHERE trade_id = V_TradeID2) THEN
					CALL I_DL_DECIDE_WAREHOUSE_LIST(V_TradeID2,V_WarehouseID);
				END IF;
				IF V_TradeStatus = 30 AND V_IsHoldEnabled = 1 AND V_WarehouseSubType = 1 THEN
					UPDATE sales_trade SET trade_status = 27 WHERE trade_id = V_TradeID2;
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID2,P_OperatorID,14,'进入待抢单');
				ELSEIF V_TradeStatus = 30 AND @cfg_auto_check_is_open THEN
					INSERT INTO tbl_deliver_auto_check(trade_id) VALUES(V_TradeID2);
				END IF;*/
				
				-- 还原主单仓库
				SET V_WarehouseID = V_OldWarehouseID;
				SET V_WarehouseSubType = V_OldWarehouseSubType;
				SET P_Split=1;
				
				SET V_I=V_I+1;
				SET V_LoopCount=V_LoopCount-1;
				IF V_LoopCount<=0 AND V_Num2>1 AND V_Num>V_I-1 THEN	-- 最后一单
					SET V_SDiscount=V_ODiscount-TRUNCATE((V_ODiscount/V_Num),4)*(V_I-1);
					SET V_SShareAmount=V_OShareAmount-TRUNCATE((V_OShareAmount/V_Num),4)*(V_I-1);
					SET V_SSharePost=V_OSharePost-TRUNCATE((V_OSharePost/V_Num),4)*(V_I-1);
					SET V_SPaid=V_OPaid-TRUNCATE((V_OPaid/V_Num),4)*(V_I-1);
					SET V_SWeight=V_OWeight-TRUNCATE((V_OWeight/V_Num),4)*(V_I-1);
					SET V_SCommission=V_OCommission-TRUNCATE((V_OCommission/V_Num),4)*(V_I-1);
					
					UPDATE sales_trade_order SET num=GREATEST(num-V_I+1,0),actual_num=(V_Num-V_I+1),
						discount=V_SDiscount,share_amount=V_SShareAmount, share_post=V_SSharePost,
						paid=V_SPaid,weight=V_SWeight,commission=V_SCommission
					WHERE rec_id=V_OrderID;
					
					IF P_IsLarge = 0 THEN
						SET P_IsLarge = 1;
					END IF;
				END IF;
				
			END WHILE;
			
			IF V_LoopCount=0 THEN
				LEAVE LARGE_GOODS_LABEL;
			END IF;
			
		END LOOP;
		CLOSE large_goods_cursor1;
		
		-- 原订单拆分标记
		UPDATE sales_trade SET split_from_trade_id=P_TradeID WHERE trade_id=P_TradeID;
	ELSEIF V_LargeCount1=1 AND P_IsLarge=0 THEN
		SET P_IsLarge = 1;
	END IF;
	IF V_TradeID2 THEN
	  -- 刷新订单
	  CALL I_DL_REFRESH_TRADE(P_OperatorID,P_TradeID,3,0);
		INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message) VALUES(P_TradeID,P_OperatorID,21,0,'自动拆分大件,重新确定物流公司');
	END IF;
	
END//
DELIMITER ;