<?php
namespace Trade\Field;
use Common\Common\Field;
class SalesTradeLogField extends Field{
	protected function get($key){
		$fields = array(
			'sales_trade_log' => array(
				'id'       => array('field'=>'id','hidden'=>true,'sortable'=>true),
				'订单编号' => array('field'=>'trade_no','width'=>150,'sortable'=>true),
				'操作类型' => array('field'=>'type','width'=>150,'sortable'=>true,'formatter'=>'formatter.type'),
				'操作记录' => array('field'=>'message','width'=>300,'sortable'=>true),
				'操作人'   => array('field'=>'fullname','width'=>150,'sortable'=>true),
				'操作时间' => array('field'=>'created','width'=>150,'sortable'=>true),
			),
		);
		return $fields[$key];

	}

}