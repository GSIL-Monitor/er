<?php
/**
 * 售后退款金额明细的field
 *
 */
namespace Statistics\Field;

use Common\Common\Field;

class StatSellbackAmountDetailField extends Field
{
    protected function get($key)
    {
        $fields = array(
            "stat_sellback_amount_detail" => array(
                '退换单号' => array('field' => 'refund_no', 'width' => '100','sortable'=>true),
                '店铺' => array('field' => 'shop_id', 'width' => '100','sortable'=>true),
                '类型' => array('field' => 'type', 'width' => '80','formatter'=>'formatter.refund_type','sortable'=>true),
                '建单者' => array('field' => 'operator_id', 'width'=>'80','sortable'=>true),
                '平台退款单号' => array('field' => 'src_no', 'width' => '120','sortable'=>true),
                '处理状态' => array('field' => 'process_status', 'width' => '80','formatter'=>'formatter.refund_process_status','sortable'=>true),
                '平台状态' => array('field' => 'status', 'width' => '80','formatter'=>'formatter.api_refund_status','sortable'=>true),
                '原始订单' => array('field' => 'tid', 'width' => '120','sortable'=>true),
                '系统订单' => array('field' => 'trade_no', 'width' => '120','sortable'=>true),
                '客户网名' => array('field' => 'buyer_nick', 'width' => '100','sortable'=>true),
                '姓名' => array('field' => 'receiver_name', 'width' => '100','sortable'=>true),
                '退换金额' => array('field' => 'goods_amount', 'width' => '100','sortable'=>true),
                 '实际应退金额' => array('field' => 'refund_amount', 'width' => '100','sortable'=>true),
                '退款类型'=>array('field'=>'reason_id','width'=>'100','sortable'=>true),//
                // '金额流向' => array('field' => 'flow_direction', 'width' => '100',),//'商家->买家','买家->商家'
                '退换物流' => array('field' => 'logistics_id', 'width' => '100','sortable'=>true),
                // '收付款账户' => array('field' => 'name', 'width' => '100',),               
                '发货仓库' => array('field' => 'send_warehouse_id', 'width' => '100','sortable'=>true),
                '发货物流' => array('field' => 'send_logistics_id', 'width' => '100','sortable'=>true),
                '发货物流单号' => array('field' => 'send_logistics_no', 'width' => '100','sortable'=>true),
                '发货时间' => array('field' => 'send_time', 'width' => '130','sortable'=>true),
                '买家账户' => array('field' => 'pay_account', 'width' => '100','sortable'=>true),
                // '开户银行' => array('field' => 'account_bank', 'width' => '100',),
                //'开户人姓名' => array('field' => 'account_name', 'width' => '100',),
                // '退款路径' => array('field' => 'refund_way', 'width' => '100',),//'平台退款','线下退款'
                //'退款阶段' => array('field' => 'cs_status', 'width' => '100',),
                '备注' => array('field' => 'remark', 'width' => '100','sortable'=>true),
                '建单时间' => array('field' => 'created', 'width' => '130','sortable'=>true),
                //'金额未确认' => array('field' => 'is_check_amount', 'width' => '100',),
                '最后修改时间' => array('field' => 'modified', 'width' => '130','sortable'=>true),
            ),
        );
        return $fields[$key];
    }
}