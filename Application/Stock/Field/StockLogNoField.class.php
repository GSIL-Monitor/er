<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 11:00
 */
namespace Stock\Field;
use Common\Common\Field;
class StockLogNoField extends Field{
    protected function get($key){
        $fields = array(
            'stocklogno'=>array(
                'id' => array('field' => 'id','hidden'=>'true',  'sortable' => true, 'align' => 'center'),
                '物流公司' => array('field' => 'logistics_id',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '热敏类型' => array('field' => 'bill_type_name',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '面单类型' => array('field' => 'bill_type',  'sortable' => true, 'align' => 'center','width'=>'200','hidden'=>true),
                '物流单号' => array('field' => 'logistics_no',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '状态' => array('field' => 'status',  'sortable' => true, 'align' => 'center','width'=>'200','formatter'=>'formatter.stock_logistics_no_status',),
                '获取时间' => array('field' => 'created',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '修改时间' => array('field' => 'modified',  'sortable' => true, 'align' => 'center','width'=>'200'),
            ),
        );
        return $fields[$key];
    }
}