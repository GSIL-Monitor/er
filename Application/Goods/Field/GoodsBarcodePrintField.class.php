<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:30
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsBarcodePrintField extends Field {
    protected function get($key) {
        $fields = array(
            'barcodeprint' => array(
                'id'   => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                '条码'   => array('field' => 'barcode', 'width' => 200, 'sortable' => true),
                '商家编码' => array('field' => 'merchant_no', 'width' => 150, 'sortable' => true),
                '货品名称' => array('field' => 'goods_name', 'width' => 200, 'sortable' => true),
                '简称'   => array('field' => 'short_name', 'width' => 100, 'sortable' => true),
                '货品编码' => array('field' => 'goods_no', 'width' => 100, 'sortable' => true),
                '规格名称'  => array('field' => 'spec_name', 'width' => 100, 'sortable' => true),
                '规格码'  => array('field' => 'spec_code', 'width' => 100, 'sortable' => true),
                '组合装'  => array('field' => 'is_suite', 'width' => 80, 'sortable' => true, ),
                '主条码'  => array('field' => 'is_master', 'width' => 80, 'sortable' => true, ),
                '自定义属性1'  => array('field' => 'prop1', 'width' => 80, 'sortable' => true, ),
                '自定义属性2'  => array('field' => 'prop2', 'width' => 80, 'sortable' => true, ),
                '自定义属性3'  => array('field' => 'prop3', 'width' => 80, 'sortable' => true, ),
                '自定义属性4'  => array('field' => 'prop4', 'width' => 80, 'sortable' => true, ),
                '打印次数'=>array('field'=>'print_num','width'=>'50','editor'=>'{type:"numberbox",options:{required:true,precision:0,min:1}}'),
            ),
        );
        return $fields[ $key ];
    }
}