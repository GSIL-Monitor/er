<?php

namespace Statistics\Field;

use Common\Common\Field;

class StockinCollectField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stockincollect" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '商家编码' => array('field' => 'spec_no', 'width' => 150),
                '货品编码' => array('field' => 'goods_no', 'width' => 150),
                '货品名称' => array('field' => 'goods_name', 'width' => 150 ),
                '规格名称' => array('field' => 'spec_name', 'width' => 150 ),
                '数量' => array('field' => 'num', 'width'=>140),
                '合计金额' => array('field' => 'total_cost','width'=>140),
                '入库均价' => array('field' => 'price' , 'width' => 140)
            ),
        );
        return $fields[$key];
    }
}