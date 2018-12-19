DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE`(IN `P_ApiTradeID` BIGINT, IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
    COMMENT '提交原始单生成订单,对原始单应用各种策略'
MAIN_LABEL:
BEGIN 
	DECLARE V_ApiOrderCount,V_OrderCount,V_SalesOrderCount,V_SalesmanID,V_TradeID,V_RecID,V_MatchTargetID,V_GoodsID,
		V_CustomerID,V_PlatformCustomer,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_WarehouseID,V_WarehouseID2,
		V_LogisticsID,V_FlagID,V_TradeMask,V_IsPreorder,V_IsFreezed,V_LogisticsType,V_Locked,V_CustomerType,V_PackageID,
		V_Max,V_Min,V_ShopHoldEnabled,V_UnmergeMask,V_GiftMask,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ApiGoodsCount,V_GoodsCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,V_PlatformCost,
		V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_GoodsCost,
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume, 
		V_SuiteWeight,V_SpecWeight DECIMAL(19,4) DEFAULT(0);
	DECLARE V_PlatformID,V_ProcessStatus,V_ApiTradeStatus,V_TradeStatus,V_PayStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_FenxiaoType,V_RemarkFlag,V_InvoiceType,V_TradeFrom,V_WmsType,V_WmsType2,V_IsAutoWms,V_IsSealed,V_IsExternal,V_OrderStatus,
		V_MatchTargetType,V_IsLarge,V_IsUnsplit,V_IsPrintSuite TINYINT DEFAULT(0);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	DECLARE V_Tid,V_FenxiaoNick,V_BuyerNick,V_BuyerName,V_ReceiverName,V_WarehouseNO,V_StockoutNO,V_StockoutNO2 VARCHAR(40);
	DECLARE V_BuyerEmail,V_ReceiverArea,V_AreaAlias VARCHAR(64);
	DECLARE V_BuyerArea,V_ExtMsg VARCHAR(40);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ToDeliverTime,
		V_DistCenter,V_DistSite,V_ReceiverRing,V_SingleSpecNO VARCHAR(40);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_GoodsName,V_SuiteName,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime,V_Now DATETIME;
	DECLARE V_BuyerMessage,V_Remark VARCHAR(1024);
	DECLARE V_Timestamp,V_DelayToTime INT DEFAULT(0);
	DECLARE V_LogisticsName,V_WarehouseName,V_ShopName,V_LogisticsMatchLog,V_LogisticsFeeLog,V_WarehouseSelectLog,V_RemarkLog,V_ClientRemarkLog,V_AreaAliasLog,V_SelectLogisticsByGoods VARCHAR(256);
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	/*
		业务员处理
		直接递交订单,映射商品
			如果有未匹配货品的，标记未匹配货品
		未付款订单进入待付款状态
	*/
	
	SET @sys_code=0, @sys_message = 'OK';
	-- 在调用I_DL_MAP_TRADE_GOODS的事务之前先创建临时表
	CALL I_DL_TMP_SALES_TRADE_ORDER();
	START TRANSACTION;
	-- 读出所有信息
	SELECT platform_id,shop_id,tid,process_status,trade_status,guarantee_mode,pay_status,delivery_term,refund_status,fenxiao_type,
		fenxiao_nick,order_count,goods_count,trade_time,pay_time,pay_account,buyer_message,remark,remark_flag,buyer_nick,buyer_name,
		buyer_email,buyer_area,logistics_type,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,
		receiver_address,receiver_mobile,receiver_telno,receiver_zip,receiver_area,receiver_ring,to_deliver_time,dist_center,dist_site,
		goods_amount,post_amount,other_amount,discount,receivable,platform_cost,ext_cod_fee,paid,trade_mask,
		invoice_type,invoice_title,invoice_content,trade_from,wms_type,is_auto_wms,warehouse_no,stockout_no,is_sealed,is_external
	INTO 
		V_PlatformID,V_ShopID,V_Tid,V_ProcessStatus,V_ApiTradeStatus,V_GuaranteeMode,V_PayStatus,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,
		V_FenxiaoNick,V_OrderCount,V_GoodsCount,V_TradeTime,V_PayTime,V_PayAccount,V_BuyerMessage,V_Remark,V_RemarkFlag,V_BuyerNick,V_BuyerName,
		V_BuyerEmail,V_BuyerArea,V_LogisticsType,V_ReceiverName,V_ReceiverCountry,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,V_ToDeliverTime,V_DistCenter,V_DistSite,
		V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,V_PlatformCost,V_ExtCodFee,V_Paid,V_TradeMask,
		V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_TradeFrom,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_StockoutNO,V_IsSealed,V_IsExternal
	FROM api_trade WHERE rec_id = P_ApiTradeID FOR UPDATE;
	
	IF V_NOT_FOUND THEN
		ROLLBACK;
		SET @sys_code=1, @sys_message = '原始单不存在';
		LEAVE MAIN_LABEL;
	END IF;

	IF V_IsAutoWMS THEN
		CALL I_DL_DELIVER_WMS_API_TRADE(P_ApiTradeID, P_OperatorID);
		LEAVE MAIN_LABEL;
	END IF;

	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '原始单状态不正确或已经递交';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ApiTradeStatus>=40 THEN
		UPDATE api_trade SET process_status=70,bad_reason=0 WHERE rec_id = P_ApiTradeID;
		UPDATE api_trade_order SET is_invalid_goods=0,process_status=70 WHERE platform_id = V_PlatformID AND tid=V_Tid;
		COMMIT;
		SET @sys_code=2, @sys_message = '原始单不需要递交';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 进入系统之前已经发货了
	IF V_IsExternal THEN
		UPDATE api_trade SET process_status=70 WHERE rec_id = P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderCount = 0 OR V_GoodsCount = 0 THEN
		-- 订单无货品
		UPDATE api_trade SET bad_reason=8 WHERE rec_id = P_ApiTradeID;
		SET @sys_code=3, @sys_message = '订单无货品';
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 非款到发货的订单，不可拆分合并
	IF V_DeliveryTerm<>1 THEN
		SET V_IsSealed=1;
	END IF;
	
	IF V_PayStatus AND V_PayTime = '1000-01-01 00:00:00' THEN 
		SET V_PayTime = V_TradeTime;
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
	
	SET V_Now = NOW();
	
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
			INSERT INTO crm_customer(customer_no,`type`,name,nickname,province,city,district,`area`,address,zip,telno,mobile,email,wangwang,qq,
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
		INSERT IGNORE INTO crm_customer_telno(customer_id,type,telno,created) VALUES(V_CustomerID, 1, V_ReceiverMobile,V_Now);
		-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 1, V_ReceiverMobile);
	END IF;
	
	IF V_ReceiverTelno<> '' THEN
		INSERT IGNORE INTO crm_customer_telno(customer_id,type,telno,created) VALUES(V_CustomerID, 2, V_ReceiverTelno,V_Now);
		-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 2, V_ReceiverTelno);
	END IF;
	
	-- 映射货品
	CALL I_DL_MAP_TRADE_GOODS(0, P_ApiTradeID, 1, V_ApiOrderCount, V_ApiGoodsCount);
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ApiOrderCount <> V_OrderCount OR V_ApiGoodsCount <> V_GoodsCount THEN
		ROLLBACK;
		UPDATE api_trade SET bad_reason=2 WHERE rec_id = P_ApiTradeID;
		SET @sys_code=7, @sys_message = CONCAT('原始单货品数量不一致',V_ApiOrderCount,'-',V_OrderCount,'----',V_ApiGoodsCount,'-',V_GoodsCount);
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 备注提取
	SET V_Remark=TRIM(V_Remark),V_ExtMsg='';
	SET V_WmsType2=V_WmsType;
	CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed,V_RemarkLog);
	IF V_IsPreorder THEN
		SET V_ExtMsg = ' 进预订单原因:客服备注提取';
	END IF;
	
	-- 客户备注
	SET V_BuyerMessage=TRIM(V_BuyerMessage);
	CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_LogisticsID, V_IsFreezed,V_ClientRemarkLog);
	
	-- select warehouse_id randomly
	-- SELECT warehouse_id INTO V_WarehouseID2 FROM cfg_warehouse where is_disabled = 0 limit 1;
	
	-- get logistics_id from cfg_shop => I_DL_DECIDEC_LOGISTICS
	/*IF V_DeliveryTerm=2 THEN
		SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	  ELSE 
		SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	END IF;*/
    
    -- 选择仓库, 备注提取的优先级高
	CALL I_DL_DECIDE_WAREHOUSE(
		V_WarehouseID2, 
		V_WmsType2, 
		V_Locked, 
		V_WarehouseNO, 
		V_ShopID, 
		0, 
		V_ReceiverProvince, 
		V_ReceiverCity, 
		V_ReceiverDistrict,
		V_ShopHoldEnabled,
		V_WareHouseSelectLog);-- V_ShopHoldEnabled=0 是否抢单
        -- 自动转入库存不存在,标记无效订单
	IF V_WarehouseID2=0 THEN
		ROLLBACK;
		UPDATE api_trade SET bad_reason=4 WHERE rec_id = P_ApiTradeID;
		SET @sys_code=2, @sys_message = '订单无可选仓库，请检查系统是否已新建仓库';
		-- INSERT INTO aux_notification(type,message,priority,order_type,order_no) VALUES(2,'订单无可选仓库',9,1,V_Tid);
		LEAVE MAIN_LABEL;
	END IF;
    
    IF V_WarehouseID AND V_WarehouseID2<>V_WarehouseID AND NOT V_Locked THEN
		SET V_WarehouseID2 = V_WarehouseID;
		SET V_WmsType2=V_WmsType;
		SET V_WareHouseSelectLog='';
	END IF;
	/*
	-- 根据货品关键字转预订单处理
	IF V_ApiTradeStatus=30 THEN
		IF @cfg_order_go_preorder AND NOT V_IsPreorder THEN
			SELECT 1 INTO V_IsPreorder FROM api_trade_order ato, cfg_preorder_goods_keyword cpgk 
			WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND LOCATE(cpgk.keyword,ato.goods_name)>0 LIMIT 1;
			
			IF V_IsPreorder THEN
				SET V_ExtMsg = ' 进预订单原因:平台货品名称包含关键词';
			END IF;
		ELSE
			SET V_IsPreorder = 0;
		END IF;
	END IF;
	
	-- 是否开启了抢单
	IF V_ApiTradeStatus=30 AND NOT V_IsPreorder AND @cfg_order_deliver_hold THEN
		SELECT is_hold_enabled INTO V_ShopHoldEnabled FROM sys_shop WHERE shop_id=V_ShopID;
	END IF;
	
	-- 选择仓库, 备注提取的优先级高
	CALL I_DL_DECIDE_WAREHOUSE(
		V_WarehouseID2, 
		V_WmsType2, 
		V_Locked, 
		V_WarehouseNO, 
		V_ShopID, 
		0, 
		V_ReceiverProvince, 
		V_ReceiverCity, 
		V_ReceiverDistrict,
		V_ShopHoldEnabled);
	
	-- 自动转入库存不存在,标记无效订单
	IF V_WarehouseID2=0 THEN
		ROLLBACK;
		UPDATE api_trade SET bad_reason=4 WHERE rec_id = P_ApiTradeID;
		INSERT INTO aux_notification(type,message,priority,order_type,order_no) VALUES(2,'订单无可选仓库',9,1,V_Tid);
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_WarehouseID AND V_WarehouseID2<>V_WarehouseID AND NOT V_Locked THEN
		SET V_WarehouseID2 = V_WarehouseID;
		SET V_WmsType2=V_WmsType;
	END IF;
	
	-- 所选仓库为实体店仓库才去抢单
	IF V_ShopHoldEnabled THEN
		SELECT (type=127 AND sub_type=1) INTO V_ShopHoldEnabled FROM cfg_warehouse WHERE warehouse_id=V_WarehouseID2;
	END IF;
	*/
	-- 根据货品来还原金额值
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
	
	-- 订单无货品
	IF V_DeliveryTerm IS NULL THEN
		ROLLBACK;
		UPDATE api_trade SET bad_reason=8 WHERE rec_id = P_ApiTradeID;
		SET @sys_code=3, @sys_message = '订单无货品';
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
	INTO V_Max,V_Min
	FROM tmp_sales_trade_order;
	
	-- 退款状态
	IF V_SalesGoodsCount=0 THEN
		SET V_RefundStatus=3,V_FlagID=8;
		SET V_TradeStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_RefundStatus=1,V_FlagID=8;
	ELSEIF V_Max THEN
		SET V_RefundStatus=2,V_FlagID=8;
	ELSE
		SET V_RefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_ApiOrderCount, V_ApiGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM tmp_sales_trade_order
	WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 THEN
	  IF @cfg_single_spec_no_code=1 THEN
      SELECT IF(suite_id,suite_name,CONCAT(spec_no,'-',spec_name)) INTO V_SingleSpecNO
      FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1;
	  ELSE
      SELECT IF(suite_id,suite_name,CONCAT(goods_name,'-',spec_name)) INTO V_SingleSpecNO
      FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1;
	  END IF;
	ELSEIF V_ApiOrderCount>1 THEN
		SET V_SingleSpecNO='多种货品';
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	/*
	-- 选择包装  根据包装策略选择包装 1 重量 2  体积    更新总预估重量  然后继续其他操作
	IF @cfg_open_package_strategy THEN
		CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_TotalWeight,V_TotalVolume);
		IF V_PackageID THEN
			SELECT weight INTO V_PackageWeight  FROM goods_spec WHERE spec_id = V_PackageID;
			SET V_TotalWeight=V_TotalWeight+V_PackageWeight;
		END IF;
	END IF;
	
	-- 选择物流,备注物流优化级高
	IF V_LogisticsID=0 THEN
		CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_WarehouseID2,V_TotalWeight, 
			0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress);
	END IF;
	
	-- 估算邮费
	CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID2, 
		0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
	
	-- 大头笔
	CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
	*/
	-- 开启按单品选择物流
	IF V_LogisticsID=0 AND @cfg_sales_trade_logistics_bygoods AND V_DeliveryTerm=1 THEN
		CALL I_DL_DECIDE_LOGISTICS_ASSIGN(V_LogisticsID,V_LogisticsType,V_DeliveryTerm,V_ShopID,V_WarehouseID2,V_ReceiverProvince,V_SelectLogisticsByGoods);
		/*IF V_LogisticsID >0 THEN
			SET V_LogisticsID2 = V_LogisticsID;
		END IF;*/		
	END IF;
	-- 选择物流,备注物流优化级高
	IF V_LogisticsID=0 THEN
		CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_WarehouseID2,V_TotalWeight, 
			 0,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress,V_Paid,V_LogisticsMatchLog);
	END IF;
    -- 估算邮费
	CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID2, 
		0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsFeeLog);
	
	-- 大头笔
	CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
	
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID2 AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;

	-- 库存不足转预订单处理
	IF V_ApiTradeStatus=30 AND @cfg_order_go_preorder AND @cfg_order_preorder_lack_stock AND 
		NOT V_IsPreorder AND NOT V_ShopHoldEnabled AND NOT V_IsAutoWMS THEN
		-- 此处库存细化
		
		SELECT 1 INTO V_IsPreorder FROM 
		(SELECT spec_id,SUM(actual_num) actual_num FROM tmp_sales_trade_order GROUP BY spec_id) tg 
			LEFT JOIN stock_spec ss on (ss.spec_id=tg.spec_id AND ss.warehouse_id=V_WarehouseID2)
		WHERE CASE @cfg_order_preorder_lack_stock
			WHEN 1 THEN	IFNULL(ss.stock_num-ss.sending_num-ss.order_num,0)
			ELSE IFNULL(ss.stock_num-ss.sending_num-ss.order_num-ss.subscribe_num,0)
		END < tg.actual_num LIMIT 1;
		
		IF V_IsPreorder THEN
			SET V_ExtMsg = ' 进预订单原因:订单货品库存不足'; 
		END IF;
	END IF;

	
	SET V_Timestamp = UNIX_TIMESTAMP();
	
	-- 计算订单状态
	IF V_TradeStatus= 5 THEN
		BEGIN END;
	ELSEIF V_IsAutoWMS AND V_WmsType2 <> 1 THEN
		SET V_TradeStatus=55;  -- 已递交仓库
								-- 要处理库存占用
		SET V_ExtMsg=' 委外订单';
	ELSE
		CASE V_ApiTradeStatus 
			WHEN 10 THEN  -- 未确认
				SET V_TradeStatus=10;  -- 待付款
			WHEN 20 THEN  -- 待尾款
				SET V_TradeStatus=12;  -- 待尾款
			WHEN 30 THEN -- 待发货
			    SET V_TradeStatus=20;
				-- SET V_TradeStatus=30;
				
				IF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
				/*
				ELSE
					IF V_ShopHoldEnabled AND LENGTH(@tmp_warehouse_enough)>0 THEN
					
						SET V_TradeStatus=27;  -- 抢单
						
						-- 抢单订单物流必须是无单号
						IF V_LogisticsType<>1 THEN
							SET V_LogisticsType=1;
							CALL I_DL_DECIDE_LOGISTICS(V_LogisticsID, V_LogisticsType, V_DeliveryTerm, V_ShopID, V_WarehouseID2,V_TotalWeight, 
								0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_ReceiverAddress);
							IF V_LogisticsID THEN
								-- 估算邮费
								CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID2, 
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
				END IF;	
			ELSE
				SET V_TradeStatus=5;  -- 已取消
		END CASE;
	END IF;
	
	IF V_TradeStatus=10 THEN -- 未付款
		-- 标记已付款的
		UPDATE sales_trade SET unmerge_mask=unmerge_mask|1,modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW()) WHERE customer_id=V_CustomerID AND trade_status>=10 AND trade_status<=95;
		IF ROW_COUNT()>0 THEN
			SET V_UnmergeMask=1;
		END IF;
	ELSEIF V_TradeStatus IN(16,19,20,27) THEN -- 已付款
		-- 标记同名未合并
		UPDATE sales_trade SET unmerge_mask=unmerge_mask|2,modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW()) WHERE customer_id=V_CustomerID AND trade_status>=10 AND trade_status<=95;
		IF ROW_COUNT()>0 THEN
			SET V_UnmergeMask=1;
		END IF;
	END IF;
	
	IF V_UnmergeMask THEN -- 有同名未合并的
		-- 计算本订单的状态
		SET V_UnmergeMask=0;
		-- 有未付款的
		IF EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status=10) THEN
			SET V_UnmergeMask=1;
		END IF;
		
		-- 有已付款的
		IF EXISTS(SELECT 1 FROM sales_trade WHERE customer_id=V_CustomerID AND trade_status>10 AND trade_status<=95) THEN
			SET V_UnmergeMask=V_UnmergeMask|2;
		END IF;
	END IF;
	/*
	-- 标记等未付
	IF @cfg_wait_unpay_sec > 0 THEN
		IF V_TradeStatus=10 THEN
			IF (V_UnmergeMask&2) AND UNIX_TIMESTAMP(V_TradeTime)+@cfg_wait_unpay_sec>V_Timestamp THEN -- 有已付款的
				-- 延时已付款，未进入审核的
				UPDATE sales_trade SET trade_status=15,delay_to_time=UNIX_TIMESTAMP(V_TradeTime)+@cfg_wait_unpay_sec 
				WHERE customer_id=V_CustomerID AND trade_status IN (16,20);
			END IF;
		ELSEIF V_TradeStatus=16 OR V_TradeStatus=20 OR V_TradeStatus=27 THEN -- 已付款
			IF (V_UnmergeMask&1) THEN
				SELECT MAX(trade_time) INTO V_OldTradeTime FROM sales_trade 
				WHERE customer_id=V_CustomerID AND trade_status=10;
				
				IF V_OldTradeTime IS NOT NULL AND UNIX_TIMESTAMP(V_OldTradeTime)+@cfg_wait_unpay_sec>V_Timestamp THEN
					SET V_TradeStatus=15;  -- 等未付
					SET V_DelayToTime=UNIX_TIMESTAMP(V_OldTradeTime)+@cfg_wait_unpay_sec;
					SET V_ExtMsg=' 等未付';
				END IF;
			END IF;
		END IF;
	END IF;
	*/
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
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID2,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType2,V_StockoutNO,V_PackageID,
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
	/*
	-- 库存占用
	IF V_WarehouseID2 > 0 THEN
		IF V_TradeStatus=10 THEN	-- 未付款
			CALL I_RESERVE_STOCK(V_TradeID, 2, V_WarehouseID2, 0);
		ELSEIF V_TradeStatus=15 OR V_TradeStatus=16 OR V_TradeStatus=20 OR V_TradeStatus=27 THEN	-- 已付款
			CALL I_RESERVE_STOCK(V_TradeID, 3, V_WarehouseID2, 0);
		ELSEIF V_TradeStatus=19 THEN -- 预订单
			CALL I_RESERVE_STOCK(V_TradeID, 5, V_WarehouseID2, 0);
		ELSEIF V_TradeStatus=55 THEN -- 已转到平台仓库(如物流宝)
			CALL I_SALES_TRADE_GENERATE_STOCKOUT(V_StockoutNO2,V_TradeID,V_WarehouseID2,55,V_StockoutNO);
			UPDATE sales_trade SET stockout_no=V_StockoutNO2 WHERE trade_id=V_TradeID;
		END IF;
	END IF;
	*/
	IF V_WarehouseID2 > 0 THEN
		IF V_TradeStatus=10 THEN	-- 未付款
			CALL I_RESERVE_STOCK(V_TradeID, 2, V_WarehouseID2, 0);
		ELSEIF V_TradeStatus=15 OR V_TradeStatus=16 OR V_TradeStatus=20 THEN	-- 已付款
			CALL I_RESERVE_STOCK(V_TradeID, 3, V_WarehouseID2, 0);
		ELSEIF V_TradeStatus=19 THEN -- 预订单
			CALL I_RESERVE_STOCK(V_TradeID, 5, V_WarehouseID2, 0);
		END IF;
	END IF;
	-- 创建退款单
	IF @tmp_refund_occur THEN
		CALL I_DL_PUSH_REFUND(P_OperatorID, V_ShopID, V_Tid);
	END IF;
	
	-- 记录客服备注
	IF V_Remark IS NOT NULL AND V_Remark<>'' THEN
		INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
	END IF;
	/*
	-- 保存抢单仓库
	IF V_ShopHoldEnabled AND LENGTH(@tmp_warehouse_enough)>0 THEN
		CALL SP_EXEC(CONCAT('INSERT IGNORE INTO sales_trade_warehouse(trade_id,warehouse_id,warehouse_no) SELECT ',
			V_TradeID,',
			warehouse_id,warehouse_no FROM cfg_warehouse WHERE warehouse_id IN(',
			@tmp_warehouse_enough, ')'));
	END IF;
	*/
	-- 日志
	-- 下单，付款时间
	INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message,created) VALUES(V_TradeID,P_OperatorID,1,P_ApiTradeID,CONCAT('客户下单:',V_Tid), V_TradeTime);
	IF V_PayStatus THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message,created) VALUES(V_TradeID,P_OperatorID,2,P_ApiTradeID,CONCAT('客户付款:',V_Tid), V_PayTime);
	END IF;
	INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_TradeID,P_OperatorID,3,CONCAT('订单递交:', V_Tid, V_ExtMsg));
	
	-- 冻结日志
	IF V_IsFreezed THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message)
		SELECT V_TradeID,P_OperatorID,28,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
		FROM cfg_oper_reason 
		WHERE reason_id = V_IsFreezed;
	END IF;
	
	-- 执行策略日志
	SET V_LogisticsName='';
	IF V_AreaAliasLog<>'' THEN 
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('根据物流公司和收货地址，',V_AreaAliasLog));
	END IF;	
	IF V_SelectLogisticsByGoods<>'' THEN
			SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
			IF V_LogisticsName!='' THEN
			  INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT(V_SelectLogisticsByGoods,':',V_LogisticsName));
			END IF;
		END IF;
	IF V_LogisticsMatchLog<>'' THEN 
		SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
		IF V_LogisticsName!='' THEN
		  INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('按收件地址：',V_ReceiverArea,'，',V_LogisticsMatchLog,'：',V_LogisticsName));
		END IF;
	END IF;
	IF V_LogisticsFeeLog<>'' THEN 
		SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
		IF V_LogisticsName!='' THEN
		   INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('按照物流公司：',V_LogisticsName,'，收件地址：',V_ReceiverArea,'，计算邮资：',V_LogisticsFeeLog));
	    END IF;
	END IF; 
	IF V_WareHouseSelectLog<>'' THEN
		SELECT shop_name INTO V_ShopName FROM cfg_shop WHERE shop_id=V_ShopID;
		SELECT name INTO V_WarehouseName FROM cfg_warehouse WHERE warehouse_id=V_WarehouseID2;
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('店铺：',V_ShopName,'。',V_WarehouseSelectLog,'：',V_WarehouseName));
	END IF;
	IF V_RemarkLog<>'' THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('客服备注提取。',V_RemarkLog));
	END IF;
	IF V_ClientRemarkLog<>'' THEN
		INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message) VALUES(V_TradeID,P_OperatorID,169,P_ApiTradeID,CONCAT('客户备注提取。',V_ClientRemarkLog));
	END IF;
	
	-- 更新原始单
	UPDATE api_trade SET process_status=20,
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_salesman_id=V_SalesmanID,
		x_trade_flag=IF(V_FlagID=8,0,V_FlagID),
		x_is_freezed=V_IsFreezed,
		x_warehouse_id=IF(V_Locked,V_WarehouseID2,0),
		modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
	UPDATE api_trade_order SET modify_flag=0,process_status=20 WHERE shop_id=V_ShopID AND tid=V_Tid;
	
	-- 标记同名未合并 进入审核时
	/*IF @cfg_order_check_warn_has_unmerge AND V_TradeStatus=30 THEN
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
	END IF;*/
	-- 订单全链路
	IF V_ApiTradeStatus=30 AND V_TradeStatus<55 THEN
		CALL I_SALES_TRADE_TRACE(V_TradeID, 1, '');
	END IF;

	COMMIT;
	
END//
DELIMITER ;