DROP PROCEDURE IF EXISTS `I_STOCK_TRANSFER_REVERT`;
DELIMITER $$
CREATE  PROCEDURE `I_STOCK_TRANSFER_REVERT`(IN `P_TransferID` INT(11),IN `P_WmsFlag` INT(1))
    SQL SECURITY INVOKER
    COMMENT '调拨单驳回审核详细操作'
BEGIN
	DECLARE V_FromWarehouseId INT DEFAULT (0);
	DECLARE V_Message VARCHAR(40);
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	START TRANSACTION;
	SELECT from_warehouse_id INTO V_FromWarehouseId FROM stock_transfer WHERE rec_id =  P_TransferID;
	-- 更新 待调拨数量
	INSERT INTO stock_spec(spec_id,warehouse_id,`status`,created)
	SELECT spec_id,V_FromWarehouseId AS warehouse_id,1,NOW()  
	FROM stock_transfer_detail WHERE transfer_id = P_TransferID 
	ON DUPLICATE KEY
	UPDATE stock_spec.transfer_num = IF(stock_spec.`transfer_num` - stock_transfer_detail.`num` <0,0,stock_spec.`transfer_num` - stock_transfer_detail.`num`),
	`status`=1;
	-- 标记平台货品变化
	-- 单品
	INSERT INTO sys_process_background(`type`,object_id) 
	SELECT 1,spec_id FROM stock_transfer_detail WHERE transfer_id = P_TransferID;
	-- 组合装
	INSERT INTO sys_process_background(TYPE,object_id)
	SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,stock_transfer_detail st
		WHERE gs.suite_id=gsd.suite_id AND gsd.spec_id=st.spec_id AND st.transfer_id = P_TransferID;
	UPDATE stock_transfer SET `status`=30 WHERE rec_id=P_TransferID;
	-- 判断是否是外部接口驳回(0否1是)
	IF P_WmsFlag = 0 THEN
		SET V_Message = '驳回调拨单';
	ELSE
		SET V_Message = '驳回调拨单--外部接口驳回';
	END IF;
	INSERT INTO stock_inout_log(order_type, order_id, operator_id, operate_type, message) 
	VALUES(3, P_TransferID, @cur_uid, 14, V_Message);
	COMMIT;
END$$
DELIMITER ;