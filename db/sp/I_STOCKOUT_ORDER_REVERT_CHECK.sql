DROP PROCEDURE IF EXISTS `I_STOCKOUT_ORDER_REVERT_CHECK`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_ORDER_REVERT_CHECK`(IN `P_StockoutId` INT(11),
	IN `P_ReasonId` INT , 
	IN `P_LogisticsStatus` INT, 
	IN `P_SendibllStatus` INT,
	IN `P_Force` INT)
    SQL SECURITY INVOKER
    COMMENT '驳回审核'
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	CALL I_STOCKOUT_ORDER_REVERT_CHECK2(P_StockoutId, P_ReasonId, P_LogisticsStatus, P_SendibllStatus, P_Force,0);
	IF @sys_code THEN
		ROLLBACK;
	ELSE
		COMMIT;
	END IF;
	
END//
DELIMITER ;
