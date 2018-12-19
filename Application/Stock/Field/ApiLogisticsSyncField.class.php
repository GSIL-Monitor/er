<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:32
 */
namespace Stock\Field;
use Common\Common\Field;

class ApiLogisticsSyncField extends Field{
    protected function get($key){
        $fields = array(
            'apilogisticssync'=>array(
                '平台'=>array('field'=>'platform_id','width'=>'100','formatter'=>'formatter.platform_id'),
                '店铺名'=>array('field'=>'shop_id','width'=>'100'),
                '原始单号'=>array('field'=>'tid','width'=>'100'),
                '订单号'=>array('field'=>'trade_no','width'=>'100'),
                '是否需要同步'=>array('field'=>'is_need_sync','width'=>'100','formatter'=>'formatter.boolen'),
                '同步状态'=>array('field'=>'sync_status','width'=>'100','formatter'=>'formatter.logistics_sync_status'),
                '错误信息'=>array('field'=>'error_msg','width'=>'100'),
                '下单时间'=>array('field'=>'trade_time','width'=>'100'),
                '同步时间'=>array('field'=>'sync_time','width'=>'100'),
                '客户网名'=>array('field'=>'buyer_nick','width'=>'100'),
                '物流'=>array('field'=>'logistics_id','width'=>'100'),
                '物流单号'=>array('field'=>'logistics_no','width'=>'100'),
                '物流类型'=>array('field'=>'bill_type','width'=>'100','hidden'=>true),
                'id' 	  =>array('field'=>'id','hidden'=>true)
            ),
            'api_logistics_sync_infor'=>array(
                'id'      =>array('field'=>'id','hidden'=>true),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'100'),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '成交价'=>array('field'=>'order_price','width'=>'100'),
                '货品数量'=>array('field'=>'num','width'=>'100'),
                '货品总额'=>array('field'=>'sum_price','width'=>'100'),
            ),
            'dialog_solution'=>array(
                '错误信息'=>array('field'=>'error_msg','width'=>'200'),
                '错误原因'=>array('field'=>'reason','width'=>'250')
            )
        );
        return $fields[$key];
    }
}