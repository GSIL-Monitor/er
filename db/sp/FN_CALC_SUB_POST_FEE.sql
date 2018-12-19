DROP FUNCTION IF EXISTS `FN_CALC_SUB_POST_FEE`;
DELIMITER //
CREATE FUNCTION `FN_CALC_SUB_POST_FEE`(`P_TruncMode` INT, `P_Weight` DECIMAL(19,4), `P_PrevWeightStep` DECIMAL(19,4), `P_WeightStep` DECIMAL(19,4), `P_UnitStep` DECIMAL(19,4), `P_PriceStep` DECIMAL(19,4)) RETURNS decimal(19,4)
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT '计算一个区间邮费'
BEGIN
	DECLARE V_Postage, V_IncWeight  DECIMAL(19,4) DEFAULT 0;
	
	IF P_UnitStep <= 0 THEN
		RETURN 0;
	END IF;
	
	IF P_WeightStep>0 AND P_Weight > P_WeightStep THEN
		SET V_IncWeight=P_WeightStep-P_PrevWeightStep;
	ELSE
		SET V_IncWeight=P_Weight-P_PrevWeightStep;
	END IF;
	
	
	IF P_TruncMode=2 THEN 
		SET V_Postage = TRUNCATE(FLOOR(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSEIF P_TruncMode=3 THEN 
		SET V_Postage = TRUNCATE(ROUND(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSEIF P_TruncMode=4 THEN 
		SET V_Postage = TRUNCATE((V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	ELSE
		SET V_Postage = TRUNCATE(CEIL(V_IncWeight/P_UnitStep) * P_PriceStep, 4);
	END IF;
	
	RETURN V_Postage;
END//
DELIMITER ;