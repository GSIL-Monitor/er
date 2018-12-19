<?php
namespace Stock\Common;

class StockFields
{
    static public function getStockFields($name)
    {
        $name = strtolower($name);
		if (isset(self::$fields[$name])){
			return self::$fields[$name];
		}else{
			\Think\Log::write('unknown field:'.$name);
			return array();
		}
    }
	
	static private $fields = array(
									'stockout_print_fields'=>array(
											'订单编号'=>array('field'=>'src_order_no','width'=>'100'),
											'出库单编号'=>array('field'=>'stockout_no','width'=>'100',),
											'店铺'=>array('field'=>'shop_name','width'=>'100',),
											'仓库'=>array('field'=>'warehouse_name','width'=>'100',),
											//'仓库类型'=>array('field'=>'warehouse_type','width'=>'100',),
											'物流公司'=>array('field'=>'logistics_name','width'=>'100',),
											'物流单号'=>array('field'=>'logistics_no','width'=>'100',),
											'网名'=>array('field'=>'buyer_nick','width'=>'100',),
											'原始单号'=>array('field'=>'src_tids','width'=>'100',),
											'审单员'=>array('field'=>'checker_id','width'=>'100',),
											'收货人'=>array('field'=>'receiver_name','width'=>'100',),
											'省市区'=>array('field'=>'receiver_area','width'=>'100',),
											'收货地址'=>array('field'=>'receiver_address','width'=>'100',),
											'收件人手机'=>array('field'=>'receiver_mobile','width'=>'100',),
											'收件人电话'=>array('field'=>'receiver_telno','width'=>'100',),
											'邮编'=>array('field'=>'receiver_zip','width'=>'100',),
											'状态'=>array('field'=>'status','width'=>'100','formatter'=>'formatter.stockout_status',),
											'发货状态'=>array('field'=>'consign_status','width'=>'100',),
											'订单类型'=>array('field'=>'trade_type','width'=>'100','formatter'=>'formatter.trade_type',),
											'冻结原因'=>array('field'=>'freeze_reason','width'=>'100',),
											'货品数量'=>array('field'=>'goods_count','width'=>'100',),
											'货品种类'=>array('field'=>'goods_type_count','width'=>'100',),
											'下单时间'=>array('field'=>'trade_time','width'=>'100',),
											'买家留言'=>array('field'=>'buyer_message','width'=>'100',),
											//'是否包含发票'=>array('field'=>'has_invoice','width'=>'100',),
											'id'=>array('field'=>'id','hidden'=>true,),
											'receiver_province'=>array('field'=>'receiver_province','hidden'=>true,),
											'receiver_city'=>array('field'=>'receiver_city','hidden'=>true,),
											'receiver_district'=>array('field'=>'receiver_district','hidden'=>true,),
											'bill_type'=>array('field'=>'bill_type','hidden'=>true,),
											'src_order_type'=>array('field'=>'src_order_type','hidden'=>true,),
											'src_order_id'=>array('field'=>'src_order_id','hidden'=>true,),
											'warehouse_id'=>array('field'=>'warehouse_id','hidden'=>true,),
											'logistics_id'=>array('field'=>'logistics_id','hidden'=>true,),
											'contact'=>array('field'=>'src_order_id','hidden'=>true,),
											'mobile'=>array('field'=>'mobile','hidden'=>true,),
											'telno'=>array('field'=>'telno','hidden'=>true,),
											'province'=>array('field'=>'province','hidden'=>true,),
											'city'=>array('field'=>'city','hidden'=>true,),
											'district'=>array('field'=>'district','hidden'=>true,),
											'address'=>array('field'=>'address','hidden'=>true,),
											'zip'=>array('field'=>'zip','hidden'=>true,),
											'logistics_type'=>array('field'=>'logistics_type','hidden'=>true,),
											'waybill_info'=>array('field'=>'waybill_info','hidden'=>true,),
										),


//									'salestradedetail'=>array(
//										'id'=>array('field'=>'id','hidden'=>true),
//										'订单编号'=>array('field'=>'trade_no','width'=>'100'),
//										'订单状态'=>array('field'=>'trade_status','width'=>'100','formatter'=>'formatter.trade_status',),
//										'交易时间'=>array('field'=>'trade_time','width'=>'150'),
//										'商家编码'=>array('field'=>'spec_no','width'=>'100'),
//										'货品编号'=>array('field'=>'goods_no','width'=>'100'),
//										'货品名称'=>array('field'=>'goods_name','width'=>'100'),
//										'规格码'=>array('field'=>'spec_code','width'=>'100'),
//										'规格名称'=>array('field'=>'spec_name','width'=>'100'),
//										'数量'=>array('field'=>'actual_num','width'=>'100'),
//									),

//									'salreftabsysgoods'=>array(
//										'平台' => array('field' => 'platform_id', 'width' => '100'),
//										'子订单号' => array('field' => 'oid', 'width' => '100'),
//										'退款总额' => array('field' => 'total_amount', 'width' => '100'),
//										'组合装名' => array('field' => 'suite_name', 'width' => '100'),
//										'组合装数量' => array('field' => 'suite_num', 'width' => '100'),
//										'入库数量' => array('field' => 'stockin_num', 'width' => '100'),
//										'订单编号' => array('field' => 'sales_tid', 'width' => '100'),
//										'原始订单号' => array('field' => 'tid', 'width' => '100'),
//										'数量' => array('field' => 'order_num', 'width' => '100'),
//										'价格' => array('field' => 'price', 'width' => '100'),
//										'商家编码' => array('field' => 'spec_no', 'width' => '100'),
//										'货品编号' => array('field' => 'goods_no', 'width' => '100'),
//										'货品名称' => array('field' => 'goods_name', 'width' => '100'),
//										'规格名' => array('field' => 'spec_name', 'width' => '100'),
//										'退款数量' => array('field' => 'refund_num', 'width' => '100'),
//										'备注' => array('field' => 'remark', 'width' => '100'),
//										'原价' => array('field' => 'original_price', 'width' => '100'),
//										'已付金额' => array('field' => 'paid', 'width' => '100'),
//										'优惠' => array('field' => 'discount', 'width' => '100'),
//										'id' => array('field' => 'id', 'hidden' => true)
//									),
//									'salreftabplatformgoods'=>array(
//										'子订单号' => array('field' => 'oid', 'width' => '100'),
//										'货品名称' => array('field' => 'goods_name', 'width' => '100'),
//										'规格' => array('field' => 'spec_name', 'width' => '100'),
//										'货品ID' => array('field' => 'goods_id', 'width' => '100'),
//										'规格ID' => array('field' => 'spec_id', 'width' => '100'),
//										'货品商家编码' => array('field' => 'goods_no', 'width' => '100'),
//										'规格商家编码' => array('field' => 'spec_no', 'width' => '100'),
//										'数量' => array('field' => 'num', 'width' => '100'),
//										'价格' => array('field' => 'price', 'width' => '100'),
//										'折扣' => array('field' => 'discount', 'width' => '100'),
//										'总价' => array('field' => 'share_amount', 'width' => '100'),
//										'id' => array('field' => 'id', 'hidden' => true)
//									),
//									'salreftabapitrade'=>array(
//										'平台' => array('field' => 'platform_id', 'width' => '100'),
//										'店铺' => array('field' => 'shop_id', 'width' => '100'),
//										'原始单号' => array('field' => 'tid', 'width' => '100'),
//										'平台状态' => array('field' => 'trade_status', 'width' => '100'),
//										'担保方式' => array('field' => 'guarantee_mode', 'width' => '100'),
//										'支付状态' => array('field' => 'pay_status', 'width' => '100'),
//										'货到付款' => array('field' => 'delivery_term', 'width' => '100'),
//										'支付方式' => array('field' => 'pay_method', 'width' => '100'),
//										'支付账号' => array('field' => 'pay_account', 'width' => '100'),
//										'退款状态' => array('field' => 'refund_status', 'width' => '100'),
//										'系统状态' => array('field' => 'process_status', 'width' => '100'),
//										'下单时间' => array('field' => 'trade_time', 'width' => '100'),
//										'支付时间' => array('field' => 'pay_time', 'width' => '100'),
//										'客户网名' => array('field' => 'buyer_nick', 'width' => '100'),
//										'收件人姓名' => array('field' => 'receiver_name', 'width' => '100'),
//										'省市县' => array('field' => 'receiver_area', 'width' => '100'),
//										'区域' => array('field' => 'receiver_ring', 'width' => '100'),
//										'收件人地址' => array('field' => 'receiver_address', 'width' => '100'),
//										'手机' => array('field' => 'receiver_mobile', 'width' => '100'),
//										'电话' => array('field' => 'receiver_telno', 'width' => '100'),
//										'邮编' => array('field' => 'receiver_zip', 'width' => '100'),
//										'送货时间' => array('field' => 'to_deliver_time', 'width' => '100'),
//										'买家备注' => array('field' => 'buyer_message', 'width' => '100'),
//										'客服备注' => array('field' => 'remark', 'width' => '100'),
//										'标旗' => array('field' => 'remark_flag', 'width' => '100'),
//										'货款' => array('field' => 'goods_amount', 'width' => '100'),
//										'邮费' => array('field' => 'post_amount', 'width' => '100'),
//										'其它收费' => array('field' => 'other_amount', 'width' => '100'),
//										'优惠' => array('field' => 'discount', 'width' => '100'),
//										'已付' => array('field' => 'paid', 'width' => '100'),
//										'平台费用' => array('field' => 'platform_cost', 'width' => '100'),
//										'已收' => array('field' => 'received', 'width' => '100'),
//										'应收' => array('field' => 'receivable', 'width' => '100'),
//										'款到发货金额' => array('field' => 'dap_amount', 'width' => '100'),
//										'货到付款金额' => array('field' => 'cod_amount', 'width' => '100'),
//										'退款金额' => array('field' => 'refund_amount', 'width' => '100'),
//										'物流方式' => array('field' => 'logistics_type', 'width' => '100'),
//										'发票类别' => array('field' => 'invoice_type', 'width' => '100'),
//										'发票抬头' => array('field' => 'invoice_title', 'width' => '100'),
//										'发票内容' => array('field' => 'invoice_content', 'width' => '100'),
//										'结束时间' => array('field' => 'end_time', 'width' => '100'),
//										'分销方式' => array('field' => 'fenxiao_type', 'width' => '100'),
//										'分销商ID' => array('field' => 'fenxiao_nick', 'width' => '100'),
//										'外部订单' => array('field' => 'is_external', 'width' => '100'),
//										'订单来源' => array('field' => 'trade_from', 'width' => '100'),
//										'修改时间' => array('field' => 'modified', 'width' => '100'),
//										'创建时间' => array('field' => 'created', 'width' => '100'),
//										'id' => array('field' => 'id', 'hidden' => true)
//									),
//									'salreftabsalestrade'=>array(
//										'订单编号' => array('field' => 'trade_no', 'width' => '100'),
//										'店铺' => array('field' => 'shop_id', 'width' => '100'),
//										'原始单号' => array('field' => 'src_tids', 'width' => '100'),
//										'客户网名' => array('field' => 'buyer_nick', 'width' => '100'),
//										'收件人' => array('field' => 'receiver_name', 'width' => '100'),
//										'地区' => array('field' => 'receiver_area', 'width' => '100'),
//										'地址' => array('field' => 'receiver_address', 'width' => '100'),
//										'手机' => array('field' => 'receiver_mobile', 'width' => '100'),
//										'固话' => array('field' => 'receiver_telno', 'width' => '100'),
//										'邮编' => array('field' => 'receiver_zip', 'width' => '100'),
//										'发货条件' => array('field' => 'delivery_term', 'width' => '100'),
//										'退款状态' => array('field' => 'refund_status', 'width' => '100'),
//										'仓库' => array('field' => 'warehouse_id', 'width' => '100'),
//										'物流公司' => array('field' => 'logistics_id', 'width' => '100'),
//										'物流单号' => array('field' => 'logistics_no', 'width' => '100'),
//										'客服备注' => array('field' => 'cs_remark', 'width' => '100'),
//										'买家留言' => array('field' => 'buyer_message', 'width' => '100'),
//										'打印备注' => array('field' => 'print_remark', 'width' => '100'),
//										'标旗' => array('field' => 'remark_flag', 'width' => '100'),
//										'货品种类数' => array('field' => 'goods_type_count', 'width' => '100'),
//										'货品总量' => array('field' => 'goods_count', 'width' => '100'),
//										'总货款' => array('field' => 'goods_amount', 'width' => '100'),
//										'邮费' => array('field' => 'post_amount', 'width' => '100'),
//										'优惠' => array('field' => 'discount', 'width' => '100'),
//										'应收' => array('field' => 'receivable', 'width' => '100'),
//										'已付' => array('field' => 'paid', 'width' => '100'),
//										'COD金额' => array('field' => 'cod_amount', 'width' => '100'),
//										'买家COD费用' => array('field' => 'ext_cod_fee', 'width' => '100'),
//										'佣金' => array('field' => 'commission', 'width' => '100'),
//										'邮费成本' => array('field' => 'post_cost', 'width' => '100'),
//										'货品估算成本' => array('field' => 'goods_cost', 'width' => '100'),
//										'估重' => array('field' => 'weight', 'width' => '100'),
//										'需要发票' => array('field' => 'invoice_type', 'width' => '100'),
//										'发票抬头' => array('field' => 'invoice_title', 'width' => '100'),
//										'发票内容' => array('field' => 'invoice_content', 'width' => '100'),
//										'下单时间' => array('field' => 'trade_time', 'width' => '100'),
//										'付款时间' => array('field' => 'pay_time', 'width' => '100'),
//										'买家付款账号' => array('field' => 'pay_account', 'width' => '100'),
//										'分销类别' => array('field' => 'fenxiao_type', 'width' => '100'),
//										'送货时间' => array('field' => 'to_deliver_time', 'width' => '100'),
//										'派送站点' => array('field' => 'dist_site', 'width' => '100'),
//										'业务员' => array('field' => 'salesman_id', 'width' => '100'),
//										'签出人' => array('field' => 'checkouter_id', 'width' => '100'),
//										'标记名称' => array('field' => 'flag_id', 'width' => '100'),
//										'类型' => array('field' => 'trade_type', 'width' => '100'),
//										'订单来源' => array('field' => 'trade_from', 'width' => '100'),
//										'递交时间' => array('field' => 'created', 'width' => '100'),
//										'id' => array('field' => 'id', 'hidden' => true)
//								),
//									'salesrefund'=>array(
//										'退换单号' => array('field' => 'refund_no', 'width' => '100'),
//										'店铺' => array('field' => 'shop_id', 'width' => '100'),
//										'类型' => array('field' => 'type', 'width' => '100'),
//										'建单者' => array('field' => 'operator_id', 'width' => '100'),
//										'平台退款单号' => array('field' => 'src_no', 'width' => '100'),
//										'处理状态' => array('field' => 'process_status', 'width' => '100'),
//										'平台状态' => array('field' => 'status', 'width' => '100'),
//										'退货仓库' => array('field' => 'warehouse_id', 'width' => '100'),
//										'仓库类型' => array('field' => 'warehouse_type', 'width' => '100'),
//										'推送状态' => array('field' => 'wms_status', 'width' => '100'),
//										'推送信息' => array('field' => 'wms_result', 'width' => '100'),
//										'外部单号' => array('field' => 'outer_no', 'width' => '100'),
//										'原始订单' => array('field' => 'tid', 'width' => '100'),
//										'系统订单' => array('field' => 'sales_tid', 'width' => '100'),
//										'客户网名' => array('field' => 'buyer_nick', 'width' => '100'),
//										'姓名' => array('field' => 'receiver_name', 'width' => '100'),
//										'手机号' => array('field' => 'return_mobile', 'width' => '100'),
//										'固话' => array('field' => 'return_telno', 'width' => '100'),
//										'地址' => array('field' => 'receiver_address', 'width' => '100'),
//										'支付帐号' => array('field' => 'pay_account', 'width' => '100'),
//										'退货货品数量' => array('field' => 'return_goods_count', 'width' => '100'),
//										'退货金额' => array('field' => 'goods_amount', 'width' => '100'),
//										'邮费' => array('field' => 'post_amount', 'width' => '100'),
//										'退款金额' => array('field' => 'refund_amount', 'width' => '100'),
//										'平台退款金额' => array('field' => 'guarante_refund_amount', 'width' => '100'),
//										'线下退款金额' => array('field' => 'direct_refund_amount', 'width' => '100'),
//										'换货金额' => array('field' => 'exchange_amount', 'width' => '100'),
//										'物流公司' => array('field' => 'logistics_name', 'width' => '100'),
//										'物流单号' => array('field' => 'logistics_no', 'width' => '100'),
//										'退回地址' => array('field' => 'return_address', 'width' => '100'),
//										'建单时间' => array('field' => 'created', 'width' => '100'),
//										'退款时间' => array('field' => 'refund_time', 'width' => '100'),
//										'建单方式' => array('field' => 'from_type', 'width' => '100'),
//										'退款原因' => array('field' => 'reason_id', 'width' => '100'),
//										'同步结果' => array('field' => 'sync_result', 'width' => '100'),
//										'备注' => array('field' => 'remark', 'width' => '100'),
//										'id' => array('field' => 'id', 'hidden' => true)
//									),



								'stockout_add_waybill'=>array(
									'订单编号'=>array('field'=>'src_order_no','width'=>'120'),
									'店铺'=>array('field'=>'shop_name','width'=>'100',),
									'收货人'=>array('field'=>'receiver_name','width'=>'100',),
									'物流单号'=>array('field'=>'logistics_no','width'=>'100','editor' => '{type:"text"}'),
									'id'=>array('field'=>'id','hidden'=>true,),
									'index'=>array('field'=>'index','hidden'=>true,)
								),
		);

}