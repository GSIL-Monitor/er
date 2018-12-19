DROP PROCEDURE IF EXISTS `I_DL_MARK_INVALID_TRADE`;
DELIMITER //
CREATE PROCEDURE `I_DL_MARK_INVALID_TRADE`(IN `P_TradeID` INT, IN `P_ShopId` INT, IN `P_Tid` VARCHAR(40))
    SQL SECURITY INVOKER
    COMMENT '标记原始单的子订单有未匹配货品'
MAIN_LABEL:BEGIN
	DECLARE V_RecID,V_MatchTargetType,V_MatchTargetID,V_InvalidGoods,V_GoodsCount,V_IsManualMatch,V_Deleted,V_Exists,V_NOT_FOUND INT DEFAULT(0);
	DECLARE V_MatchCode,V_OuterId,V_SpecOuterId VARCHAR(40);
	
	DECLARE trade_order_cursor CURSOR FOR 
		SELECT ato.rec_id,match_target_type,match_target_id,is_manual_match,ato.goods_no,ato.spec_no
		FROM api_trade_order ato LEFT JOIN api_goods_spec aps 
			ON ato.shop_id=aps.shop_id AND ato.goods_id=aps.goods_id and ato.spec_id=aps.spec_id
		WHERE ato.shop_id=P_ShopId AND ato.tid=P_Tid;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	START TRANSACTION;
	OPEN trade_order_cursor;
	TRADE_GOODS_LABEL: LOOP
		SET V_NOT_FOUND = 0;
		
		FETCH trade_order_cursor INTO V_RecID,V_MatchTargetType,V_MatchTargetID,V_IsManualMatch,V_OuterId,V_SpecOuterId;
		IF V_NOT_FOUND THEN
			LEAVE TRADE_GOODS_LABEL;
		END IF;
		
		-- 未绑定
		IF V_MatchTargetType IS NULL OR V_MatchTargetType = 0 THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			
			-- 添加到平台货品
			INSERT IGNORE INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,modify_flag,created)
			SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,ax.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,1,NOW()
			FROM api_trade_order ato LEFT JOIN api_trade ax ON ax.tid=ato.tid AND ax.platform_id=ato.platform_id
			WHERE ato.rec_id=V_RecID AND ax.platform_id<>0;
			
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF @cfg_goods_match_dynamic_check AND V_IsManualMatch=0 THEN 
			SET V_MatchCode=FN_SPEC_NO_CONV(V_OuterId,V_SpecOuterId);
			SELECT type,target_id INTO V_MatchTargetType,V_MatchTargetID from goods_merchant_no where merchant_no=V_MatchCode;
			IF V_NOT_FOUND THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
				ITERATE TRADE_GOODS_LABEL;
			END IF;
		END IF;
		
		SET V_Exists=0,V_Deleted = 0;
		IF V_MatchTargetType = 1 THEN -- 单品
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_spec WHERE spec_id=V_MatchTargetID;
		ELSEIF V_MatchTargetType = 2 THEN -- 组合装
			SELECT 1,deleted INTO V_Exists,V_Deleted FROM goods_suite WHERE suite_id=V_MatchTargetID;
		END IF;
		
		
		IF NOT V_Exists OR V_Deleted THEN
			UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
			SET V_InvalidGoods=1;
			ITERATE TRADE_GOODS_LABEL;
		END IF;
		
		IF V_MatchTargetType = 2 THEN
			SELECT COUNT(rec_id) INTO V_GoodsCount FROM goods_suite_detail WHERE suite_id=V_MatchTargetID;
			IF V_GoodsCount=0 THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
			
			-- 判断组合装里货品是否都有效
			IF EXISTS(SELECT 1 FROM goods_suite_detail gsd,goods_spec gs 
				WHERE gsd.suite_id=V_MatchTargetID AND gs.spec_id=gsd.spec_id AND gs.deleted>0) THEN
				UPDATE api_trade_order SET is_invalid_goods=1 WHERE rec_id=V_RecID;
				SET V_InvalidGoods=1;
			END IF;
		END IF;
		
	END LOOP;
	CLOSE trade_order_cursor;
	
	IF V_InvalidGoods THEN
		UPDATE api_trade SET bad_reason=1 WHERE rec_id=P_TradeID;
	END IF;
	COMMIT;
	
END//
DELIMITER ;