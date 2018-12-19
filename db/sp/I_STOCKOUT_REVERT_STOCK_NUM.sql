
DROP PROCEDURE IF EXISTS `I_STOCKOUT_REVERT_STOCK_NUM`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_REVERT_STOCK_NUM`(IN `P_StockoutId` INT,IN `P_Type` INT)
    SQL SECURITY INVOKER
    COMMENT '出库单驳回恢复库存'
MAIN_LABEL:BEGIN
	
	DECLARE V_NOT_FOUND,V_WarehouseId,V_Status,V_SpecId,V_PositionId,V_RecId,
			V_ZoneId,V_Flag,V_DefPositionId INT DEFAULT(0);
	DECLARE V_StockSpecDetailId,V_StockRecId BIGINT DEFAULT(0);
	DECLARE V_Num,V_CostPrice,V_StockNumOld,V_StockNumNew,
			V_NegStockoutNum,V_CostPriceOld,V_CostPriceNew, V_StockDiffOld,V_CostAdjustTemp DECIMAL(19,4);

	DECLARE V_Now DATETIME;

	DECLARE order_detail_cursor CURSOR FOR 
	SELECT sodp.stock_spec_detail_id, sod.spec_id, sodp.num ,sodp.position_id,sod.rec_id,sod.cost_price,IFNULL(cwp.zone_id,0)
		FROM stockout_order_detail sod 
		LEFT JOIN stockout_order_detail_position sodp ON sodp.stockout_order_detail_id=sod.rec_id 
		LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = sodp.position_id
		WHERE stockout_id=P_StockoutId;
		
	-- P_Type=0 撤销出库 P_Type=1驳回审核  占用量问题
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET @sys_code = 0;
	SET @sys_message = 'ok';
	
	SET V_Now = NOW();
	
	SELECT warehouse_id,`status` INTO V_WarehouseId,V_Status
	FROM stockout_order WHERE stockout_id = P_StockoutId;
	
	IF V_NOT_FOUND THEN
		SET @sys_code = 1;
		SET @sys_message = '出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_Flag=0; -- 标记是否存在负库存情况
	
	OPEN order_detail_cursor;
	DETAIL_LABEL:LOOP
		FETCH order_detail_cursor INTO V_StockSpecDetailId,V_SpecId,V_Num,V_PositionId,V_RecId,V_CostPrice,V_ZoneId;
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE DETAIL_LABEL;
		END IF;
		
		-- 根据单品查找当前库存情况？
		
		SELECT rec_id, stock_num, neg_stockout_num,default_position_id
		INTO V_StockRecId, V_StockNumOld, V_NegStockoutNum,V_DefPositionId
		FROM stock_spec WHERE spec_id=V_SpecId AND warehouse_id=V_WarehouseId FOR UPDATE; 
		
		IF V_DefPositionId = 0 THEN
			SET V_DefPositionId=-V_WarehouseId;
		END IF;
		
		IF V_NOT_FOUND THEN
			SET @sys_code=101;
			SET @sys_message='货品在仓库中未找到记录';			
			LEAVE MAIN_LABEL;		
		ELSE
		
			IF V_NegStockoutNum>0 THEN
				-- 存在负库存
				SET V_Flag = 1;
				-- 1，如果负库存大于等于当前货品数量 
				IF V_NegStockoutNum>=V_Num THEN
					SET V_StockNumNew = V_StockNumOld + V_Num;								
								
					UPDATE stock_spec 
						SET stock_num=V_StockNumNew,
						neg_stockout_num=neg_stockout_num-V_Num, 									
						last_inout_time=V_Now, `status`=1 WHERE rec_id=V_StockRecId;					
					
					INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
						VALUES(V_WarehouseId,V_SpecId,0,0,V_Num,V_Now,V_Now)
						ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=V_Now;
					
				ELSE
				-- 2，如果负库存小于当前货品数量				
					SET V_StockNumNew = V_StockNumOld + V_Num;				
					
					UPDATE stock_spec 
						SET stock_num=V_StockNumNew,			
						neg_stockout_num=0,last_inout_time=V_Now, `status`=1 WHERE rec_id=V_StockRecId;
					
					-- 恢复 stock_spec_detail 中的库存
					
					IF V_StockSpecDetailId =0 THEN
						
						INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,spec_id, 
						 position_id,position_no,zone_id,zone_no,batch_id,cost_price,stock_num,virtual_num,is_used_up,created) 
						(SELECT ss.rec_id,0,0,ss.spec_id,cwp.rec_id,cwp.position_no,cwz.zone_id,cwz.zone_no,0,ss.cost_price,V_Num-V_NegStockoutNum,V_Num-V_NegStockoutNum,0,NOW() 
							FROM stock_spec ss 
							LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=V_DefPositionId 
							LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id 
							WHERE ss.spec_id=V_SpecId AND ss.warehouse_id=V_WarehouseId 
						)
						ON DUPLICATE KEY UPDATE stock_num=stock_spec_detail.stock_num+VALUES(stock_num),virtual_num=virtual_num+VALUES(virtual_num),
						is_used_up=VALUES(is_used_up);				
						
						INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
						(
							SELECT V_WarehouseId,V_SpecId,V_DefPositionId,zone_id,V_Num-V_NegStockoutNum,V_Now,V_Now
							FROM cfg_warehouse_position WHERE rec_id = V_DefPositionId
						) ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=V_Now;
					
					ELSE
					
					
						UPDATE stock_spec_detail SET stock_num=stock_num+V_Num-V_NegStockoutNum,
							last_inout_time=V_Now,is_used_up=IF(stock_num<=0,2,IF(reserve_num>=stock_num,1,0)) 
						WHERE rec_id=V_StockSpecDetailId;											
						
						-- 恢复 stock_spec_position 库存
						INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
						VALUES(V_WarehouseId,V_SpecId,V_PositionId,V_ZoneId,V_Num-V_NegStockoutNum,V_Now,V_Now)
						ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=V_Now;
					
					END IF;
					
					UPDATE stock_spec_position SET stock_num=0,last_inout_time=V_Now WHERE warehouse_id=V_WarehouseId AND spec_id=V_SpecId AND position_id=0;
				
				END IF;
			ELSE
				-- 不存在负库存
							
				IF V_StockNumOld + V_Num > 0 THEN
					-- 正常情况
					SET V_StockNumNew = V_StockNumOld + V_Num;					
				ELSE
					-- 之前库存为0，入库数量也是0，特殊处理，否则会除0异常
					SET V_StockNumNew = 0;
				END IF;
					
				UPDATE stock_spec 
					SET stock_num=V_StockNumNew, 					
					neg_stockout_num=0, 					
					last_inout_time=V_Now, `status`=1 WHERE rec_id=V_StockRecId;
				
				IF V_StockSpecDetailId = 0 THEN
					
					SET V_Flag = 1;
					INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,spec_id, 
					 position_id,position_no,zone_id,zone_no,batch_id,cost_price,stock_num,virtual_num,is_used_up,created) 
					(SELECT ss.rec_id,0,0,ss.spec_id,cwp.rec_id,cwp.position_no,cwz.zone_id,cwz.zone_no,0,ss.cost_price,V_Num,V_Num,0,NOW() 
						FROM stock_spec ss 
						LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=V_DefPositionId 
						LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id 
						WHERE ss.spec_id=V_SpecId AND ss.warehouse_id=V_WarehouseId 
					)
					ON DUPLICATE KEY UPDATE stock_num=stock_spec_detail.stock_num+VALUES(stock_num),virtual_num=virtual_num+VALUES(virtual_num),
					is_used_up=VALUES(is_used_up);				
					
					INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
					(
						SELECT V_WarehouseId,V_SpecId,V_DefPositionId,zone_id,V_Num,V_Now,V_Now
						FROM cfg_warehouse_position WHERE rec_id = V_DefPositionId
					) ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=V_Now;
					
				ELSE
					-- 恢复 stock_spec_detail 中的库存
					UPDATE stock_spec_detail SET stock_num=stock_num+V_Num,
					last_inout_time=V_Now,is_used_up=IF(reserve_num>=stock_num,1,0) WHERE rec_id=V_StockSpecDetailId;

					-- 恢复 stock_spec_position 库存
					INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
						VALUES(V_WarehouseId,V_SpecId,V_PositionId,V_ZoneId,V_Num,V_Now,V_Now)
						ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=V_Now;
				
				END IF;			
			END IF;  		
		END IF;	
				
	END LOOP;
	CLOSE order_detail_cursor;
	
	-- 恢复今日销量
	INSERT INTO stock_spec(warehouse_id,spec_id,stock_num)
	(
		SELECT V_WarehouseId,spec_id,num
		FROM stockout_order_detail
		WHERE stockout_id=P_StockoutId
		ORDER BY spec_id
	)
	ON DUPLICATE KEY UPDATE 
		today_num=IF(DATE(last_sales_time)=CURRENT_DATE(),GREATEST(today_num-VALUES(stock_num),0),today_num);
	
	
	--  对于驳回的时存在未知货位的情况  需要把该订单的分配信息删除 同时 修改订单为未分配 ，该订单再次出库时要重新分配
	IF V_Flag THEN				
		-- 删除货位分配
		DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod
		WHERE sod.stockout_id=P_StockoutId AND sodp.stockout_order_detail_id=sod.rec_id;
		
		-- 更新为未分配
		UPDATE stockout_order SET is_allocated=0,pos_allocate_mode=IF(pos_allocate_mode=2,0,pos_allocate_mode) WHERE stockout_id=P_StockoutId;
	ELSE
		-- 如果不存在负库存情况 完全恢复其货位分配的占用情况...
		IF P_Type = 0 THEN
			SET @tmp_stock_spec_id=UNIX_TIMESTAMP();
			INSERT INTO stock_spec_detail(rec_id,reserve_num,is_used_up,stockin_detail_id,stock_spec_id,position_id,created)
			(
				SELECT sodp.stock_spec_detail_id,sodp.num,0,@tmp_stock_spec_id,@tmp_stock_spec_id,@tmp_stock_spec_id,NOW()
				FROM stockout_order_detail sod,stockout_order_detail_position sodp
				WHERE sod.stockout_id=P_StockoutId AND sod.rec_id=sodp.stockout_order_detail_id AND sodp.stock_spec_detail_id>0
			)
			ON DUPLICATE KEY UPDATE 
				stock_spec_detail.reserve_num=stock_spec_detail.reserve_num+VALUES(reserve_num),
				is_used_up=IF(stock_spec_detail.stock_num<=0,2,IF(stock_spec_detail.reserve_num>=stock_spec_detail.stock_num,1,0)),
				last_inout_time=NOW();
		END IF;
		
	END IF;
	
END//
DELIMITER ;
