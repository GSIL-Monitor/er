<?php
namespace Trade\Common;

class TradeFields {

    /**
     * @param $name
     * @return array
     * 获取trade目录下相应的fields
     * author:luyanfeng
     */
    static public function getTradeFields($name) {
        $name = strtolower($name);
        if (isset(self::$fields[$name])) {
            return self::$fields[$name];
        } else {
            \Think\Log::write("unknown field:" . $name);
            return array();
        }
    }

    /**
     * @var array
     * 存储trade目录下的fields
     */
    static private $fields = array(
        "originalorder"  => array(
            "id"     => array("field" => "id", "hidden" => true),
            "平台"     => array("field"     => "platform_id", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.platform_id"),
            "店铺"     => array("field" => "shop_name", "width" => 150/*, "sortable" => true*/),
            "原始单号"   => array("field" => "tid", "width" => 150, "sortable" => true),
            "平台状态"   => array("field"     => "trade_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.api_trade_status"),
            "支付状态"   => array("field"     => "pay_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.pay_status"),
            "发货条件"   => array("field"     => "delivery_term", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.delivery_term"),
            "支付方式"   => array("field"     => "pay_method", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.pay_method"),
            "退货状态"   => array("field"     => "refund_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.refund_status"),
            "系统状态"   => array("field"     => "process_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.process_status"),//系统状态
            "下单时间"   => array("field" => "trade_time", "width" => 150, "sortable" => true),
            "支付时间"   => array("field" => "pay_time", "width" => 150, "sortable" => true),
            "客户网名"   => array("field" => "buyer_nick", "width" => 150, "sortable" => true),
            "收件人姓名"  => array("field" => "receiver_name", "width" => 150, "sortable" => true),//receiver_name
            "省市县"    => array("field" => "receiver_area", "width" => 150, "sortable" => true),
            "区域"     => array("field" => "receiver_district", "width" => 150, "sortable" => true),
            "收件地址"   => array("field" => "receiver_address", "width" => 150, "sortable" => true),
            "手机"     => array("field" => "receiver_mobile", "width" => 120, "sortable" => true),
            "电话"     => array("field" => "receiver_telno", "width" => 120, "sortable" => true),
            "邮编"     => array("field" => "receiver_zip", "width" => 100, "sortable" => true),
            "送货时间"   => array("field" => "to_deliver_time", "width" => 150, "sortable" => true),//送货时间 to_deliver_time
            "买家备注"   => array("field" => "buyer_message", "width" => 150, "sortable" => true),
            "客服备注"   => array("field" => "remark", "width" => 150, "sortable" => true),
            "标旗"   => array("field" => "remark_flag", "width" => 60, "sortable" => true),
            "货款"     => array("field" => "goods_amount", "width" => 100, "sortable" => true),
            "邮费"     => array("field" => "post_amount", "width" => 100, "sortable" => true),
            "其他收费"   => array("field" => "other_amount", "width" => 100, "sortable" => true),
            "优惠"     => array("field" => "discount", "width" => 100, "sortable" => true),
            "已付"     => array("field" => "paid", "width" => 100, "sortable" => true),
            "平台费用"   => array("field" => "platform_cost", "width" => 100, "sortable" => true),
            "已收"     => array("field" => "received", "width" => 100, "sortable" => true),
            "应收"     => array("field" => "receivable", "width" => 100, "sortable" => true),
            "款到发货金额" => array("field" => "dap_amount", "width" => 100, "sortable" => true),
            "货到付款金额" => array("field" => "cod_amount", "width" => 100, "sortable" => true),
            "退款金额"   => array("field" => "refund_amount", "width" => 100, "sortable" => true),
            "物流方式"   => array("field"     => "logistics_type", "width" => 150, "sortable" => true,
                              "formatter" => "formatter.logistics_type"),
            "发票类别"   => array("field"     => "invoice_type", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.invoice_type"),
            "发票抬头"   => array("field" => "invoice_title", "width" => 150, "sortable" => true),
            "发票内容"   => array("field" => "invoice_content", "width" => 150, "sortable" => true),
            "结束时间"   => array("field" => "end_time", "width" => 150, "sortable" => true),
            /*"业务员"    => array("field" => "fullname", "width" => 150, "sortable" => true),*/
            "订单来源"   => array("field"     => "trade_from", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.trade_from"),
            "修改时间"   => array("field" => "modified", "width" => 150, "sortable" => true),
            "创建时间"   => array("field" => "created", "width" => 150, "sortable" => true),
        ),
        "goodslisttabs"  => array(
            "id"     => array("field" => "id", "hidden" => true),
            "子订单编号"  => array("field" => "oid", "width" => 100, "sortable" => true),
            "店铺"     => array("field" => "shop_name", "width" => 100, "sortable" => true),
            "状态"     => array("field"     => "status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.api_trade_status"),
            "处理状态"   => array("field"     => "process_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.process_status"),
            "退款状态"   => array("field"     => "refund_status", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.order_refund_status"),
            "子订单类型"  => array("field"     => "order_type", "width" => 100, "sortable" => true,
                              "formatter" => "formatter.order_type"),
            "平台货品ID" => array("field" => "goods_id", "width" => 100, "sortable" => true),
            "平台规格ID" => array("field" => "spec_id", "width" => 100, "sortable" => true),
            "货品编码"   => array("field" => "goods_no", "width" => 100, "sortable" => true),
            "规格编码"   => array("field" => "spec_no", "width" => 100, "sortable" => true),
            "货品名"    => array("field" => "goods_name", "width" => 100, "sortable" => true),
            "规格名"    => array("field" => "spec_name", "width" => 100, "sortable" => true),
            "数量"     => array("field" => "num", "width" => 100, "sortable" => true),
            "单价"     => array("field" => "price", "width" => 100, "sortable" => true),
            "调整"     => array("field" => "adjust_amount", "width" => 100, "sortable" => true),
            "优惠"     => array("field" => "discount", "width" => 100, "sortable" => true),
            "总价"     => array("field" => "total_amount", "width" => 100, "sortable" => true),
            "分摊优惠"   => array("field" => "share_discount", "width" => 100, "sortable" => true),
            "分摊后应收"  => array("field" => "share_amount", "width" => 100, "sortable" => true),
            "分摊邮资"   => array("field" => "share_post", "width" => 100, "sortable" => true),
            // "发票类型"   => array("field"     => "invoice_type", "width" => 100, "sortable" => true, "formatter" => "formatter.invoice_type"),
            // "发票内容"   => array("field" => "invoice_content", "width" => 100, "sortable" => true),
            "关联子订单"  => array("field" => "bind_oid", "width" => 100, "sortable" => true),
            "退款单编号"  => array("field" => "refund_id", "width" => 100, "sortable" => true),
            "退款金额"   => array("field" => "refund_amount", "width" => 100, "sortable" => true),
            "修改时间"   => array("field" => "modfied", "width" => 100, "sortable" => true),
            "创建时间"   => array("field" => "created", "width" => 130, "sortable" => true)
        ),
        "salestradetabs" => array(
            "trade_id" => array("field" => "trade_id", "hidden" => true),
            "订单编号"     => array("field" => "trade_no", "width" => 100, "sortable" => true),
            "平台类型"     => array("field"     => "platform_id", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.platform_id"),
            "店铺名称"     => array("field" => "shop_name", "width" => 100, "sortable" => true),
            "仓库名称"     => array("field" => "name", "width" => 100, "sortable" => true),
            "仓库类型"     => array("field" => "warehosue_type", "width" => 100, "sortable" => true, "formatter" => "formatter.warehouse_type"),
            "原始单编号"    => array("field" => "src_tid", "width" => 100, "sortable" => true),
            "订单状态"     => array("field"     => "trade_status", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.trade_status"),
            "订单类型"     => array("field"     => "trade_type", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.trade_type"),
            "发货条件"     => array("field"     => "delivery_term", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.delivery_term"),
            "冻结原因"     => array("field" => "title", "width" => 100, "sortable" => true),
            "退款状态"     => array("field"     => "refund_status", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.refund_status"),
            "交易时间"     => array("field" => "trade_time", "width" => 100, "sortable" => true),
            "付款时间"     => array("field" => "pay_time", "width" => 100, "sortable" => true),
            "货品总数"     => array("field" => "goods_count", "width" => 100, "sortable" => true),
            "货品种类数"    => array("field" => "goods_type_count", "width" => 100, "sortable" => true),
            "客户网名"     => array("field" => "buyer_nick", "width" => 100, "sortable" => true),
            "收件人姓名"    => array("field" => "receiver_name", "width" => 100, "sortable" => true),
            "省市县"      => array("field" => "receiver_area", "width" => 100, "sortable" => true),
            "地址"       => array("field" => "receiver_address", "width" => 100, "sortable" => true),
            "收件人手机"    => array("field" => "receiver_mobile", "width" => 100, "sortable" => true),
            "收件人电话"    => array("field" => "receiver_telno", "width" => 100, "sortable" => true),
            "邮编"       => array("field" => "receiver_zip", "width" => 100, "sortable" => true),
            "区域"       => array("field" => "receiver_ring", "width" => 100, "sortable" => true),
            "大头笔"      => array("field" => "receiver_dtb", "width" => 100, "sortable" => true),
            "派送时间"     => array("field" => "to_deliver_time", "width" => 100, "sortable" => true),
            "物流公司"     => array("field" => "logistics_name", "width" => 100, "sortable" => true),
            "买家留言"     => array("field" => "buyer_message", "width" => 100, "sortable" => true),
            "客服备注"     => array("field" => "cs_remark", "width" => 100, "sortable" => true),
            "打印备注"     => array("field" => "print_remark", "width" => 100, "sortable" => true),
            "便签数"      => array("field" => "note_count", "width" => 100, "sortable" => true),
            "买家留言数"    => array("field" => "buyer_message_count", "width" => 100, "sortable" => true),
            "客服备注数"    => array("field" => "cs_remark_count", "width" => 100, "sortable" => true),
            "货品总额"     => array("field" => "goods_amount", "width" => 100, "sortable" => true),
            "邮资"       => array("field" => "post_amount", "width" => 100, "sortable" => true),
            "其他费用"     => array("field" => "other_amount", "width" => 100, "sortable" => true),
            "折扣"       => array("field" => "discount", "width" => 100, "sortable" => true),
            "应收金额"     => array("field" => "receivable", "width" => 100, "sortable" => true),
            "优惠金额变化"   => array("field" => "discount_change", "width" => 100, "sortable" => true),
            "款到发货金额"   => array("field" => "dap_amount", "width" => 100, "sortable" => true),
            "货到付款金额"   => array("field" => "cod_amount", "width" => 100, "sortable" => true),
            "货品预估成本"   => array("field" => "goods_cost", "width" => 100, "sortable" => true),
            "邮资成本"     => array("field" => "post_cost", "width" => 100, "sortable" => true),
            "已付金额"     => array("field" => "paid", "width" => 100, "sortable" => true),
            "预估重量"     => array("field" => "weight", "width" => 100, "sortable" => true),
            "发票类型"     => array("field"     => "invoice_type", "width" => 100, "sortable" => true,
                                "formatter" => "formatter.invoice_type"),
            "发票抬头"     => array("field" => "invoice_title", "width" => 100, "sortable" => true),
            "发票内容"     => array("field" => "invoice_content", "width" => 100, "sortable" => true),
            "业务员"      => array("field" => "fullname", "width" => 100, "sortable" => true),
            /*"审核人" => array("field" => "checker_id", "width" => 100, "sortable" => true),
            "财审人" => array("field" => "fchecker_id", "width" => 100, "sortable" => true),
            "签出人" => array("field" => "checkouter_id", "width" => 100, "sortable" => true),*/
            "不可拆分订单"   => array("field" => "is_sealed", "width" => 100, "sortable" => true, "formatter" => "formatter.boolen"),
            "出库单号"     => array("field" => "stockout_no", "width" => 100, "sortable" => true),
        ),
        "log"            => array(
            '操作员' => array('field' => 'fullname', 'width' => "25%"),
            '操作'  => array('field' => 'message', 'width' => "49%"),
            '时间'  => array('field' => 'created', 'width' => "25%"),
        )
    );

}