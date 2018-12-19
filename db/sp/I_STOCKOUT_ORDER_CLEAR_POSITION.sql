
DROP PROCEDURE IF EXISTS `I_STOCKOUT_ORDER_CLEAR_POSITION`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_ORDER_CLEAR_POSITION`(IN `P_StockoutID` INT)
	SQL SECURITY INVOKER
    COMMENT '清除出库单的货品分配'
MAIN_LABEL:BEGIN
	DECLARE V_ConsignStatus,V_IsAllocated,V_NOT_FOUND INT DEFAULT (0);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET @sys_code=0, @sys_message='OK';
	
	SELECT consign_status, is_allocated
	INTO V_ConsignStatus, V_IsAllocated
	FROM stockout_order WHERE stockout_id=P_StockoutID;
	
	IF V_NOT_FOUND THEN
		SET @sys_code=1, @sys_message='出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF NOT V_IsAllocated THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF (V_ConsignStatus & 4) THEN
		SET @sys_code=3, @sys_message='出库单已出库';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新货位保留库存
	SET @tmp_stock_spec_id=UNIX_TIMESTAMP();
	INSERT INTO stock_spec_detail(rec_id,reserve_num,is_used_up,stockin_detail_id,stock_spec_id,position_id,created)
	(
		SELECT sodp.stock_spec_detail_id,-sodp.num,0,@tmp_stock_spec_id,@tmp_stock_spec_id,@tmp_stock_spec_id,NOW()
		FROM stockout_order_detail sod,stockout_order_detail_position sodp
		WHERE sod.stockout_id=P_StockoutID AND sod.rec_id=sodp.stockout_order_detail_id AND sodp.stock_spec_detail_id>0
	)
	ON DUPLICATE KEY UPDATE 
		reserve_num=stock_spec_detail.reserve_num+VALUES(reserve_num),
		is_used_up=IF(stock_spec_detail.reserve_num>=stock_spec_detail.stock_num,1,0),
		last_inout_time=NOW();


	-- 删除货位分配
	DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod
	WHERE sod.stockout_id=P_StockoutID AND sodp.stockout_order_detail_id=sod.rec_id;
	
	-- 更新为未分配
	UPDATE stockout_order SET is_allocated=0,pos_allocate_mode=IF(pos_allocate_mode=2,0,pos_allocate_mode) WHERE stockout_id=P_StockoutId;
		
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `I_SALES_TRADE_TRACE`;
DELIMITER //
CREATE PROCEDURE `I_SALES_TRADE_TRACE`(IN `P_TradeID` INT, IN `P_Status` INT, IN `P_Remark` VARCHAR(100))
    SQL SECURITY INVOKER
    COMMENT '生成订单全链路数据'
MAIN_LABEL:BEGIN
	IF @cfg_sales_trade_trace_enable IS NULL THEN
		CALL SP_UTILS_GET_CFG('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	END IF;
	
	IF NOT @cfg_sales_trade_trace_enable THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	BEGIN
		DECLARE V_IsSplit,V_ShopID,V_NOT_FOUND,V_TRIM INT DEFAULT(0);
		DECLARE V_Tid VARCHAR(40);
		DECLARE V_Oids VARCHAR(255);
		
		DECLARE api_trade_cursor CURSOR FOR SELECT sto.src_tid,IF(V_IsSplit,LEFT(GROUP_CONCAT(sto.src_oid),255),''),ax.shop_id
			FROM sales_trade_order sto, api_trade ax
			WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND sto.platform_id=1 AND
				ax.platform_id=1 AND ax.tid=sto.src_tid
			GROUP BY sto.src_tid;
		
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
		DECLARE CONTINUE HANDLER FOR 1260 SET V_TRIM = 1;
		
		-- 判断订单拆分过没有
		SELECT split_from_trade_id INTO V_IsSplit FROM sales_trade WHERE trade_id=P_TradeID;
		
		IF @cur_uname IS NULL THEN
			SELECT account INTO @cur_uname FROM hr_employee WHERE employee_id=@cur_uid;
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
			VALUES(P_TradeID, V_ShopID, V_Tid, V_Oids, P_Status, @cur_uname, P_Remark);
			
		END LOOP;
		CLOSE api_trade_cursor;
	END;
END//
DELIMITER ;
