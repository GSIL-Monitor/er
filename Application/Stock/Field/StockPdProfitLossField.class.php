<?php
namespace Stock\Field;

use Common\Common\Field;

class StockPdProfitLossField extends Field
{
   protected function get($key){
   	$fields = array(
   	    'pd_profit_loss' => array(
   	    	'id'=>array('field'=>'id','hidden'=>true),
            '盘点单号' =>array('field' => 'pd_no','width' => '100','sortable' => true),
   	    	'仓库' => array('field' => 'name','width' => '100','sortable' => true),
   	    	'货位' => array('field' => 'position_no','width' => '100'),
   	    	'商家编码' => array('field' => 'spec_no','width' => '100'),
            '货品编号' => array('field' => 'goods_no', 'width' => '100'),
            '货品名称' => array('field' => 'goods_name', 'width' => '150'),
            '规格码' => array('field' => 'spec_code', 'width' => '100'),
            '规格名称' => array('field' => 'spec_name', 'width' => '100'),
            '原始库存' => array('field' => 'old_num','width' => '100','sortable' => true),
            '盘点数量' => array('field' => 'new_num','width' => '100','sortable' => true),
            '盈余数量' => array('field' => 'yk_num','width' => '100','sortable' => true),
            '盈余金额' => array('field' => 'total_price','width' => '100','sortable' => true),
            '盘点人' => array('field' => 'fullname','width'=>'100'),
            '备注' => array('field' => 'remark', 'width' => '100'),
            '分类' => array('field' => 'class_id','hidden'=>true),
            '品牌' => array('field' => 'brand_id','hidden'=>true)
   	    	),
            // 'stockpddetailspecifics' => array(
            //    'id'=>array('field'=>'pd_id','hidden'=>true),
            //    '商家编码'=>array('field'=>'spec_no','width'=>'100'),
            //    '条码'=>array('field' => 'barcode','width'=>'100'),
            //    '货品编号'=>array('field'=>'goods_no','width'=>'100'),
            //    '货品名称'=>array('field'=>'goods_name','width'=>'150'),
            //    '规格码'=>array('field'=>'spec_code','width'=>'100'),
            //    '规格名称'=>array('field'=>'spec_name','width'=>'100'),
            //    '货位' => array('field'=>'position_no','witdh'=>'100'),
            //    '原始库存' => array('field'=>'old_name','width'=>'100'),
            //    '盘点数量' => array('field'=>'new_num','width'=>'100'),
            //    '盈余数量' => array('field'=>'yk_num','width'=>'100'),
            //    '盈余金额'=>array('field'=>'total_price','width'=>'100'),
            //    '备注'=>array('field'=>'remark','width'=>'100'),
            // ),
   		);
   	return $fields[$key];
   }
}