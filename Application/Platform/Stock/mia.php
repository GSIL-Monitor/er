<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/mia/MiaClient.php');

function mia_stock_syn(&$db,&$stock,$sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	
	$mia=new Mia();
	$mia->vendor_key = $appkey;
	$mia->secret_key = $appsecret;
	$mia->method = 'mia.update.sku.stock';
	$params=array();
	$params['sku_id']=$stock->goods_id;

	if (empty($stock->spec_id))
	{
		syn_delete($db, $stock);
		logx("蜜芽修改spec_id,之前的删除",$sid.'/Stock');
		return SYNC_FAIL;
	}
	else
	{
		$params['size'] = $stock->spec_id;
	}
	$params['quantity']=$stock->syn_stock;
	
	$retval=$mia->execute($params);
	
	if(API_RESULT_OK != miaErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;

		if ($error_msg == '您传递sku_id不是蜜芽分配的商品ID' 
			|| $error_msg == '商品库存信息不存在')
		{
			syn_delete($db, $stock);
			logx("蜜芽宝贝同步库存失败, 删除该同步记录: goods_id: {$stock->goods_id}, spec_name: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
		}
	
		syn_log($db, $stock, 0, $error_msg);
		
		logx("蜜芽宝贝同步库存失败, goods_id: {$stock->goods_id}, spec_name: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
		
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("蜜芽同步库存成功, goods_id: {$stock->goods_id}, spec_name: {$stock->spec_id}, SynStock: {$stock->syn_stock}",$sid.'/Stock');
	return SYNC_OK;
}