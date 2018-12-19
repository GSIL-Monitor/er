
-- 导出  过程 eshop_v2.SP_UTILS_GET_CFG 结构
DROP PROCEDURE IF EXISTS `SP_UTILS_GET_CFG`;
DELIMITER //
CREATE PROCEDURE `SP_UTILS_GET_CFG`(IN `P_Key` VARCHAR(60), OUT `P_Val` INT, IN `P_Def` INT)
    SQL SECURITY INVOKER
    COMMENT '读配置'
BEGIN
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET P_Val = P_Def;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION SET P_Val = P_Def;
	
	SELECT `value` INTO P_Val FROM sys_setting WHERE `key`=P_Key LOCK IN SHARE MODE;
	IF P_Val IS NULL THEN
		SET P_Val = P_Def;
	END IF;
END//
DELIMITER ;
