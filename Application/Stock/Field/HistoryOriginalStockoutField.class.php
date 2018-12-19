<?php
/**
 * Created by PhpStorm.
 * User: Asher
 * Date: 2016-10-27
 * Time: 14:17
 */

namespace Stock\Field;
use Common\Common\Field;

class HistoryOriginalStockoutField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'history_original_stockout'=>array(
                '出库单号' => array('field' => 'stockout_no', 'width' => '100', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.history_stockout_status',),
                '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockout_type',),
                '源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center'),
                '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center'),
                '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
                '邮资' => array('field' => 'post_cost', 'width' => '100', 'align' => 'center'),
                '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
                '发货时间' => array('field' => 'consign_time', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
            )
        );
        return $fields[$key];
    }
}