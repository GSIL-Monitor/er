DROP PROCEDURE IF EXISTS `I_SALES_CREATE_LOGISTICS_SYNC`;
DELIMITER //
CREATE PROCEDURE `I_SALES_CREATE_LOGISTICS_SYNC`(IN `P_TradeId` INT, IN `P_StockoutId` INT, IN `P_IsOnline` INT)
    SQL SECURITY INVOKER
    COMMENT '订单发货后生成待物流同步记录'
MAIN_LABEL:BEGIN
/*
	算法:
	0 线下订单 -- 不考虑
	1 订单没有被拆分过 -- 全部发货
	2 订单被拆分过
		2.1 订单包括某完整的子订单 -- 全部发货
		2.2 订单部分包括某子订单
			2.2.1 开启部分发货, 且平台支持	-- 部分发货
			2.2.2 不开启部分发货
				2.2.2.1 主订单决定是否发货 -- 全部发货
				2.2.2.2 只发一个即可发货   -- 全部发货
				2.2.2.3 完全发货才可发货   -- 全部发货
	考虑到效率, 算法等价转化为:
	0 线下订单 -- 不考虑
	1 订单没有被拆分过 -- 全部发货
	2 订单被拆分过
		2.1 不开启部分发货, 或平台不支持
			2.1.1 主订单决定是否发货  -- 完全发货
			2.1.2 只发一个即可发货  -- 完全发货
			2.1.3 全部发货才可发货  -- 完全发货
		2.2 开启部分发货, 且平台支持
			2.2.1 销售订单包括完整原始单 -- 完全发货
			2.2.2 销售订单包括部分原始单 -- 部分发货
*/
	DECLARE V_NOT_FOUND, V_OrderId, V_TradePlatformId, V_ShopId, V_OrderPlatformId, V_LogisticsId,V_SplitFromTradeID, V_IsMaster, V_HasSend, V_HasNoSend, 
		V_IsPart, V_DeliveryTerm,V_LastSyncID,V_LastPlatformID, V_SyncStatus,V_OrderStatus,V_Finish,V_MaxLength INT DEFAULT(0);
	DECLARE V_Tid, V_LastTid, V_Oid, V_LogisticsNo, V_OriginalOid,
		V_ReceiverDistrict,V_LastOid, V_MagicData VARCHAR(60);
	DECLARE V_Description, V_TradeTid, V_Oids, V_OrderIds VARCHAR(1024);
	DECLARE V_Now DATETIME;
	DECLARE order_cursor CURSOR FOR SELECT sto.rec_id,ato.`status`,sto.platform_id,sto.shop_id,sto.src_oid,sto.src_tid,sto.is_master
		FROM sales_trade_order sto 
			LEFT JOIN api_trade ax ON ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid
			LEFT JOIN api_trade_order ato ON ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid AND ato.tid = sto.src_tid
		WHERE sto.trade_id=P_TradeId AND sto.actual_num>0 AND sto.platform_id>0 
		ORDER BY sto.shop_id,sto.src_tid,sto.src_oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		SET @sys_code=99;
		SET @sys_message = '未知错误';
		RESIGNAL;
	END ;
	
	SET @sys_code = 0;
	SET @sys_message='OK'; 
	SET V_Now = NOW();
	SET V_MaxLength = 208;
	SET V_MagicData = '!!!!';
	
	CALL SP_UTILS_GET_CFG_INT('order_logistics_sync_time', @cfg_order_logistics_sync_time, 2); -- 0主子订单发货 1任意子订单发货 2全部子订单发货
	CALL SP_UTILS_GET_CFG_INT('order_allow_part_sync', @cfg_order_allow_part_sync, 0);
	SET V_NOT_FOUND = 0;
	
	SELECT src_tids, platform_id, split_from_trade_id,delivery_term 
	INTO V_TradeTid, V_TradePlatformId, V_SplitFromTradeID,V_DeliveryTerm 
	FROM sales_trade
	WHERE trade_id=P_TradeId;
	
	SELECT logistics_id, logistics_no INTO V_LogisticsId, V_LogisticsNo FROM stockout_order WHERE stockout_id=P_StockoutId;
	
	IF V_NOT_FOUND THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 0 线下订单, 不考虑
	IF V_TradePlatformId=0 THEN
		-- 电子面单
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 只有淘宝支持在线发货
	IF V_TradePlatformId<>1 AND V_TradePlatformId<>2 THEN
		SET P_IsOnline = 0;
	ELSEIF P_IsOnline THEN
		SET V_SyncStatus = -3;
	END IF;
	
	SET V_Finish=1; -- 判断原始单是否完成
	-- 1 对于没有拆分的订单 -- 完全发货
	IF V_SplitFromTradeID=0 THEN
		SET V_LastTid = '', V_LastPlatformID=0;
		OPEN order_cursor;
		ORDER_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH order_cursor INTO V_OrderId, V_OrderStatus, V_OrderPlatformId, V_ShopId, V_Oid, V_Tid, V_IsMaster;
			IF V_NOT_FOUND THEN
				LEAVE ORDER_LABEL;
			END IF;
			
			IF V_OrderStatus<70 THEN
				SET V_Finish = 0;
			ELSE -- 原始单处于完成状态,不需要发货
				ITERATE ORDER_LABEL;
			END IF;
			
			IF V_Tid<>'' AND (V_Tid<>V_LastTid OR V_OrderPlatformId<>V_LastPlatformID) THEN
				INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,
					delivery_term, is_part_sync, description, sync_status, consign_time, created)
				VALUES (V_OrderPlatformId, V_ShopId, V_Tid, '', P_StockoutId, P_TradeId, V_OrderId, V_LogisticsId, V_LogisticsNo,						
					V_DeliveryTerm, 0, '订单没有被拆分过, 全部发货', V_SyncStatus, V_Now, V_Now) 
				ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
					is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;

				SET V_LastSyncID = LAST_INSERT_ID();
			END IF;
			
			SET V_LastTid = V_Tid, V_LastPlatformID=V_OrderPlatformId;
		END LOOP;
		CLOSE order_cursor;
		
		IF V_Finish THEN
			-- 标记原始单已完成
			UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE stockout_id=P_StockoutId;
		END IF;
		
		/*
			if the data is migrated from v1.0, the src_tid and src_oid in the sales_trade_order are NULL.
			use src_tids in the sales_trade instead
		*/
		IF V_Tid = '' THEN
			TRADE_LABLE:LOOP
				IF LENGTH(V_TradeTid) = 0 THEN
					LEAVE TRADE_LABLE;
				END IF;
				SET V_Tid = SUBSTRING_INDEX(V_TradeTid, ',', 1);
				SET V_TradeTid = SUBSTRING(V_TradeTid, LENGTH(V_Tid) + 2);
				
				SELECT shop_id INTO V_ShopId from sales_trade where trade_id=P_TradeId;
					INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,
						delivery_term, is_part_sync, description,sync_status, consign_time, created)
					VALUES (V_TradePlatformId, V_ShopId, V_Tid, '', P_StockoutId, P_TradeId, V_OrderId, V_LogisticsId, V_LogisticsNo,						
						V_DeliveryTerm, 0, '订单没有被拆分过, 全部发货',V_SyncStatus, V_Now, V_Now) 
				ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
					is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
				
				SET V_LastSyncID = LAST_INSERT_ID();
			END LOOP;
		
		END IF;
		
		
		UPDATE api_logistics_sync SET is_last=1 WHERE rec_id=V_LastSyncID;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 2 以下都是考虑有过拆分的订单
	
	-- 2.1 不开启部分发货, 或平台不支持
	IF (V_TradePlatformId NOT IN(1,9,17)) OR @cfg_order_allow_part_sync=0 THEN
	
		SET V_LastTid = '', V_LastPlatformID=0;
		OPEN order_cursor;
		ORDER_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH order_cursor INTO V_OrderId, V_OrderStatus, V_OrderPlatformId, V_ShopId, V_Oid, V_Tid, V_IsMaster;
			IF V_NOT_FOUND THEN
				LEAVE ORDER_LABEL;
			END IF;
			
			IF V_OrderStatus<70 THEN
				SET V_Finish = 0;
			ELSE
				ITERATE ORDER_LABEL;
			END IF;
			
			-- 2.1.1 主订单决定是否发货  -- 完全发货
			IF @cfg_order_logistics_sync_time=0 THEN
				IF V_IsMaster>0 THEN
					
					INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,								
						delivery_term,is_part_sync, description,sync_status, consign_time, created)
					VALUES (V_OrderPlatformId, V_ShopId, V_Tid, V_Oid, P_StockoutId, P_TradeId, V_OrderId, V_LogisticsId, V_LogisticsNo,						
						V_DeliveryTerm, 0, '包含主子订单, 全部发货',V_SyncStatus, V_Now, V_Now) 
					ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
						is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
					
					SET V_LastSyncID = LAST_INSERT_ID();
				END IF;
			-- 2.1.2 只发一个即可发货  -- 完全发货
			ELSEIF @cfg_order_logistics_sync_time=1 THEN
				IF V_Tid<>V_LastTid OR V_OrderPlatformId<>V_LastPlatformID THEN
					SELECT COUNT(*), oids INTO V_HasSend,V_OriginalOid FROM api_logistics_sync WHERE tid=V_Tid AND shop_id=V_ShopId LIMIT 1;
					IF V_HasSend=0 THEN
						INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,							    
							delivery_term,is_part_sync, description,sync_status, consign_time, created)
						VALUES (V_OrderPlatformId, V_ShopId, V_Tid, V_Oid, P_StockoutId, P_TradeId, V_OrderId, V_LogisticsId, V_LogisticsNo,							
							V_DeliveryTerm, 0, '有子订单发货, 全部发货', V_SyncStatus,V_Now, V_Now);
						SET V_LastSyncID = LAST_INSERT_ID();
						
					ELSE
						-- if the trade is rejected, the trade needs to be resync
						-- the oids cannot be omitted, because if oids not used, we cannot judge the order is rejected or another order
						IF V_OriginalOid = V_Oid THEN
							UPDATE api_logistics_sync set is_need_sync=1,is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId 
							WHERE tid=V_Tid AND shop_id=V_ShopId;
							SET V_LastSyncID = LAST_INSERT_ID();
						END IF;
					END IF;
					
				END IF;
			-- 2.1.3 完全发货才可发货  -- 完全发货
			ELSE
				IF V_Tid<>V_LastTid OR V_OrderPlatformId<>V_LastPlatformID THEN
					SELECT COUNT(1) INTO V_HasNoSend 
					FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
					WHERE sto.shop_id=V_ShopId AND sto.src_tid=V_Tid AND
						sto.trade_id<>P_TradeId AND sto.actual_num>0 AND st.trade_status<95;
					
					IF V_HasNoSend=0 THEN
						INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,									
							delivery_term,is_part_sync, description, sync_status,consign_time, created)
						VALUES (V_OrderPlatformId, V_ShopId, V_Tid, '', P_StockoutId, P_TradeId, V_OrderId, V_LogisticsId, V_LogisticsNo,								
							V_DeliveryTerm, 0, '全部子订单都已发货, 全部发货', V_SyncStatus,V_Now, V_Now) 
						ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
							is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
						
						SET V_LastSyncID = LAST_INSERT_ID();
					END IF;	
				END IF;
				
			END IF;
			
			SET V_LastTid = V_Tid, V_LastPlatformID=V_OrderPlatformId;
		END LOOP;
		CLOSE order_cursor;
		
		IF V_Finish THEN
			-- 标记原始单已完成
			UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE stockout_id=P_StockoutId;
		END IF;
		
		UPDATE api_logistics_sync SET is_last=1 WHERE rec_id=V_LastSyncID;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 2.2 开启部分发货, 且平台支持
	SET V_LastTid = '';
	SET V_Oids = '', V_LastOid='';
	SET V_OrderIds = '';
	OPEN order_cursor;
	ORDER_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH order_cursor INTO V_OrderId, V_OrderStatus, V_OrderPlatformId, V_ShopId, V_Oid, V_Tid, V_IsMaster;
		IF V_NOT_FOUND THEN
			LEAVE ORDER_LABEL;
		END IF;
		
		IF V_OrderStatus<70 THEN
			SET V_Finish = 0;
		ELSE
			ITERATE ORDER_LABEL;
		END IF;
		
		IF V_Tid<>V_LastTid AND V_LastTid<>'' THEN
			-- 判断是否要拆分发货
			
			IF V_IsPart=0 THEN
				INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,									   
					delivery_term,is_part_sync, description, sync_status,consign_time, created)
				VALUES (V_OrderPlatformId, V_ShopId, V_LastTid, '', P_StockoutId, P_TradeId, V_OrderIds, V_LogisticsId, V_LogisticsNo,						
					V_DeliveryTerm, 0, '该订单包含原始单中所有的子订单, 全部发货', V_SyncStatus,V_Now, V_Now) 
				ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
					is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
				
				SET V_LastSyncID = LAST_INSERT_ID();
			ELSE
				IF LENGTH(V_Oids) > V_MaxLength THEN
					SET V_Oids = CONCAT(V_MagicData, NOW());
				END IF;
				
				INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,									   
					delivery_term,is_part_sync, description, sync_status,consign_time, created)
				VALUES (V_OrderPlatformId, V_ShopId, V_LastTid, V_Oids, P_StockoutId, P_TradeId, V_OrderIds, V_LogisticsId, V_LogisticsNo,						
					V_DeliveryTerm, 1, '该订单包含原始单的部分子订单, 部分发货',V_SyncStatus, V_Now, V_Now) 
				ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
					is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
				
				SET V_LastSyncID = LAST_INSERT_ID();
			END IF;
			
			SET V_Oids = '';
			SET V_OrderIds = '';
		END IF;
		IF V_Tid<>V_LastTid THEN
			SET V_IsPart = 0;
			SELECT 1 INTO V_IsPart FROM sales_trade_order 
			WHERE src_tid=V_Tid AND shop_id=V_ShopId AND (trade_id<>P_TradeId OR actual_num=0) LIMIT 1;
		END IF;
		SET V_LastTid = V_Tid;
		IF V_IsPart AND NOT EXISTS(SELECT 1 FROM api_logistics_sync WHERE tid = V_Tid AND shop_id = V_ShopId AND FIND_IN_SET(V_Oid,oids))  THEN
			IF V_Oids = '' THEN
				SET V_Oids = V_Oid;
				SET V_OrderIds = V_OrderId;
			ELSE
				IF V_LastOid <> V_Oid  THEN
					SET V_Oids = CONCAT(V_Oids, ',', V_Oid);
				END IF;
				SET V_OrderIds = CONCAT(V_OrderIds, ',', V_OrderId);
			END IF;
			SET V_LastOid = V_Oid;
		END IF;
	END LOOP;
	CLOSE order_cursor;
	
	IF V_Finish THEN
		-- 标记原始单已完成
		UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE stockout_id=P_StockoutId;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 2.2 的补充, 循环遗漏了最后一个tid, 再给补上
	-- 判断是否要拆分发货
	-- 如果拆分过，或有部分退款的
	IF V_IsPart=0 THEN
		INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,							   
			delivery_term,is_part_sync, description,sync_status, consign_time, created)
		VALUES (V_OrderPlatformId, V_ShopId, V_LastTid, '', P_StockoutId, P_TradeId, V_OrderIds, V_LogisticsId, V_LogisticsNo,				
			V_DeliveryTerm, 0, '该订单包含原始单中所有的子订单, 全部发货',V_SyncStatus, V_Now, V_Now) 
		ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
			is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
		
		SET V_LastSyncID = LAST_INSERT_ID();
	ELSE
		IF LENGTH(V_Oids) > V_MaxLength THEN
			SET V_Oids = CONCAT(V_MagicData, NOW());
		END IF;
		INSERT INTO api_logistics_sync(platform_id, shop_id, tid, oids, stockout_id, trade_id, order_ids, logistics_id, logistics_no,							   
			delivery_term,is_part_sync, description, sync_status,consign_time, created)
		VALUES (V_OrderPlatformId, V_ShopId, V_LastTid, V_Oids, P_StockoutId, P_TradeId, V_OrderIds, V_LogisticsId, V_LogisticsNo,				
			V_DeliveryTerm,1, '该订单包含原始单的部分子订单, 部分发货', V_SyncStatus, V_Now, V_Now) 
		ON DUPLICATE KEY UPDATE is_need_sync=IF(logistics_no<>V_LogisticsNo OR logistics_id<>V_LogisticsId,1,is_need_sync),
			is_online=P_IsOnline,logistics_no=V_LogisticsNo,shop_id=V_ShopId,logistics_id=V_LogisticsId;
		
		SET V_LastSyncID = LAST_INSERT_ID();
	END IF;
	
	UPDATE api_logistics_sync SET is_last=1 WHERE rec_id=V_LastSyncID;
END//
DELIMITER ;