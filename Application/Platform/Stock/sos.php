<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/sos/SosClient.php');

function sos_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	$sos = new SosClient();
		
	$sos->setAppKey($appkey);
	$sos->setAppSecret($appsecret);
	$sos->setAccessToken($stock->session);
	$sos->setAppMethod("suning.custom.inventory.modify");
	//配合订单修改 兼容处理
	if(empty($stock->goods_id)){
		$params['sn_request']['sn_body']['inventory']['productCode'] = $stock->spec_id;
	}else{
		$params['sn_request']['sn_body']['inventory']['productCode'] = $stock->goods_id;
	}
	$params['sn_request']['sn_body']['inventory']['destInvNum'] = strval($stock->syn_stock);
	$params = json_encode($params);

	$retval = $sos->execute($params);

	if(API_RESULT_OK != sosErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;

		if ($error_msg == 'biz.custom.inventory.invalid-biz:productCode')
		{
			syn_delete($db, $stock);
			logx("sos同步库存失败, 删除该同步记录: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid. "/Stock");
		}
		else
		{
			syn_log($db, $stock, 0, $error_msg);
			logx("Sos同步库存失败, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid. "/Stock");
		}
		
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("Sos同步库存成功: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid. "/Stock");
	
	return SYNC_OK;
}

?>
