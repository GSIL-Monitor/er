<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/zhe800/ZheClient.php');

function zhe_stock_syn(&$db,&$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);

	$params = array();
	
	$zhe=new Zhe800Client();
	$zhe->setApp_key($appkey);
	$zhe->setSession($stock->session);
	$zhe->setMethod('inventories/update.json');

	$params['product_id']=$stock->goods_id;
	$params['update_type']=0;
	if(!empty($stock->spec_id))
	{
			$stock_sku_id = $stock->goods_id."_".$stock->spec_id;
	}
	else
	{
			$stock_sku_id = $stock->goods_id."_000000";
	}

	$params['stock_items']=json_encode(array(array(
		'sku_id'=>$stock_sku_id,
		'stock'=>$stock->syn_stock
	)));

	$retval = $zhe->executeByPost($params);
	if(API_RESULT_OK != zheErrorTest($retval,$db,$stock->shop_id))
	{
		$error_msg = $retval->error_msg;
        if(strpos(@$retval->error_msg, "product_id cannot be empty") !== FALSE)
		{
			syn_delete($db, $stock);
			logx("折800同步库存失败,删除该同步记录： iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}",  $sid.'/Stock');
		
		}
		else
		{
			logx("折800同步库存失败, iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}",  $sid.'/Stock');
		
		}
                
		syn_log($db, $stock, 0, $error_msg);
		
		return SYNC_FAIL;
	}

	logx("zhe800 库存同步返回信息： ".print_r($retval, true),$sid.'/Stock');
	if(isset($retval->data->status) && $retval->data->status > 0){
		$status = $retval->data->status;
		$msg = $retval->data->message;

		logx("折800库存同步失败，错误码: {$status} 原因: {$msg}", $sid.'/Stock');
		if($status==1026)//商品不存在情况，删除同步记录
		{
			syn_delete($db, $stock);
			logx("折800商品不存在,删除该同步记录： iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock} ",  $sid.'/Stock');
		}

		syn_log($db, $stock, 0, $msg);

		return SYNC_FAIL;
	}



	syn_log($db, $stock, 1, "");
	
	logx("折800同步库存成功: iid: {$stock->goods_id}, OuterID: {$stock->outer_id} , spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock}",  $sid.'/Stock');
	
	return SYNC_OK;
}









































