DROP FUNCTION IF EXISTS `FN_SPEC_NO_CONV`;
DELIMITER $$
CREATE FUNCTION `FN_SPEC_NO_CONV`(`P_GoodsNO` VARCHAR(40), `P_SpecNO` VARCHAR(40)) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
BEGIN
	DECLARE V_I INT;
	
	IF LENGTH(@cfg_goods_match_split_char)>0 THEN
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_GoodsNO);
		IF V_I THEN
			SET P_GoodsNO=SUBSTRING(P_GoodsNO, 1, V_I-1);
		END IF;
		
		SET V_I=LOCATE(@cfg_goods_match_split_char,P_SpecNO);
		IF V_I THEN
			SET P_SpecNO=SUBSTRING(P_SpecNO, 1, V_I-1);
		END IF;
		
	END IF;
	
	RETURN IF(@cfg_goods_match_concat_code,CONCAT(P_GoodsNO,P_SpecNO),IF(P_SpecNO<>'',P_SpecNO,P_GoodsNO));
END$$
DELIMITER ;