<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/4
 * Time: 11:59
 */
namespace Customer\Common;

class CustomerFields {

    static public function getCustomerFields($name) {
        $name = strtolower($name);
        if (isset(self::$fields[ $name ])) {
            $field_arr = self::$fields[ $name ];
            $employee_id=get_operator_id();
            $rights_list_count=M('cfg_employee_rights')->where(array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',3)))->count();
            //默认都有权限
            if($rights_list_count == 0){
                return $field_arr;
            }
            $cfg_fields_model = M('cfg_fields');
            $rights_arr = $cfg_fields_model->query("select cf.field_id from cfg_fields cf left join cfg_employee_rights cer on cf.field_id=cer.right_id
                 where cer.type=3 and cer.employee_id='{$employee_id}' and cer.is_denied=0");
            $where = 'where true';
            if(!empty($rights_arr)){
                $tmp_arr = array_column($rights_arr,'field_id');
                $where .= " and field_id not in (".implode(",",$tmp_arr).")";
            }else{
                $denied_fields_arr = $cfg_fields_model->query("select cf.field_id from cfg_fields cf left join cfg_employee_rights cer on cf.field_id=cer.right_id
                 where cer.type=3 and cer.employee_id='{$employee_id}' and cer.is_denied=1");
                if(!empty($denied_fields_arr)){
                    $tmp_arr = array_column($denied_fields_arr,'field_id');
                    $where .= " and field_id in (".implode(",",$tmp_arr).")";
                }
            }
            $no_rights_field = $cfg_fields_model->query("select field_no,field_name from cfg_fields $where");
            foreach($no_rights_field as $nk=>$nv){
                foreach($field_arr as $fk=>$fv){
                    if($nv['field_name'] == $fk && $nv['field_no'] == $fv['field']){
                        $field_arr[$fk]['hidden'] = true;
                    }
                }
            }
            return $field_arr;
        } else {
            \Think\Log::write("unknown fields " . $name);
            return array();
        }
    }

    static private $fields = array(
        "customerfile"     => array(
            "checkbox"=>array('field' => "ck", 'checkbox' => true),
            "id"   => array("field" => "id", "hidden" => true),
            "客户类别" => array("field"     => "type", "title" => "客户类别", "width" => 100, "sortable" => true,
                            "formatter" => "formatter.customer_type"),
            "客户编号" => array("field" => "customer_no", "title" => "客户编号", "width" => 100, "sortable" => true),
            "姓名"   => array("field" => "name", "title" => "姓名", "width" => 100, "sortable" => true),
            "客户网名" => array("field" => "nickname", "title" => "客户网名", "width" => 100, "sortable" => true),
            "客户标签" => array("field" => "class_name", "title" => "客户标签", "width" => 100, "sortable" =>true),
        	"购买总次数"=>array("field"=>"trade_count","title"=>"购买总次数","width"=>100,"sortable"=>true),
        	"购买总金额"=>array("field"=>"trade_amount","title"=>"购买总金额","width"=>100,"sortable"=>true),
        	"订单总利润"=>array("field"=>"profit","title"=>"订单总利润","width"=>100,"sortable"=>true),
//         	"退款总金额"=>array("field"=>"refund_amount","title"=>"退款总金额","width"=>100,"sortable"=>true),
        	"性别"   => array("field"     => "sex", "title" => "性别", "width" => 100, "sortable" => true,
                            "formatter" => "formatter.sex"),
            /*"国家" => array("field" => "country", "title" => "国家", "width" => 100, "sortable" => true),*/
            "省份"   => array("field"     => "province", "title" => "省份", "width" => 100, "sortable" => true,
                            "formatter" => "area.province"),
            "城市"   => array("field"     => "city", "title" => "城市", "width" => 100, "sortable" => true,
                            "formatter" => "area.city"),
            "区县"   => array("field"     => "district", "title" => "区县", "width" => 100, "sortable" => true,
                            "formatter" => "area.district"),
            "地址"   => array("field" => "address", "title" => "地址", "width" => 100, "sortable" => true),
            "邮编"   => array("field" => "zip", "title" => "邮编", "width" => 100, "sortable" => true),
            "固话"   => array("field" => "telno", "title" => "固话", "width" => 100, "sortable" => true),
            "手机"   => array("field" => "mobile", "title" => "手机", "width" => 100, "sortable" => true),
            "电子邮件" => array("field" => "email", "title" => "电子邮件", "width" => 100, "sortable" => true),
            "QQ"   => array("field" => "qq", "title" => "QQ", "width" => 100, "sortable" => true),
            "旺旺"   => array("field" => "wangwang", "title" => "旺旺", "width" => 100, "sortable" => true),
            "生日"   => array("field" => "birthday", "title" => "生日", "width" => 100, "sortable" => true),
            "黑名单"  => array("field"     => "is_black", "title" => "黑名单", "width" => 100, "sortable" => true,
                            "formatter" => "formatter.boolen"),
            "停用"   => array("field"     => "is_disabled", "title" => "停用", "width" => 100, "sortable" => true,
                            "formatter" => "formatter.boolen"),
            "修改时间" => array("field" => "modified", "title" => "修改时间", "width" => 100, "sortable" => true),
            "登记时间" => array("field" => "created", "title" => "登记时间", "width" => 100, "sortable" => true),
            "备注"   => array("field" => "remark", "title" => "备注", "width" => 100, "sortable" => true)
        ),
        "customeraddress"  => array(
            "id"   => array("field" => "id", "hidden" => true),
            "姓名"   => array("field" => "name", "title" => "姓名", "width" => "10%"),
            "省"    => array("field" => "province", "title" => "省", "width" => "5%", "formatter" => "area.province"),
            "城市"   => array("field" => "city", "title" => "城市", "width" => "5%", "formatter" => "area.city"),
            "区县"   => array("field" => "district", "title" => "区县", "width" => "5%", "formatter" => "area.district"),
            "地址"   => array("field" => "address", "title" => "地址", "width" => "15%"),
            "邮编"   => array("field" => "zip", "title" => "邮编", "width" => "9%"),
            "手机"   => array("field" => "mobile", "title" => "手机", "width" => "10%"),
            "固话"   => array("field" => "telno", "title" => "固话", "width" => "10%"),
            "邮箱"   => array("field" => "email", "title" => "邮箱", "width" => "10%"),
            "修改时间" => array("field" => "modified", "title" => "修改时间", "width" => "10%"),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => "10%")
        ),
        "customertelno"    => array(
            "id"   => array("field" => "id", "hidden" => true),
            "号码类型" => array("field" => "type", "title" => "号码类型", "width" => "10%", "formatter" => "formatter.telno_type"),
            "号码"   => array("field" => "telno", "title" => "号码", "width" => "23%"),
            "修改时间" => array("field" => "modified", "title" => "修改时间", "width" => "33%"),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => "33%"),
        ),
        "platformcustomer" => array(
            "id"   => array("field" => "id", "hidden" => true),
            "客户网名" => array("field" => "account", "name" => "客户网名", "width" => "33%"),
            "平台ID" => array("field" => "platform_id", "name" => "平台ID", "width" => "33%", "formatter" => "formatter
                .platform_id"),
            "创建时间" => array("field" => "created", "name" => "创建时间", "width" => "33%")
        ),
        "recenttrade"      => array(
            "id"      => array("field" => "id", "hidden" => true),
            "订单编号"    => array("field" => "trade_no", "name" => "订单编号", "width" => "80"),
            "平台类型"    => array("field" => "platform_id", "name" => "平台类型", "width" => "80", "formatter" => "formatter.platform_id"),
            "店铺名称"    => array("field" => "shop_name", "name" => "店铺名称", "width" => "80"),
            "仓库名称"    => array("field" => "name", "name" => "仓库名称", "width" => "80"),
            "仓库类型"    => array("field" => "warehouse_type", "name" => "仓库类型", "width" => "80", "formatter" => "formatter.warehouse_type"),
            "原始单号"    => array("field" => "src_tids", "name" => "原始单号", "width" => "80"),
            "订单状态"    => array("field" => "trade_status", "name" => "订单状态", "width" => "80", "formatter" => "formatter.trade_status"),
            "发货状态"    => array("field" => "consign_status", "name" => "发货状态", "width" => "80", "formatter" => "formatter.sales_consign_status"),
            "订单类型"    => array("field" => "trade_type", "name" => "订单类型", "width" => "80", "formatter" => "formatter.trade_type"),
            "发货条件"    => array("field" => "delivery_term", "name" => "发货条件", "width" => "80", "formatter" => "formatter.delivery_term"),
            "冻结原因"    => array("field" => "title", "name" => "冻结原因", "width" => "80"),
            "退款状态"    => array("field" => "refund_status", "name" => "退款状态", "width" => "80", "formatter" => "formatter.refund_status"),
            "交易时间"    => array("field" => "trade_time", "name" => "交易时间", "width" => "80"),
            "付款时间"    => array("field" => "pay_time", "name" => "付款时间", "width" => "80"),
            "买家付款账号"  => array("field" => "pay_account", "name" => "买家付款账号", "width" => "80"),
            "客户网名"    => array("field" => "buyer_nick", "name" => "客户网名", "width" => "80"),
            "收件人"     => array("field" => "receiver_name", "name" => "收件人", "width" => "80"),
            "省市县"     => array("field" => "receiver_area", "name" => "省市县", "width" => "80"),
            "地址"      => array("field" => "receiver_address", "name" => "地址", "width" => "80"),
            "手机"      => array("field" => "receiver_mobile", "name" => "手机", "width" => "80"),
            "电话"      => array("field" => "receiver_telno", "name" => "电话", "width" => "80"),
            "邮编"      => array("field" => "receiver_zip", "name" => "邮编", "width" => "80"),
            "区域"      => array("field" => "receiver_ring", "name" => "区域", "width" => "80"),
            "大头笔"     => array("field" => "receiver_dtb", "name" => "大头笔", "width" => "80"),
            "派送时间"    => array("field" => "to_deliver_time", "name" => "派送时间", "width" => "80"),
            "物流公司"    => array("field" => "logistics_name", "name" => "物流公司", "width" => "80"),
            "物流单号"    => array("field" => "logistics_no", "name" => "物流单号", "width" => "80"),
            "买家留言"    => array("field" => "buyer_message", "name" => "买家留言", "width" => "80"),
            "客服备注"    => array("field" => "cs_remark", "name" => "客服备注", "width" => "80"),
            "客服标旗"    => array("field" => "remark_flag", "name" => "客服标旗", "width" => "80"),
            "打印备注"    => array("field" => "print_remark", "name" => "打印备注", "width" => "80"),
            "货品种类数"   => array("field" => "goods_type_count", "name" => "货品种类数", "width" => "80"),
            "货品总数"    => array("field" => "goods_count", "name" => "货品总数", "width" => "80"),
            "货品总额"    => array("field" => "goods_amount", "name" => "货品总额", "width" => "80"),
            "邮资"      => array("field" => "post_amount", "name" => "邮资", "width" => "80"),
            "其他费用"    => array("field" => "other_amount", "name" => "其他费用", "width" => "80"),
            "折扣"      => array("field" => "discount", "name" => "折扣", "width" => "80"),
            "应收金额"    => array("field" => "receivable", "name" => "应收金额", "width" => "80"),
            "款到发货金额"  => array("field" => "dap_amount", "name" => "款到发货金额", "width" => "80"),
            "COD金额"   => array("field" => "cod_amount", "name" => "COD金额", "width" => "80"),
            "买家cod费用" => array("field" => "ext_cod_fee", "name" => "买家cod费用", "width" => "80"),
            "佣金"      => array("field" => "commission", "name" => "佣金", "width" => "80"),
            "货品预估成本"  => array("field" => "goods_cost", "name" => "货品预估成本", "width" => "80"),
            "邮资成本"    => array("field" => "post_cost", "name" => "邮资成本", "width" => "80"),
            "已付金额"    => array("field" => "paid", "name" => "已付金额", "width" => "80"),
            "预估重量"    => array("field" => "weight", "name" => "预估重量", "width" => "80"),
            "预估毛利"    => array("field" => "profit", "name" => "预估毛利", "width" => "80"),
            "需要发票"    => array("field" => "invoice_type", "name" => "需要发票", "width" => "80"),
            "发票抬头"    => array("field" => "invoice_title", "name" => "发票抬头", "width" => "80"),
            "发票内容"    => array("field" => "invoice_content", "name" => "发票内容", "width" => "80"),
            "业务员"     => array("field" => "salesman_id", "name" => "业务员", "width" => "80"),
            /*"审核人" => array("field" => "checker_id", "name" => "审核人", "width" => "80"),
            "财审人" => array("field" => "fchecker_id", "name" => "财审人", "width" => "80"),
            "签出人" => array("field" => "checkouter_id", "name" => "签出人", "width" => "80"),*/
            "出库单号"    => array("field" => "stockout_no", "name" => "出库单号", "width" => "80"),
            "标记名称"    => array("field" => "flag_id", "name" => "标记名称", "width" => "80"),
            "处理天数"    => array("field" => "days", "name" => "处理天数", "width" => "80"),
            "订单来源"    => array("field" => "trade_from", "name" => "订单来源", "width" => "80", "formatter" => "formatter.trade_from"),
            "原始货品数量"  => array("field" => "raw_goods_count", "name" => "原始货品数量", "width" => "80"),
            "原始货品种类数" => array("field" => "raw_goods_type_count", "name" => "原始货品种类数", "width" => "80"),
            "递交时间"    => array("field" => "created", "name" => "递交时间", "width" => "80"),
        ),
        "recentexchange"   => array(
            "id"     => array("field" => "id", "hidden" => true),
            "退换单号"   => array("field" => "refund_no", "title" => "退换单号", "width" => ""),
            "店铺"     => array("field" => "shop_name", "title" => "店铺", "width" => "80"),
            "类型"     => array("field" => "type", "title" => "类型", "width" => "80", "formatter" => "formatter.refund_type"),
            "建单者"    => array("field" => "fullname", "title" => "建单者", "width" => "80"),
            "平台退款单号" => array("field" => "src_no", "title" => "平台退款单号", "width" => "80"),
            "处理状态"   => array("field" => "process_status", "title" => "处理状态", "width" => "80", "formatter" => "formatter.refund_process_status"),
            "平台状态"   => array("field" => "status", "title" => "平台状态", "width" => "80", "formatter" => "formatter.api_refund_status"),
            "退货仓库"   => array("field" => "name", "title" => "退货仓库", "width" => "80"),
            "仓库类型"   => array("field" => "warehouse_type", "title" => "仓库类型", "width" => "80", "formatter" => "formatter.warehouse_type"),
            "推送状态"   => array("field" => "wms_status", "title" => "推送状态", "width" => "80", "formatter" => "formatter.wms_status"),
            "推送信息"   => array("field" => "wms_result", "title" => "推送信息", "width" => "80"),
            "外部编号"   => array("field" => "outer_no", "title" => "外部编号", "width" => "80"),
            "原始订单"   => array("field" => "tid", "title" => "原始订单", "width" => "80"),
            "系统订单"   => array("field" => "sales_trade_id", "title" => "系统订单", "width" => "80"),
            "客户网名"   => array("field" => "buyer_nick", "title" => "客户网名", "width" => "80"),
            "姓名"     => array("field" => "receiver_name", "title" => "姓名", "width" => "80"),
            "手机号"    => array("field" => "return_mobile", "title" => "手机号", "width" => "80"),
            "固话"     => array("field" => "return_telno", "title" => "固话", "width" => "80"),
            "地址"     => array("field" => "receiver_address", "title" => "地址", "width" => "80"),
            "支付账号"   => array("field" => "pay_account", "title" => "支付帐号", "width" => "80"),
            "退货货品数量" => array("field" => "return_goods_count", "title" => "退货货品数量", "width" => "80"),
            "退货金额"   => array("field" => "goods_amount", "title" => "退货金额", "width" => "80"),
            "平台退款金额" => array("field" => "guarante_refund_amount", "title" => "平台退款金额", "width" => "80"),
            "线下退款金额" => array("field" => "direct_refund_amount", "title" => "线下退款金额", "width" => "80"),
            "换货金额"   => array("field" => "exchange_amount", "title" => "换货金额", "width" => "80"),
            "物流公司"   => array("field" => "logistics_name", "title" => "物流公司", "width" => "80"),
            "物流单号"   => array("field" => "logistics_no", "title" => "物流单号", "width" => "80"),
            "退回地址"   => array("field" => "return_address", "title" => "退回地址", "width" => "80"),
            "建单时间"   => array("field" => "created", "title" => "建单时间", "width" => "80"),
            "退款时间"   => array("field" => "refund_time", "title" => "退款时间", "width" => "80"),
            "建单方式"   => array("field" => "from_type", "title" => "建单方式", "width" => "80", "formatter" => "formatter.from_type"),
            "退款原因"   => array("field" => "title", "title" => "退款原因", "width" => "80"),
            "同步结果"   => array("field" => "sync_result", "title" => "同步结果", "width" => "80"),
            "备注"     => array("field" => "remark", "title" => "备注", "width" => "80")
        ),
        "operatorrecord"   => array(
            "id"   => array("field" => "id", "hidden" => true),
            "操作员"  => array("field" => "fullname", "title" => "操作员", "width" => "20%"),
            "操作"   => array("field" => "message", "title" => "操作", "width" => "50%"),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => "29%")
        ),
        "customersms"      => array(
            "短信状态" => array("field"     => "status", "title" => "短信状态", "width" => 70, "sortable" => true,"formatter" => "formatter.sms_status"),
            "短信发送类型" => array("field" => "sms_type", "title" => "短信发送类型", "width" => 100, "sortable" => true,"formatter" => "formatter.type_sms"),
            "操作人" => array("field" => "fullname", "title" => "操作人", "width" => 70, "sortable" => true,"formatter" => "formatter.operator_id"),
            "手机号码" => array("field" => "phones", "title" => "手机号码", "width" => 100),
            "手机号码个数" => array("field" => "phone_num", "title" => "手机号码个数", "width" => 80, "sortable" => true),
            "短信内容" => array("field" => "message", "title" => "短信内容", "width" => 200),
            "预定发送时间" => array("field" => "timer_time", "title" => "预定发送时间", "width" => 150, "sortable" => true),
            "实际发送时间" => array("field" => "send_time", "title" => "实际发送时间", "width" => 150, "sortable" => true),
            "批次号" => array("field" => "batch_no", "title" => "批次号", "width" => 100, "sortable" => true),
            "预计短信条数" => array("field" => "pre_count", "title" => "预计短信条数", "width" => 90, "sortable" => true),
            "成功发送条数" => array("field" => "success_count", "title" => "成功发送条数", "width" => 90, "sortable" => true),
            "成功发送人数" => array("field" => "success_people", "title" => "成功发送人数", "width" => 90, "sortable" => true),
            "尝试发送次数" => array("field" => "try_times", "title" => "尝试发送次数", "width" => 90, "sortable" => true),
            "短信类型" => array("field" => "send_type", "title" => "短信类型", "width" => 90, "sortable" => true,"formatter" => "formatter.sms_send_sms_type"),
            "错误信息" => array("field" => "error_msg", "title" => "错误信息", "width" => 180, "sortable" => true),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => 140, "sortable" => true),
        ),
        "marketmanagement"      => array(
            "id"   => array("field" => "id", "hidden" => true),
            "方案名称" => array("field"     => "plan_name", "title" => "方案名称", "width" => 100),
            "方案类型" => array("field" => "plan_type", "title" => "方案类型", "width" => 70, "sortable" => true,"formatter" => "formatter.type_market"),
            "状态" => array("field" => "status", "title" => "状态", "width" => 70, "sortable" => true,"formatter" => "formatter.status_market"),
            "内容" => array("field" => "msg_content", "title" => "内容", "width" => 150),
            "备注" => array("field" => "remark", "title" => "备注", "width" => 70),
            //"失败原因" => array("field" => "error_msg", "title" => "失败原因", "width" => 70),
            "最后修改人" => array("field" => "operator_id", "title" => "最后修改人", "width" => 70),
            "最后修改时间" => array("field" => "modified", "title" => "最后修改时间", "width" => 150, "sortable" => true),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => 150, "sortable" => true),
        ),
        "marketdetail"      => array(
            "id"   => array("field" => "id", "hidden" => true),
            "电话" => array("field" => "mobile", "title" => "电话", "width" => 100),
            "客户网名" => array("field" => "nickname", "title" => "客户网名", "width" => 100),
            "姓名" => array("field" => "name", "title" => "姓名", "width" => 100),
            "方案实施人" => array("field" => "operator_id", "title" => "方案实施人", "width" => 100),
            "最后营销日期" => array("field" => "marketing_date", "title" => "最后营销日期", "width" => 150,"sortable" => true),
            "创建时间" => array("field" => "created", "title" => "创建时间", "width" => 150, "sortable" => true),
        ),
        "addcustomer"      => array(
            "id"   => array("field" => "id", "hidden" => true),
            "客户网名" => array("field" => "nickname", "title" => "客户网名", "width" => 100),
            "姓名" => array("field" => "name", "title" => "姓名", "width" => 100),
            "手机" => array("field" => "mobile", "title" => "手机", "width" => 100),
            "电子邮件" => array("field" => "email", "title" => "电子邮件", "width" => 100, "sortable" => true),
            "性别"   => array("field"     => "sex", "title" => "性别", "width" => 100, "sortable" => true, "formatter" => "formatter.sex"),
            "生日"   => array("field" => "birthday", "title" => "生日", "width" => 100, "sortable" => true),
            "登记时间" => array("field" => "created", "title" => "登记时间", "width" => 100, "sortable" => true),
        ),



    );

}