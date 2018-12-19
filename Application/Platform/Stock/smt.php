<?php
require_once(ROOT_DIR . "/Stock/utils.php");
require_once(ROOT_DIR . "/Manager/utils.php");
require_once(TOP_SDK_DIR . "/smt/SmtClient.php");

function smt_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock, $appkey, $appsecret);
	$shop_id = $stock->shop_id;

	if (empty($stock->spec_id))
	{
		syn_delete($db, $stock);
		logx("速卖通修改spec_id,之前的删除 goods_id: {$stock->goods_id}",$sid.'/Stock');
		return SYNC_FAIL;
	}

	$retval = smt::syncStock($appkey, $appsecret, $stock->session, $stock->goods_id, $stock->spec_id, $stock->syn_stock);
	//logx("121.199.165.165".print_r($retval,true) ,$sid);
	if (API_RESULT_OK != smtbabaErrorTest($retval, $db, $shop_id))
	{
		$error_msg = $retval->error_msg;
		syn_log($db, $stock, 0, $error_msg);
		logx("Smt同步库存失败, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
		return SYNC_FAIL;
	}

	syn_log($db, $stock, 1, "");
	logx("Smt同步库存成功: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid.'/Stock');
	return SYNC_OK;		
}



?>
