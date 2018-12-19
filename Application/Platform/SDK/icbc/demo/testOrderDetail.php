<?php
require_once '../icbcApiClient.php';
require_once('config.php');
$icbc->setMethod("icbcb2c.order.detail");
$retval=$icbc->sendByPost($trade_detail);
 
print_r($retval);
?>