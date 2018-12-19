DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	END IF;
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_operator', @cfg_sales_trade_trace_operator, 0);
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		DECLARE V_Operator VARCHAR(50);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,GROUP_CONCAT(sto.src_oid),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.shop_id=ax.shop_id AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		-- 操作员
		IF @cfg_sales_trade_trace_operator THEN
			SELECT fullname INTO V_Operator FROM hr_employee WHERE employee_id=@cur_uid;
		ELSE
			SET V_Operator='';
		END IF;
		
		OPEN api_trade_cursor;
		API_TRADE_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH api_trade_cursor INTO V_Tid, V_Oids, V_ShopID;
			IF V_NOT_FOUND THEN
				LEAVE API_TRADE_LABEL;
			END IF;
			
			IF V_IsSplit AND V_TRIM THEN
				SET V_TRIM=0, V_Oids='';
			END IF;
			
			INSERT INTO sales_trade_trace(trade_id, shop_id, tid, oids, `status`, operator, remark)
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, V_Operator, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;