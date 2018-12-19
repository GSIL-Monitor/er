<?php
namespace Stock\Field;

use Common\Common\Field;

class SalesStockoutExamineField extends Field
{
    protected  function get($key){
        $fields = array(
            'salesstockoutexamine'=>array(
                '出库单详情id'=>array('field'=>'rec_id','hidden'=>true),
                '单品id'=>array('field'=>'spec_id','hidden'=>true),
                '商家编码'=>array('field'=>'spec_no','width'=>'160'),
                '货品编码'=>array('field'=>'goods_no','width'=>'160'),
                '图片'=>array('field'=>'pic_name','width'=>'160','formatter'=>'formatter.print_img'),
                '条码'=>array('field'=>'barcode','width'=>'160','hidden'=>true),
                '货品名称'=>array('field'=>'goods_name','width'=>'160'),
                '规格名称'=>array('field'=>'spec_name','width'=>'160'),
                '规格码'=>array('field'=>'spec_code','width'=>'160'),
                '备注'=>array('field'=>'remark','width'=>'160'),
                '数量'=>array('field'=>'num','width'=>'50'),
                '勾选'=>array('field'=>'is_checkbox','hidden'=>true),
                '校验量(编辑)'=>array('field'=>'check_num','width'=>'50','editor'=>'{type:"numberbox",options:{required:true,precision:'.get_config_value('point_number',0).',min:0}}'),
                '是否完成'=>array('field'=>'is_finished','hidden'=>true),
            ),
            'goodslist'=>array(
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编码'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '是否组合装'=>array('field'=>'is_suite','width'=>'100','align'=>'center',"formatter" => "formatter.boolen"),
            ),
        );
        return $fields[$key];
    }
}
?>