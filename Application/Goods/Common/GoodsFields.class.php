<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/3
 * Time: 9:25
 */
namespace Goods\Common;

class GoodsFields {

    /**
     * @param $name
     * @return array
     * ��ȡfields
     * author:luyanfeng
     */
    /*static public function getGoodsFields($name) {
        $name = strtolower($name);
        if (isset(self::$fields[$name])) {
            return self::$fields[$name];
        } else {
            \Think\Log::write("unknown fields" . $name);
            return array();
        }
    }*/

    static private $fields = array(
        "goodsbrand"            => array(
            "id"     => array("field" => "id", "hidden" => true),
            "品牌编号"   => array("field" => "brand_no", "title" => "品牌编号", "width" => "17%", "methods" => '"editor":"textbox"'),
            "品牌名称"   => array("field" => "brand_name", "title" => "品牌名称", "width" => "17%", "methods" => '"editor":"textbox"'),
            "备注"     => array("field" => "remark", "title" => "备注", "width" => "17%", "methods" => '"editor":"textbox"'),
            "是否停用"   => array("field" => "is_disabled", "title" => "是否停用", "width" => "16%", "methods" => '"editor":{type:"checkbox",options:{on:1,off:0}}', "formatter" => "formatter.boolen"),
            /*"是否停用" => array("field" => "is_disabled", "title" => "是否停用", "width" => "16%", "formatter" => "formatter.boolen"),*/
            "最后修改时间" => array("field" => "modified", "title" => "最后修改时间", "width" => "16%"),
            "创建时间"   => array("field" => "created", "title" => "创建时间", "width" => "16%")
        ),
        "platformgoods"         => array(
            "id"     => array("field" => "rec_id", "hidden" => true),
            "店铺"     => array("field" => "shop_name", "title" => "店铺", "width" => "80", "sortable" => true),
            "货品名称"   => array("field" => "goods_name", "title" => "货品名称", "width" => "80", "sortable" => true),
            "规格名称"   => array("field" => "spec_name", "title" => "规格名称", "width" => "80", "sortable" => true),
            "货品编码"   => array("field" => "outer_id", "title" => "货品编码", "width" => "80", "sortable" => true),
            "规格编码"   => array("field" => "spec_outer_id", "title" => "规格编码", "width" => "80", "sortable" => true),
            "货品ID"   => array("field" => "goods_id", "title" => "货品ID", "width" => "80", "sortable" => true),
            "规格ID"   => array("field" => "spec_id", "title" => "规格ID", "width" => "80", "sortable" => true),
            "价格"     => array("field" => "price", "title" => "价格", "width" => "80", "sortable" => true),
            "平台库存"   => array("field" => "stock_num", "title" => "平台库存", "width" => "80", "sortable" => true),
            //"占用库存"   => array("field" => "hold_stock", "title" => "占用库存", "width" => "80", "sortable" => true),
            /*"占用方式"   => array("field"     => "hold_stock_type", "title" => "占用方式", "width" => "80", "sortable" => true,
                              "formatter" => "formatter.hold_stock_type"),*/
            "自动上架"   => array("field"     => "is_auto_listing", "title" => "自动上架", "width" => "80", "sortable" => true,
                              "formatter" => "formatter.boolen"),
            "自动下架"   => array("field" => "is_auto_delisting", "title" => "自动下架", "width" => "80", "sortable" =>
                true, "formatter"     => "formatter.boolen"),
            "状态"     => array("field"     => "status", "title" => "状态", "width" => "80", "sortable" => true,
                              "formatter" => "formatter.api_goods_spec_status"),
            "自动匹配"   => array("field"     => "is_auto_match", "title" => "自动匹配", "width" => "80", "sortable" => true,
                              "formatter" => "formatter.boolen"),
            "系统货品"   => array("field" => "match_target_type", "title" => "系统货品", "width" => "80", "sortable" =>
                true, "formatter"     => "formatter.match_target_type"),
            "是否需要同步" => array("field" => "is_stock_changed", "title" => "是否需要同步", "width" => "80", "sortable" =>
                true, "formatter"     => "formatter.boolen"),
            "最后同步库存" => array("field" => "last_syn_num", "title" => "最后同步库存", "width" => "80", "sortable" => true),
            "最后同步时间" => array("field" => "last_syn_time", "title" => "最后同步时间", "width" => "80", "sortable" => true),
            "标记"     => array("field" => "flag_name", "title" => "标记", "width" => "80", "sortable" => true),
            "最后修改时间" => array("field" => "modified", "title" => "最后修改时间", "width" => "80", "sortable" => true)
        ),
        "systemgoods"           => array(
            "id"   => array("field" => "id", "hidden" => true),
            "系统货品" => array("field" => "match_target_type", "title" => "是否组合装", "width" => 100, "formatter" =>
                "formatter.match_target_type"),
            "商家编码" => array("field" => "spec_no", "title" => "商家编码", "width" => "100"),
            "货品编码" => array("field" => "goods_no", "title" => "货品编码", "width" => "100"),
            "条码"   => array("field" => "barcode", "title" => "条码", "width" => "100"),
            "货品名称" => array("field" => "goods_name", "title" => "货品名称", "width" => "100"),
            "规格名称" => array("field" => "spec_name", "title" => "规格名称", "width" => "100"),
            "零售价"  => array("field" => "retail_price", "title" => "零售价", "width" => "100"),
            "货品分类" => array("field" => "class_name", "title" => "货品分类", "width" => "100"),
            "品牌"   => array("field" => "brand_name", "title" => "品牌", "width" => "100"),
            "备注"   => array("field" => "mark", "title" => "备注", "width" => "100")
        ),
        "platformgoodsmatchlog" => array(
            "id"   => array("field" => "rec_id", "hidden" => true),
            "操作员"  => array("field" => "shortname", "title" => "操作员", "width" => 300, "sortable" => true),
            "操作内容" => array("field" => "message", "title" => "操作内容", "width" => 600, "sortable" => true),
            "操作时间" => array("field" => "created", "title" => "操作时间", "width" => 300, "sortable" => true)
        ),
        "goodssuite"            => array(
            "id"    => array("field" => "id", "hidden" => true),
            '组合装名称' => array("field" => 'suite_name', 'width' => "11%", "title" => "组合装名称", 'sortable' => true),
            '商家编码'  => array("field" => 'suite_no', 'width' => "11%", "title" => "商家编码", 'sortable' => true),
            "条形码"   => array("field" => "barcode", "width" => "11%", "title" => "条形码", "sortable" => true),
            "零售价"   => array("field" => "retail_price", "width" => "11%", "title" => "零售价", "sortable" => true),
            /*"批发价" => array("field" => "wholesale_price", "width" => "11%", "title" => "批发价", "sortable" => true),
            "会员价" => array("field" => "member_price", "width" => "11%", "title" => "会员价", "sortable" => true),*/
            "市场价"   => array("field" => "market_price", "width" => "11%", "title" => "市场价", "sortable" => true),
            "品牌"    => array("field" => "brand_name", "width" => "11%", "brand_id" => "品牌", "sortable" => true),
            "类别"    => array("field" => "class_name", "width" => "11%", "class_id" => "类别", "sortable" => true),
            "重量(kg)"    => array("field" => "weight", "width" => "11%", "title" => "重量", "sortable" => true),
            /*"自定义属性1" => array("field" => "prop1", "width" => "11%", "title" => "自定义属性1", "sortable" => true),
            "自定义属性2" => array("field" => "prop2", "width" => "11%", "title" => "自定义属性2", "sortable" => true),
            "自定义属性3" => array("field" => "prop3", "width" => "11%", "title" => "自定义属性3", "sortable" => true),
            "自定义属性4" => array("field" => "prop4", "width" => "11%", "title" => "自定义属性4", "sortable" => true),*/
            "备注"    => array("field" => "remark", "width" => "11%", "sortable" => true)
        ),
        "goodssuitedetail"      => array(
            "id"   => array("field" => "id", "hidden" => true),
            "商家编码" => array("field" => "spec_no", "title" => "商家编码", "width" => "11%"),
            "货品编码" => array("field" => "goods_no", "tiitle" => "货品编码", "width" => "11%"),
            "货品名称" => array("field" => "goods_name", "title" => "货品名称", "width" => "11%"),
            "规格名称" => array("field" => "spec_name", "title" => "规格名称", "width" => "11%"),
            "规格码"  => array("field" => "spec_code", "title" => "规格码", "width" => "11%"),
            "数量"   => array("field" => "num", "title" => "数量", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4}}'),
            "单价"   => array("field" => "retail_price", "title" => "单价", "width" => "11%"),
            "金额占比" => array("field" => "ratio", "title" => "金额占比", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4}}'),
            "固定价格" => array("field" => "is_fixed_price", "title" => "固定价格", "width" => "11%", "methods" => 'editor:{type: "checkbox", options: {on: 1, off: 0}}', "formatter" => "formatter.boolen")
        ),
        "goodssuitelog"         => array(
            "id"  => array("field" => "id", "hidden" => true),
            "操作员" => array("field" => "shortname", "title" => "操作员", "width" => "200"),
            "内容"  => array("field" => "message", "title" => "内容", "width" => "500"),
            "时间"  => array("field" => "created", "title" => "时间", "width" => "200")
        ),
        "tabsplatformgoods"     => array(
            "id"     => array("field" => "rec_id", "hidden" => true),
            "规格商家编码" => array("field" => "spec_outer_id", "title" => "规格商家编码", "width" => "90"),
            "店铺"     => array("field" => "shop_name", "title" => "店铺", "width" => "70"),
            "平台货品名称" => array("field" => "goods_name", "title" => "平台货品名称", "width" => "90"),
            "平台规格名称" => array("field" => "spec_name", "title" => "平台规格名称", "width" => "90"),
            "平台货品ID" => array("field" => "goods_id", "title" => "平台货品ID", "width" => "90"),
            "平台规格ID" => array("field" => "spec_id", "title" => "平台规格ID", "width" => "90"),
            "货品商家编码" => array("field" => "outer_id", "title" => "货品上架编码", "width" => "90"),
            "系统货品编码" => array("field" => "suite_no", "title" => "系统货品编码", "width" => "90"),
            "系统货品名称" => array("field" => "suite_name", "title" => "系统货品名称", "width" => "90"),
            "是否组合装"  => array("field" => "match_target_type", "title" => "是否组合装", "width" => "90", "formatter" => "formatter.match_target_type"),
            "价格"    => array("field" => "price", "title" => "价格", "width" => "90"),
            "状态"     => array("field" => "status", "title" => "状态", "width" => "90", "formatter" => "formatter.api_goods_spec_status"),
            "平台库存"   => array("field" => "stock_num", "title" => "平台库存", "width" => "90"),
            "最后同步库存" => array("field" => "last_syn_num", "title" => "最后同步库存", "width" => "90"),
            "最后同步时间" => array("field" => "last_syn_time", "title" => "最后同步时间", "width" => "90")
        ),

        'platform_goods'        => array(
            'id'     => array('field' => 'id', 'hidden' => true,),
            '店铺'     => array('field' => 'shop_id', 'width' => '100',),
            '货品名称'   => array('field' => 'goods_name', 'width' => '100',),
            '规格名称'   => array('field' => 'spec_name', 'width' => '100',),
            '货品编码'   => array('field' => 'outer_id', 'width' => '100',),
            '规格编码'   => array('field' => 'spec_outer_id', 'width' => '100',),
            '货品ID'   => array('field' => 'goods_id', 'width' => '100',),
            '规格ID'   => array('field' => 'spec_id', 'width' => '100',),
            '价格'     => array('field' => 'price', 'width' => '100',),
            '平台库存'   => array('field' => 'stock_num', 'width' => '100',),
            '自动上架'   => array('field' => 'is_auto_listing', 'width' => '100',),
            '自动下架'   => array('field' => 'is_auto_delisting', 'width' => '100',),
            '状态'     => array('field' => 'status', 'width' => '100',),
            '自动匹配'   => array('field' => 'is_auto_match', 'width' => '100',),
            '系统货品'   => array('field' => 'match_target_type', 'width' => '100',),
            '最后同步库存' => array('field' => 'last_syn_num', 'width' => '100',),
            '最后同步时间' => array('field' => 'last_syn_time', 'width' => '100',),
            '最后修改时间' => array('field' => 'modified', 'width' => '100',),
        ),

    );
}