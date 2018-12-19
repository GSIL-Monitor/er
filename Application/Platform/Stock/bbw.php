<?php

require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/bbw/bbwClient.php');

function bbw_stock_syn(&$db,&$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$bbw= new bbwClient($appkey,$appsecret,$stock->session);
	$retval=$bbw->stock_sync($stock->goods_id,$stock->outer_id,$stock->syn_stock);
	
	if(API_RESULT_OK != bbwErrorTest($retval,$db,$stock->shop_id))
	{
		
		$error_msg = $retval->error_msg;

		if ($error_msg == '商品不存在')
		{
			syn_delete($db, $stock);
			logx("贝贝网同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
		}
		else
		{
			syn_log($db, $stock, 0, $error_msg);
			logx("贝贝网同步库存失败, iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
		}


		
		return SYNC_FAIL;
	}
	
	syn_log($db, $stock, 1, "");
	
	logx("贝贝网同步库存成功: iid: {$stock->goods_id}, OuterID: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid.'/Stock');
	
	return SYNC_OK;
}