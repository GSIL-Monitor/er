DROP PROCEDURE IF EXISTS `I_DL_DECIDE_LOGISTICS_FEE`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_LOGISTICS_FEE`(OUT `P_PostFee` DECIMAL(19,4), IN `P_Weight` DECIMAL(19,4), IN `P_LogisticsID` INT, IN `P_ShopID` INT, IN `P_WarehouseID` INT, IN `P_ReceiverCountry` INT, IN `P_ReceiverProvince` INT, IN `P_ReceiverCity` INT, IN `P_ReceiverDistrict` INT,OUT `P_LogisticsFeeLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '计算邮费'
MAIN_LABEL: BEGIN
	DECLARE V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4 VARCHAR(40);
	DECLARE V_FirstWeight, V_FristPrice, V_WeightStep1, V_UnitStep1, V_PriceStep1, V_WeightStep2, V_UnitStep2, V_PriceStep2,
		V_WeightStep3, V_UnitStep3, V_PriceStep3, V_WeightStep4, V_UnitStep4, V_PriceStep4,
		V_SpecialWeight,V_SpecialWeight2,V_SpecialWeight3,V_SpecialFee,V_SpecialFee2,V_SpecialFee3 DECIMAL(19,4) DEFAULT(0);
	DECLARE V_TruncMode INT;
	
	SET P_PostFee = 0;
	IF P_LogisticsID=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	-- SET @cfg_logistics_match_mode=0;
	-- SET V_Tmp1 = CONCAT(CASE @cfg_logistics_match_mode WHEN 0 THEN 0 WHEN 1 THEN P_WarehouseID WHEN 2 THEN P_ShopID END,'#');
	SET V_Tmp1 = CONCAT('0#');
	SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
	SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
	SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
	
	SET V_Tmp1 = CONCAT(V_Tmp1, ',0,#', P_LogisticsID);
	SET V_Tmp2 = CONCAT(V_Tmp2, ',#', P_LogisticsID);
	SET V_Tmp3 = CONCAT(V_Tmp3, ',#', P_LogisticsID);
	SET V_Tmp4 = CONCAT(V_Tmp4, ',#', P_LogisticsID);
	
	SELECT first_weight,first_price,weight_step1,unit_step1,price_step1,
		 special_weight1,special_weight2,special_weight3,special_fee1,special_fee2,special_fee3,
		weight_step2,unit_step2,price_step2,weight_step3,unit_step3,price_step3,weight_step4,unit_step4,price_step4,trunc_mode 
	INTO V_FirstWeight, V_FristPrice, V_WeightStep1, V_UnitStep1, V_PriceStep1,
		 V_SpecialWeight,V_SpecialWeight2,V_SpecialWeight3,V_SpecialFee,V_SpecialFee2,V_SpecialFee3,
		V_WeightStep2, V_UnitStep2, V_PriceStep2,V_WeightStep3, V_UnitStep3, V_PriceStep3, V_WeightStep4, V_UnitStep4, V_PriceStep4,V_TruncMode
	FROM cfg_logistics_fee FORCE INDEX(UK_cfg_logistics_fee_path) 
	WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	
	-- 检查是否有按照此店铺设置的策略，如果有，优先执行该策略
	-- SET @cfg_logistics_match_mode=2;
	-- SET V_Tmp1 = CONCAT(CASE @cfg_logistics_match_mode WHEN 0 THEN 0 WHEN 1 THEN P_WarehouseID WHEN 2 THEN P_ShopID END,'#');
	SET V_Tmp1 = CONCAT(P_ShopID,'#');
	SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
	SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
	SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
	
	SET V_Tmp1 = CONCAT(V_Tmp1, ',0,#', P_LogisticsID);
	SET V_Tmp2 = CONCAT(V_Tmp2, ',#', P_LogisticsID);
	SET V_Tmp3 = CONCAT(V_Tmp3, ',#', P_LogisticsID);
	SET V_Tmp4 = CONCAT(V_Tmp4, ',#', P_LogisticsID);
	
	SELECT first_weight,first_price,weight_step1,unit_step1,price_step1,
		 special_weight1,special_weight2,special_weight3,special_fee1,special_fee2,special_fee3,
		weight_step2,unit_step2,price_step2,weight_step3,unit_step3,price_step3,weight_step4,unit_step4,price_step4,trunc_mode 
	INTO V_FirstWeight, V_FristPrice, V_WeightStep1, V_UnitStep1, V_PriceStep1,
		 V_SpecialWeight,V_SpecialWeight2,V_SpecialWeight3,V_SpecialFee,V_SpecialFee2,V_SpecialFee3,
		V_WeightStep2, V_UnitStep2, V_PriceStep2,V_WeightStep3, V_UnitStep3, V_PriceStep3, V_WeightStep4, V_UnitStep4, V_PriceStep4,V_TruncMode
	FROM cfg_logistics_fee FORCE INDEX(UK_cfg_logistics_fee_path) 
	WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	
	
	IF V_FirstWeight <=0 OR V_FristPrice <=0 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_SpecialWeight>0.0 AND  V_SpecialFee>0.0 AND P_Weight <=V_SpecialWeight  THEN
		SET P_LogisticsFeeLog='特殊区间1邮资';
		SET P_PostFee = V_SpecialFee;
		LEAVE MAIN_LABEL;
	ELSEIF V_SpecialWeight2>0.0 AND  V_SpecialFee2>0.0 AND P_Weight <=V_SpecialWeight2 THEN
		SET P_LogisticsFeeLog='特殊区间2邮资';
		SET P_PostFee = V_SpecialFee2;
		LEAVE MAIN_LABEL;		
	ELSEIF V_SpecialWeight3>0.0 AND  V_SpecialFee3>0.0 AND P_Weight <=V_SpecialWeight3 THEN
		SET P_LogisticsFeeLog='特殊区间3邮资';
		SET P_PostFee = V_SpecialFee3;
		LEAVE MAIN_LABEL;		
	END IF;
	
	SET P_PostFee = V_FristPrice;
	IF P_Weight <= V_FirstWeight OR V_FirstWeight<=0 THEN
		SET P_LogisticsFeeLog='首重资费';
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_FirstWeight, V_WeightStep1, V_UnitStep1, V_PriceStep1);
	
	IF P_Weight <= V_WeightStep1 OR V_WeightStep1<=0 THEN
		SET P_LogisticsFeeLog='首重资费加重量区间1计算资费';
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep1, V_WeightStep2, V_UnitStep2, V_PriceStep2);
	
	IF P_Weight <= V_WeightStep2 OR V_WeightStep2<=0 THEN
		SET P_LogisticsFeeLog='首重资费加重量区间1计算资费加重量区间2计算资费';
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep2, V_WeightStep3, V_UnitStep3, V_PriceStep3);
	
	IF P_Weight <= V_WeightStep3 OR V_WeightStep3<=0 THEN
		SET P_LogisticsFeeLog='首重资费加重量区间1计算资费加重量区间2计算资费加重量区间3计算资费';
		SET P_PostFee = TRUNCATE(P_PostFee, 2);
		LEAVE MAIN_LABEL;
	END IF;
	SET P_LogisticsFeeLog='首重资费加重量区间1计算资费加重量区间2计算资费加重量区间3计算资费加重量区间4计算资费';
	SET P_PostFee=P_PostFee+FN_CALC_SUB_POST_FEE(V_TruncMode, P_Weight, V_WeightStep3, 0, V_UnitStep4, V_PriceStep4);
	SET P_PostFee = TRUNCATE(P_PostFee, 2);
END//
DELIMITER ;