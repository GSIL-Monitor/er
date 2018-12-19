<?php
require_once '../icbcApiClient.php';
require_once('config.php');
$icbc->setMethod("icbcb2c.product.list");
$retval=$icbc->sendByPost($goods_list);
print_r($retval);

?>