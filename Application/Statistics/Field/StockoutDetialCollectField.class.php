<?php

namespace Statistics\Field;

use Common\Common\Field;

class StockoutDetialCollectField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stockoutdetialcollect" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '出库单号' => array('field' => 'stockout_no', 'width' => 150),
                '仓库' => array('field' => 'warehouse_name', 'width' => 150),
                '经办人' => array('field' => 'fullname', 'width' => 150),
                '出库类型' => array('field' => 'src_order_type', 'width' => 150,'formatter' => 'formatter.stockout_type_all'),
                '来源单号' => array('field' => 'src_order_no', 'width' => 150),
                '物流单号' => array('field' => 'logistics_no', 'width' => 150),
                '商家编码' => array('field' => 'spec_no', 'width' => 150),
                '规格名称' => array('field' => 'spec_name', 'width' => 150 ),
                '规格码' => array('field' => 'spec_code', 'width' => 150 ),
                '货品编号' => array('field' => 'goods_no', 'width' => 150),
                '货品名称' => array('field' => 'goods_name', 'width' => 150 ),
                '货品分类' => array('field' => 'class_id', 'width' => 150 ),
                '货品品牌' => array('field' => 'brand_id', 'width' => 150 ),
                '数量' => array('field' => 'num', 'width'=>140),
                '出库货位' => array('field' => 'position_no','width'=>140),
                '单价' => array('field' => 'price','width'=>140),
                '合计金额' => array('field' => 'total_amount','width'=>140),
                '成本价' => array('field' => 'cost_price','width'=>140),
                '成本总额' => array('field' => 'total_cost_price','width'=>140),
                '出库时间' => array('field' => 'consign_time' , 'width' => 140),
                '备注' => array('field' => 'remark' , 'width' => 140),
                '商品备注' => array('field' => 'goods_remark' , 'width' => 140)
            ),
        );
        return $fields[$key];
    }
}
