<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:30
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsBarcodeField extends Field {
    protected function get($key) {
        $fields = array(
            'goods_barcode' => array(
                'id'   => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                '条码'   => array('field' => 'barcode', 'width' => 200, 'sortable' => true),
                '商家编码' => array('field' => 'spec_no', 'width' => 150, 'sortable' => true),
                '货品名称' => array('field' => 'goods_name', 'width' => 200, 'sortable' => true),
                '简称'   => array('field' => 'short_name', 'width' => 100, 'sortable' => true),
                '货品编码' => array('field' => 'goods_no', 'width' => 100, 'sortable' => true),
                '规格名称'  => array('field' => 'spec_name', 'width' => 100, 'sortable' => true),
                '规格码'  => array('field' => 'spec_code', 'width' => 100, 'sortable' => true),
                '主条码'  => array('field' => 'is_master', 'width' => 80, 'sortable' => true, 'align' => 'center', "formatter" => "formatter.boolen"),
                '组合装'  => array('field' => 'is_suite', 'width' => 80, 'sortable' => true, 'align' => 'center', "formatter" => "formatter.boolen"),
                'type' => array('field' => 'type', 'hidden' => true, 'width' => 100, 'sortable' => true),
            ),
        );
        return $fields[ $key ];
    }
}