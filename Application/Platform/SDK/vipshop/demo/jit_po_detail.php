<?php
require_once "../vipapis/delivery/JitDeliveryServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\delivery\JitDeliveryServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $page = 1;
	$pageSize = 30;
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url);
    // $ctx->setAccessToken($Token);
    $retval = $service->getPoSkuList($vendor_id, $po_no, null, null, null, null, null, null, null, null, null, null, null, null, $page, $pageSize);
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>