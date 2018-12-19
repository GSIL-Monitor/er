<?php
namespace Setting\Field;

use Common\Common\Field;

class SmsSendRuleField extends Field{
    protected function get($key) {
        $fields=array(
            'sms_send_rule'=>array(
                'id'=>array('field'=>'id','hidden'=>true),
                '店铺'=>array('field'=>'shop_name','sortable'=>true,'width'=>'100'),
                '触发事件'=>array('field'=>'event_type','sortable'=>true,'width'=>'100','formatter' => 'formatter.sms_event_type'),
                '模板'=>array('field'=>'template_id','sortable'=>true,'width'=>'120'),
                '延迟时间（分钟）'=>array('field'=>'delay_time','sortable'=>true,'width'=>'150'),
                '截止时间'=>array('field'=>'end_time','sortable'=>true,'width'=>'120'),
                '停用'=>array('field'=>'is_disabled','sortable'=>true,'width'=>'120','formatter' => 'formatter.boolen'),
                '最后修改时间'=>array('field'=>'modified','sortable'=>true,'width'=>'150'),
                '创建时间'=>array('field'=>'created','sortable'=>true,'width'=>'150'),
            ),
        );
        return $fields[$key];
    }
}