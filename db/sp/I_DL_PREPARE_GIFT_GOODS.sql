DROP PROCEDURE IF EXISTS `I_DL_PREPARE_GIFT_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_PREPARE_GIFT_GOODS`(IN `P_TradeID` INT, INOUT `P_First` INT)
	SQL SECURITY INVOKER
	COMMENT '将订单货品插入到临时表,为赠品准备'
MAIN_LABEL:BEGIN
	IF P_First=0 THEN
		LEAVE MAIN_LABEL;
	END IF;

	DELETE FROM tmp_gift_trade_order;
	
	SET P_First=0;
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 0,sto.spec_id,sto.actual_num,sto.discount,sto.share_amount,sto.weight,sto.from_mask,gc.path,gg.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_goods gg ON gg.goods_id=sto.goods_id 
		LEFT JOIN goods_class gc ON gc.class_id=gg.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id=0 AND actual_num>0 AND sto.gift_type=0)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
	
	
	INSERT INTO tmp_gift_trade_order(is_suite,spec_id,num,discount,amount,weight,from_mask,class_path,brand_id)
	(SELECT 1,sto.suite_id,sto.suite_num,SUM(sto.discount),SUM(sto.share_amount),SUM(sto.weight),BIT_OR(sto.from_mask),gc.path,gs.brand_id
	FROM sales_trade_order sto LEFT JOIN goods_suite gs ON gs.suite_id=sto.suite_id
		LEFT JOIN goods_class gc ON gc.class_id=gs.class_id
	WHERE sto.trade_id=P_TradeID AND sto.suite_id>0 AND sto.actual_num>0 AND sto.gift_type=0
	GROUP BY platform_id,src_oid)
	ON DUPLICATE KEY UPDATE num=tmp_gift_trade_order.num+VALUES(num),
		discount=tmp_gift_trade_order.discount+VALUES(discount),
		amount=tmp_gift_trade_order.amount+VALUES(amount),
		weight=tmp_gift_trade_order.weight+VALUES(weight),
		from_mask=tmp_gift_trade_order.from_mask|VALUES(from_mask); 
END//
DELIMITER ;