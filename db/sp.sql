



DROP FUNCTION IF EXISTS `FN_EMPTY`;
DELIMITER //
CREATE FUNCTION `FN_EMPTY`(`P_Str` VARCHAR(1024)) RETURNS tinyint(4)
    NO SQL
    SQL SECURITY INVOKER
    DETERMINISTIC
BEGIN
	IF P_Str IS NULL OR P_Str = '' THEN
		RETURN 1;
	END IF;
	
	RETURN 0;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_GOODS_NO`;
DELIMITER //
CREATE FUNCTION `FN_GOODS_NO`(`P_Type` INT, `P_TargetID` INT) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
    COMMENT '查询货品或组合装信息'
BEGIN
	DECLARE V_GoodsNO VARCHAR(40);
	
	SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',
		@tmp_goods_id='',@tmp_spec_id='',@tmp_barcode='',@tmp_retail_price=0;-- ,@tmp_sn_enable=0
	
	IF P_Type=1 THEN
		SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name,gs.spec_code,gg.goods_id,gs.spec_id,gs.barcode,gs.retail_price -- gs.is_sn_enable,
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,V_GoodsNO,@tmp_spec_name,@tmp_spec_code,@tmp_goods_id,@tmp_spec_id,@tmp_barcode,@tmp_retail_price -- ,@tmp_sn_enable
		FROM goods_spec gs,goods_goods gg WHERE gs.spec_id=P_TargetID AND gs.goods_id=gg.goods_id;
		
	ELSEIF P_Type=2 THEN
		-- SELECT 1 INTO @tmp_sn_enable
		-- FROM goods_suite_detail gsd, goods_spec gs
		-- WHERE gsd.suite_id=P_TargetID AND gs.spec_id=gsd.spec_id AND gs.is_sn_enable>0 LIMIT 1;
		
		SELECT suite_no,suite_name,short_name,suite_id,'','',barcode,retail_price
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,@tmp_goods_id,@tmp_spec_id,@tmp_spec_name,@tmp_barcode,@tmp_retail_price 
		FROM goods_suite WHERE suite_id=P_TargetID;
		
		SET V_GoodsNO='';
	END IF;
	
	RETURN V_GoodsNO;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SEQ`;
DELIMITER //
CREATE FUNCTION `FN_SEQ`(`P_Name` VARCHAR(20)) RETURNS int(11)
	READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
 BEGIN
     SET @tmp_seq=1;
     INSERT INTO sys_sequence(`name`,`val`) VALUES(P_Name, 1) ON DUPLICATE KEY UPDATE val=(@tmp_seq:=(val+1));
     RETURN @tmp_seq;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SPEC_NO_CONV`;
DELIMITER $$
CREATE FUNCTION `FN_SPEC_NO_CONV`(`P_GoodsNO` VARCHAR(40), `P_SpecNO` VARCHAR(40)) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_I INT;
	
	IF LENGTH(@cfg_goods_match_split_char)>0 THEN
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_GoodsNO);
		IF V_I THEN
			SET P_GoodsNO=SUBSTRING(P_GoodsNO, 1, V_I-1);
		END IF;
		
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_SpecNO);
		IF V_I THEN
			SET P_SpecNO=SUBSTRING(P_SpecNO, 1, V_I-1);
		END IF;
		
	END IF;
	
	RETURN IF(@cfg_goods_match_concat_code,CONCAT(P_GoodsNO,P_SpecNO),IF(P_SpecNO<>'',P_SpecNO,P_GoodsNO));
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SYS_NO`;
DELIMITER //
CREATE FUNCTION `FN_SYS_NO`(`P_Key` VARCHAR(50)) RETURNS VARCHAR(60) CHARSET UTF8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_DateChanged BIT;
	DECLARE V_Prefix, V_PostfixStr, V_PostfixStr2 VARCHAR(64);
	DECLARE V_PostfixLen, V_PostfixVal,V_DateForm INT;
	DECLARE V_NowDate DATE;
	
	-- date_form 0 全日期 1短日期 2无日期
	
	SET V_NowDate = CURDATE();
	
	UPDATE sys_no_cfg SET postfix_val=LAST_INSERT_ID(IF(V_NowDate=last_date OR date_form=2,postfix_val+1,1)),last_date=V_NowDate
	WHERE `key`=P_Key;
	
	SET V_PostfixVal = LAST_INSERT_ID();
	
	SELECT prefix,postfix_len,date_form into V_Prefix,V_PostfixLen,V_DateForm from sys_no_cfg WHERE `key`=P_Key;
	
	SET V_PostfixStr = CAST(V_PostfixVal AS CHAR);
	SET V_PostfixStr2 = LPAD(V_PostfixStr, V_PostfixLen, '0');
	IF LENGTH(V_PostfixStr2) < LENGTH(V_PostfixStr) THEN
		SET V_PostfixStr2 = V_PostfixStr;
	END IF;
	
	IF V_DateForm=0 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%Y%m%d'), V_PostfixStr2);
	ELSEIF V_DateForm=1 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%y%m%d'), V_PostfixStr2);
	END IF;
	
	RETURN CONCAT(V_Prefix, V_PostfixStr2);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE_CHANGED`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE_CHANGED`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ModifyFlag,V_TradeCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ShopID,V_ApiTradeStatus,V_RefundStatus TINYINT DEFAULT(0);
	DECLARE V_ApiTradeID,V_RecID BIGINT DEFAULT(0);
	DECLARE V_Tid,V_Oid VARCHAR(40);
	
	DECLARE api_trade_cursor CURSOR FOR 
		SELECT rec_id FROM api_trade FORCE INDEX(IX_api_trade_modify_flag)
		WHERE modify_flag>0 AND bad_reason=0 LIMIT 100;
	
	DECLARE api_trade_order_cursor CURSOR FOR 
		SELECT modify_flag,rec_id,status,shop_id,tid,oid,refund_status
		FROM api_trade_order WHERE modify_flag>0
		LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 主订单变化
	OPEN api_trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_trade_cursor INTO V_ApiTradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_cursor;
				OPEN api_trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_MAIN_ORDER(P_OperatorID, V_ApiTradeID);
		
	END LOOP;
	CLOSE api_trade_cursor;
	
	
	SET V_TradeCount = 0;
	-- 子订单变化
	OPEN api_trade_order_cursor;
	TRADE_ORDER_LABEL: LOOP
		-- modify_flag,rec_id,status,refund_status
		FETCH api_trade_order_cursor INTO V_ModifyFlag,V_RecID,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_order_cursor;
				OPEN api_trade_order_cursor;
				ITERATE TRADE_ORDER_LABEL;
			END IF;
			LEAVE TRADE_ORDER_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_SUB_ORDER(P_OperatorID,V_RecID,V_ModifyFlag,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus);
	END LOOP;
	CLOSE api_trade_order_cursor;
END//
DELIMITER ;

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
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
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
	
	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '原始单状态不正确';
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
	CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
	IF V_IsPreorder THEN
		SET V_ExtMsg = ' 进预订单原因:客服备注提取';
	END IF;
	
	-- 客户备注
	SET V_BuyerMessage=TRIM(V_BuyerMessage);
	CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
	
	-- select warehouse_id randomly
	SELECT warehouse_id INTO V_WarehouseID2 FROM cfg_warehouse where is_disabled = 0 limit 1;
	
	-- get logistics_id from cfg_shop 
	IF V_DeliveryTerm=2 THEN
		SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	ELSE 
		SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
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
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
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
	SET V_AreaAlias = '';
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID2 AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;
	/*
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
	*/
	
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
				-- SET V_TradeStatus=20;
				SET V_TradeStatus=30;
				/*
				IF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
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
				END IF;
				*/
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
	/*
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
		salesman_id,is_sealed,freeze_reason,delay_to_time,commission,gift_mask,unmerge_mask,raw_goods_type_count,raw_goods_count,single_spec_no,created)
	VALUES(FN_SYS_NO('sales'),V_PlatformID,V_ShopID,V_Tid,V_TradeStatus,1,V_TradeFrom,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,V_FenxiaoNick,
		V_TradeTime,V_PayTime,V_PayAccount,V_CustomerType,V_CustomerID,V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,
		V_ToDeliverTime,V_DistCenter,V_DistSite,V_BuyerMessage,V_Remark,V_RemarkFlag,
		V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_TotalVolume,V_LogisticsID,V_AreaAlias,V_PostCost,V_GoodsCost,
		NOT FN_EMPTY(V_Remark),NOT FN_EMPTY(V_BuyerMessage),NOT FN_EMPTY(V_Remark),
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID2,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType2,V_StockoutNO,V_PackageID,
		V_SalesmanID,V_IsSealed,V_IsFreezed,V_DelayToTime,V_Commission,V_GiftMask,V_UnmergeMask,V_ApiOrderCount,V_ApiGoodsCount,V_SingleSpecNO,V_Now);
	
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
		ELSEIF V_TradeStatus=15 OR V_TradeStatus=16 OR V_TradeStatus=20 OR V_TradeStatus=30 THEN	-- 已付款
			CALL I_RESERVE_STOCK(V_TradeID, 3, V_WarehouseID2, 0);
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
		SELECT V_TradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
		FROM cfg_oper_reason 
		WHERE reason_id = V_IsFreezed;
	END IF;
	
	-- 更新原始单
	UPDATE api_trade SET process_status=20,
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_salesman_id=V_SalesmanID,
		x_trade_flag=V_FlagID,
		x_is_freezed=V_IsFreezed,
		x_warehouse_id=IF(V_Locked,V_WarehouseID2,0),
		modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
	UPDATE api_trade_order SET modify_flag=0,process_status=20 WHERE shop_id=V_ShopID AND tid=V_Tid;
	
	-- 标记同名未合并 进入审核时
	IF @cfg_order_check_warn_has_unmerge AND V_TradeStatus=30 THEN
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
	END IF;

	-- 订单全链路
	IF V_ApiTradeStatus=30 AND V_TradeStatus<55 THEN
		CALL I_SALES_TRADE_TRACE(V_TradeID, 1, '');
	END IF;
	
	COMMIT;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_CLIENT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_CLIENT_REMARK`(IN `P_Remark` VARCHAR(1024), 
	INOUT `P_TradeFlag` INT, 
	INOUT `P_WmsType` INT, 
	INOUT `P_WarehouseID` INT, 
	INOUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客户备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_Kw VARCHAR(255);
	DECLARE V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=2 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_c_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=2 THEN
			IF V_Target>0 AND P_TradeFlag=0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND P_WarehouseID=0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=6 THEN
			IF P_FreezeReason=0 THEN
				SET P_FreezeReason=GREATEST(1,V_Target);
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_REMARK`(IN `P_Remark` VARCHAR(1024), OUT `P_LogisticsID` INT, OUT `P_TradeFlag` INT, OUT `P_SalesmanID` INT, INOUT `P_WmsType` INT, OUT `P_WarehouseID` INT, OUT `P_IsPreorder` INT, OUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客服备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_SalesManName,V_Kw VARCHAR(255);
	DECLARE V_MacroBeginIndex, V_MacroEndIndex, V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=1 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_LogisticsID=0;
	SET P_TradeFlag=0;
	SET P_SalesmanID=0;
	SET P_WarehouseID = 0;
	SET P_IsPreorder=0;
	SET P_FreezeReason=0;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 根据括号提取
	IF @cfg_salesman_macro_begin<> '' AND @cfg_salesman_macro_end <> '' THEN
		SET V_MacroBeginIndex = LOCATE(@cfg_salesman_macro_begin, P_Remark, 1);
		IF V_MacroBeginIndex > 0 THEN
			SET V_MacroEndIndex = LOCATE(@cfg_salesman_macro_end, P_Remark, V_MacroBeginIndex+1);
			IF V_MacroEndIndex>0 THEN
				SET V_SalesManName = SUBSTRING(P_Remark, V_MacroBeginIndex+CHAR_LENGTH(@cfg_salesman_macro_begin), V_MacroEndIndex-V_MacroBeginIndex-CHAR_LENGTH(@cfg_salesman_macro_end));
				IF V_SalesManName IS NOT NULL AND V_SalesManName<>'' THEN 
					SELECT employee_id INTO P_SalesmanID FROM hr_employee WHERE shortname=V_SalesManName AND deleted=0 AND is_disabled=0;
				END IF;
			END IF;
		END IF;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=1 THEN
			IF V_Target>0 THEN
				SET P_LogisticsID=V_Target;
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=3 THEN
			IF V_Target>0 THEN
				SET P_SalesmanID=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=5 THEN
			SET P_IsPreorder=1;
		ELSEIF V_Type=6 THEN
			SET P_FreezeReason=GREATEST(1,V_Target);
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_INIT`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT`(IN `P_CreateApiGoods` INT)
    SQL SECURITY INVOKER
    COMMENT '递交处理初始化'
MAIN_LABEL:BEGIN
	DECLARE V_AutoMatchGoods INT DEFAULT(0);
	
	/*配置*/
	-- 是否开启自动递交
	CALL SP_UTILS_GET_CFG_INT('order_auto_submit',@cfg_order_auto_submit,1);

	-- 连接货品和规格商家编码
	CALL SP_UTILS_GET_CFG_INT('sys_goods_match_concat_code', @cfg_goods_match_concat_code, 0);

	-- 自动匹配平台货品的截取字符
	CALL SP_UTILS_GET_CFG_CHAR('goods_match_split_char', @cfg_goods_match_split_char, '');	
	
	-- 动态跟踪自动匹配货品
	-- CALL SP_UTILS_GET_CFG_INT('goods_match_dynamic_check', @cfg_goods_match_dynamic_check, 0);
	
	-- 是否自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge', @cfg_auto_merge, 1);
	
	-- 自动合并是否重新计算赠品
	-- CALL SP_UTILS_GET_CFG_INT('sales_trade_auto_merge_gift', @cfg_auto_merge_gift, 1);

	-- 订单审核时提示同名未合并
	CALL SP_UTILS_GET_CFG_INT('order_check_warn_has_unmerge', @cfg_order_check_warn_has_unmerge, 1);
	
	-- 延时审核分钟数
	CALL SP_UTILS_GET_CFG_INT('order_delay_check_min', @cfg_delay_check_sec, 0);	
	
	SET @cfg_delay_check_sec = @cfg_delay_check_sec*60;
	
	-- 已付等未付分钟数
	-- CALL SP_UTILS_GET_CFG_INT('order_wait_unpay_min', @cfg_wait_unpay_sec, 0);	
	
	SET @cfg_wait_unpay_sec = @cfg_wait_unpay_sec*60;
	
	-- 大件自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split', @cfg_order_auto_split, 1);
	
	-- 大件拆分最大次数
	-- CALL SP_UTILS_GET_CFG_INT('sales_split_large_goods_max_num', @cfg_sales_split_large_goods_max_num, 50);
	
	-- 按不同仓库自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split_by_warehouse',@cfg_order_auto_split_by_warehouse,0);
	
	-- 订单合并方式
	CALL SP_UTILS_GET_CFG_INT('order_auto_merge_mode', @cfg_order_merge_mode, 0);	
	-- 审核时提示条件
	CALL SP_UTILS_GET_CFG_INT('order_check_merge_warn_mode', @cfg_order_check_merge_warn_mode, 0);
	
	-- 业务员
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_begin', @cfg_salesman_macro_begin, '');	
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_end', @cfg_salesman_macro_end, '');	
	
	
	IF @cfg_salesman_macro_begin='' OR @cfg_salesman_macro_begin IS NULL OR @cfg_salesman_macro_end='' OR @cfg_salesman_macro_end IS NULL THEN
		SET @cfg_salesman_macro_begin='';
		SET @cfg_salesman_macro_end='';
	END IF;
	
	-- 物流选择方式：全局唯一，按店铺，按仓库
	-- CALL SP_UTILS_GET_CFG_INT('logistics_match_mode', @cfg_logistics_match_mode, 0);	

	-- 按货品先仓库
	-- CALL SP_UTILS_GET_CFG_INT('sales_trade_warehouse_bygoods', @cfg_sales_trade_warehouse_bygoods, 0);
	
	-- 如果仓库是按货品策略选出,修改时给出提醒
	-- CALL SP_UTILS_GET_CFG_INT('order_check_alert_locked_warehouse', @cfg_chg_locked_warehouse_alert, 0);

	-- 是否启用备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_remark_extract', @cfg_enable_remark_extract, 0);	
	-- 客户备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_c_remark_extract', @cfg_enable_c_remark_extract, 0);	
	-- 订单进入待审核后是否根据备注提取物流
	CALL SP_UTILS_GET_CFG_INT('order_deliver_enable_cs_remark_track', @cfg_order_deliver_enable_cs_remark_track, 1);	
	
	-- 自动按商家编码匹配货品
	CALL SP_UTILS_GET_CFG_INT('apigoods_auto_match', V_AutoMatchGoods, 1);	
	
	-- 转预订单设置
	/* CALL SP_UTILS_GET_CFG_INT('order_go_preorder', @cfg_order_go_preorder, 0);
	IF @cfg_order_go_preorder THEN
		CALL SP_UTILS_GET_CFG_INT('order_preorder_lack_stock', @cfg_order_preorder_lack_stock, 0);
		CALL SP_UTILS_GET_CFG_INT('preorder_split_to_order_condition',@cfg_preorder_split_to_order_condition,0);
	END IF;
	*/
	CALL SP_UTILS_GET_CFG_INT('remark_change_block_stockout', @cfg_remark_change_block_stockout, 1);
	-- 物流同步后,发生退款不拦截
	CALL SP_UTILS_GET_CFG_INT('unblock_stockout_after_logistcs_sync', @cfg_unblock_stockout_after_logistcs_sync, 0);
	
	-- 销售凭证自动过账
	-- CALL SP_UTILS_GET_CFG_INT('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	
	-- 米氏抢单全局开关
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_hold', @cfg_order_deliver_hold, 0);
	
	--  根据重量计算物流
	CALL SP_UTILS_GET_CFG_INT('calc_logistics_by_weight',@cfg_calc_logistics_by_weight,0);
	
	--  包装策略
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy', @cfg_open_package_strategy,0); 
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy_type',@cfg_open_package_strategy_type,1); -- 1,根据重量   2,根据体积
	
	-- 是否开启订单全链路
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	-- 订单中原始货品数量是否包含赠品
	CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	
	-- 强制凭证不需要审核
	-- SET @cfg_fa_voucher_must_check=0;
	
	-- 是否需要从原始单货品生成api_goods_spec
	IF NOT P_CreateApiGoods THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*导入平台货品*/
	START TRANSACTION;
	
	SELECT 1 INTO @tmp_dummy FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE;
	
	UPDATE api_goods_spec ag,api_trade_order ato,api_trade at
	SET ag.modify_flag=
		IF(ag.outer_id=ato.goods_no AND ag.spec_outer_id=ato.spec_no, ag.modify_flag, ag.modify_flag|1),
		ag.outer_id=ato.goods_no,ag.spec_outer_id=ato.spec_no,
		ag.goods_name=ato.goods_name,ag.spec_name=ato.spec_name,
		ag.cid=IF(ato.cid='',ag.cid,ato.cid),at.is_new=0
	WHERE at.process_status=10 AND at.is_new=1 AND ato.tid=at.tid AND ato.shop_id=at.shop_id AND ato.goods_id<>''
		AND ag.shop_id=ato.shop_id AND ag.goods_id=ato.goods_id AND ag.spec_id=ato.spec_id;
	
	-- 要测试平台更新编码的同步
	INSERT INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,cid,modify_flag,created)
	(
		SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,at.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,ato.cid,1,NOW()
		FROM api_trade_order ato INNER JOIN api_trade at ON ato.tid=at.tid AND ato.shop_id=at.shop_id
		WHERE at.process_status=10 AND at.is_new=1 AND ato.goods_id<>''
	)
	ON DUPLICATE KEY UPDATE modify_flag=
		IF(api_goods_spec.outer_id=VALUES(outer_id) AND api_goods_spec.spec_outer_id=VALUES(spec_outer_id), api_goods_spec.modify_flag, api_goods_spec.modify_flag|1),
		outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),
		goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),
		cid=IF(VALUES(cid)='',api_goods_spec.cid,VALUES(cid));
	
	UPDATE api_trade SET is_new=0 WHERE process_status=10 and is_new=1;
	COMMIT;
	
	IF V_AutoMatchGoods THEN
		-- 对新增和变化的平台货品进行自动匹配
		UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;
		
		-- 刷新品牌分类
		UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
		SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;
		
		UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
		SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;
		
		-- 刷新无效货品
		UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax
		SET ato.is_invalid_goods=0,ax.bad_reason=0
		WHERE ato.is_invalid_goods=1 AND ag.`shop_id`=ato.`shop_id` AND ag.`goods_id`=ato.`goods_id` AND
			ag.`spec_id`=ato.`spec_id` AND ax.shop_id=ato.`shop_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND
			ag.match_target_type>0; 
		
		-- 自动刷新库存同步规则
		-- 应该判断一下规则是否变化了，如果变化了，要触发同步开关????????????
		UPDATE api_goods_spec gs,
		(SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id 
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1)
		WHERE gs.rec_id=da.rec_id;
		UPDATE api_goods_spec SET modify_flag=(modify_flag&~1) WHERE modify_flag>0 AND (modify_flag&1);
	END IF;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MAP_TRADE_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_MAP_TRADE_GOODS`(IN `P_TradeID` INT, IN `P_ApiTradeID` BIGINT, IN `P_UseTran` INT, OUT `P_ApiOrderCount` INT, OUT `P_ApiGoodsCount` INT)
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中'
MAIN_LABEL: BEGIN 
	DECLARE V_MatchTargetID,V_GoodsID,V_SGoodsID,V_SpecID,V_SuiteSpecCount,V_I,V_GiftType,V_MasterID,V_ShopID,
		V_Cid,V_IsDeleted,V_NOT_FOUND INT DEFAULT(0);
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
	
	-- 订单信息
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,oid,ato.status,refund_status,bind_oid,invoice_type,invoice_content,num,ato.price,adjust_amount,
			discount,share_discount,share_amount,share_post,paid,match_target_type,match_target_id,spec_no,ato.gift_type,
			ato.goods_name,ato.spec_name,aps.cid,aps.is_manual_match,ato.goods_no,ato.spec_no,ato.remark
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON aps.shop_id=V_ShopID AND aps.goods_id=ato.goods_id and aps.spec_id=ato.spec_id
		WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND ato.process_status=10;
	
	-- 组合装货品
	DECLARE goods_suite_cursor CURSOR FOR 
		SELECT gsd.spec_id,gsd.num,gsd.is_fixed_price,gsd.fixed_price,gsd.ratio,gg.goods_name,gs.goods_id,gg.goods_no,
			gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,(gs.length*gs.width*gs.height) as volume ,gs.tax_rate,gs.large_type,(gs.retail_price*gsd.num),gs.is_allow_zero_cost,gs.deleted
		FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gsd.suite_id=V_MatchTargetID AND gsd.num>0
		ORDER BY gsd.is_fixed_price DESC;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	
	DELETE FROM tmp_sales_trade_order;
	
	SELECT platform_id,shop_id,tid,delivery_term,guarantee_mode,trade_mask
	INTO V_PlatformID,V_ShopID,V_Tid,V_DeliveryTerm,V_GuaranteeMode,V_TradeMask
	FROM api_trade WHERE rec_id=P_ApiTradeID;
	
	-- 展开货品
	SET P_ApiOrderCount = 0;
	SET P_ApiGoodsCount = 0;
	SET V_MasterAmount = -1;
	SET V_Now = NOW();
	SET @tmp_refund_occur = 0;
	SET @sys_code=0, @sys_message='OK';
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH trade_order_cursor INTO 
			V_RecID,V_Oid,V_OrderStatus,V_RefundStatus,V_BindOid,V_InvoiceType,V_InvoiceContent,V_Num,V_Price,V_AdjustAmount,
			V_Discount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,V_MatchTargetType,V_MatchTargetID,V_ApiSpecNO,V_GiftType,
			V_ApiGoodsName,V_ApiSpecName,V_CidNO,V_IsManualMatch,V_OuterId,V_SpecOuterId,V_Remark;
			
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_Num <= 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
			 	ROLLBACK;
			 	UPDATE api_trade SET bad_reason=(bad_reason|1) WHERE rec_id=P_ApiTradeID;
			 END IF;
			 SET @sys_code=4, @sys_message = '货品数量为零';
			 LEAVE MAIN_LABEL;
		END IF;
		
		SET P_ApiOrderCount = P_ApiOrderCount + 1;
		SET P_ApiGoodsCount = P_ApiGoodsCount + V_Num;
		
		-- 类目及佣金暂时不做
		-- SET V_CommissionFactor = 0, V_Cid = 0;
		-- 未绑定
		IF V_PlatformID=0 THEN -- 线下订单不需判断无效货品 
			SELECT `type`, target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_ApiSpecNO;
		ELSE
			/*
			IF V_CidNO <> '' THEN
				SELECT rec_id,commission_factor INTO V_Cid,V_CommissionFactor FROM api_goods_category WHERE cid=V_CidNO AND shop_id=V_ShopID;
				SET V_NOT_FOUND=0;
			END IF;
			*/
			-- 判断是否开启动态匹配
			IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
				SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
				SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			END IF;
		END IF;
		
		
		IF V_NOT_FOUND OR V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
				 ROLLBACK;
				 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
			 END IF;
			 SET @sys_code=3, @sys_message = CONCAT('订单包含无效货品:',V_Tid);
			 LEAVE MAIN_LABEL;
		END IF;
		
		-- 子订单关闭,当退款处理
		IF V_OrderStatus=80 OR V_OrderStatus=90 THEN
			 SET V_RefundStatus=5;
		END IF;
		
		IF V_RefundStatus>1 THEN -- 需要创建退款单
			 SET @tmp_refund_occur = V_RefundStatus;
		END IF;
		
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,gs.tax_rate,gs.large_type,gs.is_allow_zero_cost,gs.length*gs.width*gs.height
				INTO V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,V_Weight,V_TaxRate,V_LargeType,V_IsZeroCost,V_Volume
			FROM goods_spec gs LEFT JOIN goods_goods gg USING(goods_id)
			WHERE gs.spec_id=V_MatchTargetID AND gs.deleted=0;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					 ROLLBACK;
					 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=4, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效单品');
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果钱已经付了，则为款到发货
			IF V_Paid >= V_ShareAmount+V_SharePost THEN
				 SET V_OrderDeliveryTerm = 1;
			ELSE
				 SET V_OrderDeliveryTerm = V_DeliveryTerm;
			END IF;
			
			SET V_SharePrice=TRUNCATE(V_ShareAmount/V_Num,4);
			
			-- 退款状态处理??
			INSERT INTO tmp_sales_trade_order(
				spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,paid,
				order_price,share_amount,share_post,share_price,adjust,discount,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
				api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,from_mask,gift_type,
				cid,is_allow_zero_cost,remark)
			VALUES(V_MatchTargetID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_Num,V_Price,
				IF(V_RefundStatus>2,0,V_Num),V_Paid,V_SharePrice,V_ShareAmount,V_SharePost,V_SharePrice,V_AdjustAmount,
				(V_Discount-V_AdjustAmount+V_ShareDiscount),
				V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,
				V_ApiGoodsName,V_ApiSpecName,V_Weight*V_Num,V_Volume*V_Num,TRUNCATE(V_ShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,
				V_InvoiceType,V_InvoiceContent,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
			/*
			-- 找一个未退款的，金额最大的子订单作主订单,不考虑主订单
			IF V_RefundStatus<=2 AND V_ShareAmount > V_MasterAmount THEN
				 SET V_MasterAmount=V_ShareAmount;
				 SET V_MasterID = LAST_INSERT_ID();
			END IF;
			*/
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			-- 取组合装信息
			SELECT suite_no,suite_name,is_unsplit,is_print_suite INTO V_SuiteNO,V_SuiteName,V_IsUnsplit,V_IsPrintSuite
			FROM goods_suite WHERE suite_id=V_MatchTargetID;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=5, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效组合装');
				LEAVE MAIN_LABEL;
			END IF;
			
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
			
			-- 无货品
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装为空');
				LEAVE MAIN_LABEL;
			END IF;
			
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
					CLOSE trade_order_cursor;
					IF P_UseTran THEN
						ROLLBACK;
						CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
					END IF;
					SET @sys_code=7, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装包含已删除单品 ', V_SSpecNO);
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
				
				INSERT INTO tmp_sales_trade_order(
					spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,
					order_price,share_price,share_amount,share_post,discount,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
					api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,suite_id,suite_no,suite_name,suite_num,suite_amount,
					suite_discount,is_print_suite,from_mask,gift_type,cid,is_allow_zero_cost,remark)
				VALUES(V_SpecID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_SNum,V_SPrice,IF(V_RefundStatus>2,0,V_SNum),
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
			
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = '组合装无货品';
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	-- 标记主子订单
	-- 注：拆分合并时处理
	-- UPDATE tmp_sales_trade_order SET is_master=1 WHERE rec_id=V_MasterID;
	
	IF P_TradeID THEN
		INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,created)
		SELECT P_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,NOW()
		FROM tmp_sales_trade_order;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MARK_INVALID_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_MARK_INVALID_TRADE`(IN `P_TradeID` INT, IN `P_ShopId` TINYINT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '标记原始单的子订单有无效货品'
MAIN_LABEL:BEGIN
	DECLARE V_RecID,V_MatchTargetType,V_MatchTargetID,V_InvalidGoods,V_GoodsCount,V_IsManualMatch,V_Deleted,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,match_target_type,match_target_id,is_manual_match,ato.goods_no,ato.spec_no
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON ato.shop_id=aps.shop_id AND ato.goods_id=aps.goods_id and ato.spec_id=aps.spec_id
		WHERE ato.shop_id=P_ShopId AND ato.tid=P_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		
		FETCH trade_order_cursor INTO V_RecID,V_MatchTargetType,V_MatchTargetID,V_IsManualMatch,V_OuterId,V_SpecOuterId;
		IF V_NOT_FOUND THEN
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		-- 未绑定
		IF V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			
			-- 添加到平台货品
			INSERT IGNORE INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,modify_flag,created)
			SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,ax.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,1,NOW()
			FROM api_trade_order ato LEFT JOIN api_trade ax ON ax.tid=ato.tid AND ax.platform_id=ato.platform_id
			WHERE ato.rec_id=V_RecID;
			
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
			SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
			SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			IF V_NOT_FOUND THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
				ITERATE TRADE_GOODS_LABEL;
			END IF;
		END IF;
		
		SET V_Exists=0,V_Deleted = 0;
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_spec WHERE spec_id=V_MatchTargetID;
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_suite WHERE suite_id=V_MatchTargetID;
		END IF;
		
		
		IF NOT V_Exists OR V_Deleted THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_MatchTargetType = 2 THEN
			SELECT COUNT(rec_id) INTO V_GoodsCount FROM goods_suite_detail WHERE suite_id=V_MatchTargetID;
			IF V_GoodsCount=0 THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
			
			-- 判断组合装里货品是否都有效
			IF EXISTS(SELECT 1 FROM goods_suite_detail gsd,goods_spec gs 
				WHERE gsd.suite_id=V_MatchTargetID AND gs.spec_id=gsd.spec_id AND gs.deleted>0) THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	IF V_InvalidGoods THEN
		UPDATE api_trade SET bad_reason=1 WHERE rec_id=P_TradeID;
	END IF;
	COMMIT;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PUSH_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_PUSH_REFUND`(IN `P_OperatorID` INT, IN `P_ShopID` INT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '递交过程中自动生成退款单'
BEGIN
	DECLARE V_RefundStatus,V_GoodsID,V_SpecId,V_RefundID,V_RefundID2,V_Status,V_ApiStatus,V_Type,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Num DECIMAL(19,4);
	DECLARE V_Oid,V_RefundNO,V_RefundNO2 VARCHAR(40);
	
	DECLARE refund_order_cursor CURSOR FOR 
		SELECT refund_id,refund_status,status,oid
		FROM api_trade_order
		WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 删除临时退款单
	DELETE stro FROM sales_tmp_refund_order stro, api_trade_order sto 
	WHERE stro.shop_id=P_ShopID AND stro.oid=sto.oid AND sto.shop_id=P_ShopID AND sto.tid=P_Tid;
	
	OPEN refund_order_cursor;
	REFUND_ORDER_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH refund_order_cursor INTO V_RefundNO,V_RefundStatus,V_ApiStatus,V_Oid;
		IF V_NOT_FOUND THEN
			LEAVE REFUND_ORDER_LABEL;
		END IF;
		
		IF V_RefundStatus < 2 THEN -- 取消退款
			-- 如果订单已发货，说明是个售后退货,不需要再更新退款单
			IF V_ApiStatus>=40 THEN
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			DELETE FROM sales_tmp_refund_order WHERE shop_id=P_ShopID AND oid=V_Oid;
			
			-- 更新退款单状态
			-- 一个原始单只能出现在一个退款单中
			SET V_RefundID=0;
			SELECT sro.refund_id INTO V_RefundID FROM sales_refund_order sro,sales_refund sr
			WHERE sro.shop_id=P_ShopID AND sro.oid=V_Oid AND sro.refund_id=sr.refund_id AND sr.type=1 LIMIT 1;
			
			IF V_RefundID THEN
				UPDATE sales_refund_order SET process_status=10 WHERE refund_id=V_RefundID AND shop_id=P_ShopID AND oid=V_Oid;
				SET V_Status=0;
				SELECT 1 INTO V_Status FROM sales_refund_order WHERE refund_id=V_RefundID AND process_status<>10 LIMIT 1;
				IF V_Status=0 THEN  -- 全部子订单都取消
					UPDATE sales_refund SET process_status=10,status=V_RefundStatus WHERE refund_id=V_RefundID;
					-- 日志
					INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,4,P_OperatorID,'平台取消退款');
				END IF;
			END IF;
			-- 原始退款单状态?
			
			ITERATE REFUND_ORDER_LABEL;
		END IF;
		
		-- 目前只有淘宝存在退款单号
		-- 没有退款单号的，自动生成一个
		IF V_RefundNO='' THEN
			
			SET V_Type=IF(V_ApiStatus<40,1,2);
			SET V_NOT_FOUND=0;
			SELECT ar.refund_id,ar.refund_no INTO V_RefundID,V_RefundNO FROM api_refund ar,api_refund_order aro
			WHERE ar.shop_id=P_ShopID AND ar.tid=P_Tid AND ar.`type`=V_Type 
				AND aro.shop_id=P_ShopID AND aro.refund_no=ar.refund_no AND aro.oid=V_Oid LIMIT 1;
			
			IF V_NOT_FOUND THEN
				-- 一个货品一个退款单
				SET V_RefundNO=FN_SYS_NO('apirefund');
				
				-- 创建原始退款单
				INSERT INTO api_refund(platform_id,refund_no,shop_id,tid,title,type,status,process_status,pay_account,refund_amount,actual_refund_amount,buyer_nick,refund_time,created)
				(SELECT ax.platform_id,V_RefundNO,ax.shop_id,P_Tid,ato.goods_name,V_Type,ato.refund_status,0,ax.pay_account,ato.refund_amount,ato.refund_amount,ax.buyer_nick,NOW(),NOW()
				FROM api_trade_order ato, api_trade ax
				WHERE ato.shop_id=P_ShopID AND ato.oid=V_Oid AND ax.shop_id=P_ShopID AND ax.tid=P_Tid);
				
				INSERT INTO api_refund_order(platform_id,refund_no,shop_id,oid,status,goods_name,spec_name,num,price,total_amount,goods_id,spec_id,goods_no,spec_no,created)
				(SELECT platform_id,V_RefundNO,shop_id,oid,refund_status,goods_name,spec_name,num,price,share_amount,goods_id,spec_id,goods_no,spec_no,NOW()
				FROM api_trade_order WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0 AND refund_id='');
			ELSE
				UPDATE api_refund SET status=V_RefundStatus,modify_flag=(modify_flag|1) WHERE refund_id=V_RefundID;
				UPDATE api_refund_order SET status=V_RefundStatus WHERE shop_id=P_ShopID AND refund_no=V_RefundNO AND oid=V_Oid;
			END IF;
			
		ELSE
			IF V_ApiStatus>=40 THEN -- 已发货,销后退款,让退款同步脚本处理
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			-- 平台支持退款单的
			-- 查找退款单是否已经存在，如果已存在，就不需要创建临时退款单，直接更新退款单状态
			SET V_RefundID=0;
			SELECT refund_id INTO V_RefundID FROM sales_refund WHERE src_no=V_RefundNO AND shop_id=P_ShopID AND type=1 LIMIT 1;
			IF V_RefundID THEN
				SET V_Status=80;
				IF V_RefundStatus=2 THEN
					SET V_Status=20;
				ELSEIF V_RefundStatus=3 THEN
					SET V_Status=60;
				ELSEIF V_RefundStatus=4 THEN
					SET V_Status=60;
				END IF;

				UPDATE sales_refund SET process_status=V_Status,status=V_RefundStatus WHERE refund_id=V_RefundID;
				-- 日志
				INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,2,P_OperatorID,'平台同意退款');
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
		END IF;
		
		IF V_RefundStatus>2 THEN
			-- 创建临时退款单
			INSERT IGNORE INTO sales_tmp_refund_order(shop_id, oid) VALUES(P_ShopID, V_Oid);
		END IF;
	END LOOP;
	CLOSE refund_order_cursor;
		
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_REFRESH_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_REFRESH_TRADE`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_RefreshFlag` INT, IN `P_ToStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_WarehouseID,V_WarehouseType, V_ShopID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,
		V_LogisticsID,V_DeliveryTerm,V_Max,V_Min,V_NewRefundStatus,V_NewLogisticsID,V_Locked,V_GoodsTypeCount,
		V_NoteCount,V_GiftMask,V_PackageID,V_SalesmanId,V_PlatformId,V_RemarkFlag,V_FlagId,V_BuyerMessageCount,
		V_CsRemarkCount,V_InvoiceType,V_TradeStatus,V_RawGoodsTypeCount, V_RawGoodsCount INT DEFAULT(0);
	DECLARE V_Addr,V_SrcTids,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(255);
	DECLARE V_BuyerMessage,V_CsRemark VARCHAR(1024);
	DECLARE V_AreaAlias,V_SingleSpecNO VARCHAR(40);
	DECLARE V_GoodsCount,V_Weight,V_PostCost,V_Paid,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GoodsCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4);
	
	-- P_RefreshFlag
	-- 1选择物流 2计算大头笔 4选择包装 8刷新备注
	
	-- 统计子订单
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0)),SUM(actual_num),COUNT(DISTINCT IF(actual_num<=0,NULL,sto.spec_id)),
		SUM(IF(actual_num>0,sto.weight,0)),SUM(IF(actual_num>0,paid,0)),MAX(IF(actual_num>0,delivery_term,1)),
		SUM(IF(actual_num>0,share_amount+discount,0)),SUM(IF(actual_num>0,share_post,0)),SUM(IF(actual_num>0,discount,0)),
		SUM(IF(actual_num>0,IF(delivery_term=1,share_amount+share_post,paid),0)),
		SUM(IF(actual_num>0,IF(delivery_term=2,share_amount+share_post-paid,0),0)),
		BIT_OR(IF(actual_num>0,gift_type,0)),SUM(IF(actual_num>0,commission,0)),SUM(actual_num*gs.length*gs.width*gs.height)
	INTO V_Max,V_Min,V_GoodsCount,V_GoodsTypeCount,V_Weight,V_Paid,V_DeliveryTerm,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
	FROM sales_trade_order sto LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id  WHERE sto.trade_id=P_TradeID;	
	
	-- 退款状态
	IF V_GoodsCount<=0 THEN
		SET V_NewRefundStatus=IF(V_Max,3,4);
		SET P_ToStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_NewRefundStatus=1;
	ELSEIF V_Max THEN
		SET V_NewRefundStatus=2;
	ELSE
		SET V_NewRefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	IF @cfg_sales_raw_count_exclude_gift IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	END IF;
	
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_RawGoodsTypeCount, V_RawGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM sales_trade_order
	WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_RawGoodsCount IS NULL THEN
		 SET V_RawGoodsCount=0;
	END IF;

	IF V_RawGoodsTypeCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM sales_trade_order 
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- V_WmsType, V_WarehouseNO, V_ShopID, V_TradeID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict;
	SELECT trade_status,warehouse_type, warehouse_id,shop_id,logistics_id,post_cost,receiver_province,receiver_city,receiver_district,receiver_address,receiver_dtb,package_id
	INTO V_TradeStatus,V_WarehouseType, V_WarehouseID,V_ShopID,V_LogisticsID,V_PostCost,V_ReceiverProvince,V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_AreaAlias,V_PackageID
	FROM sales_trade
	WHERE trade_id = P_TradeID;
	
	/*
	-- 订单未审核
	IF V_TradeStatus<35 THEN
		-- 包装
		IF P_RefreshFlag & 4  THEN 
			CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_Weight,V_TotalVolume);

			IF V_PackageID THEN
				SELECT weight INTO V_PackageWeight  FROM goods_spec WHERE spec_id = V_PackageID;
				SET V_Weight=V_Weight + V_PackageWeight;

			END IF;
		END IF;

		-- 选择物流
		IF P_RefreshFlag & 1 THEN
			CALL I_DL_DECIDE_LOGISTICS(V_NewLogisticsID, -1, V_DeliveryTerm, V_ShopID, V_WarehouseID,V_Weight, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_Addr);
			IF V_LogisticsID<>V_NewLogisticsID AND V_NewLogisticsID>0 THEN
				SET V_LogisticsID=V_NewLogisticsID;
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		END IF;
		
		-- 估算邮费
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_Weight, V_LogisticsID, V_ShopID, V_WarehouseID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		
		
	END IF;
	*/
	-- 估算货品成本
	SELECT TRUNCATE(IFNULL(SUM(sto.actual_num*IFNULL(ss.cost_price,0)),0),4) INTO V_GoodsCost FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID AND ss.spec_id=sto.spec_id
	WHERE sto.trade_id=P_TradeID AND sto.actual_num>0;
	-- SET V_AreaAlias = '';
	-- 便签数量
	-- SELECT COUNT(1) INTO V_NoteCount FROM common_order_note WHERE type=1 AND order_id=P_TradeID;
	
	SET @old_sql_mode=@@SESSION.sql_mode;
	SET SESSION sql_mode='';
	SELECT IFNULL(LEFT(GROUP_CONCAT(IF(ax.platform_id OR  ax.trade_from=3 OR ax.trade_from=5,ax.tid,NULL)),255),''),MAX(ax.x_salesman_id),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.buyer_message)='',NULL,TRIM(ax.buyer_message))),1024),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.remark)='',NULL,TRIM(ax.remark))),1024),''),
		MAX(ax.platform_id),
		MAX(ax.remark_flag),
		MAX(ax.x_trade_flag),
		SUM(IF(TRIM(ax.buyer_message)='',0,1)),
		SUM(IF(TRIM(ax.remark)='',0,1)),
		MAX(ax.invoice_type),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_title)='',NULL,TRIM(ax.invoice_title))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_content)='',NULL,TRIM(ax.invoice_content))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.pay_account)='',NULL,TRIM(ax.pay_account))),128),'')
	INTO
		V_SrcTids, V_SalesmanId, V_BuyerMessage, V_CsRemark, V_PlatformId, V_RemarkFlag, V_FlagId,
		V_BuyerMessageCount, V_CsRemarkCount, V_InvoiceType, V_InvoiceTitle, V_InvoiceContent,V_PayAccount
	FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=P_TradeID) sto
		LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
	
	SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
	
	IF V_PlatformId IS NULL THEN
		UPDATE sales_trade
		SET buyer_message_count=NOT FN_EMPTY(buyer_message),
			cs_remark_change_count=NOT FN_EMPTY(cs_remark),
			cs_remark_count=NOT FN_EMPTY(cs_remark),
			refund_status=V_NewRefundStatus,
			goods_count=V_GoodsCount,
			goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,
			post_amount=V_PostAmount,
			discount=V_Discount,
			receivable=V_GoodsAmount+V_PostAmount-V_Discount,
			dap_amount=V_DapAmount,
			cod_amount=(V_CodAmount+ext_cod_fee),
			warehouse_id=V_WarehouseID,
			trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
			logistics_id=V_LogisticsID,
			post_cost=V_PostCost,
			goods_cost=V_GoodsCost,
			receiver_dtb=V_AreaAlias,
			weight=V_Weight,
			volume=V_TotalVolume,
			delivery_term=V_DeliveryTerm,
			package_id = V_PackageID,
			paid=V_Paid,
			commission=V_Commission,
			profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
			note_count=V_NoteCount,
			gift_mask=V_GiftMask,
			version_id=version_id+1
		WHERE trade_id=P_TradeID;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新订单
	UPDATE sales_trade
	SET platform_id=V_PlatformId,
		src_tids=V_SrcTids,
		buyer_message=V_BuyerMessage,
		cs_remark=IF(NOT (cs_remark_change_count&2) OR (P_RefreshFlag&8),V_CsRemark,cs_remark),
		buyer_message_count=V_BuyerMessageCount,
		cs_remark_count=GREATEST(V_CsRemarkCount,NOT FN_EMPTY(cs_remark)),
		remark_flag=V_CsRemarkCount,
		flag_id=IF(flag_id=0,V_FlagId,flag_id),
		invoice_type=IF(invoice_type=0,V_InvoiceType,invoice_type),
		invoice_title=IF(invoice_title='',V_InvoiceTitle,invoice_title),
		invoice_content=IF(invoice_content='',V_InvoiceContent,invoice_content),
		salesman_id=IF(salesman_id,salesman_id,V_SalesmanId),
		refund_status=V_NewRefundStatus,
		goods_count=V_GoodsCount,
		goods_type_count=V_GoodsTypeCount,
		goods_amount=V_GoodsAmount,
		post_amount=V_PostAmount,
		discount=V_Discount,
		receivable=V_GoodsAmount+V_PostAmount-V_Discount,
		dap_amount=V_DapAmount,
		cod_amount=(V_CodAmount+ext_cod_fee),
		warehouse_id=V_WarehouseID,
		trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
		logistics_id=V_LogisticsID,
		post_cost=V_PostCost,
		goods_cost=V_GoodsCost,
		receiver_dtb=V_AreaAlias,
		weight=V_Weight,
		volume=V_TotalVolume,
		delivery_term=V_DeliveryTerm,
		package_id = V_PackageID,
		paid=V_Paid,
		commission=V_Commission,
		profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
		note_count=V_NoteCount,
		gift_mask=V_GiftMask,
		pay_account = V_PayAccount,
		raw_goods_type_count=V_RawGoodsTypeCount,
		raw_goods_count=V_RawGoodsCount,
		single_spec_no=V_SingleSpecNO,
		version_id=version_id+1
	WHERE trade_id=P_TradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SYNC_MAIN_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_MAIN_ORDER`(IN `P_OperatorID` INT, IN `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ModifyFlag,V_DeliverTradeID,V_WarehouseID,
		V_NewWarehouseID,V_Locked,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsType,
		V_SalesOrderCount,V_Timestamp,V_DelayToTime,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,
		V_CustomerID,V_FlagID,V_IsMaster,V_RemarkFlag,V_Exists,
		V_ShopHoldEnabled,V_OldFreeze,V_PackageID,V_RemarkCount,V_GiftMask,V_UnmergeMask,
		V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_ApiGoodsCount,V_ApiOrderCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,
		V_DapAmount,V_CodAmount,V_PiAmount,
		V_Paid,V_SalesGoodsCount,V_TotalWeight,V_PostCost,
		V_GoodsCost,V_ExtCodFee,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_HasSendGoods,V_HasGift,V_PlatformID,V_ApiTradeStatus,V_TradeStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_InvoiceType,V_WmsType,V_NewWmsType,V_IsAutoWms,V_IsSealed,V_IsFreezed,V_IsPreorder,V_IsExternal TINYINT DEFAULT(0);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverRing VARCHAR(40);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	
	DECLARE V_SalesmanID,V_LogisticsID,V_TradeMask,V_OldLogisticsID INT;
	DECLARE V_Tid,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_ExtMsg,V_SingleSpecNO VARCHAR(40);
	DECLARE V_AreaAlias,V_BuyerEmail,V_BuyerNick,V_ReceiverName,V_ReceiverArea VARCHAR(60);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime DATETIME;
	DECLARE V_Remark,V_BuyerMessage VARCHAR(1024);
	
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
	START TRANSACTION;
	
	SELECT modify_flag,platform_id,tid,trade_status,refund_status,delivery_term,guarantee_mode,deliver_trade_id,pay_time,pay_account,
		receivable,goods_amount,post_amount,other_amount,dap_amount,cod_amount,pi_amount,ext_cod_fee,paid,discount,invoice_type,
		invoice_title,invoice_content,stockout_no,trade_mask,is_sealed,wms_type,is_auto_wms,warehouse_no,shop_id,logistics_type,
		buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_area,receiver_ring,receiver_address,
		receiver_zip,receiver_telno,receiver_mobile,remark_flag,remark,buyer_message,is_external
	INTO V_ModifyFlag,V_PlatformID,V_Tid,V_ApiTradeStatus,V_RefundStatus,V_DeliveryTerm,V_GuaranteeMode,V_DeliverTradeID,V_PayTime,V_PayAccount,
		V_Receivable,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_Discount,V_InvoiceType,
		V_InvoiceTitle,V_InvoiceContent,V_StockoutNO,V_TradeMask,V_IsSealed,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_ShopID,V_LogisticsType,
		V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverArea,V_ReceiverRing,V_ReceiverAddress,
		V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_RemarkFlag,V_Remark,V_BuyerMessage,V_IsExternal
	FROM api_trade WHERE rec_id=P_ApiTradeID FOR UPDATE;
	
	-- 订单还没递交，不需要处理变化
	IF V_DeliverTradeID=0 OR V_IsExternal THEN
		UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
		COMMIT;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- trade_status变化
	-- 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	IF V_ModifyFlag & 1 THEN
		-- 锁定订单
		SELECT trade_status,warehouse_id,logistics_id,customer_id,freeze_reason
		INTO V_TradeStatus,V_WarehouseID,V_LogisticsID,V_CustomerID,V_OldFreeze
		FROM sales_trade WHERE trade_id=V_DeliverTradeID FOR UPDATE;
		
		-- 递交订单状态
		-- 30待发货
		--  ??????更新父订单货品数量金额等
		IF V_ApiTradeStatus = 30 THEN
			-- 5已取消 10待付款 12待尾款 15等未付 20前处理(赠品，合并，拆分) 25预订单 30待客审 35待财审 40待递交仓库 45递交仓库中 50已递交仓库 55待拣货 60待验货 65待打包 70待称重 75待出库 80待发货 85发货中 90发往配送中心 95已发货 100已签收 105部分结算 110已完成
			IF V_TradeStatus = 10 OR V_TradeStatus=12 THEN
				-- 未付款--已付款
				
				-- 备注变化
				IF (V_ModifyFlag & 8) THEN
					SET V_Remark=TRIM(V_Remark);
					-- 记录备注
					INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
				
				IF V_IsPreorder THEN
					SET V_ExtMsg = ' 进预订单原因:客服备注提取';	
				END IF;
				
				-- 客户备注
				SET V_BuyerMessage=TRIM(V_BuyerMessage);
				CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
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
				
				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
				INTO V_Max,V_Min
				FROM tmp_sales_trade_order;
				
				-- 更新主订单退款状态
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4);
					SET V_TradeStatus=5;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
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
					SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
					FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
				ELSE
					SET V_SingleSpecNO='';
				END IF;
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
				
				-- 标记未付款的
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
				
				-- 所有订单都已付款
				IF V_UnmergeMask AND (V_UnmergeMask&1)=0 THEN
					-- 取消等同名未付款标记
					UPDATE sales_trade SET unmerge_mask=(unmerge_mask&~1)
					WHERE customer_id=V_CustomerID AND trade_status>=15 AND trade_status<95;
				END IF;
				*/
				-- 估算货品成本
				SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_NewWarehouseID AND ss.spec_id=tsto.spec_id
				WHERE tsto.actual_num>0;
				SET V_AreaAlias = '';
				-- 更新订单
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
					logistics_id=V_LogisticsID,receiver_dtb=V_AreaAlias,flag_id=V_FlagID,salesman_id=V_SalesmanID,
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
					INSERT INTO sales_trade_log(trade_id,operator_id,type,data,message)
					SELECT V_DeliverTradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
					FROM cfg_oper_reason 
					WHERE reason_id = V_IsFreezed;
				END IF;
				
				-- 订单全链路
				IF V_TradeStatus<55 THEN
					CALL I_SALES_TRADE_TRACE(V_DeliverTradeID, 1, '');
				END IF;
				
				-- 日志
				INSERT INTO sales_trade_log(trade_id,operator_id,type,`data`,message,created) VALUES(V_DeliverTradeID,P_OperatorID,2,0,CONCAT('付款:',V_Tid,V_ExtMsg),V_PayTime);
			ELSE
				-- 日志
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,-1,CONCAT('待发货异常状态：',V_TradeStatus));
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,5,CONCAT('首付款:',V_Tid));
			ELSE
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,-1,CONCAT('待尾款异常状态：',V_TradeStatus));
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
							
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(IF(V_ApiTradeStatus=80,'订单退款','订单关闭'),',拦截出库单'));
						
							INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
							-- 标记退款
							UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
						END IF;
					ELSE
						ITERATE TRADE_BY_API_LABEL;
					END IF;
				ELSEIF V_TradeStatus>=95 THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('关闭:',V_Tid,',订单已发货'));
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
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,8,CONCAT('部分退款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('部分关闭:',V_Tid));
					END IF;
				ELSE -- 除赠品之前没其它货品
					-- 取消赠品
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
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('退款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('关闭:',V_Tid));
					END IF;
				END IF;
			END LOOP;
			CLOSE trade_by_api_cursor;
			
			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid;
			UPDATE api_trade SET modify_flag=0,process_status=70 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=V_ShopID AND src_tid=V_Tid;
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
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,80,'客户打款,交易完成');
					ELSE
						UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,80,'客户打款,订单未发货');
					END IF;
				/*
				ELSE
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=105 WHERE trade_id=V_DeliverTradeID;
						UPDATE stockout_order SET status=105 WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款:',V_Tid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款,订单未发货:',V_Tid));
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
		
		-- 提取业务员
		CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
		IF V_SalesmanID THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
				-- 判断物流更新,暂时不处理
				/*
				IF V_LogisticsID THEN
					SELECT logistics_id INTO V_OldLogisticsID FROM sales_trade WHERE trade_id=V_DeliverTradeID;
					IF V_OldLogisticsID<>V_LogisticsID THEN
						UPDATE sales_trade SET logistics_id=V_LogisticsID WHERE trade_id=V_DeliverTradeID;
						CALL I_DL_REFRESH_TRADE(V_DeliverTradeID, P_OperatorID, IF(@cfg_open_package_strategy,4,0), 0);
						
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
						SELECT V_DeliverTradeID,P_OperatorID,20,CONCAT('从备注提取物流:',logistics_name)
						FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
					END IF;
				END IF;
				*/
			ELSEIF V_TradeStatus<40 THEN
				-- 加异常标记
				UPDATE sales_trade SET bad_reason=(bad_reason|16) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
			ELSEIF V_TradeStatus >= 40 AND V_TradeStatus < 95 AND @cfg_remark_change_block_stockout THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|64) WHERE stockout_id=V_StockoutID;
						-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'客服备注变化,拦截出库单');
				END IF;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT('客服备注变化,拦截出库:',V_Tid));
			ELSEIF V_TradeStatus >= 95 AND @cfg_remark_change_block_stockout THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化,订单已发货:',V_Tid));
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
				INSERT IGNORE INTO crm_customer_telno(customer_id,type,telno,created) VALUES(V_CustomerID, 1, V_ReceiverMobile,NOW());
				-- CALL I_CRM_TELNO_CREATE_IDX(V_CustomerID, 1, V_ReceiverMobile);
			END IF;
			
			IF V_ReceiverTelno<> '' THEN
				INSERT IGNORE INTO crm_customer_telno(customer_id,type,telno,created) VALUES(V_CustomerID, 2, V_ReceiverTelno,NOW());
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
			
			-- 看地址是否有变化
			IF EXISTS(SELECT 1 FROM sales_trade st,api_trade ax
				WHERE st.trade_id=V_DeliverTradeID AND ax.platform_id=V_PlatformID AND ax.tid=V_Tid
					AND st.receiver_name=ax.receiver_name
					AND st.receiver_province=ax.receiver_province
					AND st.receiver_city=ax.receiver_city
					AND st.receiver_district=ax.receiver_district
					AND st.receiver_address=ax.receiver_address
					AND st.receiver_mobile=ax.receiver_mobile
					AND st.receiver_telno=ax.receiver_telno
					AND st.receiver_zip=ax.receiver_zip
					AND st.receiver_area=ax.receiver_area
					AND st.receiver_ring=ax.receiver_ring
					AND st.to_deliver_time=ax.to_deliver_time
					AND st.dist_center=ax.dist_center
					AND st.dist_site=ax.dist_site) THEN
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('平台收件地址变更,系统已处理:',V_Tid));
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|4) WHERE stockout_id=V_StockoutID;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'收件地址变更,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
				
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更:',V_Tid));
				
				-- 刷新物流,大头笔,包装
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|3, 0);
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|2) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
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
			
			-- 拦截出库单
			IF V_TradeStatus>=40 THEN
				UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
				SET so.block_reason=(so.block_reason|8)
				WHERE sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id
					AND so.stockout_id=sod.stockout_id
					AND sto.trade_id=V_DeliverTradeID
					AND so.status<>5;
					
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
				-- 出库单日志??
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			ELSEIF V_TradeStatus<35 THEN
				UPDATE sales_trade st,api_trade ax 
				SET st.invoice_type=ax.invoice_type,
					st.invoice_title=ax.invoice_title,
					st.invoice_content=ax.invoice_content,
					st.version_id=st.version_id+1
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化:',V_Tid));
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
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
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,12,CONCAT('平台仓库变化:',V_Tid));
				END IF;
			ELSEIF V_TradeStatus<110 THEN
				UPDATE sales_trade SET bad_reason=(bad_reason|8) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,12,CONCAT('仓库变化,请处理:',V_Tid));
			END IF;
			
		END LOOP;
		CLOSE trade_by_api_cursor;
		
		SET V_ModifyFlag=V_ModifyFlag & ~64;
	END IF;
	*/
	UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SYNC_SUB_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_SUB_ORDER`(IN `P_OperatorID` INT,
	IN `P_RecID` BIGINT,
	IN `P_ModifyFlag` INT,
	IN `P_ApiTradeStatus` TINYINT,
	IN `P_ShopID` TINYINT,
	IN `P_Tid` VARCHAR(40),
	IN `P_Oid` VARCHAR(40),
	IN `P_RefundStatus` TINYINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_DeliverTradeID,V_WarehouseID,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,V_IsMaster,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_SalesGoodsCount,V_LeftSharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_HasSendGoods,V_TradeStatus TINYINT DEFAULT(0);
	
	DECLARE trade_order_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id
		FROM sales_trade_order sto LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=P_ShopID and sto.src_oid=P_Oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
				IF V_NOT_FOUND THEN
					LEAVE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				IF V_TradeStatus>=95 THEN -- 发货了,?有可能售后
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid,',订单已发货'));
					ITERATE TRADE_ORDER_BY_API_LABEL;
				END IF;
				
				IF V_TradeStatus>=95 THEN
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
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
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,'子订单退款,拦截出库单');
						END IF;
						
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
						
						UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
				
				-- 回收库存
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
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('子订单退款:',P_Oid));
				ELSE
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid));
				END IF;
				
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, V_IsMaster, 1, V_LeftSharePost);
		ELSEIF P_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=P_ShopID AND src_oid=P_Oid;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,80,CONCAT('客户打款,交易完成',P_Oid));
					ELSE
						UPDATE stockout_order SET consign_status=(consign_status|1073741824) WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,80,CONCAT('客户打款,订单未发货',P_Oid));
					END IF;
				/*
				ELSE
					IF V_TradeStatus>=95 THEN
						UPDATE sales_trade SET trade_status=105 WHERE trade_id=V_DeliverTradeID;
						UPDATE stockout_order SET status=105 WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5;
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
							VALUES(V_DeliverTradeID,P_OperatorID,81,CONCAT('客户打款:',P_Tid,',',P_Oid));
					ELSE
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) 
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
						INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
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
					SET V_NewRefundStatus=IF(V_Max,3,4);
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				UPDATE sales_trade SET refund_status=V_NewRefundStatus,version_id=version_id+1 WHERE trade_id=V_DeliverTradeID;
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
					
					INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'取消退款,需要驳回处理');
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
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, 2, 0);
			END IF;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, 0, 1, V_LeftSharePost);
			
			-- 日志
			INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,15,
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'平台修改货品,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
			END IF;
		
			UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
			INSERT INTO sales_trade_log(trade_id,operator_id,type,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid));
			
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		SET P_ModifyFlag = P_ModifyFlag & ~16;
	END IF;
	
	UPDATE api_trade_order SET modify_flag=0 WHERE rec_id=P_RecID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_SALES_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_SALES_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中建立的临时表'
MAIN_LABEL: BEGIN 
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sales_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  spec_id INT(11) NOT NULL,
	  shop_id smallint(6) NOT NULL,
	  platform_id tinyint(4) NOT NULL,
	  src_oid VARCHAR(40) NOT NULL,
	  suite_id INT(11) NOT NULL DEFAULT 0,
	  src_tid VARCHAR(40) NOT NULL,
	  gift_type TINYINT(1) NOT NULL DEFAULT 0,
	  refund_status TINYINT(4) NOT NULL DEFAULT 0,
	  guarantee_mode TINYINT(4) NOT NULL DEFAULT 1,
	  delivery_term TINYINT(4) NOT NULL DEFAULT 1,
	  bind_oid VARCHAR(40) NOT NULL DEFAULT '',
	  num DECIMAL(19, 4) NOT NULL,
	  price DECIMAL(19, 4) NOT NULL,
	  actual_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  order_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  adjust DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_post DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  paid DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  tax_rate DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
	  goods_name VARCHAR(255) NOT NULL,
	  goods_id INT(11) NOT NULL,
	  goods_no VARCHAR(40) NOT NULL,
	  spec_name VARCHAR(100) NOT NULL,
	  spec_no VARCHAR(40) NOT NULL,
	  spec_code VARCHAR(40) NOT NULL,
	  suite_no VARCHAR(40) NOT NULL DEFAULT '',
	  suite_name VARCHAR(255) NOT NULL DEFAULT '',
	  suite_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  is_print_suite TINYINT(1) NOT NULL DEFAULT 0,
	  api_goods_name VARCHAR(255) NOT NULL,
	  api_spec_name VARCHAR(40) NOT NULL,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  volume DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  commission DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  goods_type TINYINT(4) NOT NULL DEFAULT 1,
	  flag INT(11) NOT NULL DEFAULT 0,
	  large_type TINYINT(1) NOT NULL DEFAULT 0,
	  invoice_type TINYINT(4) NOT NULL DEFAULT 0,
	  invoice_content VARCHAR(255) NOT NULL DEFAULT '',
	  from_mask INT(11) NOT NULL DEFAULT 0,
	  cid INT(11) NOT NULL DEFAULT 0,
	  is_master TINYINT(1) NOT NULL DEFAULT 0,
	  is_allow_zero_cost TINYINT(1) NOT NULL DEFAULT 0,
	  remark VARCHAR(60) NOT NULL DEFAULT '',
	  PRIMARY KEY (rec_id),
	  INDEX IX_tmp_sales_trade_order_src_id (shop_id, src_oid),
	  UNIQUE INDEX UK_tmp_sales_trade_order (spec_id, shop_id, src_oid, suite_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_RESERVE_STOCK`;
DELIMITER //
CREATE PROCEDURE `I_RESERVE_STOCK`(IN `P_TradeID` INT, IN `P_Type` INT, IN `P_NewWarehouseID` INT, IN `P_OldWarehouseID` INT)
    SQL SECURITY INVOKER
    COMMENT '占用库存'
MAIN_LABEL:BEGIN
	IF P_OldWarehouseID THEN
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT P_OldWarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
			IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
			sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
		
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=P_TradeID;
	END IF;
	IF P_NewWarehouseID THEN
		IF P_Type = 2 THEN	-- 未付款库存
			INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num);
			
			UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 3 THEN	-- 已保留待审核
			INSERT INTO stock_spec(warehouse_id,spec_id,order_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num 
			FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num);
			
			UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 4 THEN	-- 待发货
			INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status)
			(SELECT P_NewWarehouseID,spec_id,actual_num,1 FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1;
			
			UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 5 THEN	-- 预订单库存
			INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num);
			
			UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		END IF;
	END IF;
	
	-- 更新平台货品库存变化
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0;
	
	-- 组合装
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
	
END//
DELIMITER ;

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
			LEFT JOIN api_trade_order ato ON ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid
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
	IF (V_TradePlatformId<>1 AND V_TradePlatformId<>2) OR @cfg_order_allow_part_sync=0 THEN
	
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

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	END IF;
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		IF @cfg_sales_trade_trace_operator THEN
			SELECT shortname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_IMPLEMENT_CLEAN`;
DELIMITER //
CREATE PROCEDURE SP_IMPLEMENT_CLEAN(IN P_CleanId INT)
  SQL SECURITY INVOKER
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;

	-- 清空账款信息和统计信息
	IF P_CleanId <> 6 AND P_CleanId <> 7 THEN


		-- 统计
-- 		TODO 统计的部分表在做完统计模块后需要打开

		DELETE  FROM stat_daily_sales_amount;

 		DELETE  FROM stat_monthly_sales_amount;

	END IF;
	-- 全清(货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关)
	IF P_CleanId = 1 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;
		
		-- crm

		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;
		-- purchase
		DELETE FROM purchase_order_log;
		DELETE FROM purchase_order_detail;
		DELETE FROM purchase_order;
		-- goods
      
		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;
		DELETE FROM goods_log;

		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;

		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		
		
		-- stock

		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		-- 关联表
		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		
		
		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;
		--  清除库存同步记录
		DELETE FROM api_stock_sync_record;

		-- 通知消息 new add
		DELETE FROM sys_notification;


		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		--  订单全链路
		DELETE FROM sales_trade_trace;
		-- 客服备注修改历史记录  new add
		DELETE FROM api_trade_remark_history;
		-- 订单备注提取策略 new add
		DELETE FROM cfg_trade_remark_extract;
		-- cfg
		DELETE FROM cfg_stock_sync_rule;

		-- sys
		DELETE FROM sys_other_log;
		DELETE FROM sys_process_background;
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 1, '清除系统所有信息');
	END IF;
	-- 清除货品信息(清除：订单、库存、事务，保留客户、员工信息）
	IF P_CleanId = 2 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;





		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;

		DELETE FROM goods_log;



		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;


		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;


		-- stock

		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;


		DELETE FROM stockin_order_detail;

		DELETE FROM stockin_order;


		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 2, '清除货品信息(清除：订单、库存，保留客户、员工信息)');
	END IF;
	-- 清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工)
	IF P_CleanId = 3 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;

		-- crm
		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;






		-- stock

 		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 3, '清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工信息)');
	END IF;
	-- 清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户、供货商信息)
	IF P_CleanId = 4 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;




		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;




		-- hr
		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 4, '清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户信息)');
	END IF;
	-- 清除订单、采购信息、库存调拨等相关库存订单信息(库存量由脚本重刷)
	IF P_CleanId = 5 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;

		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;
		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		DELETE FROM stock_spec_detail;
-- zhuyi1
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0,purchase_num=0,
			to_purchase_num=0,purchase_arrive_num=0,refund_num=0,transfer_num=0,return_num=0,return_exch_num=0,
			return_onway_num=0,refund_exch_num=0,refund_onway_num=0,default_position_id=IF(default_position_id=0,-warehouse_id,default_position_id);
		-- INSERT INTO stock_spec_detail(stock_spec_id,spec_id,stockin_detail_id,position_id,position_no,zone_id,zone_no,cost_price,stock_num,virtual_num,created)
		--	SELECT ss.rec_id,ss.spec_id,0,ss.default_position_id,cwp.position_no,cwz.zone_id,cwz.zone_no,ss.cost_price,ss.stock_num,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
 		-- INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,created)
		--	SELECT ss.warehouse_id,ss.spec_id,ss.default_position_id,cwz.zone_id,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;

		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 5, ' 清除订单、采购、盘点、等相关库存信息');
	END IF;


	-- 清除订单信息
	IF P_CleanId = 8 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api  删除原始订单和退换单
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		-- sales 删除原始订单和退换单
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

	
		-- 销售出库单未在stock_change_history里插入数据的都可以删除，在stock_change_history里插入数据的将入库类型改为其他入库
		UPDATE stockout_order so,stockout_order_detail sod,stock_change_history sch
			SET so.src_order_type=7,so.src_order_id=0,so.src_order_no='',sod.src_order_type=7 ,sod.src_order_detail_id=0,
			sch.src_order_type=7, sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=1 AND so.stockout_id=sod.stockout_id
			AND so.stockout_id=sch.stockio_id AND sch.type=2;

		DELETE sodp.* FROM stockout_order_detail_position sodp,stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND sod.rec_id=stockout_order_detail_id AND so.src_order_type=1 ;

		-- 删除未出库的出库单管理的stockout_pack_order,stockout_pack_order_detail 必须先删 有外键
-- 		DELETE spod.*  FROM stockout_pack_order spo,stockout_pack_order_detail spod,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id AND spo.pack_id=spod.pack_id AND so.src_order_type=1;

-- 		DELETE spo.*  FROM stockout_pack_order spo,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id  AND so.src_order_type=1;

		-- 删除未出库的出库单和出库单详情
		DELETE sod.* FROM stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND so.src_order_type=1 ;

		DELETE so.* FROM stockout_order so WHERE so.src_order_type=1 ;
		-- 清空打印批次相关的数据


		-- stockin
		-- 将退货入库的入库单改成其他入库
		UPDATE stockin_order so,stockin_order_detail sod,stock_change_history sch
			SET so.src_order_type=6,so.src_order_id=0,so.src_order_no='',sod.src_order_type=6,sod.src_order_detail_id=0,
			sch.src_order_type=6,sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  AND so.stockin_id=sch.stockio_id
			AND sch.type=1;

		-- 删除未入库的入库单和入库单详情
		DELETE sod.* FROM stockin_order so,stockin_order_detail sod
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  ;

		DELETE so.* FROM stockin_order so WHERE so.src_order_type=3 ;
		-- stock
		-- 将stock_spec中的未付款量，预订单量，待审核量，待发货量清0    销售退货量 销售换货在途量（发出和收回）这三个暂时没用 所以没有清0
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0;
		-- 将stock_spec_detail中的占用量清0
		UPDATE stock_spec_detail SET reserve_num=0,is_used_up=0;
		-- 删除日志表中有关订单操作的日志
		DELETE FROM stock_inout_log WHERE order_type=2 AND operate_type IN(1,2,3,4,7,14,23,24,51,52,62,63,111,113,120,121,300);
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message) VALUES(13, P_UserId, 8,'清除订单信息，和订单相关的出库单，入库单的类型变为其他出库，其他入库');
		-- -- stockout_order 中的字段consign_status,customer_id等没有用了，
	END IF;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `SP_INT_ARR_TO_TBL`;
DELIMITER //
CREATE PROCEDURE `SP_INT_ARR_TO_TBL`(IN `P_Str` VARCHAR(8192), IN `P_Clear` INT)
    SQL SECURITY INVOKER
    COMMENT '将字符串数组插入到临时表，如1,2,4,2'
MAIN_LABEL:BEGIN
	DECLARE V_I1, V_I2, V_I3 BIGINT;
	DECLARE V_IT VARCHAR(255);
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_xchg(
		rec_id int(11) NOT NULL AUTO_INCREMENT,
		f1 VARCHAR(40),
		f2 VARCHAR(1024),
		f3 VARCHAR(40),
		PRIMARY KEY (rec_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	IF P_Str IS NULL OR LENGTH(P_Str)=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_Clear THEN
		DELETE FROM tmp_xchg;
	END IF;
	
	IF P_Str=' ' THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_I1 = 1;
	STR_LABEL:LOOP
	 SET V_I2 = locate(',', P_Str, V_I1);
	 IF V_I2 = 0 THEN
	   SET V_IT = substring(P_Str, V_I1);
	 ELSE
	   SET V_IT = substring(P_Str, V_I1, V_I2 - V_I1);
	 END IF;
	 
	 IF V_IT IS NOT NULL THEN
		set V_I3 = cast(V_IT as signed);
		INSERT INTO tmp_xchg(f1) VALUES(V_I3);
	 END IF;
	 
	 IF V_I2 = 0 OR V_I2 IS NULL THEN
	   LEAVE STR_LABEL;
	 END IF;
	
	 SET V_I1 = V_I2 + 1;
	END LOOP;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_ALL`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_ALL`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	DECLARE V_LockName, V_TradeNO, V_BuyerNick VARCHAR(40);
	
	DECLARE trade_cursor CURSOR FOR SELECT rec_id,tid,buyer_nick FROM api_trade WHERE process_status=10 AND bad_reason=0 AND platform_id>0 LIMIT 100;
	-- DECLARE refund_cursor CURSOR FOR SELECT refund_id FROM api_refund WHERE process_status=0 LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
		RESIGNAL;
	END;
	
	-- 统计递交的订单数
	SET @tmp_delivered_count = 0;
	
	SET V_LockName = CONCAT('deliver_lock_', DATABASE());
	IF NOT IS_FREE_LOCK(V_LockName) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT GET_LOCK(V_LockName, 1) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET @cur_uid = P_OperatorID;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	IF @cfg_order_auto_submit THEN
		OPEN trade_cursor;
		TRADE_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				IF V_TradeCount >= 100 THEN
					-- 需要测试，改成1测试
					SET V_TradeCount = 0;
					CLOSE trade_cursor;
					OPEN trade_cursor;
					ITERATE TRADE_LABEL;
				END IF;
				LEAVE TRADE_LABEL;
			END IF;
			
			SET V_TradeCount = V_TradeCount+1;
			
			CALL I_DL_DELIVER_API_TRADE(V_TradeID, P_OperatorID);
			
			IF @sys_code = 0 THEN
				SET @tmp_delivered_count = @tmp_delivered_count+1;
			ELSEIF @sys_code<>2 THEN	-- 是指此订单已经递交过
				INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
			END IF;
		END LOOP;
		close trade_cursor;
	END IF;
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(P_OperatorID);
	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 19);
	SET @tmp_to_preorder_count = @tmp_to_process_count;
	
	-- 待审核订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 20);
	SET @tmp_to_check_count = @tmp_to_process_count;
	*/
	-- 递交退款单
	/*
	SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, P_OperatorID);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
		END IF;
	END LOOP;
	close refund_cursor;
	*/
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
	-- 解锁
	SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_SOME`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_SOME`(IN `P_TradeIDs` VARCHAR(1024))
    SQL SECURITY INVOKER
    COMMENT '根据指定的原始单ID进行递交'
BEGIN
	DECLARE V_TradeNO,V_BuyerNick VARCHAR(40);
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	
	DECLARE trade_cursor CURSOR FOR select ax.rec_id,tid,buyer_nick 
		from tmp_xchg tx,api_trade ax 
		where ax.rec_id=tx.f1;
	
	-- DECLARE refund_cursor CURSOR FOR select refund_id from api_refund where process_status=0 LIMIT 500;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
		RESIGNAL;
	END;
		
	CALL SP_INT_ARR_TO_TBL(P_TradeIDs, 1);
	
	-- 清除无效货品标记
	UPDATE tmp_xchg tx,api_trade ax 
	SET bad_reason=(bad_reason&~1)
	WHERE ax.rec_id=tx.f1;
	
	UPDATE tmp_xchg tx,api_trade ax,api_trade_order ato 
	SET ato.is_invalid_goods=0
	WHERE ax.rec_id=tx.f1 AND ato.platform_id=ax.platform_id AND ato.tid=ax.tid;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	OPEN trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			LEAVE TRADE_LABEL;
		END IF;
		
		CALL I_DL_DELIVER_API_TRADE(V_TradeID, @cur_uid);
		
		IF @sys_code = 0 THEN
			SET @tmp_delivered_count = @tmp_delivered_count+1;
		ELSEIF @sys_code<>1 THEN	-- 1是指此订单已经递交过
			INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE trade_cursor;
	
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(@cur_uid);
	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核

	-- 预订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 19);
	-- 待审核订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 20);
	*/
	
	-- 递交退款单
	/*SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 500 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, @cur_uid);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
			INSERT INTO tbl_deliver_error(tid, error_code, error_info) values(V_RefundID, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE refund_cursor;*/
	
	-- CALL I_DL_SYNC_REFUND(P_OperatorID);
	
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_CHAR`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_CHAR`(IN `P_Key` VARCHAR(60), OUT `P_Val` VARCHAR(256), IN `P_Def` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_INT`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_INT`(IN `P_Key` VARCHAR(60), OUT `P_Val` INT, IN `P_Def` INT)
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;





DROP FUNCTION IF EXISTS `FN_EMPTY`;
DELIMITER //
CREATE FUNCTION `FN_EMPTY`(`P_Str` VARCHAR(1024)) RETURNS tinyint(4)
    NO SQL
    SQL SECURITY INVOKER
    DETERMINISTIC
BEGIN
	IF P_Str IS NULL OR P_Str = '' THEN
		RETURN 1;
	END IF;
	
	RETURN 0;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_GOODS_NO`;
DELIMITER //
CREATE FUNCTION `FN_GOODS_NO`(`P_Type` INT, `P_TargetID` INT) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
    COMMENT '查询货品或组合装信息'
BEGIN
	DECLARE V_GoodsNO VARCHAR(40);
	
	SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',
		@tmp_goods_id='',@tmp_spec_id='',@tmp_barcode='',@tmp_retail_price=0;-- ,@tmp_sn_enable=0
	
	IF P_Type=1 THEN
		SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name,gs.spec_code,gg.goods_id,gs.spec_id,gs.barcode,gs.retail_price -- gs.is_sn_enable,
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,V_GoodsNO,@tmp_spec_name,@tmp_spec_code,@tmp_goods_id,@tmp_spec_id,@tmp_barcode,@tmp_retail_price -- ,@tmp_sn_enable
		FROM goods_spec gs,goods_goods gg WHERE gs.spec_id=P_TargetID AND gs.goods_id=gg.goods_id;
		
	ELSEIF P_Type=2 THEN
		-- SELECT 1 INTO @tmp_sn_enable
		-- FROM goods_suite_detail gsd, goods_spec gs
		-- WHERE gsd.suite_id=P_TargetID AND gs.spec_id=gsd.spec_id AND gs.is_sn_enable>0 LIMIT 1;
		
		SELECT suite_no,suite_name,short_name,suite_id,'','',barcode,retail_price
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,@tmp_goods_id,@tmp_spec_id,@tmp_spec_name,@tmp_barcode,@tmp_retail_price 
		FROM goods_suite WHERE suite_id=P_TargetID;
		
		SET V_GoodsNO='';
	END IF;
	
	RETURN V_GoodsNO;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SEQ`;
DELIMITER //
CREATE FUNCTION `FN_SEQ`(`P_Name` VARCHAR(20)) RETURNS int(11)
	READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
 BEGIN
     SET @tmp_seq=1;
     INSERT INTO sys_sequence(`name`,`val`) VALUES(P_Name, 1) ON DUPLICATE KEY UPDATE val=(@tmp_seq:=(val+1));
     RETURN @tmp_seq;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SPEC_NO_CONV`;
DELIMITER $$
CREATE FUNCTION `FN_SPEC_NO_CONV`(`P_GoodsNO` VARCHAR(40), `P_SpecNO` VARCHAR(40)) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_I INT;
	
	IF LENGTH(@cfg_goods_match_split_char)>0 THEN
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_GoodsNO);
		IF V_I THEN
			SET P_GoodsNO=SUBSTRING(P_GoodsNO, 1, V_I-1);
		END IF;
		
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_SpecNO);
		IF V_I THEN
			SET P_SpecNO=SUBSTRING(P_SpecNO, 1, V_I-1);
		END IF;
		
	END IF;
	
	RETURN IF(@cfg_goods_match_concat_code,CONCAT(P_GoodsNO,P_SpecNO),IF(P_SpecNO<>'',P_SpecNO,P_GoodsNO));
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SYS_NO`;
DELIMITER //
CREATE FUNCTION `FN_SYS_NO`(`P_Key` VARCHAR(50)) RETURNS VARCHAR(60) CHARSET UTF8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_DateChanged BIT;
	DECLARE V_Prefix, V_PostfixStr, V_PostfixStr2 VARCHAR(64);
	DECLARE V_PostfixLen, V_PostfixVal,V_DateForm INT;
	DECLARE V_NowDate DATE;
	
	-- date_form 0 全日期 1短日期 2无日期
	
	SET V_NowDate = CURDATE();
	
	UPDATE sys_no_cfg SET postfix_val=LAST_INSERT_ID(IF(V_NowDate=last_date OR date_form=2,postfix_val+1,1)),last_date=V_NowDate
	WHERE `key`=P_Key;
	
	SET V_PostfixVal = LAST_INSERT_ID();
	
	SELECT prefix,postfix_len,date_form into V_Prefix,V_PostfixLen,V_DateForm from sys_no_cfg WHERE `key`=P_Key;
	
	SET V_PostfixStr = CAST(V_PostfixVal AS CHAR);
	SET V_PostfixStr2 = LPAD(V_PostfixStr, V_PostfixLen, '0');
	IF LENGTH(V_PostfixStr2) < LENGTH(V_PostfixStr) THEN
		SET V_PostfixStr2 = V_PostfixStr;
	END IF;
	
	IF V_DateForm=0 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%Y%m%d'), V_PostfixStr2);
	ELSEIF V_DateForm=1 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%y%m%d'), V_PostfixStr2);
	END IF;
	
	RETURN CONCAT(V_Prefix, V_PostfixStr2);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE_CHANGED`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE_CHANGED`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ModifyFlag,V_TradeCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ShopID,V_ApiTradeStatus,V_RefundStatus TINYINT DEFAULT(0);
	DECLARE V_ApiTradeID,V_RecID BIGINT DEFAULT(0);
	DECLARE V_Tid,V_Oid VARCHAR(40);
	
	DECLARE api_trade_cursor CURSOR FOR 
		SELECT rec_id FROM api_trade FORCE INDEX(IX_api_trade_modify_flag)
		WHERE modify_flag>0 AND bad_reason=0 LIMIT 100;
	
	DECLARE api_trade_order_cursor CURSOR FOR 
		SELECT modify_flag,rec_id,status,shop_id,tid,oid,refund_status
		FROM api_trade_order WHERE modify_flag>0
		LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 主订单变化
	OPEN api_trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_trade_cursor INTO V_ApiTradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_cursor;
				OPEN api_trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_MAIN_ORDER(P_OperatorID, V_ApiTradeID);
		
	END LOOP;
	CLOSE api_trade_cursor;
	
	
	SET V_TradeCount = 0;
	-- 子订单变化
	OPEN api_trade_order_cursor;
	TRADE_ORDER_LABEL: LOOP
		-- modify_flag,rec_id,status,refund_status
		FETCH api_trade_order_cursor INTO V_ModifyFlag,V_RecID,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_order_cursor;
				OPEN api_trade_order_cursor;
				ITERATE TRADE_ORDER_LABEL;
			END IF;
			LEAVE TRADE_ORDER_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_SUB_ORDER(P_OperatorID,V_RecID,V_ModifyFlag,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus);
	END LOOP;
	CLOSE api_trade_order_cursor;
END//
DELIMITER ;

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
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
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
	
	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '原始单状态不正确';
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
	CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
	IF V_IsPreorder THEN
		SET V_ExtMsg = ' 进预订单原因:客服备注提取';
	END IF;
	
	-- 客户备注
	SET V_BuyerMessage=TRIM(V_BuyerMessage);
	CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
	
	-- select warehouse_id randomly
	SELECT warehouse_id INTO V_WarehouseID2 FROM cfg_warehouse where is_disabled = 0 limit 1;
	
	-- get logistics_id from cfg_shop 
	IF V_DeliveryTerm=2 THEN
		SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	ELSE 
		SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
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
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
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
	SET V_AreaAlias = '';
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID2 AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;
	/*
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
	*/
	
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
				/*
				IF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
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
				END IF;
				*/
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
		salesman_id,is_sealed,freeze_reason,delay_to_time,commission,gift_mask,unmerge_mask,raw_goods_type_count,raw_goods_count,single_spec_no,created)
	VALUES(FN_SYS_NO('sales'),V_PlatformID,V_ShopID,V_Tid,V_TradeStatus,1,V_TradeFrom,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,V_FenxiaoNick,
		V_TradeTime,V_PayTime,V_PayAccount,V_CustomerType,V_CustomerID,V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,
		V_ToDeliverTime,V_DistCenter,V_DistSite,V_BuyerMessage,V_Remark,V_RemarkFlag,
		V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_TotalVolume,V_LogisticsID,V_AreaAlias,V_PostCost,V_GoodsCost,
		NOT FN_EMPTY(V_Remark),NOT FN_EMPTY(V_BuyerMessage),NOT FN_EMPTY(V_Remark),
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID2,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType2,V_StockoutNO,V_PackageID,
		V_SalesmanID,V_IsSealed,V_IsFreezed,V_DelayToTime,V_Commission,V_GiftMask,V_UnmergeMask,V_ApiOrderCount,V_ApiGoodsCount,V_SingleSpecNO,V_Now);
	
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
		SELECT V_TradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
		FROM cfg_oper_reason 
		WHERE reason_id = V_IsFreezed;
	END IF;
	
	-- 更新原始单
	UPDATE api_trade SET process_status=20,
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_salesman_id=V_SalesmanID,
		x_trade_flag=V_FlagID,
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

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_SALES_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_SALES_TRADE`(IN `P_OperatorID` INT, IN `P_Status` INT)
    SQL SECURITY INVOKER
    COMMENT '递交第二步'
BEGIN
	DECLARE V_CurTime, V_TradeID, V_ShopID,V_WarehouseType,V_WarehouseID,V_DeliveryTerm,V_IsSealed,
		V_TradeID2, V_WarehouseID2,V_GiftMask,V_PlatformID, V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict, 
		V_TradeStatus, V_TradeCount, V_CheckedTradeID, V_CustomerID,V_TradeChanged,V_IsLarge,
		V_ToStatus, V_NOT_FOUND, V_bNoMerge, V_bNoSplit, V_bAllSplit,V_FreezeReasonID,
		V_LockWarehouse,V_SplitFromTradeID,V_UnmergeMask,V_GroupID INT DEFAULT(0);
	
	DECLARE V_IsSetWareByGoods INT DEFAULT(1);
	
	DECLARE V_RawTradeNO VARCHAR(40);
	DECLARE V_ReceiverArea,V_ReceiverName VARCHAR(64);
	DECLARE V_Tid,V_ReceiverAddress VARCHAR(256);
	
	DECLARE trade_cursor CURSOR FOR 
		SELECT trade_id,src_tids,shop_id,platform_id,delivery_term,customer_id,
			receiver_name,receiver_province,receiver_city,receiver_district,
			receiver_area,receiver_address,warehouse_type,warehouse_id, 
			gift_mask,customer_id,is_sealed,freeze_reason,split_from_trade_id 
		FROM sales_trade WHERE trade_status=P_Status
		LIMIT 100;
	
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
		FETCH trade_cursor INTO V_TradeID, V_Tid, V_ShopID, V_PlatformID, V_DeliveryTerm, V_CustomerID, 
			V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
			V_ReceiverArea,V_ReceiverAddress, V_WarehouseType, V_WarehouseID, 
			V_GiftMask,V_CustomerID,V_IsSealed,V_FreezeReasonID,V_SplitFromTradeID;
		
		IF V_NOT_FOUND THEN
			IF V_TradeCount >= 500 THEN
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
		
		
		--  待审核订单合并
		IF @cfg_auto_merge AND V_IsLarge<2  AND V_bNoMerge=0 AND V_FreezeReasonID=0 AND V_DeliveryTerm=1 AND V_SplitFromTradeID = 0 THEN
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
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;
			/*ELSE
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
					st.trade_from=1 AND 
					st.trade_type=1 AND 
					st.freeze_reason=0 AND 
					st.revert_reason=0 AND 
					st.checkouter_id=0 AND 
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;*/
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
					UPDATE sales_trade SET unmerge_mask=(V_UnmergeMask & ~2) WHERE trade_id=V_TradeID;
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
		/*IF @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
				ITERATE TRADE_LABEL;
			END IF;
		END IF;
		
		IF V_TradeChanged THEN
			CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
		END IF;*/
		
		-- 占用库存
		CALL I_RESERVE_STOCK(V_TradeID, IF(V_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);
		
		-- 标记同名未合并的
		IF @cfg_order_check_warn_has_unmerge THEN
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
		END IF;
		
		COMMIT;
		
		SET @tmp_to_process_count = @tmp_to_process_count+1;
	END LOOP;
	CLOSE trade_cursor;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_CLIENT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_CLIENT_REMARK`(IN `P_Remark` VARCHAR(1024), 
	INOUT `P_TradeFlag` INT, 
	INOUT `P_WmsType` INT, 
	INOUT `P_WarehouseID` INT, 
	INOUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客户备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_Kw VARCHAR(255);
	DECLARE V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=2 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_c_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=2 THEN
			IF V_Target>0 AND P_TradeFlag=0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND P_WarehouseID=0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=6 THEN
			IF P_FreezeReason=0 THEN
				SET P_FreezeReason=GREATEST(1,V_Target);
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_REMARK`(IN `P_Remark` VARCHAR(1024), OUT `P_LogisticsID` INT, OUT `P_TradeFlag` INT, OUT `P_SalesmanID` INT, INOUT `P_WmsType` INT, OUT `P_WarehouseID` INT, OUT `P_IsPreorder` INT, OUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客服备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_SalesManName,V_Kw VARCHAR(255);
	DECLARE V_MacroBeginIndex, V_MacroEndIndex, V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=1 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_LogisticsID=0;
	SET P_TradeFlag=0;
	SET P_SalesmanID=0;
	SET P_WarehouseID = 0;
	SET P_IsPreorder=0;
	SET P_FreezeReason=0;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 根据括号提取
	IF @cfg_salesman_macro_begin<> '' AND @cfg_salesman_macro_end <> '' THEN
		SET V_MacroBeginIndex = LOCATE(@cfg_salesman_macro_begin, P_Remark, 1);
		IF V_MacroBeginIndex > 0 THEN
			SET V_MacroEndIndex = LOCATE(@cfg_salesman_macro_end, P_Remark, V_MacroBeginIndex+1);
			IF V_MacroEndIndex>0 THEN
				SET V_SalesManName = SUBSTRING(P_Remark, V_MacroBeginIndex+CHAR_LENGTH(@cfg_salesman_macro_begin), V_MacroEndIndex-V_MacroBeginIndex-CHAR_LENGTH(@cfg_salesman_macro_end));
				IF V_SalesManName IS NOT NULL AND V_SalesManName<>'' THEN 
					SELECT employee_id INTO P_SalesmanID FROM hr_employee WHERE fullname=V_SalesManName AND deleted=0 AND is_disabled=0;
				END IF;
			END IF;
		END IF;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=1 THEN
			IF V_Target>0 THEN
				SET P_LogisticsID=V_Target;
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=3 THEN
			IF V_Target>0 THEN
				SET P_SalesmanID=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=5 THEN
			SET P_IsPreorder=1;
		ELSEIF V_Type=6 THEN
			SET P_FreezeReason=GREATEST(1,V_Target);
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_INIT`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT`(IN `P_CreateApiGoods` INT)
    SQL SECURITY INVOKER
    COMMENT '递交处理初始化'
MAIN_LABEL:BEGIN
	DECLARE V_AutoMatchGoods INT DEFAULT(0);
	
	/*配置*/
	-- 是否开启自动递交
	CALL SP_UTILS_GET_CFG_INT('order_auto_submit',@cfg_order_auto_submit,1);

	-- 连接货品和规格商家编码
	CALL SP_UTILS_GET_CFG_INT('sys_goods_match_concat_code', @cfg_goods_match_concat_code, 0);

	-- 自动匹配平台货品的截取字符
	CALL SP_UTILS_GET_CFG_CHAR('goods_match_split_char', @cfg_goods_match_split_char, '');	
	
	-- 动态跟踪自动匹配货品
	-- CALL SP_UTILS_GET_CFG_INT('goods_match_dynamic_check', @cfg_goods_match_dynamic_check, 0);
	
	-- 是否自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge', @cfg_auto_merge, 1);
	
	-- 自动合并是否重新计算赠品
	CALL SP_UTILS_GET_CFG_INT('sales_trade_auto_merge_gift', @cfg_auto_merge_gift, 1);
	-- 订单审核时提示同名未合并
	CALL SP_UTILS_GET_CFG_INT('order_check_warn_has_unmerge', @cfg_order_check_warn_has_unmerge, 1);
	
	-- 延时审核分钟数
	CALL SP_UTILS_GET_CFG_INT('order_delay_check_min', @cfg_delay_check_sec, 0);	
	
	SET @cfg_delay_check_sec = @cfg_delay_check_sec*60;
	
	-- 已付等未付分钟数
	-- CALL SP_UTILS_GET_CFG_INT('order_wait_unpay_min', @cfg_wait_unpay_sec, 0);	
	
	SET @cfg_wait_unpay_sec = @cfg_wait_unpay_sec*60;
	
	-- 大件自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split', @cfg_order_auto_split, 1);
	
	-- 大件拆分最大次数
	-- CALL SP_UTILS_GET_CFG_INT('sales_split_large_goods_max_num', @cfg_sales_split_large_goods_max_num, 50);
	
	-- 按不同仓库自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split_by_warehouse',@cfg_order_auto_split_by_warehouse,0);
	
	-- 订单合并方式
	CALL SP_UTILS_GET_CFG_INT('order_auto_merge_mode', @cfg_order_merge_mode, 0);	
	-- 审核时提示条件
	CALL SP_UTILS_GET_CFG_INT('order_check_merge_warn_mode', @cfg_order_check_merge_warn_mode, 0);
	
	-- 业务员
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_begin', @cfg_salesman_macro_begin, '');	
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_end', @cfg_salesman_macro_end, '');	
	
	
	IF @cfg_salesman_macro_begin='' OR @cfg_salesman_macro_begin IS NULL OR @cfg_salesman_macro_end='' OR @cfg_salesman_macro_end IS NULL THEN
		SET @cfg_salesman_macro_begin='';
		SET @cfg_salesman_macro_end='';
	END IF;
	
	-- 物流选择方式：全局唯一，按店铺，按仓库
	-- CALL SP_UTILS_GET_CFG_INT('logistics_match_mode', @cfg_logistics_match_mode, 0);	

	-- 按货品先仓库
	-- CALL SP_UTILS_GET_CFG_INT('sales_trade_warehouse_bygoods', @cfg_sales_trade_warehouse_bygoods, 0);
	
	-- 如果仓库是按货品策略选出,修改时给出提醒
	-- CALL SP_UTILS_GET_CFG_INT('order_check_alert_locked_warehouse', @cfg_chg_locked_warehouse_alert, 0);

	-- 是否启用备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_remark_extract', @cfg_enable_remark_extract, 0);	
	-- 客户备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_c_remark_extract', @cfg_enable_c_remark_extract, 0);	
	-- 订单进入待审核后是否根据备注提取物流
	CALL SP_UTILS_GET_CFG_INT('order_deliver_enable_cs_remark_track', @cfg_order_deliver_enable_cs_remark_track, 1);	
	
	-- 自动按商家编码匹配货品
	CALL SP_UTILS_GET_CFG_INT('apigoods_auto_match', V_AutoMatchGoods, 1);	
	
	-- 转预订单设置
	/* CALL SP_UTILS_GET_CFG_INT('order_go_preorder', @cfg_order_go_preorder, 0);
	IF @cfg_order_go_preorder THEN
		CALL SP_UTILS_GET_CFG_INT('order_preorder_lack_stock', @cfg_order_preorder_lack_stock, 0);
		CALL SP_UTILS_GET_CFG_INT('preorder_split_to_order_condition',@cfg_preorder_split_to_order_condition,0);
	END IF;
	*/
	CALL SP_UTILS_GET_CFG_INT('remark_change_block_stockout', @cfg_remark_change_block_stockout, 1);
	-- 物流同步后,发生退款不拦截
	CALL SP_UTILS_GET_CFG_INT('unblock_stockout_after_logistcs_sync', @cfg_unblock_stockout_after_logistcs_sync, 0);
	
	-- 销售凭证自动过账
	-- CALL SP_UTILS_GET_CFG_INT('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	
	-- 米氏抢单全局开关
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_hold', @cfg_order_deliver_hold, 0);
	
	--  根据重量计算物流
	CALL SP_UTILS_GET_CFG_INT('calc_logistics_by_weight',@cfg_calc_logistics_by_weight,0);
	
	--  包装策略
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy', @cfg_open_package_strategy,0); 
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy_type',@cfg_open_package_strategy_type,1); -- 1,根据重量   2,根据体积
	
	-- 是否开启订单全链路
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	-- 订单中原始货品数量是否包含赠品
	CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	
	-- 强制凭证不需要审核
	-- SET @cfg_fa_voucher_must_check=0;
	
	-- 是否需要从原始单货品生成api_goods_spec
	IF NOT P_CreateApiGoods THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*导入平台货品*/
	START TRANSACTION;
	
	SELECT 1 INTO @tmp_dummy FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE;
	
	UPDATE api_goods_spec ag,api_trade_order ato,api_trade at
	SET ag.modify_flag=
		IF(ag.outer_id=ato.goods_no AND ag.spec_outer_id=ato.spec_no, ag.modify_flag, ag.modify_flag|1),
		ag.outer_id=ato.goods_no,ag.spec_outer_id=ato.spec_no,
		ag.goods_name=ato.goods_name,ag.spec_name=ato.spec_name,
		ag.cid=IF(ato.cid='',ag.cid,ato.cid),at.is_new=0
	WHERE at.process_status=10 AND at.is_new=1 AND ato.tid=at.tid AND ato.shop_id=at.shop_id AND ato.goods_id<>''
		AND ag.shop_id=ato.shop_id AND ag.goods_id=ato.goods_id AND ag.spec_id=ato.spec_id;
	
	-- 要测试平台更新编码的同步
	INSERT INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,cid,modify_flag,created)
	(
		SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,at.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,ato.cid,1,NOW()
		FROM api_trade_order ato INNER JOIN api_trade at ON ato.tid=at.tid AND ato.shop_id=at.shop_id
		WHERE at.process_status=10 AND at.is_new=1 AND ato.goods_id<>''
	)
	ON DUPLICATE KEY UPDATE modify_flag=
		IF(api_goods_spec.outer_id=VALUES(outer_id) AND api_goods_spec.spec_outer_id=VALUES(spec_outer_id), api_goods_spec.modify_flag, api_goods_spec.modify_flag|1),
		outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),
		goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),
		cid=IF(VALUES(cid)='',api_goods_spec.cid,VALUES(cid));
	
	UPDATE api_trade SET is_new=0 WHERE process_status=10 and is_new=1;
	COMMIT;
	
	IF V_AutoMatchGoods THEN
		-- 对新增和变化的平台货品进行自动匹配
		UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;
		
		-- 刷新品牌分类
		UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
		SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;
		
		UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
		SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;
		
		-- 刷新无效货品
		UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax
		SET ato.is_invalid_goods=0,ax.bad_reason=0
		WHERE ato.is_invalid_goods=1 AND ag.`shop_id`=ato.`shop_id` AND ag.`goods_id`=ato.`goods_id` AND
			ag.`spec_id`=ato.`spec_id` AND ax.shop_id=ato.`shop_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND
			ag.match_target_type>0; 
		
		-- 自动刷新库存同步规则
		-- 应该判断一下规则是否变化了，如果变化了，要触发同步开关????????????
		UPDATE api_goods_spec gs,
		(SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id 
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1)
		WHERE gs.rec_id=da.rec_id;
		UPDATE api_goods_spec SET modify_flag=(modify_flag&~1) WHERE modify_flag>0 AND (modify_flag&1);
	END IF;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MAP_TRADE_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_MAP_TRADE_GOODS`(IN `P_TradeID` INT, IN `P_ApiTradeID` BIGINT, IN `P_UseTran` INT, OUT `P_ApiOrderCount` INT, OUT `P_ApiGoodsCount` INT)
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中'
MAIN_LABEL: BEGIN 
	DECLARE V_MatchTargetID,V_GoodsID,V_SGoodsID,V_SpecID,V_SuiteSpecCount,V_I,V_GiftType,V_MasterID,V_ShopID,
		V_Cid,V_IsDeleted,V_NOT_FOUND INT DEFAULT(0);
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
	
	-- 订单信息
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,oid,ato.status,refund_status,bind_oid,invoice_type,invoice_content,num,ato.price,adjust_amount,
			discount,share_discount,share_amount,share_post,paid,match_target_type,match_target_id,spec_no,ato.gift_type,
			ato.goods_name,ato.spec_name,aps.cid,aps.is_manual_match,ato.goods_no,ato.spec_no,ato.remark
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON aps.shop_id=V_ShopID AND aps.goods_id=ato.goods_id and aps.spec_id=ato.spec_id
		WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND ato.process_status=10;
	
	-- 组合装货品
	DECLARE goods_suite_cursor CURSOR FOR 
		SELECT gsd.spec_id,gsd.num,gsd.is_fixed_price,gsd.fixed_price,gsd.ratio,gg.goods_name,gs.goods_id,gg.goods_no,
			gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,(gs.length*gs.width*gs.height) as volume ,gs.tax_rate,gs.large_type,(gs.retail_price*gsd.num),gs.is_allow_zero_cost,gs.deleted
		FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gsd.suite_id=V_MatchTargetID AND gsd.num>0
		ORDER BY gsd.is_fixed_price DESC;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	
	DELETE FROM tmp_sales_trade_order;
	
	SELECT platform_id,shop_id,tid,delivery_term,guarantee_mode,trade_mask
	INTO V_PlatformID,V_ShopID,V_Tid,V_DeliveryTerm,V_GuaranteeMode,V_TradeMask
	FROM api_trade WHERE rec_id=P_ApiTradeID;
	
	-- 展开货品
	SET P_ApiOrderCount = 0;
	SET P_ApiGoodsCount = 0;
	SET V_MasterAmount = -1;
	SET V_Now = NOW();
	SET @tmp_refund_occur = 0;
	SET @sys_code=0, @sys_message='OK';
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH trade_order_cursor INTO 
			V_RecID,V_Oid,V_OrderStatus,V_RefundStatus,V_BindOid,V_InvoiceType,V_InvoiceContent,V_Num,V_Price,V_AdjustAmount,
			V_Discount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,V_MatchTargetType,V_MatchTargetID,V_ApiSpecNO,V_GiftType,
			V_ApiGoodsName,V_ApiSpecName,V_CidNO,V_IsManualMatch,V_OuterId,V_SpecOuterId,V_Remark;
			
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_Num <= 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
			 	ROLLBACK;
			 	UPDATE api_trade SET bad_reason=(bad_reason|1) WHERE rec_id=P_ApiTradeID;
			 END IF;
			 SET @sys_code=4, @sys_message = '货品数量为零';
			 LEAVE MAIN_LABEL;
		END IF;
		
		SET P_ApiOrderCount = P_ApiOrderCount + 1;
		SET P_ApiGoodsCount = P_ApiGoodsCount + V_Num;
		
		-- 类目及佣金暂时不做
		-- SET V_CommissionFactor = 0, V_Cid = 0;
		-- 未绑定
		IF V_PlatformID=0 THEN -- 线下订单不需判断无效货品 
			SELECT `type`, target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_ApiSpecNO;
		ELSE
			/*
			IF V_CidNO <> '' THEN
				SELECT rec_id,commission_factor INTO V_Cid,V_CommissionFactor FROM api_goods_category WHERE cid=V_CidNO AND shop_id=V_ShopID;
				SET V_NOT_FOUND=0;
			END IF;
			*/
			-- 判断是否开启动态匹配
			IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
				SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
				SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			END IF;
		END IF;
		
		
		IF V_NOT_FOUND OR V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
				 ROLLBACK;
				 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
			 END IF;
			 SET @sys_code=3, @sys_message = CONCAT('订单包含无效货品:',V_Tid);
			 LEAVE MAIN_LABEL;
		END IF;
		
		-- 子订单关闭,当退款处理
		IF V_OrderStatus=80 OR V_OrderStatus=90 THEN
			 SET V_RefundStatus=5;
		END IF;
		
		IF V_RefundStatus>1 THEN -- 需要创建退款单
			 SET @tmp_refund_occur = V_RefundStatus;
		END IF;
		
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,gs.tax_rate,gs.large_type,gs.is_allow_zero_cost,gs.length*gs.width*gs.height
				INTO V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,V_Weight,V_TaxRate,V_LargeType,V_IsZeroCost,V_Volume
			FROM goods_spec gs LEFT JOIN goods_goods gg USING(goods_id)
			WHERE gs.spec_id=V_MatchTargetID AND gs.deleted=0;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					 ROLLBACK;
					 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=4, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效单品');
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果钱已经付了，则为款到发货
			IF V_Paid >= V_ShareAmount+V_SharePost THEN
				 SET V_OrderDeliveryTerm = 1;
			ELSE
				 SET V_OrderDeliveryTerm = V_DeliveryTerm;
			END IF;
			
			SET V_SharePrice=TRUNCATE(V_ShareAmount/V_Num,4);
			
			-- 退款状态处理??
			INSERT INTO tmp_sales_trade_order(
				spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,paid,
				order_price,share_amount,share_post,share_price,adjust,discount,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
				api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,from_mask,gift_type,
				cid,is_allow_zero_cost,remark)
			VALUES(V_MatchTargetID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_Num,V_Price,
				IF(V_RefundStatus>2,0,V_Num),V_Paid,V_SharePrice,V_ShareAmount,V_SharePost,V_SharePrice,V_AdjustAmount,
				(V_Discount-V_AdjustAmount+V_ShareDiscount),
				V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,
				V_ApiGoodsName,V_ApiSpecName,V_Weight*V_Num,V_Volume*V_Num,TRUNCATE(V_ShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,
				V_InvoiceType,V_InvoiceContent,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
			/*
			-- 找一个未退款的，金额最大的子订单作主订单,不考虑主订单
			IF V_RefundStatus<=2 AND V_ShareAmount > V_MasterAmount THEN
				 SET V_MasterAmount=V_ShareAmount;
				 SET V_MasterID = LAST_INSERT_ID();
			END IF;
			*/
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			-- 取组合装信息
			SELECT suite_no,suite_name,is_unsplit,is_print_suite INTO V_SuiteNO,V_SuiteName,V_IsUnsplit,V_IsPrintSuite
			FROM goods_suite WHERE suite_id=V_MatchTargetID;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=5, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效组合装');
				LEAVE MAIN_LABEL;
			END IF;
			
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
			
			-- 无货品
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装为空');
				LEAVE MAIN_LABEL;
			END IF;
			
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
					CLOSE trade_order_cursor;
					IF P_UseTran THEN
						ROLLBACK;
						CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
					END IF;
					SET @sys_code=7, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装包含已删除单品 ', V_SSpecNO);
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
				
				INSERT INTO tmp_sales_trade_order(
					spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,
					order_price,share_price,share_amount,share_post,discount,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
					api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,suite_id,suite_no,suite_name,suite_num,suite_amount,
					suite_discount,is_print_suite,from_mask,gift_type,cid,is_allow_zero_cost,remark)
				VALUES(V_SpecID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_SNum,V_SPrice,IF(V_RefundStatus>2,0,V_SNum),
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
			
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = '组合装无货品';
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	-- 标记主子订单
	-- 注：拆分合并时处理
	-- UPDATE tmp_sales_trade_order SET is_master=1 WHERE rec_id=V_MasterID;
	
	IF P_TradeID THEN
		INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,created)
		SELECT P_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,NOW()
		FROM tmp_sales_trade_order;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MARK_INVALID_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_MARK_INVALID_TRADE`(IN `P_TradeID` INT, IN `P_ShopId` TINYINT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '标记原始单的子订单有无效货品'
MAIN_LABEL:BEGIN
	DECLARE V_RecID,V_MatchTargetType,V_MatchTargetID,V_InvalidGoods,V_GoodsCount,V_IsManualMatch,V_Deleted,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,match_target_type,match_target_id,is_manual_match,ato.goods_no,ato.spec_no
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON ato.shop_id=aps.shop_id AND ato.goods_id=aps.goods_id and ato.spec_id=aps.spec_id
		WHERE ato.shop_id=P_ShopId AND ato.tid=P_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		
		FETCH trade_order_cursor INTO V_RecID,V_MatchTargetType,V_MatchTargetID,V_IsManualMatch,V_OuterId,V_SpecOuterId;
		IF V_NOT_FOUND THEN
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		-- 未绑定
		IF V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			
			-- 添加到平台货品
			INSERT IGNORE INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,modify_flag,created)
			SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,ax.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,1,NOW()
			FROM api_trade_order ato LEFT JOIN api_trade ax ON ax.tid=ato.tid AND ax.platform_id=ato.platform_id
			WHERE ato.rec_id=V_RecID;
			
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
			SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
			SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			IF V_NOT_FOUND THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
				ITERATE TRADE_GOODS_LABEL;
			END IF;
		END IF;
		
		SET V_Exists=0,V_Deleted = 0;
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_spec WHERE spec_id=V_MatchTargetID;
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_suite WHERE suite_id=V_MatchTargetID;
		END IF;
		
		
		IF NOT V_Exists OR V_Deleted THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_MatchTargetType = 2 THEN
			SELECT COUNT(rec_id) INTO V_GoodsCount FROM goods_suite_detail WHERE suite_id=V_MatchTargetID;
			IF V_GoodsCount=0 THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
			
			-- 判断组合装里货品是否都有效
			IF EXISTS(SELECT 1 FROM goods_suite_detail gsd,goods_spec gs 
				WHERE gsd.suite_id=V_MatchTargetID AND gs.spec_id=gsd.spec_id AND gs.deleted>0) THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	IF V_InvalidGoods THEN
		UPDATE api_trade SET bad_reason=1 WHERE rec_id=P_TradeID;
	END IF;
	COMMIT;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PREPARE_GIFT_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_PREPARE_GIFT_GOODS`(IN `P_TradeID` INT, INOUT `P_First` INT)
	SQL SECURITY INVOKER
	COMMENT '将订单货品插入到临时表,为赠品准备'
MAIN_LABEL:BEGIN
	IF P_First=0 THEN
		LEAVE MAIN_LABEL;
	END IF;

	DELETE FROM tmp_gift_trade_order;
	
	SET P_First=0;
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 0,sto.spec_id,sto.actual_num,sto.discount,sto.share_amount,sto.weight,sto.from_mask,gc.path,gg.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_goods gg ON gg.goods_id=sto.goods_id 
		LEFT JOIN goods_class gc ON gc.class_id=gg.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id=0 AND actual_num>0 AND sto.gift_type=0)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 1,sto.suite_id,sto.suite_num,SUM(sto.discount),SUM(sto.share_amount),SUM(sto.weight),BIT_OR(sto.from_mask),gc.path,gs.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_suite gs ON gs.suite_id=sto.suite_id
		LEFT JOIN goods_class gc ON gc.class_id=gs.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id>0 AND sto.actual_num>0 AND sto.gift_type=0
	GROUP BY platform_id,src_oid)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PUSH_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_PUSH_REFUND`(IN `P_OperatorID` INT, IN `P_ShopID` INT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '递交过程中自动生成退款单'
BEGIN
	DECLARE V_RefundStatus,V_GoodsID,V_SpecId,V_RefundID,V_RefundID2,V_Status,V_ApiStatus,V_Type,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Num DECIMAL(19,4);
	DECLARE V_Oid,V_RefundNO,V_RefundNO2 VARCHAR(40);
	
	DECLARE refund_order_cursor CURSOR FOR 
		SELECT refund_id,refund_status,status,oid
		FROM api_trade_order
		WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 删除临时退款单
	DELETE stro FROM sales_tmp_refund_order stro, api_trade_order sto 
	WHERE stro.shop_id=P_ShopID AND stro.oid=sto.oid AND sto.shop_id=P_ShopID AND sto.tid=P_Tid;
	
	OPEN refund_order_cursor;
	REFUND_ORDER_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH refund_order_cursor INTO V_RefundNO,V_RefundStatus,V_ApiStatus,V_Oid;
		IF V_NOT_FOUND THEN
			LEAVE REFUND_ORDER_LABEL;
		END IF;
		
		IF V_RefundStatus < 2 THEN -- 取消退款
			-- 如果订单已发货，说明是个售后退货,不需要再更新退款单
			IF V_ApiStatus>=40 THEN
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			DELETE FROM sales_tmp_refund_order WHERE shop_id=P_ShopID AND oid=V_Oid;
			
			-- 更新退款单状态
			-- 一个原始单只能出现在一个退款单中
			SET V_RefundID=0;
			SELECT sro.refund_id INTO V_RefundID FROM sales_refund_order sro,sales_refund sr
			WHERE sro.shop_id=P_ShopID AND sro.oid=V_Oid AND sro.refund_id=sr.refund_id AND sr.type=1 LIMIT 1;
			
			IF V_RefundID THEN
				UPDATE sales_refund_order SET process_status=10 WHERE refund_id=V_RefundID AND shop_id=P_ShopID AND oid=V_Oid;
				SET V_Status=0;
				SELECT 1 INTO V_Status FROM sales_refund_order WHERE refund_id=V_RefundID AND process_status<>10 LIMIT 1;
				IF V_Status=0 THEN  -- 全部子订单都取消
					UPDATE sales_refund SET process_status=10,status=V_RefundStatus WHERE refund_id=V_RefundID;
					-- 日志
					INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,4,P_OperatorID,'平台取消退款');
				END IF;
			END IF;
			-- 原始退款单状态?
			
			ITERATE REFUND_ORDER_LABEL;
		END IF;
		
		-- 目前只有淘宝存在退款单号
		-- 没有退款单号的，自动生成一个
		IF V_RefundNO='' THEN
			
			SET V_Type=IF(V_ApiStatus<40,1,2);
			SET V_NOT_FOUND=0;
			SELECT ar.refund_id,ar.refund_no INTO V_RefundID,V_RefundNO FROM api_refund ar,api_refund_order aro
			WHERE ar.shop_id=P_ShopID AND ar.tid=P_Tid AND ar.`type`=V_Type 
				AND aro.shop_id=P_ShopID AND aro.refund_no=ar.refund_no AND aro.oid=V_Oid LIMIT 1;
			
			IF V_NOT_FOUND THEN
				-- 一个货品一个退款单
				SET V_RefundNO=FN_SYS_NO('apirefund');
				
				-- 创建原始退款单
				INSERT INTO api_refund(platform_id,refund_no,shop_id,tid,title,type,status,process_status,pay_account,refund_amount,actual_refund_amount,buyer_nick,refund_time,created)
				(SELECT ax.platform_id,V_RefundNO,ax.shop_id,P_Tid,ato.goods_name,V_Type,ato.refund_status,0,ax.pay_account,ato.refund_amount,ato.refund_amount,ax.buyer_nick,NOW(),NOW()
				FROM api_trade_order ato, api_trade ax
				WHERE ato.shop_id=P_ShopID AND ato.oid=V_Oid AND ax.shop_id=P_ShopID AND ax.tid=P_Tid);
				
				INSERT INTO api_refund_order(platform_id,refund_no,shop_id,oid,status,goods_name,spec_name,num,price,total_amount,goods_id,spec_id,goods_no,spec_no,created)
				(SELECT platform_id,V_RefundNO,shop_id,oid,refund_status,goods_name,spec_name,num,price,share_amount,goods_id,spec_id,goods_no,spec_no,NOW()
				FROM api_trade_order WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0 AND refund_id='');
			ELSE
				UPDATE api_refund SET status=V_RefundStatus,modify_flag=(modify_flag|1) WHERE refund_id=V_RefundID;
				UPDATE api_refund_order SET status=V_RefundStatus WHERE shop_id=P_ShopID AND refund_no=V_RefundNO AND oid=V_Oid;
			END IF;
			
		ELSE
			IF V_ApiStatus>=40 THEN -- 已发货,销后退款,让退款同步脚本处理
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			-- 平台支持退款单的
			-- 查找退款单是否已经存在，如果已存在，就不需要创建临时退款单，直接更新退款单状态
			SET V_RefundID=0;
			SELECT refund_id INTO V_RefundID FROM sales_refund WHERE src_no=V_RefundNO AND shop_id=P_ShopID AND type=1 LIMIT 1;
			IF V_RefundID THEN
				SET V_Status=80;
				IF V_RefundStatus=2 THEN
					SET V_Status=20;
				ELSEIF V_RefundStatus=3 THEN
					SET V_Status=60;
				ELSEIF V_RefundStatus=4 THEN
					SET V_Status=60;
				END IF;

				UPDATE sales_refund SET process_status=V_Status,status=V_RefundStatus WHERE refund_id=V_RefundID;
				-- 日志
				INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,2,P_OperatorID,'平台同意退款');
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
		END IF;
		
		IF V_RefundStatus>2 THEN
			-- 创建临时退款单
			INSERT IGNORE INTO sales_tmp_refund_order(shop_id, oid) VALUES(P_ShopID, V_Oid);
		END IF;
	END LOOP;
	CLOSE refund_order_cursor;
		
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_REFRESH_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_REFRESH_TRADE`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_RefreshFlag` INT, IN `P_ToStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_WarehouseID,V_WarehouseType, V_ShopID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,
		V_LogisticsID,V_DeliveryTerm,V_Max,V_Min,V_NewRefundStatus,V_NewLogisticsID,V_Locked,V_GoodsTypeCount,
		V_NoteCount,V_GiftMask,V_PackageID,V_SalesmanId,V_PlatformId,V_RemarkFlag,V_FlagId,V_BuyerMessageCount,
		V_CsRemarkCount,V_InvoiceType,V_TradeStatus,V_RawGoodsTypeCount, V_RawGoodsCount INT DEFAULT(0);
	DECLARE V_Addr,V_SrcTids,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(255);
	DECLARE V_BuyerMessage,V_CsRemark VARCHAR(1024);
	DECLARE V_AreaAlias,V_SingleSpecNO VARCHAR(40);
	DECLARE V_GoodsCount,V_Weight,V_PostCost,V_Paid,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GoodsCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4);
	
	-- P_RefreshFlag
	-- 1选择物流 2计算大头笔 4选择包装 8刷新备注
	
	-- 统计子订单
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0)),SUM(actual_num),COUNT(DISTINCT IF(actual_num<=0,NULL,sto.spec_id)),
		SUM(IF(actual_num>0,sto.weight,0)),SUM(IF(actual_num>0,paid,0)),MAX(IF(actual_num>0,delivery_term,1)),
		SUM(IF(actual_num>0,share_amount+discount,0)),SUM(IF(actual_num>0,share_post,0)),SUM(IF(actual_num>0,discount,0)),
		SUM(IF(actual_num>0,IF(delivery_term=1,share_amount+share_post,paid),0)),
		SUM(IF(actual_num>0,IF(delivery_term=2,share_amount+share_post-paid,0),0)),
		BIT_OR(IF(actual_num>0,gift_type,0)),SUM(IF(actual_num>0,commission,0)),SUM(actual_num*gs.length*gs.width*gs.height)
	INTO V_Max,V_Min,V_GoodsCount,V_GoodsTypeCount,V_Weight,V_Paid,V_DeliveryTerm,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
	FROM sales_trade_order sto LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id  WHERE sto.trade_id=P_TradeID;	
	
	-- 退款状态
	IF V_GoodsCount<=0 THEN
		SET V_NewRefundStatus=IF(V_Max,3,4);
		SET P_ToStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_NewRefundStatus=1;
	ELSEIF V_Max THEN
		SET V_NewRefundStatus=2;
	ELSE
		SET V_NewRefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	IF @cfg_sales_raw_count_exclude_gift IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	END IF;
	
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_RawGoodsTypeCount, V_RawGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM sales_trade_order
	WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_RawGoodsCount IS NULL THEN
		 SET V_RawGoodsCount=0;
	END IF;

	IF V_RawGoodsTypeCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM sales_trade_order 
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- V_WmsType, V_WarehouseNO, V_ShopID, V_TradeID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict;
	SELECT trade_status,warehouse_type, warehouse_id,shop_id,logistics_id,post_cost,receiver_province,receiver_city,receiver_district,receiver_address,receiver_dtb,package_id
	INTO V_TradeStatus,V_WarehouseType, V_WarehouseID,V_ShopID,V_LogisticsID,V_PostCost,V_ReceiverProvince,V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_AreaAlias,V_PackageID
	FROM sales_trade
	WHERE trade_id = P_TradeID;
	
	/*
	-- 订单未审核
	IF V_TradeStatus<35 THEN
		-- 包装
		IF P_RefreshFlag & 4  THEN 
			CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_Weight,V_TotalVolume);

			IF V_PackageID THEN
				SELECT weight INTO V_PackageWeight  FROM goods_spec WHERE spec_id = V_PackageID;
				SET V_Weight=V_Weight + V_PackageWeight;

			END IF;
		END IF;

		-- 选择物流
		IF P_RefreshFlag & 1 THEN
			CALL I_DL_DECIDE_LOGISTICS(V_NewLogisticsID, -1, V_DeliveryTerm, V_ShopID, V_WarehouseID,V_Weight, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_Addr);
			IF V_LogisticsID<>V_NewLogisticsID AND V_NewLogisticsID>0 THEN
				SET V_LogisticsID=V_NewLogisticsID;
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		END IF;
		
		-- 估算邮费
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_Weight, V_LogisticsID, V_ShopID, V_WarehouseID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		
		
	END IF;
	*/
	-- 估算货品成本
	SELECT TRUNCATE(IFNULL(SUM(sto.actual_num*IFNULL(ss.cost_price,0)),0),4) INTO V_GoodsCost FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID AND ss.spec_id=sto.spec_id
	WHERE sto.trade_id=P_TradeID AND sto.actual_num>0;
	-- SET V_AreaAlias = '';
	-- 便签数量
	-- SELECT COUNT(1) INTO V_NoteCount FROM common_order_note WHERE type=1 AND order_id=P_TradeID;
	
	SET @old_sql_mode=@@SESSION.sql_mode;
	SET SESSION sql_mode='';
	SELECT IFNULL(LEFT(GROUP_CONCAT(IF(ax.platform_id OR  ax.trade_from=3 OR ax.trade_from=5,ax.tid,NULL)),255),''),MAX(ax.x_salesman_id),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.buyer_message)='',NULL,TRIM(ax.buyer_message))),1024),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.remark)='',NULL,TRIM(ax.remark))),1024),''),
		MAX(ax.platform_id),
		MAX(ax.remark_flag),
		MAX(ax.x_trade_flag),
		SUM(IF(TRIM(ax.buyer_message)='',0,1)),
		SUM(IF(TRIM(ax.remark)='',0,1)),
		MAX(ax.invoice_type),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_title)='',NULL,TRIM(ax.invoice_title))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_content)='',NULL,TRIM(ax.invoice_content))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.pay_account)='',NULL,TRIM(ax.pay_account))),128),'')
	INTO
		V_SrcTids, V_SalesmanId, V_BuyerMessage, V_CsRemark, V_PlatformId, V_RemarkFlag, V_FlagId,
		V_BuyerMessageCount, V_CsRemarkCount, V_InvoiceType, V_InvoiceTitle, V_InvoiceContent,V_PayAccount
	FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=P_TradeID) sto
		LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
	
	SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
	
	IF V_PlatformId IS NULL THEN
		UPDATE sales_trade
		SET buyer_message_count=NOT FN_EMPTY(buyer_message),
			cs_remark_change_count=NOT FN_EMPTY(cs_remark),
			cs_remark_count=NOT FN_EMPTY(cs_remark),
			refund_status=V_NewRefundStatus,
			goods_count=V_GoodsCount,
			goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,
			post_amount=V_PostAmount,
			discount=V_Discount,
			receivable=V_GoodsAmount+V_PostAmount-V_Discount,
			dap_amount=V_DapAmount,
			cod_amount=(V_CodAmount+ext_cod_fee),
			warehouse_id=V_WarehouseID,
			trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
			logistics_id=V_LogisticsID,
			post_cost=V_PostCost,
			goods_cost=V_GoodsCost,
			receiver_dtb=V_AreaAlias,
			weight=V_Weight,
			volume=V_TotalVolume,
			delivery_term=V_DeliveryTerm,
			package_id = V_PackageID,
			paid=V_Paid,
			commission=V_Commission,
			profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
			note_count=V_NoteCount,
			gift_mask=V_GiftMask,
			version_id=version_id+1
		WHERE trade_id=P_TradeID;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新订单
	UPDATE sales_trade
	SET platform_id=V_PlatformId,
		src_tids=V_SrcTids,
		buyer_message=V_BuyerMessage,
		cs_remark=IF(NOT (cs_remark_change_count&2) OR (P_RefreshFlag&8),V_CsRemark,cs_remark),
		buyer_message_count=V_BuyerMessageCount,
		cs_remark_count=GREATEST(V_CsRemarkCount,NOT FN_EMPTY(cs_remark)),
		remark_flag=V_CsRemarkCount,
		flag_id=IF(flag_id=0,V_FlagId,flag_id),
		invoice_type=IF(invoice_type=0,V_InvoiceType,invoice_type),
		invoice_title=IF(invoice_title='',V_InvoiceTitle,invoice_title),
		invoice_content=IF(invoice_content='',V_InvoiceContent,invoice_content),
		salesman_id=IF(salesman_id,salesman_id,V_SalesmanId),
		refund_status=V_NewRefundStatus,
		goods_count=V_GoodsCount,
		goods_type_count=V_GoodsTypeCount,
		goods_amount=V_GoodsAmount,
		post_amount=V_PostAmount,
		discount=V_Discount,
		receivable=V_GoodsAmount+V_PostAmount-V_Discount,
		dap_amount=V_DapAmount,
		cod_amount=(V_CodAmount+ext_cod_fee),
		warehouse_id=V_WarehouseID,
		trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
		logistics_id=V_LogisticsID,
		post_cost=V_PostCost,
		goods_cost=V_GoodsCost,
		receiver_dtb=V_AreaAlias,
		weight=V_Weight,
		volume=V_TotalVolume,
		delivery_term=V_DeliveryTerm,
		package_id = V_PackageID,
		paid=V_Paid,
		commission=V_Commission,
		profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
		note_count=V_NoteCount,
		gift_mask=V_GiftMask,
		pay_account = V_PayAccount,
		raw_goods_type_count=V_RawGoodsTypeCount,
		raw_goods_count=V_RawGoodsCount,
		single_spec_no=V_SingleSpecNO,
		version_id=version_id+1
	WHERE trade_id=P_TradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SELECT_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SELECT_GIFT`(INOUT `P_priority` INT, IN `P_rule_id` INT,IN `P_rule_multiple_type` INT, IN `P_real_multiple` INT , IN `P_real_limit` INT , IN `P_total_name_num` INT, IN `P_total_cs_remark_num` INT,IN `P_limit_gift_stock` DECIMAL(19,4))
    SQL SECURITY INVOKER
    COMMENT '按赠品的库存优先级来选择赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,VS_spec_id,VS_is_suite,VS_gift_num,VS_real_gift_num,VS_send INT DEFAULT(0);
	DECLARE VS_Stock DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE send_cursor CURSOR FOR SELECT  spec_id,is_suite,gift_num
		FROM  cfg_gift_send_goods  
		WHERE rule_id=P_rule_id AND priority=P_priority ;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_priority=11;
	PRIORITY_LABEL: LOOP
		IF P_priority=15 THEN 
			SET P_priority=99;
			LEAVE MAIN_LABEL;
		END IF;
		SET VS_send = 0;
		OPEN send_cursor;
		SEND_LABEL: LOOP
			FETCH send_cursor INTO VS_spec_id,VS_is_suite,VS_gift_num;
			
			IF V_NOT_FOUND = 1 THEN
				SET V_NOT_FOUND = 0;
				IF VS_send THEN
					close send_cursor;
					leave MAIN_LABEL;
				ELSE
					SET P_priority=P_priority+1;
					CLOSE send_cursor;
					ITERATE PRIORITY_LABEL;
				END IF;
				
			END IF;
			
			IF VS_is_suite=0 THEN
				SELECT IFNULL(SUM(stock_num-order_num-sending_num),0) INTO VS_Stock FROM stock_spec WHERE spec_id=VS_spec_id;	
			ELSE
				SELECT SUM(tmp.suite_stock) INTO VS_Stock FROM (
				SELECT FLOOR(IFNULL(MIN(IFNULL(stock_num-order_num-sending_num, 0)/gsd.num),0)) AS suite_stock 
				FROM  goods_suite_detail gsd 
				LEFT JOIN  stock_spec ss ON ss.spec_id=gsd.spec_id 
				WHERE gsd.suite_id=VS_spec_id GROUP BY ss.warehouse_id
				) tmp;
			END IF;
			
			SET VS_real_gift_num=VS_gift_num;
			/*
			SET VS_real_gift_num=0;
			
			IF P_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=P_total_cs_remark_num;
				
			ELSEIF P_total_name_num>0 THEN 
				SET VS_real_gift_num=P_total_name_num;
				
			ELSE
				
				IF P_rule_multiple_type=0 THEN 
					IF P_real_multiple<>10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				ELSE
					IF P_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			IF VS_Stock-P_limit_gift_stock<VS_real_gift_num THEN
				SET P_priority=P_priority+1;
				SET VS_send = 0;
				CLOSE send_cursor;
				ITERATE PRIORITY_LABEL;
			ELSE
				SET VS_send = 1;
			END IF;
			
		END LOOP;
		CLOSE send_cursor;
		LEAVE MAIN_LABEL;
	END LOOP; 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SEND_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SEND_GIFT`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_CustomerID` INT, INOUT `P_SendOK` INT)
    SQL SECURITY INVOKER
    COMMENT '计算赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	
	/*使用变量*/
	DECLARE VS_sel_rule_group,VS_spec_match,VS_suite_match,
		VS_class_num,VS_suite_class_num,VS_spec_class_num,
		VS_brand_num,VS_spec_brand_num,VS_suite_brand_num,
		
		VS_brand_multiple_num,VS_spec_brand_multiple_num,VS_suite_brand_multiple_num,VS_brand_mutiple,
		VS_class_multiple_num,VS_spec_class_multiple_num,VS_suite_class_multiple_num,VS_class_mutiple,
		
		VS_specify_mutiple,VS_real_multiple,VS_real_limit,VS_priority,
		
		VS_keyword_len,VS_begin ,VS_end,VS_num,VS_total_cs_remark_num,VS_total_name_num,
		VS_real_gift_num,VS_rec_id,V_Exists,V_First,VS_cur_count,VS_cur_rule,VS_receivable_mutiple INT DEFAULT(0) ;
	
	DECLARE VS_pos TINYINT DEFAULT(1);
	DECLARE V_ApiTradeID BIGINT DEFAULT(0);
	
	DECLARE 
		VS_class_amount,VS_suite_class_amount,VS_spec_class_amount,
		VS_brand_amount,VS_spec_brand_amount,VS_suite_brand_amount,VS_post_cost
		DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VS_type,VT_delivery_term TINYINT DEFAULT(1);
	
	
	/*子订单变量*/
	DECLARE VTO_spec_id,VTO_suite_id,VTO_num,VTO_suite_num,VTO_share_amount INT ;
	DECLARE VT_trade_no,VTO_goods_name,VTO_spec_name VARCHAR(150) DEFAULT('');
	
	/*订单变量*/
	DECLARE VT_shop_id,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,VT_remark_flag,
		VT_receiver_province,VT_receiver_city,VT_receiver_district INT ;
	
	DECLARE VS_NOW,VT_trade_time,VT_pay_time,V_start_time,V_end_time DATETIME;
	DECLARE VT_goods_amount,VT_post_amount,VT_discount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VT_buyer_message,VT_cs_remark,V_ClassPath,VT_receiver_address VARCHAR(1024);
	
	/*规则列表变量*/
	DECLARE V_rule_type BIGINT DEFAULT(0) ;
	
	DECLARE V_send_spec_id,V_send_is_suite,V_send_gift_num INT DEFAULT(0);
	
	DECLARE V_rule_id,V_rule_priority,V_rule_group,V_rule_multiple_type,
		V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
		V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
		V_limit_customer_send_count,V_cur_gift_send_count,V_max_gift_send_count,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_breceivable_multiple,V_limit_receivable_count INT;
		
	DECLARE V_bbuyer_message,V_bcs_remark,V_time_type,V_is_enough_gift TINYINT;
	DECLARE V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,
		V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
		V_min_class_amount,V_max_class_amount,V_min_brand_amount,V_max_brand_amount,V_limit_gift_stock,V_receivable_multiple_amount DECIMAL(19, 4) DEFAULT(0.0000);
	DECLARE V_class_amount_type,V_brand_amount_type,V_terminal_type INT;
	DECLARE V_rule_no,V_rule_name,V_flag_type,V_shop_list,V_logistics_list,V_warehouse_list,V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,
		V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_buyer_message_key_word,V_addr_key_word VARCHAR(150);
	
	-- 赠品规则
	DECLARE rule_cursor CURSOR FOR SELECT  rec_id,rule_no,rule_name,rule_priority,rule_group,is_enough_gift,limit_gift_stock,rule_multiple_type,rule_type,bbuyer_message,bcs_remark,flag_type,time_type,start_time,end_time,shop_list,logistics_list,warehouse_list,
		min_goods_count,max_goods_count,min_goods_type_count,max_goods_type_count,min_specify_count,max_specify_count,min_class_count,max_class_count,class_count_type,min_brand_count,max_brand_count,brand_count_type,
		specify_count,bspecify_multiple,limit_specify_count,class_multiple_count,bclass_multiple,limit_class_count,class_multiple_type,brand_multiple_count,bbrand_multiple,limit_brand_count,brand_multiple_type,
		min_goods_amount,max_goods_amount,min_receivable,max_receivable,min_nopost_receivable,max_nopost_receivable,min_post_amount,max_post_amount,min_weight,max_weight,min_post_cost,max_post_cost,min_specify_amount,max_specify_amount,
		min_class_amount,max_class_amount,class_amount_type,min_brand_amount,max_brand_amount,brand_amount_type,
		buyer_rank,pay_start_time,pay_end_time,trade_start_time,trade_end_time,terminal_type,
		goods_key_word,spec_key_word,csremark_key_word,unit_key_word,limit_customer_send_count,cur_gift_send_count,max_gift_send_count,
		buyer_message_key_word,addr_key_word,min_no_specify_count,max_no_specify_count,buyer_class,receivable_multiple_amount,breceivable_multiple,limit_receivable_count  
		FROM  cfg_gift_rule rule 
		WHERE rule.is_disabled=0 ORDER BY rule_group,rule_priority desc;
	
	-- 子订单信息(单品)
	DECLARE trade_order_cursor1 CURSOR FOR SELECT spec_id,actual_num,share_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 and sto.suite_id=0;
	
	-- 子订单信息(组合装)
	DECLARE trade_order_cursor2 CURSOR FOR SELECT suite_id,suite_num,suite_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 AND sto.suite_id>0 group by sto.suite_id;
	
	
	-- 子订单名称信息(组合装名称只取一次)
	DECLARE trade_order_name_cursor CURSOR FOR SELECT distinct ato.goods_name,ato.spec_name
		FROM api_trade_order ato 
		LEFT JOIN sales_trade_order sto 
		ON (ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid) 
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0;
	
	
	-- 赠品数量范围
	DECLARE send_goods_cursor CURSOR FOR SELECT spec_id,gift_num,is_suite
		FROM cfg_gift_send_goods
		WHERE rule_id=V_rule_id AND priority=VS_priority;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET VS_NOW = NOW();	
		-- 订单信息
	SELECT trade_no,shop_id,trade_time,pay_time,goods_count,goods_type_count,customer_id,warehouse_id,logistics_id,
		receiver_province,receiver_city,receiver_district,buyer_message,cs_remark,remark_flag,
		goods_amount,post_amount,receivable,receivable-post_amount,weight,post_cost,delivery_term,receiver_address
	INTO VT_trade_no,VT_shop_id,VT_trade_time,VT_pay_time,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,
		VT_receiver_province,VT_receiver_city,VT_receiver_district,VT_buyer_message,VT_cs_remark,VT_remark_flag,
		VT_goods_amount,VT_post_amount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost,VT_delivery_term,VT_receiver_address
	FROM  sales_trade st
	WHERE st.trade_id=P_TradeID;
		
	IF V_NOT_FOUND = 1 THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 记录选中的分组
	SET @sys_code=0, @sys_message='OK';
	SET VS_sel_rule_group=-1;
	SET V_First=1;
	
	OPEN rule_cursor;
	GIFT_RULE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		SET VS_total_name_num =0;
		SET VS_total_cs_remark_num =0;
		SET VS_cur_count = 0;
		FETCH rule_cursor INTO V_rule_id,V_rule_no,V_rule_name,V_rule_priority,V_rule_group,V_is_enough_gift,V_limit_gift_stock,V_rule_multiple_type,V_rule_type,V_bbuyer_message,V_bcs_remark,V_flag_type,V_time_type,V_start_time,V_end_time,V_shop_list,V_logistics_list,V_warehouse_list,
			V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
			V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
			V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
			V_min_class_amount,V_max_class_amount,V_class_amount_type,V_min_brand_amount,V_max_brand_amount,V_brand_amount_type,
			V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,V_terminal_type,
			V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_limit_customer_send_count,V_cur_gift_send_count,
			V_max_gift_send_count,V_buyer_message_key_word,V_addr_key_word,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_receivable_multiple_amount,V_breceivable_multiple,V_limit_receivable_count;
		
		IF V_NOT_FOUND <> 0 THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		/*一个分组内只匹配一个赠品规则*/
		IF VS_sel_rule_group !=-1 AND VS_sel_rule_group=V_rule_group THEN
			ITERATE  GIFT_RULE_LABEL;
		END IF;
		
		/*此规则下没有设置赠品*/
		SELECT count(1) INTO VS_rec_id FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;
		
		-- VS_specify_mutiple VS_class_mutiple VS_brand_mutiple 
		-- 都满足的情况下VS_real_multiple来记录最小(大)的倍数关系
		
		IF V_rule_multiple_type=0 THEN 
			SET VS_real_multiple=10000;
			SET VS_real_limit=10000;
		ELSE 
			SET VS_real_multiple=-10000;
			SET VS_real_limit=-10000;	
		END IF;
	
		/*检查该赠品都设置了哪些条件*/
		
		/*检查订单是否满足用户设置的赠品条件*/
		
		/*买家留言*/
		/*IF (V_rule_type & 1) THEN
			IF  V_bbuyer_message THEN 
				IF  VT_buyer_message IS NOT NULL AND  VT_buyer_message<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_buyer_message IS  NULL OR  VT_buyer_message='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		/*客服备注*/
		/*IF V_rule_type & (1<<1) THEN
			IF  V_bcs_remark THEN
				IF  VT_cs_remark IS NOT NULL AND  VT_cs_remark<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_cs_remark IS  NULL OR  VT_cs_remark='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		
		/*淘宝标旗*/
		/*IF V_rule_type & (1<<2) THEN
			IF FIND_IN_SET(VT_remark_flag,V_flag_type)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;*/
		
		/*有效期*/
		IF V_rule_type & (1<<3) THEN
			IF V_time_type=1 AND VT_delivery_term=1 THEN 
				IF VT_pay_time<V_start_time OR VT_pay_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 3 OR VT_delivery_term=2 THEN
				IF VT_trade_time<V_start_time OR VT_trade_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 2 THEN
				IF VS_NOW<V_start_time OR VS_NOW>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;
		
		
		/*店铺*/
		IF V_rule_type & (1<<4) THEN
			IF FIND_IN_SET(VT_shop_id,V_shop_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		
		/*物流公司*/
		/*IF V_rule_type & (1<<5) THEN
			IF FIND_IN_SET(VT_logistics_id,V_logistics_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*仓库*/
		/*IF V_rule_type & (1<<6) THEN
			IF FIND_IN_SET(VT_warehouse_id,V_warehouse_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*货品总数*/
		-- 此处有问题，合并时未刷新货品
		IF V_rule_type & (1<<7) THEN
			IF V_max_goods_count=0 THEN
				IF  VT_goods_count<V_min_goods_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_count<V_min_goods_count OR VT_goods_count>V_max_goods_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
			
		/*货品种类*/
		/*IF V_rule_type & (1<<8) THEN
			IF V_max_goods_type_count=0 THEN
				IF  VT_goods_type_count<V_min_goods_type_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_type_count<V_min_goods_type_count OR VT_goods_type_count>V_max_goods_type_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*货款总额*/
		IF V_rule_type & (1<<15) THEN
			IF V_max_goods_amount=0 THEN
				IF  VT_goods_amount<V_min_goods_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_amount<V_min_goods_amount OR VT_goods_amount>V_max_goods_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
		/*实收(包含邮费)*/
		IF V_rule_type & (1<<16) THEN
			IF V_max_receivable=0 THEN
				IF  VT_receivable<V_min_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_receivable<V_min_receivable OR VT_receivable>V_max_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
			
		/*实收(去除邮费)*/
		/*IF V_rule_type & (1<<17) THEN
			IF V_max_nopost_receivable=0 THEN
				IF  VT_nopost_receivable<V_min_nopost_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_nopost_receivable<V_min_nopost_receivable OR VT_nopost_receivable>V_max_nopost_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*邮费*/
		/*IF V_rule_type & (1<<18) THEN
			IF V_max_post_amount=0 THEN
				IF  VT_post_amount<V_min_post_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VT_post_amount<V_min_post_amount OR VT_post_amount>V_max_post_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*预估重量*/
		/*IF V_rule_type & (1<<19) THEN
			IF V_max_weight=0 THEN
				IF  VT_weight<V_min_weight THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_weight<V_min_weight OR VT_weight>V_max_weight THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
			
		/*预估邮费成本*/
		/*IF V_rule_type & (1<<20) THEN
			IF V_max_post_cost=0 THEN
				IF  VT_post_cost<V_min_post_cost THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_post_cost<V_min_post_cost OR VT_post_cost>V_max_post_cost THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*客服备注关键字*/
		/*IF V_rule_type & (1<<30) THEN
			IF (VT_cs_remark IS NULL OR VT_cs_remark='') THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_csremark_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			ELSE 
				IF NOT LOCATE(V_csremark_key_word, VT_cs_remark) THEN
				-- IF (SELECT VT_cs_remark NOT LIKE CONCAT_WS('','%',V_csremark_key_word,'%')) THEN 
					ITERATE GIFT_RULE_LABEL;
				END IF;
			END IF;*/
			
			/*客服备注：AAA1支 2支BBB 1支AAA*/
			
			/*SET VS_keyword_len = CHARACTER_LENGTH(V_csremark_key_word);
			SET VS_pos = 1;
			SET VS_num=0;
			SET VS_total_cs_remark_num=0;
			SET VS_begin=0;
			SET VS_end=0;
			
			CS_REMARK_KEYWORD_LABEL:LOOP
				SET VS_begin = LOCATE(V_csremark_key_word, VT_cs_remark, VS_pos);
				IF VS_begin = 0 THEN
					LEAVE CS_REMARK_KEYWORD_LABEL;
				END IF;
				
				IF V_unit_key_word<>'' THEN 
					SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin - 1);
					IF VS_end > 0 AND VS_begin >VS_end THEN
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 2, 2));
						IF VS_num = 0 THEN
							SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 1, 1));
						END IF;
						
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos =VS_keyword_len+VS_begin;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					ELSE
						SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin);
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_begin + VS_keyword_len, VS_end - VS_begin - VS_keyword_len));
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos = VS_end;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					END IF;
				ELSE
					SET VS_total_cs_remark_num=VS_total_cs_remark_num+1;
					LEAVE CS_REMARK_KEYWORD_LABEL;	
				END IF;
			END LOOP; -- CS_REMARK_KEYWORD_LABEL
		END IF;*/
		
		
		/*
		指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		IF V_rule_type & (1<<9) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count AND tgto.num<=V_max_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类数量范围 注意组合装*/
		/*IF V_rule_type & (1<<10) THEN
			-- 未指定分类
			if V_class_count_type=0 then 
				ITERATE  GIFT_RULE_LABEL;
			end if;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_count_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_class_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_num=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_count=0 THEN
				IF  VS_class_num<V_min_class_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_num<V_min_class_count OR VS_class_num>V_max_class_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌数量范围 注意组合装*/
		
		/*IF V_rule_type & (1<<11) THEN
			-- 未指定品牌
			IF V_brand_count_type=0  THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_brand_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_count_type;
			
			IF VS_brand_num=0 THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_count=0 THEN
				IF  VS_brand_num<V_min_brand_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_brand_num<V_min_brand_count OR VS_brand_num>V_max_brand_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*
		指定货品数量备增 
		cfg_gift_attend_goods goods_type=2记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		如果是倍增关系需要计算出来倍数关系用于I_DL_SELECT_GIFT计算库存
		*/
		/*IF V_rule_type & (1<<12) THEN
			-- 参加活动的单品集合为空
			IF V_specify_count<=0 OR NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=2) THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(tgto.num),0) INTO VTO_num
			FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
			WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=2;
			
			SET VS_specify_mutiple=FLOOR(VTO_num/V_specify_count);
			IF VS_specify_mutiple =0 OR VS_specify_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bspecify_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
			
			
		/*指定分类数量倍增*/
		/*IF V_rule_type & (1<<13) THEN
			-- 未指定分类
			IF V_class_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_multiple_type;
			IF V_NOT_FOUND THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			SET VS_class_mutiple=FLOOR(VTO_num/V_class_multiple_count);
			IF VS_class_mutiple =0 OR VS_class_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bclass_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
		-- VS_class_mutiple,V_limit_class_count 传递给I_DL_SELECT_GIFT		
		
		
		
		/*指定品牌数量倍增*/
		/*IF V_rule_type & (1<<14) THEN
			-- 未指定品牌
			IF V_brand_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_multiple_type;
			
			SET VS_brand_mutiple=FLOOR(VTO_num/V_brand_multiple_count);
			IF VS_brand_mutiple =0 OR VS_brand_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bbrand_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
			
		-- VS_brand_mutiple,V_limit_brand_count 传递给I_DL_SELECT_GIFT
		
		/*
		指定货品金额范围 
		cfg_gift_attend_goods goods_type=3记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		组合装金额suie_amount 
		*/
		
		IF V_rule_type & (1<<21) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=3) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_amount=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount AND tgto.amount<=V_max_specify_amount LIMIT 1;
			END IF;
			
			-- 无满足条件的
			IF V_Exists =0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类金额范围 注意组合装*/
		/*IF V_rule_type & (1<<22) THEN
			-- 未指定分类
			IF V_class_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_amount_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_class_amount
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_amount=0 THEN
				IF  VS_class_amount<V_min_class_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_amount<V_min_class_amount OR VS_class_amount>V_max_class_amount THEN  
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌金额范围 注意组合装*/
		/*IF V_rule_type & (1<<23) THEN
			-- 未指定品牌
			IF V_brand_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_brand_amount
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_amount_type;
			
			IF VS_brand_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_amount=0 THEN
				IF  VS_brand_amount<V_min_brand_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VS_brand_amount<V_min_brand_amount OR VS_brand_amount>V_max_brand_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*客户地区*/
		/*IF V_rule_type & (1<<24) THEN
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_buyer_area WHERE rule_id=V_rule_id AND province_id=VT_receiver_province AND city_id=VT_receiver_city) THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		
		/*客户等级V_buyer_rank fixme P_CustomerID*/
		-- ELSEIF VS_type = 26 THEN
		-- ITERATE  GIFT_RULE_LABEL;
		
		/*付款时间*/
		/*IF V_rule_type & (1<<26) THEN 
			IF (DATE_FORMAT(VT_pay_time,'%H:%i:%s')<V_pay_start_time OR DATE_FORMAT(VT_pay_time,'%H:%i:%s')>V_pay_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*拍单时间*/
		/*IF V_rule_type & (1<<27)  THEN 
			IF (DATE_FORMAT(VT_trade_time,'%H:%i:%s')<V_trade_start_time OR DATE_FORMAT(VT_trade_time,'%H:%i:%s')>V_trade_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*终端类型*/
		/*IF V_rule_type & (1<<28)  THEN
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			IF V_terminal_type=2 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&1)=0) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_terminal_type=1 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&2)) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*宝贝关键字*/
		/*IF V_rule_type & (1<<29) THEN
			IF V_goods_key_word=''AND V_spec_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_goods_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.goods_name 
									LIKE CONCAT_WS('','%',V_goods_key_word,'%')
									) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_spec_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.spec_name 
									LIKE CONCAT_WS('','%',V_spec_key_word,'%')
								) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET VS_total_name_num=0;
			OPEN trade_order_name_cursor;
			NAME_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_name_cursor INTO VTO_goods_name,VTO_spec_name;
					IF V_NOT_FOUND <> 0 THEN
						LEAVE NAME_LABEL;
					END IF;
					
					IF V_goods_key_word<>'' AND V_spec_key_word='' AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word='' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word<>'' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
				END LOOP; -- NAME_LABEL
			CLOSE trade_order_name_cursor;
		END IF;*/

		/*指定赠送次数(适用于前多少名的赠送方式)*/
		/*IF V_rule_type & (1<<31) THEN
			IF V_max_gift_send_count AND V_cur_gift_send_count>=V_max_gift_send_count THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定赠品根据客户限送次数*/
		/*IF V_rule_type & (1<<32) THEN
			IF V_limit_customer_send_count THEN
				SELECT COUNT(1) INTO VS_cur_count FROM sales_gift_record  WHERE rule_id = V_rule_id AND customer_id = P_CustomerID AND created>=V_start_time AND created<=V_end_time;
				IF VS_cur_count >= V_limit_customer_send_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*指定买家留言关键词*/
		/*IF V_rule_type & (1<<33) THEN
			IF V_buyer_message_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_buyer_message = ''  OR VT_buyer_message IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_buyer_message_key_word, VT_buyer_message) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定收件人地址关键词*/
		/*IF V_rule_type & (1<<34) THEN
			IF V_addr_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_receiver_address = ''  OR VT_receiver_address IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_addr_key_word, VT_receiver_address) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		/*
		不送指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		/*IF V_rule_type & (1<<35) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_no_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count AND tgto.num<=V_max_no_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*客户分组送赠品*/
		/*IF V_rule_type & (1<<36) THEN
			IF  V_buyer_class = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT EXISTS(SELECT 1 FROM crm_customer WHERE customer_id = P_CustomerID AND class_id = V_buyer_class) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*订单实收(不包含邮费)倍增*/
		/*IF V_rule_type & (1<<37) THEN
			-- 查看
			IF V_receivable_multiple_amount = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET VS_receivable_mutiple=FLOOR(VT_nopost_receivable/V_receivable_multiple_amount);
			IF VS_receivable_mutiple =0 OR VS_receivable_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_breceivable_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
		
		-- citying

		/*订单满足赠品条件 根据优先级和库存确定赠品即VS_priority (倍增条件下考虑翻倍数量,数量从客服备注提取的情况下考虑库存)*/
		set V_NOT_FOUND=0;
		set VS_rec_id=0;

		SELECT COUNT(DISTINCT priority),IFNULL(priority,11)
		INTO VS_rec_id,VS_priority 
		FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;

		/*如果开启校验赠品库存,则都要去校验库存,否则的话则多个赠品列表的才去计算优先级*/
		IF  V_is_enough_gift THEN
			SET  VS_priority=11;
			CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,V_limit_gift_stock);
			-- IF VS_priority = 99 THEN
			IF VS_priority > 11 THEN -- 赠品库存数量不足时 VS_priority++ 目前没有做赠品优先级,只要有一个赠品不满足即不赠送货品
				SET  VS_priority=11;
				ITERATE GIFT_RULE_LABEL;
			END IF;
		/*ELSE
			--  指定多个赠品列表的情况下才去按库存计算优先级
			IF VS_rec_id>1 THEN 
				SET  VS_priority=11;
				CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,0);
				IF VS_priority = 99 THEN
					SET VS_priority = 11;
				END IF;
			END IF;*/
		END IF;
		
		/*添加赠品*/
		OPEN send_goods_cursor;
		SEND_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH send_goods_cursor INTO V_send_spec_id,V_send_gift_num,V_send_is_suite;
			-- 设置了规则 却没有设置赠品
			IF V_NOT_FOUND <> 0 THEN
				CLOSE send_goods_cursor;
				ITERATE GIFT_RULE_LABEL;
			END IF;

			-- 目前只按照赠品数量计算(没有客服备注提取、宝贝关键字计算、倍增)
			SET VS_real_gift_num=V_send_gift_num;
			
			-- 客服备注的优先级最高 名称提取其次
			-- VS_real_gift_num 是真正的赠送数量
			/*SET VS_real_gift_num=0;
			
			IF VS_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=VS_total_cs_remark_num;
				
			ELSEIF VS_total_name_num>0 THEN 
				SET VS_real_gift_num=VS_total_name_num;
				
			ELSE
				-- 有倍增关系 看是否大于VS_real_limit
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			CALL I_SALES_ORDER_INSERT(P_OperatorID, P_TradeID, 
				V_send_is_suite, V_send_spec_id, 1, VS_real_gift_num, 0, 0, 
				'自动赠品',
				CONCAT_WS ('',"自动添加赠品。使用策略编号：",V_rule_no,"策略名称：",V_rule_name), 
				V_ApiTradeID);
			
			-- 失败日志
			IF @sys_code THEN
				-- 回滚事务,否则下面日志无法保存
				ROLLBACK;
				-- 停用此赠品策略
				UPDATE cfg_gift_rule SET is_disabled=1 WHERE rec_id=V_rule_id;
				
				INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
				VALUES(60,P_TradeID,0,P_OperatorID,CONCAT('自动赠送失败,策略编号:', V_rule_no, ' 错误:', @sys_message),NOW());	
				
				INSERT INTO aux_notification(type,message,priority,order_type,order_no)
				VALUES(2,CONCAT('赠品策略异常: ', V_rule_no, ' 错误:', @sys_message, ' 订单:',VT_trade_no, ' 系统已自动停用此策略'), 
					9, 1, VT_trade_no);
				
				LEAVE SEND_GOODS_LABEL;
			ELSE
				IF VS_cur_rule <> V_rule_id THEN
					UPDATE cfg_gift_rule SET history_gift_send_count = history_gift_send_count +1,cur_gift_send_count = cur_gift_send_count +1
					WHERE rec_id=V_rule_id;
					INSERT INTO sales_gift_record(rule_id,trade_id,customer_id,created)
					values(V_rule_id,P_TradeID,VT_customer_id,NOW());
					SET VS_cur_rule = V_rule_id;
				END IF;
				SET P_SendOK=1;
			END IF;
			SET VS_sel_rule_group = V_rule_group;
		END LOOP; -- SEND_GOODS_LABEL
		CLOSE send_goods_cursor;
		
		IF @sys_code THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		
	END LOOP; -- GIFT_RULE_LABEL
	CLOSE rule_cursor;
	
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `I_DL_SYNC_MAIN_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_MAIN_ORDER`(IN `P_OperatorID` INT, IN `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ModifyFlag,V_DeliverTradeID,V_WarehouseID,
		V_NewWarehouseID,V_Locked,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsType,
		V_SalesOrderCount,V_Timestamp,V_DelayToTime,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,
		V_CustomerID,V_FlagID,V_IsMaster,V_RemarkFlag,V_Exists,
		V_ShopHoldEnabled,V_OldFreeze,V_PackageID,V_RemarkCount,V_GiftMask,V_UnmergeMask,
		V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_ApiGoodsCount,V_ApiOrderCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,
		V_DapAmount,V_CodAmount,V_PiAmount,
		V_Paid,V_SalesGoodsCount,V_TotalWeight,V_PostCost,
		V_GoodsCost,V_ExtCodFee,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_HasSendGoods,V_HasGift,V_PlatformID,V_ApiTradeStatus,V_TradeStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_InvoiceType,V_WmsType,V_NewWmsType,V_IsAutoWms,V_IsSealed,V_IsFreezed,V_IsPreorder,V_IsExternal TINYINT DEFAULT(0);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverRing VARCHAR(40);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	
	DECLARE V_SalesmanID,V_LogisticsID,V_TradeMask,V_OldLogisticsID INT;
	DECLARE V_Tid,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_ExtMsg,V_SingleSpecNO VARCHAR(40);
	DECLARE V_AreaAlias,V_BuyerEmail,V_BuyerNick,V_ReceiverName,V_ReceiverArea VARCHAR(60);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime DATETIME;
	DECLARE V_Remark,V_BuyerMessage VARCHAR(1024);
	
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
	START TRANSACTION;
	
	SELECT modify_flag,platform_id,tid,trade_status,refund_status,delivery_term,guarantee_mode,deliver_trade_id,pay_time,pay_account,
		receivable,goods_amount,post_amount,other_amount,dap_amount,cod_amount,pi_amount,ext_cod_fee,paid,discount,invoice_type,
		invoice_title,invoice_content,stockout_no,trade_mask,is_sealed,wms_type,is_auto_wms,warehouse_no,shop_id,logistics_type,
		buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_area,receiver_ring,receiver_address,
		receiver_zip,receiver_telno,receiver_mobile,remark_flag,remark,buyer_message,is_external
	INTO V_ModifyFlag,V_PlatformID,V_Tid,V_ApiTradeStatus,V_RefundStatus,V_DeliveryTerm,V_GuaranteeMode,V_DeliverTradeID,V_PayTime,V_PayAccount,
		V_Receivable,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_Discount,V_InvoiceType,
		V_InvoiceTitle,V_InvoiceContent,V_StockoutNO,V_TradeMask,V_IsSealed,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_ShopID,V_LogisticsType,
		V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverArea,V_ReceiverRing,V_ReceiverAddress,
		V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_RemarkFlag,V_Remark,V_BuyerMessage,V_IsExternal
	FROM api_trade WHERE rec_id=P_ApiTradeID FOR UPDATE;
	
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
					-- 记录备注
					INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
				
				IF V_IsPreorder THEN
					SET V_ExtMsg = ' 进预订单原因:客服备注提取';	
				END IF;
				
				-- 客户备注
				SET V_BuyerMessage=TRIM(V_BuyerMessage);
				CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
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
				
				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
				INTO V_Max,V_Min
				FROM tmp_sales_trade_order;
				
				-- 更新主订单退款状态
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4);
					SET V_TradeStatus=5;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
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
					SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
					FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
				ELSE
					SET V_SingleSpecNO='';
				END IF;
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
				IF V_NewWarehouseID = 0 THEN
					SELECT warehouse_id INTO V_NewWarehouseID FROM cfg_warehouse where is_disabled = 0 limit 1;
				END IF;
				-- 获取物流
				IF V_LogisticsID = 0 THEN
						IF V_DeliveryTerm=2 THEN
							SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						ELSE 
							SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						END IF;
				END IF;
				-- 估算货品成本
				SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_NewWarehouseID AND ss.spec_id=tsto.spec_id
				WHERE tsto.actual_num>0;
				SET V_AreaAlias = '';
				-- 更新订单
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
					logistics_id=V_LogisticsID,receiver_dtb=V_AreaAlias,flag_id=V_FlagID,salesman_id=V_SalesmanID,
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
					SELECT V_DeliverTradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,5,CONCAT('首付款:',V_Tid));
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
							
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(IF(V_ApiTradeStatus=80,'订单退款','订单关闭'),',拦截出库单'));
						
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
							-- 标记退款
							UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
			
			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid;
			UPDATE api_trade SET modify_flag=0,process_status=70 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=V_ShopID AND src_tid=V_Tid;
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
		
		-- 提取业务员
		CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
		IF V_SalesmanID THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				-- 加异常标记
				UPDATE sales_trade SET bad_reason=(bad_reason|16) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
			ELSEIF V_TradeStatus >= 40 AND V_TradeStatus < 95 AND @cfg_remark_change_block_stockout THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|64) WHERE stockout_id=V_StockoutID;
						-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'客服备注变化,拦截出库单');
				END IF;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT('客服备注变化,拦截出库:',V_Tid));
			ELSEIF V_TradeStatus >= 95 AND @cfg_remark_change_block_stockout THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化,订单已发货:',V_Tid));
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
			
			-- 看地址是否有变化
			IF EXISTS(SELECT 1 FROM sales_trade st,api_trade ax
				WHERE st.trade_id=V_DeliverTradeID AND ax.platform_id=V_PlatformID AND ax.tid=V_Tid
					AND st.receiver_name=ax.receiver_name
					AND st.receiver_province=ax.receiver_province
					AND st.receiver_city=ax.receiver_city
					AND st.receiver_district=ax.receiver_district
					AND st.receiver_address=ax.receiver_address
					AND st.receiver_mobile=ax.receiver_mobile
					AND st.receiver_telno=ax.receiver_telno
					AND st.receiver_zip=ax.receiver_zip
					AND st.receiver_area=ax.receiver_area
					AND st.receiver_ring=ax.receiver_ring
					AND st.to_deliver_time=ax.to_deliver_time
					AND st.dist_center=ax.dist_center
					AND st.dist_site=ax.dist_site) THEN
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('平台收件地址变更,系统已处理:',V_Tid));
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|4) WHERE stockout_id=V_StockoutID;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'收件地址变更,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
				
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更:',V_Tid));
				
				-- 刷新物流,大头笔,包装
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|3, 0);
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|2) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
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
			
			-- 拦截出库单
			IF V_TradeStatus>=40 THEN
				UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
				SET so.block_reason=(so.block_reason|8)
				WHERE sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id
					AND so.stockout_id=sod.stockout_id
					AND sto.trade_id=V_DeliverTradeID
					AND so.status<>5;
					
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
				-- 出库单日志??
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			ELSEIF V_TradeStatus<35 THEN
				UPDATE sales_trade st,api_trade ax 
				SET st.invoice_type=ax.invoice_type,
					st.invoice_title=ax.invoice_title,
					st.invoice_content=ax.invoice_content,
					st.version_id=st.version_id+1
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化:',V_Tid));
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
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
	UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SYNC_SUB_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_SUB_ORDER`(IN `P_OperatorID` INT,
	IN `P_RecID` BIGINT,
	IN `P_ModifyFlag` INT,
	IN `P_ApiTradeStatus` TINYINT,
	IN `P_ShopID` TINYINT,
	IN `P_Tid` VARCHAR(40),
	IN `P_Oid` VARCHAR(40),
	IN `P_RefundStatus` TINYINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_DeliverTradeID,V_WarehouseID,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,V_IsMaster,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_SalesGoodsCount,V_LeftSharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_HasSendGoods,V_TradeStatus TINYINT DEFAULT(0);
	
	DECLARE trade_order_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id
		FROM sales_trade_order sto LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=P_ShopID and sto.src_oid=P_Oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,'子订单退款,拦截出库单');
						END IF;
						
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
						
						UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
				
				-- 回收库存
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
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('子订单退款:',P_Oid));
				ELSE
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid));
				END IF;
				
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, V_IsMaster, 1, V_LeftSharePost);
		ELSEIF P_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=P_ShopID AND src_oid=P_Oid;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					SET V_NewRefundStatus=IF(V_Max,3,4);
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				UPDATE sales_trade SET refund_status=V_NewRefundStatus,version_id=version_id+1 WHERE trade_id=V_DeliverTradeID;
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
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, 2, 0);
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'平台修改货品,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
			END IF;
		
			UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid));
			
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		SET P_ModifyFlag = P_ModifyFlag & ~16;
	END IF;
	
	UPDATE api_trade_order SET modify_flag=0 WHERE rec_id=P_RecID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_GIFT_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_GIFT_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '新建订单货品插入的临时表,为赠品准备'
MAIN_LABEL:BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_gift_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  is_suite INT(11) NOT NULL,
	  spec_id INT(11) NOT NULL,
	  num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  class_path VARCHAR(1024) NOT NULL,
	  brand_id INT(11) NOT NULL,
	  from_mask INT(11) NOT NULL,
	  PRIMARY KEY (rec_id),
	  UNIQUE INDEX UK_tmp_gift_trade_order (is_suite, spec_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_SALES_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_SALES_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中建立的临时表'
MAIN_LABEL: BEGIN 
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sales_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  spec_id INT(11) NOT NULL,
	  shop_id smallint(6) NOT NULL,
	  platform_id tinyint(4) NOT NULL,
	  src_oid VARCHAR(40) NOT NULL,
	  suite_id INT(11) NOT NULL DEFAULT 0,
	  src_tid VARCHAR(40) NOT NULL,
	  gift_type TINYINT(1) NOT NULL DEFAULT 0,
	  refund_status TINYINT(4) NOT NULL DEFAULT 0,
	  guarantee_mode TINYINT(4) NOT NULL DEFAULT 1,
	  delivery_term TINYINT(4) NOT NULL DEFAULT 1,
	  bind_oid VARCHAR(40) NOT NULL DEFAULT '',
	  num DECIMAL(19, 4) NOT NULL,
	  price DECIMAL(19, 4) NOT NULL,
	  actual_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  order_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  adjust DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_post DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  paid DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  tax_rate DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
	  goods_name VARCHAR(255) NOT NULL,
	  goods_id INT(11) NOT NULL,
	  goods_no VARCHAR(40) NOT NULL,
	  spec_name VARCHAR(100) NOT NULL,
	  spec_no VARCHAR(40) NOT NULL,
	  spec_code VARCHAR(40) NOT NULL,
	  suite_no VARCHAR(40) NOT NULL DEFAULT '',
	  suite_name VARCHAR(255) NOT NULL DEFAULT '',
	  suite_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  is_print_suite TINYINT(1) NOT NULL DEFAULT 0,
	  api_goods_name VARCHAR(255) NOT NULL,
	  api_spec_name VARCHAR(40) NOT NULL,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  volume DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  commission DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  goods_type TINYINT(4) NOT NULL DEFAULT 1,
	  flag INT(11) NOT NULL DEFAULT 0,
	  large_type TINYINT(1) NOT NULL DEFAULT 0,
	  invoice_type TINYINT(4) NOT NULL DEFAULT 0,
	  invoice_content VARCHAR(255) NOT NULL DEFAULT '',
	  from_mask INT(11) NOT NULL DEFAULT 0,
	  cid INT(11) NOT NULL DEFAULT 0,
	  is_master TINYINT(1) NOT NULL DEFAULT 0,
	  is_allow_zero_cost TINYINT(1) NOT NULL DEFAULT 0,
	  remark VARCHAR(60) NOT NULL DEFAULT '',
	  PRIMARY KEY (rec_id),
	  INDEX IX_tmp_sales_trade_order_src_id (shop_id, src_oid),
	  UNIQUE INDEX UK_tmp_sales_trade_order (spec_id, shop_id, src_oid, suite_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_RESERVE_STOCK`;
DELIMITER //
CREATE PROCEDURE `I_RESERVE_STOCK`(IN `P_TradeID` INT, IN `P_Type` INT, IN `P_NewWarehouseID` INT, IN `P_OldWarehouseID` INT)
    SQL SECURITY INVOKER
    COMMENT '占用库存'
MAIN_LABEL:BEGIN
	IF P_OldWarehouseID THEN
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT P_OldWarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
			IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
			sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
		
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=P_TradeID;
	END IF;
	IF P_NewWarehouseID THEN
		IF P_Type = 2 THEN	-- 未付款库存
			INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num);
			
			UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 3 THEN	-- 已保留待审核
			INSERT INTO stock_spec(warehouse_id,spec_id,order_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num 
			FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num);
			
			UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 4 THEN	-- 待发货
			INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status)
			(SELECT P_NewWarehouseID,spec_id,actual_num,1 FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1;
			
			UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 5 THEN	-- 预订单库存
			INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num);
			
			UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		END IF;
	END IF;
	
	-- 更新平台货品库存变化
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0;
	
	-- 组合装
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
	
END//
DELIMITER ;

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
			LEFT JOIN api_trade_order ato ON ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid
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
	IF (V_TradePlatformId<>1 AND V_TradePlatformId<>2) OR @cfg_order_allow_part_sync=0 THEN
	
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

DROP PROCEDURE IF EXISTS `I_SALES_ORDER_INSERT`;
DELIMITER //
CREATE PROCEDURE `I_SALES_ORDER_INSERT`(
	IN `P_OperatorID` INT, 
	IN `P_TradeID` INT, 
	IN `P_bSuite` INT,
	IN `P_SpecID` INT,
	IN `P_GiftType` INT,
	IN `P_Num` DECIMAL(19,4),
	IN `P_ShareAmount` DECIMAL(19,4),
	IN `P_SharePost` DECIMAL(19,4),
	IN `P_GoodsRemark` VARCHAR(255),
	IN `P_Remark` VARCHAR(255),
	INOUT `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
    COMMENT '插入货品作为一个子订单'
MAIN_LABEL: BEGIN
	DECLARE V_Receivable,V_GoodsAmount,V_ApiGoodsCount DECIMAL(19,4);
	DECLARE V_PayStatus TINYINT DEFAULT(0);
	DECLARE V_Message VARCHAR(256);
	DECLARE V_OrderID,V_ShopID,V_ApiOrderCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Tid VARCHAR(40);
	DECLARE V_Now DATETIME;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 展开货品
	SET @sys_code=0;
	SET @sys_message="";
	
	IF P_GiftType THEN
		SET P_ShareAmount=0;
	END IF;
	
	SET V_Receivable=P_ShareAmount+P_SharePost;
	-- 插入原始单	
	-- IF P_Paid >= V_Receivable THEN
	-- 	SET V_PayStatus = 2; -- 已付款
	-- ELSEIF P_Paid=0 THEN
	-- 	SET V_PayStatus = 0; -- 未付款
	-- ELSE
	-- 	SET V_PayStatus = 1; -- 部分付款
	-- END IF;
	
	
	-- START TRANSACTION;
	SET V_Now = NOW();
	-- 查找是否存在一个手工建单
	IF P_ApiTradeID=0 THEN
		SELECT ax.rec_id,ax.tid INTO P_ApiTradeID,V_Tid  
		FROM sales_trade_order sto,api_trade ax 
		WHERE sto.trade_id=P_TradeID AND sto.platform_id=0 AND ax.platform_id=0 AND ax.tid=sto.src_tid AND V_Tid IS NOT NULL AND V_Tid<>'' LIMIT 1;
	END IF;
	
	-- 没找到，则手工新建一个
	IF P_ApiTradeID=0 THEN
		SET V_Tid = FN_SYS_NO("apitrade");
		
		INSERT INTO api_trade(platform_id, shop_id, tid, process_status, trade_status, guarantee_mode, pay_status, delivery_term, pay_method,
			order_count, goods_count, trade_time, pay_time,
			buyer_nick, buyer_name, buyer_area, pay_id, 
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address, 
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, receiver_hash,
			goods_amount, post_amount, discount, receivable, paid, received, 
			invoice_type, invoice_title, invoice_content, trade_from,created)
		SELECT 0, shop_id, V_Tid, 20, 30, 2, 2, 1, 1, 
			1, P_Num,  V_Now,  V_Now,
			buyer_nick, receiver_name, receiver_area, '',
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address,
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, 
			MD5(CONCAT(receiver_province,receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip)),
			0, 0, 0, 0, 0, 0,
			0, '', '', 2,V_Now 
		FROM  sales_trade
		WHERE trade_id=P_TradeID LIMIT 1;
		
		SET P_ApiTradeID = LAST_INSERT_ID();
	ELSE
		SELECT tid INTO V_Tid FROM api_trade WHERE rec_id=P_ApiTradeID;
	END IF;
	
	SELECT shop_id INTO V_ShopID FROM api_trade WHERE rec_id=P_ApiTradeID;
	-- 补原始子订单数据 
	IF P_bSuite=0 THEN
		SET @tmp_specno='',@tmp_goodsname='',@tmp_specname='';
		
		INSERT INTO api_trade_order(platform_id,shop_id, tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount,share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gg.goods_id,gg.goods_no,gs.spec_id, (@tmp_specno:=gs.spec_no),(@tmp_goodsname:=gg.goods_name),(@tmp_specname:=gs.spec_name),gs.spec_code,
			P_GiftType,P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,
			P_ShareAmount,P_SharePost,0,P_GoodsRemark,V_Now 
		FROM  goods_spec gs 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gs.spec_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='货品不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加单品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		END IF;
		
	ELSE 
		INSERT INTO api_trade_order(platform_id,shop_id , tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount, share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gs.suite_id,gs.suite_no,gs.suite_id,(@tmp_specno:=gs.suite_no),(@tmp_goodsname:=gs.suite_name),'','', P_GiftType,
			P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,P_ShareAmount,P_SharePost, 0, P_GoodsRemark, V_Now 
		FROM  goods_suite gs 
		WHERE gs.suite_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='组合装不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加货品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		END IF;
	END IF;
	
	-- 映射货品
	CALL I_DL_MAP_TRADE_GOODS(P_TradeID, P_ApiTradeID, 0, V_ApiOrderCount, V_ApiGoodsCount);
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*IF V_ApiOrderCount <> 1 OR V_ApiGoodsCount <> P_Num THEN
		SET @sys_code=7, @sys_message = '单品数量不一致';
		LEAVE MAIN_LABEL;
	END IF;*/
	
	UPDATE api_trade_order SET process_status=20 WHERE rec_id=V_OrderID;
	
	-- 日志
	INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
	VALUES(60,P_TradeID,P_SpecID,P_OperatorID,V_Message,V_Now);	
	
	-- 更新原始单金额数据
	UPDATE api_trade `at`,
		(
			SELECT SUM(share_amount+discount) goods_amount,
				SUM(share_post) post_amount,SUM(discount) discount
			FROM api_trade_order ato 
			WHERE platform_id=0 AND tid=V_Tid
		) da	
	SET 
		`at`.goods_amount =da.goods_amount,
		`at`.post_amount =da.post_amount,
		`at`.discount =da.discount,
		`at`.receivable=V_Receivable,
		`at`.modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	END IF;
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		select @cfg_sales_trade_trace_operator;
		IF @cfg_sales_trade_trace_operator THEN
			select @cur_uid;
			SELECT fullname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		select V_Operator;
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	END IF;
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		IF @cfg_sales_trade_trace_operator THEN
			SELECT fullname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_IMPLEMENT_CLEAN`;
DELIMITER //
CREATE PROCEDURE SP_IMPLEMENT_CLEAN(IN P_CleanId INT)
  SQL SECURITY INVOKER
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;

	-- 清空账款信息和统计信息
	IF P_CleanId <> 6 AND P_CleanId <> 7 THEN


		-- 统计
-- 		TODO 统计的部分表在做完统计模块后需要打开

		DELETE  FROM stat_daily_sales_amount;

 		DELETE  FROM stat_monthly_sales_amount;

	END IF;
	-- 全清(货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关)
	IF P_CleanId = 1 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;
		
		-- crm

		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;
		-- purchase
		DELETE FROM purchase_order_log;
		DELETE FROM purchase_order_detail;
		DELETE FROM purchase_order;
		-- goods
      
		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;
		DELETE FROM goods_log;

		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;

		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		
		
		-- stock

		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		-- 关联表
		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		
		
		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;
		--  清除库存同步记录
		DELETE FROM api_stock_sync_record;

		-- 通知消息 new add
		DELETE FROM sys_notification;


		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM cfg_employee_rights WHERE employee_id > 1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		--  订单全链路
		DELETE FROM sales_trade_trace;
		-- 客服备注修改历史记录  new add
		DELETE FROM api_trade_remark_history;
		-- 订单备注提取策略 new add
		DELETE FROM cfg_trade_remark_extract;
		-- cfg
		DELETE FROM cfg_stock_sync_rule;

		-- sys
		DELETE FROM sys_other_log;
		DELETE FROM sys_process_background;
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 1, '清除系统所有信息');
	END IF;
	-- 清除货品信息(清除：订单、库存、事务，保留客户、员工信息）
	IF P_CleanId = 2 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;





		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;

		DELETE FROM goods_log;



		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;


		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;


		-- stock

		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;


		DELETE FROM stockin_order_detail;

		DELETE FROM stockin_order;


		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 2, '清除货品信息(清除：订单、库存，保留客户、员工信息)');
	END IF;
	-- 清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工)
	IF P_CleanId = 3 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;

		-- crm
		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;






		-- stock

 		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 3, '清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工信息)');
	END IF;
	-- 清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户、供货商信息)
	IF P_CleanId = 4 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;




		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;




		-- hr
		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 4, '清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户信息)');
	END IF;
	-- 清除订单、采购信息、库存调拨等相关库存订单信息(库存量由脚本重刷)
	IF P_CleanId = 5 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;

		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;
		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		DELETE FROM stock_spec_detail;
-- zhuyi1
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0,purchase_num=0,
			to_purchase_num=0,purchase_arrive_num=0,refund_num=0,transfer_num=0,return_num=0,return_exch_num=0,
			return_onway_num=0,refund_exch_num=0,refund_onway_num=0,default_position_id=IF(default_position_id=0,-warehouse_id,default_position_id);
		-- INSERT INTO stock_spec_detail(stock_spec_id,spec_id,stockin_detail_id,position_id,position_no,zone_id,zone_no,cost_price,stock_num,virtual_num,created)
		--	SELECT ss.rec_id,ss.spec_id,0,ss.default_position_id,cwp.position_no,cwz.zone_id,cwz.zone_no,ss.cost_price,ss.stock_num,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
 		-- INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,created)
		--	SELECT ss.warehouse_id,ss.spec_id,ss.default_position_id,cwz.zone_id,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;

		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 5, ' 清除订单、采购、盘点、等相关库存信息');
	END IF;


	-- 清除订单信息
	IF P_CleanId = 8 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api  删除原始订单和退换单
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		-- sales 删除原始订单和退换单
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

	
		-- 销售出库单未在stock_change_history里插入数据的都可以删除，在stock_change_history里插入数据的将入库类型改为其他入库
		UPDATE stockout_order so,stockout_order_detail sod,stock_change_history sch
			SET so.src_order_type=7,so.src_order_id=0,so.src_order_no='',sod.src_order_type=7 ,sod.src_order_detail_id=0,
			sch.src_order_type=7, sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=1 AND so.stockout_id=sod.stockout_id
			AND so.stockout_id=sch.stockio_id AND sch.type=2;

		DELETE sodp.* FROM stockout_order_detail_position sodp,stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND sod.rec_id=stockout_order_detail_id AND so.src_order_type=1 ;

		-- 删除未出库的出库单管理的stockout_pack_order,stockout_pack_order_detail 必须先删 有外键
-- 		DELETE spod.*  FROM stockout_pack_order spo,stockout_pack_order_detail spod,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id AND spo.pack_id=spod.pack_id AND so.src_order_type=1;

-- 		DELETE spo.*  FROM stockout_pack_order spo,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id  AND so.src_order_type=1;

		-- 删除未出库的出库单和出库单详情
		DELETE sod.* FROM stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND so.src_order_type=1 ;

		DELETE so.* FROM stockout_order so WHERE so.src_order_type=1 ;
		-- 清空打印批次相关的数据


		-- stockin
		-- 将退货入库的入库单改成其他入库
		UPDATE stockin_order so,stockin_order_detail sod,stock_change_history sch
			SET so.src_order_type=6,so.src_order_id=0,so.src_order_no='',sod.src_order_type=6,sod.src_order_detail_id=0,
			sch.src_order_type=6,sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  AND so.stockin_id=sch.stockio_id
			AND sch.type=1;

		-- 删除未入库的入库单和入库单详情
		DELETE sod.* FROM stockin_order so,stockin_order_detail sod
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  ;

		DELETE so.* FROM stockin_order so WHERE so.src_order_type=3 ;
		-- stock
		-- 将stock_spec中的未付款量，预订单量，待审核量，待发货量清0    销售退货量 销售换货在途量（发出和收回）这三个暂时没用 所以没有清0
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0;
		-- 将stock_spec_detail中的占用量清0
		UPDATE stock_spec_detail SET reserve_num=0,is_used_up=0;
		-- 删除日志表中有关订单操作的日志
		DELETE FROM stock_inout_log WHERE order_type=2 AND operate_type IN(1,2,3,4,7,14,23,24,51,52,62,63,111,113,120,121,300);
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message) VALUES(13, P_UserId, 8,'清除订单信息，和订单相关的出库单，入库单的类型变为其他出库，其他入库');
		-- -- stockout_order 中的字段consign_status,customer_id等没有用了，
	END IF;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `SP_INT_ARR_TO_TBL`;
DELIMITER //
CREATE PROCEDURE `SP_INT_ARR_TO_TBL`(IN `P_Str` VARCHAR(8192), IN `P_Clear` INT)
    SQL SECURITY INVOKER
    COMMENT '将字符串数组插入到临时表，如1,2,4,2'
MAIN_LABEL:BEGIN
	DECLARE V_I1, V_I2, V_I3 BIGINT;
	DECLARE V_IT VARCHAR(255);
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_xchg(
		rec_id int(11) NOT NULL AUTO_INCREMENT,
		f1 VARCHAR(40),
		f2 VARCHAR(1024),
		f3 VARCHAR(40),
		PRIMARY KEY (rec_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	IF P_Str IS NULL OR LENGTH(P_Str)=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_Clear THEN
		DELETE FROM tmp_xchg;
	END IF;
	
	IF P_Str=' ' THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_I1 = 1;
	STR_LABEL:LOOP
	 SET V_I2 = locate(',', P_Str, V_I1);
	 IF V_I2 = 0 THEN
	   SET V_IT = substring(P_Str, V_I1);
	 ELSE
	   SET V_IT = substring(P_Str, V_I1, V_I2 - V_I1);
	 END IF;
	 
	 IF V_IT IS NOT NULL THEN
		set V_I3 = cast(V_IT as signed);
		INSERT INTO tmp_xchg(f1) VALUES(V_I3);
	 END IF;
	 
	 IF V_I2 = 0 OR V_I2 IS NULL THEN
	   LEAVE STR_LABEL;
	 END IF;
	
	 SET V_I1 = V_I2 + 1;
	END LOOP;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_ALL`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_ALL`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	DECLARE V_LockName, V_TradeNO, V_BuyerNick VARCHAR(40);
	
	DECLARE trade_cursor CURSOR FOR SELECT rec_id,tid,buyer_nick FROM api_trade WHERE process_status=10 AND bad_reason=0 AND platform_id>0 LIMIT 100;
	-- DECLARE refund_cursor CURSOR FOR SELECT refund_id FROM api_refund WHERE process_status=0 LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
		RESIGNAL;
	END;
	
	-- 统计递交的订单数
	SET @tmp_delivered_count = 0;
	
	SET V_LockName = CONCAT('deliver_lock_', DATABASE());
	IF NOT IS_FREE_LOCK(V_LockName) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT GET_LOCK(V_LockName, 1) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET @cur_uid = P_OperatorID;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	IF @cfg_order_auto_submit THEN
		OPEN trade_cursor;
		TRADE_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				IF V_TradeCount >= 100 THEN
					-- 需要测试，改成1测试
					SET V_TradeCount = 0;
					CLOSE trade_cursor;
					OPEN trade_cursor;
					ITERATE TRADE_LABEL;
				END IF;
				LEAVE TRADE_LABEL;
			END IF;
			
			SET V_TradeCount = V_TradeCount+1;
			
			CALL I_DL_DELIVER_API_TRADE(V_TradeID, P_OperatorID);
			
			IF @sys_code = 0 THEN
				SET @tmp_delivered_count = @tmp_delivered_count+1;
			ELSEIF @sys_code<>2 THEN	-- 是指此订单已经递交过
				INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
			END IF;
		END LOOP;
		close trade_cursor;
	END IF;
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(P_OperatorID);

	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 19);
	SET @tmp_to_preorder_count = @tmp_to_process_count;
	
	-- 待审核订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 20);
	SET @tmp_to_check_count = @tmp_to_process_count;
	*/
	-- 递交退款单
	/*
	SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, P_OperatorID);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
		END IF;
	END LOOP;
	close refund_cursor;
	*/
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
	-- 解锁
	SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_SOME`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_SOME`(IN `P_TradeIDs` VARCHAR(1024))
    SQL SECURITY INVOKER
    COMMENT '根据指定的原始单ID进行递交'
BEGIN
	DECLARE V_TradeNO,V_BuyerNick VARCHAR(40);
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	
	DECLARE trade_cursor CURSOR FOR select ax.rec_id,tid,buyer_nick 
		from tmp_xchg tx,api_trade ax 
		where ax.rec_id=tx.f1;
	
	-- DECLARE refund_cursor CURSOR FOR select refund_id from api_refund where process_status=0 LIMIT 500;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
		RESIGNAL;
	END;
		
	CALL SP_INT_ARR_TO_TBL(P_TradeIDs, 1);
	
	-- 清除无效货品标记
	UPDATE tmp_xchg tx,api_trade ax 
	SET bad_reason=(bad_reason&~1)
	WHERE ax.rec_id=tx.f1;
	
	UPDATE tmp_xchg tx,api_trade ax,api_trade_order ato 
	SET ato.is_invalid_goods=0
	WHERE ax.rec_id=tx.f1 AND ato.platform_id=ax.platform_id AND ato.tid=ax.tid;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	OPEN trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			LEAVE TRADE_LABEL;
		END IF;
		
		CALL I_DL_DELIVER_API_TRADE(V_TradeID, @cur_uid);
		
		IF @sys_code = 0 THEN
			SET @tmp_delivered_count = @tmp_delivered_count+1;
		ELSEIF @sys_code<>1 THEN	-- 1是指此订单已经递交过
			INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE trade_cursor;
	
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(@cur_uid);
	
	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 19);
	-- 待审核订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 20);
	*/
	
	-- 递交退款单
	/*SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 500 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, @cur_uid);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
			INSERT INTO tbl_deliver_error(tid, error_code, error_info) values(V_RefundID, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE refund_cursor;*/
	
	-- CALL I_DL_SYNC_REFUND(P_OperatorID);
	
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_CHAR`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_CHAR`(IN `P_Key` VARCHAR(60), OUT `P_Val` VARCHAR(256), IN `P_Def` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_INT`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_INT`(IN `P_Key` VARCHAR(60), OUT `P_Val` INT, IN `P_Def` INT)
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;





DROP FUNCTION IF EXISTS `FN_EMPTY`;
DELIMITER //
CREATE FUNCTION `FN_EMPTY`(`P_Str` VARCHAR(1024)) RETURNS tinyint(4)
    NO SQL
    SQL SECURITY INVOKER
    DETERMINISTIC
BEGIN
	IF P_Str IS NULL OR P_Str = '' THEN
		RETURN 1;
	END IF;
	
	RETURN 0;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_GOODS_NO`;
DELIMITER //
CREATE FUNCTION `FN_GOODS_NO`(`P_Type` INT, `P_TargetID` INT) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
    COMMENT '查询货品或组合装信息'
BEGIN
	DECLARE V_GoodsNO VARCHAR(40);
	
	SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',
		@tmp_goods_id='',@tmp_spec_id='',@tmp_barcode='',@tmp_retail_price=0;-- ,@tmp_sn_enable=0
	
	IF P_Type=1 THEN
		SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name,gs.spec_code,gg.goods_id,gs.spec_id,gs.barcode,gs.retail_price -- gs.is_sn_enable,
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,V_GoodsNO,@tmp_spec_name,@tmp_spec_code,@tmp_goods_id,@tmp_spec_id,@tmp_barcode,@tmp_retail_price -- ,@tmp_sn_enable
		FROM goods_spec gs,goods_goods gg WHERE gs.spec_id=P_TargetID AND gs.goods_id=gg.goods_id;
		
	ELSEIF P_Type=2 THEN
		-- SELECT 1 INTO @tmp_sn_enable
		-- FROM goods_suite_detail gsd, goods_spec gs
		-- WHERE gsd.suite_id=P_TargetID AND gs.spec_id=gsd.spec_id AND gs.is_sn_enable>0 LIMIT 1;
		
		SELECT suite_no,suite_name,short_name,suite_id,'','',barcode,retail_price
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,@tmp_goods_id,@tmp_spec_id,@tmp_spec_name,@tmp_barcode,@tmp_retail_price 
		FROM goods_suite WHERE suite_id=P_TargetID;
		
		SET V_GoodsNO='';
	END IF;
	
	RETURN V_GoodsNO;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SEQ`;
DELIMITER //
CREATE FUNCTION `FN_SEQ`(`P_Name` VARCHAR(20)) RETURNS int(11)
	READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
 BEGIN
     SET @tmp_seq=1;
     INSERT INTO sys_sequence(`name`,`val`) VALUES(P_Name, 1) ON DUPLICATE KEY UPDATE val=(@tmp_seq:=(val+1));
     RETURN @tmp_seq;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SPEC_NO_CONV`;
DELIMITER $$
CREATE FUNCTION `FN_SPEC_NO_CONV`(`P_GoodsNO` VARCHAR(40), `P_SpecNO` VARCHAR(40)) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_I INT;
	
	IF LENGTH(@cfg_goods_match_split_char)>0 THEN
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_GoodsNO);
		IF V_I THEN
			SET P_GoodsNO=SUBSTRING(P_GoodsNO, 1, V_I-1);
		END IF;
		
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_SpecNO);
		IF V_I THEN
			SET P_SpecNO=SUBSTRING(P_SpecNO, 1, V_I-1);
		END IF;
		
	END IF;
	
	RETURN IF(@cfg_goods_match_concat_code,CONCAT(P_GoodsNO,P_SpecNO),IF(P_SpecNO<>'',P_SpecNO,P_GoodsNO));
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SYS_NO`;
DELIMITER //
CREATE FUNCTION `FN_SYS_NO`(`P_Key` VARCHAR(50)) RETURNS VARCHAR(60) CHARSET UTF8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_DateChanged BIT;
	DECLARE V_Prefix, V_PostfixStr, V_PostfixStr2 VARCHAR(64);
	DECLARE V_PostfixLen, V_PostfixVal,V_DateForm INT;
	DECLARE V_NowDate DATE;
	
	-- date_form 0 全日期 1短日期 2无日期
	
	SET V_NowDate = CURDATE();
	
	UPDATE sys_no_cfg SET postfix_val=LAST_INSERT_ID(IF(V_NowDate=last_date OR date_form=2,postfix_val+1,1)),last_date=V_NowDate
	WHERE `key`=P_Key;
	
	SET V_PostfixVal = LAST_INSERT_ID();
	
	SELECT prefix,postfix_len,date_form into V_Prefix,V_PostfixLen,V_DateForm from sys_no_cfg WHERE `key`=P_Key;
	
	SET V_PostfixStr = CAST(V_PostfixVal AS CHAR);
	SET V_PostfixStr2 = LPAD(V_PostfixStr, V_PostfixLen, '0');
	IF LENGTH(V_PostfixStr2) < LENGTH(V_PostfixStr) THEN
		SET V_PostfixStr2 = V_PostfixStr;
	END IF;
	
	IF V_DateForm=0 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%Y%m%d'), V_PostfixStr2);
	ELSEIF V_DateForm=1 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%y%m%d'), V_PostfixStr2);
	END IF;
	
	RETURN CONCAT(V_Prefix, V_PostfixStr2);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE_CHANGED`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE_CHANGED`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ModifyFlag,V_TradeCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ShopID,V_ApiTradeStatus,V_RefundStatus TINYINT DEFAULT(0);
	DECLARE V_ApiTradeID,V_RecID BIGINT DEFAULT(0);
	DECLARE V_Tid,V_Oid VARCHAR(40);
	
	DECLARE api_trade_cursor CURSOR FOR 
		SELECT rec_id FROM api_trade FORCE INDEX(IX_api_trade_modify_flag)
		WHERE modify_flag>0 AND bad_reason=0 LIMIT 100;
	
	DECLARE api_trade_order_cursor CURSOR FOR 
		SELECT modify_flag,rec_id,status,shop_id,tid,oid,refund_status
		FROM api_trade_order WHERE modify_flag>0
		LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 主订单变化
	OPEN api_trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_trade_cursor INTO V_ApiTradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_cursor;
				OPEN api_trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_MAIN_ORDER(P_OperatorID, V_ApiTradeID);
		
	END LOOP;
	CLOSE api_trade_cursor;
	
	
	SET V_TradeCount = 0;
	-- 子订单变化
	OPEN api_trade_order_cursor;
	TRADE_ORDER_LABEL: LOOP
		-- modify_flag,rec_id,status,refund_status
		FETCH api_trade_order_cursor INTO V_ModifyFlag,V_RecID,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_order_cursor;
				OPEN api_trade_order_cursor;
				ITERATE TRADE_ORDER_LABEL;
			END IF;
			LEAVE TRADE_ORDER_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_SUB_ORDER(P_OperatorID,V_RecID,V_ModifyFlag,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus);
	END LOOP;
	CLOSE api_trade_order_cursor;
END//
DELIMITER ;

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
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
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
	
	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '原始单状态不正确';
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
	CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
	IF V_IsPreorder THEN
		SET V_ExtMsg = ' 进预订单原因:客服备注提取';
	END IF;
	
	-- 客户备注
	SET V_BuyerMessage=TRIM(V_BuyerMessage);
	CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
	
	-- select warehouse_id randomly
	SELECT warehouse_id INTO V_WarehouseID2 FROM cfg_warehouse where is_disabled = 0 limit 1;
	
	-- get logistics_id from cfg_shop 
	IF V_DeliveryTerm=2 THEN
		SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	ELSE 
		SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
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
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
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
	SET V_AreaAlias = '';
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID2 AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;
	/*
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
	*/
	
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
				/*
				IF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
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
				END IF;
				*/
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
		salesman_id,is_sealed,freeze_reason,delay_to_time,commission,gift_mask,unmerge_mask,raw_goods_type_count,raw_goods_count,single_spec_no,created)
	VALUES(FN_SYS_NO('sales'),V_PlatformID,V_ShopID,V_Tid,V_TradeStatus,1,V_TradeFrom,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,V_FenxiaoNick,
		V_TradeTime,V_PayTime,V_PayAccount,V_CustomerType,V_CustomerID,V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,
		V_ToDeliverTime,V_DistCenter,V_DistSite,V_BuyerMessage,V_Remark,V_RemarkFlag,
		V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_TotalVolume,V_LogisticsID,V_AreaAlias,V_PostCost,V_GoodsCost,
		NOT FN_EMPTY(V_Remark),NOT FN_EMPTY(V_BuyerMessage),NOT FN_EMPTY(V_Remark),
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID2,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType2,V_StockoutNO,V_PackageID,
		V_SalesmanID,V_IsSealed,V_IsFreezed,V_DelayToTime,V_Commission,V_GiftMask,V_UnmergeMask,V_ApiOrderCount,V_ApiGoodsCount,V_SingleSpecNO,V_Now);
	
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
		SELECT V_TradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
		FROM cfg_oper_reason 
		WHERE reason_id = V_IsFreezed;
	END IF;
	
	-- 更新原始单
	UPDATE api_trade SET process_status=20,
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_salesman_id=V_SalesmanID,
		x_trade_flag=V_FlagID,
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

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_SALES_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_SALES_TRADE`(IN `P_OperatorID` INT, IN `P_Status` INT)
    SQL SECURITY INVOKER
    COMMENT '递交第二步'
BEGIN
	DECLARE V_CurTime, V_TradeID, V_ShopID,V_WarehouseType,V_WarehouseID,V_DeliveryTerm,V_IsSealed,
		V_TradeID2, V_WarehouseID2,V_GiftMask,V_PlatformID, V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict, 
		V_TradeStatus, V_TradeCount, V_CheckedTradeID, V_CustomerID,V_TradeChanged,V_IsLarge,
		V_ToStatus, V_NOT_FOUND, V_bNoMerge, V_bNoSplit, V_bAllSplit,V_FreezeReasonID,
		V_LockWarehouse,V_SplitFromTradeID,V_UnmergeMask,V_GroupID INT DEFAULT(0);
	
	DECLARE V_IsSetWareByGoods INT DEFAULT(1);
	
	DECLARE V_RawTradeNO VARCHAR(40);
	DECLARE V_ReceiverArea,V_ReceiverName VARCHAR(64);
	DECLARE V_Tid,V_ReceiverAddress VARCHAR(256);
	
	DECLARE trade_cursor CURSOR FOR 
		SELECT trade_id,src_tids,shop_id,platform_id,delivery_term,customer_id,
			receiver_name,receiver_province,receiver_city,receiver_district,
			receiver_area,receiver_address,warehouse_type,warehouse_id, 
			gift_mask,customer_id,is_sealed,freeze_reason,split_from_trade_id 
		FROM sales_trade WHERE trade_status=P_Status
		LIMIT 100;
	
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
		FETCH trade_cursor INTO V_TradeID, V_Tid, V_ShopID, V_PlatformID, V_DeliveryTerm, V_CustomerID, 
			V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
			V_ReceiverArea,V_ReceiverAddress, V_WarehouseType, V_WarehouseID, 
			V_GiftMask,V_CustomerID,V_IsSealed,V_FreezeReasonID,V_SplitFromTradeID;
		
		IF V_NOT_FOUND THEN
			IF V_TradeCount >= 500 THEN
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
		
		
		--  待审核订单合并
		IF @cfg_auto_merge AND V_IsLarge<2  AND V_bNoMerge=0 AND V_FreezeReasonID=0 AND V_DeliveryTerm=1 AND V_SplitFromTradeID = 0 THEN
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
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;
			/*ELSE
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
					st.trade_from=1 AND 
					st.trade_type=1 AND 
					st.freeze_reason=0 AND 
					st.revert_reason=0 AND 
					st.checkouter_id=0 AND 
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;*/
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
					UPDATE sales_trade SET unmerge_mask=(V_UnmergeMask & ~2) WHERE trade_id=V_TradeID;
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
		/*IF @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
				ITERATE TRADE_LABEL;
			END IF;
		END IF;
		
		IF V_TradeChanged THEN
			CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
		END IF;*/
		
		-- 占用库存
		CALL I_RESERVE_STOCK(V_TradeID, IF(V_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);
		
		-- 标记同名未合并的
		IF @cfg_order_check_warn_has_unmerge THEN
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
		END IF;
		
		COMMIT;
		
		SET @tmp_to_process_count = @tmp_to_process_count+1;
	END LOOP;
	CLOSE trade_cursor;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_CLIENT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_CLIENT_REMARK`(IN `P_Remark` VARCHAR(1024), 
	INOUT `P_TradeFlag` INT, 
	INOUT `P_WmsType` INT, 
	INOUT `P_WarehouseID` INT, 
	INOUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客户备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_Kw VARCHAR(255);
	DECLARE V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=2 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_c_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=2 THEN
			IF V_Target>0 AND P_TradeFlag=0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND P_WarehouseID=0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=6 THEN
			IF P_FreezeReason=0 THEN
				SET P_FreezeReason=GREATEST(1,V_Target);
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_REMARK`(IN `P_Remark` VARCHAR(1024), OUT `P_LogisticsID` INT, OUT `P_TradeFlag` INT, OUT `P_SalesmanID` INT, INOUT `P_WmsType` INT, OUT `P_WarehouseID` INT, OUT `P_IsPreorder` INT, OUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客服备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_SalesManName,V_Kw VARCHAR(255);
	DECLARE V_MacroBeginIndex, V_MacroEndIndex, V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=1 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_LogisticsID=0;
	SET P_TradeFlag=0;
	SET P_SalesmanID=0;
	SET P_WarehouseID = 0;
	SET P_IsPreorder=0;
	SET P_FreezeReason=0;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 根据括号提取
	IF @cfg_salesman_macro_begin<> '' AND @cfg_salesman_macro_end <> '' THEN
		SET V_MacroBeginIndex = LOCATE(@cfg_salesman_macro_begin, P_Remark, 1);
		IF V_MacroBeginIndex > 0 THEN
			SET V_MacroEndIndex = LOCATE(@cfg_salesman_macro_end, P_Remark, V_MacroBeginIndex+1);
			IF V_MacroEndIndex>0 THEN
				SET V_SalesManName = SUBSTRING(P_Remark, V_MacroBeginIndex+CHAR_LENGTH(@cfg_salesman_macro_begin), V_MacroEndIndex-V_MacroBeginIndex-CHAR_LENGTH(@cfg_salesman_macro_end));
				IF V_SalesManName IS NOT NULL AND V_SalesManName<>'' THEN 
					SELECT employee_id INTO P_SalesmanID FROM hr_employee WHERE fullname=V_SalesManName AND deleted=0 AND is_disabled=0;
				END IF;
			END IF;
		END IF;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=1 THEN
			IF V_Target>0 THEN
				SET P_LogisticsID=V_Target;
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=3 THEN
			IF V_Target>0 THEN
				SET P_SalesmanID=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=5 THEN
			SET P_IsPreorder=1;
		ELSEIF V_Type=6 THEN
			SET P_FreezeReason=GREATEST(1,V_Target);
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_INIT`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT`(IN `P_CreateApiGoods` INT)
    SQL SECURITY INVOKER
    COMMENT '递交处理初始化'
MAIN_LABEL:BEGIN
	DECLARE V_AutoMatchGoods INT DEFAULT(0);
	
	/*配置*/
	-- 是否开启自动递交
	CALL SP_UTILS_GET_CFG_INT('order_auto_submit',@cfg_order_auto_submit,1);

	-- 连接货品和规格商家编码
	CALL SP_UTILS_GET_CFG_INT('sys_goods_match_concat_code', @cfg_goods_match_concat_code, 0);

	-- 自动匹配平台货品的截取字符
	CALL SP_UTILS_GET_CFG_CHAR('goods_match_split_char', @cfg_goods_match_split_char, '');	
	
	-- 动态跟踪自动匹配货品
	-- CALL SP_UTILS_GET_CFG_INT('goods_match_dynamic_check', @cfg_goods_match_dynamic_check, 0);
	
	-- 是否自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge', @cfg_auto_merge, 1);
	
	-- 自动合并是否重新计算赠品
	CALL SP_UTILS_GET_CFG_INT('sales_trade_auto_merge_gift', @cfg_auto_merge_gift, 1);
	-- 订单审核时提示同名未合并
	CALL SP_UTILS_GET_CFG_INT('order_check_warn_has_unmerge', @cfg_order_check_warn_has_unmerge, 1);
	
	-- 延时审核分钟数
	CALL SP_UTILS_GET_CFG_INT('order_delay_check_min', @cfg_delay_check_sec, 0);	
	
	SET @cfg_delay_check_sec = @cfg_delay_check_sec*60;
	
	-- 已付等未付分钟数
	-- CALL SP_UTILS_GET_CFG_INT('order_wait_unpay_min', @cfg_wait_unpay_sec, 0);	
	
	SET @cfg_wait_unpay_sec = @cfg_wait_unpay_sec*60;
	
	-- 大件自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split', @cfg_order_auto_split, 1);
	
	-- 大件拆分最大次数
	-- CALL SP_UTILS_GET_CFG_INT('sales_split_large_goods_max_num', @cfg_sales_split_large_goods_max_num, 50);
	
	-- 按不同仓库自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split_by_warehouse',@cfg_order_auto_split_by_warehouse,0);
	
	-- 订单合并方式
	CALL SP_UTILS_GET_CFG_INT('order_auto_merge_mode', @cfg_order_merge_mode, 0);	
	-- 审核时提示条件
	CALL SP_UTILS_GET_CFG_INT('order_check_merge_warn_mode', @cfg_order_check_merge_warn_mode, 0);
	
	-- 业务员
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_begin', @cfg_salesman_macro_begin, '');	
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_end', @cfg_salesman_macro_end, '');	
	
	
	IF @cfg_salesman_macro_begin='' OR @cfg_salesman_macro_begin IS NULL OR @cfg_salesman_macro_end='' OR @cfg_salesman_macro_end IS NULL THEN
		SET @cfg_salesman_macro_begin='';
		SET @cfg_salesman_macro_end='';
	END IF;
	
	-- 物流选择方式：全局唯一，按店铺，按仓库
	-- CALL SP_UTILS_GET_CFG_INT('logistics_match_mode', @cfg_logistics_match_mode, 0);	

	-- 按货品先仓库
	-- CALL SP_UTILS_GET_CFG_INT('sales_trade_warehouse_bygoods', @cfg_sales_trade_warehouse_bygoods, 0);
	
	-- 如果仓库是按货品策略选出,修改时给出提醒
	-- CALL SP_UTILS_GET_CFG_INT('order_check_alert_locked_warehouse', @cfg_chg_locked_warehouse_alert, 0);

	-- 是否启用备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_remark_extract', @cfg_enable_remark_extract, 0);	
	-- 客户备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_c_remark_extract', @cfg_enable_c_remark_extract, 0);	
	-- 订单进入待审核后是否根据备注提取物流
	CALL SP_UTILS_GET_CFG_INT('order_deliver_enable_cs_remark_track', @cfg_order_deliver_enable_cs_remark_track, 1);	
	
	-- 自动按商家编码匹配货品
	CALL SP_UTILS_GET_CFG_INT('apigoods_auto_match', V_AutoMatchGoods, 1);	
	
	-- 转预订单设置
	/* CALL SP_UTILS_GET_CFG_INT('order_go_preorder', @cfg_order_go_preorder, 0);
	IF @cfg_order_go_preorder THEN
		CALL SP_UTILS_GET_CFG_INT('order_preorder_lack_stock', @cfg_order_preorder_lack_stock, 0);
		CALL SP_UTILS_GET_CFG_INT('preorder_split_to_order_condition',@cfg_preorder_split_to_order_condition,0);
	END IF;
	*/
	CALL SP_UTILS_GET_CFG_INT('remark_change_block_stockout', @cfg_remark_change_block_stockout, 1);
	-- 物流同步后,发生退款不拦截
	CALL SP_UTILS_GET_CFG_INT('unblock_stockout_after_logistcs_sync', @cfg_unblock_stockout_after_logistcs_sync, 0);
	
	-- 销售凭证自动过账
	-- CALL SP_UTILS_GET_CFG_INT('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	
	-- 米氏抢单全局开关
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_hold', @cfg_order_deliver_hold, 0);
	
	--  根据重量计算物流
	CALL SP_UTILS_GET_CFG_INT('calc_logistics_by_weight',@cfg_calc_logistics_by_weight,0);
	
	--  包装策略
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy', @cfg_open_package_strategy,0); 
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy_type',@cfg_open_package_strategy_type,1); -- 1,根据重量   2,根据体积
	
	-- 是否开启订单全链路
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	-- 订单中原始货品数量是否包含赠品
	CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	
	-- 强制凭证不需要审核
	-- SET @cfg_fa_voucher_must_check=0;
	
	-- 是否需要从原始单货品生成api_goods_spec
	IF NOT P_CreateApiGoods THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*导入平台货品*/
	START TRANSACTION;
	
	SELECT 1 INTO @tmp_dummy FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE;
	
	UPDATE api_goods_spec ag,api_trade_order ato,api_trade at
	SET ag.modify_flag=
		IF(ag.outer_id=ato.goods_no AND ag.spec_outer_id=ato.spec_no, ag.modify_flag, ag.modify_flag|1),
		ag.outer_id=ato.goods_no,ag.spec_outer_id=ato.spec_no,
		ag.goods_name=ato.goods_name,ag.spec_name=ato.spec_name,
		ag.cid=IF(ato.cid='',ag.cid,ato.cid),at.is_new=0
	WHERE at.process_status=10 AND at.is_new=1 AND ato.tid=at.tid AND ato.shop_id=at.shop_id AND ato.goods_id<>''
		AND ag.shop_id=ato.shop_id AND ag.goods_id=ato.goods_id AND ag.spec_id=ato.spec_id;
	
	-- 要测试平台更新编码的同步
	INSERT INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,cid,modify_flag,created)
	(
		SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,at.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,ato.cid,1,NOW()
		FROM api_trade_order ato INNER JOIN api_trade at ON ato.tid=at.tid AND ato.shop_id=at.shop_id
		WHERE at.process_status=10 AND at.is_new=1 AND ato.goods_id<>''
	)
	ON DUPLICATE KEY UPDATE modify_flag=
		IF(api_goods_spec.outer_id=VALUES(outer_id) AND api_goods_spec.spec_outer_id=VALUES(spec_outer_id), api_goods_spec.modify_flag, api_goods_spec.modify_flag|1),
		outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),
		goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),
		cid=IF(VALUES(cid)='',api_goods_spec.cid,VALUES(cid));
	
	UPDATE api_trade SET is_new=0 WHERE process_status=10 and is_new=1;
	COMMIT;
	
	IF V_AutoMatchGoods THEN
		-- 对新增和变化的平台货品进行自动匹配
		UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;
		
		-- 刷新品牌分类
		UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
		SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;
		
		UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
		SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;
		
		-- 刷新无效货品
		UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax
		SET ato.is_invalid_goods=0,ax.bad_reason=0
		WHERE ato.is_invalid_goods=1 AND ag.`shop_id`=ato.`shop_id` AND ag.`goods_id`=ato.`goods_id` AND
			ag.`spec_id`=ato.`spec_id` AND ax.shop_id=ato.`shop_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND
			ag.match_target_type>0; 
		
		-- 自动刷新库存同步规则
		-- 应该判断一下规则是否变化了，如果变化了，要触发同步开关????????????
		UPDATE api_goods_spec gs,
		(SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id 
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1)
		WHERE gs.rec_id=da.rec_id;
		UPDATE api_goods_spec SET modify_flag=(modify_flag&~1) WHERE modify_flag>0 AND (modify_flag&1);
	END IF;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MAP_TRADE_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_MAP_TRADE_GOODS`(IN `P_TradeID` INT, IN `P_ApiTradeID` BIGINT, IN `P_UseTran` INT, OUT `P_ApiOrderCount` INT, OUT `P_ApiGoodsCount` INT)
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中'
MAIN_LABEL: BEGIN 
	DECLARE V_MatchTargetID,V_GoodsID,V_SGoodsID,V_SpecID,V_SuiteSpecCount,V_I,V_GiftType,V_MasterID,V_ShopID,
		V_Cid,V_IsDeleted,V_NOT_FOUND INT DEFAULT(0);
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
	
	-- 订单信息
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,oid,ato.status,refund_status,bind_oid,invoice_type,invoice_content,num,ato.price,adjust_amount,
			discount,share_discount,share_amount,share_post,paid,match_target_type,match_target_id,spec_no,ato.gift_type,
			ato.goods_name,ato.spec_name,aps.cid,aps.is_manual_match,ato.goods_no,ato.spec_no,ato.remark
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON aps.shop_id=V_ShopID AND aps.goods_id=ato.goods_id and aps.spec_id=ato.spec_id
		WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND ato.process_status=10;
	
	-- 组合装货品
	DECLARE goods_suite_cursor CURSOR FOR 
		SELECT gsd.spec_id,gsd.num,gsd.is_fixed_price,gsd.fixed_price,gsd.ratio,gg.goods_name,gs.goods_id,gg.goods_no,
			gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,(gs.length*gs.width*gs.height) as volume ,gs.tax_rate,gs.large_type,(gs.retail_price*gsd.num),gs.is_allow_zero_cost,gs.deleted
		FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gsd.suite_id=V_MatchTargetID AND gsd.num>0
		ORDER BY gsd.is_fixed_price DESC;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	
	DELETE FROM tmp_sales_trade_order;
	
	SELECT platform_id,shop_id,tid,delivery_term,guarantee_mode,trade_mask
	INTO V_PlatformID,V_ShopID,V_Tid,V_DeliveryTerm,V_GuaranteeMode,V_TradeMask
	FROM api_trade WHERE rec_id=P_ApiTradeID;
	
	-- 展开货品
	SET P_ApiOrderCount = 0;
	SET P_ApiGoodsCount = 0;
	SET V_MasterAmount = -1;
	SET V_Now = NOW();
	SET @tmp_refund_occur = 0;
	SET @sys_code=0, @sys_message='OK';
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH trade_order_cursor INTO 
			V_RecID,V_Oid,V_OrderStatus,V_RefundStatus,V_BindOid,V_InvoiceType,V_InvoiceContent,V_Num,V_Price,V_AdjustAmount,
			V_Discount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,V_MatchTargetType,V_MatchTargetID,V_ApiSpecNO,V_GiftType,
			V_ApiGoodsName,V_ApiSpecName,V_CidNO,V_IsManualMatch,V_OuterId,V_SpecOuterId,V_Remark;
			
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_Num <= 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
			 	ROLLBACK;
			 	UPDATE api_trade SET bad_reason=(bad_reason|1) WHERE rec_id=P_ApiTradeID;
			 END IF;
			 SET @sys_code=4, @sys_message = '货品数量为零';
			 LEAVE MAIN_LABEL;
		END IF;
		
		SET P_ApiOrderCount = P_ApiOrderCount + 1;
		SET P_ApiGoodsCount = P_ApiGoodsCount + V_Num;
		
		-- 类目及佣金暂时不做
		-- SET V_CommissionFactor = 0, V_Cid = 0;
		-- 未绑定
		IF V_PlatformID=0 THEN -- 线下订单不需判断无效货品 
			SELECT `type`, target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_ApiSpecNO;
		ELSE
			/*
			IF V_CidNO <> '' THEN
				SELECT rec_id,commission_factor INTO V_Cid,V_CommissionFactor FROM api_goods_category WHERE cid=V_CidNO AND shop_id=V_ShopID;
				SET V_NOT_FOUND=0;
			END IF;
			*/
			-- 判断是否开启动态匹配
			IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
				SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
				SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			END IF;
		END IF;
		
		
		IF V_NOT_FOUND OR V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
				 ROLLBACK;
				 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
			 END IF;
			 SET @sys_code=3, @sys_message = CONCAT('订单包含无效货品:',V_Tid);
			 LEAVE MAIN_LABEL;
		END IF;
		
		-- 子订单关闭,当退款处理
		IF V_OrderStatus=80 OR V_OrderStatus=90 THEN
			 SET V_RefundStatus=5;
		END IF;
		
		IF V_RefundStatus>1 THEN -- 需要创建退款单
			 SET @tmp_refund_occur = V_RefundStatus;
		END IF;
		
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,gs.tax_rate,gs.large_type,gs.is_allow_zero_cost,gs.length*gs.width*gs.height
				INTO V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,V_Weight,V_TaxRate,V_LargeType,V_IsZeroCost,V_Volume
			FROM goods_spec gs LEFT JOIN goods_goods gg USING(goods_id)
			WHERE gs.spec_id=V_MatchTargetID AND gs.deleted=0;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					 ROLLBACK;
					 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=4, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效单品');
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果钱已经付了，则为款到发货
			IF V_Paid >= V_ShareAmount+V_SharePost THEN
				 SET V_OrderDeliveryTerm = 1;
			ELSE
				 SET V_OrderDeliveryTerm = V_DeliveryTerm;
			END IF;
			
			SET V_SharePrice=TRUNCATE(V_ShareAmount/V_Num,4);
			
			-- 退款状态处理??
			INSERT INTO tmp_sales_trade_order(
				spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,paid,
				order_price,share_amount,share_post,share_price,adjust,discount,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
				api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,from_mask,gift_type,
				cid,is_allow_zero_cost,remark)
			VALUES(V_MatchTargetID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_Num,V_Price,
				IF(V_RefundStatus>2,0,V_Num),V_Paid,V_SharePrice,V_ShareAmount,V_SharePost,V_SharePrice,V_AdjustAmount,
				(V_Discount-V_AdjustAmount+V_ShareDiscount),
				V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,
				V_ApiGoodsName,V_ApiSpecName,V_Weight*V_Num,V_Volume*V_Num,TRUNCATE(V_ShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,
				V_InvoiceType,V_InvoiceContent,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
			/*
			-- 找一个未退款的，金额最大的子订单作主订单,不考虑主订单
			IF V_RefundStatus<=2 AND V_ShareAmount > V_MasterAmount THEN
				 SET V_MasterAmount=V_ShareAmount;
				 SET V_MasterID = LAST_INSERT_ID();
			END IF;
			*/
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			-- 取组合装信息
			SELECT suite_no,suite_name,is_unsplit,is_print_suite INTO V_SuiteNO,V_SuiteName,V_IsUnsplit,V_IsPrintSuite
			FROM goods_suite WHERE suite_id=V_MatchTargetID;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=5, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效组合装');
				LEAVE MAIN_LABEL;
			END IF;
			
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
			
			-- 无货品
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装为空');
				LEAVE MAIN_LABEL;
			END IF;
			
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
					CLOSE trade_order_cursor;
					IF P_UseTran THEN
						ROLLBACK;
						CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
					END IF;
					SET @sys_code=7, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装包含已删除单品 ', V_SSpecNO);
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
				
				INSERT INTO tmp_sales_trade_order(
					spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,
					order_price,share_price,share_amount,share_post,discount,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
					api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,suite_id,suite_no,suite_name,suite_num,suite_amount,
					suite_discount,is_print_suite,from_mask,gift_type,cid,is_allow_zero_cost,remark)
				VALUES(V_SpecID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_SNum,V_SPrice,IF(V_RefundStatus>2,0,V_SNum),
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
			
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = '组合装无货品';
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	-- 标记主子订单
	-- 注：拆分合并时处理
	-- UPDATE tmp_sales_trade_order SET is_master=1 WHERE rec_id=V_MasterID;
	
	IF P_TradeID THEN
		INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,created)
		SELECT P_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,NOW()
		FROM tmp_sales_trade_order;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MARK_INVALID_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_MARK_INVALID_TRADE`(IN `P_TradeID` INT, IN `P_ShopId` TINYINT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '标记原始单的子订单有无效货品'
MAIN_LABEL:BEGIN
	DECLARE V_RecID,V_MatchTargetType,V_MatchTargetID,V_InvalidGoods,V_GoodsCount,V_IsManualMatch,V_Deleted,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,match_target_type,match_target_id,is_manual_match,ato.goods_no,ato.spec_no
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON ato.shop_id=aps.shop_id AND ato.goods_id=aps.goods_id and ato.spec_id=aps.spec_id
		WHERE ato.shop_id=P_ShopId AND ato.tid=P_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		
		FETCH trade_order_cursor INTO V_RecID,V_MatchTargetType,V_MatchTargetID,V_IsManualMatch,V_OuterId,V_SpecOuterId;
		IF V_NOT_FOUND THEN
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		-- 未绑定
		IF V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			
			-- 添加到平台货品
			INSERT IGNORE INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,modify_flag,created)
			SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,ax.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,1,NOW()
			FROM api_trade_order ato LEFT JOIN api_trade ax ON ax.tid=ato.tid AND ax.platform_id=ato.platform_id
			WHERE ato.rec_id=V_RecID;
			
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
			SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
			SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			IF V_NOT_FOUND THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
				ITERATE TRADE_GOODS_LABEL;
			END IF;
		END IF;
		
		SET V_Exists=0,V_Deleted = 0;
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_spec WHERE spec_id=V_MatchTargetID;
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_suite WHERE suite_id=V_MatchTargetID;
		END IF;
		
		
		IF NOT V_Exists OR V_Deleted THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_MatchTargetType = 2 THEN
			SELECT COUNT(rec_id) INTO V_GoodsCount FROM goods_suite_detail WHERE suite_id=V_MatchTargetID;
			IF V_GoodsCount=0 THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
			
			-- 判断组合装里货品是否都有效
			IF EXISTS(SELECT 1 FROM goods_suite_detail gsd,goods_spec gs 
				WHERE gsd.suite_id=V_MatchTargetID AND gs.spec_id=gsd.spec_id AND gs.deleted>0) THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	IF V_InvalidGoods THEN
		UPDATE api_trade SET bad_reason=1 WHERE rec_id=P_TradeID;
	END IF;
	COMMIT;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PREPARE_GIFT_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_PREPARE_GIFT_GOODS`(IN `P_TradeID` INT, INOUT `P_First` INT)
	SQL SECURITY INVOKER
	COMMENT '将订单货品插入到临时表,为赠品准备'
MAIN_LABEL:BEGIN
	IF P_First=0 THEN
		LEAVE MAIN_LABEL;
	END IF;

	DELETE FROM tmp_gift_trade_order;
	
	SET P_First=0;
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 0,sto.spec_id,sto.actual_num,sto.discount,sto.share_amount,sto.weight,sto.from_mask,gc.path,gg.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_goods gg ON gg.goods_id=sto.goods_id 
		LEFT JOIN goods_class gc ON gc.class_id=gg.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id=0 AND actual_num>0 AND sto.gift_type=0)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 1,sto.suite_id,sto.suite_num,SUM(sto.discount),SUM(sto.share_amount),SUM(sto.weight),BIT_OR(sto.from_mask),gc.path,gs.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_suite gs ON gs.suite_id=sto.suite_id
		LEFT JOIN goods_class gc ON gc.class_id=gs.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id>0 AND sto.actual_num>0 AND sto.gift_type=0
	GROUP BY platform_id,src_oid)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PUSH_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_PUSH_REFUND`(IN `P_OperatorID` INT, IN `P_ShopID` INT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '递交过程中自动生成退款单'
BEGIN
	DECLARE V_RefundStatus,V_GoodsID,V_SpecId,V_RefundID,V_RefundID2,V_Status,V_ApiStatus,V_Type,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Num DECIMAL(19,4);
	DECLARE V_Oid,V_RefundNO,V_RefundNO2 VARCHAR(40);
	
	DECLARE refund_order_cursor CURSOR FOR 
		SELECT refund_id,refund_status,status,oid
		FROM api_trade_order
		WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 删除临时退款单
	DELETE stro FROM sales_tmp_refund_order stro, api_trade_order sto 
	WHERE stro.shop_id=P_ShopID AND stro.oid=sto.oid AND sto.shop_id=P_ShopID AND sto.tid=P_Tid;
	
	OPEN refund_order_cursor;
	REFUND_ORDER_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH refund_order_cursor INTO V_RefundNO,V_RefundStatus,V_ApiStatus,V_Oid;
		IF V_NOT_FOUND THEN
			LEAVE REFUND_ORDER_LABEL;
		END IF;
		
		IF V_RefundStatus < 2 THEN -- 取消退款
			-- 如果订单已发货，说明是个售后退货,不需要再更新退款单
			IF V_ApiStatus>=40 THEN
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			DELETE FROM sales_tmp_refund_order WHERE shop_id=P_ShopID AND oid=V_Oid;
			
			-- 更新退款单状态
			-- 一个原始单只能出现在一个退款单中
			SET V_RefundID=0;
			SELECT sro.refund_id INTO V_RefundID FROM sales_refund_order sro,sales_refund sr
			WHERE sro.shop_id=P_ShopID AND sro.oid=V_Oid AND sro.refund_id=sr.refund_id AND sr.type=1 LIMIT 1;
			
			IF V_RefundID THEN
				UPDATE sales_refund_order SET process_status=10 WHERE refund_id=V_RefundID AND shop_id=P_ShopID AND oid=V_Oid;
				SET V_Status=0;
				SELECT 1 INTO V_Status FROM sales_refund_order WHERE refund_id=V_RefundID AND process_status<>10 LIMIT 1;
				IF V_Status=0 THEN  -- 全部子订单都取消
					UPDATE sales_refund SET process_status=10,status=V_RefundStatus WHERE refund_id=V_RefundID;
					-- 日志
					INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,4,P_OperatorID,'平台取消退款');
				END IF;
			END IF;
			-- 原始退款单状态?
			
			ITERATE REFUND_ORDER_LABEL;
		END IF;
		
		-- 目前只有淘宝存在退款单号
		-- 没有退款单号的，自动生成一个
		IF V_RefundNO='' THEN
			
			SET V_Type=IF(V_ApiStatus<40,1,2);
			SET V_NOT_FOUND=0;
			SELECT ar.refund_id,ar.refund_no INTO V_RefundID,V_RefundNO FROM api_refund ar,api_refund_order aro
			WHERE ar.shop_id=P_ShopID AND ar.tid=P_Tid AND ar.`type`=V_Type 
				AND aro.shop_id=P_ShopID AND aro.refund_no=ar.refund_no AND aro.oid=V_Oid LIMIT 1;
			
			IF V_NOT_FOUND THEN
				-- 一个货品一个退款单
				SET V_RefundNO=FN_SYS_NO('apirefund');
				
				-- 创建原始退款单
				INSERT INTO api_refund(platform_id,refund_no,shop_id,tid,title,type,status,process_status,pay_account,refund_amount,actual_refund_amount,buyer_nick,refund_time,created)
				(SELECT ax.platform_id,V_RefundNO,ax.shop_id,P_Tid,ato.goods_name,V_Type,ato.refund_status,0,ax.pay_account,ato.refund_amount,ato.refund_amount,ax.buyer_nick,NOW(),NOW()
				FROM api_trade_order ato, api_trade ax
				WHERE ato.shop_id=P_ShopID AND ato.oid=V_Oid AND ax.shop_id=P_ShopID AND ax.tid=P_Tid);
				
				INSERT INTO api_refund_order(platform_id,refund_no,shop_id,oid,status,goods_name,spec_name,num,price,total_amount,goods_id,spec_id,goods_no,spec_no,created)
				(SELECT platform_id,V_RefundNO,shop_id,oid,refund_status,goods_name,spec_name,num,price,share_amount,goods_id,spec_id,goods_no,spec_no,NOW()
				FROM api_trade_order WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0 AND refund_id='');
			ELSE
				UPDATE api_refund SET status=V_RefundStatus,modify_flag=(modify_flag|1) WHERE refund_id=V_RefundID;
				UPDATE api_refund_order SET status=V_RefundStatus WHERE shop_id=P_ShopID AND refund_no=V_RefundNO AND oid=V_Oid;
			END IF;
			
		ELSE
			IF V_ApiStatus>=40 THEN -- 已发货,销后退款,让退款同步脚本处理
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			-- 平台支持退款单的
			-- 查找退款单是否已经存在，如果已存在，就不需要创建临时退款单，直接更新退款单状态
			SET V_RefundID=0;
			SELECT refund_id INTO V_RefundID FROM sales_refund WHERE src_no=V_RefundNO AND shop_id=P_ShopID AND type=1 LIMIT 1;
			IF V_RefundID THEN
				SET V_Status=80;
				IF V_RefundStatus=2 THEN
					SET V_Status=20;
				ELSEIF V_RefundStatus=3 THEN
					SET V_Status=60;
				ELSEIF V_RefundStatus=4 THEN
					SET V_Status=60;
				END IF;

				UPDATE sales_refund SET process_status=V_Status,status=V_RefundStatus WHERE refund_id=V_RefundID;
				-- 日志
				INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,2,P_OperatorID,'平台同意退款');
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
		END IF;
		
		IF V_RefundStatus>2 THEN
			-- 创建临时退款单
			INSERT IGNORE INTO sales_tmp_refund_order(shop_id, oid) VALUES(P_ShopID, V_Oid);
		END IF;
	END LOOP;
	CLOSE refund_order_cursor;
		
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_REFRESH_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_REFRESH_TRADE`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_RefreshFlag` INT, IN `P_ToStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_WarehouseID,V_WarehouseType, V_ShopID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,
		V_LogisticsID,V_DeliveryTerm,V_Max,V_Min,V_NewRefundStatus,V_NewLogisticsID,V_Locked,V_GoodsTypeCount,
		V_NoteCount,V_GiftMask,V_PackageID,V_SalesmanId,V_PlatformId,V_RemarkFlag,V_FlagId,V_BuyerMessageCount,
		V_CsRemarkCount,V_InvoiceType,V_TradeStatus,V_RawGoodsTypeCount, V_RawGoodsCount INT DEFAULT(0);
	DECLARE V_Addr,V_SrcTids,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(255);
	DECLARE V_BuyerMessage,V_CsRemark VARCHAR(1024);
	DECLARE V_AreaAlias,V_SingleSpecNO VARCHAR(40);
	DECLARE V_GoodsCount,V_Weight,V_PostCost,V_Paid,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GoodsCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4);
	
	-- P_RefreshFlag
	-- 1选择物流 2计算大头笔 4选择包装 8刷新备注
	
	-- 统计子订单
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0)),SUM(actual_num),COUNT(DISTINCT IF(actual_num<=0,NULL,sto.spec_id)),
		SUM(IF(actual_num>0,sto.weight,0)),SUM(IF(actual_num>0,paid,0)),MAX(IF(actual_num>0,delivery_term,1)),
		SUM(IF(actual_num>0,share_amount+discount,0)),SUM(IF(actual_num>0,share_post,0)),SUM(IF(actual_num>0,discount,0)),
		SUM(IF(actual_num>0,IF(delivery_term=1,share_amount+share_post,paid),0)),
		SUM(IF(actual_num>0,IF(delivery_term=2,share_amount+share_post-paid,0),0)),
		BIT_OR(IF(actual_num>0,gift_type,0)),SUM(IF(actual_num>0,commission,0)),SUM(actual_num*gs.length*gs.width*gs.height)
	INTO V_Max,V_Min,V_GoodsCount,V_GoodsTypeCount,V_Weight,V_Paid,V_DeliveryTerm,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
	FROM sales_trade_order sto LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id  WHERE sto.trade_id=P_TradeID;	
	
	-- 退款状态
	IF V_GoodsCount<=0 THEN
		SET V_NewRefundStatus=IF(V_Max,3,4);
		SET P_ToStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_NewRefundStatus=1;
	ELSEIF V_Max THEN
		SET V_NewRefundStatus=2;
	ELSE
		SET V_NewRefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	IF @cfg_sales_raw_count_exclude_gift IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	END IF;
	
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_RawGoodsTypeCount, V_RawGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM sales_trade_order
	WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_RawGoodsCount IS NULL THEN
		 SET V_RawGoodsCount=0;
	END IF;

	IF V_RawGoodsTypeCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM sales_trade_order 
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- V_WmsType, V_WarehouseNO, V_ShopID, V_TradeID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict;
	SELECT trade_status,warehouse_type, warehouse_id,shop_id,logistics_id,post_cost,receiver_province,receiver_city,receiver_district,receiver_address,receiver_dtb,package_id
	INTO V_TradeStatus,V_WarehouseType, V_WarehouseID,V_ShopID,V_LogisticsID,V_PostCost,V_ReceiverProvince,V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_AreaAlias,V_PackageID
	FROM sales_trade
	WHERE trade_id = P_TradeID;
	
	/*
	-- 订单未审核
	IF V_TradeStatus<35 THEN
		-- 包装
		IF P_RefreshFlag & 4  THEN 
			CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_Weight,V_TotalVolume);

			IF V_PackageID THEN
				SELECT weight INTO V_PackageWeight  FROM goods_spec WHERE spec_id = V_PackageID;
				SET V_Weight=V_Weight + V_PackageWeight;

			END IF;
		END IF;

		-- 选择物流
		IF P_RefreshFlag & 1 THEN
			CALL I_DL_DECIDE_LOGISTICS(V_NewLogisticsID, -1, V_DeliveryTerm, V_ShopID, V_WarehouseID,V_Weight, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_Addr);
			IF V_LogisticsID<>V_NewLogisticsID AND V_NewLogisticsID>0 THEN
				SET V_LogisticsID=V_NewLogisticsID;
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		END IF;
		
		-- 估算邮费
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_Weight, V_LogisticsID, V_ShopID, V_WarehouseID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		
		
	END IF;
	*/
	-- 估算货品成本
	SELECT TRUNCATE(IFNULL(SUM(sto.actual_num*IFNULL(ss.cost_price,0)),0),4) INTO V_GoodsCost FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID AND ss.spec_id=sto.spec_id
	WHERE sto.trade_id=P_TradeID AND sto.actual_num>0;
	-- SET V_AreaAlias = '';
	-- 便签数量
	-- SELECT COUNT(1) INTO V_NoteCount FROM common_order_note WHERE type=1 AND order_id=P_TradeID;
	
	SET @old_sql_mode=@@SESSION.sql_mode;
	SET SESSION sql_mode='';
	SELECT IFNULL(LEFT(GROUP_CONCAT(IF(ax.platform_id OR  ax.trade_from=3 OR ax.trade_from=5,ax.tid,NULL)),255),''),MAX(ax.x_salesman_id),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.buyer_message)='',NULL,TRIM(ax.buyer_message))),1024),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.remark)='',NULL,TRIM(ax.remark))),1024),''),
		MAX(ax.platform_id),
		MAX(ax.remark_flag),
		MAX(ax.x_trade_flag),
		SUM(IF(TRIM(ax.buyer_message)='',0,1)),
		SUM(IF(TRIM(ax.remark)='',0,1)),
		MAX(ax.invoice_type),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_title)='',NULL,TRIM(ax.invoice_title))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_content)='',NULL,TRIM(ax.invoice_content))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.pay_account)='',NULL,TRIM(ax.pay_account))),128),'')
	INTO
		V_SrcTids, V_SalesmanId, V_BuyerMessage, V_CsRemark, V_PlatformId, V_RemarkFlag, V_FlagId,
		V_BuyerMessageCount, V_CsRemarkCount, V_InvoiceType, V_InvoiceTitle, V_InvoiceContent,V_PayAccount
	FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=P_TradeID) sto
		LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
	
	SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
	
	IF V_PlatformId IS NULL THEN
		UPDATE sales_trade
		SET buyer_message_count=NOT FN_EMPTY(buyer_message),
			cs_remark_change_count=NOT FN_EMPTY(cs_remark),
			cs_remark_count=NOT FN_EMPTY(cs_remark),
			refund_status=V_NewRefundStatus,
			goods_count=V_GoodsCount,
			goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,
			post_amount=V_PostAmount,
			discount=V_Discount,
			receivable=V_GoodsAmount+V_PostAmount-V_Discount,
			dap_amount=V_DapAmount,
			cod_amount=(V_CodAmount+ext_cod_fee),
			warehouse_id=V_WarehouseID,
			trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
			logistics_id=V_LogisticsID,
			post_cost=V_PostCost,
			goods_cost=V_GoodsCost,
			receiver_dtb=V_AreaAlias,
			weight=V_Weight,
			volume=V_TotalVolume,
			delivery_term=V_DeliveryTerm,
			package_id = V_PackageID,
			paid=V_Paid,
			commission=V_Commission,
			profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
			note_count=V_NoteCount,
			gift_mask=V_GiftMask,
			version_id=version_id+1
		WHERE trade_id=P_TradeID;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新订单
	UPDATE sales_trade
	SET platform_id=V_PlatformId,
		src_tids=V_SrcTids,
		buyer_message=V_BuyerMessage,
		cs_remark=IF(NOT (cs_remark_change_count&2) OR (P_RefreshFlag&8),V_CsRemark,cs_remark),
		buyer_message_count=V_BuyerMessageCount,
		cs_remark_count=GREATEST(V_CsRemarkCount,NOT FN_EMPTY(cs_remark)),
		remark_flag=V_CsRemarkCount,
		flag_id=IF(flag_id=0,V_FlagId,flag_id),
		invoice_type=IF(invoice_type=0,V_InvoiceType,invoice_type),
		invoice_title=IF(invoice_title='',V_InvoiceTitle,invoice_title),
		invoice_content=IF(invoice_content='',V_InvoiceContent,invoice_content),
		salesman_id=IF(salesman_id,salesman_id,V_SalesmanId),
		refund_status=V_NewRefundStatus,
		goods_count=V_GoodsCount,
		goods_type_count=V_GoodsTypeCount,
		goods_amount=V_GoodsAmount,
		post_amount=V_PostAmount,
		discount=V_Discount,
		receivable=V_GoodsAmount+V_PostAmount-V_Discount,
		dap_amount=V_DapAmount,
		cod_amount=(V_CodAmount+ext_cod_fee),
		warehouse_id=V_WarehouseID,
		trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
		logistics_id=V_LogisticsID,
		post_cost=V_PostCost,
		goods_cost=V_GoodsCost,
		receiver_dtb=V_AreaAlias,
		weight=V_Weight,
		volume=V_TotalVolume,
		delivery_term=V_DeliveryTerm,
		package_id = V_PackageID,
		paid=V_Paid,
		commission=V_Commission,
		profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
		note_count=V_NoteCount,
		gift_mask=V_GiftMask,
		pay_account = V_PayAccount,
		raw_goods_type_count=V_RawGoodsTypeCount,
		raw_goods_count=V_RawGoodsCount,
		single_spec_no=V_SingleSpecNO,
		version_id=version_id+1
	WHERE trade_id=P_TradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SELECT_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SELECT_GIFT`(INOUT `P_priority` INT, IN `P_rule_id` INT,IN `P_rule_multiple_type` INT, IN `P_real_multiple` INT , IN `P_real_limit` INT , IN `P_total_name_num` INT, IN `P_total_cs_remark_num` INT,IN `P_limit_gift_stock` DECIMAL(19,4))
    SQL SECURITY INVOKER
    COMMENT '按赠品的库存优先级来选择赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,VS_spec_id,VS_is_suite,VS_gift_num,VS_real_gift_num,VS_send INT DEFAULT(0);
	DECLARE VS_Stock DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE send_cursor CURSOR FOR SELECT  spec_id,is_suite,gift_num
		FROM  cfg_gift_send_goods  
		WHERE rule_id=P_rule_id AND priority=P_priority ;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_priority=11;
	PRIORITY_LABEL: LOOP
		IF P_priority=15 THEN 
			SET P_priority=99;
			LEAVE MAIN_LABEL;
		END IF;
		SET VS_send = 0;
		OPEN send_cursor;
		SEND_LABEL: LOOP
			FETCH send_cursor INTO VS_spec_id,VS_is_suite,VS_gift_num;
			
			IF V_NOT_FOUND = 1 THEN
				SET V_NOT_FOUND = 0;
				IF VS_send THEN
					close send_cursor;
					leave MAIN_LABEL;
				ELSE
					SET P_priority=P_priority+1;
					CLOSE send_cursor;
					ITERATE PRIORITY_LABEL;
				END IF;
				
			END IF;
			
			IF VS_is_suite=0 THEN
				SELECT IFNULL(SUM(stock_num-order_num-sending_num),0) INTO VS_Stock FROM stock_spec WHERE spec_id=VS_spec_id;	
			ELSE
				SELECT SUM(tmp.suite_stock) INTO VS_Stock FROM (
				SELECT FLOOR(IFNULL(MIN(IFNULL(stock_num-order_num-sending_num, 0)/gsd.num),0)) AS suite_stock 
				FROM  goods_suite_detail gsd 
				LEFT JOIN  stock_spec ss ON ss.spec_id=gsd.spec_id 
				WHERE gsd.suite_id=VS_spec_id GROUP BY ss.warehouse_id
				) tmp;
			END IF;
			
			SET VS_real_gift_num=VS_gift_num;
			/*
			SET VS_real_gift_num=0;
			
			IF P_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=P_total_cs_remark_num;
				
			ELSEIF P_total_name_num>0 THEN 
				SET VS_real_gift_num=P_total_name_num;
				
			ELSE
				
				IF P_rule_multiple_type=0 THEN 
					IF P_real_multiple<>10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				ELSE
					IF P_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			IF VS_Stock-P_limit_gift_stock<VS_real_gift_num THEN
				SET P_priority=P_priority+1;
				SET VS_send = 0;
				CLOSE send_cursor;
				ITERATE PRIORITY_LABEL;
			ELSE
				SET VS_send = 1;
			END IF;
			
		END LOOP;
		CLOSE send_cursor;
		LEAVE MAIN_LABEL;
	END LOOP; 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SEND_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SEND_GIFT`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_CustomerID` INT, INOUT `P_SendOK` INT)
    SQL SECURITY INVOKER
    COMMENT '计算赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	
	/*使用变量*/
	DECLARE VS_sel_rule_group,VS_spec_match,VS_suite_match,
		VS_class_num,VS_suite_class_num,VS_spec_class_num,
		VS_brand_num,VS_spec_brand_num,VS_suite_brand_num,
		
		VS_brand_multiple_num,VS_spec_brand_multiple_num,VS_suite_brand_multiple_num,VS_brand_mutiple,
		VS_class_multiple_num,VS_spec_class_multiple_num,VS_suite_class_multiple_num,VS_class_mutiple,
		
		VS_specify_mutiple,VS_real_multiple,VS_real_limit,VS_priority,
		
		VS_keyword_len,VS_begin ,VS_end,VS_num,VS_total_cs_remark_num,VS_total_name_num,
		VS_real_gift_num,VS_rec_id,V_Exists,V_First,VS_cur_count,VS_cur_rule,VS_receivable_mutiple INT DEFAULT(0) ;
	
	DECLARE VS_pos TINYINT DEFAULT(1);
	DECLARE V_ApiTradeID BIGINT DEFAULT(0);
	
	DECLARE 
		VS_class_amount,VS_suite_class_amount,VS_spec_class_amount,
		VS_brand_amount,VS_spec_brand_amount,VS_suite_brand_amount,VS_post_cost
		DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VS_type,VT_delivery_term TINYINT DEFAULT(1);
	
	
	/*子订单变量*/
	DECLARE VTO_spec_id,VTO_suite_id,VTO_num,VTO_suite_num,VTO_share_amount INT ;
	DECLARE VT_trade_no,VTO_goods_name,VTO_spec_name VARCHAR(150) DEFAULT('');
	
	/*订单变量*/
	DECLARE VT_shop_id,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,VT_remark_flag,
		VT_receiver_province,VT_receiver_city,VT_receiver_district INT ;
	
	DECLARE VS_NOW,VT_trade_time,VT_pay_time,V_start_time,V_end_time DATETIME;
	DECLARE VT_goods_amount,VT_post_amount,VT_discount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VT_buyer_message,VT_cs_remark,V_ClassPath,VT_receiver_address VARCHAR(1024);
	
	/*规则列表变量*/
	DECLARE V_rule_type BIGINT DEFAULT(0) ;
	
	DECLARE V_send_spec_id,V_send_is_suite,V_send_gift_num INT DEFAULT(0);
	
	DECLARE V_rule_id,V_rule_priority,V_rule_group,V_rule_multiple_type,
		V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
		V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
		V_limit_customer_send_count,V_cur_gift_send_count,V_max_gift_send_count,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_breceivable_multiple,V_limit_receivable_count INT;
		
	DECLARE V_bbuyer_message,V_bcs_remark,V_time_type,V_is_enough_gift TINYINT;
	DECLARE V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,
		V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
		V_min_class_amount,V_max_class_amount,V_min_brand_amount,V_max_brand_amount,V_limit_gift_stock,V_receivable_multiple_amount DECIMAL(19, 4) DEFAULT(0.0000);
	DECLARE V_class_amount_type,V_brand_amount_type,V_terminal_type INT;
	DECLARE V_rule_no,V_rule_name,V_flag_type,V_shop_list,V_logistics_list,V_warehouse_list,V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,
		V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_buyer_message_key_word,V_addr_key_word VARCHAR(150);
	
	-- 赠品规则
	DECLARE rule_cursor CURSOR FOR SELECT  rec_id,rule_no,rule_name,rule_priority,rule_group,is_enough_gift,limit_gift_stock,rule_multiple_type,rule_type,bbuyer_message,bcs_remark,flag_type,time_type,start_time,end_time,shop_list,logistics_list,warehouse_list,
		min_goods_count,max_goods_count,min_goods_type_count,max_goods_type_count,min_specify_count,max_specify_count,min_class_count,max_class_count,class_count_type,min_brand_count,max_brand_count,brand_count_type,
		specify_count,bspecify_multiple,limit_specify_count,class_multiple_count,bclass_multiple,limit_class_count,class_multiple_type,brand_multiple_count,bbrand_multiple,limit_brand_count,brand_multiple_type,
		min_goods_amount,max_goods_amount,min_receivable,max_receivable,min_nopost_receivable,max_nopost_receivable,min_post_amount,max_post_amount,min_weight,max_weight,min_post_cost,max_post_cost,min_specify_amount,max_specify_amount,
		min_class_amount,max_class_amount,class_amount_type,min_brand_amount,max_brand_amount,brand_amount_type,
		buyer_rank,pay_start_time,pay_end_time,trade_start_time,trade_end_time,terminal_type,
		goods_key_word,spec_key_word,csremark_key_word,unit_key_word,limit_customer_send_count,cur_gift_send_count,max_gift_send_count,
		buyer_message_key_word,addr_key_word,min_no_specify_count,max_no_specify_count,buyer_class,receivable_multiple_amount,breceivable_multiple,limit_receivable_count  
		FROM  cfg_gift_rule rule 
		WHERE rule.is_disabled=0 ORDER BY rule_group,rule_priority desc;
	
	-- 子订单信息(单品)
	DECLARE trade_order_cursor1 CURSOR FOR SELECT spec_id,actual_num,share_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 and sto.suite_id=0;
	
	-- 子订单信息(组合装)
	DECLARE trade_order_cursor2 CURSOR FOR SELECT suite_id,suite_num,suite_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 AND sto.suite_id>0 group by sto.suite_id;
	
	
	-- 子订单名称信息(组合装名称只取一次)
	DECLARE trade_order_name_cursor CURSOR FOR SELECT distinct ato.goods_name,ato.spec_name
		FROM api_trade_order ato 
		LEFT JOIN sales_trade_order sto 
		ON (ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid) 
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0;
	
	
	-- 赠品数量范围
	DECLARE send_goods_cursor CURSOR FOR SELECT spec_id,gift_num,is_suite
		FROM cfg_gift_send_goods
		WHERE rule_id=V_rule_id AND priority=VS_priority;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET VS_NOW = NOW();	
		-- 订单信息
	SELECT trade_no,shop_id,trade_time,pay_time,goods_count,goods_type_count,customer_id,warehouse_id,logistics_id,
		receiver_province,receiver_city,receiver_district,buyer_message,cs_remark,remark_flag,
		goods_amount,post_amount,receivable,receivable-post_amount,weight,post_cost,delivery_term,receiver_address
	INTO VT_trade_no,VT_shop_id,VT_trade_time,VT_pay_time,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,
		VT_receiver_province,VT_receiver_city,VT_receiver_district,VT_buyer_message,VT_cs_remark,VT_remark_flag,
		VT_goods_amount,VT_post_amount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost,VT_delivery_term,VT_receiver_address
	FROM  sales_trade st
	WHERE st.trade_id=P_TradeID;
		
	IF V_NOT_FOUND = 1 THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 记录选中的分组
	SET @sys_code=0, @sys_message='OK';
	SET VS_sel_rule_group=-1;
	SET V_First=1;
	
	OPEN rule_cursor;
	GIFT_RULE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		SET VS_total_name_num =0;
		SET VS_total_cs_remark_num =0;
		SET VS_cur_count = 0;
		FETCH rule_cursor INTO V_rule_id,V_rule_no,V_rule_name,V_rule_priority,V_rule_group,V_is_enough_gift,V_limit_gift_stock,V_rule_multiple_type,V_rule_type,V_bbuyer_message,V_bcs_remark,V_flag_type,V_time_type,V_start_time,V_end_time,V_shop_list,V_logistics_list,V_warehouse_list,
			V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
			V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
			V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
			V_min_class_amount,V_max_class_amount,V_class_amount_type,V_min_brand_amount,V_max_brand_amount,V_brand_amount_type,
			V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,V_terminal_type,
			V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_limit_customer_send_count,V_cur_gift_send_count,
			V_max_gift_send_count,V_buyer_message_key_word,V_addr_key_word,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_receivable_multiple_amount,V_breceivable_multiple,V_limit_receivable_count;
		
		IF V_NOT_FOUND <> 0 THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		/*一个分组内只匹配一个赠品规则*/
		IF VS_sel_rule_group !=-1 AND VS_sel_rule_group=V_rule_group THEN
			ITERATE  GIFT_RULE_LABEL;
		END IF;
		
		/*此规则下没有设置赠品*/
		SELECT count(1) INTO VS_rec_id FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;
		
		-- VS_specify_mutiple VS_class_mutiple VS_brand_mutiple 
		-- 都满足的情况下VS_real_multiple来记录最小(大)的倍数关系
		
		IF V_rule_multiple_type=0 THEN 
			SET VS_real_multiple=10000;
			SET VS_real_limit=10000;
		ELSE 
			SET VS_real_multiple=-10000;
			SET VS_real_limit=-10000;	
		END IF;
	
		/*检查该赠品都设置了哪些条件*/
		
		/*检查订单是否满足用户设置的赠品条件*/
		
		/*买家留言*/
		/*IF (V_rule_type & 1) THEN
			IF  V_bbuyer_message THEN 
				IF  VT_buyer_message IS NOT NULL AND  VT_buyer_message<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_buyer_message IS  NULL OR  VT_buyer_message='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		/*客服备注*/
		/*IF V_rule_type & (1<<1) THEN
			IF  V_bcs_remark THEN
				IF  VT_cs_remark IS NOT NULL AND  VT_cs_remark<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_cs_remark IS  NULL OR  VT_cs_remark='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		
		/*淘宝标旗*/
		/*IF V_rule_type & (1<<2) THEN
			IF FIND_IN_SET(VT_remark_flag,V_flag_type)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;*/
		
		/*有效期*/
		IF V_rule_type & (1<<3) THEN
			IF V_time_type=1 AND VT_delivery_term=1 THEN 
				IF VT_pay_time<V_start_time OR VT_pay_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 3 OR VT_delivery_term=2 THEN
				IF VT_trade_time<V_start_time OR VT_trade_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 2 THEN
				IF VS_NOW<V_start_time OR VS_NOW>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;
		
		
		/*店铺*/
		IF V_rule_type & (1<<4) THEN
			IF FIND_IN_SET(VT_shop_id,V_shop_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		
		/*物流公司*/
		/*IF V_rule_type & (1<<5) THEN
			IF FIND_IN_SET(VT_logistics_id,V_logistics_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*仓库*/
		/*IF V_rule_type & (1<<6) THEN
			IF FIND_IN_SET(VT_warehouse_id,V_warehouse_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*货品总数*/
		-- 此处有问题，合并时未刷新货品
		IF V_rule_type & (1<<7) THEN
			IF V_max_goods_count=0 THEN
				IF  VT_goods_count<V_min_goods_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_count<V_min_goods_count OR VT_goods_count>V_max_goods_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
			
		/*货品种类*/
		/*IF V_rule_type & (1<<8) THEN
			IF V_max_goods_type_count=0 THEN
				IF  VT_goods_type_count<V_min_goods_type_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_type_count<V_min_goods_type_count OR VT_goods_type_count>V_max_goods_type_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*货款总额*/
		IF V_rule_type & (1<<15) THEN
			IF V_max_goods_amount=0 THEN
				IF  VT_goods_amount<V_min_goods_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_amount<V_min_goods_amount OR VT_goods_amount>V_max_goods_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
		/*实收(包含邮费)*/
		IF V_rule_type & (1<<16) THEN
			IF V_max_receivable=0 THEN
				IF  VT_receivable<V_min_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_receivable<V_min_receivable OR VT_receivable>V_max_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
			
		/*实收(去除邮费)*/
		/*IF V_rule_type & (1<<17) THEN
			IF V_max_nopost_receivable=0 THEN
				IF  VT_nopost_receivable<V_min_nopost_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_nopost_receivable<V_min_nopost_receivable OR VT_nopost_receivable>V_max_nopost_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*邮费*/
		/*IF V_rule_type & (1<<18) THEN
			IF V_max_post_amount=0 THEN
				IF  VT_post_amount<V_min_post_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VT_post_amount<V_min_post_amount OR VT_post_amount>V_max_post_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*预估重量*/
		/*IF V_rule_type & (1<<19) THEN
			IF V_max_weight=0 THEN
				IF  VT_weight<V_min_weight THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_weight<V_min_weight OR VT_weight>V_max_weight THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
			
		/*预估邮费成本*/
		/*IF V_rule_type & (1<<20) THEN
			IF V_max_post_cost=0 THEN
				IF  VT_post_cost<V_min_post_cost THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_post_cost<V_min_post_cost OR VT_post_cost>V_max_post_cost THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*客服备注关键字*/
		/*IF V_rule_type & (1<<30) THEN
			IF (VT_cs_remark IS NULL OR VT_cs_remark='') THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_csremark_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			ELSE 
				IF NOT LOCATE(V_csremark_key_word, VT_cs_remark) THEN
				-- IF (SELECT VT_cs_remark NOT LIKE CONCAT_WS('','%',V_csremark_key_word,'%')) THEN 
					ITERATE GIFT_RULE_LABEL;
				END IF;
			END IF;*/
			
			/*客服备注：AAA1支 2支BBB 1支AAA*/
			
			/*SET VS_keyword_len = CHARACTER_LENGTH(V_csremark_key_word);
			SET VS_pos = 1;
			SET VS_num=0;
			SET VS_total_cs_remark_num=0;
			SET VS_begin=0;
			SET VS_end=0;
			
			CS_REMARK_KEYWORD_LABEL:LOOP
				SET VS_begin = LOCATE(V_csremark_key_word, VT_cs_remark, VS_pos);
				IF VS_begin = 0 THEN
					LEAVE CS_REMARK_KEYWORD_LABEL;
				END IF;
				
				IF V_unit_key_word<>'' THEN 
					SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin - 1);
					IF VS_end > 0 AND VS_begin >VS_end THEN
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 2, 2));
						IF VS_num = 0 THEN
							SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 1, 1));
						END IF;
						
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos =VS_keyword_len+VS_begin;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					ELSE
						SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin);
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_begin + VS_keyword_len, VS_end - VS_begin - VS_keyword_len));
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos = VS_end;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					END IF;
				ELSE
					SET VS_total_cs_remark_num=VS_total_cs_remark_num+1;
					LEAVE CS_REMARK_KEYWORD_LABEL;	
				END IF;
			END LOOP; -- CS_REMARK_KEYWORD_LABEL
		END IF;*/
		
		
		/*
		指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		IF V_rule_type & (1<<9) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count AND tgto.num<=V_max_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类数量范围 注意组合装*/
		/*IF V_rule_type & (1<<10) THEN
			-- 未指定分类
			if V_class_count_type=0 then 
				ITERATE  GIFT_RULE_LABEL;
			end if;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_count_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_class_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_num=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_count=0 THEN
				IF  VS_class_num<V_min_class_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_num<V_min_class_count OR VS_class_num>V_max_class_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌数量范围 注意组合装*/
		
		/*IF V_rule_type & (1<<11) THEN
			-- 未指定品牌
			IF V_brand_count_type=0  THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_brand_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_count_type;
			
			IF VS_brand_num=0 THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_count=0 THEN
				IF  VS_brand_num<V_min_brand_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_brand_num<V_min_brand_count OR VS_brand_num>V_max_brand_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*
		指定货品数量备增 
		cfg_gift_attend_goods goods_type=2记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		如果是倍增关系需要计算出来倍数关系用于I_DL_SELECT_GIFT计算库存
		*/
		/*IF V_rule_type & (1<<12) THEN
			-- 参加活动的单品集合为空
			IF V_specify_count<=0 OR NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=2) THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(tgto.num),0) INTO VTO_num
			FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
			WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=2;
			
			SET VS_specify_mutiple=FLOOR(VTO_num/V_specify_count);
			IF VS_specify_mutiple =0 OR VS_specify_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bspecify_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
			
			
		/*指定分类数量倍增*/
		/*IF V_rule_type & (1<<13) THEN
			-- 未指定分类
			IF V_class_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_multiple_type;
			IF V_NOT_FOUND THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			SET VS_class_mutiple=FLOOR(VTO_num/V_class_multiple_count);
			IF VS_class_mutiple =0 OR VS_class_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bclass_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
		-- VS_class_mutiple,V_limit_class_count 传递给I_DL_SELECT_GIFT		
		
		
		
		/*指定品牌数量倍增*/
		/*IF V_rule_type & (1<<14) THEN
			-- 未指定品牌
			IF V_brand_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_multiple_type;
			
			SET VS_brand_mutiple=FLOOR(VTO_num/V_brand_multiple_count);
			IF VS_brand_mutiple =0 OR VS_brand_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bbrand_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
			
		-- VS_brand_mutiple,V_limit_brand_count 传递给I_DL_SELECT_GIFT
		
		/*
		指定货品金额范围 
		cfg_gift_attend_goods goods_type=3记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		组合装金额suie_amount 
		*/
		
		IF V_rule_type & (1<<21) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=3) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_amount=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount AND tgto.amount<=V_max_specify_amount LIMIT 1;
			END IF;
			
			-- 无满足条件的
			IF V_Exists =0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类金额范围 注意组合装*/
		/*IF V_rule_type & (1<<22) THEN
			-- 未指定分类
			IF V_class_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_amount_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_class_amount
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_amount=0 THEN
				IF  VS_class_amount<V_min_class_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_amount<V_min_class_amount OR VS_class_amount>V_max_class_amount THEN  
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌金额范围 注意组合装*/
		/*IF V_rule_type & (1<<23) THEN
			-- 未指定品牌
			IF V_brand_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_brand_amount
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_amount_type;
			
			IF VS_brand_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_amount=0 THEN
				IF  VS_brand_amount<V_min_brand_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VS_brand_amount<V_min_brand_amount OR VS_brand_amount>V_max_brand_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*客户地区*/
		/*IF V_rule_type & (1<<24) THEN
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_buyer_area WHERE rule_id=V_rule_id AND province_id=VT_receiver_province AND city_id=VT_receiver_city) THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		
		/*客户等级V_buyer_rank fixme P_CustomerID*/
		-- ELSEIF VS_type = 26 THEN
		-- ITERATE  GIFT_RULE_LABEL;
		
		/*付款时间*/
		/*IF V_rule_type & (1<<26) THEN 
			IF (DATE_FORMAT(VT_pay_time,'%H:%i:%s')<V_pay_start_time OR DATE_FORMAT(VT_pay_time,'%H:%i:%s')>V_pay_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*拍单时间*/
		/*IF V_rule_type & (1<<27)  THEN 
			IF (DATE_FORMAT(VT_trade_time,'%H:%i:%s')<V_trade_start_time OR DATE_FORMAT(VT_trade_time,'%H:%i:%s')>V_trade_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*终端类型*/
		/*IF V_rule_type & (1<<28)  THEN
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			IF V_terminal_type=2 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&1)=0) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_terminal_type=1 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&2)) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*宝贝关键字*/
		/*IF V_rule_type & (1<<29) THEN
			IF V_goods_key_word=''AND V_spec_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_goods_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.goods_name 
									LIKE CONCAT_WS('','%',V_goods_key_word,'%')
									) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_spec_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.spec_name 
									LIKE CONCAT_WS('','%',V_spec_key_word,'%')
								) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET VS_total_name_num=0;
			OPEN trade_order_name_cursor;
			NAME_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_name_cursor INTO VTO_goods_name,VTO_spec_name;
					IF V_NOT_FOUND <> 0 THEN
						LEAVE NAME_LABEL;
					END IF;
					
					IF V_goods_key_word<>'' AND V_spec_key_word='' AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word='' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word<>'' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
				END LOOP; -- NAME_LABEL
			CLOSE trade_order_name_cursor;
		END IF;*/

		/*指定赠送次数(适用于前多少名的赠送方式)*/
		/*IF V_rule_type & (1<<31) THEN
			IF V_max_gift_send_count AND V_cur_gift_send_count>=V_max_gift_send_count THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定赠品根据客户限送次数*/
		/*IF V_rule_type & (1<<32) THEN
			IF V_limit_customer_send_count THEN
				SELECT COUNT(1) INTO VS_cur_count FROM sales_gift_record  WHERE rule_id = V_rule_id AND customer_id = P_CustomerID AND created>=V_start_time AND created<=V_end_time;
				IF VS_cur_count >= V_limit_customer_send_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*指定买家留言关键词*/
		/*IF V_rule_type & (1<<33) THEN
			IF V_buyer_message_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_buyer_message = ''  OR VT_buyer_message IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_buyer_message_key_word, VT_buyer_message) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定收件人地址关键词*/
		/*IF V_rule_type & (1<<34) THEN
			IF V_addr_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_receiver_address = ''  OR VT_receiver_address IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_addr_key_word, VT_receiver_address) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		/*
		不送指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		/*IF V_rule_type & (1<<35) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_no_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count AND tgto.num<=V_max_no_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*客户分组送赠品*/
		/*IF V_rule_type & (1<<36) THEN
			IF  V_buyer_class = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT EXISTS(SELECT 1 FROM crm_customer WHERE customer_id = P_CustomerID AND class_id = V_buyer_class) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*订单实收(不包含邮费)倍增*/
		/*IF V_rule_type & (1<<37) THEN
			-- 查看
			IF V_receivable_multiple_amount = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET VS_receivable_mutiple=FLOOR(VT_nopost_receivable/V_receivable_multiple_amount);
			IF VS_receivable_mutiple =0 OR VS_receivable_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_breceivable_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
		
		-- citying

		/*订单满足赠品条件 根据优先级和库存确定赠品即VS_priority (倍增条件下考虑翻倍数量,数量从客服备注提取的情况下考虑库存)*/
		set V_NOT_FOUND=0;
		set VS_rec_id=0;

		SELECT COUNT(DISTINCT priority),IFNULL(priority,11)
		INTO VS_rec_id,VS_priority 
		FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;

		/*如果开启校验赠品库存,则都要去校验库存,否则的话则多个赠品列表的才去计算优先级*/
		IF  V_is_enough_gift THEN
			SET  VS_priority=11;
			CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,V_limit_gift_stock);
			-- IF VS_priority = 99 THEN
			IF VS_priority > 11 THEN -- 赠品库存数量不足时 VS_priority++ 目前没有做赠品优先级,只要有一个赠品不满足即不赠送货品
				SET  VS_priority=11;
				ITERATE GIFT_RULE_LABEL;
			END IF;
		/*ELSE
			--  指定多个赠品列表的情况下才去按库存计算优先级
			IF VS_rec_id>1 THEN 
				SET  VS_priority=11;
				CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,0);
				IF VS_priority = 99 THEN
					SET VS_priority = 11;
				END IF;
			END IF;*/
		END IF;
		
		/*添加赠品*/
		OPEN send_goods_cursor;
		SEND_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH send_goods_cursor INTO V_send_spec_id,V_send_gift_num,V_send_is_suite;
			-- 设置了规则 却没有设置赠品
			IF V_NOT_FOUND <> 0 THEN
				CLOSE send_goods_cursor;
				ITERATE GIFT_RULE_LABEL;
			END IF;

			-- 目前只按照赠品数量计算(没有客服备注提取、宝贝关键字计算、倍增)
			SET VS_real_gift_num=V_send_gift_num;
			
			-- 客服备注的优先级最高 名称提取其次
			-- VS_real_gift_num 是真正的赠送数量
			/*SET VS_real_gift_num=0;
			
			IF VS_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=VS_total_cs_remark_num;
				
			ELSEIF VS_total_name_num>0 THEN 
				SET VS_real_gift_num=VS_total_name_num;
				
			ELSE
				-- 有倍增关系 看是否大于VS_real_limit
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			CALL I_SALES_ORDER_INSERT(P_OperatorID, P_TradeID, 
				V_send_is_suite, V_send_spec_id, 1, VS_real_gift_num, 0, 0, 
				'自动赠品',
				CONCAT_WS ('',"自动添加赠品。使用策略编号：",V_rule_no,"策略名称：",V_rule_name), 
				V_ApiTradeID);
			
			-- 失败日志
			IF @sys_code THEN
				-- 回滚事务,否则下面日志无法保存
				ROLLBACK;
				-- 停用此赠品策略
				UPDATE cfg_gift_rule SET is_disabled=1 WHERE rec_id=V_rule_id;
				
				INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
				VALUES(60,P_TradeID,0,P_OperatorID,CONCAT('自动赠送失败,策略编号:', V_rule_no, ' 错误:', @sys_message),NOW());	
				
				INSERT INTO aux_notification(type,message,priority,order_type,order_no)
				VALUES(2,CONCAT('赠品策略异常: ', V_rule_no, ' 错误:', @sys_message, ' 订单:',VT_trade_no, ' 系统已自动停用此策略'), 
					9, 1, VT_trade_no);
				
				LEAVE SEND_GOODS_LABEL;
			ELSE
				IF VS_cur_rule <> V_rule_id THEN
					UPDATE cfg_gift_rule SET history_gift_send_count = history_gift_send_count +1,cur_gift_send_count = cur_gift_send_count +1
					WHERE rec_id=V_rule_id;
					INSERT INTO sales_gift_record(rule_id,trade_id,customer_id,created)
					values(V_rule_id,P_TradeID,VT_customer_id,NOW());
					SET VS_cur_rule = V_rule_id;
				END IF;
				SET P_SendOK=1;
			END IF;
			SET VS_sel_rule_group = V_rule_group;
		END LOOP; -- SEND_GOODS_LABEL
		CLOSE send_goods_cursor;
		
		IF @sys_code THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		
	END LOOP; -- GIFT_RULE_LABEL
	CLOSE rule_cursor;
	
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `I_DL_SYNC_MAIN_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_MAIN_ORDER`(IN `P_OperatorID` INT, IN `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ModifyFlag,V_DeliverTradeID,V_WarehouseID,
		V_NewWarehouseID,V_Locked,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsType,
		V_SalesOrderCount,V_Timestamp,V_DelayToTime,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,
		V_CustomerID,V_FlagID,V_IsMaster,V_RemarkFlag,V_Exists,
		V_ShopHoldEnabled,V_OldFreeze,V_PackageID,V_RemarkCount,V_GiftMask,V_UnmergeMask,
		V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_ApiGoodsCount,V_ApiOrderCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,
		V_DapAmount,V_CodAmount,V_PiAmount,
		V_Paid,V_SalesGoodsCount,V_TotalWeight,V_PostCost,
		V_GoodsCost,V_ExtCodFee,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_HasSendGoods,V_HasGift,V_PlatformID,V_ApiTradeStatus,V_TradeStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_InvoiceType,V_WmsType,V_NewWmsType,V_IsAutoWms,V_IsSealed,V_IsFreezed,V_IsPreorder,V_IsExternal TINYINT DEFAULT(0);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverRing VARCHAR(40);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	
	DECLARE V_SalesmanID,V_LogisticsID,V_TradeMask,V_OldLogisticsID INT;
	DECLARE V_Tid,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_ExtMsg,V_SingleSpecNO VARCHAR(40);
	DECLARE V_AreaAlias,V_BuyerEmail,V_BuyerNick,V_ReceiverName,V_ReceiverArea VARCHAR(60);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime DATETIME;
	DECLARE V_Remark,V_BuyerMessage VARCHAR(1024);
	
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
	START TRANSACTION;
	
	SELECT modify_flag,platform_id,tid,trade_status,refund_status,delivery_term,guarantee_mode,deliver_trade_id,pay_time,pay_account,
		receivable,goods_amount,post_amount,other_amount,dap_amount,cod_amount,pi_amount,ext_cod_fee,paid,discount,invoice_type,
		invoice_title,invoice_content,stockout_no,trade_mask,is_sealed,wms_type,is_auto_wms,warehouse_no,shop_id,logistics_type,
		buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_area,receiver_ring,receiver_address,
		receiver_zip,receiver_telno,receiver_mobile,remark_flag,remark,buyer_message,is_external
	INTO V_ModifyFlag,V_PlatformID,V_Tid,V_ApiTradeStatus,V_RefundStatus,V_DeliveryTerm,V_GuaranteeMode,V_DeliverTradeID,V_PayTime,V_PayAccount,
		V_Receivable,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_Discount,V_InvoiceType,
		V_InvoiceTitle,V_InvoiceContent,V_StockoutNO,V_TradeMask,V_IsSealed,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_ShopID,V_LogisticsType,
		V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverArea,V_ReceiverRing,V_ReceiverAddress,
		V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_RemarkFlag,V_Remark,V_BuyerMessage,V_IsExternal
	FROM api_trade WHERE rec_id=P_ApiTradeID FOR UPDATE;
	
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
					-- 记录备注
					INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
				
				IF V_IsPreorder THEN
					SET V_ExtMsg = ' 进预订单原因:客服备注提取';	
				END IF;
				
				-- 客户备注
				SET V_BuyerMessage=TRIM(V_BuyerMessage);
				CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
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
				
				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
				INTO V_Max,V_Min
				FROM tmp_sales_trade_order;
				
				-- 更新主订单退款状态
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4);
					SET V_TradeStatus=5;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
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
					SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
					FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
				ELSE
					SET V_SingleSpecNO='';
				END IF;
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
				IF V_NewWarehouseID = 0 THEN
					SELECT warehouse_id INTO V_NewWarehouseID FROM cfg_warehouse where is_disabled = 0 limit 1;
				END IF;
				-- 获取物流
				IF V_LogisticsID = 0 THEN
						IF V_DeliveryTerm=2 THEN
							SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						ELSE 
							SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						END IF;
				END IF;
				-- 估算货品成本
				SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_NewWarehouseID AND ss.spec_id=tsto.spec_id
				WHERE tsto.actual_num>0;
				SET V_AreaAlias = '';
				-- 更新订单
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
					logistics_id=V_LogisticsID,receiver_dtb=V_AreaAlias,flag_id=V_FlagID,salesman_id=V_SalesmanID,
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
					SELECT V_DeliverTradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,5,CONCAT('首付款:',V_Tid));
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
							
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(IF(V_ApiTradeStatus=80,'订单退款','订单关闭'),',拦截出库单'));
						
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
							-- 标记退款
							UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
			
			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid;
			UPDATE api_trade SET modify_flag=0,process_status=70 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=V_ShopID AND src_tid=V_Tid;
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
		
		-- 提取业务员
		CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
		IF V_SalesmanID THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				-- 加异常标记
				UPDATE sales_trade SET bad_reason=(bad_reason|16) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
			ELSEIF V_TradeStatus >= 40 AND V_TradeStatus < 95 AND @cfg_remark_change_block_stockout THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|64) WHERE stockout_id=V_StockoutID;
						-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'客服备注变化,拦截出库单');
				END IF;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT('客服备注变化,拦截出库:',V_Tid));
			ELSEIF V_TradeStatus >= 95 AND @cfg_remark_change_block_stockout THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化,订单已发货:',V_Tid));
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
			
			-- 看地址是否有变化
			IF EXISTS(SELECT 1 FROM sales_trade st,api_trade ax
				WHERE st.trade_id=V_DeliverTradeID AND ax.platform_id=V_PlatformID AND ax.tid=V_Tid
					AND st.receiver_name=ax.receiver_name
					AND st.receiver_province=ax.receiver_province
					AND st.receiver_city=ax.receiver_city
					AND st.receiver_district=ax.receiver_district
					AND st.receiver_address=ax.receiver_address
					AND st.receiver_mobile=ax.receiver_mobile
					AND st.receiver_telno=ax.receiver_telno
					AND st.receiver_zip=ax.receiver_zip
					AND st.receiver_area=ax.receiver_area
					AND st.receiver_ring=ax.receiver_ring
					AND st.to_deliver_time=ax.to_deliver_time
					AND st.dist_center=ax.dist_center
					AND st.dist_site=ax.dist_site) THEN
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('平台收件地址变更,系统已处理:',V_Tid));
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|4) WHERE stockout_id=V_StockoutID;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'收件地址变更,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
				
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更:',V_Tid));
				
				-- 刷新物流,大头笔,包装
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|3, 0);
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|2) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
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
			
			-- 拦截出库单
			IF V_TradeStatus>=40 THEN
				UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
				SET so.block_reason=(so.block_reason|8)
				WHERE sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id
					AND so.stockout_id=sod.stockout_id
					AND sto.trade_id=V_DeliverTradeID
					AND so.status<>5;
					
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
				-- 出库单日志??
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			ELSEIF V_TradeStatus<35 THEN
				UPDATE sales_trade st,api_trade ax 
				SET st.invoice_type=ax.invoice_type,
					st.invoice_title=ax.invoice_title,
					st.invoice_content=ax.invoice_content,
					st.version_id=st.version_id+1
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化:',V_Tid));
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
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
	UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SYNC_SUB_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_SUB_ORDER`(IN `P_OperatorID` INT,
	IN `P_RecID` BIGINT,
	IN `P_ModifyFlag` INT,
	IN `P_ApiTradeStatus` TINYINT,
	IN `P_ShopID` TINYINT,
	IN `P_Tid` VARCHAR(40),
	IN `P_Oid` VARCHAR(40),
	IN `P_RefundStatus` TINYINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_DeliverTradeID,V_WarehouseID,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,V_IsMaster,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_SalesGoodsCount,V_LeftSharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_HasSendGoods,V_TradeStatus TINYINT DEFAULT(0);
	
	DECLARE trade_order_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id
		FROM sales_trade_order sto LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=P_ShopID and sto.src_oid=P_Oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,'子订单退款,拦截出库单');
						END IF;
						
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
						
						UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
				
				-- 回收库存
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
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('子订单退款:',P_Oid));
				ELSE
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid));
				END IF;
				
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, V_IsMaster, 1, V_LeftSharePost);
		ELSEIF P_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=P_ShopID AND src_oid=P_Oid;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					SET V_NewRefundStatus=IF(V_Max,3,4);
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				UPDATE sales_trade SET refund_status=V_NewRefundStatus,version_id=version_id+1 WHERE trade_id=V_DeliverTradeID;
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
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, 2, 0);
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'平台修改货品,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
			END IF;
		
			UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid));
			
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		SET P_ModifyFlag = P_ModifyFlag & ~16;
	END IF;
	
	UPDATE api_trade_order SET modify_flag=0 WHERE rec_id=P_RecID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_GIFT_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_GIFT_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '新建订单货品插入的临时表,为赠品准备'
MAIN_LABEL:BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_gift_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  is_suite INT(11) NOT NULL,
	  spec_id INT(11) NOT NULL,
	  num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  class_path VARCHAR(1024) NOT NULL,
	  brand_id INT(11) NOT NULL,
	  from_mask INT(11) NOT NULL,
	  PRIMARY KEY (rec_id),
	  UNIQUE INDEX UK_tmp_gift_trade_order (is_suite, spec_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_SALES_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_SALES_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中建立的临时表'
MAIN_LABEL: BEGIN 
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sales_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  spec_id INT(11) NOT NULL,
	  shop_id smallint(6) NOT NULL,
	  platform_id tinyint(4) NOT NULL,
	  src_oid VARCHAR(40) NOT NULL,
	  suite_id INT(11) NOT NULL DEFAULT 0,
	  src_tid VARCHAR(40) NOT NULL,
	  gift_type TINYINT(1) NOT NULL DEFAULT 0,
	  refund_status TINYINT(4) NOT NULL DEFAULT 0,
	  guarantee_mode TINYINT(4) NOT NULL DEFAULT 1,
	  delivery_term TINYINT(4) NOT NULL DEFAULT 1,
	  bind_oid VARCHAR(40) NOT NULL DEFAULT '',
	  num DECIMAL(19, 4) NOT NULL,
	  price DECIMAL(19, 4) NOT NULL,
	  actual_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  order_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  adjust DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_post DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  paid DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  tax_rate DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
	  goods_name VARCHAR(255) NOT NULL,
	  goods_id INT(11) NOT NULL,
	  goods_no VARCHAR(40) NOT NULL,
	  spec_name VARCHAR(100) NOT NULL,
	  spec_no VARCHAR(40) NOT NULL,
	  spec_code VARCHAR(40) NOT NULL,
	  suite_no VARCHAR(40) NOT NULL DEFAULT '',
	  suite_name VARCHAR(255) NOT NULL DEFAULT '',
	  suite_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  is_print_suite TINYINT(1) NOT NULL DEFAULT 0,
	  api_goods_name VARCHAR(255) NOT NULL,
	  api_spec_name VARCHAR(40) NOT NULL,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  volume DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  commission DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  goods_type TINYINT(4) NOT NULL DEFAULT 1,
	  flag INT(11) NOT NULL DEFAULT 0,
	  large_type TINYINT(1) NOT NULL DEFAULT 0,
	  invoice_type TINYINT(4) NOT NULL DEFAULT 0,
	  invoice_content VARCHAR(255) NOT NULL DEFAULT '',
	  from_mask INT(11) NOT NULL DEFAULT 0,
	  cid INT(11) NOT NULL DEFAULT 0,
	  is_master TINYINT(1) NOT NULL DEFAULT 0,
	  is_allow_zero_cost TINYINT(1) NOT NULL DEFAULT 0,
	  remark VARCHAR(60) NOT NULL DEFAULT '',
	  PRIMARY KEY (rec_id),
	  INDEX IX_tmp_sales_trade_order_src_id (shop_id, src_oid),
	  UNIQUE INDEX UK_tmp_sales_trade_order (spec_id, shop_id, src_oid, suite_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_RESERVE_STOCK`;
DELIMITER //
CREATE PROCEDURE `I_RESERVE_STOCK`(IN `P_TradeID` INT, IN `P_Type` INT, IN `P_NewWarehouseID` INT, IN `P_OldWarehouseID` INT)
    SQL SECURITY INVOKER
    COMMENT '占用库存'
MAIN_LABEL:BEGIN
	IF P_OldWarehouseID THEN
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT P_OldWarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
			IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
			sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
		
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=P_TradeID;
	END IF;
	IF P_NewWarehouseID THEN
		IF P_Type = 2 THEN	-- 未付款库存
			INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num);
			
			UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 3 THEN	-- 已保留待审核
			INSERT INTO stock_spec(warehouse_id,spec_id,order_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num 
			FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num);
			
			UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 4 THEN	-- 待发货
			INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status)
			(SELECT P_NewWarehouseID,spec_id,actual_num,1 FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1;
			
			UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 5 THEN	-- 预订单库存
			INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num);
			
			UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		END IF;
	END IF;
	
	-- 更新平台货品库存变化
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0;
	
	-- 组合装
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
	
END//
DELIMITER ;

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
			LEFT JOIN api_trade_order ato ON ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid
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
	IF (V_TradePlatformId<>1 AND V_TradePlatformId<>2) OR @cfg_order_allow_part_sync=0 THEN
	
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

DROP PROCEDURE IF EXISTS `I_SALES_ORDER_INSERT`;
DELIMITER //
CREATE PROCEDURE `I_SALES_ORDER_INSERT`(
	IN `P_OperatorID` INT, 
	IN `P_TradeID` INT, 
	IN `P_bSuite` INT,
	IN `P_SpecID` INT,
	IN `P_GiftType` INT,
	IN `P_Num` DECIMAL(19,4),
	IN `P_ShareAmount` DECIMAL(19,4),
	IN `P_SharePost` DECIMAL(19,4),
	IN `P_GoodsRemark` VARCHAR(255),
	IN `P_Remark` VARCHAR(255),
	INOUT `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
    COMMENT '插入货品作为一个子订单'
MAIN_LABEL: BEGIN
	DECLARE V_Receivable,V_GoodsAmount,V_ApiGoodsCount DECIMAL(19,4);
	DECLARE V_PayStatus TINYINT DEFAULT(0);
	DECLARE V_Message VARCHAR(256);
	DECLARE V_OrderID,V_ShopID,V_ApiOrderCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Tid VARCHAR(40);
	DECLARE V_Now DATETIME;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 展开货品
	SET @sys_code=0;
	SET @sys_message="";
	
	IF P_GiftType THEN
		SET P_ShareAmount=0;
	END IF;
	
	SET V_Receivable=P_ShareAmount+P_SharePost;
	-- 插入原始单	
	-- IF P_Paid >= V_Receivable THEN
	-- 	SET V_PayStatus = 2; -- 已付款
	-- ELSEIF P_Paid=0 THEN
	-- 	SET V_PayStatus = 0; -- 未付款
	-- ELSE
	-- 	SET V_PayStatus = 1; -- 部分付款
	-- END IF;
	
	
	-- START TRANSACTION;
	SET V_Now = NOW();
	-- 查找是否存在一个手工建单
	IF P_ApiTradeID=0 THEN
		SELECT ax.rec_id,ax.tid INTO P_ApiTradeID,V_Tid  
		FROM sales_trade_order sto,api_trade ax 
		WHERE sto.trade_id=P_TradeID AND sto.platform_id=0 AND ax.platform_id=0 AND ax.tid=sto.src_tid AND V_Tid IS NOT NULL AND V_Tid<>'' LIMIT 1;
	END IF;
	
	-- 没找到，则手工新建一个
	IF P_ApiTradeID=0 THEN
		SET V_Tid = FN_SYS_NO("apitrade");
		
		INSERT INTO api_trade(platform_id, shop_id, tid, process_status, trade_status, guarantee_mode, pay_status, delivery_term, pay_method,
			order_count, goods_count, trade_time, pay_time,
			buyer_nick, buyer_name, buyer_area, pay_id, 
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address, 
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, receiver_hash,
			goods_amount, post_amount, discount, receivable, paid, received, 
			invoice_type, invoice_title, invoice_content, trade_from,created)
		SELECT 0, shop_id, V_Tid, 20, 30, 2, 2, 1, 1, 
			1, P_Num,  V_Now,  V_Now,
			buyer_nick, receiver_name, receiver_area, '',
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address,
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, 
			MD5(CONCAT(receiver_province,receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip)),
			0, 0, 0, 0, 0, 0,
			0, '', '', 2,V_Now 
		FROM  sales_trade
		WHERE trade_id=P_TradeID LIMIT 1;
		
		SET P_ApiTradeID = LAST_INSERT_ID();
	ELSE
		SELECT tid INTO V_Tid FROM api_trade WHERE rec_id=P_ApiTradeID;
	END IF;
	
	SELECT shop_id INTO V_ShopID FROM api_trade WHERE rec_id=P_ApiTradeID;
	-- 补原始子订单数据 
	IF P_bSuite=0 THEN
		SET @tmp_specno='',@tmp_goodsname='',@tmp_specname='';
		
		INSERT INTO api_trade_order(platform_id,shop_id, tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount,share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gg.goods_id,gg.goods_no,gs.spec_id, (@tmp_specno:=gs.spec_no),(@tmp_goodsname:=gg.goods_name),(@tmp_specname:=gs.spec_name),gs.spec_code,
			P_GiftType,P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,
			P_ShareAmount,P_SharePost,0,P_GoodsRemark,V_Now 
		FROM  goods_spec gs 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gs.spec_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='货品不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加单品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		END IF;
		
	ELSE 
		INSERT INTO api_trade_order(platform_id,shop_id , tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount, share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gs.suite_id,gs.suite_no,gs.suite_id,(@tmp_specno:=gs.suite_no),(@tmp_goodsname:=gs.suite_name),'','', P_GiftType,
			P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,P_ShareAmount,P_SharePost, 0, P_GoodsRemark, V_Now 
		FROM  goods_suite gs 
		WHERE gs.suite_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='组合装不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加货品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		END IF;
	END IF;
	
	-- 映射货品
	CALL I_DL_MAP_TRADE_GOODS(P_TradeID, P_ApiTradeID, 0, V_ApiOrderCount, V_ApiGoodsCount);
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*IF V_ApiOrderCount <> 1 OR V_ApiGoodsCount <> P_Num THEN
		SET @sys_code=7, @sys_message = '单品数量不一致';
		LEAVE MAIN_LABEL;
	END IF;*/
	
	UPDATE api_trade_order SET process_status=20 WHERE rec_id=V_OrderID;
	
	-- 日志
	INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
	VALUES(60,P_TradeID,P_SpecID,P_OperatorID,V_Message,V_Now);	
	
	-- 更新原始单金额数据
	UPDATE api_trade `at`,
		(
			SELECT SUM(share_amount+discount) goods_amount,
				SUM(share_post) post_amount,SUM(discount) discount
			FROM api_trade_order ato 
			WHERE platform_id=0 AND tid=V_Tid
		) da	
	SET 
		`at`.goods_amount =da.goods_amount,
		`at`.post_amount =da.post_amount,
		`at`.discount =da.discount,
		`at`.receivable=V_Receivable,
		`at`.modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	END IF;
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		IF @cfg_sales_trade_trace_operator THEN
			SELECT fullname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_IMPLEMENT_CLEAN`;
DELIMITER //
CREATE PROCEDURE SP_IMPLEMENT_CLEAN(IN P_CleanId INT)
  SQL SECURITY INVOKER
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;

	-- 清空账款信息和统计信息
	IF P_CleanId <> 6 AND P_CleanId <> 7 THEN


		-- 统计
-- 		TODO 统计的部分表在做完统计模块后需要打开

		DELETE  FROM stat_daily_sales_amount;

 		DELETE  FROM stat_monthly_sales_amount;

	END IF;
	-- 全清(货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关)
	IF P_CleanId = 1 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;
		
		-- crm

		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;
		-- purchase
		DELETE FROM purchase_order_log;
		DELETE FROM purchase_order_detail;
		DELETE FROM purchase_order;
		-- goods
      
		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;
		DELETE FROM goods_log;

		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;

		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		
		
		-- stock

		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		-- 关联表
		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		
		
		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;
		--  清除库存同步记录
		DELETE FROM api_stock_sync_record;

		-- 通知消息 new add
		DELETE FROM sys_notification;


		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM cfg_employee_rights WHERE employee_id > 1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		--  订单全链路
		DELETE FROM sales_trade_trace;
		-- 客服备注修改历史记录  new add
		DELETE FROM api_trade_remark_history;
		-- 订单备注提取策略 new add
		DELETE FROM cfg_trade_remark_extract;
		-- cfg
		DELETE FROM cfg_stock_sync_rule;

		-- sys
		DELETE FROM sys_other_log;
		DELETE FROM sys_process_background;
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 1, '清除系统所有信息');
	END IF;
	-- 清除货品信息(清除：订单、库存、事务，保留客户、员工信息）
	IF P_CleanId = 2 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;





		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;

		DELETE FROM goods_log;



		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;


		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;


		-- stock

		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;


		DELETE FROM stockin_order_detail;

		DELETE FROM stockin_order;


		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 2, '清除货品信息(清除：订单、库存，保留客户、员工信息)');
	END IF;
	-- 清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工)
	IF P_CleanId = 3 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;

		-- crm
		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;






		-- stock

 		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 3, '清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工信息)');
	END IF;
	-- 清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户、供货商信息)
	IF P_CleanId = 4 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;




		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;




		-- hr
		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 4, '清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户信息)');
	END IF;
	-- 清除订单、采购信息、库存调拨等相关库存订单信息(库存量由脚本重刷)
	IF P_CleanId = 5 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;

		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;
		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		DELETE FROM stock_spec_detail;
-- zhuyi1
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0,purchase_num=0,
			to_purchase_num=0,purchase_arrive_num=0,refund_num=0,transfer_num=0,return_num=0,return_exch_num=0,
			return_onway_num=0,refund_exch_num=0,refund_onway_num=0,default_position_id=IF(default_position_id=0,-warehouse_id,default_position_id);
		-- INSERT INTO stock_spec_detail(stock_spec_id,spec_id,stockin_detail_id,position_id,position_no,zone_id,zone_no,cost_price,stock_num,virtual_num,created)
		--	SELECT ss.rec_id,ss.spec_id,0,ss.default_position_id,cwp.position_no,cwz.zone_id,cwz.zone_no,ss.cost_price,ss.stock_num,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
 		-- INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,created)
		--	SELECT ss.warehouse_id,ss.spec_id,ss.default_position_id,cwz.zone_id,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;

		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 5, ' 清除订单、采购、盘点、等相关库存信息');
	END IF;


	-- 清除订单信息
	IF P_CleanId = 8 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api  删除原始订单和退换单
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		-- sales 删除原始订单和退换单
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

	
		-- 销售出库单未在stock_change_history里插入数据的都可以删除，在stock_change_history里插入数据的将入库类型改为其他入库
		UPDATE stockout_order so,stockout_order_detail sod,stock_change_history sch
			SET so.src_order_type=7,so.src_order_id=0,so.src_order_no='',sod.src_order_type=7 ,sod.src_order_detail_id=0,
			sch.src_order_type=7, sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=1 AND so.stockout_id=sod.stockout_id
			AND so.stockout_id=sch.stockio_id AND sch.type=2;

		DELETE sodp.* FROM stockout_order_detail_position sodp,stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND sod.rec_id=stockout_order_detail_id AND so.src_order_type=1 ;

		-- 删除未出库的出库单管理的stockout_pack_order,stockout_pack_order_detail 必须先删 有外键
-- 		DELETE spod.*  FROM stockout_pack_order spo,stockout_pack_order_detail spod,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id AND spo.pack_id=spod.pack_id AND so.src_order_type=1;

-- 		DELETE spo.*  FROM stockout_pack_order spo,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id  AND so.src_order_type=1;

		-- 删除未出库的出库单和出库单详情
		DELETE sod.* FROM stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND so.src_order_type=1 ;

		DELETE so.* FROM stockout_order so WHERE so.src_order_type=1 ;
		-- 清空打印批次相关的数据


		-- stockin
		-- 将退货入库的入库单改成其他入库
		UPDATE stockin_order so,stockin_order_detail sod,stock_change_history sch
			SET so.src_order_type=6,so.src_order_id=0,so.src_order_no='',sod.src_order_type=6,sod.src_order_detail_id=0,
			sch.src_order_type=6,sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  AND so.stockin_id=sch.stockio_id
			AND sch.type=1;

		-- 删除未入库的入库单和入库单详情
		DELETE sod.* FROM stockin_order so,stockin_order_detail sod
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  ;

		DELETE so.* FROM stockin_order so WHERE so.src_order_type=3 ;
		-- stock
		-- 将stock_spec中的未付款量，预订单量，待审核量，待发货量清0    销售退货量 销售换货在途量（发出和收回）这三个暂时没用 所以没有清0
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0;
		-- 将stock_spec_detail中的占用量清0
		UPDATE stock_spec_detail SET reserve_num=0,is_used_up=0;
		-- 删除日志表中有关订单操作的日志
		DELETE FROM stock_inout_log WHERE order_type=2 AND operate_type IN(1,2,3,4,7,14,23,24,51,52,62,63,111,113,120,121,300);
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message) VALUES(13, P_UserId, 8,'清除订单信息，和订单相关的出库单，入库单的类型变为其他出库，其他入库');
		-- -- stockout_order 中的字段consign_status,customer_id等没有用了，
	END IF;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `SP_INT_ARR_TO_TBL`;
DELIMITER //
CREATE PROCEDURE `SP_INT_ARR_TO_TBL`(IN `P_Str` VARCHAR(8192), IN `P_Clear` INT)
    SQL SECURITY INVOKER
    COMMENT '将字符串数组插入到临时表，如1,2,4,2'
MAIN_LABEL:BEGIN
	DECLARE V_I1, V_I2, V_I3 BIGINT;
	DECLARE V_IT VARCHAR(255);
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_xchg(
		rec_id int(11) NOT NULL AUTO_INCREMENT,
		f1 VARCHAR(40),
		f2 VARCHAR(1024),
		f3 VARCHAR(40),
		PRIMARY KEY (rec_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	IF P_Str IS NULL OR LENGTH(P_Str)=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_Clear THEN
		DELETE FROM tmp_xchg;
	END IF;
	
	IF P_Str=' ' THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_I1 = 1;
	STR_LABEL:LOOP
	 SET V_I2 = locate(',', P_Str, V_I1);
	 IF V_I2 = 0 THEN
	   SET V_IT = substring(P_Str, V_I1);
	 ELSE
	   SET V_IT = substring(P_Str, V_I1, V_I2 - V_I1);
	 END IF;
	 
	 IF V_IT IS NOT NULL THEN
		set V_I3 = cast(V_IT as signed);
		INSERT INTO tmp_xchg(f1) VALUES(V_I3);
	 END IF;
	 
	 IF V_I2 = 0 OR V_I2 IS NULL THEN
	   LEAVE STR_LABEL;
	 END IF;
	
	 SET V_I1 = V_I2 + 1;
	END LOOP;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_ALL`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_ALL`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	DECLARE V_LockName, V_TradeNO, V_BuyerNick VARCHAR(40);
	
	DECLARE trade_cursor CURSOR FOR SELECT rec_id,tid,buyer_nick FROM api_trade WHERE process_status=10 AND bad_reason=0 AND platform_id>0 LIMIT 100;
	-- DECLARE refund_cursor CURSOR FOR SELECT refund_id FROM api_refund WHERE process_status=0 LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
		RESIGNAL;
	END;
	
	-- 统计递交的订单数
	SET @tmp_delivered_count = 0;
	
	SET V_LockName = CONCAT('deliver_lock_', DATABASE());
	IF NOT IS_FREE_LOCK(V_LockName) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT GET_LOCK(V_LockName, 1) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET @cur_uid = P_OperatorID;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	IF @cfg_order_auto_submit THEN
		OPEN trade_cursor;
		TRADE_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				IF V_TradeCount >= 100 THEN
					-- 需要测试，改成1测试
					SET V_TradeCount = 0;
					CLOSE trade_cursor;
					OPEN trade_cursor;
					ITERATE TRADE_LABEL;
				END IF;
				LEAVE TRADE_LABEL;
			END IF;
			
			SET V_TradeCount = V_TradeCount+1;
			
			CALL I_DL_DELIVER_API_TRADE(V_TradeID, P_OperatorID);
			
			IF @sys_code = 0 THEN
				SET @tmp_delivered_count = @tmp_delivered_count+1;
			ELSEIF @sys_code<>2 THEN	-- 是指此订单已经递交过
				INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
			END IF;
		END LOOP;
		close trade_cursor;
	END IF;
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(P_OperatorID);

	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 19);
	SET @tmp_to_preorder_count = @tmp_to_process_count;
	
	-- 待审核订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 20);
	SET @tmp_to_check_count = @tmp_to_process_count;
	*/
	-- 递交退款单
	/*
	SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, P_OperatorID);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
		END IF;
	END LOOP;
	close refund_cursor;
	*/
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
	-- 解锁
	SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_SOME`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_SOME`(IN `P_TradeIDs` VARCHAR(1024))
    SQL SECURITY INVOKER
    COMMENT '根据指定的原始单ID进行递交'
BEGIN
	DECLARE V_TradeNO,V_BuyerNick VARCHAR(40);
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	
	DECLARE trade_cursor CURSOR FOR select ax.rec_id,tid,buyer_nick 
		from tmp_xchg tx,api_trade ax 
		where ax.rec_id=tx.f1;
	
	-- DECLARE refund_cursor CURSOR FOR select refund_id from api_refund where process_status=0 LIMIT 500;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
		RESIGNAL;
	END;
		
	CALL SP_INT_ARR_TO_TBL(P_TradeIDs, 1);
	
	-- 清除无效货品标记
	UPDATE tmp_xchg tx,api_trade ax 
	SET bad_reason=(bad_reason&~1)
	WHERE ax.rec_id=tx.f1;
	
	UPDATE tmp_xchg tx,api_trade ax,api_trade_order ato 
	SET ato.is_invalid_goods=0
	WHERE ax.rec_id=tx.f1 AND ato.platform_id=ax.platform_id AND ato.tid=ax.tid;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	OPEN trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			LEAVE TRADE_LABEL;
		END IF;
		
		CALL I_DL_DELIVER_API_TRADE(V_TradeID, @cur_uid);
		
		IF @sys_code = 0 THEN
			SET @tmp_delivered_count = @tmp_delivered_count+1;
		ELSEIF @sys_code<>1 THEN	-- 1是指此订单已经递交过
			INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE trade_cursor;
	
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(@cur_uid);
	
	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 19);
	-- 待审核订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 20);
	*/
	
	-- 递交退款单
	/*SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 500 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, @cur_uid);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
			INSERT INTO tbl_deliver_error(tid, error_code, error_info) values(V_RefundID, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE refund_cursor;*/
	
	-- CALL I_DL_SYNC_REFUND(P_OperatorID);
	
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_CHAR`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_CHAR`(IN `P_Key` VARCHAR(60), OUT `P_Val` VARCHAR(256), IN `P_Def` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_INT`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_INT`(IN `P_Key` VARCHAR(60), OUT `P_Val` INT, IN `P_Def` INT)
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;





DROP FUNCTION IF EXISTS `FN_CALC_SUB_POST_FEE`;
DELIMITER //
CREATE FUNCTION `FN_CALC_SUB_POST_FEE`(`P_TruncMode` INT, `P_Weight` DECIMAL(19,4), `P_PrevWeightStep` DECIMAL(19,4), `P_WeightStep` DECIMAL(19,4), `P_UnitStep` DECIMAL(19,4), `P_PriceStep` DECIMAL(19,4)) RETURNS decimal(19,4)
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT '计算一个区间邮费'
BEGIN
	DECLARE V_Postage, V_IncWeight  DECIMAL(19,4) DEFAULT 0;
	
	IF P_UnitStep <= 0 THEN
		RETURN 0;
	END IF;
	
	IF P_WeightStep>0 AND P_Weight > P_WeightStep THEN
		SET V_IncWeight=P_WeightStep-P_PrevWeightStep;
	ELSE
		SET V_IncWeight=P_Weight-P_PrevWeightStep;
	END IF;
	
	
	IF P_TruncMode=2 THEN 
		SET V_Postage = TRUNCATE(FLOOR(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSEIF P_TruncMode=3 THEN 
		SET V_Postage = TRUNCATE(ROUND(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSEIF P_TruncMode=4 THEN 
		SET V_Postage = TRUNCATE((V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSE
		SET V_Postage = TRUNCATE(CEIL(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	END IF;
	
	RETURN V_Postage;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_EMPTY`;
DELIMITER //
CREATE FUNCTION `FN_EMPTY`(`P_Str` VARCHAR(1024)) RETURNS tinyint(4)
    NO SQL
    SQL SECURITY INVOKER
    DETERMINISTIC
BEGIN
	IF P_Str IS NULL OR P_Str = '' THEN
		RETURN 1;
	END IF;
	
	RETURN 0;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_GOODS_NO`;
DELIMITER //
CREATE FUNCTION `FN_GOODS_NO`(`P_Type` INT, `P_TargetID` INT) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
    COMMENT '查询货品或组合装信息'
BEGIN
	DECLARE V_GoodsNO VARCHAR(40);
	
	SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',
		@tmp_goods_id='',@tmp_spec_id='',@tmp_barcode='',@tmp_retail_price=0;-- ,@tmp_sn_enable=0
	
	IF P_Type=1 THEN
		SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name,gs.spec_code,gg.goods_id,gs.spec_id,gs.barcode,gs.retail_price -- gs.is_sn_enable,
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,V_GoodsNO,@tmp_spec_name,@tmp_spec_code,@tmp_goods_id,@tmp_spec_id,@tmp_barcode,@tmp_retail_price -- ,@tmp_sn_enable
		FROM goods_spec gs,goods_goods gg WHERE gs.spec_id=P_TargetID AND gs.goods_id=gg.goods_id;
		
	ELSEIF P_Type=2 THEN
		-- SELECT 1 INTO @tmp_sn_enable
		-- FROM goods_suite_detail gsd, goods_spec gs
		-- WHERE gsd.suite_id=P_TargetID AND gs.spec_id=gsd.spec_id AND gs.is_sn_enable>0 LIMIT 1;
		
		SELECT suite_no,suite_name,short_name,suite_id,'','',barcode,retail_price
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,@tmp_goods_id,@tmp_spec_id,@tmp_spec_name,@tmp_barcode,@tmp_retail_price 
		FROM goods_suite WHERE suite_id=P_TargetID;
		
		SET V_GoodsNO='';
	END IF;
	
	RETURN V_GoodsNO;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SEQ`;
DELIMITER //
CREATE FUNCTION `FN_SEQ`(`P_Name` VARCHAR(20)) RETURNS int(11)
	READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
 BEGIN
     SET @tmp_seq=1;
     INSERT INTO sys_sequence(`name`,`val`) VALUES(P_Name, 1) ON DUPLICATE KEY UPDATE val=(@tmp_seq:=(val+1));
     RETURN @tmp_seq;
END//
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SPEC_NO_CONV`;
DELIMITER $$
CREATE FUNCTION `FN_SPEC_NO_CONV`(`P_GoodsNO` VARCHAR(40), `P_SpecNO` VARCHAR(40)) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_I INT;
	
	IF LENGTH(@cfg_goods_match_split_char)>0 THEN
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_GoodsNO);
		IF V_I THEN
			SET P_GoodsNO=SUBSTRING(P_GoodsNO, 1, V_I-1);
		END IF;
		
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_SpecNO);
		IF V_I THEN
			SET P_SpecNO=SUBSTRING(P_SpecNO, 1, V_I-1);
		END IF;
		
	END IF;
	
	RETURN IF(@cfg_goods_match_concat_code,CONCAT(P_GoodsNO,P_SpecNO),IF(P_SpecNO<>'',P_SpecNO,P_GoodsNO));
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS `FN_SYS_NO`;
DELIMITER //
CREATE FUNCTION `FN_SYS_NO`(`P_Key` VARCHAR(50)) RETURNS VARCHAR(60) CHARSET UTF8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_DateChanged BIT;
	DECLARE V_Prefix, V_PostfixStr, V_PostfixStr2 VARCHAR(64);
	DECLARE V_PostfixLen, V_PostfixVal,V_DateForm INT;
	DECLARE V_NowDate DATE;
	
	-- date_form 0 全日期 1短日期 2无日期
	
	SET V_NowDate = CURDATE();
	
	UPDATE sys_no_cfg SET postfix_val=LAST_INSERT_ID(IF(V_NowDate=last_date OR date_form=2,postfix_val+1,1)),last_date=V_NowDate
	WHERE `key`=P_Key;
	
	SET V_PostfixVal = LAST_INSERT_ID();
	
	SELECT prefix,postfix_len,date_form into V_Prefix,V_PostfixLen,V_DateForm from sys_no_cfg WHERE `key`=P_Key;
	
	SET V_PostfixStr = CAST(V_PostfixVal AS CHAR);
	SET V_PostfixStr2 = LPAD(V_PostfixStr, V_PostfixLen, '0');
	IF LENGTH(V_PostfixStr2) < LENGTH(V_PostfixStr) THEN
		SET V_PostfixStr2 = V_PostfixStr;
	END IF;
	
	IF V_DateForm=0 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%Y%m%d'), V_PostfixStr2);
	ELSEIF V_DateForm=1 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%y%m%d'), V_PostfixStr2);
	END IF;
	
	RETURN CONCAT(V_Prefix, V_PostfixStr2);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_DECIDE_LOGISTICS_FEE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_LOGISTICS_FEE`(OUT `P_PostFee` DECIMAL(19,4), IN `P_Weight` DECIMAL(19,4), IN `P_LogisticsID` INT, IN `P_ShopID` INT, IN `P_WarehouseID` INT, IN `P_ReceiverCountry` INT, IN `P_ReceiverProvince` INT, IN `P_ReceiverCity` INT, IN `P_ReceiverDistrict` INT)
    SQL SECURITY INVOKER
    COMMENT '计算邮费'
MAIN_LABEL: BEGIN
	DECLARE V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4 VARCHAR(40);
	DECLARE V_FirstWeight, V_FristPrice, V_WeightStep1, V_UnitStep1, V_PriceStep1, V_WeightStep2, V_UnitStep2, V_PriceStep2,
		V_WeightStep3, V_UnitStep3, V_PriceStep3, V_WeightStep4, V_UnitStep4, V_PriceStep4,
		V_SpecialWeight,V_SpecialWeight2,V_SpecialWeight3,V_SpecialFee,V_SpecialFee2,V_SpecialFee3 DECIMAL(19,4) DEFAULT(0);
	DECLARE V_TruncMode INT;
	
	SET P_PostFee = 0;
	IF P_LogisticsID=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	SET @cfg_logistics_match_mode=0;
	SET V_Tmp1 = CONCAT(CASE @cfg_logistics_match_mode WHEN 0 THEN 0 WHEN 1 THEN P_WarehouseID WHEN 2 THEN P_ShopID END,'#');
	SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
	SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
	SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
	
	SET V_Tmp1 = CONCAT(V_Tmp1, ',0,#', P_LogisticsID);
	SET V_Tmp2 = CONCAT(V_Tmp2, ',#', P_LogisticsID);
	SET V_Tmp3 = CONCAT(V_Tmp3, ',#', P_LogisticsID);
	SET V_Tmp4 = CONCAT(V_Tmp4, ',#', P_LogisticsID);
	
	SELECT first_weight,first_price,weight_step1,unit_step1,price_step1,
		-- special_weight,special_weight2,special_weight3,special_fee,special_fee2,special_fee3,
		weight_step2,unit_step2,price_step2,weight_step3,unit_step3,price_step3,weight_step4,unit_step4,price_step4,trunc_mode 
	INTO V_FirstWeight, V_FristPrice, V_WeightStep1, V_UnitStep1, V_PriceStep1,
		-- V_SpecialWeight,V_SpecialWeight2,V_SpecialWeight3,V_SpecialFee,V_SpecialFee2,V_SpecialFee3,
		V_WeightStep2, V_UnitStep2, V_PriceStep2,V_WeightStep3, V_UnitStep3, V_PriceStep3, V_WeightStep4, V_UnitStep4, V_PriceStep4,V_TruncMode
	FROM cfg_logistics_fee FORCE INDEX(UK_cfg_logistics_fee_path) 
	WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	
	IF V_FirstWeight <=0 OR V_FristPrice <=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	/*
	IF V_SpecialWeight>0.0 AND  V_SpecialFee>0.0 AND P_Weight <=V_SpecialWeight  THEN
		SET P_PostFee = V_SpecialFee;
		LEAVE MAIN_LABEL;
	ELSEIF V_SpecialWeight2>0.0 AND  V_SpecialFee2>0.0 AND P_Weight <=V_SpecialWeight2 THEN
		SET P_PostFee = V_SpecialFee2;
		LEAVE MAIN_LABEL;		
	ELSEIF V_SpecialWeight3>0.0 AND  V_SpecialFee3>0.0 AND P_Weight <=V_SpecialWeight3 THEN
		SET P_PostFee = V_SpecialFee3;
		LEAVE MAIN_LABEL;		
	END IF;
	*/
	SET P_PostFee = V_FristPrice;
	IF P_Weight <= V_FirstWeight OR V_FirstWeight<=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_FirstWeight, V_WeightStep1, V_UnitStep1, V_PriceStep1);
	
	IF P_Weight <= V_WeightStep1 OR V_WeightStep1<=0 THEN
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep1, V_WeightStep2, V_UnitStep2, V_PriceStep2);
	
	IF P_Weight <= V_WeightStep2 OR V_WeightStep2<=0 THEN
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep2, V_WeightStep3, V_UnitStep3, V_PriceStep3);
	
	IF P_Weight <= V_WeightStep3 OR V_WeightStep3<=0 THEN
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep3, 0, V_UnitStep4, V_PriceStep4);
	SET P_PostFee = TRUNCATE(P_PostFee, 2);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE_CHANGED`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE_CHANGED`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ModifyFlag,V_TradeCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ShopID,V_ApiTradeStatus,V_RefundStatus TINYINT DEFAULT(0);
	DECLARE V_ApiTradeID,V_RecID BIGINT DEFAULT(0);
	DECLARE V_Tid,V_Oid VARCHAR(40);
	
	DECLARE api_trade_cursor CURSOR FOR 
		SELECT rec_id FROM api_trade FORCE INDEX(IX_api_trade_modify_flag)
		WHERE modify_flag>0 AND bad_reason=0 LIMIT 100;
	
	DECLARE api_trade_order_cursor CURSOR FOR 
		SELECT modify_flag,rec_id,status,shop_id,tid,oid,refund_status
		FROM api_trade_order WHERE modify_flag>0
		LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 主订单变化
	OPEN api_trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_trade_cursor INTO V_ApiTradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_cursor;
				OPEN api_trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_MAIN_ORDER(P_OperatorID, V_ApiTradeID);
		
	END LOOP;
	CLOSE api_trade_cursor;
	
	
	SET V_TradeCount = 0;
	-- 子订单变化
	OPEN api_trade_order_cursor;
	TRADE_ORDER_LABEL: LOOP
		-- modify_flag,rec_id,status,refund_status
		FETCH api_trade_order_cursor INTO V_ModifyFlag,V_RecID,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_order_cursor;
				OPEN api_trade_order_cursor;
				ITERATE TRADE_ORDER_LABEL;
			END IF;
			LEAVE TRADE_ORDER_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_SUB_ORDER(P_OperatorID,V_RecID,V_ModifyFlag,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus);
	END LOOP;
	CLOSE api_trade_order_cursor;
END//
DELIMITER ;

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
		V_SalesGoodsCount,V_TotalWeight,V_PostCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
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
	
	IF V_ProcessStatus <> 10 THEN
		ROLLBACK;
		SET @sys_code=2, @sys_message = '原始单状态不正确';
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
	CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
	IF V_IsPreorder THEN
		SET V_ExtMsg = ' 进预订单原因:客服备注提取';
	END IF;
	
	-- 客户备注
	SET V_BuyerMessage=TRIM(V_BuyerMessage);
	CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
	
	-- select warehouse_id randomly
	SELECT warehouse_id INTO V_WarehouseID2 FROM cfg_warehouse where is_disabled = 0 limit 1;
	
	-- get logistics_id from cfg_shop 
	IF V_DeliveryTerm=2 THEN
		SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
	ELSE 
		SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
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
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_ApiOrderCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
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
    -- 估算邮费
	CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_WarehouseID2, 
		0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
	
	SET V_AreaAlias = '';
	-- 估算货品成本
	SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID2 AND ss.spec_id=tsto.spec_id
	WHERE tsto.actual_num>0;
	/*
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
	*/
	
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
				/*
				IF V_IsPreorder THEN
					SET V_TradeStatus=19;  -- 预订单
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
				END IF;
				*/
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
		salesman_id,is_sealed,freeze_reason,delay_to_time,commission,gift_mask,unmerge_mask,raw_goods_type_count,raw_goods_count,single_spec_no,created)
	VALUES(FN_SYS_NO('sales'),V_PlatformID,V_ShopID,V_Tid,V_TradeStatus,1,V_TradeFrom,V_DeliveryTerm,V_RefundStatus,V_FenxiaoType,V_FenxiaoNick,
		V_TradeTime,V_PayTime,V_PayAccount,V_CustomerType,V_CustomerID,V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
		V_ReceiverAddress,V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverArea,V_ReceiverRing,
		V_ToDeliverTime,V_DistCenter,V_DistSite,V_BuyerMessage,V_Remark,V_RemarkFlag,
		V_SalesGoodsCount,V_SalesOrderCount,V_TotalWeight,V_TotalVolume,V_LogisticsID,V_AreaAlias,V_PostCost,V_GoodsCost,
		NOT FN_EMPTY(V_Remark),NOT FN_EMPTY(V_BuyerMessage),NOT FN_EMPTY(V_Remark),
		V_Paid,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,(V_GoodsAmount+V_PostAmount-V_Discount),V_FlagID,V_WarehouseID2,
		V_DapAmount,(V_CodAmount+V_ExtCodFee),V_PiAmount,V_ExtCodFee,V_InvoiceType,V_InvoiceTitle,V_InvoiceContent,V_WmsType2,V_StockoutNO,V_PackageID,
		V_SalesmanID,V_IsSealed,V_IsFreezed,V_DelayToTime,V_Commission,V_GiftMask,V_UnmergeMask,V_ApiOrderCount,V_ApiGoodsCount,V_SingleSpecNO,V_Now);
	
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
		SELECT V_TradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
		FROM cfg_oper_reason 
		WHERE reason_id = V_IsFreezed;
	END IF;
	
	-- 更新原始单
	UPDATE api_trade SET process_status=20,
		deliver_trade_id=V_TradeID,
		x_customer_id=V_CustomerID,
		x_salesman_id=V_SalesmanID,
		x_trade_flag=V_FlagID,
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

DROP PROCEDURE IF EXISTS `I_DL_DELIVER_SALES_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_SALES_TRADE`(IN `P_OperatorID` INT, IN `P_Status` INT)
    SQL SECURITY INVOKER
    COMMENT '递交第二步'
BEGIN
	DECLARE V_CurTime, V_TradeID, V_ShopID,V_WarehouseType,V_WarehouseID,V_DeliveryTerm,V_IsSealed,
		V_TradeID2, V_WarehouseID2,V_GiftMask,V_PlatformID, V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict, 
		V_TradeStatus, V_TradeCount, V_CheckedTradeID, V_CustomerID,V_TradeChanged,V_IsLarge,
		V_ToStatus, V_NOT_FOUND, V_bNoMerge, V_bNoSplit, V_bAllSplit,V_FreezeReasonID,
		V_LockWarehouse,V_SplitFromTradeID,V_UnmergeMask,V_GroupID INT DEFAULT(0);
	
	DECLARE V_IsSetWareByGoods INT DEFAULT(1);
	
	DECLARE V_RawTradeNO VARCHAR(40);
	DECLARE V_ReceiverArea,V_ReceiverName VARCHAR(64);
	DECLARE V_Tid,V_ReceiverAddress VARCHAR(256);
	
	DECLARE trade_cursor CURSOR FOR 
		SELECT trade_id,src_tids,shop_id,platform_id,delivery_term,customer_id,
			receiver_name,receiver_province,receiver_city,receiver_district,
			receiver_area,receiver_address,warehouse_type,warehouse_id, 
			gift_mask,customer_id,is_sealed,freeze_reason,split_from_trade_id 
		FROM sales_trade WHERE trade_status=P_Status
		LIMIT 100;
	
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
		FETCH trade_cursor INTO V_TradeID, V_Tid, V_ShopID, V_PlatformID, V_DeliveryTerm, V_CustomerID, 
			V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,
			V_ReceiverArea,V_ReceiverAddress, V_WarehouseType, V_WarehouseID, 
			V_GiftMask,V_CustomerID,V_IsSealed,V_FreezeReasonID,V_SplitFromTradeID;
		
		IF V_NOT_FOUND THEN
			IF V_TradeCount >= 500 THEN
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
		
		
		--  待审核订单合并
		IF @cfg_auto_merge AND V_IsLarge<2  AND V_bNoMerge=0 AND V_FreezeReasonID=0 AND V_DeliveryTerm=1 AND V_SplitFromTradeID = 0 THEN
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
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;
			/*ELSE
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
					st.trade_from=1 AND 
					st.trade_type=1 AND 
					st.freeze_reason=0 AND 
					st.revert_reason=0 AND 
					st.checkouter_id=0 AND 
					ELT(V_IsLarge+1,
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type=2),
						NOT EXISTS (SELECT 1 FROM sales_trade_order WHERE trade_id=st.trade_id AND large_type>0))
				LIMIT 1 FOR UPDATE;*/
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
					UPDATE sales_trade SET unmerge_mask=(V_UnmergeMask & ~2) WHERE trade_id=V_TradeID;
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
		/*IF @cfg_order_auto_split_by_warehouse AND V_IsSetWareByGoods = 1 AND V_IsSealed=0 AND V_DeliveryTerm=1 AND V_bNoSplit=0 THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,`data`,message) VALUES(V_TradeID,P_OperatorID,33,3,CONCAT('预订单自动拆分',ELT(@cfg_preorder_split_to_order_condition,'库存充足且不包含关键词的订单转审核','库存充足的订单转审核','不含关键词的订单转审核','库存充足或不包含关键词的订单转审核'))); 
				ITERATE TRADE_LABEL;
			END IF;
		END IF;
		
		IF V_TradeChanged THEN
			CALL I_DL_REFRESH_TRADE(P_OperatorID, V_TradeID,IF(@cfg_open_package_strategy,4,0)|@cfg_calc_logistics_by_weight, V_ToStatus);
		END IF;*/
		
		-- 占用库存
		CALL I_RESERVE_STOCK(V_TradeID, IF(V_ToStatus=30,3,5), V_WarehouseID, V_WarehouseID);
		
		-- 标记同名未合并的
		IF @cfg_order_check_warn_has_unmerge THEN
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
		END IF;
		
		COMMIT;
		
		SET @tmp_to_process_count = @tmp_to_process_count+1;
	END LOOP;
	CLOSE trade_cursor;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_CLIENT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_CLIENT_REMARK`(IN `P_Remark` VARCHAR(1024), 
	INOUT `P_TradeFlag` INT, 
	INOUT `P_WmsType` INT, 
	INOUT `P_WarehouseID` INT, 
	INOUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客户备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_Kw VARCHAR(255);
	DECLARE V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=2 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_c_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=2 THEN
			IF V_Target>0 AND P_TradeFlag=0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND P_WarehouseID=0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=6 THEN
			IF P_FreezeReason=0 THEN
				SET P_FreezeReason=GREATEST(1,V_Target);
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_REMARK`(IN `P_Remark` VARCHAR(1024), OUT `P_LogisticsID` INT, OUT `P_TradeFlag` INT, OUT `P_SalesmanID` INT, INOUT `P_WmsType` INT, OUT `P_WarehouseID` INT, OUT `P_IsPreorder` INT, OUT `P_FreezeReason` INT)
    SQL SECURITY INVOKER
    COMMENT '客服备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_SalesManName,V_Kw VARCHAR(255);
	DECLARE V_MacroBeginIndex, V_MacroEndIndex, V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=1 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_LogisticsID=0;
	SET P_TradeFlag=0;
	SET P_SalesmanID=0;
	SET P_WarehouseID = 0;
	SET P_IsPreorder=0;
	SET P_FreezeReason=0;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 根据括号提取
	IF @cfg_salesman_macro_begin<> '' AND @cfg_salesman_macro_end <> '' THEN
		SET V_MacroBeginIndex = LOCATE(@cfg_salesman_macro_begin, P_Remark, 1);
		IF V_MacroBeginIndex > 0 THEN
			SET V_MacroEndIndex = LOCATE(@cfg_salesman_macro_end, P_Remark, V_MacroBeginIndex+1);
			IF V_MacroEndIndex>0 THEN
				SET V_SalesManName = SUBSTRING(P_Remark, V_MacroBeginIndex+CHAR_LENGTH(@cfg_salesman_macro_begin), V_MacroEndIndex-V_MacroBeginIndex-CHAR_LENGTH(@cfg_salesman_macro_end));
				IF V_SalesManName IS NOT NULL AND V_SalesManName<>'' THEN 
					SELECT employee_id INTO P_SalesmanID FROM hr_employee WHERE fullname=V_SalesManName AND deleted=0 AND is_disabled=0;
				END IF;
			END IF;
		END IF;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=1 THEN
			IF V_Target>0 THEN
				SET P_LogisticsID=V_Target;
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 THEN
				SET P_TradeFlag=V_Target;
			END IF;
		ELSEIF V_Type=3 THEN
			IF V_Target>0 THEN
				SET P_SalesmanID=V_Target;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 THEN
				SET P_WarehouseID=V_Target;
				SELECT type INTO P_WmsType FROM cfg_warehouse WHERE warehouse_id=V_Target;
			END IF;
		ELSEIF V_Type=5 THEN
			SET P_IsPreorder=1;
		ELSEIF V_Type=6 THEN
			SET P_FreezeReason=GREATEST(1,V_Target);
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_INIT`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT`(IN `P_CreateApiGoods` INT)
    SQL SECURITY INVOKER
    COMMENT '递交处理初始化'
MAIN_LABEL:BEGIN
	DECLARE V_AutoMatchGoods INT DEFAULT(0);
	
	/*配置*/
	-- 是否开启自动递交
	CALL SP_UTILS_GET_CFG_INT('order_auto_submit',@cfg_order_auto_submit,1);

	-- 连接货品和规格商家编码
	CALL SP_UTILS_GET_CFG_INT('sys_goods_match_concat_code', @cfg_goods_match_concat_code, 0);

	-- 自动匹配平台货品的截取字符
	CALL SP_UTILS_GET_CFG_CHAR('goods_match_split_char', @cfg_goods_match_split_char, '');	
	
	-- 动态跟踪自动匹配货品
	-- CALL SP_UTILS_GET_CFG_INT('goods_match_dynamic_check', @cfg_goods_match_dynamic_check, 0);
	
	-- 是否自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge', @cfg_auto_merge, 1);
	
	-- 自动合并是否重新计算赠品
	CALL SP_UTILS_GET_CFG_INT('sales_trade_auto_merge_gift', @cfg_auto_merge_gift, 1);
	-- 订单审核时提示同名未合并
	CALL SP_UTILS_GET_CFG_INT('order_check_warn_has_unmerge', @cfg_order_check_warn_has_unmerge, 1);
	
	-- 延时审核分钟数
	CALL SP_UTILS_GET_CFG_INT('order_delay_check_min', @cfg_delay_check_sec, 0);	
	
	SET @cfg_delay_check_sec = @cfg_delay_check_sec*60;
	
	-- 已付等未付分钟数
	-- CALL SP_UTILS_GET_CFG_INT('order_wait_unpay_min', @cfg_wait_unpay_sec, 0);	
	
	SET @cfg_wait_unpay_sec = @cfg_wait_unpay_sec*60;
	
	-- 大件自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split', @cfg_order_auto_split, 1);
	
	-- 大件拆分最大次数
	-- CALL SP_UTILS_GET_CFG_INT('sales_split_large_goods_max_num', @cfg_sales_split_large_goods_max_num, 50);
	
	-- 按不同仓库自动拆分
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split_by_warehouse',@cfg_order_auto_split_by_warehouse,0);
	
	-- 订单合并方式
	CALL SP_UTILS_GET_CFG_INT('order_auto_merge_mode', @cfg_order_merge_mode, 0);	
	-- 审核时提示条件
	CALL SP_UTILS_GET_CFG_INT('order_check_merge_warn_mode', @cfg_order_check_merge_warn_mode, 0);
	
	-- 业务员
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_begin', @cfg_salesman_macro_begin, '');	
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_end', @cfg_salesman_macro_end, '');	
	
	
	IF @cfg_salesman_macro_begin='' OR @cfg_salesman_macro_begin IS NULL OR @cfg_salesman_macro_end='' OR @cfg_salesman_macro_end IS NULL THEN
		SET @cfg_salesman_macro_begin='';
		SET @cfg_salesman_macro_end='';
	END IF;
	
	-- 物流选择方式：全局唯一，按店铺，按仓库
	-- CALL SP_UTILS_GET_CFG_INT('logistics_match_mode', @cfg_logistics_match_mode, 0);	

	-- 按货品先仓库
	-- CALL SP_UTILS_GET_CFG_INT('sales_trade_warehouse_bygoods', @cfg_sales_trade_warehouse_bygoods, 0);
	
	-- 如果仓库是按货品策略选出,修改时给出提醒
	-- CALL SP_UTILS_GET_CFG_INT('order_check_alert_locked_warehouse', @cfg_chg_locked_warehouse_alert, 0);

	-- 是否启用备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_remark_extract', @cfg_enable_remark_extract, 0);	
	-- 客户备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_c_remark_extract', @cfg_enable_c_remark_extract, 0);	
	-- 订单进入待审核后是否根据备注提取物流
	CALL SP_UTILS_GET_CFG_INT('order_deliver_enable_cs_remark_track', @cfg_order_deliver_enable_cs_remark_track, 1);	
	
	-- 自动按商家编码匹配货品
	CALL SP_UTILS_GET_CFG_INT('apigoods_auto_match', V_AutoMatchGoods, 1);	
	
	-- 转预订单设置
	/* CALL SP_UTILS_GET_CFG_INT('order_go_preorder', @cfg_order_go_preorder, 0);
	IF @cfg_order_go_preorder THEN
		CALL SP_UTILS_GET_CFG_INT('order_preorder_lack_stock', @cfg_order_preorder_lack_stock, 0);
		CALL SP_UTILS_GET_CFG_INT('preorder_split_to_order_condition',@cfg_preorder_split_to_order_condition,0);
	END IF;
	*/
	CALL SP_UTILS_GET_CFG_INT('remark_change_block_stockout', @cfg_remark_change_block_stockout, 1);
	-- 物流同步后,发生退款不拦截
	CALL SP_UTILS_GET_CFG_INT('unblock_stockout_after_logistcs_sync', @cfg_unblock_stockout_after_logistcs_sync, 0);
	
	-- 销售凭证自动过账
	-- CALL SP_UTILS_GET_CFG_INT('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	
	-- 米氏抢单全局开关
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_hold', @cfg_order_deliver_hold, 0);
	
	--  根据重量计算物流
	CALL SP_UTILS_GET_CFG_INT('calc_logistics_by_weight',@cfg_calc_logistics_by_weight,0);
	
	--  包装策略
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy', @cfg_open_package_strategy,0); 
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy_type',@cfg_open_package_strategy_type,1); -- 1,根据重量   2,根据体积
	
	-- 是否开启订单全链路
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	-- 订单中原始货品数量是否包含赠品
	CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	
	-- 强制凭证不需要审核
	-- SET @cfg_fa_voucher_must_check=0;
	
	-- 是否需要从原始单货品生成api_goods_spec
	IF NOT P_CreateApiGoods THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*导入平台货品*/
	START TRANSACTION;
	
	SELECT 1 INTO @tmp_dummy FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE;
	
	UPDATE api_goods_spec ag,api_trade_order ato,api_trade at
	SET ag.modify_flag=
		IF(ag.outer_id=ato.goods_no AND ag.spec_outer_id=ato.spec_no, ag.modify_flag, ag.modify_flag|1),
		ag.outer_id=ato.goods_no,ag.spec_outer_id=ato.spec_no,
		ag.goods_name=ato.goods_name,ag.spec_name=ato.spec_name,
		ag.cid=IF(ato.cid='',ag.cid,ato.cid),at.is_new=0
	WHERE at.process_status=10 AND at.is_new=1 AND ato.tid=at.tid AND ato.shop_id=at.shop_id AND ato.goods_id<>''
		AND ag.shop_id=ato.shop_id AND ag.goods_id=ato.goods_id AND ag.spec_id=ato.spec_id;
	
	-- 要测试平台更新编码的同步
	INSERT INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,cid,modify_flag,created)
	(
		SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,at.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,ato.cid,1,NOW()
		FROM api_trade_order ato INNER JOIN api_trade at ON ato.tid=at.tid AND ato.shop_id=at.shop_id
		WHERE at.process_status=10 AND at.is_new=1 AND ato.goods_id<>''
	)
	ON DUPLICATE KEY UPDATE modify_flag=
		IF(api_goods_spec.outer_id=VALUES(outer_id) AND api_goods_spec.spec_outer_id=VALUES(spec_outer_id), api_goods_spec.modify_flag, api_goods_spec.modify_flag|1),
		outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),
		goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),
		cid=IF(VALUES(cid)='',api_goods_spec.cid,VALUES(cid));
	
	UPDATE api_trade SET is_new=0 WHERE process_status=10 and is_new=1;
	COMMIT;
	
	IF V_AutoMatchGoods THEN
		-- 对新增和变化的平台货品进行自动匹配
		UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;
		
		-- 刷新品牌分类
		UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
		SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;
		
		UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
		SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;
		
		-- 刷新无效货品
		UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax
		SET ato.is_invalid_goods=0,ax.bad_reason=0
		WHERE ato.is_invalid_goods=1 AND ag.`shop_id`=ato.`shop_id` AND ag.`goods_id`=ato.`goods_id` AND
			ag.`spec_id`=ato.`spec_id` AND ax.shop_id=ato.`shop_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND
			ag.match_target_type>0; 
		
		-- 自动刷新库存同步规则
		-- 应该判断一下规则是否变化了，如果变化了，要触发同步开关????????????
		UPDATE api_goods_spec gs,
		(SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id 
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1)
		WHERE gs.rec_id=da.rec_id;
		UPDATE api_goods_spec SET modify_flag=(modify_flag&~1) WHERE modify_flag>0 AND (modify_flag&1);
	END IF;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MAP_TRADE_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_MAP_TRADE_GOODS`(IN `P_TradeID` INT, IN `P_ApiTradeID` BIGINT, IN `P_UseTran` INT, OUT `P_ApiOrderCount` INT, OUT `P_ApiGoodsCount` INT)
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中'
MAIN_LABEL: BEGIN 
	DECLARE V_MatchTargetID,V_GoodsID,V_SGoodsID,V_SpecID,V_SuiteSpecCount,V_I,V_GiftType,V_MasterID,V_ShopID,
		V_Cid,V_IsDeleted,V_NOT_FOUND INT DEFAULT(0);
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
	
	-- 订单信息
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,oid,ato.status,refund_status,bind_oid,invoice_type,invoice_content,num,ato.price,adjust_amount,
			discount,share_discount,share_amount,share_post,paid,match_target_type,match_target_id,spec_no,ato.gift_type,
			ato.goods_name,ato.spec_name,aps.cid,aps.is_manual_match,ato.goods_no,ato.spec_no,ato.remark
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON aps.shop_id=V_ShopID AND aps.goods_id=ato.goods_id and aps.spec_id=ato.spec_id
		WHERE ato.platform_id=V_PlatformID AND ato.tid=V_Tid AND ato.process_status=10;
	
	-- 组合装货品
	DECLARE goods_suite_cursor CURSOR FOR 
		SELECT gsd.spec_id,gsd.num,gsd.is_fixed_price,gsd.fixed_price,gsd.ratio,gg.goods_name,gs.goods_id,gg.goods_no,
			gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,(gs.length*gs.width*gs.height) as volume ,gs.tax_rate,gs.large_type,(gs.retail_price*gsd.num),gs.is_allow_zero_cost,gs.deleted
		FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id) 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gsd.suite_id=V_MatchTargetID AND gsd.num>0
		ORDER BY gsd.is_fixed_price DESC;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	
	DELETE FROM tmp_sales_trade_order;
	
	SELECT platform_id,shop_id,tid,delivery_term,guarantee_mode,trade_mask
	INTO V_PlatformID,V_ShopID,V_Tid,V_DeliveryTerm,V_GuaranteeMode,V_TradeMask
	FROM api_trade WHERE rec_id=P_ApiTradeID;
	
	-- 展开货品
	SET P_ApiOrderCount = 0;
	SET P_ApiGoodsCount = 0;
	SET V_MasterAmount = -1;
	SET V_Now = NOW();
	SET @tmp_refund_occur = 0;
	SET @sys_code=0, @sys_message='OK';
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH trade_order_cursor INTO 
			V_RecID,V_Oid,V_OrderStatus,V_RefundStatus,V_BindOid,V_InvoiceType,V_InvoiceContent,V_Num,V_Price,V_AdjustAmount,
			V_Discount,V_ShareDiscount,V_ShareAmount,V_SharePost,V_Paid,V_MatchTargetType,V_MatchTargetID,V_ApiSpecNO,V_GiftType,
			V_ApiGoodsName,V_ApiSpecName,V_CidNO,V_IsManualMatch,V_OuterId,V_SpecOuterId,V_Remark;
			
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_Num <= 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
			 	ROLLBACK;
			 	UPDATE api_trade SET bad_reason=(bad_reason|1) WHERE rec_id=P_ApiTradeID;
			 END IF;
			 SET @sys_code=4, @sys_message = '货品数量为零';
			 LEAVE MAIN_LABEL;
		END IF;
		
		SET P_ApiOrderCount = P_ApiOrderCount + 1;
		SET P_ApiGoodsCount = P_ApiGoodsCount + V_Num;
		
		-- 类目及佣金暂时不做
		-- SET V_CommissionFactor = 0, V_Cid = 0;
		-- 未绑定
		IF V_PlatformID=0 THEN -- 线下订单不需判断无效货品 
			SELECT `type`, target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_ApiSpecNO;
		ELSE
			/*
			IF V_CidNO <> '' THEN
				SELECT rec_id,commission_factor INTO V_Cid,V_CommissionFactor FROM api_goods_category WHERE cid=V_CidNO AND shop_id=V_ShopID;
				SET V_NOT_FOUND=0;
			END IF;
			*/
			-- 判断是否开启动态匹配
			IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
				SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
				SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			END IF;
		END IF;
		
		
		IF V_NOT_FOUND OR V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			 CLOSE trade_order_cursor;
			 IF P_UseTran THEN
				 ROLLBACK;
				 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
			 END IF;
			 SET @sys_code=3, @sys_message = CONCAT('订单包含无效货品:',V_Tid);
			 LEAVE MAIN_LABEL;
		END IF;
		
		-- 子订单关闭,当退款处理
		IF V_OrderStatus=80 OR V_OrderStatus=90 THEN
			 SET V_RefundStatus=5;
		END IF;
		
		IF V_RefundStatus>1 THEN -- 需要创建退款单
			 SET @tmp_refund_occur = V_RefundStatus;
		END IF;
		
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.weight,gs.tax_rate,gs.large_type,gs.is_allow_zero_cost,gs.length*gs.width*gs.height
				INTO V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,V_Weight,V_TaxRate,V_LargeType,V_IsZeroCost,V_Volume
			FROM goods_spec gs LEFT JOIN goods_goods gg USING(goods_id)
			WHERE gs.spec_id=V_MatchTargetID AND gs.deleted=0;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					 ROLLBACK;
					 CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=4, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效单品');
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果钱已经付了，则为款到发货
			IF V_Paid >= V_ShareAmount+V_SharePost THEN
				 SET V_OrderDeliveryTerm = 1;
			ELSE
				 SET V_OrderDeliveryTerm = V_DeliveryTerm;
			END IF;
			
			SET V_SharePrice=TRUNCATE(V_ShareAmount/V_Num,4);
			
			-- 退款状态处理??
			INSERT INTO tmp_sales_trade_order(
				spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,paid,
				order_price,share_amount,share_post,share_price,adjust,discount,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
				api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,from_mask,gift_type,
				cid,is_allow_zero_cost,remark)
			VALUES(V_MatchTargetID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_Num,V_Price,
				IF(V_RefundStatus>2,0,V_Num),V_Paid,V_SharePrice,V_ShareAmount,V_SharePost,V_SharePrice,V_AdjustAmount,
				(V_Discount-V_AdjustAmount+V_ShareDiscount),
				V_GoodsName,V_GoodsID,V_GoodsNO,V_SpecName,V_SpecNO,V_SpecCode,
				V_ApiGoodsName,V_ApiSpecName,V_Weight*V_Num,V_Volume*V_Num,TRUNCATE(V_ShareAmount*V_CommissionFactor,4),V_TaxRate,V_LargeType,
				V_InvoiceType,V_InvoiceContent,V_TradeMask,V_GiftType,V_Cid,V_IsZeroCost,V_Remark);
			/*
			-- 找一个未退款的，金额最大的子订单作主订单,不考虑主订单
			IF V_RefundStatus<=2 AND V_ShareAmount > V_MasterAmount THEN
				 SET V_MasterAmount=V_ShareAmount;
				 SET V_MasterID = LAST_INSERT_ID();
			END IF;
			*/
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			-- 取组合装信息
			SELECT suite_no,suite_name,is_unsplit,is_print_suite INTO V_SuiteNO,V_SuiteName,V_IsUnsplit,V_IsPrintSuite
			FROM goods_suite WHERE suite_id=V_MatchTargetID;
			
			IF V_NOT_FOUND THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=5, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 包含无效组合装');
				LEAVE MAIN_LABEL;
			END IF;
			
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
			
			-- 无货品
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装为空');
				LEAVE MAIN_LABEL;
			END IF;
			
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
					CLOSE trade_order_cursor;
					IF P_UseTran THEN
						ROLLBACK;
						CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
					END IF;
					SET @sys_code=7, @sys_message = CONCAT('订单:', V_Tid, ' 子订单',V_Oid,' 组合装包含已删除单品 ', V_SSpecNO);
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
				
				INSERT INTO tmp_sales_trade_order(
					spec_id,shop_id,platform_id,src_oid,src_tid,refund_status,guarantee_mode,delivery_term,bind_oid,num,price,actual_num,
					order_price,share_price,share_amount,share_post,discount,paid,goods_name,goods_id,goods_no,spec_name,spec_no,spec_code,
					api_goods_name,api_spec_name,weight,volume,commission,tax_rate,large_type,invoice_type,invoice_content,suite_id,suite_no,suite_name,suite_num,suite_amount,
					suite_discount,is_print_suite,from_mask,gift_type,cid,is_allow_zero_cost,remark)
				VALUES(V_SpecID,V_ShopID,V_PlatformID,V_Oid,V_Tid,V_RefundStatus,V_GuaranteeMode,V_OrderDeliveryTerm,V_BindOid,V_SNum,V_SPrice,IF(V_RefundStatus>2,0,V_SNum),
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
			
			IF V_SuiteSpecCount=0 THEN
				CLOSE trade_order_cursor;
				IF P_UseTran THEN
					ROLLBACK;
					CALL I_DL_MARK_INVALID_TRADE(P_ApiTradeID, V_ShopID, V_Tid);
				END IF;
				SET @sys_code=6, @sys_message = '组合装无货品';
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	-- 标记主子订单
	-- 注：拆分合并时处理
	-- UPDATE tmp_sales_trade_order SET is_master=1 WHERE rec_id=V_MasterID;
	
	IF P_TradeID THEN
		INSERT INTO sales_trade_order(trade_id,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,created)
		SELECT P_TradeID,spec_id,shop_id,platform_id,src_oid,suite_id,src_tid,gift_type,refund_status,guarantee_mode,delivery_term,
			bind_oid,num,price,actual_num,order_price,share_price,adjust,discount,share_amount,share_post,paid,tax_rate,goods_name,goods_id,
			goods_no,spec_name,spec_no,spec_code,suite_no,suite_name,suite_num,suite_amount,suite_discount,is_print_suite,api_goods_name,api_spec_name,
			weight,commission,goods_type,flag,large_type,invoice_type,invoice_content,from_mask,is_master,is_allow_zero_cost,cid,remark,NOW()
		FROM tmp_sales_trade_order;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_MARK_INVALID_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_MARK_INVALID_TRADE`(IN `P_TradeID` INT, IN `P_ShopId` TINYINT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '标记原始单的子订单有无效货品'
MAIN_LABEL:BEGIN
	DECLARE V_RecID,V_MatchTargetType,V_MatchTargetID,V_InvalidGoods,V_GoodsCount,V_IsManualMatch,V_Deleted,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,match_target_type,match_target_id,is_manual_match,ato.goods_no,ato.spec_no
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON ato.shop_id=aps.shop_id AND ato.goods_id=aps.goods_id and ato.spec_id=aps.spec_id
		WHERE ato.shop_id=P_ShopId AND ato.tid=P_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		
		FETCH trade_order_cursor INTO V_RecID,V_MatchTargetType,V_MatchTargetID,V_IsManualMatch,V_OuterId,V_SpecOuterId;
		IF V_NOT_FOUND THEN
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		-- 未绑定
		IF V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			
			-- 添加到平台货品
			INSERT IGNORE INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,modify_flag,created)
			SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,ax.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,1,NOW()
			FROM api_trade_order ato LEFT JOIN api_trade ax ON ax.tid=ato.tid AND ax.platform_id=ato.platform_id
			WHERE ato.rec_id=V_RecID;
			
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
			SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
			SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			IF V_NOT_FOUND THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
				ITERATE TRADE_GOODS_LABEL;
			END IF;
		END IF;
		
		SET V_Exists=0,V_Deleted = 0;
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_spec WHERE spec_id=V_MatchTargetID;
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_suite WHERE suite_id=V_MatchTargetID;
		END IF;
		
		
		IF NOT V_Exists OR V_Deleted THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_MatchTargetType = 2 THEN
			SELECT COUNT(rec_id) INTO V_GoodsCount FROM goods_suite_detail WHERE suite_id=V_MatchTargetID;
			IF V_GoodsCount=0 THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
			
			-- 判断组合装里货品是否都有效
			IF EXISTS(SELECT 1 FROM goods_suite_detail gsd,goods_spec gs 
				WHERE gsd.suite_id=V_MatchTargetID AND gs.spec_id=gsd.spec_id AND gs.deleted>0) THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	IF V_InvalidGoods THEN
		UPDATE api_trade SET bad_reason=1 WHERE rec_id=P_TradeID;
	END IF;
	COMMIT;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PREPARE_GIFT_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_PREPARE_GIFT_GOODS`(IN `P_TradeID` INT, INOUT `P_First` INT)
	SQL SECURITY INVOKER
	COMMENT '将订单货品插入到临时表,为赠品准备'
MAIN_LABEL:BEGIN
	IF P_First=0 THEN
		LEAVE MAIN_LABEL;
	END IF;

	DELETE FROM tmp_gift_trade_order;
	
	SET P_First=0;
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 0,sto.spec_id,sto.actual_num,sto.discount,sto.share_amount,sto.weight,sto.from_mask,gc.path,gg.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_goods gg ON gg.goods_id=sto.goods_id 
		LEFT JOIN goods_class gc ON gc.class_id=gg.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id=0 AND actual_num>0 AND sto.gift_type=0)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 1,sto.suite_id,sto.suite_num,SUM(sto.discount),SUM(sto.share_amount),SUM(sto.weight),BIT_OR(sto.from_mask),gc.path,gs.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_suite gs ON gs.suite_id=sto.suite_id
		LEFT JOIN goods_class gc ON gc.class_id=gs.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id>0 AND sto.actual_num>0 AND sto.gift_type=0
	GROUP BY platform_id,src_oid)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_PUSH_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_PUSH_REFUND`(IN `P_OperatorID` INT, IN `P_ShopID` INT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '递交过程中自动生成退款单'
BEGIN
	DECLARE V_RefundStatus,V_GoodsID,V_SpecId,V_RefundID,V_RefundID2,V_Status,V_ApiStatus,V_Type,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Num DECIMAL(19,4);
	DECLARE V_Oid,V_RefundNO,V_RefundNO2 VARCHAR(40);
	
	DECLARE refund_order_cursor CURSOR FOR 
		SELECT refund_id,refund_status,status,oid
		FROM api_trade_order
		WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 删除临时退款单
	DELETE stro FROM sales_tmp_refund_order stro, api_trade_order sto 
	WHERE stro.shop_id=P_ShopID AND stro.oid=sto.oid AND sto.shop_id=P_ShopID AND sto.tid=P_Tid;
	
	OPEN refund_order_cursor;
	REFUND_ORDER_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH refund_order_cursor INTO V_RefundNO,V_RefundStatus,V_ApiStatus,V_Oid;
		IF V_NOT_FOUND THEN
			LEAVE REFUND_ORDER_LABEL;
		END IF;
		
		IF V_RefundStatus < 2 THEN -- 取消退款
			-- 如果订单已发货，说明是个售后退货,不需要再更新退款单
			IF V_ApiStatus>=40 THEN
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			DELETE FROM sales_tmp_refund_order WHERE shop_id=P_ShopID AND oid=V_Oid;
			
			-- 更新退款单状态
			-- 一个原始单只能出现在一个退款单中
			SET V_RefundID=0;
			SELECT sro.refund_id INTO V_RefundID FROM sales_refund_order sro,sales_refund sr
			WHERE sro.shop_id=P_ShopID AND sro.oid=V_Oid AND sro.refund_id=sr.refund_id AND sr.type=1 LIMIT 1;
			
			IF V_RefundID THEN
				UPDATE sales_refund_order SET process_status=10 WHERE refund_id=V_RefundID AND shop_id=P_ShopID AND oid=V_Oid;
				SET V_Status=0;
				SELECT 1 INTO V_Status FROM sales_refund_order WHERE refund_id=V_RefundID AND process_status<>10 LIMIT 1;
				IF V_Status=0 THEN  -- 全部子订单都取消
					UPDATE sales_refund SET process_status=10,status=V_RefundStatus WHERE refund_id=V_RefundID;
					-- 日志
					INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,4,P_OperatorID,'平台取消退款');
				END IF;
			END IF;
			-- 原始退款单状态?
			
			ITERATE REFUND_ORDER_LABEL;
		END IF;
		
		-- 目前只有淘宝存在退款单号
		-- 没有退款单号的，自动生成一个
		IF V_RefundNO='' THEN
			
			SET V_Type=IF(V_ApiStatus<40,1,2);
			SET V_NOT_FOUND=0;
			SELECT ar.refund_id,ar.refund_no INTO V_RefundID,V_RefundNO FROM api_refund ar,api_refund_order aro
			WHERE ar.shop_id=P_ShopID AND ar.tid=P_Tid AND ar.`type`=V_Type 
				AND aro.shop_id=P_ShopID AND aro.refund_no=ar.refund_no AND aro.oid=V_Oid LIMIT 1;
			
			IF V_NOT_FOUND THEN
				-- 一个货品一个退款单
				SET V_RefundNO=FN_SYS_NO('apirefund');
				
				-- 创建原始退款单
				INSERT INTO api_refund(platform_id,refund_no,shop_id,tid,title,type,status,process_status,pay_account,refund_amount,actual_refund_amount,buyer_nick,refund_time,created)
				(SELECT ax.platform_id,V_RefundNO,ax.shop_id,P_Tid,ato.goods_name,V_Type,ato.refund_status,0,ax.pay_account,ato.refund_amount,ato.refund_amount,ax.buyer_nick,NOW(),NOW()
				FROM api_trade_order ato, api_trade ax
				WHERE ato.shop_id=P_ShopID AND ato.oid=V_Oid AND ax.shop_id=P_ShopID AND ax.tid=P_Tid);
				
				INSERT INTO api_refund_order(platform_id,refund_no,shop_id,oid,status,goods_name,spec_name,num,price,total_amount,goods_id,spec_id,goods_no,spec_no,created)
				(SELECT platform_id,V_RefundNO,shop_id,oid,refund_status,goods_name,spec_name,num,price,share_amount,goods_id,spec_id,goods_no,spec_no,NOW()
				FROM api_trade_order WHERE shop_id=P_ShopID AND tid=P_Tid AND refund_status>0 AND refund_id='');
			ELSE
				UPDATE api_refund SET status=V_RefundStatus,modify_flag=(modify_flag|1) WHERE refund_id=V_RefundID;
				UPDATE api_refund_order SET status=V_RefundStatus WHERE shop_id=P_ShopID AND refund_no=V_RefundNO AND oid=V_Oid;
			END IF;
			
		ELSE
			IF V_ApiStatus>=40 THEN -- 已发货,销后退款,让退款同步脚本处理
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
			-- 平台支持退款单的
			-- 查找退款单是否已经存在，如果已存在，就不需要创建临时退款单，直接更新退款单状态
			SET V_RefundID=0;
			SELECT refund_id INTO V_RefundID FROM sales_refund WHERE src_no=V_RefundNO AND shop_id=P_ShopID AND type=1 LIMIT 1;
			IF V_RefundID THEN
				SET V_Status=80;
				IF V_RefundStatus=2 THEN
					SET V_Status=20;
				ELSEIF V_RefundStatus=3 THEN
					SET V_Status=60;
				ELSEIF V_RefundStatus=4 THEN
					SET V_Status=60;
				END IF;

				UPDATE sales_refund SET process_status=V_Status,status=V_RefundStatus WHERE refund_id=V_RefundID;
				-- 日志
				INSERT INTO sales_refund_log(refund_id,type,operator_id,remark) VALUES(V_RefundID,2,P_OperatorID,'平台同意退款');
				ITERATE REFUND_ORDER_LABEL;
			END IF;
			
		END IF;
		
		IF V_RefundStatus>2 THEN
			-- 创建临时退款单
			INSERT IGNORE INTO sales_tmp_refund_order(shop_id, oid) VALUES(P_ShopID, V_Oid);
		END IF;
	END LOOP;
	CLOSE refund_order_cursor;
		
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_REFRESH_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_REFRESH_TRADE`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_RefreshFlag` INT, IN `P_ToStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_WarehouseID,V_WarehouseType, V_ShopID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,
		V_LogisticsID,V_DeliveryTerm,V_Max,V_Min,V_NewRefundStatus,V_NewLogisticsID,V_Locked,V_GoodsTypeCount,
		V_NoteCount,V_GiftMask,V_PackageID,V_SalesmanId,V_PlatformId,V_RemarkFlag,V_FlagId,V_BuyerMessageCount,
		V_CsRemarkCount,V_InvoiceType,V_TradeStatus,V_RawGoodsTypeCount, V_RawGoodsCount INT DEFAULT(0);
	DECLARE V_Addr,V_SrcTids,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(255);
	DECLARE V_BuyerMessage,V_CsRemark VARCHAR(1024);
	DECLARE V_AreaAlias,V_SingleSpecNO VARCHAR(40);
	DECLARE V_GoodsCount,V_Weight,V_PostCost,V_Paid,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GoodsCost,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4);
	
	-- P_RefreshFlag
	-- 1选择物流 2计算大头笔 4选择包装 8刷新备注
	
	-- 统计子订单
	SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0)),SUM(actual_num),COUNT(DISTINCT IF(actual_num<=0,NULL,sto.spec_id)),
		SUM(IF(actual_num>0,sto.weight,0)),SUM(IF(actual_num>0,paid,0)),MAX(IF(actual_num>0,delivery_term,1)),
		SUM(IF(actual_num>0,share_amount+discount,0)),SUM(IF(actual_num>0,share_post,0)),SUM(IF(actual_num>0,discount,0)),
		SUM(IF(actual_num>0,IF(delivery_term=1,share_amount+share_post,paid),0)),
		SUM(IF(actual_num>0,IF(delivery_term=2,share_amount+share_post-paid,0),0)),
		BIT_OR(IF(actual_num>0,gift_type,0)),SUM(IF(actual_num>0,commission,0)),SUM(actual_num*gs.length*gs.width*gs.height)
	INTO V_Max,V_Min,V_GoodsCount,V_GoodsTypeCount,V_Weight,V_Paid,V_DeliveryTerm,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GiftMask,V_Commission,V_TotalVolume
	FROM sales_trade_order sto LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id  WHERE sto.trade_id=P_TradeID;	
	
	-- 退款状态
	IF V_GoodsCount<=0 THEN
		SET V_NewRefundStatus=IF(V_Max,3,4);
		SET P_ToStatus=5;
	ELSEIF V_Max=0 AND V_Min THEN
		SET V_NewRefundStatus=1;
	ELSEIF V_Max THEN
		SET V_NewRefundStatus=2;
	ELSE
		SET V_NewRefundStatus=0;
	END IF;
	
	-- 计算原始货品数量
	IF @cfg_sales_raw_count_exclude_gift IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	END IF;
	
	SELECT COUNT(DISTINCT spec_no),SUM(num) INTO V_RawGoodsTypeCount, V_RawGoodsCount
	FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num
	FROM sales_trade_order
	WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1)
	GROUP BY shop_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp;
	
	IF V_RawGoodsCount IS NULL THEN
		 SET V_RawGoodsCount=0;
	END IF;

	IF V_RawGoodsTypeCount=1 THEN
		SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
		FROM sales_trade_order 
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- V_WmsType, V_WarehouseNO, V_ShopID, V_TradeID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict;
	SELECT trade_status,warehouse_type, warehouse_id,shop_id,logistics_id,post_cost,receiver_province,receiver_city,receiver_district,receiver_address,receiver_dtb,package_id
	INTO V_TradeStatus,V_WarehouseType, V_WarehouseID,V_ShopID,V_LogisticsID,V_PostCost,V_ReceiverProvince,V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_AreaAlias,V_PackageID
	FROM sales_trade
	WHERE trade_id = P_TradeID;
	
	
	-- 订单未审核
	IF V_TradeStatus<35 THEN
		-- 包装
		/*IF P_RefreshFlag & 4  THEN 
			CALL I_DL_DECIDE_PACKAGE(V_PackageID,V_Weight,V_TotalVolume);

			IF V_PackageID THEN
				SELECT weight INTO V_PackageWeight  FROM goods_spec WHERE spec_id = V_PackageID;
				SET V_Weight=V_Weight + V_PackageWeight;

			END IF;
		END IF;

		-- 选择物流
		IF P_RefreshFlag & 1 THEN
			CALL I_DL_DECIDE_LOGISTICS(V_NewLogisticsID, -1, V_DeliveryTerm, V_ShopID, V_WarehouseID,V_Weight, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_Addr);
			IF V_LogisticsID<>V_NewLogisticsID AND V_NewLogisticsID>0 THEN
				SET V_LogisticsID=V_NewLogisticsID;
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		END IF;*/
		
		-- 估算邮费
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_Weight, V_LogisticsID, V_ShopID, V_WarehouseID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
		
		
	END IF;
	
	-- 估算货品成本
	SELECT TRUNCATE(IFNULL(SUM(sto.actual_num*IFNULL(ss.cost_price,0)),0),4) INTO V_GoodsCost FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_WarehouseID AND ss.spec_id=sto.spec_id
	WHERE sto.trade_id=P_TradeID AND sto.actual_num>0;
	-- SET V_AreaAlias = '';
	-- 便签数量
	-- SELECT COUNT(1) INTO V_NoteCount FROM common_order_note WHERE type=1 AND order_id=P_TradeID;
	
	SET @old_sql_mode=@@SESSION.sql_mode;
	SET SESSION sql_mode='';
	SELECT IFNULL(LEFT(GROUP_CONCAT(IF(ax.platform_id OR  ax.trade_from=3 OR ax.trade_from=5,ax.tid,NULL)),255),''),MAX(ax.x_salesman_id),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.buyer_message)='',NULL,TRIM(ax.buyer_message))),1024),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.remark)='',NULL,TRIM(ax.remark))),1024),''),
		MAX(ax.platform_id),
		MAX(ax.remark_flag),
		MAX(ax.x_trade_flag),
		SUM(IF(TRIM(ax.buyer_message)='',0,1)),
		SUM(IF(TRIM(ax.remark)='',0,1)),
		MAX(ax.invoice_type),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_title)='',NULL,TRIM(ax.invoice_title))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(IF(TRIM(ax.invoice_content)='',NULL,TRIM(ax.invoice_content))),255),''),
		IFNULL(LEFT(GROUP_CONCAT(DISTINCT IF(TRIM(ax.pay_account)='',NULL,TRIM(ax.pay_account))),128),'')
	INTO
		V_SrcTids, V_SalesmanId, V_BuyerMessage, V_CsRemark, V_PlatformId, V_RemarkFlag, V_FlagId,
		V_BuyerMessageCount, V_CsRemarkCount, V_InvoiceType, V_InvoiceTitle, V_InvoiceContent,V_PayAccount
	FROM (SELECT DISTINCT shop_id,src_tid FROM sales_trade_order WHERE trade_id=P_TradeID) sto
		LEFT JOIN api_trade ax ON (ax.shop_id=sto.shop_id AND ax.tid=sto.src_tid);
	
	SET SESSION sql_mode=IFNULL(@old_sql_mode,'');
	
	IF V_PlatformId IS NULL THEN
		UPDATE sales_trade
		SET buyer_message_count=NOT FN_EMPTY(buyer_message),
			cs_remark_change_count=NOT FN_EMPTY(cs_remark),
			cs_remark_count=NOT FN_EMPTY(cs_remark),
			refund_status=V_NewRefundStatus,
			goods_count=V_GoodsCount,
			goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,
			post_amount=V_PostAmount,
			discount=V_Discount,
			receivable=V_GoodsAmount+V_PostAmount-V_Discount,
			dap_amount=V_DapAmount,
			cod_amount=(V_CodAmount+ext_cod_fee),
			warehouse_id=V_WarehouseID,
			trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
			logistics_id=V_LogisticsID,
			post_cost=V_PostCost,
			goods_cost=V_GoodsCost,
			receiver_dtb=V_AreaAlias,
			weight=V_Weight,
			volume=V_TotalVolume,
			delivery_term=V_DeliveryTerm,
			package_id = V_PackageID,
			paid=V_Paid,
			commission=V_Commission,
			profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
			note_count=V_NoteCount,
			gift_mask=V_GiftMask,
			version_id=version_id+1
		WHERE trade_id=P_TradeID;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新订单
	UPDATE sales_trade
	SET platform_id=V_PlatformId,
		src_tids=V_SrcTids,
		buyer_message=V_BuyerMessage,
		cs_remark=IF(NOT (cs_remark_change_count&2) OR (P_RefreshFlag&8),V_CsRemark,cs_remark),
		buyer_message_count=V_BuyerMessageCount,
		cs_remark_count=GREATEST(V_CsRemarkCount,NOT FN_EMPTY(cs_remark)),
		remark_flag=V_CsRemarkCount,
		flag_id=IF(flag_id=0,V_FlagId,flag_id),
		invoice_type=IF(invoice_type=0,V_InvoiceType,invoice_type),
		invoice_title=IF(invoice_title='',V_InvoiceTitle,invoice_title),
		invoice_content=IF(invoice_content='',V_InvoiceContent,invoice_content),
		salesman_id=IF(salesman_id,salesman_id,V_SalesmanId),
		refund_status=V_NewRefundStatus,
		goods_count=V_GoodsCount,
		goods_type_count=V_GoodsTypeCount,
		goods_amount=V_GoodsAmount,
		post_amount=V_PostAmount,
		discount=V_Discount,
		receivable=V_GoodsAmount+V_PostAmount-V_Discount,
		dap_amount=V_DapAmount,
		cod_amount=(V_CodAmount+ext_cod_fee),
		warehouse_id=V_WarehouseID,
		trade_status=IF(P_ToStatus,P_ToStatus,trade_status),
		logistics_id=V_LogisticsID,
		post_cost=V_PostCost,
		goods_cost=V_GoodsCost,
		receiver_dtb=V_AreaAlias,
		weight=V_Weight,
		volume=V_TotalVolume,
		delivery_term=V_DeliveryTerm,
		package_id = V_PackageID,
		paid=V_Paid,
		commission=V_Commission,
		profit=receivable-V_GoodsCost-V_PostCost-V_Commission,
		note_count=V_NoteCount,
		gift_mask=V_GiftMask,
		pay_account = V_PayAccount,
		raw_goods_type_count=V_RawGoodsTypeCount,
		raw_goods_count=V_RawGoodsCount,
		single_spec_no=V_SingleSpecNO,
		version_id=version_id+1
	WHERE trade_id=P_TradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SELECT_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SELECT_GIFT`(INOUT `P_priority` INT, IN `P_rule_id` INT,IN `P_rule_multiple_type` INT, IN `P_real_multiple` INT , IN `P_real_limit` INT , IN `P_total_name_num` INT, IN `P_total_cs_remark_num` INT,IN `P_limit_gift_stock` DECIMAL(19,4))
    SQL SECURITY INVOKER
    COMMENT '按赠品的库存优先级来选择赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,VS_spec_id,VS_is_suite,VS_gift_num,VS_real_gift_num,VS_send INT DEFAULT(0);
	DECLARE VS_Stock DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE send_cursor CURSOR FOR SELECT  spec_id,is_suite,gift_num
		FROM  cfg_gift_send_goods  
		WHERE rule_id=P_rule_id AND priority=P_priority ;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_priority=11;
	PRIORITY_LABEL: LOOP
		IF P_priority=15 THEN 
			SET P_priority=99;
			LEAVE MAIN_LABEL;
		END IF;
		SET VS_send = 0;
		OPEN send_cursor;
		SEND_LABEL: LOOP
			FETCH send_cursor INTO VS_spec_id,VS_is_suite,VS_gift_num;
			
			IF V_NOT_FOUND = 1 THEN
				SET V_NOT_FOUND = 0;
				IF VS_send THEN
					close send_cursor;
					leave MAIN_LABEL;
				ELSE
					SET P_priority=P_priority+1;
					CLOSE send_cursor;
					ITERATE PRIORITY_LABEL;
				END IF;
				
			END IF;
			
			IF VS_is_suite=0 THEN
				SELECT IFNULL(SUM(stock_num-order_num-sending_num),0) INTO VS_Stock FROM stock_spec WHERE spec_id=VS_spec_id;	
			ELSE
				SELECT SUM(tmp.suite_stock) INTO VS_Stock FROM (
				SELECT FLOOR(IFNULL(MIN(IFNULL(stock_num-order_num-sending_num, 0)/gsd.num),0)) AS suite_stock 
				FROM  goods_suite_detail gsd 
				LEFT JOIN  stock_spec ss ON ss.spec_id=gsd.spec_id 
				WHERE gsd.suite_id=VS_spec_id GROUP BY ss.warehouse_id
				) tmp;
			END IF;
			
			SET VS_real_gift_num=VS_gift_num;
			/*
			SET VS_real_gift_num=0;
			
			IF P_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=P_total_cs_remark_num;
				
			ELSEIF P_total_name_num>0 THEN 
				SET VS_real_gift_num=P_total_name_num;
				
			ELSE
				
				IF P_rule_multiple_type=0 THEN 
					IF P_real_multiple<>10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				ELSE
					IF P_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			IF VS_Stock-P_limit_gift_stock<VS_real_gift_num THEN
				SET P_priority=P_priority+1;
				SET VS_send = 0;
				CLOSE send_cursor;
				ITERATE PRIORITY_LABEL;
			ELSE
				SET VS_send = 1;
			END IF;
			
		END LOOP;
		CLOSE send_cursor;
		LEAVE MAIN_LABEL;
	END LOOP; 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SEND_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SEND_GIFT`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_CustomerID` INT, INOUT `P_SendOK` INT)
    SQL SECURITY INVOKER
    COMMENT '计算赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	
	/*使用变量*/
	DECLARE VS_sel_rule_group,VS_spec_match,VS_suite_match,
		VS_class_num,VS_suite_class_num,VS_spec_class_num,
		VS_brand_num,VS_spec_brand_num,VS_suite_brand_num,
		
		VS_brand_multiple_num,VS_spec_brand_multiple_num,VS_suite_brand_multiple_num,VS_brand_mutiple,
		VS_class_multiple_num,VS_spec_class_multiple_num,VS_suite_class_multiple_num,VS_class_mutiple,
		
		VS_specify_mutiple,VS_real_multiple,VS_real_limit,VS_priority,
		
		VS_keyword_len,VS_begin ,VS_end,VS_num,VS_total_cs_remark_num,VS_total_name_num,
		VS_real_gift_num,VS_rec_id,V_Exists,V_First,VS_cur_count,VS_cur_rule,VS_receivable_mutiple INT DEFAULT(0) ;
	
	DECLARE VS_pos TINYINT DEFAULT(1);
	DECLARE V_ApiTradeID BIGINT DEFAULT(0);
	
	DECLARE 
		VS_class_amount,VS_suite_class_amount,VS_spec_class_amount,
		VS_brand_amount,VS_spec_brand_amount,VS_suite_brand_amount,VS_post_cost
		DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VS_type,VT_delivery_term TINYINT DEFAULT(1);
	
	
	/*子订单变量*/
	DECLARE VTO_spec_id,VTO_suite_id,VTO_num,VTO_suite_num,VTO_share_amount INT ;
	DECLARE VT_trade_no,VTO_goods_name,VTO_spec_name VARCHAR(150) DEFAULT('');
	
	/*订单变量*/
	DECLARE VT_shop_id,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,VT_remark_flag,
		VT_receiver_province,VT_receiver_city,VT_receiver_district INT ;
	
	DECLARE VS_NOW,VT_trade_time,VT_pay_time,V_start_time,V_end_time DATETIME;
	DECLARE VT_goods_amount,VT_post_amount,VT_discount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VT_buyer_message,VT_cs_remark,V_ClassPath,VT_receiver_address VARCHAR(1024);
	
	/*规则列表变量*/
	DECLARE V_rule_type BIGINT DEFAULT(0) ;
	
	DECLARE V_send_spec_id,V_send_is_suite,V_send_gift_num INT DEFAULT(0);
	
	DECLARE V_rule_id,V_rule_priority,V_rule_group,V_rule_multiple_type,
		V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
		V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
		V_limit_customer_send_count,V_cur_gift_send_count,V_max_gift_send_count,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_breceivable_multiple,V_limit_receivable_count INT;
		
	DECLARE V_bbuyer_message,V_bcs_remark,V_time_type,V_is_enough_gift TINYINT;
	DECLARE V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,
		V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
		V_min_class_amount,V_max_class_amount,V_min_brand_amount,V_max_brand_amount,V_limit_gift_stock,V_receivable_multiple_amount DECIMAL(19, 4) DEFAULT(0.0000);
	DECLARE V_class_amount_type,V_brand_amount_type,V_terminal_type INT;
	DECLARE V_rule_no,V_rule_name,V_flag_type,V_shop_list,V_logistics_list,V_warehouse_list,V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,
		V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_buyer_message_key_word,V_addr_key_word VARCHAR(150);
	
	-- 赠品规则
	DECLARE rule_cursor CURSOR FOR SELECT  rec_id,rule_no,rule_name,rule_priority,rule_group,is_enough_gift,limit_gift_stock,rule_multiple_type,rule_type,bbuyer_message,bcs_remark,flag_type,time_type,start_time,end_time,shop_list,logistics_list,warehouse_list,
		min_goods_count,max_goods_count,min_goods_type_count,max_goods_type_count,min_specify_count,max_specify_count,min_class_count,max_class_count,class_count_type,min_brand_count,max_brand_count,brand_count_type,
		specify_count,bspecify_multiple,limit_specify_count,class_multiple_count,bclass_multiple,limit_class_count,class_multiple_type,brand_multiple_count,bbrand_multiple,limit_brand_count,brand_multiple_type,
		min_goods_amount,max_goods_amount,min_receivable,max_receivable,min_nopost_receivable,max_nopost_receivable,min_post_amount,max_post_amount,min_weight,max_weight,min_post_cost,max_post_cost,min_specify_amount,max_specify_amount,
		min_class_amount,max_class_amount,class_amount_type,min_brand_amount,max_brand_amount,brand_amount_type,
		buyer_rank,pay_start_time,pay_end_time,trade_start_time,trade_end_time,terminal_type,
		goods_key_word,spec_key_word,csremark_key_word,unit_key_word,limit_customer_send_count,cur_gift_send_count,max_gift_send_count,
		buyer_message_key_word,addr_key_word,min_no_specify_count,max_no_specify_count,buyer_class,receivable_multiple_amount,breceivable_multiple,limit_receivable_count  
		FROM  cfg_gift_rule rule 
		WHERE rule.is_disabled=0 ORDER BY rule_group,rule_priority desc;
	
	-- 子订单信息(单品)
	DECLARE trade_order_cursor1 CURSOR FOR SELECT spec_id,actual_num,share_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 and sto.suite_id=0;
	
	-- 子订单信息(组合装)
	DECLARE trade_order_cursor2 CURSOR FOR SELECT suite_id,suite_num,suite_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 AND sto.suite_id>0 group by sto.suite_id;
	
	
	-- 子订单名称信息(组合装名称只取一次)
	DECLARE trade_order_name_cursor CURSOR FOR SELECT distinct ato.goods_name,ato.spec_name
		FROM api_trade_order ato 
		LEFT JOIN sales_trade_order sto 
		ON (ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid) 
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0;
	
	
	-- 赠品数量范围
	DECLARE send_goods_cursor CURSOR FOR SELECT spec_id,gift_num,is_suite
		FROM cfg_gift_send_goods
		WHERE rule_id=V_rule_id AND priority=VS_priority;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET VS_NOW = NOW();	
		-- 订单信息
	SELECT trade_no,shop_id,trade_time,pay_time,goods_count,goods_type_count,customer_id,warehouse_id,logistics_id,
		receiver_province,receiver_city,receiver_district,buyer_message,cs_remark,remark_flag,
		goods_amount,post_amount,receivable,receivable-post_amount,weight,post_cost,delivery_term,receiver_address
	INTO VT_trade_no,VT_shop_id,VT_trade_time,VT_pay_time,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,
		VT_receiver_province,VT_receiver_city,VT_receiver_district,VT_buyer_message,VT_cs_remark,VT_remark_flag,
		VT_goods_amount,VT_post_amount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost,VT_delivery_term,VT_receiver_address
	FROM  sales_trade st
	WHERE st.trade_id=P_TradeID;
		
	IF V_NOT_FOUND = 1 THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 记录选中的分组
	SET @sys_code=0, @sys_message='OK';
	SET VS_sel_rule_group=-1;
	SET V_First=1;
	
	OPEN rule_cursor;
	GIFT_RULE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		SET VS_total_name_num =0;
		SET VS_total_cs_remark_num =0;
		SET VS_cur_count = 0;
		FETCH rule_cursor INTO V_rule_id,V_rule_no,V_rule_name,V_rule_priority,V_rule_group,V_is_enough_gift,V_limit_gift_stock,V_rule_multiple_type,V_rule_type,V_bbuyer_message,V_bcs_remark,V_flag_type,V_time_type,V_start_time,V_end_time,V_shop_list,V_logistics_list,V_warehouse_list,
			V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
			V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
			V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
			V_min_class_amount,V_max_class_amount,V_class_amount_type,V_min_brand_amount,V_max_brand_amount,V_brand_amount_type,
			V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,V_terminal_type,
			V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_limit_customer_send_count,V_cur_gift_send_count,
			V_max_gift_send_count,V_buyer_message_key_word,V_addr_key_word,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_receivable_multiple_amount,V_breceivable_multiple,V_limit_receivable_count;
		
		IF V_NOT_FOUND <> 0 THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		/*一个分组内只匹配一个赠品规则*/
		IF VS_sel_rule_group !=-1 AND VS_sel_rule_group=V_rule_group THEN
			ITERATE  GIFT_RULE_LABEL;
		END IF;
		
		/*此规则下没有设置赠品*/
		SELECT count(1) INTO VS_rec_id FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;
		
		-- VS_specify_mutiple VS_class_mutiple VS_brand_mutiple 
		-- 都满足的情况下VS_real_multiple来记录最小(大)的倍数关系
		
		IF V_rule_multiple_type=0 THEN 
			SET VS_real_multiple=10000;
			SET VS_real_limit=10000;
		ELSE 
			SET VS_real_multiple=-10000;
			SET VS_real_limit=-10000;	
		END IF;
	
		/*检查该赠品都设置了哪些条件*/
		
		/*检查订单是否满足用户设置的赠品条件*/
		
		/*买家留言*/
		/*IF (V_rule_type & 1) THEN
			IF  V_bbuyer_message THEN 
				IF  VT_buyer_message IS NOT NULL AND  VT_buyer_message<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_buyer_message IS  NULL OR  VT_buyer_message='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		/*客服备注*/
		/*IF V_rule_type & (1<<1) THEN
			IF  V_bcs_remark THEN
				IF  VT_cs_remark IS NOT NULL AND  VT_cs_remark<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_cs_remark IS  NULL OR  VT_cs_remark='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		
		/*淘宝标旗*/
		/*IF V_rule_type & (1<<2) THEN
			IF FIND_IN_SET(VT_remark_flag,V_flag_type)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;*/
		
		/*有效期*/
		IF V_rule_type & (1<<3) THEN
			IF V_time_type=1 AND VT_delivery_term=1 THEN 
				IF VT_pay_time<V_start_time OR VT_pay_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 3 OR VT_delivery_term=2 THEN
				IF VT_trade_time<V_start_time OR VT_trade_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 2 THEN
				IF VS_NOW<V_start_time OR VS_NOW>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;
		
		
		/*店铺*/
		IF V_rule_type & (1<<4) THEN
			IF FIND_IN_SET(VT_shop_id,V_shop_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		
		/*物流公司*/
		/*IF V_rule_type & (1<<5) THEN
			IF FIND_IN_SET(VT_logistics_id,V_logistics_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*仓库*/
		/*IF V_rule_type & (1<<6) THEN
			IF FIND_IN_SET(VT_warehouse_id,V_warehouse_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*货品总数*/
		-- 此处有问题，合并时未刷新货品
		IF V_rule_type & (1<<7) THEN
			IF V_max_goods_count=0 THEN
				IF  VT_goods_count<V_min_goods_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_count<V_min_goods_count OR VT_goods_count>V_max_goods_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
			
		/*货品种类*/
		/*IF V_rule_type & (1<<8) THEN
			IF V_max_goods_type_count=0 THEN
				IF  VT_goods_type_count<V_min_goods_type_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_type_count<V_min_goods_type_count OR VT_goods_type_count>V_max_goods_type_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*货款总额*/
		IF V_rule_type & (1<<15) THEN
			IF V_max_goods_amount=0 THEN
				IF  VT_goods_amount<V_min_goods_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_amount<V_min_goods_amount OR VT_goods_amount>V_max_goods_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
		/*实收(包含邮费)*/
		IF V_rule_type & (1<<16) THEN
			IF V_max_receivable=0 THEN
				IF  VT_receivable<V_min_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_receivable<V_min_receivable OR VT_receivable>V_max_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
			
		/*实收(去除邮费)*/
		/*IF V_rule_type & (1<<17) THEN
			IF V_max_nopost_receivable=0 THEN
				IF  VT_nopost_receivable<V_min_nopost_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_nopost_receivable<V_min_nopost_receivable OR VT_nopost_receivable>V_max_nopost_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*邮费*/
		/*IF V_rule_type & (1<<18) THEN
			IF V_max_post_amount=0 THEN
				IF  VT_post_amount<V_min_post_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VT_post_amount<V_min_post_amount OR VT_post_amount>V_max_post_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*预估重量*/
		/*IF V_rule_type & (1<<19) THEN
			IF V_max_weight=0 THEN
				IF  VT_weight<V_min_weight THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_weight<V_min_weight OR VT_weight>V_max_weight THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
			
		/*预估邮费成本*/
		/*IF V_rule_type & (1<<20) THEN
			IF V_max_post_cost=0 THEN
				IF  VT_post_cost<V_min_post_cost THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_post_cost<V_min_post_cost OR VT_post_cost>V_max_post_cost THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*客服备注关键字*/
		/*IF V_rule_type & (1<<30) THEN
			IF (VT_cs_remark IS NULL OR VT_cs_remark='') THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_csremark_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			ELSE 
				IF NOT LOCATE(V_csremark_key_word, VT_cs_remark) THEN
				-- IF (SELECT VT_cs_remark NOT LIKE CONCAT_WS('','%',V_csremark_key_word,'%')) THEN 
					ITERATE GIFT_RULE_LABEL;
				END IF;
			END IF;*/
			
			/*客服备注：AAA1支 2支BBB 1支AAA*/
			
			/*SET VS_keyword_len = CHARACTER_LENGTH(V_csremark_key_word);
			SET VS_pos = 1;
			SET VS_num=0;
			SET VS_total_cs_remark_num=0;
			SET VS_begin=0;
			SET VS_end=0;
			
			CS_REMARK_KEYWORD_LABEL:LOOP
				SET VS_begin = LOCATE(V_csremark_key_word, VT_cs_remark, VS_pos);
				IF VS_begin = 0 THEN
					LEAVE CS_REMARK_KEYWORD_LABEL;
				END IF;
				
				IF V_unit_key_word<>'' THEN 
					SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin - 1);
					IF VS_end > 0 AND VS_begin >VS_end THEN
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 2, 2));
						IF VS_num = 0 THEN
							SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 1, 1));
						END IF;
						
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos =VS_keyword_len+VS_begin;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					ELSE
						SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin);
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_begin + VS_keyword_len, VS_end - VS_begin - VS_keyword_len));
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos = VS_end;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					END IF;
				ELSE
					SET VS_total_cs_remark_num=VS_total_cs_remark_num+1;
					LEAVE CS_REMARK_KEYWORD_LABEL;	
				END IF;
			END LOOP; -- CS_REMARK_KEYWORD_LABEL
		END IF;*/
		
		
		/*
		指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		IF V_rule_type & (1<<9) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count AND tgto.num<=V_max_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类数量范围 注意组合装*/
		/*IF V_rule_type & (1<<10) THEN
			-- 未指定分类
			if V_class_count_type=0 then 
				ITERATE  GIFT_RULE_LABEL;
			end if;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_count_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_class_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_num=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_count=0 THEN
				IF  VS_class_num<V_min_class_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_num<V_min_class_count OR VS_class_num>V_max_class_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌数量范围 注意组合装*/
		
		/*IF V_rule_type & (1<<11) THEN
			-- 未指定品牌
			IF V_brand_count_type=0  THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_brand_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_count_type;
			
			IF VS_brand_num=0 THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_count=0 THEN
				IF  VS_brand_num<V_min_brand_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_brand_num<V_min_brand_count OR VS_brand_num>V_max_brand_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*
		指定货品数量备增 
		cfg_gift_attend_goods goods_type=2记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		如果是倍增关系需要计算出来倍数关系用于I_DL_SELECT_GIFT计算库存
		*/
		/*IF V_rule_type & (1<<12) THEN
			-- 参加活动的单品集合为空
			IF V_specify_count<=0 OR NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=2) THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(tgto.num),0) INTO VTO_num
			FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
			WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=2;
			
			SET VS_specify_mutiple=FLOOR(VTO_num/V_specify_count);
			IF VS_specify_mutiple =0 OR VS_specify_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bspecify_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
			
			
		/*指定分类数量倍增*/
		/*IF V_rule_type & (1<<13) THEN
			-- 未指定分类
			IF V_class_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_multiple_type;
			IF V_NOT_FOUND THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			SET VS_class_mutiple=FLOOR(VTO_num/V_class_multiple_count);
			IF VS_class_mutiple =0 OR VS_class_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bclass_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
		-- VS_class_mutiple,V_limit_class_count 传递给I_DL_SELECT_GIFT		
		
		
		
		/*指定品牌数量倍增*/
		/*IF V_rule_type & (1<<14) THEN
			-- 未指定品牌
			IF V_brand_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_multiple_type;
			
			SET VS_brand_mutiple=FLOOR(VTO_num/V_brand_multiple_count);
			IF VS_brand_mutiple =0 OR VS_brand_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bbrand_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
			
		-- VS_brand_mutiple,V_limit_brand_count 传递给I_DL_SELECT_GIFT
		
		/*
		指定货品金额范围 
		cfg_gift_attend_goods goods_type=3记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		组合装金额suie_amount 
		*/
		
		IF V_rule_type & (1<<21) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=3) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_amount=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount AND tgto.amount<=V_max_specify_amount LIMIT 1;
			END IF;
			
			-- 无满足条件的
			IF V_Exists =0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类金额范围 注意组合装*/
		/*IF V_rule_type & (1<<22) THEN
			-- 未指定分类
			IF V_class_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_amount_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_class_amount
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_amount=0 THEN
				IF  VS_class_amount<V_min_class_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_amount<V_min_class_amount OR VS_class_amount>V_max_class_amount THEN  
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌金额范围 注意组合装*/
		/*IF V_rule_type & (1<<23) THEN
			-- 未指定品牌
			IF V_brand_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_brand_amount
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_amount_type;
			
			IF VS_brand_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_amount=0 THEN
				IF  VS_brand_amount<V_min_brand_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VS_brand_amount<V_min_brand_amount OR VS_brand_amount>V_max_brand_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*客户地区*/
		/*IF V_rule_type & (1<<24) THEN
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_buyer_area WHERE rule_id=V_rule_id AND province_id=VT_receiver_province AND city_id=VT_receiver_city) THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		
		/*客户等级V_buyer_rank fixme P_CustomerID*/
		-- ELSEIF VS_type = 26 THEN
		-- ITERATE  GIFT_RULE_LABEL;
		
		/*付款时间*/
		/*IF V_rule_type & (1<<26) THEN 
			IF (DATE_FORMAT(VT_pay_time,'%H:%i:%s')<V_pay_start_time OR DATE_FORMAT(VT_pay_time,'%H:%i:%s')>V_pay_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*拍单时间*/
		/*IF V_rule_type & (1<<27)  THEN 
			IF (DATE_FORMAT(VT_trade_time,'%H:%i:%s')<V_trade_start_time OR DATE_FORMAT(VT_trade_time,'%H:%i:%s')>V_trade_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*终端类型*/
		/*IF V_rule_type & (1<<28)  THEN
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			IF V_terminal_type=2 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&1)=0) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_terminal_type=1 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&2)) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*宝贝关键字*/
		/*IF V_rule_type & (1<<29) THEN
			IF V_goods_key_word=''AND V_spec_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_goods_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.goods_name 
									LIKE CONCAT_WS('','%',V_goods_key_word,'%')
									) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_spec_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.platform_id=sto.platform_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.spec_name 
									LIKE CONCAT_WS('','%',V_spec_key_word,'%')
								) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET VS_total_name_num=0;
			OPEN trade_order_name_cursor;
			NAME_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_name_cursor INTO VTO_goods_name,VTO_spec_name;
					IF V_NOT_FOUND <> 0 THEN
						LEAVE NAME_LABEL;
					END IF;
					
					IF V_goods_key_word<>'' AND V_spec_key_word='' AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word='' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word<>'' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
				END LOOP; -- NAME_LABEL
			CLOSE trade_order_name_cursor;
		END IF;*/

		/*指定赠送次数(适用于前多少名的赠送方式)*/
		/*IF V_rule_type & (1<<31) THEN
			IF V_max_gift_send_count AND V_cur_gift_send_count>=V_max_gift_send_count THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定赠品根据客户限送次数*/
		/*IF V_rule_type & (1<<32) THEN
			IF V_limit_customer_send_count THEN
				SELECT COUNT(1) INTO VS_cur_count FROM sales_gift_record  WHERE rule_id = V_rule_id AND customer_id = P_CustomerID AND created>=V_start_time AND created<=V_end_time;
				IF VS_cur_count >= V_limit_customer_send_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*指定买家留言关键词*/
		/*IF V_rule_type & (1<<33) THEN
			IF V_buyer_message_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_buyer_message = ''  OR VT_buyer_message IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_buyer_message_key_word, VT_buyer_message) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定收件人地址关键词*/
		/*IF V_rule_type & (1<<34) THEN
			IF V_addr_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_receiver_address = ''  OR VT_receiver_address IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_addr_key_word, VT_receiver_address) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		/*
		不送指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		/*IF V_rule_type & (1<<35) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_no_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count AND tgto.num<=V_max_no_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*客户分组送赠品*/
		/*IF V_rule_type & (1<<36) THEN
			IF  V_buyer_class = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT EXISTS(SELECT 1 FROM crm_customer WHERE customer_id = P_CustomerID AND class_id = V_buyer_class) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*订单实收(不包含邮费)倍增*/
		/*IF V_rule_type & (1<<37) THEN
			-- 查看
			IF V_receivable_multiple_amount = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET VS_receivable_mutiple=FLOOR(VT_nopost_receivable/V_receivable_multiple_amount);
			IF VS_receivable_mutiple =0 OR VS_receivable_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_breceivable_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
		
		-- citying

		/*订单满足赠品条件 根据优先级和库存确定赠品即VS_priority (倍增条件下考虑翻倍数量,数量从客服备注提取的情况下考虑库存)*/
		set V_NOT_FOUND=0;
		set VS_rec_id=0;

		SELECT COUNT(DISTINCT priority),IFNULL(priority,11)
		INTO VS_rec_id,VS_priority 
		FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;

		/*如果开启校验赠品库存,则都要去校验库存,否则的话则多个赠品列表的才去计算优先级*/
		IF  V_is_enough_gift THEN
			SET  VS_priority=11;
			CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,V_limit_gift_stock);
			-- IF VS_priority = 99 THEN
			IF VS_priority > 11 THEN -- 赠品库存数量不足时 VS_priority++ 目前没有做赠品优先级,只要有一个赠品不满足即不赠送货品
				SET  VS_priority=11;
				ITERATE GIFT_RULE_LABEL;
			END IF;
		/*ELSE
			--  指定多个赠品列表的情况下才去按库存计算优先级
			IF VS_rec_id>1 THEN 
				SET  VS_priority=11;
				CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,0);
				IF VS_priority = 99 THEN
					SET VS_priority = 11;
				END IF;
			END IF;*/
		END IF;
		
		/*添加赠品*/
		OPEN send_goods_cursor;
		SEND_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH send_goods_cursor INTO V_send_spec_id,V_send_gift_num,V_send_is_suite;
			-- 设置了规则 却没有设置赠品
			IF V_NOT_FOUND <> 0 THEN
				CLOSE send_goods_cursor;
				ITERATE GIFT_RULE_LABEL;
			END IF;

			-- 目前只按照赠品数量计算(没有客服备注提取、宝贝关键字计算、倍增)
			SET VS_real_gift_num=V_send_gift_num;
			
			-- 客服备注的优先级最高 名称提取其次
			-- VS_real_gift_num 是真正的赠送数量
			/*SET VS_real_gift_num=0;
			
			IF VS_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=VS_total_cs_remark_num;
				
			ELSEIF VS_total_name_num>0 THEN 
				SET VS_real_gift_num=VS_total_name_num;
				
			ELSE
				-- 有倍增关系 看是否大于VS_real_limit
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;
			END IF ;*/
			
			CALL I_SALES_ORDER_INSERT(P_OperatorID, P_TradeID, 
				V_send_is_suite, V_send_spec_id, 1, VS_real_gift_num, 0, 0, 
				'自动赠品',
				CONCAT_WS ('',"自动添加赠品。使用策略编号：",V_rule_no,"策略名称：",V_rule_name), 
				V_ApiTradeID);
			
			-- 失败日志
			IF @sys_code THEN
				-- 回滚事务,否则下面日志无法保存
				ROLLBACK;
				-- 停用此赠品策略
				UPDATE cfg_gift_rule SET is_disabled=1 WHERE rec_id=V_rule_id;
				
				INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
				VALUES(60,P_TradeID,0,P_OperatorID,CONCAT('自动赠送失败,策略编号:', V_rule_no, ' 错误:', @sys_message),NOW());	
				
				INSERT INTO aux_notification(type,message,priority,order_type,order_no)
				VALUES(2,CONCAT('赠品策略异常: ', V_rule_no, ' 错误:', @sys_message, ' 订单:',VT_trade_no, ' 系统已自动停用此策略'), 
					9, 1, VT_trade_no);
				
				LEAVE SEND_GOODS_LABEL;
			ELSE
				IF VS_cur_rule <> V_rule_id THEN
					UPDATE cfg_gift_rule SET history_gift_send_count = history_gift_send_count +1,cur_gift_send_count = cur_gift_send_count +1
					WHERE rec_id=V_rule_id;
					INSERT INTO sales_gift_record(rule_id,trade_id,customer_id,created)
					values(V_rule_id,P_TradeID,VT_customer_id,NOW());
					SET VS_cur_rule = V_rule_id;
				END IF;
				SET P_SendOK=1;
			END IF;
			SET VS_sel_rule_group = V_rule_group;
		END LOOP; -- SEND_GOODS_LABEL
		CLOSE send_goods_cursor;
		
		IF @sys_code THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		
	END LOOP; -- GIFT_RULE_LABEL
	CLOSE rule_cursor;
	
END//
DELIMITER ;



DROP PROCEDURE IF EXISTS `I_DL_SYNC_MAIN_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_MAIN_ORDER`(IN `P_OperatorID` INT, IN `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ModifyFlag,V_DeliverTradeID,V_WarehouseID,
		V_NewWarehouseID,V_Locked,V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsType,
		V_SalesOrderCount,V_Timestamp,V_DelayToTime,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,
		V_CustomerID,V_FlagID,V_IsMaster,V_RemarkFlag,V_Exists,
		V_ShopHoldEnabled,V_OldFreeze,V_PackageID,V_RemarkCount,V_GiftMask,V_UnmergeMask,
		V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_ApiGoodsCount,V_ApiOrderCount,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_Discount,V_Receivable,
		V_DapAmount,V_CodAmount,V_PiAmount,
		V_Paid,V_SalesGoodsCount,V_TotalWeight,V_PostCost,
		V_GoodsCost,V_ExtCodFee,V_Commission,V_PackageWeight,V_TotalVolume DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_HasSendGoods,V_HasGift,V_PlatformID,V_ApiTradeStatus,V_TradeStatus,V_GuaranteeMode,V_DeliveryTerm,V_RefundStatus,
		V_InvoiceType,V_WmsType,V_NewWmsType,V_IsAutoWms,V_IsSealed,V_IsFreezed,V_IsPreorder,V_IsExternal TINYINT DEFAULT(0);
	DECLARE V_ReceiverMobile,V_ReceiverTelno,V_ReceiverZip,V_ReceiverRing VARCHAR(40);
	DECLARE V_ShopID,V_ReceiverCountry SMALLINT DEFAULT(0);
	
	DECLARE V_SalesmanID,V_LogisticsID,V_TradeMask,V_OldLogisticsID INT;
	DECLARE V_Tid,V_WarehouseNO,V_StockoutNO,V_StockoutNO2,V_ExtMsg,V_SingleSpecNO VARCHAR(40);
	DECLARE V_AreaAlias,V_BuyerEmail,V_BuyerNick,V_ReceiverName,V_ReceiverArea VARCHAR(60);
	DECLARE V_ReceiverAddress,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(256);
	DECLARE V_TradeTime,V_PayTime,V_OldTradeTime DATETIME;
	DECLARE V_Remark,V_BuyerMessage VARCHAR(1024);
	
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
	START TRANSACTION;
	
	SELECT modify_flag,platform_id,tid,trade_status,refund_status,delivery_term,guarantee_mode,deliver_trade_id,pay_time,pay_account,
		receivable,goods_amount,post_amount,other_amount,dap_amount,cod_amount,pi_amount,ext_cod_fee,paid,discount,invoice_type,
		invoice_title,invoice_content,stockout_no,trade_mask,is_sealed,wms_type,is_auto_wms,warehouse_no,shop_id,logistics_type,
		buyer_nick,receiver_name,receiver_province,receiver_city,receiver_district,receiver_area,receiver_ring,receiver_address,
		receiver_zip,receiver_telno,receiver_mobile,remark_flag,remark,buyer_message,is_external
	INTO V_ModifyFlag,V_PlatformID,V_Tid,V_ApiTradeStatus,V_RefundStatus,V_DeliveryTerm,V_GuaranteeMode,V_DeliverTradeID,V_PayTime,V_PayAccount,
		V_Receivable,V_GoodsAmount,V_PostAmount,V_OtherAmount,V_DapAmount,V_CodAmount,V_PiAmount,V_ExtCodFee,V_Paid,V_Discount,V_InvoiceType,
		V_InvoiceTitle,V_InvoiceContent,V_StockoutNO,V_TradeMask,V_IsSealed,V_WmsType,V_IsAutoWms,V_WarehouseNO,V_ShopID,V_LogisticsType,
		V_BuyerNick,V_ReceiverName,V_ReceiverProvince,V_ReceiverCity,V_ReceiverDistrict,V_ReceiverArea,V_ReceiverRing,V_ReceiverAddress,
		V_ReceiverZip,V_ReceiverTelno,V_ReceiverMobile,V_RemarkFlag,V_Remark,V_BuyerMessage,V_IsExternal
	FROM api_trade WHERE rec_id=P_ApiTradeID FOR UPDATE;
	
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
					-- 记录备注
					INSERT INTO api_trade_remark_history(platform_id,tid,remark) VALUES(V_PlatformID,V_Tid,V_Remark);
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
				
				IF V_IsPreorder THEN
					SET V_ExtMsg = ' 进预订单原因:客服备注提取';	
				END IF;
				
				-- 客户备注
				SET V_BuyerMessage=TRIM(V_BuyerMessage);
				CALL I_DL_EXTRACT_CLIENT_REMARK(V_BuyerMessage, V_FlagID, V_WmsType, V_WarehouseID, V_IsFreezed);
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
				
				SELECT MAX(IF(refund_status>2,1,0)),MAX(IF(refund_status=2,1,0))
				INTO V_Max,V_Min
				FROM tmp_sales_trade_order;
				
				-- 更新主订单退款状态
				IF V_SalesGoodsCount<=0 THEN
					SET V_NewRefundStatus=IF(V_Max,3,4);
					SET V_TradeStatus=5;
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
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
					SELECT IF(suite_id,suite_no,spec_no) INTO V_SingleSpecNO 
					FROM tmp_sales_trade_order WHERE actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1; 
				ELSE
					SET V_SingleSpecNO='';
				END IF;
				-- 估算邮费
				CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_TotalWeight, V_LogisticsID, V_ShopID, V_NewWarehouseID, 
					0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict);
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
				IF V_NewWarehouseID = 0 THEN
					SELECT warehouse_id INTO V_NewWarehouseID FROM cfg_warehouse where is_disabled = 0 limit 1;
				END IF;
				-- 获取物流
				IF V_LogisticsID = 0 THEN
						IF V_DeliveryTerm=2 THEN
							SELECT cod_logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						ELSE 
							SELECT logistics_id INTO V_LogisticsID FROM cfg_shop where shop_id = V_ShopID;
						END IF;
				END IF;
				-- 估算货品成本
				SELECT SUM(tsto.actual_num*IFNULL(ss.cost_price,0)) INTO V_GoodsCost FROM tmp_sales_trade_order tsto LEFT JOIN stock_spec ss ON ss.warehouse_id=V_NewWarehouseID AND ss.spec_id=tsto.spec_id
				WHERE tsto.actual_num>0;
				SET V_AreaAlias = '';
				-- 更新订单
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
					logistics_id=V_LogisticsID,receiver_dtb=V_AreaAlias,flag_id=V_FlagID,salesman_id=V_SalesmanID,
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
					SELECT V_DeliverTradeID,P_OperatorID,19,V_IsFreezed,CONCAT('自动冻结,冻结原因:',title)
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,5,CONCAT('首付款:',V_Tid));
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
							
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,CONCAT(IF(V_ApiTradeStatus=80,'订单退款','订单关闭'),',拦截出库单'));
						
							INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
							-- 标记退款
							UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
			
			-- 清除子订单状态变化
			UPDATE api_trade_order SET modify_flag=0 WHERE platform_id=V_PlatformID and tid=V_Tid;
			UPDATE api_trade SET modify_flag=0,process_status=70 WHERE rec_id=P_ApiTradeID;
			COMMIT;
			LEAVE MAIN_LABEL;
		ELSEIF V_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=V_ShopID AND src_tid=V_Tid;
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
		
		-- 提取业务员
		CALL I_DL_EXTRACT_REMARK(V_Remark, V_LogisticsID, V_FlagID, V_SalesmanID, V_WmsType, V_WarehouseID, V_IsPreorder, V_IsFreezed);
		IF V_SalesmanID THEN
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
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
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
				-- 加异常标记
				UPDATE sales_trade SET bad_reason=(bad_reason|16) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化:',V_Tid));
			ELSEIF V_TradeStatus >= 40 AND V_TradeStatus < 95 AND @cfg_remark_change_block_stockout THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|64) WHERE stockout_id=V_StockoutID;
						-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'客服备注变化,拦截出库单');
				END IF;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,CONCAT('客服备注变化,拦截出库:',V_Tid));
			ELSEIF V_TradeStatus >= 95 AND @cfg_remark_change_block_stockout THEN
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,9,CONCAT('客服备注变化,订单已发货:',V_Tid));
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
			
			-- 看地址是否有变化
			IF EXISTS(SELECT 1 FROM sales_trade st,api_trade ax
				WHERE st.trade_id=V_DeliverTradeID AND ax.platform_id=V_PlatformID AND ax.tid=V_Tid
					AND st.receiver_name=ax.receiver_name
					AND st.receiver_province=ax.receiver_province
					AND st.receiver_city=ax.receiver_city
					AND st.receiver_district=ax.receiver_district
					AND st.receiver_address=ax.receiver_address
					AND st.receiver_mobile=ax.receiver_mobile
					AND st.receiver_telno=ax.receiver_telno
					AND st.receiver_zip=ax.receiver_zip
					AND st.receiver_area=ax.receiver_area
					AND st.receiver_ring=ax.receiver_ring
					AND st.to_deliver_time=ax.to_deliver_time
					AND st.dist_center=ax.dist_center
					AND st.dist_site=ax.dist_site) THEN
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('平台收件地址变更,系统已处理:',V_Tid));
				ITERATE TRADE_BY_API_LABEL;
			END IF;
			
			-- 拦截出库单
			IF V_TradeStatus >= 40 THEN
				SELECT stockout_id INTO V_StockoutID FROM stockout_order 
				WHERE src_order_type=1 AND src_order_id=V_DeliverTradeID AND status<>5 LIMIT 1;
				
				IF V_NOT_FOUND=0 THEN
					UPDATE stockout_order SET block_reason=(block_reason|4) WHERE stockout_id=V_StockoutID;
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'收件地址变更,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
				
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
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更:',V_Tid));
				
				-- 刷新物流,大头笔,包装
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, IF(@cfg_open_package_strategy,4,0)|3, 0);
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|2) WHERE trade_id=V_DeliverTradeID;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,10,CONCAT('收件地址变更,请处理,原始单号:',V_Tid));
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
			
			-- 拦截出库单
			IF V_TradeStatus>=40 THEN
				UPDATE sales_trade_order sto,stockout_order_detail sod,stockout_order so
				SET so.block_reason=(so.block_reason|8)
				WHERE sod.src_order_type=1 AND sod.src_order_detail_id=sto.rec_id
					AND so.stockout_id=sod.stockout_id
					AND sto.trade_id=V_DeliverTradeID
					AND so.status<>5;
					
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
				-- 出库单日志??
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化，请处理:',V_Tid));
			ELSEIF V_TradeStatus<35 THEN
				UPDATE sales_trade st,api_trade ax 
				SET st.invoice_type=ax.invoice_type,
					st.invoice_title=ax.invoice_title,
					st.invoice_content=ax.invoice_content,
					st.version_id=st.version_id+1
				WHERE st.trade_id=V_DeliverTradeID and ax.platform_id=V_PlatformID AND ax.tid=V_Tid;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,11,CONCAT('发票变化:',V_Tid));
			ELSE
				UPDATE sales_trade SET bad_reason=(bad_reason|4) WHERE trade_id=V_DeliverTradeID;
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
	UPDATE api_trade SET modify_flag=0 WHERE rec_id=P_ApiTradeID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_SYNC_SUB_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_SUB_ORDER`(IN `P_OperatorID` INT,
	IN `P_RecID` BIGINT,
	IN `P_ModifyFlag` INT,
	IN `P_ApiTradeStatus` TINYINT,
	IN `P_ShopID` TINYINT,
	IN `P_Tid` VARCHAR(40),
	IN `P_Oid` VARCHAR(40),
	IN `P_RefundStatus` TINYINT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_DeliverTradeID,V_WarehouseID,V_Max,V_Min,V_NewRefundStatus,V_StockoutID,V_IsMaster,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_SalesGoodsCount,V_LeftSharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_HasSendGoods,V_TradeStatus TINYINT DEFAULT(0);
	
	DECLARE trade_order_by_api_cursor CURSOR FOR 
		SELECT DISTINCT st.trade_id,st.trade_status,st.warehouse_id
		FROM sales_trade_order sto LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)
		WHERE sto.shop_id=P_ShopID and sto.src_oid=P_Oid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
							-- 出库单日志
							INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
							VALUES(2,V_StockoutID,P_OperatorID,53,'子订单退款,拦截出库单');
						END IF;
						
						INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
						
						UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
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
				
				-- 回收库存
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
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,7,CONCAT('子订单退款:',P_Oid));
				ELSE
					INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,4,CONCAT('子订单关闭:',P_Oid));
				END IF;
				
			END LOOP;
			CLOSE trade_order_by_api_cursor;
			
			-- 重新分配邮费
			-- CALL I_RESHARE_AMOUNT_BY_TID(P_ShopID, P_Tid, V_IsMaster, 1, V_LeftSharePost);
		ELSEIF P_ApiTradeStatus=50 THEN -- 已发货
			UPDATE sales_trade_order SET is_consigned=1 WHERE shop_id=P_ShopID AND src_oid=P_Oid;
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
				FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					SET V_NewRefundStatus=IF(V_Max,3,4);
				ELSEIF V_Max=0 AND V_Min THEN
					SET V_NewRefundStatus=1;
				ELSEIF V_Max THEN
					SET V_NewRefundStatus=2;
				ELSE
					SET V_NewRefundStatus=0;
				END IF;
				
				UPDATE sales_trade SET refund_status=V_NewRefundStatus,version_id=version_id+1 WHERE trade_id=V_DeliverTradeID;
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
				-- CALL I_DL_REFRESH_TRADE(P_OperatorID, V_DeliverTradeID, 2, 0);
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
			FETCH trade_order_by_api_cursor INTO V_DeliverTradeID,V_TradeStatus,V_WarehouseID;
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
					-- 出库单日志
					INSERT INTO  stock_inout_log(order_type,order_id,operator_id,operate_type,message)
					VALUES(2,V_StockoutID,P_OperatorID,53,'平台修改货品,拦截出库单');
				END IF;
				
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,6,'拦截出库');
			END IF;
		
			UPDATE sales_trade SET bad_reason=(bad_reason|64) WHERE trade_id=V_DeliverTradeID;
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_DeliverTradeID,P_OperatorID,17,CONCAT('平台更换货品:',P_Tid));
			
		END LOOP;
		CLOSE trade_order_by_api_cursor;
		
		SET P_ModifyFlag = P_ModifyFlag & ~16;
	END IF;
	
	UPDATE api_trade_order SET modify_flag=0 WHERE rec_id=P_RecID;
	COMMIT;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_GIFT_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_GIFT_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '新建订单货品插入的临时表,为赠品准备'
MAIN_LABEL:BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_gift_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  is_suite INT(11) NOT NULL,
	  spec_id INT(11) NOT NULL,
	  num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  class_path VARCHAR(1024) NOT NULL,
	  brand_id INT(11) NOT NULL,
	  from_mask INT(11) NOT NULL,
	  PRIMARY KEY (rec_id),
	  UNIQUE INDEX UK_tmp_gift_trade_order (is_suite, spec_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_DL_TMP_SALES_TRADE_ORDER`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_SALES_TRADE_ORDER`()
    SQL SECURITY INVOKER
	COMMENT '将原始单的货品映射到订单中建立的临时表'
MAIN_LABEL: BEGIN 
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sales_trade_order(
	  rec_id INT(11) NOT NULL AUTO_INCREMENT,
	  spec_id INT(11) NOT NULL,
	  shop_id smallint(6) NOT NULL,
	  platform_id tinyint(4) NOT NULL,
	  src_oid VARCHAR(40) NOT NULL,
	  suite_id INT(11) NOT NULL DEFAULT 0,
	  src_tid VARCHAR(40) NOT NULL,
	  gift_type TINYINT(1) NOT NULL DEFAULT 0,
	  refund_status TINYINT(4) NOT NULL DEFAULT 0,
	  guarantee_mode TINYINT(4) NOT NULL DEFAULT 1,
	  delivery_term TINYINT(4) NOT NULL DEFAULT 1,
	  bind_oid VARCHAR(40) NOT NULL DEFAULT '',
	  num DECIMAL(19, 4) NOT NULL,
	  price DECIMAL(19, 4) NOT NULL,
	  actual_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  order_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_price DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  adjust DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  share_post DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  paid DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  tax_rate DECIMAL(8, 4) NOT NULL DEFAULT 0.0000,
	  goods_name VARCHAR(255) NOT NULL,
	  goods_id INT(11) NOT NULL,
	  goods_no VARCHAR(40) NOT NULL,
	  spec_name VARCHAR(100) NOT NULL,
	  spec_no VARCHAR(40) NOT NULL,
	  spec_code VARCHAR(40) NOT NULL,
	  suite_no VARCHAR(40) NOT NULL DEFAULT '',
	  suite_name VARCHAR(255) NOT NULL DEFAULT '',
	  suite_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  suite_discount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  is_print_suite TINYINT(1) NOT NULL DEFAULT 0,
	  api_goods_name VARCHAR(255) NOT NULL,
	  api_spec_name VARCHAR(40) NOT NULL,
	  weight DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  volume DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  commission DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
	  goods_type TINYINT(4) NOT NULL DEFAULT 1,
	  flag INT(11) NOT NULL DEFAULT 0,
	  large_type TINYINT(1) NOT NULL DEFAULT 0,
	  invoice_type TINYINT(4) NOT NULL DEFAULT 0,
	  invoice_content VARCHAR(255) NOT NULL DEFAULT '',
	  from_mask INT(11) NOT NULL DEFAULT 0,
	  cid INT(11) NOT NULL DEFAULT 0,
	  is_master TINYINT(1) NOT NULL DEFAULT 0,
	  is_allow_zero_cost TINYINT(1) NOT NULL DEFAULT 0,
	  remark VARCHAR(60) NOT NULL DEFAULT '',
	  PRIMARY KEY (rec_id),
	  INDEX IX_tmp_sales_trade_order_src_id (shop_id, src_oid),
	  UNIQUE INDEX UK_tmp_sales_trade_order (spec_id, shop_id, src_oid, suite_id)
	);
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_RESERVE_STOCK`;
DELIMITER //
CREATE PROCEDURE `I_RESERVE_STOCK`(IN `P_TradeID` INT, IN `P_Type` INT, IN `P_NewWarehouseID` INT, IN `P_OldWarehouseID` INT)
    SQL SECURITY INVOKER
    COMMENT '占用库存'
MAIN_LABEL:BEGIN
	IF P_OldWarehouseID THEN
		INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
		(SELECT P_OldWarehouseID,spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
			IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
		FROM sales_trade_order WHERE trade_id=P_TradeID ORDER BY spec_id)
		ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
			sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num);
		
		UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=P_TradeID;
	END IF;
	IF P_NewWarehouseID THEN
		IF P_Type = 2 THEN	-- 未付款库存
			INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num);
			
			UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 3 THEN	-- 已保留待审核
			INSERT INTO stock_spec(warehouse_id,spec_id,order_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num 
			FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num);
			
			UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 4 THEN	-- 待发货
			INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status)
			(SELECT P_NewWarehouseID,spec_id,actual_num,1 FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1;
			
			UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		ELSEIF P_Type = 5 THEN	-- 预订单库存
			INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num)
			(SELECT P_NewWarehouseID,spec_id,actual_num FROM sales_trade_order 
			WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id)
			ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num);
			
			UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=P_TradeID AND actual_num>0 AND stock_reserved<2;
			
		END IF;
	END IF;
	
	-- 更新平台货品库存变化
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=P_TradeID AND actual_num>0;
	
	-- 组合装
	INSERT INTO sys_process_background(`type`,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
		WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id;
	
END//
DELIMITER ;

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
			LEFT JOIN api_trade_order ato ON ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid
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
	IF (V_TradePlatformId<>1 AND V_TradePlatformId<>2) OR @cfg_order_allow_part_sync=0 THEN
	
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

DROP PROCEDURE IF EXISTS `I_SALES_ORDER_INSERT`;
DELIMITER //
CREATE PROCEDURE `I_SALES_ORDER_INSERT`(
	IN `P_OperatorID` INT, 
	IN `P_TradeID` INT, 
	IN `P_bSuite` INT,
	IN `P_SpecID` INT,
	IN `P_GiftType` INT,
	IN `P_Num` DECIMAL(19,4),
	IN `P_ShareAmount` DECIMAL(19,4),
	IN `P_SharePost` DECIMAL(19,4),
	IN `P_GoodsRemark` VARCHAR(255),
	IN `P_Remark` VARCHAR(255),
	INOUT `P_ApiTradeID` BIGINT)
    SQL SECURITY INVOKER
    COMMENT '插入货品作为一个子订单'
MAIN_LABEL: BEGIN
	DECLARE V_Receivable,V_GoodsAmount,V_ApiGoodsCount DECIMAL(19,4);
	DECLARE V_PayStatus TINYINT DEFAULT(0);
	DECLARE V_Message VARCHAR(256);
	DECLARE V_OrderID,V_ShopID,V_ApiOrderCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_Tid VARCHAR(40);
	DECLARE V_Now DATETIME;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 展开货品
	SET @sys_code=0;
	SET @sys_message="";
	
	IF P_GiftType THEN
		SET P_ShareAmount=0;
	END IF;
	
	SET V_Receivable=P_ShareAmount+P_SharePost;
	-- 插入原始单	
	-- IF P_Paid >= V_Receivable THEN
	-- 	SET V_PayStatus = 2; -- 已付款
	-- ELSEIF P_Paid=0 THEN
	-- 	SET V_PayStatus = 0; -- 未付款
	-- ELSE
	-- 	SET V_PayStatus = 1; -- 部分付款
	-- END IF;
	
	
	-- START TRANSACTION;
	SET V_Now = NOW();
	-- 查找是否存在一个手工建单
	IF P_ApiTradeID=0 THEN
		SELECT ax.rec_id,ax.tid INTO P_ApiTradeID,V_Tid  
		FROM sales_trade_order sto,api_trade ax 
		WHERE sto.trade_id=P_TradeID AND sto.platform_id=0 AND ax.platform_id=0 AND ax.tid=sto.src_tid AND V_Tid IS NOT NULL AND V_Tid<>'' LIMIT 1;
	END IF;
	
	-- 没找到，则手工新建一个
	IF P_ApiTradeID=0 THEN
		SET V_Tid = FN_SYS_NO("apitrade");
		
		INSERT INTO api_trade(platform_id, shop_id, tid, process_status, trade_status, guarantee_mode, pay_status, delivery_term, pay_method,
			order_count, goods_count, trade_time, pay_time,
			buyer_nick, buyer_name, buyer_area, pay_id, 
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address, 
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, receiver_hash,
			goods_amount, post_amount, discount, receivable, paid, received, 
			invoice_type, invoice_title, invoice_content, trade_from,created)
		SELECT 0, shop_id, V_Tid, 20, 30, 2, 2, 1, 1, 
			1, P_Num,  V_Now,  V_Now,
			buyer_nick, receiver_name, receiver_area, '',
			receiver_name, receiver_province, receiver_city, receiver_district, receiver_address,
			receiver_mobile, receiver_telno, receiver_zip, receiver_area, 
			MD5(CONCAT(receiver_province,receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno,receiver_zip)),
			0, 0, 0, 0, 0, 0,
			0, '', '', 2,V_Now 
		FROM  sales_trade
		WHERE trade_id=P_TradeID LIMIT 1;
		
		SET P_ApiTradeID = LAST_INSERT_ID();
	ELSE
		SELECT tid INTO V_Tid FROM api_trade WHERE rec_id=P_ApiTradeID;
	END IF;
	
	SELECT shop_id INTO V_ShopID FROM api_trade WHERE rec_id=P_ApiTradeID;
	-- 补原始子订单数据 
	IF P_bSuite=0 THEN
		SET @tmp_specno='',@tmp_goodsname='',@tmp_specname='';
		
		INSERT INTO api_trade_order(platform_id,shop_id, tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount,share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gg.goods_id,gg.goods_no,gs.spec_id, (@tmp_specno:=gs.spec_no),(@tmp_goodsname:=gg.goods_name),(@tmp_specname:=gs.spec_name),gs.spec_code,
			P_GiftType,P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,
			P_ShareAmount,P_SharePost,0,P_GoodsRemark,V_Now 
		FROM  goods_spec gs 
		LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
		WHERE gs.spec_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='货品不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加单品，商家编码：', @tmp_specno, ' 货品名称： ', @tmp_goodsname, ' 规格名称： ', @tmp_specname ,' 数量： ', P_Num);
		END IF;
		
	ELSE 
		INSERT INTO api_trade_order(platform_id,shop_id , tid, oid, `status`, process_status,
			goods_id,goods_no, spec_id,spec_no, goods_name, spec_name, spec_code, gift_type,
			num, price, discount, total_amount, share_amount, share_post, paid, remark, created)
		SELECT 0,V_ShopID,V_Tid,FN_SYS_NO("apiorder"), 30, 10,
			gs.suite_id,gs.suite_no,gs.suite_id,(@tmp_specno:=gs.suite_no),(@tmp_goodsname:=gs.suite_name),'','', P_GiftType,
			P_Num,gs.retail_price,gs.retail_price*P_Num-P_ShareAmount,gs.retail_price*P_Num,P_ShareAmount,P_SharePost, 0, P_GoodsRemark, V_Now 
		FROM  goods_suite gs 
		WHERE gs.suite_id=P_SpecID;
		
		SET V_OrderID=LAST_INSERT_ID();
		
		IF ROW_COUNT()=0 THEN
			SET @sys_code=3,@sys_message='组合装不存在';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF P_Remark<>'' THEN
			SET V_Message = P_Remark;
		ELSEIF P_GiftType THEN
			SET V_Message = CONCAT('添加赠品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		ELSE
			SET V_Message = CONCAT('添加货品，组合装商家编码：', @tmp_specno, ' 名称： ', @tmp_goodsname, ' 数量： ', P_Num);
		END IF;
	END IF;
	
	-- 映射货品
	CALL I_DL_MAP_TRADE_GOODS(P_TradeID, P_ApiTradeID, 0, V_ApiOrderCount, V_ApiGoodsCount);
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*IF V_ApiOrderCount <> 1 OR V_ApiGoodsCount <> P_Num THEN
		SET @sys_code=7, @sys_message = '单品数量不一致';
		LEAVE MAIN_LABEL;
	END IF;*/
	
	UPDATE api_trade_order SET process_status=20 WHERE rec_id=V_OrderID;
	
	-- 日志
	INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
	VALUES(60,P_TradeID,P_SpecID,P_OperatorID,V_Message,V_Now);	
	
	-- 更新原始单金额数据
	UPDATE api_trade `at`,
		(
			SELECT SUM(share_amount+discount) goods_amount,
				SUM(share_post) post_amount,SUM(discount) discount
			FROM api_trade_order ato 
			WHERE platform_id=0 AND tid=V_Tid
		) da	
	SET 
		`at`.goods_amount =da.goods_amount,
		`at`.post_amount =da.post_amount,
		`at`.discount =da.discount,
		`at`.receivable=V_Receivable,
		`at`.modify_flag=0
	WHERE rec_id=P_ApiTradeID;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	END IF;
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		IF @cfg_sales_trade_trace_operator THEN
			SELECT fullname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_IMPLEMENT_CLEAN`;
DELIMITER //
CREATE PROCEDURE SP_IMPLEMENT_CLEAN(IN P_CleanId INT)
  SQL SECURITY INVOKER
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;

	-- 清空账款信息和统计信息
	IF P_CleanId <> 6 AND P_CleanId <> 7 THEN


		-- 统计
-- 		TODO 统计的部分表在做完统计模块后需要打开

		DELETE  FROM stat_daily_sales_amount;

 		DELETE  FROM stat_monthly_sales_amount;

	END IF;
	-- 全清(货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关)
	IF P_CleanId = 1 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;
		
		-- crm

		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;
		-- purchase
		DELETE FROM purchase_order_log;
		DELETE FROM purchase_order_detail;
		DELETE FROM purchase_order;
		-- goods
      
		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;
		DELETE FROM goods_log;

		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;

		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		
		
		-- stock

		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		
		-- 关联表
		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		
		
		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;
		
		DELETE FROM stock_transfer_detail;
		DELETE FROM stock_transfer;
		--  清除库存同步记录
		DELETE FROM api_stock_sync_record;

		-- 通知消息 new add
		DELETE FROM sys_notification;


		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM cfg_employee_rights WHERE employee_id > 1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		--  订单全链路
		DELETE FROM sales_trade_trace;
		-- 客服备注修改历史记录  new add
		DELETE FROM api_trade_remark_history;
		-- 订单备注提取策略 new add
		DELETE FROM cfg_trade_remark_extract;
		-- cfg
		DELETE FROM cfg_stock_sync_rule;

		-- sys
		DELETE FROM sys_other_log;
		DELETE FROM sys_process_background;
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 1, '清除系统所有信息');
	END IF;
	-- 清除货品信息(清除：订单、库存、事务，保留客户、员工信息）
	IF P_CleanId = 2 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;





		DELETE FROM api_goods_spec;
		DELETE FROM goods_merchant_no;
		DELETE FROM goods_barcode;

		DELETE FROM goods_log;



		DELETE FROM goods_suite_detail;
		DELETE FROM goods_suite;


		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;
		DELETE FROM goods_spec;
		DELETE FROM goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;


		-- stock

		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;


		DELETE FROM stockin_order_detail;

		DELETE FROM stockin_order;


		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 2, '清除货品信息(清除：订单、库存，保留客户、员工信息)');
	END IF;
	-- 清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工)
	IF P_CleanId = 3 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;

		-- crm
		DELETE FROM crm_customer_telno;
		DELETE FROM crm_customer_address;
		DELETE FROM crm_customer_log;
		DELETE FROM crm_platform_customer;
		DELETE FROM crm_customer;






		-- stock

 		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;



		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 3, '清除客户资料(清除：订单、库存，保留货品(单品、组合装)、员工信息)');
	END IF;
	-- 清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户、供货商信息)
	IF P_CleanId = 4 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;

		DELETE FROM api_trade_order;
		DELETE FROM api_trade;

		DELETE FROM api_trade_discount;

		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;




		DELETE FROM stock_spec_detail;
		DELETE FROM stock_spec;


		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;

		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;

 		DELETE FROM stock_logistics_no;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;




		-- hr
		-- UPDATE hr_employee SET position_id=1,department_id=1 WHERE employee_id=1;
		DELETE FROM hr_employee WHERE employee_id > 1;

		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;


		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 4, '清除员工资料(清除：订单、库存，保留货品(单品、组合装)、客户信息)');
	END IF;
	-- 清除订单、采购信息、库存调拨等相关库存订单信息(库存量由脚本重刷)
	IF P_CleanId = 5 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		-- api
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		-- sales
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

		DELETE FROM stock_pd_detail;
		DELETE FROM stock_pd;

		DELETE FROM stockout_order_detail_position;
		DELETE FROM stockout_order_detail;
		DELETE FROM stockout_order;
		DELETE FROM stockin_order_detail;
		DELETE FROM stockin_order;
		DELETE FROM stock_inout_log;
		DELETE FROM stock_change_history;
		DELETE FROM stock_spec_detail;
-- zhuyi1
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0,purchase_num=0,
			to_purchase_num=0,purchase_arrive_num=0,refund_num=0,transfer_num=0,return_num=0,return_exch_num=0,
			return_onway_num=0,refund_exch_num=0,refund_onway_num=0,default_position_id=IF(default_position_id=0,-warehouse_id,default_position_id);
		-- INSERT INTO stock_spec_detail(stock_spec_id,spec_id,stockin_detail_id,position_id,position_no,zone_id,zone_no,cost_price,stock_num,virtual_num,created)
		--	SELECT ss.rec_id,ss.spec_id,0,ss.default_position_id,cwp.position_no,cwz.zone_id,cwz.zone_no,ss.cost_price,ss.stock_num,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
 		-- INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,created)
		--	SELECT ss.warehouse_id,ss.spec_id,ss.default_position_id,cwz.zone_id,ss.stock_num,NOW()
		--	FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
		--	LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;

		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message)
			-- VALUES(13, P_UserId, 5, ' 清除订单、采购、盘点、等相关库存信息');
	END IF;


	-- 清除订单信息
	IF P_CleanId = 8 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';

		-- api  删除原始订单和退换单
		DELETE FROM api_trade_order;
		DELETE FROM api_trade;
		DELETE FROM api_trade_discount;
		DELETE FROM api_refund_order;
		DELETE FROM api_refund;
		-- sales 删除原始订单和退换单
		DELETE FROM sales_trade_log;
		DELETE FROM sales_trade_order;
		DELETE FROM sales_trade;
		DELETE FROM sales_refund_out_goods;
		DELETE FROM sales_refund_order;
		DELETE FROM sales_refund_log;
		DELETE FROM sales_refund;
		DELETE FROM sales_tmp_refund_order;

	
		-- 销售出库单未在stock_change_history里插入数据的都可以删除，在stock_change_history里插入数据的将入库类型改为其他入库
		UPDATE stockout_order so,stockout_order_detail sod,stock_change_history sch
			SET so.src_order_type=7,so.src_order_id=0,so.src_order_no='',sod.src_order_type=7 ,sod.src_order_detail_id=0,
			sch.src_order_type=7, sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=1 AND so.stockout_id=sod.stockout_id
			AND so.stockout_id=sch.stockio_id AND sch.type=2;

		DELETE sodp.* FROM stockout_order_detail_position sodp,stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND sod.rec_id=stockout_order_detail_id AND so.src_order_type=1 ;

		-- 删除未出库的出库单管理的stockout_pack_order,stockout_pack_order_detail 必须先删 有外键
-- 		DELETE spod.*  FROM stockout_pack_order spo,stockout_pack_order_detail spod,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id AND spo.pack_id=spod.pack_id AND so.src_order_type=1;

-- 		DELETE spo.*  FROM stockout_pack_order spo,stockout_order so
-- 			WHERE so.stockout_id=spo.stockout_id  AND so.src_order_type=1;

		-- 删除未出库的出库单和出库单详情
		DELETE sod.* FROM stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND so.src_order_type=1 ;

		DELETE so.* FROM stockout_order so WHERE so.src_order_type=1 ;
		-- 清空打印批次相关的数据


		-- stockin
		-- 将退货入库的入库单改成其他入库
		UPDATE stockin_order so,stockin_order_detail sod,stock_change_history sch
			SET so.src_order_type=6,so.src_order_id=0,so.src_order_no='',sod.src_order_type=6,sod.src_order_detail_id=0,
			sch.src_order_type=6,sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  AND so.stockin_id=sch.stockio_id
			AND sch.type=1;

		-- 删除未入库的入库单和入库单详情
		DELETE sod.* FROM stockin_order so,stockin_order_detail sod
			WHERE so.src_order_type=3 AND so.stockin_id=sod.stockin_id  ;

		DELETE so.* FROM stockin_order so WHERE so.src_order_type=3 ;
		-- stock
		-- 将stock_spec中的未付款量，预订单量，待审核量，待发货量清0    销售退货量 销售换货在途量（发出和收回）这三个暂时没用 所以没有清0
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0;
		-- 将stock_spec_detail中的占用量清0
		UPDATE stock_spec_detail SET reserve_num=0,is_used_up=0;
		-- 删除日志表中有关订单操作的日志
		DELETE FROM stock_inout_log WHERE order_type=2 AND operate_type IN(1,2,3,4,7,14,23,24,51,52,62,63,111,113,120,121,300);
		-- 插入系统日志
		-- INSERT INTO sys_log_other(`type`,operator_id,`data`,message) VALUES(13, P_UserId, 8,'清除订单信息，和订单相关的出库单，入库单的类型变为其他出库，其他入库');
		-- -- stockout_order 中的字段consign_status,customer_id等没有用了，
	END IF;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `SP_INT_ARR_TO_TBL`;
DELIMITER //
CREATE PROCEDURE `SP_INT_ARR_TO_TBL`(IN `P_Str` VARCHAR(8192), IN `P_Clear` INT)
    SQL SECURITY INVOKER
    COMMENT '将字符串数组插入到临时表，如1,2,4,2'
MAIN_LABEL:BEGIN
	DECLARE V_I1, V_I2, V_I3 BIGINT;
	DECLARE V_IT VARCHAR(255);
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_xchg(
		rec_id int(11) NOT NULL AUTO_INCREMENT,
		f1 VARCHAR(40),
		f2 VARCHAR(1024),
		f3 VARCHAR(40),
		PRIMARY KEY (rec_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	IF P_Str IS NULL OR LENGTH(P_Str)=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_Clear THEN
		DELETE FROM tmp_xchg;
	END IF;
	
	IF P_Str=' ' THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_I1 = 1;
	STR_LABEL:LOOP
	 SET V_I2 = locate(',', P_Str, V_I1);
	 IF V_I2 = 0 THEN
	   SET V_IT = substring(P_Str, V_I1);
	 ELSE
	   SET V_IT = substring(P_Str, V_I1, V_I2 - V_I1);
	 END IF;
	 
	 IF V_IT IS NOT NULL THEN
		set V_I3 = cast(V_IT as signed);
		INSERT INTO tmp_xchg(f1) VALUES(V_I3);
	 END IF;
	 
	 IF V_I2 = 0 OR V_I2 IS NULL THEN
	   LEAVE STR_LABEL;
	 END IF;
	
	 SET V_I1 = V_I2 + 1;
	END LOOP;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_ALL`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_ALL`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	DECLARE V_LockName, V_TradeNO, V_BuyerNick VARCHAR(40);
	
	DECLARE trade_cursor CURSOR FOR SELECT rec_id,tid,buyer_nick FROM api_trade WHERE process_status=10 AND bad_reason=0 AND platform_id>0 LIMIT 100;
	-- DECLARE refund_cursor CURSOR FOR SELECT refund_id FROM api_refund WHERE process_status=0 LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
		RESIGNAL;
	END;
	
	-- 统计递交的订单数
	SET @tmp_delivered_count = 0;
	
	SET V_LockName = CONCAT('deliver_lock_', DATABASE());
	IF NOT IS_FREE_LOCK(V_LockName) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT GET_LOCK(V_LockName, 1) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	SET @cur_uid = P_OperatorID;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	IF @cfg_order_auto_submit THEN
		OPEN trade_cursor;
		TRADE_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				IF V_TradeCount >= 100 THEN
					-- 需要测试，改成1测试
					SET V_TradeCount = 0;
					CLOSE trade_cursor;
					OPEN trade_cursor;
					ITERATE TRADE_LABEL;
				END IF;
				LEAVE TRADE_LABEL;
			END IF;
			
			SET V_TradeCount = V_TradeCount+1;
			
			CALL I_DL_DELIVER_API_TRADE(V_TradeID, P_OperatorID);
			
			IF @sys_code = 0 THEN
				SET @tmp_delivered_count = @tmp_delivered_count+1;
			ELSEIF @sys_code<>2 THEN	-- 是指此订单已经递交过
				INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
			END IF;
		END LOOP;
		close trade_cursor;
	END IF;
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(P_OperatorID);

	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 19);
	SET @tmp_to_preorder_count = @tmp_to_process_count;
	
	-- 待审核订单
	CALL I_DL_DELIVER_SALES_TRADE(P_OperatorID, 20);
	SET @tmp_to_check_count = @tmp_to_process_count;
	*/
	-- 递交退款单
	/*
	SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, P_OperatorID);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
		END IF;
	END LOOP;
	close refund_cursor;
	*/
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
	-- 解锁
	SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_SOME`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_SOME`(IN `P_TradeIDs` VARCHAR(1024))
    SQL SECURITY INVOKER
    COMMENT '根据指定的原始单ID进行递交'
BEGIN
	DECLARE V_TradeNO,V_BuyerNick VARCHAR(40);
	DECLARE V_TradeCount, V_NOT_FOUND INT DEFAULT(0);
	
	DECLARE V_TradeID BIGINT;
	
	DECLARE trade_cursor CURSOR FOR select ax.rec_id,tid,buyer_nick 
		from tmp_xchg tx,api_trade ax 
		where ax.rec_id=tx.f1;
	
	-- DECLARE refund_cursor CURSOR FOR select refund_id from api_refund where process_status=0 LIMIT 500;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
		RESIGNAL;
	END;
		
	CALL SP_INT_ARR_TO_TBL(P_TradeIDs, 1);
	
	-- 清除无效货品标记
	UPDATE tmp_xchg tx,api_trade ax 
	SET bad_reason=(bad_reason&~1)
	WHERE ax.rec_id=tx.f1;
	
	UPDATE tmp_xchg tx,api_trade ax,api_trade_order ato 
	SET ato.is_invalid_goods=0
	WHERE ax.rec_id=tx.f1 AND ato.platform_id=ax.platform_id AND ato.tid=ax.tid;
	
	/*初始化*/
	CALL I_DL_INIT(1);
	
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(100));
	
	-- 递交新订单
	SET V_NOT_FOUND = 0;
	OPEN trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH trade_cursor INTO V_TradeID, V_TradeNO, V_BuyerNick;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			LEAVE TRADE_LABEL;
		END IF;
		
		CALL I_DL_DELIVER_API_TRADE(V_TradeID, @cur_uid);
		
		IF @sys_code = 0 THEN
			SET @tmp_delivered_count = @tmp_delivered_count+1;
		ELSEIF @sys_code<>1 THEN	-- 1是指此订单已经递交过
			INSERT INTO tbl_deliver_error(tid, buyer_nick, error_code, error_info) values(V_TradeNO, V_BuyerNick, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE trade_cursor;
	
	-- 执行修改的订单同步
	CALL I_DL_DELIVER_API_TRADE_CHANGED(@cur_uid);
	
	-- 第三步 前处理 递交到 客审
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,20);

	/* 订单的拆分合并及赠品暂不处理
	-- 第三步
	-- 赠品,仓库，物流, 转入审核
	-- 预订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 19);
	-- 待审核订单
	CALL I_DL_DELIVER_TRADE2(@cur_uid, 20);
	*/
	
	-- 递交退款单
	/*SET V_NOT_FOUND = 0;
	SET V_TradeCount = 0;
	OPEN refund_cursor;
	REFUND_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		FETCH refund_cursor INTO V_TradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 500 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE refund_cursor;
				OPEN refund_cursor;
				ITERATE REFUND_LABEL;
			END IF;
			LEAVE REFUND_LABEL;
		END IF;
		
		SET V_TradeCount = V_TradeCount+1;
		
		START TRANSACTION;
		CALL I_DL_DELIVER_REFUND(V_TradeID, @cur_uid);
		IF @sys_code=0 THEN
			COMMIT;
		ELSE
			ROLLBACK;
			INSERT INTO tbl_deliver_error(tid, error_code, error_info) values(V_RefundID, @sys_code, @sys_message);
		END IF;
	END LOOP;
	CLOSE refund_cursor;*/
	
	-- CALL I_DL_SYNC_REFUND(P_OperatorID);
	
	SELECT * FROM tbl_deliver_error;
	DROP TEMPORARY TABLE IF EXISTS tbl_deliver_error;
	
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_CHAR`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_CHAR`(IN `P_Key` VARCHAR(60), OUT `P_Val` VARCHAR(256), IN `P_Def` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG_INT`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG_INT`(IN `P_Key` VARCHAR(60), OUT `P_Val` INT, IN `P_Def` INT)
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM cfg_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;

