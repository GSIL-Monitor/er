<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/mls/MeilishuoClient.php');

function meilishuoDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	$page = 1;
	$page_size = 50;
	$spec_list = array();
	
	if((int)$shop->platform_id == 20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $sessionkey, 'xiaodian.item.onsale.get');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $sessionkey, 'xiaodian.item.onsale.get');
	}
	

	$params = array(
		'isShelf' =>0, // 0表示上架，1表示下架
		'pageSize'=>$page_size,
		'page'=>$page,
	);

	$retval = $mls->executeByPost($params);

	if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		logx("meilishuoDownloadGoodsList fail: $error_msg", $sid. "/Goods");
		return false;
	}

	if(!isset($retval->result->data->total) || $retval->result->data->total == 0)
	{
		logx("meilishuoDownloadGoodsList $shopid count: 0", $sid. "/Goods");
		return true;
	}

	$items = $retval->result->data->items;
	//总条数
	$total_results = $retval->result->data->total;
	logx("meilishuoDownloadGoodsList shopid: $shopid count : $total_results",$sid. "/Goods");
	
	//不足一页
	if($total_results <= $page_size)
	{
		logx("不足一页，只抓一次即可",$sid. "/Goods");
		$num_iids = array();
		foreach($items as $t) $num_iids[] = $t->itemId;
		if(!downMeilishuoGoodsDetail($db,$shop,$num_iids,$spec_list,$new_count,$chg_count,$error_msg))
		{
			return false;
		}
		
	}
	else//超过一页
	{
		$total_pages = ceil(floatval($total_results)/$page_size);
		logx("共 {$total_pages} 页",$sid. "/Goods");
		
		for($i = $total_pages - 1; $i > 0; $i--)
		{
			logx("准备抓第 {$i} 页",$sid. "/Goods");
			$params['page'] = $i;
			$retval = $mls->executeByPost($params);
			
			if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $shopid))
			{
				$error_msg = $retval->error_msg;
				logx("meilishuoDownloadGoodsList  fail2 ,$error_msg", $sid. "/Goods");
				return false;
			}
			
			$items = $retval->result->data->items;
			
			
			foreach($items as $t) $num_iids[] = $t->itemId;
			if(!downMeilishuoGoodsDetail($db,$shop,$num_iids,$spec_list,$new_count,$chg_count,$error_msg))
			{
				return false;
			}
			
		}
	}
	//保存数据
	if(count($spec_list) > 0)
	{
		if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
		{
			return false;
		}
	}
	return true;
	
}

function downMeilishuoGoodsDetail($db,$shop,$num_iids,&$spec_list, &$new_count,&$chg_count,&$error_msg)
{
	$sid=$shop->sid;
	if(empty($num_iids)){
		logx('downMeilishuoGoodsDetail goods is empty', $sid. "/Goods");
		return true;
	}
	$shopid=$shop->shop_id;
	$appkey=$shop->key;
	$appsecret=$shop->secret;
	$access_token=$shop->session;
	for($i=0;$i<count($num_iids);$i++)
	{
		if((int)$shop->platform_id == 20)
		{
			$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $access_token, 'xiaodian.item.get');
		}
		else
		{
			$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $access_token, 'xiaodian.item.get');
		}
		
		
		$params = array(
			'itemId' =>$num_iids[$i],
		);

		$retval = $mls->executeByPost($params);
		if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $shopid))
		{
			logx("downMeilishuoGoodsDetail fail ", $sid. "/Goods");
			return false;
		}
		
		$item = $retval->result->data;
		if(!loadMeilishuoGoodsImpl($db, $shop,$spec_list, $new_count, $chg_count, $error_msg, $item))
		{
			continue;
		}
	}
	//超过100条写一次库
	if(count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	return true;
}

function loadMeilishuoGoodsImpl(&$db, $shop,&$spec_list, &$new_count, &$chg_count, &$error_msg, &$item)
{
	$shopid = $shop->shop_id;
	$sid = $shop->sid;
	$platform_id = (int)$shop->platform_id;
	
	
		
	$goods_id = $item->itemId;
	$price = $item->price;
	$title = $item->title;
	$num = $item->stock;
	$outer_id = $item->code;
	
	$spec = array
	(
		'status' => 1,
		'platform_id' => $platform_id,
		'shop_id' => $shopid,
		'goods_id' => trim($goods_id),
		'outer_id' => trim($outer_id),
		'goods_name' => trim($title),
		'price' => $price,
		'stock_num' => $num,
		'pic_url' => $item->image,
		'spec_id' => '',
		'spec_code' => '',
		'spec_name' => '',
		'spec_outer_id' => '',
		'is_stock_changed' => '1',
		'spec_sku_properties' => '',
		'created' =>date('Y-m-d H:i:s',time())
	);
	
	//规格
	$skus = $item->skus;
	if(empty($skus))
	{
		$spec_list[] = $spec;
	}
	else
	{
		foreach($skus as &$sku)
		{
			$specName = '';
			for($j = 0; $j < count($sku->attrs); $j++)
			{
				$specName.= $sku->attrs[$j]->name.":".$sku->attrs[$j]->value." ";
			}
			
			$nspec = $spec;
			$nspec['spec_id'] = $sku->skuId;
			$nspec['spec_code'] =trim(@$sku->code);
			$nspec['spec_name'] = $specName;
			$nspec['spec_outer_id'] = trim(@$sku->code);
			$nspec['price'] = $sku->price;
			$nspec['stock_num'] = $sku->stock;
			
			$spec_list[] = $nspec;
		}
	}
		
		
	return true;
}

function downMeilishuoGoodsDetailById($db,$shop,$mode,$condition,&$new_count,&$chg_count,&$error_msg)
{
	$sid=$shop->sid;
	$shopid=$shop->shop_id;
	$appkey=$shop->key;
	$appsecret=$shop->secret;
	$access_token=$shop->session;
	$spec_list = array();
	
	if((int)$shop->platform_id == 20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $access_token, 'xiaodian.item.get');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $access_token, 'xiaodian.item.get');
	}
	
	
	$params = array(
		'itemId' =>trim($condition),
	);

	$retval = $mls->executeByPost($params);
	if(API_RESULT_OK != meilishuoErrorTest($retval, $db, $shopid))
	{
		logx("downMeilishuoGoodsDetailById fail ", $sid. "/Goods");
		return false;
	}
	
	$item = $retval->result->data;
	if(!loadMeilishuoGoodsImpl($db, $shop,$spec_list, $new_count, $chg_count, $error_msg, $item))
	{
		//continue;
		return false;
	}
	
	
	if(count($spec_list) >0 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	return true;
}






