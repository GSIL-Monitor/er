<?php
//查询物流公司
require_once('../vipapis/delivery/DvdDeliveryServiceClient.php');
require_once('config.php');
try 
{
    $service=\vipapis\delivery\DvdDeliveryServiceClient::getService();
    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
    $ctx->setAppKey($appkey);
    $ctx->setAppSecret($secret);
    $ctx->setAppURL($url);
    $ctx->setAccessToken("871F3323F00160DE752A6EE8CA68013252461A6D");
    $retval = $service->getCarrierList($vendor_id,$logistics_page,$logistics_page_size);
}
catch(\Osp\Exception\OspException $e)
{
    var_dump($e);
}

$enter = "\r\n";
$str = ''; 
$str .= date('Y-m-d H:i:s', time()).$enter;
$str .= '---------------------------------------'.$enter;
$str .= "物流公司编码\t物流公司".$enter.$enter;

$total_results = intval($retval->total);
    
$vipshop_companies = $retval->carriers;

if ($total_results <= count($vipshop_companies))
{
    for($j =0; $j < count($vipshop_companies); $j++)
    {
        $t = $vipshop_companies[$j];
        if($t->carrier_isvalid == 1){//承运商状态 1启用 0关闭
            $str .= $t->carrier_code."\t\t".$t->carrier_shortname.$enter;
        }
    }
}
else
{
    $total_pages = ceil(floatval($total_results)/$page_size);

    for($i=$total_pages; $i>0; $i--)
    {
        $page = $i;
        try
        {
            $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
            $ctx = \Osp\Context\InvocationContextFactory::getInstance();
            $retval = $service->getCarrierList($vendor_id, $page, $page_size);
        }
        catch(\Osp\Exception\OspException $e)
        {
            var_dump($e);
        }
        
        $vipshop_companies = &$retval->carriers;
        for($j =0; $j < count($vipshop_companies); $j++)
        {
            $t = & $vipshop_companies[$j];
            if($t->carrier_isvalid == 1){
                $str .= $t->carrier_code."\t\t".$t->carrier_shortname.$enter;
            }
        }

    }
}
print_r($str);
file_put_contents("vip-logis.txt",$str);
?>