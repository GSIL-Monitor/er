
DROP PROCEDURE IF EXISTS `I_STOCKOUT_SALES_CONSIGN`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_SALES_CONSIGN`(IN `P_StockoutId` INT, IN `P_IsForce` INT, IN `P_ConsignNow` INT)
    SQL SECURITY INVOKER
    COMMENT '出库单出库(或发货)'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_Status,V_ConsignStatus,V_TradeId,V_SrcOrderType,V_LogisticsId,V_LogisticsType,V_ToStatus,V_ShopId,V_WarehouseId,V_WarehouseType,
		V_PackageId,V_CustomerId,V_IsWeighed,V_Num,V_PlatformId,V_TradeType,V_SmsType,V_NewConsignStatus,V_TradeStatus,V_TradeMask,
		V_DeliveryTerm,V_GoodsType,V_IsAllocated,V_PackageDetailId,V_Flag,V_StockSpecId INT DEFAULT(0);
	DECLARE V_LogisticsNo VARCHAR(40);
	DECLARE V_ReceiverMobile,V_ReceiverTelno VARCHAR(40);
	DECLARE V_Receivable,V_TotalCost,V_Profit,V_PostCost,V_Weight,V_UnknownGoodsAmount,V_Commission,V_TotalPickScore,V_TotalPackScore,V_OtherCost,V_COMMISSION_RATIO DECIMAL(19,4);
	DECLARE V_ReceiverArea VARCHAR(64) DEFAULT '';

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	-- P_IsForce
	-- 0不强制 1 强制 2不强制,但允许负库存
	
	SET @sys_code = 0;
	SET @sys_message='ok'; 
	IF @tmp_skip_cfg IS NULL OR @tmp_skip_cfg <> 5 THEN
		CALL SP_UTILS_GET_CFG('stockout_examine_goods', @cfg_stockout_examine_goods, 0);
		CALL SP_UTILS_GET_CFG('stockout_weight_goods', @cfg_stockout_weight_goods, 0);
		CALL SP_UTILS_GET_CFG('open_message_strategy', @cfg_open_message_strategy,0);  -- 短信策略全局配置
		CALL SP_UTILS_GET_CFG('open_return_visit_rule',@cfg_open_return_visit_rule,0); -- 回访策略全局配置
		CALL SP_UTILS_GET_CFG('open_logistics_trace',@cfg_open_logistics_trace,0); -- 物流追踪全局配置
		CALL SP_UTILS_GET_CFG('sales_split_share_post_to_first_consign',@cfg_sales_split_share_post_to_first_consign,0); -- 针对款到发货的拆分订单邮资是否都放在首单发货订单上
		CALL SP_UTILS_GET_CFG('open_abnormal_stockout',@cfg_open_abnormal_stockout,0); -- 外部仓储回传 对平台已取消订单正常发货  标记异常发货
		-- 出库或者发货时 根据建议包装生成包装出库明细
		CALL SP_UTILS_GET_CFG('stockout_add_package_detail',@cfg_stockout_add_package_detail,0);
		CALL SP_UTILS_GET_CFG('accounting_sync',@cfg_accounting_sync,0); -- 支付宝对账
		CALL SP_UTILS_GET_CFG('stock_sales_not_allow_package_neg_stock',@cfg_stock_sales_not_allow_package_neg_stock,0);
		-- 微信全链路
		CALL SP_UTILS_GET_CFG('open_wechat_send_strategy',@cfg_open_wechat_send_strategy,0);
		SET @tmp_skip_cfg =5;
	END IF;
	SELECT src_order_type,src_order_id,`status`,consign_status,logistics_id,logistics_no,customer_id,post_cost,weight,receiver_area,warehouse_id,warehouse_type,package_id,is_allocated
	INTO V_SrcOrderType,V_TradeId,V_Status,V_ConsignStatus,V_LogisticsId,V_LogisticsNo,V_CustomerId,V_PostCost,V_Weight,V_ReceiverArea,V_WarehouseId,V_WarehouseType,V_PackageId,V_IsAllocated
	FROM stockout_order 
	WHERE stockout_id=P_StockoutId FOR UPDATE;
		
	IF V_NOT_FOUND<>0 THEN
		SET @sys_code=1;
		SET @sys_message='出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_SrcOrderType<>1 THEN	
		SET @sys_code=2;
		SET @sys_message='出库单不是销售出库单';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 55已审核 95已发货
	IF V_Status>=95 THEN 
		SET @sys_code=7;
		SET @sys_message='订单已发货';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 已出库
	IF (V_ConsignStatus&4) AND NOT P_ConsignNow  THEN
		SET @sys_code=9;
		SET @sys_message='订单已出库';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_LogisticsId IS NULL OR V_LogisticsId=0 THEN
		SET @sys_code=5;
		SET @sys_message='物流公司未设置';
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT logistics_type,commission_ratio INTO V_LogisticsType,V_COMMISSION_RATIO FROM cfg_logistics WHERE logistics_id = V_LogisticsId;	
	
	IF V_LogisticsType>1 AND FN_EMPTY(V_LogisticsNo) THEN
		SET @sys_code=6;
		SET @sys_message='物流单号不能为空';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_IsForce<>1 AND P_ConsignNow THEN 
		IF NOT (V_ConsignStatus&1) AND @cfg_stockout_examine_goods THEN
			SET @sys_code=3;
			SET @sys_message='发货前必须验货';
			LEAVE MAIN_LABEL;
		END IF;
		IF NOT (V_ConsignStatus&2) AND @cfg_stockout_weight_goods THEN
			SET @sys_code=4;
			SET @sys_message='发货前必须称重';
			LEAVE MAIN_LABEL;
		END IF;	
		END IF;

	
	
	SELECT platform_id,receivable,shop_id,trade_status,trade_mask,delivery_term,commission,other_cost,receiver_mobile,receiver_telno
		INTO V_PlatformId,V_Receivable,V_ShopId,V_TradeStatus,V_TradeMask,V_DeliveryTerm,V_Commission,V_OtherCost,V_ReceiverMobile,V_ReceiverTelno
	FROM sales_trade WHERE trade_id=V_TradeID FOR UPDATE;
	
	SET V_Flag = 0 ;
	
	IF V_ReceiverMobile='' THEN
		SET V_ReceiverMobile=V_ReceiverTelno;
	END IF;
	
	IF V_TradeStatus = 5 AND V_WarehouseType>1 AND V_WarehouseType<127 THEN
		IF @cfg_open_abnormal_stockout=0 THEN
			SET @sys_code=9;
			SET @sys_message='订单状态不正确';
			LEAVE MAIN_LABEL;
		END IF;

		SET V_Flag = 1;

	ELSEIF  V_TradeStatus<>55 AND V_TradeStatus<>53 THEN
		SET @sys_code=7;
		SET @sys_message='订单状态不正确';
		LEAVE MAIN_LABEL;
	END IF;
	
	
	-- 计算出库单出库后状态
	SET V_ToStatus = V_Status;
	IF P_ConsignNow THEN
		-- 线下订单发货后立即完成
		SET V_ToStatus = IF(V_PlatformId=0 OR V_TradeMask&32 ,110, 95);
		SET V_ToStatus = IF(V_Flag=1,113,V_ToStatus);
	END IF;
	
	IF NOT (V_ConsignStatus&4) THEN
		-- 
		IF @cfg_stockout_add_package_detail AND  V_PackageId>0 AND NOT EXISTS(SELECT 1 FROM stockout_order_detail WHERE stockout_id = P_StockoutId AND spec_id=V_PackageId ) THEN
			
			SET V_NOT_FOUND=0;
			SELECT gg.goods_type INTO V_GoodsType FROM goods_spec gs LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id AND gs.deleted = 0 WHERE gs.spec_id=V_PackageId;
			IF V_NOT_FOUND THEN
				SET @sys_code=8;
				SET @sys_message='操作失败:包装不存在或已被删除';
				LEAVE MAIN_LABEL;
			END IF;
			
			IF V_GoodsType<>3 THEN
				SET @sys_code=9;
				SET @sys_message='操作失败:所选包装货品类别不属于包装';
				LEAVE MAIN_LABEL;
			END IF;
			
			IF @cfg_stock_sales_not_allow_package_neg_stock THEN 
				
				SELECT rec_id INTO V_StockSpecId FROM stock_spec WHERE spec_id=V_PackageId AND warehouse_id=V_WarehouseId;
				
				IF NOT EXISTS( SELECT rec_id FROM stock_spec_detail WHERE is_used_up=0 AND stock_spec_id=V_StockSpecId  AND (stock_num - reserve_num)>=1) THEN
					SET @sys_code=10;
					SET @sys_message='操作失败:所选包装库存不足';
					LEAVE MAIN_LABEL;
				END IF;
				
			END IF;
			
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,num,price,cost_price,goods_name,goods_id,goods_no,spec_id,
				spec_name,spec_code,spec_no,is_package)
			(
				SELECT P_StockoutId,1,0,1,0,0,gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_id,gs.spec_name,gs.spec_code,gs.spec_no,1
				FROM goods_spec gs LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
				WHERE gs.spec_id=V_PackageId AND gg.goods_type=3
			);
			
			IF V_IsAllocated THEN
				SET V_PackageDetailId = LAST_INSERT_ID();
				CALL I_DISTRIBUTION_POSITION_ONE(P_StockoutId,V_PackageDetailId,V_WarehouseId);
			END IF;
			
		END IF;

		CALL I_STOCKOUT_OUT(P_StockoutId, P_IsForce, V_ToStatus, 1, 0);
		IF @sys_code<>0 THEN
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 释放订单占用库存标记
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=V_TradeId;
	END IF;
	
	SET V_NewConsignStatus=V_ConsignStatus;
	-- 发货
	IF V_ToStatus>=95 THEN
		-- 增加待物流同步记录
		IF NOT (V_ConsignStatus&8) THEN
			IF  V_ToStatus<>113 AND !(V_TradeMask&32) AND V_LogisticsType<>1 THEN
				CALL I_STOCK_LOGISTICS_SYNC(P_StockoutId);
				IF @sys_code<>0 THEN
					LEAVE MAIN_LABEL;
				END IF;
			END IF;
			
			-- 重新读取consign_status,判断原始单是否已完成
			SELECT consign_status INTO V_NewConsignStatus FROM stockout_order WHERE stockout_id=P_StockoutId;
		END IF;

		-- 智选物流的订单需要推送到淘宝
		IF (V_TradeMask&1) THEN
			INSERT IGNORE INTO sales_smart_logistics_sync(trade_id,`status`,logistics_id,author_shop_id,created) 
				SELECT trade_id,0,logistics_id,csl.author_shop_id,NOW() FROM sales_trade st LEFT JOIN cfg_smart_logistics csl ON st.shop_id=csl.shop_id WHERE st.trade_id=V_TradeId;
		END IF;

		 -- 开启拆分订单邮资全部分摊到首单发货的订单上的配置
		IF @cfg_sales_split_share_post_to_first_consign THEN
			CALL I_SALES_POST_SPLIT_SHARE_TO_FIRST_CONSIGN(V_TradeID);
			IF @sys_code > 0 THEN
				LEAVE MAIN_LABEL;
			ELSEIF @sys_code < 0 THEN 
				SELECT receivable INTO V_Receivable FROM sales_trade WHERE trade_id = V_TradeID;
				SET @sys_code = 0;
			END IF;
		END IF;
		-- 出库记录应收账款
		-- 发货记录预估邮资凭证,以对账
	
		IF @sys_code<>0 THEN
			LEAVE MAIN_LABEL;
		END IF;

		IF V_DeliveryTerm=2 THEN
			-- 计算物流佣金
			UPDATE sales_trade 
			SET other_cost = (receivable*V_COMMISSION_RATIO) 
			WHERE trade_id=V_TradeId;
		END IF;

		-- 利润计算  更新客户信息  crm_customer
		-- 计算不允许0成本的  售价总额 
		-- 计算佣金成本  
		SELECT (goods_total_cost+calc_post_cost+package_cost),unknown_goods_amount,post_cost,weight INTO V_TotalCost,V_UnknownGoodsAmount,V_PostCost,V_Weight FROM stockout_order WHERE stockout_id=P_StockoutId;
		-- SELECT unknown_goods_amount INTO V_UnknownGoodsAmount FROM stockout_order WHERE stockout_id = P_StockoutId;
		-- SELECT so.post_cost,so.weight INTO V_PostCost,V_Weight FROM stockout_order so WHERE so.src_order_type=1 AND so.stockout_id=P_StockoutId;
				
		--  计算打包积分和分拣积分
		SELECT SUM(IFNULL(gs.pick_score,0)*sod.num),SUM(IFNULL(gs.pack_score,0)*num) INTO V_TotalPickScore,V_TotalPackScore
		FROM stockout_order_detail sod LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id
		WHERE sod.stockout_id = P_StockoutId;

		SET V_Profit = V_Receivable-V_TotalCost-V_UnknownGoodsAmount-V_Commission-V_OtherCost;
		
		-- 递交是不要记录客户订单数
		-- UPDATE sales_trade SET profit=V_Profit WHERE trade_id=V_TradeId;
		
		-- 驳回时要扣减!!!!!!!!
		UPDATE crm_customer SET 
			last_trade_time=NOW(),
			trade_count=trade_count+1,
			trade_amount=trade_amount+V_Receivable,
			profit=profit+V_Profit
		WHERE customer_id=V_CustomerId;
		-- 会员累计积分
		
		
		--   插入一条待结算物流单记录 type:1销售出库 2销售退货 3采购入库 4采购退货 5货品调拨 6其它入库 7其它出库
		INSERT INTO fa_logistics_fee(logistics_id,logistics_no,`status`,`type`,shop_id,warehouse_id,postage,weight,`area`,make_oper_id,trade_count,created)
		VALUES(V_LogisticsId,V_LogisticsNo,0,1,V_ShopId,V_WarehouseId,V_PostCost,V_Weight,V_ReceiverArea,@cur_uid,1,NOW())
		ON DUPLICATE KEY UPDATE  trade_count=trade_count+VALUES(trade_count),weight=weight+VALUES(weight),postage=postage+VALUES(postage);

		-- 对存在多物流单号的单据插入 fa_logistics_fee信息
		INSERT INTO fa_logistics_fee(logistics_id,logistics_no,`status`,`type`,shop_id,warehouse_id,postage,weight,`area`,make_oper_id,trade_count,created)
		(
			SELECT logistics_id,logistics_no,0,1,V_ShopId,V_WarehouseId,post_cost,weight,V_ReceiverArea,@cur_uid,1,NOW()
			FROM sales_record_multi_logistics
			WHERE trade_id = V_TradeId
		)
		ON DUPLICATE KEY UPDATE  trade_count=trade_count+VALUES(trade_count),fa_logistics_fee.weight=fa_logistics_fee.weight+VALUES(weight),postage=postage+VALUES(postage);
		-- 微信全链路策略
		IF @cfg_open_wechat_send_strategy THEN
			CALL I_CRM_WECHAT_RECORD_INSERT(1,V_TradeId,@cur_uid); 
		END IF;
		-- 短信发送策略
		IF @cfg_open_message_strategy THEN
			SELECT trade_type INTO V_TradeType FROM sales_trade WHERE trade_id = V_TradeId;
			IF V_TradeType = 3 THEN
				SET V_SmsType = 3;
			ELSEIF V_TradeType=5 OR V_TradeType=6 THEN
				SET V_SmsType=7;
			ELSE
				SET V_SmsType = 1;
			END IF;
			-- 微信测试优先发送
			IF @cfg_open_wechat_send_strategy  THEN
				IF NOT EXISTS(SELECT 1 FROM crm_wechat_record WHERE trade_id=V_TradeId AND event_type=1) THEN
					CALL I_CRM_SMS_RECORD_INSERT_TRIGGER(V_SmsType,V_TradeId,@cur_uid);
				END IF;
			ELSE
				CALL I_CRM_SMS_RECORD_INSERT_TRIGGER(V_SmsType,V_TradeId,@cur_uid);
			END IF;

		END IF;
		
		--  回访策略
		IF @cfg_open_return_visit_rule THEN
			CALL I_CRM_RETURN_VISIT_INSERT_AUTO(V_TradeId);
		END IF;

		-- 物流追踪
		IF @cfg_open_logistics_trace THEN 
			INSERT IGNORE INTO sales_logistics_trace(shop_id,warehouse_id,logistics_no,logistics_type,logistics_id,trade_id,stockout_id,created,delivery_term,receiver_mobile)
			VALUES(V_ShopId,V_WarehouseId,V_LogisticsNo,V_LogisticsType,V_LogisticsId,V_TradeId,P_StockoutId,NOW(),V_DeliveryTerm,V_ReceiverMobile);
			
			INSERT IGNORE INTO sales_logistics_trace(shop_id,warehouse_id,logistics_no,logistics_type,logistics_id,trade_id,stockout_id,created,delivery_term,receiver_mobile)
				SELECT V_ShopId,V_WarehouseId,srml.logistics_no,cl.logistics_type,srml.logistics_id,V_TradeId,P_StockoutId,NOW(),V_DeliveryTerm,V_ReceiverMobile
				FROM sales_record_multi_logistics srml,cfg_logistics cl WHERE srml.logistics_id = cl.logistics_id AND srml.trade_id = V_TradeId;
			UPDATE sales_logistics_trace slt,sales_trade st
			SET slt.trade_no = st.trade_no ,slt.stockout_no = st.stockout_no,slt.src_tids = st.src_tids,slt.buyer_nick = st.buyer_nick,
			slt.receiver_name = st.receiver_name , slt.receiver_addr = st.receiver_address , slt.receiver_area = st.receiver_area,
			slt.pay_time = st.pay_time,slt.created = NOW(),slt.delivery_term=st.delivery_term,slt.receiver_mobile=V_ReceiverMobile,
			slt.receivable = st.receivable 
			WHERE slt.trade_id = st.trade_id AND st.trade_id = V_TradeId;
		END IF;
		IF @cfg_accounting_sync THEN 	
			-- 增加平台对账发货金额
			
			insert into fa_alipay_account_check(account_check_no,tid,send_amount,shop_id,
			platform_id,created,consign_time)
			(	
				SELECT FN_SYS_NO('account_check'),sto.src_tid,SUM(sod.total_amount)+SUM(sto.share_post),st.shop_id,
				sto.platform_id, now(),now()  
				from stockout_order_detail sod, sales_trade_order sto,
				sales_trade st where sod.src_order_detail_id=sto.rec_id and sto.trade_id=st.trade_id
				and sod.stockout_id=P_StockoutId and sto.gift_type=0 and sto.refund_status<>5 
				and (st.trade_type=1 or st.trade_type=2)
				group by sto.src_tid,sto.platform_id
			)
			on duplicate key update send_amount=send_amount+values(send_amount),
			`status`=IF(pay_amount>0,IF(pay_amount=send_amount-refund_amount,3,1),0),check_time=IF(`status`>0,NOW(),'0000-00-00 00:00:00'),
			consign_time=IF(consign_time='0000-00-00 00:00:00',NOW(),consign_time);
			
			
				
			-- 可能多个   
			INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,send_amount,diff_amount,created)	
			(
				select  sto.src_tid,sto.platform_id,st.shop_id,DATE_FORMAT(NOW(),'%Y-%m'),sum(sod.total_amount)+sum(sto.share_post),
				sum(sod.total_amount)+sum(sto.share_post),now()
				  from stockout_order_detail sod,sales_trade_order sto,sales_trade st 
				where sod.src_order_detail_id=sto.rec_id and sto.trade_id=st.trade_id 
				and sod.stockout_id=P_StockoutId and sto.gift_type=0 and sto.refund_status<>5 
				and ( st.trade_type=1 or st.trade_type=2 )
				group  by sto.src_tid,sto.platform_id
			)ON DUPLICATE KEY UPDATE send_amount=send_amount+VALUES(send_amount),
			diff_amount=diff_amount+VALUES(diff_amount),`status`=IF(diff_amount=0,1,0);
		END IF;
		-- 如果是线下订单，发货后修改原始单信息为已发货
		UPDATE sales_trade_order sto LEFT JOIN api_trade `at` ON (sto.platform_id=at.platform_id AND sto.src_tid = at.tid ) SET at.process_status=60,at.trade_status=70 WHERE sto.trade_id= V_TradeId AND sto.platform_id=0;
	END IF;
	
	-- 如果原始单已完成,订单直接进入完成状态
	IF V_ToStatus>=95 AND V_ToStatus<110 AND (V_NewConsignStatus&1073741824) THEN
		SET V_ToStatus = 110;
	END IF;
	
	-- 更新出库单状态
	UPDATE stockout_order SET `status`=V_ToStatus,
		checkouter_id=IF(V_ToStatus>=95,0,checkouter_id),
		consign_status=(consign_status|IF(V_PlatformId AND V_ToStatus>=95 AND V_LogisticsType<>1 AND !(V_TradeMask&32),12,4)),
		consign_time=IF(V_ToStatus>=95,NOW(),consign_time),
		pack_score=IF(V_ToStatus>=95,IFNULL(V_TotalPackScore,0),pack_score),
		pick_score=IF(V_ToStatus>=95,IFNULL(V_TotalPickScore,0),pick_score)
	WHERE stockout_id=P_StockoutId;

	-- 如果物流类型为无单号物流，则更新出库单为无需物流同步
	IF V_LogisticsType=1 THEN
		UPDATE  stockout_order SET `consign_status`=(consign_status|32)
		WHERE stockout_id=P_StockoutId;
	END IF;

	-- 更新订单状态
	UPDATE sales_trade SET `trade_status`=IF(V_ToStatus=113,`trade_status`,V_ToStatus),
		consign_status=(consign_status|IF(V_PlatformId AND V_ToStatus>=95 AND !(V_TradeMask&32),12,4))
	WHERE trade_id=V_TradeId;
	
	-- 订单日志
	IF V_ToStatus>=95 AND NOT (V_ConsignStatus&4)  THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message)
		VALUES(V_TradeId, @cur_uid, 105,0, '订单出库并发货');
		IF V_ToStatus<>113 AND !(V_TradeMask&32) THEN
			-- 订单全链路
			CALL I_SALES_TRADE_TRACE(V_TradeId, 13, '');
		END IF;
	ELSEIF V_ToStatus>=95  THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message)
		VALUES(V_TradeId, @cur_uid, 105,1, '订单发货');
		IF V_ToStatus<>113 AND !(V_TradeMask&32) THEN
			-- 订单全链路
			CALL I_SALES_TRADE_TRACE(V_TradeId, 14, '');
		END IF;
	ELSE
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message)
		VALUES(V_TradeId, @cur_uid, 103, '订单出库');
		IF V_ToStatus<>113 AND !(V_TradeMask&32) THEN
			-- 订单全链路
			CALL I_SALES_TRADE_TRACE(V_TradeId, 13, '');
		END IF;
	END IF;
	
END//
DELIMITER ;
