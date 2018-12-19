<?php
namespace Statistics\Field;

use Common\Common\Field;

class StatSellbackDetailField extends Field{
	protected function get($key){
		$fields=array(
				'stat_sellback_detail'=>array(
						'商家编码'=>array('field'=>'spec_no','width'=>100,'sortable'=>true),
						'退换单号'=>array('field'=>'refund_no','width'=>100,'sortable'=>true),
						'退换单备注'=>array('field'=>'remark','width'=>100,'sortable'=>true),
						'类型'=>array('field'=>'type','width'=>80,'sortable'=>true,'formatter'=>'formatter.refund_type'),
						'退换原因'=>array('field'=>'reason_id','width'=>100,'sortable'=>true),
						'处理状态'=>array('field'=>'process_status','width'=>80,'sortable'=>true,'formatter'=>'formatter.refund_process_status'),
						'标记'=>array('field'=>'flag_id','width'=>50,'sortable'=>true,'formatter'=>'formatter.sales_refund_flag'),
						'品牌'=>array('field'=>'brand_id','width'=>50,'sortable'=>true),
						'分类'=>array('field'=>'class_id','width'=>50,'sortable'=>true),
						'货品名称'=>array('field'=>'goods_name','width'=>100,'sortable'=>true),
						'货品编码'=>array('field'=>'goods_no','width'=>100,'sortable'=>true),
						'规格码'=>array('field'=>'spec_code','width'=>100,'sortable'=>true),
						'规格名称'=>array('field'=>'spec_name','width'=>100,'sortable'=>true),
						'退货详情备注'=>array('field'=>'goods_remark','width'=>100,'sortable'=>true),
						'组合装'=>array('field'=>'suite','width'=>100,'sortable'=>true),
						'登记数量'=>array('field'=>'refund_num','width'=>100,'sortable'=>true),
						'入库数量'=>array('field'=>'stockin_num','width'=>100,'sortable'=>true),
						'货品成本'=>array('field'=>'cost_price','width'=>100,'sortable'=>true),
						'单价'=>array('field'=>'price','width'=>100,'sortable'=>true),
						'优惠'=>array('field'=>'discount','width'=>100,'sortable'=>true),
						'入库总额'=>array('field'=>'stockin_amount','width'=>100,'sortable'=>true),
						'登记时间'=>array('field'=>'created','width'=>130,'sortable'=>true),
						'店铺'=>array('field'=>'shop_id','width'=>100,'sortable'=>true),
						'业务员'=>array('field'=>'salesman_id','width'=>100,'sortable'=>true),
						'客户网名'=>array('field'=>'buyer_nick','width'=>100,'sortable'=>true),
						'订单编号'=>array('field'=>'sales_tid','width'=>100,'sortable'=>true),
						'物流公司'=>array('field'=>'logistics_name','width'=>100,'sortable'=>true),
						'原始单号'=>array('field'=>'tid','width'=>100,'sortable'=>true),
						'仓库'=>array('field'=>'warehouse_name','width'=>100,'sortable'=>true),
						'退货货品金额'=>array('field'=>'total_amount','width'=>100,'sortable'=>true),
						'平台退款金额'=>array('field'=>'guarante_refund_amount','width'=>100,'sortable'=>true),
						'线下退款金额'=>array('field'=>'direct_refund_amount','width'=>100,'sortable'=>true),
						'退款总额'=>array('field'=>'refund_amount','width'=>100,'sortable'=>true),
						'发货仓库'=>array('field'=>'send_warehouse_id','width'=>100,'sortable'=>true),
						'发货物流'=>array('field'=>'send_logistics_id','width'=>100,'sortable'=>true),
						'物流单号'=>array('field'=>'logistics_no','width'=>100,'sortable'=>true),
						'地址'=>array('field'=>'receiver_address','width'=>100,'sortable'=>true),
						'发货时间'=>array('field'=>'send_time','width'=>130,'sortable'=>true),
				),
		);
		return $fields[$key];
	}
}