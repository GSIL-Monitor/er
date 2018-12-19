<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:32
 */
namespace Goods\Field;

use Common\Common\Field;

class GoodsSpecField extends Field {
    protected function get($key) {
        $fields = array(
            "goods_spec" => array(
                'id'    => array('field' => 'id', 'hidden' => true,),
                '商家编码'  => array('field' => 'spec_no', 'width' => '100',),
                '货品名称'  => array('field' => 'goods_name', 'width' => '100',),
                '简称'    => array('field' => 'short_name', 'width' => '100',),
                '货品编码'  => array('field' => 'goods_no', 'width' => '100',),
                '分类'    => array('field' => 'class_id', 'width' => '100', 'formatter' => 'classFormatter'),
                '品牌'    => array('field' => 'brand_id', 'width' => '100', "formatter" => 'brandFormatter'),
                '规格名称'  => array('field' => 'spec_name', 'width' => '100',),
                '规格码'   => array('field' => 'spec_code', 'width' => '100',),
                '主条码'   => array('field' => 'barcode', 'width' => '100',),
                '是否爆款'   =>array('field'=> 'is_hotcake','width' => '100','formatter' => 'formatter.boolen'),
                '允许负库存出库'   =>array('field'=> 'is_allow_neg_stock','width' => '130','formatter' => 'formatter.boolen'),
                '零售价'   => array('field' => 'retail_price', 'width' => '100',),
                //'批发价' => array('field' => 'wholesale_price', 'width' => '100',),
                //'会员价' => array('field' => 'member_price', 'width' => '100',),
                '市场价'   => array('field' => 'market_price', 'width' => '100',),
                '最低价'   => array('field' => 'lowest_price', 'width' => '100',),
                '有效期天数' => array('field' => 'validity_days', 'width' => '100',),
                '长'     => array('field' => 'length', 'width' => '100',),
                '宽'     => array('field' => 'width', 'width' => '100',),
                '高'     => array('field' => 'height', 'width' => '100',),
                /*'销售积分' => array('field' => 'sale_score', 'width' => '100',),
                '打包积分' => array('field' => 'pack_score', 'width' => '100',),
                '拣货积分' => array('field' => 'pick_score', 'width' => '100',),*/
                '重量(kg)'    => array('field' => 'weight', 'width' => '100',),
                /*'税率'    => array('field' => 'tax_rate', 'width' => '100',),*/
//                        '启用序列号' => array('field' => 'is_sn_enable', 'width' => '100',),
//                        '允许负库存' => array('field' => 'is_allow_neg_stock', 'width' => '100',),
//
//                        '允许0成本' => array('field' => 'is_zero_cost', 'width' => '100',),
//                        '允许低于成本价' => array('field' => 'is_lower_cost', 'width' => '100',),
                '无需验货' => array('field' => 'is_not_need_examine', 'width' => '100','formatter' => 'formatter.boolen'),
                        '大件类别' => array('field' => 'large_type', 'width' => '100','formatter' => 'formatter.large_type'),
                ///'基本单位'  => array('field' => 'name', 'width' => '100',),
//                        '辅助单位' => array('field' => 'aux_unit', 'width' => '100',),
                '自定义1' => array('field' => 'prop1', 'width' => '100',),
                '自定义2' => array('field' => 'prop2', 'width' => '100',),
                '自定义3' => array('field' => 'prop3', 'width' => '100',),
                '自定义4' => array('field' => 'prop4', 'width' => '100',),
//                '4' => array('field' => 'prop5', 'width' => '100',),
//                '5' => array('field' => 'prop6', 'width' => '100',),
                '备注'    => array('field' => 'remark', 'width' => '100',),
            ),
        );
        return $fields[ $key ];
    }
}