<?php
	include "mlsClient.php";
	define('CO', 'ZSXJ');
	define('MLS_API_URL', 'http://shopapi.meilishuo.com');
	$session ="yxebyxuzpdwniayxUDebebqmtcwnUDwnqmqmuzqmtc" ;
	$mls = new MlsApiClient();
	$logistics_code ='' ;
	$tid = '';
	$logistics_no ='' ;
	$url = '/order/deliver';
	$query = array (
		'vcode' =>$session,
		'co' => CO);
	$post = array (
		'express_company' => $logistics_code,
		'order_id' =>$tid,
		'express_id' => $logistics_no,
		'co' =>CO);
	$retval = $mls->fetch($url, $query,$post);
	var_dump($retval);
