<?php
require_once (TOP_SDK_DIR . '/icbc/icbcApiClient.php');

$GLOBALS ['icbc_logistics_name_map'] = array(
	    '0000000571' =>'圆通快递',
	   	'0000000586' =>'DHL',
	    '0000000587' =>'EMS快递',
	    '0000000588' =>'FedEx',
	    '0000000589' =>'TNT',
	    '0000000590' =>'UPS',
		'0000000591' =>'华企快运',
		'0000000592' =>'德邦物流',
		'0000000593' =>'飞康达',
		'0000000594' =>'能达速递',
		'0000000595' =>'共速达',
		'0000000596' =>'急先达',
		'0000000597' =>'快捷速递',
		'0000000598' =>'联昊通',
		'0000000599' =>'龙邦快递',
		'0000000600' =>'全峰快递',
		'0000000601' =>'全日通',
		'0000000602' =>'全一快递',
		'0000000603' =>'如风达',
		'0000000604' =>'申通快递',
		'0000000605' =>'顺丰快递',
		'0000000606' =>'天地华宇',
		'0000000607' =>'天天快递',
		'0000000608' =>'优速快递',
		'0000000609' =>'韵达快递',
		'0000000610' =>'宅急送',
		'0000000611' =>'中铁快运',
		'0000000612' =>'中通快递',
		'0000000701' =>'自有物流',
		'0000000706' =>'百世汇通',
		'0000000711' =>'小红帽物流',
		'0000000721' =>'城际物流',
	    '0000000722' =>'国通快递',
	    '0000000726' =>'邮政小包',
	    '0000000736' =>'其他',
	    '0000000741' =>'安能物流',
	    '0000000746' =>'远成物流',
	    '0000000751' =>'居家通物流',
	    '0000000756' =>'黑猫宅急送',
	    '0000000761' =>'北京同城快递',
	    '0000000766' =>'速尔快递'
		
		
);

function icbc_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	global $icbc_logistics_name_map;
	
	foreach ( $icbc_logistics_name_map as $code => $name )
	{
		$companies [] = array(
				'shop_id' => $shop->shop_id,
				'logistics_code' => $code,
				'name' => $name,
				'created' => date ( 'Y-m-d H:i:s', time () )
		);
	}
	return true;
}

function icbc_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret ( $trade, $appkey, $appsecret );


	if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_code, $trade->logistics_no))
	{
		logx("icbc_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid . "/Logistics");
		return false;
	}
	
	// API参数
	$icbcApi=new icbcApiClient();
	$icbcApi->setApp_key($appkey);
	$icbcApi->setApp_secret($appsecret);
	$icbcApi->setAuth_code($trade->session);
	$icbcApi->setMethod("icbcb2c.order.sendmess");
	
	$params = array();
	$params['order_id']=$trade->tid;
	$params['logistics_company']=$trade->logistics_code; //物流公司
	$params['shipping_code']=$trade->logistics_no; //物流单号
	$params['shipping_time']=date ( 'Y-m-d H:i:s', time () );
	$retval = $icbcApi->sendByPost($params);
	if (API_RESULT_OK != icbcErrorTest ( $retval, $db, $trade->shop_id ))
	{
		set_sync_fail ( $db, $sid, $trade->rec_id, 2, $retval['error_msg'] );
	
		logx ( "icbc_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval['error_msg']}", $sid . "/Logistics" );
		logx ( "WARNING $sid icbc_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval['error_msg']}", $sid . "/Logistics" , 'error' );
		return false;
	}
	
	set_sync_succ ( $db, $sid, $trade->rec_id );
	logx ( "icbc_sync_ok: tid {$trade->tid}", $sid . "/Logistics" );
	
	return true;
}

?>