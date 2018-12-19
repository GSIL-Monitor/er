DROP PROCEDURE IF EXISTS `I_WMS_STOCK_PD_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `I_WMS_STOCK_PD_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外盘点单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count INT(4) DEFAULT (0);
	DECLARE V_IsDefect,V_WmsOrderType,V_WmsOrderStatus,V_PlanFlag TINYINT(2);
	DECLARE V_WarehouseId,V_MatchWarehouseId,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_InventoryType,V_ConfirmFlag TINYINT(1);
	DECLARE V_WmsSpecNo,V_Wmsno,V_OwnerNo,V_BizCode VARCHAR(50);
	DECLARE V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_WmsRemark,V_LogisticsList VARCHAR(256);
	DECLARE V_StockInOrderId,V_StockOutOrderId,V_GoodsCount ,V_GoodsTypeCount ,V_StockPdId INT(11) DEFAULT (0);
	DECLARE V_StockInOrderNo,V_StockOutOrderNo,V_StockPdOrderNo,V_WmsOrderStatusName,V_WmsOrderLogisticsCode,V_WmsOrderLogisticsNo VARCHAR(40) DEFAULT ('') ;
	DECLARE V_TotalPrice,V_GoodsAmount,V_WmsWeight DECIMAL(19,4);
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
	
	-- 判断是否有一仓多货主的情况
	SELECT COUNT(1) INTO V_Count FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno;
	
	IF V_Count = 0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT("外部编号",V_Wmsno," 仓库不存在!");
		LEAVE MAIN_LABEL;
	ELSEIF V_Count = 1 THEN
		SELECT warehouse_id INTO V_WarehouseId FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno;
	ELSE 
		SELECT COUNT(1) INTO V_Count FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno AND api_object_id = '';
		
		IF  V_Count <> 0 THEN
			ROLLBACK;
			SET V_NOT_FOUND=0;
			SET P_Code = 1002;
			SET P_ErrorMsg = CONCAT("外部编号",V_Wmsno," 仓库授权信息配置有误，请联系旺店通技术人员处理!");
			LEAVE MAIN_LABEL;
		ELSE 
			SELECT warehouse_id INTO V_WarehouseId FROM cfg_warehouse WHERE ext_warehouse_no = V_Wmsno AND api_object_id = V_OwnerNo;
			IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1003;
				SET P_ErrorMsg = CONCAT("货主编码:",V_OwnerNo,"不正确，未找到相应仓库信息");
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
	END IF;
	
	-- 单据明细判断
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo;
	IF V_Count=0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 1004;
		SET P_ErrorMsg = "单据信息异常，无商品明细!";
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 校验消息ID
	SET V_Count = 0;
	SELECT COUNT(1) INTO V_Count FROM stockin_order WHERE src_order_type = 4  AND outer_no = V_BizCode;
	IF V_Count <> 0 THEN
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 0;
		SET P_ErrorMsg = "该消息ID请求已处理(盘盈)";
		LEAVE MAIN_LABEL;
	END IF;
	
	SET V_Count = 0;
	SELECT COUNT(1) INTO V_Count FROM stockout_order WHERE src_order_type = 4  AND outer_no = V_BizCode;
	IF V_Count <> 0 THEN
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = 0;
		SET P_ErrorMsg = "该消息ID请求已处理(盘亏)";
		LEAVE MAIN_LABEL;
	END IF;
	
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
	
	-- 校验商品	
	OPEN order_cursor;
		ORDER_LABEL:LOOP
			FETCH order_cursor INTO V_WmsSpecNo,V_TmpRecId;
			IF V_NOT_FOUND THEN
				SET V_NOT_FOUND=0;
				LEAVE ORDER_LABEL;
			END IF;
			SELECT inventory_type INTO V_InventoryType FROM tmp_wms_order_detail WHERE rec_id = V_TmpRecId;			
			SELECT gs.spec_id INTO  V_SpecId FROM  stock_spec ss 
			LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id 
			WHERE  ss.warehouse_id = IF(V_InventoryType = 1 AND V_IsDefect = 0,V_MatchWarehouseId,V_WarehouseId) AND gs.spec_no = V_WmsSpecNo  AND gs.deleted = 0;
			 
			 IF V_NOT_FOUND = 1 THEN 
				ROLLBACK;
				SET V_NOT_FOUND=0;
				SET P_Code = 1005;
				SET P_ErrorMsg = CONCAT_WS('','委外盘点单回传信息异常，商品不存在，仓库ID:',IF(V_InventoryType = 1 AND V_IsDefect = 0,V_MatchWarehouseId,V_WarehouseId),' 商品编码:',V_WmsSpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			
			UPDATE tmp_wms_order_detail SET spec_id = V_SpecId WHERE rec_id = V_TmpRecId;
		END LOOP;
	CLOSE order_cursor;
		
	-- 正品处理
	SET V_Count = 0;
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0;
	IF V_Count > 0 THEN
	
		-- 创建盘点单
		SELECT FN_SYS_NO('stockpd') INTO V_StockPdOrderNo;
		INSERT INTO stock_pd(pd_no,outer_no,creator_id,`status`,warehouse_id, `mode`,remark, created)
		VALUES(V_StockPdOrderNo,P_OrderNo,0,2,V_WarehouseId,0,V_WmsRemark,NOW());
		
		SELECT LAST_INSERT_ID() INTO V_StockPdId;
		IF V_StockPdId <= 0 THEN 
			SET V_NOT_FOUND=0; 
			SET P_Code = 1006;
			SET P_ErrorMsg = '正品盘点单信息录入异常,请重试!';
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 创建盘点单明细
		INSERT INTO stock_pd_detail(pd_id,spec_id,position_id,old_num,input_num,new_num,cost_price,stock_spec_detail_id,pd_flag,created)
		SELECT V_StockPdId,gs.spec_id,IF(ss.default_position_id <> 0,ss.default_position_id,-V_WarehouseId),ss.stock_num,ss.stock_num+twod.num,
			   ss.stock_num+twod.num,ss.cost_price,ss.rec_id,1,NOW()
		FROM (SELECT spec_no,pd_flag,SUM(IF(pd_flag = 1,num,-num)) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 0 GROUP BY spec_id) AS twod    
		LEFT JOIN goods_spec gs ON gs.spec_no =  twod.spec_no  
		LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id  
		WHERE ss.warehouse_id = V_WarehouseId;
		
		-- 记录日志
		INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message)
		VALUES(4,V_StockPdId,0,13,'WMS回传新建并递交正品盘点单');
		
		
		-- 盘盈入库
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM stock_pd_detail WHERE pd_id = V_StockPdId AND new_num > old_num;
		
		IF V_Count<>0 THEN 
		
			-- 创建入库单，并获取入库单号
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id,src_order_no,outer_no,operator_id,`status`,remark,created)  
			VALUES(V_StockInOrderNo ,V_WarehouseId,4,V_StockPdId,V_StockPdOrderNo,V_BizCode,0,30,V_WmsRemark,NOW()) ;		
				
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1007;
				SET P_ErrorMsg = '正品入库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
				
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id,
						num,unit_id,base_unit_id,src_price,tax_price,tax_amount,cost_price,total_cost,created)
			SELECT V_StockInOrderId,4,spd.rec_id,gs.spec_id,IF(ss.default_position_id <> 0,ss.default_position_id,-V_WarehouseId),spd.new_num-spd.old_num,
					gs.aux_unit,gs.unit,ss.cost_price,ss.cost_price,ss.cost_price*(spd.new_num-spd.old_num),ss.cost_price,ss.cost_price*(spd.new_num-spd.old_num),NOW()
			FROM stock_pd_detail spd
			LEFT JOIN goods_spec gs ON gs.spec_id = spd.spec_id
			LEFT JOIN stock_spec ss ON gs.spec_id = ss.spec_id
			WHERE spd.pd_id =V_StockPdId AND spd.new_num > spd.old_num AND ss.warehouse_id = V_WarehouseId;
					
			-- 更新入库单货品数量和货品种类
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0)),SUM(IFNULL(num,0)*IFNULL(src_price,0)) 
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice,V_GoodsAmount
			FROM stockin_order_detail WHERE stockin_id=V_StockInOrderId FOR UPDATE ;
			
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,total_price=V_TotalPrice,discount=V_GoodsAmount-V_TotalPrice WHERE stockin_id = V_StockInOrderId;
			
			-- 记录日志			
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_StockInOrderId,0,72,'新建并递交委外正品盘盈入库单');
					
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','盘点入库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		-- 如果开启了批次管理，需要插入明细到入库单/出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 6,V_StockPdId,V_StockPdOrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,SUM(twod.num),twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0 AND twod.pd_flag = 1 GROUP BY spec_id,batch,expire_date;
			END IF;
		SET V_StockInOrderId = 0;
		SET V_StockInOrderNo = '';
			
		-- 盘亏出库
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM stock_pd_detail WHERE pd_id = V_StockPdId AND new_num < old_num;
		
		IF V_Count<>0 THEN 
			
			-- 创建出库单，并获取出库单号
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
			
			INSERT INTO stockout_order(stockout_no,src_order_type,src_order_id,src_order_no,outer_no,`status`,warehouse_id,operator_id,pos_allocate_mode,remark,created)
			VALUES(V_StockOutOrderNo,4,V_StockPdId,V_StockPdOrderNo,V_BizCode,50,V_WarehouseId,0,0,V_WmsRemark,NOW());
			
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1008;
				SET P_ErrorMsg = '正品出库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 创建出库单明细
			SET V_NOT_FOUND = 0;
			
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,base_unit_id,unit_id,unit_ratio,num2,num,price,total_amount,
						goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,weight,position_id,created)
			SELECT V_StockOutOrderId,4,spd.rec_id,IF(gs.unit=0,gg.unit,gs.unit),IF(gs.aux_unit=0,gg.aux_unit,gs.aux_unit),IFNULL(cau.base_ratio,1),
				   (spd.old_num-spd.new_num)/IFNULL(cau.base_ratio,1),spd.old_num-spd.new_num,IFNULL(ss.cost_price,0),(spd.old_num-spd.new_num)*IFNULL(ss.cost_price,0),
				   gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,(spd.old_num-spd.new_num)*gs.weight,IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id),NOW()
			FROM stock_pd_detail spd
			LEFT JOIN goods_spec gs ON gs.spec_id = spd.spec_id
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE spd.pd_id =V_StockPdId AND spd.old_num > spd.new_num AND ss.warehouse_id = V_WarehouseId; 

			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0))
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId FOR UPDATE ;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_TotalPrice
			WHERE stockout_id=V_StockOutOrderId;	
						
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(2,V_StockOutOrderId,0,74,'新建并递交委外正品盘亏出库单');	
			
			SET @cur_uid=0;
			CALL I_STOCKOUT_OTHER_CHECK(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','盘点出库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		-- 如果开启了批次管理，需要插入明细到入库单/出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 6,V_StockPdId,V_StockPdOrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,SUM(twod.num),twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 0 AND twod.pd_flag = 2 GROUP BY spec_id,batch,expire_date;
			END IF;
		SET V_StockOutOrderId = 0;
		SET V_StockOutOrderNo = '';
		END IF;
		
	-- 残次品处理
	SET V_Count = 0;
	SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1;
	IF V_Count > 0 THEN
	
		IF V_IsDefect = 0 THEN
			SET V_WarehouseId = V_MatchWarehouseId;
		END IF;
		
		-- 创建盘点单
		SELECT FN_SYS_NO('stockpd') INTO V_StockPdOrderNo;
		INSERT INTO stock_pd(pd_no,outer_no,creator_id,`status`,warehouse_id, `mode`,remark, created)
		VALUES(V_StockPdOrderNo,P_OrderNo,0,40,V_WarehouseId,0,V_WmsRemark,NOW());
		
		SELECT LAST_INSERT_ID() INTO V_StockPdId;
		IF V_StockPdId <= 0 THEN 
			SET V_NOT_FOUND=0; 
			SET P_Code = 1009;
			SET P_ErrorMsg = '残品盘点单信息录入异常,请重试!';
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 创建盘点单明细
		INSERT INTO stock_pd_detail(pd_id,spec_id,position_id,old_num,input_num,new_num,cost_price,stock_spec_detail_id,pd_flag,created)
		SELECT V_StockPdId,gs.spec_id,IF(ss.default_position_id <> 0,ss.default_position_id,-V_WarehouseId),ss.stock_num,ss.stock_num+twod.num,
		       ss.stock_num+twod.num,ss.cost_price,ss.rec_id,1,NOW()
		FROM (SELECT spec_no,pd_flag,SUM(IF(pd_flag = 1,num,-num)) AS num FROM tmp_wms_order_detail WHERE order_no = P_OrderNo AND inventory_type = 1 GROUP BY spec_id) AS twod    
		LEFT JOIN goods_spec gs ON gs.spec_no =  twod.spec_no  
		LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id  
		WHERE ss.warehouse_id = V_WarehouseId;
		
		-- 记录日志
		INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message)
		VALUES(4,V_StockPdId,0,13,'WMS回传新建并递交残品盘点单');
		
		
		-- 盘盈入库
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM stock_pd_detail WHERE pd_id = V_StockPdId AND new_num > old_num;
		
		IF V_Count<>0 THEN 
		
			-- 创建入库单，并获取入库单号
			SELECT FN_SYS_NO('stockin') INTO V_StockInOrderNo;
			INSERT INTO stockin_order(stockin_no,warehouse_id,src_order_type,src_order_id,src_order_no,outer_no,operator_id,`status`,remark,created)  
			VALUES(V_StockInOrderNo ,V_WarehouseId,4,V_StockPdId,V_StockPdOrderNo,V_BizCode,0,30,V_WmsRemark,NOW()) ;		
				
			SELECT LAST_INSERT_ID() INTO V_StockInOrderId;
			IF V_StockInOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1010;
				SET P_ErrorMsg = '残品入库单信息录入异常,请重试!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
				
			-- 创建入库单明细
			SET V_NOT_FOUND = 0;
			INSERT INTO stockin_order_detail(stockin_id,src_order_type,src_order_detail_id,spec_id,position_id,
						num,unit_id,base_unit_id,src_price,tax_price,tax_amount,cost_price,total_cost,created)
			SELECT V_StockInOrderId,4,spd.rec_id,gs.spec_id,IF(ss.default_position_id <> 0,ss.default_position_id,-V_WarehouseId),spd.new_num-spd.old_num,
					 gs.aux_unit,gs.unit,ss.cost_price,ss.cost_price,ss.cost_price*(spd.new_num-spd.old_num),ss.cost_price,ss.cost_price*(spd.new_num-spd.old_num),NOW()
			FROM stock_pd_detail spd
			LEFT JOIN goods_spec gs ON gs.spec_id = spd.spec_id
			LEFT JOIN stock_spec ss ON gs.spec_id = ss.spec_id
			WHERE spd.pd_id =V_StockPdId AND spd.new_num > spd.old_num AND ss.warehouse_id = V_WarehouseId;
			
			-- 更新入库单货品数量和货品种类
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0)),SUM(IFNULL(num,0)*IFNULL(src_price,0)) 
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice,V_GoodsAmount
			FROM stockin_order_detail WHERE stockin_id=V_StockInOrderId FOR UPDATE ;
			
			UPDATE stockin_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,
			goods_amount=V_GoodsAmount,total_price=V_TotalPrice,discount=V_GoodsAmount-V_TotalPrice WHERE stockin_id = V_StockInOrderId;
			
			-- 记录日志			
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(1,V_StockInOrderId,0,72,'新建并递交委外残品盘盈入库单');
					
			-- 调用存储过程，进行入库单审核，并对调用结果进行判断
			SET @cur_uid=0;
			CALL I_STOCKIN_CHECK(V_StockInOrderId);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','盘点入库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
		END IF;
		-- 如果开启了批次管理，需要插入明细到入库单/出库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockin_batch_detail(`type`,src_order_id,src_order_no,stockin_id,stockin_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 6,V_StockPdId,V_StockPdOrderNo,V_StockInOrderId,V_StockInOrderNo,twod.spec_id,twod.batch,SUM(twod.num),twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1 AND twod.pd_flag = 1 GROUP BY spec_id,batch,expire_date;
			END IF;
		SET V_StockInOrderId = 0;
		SET V_StockInOrderNo = '';
		-- 盘亏出库
		SET V_Count = 0;
		SELECT COUNT(1) INTO V_Count FROM stock_pd_detail WHERE pd_id = V_StockPdId AND new_num < old_num;
		
		IF V_Count<>0 THEN 
			
			-- 创建出库单，并获取出库单号
			SELECT FN_SYS_NO('stockout') INTO V_StockOutOrderNo;
			
			INSERT INTO stockout_order(stockout_no,src_order_type,src_order_id,src_order_no,outer_no,`status`,warehouse_id,operator_id,pos_allocate_mode,remark,created)
			VALUES(V_StockOutOrderNo,4,V_StockPdId,V_StockPdOrderNo,V_BizCode,50,V_WarehouseId,0,0,V_WmsRemark,NOW());
			
			SELECT LAST_INSERT_ID() INTO V_StockOutOrderId;
			IF V_StockOutOrderId <= 0 THEN 
				SET V_NOT_FOUND=0; 
				SET P_Code = 1011;
				SET P_ErrorMsg = '残品出库单信息录入异常,请重试!!';
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 创建出库单明细
			SET V_NOT_FOUND = 0;
			
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,base_unit_id,unit_id,unit_ratio,num2,num,price,total_amount,
						goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,weight,position_id,created)
			SELECT V_StockOutOrderId,4,spd.rec_id,IF(gs.unit=0,gg.unit,gs.unit),IF(gs.aux_unit=0,gg.aux_unit,gs.aux_unit),IFNULL(cau.base_ratio,1),
				   (spd.old_num-spd.new_num)/IFNULL(cau.base_ratio,1),spd.old_num-spd.new_num,IFNULL(ss.cost_price,0),(spd.old_num-spd.new_num)*IFNULL(ss.cost_price,0),
				   gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,(spd.old_num-spd.new_num)*gs.weight,IF(ss.default_position_id = 0,-V_WarehouseId,default_position_id),NOW()
			FROM stock_pd_detail spd
			LEFT JOIN goods_spec gs ON gs.spec_id = spd.spec_id
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
			WHERE spd.pd_id =V_StockPdId AND spd.old_num > spd.new_num AND ss.warehouse_id = V_WarehouseId; 	
				
			SELECT COUNT(DISTINCT spec_id),IFNULL(SUM(num),0),SUM(IFNULL(num,0)*IFNULL(cost_price,0))
			INTO V_GoodsTypeCount,V_GoodsCount,V_TotalPrice
			FROM stockout_order_detail WHERE stockout_id=V_StockOutOrderId FOR UPDATE ;
			UPDATE stockout_order SET goods_count=V_GoodsCount,goods_type_count=V_GoodsTypeCount,goods_total_amount=V_TotalPrice
			WHERE stockout_id=V_StockOutOrderId;	
						
			INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) 
			VALUES(2,V_StockOutOrderId,0,74,'新建并递交委外残品盘亏出库单');	
			
			SET @cur_uid=0;
			CALL I_STOCKOUT_OTHER_CHECK(V_StockOutOrderId,0,0);
			IF @sys_code<>0 THEN
				SET P_Code = @sys_code;
				SET P_ErrorMsg = LEFT(CONCAT_WS('','盘点出库单自动审核失败 ',@sys_message),255);
				ROLLBACK;
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		-- 如果开启了批次管理，需要插入明细到入库单批次中间表
			IF @batch_flag = 1 THEN
				INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,num,product_date,expire_date,created)
				SELECT 6,V_StockPdId,V_StockPdOrderNo,V_StockOutOrderId,V_StockOutOrderNo,twod.spec_id,twod.batch,SUM(twod.num),twod.product_date,twod.expire_date,NOW()
				FROM tmp_wms_order_detail twod
				WHERE twod.order_no = P_OrderNo AND twod.inventory_type = 1 AND twod.pd_flag = 2 GROUP BY spec_id,batch,expire_date;
			END IF;
		SET V_StockOutOrderId = 0;
		SET V_StockOutOrderNo = '';
		END IF;
	
END$$
DELIMITER ;