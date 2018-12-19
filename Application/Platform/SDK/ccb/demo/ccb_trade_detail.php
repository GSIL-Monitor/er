<?php

require_once('config.php');

$params = array(
	'head' => array(
		'tran_code' => '汉字',
		'cust_id' => $cust_id,
		'tran_sid' => $tran_sid,
	),
	'body' => array(
		'order' => array(
			'order_id' => $tid,
		),
	)
);
print_r($params);

$client = new ccbClient();


$retval = $client->execute($params);

print_r($retval);
