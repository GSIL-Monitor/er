<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/24
 * Time: 19:24
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsBrandField extends Field {

    protected function get($key) {
        $fields = array(
            "goods_brand" => array(
                "id"     => array("field" => "id", "hidden" => true),
                "品牌名称"   => array("field" => "brand_name", "title" => "品牌名称", "width" => "20%", "methods" => '"editor":"textbox"'),
                "备注"     => array("field" => "remark", "title" => "备注", "width" => "20%", "methods" => '"editor":"textbox"'),
                "是否停用"   => array("field" => "is_disabled", "title" => "是否停用", "width" => "19%", "methods" => '"editor":{type:"checkbox",options:{on:1,off:0}}', "formatter" => "formatter.boolen"),
                "最后修改时间" => array("field" => "modified", "title" => "最后修改时间", "width" => "20%"),
                "创建时间"   => array("field" => "created", "title" => "创建时间", "width" => "20%")
            )
        );
        return $fields[ $key ];
    }

}