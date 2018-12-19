<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/zhe800/ZheClient.php');

$GLOBALS['zhe_logistics_name_map'] = array(
		'圆通'=>'圆通速递',
		'韵达'=>'韵达快运',
		'申通'=>'申通快递',
		'中通'=>'中通快递',
		'全峰'=>'全峰快递',
		'汇通'=>'百世汇通',
		'顺丰'=>'顺丰速运',
		'宅急送'=>'宅急送',
		'天天'=>'天天快递',
		'EMS经济'=>'EMS经济快递',
		'EMS'=>'EMS',
		'平邮'=>'包裹/平邮/挂号信',
		'邮政国内小包'=>'邮政国内小包',
		'邮政平邮'=>'邮政平邮',
		'快捷'=>'快捷快递',
		'邮政EMS'=>'邮政EMS速递',
		'中铁'=>'中铁快运',
		'中铁物流'=>'中铁物流',
		'中邮'=>'中邮物流',
		'德邦快递'=>'德邦快递',
		'德邦物流'=>'德邦物流',
		'天地华宇'=>'天地华宇',
		'佳吉'=>'佳吉物流',
		'远成'=>'远成物流',
		'平安达腾飞'=>'平安达腾飞',
		'安能'=>'安能物流',
		'腾达'=>'腾达物流',
		'航丰'=>'航丰物流',
		'欧姆讯'=>'欧姆讯物流',
		'德通'=>'联合德通',
		'路路通'=>'路路通物流',
		'广宁'=>'广宁货运',
		'路安达'=>'路安达货运',
		'昌隆'=>'昌隆物流',
		'龙之峰'=>'龙之峰物流',
		'城市之星'=>'城市之星物流',
		'电商中转'=>'电商中转',
		'龙佳'=>'龙佳物流',
		'呈龙'=>'呈龙物流',
		'宏驰'=>'宏驰物流',
		'国通'=>'国通快递',
		'优速'=>'优速物流'
);

function zhe_get_logistics_companies(&$db,&$shop,&$companies,&$error_msg)
{
	global $zhe_logistics_name_map;

	foreach($zhe_logistics_name_map as $code=>$name)
	{
		$companies[]=array
		(
				'shop_id'=>$shop->shop_id,
				'logistics_code'=>$code,
				'name'=>$name,
				'created'=>date('Y-m-d H:i:s',time())
		);
	}
	return true;
}

function  zhe_sync_logistics(&$db,&$trade,$sid)
{
	getAppSecret($trade, $appkey, $appsecret);

	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
        $error_msg = $retval->error_msg;
		logx("zhe800_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid . "/Logistics" );
		return false;
	}
	
	$access_token=$trade->session;
	
	$zhe=new Zhe800Client();
	
	$zhe->setApp_key($appkey);
	$zhe->setSession($access_token);
	$zhe->setMethod("orders/".$trade->tid."/deliver.json");
	
	$params=array();
	
	$params['express_company']=$trade->logistics_code;
	$params['express_no']=$trade->logistics_no;
	
	$retval=$zhe->executeByPost($params);
	logx(print_r($retval,true),$sid . "/Logistics");
	
	if(API_RESULT_OK != zheErrorTest($retval, $db, $trade->shop_id))
	{
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
       	logx("zhe800_sync_fail: tid {$trade->tid}, logistics_code {$trade->logistics_code}, logistics_no {$trade->logistics_no}, error:{$retval->error_msg}", $sid . "/Logistics");
	
		return false;
	}
	
	if($retval->data->is_success == 0)
	{
		$retval->error_msg = $retval->data->error_description;
		set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

        logx("zhe800_sync_fail2: tid {$trade->tid}, logistics_code {$trade->logistics_code}, logistics_no {$trade->logistics_no}, error:{$retval->error_msg}", $sid. "/Logistics" );
	
		return false;
	}
	
	set_sync_succ($db, $sid, $trade->rec_id);
	logx("zhe800_sync_ok: {$trade->tid}", $sid. "/Logistics");
	
	return true;
	
}
















