DROP PROCEDURE IF EXISTS `I_DL_SELECT_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SELECT_GIFT`(INOUT `P_priority` INT, IN `P_rule_id` INT,IN `P_rule_multiple_type` INT, IN `P_real_multiple` INT , IN `P_real_limit` INT , IN `P_total_name_num` INT, IN `P_total_cs_remark_num` INT,IN `P_limit_gift_stock` DECIMAL(19,4))
    SQL SECURITY INVOKER
    COMMENT '按赠品的库存优先级来选择赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,VS_spec_id,VS_is_suite,VS_gift_num,VS_real_gift_num,VS_send INT DEFAULT(0);
	DECLARE VS_Stock DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE send_cursor CURSOR FOR SELECT  spec_id,is_suite,gift_num
		FROM  cfg_gift_send_goods  
		WHERE rule_id=P_rule_id AND priority=P_priority ;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_priority=11;
	PRIORITY_LABEL: LOOP
		IF P_priority=15 THEN 
			SET P_priority=99;
			LEAVE MAIN_LABEL;
		END IF;
		SET VS_send = 0;
		OPEN send_cursor;
		SEND_LABEL: LOOP
			FETCH send_cursor INTO VS_spec_id,VS_is_suite,VS_gift_num;
			
			IF V_NOT_FOUND = 1 THEN
				SET V_NOT_FOUND = 0;
				IF VS_send THEN
					close send_cursor;
					leave MAIN_LABEL;
				ELSE
					SET P_priority=P_priority+1;
					CLOSE send_cursor;
					ITERATE PRIORITY_LABEL;
				END IF;
				
			END IF;
			
			IF VS_is_suite=0 THEN
				SELECT IFNULL(SUM(stock_num-order_num-sending_num),0) INTO VS_Stock FROM stock_spec WHERE spec_id=VS_spec_id;	
			ELSE
				SELECT SUM(tmp.suite_stock) INTO VS_Stock FROM (
				SELECT FLOOR(IFNULL(MIN(IFNULL(stock_num-order_num-sending_num, 0)/gsd.num),0)) AS suite_stock 
				FROM  goods_suite_detail gsd 
				LEFT JOIN  stock_spec ss ON ss.spec_id=gsd.spec_id 
				WHERE gsd.suite_id=VS_spec_id GROUP BY ss.warehouse_id
				) tmp;
			END IF;
			
			SET VS_real_gift_num=VS_gift_num;
			
			-- SET VS_real_gift_num=0;
			
			IF P_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=P_total_cs_remark_num;
				
			ELSEIF P_total_name_num>0 THEN 
				SET VS_real_gift_num=P_total_name_num;
				
			ELSE
				
				IF P_rule_multiple_type=0 THEN 
					IF P_real_multiple<>10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				ELSE
					IF P_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=P_real_multiple*VS_gift_num;
						
						IF VS_real_gift_num>P_real_limit and P_real_limit>0  THEN
							SET VS_real_gift_num=P_real_limit;
						END IF;
					
					ELSE
						SET VS_real_gift_num=VS_gift_num;
					END IF;
				END IF;
			END IF ;
			
			IF VS_Stock-P_limit_gift_stock<VS_real_gift_num THEN
				SET P_priority=P_priority+1;
				SET VS_send = 0;
				CLOSE send_cursor;
				ITERATE PRIORITY_LABEL;
			ELSE
				SET VS_send = 1;
			END IF;
			
		END LOOP;
		CLOSE send_cursor;
		LEAVE MAIN_LABEL;
	END LOOP; 
END//
DELIMITER ;