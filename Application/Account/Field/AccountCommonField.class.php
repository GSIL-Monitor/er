<?php
namespace Account\Field;

use Common\Common\Field;

class AccountCommonField extends Field {

    protected function get($key) {
        $fields = array(
            "goods_detail" => array(
                "订单编号"  => array("field" => "trade_no", "width" => "12%"),
                "唯一码"   => array("field" => "unique_code", "width" =>"15%"),
                //"规格"  => array("field" => "spec_name", "width" => "10%"),
                //"属性"   => array("field" => "spec_code", "width" => "8%"),
                "采购员"   => array("field" => "purchaser_id", "width" => "12%"),
                "采购单编号"   => array("field" => "stalls_no", "width" => "12%"),
                "采购价"   => array("field" => "detail_price", "width" => "8%"),
                "数量"   => array("field" => "num", "width" => "8%"),
                "采购总金额"   => array("field" => "price", "width" => "8%"),
                "入库日期" => array("field" => "stockin_time", "width" => "15%"),
                 ),
            'stalls_purchaser_goods_detail' => array(
                //"订单编号"  => array("field" => "trade_no", "width" => "10%"),
                "采购单编号"   => array("field" => "stalls_no", "width" => "8%"),
                "货品编码"   => array("field" => "goods_no", "width" =>"10%"),
                "货品名称"   => array("field" => "goods_name", "width" =>"10%"),
                "商家编码"   => array("field" => "spec_no", "width" =>"12%"),
                "入库数量"   => array("field" => "in_num", "width" =>"8%"),
                "取货数量"   => array("field" => "put_num", "width" =>"8%"),
                "总金额"   => array("field" => "total_price", "width" => "8%"),
                "取货时间" => array("field" => "purchase_time", "width" => "12%")
            ),
            'sales_trade' => array(
                "货品名称"   => array("field" => "goods_name", "width" => "12%"),
                "规格名称"   => array("field" => "spec_name", "width" =>"12%"),
                "商家编码"   => array("field" => "spec_no", "width" =>"12%"),
                "销售金额"   => array("field" => "share_amount", "width" =>"12%"),
                "邮资金额"   => array("field" => "share_post", "width" =>"12%"),
                "总金额"   => array("field" => "amount", "width" =>"12%"),
                "是否发货"   => array("field" => "is_consigned", "width" => "12%",'formatter'=>'formatter.boolen'),
                "是否退款" => array("field" => "is_refund", "width" => "12%",'formatter'=>'formatter.boolen')
            ),
            'alipay_account_bill' => array(
                "财务类型"   => array("field" => "item", "width" => "12%"),
                "支付单号"   => array("field" => "pay_order_no", "width" =>"12%"),
                "对方支付宝帐号"   => array("field" => "opt_pay_account", "width" =>"12%"),
                "收入"   => array("field" => "in_amount", "width" =>"8%"),
                "支出"   => array("field" => "out_amount", "width" =>"8%"),
                "支付总额"   => array("field" => "amount", "width" =>"8%"),
                "余额"   => array("field" => "balance", "width" => "8%"),
                "备注" => array("field" => "remark", "width" => "32%")
            ),
            'payment_bill' => array(
                "收付款单号"   => array("field" => "payment_no", "width" => "8%"),
                "状态"   => array("field" => "payment_status", "width" =>"10%"),
                "客户网名"   => array("field" => "obj_name", "width" =>"10%"),
                "收入"   => array("field" => "in_amount", "width" =>"12%"),
                "支出"   => array("field" => "out_amount", "width" =>"8%"),
            ),
            'alipay_account_check_log' => array(
                '操作员' => array('field' => 'operator_id', 'width' => '33%'),
                '操作内容' => array('field' => 'message', 'width' => '33%'),
                '操作时间' => array('field' => 'created', 'width' => '34%'),
            )

        );
        return $fields[ $key ];
    }

}