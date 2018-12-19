<?php
namespace Statistics\Field;

use Common\Common\Field;
class StatSellbackAnalysisField extends Field{
	
	protected function get($key){
		$fields=array(
				'stat_sellback_analysis'=>array(
						'店铺'=>array('field'=>'shop_id','width'=>150,'sortable'=>true),
						'业务员'=>array('field'=>'operator_id','width'=>150,'sortable'=>true,'hidden'=>true),
						'退换原因'=>array('field'=>'reason_id','width'=>150,'sortable'=>true,'hidden'=>true),
						'退换次数'=>array('field'=>'refund_count','width'=>150,'sortable'=>true),
						'退货数量'=>array('field'=>'refund_num','width'=>150,'sortable'=>true),
						'退款不退货金额'=>array('field'=>'refund_amount','width'=>150,'sortable'=>true),
						'退货金额'=>array('field'=>'return_amount','width'=>150,'sortable'=>true),
						'退货成本'=>array('field'=>'return_cost','width'=>150,'sortable'=>true),
						'入库数量'=>array('field'=>'stockin_num','width'=>150,'sortable'=>true),
				)
		);
		return $fields[$key];
	}
}