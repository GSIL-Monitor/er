DROP PROCEDURE IF EXISTS `I_DL_DELIVER_API_TRADE_CHANGED`;
DELIMITER //
CREATE PROCEDURE `I_DL_DELIVER_API_TRADE_CHANGED`(IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
BEGIN
	DECLARE V_ModifyFlag,V_TradeCount,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_ApiTradeStatus,V_RefundStatus TINYINT DEFAULT(0);
	DECLARE V_ShopID SMALLINT DEFAULT(0);
	DECLARE V_ApiTradeID,V_RecID BIGINT DEFAULT(0);
	DECLARE V_Tid,V_Oid VARCHAR(40);
	
	DECLARE api_trade_cursor CURSOR FOR 
		SELECT rec_id FROM api_trade FORCE INDEX(IX_api_trade_modify_flag)
		WHERE modify_flag>0 AND bad_reason=0 LIMIT 100;
	
	DECLARE api_trade_order_cursor CURSOR FOR 
		SELECT modify_flag,rec_id,status,shop_id,tid,oid,refund_status
		FROM api_trade_order WHERE modify_flag>0
		LIMIT 100;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	-- 主订单变化
	OPEN api_trade_cursor;
	TRADE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_trade_cursor INTO V_ApiTradeID;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_cursor;
				OPEN api_trade_cursor;
				ITERATE TRADE_LABEL;
			END IF;
			LEAVE TRADE_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_MAIN_ORDER(P_OperatorID, V_ApiTradeID);
		
	END LOOP;
	CLOSE api_trade_cursor;
	
	
	SET V_TradeCount = 0;
	-- 子订单变化
	OPEN api_trade_order_cursor;
	TRADE_ORDER_LABEL: LOOP
		-- modify_flag,rec_id,status,refund_status
		FETCH api_trade_order_cursor INTO V_ModifyFlag,V_RecID,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			IF V_TradeCount >= 100 THEN
				-- 需要测试，改成1测试
				SET V_TradeCount = 0;
				CLOSE api_trade_order_cursor;
				OPEN api_trade_order_cursor;
				ITERATE TRADE_ORDER_LABEL;
			END IF;
			LEAVE TRADE_ORDER_LABEL;
		END IF;
		
		SET V_TradeCount=V_TradeCount+1;
		CALL I_DL_SYNC_SUB_ORDER(P_OperatorID,V_RecID,V_ModifyFlag,V_ApiTradeStatus,V_ShopID,V_Tid,V_Oid,V_RefundStatus);
	END LOOP;
	CLOSE api_trade_order_cursor;
END//
DELIMITER ;