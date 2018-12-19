<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:50
 */
namespace Stock\Field;
use Common\Common\Field;

class StockOutField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'stockout_order_detail_fields'=>array(
                'id'=>array('field'=>'id','hidden'=>true),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'100'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '品牌'=>array('field'=>'brand_id','width'=>'100'),
                '平台货品名称'=>array('field'=>'api_goods_name','width'=>'100'),
                '平台规格名称'=>array('field'=>'api_spec_name','width'=>'100'),
                '条形码'=>array('field'=>'barcode','width'=>'100'),
                '单价'=>array('field'=>'price','width'=>'100'),
                '数量'=>array('field'=>'num','width'=>'100'),
                '组合装名称'=>array('field'=>'suite_name','width'=>'100'),
                '组合装编码'=>array('field'=>'suite_no','width'=>'100'),
                '组合装数量'=>array('field'=>'suite_num','width'=>'100'),
                '估重'=>array('field'=>'weight','width'=>'100'),
                '验货方式'=>array('field'=>'scan_type','width'=>'100'),
                '备注'=>array('field'=>'remark','width'=>'100',)),
            'stockoutorder'=>array(
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'货位' => array('field' => 'position_no', 'width' => '100' ),
				'品牌' => array('field' => 'brand_name', 'width' => '100' ),
                '品牌id' => array('field' => 'brand_id', 'width' => '100','hidden'=>true ),
                '单位' => array('field' => 'unit_name', 'width' => '100'),
            '库存数量' => array('field' => 'stock_num', 'width' => '100',),
                '出库数量' => array('field' => 'num', 'width' => '100','editor'=>'{type:"numberbox",options:{required:true,precision:'.get_config_value('point_number',0).',value:0,min:0}}'),
                '单价' => array('field' => 'price', 'width' => '100', ),//'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setPrice}}'
                '总货款' => array('field' => 'total_amount', 'width' => '100', ),
                '单位id' => array('field' => 'base_unit_id', 'width' => '100','hidden'=>true),
                '备注' => array('field' => 'remark', 'width' => '200'),
                'id' => array('field' => 'id', 'hidden' => true)
            ),
            'stockoutmanagement'=>array(
                '出库单号' => array('field' => 'stockout_no', 'width' => '100', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockout_status',),
                '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockout_type',),
				'源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
			    '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center'),
                '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center'),
                '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
                '邮资' => array('field' => 'post_cost', 'width' => '100', 'align' => 'center'),
                '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
                '发货时间' => array('field' => 'consign_time', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
            ),
            'stockoutdetail'=>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '150'),
                '货位' => array('field' => 'position_no', 'width' => '150'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '单价' => array('field' => 'cost_price', 'width' => '100'),
                '数量' => array('field' => 'num', 'width' => '100'),
//                 '估重' => array('field' => 'weight', 'width' => '100'),
                '备注' => array('field' => 'remark', 'width' => '200'),
            ),
            'stockoutexport'=>array(

                '出库单号'=>array('field'=>'stockout_no','width'=>'100'),
                '出库人员'=>array('field'=>'creator_name','width'=>'100'),
                '仓库名称'=>array('field'=>'warehouse_name','width'=>'100'),
                '出库单状态'=>array('field'=>'status','width'=>'100'),
                '出库单类别'=>array('field'=>'mode','width'=>'100'),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'150'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '品牌'=>array('field'=>'brand_id','width'=>'100'),
                '条形码'=>array('field'=>'barcode','width'=>'100'),
                '货位' => array('field' => 'position_no', 'width' => '100'),
                '单价'=>array('field'=>'cost_price','width'=>'100'),
                '数量'=>array('field'=>'num','width'=>'100'),
                '单位'=>array('field'=>'base_unit_id','width'=>'100'),
                '备注' => array('field' => 'remark', 'width' => '200', 'align' => 'center'),
                '修改时间' => array('field' => 'modified', 'width' => '200', 'align' => 'center'),
                '创建时间' => array('field' => 'created', 'width' => '200', 'align' => 'center'),
            ),
        );
        return $fields[$key];
    }
}