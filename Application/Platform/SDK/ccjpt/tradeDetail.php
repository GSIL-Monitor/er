<?php

require_once("./ccjptClient.php");

$ccjpt = new ccjptClient;
$ccjpt->app_key = "3890531045";
$ccjpt->method = "orderList";
$ccjpt->appsecret = "66200cae44ff7e65a81b091601c4d21e";

$params = array(
	'order_sn'=>"161123104617701725fMS"
	);
$result = $ccjpt->excute($params);
echo "tradeDetail:\n";
print_r($result);


