<?php

require_once(ROOT_DIR . '/Goods/utils.php');
require_once(ROOT_DIR . '/Common/api_error.php');
require_once(TOP_SDK_DIR . '/zhe800/ZheClient.php');


function zheDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    $sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$sessionkey = $shop->session;
	$page = 1;
	$page_size = 50;
    
	$params = array();
	$zhe=new Zhe800Client();
	$zhe->setApp_key($appkey);
	$zhe->setSession($sessionkey);
	$zhe->setMethod('products.json');
   
    $params['page'] = 1;
	$params['per_page'] = $page_size;
	$params['active_state'] = 3;
     
	if($mode == 2)
	{
		$params['name'] = trim($condition);
	}
	$retval = $zhe->executeByGet($params);
	logx(print_r($retval,true),$sid. "/Goods");
	$spec_list = array();
	
	if(API_RESULT_OK != zheErrorTest($retval,$db,$shopid))
	{

        $error_msg['status'] = 0;
        $error_msg['info'] = $retval->error_msg;
        logx("zheDownloadGoodsList zhe->getGoods fail  错误信息：{$error_msg['info']}",$sid. "/Goods",'error');

		return false;
	}
	
	if(!isset($retval->data->pagination->total_count) || $retval->data->pagination->total_count == 0)
	{
		logx("zheDownloadGoodsList $shopid count: 0", $sid. "/Goods");
		return true;
	}
        
    $total_results = $retval->data->pagination->total_count;
	$total_page = $retval->data->pagination->total_pages;
	
	if($total_page == 1)
	{
		$goods = $retval->data->products;
		$numiid_arr = array();
			for($m = 0; $m < count($goods); $m++)
			{
				$numiid_arr[] =$goods[$m];
			}
			if(count($numiid_arr) > 0)
			{ 
				if(!loadZheGoods($sid, $shopid, $db,$numiid_arr, $spec_list, $new_count, $chg_count, $error_msg))
				{
					return false;
				}
			}	
	}
	else
	{
		for($i =$total_page; $i >= 1; $i--)
		{
			$params['page'] = $i;
			
			$retval = $zhe->executeByGet($params);
			
			if(API_RESULT_OK != zheErrorTest($retval,$db,$shopid))
			{
                $error_msg['status'] = 0;
                $error_msg['info'] = $retval->error_msg;
                logx("zheDownloadGoodsList zhe->getGoods fail  错误信息：{$error_msg['info']}",$sid. "/Goods",'error');

				return false;
			}
			
			$goods = $retval->data->products;
			$numiid_arr = array();
			for($n = 0; $n < count($goods); $n++)
			{
				$numiid_arr[] =$goods[$n];
			}
			if(count($numiid_arr) > 0)
			{
				if(!loadZheGoods($sid, $shopid, $db,$numiid_arr, $spec_list, $new_count, $chg_count, $error_msg))
				{
					return false;
				}
			}
		}
	}
	
	return true;
	        
}


function loadZheGoods($sid, $shopid, &$db, $items, &$spec_list, &$new_count, &$chg_count, &$error_msg)
{
	for($i = 0;$i < count($items); $i++)
	{
		$item = $items[$i];
		
		for($j = 0; $j < count($item->sku_descs); $j++)
		{
			
			$sku = $item->sku_descs[$j];
			$spec = array
			(
				'status' =>1,
				'platform_id' => 24,
				'shop_id' => $shopid,
				'goods_id'=>$item->id,
				'goods_name'=>$item->name,
				'spec_id'=>$sku->sku_num,
				'spec_name'=>$sku->sku_desc,
				'outer_id'=>$item->num,
				'spec_outer_id'=>$sku->seller_no,
				'price'=>$sku->org_price,
				'stock_num'=>$sku->stock,
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
 

 function zheGoodsDetail($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
 {
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$appkey=$shop->key;
	$sessionkey = $shop->session;
	 
	$goods_id = trim($condition); 
	$spec_list = array();
	$params=array();	
	$zhe = new Zhe800Client();

	$zhe->setApp_key($appkey);
	$zhe->setSession($sessionkey);
	$zhe->setMethod('products/'.$goods_id.'.json');
	 
	$retval=$zhe->executeByGet($params); 

	if(API_RESULT_OK != zheErrorTest($retval,$db,$shopid))
	{

        $error_msg['status'] = 0;
        $error_msg['info'] = $retval->error_msg;
        logx("zheGoodsDetail zhe fail  错误信息：{$error_msg['info']}",$sid. "/Goods",'error');

		return false;
	} 
	 
	
	$goods = array(0=>($retval->data));
	
	if(!loadZheGoods($sid, $shopid, $db, $goods, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
			
	if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
	{
		return false;
	}
	
	
	return true; 
 }
 
 
 
