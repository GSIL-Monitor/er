<?php
require_once (ROOT_DIR . '/Stock/utils.php');
require_once (ROOT_DIR . '/Manager/utils.php');
require_once (TOP_SDK_DIR . '/icbc/icbcApiClient.php');
function icbc_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret ( $stock, $appkey, $appsecret );
	
	// API参数
	$icbcApi=new icbcApiClient();
	$icbcApi->setApp_key($appkey);
	$icbcApi->setApp_secret($appsecret);
	$icbcApi->setAuth_code($stock->session);
	$icbcApi->setMethod("icbcb2c.storage.modify");
	
	$params = array();
	$params['products']['product']['product_sku_id']=$stock->spec_id;///
	$params['products']['product']['storage']=$stock->syn_stock;
	$retval = $icbcApi->sendByPost($params);
	
	if(API_RESULT_OK != icbcErrorTest($retval, $db, $stock->shop_id))
	{
		$error_msg['info'] = $retval['head']['ret_msg'];
		$error_msg['status'] = 0;					
		syn_log ( $db, $stock, 0, $error_msg );
		
		logx ( "ICBC同步库存失败, NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid . "/Stock" );
		return SYNC_FAIL;
	}
	
	syn_log ( $db, $stock, 1, "" );
	logx ( "ICBC同步库存成功: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid . "/Stock" );
	return SYNC_OK;
}




?>