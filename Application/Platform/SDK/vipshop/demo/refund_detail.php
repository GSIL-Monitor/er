<?php
require_once "../vipapis/vreturn/VendorReturnServiceClient.php";
require_once('config.php');
echo time();
echo "</br>";
try 
{
    $service=\vipapis\vreturn\VendorReturnServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $page = 1;
    $page_size = 30;
	$ctx->setAppKey('b727500f');
	$ctx->setAppSecret('96369CEF9EC112425EBDA3A08290501B');
	$ctx->setAppURL("http://gw.vipapis.com/");
	$ctx->setAccessToken('8DC6A69003321E885C1C561CB18C3A75B5656111');

	$retval = $service->getReturnDetail($vendor_id, 1, null, '2017-11-30 02:00:00', '2017-11-30 05:00:00',  $page, $page_size);
    //print_r($retval);
	//echo time();
	echo (1512014020 - 1512013887);
}
catch(\Osp\Exception\OspException $e)
{
    print_r($e);
}
?>
