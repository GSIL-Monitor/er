<?php

require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/rrd/RrdClient.php');


function rrd_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock, $appkey, $appsecret);
	$shopid = $stock->shop_id;

	$rrd = new RrdClient();
	$rrd->appid = $appkey;
	$rrd->secret = $appsecret;
	$rrd->access_token = $stock->session;
	$mode = "GET";
	if(empty($stock->spec_id))
	{
		$rrd->method = "weiba.wxrrd.goods.sku_edit";

		$params['stock'] = $stock->syn_stock;
	}
	else
	{
		$rrd->method = "weiba.wxrrd.product.sku_edit";	
		$sku[] = array(
			'skuid' => $stock->spec_id,
			'stock' => $stock->syn_stock
			);
		$skus = json_encode($sku);
		$params['skus'] = $skus;
	}
	$params['goods_id'] = $stock->goods_id;
	$retval = $rrd->execute($params, $mode);
	logx("rrd_stock_syn:".print_r($retval,true) ,$sid .'/Stock');
	if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shopid ))
	{
		if (30008 == intval(@$retval->errCode))
		{	
			releaseDb($db);
			refreshRrdToken($appkey, $appsecret, $trades);
			$error_msg = $retval->error_msg;
			return TASK_OK; 
		}
		$error_msg = $retval->error_msg;
		syn_log($db, $stock, 0, $error_msg);
		logx ( "人人店同步库存失败, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid .'/Stock');

		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("人人店同步库存成功, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock}" ,$sid.'/Stock');
	return SYNC_OK;
}



