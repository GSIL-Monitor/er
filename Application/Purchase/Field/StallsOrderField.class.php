<?php  
namespace Purchase\Field;
use Common\Common\Field;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
class StallsOrderField extends Field{
	protected function get($key){
		 $provider = D('Setting/PurchaseProvider')->field('id as provider_id,provider_name')->where(array('id'=>array('neq','0')))->select();
		$fields = array(
			'stallsordermanagement'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'档口单号/爆款码'=>array('field' => 'stalls_no', 'width'=>'150','align' => 'center'),
				'档口状态'=>array('field'=>'status','width'=>'100','align' => 'center','formatter' => 'formatter.stalls_status',),
				'是否爆款单'=>array('field'=>'is_hot','width'=>'100','align'=>'center'),
				'爆款码打印状态'=>array('field'=>'hot_print_status','width'=>'100','align'=>'center','formatter' => 'formatter.hot_print_status'),
				'唯一码状态' => array('field' => 'unique_print_status', 'width' => '100','align' => 'center','formatter' => 'formatter.unique_print_status'),
				'明细打印状态' => array('field' => 'detail_print_status', 'width' => '100','align' => 'center','formatter' => 'formatter.unique_print_status'),
				'仓库' => array('field' => 'warehouse_name', 'width' => '100','align' => 'center'),
				'货品总量' => array('field' => 'goods_count', 'width' => '100','align' => 'center'),
				'货品总价' => array('field' => 'goods_fee', 'width' => '100','align' => 'center'),
				'入库总量' => array('field' => 'in_num', 'width' => '100','align' => 'center'),
				'爆款码打印数量'=>array('field'=>'hot_print_num','width'=>'100','align'=>'center'),
				'取货总量' => array('field' => 'put_num', 'width' => '100','align' => 'center'),
                '邮费' => array('field' => 'post_fee', 'width' => '100','align' => 'center'),
                '其他费用' => array('field' => 'other_fee', 'width' => '100','align' => 'center'),
				'总金额' => array('field' => 'tax_fee', 'width' => '100','align' => 'center'),
				'货品类别' => array('field' => 'goods_type_count', 'width' => '100','align' => 'center'),
				'建单人' => array('field' => 'creator_name', 'width' => '100','align' => 'center'),
                '采购员' => array('field' => 'purchaser_name', 'width' => '100','align' => 'center'),
                '物流方式' => array('field' => 'logistics_type', 'width' => '150','align' => 'center'),
				'备注' => array('field' => 'remark', 'width' => '100','align' => 'center'),
				'修改时间' => array('field' => 'modified', 'width' => '150','align' => 'center'),
				'创建时间' => array('field' => 'created', 'width' => '150','align' => 'center'),
			),
			'stalls_order_detail'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'入库量' =>array('field' => 'in_num','width' => '100'),
				'取货量' =>array('field' => 'put_num','width' => '100'),
				'品牌id'=>array('field'=>'brand_id','width'=>'100','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'100'),
				'基本单位' => array('field' => 'unit_name', 'width' => '100'), 
				'供应商'=>array('field'=>'provider_name','width'=>'100'),
				'采购量' => array('field' => 'num', 'width' => '100'),
                '采购价' => array('field' => 'price', 'width' => '100'),
                '采购金额' => array('field' => 'amount', 'width' => '100'),
                '备注' => array('field' => 'remark', 'width' => '100'),
			),
			'stalls_order_edit'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'品牌id'=>array('field'=>'brand_id','width'=>'100','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'100'),
				'基本单位' => array('field' => 'unit_name', 'width' => '100'),
				'采购价(可编辑)' => array('field' => 'price', 'width' => '100','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),
				'供应商(可编辑)' => array('field' => 'provider_name', 'width' => '150','editor'=>'{type:"textbox",options:{buttonText:"...",editable:false}}'),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
               ),
			'stalls_order_split'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'拆分数(可编辑)'=>array('field'=>'split_num','width'=>'100','editor'=>'{type:"numberbox",options:{required:true,value:0,min:0}}'),
				'剩余数量' => array('field' => 'left_num', 'width' => '100'),
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
				'入库量' =>array('field' => 'in_num','width' => '100'),
				'取货量' =>array('field' => 'put_num','width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'供应商'=>array('field'=>'provider_name','width'=>'100'),
                '采购价' => array('field' => 'price', 'width' => '100'),
                '采购金额' => array('field' => 'amount', 'width' => '100'),
			),  
			'stalls_order_new_split'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'商家编码' => array('field' => 'spec_no', 'width' => '100'),
				'数量' => array('field' => 'num', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'入库量' =>array('field' => 'in_num','width' => '100'),
				'取货量' =>array('field' => 'put_num','width' => '100'),
				'品牌id'=>array('field'=>'brand_id','width'=>'100','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'100'),
				'基本单位' => array('field' => 'unit_name', 'width' => '100'), 
				'供应商'=>array('field'=>'provider_name','width'=>'100'),
				'采购量' => array('field' => 'num', 'width' => '100'),
                '采购价' => array('field' => 'price', 'width' => '100'),
                '采购金额' => array('field' => 'amount', 'width' => '100'),
                '备注' => array('field' => 'remark', 'width' => '100'),
			),
			'stalls_order_log'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'操作员' => array('field' => 'account', 'width' => '100'),
                '操作' => array('field' => 'remark', 'width' => '800'),
                '时间' => array('field' => 'created', 'width' => '150'),
                
			),
			'stalls_less_goods_detail'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'唯一码' => array('field' => 'unique_code', 'width' => '180'),
				'商家编码' => array('field' => 'spec_no', 'width' => '100','sortable'=>true),
				'货品编号' => array('field' => 'goods_no', 'width' => '100'),
				'货品名称' => array('field' => 'goods_name', 'width' => '100'),
				'规格码' => array('field' => 'spec_code', 'width' => '100'),
				'规格名称' => array('field' => 'spec_name', 'width' => '100'),
//				'条形码' => array('field' => 'barcode', 'width' => '100'),
				'订单号' => array('field' => 'trade_no', 'width' => '110'),
				'仓库' => array('field' => 'warehouse_name', 'width' => '100'),
//				'订单状态' => array('field' => 'trade_status', 'width' => '100'),
				'拦截原因' => array('field' => 'block_reason', 'width' => '100', 'formatter' => 'formatter.stockout_block_reason'),
				'分拣框编号' => array('field' => 'box_no', 'width' => '100'),
				'分拣状态' => array('field' => 'sort_status', 'width' => '100'),
				'入库状态' => array('field' => 'stockin_status', 'width' => '100'),
				'取货状态' => array('field' => 'pickup_status', 'width' => '100'),
				'爆款码打印状态'=>array('field'=>'hot_print_status','width'=>'100','align'=>'center','formatter' => 'formatter.hot_print_status'),
				'唯一码打印状态' => array('field' => 'unique_print_status', 'width' => '100'),
				'物流单打印状态' => array('field' => 'logistics_print_status', 'width' => '100'),
				'吊牌打印状态' => array('field' => 'tag_print_status', 'width' => '100'),
				'生成档口采购单状态' => array('field' => 'generate_status', 'width' => '100'),
				'生成爆款单状态' => array('field' => 'hot_status', 'width' => '100'),
				'供应商' => array('field' => 'provider_name', 'width' => '100'),
				'备注' => array('field' => 'remark', 'width' => '100'),
				'修改时间' => array('field' => 'modified', 'width' => '150'),
				'创建时间' => array('field' => 'created', 'width' => '150'),
			),
			'edit'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'平台'=>array('field'=>'platform_id','width'=>60,'formatter'=>'formatter.platform_id'),
				'货品名称'=>array('field'=>'goods_name','width'=>150),
				'规格名'=>array('field'=>'spec_name','width'=>150),
				'商家编号'=>array('field'=>'spec_no','width'=>100),
				'下单数量'=>array('field'=>'num','width'=>80),
				'标价'=>array('field'=>'price','width'=>80),
				'成交价'=>array('field'=>'order_price','width'=>80),
				'实发数量'=>array('field'=>'actual_num','width'=>80),
				'备注'=> array('field'=>'remark','width'=>150),
				'成本价'=>array('field'=>'cost_price','width'=>80),
				'优惠后单价'=>array('field'=>'share_price','width'=>80),
				'优惠后总价'=>array('field'=>'share_amount','width'=>80),
				'优惠'=>array('field'=>'discount','width'=>80),
				'分摊邮费'=>array('field'=>'share_post','width'=>80),
				'赠品方式'=>array('field'=>'gift_type','width'=>60,'formatter'=>'formatter.gift_type'),
				'拆自组合装'=>array('field'=>'suite_name','width'=>80),//拆自组合装
				'组合装数量'=>array('field'=>'suite_num','width'=>80),
				'组合装编码'=>array('field'=>'suite_no','width'=>80),
				'估重'=>array('field'=>'weight','width'=>80),
				'库存'=> array('field'=>'stock_num','width'=>80),
				'子订单编号'=>array('field'=>'src_oid','width'=>100),//
				'货品编号'=>array('field'=>'goods_no','width'=>100),
				'规格码'=>array('field'=>'spec_code','width'=>100),
			),
			'exchange'=>array(
				'id' => array('field' => 'id', 'hidden'=>'true'),
				'商家编码' => array('field' => 'spec_no',   'width'=>130),
				'货品名称' => array('field' => 'goods_name',  'width'=>150),
				'规格名称' => array('field' => 'spec_name',   'width'=>150),
				'价格'=>array('field'=>'price','width'=>100),
				'数量(可编辑)' => array('field' => 'num',   'width'=>100,'methods'=>'editor:{type:"numberbox",options:{required:true,min:1}}'),
			),
			'order'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'平台'=>array('field'=>'platform_id','width'=>60,'formatter'=>'formatter.platform_id'),
				'货品名称'=>array('field'=>'goods_name','width'=>150),
				'规格名'=>array('field'=>'spec_name','width'=>100),//,'methods'=>'editor:{type:"textbox"}'
				// '原始订单号'=>array('field'=>'src_tids','width'=>100),
				'子订单编号'=>array('field'=>'src_oid','width'=>100),//
				'商家编号'=>array('field'=>'spec_no','width'=>100),
				'货品编号'=>array('field'=>'goods_no','width'=>100),
				'规格码'=>array('field'=>'spec_code','width'=>100),
				'下单数量'=>array('field'=>'num','width'=>80),
				'成本价'=>array('field'=>'cost_price','width'=>80),
				'标价'=>array('field'=>'price','width'=>80),
				'成交价'=>array('field'=>'order_price','width'=>80),
				'实发数量'=>array('field'=>'actual_num','width'=>80),
				'分摊价格'=>array('field'=>'share_price','width'=>80),
				'分摊后总价'=>array('field'=>'share_amount','width'=>80),
				'分摊邮费'=>array('field'=>'share_post','width'=>80),
				'优惠'=>array('field'=>'discount','width'=>80),
				'赠品方式'=>array('field'=>'gift_type','width'=>60,'formatter'=>'formatter.gift_type'),
				'拆自组合装'=>array('field'=>'suite_name','width'=>80),//拆自组合装
				'组合装数量'=>array('field'=>'suite_num','width'=>80),
				'组合装编码'=>array('field'=>'suite_no','width'=>80),
				'估重'=>array('field'=>'weight','width'=>80),
				'库存'=> array('field'=>'stock_num','width'=>80),
				'备注'=> array('field'=>'remark','width'=>150),
			),
            'hot_goods_order'=>array(
            'id'=>array('field'=>'id','hidden'=>true),
            '订单号'=>array('field'=>'src_order_no','width'=>100),
            '原始单号'=>array('field'=>'src_tids','width'=>100),
            '物流公司'=>array('field'=>'logistics_name','width'=>100),
            '物流单号'=>array('field'=>'logistics_no','width'=>100),
            '物流单打印状态'=>array('field'=>'logistics_print_status','width'=>80,'formatter'=>'formatter.print_status'),
            '收件人'=>array('field'=>'receiver_name','width'=>80),
            '客户网名'=>array('field'=>'buyer_nick','width'=>80),
            '已付'=>array('field'=>'paid','width'=>80),
            '估重'=>array('field'=>'weight','width'=>80),
            '付款时间'=>array('field'=>'pay_time','width'=>150),
            '下单时间'=> array('field'=>'trade_time','width'=>150),
			),
			'split_hot_order_info'=>array(
                'id'     =>array('field'=>'stock_id','hidden'=>true),
                '订单号' =>array('field'=>'trade_no','width'=>'200'),
                '错误信息' =>array('field'=>'msg','width'=>'400')
            ),
			'trade_order_detail'=>array(
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

		);
		 return $fields[$key];
	}
	
}