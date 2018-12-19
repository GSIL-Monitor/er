<?php

require_once('config.php');

$params = array(
    'head' => array(
        'tran_code' => 'T0008',
        'cust_id' => $cust_id,
        'tran_sid' => '',
    ),
    'body' => array(
        'order' => array(
            'start_created' => $start_time,
            'end_created' => $end_time,
            'status' => '',
            'page_no' => $page_num,
            'page_size' => $page_size,
            /*'start_update' => $start_time,
            'end_update' => $end_time,*/
        ),
    )
);

print_r($params);
$client = new ccbClient();
$client->setKey($secret);

$retval = $client->execute($params);


print_r($retval);

logx($retval,'ccb_trade_list');
