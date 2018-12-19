<?php
//小红书
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/xhs/XhsClient.php');
require_once(ROOT_DIR.'/Common/api_error.php');

function xhs_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{   
    $sid = $shop->sid;
	$shopid = $shop->shop_id;
	getAppSecret($shop, $appkey, $appsecret);
	$system_param['timestamp'] = time();
	$system_param['app-key'] = $appkey;		
	$xhs = new XhsClient();
	$url = '/ark/open_api/v0/express_companies';
	@$retval = $xhs->sendByGet($url,$appsecret,$system_param);
	
	if($retval->code != NULL)
	{
		logx("xhs_get_logistics_companies : {$shop->shop_id} {$retval->error_msg}", $sid.'/Logistics');
		$error_msg = $retval->error_msg;
		return false;
	} else {
		foreach($retval->data as $company)
		{

			$companies[] = array
			(
				'shop_id' => $shopid,
				'logistics_code' => $company->express_company_code,
				'name' => $company->express_company_name,
				'created' => date('Y-m-d H:i:s',time()),
			);
			
		}
		
		return true;
	}
}


function xhs_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("xhs_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
		return false;
	}
	
	//API系统参数
	$system_param['timestamp'] = time();
	$system_param['app-key'] = $appkey;
	$url="";
	$url = "/ark/open_api/v0/packages/".$trade->tid;
	$xhs = new XhsClient();

	$retval = $xhs->sendByPost($url, $appsecret, $system_param,$trade->logistics_code,$trade->logistics_no);
	
	if(API_RESULT_OK != xhsErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		
		logx("WARNING $sid xhs_sync_fail tid {$trade->tid}, logistics_code {$trade->logistics_code}, logistics_no {$trade->logistics_no}, error:{$retval->error_msg}",$sid.'/Logistics');
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("xhs_sync_ok: {$trade->tid}", $sid.'/Logistics');
	
	return true;
}

?>