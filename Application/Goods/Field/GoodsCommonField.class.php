<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/24
 * Time: 17:43
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsCommonField extends Field {

    protected function get($key) {
        $fields = array(
            "spec_list"                  => array(
                "商家编码"  => array("field" => "spec_no", "width" => "10%"),
                "规格名称"  => array("field" => "spec_name", "width" => "10%"),
                "规格码"   => array("field" => "spec_code", "width" => "8%"),
                "主条码"   => array("field" => "barcode", "width" => "8%"),
                "零售价"   => array("field" => "retail_price", "width" => "8%"),
                /* "批发价"     => array("field" => "wholesale_price", "width" => 60),
                 "会员价"     => array("field" => "member_price", "width" => 60),*/
                "市场价"   => array("field" => "market_price", "width" => "8%"),
                "最低价"   => array("field" => "lowest_price", "width" => "8%"),
                "有效期"   => array("field" => "validity_days", "width" => "8%"),
                "长(CM)" => array("field" => "length", "width" => "8%"),
                "宽(CM)" => array("field" => "width", "width" => "8%"),
                "高(CM)" => array("field" => "height", "width" => "8%"),
                /*"销售积分"    => array("field" => "sale_score", "width" => 60),
                "打包积分"    => array("field" => "pack_score", "width" => 60),
                "拣货积分"    => array("field" => "pick_score", "width" => 60),*/
                "重量"    => array("field" => "weight", "width" => "7%"),
                "大件类别" => array('field' => 'large_type', 'width' => '7%', 'formatter' => 'formatter.large_type'),
                '自定义1' => array('field' => 'prop1', 'width' => '8%'),
                '自定义2' => array('field' => 'prop2', 'width' => '8%'),
                '自定义3' => array('field' => 'prop3', 'width' => '8%'),
                '自定义4' => array('field' => 'prop4', 'width' => '8%'),
                /* "税率"      => array("field" => "tax_rate", "width" => 60),
                 "启用序列号"   => array("field" => "is_sn_enable", "width" => 60, "formatter" => "formatter.boolen"),
                 "允许负库存"   => array("field" => "is_allow_neg_stock", "width" => 60, "formatter" => "formatter.boolen"),
                 "出库不验货"   => array("field" => "is_not_need_examine", "width" => 60, "formatter" => "formatter.boolen"),
                 "允许0成本"   => array("field" => "is_allow_zero_cost", "width" => 60, "formatter" => "formatter.boolen"),
                 "允许低于成本价" => array("field" => "is_allow_lower_cost", "width" => 60, "formatter" => "formatter.boolen"),*/
            ),
            "goods_log"                  => array(
                "货品编码" => array("field" => "goods_no", "width" => 100),
                "商家编码" => array("field" => "spec_no", "width" => 100),
                "规格名称"  => array("field" => "spec_name", "width" => 100),
                "操作员"  => array("field" => "operator_id", "width" => 100),
                "内容"   => array("field" => "message", "width" => 150),
                "时间"   => array("field" => "created", "width" => 100),
            ),
            "goods_suite_detail"         => array(
                "id"   => array("field" => "id", "hidden" => true),
                "商家编码" => array("field" => "spec_no", "title" => "商家编码", "width" => "11%"),
                "货品编码" => array("field" => "goods_no", "tiitle" => "货品编码", "width" => "11%"),
                "货品名称" => array("field" => "goods_name", "title" => "货品名称", "width" => "11%"),
                "规格名称" => array("field" => "spec_name", "title" => "规格名称", "width" => "11%"),
                "规格码"  => array("field" => "spec_code", "title" => "规格码", "width" => "11%"),
                "可发库存" => array("field" => "avaliable_num", "title" => "可发库存", "width" => "11%"),
                "数量"   => array("field" => "num", "title" => "数量", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4,"min":0}}'),
                "单价"   => array("field" => "retail_price", "title" => "单价", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4,"min":0}}'),
                "金额占比" => array("field" => "ratio", "title" => "金额占比", "width" => "11%"),
                "固定价格" => array("field" => "is_fixed_price", "title" => "固定价格", "width" => "11%", "methods" => 'editor:{type: "checkbox", options: {on: 1, off: 0}}', "formatter" => "formatter.boolen"),
                /*"库存数量" => array("field" => "stock_num", "title" => "库存数量", "width" => "9%")*/
            ),
            "goods_suite_detail_unstock" => array(
                "id"   => array("field" => "id", "hidden" => true),
                "商家编码" => array("field" => "spec_no", "title" => "商家编码", "width" => "11%"),
                "货品编码" => array("field" => "goods_no", "tiitle" => "货品编码", "width" => "11%"),
                "货品名称" => array("field" => "goods_name", "title" => "货品名称", "width" => "11%"),
                "规格名称" => array("field" => "spec_name", "title" => "规格名称", "width" => "11%"),
                "规格码"  => array("field" => "spec_code", "title" => "规格码", "width" => "11%"),
                "数量"   => array("field" => "num", "title" => "数量", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4,"min":0}}'),
                "单价"   => array("field" => "retail_price", "title" => "单价", "width" => "11%", "methods" => 'editor:{"type":"numberbox","options":{"precision":4,"min":0}}'),
                "金额占比" => array("field" => "ratio", "title" => "金额占比", "width" => "11%"),
                "固定价格" => array("field" => "is_fixed_price", "title" => "固定价格", "width" => "11%", "methods" => 'editor:{type: "checkbox", options: {on: 1, off: 0}}', "formatter" => "formatter.boolen")
            ),
            "goods_suite_log"            => array(
                "id"  => array("field" => "id", "hidden" => true),
                "操作员" => array("field" => "fullname", "title" => "操作员", "width" => "200"),
                "内容"  => array("field" => "message", "title" => "内容", "width" => "500"),
                "时间"  => array("field" => "created", "title" => "时间", "width" => "200")
            ),
            "platform_goods"             => array(
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
            "platform_goods_log"         => array(
                "id"   => array("field" => "rec_id", "hidden" => true),
                "操作员"  => array("field" => "fullname", "title" => "操作员", "width" => 300, "sortable" => true),
                "操作内容" => array("field" => "message", "title" => "操作内容", "width" => 600, "sortable" => true),
                "操作时间" => array("field" => "created", "title" => "操作时间", "width" => 300, "sortable" => true)
            ),
            "system_goods"               => array(
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
            'spec_platform_goods'        => array(
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
                /*'自动上架'   => array('field' => 'is_auto_listing', 'width' => '100', "formatter" => "formatter.boolen"),
                '自动下架'   => array('field' => 'is_auto_delisting', 'width' => '100', "formatter" => "formatter.boolen"),*/
                '状态'     => array('field' => 'status', 'width' => '100', "formatter" => "formatter.api_goods_spec_status"),
                '自动匹配'   => array('field' => 'is_auto_match', 'width' => '100', "formatter" => "formatter.boolen"),
                '系统货品'   => array('field' => 'match_target_type', 'width' => '100', "formatter" => "formatter.match_target_type"),
                '最后同步库存' => array('field' => 'last_syn_num', 'width' => '100',),
                '最后同步时间' => array('field' => 'last_syn_time', 'width' => '100',),
                '最后修改时间' => array('field' => 'modified', 'width' => '100',),
            ),
            'goods_spec_log'             => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '操作员'  => array('field' => 'operator_id', 'width' => '100',),
                '操作描述' => array('field' => 'message', 'width' => '900'),
                '操作时间' => array('field' => 'created', 'width' => '150'),
            ),
            'goods_set_out_warehouse'             => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '店铺'  => array('field' => 'shop_id', 'width' => '350',),
                '仓库' => array('field' => 'warehouse_id', 'width' => '650'),
            ),
            'goods_set_out_logistics'             => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '店铺'  => array('field' => 'shop_id', 'width' => '350',),
                '仓库' => array('field' => 'warehouse_id', 'width' => '650'),
                '物流' => array('field' => 'logistics_id', 'width' => '650'),
            ),
            'out_warehouse_dialog'            => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                 '选中' => array('field' => 'is_select','align'=>'center', 'width'=>'50','formatter'=>'formatter.show_checkbox','methods'=>'editor:{type:"checkbox",options:{on:"1",off:"0"}}'),
                 '仓库' => array('field' => 'name', 'width' => '260'),                
                 '优先级(可编辑,数值越大优先级越高)' => array('field' => 'priority', 'width'=>'240','methods'=>'editor:{type:"numberbox",options:{min:1,max:10000}}'),
            ),
            'out_logistics_dialog'            => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '选中' => array('field' => 'is_select','align'=>'center', 'width'=>'50','formatter'=>'formatter.show_checkbox','methods'=>'editor:{type:"checkbox",options:{on:"1",off:"0"}}'),
                '物流' => array('field' => 'logistics_name', 'width' => '260'),
                '优先级(可编辑,数值越大优先级越高)' => array('field' => 'priority', 'width'=>'240','methods'=>'editor:{type:"numberbox",options:{min:1,max:10000}}'),
            )

        );
        return $fields[ $key ];
    }

}