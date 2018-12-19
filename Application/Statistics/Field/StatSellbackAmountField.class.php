<?php
/**
 * 售后退款金额的field
 *
 
 */
namespace Statistics\Field;

use Common\Common\Field;

class StatSellbackAmountField extends Field
{
    protected function get($key)
    {
        $fields = array(
            "stat_sellback_amount" => array(
                '退款单号' => array('field' => 'refund_no', 'width' => '100','sortable'=>true),
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
                '支付账户' => array('field' => 'pay_account', 'width' => '100','sortable'=>true),
                '退换金额' => array('field' => 'goods_amount', 'width' => '100','sortable'=>true),
                '实际应退金额' => array('field' => 'refund_amount', 'width' => '100','sortable'=>true),
                '已退款' => array('field' => 'paid', 'width' => '100','sortable'=>true),
                '实际应收金额' => array('field' => 'receive_amount', 'width' => '100','sortable'=>true),
                '已收款' => array('field' => 'received', 'width' => '100','sortable'=>true),
                '邮费' => array('field' => 'post_amount', 'width' => '100','sortable'=>true),
                '退款完成日期' => array('field' => 'modified', 'width' => '140','sortable'=>true),
                '退款原因' => array('field' => 'reason_id', 'width' => '100','sortable'=>true),
                '备注' => array('field' => 'remark', 'width' => '100','sortable'=>true),
            ),
        );
        return $fields[$key];
    }
}