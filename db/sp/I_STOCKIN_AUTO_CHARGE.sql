-- 导出  过程 eshop_v2.I_STOCKIN_AUTO_CHARGE 结构
DROP PROCEDURE IF EXISTS `I_STOCKIN_AUTO_CHARGE`;
DELIMITER //
CREATE PROCEDURE `I_STOCKIN_AUTO_CHARGE`(IN `P_StockinId` INT, IN `P_SrcOrderType` INT)
    SQL SECURITY INVOKER
    COMMENT '入库单审核自动结算'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND, V_AutoCheck INT DEFAULT(0);
	
	DECLARE V_PurchaseAutoCharge,V_RepairAutoCharge,V_TransferAutoCharge,V_ProcessAutoCharge,V_OtherAutoCharge INT DEFAULT(0);
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		SET @sys_code = 99;
		SET @sys_message = '未知错误';
		RESIGNAL;
	END;
	
	SET @sys_code=0;
	SET @sys_message='ok';
	-- 如果没有传src_order_type, 从表里面取
	IF P_SrcOrderType=0 THEN
		SELECT src_order_type INTO P_SrcOrderType FROM stockin_order WHERE stockin_id=P_StockinID;
		IF V_NOT_FOUND<>0 THEN
			SET @sys_code = 1 ;
			SET @sys_message = '没有找到入库单';
			ROLLBACK;
			LEAVE MAIN_LABEL;
		END IF;
	END IF;	
	
	--  自动结算  1 采购入库 2 调拨入库  5生产  6 其他
	CASE P_SrcOrderType
	WHEN 1 THEN
		CALL SP_UTILS_GET_CFG('purchase_stockin_not_auto_debtcontacts',@cfg_stockin_auto_debtcontacts,0); -- 默认开启
		CALL SP_UTILS_GET_CFG('stockin_purchase_auto_charge',V_PurchaseAutoCharge,0);
		IF @cfg_stockin_auto_debtcontacts=0 THEN -- 开启了自动生成应收应付单据设置之后，才能进行自动结算。
			IF V_PurchaseAutoCharge<>0 THEN
				CALL I_CHARGE_STOCKIN_ORDER_FOR_PURCHASE(P_StockinId,'',1);
				IF @sys_code<>0 THEN
					LEAVE MAIN_LABEL;
				END IF;
			END IF;
		END IF;
	WHEN 2 THEN
		CALL SP_UTILS_GET_CFG('stockin_transfer_auto_charge',V_TransferAutoCharge,0);
		IF V_TransferAutoCharge<>0 THEN
			CALL I_CHARGE_STOCKIN_ORDER_FOR_OTHER(P_StockinId,'',1);
			IF @sys_code<>0 THEN
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
	WHEN 5 THEN
		CALL SP_UTILS_GET_CFG('stockin_process_auto_charge',V_ProcessAutoCharge,0);
		IF V_ProcessAutoCharge<>0 THEN
			CALL I_CHARGE_STOCKIN_ORDER_FOR_OTHER(P_StockinId,'',1);
			IF @sys_code<>0 THEN
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
	WHEN 6 THEN
		CALL SP_UTILS_GET_CFG('stockin_other_auto_charge',V_OtherAutoCharge,0);
		IF V_OtherAutoCharge<>0 THEN
			CALL I_CHARGE_STOCKIN_ORDER_FOR_OTHER(P_StockinId,'',1);
			IF @sys_code<>0 THEN
				LEAVE MAIN_LABEL;
			END IF;
		END IF;
		
	when 7 then 
		call SP_UTILS_GET_CFG('stockin_repair_auto_charge',V_RepairAutoCharge,0);
		if V_RepairAutoCharge<>0 then
			call I_CHARGE_STOCKIN_ORDER_FOR_OTHER(P_StockinId,'',1);
			if @sys_code<>0 then
				leave MAIN_LABEL;
			end if;
		end if;
	ELSE
		BEGIN END;
	END CASE;
END//

DELIMITER ;

