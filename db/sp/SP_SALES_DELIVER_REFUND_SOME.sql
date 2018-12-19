DROP PROCEDURE IF EXISTS `SP_SALES_DELIVER_REFUND_SOME`;
DELIMITER //
CREATE PROCEDURE `SP_SALES_DELIVER_REFUND_SOME`(IN `P_TradeIDs` VARCHAR(1024))
    SQL SECURITY INVOKER
    COMMENT '根据指定的原始退款单ID进行递交'
MAIN_LABEL:BEGIN
        DECLARE V_RefundCount, V_RefundID, V_NOT_FOUND INT DEFAULT(0);
        DECLARE V_RefundNo VARCHAR(40);
        DECLARE refund_cursor CURSOR FOR SELECT ax.refund_id,ax.refund_no 
                FROM tmp_xchg tx,api_refund ax 
                WHERE ax.refund_id=tx.f1;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
        DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
                ROLLBACK;
                RESIGNAL;
        END;
        CALL SP_INT_ARR_TO_TBL(P_TradeIDs, 1);
        CALL I_DL_INIT(0);
        CREATE TEMPORARY TABLE IF NOT EXISTS tbl_deliver_error(tid VARCHAR(40), buyer_nick VARCHAR(40), error_code INT, error_info VARCHAR(50));
        DELETE FROM tbl_deliver_error;
        -- CALL SP_UTILS_GET_CFG('sales_refund_default_in_warehouse',@cfg_sales_refund_default_in_warehouse,0); 
        IF @cfg_sales_refund_default_in_warehouse THEN
                SELECT `type` INTO @cfg_sales_refund_default_in_warehouse_type FROM sys_warehouse WHERE warehouse_id=@cfg_sales_refund_default_in_warehouse;
                IF V_NOT_FOUND<>0 THEN
                        SET V_NOT_FOUND=0;
                        SET @cfg_sales_refund_default_in_warehouse_type=0;
                END IF;
        END IF;
        OPEN refund_cursor;
        REFUND_LABEL: LOOP
                SET V_NOT_FOUND = 0;
                FETCH refund_cursor INTO V_RefundID,V_RefundNo;
                IF V_NOT_FOUND THEN
                        SET V_NOT_FOUND=0;
                        IF V_RefundCount >= 500 THEN
                                SET V_RefundCount = 0;
                                CLOSE refund_cursor;
                                OPEN refund_cursor;
                                ITERATE REFUND_LABEL;
                        END IF;
                        LEAVE REFUND_LABEL;
                END IF;
                SET V_RefundCount = V_RefundCount+1;
                START TRANSACTION;
                CALL I_DL_DELIVER_REFUND(V_RefundID, @cur_uid);
                IF @sys_code=0 THEN
                        COMMIT;
                ELSE
                        ROLLBACK;
                        INSERT INTO tbl_deliver_error(tid, error_code, error_info) VALUES(V_RefundNo, @sys_code, @sys_message);
                END IF;
        END LOOP;
        CLOSE refund_cursor;
        CALL I_DL_SYNC_REFUND(@cur_uid);
	SELECT tid, error_code, error_info FROM tbl_deliver_error;
	DELETE FROM tbl_deliver_error;
END
//
DELIMITER ;
