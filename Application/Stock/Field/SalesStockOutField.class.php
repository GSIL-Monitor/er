<?php
namespace Stock\Field;

use Common\Common\Field;

class SalesStockOutField extends Field
{
    protected function get($key){
    	if($key=="sales_stockout"){
    		$salesstockout_status='formatter.salesstockout_status';
    		$res_cfg_val=get_config_value('order_fa_condition',0);
    		if($res_cfg_val==1){
    			$salesstockout_status='formatter.salesstockout_status_fc';
    		}
    	}
        $fields = array(
            /*'sales_stockout' => array(
                    'flag_id'=> array('field'=>'flag_id','hidden'=>true,'sortable'=>true),
                    '订单编号'=>array('field'=>'src_order_no','width'=>'100'),
                    '出库单编号'=>array('field'=>'stockout_no','width'=>'100',),
                    '仓库'=>array('field'=>'warehouse_id','width'=>'100',),
                    '仓库类型'=>array('field'=>'warehouse_type','width'=>'100','formatter'=>'formatter.warehouse_type'),
                    '店铺'=>array('field'=>'shop_id','width'=>'100',),
                    '订单类型'=>array('field'=>'trade_type','width'=>'100','formatter'=>'formatter.trade_type'),
                    '下单时间'=>array('field'=>'trade_time','width'=>'100',),
                    '支付时间'=>array('field'=>'pay_time','width'=>'100',),
                    '状态'=>array('field'=>'status','width'=>'100','formatter'=>'formatter.stockout_status'),
                    //'错误信息'=>array('field'=>'error_info','width'=>'100',),
                    '发货状态'=>array('field'=>'consign_status','width'=>'100','formatter'=>'formatter.sales_consign_status'),
                    //'冻结原因'=>array('field'=>'freeze_reason','width'=>'100',),
//                     '制单人'=>array('field'=>'operator_id','width'=>'100',),
                    '货品数量'=>array('field'=>'goods_count','width'=>'100',),
                    '货品种类'=>array('field'=>'goods_type_count','width'=>'100',),
                    '网名'=>array('field'=>'buyer_nick','width'=>'100',),
                    '收货人'=>array('field'=>'receiver_name','width'=>'100',),
                    '收货地区'=>array('field'=>'receiver_area','width'=>'100',),
                    '收货地址'=>array('field'=>'receiver_address','width'=>'100',),
                    '收件人手机'=>array('field'=>'receiver_mobile','width'=>'100',),
                    '收件人电话'=>array('field'=>'receiver_telno','width'=>'100',),
                    '邮编'=>array('field'=>'receiver_zip','width'=>'100',),
                    '物流公司'=>array('field'=>'logistics_id','width'=>'100',),
                    '总成本'=>array('field'=>'goods_total_cost','width'=>'100',),
//                     '预估邮资'=>array('field'=>'calc_post_cost','width'=>'100',),
//                     '邮资成本'=>array('field'=>'post_cost','width'=>'100',),
                    '预估重量'=>array('field'=>'calc_weight','width'=>'100',),
//                     '实际重量'=>array('field'=>'weight','width'=>'100',),
                    '是否包含发票'=>array('field'=>'has_invoice','width'=>'100','formatter'=>'formatter.boolen'),
                    //'业务员'=>array('field'=>'salesman_id','width'=>'100',),
//                     '审单员'=>array('field'=>'checker_id','width'=>'100',),
                    //'财审员'=>array('field'=>'fchecker_id','width'=>'100',),
//                     '打单员'=>array('field'=>'printer_id','width'=>'100',),
                    //'拣货员'=>array('field'=>'picker_id','width'=>'100',),
                    //'打包员'=>array('field'=>'packager_id','width'=>'100',),
                    //'扫描员'=>array('field'=>'examiner_id','width'=>'100',),
                    //'检视员'=>array('field'=>'watcher_id','width'=>'100',),
                    //'打印批次'=>array('field'=>'batch_no','width'=>'100',),
                    '物流单号'=>array('field'=>'logistics_no','width'=>'100',),
                    //'分拣单编号'=>array('field'=>'picklist_no','width'=>'100',),
                    '发货时间'=>array('field'=>'consign_time','width'=>'100',),
                    //'外部单号'=>array('field'=>'outer_no','width'=>'100',),
                    'id'=>array('field'=>'id','hidden'=>true,)
            ),*/
            'sales_stockout'=>array(
                'id'=>array('field'=>'id','width'=>'100','hidden'=>true),
                '订单编号'=>array('field'=>'src_order_no','width'=>'100'),
                '出库单编号'=>array('field'=>'stockout_no','width'=>'100'),
                '原始单号'=>array('field'=>'src_tids','width'=>'100'),
                '仓库id'=>array('field'=>'warehouse_id','width'=>'100','hidden'=>true),
                '仓库'=>array('field'=>'warehouse_name','width'=>'100'),
                '仓库类型'=>array('field'=>'warehouse_type','width'=>'100','formatter'=>'formatter.warehouse_type'),
                '店铺id'=>array('field'=>'shop_id','width'=>'100','hidden'=>true),
                '店铺'=>array('field'=>'shop_name','width'=>'100'),
                '订单类型'=>array('field'=>'trade_type','width'=>'100','formatter'=>'formatter.trade_type'),
                '下单时间'=>array('field'=>'trade_time','width'=>'100'),
                '支付时间'=>array('field'=>'pay_time','width'=>'100'),
                '出库单状态'=>array('field'=>'status','width'=>'100','formatter'=>$salesstockout_status),
                '发货状态'=>array('field'=>'consign_status','width'=>'100','formatter'=>'formatter.sales_consign_status'),
                '物流同步情况'=>array('field'=>'logistics_sync_info','width'=>'auto'),
                '拦截原因'=>array('field'=>'block_reason','width'=>'100','formatter'=>'formatter.stockout_block_reason'),
                '审单员'=>array('field'=>'checker_name','width'=>'100'),
                '货品数量'=>array('field'=>'goods_count','width'=>'100'),
                '货品种类'=>array('field'=>'goods_type_count','width'=>'100'),
                '网名'=>array('field'=>'buyer_nick','width'=>'100'),
                '收货人'=>array('field'=>'receiver_name','width'=>'100'),
                '收货地区'=>array('field'=>'receiver_area','width'=>'100'),
                '收货地址'=>array('field'=>'receiver_address','width'=>'100'),
                '收件人手机'=>array('field'=>'receiver_mobile','width'=>'100'),
                '收件人电话'=>array('field'=>'receiver_telno','width'=>'100'),
                '货品摘要'=>array('field'=>'goods_abstract','width'=>'200'),
                '金额'=>array('field'=>'paid','width'=>'100'),
                '邮编'=>array('field'=>'receiver_zip','width'=>'100'),
                '物流公司id'=>array('field'=>'logistics_id','width'=>'100','hidden'=>true),
                '物流公司'=>array('field'=>'logistics_name','width'=>'100'),
                '买家留言'=>array('field'=>'buyer_message','width'=>'100'),
                '总成本'=>array('field'=>'goods_total_cost','width'=>'100'),
                '预估邮资成本'=>array('field'=>'calc_post_cost','width'=>'100',),
                '邮资成本'=>array('field'=>'post_cost','width'=>'100',),
                '预估重量'=>array('field'=>'calc_weight','width'=>'100',),
                '实际重量'=>array('field'=>'weight','width'=>'100',),
                '是否包含发票'=>array('field'=>'has_invoice','width'=>'100','formatter'=>'formatter.boolen'),
                '物流单打印状态'=>array('field'=>'logistics_print_status','width'=>'100','formatter'=>'formatter.print_status'),
                '发货单打印状态'=>array('field'=>'sendbill_print_status','width'=>'100','formatter'=>'formatter.print_status'),
                '物流单号'=>array('field'=>'logistics_no','width'=>'130'),
				'外部WMS单号'=>array('field'=>'outer_no','width'=>'100'),
				'WMS错误信息'=>array('field'=>'error_info','width'=>'150'),
                '发货时间'=>array('field'=>'consign_time','width'=>'100'),
                'flag_id'=>array('field'=>'flag_id','width'=>'100','hidden'=>true),
            ),
        );
        return $fields[$key];
    }
}
