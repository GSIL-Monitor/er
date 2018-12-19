<?php
	include "mlsClient.php";
	define('CO', 'ZSXJ');
	define('MLS_API_URL', 'http://shopapi.meilishuo.com');
	$session ="yxebyxuzpdwniayxUDebebqmtcwnUDwnqmqmuzqmtc" ;
	$mls = new MlsApiClient();
	$goods_id ='' ;
	$url = '/goods/goods_stocks';
	$params = array (
	'vcode' =>$session,
	'co' =>CO,
	'twitter_id' =>$goods_id,
	'co' => CO
	);
	$retval = $mls->fetch($url, $params);
	var_dump($retval);
