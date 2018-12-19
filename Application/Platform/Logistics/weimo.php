<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/wm/newWmClient.php');
require_once(ROOT_DIR . '/Common/api_error.php');

$GLOBALS['wm_logistics_name_map'] = array(
		'ems' => 'EMS',
		'huitongkuaidi' => '百世汇通',
		'quanfengkuaidi' => '全峰快递',
		'shentong' => '申通',
		'shunfeng' => '顺丰速运',
		'yuantong' => '圆通速递',
		'yunda' => '韵达快运',
		'zhaijisong' => '宅急送',
		'zhongtong' => '中通速递',
		'tiantian' => '天天快递',
		'debangwuliu' => '德邦物流',
		'dhl' => 'DHL中国件',
		'dhlen' => 'DHL国际件',
		'suer' => '速尔物流',
		'jiayiwuliu' => '佳怡物流',
		'yuanchengwuliu' => '远成物流',
		'tiandihuayu' => '天地华宇',
		'ups' => 'UPS',
		'youshuwuliu' => '优速物流',
		'lianbangkuaidi' => '联邦快递',
		'longbanwuliu' => '龙邦物流',
		'tnt' => 'TNT',
		'usps' => 'USPS',
		'youzhengguonei' => '邮政国内',
		'youzhengguoji' => '邮政国际',
		'ziyouwuliu' => '自有物流',
		'jinguangsudikuaijian' => '京广速递',
		'quanyikuaidi' => '全一快递',
		'guotongkuaidi' => '国通快递',
		'nsf' => '新顺丰',
		'feibaokuaidi' => '飞豹快递',
		'kuaijiesudi' => '快捷快递',
		'wanjiakangwuliu' => '万家康物流',
		'heigouwuliu' => '黑狗物流',
		'jikelenglian' => '极客冷链',
		'speedoex' => '申必达国际速递',
		'annengwuliu' => '安能物流',
		'huitongkuaidi' => '百世快运',
		'lantiankuaidi' => '蓝天国际航空快递',
		'quansutong' => '全速通',
		'xixiguojikuaidi' => '西西国际速递',
		'LZGJWL' => '联众国际物流',
		'hnfy' => '飞鹰物流',
		'polarexpress' => '极地快递',
		'jd' => '京东快递',
		'youshuwuliu' => '优速快递',
		'xlobo' => '贝海速递',
		'oyshk' => '欧亚物流',
		'easy2go' => '中欧联邦物流',
		'zhonghuan' => '中环国际快递',
		'auexpress' => '澳邮中国快运',
		'auvanda' => '中联速递',
		'flywayex' => '程光快递',
		'idada' => '大达物流',
		'supinexpress' => '速品物流',
		'freakyquick' => '狂派物流',
		'briems' => '宏桥国际',
		'rrs' => '日日顺',
		'fastgo' => '速派快递FastGO',
		'zhaijibian' => '宅急便',
		'hxjsd' => '豪祥物流',
		'200056' => '贰仟家物流',
		'liancheng56' => '联诚物流',
		'wqudao' => '花花牛物流',
		'chinz56' => '秦远国际物流',
		'vtepai' => '微特派快递',
		'xinfengwuliu' => '信丰',
		'ycgky' => '远成快运 ',
		'shipgce' => '飞洋',
		'xingyuankuaidi' => '新元快递',
		'yue777' => '玥玛快递',
		'banma' => '斑马物联网',
		'goldhaitao' => '金海淘',
		'966977' => '重报物流',
		'hsdl' => '青岛合盛达物流',
		'sihaiet' => '四海快递',
		'uluckex' => '优联吉运',
		'ecmsglobal' => '易客满',
		'rufengda' => '如风达',
);
//下载物流公司
function weimo_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	global $wm_logistics_name_map;

	foreach ($wm_logistics_name_map as $code => $name) {
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

//同步物流
function weimo_sync_logistics(&$db, &$trade, $sid)
{
	global $wm_logistics_name_map;
	getAppSecret($trade, $appkey, $appsecret);

	$appId = $appkey;
	$appSecret = $appsecret;

	$shopid = $trade->shop_id;

	if (is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code)) {
		logx("weimob_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
		return false;
	}

	$session = $trade->session;

	$client = new WmClient();
	$client->setSession($session);

	$api_action = 'wangpu/Logistics/Delivery'; //发货接口  同步物流

	$result = $db->query_result("select buyer_area,buyer_message,buyer_name,receiver_mobile from api_trade where tid=%s and platform_id=%d", $trade->tid, $trade->platform_id);

	$no = iconv_strpos($trade->tid, "s");
	if ($no != false) {
		$tid = iconv_substr($trade->tid, 0, $no, 'UTF-8');
	} else {
		$tid = $trade->tid;
	}

	$params = array(
			'deliveries' => array(
					array(
							'order_no' => $trade->tid,
							'need_delivery' => true,
							'carrier_code' => $trade->logistics_code,
							'carrier_name' => $wm_logistics_name_map[$trade->logistics_code],
							'express_no' => $trade->logistics_no,
							'remark' => $result['buyer_message'],
							'sender_address' => $result['buyer_area'],
							'sender_name' => $result['buyer_name'],
							'sender_tel' => @$result['receiver_mobile'],
					),
			),
	);

	$retval = $client->execute($api_action, $params);

	if (API_RESULT_OK != weimoErrorTest($retval, $db, $shopid)) {

		if ($retval->code->errcode == 80001001000119) {
			logx("weimob_stock_sync fail {$shopid} refreshWeimoToken error_msg: 授权刷新", $sid.'/Logistics');
			refreshWeimoToken($db, $trade);
			releaseDb($db);
			return false;
		}

		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

		logx("weimob_sync_fail: order_no {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid.'/Logistics');
		logx("WARNING $sid weimob_sync_fail: order_no {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}",$sid.'/Logistics');
		return false;
	}

	if (isset($retval->data[0]->is_success) && $retval->data[0]->is_success == 1) {
		set_sync_succ($db, $sid, $trade->rec_id);

		logx("weimob_sync_ok: order_no {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
		return true;
	}

	$error_msg = $retval->data[0]->error_message;
	set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);

	logx("weimob_sync_other_fail: order_no {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$error_msg}", $sid.'/Logistics');
	return false;

}