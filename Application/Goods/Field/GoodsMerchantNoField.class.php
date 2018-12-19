<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/25
 * Time: 10:52
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsMerchantNoField extends Field {

    /**
     * 返回货品档案页面的fields
     * @param $key
     * @return mixed
     */
    protected function get($key) {
        $fields = array(
            "goods_merchant_no" => array(
                "rec_id"     => array("field" => "rec_id", "hidden" => true),
                '商家编码'  => array('field' => 'merchant_no', 'width' => 100, 'sortable' => false),
                '货品编码'  => array('field' => 'goods_no', 'width' => 100, 'sortable' => true),
                '货品名称'  => array('field' => 'goods_name', 'width' => 150, 'sortable' => true),
                '货品简称'  => array('field' => 'short_name', 'width' => 100, 'sortable' => true),
                '规格名称'  => array('field' => 'spec_name', 'width' => 100, 'sortable' => true),
                '规格码'   => array('field' => 'spec_code', 'width' => 100, 'sortable' => true),
                '是否组合装' => array('field' => 'is_suite', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.boolen'),
            ));
        return $fields[ $key ];
    }

}