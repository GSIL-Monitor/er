<?php
//善融商城
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');

require_once(TOP_SDK_DIR . '/ccb/ccbClient2.php');


$GLOBALS['ccb_logistics_name_map'] = array(
	'0000000001' => '申通快递',
	'0000000002' => 'EMS快递',
	'0000000003' => '顺丰快递',
	'0000000004' => '圆通快递',
	'0000000005' => '中通快递',
	'0000000006' => '如风达',
	'0000000007' => '韵达快递',
	'0000000008' => '天天快递',
	'0000000009' => '汇通快递',
	'0000000010' => '全峰快递',
	'0000000011' => '德邦物流',
	'0000000012' => '宅急送',
	'0000000013' => '龙邦快递',
	'0000000014' => '全一快递',
	'0000000015' => '安信达',
	'0000000016' => '急先达',
	'0000000017' => 'E邮宝',
	'0000000018' => '全日通',
	'0000000019' => '飞康达',
	'0000000020' => '联昊通',
	'0000000021' => '共速达',
	'0000000022' => '快捷速递',
	'0000000023' => '中邮物流',
	'0000000024' => '中铁快运',
	'0000000025' => '优速快递',
	'0000000026' => '港中能达',
	'0000000027' => '天地华宇',
	'0000000028' => 'FedEx',
	'0000000029' => 'DHL',
	'0000000030' => 'UPS',
	'0000000031' => 'TNT',
	'0000000501' => '海尔物流',
	'0000000506' => '速尔快递',
	'0000000511' => '国内小包',
	'0000000516' => '澳柯玛物流',
	'0000000521' => '黑猫宅急便',
	'0000000526' => '爱彼西物流',
	'0000000527' => '国通快递',
	'0000000528' => '河北城通物流有限公司',
	'0000000531' => '国美快递',
	'0000000536' => '新邦物流',
	'0000000541' => '新进电器生活馆自有物流',
	'0000000546' => '邮政电商小包',
	'0000000551' => '安捷快递',

);

function ccb_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	global $ccb_logistics_name_map;
	foreach($ccb_logistics_name_map as $code=>$name)
	{
		$companies[] = array(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $code,
			'name' => $name,
			'created' => date('Y-m-d H:i:s', time())
		);
	}
	return true;
}

function ccb_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);

	$client = new ccbClient();
	$client->setKey($appsecret);

	$params = array();
	$params['head'] = array(
		'tran_code' => 'T0005',
		'cust_id' => $appkey,
		'tran_sid' => '',
	);

	$params['body']['delivery']['order_id'] = $trade->tid;
	if($trade->logistics_type == 1)//无需物流发货
	{
		$params['body']['type'] = 1;
	}
	else//指定物流发货
	{
		$params['body']['delivery']['out_sid'] = $trade->logistics_no;//运单号
		$params['body']['delivery']['company_code'] = $trade->logistics_code;//快递编号编号
		$params['body']['type'] = 0;
	}
	$retval = $client->execute($params);

	if(API_RESULT_OK != ccbErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval['error_msg']);
		logx("ccb_export_sync_fail: tid:{$trade->tid} logistics_no:{$trade->logistics_no} logistics_code:{$trade->logistics_code} logistics_name:{$trade->logistics_name} error:{$retval['error_msg']}", $sid.'/Logistics');
		return false;
	}

	set_sync_succ($db, $sid, $trade->rec_id);
	logx("ccb_sync_ok: tid:{$trade->tid}", $sid.'/Logistics');

	return true;
}