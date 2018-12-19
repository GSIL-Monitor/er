<?php
/**
 * Created by PhpStorm.
 * User: lbx
 * Date: 2016/6/30
 * Time: 11:06
 */
namespace Setting\Field;
use Common\Common\Field;
class WarehousePositionField extends Field{
    protected function get($key){
        $fields = array(
            'warehouse_position' => array(
				'仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'name', 'width' => '130'),
                '货位' => array('field' => 'position_no', 'width' => '130'),
                '停用' => array('field' => 'is_disabled', 'width' => '130','formatter' => 'formatter.toYN'),
                '最近修改时间' => array('field' => 'modified', 'width' => '130'),
                '创建时间' => array('field' => 'created', 'width' => '130'),
                'id' => array('field' => 'id', 'hidden' => true),
            ),
            'dialog_position' => array(
				'仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库' => array('field' => 'name', 'width' => '80'),
                '货位' => array('field' => 'position_no', 'width' => '80'),
                '停用' => array('field' => 'is_disabled', 'width' => '50','formatter' => 'formatter.toYN'),
                '最近修改时间' => array('field' => 'modified', 'width' => '130'),
                '创建时间' => array('field' => 'created', 'width' => '130'),
                'id' => array('field' => 'id', 'hidden' => true),
            ),
        );
        return $fields[$key];
    }
}
