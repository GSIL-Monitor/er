DROP PROCEDURE IF EXISTS `I_WMS_STOCKCHANGE_IN_HANDLE`;

DELIMITER $$

CREATE  PROCEDURE `I_WMS_STOCKCHANGE_IN_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外库存异动入库单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus,V_PlanFlag,V_IsDefect TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo  ,V_WmsOrderStatusName  VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_SpecId, V_WarehouseId, V_MatchWarehouseId , V_OrderWmsStatus,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag,V_FOUND_STOCKOUT,V_InventoryType TINYINT(1);
	DECLARE V_WmsSpecNo,V_Wmsno,V_OwnerNo,V_BizCode,V_CustomerId,V_BatchGroup VARCHAR(50);
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId INT(11);
	DECLARE V_StockInOrderNo VARCHAR(40);
	DECLARE V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_TotalPrice ,V_Discount,V_GoodsAmount,V_WmsWeight DECIMAL(19,4);
	DECLARE order_cursor CURSOR FOR SELECT rec_id,spec_no,inventory_type,biz_code,batch_group FROM tmp_wms_order_detail ORDER BY batch_group,biz_code DESC;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SELECT `status` , `status_name` ,`logistics_code` , `logistics_no`, `weight`,`undefined4`, `remark`, `confirm_flag` ,`logistics_list` , `wms_no` ,`owner_no`, `wms_id`  
	INTO V_WmsOrderStatus, V_WmsOrderStatusName, V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo, V_WmsWeight, V_WmsOuterNO, V_WmsRemark, V_ConfirmFlag, V_LogisticsList , V_Wmsno, V_OwnerNo, V_CustomerId  
	FROM tmp_wms_order WHERE order_no = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = -1;
		SET P_ErrorMsg = "服务器错误请稍后重试";
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 判断是否有一仓多货主的情况
	SELECT COUNT(1) INTO V_Count FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno;
	
	IF V_Count = 0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT("单据信息异常，没有该仓库:",V_Wmsno);
		LEAVE MAIN_LABEL;
	ELSEIF V_Count = 1 THEN
		SELECT warehouse_id,match_warehouse_id INTO V_WarehouseId,V_MatchWarehouseId FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno;
	ELSE 
		SELECT COUNT(1) INTO V_Count FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno AND api_object_id = '';
		
		IF  V_Count <> 0 THEN
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1002;
			SET P_ErrorMsg = CONCAT("仓库:",V_Wmsno,"授权信息配置有误，请联系旺店通技术人员处理");
			LEAVE MAIN_LABEL;
		ELSE 
			SELECT warehouse_id,match_warehouse_id INTO V_WarehouseId,V_MatchWarehouseId FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno AND api_object_id = V_OwnerNo;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1003;
				SET P_ErrorMsg = CONCAT("货主编码:",V_OwnerNo,"不正确，未找到相应仓库信息");
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
	END IF;
	
	-- 商品信息的有效性判断
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo;
	IF V_Count=0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 1004;
		SET P_ErrorMsg = CONCAT('单据',P_OrderNo,' 信息异常，无商品明细');
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_NOT_FOUND=0;
	
	-- 校验是否开启了残次品管理
	SELECT is_defect,match_warehouse_id INTO V_IsDefect,V_MatchWarehouseId FROM cfg_warehouse WHERE warehouse_id = V_WarehouseId;
	-- 正品仓
	IF V_IsDefect = 0 THEN
		-- 如果没有匹配残次品仓,则全按正品处理
		IF V_MatchWarehouseId = 0 THEN
			UPDATE tmp_wms_order_detail SET inventory_type = 0 WHERE order_no = P_OrderNo;
		END IF;
		
	-- 残次品仓，全部按照残次品处理	
	ELSE 
		UPDATE tmp_wms_order_detail SET inventory_type = 1 WHERE order_no = P_OrderNo;
		
	END IF;
	
	-- 商品校验
	OPEN order_cursor;
	ORDER_LABEL:LOOP
		FETCH order_cursor INTO V_TmpRecId,V_WmsSpecNo,V_InventoryType,V_BizCode,V_BatchGroup; 
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND=0;
			LEAVE ORDER_LABEL;
		END IF;
		
		IF V_BizCode = '' THEN
		     SELECT spec_id INTO V_SpecId FROM tmp_wms_order_detail WHERE batch_group = V_BatchGroup AND biz_code <> '';
		     -- 主货品消息ID校验失败，次货品无需校验消息ID直接continue
		     IF V_SpecId = 0 THEN
			ITERATE ORDER_LABEL;
		     END IF;
		ELSE    
			-- 消息ID校验
			SET V_Count = 0;
			SELECT COUNT(1) INTO V_Count FROM wms_bizcode WHERE order_type = 1 AND customer_id = V_CustomerId AND biz_code = V_BizCode; 
			IF V_Count = 0 THEN
				-- 校验是否为历史单据
				SELECT COUNT(1) INTO V_Count FROM wms_bizcode WHERE order_type = 1 AND customer_id = V_CustomerId AND outer_no = P_OrderNo; 
				IF V_Count = 0 THEN
					SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE src_order_type = 6 AND outer_no = P_OrderNo AND warehouse_id  IN (V_WarehouseId,V_MatchWarehouseId); 
					IF V_Count <> 0 THEN
						SET P_Code = 0;
						SET P_ErrorMsg = '入库单重复';
						LEAVE MAIN_LABEL;
					END IF;
				END IF;
			ELSE 
				SET P_ErrorMsg = CONCAT_WS('',P_ErrorMsg,'商品:',V_WmsSpecNo,'消息ID已处理;');
				ITERATE ORDER_LABEL;
			END IF;
			-- 插入到消息ID表
			INSERT INTO wms_bizcode(order_type,customer_id,biz_code,outer_no,spec_no,created)
			VALUES(1,V_CustomerId,V_BizCode,P_OrderNo,V_WmsSpecNo,NOW());
		END IF;
		
		SET V_NOT_FOUND=0;
		SELECT gs.spec_id INTO V_OrderDetailId FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0 LIMIT 1;
		IF V_NOT_FOUND = 1 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1005;
			SET P_ErrorMsg = CONCAT_WS('','单据信息异常，存在不明商品:',V_WmsSpecNo);
			LEAVE MAIN_LABEL;
		END IF;
		 
		-- 此处order_detail_id 用来存储出库单的单品id
		UPDATE tmp_wms_order_detail SET order_detail_id = V_OrderDetailId,spec_id = V_OrderDetailId WHERE rec_id = V_TmpRecId;	
		SET V_OrderDetailId = 0;
	END LOOP;
	CLOSE order_cursor;
	
	-- 判断是否还有需要处理的商品
	SET V_Count = 0;
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE spec_id <> 0;
	IF V_Count = 0 THEN
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = '所有商品消息ID已处理';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 正品处理
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0;
	IF V_Count > 0 THEN
				
		-- 创建入库单
		SET @cur_uid = 0;
		SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
		INSERT INTO stockin_order(stockin_no,outer_no,warehouse_id,src_order_type,src_order_id,
		operator_id,`status`,remark,created)
		VALUES(V_StockInOrderNo,P_OrderNo,V_WarehouseId,6,0,@cur_uid,30,V_WmsRemark,NOW());
		SELECT LAST_INSERT_ID() INTO V_OrderId;
		IF V_OrderId <= 0 THEN 
			SET P_Code = 1006;
			SET P_ErrorMsg = CONCAT('正品单据',P_OrderNo,' 信息录入异常,请重试!');
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 插入入库单日志
		INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_OrderId,1,13,'委外库存异动新建并递交正品入库单');
			
		-- 插入入库单详细信息
		INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
			expect_num,base_unit_id,num,unit_ratio,unit_id,num2,cost_price,created) 
		SELECT V_OrderId ,6,0,gs.spec_id,-V_WarehouseId,twod.num,gg.unit 
			,twod.num,IFNULL(cau.base_ratio,1),gg.aux_unit,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(ss.cost_price,0),NOW() 
		FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 AND order_detail_id <> 0 GROUP BY order_detail_id ) AS twod    
		LEFT JOIN goods_spec gs ON gs.spec_id =  twod.order_detail_id   
		LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
		LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id AND ss.warehouse_id = V_WarehouseId
		LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit;
		
		-- 更新入库单货品种类和数量
		SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
		INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
		FROM stockin_order_detail WHERE stockin_id = V_OrderId FOR UPDATE ;
		
		UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
		WHERE stockin_id=V_OrderId ;
		
		-- 调用存储过程，审核入库单，并对结果进行判断
		SET @cur_uid=0;
		CALL I_STOCKIN_CHECK(V_OrderId);
		IF @sys_code<>0 THEN
			SET P_Code = @sys_code;
			SET P_ErrorMsg = LEFT(CONCAT_WS('','正品入库单审核失败',@sys_message),255);
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 如果开启了批次管理，需要插入明细到入库单批次中间表
		IF @batch_flag = 1 THEN
			INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
			SELECT 2,0,'',V_OrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
			FROM tmp_wms_order_detail twod
			WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0 AND order_detail_id <> 0
			ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
		END IF;
		
		UPDATE stockin_order SET wms_status = 5 WHERE stockin_id = V_OrderId;
		
	END IF;
	
	-- 残品处理
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
	IF V_Count > 0 THEN
				
		-- 创建入库单
		SET @cur_uid = 0;
		SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
		INSERT INTO stockin_order(stockin_no,outer_no,warehouse_id,src_order_type,src_order_id,
		operator_id,`status`,remark,created)
		VALUES(V_StockInOrderNo,P_OrderNo,IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId),6,0,@cur_uid,30,V_WmsRemark,NOW());
		SELECT LAST_INSERT_ID() INTO V_OrderId;
		IF V_OrderId <= 0 THEN 
			SET P_Code = 1007;
			SET P_ErrorMsg = CONCAT('残品单据',P_OrderNo,' 信息录入异常,请重试!');
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		
		-- 插入入库单日志
		INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_OrderId,1,13,'委外库存异动新建并递交残品入库单');
			
		-- 插入入库单详细信息
		INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
			expect_num,base_unit_id,num,unit_ratio,unit_id,num2,cost_price,created) 
		SELECT V_OrderId ,6,0,gs.spec_id,-IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId),twod.num,gg.unit 
			,twod.num,IFNULL(cau.base_ratio,1),gg.aux_unit,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(ss.cost_price,0),NOW() 
		FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 AND order_detail_id <> 0 GROUP BY order_detail_id ) AS twod    
		LEFT JOIN goods_spec gs ON gs.spec_id =  twod.order_detail_id   
		LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
		LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id AND ss.warehouse_id = V_WarehouseId
		LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit;  
		
		-- 更新入库单货品种类和数量
		SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
		INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
		FROM stockin_order_detail WHERE stockin_id = V_OrderId FOR UPDATE ;
		
		UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
			total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
		WHERE stockin_id=V_OrderId ;
		
		-- 调用存储过程，审核入库单，并对结果进行判断
		SET @cur_uid=0;
		CALL I_STOCKIN_CHECK(V_OrderId);
		IF @sys_code<>0 THEN
			SET P_Code = @sys_code;
			SET P_ErrorMsg = LEFT(CONCAT_WS('','残品入库单审核失败',@sys_message),255);
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 如果开启了批次管理，需要插入明细到入库单批次中间表
		IF @batch_flag = 1 THEN
			INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
			SELECT 2,0,'',V_OrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
			FROM tmp_wms_order_detail twod
			WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1 AND order_detail_id <> 0
			ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
		END IF;
		
		UPDATE stockin_order SET wms_status = 5 WHERE stockin_id = V_OrderId;
		
	END IF;
	
END$$

DELIMITER ;

