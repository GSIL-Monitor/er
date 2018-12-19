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
	
	-- 清除未匹配货品标记
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
	
	-- 递交到 预订单
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid,19);
	
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