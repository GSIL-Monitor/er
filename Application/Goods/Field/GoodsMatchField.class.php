<?php
namespace Goods\Field;

use Common\Common\Field;

class GoodsMatchField extends Field {

    protected function get($key) {
        $fields = array(
            "goods_match_merchant_no" => array(
                "id"     => array("field" => "rec_id", "hidden" => true),
                "店铺"     => array("field" => "shop_name", "title" => "店铺", "width" => '60'),
                '图片'    => array('field'=>'pic_url','width'=>'50','sortable'=>false,'formatter'=>'formatter.print_img'),
                "宝贝名称"   => array("field" => "platform_goods_name", "title" => "宝贝名称", "width" => '100', "sortable" => false),
                "SKU名称"   => array("field" => "platform_spec_name", "title" => "SKU名称", "width" => '80', "sortable" => false),
                "宝贝编码"   => array("field" => "platform_outer_id", "title" => "宝贝编码", "width" => '80', "sortable" => false),
                "SKU编码"   => array("field" => "platform_spec_outer_id", "title" => "SKU编码", "width" => '80', "sortable" => false),
                '系统商家编码'  => array('field' => 'merchant_no', 'width' => '80', 'sortable' => false),
                //'系统货品编码'  => array('field' => 'goods_no', 'width' => '80', 'sortable' => true),
                '系统规格码'   => array('field' => 'spec_code', 'width' => '80', 'sortable' => true),
                '系统货品名称'  => array('field' => 'goods_name', 'width' => '120', 'sortable' => true),
                '系统规格名称'  => array('field' => 'spec_name', 'width' => '120', 'sortable' => true),
                '是否组合装' => array('field' => 'is_suite', 'width' => '50px', 'sortable' => false, 'formatter' => 'formatter.boolen'),
            ));
        return $fields[ $key ];
    }

}