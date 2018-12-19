DROP PROCEDURE IF EXISTS `I_DL_SYNC_REFUND`;
DELIMITER //
CREATE PROCEDURE `I_DL_SYNC_REFUND`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ApiRefundID, V_RefundID, V_PlatformId, V_Type, V_Status, V_Count, V_ModifyFlag, V_LogType, 
		V_ProcessStatus,V_OldProcessStatus,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_RefundNO,V_LogisticsNO,V_Version VARCHAR(40);
	DECLARE V_LogisticsName, V_Message VARCHAR(100);
	DECLARE V_Amount,V_AcutalAmount DECIMAL(19,4);
	
	DECLARE api_refund_cursor CURSOR FOR 
		SELECT modify_flag,refund_id,platform_id,refund_no,type,status,refund_amount,actual_refund_amount,logistics_name,logistics_no,refund_version
		FROM api_refund WHERE modify_flag>0
		LIMIT 500;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	OPEN api_refund_cursor;
	API_REFUND_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_refund_cursor INTO V_ModifyFlag, V_ApiRefundID, V_PlatformId, V_RefundNO, 
			V_Type, V_Status,V_Amount,V_AcutalAmount,V_LogisticsName,V_LogisticsNO,V_Version;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_Count >= 500 THEN
				-- 需要测试，改成1测试
				SET V_Count = 0;
				CLOSE api_refund_cursor;
				OPEN api_refund_cursor;
				ITERATE API_REFUND_LABEL;
			END IF;
			LEAVE API_REFUND_LABEL;
		END IF;
		
		SET V_Count=V_Count+1;
		
		START TRANSACTION;
		
		SELECT refund_id, process_status INTO V_RefundID,V_OldProcessStatus FROM sales_refund 
		WHERE src_no=V_RefundNO AND platform_id=V_PlatformId AND type=V_Type AND from_type=1 LIMIT 1;
		
		IF V_NOT_FOUND THEN
			UPDATE api_refund SET modify_flag=0 WHERE refund_id=V_ApiRefundID;
			COMMIT;
			ITERATE API_REFUND_LABEL;
		END IF;
		
		IF V_ModifyFlag & 2 THEN -- amount
			
			UPDATE sales_refund SET refund_amount=V_Amount,actual_refund_amount=V_AcutalAmount WHERE refund_id=V_RefundID;
			
			INSERT INTO sales_refund_log(refund_id,type,operator_id,remark)
			VALUES(V_RefundID,9,P_OperatorID,'退款金额变化');
		END IF;
		
		IF V_ModifyFlag & 4 THEN -- amount
			UPDATE sales_refund SET refund_version=V_Version WHERE refund_id=V_RefundID;
		END IF;
		
		IF V_ModifyFlag & 8 THEN -- logisitcs
			UPDATE sales_refund SET logistics_name=V_LogisticsName,logistics_no=V_LogisticsNO
			WHERE refund_id=V_RefundID;
			
			INSERT INTO sales_refund_log(refund_id,type,operator_id,remark)
			VALUES(V_RefundID,10,P_OperatorID,'更新物流');
		END IF;
		
		IF V_ModifyFlag & 1 THEN -- status
			SET V_ProcessStatus=-1;
			SET V_LogType = 0, V_Message='平台状态变化';
			IF V_Status=1 THEN -- 1取消退款
				SET V_ProcessStatus=10;
				SET V_LogType = 6, V_Message='平台取消退款';
			ELSEIF V_Status=3 THEN	-- 等待退货
				SET V_ProcessStatus=60;
				SET V_LogType = 8, V_Message='等待退货';
			ELSEIF V_Status=4 THEN -- 等待收货
				SET V_ProcessStatus=60;
				SET V_LogType = 11, V_Message='等待收货';
			ELSEIF V_Status=5 THEN	--  退款完成
				SET V_ProcessStatus=80;
				IF V_Type = 1 THEN
					SET V_ProcessStatus=90;
				END IF;
				SET V_LogType = 12, V_Message='退款完成';
			END IF;
			
			IF V_ProcessStatus>0 THEN
				UPDATE sales_refund SET process_status=V_ProcessStatus, status=V_Status WHERE refund_id=V_RefundID;
				
				INSERT INTO sales_refund_log(refund_id,type,operator_id,remark)
				VALUES(V_RefundID,V_LogType,P_OperatorID,V_Message);
			ELSE
				UPDATE sales_refund SET status=V_Status WHERE refund_id=V_RefundID;
			END IF;
		END IF;
		
		-- 删除临时退款单?
		
		UPDATE api_refund SET modify_flag=0 WHERE refund_id=V_ApiRefundID;
		COMMIT;
	END LOOP;
	CLOSE api_refund_cursor;
END
//
DELIMITER ;