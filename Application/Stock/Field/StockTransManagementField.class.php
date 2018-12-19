<?php
/**
 * Created by PhpStorm.
 * User: lbx
 */
namespace Stock\Field;

use Common\Common\Field;

class StockTransManagementField extends Field {
    protected function get($key) {
       
                  $fields=array(
					"stocktransmanagement"=>array(
                        'id'=>array('field'=>'id','hidden'=>true),
                        '调拨单号'=>array('field'=>'transfer_no','width'=>'100'),
                        '调拨单状态'=>array('field'=>'status','width'=>'100', 'align' => 'center','formatter' => 'formatter.stocktrans_status',),
                        '调拨类型'=>array('field'=>'type','width'=>'100', 'align' => 'center','formatter' => 'formatter.stocktrans_type',),
                        '调拨方案'=>array('field'=>'mode','width'=>'100', 'align' => 'center','formatter' => 'formatter.stocktrans_mode',),
                        '仓库id' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center','hidden'=>true),
						'原仓库'=>array('field'=>'from_warehouse_id','width'=>'100', 'align' => 'center'),
                        '目标仓库'=>array('field'=>'to_warehouse_id','width'=>'100', 'align' => 'center'),
						'外部WMS单号' => array('field' => 'outer_no', 'width' => '150','align' => 'center'),
						'WMS错误信息' => array('field' => 'wms_result', 'width' => '150','align' => 'center'),         
						'经办人' => array('field' => 'creator_id', 'width' => '100', 'align' => 'center',),
						'联系人'=>array('field'=>'contact','width'=>'100'),
                        '联系电话'=>array('field'=>'telno','width'=>'100'),
                        '物流公司'=>array('field'=>'logistics_id','width'=>'100'),
                        '备注'=>array('field'=>'remark','width'=>'100'),
                        '调拨开单量'=>array('field'=>'goods_count','width'=>'100'),
                        '货品种类'=>array('field'=>'goods_type_count','width'=>'100'),
                        '已入库总量'=>array('field'=>'goods_in_count','width'=>'100'),
                        '已出库总量'=>array('field'=>'goods_out_count','width'=>'100'),
                        '最后修改时间'=>array('field'=>'modified','width'=>'100'),
                        '创建时间'=>array('field'=>'created','width'=>'100'),
					),
				   );
				    return $fields[$key];
			}
}