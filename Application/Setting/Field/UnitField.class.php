<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 09:29
 */

namespace Setting\Field;
use Common\Common\Field;
class UnitField extends Field{
    protected function get($key){
        $fields = array(
            'unit'=>array(
                'id' => array('field' => 'id','hidden'=>'true',  'sortable' => true, 'align' => 'center'),
                '名称' => array('field' => 'name',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '是否停用' => array('field' => 'is_disabled',  'sortable' => true, 'align' => 'center','width'=>'200','formatter'=>"formatter.toYN"),
                '备注' => array('field' => 'remark',  'sortable' => true, 'align' => 'center','width'=>'200'),
            ),
        );
        return $fields[$key];
    }
}