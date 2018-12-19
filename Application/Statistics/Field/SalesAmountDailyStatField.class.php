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

class SalesAmountDailyStatField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stat_sales_daysell" => array(
                '日期' => array('field' => 'sales_date', 'width' => '100',),
                '店铺' => array('field' => 'shop_id', 'width' => '100',),
                '仓库' => array('field' => 'warehouse_name', 'width'=>'100'),
                '新增订单数' => array('field' => 'new_trades', 'width' => '100',),
                '新增订单金额' => array('field' => 'new_trades_amount', 'width' => '100',),
                '审核订单数' => array('field' => 'check_trades', 'width' => '100', ),
                '审核订单金额' => array('field' => 'check_trades_amount', 'width' => '100',),
                '发货订单数' => array('field' => 'send_trades', 'width' => '100',),
                '发货订单金额' => array('field' => 'send_trades_amount', 'width' => '100',),
                '零成本价出库销售总额' => array('field' => 'send_unknown_goods_amount', 'width' => '100',),
                '发货货品成本' => array('field' => 'send_goods_cost', 'width' => '100',),
//                 '佣金成本' => array('field' => 'commission', 'width' => '100',),
//                 '物流佣金成本' => array('field' => 'other_cost', 'width' => '100',),
                '邮资成本' => array('field' => 'post_cost', 'width' => '100',),
                '发货订单毛利' => array('field' => 'send_trade_profit', 'width' => '100',),
//                 '实收邮资' => array('field' => 'post_amount', 'width' => '100',),
//                 '邮资收益' => array('field' => 'post_profit', 'width' => '100',),
//                 '包装成本' => array('field' => 'package_cost', 'width' => '100',),
                '已发订单均价' => array('field' => 'send_trade_avg_price','width'=> '100')
            ),
        );
        return $fields[$key];
    }
}