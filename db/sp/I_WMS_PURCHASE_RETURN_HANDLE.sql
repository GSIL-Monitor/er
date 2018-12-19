DROP PROCEDURE IF EXISTS `I_WMS_PURCHASE_RETURN_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `I_WMS_PURCHASE_RETURN_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外采购退货单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus,V_IsDefect,V_WmsOrderType,V_PlanFlag TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo  ,V_WmsOrderStatusName VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId,V_MatchWarehouseId,V_OrderWmsStatus,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_BizCode,V_Wmsno,V_OwnerNo VARCHAR(50);
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId,V_WmsOuterNO,V_OldLogisticsName,V_NewLogisticsName  VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId INT DEFAULT (0);
	DECLARE V_StockOutOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId INT(11);
	DECLARE V_StockOutOrderNo VARCHAR(40);
	DECLARE V_TotalPrice,V_WmsWeight,V_PostCost DECIMAL(19,4);
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
	
	SELECT pr.return_id , pr.status , pr.warehouse_id ,pr.return_no , pr.logistics_type , pr.wms_status ,pr.post_fee
	INTO V_OrderId, V_OrderStatus, V_WarehouseId, V_OrderNo, V_OrderLogisticsId , V_OrderWmsStatus ,V_PostCost
	FROM purchase_return pr 
	WHERE pr.outer_no = P_OrderNo;
	
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('采购退货单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
		
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=60 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('采购退货单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=50 AND V_ConfirmFlag =0 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('采购退货单',P_OrderNo,' 上次出库为最终出库!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderStatus <> 46 AND V_OrderStatus <> 50 THEN 
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('采购退货单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_WmsOrderStatus = 6 THEN -- 出库操作
		
		IF V_WmsOrderLogisticsCode<>'' THEN 
		
			SELECT clw.logistics_id,cl.logistics_name INTO V_RetLogisticsId,V_NewLogisticsName
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
			
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId  THEN 
				
				UPDATE purchase_return pr 
				SET pr.logistics_type = V_RetLogisticsId 
				WHERE pr.outer_no = P_OrderNo;
					
				SELECT logistics_name INTO V_OldLogisticsName FROM cfg_logistics WHERE logistics_id = V_OrderLogisticsId;
				INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark,created)
				VALUES(V_OrderId,0,4,CONCAT('WMS回传物流变更,从:',V_OldLogisticsName,' 到 ',V_NewLogisticsName),NOW());
					
				SET V_OrderLogisticsId=V_RetLogisticsId;
			END IF;
			
		END IF;	
		
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
			SELECT COUNT(1) INTO V_Count FROM stockout_order WHERE src_order_type = 3 AND src_order_id = V_OrderId AND outer_no = V_BizCode;
			IF V_Count <> 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 0;
				SET P_ErrorMsg = "该消息ID请求已处理";
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
		-- 校验是否开启了残次品管理
		SELECT is_defect,match_warehouse_id INTO V_IsDefect,V_MatchWarehouseId FROM cfg_warehouse WHERE warehouse_id = V_WarehouseId;
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
			END IF;
			
		END IF;
		
		OPEN order_cursor;
		ORDER_LABEL:LOOP
			FETCH order_cursor INTO V_WmsSpecNo,V_TmpRecId;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE ORDER_LABEL;
			END IF;
			 
			SELECT prd.rec_id,prd.spec_id INTO V_OrderDetailId,V_SpecId FROM purchase_return_detail prd
			WHERE prd.return_id = V_OrderId  
			AND prd.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT_WS('','单据信息异常，存在不明商品',V_WmsSpecNo);
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
		
			-- 残次品仓的采购退货单不允许出正品
			IF V_IsDefect = 1 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1006;
				SET P_ErrorMsg = CONCAT("残品仓采购退货单",P_OrderNo," 回传信息异常，存在正品商品!");
				LEAVE MAIN_LABEL;
			END IF;
			
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
		
			INSERT INTO stockout_order(stockout_no,warehouse_id,src_order_type,src_order_id,
			src_order_no,outer_no,logistics_id,logistics_no,post_cost,operator_id,`status`,pos_allocate_mode,remark,created)
			VALUES(V_StockOutOrderNo,V_WarehouseId,3,V_OrderId,V_OrderNo,V_BizCode,V_OrderLogisticsId,V_WmsOrderLogisticsNo,
			V_PostCost,0,50,0,V_WmsRemark,NOW());
		
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1007;
				SET P_ErrorMsg = '正品出库单信息录入异常,请重试!!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			SET V_NOT_FOUND = 0;
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,weight,base_unit_id,num,num2,unit_ratio,unit_id,
			goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,price,total_amount,position_id,remark)
			SELECT V_StockOutOrderId,3,prd.rec_id,twod.num*gs.weight,IF(gs.unit=0,gg.unit,gs.unit),twod.num,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),
			gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,prd.price,CAST(prd.price*twod.num AS DECIMAL(19,4)),IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id),prd.remark
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN purchase_return_detail prd ON prd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = prd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE ss.warehouse_id = V_WarehouseId; 
			
			-- 更新重量
			IF V_WmsWeight<>0 THEN 
				UPDATE stockout_order SET consign_status = (consign_status|2),weight=V_WmsWeight   
				WHERE stockout_id=V_StockOutOrderId ;
			END IF;

			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(price,0))
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId FOR UPDATE ;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_TotalPrice
			WHERE stockout_id=V_StockOutOrderId;	
					
			INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark) 
			VALUES(V_OrderId,0,15,CONCAT_WS('','采购退货对应的正品出库单:',V_StockOutOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(2,V_StockOutOrderId,0,74,'新建并递交出库单');	
		
			SET @cur_uid=0;
			CALL I_STOCKOUT_OTHER_CHECK(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','正品采购退货自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果开启了批次管理，需要插入明细到出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 5,V_OrderId,V_OrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0 
				ON DUPLICATE KEY UPDATE stockout_batch_detail.num = stockout_batch_detail.num+VALUES(stockout_batch_detail.num);
			END IF;
			
		END IF;
		
		-- 残次品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
		IF V_Count > 0 THEN
		
			-- 正品仓的采购退货不允许出残次品
			IF V_IsDefect = 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1008;
				SET P_ErrorMsg = CONCAT("正品仓采购退货单",P_OrderNo," 回传信息异常，存在残品商品!");
				LEAVE MAIN_LABEL;
			END IF;
			
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
		
			INSERT INTO stockout_order(stockout_no,warehouse_id,src_order_type,src_order_id,
			src_order_no,outer_no,logistics_id,logistics_no,post_cost,operator_id,`status`,pos_allocate_mode,remark,created)
			VALUES(V_StockOutOrderNo,V_WarehouseId,3,V_OrderId,V_OrderNo,V_BizCode,V_OrderLogisticsId,V_WmsOrderLogisticsNo,
			V_PostCost,0,50,0,V_WmsRemark,NOW());
		
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1009;
				SET P_ErrorMsg = '残品出库单信息录入异常,请重试!!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			SET V_NOT_FOUND = 0;
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,weight,base_unit_id,num,num2,unit_ratio,unit_id,
			goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,price,total_amount,position_id,remark)
			SELECT V_StockOutOrderId,3,prd.rec_id,twod.num*gs.weight,IF(gs.unit=0,gg.unit,gs.unit),twod.num,(twod.num/IFNULL(cau.base_ratio,1)),IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),
			gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,prd.price,CAST(prd.price*twod.num AS DECIMAL(19,4)),IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id),prd.remark
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN purchase_return_detail prd ON prd.rec_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = prd.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE ss.warehouse_id = V_WarehouseId; 
			
			-- 更新重量
			IF V_WmsWeight<>0 THEN 
				UPDATE stockout_order SET consign_status = (consign_status|2),weight=V_WmsWeight   
				WHERE stockout_id=V_StockOutOrderId ;
			END IF;
		
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(price,0))
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId FOR UPDATE ;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_TotalPrice
			WHERE stockout_id=V_StockOutOrderId;	
					
			INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark) 
			VALUES(V_OrderId,0,15,CONCAT_WS('','采购退货对应的残品出库单:',V_StockOutOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(2,V_StockOutOrderId,0,74,'新建并递交出库单');	
		
			SET @cur_uid=0;
			CALL I_STOCKOUT_OTHER_CHECK(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','残品采购退货自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果开启了批次管理，需要插入明细到出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 5,V_OrderId,V_OrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1
				ON DUPLICATE KEY UPDATE stockout_batch_detail.num = stockout_batch_detail.num+VALUES(stockout_batch_detail.num);
			END IF;
			
		END IF;
		
		UPDATE purchase_return SET wms_status=5,error_info='WMS推送出库信息' 
		WHERE return_id=V_OrderId ;
		
		IF V_ConfirmFlag = 0 THEN 
			UPDATE purchase_return SET wms_status=6,error_info='WMS推送最终出库信息' 
			WHERE return_id=V_OrderId ;
		END IF;
		
	ELSEIF V_WmsOrderStatus = 2 THEN 
		UPDATE purchase_return SET `status`=44,wms_status=1,error_info=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE return_id=V_OrderId ;
		INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark) VALUES(V_OrderId,0,16,'WMS拒绝接收委外出库单');
	ELSEIF V_WmsOrderStatus = 1 THEN 
		UPDATE purchase_return SET wms_status=2,error_info='WMS推送:已接单' 
		WHERE return_id=V_OrderId ;
	ELSE  
		UPDATE purchase_return SET error_info=CONCAT_WS('','WMS推送:', V_WmsOrderStatusName)
		WHERE return_id=V_OrderId ;
	END IF;
		
END$$
DELIMITER ;