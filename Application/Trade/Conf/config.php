<?php
return array(
	//'配置项'=>'配置值'
	
	//订单后台验证数组
	'trade_type'=>array(1,2,3,4),
	'delivery_term'=>array(1,2,3),
	'invoice_type'=>array(0,1,2),
	'pay_method'=>array(1,2,3,4,5,6),
	//退换单
	'refund_type'=>array(2,3,4,5),
	'flow_type'=>array(1,2),
	//异常单原因
	'bad_reason'=>array(
			1=>'无库存记录',
			2=>'地址发生变化',
			4=>'发票变化',
			8=>'仓库变化',
			16=>'备注变化',
			32=>'平台更换货品',
			64=>'退款',
			128=>'平台换货自动更换订单货品',
			256=>'平台已发货',
			512=>'拦截赠品'
	),
	
	//订单设置名称-用于日志记录
	'system_setting'=>array(
			'order_check_warn_has_unmerge'			=>'拦截同名未合并订单（开启同名未合并订单标记）',
			'order_check_warn_has_unmerge_checked'	=>'同名未合并（包含已审核订单）',
			'order_check_warn_has_unmerge_freeze'	=>'同名未合并（包含冻结）',
			'order_check_warn_has_unmerge_address'	=>'同名未合并（包含不同地址）',
			'order_check_warn_has_unpay'			=>'提示有未付款的同名未合并订单',
			'order_check_no_stock'					=>'阻止库存不足订单通过审核',
			'order_check_get_waybill'				=>'订单审核自动获取电子面单',
			'order_allow_man_create_cod'			=>'允许手工新建COD（货到付款）订单',
			'sales_trade_split_num'					=>'允许拆分数量为小数',
			'order_allow_part_sync'					=>'开启淘宝拆单发货',
			'goods_match_split_char'				=>'平台货品匹配截取字符',
			'order_sync_interval'					=>'下载订单时间间隔',
			'order_go_preorder'						=>'开启预订单',
			'order_preorder_lack_stock'				=>'库存不足转预订单',
			'auto_check_is_open'					=>'开启自动审核',
			'auto_check_buyer_message'				=>'自动审核无客户备注',
			'auto_check_csremark'					=>'自动审核无客服备注',
			'auto_check_no_invoice'					=>'自动审核无发票',
			'auto_check_no_adr'						=>'自动审核收货地址无（村、组）',
			'auto_check_start_time'					=>'自动审核下单开始时间',
			'auto_check_end_time'					=>'自动审核下单结束时间',
			'order_fa_condition'					=>'开启财务审核',
			'order_fc_man_order'					=>'手工建单进财务审核',
			'order_fc_excel_import'					=>'EXCEL导入订单进财务审核',
			'order_fc_receivable_outnumber'			=>'订单应收金额高于 ',
			'order_fc_discount'						=>'优惠金额达到',
	)
);
?>