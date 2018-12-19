<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:53
 */
namespace Stock\Field;

use Common\Common\Field;
use Common\Common\UtilDB;

class StockInField extends Field
{
    protected function get($key)
    {
		$number = get_config_value('point_number',0);
        if($key=="intelligence_return_order_list"){
            $res_cfg_val=get_config_value('order_fa_condition',0);
            if($res_cfg_val==1){
                $trade_status_formatter='formatter.trade_status_fc';
            }else{
                $trade_status_formatter='formatter.trade_status';
            }
        }
        $fields = array(
            'stockinorder' => array(
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编码'=> array('field' => 'goods_no', 'width'=>'100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '图片'=>array('field'=>'img_url','width'=>'100','formatter'=>'formatter.print_img'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '品牌id' => array('field' => 'brand_id', 'width' => '100','hidden'=>true),
                '品牌' => array('field' => 'brand_name', 'width' => '100'),
                '预期数量' => array('field' => 'expect_num', 'width' => '100'),
                '入库数量(可编辑)' => array('field' => 'num', 'width' => 'auto','editor'=>'{type:"numberbox",options:{required:true,precision:'.$number.',value:0,min:0}}'),// 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '原价(可编辑)' => array('field' => 'src_price', 'width' => 'auto','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),//, 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '入库价(可编辑)' => array('field' => 'cost_price', 'width' => 'auto','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),//, 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '入库总价(可编辑)' => array('field' => 'total_cost', 'width' => 'auto','editor'=>'{type:"numberbox",options:{required:true,precision:4,value:0,min:0}}'),//, 'formatter' => 'function(value,row,index){ var num = parseFloat(row["num"]);var cost_price = parseFloat(row["cost_price"]); var temp_cost_price = parseFloat(value)/num; if(temp_cost_price.toFixed(4) == cost_price){return row["total_cost"] = parseFloat(value).toFixed(4);} var total_cost = num*cost_price; return row["total_cost"] = total_cost.toFixed(4); }'
                '货位id' => array('field' => 'position_id', 'width' => '100','hidden'=>true),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '货位(可编辑)' => array('field' => 'position_no', 'width' => 'auto','editor'=>'{type:"textbox",options:{buttonText:"...",editable:false}}'),//, 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; var temp_total_cost = parseFloat(row["total_cost"])*(tax_rate+1);if(temp_total_cost.toFixed(4)==parseFloat(value).toFixed(4)){return row["tax_amount"]= parseFloat(value).toFixed(4);}return row["tax_amount"]=tax_amount.toFixed(4);}'
                '单位' => array('field' => 'unit_name', 'width' => '80'),
                '零售价' => array('field' => 'retail_price', 'width' => '80',),//'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(4);}'
                '最低价' => array('field' => 'lowest_price', 'width' => '80', 'hidden' => false),
                '市场价' => array('field' => 'market_price', 'width' => '80', 'hidden' => false),
                '单位id' => array('field' => 'base_unit_id', 'width' => '80','hidden'=>true),
                'id' => array('field' => 'id', 'hidden' => true)
            ),
            'stockinmanagement' => array(
                '入库单号' => array('field' => 'stockin_no', 'width' => '100', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockin_status',),
                '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockin_type',),

                '来源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center',),
                '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center',),
                '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
                '货款总额' => array('field' => 'goods_amount', 'width' => '100', 'align' => 'center'),
                '货品成本(入库总价)' => array('field' => 'total_price', 'width' => '100', 'align' => 'center'),
                '优惠' => array('field' => 'discount', 'width' => '100', 'align' => 'center'),
                '邮资' => array('field' => 'post_fee', 'width' => '100', 'align' => 'center'),
                '其他金额' => array('field' => 'other_fee', 'width' => '100', 'align' => 'center'),
                //'调整后总金额' => array('field' => 'right_fee', 'width' => '100', 'align' => 'center'),
                '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                //'调整后总数量' => array('field' => 'right_num', 'width' => '100', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
//										'修改时间' => array('field' => 'modified', 'width' => '100', 'align' => 'center'),
										'入库时间' => array('field' => 'check_time', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
            ),

            "stockindetail"=>array(
                'id'=>array('field'=>'id','hidden'=>true),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'150'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '品牌'=>array('field'=>'brand_id','width'=>'100',),
                '条形码'=>array('field'=>'barcode','width'=>'100'),
                '货位id' => array('field' => 'position_id', 'width' => '100','hidden'=>true),
                '货位' => array('field' => 'position_no', 'width' => '100'),
                '入库价'=>array('field'=>'cost_price','width'=>'100'),//入库价格
                '数量'=>array('field'=>'num','width'=>'100'),
//                        '调整后单价'=>array('field'=>'right_price','width'=>'100'),
//                        '调整后数量'=>array('field'=>'right_num','width'=>'100'),
//                '税率'=>array('field'=>'tax','width'=>'100'),
//                '税后价'=>array('field'=>'tax_price','width'=>'100'),
                '单位'=>array('field'=>'base_unit_id','width'=>'100'),
//                 '调整后总金额'=>array('field'=>'right_cost','width'=>'100'),
            ),
            'stockinexport' => array(

                '入库单号'=>array('field'=>'stockin_no','width'=>'100'),
                '入库人员'=>array('field'=>'creator_name','width'=>'100'),
                '仓库名称'=>array('field'=>'warehouse_name','width'=>'100'),
                '入库单状态'=>array('field'=>'status','width'=>'100'),
                '入库单类别'=>array('field'=>'mode','width'=>'100'),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'150'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '品牌'=>array('field'=>'brand_id','width'=>'100'),
                '条形码'=>array('field'=>'barcode','width'=>'100'),
                '货位' => array('field' => 'position_no', 'width' => '100'),
                '入库价'=>array('field'=>'cost_price','width'=>'100'),//入库价格
                '数量'=>array('field'=>'num','width'=>'100'),
                '单位'=>array('field'=>'base_unit_id','width'=>'100'),
                '备注' => array('field' => 'remark', 'width' => '200', 'align' => 'center'),
                '修改时间' => array('field' => 'modified', 'width' => '200', 'align' => 'center'),
                '创建时间' => array('field' => 'created', 'width' => '200', 'align' => 'center'),

            ),
            'stock_in_order_barcode' => array(
                '入库单号' => array('field' => 'stockin_no', 'width' => '100', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockin_status',),
                '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockin_type',),
                '来源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center',),
                '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center',),
                '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
                '货款总额' => array('field' => 'goods_amount', 'width' => '100', 'align' => 'center'),
                '货品成本(入库总价)' => array('field' => 'total_price', 'width' => '100', 'align' => 'center'),
                '优惠' => array('field' => 'discount', 'width' => '100', 'align' => 'center'),
                '邮资' => array('field' => 'post_fee', 'width' => '100', 'align' => 'center'),
                '其他金额' => array('field' => 'other_fee', 'width' => '100', 'align' => 'center'),
                //'调整后总金额' => array('field' => 'right_fee', 'width' => '100', 'align' => 'center'),
                '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                //'调整后总数量' => array('field' => 'right_num', 'width' => '100', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
//				'修改时间' => array('field' => 'modified', 'width' => '100', 'align' => 'center'),
                '入库时间' => array('field' => 'check_time', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
            ),
            'intelligence_return_stock_in'  => array(
                'id'=>array('field'=>'id','hidden'=>true),
                '图片' => array('field' => 'pic_name', 'width' => '100', 'formatter'=>'formatter.print_img'),
                '条码'=> array('field' => 'barcode', 'width'=>'100'),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '订单编号' => array('field' => 'trade_no', 'width' => '100'),
                '店铺' => array('field' => 'shop_name', 'width' => '100'),
                '预期数量' => array('field' => 'expect_num', 'width' => '100'),
                '入库数量(可编辑)' => array('field' => 'stockin_num', 'width' => '100','methods'=>'editor:{type:"numberbox",options:{precision:0,min:0}}'),
                '备注' => array('field' => 'remark', 'width' => '100'),
                '货品简称'=>array('field'=>'short_name','width'=>'160'),
                '货品名称'=>array('field'=>'goods_name','width'=>'160'),
                '货品编码'=>array('field'=>'goods_no','width'=>'160'),
            ),
            'intelligence_return_order_list'=> array(
                'id'     => array('field'=>'id','hidden'=>true,'sortable'=>true),
                'flag_id'=> array('field'=>'flag_id','hidden'=>true,'sortable'=>true),
                //'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                //'系统标记'=>array('field'=>'flag','width'=>100,'frozen'=>true),
                '订单号'   => array('field'=>'trade_no','width'=>100,'sortable'=>true),
                '店铺名称'  => array('field'=>'shop_name','width'=>100,'sortable'=>true),
                '平台类型'  => array('field'=>'platform_id','width'=>100,'sortable'=>true,'formatter'=>'formatter.platform_id'),
                '原始单号'  => array('field'=>'src_tids','width'=>100,'sortable'=>true),
                '客户网名'   => array('field'=>'buyer_nick','width'=>100,'sortable'=>true),
                '收件人'  => array('field'=>'receiver_name','width'=>100,'sortable'=>true),
                '地区'  => array('field'=>'receiver_area','width'=>150,'sortable'=>true),
                '地址'  => array('field'=>'receiver_address','width'=>150,'sortable'=>true),
                '手机'  => array('field'=>'receiver_mobile','width'=>100,'sortable'=>true),
                '固话'  => array('field'=>'receiver_telno','width'=>100,'sortable'=>true),
                '邮编'  => array('field'=>'receiver_zip','width'=>100,'sortable'=>true),
                '发货条件'  => array('field'=>'delivery_term','width'=>100,'sortable'=>true,'formatter'=>'formatter.delivery_term'),
                '订单状态'  => array('field'=>'trade_status','width'=>100,'sortable'=>true,'formatter'=>$trade_status_formatter),
                '退货状态'  => array('field'=>'refund_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.refund_status'),
                '发货状态'  => array('field'=>'consign_status','width'=>100,'sortable'=>true,'formatter'=>'formatter.sales_consign_status'),
                '发货时间'  => array('field'=>'consign_time','width'=>130),
                '冻结原因'  => array('field'=>'freeze_info','width'=>100,'sortable'=>false),
                '仓库'  => array('field'=>'warehouse_name','width'=>100,'sortable'=>true),
                '仓库类型'  => array('field'=>'warehouse_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.warehouse_type'),
                '物流公司'  => array('field'=>'logistics_id','width'=>100,'sortable'=>true),
                '物流单号'  => array('field'=>'logistics_no','width'=>100,'sortable'=>true),
                '客服备注'  => array('field'=>'cs_remark','width'=>100,'sortable'=>true),
                '标旗'    => array('field'=>'remark_flag','width'=>60,'sortable'=>true),
                '买家留言'  => array('field'=>'buyer_message','width'=>100,'sortable'=>true),
                '打印备注'  => array('field'=>'print_remark','width'=>100,'sortable'=>true),
                '货品种类数'  => array('field'=>'goods_type_count','width'=>100,'sortable'=>true),
                '货品总量'  => array('field'=>'goods_count','width'=>100,'sortable'=>true),
                '总货款'  => array('field'=>'goods_amount','width'=>100,'sortable'=>true),
                '邮费'  => array('field'=>'post_amount','width'=>100,'sortable'=>true),
                '优惠'  => array('field'=>'discount','width'=>100,'sortable'=>true),
                '应收'  => array('field'=>'receivable','width'=>100,'sortable'=>true),
                '已付'  => array('field'=>'paid','width'=>100,'sortable'=>true),
                '款到发货金额'  => array('field'=>'dap_amount','width'=>100,'sortable'=>true),
                '买家COD费用'  => array('field'=>'cod_amount','width'=>100,'sortable'=>true),
                // '佣金'  => array('field'=>'commission','width'=>100,'sortable'=>true),
                '预估邮资成本'  => array('field'=>'post_cost','width'=>100,'sortable'=>true),
                '货品估算成本'  => array('field'=>'goods_cost','width'=>100,'sortable'=>true),
                '估重'  => array('field'=>'weight','width'=>100,'sortable'=>true),
                '预估利润'=>array('field'=>'profit','width'=>100,'sortable'=>true),
                '需要发票'  => array('field'=>'invoice_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.invoice_type'),
                '发票抬头'  => array('field'=>'invoice_title','width'=>100,'sortable'=>true),
                '发票内容'  => array('field'=>'invoice_content','width'=>100,'sortable'=>true),
                '下单时间'  => array('field'=>'trade_time','width'=>100,'sortable'=>true),
                '付款时间'  => array('field'=>'pay_time','width'=>100,'sortable'=>true),
                '支付账户'  => array('field'=>'pay_account','width'=>100,'sortable'=>true),
                //'分销类型'  => array('field'=>'fenxiao_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.fenxiao_type'),
                //'分销商名称'  => array('field'=>'fenxiao_nick','width'=>100,'sortable'=>true),
                '大头笔'  => array('field'=>'receiver_dtb','width'=>100,'sortable'=>true),
                '送货时间'  => array('field'=>'to_deliver_time','width'=>100,'sortable'=>true),
                '业务员'  => array('field'=>'salesman_id','width'=>100,'sortable'=>true),
                '审核人'  => array('field'=>'checker_id','width'=>100,'sortable'=>true),
                '类型'  => array('field'=>'trade_type','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_type'),
                '处理天数'  => array('field'=>'handle_days','width'=>100,'sortable'=>true),
                '订单来源'  => array('field'=>'trade_from','width'=>100,'sortable'=>true,'formatter'=>'formatter.trade_from'),
                //'货品商家编码'  => array('field'=>'single_spec_no','width'=>100,'sortable'=>true),
                '出库单号'  => array('field'=>'stockout_no','width'=>100,'sortable'=>true),
                '原始货品种类'  => array('field'=>'raw_goods_type_count','width'=>100,'sortable'=>true),
                '原始货品数量'  => array('field'=>'raw_goods_count','width'=>100,'sortable'=>true),
                //'标记名称'  => array('field'=>'flag_name','width'=>100,'sortable'=>true),
                //'递交时间'  => array('field'=>'created','width'=>100,'sortable'=>true),
            ),
        );
        return $fields[$key];
    }
}