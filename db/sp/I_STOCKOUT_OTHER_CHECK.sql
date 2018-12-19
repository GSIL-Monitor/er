DROP PROCEDURE IF EXISTS `I_STOCKOUT_OTHER_CHECK`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_OTHER_CHECK`(IN `P_StockoutId` INT, IN `P_IsForce` INT, IN `P_FreeAllocated` INT)
    SQL SECURITY INVOKER
    COMMENT '审核其它出库单'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_SrcOrderType, V_SrcOrderId, V_WarehouseId, V_ToWarehouseId, V_CheckouterId, V_SrcOrderStatus, 
		V_TempSpecId,V_TypeT,V_Status,V_OutsideWmsID,V_LockId,V_Type,V_CoMode,V_CooNo,
		V_TempRecId, V_Temp, V_VoucherID,V_ModeT,V_GoodsTypeCount,V_FromWarehouseId,V_SpecId2 ,V_ToWarehouseType INT DEFAULT(0);
	DECLARE V_StockoutNo, V_VoucherNO, V_VoucherPeriod,V_SpecNo,V_VphPoNO,V_PoNo,V_VphWarehouse,V_VphSpecNo VARCHAR(40);

	DECLARE V_Price, V_Amount, V_UnitRatio, V_GoodsTotalCost,V_GoodsCount,V_GoodsOutCount,V_Num,V_StockPackNum,V_OutNum,V_Count DECIMAL(19,4);
	DECLARE V_UnitId, V_BaseUnitId,V_AvailType,V_PoId,V_StockoutPackCheckNum,V_StockoutPackIsCheck INT;

	DECLARE V_GoodsName,V_VphPickNo VARCHAR(255);
	DECLARE V_AvailableStock VARCHAR(1024);
	
	DECLARE spec_cursor CURSOR FOR SELECT SUM(sod.num),sod.spec_no,sod.goods_name,ss.spec_id 
	FROM stockout_order_detail sod INNER JOIN stock_spec ss ON (ss.spec_id=sod.spec_id AND ss.warehouse_id = V_WarehouseId)
	WHERE sod.stockout_id =P_StockoutId GROUP BY sod.spec_id;
	
	DECLARE spec_cursor1 CURSOR FOR SELECT SUM(sod.num),sod.spec_no,sod.goods_name,ss.spec_id 
	FROM stockout_order_detail sod INNER JOIN stock_spec ss ON (ss.spec_id=sod.spec_id AND ss.warehouse_id = V_WarehouseId)
	WHERE sod.stockout_id =P_StockoutId GROUP BY sod.rec_id;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	SET @sys_code=0;
	SET @sys_message='ok';
	
	-- 1: 出库单状态校验
	
	SELECT stockout_no, src_order_type, src_order_id, warehouse_id, checkouter_id,`status`,reserve_i
		INTO V_StockoutNo, V_SrcOrderType, V_SrcOrderId, V_WarehouseId, V_CheckouterId,V_Status,V_LockId
	FROM stockout_order WHERE src_order_type<>1 AND stockout_id=P_StockoutId FOR UPDATE;
	SELECT `type` INTO V_Type FROM cfg_warehouse WHERE warehouse_id = V_WarehouseId;
	
	IF V_NOT_FOUND<>0 THEN
		SET V_NOT_FOUND=0;
		SET @sys_code=1;
		SET @sys_message='出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status>=95 THEN
		SET @sys_code=11;
		SET @sys_message='出库单状态已经改变';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status=5 THEN
		SET @sys_code=11;
		SET @sys_message='出库单已取消';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status<>50 AND V_Status<>55  THEN
		SET @sys_code=11;
		SET @sys_message='出库单状态错误';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_CheckouterId AND V_CheckouterId<>@cur_uid THEN
		SET @sys_code=6;
		SET @sys_message='已经被其他员工签出';
		LEAVE MAIN_LABEL;
	END IF;

	CALL SP_UTILS_GET_CFG('stockout_check_is_pack', V_StockoutPackIsCheck, 0);
	CALL SP_UTILS_GET_CFG('stockout_check_pack_num', V_StockoutPackCheckNum, 0);
	
	CALL SP_UTILS_GET_CFG('sys_stockout_other_stocknum', V_AvailType, 0);
	IF V_AvailType&32768 AND V_SrcOrderType=2  THEN
		SET V_AvailType=V_AvailType & (~32768);
	END IF;
	IF V_LockId THEN
		SET V_AvailType=V_AvailType & ~(1<<14);
	END IF;
	SET V_AvailableStock = FN_GET_STOCK(V_AvailType);
	
	
	-- 2: 校验原始单据状态
	CASE V_SrcOrderType 
		WHEN 2 THEN
			-- 调拨出库
			IF V_SrcOrderId>0 THEN
				SELECT `status`,`type`,`mode` INTO V_SrcOrderStatus,V_TypeT,V_ModeT FROM stock_transfer WHERE rec_id=V_SrcOrderId;			
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=5;
					SET @sys_message='出库单对应的调拨单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_TypeT=0 AND V_SrcOrderStatus<>40 AND V_SrcOrderStatus<>46 AND V_SrcOrderStatus<>50 THEN  -- 有了入库之后不能再出库（快速调拨时不做校验）
					SET @sys_code=6;
					SET @sys_message='出库单对应的调拨单非已审核、待出库、部分出库状态';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_LockId THEN 
					IF NOT EXISTS (SELECT 1 FROM cfg_stock_lock WHERE lock_id=V_LockId AND `status`=1 AND is_disabled=0) THEN
						SET @sys_code=17;
						SET @sys_message='调拨出库单关联的库存锁定策略被停用或者状态不是已锁定';
						LEAVE MAIN_LABEL;
					END IF;
					IF  EXISTS (SELECT 1 FROM stockout_order_detail sod 
						LEFT JOIN cfg_stock_lock_detail sld ON sod.spec_id=sld.spec_id  AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId 
						WHERE sod.stockout_id=P_StockoutId AND ISNULL(sld.spec_id)) THEN
						SET @sys_code=18;
						SET @sys_message='调拨出库单单品在锁定策略中不存在';
						LEAVE MAIN_LABEL;	
					END IF;
					IF V_Type = 1 THEN  -- 委外仓库不判断
						IF EXISTS (SELECT 1 FROM  stockout_order_detail sod
							LEFT JOIN cfg_stock_lock_detail sld ON sod.spec_id=sld.spec_id  AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId 
							WHERE sod.stockout_id=P_StockoutId  GROUP BY sod.spec_id HAVING SUM(sod.num)-AVG(sld.num-sld.out_num)>0) THEN
							SET @sys_code=16;
							SET @sys_message='调拨出库单单品出库的数量大于锁定数量';
							LEAVE MAIN_LABEL;
						END IF;
					END IF;
				END IF;
			--	IF  EXISTS (SELECT 1 FROM stockout_pick_order spo WHERE spo.stockout_id=P_StockoutId AND spo.status IN (20,30)) THEN
				--	SET @sys_code=20;
				--	SET @sys_message='调拨出库单存在拣货单并且拣货单状态不为已取消或者已完成';
				--	LEAVE MAIN_LABEL;	
			--	END IF;
				CALL SP_UTILS_GET_CFG('stock_transfer_stockout_check_num',@stock_transfer_stockout_check_num,0);
				IF @stock_transfer_stockout_check_num AND (V_Type = 1) THEN
					IF EXISTS (SELECT 1 FROM stockout_order_detail sod LEFT JOIN stock_transfer_detail `std` ON sod.src_order_detail_id = std.rec_id WHERE stockout_id = P_StockoutId AND std.num < std.out_num + sod.num) THEN
						SET @sys_code=21;
						SET @sys_message='调拨出库数量大于调拨单剩余出库数量';
						LEAVE MAIN_LABEL;
					END IF;
				END IF;
				-- 更改出库单的货位分配方式
				UPDATE stockout_order SET pos_allocate_mode=V_ModeT WHERE stockout_id=P_StockoutId;
			END IF;
		WHEN 3 THEN
			-- 采购退货出库
			IF V_SrcOrderId>0 THEN
				SELECT `status` INTO V_SrcOrderStatus FROM purchase_return WHERE return_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=7;
					SET @sys_message='出库单对应的采购退货单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_SrcOrderStatus<>40 AND V_SrcOrderStatus<>46 AND V_SrcOrderStatus<>50 THEN
					-- ROLLBACK;
					SET @sys_code=8;
					SET @sys_message='出库单对应的采购退回单非已审核、部分出库或委外待出库状态';
					LEAVE MAIN_LABEL;
				END IF;
			END IF;
		WHEN 5 THEN
		-- 生产出库	
			IF V_SrcOrderId>0 THEN
				SELECT `status` INTO V_SrcOrderStatus FROM goods_process WHERE process_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=9;
					SET @sys_message='出库单对应的生产单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_SrcOrderStatus<>40 AND V_SrcOrderStatus<>45 AND V_SrcOrderStatus<>50 AND V_SrcOrderStatus<>57 THEN
					SET @sys_code=10;
					SET @sys_message='出库单对应的生产单非已审核状态';
					LEAVE MAIN_LABEL;
				END IF;
				
			END IF;		
		
		WHEN 10 THEN
		-- 保修配件出库	
			IF V_SrcOrderId>0 THEN
				SELECT `repair_status` INTO V_SrcOrderStatus FROM sales_repair WHERE repair_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=9;
					SET @sys_message='出库单对应的保修单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_SrcOrderStatus<>40  THEN
					SET @sys_code=10;
					SET @sys_message='出库单对应的保修单非待保修状态';
					LEAVE MAIN_LABEL;
				END IF;
				
			END IF;	
		WHEN 12 THEN
		-- jit拣货出库	
			IF V_LockId THEN 
				IF NOT EXISTS (SELECT 1 FROM cfg_stock_lock WHERE lock_id=V_LockId AND `status`=1 AND is_disabled=0) THEN
					SET @sys_code=17;
					SET @sys_message='jit出库单关联的库存锁定策略被停用或者状态不是已锁定';
					LEAVE MAIN_LABEL;
				END IF;
				IF  EXISTS (SELECT 1 FROM stockout_order_detail sod 
					LEFT JOIN cfg_stock_lock_detail sld ON sod.spec_id=sld.spec_id  AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId 
					WHERE sod.stockout_id=P_StockoutId AND ISNULL(sld.spec_id)) THEN
					SET @sys_code=18;
					SET @sys_message='jit出库单单品在锁定策略中不存在';
					LEAVE MAIN_LABEL;	
				END IF;
				IF V_Type = 1 THEN
					IF EXISTS (SELECT 1 FROM  stockout_order_detail sod
						LEFT JOIN cfg_stock_lock_detail sld ON sod.spec_id=sld.spec_id  AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId 
						WHERE sod.stockout_id=P_StockoutId  GROUP BY sod.spec_id  HAVING SUM(sod.num)-AVG(sld.num-sld.out_num)>0) THEN
						SET @sys_code=16;
						SET @sys_message='jit出库单单品出库的数量大于锁定数量';
						LEAVE MAIN_LABEL;
					END IF;
				END IF;
			END IF;
			IF V_SrcOrderId>0 THEN
				SELECT `status` INTO V_SrcOrderStatus FROM jit_pick WHERE rec_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=11;
					SET @sys_message='出库单对应的jit拣货单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_SrcOrderStatus<>30 AND  V_SrcOrderStatus<>37 AND V_SrcOrderStatus<>40  THEN
					SET @sys_code=12;
					SET @sys_message='出库单对应的jit拣货单非已审核或部分出库状态';
					LEAVE MAIN_LABEL;
				END IF;
			--	IF  EXISTS (SELECT 1 FROM stockout_pick_order spo WHERE spo.stockout_id=P_StockoutId AND spo.status IN (20,30)) THEN
			--		SET @sys_code=20;
			--		SET @sys_message='jit出库单存在拣货单并且拣货单状态不为已取消或者已完成';
			--		LEAVE MAIN_LABEL;	
			--	END IF;
			END IF;	
		WHEN 13 THEN
		-- 委外出库	
			IF V_SrcOrderId>0 THEN
				SELECT `status` INTO V_SrcOrderStatus FROM outside_wms_order WHERE order_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=13;
					SET @sys_message='出库单对应的委外单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_SrcOrderStatus<>60 AND  V_SrcOrderStatus<>70  THEN
					SET @sys_code=14;
					SET @sys_message='出库单对应的委外单非待出库或部分出库状态';
					LEAVE MAIN_LABEL;
				END IF;
				
			END IF;	
			
		ELSE
			BEGIN END;
	END CASE;

	-- 检查出库单是否已装箱 现在只支持jit拣货出库
	
	
	-- 对于存在装箱单的出库单   需校验出库单货品明细 以及 所有装箱单货品明细总和进行 比较
	
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_stockout_spec_error(rec_id INT(11),error VARCHAR(255)) ENGINE=MYISAM;
	DELETE FROM tbl_stockout_spec_error;

	
	OPEN spec_cursor1;
	SPEC_LABEL:LOOP
		FETCH spec_cursor1 INTO V_Num,V_SpecNo,V_GoodsName,V_SpecId2;
		IF V_NOT_FOUND<> 0 THEN
			SET V_NOT_FOUND=0;
			LEAVE SPEC_LABEL;
		END IF;
		
		SET @tmp_avaliable_num=0;
		SET @tmp_sql = CONCAT('select (',V_AvailableStock,' ) into @tmp_avaliable_num from stock_spec ss where ss.spec_id=',V_SpecId2,' and ss.warehouse_id=',V_WarehouseId);
		CALL SP_EXEC(@tmp_sql);
		IF @tmp_avaliable_num<V_Num THEN
			SET @sys_code=19;   -- 设置成19用于定位库存不满足出库要求的错误
			SET @sys_message=CONCAT('库存数量不足以出库,商家编码:',V_SpecNo,' 货品名称:',V_GoodsName,' 可出数量:',@tmp_avaliable_num,' 欲出数量:',V_Num,' 多出量:',CONVERT(V_Num-@tmp_avaliable_num,DECIMAL(19,4)));
			INSERT INTO tbl_stockout_spec_error(error)
				VALUES(SUBSTRING(@sys_message,1,255));						
			ITERATE SPEC_LABEL;
		END IF;
	END LOOP;
	CLOSE spec_cursor1;
	
	IF @sys_code THEN
		LEAVE MAIN_LABEL;
	END IF;
	-- 以上校验通过, 开始处理本次出库...
	-- 55待打印 95已发货
	CALL I_STOCKOUT_OUT(P_StockoutId, P_IsForce, 95, 0, P_FreeAllocated);
	IF @sys_code THEN
		-- ROLLBACK;
		LEAVE MAIN_LABEL;
	END IF;
	
	--  step2: 根据单源进行处理
	CASE V_SrcOrderType
		WHEN 2 THEN -- 调拨出库
			IF V_SrcOrderId>0 THEN
				--  更新 stock_spec的调拨在途量  必须要满足 in_num<=out_num<=num???
				-- FIXME 此处有问题
				-- 1,根据当前的调拨单 更新 目的仓库对应stock_spec的调拨在途量 transfer_num
				SELECT to_warehouse_id,from_warehouse_id,`type` INTO V_ToWarehouseId,V_FromWarehouseId,V_TypeT FROM stock_transfer WHERE rec_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					-- ROLLBACK;
					SET V_NOT_FOUND=0;
					SET @sys_code=9;
					SET @sys_code='调拨单不存在入库仓库';
					LEAVE MAIN_LABEL;
				END IF;
				
				INSERT INTO stock_spec(warehouse_id,spec_id,`status`,transfer_num,last_inout_time,created)
				(
					SELECT V_ToWarehouseId,sod.spec_id,1,sod.num,NOW(),NOW()
					FROM stock_transfer_detail td,stockout_order_detail sod
					WHERE td.rec_id=sod.src_order_detail_id AND sod.src_order_type=2 AND sod.stockout_id=P_StockoutId
					ORDER BY sod.spec_id
				)
				ON DUPLICATE KEY UPDATE stock_spec.transfer_num=stock_spec.transfer_num+VALUES(stock_spec.transfer_num),last_inout_time=NOW();
				-- 关联锁定策略  更新cfg_stock_lock_detail的out_num
				IF V_LockId THEN
					UPDATE cfg_stock_lock_detail sld,(SELECT spec_id,SUM(num) AS all_num FROM stockout_order_detail WHERE stockout_id=P_StockoutId GROUP BY spec_id) tmp 
						SET sld.out_num=IF(sld.out_num+tmp.all_num>num,num,sld.out_num+tmp.all_num)
						WHERE tmp.spec_id=sld.spec_id AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId;
					UPDATE stock_spec ss,(SELECT spec_id,SUM(num) AS all_num FROM stockout_order_detail WHERE stockout_id=P_StockoutId GROUP BY spec_id) tmp
						SET ss.lock_num=IF(ss.lock_num-tmp.all_num>0,ss.lock_num-tmp.all_num,0)
						WHERE ss.warehouse_id=V_WarehouseId AND tmp.spec_id=ss.spec_id;	
					SELECT SUM(out_num) INTO V_OutNum FROM cfg_stock_lock_detail WHERE lock_id = V_LockID GROUP BY lock_id;
					UPDATE cfg_stock_lock SET out_num = V_OutNum WHERE lock_id = V_LockID;
				END IF;
				
				--  关联问题
				-- 更新 stock_transfer_detail 的出库数量 out_num
					
				UPDATE stock_transfer_detail st,stockout_order_detail sod
				SET st.out_num=st.out_num+sod.num,st.out_cost_total=st.out_cost_total+sod.total_amount
				WHERE st.rec_id=sod.src_order_detail_id AND sod.stockout_id=P_StockoutId;
				
				
				-- 更新 待调拨数量
			--	UPDATE stock_spec ss,(SELECT sod.spec_id,SUM(sd.num) AS num,SUM(sd.out_num) AS out_num,SUM(sod.num) AS sod_num
			--	FROM stock_transfer_detail sd,stockout_order_detail sod WHERE sd.rec_id = sod.src_order_detail_id AND sod.stockout_id = P_StockoutId
			--	GROUP BY sod.spec_id) tl
			--	SET ss.to_transfer_num=IF(tl.out_num>tl.num,IF(tl.out_num-tl.sod_num>tl.num,ss.to_transfer_num-0,ss.to_transfer_num-tl.num+tl.out_num-tl.sod_num),
			--	IF(tl.sod_num>ss.to_transfer_num,0,ss.to_transfer_num-tl.sod_num))
			--	WHERE ss.spec_id = tl.spec_id AND ss.warehouse_id = V_FromWarehouseId;
				/*
				UPDATE stock_spec ss,stock_transfer_detail sd,stockout_order_detail sod 
				SET ss.to_transfer_num = IF(sd.out_num>sd.num,IF(sd.out_num-sod.num>sd.num,ss.to_transfer_num-0,ss.to_transfer_num-sd.num+sd.out_num-sod.num),
				IF(sod.num>ss.to_transfer_num,0,ss.to_transfer_num-sod.num))
				WHERE sd.rec_id = sod.src_order_detail_id and ss.spec_id = sod.spec_id and ss.warehouse_id = V_FromWarehouseId and sod.stockout_id = P_StockoutId;
				*/
				SET V_GoodsOutCount = 0;
				SELECT SUM(out_num) INTO V_GoodsOutCount FROM stock_transfer_detail WHERE transfer_id = V_SrcOrderId;
				UPDATE stock_transfer SET goods_out_count = V_GoodsOutCount  WHERE rec_id=V_SrcOrderId;
						
				-- 更新状态（快速调拨时跳过此步）
				IF V_TypeT=1 THEN
				--   调拨单状态  编辑  待审核 已审核  部分出库  全部出库  部分入库 部分出库 已完成
					SET V_Count =0;
					SELECT SUM(IF(num-out_num<0,0,num-out_num)) INTO V_Count FROM stock_transfer_detail WHERE transfer_id = V_SrcOrderId;
					IF V_NOT_FOUND<>0 THEN
						-- ROLLBACK;
						SET V_NOT_FOUND=0;
						SET @sys_code=7;
						SET @sys_message='出库单对应的调拨单未找到';
						LEAVE MAIN_LABEL;
					END IF;
					
					-- if V_SrcOrderStatus<70 then  这个判断不要了，因为现在有调拨入库之后就不能再出库了
					UPDATE stock_transfer SET `status`=IF(V_Count>0,50,90) WHERE rec_id=V_SrcOrderId;
					
					-- 委外状态处理:如果调入仓库为外部仓库且全部出库,则状态更新为入库单待推送(62)
					SELECT `type` INTO V_ToWarehouseType FROM cfg_warehouse WHERE warehouse_id=V_ToWarehouseId;
					IF V_ToWarehouseType>2 AND V_Count <= 0 THEN
						UPDATE stock_transfer SET `status`= 62 WHERE rec_id=V_SrcOrderId;
					END IF;
					
					-- end if;
				END IF;
			--	UPDATE stockout_collect_area  SET `status` = 10  , stockout_id = 0 WHERE stockout_id = P_StockoutId;
			END IF;
			
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;
			/*      
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(3,V_SrcOrderId,P_OperatorId,32,CONCAT('调拨出库,出库单为:',V_StockOutNo));
			*/
		WHEN 3 THEN
			-- 采购退货出库
			
			IF V_SrcOrderId>0 THEN
				-- 更新stock_spec 的 采购退货量 return_num
				UPDATE stock_spec ss,stockout_order_detail sod,purchase_return_detail srd SET
				ss.return_num=IF(srd.out_num+sod.num>srd.num,
				IF(srd.out_num>srd.num,ss.return_num-0,ss.return_num-srd.num+srd.out_num),IF(sod.num>ss.return_num,0,ss.return_num-sod.num))
				WHERE srd.rec_id=sod.src_order_detail_id AND ss.spec_id=sod.spec_id
				AND ss.warehouse_id=V_WarehouseId AND sod.src_order_type=3 AND sod.stockout_id=P_StockoutId;
					
					
				-- 更新 purchase_return_detai 中的出库量
				INSERT INTO purchase_return_detail(return_id,spec_id,num,num2,out_num,unit_id,unit_ratio,base_unit_id,price,out_amount,amount,remark)
				(
					SELECT V_SrcOrderId,spec_id,0 AS num,0 AS num2, num ,unit_id,1 AS unit_ratio,base_unit_id,price ,total_amount,total_amount,CONCAT_WS(remark,'由出库单引入采购退货单') 
					FROM stockout_order_detail WHERE stockout_id = P_StockoutId
				)
				ON DUPLICATE KEY UPDATE purchase_return_detail.out_num = purchase_return_detail.out_num+VALUES(purchase_return_detail.out_num),
				purchase_return_detail.out_amount = purchase_return_detail.out_amount+VALUES(purchase_return_detail.out_amount);
				
				-- 更新出库单和采购退货单的关联
				
				UPDATE stockout_order_detail sod,purchase_return_detail prd SET sod.src_order_detail_id = prd.rec_id 
				WHERE sod.stockout_id = P_StockoutId AND sod.spec_id = prd.spec_id AND prd.return_id = V_SrcOrderId;	
	
				
				-- 更新采购退货单状态 
				SET V_Count=0,V_GoodsCount=0,V_GoodsTypeCount=0,V_GoodsOutCount=0;
				SELECT SUM(IF(num-out_num<0,0,num-out_num)),COUNT(DISTINCT spec_id),SUM(num),SUM(out_num) 
				INTO V_Count,V_GoodsTypeCount,V_GoodsCount,V_GoodsOutCount FROM purchase_return_detail WHERE return_id=V_SrcOrderId;
								
				UPDATE purchase_return SET `status`=IF(V_Count>0,50,90),goods_type_count = V_GoodsTypeCount,
				goods_count = V_GoodsCount,goods_out_count = V_GoodsOutCount WHERE return_id=V_SrcOrderId;
				
				INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark)
				 VALUES(V_SrcOrderId,@cur_uid,80,CONCAT('采购退货对应出库单审核:',V_StockoutNo));
				-- 修改出库单状态为 110已完成
				/*CALL SP_UTILS_GET_CFG('purchase_stockin_not_auto_debtcontacts',@cfg_stockin_auto_debtcontacts,0);
				IF @cfg_stockin_auto_debtcontacts=1 THEN   -- 若不生成应收应付单据，则 处库状态更为 110 已完成*/
					UPDATE stockout_order SET `status` =110 WHERE stockout_id=P_StockoutId;
				/*END IF;*/
			END IF;
		WHEN 4 THEN
			-- 盘点出库  更改最后盘点时间
			IF V_SrcOrderId>0 THEN
				UPDATE stock_spec ss,stockout_order_detail sod
				SET ss.last_pd_time=NOW()
				WHERE ss.spec_id=sod.spec_id AND ss.warehouse_id=V_WarehouseId AND sod.stockout_id=P_StockoutId;
				
				UPDATE stock_spec_position ssp,stockout_order_detail sod,stockout_order_detail_position sodp
				SET ssp.last_pd_time=NOW()
				WHERE ssp.spec_id=sod.spec_id AND ssp.warehouse_id=V_WarehouseId
				AND sod.rec_id=sodp.stockout_order_detail_id AND ssp.position_id=sodp.position_id
				AND sod.stockout_id=P_StockoutId;
				
				UPDATE stock_spec_detail ssd,stockout_order_detail_position sodp,stockout_order_detail sod
				SET ssd.last_pd_time=NOW()
				WHERE ssd.rec_id=sodp.stock_spec_detail_id AND sod.rec_id=sodp.stockout_order_detail_id AND sod.stockout_id=P_StockoutId;
			END IF;
			
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;
				
		WHEN 5 THEN -- 生产出库
			IF V_SrcOrderId>0 THEN   	
				-- 更新 goods_process_detail的实际出库量		
				IF EXISTS(SELECT 1 FROM goods_process_detail gpd
						INNER JOIN stockout_order_detail sod ON gpd.rec_id=sod.src_order_detail_id
						INNER JOIN goods_process gp ON gpd.process_id = gp.process_id
						WHERE sod.stockout_id=P_StockoutId AND gpd.out_num+sod.num > gpd.num*gp.process_count)
					THEN
						SET @sys_code=10;
						SET @sys_message='出库单数量大于生产单原料数量';
						LEAVE MAIN_LABEL;
				END IF; 
				UPDATE goods_process_detail gpd,stockout_order_detail sod
				SET gpd.out_num=gpd.out_num+sod.num,gpd.cost_price=((gpd.out_num-gpd.process_num-bad_num)*gpd.cost_price+sod.num*sod.price)/(gpd.out_num+sod.num-gpd.process_num-bad_num)
				WHERE gpd.rec_id=sod.src_order_detail_id AND sod.stockout_id=P_StockoutId AND gpd.is_product = 0;
				
				
				-- 更新 goods_process 的状态和出库单ID 并在goods_process_log中插入日志
				UPDATE goods_process SET `status`=IF(`status`<45,45,`status`),out_warehouse_id=V_WarehouseId WHERE process_id=V_SrcOrderId;
				INSERT INTO goods_process_log(process_id,operator_id,remark)
				VALUES(V_SrcOrderId,@cur_uid,CONCAT('原料出库，出库单为:',V_StockoutNo));
			END IF;
			
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;
		WHEN 7  THEN -- 其它出库
			BEGIN END;	
		--	UPDATE stockout_collect_area  SET `status` = 10 , stockout_id = 0 WHERE stockout_id = P_StockoutId;
		WHEN 9 THEN 
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;
		WHEN 10 THEN 
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=95 WHERE stockout_id=P_StockoutId;			
		
		WHEN 11 THEN 
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;	
		WHEN 12 THEN 
			IF V_SrcOrderId>0 THEN 
				-- 关联锁定策略  更新cfg_stock_lock_detail的out_num
				IF V_LockId THEN
					UPDATE cfg_stock_lock_detail sld,(SELECT spec_id,SUM(num) AS all_num FROM stockout_order_detail WHERE stockout_id=P_StockoutId GROUP BY spec_id) tmp 
						SET sld.out_num=IF(sld.out_num+tmp.all_num>num,num,sld.out_num+tmp.all_num)
						WHERE tmp.spec_id=sld.spec_id AND sld.warehouse_id=V_WarehouseId AND sld.lock_id=V_LockId;
					UPDATE stock_spec ss,(SELECT spec_id,SUM(num) AS all_num FROM stockout_order_detail WHERE stockout_id=P_StockoutId GROUP BY spec_id) tmp
						SET ss.lock_num=IF(ss.lock_num-tmp.all_num>0,ss.lock_num-tmp.all_num,0)
						WHERE ss.warehouse_id=V_WarehouseId AND tmp.spec_id=ss.spec_id;	
					SELECT SUM(out_num) INTO V_OutNum FROM cfg_stock_lock_detail WHERE lock_id = V_LockID GROUP BY lock_id;
					UPDATE cfg_stock_lock SET out_num = V_OutNum WHERE lock_id = V_LockID;
				END IF;
				-- 更新 jit_pick_detail的实际出库量
				UPDATE jit_pick_detail jpd,stockout_order_detail sod 
				SET jpd.stockout_num=jpd.stockout_num+sod.num
				WHERE jpd.rec_id=sod.src_order_detail_id AND sod.stockout_id=P_StockoutId;
				
				SELECT jp.vph_pick_no,jo.po_no,jp.vph_warehouse,jo.cooperation_no INTO V_VphPickNo,V_PoNo ,V_VphWarehouse,V_CooNo
				FROM jit_pick jp LEFT JOIN jit_po jo ON jo.po_no = jp.po_no WHERE jp.rec_id = V_SrcOrderId;	
				
				-- 更新jit_po_goods中的发货数量send_num
				/*UPDATE jit_po_goods jpg,stockout_order_detail sod 
				SET jpg.send_num = jpg.send_num+sod.num,jpg.dif_num = jpg.sales_count- jpg.send_num
				WHERE jpg.po_no = V_PoNo AND sod.stockout_id=P_StockoutId  AND jpg.jit_spec_no = sod.spec_no AND jpg.jit_warehouse = V_VphWarehouse;
				*/
				
				UPDATE jit_po_goods jpg,stockout_order_detail sod 
				SET jpg.send_num = jpg.send_num+sod.num
				WHERE jpg.po_no = V_PoNo AND sod.stockout_id=P_StockoutId  AND jpg.jit_spec_no = sod.spec_no AND jpg.jit_warehouse = V_VphWarehouse;
				
				UPDATE jit_po_goods jpg,stockout_order_detail sod 
				SET jpg.dif_num = jpg.sales_count- jpg.send_num
				WHERE jpg.po_no = V_PoNo AND sod.stockout_id=P_StockoutId  AND jpg.jit_spec_no = sod.spec_no AND jpg.jit_warehouse = V_VphWarehouse;
				
				-- 更新jit_stock_goods中拣货单占用量
				SELECT co_mode INTO V_CoMode FROM jit_cooperation WHERE jit_cooperation_no = V_CooNo and is_disable = 0;
				IF V_CoMode = 0 THEN
					
					UPDATE jit_stock_goods jsg ,stockout_order_detail sod 
					SET jsg.pick_reserve_num = jsg.pick_reserve_num - sod.num
					WHERE sod.stockout_id=P_StockoutId AND jsg.jit_cooperation_no = V_CooNo AND jsg.jit_spec_no = sod.spec_no  AND jsg.is_terminate_cooperation = 0;
				ELSE
					UPDATE jit_stock_goods jsg ,stockout_order_detail sod 
					SET jsg.pick_reserve_num = jsg.pick_reserve_num - sod.num
					WHERE sod.stockout_id=P_StockoutId AND jsg.jit_cooperation_no = V_CooNo AND  jsg.jit_warehouse = V_VphWarehouse AND jsg.jit_spec_no = sod.spec_no  AND jsg.is_terminate_cooperation = 0;
				END IF;
				
				-- 更新 jit_pick 的状态 
				SELECT SUM(IF(num-stockout_num<0,0,num-stockout_num)) INTO V_Count FROM jit_pick_detail WHERE vph_pick_no = V_VphPickNo;
				
				IF V_Count = 0 THEN
				
					UPDATE jit_pick SET `status` = 50 WHERE rec_id = V_SrcOrderId;
					-- 在jit_pick_log中插入日志
					INSERT INTO jit_po_log(order_id,`type`,operater_id,operator_type,message)
					VALUES(V_SrcOrderId,2,@cur_uid,6,CONCAT('JIT拣货单完全出库'));
				ELSE 
					UPDATE jit_pick SET `status` = 40 WHERE rec_id = V_SrcOrderId;
					SELECT vph_pick_no INTO V_VphPickNo FROM jit_pick WHERE rec_id = V_SrcOrderId;
					 UPDATE jit_pick_detail SET num = num-stockout_num WHERE vph_pick_no = V_VphPickNo;
					
					-- 在jit_pick_log中插入日志
					-- SELECT po_id INTO V_PoId FROM jit_po po LEFT JOIN jit_pick `pi` ON po.rec_id = pi.po_id WHERE pi.rec_id = V_SrcOrderId; 
					INSERT INTO jit_po_log(order_id,`type`,operater_id,operator_type,message)
					VALUES(V_SrcOrderId,2,@cur_uid,5,CONCAT('JIT拣货单部分出库，出库单号为：',V_StockoutNo));
				END IF;
				-- 账款明细
				INSERT IGNORE INTO fa_jit_check (po_no,shop_id,send_count,send_amount)
					SELECT  po.po_no,po.shop_id,sod.num,sod.num*jpd.price
					FROM stockout_order_detail sod
					LEFT JOIN jit_pick_detail jpd ON sod.src_order_detail_id= jpd.rec_id 
					LEFT JOIN jit_pick jp  ON jp.vph_pick_no = jpd.vph_pick_no
					LEFT JOIN jit_po po ON po.po_no = jp.po_no
					WHERE sod.stockout_id = P_StockoutId
				ON DUPLICATE KEY UPDATE fa_jit_check.send_count = fa_jit_check.send_count+VALUES(send_count),fa_jit_check.send_amount=fa_jit_check.send_amount+VALUES(send_amount);
				/*
				INSERT INTO fa_jit_check(po_no,send_count,send_amount)
					SELECT V_PoNo,sod.num,IFNULL(sod.num*jpd.price,0)
					FROM stockout_order_detail sod 
					LEFT JOIN jit_pick_detail jpd ON sod.src_order_detail_id = jpd.rec_id
					WHERE sod.stockout_id = P_StockoutId
				ON DUPLICATE KEY UPDATE fa_jit_check.send_count = fa_jit_check.send_count + VALUES(send_count),fa_jit_check.send_amount = fa_jit_check.send_amount+VALUES(send_amount);
				*/
				
			--	UPDATE stockout_collect_area  SET `status` = 10 , stockout_id = 0 WHERE stockout_id = P_StockoutId;
			END IF;
			
			-- 修改出库单状态为 95已发货  之后经过推送传到平台 变成110已完成
			UPDATE stockout_order SET `status`=95 WHERE stockout_id=P_StockoutId;	
		WHEN 13 THEN 
			IF V_SrcOrderId>0 THEN 
				
				-- 更新 outside_wms_order_detail的实际出库量
				UPDATE  outside_wms_order_detail owd,stockout_order_detail sod 
				SET owd.inout_num=owd.inout_num+sod.num
				WHERE owd.rec_id=sod.src_order_detail_id AND sod.stockout_id=P_StockoutId AND owd.order_id = V_SrcOrderId;
					
				-- 更新 outside_wms_order 的状态 
				SELECT SUM(IF(num-inout_num<0,0,num-inout_num)) INTO V_Count FROM outside_wms_order_detail WHERE order_id = V_SrcOrderId;
				
				IF V_Count = 0 THEN
				
					UPDATE outside_wms_order SET `status` = 80 WHERE order_id = V_SrcOrderId;
					-- 在outside_wms_order_log中插入日志
					INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message)
					VALUES(V_SrcOrderId,@cur_uid,11,CONCAT('委外出库单完全出库'));
				ELSE 
					UPDATE outside_wms_order SET `status` = 70 WHERE order_id = V_SrcOrderId;
					-- UPDATE outside_wms_order_detail SET num = num-inout_num WHERE order_id = V_SrcOrderId;
					
					-- 在outside_wms_order_log中插入日志
					INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message)
					VALUES(V_SrcOrderId,@cur_uid,9,CONCAT('委外出库单部分出库','出库单号为:',V_StockoutNo));
				END IF;
				
			END IF;
			
			-- 修改出库单状态为 110已完成
			UPDATE stockout_order SET `status`=110 WHERE stockout_id=P_StockoutId;	
	END CASE;
			
	-- step3 记录凭证
	
			
	-- step 4 自动结算  type:3 采购退货出库 7 其他出库 结算
	
END//
DELIMITER ;
