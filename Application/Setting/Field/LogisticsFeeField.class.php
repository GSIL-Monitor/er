<?php
namespace Setting\Field;

use Common\Common\Field;
class LogisticsFeeField extends Field{
    protected function get($key){
        $fields = array(
            'logistics_fee' => array(
                'id' => array('field' => 'id', 'hidden' => true),
                '物流公司' => array('field' => 'logistics_id',  'sortable' => true,'width'=>'100'),
            	'适用店铺' =>array('field'=>'target_id','sortable'=>true,'width'=>'100'),
                '地址' => array('field' => 'path',  'sortable' => false,'width'=>'130','formatter'=>'formatterLogisticsArea'),
                '首重' => array('field' => 'first_weight',  'sortable' => true,'width'=>'80'),
                '首重资费' => array('field' => 'first_price',  'sortable' => true,'width'=>'80'),
                '重量区间1' => array('field' => 'weight_step1',  'sortable' => true,'width'=>'80'),
                '续重单位1' => array('field' => 'unit_step1',  'sortable' => true,'width'=>'80'),
                '单位资费1' => array('field' => 'price_step1',  'sortable' => true,'width'=>'80'),
                '重量区间2' => array('field' => 'weight_step2',  'sortable' => true,'width'=>'80'),
                '续重单位2' => array('field' => 'unit_step2',  'sortable' => true,'width'=>'80'),
                '单位资费2' => array('field' => 'price_step2',  'sortable' => true,'width'=>'80'),
                '重量区间3' => array('field' => 'weight_step3',  'sortable' => true,'width'=>'80'),
                '续重单位3' => array('field' => 'unit_step3',  'sortable' => true,'width'=>'80'),
                '单位资费3' => array('field' => 'price_step3',  'sortable' => true,'width'=>'80'),
                '重量区间4' => array('field' => 'weight_step4',  'sortable' => true,'width'=>'80'),
                '续重单位4' => array('field' => 'unit_step4',  'sortable' => true,'width'=>'80'),
                '单位资费4' => array('field' => 'price_step4',  'sortable' => true,'width'=>'80'),
                '特殊重量区间1' => array('field' => 'special_weight1',  'sortable' => true,'width'=>'100'),
                '特殊区间1邮资' => array('field' => 'special_fee1',  'sortable' => true,'width'=>'100'),
                '特殊重量区间2' => array('field' => 'special_weight2',  'sortable' => true,'width'=>'100'),
                '特殊区间2邮资' => array('field' => 'special_fee2',  'sortable' => true,'width'=>'100'),
                '特殊重量区间3' => array('field' => 'special_weight3',  'sortable' => true,'width'=>'100'),
                '特殊区间3邮资' => array('field' => 'special_fee3',  'sortable' => true,'width'=>'100'),
                '修改时间' => array('field' => 'modified',  'sortable' => true,'width'=>'120'),
                '创建时间' => array('field' => 'created',  'sortable' => true,'width'=>'120'),
            ),
        );
        return $fields[$key];
    }
}