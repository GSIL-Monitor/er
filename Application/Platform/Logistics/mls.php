<?php
require_once(TOP_SDK_DIR . '/mls/MeilishuoClient.php');

$GLOBALS['meilishuo_logistics_name_map'] = array(
	'shunfeng' => '顺丰速运',
	'yunda' => '韵达快运',
	'yuantong' => '圆通速递', 
	'shentong' => '申通快递', 
	'zhongtong' => '中通速递',
	'tiantian' => '天天快递',
	'huitongkuaidi' => '汇通快运', 
	'ems' => 'EMS',
	'emsguoji' => 'EMS国际', 
	'youzhengguonei' => '邮政包裹', 
	'youshuwuliu' => '优速快递', 
	'guotongkuaidi' => '国通快递',
	'quanfengkuaidi' => '全峰快递',
	'zhaijisong' => '宅急送', 
	'kuaijiesudi' => '快捷速递'
);

function meilishuo_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	global $meilishuo_logistics_name_map;
	
	foreach($meilishuo_logistics_name_map as $code=>$name)
	{
		$companies[] = array
		(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $code,
			'name' => $name,
			'created' => date('Y-m-d H:i:s',time())
		);
		
	}
	return true;
}

function meilishuo_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade,$appkey,$appsecret);
	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_code, $trade->logistics_no))
	{
		logx("meilishuo_empty_arg: shop_id {$trade->shop_id}, tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid. "/Logistics");
		return false;
	}
	
	if($trade->platform_id == 20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $trade->session, 'xiaodian.logistics.send');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $trade->session, 'xiaodian.logistics.send');
	}	
	
	
	
	$params = array(
		'expressCode' => $trade->logistics_code,
		'expressId' => $trade->logistics_no,
		'orderId' => $trade->tid,
	);

	$app_params['shipOrderForOpenApiReqDTO']=json_encode($params);

	$retval = $mls->executeByPost($app_params);
	
	if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		
		logx("WARNING $sid meilishuo_sync_fail  shop_id {$trade->shop_id}, tid {$trade->tid}, logistics_code {$trade->logistics_code}, logistics_no {$trade->logistics_no}, error:{$retval->error_msg}",$sid. "/Logistics");
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("meilishuo_sync_ok: shop_id {$trade->shop_id}, tid {$trade->tid}", $sid. "/Logistics");
	
	return true;
	
	
}


















