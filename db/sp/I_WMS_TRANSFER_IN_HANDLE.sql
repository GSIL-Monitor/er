DROP PROCEDURE IF EXISTS `I_WMS_TRANSFER_IN_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `I_WMS_TRANSFER_IN_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外调拨入库单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderType,V_WmsOrderStatus,V_PlanFlag,V_IsDefect TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo  ,V_WmsOrderStatusName VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId,V_MatchWarehouseId, V_OrderWmsStatus,V_LockId,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_TransferInNo,V_BizCode,V_Wmsno,V_OwnerNo VARCHAR(50);
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId,V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId INT DEFAULT (0);
	DECLARE V_StockInOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId INT(11);
	DECLARE V_StockInOrderNo,V_WarehouseNo VARCHAR(40);
	DECLARE V_TotalPrice ,V_Discount,V_GoodsAmount,V_WmsWeight DECIMAL(19,4);
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
	IF V_PlanFlag=1 THEN -- 调拨出库单
		SELECT st.rec_id, st.from_warehouse_id, st.transfer_no, st.logistics_id, st.status ,st.wms_status ,st.lock_id  
		INTO V_OrderId, V_WarehouseId, V_OrderNo, V_OrderLogisticsId, V_OrderStatus, V_OrderWmsStatus,V_LockId 
		FROM stock_transfer st 
		WHERE st.outer_no = P_OrderNo;
		IF  V_NOT_FOUND<>0 THEN 
			ROLLBACK;
			SET  V_NOT_FOUND = 0;
			SET P_Code = 1001;
			SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 不存在!');
			LEAVE MAIN_LABEL;
		END IF;
		-- 50 部分出库，62 入库单待推送，64 入库单推送失败 
		IF V_OrderStatus<>50 AND V_OrderStatus<>62 AND V_OrderStatus<>64 THEN 
			ROLLBACK;
			SET P_Code = 1002;
			SET P_ErrorMsg = CONCAT('调拨出库单',P_OrderNo,' 状态错误!');
			LEAVE MAIN_LABEL;
		END IF;
		SELECT FN_SYS_NO('outer_no') INTO V_TransferInNo;
		SET V_TransferInNo = CONCAT_WS('','ODB',V_TransferInNo);
		-- 更新为待入库状态
		UPDATE stock_transfer SET wms_status=2,error_info = '调拨计划单',`status` = 66,outer_no2=V_TransferInNo,to_wms_order_no = '' WHERE rec_id = V_OrderId ;
		UPDATE tmp_wms_order_detail SET order_no=V_TransferInNo WHERE order_no=P_OrderNo;
		SET P_OrderNo = V_TransferInNo;
	END IF;
	SELECT st.rec_id, st.to_warehouse_id, st.transfer_no, st.logistics_id, st.status ,st.wms_status  
	INTO V_OrderId, V_WarehouseId, V_OrderNo, V_OrderLogisticsId, V_OrderStatus, V_OrderWmsStatus
	FROM stock_transfer st 
	WHERE st.outer_no2 = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND = 0;
		SET P_Code = 1003;
		SET P_ErrorMsg = CONCAT('调拨入库单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=80 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('调拨入库单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 上次入库已经为最后一次入库,并且调拨单状态为部分到货之后的状态，返回成功
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=70  AND V_ConfirmFlag =0  THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('调拨入库单',P_OrderNo,' 上次入库为最终入库!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 66,待入库; 70,部分入库
	IF V_OrderStatus <> 66 AND V_OrderStatus <> 70 THEN 
		ROLLBACK;
		SET P_Code = 1004;
		SET P_ErrorMsg = CONCAT('调拨入库单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	IF V_WmsOrderStatus = 6 THEN -- 入库操作
		-- 物流校验
		IF V_WmsOrderLogisticsCode<>'' THEN 
			SELECT clw.logistics_id INTO V_RetLogisticsId   
			FROM cfg_logistics_wms clw  
			WHERE clw.logistics_code = V_WmsOrderLogisticsCode AND clw.warehouse_id = V_WarehouseId LIMIT 1;
			IF  V_NOT_FOUND<>0 THEN 
				ROLLBACK;
				SET V_NOT_FOUND = 0;
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT('物流公司',V_WmsOrderLogisticsCode,' 不存在!');
				LEAVE MAIN_LABEL;
			END IF;
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId THEN 
				SET V_OrderLogisticsId=V_RetLogisticsId;
			END IF;
		END IF;	
		-- 商品信息的有效性判断
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
		IF V_Count=0 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1006;
			SET P_ErrorMsg = "单据信息异常，无商品明细!";
			LEAVE MAIN_LABEL;
		END IF;
		-- 开启了消息id校验
		IF V_BizCode <> '' THEN
			SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE src_order_type = 2 AND src_order_id = V_OrderId AND outer_no = V_BizCode;
			IF V_Count <> 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 0;
				SET P_ErrorMsg = "该消息ID请求已处理";
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		-- 校验是否开启了残次品管理
		SELECT is_defect,match_warehouse_id,'' as warehouse_no INTO V_IsDefect,V_MatchWarehouseId,V_WarehouseNo FROM cfg_warehouse WHERE warehouse_id = V_WarehouseId;
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
				SET P_Code = 1007;
				SET P_ErrorMsg = CONCAT('仓库编号',V_WarehouseNo,' 为被匹配的残品仓,不允许处理委外调拨入库单!');
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
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
				SET P_Code = 1008;
				SET P_ErrorMsg = CONCAT_WS('','单据信息异常，存在不明商品:',V_WmsSpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			UPDATE tmp_wms_order_detail SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId WHERE rec_id = V_TmpRecId;
			SET V_OrderDetailId = 0;
			SET V_SpecId = 0;
		END LOOP;
		CLOSE order_cursor;
		-- 正品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0;
		IF V_Count > 0 THEN
			-- 创建入库单，并获取入库单号
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
				,src_order_no,outer_no,logistics_id,logistics_no,operator_id,`status`,created, `remark`) 
			VALUES(V_StockInOrderNo ,V_WarehouseId , 2,V_OrderId ,V_OrderNo ,V_BizCode ,V_OrderLogisticsId ,V_WmsOrderLogisticsNo ,0 ,30 ,NOW() ,V_WmsRemark) ;		
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET P_Code = 1009;
				SET P_ErrorMsg = '正品入库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,tax_price,tax_amount,src_price,cost_price,cost_price2,total_cost,created) 
			SELECT V_StockInOrderId ,2,ssd.rec_id,ssd.spec_id,-V_WarehouseId,twod.num,IF(gs.unit=0,gg.unit,gs.unit) 
				,twod.num,IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),(twod.num/IFNULL(cau.base_ratio,1))
				,IFNULL(ssd.out_cost_total/ssd.out_num,0),IFNULL(ssd.out_cost_total/ssd.out_num,0)*twod.num
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)  
				,IFNULL(ssd.out_cost_total/ssd.out_num,0) 
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)*twod.num,NOW() 
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN stock_transfer_detail ssd ON ssd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = ssd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit  
			WHERE ssd.transfer_id = V_OrderId;
			-- 更新入库单货品数量和货品种类 
			SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
			FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
			WHERE stockin_id=V_StockInOrderId ;
			-- 记录日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_StockInOrderId,0,13,'WMS正品入库回传,新建并递交入库单');	
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(3,V_OrderId,0,72,CONCAT_WS(':','WMS正品已入库--入库单号',V_StockInOrderNo));	
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','正品调拨入库单审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 如果开启了批次管理，需要插入明细到入库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 3,V_OrderId,V_OrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
		END IF;
		-- 残次品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
		IF V_Count > 0 THEN
			-- 创建入库单，并获取入库单号
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id,src_order_no,outer_no,logistics_id,logistics_no,operator_id,`status`,created, `remark`) 
			VALUES(V_StockInOrderNo ,IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId) , 2,V_OrderId ,V_OrderNo ,V_BizCode ,V_OrderLogisticsId ,V_WmsOrderLogisticsNo ,0 ,30 ,NOW() ,V_WmsRemark) ;		
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET P_Code = 1010;
				SET P_ErrorMsg = '残品入库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,tax_price,tax_amount,src_price,cost_price,cost_price2,total_cost,created) 
			SELECT V_StockInOrderId ,2,ssd.rec_id,ssd.spec_id,-IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId),twod.num,IF(gs.unit=0,gg.unit,gs.unit) 
				,twod.num,IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),(twod.num/IFNULL(cau.base_ratio,1))
				,IFNULL(ssd.out_cost_total/ssd.out_num,0),IFNULL(ssd.out_cost_total/ssd.out_num,0)*twod.num
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)  
				,IFNULL(ssd.out_cost_total/ssd.out_num,0) 
				,IFNULL(ssd.out_cost_total/ssd.out_num,0)*twod.num,NOW() 
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN stock_transfer_detail ssd ON ssd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = ssd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit  
			WHERE ssd.transfer_id = V_OrderId;
			-- 更新入库单货品数量和货品种类 
			SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
			FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
		
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
			WHERE stockin_id=V_StockInOrderId ;
			
			-- 记录日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_StockInOrderId,0,13,'WMS残品入库回传,新建并递交入库单');	
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(3,V_OrderId,0,72,CONCAT_WS(':','WMS残品已入库--入库单号',V_StockInOrderNo));	
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','残品调拨入库单审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果开启了批次管理，需要插入明细到入库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 3,V_OrderId,V_OrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		UPDATE stock_transfer SET wms_status=5,error_info = '' WHERE rec_id=V_OrderId ;
		
		-- 最终入库标志 设置标记，防止下次重复入库
		IF V_ConfirmFlag = 0 THEN 
			UPDATE stock_transfer SET wms_status=6,error_info='WMS推送最终入库信息' 
			WHERE rec_id=V_OrderId ;
		END IF;
		
	ELSEIF V_WmsOrderStatus = 2 THEN -- WMS拒绝接收单据
		UPDATE stock_transfer SET STATUS=64,wms_status=1,error_info=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE rec_id=V_OrderId ;
	
	ELSEIF V_WmsOrderStatus = 1 THEN -- WMS成功接收单据
		UPDATE stock_transfer SET wms_status=2,error_info='WMS推送状态:已接单' WHERE rec_id=V_OrderId ;
	ELSE  
		UPDATE stock_transfer SET error_info=CONCAT_WS('','WMS推送状态:',V_WmsOrderStatusName) WHERE rec_id=V_OrderId ;
	END IF ;
	
END$$
DELIMITER ;