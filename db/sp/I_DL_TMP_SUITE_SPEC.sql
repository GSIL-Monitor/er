DROP PROCEDURE IF EXISTS `I_DL_TMP_SUITE_SPEC`;
DELIMITER //
CREATE PROCEDURE `I_DL_TMP_SUITE_SPEC`()
    SQL SECURITY INVOKER
	COMMENT '将组合装的单品映射到临时表里'
MAIN_LABEL: BEGIN 
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_suite_spec(
		spec_id INT(11) NOT NULL,
		num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
		order_num DECIMAL(19, 4) NOT NULL DEFAULT 0.0000
	);
END//
DELIMITER ;