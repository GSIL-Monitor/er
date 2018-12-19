
DROP PROCEDURE IF EXISTS `I_STOCKOUT_OUT`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_OUT`(IN `P_StockoutId` INT , IN `P_IsForce` INT, IN `P_ToStatus` INT , IN `P_IsSales` INT, IN `P_FreeAllocated` INT)
    SQL SECURITY INVOKER
    COMMENT '出库操作，扣减库存'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_SrcOrderType,V_SrcOrderId,V_WarehouseId,V_Status,V_ConsignStatus,V_WarehouseType,
		V_CheckouterId,V_FreezeReason,V_IsAllocated,V_BlockReason,V_PosAllocateMode,V_IsNewCash INT;
	DECLARE V_SrcOrderNo,V_WarehouseName,V_StockoutNo,V_SpecNo VARCHAR(40);
	DECLARE V_GoodsName,V_SpecName VARCHAR(255);
	DECLARE V_SpecId,V_SkipSnCheck,V_OrderMask,V_CustomType INT DEFAULT(0);
	DECLARE V_Num,V_StockNum,V_GoodsCost,V_PackageCost,V_UnknownGoodsAmount,V_TotalNum,V_CountNum DECIMAL(19,4);
	
	-- SN货品 
	DECLARE order_detail_cursor CURSOR FOR
		SELECT sod.spec_id,SUM(sod.num),gs.spec_no,sod.goods_name
		FROM stockout_order_detail sod
			LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id
		WHERE sod.stockout_id = P_StockoutId AND IF(V_SrcOrderType=1,IF(V_IsNewCash=4,gs.is_sn_enable=1,gs.is_sn_enable>0),gs.is_sn_enable=1)
		GROUP BY sod.spec_id;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET @sys_code=0;
	SET @sys_message='OK';
	
	IF P_ToStatus<55 THEN
		SET @sys_code=99;
		SET @sys_message='出库单状态错误';
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT src_order_type,src_order_id,src_order_no,warehouse_id,`status`,consign_status,freeze_reason,block_reason,1 as is_allocated, pos_allocate_mode,stockout_no,warehouse_type,0
	INTO V_SrcOrderType,V_SrcOrderId,V_SrcOrderNo,V_WarehouseId,V_Status,V_ConsignStatus,V_FreezeReason,V_BlockReason,V_IsAllocated, V_PosAllocateMode,V_StockoutNo,V_WarehouseType,V_CustomType
	FROM stockout_order WHERE stockout_id=P_StockoutId FOR UPDATE;
	
	-- 首先  出库单的校验
	
	IF V_NOT_FOUND<>0 THEN
		SET @sys_code=1;
		SET @sys_message='出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF (V_ConsignStatus & 4)  THEN
		SET @sys_code=3;
		SET @sys_message='出库单已出库';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status=5 THEN
		SET @sys_code=3;
		SET @sys_message='出库单已取消';
		LEAVE MAIN_LABEL;
	END IF;
	
	/*其它出库50是待审核*/
	IF V_SrcOrderType=1 THEN
		IF V_Status<55 OR V_Status>=95 THEN
			SET @sys_code=3;
			SET @sys_message='出库单状态不正确';
			LEAVE MAIN_LABEL;
		END IF;
	
		/*针对现款销售做下处理*/
		
		IF P_IsSales =0 THEN
			SET V_IsNewCash = 0;
			SELECT trade_from INTO V_IsNewCash FROM sales_trade where trade_id = V_SrcOrderId;
		END IF;
	ELSE
		IF V_Status<>50 AND V_Status<>55 THEN
			SET @sys_code=3;
			SET @sys_message='出库单状态不正确';
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	
	IF V_WarehouseId<=0 THEN
		SET @sys_code=4;
		SET @sys_message='未指定出库仓库';
		LEAVE MAIN_LABEL;
	END IF;
/*	
	IF @cur_uid>0 AND NOT EXISTS(SELECT 1 FROM cfg_employee_warehouse WHERE warehouse_id = V_WarehouseId AND employee_id = @cur_uid AND is_denied=0) THEN
		SET @sys_code=20;
		SET @sys_message='没有该出库仓库的权限';
		LEAVE MAIN_LABEL;
	END IF;
	*/
	IF V_WarehouseType=1 THEN
		IF V_FreezeReason<>0 THEN
			SET @sys_code=5;
			SET @sys_message='订单已经冻结';
			LEAVE MAIN_LABEL;
		END IF;
		
		IF V_BlockReason<>0 THEN
			SET @sys_code=6;
			SET @sys_message=CONCAT('阻止出库:',FN_BLOCK_REASON(V_BlockReason));
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	
	-- 判断是否要检查SN
	-- 调拨出库,同一仓库不需要检查sn
	IF V_SrcOrderType=2 AND
		(SELECT from_warehouse_id=to_warehouse_id FROM stock_transfer WHERE rec_id=V_SrcOrderId) THEN
		SET V_SkipSnCheck=1;
	END IF;
	
	IF NOT V_SkipSnCheck THEN
		OPEN order_detail_cursor;
		DETAIL_LABEL:LOOP
			SET V_NOT_FOUND=0;
			FETCH order_detail_cursor INTO V_SpecId,V_TotalNum,V_SpecNo,V_GoodsName;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE DETAIL_LABEL;
			END IF;
			
			-- 如果或启用了SN -- 修改stock_goods_sn对应的状态 并记录日志 基本和驳回保持一致  如果仅仅判断数量是否相等 驳回会产生影响
			SELECT COUNT(1) INTO V_CountNum FROM stockout_detail_sn WHERE spec_id = V_SpecId AND stockout_id=P_StockoutId;
			IF V_CountNum<>V_TotalNum THEN
				CLOSE order_detail_cursor;
				SET @sys_code=8;
				SET @sys_message=CONCAT('货品启用序列号管理,序列号数量不等于出库数量,货品:',V_GoodsName,' 商家编码:',V_SpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			
			UPDATE stock_goods_sn sgs,stockout_detail_sn sds
			SET sgs.status=40 
			WHERE sgs.rec_id = sds.sn_id AND sds.spec_id = V_SpecId AND sds.stockout_id = P_StockoutId;
			
			INSERT stock_goods_sn_log(sn_id,operator_id,event_type,warehouse_id,message)
			SELECT sn_id,@cur_uid,CASE V_SrcOrderType WHEN 1 THEN 5 WHEN 2 THEN  4 WHEN 3 THEN 7 WHEN 5 THEN 13 WHEN 7 THEN 10 END,V_WarehouseId,
				CONCAT_WS('',CONCAT(' 出库单号:',V_StockoutNo),' 货品:',V_GoodsName,' 商家编码:',V_SpecNo)
			FROM stockout_detail_sn WHERE spec_id=V_SpecId AND stockout_id = P_StockoutId;
			
		END LOOP;
		CLOSE order_detail_cursor;
	END IF;
	
	-- 整数配置，出库开单界面可以录入小数，为防止货位分配有问题，故提示。销售出库暂不提示，后续观察。
	IF V_SrcOrderType > 1 THEN
		SET V_NOT_FOUND = 0;
		IF @cfg_gbl_goods_int_count IS NULL THEN
			CALL SP_UTILS_GET_CFG('gbl_goods_int_count', @cfg_gbl_goods_int_count, 1);
		END IF;
		IF @cfg_gbl_goods_int_count THEN
			SELECT gs.spec_no INTO V_SpecNo FROM stockout_order_detail sod
			LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id 
			WHERE sod.stockout_id=P_StockoutId AND num%1>0 LIMIT 1;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND = 0;
			ELSE
				SET @sys_code=9;
				SET @sys_message=CONCAT('整数配置,出库单开单数量存在小数,请修改出库数量,商家编码：',V_SpecNo);
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
	END IF;
	
	--  扣减库存数量 
	IF V_IsAllocated=0 THEN   
		IF V_SrcOrderType = 7 OR V_SrcOrderType=3 THEN		
			CALL I_DISTRIBUTION_POSITION_BATCH(P_StockoutId,V_WarehouseId,P_FreeAllocated);
		ELSE
			CALL I_DISTRIBUTION_POSITION(P_StockoutId,V_WarehouseId, V_PosAllocateMode, P_IsForce, P_FreeAllocated);
		END IF;
		IF @sys_code THEN
			LEAVE MAIN_LABEL;
		END IF;
	END IF;
	
	
	--  出入库记录
	-- 确定出库成本价
	-- 对于调拨,成本价转移到入库方
	UPDATE stockout_order_detail sod,stock_spec ss
	SET sod.cost_price=ss.cost_price
	WHERE sod.stockout_id=P_StockoutId AND ss.spec_id=sod.spec_id AND ss.warehouse_id=V_WarehouseId;
	
	/*
	-- 估算重量
	-- !!!这里看能否去掉,按理早算出来了
	UPDATE stockout_order_detail sod,goods_spec gs
	SET sod.weight=gs.weight*sod.num
	WHERE sod.stockout_id=P_StockoutId AND gs.spec_id=sod.spec_id;
	*/
	-- 未知货位的特殊处理 销售出库??
	-- 假设负库存出库的概率很低
	IF V_SrcOrderType=1 AND EXISTS(SELECT 1 FROM stockout_order_detail sod,stockout_order_detail_position sodp
		WHERE sod.stockout_id = P_StockoutId AND sodp.stockout_order_detail_id = sod.rec_id AND sodp.stock_spec_detail_id=0) THEN
		
		IF EXISTS(SELECT 1 FROM stockout_order_detail sod,stockout_order_detail_position sodp,stock_spec ss
			WHERE sod.stockout_id = P_StockoutId AND sodp.stockout_order_detail_id = sod.rec_id AND ss.spec_id=sod.spec_id 
			AND ss.warehouse_id = V_WarehouseId AND sodp.stock_spec_detail_id=0 AND ss.stock_num>0) THEN
			
			CALL I_DISTRIBUTION_UNKNOWN_POSITION(P_StockoutId);
			IF @sys_code THEN
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
	END IF;
	
	-- 计算负库存
	/*
	UPDATE stock_spec ss,stockout_order_detail_position sodp,stockout_order_detail sod 
	SET ss.neg_stockout_num=ss.neg_stockout_num+sodp.num 
	WHERE sod.stockout_id=P_StockoutId AND 
		ss.warehouse_id=V_WarehouseId AND ss.spec_id=sod.spec_id and 
		sodp.stockout_order_detail_id=sod.rec_id AND sodp.stock_spec_detail_id=0;
	*/
	IF EXISTS(SELECT 1 FROM stockout_order_detail_position sodp,stockout_order_detail sod WHERE sod.stockout_id = P_StockoutId AND sodp.stockout_order_detail_id = sod.rec_id AND sodp.stock_spec_detail_id=0) THEN
		UPDATE stock_spec ss,
		(
				SELECT sod.spec_id,SUM(sodp.num) num
				FROM stockout_order_detail_position sodp,stockout_order_detail sod
				WHERE sod.stockout_id = P_StockoutId AND sodp.stockout_order_detail_id = sod.rec_id 
					AND sodp.stock_spec_detail_id=0
				GROUP BY sod.spec_id
		) tmp
		SET ss.neg_stockout_num=ss.neg_stockout_num+tmp.num,ss.`status`=1,ss.last_inout_time=NOW()
		WHERE ss.warehouse_id=V_WarehouseId AND ss.spec_id=tmp.spec_id;
	END IF;
	
	-- 扣减库存
	/*
	UPDATE stock_spec ss, stockout_order_detail sod
	SET ss.stock_num=ss.stock_num-sod.num,ss.sending_num=if(P_IsSales,ss.sending_num-sod.num,ss.sending_num),ss.last_inout_time=now()
	WHERE ss.spec_id=sod.spec_id AND ss.warehouse_id=V_WarehouseId  AND sod.stockout_id=P_StockoutId;
	*/
	
	IF P_IsSales  THEN
		UPDATE stock_spec ss,
		(
			SELECT sod.spec_id,SUM(sod.num) num,SUM(IF(sod.is_package=0,sto.actual_num,0)) goods_num
			FROM stockout_order_detail sod LEFT JOIN sales_trade_order sto ON (sto.rec_id = sod.src_order_detail_id)
			WHERE sod.stockout_id=P_StockoutId AND sod.src_order_type = 1
			GROUP BY sod.spec_id
		) tmp
		SET ss.stock_num=ss.stock_num-tmp.num,
			ss.sending_num=ss.sending_num-tmp.goods_num,
			ss.today_num=IF(DATE(ss.last_sales_time)=CURRENT_DATE(),ss.today_num+tmp.num,tmp.num),
			ss.last_sales_time=CURRENT_DATE(),
			ss.`status`=1,ss.last_inout_time=NOW()
		WHERE ss.warehouse_id=V_WarehouseId AND ss.spec_id=tmp.spec_id;
	ELSE
		UPDATE stock_spec ss,
		(
			SELECT spec_id,SUM(num) num
			FROM stockout_order_detail 
			WHERE stockout_id=P_StockoutId
			GROUP BY spec_id
		) tmp
		SET ss.stock_num=ss.stock_num-tmp.num,
			ss.today_num=IF(V_IsNewCash = 4,IF(DATE(ss.last_sales_time)=CURRENT_DATE(),ss.today_num+tmp.num,tmp.num),ss.today_num),
			ss.last_sales_time=IF(V_IsNewCash = 4,CURRENT_DATE(),ss.last_sales_time),
			ss.`status`=1,ss.last_inout_time=NOW()
		WHERE ss.warehouse_id=V_WarehouseId AND ss.spec_id=tmp.spec_id;
	END IF;
	INSERT INTO stock_spec_log(operator_id,stock_spec_id,operator_type,num,stock_num,message) 
		select @cur_uid as operator_id,ss.rec_id as stock_spec_id,3 as operator_type,sod.num,ss.stock_num+sod.num as stock_num,
		CONCAT(if(so.src_order_type = 1,'销售出库-',if(so.src_order_type = 2,'调拨出库-',if(so.src_order_type = 3,'采购退货出库-',if(so.src_order_type = 4,'盘亏出库-','其他出库')))),so.stockout_no) as message from stockout_order so left join stockout_order_detail sod on sod.stockout_id = so.stockout_id left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id = so.warehouse_id where so.stockout_id = P_StockoutId;
	-- 正常扣减 减掉 stock_spec_detail 中的占用量 reserve_num
	-- 注意，这里因为用update时is_used_up会不准，所以改成insert into on duplicate key 的形式。
	SET @tmp_stock_spec_id=UNIX_TIMESTAMP();
	INSERT INTO stock_spec_detail(rec_id,stock_num,reserve_num,stockin_detail_id,stock_spec_id,position_id,last_inout_time,created)
	(
		SELECT sodp.stock_spec_detail_id,-sodp.num,-sodp.num,@tmp_stock_spec_id,@tmp_stock_spec_id,@tmp_stock_spec_id,NOW(),NOW()
		FROM stockout_order_detail sod,stockout_order_detail_position sodp
		WHERE sod.stockout_id=P_StockoutId AND sod.rec_id=sodp.stockout_order_detail_id AND sodp.stock_spec_detail_id>0
	)
	ON DUPLICATE KEY UPDATE 
		stock_num=stock_spec_detail.stock_num+VALUES(stock_num),
		reserve_num=stock_spec_detail.reserve_num+VALUES(reserve_num),
		is_used_up=IF(stock_spec_detail.stock_num=0,2,stock_spec_detail.is_used_up),
		last_inout_time=NOW();
	
	
	-- stock_spec_position 对应货位数量扣减
	INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
	(
		SELECT V_WarehouseId,sod.spec_id,sodp.position_id,IFNULL(cwp.zone_id,0),-sodp.num,NOW(),NOW()
		FROM stockout_order_detail_position sodp LEFT JOIN stockout_order_detail sod ON sod.rec_id=sodp.stockout_order_detail_id
		LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=sodp.position_id
		WHERE sod.stockout_id=P_StockoutId
	)
	ON DUPLICATE KEY UPDATE stock_spec_position.stock_num=stock_spec_position.stock_num+VALUES(stock_spec_position.stock_num),last_inout_time=NOW();
	-- 这里要对现款销售进行单独的判断  因为之前现款销售的src_order_type是6 后来改动后  现款销售的类型和线上销售的类型都是1了 但是库存台账需要看到现款销售
	-- 加order_mask掩码字段来区分
	IF V_SrcOrderType=1 THEN
		SELECT IF(trade_type=2,1,0)|IF(trade_type=3,2,0)|IF(trade_from=4,4,0)|IF(delivery_term=2,8,0) 
		INTO V_OrderMask FROM sales_trade WHERE trade_id=V_SrcOrderID;
	END IF;
	-- 记录库存变化
	INSERT INTO stock_change_history(src_order_type, src_order_id, src_order_no, stockio_id, stockio_detail_id, stockio_no, spec_id, warehouse_id, `type`, 
		cost_price_old, stock_num_old, price, num, amount, cost_price_new, stock_num_new, operator_id, order_mask, remark)
	SELECT V_SrcOrderType, IF(V_SrcOrderType<>7,V_SrcOrderID,V_CustomType), V_SrcOrderNO, P_StockoutId, sod.rec_id, V_StockoutNo, ss.spec_id, V_WarehouseId, 2, 
		ss.cost_price, ss.stock_num+SUM(sod.num), ss.cost_price, SUM(sod.num), ss.cost_price*SUM(sod.num), ss.cost_price, ss.stock_num, @cur_uid, V_OrderMask, ''
		FROM stockout_order_detail sod 
		LEFT JOIN stock_spec ss ON sod.spec_id=ss.spec_id AND ss.warehouse_id=V_WarehouseId 
		WHERE sod.stockout_id=P_StockoutId
		GROUP BY sod.spec_id;
		
	CALL SP_UTILS_GET_CFG('stock_auto_sync', @cfg_stock_auto_sync, 0);
	IF @cfg_stock_auto_sync <>0 THEN
		-- 更新一下平台货品的库存标记
		INSERT INTO sys_process_background(`type`,object_id)
		SELECT 1,spec_id FROM stockout_order_detail WHERE stockout_id=P_StockoutId;
		
		-- 组合装
		INSERT INTO sys_process_background(`type`,object_id)
		SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,stockout_order_detail sod 
			WHERE sod.stockout_id=P_StockoutId AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sod.spec_id;
	END IF;
	
	-- 修改出库单状态    55待打印 60已打印 65已验货 70已打包? 75已称重 80已出库? 85待发货 90发货中 95已发货 100已签收 105部分打款 110已完成
	SELECT SUM(IFNULL(IF(is_package,0,num*cost_price),0)),SUM(IFNULL(IF(is_package,num*cost_price,0),0))
		INTO V_GoodsCost,V_PackageCost
	FROM stockout_order_detail WHERE stockout_id=P_StockoutId;

	-- 计算不允许0成本的  售价总额  
	SELECT SUM(IFNULL(num*price,0))
		INTO V_UnknownGoodsAmount
	FROM stockout_order_detail WHERE stockout_id=P_StockoutId AND is_package=0 AND is_allow_zero_cost=0 AND (cost_price=0.0 OR cost_price IS NULL); 
	
	UPDATE stockout_order 
	SET `status`=P_ToStatus,
		consign_status=(consign_status|4),
		checkouter_id=IF(P_ToStatus>=95,0,checkouter_id),
		consigner_id=@cur_uid,
		goods_total_cost=V_GoodsCost,
		unknown_goods_amount=IFNULL(V_UnknownGoodsAmount,0),
		package_cost=V_PackageCost,
		weight=IF(weight=0,calc_weight,weight),
		post_cost=IF(post_cost=0,calc_post_cost,post_cost),
		consign_time=IF(P_ToStatus>=95,NOW(),consign_time)
	WHERE stockout_id=P_StockoutId;
	
	-- 日志
	INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
	VALUES(2,P_StockoutId,@cur_uid,
		CASE P_ToStatus WHEN 55 THEN 18 WHEN 95 THEN 50 ELSE 112 END,
		CASE P_ToStatus WHEN 55 THEN '出库完成' WHEN 95 THEN '出库单已发货' ELSE '出库单已完成' END);
	
END//
DELIMITER ;
