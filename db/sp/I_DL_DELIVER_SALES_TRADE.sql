DROP PROCEDURE IF EXISTS `I_DL_DELIVER_SALES_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_SALES_TRADE`(IN `P_OperatorID` INT, IN `P_Status` INT)
    SQL SECURITY INVOKER
    COMMENT '递交第二步'
BEGIN
	DECLARE V_CurTime, V_TradeID, V_ShopID,V_WarehouseType,V_WarehouseID,V_DeliveryTerm,V_IsSealed,
		V_TradeID2, V_WarehouseID2,V_GiftMask,V_PlatformID, V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict, 
		V_TradeStatus,V_RefundStatus, V_TradeCount, V_CheckedTradeID, V_CustomerID,V_TradeChanged,V_IsLarge,
		V_ToStatus, V_NOT_FOUND, V_bNoMerge, V_bNoSplit, V_bAllSplit,V_FreezeReasonID,
		V_LockWarehouse,V_SplitFromTradeID,V_UnmergeMask,V_GroupID INT DEFAULT(0);
	
	DECLARE V_IsSetWareByGoods INT DEFAULT(1);
	
	DECLARE V_RawTradeNO VARCHAR(40);
	DECLARE V_ReceiverArea,V_ReceiverName VARCHAR(64);
	DECLARE V_Tid,V_ReceiverAddress VARCHAR(256);
	
	DECLARE V_Receivable,V_Profit DECIMAL(19,4);
	
	DECLARE trade_cursor CURSOR FOR 
		SELECT trade_id,src_tids,shop_id,platform_id,delivery_term,customer_id,refund_status,
			receiver_name,receiver_province,receiver_city,receiver_district,
			receiver_area,receiver_address,warehouse_type,warehouse_id, 
			gift_mask,customer_id,is_sealed,freeze_reason,split_from_trade_id 
		FROM sales_trade WHERE trade_status=P_Status
		LIMIT 200;
	
	-- 待合并订单
	/*DECLARE user_trade_cursor1 CURSOR FOR 
		SELECT trade_id,warehouse_id,src_tids,unmerge_mask
		FROM sales_trade 
		WHERE trade_status=P_Status AND 
			shop_id=V_ShopID AND 
			customer_id=V_CustomerID AND 
			receiver_name=V_ReceiverName AND 
			receiver_area=V_ReceiverArea AND 
			receiver_address=V_ReceiverAddress AND 
			trade_id<>V_TradeID AND
			warehouse_type=V_WarehouseType AND
			delivery_term=1 AND
			is_sealed=0 AND 
			freeze_reason=0 AND
			split_from_trade_id = 0;
	
	-- 待合并订单
	DECLARE user_trade_cursor2 CURSOR FOR 
		SELECT st.trade_id,st.warehouse_id,st.src_tids,st.unmerge_mask
		FROM sales_trade st,cfg_shop ss
		WHERE st.trade_status=P_Status AND 
			st.platform_id=V_PlatformID AND
			ss.shop_id = st.shop_id AND
			ss.group_id  = V_GroupID AND
			st.customer_id=V_CustomerID AND 
			st.receiver_name=V_ReceiverName AND 
			st.receiver_area=V_ReceiverArea AND 
			st.receiver_address=V_ReceiverAddress AND 
			st.trade_id<>V_TradeID AND
			st.warehouse_type=V_WarehouseType AND
			st.delivery_term=1 AND
			st.is_sealed=0 AND 
			st.freeze_reason=0 AND
			st.split_from_trade_id = 0;*/
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	
	SET V_CurTime = UNIX_TIMESTAMP();
	SET @tmp_to_process_count = 0;  -- 转移到等处理的订单数
	
	/*IF P_Status=20 THEN  -- 目前没有等未付以及延迟审核处理
		-- 转移等未付到延时审核
		UPDATE sales_trade SET trade_status=16,delay_to_time=UNIX_TIMESTAMP(IF(delivery_term=1,pay_time,trade_time))+@cfg_delay_check_sec
		WHERE trade_status=15 AND delay_to_time<=V_CurTime AND UNIX_TIMESTAMP(IF(delivery_term=1,pay_time,trade_time))+@cfg_delay_check_sec>V_CurTime;
		
		-- 转移等未付到前处理队列
		UPDATE sales_trade SET trade_status=20,delay_to_time=0
		WHERE trade_status=15 AND delay_to_time<=V_CurTime;
		
		-- 转移延时审核到前处理队列
		UPDATE sales_trade SET trade_status=20,delay_to_time=0
		WHERE trade_status=16 AND delay_to_time<=V_CurTime;
	END IF;*/
	-- 轮循所有预处理订单
	OPEN trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		SET V_TradeChanged=0;
		FETCH trade_cursor INTO V_TradeID, V_Tid, V_ShopID, V_PlatformID, V_DeliveryTerm, V_CustomerID, V_RefundStatus,
			V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
			V_ReceiverArea,V_ReceiverAddress, V_WarehouseType, V_WarehouseID, 
			V_GiftMask,V_CustomerID,V_IsSealed,V_FreezeReasonID,V_SplitFromTradeID;
		
		IF V_NOT_FOUND THEN
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE trade_cursor;
				OPEN trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SELECT is_nomerge,is_nosplit,is_setwarebygoods,group_id INTO V_bNoMerge,V_bNoSplit,V_IsSetWareByGoods,V_GroupID FROM cfg_shop WHERE shop_id = V_ShopID;
		
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			SET V_bNoMerge=0;
			SET V_bNoSplit=0; 
		END IF;
		
		-- 赠品临时表
		CALL I_DL_TMP_GIFT_TRADE_ORDER();
		CALL I_DL_TMP_SUITE_SPEC();
		CALL I_DL_TMP_SALES_TRADE_ORDER_WAREHOUSE();
		START TRANSACTION;
		-- 检查订单状态，有可能合并掉了
		SELECT trade_status INTO V_TradeStatus FROM sales_trade WHERE trade_id=V_TradeID FOR UPDATE;
		IF V_NOT_FOUND OR V_TradeStatus<>P_Status THEN
			ROLLBACK;
			ITERATE TRADE_LABEL;
		END IF;
		
		-- 合并条件
		/*IF @cfg_auto_merge AND V_DeliveryTerm=1 AND V_IsSealed=0  AND V_bNoMerge=0 AND V_FreezeReasonID=0 AND V_SplitFromTradeID=0 THEN
			-- 判断订单否有锁定仓库
			SET V_LockWarehouse = 0;
			IF @cfg_chg_locked_warehouse_alert THEN
				SELECT 1 INTO V_LockWarehouse FROM api_trade WHERE platform_id=V_PlatformID AND tid=V_Tid AND x_warehouse_id=V_WarehouseID;
			END IF;
			
			IF @cfg_order_merge_mode = 0 THEN
				OPEN user_trade_cursor1;
			ELSEIF @cfg_order_merge_mode = 1 THEN
				OPEN user_trade_cursor2;
			END IF;
			
			-- 预处理订单的合并
			USER_TRADE_LABEL: LOOP
				SET V_NOT_FOUND=0;
				IF @cfg_order_merge_mode = 0 THEN
					FETCH user_trade_cursor1 INTO V_TradeID2, V_WarehouseID2, V_RawTradeNO, V_UnmergeMask;
				ELSEIF @cfg_order_merge_mode = 1 THEN
					FETCH user_trade_cursor2 INTO V_TradeID2, V_WarehouseID2, V_RawTradeNO, V_UnmergeMask;
				END IF;
				
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE USER_TRADE_LABEL;
				END IF;
				
				-- 仓库不一样
				IF V_WarehouseID2<>V_WarehouseID AND V_WarehouseID2>0 THEN
					IF @cfg_chg_locked_warehouse_alert THEN
						-- 原单不允许改仓库
						IF V_LockWarehouse THEN
							ITERATE USER_TRADE_LABEL;
						END IF;
	
						-- 被合并订单不允许改仓库
						IF EXISTS(SELECT 1 FROM sales_trade_order sto,api_trade_order ato,api_trade ax 
							WHERE sto.trade_id=V_TradeID2 AND sto.actual_num>0 AND ato.platform_id=sto.platform_id AND 
							ato.oid=sto.src_oid AND ax.platform_id=ato.platform_id AND ax.tid=ato.tid AND ax.x_warehouse_id=V_WarehouseID2) THEN
							
							ITERATE USER_TRADE_LABEL;
						END IF;
					END IF;
					
					-- 释放库存,后面重新占用
					CALL I_RESERVE_STOCK(V_TradeID2, 0, 0, V_WarehouseID2);
				END IF;
				
				-- 合并货品到到主订单
				UPDATE sales_trade_order SET trade_id=V_TradeID WHERE trade_id=V_TradeID2;
								
				-- 删除被合并的订单
				DELETE FROM sales_trade WHERE trade_id=V_TradeID2;
				-- 将订单日志也合并过来
				UPDATE sales_trade_log SET trade_id=V_TradeID,data=IF(type=1 OR type=2,-50,data) WHERE trade_id=V_TradeID2 ;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,5,CONCAT('自动合并:',V_RawTradeNO));
				
				-- 判断同名未合并
				IF (V_UnmergeMask & 2) AND
					NOT EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status>=95 AND trade_id<>V_TradeID) THEN
					UPDATE sales_trade SET unmerge_mask=(V_UnmergeMask & ~2) WHERE trade_id=V_TradeID;
				END IF;
				
				SET V_TradeChanged=1;
			END LOOP;
			
			IF @cfg_order_merge_mode = 0 THEN
				CLOSE user_trade_cursor1;
			ELSEIF @cfg_order_merge_mode = 1 THEN
				CLOSE user_trade_cursor2;
			END IF;
			
		END IF;*/
		
		SET V_ToStatus=IF(P_Status=20, 30, 25);
		
		-- 合并不重新计算赠品，则选送赠品
		IF (NOT @cfg_auto_merge_gift OR ISNULL(@cfg_auto_merge_gift))  AND V_SplitFromTradeID = 0 THEN
			IF V_TradeChanged THEN
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
				SET V_TradeChanged=0;
			END IF;

			-- 赠品处理
			IF NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND gift_type = 1) THEN
				CALL I_DL_SEND_GIFT(P_OperatorID, V_TradeID, V_CustomerID, V_TradeChanged);
				IF @sys_code THEN
					ROLLBACK;
					ITERATE TRADE_LABEL;
				END IF;
			END IF;
		END IF;
		
		
		--  待审核订单合并，限制条数，最多自动合并6条
		IF @cfg_auto_merge AND V_IsLarge<2  AND V_bNoMerge=0 AND V_FreezeReasonID=0 AND V_DeliveryTerm=1 AND V_SplitFromTradeID = 0 AND !(@cfg_auto_merge_ban_refund=1&&V_RefundStatus=1) THEN
			SET V_NOT_FOUND=0;
			IF @cfg_order_merge_mode = 0 THEN
				SELECT trade_id,unmerge_mask INTO V_CheckedTradeID,V_UnmergeMask FROM sales_trade st
				WHERE customer_id=V_CustomerID AND 
					trade_status=V_ToStatus AND 
					trade_id<>V_TradeID AND
					is_sealed=0 AND 
					delivery_term=1 AND
					split_from_trade_id=0 AND
					shop_id=V_ShopID AND 
					receiver_name=V_ReceiverName AND 
					receiver_area=V_ReceiverArea AND 
					receiver_address=V_ReceiverAddress AND 
					warehouse_id=V_WarehouseID AND 
					(trade_from=1 OR trade_from=3) AND trade_type=1 AND freeze_reason=0 AND 
					revert_reason=0 AND checkouter_id=0 AND 
					LENGTH(src_tids)-LENGTH(replace(src_tids,',',''))<5 AND 
					IF(@cfg_auto_merge_ban_refund,refund_status<>1,1) AND 
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;
			ELSE
				SELECT st.trade_id,st.unmerge_mask INTO V_CheckedTradeID,V_UnmergeMask 
				FROM sales_trade st,cfg_shop ss 
				WHERE st.customer_id=V_CustomerID AND 
					st.trade_status=V_ToStatus AND 
					st.platform_id = V_PlatformID AND 
					st.shop_id = ss.shop_id AND
					ss.group_id = V_GroupID AND
					st.trade_id<>V_TradeID AND
					st.is_sealed=0 AND 
					st.delivery_term=1 AND
					st.split_from_trade_id=0 AND
					st.receiver_name=V_ReceiverName AND 
					st.receiver_area=V_ReceiverArea AND 
					st.receiver_address=V_ReceiverAddress AND 
					st.warehouse_id=V_WarehouseID AND
					(trade_from=1 OR trade_from=3) AND 
					st.trade_type=1 AND 
					st.freeze_reason=0 AND 
					st.revert_reason=0 AND 
					st.checkouter_id=0 AND 
					LENGTH(src_tids)-LENGTH(replace(src_tids,',',''))<5 AND 
					IF(@cfg_auto_merge_ban_refund,refund_status<>1,1) AND 
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;
			END IF;
			
			IF V_NOT_FOUND=0 THEN
				-- 释放库存占用
				CALL I_RESERVE_STOCK(V_CheckedTradeID, 0, 0, V_WarehouseID);
				
				-- 如果合并订单需要重新计算赠品,则选删除原来的赠品
				IF @cfg_auto_merge_gift THEN
					-- 删除赠品原始单
					DELETE ax FROM api_trade ax, sales_trade_order sto
					WHERE ax.platform_id=0 AND ax.tid=sto.src_tid AND sto.trade_id=V_CheckedTradeID AND sto.gift_type=1;
					
					-- 删除原始单中赠品
					DELETE ato FROM api_trade_order ato, sales_trade_order sto
					WHERE ato.platform_id=0 AND ato.oid=sto.src_oid AND sto.trade_id=V_CheckedTradeID AND sto.gift_type=1;
					
					-- 删除处理单中赠品
					DELETE FROM sales_trade_order WHERE trade_id=V_CheckedTradeID AND gift_type=1;
					-- 删除使用赠品策略的记录
					SET @tmp_gift_send_num = 0;
					SELECT COUNT(1) INTO @tmp_gift_send_num FROM sales_gift_record WHERE trade_id = V_CheckedTradeID;
					IF @tmp_gift_send_num>0 THEN
						UPDATE cfg_gift_rule cgr ,(SELECT rule_id,COUNT(1) num FROM sales_gift_record WHERE trade_id = V_CheckedTradeID GROUP BY rule_id) cr
						SET cgr.history_gift_send_count = cgr.history_gift_send_count - cr.num,
						cgr.cur_gift_send_count = IF(cgr.cur_gift_send_count>=cr.num,cgr.cur_gift_send_count-cr.num,cgr.cur_gift_send_count)
						WHERE cgr.rec_id = cr.rule_id;
						DELETE FROM sales_gift_record WHERE trade_id = V_CheckedTradeID;
					END IF;
				END IF;
				
				-- 删除新订单(用老订单替代)
				DELETE FROM sales_trade WHERE trade_id=V_TradeID;
				
				-- 货品合并到新的订单
				UPDATE sales_trade_order SET trade_id=V_CheckedTradeID WHERE trade_id=V_TradeID;
				-- 合并日志
				UPDATE sales_trade_log SET trade_id=V_CheckedTradeID,data=IF(type=1 OR type=2,-50,data) WHERE trade_id=V_TradeID ;
				-- 合并便签
				-- UPDATE common_order_note SET order_id=V_CheckedTradeID WHERE type=1 AND order_id=V_TradeID;
				
				SET V_TradeID=V_CheckedTradeID;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,5,'自动合并');
				
				-- 判断同名未合并
				IF (V_UnmergeMask & 2) AND
					NOT EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status>=95 AND trade_id<>V_TradeID) THEN
					UPDATE sales_trade SET unmerge_mask=(V_UnmergeMask & ~2),flag_id=20  WHERE trade_id=V_TradeID;
				END IF;
				
				SET V_TradeChanged = 1;
			ELSE
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,14,CONCAT(IF(V_ToStatus=30,'待审核:','预订单:'),V_Tid));
			END IF;
		ELSE
			INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,14,CONCAT(IF(V_ToStatus=30,'待审核:','预订单:'),V_Tid));
		END IF;
		
		IF V_TradeChanged THEN
			CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
			SET V_TradeChanged=0;
		ELSE
			UPDATE sales_trade SET trade_status=V_ToStatus,version_id=version_id+1 WHERE trade_id=V_TradeID;
		END IF;
		
		-- 合并后需重新计算赠品
		IF @cfg_auto_merge_gift AND V_SplitFromTradeID = 0  THEN
			-- 赠品处理
			IF NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id = V_TradeID AND gift_type = 1) THEN
				CALL I_DL_SEND_GIFT(P_OperatorID, V_TradeID, V_CustomerID, V_TradeChanged);
				IF @sys_code THEN
					ROLLBACK;
					ITERATE TRADE_LABEL;
				END IF;
			END IF;
		END IF;
		
		-- 拆分不同仓库的订单
		IF @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
			IF EXISTS (SELECT 1 FROM sales_trade_order sto,cfg_goods_warehouse cgw WHERE sto.trade_id = V_TradeID and sto.actual_num > 0 
			AND cgw.spec_id=sto.spec_id AND (cgw.shop_id = 0 OR cgw.shop_id = V_ShopID)) THEN
				CALL I_DL_SPLIT_GOODS_BY_WAREHOUSE(P_OperatorID,V_TradeID,V_TradeChanged,V_WarehouseID);
			END IF;
		END IF;
		
		SET V_IsLarge=0;
		-- 拆分独立大件
		IF @cfg_order_auto_split AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
			CALL I_DL_SPLIT_LARGE_GOODS(P_OperatorID, V_TradeID, V_ToStatus, V_IsLarge, V_TradeChanged);
		END IF;
		
		-- 预订单拆分进审核
		IF @cfg_preorder_split_to_order_condition AND P_Status = 19 AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
			SET V_bAllSplit = 0;
			CALL I_DL_PREORDER_SPLIT_TO_ORDER(P_OperatorID, V_TradeID, 30,V_bAllSplit, V_TradeChanged);
			IF V_bAllSplit THEN
				UPDATE sales_trade SET trade_status = 20 WHERE trade_id = V_TradeID;
				CALL I_DL_REFRESH_TRADE(P_OperatorID,V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight,0);
				-- INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分-','库存充足的订单转审核')); 
				ITERATE TRADE_LABEL;
			END IF;
		END IF;
		
		IF V_TradeChanged THEN
			CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
		END IF;
		
		-- 占用库存
		CALL I_RESERVE_STOCK(V_TradeID, IF(V_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);
		
		-- 标记同名未合并的(不区分是否开启拦截同名未合并标记，都标记)
		-- IF @cfg_order_check_warn_has_unmerge THEN
			UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2),modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW())
			WHERE trade_status>=15 AND trade_status<=95 AND 
				customer_id=V_CustomerID AND 
				is_sealed=0 AND 
				delivery_term=1 AND
				split_from_trade_id<=0 AND
				trade_id <> V_TradeID;
			
			IF ROW_COUNT() > 0 THEN
				UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2) WHERE trade_id=V_TradeID;
			ELSE
				UPDATE sales_trade SET unmerge_mask=(unmerge_mask & ~2) WHERE trade_id=V_TradeID;
			END IF;
		-- END IF;
		
		COMMIT;
		
		SET @tmp_to_process_count = @tmp_to_process_count+1;
		CALL I_DL_REFRESH_TRADE(P_OperatorID,V_TradeID,0,0);
		-- 记录该买家的下单量，订单金额等
		SELECT receivable,profit INTO V_Receivable ,V_Profit FROM sales_trade WHERE trade_id=V_TradeID;
		UPDATE crm_customer SET 
			last_trade_time=NOW(),
			trade_count=trade_count+1,
			trade_amount=trade_amount+V_Receivable,
			profit=profit+V_Profit
		WHERE customer_id=V_CustomerID;
	END LOOP;
	CLOSE trade_cursor;
	
END//
DELIMITER ;