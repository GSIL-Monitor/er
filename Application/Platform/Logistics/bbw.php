<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/bbw/bbwClient.php');


function bbw_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$shopId=$shop->shop_id;
	$bbw=new bbwClient($shop->key,$shop->secret,$shop->session);
	$retval=$bbw->get_logistics_companies();
	if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
	{
		$error_msg = $retval->error_msg;
		
		logx("bbw_get_logistics_companies $shopId bbw->get_logistics_companies fail  ". $error_msg,  $shopId. "/Logistics",'error');

		return TASK_OK;
	}
	else
	{
		$row=(array)$retval->data;

		foreach($row as $code => $company)
		{
			$companies[] = array
			(
				'shop_id' => $shopId,
				'logistics_code' => $code,
				'name' => $company,
				'created' => date('Y-m-d H:i:s',time())
			);
		}
		
		return true;
	}
}

function bbw_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);

	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("bbw_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
		return false;
	}

	$bbw=new bbwClient($appkey,$appsecret,$trade->session);
	$retval=$bbw->logistics($trade->tid,$trade->logistics_code,$trade->logistics_no);

	logx("bbw_sync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');
	
	if(API_RESULT_OK != bbwErrorTest($retval,$db,$trade->shop_id))
	{
		$error_msg = $retval->error_msg;
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("bbw_sync_fail: tid {$trade->tid}, logistics_code {$trade->logistics_code}, logistics_no {$trade->logistics_no}, error:{$retval->error_msg}",  $sid. "/Logistics");

		return TASK_OK;
	}	

	set_sync_succ($db, $sid, $trade->rec_id);
	logx("bbwshop_sync_ok: tid {$trade->tid}",$sid.'/Logistics');
	
	return true;	
}
?>