<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/alibaba/AlibabaApi.class.php');

function aliGetSku($appkey, $appsecret, $session, $goodsId, $sid)
{
	$retval = AlibabaApi::getGoodsInfo($appkey, $appsecret, $session, $goodsId);
	if($retval->total == 0) return;
	if(!empty($retval->toReturn[0]->skuArray))
	{
		return $retval->toReturn[0]->skuArray;
	}
	else if(isset($retval->toReturn[0]->skuArray) && count($retval->toReturn[0]->skuArray) == 0)
	{
		return;
	}
	
	logx("aliGetSku retval:" . print_r($retval, true), $sid . "/Goods");
	return;
}

function alibabaDownloadGoodsList($db, $shop, &$new_count, &$chg_count, &$error_msg)
{
	getAppSecret($shop, $appkey, $appsecret);
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$page = 1;
	$pageSize = 25;
	
	$retval = AlibabaApi::getGoodsList(
							$appkey, 
							$appsecret,
							$shop->session,
							$page,
							$pageSize);
	
	if(!isset($retval->success))
	{
		$error_msg=$retval->error_message;
		logx("ERROR $sid alibabaDownloadGoodsList ali->execute fail: $error_msg", $sid . "/Goods",'error');
		return false;
	}

	if($retval->success && API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
	{
		$error_msg = $retval->error_msg;
		logx("ERROR $sid alibabaDownloadGoodsList ali->execute fail: $error_msg", $sid . "/Goods",'error');
		return false;
	}		
	if(!isset($retval->toReturn) || count($retval->toReturn) == 0)
	{
		logx("alibabaDownloadGoodsList $shopid count: 0", $sid . "/Goods");
		return true;
	}
	$items = $retval->toReturn;
	$total_results = $retval->total;
	
	logx("total_results : $total_results ", $sid . "/Goods");
	logx("alibabaDownloadGoodsList $shopid count: $total_results", $sid . "/Goods");
	if($total_results <= $pageSize)
	{
		logx(" 不足一页，只抓这一次即可", $sid . "/Goods");
		
		if(count($items) > 0 && !downalibabaGoodsDetail($db, $appkey, $appsecret, $shop, $new_count, $chg_count, $error_msg, $items))
		{
			return false;
		}
	}
	else //超过一页，第一页抓的作废，从最后一页开始抓
	{
		$total_pages = ceil(floatval($total_results)/$pageSize);
		logx(" 共发现 $total_pages 页 ", $sid . "/Goods");
		for($i=$total_pages; $i>=1; $i--)
		{
			logx(" 准备抓第 $i 页 ", $sid . "/Goods");
			
			$retval = AlibabaApi::getGoodsList(
							$appkey, 
							$appsecret,
							$shop->session,
							$i,
							$pageSize);
	
			if($retval->success && API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
			{
				$error_msg = $retval->error_msg;
				logx("ERROR $sid alibabaDownloadGoodsList ali->execute fail: $error_msg", $sid . "/Goods",'error');
				return false;
			}		
			
			$items =$retval->toReturn;	
			
			if(count($items) > 0 && !downalibabaGoodsDetail($db, $appkey, $appsecret, $shop, $new_count, $chg_count, $error_msg, $items))
			{
				return false;
			}
		}
	}
	
	return true;	
} 
function downalibabaGoodsDetail(&$db, $appkey, $appsecret, $shop, &$new_count, &$chg_count, &$error_msg, &$items)
{
	$spec_list = array();
	$shopid = $shop->shop_id;
	$sid = $shop->sid;
	
	for($i=0; $i<count($items); $i++)
	{
		
		sleep(1);
		$item = $items[$i];
		$numiid = @$item->offerId;
		$num = @$item->amount;
		$title = $item->subject;
		$pic_url = isset($item->imageList[0])?$item->imageList[0]->summURL:" ";
		$price = @$item->priceRanges[0]->price;
		$goodsNO = '';
		foreach($item->productFeatureList  as $prop)
		{
			if($prop->name == '货号')
			{
				$goodsNO = $prop->value;
				break;
			}
		}

		if(iconv_strlen($goodsNO, 'UTF-8')>40)
		{
			logx("GOODS_NO_EXCEED\t{$goodsNO}\t{$title}", $sid . "/Goods");
			$goodsNO = iconv_substr($goodsNO, 0, 40, 'UTF-8');
		}

		$spec = array
		(
			'status' =>$item->offerStatus=='online'?1:2, //上下架状态0：下架，1：上架
			'platform_id' => 9,
			'shop_id'=>$shopid,
			'goods_id' => $numiid,
			'outer_id' =>$goodsNO,
			'goods_name' => $title,
			'price' =>$price,
			'stock_num' => $num,
			'pic_url' => $pic_url,
			'spec_id' => '',
			'spec_code' => '',
			'spec_name' => '',
			'spec_outer_id' => '',
			'is_stock_changed' => '1',
			'created'=>date('Y-m-d H:i:s',time())		
		);
		
		$skus = aliGetSku($appkey, $appsecret, $shop->session, $numiid, $sid);
		if(empty($skus))
		{
			$spec_list[] = $spec;
		}
		else
		{
			foreach($skus as &$sk)
			{
				if(isset($sk->children))
				{
					$item = $sk->children;
					foreach($item as &$sku)
					{
						$nspec = $spec;
						$nspec['spec_id'] = @$sku->specId;
						$nspec['spec_code'] = @$sku->cargoNumber;
						$nspec['spec_outer_id'] = @$sku->cargoNumber;
						$nspec['spec_name'] = @$sku->value." : ".$sk->value;
						if(!empty($sku->price))
						{
							$nspec['price'] = (int)@$sku->price;
						}
						$nspec['stock_num'] = (int)@$sku->canBookCount;
				
						$spec_list[] = $nspec;
					}

				}
				else
				{
					$nspec = $spec;
					$nspec['spec_id'] = @$sk->specId;
					$nspec['spec_code'] = @$sk->cargoNumber;
					$nspec['spec_outer_id'] = @$sk->cargoNumber;
					$nspec['spec_name'] = @$sk->value;
					if(!empty($sku->price))
					{
						$nspec['price'] = (int)@$sk->price;
					}
									
					$nspec['stock_num'] = (int)@$sk->canBookCount;
				
					$spec_list[] = $nspec;
				}
			}
			
		}
		
	}
	if(!putGoodsToDb($sid,$db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	
	return true;
}

function downAliGoodsDetailById($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$appkey=$shop->key;
	$appsecret=$shop->secret;
	getAppSecret($shop, $appkey, $appsecret);
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	
	$retval = AlibabaApi::getGoodsInfo(
			$appkey,
			$appsecret,
			$shop->session,
			$condition);
	
	if(!isset($retval->success))
	{
		$error_msg=$retval->error_message;
		logx("ERROR $sid downAliGoodsDetailById ali->execute fail: $error_msg", $sid . "/Goods",'error');
		return false;
	}
	
	if($retval->success && API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
	{
		$error_msg = $retval->error_msg;
		logx("ERROR $sid downAliGoodsDetailById ali->execute fail: $error_msg", $sid . "/Goods",'error');
		return false;
	}
	
	$items = $retval->toReturn;
	
	if(count($items) > 0 && !downalibabaGoodsDetail($db, $appkey, $appsecret, $shop, $new_count, $chg_count, $error_msg, $items))
	{
		return false;
	}
	
	return true;
}










