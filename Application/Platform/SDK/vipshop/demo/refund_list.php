<?php
require_once "../vipapis/vreturn/VendorReturnServiceClient.php";
require_once('config.php');
try 
{
    $service=\vipapis\vreturn\VendorReturnServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $page = 1;
    $page_size = 30;
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url);
    $ctx->setAccessToken($Token);
    $retval = $service->getReturnInfo($vendor_id, 4, null, $refund_st_time, $refund_ed_time, $page, $page_size);
    
    print_r($retval);
} 
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>