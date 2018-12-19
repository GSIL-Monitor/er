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
//    $ctx->setAccessToken($Token);
     $retval = $service->getPickDetail($po_no, $vendor_id, $pick_no,null,null);
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>
