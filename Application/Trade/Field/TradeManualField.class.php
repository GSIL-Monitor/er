<?php
namespace Trade\Field;

use Common\Common\Field;
class TradeManualField extends Field{

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
						'数量(可编辑)'=> array('field'=>'num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
						'原价(可编辑)'=> array('field'=>'original_price','width'=>120,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
						'重量'=> array('field'=>'weight','width'=>60),
						'单位'=> array('field'=>'unit_name','width'=>30),
						//'辅助数量(可编辑)'=> array('field'=>'num2','width'=>100,'methods'=>'editor:{type:"numberbox",options:{precision:4}}'),
						//'辅助单位'=> array('field'=>'aux_unit_id','width'=>60),
						//'换算系数'=> array('field'=>'unit_ratio','width'=>60),
						'折扣(可编辑)'=> array('field'=>'discount','width'=>80,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
						'折后价(可编辑)'=> array('field'=>'real_price','width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4}}'),
						'最低价'=> array('field'=>'lowest_price','width'=>80,'hidden'=>true),
						'零售价'=> array('field'=>'retail_price','width'=>80,'hidden'=>true),
						'市场价'=> array('field'=>'market_price','width'=>80,'hidden'=>true),
						'总计'=> array('field'=>'total_price','width'=>60),
						'组合'=> array('field'=>'is_suite','width'=>40,'formatter'=>'formatter.boolen'),
						'赠品类型'=> array('field'=>'gift_type','width'=>80,'formatter'=>'formatter.gift_type'),
						'备注(可编辑)' => array('field'=>'cs_remark','width'=>150,'methods'=>'editor:{type:"textbox"}'),
						'可订购量' => array('field'=>'orderable_num','width'=>100),
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