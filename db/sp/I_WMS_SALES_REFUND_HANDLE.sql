DROP PROCEDURE IF EXISTS `I_WMS_SALES_REFUND_HANDLE`;

DELIMITER $$

CREATE  PROCEDURE `I_WMS_SALES_REFUND_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外销售退货单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND, V_Count INT(4) DEFAULT (0);
	DECLARE V_WmsOrderStatus, V_PlanFlag, V_WmsInventoryType TINYINT(2) ;
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo, V_WmsOrderStatusName, V_WmsBatch  VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId, V_WarehouseId, V_MatchWarehouseId , V_OrderWmsStatus, V_SpecId, V_TmpRecId INT(11);
	DECLARE V_OrderStatus, V_ConfirmFlag, V_WmsPdFlag TINYINT(1);
	DECLARE V_OrderNo, V_WmsSpecNo, V_Wmsno, V_OwnerNo, V_BizCode VARCHAR(50);
	DECLARE V_WmsRemark, V_WmsDeatilRemark, V_LogisticsList VARCHAR(256);
	DECLARE V_OrderLogisticsId  VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId INT DEFAULT (0);
	DECLARE V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_StockInOrderId, V_GoodsCount, V_GoodsTypeCount, V_OrderDetailId, V_CpStockInOrderId INT(11);
	DECLARE V_StockInOrderNo, V_CpStockInOrderNo VARCHAR(40);
	DECLARE V_TotalPrice, V_Discount, V_GoodsAmount, V_WmsWeight, V_WmsSpecNum, V_WmsPrice, V_DiffNum DECIMAL(19,4);
	DECLARE V_WmsProductDate,V_WmsExpireDate DATETIME DEFAULT '0000-00-00 00:00:00';
	DECLARE order_cursor CURSOR FOR SELECT rec_id,spec_no,num,price,batch,product_date,expire_date,inventory_type,pd_flag,remark FROM tmp_wms_order_detail;
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
	
	SELECT sr.refund_id, sr.warehouse_id, sr.refund_no, cl.logistics_id, sr.process_status ,sr.wms_status  
	INTO V_OrderId, V_WarehouseId, V_OrderNo, V_OrderLogisticsId, V_OrderStatus, V_OrderWmsStatus
	FROM sales_refund sr 
	LEFT JOIN cfg_logistics cl ON cl.logistics_name = sr.logistics_name 
	WHERE sr.outer_no = P_OrderNo;
	
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET  V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('销售退货单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 上次入库已经为最后一次入库,并且退货单状态为部分到货之后的状态，返回成功
	IF V_OrderWmsStatus = 6 AND V_OrderStatus >=70 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('入库单',P_OrderNo,' 已完成或上次入库为最终入库!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderWmsStatus = 5 AND V_OrderStatus >=80 THEN 
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('入库单',P_OrderNo,' 已完成!');
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_OrderStatus <> 65 AND V_OrderStatus <> 70 THEN 
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('销售退货单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 消息id处理
	IF V_BizCode<>'' THEN 
	
		SET V_Count=0;
		SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE  src_order_type=3 AND src_order_id=V_OrderId AND outer_no= V_BizCode;
		IF V_Count<>0 THEN 
			ROLLBACK;
			SET P_Code = 0;
			SET P_ErrorMsg = CONCAT('销售退货单',P_OrderNo,' 消息ID重复!');
			LEAVE MAIN_LABEL;	
			
		END IF;
	
	END IF;
	
	IF V_WmsOrderStatus = 6 THEN -- 入库操作
		
		-- 物流校验
		SET V_OrderLogisticsId = 0;
		IF V_WmsOrderLogisticsCode<>'' THEN 
			SELECT clw.logistics_id INTO V_RetLogisticsId   
			FROM cfg_logistics_wms clw  
			WHERE clw.logistics_code = V_WmsOrderLogisticsCode AND clw.warehouse_id = V_WarehouseId LIMIT 1;
			
			IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId THEN 
				SET V_OrderLogisticsId=V_RetLogisticsId;
			END IF;
			
		END IF;	
		
		
		-- 商品信息的有效性判断
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
		IF V_Count=0 THEN 
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1003;
			SET P_ErrorMsg = CONCAT('销售退货单',P_OrderNo,' 信息异常，无商品明细');
			LEAVE MAIN_LABEL;
		END IF;
		
		SET V_NOT_FOUND = 0;
		OPEN order_cursor;
		ORDER_LABEL:LOOP
			FETCH order_cursor INTO V_TmpRecId,V_WmsSpecNo,V_WmsSpecNum,V_WmsPrice,V_WmsBatch,V_WmsProductDate,V_WmsExpireDate,V_WmsInventoryType,V_WmsPdFlag,V_WmsDeatilRemark; 
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE ORDER_LABEL;
			END IF;

			SELECT sro.spec_id INTO V_SpecId FROM sales_refund_order sro  
			WHERE sro.refund_id = V_OrderId 
			AND sro.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1004;
				SET P_ErrorMsg = CONCAT_WS('','销售退货单信息异常，存在不明商品:',V_WmsSpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			-- 判断是否需要分摊
			SELECT COUNT(1),refund_order_id INTO V_Count,V_OrderDetailId FROM 
			(SELECT sro.refund_order_id FROM sales_refund_order sro LEFT JOIN tmp_wms_order_detail twod ON sro.refund_order_id = twod.order_detail_id 
			WHERE sro.refund_id = V_OrderId AND sro.spec_id = V_SpecId 
			GROUP BY sro.refund_order_id,sro.refund_num,sro.stockin_num HAVING sro.refund_num > sro.stockin_num+SUM(IFNULL(twod.num,0))
			) AS tmp ;
			IF V_Count = 0 THEN
				-- 没有匹配到，不需要分摊
				SELECT refund_order_id INTO V_OrderDetailId FROM sales_refund_order WHERE refund_id = V_OrderId AND spec_id = V_SpecId LIMIT 1;
				UPDATE tmp_wms_order_detail  SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId WHERE rec_id = V_TmpRecId;	
				
			ELSEIF V_Count = 1 THEN
				-- 只匹配到一行，不需要分摊
				UPDATE tmp_wms_order_detail  SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId WHERE rec_id = V_TmpRecId;	
				
			ELSE
				-- 匹配到多行，需要分摊(优先分摊到线上货品)
				REFUND_DETAIL_LABEL:LOOP
					SELECT sro.refund_order_id,sro.refund_num-sro.stockin_num-SUM(IFNULL(twod.num,0)) INTO V_OrderDetailId,V_DiffNum FROM sales_refund_order sro LEFT JOIN tmp_wms_order_detail twod ON sro.refund_order_id = twod.order_detail_id 
					WHERE sro.refund_id = V_OrderId AND sro.spec_id = V_SpecId 
					GROUP BY sro.refund_order_id,sro.refund_num,sro.stockin_num HAVING sro.refund_num > sro.stockin_num+SUM(IFNULL(twod.num,0)) ORDER BY sro.platform_id DESC LIMIT 1;
					IF V_NOT_FOUND THEN 
						SET V_NOT_FOUND=0;
						SELECT refund_order_id INTO V_OrderDetailId FROM sales_refund_order WHERE refund_id = V_OrderId AND spec_id = V_SpecId LIMIT 1;
						UPDATE tmp_wms_order_detail  SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId,num = V_WmsSpecNum WHERE rec_id = V_TmpRecId;
						LEAVE REFUND_DETAIL_LABEL;
					END IF;
					
					IF V_WmsSpecNum <= V_DiffNum THEN
						UPDATE tmp_wms_order_detail  SET order_detail_id = V_OrderDetailId,spec_id = V_SpecId,num = V_WmsSpecNum WHERE rec_id = V_TmpRecId;
						LEAVE REFUND_DETAIL_LABEL;
					ELSE
						SET V_WmsSpecNum = V_WmsSpecNum - V_DiffNum;
						INSERT INTO tmp_wms_order_detail(order_no,spec_no,num,price,batch,product_date,expire_date,inventory_type,pd_flag,remark,order_detail_id,spec_id)
						VALUES(P_OrderNo,V_WmsSpecNo,V_DiffNum,V_WmsPrice,V_WmsBatch,V_WmsProductDate,V_WmsExpireDate,V_WmsInventoryType,V_WmsPdFlag,V_WmsDeatilRemark,V_OrderDetailId,V_SpecId);
					END IF;
				END LOOP;
			END IF;
			
			
		END LOOP;
		CLOSE order_cursor;
		
		-- 是否存在残次品
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=1;
		
		-- 是否配置残次品库 
		SELECT IFNULL(csw.warehouse_id,0) INTO V_MatchWarehouseId FROM cfg_warehouse sw LEFT JOIN cfg_warehouse csw ON csw.warehouse_id = sw.match_warehouse_id AND csw.is_defect=1  AND csw.is_disabled=0 WHERE sw.warehouse_id =V_WarehouseId ; 
		
		SET V_StockInOrderId=0;
		SET V_CpStockInOrderId=0;
		IF V_MatchWarehouseId=0 OR (V_MatchWarehouseId<>0 AND V_Count=0) THEN  -- 没有配置,或开启配置，没有残次品则入正品库
			-- 创建入库单，并获取入库单号
			SET V_StockInOrderNo = FN_SYS_NO('stockin');
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
			,src_order_no,logistics_id,logistics_no,operator_id,`STATUS`,created, `remark`,outer_no) 
			VALUES(V_StockInOrderNo ,V_WarehouseId , 3,V_OrderId ,V_OrderNo ,V_OrderLogisticsId ,V_WmsOrderLogisticsNo ,1 ,30 ,NOW() ,V_WmsRemark,V_BizCode) ;		
			
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT('销售退货单',P_OrderNo,' 明细信息录入异常,请重试!!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
			expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created) 
			SELECT V_StockInOrderId ,3,sro.refund_order_id,sro.spec_id,-V_WarehouseId,twod.num,IF(gs.unit=0,gg.unit,gs.unit) AS unit 
				,twod.num,IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),(twod.num/IFNULL(cau.base_ratio,1)),sro.price
				,sro.cost_price  
				,sro.cost_price*IFNULL(cau.base_ratio,1) ,(sro.price-sro.cost_price)*twod.num
				,twod.num*sro.cost_price,NOW() 
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo GROUP BY order_detail_id ) AS twod   
			LEFT JOIN sales_refund_order sro ON sro.refund_order_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = IF(gs.unit=0,gg.aux_unit,gs.aux_unit)   
			WHERE sro.refund_id = V_OrderId;
			IF V_NOT_FOUND <> 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1006;
				SET P_ErrorMsg = CONCAT('入库单',P_OrderNo,' 明细异常,请重试!!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;	
			
			-- 更新入库单货品数量和货品种类 
			SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
			FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
			
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
			WHERE stockin_id=V_StockInOrderId ;
			
			
		ELSE -- 开启配置有残次
			
			-- 是否存在正品
			SET V_Count = 0;
			SET V_StockInOrderId = 0;
			SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=0;
			IF V_Count<>0 THEN -- 存在正品
				
				-- 创建入库单，并获取入库单号
				SET V_StockInOrderNo = FN_SYS_NO('stockin');
				INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
				,src_order_no,logistics_id,logistics_no,operator_id,`STATUS`,created, `remark`,outer_no) 
				VALUES(V_StockInOrderNo ,V_WarehouseId , 3,V_OrderId ,V_OrderNo ,V_OrderLogisticsId ,V_WmsOrderLogisticsNo ,1 ,30 ,NOW() ,V_WmsRemark,V_BizCode) ;		
				
				SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
				IF V_StockInOrderId <= 0 THEN 
					SET P_Code = 1007;
					SET P_ErrorMsg = CONCAT('退货单',P_OrderNo,' 明细信息录入异常,请重试!!');
					ROLLBACK;
					LEAVE MAIN_LABEL;
				END IF;
				
				-- 创建入库单明细
				SET V_NOT_FOUND = 0;
				INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created) 
				SELECT V_StockInOrderId ,3,sro.refund_order_id,sro.spec_id,-V_WarehouseId,twod.num,gg.unit 
					,twod.num,IFNULL(cau.base_ratio,1),gg.aux_unit,(twod.num/IFNULL(cau.base_ratio,1)),sro.price
					,sro.cost_price  
					,sro.cost_price*IFNULL(cau.base_ratio,1) ,(sro.price-sro.cost_price)*twod.num
					,twod.num*sro.cost_price,NOW() 
				FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=0  GROUP BY order_detail_id ) AS twod   
				LEFT JOIN sales_refund_order sro ON sro.refund_order_id = twod.order_detail_id 
				LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id   
				LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
				LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit  
				WHERE sro.refund_id = V_OrderId;
				
				-- 更新入库单货品数量和货品种类 
				SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
				INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
				FROM stockin_order_detail WHERE stockin_id = V_StockInOrderId FOR UPDATE ;
				
				UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
					total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
				WHERE stockin_id=V_StockInOrderId ;
			
			END IF;
			
			-- 残次品入库
				
			-- 创建入库单，并获取入库单号
			SET V_CpStockInOrderNo = FN_SYS_NO('stockin');
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id
			,src_order_no,logistics_id,logistics_no,operator_id,`STATUS`,created, `remark`,outer_no) 
			VALUES(V_CpStockInOrderNo,V_MatchWarehouseId , 3,V_OrderId ,V_OrderNo ,V_OrderLogisticsId ,V_WmsOrderLogisticsNo ,1 ,30 ,NOW() ,V_WmsRemark,V_BizCode) ;		
			
			SELECT LAST_INSERT_ID() INTO V_CpStockInOrderId;
			IF V_CpStockInOrderId <= 0 THEN 
				SET P_Code = 1008;
				SET P_ErrorMsg = CONCAT('销售退货入库单',P_OrderNo,' 录入异常,请重试!!');
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
				
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id, 
				expect_num,base_unit_id,num,unit_ratio,unit_id,num2,src_price,cost_price,cost_price2,discount,total_cost,created) 
			SELECT V_CpStockInOrderId ,3,sro.refund_order_id,sro.spec_id,-V_MatchWarehouseId,twod.num,gg.unit 
				,twod.num,IFNULL(cau.base_ratio,1),gg.aux_unit,(twod.num/IFNULL(cau.base_ratio,1)),sro.price
				,sro.cost_price  
				,sro.cost_price*IFNULL(cau.base_ratio,1) ,(sro.price-sro.cost_price)*twod.num
				,twod.num*sro.cost_price,NOW() 
			FROM (SELECT order_detail_id, SUM(num) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type=1 GROUP BY order_detail_id ) AS twod   
			LEFT JOIN sales_refund_order sro ON sro.refund_order_id = twod.order_detail_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id   
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit  
			WHERE sro.refund_id = V_OrderId;
			
			-- 更新入库单货品数量和货品种类 
			SELECT IFNULL(SUM(num),0), IFNULL(COUNT(DISTINCT spec_id),0), IFNULL(SUM(total_cost),0), IFNULL(SUM(discount),0),IFNULL((SUM(total_cost)+SUM(discount)),0) 
			INTO V_GoodsCount, V_GoodsTypeCount, V_TotalPrice, V_Discount, V_GoodsAmount 
			FROM stockin_order_detail WHERE stockin_id = V_CpStockInOrderId FOR UPDATE ;
				
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount, 
				total_price = V_TotalPrice, discount = V_Discount, goods_amount=V_GoodsAmount  
			WHERE stockin_id=V_CpStockInOrderId ;
			
		END IF;
		
		
		IF V_StockInOrderId<>0 THEN 
		
			-- 记录日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_StockInOrderId,0,13,'新建并递交入库单');	
			
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','销售退货单审核失败',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 插入批次表
			IF @batch_flag=1 THEN 
				INSERT INTO stockin_batch_detail(`TYPE`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,expire_date,product_date,num,remark,created) 
				SELECT 4,V_OrderId,V_OrderNo, V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,expire_date,product_date,SUM(twod.num),twod.remark, NOW()  
				FROM tmp_wms_order_detail twod  WHERE twod.order_no = P_OrderNo AND twod.inventory_type=0 GROUP BY twod.spec_id,twod.batch,twod.expire_date,twod.product_date
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		IF V_CpStockInOrderId<>0 THEN 
		
			-- 记录日志
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_CpStockInOrderId,0,13,'新建并递交入库单');	
			
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_CpStockInOrderId);
			IF @sys_code<>0 THEN
				
				ROLLBACK;
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','退货单审核失败',@sys_message),255);
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 插入批次表
			IF @batch_flag=1 THEN 
				INSERT INTO stockin_batch_detail(`TYPE`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,expire_date,product_date,num,remark,created) 
				SELECT 4,V_OrderId,V_OrderNo, V_CpStockInOrderId,V_CpStockInOrderNo,twod.spec_id,twod.batch,expire_date,product_date,SUM(twod.num),twod.remark, NOW()  
				FROM tmp_wms_order_detail twod  WHERE twod.order_no = P_OrderNo AND twod.inventory_type=1 GROUP BY twod.spec_id,twod.batch,twod.expire_date,twod.product_date
				ON DUPLICATE KEY UPDATE stockin_batch_detail.num = stockin_batch_detail.num+VALUES(stockin_batch_detail.num);
			END IF;
			
		END IF;
		
		UPDATE sales_refund SET wms_status=5,wms_result = '' WHERE refund_id=V_OrderId ;
		-- 最终入库标志 设置标记，防止下次重复入库
		IF V_ConfirmFlag = 0 THEN 
			UPDATE sales_refund SET wms_status=6,wms_result='wms推送最终入库信息' 
			WHERE refund_id=V_OrderId ;
		END IF;
	
		
	ELSEIF V_WmsOrderStatus = 2 THEN -- WMS拒绝接收单据
		UPDATE sales_refund SET process_status=64,wms_status=1,wms_result=CONCAT_WS('','WMS拒绝接收单据:', V_WmsOrderStatusName) 
		WHERE refund_id=V_OrderId ;
		INSERT INTO sales_refund_log(refund_id,operator_id,TYPE,remark) VALUES (V_OrderId,0,100,'WMS拒绝接收退货单');
	
	ELSEIF V_WmsOrderStatus = 1 THEN -- WMS成功接收单据
		UPDATE sales_refund SET wms_status=2,wms_result='WMS推送状态:已接单' WHERE refund_id=V_OrderId ;
	ELSE  
		UPDATE sales_refund SET wms_result=CONCAT_WS('','WMS推送状态:',V_WmsOrderStatusName) WHERE refund_id=V_OrderId ;
	END IF;
	
END$$
DELIMITER ;