<?php
namespace Account\Field;

use Common\Common\Field;

class LogisticsFeeManagementField extends Field{
    protected function get($key) {
        $fields = array(
            "logistics_fee_management" => array(
                '物流单号'  => array('field' => 'logistics_no', 'width' => 100, 'sortable' => false),
                '状态'  => array('field' => 'status', 'width' => 80, 'sortable' => true,'formatter'=>'formatter.logistics_status'),
                '物流公司'  => array('field' => 'logistics_id', 'width' => 100, 'sortable' => true),
                '目的地区'  => array('field' => 'area', 'width' => 100, 'sortable' => false),
                '店铺名称'  => array('field' => 'shop_name', 'width' => 100, 'sortable' => true),
                '仓库名称'  => array('field' => 'warehouse_name', 'width' => 100, 'sortable' => true),
                //'预估重量'  => array('field' => 'calc_weight', 'width' => 80, 'sortable' => true),
                '称重重量' => array('field'=>'weight','width'=>80,'sortable'=>true),
                '导入重量'  => array('field' => 'import_weight', 'width' => 80, 'sortable' => true),
                '重量差' => array('field' => 'weight_diff','width'=>80,'sortable'=> false),
                //'预估邮资'   => array('field' => 'calc_postage', 'width' => 80, 'sortable' => true),
                //'货品摘要' => array('field' => 'goods_str','width'=>80,'sortable'=> false),
                '预估邮资'  => array('field'=>'postage','width'=>80,'sortable'=>true),
                '导入邮资' => array('field' => 'import_postage', 'width' => 80, 'sortable' => true),
                '邮资差' => array('field' => 'postage_diff', 'width' => 80, 'sortable' => false),
                '制单人'  => array('field'=>'make_oper_id','width'=>80,'sortable'=>true),
                '结算人'  => array('field'=>'charge_oper_id','width'=>80,'sortable'=>true),
                '结算时间'  => array('field'=>'charge_time','width'=>135,'sortable'=>true),
                '修改时间'  => array('field'=>'modified','width'=>135,'sortable'=>true),
                '创建时间'  => array('field'=>'created','width'=>135,'sortable'=>true),
            ),
            'logistics_order_detail' => array(
                '物流单号'  => array('field' => 'logistics_no', 'width' => 100, 'sortable' => false),
                '目的地区'  => array('field' => 'area', 'width' => 100, 'sortable' => false),
                '邮资'  => array('field' => 'postage', 'width' => 100, 'sortable' => false),
                '导入邮资'  => array('field' => 'import_postage', 'width' => 100, 'sortable' => false),
                '邮资差额'  => array('field' => 'postage_difference', 'width' => 100, 'sortable' => false),
                '重量KG'  => array('field' => 'weight', 'width' => 100, 'sortable' => false),
                '导入重量'  => array('field' => 'import_weight', 'width' => 100, 'sortable' => false),
                '重量差KG'  => array('field' => 'weight_difference', 'width' => 100, 'sortable' => false),
                '备注'  => array('field' => 'remark', 'width' => 100, 'sortable' => false),
                '创建时间'  => array('field' => 'created', 'width' => 100, 'sortable' => false),
                '导入时间'  => array('field' => 'import_time', 'width' => 100, 'sortable' => false),
            ),
            'logistics_order_log' => array(
                '物流单号'  => array('field' => 'logistics_no', 'width' => 100, 'sortable' => false),
                '操作员'  => array('field' => 'operator_id', 'width' => 80, 'sortable' => false),
                '内容'  => array('field' => 'message', 'width' => 200, 'sortable' => false),
                '时间'  => array('field' => 'created', 'width' => 100, 'sortable' => false),

            )

        );
        return $fields[ $key ];
    }
}