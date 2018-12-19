<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2016/5/12
 * Time: 11:06
 */
namespace Stock\Field;
use Common\Common\Field;

class StockTransferField extends Field {
    protected function get($key) {
        $fields = array(
            'stock_transfer_made_detail'=>array(
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'100'),
//                '货品简称'=>array('field'=>'short_name','width'=>'100'),
                '品牌id'=>array('field'=>'brand_id','width'=>'100','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'80'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '条形码'=>array('field'=>'barcode','width'=>'100'),
                '调出货位'=>array('field'=>'from_position_no','width'=>'100'),//,'editor'=>'{type:"textbox",options:{buttonText:"...",editable:false}}'
//                '调出货位'=>array('field'=>'from_position','width'=>'100'),
                '调入货位'=>array('field'=>'to_position_no','width'=>'100','editor'=>'{type:"textbox",options:{buttonText:"...",editable:false}}'),
//                '调入货位'=>array('field'=>'to_position','width'=>'100'),
//                '有效期'=>array('field'=>'expire_date','width'=>'100'),
//                '批次号'=>array('field'=>'batch_no','width'=>'100'),
                '库存量'=>array('field'=>'stock_num','width'=>'50'),
//                '可调拨数量'=>array('field'=>'no_reserve_num','width'=>'100'),
                '可出库存量'=>array('field'=>'orderable_num','width'=>'100'),
               '调拨数量(可编辑)'=>array('field'=>'num','width'=>'100','editor'=>'{type:"numberbox",options:{required:true,precision:'.get_config_value('point_number',0).',value:0,min:0}}'),
                '基本单位'=>array('field'=>'unit_name','width'=>'100'),
//                '辅助量'=>array('field'=>'num2','width'=>'100'),
//                '换算系数'=>array('field'=>'unit_ratio','width'=>'100'),
//                '辅助单位'=>array('field'=>'unit_id','width'=>'100'),
                '备注(可编辑)'=>array('field'=>'remark','width'=>'100','methods'=>'editor:{type:"textbox"}'),
            )
        );
        return $fields[$key];
    }
}

