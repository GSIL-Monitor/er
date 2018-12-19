<?php
//拼多多 物流

require_once(TOP_SDK_DIR. '/pdd/pddClient2.php');
require_once(TOP_SDK_DIR. '/pdd/request/LogisticsCompaniesGetRequest_pdd.php');
require_once(TOP_SDK_DIR. '/pdd/request/LogisticsOnlineSendRequest_pdd.php');

require_once(ROOT_DIR. '/Logistics/util.php');
//require_once(ROOT_DIR. '/modules/logistics/util.php');
require_once(ROOT_DIR. '/Common/api_error.php');

function pdd_get_logistics_companies(&$db, $shop, &$companies, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;

	getAppSecret($shop,$appkey,$appsecret);

	$client = new pddClient2();

	$req = new LogisticsCompaniesGetRequest_pdd();
	$retval = $client->execute($req);

	if(isset($retval->logistics_companies)){
		foreach($retval->logistics_companies as $row)
		{
			$companies[] = array(
					'shop_id' => $shop->shop_id,
					'logistics_code' => $row->id,
					'name' => $row->logistics_company,
					'created' => date('Y-m-d H:i:s',time()),
			);
		}
	}

	return true;
}

function pdd_sync_logistics(&$db, &$trade, $sid)
{
	usleep(5*10000);
	getAppSecret($trade,$appkey,$appsecret);

	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no,$trade->logistics_code))
	{
		logx("pdd_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code:{$trade->logistics_code}", $sid.'/Logistics');
		return false;
	}

	$client = new pddClient2();
	$client->clientId = $appkey;
	$client->clientSecret = $appsecret;
	$client->dataType = 'JSON';
	$client->accessToken = $trade->session;

	$req = new LogisticsOnlineSendRequest_pdd();
	$req->setOrderSn($trade->tid);//订单号
	$req->setLogisticsId($trade->logistics_code);//快递公司编号
	$req->setTrackingNumber($trade->logistics_no);//快递单号

	$retval = $client->execute($req);
	if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
		$trade->sid = $sid;
		refreshPddToken($db,$appkey,$appsecret,$trade);
		$client->accessToken = $trade->session;
		$retval = $client->execute($req);
	}

	if(API_RESULT_OK != pddErrorTest($retval,$db,$trade->shop_id))
	{
		$error_msg = $retval->error_msg;
		if(strpos('DOCTYPE',$error_msg)){
			$error_msg = '拼多多平台错误';
		}
		set_sync_fail($db,$sid,$trade->rec_id,2,$error_msg);

		logx("pdd_sync_fail tid:{$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} retval:".print_r($retval,true),$sid.'/Logistics');
		return TASK_OK;
	}

	set_sync_succ($db,$sid,$trade->rec_id);
	logx("pdd_sync_ok tid:{$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}",$sid.'/Logistics');

	return true;
}


