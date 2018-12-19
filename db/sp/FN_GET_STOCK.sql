DROP FUNCTION IF EXISTS `FN_GET_STOCK`;
DELIMITER //
CREATE FUNCTION `FN_GET_STOCK`(`P_Type`	INT(11)) RETURNS VARCHAR(4096) CHARSET UTF8
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN

	RETURN REPLACE(CONCAT('ss.stock_num',
			MAKE_SET(P_Type,'+ss.purchase_num','+ss.to_purchase_num','+ss.transfer_num','+ss.purchase_arrive_num','+ss.return_onway_num',
			'+ss.refund_exch_num','-ss.subscribe_num','-ss.order_num','-ss.unpay_num','-ss.sending_num','-ss.return_num','-ss.refund_num',
			'-ss.return_exch_num','-ss.refund_onway_num','-ss.lock_num','-ss.to_transfer_num')),',','');
END//
DELIMITER ;