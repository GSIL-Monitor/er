<?php

// require_once(TOP_SDK_DIR . 'vipshop/vipshopOpenApiHandler.php');


/*function vipshop_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$page = 1;
	$page_size = 50;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$vendor_id = $shop->account_nick;
	$session = $shop->session;
	getAppSecret($shop, $appkey, $appsecret);

	try
	{
		require_once TOP_SDK_DIR."vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
   		$service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
   		$ctx = \Osp\Context\InvocationContextFactory::getInstance();
    		$ctx->setAppKey($appkey);
    		$ctx->setAppSecret($appsecret);
    		$ctx->setAppURL("http://gw.vipapis.com/");
    		$ctx->setAccessToken($session);
    		$retval=$service->getCarrierList($vendor_id, $page, $page_size);
	}
	catch(\Osp\Exception\OspException $e)
	{

		if(API_RESULT_OK != vipshopErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->error_msg;
			logx("vipshop_get_logistics_companies error_msg: {$error_msg}!!", $sid);
			return false;
		}
		$error_msg = $e->returnMessage;
   		logx("vipshop_getCarrierList->excute fail: $error_msg".print_r($e,true) ,$sid);
   		return false;
	}

	$total_results = intval($retval->total);

	$vipshop_companies = $retval->carriers;

	if ($total_results <= count($vipshop_companies))
	{
		for($j =0; $j < count($vipshop_companies); $j++)
		{
			$t = $vipshop_companies[$j];
			if($t->carrier_isvalid == 1){//承运商状态 1启用 0关闭
				$companies[] = array
				(
					'shop_id' => $shop->shop_id,
					'logistics_code' => $t->carrier_code,
					'name' => $t->carrier_shortname,
					'created' => date('Y-m-d H:i:s',time())
				);
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

				if(API_RESULT_OK != vipshopErrorTest($retval, $db, $shopid))
				{
					$error_msg = $retval->error_msg;
					logx("vipshop_get_logistics_companies error_msg: {$error_msg}!!", $sid);
					return false;
				}
				$error_msg = $e->returnMessage;
		   		logx("vipshop_getCarrierList->excute fail: $error_msg".print_r($e,true) ,$sid);
		   		return false;
			}

			$vipshop_companies = &$retval->carriers;
			for($j =0; $j < count($vipshop_companies); $j++)
			{
				$t = & $vipshop_companies[$j];
				if($t->carrier_isvalid == 1){
					$companies[] = array
					(
						'shop_id' => $shop->shop_id,
						'logistics_code' => $t->carrier_code,
						'name' => $t->carrier_shortname,
						'created' => date('Y-m-d H:i:s',time())
					);
				}
			}
		}
	}

	return true;
}*/
$GLOBALS['vipshop_logistics_name_map'] = array(
    '1200000587'=>'仁力家具(直发)',
    '1200000586'=>'和众互联(直发)',
    '1200000585'=>'小憨豆家居(直发)',
    '1200000584'=>'恩嘉依(直发)',
    '1200000583'=>'湘辉实业(直发)',
    '1200000582'=>'百世物流(直发)',
    '1200000579'=>'安得物流(直发)',
    '1200000578'=>'平安物流(直发)',
    '1200000577'=>'贝业新兄弟(直发)',
    '1200000576'=>'德威物流(直发)',
    '1200000575'=>'远成物流(直发)',
    '1200000574'=>'聚丰百达(直发)',
    '1200000573'=>'UCS(直发)',
    '1200000570'=>'中远e环球(直发)',
    '1200000534'=>'速必达物流(直发)',
    '1200000533'=>'清群物流(直发)',
    '1200000530'=>'联邦快递(直发)',
    '1000000459'=>'中通速递(直发)',
    '1000000462'=>'德邦物流(直发)',
    '1000000465'=>'宅急送(直发)',
    '1000000455'=>'圆通速递(直发)',
    '1000000468'=>'恒路物流(直发)',
    '1000000464'=>'天地华宇(直发)',
    '1000000461'=>'天天快递(直发)',
    '1000000466'=>'东瀚物流(直发)',
    '1000000458'=>'顺丰速运(直发)',
    '1000000460'=>'百世汇通(直发)',
    '1000000456'=>'韵达快递(直发)',
    '1000000471'=>'全峰快递(直发)',
    '1000000454'=>'EMS(直发)',
    '1000000457'=>'申通快递(直发)',
    '1000000472'=>'邮政小包(直发)',
    '1000000482'=>'佳吉快运(直发)',
    '1000000483'=>'万家物流(直发)',
    '1000000478'=>'中铁快运(直发)',
    '1000000511'=>'中铁物流(直发)',
    '1000000510'=>'路路通物流(直发)',
    '1000000504'=>'大田物流(直发)',
    '1000000512'=>'蓝欣物流(直发)',
    '1000000475'=>'新邦物流(直发)',
    '1000000474'=>'安能物流(直发)',
    '120000981'=>'和购专线物流(直发)',
    '120000977'=>'环宇物流(直发)',
    '120000975'=>'全友专线物流(直发)',
    '120000974'=>'爱尚速递(直发)',
    '120000886'=>'芝华仕专线物流(直发)',
    '120000885'=>'TOTO专线物流(直发)',
    '120000884'=>'优梵艺术专线(直发)',
    '120000855'=>'百易信息专线(直发)',
    '120000839'=>'箭牌专线(直发)',
    '120000835'=>'海尔日日顺(直发)',
    '120000829'=>'乐视电视专线(直发)',
    '120000813'=>'德邦快递(直发)',
    '1200000660'=>'羊氏家具专线(直发)',
    '1200000659'=>'凡卡家具专线(直发)',
    '1200000646'=>'华日家具(直发)',
    '1200000640'=>'品骏快递(直发)',
    '1200000639'=>'KOREAPOST(直发)',
    '1200000637'=>'艾欧史密斯(直发)',
    '1200000636'=>'御风电子(直发)',
    '1200000635'=>'红美电子(直发)',
    '1200000634'=>'香江祥龙电子(直发)',
    '1200000633'=>'艺度家具(直发)',
    '1200000632'=>'亚驰贸易(直发)',
    '1200000618'=>'居家通专线(直发)',
    '1200000613'=>'兆生家具(直发)',
    '1200000612'=>'泰桦电子(直发)',
    '1200000611'=>'新置家居(直发)',
    '1200000610'=>'欧瑞电子(直发)',
    '1200000609'=>'国晖(直发)',
    '1200000608'=>'红杉供应链(直发)',
    '1200000607'=>'万江博大(直发)',
    '1200000606'=>'迈祺商贸(直发)',
    '1200000605'=>'思派德(直发)',
    '1200000604'=>'尚格电子(直发)',
    '1200000603'=>'定丁实业(直发)',
    '1200000602'=>'高林商贸(直发)',
    '1200000601'=>'酷漫居(直发)',
    '1200000600'=>'黑白调(直发)',
    '1200000599'=>'恒大美森美(直发)',
    '1200000598'=>'博洋控股(直发)',
    '1200000597'=>'多赢电子(直发)',
    '1200000596'=>'丝里伯睡眠(直发)',
    '1200000595'=>'军哥家具(直发)',
    '1200000594'=>'米爱家具(直发)',
    '1200000593'=>'创一家具(直发)',
    '1200000592'=>'惠品宜家(直发)',
    '1200000591'=>'顾家艺购(直发)',
    '1200000590'=>'诺亚创盟(直发)',
    '1200000589'=>'双虎实业(直发)',
    '1200000588'=>'诺贝尔家具(直发)',
);
function vipshop_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    global $vipshop_logistics_name_map;

    foreach($vipshop_logistics_name_map as $code=>$name)
    {
        $companies[]=array
        (
            'shop_id'=>$shop->shop_id,
            'logistics_code'=>$code,
            'name'=>$name,
            'created'=>date('Y-m-d H:i:s',time())
        );
    }
    return true;
}

function vipshop_sync_logistics(&$db, &$trade, $sid)
{
    $shop = getShopAuth($sid, $db, $trade->shop_id);
    $tid = $trade->tid;
    $platformId = $trade->platform_id;
    //供应商ID
    $vendor_id=$shop->account_id;
    $session = $trade->session;
    getAppSecret($trade, $appkey, $appsecret);
    global $vipshop_logistics_name_map;
    if(is_empty($db, $sid, $trade->rec_id, $shop->account_id, $trade->tid, $trade->logistics_code, $trade->logistics_no, $vipshop_logistics_name_map[$trade->logistics_code]))
    {
        logx("vipshop_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, logistics_name {$vipshop_logistics_name_map[$trade->logistics_code]}", $sid.'/Logistics');
        return false;
    }

    if ($trade->is_part_sync == 1)
    {
        $error_msg['status'] = 0;
        $error_msg['info'] = '不支持拆单发货';
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg['info']);
        logx("vipshop_sync_logistics->excute fail:tid {$trade->tid} error:{$error_msg['info']}", $sid.'/Logistics');
        return false;
    }

    try
    {
        require_once TOP_SDK_DIR."/vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
        $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
        $ctx = \Osp\Context\InvocationContextFactory::getInstance();
        $ctx->setAppKey($appkey);
        $ctx->setAppSecret($appsecret);
        $ctx->setAppURL("https://gw.vipapis.com/");
        $ctx->setAccessToken($session);
        $ctx->setLanguage("zh");
        $retval=$service->exportOrderById($vendor_id,$tid);
    }
    catch(\Osp\Exception\OspException $e)
    {
        $error_msg['status'] = 0;
        $error_msg['info']=$e->returnMessage;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg['info']);
        logx("vipshop_sync_logistics->excute fail:tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} logistics_name {$trade->logistics_name} error:{$error_msg['info']}", $sid.'/Logistics','error');

        return false;
    }

    if(API_RESULT_OK != vipshopErrorTest($retval, $db, $trade->shop_id))
    {
        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

        logx("vipshop_export_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid.'/Logistics','error');
        return false;
    }

    //批量发货
    try
    {
        $service=\vipapis\delivery\DvdDeliveryServiceClient::getService();
        $ctx=\Osp\Context\InvocationContextFactory::getInstance();

        //单包裹..
        $ship_list=array();
        $ship=new \vipapis\delivery\Ship();
        $ship->order_id=$tid;
        $ship->carrier_code=$trade->logistics_code;
        // global $vipshop_logistics_name_map;
        $ship->carrier_name=$vipshop_logistics_name_map[$trade->logistics_code];
        $ship->package_type="1";
        $packages=array();
        $package=new \vipapis\delivery\Package();
        $package_product_list=array();

        $result=$db->query("select spec_id,num  from api_trade_order where platform_id={$platformId} and tid={$tid}");

        while($goods=$db->fetch_array($result))
        {
            $product=new \vipapis\delivery\PackageProduct();
            $product->barcode=$goods['spec_id'];
            $product->amount=(int)$goods['num'];
            $package_product_list[]=$product;
        }

        $package->package_product_list=$package_product_list;
        $package->transport_no=$trade->logistics_no;
        $packages[0]=$package;

        $ship->packages=$packages;
        $ship_list[0]=$ship;

        $retval=$service->ship($vendor_id,$ship_list);

    }
    catch(\Osp\Exception\OspException $e)
    {
        $error_msg['status'] = 0;
        $error_msg['info']=$e->returnMessage;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg['info']);
        logx("vipshop_ship_sync->excute fail:tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} logistics_name {$vipshop_logistics_name_map[$trade->logistics_code]} error:{$error_msg['info']}", $sid.'/Logistics','error');
        logx("vipshop_sync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');

        return false;
    }

    logx("vipshop_sync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');

    if(API_RESULT_OK != vipshopErrorTest($retval, $db, $trade->shop_id))
    {
        $error_msg['status'] = 0;
        $error_msg['info'] = @$retval->fail_data[0]->error_msg;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg['info']);

        logx("vipshop_ship_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$error_msg['info']}", $sid.'/Logistics','error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("vipshop_sync_ok: tid {$trade->tid}", $sid.'/Logistics');

    return true;
}

function vipshop_resync_logistics(&$db, &$trade, $sid)
{
    $shop = getShopAuth($sid, $db, $trade->shop_id);
    global $vipshop_logistics_name_map;
    if(is_empty($db, $sid, $trade->rec_id, $shop->account_id, $trade->tid, $trade->logistics_code, $trade->logistics_no, $vipshop_logistics_name_map[$trade->logistics_code]))
    {
        logx("vipshop_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, logistics_name {$vipshop_logistics_name_map[$trade->logistics_code]}", $sid.'/Logistics');
        return false;
    }
    $vendor_id = $shop->account_id;
    $session = $trade->session;
    getAppSecret($trade, $appkey, $appsecret);
    $platformId = $trade->platform_id;
    $tid = $trade->tid;
    try
    {
        require_once TOP_SDK_DIR."/vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
        $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
        $ctx = \Osp\Context\InvocationContextFactory::getInstance();
        $ctx->setAppKey($appkey);
        $ctx->setAppSecret($appsecret);
        $ctx->setAppURL("https://gw.vipapis.com/");
        $ctx->setAccessToken($session);
        $ctx->setLanguage("zh");
        //单包裹..
        $ship_list=array();
        $ship=new \vipapis\delivery\Ship();
        $ship->order_id=$tid;
        $ship->carrier_code=$trade->logistics_code;
        // global $vipshop_logistics_name_map;
        $ship->carrier_name=$vipshop_logistics_name_map[$trade->logistics_code];
        $ship->package_type="1";
        $packages=array();
        $package=new \vipapis\delivery\Package();
        $package_product_list=array();

        $result=$db->query("select spec_id,num  from api_trade_order where platform_id={$platformId} and tid={$tid}");

        while($goods=$db->fetch_array($result))
        {
            $product=new \vipapis\delivery\PackageProduct();
            $product->barcode=$goods['spec_id'];
            $product->amount=(int)$goods['num'];
            $package_product_list[]=$product;
        }

        $package->package_product_list=$package_product_list;
        $package->transport_no=$trade->logistics_no;
        $packages[0]=$package;
        $ship->packages=$packages;
        $ship_list[0]=$ship;

        $retval = $service->editShipInfo($vendor_id,$ship_list);
    }
    catch(\Osp\Exception\OspException $e)
    {
        $error_msg['status'] = 0;
        $error_msg['info']=$e->returnMessage;
        logx("vipshop_ship_resync->excute fail: {$error_msg['info']}",$sid.'/Logistics','error');
        logx("vipshop_resync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');

        return false;
    }

    logx("vipshop_resync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');

    if(API_RESULT_OK != vipshopErrorTest($retval, $db, $trade->shop_id))
    {
        $error_msg['status'] = 0;
        $error_msg['info'] = @$retval->fail_data[0]->error_msg;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg['info']);

        logx("vipshop_ship_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$error_msg['info']}", $sid.'/Logistics','error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("vipshop_sync_ok: tid {$trade->tid}", $sid.'/Logistics');

    return true;
}
