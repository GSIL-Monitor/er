DROP PROCEDURE IF EXISTS `I_DL_EXTRACT_CLIENT_REMARK`;
DELIMITER //
CREATE PROCEDURE `I_DL_EXTRACT_CLIENT_REMARK`(IN `P_Remark` VARCHAR(1024), 
	INOUT `P_TradeFlag` INT, 
	INOUT `P_WmsType` INT, 
	INOUT `P_WarehouseID` INT, 
    INOUT `P_LogisticsID`INT,
	INOUT `P_FreezeReason` INT,
	  OUT `P_ClientRemarkLog` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '客户备注提取'
MAIN_LABEL: BEGIN
	DECLARE V_Kw,V_LogisticsName,V_FlagName,V_SalesManName1,V_WarehouseName,V_Reason VARCHAR(255);
	DECLARE V_Type, V_Target, V_NOT_FOUND INT DEFAULT 0;
	
	DECLARE remark_cursor CURSOR FOR select TRIM(`keyword`),type,target from cfg_trade_remark_extract where is_disabled=0 AND `class`=2 ORDER BY rec_id DESC;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 是否启用备注提取
	IF NOT @cfg_enable_c_remark_extract OR P_Remark = '' OR P_Remark IS NULL THEN
		LEAVE MAIN_LABEL;
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
		
		IF V_Type =1 THEN
			IF V_Target>0 AND P_LogisticsID=0 AND EXISTS( SELECT 1 FROM cfg_logistics WHERE logistics_id = V_Target AND is_disabled=0 ) THEN
				SELECT logistics_name INTO V_LogisticsName FROM cfg_logistics WHERE logistics_id = V_Target AND is_disabled=0;
				SET P_LogisticsID =V_Target;
				SET P_ClientRemarkLog=CONCAT('根据关键词:',V_Kw,'，修改物流方式为:',V_LogisticsName);
			END IF;
		ELSEIF V_Type=2 THEN
			IF V_Target>0 AND (P_TradeFlag=0 OR P_TradeFlag=1) THEN
				SELECT flag_name INTO V_FlagName FROM cfg_flags WHERE flag_id = V_Target AND is_disabled=0;
				SET P_TradeFlag=V_Target;				
				IF P_ClientRemarkLog IS NOT NULL AND P_ClientRemarkLog<>'' THEN
					SET P_ClientRemarkLog=CONCAT(P_ClientRemarkLog,';根据关键词:',V_Kw,'，修改订单标记为:',V_FlagName);
				ELSE
					SET P_ClientRemarkLog=CONCAT('根据关键词:',V_Kw,'，修改订单标记为:',V_FlagName);
				END IF;
			END IF;
		ELSEIF V_Type=4 THEN
			IF V_Target>0 AND P_WarehouseID=0 AND EXISTS( SELECT 1 FROM cfg_warehouse WHERE warehouse_id = V_Target AND is_disabled=0 ) THEN
				SET P_WarehouseID=V_Target;
				SELECT type,name INTO P_WmsType,V_WarehouseName FROM cfg_warehouse WHERE warehouse_id=V_Target;
				IF P_ClientRemarkLog IS NOT NULL AND P_ClientRemarkLog<>'' THEN
					SET P_ClientRemarkLog=CONCAT(P_ClientRemarkLog,';根据关键词:',V_Kw,'，修改仓库为:',V_WarehouseName);
				ELSE
					SET P_ClientRemarkLog=CONCAT('根据关键词:',V_Kw,'，修改仓库为:',V_WarehouseName);
				END IF;
			END IF;
		ELSEIF V_Type=6 THEN
			IF P_FreezeReason=0 THEN
				SET P_FreezeReason=GREATEST(1,V_Target);
				IF P_TradeFlag=0 THEN
					SET P_TradeFlag=1;
				END IF;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE remark_cursor;
END//
DELIMITER ;