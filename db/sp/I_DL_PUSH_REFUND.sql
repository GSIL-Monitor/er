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