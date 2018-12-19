DROP PROCEDURE IF EXISTS `I_DL_INIT_AUTO_MAKE_SYS_GOODS`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT_AUTO_MAKE_SYS_GOODS`(IN `P_GoodsIds` VARCHAR(2048),IN `P_OperatorID` INT)
    SQL SECURITY INVOKER
    COMMENT '自动生成系统货品'
MAIN_LABEL:BEGIN
  DECLARE V_ShopID,V_GoodsID,V_SpecID,V_GoodsType,V_FlagID,V_ResGoodsSpecID,V_NOT_FOUND,V_SpecOuterIdCount INT DEFAULT(0);
  DECLARE V_RecID BIGINT DEFAULT(0);
  DECLARE V_Price DECIMAL(19,4) DEFAULT(0);
	DECLARE V_SpecCode,V_OuterId,V_SpecOuterId,V_ResGoodsID,V_OldGoodsNo,V_OldGoodsNo2,V_GoodsNO,V_SpecNo VARCHAR(40) DEFAULT '';
	DECLARE V_GoodsName,V_SpecName,V_PicUrl,V_Barcode,V_MerchantNo VARCHAR(256) DEFAULT '';
	DECLARE V_Now DATETIME;	

  DECLARE api_goods_cursor CURSOR FOR SELECT ags.rec_id AS id,ags.shop_id,ags.goods_id,ags.spec_id,ags.spec_code,ags.goods_name,ags.spec_name,ags.outer_id,ags.spec_outer_id,ags.price,ags.pic_url,ags.barcode
		FROM api_goods_spec ags
		WHERE FIND_IN_SET(ags.rec_id,P_GoodsIds);

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
  SET V_Now = NOW();
  SET @sys_code=0, @sys_message='OK';

	OPEN api_goods_cursor;
	API_GOODS_LABEL: LOOP
		SET V_NOT_FOUND=0;
		FETCH api_goods_cursor INTO
		  V_RecID,V_ShopID,V_GoodsID,V_SpecID,V_SpecCode,V_GoodsName,V_SpecName,V_OuterId,V_SpecOuterId,V_Price,V_PicUrl,V_Barcode;
		
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE API_GOODS_LABEL;
		END IF;

		SET V_ResGoodsID='';
		
    IF @cfg_goods_match_concat_code=1 AND V_OuterId='' AND V_SpecOuterId='' THEN
      -- 货品商家编码为空或规格商家编码为空
      ITERATE API_GOODS_LABEL;
    END IF;
    IF @cfg_goods_match_concat_code=0 AND V_SpecOuterId='' THEN
      -- 规格商家编码为空
      ITERATE API_GOODS_LABEL;
    END IF;
    IF @cfg_goods_match_concat_code=3 AND V_OuterId='' THEN
      -- 货品商家编码为空
      ITERATE API_GOODS_LABEL;
    END IF;

    IF @cfg_goods_match_concat_code=0 THEN
      SELECT  COUNT(1) INTO V_SpecOuterIdCount FROM  api_goods_spec ags WHERE ags.rec_id IN (P_GoodsIds) AND ags.spec_outer_id=V_spec_outer_id;
      IF V_SpecOuterIdCount>1 THEN
          -- 该商家编码货品的规格编码对应多个单品
          ITERATE API_GOODS_LABEL;
      END IF;
    END IF;

    SELECT FN_SPEC_NO_CONV(IF(@cfg_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF(@cfg_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) into V_MerchantNo FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.rec_id =V_RecID;

    START TRANSACTION;

    IF @cfg_goods_match_concat_code=3 OR @cfg_goods_match_concat_code=1 THEN
        SET V_GoodsNO=V_OuterId;
    ELSE
        SET V_GoodsNO=V_SpecOuterId;
    END  IF;
    -- 兼容旧数据
    SELECT  gg.goods_id,gg.goods_no INTO V_ResGoodsID,V_OldGoodsNo FROM goods_goods gg WHERE gg.deleted=0 AND gg.goods_no=V_OuterID LIMIT 1;
    IF V_ResGoodsID=0 THEN
      SELECT  gg.goods_id,gg.goods_no INTO V_ResGoodsID,V_OldGoodsNo2 FROM goods_goods gg WHERE gg.deleted=0 AND gg.goods_no=V_GoodsNO LIMIT 1;
      IF V_ResGoodsID=0 THEN
        INSERT INTO goods_goods(goods_no,goods_name,goods_type,flag_id,modified,created) VALUES(V_GoodsNO,V_GoodsName,1,0,V_Now,V_Now);
        SET V_ResGoodsID=LAST_INSERT_ID();
      END IF;
    END IF;

    IF V_OldGoodsNo2='' AND V_ResGoodsID THEN
        INSERT  INTO goods_log (goods_type,goods_id,spec_id,operator_id,operate_type,message,created)
        VALUES (1,V_ResGoodsID,0,P_OperatorID,11,CONCAT('自动从平台货品导入货品--', V_GoodsName),V_Now);
    END IF;

    SELECT gm.merchant_no INTO V_SpecNo  FROM goods_merchant_no gm WHERE gm.merchant_no=V_MerchantNo;
    IF V_SpecNo THEN
      -- 该商家编码在货品档案或组合装中已经存在
      ROLLBACK;
      ITERATE API_GOODS_LABEL;
    END IF;

    -- 插入数据
    INSERT INTO goods_spec (goods_id,spec_no,spec_name,spec_code,retail_price,img_url,barcode,is_allow_neg_stock,flag_id,modified,created)
    VALUES (V_ResGoodsID,V_MerchantNo,V_SpecName,V_SpecOuterId,V_Price,V_PicUrl,V_Barcode,0,9,V_Now,V_Now);
    SET V_ResGoodsSpecID=LAST_INSERT_ID();		

    IF V_ResGoodsSpecID THEN
      UPDATE goods_goods SET spec_count=(SELECT COUNT(spec_id) FROM goods_spec WHERE goods_id=V_ResGoodsID AND deleted=0) WHERE goods_id=V_ResGoodsID;
      IF V_Barcode!='' THEN
        INSERT INTO goods_barcode(barcode,type,target_id,tag,is_master,created)
        VALUES (V_Barcode,1,V_ResGoodsSpecID,FN_SEQ('goods_barcode'),1,V_Now);
      END IF;

      -- 初始化单品库存
      IF @cfg_addgoods_refresh_stock=0 THEN
          INSERT INTO stock_spec(spec_id,warehouse_id)
          (SELECT V_ResGoodsSpecID,warehouse_id FROM cfg_warehouse WHERE is_disabled=0);

          INSERT INTO stock_spec_position(spec_id,warehouse_id,position_id,zone_id)
          (SELECT V_ResGoodsSpecID,cw.warehouse_id,if(ssp.position_id is NULL,-cw.warehouse_id,ssp.position_id),cwz.zone_id
           FROM cfg_warehouse cw
           LEFT JOIN stock_spec_position ssp on ssp.spec_id = V_ResGoodsSpecID and ssp.warehouse_id =cw.warehouse_id
           LEFT JOIN cfg_warehouse_zone cwz ON cwz.warehouse_id=cw.warehouse_id
           WHERE cw.is_disabled=0);
--           SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0;
      ELSE
          INSERT INTO stock_spec(spec_id,warehouse_id)
          (SELECT V_ResGoodsSpecID,warehouse_id FROM cfg_warehouse WHERE is_disabled=0 and type = 11);

          INSERT INTO stock_spec_position(spec_id,warehouse_id,position_id,zone_id)
          (SELECT V_ResGoodsSpecID,cw.warehouse_id,if(ssp.position_id is NULL,-cw.warehouse_id,ssp.position_id),cwz.zone_id
           FROM cfg_warehouse cw
           LEFT JOIN stock_spec_position ssp ON ssp.spec_id = V_ResGoodsSpecID and ssp.warehouse_id =cw.warehouse_id
           LEFT JOIN cfg_warehouse_zone cwz ON cwz.warehouse_id=cw.warehouse_id
           WHERE cw.is_disabled=0 and cw.type = 11);
--          SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0 and type = 11;
      END IF;
    END IF;

    INSERT  INTO goods_log (goods_type,goods_id,spec_id,operator_id,operate_type,message,created)
    VALUES (1,V_ResGoodsID,V_ResGoodsSpecID,P_OperatorID,11,CONCAT('自动从平台货品导入--单品--', V_SpecName),V_Now);

    INSERT INTO goods_merchant_no(merchant_no,type,target_id,modified,created)
    VALUES (V_MerchantNo,1,V_ResGoodsSpecID,V_Now,V_Now);		
		
		
    COMMIT;
	END LOOP;
	CLOSE api_goods_cursor;

END//
DELIMITER ;