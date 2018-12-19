<?php
namespace Stock\Field;
use Common\Common\Field;

class StockSalesPrintField extends Field
{
	protected function get($key){
		if($key=="salesstockout_order"){
			$salesstockout_status='formatter.salesstockout_status';
			$res_cfg_val=get_config_value('order_fa_condition',0);
			if($res_cfg_val==1){
				$salesstockout_status='formatter.salesstockout_status_fc';
			}
		}
		$fields = array(
		    'stockout_print'=>array(
				'物流单打印状态'=>array('field'=>'logistics_print_status','width'=>'100','formatter'=>'formatter.boolen'),
		        '发货单打印状态'=>array('field'=>'sendbill_print_status','width'=>'100','formatter'=>'formatter.boolen'),
				'订单编号'=>array('field'=>'src_order_no','width'=>'100'),
				'出库单编号'=>array('field'=>'stockout_no','width'=>'100',),
				'店铺'=>array('field'=>'shop_name','width'=>'100',),
				'仓库'=>array('field'=>'warehouse_name','width'=>'100',),
				//'仓库类型'=>array('field'=>'warehouse_type','width'=>'100',),
				'物流公司'=>array('field'=>'logistics_name','width'=>'100',),
				'物流单号'=>array('field'=>'logistics_no','width'=>'100',),
				'网名'=>array('field'=>'buyer_nick','width'=>'100',),
				'原始单号'=>array('field'=>'src_tids','width'=>'100',),
				'审单员'=>array('field'=>'checker_id','width'=>'100',),
				'收货人'=>array('field'=>'receiver_name','width'=>'100',),
				'省市区'=>array('field'=>'receiver_area','width'=>'100',),
				'收货地址'=>array('field'=>'receiver_address','width'=>'100',),
				'收件人手机'=>array('field'=>'receiver_mobile','width'=>'100',),
				'收件人电话'=>array('field'=>'receiver_telno','width'=>'100',),
				'邮编'=>array('field'=>'receiver_zip','width'=>'100',),
				'状态'=>array('field'=>'status','width'=>'100','formatter'=>'formatter.salesstockout_status',),
				'发货状态'=>array('field'=>'consign_status','width'=>'100','formatter'=>'formatter.sales_consign_status'),
				'订单类型'=>array('field'=>'trade_type','width'=>'100','formatter'=>'formatter.trade_type',),
// 				'冻结原因'=>array('field'=>'freeze_reason','width'=>'100',),
				'货品数量'=>array('field'=>'goods_count','width'=>'100',),
				'货品种类'=>array('field'=>'goods_type_count','width'=>'100',),
				'下单时间'=>array('field'=>'trade_time','width'=>'100',),
				'买家留言'=>array('field'=>'buyer_message','width'=>'100',),
		        
				//'是否包含发票'=>array('field'=>'has_invoice','width'=>'100',),
				'id'=>array('field'=>'id','hidden'=>true,),
				/* 'receiver_province'=>array('field'=>'receiver_province','hidden'=>true,),
				'receiver_city'=>array('field'=>'receiver_city','hidden'=>true,),
				'receiver_district'=>array('field'=>'receiver_district','hidden'=>true,),
				'bill_type'=>array('field'=>'bill_type','hidden'=>true,),
				'src_order_type'=>array('field'=>'src_order_type','hidden'=>true,),
				'src_order_id'=>array('field'=>'src_order_id','hidden'=>true,),
				'warehouse_id'=>array('field'=>'warehouse_id','hidden'=>true,),
				'logistics_id'=>array('field'=>'logistics_id','hidden'=>true,),
				'contact'=>array('field'=>'contact','hidden'=>true,),
				'mobile'=>array('field'=>'mobile','hidden'=>true,),
				'telno'=>array('field'=>'telno','hidden'=>true,),
				'province'=>array('field'=>'province','hidden'=>true,),
				'city'=>array('field'=>'city','hidden'=>true,),
				'district'=>array('field'=>'district','hidden'=>true,),
				'address'=>array('field'=>'address','hidden'=>true,),
				'zip'=>array('field'=>'zip','hidden'=>true,),
				'logistics_type'=>array('field'=>'logistics_type','hidden'=>true,),
				'waybill_info'=>array('field'=>'waybill_info','hidden'=>true,), */
			),
    	'stockout_add_waybill'=>array(
    		'订单编号'=>array('field'=>'src_order_no','width'=>'20%'),
    		'店铺'=>array('field'=>'shop_name','width'=>'20%',),
    		'收货人'=>array('field'=>'receiver_name','width'=>'20%',),
    		'物流单号'=>array('field'=>'logistics_no','width'=>'30%','editor' => '{type:"textbox"}'),
    		'id'=>array('field'=>'id','hidden'=>true,),
    		'index'=>array('field'=>'index','hidden'=>true,),
            '包裹数'=>array('field'=>'package_count','width'=>'20%','editor' => '{type:"textbox"}')
    	   ),
			'stockout_add_multi_waybill'=>array(
//    		'订单编号'=>array('field'=>'src_order_no','width'=>'20%'),
    		'店铺'=>array('field'=>'shop_name','width'=>'20%',),
    		'收货人'=>array('field'=>'receiver_name','width'=>'20%',),
    		'物流单号'=>array('field'=>'logistics_no','width'=>'30%','editor' => '{type:"text"}'),
    		'id'=>array('field'=>'id','hidden'=>true,),
    		'index'=>array('field'=>'index','hidden'=>true,)
    	   ),
			/*订单编号、状态、发货状态、收货人、收货地区、收货地址、物流公司、物流单号、物流单打印状态、拦截原因、店铺、仓库*/
			'salesstockout_order'=>array(
		     'id'=>array('field'=>'id','hidden'=>true,'sortable'=>true),
			 'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
		     '订单编号'=>array('field'=>'src_order_no','width'=>'100','sortable'=>true),
			 '出库单状态'=>array('field'=>'status','width'=>'100','formatter'=>$salesstockout_status,'sortable'=>true),
			 '发货状态'=>array('field'=>'consign_status','width'=>'100','formatter'=>'formatter.sales_consign_status','sortable'=>true),
			 '物流同步情况'=>array('field'=>'logistics_sync_info','width'=>'120'),
             '收货人'=>array('field'=>'receiver_name','width'=>'100','sortable'=>true),
			 '网名'=>array('field'=>'buyer_nick','width'=>'100','sortable'=>true),
			 '收货地区'=>array('field'=>'receiver_area','width'=>'120','sortable'=>true),
			 '收货地址'=>array('field'=>'receiver_address','width'=>'150','sortable'=>true),
				'货品摘要'=>array('field'=>'goods_abstract','width'=>'200','sortable'=>true),
				'金额'=>array('field'=>'paid','width'=>'100','sortable'=>true),
			 '物流公司id'=>array('field'=>'logistics_id','width'=>'100','hidden'=>true,'sortable'=>true),
			 '物流公司'=>array('field'=>'logistics_name','width'=>'100','sortable'=>true),
			 '物流单号'=>array('field'=>'logistics_no','width'=>'100','sortable'=>true),
			 '物流单打印状态'=>array('field'=>'logistics_print_status','width'=>'100','formatter'=>'formatter.print_status','sortable'=>true),
			 '发货单打印状态'=>array('field'=>'sendbill_print_status','width'=>'100','formatter'=>'formatter.print_status','sortable'=>true),
			 '分拣单打印状态'=>array('field'=>'picklist_print_status','width'=>'100','formatter'=>'formatter.print_status','sortable'=>true),
			 '打印批次' =>array('field'=>'batch_no','width'=>'100','sortable'=>true),
			 '分拣批次' =>array('field'=>'picklist_no','width'=>'100','sortable'=>true),
			 '买家留言'=>array('field'=>'buyer_message','width'=>'100','sortable'=>true),
			 '客服备注'=>array('field'=>'cs_remark','width'=>'100','sortable'=>true),
			 '打印备注'=>array('field'=>'print_remark','width'=>'100','sortable'=>true),
			 '拦截原因'=>array('field'=>'block_reason','width'=>'100','formatter'=>'formatter.stockout_block_reason','sortable'=>true),
			 '仓库id'=>array('field'=>'warehouse_id','width'=>'100','hidden'=>true,'sortable'=>true),
			 '仓库'=>array('field'=>'warehouse_name','width'=>'100','sortable'=>true),
			 '店铺id'=>array('field'=>'shop_id','width'=>'100','hidden'=>true,'sortable'=>true),
			 '店铺'=>array('field'=>'shop_name','width'=>'100','sortable'=>true),
			 '出库单编号'=>array('field'=>'stockout_no','width'=>'100','sortable'=>true),
		     '原始单号'=>array('field'=>'src_tids','width'=>'100','sortable'=>true),
//		     '仓库类型'=>array('field'=>'warehouse_type','width'=>'100','formatter'=>'formatter.warehouse_type'),
		     '订单类型'=>array('field'=>'trade_type','width'=>'100','formatter'=>'formatter.trade_type','sortable'=>true),
		     '审单员'=>array('field'=>'checker_name','width'=>'100','sortable'=>true),
		     '货品数量'=>array('field'=>'goods_count','width'=>'100','sortable'=>true),
		     '货品种类'=>array('field'=>'goods_type_count','width'=>'100','sortable'=>true),
//             '图片'=>array('field'=>'img_url','width'=>'auto','formatter'=>'formatter.print_img'),
             '收件人手机'=>array('field'=>'receiver_mobile','width'=>'100','sortable'=>true),
		     '收件人电话'=>array('field'=>'receiver_telno','width'=>'100','sortable'=>true),
		     '邮编'=>array('field'=>'receiver_zip','width'=>'100','sortable'=>true),
		     '总成本'=>array('field'=>'goods_total_cost','width'=>'100','sortable'=>true),
			 '预估邮资成本'=>array('field'=>'calc_post_cost','width'=>'100','sortable'=>true),
			 '邮资成本'=>array('field'=>'post_cost','width'=>'100','sortable'=>true),
			 '预估重量'=>array('field'=>'calc_weight','width'=>'100','sortable'=>true),
			 '实际重量'=>array('field'=>'weight','width'=>'100','sortable'=>true),
		     '是否包含发票'=>array('field'=>'has_invoice','width'=>'100','formatter'=>'formatter.boolen','sortable'=>true), 
		     '是否为档口单'=>array('field'=>'is_stalls','width'=>'100','formatter'=>'formatter.boolen','sortable'=>true),
			 '发票信息'=>array('field'=>'invoice_message','width'=>'120'),
			 '发票抬头'=>array('field'=>'invoice_title','width'=>'120'),
			 '发票内容'=>array('field'=>'invoice_content','width'=>'120'),
			 '下单时间'=>array('field'=>'trade_time','width'=>'100','sortable'=>true),
			 '支付时间'=>array('field'=>'pay_time','width'=>'100','sortable'=>true),
			 '发货时间'=>array('field'=>'consign_time','width'=>'100','sortable'=>true),
		     'flag_id'=>array('field'=>'flag_id','width'=>'100','hidden'=>true,'sortable'=>true),
		     '包裹数'=>array('field'=>'package_count','width'=>'100'),
		     '目的地编码'=>array('field'=>'receiver_dtb','hidden'=>true),
		     'app_key'=>array('field'=>'app_key','hidden'=>true),
		     'waybill_info'=>array('field'=>'waybill_info','hidden'=>true)
		 ),
		'stockout_has_printed_info'=>array(
		     'id'     =>array('field'=>'stock_id','hidden'=>true),
		     '出库单号' =>array('field'=>'stock_no','width'=>'200'),
		     '错误信息' =>array('field'=>'msg','width'=>'400')
		 ),
		'multilogistic_has_printed_info'=>array(
		     'id'     =>array('field'=>'rec_id','hidden'=>true),
		     '出库单号' =>array('field'=>'stock_no','width'=>'150'),
		     '多物流单号' =>array('field'=>'logistics_no','width'=>'150'),
		     '错误信息' =>array('field'=>'msg','width'=>'400')
		 ),
		'sales_print_status'=>array(
				'id'     =>array('field'=>'print_class','hidden'=>true),
				'打印单据' =>array('field'=>'print_order_name','width'=>'150'),
				'打印状态' =>array('field'=>'print_status','width'=>'80','formatter'=>'formatter.print_status_chg','editor'=>'{type:"combobox","options":{required:true,valueField: "id",textField: "name",data:formatter.get_data("print_status_chg") }}')
		),
		'print_batch'=>array(
				'id'     =>array('field'=>'rec_id','hidden'=>true),
				'打印批次' =>array('field'=>'batch_no','width'=>'150','sortable'=>true),
				'分拣批次' =>array('field'=>'pick_list_no','width'=>'150','sortable'=>true),
				'单据' =>array('field'=>'order_mask','width'=>'150','formatter'=>'formatter.print_type','sortable'=>true),
				'订单数' =>array('field'=>'order_num','width'=>'100','sortable'=>true),
				'创建时间' =>array('field'=>'created','width'=>'250','sortable'=>true),
		),
		'stockout_add_package'=>array(
			'id'=>array('field'=>'id','hidden'=>true,),
			'index'=>array('field'=>'index','hidden'=>true,),
			'订单编号'=>array('field'=>'src_order_no','width'=>'23%'),
			'店铺'=>array('field'=>'shop_name','width'=>'25%',),
			'收货人'=>array('field'=>'receiver_name','width'=>'25%',),
			//'物流单号'=>array('field'=>'logistics_no','width'=>'30%','editor' => '{type:"textbox"}'),
			'包裹数'=>array('field'=>'package_count','width'=>'25%','editor' => '{type:"textbox"}')
		),
		'include_goods'=>array(
			'id'     =>array('field'=>'rec_id','hidden'=>true),
			'商家编码' =>array('field'=>'spec_no','width'=>'18%','sortable'=>true),
			'货品编号' =>array('field'=>'goods_no','width'=>'18%','sortable'=>true),
			'货品名称' =>array('field'=>'goods_name','width'=>'20%','sortable'=>true),
			'规格码' =>array('field'=>'spec_code','width'=>'18%','sortable'=>true),
			'条件(可编辑)' =>array('field'=>'condition','width'=>'10%','editor'=>'{type:"combobox",options:{valueField:"id",textField:"name",data:[{"id":"0","name":"小于"},{"id":"1","name":"等于"},{"id":"2","name":"大于"}],editable:false,value:"1"}}'),
			'数量(可编辑)' =>array('field'=>'num','width'=>'10%','editor'=>'{type:"numberbox",options:{required:true,precision:0,min:0}}'),
		),
	);
		return $fields[$key];
	}

}