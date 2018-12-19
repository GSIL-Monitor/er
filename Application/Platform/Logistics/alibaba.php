<?php 
require_once(TOP_SDK_DIR . '/alibaba/AlibabaApi.class.php');

function alibaba_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	getAppSecret($shop, $appkey, $appsecret);
	$retval = AlibabaApi::getLogisticsCompanies(
											$appkey, 
											$appsecret,
											$shop->session,
											$shop->account_id);
	if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
	{
		logx("get_logistics_companies getLogisticsCompanies: {$shop->shop_id} {$retval->error_msg}",$sid . "/Logistics");
		$error_msg['info'] = $retval->error_msg;
		$error_msg['status'] = 0;
		return false;
	}
	else
	{
		foreach($retval->toReturn as $company) 
		{
			$companies[] = array
			(
				'shop_id' => $shop->shop_id,
				//'logistics_code' => $company->companyNo,
				'logistics_code' => $company->id,
				'name' => $company->companyName,
				//'cod_support' => isset($company->is_cod) ? ($company->is_cod ? 2:1) : 0,
				'created' => date('Y-m-d H:i:s',time())
			);
			
		}
		return true;
	}
}

function alibaba_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("ali_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid);
		return false;
	}
	
	$oid_info = $db->query_result("select GROUP_CONCAT(SUBSTR(oid,4)) as oid from api_trade_order where platform_id = {$trade->platform_id} and tid = '".$db->escape_string($trade->tid)."' and status in (30,40)");
	$oid_send = $oid_info['oid'];
	if($trade->logistics_type == 1) //无单号
	{
		$retval = AlibabaApi::syncDummyLogistics($appkey, 
											$appsecret,
											$trade->session,
											$trade->account_id, 
											$trade->tid, 
											$oid_info['oid']
											);
	}
	else
	{
		if (1 == $trade->is_part_sync)
		{
			handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);	
			$oids = explode(',', $trade->oids);
			$oid_send = '';
			for ($i=0; $i <count($oids) ; $i++)
			{ 
				$oid_send .= substr($oids[$i],3).',';
			}
			$oid_send = trim($oid_send,',');

		}
		$retval = AlibabaApi::syncLogistics($appkey, 
											$appsecret,
											$trade->session,
											$trade->account_id, 
											$trade->tid, 
											$oid_send, 
											$trade->logistics_no,
											$trade->logistics_code, 
											$trade->logistics_name
											);
	}
		
	logx("alibaba_sync_logistics retval:" . print_r($retval, true), $sid. "/Logistics");

	if(API_RESULT_OK != alibabaErrorTest($retval, $db, $trade->shop_id))
	{
		if (401 == intval($retval->error_code))
		{
			releaseDb($db);
			refreshAliToken($appkey, $appsecret, $trade);
			$db = getUserDb($sid);
			if(!set_sync_reset($db,$sid,$trade->rec_id)){
				logx('reset_sync fail',$sid.'/Logistics','error');
				return false;
			}
			return true;
		}
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("WARNING $sid ali_sync_fail tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid. "/Logistics");
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("ali_sync_ok: {$trade->tid}", $sid. "/Logistics");
	
	return true;
}


?>
