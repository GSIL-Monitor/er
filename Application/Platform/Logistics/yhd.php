<?php
//1号店
include_once(TOP_SDK_DIR . '/yhd/YhdClient.php');

function yhd_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{   
    $sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appKey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	
	$params = array();
	
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	$params['timestamp'] = date('Y-m-d H:i:s',time());
	$params['method'] = "yhd.logistics.deliverys.company.get";
			
	$yhd = new YhdClient();
	$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
	
	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		logx("yhd_get_logistics_companies : {$shopid} {$retval->error_msg}", $sid . "/Logistics");
		$error_msg = $retval->error_msg;
		return false;
	}
	else
	{  
		foreach($retval->logisticsInfoList->logisticsInfo as $company)
		{

			$companies[] = array
			(
				'shop_id' => $shopid,
				'logistics_code' => $company->id,
				//'logistics_id' => $company->id,
				'name' => $company->companyName,
				//'cod_support' =>0,
				'created' => date('Y-m-d H:i:s',time())
			);
			
		}
		
		return true;
	}
}


function yhd_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("yhd_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid . "/Logistics");
		return false;
	}
	
	//API系统参数
	$params = array();
	$params['appKey'] = $appkey;
	$params['sessionKey'] = $trade->session;
	$params['format'] = "json";
	$params['ver'] = "1.0";
	$params['timestamp'] = date('Y-m-d H:i:s',time());

	$params['method'] = "yhd.logistics.order.shipments.update";
	
	$params['orderCode'] = $trade->tid;
	$params['deliverySupplierId'] = $trade->logistics_code;
	$params['expressNbr'] = $trade->logistics_no;
	
	$yhd = new YhdClient();
	
	$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
	
	if(API_RESULT_OK != yhdErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		
		logx("WARNING $sid yhd_sync_fail tid {$trade->tid} logistics_code {$trade->logistics_code} logistics_no {$trade->logistics_no} error:{$retval->error_msg}", $sid . "/Logistics");
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("yhd_sync_ok: {$trade->tid}", $sid . "/Logistics");
	
	return true;
}

?>