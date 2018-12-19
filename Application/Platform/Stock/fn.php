<?php

require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/fn/FnClient.class.php');

//库存同步
function fn_stock_syn(&$db,&$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	
	$client= new FnClient($appkey,$appsecret);
	$client->setAuthSession($stock->session);
	
	$method = "fn.item.sku.update";
	$data = array(
	    "sku_id"=>"{$stock->spec_id}",
	    "price"=>"",
	    "stock"=>"{$stock->syn_stock}",
	);
	$params = array(
	    'method'=> $method,
	    'params'=>@json_encode($data),
	);
	$retval = $client->sendDataByCurl($params);
	if(API_RESULT_OK != fnErrorTest($retval,$db,$stock->shop_id))
	{
		$error_msg = @$retval->error_msg;
		
		if(strpos(@$error_msg, "sku不存在") !== FALSE || strpos($error_msg, '查询不到商品') !== FALSE)
	    {
	        //对于这些错误, 不需再次同步, 可将其从match表中删掉
	        syn_delete($db, $stock);
	        logx("fn_stock_syn 同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->code} {$error_msg}", $sid.'/Stock');
	        	
	    }elseif(strpos(@$error_msg, "商品以删除或是待审核状态不能进行此操作") !== FALSE){
	        	
	        //停止同步，除非人工重新开启
	        syn_disable($db, $stock, $error_msg);
	        logx("fn_stock_syn 同步库存失败, 停止同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->code} {$error_msg}", $sid.'/Stock');
	    
	    }else{
	        	
	        //否则设置再次同步
	        logx("fn_stock_syn 同步库存失败,  下次重新同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id} ,SynStock: {$stock->syn_stock} 失败原因: {$retval->code} {$error_msg}", $sid.'/Stock');
	    }
	    
	    syn_log($db, $stock, 0, $error_msg);
	    
	    return SYNC_FAIL;
		
	}
	
	syn_log($db, $stock, 1, "");
	
	logx("fn_stock_syn success, NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid.'/Stock');
	
	return SYNC_OK;
}