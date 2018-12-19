<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/sos/SosClient.php');

function sosDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$spec_list = array();
	$loop_count = 0;
	if($mode == 1)
	{
		$start_time = $condition;
		$end_time = time();
		$ptime = $end_time;
		logx("sosDownloadGoodsList shopId: $shopId start_time:" . 
			date('Y-m-d H:i:s', $start_time) . " end_time: " . 
			date('Y-m-d H:i:s', $end_time), $sid. "/Goods");
	}
	else if($mode == 3)
	{
		$productId=array();
		$productId[0]=(string)$condition;
		logx("sosDownloadGoodsList shopId: $shopId title {$condition}", $sid. "/Goods");
		$ret1 = downSosGoodsDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
		$ret2 = downSosGoodsBookDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
		
		if((!$ret1) && (!$ret2))
		{
			return false;
		}
		return true;
	}
	else
	{
		return false;
	}
	$save_time = $end_time;
	while($ptime > $start_time)
	{
		$loop_count++;
		
		if($loop_count > 1) resetAlarm();
		if($ptime - $start_time > 3600*24*30) $ptime = $end_time - 3600*24*30 + 1;
		else $ptime = $start_time;
		
		logx("sosgoods shopId: $shopId start_time:" . 
			date('Y-m-d H:i:s', $ptime) . " end_time: " . 
			date('Y-m-d H:i:s', $end_time), $sid. "/Goods");
		
		$sos = new SosClient();
		$sos->setAppKey($shop->key);
		$sos->setAppSecret($shop->secret);
		$sos->setAccessToken($shop->session);
		$sos->setAppMethod("suning.custom.item.query");
		$params['sn_request']['sn_body']['item']['startTime'] = date('Y-m-d H:i:s', $ptime);
		$params['sn_request']['sn_body']['item']['endTime'] = date('Y-m-d H:i:s', $end_time);
		$params['sn_request']['sn_body']['item']['pageNo'] ="1";
		$params['sn_request']['sn_body']['item']['pageSize'] ="50";
		$params = json_encode($params);
		$retval = $sos->execute($params);
		unset($params);
		if(API_RESULT_OK != sosErrorTest($retval, $db, $shopId))
		{
			if($retval->error_msg=='biz.handler.data-get:no-result')
			{
				$end_time = $ptime + 1;
				continue;
			}
			$error_msg['status'] = 1;
			$error_msg['info'] = $retval->error_msg;
			logx("$sid sosDownloadGoodsList $shopId ".$error_msg['info'], $sid. "/Goods",'error');
			return false;
		}
		
		$total_pages=$retval->sn_head->pageTotal;
		
		logx("total_pages {$total_pages}",$sid. "/Goods");
		

		// just one page
		if (1 == $retval->sn_head->pageTotal)
		{	
			
			$items = &$retval->sn_body->item;
			logx("page 1",$sid. "/Goods");
			$page_result=count($items);
			logx("sosDownloadGoodsList page_result:{$page_result}",$sid. "/Goods");
				
			
			$productId=array();
			foreach($items as $item) 
			{
				if(!empty($item->productCode))
				{
					$productId[]=$item->productCode;
				}
			}
			
			if(count($items > 0))
			{
				$ret1 = downSosGoodsDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
				$ret2 = downSosGoodsBookDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
		
				if((!$ret1) && (!$ret2))
				{
					return false;
				}
			}
		
		}
			//more than one
		else
		{
			$total_pages = $retval->sn_head->pageTotal;
			for($i=$total_pages; $i>0; $i--)
			{
				$sos->setAppMethod("suning.custom.item.query");
				$params['sn_request']['sn_body']['item']['startTime'] = date('Y-m-d H:i:s', $ptime);
				$params['sn_request']['sn_body']['item']['endTime'] = date('Y-m-d H:i:s', $end_time);
				$params['sn_request']['sn_body']['item']['pageNo']= strval($i);
				$params['sn_request']['sn_body']['item']['pageSize'] ="50" ;
				$params = json_encode($params);
				$retval = $sos->execute($params);
				unset($params);
				if(API_RESULT_OK != sosErrorTest($retval, $db, $shopId))
				{
					releaseDb($db);

					if($retval->error_msg=='biz.handler.data-get:no-result')
					{
						$end_time = $ptime + 1;
						continue;
					}
					$error_msg['status'] = 1;
					$error_msg['info'] = $retval->error_msg;
					
					logx("$sid sosDownloadGoodsList $shopId ".$error_msg['info'], $sid. "/Goods",'error');
					return false;
				}
				logx("page $i",$sid. "/Goods");
				
				$items = &$retval->sn_body->item;
				$page_result=count($items);
				logx("sosDownloadGoodsList page_result:{$page_result}",$sid. "/Goods");
				
				$productId=array();
				foreach($items as $item) 
				{
					if(!empty($item->productCode))
					{
						$productId[]=$item->productCode;
					}
					
				}
				
				if(count($items > 0))
				{
					$ret1 = downSosGoodsDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
					$ret2 = downSosGoodsBookDetail($db,$shop, $spec_list, $new_count, $chg_count, $error_msg ,$productId);
					
					if((!$ret1) && (!$ret2))
					{
						return false;
					}
				}

			}
				
		}
		$end_time = $ptime + 1;
	}
	if($end_time)
	{
		$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$save_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}

	return true;	
}

function downSosGoodsDetail(&$db, $shop,&$spec_list, &$new_count, &$chg_count, &$error_msg ,&$productId)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$sos = new SosClient();
	$sos->setAppKey($shop->key);
	$sos->setAppSecret($shop->secret);
	$sos->setAccessToken($shop->session);
	$sos->setAppMethod("suning.custom.itemdetail.query");
	for($i=0;$i<count($productId);$i++)
	{
		
		$params['sn_request']['sn_body']['itemDetail']['productCode'] = $productId[$i];
		$params = json_encode($params);
		$retval = $sos->execute($params);
		unset($params);
		

		if(empty($retval->sn_body->itemDetail->productCode))
		{
			continue;
		}

		if(API_RESULT_OK != sosErrorTest($retval, $db, $shopId))
		{
			$error_msg['status'] = 1;
			$error_msg['info'] = $retval->error_msg;
			logx("$sid downSosGoodsDetail $shopId ".$error_msg['info'], $sid. "/Goods",'error');
			return false;
		}
		$item = $retval->sn_body->itemDetail;
			
		$outer_id=trim($item->itemCode);
		$title=trim($item->productName);
		
		if(isset($item->childItem) && count($item->childItem)>0)
		{
			//多规格
			$skus=$item->childItem;
			foreach($skus as &$sku)
			{
				$spec = array
				(
					'status' => $item->status == 2?1:2,
					'platform_id' => 13,
					'shop_id' => $shopId,
					'goods_id' => trim($sku->productCode),
					'outer_id' =>  '',
					'spec_id' => '',
					'spec_code' => trim($sku->itemCode),
					'goods_name' => $title,
					'spec_name' => trim($sku->productName),
					'price' =>(float)$sku->price,
					'spec_outer_id' => trim($sku->itemCode),
					'stock_num' =>(int)@trim($sku->invQty),
					'pic_url' => trim($sku->img1Url),
					'is_stock_changed' => '1',
					'created' => date('Y-m-d H:i:s',time())
				);
				$spec_list[] = $spec;
			}
		
		}
		else
		{
			//单规格
		
			$spec = array
			(
				'status' => $item->status == 2?1:2,
				'platform_id' => 13,
				'shop_id' => $shopId,
				//平台货品ID
				'goods_id' => trim($item->productCode),
				//商家编码
				'outer_id' =>  '',
				'spec_id' => '',
				'spec_code' => trim($item->itemCode),
				'goods_name' => $title,
				'spec_name' => '',
				'price' =>@(float)$item->price,
				'spec_outer_id' => trim($item->itemCode),
				'stock_num' =>(int)@trim($item->invQty),
				'pic_url' => @$item->img1Url,
				'is_stock_changed' => '1',
				'created' => date('Y-m-d H:i:s',time())
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


function downSosGoodsBookDetail(&$db, $shop,&$spec_list, &$new_count, &$chg_count, &$error_msg ,&$productId)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$sos = new SosClient();
	$sos->setAppKey($shop->key);
	$sos->setAppSecret($shop->secret);
	$sos->setAccessToken($shop->session);
	$sos->setAppMethod("suning.custom.book.itemdetail.query");
	for($i=0;$i<count($productId);$i++)
	{
		
		$params['sn_request']['sn_body']['itemDetail']['productCode'] = $productId[$i];
		$params = json_encode($params);
		$retval = $sos->execute($params);
		unset($params);
		
		if(empty($retval->sn_body->itemDetail->productCode))
		{
			return false;
		}
		
		if(API_RESULT_OK != sosErrorTest($retval, $db, $shopId))
		{
			$error_msg['status'] = 1;
			$error_msg['info'] = $retval->error_msg;
			logx("$sid downSosGoodsBookDetail $shopId ".$error_msg['info'], $sid. "/Goods",'error');
			return false;
		}
		$item = $retval->sn_body->itemDetail;
			
		$outer_id=trim($item->itemCode);
		$title=trim($item->productName);
		
		if(isset($item->childItem) && count($item->childItem)>0)
		{
			//多规格
			$skus=$item->childItem;
			foreach($skus as &$sku)
			{
				$spec = array
				(
					'status' => $item->status == 2?1:2,
					'platform_id' => 13,
					'shop_id' => $shopId,
					'goods_id' => trim($sku->productCode),
					'outer_id' =>  '',
					'spec_id' => '',
					'spec_code' => trim($sku->itemCode),
					'goods_name' => $title,
					'spec_name' => trim($sku->productName),
					'price' =>(float)$sku->price,
					'spec_outer_id' => trim($sku->itemCode),
					'stock_num' =>(int)@trim($sku->invQty),
					'pic_url' => trim($sku->img1Url),
					'is_stock_changed' => '1',
					'created' => date('Y-m-d H:i:s',time())
				);
				$spec_list[] = $spec;
			}
		
		}
		else
		{
			//单规格
		
			$spec = array
			(
				'status' => $item->status == 2?1:2,
				'platform_id' => 13,
				'shop_id' => $shopId,
				//平台货品ID
				'goods_id' => trim($item->productCode),
				//商家编码
				'outer_id' =>  '',
				'spec_id' => '',
				'spec_code' => trim($item->itemCode),
				'goods_name' => $title,
				'spec_name' => '',
				'price' =>@(float)$item->price,
				'spec_outer_id' => trim($item->itemCode),
				'stock_num' =>@trim($item->invQty),
				'pic_url' => @$item->img1Url,
				'is_stock_changed' => '1',
				'created' => date('Y-m-d H:i:s',time())
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
?>