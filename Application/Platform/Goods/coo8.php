<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/coo8/Coo8Client.php');

function coo8DownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appsecret = $shop->secret;
	$account = $shop->account_nick;
	
	$page_size = 20;
	/*
	* 接口中商品名称不支持汉字输入,无法实现按货品名称下载
	* 输入汉字报 not correct sign. please check secret is correct
	*/
	if($mode == 1)
	{
		$start_time = $condition;
		$end_time = time();
		$params = array(
			'venderId' => $account,
			'method' => 'coo8.items.onsale.get',
			'timestamp' => date('Y-m-d H:i:s', time()),
			'pageNo' => 1,
			'pageSize' => $page_size,
			'startModified'=>$start_time,
			'endModified'=>$end_time
		);
		logx("coo8DownloadGoodsList shopid: $shopid start_time:". date('Y-m-d H:i:s' ,$start_time) . "end_time:" . date('Y-m-d H:i:s' ,$end_time), $sid . "/Goods");
	}
	else
	{
		$error_msg =array('status'=>0,'info'=>'此功能不支持!');
		return false;
	}
	
	$coo8 = new Coo8Client();
	$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
	if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->error_msg;
		logx("ERROR $sid coo8DownloadGoodsList coo8->get fail: $error_msg", $sid . "/Goods",'error');
		return false;
	}
	
	if(!isset( $retval->products->product) || count( $retval->products->product) == 0)
	{	
		logx("coo8DownloadGoodsList $shopid count: 0", $sid . "/Goods");
		return true;
	}
	$items = $retval->products->product;
	//总条数
	$total_results = intval($retval->totalResult);
	logx("coo8DownloadGoodsList $shopid total result: $total_results", $sid . "/Goods");
	
	//如果不足一页，则不需要在抓了
	if($total_results <= count($items))
	{
		logx("just one page...", $sid . "/Goods");

		if(count($items) > 0 && !downcoo8GoodsDetail($db, $shop, $new_count, $chg_count, $error_msg, $items))
		{
			return false;
		}
	}
	else
	{
		$total_pages = ceil(floatval($total_results)/$page_size);
		logx("total page: $total_pages", $sid . "/Goods");
		for($i=$total_pages; $i>=1; $i--)
		{
			$params['pageNo'] = $i;
			$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
			if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shopid))
			{
				$error_msg = $retval->error_msg;
				logx("ERROR $sid coo8DownloadGoodsList coo8->get fail: $error_msg", 'error' . "/Goods",'error');
				return false;
			}
			
			$items = $retval->products->product;
			logx("The $i page is downloaded...", $sid . "/Goods");
			
			if(count($items) > 0 && !downcoo8GoodsDetail($db, $shop, $new_count, $chg_count, $error_msg, $items))
			{
				return false;
			}
		}
	}
	
	if($end_time)
	{
		$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}
	return true;
}

function downcoo8GoodsDetail(&$db, $shop,&$new_count, &$chg_count, &$error_msg, &$items)
{
	$spec_list = array();
	
	$shopid=$shop->shop_id;
	$sid = $shop -> sid;


	for($i = 0;$i<count($items);$i++)
	{
		for($j = 0; $j < count($items[$i]->items->item); $j++)
		{
			$item = $items[$i]->items->item[$j];
			$numiid = @$item->itemId;
			$title = @$item->goodsName;
			$outer_id = @$item->outId;
			$num = (int)@$item->quantity;
			$price =@$item->originalPrice;
			$cid = @$items[$i]->catalogId;
			$update_time = @$item->updateTime; 
			$last_modify_time = time();
			//$brandId = $items[$i]->brandId;
			$spec = array
	   		( //上下架状态0：下架，1：上架
		   		'status' => $item->status == 4?1:2,//0删除 1在架 2下架
		   		'platform_id' => 8,
		   		'shop_id' => $shopid,
		   		'goods_id' => $numiid,
		   		'outer_id' => $outer_id ,
		   		'goods_name' => $title,
		  		'price' =>$price,
		   		'stock_num' =>$num,
		   		'pic_url' => @$item->pics->pic[0]->imgUrl,
		   		'spec_id' => '',
		   		'spec_code' => '',
		   		'spec_name' => '',
		   		'spec_outer_id' => '',
		   		'is_stock_changed' => '1',
		   		'cid'=>@$cid,
		  		// 'brand_id'=> $brandId,
		   		//'stock_syn_warehouses'=> $warehouses,
				//'is_deleted' =>$is_deleted,
		   		'created' =>date('Y-m-d H:i:s',time())
			);
				$spec_list[] = $spec;
		}

	}
	
	
	if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	
	return true;
}