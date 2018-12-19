DROP FUNCTION IF EXISTS `FN_GOODS_NO`;
DELIMITER //
CREATE FUNCTION `FN_GOODS_NO`(`P_Type` INT, `P_TargetID` INT) RETURNS VARCHAR(40) CHARSET utf8
    READS SQL DATA
    SQL SECURITY INVOKER
    NOT DETERMINISTIC
    COMMENT '查询货品或组合装信息'
BEGIN
	DECLARE V_GoodsNO VARCHAR(40);
	
	SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',
		@tmp_goods_id='',@tmp_spec_id='',@tmp_barcode='',@tmp_retail_price=0;-- ,@tmp_sn_enable=0
	
	IF P_Type=1 THEN
		SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name,gs.spec_code,gg.goods_id,gs.spec_id,gs.barcode,gs.retail_price -- gs.is_sn_enable,
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,V_GoodsNO,@tmp_spec_name,@tmp_spec_code,@tmp_goods_id,@tmp_spec_id,@tmp_barcode,@tmp_retail_price -- ,@tmp_sn_enable
		FROM goods_spec gs,goods_goods gg WHERE gs.spec_id=P_TargetID AND gs.goods_id=gg.goods_id;
		
	ELSEIF P_Type=2 THEN
		-- SELECT 1 INTO @tmp_sn_enable
		-- FROM goods_suite_detail gsd, goods_spec gs
		-- WHERE gsd.suite_id=P_TargetID AND gs.spec_id=gsd.spec_id AND gs.is_sn_enable>0 LIMIT 1;
		
		SELECT suite_no,suite_name,short_name,suite_id,'','',barcode,retail_price
		INTO @tmp_merchant_no,@tmp_goods_name,@tmp_short_name,@tmp_goods_id,@tmp_spec_id,@tmp_spec_name,@tmp_barcode,@tmp_retail_price 
		FROM goods_suite WHERE suite_id=P_TargetID;
		
		SET V_GoodsNO='';
	END IF;
	
	RETURN V_GoodsNO;
END//
DELIMITER ;