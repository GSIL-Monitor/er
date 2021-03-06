<?php
namespace Trade\Field;
use Common\Common\Field;
class TradeField extends Field{

    protected function get($key){
    	if($key=="trade_manage"){
    		$res_cfg_val=get_config_value('order_fa_condition',0);
    		if($res_cfg_val==1){
    			$trade_status_formatter='formatter.trade_status_fc';
    		}else{
    			$trade_status_formatter='formatter.trade_status';
    		}
    	}
    	$fields = array(
    			'trade_manage'=> array(
                        'id'     => array('field'=>'id','hidden'=>true,'sortable'=>true),
    					'flag_id'=> array('field'=>'flag_id','hidden'=>true,'sortable'=>true),
						'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
    					'系统标记'=>array('field'=>'flag','width'=>100,'frozen'=>true),
    					'订单号'   => array('field'=>'trade_no','width'=>100,'sortable'=>true),
    					'店铺名称'  => array('field'=>'shop_name','width'=>100,'sortable'=>true),
    					'平台类型'  => array('field'=>'platform_id','width'=>100,'sortable'=>true,'formatter'=>'formatter.platform_id'),
    					'原始单号'  => array('field'=>'src_tids','width'=>100,'sortable'=>true),
    					'客户网名'   => array('field'=>'buyer_nick','width'=>100,'sortable'=>true),
    					'收件人'  => array('field'=>'receiver_name','width'=>100,'sortable'=>true),
    					'地区'  => array('field'=>'receiver_area','width'=>150,'sortable'=>true),
    					'地址'  => array('field'=>'receiver_address','width'=>150,'sortable'=>true),
    					'手机'  => array('field'=>'receiver_mobile','width'=>100,'sortable'=>true),
    					'固话'  => array('field'=>'receiver_telno','width'=>100,'sortable'=>true),
    					'邮编'  => array('field'=>'receiver_zip','width'=>100,'sortable'=>true),
    					'发货条件'  => array('field'=>'delivery_term','width'=>100,'sortable'=>true,'formatter'=>'formatter.delivery_term'),
    					'订单状态'  => array('field'=>'trade_status','width'=>100,'sortable'=>true,'formatter'=>$trade_status_formatter),
    					'退货状态'  => array('field'=>'refund_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.refund_status'),
    					'发货状态'  => array('field'=>'consign_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.sales_consign_status'),
    					'发货时间'  => array('field'=>'consign_time','width'=>130),
						'冻结原因'  => array('field'=>'freeze_info','width'=>100,'sortable'=>false),
    					'仓库'  => array('field'=>'warehouse_name','width'=>100,'sortable'=>true),
    					'仓库类型'  => array('field'=>'warehouse_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.warehouse_type'),
    					'物流公司'  => array('field'=>'logistics_id','width'=>100,'sortable'=>true),
    					'物流单号'  => array('field'=>'logistics_no','width'=>100,'sortable'=>true),
    					'客服备注'  => array('field'=>'cs_remark','width'=>100,'sortable'=>true),
					'标旗'    => array('field'=>'remark_flag','width'=>60,'sortable'=>true),
    					'买家留言'  => array('field'=>'buyer_message','width'=>100,'sortable'=>true),
    					'打印备注'  => array('field'=>'print_remark','width'=>100,'sortable'=>true),
    					'货品种类数'  => array('field'=>'goods_type_count','width'=>100,'sortable'=>true),
    					'货品总量'  => array('field'=>'goods_count','width'=>100,'sortable'=>true),
    					'总货款'  => array('field'=>'goods_amount','width'=>100,'sortable'=>true),
    					'邮费'  => array('field'=>'post_amount','width'=>100,'sortable'=>true),
    					'优惠'  => array('field'=>'discount','width'=>100,'sortable'=>true),
    					'应收'  => array('field'=>'receivable','width'=>100,'sortable'=>true),
    					'已付'  => array('field'=>'paid','width'=>100,'sortable'=>true),
    					'款到发货金额'  => array('field'=>'dap_amount','width'=>100,'sortable'=>true),
    					'买家COD费用'  => array('field'=>'cod_amount','width'=>100,'sortable'=>true),
    					// '佣金'  => array('field'=>'commission','width'=>100,'sortable'=>true),
    					'预估邮资成本'  => array('field'=>'post_cost','width'=>100,'sortable'=>true),
    					'货品估算成本'  => array('field'=>'goods_cost','width'=>100,'sortable'=>true),
    					'估重'  => array('field'=>'weight','width'=>100,'sortable'=>true),
    					'预估利润'=>array('field'=>'profit','width'=>100,'sortable'=>true),
    					'需要发票'  => array('field'=>'invoice_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.invoice_type'),
    					'发票抬头'  => array('field'=>'invoice_title','width'=>100,'sortable'=>true),
    					'发票内容'  => array('field'=>'invoice_content','width'=>100,'sortable'=>true),
    					'下单时间'  => array('field'=>'trade_time','width'=>100,'sortable'=>true),
    					'付款时间'  => array('field'=>'pay_time','width'=>100,'sortable'=>true),
    					'支付账户'  => array('field'=>'pay_account','width'=>100,'sortable'=>true),
    					//'分销类型'  => array('field'=>'fenxiao_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.fenxiao_type'),
    					//'分销商名称'  => array('field'=>'fenxiao_nick','width'=>100,'sortable'=>true),
    					'大头笔'  => array('field'=>'receiver_dtb','width'=>100,'sortable'=>true),
    					'送货时间'  => array('field'=>'to_deliver_time','width'=>100,'sortable'=>true),
    					'业务员'  => array('field'=>'salesman_id','width'=>100,'sortable'=>true),
    					'审核人'  => array('field'=>'checker_id','width'=>100,'sortable'=>true),
    					'类型'  => array('field'=>'trade_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_type'),
    					'处理天数'  => array('field'=>'handle_days','width'=>100,'sortable'=>true),
    					'订单来源'  => array('field'=>'trade_from','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_from'),
    					//'货品商家编码'  => array('field'=>'single_spec_no','width'=>100,'sortable'=>true),
    					'出库单号'  => array('field'=>'stockout_no','width'=>100,'sortable'=>true),
    					'原始货品种类'  => array('field'=>'raw_goods_type_count','width'=>100,'sortable'=>true),
    					'原始货品数量'  => array('field'=>'raw_goods_count','width'=>100,'sortable'=>true),
    					'标记名称'  => array('field'=>'flag_name','width'=>100,'sortable'=>true),
    					'递交时间'  => array('field'=>'created','width'=>100,'sortable'=>true),
    			),
    			'shop_list'=>array(
    					'id'     => array('field'=>'id','hidden'=>true,'sortable'=>true),
    					//'店铺编号'  => array('field'=>'shop_no','width'=>100,'sortable'=>true),
    					'店铺名称'  => array('field'=>'shop_name','width'=>150,'sortable'=>true),
    					'平台'  	 => array('field'=>'platform_id','width'=>100,'sortable'=>true,'formatter'=>'formatter.platform_id'),
    					'子平台'   => array('field'=>'sub_platform_id','width'=>100,'sortable'=>true,'formatter'=>'formatterSubPlatForm'),
    					'平台账号'  => array('field'=>'account_nick','width'=>150,'sortable'=>true),
    					'授权状态'  => array('field'=>'auth_state','width'=>100,'sortable'=>true,'formatter'=>'formatter.auth_state'),
    			),
				"originalorder"  => array(
                        "checkbox"=>array("field"=>"ck","hidden"=>false,"checkbox"=>true,"frozen"=>true),
						"id"     => array("field" => "id", "hidden" => true),
						"平台"     => array("field"     => "platform_id", "width" => 100, "sortable" => true,
								"formatter" => "formatter.platform_id"),
						"店铺"     => array("field" => "shop_name", "width" => 150/*, "sortable" => true*/),
						"原始单号"   => array("field" => "tid", "width" => 150, "sortable" => true),
						"平台状态"   => array("field"     => "trade_status", "width" => 100, "sortable" => true,
								"formatter" => "formatter.api_trade_status"),
						"支付状态"   => array("field"     => "pay_status", "width" => 100, "sortable" => true,
								"formatter" => "formatter.pay_status"),
						"发货条件"   => array("field"     => "delivery_term", "width" => 100, "sortable" => true,
								"formatter" => "formatter.delivery_term"),
						"支付方式"   => array("field"     => "pay_method", "width" => 100, "sortable" => true,
								"formatter" => "formatter.pay_method"),
						"退货状态"   => array("field"     => "refund_status", "width" => 100, "sortable" => true,
								"formatter" => "formatter.refund_status"),
						"系统状态"   => array("field"     => "process_status", "width" => 100, "sortable" => true,
								"formatter" => "formatter.process_status"),//系统状态
						"下单时间"   => array("field" => "trade_time", "width" => 150, "sortable" => true),
						"支付时间"   => array("field" => "pay_time", "width" => 150, "sortable" => true),
						"客户网名"   => array("field" => "buyer_nick", "width" => 150, "sortable" => true),
						"收件人姓名"  => array("field" => "receiver_name", "width" => 150, "sortable" => true),//receiver_name
						"省市县"    => array("field" => "receiver_area", "width" => 150, "sortable" => true),
						//"区域"     => array("field" => "receiver_district", "width" => 150, "sortable" => true),
						"收件地址"   => array("field" => "receiver_address", "width" => 150, "sortable" => true),
						"手机"     => array("field" => "receiver_mobile", "width" => 120, "sortable" => true),
						"电话"     => array("field" => "receiver_telno", "width" => 120, "sortable" => true),
						"邮编"     => array("field" => "receiver_zip", "width" => 100, "sortable" => true),
						"送货时间"   => array("field" => "to_deliver_time", "width" => 150, "sortable" => true),//送货时间 to_deliver_time
						"买家备注"   => array("field" => "buyer_message", "width" => 150, "sortable" => true),
						"客服备注"   => array("field" => "remark", "width" => 150, "sortable" => true),
                        "标旗"   => array("field" => "remark_flag", "width" => 60, "sortable" => true),
						"货款"     => array("field" => "goods_amount", "width" => 100, "sortable" => true),
						"邮费"     => array("field" => "post_amount", "width" => 100, "sortable" => true),
						"其他收费"   => array("field" => "other_amount", "width" => 100, "sortable" => true),
						"优惠"     => array("field" => "discount", "width" => 100, "sortable" => true),
						"已付"     => array("field" => "paid", "width" => 100, "sortable" => true),
						"平台费用"   => array("field" => "platform_cost", "width" => 100, "sortable" => true),
						"已收"     => array("field" => "received", "width" => 100, "sortable" => true),
						"应收"     => array("field" => "receivable", "width" => 100, "sortable" => true),
						"款到发货金额" => array("field" => "dap_amount", "width" => 100, "sortable" => true),
						"货到付款金额" => array("field" => "cod_amount", "width" => 100, "sortable" => true),
						"退款金额"   => array("field" => "refund_amount", "width" => 100, "sortable" => true),
						"物流方式"   => array("field"     => "logistics_type", "width" => 150, "sortable" => true,
								"formatter" => "formatter.logistics_type"),
						"发票类别"   => array("field"     => "invoice_type", "width" => 100, "sortable" => true,
								"formatter" => "formatter.invoice_type"),
						"发票抬头"   => array("field" => "invoice_title", "width" => 150, "sortable" => true),
						"发票内容"   => array("field" => "invoice_content", "width" => 150, "sortable" => true),
						"结束时间"   => array("field" => "end_time", "width" => 150, "sortable" => true),
					/*"业务员"    => array("field" => "fullname", "width" => 150, "sortable" => true),*/
					    "外部订单"   => array("field"     => "is_auto_wms", "width" => 60, "sortable" => true),
					    "订单来源"   => array("field"     => "trade_from", "width" => 100, "sortable" => true,
								"formatter" => "formatter.trade_from"),
						"修改时间"   => array("field" => "modified", "width" => 150, "sortable" => true),
						"创建时间"   => array("field" => "created", "width" => 150, "sortable" => true),
				),
    	);
    	return $fields[$key];
    }
}