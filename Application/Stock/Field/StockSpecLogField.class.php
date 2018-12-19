<?php
namespace Stock\Field;
use Common\Common\Field;

class StockSpecLogField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'stockspeclog'=>array(
                'id'=>array('field'=>'id','hidden'=>true),
                '仓库'=>array('field'=>'warehouse_name','width'=>'100',),
                '货品名称'=>array('field'=>'goods_name','width'=>'100',),
                '商家编码'=>array('field'=>'spec_no','width'=>'100',),
                '货品编码'=>array('field'=>'goods_no','width'=>'100',),
//                '分类'=>array('field'=>'class_name','width'=>'100',),
                '规格'=>array('field'=>'spec_name','width'=>'100',),
//                '数量'=>array('field'=>'num','width'=>'100',),
//                '入库数量'=>array('field'=>'stockin_num','width'=>'100',),
                '操作数量'=>array('field'=>'num','width'=>'100',),
                '操作类型'=>array('field' => 'operator_name','width'=>'100'),
                '操作日志'=>array('field'=>'message','width'=>'400'),
                '操作人'=>array('field' => 'fullname','width'=>'80'),
                '操作时间'=>array('field'=>'created','width'=>'150'),
            ),
        );
        return $fields[$key];
    }
}