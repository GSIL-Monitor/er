<?php

namespace Statistics\Field;

use Common\Common\Field;

class SalesGoodsSpecMarketField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "sales_goods_spec_market" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '商家编码' => array('field' => 'spec_no', 'width' => 100),  
                '品牌' => array('field' => 'brand_id', 'width' => 100),
                '分类' => array('field' => 'class_id', 'width' => 100),
                '货品编号' => array('field' => 'goods_no', 'width' => 100),
                '货品名称' => array('field' => 'goods_name', 'width' => 100 ),
                '货品简称' => array('field' => 'short_name', 'width' => 100 ),
                '规格码' => array('field' => 'spec_code', 'width' => 100 ),
                '规格名称' => array('field' => 'spec_name', 'width' => 100 ),
                '均价' => array('field' => 'avg_price', 'width' => 100 ),
                '零售价' => array('field' => 'retail_price', 'width' => 100 ),
                '折扣率' => array('field' => 'discount_rate', 'width' => 100 ),
                '发货总量' => array('field' => 'num', 'width'=>100, 'sortable'=>true),
                '发货总金额' => array('field' => 'amount' , 'width' => 100 , 'sortable'=>true),
                '退款总量' => array('field' => 'refund_num','width'=>100),
                '退货总量' => array('field' => 'return_num','width'=>100),
                '赠品总量' => array('field' => 'gift_num' , 'width' => 100),
                '换货总量' => array('field' => 'swap_num' , 'width' => 100),
                '实际销售量' => array('field' => 'actual_num', 'width' => 100 ),
                '邮资收入' => array('field' => 'post_amount', 'width' => 100 ),
                '未知成本销售总额' => array('field' => 'unknown_goods_amount', 'width' => 120 ),
                '货品总成本' => array('field' => 'goods_cost', 'width' => 100 ),
                '货品总利润' => array('field' => 'profit', 'width' => 100 ),
                '退货总金额' => array('field' => 'return_amount', 'width' => 100 ),
                '退货总成本' => array('field' => 'return_cost', 'width' => 100 ),
                '实际销售额' => array('field' => 'actual_amount', 'width' => 100 ),
                '实际货品总成本' => array('field' => 'actual_goods_cost', 'width' => 100 ),
                '实际货品总利润' => array('field' => 'actual_profit', 'width' => 100 ),
        ),
);
        return $fields[$key];
    }
}