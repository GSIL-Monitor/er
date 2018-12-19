
DROP PROCEDURE IF EXISTS `I_WMS_STOCKIN_OTHER_HANDLE`;

DELIMITER $$

CREATE  PROCEDURE `I_WMS_STOCKIN_OTHER_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外入库单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus,V_IsDefect,V_PlanFlag TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode,V_WmsOrderLogisticsNo, V_WmsOrderStatusName VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId,V_MatchWarehouseId , V_OrderWmsStatus,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_Wmsno,V_OwnerNo,V_BizCode VARCHAR(50);
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId ,V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId INT DEFAULT (0);
	DECLARE V_StockInOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId INT(11);
	DECLARE V_StockInOrderNo VARCHAR(40);
	DECLARE V_TotalPrice ,V_GoodsAmount,V_WmsWeight DECIMAL(19,4);
	DECLARE order_cursor CURSOR FOR SELECT spec_no,rec_id FROM tmp_wms_order_detail;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SELECT `status` , `status_name` ,`logistics_code` , `logistics_no`, `weight`,`undefined4`, `remark`, `confirm_flag` ,`logistics_list` , `wms_no` ,`owner_no`, `order_plan_flag`,`biz_code`  
	INTO V_WmsOrderStatus, V_WmsOrderStatusName, V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo, V_WmsWeight, V_WmsOuterNO, V_WmsRemark, V_ConfirmFlag, V_LogisticsList , V_Wmsno, V_OwnerNo,V_PlanFlag,V_BizCode   
	FROM tmp_wms_order WHERE order_no = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = -1;
		SET P_ErrorMsg = "服务器错误请稍后重试";
		LEAVE MAIN_LABEL;
	END IF;
	
	SELECT owo.order_id , owo.status , owo.warehouse_id ,owo.order_no , owo.logistics_id , owo.wms_status 
	INTO V_OrderId, V_OrderStatus, V_WarehouseId, V_OrderNo, V_OrderLogisticsId , V_OrderWmsStatus 
	FROM outside_wms_order owo 
	WHERE owo.outer_no = P_OrderNo;
	
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('委外入库单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
		
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=80 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('委外入库单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=75 AND V_ConfirmFlag =0 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('委外入库单',P_OrderNo,' 上次入库为最终入库!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderStatus <> 65 AND V_OrderStatus <> 75 THEN 
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('委外入库单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	
	
	IF V_WmsOrderStatus = 6 THEN 
		
		IF V_WmsOrderLogisticsCode<>'' THEN 
		
			SELECT clw.logistics_id INTO V_RetLogisticsId 
			FROM cfg_logistics_wms clw 
			WHERE clw.logistics_code = V_WmsOrderLogisticsCode AND clw.warehouse_id = V_WarehouseId  LIMIT 1;
			
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId  THEN 
				SET V_OrderLogisticsId=V_RetLogisticsId;
			END IF;
			
		END IF;	
		
		
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
		IF V_Count=0 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1003;
			SET P_ErrorMsg = CONCAT('委外入库单',P_OrderNo,' 回传信息异常，无商品明细');
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 开启了消息id校验
		IF V_BizCode <> '' THEN
			SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE src_order_type = 12 AND src_order_id = V_OrderId AND outer_no = V_BizCode;
			IF V_Count <> 0 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 0;
				SET P_ErrorMsg = "该消息id请求已处理";
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
			 
			SELECT owod.rec_id,owod.spec_id INTO V_OrderDetailId,V_SpecId FROM outside_wms_order_detail owod
			WHERE owod.order_id = V_OrderId  
			AND owod.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1004;
				SET P_ErrorMsg = CONCAT_WS('','委外入库单回传信息异常，存在不明商品',V_WmsSpecNo);
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
		
			-- 残次品仓的委外入库单不允许入正品
			IF V_IsDefect = 1 THEN
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT('残次品仓委外入库单',P_OrderNo,' 回传信息异常，存在正品商品');
				LEAVE MAIN_LABEL;
			END IF;
		
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id,src_order_no,outer_no,logistics_id,operator_id,`status`,created,remark)  
			VALUES(V_StockInOrderNo ,V_WarehouseId ,12 ,V_OrderId ,V_OrderNo ,V_BizCode ,V_OrderLogisticsId , 0,30,NOW() ,V_WmsRemark) ;		
		
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1006;
				SET P_ErrorMsg = CONCAT('正品入库单',P_OrderNo,' 信息录入异常,请重试!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
		
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id,
			base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created)
			SELECT V_StockInOrderId,12,owod.rec_id,owod.spec_id,-V_WarehouseId,
			owod.base_unit_id,twod.num,owod.unit_ratio,owod.unit_id,(twod.num/owod.unit_ratio),owod.price,
			owod.price,owod.price*owod.unit_ratio,0,twod.num*owod.price,NOW()
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 GROUP BY order_detail_id ) AS twod  
			LEFT JOIN outside_wms_order_detail owod ON owod.rec_id = twod.order_detail_id   
			WHERE  owod.order_id = V_OrderId;
		
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0)),SUM(IFNULL(num,0)*IFNULL(src_price,0)) 
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice,V_GoodsAmount
			FROM stockin_order_detail WHERE stockin_id=V_StockInOrderId FOR UPDATE ;
		
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,total_price=V_TotalPrice,discount=V_GoodsAmount-V_TotalPrice WHERE stockin_id = V_StockInOrderId;
					
			INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) 
				VALUES(V_OrderId,0,10,CONCAT_WS('','委外入库单对应的正品入库单:',V_StockInOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_StockInOrderId,0,72,'新建并递交入库单');	
		
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','正品委外入库单自动审核失败:',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果开启了批次管理，需要插入明细到入库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 5,V_OrderId,V_OrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		-- 残次品处理
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
		IF V_Count > 0 THEN
		
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id,src_order_no,outer_no,logistics_id,operator_id,`status`,created,remark)  
			VALUES(V_StockInOrderNo ,IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId) ,12 ,V_OrderId ,V_OrderNo ,V_BizCode ,V_OrderLogisticsId , 0,30,NOW() ,V_WmsRemark) ;		
		
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1007;
				SET P_ErrorMsg = CONCAT('残次品入库单',P_OrderNo,' 信息录入异常,请重试!!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
		
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id,
			base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created)
			SELECT V_StockInOrderId,12,owod.rec_id,owod.spec_id,-IF(V_IsDefect = 1,V_WarehouseId,V_MatchWarehouseId),
			owod.base_unit_id,twod.num,owod.unit_ratio,owod.unit_id,(twod.num/owod.unit_ratio),owod.price,
			owod.price,owod.price*owod.unit_ratio,0,twod.num*owod.price,NOW()
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 GROUP BY order_detail_id ) AS twod  
			LEFT JOIN outside_wms_order_detail owod ON owod.rec_id = twod.order_detail_id   
			WHERE  owod.order_id = V_OrderId;
		
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0)),SUM(IFNULL(num,0)*IFNULL(src_price,0)) 
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice,V_GoodsAmount
			FROM stockin_order_detail WHERE stockin_id=V_StockInOrderId FOR UPDATE ;
		
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,total_price=V_TotalPrice,discount=V_GoodsAmount-V_TotalPrice WHERE stockin_id = V_StockInOrderId;
					
			INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) 
				VALUES(V_OrderId,0,11,CONCAT_WS('','委外入库单对应的残次品入库单:',V_StockInOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_StockInOrderId,0,72,'新建并递交入库单');	
		
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','残次品委外入库单自动审核失败:',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 如果开启了批次管理，需要插入明细到入库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 5,V_OrderId,V_OrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.num,twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		UPDATE outside_wms_order SET wms_status=5,error_info='wms推送入库信息' 
		WHERE order_id=V_OrderId ;
		
		IF V_ConfirmFlag = 0 THEN 
			UPDATE outside_wms_order SET wms_status=6,error_info='wms推送最终入库信息' 
			WHERE order_id=V_OrderId ;
		END IF;
		
	ELSEIF V_WmsOrderStatus = 2 THEN 
		UPDATE outside_wms_order SET `status`=50,wms_status=1,error_info=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE order_id=V_OrderId ;
		INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) VALUES(V_OrderId,0,40,'WMS拒绝接收委外入库单');
	ELSEIF V_WmsOrderStatus = 1 THEN 
		
		UPDATE outside_wms_order SET wms_status=2,error_info='WMS推送:已接单' 
		WHERE order_id=V_OrderId ;
	ELSE  
		UPDATE outside_wms_order SET error_info=CONCAT_WS('','WMS推送:', V_WmsOrderStatusName)
		WHERE order_id=V_OrderId ;
	END IF;
	
END$$
DELIMITER ;
