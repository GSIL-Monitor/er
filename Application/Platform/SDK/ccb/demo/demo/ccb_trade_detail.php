<?php

require_once(ROOT_DIR .'ccb/ccbClient.php');
require_once(ROOT_DIR .'AES.php');

$client = new ccbClient();
$client->setKey($app_secret);


$params = array(
	'head' => array(
		'tran_code' => 'T0007',
		'cust_id' => $app_key,
		'tran_sid' => '',
	),
	'body' => array(
		'order' => array(
			'order_id' => $tid,
		),
	)
);

$retval = $client->execute($params);

print_r($retval);