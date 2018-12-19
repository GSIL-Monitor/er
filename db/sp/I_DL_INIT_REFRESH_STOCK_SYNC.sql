DROP PROCEDURE IF EXISTS `I_DL_INIT_REFRESH_STOCK_SYNC`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT_REFRESH_STOCK_SYNC`()
    SQL SECURITY INVOKER
    COMMENT '自动刷新库存同步规则'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND,V_RecID,V_RuleID,V_Priority,V_StockFlag,V_Percent,V_IsAutoListing,V_IsAutoDelisting,V_IsDisableSyn INT(25);
	DECLARE V_RuleNo VARCHAR (255);
	DECLARE V_WarehouseList VARCHAR(1024);
	DECLARE V_PlusValue,V_MinStock DECIMAL(19,4);
	
	DECLARE stock_sync_cursor CURSOR FOR 
		SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id ;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	OPEN stock_sync_cursor;
	RULE_LABEL:LOOP
		SET V_NOT_FOUND=0;
		FETCH stock_sync_cursor INTO V_RecID,V_RuleID,V_Priority,V_RuleNo,V_WarehouseList,V_StockFlag,
			V_Percent,V_PlusValue,V_MinStock,V_IsAutoListing,V_IsAutoDelisting,V_IsDisableSyn;
		
		IF V_NOT_FOUND THEN
			SET V_NOT_FOUND = 0;
			LEAVE RULE_LABEL;
		END IF;
		
		UPDATE api_goods_spec gs
		SET
			gs.stock_syn_rule_id=IFNULL(V_RuleID,-1),
			gs.stock_syn_rule_no=IFNULL(V_RuleNo,''),
			gs.stock_syn_warehouses=IFNULL(V_WarehouseList,''),
			gs.stock_syn_mask=IFNULL(V_StockFlag,0),
			gs.stock_syn_percent=IFNULL(V_Percent,100),
			gs.stock_syn_plus=IFNULL(V_PlusValue,0),
			gs.stock_syn_min=IFNULL(V_MinStock,0),
			gs.is_auto_listing=IFNULL(V_IsAutoListing,1),
			gs.is_auto_delisting=IFNULL(V_IsAutoDelisting,1),
			gs.is_disable_syn=IFNULL(V_IsDisableSyn,1)
		WHERE gs.rec_id=V_RecID;
	END LOOP;
	CLOSE stock_sync_cursor;
END//
DELIMITER ;