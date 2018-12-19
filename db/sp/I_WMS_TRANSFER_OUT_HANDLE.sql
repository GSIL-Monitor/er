DROP PROCEDURE IF EXISTS `I_WMS_TRANSFER_OUT_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `I_WMS_TRANSFER_OUT_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外调拨出库单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus,V_IsDefect,V_WmsOrderType,V_PlanFlag TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo, V_OrderLogisticsNo ,V_WmsOrderStatusName VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId,V_MatchWarehouseId, V_OrderWmsStatus,V_LockId,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_BizCode,V_Wmsno,V_OwnerNo VARCHAR(50);
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId,V_WmsOuterNO,V_NewLogisticsName,V_OldLogisticsName VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId,V_RetLogisticsType INT DEFAULT (0);
	DECLARE V_StockOutOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId INT(11);
	DECLARE V_StockOutOrderNo,V_WarehouseNo VARCHAR(40);
	DECLARE V_GoodsAmount , V_TotalCalcWeight,V_WmsWeight DECIMAL(19,4);
	DECLARE order_cursor CURSOR FOR SELECT spec_no,rec_id FROM tmp_wms_order_detail;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	SELECT `order_type` , `status` , `status_name` ,`logistics_code` , `logistics_no`, `weight`,`undefined4`, `remark`, `confirm_flag` ,`logistics_list` , `wms_no` ,`owner_no`, `order_plan_flag`,`biz_code`  
	INTO V_WmsOrderType, V_WmsOrderStatus, V_WmsOrderStatusName, V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo, V_WmsWeight, V_WmsOuterNO, V_WmsRemark, V_ConfirmFlag, V_LogisticsList, V_Wmsno, V_OwnerNo, V_PlanFlag,V_BizCode   
	FROM tmp_wms_order WHERE order_no = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = -1;
		SET P_ErrorMsg = "服务器错误请稍后重试";
		LEAVE MAIN_LABEL;
	END IF;
	SELECT st.rec_id, st.from_warehouse_id, st.transfer_no, st.logistics_id, st.logistics_no, st.status ,st.wms_status ,st.lock_id  
	INTO V_OrderId, V_WarehouseId, V_OrderNo, V_OrderLogisticsId,V_OrderLogisticsNo,V_OrderStatus, V_OrderWmsStatus, V_LockId 
	FROM stock_transfer st 
	WHERE st.outer_no = P_OrderNo;
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET  V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=80 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 上次入库已经为最后一次出库,并且调拨单状态为部分到货之后的状态，返回成功
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=50 AND V_ConfirmFlag =0 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 上次出库为最终出库!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 46,待出库; 50,部分出库;
	IF V_OrderStatus <> 46 AND V_OrderStatus <> 50 THEN 
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	IF V_WmsOrderStatus = 6 THEN -- 出库操作
		IF V_WmsOrderLogisticsCode<>'' THEN 
		-- 物流校验		
			SELECT clw.logistics_id ,cl.logistics_type,cl.logistics_name INTO V_RetLogisticsId,V_RetLogisticsType,V_NewLogisticsName
			FROM cfg_logistics_wms clw  
			LEFT JOIN cfg_logistics cl ON clw.logistics_id = cl.logistics_id
			WHERE clw.logistics_code = V_WmsOrderLogisticsCode AND clw.warehouse_id = V_WarehouseId ORDER BY IF(clw.logistics_id = V_OrderLogisticsId,0,1) LIMIT 1;
			IF  V_NOT_FOUND<>0 THEN 
				ROLLBACK;
				SET V_NOT_FOUND = 0;
				SET P_Code = 1003;
				SET P_ErrorMsg = CONCAT('物流公司',V_WmsOrderLogisticsCode,' 不存在!');
				LEAVE MAIN_LABEL;
			END IF;
			IF V_WmsOrderLogisticsNo = '' AND V_RetLogisticsType <> 1 THEN 
				ROLLBACK;
				SET P_Code = 1102;
				SET P_ErrorMsg = '物流单号不能为空!';
				LEAVE MAIN_LABEL;
			END IF;
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId THEN 
				IF EXISTS(SELECT logistics_no FROM stock_transfer WHERE rec_id = V_OrderId AND logistics_no = '') THEN					
					UPDATE stock_transfer st SET st.logistics_id = V_RetLogisticsId WHERE st.outer_no = P_OrderNo;
						
					SELECT logistics_name INTO V_OldLogisticsName FROM cfg_logistics WHERE logistics_id = V_OrderLogisticsId;
					INSERT INTO stock_inout_log(order_type,order_id,operator_id,`operate_type`,message,`data`,created)
					VALUES(3,V_OrderId,0,18,CONCAT('WMS回传物流变更,从:',V_OldLogisticsName,' 到 ',V_NewLogisticsName),0,NOW());
						
					SET V_OrderLogisticsId = V_RetLogisticsId;
				END IF;
			END IF;
		END IF;	
		-- 商品信息的有效性判断
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
		IF V_Count=0 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1004;
			SET P_ErrorMsg = "单据信息异常，无商品明细!";
			LEAVE MAIN_LABEL;
		END IF;
		-- 开启了消息id校验
		IF V_BizCode <> '' THEN
			SELECT COUNT(1) INTO V_Count FROM stockout_order WHERE src_order_type = 2 AND src_order_id = V_OrderId AND outer_no = V_BizCode;
			IF V_Count <> 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 0;
				SET P_ErrorMsg = "该消息ID请求已处理";
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		-- 校验是否开启了残次品管理
		SELECT is_defect,0 as match_warehouse_id,'' as warehouse_no INTO V_IsDefect,V_MatchWarehouseId,V_WarehouseNo FROM cfg_warehouse WHERE warehouse_id = V_WarehouseId;
		-- 正品仓
		IF V_IsDefect = 0 THEN
			-- 如果没有匹配残次品仓,则全按正品处理
			IF V_MatchWarehouseId = 0 THEN
				UPDATE tmp_wms_order_detail SET inventory_type = 0 WHERE order_no = P_OrderNo;
			END IF;
		-- 残次品仓	
		ELSE 
			SELECT COUNT(1) INTO V_Count FROM cfg_warehouse WHERE match_warehouse_id = V_WarehouseId;
			-- 如果没有被匹配,则全按残次品处理
			IF V_Count = 0 THEN
				UPDATE tmp_wms_order_detail SET inventory_type = 1 WHERE order_no = P_OrderNo;
			-- 如果被匹配过，则报错
			ELSE
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT('仓库编号',V_WarehouseNo,' 为被匹配的残品仓,不允许处理委外调拨出库单!');
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		SET V_NOT_FOUND=0;
		OPEN order_cursor;
		ORDER_LABEL:LOOP
			FETCH order_cursor INTO V_WmsSpecNo,V_TmpRecId; 
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE ORDER_LABEL;
			END IF;
			SELECT ssd.rec_id,ssd.spec_id INTO V_OrderDetailId,V_SpecId FROM stock_transfer_detail ssd  
			WHERE ssd.transfer_id = V_OrderId 
			AND ssd.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1006;
				SET P_ErrorMsg = CONCAT_WS('','单据信息异常，存在不明商品:',V_WmsSpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			UPDATE tmp_wms_order_detail SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId WHERE rec_id = V_TmpRecId;
			SET V_TmpRecId = 0;
			SET V_SpecId = 0;
		END LOOP;
		CLOSE order_cursor;
		-- 正品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0;
		IF V_Count > 0 THEN
			-- 创建出库单 status=50 为待审核 
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
			INSERT INTO stockout_order(stockout_no,outer_no,warehouse_id,src_order_type,src_order_id,src_order_no,operator_id,`status`,logistics_id,logistics_no,remark,reserve_i,created)
			VALUES(V_StockOutOrderNo,V_BizCode,V_WarehouseId,2,V_OrderId,V_OrderNo,0,50,V_OrderLogisticsId,V_WmsOrderLogisticsNo,V_WmsRemark,V_LockId,NOW());
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET P_Code = 1007;
				SET P_ErrorMsg = '正品出库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 插入出库单详情
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,base_unit_id,num,num2,unit_ratio,unit_id,
			goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,price,total_amount,position_id)
			SELECT V_StockOutOrderId,2,ssd.rec_id,IF(gs.unit=0,gg.unit,gs.unit),twod.num,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),
			gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,IFNULL(ss.cost_price,0),CAST(ss.cost_price*twod.num AS DECIMAL(19,4)),IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id)
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN stock_transfer_detail ssd ON ssd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = ssd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE ss.warehouse_id = V_WarehouseId; 
			-- 更新货品数量 和货品类型数量
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),IFNULL(SUM(total_amount),0),IFNULL(SUM(weight),0) INTO V_GoodsTypeCount,V_GoodsCount,V_GoodsAmount,V_TotalCalcWeight 
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_GoodsAmount,calc_weight = V_TotalCalcWeight
			WHERE stockout_id=V_StockOutOrderId;  
			-- 插入出库单日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message)
			VALUES(2,V_StockOutOrderId,0,13,'WMS正品出库回传,新建并递交出库单');
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(3,V_OrderId,0,74,CONCAT_WS(':','WMS正品已出库--出库单号',V_StockOutOrderNo));	
			-- 调用存储过程，进行出库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL `I_STOCKOUT_OTHER_CHECK`(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','正品调拨出库单审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 如果开启了批次管理，需要插入明细到出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 3,V_OrderId,V_OrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0
				ON DUPLICATE KEY UPDATE stockout_batch_detail.num = stockout_batch_detail.num+VALUES(stockout_batch_detail.num);
			END IF;
		END IF;
		-- 残次品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
		IF V_Count > 0 THEN
			-- 正品仓的调拨出库单不允许出残次品
			IF V_IsDefect = 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1008;
				SET P_ErrorMsg = CONCAT("正品仓调拨出库单",P_OrderNo," 回传信息异常，存在残品商品!");
				LEAVE MAIN_LABEL;
			END IF;
			-- 创建出库单 status=50 为待审核 
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
			INSERT INTO stockout_order(stockout_no,outer_no,warehouse_id,src_order_type,src_order_id,src_order_no,operator_id,`status`,logistics_id,logistics_no,remark,reserve_i,created)
			VALUES(V_StockOutOrderNo,V_BizCode,V_WarehouseId,2,V_OrderId,V_OrderNo,0,50,V_OrderLogisticsId,V_WmsOrderLogisticsNo,V_WmsRemark,V_LockId,NOW());
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET P_Code = 1009;
				SET P_ErrorMsg = '残品出库单信息录入异常,请重试!!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 插入出库单详情
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,base_unit_id,num,num2,unit_ratio,unit_id,
			goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,price,total_amount,position_id)
			SELECT V_StockOutOrderId,2,ssd.rec_id,IF(gs.unit=0,gg.unit,gs.unit),twod.num,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),
			gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,IFNULL(ss.cost_price,0),CAST(ss.cost_price*twod.num AS DECIMAL(19,4)),IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id)
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN stock_transfer_detail ssd ON ssd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = ssd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE ss.warehouse_id = V_WarehouseId; 
			-- 更新货品数量 和货品类型数量
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),IFNULL(SUM(total_amount),0),IFNULL(SUM(weight),0) INTO V_GoodsTypeCount,V_GoodsCount,V_GoodsAmount,V_TotalCalcWeight 
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_GoodsAmount,calc_weight = V_TotalCalcWeight
			WHERE stockout_id=V_StockOutOrderId;  
			-- 插入出库单日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message)
			VALUES(2,V_StockOutOrderId,0,13,'WMS残品出库回传,新建并递交出库单');
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(3,V_OrderId,0,74,CONCAT_WS(':','WMS残品已出库--出库单号',V_StockOutOrderNo));	
			-- 调用存储过程，进行出库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL `I_STOCKOUT_OTHER_CHECK`(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','残品调拨出库单审核失败',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 如果开启了批次管理，需要插入明细到出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 3,V_OrderId,V_OrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1
				ON DUPLICATE KEY UPDATE stockout_batch_detail.num = stockout_batch_detail.num+VALUES(stockout_batch_detail.num);
			END IF;
		END IF;
		-- 物流更新
		UPDATE stock_transfer SET logistics_no = V_WmsOrderLogisticsNo WHERE rec_id = V_OrderId ;
		UPDATE stock_transfer SET wms_status=5,error_info = '' WHERE rec_id=V_OrderId ;
		-- 最终出库标志 设置标记，防止下次重复出库
		IF V_ConfirmFlag = 0 THEN 
			UPDATE stock_transfer SET wms_status=6,error_info='WMS推送最终出库信息' 
			WHERE rec_id=V_OrderId ;
		END IF;
	ELSEIF V_WmsOrderStatus = 2 THEN -- WMS拒绝接收单据
		UPDATE stock_transfer SET STATUS=44,wms_status=1,error_info=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE rec_id=V_OrderId ;
	ELSEIF V_WmsOrderStatus = 1 THEN -- WMS成功接收单据
		UPDATE stock_transfer SET wms_status=2,error_info='WMS推送状态:已接单' WHERE rec_id=V_OrderId ;
	ELSE  
		UPDATE stock_transfer SET error_info=CONCAT_WS('','WMS推送状态:',V_WmsOrderStatusName) WHERE rec_id=V_OrderId ;
	END IF ;
END$$
DELIMITER ;