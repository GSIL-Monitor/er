<?php
namespace Trade\Field;
use Common\Common\Field;
class FinancialCheckField extends Field{

    protected function get($key){
    	$fields = array(
    			'financial_check'=> array(
    					'id'     => array('field'=>'id','hidden'=>true,'sortable'=>true),
    					'flag_id'=> array('field'=>'flag_id','hidden'=>true,'sortable'=>true),
						'checkbox'=>array('field'=>'ck','checkbox'=>true,'frozen'=>true),
    					'订单号'   => array('field'=>'trade_no','width'=>100,'sortable'=>true),
    					'店铺名称'  => array('field'=>'shop_id','width'=>100,'sortable'=>true),
    					'原始单号'  => array('field'=>'src_tids','width'=>100,'sortable'=>true),
    					'客户网名'   => array('field'=>'buyer_nick','width'=>100,'sortable'=>true),
    					'收件人'  => array('field'=>'receiver_name','width'=>100,'sortable'=>true),
    					'地区'  => array('field'=>'receiver_area','width'=>150,'sortable'=>true),
    					'地址'  => array('field'=>'receiver_address','width'=>150,'sortable'=>true),
    					'手机'  => array('field'=>'receiver_mobile','width'=>100,'sortable'=>true),
    					'固话'  => array('field'=>'receiver_telno','width'=>100,'sortable'=>true),
    					'邮编'  => array('field'=>'receiver_zip','width'=>100,'sortable'=>true),
    					'发货条件'  => array('field'=>'delivery_term','width'=>100,'sortable'=>true,'formatter'=>'formatter.delivery_term'),
    					'退货状态'  => array('field'=>'refund_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.refund_status'),
    					'仓库'  => array('field'=>'warehouse_name','width'=>100,'sortable'=>true),
    					'物流公司'  => array('field'=>'logistics_id','width'=>100,'sortable'=>true),
    					'客服备注'  => array('field'=>'cs_remark','width'=>100,'sortable'=>true),
    					'买家留言'  => array('field'=>'buyer_message','width'=>100,'sortable'=>true),
    					'打印备注'  => array('field'=>'print_remark','width'=>100,'sortable'=>true),
                        '货品种类数'  => array('field'=>'goods_type_count','width'=>100,'sortable'=>true),
    					'货品总量'  => array('field'=>'goods_count','width'=>100,'sortable'=>true),
    					'总货款'  => array('field'=>'goods_amount','width'=>100,'sortable'=>true),
    					'邮费'  => array('field'=>'post_amount','width'=>100,'sortable'=>true),
    					'优惠'  => array('field'=>'discount','width'=>100,'sortable'=>true),
    					'应收'  => array('field'=>'receivable','width'=>100,'sortable'=>true),
    					'已付'  => array('field'=>'paid','width'=>100,'sortable'=>true),
    					'货到付款金额'  => array('field'=>'dap_amount','width'=>100,'sortable'=>true),
    					'买家COD费用'  => array('field'=>'cod_amount','width'=>100,'sortable'=>true),
    					'佣金' => array('field'=>'commission','width'=>100,'sortable'=>true),
                        '预估邮资成本'  => array('field'=>'post_cost','width'=>100,'sortable'=>true),
    					'货品估算成本'  => array('field'=>'goods_cost','width'=>100,'sortable'=>true),
    					'估重'  => array('field'=>'weight','width'=>100,'sortable'=>true),
    					'需要发票'  => array('field'=>'invoice_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.invoice_type'),
    					'发票抬头'  => array('field'=>'invoice_title','width'=>100,'sortable'=>true),
    					'发票内容'  => array('field'=>'invoice_content','width'=>100,'sortable'=>true),
    					'下单时间'  => array('field'=>'trade_time','width'=>100,'sortable'=>true),
    					'付款时间'  => array('field'=>'pay_time','width'=>100,'sortable'=>true),
    					'支付账户'  => array('field'=>'pay_account','width'=>100,'sortable'=>true),
    					'送货时间'  => array('field'=>'to_deliver_time','width'=>100,'sortable'=>true),
    					'业务员'  => array('field'=>'salesman_id','width'=>100,'sortable'=>true),
    					'标记名称'  => array('field'=>'flag_name','width'=>100,'sortable'=>true),
                        '类型'  => array('field'=>'trade_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_type'),
    					'处理天数'  => array('field'=>'handle_days','width'=>100,'sortable'=>true),
                        '订单来源'  => array('field'=>'trade_from','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_from'),
    					'原始货品种类'  => array('field'=>'raw_goods_type_count','width'=>100,'sortable'=>true),
    					'原始货品数量'  => array('field'=>'raw_goods_count','width'=>100,'sortable'=>true),
                        '冻结原因'  => array('field'=>'freeze_info','width'=>100,),
                        '拦截原因' => array('field'=>'block_reason','width'=>100,'formatter'=>'formatter.stockout_block_reason'),
                        '递交时间'  => array('field'=>'created','width'=>100,'sortable'=>true),
                        '预估毛利' => array('field'=>'profit','width'=>100,'sortable'=>true)
    			),
    	);
    	return $fields[$key];
    }
}