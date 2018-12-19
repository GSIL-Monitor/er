<?php
namespace Setting\Field;

use Common\Common\Field;
class SmsTemplateField extends Field{
    protected function get($key){
        $fields = array(
            'sms_template'       => array(
                'id'   => array('field' => 'id', 'hidden' => true),
                '短信类型'  => array('field' => 'is_marketing', 'width' => '100','formatter'=>'formatter.sms_type'),
                '模板名称' => array('field' => 'title', 'width' => '150'),
                '签名' => array('field' => 'sign', 'width' => '100'),
                '模板内容' => array('field'=>'content','width' => '500'),
                '修改时间' => array('field'=>'modified','width'=>'150'),
                '创建时间' => array('field'=>'created','width' => '150'),
            ),
            "sms"              => array(
                "姓名"   => array("field" => "name", "title" => "姓名", "width" => "33%"),
                "客户网名" => array("field" => "nickname", "title" => "客户网名", "width" => "33%"),
                "手机"   => array("field" => "mobile", "title" => "手机", "width" => "33%")
            )

        );
        return $fields[$key];
    }
}