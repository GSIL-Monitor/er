
DROP PROCEDURE IF EXISTS `I_DISTRIBUTION_POSITION`;
DELIMITER //
CREATE PROCEDURE `I_DISTRIBUTION_POSITION`(IN `P_StockoutId` INT, 
	IN `P_WarehouseId` INT, 
	IN P_PosAllocateMode INT, 	-- 货位分配方式
	IN `P_AllowNegStock` INT,	-- 允许负库存出库
	IN `P_FreeAllocated` INT -- 如果库存不足，但有占用库存，允许先释放占用库存
	)
    SQL SECURITY INVOKER
    COMMENT '分配货位'
MAIN_LABEL:BEGIN
	
	DECLARE V_NOT_FOUND,V_RecId,V_SpecId,V_PositionId,V_BatchId,V_Sp_BatchId,V_Sp_PositionId,
		V_StockoutId,V_StockinDetailId,V_IsAllocated,V_FreeAllocated INT DEFAULT(0);
	DECLARE V_StockSpecId,V_RecId2 BIGINT DEFAULT(0);
	DECLARE V_ExpireDate,V_Sp_ExpireDate DATETIME;
	DECLARE V_BatchNo,V_PositionNo,V_SpecNo VARCHAR(40);
	DECLARE V_GoodsName, V_SpecName VARCHAR(255);
	DECLARE V_Num,V_StockNum,V_ReserveNum,V_TempNum,V_Num2,V_Num3 DECIMAL(19,4) DEFAULT(0);
	
	DECLARE spec_cursor CURSOR FOR
		SELECT rec_id,spec_id,num,position_id,batch_id,expire_date
		FROM stockout_order_detail WHERE stockout_id=P_StockoutId;
	
	/*根据出库单里设置的货位分配方式来检索。
	 mode=0，系统自动分配，即不关心具体的货位和有效期，检索出所有信息根据出库顺序依次扣除
	 mode=1, 指定货位分配，从指定货位上出库，但是不关心具体的有效期，检索出指定货位上所有的库存根据出库顺序依次扣除
	 mode=2，指定货位和有效期分配，检索指定货位和有效期的库存依次扣除
	 mode=3, 先从指定货位出库，指定货位没有的了按出库顺序出库
	 mode=4, 指定批次分配货位，如果批次=0 即出库就自动分配 否则就按照指定批次分配
	 注：mode=1或mode=2时，如果检索出来的库存不足以出库的话即使勾选负库存出库也不能允许负库存出库，应该给提示报错。
	     因为比如A货位有10个，B货位有5个，调拨开单要从A上调拨10个到C货位，在调拨单审核之前恰好A货位销售出库了1个。当
	     调拨单审核时发现A上库存不足，如果允许负库存出库的话会变成 A货位-1，B货位5，C货位10.这样客户该郁闷了，我其他货位
	     明明有库存，为什么A货位还要置为-1呢。所以当发现指定的货位库存不足时我们提示库存不足让他回去修改即可。
	 */
	DECLARE stock_cursor CURSOR FOR
		SELECT rec_id
		FROM stock_spec_detail
		WHERE is_used_up=0
			AND stock_spec_id=V_StockSpecId AND stock_num>0 AND reserve_num>=0
			AND ELT(P_PosAllocateMode+1, TRUE,
				position_id=V_Sp_PositionId,
				position_id=V_Sp_PositionId AND batch_id=V_Sp_BatchId AND expire_date=V_Sp_ExpireDate,				
				FALSE,
				(V_Sp_BatchId=0 OR batch_id=V_Sp_BatchId))
		ORDER BY stockout_sequence,expire_date,org_stockin_id,position_id,rec_id;
	
	DECLARE stock_cursor1 CURSOR FOR
		SELECT rec_id
		FROM stock_spec_detail
		WHERE is_used_up=0
			AND stock_spec_id=V_StockSpecId AND stock_num>0 AND reserve_num>=0 
		ORDER BY position_id=V_Sp_PositionId DESC, stockout_sequence,expire_date,org_stockin_id,position_id,rec_id;
	
	-- 销售出库单和其他出库单 清除占用分配 分开处理
	DECLARE allocated_stockout_cursor CURSOR FOR
		SELECT so.stockout_id,sodp.num
		FROM stockout_order so USE INDEX(IX_stockout_order_type_status),stockout_order_detail sod USE INDEX(FK_stockout_order_detail_stockout_id),stockout_order_detail_position sodp
		WHERE so.src_order_type=1 AND so.`status`=55 AND so.warehouse_id=P_WarehouseId AND so.is_allocated 
			AND (so.consign_status&4)=0 AND so.stockout_id<>P_StockoutId
			AND sod.stockout_id=so.stockout_id AND sod.spec_id=V_SpecId AND sodp.stockout_order_detail_id=sod.rec_id AND sodp.stock_spec_detail_id>0
			AND ELT(P_PosAllocateMode+1, TRUE,
				sodp.position_id=V_Sp_PositionId,
				sodp.position_id=V_Sp_PositionId AND sodp.batch_id=V_Sp_BatchId AND sodp.expire_date=V_Sp_ExpireDate,
				TRUE,
				(V_Sp_BatchId=0 OR sodp.batch_id=V_Sp_BatchId));
	
	-- 其他出库单
	DECLARE allocated_stockout_cursor2 CURSOR FOR
		SELECT so.stockout_id,sodp.num
		FROM stockout_order so USE INDEX(IX_stockout_order_type_status),stockout_order_detail sod USE INDEX(FK_stockout_order_detail_stockout_id),stockout_order_detail_position sodp
		WHERE so.src_order_type>1 AND so.`status`=50 AND so.warehouse_id=P_WarehouseId AND so.is_allocated 
			AND (so.consign_status&4)=0 AND so.stockout_id<>P_StockoutId
			AND sod.stockout_id=so.stockout_id AND sod.spec_id=V_SpecId AND sodp.stockout_order_detail_id=sod.rec_id AND sodp.stock_spec_detail_id>0
			AND ELT(P_PosAllocateMode+1, TRUE,
				sodp.position_id=V_Sp_PositionId,
				sodp.position_id=V_Sp_PositionId AND sodp.batch_id=V_Sp_BatchId AND sodp.expire_date=V_Sp_ExpireDate,
				TRUE,
				(V_Sp_BatchId=0 OR sodp.batch_id=V_Sp_BatchId));
	

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	SET @sys_code = 0,@sys_message = 'OK';
	
	SELECT is_allocated INTO V_IsAllocated FROM stockout_order WHERE stockout_id=P_StockoutId FOR UPDATE;
	IF V_IsAllocated THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	IF @cfg_gbl_goods_int_count IS NULL THEN
		CALL SP_UTILS_GET_CFG('gbl_goods_int_count', @cfg_gbl_goods_int_count, 1);
	END IF;
	
	OPEN spec_cursor;
	SPEC_LABEL:LOOP
		SET V_NOT_FOUND=0;
		FETCH spec_cursor INTO V_RecId,V_SpecId,V_Num,V_Sp_PositionId,V_Sp_BatchId,V_Sp_ExpireDate;
		IF V_NOT_FOUND<>0 THEN 
			SET V_NOT_FOUND=0;
			LEAVE SPEC_LABEL;
		END IF;
		
		IF @cfg_gbl_goods_int_count THEN
			IF V_Num<1 THEN
				-- 如果存在正数和小数切换时 异常数据的处理
				INSERT INTO stockout_order_detail_position(stockout_order_detail_id,stock_spec_detail_id,num,position_id,created)
				VALUES(V_RecId,0,V_Num,0,NOW());
				ITERATE SPEC_LABEL;
			END IF;
		ELSEIF V_Num<=0 THEN
			ITERATE SPEC_LABEL;
		END IF;
		
		SET V_FreeAllocated=P_FreeAllocated;
		
		SELECT rec_id INTO V_StockSpecId FROM stock_spec WHERE spec_id=V_SpecId AND warehouse_id=P_WarehouseId;

		IF V_NOT_FOUND<>0 THEN
			SET V_NOT_FOUND=0;
			INSERT INTO stock_spec(spec_id,warehouse_id,stock_num)
			VALUES(V_SpecId,P_WarehouseId,0)
			ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num);
			
			SET V_StockSpecId = LAST_INSERT_ID();
		END IF;
		
RETRY_ALLOCATE_LABEL: REPEAT
			IF P_PosAllocateMode=3 THEN
				OPEN stock_cursor1;
			ELSE
				OPEN stock_cursor;
			END IF;
			STOCK_LABEL:LOOP
				SET V_NOT_FOUND=0;
				IF P_PosAllocateMode=3 THEN
					FETCH stock_cursor1 INTO V_RecId2;
				ELSE
					FETCH stock_cursor INTO V_RecId2; -- V_StockNum,V_ReserveNum,V_PositionId,V_BatchId,V_ExpireDate,V_BatchNo,V_PositionNo,V_StockinDetailId;
				END IF;
				
				IF V_NOT_FOUND<>0 THEN 
					LEAVE STOCK_LABEL;
				END IF;
				
				SELECT stock_num,reserve_num,position_id,batch_id,expire_date,batch_no,position_no,stockin_detail_id
				INTO V_StockNum,V_ReserveNum,V_PositionId,V_BatchId,V_ExpireDate,V_BatchNo,V_PositionNo,V_StockinDetailId
				FROM stock_spec_detail WHERE rec_id = V_RecId2 FOR UPDATE;

				IF @cfg_gbl_goods_int_count THEN
					IF V_StockNum<1 THEN
						UPDATE stock_spec_detail SET is_used_up=2 WHERE rec_id=V_RecId2;
						ITERATE STOCK_LABEL;
					ELSEIF V_StockNum<V_ReserveNum+1 THEN
						UPDATE stock_spec_detail SET is_used_up=1 WHERE rec_id=V_RecId2;
						ITERATE STOCK_LABEL;
					END IF;
				ELSE
					IF V_StockNum<=0 THEN
						UPDATE stock_spec_detail SET is_used_up=2 WHERE rec_id=V_RecId2;
						ITERATE STOCK_LABEL;
					ELSEIF V_StockNum<=V_ReserveNum THEN
						UPDATE stock_spec_detail SET is_used_up=1 WHERE rec_id=V_RecId2;
						ITERATE STOCK_LABEL;
					END IF;
				END IF;
				
				SET V_TempNum = V_StockNum-V_ReserveNum;
				IF V_TempNum<=V_Num THEN
					SET V_Num = V_Num-V_TempNum;
					UPDATE stock_spec_detail SET reserve_num=reserve_num+V_TempNum,is_used_up=1 WHERE rec_id=V_RecId2;
					
					INSERT INTO stockout_order_detail_position(stockout_order_detail_id,stock_spec_detail_id,position_id,batch_id,position_no,batch_no,expire_date,num,stockin_detail_id,created)
					VALUES(V_RecId,V_RecId2,V_PositionId,V_BatchId,V_PositionNo,V_BatchNo,V_ExpireDate,V_TempNum,V_StockinDetailId,NOW());
				ELSE
					UPDATE stock_spec_detail SET reserve_num=reserve_num+V_Num WHERE rec_id=V_RecId2;
					
					INSERT INTO stockout_order_detail_position(stockout_order_detail_id,stock_spec_detail_id,position_id,batch_id,position_no,batch_no,expire_date,num,stockin_detail_id,created)
					VALUES(V_RecId,V_RecId2,V_PositionId,V_BatchId,V_PositionNo,V_BatchNo,V_ExpireDate,V_Num,V_StockinDetailId,NOW());
					SET V_Num=0;
				END IF;
					
				IF V_Num=0 THEN
					LEAVE STOCK_LABEL;
				END IF;
			END LOOP;
			IF P_PosAllocateMode=3 THEN
				CLOSE stock_cursor1;
			ELSE
				CLOSE stock_cursor;
			END IF;
			
			IF V_Num<=0 THEN
				LEAVE RETRY_ALLOCATE_LABEL;
			END IF;
			
			-- UPDATE stock_spec SET stock_num=-V_Num,neg_stockout_num=V_Num WHERE warehouse_id=P_WarehouseId AND spec_id=V_SpecId;
			IF (P_AllowNegStock AND P_PosAllocateMode=0) OR (P_PosAllocateMode=4 AND V_Sp_BatchId=0) THEN
					INSERT INTO stockout_order_detail_position(stockout_order_detail_id,stock_spec_detail_id,num,position_id,created)
					VALUES(V_RecId,0,V_Num,0,NOW());
					SET V_Num=0;
					LEAVE RETRY_ALLOCATE_LABEL;
			ELSEIF V_FreeAllocated THEN
				-- 取消当前订单的分配
				-- CALL I_STOCKOUT_ORDER_CLEAR_POSITION(P_StockoutId);
				
				SET V_Num3=V_Num;
				-- 释放占用的库存
				OPEN allocated_stockout_cursor;
				ALLOCATED_STOCKOUT_LABEL: LOOP
					SET V_NOT_FOUND=0;
					FETCH allocated_stockout_cursor INTO V_StockoutId,V_Num2;
					IF V_NOT_FOUND THEN
						LEAVE ALLOCATED_STOCKOUT_LABEL;
					END IF;
					
					CALL I_STOCKOUT_ORDER_CLEAR_POSITION(V_StockoutId);
					IF @sys_code THEN
						CLOSE allocated_stockout_cursor;
						CLOSE spec_cursor;
						
						SELECT g.goods_name,gs.spec_name,gs.spec_no INTO V_GoodsName, V_SpecName,V_SpecNo
						FROM goods_goods g, goods_spec gs WHERE gs.spec_id=V_SpecId AND g.goods_id=gs.goods_id;
						
						SET @sys_code = 4;
						SET @sys_message = CONCAT_WS('','库存不足: 货品-', V_GoodsName,'商家编码-',V_SpecNo,' 规格-', V_SpecName);
						LEAVE MAIN_LABEL;
					END IF;
					
					SET V_Num3=V_Num3-V_Num2;
					IF V_Num3<=0 THEN
						LEAVE ALLOCATED_STOCKOUT_LABEL;
					END IF;
				END LOOP;
				CLOSE allocated_stockout_cursor;

				-- 释放其他出库单占用库存
				OPEN allocated_stockout_cursor2;
				ALLOCATED_STOCKOUT_LABEL2:LOOP
					SET V_NOT_FOUND=0;					
					FETCH allocated_stockout_cursor2 INTO V_StockoutId,V_Num2;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND=0;
						LEAVE ALLOCATED_STOCKOUT_LABEL2;
					END IF;
					IF V_Num3<=0 THEN
						LEAVE ALLOCATED_STOCKOUT_LABEL2;
					END IF;

					CALL I_STOCKOUT_ORDER_CLEAR_POSITION(V_StockoutId);
					IF @sys_code THEN
						CLOSE allocated_stockout_cursor2;
						CLOSE spec_cursor;
						
						SELECT g.goods_name,gs.spec_name,gs.spec_no INTO V_GoodsName, V_SpecName,V_SpecNo
						FROM goods_goods g, goods_spec gs WHERE gs.spec_id=V_SpecId AND g.goods_id=gs.goods_id;
						
						SET @sys_code = 4;
						SET @sys_message = CONCAT_WS('','库存不足: 货品-', V_GoodsName,'商家编码-',V_SpecNo,' 规格-', V_SpecName);
						LEAVE MAIN_LABEL;
					END IF;
					
					SET V_Num3=V_Num3-V_Num2;
					IF V_Num3<=0 THEN
						LEAVE ALLOCATED_STOCKOUT_LABEL2;
					END IF;

				END LOOP;
				CLOSE allocated_stockout_cursor2;		
				
				IF V_Num3<=0 THEN
					SET V_FreeAllocated=0/*防止死循环*/;
					ITERATE RETRY_ALLOCATE_LABEL;
				END IF;
			END IF;
			
			-- 计算有多少占用库存
			SELECT IFNULL(SUM(reserve_num),0) INTO V_TempNum FROM stock_spec_detail
			WHERE is_used_up<2
				AND stock_spec_id=V_StockSpecId 
				AND ELT(P_PosAllocateMode+1, TRUE,
					position_id=V_Sp_PositionId,
					position_id=V_Sp_PositionId AND batch_id=V_Sp_BatchId AND expire_date=V_Sp_ExpireDate,
					TRUE,
					(V_Sp_BatchId=0 OR batch_id=V_Sp_BatchId));
			
			SELECT g.goods_name,gs.spec_name,gs.spec_no INTO V_GoodsName, V_SpecName,V_SpecNo
			FROM goods_goods g, goods_spec gs WHERE gs.spec_id=V_SpecId AND g.goods_id=gs.goods_id;
			
			SET @sys_code = 4;
			SET @sys_message = CONCAT_WS('','库存不足',
				IF(V_TempNum>0,CONCAT(' 可用库存',V_TempNum),''),
				' 货品-',V_GoodsName,'商家编码-',V_SpecNo,' 规格-', V_SpecName);
			
			CLOSE spec_cursor;
			LEAVE MAIN_LABEL;
		UNTIL FALSE END REPEAT;
	END LOOP;
	CLOSE spec_cursor;
	
	UPDATE stockout_order SET is_allocated=1 WHERE stockout_id=P_StockoutId;
END//
DELIMITER ;
