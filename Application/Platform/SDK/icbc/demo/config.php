<?php
/**@平台：融e购
 * @plat: 25
 * @AppKey
 * @AppSecrect
 * @session
 * 
 */

$appkey =  '';
$appsecret =  '';

$session = '';

$icbc=new icbcApiClient();
$icbc->setApp_key($appkey);
$icbc->setApp_secret($appsecret);
$icbc->setAuth_code($session);

//trade detail
$trade_detail=array
(
	'order_ids'=>'020150825IM6758558'
);

//trade list
$trade_list = array
(
	'modify_time_from' 	=> '2015-08-26 00:00:00',
	'modify_time_to' 	=> '2015-08-27 00:00:00'

);

//goods detail
$goods_detail=array
(
	'product_ids'	=>	'0000103077'
);

//goods list
$goods_list=array
(
	'modify_time_from'	=>	'2015-04-20 00:00:00',
	'modify_time_to'	=>	'2015-08-23 00:00:00',
	'product_status'	=>	''
);