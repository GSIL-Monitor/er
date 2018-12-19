<?php
require_once(ROOT_DIR .'/api/mls/MlsClient.php');
require_once(ROOT_DIR .'/api/mls/request/OrderListInfoRequest.php');

global $mls_app_config;
$app_key = $mls_app_config['app_key'];
$app_secret = $mls_app_config['app_secret'];

$str .= "mls_key = $app_key mls_secret = $app_secret";

$mls = new MlsClient();
$mls->app_key = $app_key;
$mls->secretKey = $app_secret;
$mls->sessionKey = $session;
$req = new OrderListInfoRequest();
$req->setPage(0);
$req->setPageSize(10);

$req->setUptimeStart(date('Y-m-d H:i:s', $sta_time));
$req->setUptimeEnd(date('Y-m-d H:i:s', $end_time));

$retval = $mls->execute($req);
