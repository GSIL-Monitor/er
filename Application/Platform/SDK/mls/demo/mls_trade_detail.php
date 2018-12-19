<?php
require_once(ROOT_DIR .'/api/mls/MlsClient.php');
require_once(ROOT_DIR .'/api/mls/request/OrderDetailsRequest.php');

global $mls_app_config;
$app_key = $mls_app_config['app_key'];
$app_secret = $mls_app_config['app_secret'];

$str .= "mls_key = $app_key mls_secret = $app_secret";

$mls = new MlsClient();
$mls->app_key = $app_key;
$mls->secretKey = $app_secret;
$mls->sessionKey = $session;
$req = new OrderDetailsRequest();

$req -> setOrderId($tid);

$retval = $mls->execute($req);
