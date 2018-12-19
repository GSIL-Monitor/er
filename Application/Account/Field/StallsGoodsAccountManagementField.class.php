<?php
namespace Account\Field;

use Common\Common\Field;

class StallsGoodsAccountManagementField extends Field{
    protected function get($key) {
        $fields = array(
            'stalls_goods_account' => array(
                'id'         => array('field' => 'rec_id', 'hidden' => true, 'sortable' => true),
                'checkbox'   => array('field'=>'ck','hidden'=>false,'checkbox'=>true),
                '结算状态'        => array('field' => 'status', 'width' => 80,'formatter'=>'formatter.stat_stalls_status'),
                '货品编码'     => array('field' => 'goods_no', 'width' => 150, 'sortable' => true),
                '货品名称'     => array('field' => 'goods_name', 'width' => 200, 'sortable' => true),
                '商家编码'     => array('field' => 'spec_no', 'width' => 150),
                '分类'        => array('field' => 'class_id', 'width' => 150),
                '品牌'        => array('field' => 'brand_id', 'width' => 150),
                '供货商名称'   => array('field' => 'provider_name', 'width' => 200),
                //'采购价'      => array('field' => 'retail_price', 'width' => 150),
                '数量'        => array('field' => 'num', 'width' => 150,'sortable' => true),
                '入库日期'     => array('field' => 'created', 'width' => 250,'sortable' => true),
                '采购总金额'   => array('field' => 'price', 'width' => 150,'sortable' => true),
                '导入金额'     => array('field' => 'import_price', 'width' => 150, 'sortable' => false),
                '采购差额'     => array('field' => 'diff_price', 'width' => 150, 'sortable' => true),
                '结算人'       => array('field' => 'charge_oper_id', 'width' => 80, 'sortable' => true),
                '结算时间'     => array('field' => 'charge_time', 'width' => 150, 'sortable' => true),


            )

        );
        return $fields[ $key ];
    }
}