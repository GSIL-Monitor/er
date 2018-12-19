<?php
namespace Statistics\Field;

use Common\Common\Field;

class StatRefundRateField extends Field{
	protected function get($key){
		$fields=array(
				'stat_refund_rate'=>array(
						'商家编码'=>array('field'=>'spec_no','width'=>100,'sortable'=>true),
						'货品编码'=>array('field'=>'goods_no','width'=>100,'sortable'=>true),
						'货品名称'=>array('field'=>'goods_name','width'=>100,'sortable'=>true),
						'规格码'=>array('field'=>'spec_code','width'=>100,'sortable'=>true),
						'规格名称'=>array('field'=>'spec_name','width'=>100,'sortable'=>true),
						'品牌'=>array('field'=>'brand_id','width'=>100,'sortable'=>true),
						'分类'=>array('field'=>'class_id','width'=>100,'sortable'=>true),
						'下单数量'=>array('field'=>'num','width'=>100,'sortable'=>true),
						'下单金额'=>array('field'=>'amount','width'=>100,'sortable'=>true),
						'退款数量'=>array('field'=>'refund_num','width'=>100,'sortable'=>true),
						'退款金额'=>array('field'=>'refund_amount','width'=>100,'sortable'=>true),
						'退货数量'=>array('field'=>'return_num','width'=>100,'sortable'=>true),
						'退货金额'=>array('field'=>'return_amount','width'=>100,'sortable'=>true),
						'退款率'=>array('field'=>'refund_rate','width'=>100,'sortable'=>true),
						'退货率'=>array('field'=>'return_rate','width'=>100,'sortable'=>true),
				),
		);
		return $fields[$key];
	}
}