<?php
namespace Help\Field;
use Common\Common\Field;
class SystemLogField extends Field{
	protected function get($key){
		$fields = array(
			'system_other_log' => array(
				'id'       => array('field'=>'id','hidden'=>true,'sortable'=>true),
				'操作类型' => array('field'=>'type','width'=>150,'sortable'=>true,'formatter'=>'formatter.sys_other_log_type'),
				'操作记录' => array('field'=>'message','width'=>300,'sortable'=>true),
				'操作人员' => array('field'=>'fullname','width'=>150,'sortable'=>true),
				'操作时间' => array('field'=>'created','width'=>150,'sortable'=>true),
			),
		);
		return $fields[$key];

	}

}