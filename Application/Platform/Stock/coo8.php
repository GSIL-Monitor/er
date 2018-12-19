<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/coo8/Coo8Client.php');	

function coo8_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shop_id=$stock->shop_id;
	$shop = getShopAuth($sid, $db, $shop_id);
	
	$params = array(
				'venderId' => $shop->account_nick,
				'method' => 'coo8.item.quantity.update',
				'timestamp' => date('Y-m-d H:i:s', time()),
				'signMethod' => 'md5',
				'format' => 'json',
				'itemId'=>$stock->goods_id,
				'quantity'=>$stock->syn_stock
				);
	$coo8 = new Coo8Client();
	$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
	if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shop_id))
	{
		$error_msg = $retval->error_msg;
		if (strpos($error_msg, "你传入的库存值为0") !== FALSE)
		{
			syn_delay($db, $stock, 3600*24, $error_msg);
			logx("coo8同步库存失败,延时同步 NumIID: {$stock->goods_id},SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}  失败原因: {$error_msg}", $sid. "/Stock");
		}
		syn_log($db,$stock,0,$error_msg);
		logx("coo8同步库存失败,NumIID: {$stock->goods_id},SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}  失败原因: {$error_msg}", $sid. "/Stock");
	
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("Coo8同步库存成功: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid. "/Stock");
	
	return SYNC_OK;
	
}








