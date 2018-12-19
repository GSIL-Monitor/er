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