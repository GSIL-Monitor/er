<?php
    require_once('../icbcApiClient.php');
    require_once('config.php');
    $icbc->setMethod("icbcb2c.order.list");
    $retval=$icbc->sendByPost($trade_list);
    print_r($retval);

?>
