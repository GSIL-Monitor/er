
DROP PROCEDURE IF EXISTS `I_WMS_SALES_TRADE_HANDLE`;

DELIMITER $$

CREATE  PROCEDURE `I_WMS_SALES_TRADE_HANDLE`(IN `P_OrderNo` VARCHAR(50),INOUT `P_Code` INT, INOUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外销售订单回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND ,V_Count, V_CheckDetail INT(4) DEFAULT (0);
	DECLARE V_WmsOrderType, V_WmsOrderStatus,V_PlanFlag TINYINT(2);
	DECLARE V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo  ,V_WmsOrderStatusName ,V_StockOutNo ,V_SnNo ,V_SpecNo VARCHAR(40) DEFAULT ('');
	DECLARE V_OrderId , V_WarehouseId, V_TradeId , V_OrderWmsStatus,V_SpecId,V_TmpRecId INT(11);
	DECLARE V_OrderStatus , V_ConfirmFlag,V_GoodsType TINYINT(1);
	DECLARE V_OrderNo ,V_WmsSpecNo,V_Wmsno,V_OwnerNo,V_BizCode,V_PackageCode,V_MaterialNo VARCHAR(50);
	DECLARE V_WmsRemark, V_MaterialErrMsg, V_SNErrMsg VARCHAR(256);
	DECLARE V_LogisticsList VARCHAR(1024);
	DECLARE V_OrderLogisticsId,V_OldLogisticsName,V_NewLogisticsName VARCHAR(64) DEFAULT ('') ;
	DECLARE V_RetLogisticsId,V_RetLogisticsType,V_MaterialAmount INT DEFAULT (0);
	DECLARE V_WmsOuterNO VARCHAR(64) DEFAULT ('') ;
	DECLARE V_OrderDetailId INT(11);
	DECLARE V_WmsWeight DECIMAL(19,4);
	DECLARE V_OldPostCost,V_PostCost DECIMAL(19,4);
	DECLARE order_cursor CURSOR FOR SELECT spec_no,rec_id FROM tmp_wms_order_detail;
	DECLARE trade_sn_cursor CURSOR FOR SELECT stockout_no,spec_no,sn_no FROM tmp_trade_sn_list;
	DECLARE order_package_material_cursor CURSOR FOR SELECT rec_id,material,num,package_code FROM tmp_wms_order_package_material_detail;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SELECT `order_type` , `status` , `status_name` ,`logistics_code` , `logistics_no`, `weight`,`undefined4`, `remark`, `confirm_flag` ,`logistics_list` , `wms_no` ,`owner_no`, `order_plan_flag`,`biz_code`  
	INTO V_WmsOrderType, V_WmsOrderStatus, V_WmsOrderStatusName, V_WmsOrderLogisticsCode, V_WmsOrderLogisticsNo , V_WmsWeight , V_WmsOuterNO, V_WmsRemark, V_ConfirmFlag, V_LogisticsList , V_Wmsno , V_OwnerNo ,V_PlanFlag ,V_BizCode   
	FROM tmp_wms_order WHERE order_no = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = -1;
		SET P_ErrorMsg = "服务器错误请稍后重试";
		LEAVE MAIN_LABEL;
	END IF;
		
	-- 获取系统内订单信息，并进行有效性判断
	SELECT so.stockout_id,so.post_cost,so.status, st.trade_id, st.trade_no, so.warehouse_id, so.wms_status, so.logistics_id 
	INTO V_OrderId, V_OldPostCost, V_OrderStatus, V_TradeId, V_OrderNo, V_WarehouseId,V_OrderWmsStatus, V_OrderLogisticsId 
	FROM stockout_order so 
	LEFT JOIN sales_trade st ON so.src_order_type=1 AND so.src_order_id=st.trade_id 
	WHERE so.stockout_no = P_OrderNo ;
	
	IF  V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND = 0;
		SET P_Code = 1001;
		SET P_ErrorMsg = CONCAT('出库单',P_OrderNo,' 不存在!');
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 更新outer_no 字段
	IF V_WmsOuterNO<>'' THEN
		UPDATE stockout_order SET outer_no = V_WmsOuterNO WHERE stockout_no = P_OrderNo ;
	END IF;
	
	IF V_OrderStatus >= 95 AND V_OrderWmsStatus = 5 AND V_WmsOrderStatus<>5 THEN
		ROLLBACK;
		SET P_Code = 0;
		SET P_ErrorMsg = CONCAT('出库单',P_OrderNo,' 已出库!');
		LEAVE MAIN_LABEL;
	END IF;
		
	IF  V_OrderStatus <> 60 AND (V_WmsOrderStatus <> 5 OR  V_OrderStatus < 95) THEN
		ROLLBACK;
		SET P_Code = 1002;
		SET P_ErrorMsg = CONCAT('出库单',P_OrderNo,' 状态错误!');
		LEAVE MAIN_LABEL;
	END IF;
	
	
	-- 根据单据的不同状态值进行不同的操作
	IF V_WmsOrderStatus = 6 THEN -- 出库操作
		-- 物流校验
		SELECT clw.logistics_id, cl.logistics_type, cl.logistics_name INTO V_RetLogisticsId,V_RetLogisticsType,V_NewLogisticsName
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
		
		-- logistics_type 1  无物流单号
		IF V_WmsOrderLogisticsNo = '' AND V_RetLogisticsType <> 1 THEN 
			ROLLBACK;
			SET P_Code = 1004;
			SET P_ErrorMsg = '物流单号不能为空!';
			LEAVE MAIN_LABEL;
		END IF;
		
		-- 更新物流信息
		IF V_RetLogisticsId<>0 AND V_RetLogisticsId<>V_OrderLogisticsId  THEN 
			-- 如果有物流单号，则是我们这边获取单号传递到仓储，并且不修改物流公司
			IF EXISTS(SELECT logistics_no FROM stockout_order WHERE stockout_id=V_OrderId AND logistics_no = '') THEN
				UPDATE sales_trade st,stockout_order so 
				SET st.logistics_id=V_RetLogisticsId ,so.logistics_id=V_RetLogisticsId 
				WHERE so.src_order_type=1 AND so.src_order_id=st.trade_id AND so.stockout_id=V_OrderId ; 
				-- 记录日志	
				SELECT logistics_name INTO V_OldLogisticsName FROM cfg_logistics WHERE logistics_id=V_OrderLogisticsId;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message,created)
				VALUES(V_TradeId,0,20,CONCAT('WMS回传物流变更,从:',V_OldLogisticsName,' 到 ',V_NewLogisticsName),NOW());
			END IF;
		END IF;
		
		
		-- 商品信息校验	
		
		
		-- SELECT IFNULL(@tmp_check_detail,0) INTO V_CheckDetail;
		-- IF V_CheckDetail > 0 THEN 
		
		IF @tmp_check_detail>0 OR @batch_flag=1 THEN 
			
			-- 是否存在商品
			SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo LIMIT 1;
			IF V_Count=0 THEN                                                                                                        
				ROLLBACK;
				SET P_Code = 1005;
				SET P_ErrorMsg = "订单信息异常，无商品明细!";
				LEAVE MAIN_LABEL;
			END IF;
			
			-- 是否有残次品
			SET V_Count = 0;
			SELECT COUNT(1) INTO V_Count FROM tmp_wms_order_detail WHERE order_no = P_OrderNo  AND inventory_type=1;
			IF V_Count<>0 THEN 
				ROLLBACK;
				SET P_Code = 1006;
				SET P_ErrorMsg = "销售出库单不允许出残次品!";
				LEAVE MAIN_LABEL;
			END IF;
			
			OPEN order_cursor;
			ORDER_LABEL:LOOP
				FETCH order_cursor INTO V_WmsSpecNo,V_TmpRecId; 
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE ORDER_LABEL;
				END IF;
				
				SELECT sod.rec_id,sod.spec_id INTO V_OrderDetailId,V_SpecId FROM stockout_order_detail sod 
				WHERE sod.stockout_id = V_OrderId  
				AND sod.spec_id IN (SELECT gs.spec_id FROM goods_spec gs WHERE gs.spec_no = V_WmsSpecNo AND gs.deleted = 0) LIMIT 1;
				IF V_NOT_FOUND = 1 THEN 
					ROLLBACK;
					SET V_NOT_FOUND=0;
					SET P_Code = 1007;
					SET P_ErrorMsg = CONCAT_WS('','订单信息异常，存在不明商品:',V_WmsSpecNo);
					LEAVE MAIN_LABEL;
				END IF;
				
				UPDATE tmp_wms_order_detail twod SET twod.order_detail_id = V_OrderDetailId,twod.spec_id = V_SpecId WHERE twod.rec_id=V_TmpRecId;
				SET V_OrderDetailId = 0;	
				
			END LOOP;
			CLOSE order_cursor;
			
			-- 判断商品数量是否一样
			SET V_Count=0;
			SELECT COUNT(1) INTO V_Count FROM (SELECT sod.spec_id,SUM(sod.num) AS erp_num FROM stockout_order_detail sod WHERE sod.stockout_id = V_OrderId GROUP BY sod.spec_id) AS tsod
			LEFT JOIN (SELECT twod.spec_id,SUM(twod.num) AS wms_num FROM tmp_wms_order_detail twod WHERE twod.order_no = P_OrderNo GROUP BY twod.spec_id ) AS twods  ON tsod.spec_id=twods.spec_id 
			WHERE twods.wms_num<>tsod.erp_num;
			IF V_Count <>0 THEN 
				ROLLBACK;
				SET P_Code = 1008;
				SET P_ErrorMsg = '出库单不是全部出库，商品数量不正确';
				LEAVE MAIN_LABEL;
			END IF;
				
		END IF;
		
		-- SN码的检验&处理
		IF @sn_flag THEN
			-- 校验SN码临时表是否有数据
			SELECT COUNT(1) INTO V_Count FROM tmp_trade_sn_list WHERE stockout_no = P_OrderNo;
			IF V_Count <> 0 THEN 
	
				SET V_SNErrMsg = '';
				OPEN trade_sn_cursor;
				SN_LABEL:LOOP
					FETCH trade_sn_cursor INTO V_StockOutNo,V_SpecNo,V_SnNo;
					IF V_NOT_FOUND THEN
						SET V_NOT_FOUND=0;
						LEAVE SN_LABEL;
					END IF;
					
					SELECT COUNT(1) INTO V_Count FROM goods_spec gs LEFT JOIN stockout_order_detail sod ON gs.spec_id = sod.spec_id  
					WHERE gs.spec_no = V_SpecNo AND gs.deleted = 0;
					
					IF V_Count = 0 THEN 
						ROLLBACK;
						SET P_Code = 1009;
						SET P_ErrorMsg = CONCAT('出库单商品',V_SpecNo,' 不存在!');
						LEAVE MAIN_LABEL;
					END IF;

				END LOOP;
				CLOSE trade_sn_cursor;	
			
				INSERT INTO trade_sn_list(stockout_id,trade_id,stockout_no,spec_no,sn_no,version_id,created)
				SELECT V_OrderId,so.src_order_id,ttsl.stockout_no,ttsl.spec_no,ttsl.sn_no,0,NOW()
				FROM tmp_trade_sn_list ttsl				
				LEFT JOIN stockout_order so ON so.stockout_no = ttsl.stockout_no
				WHERE  ttsl.stockout_no = P_OrderNo;
			END IF;
			
		END IF;					
		
		-- 开启计算邮资配置 并且wms回传了重量
		IF @calc_post_cost AND V_WmsWeight<>0 THEN
			   SET V_PostCost='';
			   -- 调用存储过程计算邮资
			   CALL SP_CALC_POST_COST(V_PostCost,V_OrderId,V_WmsWeight);
			   -- 将邮资插入到表中
			   UPDATE stockout_order SET post_cost=V_PostCost
			   WHERE stockout_id=V_OrderId; 
			   -- 插入日志
			   INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message,created)
			   VALUES(V_TradeId,0,303,CONCAT('WMS回传邮资变更,从:',V_OldPostCost,' 到 ',V_PostCost),NOW());
		END IF;	   
			   	
		-- 包材信息校验&处理
		IF @package_material_flag THEN 
			SET V_MaterialErrMsg = '';
			OPEN order_package_material_cursor;
			PACKAGE_LABEL:LOOP
				FETCH order_package_material_cursor INTO V_TmpRecId,V_MaterialNo,V_MaterialAmount,V_PackageCode; 
				IF V_NOT_FOUND THEN
					SET V_NOT_FOUND=0;
					LEAVE PACKAGE_LABEL;
				END IF;
				
				IF V_MaterialNo = '-1' AND V_MaterialAmount = -1 THEN
					SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，不存在包材信息;'));
					ITERATE PACKAGE_LABEL;
				END IF;
				
				IF V_MaterialNo = '' THEN
					SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号不存在或为空;'));
					ITERATE PACKAGE_LABEL;
				END IF;
				
				IF V_MaterialAmount = 0 THEN
					SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号',V_MaterialNo,'数量不存在或为0;'));
					ITERATE PACKAGE_LABEL;
				END IF;
				
				-- 判断是否在仓库中存在(1商家编码2条码)
				IF @package_material_flag = 1 THEN
					SELECT gs.spec_id, gg.goods_type INTO V_SpecId,V_GoodsType 
					FROM goods_spec gs 
					LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
					LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
					WHERE gs.spec_no = V_MaterialNo AND ss.warehouse_id = V_WarehouseId AND ss.status = 1 AND gs.deleted = 0;
					IF V_NOT_FOUND = 1 THEN 
						SET V_NOT_FOUND=0;
						SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号',V_MaterialNo,'在仓库中不存在;'));
						ITERATE PACKAGE_LABEL;
					END IF;
				ELSEIF @package_material_flag = 2 THEN
					SELECT COUNT(1),gs.spec_id, gg.goods_type INTO V_Count,V_SpecId,V_GoodsType
					FROM goods_spec gs 
					LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
					LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id 
					WHERE gs.barcode = V_MaterialNo AND ss.warehouse_id = V_WarehouseId AND ss.status = 1 AND gs.deleted = 0;
					IF V_Count = 0 THEN
						SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号(条码)',V_MaterialNo,'在仓库中不存在;'));
						ITERATE PACKAGE_LABEL;
					ELSEIF V_Count > 1 THEN
						SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号(条码)',V_MaterialNo,'在仓库中匹配多条货品信息;'));
						ITERATE PACKAGE_LABEL;
					END IF;
				END IF;
				
				-- 判断是否为包装
				IF V_GoodsType <> 3 THEN
					SET V_MaterialErrMsg = CONCAT(V_MaterialErrMsg,CONCAT('包裹',V_PackageCode,'信息异常，包材型号',V_MaterialNo,'在系统中非包装类型;'));
					ITERATE PACKAGE_LABEL;
				END IF;
				
				UPDATE tmp_wms_order_package_material_detail SET spec_id = V_SpecId WHERE rec_id = V_TmpRecId;
				
			END LOOP;
			CLOSE order_package_material_cursor;
			
			-- 插入出库单明细
			INSERT INTO stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,weight,base_unit_id,num,num2,unit_ratio,unit_id,
			goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,price,total_amount,position_id,is_package)
			SELECT V_OrderId,1,0,SUM(twopmd.num)*gs.weight,IF(gs.unit=0,gg.unit,gs.unit),SUM(twopmd.num),(SUM(twopmd.num)/IFNULL(cau.base_ratio,1)),IFNULL(cau.base_ratio,1),IF(gs.unit=0,gg.aux_unit,gs.aux_unit),
			gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code,IFNULL(ss.cost_price,0),CAST(IFNULL(ss.cost_price,0)*SUM(twopmd.num) AS DECIMAL(19,4)),IF(IFNULL(ss.default_position_id,0) = 0,-V_WarehouseId,ss.default_position_id),1
			FROM tmp_wms_order_package_material_detail twopmd
			LEFT JOIN goods_spec gs ON twopmd.spec_id = gs.spec_id
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
			LEFT JOIN cfg_goods_aux_unit cau ON cau.rec_id = gg.aux_unit 
			LEFT JOIN stock_spec ss ON ss.spec_id = gs.spec_id AND ss.warehouse_id = V_WarehouseId
			WHERE twopmd.spec_id <> 0 GROUP BY twopmd.spec_id ;
			
			-- 插入日志
			IF V_MaterialErrMsg <> '' THEN
				SET P_ErrorMsg = V_MaterialErrMsg;
				INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
				VALUES(V_TradeId ,0,302,V_MaterialErrMsg);
			END IF;
			
		END IF;
		
		-- 更新运货单号等物流信息
		UPDATE sales_trade SET logistics_no=V_WmsOrderLogisticsNo WHERE trade_id=V_TradeId;
		
		UPDATE stockout_order SET logistics_no=V_WmsOrderLogisticsNo,wms_status=2,error_info='DELIVERED'  
		WHERE stockout_id=V_OrderId ;
		
		
		IF V_WmsWeight<>0 THEN 
			UPDATE stockout_order SET consign_status = (consign_status|2),weight=V_WmsWeight   
			WHERE stockout_id=V_OrderId ;
		END IF;
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
		VALUES(V_TradeId ,0,300,CONCAT_WS('','WMS回传发货信息,物流:',V_WmsOrderLogisticsCode,',单号:',V_WmsOrderLogisticsNo));
		
		IF @logistics_flag=1 THEN
		
			INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) 
			VALUES(V_TradeId ,0,300,CONCAT_WS('','WMS回传多物流发货信息:',V_LogisticsList));
			
			UPDATE tmp_logistics_list tll,cfg_logistics_wms clw SET tll.logistics_id = clw.logistics_id
			WHERE clw.logistics_code = tll.logistics_code AND clw.warehouse_id = V_WarehouseId;
				
			SELECT COUNT(1),logistics_code INTO V_Count,V_WmsOrderLogisticsCode FROM tmp_logistics_list
			WHERE logistics_id = '';
				IF  V_Count <> 0 THEN 
					SET P_Code = 1010;
					SET P_ErrorMsg = CONCAT('物流公司',V_WmsOrderLogisticsCode,' 不存在!');
					LEAVE MAIN_LABEL;
				END IF;
			
				INSERT INTO sales_record_multi_logistics(operator_id,trade_id,logistics_no,logistics_id,created)
			SELECT 0,V_TradeId,tll.logistics_no,tll.logistics_id,NOW()
			FROM tmp_logistics_list tll;
			
		END IF;
		
		-- 调用出库单出库的存储过程，并进行异常判断
		SET @cur_uid=0;
		CALL I_STOCKOUT_SALES_CONSIGN(V_OrderId, 1, 1);
		IF @sys_code<>0 THEN
			SET P_Code = @sys_code;
			SET P_ErrorMsg = LEFT(CONCAT_WS('','销售出库单审核失败:',@sys_message,';',V_MaterialErrMsg),255);
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
		
		
		UPDATE stockout_order SET wms_status= 5,error_info=V_WmsRemark WHERE stockout_id=V_OrderId ;
		
		-- 启用批次管理抓入批次中间表
		IF @batch_flag=1 THEN
			
			INSERT INTO stockout_batch_detail(`type`,src_order_id,src_order_no,stockout_id,stockout_no,spec_id,batch,expire_date,product_date,num,remark,created) 
				SELECT 1,V_TradeId,V_OrderNo, V_OrderId,P_OrderNo,twod.spec_id,twod.batch,twod.expire_date,twod.product_date,twod.num  ,twod.remark, NOW()  
				FROM tmp_wms_order_detail twod  WHERE twod.order_no = P_OrderNo AND twod.inventory_type=0
				ON DUPLICATE KEY UPDATE stockout_batch_detail.num = stockout_batch_detail.num+VALUES(stockout_batch_detail.num);
		END IF;
	
	ELSEIF V_WmsOrderStatus = 2 THEN -- WMS拒绝接收单据
		UPDATE stockout_order SET `status`=53,error_info=CONCAT_WS('','WMS拒绝接收订单:', V_WmsOrderStatusName)  
		WHERE stockout_id=V_OrderId ;
		INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message) VALUES(V_TradeId ,0,300,'WMS拒绝接收订单');
	ELSEIF V_WmsOrderStatus = 1 THEN -- WMS成功接收单据
		UPDATE stockout_order SET wms_status=2,error_info='WMS推送:已接单' WHERE stockout_id=V_OrderId;
	ELSE  
		UPDATE stockout_order SET error_info=CONCAT_WS('','WMS推送:', V_WmsOrderStatusName) WHERE stockout_id=V_OrderId;
	END IF;
	
END$$
DELIMITER ;

