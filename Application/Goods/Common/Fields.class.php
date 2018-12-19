<?php
namespace Goods\Common;
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/22
 * Time: 下午12:52
 */
class Fields {
    static public function getGoodsFields($field_id = '') {
        $field_id = strtolower($field_id);
        if (!empty($field_id)) {
            switch ($field_id) {
                case 'goods_spec':
                    $fields = array(
                        'id'    => array('field' => 'id', 'hidden' => true,),
                        '商家编码'  => array('field' => 'spec_no', 'width' => '100',),
                        '货品名称'  => array('field' => 'goods_name', 'width' => '100',),
                        '简称'    => array('field' => 'short_name', 'width' => '100',),
                        '货品编码'  => array('field' => 'goods_no', 'width' => '100',),
                        '分类'    => array('field' => 'class_id', 'width' => '100',),
                        '品牌'    => array('field' => 'brand_id', 'width' => '100', "formatter" => 'brandFormatter'),
                        '规格名称'  => array('field' => 'spec_name', 'width' => '100',),
                        '规格码'   => array('field' => 'spec_code', 'width' => '100',),
                        '主条码'   => array('field' => 'barcode', 'width' => '100',),
                        '零售价'   => array('field' => 'retail_price', 'width' => '100',),
                        '批发价'   => array('field' => 'wholesale_price', 'width' => '100',),
                        '会员价'   => array('field' => 'member_price', 'width' => '100',),
                        '市场价'   => array('field' => 'market_price', 'width' => '100',),
                        '最低价'   => array('field' => 'lowest_price', 'width' => '100',),
                        '有效期天数' => array('field' => 'validity_days', 'width' => '100',),
                        '长'     => array('field' => 'length', 'width' => '100',),
                        '宽'     => array('field' => 'width', 'width' => '100',),
                        '高'     => array('field' => 'height', 'width' => '100',),
                        '销售积分'  => array('field' => 'sale_score', 'width' => '100',),
                        '打包积分'  => array('field' => 'pack_score', 'width' => '100',),
                        '拣货积分'  => array('field' => 'pick_score', 'width' => '100',),
                        '重量(kg)'    => array('field' => 'weight', 'width' => '100',),
                        '税率'    => array('field' => 'tax_rate', 'width' => '100',),
//                        '启用序列号' => array('field' => 'is_sn_enable', 'width' => '100',),
//                        '允许负库存' => array('field' => 'is_allow_neg_stock', 'width' => '100',),
//                        '出库不验货' => array('field' => 'is_not_need_examine', 'width' => '100',),
//                        '允许0成本' => array('field' => 'is_zero_cost', 'width' => '100',),
//                        '允许低于成本价' => array('field' => 'is_lower_cost', 'width' => '100',),
//                        '大件类别' => array('field' => 'large_type', 'width' => '100',),
                        '基本单位'  => array('field' => 'unit', 'width' => '100',),
//                        '辅助单位' => array('field' => 'aux_unit', 'width' => '100',),
//                '0' => array('field' => 'prop1', 'width' => '100',),
//                '1' => array('field' => 'prop2', 'width' => '100',),
//                '2' => array('field' => 'prop3', 'width' => '100',),
//                '3' => array('field' => 'prop4', 'width' => '100',),
//                '4' => array('field' => 'prop5', 'width' => '100',),
//                '5' => array('field' => 'prop6', 'width' => '100',),
                        '备注'    => array('field' => 'remark', 'width' => '100',),
                    );
                    return $fields;
                case 'platform_goods':
                    $fields = array(
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
                        '自动上架'   => array('field' => 'is_auto_listing', 'width' => '100', "formatter" => "formatter.boolen"),
                        '自动下架'   => array('field' => 'is_auto_delisting', 'width' => '100', "formatter" => "formatter.boolen"),
                        '状态'     => array('field' => 'status', 'width' => '100', "formatter" => "formatter.api_goods_spec_status"),
                        '自动匹配'   => array('field' => 'is_auto_match', 'width' => '100', "formatter" => "formatter.boolen"),
                        '系统货品'   => array('field' => 'match_target_type', 'width' => '100', "formatter" => "formatter.match_target_type"),
                        '最后同步库存' => array('field' => 'last_syn_num', 'width' => '100',),
                        '最后同步时间' => array('field' => 'last_syn_time', 'width' => '100',),
                        '最后修改时间' => array('field' => 'modified', 'width' => '100',),
                    );
                    return $fields;

                default:
                    return;
            }
        }
    }

    static public function getTabDatagrid($DatagridId, $fields, $url, $tool_bar) {
        $datagrid = array(
            'id'      => $DatagridId,
            'class'   => 'easyui-datagrid',
            'options' => array(
                'title'        => '',
                'url'          => $url,
                'toolbar'      => "#{$tool_bar}",
                'fitColumns'   => false,
                'singleSelect' => false,
                'ctrlSelect'   => true
            ),
            'fields'  => $fields,
            'style'   => "overflow:scroll",
        );
        return $datagrid;
    }
}