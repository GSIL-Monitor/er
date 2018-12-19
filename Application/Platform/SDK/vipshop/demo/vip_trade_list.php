<?php
require_once "../vipapis/delivery/DvdDeliveryServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\delivery\DvdDeliveryServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url); //生产环境
    $ctx->setAccessToken($Token);
    $retval = $service->getOrderList($start_time, $end_time, null, null, null, $vendor_id, $page, $page_size);
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>