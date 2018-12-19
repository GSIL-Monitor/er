<?php

namespace Statistics\Field;

use Common\Common\Field;

class StockinDetialCollectField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stockindetialcollect" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '入库单号' => array('field' => 'stockin_no', 'width' => 100),
                '入库类型' => array('field' => 'src_order_type', 'width' => 90,'formatter' => 'formatter.stockin_type'),
                '仓库' => array('field' => 'warehouse_name', 'width' => 120),
                '入库货位' => array('field' => 'position_no', 'width'=>100),
                '经办人' => array('field' => 'fullname', 'width' => 80),
                '物流单号' => array('field' => 'logistics_no', 'width'=>100),
                '商家编码' => array('field' => 'spec_no', 'width' => 150),
                '货品编码' => array('field' => 'goods_no', 'width' => 150),
                '货品名称' => array('field' => 'goods_name', 'width' => 150 ),
                '规格名称' => array('field' => 'spec_name', 'width' => 150 ),
                '数量' => array('field' => 'num', 'width'=>100),
                '调整后数量' => array('field' => 'spec_right_num', 'width'=>100),
                '调整后总数量' => array('field' => 'right_num', 'width'=>100),
                '入库价' => array('field' => 'cost_price', 'width'=>100),
                '调整后单价' => array('field' => 'right_price', 'width'=>100),
                '原价' => array('field' => 'src_price', 'width'=>100),
                '合计金额' => array('field' => 'total_cost', 'width'=>100),
                '调整后金额' => array('field' => 'right_cost','width'=>100),
                '调整后总金额' => array('field' => 'total_right_price' , 'width' => 100),
                '审核时间' => array('field' => 'check_time','width'=>140),
                '备注' => array('field' => 'remark','width'=>140)
            ),
        );
        return $fields[$key];
    }
}