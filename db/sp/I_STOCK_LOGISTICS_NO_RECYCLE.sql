
DROP PROCEDURE IF EXISTS I_STOCK_LOGISTICS_NO_RECYCLE;
DELIMITER $$
CREATE PROCEDURE I_STOCK_LOGISTICS_NO_RECYCLE(IN P_StockoutID INT)
	SQL SECURITY INVOKER 
COMMENT '处理热敏的单号'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUNT,V_LogisticsType,V_BillType,V_LogisticId,V_SrcOrderType,V_SrcOrderId INT DEFAULT 0;
	DECLARE V_logisticsNO,V_StockoutNO,V_ReceiverArea,V_ReceiverDtb,V_ReceiverName,V_ReceiverMobil VARCHAR(60) DEFAULT '';
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUNT=1;
	
	SELECT so.logistics_no,logistics_type,bill_type,so.logistics_id,src_order_type,src_order_id,so.stockout_no,so.receiver_area,so.receiver_dtb,so.receiver_name,so.receiver_mobile
		INTO V_logisticsNO,V_LogisticsType,V_BillType,V_LogisticId,V_SrcOrderType,V_SrcOrderId,V_StockoutNO,V_ReceiverArea,V_ReceiverDtb,V_ReceiverName,V_ReceiverMobil
	FROM stockout_order so LEFT JOIN cfg_logistics cl ON cl.logistics_id=so.logistics_id 
	WHERE so.stockout_id=P_StockoutID;
	
	IF V_NOT_FOUNT =1 THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 清空stock_logistics_sync
	DELETE FROM stock_logistics_sync WHERE stockout_id = P_StockoutID AND logistics_no = V_logisticsNO AND logistics_type = V_LogisticsType;
	IF V_logisticsNO = '' OR V_BillType = 0 THEN
		LEAVE MAIN_LABEL;
	END IF;

	-- 云栈与韵达调接口清空单号
	IF V_BillType = 2 OR V_BillType = 4 OR V_BillType = 5 OR (V_BillType = 7 AND (V_LogisticsType = 4 OR V_LogisticsType = 5)) OR (V_BillType = 8 AND V_LogisticsType = 1306) OR (V_BillType = 1 AND (V_LogisticsType = 9 OR V_LogisticsType = 8 OR V_LogisticsType = 6 OR V_LogisticsType = 10 OR V_LogisticsType = 16 OR V_LogisticsType = 7 OR V_LogisticsType = 87 OR V_LogisticsType = 22 OR V_LogisticsType = 1307 )) THEN
		IF V_BillType = 2 THEN
			INSERT INTO stock_logistics_no(logistics_id,logistics_no,logistics_type,status,stockout_id,v_trade_no,send_province,send_city,send_district,send_address,receiver_dtb,receiver_info,created)
				SELECT so.logistics_id,V_logisticsNO,V_LogisticsType,5,P_StockoutID,stockout_no,sw.province,sw.city,sw.district,sw.address,V_ReceiverDtb,CONCAT(V_ReceiverArea,V_ReceiverName,V_ReceiverMobil),now() 
				FROM stockout_order so LEFT JOIN cfg_warehouse sw ON so.warehouse_id = sw.warehouse_id 
				WHERE so.stockout_id=P_StockoutID
				ON DUPLICATE KEY UPDATE stockout_id=VALUES(stockout_id),v_trade_no = IF(v_trade_no = '',VALUES(v_trade_no),v_trade_no),status = 5,receiver_dtb = VALUES(receiver_dtb),receiver_info=VALUES(receiver_info);
		END IF;

		-- 韵达、申通和百世汇通线下热敏
		IF  V_BillType = 4 OR V_BillType = 5 OR (V_BillType = 7 AND (V_LogisticsType = 4 OR V_LogisticsType = 5)) OR (V_BillType = 8 AND V_LogisticsType = 1306) OR (V_BillType = 1 AND (V_LogisticsType = 9 OR V_LogisticsType = 8 OR V_LogisticsType = 10 OR V_LogisticsType = 6 OR V_LogisticsType = 16 OR V_LogisticsType = 7 OR V_LogisticsType = 87 OR V_LogisticsType = 22 OR V_LogisticsType = 1307  )) THEN
			INSERT INTO stock_logistics_no(logistics_id,logistics_no,logistics_type,stockout_id,v_trade_no,status,send_address,receiver_dtb,receiver_info,created) 
				VALUES(V_LogisticId,V_logisticsNO,V_LogisticsType,P_StockoutID,V_StockoutNO,5,V_ReceiverArea,V_ReceiverDtb,V_ReceiverArea,now())
				ON DUPLICATE KEY UPDATE stockout_id=VALUES(stockout_id),v_trade_no = IF(v_trade_no = '',VALUES(v_trade_no),v_trade_no),status = 5,send_address = VALUES(send_address),receiver_dtb=V_ReceiverDtb,receiver_info=VALUES(receiver_info);
		END IF;

		UPDATE stockout_order SET logistics_no='' WHERE stockout_id=P_StockoutID;
		IF V_SrcOrderType=1 THEN
			UPDATE sales_trade SET logistics_no='' WHERE trade_id=V_SrcOrderId;
		END IF;
	END IF;
		--  保税区热敏
	IF V_BillType = 3 AND V_LogisticsType = 3 THEN
			INSERT INTO stock_logistics_no(logistics_id,logistics_no,logistics_type,status,stockout_id,v_trade_no,created)
				SELECT so.logistics_id,V_logisticsNO,V_LogisticsType,5,so.stockout_id,st.src_tids,NOW()
				FROM stockout_order so LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id
				WHERE so.stockout_id=P_StockoutID
				ON DUPLICATE KEY UPDATE stockout_id=VALUES(stockout_id),v_trade_no = IF(v_trade_no = '',VALUES(v_trade_no),v_trade_no),status = 5;

			UPDATE stockout_order SET logistics_no='' WHERE stockout_id=P_StockoutID;
			IF V_SrcOrderType=1 THEN
				UPDATE sales_trade SET logistics_no='' WHERE trade_id=V_SrcOrderId;
			END IF;
	END IF;

	IF V_BillType = 1 AND (V_LogisticsType = 4 OR V_LogisticsType = 5 OR V_LogisticsType = 3 OR V_LogisticsType = 1311) THEN
		/*圆通，中通，ems,京邦达线下热敏单号回收*/
		UPDATE stock_logistics_no SET `status`=5,stockout_id = P_StockoutID,receiver_info=V_ReceiverArea
		WHERE logistics_no=V_logisticsNO AND logistics_type=V_LogisticsType AND logistics_id=V_LogisticId and `status`<>2;

		UPDATE stockout_order SET logistics_no='' WHERE stockout_id=P_StockoutID;
		IF V_SrcOrderType=1 THEN
			UPDATE sales_trade SET logistics_no='' WHERE trade_id=V_SrcOrderId;
		END IF;
	END IF;

END$$
DELIMITER ;
