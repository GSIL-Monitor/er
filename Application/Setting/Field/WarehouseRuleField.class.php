<?php
namespace Setting\Field;
use Common\Common\Field;
class WarehouseRuleField extends Field{
    protected function get($key){
        $fields = array(
            'warehouse'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true'),
                '仓库名称' => array('field' => 'name', 'width'=>130),
                '省' => array('field' => 'province', 'width'=>100),
                '市' => array('field' => 'city', 'width'=>100),
                '区' => array('field' => 'district', 'width'=>100),
            ),
            'shop_warehouse'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true'),
                '仓库名称' => array('field' => 'name', 'width'=>130),
                '优先级(可编辑,数值越大优先级越高)' => array('field' => 'priority', 'width'=>120,'methods'=>'editor:{type:"numberbox",options:{min:1,max:10000}}'),
                '选中仓库(可编辑)' => array('field' => 'is_select','align'=>'center', 'width'=>120,'formatter'=>'formatter.boolen','methods'=>'editor:{type:"checkbox",options:{on:"1",off:"0"}}'),
                // '允许缺货(可编辑)' => array('field' => 'is_lack', 'width'=>120),
                '省' => array('field' => 'province', 'width'=>100),
                '市' => array('field' => 'city', 'width'=>100),
                '区' => array('field' => 'district', 'width'=>100),
            ),
        );
        return $fields[$key];
    }
}