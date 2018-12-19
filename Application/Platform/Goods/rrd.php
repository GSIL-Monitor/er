<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/rrd/RrdClient.php');
//$mode:1 增量下载 2按货品名称下载 3按货品ID下载 4按时间段下载

function rrdDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$rrdApi = new RrdClient();
	$rrdApi->appid = $shop->key;
	$rrdApi->secret = $shop->secret;
	$rrdApi->method = "weiba.wxrrd.goods.lists";
	$rrdApi->access_token = $shop->session;
	$sid       = $shop->sid;
	$shop_id    = $shop->shop_id;
	$appkey    = $shop->key;
	$appsecret = $shop->secret;
	$type = "GET";
	$page = 0;
	$pageSize = 40;

	$params = array(
		'offset' => $page,
		'limit' => $pageSize
		);
	if ($mode == 2) {
		$params['keywords'] = $condition;
	}

	$retval = $rrdApi->execute($params,$type);
	if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id )) {
		if (30008 == intval(@$retval->errCode))
		{	
			releaseDb($db);
			refreshRrdToken($appkey, $appsecret, $shop);
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			return TASK_OK; 
		}
		$error_msg['status'] = 0;
		$error_msg['info'] = $retval->error_msg;
		logx ( "ERROR $sid rrdDownloadGoodsList fail errCode:{$retval->errCode}", $sid.'/Goods','error');
		return TASK_OK;
	}
	if ($retval->count == 0) {
		logx ( "rrdDownloadGoodsList $shop_id count: 0", $sid.'/Goods' );
		return true;
	}

	$data = $retval->data;
	$total = $retval->count;
	logx("rrdDownloadGoodsList $shop_id count: $total",$sid.'/Goods');
	if ($total <= count($data)) 
	{
		logx(" 不足一页， 只抓这一次即可", $sid.'/Goods');
		for ($i=0; $i < count($data); $i++) { 
			$ret = $data[$i];
			if(!rrdDownloadGoodsDetail($db,  $shop, $new_count, $chg_count, $error_msg, $ret->id)){
				return false;
			};
		}
	}
	else
	{
		$page = ceil(floatval($data)/$pageSize);
		logx(" 共发现 $page 页 ", $sid.'/Goods');
		for ($i=$page; $i > 0; $i--) { 
			logx("共{$page} 页, 当前第 {$i} 页",$sid.'/Goods');
			$params['offset'] = $i*$pageSize;
			$retval = $rrdApi->execute($params,$mode);
			if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id ))
			{
				if (30008 == intval(@$retval->errCode))
				{	
					releaseDb($db);
					refreshRrdToken($appkey, $appsecret, $shop);
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					return TASK_OK; 
				}
				$error_msg['status'] = 0;
				$error_msg['info'] = $retval->error_msg;
				logx ( "ERROR $sid rrdDownloadGoodsList {$retval->errCode}", $sid.'/Goods','error' );
				return TASK_OK;
			}
			$data = $retval->data;
			for ($i=0; $i < count($data); $i++) { 
				$ret = $data[$i];
				if(!rrdDownloadGoodsDetail($db,  $shop, $new_count, $chg_count, $error_msg, $ret->id))
				{
					return false;
				}
			}
		}
	}
	return true;
}

function rrdDownloadGoodsDetail(&$db, $shop, &$new_count, &$chg_count, &$error_msg, &$goods_id)
{

	$shop_id = $shop->shop_id;
	$sid = $shop->sid;
	$appkey    = $shop->key;
	$appsecret = $shop->secret;

	$rrdApi = new RrdClient();
	$rrdApi->appid = $shop->key;
	$rrdApi->secret = $shop->secret;
	$rrdApi->method = "weiba.wxrrd.goods.details";
	$rrdApi->access_token = $shop->session;
	$mode = "GET";

	$params = array('goods_id' => $goods_id);

	$retval = $rrdApi->execute($params, $mode);
	if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id )) {
		if (30008 == intval(@$retval->errCode))
		{	
			releaseDb($db);
			refreshRrdToken($appkey, $appsecret, $shop);
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			return TASK_OK; 
		}
		$error_msg['status'] = 0;
		$error_msg['info'] = $retval->error_msg;
		logx ( "ERROR $sid rrdDownloadGoodsDetail fail errCode: {$retval->errCode}", $sid.'/Goods','error' );
		return TASK_OK;
	}
	//$new_count++;
	$ret = $retval->data;

	if ($ret->onsale == 0) {//下架
		$status = 2;
	}elseif ($ret->onsale == 1) {//上架
		$status = 1;
	}
	$spec_list = array();
	$spec = array(
		'status' => $status,
		'platform_id' => 47,
		'goods_id' => $goods_id,//商品id
		'shop_id' => $shop_id,
		'goods_name' => $ret->title,//平台货品名称
		'price' => $ret->price,//平台售价，开启库存同步才准确
		'pic_url' => $ret->img,//图片url
		'stock_num' => $ret->stock,//平台库存量
		'outer_id' => '',//商家编码
		'spec_id' => '',//平台skuid
		'spec_name'=>'',//规格名称
		'spec_code' => '',
		'spec_outer_id' => '',
		'spec_sku_properties' => '',//平台sku属性串
		'is_stock_changed' => '1',//最后一次库存同步后，库存有没发生变化
		'created' =>date('Y-m-d H:i:s',time())
	);
	//规格
	$skus = $ret->products;
	if(empty($skus))
	{
		$spec['outer_id'] = $ret->goods_sn;
		$spec_list[] = $spec;
	}
	else
	{
		foreach($skus as $sku)
		{
			$nspec = $spec;
			$nspec['outer_id'] = $sku->product_sn;
			$nspec['spec_id'] = $sku->id;
			$nspec['spec_name'] = $sku->props_str;
			$spec_list[] = $nspec;
		}
	}
 	if(!putGoodsToDb($sid,$db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	return true;
}




