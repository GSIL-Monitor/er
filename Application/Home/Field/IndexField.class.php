<?php
namespace Home\Field;
class IndexField {
    static public function getFields($field_id = '') {
        $field_id = strtolower($field_id);
        $fields = array();
        if (!empty($field_id)) {
            switch ($field_id) {
                case 'electronic_sheet_detial':
                    $fields = array(
                        '店铺名称'=>array('field'=>'shop_name','width'=>'140','align'=>'center'),
                        '物流公司'=>array('field'=>'cp_code','width'=>'140','align'=>'center','formatter'=>'formatter.logistics_name_code'),
                        '网点名称'=>array('field'=>'branch_name','width'=>'140','align'=>'center'),
                        '已用单数'=>array('field'=>'allocated_quantity','width'=>'120','align'=>'center'),
                        '可用单数'=>array('field'=>'quantity','width'=>'120','align'=>'center')
                    );
                    break;
                case 'dialog_not_authorize_shop':
                    $fields = array(
                        '来自'=>array('field'=>'sender','width'=>'80','align'=>'center'/*,'formatter'=>'formatter.styler'*/),
                        '消息'=>array('field'=>'message','width'=>'285','align'=>'left'),
                        '处理人'=>array('field'=>'handle_oper_id','width'=>'100','align'=>'center'),
                        '类型'=>array('field'=>'type','width'=>'100','align'=>'center'),
                        '时间'=>array('field'=>'created','width'=>'140','align'=>'center')
                );
                    break;
                case 'system_alarm':
                	$fields=array(
                		'消息'=>array('field'=>'msg','width'=>'500'),
//                 		'处理'=>array('field'=>'way','width'=>'100')
                	);
                	break;
				 case 'order_balance':
                    $fields = array(
						'id'=>array('field'=>'id','hidden'=>true),
                        '单量变化'=>array('field'=>'put_num','width'=>'140','align'=>'center'),
                        '记录信息'=>array('field'=>'message','width'=>'230','align'=>'center'),
                        '剩余单量'=>array('field'=>'data','width'=>'140','align'=>'center'),
                        '操作日期'=>array('field'=>'created','width'=>'160','align'=>'center'),
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