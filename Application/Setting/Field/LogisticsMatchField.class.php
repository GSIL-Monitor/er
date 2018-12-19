<?php
namespace Setting\Field;

use Common\Common\Field;
class LogisticsMatchField extends Field{
	protected function get($key){
		$fields=array(
			'logistics_match'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'适用店铺' =>array('field'=>'shop_target_id','sortable'=>true,'width'=>'100'),
				'适用仓库' =>array('field'=>'warehouse_target_id','sortable'=>true,'width'=>'100'),
				'地址'=>array('field'=>'path','sortable'=>true,'width'=>'130','formatter'=>'formatterLogisticsArea'),
				'默认物流公司'=>array('field'=>'logistics_id','sortable'=>true,'width'=>'100'),
				'已付金额等级1'=>array('field'=>'paid_amount','sortable'=>true,'width'=>'100'),
				'物流公司(金额)1'=>array('field'=>'amount_logistics_id','sortable'=>true,'width'=>'100'),
				'已付金额等级2'=>array('field'=>'paid_amount2','sortable'=>true,'width'=>'100'),
				'物流公司(金额)2'=>array('field'=>'amount_logistics_id2','sortable'=>true,'width'=>'100'),
				'已付金额等级3'=>array('field'=>'paid_amount3','sortable'=>true,'width'=>'100'),
				'物流公司(金额)3'=>array('field'=>'amount_logistics_id3','sortable'=>true,'width'=>'100'),
				'重量区间1'=>array('field'=>'weight','sortable'=>true,'width'=>'100'),
				'物流公司(重量)1'=>array('field'=>'weight_logistics_id','sortable'=>true,'width'=>'100'),
				'不到地址(重量)1'=>array('field'=>'except_words_weight','sortable'=>true,'width'=>'100'),
				'重量区间2'=>array('field'=>'weight2','sortable'=>true,'width'=>'100'),
				'物流公司(重量)2'=>array('field'=>'weight_logistics_id2','sortable'=>true,'width'=>'100'),
				'不到地址(重量)2'=>array('field'=>'except_words_weight2','sortable'=>true,'width'=>'100'),
				'重量区间3'=>array('field'=>'weight3','sortable'=>true,'width'=>'100'),
				'物流公司(重量)3'=>array('field'=>'weight_logistics_id3','sortable'=>true,'width'=>'100'),
				'不到地址(重量)3'=>array('field'=>'except_words_weight3','sortable'=>true,'width'=>'100'),
				'一级不到地址'=>array('field'=>'except_words','sortable'=>true,'width'=>'100'),
				'不到改用1'=>array('field'=>'except_logistics_id','sortable'=>true,'width'=>'100'),
				'二级不到地址'=>array('field'=>'except_words2','sortable'=>true,'width'=>'100'),
				'不到改用2'=>array('field'=>'except_logistics_id2','sortable'=>true,'width'=>'100'),
				'修改时间' => array('field' => 'modified',  'sortable' => true,'width'=>'120'),
				'创建时间' => array('field' => 'created',  'sortable' => true,'width'=>'120'),
			),
		);
        return $fields[$key];
	}
}