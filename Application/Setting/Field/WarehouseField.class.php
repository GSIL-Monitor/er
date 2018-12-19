<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:18
 */
namespace Setting\Field;
use Common\Common\Field;
class WarehouseField extends Field{
    protected function get($key){
        $fields = array(
            'warehouse' => array(
                '仓库名称' => array('field' => 'name', 'width' => '100'),
                '仓库类别' => array('field' => 'type', 'width' => '100', 'formatter' => 'formatter.warehouse_type',),
                '省份' => array('field' => 'province', 'width' => '100' ),
                '城市' => array('field' => 'city', 'width' => '100' ),
                '地区' => array('field' => 'district', 'width' => '100'),
                '地址' => array('field' => 'address', 'width' => '100'),
                '联系人' => array('field' => 'contact', 'width' => '100'),
                '邮编' => array('field' => 'zip', 'width' => '100'),
                '手机' => array('field' => 'mobile', 'width' => '100'),
                '固话' => array('field' => 'telno', 'width' => '100'),
                '残次品库' => array('field' => 'is_defect', 'width' => '100', 'formatter' => 'formatter.toYN'),
                '停用' => array('field' => 'is_disabled', 'width' => '100','formatter' => 'formatter.toYN'),
                'id' => array('field' => 'id', 'hidden' => true),
            ),
			'warehouse_address'=>array(
				'物流公司'=>array('field'=>'cp_code','width'=>'140','align'=>'center','formatter'=>'formatter.logistics_name_code'),
                '网点名称'=>array('field'=>'branch_name','width'=>'140','align'=>'center'),
				'省份'=>array('field'=>'province','width'=>'140','align'=>'center'),
				'城市'=>array('field'=>'city','width'=>'140','align'=>'center'),
				'地区'=>array('field'=>'district','width'=>'140','align'=>'center'),
				'地址'=>array('field'=>'address','width'=>'140','align'=>'center'),
			),
        );
        return $fields[$key];
    }
}