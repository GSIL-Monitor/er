DROP VIEW IF EXISTS v_logistics_sync;

CREATE SQL SECURITY INVOKER VIEW v_logistics_sync AS 
	SELECT als.rec_id, als.platform_id, als.shop_id, als.tid, als.oids, als.sync_status, als.logistics_no,als.stockout_id,
		als.logistics_id, als.trade_id, als.delivery_term, als.is_part_sync, als.is_online, cl.logistics_type, 
		cl.bill_type,cl.app_key as logistics_key, clg.logistics_code, clg.logistics_name, als.try_times,
		cs.sub_platform_id, cs.app_key, cs.contact, cs.address, cs.telno, cs.mobile , cs.auth_time,cs.refresh_token,als.consign_time
	FROM api_logistics_sync als FORCE INDEX(IDX_NEED_SYNC)
		LEFT JOIN cfg_logistics cl on cl.logistics_id = als.logistics_id 
		LEFT JOIN cfg_logistics_shop clg on clg.shop_id = als.shop_id AND clg.logistics_type = cl.logistics_type 
		LEFT JOIN cfg_shop cs on cs.shop_id = als.shop_id 
	WHERE als.is_need_sync = 1 AND cs.auth_state=1;

DROP VIEW IF EXISTS v_api_goodsspec_sync;

CREATE SQL SECURITY INVOKER VIEW v_api_goodsspec_sync AS 
	SELECT  ag.rec_id,ag.platform_id,ag.shop_id,ag.goods_id,ag.spec_id,
		ag.outer_id, ag.spec_outer_id, ag.match_target_id,ag.status,ag.stock_num,
		ag.is_manual_match,ag.match_target_type,ag.match_code,ag.stock_change_count,
		ag.stock_syn_rule_id,ag.stock_syn_rule_no,ag.stock_syn_warehouses,
		ag.stock_syn_mask,ag.stock_syn_percent,ag.stock_syn_plus,ag.stock_syn_min,
		ag.last_syn_num,ag.is_stock_changed,ag.is_auto_listing,ag.is_auto_delisting,
		ag.list_time,ag.delist_time,
		cs.sub_platform_id,cs.account_id,cs.app_key,cs.auth_time
	FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec)
		LEFT JOIN cfg_shop cs ON cs.shop_id=ag.shop_id
	WHERE ag.is_disable_syn=0 AND ag.is_stock_changed=1 AND ag.disable_syn_until<NOW() AND ag.is_deleted=0 AND ag.status<>0 AND cs.auth_state=1;


