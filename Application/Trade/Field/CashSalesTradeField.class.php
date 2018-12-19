<?php
namespace Trade\Field;

use Common\Common\Field;
class CashSalesTradeField extends Field{

	protected function get($key){
		$point_number = get_config_value('point_number',0);
		$fields = array(
				'trade_manual'=>array(//sales_trade_order_copy_new_trade    33458-cmd.txt
					'id'=> array('field'=>'id','hidden'=>true),
					'checkbox'=> array('field'=>'ck','checkbox'=>true,'frozen'=>true),
					'商家编码'=> array('field'=>'spec_no','width'=>120),
					'货品编码'=> array('field'=>'goods_no','width'=>120),
					'货品名称'=> array('field'=>'goods_name','width'=>100),
					'规格名称'=> array('field'=>'spec_name','width'=>100),
					'规格码'=> array('field'=>'spec_code','width'=>100),
					'数量(可编辑)'=> array('field'=>'num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:'.$point_number.'}}'),
					'单位'=> array('field'=>'unit_name','width'=>30),
					'原价(可编辑)'=> array('field'=>'original_price','width'=>120,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
					'折扣(可编辑)'=> array('field'=>'discount','width'=>80,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
					'折后价(可编辑)'=> array('field'=>'real_price','width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
					'总计'=> array('field'=>'total_price','width'=>60),
					'组合装'=> array('field'=>'is_suite','width'=>40,'formatter'=>'formatter.boolen'),
					'赠品类型'=> array('field'=>'gift_type','width'=>80,'formatter'=>'formatter.gift_type'),
					'备注(可编辑)' => array('field'=>'cs_remark','width'=>150,'methods'=>'editor:{type:"textbox"}'),
				),
				'choose_goods_list'=>array(
					'商家编码'=>array('field'=>'spec_no','width'=>'100'),
					'货品编码'=>array('field'=>'goods_no','width'=>'100'),
					'货品名称'=>array('field'=>'goods_name','width'=>'100'),
					'规格名称'=>array('field'=>'spec_name','width'=>'100'),
					'规格码'=>array('field'=>'spec_code','width'=>'100'),
					'是否组合装'=>array('field'=>'is_suite','width'=>'100','align'=>'center',"formatter" => "formatter.boolen"),
				)
		);
		return $fields[$key];
	}
}