DROP PROCEDURE IF EXISTS `I_DL_INIT`;
DELIMITER //
CREATE PROCEDURE `I_DL_INIT`(IN `P_CreateApiGoods` INT)
    SQL SECURITY INVOKER
    COMMENT '递交处理初始化'
MAIN_LABEL:BEGIN
	DECLARE V_AutoMatchGoods,V_AutoMakeSysGoods,V_OldRecId INT DEFAULT(0);
	DECLARE V_NewRecId VARCHAR(2048)  DEFAULT '';

	/*配置*/
	-- 是否开启自动递交
	CALL SP_UTILS_GET_CFG_INT('order_auto_submit',@cfg_order_auto_submit,1);

	-- 连接货品和规格商家编码
	CALL SP_UTILS_GET_CFG_INT('sys_goods_match_concat_code', @cfg_goods_match_concat_code, 0);

	-- 自动匹配平台货品的截取字符
	CALL SP_UTILS_GET_CFG_CHAR('goods_match_split_char', @cfg_goods_match_split_char, '');	
	
	-- 动态跟踪自动匹配货品
	-- CALL SP_UTILS_GET_CFG_INT('goods_match_dynamic_check', @cfg_goods_match_dynamic_check, 0);
	
	-- 是否自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge', @cfg_auto_merge, 1);
	
	-- 自动合并是否重新计算赠品
	CALL SP_UTILS_GET_CFG_INT('sales_trade_auto_merge_gift', @cfg_auto_merge_gift, 1);
	
	-- 申请退款单是否禁止自动合并
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_merge_ban_refund', @cfg_auto_merge_ban_refund, 0);
	
	-- 订单审核时提示同名未合并
	CALL SP_UTILS_GET_CFG_INT('order_check_warn_has_unmerge', @cfg_order_check_warn_has_unmerge, 1);
	
	-- 延时审核分钟数
	CALL SP_UTILS_GET_CFG_INT('order_delay_check_min', @cfg_delay_check_sec, 0);	
	
	SET @cfg_delay_check_sec = @cfg_delay_check_sec*60;
	
	-- 已付等未付分钟数
	-- CALL SP_UTILS_GET_CFG_INT('order_wait_unpay_min', @cfg_wait_unpay_sec, 0);	
	
	SET @cfg_wait_unpay_sec = @cfg_wait_unpay_sec*60;
	
	-- 大件自动拆分
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split', @cfg_order_auto_split, 0);
	
	-- 大件拆分最大次数
	CALL SP_UTILS_GET_CFG_INT('sales_split_large_goods_max_num', @cfg_sales_split_large_goods_max_num, 50);
	
	-- 按不同仓库自动拆分
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_split_by_warehouse',@cfg_order_auto_split_by_warehouse,0);

	-- 不允许按指定仓库拆分出全是赠品的订单
    CALL SP_UTILS_GET_CFG_INT('order_warehouse_split_check_gift',@cfg_order_warehouse_split_check_gift,0);

	-- 订单合并方式
	CALL SP_UTILS_GET_CFG_INT('order_auto_merge_mode', @cfg_order_merge_mode, 0);	
	-- 审核时提示条件
	CALL SP_UTILS_GET_CFG_INT('order_check_merge_warn_mode', @cfg_order_check_merge_warn_mode, 0);
	
	-- 业务员
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_begin', @cfg_salesman_macro_begin, '');	
	CALL SP_UTILS_GET_CFG_CHAR('salesman_macro_end', @cfg_salesman_macro_end, '');	
	
	
	IF @cfg_salesman_macro_begin='' OR @cfg_salesman_macro_begin IS NULL OR @cfg_salesman_macro_end='' OR @cfg_salesman_macro_end IS NULL THEN
		SET @cfg_salesman_macro_begin='';
		SET @cfg_salesman_macro_end='';
	END IF;
	
	-- 物流选择方式：全局唯一，按店铺，按仓库
	CALL SP_UTILS_GET_CFG_INT('logistics_match_mode', @cfg_logistics_match_mode, 2);	

	-- 启用按货品选仓库策略
	CALL SP_UTILS_GET_CFG_INT('sales_trade_warehouse_bygoods', @cfg_sales_trade_warehouse_bygoods, 0);
	
	-- 启用按货品选物流策略
	CALL SP_UTILS_GET_CFG_INT('sales_trade_logistics_bygoods',@cfg_sales_trade_logistics_bygoods,0);
	
	-- 如果仓库是按货品策略选出,修改时给出提醒
	-- CALL SP_UTILS_GET_CFG_INT('order_check_alert_locked_warehouse', @cfg_chg_locked_warehouse_alert, 0);

	-- 是否启用备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_remark_extract', @cfg_enable_remark_extract, 0);	
	-- 客户备注提取
	CALL SP_UTILS_GET_CFG_INT('order_deliver_c_remark_extract', @cfg_enable_c_remark_extract, 0);	
	-- 订单进入待审核后是否根据备注提取物流
	CALL SP_UTILS_GET_CFG_INT('order_deliver_enable_cs_remark_track', @cfg_order_deliver_enable_cs_remark_track, 1);	
	
	-- 自动按商家编码匹配货品
	CALL SP_UTILS_GET_CFG_INT('apigoods_auto_match', V_AutoMatchGoods, 1);	
	
	-- 转预订单设置
	 CALL SP_UTILS_GET_CFG_INT('order_go_preorder', @cfg_order_go_preorder, 0);
	IF @cfg_order_go_preorder THEN
		CALL SP_UTILS_GET_CFG_INT('order_preorder_lack_stock', @cfg_order_preorder_lack_stock, 0);
		CALL SP_UTILS_GET_CFG_INT('preorder_split_to_order_condition',@cfg_preorder_split_to_order_condition,0);
	END IF;

	CALL SP_UTILS_GET_CFG_INT('remark_change_block_stockout', @cfg_remark_change_block_stockout, 1);
	-- 物流同步后,发生退款不拦截
	CALL SP_UTILS_GET_CFG_INT('unblock_stockout_after_logistcs_sync', @cfg_unblock_stockout_after_logistcs_sync, 0);
	
	-- 销售凭证自动过账
	-- CALL SP_UTILS_GET_CFG_INT('fa_sales_auto_post', @cfg_fa_sales_auto_post, 1);
	
	-- 米氏抢单全局开关
	-- CALL SP_UTILS_GET_CFG_INT('order_deliver_hold', @cfg_order_deliver_hold, 0);
	
	--  根据重量计算物流
	CALL SP_UTILS_GET_CFG_INT('calc_logistics_by_weight',@cfg_calc_logistics_by_weight,0);
	
	--  包装策略
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy', @cfg_open_package_strategy,0); 
	-- CALL SP_UTILS_GET_CFG_INT('open_package_strategy_type',@cfg_open_package_strategy_type,1); -- 1,根据重量   2,根据体积
	
	-- 是否开启订单全链路
	CALL SP_UTILS_GET_CFG_INT('sales_trade_trace_enable', @cfg_sales_trade_trace_enable, 1);
	-- 订单中原始货品数量是否包含赠品
	CALL SP_UTILS_GET_CFG_INT('sales_raw_count_exclude_gift',@cfg_sales_raw_count_exclude_gift,0);
	
	-- 订单递交是否拦截已发货订单
	CALL SP_UTILS_GET_CFG_INT('order_deliver_block_consign',@cfg_order_deliver_block_consign,0);
	
	-- 订单包含组合装时按照组合装重量计算订单重量
	CALL SP_UTILS_GET_CFG_INT('order_cal_weight_by_suite',@cfg_order_cal_weight_by_suite,0);
	
	-- 平台更换货品订单是否需要自动更换
	CALL SP_UTILS_GET_CFG_INT('order_deliver_auto_exchange',@cfg_order_deliver_auto_exchange,0);

	-- 订单退款拦截未发货包含赠品的同一买家的订单 
	CALL SP_UTILS_GET_CFG_INT('sales_trade_refund_block_gift',@cfg_sales_trade_refund_block_gift,0);

  -- 订单货品摘要生成方式
	CALL SP_UTILS_GET_CFG_INT('single_spec_no_code',@cfg_single_spec_no_code,0);

	-- 是否自动生成系统货品
	CALL SP_UTILS_GET_CFG_INT('sys_goods_auto_make',V_AutoMakeSysGoods,0);

	-- 生成货品自动更新库存
	CALL SP_UTILS_GET_CFG_INT('addgoods_refresh_stock',@cfg_addgoods_refresh_stock,0);

	-- 强制凭证不需要审核
	-- SET @cfg_fa_voucher_must_check=0;
	
	-- 是否需要从原始单货品生成api_goods_spec
	IF NOT P_CreateApiGoods THEN
		LEAVE MAIN_LABEL;
	END IF;
	
	/*导入平台货品*/
	START TRANSACTION;
	
	SELECT 1 INTO @tmp_dummy FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE;

	SELECT rec_id INTO V_OldRecId FROM  api_goods_spec ORDER BY rec_id DESC LIMIT 1;

	UPDATE api_goods_spec ag,api_trade_order ato,api_trade at
	SET ag.modify_flag=
		IF(ag.outer_id=ato.goods_no AND ag.spec_outer_id=ato.spec_no, ag.modify_flag, ag.modify_flag|1),
		ag.outer_id=ato.goods_no,ag.spec_outer_id=ato.spec_no,
		ag.goods_name=ato.goods_name,ag.spec_name=ato.spec_name,
		ag.cid=IF(ato.cid='',ag.cid,ato.cid),at.is_new=0
	WHERE at.process_status=10 AND at.is_new=1 AND ato.tid=at.tid AND ato.shop_id=at.shop_id AND ato.goods_id<>''
		AND ag.shop_id=ato.shop_id AND ag.goods_id=ato.goods_id AND ag.spec_id=ato.spec_id;
	
	-- 要测试平台更新编码的同步
	INSERT INTO api_goods_spec(platform_id,goods_id,spec_id,status,shop_id,goods_name,spec_name,outer_id,spec_outer_id,price,cid,modify_flag,created)
	(
		SELECT ato.platform_id,ato.goods_id,ato.spec_id,1,at.shop_id,ato.goods_name,ato.spec_name,ato.goods_no,ato.spec_no,ato.price,ato.cid,1,NOW()
		FROM api_trade_order ato INNER JOIN api_trade at ON ato.tid=at.tid AND ato.shop_id=at.shop_id
		WHERE at.process_status=10 AND at.is_new=1 AND ato.goods_id<>'' AND at.platform_id<>0 
	)
	ON DUPLICATE KEY UPDATE modify_flag=
		IF(api_goods_spec.outer_id=VALUES(outer_id) AND api_goods_spec.spec_outer_id=VALUES(spec_outer_id), api_goods_spec.modify_flag, api_goods_spec.modify_flag|1),
		outer_id=VALUES(outer_id),spec_outer_id=VALUES(spec_outer_id),
		goods_name=VALUES(goods_name),spec_name=VALUES(spec_name),
		cid=IF(VALUES(cid)='',api_goods_spec.cid,VALUES(cid));
	
	UPDATE api_trade SET is_new=0 WHERE process_status=10 and is_new=1;
	COMMIT;

  IF V_AutoMakeSysGoods THEN
    -- 对于新增的平台货品自动生成系统货品
    SELECT  GROUP_CONCAT(rec_id) INTO V_NewRecID  FROM api_goods_spec WHERE rec_id>V_OldRecId LIMIT 1000;    
    CALL I_DL_INIT_AUTO_MAKE_SYS_GOODS(V_NewRecID,@cur_uid);
  END IF;
	
	IF V_AutoMatchGoods  OR V_AutoMakeSysGoods THEN
		-- 对新增和变化的平台货品进行自动匹配
		UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(IF(@cfg_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF(@cfg_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no
			FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;

		/*UPDATE api_goods_spec gs INNER JOIN 
			(SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs 
			WHERE gs.modify_flag>0 AND gs.is_manual_match=0 AND gs.status>0) tmp ON gs.rec_id=tmp.rec_id
			LEFT JOIN goods_merchant_no mn ON(mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>'')
		SET gs.match_target_type=IFNULL(mn.type,0),
			gs.match_target_id=IFNULL(mn.target_id,0),
			gs.match_code=IFNULL(mn.merchant_no,''),
			gs.is_stock_changed=IF(gs.match_target_id,1,0),
			gs.is_deleted=0;*/
		
		-- 刷新品牌分类
		UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
		SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;
		
		UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
		SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
		WHERE ag.modify_flag>0 AND ag.status>0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;
		
		-- 刷新未匹配货品
		UPDATE api_trade_order ato,api_goods_spec ag,api_trade ax
		SET ato.is_invalid_goods=0,ax.bad_reason=0
		WHERE ato.is_invalid_goods=1 AND ag.`shop_id`=ato.`shop_id` AND ag.`goods_id`=ato.`goods_id` AND
			ag.`spec_id`=ato.`spec_id` AND ax.shop_id=ato.`shop_id` AND ax.tid=ato.tid AND ax.trade_status<40 AND
			ag.match_target_type>0; 
		
		-- 自动刷新库存同步规则
		-- 应该判断一下规则是否变化了，如果变化了，要触发同步开关????????????
		CALL I_DL_INIT_REFRESH_STOCK_SYNC();
		/* UPDATE api_goods_spec gs,
		(SELECT * FROM  
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn	
			FROM api_goods_spec ag FORCE INDEX(IX_api_goods_spec_modify_flag)
			LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list) AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`)) 
			WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0 AND (ag.modify_flag & 1) AND ag.status>0 ORDER BY rule.priority DESC
			) 
			_ALIAS_ GROUP BY rec_id 
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1)
		WHERE gs.rec_id=da.rec_id; */
		UPDATE api_goods_spec SET modify_flag=(modify_flag&~1) WHERE modify_flag>0 AND (modify_flag&1);
	END IF;
	
END//
DELIMITER ;