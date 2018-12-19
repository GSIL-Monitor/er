DROP PROCEDURE IF EXISTS `I_DL_DELIVER_WMS_API_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_WMS_API_TRADE`(IN `P_ApiTradeID` BIGINT, IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
    COMMENT '递交自动流转委外的订单'
MAIN_LABEL:
BEGIN 
	DECLARE V_ApiOrderCount,V_OrderCount,V_SalesOrderCount,V_SalesmanID,V_TradeID,V_RecID,V_MatchTargetID,V_GoodsID,
		V_CustomerID,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_WarehouseID,
		V_LogisticsID,V_LogisticsID2,V_FlagID,V_TradeMask,V_IsFreezed,V_LogisticsType,V_Locked,V_CustomerType,V_PackageID,
		V_Max,V_Min,V_UnmergeMask,V_GiftMask,V_NOT_FOUND,V_SalesTradeMask INT DEFAULT(0);
	DECLARE V_ApiGoodsCount,V_GoodsCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,V_PlatformCost,
		V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_GoodsCost,
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
	DECLARE V_PlatformID,V_ProcessStatus,V_ApiTradeStatus,V_TradeStatus,V_PayStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_FenxiaoType,V_RemarkFlag,V_InvoiceType,V_TradeFrom,V_WmsType,V_IsAutoWms,V_IsSealed,V_IsExternal,V_OrderStatus,
		V_MatchTargetType,V_IsLarge,V_IsUnsplit,V_IsPrintSuite,V_IsPreorder,V_IsPreorder2,V_IsNotUseAir,V_IsSingleBatch TINYINT DEFAULT(0);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	DECLARE V_Tid,V_FenxiaoNick,V_BuyerNick,V_BuyerName,V_ReceiverName,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_Currency VARCHAR(40) DEFAULT '';
	DECLARE V_BuyerEmail,V_ReceiverArea,V_AreaAlias VARCHAR(64) DEFAULT '';
	DECLARE V_BuyerArea,V_ExtMsg,V_IDcard VARCHAR(40) DEFAULT '';
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ToDeliverTime,
		V_DistCenter,V_DistSite,V_ReceiverRing,V_SingleSpecNO VARCHAR(40) DEFAULT '';
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_GoodsName,V_SuiteName,V_PayAccount,V_WarehouseSelectLog VARCHAR(256) DEFAULT '';
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime,V_Now DATETIME;
	DECLARE V_BuyerMessage,V_Remark VARCHAR(1024) DEFAULT '';
	DECLARE V_Timestamp,V_DelayToTime INT DEFAULT(0);
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	/*
		业务员处理
		直接递交订单,映射商品
			如果有无效货品的，标记无效货品
		未付款订单进入待付款状态
	*/
	
	SET @sys_code=0, @sys_message = 'OK';
	
	-- 读出所有信息
	SELECT platform_id,shop_id,tid,process_status,trade_status,guarantee_mode,pay_status,delivery_term,refund_status,fenxiao_type,
		fenxiao_nick,order_count,goods_count,trade_time,pay_time,pay_account,buyer_message,remark,remark_flag,buyer_nick,buyer_name,
		buyer_email,buyer_area,logistics_type,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,
		receiver_address,receiver_mobile,receiver_telno,receiver_zip,receiver_area,receiver_ring,to_deliver_time,
		goods_amount,post_amount,other_amount,discount,receivable,platform_cost,ext_cod_fee,paid,trade_mask,
		invoice_type,invoice_title,invoice_content,trade_from,wms_type,is_auto_wms,warehouse_no,stockout_no,is_sealed,is_external,currency,id_card
	INTO 
		V_PlatformID,V_ShopID,V_Tid,V_ProcessStatus,V_ApiTradeStatus,V_GuaranteeMode,V_PayStatus,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,
		V_FenxiaoNick,V_OrderCount,V_GoodsCount,V_TradeTime,V_PayTime,V_PayAccount,V_BuyerMessage,V_Remark,V_RemarkFlag,V_BuyerNick,V_BuyerName,
		V_BuyerEmail,V_BuyerArea,V_LogisticsType,V_ReceiverName,V_ReceiverCountry,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,V_ToDeliverTime,
		V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,V_PlatformCost,V_ExtCodFee,V_Paid,V_TradeMask,
		V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_TradeFrom,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_StockoutNO,V_IsSealed,V_IsExternal,V_Currency,V_IDcard
	FROM api_trade WHERE rec_id = P_ApiTradeID FOR UPDATE;
	
	IF V_NOT_FOUND THEN
		ROLLBACK;
		SET @sys_code=1, @sys_message = '订单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT V_IsAutoWMS THEN
		ROLLBACK;
		SET @sys_code=1, @sys_message = '非自动流转订单';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_WmsType=1 THEN
		ROLLBACK;
		SET @sys_code=1, @sys_message = '非外部仓库';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '订单状态不正确';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ApiTradeStatus>70 THEN
		UPDATE api_trade SET process_status = 70 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderCount = 0 OR V_GoodsCount = 0 THEN
		-- 订单无货品
		UPDATE api_trade SET bad_reason=8 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_PayStatus AND V_PayTime = '0000-00-00 00:00:00' THEN 
		SET V_PayTime = V_TradeTime;
	END IF;
	
	SET V_Now = NOW();
	
	-- 映射货品
	CALL I_DL_MAP_TRADE_GOODS(0, P_ApiTradeID, 1, V_ApiOrderCount, V_ApiGoodsCount);
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ApiOrderCount <> V_OrderCount OR V_ApiGoodsCount <> V_GoodsCount THEN
		UPDATE api_trade SET bad_reason=2 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		SET @sys_code=7, @sys_message = CONCAT('订单货品数量不一致',V_ApiOrderCount,'-',V_OrderCount,'----',V_ApiGoodsCount,'-',V_GoodsCount);
		LEAVE MAIN_LABEL;
	END IF;

	-- 选择仓库, 备注提取的优先级高
	-- 直接选仓库
	CALL I_DL_DECIDE_WAREHOUSE(
		V_WarehouseID, 
		V_WmsType, 
		V_Locked, 
		V_WarehouseNO, 
		V_ShopID, 
		0,
		V_ReceiverProvince, 
		V_ReceiverCity, 
		V_ReceiverDistrict,
		0,
		V_WareHouseSelectLog);
	-- 自动转入库存不存在,标记无效订单

	IF V_WarehouseID=0 THEN
		UPDATE api_trade SET bad_reason=4 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		SET @sys_code=8, @sys_message = '订单无可选外部仓库';
		LEAVE MAIN_LABEL;
	END IF;

	-- 直接选择物流
	SELECT logistics_id INTO V_LogisticsID FROM cfg_logistics WHERE is_disabled=0 AND logistics_type=1 AND logistics_id>0 ORDER BY priority LIMIT 1;
  
	-- 根据货品来还原金额值
	SELECT SUM(actual_num),COUNT(DISTINCT spec_id),SUM(weight),SUM(paid),
		MAX(delivery_term),SUM(share_amount+discount),SUM(share_post),SUM(discount),
		SUM(IF(delivery_term=1,share_amount+share_post,paid)),
		SUM(IF(delivery_term=2,share_amount+share_post-paid,0)),
		BIT_OR(gift_type),SUM(commission),SUM(volume)
	INTO V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_Paid,V_DeliveryTerm,
		V_GoodsAmount,V_PostAmount,V_Discount,V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
	FROM tmp_sales_trade_order WHERE refund_status<=2 AND actual_num>0;

	SET V_SalesTradeMask=0;
	
	-- 订单无货品
	IF V_SalesGoodsCount IS NULL THEN
		UPDATE api_trade SET bad_reason=8 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		SET @sys_code=3, @sys_message = '订单无货品';
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
	INTO V_Max,V_Min
	FROM tmp_sales_trade_order;
	
	
	-- 退款状态
	IF V_SalesGoodsCount=0 THEN
		SET V_RefundStatus=3;
		SET V_TradeStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_RefundStatus=1;
	ELSEIF V_Max THEN
		SET V_RefundStatus=2;
	ELSE
		SET V_RefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_ApiOrderCount, V_ApiGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM tmp_sales_trade_order
	WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY platform_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 OR @sys_single_spec_show=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;
	
	SET V_Timestamp = UNIX_TIMESTAMP();
	
	-- 计算订单状态
	IF V_TradeStatus= 5 THEN
		BEGIN END;
	ELSEIF V_ApiTradeStatus IN (30,50,60,70) THEN
		SET V_TradeStatus=21;  -- 已递交仓库,委外订单前处理
		SET V_SalesTradeMask = V_SalesTradeMask|32;
		SET V_ExtMsg=' 自动流转委外订单';
		-- 直接找一个无单号物流
	ELSEIF V_ApiTradeStatus=10 THEN
		SET V_TradeStatus=10;  -- 待付款
	ELSEIF V_ApiTradeStatus=20 THEN
		SET V_TradeStatus=12;  -- 待尾款
	ELSE
		SET V_TradeStatus=5;  -- 已取消
	END IF;
	
	-- 更新客户资料
	-- 分销商有待处理	
	IF V_BuyerNick IS NULL OR V_BuyerNick = '' THEN
		IF V_ReceiverMobile<>'' THEN
			SET V_BuyerNick = CONCAT('MOB', V_ReceiverMobile);
		ELSEIF V_ReceiverTelno<>'' THEN
			SET V_BuyerNick = CONCAT('TEL', V_ReceiverTelno);
		ELSE
			SET V_BuyerNick = '未知买家';
		END IF;
	END IF;
	
	-- 查找客户,按昵称和平台查询
	SELECT customer_id INTO V_CustomerID FROM crm_platform_customer WHERE platform_id=V_PlatformID AND account=V_BuyerNick;
	IF V_NOT_FOUND THEN -- 如果客户不存在
		-- 看是否有手工导入的客户
		SET V_NOT_FOUND=0;
		SELECT customer_id INTO V_CustomerID FROM crm_platform_customer WHERE platform_id=0 AND account=V_BuyerNick; 
		IF V_NOT_FOUND=0 THEN 
			-- 存在导入客户,有相同网名
			-- 要求此导入客户与订单里客户至少有一个电话号码相同或姓名相同,否则不要合并
			IF NOT EXISTS(SELECT 1 FROM crm_customer_telno WHERE customer_id=V_CustomerID AND telno IN(V_ReceiverMobile,V_ReceiverTelno)) AND
				NOT EXISTS(SELECT 1 FROM crm_customer_address WHERE customer_id=V_CustomerID AND `name`=V_ReceiverName) THEN
				SET V_CustomerID = 0;
			END IF;
		END IF;
		
		SET V_NOT_FOUND=0;
		IF V_CustomerID = 0 THEN -- 要建新客户
			INSERT INTO crm_customer(customer_no,`type`,NAME,nickname,province,city,district,`area`,address,zip,telno,mobile,email,wangwang,qq,
				trade_count,trade_amount,created)
			VALUES(FN_SYS_NO('customer'),(V_FenxiaoType>1),V_ReceiverName,V_BuyerNick,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
				V_ReceiverArea,V_ReceiverAddress,V_ReceiverZip,V_ReceiverTelno,
				V_ReceiverMobile,V_BuyerEmail,IF(V_PlatformID=1,V_BuyerNick,''),IF(V_PlatformID=4,V_BuyerNick,''),0,0,V_Now);
			
			SET V_CustomerID = LAST_INSERT_ID();
			
			INSERT INTO crm_platform_customer(platform_id,account,customer_id,created) VALUES(V_PlatformID, V_BuyerNick, V_CustomerID,NOW());
		ELSE
			-- 合并客户
			INSERT IGNORE INTO crm_platform_customer(platform_id,account,customer_id,created) VALUES(V_PlatformID, V_BuyerNick, V_CustomerID, NOW());
		END IF;
	ELSE -- 客户存在
		SELECT `type` INTO V_CustomerType FROM crm_customer WHERE customer_id=V_CustomerID;
	END IF;
	
	-- 更新地址库
	INSERT IGNORE INTO crm_customer_address(customer_id,`name`,addr_hash,province,city,district,address,zip,telno,mobile,email,created)
	VALUES(V_CustomerID,V_ReceiverName,MD5(CONCAT(V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverAddress)),
		V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverAddress,V_ReceiverZip,V_ReceiverTelno,
		V_ReceiverMobile,V_BuyerEmail, V_Now);
	
	IF V_ReceiverMobile<> '' THEN
		INSERT IGNORE INTO crm_customer_telno(customer_id,TYPE,telno,created) VALUES(V_CustomerID, 1, V_ReceiverMobile,V_Now);
		-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 1, V_ReceiverMobile);
	END IF;
	
	IF V_ReceiverTelno<> '' THEN
		INSERT IGNORE INTO crm_customer_telno(customer_id,TYPE,telno,created) VALUES(V_CustomerID, 2, V_ReceiverTelno,V_Now);
		-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 2, V_ReceiverTelno);
	END IF;
	
	-- 生成处理单
	-- V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_Paid,V_DeliveryTerm,V_GoodsAmount,V_PostAmount,V_Discount,V_DapAmount,V_CodAmount
		INSERT INTO sales_trade(
		trade_no,platform_id,shop_id,src_tids,trade_status,trade_type,trade_from,delivery_term,refund_status,fenxiao_type,fenxiao_nick,
		trade_time,pay_time,pay_account,customer_type,customer_id,buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_address,
		receiver_mobile,receiver_telno,receiver_zip,receiver_area,receiver_ring,
		to_deliver_time,dist_center,dist_site,buyer_message,cs_remark,remark_flag,
		goods_count,goods_type_count,weight,volume,logistics_id,receiver_dtb,post_cost,goods_cost,cs_remark_change_count,
		buyer_message_count,cs_remark_count,paid,goods_amount,post_amount,other_amount,discount,receivable,flag_id,warehouse_id,
		dap_amount,cod_amount,pi_amount,ext_cod_fee,invoice_type,invoice_title,invoice_content,warehouse_type,stockout_no,package_id,
		salesman_id,is_sealed,freeze_reason,delay_to_time,commission,gift_mask,unmerge_mask,raw_goods_type_count,raw_goods_count,single_spec_no,profit,created)
	VALUES(FN_SYS_NO('sales'),V_PlatformID,V_ShopID,V_Tid,V_TradeStatus,1,V_TradeFrom,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,V_FenxiaoNick,
		V_TradeTime,V_PayTime,V_PayAccount,V_CustomerType,V_CustomerID,V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,
		V_ToDeliverTime,V_DistCenter,V_DistSite,V_BuyerMessage,V_Remark,V_RemarkFlag,
		V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_TotalVolume,V_LogisticsID,V_AreaAlias,V_PostCost,V_GoodsCost,
		NOT FN_EMPTY(V_Remark),NOT FN_EMPTY(V_BuyerMessage),NOT FN_EMPTY(V_Remark),
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType,V_StockoutNO,V_PackageID,
		V_SalesmanID,V_IsSealed,V_IsFreezed,V_DelayToTime,V_Commission,V_GiftMask,V_UnmergeMask,V_ApiOrderCount,V_ApiGoodsCount,V_SingleSpecNO,V_GoodsAmount+V_PostAmount-V_Discount-V_GoodsCost-V_PostCost-V_Commission,V_Now);
	


	SET V_TradeID = LAST_INSERT_ID();
	
	-- 从临时表插入货品	
	INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
		bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
		goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,is_print_suite,api_goods_name,api_spec_name,weight,
		commission,cid,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,remark,created)
	SELECT V_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
		bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
		goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,is_print_suite,api_goods_name,api_spec_name,weight,
		commission,cid,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,remark,V_Now
	FROM tmp_sales_trade_order;
	
	DELETE FROM tmp_sales_trade_order;
	
	-- 日志
	-- 下单，付款时间
	INSERT INTO sales_trade_log(trade_id,operator_id,TYPE,DATA,message,created) VALUES(V_TradeID,P_OperatorID,1,P_ApiTradeID,CONCAT('客户下单:',V_Tid), V_TradeTime);
	IF V_PayStatus THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,TYPE,DATA,message,created) VALUES(V_TradeID,P_OperatorID,2,P_ApiTradeID,CONCAT('客户付款:',V_Tid), V_PayTime);
	END IF;
	INSERT INTO sales_trade_log(trade_id,operator_id,TYPE,message) VALUES(V_TradeID,P_OperatorID,3,CONCAT('订单递交:', V_Tid, V_ExtMsg));

	-- 库存占用
	IF V_TradeStatus=10 THEN	-- 未付款
		CALL I_RESERVE_STOCK(V_TradeID, 2, V_WarehouseID, 0);
	ELSEIF V_TradeStatus=21 THEN -- 已转到平台仓库(如淘宝菜鸟)占用待发货量
		CALL I_RESERVE_STOCK(V_TradeID, 4, V_WarehouseID, 0);
		IF V_ApiTradeStatus IN(50,60,70) THEN
			SET V_TradeStatus = 110;
			CALL I_DL_AUTO_WMS_CONSIGN(V_TradeID,P_OperatorID);
			IF @sys_code<>0 THEN
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
	END IF;
	
	-- 创建退款单
	IF @tmp_refund_occur THEN
		CALL I_DL_PUSH_REFUND(P_OperatorID, V_PlatformID, V_Tid);
	END IF;
	
	-- 记录客服备注
	IF V_Remark IS NOT NULL AND V_Remark<>'' THEN
		INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
	END IF;
	
	
	
	-- 更新原始单
	UPDATE api_trade SET process_status=IF(V_TradeStatus = 110,60,20),
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_warehouse_id=V_WarehouseID,
		modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
	UPDATE api_trade_order SET modify_flag=0,process_status=IF(V_TradeStatus = 110,60,20) WHERE platform_id=V_PlatformID AND tid=V_Tid;
	
	COMMIT;
	
END//
DELIMITER ;
