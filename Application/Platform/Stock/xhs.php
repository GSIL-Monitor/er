<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/xhs/XhsClient.php');

function xhs_stock_syn(&$db,&$stock,$sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	
	$xhs=new XhsClient();
	if (empty($stock->goods_id))
	{
		syn_delete($db, $stock);
		logx("小红书修改goods_id,之前的删除",$sid.'/Stock');
		return SYNC_FAIL;
	}
	else
	{
		$url = "/ark/open_api/v0/inventories/".$stock->goods_id;
	}
	$system_param['timestamp'] = time();
	$system_param['app-key'] = $appkey;
	$qty=$stock->syn_stock;
	
	@$retval = $xhs->stockByPost($url,$appsecret,$system_param,$qty);
	
	if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;

		if (@$retval->error_code == -8001)
		{
			syn_delete($db, $stock);
			logx("小红书同步库存失败, 删除该同步记录: goods_id: {$stock->goods_id},SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
		}
	
		syn_log($db, $stock, 0, $error_msg);
		
		logx("小红书同步库存失败, goods_id: {$stock->goods_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
		
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("小红书同步库存成功, goods_id: {$stock->goods_id}, SynStock: {$stock->syn_stock}",$sid.'/Stock');
	return SYNC_OK;
}