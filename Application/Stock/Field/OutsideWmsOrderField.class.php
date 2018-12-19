<?php 

namespace Stock\Field;
use Common\Common\Field;

class OutsideWmsOrderField  extends Field{
	protected function get($key){
		$number = get_config_value('point_number',0);
		$fields = array(
			'outsidewmsorder'=>array(
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编码'=> array('field' => 'goods_no', 'width'=>'100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '品牌id' => array('field' => 'brand_id', 'width' => '100','hidden'=>true),
                '品牌' => array('field' => 'brand_name', 'width' => '100'),
                '库存数量' => array('field' => 'stock_num', 'width' => '100'),
                '开单数量(可编辑)' => array('field' => 'num', 'width' => '150','editor'=>'{type:"numberbox",options:{required:true,precision:'.$number.',value:0,min:0}}'),// 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
               // '原价(可编辑)' => array('field' => 'src_price', 'width' => 'auto','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),//, 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '成本价(可编辑)' => array('field' => 'cost_price', 'width' => '150','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),//, 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '总金额' =>array('field' => 'amount', 'width' => '100'),
				'货位id' => array('field' => 'position_id', 'width' => '100','hidden'=>true),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '货位(可编辑)' => array('field' => 'position_no', 'width' => '140','editor'=>'{type:"textbox",options:{buttonText:"...",editable:false}}'),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '单位' => array('field' => 'unit_name', 'width' => '100'),
               // '零售价' => array('field' => 'retail_price', 'width' => '80',),//'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                //'最低价' => array('field' => 'lowest_price', 'width' => '80', 'hidden' => false),
                //'市场价' => array('field' => 'market_price', 'width' => '80', 'hidden' => false),
                '单位id' => array('field' => 'base_unit_id', 'width' => '80','hidden'=>true),
                'id' => array('field' => 'id', 'hidden' => true)
			),
			
			'outsidewmsmanage'=>array(
				 '委外单号' => array('field' => 'order_no', 'width' => '120', 'align' => 'center'),
                '单据类型' => array('field' => 'order_type', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.outside_wms_type',),
                '外部单号' => array('field' => 'outer_no', 'width' => '150', 'align' => 'center'),
                '仓储单号' => array('field' => 'wms_outer_no', 'width' => '120', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center','formatter'=>'formatter.wms_order_status'),
               // '处理状态' => array('field' => 'wms_status', 'width' => '100', 'align' => 'center','formatter'=>'formatter.outside_wms_status'),
                '推送信息' => array('field' => 'error_info', 'width' => '150', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'name', 'width' => '100', 'align' => 'center'),
                '运输模式' => array('field' => 'transport_mode', 'width' => '100', 'align' => 'center','formatter'=>'formatter.transport_mode'),
                '物流公司' => array('field' => 'logistics_name', 'width' => '100', 'align' => 'center'),
                '物流公司id' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                
				'物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '联系人' => array('field' => 'receiver_name', 'width' => '100', 'align' => 'center'),
                '省市区' => array('field' => 'receiver_area', 'width' => '150', 'align' => 'center'),
               
                '地址' => array('field' => 'receiver_address', 'width' => '150', 'align' => 'center'),
                '联系电话' => array('field' => 'receiver_telno', 'width' => '100', 'align' => 'center'),
                '邮编' => array('field' => 'receiver_zip', 'width' => '100', 'align' => 'center'),
				'制单人' => array('field' => 'fullname', 'width' => '100', 'align' => 'center'),
				'制单人id' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '制单时间' => array('field' => 'created', 'width' => '120', 'align' => 'center'),
				'修改时间' => array('field' => 'modified', 'width' => '120', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
			),
			
			'outsidewmsdetail'=>array(
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编码'=> array('field' => 'goods_no', 'width'=>'100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '品牌id' => array('field' => 'brand_id', 'width' => '100','hidden'=>true),
                '品牌' => array('field' => 'brand_name', 'width' => '100'),
              //  '库存数量' => array('field' => 'stock_num', 'width' => '100'),
                '开单数量' => array('field' => 'num', 'width' => '150'),// 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '以出入库数量'=>array('field' => 'inout_num', 'width' => '100'),
				'成本价' => array('field' => 'price', 'width' => '150'),//, 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
               // '总金额' =>array('field' => 'amount', 'width' => '100'),
				'货位id' => array('field' => 'position_id', 'width' => '100','hidden'=>true),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '货位' => array('field' => 'position_no', 'width' => '140'),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '单位' => array('field' => 'name', 'width' => '100'),
                '单位id' => array('field' => 'base_unit_id', 'width' => '80','hidden'=>true),
                'id' => array('field' => 'id', 'hidden' => true)
			),
			'outsidewmslog'=>array(
				'操作员'=>array('field' => 'fullname', 'width' => '120'),
				'操作'=>array('field' => 'message', 'width' => '500'),
				'时间'=>array('field' => 'created', 'width' => '120'),
			),
			
		);
		 return $fields[$key];
	}
	
}