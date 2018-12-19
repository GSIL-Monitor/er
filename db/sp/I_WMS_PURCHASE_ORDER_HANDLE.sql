DROP PROCEDURE IF EXISTS `I_WMS_PURCHASE_ORDER_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `I_WMS_PURCHASE_ORDER_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外采购单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus,V_PlanFlag TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo  ,V_WmsOrderStatusName VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId,V_MatchWarehouseId , V_OrderWmsStatus,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_Wmsno,V_OwnerNo,V_BizCode VARCHAR(50);
	DECLARE V_WmsRemark ,V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId  VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId INT DEFAULT (0);
	DECLARE V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_StockInOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_OrderDetailId,V_CpStockInOrderId,V_TotalCount INT(11);
	DECLARE V_StockInOrderNo,V_CpStockInOrderNo VARCHAR(40);
	DECLARE V_TotalPrice ,V_Discount,V_GoodsAmount , V_WmsWeight,V_TotalTax DECIMAL(19,4);
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
	-- 获取系统内采购单信息，并进行有效性判断
	SELECT po.purchase_id , po.status , po.warehouse_id ,po.purchase_no , po.logistics_type , po.wms_status 
	INTO V_OrderId, V_OrderStatus, V_WarehouseId, V_OrderNo, V_OrderLogisticsId , V_OrderWmsStatus 
	FROM purchase_order po 
	WHERE po.outer_no = P_OrderNo;
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=70 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 上次入库已经为最后一次入库,并且采购单状态为部分到货之后的状态，返回成功
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=50 AND V_ConfirmFlag =0 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 上次为最终入库!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 48,已审核；50 部分到货
	IF V_OrderStatus <> 48 AND V_OrderStatus <> 50 THEN 
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	-- 消息id判断 
	IF V_BizCode<>'' THEN 
		SET V_Count=0;
		SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE  src_order_type=1 AND src_order_id=V_OrderId AND outer_no= V_BizCode;
		IF V_Count<>0 THEN 
			ROLLBACK;
			SET P_Code = 0;
			SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 消息ID重复!');
			LEAVE MAIN_LABEL;	
		END IF;
	END IF;
	IF V_WmsOrderStatus = 6 THEN -- 入库操作
		-- 物流信息更新
		IF V_WmsOrderLogisticsCode<>'' THEN
			SELECT clw.logistics_id INTO V_RetLogisticsId 
			FROM cfg_logistics_wms clw 
			WHERE clw.logistics_code = V_WmsOrderLogisticsCode AND clw.warehouse_id = V_WarehouseId  LIMIT 1;
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId  THEN 
				SET V_OrderLogisticsId=V_RetLogisticsId;
			END IF;
		END IF;
		-- 商品信息的有效性判断
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
		IF V_Count=0 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1003;
			SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 信息异常，无商品明细!');
			LEAVE MAIN_LABEL;
		END IF;
		OPEN order_cursor;
		ORDER_LABEL:LOOP
			FETCH order_cursor INTO V_WmsSpecNo,V_TmpRecId;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE ORDER_LABEL;
			END IF;
			SELECT pod.rec_id ,pod.spec_id INTO V_OrderDetailId,V_SpecId FROM purchase_order_detail pod 
			WHERE pod.purchase_id = V_OrderId  
			AND pod.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1004;
				SET P_ErrorMsg = CONCAT_WS('','采购单信息异常，存在不明商品',V_WmsSpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			UPDATE tmp_wms_order_detail twod SET twod.order_detail_id = V_OrderDetailId, twod.spec_id = V_SpecId WHERE twod.rec_id = V_TmpRecId;
			SET V_OrderDetailId = 0;
		END LOOP;
		CLOSE order_cursor;
		-- 是否存在残次品
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=1;
		-- 是否配置残次品库
		SELECT IFNULL(csw.warehouse_id,0) INTO V_MatchWarehouseId FROM cfg_warehouse sw LEFT JOIN cfg_warehouse csw ON csw.warehouse_id = sw.match_warehouse_id AND csw.is_defect=1  AND csw.is_disabled=0 WHERE sw.warehouse_id =V_WarehouseId ;

		SET V_StockInOrderId=0;
		SET V_CpStockInOrderId=0;
		IF V_MatchWarehouseId=0 OR (V_MatchWarehouseId<>0 AND V_Count=0)THEN  -- 没有配置,或开启配置，没有残次品入正品库
		
			-- 创建入库单，并获取入库单号
			
			SET V_StockInOrderNo = FN_SYS_NO('stockin');
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
				,src_order_no,logistics_id,operator_id,`status`,created,remark,outer_no)  
			VALUES(V_StockInOrderNo,V_WarehouseId ,1 ,V_OrderId ,V_OrderNo ,V_OrderLogisticsId , 1,30,NOW() ,V_WmsRemark,V_BizCode) ;		
			
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 明细信息录入异常,请重试!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created,tax,tax_price,tax_amount) 
			SELECT V_StockInOrderId ,1,pod.rec_id,pod.spec_id,-V_WarehouseId,pod.num-pod.arrive_num -pod.stockin_num,pod.base_unit_id
				,twod.num,pod.unit_ratio,pod.unit_id,(twod.num/pod.unit_ratio),pod.price
				,CAST(pod.price*pod.discount AS DECIMAL(19,4)) AS cost_price 
				,pod.price*pod.discount*pod.unit_ratio,(pod.price-pod.price*pod.discount)*twod.num
				,twod.num*pod.price*pod.discount,NOW() ,pod.tax, pod.tax_price,CAST(pod.tax_price*twod.num AS DECIMAL(19,4)) AS tax_amount   
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo GROUP BY order_detail_id ) AS twod  
			LEFT JOIN purchase_order_detail pod ON pod.rec_id = twod.order_detail_id   
			WHERE  pod.purchase_id = V_OrderId;
			
			-- 更新入库单货品数量和货品种类 
			SELECT SUM(num), COUNT(DISTINCT spec_id), SUM(total_cost), SUM(discount), (SUM(total_cost)+SUM(discount)),SUM(tax_amount) 
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount ,V_TotalTax
			FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
			
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  ,tax_amount=V_TotalTax 
			WHERE stockin_id=V_StockInOrderId ;
			
			SET V_TotalCount = V_GoodsCount;
		
			
			-- 更新stock_spec中的信息 入正品库
			INSERT INTO stock_spec(warehouse_id,spec_id,`status`,purchase_arrive_num) 
			SELECT V_WarehouseId ,pod.spec_id,1,twod.num
			FROM (SELECT order_no,order_detail_id,inventory_type, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  GROUP BY order_detail_id ) AS twod  
			LEFT JOIN purchase_order_detail pod ON pod.rec_id = twod.order_detail_id   
			ON DUPLICATE KEY 
			UPDATE purchase_arrive_num = purchase_arrive_num + VALUES(stock_spec.purchase_arrive_num),stock_spec.status=1;
			
		ELSE -- 开启配置有残次
			
			-- 是否存在正品
			SET V_Count = 0;
			SET V_StockInOrderId = 0;
			SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=0;
			IF V_Count<>0 THEN -- 存在正品
				
				-- 创建入库单，并获取入库单号
				SET V_StockInOrderNo = FN_SYS_NO('stockin');
				INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
					,src_order_no,logistics_id,operator_id,`status`,created,remark,outer_no)  
				VALUES(V_StockInOrderNo ,V_WarehouseId ,1 ,V_OrderId ,V_OrderNo ,V_OrderLogisticsId , 1,30,NOW() ,V_WmsRemark,V_BizCode) ;		
				
				SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
				IF V_StockInOrderId <= 0 THEN 
					SET V_NOT_FOUND=0; 
					SET P_Code = 1006;
					SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 明细信息录入异常,请重试!!');
					ROLLBACK;
					LEAVE MAIN_LABEL;
				END IF;
				
				-- 创建入库单明细
				SET V_NOT_FOUND = 0;
				INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
					expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created,tax,tax_price,tax_amount) 
				SELECT V_StockInOrderId ,1,pod.rec_id,pod.spec_id,-V_WarehouseId,pod.num-pod.arrive_num -pod.stockin_num,pod.base_unit_id
					,twod.num,pod.unit_ratio,pod.unit_id,(twod.num/pod.unit_ratio),pod.price
					,CAST(pod.price AS DECIMAL(19,4)) AS cost_price 
					,pod.price*pod.discount*pod.unit_ratio,(pod.price-pod.price*pod.discount)*twod.num
					,twod.num*pod.price*pod.discount,NOW() ,pod.tax, pod.tax_price,CAST(pod.tax_price*twod.num AS DECIMAL(19,4)) AS tax_amount   
				FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=0 GROUP BY order_detail_id ) AS twod  
				LEFT JOIN purchase_order_detail pod ON pod.rec_id = twod.order_detail_id   
				WHERE  pod.purchase_id = V_OrderId;
				
				-- 更新入库单货品数量和货品种类 
				SELECT SUM(num), COUNT(DISTINCT spec_id), SUM(total_cost), SUM(discount), (SUM(total_cost)+SUM(discount)) ,SUM(tax_amount)
				INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount ,V_TotalTax
				FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
				
				UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
					total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  ,tax_amount=V_TotalTax 
				WHERE stockin_id=V_StockInOrderId ;
				
				SET V_TotalCount = V_GoodsCount;
				
			END IF;
			
			-- 残次品入库
				
			-- 创建入库单，并获取入库单号
			SET V_CpStockInOrderNo = FN_SYS_NO('stockin');
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
				,src_order_no,logistics_id,operator_id,`status`,created,remark,outer_no)  
			VALUES(V_CpStockInOrderNo ,V_MatchWarehouseId ,1 ,V_OrderId ,V_OrderNo ,V_OrderLogisticsId , 1,30,NOW() ,V_WmsRemark,V_BizCode) ;		
				
			SELECT LAST_INSERT_ID() INTO V_CpStockInOrderId;
			IF V_CpStockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1007;
				SET P_ErrorMsg = CONCAT('采购单',P_OrderNo,' 明细信息录入异常,请重试!!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
				
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created,tax,tax_price,tax_amount) 
			SELECT V_CpStockInOrderId ,1,pod.rec_id,pod.spec_id,-V_MatchWarehouseId,pod.num-pod.arrive_num -pod.stockin_num,pod.base_unit_id
				,twod.num,pod.unit_ratio,pod.unit_id,(twod.num/pod.unit_ratio),pod.price
				,CAST(pod.price*pod.discount AS DECIMAL(19,4)) AS cost_price 
				,pod.price*pod.discount*pod.unit_ratio,(pod.price-pod.price*pod.discount)*twod.num
				,twod.num*pod.price*pod.discount,NOW() ,pod.tax, pod.tax_price,CAST(pod.tax_price*twod.num AS DECIMAL(19,4)) AS tax_amount   
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type=1 GROUP BY order_detail_id ) AS twod  
			LEFT JOIN purchase_order_detail pod ON pod.rec_id = twod.order_detail_id   
			WHERE  pod.purchase_id = V_OrderId;
			
			-- 更新入库单货品数量和货品种类 
			SELECT SUM(num), COUNT(DISTINCT spec_id), SUM(total_cost), SUM(discount), (SUM(total_cost)+SUM(discount)) ,SUM(tax_amount)
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount ,V_TotalTax
			FROM stockin_order_detail WHERE stockin_id = V_CpStockInOrderId FOR UPDATE ;
			
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  ,tax_amount=V_TotalTax 
			WHERE stockin_id=V_CpStockInOrderId ;
			
			SET V_TotalCount = V_TotalCount + V_GoodsCount; 
			
			-- 更新stock_spec中的信息 正品入正品库残次品入残次品库
			INSERT INTO stock_spec(warehouse_id,spec_id,`status`,purchase_arrive_num) 
			SELECT IF(inventory_type=0,V_WarehouseId,V_MatchWarehouseId) ,pod.spec_id,1,twod.num
			FROM (SELECT order_no,order_detail_id,inventory_type,SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  GROUP BY order_detail_id,inventory_type) AS twod  
			LEFT JOIN purchase_order_detail pod ON pod.rec_id = twod.order_detail_id   
			ON DUPLICATE KEY 
			UPDATE purchase_arrive_num = purchase_arrive_num + VALUES(stock_spec.purchase_arrive_num),stock_spec.status=1;
				
		END IF;
		
		
		-- 更新采购单的正残货品到货量
		UPDATE purchase_order_detail pod, (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail GROUP BY order_detail_id ) AS twod  
		SET pod.arrive_num = pod.arrive_num+twod.num   
		WHERE pod.purchase_id=V_OrderId AND pod.rec_id = twod.order_detail_id;
		-- 更新采购正残在途数量
		UPDATE stock_spec ss,purchase_order_detail pod,(SELECT spec_id, stockin_id,src_order_detail_id,src_order_type, SUM(num) AS num FROM stockin_order_detail WHERE `src_order_type` = 1 AND `stockin_id` IN(V_StockInOrderId,V_CpStockInOrderId) GROUP BY spec_id) sod SET 
		ss.purchase_num = IF(pod.arrive_num +pod.stockin_num > pod.num,
		IF(pod.`arrive_num`+pod.stockin_num - sod.num > pod.`num`,ss.`purchase_num` - 0,ss.`purchase_num`-pod.`num`+pod.`arrive_num` + pod.stockin_num-sod.num),
		IF(sod.num>ss.purchase_num,0,ss.purchase_num - sod.`num`))
		WHERE pod.`rec_id` = sod.`src_order_detail_id` AND ss.`spec_id` = sod.`spec_id`
		AND ss.`warehouse_id` = V_WarehouseId ;
		
		
		
		IF V_StockInOrderId<>0 THEN 
		
			-- 记录日志
			INSERT INTO purchase_order_log(purchase_id,operator_id,`type`,remark) 
				VALUES(V_OrderId,0,70,CONCAT_WS('','采购单对应的入库单',V_StockInOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_StockInOrderId,0,13,'新建并递交入库单');
			
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','采购入库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 插入批次表
			IF @batch_flag=1 THEN 
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,expire_date,product_date,num,remark,created) 
				SELECT 1,V_OrderId,V_OrderNo, V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,twod.expire_date,twod.product_date,twod.num,twod.remark, NOW()  
				FROM tmp_wms_order_detail twod  WHERE twod.order_no = P_OrderNo AND twod.inventory_type=0 
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		IF V_CpStockInOrderId<>0 THEN 
		
			-- 记录日志
			INSERT INTO purchase_order_log(purchase_id,operator_id,`type`,remark) 
				VALUES(V_OrderId,0,70,CONCAT_WS('','采购单对应的残次品入库单',V_CpStockInOrderNo,'递交'));
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
				VALUES(1,V_CpStockInOrderId,0,13,'新建并递交残次品入库单');
				
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_CpStockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','采购残次入库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;	
			
			-- 插入批次表
			IF @batch_flag=1 THEN 
					
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,expire_date,product_date,num,remark,created) 
				SELECT 1,V_OrderId,V_OrderNo, V_CpStockInOrderId,V_CpStockInOrderNo,twod.spec_id,twod.batch,twod.expire_date,twod.product_date,twod.num,twod.remark, NOW()  
				FROM tmp_wms_order_detail twod  WHERE twod.order_no = P_OrderNo AND twod.inventory_type=1  
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
				
			END IF;
			
		END IF;
		
		
		UPDATE purchase_order SET wms_status=5,error_info='WMS推送入库信息' ,goods_arrive_count=goods_arrive_count+V_TotalCount
		WHERE purchase_id=V_OrderId ;
		
		-- 最终入库标志 设置标记，防止下次重复入库
		IF V_ConfirmFlag = 0 THEN 
			UPDATE purchase_order SET wms_status=6,error_info='WMS推送最终入库信息' 
			WHERE purchase_id=V_OrderId ;
		END IF;
		
		
	ELSEIF V_WmsOrderStatus = 2 THEN -- WMS拒绝接收单据
		UPDATE purchase_order SET `status`=45,wms_status=1,error_info=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE purchase_id=V_OrderId ;
		INSERT INTO purchase_order_log(purchase_id,operator_id,`type`,remark) VALUES(V_OrderId,0,70,'WMS拒绝接收采购单');
	ELSEIF V_WmsOrderStatus = 1 THEN -- WMS成功接收单据
		
		UPDATE purchase_order SET wms_status=2,error_info='WMS推送:已接单' 
		WHERE purchase_id=V_OrderId ;
	ELSE  
		UPDATE purchase_order SET error_info=CONCAT_WS('','WMS推送:', V_WmsOrderStatusName)
		WHERE purchase_id=V_OrderId ;
	END IF;

END$$	
DELIMITER ;