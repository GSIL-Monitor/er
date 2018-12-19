<?php

require_once("./ccjptClient.php");

$ccjpt = new ccjptClient;
$ccjpt->app_key = "3890531045";
$ccjpt->method = "orderList";
$ccjpt->appsecret = "66200cae44ff7e65a81b091601c4d21e";

$params = array(
	'start_time'=>"2016-11-20 00:00:00",
	'end_time'=>"2016-11-28 00:00:00",
	'page'=>1,
	'page_size'=>40
	);
$result = $ccjpt->excute($params);
echo "tradeList:\n";
print_r($result);



