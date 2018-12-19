<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:21
 */
namespace Setting\Field;
use Common\Common\Field;
class LogisticsField extends Field{
    protected function get($key){
        $fields = array(
            'logistics' => array(
                'id' => array('field' => 'logistics_id', 'hidden' => true, 'sortable' => true, 'align' => 'center'),
                //'物流编号' => array('field' => 'logistics_no', 'width' => 100, 'sortable' => true, 'align' => 'center'),
                '物流名称' => array('field' => 'logistics_name', 'width' => 150, 'sortable' => true, 'align' => 'center'),
                '物流类型' => array('field' => 'logistics_type', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.logistics_type', 'align' => 'center'),
                '联系人' => array('field' => 'contact', 'width' => 100, 'sortable' => true, 'align' => 'center'),
                '单号类型' => array('field' => 'bill_type', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.bill_type', 'align' => 'center'),
                '电子面单授权' => array('field' =>'is_authorized','width'=>100,'sortable' => true, 'formatter' => 'formatter.toYN', 'align' => 'center'),
                '联系电话' => array('field' => 'telno', 'width' => 100, 'sortable' => true),
                '地址' => array('field' => 'address', 'width' => 100, 'sortable' => true),
                '手动获取单号' => array('field' => 'is_manual', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.toYN', 'align' => 'center'),
                '支持货到付款' => array('field' => 'is_support_cod', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.toYN', 'align' => 'center'),
                '停用' => array('field' => 'is_disabled', 'width' => 60, 'sortable' => true, 'formatter' => 'formatter.toYN', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '200', 'sortable' => true),
            ),
        );
        return $fields[$key];
    }
}