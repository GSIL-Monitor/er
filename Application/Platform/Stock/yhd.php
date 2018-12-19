<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/yhd/YhdClient.php');

function yhd_stock_syn(&$db, &$stock, $sid)
{
	//API系统参数
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	
	$params = array();
	$params['format'] = "json";
	$params['ver'] = "1.0";
	$params['appKey'] = $appkey;
	$params['sessionKey'] = $stock->session;
	$params['timestamp'] = date("Y-m-d H:i:s");
	$url = YHD_API_URL;

	$params['productId'] = $stock->goods_id;
	$params['virtualStockNum'] = $stock->syn_stock;
	$params['updateType'] = 1;
	
	$params['method'] = "yhd.product.stock.update";
	
	$yhd = new YhdClient();
	
	$retval = $yhd->sendByPost($url, $params, array(), $appsecret);
	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		
		if(strpos(@$retval->error_msg, "指定的产品信息不存在") !== FALSE)
		{
			syn_delete($db, $stock);
		}
		else if(strpos(@$retval->error_msg, "库存数值超出范围") !== FALSE)
		{
			syn_delay($db, $stock, 3600*12, @$retval->error_msg);
		}
		else
		{
			
			syn_log($db, $stock, 0, $error_msg);
		}
		
		logx("YHD同步库存失败, NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid . "/Stock");
		
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	
	return SYNC_OK;
}

?>