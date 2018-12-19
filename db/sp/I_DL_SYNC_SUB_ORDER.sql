DROP PROCEDURE IF EXISTS `I_DL_SYNC_SUB_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_SUB_ORDER`(IN `P_OperatorID` INT,
	IN `P_RecID` BIGINT,
	IN `P_ModifyFlag` INT,
	IN `P_ApiTradeStatus` TINYINT,
	IN `P_ShopID` INT,
	IN `P_Tid` VARCHAR(40),
	IN `P_Oid` VARCHAR(40),
	IN `P_RefundStatus` TINYINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_DeliverTradeID,V_WarehouseID,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,V_IsMaster,V_Exists,V_NOT_FOUND,V_FlagID,V_SalesExists,V_CustomerID,V_BadReason,V_ConsignStatus INT DEFAULT(0);
	DECLARE V_SalesGoodsCount,V_LeftSharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_HasSendGoods,V_TradeStatus,V_ChangeStatus TINYINT DEFAULT(0);
	
	DECLARE V_ChangeGoodsMsg VARCHAR(256);
	DECLARE trade_order_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id,st.customer_id,st.bad_reason
		FROM sales_trade_order sto LEFT JOIN sales_trade st ON (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=P_ShopID and sto.src_oid=P_Oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	CALL I_DL_TMP_SUITE_SPEC();
	START TRANSACTION;  
	-- 判断sales_trade_order表中是否有该子订单
  	SET V_SalesExists=0;
	SELECT 1  INTO V_SalesExists FROM sales_trade_order sto LEFT JOIN sales_trade st ON (st.trade_id=sto.trade_id) WHERE sto.shop_id=P_ShopID and sto.src_tid=P_Tid and  sto.src_oid=P_Oid LIMIT 1;
	IF V_SalesExists=0 THEN
		UPDATE api_trade_order SET modify_flag=0 WHERE shop_id=P_ShopID and tid=P_Tid and oid=P_Oid;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	-- status变化
	-- 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	IF P_ModifyFlag & 1 THEN
		IF P_ApiTradeStatus=80 OR P_ApiTradeStatus=90 THEN	-- 关闭
			
			-- 判断是否有主子订单
			SELECT MAX(is_master) INTO V_IsMaster FROM sales_trade_order
			WHERE shop_id=P_ShopID AND src_oid=P_Oid;
			
			IF P_ApiTradeStatus=80 THEN
				CALL I_DL_PUSH_REFUND(P_OperatorID, P_ShopID, P_Tid);
			END IF;
			
			-- 要轮循处理单,可能已经发货，需要拦截
			OPEN trade_order_by_api_cursor;
			TRADE_ORDER_BY_API_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID,V_CustomerID,V_BadReason;
				IF V_NOT_FOUND THEN
					LEAVE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				IF V_TradeStatus>=95 THEN -- 发货了,?有可能售后
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid,',订单已发货'));
					ITERATE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				IF V_TradeStatus>=95 THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
					VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单',IF(P_ApiTradeStatus=80, '退款:','关闭:'),P_Oid,',订单已发货'));
					ITERATE TRADE_ORDER_BY_API_LABEL;
				ELSEIF V_TradeStatus >= 40 THEN
					-- 如果客户没有处理过退款
					IF EXISTS(SELECT 1 FROM sales_trade_order WHERE shop_id=P_ShopID AND 
						src_tid=P_Tid AND src_oid=P_Oid AND trade_id=V_DeliverTradeID AND actual_num>0) THEN
						
						SELECT stockout_id INTO V_StockoutID FROM stockout_order 
						WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
						
						IF V_NOT_FOUND=0 THEN
							UPDATE stockout_order SET block_reason=(block_reason|2) WHERE stockout_id=V_StockoutID;
							UPDATE stalls_less_goods_detail SET block_reason=(block_reason|2) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
							-- 出库单日志(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,'子订单退款,拦截出库单');
						END IF;
						
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
						
						UPDATE sales_trade SET bad_reason=(bad_reason|64),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
					ELSE
						ITERATE TRADE_ORDER_BY_API_LABEL;
					END IF;
				END IF;
				
				-- 更新平台库存变化
				-- 单品
				INSERT INTO sys_process_background(`type`,object_id)
				SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND actual_num>0;
				
				-- 组合装
				INSERT INTO sys_process_background(`type`,object_id)
				SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
				WHERE sto.trade_id=V_DeliverTradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
				
				-- 回收库存,记录日志
				INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
				(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'子订单退款，释放库存' FROM sales_trade_order sto 
				LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE sto.stock_reserved>=2 AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND sto.trade_id=V_DeliverTradeID AND sto.actual_num>0 );

				INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
				(SELECT V_WarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
					IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
				FROM sales_trade_order 
				WHERE shop_id=P_ShopID AND src_oid=P_Oid AND trade_id=V_DeliverTradeID AND actual_num>0 ORDER BY spec_id)
				ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
					sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
				
				UPDATE sales_trade_order
				SET actual_num=0,stock_reserved=0,refund_status=IF(P_ApiTradeStatus=80,5,6),
					remark=IF(P_ApiTradeStatus=80, '退款','关闭')
				WHERE shop_id=P_ShopID AND src_oid=P_Oid AND trade_id=V_DeliverTradeID AND actual_num>0;
				
				-- 看当前订单还有没非赠品货品
				SET V_HasSendGoods=0;
				SELECT 1 INTO V_HasSendGoods FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND actual_num>0 AND gift_type=0 LIMIT 1;
				IF V_HasSendGoods THEN
					CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,6,2)|@cfg_calc_logistics_by_weight, 0);
				ELSE -- 除赠品之前没其它货品
					-- 取消赠品
					INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
					(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'子订单赠品退款，释放库存' FROM sales_trade_order sto 
					LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE sto.trade_id=V_DeliverTradeID AND sto.stock_reserved>=2 AND sto.gift_type>0 );

					INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
					(SELECT V_WarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
						IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
					FROM sales_trade_order
					WHERE trade_id=V_DeliverTradeID AND stock_reserved>=2 AND gift_type>0 ORDER BY spec_id)
					ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
						sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
					
					UPDATE sales_trade_order
					SET actual_num=0,stock_reserved=0,refund_status=5,
						remark=IF(P_ApiTradeStatus=80, '退款','关闭')
					WHERE trade_id=V_DeliverTradeID AND stock_reserved>=2 AND gift_type>0;
					
					CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,6,2)|@cfg_calc_logistics_by_weight, 5);
				END IF;
				
				IF P_ApiTradeStatus=80 THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,15,CONCAT('子订单退款:',P_Oid));
				ELSE
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid));
				END IF;
				
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
				-- 开启拦截赠品的配置
			IF @cfg_sales_trade_refund_block_gift THEN
				-- 拦截已审核前的包含赠品的订单
				 --  先判断赠品的原始子订单是否已经记录来源原始单号(过渡期)
				 IF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=25 AND st.trade_status < 55 
					AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND ato.invoice_content='') THEN
					UPDATE sales_trade st,sales_trade_order sto 
					SET st.bad_reason = (st.bad_reason | 512)
					WHERE st.trade_id = sto.trade_id AND st.trade_status >= 25 AND st.trade_status < 55 
					AND st.customer_id = V_CustomerID AND sto.actual_num>0 AND sto.`gift_type`>0 AND (st.bad_reason&512)=0;
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto
							WHERE st.trade_id = sto.trade_id  AND st.trade_status >=25 
							AND st.trade_status <55 AND st.customer_id = V_CustomerID AND sto.actual_num > 0 AND sto.gift_type >0;
					END IF;
				ELSEIF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=25 AND st.trade_status < 55 
					AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0) THEN

					UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato
					SET st.bad_reason = (st.bad_reason | 512)
					WHERE st.trade_status >= 25 AND st.trade_status < 55 
					AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
					AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto,api_trade_order ato
							WHERE st.trade_status >= 25 AND st.trade_status < 55 AND st.customer_id = V_CustomerID 
							AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
							AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
					END IF;

				END IF;
				
				--  先判断赠品的原始子订单是否已经记录来源原始单号
				 IF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=55 AND st.trade_status < 95 
					AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND ato.invoice_content='') THEN
					-- 拦截已审核后的包含赠品的订单(过渡期)
					UPDATE sales_trade st,sales_trade_order sto,stockout_order so
					SET st.bad_reason = (st.bad_reason | 512) ,so.block_reason = (so.block_reason | 2048)
					WHERE st.trade_id = sto.trade_id AND so.src_order_type = 1 AND so.src_order_id = st.trade_id
						AND st.trade_status >= 55 AND st.trade_status < 95 AND st.customer_id = V_CustomerID 
						AND (st.bad_reason&512)=0 AND sto.actual_num > 0 AND sto.gift_type > 0;
					-- 插入拦截日志
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto
							WHERE st.trade_id = sto.trade_id AND st.customer_id = V_CustomerID AND st.trade_status >=55 
							AND st.trade_status < 95 AND sto.actual_num > 0 AND sto.gift_type >0;
					END IF;
				ELSEIF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=55 AND st.trade_status < 95 
					AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0) THEN

					UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato,stockout_order so
					SET st.bad_reason = (st.bad_reason | 512),so.block_reason = (so.block_reason | 2048)
					WHERE so.src_order_type = 1 AND so.src_order_id = st.trade_id AND st.trade_status >= 55 AND st.trade_status < 95 
					AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 
					AND sto.`gift_type`>0 AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid 
					AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto,api_trade_order ato
							WHERE st.trade_status >= 55 AND st.trade_status < 95 AND st.customer_id = V_CustomerID 
							AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
							AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
					END IF;

				END IF;
				
			END IF;

			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, V_IsMaster, 1, V_LeftSharePost);
		ELSEIF P_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=P_ShopID AND src_oid=P_Oid;
			-- 判断配置是否开启，如果开启，判断子订单对应的订单中的所有子订单是否都为平台已发货并且主订单没有标记平台已发货，给他标记
			IF (@cfg_order_deliver_block_consign) THEN
				-- 判断主订单没有标记
				OPEN trade_order_by_api_cursor;
				TRADE_ORDER_BY_API_LABEL: LOOP
					SET V_NOT_FOUND=0;
					FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID,V_CustomerID,V_BadReason;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND=0;
						LEAVE TRADE_ORDER_BY_API_LABEL;
					END IF;
					IF V_DeliverTradeID IS NULL THEN -- 历史订单
						ITERATE TRADE_ORDER_BY_API_LABEL;
					END IF;				
					IF (V_BadReason&256) THEN
						ITERATE TRADE_ORDER_BY_API_LABEL;
					END IF;
					SELECT consign_status INTO V_ConsignStatus FROM stockout_order WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID LIMIT 1;
					IF V_TradeStatus<95 && !(V_ConsignStatus & 128) THEN
						-- 判断该订单下的所有子订单都是平台已发货状态
						IF NOT EXISTS (SELECT 1 FROM sales_trade_order sto WHERE sto.is_consigned =0 AND sto.trade_id=V_DeliverTradeID) THEN
							UPDATE sales_trade SET bad_reason=(bad_reason|256),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
							-- 订单日志
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截平台已发货订单');
							IF V_TradeStatus>=55 THEN
								SELECT stockout_id INTO V_StockoutID FROM stockout_order 
								WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
								UPDATE stockout_order SET block_reason=(block_reason|4096) WHERE stockout_id=V_StockoutID;
								UPDATE stalls_less_goods_detail SET block_reason=(block_reason|4096) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
								-- 出库单日志
								INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
								VALUES(2,V_StockoutID,P_OperatorID,53,'原始单已发货,拦截出库单');
							END IF;
						END IF;	
					END IF;
					
				END LOOP;
				CLOSE trade_order_by_api_cursor;				
			END IF;
			
			/*
			UPDATE sales_trade_order sto,sales_trade st
			SET st.trade_status=
				IF(EXISTS (SELECT 1 FROM sales_trade_order x WHERE x.trade_id=st.trade_id AND x.actual_num>0 AND x.is_consigned=0),90,95)
			WHERE sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND st.trade_id=sto.trade_id AND (st.trade_status=85 OR st.trade_status=90);
			*/
			UPDATE api_trade SET process_status=GREATEST(30,process_status) WHERE shop_id=P_ShopID AND tid=P_Tid AND deliver_trade_id>0;
		ELSEIF P_ApiTradeStatus=70 THEN -- 已完成
			OPEN trade_order_by_api_cursor;
			TRADE_ORDER_BY_API_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID,V_CustomerID,V_BadReason;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				-- 确认账款
				-- CALL I_VR_SALES_TRADE_CONFIRM(V_DeliverTradeID, P_ShopID, P_Tid, P_Oid);
				
				-- 日志
				/*
				SET V_Exists=0;
				-- 可能有原始单合并,有原始单没有打款
				SELECT 1 INTO V_Exists FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND is_received=0 AND share_amount>0 LIMIT 1;
				IF V_Exists=0 THEN
				*/
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=110 WHERE trade_id=V_DeliverTradeID;
						UPDATE stockout_order SET status=110,consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,80,CONCAT('客户打款,交易完成',P_Oid));
					ELSE
						UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						UPDATE stalls_less_goods_detail SET trade_status=1 WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,80,CONCAT('客户打款,订单未发货',P_Oid));
					END IF;
				/*
				ELSE
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=105 WHERE trade_id=V_DeliverTradeID;
						UPDATE stockout_order SET status=105 WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款:',P_Tid,',',P_Oid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款,订单未发货:',P_Tid,',',P_Oid));
					END IF;
				END IF;
				*/
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
			UPDATE api_trade SET process_status=GREATEST(50,process_status) WHERE shop_id=P_ShopID AND tid=P_Tid;
		END IF;
		
		SET P_ModifyFlag=P_ModifyFlag & ~1; -- trade_status
	END IF;
	
	IF (P_ModifyFlag & 2) AND P_RefundStatus<5 THEN -- 退款状态变化,此处只有退款申请，及售后退换
		
		OPEN trade_order_by_api_cursor;
		TRADE_ORDER_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID,V_CustomerID,V_BadReason;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_ORDER_BY_API_LABEL;
			END IF;
			
			IF P_RefundStatus>1 THEN
				-- 拦截出库单
				IF V_TradeStatus >= 40 AND V_TradeStatus<95 THEN
					UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
					SET so.block_reason=(so.block_reason|1)
					WHERE sto.trade_id=V_DeliverTradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid
						AND sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id AND so.stockout_id=sod.stockout_id 
						AND so.status<>5 AND (NOT @cfg_unblock_stockout_after_logistcs_sync OR (so.consign_status&8)=0);
					IF ROW_COUNT()>0 THEN
						UPDATE stalls_less_goods_detail SET block_reason=(block_reason|1) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
					END IF;
				ELSEIF V_TradeStatus>=95 THEN
					ITERATE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				-- 标记退款状态
				UPDATE sales_trade_order sto 
				SET sto.refund_status=P_RefundStatus
				WHERE sto.trade_id=V_DeliverTradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND sto.actual_num>0;
				
				-- 更新主订单退款状态
				SET V_Max=0,V_Min=0;
				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0)),SUM(actual_num) INTO V_Max,V_Min,V_SalesGoodsCount 
				FROM sales_trade_order WHERE trade_id=V_DeliverTradeID;
				
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4),V_FlagID=8;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1,V_FlagID=8;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2,V_FlagID=8;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				UPDATE sales_trade SET refund_status=V_NewRefundStatus,version_id=version_id+1,flag_id=IF(flag_id>1000,flag_id,V_FlagID) WHERE trade_id=V_DeliverTradeID;
					-- 开启拦截赠品的配置
				IF @cfg_sales_trade_refund_block_gift THEN
					-- 拦截已审核前的包含赠品的订单
					 --  先判断赠品的原始子订单是否已经记录来源原始单号(过渡期)
					 IF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=25 AND st.trade_status < 55 
						AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
						ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND ato.invoice_content='') THEN
						UPDATE sales_trade st,sales_trade_order sto 
						SET st.bad_reason = (st.bad_reason | 512)
						WHERE st.trade_id = sto.trade_id AND st.trade_status >= 25 AND st.trade_status < 55 
						AND st.customer_id = V_CustomerID AND sto.actual_num>0 AND sto.`gift_type`>0 AND (st.bad_reason&512)=0;
						IF ROW_COUNT()>0 THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
								SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
								FROM sales_trade st,sales_trade_order sto
								WHERE st.trade_id = sto.trade_id  AND st.trade_status >=25 
								AND st.trade_status <55 AND st.customer_id = V_CustomerID AND sto.actual_num > 0 AND sto.gift_type >0;
						END IF;
					ELSEIF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=25 AND st.trade_status < 55 
						AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
						ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0) THEN

						UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato
						SET st.bad_reason = (st.bad_reason | 512)
						WHERE st.trade_status >= 25 AND st.trade_status < 55 
						AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
						AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
						IF ROW_COUNT()>0 THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
								SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
								FROM sales_trade st,sales_trade_order sto,api_trade_order ato
								WHERE st.trade_status >= 25 AND st.trade_status < 55 AND st.customer_id = V_CustomerID 
								AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
								AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
						END IF;

					END IF;
					
					--  先判断赠品的原始子订单是否已经记录来源原始单号
					 IF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=55 AND st.trade_status < 95 
						AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
						ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND ato.invoice_content='') THEN
						-- 拦截已审核后的包含赠品的订单(过渡期)
						UPDATE sales_trade st,sales_trade_order sto,stockout_order so
						SET st.bad_reason = (st.bad_reason | 512) ,so.block_reason = (so.block_reason | 2048)
						WHERE st.trade_id = sto.trade_id AND so.src_order_type = 1 AND so.src_order_id = st.trade_id
							AND st.trade_status >= 55 AND st.trade_status < 95 AND st.customer_id = V_CustomerID 
							AND (st.bad_reason&512)=0 AND sto.actual_num > 0 AND sto.gift_type > 0;
						-- 插入拦截日志
						IF ROW_COUNT()>0 THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
								SELECT DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
								FROM sales_trade st,sales_trade_order sto
								WHERE st.trade_id = sto.trade_id AND st.customer_id = V_CustomerID AND st.trade_status >=55 
								AND st.trade_status < 95 AND sto.actual_num > 0 AND sto.gift_type >0;
						END IF;
					ELSEIF EXISTS(SELECT 1 FROM sales_trade st,sales_trade_order sto,api_trade_order ato WHERE st.trade_status >=55 AND st.trade_status < 95 
						AND st.customer_id=V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id=st.trade_id AND sto.gift_type>0 AND 
						ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0) THEN

						UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato,stockout_order so
						SET st.bad_reason = (st.bad_reason | 512),so.block_reason = (so.block_reason | 2048)
						WHERE so.src_order_type = 1 AND so.src_order_id = st.trade_id AND st.trade_status >= 55 AND st.trade_status < 95 
						AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 
						AND sto.`gift_type`>0 AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid 
						AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
						IF ROW_COUNT()>0 THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
								SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
								FROM sales_trade st,sales_trade_order sto,api_trade_order ato
								WHERE st.trade_status >= 55 AND st.trade_status < 95 AND st.customer_id = V_CustomerID 
								AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
								AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(P_Tid,ato.invoice_content)>0;
						END IF;

					END IF;
					
				END IF;
			ELSE -- 取消退款
				-- 判断子订单是否已经处理退款
				SET V_Exists=0;
				SELECT 1 INTO V_Exists FROM sales_trade_order
				WHERE trade_id=V_DeliverTradeID AND shop_id=P_ShopID AND src_oid=P_Oid AND actual_num=0 LIMIT 1;
				
				
				IF V_TradeStatus >= 40 AND V_Exists THEN
					-- 如果是多个订单合并,取消有问题!!!
					UPDATE stockout_order
					SET block_reason=block_reason|256
					WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;

					UPDATE stalls_less_goods_detail SET block_reason=(block_reason|256) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
					
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'取消退款,需要驳回处理');
				ELSEIF V_Exists THEN
					-- 标记退款状态
					UPDATE sales_trade_order sto 
					SET sto.refund_status=P_RefundStatus
					WHERE sto.trade_id=V_DeliverTradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid;
					
					IF V_TradeStatus=5 THEN
						UPDATE sales_trade SET trade_status=30 WHERE trade_id=V_DeliverTradeID;
						
						SET V_TradeStatus=30;
					END IF;
					
					-- 还原货品数量\金额
					-- !!!过一段时间可以改成SET sto.actual_num=sto.num,sto.weight=sto.num*gs.weight,sto.stock_reserved=0,sto.refund_status=1
					UPDATE sales_trade_order sto,goods_spec gs
					SET sto.actual_num=sto.num,sto.share_amount=IF(sto.share_amount=0,sto.share_amount2,sto.share_amount),
					sto.weight=sto.num*gs.weight,sto.stock_reserved=0,sto.refund_status=1
					WHERE sto.trade_id=V_DeliverTradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND sto.actual_num=0
						AND sto.spec_id=gs.spec_id;
					
					CALL I_RESERVE_STOCK(V_DeliverTradeID, IF(V_TradeStatus=25,5,3), V_WarehouseID, 0);
				ELSE
					IF EXISTS(SELECT 1 FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND shop_id=P_ShopID AND src_oid=P_Oid AND refund_status = 2) THEN
						UPDATE sales_trade_order sto 
						SET sto.refund_status=P_RefundStatus
						WHERE sto.trade_id=V_DeliverTradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND refund_status = 2;
					END IF;
				END IF;
				
				-- 刷新订单
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, 2, 0);
				-- 清除订单退款标记
				UPDATE sales_trade SET flag_id=IF(flag_id=8,0,flag_id) WHERE trade_id=V_DeliverTradeID AND refund_status=0;
			END IF;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, 0, 1, V_LeftSharePost);
			
			-- 日志
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,15,
				CONCAT('子订单退款:',P_Oid,'--',ELT(P_RefundStatus+1,'?','取消退款','申请退款','等待退货','等待收货','退款成功')));
			
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		CALL I_DL_PUSH_REFUND(P_OperatorID, P_ShopID, P_Tid);
		
		SET P_ModifyFlag = P_ModifyFlag & ~4;
	END IF;
	
	-- 折扣变化，这应该伴随主订单状态变化
	-- 数量变化，这个不应该发生
	
	-- 货品发生变化
	IF (P_ModifyFlag & 16) THEN -- 更换货品
		-- 更新平台货品名称
		/*UPDATE sales_trade_order sto, api_trade_order ato
		SET sto.api_goods_name=ato.goods_name,sto.api_spec_name=ato.spec_name
		WHERE sto.shop_id=P_ShopID AND sto.src_oid=P_Oid AND 
			ato.shop_id=P_ShopID AND ato.oid=P_Oid;*/
		
		OPEN trade_order_by_api_cursor;
		TRADE_ORDER_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID,V_CustomerID,V_BadReason;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_ORDER_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|128) WHERE stockout_id=V_StockoutID;
					UPDATE stalls_less_goods_detail SET block_reason=(block_reason|128) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
					-- 原始子订单标记换货
					UPDATE api_trade_order SET other_flags=1 WHERE rec_id=P_RecID;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'平台修改货品,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'平台更换货品，拦截出库');
		
			ELSE IF @cfg_order_deliver_auto_exchange=1 THEN
				CALL I_DL_SYNC_ORDER_GOODS(V_DeliverTradeID,P_ShopID,P_Oid,P_Tid,V_ChangeGoodsMsg,V_ChangeStatus);
				IF V_ChangeStatus=0 THEN 
					UPDATE sales_trade SET bad_reason=(bad_reason|128),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid,V_ChangeGoodsMsg));
				ELSE 
					UPDATE sales_trade SET bad_reason=(bad_reason|32),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid,V_ChangeGoodsMsg));
				END IF;
			ELSE 
				UPDATE sales_trade SET bad_reason=(bad_reason|32),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid,'，系统单未更换'));
			END IF;
			END IF;
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		SET P_ModifyFlag = P_ModifyFlag & ~16;
	END IF;
	
	UPDATE api_trade_order SET modify_flag=0 WHERE rec_id=P_RecID;
	COMMIT;
END//
DELIMITER ;