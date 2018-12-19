<?php

require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/mls/MeilishuoClient.php');

function meilishuo_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	
	if($stock->platform_id==20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $stock->session, 'xiaodian.item.skustock.update');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $stock->session, 'xiaodian.item.skustock.update');
	}
	

	$params = array(
		'skuId' =>$stock->spec_id, 
		'stock'=>$stock->syn_stock,
	);

	$retval = $mls->executeByPost($params);

	if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		
		if($retval->status->code == '1100400')
		{
			syn_delete($db, $stock);
			logx("美丽说同步库存失败,删除同步 shop_id: {$shopid}, goods_id: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid .'/Stock');
		
		}
		else
		{
			syn_log($db, $stock, 0, $error_msg);
			logx("美丽说同步库存失败, shop_id: {$shopid}, goods_id: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid .'/Stock');
		
		}
		
		
		return SYNC_FAIL;
	}
	
	syn_log($db, $stock, 1, "");
	
	logx("美丽说同步库存成功: shop_id: {$shopid}, iid: {$stock->goods_id}, OuterID: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid .'/Stock');
	
	return SYNC_OK;
}