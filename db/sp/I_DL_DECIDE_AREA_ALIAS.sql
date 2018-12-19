DROP PROCEDURE IF EXISTS `I_DL_DECIDE_AREA_ALIAS`;
DELIMITER //
CREATE PROCEDURE `I_DL_DECIDE_AREA_ALIAS`(OUT `P_AreaAlias` VARCHAR(40), IN `P_LogisticsID` INT, IN `P_ReceiverCountry` INT, IN `P_ReceiverProvince` INT, IN `P_ReceiverCity` INT, IN `P_ReceiverDistrict` INT,OUT `P_AreaAliasLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '判断地区别名(大头笔)'
BEGIN
	DECLARE V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4, V_City, V_District VARCHAR(40);
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_AreaAlias = '';
		
	SET V_Tmp1 = CONCAT('0#');
	SET V_Tmp2 = CONCAT(V_Tmp1,',',P_ReceiverProvince);
	SET V_Tmp3 = CONCAT(V_Tmp2,',',P_ReceiverCity);
	SET V_Tmp4 = CONCAT(V_Tmp3,',',P_ReceiverDistrict);
	SET V_Tmp1 = CONCAT(V_Tmp1,',0,#',P_LogisticsID);
	SET V_Tmp2 = CONCAT(V_Tmp2,',#',P_LogisticsID);
	SET V_Tmp3 = CONCAT(V_Tmp3,',#',P_LogisticsID);
	SET V_Tmp4 = CONCAT(V_Tmp4,',#',P_LogisticsID);
	SELECT alias_name INTO P_AreaAlias FROM cfg_logistics_area_alias FORCE INDEX(UX_cfg_logistics_area_alias_path) 
	WHERE path in (V_Tmp1,V_Tmp2,V_Tmp3,V_Tmp4) ORDER BY `level` DESC LIMIT 1;
	
	if V_NOT_FOUND = 1 then
		SELECT name INTO V_City FROM dict_city WHERE city_id=P_ReceiverCity;
		SELECT name INTO V_District FROM dict_district WHERE district_id=P_ReceiverDistrict;
		SET P_AreaAlias =  CONCAT_WS(' ', V_City, V_District);
	end if;
	IF P_AreaAlias IS NOT NULL AND P_AreaAlias<>'' THEN
			SET P_AreaAliasLog=CONCAT('使用大头笔：',P_AreaAlias);
	END IF;
END//
DELIMITER ;