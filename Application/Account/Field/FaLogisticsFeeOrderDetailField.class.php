<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/6/14
 * Time: 4:51
 */
namespace Account\Field;

use Common\Common\Field;

class FaLogisticsFeeOrderDetailField extends Field {
    protected function get($key) {
        $fields = array(
            "falogisticsfeeorderdetail" => array(
                '物流单号' => array('field' => 'logistics_no', 'width' => 100),
                '目的地区' => array('field' => 'area', 'width' => 80),
                '邮资'   => array('field' => 'postage', 'width' => 100),
                '导入邮资' => array('field' => 'import_postage', 'width' => 100),
                '邮资差额' => array('field' => 'diff_postage', 'width' => 100),
                '重量kg' => array('field' => 'weight', 'width' => 80),
                '导入重量' => array('field' => 'import_weight', 'width' => 80),
                '重量差'  => array('field' => 'diff_weight', 'width' => 80),
                '备注'   => array('field' => 'renark', 'width' => 80),
                '导入备注' => array('field' => 'import_summary', 'width' => 80),
                '创建时间' => array('field' => 'created', 'width' => 80),
                '导入时间' => array('field' => 'import_time', 'width' => 80),
            )
        );
        return $fields[$key];
    }
}