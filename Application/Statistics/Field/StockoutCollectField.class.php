<?php

namespace Statistics\Field;

use Common\Common\Field;

class StockoutCollectField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stockoutcollect" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '商家编码' => array('field' => 'spec_no', 'width' => 150),
                '货品编码' => array('field' => 'goods_no', 'width' => 150),
                '货品名称' => array('field' => 'goods_name', 'width' => 150 ),
                '规格名称' => array('field' => 'spec_name', 'width' => 150 ),
                '数量' => array('field' => 'num', 'width'=>140),
                '合计金额' => array('field' => 'total_price','width'=>140),
                '成本总额' => array('field' => 'cost_price' , 'width' => 140)
            ),
        );
        return $fields[$key];
    }
}
