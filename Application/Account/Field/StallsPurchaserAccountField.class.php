<?php
namespace Account\Field;

use Common\Common\Field;

class StallsPurchaserAccountField extends Field{
    protected function get($key) {
        $fields = array(
            'stalls_purchaser_account' => array(
                'id'         => array('field' => 'rec_id', 'hidden' => true, 'sortable' => true),
                '采购员'      => array('field' => 'purchaser_name', 'width' => 150),
                '供货商名称'   => array('field' => 'provider_name', 'width' => 200),
                '入库数量'     => array('field' => 'in_num', 'width' => 150, 'sortable' => true),
                '取货数量'     => array('field' => 'put_num', 'width' => 150,'sortable' => true),
                //'入库日期'     => array('field' => 'stockin_date', 'width' => 250,'sortable' => true),
                '总金额'   => array('field' => 'total_price', 'width' => 150,'sortable' => true),
                '取货日期'     => array('field' => 'pickup_date', 'width' => 250,'sortable' => true)

            )

        );
        return $fields[ $key ];
    }
}