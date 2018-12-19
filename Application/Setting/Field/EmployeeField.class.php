<?php
namespace Setting\Field;
use Common\Common\Field;
class EmployeeField extends Field{
    protected function get($key){
        $fields = array(
            'employee'=>array(
                'id' => array('field' => 'id', 'hidden'=>'true',  'sortable' => true, 'align' => 'center'),
                '账号' => array('field' => 'account',  'sortable' => true, 'align' => 'center','width'=>'200'),
                '姓名' => array('field' => 'fullname',  'sortable' => true, 'align' => 'center','width'=>'150'),
                '性别' => array('field' => 'gender', 'sortable' => true, 'align' => 'center','width'=>'50','formatter' => 'formatter.sex'),
                '职位' => array('field' => 'position',  'sortable' => true, 'align' => 'center','width'=>'150'),
                '手机' => array('field' => 'mobile_no',  'sortable' => true, 'align' => 'center','width'=>'150'),
                'QQ' => array('field' => 'qq',  'sortable' => true, 'align' => 'center','width'=>'150'),
                '旺旺' => array('field' => 'wangwang',  'sortable' => true, 'align' => 'center','width'=>'150'),
                '邮箱' => array('field' => 'email',  'sortable' => true, 'align' => 'center','width'=>'150'),
                //'角色' => array('field' => 'roles_mask',  'sortable' => true,'formatter'=>'formatter.role'),
                '上次登陆时间' => array('field' => 'last_login_time',  'sortable' => true, 'align' => 'center','width'=>'200'),
            ),
        );
        return $fields[$key];
    }
}