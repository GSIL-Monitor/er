DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_REMARK`(IN `P_Remark` VARCHAR(1024), OUT `P_LogisticsID` INT, OUT `P_TradeFlag` INT, OUT `P_SalesmanID` INT, INOUT `P_WmsType` INT, OUT `P_WarehouseID` INT, OUT `P_IsPreorder` INT, OUT `P_FreezeReason` INT,OUT `P_RemarkLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '客服备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_SalesManName,V_Kw,V_LogisticsName,V_FlagName,V_SalesManName1,V_WarehouseName,V_Reason VARCHAR(255);
	DECLARE V_MacroBeginIndex, V_MacroEndIndex, V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=1 ORDER BY rec_id ASC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET P_LogisticsID=0;
	SET P_TradeFlag=0;
	SET P_SalesmanID=0;
	SET P_WarehouseID = 0;
	SET P_IsPreorder=0;
	SET P_FreezeReason=0;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 根据括号提取
	IF @cfg_salesman_macro_begin<> '' AND @cfg_salesman_macro_end <> '' THEN
		SET V_MacroBeginIndex = LOCATE(@cfg_salesman_macro_begin, P_Remark, 1);
		IF V_MacroBeginIndex > 0 THEN
			SET V_MacroEndIndex = LOCATE(@cfg_salesman_macro_end, P_Remark, V_MacroBeginIndex+1);
			IF V_MacroEndIndex>0 THEN
				SET V_SalesManName = SUBSTRING(P_Remark, V_MacroBeginIndex+CHAR_LENGTH(@cfg_salesman_macro_begin), V_MacroEndIndex-V_MacroBeginIndex-CHAR_LENGTH(@cfg_salesman_macro_end));
				IF V_SalesManName IS NOT NULL AND V_SalesManName<>'' THEN 
					SELECT employee_id INTO P_SalesmanID FROM hr_employee WHERE fullname=V_SalesManName AND deleted=0 AND is_disabled=0 LIMIT 1;
					SET P_RemarkLog=CONCAT('根据括号提取业务员:',V_SalesManName);
				END IF;
			END IF;
		END IF;
	END IF;
	
	OPEN remark_cursor;
	REMARK_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH remark_cursor INTO V_Kw,V_Type,V_Target;
		IF V_NOT_FOUND THEN
			LEAVE REMARK_LABEL;
		END IF;
		
		IF V_Kw IS NULL OR V_Kw = '' OR V_Type<1 OR V_Type>6 THEN
			ITERATE REMARK_LABEL;
		END IF;
		
		IF LOCATE(V_Kw, P_Remark, 1) <=0 THEN 
			ITERATE REMARK_LABEL;
		END IF;
		
		IF V_Type=1 THEN
			IF V_Target>0 AND EXISTS(SELECT 1 FROM cfg_logistics WHERE logistics_id = V_Target AND is_disabled=0) THEN
				SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id = V_Target AND is_disabled=0;
				SET P_LogisticsID=V_Target;				
				IF P_RemarkLog IS NOT NULL AND P_RemarkLog<>'' THEN
					SET P_RemarkLog=CONCAT(P_RemarkLog,';根据关键词:',V_Kw,'，修改物流方式为:',V_LogisticsName);
				ELSE
					SET P_RemarkLog=CONCAT('根据关键词:',V_Kw,'，修改物流方式为:',V_LogisticsName);
				END IF;
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 THEN
				SELECT flag_name INTO V_FlagName FROM cfg_flags WHERE flag_id = V_Target AND is_disabled=0;
				SET P_TradeFlag=V_Target;
				IF P_RemarkLog IS NOT NULL AND P_RemarkLog<>'' THEN
					SET P_RemarkLog=CONCAT(P_RemarkLog,';根据关键词:',V_Kw,'，修改订单标记为:',V_FlagName);
				ELSE
					SET P_RemarkLog=CONCAT('根据关键词:',V_Kw,'，修改订单标记为:',V_FlagName);
				END IF;				
			END IF;
		ELSEIF V_Type=3 THEN
			IF V_Target>0 THEN
				SELECT fullname INTO V_SalesmanName1 FROM hr_employee WHERE employee_id=V_Target AND deleted=0 AND is_disabled=0;
				SET P_SalesmanID=V_Target;
				IF P_RemarkLog IS NOT NULL AND P_RemarkLog<>'' THEN
					SET P_RemarkLog=CONCAT(P_RemarkLog,';根据关键词:',V_Kw,'，修改业务员为:',V_SalesmanName1);
				ELSE
					SET P_RemarkLog=CONCAT('根据关键词:',V_Kw,'，修改业务员为:',V_SalesmanName1);
				END IF;					
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND EXISTS(SELECT 1 FROM cfg_warehouse WHERE warehouse_id = V_Target AND is_disabled=0) THEN
				SET P_WarehouseID=V_Target;
				SELECT type,name INTO P_WmsType,V_WarehouseName FROM cfg_warehouse WHERE warehouse_id=V_Target;
				IF P_RemarkLog IS NOT NULL AND P_RemarkLog<>'' THEN
					SET P_RemarkLog=CONCAT(P_RemarkLog,';根据关键词:',V_Kw,'，修改仓库为:',V_WarehouseName);
				ELSE
					SET P_RemarkLog=CONCAT('根据关键词:',V_Kw,'，修改仓库为:',V_WarehouseName);
				END IF;				
			END IF;
		ELSEIF V_Type=5 THEN
			SET P_IsPreorder=1;
		ELSEIF V_Type=6 THEN
			SET P_FreezeReason=GREATEST(1,V_Target);
			IF P_TradeFlag=0 THEN
				SET P_TradeFlag=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;