<?php
return array(
	//'配置项'=>'配置值'
	//出库单状态 
/*
5已取消
48编辑中 
50待审核 
52待推送 
53同步失败 
54获取面单号 
55已审核 
90部分发货 
95已发货 
100已签收 
105部分打款 
110已完成
*/
/* 	'status' =>array(
			array('key' =>'5'   ,'value'=>'已取消'  	),
			array('key' =>'48'  ,'value'=>'编辑中'  	),
			array('key' =>'50'  ,'value'=>'待审核'  	),
			array('key' =>'52'  ,'value'=>'待推送'  	),
			array('key' =>'53'  ,'value'=>'同步失败' 	),
			array('key' =>'54'  ,'value'=>'获取面单号'	),
			array('key' =>'55'  ,'value'=>'已审核'  	),
			array('key' =>'90'  ,'value'=>'部分发货' 	),
			array('key' =>'95'  ,'value'=>'已发货'  	),
			array('key' =>'100' ,'value'=>'已签收'  	),
			array('key' =>'105' ,'value'=>'部分打款' 	),
			array('key' =>'110' ,'value'=>'已完成'  	)
	), */
	/* 'role_mask'=>array(
		array('key' =>'1'  ,'value' => '业务员' ),
		array('key' =>'2'  ,'value' => '审单员' ),
		array('key' =>'4'  ,'value' => '打单员' ),
		array('key' =>'8'  ,'value' => '扫描员' ),
		array('key' =>'16' ,'value' => '打包员' ),
		array('key' =>'32' ,'value' => '拣货员' ),
		array('key' =>'64' ,'value' => '发货员' ),
		array('key' =>'128','value' => '结算员' ),
		array('key' =>'256','value' => '检视员' )
	), */
	/* 'goods_type'=>array(
		array('key'=>'0','value'=>'其他'),
		array('key'=>'1','value'=>'销售商品'),
		array('key'=>'2','value'=>'原材料'),
		array('key'=>'3','value'=>'包装'),
		array('key'=>'4','value'=>'周转材料'),
		array('key'=>'5','value'=>'虚拟商品'),
		array('key'=>'6','value'=>'固定资产')
	), */
	/*'1网店销售2线下零售3售后换货4批发业务',*/
	/* 'trade_type'=>array(
		array('key'=>'1','value'=>'网店销售'),
		array('key'=>'2','value'=>'线下零售'),
		array('key'=>'3','value'=>'售后换货'),
		array('key'=>'4','value'=>'批发业务'),
	), */
	/* 'sotck_in_order_type'=>array(
		array('key'=>'1','value'=>'采购入库'),
		array('key'=>'3','value'=>'退换入库'),
		array('key'=>'6','value'=>'其它入库'),
	), */
	/* 'list_price'=>array(
		array('key'=>'wholesale_price','value'=>'批发价'),
		array('key'=>'retail_price','value'=>'零售价'),
		array('key'=>'lowest_price','value'=>'最低价'),
		array('key'=>'member_price','value'=>'会员价'),
		array('key'=>'market_price','value'=>'市场价'),
		array('key'=>'src_price','value'=>'原价'),
	),	 */			
	/* 'form_config'=>array(
		'default_form'		=>array('search[src_order_type]'=>'all','search[src_order_no]'=>'','search[stockin_no]'=>'','search[provider_id]'=>'all','search[remark]'=>'','search[logistics_id]'=>'0','search[logistics_no]'=>'','search[src_price]'=>'0.00','search[total_price]'=>'0.00','search[discount]'=>'0.00','search[post_fee]'=>'0.00','search[other_fee]'=>'0.00','search[tax_amount]'=>'0.00','search[total_amount]'=>'0.00',),
		'default_purchase'	=>array('search[src_order_type]'=>'1','search[src_order_no]'=>'','search[stockin_no]'=>'','search[provider_id]'=>'all','search[remark]'=>'','search[logistics_id]'=>'0','search[logistics_no]'=>'','search[src_price]'=>'0.00','search[total_price]'=>'0.00','search[discount]'=>'0.00','search[post_fee]'=>'0.00','search[other_fee]'=>'0.00','search[tax_amount]'=>'0.00','search[total_amount]'=>'0.00',),
		'default_refund'	=>array('search[src_order_type]'=>'3','search[src_order_no]'=>'','search[stockin_no]'=>'','search[provider_id]'=>'all','search[remark]'=>'','search[logistics_id]'=>'0','search[logistics_no]'=>'','search[src_price]'=>'0.00','search[total_price]'=>'0.00','search[discount]'=>'0.00','search[post_fee]'=>'0.00','search[other_fee]'=>'0.00','search[tax_amount]'=>'0.00','search[total_amount]'=>'0.00',),
		'default_other'		=>array('search[src_order_type]'=>'6','search[src_order_no]'=>'','search[stockin_no]'=>'','search[provider_id]'=>'all','search[remark]'=>'','search[logistics_id]'=>'0','search[logistics_no]'=>'','search[src_price]'=>'0.00','search[total_price]'=>'0.00','search[discount]'=>'0.00','search[post_fee]'=>'0.00','search[other_fee]'=>'0.00','search[tax_amount]'=>'0.00','search[total_amount]'=>'0.00',),
	), */
	'apilogssync_sync_status'=>array(
		array('value'=>'淘宝发货等待判断','key'=>'-3',),
		array('value'=>'淘宝发货不可达','key'=>'-2',),
		array('value'=>'淘宝发货可达','key'=>'-1',),
		array('value'=>'等待同步','key'=>'0',),
		array('value'=>'提交运单信息失败','key'=>'1',),
		array('value'=>'同步失败','key'=>'2',),
		array('value'=>'同步成功','key'=>'3',),
		array('value'=>'手动设置同步成功','key'=>'4',),
		array('value'=>'手动取消同步','key'=>'5',),
	),

	
    //出库单状态
    'stockout_status' => array(
		array("status_id" => "all", "name" => "全部"),
		array("status_id" => "5", "name" => "已取消"),
		array("status_id" => "48", "name" => "编辑中"),
		array("status_id" => "110", "name" => "已完成"),
	),

    //出库类型
    'stockout_type' => array(
		array("status_id" => "all", "name" => "全部"),
		array("status_id" => "4", "name" => "盘亏出库"),
		array("status_id" => "7", "name" => "其他出库"),
	),
    //仓库类别
    'warehouse_type' => array(
		array("status_id" => "1", "name" => "普通仓库"),
		array("status_id" => "2", "name" => "物流宝"),
		array("status_id" => "3", "name" => "京东仓储"),
		array("status_id" => "127", "name" => "其他仓库"),
	),
    //入库单状态
    'stockin_status' =>  array(
		array("status_id" => "all", "name" => "全部"),
		array("status_id" => "10", "name" => "已取消"),
		array("status_id" => "20", "name" => "编辑中"),
//		array("status_id" => "30", "name" => "待审核"),
//		array("status_id" => "32", "name" => "待推送"),
//		array("status_id" => "35", "name" => "委外待入库"),
//		array("status_id" => "40", "name" => "待关联"),
//		array("status_id" => "50", "name" => "待价格确认"),
//		array("status_id" => "60", "name" => "待结算"),
//		array("status_id" => "70", "name" => "暂估结算"),
		array("status_id" => "80", "name" => "已完成"),
	),
    ////入库类型
    'stockin_type' => array(
		"1" => "采购入库",
		"2" => "调拨入库",
		"3" => "退货入库",
		"4" => "盘盈入库",
		"6" => "其他入库",
		"9" => "初始化入库",
	),
    'stockout_type' => array(
		"1" => "销售出库",
		 "2" => "调拨出库",
		 "4" => "盘亏出库",
		 "7" => "其他出库",
		 "11" => "初始化出库",
	),
	'salesrefund_type' => array(
		array("status_id" => "all", "name" => "全部"),
		array("status_id" => "2", "name" => "退货"),
		array("status_id" => "3", "name" => "换货"),
	),
	'salesrefund_status' => array(
		array("status_id" => "all", "name" => "全部"),
		array("status_id" => "60", "name" => "待收货"),
		array("status_id" => "70", "name" => "部分到货"),
	),
	'stockout_reason' =>array(
	array("status_id" => "7", "name" => "其它入库"),
	),
    //截停原因block_reason  清除截停时用到  销售出库 模块
    'stockout_block_reason'=>array(
        '1' =>'申请退款',
        '2'=>'已退款',
        '4'=>'地址被修改',
        '8'=>'发票被修改',
        '16'=>'物流被修改',
        '32'=>'仓库变化',
        '64'=>'备注修改',
        '128'=>'更换货品',
        '256'=>'取消退款',
       // '512'=> '放弃抢单',
       // '1024'=> '其他',
        '2048'=> '拦截赠品',
        '4096'=> '平台已发货',
    ),
);