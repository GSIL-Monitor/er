<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/sos/SosClient.php');
require_once(ROOT_DIR.'/Common/api_error.php');

function sos_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	
	$sos = new SosClient();	
	$sos->setAppKey($shop->key);
	$sos->setAppSecret($shop->secret);
	$sos->setAccessToken($shop->session);
	$sos->setAppMethod("suning.custom.logisticcompany.query");
	
	$params['sn_request']['sn_body']['logisticCompany']['pageNo'] = "1";
	$params['sn_request']['sn_body']['logisticCompany']['pageSize'] = "50";
	
	
	
	$params = json_encode($params);
	$retval = $sos->execute($params);
	//logx(print_r($retval,true),$shop->sid);
	unset($params);
	if(API_RESULT_OK != sosErrorTest($retval,$db, $shop->shop_id. "/Logistics"))
	{
		$error_msg = $retval->error_msg;
		logx("sos_get_logistics_companies fail :$error_msg ",$shop->sid. "/Logistics");
		return false;
	}
	
	$total_page=$retval->sn_head->pageTotal;
	if($total_page==1)
	{
		foreach($retval->sn_body->logisticCompany as $company)
		{
			$companies[] = array
			(
					'shop_id' => $shop->shop_id,
					'logistics_code' => $company->expressCompanyCode,
					'name' => $company->expressCompanyName,
					//'cod_support' => isset($company->is_cod) ? ($company->is_cod ? 2:1) : 0,
					'created' => date('Y-m-d H:i:s',time())
			);
		}
	}
	else
	{
		for($i=$total_page;$i>=1;$i--)
		{
		
			$params['sn_request']['sn_body']['logisticCompany']['pageNo'] = (string)$i;
			$params['sn_request']['sn_body']['logisticCompany']['pageSize'] = "50";
		
		
		
			$params = json_encode($params);
			$retval = $sos->execute($params);
			//logx(print_r($retval,true),$shop->sid);
			unset($params);
			if(API_RESULT_OK != sosErrorTest($retval,$db, $shop->shop_id))
			{
				$error_msg = $retval->error_msg;
				logx("sos_get_logistics_companies fail :$error_msg ",$shop->sid. "/Logistics");
				return false;
			}
		
			foreach($retval->sn_body->logisticCompany as $company)
			{
				$companies[] = array
				(
						'shop_id' => $shop->shop_id,
						'logistics_code' => $company->expressCompanyCode,
						'name' => $company->expressCompanyName,
						//'cod_support' => isset($company->is_cod) ? ($company->is_cod ? 2:1) : 0,
						'created' => date('Y-m-d H:i:s',time())
				);
			}
		
		}
		
	}
	return true;		
}

function sos_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("sos_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid. "/Logistics");
		return false;
	}
	
	$sos = new SosClient();
	$sos->setAppKey($appkey);
	$sos->setAppSecret($appsecret);
	$sos->setAccessToken($trade->session);
	$sos->setAppMethod("suning.custom.orderdelivery.add");
	
	
	$params['sn_request']['sn_body']['orderDelivery']['orderCode'] = $trade->tid;//订单号
	$params['sn_request']['sn_body']['orderDelivery']['expressNo'] = $trade->logistics_no;//快递单号
	$params['sn_request']['sn_body']['orderDelivery']['expressCompanyCode'] = $trade->logistics_code;//快递公司id
	
	$goods_info = $db->query(sprintf("SELECT goods_id,spec_id FROM api_trade_order WHERE platform_id = %d and tid = '%s' ", $trade->platform_id, $trade->tid));
	if(!$goods_info)
	{
		$error_msg = 'Unknow Goods_info';
		set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
		logx("WARNING  $sid sos_sync_logistics  cannot get goods_no ,orderCode {$trade->tid} expressNo {$trade->logistics_no} expressCompanyCode {$trade->logistics_code}",$sid.'/Logistics', 'error');
		return false;
	}

	while($row = $db->fetch_array($goods_info))
	{
		if(empty($row['spec_id']))//配合订单修改，兼容处理
		{
			$params['sn_request']['sn_body']['orderDelivery']['sendDetail']['productCode'][] = $row['goods_id'];
		}
		else
		{
			$params['sn_request']['sn_body']['orderDelivery']['sendDetail']['productCode'][] = $row['spec_id'];
		}
	}
	//判断是否有重复编码
	if(is_repeat($params['sn_request']['sn_body']['orderDelivery']['sendDetail']['productCode'])){
		$sos_trade = new SosClient();
		$sos_trade->setAppKey($appkey);
		$sos_trade->setAppSecret($appsecret);
		$sos_trade->setAccessToken($trade->session);
		$sos_trade->setAppMethod("suning.custom.order.get");
		$parm['sn_request']['sn_body']['orderGet']['orderCode'] =$trade->tid;
		$parm = json_encode($parm);
		$retval = $sos_trade->execute($parm);
		logx("sos_sync_logistics getOrderLineNumber retval:" . print_r($retval, true), $sid. "/Logistics");
		if(!empty($retval->sn_error)){
			logx($sid."sos_sync_logistics getOrderLineNumber fail:" . print_r($retval, true), $sid. "/Logistics",'error');
		}else{
			$t = $retval->sn_body->orderGet;
			$orders = $t->orderDetail;
			$order_count = count($orders);
			for($i = 0; $i < $order_count; $i++)
			{
				$params['sn_request']['sn_body']['orderDelivery']['orderLineNumbers']['orderLineNumber'][]=$orders[$i]->orderLineNumber;
				$params['sn_request']['sn_body']['orderDelivery']['sendDetail']['productCode']='';
			}
		}

	}
	


	$params = json_encode($params);
	//logx(print_r($params,true),$sid);
	$retval = $sos->execute($params);
	logx("sos_sync_logistics retval:" . print_r($retval, true), $sid. "/Logistics");
	if(API_RESULT_OK != sosErrorTest($retval,$db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("WARNING $sid sos_sync_logistics,cannot get goods_no orderCode {$trade->tid} expressNo {$trade->logistics_no} expressCompanyCode {$trade->logistics_code}", $sid . '/Logistics');
		return false;
	}
	set_sync_succ($db,$sid, $trade->rec_id);
	logx("sos_sync_ok: {$trade->tid}", $sid. "/Logistics");
	return true;
	
}
function sos_resync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("sos_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid. "/Logistics");
		return false;
	}
	
	$sos = new SosClient();
	$sos->setAppKey($appkey);
	$sos->setAppSecret($appsecret);
	$sos->setAccessToken($trade->session);
	$sos->setAppMethod("suning.custom.orderdelivery.modify");
	$params['sn_request']['sn_body']['orderDelivery']['orderCode'] = $trade->tid;//订单号
	$params['sn_request']['sn_body']['orderDelivery']['expressNo'] = $trade->logistics_no;//快递单号
	$params['sn_request']['sn_body']['orderDelivery']['expressCompanyCode'] = $trade->logistics_code;//快递公司id	
	$goods_info = $db->query(sprintf("SELECT goods_no FROM api_trade_order WHERE platform_id = %d and tid = '%s' ", $trade->platform_id, $trade->tid));
	if(!$goods_info)
	{
		$error_msg = 'Unknow Goods_info';
		set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
		logx("WARNING $sid sos_resync_logistics,cannot get goods_info orderCode {$trade->tid} expressNo {$trade->logistics_no} expressCompanyCode {$trade->logistics_code}",$sid . '/Logistics');
		return false;
	}

	while($row = $db->fetch_array($goods_info))
	{
		$params['sn_request']['sn_body']['orderDelivery']['sendDetail']['productCode'][] = $row['goods_no'];
	}
	
	$params['sn_request']['sn_body']['orderDelivery']['orderLineNumbers']['orderLineNumber'] = new stdClass();
	
	$params = json_encode($params);
	$retval = $sos->execute($params);
	logx("sos_resync_logistics retval:" . print_r($retval, true), $sid. "/Logistics");
	if(API_RESULT_OK != sosErrorTest($retval,$db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("WARNING $sid sos_resync_logistics,cannot get goods_no orderCode {$trade->tid} expressNo {$trade->logistics_no} expressCompanyCode {$trade->logistics_code}",$sid . '/Logistics');
		return false;
	}
	set_sync_succ($db,$sid, $trade->rec_id);
	logx("sos_sync_ok: {$trade->tid}", $sid. "/Logistics");
	return true;
}
?>