<?php
require_once(ROOT_DIR . '/Goods/utils.php');

require_once(TOP_SDK_DIR . '/yhd/YhdClient.php');

function yhdGetPicUrlById(&$db,$appKey, $appsecret, $shop, $productId)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	
	//API系统参数
	$params = array();
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	$params['timestamp'] = date('Y-m-d H:i:s',time());
	$params['method'] = "yhd.general.products.search";
	$params['productIdList'] = $productId;
	
	$yhd = new YhdClient();
	$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
		
	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		logx("yhdGetPicUrlById yhd->sendByPost fail,error_msg: {$error_msg}", $sid . "/Goods");
		return 0;
	}
	
	if(!isset($retval->productList->product) || count($retval->productList->product) == 0)
	{
		logx("yhdGetPicUrlById $shopid count: 0", $sid . "/Goods");
		return true;
	}
	
	return yhdGetPicUrl($retval->productList->product[0]->prodImg);
}


function yhdGetProductPrice(&$db,$appKey, $appsecret, $shop, $productIds)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$sessionkey = $shop->session;
	$price_list = array();
	
	//API系统参数
	$params = array();
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";
	$params['timestamp'] = date('Y-m-d H:i:s',time());
	$params['method'] = "yhd.products.price.get";
	$yhd = new YhdClient();
	while (count($productIds) > 0)
	{
		$arr = array_splice($productIds, 0, 10);
		$productId_merge = implode(',', $arr);
	
		$params['productIdList'] = $productId_merge;
		
		$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
		if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->error_msg;
			logx("yhdGetProductPrice yhd->sendByPost fail,error_msg:{$error_msg}", $sid . "/Goods");
			return 0;
		}
		
		if(!isset($retval->pmPriceList) || count($retval->pmPriceList) == 0)
		{
			logx("yhdGetProductPrice $shopid count: 0", $sid . "/Goods");
			return true;
		}

		$price_list = array_merge($price_list,$retval->pmPriceList->pmPrice);
	}
	return $price_list;
}

function yhdGetStockCount(&$db,$appKey, $appsecret, $shop, $productIds)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$sessionkey = $shop->session;
	$num_list = array();
	//API系统参数
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	$params['timestamp'] = date('Y-m-d H:i:s',time());
	$params['method'] = "yhd.products.warehouse.stocks.get";
	$num_list = array();
	while (count($productIds) > 0)
	{
		$arr = array_splice($productIds, 0, 10);
		$productId_merge = implode(',', $arr);
	
		$params['productIdList'] = $productId_merge;
		
		$yhd = new YhdClient();
		$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
		if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->error_msg;
			logx("yhdGetStockCount yhd->sendByPost fail,error_msg:{$error_msg}", $sid . "/Goods");
			return 0;
		}
		
		if(!isset($retval->pmStockList) || count($retval->pmStockList) == 0)
		{
			logx("yhdGetStockCount $shopid count: 0", $sid . "/Goods");
			return true;
		}

		$num_list = array_merge($num_list,$retval->pmStockList->pmStock);
	}
	//$warehouseId=$retval->pmStockList->warehouseId;

	return $num_list;
}

function yhdGetPicUrl($prodImg)
{
	$images = explode(',', $prodImg);
	
	for ($i = 0; $i < count($images); ++$i)
	{
		$image_info = explode('|', $images[$i]);
		
		// 返回主图
		if (3 == count($image_info) && 1 == $image_info[2])
		{
			return $image_info[1];
		}
	}
	
	return '';
}

//yhd下载货品列表
function yhdDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$total_count = 0;
	yhdDownloadGeneralGoodsList($db, $shop, $mode, $condition, $new_count, $chg_count, $error_msg);
	yhdDownloadSerialGoodsList($db,  $shop, $mode, $condition, $new_count, $chg_count, $error_msg);

	return true;
}

//yhd下载普通货品
function yhdDownloadGeneralGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appKey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	
	
	logx("yhdDownloadGeneralGoodsList shopid: $shopid ", $sid . "/Goods");
	
	//是否采用商家编码拼接的方式
	//$sql = "select CfgValue from g_cfg_sys where CfgKey='apitrade_bConcatGoodsSkuID' ";
	//$bConcatGoodsSkuID = $db->query_result_single($sql);
	
	//API系统参数,目前不支持开始时间和结束时间的设置
	$params = array();
	
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	$params['timestamp'] = date('Y-m-d H:i:s',time());
	
	$params["verifyFlg"] = 2;
	$params["pageRows"] = 20;
	$params['curPage'] = 1;
	$params['canSale'] = 1;
	
	$params['method'] = "yhd.general.products.search";

	if ($mode == 1)
	{
		$start_time = $condition;
		$end_time = time();
		$params['updateStartTime'] = date("Y-m-d H:i:s",$start_time);
		$params['updateEndTime'] = date("Y-m-d H:i:s",$end_time);
		logx("yhdDownloadGeneralGoodsList mode:{$mode} start_time:".date("Y-m-d H:i:s",$start_time)." end_time:".date("Y-m-d H:i:s",$end_time),$sid . "/Goods");
	}
	elseif ($mode == 2)
	{
		$params['productCname'] = $condition;
		logx("yhdDownloadGeneralGoodsList mode:{$mode} title:{$condition}",$sid . "/Goods");
	}
	elseif ($mode == 3)
	{
		$params['productIdList'] = $condition;
		logx("yhdDownloadGeneralGoodsList mode:{$mode} goods_id:{$condition}",$sid . "/Goods");
	}
	elseif ($mode == 4)
	{
		$time=explode(',',trim($condition));
		$start_time=strtotime($time[0]);
		$end_time=strtotime($time[1]);
		$now=time();
		if($end_time>=$now)
		{
			$end_time=$now;
		}
		$params['updateStartTime'] = date("Y-m-d H:i:s",$start_time);
		$params['updateEndTime'] = date("Y-m-d H:i:s",$end_time);
		logx("yhdDownloadGeneralGoodsList mode:{$mode} start_time:".date("Y-m-d H:i:s",$start_time)." end_time:".date("Y-m-d H:i:s",$end_time),$sid . "/Goods");
	}

	
	$yhd = new YhdClient();
	$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);

	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		if ($retval->errInfoList->errDetailInfo[0]->errorCode != 'yhd.general.products.search.prod_no_found')
		{
			$error_msg = $retval->error_msg;
			logx("yhdDownloadGeneralGoodsList yhd->sendByPost fail,error_msg:{$error_msg}", $sid . "/Goods");
			return false;
		}
		logx("yhdDownloadGeneralGoodsList $shopid count: 0", $sid . "/Goods");
		if(@$end_time && $mode == 1)
		{
			$db->execute("INSERT INTO sys_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
		}
		return false;
	}
		
	/*if(!isset($retval->productList->product) || count($retval->productList->product) == 0)
	{
		logx("yhdDownloadGeneralGoodsList $shopid count: 0", $sid);
		return true;
	}*/

	$items = $retval->productList->product;
	//总条数
	$total_results = intval($retval->totalCount);
	logx("total_results : $total_results ", $sid . "/Goods");
	
	logx("yhdDownloadGeneralGoodsList $shopid count: $total_results", $sid . "/Goods");
		
	//如果不足一页，则不需要再抓了
	if($total_results <= count($items))
	{
		logx(" 不足一页， 只抓这一次即可", $sid . "/Goods");
		$numiid_arr = array();
		for($j =0; $j < count($items); $j++)
		{
			$numiid_arr[] = $items[$j]->productId;
		}
		$price_list = yhdGetProductPrice($db,$appKey, $appsecret, $shop, $numiid_arr);
		$num_list = yhdGetStockCount($db,$appKey, $appsecret, $shop, $numiid_arr);
		if(count($items) > 0)
		{
			$result = downYhdGeneralGoodsDetail($db, $appKey, $appsecret, $shop, $new_count, $chg_count, $error_msg, $items, $price_list, $num_list);
		}
	}
	else //超过一页，第一页抓的作废，从最后一页开始抓
	{
		$total_pages = ceil(floatval($total_results)/20);
		logx(" 共发现 $total_pages 页 ", $sid . "/Goods");
		for($i=$total_pages; $i>=1; $i--)
		{
			logx(" 准备抓第 $i 页 ", $sid . "/Goods");

			$params['curPage'] = $i;
			$params['timestamp'] = date('Y-m-d H:i:s',time());
			$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
	
			if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
			{
				$error_msg = $retval->error_msg;
				logx("yhdDownloadGeneralGoodsList yhd->sendByPost fail2,error_msg:{$error_msg}", $sid . "/Goods");
				return false;
			}
				
			$items = $retval->productList->product;
			
			$numiid_arr = array();
			for($j =0; $j < count($items); $j++)
			{
				$numiid_arr[] = $items[$j]->productId;
			}
			$price_list = yhdGetProductPrice($db,$appKey, $appsecret, $shop, $numiid_arr);
			$num_list = yhdGetStockCount($db,$appKey, $appsecret, $shop, $numiid_arr);
			if(count($items) > 0)
			{
				$result = downYhdGeneralGoodsDetail($db, $appKey, $appsecret, $shop, $new_count, $chg_count, $error_msg, $items, $price_list, $num_list);
			}
		}
	}

	if($result && @$end_time && $mode == 1)
	{
		$db->execute("INSERT INTO sys_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}
	
	return true;
}

function downYhdGeneralGoodsDetail(&$db, $appKey, $appsecret, $shop, &$new_count, &$chg_count, &$error_msg, &$items, &$price_list, &$num_list)
{
	$spec_list = array();
	$shopid = $shop->shop_id;
	$sid = $shop->sid;

	for($i=0; $i<count($items); $i++)
	{
		sleep(1);
		$item = $items[$i];
		$numiid = @$item->productId;
		$title = @$item->productCname;
		$outer_id = @$item->outerId;
		$productId = $item->productId; //outerId可能为空
			
		for ($j=0; $j <count($price_list) ; $j++)
		{
			$p = $price_list[$j];
			if ($p->productId == $numiid)
			{
				$price = $p->nonMemberPrice;
			}
		}

		$num = 0;
		for ($h=0; $h <count($num_list) ; $h++)
		{
			$n = $num_list[$h];
			if ($n->productId == $numiid)
			{
				$num += $n->vs;
			}
		}

		
		$pic_url = yhdGetPicUrl($item->prodImg);
		$update_time = time(); 
		$last_modify_time = time();
	
	    $spec = array
	    ( //上下架状态0：下架，1：上架
		  'status' => $item->canSale == 1?1:2,//0删除 1在架 2下架
		  'platform_id' => 6,
		  'shop_id' => $shopid,
	      'goods_id' => $numiid,
		  'spec_id' => '',
		  'spec_code' => '',
		  'goods_name' => $title,
		  'spec_name' => '',
		  'outer_id' => $outer_id ,
		  'price' => $price,
		  'stock_num' => $num,
		  'pic_url' => $pic_url,
		  'spec_outer_id' => '',
		 // 'is_stock_changed' => '1',
		  // 'stock_syn_warehouses'=> $warehouses,
		  //'is_deleted' =>$is_deleted,
		  'created' =>date('Y-m-d H:i:s',time())
	    );
		$spec_list[] = $spec;	
	}
		//var_dump($spec_list);die;
	//写数据库
	//logx(print_r($goods_list, true), $sid);
	// $a = count($spec_list);
	// logx("个数: {$a}".print_r($spec_list, true), $sid);
	//logx(print_r($match_list, true), $sid);
	if(!putGoodsToDb($sid,$db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
		
	return true;
}

//yhd下载多规格货品(系列产品)

function yhdDownloadSerialGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	
    $sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appKey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	//var_dump($appKey);
	
	logx("yhdDownloadSerialGoodsList shopid: $shopid ", $sid . "/Goods");
	
	//是否采用商家编码拼接的方式
	//$sql = "select CfgValue from g_cfg_sys where CfgKey='apitrade_bConcatGoodsSkuID' ";
	//$bConcatGoodsSkuID = $db->query_result_single($sql);
	
	//API系统参数,目前不支持开始时间和结束时间的设置
	$params = array();
	
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	$params['timestamp'] = date('Y-m-d H:i:s',time());
	
	$params["verifyFlg"] = 2;
	$params["pageRows"] = 20;
	$params['curPage'] = 1;
	$params['canSale'] = 1;
	
	
	$params['method'] = "yhd.serial.products.search";

	if ($mode == 1)
	{
		$start_time = $condition;
		$end_time = time();
		$params['updateStartTime'] = date("Y-m-d H:i:s",$start_time);
		$params['updateEndTime'] = date("Y-m-d H:i:s",$end_time);
		logx("yhdDownloadGeneralGoodsList mode:{$mode} start_time:".date("Y-m-d H:i:s",$start_time)." end_time:".date("Y-m-d H:i:s",$end_time),$sid . "/Goods");
	}
	elseif ($mode == 2)
	{
		$params['productCname'] = $condition;
		logx("yhdDownloadGeneralGoodsList mode:{$mode} title:{$condition}",$sid . "/Goods");
	}
	elseif ($mode == 3)
	{
		$params['productIdList'] = $condition;
		logx("yhdDownloadGeneralGoodsList mode:{$mode} goods_id:{$condition}",$sid . "/Goods");
	}
	elseif ($mode == 4)
	{
		$time=explode(',',trim($condition));
		$start_time=strtotime($time[0]);
		$end_time=strtotime($time[1]);
		$now=time();
		if($end_time>=$now)
		{
			$end_time=$now;
		}
		$params['updateStartTime'] = date("Y-m-d H:i:s",$start_time);
		$params['updateEndTime'] = date("Y-m-d H:i:s",$end_time);
		logx("yhdDownloadGeneralGoodsList mode:{$mode} start_time:".date("Y-m-d H:i:s",$start_time)." end_time:".date("Y-m-d H:i:s",$end_time),$sid . "/Goods");
	}
	
	$yhd = new YhdClient();
	$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
	//var_dump($retval);die;
	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		if ($retval->errInfoList->errDetailInfo[0]->errorCode != 'yhd.serial.products.search.prod_not_found')
		{
			$error_msg = $retval->error_msg;
		}
		logx("yhdDownloadSerialGoodsList yhd->sendByPost fail,error_msg:{$error_msg}", $sid . "/Goods");
		return false;
	}
	
	if(!isset($retval->serialProductList) || count($retval->serialProductList) == 0)
	{
		logx("yhdDownloadSerialGoodsList $shopid count: 0", $sid . "/Goods");
		return true;
	}
	
	$items = $retval->serialProductList->serialProduct;
	//总条数
	$total_results = intval($retval->totalCount);
	
	logx("total_results : $total_results ", $sid . "/Goods");
	
	//echo "total_results: $total_results\n";
	logx("yhdDownloadSerialGoodsList $shopid count: $total_results", $sid . "/Goods");
	
	//如果不足一页，则不需要再抓了
	if($total_results <= count($items))
	{
		logx(" 不足一页， 只抓这一次即可", $sid . "/Goods");
		$numiid_arr = array();
		for($j =0; $j < count($items); $j++)
		{
			$numiid_arr[] = $items[$j]->productId;
		}
		if(count($items) > 0)
		{
			$shop->NumIIDs = $numiid_arr;
			downYhdSerialGoodsDetail($db, $shop, $new_count, $chg_count, $error_msg, $items);
		}
	}
	else //超过一页，第一页抓的作废，从最后一页开始抓
	{	
		$total_pages = ceil(floatval($total_results)/20);
		logx(" 共发现 $total_pages 页 ", $sid . "/Goods");
		for($i=$total_pages; $i>=1; $i--)
		{
			logx(" 准备抓第 $i 页 ", $sid . "/Goods");

			$params['curPage'] = $i;
			$params['timestamp'] = date('Y-m-d H:i:s',time());
			$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);

			if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
			{
				$error_msg = $retval->error_msg;
				logx("yhdDownloadSerialGoodsList yhd->sendByPost fail2,error_msg:{$error_msg}", $sid . "/Goods");
				return false;
			}
			$items = $retval->serialProductList->serialProduct;
			$numiid_arr = array();
			for($j =0; $j < count($items); $j++)
			{
				$numiid_arr[] = @$items[$j]->productId;
			}
			
			if(count($items) > 0)
			{
				$shop->NumIIDs = $numiid_arr;
				downYhdSerialGoodsDetail($db, $shop, $new_count, $chg_count, $error_msg, $items);
			}
		}
	}
	
	return true;
}

function downYhdSerialGoodsDetail(&$db, $shop,&$new_count, &$chg_count, &$error_msg, &$items)
{
	
	$spec_list = array();
	
	$shopid = $shop->shop_id;
	$sid = $shop->sid;
	$numiid_arr = & $shop->NumIIDs;
    $appKey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	
	//API系统参数
	$params = array();
	$params['appKey'] = $appKey;
	$params['sessionKey'] = $sessionkey;
	$params['format'] = "json";
	$params['ver'] = "1.0";

	// $params['timestamp'] = date('Y-m-d H:i:s',time());
	
	/*$params["verifyFlg"] = 2;
	$params["pageRows"] = 20;
	$params['curPage'] = 1;*/
	
	$params['method'] = "yhd.serial.product.get";
	$yhd = new YhdClient();
	// logx(print_r($numiid_arr,true) ,$sid);
	for($i=0; $i<count($numiid_arr); $i++)
	{
		sleep(1);
		$params['productId'] = $numiid_arr[$i];
		$params['timestamp'] = date('Y-m-d H:i:s',time());
		// logx(print_r($params,true),$sid);
		$retval = $yhd->sendByPost(YHD_API_URL, $params, array(), $appsecret);
		if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->error_msg;
			logx("downYhdSerialGoodsDetail yhd->sendByPost fail,error_msg:{$error_msg}", $sid . "/Goods");
			return 0;
		}
		
		if(!isset($retval->serialChildProdList->serialChildProd) || count($retval->serialChildProdList->serialChildProd) == 0)
		{
			logx("downYhdSerialGoodsDetail $shopid count: 0", $sid . "/Goods");
			return true;
		}

		$goods_list = $retval->serialChildProdList->serialChildProd;
		$numiid_list = array();
		for($j =0; $j < count($goods_list); $j++)
		{
			$numiid_list[] = @$goods_list[$j]->productId;
		}

		$price_list = yhdGetProductPrice($db,$appKey, $appsecret, $shop, $numiid_list);
		$num_list = yhdGetStockCount($db,$appKey, $appsecret, $shop, $numiid_list);
		
		for($s=0; $s<count($retval->serialChildProdList->serialChildProd); ++$s)
		{
		
		    $item = $retval->serialChildProdList->serialChildProd[$s];
		    $numiid = @$item->productId;
		    $title = @$item->productCname;
		    $outer_id = @$item->outerId;
		    $productId = $item->productId; //outerId可能为空

		    for ($d=0; $d <count($price_list) ; $d++)
			{
				$p = $price_list[$d];
				if ($p->productId == $numiid)
				{
					$price = $p->nonMemberPrice;
					break;
				}
			}

			$num = 0;
			for ($h=0; $h <count($num_list) ; $h++)
			{
				$n = $num_list[$h];
				if ($n->productId == $numiid)
				{
					$num += $n->vs;
				}
			}
		
			$pic_url = yhdGetPicUrl($item->prodImg);
			
			$update_time = time(); 
			$last_modify_time = time();

		    $spec = array
	       ( //上下架状态0：下架，1：上架
		       'status' => $item->canSale == 1?1:2,//0删除 1在架 2下架
		       'platform_id' => 6,
		       'shop_id' => $shopid,
	           'goods_id' => $numiid,
		       'outer_id' => $outer_id ,
		       'goods_name' => $title,
		       'price' => $price,
		       'stock_num' => $num,
		       'pic_url' => $pic_url,
		       'spec_id' => '',
		       'spec_code' => '',
		       'spec_name' => '',
		       'spec_outer_id' => '',
		       'is_stock_changed' => '1',
		       'cid'=> $item->categoryId,
		       //'brand_id'=> $item->brandId,
		       //'stock_syn_warehouses'=> $warehouses,
		        //'is_deleted' =>$is_deleted,
		       'created' =>date('Y-m-d H:i:s',time())
	        );
		     $spec_list[] = $spec;	
		
		}
		
	}

    if(!putGoodsToDb($sid,$db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
		
	return true;
}

?>
