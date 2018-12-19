DROP FUNCTION IF EXISTS `FN_SYS_NO`;
DELIMITER //
CREATE FUNCTION `FN_SYS_NO`(`P_Key` VARCHAR(50)) RETURNS VARCHAR(60) CHARSET UTF8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_DateChanged BIT;
	DECLARE V_Prefix, V_PostfixStr, V_PostfixStr2 VARCHAR(64);
	DECLARE V_PostfixLen, V_PostfixVal,V_DateForm INT;
	DECLARE V_NowDate DATE;
	
	-- date_form 0 全日期 1短日期 2无日期
	
	SET V_NowDate = CURDATE();
	
	UPDATE sys_no_cfg SET postfix_val=LAST_INSERT_ID(IF(V_NowDate=last_date OR date_form=2,postfix_val+1,1)),last_date=V_NowDate
	WHERE `key`=P_Key;
	
	SET V_PostfixVal = LAST_INSERT_ID();
	
	SELECT prefix,postfix_len,date_form into V_Prefix,V_PostfixLen,V_DateForm from sys_no_cfg WHERE `key`=P_Key;
	
	SET V_PostfixStr = CAST(V_PostfixVal AS CHAR);
	SET V_PostfixStr2 = LPAD(V_PostfixStr, V_PostfixLen, '0');
	IF LENGTH(V_PostfixStr2) < LENGTH(V_PostfixStr) THEN
		SET V_PostfixStr2 = V_PostfixStr;
	END IF;
	
	IF V_DateForm=0 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%Y%m%d'), V_PostfixStr2);
	ELSEIF V_DateForm=1 THEN
		RETURN CONCAT(V_Prefix, DATE_FORMAT(V_NowDate,'%y%m%d'), V_PostfixStr2);
	END IF;
	
	RETURN CONCAT(V_Prefix, V_PostfixStr2);
END//
DELIMITER ;