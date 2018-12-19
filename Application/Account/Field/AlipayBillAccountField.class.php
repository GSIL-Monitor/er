<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/6/14
 * Time: 4:51
 */
namespace Account\Field;

use Common\Common\Field;

class AlipayBillAccountField extends Field {
    protected function get($key) {
        $fields = array(
            /*"alipay_bill_account" => array(
                'id'  => array('field' => 'rec_id', 'hidden' => true, 'sortable' => true),
                '店铺' => array('field' => 'shop_name', 'width' => 100,'sortable' => true),
                '支付宝交易号' => array('field' => 'alipay_order_no', 'width' => 200),
                '商户订单号' => array('field' => 'raw_order_no', 'width' => 120),
                '商户类型'   => array('field' => 'item', 'width' => 120),
                '商品名称' => array('field' => 'goods_name', 'width' => 100),
                '账单创建时间' => array('field' => 'alipay_create_time', 'width' => 130),
                '账单完成时间' => array('field' => 'alipay_complete_time', 'width' => 130),
                '对方支付宝账号' => array('field' => 'opt_pay_account', 'width' => 130),
                '订单金额(元)'  => array('field' => 'order_amount', 'width' => 120,'sortable' => true),
                '商家实收金额(元)'  => array('field' => 'in_amount', 'width' => 120,'sortable' => true),
                '支付宝红包(元)'  => array('field' => 'alipay_red_packet', 'width' => 120,'sortable' => true),
                '集分宝(元)'  => array('field' => 'ollection_treasure', 'width' => 120,'sortable' => true),
                '支付宝优惠(元)'  => array('field' => 'alipay_preferential', 'width' => 120,'sortable' => true),
                '商家优惠(元)'  => array('field' => 'seller_preferential', 'width' => 120,'sortable' => true),
                '券核销金额(元)'  => array('field' => 'amount_voucher', 'width' => 120,'sortable' => true),
                '券名称'  => array('field' => 'amount_name', 'width' => 100),
                '商家红包消费金额(元)'  => array('field' => 'seller_red_packet', 'width' => 130,'sortable' => true),
                '卡消费金额(元)'  => array('field' => 'card_consumption', 'width' => 120,'sortable' => true),
                '退款批次号/请求号'  => array('field' => 'refund_batch_no', 'width' => 120),
                '服务费(元)'  => array('field' => 'service_amount', 'width' => 120,'sortable' => true),
                '分润(元)'  => array('field' => 'share_benefit', 'width' => 120,'sortable' => true),
                '备注'   => array('field' => 'remark', 'width' => 120),
            ),*/
             "alipay_bill_account" => array(
                 'id'  => array('field' => 'rec_id', 'hidden' => true, 'sortable' => true),
                 '店铺' => array('field' => 'shop_name', 'width' => 100,'sortable' => true),
                 '账务流水号' => array('field' => 'financial_no', 'width' => 200),
                 '业务流水号' => array('field' => 'business_no', 'width' => 200),
                 '商户订单号' => array('field' => 'merchant_order_no', 'width' => 200),
                 '关联原始单号' => array('field' => 'order_no', 'width' => 200),
                 '账务类型'   => array('field' => 'item', 'width' => 120,'sortable'=>true),
                 '商品名称' => array('field' => 'goods_name', 'width' => 100),
                 '账单创建时间' => array('field' => 'create_time', 'width' => 130),
                 '对方账号' => array('field' => 'opt_pay_account', 'width' => 130),
                 '收入金额(元)'  => array('field' => 'in_amount', 'width' => 120,'sortable' => true),
                 '支出金额(元)'  => array('field' => 'out_amount', 'width' => 120,'sortable' => true),
                 '订单金额(元)'  => array('field' => 'balance', 'width' => 120,'sortable' => true),
                 '备注'   => array('field' => 'remark', 'width' => 250),
            ),
            'account_summary' => array
            (
                'id'  => array('field' => 'rec_id', 'hidden' => true, 'sortable' => true),
                '店铺' => array('field' => 'shop_name', 'width' => 100,'sortable' => true),
                '账务类型'   => array('field' => 'item', 'width' => 120,'sortable'=>true),
                '收入笔数'  => array('field' => 'in_num', 'width' => 80,'sortable' => true),
                '收入金额(元)'  => array('field' => 'in_amount', 'width' => 100,'sortable' => true),
                '支出笔数'  => array('field' => 'out_num', 'width' => 80,'sortable' => true),
                '支出金额(元)'  => array('field' => 'out_amount', 'width' => 100,'sortable' => true),
                '合计(元)'  => array('field' => 'total_amount', 'width' => 100,'sortable' => true),
                '日期'  => array('field' => 'create_time', 'width' => 120,'sortable' => true),

            )
        );
        return $fields[$key];
    }
}