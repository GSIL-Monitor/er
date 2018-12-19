<?php
return array(
	//'配置项'=>'配置值'
	'DEFAULT_F_LAYER'=>'Field', // 默认的字段层名字
	'SHOW_ERROR_MSG' => true,
	//'SHOW_PAGE_TRACE'=>true
		'alipay_account_check_status'=>array(
				array('value'=>'未对账','key'=>'0',),
				array('value'=>'对账失败','key'=>'1',),
				array('value'=>'部分对账','key'=>'2',),
				array('value'=>'对账成功','key'=>'3',),
				array('value'=>'设置对账成功','key'=>'4',),
				array('value'=>'未关联原始单','key'=>'5',),

		),
);