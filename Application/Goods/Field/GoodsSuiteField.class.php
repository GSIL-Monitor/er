<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/24
 * Time: 19:49
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsSuiteField extends Field {

    /**
     * 返回组合装页面的fields
     * author:luyanfeng
     * @param $key
     * @return mixed
     */
    protected function get($key) {
        $fields = array(
            "goods_suite" => array(
                "id"    => array("field" => "id", "hidden" => true),
                '组合装名称' => array("field" => 'suite_name', 'width' => "11%", "title" => "组合装名称"),
                '商家编码'  => array("field" => 'suite_no', 'width' => "11%", "title" => "商家编码"),
                "条形码"   => array("field" => "barcode", "width" => "11%", "title" => "条形码"),
                "零售价"   => array("field" => "retail_price", "width" => "11%", "title" => "零售价"),
                "市场价"   => array("field" => "market_price", "width" => "11%", "title" => "市场价"),
                "品牌"    => array("field" => "brand_name", "width" => "11%", "brand_id" => "品牌"),
                "类别"    => array("field" => "class_name", "width" => "11%", "class_id" => "类别"),
                "重量(kg)"    => array("field" => "weight", "width" => "11%", "title" => "重量"),
                "备注"    => array("field" => "remark", "width" => "11%")
            ),
            "goods_suite_barcode"=>array(
            "id"   => array("field" => "id", "hidden" => true),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => 120, "sortable" => true),
            "商家编码" => array("field" => 'suite_no', "title" => '商家编码', "width" => 120, "sortable" => true),
            "组合装名称" => array("field" => 'suite_name', "title" => '货品编码', "width" => 120, "sortable" => true),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => 120, "sortable" => true),
            "主条码"  => array("field" => 'is_master', "title" => '主条码', "width" => 120, "sortable" => true),
        )
        );
        return $fields[ $key ];
    }

}