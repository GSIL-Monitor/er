DROP PROCEDURE IF EXISTS `I_DL_SEND_GIFT`;
DELIMITER //
CREATE PROCEDURE `I_DL_SEND_GIFT`(IN `P_OperatorID` INT, IN `P_TradeID` INT, IN `P_CustomerID` INT, INOUT `P_SendOK` INT)
    SQL SECURITY INVOKER
    COMMENT '计算赠品'
MAIN_LABEL:BEGIN
	DECLARE V_NOT_FOUND INT DEFAULT(0);
	
	/*使用变量*/
	DECLARE VS_sel_rule_group,VS_spec_match,VS_suite_match,
		VS_class_num,VS_suite_class_num,VS_spec_class_num,
		VS_brand_num,VS_spec_brand_num,VS_suite_brand_num,
		
		VS_brand_multiple_num,VS_spec_brand_multiple_num,VS_suite_brand_multiple_num,VS_brand_mutiple,
		VS_class_multiple_num,VS_spec_class_multiple_num,VS_suite_class_multiple_num,VS_class_mutiple,
		
		VS_specify_mutiple,VS_real_multiple,VS_real_limit,VS_priority,
		
		VS_keyword_len,VS_begin ,VS_end,VS_num,VS_total_cs_remark_num,VS_total_name_num,
		VS_real_gift_num,VS_rec_id,V_Exists,V_First,VS_cur_count,VS_cur_rule,VS_receivable_mutiple INT DEFAULT(0) ;
	
	DECLARE VS_pos TINYINT DEFAULT(1);
	DECLARE V_ApiTradeID BIGINT DEFAULT(0);
	
	DECLARE 
		VS_class_amount,VS_suite_class_amount,VS_spec_class_amount,
		VS_brand_amount,VS_spec_brand_amount,VS_suite_brand_amount,VS_post_cost
		DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VS_type,VT_delivery_term TINYINT DEFAULT(1);
	DECLARE VS_goods_no VARCHAR(40) DEFAULT('');
	
	
	/*子订单变量*/
	DECLARE VTO_spec_id,VTO_suite_id,VTO_num,VTO_suite_num,VTO_share_amount INT ;
	DECLARE VT_trade_no,VTO_goods_name,VTO_spec_name VARCHAR(150) DEFAULT('');
	DECLARE VTO_amount DECIMAL(19, 4) DEFAULT(0.0000);
	
	/*订单变量*/
	DECLARE VT_shop_id,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,VT_remark_flag,
		VT_receiver_province,VT_receiver_city,VT_receiver_district INT ;
	
	DECLARE VS_NOW,VT_trade_time,VT_pay_time,V_start_time,V_end_time DATETIME;
	DECLARE VT_goods_amount,VT_post_amount,VT_discount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost DECIMAL(19, 4) DEFAULT(0.0000);
	
	DECLARE VT_buyer_message,VT_cs_remark,V_ClassPath,VT_receiver_address VARCHAR(1024);
	
	/*规则列表变量*/
	DECLARE V_rule_type BIGINT DEFAULT(0) ;
	
	DECLARE V_send_spec_id,V_send_is_suite,V_send_gift_num INT DEFAULT(0);
	
	DECLARE V_rule_id,V_rule_priority,V_rule_group,V_rule_multiple_type,
		V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
		V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
		V_limit_customer_send_count,V_cur_gift_send_count,V_max_gift_send_count,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_breceivable_multiple,V_limit_receivable_count INT;
		
	DECLARE V_bbuyer_message,V_bcs_remark,V_time_type,V_is_enough_gift,V_is_specify_sum TINYINT;
	DECLARE V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,
		V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,
		V_min_class_amount,V_max_class_amount,V_min_brand_amount,V_max_brand_amount,V_limit_gift_stock,V_receivable_multiple_amount DECIMAL(19, 4) DEFAULT(0.0000);
	DECLARE V_class_amount_type,V_brand_amount_type,V_terminal_type INT;
	DECLARE V_rule_no,V_rule_name,V_flag_type,V_shop_list,V_logistics_list,V_warehouse_list,V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,
		V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_buyer_message_key_word,V_addr_key_word VARCHAR(150);
	
	DECLARE V_GiftIsRandom INT DEFAULT(0);
	
	-- 赠品规则
	DECLARE rule_cursor CURSOR FOR SELECT  rec_id,rule_no,rule_name,rule_priority,rule_group,is_enough_gift,limit_gift_stock,rule_multiple_type,rule_type,bbuyer_message,bcs_remark,flag_type,time_type,start_time,end_time,shop_list,logistics_list,warehouse_list,
		min_goods_count,max_goods_count,min_goods_type_count,max_goods_type_count,min_specify_count,max_specify_count,min_class_count,max_class_count,class_count_type,min_brand_count,max_brand_count,brand_count_type,
		specify_count,bspecify_multiple,limit_specify_count,class_multiple_count,bclass_multiple,limit_class_count,class_multiple_type,brand_multiple_count,bbrand_multiple,limit_brand_count,brand_multiple_type,
		min_goods_amount,max_goods_amount,min_receivable,max_receivable,min_nopost_receivable,max_nopost_receivable,min_post_amount,max_post_amount,min_weight,max_weight,min_post_cost,max_post_cost,min_specify_amount,max_specify_amount,is_specify_sum,
		min_class_amount,max_class_amount,class_amount_type,min_brand_amount,max_brand_amount,brand_amount_type,
		buyer_rank,pay_start_time,pay_end_time,trade_start_time,trade_end_time,terminal_type,
		goods_key_word,spec_key_word,csremark_key_word,unit_key_word,limit_customer_send_count,cur_gift_send_count,max_gift_send_count,
		buyer_message_key_word,addr_key_word,min_no_specify_count,max_no_specify_count,buyer_class,receivable_multiple_amount,breceivable_multiple,limit_receivable_count,gift_is_random   
		FROM  cfg_gift_rule rule 
		WHERE rule.is_disabled=0 ORDER BY rule_group,rule_priority desc;
	
	-- 子订单信息(单品)
	DECLARE trade_order_cursor1 CURSOR FOR SELECT spec_id,actual_num,share_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 and sto.suite_id=0;
	
	-- 子订单信息(组合装)
	DECLARE trade_order_cursor2 CURSOR FOR SELECT suite_id,suite_num,suite_amount
		FROM  sales_trade_order sto
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0 AND sto.suite_id>0 group by sto.suite_id;
	
	
	-- 子订单名称信息(组合装名称只取一次)
	DECLARE trade_order_name_cursor CURSOR FOR SELECT distinct ato.goods_name,ato.spec_name
		FROM api_trade_order ato 
		LEFT JOIN sales_trade_order sto 
		ON (ato.shop_id=sto.shop_id AND ato.oid=sto.src_oid) 
		WHERE sto.trade_id=P_TradeID AND sto.gift_type=0;
	
	
	-- 赠品数量范围
	DECLARE send_goods_cursor CURSOR FOR SELECT spec_id,gift_num,is_suite
		FROM cfg_gift_send_goods
		WHERE rule_id=V_rule_id AND priority=VS_priority;
	
	-- 赠品随机赠送
	DECLARE send_goods_random CURSOR FOR SELECT spec_id,gift_num,is_suite
		FROM (SELECT * FROM cfg_gift_send_goods where rule_id=V_rule_id AND priority=VS_priority ORDER BY RAND()) cgsg
		GROUP BY gift_group;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET V_NOT_FOUND = 1;
	
	SET VS_NOW = NOW();	
		-- 订单信息
	SELECT trade_no,shop_id,trade_time,pay_time,goods_count,goods_type_count,customer_id,warehouse_id,logistics_id,
		receiver_province,receiver_city,receiver_district,buyer_message,cs_remark,remark_flag,
		goods_amount,post_amount,receivable,receivable-post_amount,weight,post_cost,delivery_term,receiver_address
	INTO VT_trade_no,VT_shop_id,VT_trade_time,VT_pay_time,VT_goods_count,VT_goods_type_count,VT_customer_id,VT_warehouse_id,VT_logistics_id,
		VT_receiver_province,VT_receiver_city,VT_receiver_district,VT_buyer_message,VT_cs_remark,VT_remark_flag,
		VT_goods_amount,VT_post_amount,VT_receivable,VT_nopost_receivable,VT_weight,VT_post_cost,VT_delivery_term,VT_receiver_address
	FROM  sales_trade st
	WHERE st.trade_id=P_TradeID;
		
	IF V_NOT_FOUND = 1 THEN
		SET V_NOT_FOUND = 0;
		LEAVE MAIN_LABEL;
	END IF;
	
	-- 记录选中的分组
	SET @sys_code=0, @sys_message='OK';
	SET VS_sel_rule_group=-1;
	SET V_First=1;
	
	OPEN rule_cursor;
	GIFT_RULE_LABEL: LOOP
		SET V_NOT_FOUND=0;
		SET VS_total_name_num =0;
		SET VS_total_cs_remark_num =0;
		SET VS_cur_count = 0;
		FETCH rule_cursor INTO V_rule_id,V_rule_no,V_rule_name,V_rule_priority,V_rule_group,V_is_enough_gift,V_limit_gift_stock,V_rule_multiple_type,V_rule_type,V_bbuyer_message,V_bcs_remark,V_flag_type,V_time_type,V_start_time,V_end_time,V_shop_list,V_logistics_list,V_warehouse_list,
			V_min_goods_count,V_max_goods_count,V_min_goods_type_count,V_max_goods_type_count,V_min_specify_count,V_max_specify_count,V_min_class_count,V_max_class_count,V_class_count_type,V_min_brand_count,V_max_brand_count,V_brand_count_type,
			V_specify_count,V_bspecify_multiple,V_limit_specify_count,V_class_multiple_count,V_bclass_multiple,V_limit_class_count,V_class_multiple_type,V_brand_multiple_count,V_bbrand_multiple,V_limit_brand_count,V_brand_multiple_type,
			V_min_goods_amount,V_max_goods_amount,V_min_receivable,V_max_receivable,V_min_nopost_receivable,V_max_nopost_receivable,V_min_post_amount,V_max_post_amount,V_min_weight,V_max_weight,V_min_post_cost,V_max_post_cost,V_min_specify_amount,V_max_specify_amount,V_is_specify_sum,
			V_min_class_amount,V_max_class_amount,V_class_amount_type,V_min_brand_amount,V_max_brand_amount,V_brand_amount_type,
			V_buyer_rank,V_pay_start_time,V_pay_end_time,V_trade_start_time,V_trade_end_time,V_terminal_type,
			V_goods_key_word,V_spec_key_word,V_csremark_key_word,V_unit_key_word,V_limit_customer_send_count,V_cur_gift_send_count,
			V_max_gift_send_count,V_buyer_message_key_word,V_addr_key_word,V_min_no_specify_count,V_max_no_specify_count,V_buyer_class,V_receivable_multiple_amount,V_breceivable_multiple,V_limit_receivable_count,V_GiftIsRandom;
		
		IF V_NOT_FOUND <> 0 THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		/*一个分组内只匹配一个赠品规则*/
		IF VS_sel_rule_group !=-1 AND VS_sel_rule_group=V_rule_group THEN
			ITERATE  GIFT_RULE_LABEL;
		END IF;
		
		/*此规则下没有设置赠品*/
		SELECT count(1) INTO VS_rec_id FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;
		
		-- VS_specify_mutiple VS_class_mutiple VS_brand_mutiple 
		-- 都满足的情况下VS_real_multiple来记录最小(大)的倍数关系
		
		IF V_rule_multiple_type=0 THEN 
			SET VS_real_multiple=10000;
			SET VS_real_limit=10000;
		ELSE 
			SET VS_real_multiple=-10000;
			SET VS_real_limit=-10000;	
		END IF;
	
		/*检查该赠品都设置了哪些条件*/
		
		/*检查订单是否满足用户设置的赠品条件*/
		
		/*买家留言*/
		/*IF (V_rule_type & 1) THEN
			IF  V_bbuyer_message THEN 
				IF  VT_buyer_message IS NOT NULL AND  VT_buyer_message<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_buyer_message IS  NULL OR  VT_buyer_message='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		/*客服备注*/
		/*IF V_rule_type & (1<<1) THEN
			IF  V_bcs_remark THEN
				IF  VT_cs_remark IS NOT NULL AND  VT_cs_remark<>'' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_cs_remark IS  NULL OR  VT_cs_remark='' THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;	
			END IF; 
		END IF;*/
		
		
		/*淘宝标旗*/
		/*IF V_rule_type & (1<<2) THEN
			IF FIND_IN_SET(VT_remark_flag,V_flag_type)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;*/
		
		/*有效期*/
		IF V_rule_type & (1<<3) THEN
			IF V_time_type=1 AND VT_delivery_term=1 THEN 
				IF VT_pay_time<V_start_time OR VT_pay_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 3 OR VT_delivery_term=2 THEN
				IF VT_trade_time<V_start_time OR VT_trade_time>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSEIF V_time_type = 2 THEN
				IF VS_NOW<V_start_time OR VS_NOW>V_end_time THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				ITERATE  GIFT_RULE_LABEL;
			END IF; 
		END IF;
		
		
		/*店铺*/
		IF V_rule_type & (1<<4) THEN
			IF FIND_IN_SET(VT_shop_id,V_shop_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		
		/*物流公司*/
		/*IF V_rule_type & (1<<5) THEN
			IF FIND_IN_SET(VT_logistics_id,V_logistics_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*仓库*/
		/*IF V_rule_type & (1<<6) THEN
			IF FIND_IN_SET(VT_warehouse_id,V_warehouse_list)=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*货品总数*/
		-- 此处有问题，合并时未刷新货品
		IF V_rule_type & (1<<7) THEN
			IF V_max_goods_count=0 THEN
				IF  VT_goods_count<V_min_goods_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_count<V_min_goods_count OR VT_goods_count>V_max_goods_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
			
		/*货品种类*/
		/*IF V_rule_type & (1<<8) THEN
			IF V_max_goods_type_count=0 THEN
				IF  VT_goods_type_count<V_min_goods_type_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_type_count<V_min_goods_type_count OR VT_goods_type_count>V_max_goods_type_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*货款总额*/
		IF V_rule_type & (1<<15) THEN
			IF V_max_goods_amount=0 THEN
				IF  VT_goods_amount<V_min_goods_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_goods_amount<V_min_goods_amount OR VT_goods_amount>V_max_goods_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
		/*实收(包含邮费)*/
		IF V_rule_type & (1<<16) THEN
			IF V_max_receivable=0 THEN
				IF  VT_receivable<V_min_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_receivable<V_min_receivable OR VT_receivable>V_max_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;
		
			
		/*实收(去除邮费)*/
		/*IF V_rule_type & (1<<17) THEN
			IF V_max_nopost_receivable=0 THEN
				IF  VT_nopost_receivable<V_min_nopost_receivable THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_nopost_receivable<V_min_nopost_receivable OR VT_nopost_receivable>V_max_nopost_receivable THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*邮费*/
		/*IF V_rule_type & (1<<18) THEN
			IF V_max_post_amount=0 THEN
				IF  VT_post_amount<V_min_post_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VT_post_amount<V_min_post_amount OR VT_post_amount>V_max_post_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
		
		/*预估重量*/
		/*IF V_rule_type & (1<<19) THEN
			IF V_max_weight=0 THEN
				IF  VT_weight<V_min_weight THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_weight<V_min_weight OR VT_weight>V_max_weight THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;	
		END IF;*/
			
		/*预估邮费成本*/
		/*IF V_rule_type & (1<<20) THEN
			IF V_max_post_cost=0 THEN
				IF  VT_post_cost<V_min_post_cost THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VT_post_cost<V_min_post_cost OR VT_post_cost>V_max_post_cost THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*客服备注关键字*/
		IF V_rule_type & (1<<30) THEN
			IF (VT_cs_remark IS NULL OR VT_cs_remark='') THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_csremark_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			ELSE 
				IF NOT LOCATE(V_csremark_key_word, VT_cs_remark) THEN
				-- IF (SELECT VT_cs_remark NOT LIKE CONCAT_WS('','%',V_csremark_key_word,'%')) THEN 
					ITERATE GIFT_RULE_LABEL;
				END IF;
			END IF;
			
			/*客服备注：AAA1支 2支BBB 1支AAA*/
			
			SET VS_keyword_len = CHARACTER_LENGTH(V_csremark_key_word);
			SET VS_pos = 1;
			SET VS_num=0;
			SET VS_total_cs_remark_num=0;
			SET VS_begin=0;
			SET VS_end=0;
			
			CS_REMARK_KEYWORD_LABEL:LOOP
				SET VS_begin = LOCATE(V_csremark_key_word, VT_cs_remark, VS_pos);
				IF VS_begin = 0 THEN
					LEAVE CS_REMARK_KEYWORD_LABEL;
				END IF;
				
				IF V_unit_key_word<>'' THEN 
					SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin - 1);
					IF VS_end > 0 AND VS_begin >VS_end THEN
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 2, 2));
						IF VS_num = 0 THEN
							SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_end - 1, 1));
						END IF;
						
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos =VS_keyword_len+VS_begin;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					ELSE
						SET VS_end = LOCATE(V_unit_key_word, VT_cs_remark, VS_begin);
						SET VS_num = FN_STR_TO_NUM(SUBSTRING(VT_cs_remark, VS_begin + VS_keyword_len, VS_end - VS_begin - VS_keyword_len));
						IF VS_num > 0 THEN
							SET VS_total_cs_remark_num=VS_total_cs_remark_num+VS_num;
							SET VS_pos = VS_end;
						ELSE
							LEAVE CS_REMARK_KEYWORD_LABEL;
						END IF;
					END IF;
				ELSE
					SET VS_total_cs_remark_num=VS_total_cs_remark_num+1;
					LEAVE CS_REMARK_KEYWORD_LABEL;	
				END IF;
			END LOOP; -- CS_REMARK_KEYWORD_LABEL
		END IF;
		
		
		/*
		指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		IF V_rule_type & (1<<9) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_specify_count AND tgto.num<=V_max_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类数量范围 注意组合装*/
		/*IF V_rule_type & (1<<10) THEN
			-- 未指定分类
			if V_class_count_type=0 then 
				ITERATE  GIFT_RULE_LABEL;
			end if;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_count_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_class_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_num=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_count=0 THEN
				IF  VS_class_num<V_min_class_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_num<V_min_class_count OR VS_class_num>V_max_class_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌数量范围 注意组合装*/
		
		/*IF V_rule_type & (1<<11) THEN
			-- 未指定品牌
			IF V_brand_count_type=0  THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_num=0;
			
			SELECT IFNULL(SUM(num),0) INTO VS_brand_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_count_type;
			
			IF VS_brand_num=0 THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_count=0 THEN
				IF  VS_brand_num<V_min_brand_count THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_brand_num<V_min_brand_count OR VS_brand_num>V_max_brand_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*
		指定货品数量备增 
		cfg_gift_attend_goods goods_type=2记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		如果是倍增关系需要计算出来倍数关系用于I_DL_SELECT_GIFT计算库存
		*/
		/*IF V_rule_type & (1<<12) THEN
			-- 参加活动的单品集合为空
			IF V_specify_count<=0 OR NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=2) THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(tgto.num),0) INTO VTO_num
			FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
			WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=2;
			
			SET VS_specify_mutiple=FLOOR(VTO_num/V_specify_count);
			IF VS_specify_mutiple =0 OR VS_specify_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bspecify_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_specify_mutiple THEN 
						SET VS_real_multiple=VS_specify_mutiple;
						SET VS_real_limit=V_limit_specify_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
		
		/*
		指定货品数量备增 
		cfg_gift_attend_goods goods_type=2记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		如果是倍增关系需要计算出来倍数关系用于I_DL_SELECT_GIFT计算库存
		*/
		IF V_rule_type & (1<<12) THEN
			-- 参加活动的单品集合为空
			IF V_specify_count<=0 OR NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=2) THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(tgto.num),0) INTO VTO_num
			FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
			WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=2;

			SET VS_specify_mutiple=FLOOR(VTO_num/V_specify_count);
			IF VS_specify_mutiple =0 OR VS_specify_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF VS_real_multiple>VS_specify_mutiple THEN 
				SET VS_real_multiple=VS_specify_mutiple;
				SET VS_real_limit=V_limit_specify_count;
			END IF;
			
		END IF;	
			
		/*指定分类数量倍增*/
		/*IF V_rule_type & (1<<13) THEN
			-- 未指定分类
			IF V_class_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_multiple_type;
			IF V_NOT_FOUND THEN
				ITERATE GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			SET VS_class_mutiple=FLOOR(VTO_num/V_class_multiple_count);
			IF VS_class_mutiple =0 OR VS_class_mutiple IS NULL THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bclass_multiple=1 THEN
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_class_mutiple THEN 
						SET VS_real_multiple=VS_class_mutiple;
						SET VS_real_limit=V_limit_class_count;
					END IF;
				END IF;
			END IF;
		END IF;*/
		-- VS_class_mutiple,V_limit_class_count 传递给I_DL_SELECT_GIFT		
		
		
		
		/*指定品牌数量倍增*/
		/*IF V_rule_type & (1<<14) THEN
			-- 未指定品牌
			IF V_brand_multiple_type=0  THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VTO_num=0;
			SELECT IFNULL(SUM(num),0) INTO VTO_num
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_multiple_type;
			
			SET VS_brand_mutiple=FLOOR(VTO_num/V_brand_multiple_count);
			IF VS_brand_mutiple =0 OR VS_brand_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_bbrand_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_brand_mutiple THEN 
						SET VS_real_multiple=VS_brand_mutiple;
						SET VS_real_limit=V_limit_brand_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
			
		-- VS_brand_mutiple,V_limit_brand_count 传递给I_DL_SELECT_GIFT
		
		/*
		指定货品金额范围 
		cfg_gift_attend_goods goods_type=3记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		组合装金额suie_amount 
		*/
		
		IF V_rule_type & (1<<21) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=3) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			--  判断计算方式（单个还是多个的和）
			IF V_is_specify_sum THEN
				SELECT IFNULL(SUM(tgto.amount),0) INTO VTO_amount
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3;
				IF V_max_specify_amount=0 THEN
					IF VTO_amount>=V_min_specify_amount THEN
						SET V_Exists=1;
					END IF;
				ELSE
					IF VTO_amount>=V_min_specify_amount && VTO_amount<=V_max_specify_amount THEN
						SET V_Exists=1;
					END IF;
				END IF;
			ELSE				
				IF V_max_specify_amount=0 THEN
					SELECT 1 INTO V_Exists
					FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
					WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount LIMIT 1;
				ELSE
					SELECT 1 INTO V_Exists
					FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
					WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=3 AND tgto.amount IS NOT NULL AND tgto.amount>=V_min_specify_amount AND tgto.amount<=V_max_specify_amount LIMIT 1;
				END IF;

			END IF;
			
			-- 无满足条件的
			IF V_Exists =0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;
		
		/*指定分类金额范围 注意组合装*/
		/*IF V_rule_type & (1<<22) THEN
			-- 未指定分类
			IF V_class_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET V_NOT_FOUND=0;
			SELECT path INTO V_ClassPath FROM goods_class WHERE class_id=V_class_amount_type;
			IF V_NOT_FOUND THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET V_ClassPath=CONCAT(V_ClassPath, '%');
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_class_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_class_amount
			FROM tmp_gift_trade_order 
			WHERE class_path LIKE V_ClassPath;
			
			IF VS_class_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_class_amount=0 THEN
				IF  VS_class_amount<V_min_class_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF  VS_class_amount<V_min_class_amount OR VS_class_amount>V_max_class_amount THEN  
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*指定品牌金额范围 注意组合装*/
		/*IF V_rule_type & (1<<23) THEN
			-- 未指定品牌
			IF V_brand_amount_type =0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			SET VS_brand_amount=0;
			
			SELECT IFNULL(SUM(amount),0) INTO VS_brand_amount
			FROM tmp_gift_trade_order 
			WHERE brand_id=V_brand_amount_type;
			
			IF VS_brand_amount=0 THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_max_brand_amount=0 THEN
				IF  VS_brand_amount<V_min_brand_amount THEN 
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			ELSE
				IF VS_brand_amount<V_min_brand_amount OR VS_brand_amount>V_max_brand_amount THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		
		/*客户地区*/
		/*IF V_rule_type & (1<<24) THEN
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_buyer_area WHERE rule_id=V_rule_id AND province_id=VT_receiver_province AND city_id=VT_receiver_city) THEN 
				ITERATE GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		
		/*客户等级V_buyer_rank fixme P_CustomerID*/
		-- ELSEIF VS_type = 26 THEN
		-- ITERATE  GIFT_RULE_LABEL;
		
		/*付款时间*/
		/*IF V_rule_type & (1<<26) THEN 
			IF (DATE_FORMAT(VT_pay_time,'%H:%i:%s')<V_pay_start_time OR DATE_FORMAT(VT_pay_time,'%H:%i:%s')>V_pay_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*拍单时间*/
		/*IF V_rule_type & (1<<27)  THEN 
			IF (DATE_FORMAT(VT_trade_time,'%H:%i:%s')<V_trade_start_time OR DATE_FORMAT(VT_trade_time,'%H:%i:%s')>V_trade_end_time) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*终端类型*/
		/*IF V_rule_type & (1<<28)  THEN
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			
			IF V_terminal_type=2 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&1)=0) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_terminal_type=1 AND EXISTS(SELECT 1 FROM tmp_gift_trade_order WHERE (from_mask&2)) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		
		/*宝贝关键字*/
		IF V_rule_type & (1<<29) THEN
			IF V_goods_key_word=''AND V_spec_key_word='' THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_goods_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.shop_id=sto.shop_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.goods_name 
									LIKE CONCAT_WS('','%',V_goods_key_word,'%')
									) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			IF V_spec_key_word<>'' AND NOT EXISTS(SELECT 1 FROM api_trade_order ato 
								LEFT JOIN sales_trade_order sto 
								ON (ato.shop_id=sto.shop_id AND  ato.oid=sto.src_oid) 
								WHERE sto.trade_id=P_TradeID 
									AND sto.gift_type=0 
									AND ato.spec_name 
									LIKE CONCAT_WS('','%',V_spec_key_word,'%')
								) THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			SET VS_total_name_num=0;
			OPEN trade_order_name_cursor;
			NAME_LABEL: LOOP
				SET V_NOT_FOUND=0;
				FETCH trade_order_name_cursor INTO VTO_goods_name,VTO_spec_name;
					IF V_NOT_FOUND <> 0 THEN
						LEAVE NAME_LABEL;
					END IF;
					
					IF V_goods_key_word<>'' AND V_spec_key_word='' AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word='' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
					IF V_spec_key_word<>'' AND V_goods_key_word<>'' AND (SELECT VTO_spec_name LIKE CONCAT_WS('','%',V_spec_key_word,'%')) AND (SELECT VTO_goods_name LIKE CONCAT_WS('','%',V_goods_key_word,'%')) THEN 
						SET VS_total_name_num=VS_total_name_num+1;
					END IF;
					
				END LOOP; -- NAME_LABEL
			CLOSE trade_order_name_cursor;
		END IF;

		/*指定赠送次数(适用于前多少名的赠送方式)*/
		/*IF V_rule_type & (1<<31) THEN
			IF V_max_gift_send_count AND V_cur_gift_send_count>=V_max_gift_send_count THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定赠品根据客户限送次数*/
		/*IF V_rule_type & (1<<32) THEN
			IF V_limit_customer_send_count THEN
				SELECT COUNT(1) INTO VS_cur_count FROM sales_gift_record  WHERE rule_id = V_rule_id AND customer_id = P_CustomerID AND created>=V_start_time AND created<=V_end_time;
				IF VS_cur_count >= V_limit_customer_send_count THEN
					ITERATE  GIFT_RULE_LABEL;
				END IF;
			END IF;
		END IF;*/
		
		/*指定买家留言关键词*/
		/*IF V_rule_type & (1<<33) THEN
			IF V_buyer_message_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_buyer_message = ''  OR VT_buyer_message IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_buyer_message_key_word, VT_buyer_message) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*指定收件人地址关键词*/
		/*IF V_rule_type & (1<<34) THEN
			IF V_addr_key_word = '' THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF (VT_receiver_address = ''  OR VT_receiver_address IS NULL) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT LOCATE(V_addr_key_word, VT_receiver_address) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/
		/*
		不送指定货品数量范围 
		cfg_gift_attend_goods goods_type=1记录的是一个集合 
		只要订单中有单品属于这个集合就满足
		或的关系
		注意组合装
		*/
		/*IF V_rule_type & (1<<35) THEN
			-- 参加活动的单品集合为空
			IF NOT EXISTS(SELECT 1 FROM cfg_gift_attend_goods WHERE rule_id=V_rule_id AND goods_type=1) THEN  
				ITERATE GIFT_RULE_LABEL;
			END IF;
			
			CALL I_DL_PREPARE_GIFT_GOODS(P_TradeID, V_First);
			SET V_Exists=0;
			IF V_max_no_specify_count=0 THEN
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count LIMIT 1;
			ELSE
				SELECT 1 INTO V_Exists
				FROM cfg_gift_attend_goods cgag LEFT JOIN tmp_gift_trade_order tgto ON tgto.is_suite=cgag.is_suite AND tgto.spec_id=cgag.spec_id
				WHERE cgag.rule_id=V_rule_id AND cgag.goods_type=1 AND tgto.num IS NOT NULL AND tgto.num>=V_min_no_specify_count AND tgto.num<=V_max_no_specify_count LIMIT 1;
			END IF;
			
			IF V_Exists THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*客户分组送赠品*/
		/*IF V_rule_type & (1<<36) THEN
			IF  V_buyer_class = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			IF NOT EXISTS(SELECT 1 FROM crm_customer WHERE customer_id = P_CustomerID AND class_id = V_buyer_class) THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
		END IF;*/

		/*订单实收(不包含邮费)倍增*/
		/*IF V_rule_type & (1<<37) THEN
			-- 查看
			IF V_receivable_multiple_amount = 0 THEN
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			SET VS_receivable_mutiple=FLOOR(VT_nopost_receivable/V_receivable_multiple_amount);
			IF VS_receivable_mutiple =0 OR VS_receivable_mutiple IS NULL THEN 
				ITERATE  GIFT_RULE_LABEL;
			END IF;
			
			-- 倍增
			IF V_breceivable_multiple=1 THEN
			
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple>VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				ELSE 
					IF VS_real_multiple<VS_receivable_mutiple THEN 
						SET VS_real_multiple=VS_receivable_mutiple;
						SET VS_real_limit=V_limit_receivable_count;
					END IF;
				END IF;
			END IF;
				
		END IF;*/
		
		-- citying

		/*订单满足赠品条件 根据优先级和库存确定赠品即VS_priority (倍增条件下考虑翻倍数量,数量从客服备注提取的情况下考虑库存)*/
		set V_NOT_FOUND=0;
		set VS_rec_id=0;

		SELECT COUNT(DISTINCT priority),IFNULL(priority,11)
		INTO VS_rec_id,VS_priority 
		FROM  cfg_gift_send_goods WHERE rule_id=V_rule_id;
		
		IF V_NOT_FOUND <> 0 THEN
			SET V_NOT_FOUND=0;
			ITERATE GIFT_RULE_LABEL;
		END IF;

		/*如果开启校验赠品库存,则都要去校验库存,否则的话则多个赠品列表的才去计算优先级*/
		IF  V_is_enough_gift THEN
			SET  VS_priority=11;
			CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,V_limit_gift_stock);
			-- IF VS_priority = 99 THEN
			IF VS_priority > 11 THEN -- 赠品库存数量不足时 VS_priority++ 目前没有做赠品优先级,只要有一个赠品不满足即不赠送货品
				SET  VS_priority=11;
				ITERATE GIFT_RULE_LABEL;
			END IF;
		/*ELSE
			--  指定多个赠品列表的情况下才去按库存计算优先级
			IF VS_rec_id>1 THEN 
				SET  VS_priority=11;
				CALL I_DL_SELECT_GIFT(VS_priority,V_rule_id,V_rule_multiple_type,VS_real_multiple,VS_real_limit,VS_total_name_num,VS_total_cs_remark_num,0);
				IF VS_priority = 99 THEN
					SET VS_priority = 11;
				END IF;
			END IF;*/
		END IF;
		
		/*添加赠品*/
		IF V_GiftIsRandom=0 THEN 
		OPEN send_goods_cursor;
		SEND_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH send_goods_cursor INTO V_send_spec_id,V_send_gift_num,V_send_is_suite;
			-- 设置了规则 却没有设置赠品
			IF V_NOT_FOUND <> 0 THEN
				CLOSE send_goods_cursor;
				ITERATE GIFT_RULE_LABEL;
			END IF;

			-- 目前只按照赠品数量计算(没有客服备注提取、宝贝关键字计算、倍增)
			 SET VS_real_gift_num=V_send_gift_num;
			
			-- 客服备注的优先级最高 名称提取其次
			-- VS_real_gift_num 是真正的赠送数量
			-- SET VS_real_gift_num=0;
			
			IF VS_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=VS_total_cs_remark_num;
				
			ELSEIF VS_total_name_num>0 THEN 
				SET VS_real_gift_num=VS_total_name_num;
				
			ELSE
				-- 有倍增关系 看是否大于VS_real_limit
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;
			END IF ;
			
			    /*IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;*/
			
			IF V_send_is_suite=1 THEN
				SELECT suite_no INTO VS_goods_no FROM goods_suite WHERE suite_id=V_send_spec_id;
			ELSE
				SELECT spec_no INTO VS_goods_no FROM goods_spec WHERE spec_id=V_send_spec_id;
			END IF;
			CALL I_SALES_ORDER_INSERT(P_OperatorID, P_TradeID, 
				V_send_is_suite, V_send_spec_id, 1, VS_real_gift_num, 0, 0, 
				'自动赠品',
				CONCAT_WS ('',"自动添加赠品。使用策略编号：",V_rule_no,"，策略名称：",V_rule_name,"，赠品商家编码：",VS_goods_no),  
				V_ApiTradeID);
			
			-- 失败日志
			IF @sys_code THEN
				-- 回滚事务,否则下面日志无法保存
				ROLLBACK;
				-- 停用此赠品策略
				UPDATE cfg_gift_rule SET is_disabled=1 WHERE rec_id=V_rule_id;
				
				INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
				VALUES(60,P_TradeID,0,P_OperatorID,CONCAT('自动赠送失败,策略编号:', V_rule_no, ' 错误:', @sys_message),NOW());	
				
				INSERT INTO aux_notification(type,message,priority,order_type,order_no)
				VALUES(2,CONCAT('赠品策略异常: ', V_rule_no, ' 错误:', @sys_message, ' 订单:',VT_trade_no, ' 系统已自动停用此策略'), 
					9, 1, VT_trade_no);
				
				LEAVE SEND_GOODS_LABEL;
			ELSE
				IF VS_cur_rule <> V_rule_id THEN
					UPDATE cfg_gift_rule SET history_gift_send_count = history_gift_send_count +1,cur_gift_send_count = cur_gift_send_count +1
					WHERE rec_id=V_rule_id;
					INSERT INTO sales_gift_record(rule_id,trade_id,customer_id,created)
					values(V_rule_id,P_TradeID,VT_customer_id,NOW());
					SET VS_cur_rule = V_rule_id;
				END IF;
				SET P_SendOK=1;
			END IF;
			SET VS_sel_rule_group = V_rule_group;
		END LOOP; -- SEND_GOODS_LABEL
		CLOSE send_goods_cursor;
		ELSE
			OPEN send_goods_random;
		SEND_GOODS_LABEL: LOOP
			SET V_NOT_FOUND=0;
			FETCH send_goods_random INTO V_send_spec_id,V_send_gift_num,V_send_is_suite;
			-- 设置了规则 却没有设置赠品
			IF V_NOT_FOUND <> 0 THEN
				CLOSE send_goods_random;
				ITERATE GIFT_RULE_LABEL;
			END IF;

			-- 目前只按照赠品数量计算(没有客服备注提取、宝贝关键字计算、倍增)
			SET VS_real_gift_num=V_send_gift_num;
			
			-- 客服备注的优先级最高 名称提取其次
			-- VS_real_gift_num 是真正的赠送数量
			-- SET VS_real_gift_num=0;
			
			IF VS_total_cs_remark_num>0 THEN 
				SET VS_real_gift_num=VS_total_cs_remark_num;
				
			ELSEIF VS_total_name_num>0 THEN 
				SET VS_real_gift_num=VS_total_name_num;
				
			ELSE
				-- 有倍增关系 看是否大于VS_real_limit
				IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;
			END IF ;
			
			    /*IF V_rule_multiple_type=0 THEN 
					IF VS_real_multiple<>10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				ELSE
					IF VS_real_multiple<>-10000 THEN 
						SET VS_real_gift_num=VS_real_multiple*V_send_gift_num;
						
						IF VS_real_gift_num>VS_real_limit and VS_real_limit>0  THEN
							SET VS_real_gift_num=VS_real_limit;
						END IF;
					-- 无倍增关系
					ELSE
						SET VS_real_gift_num=V_send_gift_num;
					END IF;
				END IF;*/
			
			IF V_send_is_suite=1 THEN
				SELECT suite_no INTO VS_goods_no FROM goods_suite WHERE suite_id=V_send_spec_id;
			ELSE
				SELECT spec_no INTO VS_goods_no FROM goods_spec WHERE spec_id=V_send_spec_id;
			END IF;
			CALL I_SALES_ORDER_INSERT(P_OperatorID, P_TradeID, 
				V_send_is_suite, V_send_spec_id, 1, VS_real_gift_num, 0, 0, 
				'自动赠品',
				CONCAT_WS ('',"自动添加赠品。使用策略编号：",V_rule_no,"，策略名称：",V_rule_name,"，赠品商家编码：",VS_goods_no),  
				V_ApiTradeID);
			
			-- 失败日志
			IF @sys_code THEN
				-- 回滚事务,否则下面日志无法保存
				ROLLBACK;
				-- 停用此赠品策略
				UPDATE cfg_gift_rule SET is_disabled=1 WHERE rec_id=V_rule_id;
				
				INSERT INTO sales_trade_log(`type`,trade_id,`data`,operator_id,message,created)
				VALUES(60,P_TradeID,0,P_OperatorID,CONCAT('自动赠送失败,策略编号:', V_rule_no, ' 错误:', @sys_message),NOW());	
				
				INSERT INTO aux_notification(type,message,priority,order_type,order_no)
				VALUES(2,CONCAT('赠品策略异常: ', V_rule_no, ' 错误:', @sys_message, ' 订单:',VT_trade_no, ' 系统已自动停用此策略'), 
					9, 1, VT_trade_no);
				
				LEAVE SEND_GOODS_LABEL;
			ELSE
				IF VS_cur_rule <> V_rule_id THEN
					UPDATE cfg_gift_rule SET history_gift_send_count = history_gift_send_count +1,cur_gift_send_count = cur_gift_send_count +1
					WHERE rec_id=V_rule_id;
					INSERT INTO sales_gift_record(rule_id,trade_id,customer_id,created)
					values(V_rule_id,P_TradeID,VT_customer_id,NOW());
					SET VS_cur_rule = V_rule_id;
				END IF;
				SET P_SendOK=1;
			END IF;
			SET VS_sel_rule_group = V_rule_group;
		END LOOP; -- SEND_GOODS_LABEL
		CLOSE send_goods_random;
		END IF;
		IF @sys_code THEN
			LEAVE GIFT_RULE_LABEL;
		END IF;
		
		
	END LOOP; -- GIFT_RULE_LABEL
	CLOSE rule_cursor;
	
END//
DELIMITER ;

