<?php 
require_once(TOP_SDK_DIR . '/smt/SmtClient.php');

function smt_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	getAppSecret($shop, $appkey, $appsecret);
	$retval = Smt::getLogisticsCompanies($appkey, 
										$appsecret,
										$shop->session);
	if(API_RESULT_OK != smtbabaErrorTest($retval,$db,$shopid))
	{
		logx("get_logistics_companies getLogisticsCompanies: {$shop->shop_id} {$retval->error_msg}", $shop->sid.'/Logistics');
		$error_msg['status'] = 0;
		$error_msg['info'] = $retval->error_msg;
		return false;
	}
	else
	{
		foreach($retval->result as $company) 
		{
			$companies[] = array
			(
				'shop_id' => $shop->shop_id,
				'logistics_code' => $company->serviceName,
				'name' => $company->displayName,
				'created' => date('Y-m-d H:i:s',time())
			);
			
		}
		return true;
	}
}

function smt_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	
	/*$oids = '';
	$oid_info = $db->query("select GROUP_CONCAT(SUBSTR(oid,4)) as oid from api_trade_order where platform_id = {$trade->platform_id} and tid = {$trade->tid}");
	while($row = $db->fetch_array($oid_info))
	{
		$oids .= $row['oid'] . ",";
	}
	$oids = rtrim($oids, ",");*/
	$retval = Smt::syncLogistics($appkey, 
								$appsecret,
								$trade->session,
								$trade->tid, 
								$trade->logistics_no,
								$trade->logistics_code);
	logx("smtbaba_sync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');
	if(API_RESULT_OK != smtbabaErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("WARNING $sid smt_sync_fail tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}",$sid.'/Logistics');
		
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("smt_sync_ok: {$trade->tid}", $sid.'/Logistics');
	
	return true;
}


?>