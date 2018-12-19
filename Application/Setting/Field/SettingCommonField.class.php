<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 11:24
 */
namespace Setting\Field;

use Common\Common\Field;

class SettingCommonField extends Field {
    protected function get($key) {
        $fields = array(
            'operation_log'       => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '操作员'  => array('field' => 'operator_id', 'width' => '100',),
                '操作描述' => array('field' => 'message', 'width' => '900'),
                '操作时间' => array('field' => 'created', 'width' => '150'),
            ),
            /*"cfg_stock_sync_rule" => array(
                'id'      => array('field' => 'id', 'width' => '100', 'hidden' => 'true'),
                '规则编号'    => array('field' => 'rule_no', 'width' => '100'),
                '规则名称'    => array('field' => 'rule_name', 'width' => '100'),
                '优先级'     => array('field' => 'priority', 'width' => '100'),
                '库存列表'    => array('field' => 'warehouse_list', 'width' => '100', 'formatter' => 'tabsWarehouseFormatter'),
                '库存方法'    => array('field' => 'stock_flag_string', 'width' => '300'),
                '百分比'     => array('field' => 'percent', 'width' => '100'),
                '百分比附加值'  => array('field' => 'plus_value', 'width' => '100'),
                '最小同步库存量' => array('field' => 'min_stock', 'width' => '100'),
                '自动上架'    => array('field' => 'is_auto_listing', 'width' => '100', 'formatter' => 'formatter.boolen'),
                '自动下架'    => array('field' => 'is_auto_delisting', 'width' => '100', 'formatter' => 'formatter.boolen'),
                '停用'      => array('field' => 'is_disabled', 'width' => '100', 'formatter' => 'formatter.boolen'),
                '创建时间'    => array('field' => 'created', 'width' => '100'),
                '修改时间'    => array('field' => 'modified', 'width' => '100'),
            )*/
            "cfg_stock_sync_rule" => array(
                "id"      => array("field" => "rule_id", "hidden" => true),
                "停止库存同步"  => array("field" => "is_disable_syn", "width" => "14.2%", "formatter" => "formatter.boolen"),
                "同步规则信息"  => array("field" => "stock_syn_info", "width" => "14.2%"),
                "同步规则编号"  => array("field" => "rule_no", "width" => "14.2%"),
                "仓库列表"    => array("field" => "warehouse_list", "width" => "14.2%", "formatter" => "tabsWarehouseFormatter"),
                "库存计算方式"  => array("field" => "stock_flag_string", "width" => "14.2%"),
                "同步百分比"   => array("field" => "stock_syn_percent", "width" => "14.2%"),
                "增加值"     => array("field" => "stock_syn_plus", "width" => "14.2%"),
                "最小库存同步量" => array("field" => "stock_syn_min", "width" => "14.2%")
            ),
        	'login_log'=>array(
        			'id' 	=> array('field' => 'id', 'hidden'=>'true',  'sortable' => true),
        			'时间' 	=> array('field' => 'created','width'=>'200'),
        			'登录IP'	=> array('field' => 'message','width'=>'300'),
        	),
            'position_spec'=>array(
                '仓库'=>array('field'=>'warehouse_name','width'=>'100'),
                'id'=>array('field'=>'id','width'=>'100','hidden'=>true),
                '商家编码'=>array('field'=>'spec_no','width'=>'70'),
                '规格名称'=>array('field'=>'spec_name','width'=>'70'),
                '规格码'=>array('field'=>'spec_code','width'=>'70'),
                '货位'=>array('field'=>'position_no','width'=>'70'),
                '货品名称'=>array('field'=>'goods_name','width'=>'70'),
                '货品简称'=>array('field'=>'short_name','width'=>'70'),
                '货品编号'=>array('field'=>'goods_no','width'=>'70'),
            ),
            'upon_logistics'=>array(
                'id'                => array('field'=>'id','width'=>'100','hidden'=>true),
                '店铺'              => array('field'=>'shop_name','width'=>'100'),
                '对应平台物流'       => array('field'=>'api_logistics','width'=>'100'),
            )
        );
        return $fields[ $key ];
    }
}