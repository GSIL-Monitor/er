<?php
require_once (ROOT_DIR . '/api/icbc/icbcApiClient.php');

$str .= "icbc_key = $app_key icbc_secret = $app_secret icbc_session = $session";
$icbcApi = new icbcApiClient ();
$icbcApi->setApp_key ( $app_key );
$icbcApi->setApp_secret ( $app_secret );
$icbcApi->setAuth_code ( $session );
$icbcApi->setMethod ( "icbcb2c.order.detail" );
$params ['order_ids'] = $tid;
$retval = $icbcApi->sendByPost ( $params );
?>