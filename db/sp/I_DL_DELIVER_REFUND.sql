DROP PROCEDURE IF EXISTS `I_DL_DELIVER_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_REFUND`(IN `P_ApiRefundID` INT, IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_ProcessStatus,V_Status,V_RefundID,V_PlatformID,V_ShopID,V_Exists,V_NOT_FOUND,V_Type,V_CustomerID,V_WarehouseID,V_WarehouseType INT DEFAULT(0);
	DECLARE V_RefundNO,V_ApiRefundNO,V_Tid,V_PayAccount,V_ReceiverName VARCHAR(40) DEFAULT '';
	DECLARE V_TelNO,V_ReceiverArea,V_ReceiverAddress,V_ReturnMobile,V_ReceiverTelno,V_ReturnTelno VARCHAR(255) DEFAULT '';
	DECLARE V_GoodsAmount DECIMAL(19,4);
	DECLARE V_BuyerNick VARCHAR(100) DEFAULT '';
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET @sys_code=0,@sys_message='OK';
	
	SELECT process_status,`status`,platform_id,shop_id,refund_no,tid,pay_account,`type`,buyer_nick 
	INTO V_ProcessStatus,V_Status,V_PlatformID,V_ShopID,V_ApiRefundNO,V_Tid,V_PayAccount,V_Type,V_BuyerNick 
	FROM api_refund WHERE refund_id=P_ApiRefundID FOR UPDATE;
	
	IF V_NOT_FOUND THEN
		SET @sys_code=1,@sys_message='退款单未找到';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_ProcessStatus<>0 THEN
		SET @sys_code=2,@sys_message='退款单状态不正确';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF EXISTS(SELECT 1 FROM api_trade where tid = V_Tid and platform_id = V_PlatformID AND process_status = 70 AND deliver_trade_id = 0)  THEN
		UPDATE api_refund SET process_status=10,modify_flag=0 WHERE refund_id=P_ApiRefundID;
		LEAVE MAIN_LABEL;
	ELSEIF NOT EXISTS(SELECT 1 FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id = sto.trade_id WHERE sto.src_tid = V_Tid AND sto.platform_id = V_PlatformID AND st.trade_status >= 10) THEN
		UPDATE api_refund SET process_status=10,modify_flag=0 WHERE refund_id=P_ApiRefundID;
		LEAVE MAIN_LABEL;
	ELSEIF V_Type = 2 AND NOT EXISTS(SELECT 1 FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id = sto.trade_id WHERE sto.src_tid = V_Tid AND sto.platform_id = V_PlatformID AND st.trade_status >= 95) THEN
		SET @sys_code=3,@sys_message='系统订单未发货,无法创建退货单';
		LEAVE MAIN_LABEL;
	ELSEIF NOT EXISTS(SELECT 1 FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id = sto.trade_id WHERE sto.src_tid = V_Tid AND sto.platform_id = V_PlatformID) THEN
		SET @sys_code=4,@sys_message='系统订单不存在,无法递交到系统中';
		LEAVE MAIN_LABEL;
	ELSEIF EXISTS(SELECT 1 FROM sales_refund_order sro,api_refund_order aro,sales_refund sr WHERE sr.refund_id = sro.refund_id AND sro.platform_id = aro.platform_id AND aro.oid = sro.oid AND sr.type= V_Type AND aro.platform_id = V_PlatformID AND aro.refund_no = V_ApiRefundNO) THEN
		SET @sys_code=5,@sys_message='系统中已存在该货品的同一类型的退换单据';
		UPDATE api_refund SET process_status=10,modify_flag=0 WHERE refund_id=P_ApiRefundID;
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status<=1 THEN -- 1取消退款
		SET V_ProcessStatus=10;
	ELSEIF V_Status=2 THEN -- 申请退款，等待同意
		SET V_ProcessStatus=20;
		-- 查看临时退款单，是否都存在
		SELECT 1 INTO V_Exists FROM api_refund_order aro LEFT JOIN sales_tmp_refund_order stro ON stro.shop_id=V_ShopID AND stro.oid=aro.oid
		WHERE aro.platform_id=V_PlatformID AND aro.refund_no=V_ApiRefundNO AND stro.rec_id IS NULL LIMIT 1;
		IF V_Exists=0 THEN -- 所有子订单都已经同意
			SET V_ProcessStatus=30;
		END IF;
	ELSEIF V_Status=3 THEN	-- 等待退货
		SET V_ProcessStatus=20;
	ELSEIF V_Status=4 THEN -- 等待收货
		SET V_ProcessStatus=20;
	ELSEIF V_Status=5 THEN	--  退款完成
		-- 如果type<>1,需要进行帐款处理!!!???
		SET V_ProcessStatus=90;
		IF V_Type = 1 THEN
			SET V_ProcessStatus = 90;
		END IF;
	END IF;
	
	SELECT customer_id INTO V_CustomerID  FROM crm_platform_customer WHERE platform_id = V_PlatformID AND account = V_BuyerNick;
	IF V_NOT_FOUND THEN
		SET V_NOT_FOUND = 0;
		SET @sys_code=4,@sys_message='客户未记录到系统中,无法递交到系统中';
		LEAVE MAIN_LABEL;
	END IF;
	-- 补充原始货品信息
	-- 淘宝退款单里没有货品详情
	UPDATE api_refund_order aro, api_trade_order ato
	SET aro.num=ato.num,aro.price=TRUNCATE(ato.share_amount/ato.num,4),aro.total_amount=ato.share_amount,
		aro.goods_id=ato.goods_id,aro.spec_id=ato.spec_id,aro.goods_no=ato.goods_no,aro.spec_no=ato.spec_no,
		aro.goods_name=ato.goods_name,aro.spec_name=ato.spec_name,aro.modified=NOW()
	WHERE aro.platform_id=V_PlatformID AND aro.refund_no=V_ApiRefundNO
		AND ato.platform_id=V_PlatformID AND ato.oid=aro.oid;
	
	IF ROW_COUNT()=0 THEN
		UPDATE api_refund SET process_status=10,modify_flag=0 WHERE refund_id=P_ApiRefundID;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 补充原始单的支付帐号
	IF V_PayAccount='' THEN
		SELECT pay_account,CONCAT_WS(',',IF(receiver_mobile='',NULL,receiver_mobile),IF(receiver_telno='',NULL,receiver_telno)),
			receiver_area,receiver_address,receiver_name,receiver_mobile,receiver_telno
			INTO V_PayAccount,V_TelNO,V_ReceiverArea,V_ReceiverAddress,V_ReceiverName,V_ReturnMobile,V_ReceiverTelno
		FROM api_trade WHERE platform_id=V_PlatformID AND tid=V_Tid;
		
		UPDATE api_refund SET pay_account=V_PayAccount WHERE refund_id=P_ApiRefundID;
	ELSE
		SELECT CONCAT_WS(',',IF(receiver_mobile='',NULL,receiver_mobile),IF(receiver_telno='',NULL,receiver_telno)),
			receiver_area,receiver_address,receiver_name
			INTO V_TelNO,V_ReceiverArea,V_ReceiverAddress,V_ReceiverName
		FROM api_trade WHERE platform_id=V_PlatformID AND tid=V_Tid;
	END IF;
	
	-- 统计总货款
	SELECT SUM(IFNULL(ato.share_amount,aro.total_amount)) INTO V_GoodsAmount
	FROM api_refund_order aro LEFT JOIN api_trade_order ato ON (ato.platform_id=V_PlatformID AND ato.oid=aro.oid)
	WHERE aro.platform_id=V_PlatformID AND aro.refund_no=V_ApiRefundNO;
	
	-- 判断仓库信息
	IF @cfg_sales_refund_default_in_warehouse THEN
		SET V_WarehouseID=@cfg_sales_refund_default_in_warehouse;
		SET V_WarehouseType=@cfg_sales_refund_default_in_warehouse_type;
	ELSE
		-- 读取原订单的仓库信息
		SELECT st.warehouse_id,st.warehouse_type INTO V_WarehouseID,V_WarehouseType FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id = sto.trade_id WHERE sto.src_tid = V_Tid AND sto.platform_id = V_PlatformID LIMIT 1;
	END IF;
	-- 生成处理单
	SET V_RefundNO=FN_SYS_NO('refund');
	
	INSERT INTO sales_refund(refund_no,src_no,`type`,process_status,`status`,shop_id,warehouse_id,warehouse_type,pay_account,refund_amount,actual_refund_amount,goods_amount,
		platform_id,tid,customer_id,buyer_nick,receiver_name,receiver_telno,receiver_address,return_mobile,return_telno,refund_time,logistics_name,
		logistics_no,remark,refund_version,operator_id,created,cs_status)
	SELECT V_RefundNO,V_ApiRefundNO,`type`,V_ProcessStatus,V_Status,shop_id,V_WarehouseID,V_WarehouseType,pay_account,refund_amount,actual_refund_amount,IF(TYPE=2 OR TYPE=3,V_GoodsAmount,0),
		platform_id,tid,V_CustomerID,buyer_nick,V_ReceiverName,V_TelNO,CONCAT(V_ReceiverArea,' ',V_ReceiverAddress),V_ReturnMobile,V_ReturnTelno,refund_time,IF(logistics_name='','无',logistics_name),logistics_no,
		reason,refund_version,P_OperatorID,NOW(),if(`type`=1,1,2)
	FROM api_refund WHERE refund_id=P_ApiRefundID;
	
	SET V_RefundID=LAST_INSERT_ID();
	-- 历史订单
	SET @tmp_old_trade = 0;
	
	INSERT INTO sales_refund_order(refund_id,process_status,platform_id,shop_id,oid,tid,trade_id,trade_order_id,trade_no,order_num,price,
				refund_num,total_amount,goods_id,spec_id,spec_no,goods_name,spec_name,suite_id,suite_no,suite_name,
				suite_num,created,original_price,discount,paid,cost_price)
	(SELECT V_RefundID,IF(V_ProcessStatus=90,80,V_ProcessStatus),V_PlatformID,aro.shop_id,aro.oid,sto.src_tid,IFNULL(sto.trade_id,0),IFNULL(sto.rec_id,0),IFNULL(st.trade_no,''),
		IFNULL(sto.num,aro.num),IFNULL(sto.share_price,aro.price),IFNULL(sto.num,aro.num),
		IFNULL(sto.share_amount,aro.total_amount),IFNULL(sto.goods_id,(@tmp_old_trade:=1)-1),IFNULL(sto.spec_id,0),IFNULL(sto.spec_no,''),
		IFNULL(sto.goods_name,aro.goods_name),IFNULL(sto.spec_name,aro.spec_name),IFNULL(sto.suite_id,0),IFNULL(sto.suite_no,''),
		IFNULL(sto.suite_name,''), IFNULL(sto.suite_num,0),NOW(),IFNULL(sto.price,0),IFNULL(sto.discount,0),IFNULL(sto.paid,0),IF(sod.cost_price=0,sto.share_price,sod.cost_price)
	FROM api_refund_order aro 
		LEFT JOIN sales_trade_order sto ON (sto.platform_id=V_PlatformID AND sto.src_oid=aro.oid)
		LEFT JOIN sales_trade st ON (st.trade_id=sto.trade_id)
		LEFT JOIN stockout_order_detail sod ON sod.src_order_type = 1 AND src_order_detail_id = sto.rec_id
	WHERE aro.platform_id=V_PlatformID AND aro.refund_no=V_ApiRefundNO);
	UPDATE sales_refund sr ,sales_refund_order sro 
	SET sr.trade_id = sro.trade_id,sr.trade_no = sro.trade_no 
	WHERE sr.refund_id = sro.refund_id AND sr.refund_id = V_RefundID; 
	
	IF @tmp_old_trade THEN
		-- 修改历史订单
		-- 组合装没法处理了?
		UPDATE sales_refund_order sro
			LEFT JOIN api_trade_order ato ON (ato.platform_id=1 AND ato.oid=sro.oid)
			LEFT JOIN api_trade ax ON (ax.platform_id=1 AND ax.tid=ato.tid)
			LEFT JOIN api_goodsspec ags ON(ags.shop_id=ax.shop_id AND ags.goods_id=ato.goods_id AND ags.spec_id=ato.spec_id)
			LEFT JOIN goods_spec gs ON(gs.spec_id=ags.match_target_id)
			LEFT JOIN goods_goods gg ON(gg.goods_id=gs.goods_id)
		SET sro.spec_id=ags.match_target_id,
			sro.goods_id=gs.goods_id,
			sro.spec_no=gs.spec_no,
			sro.spec_name=gs.spec_name,
			sro.goods_name=gg.goods_name
		WHERE sro.refund_id=V_RefundID AND sro.spec_id=0 AND ags.match_target_type IS NOT NULL AND ags.match_target_type=1 AND gg.goods_id IS NOT NULL;
	END IF;
	
	-- 邮费,总退款金额？
	
	UPDATE api_refund SET process_status=IF(V_ProcessStatus>20,20,V_ProcessStatus),modify_flag=0 WHERE refund_id=P_ApiRefundID;
	
	-- 拦截? 
	
	-- 删除临时退款单
	DELETE stro FROM sales_tmp_refund_order stro,api_refund_order aro
	WHERE aro.platform_id=V_PlatformID AND aro.refund_no=V_ApiRefundNO AND stro.shop_id=V_ShopID AND stro.oid=aro.oid;
	
	-- 日志
	INSERT INTO sales_refund_log(refund_id,`type`,operator_id,remark)
	VALUES(V_RefundID,1,P_OperatorID,'递交退款单');
	
END
//
DELIMITER ;