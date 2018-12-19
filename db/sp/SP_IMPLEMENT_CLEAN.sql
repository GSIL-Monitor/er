DROP PROCEDURE IF EXISTS `SP_IMPLEMENT_CLEAN`;
DELIMITER //
CREATE PROCEDURE SP_IMPLEMENT_CLEAN(IN P_CleanId INT)
  SQL SECURITY INVOKER
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	set FOREIGN_KEY_CHECKS = 0;
	IF P_CleanId <> 6 AND P_CleanId <> 7 THEN 
		-- 账款
		TRUNCATE TABLE fa_logistics_fee_order_detail;
		TRUNCATE TABLE fa_logistics_fee_order;
		TRUNCATE TABLE fa_logistics_fee;
		-- TRUNCATE TABLE fa_debt_contacts;
		
		-- 统计
		TRUNCATE TABLE stat_daily_sales_amount;
		TRUNCATE TABLE stat_daily_sales_spec_shop;
		TRUNCATE TABLE stat_daily_sales_spec_warehouse;
		TRUNCATE TABLE stat_monthly_sales_amount;
		TRUNCATE TABLE stat_salesman_performance;
		TRUNCATE TABLE stock_ledger;
		
	END IF;
	
	-- 全清(货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关)
	IF P_CleanId = 1 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		

		-- sales_refund
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;
		
		-- crm
		TRUNCATE TABLE crm_customer_telno;
		TRUNCATE TABLE crm_customer_address;
		TRUNCATE TABLE crm_customer_log;
		TRUNCATE TABLE crm_platform_customer;
		TRUNCATE TABLE crm_customer;
		
		TRUNCATE TABLE crm_sms_record;
		TRUNCATE TABLE cfg_sms_send_rule;
		TRUNCATE TABLE cfg_sms_template;
		
		-- purchase
		TRUNCATE TABLE purchase_provider_goods;
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		DELETE FROM purchase_provider  WHERE id > 0 ;
		
		
		-- outside_wms
		/* TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		
		-- jit
		
		
		-- goods
		TRUNCATE TABLE api_goods_spec;
		TRUNCATE TABLE goods_merchant_no;
		TRUNCATE TABLE goods_barcode;
		TRUNCATE TABLE goods_log;
		
		TRUNCATE TABLE goods_suite_detail;
		TRUNCATE TABLE goods_suite;
		
		
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		TRUNCATE TABLE goods_spec;
		TRUNCATE TABLE goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		TRUNCATE TABLE cfg_gift_attend_goods;
		TRUNCATE TABLE cfg_gift_send_goods;
		TRUNCATE TABLE cfg_goods_warehouse;
		
		
		
		-- stock
		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		
		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		
		TRUNCATE TABLE stockin_order_detail;
		-- TRUNCATE TABLE stock_goods_batch;
		TRUNCATE TABLE stockin_order;
		
		TRUNCATE TABLE stock_logistics_no;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;
		TRUNCATE TABLE stock_change_record;
		-- oa
		
		
		-- hr
		DELETE FROM cfg_employee_rights WHERE employee_id > 1;
		-- DELETE FROM cfg_employee_warehouse WHERE employee_id > 1;
		
		
		DELETE FROM hr_employee WHERE employee_id > 1;
		
		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		DELETE FROM purchase_provider_group WHERE id > 1;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		
		SET FOREIGN_KEY_CHECKS = 1;
	END IF;
	-- 清除货品信息(清除：订单、库存、事务，保留客户、员工信息）	
	IF P_CleanId = 2 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		
		-- sales_refund
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;
		
		-- purchase
		TRUNCATE TABLE purchase_provider_goods;
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		DELETE FROM purchase_provider  WHERE id > 0 ;
		
		
		-- outside_wms
	/* 	TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		
		-- jit
		
		-- goods
		TRUNCATE TABLE api_goods_spec;
		TRUNCATE TABLE goods_merchant_no;
		TRUNCATE TABLE goods_barcode;
		TRUNCATE TABLE goods_log;
		
		TRUNCATE TABLE goods_suite_detail;
		TRUNCATE TABLE goods_suite;
		
		
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		TRUNCATE TABLE goods_spec;
		TRUNCATE TABLE goods_goods;
		DELETE FROM goods_class	 WHERE class_id > 0 ;
		DELETE FROM goods_brand WHERE brand_id > 0;
		TRUNCATE TABLE cfg_gift_attend_goods;
		TRUNCATE TABLE cfg_gift_send_goods;
		TRUNCATE TABLE cfg_goods_warehouse;
		
		-- stock
		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		
		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		
		TRUNCATE TABLE stockin_order_detail;
		-- TRUNCATE TABLE stock_goods_batch;
		TRUNCATE TABLE stockin_order;
		
		TRUNCATE TABLE stock_logistics_no;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;
		TRUNCATE TABLE stock_change_record;
		-- oa
		
		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		DELETE FROM purchase_provider_group WHERE id > 1;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	-- 清除客户资料(清除：订单、库存、事务，保留货品(单品、组合装)、员工、供应商信息)
	IF P_CleanId = 3 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		
		-- sales_refund
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;
		
		-- crm
		TRUNCATE TABLE crm_customer_telno;
		TRUNCATE TABLE crm_customer_address;
		TRUNCATE TABLE crm_customer_log;
		TRUNCATE TABLE crm_platform_customer;
		TRUNCATE TABLE crm_customer;
		
		TRUNCATE TABLE crm_sms_record;
		TRUNCATE TABLE cfg_sms_send_rule;
		TRUNCATE TABLE cfg_sms_template;
		
	
		
		-- purchase
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		
		
		-- jit
		
		-- outside_wms
		/* TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		
		-- stock
		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		
		
		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		
		TRUNCATE TABLE stockin_order_detail;
		-- TRUNCATE TABLE stock_goods_batch;
		TRUNCATE TABLE stockin_order;
		
		TRUNCATE TABLE stock_logistics_no;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;
		TRUNCATE TABLE stock_change_record;
		-- oa
		
		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	-- 清除员工资料(清除：订单、库存、事务，保留货品(单品、组合装)、客户、供货商信息)
	IF P_CleanId = 4 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		
		-- sales_refund
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;
		
		
		-- purchase
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		
		
		
		-- jit
		
		-- outside_wms
/* 		TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		-- stock
		DELETE FROM cfg_warehouse_position WHERE rec_id > 0;
		DELETE FROM cfg_warehouse_zone WHERE zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position WHERE rec_id < 0);
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		
		
		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		
		TRUNCATE TABLE stockin_order_detail;
		-- TRUNCATE TABLE stock_goods_batch;
		TRUNCATE TABLE stockin_order;
		
		TRUNCATE TABLE stock_logistics_no;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;
		TRUNCATE TABLE stock_change_record;
		-- oa
		
		
		-- hr
		DELETE FROM cfg_employee_rights WHERE employee_id > 1;
		-- DELETE FROM cfg_employee_warehouse WHERE employee_id > 1;
		
		
		
		DELETE FROM hr_employee WHERE employee_id > 1;
		

		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	-- 清除订单、采购信息、库存调拨等相关库存订单信息(库存量由脚本重刷)
	IF P_CleanId = 5 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		

		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;
		
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		-- 清理事务
				
		-- 采购 盘点 调拨 生产 保修 包装
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;
		
		-- jit
		
		-- outside_wms
/* 		TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		-- stock
		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		TRUNCATE TABLE stockin_order_detail;
		TRUNCATE TABLE stockin_order;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_change_record;
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0,purchase_num=0,lock_num=0,
			to_purchase_num=0,purchase_arrive_num=0,refund_num=0,transfer_num=0,return_num=0,return_exch_num=0,
			return_onway_num=0,refund_onway_num=0,default_position_id=IF(default_position_id=0,-warehouse_id,default_position_id);
		INSERT INTO stock_spec_detail(stock_spec_id,spec_id,stockin_detail_id,position_id,position_no,zone_id,zone_no,cost_price,stock_num,virtual_num,created)
			SELECT ss.rec_id,ss.spec_id,0,ss.default_position_id,cwp.position_no,cwz.zone_id,cwz.zone_no,ss.cost_price,ss.stock_num,ss.stock_num,NOW()
			FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
			LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
		INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,created)
			SELECT ss.warehouse_id,ss.spec_id,ss.default_position_id,cwz.zone_id,ss.stock_num,NOW()
			FROM stock_spec ss LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=ss.default_position_id
			LEFT JOIN cfg_warehouse_zone cwz ON cwz.zone_id=cwp.zone_id;
		
		SET FOREIGN_KEY_CHECKS = 1;	
		
	END IF;
	-- 清除事务信息(包含消息)
	IF P_CleanId = 6 THEN
		
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	-- 清除客户营销信息
	IF P_CleanId = 7 THEN
		TRUNCATE TABLE cfg_sms_send_rule;
		TRUNCATE TABLE cfg_sms_template;
		TRUNCATE TABLE crm_sms_record;
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	
	-- 清除订单信息
	IF P_CleanId = 8 THEN
		DELETE FROM cfg_setting WHERE `key` LIKE 'order_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'refund_last_synctime_%';
		DELETE FROM cfg_setting WHERE `key` LIKE 'goods_sync_shop_%';
		
		-- api  删除原始订单和退换单
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		
		-- sales 删除原始订单和退换单
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;
		
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;
		
		-- 清理事务
		
		
		-- stockout  
		TRUNCATE TABLE stock_change_record;
		-- 销售出库单未在stock_change_history里插入数据的都可以删除，在stock_change_history里插入数据的将入库类型改为其他入库
		UPDATE stockout_order so,stockout_order_detail sod,stock_change_history sch 
			SET so.src_order_type=7,so.src_order_id=0,so.src_order_no='',sod.src_order_type=7 ,sod.src_order_detail_id=0,
			sch.src_order_type=7, sch.src_order_id=0,sch.src_order_no=''
			WHERE so.src_order_type=1 AND so.stockout_id=sod.stockout_id  
			AND so.stockout_id=sch.stockio_id AND sch.type=2;
			
		-- 将未出库的出库单关联的stock_goods_sn的状态由已占用（30）改为已入库（20）
		
			
		-- 删除未出库的出库单关联的stockout_detail_sn 必须先删  有外键

			
		-- 删除未出库的出库单关联的stockout_order_detail_position 必须先删  有外键
		DELETE sodp.* FROM stockout_order_detail_position sodp,stockout_order so,stockout_order_detail sod
			WHERE so.stockout_id=sod.stockout_id AND sod.rec_id=stockout_order_detail_id AND so.src_order_type=1 ;
				
		-- 删除未出库的出库单管理的stockout_pack_order,stockout_pack_order_detail 必须先删 有外键

			
		-- 删除未出库的出库单和出库单详情
		DELETE sod.* FROM stockout_order so,stockout_order_detail sod 
			WHERE so.stockout_id=sod.stockout_id AND so.src_order_type=1 ;
			
		DELETE so.* FROM stockout_order so WHERE so.src_order_type=1 ;
		-- 清空打印批次相关的数据

		       
		-- stockin
		-- 将退货入库的入库单改成其他入库
		UPDATE stockin_order so,stockin_order_detail sod,stock_change_history sch
			SET so.src_order_type=6,so.src_order_id=0,so.src_order_no='',sod.src_order_type=6,sod.src_order_detail_id=0,
			sch.src_order_type=6,sch.src_order_id=0,sch.src_order_no=''
			WHERE (so.src_order_type=3 or so.src_order_type=10) AND so.stockin_id=sod.stockin_id  AND so.stockin_id=sch.stockio_id
			AND sch.type=1;
			
		-- 退货入库的sn码状态是二次录入,将未入库的入库单关联的stock_goods_sn状态由二次录入（15）改成已入库（20）
			
		-- 删除未入库的入库单关联的stockin_detail_sn 必须先删  有外键
		
		-- 删除未入库的入库单和入库单详情
		DELETE sod.* FROM stockin_order so,stockin_order_detail sod
			WHERE (so.src_order_type=3 OR so.src_order_type=10) AND so.stockin_id=sod.stockin_id  ;
			
		DELETE so.* FROM stockin_order so WHERE (so.src_order_type=3 OR so.src_order_type=10) ;
		-- stock
		-- 将stock_spec中的未付款量，预订单量，待审核量，待发货量清0    销售退货量 销售换货在途量（发出和收回）这三个暂时没用 所以没有清0
		UPDATE stock_spec SET unpay_num=0,subscribe_num=0,order_num=0,sending_num=0;
		-- 将stock_spec_detail中的占用量清0
		UPDATE stock_spec_detail SET reserve_num=0,is_used_up=0;
		-- 删除日志表中有关订单操作的日志
		DELETE FROM stock_inout_log WHERE order_type=2 AND operate_type IN(1,2,3,4,7,14,23,24,51,52,62,63,111,113,120,121,300);
		-- stockout_order 中的字段consign_status,customer_id等没有用了，我想的是暂时先不清
		UPDATE cfg_setting SET `value`=0 WHERE `key`='cfg_stock_account_id';
		UPDATE cfg_setting SET `value`='0000-00-00' WHERE `key`='cfg_stock_account_date';
		
		SET FOREIGN_KEY_CHECKS = 1;
		
	END IF;
	-- 恢复初始化过程（店铺+仓库+物流+货品信息+组合装信息+货品条码+货品日志+订单相关+采购相关+售后相关+库存相关）
	IF P_CleanId = 9 THEN			
		-- api
		TRUNCATE TABLE api_refund_order;
		TRUNCATE TABLE api_refund;
		
		TRUNCATE TABLE api_trade_order;
		TRUNCATE TABLE api_trade_order_history;
		TRUNCATE TABLE api_trade;
		TRUNCATE TABLE api_trade_history;
		TRUNCATE TABLE api_trade_remark_history;
		
		TRUNCATE TABLE api_trade_discount;
		-- TRUNCATE TABLE api_trade_discount_history;
		TRUNCATE TABLE api_logistics_sync;
		TRUNCATE TABLE api_logistics_sync_history;
		-- TRUNCATE TABLE stock_logistics_sync;
		TRUNCATE TABLE api_stock_sync_record;
		TRUNCATE TABLE api_stock_sync_history;

		-- sales_refund
		TRUNCATE TABLE sales_refund_out_goods;
		TRUNCATE TABLE sales_refund_order;
		TRUNCATE TABLE sales_refund_log;
		TRUNCATE TABLE sales_refund;
		TRUNCATE TABLE sales_tmp_refund_order;
		
		-- crm
		TRUNCATE TABLE crm_customer_telno;
		TRUNCATE TABLE crm_customer_address;
		TRUNCATE TABLE crm_customer_log;
		TRUNCATE TABLE crm_platform_customer;
		TRUNCATE TABLE crm_customer;

		TRUNCATE TABLE crm_marketing_plan;
		TRUNCATE TABLE crm_marketing_result;

		TRUNCATE TABLE crm_sms_record;
		TRUNCATE TABLE cfg_sms_send_rule;
		TRUNCATE TABLE cfg_sms_template;
		
		-- purchase
		TRUNCATE TABLE purchase_provider_goods;
		TRUNCATE TABLE purchase_order_log;
		TRUNCATE TABLE purchase_order_detail;
		TRUNCATE TABLE purchase_order;
		TRUNCATE TABLE purchase_provider;
		
		
		-- outside_wms
		/* TRUNCATE TABLE `outside_wms_order_log`;
		TRUNCATE TABLE `outside_wms_order_detail`;
		TRUNCATE TABLE `outside_wms_order`; */
		
		-- jit
		
		
		-- goods
		TRUNCATE TABLE api_goods_spec;
		TRUNCATE TABLE goods_merchant_no;
		TRUNCATE TABLE goods_barcode;
		TRUNCATE TABLE goods_log;
		
		TRUNCATE TABLE goods_suite_detail;
		TRUNCATE TABLE goods_suite;
		
		
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		TRUNCATE TABLE goods_spec;
		TRUNCATE TABLE goods_goods;
		TRUNCATE TABLE cfg_gift_attend_goods;
		TRUNCATE TABLE cfg_gift_send_goods;
		TRUNCATE TABLE cfg_goods_warehouse;

		TRUNCATE TABLE goods_class;		
		TRUNCATE TABLE goods_brand;
		TRUNCATE TABLE cfg_goods_unit;     

		-- stock
		TRUNCATE TABLE cfg_warehouse_position;
		TRUNCATE TABLE cfg_warehouse_zone;
		TRUNCATE TABLE stock_spec_position;
		TRUNCATE TABLE stock_spec_detail;
		TRUNCATE TABLE stock_spec;
		TRUNCATE TABLE stock_spec_log;
		TRUNCATE TABLE stock_spec_log_history;

		TRUNCATE TABLE stockout_order_detail_position;
		TRUNCATE TABLE stockout_order_detail;
		TRUNCATE TABLE stockout_order;
		TRUNCATE TABLE stockout_order_history;
		TRUNCATE TABLE stockout_order_detail_history;
		
		TRUNCATE TABLE stockin_order_detail;
		-- TRUNCATE TABLE stock_goods_batch;
		TRUNCATE TABLE stockin_order;
		TRUNCATE TABLE stockin_adjust_order;
		TRUNCATE TABLE stockin_adjust_order_detail;

		TRUNCATE TABLE stock_logistics_no;
		TRUNCATE TABLE stock_logistics_no_history;
		TRUNCATE TABLE stock_inout_log;
		TRUNCATE TABLE stock_change_history;
		
		TRUNCATE TABLE stock_pd_detail;
		TRUNCATE TABLE stock_pd;
		TRUNCATE TABLE stock_transfer_detail;
		TRUNCATE TABLE stock_transfer;

		TRUNCATE TABLE cfg_stock_sync_rule;

		TRUNCATE TABLE stockout_print_batch;

		TRUNCATE TABLE stock_change_record;
		-- oa
		
		
		-- hr		
		TRUNCATE TABLE cfg_employee_rights;
		-- TRUNCATE TABLE hr_employee;	
		DELETE FROM hr_employee WHERE employee_id > 1;		

		-- sales
		TRUNCATE TABLE sales_trade_log;
		TRUNCATE TABLE sales_trade_log_history;
		TRUNCATE TABLE sales_trade_order;
		TRUNCATE TABLE sales_trade_order_history;
		TRUNCATE TABLE sales_trade;
		TRUNCATE TABLE sales_trade_history;
		TRUNCATE TABLE sales_gift_record;
		TRUNCATE TABLE sales_trade_trace;

		-- stalls
		TRUNCATE TABLE stat_stalls_goods_amount;
		TRUNCATE TABLE purchase_provider_group;
		TRUNCATE TABLE stalls_less_goods_detail;
		TRUNCATE TABLE stalls_less_goods_detail_history;
		TRUNCATE TABLE stalls_order_log;		
		TRUNCATE TABLE stalls_order;
		TRUNCATE TABLE stalls_order_history;
		TRUNCATE TABLE sorting_wall_detail;
		TRUNCATE TABLE cfg_sorting_wall;
		TRUNCATE TABLE box_goods_detail;
		TRUNCATE TABLE operator_stalls_pickup_log;
		TRUNCATE TABLE operator_stalls_pickup_log_history;
		TRUNCATE TABLE alipay_account_bill_detail;


		-- shop
		TRUNCATE TABLE cfg_shop;		
		TRUNCATE TABLE cfg_shop_warehouse;

		-- warehouse		
		TRUNCATE TABLE cfg_warehouse;
		TRUNCATE TABLE cfg_warehouse_area;

		-- logistics
		TRUNCATE TABLE cfg_logistics;
		TRUNCATE TABLE cfg_logistics_fee;
		TRUNCATE TABLE cfg_logistics_match;
		TRUNCATE TABLE cfg_logistics_area_alias;
		TRUNCATE TABLE api_logistics_shop;
		TRUNCATE TABLE cfg_logistics_shop;
		TRUNCATE TABLE sales_multi_logistics;

		-- cfg_oper_reason
		TRUNCATE TABLE cfg_oper_reason;
		-- flag
		TRUNCATE TABLE cfg_flags;
		-- setting
		TRUNCATE TABLE cfg_setting;        
        -- gift
        TRUNCATE TABLE cfg_gift_rule;
        TRUNCATE TABLE cfg_gift_attend_goods;
        TRUNCATE TABLE cfg_gift_send_goods;
        TRUNCATE TABLE sales_gift_record;

        -- remark
        TRUNCATE TABLE cfg_trade_remark_extract;

        -- 模板
        TRUNCATE TABLE cfg_print_template;
        TRUNCATE TABLE cfg_sms_template;

        TRUNCATE TABLE cfg_user_data;

        -- sys
        TRUNCATE TABLE sys_other_log;
        TRUNCATE TABLE sys_process_background;
        TRUNCATE TABLE sys_sequence;
        TRUNCATE TABLE sys_notification;
        TRUNCATE TABLE sys_lock;

        TRUNCATE TABLE stat_use;

        -- 初始化插入
  -- 		 INSERT INTO `hr_employee` (`employee_id`, `password`, `salt`, `algo`, `field_mask`, `field_rights`, `group_field_rights`, `account`, `fullname`, `gender`, `is_regular`, `roles_mask`, `created`) VALUES 
		-- ('0', RAND(), 0, 0, 0, 0, 0, '系统', '系统', 0, 0, 0, NOW()),
		-- ('1', 'ca098cccf935dbfdf0f7dffaebbacbb5', 'a2be999102b50c95a788a5118289e065', 1, 242719, 242719, 111647, 'admin', '管理员', 1, 1, 2, NOW());
		-- update `hr_employee` set employee_id = 0 WHERE fullname='系统';
		
		INSERT INTO `cfg_setting` (`key`, `value`, `class`, `name`, `log_type`, `value_type`, `value_desc`, `modify_mode`, `modified`)
		VALUES ('cfg_statsales_date', '0',  'system', '', '5', '0', '', '0', NOW()),
			('cfg_statsales_date_time', '0', 'system', '', '5', '0', '', '0', NOW()),
			('cfg_statsales_per_spec_date', '0', 'system', '', '5', '0', '', '0', NOW()),
			('goods_match_split_char', '~', 'system', '', '5', '2', '', '0', NOW()),
			('login_check_code', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_allow_man_create_cod', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_auto_download', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_auto_submit', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_deliver_auto_merge', '1', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_warn_has_unmerge', '1', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_warn_has_unmerge_address', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_warn_has_unmerge_checked', '1', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_warn_has_unmerge_freeze', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_warn_has_unpay', '1', 'system', '', '5', '2', '', '0', NOW()),
			('sales_trade_auto_merge_gift', '1', 'system', '', '5', '2', '', '0', NOW()),
			('sales_raw_count_exclued_gift', '0', 'system', '', '5', '2', '', '0', NOW()),
			('sales_trade_split_num', '0', 'system', '', '5', '2', '', '0', NOW()),
			('sales_trade_trace_enable', '1', 'system', '', '5', '2', '', '0', NOW()),
			('sales_trade_trace_operator', '0', 'system', '', '5', '2', '', '0', NOW()),
			('show_number_to_star', '1', 'system', '', '5', '2', '', '0', NOW()),
			('stockout_examine_goods', '1', 'system', '', '5', '2', '', '0', NOW()),
			('sys_goods_match_concat_code', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_sync_interval', '10', 'system', '', '5', '2', '', '0', NOW()),
			('order_check_no_stock', '0', 'system', '', '5', '2', '', '0', NOW()),
			('order_allow_part_sync', '0', 'system', '拆单发货', '5', '2', '', '0', NOW()),
			('order_logistics_sync_time', '2', 'system', '物流同步条件', '5', '1', '01-1:0006-任一个子订单,01-0:0007-主子订单发货时,01-2:0007-全部子订单发货', '0', NOW()),
			('sys_version', '1.0.1.0', 'system', '系统版本', '5', '5', '', '0', NOW()),
			('calc_logistics_by_weight', '1', 'system', '', '5', '0', '', '0', NOW()),
			('apigoods_auto_match', '1', 'system', '', '5', '0', '', '0', NOW()),
			('order_deliver_c_remark_extract', '1', 'system', '递交是否启用客户备注提取', '5', '0', '', '0', NOW()),
			('order_deliver_remark_extract', '1', 'system', '递交是否启用客服备注提取', '5', '0', '', '0', NOW()),
			('salesman_macro_begin', '', 'system', '业务员提取前括号', '5', '0', '', '0', NOW()),
			('salesman_macro_end', '', 'system', '业务员提取后括号', '5', '0', '', '0', NOW()),
			('order_preorder_lack_stock', '0', 'system', '', '5', '2', '', '0', NOW()),
			('auto_check_is_open', '0', 'system', '开启自动审核', '5', '2', '', '0', NOW()),
			('auto_check_buyer_message', '0', 'system', '自动审核-无客户备注', '5', '2', '', '0', NOW()),
			('auto_check_csremark', '0', 'system', '自动审核-无客服备注', '5', '2', '', '0', NOW()),
			('auto_check_no_invoice', '0', 'system', '自动审核-无发票', '5', '2', '', '0', NOW()),
			('auto_check_no_adr', '0', 'system', '自动审核-收货地址无（村、组）', '5', '2', '', '0', NOW()),
			('auto_check_start_time', '2016-01-01 00:00:00', 'system', '自动审核-开始时间', '5', '0', '', '0', NOW()),
			('auto_check_end_time', '2016-01-01 00:00:00', 'system', '自动审核-结束时间', '5', '0', '', '0', NOW()),
			('stock_auto_submit_time', '10', 'system', '', '5', '2', '', '0', NOW()),
			('stock_scan_once', '1', 'system', '', '5', '2', '', '0', NOW()),
			('sys_available_stock', '0', 'system', '可发库存具体显示配置', '5', '2', '640:2^7-(-order_num),2^9-(-sending_num)=2^7|2^9=-order_num-sending_num', '0', NOW()),
			('sales_print_time_range', '7', 'system', '单据打印显示时间段', '5', '2', '', '0', NOW()),
			('cfg_stat_salesman_performance', '0', 'system', '业务员绩效上次统计时间', '5', '0', '', '0', NOW()),
			('order_check_force_check_pwd_is_open', '0', 'system', '强制审核是否校验密码', '5', '2', '', '0', NOW()),
			('order_check_force_check_pwd', '0', 'system', '强制审核密码', '5', '4', '', '0', NOW()),
			('auto_check_max_weight', '0', 'system', '自动审核-最大重量', '5', '0', '', '0', NOW()),
			('auto_check_under_weight', '0', 'system', '自动审核-限重', '5', '2', '', '0', NOW()),
			('order_deliver_auto_merge_ban_refund', '0', 'system', '禁止申请退款单自动合并', '5', '2', '', '0', NOW()),
			('order_auto_downloadjdsellback','0','system','开启订单自动下载京东售后单','5','2','','0',NOW()),
			('cfg_open_message_strategy', '0', 'system', '启用短信发送策略', '5', '0', '', '0', NOW()),
			('crm_member_send_sms_limit_time', '0', 'system', '同一客户短信发送时间间隔', '5', '0', '', '0', NOW()),
			('order_cal_weight_by_suite', '0', 'system', '订单包含组合装时按照组合装重量计算订单重量', '5', '2', '', '0', NOW()),
			('auto_check_time_type', '0', 'system', '自动审核-限制时间类型', '5', '2', '', '0', NOW()),
			('refund_should_deliver', '0', 'system', '自动递交类型为退款的原始退款单', '5', '2', '', '0', NOW()),
			('order_check_black_customer', '0', 'system', '阻止黑名单客户通过审核', '5', '2', '', '0', NOW()),
			('sys_init', '1', 'system', '实施助手是否开启', '5', '2', '', '0', NOW()),
			('sales_trade_warehouse_bygoods', '0', 'system', '按货品选仓', '5', '2', '', '0',  NOW()),
			('return_agree_auto_remark', '换货成功', 'system', '同意换货单后自动同步的备注信息', '5', '0', '', '0', NOW()),
			('return_agree_auto_sync_remark', '0', 'system', '同意换货单后自动同步备注到线上订单', '5', '2', '', '0', NOW()),
			('order_auto_merge_mode', '0', 'system', '订单自动合并方式', '5', '2', '', '0', NOW()),
			('order_limit_real_price','0','system','限制手工建单商品折后价的值','5','2','','0',NOW()),
			('real_price_limit_value','0','system','手工建单商品折后价的限制值','5','0','0:最低价,1:零售价,2:市场价','0',NOW()),
			('order_deliver_auto_split_by_warehouse', '0', 'system', '按货品指定仓库自动拆分', '5', '2', '', '0', NOW()),
		    ('order_warehouse_split_check_gift', '1', 'system', '不允许按指定仓库拆分出全是赠品的订单', '5', '2', '', '0', NOW()),
			('alarm_not_prompt_today', '0', 'system', '当天不再弹窗提示余额不足等', '5', '0', '', '0', NOW()),
			('sms_num_alarm', '0', 'system', '短信余额不足提醒', '5', '2', '', '0', NOW()),
			('sms_num_alarm_num', '0', 'system', '短信余额警戒值', '5', '2', '', '0', NOW()),
			('waybill_num_alarm', '0', 'system', '电子面单余额不足提醒', '5', '2', '', '0', NOW()),
			('waybill_num_alarm_num', '0', 'system', '电子面单余额警戒值', '5', '2', '', '0', NOW()),
			('cfg_login_interval', '30', 'system', '用户登录时间间隔', '5', '2', '', '0', NOW()),
			('logistics_disabled_search', '0', 'system', '停用的物流是否显示在搜索中', '5', '2', '', '0', NOW()),
			('reason_disabled_search', '0', 'system', '停用的原因列表是否显示在搜索中', '5', '2', '', '0', NOW()),
			('shop_disabled_search', '0', 'system', '停用的店铺是否显示在搜索中', '5', '2', '', '0', NOW()),
			('warehouse_disabled_search', '0', 'system', '停用的仓库是否显示在搜索中', '5', '2', '', '0', NOW()),
			('order_check_address_reachable', '0', 'system', '订单审核校验物流是否可达', '5', '2', '', '0', NOW()),
			('refund_auto_agree', '0', 'system', '自动同意退货单', '5', '2', '', '0', NOW()),
			('order_fc_below_costprice','0','system','商品成交金额低于成本价时进财审','5','2','','0',NOW()),
			('last_stock_sync_file_time', '0', 'system', '上次库存同步日志归档时间', '5', '0', '', '0', NOW()),
			('last_api_logistics_sync_file_time', '0', 'system', '上次物流同步归档时间', '5', '0', '', '0', NOW()),
			('last_api_trade_file_time', '0', 'system', '上次原始订单归档时间', '5', '0', '', '0', NOW()),
			('last_stockspec_log', '0', 'system', '上次库存日志归档时间', '5', '0', '', '0', NOW()),
			('last_stocklogistics_no', '0', 'system', '上次物流单号归档时间', '5', '0', '', '0', NOW()),
			('last_stockout', '0', 'system', '上次出库单归档时间', '5', '0', '', '0', NOW()),
			('last_sales_trade_file_time', '0', 'system', '上次订单归档时间', '5', '0', '', '0', NOW());


		INSERT INTO `goods_class` (`class_id`, `parent_id`, `is_leaf`, `class_name`, `path`, `modified`, `created`) VALUES (0, -1, 1, '无', '-1,0', NOW(), NOW());
		update goods_class set class_id = 0 where parent_id = -1;

		INSERT INTO `goods_brand` (`brand_id`,`brand_name`,`remark`,`sales_rate_type`,`sales_rate_cycle`,`sales_rate`,`alarm_type`,`alarm_days`,`is_disabled`,`modified`,`created`) VALUES(0,'无','30','2','2','1.000','30','0','0',NOW(),NOW());
        update `goods_brand` set brand_id = 0 WHERE brand_name='无';

        INSERT INTO `cfg_print_template` (`type`, `title`, `content`, `logistics_list`, `shop_ids`, `warehouse_list`, `is_disabled`, `is_default`, `modified`, `created`)VALUES (0,'京邦达打印模板','{\"formatter_table_info\":{\"goods_name\":{\"is_display\":1,\"width\":90},\"spec_name\":{\"is_display\":1,\"width\":90},\"num\":{\"is_display\":1,\"width\":90},\"price\":{\"is_display\":0,\"width\":90},\"spec_no\":{\"is_display\":0,\"width\":90},\"goods_no\":{\"is_display\":0,\"width\":90},\"spec_code\":{\"is_display\":0,\"width\":90}},\"print\":\"@w0Luru1dt1vovf0ncLzatfvfptq1dqOncLTquK5ut1aDdqPjvevnugfUzwXqufrmptbncKLuru0XptG3dqPjvevnmJ0XmdencKLuru0ZpteXnG0ksvrfttq9mtbXdqPjvevnnt0XmdbncKLuru02pte4nW0ksvrfttC9mtyXdqPjvevnod0XnZuncKLuru05pte3nq0ksvrftteWpte3mW0ksvrftteXptm0mq0ksvrftteYptm1nq0ksvrftteZptm1nq0ksvrftte0ptm2nW0ksvrftte1ptm1nq0ksvrftte2pti2nW0ksvrftte3pti4mW0ksvrftte4pti4mW0ksvrftte5pti4mG0ksvrfttiWpti5ob0ksvrfttiXptG2dqPjvevnmJi9mtGZdqPjvevnmJm9mJC3dqPjvevnmJq9mJq3dqPjvevnmJu9mJq2dqPjvevnmJy9mtm2dqPjvevnmJC9mJy2dqPjvevnmJG9mtbXdqPjvevnmJK9mtbXdqPjvevnmZb9mJK4dqPjvevnmZe9mJK4dqPjvevnmZi9mZuWdqPjvevnmZm9mtKncKLuru0Znd0XmdincKLuru0Znt0YotGncKLuru0ZnJ0YmJqncKLuru0ZnZ00mdyncKLuru0Zod0XmdbncKLuru0Zot0YodincKLuru00md0ZntuncKLuru00mt0XnZuncKLuru00mJ0XodCncKLuru00mZ0ZnJGncKLuru00nd0XmtuncKLuru00nt0YotGncG0kw1astKXfrLrDdqPjvevnugfUzwXqufrmptbncKLuru0Xpte5dqPjvevnmJ0Xoq0ksvrfttm9mtKncKLuru00ptu0dqPjvevnnt0XndmncKLuru02pteYdqPjvevnnZ0XmW0ksvrfttG9mtm0dqPjvevnot0XmG0ksvrftteWptu1dqPjvevnmte9mtmncKLuru0XmJ0XmG0ksvrftteZptuYdqPjvevnmtq9mtincKLuru0Xnt0XmZqncKLuru0XnJ0XmW0ksvrftte3pteZdqPjvevnmtG9ndGncKLuru0Xot0XmZCncKLuru0Ymd0XmW0ksvrfttiXpti2mW0ksvrfttiYpti2nq0ksvrfttiZpti2nb0ksvrftti0pteXmq0ksvrftti1pte1nG0ksvrftti2pti2nb0ksvrftti3ptCYdqPjvevnmJG9mJyWdqPjvevnmJK9mZm3dqPjvevnmZb9mJu4dqPjvevnmZe9mZm1dqPjvevnmZi9mJu4dqPjvevnmZm9mZGncKLuru0Znd0YodqncKLuru0Znt0YnZqncKLuru0ZnJ0YmJmncKLuru0ZnZ0YmJmncKLuru0Zod0XnZKncKLuru0Zot0XnZincKLuru00md0XnJuncKLuru00mt0XnJKncKLuru00mJ00nG0ksvrfttqZptq3dqPjvevnndq9ntqncKLuru00nt00nW0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptm3nq0ksvrftte9nZencKLuru0YptuWdqPjvevnmZ00nb0ksvrfttq9nJyncKLuru01ptq4dqPjvevnnJ00ob0ksvrfttC9nZencKLuru04ptq4dqPjvevnot00ob0ksvrftteWpty2dqPjvevnmte9nZencKLuru0XmJ00ob0ksvrftteZpty2dqPjvevnmtq9ndGncKLuru0Xnt00ob0ksvrftte2ptC1dqPjvevnmtC9ntbncKLuru0Xod02nG0ksvrftte5ptq4dqPjvevnmJb9ndqncKLuru0Ymt03mq0ksvrfttiYptCXdqPjvevnmJm9nZencKLuru0Ynd03nq0ksvrftti1pte5ob0ksvrftti2pteWmb0ksvrftti3pteWmb0ksvrftti4ptiWdqPjvevnmJK9mJbncKLuru0Zmd0Ymb0ksvrfttmXptiWdqPjvevnmZi9mteYdqPjvevnmZm9mZu1dqPjvevnmZq9nZincKLuru0Znt03mb0ksvrfttm2ptyWdqPjvevnmZC9nJbncKLuru0Zod03mb0ksvrfttm5ptCWdqPjvevnndb9nZbncKLuru00mt03mb0ksvrfttqYptiWnb0ksvrfttqZptiWnb0ksvrfttq0pte5nq0ksvrfttq1pte5nq0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd00mJCncKLuru0XpteXdqPjvevnmJ0Xmq0ksvrfttm9mtencKLuru00pteXdqPjvevnnt0Xmq0ksvrftty9mtencKLuru03pteXdqPjvevnod0Xmq0ksvrfttK9mtencKLuru0Xmd0Xmq0ksvrftteXpteXdqPjvevnmti9mtencKLuru0XmZ0Xmq0ksvrftte0pteXdqPjvevnmtu9mtencKLuru0XnJ0Xmq0ksvrftte3pteXdqPjvevnmtG9mtencKLuru0Xot0Xmq0ksvrfttiWpteXdqPjvevnmJe9mtencKLuru0YmJ0Xmq0ksvrfttiZpteXdqPjvevnmJq9mtencKLuru0Ynt0XmW0ksvrftti2ptmYdqPjvevnmJC9mtmncKLuru0Yod0Ymb0ksvrftti5ptiZdqPjvevnmZb9mJbncKLuru0Zmt0YmW0ksvrfttmYptq4dqPjvevnmZm9ntqncKLuru0Znd0Ymb0ksvrfttm1ptiWdqPjvevnmZy9oq0ksvrfttm3ptKncKLuru0Zod0Xmq0ksvrfttm5pteXdqPjvevnndb9mtencKLuru00mt0Xmq0ksvrfttqYptm0dqPjvevnndm9mZqncKLuru00nd0ZnG0ksvrfttq1ptm2dqOncLTquK5iB3j6t3jUDf0ncKLuru0ZmJ0ZdqOncLTquK5wzxj0t3jUDf0ncKLuru0ZmJ0ZdqOncLTquK5gt05uu0LArv0ncKLuru0XptGncKLuru0YptGncKLuru0ZptGncKLuru00ptGncKLuru01ptGncKLuru02ptGncKLuru03ptGncKLuru04ptGncKLuru05ptGncKLuru0Xmd04dqPjvevnmte9ob0ksvrftteYptGncKLuru0XmZ04dqPjvevnmtq9ob0ksvrftte1ptGncKLuru0XnJ04dqPjvevnmtC9ob0ksvrftte4ptGncKLuru0Xot04dqPjvevnmJb9ob0ksvrfttiXptGncKLuru0YmJ04dqPjvevnmJm9ob0ksvrftti0ptGncKLuru0YnJ0Ynb0ksvrftti3ptGncKLuru0Yod0Xnb0ksvrftti5pte0dqPjvevnmZb9mtqncKLuru0Zmt0Xnb0ksvrfttmYptyncKLuru0Znd0XmW0ksvrfttm1pteZdqPjvevnmZy9nG0ksvrfttm3ptyncG0kw1astKzptLroqu1fxq0ksvrftte9UTRm5q0ksvrftti9UTRm5q0ksvrfttm9UTRm5q0ksvrfttq9UTRm5q0ksvrfttu9UTRm5q0ksvrftty9UTRm5q0ksvrfttC9UTRm5q0ksvrfttG9UTRm5q0ksvrfttK9UTRm5q0ksvrftteWpBRAZouncKLuru0Xmt262SZLdqPjvevnmti9UTRm5q0ksvrftteZpBRAZouncKLuru0Xnd262SZLdqPjvevnmtu9UTRm5q0ksvrftte2pBRAZouncKLuru0XnZ262SZLdqPjvevnmtG9UTRm5q0ksvrftte5pBRAZouncKLuru0Ymd262SZLdqPjvevnmJe9UTRm5q0ksvrfttiYpBRAZouncKLuru0YmZ262SZLdqPjvevnmJq9UTRm5q0ksvrftti1pBRAZouncKLuru0YnJ262SZLdqPjvevnmJC9UTRm5q0ksvrftti4pBRAZouncKLuru0Yot262SZLdqPjvevnmZb9UTRm5q0ksvrfttmXpBRAZouncKLuru0ZmJ0XmJHadqPjvevnmZm9mti4qq0ksvrfttm0pBRAZouncKLuru0Znt262SZLdqPjvevnmZy9UTRm5q0ksvrfttm3pBRAZouncKLuru0Zod262SZLdqPjvevnmZK9UTRm5q0ksvrfttqWpBRAZouncKLuru00mt262SZLdqOncLTdteftu0Lorevyxq0ksvrftte9mG0ksvrftti9mG0ksvrfttm9mG0ksvrfttq9mG0ksvrfttu9mG0ksvrftty9mG0ksvrfttC9mG0ksvrfttG9mG0ksvrfttK9mG0ksvrftteWptincKLuru0Xmt0YdqPjvevnmti9mG0ksvrftteZptincKLuru0Xnd0YdqPjvevnmtu9mG0ksvrftte2ptincKLuru0XnZ0YdqPjvevnmtG9mG0ksvrftte5ptincKLuru0Ymd0YdqPjvevnmJe9mG0ksvrfttiYptincKLuru0YmZ0YdqPjvevnmJq9mG0ksvrftti1ptincKLuru0YnJ0YdqPjvevnmJC9mG0ksvrftti4ptincKLuru0Yot0YdqPjvevnmZb9mG0ksvrfttmXptincKLuru0ZmJ05dqPjvevnmZm9oq0ksvrfttm0ptincKLuru0Znt0YdqPjvevnmZy9mG0ksvrfttm3ptincKLuru0Zod0YdqPjvevnmZK9mG0ksvrfttqWptincKLuru00mt0YdqPjvevnndi9mG0ksvrfttqZptincKLuru00nd0YdqPjvevnndu9mG0kdqPBq29UDgvUDf0ncKLuru0XpxL0vZn2zergEJzjpq0ksvrftti9me5yrcS2tZyncKLuru0ZpxrKALD0nK82dqPjvevnnd0WtLHek3C9pq0ksvrfttu9DgvLn3nltZyncKLuru02pxrKALD0nK82dqPjvevnnZ12tvmZDMrerNO2st0ncKLuru04pxrLztDZs082dqPjvevnot0WtLHekZzpnG0ksvrftteWptaoweqRDZ09dqPjvevnmte9DK1tm3zKrez6nKK9dqPjvevnmti9me5yrcS2tZyncKLuru0XmZ0WtLHek3C9pq0ksvrftte0pxrKALD0nK82dqPjvevnmtu9DgvLn3nltZyncKLuru0XnJ10Cw0XCgjYrM83BZ0ncKLuru0XnZ0WtLHekZzpnG0ksvrftte4ptaoweqRDZ09dqPjvevnmtK9DgvLn3nltZyncKLuru0Ymd10zgPxDdzpnG0ksvrfttiXpxrqCKSXyJn3Dhu0pq0ksvrfttiYpxy4mJDWogvWDY9Zpq0ksvrfttiZpxrqCKSXyJn3Dhu0pq0ksvrftti0ptfnDtfWyNjgBZDVpq0ksvrftti1pxP1l0i5n1DSDxnvpq0ksvrftti2pu1tohGncKLuru0YnZ0XsZnlDKXxBhvZvt0ncKLuru0Yod1VnLe9dqPjvevnmJK9muTVpq0ksvrfttmWpw82ut0ncKLuru0Zmt0Xs289dqPjvevnmZi9t0rND01uqtnoEMSWturfEu56sxLnELu1dqPjvevnmZm9turaD01eqxDnvgT5t1rRD0XurxrnuZb9dqPjvevnmZq9tvrfEeXQqxOncKLuru0Znt1nvev4tgPaEq0ksvrfttm2pxrqtfrVy2PwEhrVpq0ksvrfttm3puTmAtbXC1KWtNLTmdH0t2H5tLHhmMC9pq0ksvrfttm4pxrLztDZqt09dqPjvevnmZK9s0XPmdfZwtfnu20XntD1DW0ksvrfttqWpxrLztDZqt09dqPjvevnnde9s0XPmdfZwtaoEw0XntD1DW0ksvrfttqYpxORCLa1tfHzmxjJpq0ksvrfttqZpuTmAtbXC1KWtNLUudzZkZr0zgPxDhC9pq0ksvrfttq0pxORCLa1tfHzmxjJpq0ksvrfttq1puTmAtbXC1KWt1nUudzZkZr0zgPxDhC9pq0kdqPBsxrLBu5HBwvDdqPjvevnnd1YzwnLAxzLCL9Uyw1LdqPjvevnmtb9y29UDgfJDb0ksvrftteZpwnVBNrHy3qncKLuru0Xod1YzwnLAxzLCL9Uyw1LdqPjvevnmJu9Bg9NAxn0AwnZx25VdqPjvevnmJy9CgfJA2fNzv9Zzxf1zw5Jzq0ksvrftti3pxnYy190AwrZdqPjvevnmZi9Bg9NAxn0AwnZx25VdqPjvevnmZm9CgfJA2fNzv9UB19JB2rLdqPjvevnmZq9CMvJzwL2ywjSzq0ksvrfttm1pxjLy2vPDMfIBguncKLuru0ZnJ1WCMLUDf9KyxrLdqPjvevnmZC9ChjPBNrFzgf0zq0ksvrfttm4pxjLy2vPDMvYx3aOB25LdqPjvevnmZK9CMvJzwL2zxjFCgHVBMuncKLuru00md1WAg9Uzq0ksvrfttqXpxaOB25LdqPjvevnndi9ywrKCMvZC19KzxrHAwWncKLuru00mZ1HzgrYzxnZx2rLDgfPBb0ksvrfttq0pxjLy2vPDMvYx2fKzhjLC3nFzgv0ywLSdqPjvevnndu9CMvJzwL2zxjFywrKCMvZC19KzxrHAwWncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde2ltb4ltmWlte1ltu3dqPBsvrftuvorf0ncG==\\r\\n\",\"width\":\"375\",\"height\":\"427\",\"printor_float_position\":{\"total\":0,\"rows\":[]},\"font_info\":{\"font_family\":\"宋体\",\"font_size\":\"9\",\"title_show\":\"1\",\"line_show\":\"1\",\"number_show\":\"1\"},\"item_to_position\":{\"receiver_name\":[4,18],\"contact\":[10,13],\"logistics_no\":[25,32],\"package_sequence\":[26],\"src_tids\":[27],\"package_no_code\":[33],\"receivable\":[34,35],\"print_date\":[36,37],\"receiver_phone\":[38,39],\"phone\":[40,41],\"address_detail\":[42,43],\"receiver_address_detail\":[44,45]},\"total_item\":45}','','','',0,0,NOW(),NOW());
		INSERT INTO `cfg_print_template` ( `type`, `title`, `content`, `logistics_list`, `shop_ids`, `warehouse_list`, `is_disabled`, `is_default`, `modified`, `created`) VALUES('0','申通快递普通','{\"print\":\"@w0Luru1dt1vovf0ncLzatfvfpte4dqOncLTquK5ut1aDdqPjvevnugfUzwXqufrmptbncKLuru0XptCXdqPjvevnmJ0XodKncKLuru0Zpte0nq0ksvrfttq9mZy4dqPjvevnnt0XmdencKLuru02pteWmG0ksvrfttC9mZq1dqPjvevnod03nb0ksvrfttK9mtG4dqPjvevnmtb9mJC2dqPjvevnmte9mJC2dqPjvevnmti9mJC2dqPjvevnmtm9mtm1dqPjvevnmtq9mJmXdqPjvevnmtu9mZi3dqPjvevnmty9mZi2dqPjvevnmtC9mZq3dqPjvevnmtG9nZencG0kw1astKXfrLrDdqPjvevnugfUzwXqufrmptbncKLuru0XptuWdqPjvevnmJ02nb0ksvrfttm9mW0ksvrfttq9nq0ksvrfttu9ndCncKLuru02ptqWnW0ksvrfttC9otKncKLuru04ptqWnq0ksvrfttK9ndu4dqPjvevnmtb9mJG0dqPjvevnmte9ndiZdqPjvevnmti9ntyYdqPjvevnmtm9mZu5dqPjvevnmtq9mJGncKLuru0Xnt0XmdCncKLuru0XnJ0XdqPjvevnmtC9mb0ksvrftte4ptiXnW0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptG2oq0ksvrftte9mtbWdqPjvevnmJ0XmtmncKLuru0ZptmZmW0ksvrfttq9mtbWdqPjvevnnt0XmdbncKLuru02pteWmb0ksvrfttC9mZyYdqPjvevnod0XmdbncKLuru05pteYmG0ksvrftteWpte2nb0ksvrftteXpte0oq0ksvrftteYpte0oq0ksvrftteZptmYnG0ksvrftte0pti5mb0ksvrftte1ptm1nW0ksvrftte2pteWmb0ksvrftte3pteWmb0ksvrftte4pteWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd00odbncKLuru0Xpti1dqPjvevnmJ0Ymb0ksvrfttm9mZGncKLuru00ptiWdqPjvevnnt0Ymb0ksvrftty9mJbncKLuru03ptiWdqPjvevnod0Ymb0ksvrfttK9mJbncKLuru0Xmd00nG0ksvrftteXptq2dqPjvevnmti9ndyncKLuru0XmZ00nG0ksvrftte0ptGWdqPjvevnmtu9mJbncKLuru0XnJ0Ymb0ksvrftte3ptiWdqPjvevnmtG9mJbncG0kw1astKzptLrtsvPfxq0ksvrftte9mtencKLuru0ZpteXdqPjvevnnd0XmG0ksvrfttu9mtencKLuru02pteXdqPjvevnnZ0XmG0ksvrfttG9mtencKLuru05pteYdqPjvevnmtb9mJmncKLuru0Xmt0YmG0ksvrftteYptiYdqPjvevnmtm9mtincKLuru0Xnt0XmG0ksvrftte2pteYdqPjvevnmtC9mtincG0kw1astKzptLroqu1fxq0ksvrftte9T8llZG0ksvrfttm9T8llZG0ksvrfttu9T8llZG0ksvrftty9T8llZG0ksvrfttG9T8llZG0ksvrftteZpBFcY84ncG0kw1astKjpterDdqPjvevnmt0XdqPjvevnmJ0XdqPjvevnmZ0XdqPjvevnnd0XdqPjvevnnt0XdqPjvevnnJ0XdqPjvevnod0XdqPjvevnot0XdqPjvevnmtb9mq0ksvrftteXptencKLuru0XmJ0XdqPjvevnmtm9mq0ksvrftte2ptencKLuru0XnZ0XdqOncLTdteftu0Lorevyxq0ksvrftte9mG0ksvrftti9mG0ksvrfttm9mG0ksvrfttq9mG0ksvrfttu9mG0ksvrftty9mG0ksvrfttC9mG0ksvrfttG9mG0ksvrfttK9mG0ksvrftteWptincKLuru0Xmt0YdqPjvevnmti9mG0ksvrftteZptincKLuru0Xnd00dqPjvevnmtu9mG0ksvrftte2ptincKLuru0XnZ0YdqPjvevnmtG9mG0kdqPBq29UDgvUDf0ncKLuru0Xpxq2sZGVC2Pmme5yrcT3pt0ncKLuru0Ypxq2sZGVC2PmExrHnYTNpt0ncKLuru0Zpxq2sZGVC2PmEITYuhvmwfKXCMm9dqPjvevnnd10nKS4l3nQtdaoweqRDZ09dqPjvevnnt10zxjhEK1qn3m4wt0ncKLuru02pxD2sZGWCZm0DY9Zpq0ksvrfttC9D3zlodaZsdbWzfe9dqPjvevnod15DfC4l3nQtdaoweqRDZ09dqPjvevnot15DfC4l3nQthL0ytCRzZ09dqPjvevnmtb9Exrxoc9ZAKX5CuCZm1e9pq0ksvrftteXpxL0vZGVC2Pm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\\r\\n\",\"width\":\"869\",\"height\":\"480\",\"printor_float_position\":{\"total\":0,\"rows\":[]},\"background_url\":\"\",\"font_info\":{\"font_family\":\"宋体\",\"font_size\":\"10\",\"title_show\":\"0\",\"line_show\":\"1\",\"number_show\":\"1\"},\"item_to_position\":{\"contact\":[1,4],\"mobile\":[2],\"address_detail\":[3],\"shop_name\":[5],\"buyer_nick\":[6],\"buyer_message\":[7],\"receiver_name\":[8],\"receiver_mobile\":[9],\"receiver_province\":[10],\"receiver_city\":[11],\"receiver_district\":[12],\"receiver_address_detail\":[13],\"goods_info_table\":[14],\"cs_remark\":[15],\"print_date\":[18]},\"total_item\":18,\"formatter_table_info\":{\"goods_name\":{\"is_display\":\"0\",\"width\":\"100\"},\"short_name\":{\"is_display\":0,\"width\":90},\"spec_name\":{\"is_display\":\"1\",\"width\":\"150\"},\"num\":{\"is_display\":1,\"width\":\"40\"},\"price\":{\"is_display\":0,\"width\":80},\"spec_no\":{\"is_display\":0,\"width\":80},\"goods_no\":{\"is_display\":0,\"width\":80},\"spec_code\":{\"is_display\":0,\"width\":80},\"position_no\":{\"is_display\":0,\"width\":90}},\"cn_setting\":{\"logo_show\":\"1\"}}','','','','0','0',NOW(),NOW());
		INSERT INTO `cfg_print_template` ( `type`, `title`, `content`, `logistics_list`, `shop_ids`, `warehouse_list`, `is_disabled`, `is_default`, `modified`, `created`) VALUES('0','圆通快递普通','{\"formatter_table_info\":{\"goods_name\":{\"is_display\":\"0\",\"width\":200},\"spec_name\":{\"is_display\":\"1\",\"width\":200},\"num\":{\"is_display\":1,\"width\":80},\"price\":{\"is_display\":0,\"width\":80},\"spec_no\":{\"is_display\":0,\"width\":80},\"goods_no\":{\"is_display\":0,\"width\":80},\"spec_code\":{\"is_display\":0,\"width\":80}},\"print\":\"@w0Luru1dt1vovf0ncLzatfvfpte5dqOncLTquK5ut1aDdqPjvevnugfUzwXqufrmptbncKLuru0Xpty3dqPjvevnmJ0XoduncKLuru0ZpteYnG0ksvrfttq9otCncKLuru01ptiZmG0ksvrftty9nJKncKLuru03pte4mG0ksvrfttG9mJu3dqPjvevnot0YntCncKLuru0Xmd0YntCncKLuru0Xmt0XmJmncKLuru0XmJ0ZnJencKLuru0XmZ05nb0ksvrftte0ptmWnG0ksvrftte1ptmYnW0ksvrftte2ptmWnG0ksvrftte3ptmYob0ksvrftte4ptm0ob0ksvrftte5pty5dqOncLTquK5mruzuxq0ksvrftvaHBMvSufautd0WdqPjvevnmt0Xoq0ksvrftti9nJqncKLuru0ZpteZdqPjvevnnd0Ymq0ksvrfttu9mJbncKLuru02ptm4nb0ksvrfttC9nde0dqPjvevnod0ZnJqncKLuru05ptq5mb0ksvrftteWptyXnW0ksvrftteXptqXnq0ksvrftteYptbncKLuru0XmZ0ZnZCncKLuru0Xnd0XmdencKLuru0Xnt0XmdencKLuru0XnJ0WdqPjvevnmtC9ltyncKLuru0Xod0YmJKncKLuru0Xot0YmdqncG0kw1astLDjrfrixq0ksvrftvaHBMvSufautd04nJKncKLuru0XpteYob0ksvrftti9mti0dqPjvevnmZ0ZmdyncKLuru00pteYmG0ksvrfttu9mZGWdqPjvevnnJ0XmZCncKLuru03pteZmq0ksvrfttG9mtiYdqPjvevnot0XmJincKLuru0Xmd0XmJincKLuru0Xmt0ZmtencKLuru0XmJ05oq0ksvrftteZpteXnb0ksvrftte0ptmXnq0ksvrftte1ptmXmW0ksvrftte2ptK5dqPjvevnmtC9otKncKLuru0Xod05oq0ksvrftte5pteWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd00odbncKLuru0Xpti1dqPjvevnmJ0Ymb0ksvrfttm9ntencKLuru00ptiWdqPjvevnnt04mq0ksvrftty9mJbncKLuru03ptiWdqPjvevnod00ob0ksvrfttK9ndGncKLuru0Xmd00ob0ksvrftteXptu2dqPjvevnmti9mJbncKLuru0XmZ0Ymb0ksvrftte0ptiWdqPjvevnmtu9mJbncKLuru0XnJ0Ymb0ksvrftte3ptiWdqPjvevnmtG9mJbncKLuru0Xot0Ymb0kdqPBufjorK9ovfnjwKvDdqPjvevnmt0Xmq0ksvrftti9mtencKLuru0ZpteXdqPjvevnnd0Xmq0ksvrftty9mtencKLuru03pteXdqPjvevnod0Ynb0ksvrfttK9mJqncKLuru0Xmd0Ynb0ksvrftteXpteXdqPjvevnmti9mtencKLuru0XmZ0Xmq0ksvrftte0pteXdqPjvevnmtu9mtencKLuru0XnJ0Xmq0ksvrftte3pteXdqPjvevnmtG9mtencKLuru0Xot0Xmq0kdqPBufjoqK9mrf0ncKLuru0XptencKLuru0YptencKLuru0ZptencKLuru00ptencKLuru02ptencKLuru03ptencKLuru04ptencKLuru05ptencKLuru0Xmd0XdqPjvevnmte9mq0ksvrftteYptencKLuru0XmZ0XdqPjvevnmtq9mq0ksvrftte1ptencKLuru0XnJ0XdqPjvevnmtC9mq0ksvrftte4ptencKLuru0Xot0XdqOncLTdteftu0Lorevyxq0ksvrftte9mG0ksvrftti9mG0ksvrfttm9mG0ksvrfttq9mG0ksvrfttu9nb0ksvrftty9mG0ksvrfttC9mG0ksvrfttG9mG0ksvrfttK9mG0ksvrftteWptincKLuru0Xmt0YdqPjvevnmti9mG0ksvrftteZptincKLuru0Xnd0YdqPjvevnmtu9mG0ksvrftte2ptincKLuru0XnZ0YdqPjvevnmtG9mG0ksvrftte5ptincG0kw0nVBNrLBNrDdqPjvevnmt10nKS4l3nQtdaoweqRDZ09dqPjvevnmJ10nKS4l3nQthL0ytCRzZ09dqPjvevnmZ10nKS4l3nQthORCLa1tfHzmxjJpq0ksvrfttq9DgvYr3PnudDZofK9dqPjvevnnt1qr0POyZjvz2fisMXAAJaPyuHsmgneB3zmmLzYwwK1m1Lxnw5Ar2XOyMK1AMjPogLmEJrlsunaz0LdqwDjq0e4yZnsnwjhvsTdAufNxg5jq0fNsunaz0LdqwDjq0fNsunaAMjhow5Hwe4WyvDo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\\r\\n\",\"width\":\"869\",\"height\":\"480\",\"printor_float_position\":{\"total\":0,\"rows\":[]},\"background_url\":\"\",\"font_info\":{\"font_family\":\"宋体\",\"font_size\":\"9\",\"title_show\":\"0\",\"line_show\":\"1\",\"number_show\":\"1\"},\"item_to_position\":{\"contact\":[1,12],\"mobile\":[2],\"address_detail\":[3],\"shop_name\":[4],\"goods_info_table\":[5],\"receiver_name\":[6],\"receiver_mobile\":[7],\"receiver_province\":[8],\"receiver_city\":[9],\"receiver_district\":[10],\"receiver_address_detail\":[11],\"buyer_nick\":[13],\"buyer_message\":[14],\"cs_remark\":[15],\"print_date\":[18],\"province\":[19]},\"total_item\":19}','','','','0','0',NOW(),NOW());
		INSERT INTO `cfg_print_template` ( `type`, `title`, `content`, `logistics_list`, `shop_ids`, `warehouse_list`, `is_disabled`, `is_default`, `modified`, `created`) VALUES('0','天天快递普通','{\"print\":\"@w0Luru1dt1vovf0ncLzatfvfpte2dqOncLTquK5ut1aDdqPjvevnugfUzwXqufrmptbncKLuru0XptG5dqPjvevnmJ0XndincKLuru0ZpteXnW0ksvrfttq9mJuYdqPjvevnnt04mG0ksvrftty9mtKZdqPjvevnnZ0XndmncKLuru04pte4ob0ksvrfttK9mte0dqPjvevnmtb9mtGXdqPjvevnmte9mZb0dqPjvevnmti9mZu4dqPjvevnmtm9mtiWdqPjvevnmtq9mJmZdqPjvevnmtu9mJm0dqPjvevnmty9mJm1dqOncLTquK5mruzuxq0ksvrftvaHBMvSufautd0WdqPjvevnmt05ob0ksvrftti9mZqncKLuru0ZptK5dqPjvevnnd0YmJmncKLuru01ptq1mb0ksvrftty9mZC1dqPjvevnnZ0ZnZyncKLuru04ptu0nG0ksvrfttK9nJiWdqPjvevnmtb9mZuncKLuru0Xmt0Ynq0ksvrftteYptiYdqPjvevnmtm9ndq4dqPjvevnmtq9mZG1dqPjvevnmtu9ndKWdqPjvevnmty9nJeWdqOncLTquK5xsurusf0ncKLuru1qyw5LBfaqveW9ody5dqPjvevnmt0XmdbncKLuru0Yptm0mG0ksvrfttm9mtbWdqPjvevnnd0XmZyncKLuru01pteWmb0ksvrftty9mtuXdqPjvevnnZ0ZndGncKLuru04pteXmb0ksvrfttK9mtbWdqPjvevnmtb9mZqWdqPjvevnmte9ndG2dqPjvevnmti9ndG2dqPjvevnmtm9mtbWdqPjvevnmtq9mtbWdqPjvevnmtu9mtbWdqPjvevnmty9mteXdqOncLTquK5iruLhsfrDdqPjvevnugfUzwXqufrmptq4mb0ksvrftte9mJuncKLuru0Yptm1dqPjvevnmZ0Ynq0ksvrfttq9mZencKLuru01pti2dqPjvevnnJ0Ynq0ksvrfttC9nduncKLuru04ptmWdqPjvevnot0YnG0ksvrftteWptGXdqPjvevnmte9mZbncKLuru0XmJ0Zmb0ksvrftteZptiWdqPjvevnmtq9ndincKLuru0Xnt00nq0ksvrftte2ptq2dqOncLTquK5gt05uu0LArv0ncKLuru0Xpte0dqPjvevnmJ0XmG0ksvrfttm9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\\r\\n\",\"width\":\"869\",\"height\":\"480\",\"printor_float_position\":{\"total\":0,\"rows\":[]},\"font_info\":{\"font_family\":\"宋体\",\"font_size\":\"\",\"title_show\":\"0\",\"line_show\":\"1\",\"number_show\":\"1\"},\"cn_setting\":{\"logo_show\":\"1\"},\"item_to_position\":{\"contact\":[1],\"address_detail\":[2],\"shop_name\":[3],\"mobile\":[4],\"receiver_name\":[5],\"receiver_mobile\":[6],\"receiver_address_detail\":[7],\"receiver_telno\":[8],\"print_date\":[9],\"goods_info_table\":[10],\"cs_remark\":[11],\"buyer_message\":[12],\"buyer_nick\":[13],\"receiver_province\":[14],\"receiver_city\":[15],\"receiver_district\":[16]},\"total_item\":16,\"formatter_table_info\":{\"goods_name\":{\"is_display\":\"0\",\"width\":90},\"short_name\":{\"is_display\":0,\"width\":90},\"spec_name\":{\"is_display\":1,\"width\":\"180\"},\"num\":{\"is_display\":1,\"width\":\"60\"},\"price\":{\"is_display\":0,\"width\":90},\"spec_no\":{\"is_display\":0,\"width\":90},\"goods_no\":{\"is_display\":0,\"width\":90},\"spec_code\":{\"is_display\":0,\"width\":90},\"position_no\":{\"is_display\":0,\"width\":90}}}','','','','0','0',NOW(),NOW());

		INSERT INTO `purchase_provider` ( `id`,`provider_name`, `is_disabled`, `created`) VALUES(0,'无',0,NOW());
		update `purchase_provider` set id = 0 WHERE provider_name='无';

		INSERT INTO `cfg_flags` (`flag_id`, `flag_class`, `flag_name`, `bg_color`, `font_color`, `font_name`, `is_underline`, `is_italic`, `is_bold`, `is_disabled`, `is_builtin`, `modified`, `created`) 
		VALUES('1','1','冻结','#6ccff7','#000000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('2','1','取消','#48929B','#000000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('3','1','拆分订单','#875F9A','#F5AB35','SimSun','0','0','0','0','1',NOW(),NOW()),
		('4','1','合并订单','#292a34','#ffffff','SimSun','0','0','0','0','1',NOW(),NOW()),
		('5','1','驳回订单','#C93756','#000000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('6','1','货到付款','#F9690E','#000000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('7','1','手工建单','#87D37C','#003171','SimSun','0','0','0','0','1',NOW(),NOW()),
		('8','1','退款','#EE1D24','#ffffff','SimSun','0','0','0','0','1',NOW(),NOW()),
		('19','1','换货销售单','#FB00FB','#000000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('30','1','异常订单','#003370','#ff0000','SimSun','0','0','0','0','1',NOW(),NOW()),
		('40','7','警戒库存','#ee1d24','#fff','SimSun','0','0','0','0','1',NOW(),NOW()),
		('1000','1','区分内置与自定义标记','#000000','#000000','SimSun','0','0','0','1','1',NOW(),NOW()) ;

		insert into `cfg_goods_unit` (`rec_id`, `name`, `deleted`, `is_disabled`, `remark`, `modified`, `created`) values
		(0,'无','1000-01-01 00:00:00','0','',NOW(),NOW());
		update `cfg_goods_unit` set rec_id = 0 WHERE name='无';

		insert into `cfg_goods_unit` (`rec_id`, `name`, `deleted`, `is_disabled`, `remark`, `modified`, `created`) values
		('1','个','1000-01-01 00:00:00','0','',NOW(),NOW()),
		('2','件','1000-01-01 00:00:00','0','',NOW(),NOW()),
		('3','袋','1000-01-01 00:00:00','0','',NOW(),NOW()),
		('4','包','1000-01-01 00:00:00','0','',NOW(),NOW());

		insert into `purchase_provider_group` (`id`,`provider_group_name`,`modified`,`created`) values(1,'无',NOW(),NOW());


        SET FOREIGN_KEY_CHECKS = 1;

	END IF;	
END//
DELIMITER ;
