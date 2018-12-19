<?php
require_once '../icbcApiClient.php';
require_once('config.php');
$icbc->setMethod("icbcb2c.product.detail");
$retval = $icbc->sendByPost($goods_detail);
print_r($retval);

?>