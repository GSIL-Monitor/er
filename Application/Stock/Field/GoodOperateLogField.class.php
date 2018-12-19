<?php
/**
 * Created by PhpStorm.
 * User: Asher
 * Date: 2016-11-01
 * Time: 17:03
 */
namespace Stock\Field;
use Common\Common\Field;
class GoodOperateLogField extends Field{
    protected function get($key){
        $fields = array(
            'good_operate_log' => array(
                'id'       => array('field'=>'id','hidden'=>true,'sortable'=>true),
                '货品编号' => array('field'=>'goods_no','width'=>100,'sortable'=>true),
                '商品编号' => array('field'=>'spec_no','width'=>100,'sortable'=>true),
                '操作类型' => array('field'=>'operate_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.operator_type'),
                '操作记录' => array('field'=>'message','width'=>300,'sortable'=>true),
                '操作人'   => array('field'=>'fullname','width'=>80,'sortable'=>true),
                '操作时间' => array('field'=>'created','width'=>150,'sortable'=>true),
            ),
        );
        return $fields[$key];
    }
}