<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/25
 * Time: 11:02
 */
namespace Goods\Field;

use Common\Common\Field;

class PlatformGoodsField extends Field {

    /**
     * 获取平台货品的field
     * @param $key
     * @return mixed
     */
    protected function get($key) {
        $fields = array(
            "platform_goods" => array(
                "id"     => array("field" => "rec_id", "hidden" => true),
                "店铺"     => array("field" => "shop_name", "title" => "店铺", "width" => "80"),
                "图片"   => array("field" => "pic_url", "title" => "图片", "width" => "60",'formatter'=>'formatter.print_img'),
                "货品名称 （颜色标记为平台名称变动）"   => array("field" => "goods_name", "title" => "货品名称", "width" => "250", "sortable" => true),
                "规格名称"   => array("field" => "spec_name", "title" => "规格名称", "width" => "80", "sortable" => true),
                "货品商家编码"   => array("field" => "outer_id", "title" => "货品商家编码", "width" => "80", "sortable" => true),
                "规格商家编码"   => array("field" => "spec_outer_id", "title" => "规格商家编码", "width" => "80", "sortable" => true),
                "货品ID"   => array("field" => "goods_id", "title" => "货品ID", "width" => "80", "sortable" => true),
                "规格ID"   => array("field" => "spec_id", "title" => "规格ID", "width" => "80", "sortable" => true),
                "价格"     => array("field" => "price", "title" => "价格", "width" => "80", "sortable" => true),
                "平台库存"   => array("field" => "stock_num", "title" => "平台库存", "width" => "80", "sortable" => true),
                //"占用库存"   => array("field" => "hold_stock", "title" => "占用库存", "width" => "80", "sortable" => true),
                /*"占用方式"   => array("field"     => "hold_stock_type", "title" => "占用方式", "width" => "80", "sortable" => true,
                                  "formatter" => "formatter.hold_stock_type"),*/
                /*"自动上架"   => array("field"     => "is_auto_listing", "title" => "自动上架", "width" => "80", "sortable" => true,
                                  "formatter" => "formatter.boolen"),
                "自动下架"   => array("field" => "is_auto_delisting", "title" => "自动下架", "width" => "80", "sortable" =>
                    true, "formatter"     => "formatter.boolen"),*/
                "状态"     => array("field"     => "status", "title" => "状态", "width" => "80", "sortable" => true,
                                  "formatter" => "formatter.api_goods_spec_status"),
                "自动匹配"   => array("field"     => "is_auto_match", "title" => "自动匹配", "width" => "80",
                                  "formatter" => "formatter.boolen"),
                "系统货品"   => array("field" => "match_target_type", "title" => "系统货品", "width" => "80", "sortable" =>
                    true, "formatter"     => "formatter.match_target_type"),
                "是否需要同步" => array("field" => "is_stock_changed", "title" => "是否需要同步", "width" => "80", "sortable" =>
                    true, "formatter"     => "formatter.boolen"),
                "最后同步库存" => array("field" => "last_syn_num", "title" => "最后同步库存", "width" => "80", "sortable" => true),
                "最后同步时间" => array("field" => "last_syn_time", "title" => "最后同步时间", "width" => "80", "sortable" => true),
                "标记"     => array("field" => "flag_name", "title" => "标记", "width" => "80"),
                "最后修改时间" => array("field" => "modified", "title" => "最后修改时间", "width" => "130")
            ),
        );
        return $fields[ $key ];
    }

}