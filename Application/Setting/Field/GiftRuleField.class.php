<?php
namespace Setting\Field;
use Common\Common\Field;
class GiftRuleField extends Field{
    protected function get($key){
        $fields = array(
            'gift_rule'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true'),
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true),
                '策略编码' => array('field' => 'rule_no',  'sortable' => true, 'width'=>120),
                '规则名称' => array('field' => 'rule_name',  'sortable' => true, 'width'=>150),
                '分组' => array('field' => 'rule_group', 'sortable' => true, 'width'=>80),
                '时间类型' => array('field' => 'time_type',  'sortable' => true, 'width'=>100,'formatter' => 'formatter.time_type'),
                '开始时间' => array('field' => 'start_time',  'sortable' => true, 'width'=>150),
                '结束时间' => array('field' => 'end_time',  'sortable' => true, 'width'=>150),
                '优先级' => array('field' => 'rule_priority',  'sortable' => true, 'width'=>50),
                '停用' => array('field' => 'is_disabled',  'sortable' => true, 'width'=>50,'formatter' => 'formatter.boolen'),
                '备注' => array('field' => 'remark',  'sortable' => true, 'width'=>120),
                '创建时间' => array('field' => 'created',  'sortable' => true, 'width'=>150),
                '最后修改时间' => array('field' => 'modified',  'sortable' => true, 'width'=>150),
            ),
            'goods_list'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true'),
                '商家编码' => array('field' => 'merchant_no',   'width'=>120),
                '货品编码' => array('field' => 'goods_no',   'width'=>120),
                '货品名称' => array('field' => 'goods_name',  'width'=>150),
                '规格名称' => array('field' => 'spec_name',   'width'=>150),
                '规格编码' => array('field' => 'spec_code',   'width'=>120),
                '是否为组合装' => array('field' => 'is_suite',   'width'=>80,'formatter' => 'formatter.boolen'),
            ),
            'gift_list'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true'),
                '商家编码' => array('field' => 'merchant_no',   'width'=>110),
                '货品编码' => array('field' => 'goods_no',   'width'=>110),
                '货品名称' => array('field' => 'goods_name',  'width'=>150),
                '规格名称' => array('field' => 'spec_name',   'width'=>120),
                '规格编码' => array('field' => 'spec_code',   'width'=>110),
                '数量(可编辑)' => array('field' => 'gift_num',   'width'=>90,'methods'=>'editor:{type:"numberbox",options:{required:true,precision:4,min:1}}'),
                '是否为组合装' => array('field' => 'is_suite',   'width'=>90,'formatter' => 'formatter.boolen'),
            	'赠品分组(可编辑)'=>array('field'=>'gift_group','hidden'=>true,'width'=>120,'methods'=>'editor:{type:"numberbox",options:{equired:true,min:1}}')
            ),
        );
        return $fields[$key];
    }
}