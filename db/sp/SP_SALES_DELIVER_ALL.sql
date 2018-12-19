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
					-- SET V_TradeCount = 0;
					LEAVE TRADE_LABEL;
					CLOSE trade_cursor;
					-- OPEN trade_cursor;
					-- ITERATE TRADE_LABEL;
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

	-- 预订单
	CALL I_DL_DELIVER_SALES_TRADE(@cur_uid, 19);
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