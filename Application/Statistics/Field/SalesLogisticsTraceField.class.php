<?php

namespace Statistics\Field;

use Common\Common\Field;

class SalesLogisticsTraceField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "sales_logistics_trace" => array(
                'id'      => array('field' => 'id', 'hidden' => true),
                '店铺' => array('field' => 'shop_name', 'width' => 100),
                '仓库' => array('field' => 'warehouse_name', 'width' => 100),
                '订单编号' => array('field' => 'trade_no', 'width' => 100),
                '出库单号' => array('field' => 'stockout_no', 'width' => 100),
                '原始单号' => array('field' => 'src_tids', 'width' => 100 ),
                '昵称' => array('field' => 'buyer_nick', 'width' => 100 ),
                '收件人' => array('field' => 'receiver_name', 'width' => 100 ),
                '电话' => array('field' => 'receiver_mobile', 'width' => 100 ),
                '应收' => array('field' => 'receivable', 'width' => 100 ),
                '发货条件' => array('field' => 'delivery_term', 'width' => 100 ,'formatter' => 'formatter.delivery_term'),
                '省市区' => array('field' => 'receiver_area', 'width' => 100 ),
                '地址' => array('field' => 'receiver_addr', 'width'=>100),
                '物流公司' => array('field' => 'logistics_name' , 'width' => 100),
                '物流单号' => array('field' => 'logistics_no','width'=>100),
                '状态' => array('field' => 'logistics_status','width'=>100,'formatter' => 'formatter.logistics_trace_type'),
                '支付时间' => array('field' => 'pay_time' , 'width' => 100),
                '发货时间' => array('field' => 'created' , 'width' => 100),
                '揽件时间' => array('field' => 'get_time', 'width' => 100 ),
                '备注' => array('field' => 'remark', 'width' => 100 ),
                '最后修改时间' => array('field' => 'modified', 'width' => 100 ),
            ),

            "logistics_trace_detail" => array(
                '物流状态'=>array('field' => 'accept_station','width'=>'50%' ),
                '时间' => array('field' => 'accept_time','width'=>'50%' ),
            )
        );
        return $fields[$key];
    }
}