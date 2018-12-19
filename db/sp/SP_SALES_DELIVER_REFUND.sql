DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_REFUND`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_REFUND`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
MAIN_LABEL:BEGIN
	DECLARE V_RefundCount, V_RefundID, V_NOT_FOUND,V_LoopCount INT DEFAULT(0);
	DECLARE V_RefundNo,V_LockName VARCHAR(40);
	
	DECLARE refund_cursor CURSOR FOR select refund_id,refund_no from api_refund where process_status=0 AND type = 1 LIMIT 500;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
		RESIGNAL;
	END;
	SET V_LockName = CONCAT('deliver_refund_lock_', DATABASE());
	IF NOT IS_FREE_LOCK(V_LockName) THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT GET_LOCK(V_LockName, 1) THEN
		LEAVE MAIN_LABEL;
	END IF;
	/*初始化*/
	-- 是否开启自动递交退款单
	CALL SP_UTILS_GET_CFG_INT('api_refund_auto_submit',@cfg_api_refund_auto_submit,0);
	-- 错误结果临时表
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(50));
	DELETE FROM tbl_deliver_error;
	
	-- 递交退款单
	IF @cfg_api_refund_auto_submit THEN
		OPEN refund_cursor;
		SET V_LoopCount = 0;
		REFUND_LABEL: LOOP
			SET V_NOT_FOUND = 0;
			FETCH refund_cursor INTO V_RefundID,V_RefundNo;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				SET V_LoopCount = V_LoopCount+1;
				IF V_RefundCount >= 500  AND V_LoopCount<@sys_deliver_loop_count THEN
					-- 需要测试，改成1测试
					SET V_RefundCount = 0;
					CLOSE refund_cursor;
					OPEN refund_cursor;
					ITERATE REFUND_LABEL;
				END IF;
				LEAVE REFUND_LABEL;
			END IF;
			
			SET V_RefundCount = V_RefundCount+1;
			
			START TRANSACTION;
			CALL I_DL_DELIVER_REFUND(V_RefundID, P_OperatorID);
			IF @sys_code=0 THEN
				COMMIT;
			ELSE
				ROLLBACK;
				INSERT INTO tbl_deliver_error(tid, error_code, error_info) values(V_RefundNo, @sys_code, @sys_message);
			END IF;
		END LOOP;
		CLOSE refund_cursor;
	END IF;
	CALL I_DL_SYNC_REFUND(P_OperatorID);
	
	SELECT tid, error_code, error_info FROM tbl_deliver_error;
	DELETE FROM tbl_deliver_error;
	-- 解锁
	SELECT RELEASE_LOCK(V_LockName) INTO @tmp_dummy;
END
//
DELIMITER ;