<?php
namespace Trade\Field;

use Common\Common\Field;
class RefundManageField extends Field{

	protected function get($key){
		$point_number = get_config_value('point_number',0);
		$fields = array(
				'refund_manage'=>array(
						'id'     => array('field'=>'id','hidden'=>true,'sortable'=>true),
						'flag_id'=> array('field'=>'flag_id','hidden'=>true,'sortable'=>true),
						'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
						'退换单号'  => array('field'=>'refund_no','width'=>100,'sortable'=>true),
						'店铺名称'  => array('field'=>'shop_id','width'=>100,'sortable'=>true),
						'类型'    => array('field'=>'type','width'=>100,'sortable'=>true,'formatter'=>'formatter.refund_type'),
						'建单者'   => array('field'=>'operator_id','width'=>100,'sortable'=>true),
						'平台退款单号'  => array('field'=>'src_no','width'=>100,'sortable'=>true),
						'处理状态'  => array('field'=>'process_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.refund_process_status'),
						'平台状态'  => array('field'=>'status','width'=>100,'sortable'=>true,'formatter'=>'formatter.api_refund_status'),
						'退货仓库'  => array('field'=>'warehouse_id','width'=>100,'sortable'=>true),
						'仓库类型'  => array('field'=>'warehouse_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.warehouse_type'),
						'外部WMS单号' => array('field' => 'outer_no', 'width' => '150','align' => 'center'),
						'WMS错误信息' => array('field' => 'wms_result', 'width' => '150','align' => 'center'),
						'预入库单号'  => array('field'=>'stockin_pre_no','width'=>120,'sortable'=>true),
						//'推送状态'  => array('field'=>'wms_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.wms_status'),
						//'推送信息'  => array('field'=>'wms_result','width'=>100,'sortable'=>true),
						//'外部编号'  => array('field'=>'outer_no','width'=>100,'sortable'=>true),
						'原始订单'  => array('field'=>'tid','width'=>120,'sortable'=>true),
						'系统单号'  => array('field'=>'trade_no','width'=>120,'sortable'=>true),
						'客户网名'  => array('field'=>'buyer_nick','width'=>100,'sortable'=>true),
						'收件人'   => array('field'=>'receiver_name','width'=>100,'sortable'=>true),
						'地区'     => array('field'=>'swap_area','width'=>150,'sortable'=>true),
						'手机'    => array('field'=>'return_mobile','width'=>100,'sortable'=>true),
						'固话'    => array('field'=>'return_telno','width'=>100,'sortable'=>true),
						'地址'     => array('field'=>'receiver_address','width'=>150,'sortable'=>true),
						'支付账户'  => array('field'=>'pay_account','width'=>100,'sortable'=>true),
						//'退货货品数量'  => array('field'=>'return_goods_count','width'=>100,'sortable'=>true),
						'退货金额'  => array('field'=>'goods_amount','width'=>100,'sortable'=>true),
						'邮费'  => array('field'=>'post_amount','width'=>100,'sortable'=>true),
						'退款金额'  => array('field'=>'refund_amount','width'=>100,'sortable'=>true),
						'平台退款金额'  => array('field'=>'guarante_refund_amount','width'=>100,'sortable'=>true),
						'线下退款金额'  => array('field'=>'direct_refund_amount','width'=>100,'sortable'=>true),
						'换货金额'  => array('field'=>'exchange_amount','width'=>100,'sortable'=>true),
						'物流公司'  => array('field'=>'logistics_name','width'=>100,'sortable'=>true),
						'物流单号'  => array('field'=>'logistics_no','width'=>100,'sortable'=>true),
						'退回地址'  => array('field'=>'return_address','width'=>100,'sortable'=>true),
						'建单时间'  => array('field'=>'created','width'=>130,'sortable'=>true),
						'退款时间'  => array('field'=>'refund_time','width'=>130,'sortable'=>true),
						'建单方式'  => array('field'=>'from_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_from'),
						'退款原因'  => array('field'=>'reason_id','width'=>100,'sortable'=>true),
						'驳回原因'  => array('field'=>'revert_reason','width'=>100,'sortable'=>true),
						//'同步结果'  => array('field'=>'sync_result','width'=>100,'sortable'=>true),
						'备注'  => array('field'=>'remark','width'=>100,'sortable'=>true),
				),
				'refund_order'=>array(//退回货品
						'id'=> array('field'=>'id','hidden'=>true,'sortable'=>true),
						'平台'=>array('field'=>'platform_id','width'=>100,'formatter'=>'formatter.platform_id'),
						'商家编码'=>array('field'=>'spec_no','width'=>100),
						'货品编号'=>array('field'=>'goods_no','width'=>100),
						'系统编号'=>array('field'=>'trade_no','width'=>100),
						'原始单号'=>array('field'=>'tid','width'=>100),//src_tid
						'货品名称'=>array('field'=>'goods_name','width'=>100),
						'规格名'=>array('field'=>'spec_name','width'=>100),
						'原价'=>array('field'=>'original_price','width'=>100),
						'优惠'=>array('field'=>'discount','width'=>100),
						'已付金额'=>array('field'=>'paid','width'=>100),
						'价格'=>array('field'=>'share_price','width'=>100),
						'数量'=>array('field'=>'order_num','width'=>100),
						'退款数量(可编辑)'=>array('field'=>'refund_num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:'.$point_number.'}}'),
						'备注(可编辑)'=>array('field'=>'remark','width'=>100,'methods'=>'editor:{type:"textbox"}'),
				),
				'return_order'=>array(//换出货品
						'id'=> array('field'=>'id','hidden'=>true,'sortable'=>true),
						'商家编码'=>array('field'=>'merchant_no','width'=>100),
						'货品编号'=>array('field'=>'goods_no','width'=>100),
						'组合装'=>array('field'=>'is_suite','width'=>60,'formatter'=>'formatter.boolen'),
						'货品名称'=>array('field'=>'goods_name','width'=>100),
						'规格名'=>array('field'=>'spec_name','width'=>100),
						'售价(可编辑)'=>array('field'=>'retail_price','width'=>80,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
						'数量(可编辑)'=>array('field'=>'num','width'=>80,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:'.$point_number.'}}'),
						'备注(可编辑)'=>array('field'=>'remark','width'=>100,'methods'=>'editor:{type:"textbox"}'),
				),
				'order'=>array(
						'id'=> array('field'=>'id','hidden'=>true,'sortable'=>true),
						'平台'=>array('field'=>'platform_id','width'=>60,'formatter'=>'formatter.platform_id'),
						'货品名称'=>array('field'=>'goods_name','width'=>150),
						'规格名'=>array('field'=>'spec_name','width'=>100),//,'methods'=>'editor:{type:"textbox"}'
						'商家编号'=>array('field'=>'spec_no','width'=>100),
						'货品编号'=>array('field'=>'goods_no','width'=>100),
						'退款数量'=>array('field'=>'refund_num','width'=>100),
						'系统编号'=>array('field'=>'trade_no','width'=>100),
						'原始单号'=>array('field'=>'tid','width'=>100),//src_tid
						'原价'=>array('field'=>'original_price','width'=>100),
						'优惠'=>array('field'=>'discount','width'=>100),
						'已付金额'=>array('field'=>'paid','width'=>100),
						'价格'=>array('field'=>'share_price','width'=>100),
						'数量'=>array('field'=>'order_num','width'=>100)						
				),
				'exchange'=>array(
		                'id' => array('field' => 'id', 'hidden'=>'true'),
		                '商家编码' => array('field' => 'spec_no',   'width'=>130),
		                '货品名称' => array('field' => 'goods_name',  'width'=>150),
		                '规格名称' => array('field' => 'spec_name',   'width'=>150),
		                '价格'=>array('field'=>'price','width'=>100),
		                '退款数量(可编辑)' => array('field' => 'num',   'width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,min:1}}'),
	            ),

		);
		return $fields[$key];
	}
}