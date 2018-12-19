<?php

require_once(ROOT_DIR .'ccb/ccbClient.php');
require_once(ROOT_DIR .'AES.php');

$client = new ccbClient();
$client->setKey($app_secret);

$params = array(
	'head' => array(
		'tran_code' => 'T0008',
		'cust_id' => $app_key,
		'tran_sid' => '',
	),
	'body' => array(
		'order' => array(
			'start_created' => '',
			'end_created' => '',
			'status' => '',
			'page_no' => 1,
			'page_size' => 10,
			'start_update' => $sta_time,
			'end_update' => $end_time,
		),
	)
);

$retval = $client->execute($params);

print_r($retval);


