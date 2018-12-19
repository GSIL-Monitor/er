<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:53
 */
namespace Stock\Field;

use Common\Common\Field;

class StockInventoryField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'stockinventory' => array(
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '货位id' => array('field' => 'position_id', 'width' => '100','hidden'=>true),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '货位' => array('field' => 'position_no', 'width' => '100'),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                 '库存数量'=>array('field'=>'stock_num','width'=>'100','align'=>'center'),
				 
				'辅助量' => array('field' => 'num2', 'width' => '100', 'hidden' => true, 'editor' => '{type:"numberbox",options:{precision:2}}'),
//                 '辅助单价' => array('field' => 'cost_price2', 'hidden' => true, 'width' => '100'),
                '成本价' =>array('field' => 'cost_price', 'width' => '100', 'hidden' =>true),
				 '制单人' =>array('field'=> 'operator_id', 'width'=> '100','hidden' =>true),             
				'换算系数' => array('field' => 'unit_ratio', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2}}', 'hidden' => true),
				'辅助单位' => array('field' => 'unit_id', 'width' => '100', 'hidden' => true),
 //                 '单位邮费分摊' => array('field' => 'share_post_cost', 'width' => '100', 'hidden' => true),
//                 '邮费总分摊' => array('field' => 'share_post_total', 'width' => '100', 'hidden' => true),
                
				//'有效期' => array('field' => 'expire_date', 'width' => '100'),
				//'批次' => array('field' => 'batch_id', 'width' => '100'),
				'实际盘点量(可编辑)'=>array('field'=>'pd_num','width'=>'150','editor'=>'{type:"numberbox",options:{required:true,precision:'.get_config_value('point_number',0).',value:0,min:0}}'),
				'盈亏量' => array('field'=>'pl_num','width'=>'100'),
				//'备注(可编辑)'=>array('field'=>'remark','width'=>'100','methods'=>'editor:{type:"textbox"}'),
				'基本单位'=>array('field'=>'unit_name','width'=>'100'),
				'备注(可编辑)'=>array('field'=>'remark','width'=>'100'),
				
				'id' => array('field' => 'id', 'hidden' => true)
            ),
			'stockinventorymanagement'=>array(
                '盘点单号' => array('field' => 'pd_no', 'width' => '150', 'align' => 'center'),
                '盘点人' => array('field' => 'creator_name', 'width' => '100', 'align' => 'center'),
				'仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
				'盘点方案' => array('field' => 'mode', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockpd_type',),
				'盘点状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockpd_status',),
                '备注' => array('field' => 'remark', 'width' => '200', 'align' => 'center'),
                '修改时间' => array('field' => 'modified', 'width' => '200', 'align' => 'center'),
                '创建时间' => array('field' => 'created', 'width' => '200', 'align' => 'center'),
                'id' => array('field' => 'rec_id', 'hidden' => true,),
            ),
            'stockpddetail'=>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '150'),
                '货位' => array('field' => 'position_no', 'width' => '150'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '库存数量' => array('field' => 'old_num', 'width' => '100'),
                '盘点数量' => array('field' => 'new_num', 'width' => '100'),
                '盈亏数量' => array('field' => 'pd_num', 'width' => '100'),
                '备注' => array('field' => 'remark', 'width' => '200'),
            ),
			'stockpdexport' => array(
				'盘点单号' => array('field' => 'pd_no', 'width' => '150', 'align' => 'center'),
                '盘点人' => array('field' => 'creator_name', 'width' => '100', 'align' => 'center'),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
				'盘点方案' => array('field' => 'mode', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockpd_type',),
				'盘点状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockpd_status',),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '150'),
                '货位' => array('field' => 'position_no', 'width' => '150'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '库存数量' => array('field' => 'old_num', 'width' => '100'),
                '盘点数量' => array('field' => 'new_num', 'width' => '100'),
                '盈亏数量' => array('field' => 'pd_num', 'width' => '100'),
				'备注' => array('field' => 'remark', 'width' => '200', 'align' => 'center'),
                '修改时间' => array('field' => 'modified', 'width' => '200', 'align' => 'center'),
                '创建时间' => array('field' => 'created', 'width' => '200', 'align' => 'center'),
			
			),
			
            
        );
        return $fields[$key];
    }
}