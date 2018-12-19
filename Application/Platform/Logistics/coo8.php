<?php
require_once(TOP_SDK_DIR . '/coo8/Coo8Client.php');

function coo8_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$account = $shop->account_nick;
	$secret = $shop->secret;
	
	$params = array(
		'venderId' => $account,
		'method' => 'coo8.carriers.get',
		'timestamp' => date('Y-m-d H:i:s', time()),
		'v'=>2.0,
		'signMethod' => 'md5',
		'format' => 'json'
		);
	
	$coo8 = new Coo8Client();
	$retval = $coo8->sendByPost(COO8_API_URL, $params, $secret);
	if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		logx("ERROR $sid coo8DownloadTradeList error_msg:{$error_msg}", $sid . "/Logistics", 'error' );
		return false;
	}	
	else
	{
		foreach($retval->carriers->carrier as $LogisticsCorpPop)
		{
		
			$companies[] = array
			(
				'shop_id' => $shop->shop_id,
				'logistics_code' => $LogisticsCorpPop->logisticsId,
				'name' => $LogisticsCorpPop->logisticsName,
				'created' => date('Y-m-d H:i:s',time())
			);
			
		}
		
		return true;
	
	}
}
function coo8_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	$shop = getShopAuth($sid, $db, $trade->shop_id);
	
	if(is_empty($db, $sid, $trade->rec_id, $shop->account_nick, $trade->tid, $trade->logistics_code, $trade->logistics_no))
	{
		logx("coo8_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid . "/Logistics");
		return false;
	}
	//API系统参数
	$params = array(
		'venderId' => $shop->account_nick,
		'method' => 'coo8.order.send',
		'timestamp' => date('Y-m-d H:i:s', time()),
		'v'=>2.0,
		'signMethod' => 'md5',
		'format' => 'json',
		'orderid' => $trade->tid,
		'carriersName' => $trade->logistics_code,
		'logisticsNumber' => $trade->logistics_no
		);
	
	$coo8 = new Coo8Client();
	$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
	logx("coo8_sync_logistics retval:" . print_r($retval, true), $sid . "/Logistics");
	
	if(API_RESULT_OK != coo8ErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		
		logx("WARNING $sid coo8_sync_fail tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid. "/Logistics" );
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("coo8_sync_ok: {$trade->tid}", $sid . "/Logistics");

	return true;

}