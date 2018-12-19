<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/fn/FnClient.class.php');


//下载货品列表
function fnDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$session = $shop->session;
	
	$pageSize = 40;
	$curPage = 1;
	
    logx("fnDownloadGoodsList shopid: $shopId shops:".print_r($shop,true),$sid.'/Goods');
	
	$client = new FnClient($shop->key,$shop->secret);
	$client->setAuthSession($session);
	$method = "fn.item.inventory.get";
	$data = array(
	    "status"=>"",//查询商品状态2-仓库中,3-销售中
	    "title"=>"",
	    "merchantCodeStr"=>"",//商家商品编码
	    "goodsId"=>"", //商品id
	    "curPage"=>"{$curPage}",
	    "pageRows"=>"{$pageSize}",
	);
	switch ($mode) {
	    case 1:
	    case 4:
	        {   
	            logx("fnDownloadGoodsList shopid: {$shopId}  mode:{$mode}", $sid.'/Goods');
	            break;
	        }
	    default:
	        return false;
	        break;
	}

	$params = array(
	    'method'=> $method,
    	'params'=>@json_encode($data),
	);
	
	logx("fnDownloadGoodsList shopid: $shopId params:".print_r($params,true),$sid.'/Goods');
	
	$retval = $client->sendDataByCurl($params);
	if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
	{
		$error_msg = $retval->error_msg;
		logx("ERROR $sid fnDownloadGoodsList fn.item.inventory.get fail {$error_msg}",$sid.'/Goods' ,'error');
		return false;
	}
	
	if(isset($retval->data->list) && count($retval->data->list) == 0)
	{
		logx("fnGoods $shopId count: 0", $sid.'/Goods');
		return true;
	}
	
	$items = $retval->data->list;
	$total_results = intval($retval->data->totalRows);
	//总条数
	logx("total_results : $total_results ", $sid.'/Goods');
	//如果不足一页，则不需要再抓了
	if($total_results <= count($items))
	{
		for($j =0; $j < count($items); $j++)
		{
			$item = $items[$j];
    		if(!loadGoodsDetailImpl($shopId, $item, $spec_list))
    		    continue;
		}	
	}
	else //超过一页，第一页抓的作废，从最后一页开始抓
	{
		$total_pages = ceil(floatval($total_results)/$pageSize);
		logx("total_page $total_pages", $sid.'/Goods');
		for($i=$total_pages; $i>=1; $i--)
		{
			logx("page $i ", $sid.'/Goods');
			$data['curPage'] = "{$i}";
			$params = array(
			    'method'=> $method,
			    'params'=>@json_encode($data),
			);
			$retval = $client->sendDataByCurl($params);
			if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
			{
				$error_msg = $retval->error_msg;
				logx("ERROR $sid fnDownloadGoodsList fn.item.inventory.get fail more than one pages {$error_msg}",$sid.'/Goods' ,'error');
				return false;
			}
			$items = $retval->data->list;
			for($j =0; $j < count($items); $j++)
			{
				$item = $items[$j];
    		    if(!loadGoodsDetailImpl($shopId, $item, $spec_list))
    		        continue;
			}

		}
	}
	if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
	    return false;
	}
	
	return true;
}

//下载货品详情
function fnDownloadGoodsDetail($db,$shop,$mode,$num_iids,&$new_count,&$chg_count,&$error_msg)
{

    $goods_list = array();
    $spec_list = array();
    $match_list = array();
    
    $sid = $shop->sid;
    $shopId = $shop->shop_id;
    $session = $shop->session;
    
    logx("准备下载numiids： $num_iids ", $sid.'/Goods');

    $client = new FnClient($shop->key,$shop->secret);
	$client->setAuthSession($session);
	
	$method = "fn.item.get";
	$data = array(
	    "goodsId"=> $num_iids,
	    "fields"=>"goodsId,title,sellPoint,catId,shopCatId,brandId,marketPrice,costPrice,mallPrice,length,width,height,pic01,pic02,pic03,pic04,pic05,pic06,weight,createTime,updateTime,createId,updateId,propnames,goodscontent,stock,aftersaleservice,status,downdate,merchantId,barcode,approveStatus,merchantCode,period,hasWarranty,warrantyTime,warrantyUrl,warrantyTel,warrantyAddr,packList,reReason,weightUnit,brandName,catSeo,sold,specType,old_id,source",
	);
	
	$params = array(
	    'method'=> $method,
	    'params'=>@json_encode($data),
	);

	$retval = $client->sendDataByCurl($params);
	if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
	{
	    $error_msg = $retval->error_msg;
	    logx("ERROR $sid fnDownloadGoodsDetail fn.item.get fail {$error_msg}", $sid.'/Goods','error');
	    return false;
	}

    $items = $retval->data->list;

    for($i=0; $i<count($items); $i++)
    {
        $item = $items[$i];

        if(!loadGoodsDetailImpl($shopId, $item, $spec_list))
            continue;
    }

    if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
    {
        return false;
    }

	return true;
}

//下载商品模板
function loadGoodsDetailImpl($shopId, &$item, &$spec_list)
{
	$goods_id = $item->goodsId;
	$skus = & $item->items;
	$platform_id = 31;
	
	for($j=0 ; $j < count($skus); $j++)
	{
		$sku = $item->items[$j];
		if(isset($sku->colorprop))
		{
			if(strpos(';', @$sku->colorprop) !== false)
			{
				list($color_id,$colorname) = @explode(';', @$sku->colorprop);
			}else
			{
				$color_id = '';
				$colorname = @$sku->colorprop;
			}
		}else{
			$color_id = '';
			$colorname = '';
		}
		
		if(isset($sku->salerprop))
		{
			if(strpos(';', @$sku->salerprop) !== false)
			{
				list($salerprop_id,$salerpropname) = @explode(';', @$sku->salerprop);
			}else{
				$salerprop_id = '';
				$salerpropname = $sku->salerprop;
			}
		}else{
			$salerprop_id = '';
			$salerpropname = '';
		}

		$sku_name = $colorname." ".$salerpropname;
		
		$spec_sku_properties = '';
		if($color_id != "" || $salerprop_id != "")
		{
			$spec_sku_properties = $color_id.":".$salerprop_id;
		}

		$spec = array
		(
			'status' => (@$item->status == 2)?2:1,//2 仓库 3 销售
			'platform_id' => $platform_id,
			'shop_id' => $shopId,
			'goods_id' => $goods_id,
			'outer_id' => @$item->merchantId,
			'cid' => @$item->catId,
			'goods_name' => trim($item->title),
			'price' => $sku->price,
			'stock_num' => $sku->num,
			'pic_url' => trim(@$sku->pic01),
			'spec_id' => $sku->skuId,//规格id
			'spec_code' => '',
		    'spec_sku_properties' => $spec_sku_properties,
			'spec_name' => $sku_name,
			'spec_outer_id' => trim(@$sku->barcode),
			'is_stock_changed' => '1',
			'spec_sku_properties' => '',
			'created' => array('NOW()')
		);
		$spec_list[] = $spec;
	}
	return true;
}

