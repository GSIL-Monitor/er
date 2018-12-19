<?php  
namespace Purchase\Field;
use Common\Common\Field;
use Common\Common\UtilDB;
class SortingWallField extends Field{
	
	protected function get($key){
		$fields = array(
			'sortingwall' =>array(
				'id' 		=> array('field' => 'id', 'hidden' => true),
				'编号'		=>array('field'=>'wall_no','width'=>'150', 'sortable' => true),
				'排数'		=>array('field'=>'row_num','width'=>'150', 'sortable' => true),
				'列数' 		=> array('field' => 'column_num', 'width' => '150', 'sortable' => true),
                '属性' 		=> array('field' => 'type', 'width' => '150', 'sortable' => true),
                '停用' 		=> array('field' => 'is_disabled', 'width' => '150', 'sortable' => true,'formatter' => 'formatter.toYN'),
				'最后修改时间'	=> array('field' => 'modified', 'width' => '200', 'sortable' => true),
				'创建时间'	=> array('field' => 'created', 'width' => '200', 'sortable' => true),
			),
			'dynamic' =>array(
				'id' 		=> array('field' => 'id', 'hidden' => true),
				'编号'		=>array('field'=>'wall_no','width'=>'150', 'sortable' => true),
				'框数'		=>array('field'=>'box_num','width'=>'150', 'sortable' => true),
				'货品数' 		=> array('field' => 'goods_num', 'width' => '150', 'sortable' => true),
                '属性' 		=> array('field' => 'type', 'width' => '150', 'sortable' => true),
                '停用' 		=> array('field' => 'is_disabled', 'width' => '150', 'sortable' => true,'formatter' => 'formatter.toYN'),
				'最后修改时间'	=> array('field' => 'modified', 'width' => '200', 'sortable' => true),
				'创建时间'	=> array('field' => 'created', 'width' => '200', 'sortable' => true),
			),
			'sortingbox' =>array(
				'id' 		=> array('field' => 'id', 'hidden' => true),
				'分拣框编号'		=>array('field'=>'box_no','width'=>'150', 'sortable' => true),
				'订单编号'		=>array('field'=>'trade_no','width'=>'150', 'sortable' => true),
				'出库单编号'		=>array('field'=>'stockout_no','width'=>'150', 'sortable' => true),
				'使用状态' 		=> array('field' => 'use_status', 'width' => '150', 'sortable' => true),
				'所属墙属性' 		=> array('field' => 'wall_type', 'width' => '150', 'sortable' => true),
				'所属墙编号'	=>array('field'=>'wall_no','width'=>'150', 'sortable' => true),
				'最后修改时间'	=> array('field' => 'modified', 'width' => '200', 'sortable' => true),
				'创建时间'	=> array('field' => 'created', 'width' => '200', 'sortable' => true),
			),
			'sorting_box_detail' =>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'订单编号' => array('field' => 'trade_no', 'width' => '120'),
				'出库单编号' => array('field' => 'stockout_no', 'width' => '120'),
				'商家编码' => array('field' => 'spec_no', 'width' => '120'),
				'货品编号' => array('field' => 'goods_no', 'width' => '120'),
				'货品名称' => array('field' => 'goods_name', 'width' => '120'),
				'规格码' => array('field' => 'spec_code', 'width' => '120'),
				'规格名称' => array('field' => 'spec_name', 'width' => '120'),
				'条形码' => array('field' => 'barcode', 'width' => '120'),
				'未分拣数量' => array('field' => 'unsort_num', 'width' => '100'),
				'已分拣数量' => array('field' => 'sort_num', 'width' => '100'),
				'数量' => array('field' => 'num', 'width' => '100'),
//				'组合装名称' => array('field' => 'suite_name', 'width' => '100'),
//				'组合装编码' => array('field' => 'suite_no', 'width' => '100'),
//				'组合装数量' => array('field' => 'suite_num', 'width' => '100'),
			),
			'boxgoodstrans' =>array(
				'id' 		=> array('field' => 'id', 'hidden' => true),
				'订单编号'	=>array('field'=>'trade_no','width'=>'150'),
				'分拣框编号'	=>array('field'=>'box_no','width'=>'150' ),
				'目标框编号(可编辑)'=> array('field' => 'new_box_no', 'width' => '150','editor'=>'{type:"combobox",options:{valueField:"box_no",textField:"box_no",editable:false,validType:"new_box_no_unique"}}'),
			),
		);
		 return $fields[$key];
	}
	
}