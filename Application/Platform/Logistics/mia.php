<?php
include_once(TOP_SDK_DIR . '/mia/MiaClient.php');

function mia_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;


	$mia = new Mia();
	$mia->vendor_key = $appkey;
	$mia->secret_key = $appsecret;
	$mia->method = 'mia.logistics.get';
	$retval = $mia->execute();
	logx("mia_get_logistics_companies".print_r($retval,true) ,$sid.'/Logistics');
	if(API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
	{
		$error_msg['info'] = $retval->msg;
		$error_msg['status'] = 0;
		logx("mia_get_logistics_companies $shopid {$error_msg['info']}", $sid.'/Logistics', 'error');
		return false;
	}

	$logistics = $retval->content->logistic_response;
	foreach( $logistics as $company)
	{

		$companies[] = array
		(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $company->logistics_id,
			'name' => $company->name,
			'created' => date('Y-m-d H:i:s',time())
		);
	}
	return true;

}

function mia_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret ( $trade, $appkey, $appsecret );
	$shopid = $trade->shop_id;
	$logistics_code = $trade->logistics_code;

	$mia = new Mia();
	$mia->vendor_key = $appkey;
	$mia->secret_key = $appsecret;
	//先打单才能出库
	$mia->method = 'mia.order.confirm';
	$params = array(
			'order_id' => $trade->tid
		);
	$retval = $mia->execute($params);

	logx("mia_sync_confirm :".print_r($retval,true) ,$sid.'/Logistics');

	if(API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
	{

		set_sync_fail ( $db, $sid, $trade->rec_id, 2, $retval->msg );
		logx ( "WARNING $sid mia_sync_confirm_fail: tid {$trade->tid} error:{$retval->msg}",$sid.'/Logistics', 'error' );
		return false;
	}

	//出库
	$oid_info = $db->query_result("select GROUP_CONCAT(SUBSTR(oid,4)) as oid from api_trade_order where platform_id = {$trade->platform_id} and tid = '".$db->escape_string($trade->tid)."'");
	$mia->method = 'mia.order.deliver.upgrade';
	$sheet_code_info = array(
			array(
				'logistics_id' => $trade->logistics_code,
				'sheet_code' => $trade->logistics_no,
				'abroad_logistics_id' => '',
				'abroad_sheet_code' => ''
			)

		);
	$params = array(
			'order_id' => $trade->tid,
			'item_id' => $oid_info['oid'],
			'sheet_code_info' => json_encode($sheet_code_info)
		);
	$retval = $mia->execute($params);

	logx("mia_sync_logistics :".print_r($retval,true) ,$sid.'/Logistics');

	if(API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
	{

		set_sync_fail ( $db, $sid, $trade->rec_id, 2, $retval->msg );
		logx ( "WARNING $sid mia_sync_logistics: tid {$trade->tid}  logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->msg}",$sid.'/Logistics' );
		return false;
	}
	
	set_sync_succ ( $db, $sid, $trade->rec_id );
	logx ( "mia_sync_ok: tid {$trade->tid}", $sid .'/Logistics');
	
	return true;
}

?>