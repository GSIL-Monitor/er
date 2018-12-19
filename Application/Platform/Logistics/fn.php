<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/fn/FnClient.class.php');
//下载物流公司
function fn_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    $sid = $shop->sid;
	$shopId = $shop->shop_id;
	$session = $shop->session;
	$client = new FnClient($shop->key,$shop->secret);
	$client->setAuthSession($session);
	$method= "fn.logistics.companies.get";
	$data = array();
	$params = array(
	    'method'=> $method,
	    'params'=>@json_encode($data),
	);
	$retval = $client->sendDataByCurl($params);
	
	if(API_RESULT_OK != fnErrorTest($retval,$db,$shopId))
	{
		$error_msg = $retval->error_msg;
		
		logx("ERROR $sid fn_get_logistics_companies   $error_msg" ,$sid.'/Logistics','error');
		return false;
	}
	else
	{
		$row=(array)$retval->data;

		foreach($row as $company)
		{
			$companies[] = array
			(
				'shop_id' => $shopId,
				'logistics_code' => $company->id,//logisticsNo
				'name' => $company->name,
				'created' => date('Y-m-d H:i:s',time())
			);
		}
		
		return true;
	}
}
//发货
function fn_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	
	$client = new FnClient($appkey,$appsecret);
	$client->setAuthSession($trade->session);

	$method= "fn.order.deliverByOgNo";//订单发货
	$data = array(
	    'express_id'=>$trade->logistics_code,
	    'express_no'=>$trade->logistics_no,
		'ogNo'=>$trade->tid,
	);
	$params = array(
	    'method'=> $method,
	    'params'=>@json_encode($data),
	);
	$retval = $client->sendDataByCurl($params);
	
	if(API_RESULT_OK != fnErrorTest($retval,$db,$trade->shop_id))
	{
		$error_msg = $retval->error_msg;
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("ERROR $sid fn_sync_logistics   $error_msg", $sid.'/Logistics','error');
		return false;
	}	

	set_sync_succ($db, $sid, $trade->rec_id);
	logx("fnshop_sync_ok: tid: {$trade->tid} logistics_no: {$trade->logistics_no} logistics_code: {$trade->logistics_code}", $sid.'/Logistics');
	
	return true;	
}
?>