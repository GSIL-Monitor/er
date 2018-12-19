<?php

require_once('config.php');

$params = array(
	'head' => array(
		'tran_code' => 'T0005',
		'cust_id' => $cust_id,
		'tran_sid' => '',
	),
	'body' => array(
		'delivery' => array(
			'order_id'=> $tid,
			'company_code' => $logistics_code,
			'out_sid' => $out_sid,
			'type' => $type,
		),
	),
);

$client = new ccbClient();
$client->setKey($secret);

$retval = $client->execute($params);


print_r($retval);

logx($retval,'ccb_trade_list');
