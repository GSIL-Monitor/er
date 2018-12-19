<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/6/14
 * Time: 4:51
 */
namespace Account\Field;

use Common\Common\Field;

class AlipayAccountCheckField extends Field {
    protected function get($key) {
        $fields = array(
            "alipay_account_check" => array(
                'id'  => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                '对账单号' => array('field' => 'account_check_no', 'width' => 100),
                '原始单号' => array('field' => 'tid', 'width' => 100),
                '平台' => array('field' => 'platform_id', 'width' => 80,'formatter'=>'formatter.platform_id'),
                '店铺' => array('field' => 'shop_id', 'width' => 80),
                '已收金额(元)' => array('field' => 'pay_amount', 'width' => 100, 'sortable' => true),
                '发货金额(元)'   => array('field' => 'send_amount', 'width' => 100, 'sortable' => true),
                '售中退款金额(元)' => array('field' => 'refund_amount', 'width' => 100, 'sortable' => true),
                '费用(元)' => array('field' => 'cost_amount', 'width' => 100, 'sortable' => true),
                '确认收货金额(元)' => array('field' => 'confirm_amount', 'width' => 100, 'sortable' => true),
                '对账状态'  => array('field' => 'status', 'width' => 100,'formatter'=>'formatter.account_check_status'),
                '平台退款状态'  => array('field' => 'refund_status', 'width' => 100,'formatter'=>'formatter.refund_status'),
                '是否全部发货'  => array('field' => 'is_send_all', 'width' => 80,'formatter'=>'formatter.boolen'),
                '对账时间'   => array('field' => 'check_time', 'width' => 120, 'sortable' => true),
                '发货时间'   => array('field' => 'consign_time', 'width' => 120, 'sortable' => true),
                '确认收货时间'   => array('field' => 'confirm_time', 'width' => 120, 'sortable' => true),
            ),
        );
        return $fields[$key];
    }
}