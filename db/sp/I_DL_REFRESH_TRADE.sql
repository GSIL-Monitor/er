DROP PROCEDURE IF EXISTS `I_DL_REFRESH_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_REFRESH_TRADE`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_RefreshFlag` INT, IN `P_ToStatus` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_WarehouseID,V_WarehouseType, V_ShopID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,
		V_LogisticsID,V_DeliveryTerm,V_Max,V_Min,V_NewRefundStatus,V_NewLogisticsID,V_Locked,V_GoodsTypeCount,
		V_NoteCount,V_GiftMask,V_PackageID,V_SalesmanId,V_PlatformId,V_RemarkFlag,V_FlagId,V_BuyerMessageCount,
		V_CsRemarkCount,V_InvoiceType,V_TradeStatus,V_RawGoodsTypeCount, V_RawGoodsCount,V_CustomerID INT DEFAULT(0);
	DECLARE V_Addr,V_SrcTids,V_InvoiceTitle,V_InvoiceContent,V_PayAccount VARCHAR(255);
	DECLARE V_BuyerMessage,V_CsRemark VARCHAR(1024);
	DECLARE V_AreaAlias,V_SingleSpecNO VARCHAR(40);
	DECLARE V_GoodsCount,V_Weight,V_PostCost,V_Paid,V_GoodsAmount,V_PostAmount,V_Discount,
		V_DapAmount,V_CodAmount,V_GoodsCost,V_Commission,V_PackageWeight,V_TotalVolume, 
		V_Receivable,V_Profit DECIMAL(19,4);
	DECLARE V_LogisticsName,V_LogisticsMatchLog,V_LogisticsFeeLog,V_AreaAliasLog VARCHAR(256);
	DECLARE V_SuiteWeight,V_SpecWeight  DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_SuiteNo,V_Srcoid VARCHAR(256);
	DECLARE V_SuiteNum,V_SuiteSpecNum,V_SpecNum,V_SuiteNewNum,V_SpecNum1,V_SpecNum2,V_NOT_FOUND,V_IsScale DECIMAL(19,4) DEFAULT(0);
	DECLARE suite_cousor CURSOR FOR SELECT src_oid, suite_no,suite_num,count(spec_no)  
		FROM  sales_trade_order sto WHERE sto.trade_id=P_TradeID AND sto.suite_no<>'' GROUP BY sto.src_oid;
	DECLARE tmp_spec_num CURSOR FOR SELECT  num,order_num   
		FROM  tmp_suite_spec tsn ;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	-- P_RefreshFlag
	-- 1选择物流 2计算大头笔 4选择包装 8刷新备注 16不刷新该客户订单量
	
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
	-- 刷新组合装数量
	OPEN suite_cousor;
	SUITE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH suite_cousor INTO V_Srcoid,V_SuiteNo,V_SuiteNum,V_SuiteSpecNum ;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE SUITE_LABEL;
		END IF;
		
		SELECT count(*) INTO V_SpecNum FROM goods_suite gs LEFT JOIN goods_suite_detail gsd ON gs.suite_id=gsd.suite_id WHERE suite_no=V_SuiteNo;
		IF V_SpecNum<>V_SuiteSpecNum THEN
			UPDATE sales_trade_order SET suite_num=0 WHERE trade_id=P_TradeID AND suite_no=V_SuiteNo AND src_oid=V_Srcoid;
			LEAVE SUITE_LABEL;
		END IF;
		DELETE FROM tmp_suite_spec;
		INSERT INTO tmp_suite_spec (spec_id,num) 
			SELECT spec_id,num FROM goods_suite_detail gsd LEFT JOIN goods_suite gs ON gs.suite_id=gsd.suite_id
			WHERE gs.suite_no=V_SuiteNo;
		UPDATE tmp_suite_spec tss,sales_trade_order sto SET tss.order_num=sto.actual_num 
		WHERE sto.trade_id=P_TradeID AND sto.suite_no=V_SuiteNo AND sto.spec_id=tss.spec_id AND sto.src_oid=V_Srcoid;
		SET V_SuiteNewNum=0;
		SET V_IsScale=1;
		OPEN tmp_spec_num;
		TMP_SPEC: LOOP
			FETCH tmp_spec_num INTO V_SpecNum1,V_SpecNum2; 
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
				LEAVE TMP_SPEC;
			END IF;
			IF V_SpecNum2%V_SpecNum1<>0 THEN
				SET V_IsScale=0;
				LEAVE TMP_SPEC;
			END IF;
			IF V_SuiteNewNum=0 THEN
				SET V_SuiteNewNum=V_SpecNum2/V_SpecNum1;
			END IF;
			IF V_SuiteNewNum<>V_SpecNum2/V_SpecNum1 THEN 
				SET V_IsScale=0;
				LEAVE TMP_SPEC;
			END IF;
		END LOOP;
		CLOSE tmp_spec_num;
		IF V_IsScale=1 THEN
			UPDATE sales_trade_order SET suite_num=V_SuiteNewNum WHERE trade_id=P_TradeID AND suite_no=V_SuiteNo AND src_oid=V_Srcoid;
		ELSE
			UPDATE sales_trade_order SET suite_num=0 WHERE trade_id=P_TradeID AND suite_no=V_SuiteNo AND src_oid=V_Srcoid;
		END IF;
	END LOOP;
	CLOSE suite_cousor;
	
	-- 根据配置确定订单重量是否按照组合装重量计算
	IF @cfg_order_cal_weight_by_suite IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('order_cal_weight_by_suite',@cfg_order_cal_weight_by_suite,0);
	END IF;
	IF(@cfg_order_cal_weight_by_suite) THEN 
		SELECT SUM(t.suite_weight) INTO V_SuiteWeight 
		FROM (SELECT gs.weight*sto.suite_num suite_weight FROM sales_trade_order sto 
			LEFT JOIN goods_suite gs ON gs.suite_no=sto.suite_no 
			WHERE sto.suite_no<>'' AND sto.trade_id=P_TradeID GROUP BY sto.suite_no,sto.src_oid) t;
		IF V_SuiteWeight>0 THEN 
			SELECT IFNULL(SUM(weight),0) INTO V_SpecWeight FROM sales_trade_order WHERE (suite_no='' OR (suite_no<>'' AND suite_num=0)) AND trade_id=P_TradeID;
			SET V_Weight=V_SuiteWeight+V_SpecWeight;
		END IF;
	END IF;
	
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
	  IF @cfg_single_spec_no_code=1 THEN
      SELECT IF(suite_id,suite_name, concat(spec_no,'-',spec_name)) INTO V_SingleSpecNO
		FROM sales_trade_order
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1;
	  ELSE
      SELECT IF(suite_id,suite_name, concat(goods_name,'-',spec_name)) INTO V_SingleSpecNO
		FROM sales_trade_order
		WHERE trade_id=P_TradeID AND actual_num>0 AND IF(@cfg_sales_raw_count_exclude_gift,gift_type=0,1) LIMIT 1;
    END IF;
	ELSEIF V_RawGoodsTypeCount>1 THEN
		SET V_SingleSpecNO='多种货品';
	ELSE
		SET V_SingleSpecNO='';
	END IF;
	
	-- V_WmsType, V_WarehouseNO, V_ShopID, V_TradeID, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict;
	SELECT trade_status,warehouse_type, warehouse_id,shop_id,logistics_id,post_cost,receiver_province,receiver_city,receiver_district,receiver_address,receiver_dtb,package_id,customer_id,receivable,profit
	INTO V_TradeStatus,V_WarehouseType, V_WarehouseID,V_ShopID,V_LogisticsID,V_PostCost,V_ReceiverProvince,V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_AreaAlias,V_PackageID,V_CustomerID,V_Receivable,V_Profit
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
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
		END IF;*/
		-- 选择物流
		IF P_RefreshFlag & 1 THEN
			CALL I_DL_DECIDE_LOGISTICS(V_NewLogisticsID, -1, V_DeliveryTerm, V_ShopID, V_WarehouseID,V_Weight, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict, V_Addr,V_Paid,V_LogisticsMatchLog);
			IF V_LogisticsID<>V_NewLogisticsID AND V_NewLogisticsID>0 THEN
				SET V_LogisticsID=V_NewLogisticsID;
				-- 大头笔
				CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
				SET P_RefreshFlag=(P_RefreshFlag & (~2));
			END IF;
		END IF;
		-- 估算邮费
		CALL I_DL_DECIDE_LOGISTICS_FEE(V_PostCost, V_Weight, V_LogisticsID, V_ShopID, V_WarehouseID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_LogisticsFeeLog);
		IF P_RefreshFlag & 2 THEN
			CALL I_DL_DECIDE_AREA_ALIAS(V_AreaAlias, V_LogisticsID, 0, V_ReceiverProvince, V_ReceiverCity, V_ReceiverDistrict,V_AreaAliasLog);
		END IF;
		
		
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
	-- 更新该客户的订单金额、利润
	IF (P_RefreshFlag & 16=0) THEN 
		IF ((V_Receivable<>V_GoodsAmount+V_PostAmount-V_Discount)||(V_Profit<>V_GoodsAmount+V_PostAmount-V_Discount-V_GoodsCost-V_PostCost-V_Commission)) THEN
			UPDATE crm_customer SET 
				trade_amount=trade_amount+V_GoodsAmount+V_PostAmount-V_Discount-V_Receivable,
				profit=profit+V_GoodsAmount+V_PostAmount-V_Discount-V_GoodsCost-V_PostCost-V_Commission-V_Profit
			WHERE customer_id=V_CustomerID;
		END IF;
	END IF;
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
		remark_flag=V_RemarkFlag,
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