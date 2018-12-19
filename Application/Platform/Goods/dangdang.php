<?php

require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/dangdang/DangdangClient.php');
require_once(ROOT_DIR . '/Stock/dangdang.php');


//dangdang下载货品列表
function ddDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;

	$start_time = $condition;
	$end_time = time();
	
	logx("ddDownloadGoodsList shopid: $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . " end_time: " . 
		date('Y-m-d H:i:s', $end_time), $sid.'/Goods');

	//dd
	$dd = new DangdangClient(DD_NEW_API_URL);

	$dd->setAppKey($appkey);
	$dd->setAppSecret($appsecret);
	//$dd->setMethod('dangdang.items.list.get');
	$dd->setSession($session);
	$params = array();

	//获取店铺类型  百货/出版
	$dd_shop_type = $db->query_result_single("select prop1 from cfg_shop where shop_id = {$shopId}");
	
	if($dd_shop_type == "")
	{
		$shop->appsercret = $appsecret;
		if(ddGetShopType($db, $shop, $dd,$sid,$type))
		{
			$shop->prop1 = $type;
			if(!$db->execute("update cfg_shop set prop1='".$shop->prop1."' where shop_id=".$shopId))
			{
				logx("当当下载货品时获取商家类型保存失败.类型：".$shop->prop1, $sid.'/Goods');
			}
		}
		else
		{
			logx("当当下载货品时获取商家类型失败", $sid.'/Goods');
		}
	}


	if($dd_shop_type == 2 || (isset($shop->prop1)&&$shop->prop1 == 2))  //1百货 2出版
	{
		$dd->setV('3.0');
		logx("当当下载出版类货品 ShopID: {$shopId}", $sid.'/Goods');
		
	}
	else
	{
		$dd->setV('1.0');
		logx("当当下载百货类货品 ShopID: {$shopId}", $sid.'/Goods');
	}
	
	
	if(!ddDownloadGoodsListImpl($db, $sid, $shop, $shop->secret,$shop->session, $mode, $condition,$dd, $params, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	
	return true;
}



function loadGoodsDetailImpl($shopId, &$item, &$spec_list)
{
	$spec = array
	(
		'status' => (trim($item->itemState) == '上架')?1:2,
		'platform_id' => 7,
		'shop_id' => $shopId,
		'goods_id' => trim((string)$item->itemID),
		'outer_id' => trim($item->outerItemID),
		'goods_name' => trim($item->itemName),
		'price' => (float)$item->unitPrice,
		'stock_num' => (int)$item->stockCount,
		'pic_url' => trim($item->pic1),
		'spec_id' => '',
		'spec_code' => '',
		'spec_name' => '',
		'spec_outer_id' => '',
		'is_stock_changed' => '1',
		'created' =>date('Y-m-d H:i:s',time())
	);
	$skus =$item->SpecilaItemInfo;
	if(empty($item->SpecilaItemInfo))
	{
		$spec_list[] = $spec;
	}
	else
	{
		$skus =$item->SpecilaItemInfo;
		///$prop_imgs = @$item->itemPic;
		
		foreach($skus as $sku)
		{
			$nspec = $spec;
			$nspec['spec_id'] = @$sku->subItemID;
			$nspec['spec_code'] =trim(@$sku->specialAttributeClass);
			$nspec['spec_name'] = trim(@$sku->specialAttribute);
			$nspec['spec_outer_id'] = trim(@$sku->outerItemID);
			$nspec['price'] = @$sku->unitPrice;
			$nspec['stock_num'] = @$sku->stockCount;
			$nspec['pic_url'] = @$sku->itemPic;
			
			$spec_list[] = $nspec;
		}
	}
		
	
	return true;
}





function downDdGoodsDetail($sid, $shopId,&$db, &$dd,$appsecret,$session, &$num_iids, &$spec_list,&$new_count, &$chg_count, &$error_msg)
{
	$dd->setMethod('dangdang.item.get');
	$dd->setV('1.0');

	for($i=0; $i<count($num_iids); $i++)
	{
		$params['it'] = $num_iids[$i];
	    $retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
	    if(empty($retval))
	    {
	    	continue;
	    }
		if(API_RESULT_OK != ddErrorTest($retval,$db,$shopId))
		{
			logx("ddDownloadGoodsList dd->sendByPost fail goods_id:$num_iids[$i]", $sid.'/Goods');
			return false;
		}
		$item=$retval->ItemDetail;
		if(!loadGoodsDetailImpl($shopId, $item, $spec_list))
			continue;
	
	}
	
	//超过100条写一次库
	if(count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
		
	return true;
}

function ddDownloadGoodsListImpl($db, $sid, $shop, $appsecret,$session, $mode, $condition,&$dd, &$params, &$new_count, &$chg_count, &$error_msg)
{
	$shopId = $shop->shop_id;

	$start_time = $condition;
	$end_time = time();
	$params['mts'] = date('Y-m-d', $start_time);
	$params['mte'] = date('Y-m-d', $end_time);

	
	$spec_list = array();
	$api_count = 0;
	$result = splitTime($start_time, $end_time, 3600*24, function($from_time, $to_time) use(&$db, &$params, &$dd, &$error_msg, &$spec_list, $session, $sid, $shop, $shopId, $appsecret,&$new_count, &$chg_count,&$api_count)
	{
		$api_count++;
		if($api_count >= 100){
			sleep(30);
			$api_count = 0;
		}
		if($from_time && $to_time)
		{
		    $params['mts'] = date('Y-m-d', $from_time);
		    $params['mte'] = date('Y-m-d', $to_time);
		}
		$params['its'] = 9999;	// 商品状态,全部
	    $params['pageSize'] = 20;

		if(isset($shop->prop1)&&$shop->prop1 == 2)  //1百货 2出版
		{
			$dd->setMethod('dangdang.item.search');
		}
		else
		{
			$dd->setMethod('dangdang.items.list.get');
		}
		$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
	
		if(API_RESULT_OK != ddErrorTest($retval,$db,$shopId))
		{
			$error_msg['info'] = (string)$retval->error_msg;
			$error_msg['status'] = 0;
			logx("ddDownloadGoodsList dd->execute fail count:{$api_count}  错误信息：{$error_msg['info']}", $sid.'/Goods');
			return false;
		}
			$trades = $retval->totalInfo->itemsCount;
			
		//总条数
		$total_results = intval($trades);
		if($total_results ==0)
		{
			logx("total_results : $total_results ", $sid.'/Goods');
			return true;
		}
		logx("total_results : $total_results ", $sid.'/Goods');

		//获取店铺类型  百货/出版
		$dd_shop_type = $db->query_result_single("select prop1 from cfg_shop where shop_id = {$shopId}");
		if($dd_shop_type == 2)  //1百货 2出版
		{
			if(!isset($retval->items) || count($retval->items) == 0)
			{
				return true;
			}


			$Item = $retval->items->xpath('item');
			//如果不足一页，则不需要再抓了
			if($total_results <= 20)
			{
				$num_iids = array();
				foreach($Item as $t) $num_iids[] = $t->item_id;
				if(!downDdCbGoodsDetail($sid, $shopId, $db, $dd,$appsecret,$session, $num_iids, $spec_list, $new_count, $chg_count, $error_msg))
					return false;
			}
			else //超过一页，第一页抓的作废，从最后一页开始抓
			{
				$total_pages = ceil(floatval($total_results)/20);
				logx("total_page $total_pages",$sid.'/Goods');
				for($i=$total_pages; $i>=1; $i--)
				{
					logx("page $i ", $sid.'/Goods');
					$dd->setMethod('dangdang.item.search');
					$params['p'] = $i;
					$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
					$Item = $retval->items->xpath('item');
					if(API_RESULT_OK != ddErrorTest($retval,$db, $shopId))
					{
						logx("ddDownloadGoodsList dd->execute fail2 ", $sid.'/Goods');
						return false;
					}

					$num_iids = array();
					foreach($Item as $t) $num_iids[] = $t->item_id;
					if(!downDdCbGoodsDetail($sid, $shopId, $db, $dd, $appsecret,$session, $num_iids, $spec_list, $new_count, $chg_count, $error_msg))
						return false;
				}
			}
		}
		else
		{
			if(!isset($retval->ItemsList) || count($retval->ItemsList) == 0)
			{
				return true;
			}

			//$Items = $retval->ItemsList->ItemInfo;
			$Items = $retval->ItemsList->xpath('ItemInfo');
			//如果不足一页，则不需要再抓了
			if($total_results <= 20)
			{	
				$num_iids = array();	
				foreach($Items as $t) $num_iids[] = $t->itemID;
				if(!downDdGoodsDetail($sid, $shopId, $db, $dd,$appsecret,$session, $num_iids, $spec_list, $new_count, $chg_count, $error_msg))
				return false;
			}
			else //超过一页，第一页抓的作废，从最后一页开始抓
			{
				$total_pages = ceil(floatval($total_results)/20);
				logx("total_page $total_pages", $sid.'/Goods');
				for($i=$total_pages; $i>=1; $i--)
				{
					logx("page $i ", $sid.'/Goods');
					$dd->setMethod('dangdang.items.list.get');
					$params['p'] = $i;
					$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
					//$Items = $retval->ItemsList->ItemInfo;
					$Items = $retval->ItemsList->xpath('ItemInfo');
					if(API_RESULT_OK != ddErrorTest($retval,$db, $shopId))
					{
						logx("ddDownloadGoodsList dd->execute fail2 ", $sid.'/Goods');
						return false;
					}
					
					$num_iids = array();	
					foreach($Items as $t) $num_iids[] = $t->itemID;
					if(!downDdGoodsDetail($sid, $shopId, $db, $dd, $appsecret,$session, $num_iids, $spec_list, $new_count, $chg_count, $error_msg))
					return false;
				}
			}
		}
		
		if($to_time || count($spec_list) == 0)
		{
			$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$to_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
		}
		
		return true;
	});
	
	//保存数据
	if((count($spec_list) == 0 || putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) &&$result &&$end_time)
	{
		$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}
	
	return $result;
}


function downDdGoodsDetailById($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$spec_list = array();
	
	$dd = new DangdangClient(DD_NEW_API_URL);
	
	$dd->setAppKey($appkey);
	$dd->setAppSecret($appsecret);
	$dd->setMethod('dangdang.item.get');
	$dd->setSession($session);
	$params = array();
	
	//获取店铺类型  百货/出版
	$dd_shop_type = $db->query_result_single("select prop1 from cfg_shop where shop_id = {$shopId}");
	if($dd_shop_type == "")
	{
		$shop->appsercret = $appsecret;
		if(ddGetShopType($db, $shop, $dd,$sid,$type))
		{
			$shop->prop1 = $type;
			if(!$db->execute("update cfg_shop set prop1='".$shop->prop1."' where shop_id=".$shopId))
			{
				logx("当当下载货品时获取商家类型保存失败.类型：".$shop->prop1, $sid.'/Goods');
			}
		}
		else
		{
			logx("当当下载货品时获取商家类型失败", $sid.'/Goods');
		}
		
	}


	if($dd_shop_type == 2 || (isset($shop->prop1)&&$shop->prop1 == 2))  //1百货 2出版
	{
		$dd->setV('3.0');

		logx("当当下载出版类货品 ShopID: {$shopId},goods_id:{$condition}", $sid.'/Goods');

		$params['item_id'] = $condition;
		$params['gShopID'] = $shopId;
		$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);

		if(API_RESULT_OK != ddErrorTest($retval,$db,$shopId))
		{
			logx("downDdGoodsDetailById dd->sendByPost fail ", $sid.'/Goods');
			return false;
		}
		$item=$retval->item;
		if(!loadCbGoodsDetailImpl($shopId, $item, $spec_list))
			return false;
	}
	else
	{
		$dd->setV('1.0');

			logx("当当下载百货类货品 ShopID: {$shopId},goods_id:{$condition}", $sid.'/Goods');

			$params['it'] = $condition;
			$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);

		if(API_RESULT_OK != ddErrorTest($retval,$db,$shopId))
		{
			logx("downDdGoodsDetailById dd->sendByPost fail ", $sid.'/Goods');
			return false;
		}

			$item=$retval->ItemDetail;
		if(!loadGoodsDetailImpl($shopId, $item, $spec_list))
			return false;
	}
	
	if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
		
	return true;
}



function downDdCbGoodsDetail($sid, $shopId,&$db, &$dd,$appsecret,$session, &$num_iids, &$spec_list,&$new_count, &$chg_count, &$error_msg)
{
	$dd->setMethod('dangdang.item.get');

	for($i=0; $i<count($num_iids); $i++)
	{
		$params['item_id'] = $num_iids[$i];
		$params['gShopID'] = $shopId;
		$retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);

		if(API_RESULT_OK != ddErrorTest($retval,$db,$shopId))
		{
			logx("ddDownloadGoodsList dd->sendByPost fail goods_id:$num_iids[$i]", $sid.'/Goods');
			return false;
		}
		$item=$retval->item;
		if(!loadCbGoodsDetailImpl($shopId, $item, $spec_list))
			continue;
	
	}
	
	//超过100条写一次库
	if(count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
		
	return true;
}

function loadCbGoodsDetailImpl($shopId, &$item, &$spec_list)
{
	$spec_name = $item->item_name.'_'.@$item->standard_attribs;
	$spec = array
	(
		'status' => (trim($item->status) == 0)?1:2,
		'platform_id' => 7,
		'shop_id' => $shopId,
		'goods_id' => trim((string)$item->item_id),
		'outer_id' => trim($item->out_id),
		'goods_name' => iconv_substr((string)$item->item_name,0,100,'UTF-8'),
		'price' => (float)$item->sale_price,
		'stock_num' => (int)$item->stock,
		'pic_url' => trim(@$item->pic_urls->pic_url[0]),
		'spec_id' => '',
		'spec_code' => '',
		'spec_name' => iconv_substr((string)$spec_name,0,100,'UTF-8'),
		'spec_outer_id' => '',
		'is_stock_changed' => '1',
		'created' =>date('Y-m-d H:i:s',time())
	);

	$spec_list[] = $spec;		
	
	return true;
}

?>
