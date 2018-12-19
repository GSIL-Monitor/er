<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:20
 */
namespace Setting\Field;
use Common\Common\Field;
class StockSyncStrategyField extends Field{
    protected function get($key){
        $fields = array(
            'stocksyncstrategy' => array(
                'id' => array('field' => 'id', 'width' => '100', 'hidden' => 'true'),
                '规则编号' => array('field' => 'rule_no', 'width' => '100'),
                '规则名称' => array('field' => 'rule_name', 'width' => '100'),
                '优先级' => array('field' => 'priority', 'width' => '100'),
                '店铺列表' => array('field' => 'shop_list', 'width' => '100', 'formatter' => 'shopFormatter'),
                '库存列表' => array('field' => 'warehouse_list', 'width' => '100', 'formatter' => 'warehouseFormatter'),
                '分类' => array('field' => 'class_id', 'width' => '100', 'formatter' => 'classFormatter'),
                '品牌' => array('field' => 'brand_id', 'width' => '100', 'formatter' => 'brandFormatter'),
                '库存方法' => array('field' => 'stock_flag_string', 'width' => '300'),
                '百分比' => array('field' => 'percent', 'width' => '100'),
                '百分比附加值' => array('field' => 'plus_value', 'width' => '100'),
                '最小同步库存量' => array('field' => 'min_stock', 'width' => '100'),
                '自动上架' => array('field' => 'is_auto_listing', 'width' => '100', 'formatter' => 'formatter.toYN'),
                '自动下架' => array('field' => 'is_auto_delisting', 'width' => '100', 'formatter' => 'formatter.toYN'),
                '创建时间' => array('field' => 'created', 'width' => '100'),
                '修改时间' => array('field' => 'modified', 'width' => '100'),
                '停用' => array('field' => 'is_disabled', 'width' => '100', 'formatter' => 'formatter.toYN'),
            ),
        );
        return $fields[$key];
    }
}