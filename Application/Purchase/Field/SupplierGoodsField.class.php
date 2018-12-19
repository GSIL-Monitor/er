<?php  
namespace Purchase\Field;
use Common\Common\Field;
use Common\Common\UtilDB;
class SupplierGoodsField extends Field{
	
	protected function get($key){
		$fields = array(
			
			'supplier'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'spec_id'=>array('field'=>'spec_id','hidden'=>true),
				'goods_id'=>array('field'=>'goods_id','hidden'=>true),
				'商家编码' => array('field' => 'spec_no', 'width' => '170','align' => 'center'),
				'供应商' => array('field' => 'provider_name', 'width' => '170','align' => 'center'),
				//'供货商货号'=>array('field'=>'provider_goods_no','width'=>'170','align' => 'center'),
				'采购价' => array('field' => 'price', 'width' => '170','align' => 'center'),
                '货品编号' => array('field' => 'goods_no', 'width' => '200','align' => 'center'),
                '货品名称' => array('field' => 'goods_name', 'width' => '200','align' => 'center'),
				'创建时间' => array('field' => 'created', 'width' => '200','align' => 'center'),
				
			),
			'add'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'spec_id'=>array('field'=>'spec_id','hidden'=>true),
				'goods_id'=>array('field'=>'goods_id','hidden'=>true),
				'商家编码' => array('field' => 'spec_no', 'width' => '160','align' => 'center'),
				//'供应商(可选择)' => array('field' => 'provider_name', 'width' => '150','align' => 'center','editor'=>'{type:"combobox",options:{valueField:"name",textField:"name",data:supplier_data,required: true,checkbox: true,editable: false}}'),
				//'供货商货号(可编辑)'=>array('field'=>'provider_goods_no','width'=>'180','align' => 'center','editor'=>'{type:"text",options:{required:false,value:""}}'),
				'采购价(可编辑)' => array('field' => 'market_price', 'width' => '150','align' => 'center','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),
				 '规格名称' => array('field' => 'spec_name', 'width' => '200','align' => 'center'),
				 '品牌id'=>array('field'=>'brand_id','width'=>'150','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'150','align' => 'center'),
                '货品编号' => array('field' => 'goods_no', 'width' => '160','align' => 'center'),
                '货品名称' => array('field' => 'goods_name', 'width' => '200','align' => 'center'),
				
			),
			
			
		);
		 return $fields[$key];
	}
	
}