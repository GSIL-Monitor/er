DROP PROCEDURE IF EXISTS `I_DL_SYNC_ORDER_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_ORDER_GOODS`(
	IN `P_TradeID` INT,
	IN `P_ShopID` INT,
	IN `P_Oid` VARCHAR (256),
	IN `P_Tid` VARCHAR (256),
	OUT `P_Message` VARCHAR(255),
	OUT `P_ChangeStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	
	DECLARE V_MatchTargetID,V_GoodsID,V_SGoodsID,V_SpecID,V_SuiteSpecCount,V_I,V_GiftType,V_MasterID,V_WarehouseID,
		V_Cid,V_IsDeleted,V_NOT_FOUND,V_StockReserved INT DEFAULT(0);
	DECLARE V_RecID BIGINT DEFAULT(0);
	DECLARE V_Discount,V_PlatformCost,V_Num,V_Price,V_AdjustAmount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,
		V_SNum,V_FixedPrice,V_Ratio,V_Weight,V_Tmp,V_OrderPrice,V_TmpShareAmount,V_SDiscount,V_SPrice,V_SShareAmount,
		V_SSharePost,V_TotalDiscount,V_LeftDiscount,V_LeftPost,V_LeftShareAmount,V_LeftPaid,V_TaxRate, V_MasterAmount,
		V_SuitePrice,V_SuiteItemPrice,V_SharePrice,V_CommissionFactor,V_Volume DECIMAL(19,4) DEFAULT(0);
	DECLARE V_PlatformID,V_RefundStatus,V_InvoiceType,V_MergeSplitMask,V_OrderStatus,V_MatchTargetType,V_LargeType,V_IsUnsplit,V_IsPrintSuite,V_OrderDeliveryTerm,
		V_DeliveryTerm,V_GuaranteeMode,V_TradeMask,V_IsFixedPrice,V_IsManualMatch TINYINT DEFAULT(0);
	DECLARE V_Oid,V_BindOid,V_GoodsNO,V_SGoodsNO,V_SpecNO,V_SSpecNO,V_SpecCode,V_SSpecCode,V_SuiteNO, V_ApiSpecNO,
		V_Tid,V_CidNO,V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	DECLARE V_InvoiceContent,V_GoodsName,V_SuiteName,V_SGoodsName,V_ApiGoodsName,V_ApiSpecName,V_SpecName,V_SSpecName,V_Remark VARCHAR(256);
	DECLARE V_Now DATETIME;
	DECLARE V_IsZeroCost TINYINT DEFAULT(0);
	DECLARE goods_suite_cursor CURSOR FOR 
		SELECT gsd.spec_id,gsd.num,gsd.is_fixed_price,gsd.fixed_price,gsd.ratio,gg.goods_name,gs.goods_id,gg.goods_no,
			gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,(gs.length*gs.width*gs.height) as volume ,gs.tax_rate,gs.large_type,(gs.retail_price*gsd.num),gs.is_allow_zero_cost,gs.deleted
		FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gsd.suite_id=V_MatchTargetID AND gsd.num>0
		ORDER BY gsd.is_fixed_price DESC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	DELETE FROM tmp_sales_trade_order;
	
	SET P_ChangeStatus=0;
	-- 获取换货之后平台货品对应的系统货品id和类型
	SELECT match_target_id,match_target_type,ags.is_manual_match INTO V_MatchTargetID,V_MatchTargetType,V_IsManualMatch 
		FROM api_trade_order ato LEFT JOIN api_goods_spec ags 
		ON ags.shop_id=P_ShopID AND ags.goods_id=ato.goods_id AND ags.spec_id=ato.spec_id 
	WHERE ato.shop_id=P_ShopID AND ato.oid=P_Oid;
	-- 判断是否开启动态匹配
	IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
		SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
		SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
	END IF;
	
	-- 新货品为未匹配货品
	IF V_MatchTargetType IS NULL OR V_MatchTargetType=0 THEN 
		SET P_Message="，系统订单更换失败，原因：新货品未匹配";
		SET P_ChangeStatus=1;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 记录原子订单的状态、金额等信息,生成新的货品即子订单需要
	SELECT platform_id,delivery_term,guarantee_mode,trade_mask
		INTO V_PlatformID,V_DeliveryTerm,V_GuaranteeMode,V_TradeMask
		FROM api_trade WHERE tid=P_Tid AND shop_id=P_ShopID;
	
	SELECT ato.rec_id,oid,ato.status,refund_status,bind_oid,invoice_type,invoice_content,num,ato.price,adjust_amount,
			discount,share_discount,share_amount,share_post,paid,match_target_type,match_target_id,spec_no,ato.gift_type,
			ato.goods_name,ato.spec_name,aps.cid,aps.is_manual_match,ato.goods_no,ato.spec_no,ato.remark
		INTO 
			V_RecID,V_Oid,V_OrderStatus,V_RefundStatus,V_BindOid,V_InvoiceType,V_InvoiceContent,V_Num,V_Price,V_AdjustAmount,
			V_Discount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,V_MatchTargetType,V_MatchTargetID,V_ApiSpecNO,V_GiftType,
			V_ApiGoodsName,V_ApiSpecName,V_CidNO,V_IsManualMatch,V_OuterId,V_SpecOuterId,V_Remark 
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON aps.shop_id=P_ShopID AND aps.goods_id=ato.goods_id AND aps.spec_id=ato.spec_id
		WHERE ato.shop_id=P_ShopID AND ato.oid=P_Oid;
		
	-- 记录原订单的仓库id，占用库存需要
	SELECT warehouse_id INTO V_WarehouseID FROM sales_trade WHERE trade_id=P_TradeID;
	-- 记录订单的stock_reserved
	SELECT stock_reserved INTO V_StockReserved FROM sales_trade_order WHERE trade_id=P_TradeID AND shop_id=P_ShopID AND src_oid=P_Oid LIMIT 1;
	-- 删除系统订单中的原货品，并回收待审核量,记录日志
	INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
	(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'平台自动换货，老货品释放库存' FROM sales_trade_order sto 
	LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE sto.stock_reserved>=2 AND sto.trade_id=P_TradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid);

	INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
	(SELECT V_WarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
		IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
	FROM sales_trade_order WHERE trade_id=P_TradeID AND shop_id=P_ShopID AND src_oid=P_Oid ORDER BY spec_id)
	ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
		sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);

	DELETE FROM sales_trade_order WHERE trade_id=P_TradeID AND shop_id=P_ShopID AND src_oid=P_Oid;
	
	-- 添加新货品
	IF V_MatchTargetType = 1 THEN -- 单品
		SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,gs.tax_rate,gs.large_type,gs.is_allow_zero_cost,gs.length*gs.width*gs.height
			INTO V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,V_Weight,V_TaxRate,V_LargeType,V_IsZeroCost,V_Volume
			FROM goods_spec gs LEFT JOIN goods_goods gg USING(goods_id)
			WHERE gs.spec_id=V_MatchTargetID AND gs.deleted=0;
		
		SET V_SharePrice=TRUNCATE(V_ShareAmount/V_Num,4);
		
		-- 将货品信息插入临时表
		INSERT INTO tmp_sales_trade_order(
			spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,num,price,actual_num,paid,
			order_price,share_amount,share_post,share_price,adjust,discount,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
			api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,from_mask,gift_type,
			cid,is_allow_zero_cost,remark)
		VALUES(V_MatchTargetID,P_ShopID,V_PlatformID,P_Oid,P_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_Num,V_Price,IF(V_RefundStatus>2,0,V_Num),V_Paid,
			V_SharePrice,V_ShareAmount,V_SharePost,V_SharePrice,V_AdjustAmount,
			(V_Discount-V_AdjustAmount+V_ShareDiscount),
			V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,
			V_ApiGoodsName,V_ApiSpecName,V_Weight*V_Num,V_Volume*V_Num,TRUNCATE(V_ShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,
			V_InvoiceType,V_InvoiceContent,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
	ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			-- 取组合装信息
			SELECT suite_no,suite_name,is_unsplit,is_print_suite INTO V_SuiteNO,V_SuiteName,V_IsUnsplit,V_IsPrintSuite
			FROM goods_suite WHERE suite_id=V_MatchTargetID;
			
			IF V_IsPrintSuite THEN
				SET V_IsUnsplit=1;
			END IF;
			
			IF V_IsUnsplit AND (V_BindOid IS NULL OR V_BindOid='') THEN
				SET V_BindOid = V_Oid;
			END IF;
			
			-- 货品数量
			SELECT SUM(gs.retail_price*gsd.num),COUNT(1) INTO V_SuitePrice,V_SuiteSpecCount 
			FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
			WHERE gsd.suite_id=V_MatchTargetID;			
			SET V_I=0;
			SET V_TmpShareAmount = V_ShareAmount;
			SET V_LeftShareAmount = V_ShareAmount;
			SET V_TotalDiscount = V_Discount-V_AdjustAmount+V_ShareDiscount;
			SET V_LeftDiscount = V_TotalDiscount;
			SET V_LeftPost = V_SharePost;
			SET V_LeftPaid = V_Paid;
			
			OPEN goods_suite_cursor;
			SUITE_GOODS_LABEL: LOOP
				FETCH goods_suite_cursor INTO V_SpecID,V_SNum,V_IsFixedPrice,V_FixedPrice,V_Ratio,V_SGoodsName,V_SGoodsID,V_SGoodsNO,
					V_SSpecName,V_SSpecNO,V_SSpecCode,V_Weight,V_Volume,V_TaxRate,V_LargeType,V_SuiteItemPrice,V_IsZeroCost,V_IsDeleted;
				IF V_NOT_FOUND THEN
					LEAVE SUITE_GOODS_LABEL;
				END IF;
				
				IF V_IsDeleted THEN
					CLOSE goods_suite_cursor;
					IF P_UseTran THEN
						ROLLBACK;
						CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
					END IF;
					SET P_Message = CONCAT('，系统订单更换失败，原因：新货品组合装包含已删除单品 ', V_SSpecNO);
					SET P_ChangeStatus=1;
					LEAVE MAIN_LABEL;
				END IF;
				
				SET V_I=V_I+1;
				-- 分摊处理: 成交价,分摊价,邮费,已付,折扣
				-- 组合装约束性. 货品数量不能为0，占比和为1
				
				SET V_SNum = V_SNum*V_Num;
				-- 成交价分摊, 分摊价=成交价
				IF V_IsFixedPrice THEN
					SET V_Tmp=TRUNCATE(V_FixedPrice*V_SNum,4);
					IF V_TmpShareAmount >= V_Tmp THEN
						SET V_OrderPrice = V_FixedPrice;
						SET V_TmpShareAmount = V_TmpShareAmount - V_Tmp;
					ELSE
						SET V_OrderPrice = TRUNCATE(V_TmpShareAmount/V_SNum,4);
						SET V_TmpShareAmount = 0;
					END IF;
				ELSE
					SET V_OrderPrice = TRUNCATE(V_TmpShareAmount*V_Ratio/V_SNum,4);
				END IF;
				
				-- 最后一条
				IF V_I=V_SuiteSpecCount THEN
					SET V_SShareAmount=V_LeftShareAmount;
					SET V_LeftShareAmount=0;
				ELSE
					SET V_SShareAmount=TRUNCATE(V_OrderPrice*V_SNum,4);
					SET V_LeftShareAmount=V_LeftShareAmount-V_SShareAmount;
				END IF;
				
				IF V_ShareAmount<=0 THEN
					IF V_SuitePrice>0 THEN
						SET V_Ratio=TRUNCATE(V_SuiteItemPrice/V_SuitePrice,4);
					ELSE
						SET V_Ratio=TRUNCATE(1.0/V_SuiteSpecCount,4);
					END IF;
				ELSE
					SET V_Ratio=TRUNCATE(V_SShareAmount/V_ShareAmount,4);
				END IF;
				
				-- 最后一条
				IF V_I=V_SuiteSpecCount THEN
					-- 邮费
					SET V_SSharePost = V_LeftPost;
					SET V_LeftPost = 0;
					
					-- 已付
					SET V_Paid=V_LeftPaid;
					SET V_LeftPaid=0;
					
					-- 折扣
					SET V_SDiscount=V_LeftDiscount;
					SET V_LeftDiscount=0;
				ELSE
					-- 邮费
					SET V_SSharePost = V_SharePost*V_Ratio;
					IF V_SSharePost > V_LeftPost THEN
						SET V_LeftPost=V_LeftPost;
						SET V_LeftPost=0;
					ELSE
						SET V_LeftPost = V_LeftPost-V_SSharePost;
					END IF;
					
					-- 已付
					IF V_LeftPaid >= V_SShareAmount+V_SSharePost THEN
						SET V_Paid = V_SShareAmount+V_SSharePost;
						SET V_LeftPaid=V_LeftPaid-V_Paid;
					ELSE
						SET V_Paid=V_LeftPaid;
						SET V_LeftPaid=0;
					END IF;
					
					-- 折扣
					SET V_SDiscount=V_TotalDiscount*V_Ratio;
					IF V_SDiscount > V_LeftDiscount THEN
						SET V_SDiscount=V_LeftDiscount;
						SET V_LeftDiscount=0;
					ELSE
						SET V_LeftDiscount=V_LeftDiscount-V_SDiscount;
					END IF;
				END IF;
				
				-- 原价
				SET V_SPrice=TRUNCATE((V_SShareAmount+V_SDiscount)/V_SNum,4);
				 
				-- 分摊已付
				IF V_Paid >= V_SShareAmount+V_SSharePost THEN
					SET V_OrderDeliveryTerm = 1;
				ELSE
					SET V_OrderDeliveryTerm = V_DeliveryTerm;
				END IF;
				
				-- 将组合装中的单品信息插入货品临时表
				INSERT INTO tmp_sales_trade_order(
					spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,
					order_price,share_price,share_amount,share_post,discount,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
					api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,suite_id,suite_no,suite_name,suite_num,suite_amount,
					suite_discount,is_print_suite,from_mask,gift_type,cid,is_allow_zero_cost,remark)
				VALUES(V_SpecID,P_ShopID,V_PlatformID,P_Oid,P_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_SNum,V_SPrice,IF(V_RefundStatus>2,0,V_SNum),
					V_OrderPrice,V_OrderPrice,V_SShareAmount,V_SSharePost,V_SDiscount,V_Paid,V_SGoodsName,V_SGoodsID,V_SGoodsNO,V_SSpecName,V_SSpecNO,V_SSpecCode,
					V_ApiGoodsName,V_ApiSpecName,V_Weight*V_SNum,V_Volume*V_SNum,TRUNCATE(V_SShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,V_InvoiceType,V_InvoiceContent,V_MatchTargetID,V_SuiteNO,V_SuiteName,V_Num,
					V_ShareAmount,V_TotalDiscount,V_IsPrintSuite,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
				/*
				-- 找一个未退款的，金额最大的子订单作主订单,不考虑主订单
				IF V_RefundStatus<=2 AND V_SShareAmount > V_MasterAmount THEN
					SET V_MasterAmount=V_SShareAmount;
					SET V_MasterID = LAST_INSERT_ID();
				END IF;
				*/
			END LOOP;
			CLOSE goods_suite_cursor;
		END IF;
	-- 添加新货品，并占用相应待审核量库存
	IF P_TradeID THEN
		INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,stock_reserved,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,created)
		SELECT P_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,V_StockReserved,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,NOW()
		FROM tmp_sales_trade_order;
		-- 占用库存量，记录日志
		INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
		(SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(V_StockReserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'平台自动换货，新货品占用库存' FROM sales_trade_order sto 
		LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=V_WarehouseID WHERE V_StockReserved>=2 AND sto.trade_id=P_TradeID AND sto.shop_id=P_ShopID AND sto.src_oid=P_Oid);
		
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT V_WarehouseID,spec_id,IF(V_StockReserved=2,actual_num,0),IF(V_StockReserved=3,actual_num,0),
		IF(V_StockReserved=4,actual_num,0),IF(V_StockReserved=5,actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID AND shop_id=P_ShopID AND src_oid=P_Oid ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
		sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);

		SET P_Message="，系统货品更换成功";
		SET P_ChangeStatus=0;
	END IF;
END//
DELIMITER ;