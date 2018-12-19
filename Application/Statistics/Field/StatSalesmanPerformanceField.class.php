<?php
namespace Statistics\Field;

use Common\Common\Field;

class StatSalesmanPerformanceField extends Field{
	protected function get($key){
		$fields=array(
				'stat_salesman_performance'=>array(
						'业务员'=>array('field'=>'salesman_id','width'=>100),
						'日期'=>array('field'=>'sales_date','width'=>100,'hidden'=>true),
						'店铺'=>array('field'=>'shop_id','width'=>100,'hidden'=>true),
						'订单总量'=>array('field'=>'trade_count','width'=>100),
						'订单应收总额'=>array('field'=>'total_receivable','width'=>100),
						'订单应收(扣除退款)'=>array('field'=>'trade_total','width'=>120),
						'预估总利润'=>array('field'=>'total_profit','width'=>100),
						'邮费总额'=>array('field'=>'total_post_amount','width'=>100),
						'售后退款总额'=>array('field'=>'total_refund_price','width'=>100),
						'客单价'=>array('field'=>'trade_avg','width'=>100),
						'货品总量'=>array('field'=>'total_goods_count','width'=>100),
						'货品种类'=>array('field'=>'total_goods_type_count','width'=>100),
				),
		);
		return $fields[$key];
	}
}