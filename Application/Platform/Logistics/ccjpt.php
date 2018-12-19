<?php

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/ccjpt/ccjptClient.php');
require_once(ROOT_DIR.'/Common/api_error.php');

$GLOBALS['ccjpt_logistics_name_map'] = array(
	'ems'=>'EMS快递',
    'zhongtong'=>'中通快递',
    'huitong'=>'汇通快递',
    'shentong'=>'申通快递',
    'yuantong'=>'圆通快递',
    'gnxb'=>'邮政国内小包',
    'yunda'=>'韵达快递',
    'shunfeng'=>'顺丰快递',
    'debangwuliu'=>'德邦物流',
    'tiantian'=>'天天快递',
    'chengji'=>'城际快递',
    'aae'=>'AAE快递',
    'anjie'=>'安捷快递',
    'aoshuo'=>'奥硕物流',
    'aramex'=>'Aramex国际快递',
    'chengshi100'=>'城市100',
    'chuanxi'=>'传喜快递',
    'citylink'=>'CityLinkExpress',
    'coe'=>'东方快递',
    'datian'=>'大田物流',
    'dhl'=>'DHL快递',
    'disifang'=>'递四方速递',
    'dpex'=>'DPEX快递',
    'dsu'=>'D速快递',
    'ees'=>'百福东方物流',
    'eyoubao'=>'E邮宝',
    'fardar'=>'Fardar',
    'fedex'=>'国际Fedex',
    'feibao'=>'飞豹快递',
    'feihang'=>'原飞航物流',
    'fkd'=>'飞康达快递',
    'gdyz'=>'广东邮政物流',
    'gongsuda'=>'共速达物流|快递',
    'guotong'=>'国通快递',
    'haihong'=>'山东海红快递',
    'hebeijianhua'=>'河北建华快递',
    'henglu'=>'恒路物流',
    'huaqi'=>'华企快递',
    'huayu'=>'天地华宇物流',
    'hwhq'=>'海外环球快递',
    'jiaji'=>'佳吉快运',
    'jiayi'=>'佳怡物流',
    'jiayunmei'=>'加运美快递',
    'jiete'=>'捷特快递',
    'jinda'=>'金大物流',
    'jingguang'=>'京广快递',
    'jinyue'=>'晋越快递',
    'jixianda'=>'急先达物流',
    'jldt'=>'嘉里大通物流',
    'kangli'=>'康力物流',
    'kuaijie'=>'快捷快递',
    'kuayue'=>'跨越快递',
    'lejiedi'=>'乐捷递快递',
    'lianhaotong'=>'联昊通快递',
    'lijisong'=>'成都立即送快递',
    'longbang'=>'龙邦快递',
    'menduimen'=>'门对门快递',
    'mingliang'=>'明亮物流',
    'nengda'=>'港中能达快递',
    'ocs'=>'OCS快递',
    'quanchen'=>'全晨快递',
    'quanfeng'=>'全峰快递',
    'quanritong'=>'全日通快递',
    'quanyi'=>'全一快递',
    'rufeng'=>'如风达快递',
    'saiaodi'=>'赛澳递',
    'santai'=>'三态速递',
    'shengan'=>'圣安物流',
    'shengfeng'=>'盛丰物流',
    'shenghui'=>'盛辉物流',
    'suijia'=>'穗佳物流',
    'sure'=>'速尔快递',
    'tnt'=>'TNT快递',
    'ups'=>'UPS快递',
    'usps'=>'USPS快递',
    'weitepai'=>'微特派',
    'xinbang'=>'新邦物流',
    'xinfeng'=>'信丰快递',
    'xiyoute'=>'希优特快递',
    'yad'=>'源安达快递',
    'yafeng'=>'亚风快递',
    'yibang'=>'一邦快递',
    'yinjie'=>'银捷快递',
    'yousu'=>'优速快递',
    'ytfh'=>'北京一统飞鸿快递',
    'yuancheng'=>'远成物流',
    'yuefeng'=>'越丰快递',
    'yuntong'=>'运通中港快递',
    'zhaijisong'=>'宅急送快递',
    'zhengzhoujianhua'=>'郑州建华快递',
    'zhima'=>'芝麻开门快递',
    'zhongtian'=>'济南中天万运',
    'zhongtie'=>'中铁快运',
    'zhongxinda'=>'忠信达快递',
    'zhongyou'=>'中邮物流',
    'wanxiangwuliu'=>'万象物流',
    'yamaxun' => '亚马逊物流',
    'jd' => '京东物流',
    'ririshunwuliu' => '日日顺物流',
    'sxhongmajia' => '山西红马甲',
    'nanjingshengbang' => '晟邦物流',
    'pjbest' => '品骏快递',
    'feiyuanvipshop' => '飞远配送',
    'sccod' => '丰程物流'
	);


function ccjpt_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	//取自系统
	$result = $db->query("select dl.logistics_name as name, dlc.logistics_code as code " .
			" from dict_logistics_code dlc " .
			" left join dict_logistics dl using(logistics_type) " .
			" where dlc.platform_id = 4");
	foreach ($result as $k => $v){
		$companies[] = array
		(
				'shop_id' => $shop->shop_id,
				'logistics_code' => $v['code'],
				'name' => $v['name'],
				'created' => date('Y-m-d H:i:s', time())
		);
	}
	return true;
}

	function ccjpt_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);
	$ccjpt = new ccjptClient;
	$ccjpt->app_key = $appkey;
	$ccjpt->method = "sendGoods";
	$ccjpt->appsecret = $appsecret;

	$params = array(
		'shipping' => $trade->logistics_code,//如果物流同步失败，把这里改成logistics_name试试
		'invoice_no' => $trade->logistics_no,
		'order_sn' => $trade->tid
		);

	$result = $ccjpt->excute($params);

	if(API_RESULT_OK != ccjptErrorTest($result, $db, $trade->shop_id))
	{
		$error_msg = $result->msg;
		if (strpos($error_msg, '消息提醒失败') !== false) {
			set_sync_succ($db, $sid, $trade->rec_id);
			logx ( "ccjpt_sync_ok: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics' );
			return true;
		}
		set_sync_fail($db, $sid, $trade->rec_id, 2, $result->msg);
		logx ( "ccjpt_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$result->msg}", $sid.'/Logistics' );
		logx("ERROR $sid ccjpt_sync_logistics {$error_msg}",'error');
		return TASK_OK;
	}

	set_sync_succ($db, $sid, $trade->rec_id);
	logx ( "ccjpt_sync_ok: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics' );
	return true;
}





















?>