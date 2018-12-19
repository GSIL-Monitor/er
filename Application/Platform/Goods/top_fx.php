<?php
//淘宝分销货品
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoProductsGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoProductUpdateRequest.php');

//淘宝分销模板
function topFenxiaoDownloadGoodsListImpl($db, 
	$sid, 
	$shopId, 
	$session, 
	$mode, 
	$condition,
	&$top, 
	&$req, 
	&$new_count, 
	&$chg_count, 
	&$error_msg)
{
	$page_no = 1;
	$page_size = 20;
	switch($mode)
	{
		case 1:
		{
			$start_time = $condition;
			$end_time = time();
			$req->setPageNo($page_no);
			$req->setPageSize($page_size);
			break;
		}
		case 3:
		{
			$start_time = 0;
			$end_time = 0;
			$req->setPids($condition); //产品id
			break;
		}
		case 4:
	    {
	        $timeArray = explode(',', $condition);
	        $start_time = strtotime($timeArray[0]);
	        $end_time = strtotime($timeArray[1]);
	        
	        $req->setPageNo($page_no);
	        $req->setPageSize($page_size);
	        break;
	        
	    }
	}
	$req->setFields('skus'); //sku数据
	
	
	$spec_list = array();
	
	$result = splitTime($start_time, $end_time, 3600*24, function($from_time, $to_time) use(&$db, &$req, &$top, &$error_msg, &$spec_list, $session, $sid, $shopId, $mode,$page_size ,&$new_count, &$chg_count)
	{
		if($from_time && $to_time)
		{
			$req->setStartModified(date('Y-m-d H:i:s', $from_time));
			$req->setEndModified(date('Y-m-d H:i:s', $to_time));
		}
		
		$retval = $top->execute($req, $session);
		
		if(API_RESULT_OK != topErrorTest($retval,$db,$shopId))
		{
			$error_msg["status"] = 0;
			$error_msg["info"] = $retval->error_msg;
			logx("topFenxiaoDownloadGoodsList top->execute fail: ".$retval->error_msg, $sid . "/Goods");
			return false;
		}
		if(!isset($retval->products) || count($retval->products) == 0)
		{
			return true;
		}
		
		$items = @$retval->products->fenxiao_product;
		$total_results = intval($retval->total_results);
		
		//总条数
		
		logx("topFenxiaoDownloadGoodsList ".date('Y-m-d H:i:s', $from_time)."--".date('Y-m-d H:i:s', $to_time)." total_results: $total_results ", $sid . "/Goods");
		
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($items))
		{
			for($j =0; $j < count($items); $j++)
			{
			    $item = $items[$j];
				if(!loadGoodsDetailImpl($shopId,$sid, $item, $spec_list))
				    continue;
				//超过100条写一次库
				if(count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
				{
				    return false;
				}
			}
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			logx("topFenxiaoDownloadGoodsList :total_page $total_pages", $sid . "/Goods");
			for($i=$total_pages; $i>=1; $i--)
			{
				logx("topFenxiaoDownloadGoodsList page $i ", $sid . "/Goods");
				$req->setPageNo($i);
				$retval = $top->execute($req, $session);
				if(API_RESULT_OK != topErrorTest($retval,$db, $shopId))
				{
					$error_msg["status"] = 0;
					$error_msg["info"] = $retval->error_msg;
					logx("topFenxiaoDownloadGoodsList top->execute fail2: ".$retval->error_msg, $sid . "/Goods");
					return false;
				}
					
				$items = $retval->products->fenxiao_product;
				for($j =0; $j < count($items); $j++)
				{
					$item = $items[$j];
				    if(!loadGoodsDetailImpl($shopId,$sid, $item, $spec_list))
				        continue;
				    //超过100条写一次库
				    if(count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
				    {
				        return false;
				    }
				}
			}
		}
		
		if($to_time || count($spec_list) == 0)
		{
			if ($mode == 1)
			{
				$db->execute ( "INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$to_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)" );
			}
		}
		
		return true;
	});
	
	//保存数据
	if((count($spec_list) == 0 || putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) && $result && $end_time)
	{
		$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}
	
	return $result;
}

//淘宝分销下载货品列表
function topFenxiaoDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	switch ($mode) {
	    case 1:
    	    $start_time = $condition;
    		$end_time = time();
    		logx("topFenxiaoDownloadGoodsList shopid: $shopId start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time: " . 
    			date('Y-m-d H:i:s', $end_time), $sid . "/Goods");
	    break;
	    case 3:
	        $start_time = 0;
		    $end_time = 0;
		    logx("topFenxiaoDownloadGoodsList shopid: $shopId pid: {$condition}", $sid . "/Goods");
        break;
        case 4:
            $timeArray = explode(',', $condition);
			$start_time = $timeArray[0];
			$end_time = $timeArray[1];
            logx("topFenxiaoDownloadGoodsListbytime shopid: $shopId start_time:" . $start_time . " end_time: " . $end_time, $sid . "/Goods");
        break;
	}
	
	//taobao
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $shop->key;
	$top->secretKey = $shop->secret;
	
    $req = new FenxiaoProductsGetRequest();
	
	if(!topFenxiaoDownloadGoodsListImpl($db, 
		$sid, 
		$shopId, 
		$shop->session, 
		$mode, 
		$condition,
		$top, 
		$req, 
		$new_count, 
		$chg_count, 
		$error_msg))
	{
		return false;
	}
	
	return true;
}

//下载货品模板
function loadGoodsDetailImpl($shopId,$sid, &$item, &$spec_list,$is_deleted=0)
{
    $platform_id = 2;//分销
	$outer_id = trim(@$item->outer_id);
	if(iconv_strlen($outer_id, 'UTF-8')>40)
	{
		logx("GOODS_NO_EXCEED\t{$outer_id}\t{$item->title}", $sid . "/Goods");
		$outer_id = iconv_substr($outer_id, 0, 40, 'UTF-8');
	}
	
	$property_alias = trim(@$item->property_alias);
	
	$spec = array
	(
	    'status' => $is_deleted==1?0:($item->status == 'up')?1:2,
	    'platform_id' => $platform_id,
	    'shop_id' => $shopId,
	    'goods_id' => trim($item->pid),//产品id
	    'outer_id' => $outer_id,
	    'cid' => @$item->category_id,
	    'goods_name' => trim($item->name),
	    'price' => (float)$item->standard_retail_price, //零售基准价
	    'stock_num' => $item->quantity,
	    'pic_url' => iconv_substr(trim($item->pictures),0,255,'UTF-8'),
	    'spec_id' => '',
	    'spec_code' => '',
	    'spec_name' => '',
	    'spec_outer_id' => '',
	    'is_stock_changed' => '1',
	    'is_deleted' =>$is_deleted,
	    'spec_sku_properties' => '',
	    'created' =>date('Y-m-d H:i:s',time())
	);
	//规格
	$skus = & $item->skus->fenxiao_sku;
	if(empty($skus))
	{
		$spec_list[] = $spec;
	}
	else
	{
		$prop_imgs = @$item->prop_imgs->prop_img;
		
		foreach($skus as &$sku)
		{
			$spec_outer_id = trim(@$sku->outer_id);
			if(iconv_strlen($spec_outer_id, 'UTF-8')>40)
			{
				logx("SPEC_NO_EXCEED\t{$outer_id}\t{$spec_outer_id}\t{$item->title}", $sid . "/Goods");
				$spec_outer_id = iconv_substr($spec_outer_id, 0, 40, 'UTF-8');
			}
			
			$nspec = $spec;
			$nspec['spec_id'] = @$sku->id;
			$nspec['spec_code'] = $spec_outer_id;
			$nspec['spec_sku_properties'] = @$sku->properties;
			$nspec['spec_name'] = analysisAlias(@$sku->properties, $property_alias, @$sku->name);
			$nspec['spec_outer_id'] = $spec_outer_id;
			$nspec['price'] = (float)@$sku->standard_price;
			$nspec['stock_num'] = @$sku->quantity;
			
			$sku_properties = explode(';', $sku->properties);
			//规格图片
			if(!empty($prop_imgs) && !empty($sku_properties[0]))
			{
				foreach($prop_imgs as &$prop_img)
				{
					if($prop_img->properties == $sku_properties[0])
					{
						$nspec['pic_url'] = iconv_substr(@$prop_img->url,0,255,'UTF-8');
						break;
					}
				}
			}
			
			$spec_list[] = $nspec;
		}
	}
		
	return true;
}

//淘宝分销回写商家编码
function topFenxiaoUploadSpecno($db, $shop, $pid, $skuId, $skuProperties, $outerId, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$session = $shop->session;
	
	logx("topFenxiaoUploadSpecno shopid: $shopId pid {$pid} skuId {$skuId} skuProperties {$skuProperties} outerId {$outerId} ", $sid . "/Goods");
	
	//taobao
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $shop->key;
	$top->secretKey = $shop->secret;
	
	if(empty($skuId))
	{
		//链接对应单规格宝贝
		$req = new FenxiaoProductUpdateRequest();
		$req->setPid($pid);
		$req->setOuterId($outerId);

		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval,$db,$shopId))
		{
			$error_msg["status"] = 0;
			$error_msg["info"] = $retval->error_msg;
			logx("topFenxiaoUploadSpecno top->execute fail", $sid . "/Goods");
			return false;
		}
		
		if(isset($retval->item))
		{
			return true;
		}
		else
		{
			$error_msg["status"] = 0;
			$error_msg["info"] = '未知错误';
			return false;
		}
		
	}
	else
	{
		//链接对应多规格报表
		if(!empty($skuProperties))
		{
			//正常数据
			$req = new FenxiaoProductUpdateRequest();
			$req->setPid($pid);
			$req->setProperties($skuProperties);
			$req->setOuterId($outerId);

			$retval = $top->execute($req, $session);
			if(API_RESULT_OK != topErrorTest($retval,$db,$shopId))
			{
				$error_msg["status"] = 0;
				$error_msg["info"] = $retval->error_msg;
				logx("topFenxiaoUploadSpecno top->execute fail", $sid . "/Goods");
				return false;
			}
			
			if(isset($retval->sku))
			{
				return true;
			}
			else
			{
				$error_msg["status"] = 0;
				$error_msg["info"] = '未知错误';
				return false;
			}
		}
		else
		{
			//历史数据, 月亮宝贝和话梅会有, 有skuid但是没有skuproperties, 需要重新下载该链接
			$error_msg["status"] = 0;
			$error_msg["info"] = "不是最新数据, 请重新下载该链接";
			return false;
		}
	}
	
	return true;
}



//取出淘宝规格的别名
//sku_properties: '1627207:30156;122216343:568';
//property_alias: '1627207:3232483:藏青色;1627207:28326:红色;1627207:3232484:薄荷绿色'
//sku_name:  '颜色分类：浅绿色，参考身高：110'
//返回值示例： 蝴蝶兰M
function analysisAlias($sku_properties, $property_alias, $sku_properties_name)
{
    $sku_properties_arr = explode(';', $sku_properties);
    $sku_properties_name_arr = explode(';', $sku_properties_name);
    $property_alias_arr = explode(';', $property_alias);

    $result = "";
    foreach($sku_properties_arr as $sku_properties_item)
    {
        //1627207:3232483
        $find_alias = false;
        //先找别名
        foreach($property_alias_arr as $property_alias_item)
        {
            //1627207:3232483:蝴蝶兰
            if(strpos($property_alias_item, $sku_properties_item) !== false)
            {
                $result .= substr($property_alias_item, strrpos($property_alias_item, ":")+1);
                $find_alias = true;
                break;
            }
        }
        if(!$find_alias)
        {
            //再找默认名称
            foreach($sku_properties_name_arr as $sku_properties_name_item)
            {
                //1627207:3232483:颜色分类:军绿色
                if(strpos($sku_properties_name_item, $sku_properties_item) !== false)
                {
                    $result .= substr($sku_properties_name_item, strrpos($sku_properties_name_item, ":")+1) ;
                    break;
                }
            }
        }
    }
    //上面的找默认名称的方法目前已经无法使用。根据当前接口返回情况直接获取规格名称。
	//$sku_properties_name_item  '颜色分类：浅绿色'
    if(!$find_alias && $result == ''){
	    foreach($sku_properties_name_arr as $sku_properties_name_item)
	    {
		    $result .= substr($sku_properties_name_item, strrpos($sku_properties_name_item, ":")+1) ;
	    }
    }

    return trim($result);
}
?>

