DROP PROCEDURE IF EXISTS `I_DL_SYNC_MAIN_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_MAIN_ORDER`(IN `P_OperatorID` INT, IN `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ModifyFlag,V_DeliverTradeID,V_WarehouseID,
		V_NewWarehouseID,V_Locked,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsType,
		V_SalesOrderCount,V_Timestamp,V_DelayToTime,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,
		V_CustomerID,V_FlagID,V_IsMaster,V_RemarkFlag,V_Exists,V_SalesExists,
		V_ShopHoldEnabled,V_OldFreeze,V_PackageID,V_RemarkCount,V_GiftMask,V_UnmergeMask,V_ConsignStatus,
		V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_ApiGoodsCount,V_ApiOrderCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,
		V_DapAmount,V_CodAmount,V_PiAmount,
		V_Paid,V_SalesGoodsCount,V_TotalWeight,V_PostCost,
		V_GoodsCost,V_ExtCodFee,V_Commission,V_PackageWeight,V_TotalVolume,V_SuiteWeight,V_SpecWeight DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_HasSendGoods,V_HasGift,V_PlatformID,V_ApiTradeStatus,V_TradeStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_InvoiceType,V_WmsType,V_NewWmsType,V_IsAutoWms,V_IsSealed,V_IsFreezed,V_IsPreorder,V_IsExternal TINYINT DEFAULT(0);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverRing VARCHAR(40);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	
	DECLARE V_SalesmanID,V_OriginSalesmanId,V_LogisticsID,V_TradeMask,V_OldLogisticsID INT;
	DECLARE V_Tid,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_ExtMsg,V_SingleSpecNO VARCHAR(40);
	DECLARE V_AreaAlias,V_BuyerEmail,V_BuyerNick,V_ReceiverName,V_ReceiverArea VARCHAR(60);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime DATETIME;
	DECLARE V_Remark,V_BuyerMessage VARCHAR(1024);
	DECLARE V_LogisticsName,V_WarehouseName,V_ShopName,V_LogisticsMatchLog,V_LogisticsFeeLog,V_PromptRemarkFlag,V_PromptRemark,V_WarehouseSelectLog,V_RemarkLog,V_RemarkLog2,V_ClientRemarkLog,V_AreaAliasLog VARCHAR(256);
	
	DECLARE trade_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id
		FROM sales_trade_order sto LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=V_ShopID AND sto.src_tid=V_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	-- 主订单变化
	-- 在调用I_DL_MAP_TRADE_GOODS的事务之前先创建临时表
	CALL I_DL_TMP_SALES_TRADE_ORDER();
	CALL I_DL_TMP_SUITE_SPEC();
	START TRANSACTION;
	
	SELECT modify_flag,platform_id,tid,trade_status,refund_status,delivery_term,guarantee_mode,deliver_trade_id,pay_time,pay_account,
		receivable,goods_amount,post_amount,other_amount,dap_amount,cod_amount,pi_amount,ext_cod_fee,paid,discount,invoice_type,
		invoice_title,invoice_content,stockout_no,trade_mask,is_sealed,wms_type,is_auto_wms,warehouse_no,shop_id,logistics_type,
		buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_area,receiver_ring,receiver_address,
		receiver_zip,receiver_telno,receiver_mobile,remark_flag,remark,buyer_message,is_external,x_salesman_id
	INTO V_ModifyFlag,V_PlatformID,V_Tid,V_ApiTradeStatus,V_RefundStatus,V_DeliveryTerm,V_GuaranteeMode,V_DeliverTradeID,V_PayTime,V_PayAccount,
		V_Receivable,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_Discount,V_InvoiceType,
		V_InvoiceTitle,V_InvoiceContent,V_StockoutNO,V_TradeMask,V_IsSealed,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_ShopID,V_LogisticsType,
		V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverArea,V_ReceiverRing,V_ReceiverAddress,
		V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_RemarkFlag,V_Remark,V_BuyerMessage,V_IsExternal,V_OriginSalesmanId
	FROM api_trade WHERE rec_id=P_ApiTradeID FOR UPDATE;
	
	-- 判断sales_trade表中是否有该订单
	SET V_SalesExists=0;
	SELECT 1 INTO V_SalesExists FROM sales_trade st LEFT JOIN sales_trade_order sto on (st.trade_id=sto.trade_id) 
	WHERE sto.shop_id=V_ShopID AND sto.src_tid=V_Tid LIMIT 1;
	IF V_SalesExists=0 THEN
		UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 订单还没递交，不需要处理变化
	IF V_DeliverTradeID=0 OR V_IsExternal THEN
		UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	-- modify_flag 1 trade_status | 2 pay_status | 4 refund_status | 8 remark | 16 address | 32 inovice | 64 warehouse | 128 buyer_message
	-- trade_status变化
	-- 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	IF V_ModifyFlag & 1 THEN
		-- 锁定订单
		SELECT trade_status,warehouse_id,logistics_id,customer_id,freeze_reason
		INTO V_TradeStatus,V_WarehouseID,V_LogisticsID,V_CustomerID,V_OldFreeze
		FROM sales_trade WHERE trade_id=V_DeliverTradeID FOR UPDATE;
		
		-- 获取仓库 
		IF V_WarehouseID = 0 THEN
			SELECT warehouse_id INTO V_WarehouseID FROM cfg_warehouse where is_disabled = 0 limit 1;
		END IF;

		-- 递交订单状态
		-- 30待发货
		--  ??????更新父订单货品数量金额等
		IF V_ApiTradeStatus = 30 THEN
			-- 5已取消 10待付款 12待尾款 15等未付 20前处理(赠品，合并，拆分) 25预订单 30待客审 35待财审 40待递交仓库 45递交仓库中 50已递交仓库 55待拣货 60待验货 65待打包 70待称重 75待出库 80待发货 85发货中 90发往配送中心 95已发货 100已签收 105部分结算 110已完成
			IF V_TradeStatus = 10 OR V_TradeStatus=12 THEN
				-- 未付款--已付款
				
				-- 款的发货 状态变化 更新到系统订单
				/*IF V_DeliveryTerm = 1 THEN
					SET V_TradeStatus = 30;
				END IF;*/

				-- 备注变化
				IF (V_ModifyFlag & 8) THEN
						SET V_Remark=TRIM(V_Remark);
						-- 判断是客服备注变化还是标旗变化
	          SELECT IF(V_Remark<>cs_remark,'客服备注变化',''),IF(V_RemarkFlag<>remark_flag,'标旗变化','') INTO V_PromptRemark,V_PromptRemarkFlag FROM sales_trade WHERE trade_id=V_DeliverTradeID;
						-- 记录备注
						INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
						IF (V_PromptRemark<>'') THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemark,':',V_Tid));
						END IF;
						IF (V_PromptRemarkFlag<>'') THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemarkFlag,':',V_Tid));
						END IF;						
				END IF;
				
				-- 释放未付款占用库存
				CALL I_RESERVE_STOCK(V_DeliverTradeID, 0, 0, V_WarehouseID);
				
				--  重新生成货品
				DELETE FROM sales_trade_order WHERE trade_id=V_DeliverTradeID;
				UPDATE api_trade_order SET process_status=10 WHERE platform_id=V_PlatformID AND tid=V_Tid;
				
				-- 映射货品
				CALL I_DL_MAP_TRADE_GOODS(V_DeliverTradeID, P_ApiTradeID, 1, V_ApiOrderCount, V_ApiGoodsCount);
				IF @sys_code THEN
					LEAVE MAIN_LABEL;
				END IF;
				
				-- 备注提取
				SET V_NewWmsType=V_WmsType,V_ExtMsg='';
				CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed,V_RemarkLog);
				
				IF V_IsPreorder THEN
					SET V_ExtMsg = ' 进预订单原因:客服备注提取';	
				END IF;
				
				-- 客户备注
				SET V_BuyerMessage=TRIM(V_BuyerMessage);
				CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_LogisticsID, V_IsFreezed,V_ClientRemarkLog);
                
                -- 选择仓库, 备注提取的优先级高
				CALL I_DL_DECIDE_WAREHOUSE(
					V_NewWarehouseID, 
					V_NewWmsType, 
					V_Locked, 
					V_WarehouseNO, 
					V_ShopID, 
					0, 
					V_ReceiverProvince, 
					V_ReceiverCity, 
					V_ReceiverDistrict,
					V_ShopHoldEnabled,
					V_WareHouseSelectLog);-- V_ShopHoldEnabled=0 是否抢单
				
				-- 无效仓库
				IF V_NewWarehouseID=0 THEN
					ROLLBACK;
					UPDATE api_trade SET bad_reason=4 WHERE rec_id = P_ApiTradeID;
					-- INSERT INTO aux_notification(type,message,priority,order_type,order_no) VALUES(2,'订单无可选仓库',9,1,V_Tid);
					LEAVE MAIN_LABEL;
				END IF;
				
				IF V_WarehouseID AND (V_NewWarehouseID = 0 OR NOT V_Locked) THEN
					SET V_NewWarehouseID = V_WarehouseID;
					SET V_NewWmsType = V_WmsType;
					SET V_WareHouseSelectLog='';
				END IF;
				/*
				-- 根据货品关键字转预订单处理
				IF @cfg_order_go_preorder AND NOT V_IsPreorder THEN
					SELECT 1 INTO V_IsPreorder FROM api_trade_order ato, cfg_preorder_goods_keyword cpgk 
					WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND LOCATE(cpgk.keyword,ato.goods_name)>0 LIMIT 1;
					
					IF V_IsPreorder THEN
						SET V_ExtMsg = ' 进预订单原因:平台货品名称包含关键词';	
					END IF;
				END IF;
				
				-- 是否开启了抢单
				SET V_ShopHoldEnabled = 0;
				IF @cfg_order_deliver_hold AND NOT V_IsPreorder THEN
					SELECT is_hold_enabled INTO V_ShopHoldEnabled FROM sys_shop WHERE shop_id=V_ShopID;
				END IF;
				
				-- 选择仓库, 备注提取的优先级高
				CALL I_DL_DECIDE_WAREHOUSE(
					V_NewWarehouseID, 
					V_NewWmsType, 
					V_Locked, 
					V_WarehouseNO, 
					V_ShopID, 
					0, 
					V_ReceiverProvince, 
					V_ReceiverCity, 
					V_ReceiverDistrict,
					V_ShopHoldEnabled);
				
				-- 无效仓库
				IF V_NewWarehouseID=0 THEN
					ROLLBACK;
					UPDATE api_trade SET bad_reason=4 WHERE rec_id = P_ApiTradeID;
					INSERT INTO aux_notification(type,message,priority,order_type,order_no) VALUES(2,'订单无可选仓库',9,1,V_Tid);
					LEAVE MAIN_LABEL;
				END IF;
				
				IF V_WarehouseID AND (V_NewWarehouseID = 0 OR NOT V_Locked) THEN
					SET V_NewWarehouseID = V_WarehouseID;
					SET V_NewWmsType = V_WmsType;
				END IF;
				
				-- 所选仓库为实体店仓库才去抢单
				IF V_ShopHoldEnabled THEN
					SELECT (type=127 AND sub_type=1) INTO V_ShopHoldEnabled FROM cfg_warehouse WHERE warehouse_id=V_NewWarehouseID;
				END IF;
				*/
				-- 更新GoodsCount,等
				SELECT SUM(actual_num),COUNT(DISTINCT spec_id),SUM(weight),SUM(paid),
					MAX(delivery_term),SUM(share_amount+discount),SUM(share_post),SUM(discount),
					SUM(IF(delivery_term=1,share_amount+share_post,paid)),
					SUM(IF(delivery_term=2,share_amount+share_post-paid,0)),
					BIT_OR(gift_type),SUM(commission),SUM(volume)
				INTO V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_Paid,V_DeliveryTerm,
					V_GoodsAmount,V_PostAmount,V_Discount,V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
				FROM tmp_sales_trade_order WHERE refund_status<=2 AND actual_num>0;
				
				-- 根据配置确定订单重量是否按照组合装重量计算
				IF(@cfg_order_cal_weight_by_suite) THEN 
					SELECT SUM(t.suite_weight) INTO V_SuiteWeight 
					FROM (SELECT gs.weight*tsto.suite_num suite_weight FROM tmp_sales_trade_order tsto 
						LEFT JOIN goods_suite gs ON gs.suite_no=tsto.suite_no 
						WHERE tsto.suite_no<>'' GROUP BY tsto.suite_no) t;
					IF V_SuiteWeight>0 THEN 
						SELECT IFNULL(SUM(weight),0) INTO V_SpecWeight FROM tmp_sales_trade_order WHERE suite_no='';
						SET V_TotalWeight=V_SuiteWeight+V_SpecWeight;
					END IF;
				END IF;

				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
				INTO V_Max,V_Min
				FROM tmp_sales_trade_order;
				
				-- 更新主订单退款状态
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4),V_FlagID=8;
					SET V_TradeStatus=5;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1,V_FlagID=8;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2,V_FlagID=8;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				-- 计算原始货品数量
				SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_ApiOrderCount, V_ApiGoodsCount
				FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
				FROM tmp_sales_trade_order
				WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
				GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
				
				IF V_ApiOrderCount=1 THEN
					SELECT IF(suite_id,suite_name,CONCAT(goods_name,'-',spec_name)) INTO V_SingleSpecNO 
					FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
				ELSEIF V_ApiOrderCount>1 THEN
					SET V_SingleSpecNO='多种货品';
				ELSE
					SET V_SingleSpecNO='';
				END IF;
				-- 选择物流,备注物流优化级高
				IF V_LogisticsID=0 THEN
					CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_NewWarehouseID,V_TotalWeight,
						0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress, V_Paid,V_LogisticsMatchLog);
				END IF;
				-- 估算邮费
				CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_NewWarehouseID, 
					0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsFeeLog);
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
				/*
				-- 包装
				IF @cfg_open_package_strategy THEN
					CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_TotalWeight,V_TotalVolume);
					IF V_PackageID THEN
						SELECT weight INTO V_PackageWeight FROM goods_spec WHERE spec_id = V_PackageID;
						SET V_TotalWeight=V_TotalWeight+V_PackageWeight;
					END IF;
				END IF;
				
				-- 选择物流,备注物流优化级高
				IF V_LogisticsID=0 THEN
					CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_NewWarehouseID,V_TotalWeight,
						0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress);
				END IF;
				
				-- 估算邮费
				CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_NewWarehouseID, 
					0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
				
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);

				
				-- 转预订单处理
				-- 库存不足转预订单处理
				IF  @cfg_order_go_preorder AND @cfg_order_preorder_lack_stock AND 
					NOT V_IsPreorder AND NOT V_ShopHoldEnabled AND NOT V_IsAutoWMS THEN
					
					SELECT 1 INTO V_IsPreorder FROM 
					(SELECT spec_id,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg 
						LEFT JOIN stock_spec ss on (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_NewWarehouseID)
					WHERE CASE @cfg_order_preorder_lack_stock
						WHEN 1 THEN	IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)
						ELSE IFNULL(ss.stock_num-ss.sending_num-ss.order_num-ss.subscribe_num,0)
					END < tg.actual_num LIMIT 1;
					
					IF V_IsPreorder THEN
						SET V_ExtMsg = ' 进预订单原因:订单货品存在库存不足';	
					END IF;
				END IF;
				
				
				SET V_Timestamp = UNIX_TIMESTAMP(),V_DelayToTime=0;
				
				-- 计算订单状态
				IF V_IsAutoWMS AND V_NewWmsType > 1 THEN
					SET V_TradeStatus=55;  -- 已递交仓库
					SET V_ExtMsg = ' 委外订单';
				ELSEIF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
				ELSEIF V_ShopHoldEnabled AND LENGTH(@tmp_warehouse_enough)>0 THEN
					SET V_TradeStatus=27;  -- 抢单
					-- 抢单订单物流必须是无单号
					IF V_LogisticsType<>1 THEN
						SET V_LogisticsType=1;
								
						CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_NewWarehouseID,V_TotalWeight,
							0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress);
						IF V_LogisticsID THEN
							-- 估算邮费
							CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_NewWarehouseID, 
								0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
							-- 大头笔
							CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
						ELSE
							SET V_PostCost=0, V_AreaAlias='';
						END IF;
						
					END IF;
				ELSE
					SET V_TradeStatus=20;  -- 预处理
					IF V_DeliveryTerm=1 THEN
						IF UNIX_TIMESTAMP(V_PayTime)+@cfg_delay_check_sec>V_Timestamp THEN
							SET V_TradeStatus=16;  -- 延时审核
							SET V_DelayToTime=UNIX_TIMESTAMP(V_PayTime)+@cfg_delay_check_sec;
						END IF;
					ELSEIF V_DeliveryTerm=2 THEN
						IF UNIX_TIMESTAMP(V_TradeTime)+@cfg_delay_check_sec>V_Timestamp THEN
							SET V_TradeStatus=16;  -- 延时审核
							SET V_DelayToTime=UNIX_TIMESTAMP(V_TradeTime)+@cfg_delay_check_sec;
						END IF;
					END IF;
				END IF;
				*/
				-- 标记未付款的
				SET V_TradeStatus=20;  -- 前处理(赠品、合并、拆分)
				SET V_UnmergeMask=0;
				UPDATE sales_trade SET unmerge_mask=unmerge_mask|IF(V_TradeStatus>=15 AND V_TradeStatus<25,2,0),modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW()) 
				WHERE customer_id=V_CustomerID AND trade_status>=10 AND trade_status<=95 AND trade_id<>V_DeliverTradeID;
				IF ROW_COUNT()>0 THEN
					-- 计算本订单的状态
					-- 有未付款的
					IF EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status=10) THEN
						SET V_UnmergeMask=1;
					END IF;
					
					-- 有已付款的
					IF EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status>10 AND trade_status<=95 AND trade_id<>V_DeliverTradeID) THEN
						SET V_UnmergeMask=V_UnmergeMask|2;
					END IF;
				END IF;
				/*
				-- 标记等未付
				IF V_TradeStatus>=15 AND V_TradeStatus<25 AND @cfg_wait_unpay_sec > 0 AND (V_UnmergeMask&1) THEN
					SET V_NOT_FOUND=0;
					SELECT MAX(trade_time) INTO V_OldTradeTime 
					FROM sales_trade 
					WHERE customer_id=V_CustomerID AND trade_status=10 AND trade_id<>V_DeliverTradeID;
					
					IF V_NOT_FOUND=0 AND UNIX_TIMESTAMP(V_OldTradeTime)+@cfg_wait_unpay_sec>V_Timestamp THEN
						SET V_TradeStatus=15;  -- 等未付
						SET V_DelayToTime=UNIX_TIMESTAMP(V_OldTradeTime)+@cfg_wait_unpay_sec;
						SET V_ExtMsg = ' 等未付';
					END IF;
				END IF;
				
				-- 解除之前的等未付订单
				IF V_TradeStatus IN(16,19,20,27) AND V_UnmergeMask AND (V_UnmergeMask&1)=0 THEN
					UPDATE sales_trade SET trade_status=16,
						delay_to_time=UNIX_TIMESTAMP(IF(delivery_term=1,pay_time,trade_time))+@cfg_delay_check_sec
					WHERE customer_id=V_CustomerID AND trade_status=15;
				END IF;
				*/
				-- 所有订单都已付款
				IF V_UnmergeMask AND (V_UnmergeMask&1)=0 THEN
					-- 取消等同名未付款标记
					UPDATE sales_trade SET unmerge_mask=(unmerge_mask&~1)
					WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status<95;
				END IF;
				
				-- 获取仓库-新
				/*IF V_NewWarehouseID = 0 THEN
					SELECT warehouse_id INTO V_NewWarehouseID FROM cfg_warehouse where is_disabled = 0 limit 1;
				END IF;*/
				-- 获取物流
				/*IF V_LogisticsID = 0 THEN
						IF V_DeliveryTerm=2 THEN
							SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						ELSE 
							SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						END IF;
				END IF;*/
				-- 估算货品成本
				SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_NewWarehouseID AND ss.spec_id=tsto.spec_id
				WHERE tsto.actual_num>0;
				SET V_AreaAlias = '';
				-- 更新订单(设置订单如果有自定义标记就不变，没有自定义标记就改flag_id)
				UPDATE sales_trade
				SET trade_status=V_TradeStatus,refund_status=V_NewRefundStatus,delivery_term=V_DeliveryTerm,pay_time=V_PayTime,pay_account = V_PayAccount,
					receiver_name=V_ReceiverName,receiver_province=V_ReceiverProvince,receiver_city=V_ReceiverCity,
					receiver_district=V_ReceiverDistrict,receiver_area=V_ReceiverArea,receiver_ring=V_ReceiverRing,
					receiver_address=V_ReceiverAddress,receiver_zip=V_ReceiverZip,receiver_telno=V_ReceiverTelno,receiver_mobile=V_ReceiverMobile,
					paid=V_Paid,goods_amount=V_GoodsAmount,post_amount=V_PostAmount,
					other_amount=V_OtherAmount,discount=V_Discount,receivable=V_Receivable,
					dap_amount=V_DapAmount,cod_amount=V_CodAmount,pi_amount=V_PiAmount,invoice_type=V_InvoiceType,
					ext_cod_fee=V_ExtCodFee,invoice_title=V_InvoiceTitle,invoice_content=V_InvoiceContent,
					is_sealed=V_IsSealed,goods_count=V_SalesGoodsCount,goods_type_count=V_SalesOrderCount,
					weight=V_TotalWeight,volume=V_TotalVolume,warehouse_type=V_NewWmsType,warehouse_id=V_NewWarehouseID,package_id=V_PackageID,
					logistics_id=V_LogisticsID,receiver_dtb=V_AreaAlias,flag_id=IF(flag_id>1000,flag_id,V_FlagID),salesman_id=V_SalesmanID,
					goods_cost=V_GoodsCost,profit=V_Receivable-V_GoodsCost-V_PostCost,freeze_reason=V_IsFreezed,
					remark_flag=V_RemarkFlag,cs_remark=V_Remark,unmerge_mask=V_UnmergeMask,delay_to_time=V_DelayToTime,
					cs_remark_change_count=NOT FN_EMPTY(V_Remark),buyer_message_count=NOT FN_EMPTY(V_BuyerMessage),
					cs_remark_count=NOT FN_EMPTY(V_Remark),gift_mask=V_GiftMask,commission=V_Commission,
					raw_goods_type_count=V_ApiOrderCount,raw_goods_count=V_ApiGoodsCount,single_spec_no=V_SingleSpecNO,
					buyer_message=V_BuyerMessage,version_id=version_id+1
				WHERE trade_id=V_DeliverTradeID;
				-- 重新占用库存
				IF V_NewWarehouseID > 0 THEN
					IF V_TradeStatus=15 OR V_TradeStatus=16 OR V_TradeStatus=20 OR V_TradeStatus=27 THEN	-- 已付款
						CALL I_RESERVE_STOCK(V_DeliverTradeID, 3, V_NewWarehouseID, 0);
					ELSEIF V_TradeStatus=19 THEN -- 预订单
						CALL I_RESERVE_STOCK(V_DeliverTradeID, 5, V_NewWarehouseID, 0);
					ELSEIF V_TradeStatus=50 THEN -- 已转到平台仓库(如物流宝)
						CALL I_SALES_TRADE_GENERATE_STOCKOUT(V_StockoutNO2,V_DeliverTradeID,V_NewWarehouseID,55,V_StockoutNO);
						UPDATE sales_trade SET stockout_no=V_StockoutNO2 WHERE trade_id=V_DeliverTradeID;
					END IF;
				END IF;
				
				-- 创建退款单
				IF @tmp_refund_occur THEN
					CALL I_DL_PUSH_REFUND(P_OperatorID, V_ShopID, V_Tid);
				END IF;
				/*
				-- 保存抢单仓库
				IF V_ShopHoldEnabled AND LENGTH(@tmp_warehouse_enough)>0 THEN
					CALL SP_EXEC(CONCAT('INSERT IGNORE INTO sales_trade_warehouse(trade_id,warehouse_id,warehouse_no) SELECT ',
						V_DeliverTradeID,
						',warehouse_id,warehouse_no FROM cfg_warehouse WHERE warehouse_id IN(',
						@tmp_warehouse_enough, ')'));
				END IF;
				*/
				-- 清除子订单状态变化
				UPDATE api_trade_order SET modify_flag=0,process_status=20 WHERE platform_id=V_PlatformID and tid=V_Tid;
				
				-- 更新原始单
				UPDATE api_trade SET
					x_salesman_id=V_SalesmanID,
					x_trade_flag=V_FlagID,
					x_is_freezed=V_IsFreezed,
					x_warehouse_id=IF(V_Locked,V_NewWarehouseID,0)
				WHERE rec_id=P_ApiTradeID;
				
				-- 冻结日志
				IF V_IsFreezed AND NOT V_OldFreeze THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,data,message)
					SELECT V_DeliverTradeID,P_OperatorID,28,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
					FROM cfg_oper_reason 
					WHERE reason_id = V_IsFreezed;
				END IF;

				-- 标记同名未合并 进入审核时
				/*IF @cfg_order_check_warn_has_unmerge AND V_TradeStatus=30 THEN
					UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2),modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW())
					WHERE trade_status>=15 AND trade_status<=95 AND 
						customer_id=V_CustomerID AND 
						is_sealed=0 AND
						delivery_term=1 AND
						split_from_trade_id<=0 AND
						trade_id <> V_DeliverTradeID;
					
					IF ROW_COUNT() > 0 THEN
						UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2) WHERE trade_id=V_DeliverTradeID;
					ELSE
						UPDATE sales_trade SET unmerge_mask=(unmerge_mask & ~2) WHERE trade_id=V_DeliverTradeID;
					END IF;
				END IF;*/

				-- 订单全链路
				IF V_TradeStatus<55 THEN
					CALL I_SALES_TRADE_TRACE(V_DeliverTradeID, 1, '');
				END IF;
				
				-- 日志
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message,created) VALUES(V_DeliverTradeID,P_OperatorID,2,0,CONCAT('付款:',V_Tid,V_ExtMsg),V_PayTime);
			ELSE
				-- 日志
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,-1,CONCAT('待发货异常状态：',V_TradeStatus));
			END IF;
			
			UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
			
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus = 20 THEN -- 20待尾款
			IF V_TradeStatus = 10 THEN
				-- 更新支付
				UPDATE sales_trade
				SET trade_status=12,pay_time=V_PayTime,goods_amount=V_GoodsAmount,post_amount=V_PostAmount,other_amount=V_OtherAmount,
					discount=V_Discount,receivable=V_Receivable,
					dap_amount=V_DapAmount,cod_amount=V_CodAmount,pi_amount=V_PiAmount,invoice_type=V_InvoiceType,
					invoice_title=V_InvoiceTitle,invoice_content=V_InvoiceContent,stockout_no=V_StockoutNO,
					is_sealed=V_IsSealed
				WHERE trade_id=V_DeliverTradeID;
				/*
				IF NOT EXISTS(SELECT 1 FROM sales_trade WHERE trade_status=10) THEN
					-- 取消等同名未付款标记
					UPDATE sales_trade SET unmerge_mask=(unmerge_mask&~1)
					WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status<95;
				END IF;
				*/
				-- 重新分配paid
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,2,CONCAT('首付款:',V_Tid));
			ELSE
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,-1,CONCAT('待尾款异常状态：',V_TradeStatus));
			END IF;
		ELSEIF V_ApiTradeStatus = 80 OR V_ApiTradeStatus = 90 THEN	-- 整单退款或关闭
			-- 创建退款单
			IF V_ApiTradeStatus=80 THEN
				CALL I_DL_PUSH_REFUND(P_OperatorID, V_ShopID, V_Tid);
			END IF;
			-- 要轮循处理单,可能已经发货，需要拦截
			OPEN trade_by_api_cursor;
			TRADE_BY_API_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE TRADE_BY_API_LABEL;
				END IF;
				
				IF V_DeliverTradeID IS NULL THEN -- 历史订单
					ITERATE TRADE_BY_API_LABEL;
				END IF;
				
				IF V_TradeStatus=10 OR V_TradeStatus=12 THEN -- 未付款订单
					SELECT customer_id INTO V_CustomerID
					FROM sales_trade WHERE trade_id=V_DeliverTradeID;
					
					SELECT MAX(trade_time) INTO V_OldTradeTime FROM sales_trade 
					WHERE customer_id=V_CustomerID AND trade_status=10 AND trade_id<>V_DeliverTradeID;
					/*
					-- 没有未付款订单,或者未付款订单时间都很久了
					IF V_OldTradeTime IS NULL OR UNIX_TIMESTAMP(V_OldTradeTime)+@cfg_wait_unpay_sec<V_Timestamp THEN
						-- 解除等未付
						UPDATE sales_trade SET trade_status=16,delay_to_time=UNIX_TIMESTAMP(IF(delivery_term=1,pay_time,trade_time))+@cfg_delay_check_sec
						WHERE customer_id=V_CustomerID AND trade_status=15;
						
						-- 解除待审核订单的待付款标记
						UPDATE sales_trade SET unmerge_mask=(unmerge_mask & ~1)
						WHERE customer_id=V_CustomerID AND trade_status>15 AND trade_status<=95;
					END IF;
					*/
				ELSEIF V_TradeStatus>=15 AND V_TradeStatus<=95 THEN
					-- 只有一个已付款的
					IF 1=(SELECT COUNT(1) FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status<=95 AND trade_id<>V_DeliverTradeID) THEN
						-- 取消同名未合并
						UPDATE sales_trade SET unmerge_mask=(unmerge_mask & ~2)
						WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status<=95;
					END IF;
				END IF;
				
				IF V_TradeStatus>=40 AND V_TradeStatus<95 THEN -- 已审核，拦截出库单
					-- 如果用户已经处理退款,就不需要拦截了
					IF EXISTS(SELECT 1 FROM sales_trade_order WHERE shop_id=V_ShopID AND src_tid=V_Tid AND trade_id=V_DeliverTradeID AND actual_num>0) THEN
						SET V_NOT_FOUND=0;
						SELECT stockout_id INTO V_StockoutID FROM stockout_order 
						WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
						
						IF V_NOT_FOUND=0 THEN
							UPDATE stockout_order SET block_reason=(block_reason|2) WHERE stockout_id=V_StockoutID;
							UPDATE stalls_less_goods_detail SET block_reason=(block_reason|2) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(IF(V_ApiTradeStatus=80,'订单退款','订单关闭'),',拦截出库单'));
						
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
							-- 标记退款(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
							UPDATE sales_trade SET bad_reason=(bad_reason|64),flag_id=IF(flag_id>1000,flag_id,30)  WHERE trade_id=V_DeliverTradeID;
						END IF;
					ELSE
						ITERATE TRADE_BY_API_LABEL;
					END IF;
				ELSEIF V_TradeStatus>=95 THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('关闭:',V_Tid,',订单已发货'));
					ITERATE TRADE_BY_API_LABEL;
				END IF;
				
				-- 更新平台货品库存变化
				-- 单品
				INSERT INTO sys_process_background(`type`,object_id)
				SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND actual_num>0;
				
				-- 组合装
				INSERT INTO sys_process_background(`type`,object_id)
				SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
					WHERE sto.trade_id=V_DeliverTradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
				
				-- 回收库存
				INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
				(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'退款，释放库存' FROM sales_trade_order sto 
				LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE sto.stock_reserved>=2 AND sto.trade_id=V_DeliverTradeID AND sto.shop_id=V_ShopID AND sto.src_tid=V_Tid AND sto.actual_num>0);

				INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
				(SELECT V_WarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
					IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
				FROM sales_trade_order 
				WHERE shop_id=V_ShopID AND src_tid=V_Tid
					AND trade_id=V_DeliverTradeID AND actual_num>0 ORDER BY spec_id)
				ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
					sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
				
				UPDATE sales_trade_order
				SET actual_num=0,stock_reserved=0,refund_status=IF(V_ApiTradeStatus=80,5,0),remark=IF(V_ApiTradeStatus=80, '退款','关闭')
				WHERE shop_id=V_ShopID AND src_tid=V_Tid
					AND trade_id=V_DeliverTradeID AND actual_num>0;
				
				-- 看当前订单还有没非赠品货品
				SET V_HasSendGoods=0,V_HasGift=0;
				SELECT MAX(IF(gift_type=0,1,0)),MAX(IF(gift_type,1,0)) INTO V_HasSendGoods,V_HasGift FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND actual_num>0 AND gift_type=0;
				IF V_HasSendGoods THEN
					CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, 0);
					
					-- 日志
					IF V_ApiTradeStatus=80 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,8,CONCAT('部分退款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('部分关闭:',V_Tid));
					END IF;
				ELSE -- 除赠品之前没其它货品
					-- 取消赠品
					INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
					(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'退赠品，释放库存' FROM sales_trade_order sto 
					LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE sto.trade_id=V_DeliverTradeID  AND sto.stock_reserved>=2 AND sto.gift_type>0);

					INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
					(SELECT V_WarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
						IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
					FROM sales_trade_order 
					WHERE trade_id=V_DeliverTradeID AND stock_reserved>=2 AND gift_type>0 ORDER BY spec_id)
					ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
						sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
					
					UPDATE sales_trade_order
					SET actual_num=0,stock_reserved=0,refund_status=5, remark=IF(V_ApiTradeStatus=80, '退款','关闭')
					WHERE trade_id=V_DeliverTradeID AND stock_reserved>=2 AND gift_type>0;
					
					CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, 5);
					IF V_ApiTradeStatus=80 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('退款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('关闭:',V_Tid));
					END IF;
				END IF;
			END LOOP;
			CLOSE trade_by_api_cursor;			
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
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(V_Tid,ato.invoice_content)>0) THEN

					UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato
					SET st.bad_reason = (st.bad_reason | 512)
					WHERE st.trade_status >= 25 AND st.trade_status < 55 
					AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
					AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(V_Tid,ato.invoice_content)>0;
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto,api_trade_order ato
							WHERE st.trade_status >= 25 AND st.trade_status < 55 AND st.customer_id = V_CustomerID 
							AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
							AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(V_Tid,ato.invoice_content)>0;
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
					ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND FIND_IN_SET(V_Tid,ato.invoice_content)>0) THEN

					UPDATE sales_trade st,sales_trade_order sto,api_trade_order ato,stockout_order so
					SET st.bad_reason = (st.bad_reason | 512),so.block_reason = (so.block_reason | 2048)
					WHERE so.src_order_type = 1 AND so.src_order_id = st.trade_id AND st.trade_status >= 55 AND st.trade_status < 95 
					AND st.customer_id = V_CustomerID AND (st.bad_reason&512)=0 AND sto.trade_id = st.trade_id AND sto.actual_num>0 
					AND sto.`gift_type`>0 AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid 
					AND FIND_IN_SET(V_Tid,ato.invoice_content)>0;
					IF ROW_COUNT()>0 THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message)
							SELECT  DISTINCT st.trade_id,P_OperatorID,172,'拦截赠品'
							FROM sales_trade st,sales_trade_order sto,api_trade_order ato
							WHERE st.trade_status >= 55 AND st.trade_status < 95 AND st.customer_id = V_CustomerID 
							AND sto.trade_id = st.trade_id AND sto.actual_num>0 AND sto.`gift_type`>0 
							AND ato.platform_id = sto.platform_id AND ato.oid = sto.src_oid AND FIND_IN_SET(V_Tid,ato.invoice_content)>0;
					END IF;

				END IF;
				
			END IF;

			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid;
			UPDATE api_trade SET modify_flag=0,process_status=70 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=V_ShopID AND src_tid=V_Tid;
			IF (@cfg_order_deliver_block_consign) THEN
				OPEN trade_by_api_cursor;
				TRADE_BY_API_LABEL: LOOP
					SET V_NOT_FOUND=0;
					FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND=0;
						LEAVE TRADE_BY_API_LABEL;
					END IF;
				
					IF V_DeliverTradeID IS NULL THEN -- 历史订单
						ITERATE TRADE_BY_API_LABEL;
					END IF;
					SELECT consign_status INTO V_ConsignStatus FROM stockout_order WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID LIMIT 1;
					IF V_TradeStatus<95 && !(V_ConsignStatus & 128) THEN
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
				END LOOP;
				CLOSE trade_by_api_cursor;
			END IF;
			/*
			UPDATE sales_trade_order sto,sales_trade st
			SET st.trade_status=
				IF(EXISTS (SELECT 1 FROM sales_trade_order x WHERE x.trade_id=st.trade_id AND x.actual_num>0 AND x.is_consigned=0),90,95)
			WHERE sto.platform_id=V_PlatformID AND sto.src_tid=V_Tid AND st.trade_id=sto.trade_id AND (st.trade_status=85 OR st.trade_status=90);
			*/
			UPDATE api_trade SET process_status=40 WHERE rec_id=P_ApiTradeID;
		ELSEIF V_ApiTradeStatus = 70 THEN	-- 订单已完成,确认打款
			-- 要轮循处理单
			OPEN trade_by_api_cursor;
			TRADE_BY_API_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE TRADE_BY_API_LABEL;
				END IF;
				
				IF V_DeliverTradeID IS NULL THEN -- 历史订单
					ITERATE TRADE_BY_API_LABEL;
				END IF;
				
				-- 处理打款,财务相关暂不考虑
				-- CALL I_VR_SALES_TRADE_CONFIRM(V_DeliverTradeID, V_PlatformId, V_Tid, '');
				
				-- 日志
				/*
				SET V_Exists=0;
				SELECT 1 INTO V_Exists FROM sales_trade_order WHERE trade_id=V_DeliverTradeID AND is_received=0 AND share_amount>0 LIMIT 1;
				IF V_Exists=0 THEN
				*/
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=110,unmerge_mask=0 WHERE trade_id=V_DeliverTradeID;
						-- 1073741824原始单已全部完成,指示这个单子不用物流同步了
						UPDATE stockout_order SET status=110,consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,80,'客户打款,交易完成');
					ELSE
						UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						UPDATE stalls_less_goods_detail SET trade_status=1 WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,80,'客户打款,订单未发货');
					END IF;
				/*
				ELSE
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=105 WHERE trade_id=V_DeliverTradeID;
						UPDATE stockout_order SET status=105 WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款,订单未发货:',V_Tid));
					END IF;
				END IF;
				*/
			END LOOP;
			CLOSE trade_by_api_cursor;
			
			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid AND `status`=70;
			UPDATE api_trade SET modify_flag=0,process_status=60 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		END IF;
		
		SET V_ModifyFlag=V_ModifyFlag & ~3; -- trade_status | pay_status
	END IF;
	
	SET V_ModifyFlag=V_ModifyFlag & ~4;
	-- 部分退款效给子订单处理
	
	-- 备注变化
	IF (V_ModifyFlag & 8) THEN
		SET V_Remark=TRIM(V_Remark);
		-- 记录备注
		INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);		
		-- 判断是客服备注变化还是标旗变化
	  SELECT IF(V_Remark<>cs_remark,'客服备注变化',''),IF(V_RemarkFlag<>remark_flag,'标旗变化','') INTO V_PromptRemark,V_PromptRemarkFlag FROM sales_trade WHERE trade_id=V_DeliverTradeID;
	  -- 提取业务员
		CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed,V_RemarkLog2);
		IF V_SalesmanID THEN
			IF V_RemarkLog2<>'' AND V_OriginSalesmanId=0 THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('客服备注提取。',V_RemarkLog2));
			END IF;
			UPDATE api_trade SET
				x_salesman_id=IF(x_salesman_id=0,V_SalesmanID,x_salesman_id),
				x_logistics_id=IF(x_logistics_id=0,V_LogisticsID,x_logistics_id),
				x_is_freezed=IF(x_is_freezed=0,V_IsFreezed,x_is_freezed),
				x_trade_flag=IF(x_trade_flag=0,V_FlagID,x_trade_flag)
			WHERE platform_id=V_PlatformID and tid=V_Tid;
		END IF;
		
		OPEN trade_by_api_cursor;
		TRADE_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_BY_API_LABEL;
			END IF;
			
			IF V_DeliverTradeID IS NULL THEN -- 历史订单
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			SET @old_sql_mode=@@SESSION.sql_mode;
			SET SESSION sql_mode='';
			-- 计算备注,可能有订单合并
			SELECT IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.remark)='',NULL,TRIM(ax.remark))),1024),''),
				SUM(IF(TRIM(ax.remark)='',0,1)),MAX(ax.remark_flag)
			INTO V_Remark,V_RemarkCount,V_RemarkFlag
			FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=V_DeliverTradeID) sto
				LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
			
			SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
			
			-- 更新备注
			UPDATE sales_trade 
			SET salesman_id=IF(V_TradeStatus<55 AND salesman_id<=0,V_SalesmanID,salesman_id),
				cs_remark=V_Remark,
				remark_flag=V_RemarkFlag,
				cs_remark_count=V_RemarkCount,
				cs_remark_change_count=cs_remark_change_count|1,
				version_id=version_id+1
			WHERE trade_id=V_DeliverTradeID;
			
			-- 未审核
			IF V_TradeStatus < 25 OR (V_TradeStatus<35 AND @cfg_order_deliver_enable_cs_remark_track) THEN 
				    IF (V_PromptRemark<>'') THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemark,':',V_Tid));
						END IF;
						IF (V_PromptRemarkFlag<>'') THEN
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemarkFlag,':',V_Tid));
						END IF;
				
				-- 判断物流更新,暂时不处理
				/*
				IF V_LogisticsID THEN
					SELECT logistics_id INTO V_OldLogisticsID FROM sales_trade WHERE trade_id=V_DeliverTradeID;
					IF V_OldLogisticsID<>V_LogisticsID THEN
						UPDATE sales_trade SET logistics_id=V_LogisticsID WHERE trade_id=V_DeliverTradeID;
						CALL I_DL_REFRESH_TRADE(V_DeliverTradeID, P_OperatorID, IF(@cfg_open_package_strategy,4,0), 0);
						
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
						SELECT V_DeliverTradeID,P_OperatorID,20,CONCAT('从备注提取物流:',logistics_name)
						FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
					END IF;
				END IF;
				*/
			ELSEIF V_TradeStatus<40 THEN
				-- 加异常标记(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
				UPDATE sales_trade SET bad_reason=(bad_reason|16),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
				IF (V_PromptRemark<>'') THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemark,':',V_Tid));
				END IF;
				IF (V_PromptRemarkFlag<>'') THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemarkFlag,':',V_Tid));
				END IF;					
			ELSEIF V_TradeStatus >= 40 AND V_TradeStatus < 95 AND @cfg_remark_change_block_stockout THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|64) WHERE stockout_id=V_StockoutID;
					UPDATE stalls_less_goods_detail SET block_reason=(block_reason|64) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
						-- 出库单日志
					IF (V_PromptRemark<>'') THEN
						INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(V_PromptRemark,',拦截出库单'));
					END IF;
					IF (V_PromptRemarkFlag<>'') THEN
						INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(V_PromptRemarkFlag,',拦截出库单'));
					END IF;					
				END IF;
					IF (V_PromptRemark<>'') THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT(V_PromptRemark,',拦截出库:',V_Tid));
					END IF;
					IF (V_PromptRemarkFlag<>'') THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT(V_PromptRemarkFlag,',拦截出库:',V_Tid));
					END IF;				
			ELSEIF V_TradeStatus >= 95 AND @cfg_remark_change_block_stockout THEN
					IF (V_PromptRemark<>'') THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemark,',订单已发货:',V_Tid));
					END IF;
					IF (V_PromptRemarkFlag<>'') THEN
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,43,CONCAT(V_PromptRemarkFlag,',订单已发货:',V_Tid));
					END IF;					
			END IF;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~8;
	END IF;
	
	-- 客户留言变化
	IF (V_ModifyFlag & 128) THEN
		OPEN trade_by_api_cursor;
		TRADE_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_BY_API_LABEL;
			END IF;
			
			IF V_DeliverTradeID IS NULL THEN -- 历史订单
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			SET @old_sql_mode=@@SESSION.sql_mode;
			SET SESSION sql_mode='';
			-- 计算备注
			SELECT IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.buyer_message)='',NULL,TRIM(ax.buyer_message))),1024),''),
				SUM(IF(TRIM(ax.buyer_message)='',0,1))
			INTO V_BuyerMessage,V_RemarkCount
			FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=V_DeliverTradeID) sto
				LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
			
			SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
			
			-- 更新备注
			UPDATE sales_trade 
			SET buyer_message=V_BuyerMessage,buyer_message_count=V_RemarkCount,version_id=version_id+1
			WHERE trade_id=V_DeliverTradeID;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~128;
	END IF;
	
	IF V_ModifyFlag & 16 THEN -- 地址
		SELECT x_customer_id,receiver_name,receiver_address,receiver_zip,receiver_telno,receiver_mobile,buyer_email
		INTO V_CustomerID,V_ReceiverName,V_ReceiverAddress,V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_BuyerEmail
		FROM api_trade WHERE rec_id=P_ApiTradeID;
		
		-- 更新地址库
		IF V_CustomerID THEN
			INSERT IGNORE INTO crm_customer_address(customer_id,`name`,addr_hash,province,city,district,address,zip,telno,mobile,email,created)
			VALUES(V_CustomerID,V_ReceiverName,MD5(CONCAT(V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverAddress)),
				V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverAddress,V_ReceiverZip,V_ReceiverTelno,
				V_ReceiverMobile,V_BuyerEmail, NOW());
			
			IF V_ReceiverMobile<> '' THEN
				INSERT IGNORE INTO crm_customer_telno(customer_id,`type`,telno,created) VALUES(V_CustomerID, 1, V_ReceiverMobile,NOW());
				-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 1, V_ReceiverMobile);
			END IF;
			
			IF V_ReceiverTelno<> '' THEN
				INSERT IGNORE INTO crm_customer_telno(customer_id,`type`,telno,created) VALUES(V_CustomerID, 2, V_ReceiverTelno,NOW());
				-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 2, V_ReceiverTelno);
			END IF;
		END IF;
		
		OPEN trade_by_api_cursor;
		TRADE_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_BY_API_LABEL;
			END IF;
			
			IF V_DeliverTradeID IS NULL THEN -- 历史订单
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 刷新同名未合并 IGNORE!!!
			
			-- 判断原始单的状态（针对拼多多对已发货的电话加密的情况处理）
			IF V_ApiTradeStatus>=50 THEN
        		ITERATE TRADE_BY_API_LABEL;
			END IF;
      		-- 看地址是否有变化
			IF EXISTS(SELECT 1 FROM sales_trade st,api_trade ax
				WHERE st.trade_id=V_DeliverTradeID AND ax.platform_id=V_PlatformID AND ax.tid=V_Tid
					AND st.receiver_name=ax.receiver_name
					AND st.receiver_province=ax.receiver_province
					AND st.receiver_city=ax.receiver_city
					AND st.receiver_district=ax.receiver_district
					AND REPLACE(st.receiver_address,' ','')=REPLACE(ax.receiver_address,' ','')
					AND st.receiver_mobile=ax.receiver_mobile
					AND st.receiver_telno=ax.receiver_telno
					AND st.receiver_zip=ax.receiver_zip
					AND st.receiver_area=ax.receiver_area
					AND st.receiver_ring=ax.receiver_ring
					AND st.to_deliver_time=ax.to_deliver_time
					AND st.dist_center=ax.dist_center
					AND st.dist_site=ax.dist_site) THEN
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,32,CONCAT('平台收件地址变更,系统已处理(客服已将地址修改正确或者地址变更情况可忽略不计比如多余空格):',V_Tid));
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单，如果系统已发货不拦截
			IF V_TradeStatus >= 40 AND V_TradeStatus<95 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|4) WHERE stockout_id=V_StockoutID;
					UPDATE stalls_less_goods_detail SET block_reason=(block_reason|4) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'收件地址变更,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,32,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
				
			ELSEIF V_TradeStatus < 35 THEN
				-- 还没到审核，直接修改地址
				-- 多个订单合并的情况，是否不应该修改地址?
				UPDATE sales_trade st,api_trade ax 
				SET st.receiver_name=ax.receiver_name,
					st.receiver_province=ax.receiver_province,
					st.receiver_city=ax.receiver_city,
					st.receiver_district=ax.receiver_district,
					st.receiver_address=ax.receiver_address,
					st.receiver_mobile=ax.receiver_mobile,
					st.receiver_telno=ax.receiver_telno,
					st.receiver_zip=ax.receiver_zip,
					st.receiver_area=ax.receiver_area,
					st.receiver_ring=ax.receiver_ring,
					st.to_deliver_time=ax.to_deliver_time,
					st.dist_center=ax.dist_center,
					st.dist_site=ax.dist_site,
					st.version_id=st.version_id+1 
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
				-- 是否重新计算仓库??
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,32,CONCAT('收件地址变更:',V_Tid));
				
				-- 刷新物流,大头笔,包装(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
				CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|3, 0);
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|2),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,32,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
			END IF;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~16;
	END IF;
	
	IF V_ModifyFlag & 32 THEN -- 发票
		OPEN trade_by_api_cursor;
		TRADE_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_BY_API_LABEL;
			END IF;
			
			IF V_DeliverTradeID IS NULL THEN -- 历史订单
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			IF V_TradeStatus<95 THEN
			  UPDATE sales_trade st,api_trade ax
				SET st.invoice_type=ax.invoice_type,
					st.invoice_title=ax.invoice_title,
					st.invoice_content=ax.invoice_content,
					st.version_id=st.version_id+1
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
			END IF;
			-- 拦截出库单(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
			IF V_TradeStatus>=40 THEN
				UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
				SET so.block_reason=(so.block_reason|8)
				WHERE sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id
					AND so.stockout_id=sod.stockout_id
					AND sto.trade_id=V_DeliverTradeID
					AND so.status<>5;
				UPDATE stalls_less_goods_detail SET block_reason=(block_reason|8) WHERE trade_id=V_DeliverTradeID AND trade_status<>1;	
				UPDATE sales_trade SET bad_reason=(bad_reason|4),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID;
				-- 出库单日志??(订单如果有自定义标记就不变，没有自定义标记就改flag_id)
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			ELSEIF V_TradeStatus<30 THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化:',V_Tid));
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|4),flag_id=IF(flag_id>1000,flag_id,30) WHERE trade_id=V_DeliverTradeID; 
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			END IF;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~32;
	END IF;
	/* 
	IF V_ModifyFlag & 64 THEN -- 仓库发生变化
		OPEN trade_by_api_cursor;
		TRADE_BY_API_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH trade_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE TRADE_BY_API_LABEL;
			END IF;
			
			IF V_DeliverTradeID IS NULL THEN -- 历史订单
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 AND V_TradeStatus<95 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|8) WHERE stockout_id=V_StockoutID;
					-- 出库单日志
					-- INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					-- VALUES(2,V_StockoutID,P_OperatorID,53,'仓库变化,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,12,CONCAT('仓库变化,请处理:',V_Tid));
				
			ELSEIF V_TradeStatus < 35 THEN
				-- 重新计算仓库，判断仓库是否改变
				CALL I_DL_DECIDE_WAREHOUSE(
					V_NewWarehouseID, 
					V_WmsType, 
					V_Locked, 
					V_WarehouseNO, 
					V_ShopID, 
					0, 
					V_ReceiverProvince, 
					V_ReceiverCity, 
					V_ReceiverDistrict, 
					0);
				
				-- 仓库发生变化, 且必须要修改
				IF V_NewWarehouseID AND V_Locked AND V_NewWarehouseID<>V_WarehouseID THEN
					IF V_TradeStatus=10 THEN	-- 未付款
						CALL I_RESERVE_STOCK(V_DeliverTradeID, 2, V_NewWarehouseID, V_WarehouseID);
					ELSEIF V_TradeStatus=15 OR V_TradeStatus=16 OR V_TradeStatus=20 OR V_TradeStatus=30 OR V_TradeStatus=35 THEN	-- 已付款
						CALL I_RESERVE_STOCK(V_DeliverTradeID, 3, V_NewWarehouseID, V_WarehouseID); 
					ELSEIF V_TradeStatus=19 OR V_TradeStatus=25 THEN -- 预订单
						CALL I_RESERVE_STOCK(V_DeliverTradeID, 5, V_NewWarehouseID, V_WarehouseID);
						-- 转移到外部仓库，并创建出库单???
					END IF;
					
					UPDATE api_trade SET x_warehouse_id=IF(V_Locked,V_NewWarehouseID,0) WHERE rec_id=P_ApiTradeID;
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,12,CONCAT('平台仓库变化:',V_Tid));
				END IF;
			ELSEIF V_TradeStatus<110 THEN
				UPDATE sales_trade SET bad_reason=(bad_reason|8) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,12,CONCAT('仓库变化,请处理:',V_Tid));
			END IF;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~64;
	END IF;
	*/
	-- 执行策略日志
	IF V_DeliverTradeID THEN
		IF V_AreaAliasLog<>'' THEN 
			INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('根据物流公司和收货地址，',V_AreaAliasLog));
		END IF;	
		IF V_LogisticsMatchLog<>'' THEN 
			SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
			INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('按收件地址：',V_ReceiverArea,'，',V_LogisticsMatchLog,'：',V_LogisticsName));
		END IF;
		IF V_LogisticsFeeLog<>'' THEN 
			SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
			INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('按照物流公司：',V_LogisticsName,'，收件地址：',V_ReceiverArea,'，计算邮资：',V_LogisticsFeeLog));
		END IF; 
		IF V_WareHouseSelectLog<>'' THEN
			SELECT shop_name INTO V_ShopName FROM cfg_shop WHERE shop_id=V_ShopID;
			SELECT name INTO V_WarehouseName FROM cfg_warehouse WHERE warehouse_id=V_WarehouseID2;
			INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('店铺：',V_ShopName,'。',V_WarehouseSelectLog,'：',V_WarehouseName));
		END IF;	
		IF V_RemarkLog<>'' THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('客服备注提取。',V_RemarkLog));
		END IF;		
		IF V_ClientRemarkLog<>'' THEN
			INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_DeliverTradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('客户备注提取。',V_ClientRemarkLog));
		END IF;
	END IF;
	
	UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
	COMMIT;
END//
DELIMITER ;