DROP PROCEDURE IF EXISTS `SP_WMS_ORDER_HANDLE`;
DELIMITER $$
CREATE  PROCEDURE `SP_WMS_ORDER_HANDLE`(IN `P_OrderNo` VARCHAR(50),OUT `P_Code` INT, OUT `P_ErrorMsg` VARCHAR(256))
    SQL SECURITY INVOKER
    COMMENT '委外单据回传处理'
MAIN_LABEL:BEGIN
	DECLARE V_WmsOrderType,V_NOT_FOUND TINYINT(2) DEFAULT (0);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK; 
		SET P_Code = 99; 
		SET P_ErrorMsg = '处理异常';
		RESIGNAL;
	END;
	SET P_Code = 0;
	SET P_ErrorMsg = '';
	START TRANSACTION;
	-- 获取单据信息
	IF P_OrderNo='' THEN 
		ROLLBACK;
		SET P_Code = -2;
		SET P_ErrorMsg = "单据号为空";
		LEAVE MAIN_LABEL;
	END IF;
	SELECT `order_type`	INTO V_WmsOrderType	FROM tmp_wms_order WHERE order_no = P_OrderNo;
	IF V_NOT_FOUND<>0 THEN 
		ROLLBACK;
		SET V_NOT_FOUND=0;
		SET P_Code = -1;
		SET P_ErrorMsg = "服务器错误请稍后重试";
		LEAVE MAIN_LABEL;
	END IF;
	-- 根据不同类型的单据做不同的操作
	IF V_WmsOrderType = 1 THEN-- 订单出库
			CALL I_WMS_SALES_TRADE_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 2 THEN-- 采购单 
			CALL I_WMS_PURCHASE_ORDER_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 3 THEN-- 退货入库
			CALL I_WMS_SALES_REFUND_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 4 THEN-- 委外其它出库
			CALL I_WMS_STOCKOUT_OTHER_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 5 THEN-- 委外其他入库
			CALL I_WMS_STOCKIN_OTHER_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 6 THEN-- 其他出库_库存异动
			CALL I_WMS_STOCKCHANGE_OUT_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 7 THEN-- 其他入库_库存异动
			CALL I_WMS_STOCKCHANGE_IN_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 8 THEN-- 调拨入库单
			CALL I_WMS_TRANSFER_IN_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 9 THEN-- 调拨出库单
			CALL I_WMS_TRANSFER_OUT_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 10 THEN-- 采购退货单
			CALL I_WMS_PURCHASE_RETURN_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 11 THEN-- 盘点
			CALL I_WMS_STOCK_PD_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 12 THEN-- JIT出库单
			CALL I_WMS_JIT_PICK_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 13 THEN-- JIT退货入库单
			CALL I_WMS_JIT_REFUND_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 15 THEN-- 退货预入库
			CALL I_WMS_STOCKIN_PRE_REFUND_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSEIF V_WmsOrderType = 17 THEN-- 采购计划单
			CALL I_WMS_PURCHASE_PLAN_HANDLE(P_OrderNo,P_Code,P_ErrorMsg);
	ELSE 
		ROLLBACK;
		SET P_Code = -3;
		SET P_ErrorMsg = '接口不存在';	
	END IF;
COMMIT;
END$$
DELIMITER ;