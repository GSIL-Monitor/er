<?php
require_once "../vipapis/delivery/DvdDeliveryServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\delivery\DvdDeliveryServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url);
    $ctx->setAccessToken($Token);
    $retval=$service->getOrderDetail($vendor_id,$tid,null,null);//获取订单商品
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>