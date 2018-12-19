<?php
require_once "../vipapis/delivery/JitDeliveryServiceClient.php";
require_once('config.php');
$pageSize = 1;
$vendor_id = '4493';
try {
	$service=\vipapis\delivery\JitDeliveryServiceClient::getService();
	$ctx=\Osp\Context\InvocationContextFactory::getInstance();
	$page = 2;
	$pageSize = 10;
	//$st_create_time = date('Y-m-d H:i:s', 0);
	//$et_create_time = date('Y-m-d H:i:s',  0);
	$ctx->setAppKey('b727500f');
	$ctx->setAppSecret('96369CEF9EC112425EBDA3A08290501B');
	$ctx->setAppURL("http://gw.vipapis.com/");
	$ctx->setAccessToken('36A45B2A3B1E7B4280C775F2B4E2AE572FA41CD7');
	$retval = $service->getPickList($vendor_id, null, null, null, null,  null, '2017-07-13 12:00:00', '2017-07-13 15:00:00', null, null, null,  null, null, 2, $pageSize, null);
}
catch(\Osp\Exception\OspException $e)
{
    var_dump($e);
}
$fp = fopen('www.txt','a+');
fwrite($fp,print_r($retval,true));
fclose($fp);
var_dump($retval);
?>