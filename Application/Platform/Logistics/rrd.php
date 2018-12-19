<?php
require_once(TOP_SDK_DIR . '/rrd/RrdClient.php');

function rrd_get_logistics_companies(&$db,&$shop,&$companies,&$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;

	$rrdApi = new RrdClient();
	$rrdApi->appid = $appkey;
	$rrdApi->secret = $appsecret;
	$rrdApi->access_token = $session;
	$rrdApi->method = "weiba.wxrrd.shipping.company";
	$mode = "POST";
	$retval = $rrdApi->execute(NULL,$mode);
	//logx("rrd_get_logistics_companies $shopid".print_r($retval,true));
	if (API_RESULT_OK != rrdErrorTest($retval,$db,$shopid)) 
	{
		if (30008 == intval(@$retval->errCode))
		{	
			releaseDb($db);
			refreshRrdToken($appkey, $appsecret, $shop);
			$error_msg = $retval->error_msg;
			return TASK_OK; 
		}
		$error_msg['status'] = 0;
		$error_msg['info'] = $retval->error_msg;
		logx("ERROR $sid rrd_get_logistics_companies {$error_msg['info']}",$sid . '/Logistics','error');
		return TASK_OK;
	}else{
		for ($i=0; $i < count($retval->data); $i++) 
		{ 
			$company = $retval->data[$i];
			$companies[] = array(
				'shop_id' => $shop->shop_id,
				'name' => $company->name,
				'logistics_code' => $company->code,
				'created' => date('Y-m-d H:i:s',time())
			);
		}
		return true;
	}
}

function rrd_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	//获取店铺授权信息
	$shop = getShopAuth($sid, $db, $trade->shop_id);

	$tid = $trade->tid;

	$params = array(
		'order_sn' => $tid,
		'logis_no' => $trade->logistics_no,
		'logis_code' => $trade->logistics_code
		);

	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;

	$rrdApi = new RrdClient();
	$rrdApi->appid = $appkey;
	$rrdApi->secret = $appsecret;
	$rrdApi->access_token = $session;
	$rrdApi->method = "weiba.wxrrd.trade.send";
	$mode = "POST";

	$retval = $rrdApi->execute($params,$mode);

	logx("rrd_sync_logistics retval:".print_r($retval,true),$sid . '/Logistics');
	if (API_RESULT_OK != rrdErrorTest($retval, $db, $trade->shop_id)) {
		if (30008 == intval(@$retval->errCode))
		{	
			releaseDb($db);
			refreshRrdToken($appkey, $appsecret, $shop);
			$error_msg = $retval->error_msg;
			return TASK_OK; 
		}
		$error_msg = $retval->error_msg;
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx ( "$sid rrd_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid . '/Logistics');
		logx("ERROR $sid rrd_sync_logistics {$error_msg}",$sid . '/Logistics','error');
		return TASK_OK;
	}
	set_sync_succ($db, $sid, $trade->rec_id);
	logx ( "rrd_sync_ok: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid . '/Logistics');
	return true;
}










