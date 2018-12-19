<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:43
 */
namespace Stock\Field;

use Common\Common\Field;

class StockSyncField extends Field {
    protected function get($key) {
        $fields = array(
            'stocksync' => array(
                '店铺'       => array('field' => 'shop_id', 'width' => 100, 'sortable' => false),
                '平台货品商家编码' => array('field' => 'outer_id', 'width' => 100, 'sortable' => false),
                '平台货品ID'   => array('field' => 'goods_id', 'width' => 100, 'sortable' => false),
                '平台规格ID'   => array('field' => 'spec_id', 'width' => 100, 'sortable' => false),
                '平台货品名称'   => array('field' => 'api_goods_name', 'width' => 100, 'sortable' => false),
                '平台规格商家编码' => array('field' => 'spec_outer_id', 'width' => 100, 'sortable' => false),
                '平台规格名称'   => array('field' => 'api_spec_name', 'width' => 100, 'sortable' => false),
                '货品商家编码'   => array('field' => 'spec_no', 'width' => 100, 'sortable' => false),
                '货品类型'     => array('field' => 'goods_type', 'width' => 100, 'sortable' => false),
                '同步数量'     => array('field' => 'syn_stock', 'width' => 100, 'sortable' => false),
                '同步规则编号'   => array('field' => 'stock_syn_rule_no', 'width' => 100, 'sortable' => false),
                '同步仓库'     => array('field' => 'stock_syn_warehouses', 'width' => 100, 'sortable' => false, 'formatter' => 'warehouseFormatter'),
                '同步数量计算方式' => array('field' => 'stock_syn_mask', 'width' => 100, 'sortable' => false),
                '同步百分比'    => array('field' => 'stock_syn_percent', 'width' => 100, 'sortable' => false),
                '同步附加量'    => array('field' => 'stock_syn_plus', 'width' => 100, 'sortable' => false),
                '最小同步量'    => array('field' => 'stock_syn_min', 'width' => 100, 'sortable' => false),
                '自动上架'     => array('field' => 'is_auto_listing', 'width' => 100, 'sortable' => false),
                '自动下架'     => array('field' => 'is_auto_delisting', 'width' => 100, 'sortable' => false),
                '是否同步成功'   => array('field' => 'is_syn_sucess', 'width' => 100, 'sortable' => false),
                '同步结果'     => array('field' => 'syn_result', 'width' => 100, 'sortable' => false),
                '同步方式'     => array('field' => 'syn_type', 'width' => 100, 'sortable' => false),
                '同步时间'     => array('field' => 'created', 'width' => 100, 'sortable' => false),
            ),
        );
        return $fields[ $key ];
    }
}