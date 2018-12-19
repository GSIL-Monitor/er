<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/kdt/KdtApiClient.php');
require_once(TOP_SDK_DIR . '/youzan/YZTokenClient.php');

function kdt_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock, $appkey, $appsecret);
	$shop_id = $stock->shop_id;
	$shop = getShopAuth($sid, $db, $shop_id);
	$session = $stock->session;
	//有赞新版上线通知授权失效--临时处理
	if($session == ''){
		markShopAuthExpired($db, $shop_id);
		releaseDb($db);
		logx("kdt_stock_syn shop not auth {$shop_id}!!", $sid);
		return SYNC_OK;
	}

	$params =array();

	$client = new YZTokenClient($session);
	$methodVersion = '3.0.0';

	if(!empty($stock->spec_id))
	{
		$method = 'youzan.item.sku.update';
		$params['sku_id'] = $stock->spec_id;
	}
	else
	{
		$method = 'youzan.item.update';
	}

	$params['item_id'] = $stock->goods_id;

	$params['quantity'] = $stock->syn_stock;

    $retval = $client->post($method, $methodVersion, $params);
	logx("kdt_stock_syn" . print_r($retval, true), $sid.'/Stock');
	if(API_RESULT_OK != kdtErrorTest($retval, $db, $shop_id))
	{
		if (40010 == @$retval['code'])
		{
			releaseDb($db);
			refreshKdtToken($appkey, $appsecret, $shop);
			$error_msg = $retval['error_msg'];
			return TASK_OK;
		}

		$error_msg = $retval['error_msg'];

		if ($error_msg == '商品不存在' || $error_msg == '找不到sku信息' || $error_msg == '商品库存不存在')
		{
			syn_delete($db, $stock);
			logx("同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
		}
		else
		{
			syn_log($db, $stock, 0, $error_msg);

			logx("口袋通同步库存失败, NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
		}

		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");

	return SYNC_OK;
}