<?php
namespace Setting\Field;

use Common\Common\Field;

class RemarkExtractField extends Field{
	protected function get($key) {
		$fields=array(
				'remark'=>array(
						'id'=>array('field'=>'id','hidden'=>true),
						'关键词'=>array('field'=>'keyword','sortable'=>true,'width'=>'100'),
						'说明'=>array('field'=>'remark','sortable'=>true,'width'=>'350'),
						'停用'=>array('field'=>'is_disabled','sortable'=>true,'width'=>'60','formatter' => 'formatter.boolen'),
						'修改时间'=>array('field'=>'modified','sortable'=>true,'width'=>'120'),
						'创建时间'=>array('field'=>'created','sortable'=>true,'width'=>'120'),
				),
		);
		return $fields[$key];
	}
}