
DROP PROCEDURE IF EXISTS `I_STOCKIN_CHECK`;
DELIMITER //
CREATE PROCEDURE `I_STOCKIN_CHECK`(IN `P_StockinId` INT)
    SQL SECURITY INVOKER
    COMMENT '入库单审核细节处理'
MAIN_LABEL:BEGIN
	CALL I_STOCKIN_CHECK_EX(P_StockinId,1);
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `I_STOCKIN_CHECK_EX`;
DELIMITER //
CREATE PROCEDURE `I_STOCKIN_CHECK_EX`(IN `P_StockinId` INT,IN `P_IsForce` INT)
    SQL SECURITY INVOKER
    COMMENT '入库单审核细节处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_Status,V_ChargeStatus,V_StatusP,V_StatusT,V_RefundStatus,V_ProcessStatus,V_ProcessMaterialFinish,V_ProcessProductFinish,V_ProcessBadProductFinish,V_RefundType,V_WarehouseId,V_IsDefect,V_SrcOrderType,V_SrcOrderId,V_TypeT,
		V_SrcOrderDetailType,V_SrcOrderDetailId,V_Count,V_InCount,V_ZoneId,V_TmpProcessStatus, V_SwapTradeID,V_SODP_RecId,V_SOD_RecId,V_StockSpecDetailId,V_ConsignStatus,V_IsZeroCost,V_IsSnEnable,V_SkipSnCheck,V_WarehouseId2,V_ProviderId,
		IsTradeCharged,V_JITRefundStatus,V_JitRefundID,V_OutsideWmsStatus,V_OutsideWmsID INT DEFAULT(0);
	DECLARE V_StockinNo, V_ZoneNO, V_PositionNO,V_BatchNO, V_SrcOrderNO,V_SpecNo,V_SpecNo2,V_PoNo VARCHAR(40);
	DECLARE  V_Remark,V_GoodsName,V_GoodsName2,V_VphRefundNo,V_SpecUpLoad VARCHAR(255);
	 -- 状态  仓库  订单来源 (采购入库、调拨入库、退货入库、盘盈入库、生产入库、其他入库、jit退货单、委外入库...)
	DECLARE V_StockinDetailId,V_SpecId,V_PositionId,V_BatchId,V_DefPositionId,V_StockPositonId,V_TradeID,V_OrgStockinId,V_OrgStockinDetailId,V_CountNum,V_ReceiveDays INT DEFAULT(0);
	DECLARE V_StockSpecId BIGINT DEFAULT(0);
	DECLARE V_ExpireDate, V_Now,V_ProductionDate DATETIME DEFAULT '0000-00-00 00:00:00';	
	DECLARE V_CostPrice, V_CostPriceOld, V_CostPriceNew, V_Num, V_StockNumOld, V_StockNumNew,V_NegStockoutNum, V_StockDiff,
		V_CostAdjustTemp, V_GoodsAmountTemp, V_ActualRefundAmount, V_Paid, V_NegNum, V_TmpNum,V_SharePostCost,
		V_StockSpecCost,V_StockSpecNum,V_SalesRepairNum,V_SalesRepairStockinNum,V_GoodsInCount,V_TmpTotalNum,V_SendNum DECIMAL(19,4) DEFAULT(0);
	
	DECLARE V_SnId1,V_SnId2,V_StockoutId,V_TmpCount,V_RepairSpecId INT DEFAULT(0);
	DECLARE order_detail_cursor CURSOR FOR SELECT sod.rec_id, sod.spec_id, sod.num,cwp.zone_id,cwz.zone_no,
		sod.position_id, cwp.position_no, IFNULL(sod.batch_id,0), IFNULL(sgb.batch_no,''), sod.expire_date, sod.cost_price,sod.share_post_cost,sod.org_stockin_detail_id,
		gg.goods_name,gs.spec_no,gs.is_sn_enable,cwp.warehouse_id,sod.production_date,gs.receive_days
		FROM stockin_order_detail sod 
			LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=sod.position_id 
			LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id
			LEFT JOIN stock_goods_batch sgb ON sgb.batch_id=sod.batch_id
			LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
		WHERE stockin_id=P_StockinId;
	
	-- 使用出库单的发货状态来进行排序,目的是为了先处理已经发货的,对于那些未发货只是占用的情况处理起来就简单了
	/*
	DECLARE spec_stockout_negative_cursor CURSOR FOR SELECT sodp.rec_id,sod.rec_id,sodp.num,so.consign_status
		FROM stockout_order_detail_position sodp
			LEFT JOIN stockout_order_detail sod ON sodp.stockout_order_detail_id=sod.rec_id
			LEFT JOIN stockout_order so ON so.stockout_id = sod.stockout_id
		WHERE so.warehouse_id=V_WarehouseId AND sodp.position_id=0 AND sodp.stock_spec_detail_id=0 AND sod.spec_id=V_SpecId
		ORDER BY (so.consign_status & 4) DESC,sodp.rec_id ;
	*/
	-- -------------SN-BEGIN--------------------
	--  退货货品sn校验 
	DECLARE stockout_detail_sn_cursor CURSOR FOR
			SELECT sn_id
			FROM stockout_detail_sn
			WHERE stockout_id = V_StockoutId AND spec_id = V_SpecId;
			
	-- 调拨货品sn校验  调拨单的出库原单不唯一
	DECLARE stockout_transfer_detail_sn_cursor CURSOR FOR
		SELECT sn_id 
		FROM stockout_detail_sn sds
		LEFT JOIN stockout_order_detail sod ON sds.stockout_id = sod.stockout_id AND sds.spec_id = sod.spec_id
		LEFT JOIN stockout_order so ON so.stockout_id = sod.stockout_id 
		WHERE so.src_order_type = 2 AND so.src_order_id  = V_SrcOrderId AND sod.spec_id = V_SpecId AND so.status <> 5;
		
	
	-- --------------SN-END---------------------
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET @sys_code = 0 ;
	SET @sys_message = 'ok';
	
	CALL SP_UTILS_GET_CFG('open_message_strategy', @cfg_open_message_strategy,0);  -- 短信策略全局配置

	-- 1: 入库单状态校验
	SELECT `status`, stockin_no, warehouse_id, src_order_type, src_order_id, src_order_no 
		INTO V_Status, V_StockinNo, V_WarehouseId, V_SrcOrderType, V_SrcOrderId, V_SrcOrderNO
		FROM stockin_order WHERE stockin_id = P_StockinId FOR UPDATE;	
	IF V_NOT_FOUND<>0 THEN
		SET V_NOT_FOUND=0;
		SET @sys_code=1;
		SET @sys_message='入库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status <> 30 AND V_Status <>35 THEN
		SET @sys_code=2;
		SET @sys_message='入库单状态已经改变';
		LEAVE MAIN_LABEL;
	END IF;
	
	/*IF @cur_uid<>0 AND V_SrcOrderType<>2 AND NOT EXISTS(SELECT 1 FROM cfg_employee_warehouse WHERE warehouse_id = V_WarehouseId AND employee_id = @cur_uid  AND is_denied=0) THEN

		SET @sys_code=20;

		SET @sys_message='没有该入库仓库的权限';

		LEAVE MAIN_LABEL;

	END IF;
	
	IF V_SrcOrderType=2 THEN
		CALL SP_UTILS_GET_CFG('stock_transfer_to_warehouse_all', @cfg_stock_transfer_to_warehouse_all,0);
		IF @cur_uid<>0 AND @cfg_stock_transfer_to_warehouse_all=0 AND NOT EXISTS(SELECT 1 FROM cfg_employee_warehouse WHERE warehouse_id = V_WarehouseId AND employee_id = @cur_uid  AND is_denied=0) THEN
			SET @sys_code=20;
			SET @sys_message='没有该入库仓库的权限';
			LEAVE MAIN_LABEL;
		END IF;
	END IF;*/

	-- 2: 校验源单据的状态
	CASE  V_SrcOrderType
		WHEN 1 THEN -- 采购入库
			IF V_SrcOrderId >0  THEN
				SELECT `status`,provider_id INTO V_StatusP,V_ProviderId FROM purchase_order WHERE purchase_id = V_SrcOrderId;
				IF V_NOT_FOUND <>0 THEN
					SET V_NOT_FOUND = 0;
					SET @sys_code = 11;
					SET @sys_message='入库单对应的采购单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				IF V_StatusP <> 48 AND V_StatusP <> 50 AND V_StatusP <> 60 AND V_StatusP <> 70 AND V_StatusP<>80 THEN
					SET @sys_code = 12;
					SET @sys_message = '入库单对应的采购单的状态非待结算或者部分入库状态或者已到货状态或者部分结算状态';
					LEAVE MAIN_LABEL;
				END IF;
			END IF;
		WHEN 2 THEN -- 调拨入库
			IF V_SrcOrderId>=0 THEN
				SELECT `status`,`type` INTO V_StatusT,V_TypeT FROM stock_transfer WHERE rec_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=13;
					SET @sys_message='入库单对应的调拨单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 分步调拨单验证部分出库，全部出库，待入库，部分入库时才能审核，快速调拨则不验证
				IF V_TypeT=0 AND V_StatusT<>50 AND V_StatusT<>60 AND V_StatusT<> 66 AND V_StatusT<>70 THEN
					SET @sys_code=14;
					SET @sys_message='入库单对应的调拨单状态错误';
					LEAVE MAIN_LABEL;
				END IF;
				
				IF (SELECT from_warehouse_id=to_warehouse_id FROM stock_transfer WHERE rec_id=V_SrcOrderId) THEN
					SET V_SkipSnCheck=1;
				END IF;
				
			--	IF EXISTS( SELECT 1 FROM stockin_order_detail sod LEFT JOIN stock_transfer_detail `std` ON sod.src_order_detail_id=`std`.rec_id WHERE stockin_id=P_StockinId AND std.out_num<sod.num+`std`.in_num) THEN
			--		SET @sys_code=18;
			--		SET @sys_message='调拨入库的数量大于出库剩余的数量';
			--		LEAVE MAIN_LABEL;
			--	END IF;
				
			END IF;
		WHEN 3 THEN
			-- 销售退货入库
			IF V_SrcOrderId>=0 THEN
				SELECT process_status INTO V_RefundStatus FROM sales_refund WHERE refund_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=15;
					SET @sys_message ='入库单对应的退货单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 判断退货单状态是否为待收货			
				IF V_RefundStatus<>60 AND V_RefundStatus<>65 AND V_RefundStatus <> 69 AND V_RefundStatus<>70 AND V_RefundStatus<>71 THEN
					SET @sys_code=16;
					SET @sys_message='入库单对应的退货单状态错误';
					LEAVE MAIN_LABEL;
				END IF; 
				
			END IF;
		WHEN 5 THEN
			-- 生产入库
			IF V_SrcOrderId>=0 THEN
				
				SELECT STATUS INTO V_ProcessStatus FROM goods_process WHERE process_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=15;
					SET @sys_message ='入库单对应的生产单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 判断生产单状态是否为待收货			
				IF V_ProcessStatus<>50 AND V_ProcessStatus<>55 AND V_ProcessStatus<>57 AND V_ProcessStatus<>80 THEN
					SET @sys_code=16;
					SET @sys_message='入库单对应的生产单单状态错误';
					LEAVE MAIN_LABEL;
				END IF; 	
			END IF;
		WHEN 7 THEN
			-- 保修单保修货品入库
			IF V_SrcOrderId>=0 THEN
				SELECT repair_status INTO V_RefundStatus FROM sales_repair WHERE repair_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=15;
					SET @sys_message ='入库单对应的保修单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 判断保修单状态是否为待入库或部分入库			
				IF V_RefundStatus<30  THEN
					SET @sys_code=16;
					SET @sys_message='入库单对应的保修单状态错误';
					LEAVE MAIN_LABEL;
				END IF; 
				
			END IF;
		WHEN 11 THEN
			-- jit退货入库
			IF V_SrcOrderId>=0 THEN
				SELECT `status` INTO V_JITRefundStatus FROM jit_refund WHERE rec_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=15;
					SET @sys_message ='入库单对应的jit退货单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 判断jit退货单状态是否为已审核			
				IF V_JITRefundStatus<>30 AND V_JITRefundStatus<>40 THEN
					SET @sys_code=16;
					SET @sys_message='入库单对应的jit退货单状态错误';
					LEAVE MAIN_LABEL;
				END IF; 
				
			END IF;
		WHEN 12 THEN
			-- 委外入库
			IF V_SrcOrderId>0 THEN
				SELECT `status` INTO V_OutsideWmsStatus FROM outside_wms_order WHERE order_id=V_SrcOrderId;
				IF V_NOT_FOUND<>0 THEN
					SET V_NOT_FOUND=0;
					SET @sys_code=15;
					SET @sys_message ='入库单对应的委外单未找到';
					LEAVE MAIN_LABEL;
				END IF;
				-- 判断委外单状态是否为待入库和部分入库			
				IF V_OutsideWmsStatus<>65 AND V_OutsideWmsStatus<>75 THEN
					SET @sys_code=16;
					SET @sys_message='入库单对应的委外单状态错误';
					LEAVE MAIN_LABEL;
				END IF; 
				
			END IF;
		ELSE
			BEGIN END;
	END CASE;
	
	-- 3: 校验入库数量和价格不能为负
	SET V_NOT_FOUND = 0;
	-- SELECT num,cost_price INTO V_Num,V_CostPrice FROM stockin_order_detail WHERE stockin_id=P_StockinId AND (num<=0 OR cost_price<0) LIMIT 1;
	SELECT sod.num,sod.cost_price,ss.cost_price,gs.is_allow_zero_cost,gg.goods_name,gs.spec_no,IFNULL(ss.stock_num,0) INTO V_Num,V_CostPrice,V_StockSpecCost,V_IsZeroCost,V_GoodsName,V_SpecNo,V_StockSpecNum
	FROM stockin_order_detail sod LEFT JOIN stock_spec ss ON ss.spec_id = sod.spec_id AND warehouse_id=V_WarehouseId
	LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
	WHERE sod.stockin_id=P_StockinId AND gs.is_allow_zero_cost=0 AND (sod.num<=0 OR sod.cost_price IS NULL OR sod.cost_price<=0 OR 
		(ss.stock_num<>0 AND ss.cost_price=0))
	LIMIT 1;
	
	IF V_NOT_FOUND=0 THEN
		IF V_Num<=0 THEN
			SET @sys_code=22;
			SET @sys_message=CONCAT('入库数量必须大于0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		ELSEIF V_CostPrice<=0 THEN
			SET @sys_code=21;
			SET @sys_message=CONCAT('入库价格必须大于0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		ELSEIF V_StockSpecCost=0 THEN
			SET @sys_code=24;
			SET @sys_message=CONCAT('初始库存成本为0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		ELSE
			SET @sys_code=23;
			SET @sys_message=CONCAT('入库价格不允许为0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		END IF;
		
		LEAVE MAIN_LABEL;
	END IF;
	SET V_NOT_FOUND = 0;
	SELECT sod.num,sod.cost_price,ss.cost_price,gs.is_allow_zero_cost,gg.goods_name,gs.spec_no,IFNULL(ss.stock_num,0) INTO V_Num,V_CostPrice,V_StockSpecCost,V_IsZeroCost,V_GoodsName,V_SpecNo,V_StockSpecNum
	FROM stockin_order_detail sod LEFT JOIN stock_spec ss ON ss.spec_id = sod.spec_id AND warehouse_id=V_WarehouseId
	LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
	WHERE sod.stockin_id=P_StockinId AND gs.is_allow_zero_cost=1 AND (sod.num<=0 OR sod.cost_price<0)
	LIMIT 1;
	
	IF V_NOT_FOUND=0 THEN
		IF V_Num<=0 THEN
			SET @sys_code=22;
			SET @sys_message=CONCAT('入库数量必须大于0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		ELSEIF V_CostPrice<0 THEN
			SET @sys_code=21;
			SET @sys_message=CONCAT('入库价格不能小于0 商家编码:',V_SpecNo,'货品:',V_GoodsName);
		END IF;
		
		LEAVE MAIN_LABEL;
	END IF;
	
	
	-- 以上校验通过, 开始处理本次入库...
	SET V_Now = NOW();
	
	-- step0: 建立临时表, 保存和账款相关的数据
	CREATE TEMPORARY TABLE IF NOT EXISTS tbl_vr_stockin_check(
		spec_id INT(11),
		goods_amount DECIMAL(19,4), 
		adjust_amount DECIMAL(19,4));
	DELETE FROM tbl_vr_stockin_check;
			
	-- step1: 库存与成本价的更新, 凭证相关金额计算
	
	OPEN order_detail_cursor;
	DETAIL_LABEL2:LOOP
	
		SET V_NOT_FOUND = 0;
		SET V_SpecId = 0;
		SET V_CostPriceOld = 0;
		SET V_StockNumOld = 0;
		SET V_CostPrice = 0;
		SET V_Num = 0;
		SET V_CostPriceNew = 0;
		SET V_StockNumNew = 0;
		SET V_Remark = '';
		SET V_StockSpecId = 0;
		SET V_NegStockoutNum = 0;
		SET V_StockDiff = 0;
		SET V_GoodsAmountTemp = 0;
		SET V_CostAdjustTemp = 0;
		

		FETCH order_detail_cursor INTO V_StockinDetailId,V_SpecId,V_Num,V_ZoneId,V_ZoneNO,V_PositionId,V_PositionNO,V_BatchId,V_BatchNO,V_ExpireDate,V_CostPrice,V_SharePostCost,V_OrgStockinDetailId,V_GoodsName2,V_SpecNo2,V_IsSnEnable,V_WarehouseId2,V_ProductionDate,V_ReceiveDays;
		IF V_NOT_FOUND<>0 THEN
			SET V_NOT_FOUND=0;
			LEAVE DETAIL_LABEL2;
		END IF;
		
		IF V_Num % 1 <>0 THEN
			IF @cfg_gbl_goods_int_count IS NULL THEN
				CALL SP_UTILS_GET_CFG('gbl_goods_int_count', @cfg_gbl_goods_int_count, 1);
			END IF;
			IF @cfg_gbl_goods_int_count THEN
				CLOSE order_detail_cursor;
				SET @sys_code = 33;
				SET @sys_message=CONCAT('系统配置为整数,入库数量为小数,请修改入库数量,货品:',V_GoodsName2,',商家编码:',V_SpecNo2);
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
		IF V_WarehouseId2<>V_WarehouseId THEN
			CLOSE order_detail_cursor;
			SET @sys_code = 32;
			SET @sys_message=CONCAT('货位:',V_PositionNO,'不属于所在仓库');
			LEAVE MAIN_LABEL;
		END IF;

		-- 如果当前是强制审核并且 生产日期+最佳收货期天数 > 当前时间
		IF P_IsForce<>1 AND V_ProductionDate>'0000-00-00 00:00:00' AND V_ReceiveDays<>0  THEN
			IF DATE_ADD(V_ProductionDate,INTERVAL V_ReceiveDays DAY)< V_Now THEN
				CLOSE order_detail_cursor;
				SET @sys_code = 52;
				SET @sys_message=CONCAT('货品:',V_GoodsName2,',商家编码:',V_SpecNo2,',已经超出最佳收货日期!!!');
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
		SET V_OrgStockinId=0;
		IF V_OrgStockinDetailId THEN
			SELECT stockin_id INTO V_OrgStockinId  FROM stockin_order_detail WHERE rec_id=V_OrgStockinDetailId;
		END IF;
		
		IF V_OrgStockinId=0 THEN
			SET V_OrgStockinId = P_StockinId;
		END IF;

		
		-- -----------------SN-BEGIN---------------------
		-- 货品启用SN码管理  审核时要校验 stockin_detail_sn(stockin_detail_id,spec_id) 对应的记录数量是否等于本次审核的入库数量
		-- 采购入库   入库开单时 stockin_detail_sn表插入记录   审核时 将status =20 
		-- 退货入库   如何将已经使用的SN的状态设置为 启用状态  即 15 =>20   该退货货品的SN是否是出库的SN
			-- stockin_order_detail  -(stockin_id)-> stockin_order  -(src_order_type=3,src_order_id)-> sales_refund  -(trade_id)->  
			-- sales_trade     -(src_trade_id AND src_order_type=1)-> stockout_order  -(stockout_id)-> stockout_order_detail 
			--  -(stockout_id,spec_id)-> stockout_detail_sn  --(sn_id)--> stock_goods_sn
			
			--  P_StockinId , V_SrcOrderType, V_SrcOrderId
			-- 指对强序列号处理
		IF NOT V_SkipSnCheck AND V_IsSnEnable=1 THEN
			SET V_NOT_FOUND=0;
			
			--  调拨入库
			IF V_SrcOrderType=2 THEN
				-- 获得之前的出库单 ID：stockout_id
				SET V_TmpTotalNum =0;
				IF V_NOT_FOUND=0 THEN
					--  循环 校验调拨入库的SN码是否是当时出库的
					OPEN stockout_transfer_detail_sn_cursor;
					DETAIL_SN_LABEL:LOOP
						SET V_NOT_FOUND=0;
						FETCH stockout_transfer_detail_sn_cursor INTO V_SnId1;
						IF V_NOT_FOUND<>0 THEN
							SET V_NOT_FOUND=0;
							LEAVE DETAIL_SN_LABEL;
						END IF;
						
						SELECT COUNT(1) INTO V_TmpCount FROM stockin_detail_sn 
						WHERE sn_id = V_SnId1 AND stockin_detail_id = V_StockinDetailId AND spec_id = V_SpecId;
						SET V_TmpTotalNum = V_TmpTotalNum+V_TmpCount;
						
					END LOOP;
					CLOSE stockout_transfer_detail_sn_cursor;
					IF V_TmpTotalNum<>V_Num THEN
						SET @sys_code = 33;
						SET @sys_message=CONCAT('调拨入库的序列号编码不是出库对应的序列号编码,商家编码:',V_SpecNo2,'货品:',V_GoodsName2);
						LEAVE MAIN_LABEL;
					END IF;
				ELSE
					SET V_NOT_FOUND = 0;
				END IF;
			END IF;
			--  退货入库
			IF V_SrcOrderType=3 THEN
				-- 获得之前的出库单 ID：stockout_id
				
				SELECT  so.stockout_id INTO V_StockoutId
					FROM sales_refund sr 
					INNER JOIN stockout_order so ON (so.src_order_id = sr.trade_id AND so.src_order_type=1)
					WHERE sr.refund_id = V_SrcOrderId;
				SET V_TmpTotalNum =0;
				IF V_NOT_FOUND=0 THEN
					--  循环 校验退回SN码是否是当时出库的
					OPEN stockout_detail_sn_cursor;
					DETAIL_SN_LABEL:LOOP
						SET V_NOT_FOUND=0;
						FETCH stockout_detail_sn_cursor INTO V_SnId1;
						IF V_NOT_FOUND<>0 THEN
							SET V_NOT_FOUND=0;
							LEAVE DETAIL_SN_LABEL;
						END IF;
						
						SELECT COUNT(1) INTO V_TmpCount FROM stockin_detail_sn 
						WHERE sn_id = V_SnId1 AND stockin_detail_id = V_StockinDetailId AND spec_id = V_SpecId;
						SET V_TmpTotalNum = V_TmpTotalNum+V_TmpCount;
						
					END LOOP;
					CLOSE stockout_detail_sn_cursor;

					IF V_TmpTotalNum<>V_Num THEN
						SET @sys_code = 31;
						SET @sys_message=CONCAT('退货入库的序列号编码不是出库对应的序列号编码,商家编码:',V_SpecNo2,'货品:',V_GoodsName2);
						LEAVE MAIN_LABEL;
					END IF;
				ELSE
					SET V_NOT_FOUND = 0;
				END IF;
			END IF;
			
			SELECT COUNT(1) INTO V_CountNum FROM stockin_detail_sn WHERE stockin_detail_id = V_StockinDetailId AND spec_id=V_SpecId;
			IF V_CountNum<>V_Num THEN
				CLOSE order_detail_cursor;
				SET @sys_code=25;
				SET @sys_message=CONCAT('序列号编码记录数量不等于入库数量 商家编码:',V_SpecNo2,'货品:',V_GoodsName2);
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 更新 stockin_detail_sn的status 为启用状态
			UPDATE stock_goods_sn sgs,stockin_detail_sn sds
			SET sgs.status=20,sgs.old_warehouse_id=sgs.warehouse_id,sgs.provider_id=IF(V_SrcOrderType=1,V_ProviderId,sgs.provider_id)
			WHERE sgs.rec_id = sds.sn_id AND sds.stockin_detail_id = V_StockinDetailId AND sds.spec_id = V_SpecId;
			
			-- 写日志
			INSERT stock_goods_sn_log(sn_id,operator_id,event_type,warehouse_id,message)
			SELECT sn_id,@cur_uid,CASE WHEN V_SrcOrderType=1 THEN 1 WHEN V_SrcOrderType=3 THEN 2  WHEN V_SrcOrderType=2 THEN 3 WHEN V_SrcOrderType=6 THEN 9 WHEN V_SrcOrderType=7 THEN 11 WHEN V_SrcOrderType=5 THEN 12 END,V_WarehouseId,
				CONCAT_WS('','入库单号:',V_StockinNO,' 商家编码:',V_SpecNo2,' 货品:',V_GoodsName2)
			FROM stockin_detail_sn WHERE stockin_detail_id = V_StockinDetailId AND spec_id = V_SpecId;
		END IF;
		-- ----------------------SN-END-----------------
		
		SET V_NOT_FOUND = 0;
		SELECT rec_id, stock_num, neg_stockout_num, cost_price, stock_diff, default_position_id
		INTO V_StockSpecId, V_StockNumOld, V_NegStockoutNum, V_CostPriceOld, V_StockDiff, V_DefPositionId
		FROM stock_spec WHERE spec_id=V_SpecId AND warehouse_id=V_WarehouseId FOR UPDATE;
	 
		SET V_CostPrice = V_CostPrice+V_SharePostCost;
		IF V_NOT_FOUND THEN
			SET V_StockNumNew = V_Num;
			SET V_CostPriceNew = V_CostPrice;
			-- 凭证金额
			SET V_GoodsAmountTemp = V_CostPrice*V_Num;
			INSERT INTO tbl_vr_stockin_check(spec_id, goods_amount, adjust_amount)
				VALUES(V_SpecId, V_GoodsAmountTemp, 0);	
			
			-- 插入一条库存记录
			INSERT INTO stock_spec(warehouse_id,spec_id,stock_num,cost_price,last_inout_time,STATUS,default_position_id,last_position_id)
				VALUES(V_WarehouseId,V_SpecId,V_StockNumNew,V_CostPriceNew,V_Now,1,V_PositionId,V_PositionId);
			
			SELECT LAST_INSERT_ID() INTO V_StockSpecId;
			INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,zone_id,zone_no,
				position_id,position_no,spec_id,expire_date,batch_id,batch_no,
				cost_price,stock_num,virtual_num,org_stockin_id,org_stockin_detail_id,last_inout_time,created)
			VALUES( V_StockSpecId,P_StockinId,V_StockinDetailId,V_ZoneId,V_ZoneNO,V_PositionId,V_PositionNO,
				V_SpecId,V_ExpireDate,V_BatchId,V_BatchNO,
				V_CostPriceNew,V_StockNumNew,V_StockNumNew,V_OrgStockinId,V_OrgStockinDetailId,V_Now,V_Now);
			
			INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,stock_num,last_inout_time)
				VALUES(V_WarehouseId,V_SpecId,V_PositionId,V_StockNumNew,NOW());
			
					
		ELSE
			/* 如果存在负库存,则需要把之前出库单赊欠的库存信息给填补上. 
			 	处理的思路:
			 	根据出库单的出库状态把之前负库存出库的货位分配单排序,已经发货的排前面,这样的话如果是入库库存不足以弥补负库存,则直接
			 	把stock_spec_detail里的库存数置为0,is_used_up置为2.入库数量用完了就不用考虑只分配货位而没出库的单子了.

			 	先把入库数据落实到stock_spec_detail 和stock_spec_position表上,再通过出库单分析更改库存.

			 	对于已发货的单子,是直接用光入库数量.对于未发货的则是更新占用库存即可.
			 */

			IF V_NegStockoutNum>0 THEN				


				IF V_NegStockoutNum>=V_Num THEN
					-- 如果负库存量大于 该次入库数量 如：-100*18 + 80*16
					-- 逻辑: 库存初始 -100*18, 本次入库金额 80*16, 入库后库存 (80-100)*18, 调整 80*(16-18) 
					/* 初始:
						借 销售成本	1800
							贷 库存商品	1800
					 本次入库:
						借 库存商品	1280
							贷 应付账款	1280
						借 销售成本	-160
							贷 库存商品	-160 
					*/
					SET V_StockNumNew = V_StockNumOld + V_Num;
					SET V_CostPriceNew = V_CostPriceOld;

					-- 凭证金额
					SET V_GoodsAmountTemp = V_CostPrice*V_Num;
					SET V_CostAdjustTemp = V_Num*(V_CostPrice-V_CostPriceOld);
					INSERT INTO tbl_vr_stockin_check(spec_id, goods_amount, adjust_amount)
						VALUES(V_SpecId, V_GoodsAmountTemp, V_CostAdjustTemp);	

					-- 修改库存和成本价
					UPDATE stock_spec SET stock_num=V_StockNumNew, cost_price=V_CostPriceNew, 
						neg_stockout_num=neg_stockout_num-V_Num, 
						stock_diff=stock_diff+V_CostAdjustTemp, 
						last_position_id=V_PositionId, last_inout_time=NOW(), `status`=1 
					WHERE rec_id=V_StockSpecId;
					
					-- 插入货位和明细表信息

					INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,zone_id,zone_no,
						position_id,position_no,spec_id,expire_date,batch_id,batch_no,cost_price,stock_num,reserve_num,virtual_num,
						org_stockin_id,org_stockin_detail_id,is_used_up,last_inout_time,created)
					VALUES( V_StockSpecId,P_StockinId,V_StockinDetailId,V_ZoneId,V_ZoneNO,V_PositionId,V_PositionNO,
						V_SpecId,V_ExpireDate,V_BatchId,V_BatchNO,V_CostPrice,0,0,V_Num,V_OrgStockinId,V_OrgStockinDetailId,2,NOW(),NOW())
					ON DUPLICATE KEY UPDATE 
						expire_date=VALUES(expire_date),batch_id=VALUES(batch_id),batch_no=VALUES(batch_no),cost_price=VALUES(cost_price),
						stock_num=0,reserve_num=0,is_used_up=2,last_inout_time=NOW();
					
					INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
					VALUES(V_WarehouseId,V_SpecId,0,0,V_Num,NOW(),NOW())
					ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=NOW();

					
				ELSE
					-- 如果负库存量 小于 该次入库数量 如：-100*18(记销售成本) + 160*16(库存商品)
					-- 逻辑: 库存初始 -100*18, 本次入库金额 160*16, 入库后库存 (160-100)*16, 调整 100*(16-18)
					/*初始:
						借: 销售成本	1800
							贷:库存商品	1800
					 本次入库:
						借: 库存商品	2560
							贷:应付账款	2560
						借: 销售成本	-200
							贷:库存商品	-200					
					*/
					SET V_StockNumNew = V_StockNumOld + V_Num;
					SET V_CostPriceNew = V_CostPrice;

					-- 凭证金额
					SET V_GoodsAmountTemp = V_CostPrice*V_Num;
					SET V_CostAdjustTemp = (0-V_StockNumOld)*(V_CostPrice-V_CostPriceOld);
					INSERT INTO tbl_vr_stockin_check(spec_id, goods_amount, adjust_amount)
						VALUES(V_SpecId, V_GoodsAmountTemp, V_CostAdjustTemp);
					
					-- 修改库存和成本价
					UPDATE stock_spec SET stock_num=V_StockNumNew, cost_price=V_CostPriceNew, neg_stockout_num=0, 
						stock_diff=stock_diff+V_CostAdjustTemp,
						last_position_id=V_PositionId, last_inout_time=NOW(), STATUS=1 
					WHERE rec_id=V_StockSpecId;

					-- 插入货位和明细表信息
					INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,zone_id,zone_no,position_id,position_no,
						spec_id,expire_date,batch_id,batch_no,cost_price,stock_num,virtual_num,
						org_stockin_id,org_stockin_detail_id,is_used_up,last_inout_time,created)
					VALUES(V_StockSpecId,P_StockinId,V_StockinDetailId,V_ZoneId,V_ZoneNO,V_PositionId,V_PositionNO,
						V_SpecId,V_ExpireDate,V_BatchId,V_BatchNO,V_CostPrice,V_Num-V_NegStockoutNum,V_Num,
						V_OrgStockinId,V_OrgStockinDetailId,0,NOW(),NOW())
					ON DUPLICATE KEY UPDATE expire_date=VALUES(expire_date),batch_id=VALUES(batch_id),batch_no=VALUES(batch_no),
					cost_price=VALUES(cost_price),stock_num=VALUES(stock_num),virtual_num=VALUES(virtual_num),last_inout_time=NOW(),
					is_used_up=IF(stock_num<=0,2,is_used_up);
					
					UPDATE stock_spec_position SET stock_num=0,last_inout_time=NOW() WHERE warehouse_id=V_WarehouseId AND spec_id=V_SpecId AND position_id=0;
					
					INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
					VALUES(V_WarehouseId,V_SpecId,V_PositionId,0,V_Num-V_NegStockoutNum,NOW(),NOW())
					ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=NOW();
					
				END IF;
			ELSE
			-- 如果不存在负库存 
				IF V_StockNumOld + V_Num > 0 THEN
					-- 正常情况
					SET V_StockNumNew = V_StockNumOld + V_Num;
					SET V_CostPriceNew = (V_StockNumOld*V_CostPriceOld+V_Num*V_CostPrice)/(V_StockNumOld+V_Num);
				ELSE
					-- 之前库存为0，入库数量也是0，特殊处理，否则会除0异常
					SET V_StockNumNew = 0;
					SET V_CostPriceNew = V_CostPrice;
				END IF;
				
				-- 插入凭证明细
				SET V_GoodsAmountTemp = V_CostPrice*V_Num;
				INSERT INTO tbl_vr_stockin_check(spec_id, goods_amount, adjust_amount)
					VALUES(V_SpecId, V_GoodsAmountTemp, 0);
				
				-- 修改库存和成本价
				UPDATE stock_spec SET stock_num=V_StockNumNew, cost_price=V_CostPriceNew, 
					last_inout_time=NOW(), `status`=1, last_position_id=V_PositionId
				WHERE rec_id=V_StockSpecId;
				
				INSERT INTO stock_spec_detail(stock_spec_id,stockin_id,stockin_detail_id,zone_id,zone_no,position_id,position_no,
					spec_id,expire_date,batch_id,batch_no,cost_price,stock_num,virtual_num,org_stockin_id,org_stockin_detail_id,last_inout_time,created)
				VALUES( V_StockSpecId,P_StockinId,V_StockinDetailId,V_ZoneId,V_ZoneNO,V_PositionId,V_PositionNO,
					V_SpecId,V_ExpireDate,V_BatchId,V_BatchNO,V_CostPrice,V_Num,V_Num,V_OrgStockinId,V_OrgStockinDetailId,NOW(),NOW())
				ON DUPLICATE KEY UPDATE expire_date=VALUES(expire_date),batch_id=VALUES(batch_id),batch_no=VALUES(batch_no),
				cost_price=VALUES(cost_price),stock_num=VALUES(stock_num),virtual_num=VALUES(virtual_num),last_inout_time=NOW(),
				is_used_up=IF(stock_num<=0,2,is_used_up);


				
				INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
				VALUES(V_WarehouseId,V_SpecId,V_PositionId,0,V_Num,NOW(),NOW())
				ON DUPLICATE KEY UPDATE stock_num=stock_num+VALUES(stock_num),last_inout_time=NOW();			
				
			END IF;

			IF V_DefPositionId=0 THEN
				UPDATE stock_spec SET default_position_id=V_PositionId WHERE rec_id=V_StockSpecId;
			END IF;
		END IF;
		
		-- 插入库存变化表
		IF V_CostAdjustTemp>0 THEN
			SET V_Remark = CONCAT("负库存引起的成本调整: ", V_CostAdjustTemp);
		END IF;
		
		INSERT INTO stock_change_history(src_order_type, src_order_id, src_order_no, stockio_id, stockio_detail_id, stockio_no, spec_id, warehouse_id, `type`, 
			cost_price_old, stock_num_old, price, num, amount, cost_price_new, stock_num_new, operator_id, remark)
		VALUES(V_SrcOrderType, V_SrcOrderID,  V_SrcOrderNO, P_StockinId, V_StockinDetailId, V_StockinNo, V_SpecId, V_WarehouseId, 1, 
			V_CostPriceOld, V_StockNumOld, V_CostPrice, V_Num, V_CostPrice*V_Num, V_CostPriceNew, V_StockNumNew, @cur_uid, V_Remark);
		
	END LOOP;
	CLOSE order_detail_cursor;
	
	-- step2: 更新库存的修改时间
	CALL I_STOCKIN_CHANGE_API_GOODSSPEC(P_StockinId);
	
	
	-- step3: 业务差异处理
	
	CASE V_SrcOrderType 
		WHEN 1 THEN
			CALL SP_UTILS_GET_CFG('purchase_stockin_not_auto_debtcontacts',@cfg_stockin_auto_debtcontacts,1); -- 默认开启
			IF V_SrcOrderId > 0 THEN	
				--  更新 stock_spec中的采购到货量  
			 
				-- 连表更新一次只能更新一条数据，所以不能所以stock_spec和purchase_order_detail不能同时更新
				UPDATE stock_spec ss, (SELECT spec_id,SUM(num) AS num FROM stockin_order_detail WHERE stockin_id = P_StockinId GROUP BY  spec_id ) sod
					SET ss.purchase_arrive_num = IF(ss.purchase_arrive_num - sod.num < 0,0,ss.purchase_arrive_num - sod.num )
				WHERE ss.spec_id = sod.spec_id AND ss.warehouse_id = V_WarehouseId;
				-- 采购详情单和入库详情单在相同单品的时候也不是一一对应的。

				UPDATE purchase_order_detail pod,(SELECT src_order_detail_id,SUM(num) AS num,SUM(cost_price*num) AS amount FROM stockin_order_detail WHERE stockin_id = P_StockinId GROUP BY src_order_detail_id ) sod
					SET pod.arrive_num = pod.arrive_num - sod.num,
						pod.stockin_num = pod.stockin_num + sod.num,pod.stockin_amount = pod.stockin_amount + sod.amount
				WHERE  sod.src_order_detail_id = pod.rec_id;
				
				IF @cfg_stockin_auto_debtcontacts<>1 THEN -- 开启 采购入库自动生成应收应付单据 
					-- 修改入库单状态为 60待结算
					UPDATE stockin_order SET `status` = 60,check_time=NOW() WHERE stockin_id = P_StockinId;
					-- 更新采购单状态
					SET V_Count = 0;
					SET V_InCount=0;
					-- 判断 采购单 是否 全部到货 
					SELECT SUM(IF(num-stockin_num-arrive_num<0,0,num-stockin_num-arrive_num)) INTO V_Count FROM purchase_order_detail WHERE purchase_id = V_SrcOrderId ;
					IF V_Count=0 THEN 
						-- 判断 采购单 是否 全部入库
						SELECT SUM(IF(num-stockin_num<0,0,num-stockin_num)) INTO  V_InCount FROM purchase_order_detail WHERE purchase_id= V_SrcOrderId ;
						IF V_InCount=0 THEN -- 判断入库单是否有其他结算单据，若有，部分结算，否则 待结算
							SELECT MAX(STATUS) INTO V_ChargeStatus FROM stockin_order WHERE src_order_id=V_SrcOrderId AND src_order_type = 1 ;
					END IF;
					END IF;
					UPDATE purchase_order SET `status`=IF(V_Count>0,50,IF(V_InCount>0,60,IF(V_ChargeStatus>60,80,70))) WHERE purchase_id = V_SrcOrderId;
					
					
				ELSE  --  关闭配置，更新采购单，入库单状态为已完成。
					UPDATE stockin_order SET `status`=80,check_time=NOW() WHERE stockin_id=P_StockinId;
					SET V_Count=0;
					SET V_InCount=0;
					-- 判断 采购单 是否 全部到货 
					SELECT SUM(IF(num-stockin_num-arrive_num<0,0,num-stockin_num-arrive_num)) INTO V_Count FROM purchase_order_detail WHERE purchase_id = V_SrcOrderId ;
					-- 和上面情况不同，若采购单10，入库单3，入库单10.审核入库单10，默认
					-- 采购单状态 待结算。此情况下，没有待结算状态，不能已完成。仍是 已到货，否则入库单3 不能审核。
					IF V_Count=0 THEN  --  判断是否还有其他入库单
						SELECT COUNT(1) INTO V_InCount FROM stockin_order 
							WHERE  src_order_type = 1 AND src_order_id = V_SrcOrderId AND `status` < 80 AND `status` > 10;
					END IF;
					UPDATE purchase_order SET `status`=IF(V_Count>0,50,IF(V_InCount>0,60,90)) WHERE purchase_id = V_SrcOrderId;
					
						
				END IF;
				
				INSERT INTO purchase_order_log(purchase_id,operator_id,`type`,remark)
					VALUES(V_SrcOrderId,@cur_uid,65,CONCAT('采购到货入库,入库单为:',V_StockinNo));
			ELSE
				UPDATE stockin_order SET `status`= 40,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id=P_StockinId;
			END IF;
			
		WHEN 2 THEN
			-- 调拨入库
			IF V_SrcOrderId>0 THEN				
				-- 更新 stock_spec 的调拨在途量
				/*
				UPDATE stock_spec ss, stock_transfer_detail td, stockin_order_detail sod
					SET ss.transfer_num=ss.transfer_num-sod.num
					WHERE td.rec_id=sod.src_order_detail_id AND ss.spec_id=sod.spec_id 
					AND ss.warehouse_id=V_WarehouseId AND sod.src_order_type=2 AND sod.stockin_id=P_StockinId ;	
				*/
				-- 更新调拨在途量
				INSERT INTO stock_spec(spec_id,warehouse_id,transfer_num)
				(
					SELECT sod.spec_id,V_WarehouseId,-sod.num
					FROM stockin_order_detail sod LEFT JOIN stock_transfer_detail td ON (sod.src_order_detail_id = td.rec_id AND sod.src_order_type=2)
					WHERE sod.stockin_id=P_StockinId
				)
				ON DUPLICATE KEY UPDATE transfer_num=transfer_num+VALUES(transfer_num);

				-- 更新调拨单的 入库数量in_num		
				/*
				UPDATE stock_transfer_detail st, stockin_order_detail sod
					SET st.in_num=st.in_num+sod.num 
					WHERE st.rec_id=sod.src_order_detail_id AND sod.stockin_id=P_StockinId;
				*/
				SELECT from_warehouse_id INTO @tmp_from_warehouse FROM stock_transfer WHERE rec_id=V_SrcOrderId;
				INSERT INTO stock_transfer_detail(rec_id,transfer_id,num,from_position,to_position,in_num)
				(
					SELECT src_order_detail_id,V_SrcOrderId,num,@tmp_from_warehouse,position_id,num 
					FROM stockin_order_detail 
					WHERE stockin_id=P_StockinId
				)
				ON DUPLICATE KEY UPDATE in_num=in_num+VALUES(in_num);

				-- 只要存在入库 就有修改对应的待调拨量
			--	UPDATE stock_spec ss,(SELECT spec_id,SUM(out_num) AS right_num,SUM(num) AS left_num FROM stock_transfer_detail 
			--	WHERE transfer_id =V_SrcOrderId GROUP BY spec_id) tl
			--	SET ss.to_transfer_num = IF(ss.to_transfer_num-tl.left_num+IF(tl.right_num>tl.left_num,tl.left_num,tl.right_num)<0,0,ss.to_transfer_num-tl.left_num+IF(tl.right_num>tl.left_num,tl.left_num,tl.right_num))
			--	WHERE ss.spec_id = tl.spec_id AND ss.warehouse_id = @tmp_from_warehouse;
				
				-- 更新调拨单总入库数量
				SELECT SUM(in_num) INTO V_GoodsInCount FROM stock_transfer_detail WHERE transfer_id = V_SrcOrderId;			
				UPDATE stock_transfer SET goods_in_count = V_GoodsInCount WHERE rec_id = V_SrcOrderId;
				
				-- 入库单状态设置为 60待结算
				UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
				-- 分步调拨时要更改调拨单状态，快速调拨时不用更新
				
					-- 检查调拨入库货品数量, 修改调拨单状态, 完全到货->90已完成 部分到货->70部分入库 
					SET V_Count=0;
					SELECT COUNT(*) INTO V_Count FROM stock_transfer_detail WHERE transfer_id=V_SrcOrderId AND out_num>in_num;
					UPDATE stock_transfer SET `status`=IF(V_Count>0,70,90) WHERE rec_id=V_SrcOrderId;
				
				
			END IF;
			
		WHEN 3 THEN
			
			-- 退货入库  1，退货 2 换货 ()
			IF V_SrcOrderId>0 THEN
				-- 更新 sales_refund状态为 已完成？？ 如果存在换货 换货完成后才能更新为完成状态？
				SELECT `type`,swap_trade_id, 1 INTO V_RefundType,V_SwapTradeID,IsTradeCharged FROM sales_refund WHERE refund_id = V_SrcOrderId;
				-- IF V_RefundType=2 THEN
					-- call SP_ASSERT(0,concat('销售退货单类型',V_RefundType));
					-- 退货  更新 stock_spec 的refund_num
					/*
					UPDATE stock_spec ss,sales_refund_order srd,stockin_order_detail sod
					SET ss.refund_num=IF(srd.stockin_num + sod.num > srd.refund_num,
						 IF(srd.stockin_num > srd.refund_num,ss.refund_num-0,ss.refund_num-srd.refund_num+srd.stockin_num),ss.refund_num - sod.num)
					WHERE srd.order_id=sod.src_order_detail_id AND ss.spec_id=sod.spec_id
					AND ss.warehouse_id=V_WarehouseId AND sod.src_order_type=3 AND sod.stockin_id=P_StockinId ;
					*/
					
					UPDATE stock_spec ss,sales_refund_order srd,stockin_order_detail sod,sales_refund sr
					SET ss.refund_onway_num=IF(srd.stockin_num+sod.num > srd.refund_num,
						 IF(srd.stockin_num > srd.refund_num,ss.refund_onway_num-0,ss.refund_onway_num-srd.refund_num+srd.stockin_num),ss.refund_onway_num - sod.num)
					WHERE srd.refund_order_id = sod.src_order_detail_id AND ss.spec_id = sod.spec_id
					AND ss.warehouse_id = V_WarehouseId AND sr.refund_id = srd.refund_id AND sr.return_mask&8 > 0 AND sod.src_order_type=3 AND sod.stockin_id=P_StockinId ;
					
					--  sales_refund_order 更新退货入库数量 stockin_num
					UPDATE sales_refund_order sro,stockin_order_detail sod
					SET sro.stockin_num=sro.stockin_num+sod.num,sro.stockin_amount = sro.stockin_amount + sod.num * sod.cost_price
					WHERE sod.src_order_detail_id = sro.refund_order_id AND sod.stockin_id=P_StockinId;
					
					-- 更新 sales_refund 状态  70 部分到货 90 已完成
					SET V_Count=0;
					SELECT SUM(IF(refund_num-stockin_num<0,0,refund_num-stockin_num)),trade_id INTO V_Count,V_TradeID FROM sales_refund_order WHERE refund_id=V_SrcOrderId;
					IF V_Count>0 THEN
						-- 部分到货
						IF V_RefundStatus = 69 OR V_RefundStatus = 71 THEN
							UPDATE sales_refund SET process_status=71,version_id=version_id+1 WHERE refund_id=V_SrcOrderId;
						ELSE
							UPDATE sales_refund SET process_status=70,version_id=version_id+1 WHERE refund_id=V_SrcOrderId;
						END IF;
					ELSE
						-- 完全到货
						IF V_RefundStatus = 69 OR V_RefundStatus = 71 THEN
							UPDATE sales_refund SET process_status=90,version_id=version_id+1 WHERE refund_id=V_SrcOrderId;
						ELSE
							UPDATE sales_refund SET process_status=80,version_id=version_id+1 WHERE refund_id=V_SrcOrderId;
						END IF;
					END IF;
					
					-- 日志
					INSERT INTO sales_refund_log(refund_id,`type`,operator_id,remark)
					VALUES(V_SrcOrderId,10,@cur_uid,CONCAT('退货入库,',IF(V_Count>0,'部分到货','完全到货')));
					
					
				-- ELSEIF V_RefundType=3 THEN
						-- CALL SP_ASSERT(0,CONCAT('销售退货单类型',V_RefundType));
					-- 换货入库  更新 stock_spec 的 refund_exch_num（换回数量） 
					/*
					UPDATE stock_spec ss,sales_refund_order srd,stockin_order_detail sod
					SET ss.refund_onway_num=IF(srd.stockin_num + sod.num > srd.refund_num,
						 IF(srd.stockin_num > srd.refund_num,ss.refund_onway_num-0,ss.refund_onway_num-srd.refund_num+srd.stockin_num),ss.refund_onway_num - sod.num)
					WHERE srd.order_id = sod.src_order_detail_id AND ss.spec_id = sod.spec_id
					AND ss.warehouse_id = V_WarehouseId AND sod.src_order_type=3 AND sod.stockin_id=P_StockinId ;
					*/
					-- sales_refund_order 更新退货入库数量 stockin_num
					/*
					UPDATE sales_refund_order sro,stockin_order_detail sod
					SET sro.stockin_num=sro.stockin_num+sod.num
					WHERE sod.src_order_detail_id = sro.refund_order_id AND sod.stockin_id = P_StockinId;
					
					--  状态更新  完成=（换回货量入库量>= refund_num ）
					SET V_Count=0;
					SELECT SUM(IF(refund_num-stockin_num<0,0,refund_num-stockin_num)) INTO V_Count FROM sales_refund_order WHERE refund_id=V_SrcOrderId;
					UPDATE sales_refund SET process_status =IF(V_Count>0,70,80) WHERE refund_id=V_SrcOrderId;
					SELECT process_status INTO V_TmpProcessStatus FROM sales_refund WHERE refund_id = V_SrcOrderId;
					IF V_TmpProcessStatus = 80 THEN
						-- 生产销售预订单 todo:
						 CALL I_SALES_REFUND_NEW(V_SrcOrderId);
						IF @sys_code<>0 THEN
							SET @sys_code=10;
							SET @sys_message='售后新建换货订单失败';
							LEAVE MAIN_LABEL;
						END IF;	
					END IF;
					
				END IF;
				*/
				-- 销售退货入库单的状态改为已完成(不对入库单结算了, 对整个退换单结算)
				UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
				
				IF V_RefundType=3  AND V_SwapTradeID=0 AND IsTradeCharged=0 THEN
					-- 生成新的订单SET V_I=0;
					CALL I_SALES_REFUND_NEW(V_SrcOrderId);
				END IF;
				
				-- 退货入库,短信通知
				
				-- 退换入库查看是否需要回传备注到平台的配置
				CALL SP_UTILS_GET_CFG('sales_refund_upload',@cfg_sales_refund_upload,0);
				IF @cfg_sales_refund_upload THEN
					CALL SP_UTILS_GET_CFG('sales_refund_upload_flag_remark',@cfg_sales_refund_upload_flag_remark,0);
				ELSE
					SET @cfg_sales_refund_upload_flag_remark = -1;
				END IF;
				CALL SP_UTILS_GET_CFG('sales_refund_upload_remark',@cfg_sales_refund_upload_remark,0);
				IF @cfg_sales_refund_upload_remark THEN
					CALL SP_UTILS_GET_CFG2('sales_refund_upload_remark_content',@cfg_sales_refund_upload_remark_content,NULL);
				END IF;
				-- 销售退换单需要回写备注到平台
				IF @cfg_sales_refund_upload OR @cfg_sales_refund_upload_remark THEN
					CALL SP_UTILS_GET_CFG('smart_stockin_remark_upload',@cfg_smart_stockin_remark_upload,0);
					IF @cfg_smart_stockin_remark_upload = 0 THEN
						SELECT GROUP_CONCAT(CONCAT('【',spec_no,'*',TRUNCATE(num,0),'】')) INTO V_SpecUpLoad 
						FROM stockin_order_detail sod,goods_spec gs ,goods_goods gg
						WHERE sod.stockin_id = P_StockinId AND sod.spec_id = gs.spec_id AND gs.goods_id = gg.goods_id;
					ELSE
						SELECT GROUP_CONCAT(CONCAT('【',short_name,'*',TRUNCATE(num,0),'】')) INTO V_SpecUpLoad 
						FROM stockin_order_detail sod,goods_spec gs ,goods_goods gg
						WHERE sod.stockin_id = P_StockinId AND sod.spec_id = gs.spec_id AND gs.goods_id = gg.goods_id;
					END IF; 
					UPDATE sales_refund SET remark = LEFT(
						CONCAT_WS(' ',remark,CONCAT(MONTH(NOW()),'/',DAY(NOW())),
								IF(@cfg_sales_refund_upload_remark,CONCAT('追加备注:',@cfg_sales_refund_upload_remark_content),NULL),
								V_SpecUpLoad,@cur_uname),1024)
					WHERE refund_id = V_SrcOrderID;
					
					INSERT INTO sales_refund_log(refund_id,`type`,operator_id,remark) 
					VALUES(V_SrcOrderID,64,@cur_uid,
						LEFT(CONCAT_WS(' ',
						CONCAT(MONTH(NOW()),'/',DAY(NOW())),
						IF(@cfg_sales_refund_upload_remark,CONCAT('追加备注:',@cfg_sales_refund_upload_remark_content),NULL),
						V_SpecUpLoad,@cur_uname),1024));
											
					INSERT INTO api_trade_upload_remark(src_order_type,src_order_id,platform_id,tid,shop_id,cs_remark,remark_flag)
						SELECT DISTINCT 1,V_SrcOrderID,sr.platform_id,sro.tid,apt.shop_id,sr.remark,IF(@smart_flag_remark,@smart_flag_remark,@cfg_sales_refund_upload_flag_remark)
						FROM sales_refund sr,sales_refund_order sro,api_trade apt 
						WHERE sr.refund_id = sro.refund_id AND sr.refund_id = V_SrcOrderID AND sro.platform_id >0 AND apt.tid = sro.tid;
					SET @smart_flag_remark = NULL;
				END IF;
			END IF;
			
		WHEN 4 THEN
			-- 盘点入库
			IF V_SrcOrderId>0 THEN
				-- 更新上次盘点时间
				-- 库存表
				UPDATE stock_spec ss, stockin_order_detail sod 
					SET ss.last_pd_time=V_Now
					WHERE ss.spec_id=sod.spec_id 
					AND ss.warehouse_id=V_WarehouseId AND sod.src_order_type=4 AND sod.stockin_id=P_StockinId;
				-- 货位库存表
				UPDATE stock_spec_position ssp, stockin_order_detail sod
					SET ssp.last_pd_time=V_Now
					WHERE sod.spec_id=ssp.spec_id AND ssp.position_id=sod.position_id AND ssp.warehouse_id=V_WarehouseId
					AND sod.stockin_id=P_StockinId;
				-- 库存明细表
				UPDATE stock_spec_detail ssd, stockin_order_detail sod
					SET ssd.last_pd_time=V_Now
					WHERE sod.rec_id=ssd.stockin_detail_id AND sod.stockin_id=P_StockinId;
				
				-- 更新盘点入库单状态为已完成
				UPDATE stockin_order SET `status`=80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
			END IF;
		
		WHEN 5 THEN
			-- 生产入库
			IF V_SrcOrderId>0 THEN
				-- 更新其它入库单状态为待结算
				
				UPDATE stockin_order SET `status` = 60,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id=P_StockinId;
				SELECT sw.is_defect INTO V_IsDefect FROM cfg_warehouse sw WHERE sw.warehouse_id=V_WarehouseId; 
				UPDATE stock_transfer_detail st, stockin_order_detail sod
						SET st.in_num=st.in_num+sod.num 
						WHERE st.rec_id=sod.src_order_detail_id AND sod.stockin_id=P_StockinId;
				IF V_IsDefect THEN
					UPDATE goods_process_detail gpd,stockin_order_detail sod
						SET bad_in_num=bad_in_num+sod.num WHERE gpd.rec_id=sod.src_order_detail_id AND sod.stockin_id=P_StockinId ;
				ELSE
					UPDATE goods_process_detail gpd,stockin_order_detail sod
						SET gpd.in_num=gpd.in_num+sod.num WHERE gpd.rec_id=sod.src_order_detail_id AND sod.stockin_id=P_StockinId ;
					UPDATE goods_process gp SET in_warehouse_id = V_WarehouseId WHERE gp.process_id = V_SrcOrderId;
				END IF;
				
				-- 更新生产单状态为待结算
				SELECT SUM(IF(process_num+bad_num+in_num<gp.process_count*gpd.num,1,0))  INTO V_ProcessMaterialFinish 
					FROM goods_process_detail gpd LEFT JOIN goods_process gp ON gp.process_id=gpd.process_id WHERE is_product=0 AND gpd.process_id=V_SrcOrderId GROUP BY gpd.process_id;
				SELECT SUM(IF(process_num>in_num,1,0))  INTO V_ProcessProductFinish FROM goods_process_detail WHERE is_product=1 AND process_id=V_SrcOrderId GROUP BY process_id;
				SELECT SUM(IF(bad_num>bad_in_num,1,0))  INTO V_ProcessBadProductFinish FROM goods_process_detail WHERE process_id=V_SrcOrderId GROUP BY process_id;
				IF V_ProcessMaterialFinish=0 AND V_ProcessProductFinish=0 AND V_ProcessBadProductFinish=0 THEN
					UPDATE goods_process SET `status` = 60 WHERE process_id=V_SrcOrderId;
				ELSE 
					UPDATE goods_process SET `status` = 57 WHERE process_id=V_SrcOrderId;
				END IF;
				-- 日志
				INSERT INTO goods_process_log(process_id,operator_id,remark)
					VALUES(V_SrcOrderId,@cur_uid,CONCAT('生产入库，入库单为:',V_StockinNo));
			END IF;
		
	
		
		WHEN 7 THEN
			-- 7 保修入库
			-- 更新 保修入库单状态为 待结算 更新入库单字段、入库人字段、入库时间字段
			UPDATE stockin_order SET `status` = 60,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
			SELECT from_goods_count,from_spec_id INTO V_SalesRepairNum,V_RepairSpecId FROM sales_repair WHERE repair_id = V_SrcOrderId ;
			SELECT num INTO V_SalesRepairStockinNum  FROM stockin_order_detail  WHERE spec_id= V_RepairSpecId AND stockin_id = P_StockinId ;
			
			IF V_SalesRepairStockinNum <> 0 THEN
				UPDATE sales_repair SET repair_status = IF(V_SalesRepairNum - V_SalesRepairStockinNum - arrival_goods_count > 0,35,40),arrival_goods_count = V_SalesRepairStockinNum+arrival_goods_count,stockin_id = P_StockinId WHERE repair_id = V_SrcOrderId ;
			END IF;
			INSERT INTO sales_repair_log(repair_id,operator_id,detail,created) VALUES(V_SrcOrderId,@cur_uid,CONCAT('保修货品入库 入库单号 ',V_StockinNo),NOW());
			-- 保修入库,短信通知
			IF V_SrcOrderId>0 && @cfg_open_message_strategy THEN
				CALL I_CRM_SMS_RECORD_INSERT_TRIGGER(6,V_SrcOrderId,@cur_uid);
			END IF;
		WHEN 8 THEN
			UPDATE stockin_order SET `status` = 80,check_time=NOW() WHERE stockin_id = P_StockinId;
		WHEN 9 THEN
			UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
		WHEN 10 THEN
			UPDATE stockin_pre_order SET b_stockin = 1 WHERE stockin_pre_id = V_SrcOrderID;
			UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
		WHEN 11 THEN
			-- jit退货入库 
			IF V_SrcOrderId>0 THEN
				--  jit_refund_detail 更新退货入库数量 stockin_num
				UPDATE jit_refund_detail jrd,stockin_order_detail sod
				SET jrd.stockin_num=jrd.stockin_num+sod.num
				WHERE sod.src_order_detail_id = jrd.rec_id AND sod.stockin_id=P_StockinId;
				
				-- 更新 jit_refund 状态  40 部分入库 50 全部入库
				SET V_Count=0;
				
				SELECT  vph_refund_no INTO V_VphRefundNo FROM jit_refund WHERE rec_id=V_SrcOrderId;
				
				SELECT SUM(IF(num-stockin_num<0,0,num-stockin_num)) INTO  V_Count FROM jit_refund_detail WHERE vph_refund_no =V_VphRefundNo ;
				
				UPDATE jit_refund SET `status` = IF(V_Count>0,40,50) WHERE vph_refund_no = V_VphRefundNo;
								
				-- 日志
				INSERT INTO jit_po_log(order_id,`type`,operater_id,message)
				VALUES(V_SrcOrderId,2,@cur_uid,CONCAT('jit退货入库,',IF(V_Count>0,'部分到货','完全到货')));
				
				-- 账款明细
				INSERT INTO fa_jit_check (po_no,refund_count,refund_amount)
					SELECT po.po_no,sid.num,sid.num*jrd.price
					FROM stockin_order_detail sid
					LEFT JOIN jit_refund_detail jrd ON sid.src_order_detail_id= jrd.rec_id 
					LEFT JOIN jit_po po ON po.po_no = jrd.po_no
					WHERE sid.stockin_id = P_StockinId
				ON DUPLICATE KEY UPDATE fa_jit_check.refund_count = fa_jit_check.`refund_count`+VALUES(refund_count),
				fa_jit_check.refund_amount=fa_jit_check.`refund_amount`+VALUES(refund_amount);
				
				-- 退货入库单的状态改为已完成(不对入库单结算了, 对整个退换单结算)
				UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
			
			END IF;
			
		WHEN 12 THEN
			-- 委外入库 
			IF V_SrcOrderId>0 THEN
				--  outside_wms_order_detail 更新委外入库数量 inout_num
				UPDATE outside_wms_order_detail owd,stockin_order_detail sod
				SET owd.inout_num=owd.inout_num+sod.num
				WHERE sod.src_order_detail_id = owd.rec_id AND sod.stockin_id=P_StockinId AND owd.order_id = V_SrcOrderId;
				
				-- 更新 outside_wms 状态  75 部分入库 80 已完成
				SET V_Count=0;
								
				SELECT SUM(IF(num-inout_num<0,0,num-inout_num)) INTO  V_Count FROM outside_wms_order_detail WHERE order_id =V_SrcOrderId ;
				
				UPDATE outside_wms_order SET `status` = IF(V_Count>0,75,80) WHERE  order_id =V_SrcOrderId ;
								
				-- 日志
				INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message)
				VALUES(V_SrcOrderId,@cur_uid,9,CONCAT('委外入库,',IF(V_Count>0,'部分入库','完全入库')));
				
				-- 账款明细
				
				-- 委外入库单的状态改为已完成(
				UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
			
			END IF;
		ELSE
			-- 6 其它入库
			-- 更新其它入库单状态为待结算
			UPDATE stockin_order SET `status` = 80,check_time=NOW(),check_operator_id = @cur_uid WHERE stockin_id = P_StockinId;
	END CASE;
	
	-- step4: 记录凭证
	
	-- step5: 日志	 
	INSERT stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(1,P_StockinId,@cur_uid,16,CONCAT('审核入库单: ',V_StockinNo,' ;'));

	-- step6: 是否开启自动结算 1，采购
	

END//
DELIMITER ;
