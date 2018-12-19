
DROP PROCEDURE IF EXISTS `I_VR_SALES_TRADE_CONSIGN`;
DELIMITER //
CREATE PROCEDURE `I_VR_SALES_TRADE_CONSIGN`(IN P_StockoutID INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
			
	DECLARE V_NOT_FOUND, V_TradeID, V_ShopID, V_WarehouseID, V_VoucherID, V_CustomerID, V_SpecID, V_LogisticsID ,V_TradeFrom, V_DeliveryTerm, V_PlatformID,V_AccountType,V_MakeOperID INT DEFAULT(0);
	DECLARE V_StockoutNO, V_VoucherNO, V_VoucherPeriod, V_BuyerNick, V_LogisticsName, V_SrcOid, V_TradeNo,V_SpecNO , V_LogisticsNO,V_PreChargeTime  VARCHAR(40);
	DECLARE V_Title,V_SrcTid VARCHAR(255);
	DECLARE V_Receivable, V_GoodsTotalCost, V_PostAmount, V_GoodsAmount, V_TotalAmount, V_CostPriceAmount, V_PostCost, V_SharePost DECIMAL(19,4) DEFAULT(0);
	DECLARE V_Now DATETIME;
	DECLARE V_NickName VARCHAR(100);
	DECLARE V_AccSalesStockoutGoodsYis, -- 销售订单货品应收(应收账款-销售订单货品应收)
		V_AccOfflinesaleYus, -- 线下订单预收
		V_AccSalesStockoutPostYis, -- 销售订单邮资应收(应收账款-销售订单邮资应收)
		V_AccSalesStockoutIncome,	-- 订单销售收入(主营业务收入-销售订单收入)
		V_AccSalesStockoutGoodsCost,	-- 订单货品货品成本(主营业务成本-销售订单货品成本)
		V_AccMerchandiseInventory,	-- 库存商品
		V_AccSalesEstimatePostCost, -- 订单预估邮资费用(销售费用-预估邮资)
		V_AccSalesEstimatePostYuti -- 订单预估邮资预提(预提费用-邮资费用-顺丰)
		INT DEFAULT(0);
	
	DECLARE detail_cursor CURSOR FOR
		SELECT spec_id,  total_amount,cost_price*num AS cost_price_amount
		FROM stockout_order_detail  
		WHERE stockout_id=P_StockoutID ;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
   		
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		SET @sys_code=99;
		SET @sys_message='记录凭证失败';
		RESIGNAL;
	END;
	
	SET @sys_code=0;
	SET @sys_message='ok';

	
	-- 校验相关账户是否存在	
/*	SET V_AccSalesStockoutGoodsCost = FN_DEFAULT_ACCOUNT("sales_stockout_goods_cost", V_ShopID);
	SET V_AccMerchandiseInventory = FN_DEFAULT_ACCOUNT("merchandise_inventory", 0);
	IF V_AccSalesStockoutGoodsCost=0 THEN
		SET @sys_code=4;
		SET @sys_message='没有给订单出库货品成本设置默认科目';
		LEAVE MAIN_LABEL;
	END IF;
	IF V_AccMerchandiseInventory=0 THEN
		SET @sys_code=5;
		SET @sys_message='没有给库存商品设置默认科目';
		LEAVE MAIN_LABEL;
	END IF;
	*/ 
	-- 从销售出库单中获得 出库单号,仓库,出库货品总成本,物流公司,物流成本
	SELECT src_order_id, stockout_no, warehouse_id, goods_total_cost, logistics_id, post_cost 
		INTO V_TradeID, V_StockoutNO, V_WarehouseID, V_GoodsTotalCost, V_LogisticsID, V_PostCost
	FROM stockout_order WHERE stockout_id=P_StockoutID;

	-- 从销售订单中获得 店铺,邮资,应收总金额, 客户
	SELECT shop_id, receivable, post_amount, customer_id, buyer_nick,trade_no,src_tids,platform_id,warehouse_id,logistics_no,to_deliver_time,salesman_id,delivery_term
		INTO V_ShopID, V_Receivable, V_PostAmount, V_CustomerID, V_BuyerNick,V_TradeNO,V_SrcTid,V_PlatformID,V_WarehouseID,V_LogisticsNO,V_PreChargeTime,V_MakeOperID,V_DeliveryTerm
	FROM sales_trade WHERE trade_id=V_TradeID;

	SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id=V_LogisticsID;
	
	SET V_GoodsAmount = V_Receivable - V_PostAmount;
	
	-- 记录凭证
	/*SET V_Now = NOW();
	SET V_VoucherNO = FN_SYS_NO("voucher");
	SELECT DATE_FORMAT(NOW(), "%Y%m") INTO V_VoucherPeriod;
	
	INSERT INTO fa_voucher(voucher_no, period_id, voucher_date, business_date, title, `type`, `status`, make_oper_id, check_oper_id, check_time,
		src_order_type, src_order_subtype, src_order_id, src_order_no, created)
	VALUES(V_VoucherNO, V_VoucherPeriod, V_Now, V_Now, "订单发货自动生成成本凭证", 0, 1, @cur_uid, @cur_uid, V_Now, 
		1, 1101, P_StockoutID, V_StockoutNO, V_Now);
	SELECT LAST_INSERT_ID() INTO V_VoucherID;*/

	-- 插入凭证明细

	/*
		例: 出库 成本价800 售价1000 的商品, 收用户邮资10 记录
		借：应收账款-销售订单货品应收 1000
		借: 应收账款-销售订单邮资应收 10
		   贷: 主营业务收入-销售订单收入 1010
		同时结转成本
		借: 主营业务成本-销售订单货品成本 800
		   贷：库存商品   800
	*/
	
	/*
		分为线上和线下：
		线上邮费应收单(总有费-线下邮费)
		线上货品应收单（每个单子的share_amount）
		线下订单的应收单
		邮资的应付单

	 */
	
	/*OPEN detail_cursor;
	DETAIL_LABEL:LOOP
		FETCH detail_cursor INTO V_SpecID, V_TotalAmount, V_CostPriceAmount;
		IF V_NOT_FOUND<>0 THEN
			SET V_NOT_FOUND=0;
			LEAVE DETAIL_LABEL;
		END IF;
		-- 借: 主营业务成本-销售订单货品成本 800
		INSERT INTO fa_voucher_detail(voucher_id, account_id, period_id, dr, cr, title, 
			obj_type, obj_id, obj_name, spec_id, shop_id, warehouse_id, created)
		VALUES(V_VoucherID, V_AccSalesStockoutGoodsCost, V_VoucherPeriod, V_CostPriceAmount, 0, "系统自动生成:订单货品成本", 
			3, V_CustomerID, V_BuyerNick, V_SpecID, V_ShopID, V_WarehouseID, V_Now);
		
	END LOOP;
	CLOSE detail_cursor;
	
	-- 贷：库存商品	800
	INSERT INTO fa_voucher_detail(voucher_id, account_id, period_id, dr, cr, title, 
		obj_type, obj_id, obj_name, spec_id, shop_id, warehouse_id, created)
	VALUES(V_VoucherID, V_AccMerchandiseInventory, V_VoucherPeriod, 0, V_GoodsTotalCost, "系统自动生成:库存商品", 
		3, V_CustomerID, V_BuyerNick, 0, V_ShopID, V_WarehouseID, V_Now);
	*/
	--  依据系统订单获取应收应付期望结算类型，原始单和系统单订单类型可能不一致，可更改，以系统发货的系统单为准
	IF V_PlatformID=0 AND V_DeliveryTerm=2 THEN 
		SET V_AccountType=3;
		CALL SP_UTILS_GET_CFG('fa_cod_pay_time', @fa_cod_pay_time, 1);
		SET V_PreChargeTime = ADDDATE(NOW(),@fa_cod_pay_time);
	ELSEIF V_PlatformID=0 AND V_DeliveryTerm=4 THEN
		SET V_AccountType=2;
	ELSE 
		SET V_AccountType=1;
	END IF;

	-- 线上的货到付款
	-- 线上线下应收货款放在一个tradeno中
/*	IF V_GoodsAmount >0 THEN
		INSERT INTO fa_debt_contacts(contacts_no,contacts_status,contacts_type,order_no,order_sub_no,
			order_type,order_subtype,platform_id,obj_type,obj_id,obj_name,amount,last_amount,shop_id,warehouse_id,salesman_id,created,account_type,pre_charge_time)VALUES
		(FN_SYS_NO('dept_contacts'),0,1,V_TradeNO,V_StockoutNO,1,10,V_PlatformID,3,V_CustomerID,V_BuyerNick, V_GoodsAmount,
			V_GoodsAmount,V_ShopID,V_WarehouseID,V_MakeOperID,NOW(),V_AccountType,V_PreChargeTime);
	 
	END IF;
	-- 线上订单邮资应收
	IF V_PostAmount  > 0 THEN
		INSERT INTO fa_debt_contacts(contacts_no,contacts_status,contacts_type,order_no,order_sub_no,
			order_type,order_subtype,platform_id,obj_type,obj_id,obj_name,amount,last_amount,shop_id,warehouse_id,salesman_id,created,account_type,pre_charge_time)VALUES
		(FN_SYS_NO('dept_contacts'),0,1,V_TradeNO,V_StockoutNO,1,11,V_PlatformID,3,V_CustomerID,V_BuyerNick,V_PostAmount,
			V_PostAmount,V_ShopID,V_WarehouseID,V_MakeOperID,NOW(),V_AccountType,V_PreChargeTime);
	 
	END IF;
	-- 审核生成的应收应付单
	IF V_GoodsAmount + V_PostAmount > 0 THEN
		CALL I_FA_CONTACTS_BUSINESS_CHECK(V_TradeNO,V_StockoutNO,1);
	END IF;
*/
	-- 核销
	IF V_PlatformID = 0 AND V_AccountType = 1 THEN
		CALL I_FA_AUTO_VERIFY(V_TradeNO,V_StockoutNO,1,10);
		IF @sys_code!= 0 THEN
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	-- 更新凭据的往来方向
	/*UPDATE fa_voucher_detail fvd, fa_account fa SET fvd.direction=fa.direction 
		WHERE fvd.account_id=fa.rec_id AND fvd.voucher_id=V_VoucherID;
	
	-- 自动过账
	CALL SP_UTILS_GET_CFG('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	IF @cfg_fa_sales_auto_post THEN
		-- 强制凭证不需要审核
		SET @cfg_fa_voucher_must_check=0;
		-- CALL I_FA_VOUCHER_POST_ONE(V_VoucherID, V_VoucherNO, V_Title);
		INSERT INTO sys_process_background(`type`,object_id) VALUES(3,V_VoucherID);
	END IF;*/
END//
DELIMITER ;