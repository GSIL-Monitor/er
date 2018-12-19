<?php
namespace Stock\Field;

use Common\Common\Field;

class StockInManagementField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'refundlinksporder' => array(
                '入库单号' => array('field' => 'stockin_no', 'width' => '100', 'align' => 'center'),
                '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockin_status',),
                '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockin_type',),
                '来源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center',),
                '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center',),
                '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => '100', 'align' => 'center'),
                '货款总额' => array('field' => 'goods_amount', 'width' => '100', 'align' => 'center'),
                '货品成本(入库总价)' => array('field' => 'total_price', 'width' => '100', 'align' => 'center'),
                '优惠' => array('field' => 'discount', 'width' => '100', 'align' => 'center'),
                '邮资' => array('field' => 'post_fee', 'width' => '100', 'align' => 'center'),
                '其他金额' => array('field' => 'other_fee', 'width' => '100', 'align' => 'center'),
                //'调整后总金额' => array('field' => 'right_fee', 'width' => '100', 'align' => 'center'),
                '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                //'调整后总数量' => array('field' => 'right_num', 'width' => '100', 'align' => 'center'),
                '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
//				'修改时间' => array('field' => 'modified', 'width' => '100', 'align' => 'center'),
                '入库时间' => array('field' => 'check_time', 'width' => '100', 'align' => 'center'),
                'id' => array('field' => 'id', 'hidden' => true,),
            ),
        );
        return $fields[$key];
    }
}