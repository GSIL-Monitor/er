<?php
require_once "../vipapis/delivery/JitDeliveryServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\delivery\JitDeliveryServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $page = 2;
	$pageSize = 10;
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url);
    // $ctx->setAccessToken($Token);
    // print_r($ctx);
    $retval = $service->getPoList(null, null, null, $po_no, null, $vendor_id, null, null, null, null, 1, $pageSize);
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>