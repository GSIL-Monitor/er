
DROP PROCEDURE IF EXISTS `I_STOCKOUT_ORDER_REVERT_CHECK2`;
DELIMITER //
CREATE PROCEDURE `I_STOCKOUT_ORDER_REVERT_CHECK2`(IN `P_StockoutId` INT(11),
	IN `P_ReasonId` INT , 
	IN `P_LogisticsStatus` INT, 
	IN `P_SendibllStatus` INT,
	IN `P_Force` INT,
	IN `P_Occupy` INT)
    SQL SECURITY INVOKER
    COMMENT '驳回审核'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_StockoutId,V_PlatformId,V_SrcOrderId,V_SrcOrderType,V_Status,V_ConsignStatus,V_OrderMask,
		V_CheckouterId,V_IsAllocated,V_WarehouseId,V_BlockReason,V_ShopId,V_LogisticsId,V_TradeCount,V_CustomerId,V_WmsStatus,V_TradeID,
		V_ElectronicInvoiceID,V_ElectronicInvoiceStatus,V_InvoiceCategory,V_NewElectronicInvoiceID,V_MinInvoiceID,V_AccountSync INT DEFAULT(0);
	DECLARE V_Reason VARCHAR(255);
	DECLARE V_StockoutNo,V_SrcOrderNO,V_LogisticsNo,V_BatchNO,V_AccountCheckNO,V_PicklistNo,V_SnNo,V_StockinNo VARCHAR(40);
	DECLARE V_ElectronicInvoiceNO VARCHAR(20);
	DECLARE V_ScanForbiddenCancel,V_ConfignForbiddenCancel,V_SnType,V_DeleteFlag,V_SnStatus,V_SnId INT DEFAULT(0);
	DECLARE V_Weight,V_PostCost,V_Receivable,V_Profit DECIMAL(19,4);

	 -- ---------SN-BEGIN---------
	 DECLARE V_SpecId,V_IsSnEnable,V_Count INT DEFAULT(0);
	 DECLARE V_GoodsName VARCHAR(255);
	 DECLARE V_SpecNo VARCHAR(40);
	 DECLARE V_Num DECIMAL(19,4);
	
	 DECLARE order_detail_cursor CURSOR FOR
		SELECT sod.spec_id,SUM(sod.num),gs.is_sn_enable,gs.spec_no,sod.goods_name
		FROM stockout_order_detail sod LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id
		WHERE sod.stockout_id = P_StockoutId
		GROUP BY sod.spec_id;
		
	DECLARE invoice_cursor CURSOR FOR 
		SELECT invoice_id,`status`,invoice_category  
		FROM electronic_invoice
		WHERE trade_id = V_SrcOrderId AND `type` = 1 AND relation_invoice_id = 0 AND `status`<>0;
	
	 --  ------------SN-END-----------
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND=1;
	
	SET @sys_code = 0;
	SET @sys_message = '';
	SET @tmp_stock_spec_id=UNIX_TIMESTAMP();
	
	CALL SP_UTILS_GET_CFG('stockout_disable_revert',V_ScanForbiddenCancel,0); -- 禁止撤销出库
	CALL SP_UTILS_GET_CFG('stockout_consign_disable_revert',V_ConfignForbiddenCancel,0); -- 发货后禁止撤销发货
	CALL SP_UTILS_GET_CFG('open_logistics_trace',@cfg_open_logistics_trace,0); -- 物流追踪全局配置
	
	IF P_ReasonId=-1 THEN
		SET V_Reason='PDA异常驳回'; 
	ELSEIF P_ReasonId = 0 THEN  -- 后台PHP驳回退款订单
		SET V_Reason = '系统驳回已退货的出库单';
	ELSE 
		SELECT title INTO V_Reason FROM cfg_oper_reason WHERE reason_id = P_ReasonId;
		IF V_NOT_FOUND = 1 THEN
			CALL SP_ASSERT(0,'驳回原因不存在');
		END IF;
	END IF;
	
	SELECT stockout_id,stockout_no,src_order_id,src_order_type,src_order_no,`status`,consign_status,wms_status,checkouter_id,
		is_allocated,warehouse_id,logistics_id,logistics_no,weight,post_cost,batch_no,customer_id,src_order_id,picklist_no
	INTO V_StockoutId,V_StockoutNo,V_SrcOrderId,V_SrcOrderType,V_SrcOrderNO,V_Status,V_ConsignStatus,V_WmsStatus,
		V_CheckouterId,V_IsAllocated,V_WarehouseId,V_LogisticsId,V_LogisticsNo,V_Weight,V_PostCost,V_BatchNO,V_CustomerId,V_TradeID,V_PicklistNo
	FROM stockout_order WHERE stockout_id = P_StockoutId FOR UPDATE;
	
	IF V_NOT_FOUND THEN
		SET @sys_code = 1;
		SET @sys_message = '出库单不存在';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status = 5 THEN
		SET @sys_code = 2;
		SET @sys_message = '出库单已经取消';			
		LEAVE MAIN_LABEL;
	END IF;

	-- 未推送、处理中
	IF V_Status = 52 AND (V_WmsStatus = 2 OR V_WmsStatus = 3 OR V_WmsStatus = 4) THEN
		SET @sys_code = 10;
		SET @sys_message = '该单据正在向外部仓库同步,请稍等';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status=54 THEN
		SET @sys_code = 11;
		SET @sys_message = '正在获取面单号,请稍后';
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 53是同步失败,允许驳回
	IF V_Status<52 THEN
		SET @sys_code = 12;
		SET @sys_message = '该出库单正在处理中,请稍后';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_SrcOrderType<>1 THEN
		SET @sys_code = 3;
		SET @sys_message = '不是销售出库单';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF (V_ConsignStatus & 4) AND V_ScanForbiddenCancel THEN
		SET @sys_code = 4;
		SET @sys_message = '系统禁止撤销出库';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_Status>=95 AND V_ConfignForbiddenCancel THEN
		SET @sys_code = 5;
		SET @sys_message = '系统禁止撤销出库';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF V_CheckouterId AND V_CheckouterId<>@cur_uid THEN
		SET @sys_code = 5;
		SET @sys_message = '出库单已经被其他人签出';
		LEAVE MAIN_LABEL;
	END IF;
	
	IF P_Force THEN
		SELECT platform_id INTO V_PlatformId FROM sales_trade WHERE trade_id=V_SrcOrderId;
		IF (V_PlatformId AND V_Status>=100) OR V_Status=110 THEN 
			SET @sys_code = 7;
			SET @sys_message = CONCAT('出库单已', CASE V_Status WHEN 100 THEN '签收' WHEN 105 THEN '部分打款' WHEN 110 THEN '完成' END);
			LEAVE MAIN_LABEL;
		END IF;
	ELSE
		IF V_Status>=95 THEN
			SET @sys_code = 8;
			SET @sys_message = CONCAT('出库单已', CASE V_Status WHEN 95 THEN '发货' WHEN 100 THEN '签收' WHEN 105 THEN '部分打款' WHEN 110 THEN '完成' END);
			LEAVE MAIN_LABEL;
		END IF;
	END IF;

	--  对于存在拣货车 拣货单的 需要额外处理 占用情况
	IF V_PicklistNo<>'' THEN
		/*IF EXISTS(SELECT 1 FROM  stockout_print_pick WHERE pick_no=V_PicklistNo AND `status`>10) AND V_Status<95  THEN
			SET P_Occupy=1;
		END IF;*/

		SET V_DeleteFlag = 0;
		IF EXISTS(SELECT 1 FROM  stockout_print_pick WHERE pick_no=V_PicklistNo) AND V_Status<95 THEN
			SET V_DeleteFlag = 1;
		END IF;
	END IF;

	-- ------------SN-BEGIN------------
	-- 驳回验货和发货   货品启用SN 要修改对应状态值  
	-- 驳回情况  1，驳回验货   2撤销出库  3 驳回验货并且撤销出库
	--  驳回验货    从占用 -->启用  30---->20  须删除 stockout_detail_sn对应记录
	--  驳回发货    从已使用(已发货) --> 启用   40---->30  驳回发货 根据stockout_detail_sn 修改 stock_goods_sn对应状态 
	SET V_NOT_FOUND=0;
	IF V_ConsignStatus & 1 OR V_ConsignStatus & 4 THEN
		OPEN order_detail_cursor;
		DETAIL_LABEL:LOOP
			FETCH order_detail_cursor INTO V_SpecId,V_Num,V_IsSnEnable,V_SpecNo,V_GoodsName;
			IF V_NOT_FOUND THEN 					
				SET V_NOT_FOUND=0;
				LEAVE DETAIL_LABEL;
			END IF;
			
			-- 查询stockout_detail_sn中是否有
			SELECT COUNT(1) INTO V_Count FROM stockout_detail_sn WHERE stockout_id = P_StockoutId AND spec_id = V_SpecId;
			
			IF V_Count >0 THEN
				SELECT  sn_type INTO V_SnType FROM stock_goods_sn WHERE rec_id = (SELECT sn_id FROM stockout_detail_sn WHERE stockout_id = P_StockoutId AND spec_id = V_SpecId LIMIT 1);
			END IF;
			
			-- 如果货品开启了SN管理  
			IF NOT V_IsSnEnable THEN
				IF V_Count>0 THEN 
					CLOSE order_detail_cursor;
					SET @sys_code=4;			
					SET @sys_message=CONCAT('请开启 ',IF(V_SnType=1,'强序列号','弱序列号'),',出库单号:',V_StockoutNo,'货品:',V_GoodsName,' 商家编码:',V_SpecNo);
					LEAVE MAIN_LABEL;
				ELSE
					ITERATE DETAIL_LABEL;
				END IF;
			ELSEIF V_Count<>V_Num THEN
				CLOSE order_detail_cursor;
				SET @sys_code=5;
				SET @sys_message=CONCAT('请关闭序列号管理,出库单号:',V_StockoutNo,'货品:',V_GoodsName,' 商家编码:',V_SpecNo);
				LEAVE MAIN_LABEL;
			END IF;
			
			
			IF V_IsSnEnable=1 THEN
				-- 驳回前要判断序列号是否被二次使用
				SELECT sgs.status,sgs.rec_id,sgs.sn INTO V_SnStatus,V_SnId,V_SnNo FROM stock_goods_sn sgs,stockout_detail_sn sds 
				WHERE sgs.rec_id = sds.sn_id AND sds.stockout_id = P_StockoutId AND sgs.status IN (15,20)
				LIMIT 1;
	
				IF V_NOT_FOUND THEN 
					SET V_NOT_FOUND = 0;
				ELSE
					SELECT so.stockin_no INTO V_StockinNo FROM stockin_order so 
					LEFT JOIN stockin_order_detail sod ON sod.stockin_id = so.stockin_id
					LEFT JOIN stockin_detail_sn sds ON  sds.stockin_detail_id = sod.rec_id
					WHERE sds.sn_id  = V_SnId ORDER BY so.stockin_id DESC LIMIT 1;
					
					CLOSE order_detail_cursor;
					SET @sys_code = 6;
					SET @sys_message=CONCAT('序列号为',V_SnNo,'的货品已被入库单',V_StockinNo,'再次入库，请驳回入库单');
					LEAVE MAIN_LABEL;
				END IF;
				
				--  驳回时要修改为占用状态
				UPDATE stock_goods_sn sgs,stockout_detail_sn sds
				SET sgs.status = 20
				WHERE sgs.rec_id = sds.sn_id AND sds.spec_id = V_SpecId AND sds.stockout_id = P_StockoutId;
				
				-- 写日志
				INSERT stock_goods_sn_log(sn_id,operator_id,event_type,warehouse_id,message)
				SELECT sn_id,@cur_uid,6,V_WarehouseId,CONCAT_WS('',CONCAT(' 出库单号:',V_StockoutNo,'驳回审核,'),' 货品:',V_GoodsName,' 商家编码:',V_SpecNo)
				FROM stockout_detail_sn WHERE spec_id=V_SpecId AND stockout_id = P_StockoutId;
				
				-- 删除stockout_detail_sn 对应记录
				DELETE FROM stockout_detail_sn WHERE stockout_id = P_StockoutId AND spec_id = V_SpecId;

			ELSEIF V_IsSnEnable=2 THEN
			
				-- 如果驳回验货 需要删除弱序列号在stock_goods_sn中的信息 同时和出库记录信息  stockout_detail_sn
				DELETE sgs,sds
				FROM stock_goods_sn sgs,stockout_detail_sn sds
				WHERE sgs.rec_id = sds.sn_id AND sds.spec_id = V_SpecId AND sds.stockout_id = P_StockoutId;								
			
			END IF;
			
		END LOOP;
		CLOSE order_detail_cursor;
	END IF;
	--  ----------SN-END-------------
	
	-- 已出库
	IF (V_ConsignStatus & 4) THEN			
		
		CALL I_STOCKOUT_REVERT_STOCK_NUM(P_StockoutId,1);
		IF @sys_code<>0 THEN
			LEAVE MAIN_LABEL;
		END IF;

		-- 生成调价单
		CALL I_VR_STOCKOUT_REVERT_ADJUST_PRICE(V_StockoutId, V_WarehouseId, V_SrcOrderID, V_StockoutNo);
		IF @sys_code THEN
			LEAVE MAIN_LABEL;
		END IF;		


		-- 增加待审核量
		CALL I_RESERVE_STOCK(V_SrcOrderId,3,V_WarehouseId,0);
		
		-- 删除货位分配
		DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod
		WHERE sod.stockout_id=V_StockoutId AND sodp.stockout_order_detail_id=sod.rec_id;
		
		SELECT IF(trade_type=2,1,0)|IF(trade_type=3,2,0)|IF(trade_from=4,4,0)|IF(delivery_term=2,8,0) INTO V_OrderMask FROM sales_trade WHERE trade_id=V_SrcOrderId;
		
		-- 出入库记录
		INSERT INTO stock_change_history(src_order_type, src_order_id, src_order_no, stockio_id,stockio_detail_id, stockio_no, spec_id, warehouse_id, `type`, 
			cost_price_old, stock_num_old, price, num, amount, cost_price_new, stock_num_new, operator_id, order_mask, remark)
		SELECT V_SrcOrderType, V_SrcOrderID, V_SrcOrderNO, V_StockoutId, sod.rec_id, V_StockoutNo, ss.spec_id, V_WarehouseId, 2, 
			ss.cost_price, ss.stock_num-SUM(sod.num), ss.cost_price, -SUM(sod.num), -ss.cost_price*SUM(sod.num), ss.cost_price, ss.stock_num, @cur_uid, V_OrderMask, '撤销出库'
		FROM stockout_order_detail sod 
			LEFT JOIN stock_spec ss ON sod.spec_id=ss.spec_id AND ss.warehouse_id=V_WarehouseId 
		WHERE sod.stockout_id=V_StockoutId
		GROUP BY sod.spec_id;
		
	ELSEIF V_IsAllocated THEN
		-- 还原货位保留库存
		-- IF P_Occupy = 0 THEN
			INSERT INTO stock_spec_detail(rec_id,reserve_num,is_used_up,stockin_detail_id,stock_spec_id,position_id,created)
			(
				SELECT sodp.stock_spec_detail_id,-sodp.num,0,@tmp_stock_spec_id,@tmp_stock_spec_id,@tmp_stock_spec_id,NOW()
				FROM stockout_order_detail sod,stockout_order_detail_position sodp
				WHERE sod.stockout_id=V_StockoutId AND sod.rec_id=sodp.stockout_order_detail_id AND sodp.stock_spec_detail_id>0
			)
			ON DUPLICATE KEY UPDATE 
				reserve_num=stock_spec_detail.reserve_num+VALUES(reserve_num),
				is_used_up=IF(stock_spec_detail.reserve_num>=stock_spec_detail.stock_num,1,0);
			
			-- 删除货位分配
			DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod
			WHERE sod.stockout_id=V_StockoutId AND sodp.stockout_order_detail_id=sod.rec_id;		
			UPDATE stockout_order SET is_allocated=0 WHERE stockout_id=V_StockoutId;
		-- END IF;

		-- 增加待审核量
		CALL I_RESERVE_STOCK(V_SrcOrderId,3,V_WarehouseId,V_WarehouseId);
	ELSE
		-- 增加待审核量
		CALL I_RESERVE_STOCK(V_SrcOrderId,3,V_WarehouseId,V_WarehouseId);
	END IF;
	
	-- 删除或者修改 待结算物流记录
	IF V_Status>=95 THEN
		CALL SP_UTILS_GET_CFG('accounting_sync',V_AccountSync,0);
		SELECT trade_count INTO V_TradeCount FROM fa_logistics_fee WHERE logistics_id=V_LogisticsId AND logistics_no = V_LogisticsNo;
		IF V_NOT_FOUND<>0 THEN
			SET V_NOT_FOUND=0;
		ELSE
			IF V_TradeCount<=1 THEN
				DELETE FROM fa_logistics_fee WHERE logistics_id=V_LogisticsId AND logistics_no = V_LogisticsNo;
			ELSE
				SELECT shop_id INTO V_ShopId FROM sales_trade WHERE trade_id = V_SrcOrderId;
				INSERT INTO fa_logistics_fee(logistics_id,logistics_no,shop_id,warehouse_id,postage,weight,trade_count)
				VALUE(V_LogisticsId,V_LogisticsNo,V_ShopId,V_WarehouseId,-V_PostCost,-V_Weight,-1)
				ON DUPLICATE KEY UPDATE trade_count=trade_count+VALUES(trade_count),postage=postage+VALUES(postage),weight=weight+VALUES(weight);
			END IF;
		END IF;

		-- 删除多物流单fa_logistics_fee记录
		DELETE flf.* FROM fa_logistics_fee flf,sales_record_multi_logistics srml  
		WHERE flf.logistics_id=srml.logistics_id AND flf.logistics_no=srml.logistics_no AND srml.trade_id = V_SrcOrderId;

		-- 1101 和1102凭证 冲销
		
		
		-- 将出库日志,审核日志作驳回标记,方便统计
		UPDATE  sales_trade_log SET `data`=99 WHERE trade_id=V_SrcOrderID AND (`type`=105 OR `type`=45);

		--  crm_customer 表中对应的customer_id 信息要恢复  驳回扣减？？
		IF V_CustomerId<>0  THEN
			SELECT receivable,profit INTO V_Receivable,V_Profit  FROM sales_trade WHERE trade_id = V_SrcOrderId;
			
			UPDATE crm_customer SET 
				trade_count=trade_count-1,
				trade_amount=trade_amount-V_Receivable,
				profit=profit-V_Profit
			WHERE customer_id=V_CustomerId;
			-- 会员累计积分
			
		END IF;
		IF V_AccountSync THEN 
		-- 扣减平台对账发货金额
			SET V_AccountCheckNO = FN_SYS_NO('account_check');
			INSERT INTO fa_alipay_account_check(account_check_no,tid,send_amount,shop_id,platform_id,created)
			(
				SELECT V_AccountCheckNO,at.tid,-SUM(sto.share_amount)-SUM(sto.share_post),at.shop_id,at.platform_id,NOW()
				FROM sales_trade_order sto
				LEFT JOIN api_trade `at` ON sto.src_tid = at.tid AND sto.platform_id = at.platform_id
				WHERE sto.trade_id=V_TradeId AND sto.platform_id>0 AND sto.refund_status<>5 GROUP BY at.tid
			)ON DUPLICATE KEY UPDATE send_amount = send_amount+VALUES(send_amount);
		
		INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,send_amount,diff_amount,created)	
		(
			SELECT at.tid,at.platform_id,at.shop_id,DATE_FORMAT(NOW(),'%Y-%m'),-SUM(sto.share_amount)-SUM(sto.share_post),-SUM(sto.share_amount)-SUM(sto.share_post),NOW()
			FROM sales_trade_order sto
			LEFT JOIN api_trade `at` ON sto.src_tid = at.tid AND sto.platform_id = at.platform_id
			WHERE sto.trade_id=V_TradeId AND sto.platform_id>0 AND sto.refund_status<>5 GROUP BY at.tid
			
		)ON DUPLICATE KEY UPDATE send_amount=send_amount+VALUES(send_amount),diff_amount=diff_amount+VALUES(diff_amount);
			
		
		END IF;
	END IF;

	
	-- 删除stockout_order_detail 的包装
	DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod
	WHERE sod.stockout_id=V_StockoutId AND sodp.stockout_order_detail_id = sod.rec_id AND sod.is_package=1;

	DELETE FROM stockout_order_detail WHERE stockout_id=V_StockoutId AND is_package=1;
	
	IF ((P_LogisticsStatus = 0 OR P_SendibllStatus = 0) AND V_BatchNO<>'') OR V_DeleteFlag  THEN
		-- 清除打印批次
		DELETE FROM stockout_print_batch_detail WHERE stockout_order_id=V_StockoutId;
		
		UPDATE stockout_print_batch spb
		SET spb.order_num=(SELECT COUNT(1) FROM stockout_print_batch_detail spbd WHERE spbd.batch_id=spb.rec_id)
		WHERE spb.batch_no=V_BatchNO;

		UPDATE stockout_print_pick spp INNER JOIN stockout_print_batch spb ON spp.batch_id=spb.rec_id
		SET spp.order_num = spb.order_num
		WHERE spp.pick_no = V_PicklistNo;
		
		
		DELETE FROM stockout_print_batch WHERE batch_no=V_BatchNO AND order_num=0;
	END IF;
	
	/*回收热敏单号*/
	CALL I_STOCK_LOGISTICS_NO_RECYCLE(V_StockoutId);
	/*回收多物流单号*/
	DELETE srm.* FROM sales_record_multi_logistics srm LEFT JOIN cfg_logistics cl ON cl.logistics_id = srm.logistics_id WHERE srm.trade_id = V_TradeID AND cl.bill_type<>0;
	
	IF P_LogisticsStatus=0 THEN
		DELETE srm.* FROM sales_record_multi_logistics srm LEFT JOIN cfg_logistics cl ON cl.logistics_id = srm.logistics_id WHERE srm.trade_id = V_TradeID AND cl.bill_type=0;
	END IF;

	UPDATE stock_logistics_no SET `status`=5 WHERE stockout_id = V_StockoutId AND `status`=1 AND `type` = 1;
	
	-- IF P_Occupy=0 THEN 
		-- 取消出库单
		UPDATE stockout_order SET `status`=5,block_reason=0,
			logistics_print_status=IF(P_LogisticsStatus,logistics_print_status,0),
			sendbill_print_status=IF(P_SendibllStatus,sendbill_print_status,0),
			consign_status=0,is_allocated=0,post_cost=0,weight=0,package_id=0,
			package_cost=0,pick_error_count=0,picker_id=0,examiner_id=0,consigner_id=0,
			packager_id=0,checkouter_id=0,calc_weight=0,consign_time='0000-00-00 00:00:00',
			batch_no=IF(P_LogisticsStatus=0 OR P_SendibllStatus=0 OR V_DeleteFlag, '', batch_no),
			picklist_no=IF(P_LogisticsStatus=0 OR P_SendibllStatus=0 OR V_DeleteFlag , '', picklist_no),
			picklist_seq=IF(P_LogisticsStatus=0 OR P_SendibllStatus=0, 0, picklist_seq),
			watcher_id=0,logistics_no=IF(P_LogisticsStatus,logistics_no,''),
			pos_allocate_mode=0
		WHERE stockout_id=V_StockoutId;
	/* ELSE
		UPDATE stockout_order SET `status`=50,src_order_type = 100,block_reason=0,
		logistics_print_status=0,
		sendbill_print_status=0,
		consign_status=0,post_cost=0,weight=0,package_id=0,
		package_cost=0,pick_error_count=0,picker_id=0,examiner_id=0,consigner_id=0,
		packager_id=0,checkouter_id=0,calc_weight=0,consign_time='0000-00-00 00:00:00',
		batch_no='',
		picklist_no='',
		picklist_seq=0,
		watcher_id=0,logistics_no=''
		WHERE stockout_id=V_StockoutId;
	END IF; */
	
	--  sales_trade 的状态修改为待客核状态
	UPDATE sales_trade 
	SET trade_status=30,fchecker_id=0,check_step=0,consign_status=0,revert_reason=P_ReasonId,logistics_no=IF(P_LogisticsStatus,logistics_no,'') 
	WHERE trade_id=V_SrcOrderId AND trade_status<>5;
	
	-- 销售出库单驳回到待客审更新电子发票，编辑中的发票直接取消，进入处理状态的发票冲红
	IF @cfg_auto_make_red = 0 THEN	
		OPEN invoice_cursor;
		INVOICE_LABEL:LOOP
		
		FETCH invoice_cursor INTO V_ElectronicInvoiceID,V_ElectronicInvoiceStatus,V_InvoiceCategory;
		
		IF V_NOT_FOUND = 1 THEN
			SET V_NOT_FOUND = 0;
			LEAVE INVOICE_LABEL;
		END IF;
		
		IF V_ElectronicInvoiceStatus <> 0 AND @cfg_auto_make_red = 0 THEN
			IF V_ElectronicInvoiceStatus = 30 THEN
				-- 未开票，直接取消
				IF V_InvoiceCategory = 1 THEN
					-- UPDATE sales_trade SET invoice_id = 0,version_id = version_id + 1 WHERE trade_id = V_SrcOrderId;
					UPDATE electronic_invoice SET `status` = 0,version_id = version_id + 1 WHERE invoice_id = V_ElectronicInvoiceID;
					INSERT INTO electronic_invoice_log(order_id,`type`,operator_id,message) VALUES(V_ElectronicInvoiceID,211,@cur_uid,'销售出库单驳回到待客审--取消发票');
				END IF;
			ELSEIF V_InvoiceCategory = 1 THEN
				-- 已审核，生成对应红票
				SET V_ElectronicInvoiceNO=FN_SYS_NO('electronic_invoice');
				INSERT INTO electronic_invoice
					(invoice_no,platform_id,shop_id,warehouse_id,invoice_payee_id,invoice_provider_id,src_tid,trade_id,trade_no,`status`,
					`type`,payer_type,payer_name,payer_register_no,payer_phone,payer_email,payer_address,payer_bank,payer_account,payee_operator,
					payee_receiver,payee_checker,receiver_name,receiver_phone,receiver_address,invoice_category,version_id,created,relation_invoice_id,platform_status,invoice_amount,goods_amount,goods_tax,discount,
					discount_tax,sum_price,sum_tax,remark)
				SELECT V_ElectronicInvoiceNO,platform_id,shop_id,warehouse_id,invoice_payee_id,invoice_provider_id,src_tid,trade_id,trade_no,30,
					2,payer_type,payer_name,payer_register_no,payer_phone,payer_email,payer_address,payer_bank,payer_account,payee_operator,
					payee_receiver,payee_checker,receiver_name,receiver_phone,receiver_address,invoice_category,1,NOW(),V_ElectronicInvoiceID,platform_status,-invoice_amount,-goods_amount,-goods_tax,-discount,
					-discount_tax,-sum_price,-sum_tax,'销售出库单驳回自动创建'
					FROM electronic_invoice WHERE invoice_id = V_ElectronicInvoiceID;
					
				SET V_NewElectronicInvoiceID=LAST_INSERT_ID();
				
				UPDATE electronic_invoice SET relation_invoice_id = V_NewElectronicInvoiceID,version_id = version_id + 1 WHERE invoice_id = V_ElectronicInvoiceID;
				
				INSERT INTO electronic_invoice_detail
					(invoice_id,src_tid,src_oid,platform_id,shop_id,item_name,item_no,unit,num,tax_code,tax_rate,
					sum_price,tax,sum_amount,discount,discount_tax,discount_amount,discount_flag,created)
					
					SELECT V_NewElectronicInvoiceID,src_tid,src_oid,platform_id,shop_id,item_name,item_no,unit,num,tax_code,tax_rate,
					-(sum_price-discount),-(tax-discount_tax),-(sum_amount-discount_amount),0,0,0,0,NOW()
					FROM electronic_invoice_detail WHERE invoice_id = V_ElectronicInvoiceID;
					
				UPDATE electronic_invoice_detail SET price=-CAST((sum_price/num)AS DECIMAL(19,6)) WHERE invoice_id = V_NewElectronicInvoiceID;
		
				INSERT INTO electronic_invoice_log(order_id,`type`,operator_id,message) VALUES(V_NewElectronicInvoiceID,211,@cur_uid,'销售出库单驳回到待客审--创建红色电子发票');
			END IF;
			
		END IF;
		END LOOP;	
		CLOSE invoice_cursor;
		-- 冲红之后重置订单关联的发票ID
		SELECT MIN(invoice_id) INTO V_MinInvoiceID FROM electronic_invoice WHERE trade_id = V_SrcOrderId AND `type` = 1 AND `status` <> 0 AND relation_invoice_id = 0;
		UPDATE sales_trade SET invoice_id = V_MinInvoiceID,version_id = version_id + 1 WHERE trade_id = V_SrcOrderId;
		
	END IF;
	-- 发票处理结束
	
	--  stockout_order_detail 是否已扫描
	UPDATE stockout_order_detail SET scan_type=0 WHERE stockout_id=P_StockoutId;


	-- salse_trade_log,修改驳回标记,方便统计
	UPDATE sales_trade_log SET `data`=99 WHERE trade_id=V_SrcOrderId AND (`type`=100 OR `type`=103 OR (`type`=105 AND `data`=0 ) OR `type`=45);
	
	-- 删除接口定时驳回的记录
	DELETE FROM sys_asyn_task WHERE task_type=2 AND target_type = 1 AND target_id = V_SrcOrderId;

	-- 删除物流追踪记录
	IF @cfg_open_logistics_trace THEN
		DELETE FROM sales_logistics_trace WHERE trade_id = V_SrcOrderId AND logistics_status = 0;
	END IF;

	-- 插入日志
	INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message)
	VALUES(V_SrcOrderId,@cur_uid,30,CONCAT(IF(P_Force,'强制驳回出库单到客审,驳回原因:','驳回到客审,驳回原因:'),V_Reason,
		IF(P_LogisticsStatus,',保留物流单',''),
		IF(P_SendibllStatus,',保留发货单','')));
	
	
END//
DELIMITER ;
