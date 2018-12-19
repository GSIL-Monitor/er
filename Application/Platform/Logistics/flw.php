<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/flw/FlwClient.php');



$GLOBALS['flw_logistics_name_map'] = array(
	'8888' => '商家自行配送',
	'10001' => '申通快递',
	'10003' => '圆通速递',
	'10005' => '汇通快运',
	'10007' => '中通速递',
	'10009' => '运通快递',
	'10011' => '韵达快运',
	'10013' => '顺丰速递',
	'10015' => '包裹/平邮/挂号信',
	'10017' => '邮政小包',
	'10019' => '优速物流',
	'10023' => 'UPS（中文结果）',
	'10025' => '通和天下',
	'10027' => '天天快递',
	'10029' => '天地华宇',
	'10031' => '穗佳物流',
	'10033' => '圣安物流',
	'10035' => '山西红马甲',
	'10037' => '山东海虹',
	'10039' => '赛澳递',
	'10045' => '上大物流',
	'10047' => '盛丰物流',
	'10049' => '速尔物流',
	'10051' => '盛辉物流',
	'10053' => '三态速递',
	'10055' => '如风达快递',
	'10057' => '七天连锁',
	'10059' => '全峰快递',
	'10061' => '全一快递',
	'10063' => '全日通',
	'10065' => '全际通',
	'10067' => '全晨快递',
	'10069' => 'onTrac',
	'10071' => 'OCS',
	'10073' => '明亮物流',
	'10075' => '美国快递',
	'10077' => '门对门',
	'10079' => '隆浪快递',
	'10081' => '立即送',
	'10083' => '乐捷达',
	'10085' => '蓝镖快递',
	'10087' => '龙邦物流',
	'10089' => '联昊通',
	'10091' => '跨越物流',
	'10093' => '康力物流',
	'10095' => '快捷速递',
	'10097' => '嘉里大通',
	'10099' => '金大物流',
	'10101' => '捷特快递',
	'10103' => '晋越快递',
	'10105' => '急先达',
	'10107' => '京广速递',
	'10109' => '加运美',
	'10111' => '佳怡物流',
	'10113' => '佳吉物流',
	'10115' => '山东海红',
	'10117' => '华企快运',
	'10119' => '海盟速递',
	'10121' => '河北建华',
	'10123' => '海外环球',
	'10125' => '海航天天',
	'10127' => '华夏龙',
	'10129' => '恒路物流',
	'10131' => '华宇物流',
	'10135' => '共速达',
	'10137' => 'GLS',
	'10139' => '国际邮件',
	'10141' => '国内邮件',
	'10143' => '挂号信',
	'10145' => '广东邮政',
	'10147' => '国通快递',
	'10149' => '港中能达',
	'10151' => '飞豹快递',
	'10153' => '风行天下',
	'10155' => '凡客如风达',
	'10157' => '中天万运',
	'10159' => '飞快达',
	'10161' => '郑州建华',
	'10163' => '飞康达物流',
	'10165' => '芝麻开门',
	'10167' => '中速快件',
	'10169' => '忠信达',
	'10171' => '中邮物流',
	'10173' => '宅急送',
	'10175' => '一统飞鸿',
	'10177' => 'E邮宝',
	'10179' => '银捷快递',
	'10181' => 'EMS（中文结果）',
	'10185' => '忠信达快递',
	'10187' => '递四方',
	'10189' => '原飞航',
	'10191' => 'D速快递',
	'10193' => '源安达',
	'10195' => '越丰物流',
	'10197' => '元智捷诚',
	'10199' => '源伟丰快递',
	'10201' => 'DPEX',
	'10205' => '德邦物流',
	'10207' => '一邦速递',
	'10209' => '大田物流',
	'10211' => '亚风速递',
	'10213' => '远成物流',
	'10215' => '传喜物流',
	'10217' => '中国东方(COE)',
	'10219' => '希伊艾斯',
	'10221' => '邦送物流',
	'10223' => '香港邮政',
	'10225' => '新蛋奥硕物流',
	'10227' => 'BHT',
	'10229' => '新邦物流',
	'10231' => '百福东方',
	'10233' => '信丰物流',
	'10235' => '百世汇通',
	'10239' => '安信达',
	'10241' => '微特派',
	'10243' => 'AAE',
	'10245' => '万象物流',
	'10247' => '万家物流',
	//'10249'     =>      'USPS(中英文)',
	//'10251'     =>      'TNT(英文结果)',
	//'10253'     =>      'TNT(中文结果)',
	//'10255'     =>      'UPS(英文结果)',
	//'10257'     =>      '联邦快递(Fedex-中国-英文结果)',
	//'10259'     =>      '联邦快递(Fedex-中国-中文结果)',
	'10261' => '加拿大邮政Canada',
	//'10263'     =>      '邮政小包(国际)',
	'10265' => '加拿大邮政',
	//'10267'     =>      '澳大利亚邮政(英文结果)',
	//'10269'     =>      'DHL-德国件-德文结果',
	//'10271'     =>      'DHL-国际件-中文结果',
	//'10273'     =>      'Fedex-国际件-中文结果',
	//'10275'     =>      'Fedex-美国件',
	//'10277'     =>      'DHL-中国件-中文结果',
	//'10279'     =>      'EMS-(中国-国际)',
	//'10283'     =>      'EMS(英文结果)',
	//'10285'     =>      'Fedex-国际件-英文结果',
	//'10287'     =>      '顺丰(英文结果)',
	'10289' => '安能物流'

);

function flw_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	global $flw_logistics_name_map;
	
	foreach ($flw_logistics_name_map as $code => $name) {
		$companies[] = array
		(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $code,
			'name' => $name,
			'created' => date('Y-m-d H:i:s', time())
		);
	}
	return true;
}

function flw_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	global $flw_logistics_name_map;
	if (is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code)) {
		logx("flw_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid. "/Logistics");
		return false;
	}
	global $flw_app_config;
	$fl = new FlwClient($appkey, $appsecret, $flw_app_config);
	$fl->setDirname('order/delivery/add');
	$params['deliveryItem'] = array(
		'orderCode' => $trade->tid,
		'expressNo' => $trade->logistics_no,
		'expressCode' => $trade->logistics_code,
		'expressCompany' => $flw_logistics_name_map[$trade->logistics_code],
	);
	$retval = $fl->execute($params, 'post');
	if (API_RESULT_OK != flwErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
		logx("flw_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid. "/Logistics");
		return false;
	}
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("flw_sync_ok: tid: {$trade->tid} logistics_no: {$trade->logistics_no} logistics_code: {$trade->logistics_code}", $sid. "/Logistics");
	
	return true;
}
