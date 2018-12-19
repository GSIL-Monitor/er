<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoProductUpdateRequest.php');
require_once(ROOT_DIR.'/Common/api_error.php');


function top_fx_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$session = $stock->session;
	$shopId=$stock->shop_id;

	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	
	$req = new FenxiaoProductUpdateRequest();
	$req->setPid($stock->goods_id);
	if(!empty($stock->spec_id))
	{
		$req->setSkuIds($stock->spec_id);
		$req->setSkuQuantitys($stock->syn_stock);
	}
	else
	{
		$req->setQuantity($stock->syn_stock);
	}
	
	$retval = $top->execute($req, $session);
	
	//同步失败做以下操作:
	if(API_RESULT_OK != topErrorTest($retval,$db,$shopId))
	{
		if(@$retval->sub_code == "isv.invalid-parameter:product_pid"
			|| strpos(@$retval->error_msg, "SKU_NOT_FIND") !== FALSE) 
		{
			//对于这些错误, 不需再次同步, 可将其从match表中删掉
			syn_delete($db, $stock);
			logx("同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
		}
		else if(strpos(@$retval->error_msg, "分销平台代销商品，禁止改库存") !== FALSE || strpos(@$retval->error_msg, "活动期间") !== FALSE || strpos(@$retval->error_msg, "国际供应商暂不支持操作") !== FALSE )
		{
			syn_disable($db, $stock, @$retval->error_msg);
			logx("同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} TOP分销停止同步: {$retval->sub_code} {$retval->error_msg}", $sid . "/Stock");
		}
		else
		{
			//添加同步失败记录
			syn_log($db, $stock, 0, $retval->error_msg);
		}
		
		if(@$retval->sub_code == 'accesscontrol.limited-by-api-access-count' || 
			@$retval->sub_code == 'accesscontrol.limited-by-dynamic-access-count')
			return SYNC_QUIT;
		
		return SYNC_FAIL;
	}
	
	syn_log($db, $stock, 1, "");
	logx("淘宝分销同步库存成功: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid . "/Stock");
	
	return SYNC_OK;
}

?>