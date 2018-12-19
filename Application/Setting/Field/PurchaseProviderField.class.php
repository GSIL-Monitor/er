<?php
namespace Setting\Field;

use Common\Common\Field;

class PurchaseProviderField extends Field {
    protected function get($key) {
        $fields = array(
            'purchase_provider' => array(
                'id'   => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                'provider_group_id'   => array('field' => 'provider_group_id', 'hidden' => true, 'sortable' => true),
				'供应商名称' => array('field' => 'provider_name', 'width' => 150, 'sortable' => true),
                '联系人' => array('field' => 'contact', 'width' => 80, 'sortable' => true),
                '座机'   => array('field' => 'telno', 'width' => 80, 'sortable' => true),
                '移动电话' => array('field' => 'mobile', 'width' => 100, 'sortable' => true),
				'供应商分组名称' => array('field' => 'provider_group_name', 'width' => 150, 'sortable' => true),
                '省份'  => array('field' => 'province', 'width' => 100, 'sortable' => true),
                '城市'  => array('field' => 'city', 'width' => 100, 'sortable' => true),
                '地区'  => array('field' => 'district', 'width' => 100, 'sortable' => true),
                '地址'  => array('field' => 'address', 'width' => 100, 'sortable' => true),
                '旺旺'  => array('field' => 'wangwang', 'width' => 100, 'sortable' => true),
                '备注'  => array('field' => 'remark', 'width' => 150, 'sortable' => true),
                '是否停用'  => array('field' => 'is_disabled', 'width' => 70, 'sortable' => true, "methods" => '"editor":{type:"checkbox",options:{on:1,off:0}}', "formatter" => "formatter.boolen"),
                '最后修改时间'  => array('field' => 'modified', 'width' => 130, 'sortable' => true),
                '创建时间' => array('field' => 'created', 'width' => 130, 'sortable' => true),
            ),
			'provider_group'=>array(
				'id'   => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                '供应商分组名称' => array('field' => 'provider_group_name', 'width' => 150, 'sortable' => true),
				'供应商分组编号' => array('field' => 'provider_group_no', 'width' => 150, 'sortable' => true),
                '省份' => array('field' => 'province', 'width' => 100, 'sortable' => true),
                '城市'   => array('field' => 'city', 'width' => 100, 'sortable' => true),
                '地区' => array('field' => 'district', 'width' => 100, 'sortable' => true),
                '供应商分组地址'  => array('field' => 'address', 'width' => 150, 'sortable' => true),           
                '备注'  => array('field' => 'remark', 'width' => 150, 'sortable' => true),
                '是否停用'  => array('field' => 'is_disabled', 'width' => 70, 'sortable' => true, "methods" => '"editor":{type:"checkbox",options:{on:1,off:0}}', "formatter" => "formatter.boolen"),
                '最后修改时间'  => array('field' => 'modified', 'width' => 130, 'sortable' => true),
                '创建时间' => array('field' => 'created', 'width' => 130, 'sortable' => true),
			),
        );
        return $fields[ $key ];
    }
}