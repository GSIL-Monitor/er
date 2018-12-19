<?php
require_once "../vipapis/vreturn/VendorReturnServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\vreturn\VendorReturnServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $page = 1;
    $page_size = 50;
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL("http://gw.vipapis.com/");
    $ctx->setAccessToken($Token);
    $retval = $service->getReturnDetail($vendor_id, $warehouse_goods_no, $return_goods_on, null, null, $page, $page_size);
    print_r($retval);
    file_put_contents("vip-refund_goods.txt",print_r($retval,true));
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>