<?php
namespace Setting\Field;

use Common\Common\Field;
class AreaAliasField extends Field{
    protected function get($key){
        $fields = array(
            'logistics_area_alias' => array(
                'id' => array('field' => 'id', 'hidden' => true),
                '物流公司' => array('field' => 'logistics_id',  'sortable' => true,'width'=>'200'),
                '地址' => array('field' => 'path',  'sortable' => false,'width'=>'300','formatter'=>'formatterLogisticsArea'),
                '地区别名' => array('field' => 'alias_name',  'sortable' => true,'width'=>'300'),
                '修改时间' => array('field' => 'modified', 'sortable' => true,'width'=>'200'),
                '创建时间' => array('field' => 'created', 'sortable' => true,'width'=>'200')
            ),
        );
        return $fields[$key];
    }
}