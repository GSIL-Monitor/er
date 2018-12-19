<?php
/**
 * 销售日统计的field
 *
 * @author gaosong
 * @date: 15/12/20
 * @time: 下午03:00
 */
namespace Statistics\Field;

use Common\Common\Field;

class SalesAmountGoodsSpecField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "sales_amount_goods_spec" => array(
                '商家编码' => array('field' => 'spec_no', 'width' => 100),
                '店铺' => array('field' => 'shop_id', 'width' => 100,'sortable' => true),
                '仓库' => array('field' => 'warehouse_name', 'width' => 100,'sortable' => true,'hidden'=>true),
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
                '下单量' => array('field' => 'order_num', 'width' => 100,'hidden'=>true ),
                '发货总量' => array('field' => 'num', 'width' => 100 ),
                '发货总金额' => array('field' => 'amount', 'width' => 100 ),
                '退款总量' => array('field' => 'refund_num', 'width' => 150 ),
                '退款总金额' => array('field' => 'refund_amount','width' => 100),
                '退货总量' => array('field' => 'return_num', 'width' => 100 ),
                '赠品总量' => array('field' => 'gift_num' , 'width' => 100),
                '换货总量' => array('field' => 'swap_num' , 'width' => 100),
                '实际销售量' => array('field' => 'actual_num', 'width' => 100 ),
                '邮资收入' => array('field' => 'post_amount', 'width' => 100 ),
                '未知成本销售总额' => array('field' => 'unknown_goods_amount', 'width' => 120 ),
                //'佣金成本' => array('field' => 'commission', 'width' => 100 ),
                '货品总成本' => array('field' => 'goods_cost', 'width' => 100 ),
                '货品总利润' => array('field' => 'profit', 'width' => 100 ),
                '退货总金额' => array('field' => 'return_amount', 'width' => 100 ),
                '平台退款金额'=>array('field'=>'guarante_refund_amount','width'=>100),
                '退货总成本' => array('field' => 'return_cost', 'width' => 100 ),
                '实际销售额' => array('field' => 'actual_amount', 'width' => 100 ),
                '实际货品总成本' => array('field' => 'actual_goods_cost', 'width' => 100 ),
                '实际货品总利润' => array('field' => 'actual_profit', 'width' => 100 ),

                //'系统发货但平台退款金额' => array('field' => 'refund_amount', 'width' => 150 ),

            ),
            "sales_amount_goods_spec_by_warehouse" => array(
                '商家编码' => array('field' => 'spec_no', 'width' => 100),
                '店铺' => array('field' => 'shop_id', 'width' => 100,'sortable' => true,'hidden'=>true),
                '仓库' => array('field' => 'warehouse_name', 'width' => 100,'sortable' => true),
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
                '发货总量' => array('field' => 'num', 'width' => 100 ),
                '发货总金额' => array('field' => 'amount', 'width' => 100 ),
                '退款总量' => array('field' => 'refund_num', 'width' => 150 ),
                '退款总金额' => array('field' => 'refund_amount','width' => 100),
                '退货总量' => array('field' => 'return_num', 'width' => 100 ),
                '赠品总量' => array('field' => 'gift_num' , 'width' => 100),
                '换货总量' => array('field' => 'swap_num' , 'width' => 100),
                '实际销售量' => array('field' => 'actual_num', 'width' => 100 ),
                '邮资收入' => array('field' => 'post_amount', 'width' => 100 ),
                '未知成本销售总额' => array('field' => 'unknown_goods_amount', 'width' => 120 ),
                //'佣金成本' => array('field' => 'commission', 'width' => 100 ),
                '货品总成本' => array('field' => 'goods_cost', 'width' => 100 ),
                '货品总利润' => array('field' => 'profit', 'width' => 100 ),
                '退货总金额' => array('field' => 'return_amount', 'width' => 100 ),
                '平台退款金额'=>array('field'=>'guarante_refund_amount','width'=>100),
                '退货总成本' => array('field' => 'return_cost', 'width' => 100 ),
                '实际销售额' => array('field' => 'actual_amount', 'width' => 100 ),
                '实际货品总成本' => array('field' => 'actual_goods_cost', 'width' => 100 ),
                '实际货品总利润' => array('field' => 'actual_profit', 'width' => 100 ),
                //'系统发货但平台退款金额' => array('field' => 'refund_amount', 'width' => 150 ),

            ),
            "sales_amount_goods_spec_by_pay_time" => array(
                '商家编码' => array('field' => 'spec_no', 'width' => 100),
                '店铺' => array('field' => 'shop_id', 'width' => 100),
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
                '下单量' => array('field' => 'order_num', 'width' => 100),
                '实际销售量' => array('field' => 'actual_num', 'width' => 100 ),
                '邮资收入' => array('field' => 'post_amount', 'width' => 100 )
            ),
        );
        return $fields[$key];
    }
}