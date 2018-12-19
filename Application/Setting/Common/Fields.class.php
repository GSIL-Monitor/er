<?php
namespace Setting\Common;
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/10/21
 * Time: 下午12:52
 */
class Fields {
    static public function getSettingFields($field_id = '') {
        $field_id = strtolower($field_id);
        $fields = array();
        if (!empty($field_id)) {
            switch ($field_id) {
                case 'stocksyncstrategy':
                    $fields = array(
                        'id' => array('field' => 'id', 'width' => '100', 'hidden' => 'true'),
                        '规则编号' => array('field' => 'rule_no', 'width' => '100'),
                        '规则名称' => array('field' => 'rule_name', 'width' => '100'),
                        '优先级' => array('field' => 'priority', 'width' => '60'),
                        '店铺列表' => array('field' => 'shop_list', 'width' => '100', 'formatter' => 'shopFormatter'),
                        '仓库列表' => array('field' => 'warehouse_list', 'width' => '100', 'formatter' => 'warehouseFormatter'),
                        '分类' => array('field' => 'class_id', 'width' => '120', 'formatter' => 'classFormatter'),
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
                    );
                    break;
                case 'logistics':
                    $fields = array(
                        'id' => array('field' => 'logistics_id', 'hidden' => true, 'sortable' => true),
                        '物流编号' => array('field' => 'logistics_no', 'width' => 100, 'sortable' => true),
                        '物流名称' => array('field' => 'logistics_name', 'width' => 150, 'sortable' => true),
                        '物流类型' => array('field' => 'logistics_type', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.logistics_type'),
                        '联系人' => array('field' => 'contact', 'width' => 100, 'sortable' => true),
                        '单号类型' => array('field' => 'bill_type', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.bill_type'),
                        '联系电话' => array('field' => 'telno', 'width' => 100, 'sortable' => true),
                        '地址' => array('field' => 'address', 'width' => 100, 'sortable' => true),
                        '手动获取单号' => array('field' => 'is_manual', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.toYN'),
                        '停用' => array('field' => 'is_disabled', 'width' => 60, 'sortable' => true, 'formatter' => 'formatter.toYN'),
                        '备注' => array('field' => 'remark', 'width' => 60, 'sortable' => true),
                    );
                    break;
                case 'warehouse':
                    $fields = array(
                        '仓库编号' => array('field' => 'warehouse_no', 'width' => '100', 'align' => 'center'),
                        '仓库名称' => array('field' => 'name', 'width' => '100', 'align' => 'center'),
                        '仓库类别' => array('field' => 'type', 'width' => '100', 'align' => 'center', 'formatter' => 'warehouseFormatter',),
                        '省份' => array('field' => 'province', 'width' => '100', 'align' => 'center'),
                        '城市' => array('field' => 'city', 'width' => '100', 'align' => 'center'),
                        '地区' => array('field' => 'district', 'width' => '100', 'align' => 'center'),
                        '地址' => array('field' => 'address', 'width' => '100', 'align' => 'center'),
                        '联系人' => array('field' => 'contact', 'width' => '100', 'align' => 'center'),
                        '邮编' => array('field' => 'zip', 'width' => '100', 'align' => 'center'),
                        '手机' => array('field' => 'mobile', 'width' => '100', 'align' => 'center'),
                        '固话' => array('field' => 'telno', 'width' => '100', 'align' => 'center'),
                        '残次品库' => array('field' => 'is_defect', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.toYN'),
                        '停用' => array('field' => 'is_disabled', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.toYN'),
                        'id' => array('field' => 'id', 'hidden' => true),
                    );
                    break;
                case "shop":
                    $fields = array(
                        "id" => array("field" => "id", "hidden" => true),
                        "店铺编号" => array("field" => "shop_no", "title" => "店铺编号", "width" => "80", "sortable" => true),
                        "店铺名称" => array("field" => "shop_name", "title" => "店铺名称", "width" => "80", "sortable" => true),
                        "平台" => array("field" => "platform_id", "title" => "平台", "width" => "80", "formatter" => "formatter.platform_id", "sortable" => true),
                        "子平台" => array("field" => "sub_platform_id", "title" => "子平台", "width" => "80", "sortable" => true, "formatter" => "formatterShopSubPlatform"),
                        "平台账号" => array("field" => "account_nick", "title" => "平台账号", "width" => "80", "sortable" => true),
                        "授权状态" => array("field" => "auth_state", "title" => "授权状态", "width" => "80", "sortable" => true, "formatter" => "formatter.auth_state"),
                        "授权时间" => array("field" => "auth_time", "title" => "授权时间", "width" => "80", "sortable" => true),
                        "授权到期时间" => array("field" => "expire_time", "title" => "授权到期时间", "width" => "80", "sortable" => true),
                        "停用" => array("field" => "is_disabled", "title" => "停用", "width" => "80", "formatter" => "formatter.boolen", "sortable" => true)
                    );
                    break;
                default:
                    $fields = array();
                    break;
            }
        }
        return $fields;
    }


}