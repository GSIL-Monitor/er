<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/bbw/bbwClient.php');

function bbwDownloadGoodsList(&$db, $shop,&$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appKey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$page_size=40;
	
	logx("bbwDownloadGoodsList shopid: $shopId ", $sid."/Goods");
	//API系统参数,目前不支持开始时间和结束时间的设置
	
	$bbw=new bbwClient($appKey,$appsecret,$session);
	$retval=$bbw->getGoods(1,$page_size);
	if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
	{
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval->error_msg;
        logx("bbwDownloadGoodsList bbw->getGoods fail  错误信息：{$error_msg['info']}",$sid. "/Goods",'error');


		return false;
	}
	
	if(!isset($retval->data) || count($retval->data) == 0)
	{
		logx("bbwDownloadGoodsList $shopId noGoods count: 0",$sid. "/Goods");
		return true;
	}
	$total_results=$retval->count;
	
	if($total_results < $page_size)
	{
		$goods = $retval->data;
		$numiid_arr = array();
		
		for($i = 0; $i < count($goods); $i++)
		{
			$numiid_arr[] =$goods[$i];
		}
		if(count($numiid_arr) > 0)
		{
			if(!downBbwGoodsDetail($sid, $shopId, $db,$numiid_arr, $spec_list, $new_count, $chg_count, $error_msg))
			{
				return false;
			}
		}
	}
	else
	{
		$total_pages =ceil(floatval($total_results)/$page_size);
		for($i =$total_pages; $i >= 1; $i--)
		{
			$retval=$bbw->getGoods($i,$page_size);
			if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
			{
				$error_msg = $retval->error_msg;

                $error_msg['status'] = 0;
                $error_msg['info'] = $error_msg;
                logx("bbwDownloadGoodsList bbw->getGoods fail  错误信息：{$error_msg['info']}",$sid. "/Goods",'error');

				return false;
			}
			$goods = $retval->data;
			$numiid_arr = array();
			for($j = 0; $j < count($goods); $j++)
			{
				$numiid_arr[] =$goods[$j];
			}
			if(count($numiid_arr) > 0)
			{
				if(!downBbwGoodsDetail($sid, $shopId, $db,$numiid_arr, $spec_list, $new_count, $chg_count, $error_msg))
				{
					return false;
				}
			}
		}
	}
	
	return true;

}
function downBbwGoodsDetail($sid, $shopId, &$db,$numiid_arr, &$spec_list, &$new_count, &$chg_count, &$error_msg)
{


	for($i=0;$i<count($numiid_arr);$i++)
	{
		$item = $numiid_arr[$i];
		for($j=0 ; $j < count($item->sku); $j++)
		{
			$sku=$item->sku[$j];
			$spec = array
			(
				'status' =>1,
				'platform_id' => 22,
				'shop_id' => $shopId,
				'goods_id'=>$item->iid,
				'goods_name'=>$item->title,
				'spec_id'=>$sku->id,
				'spec_name'=>$sku->sku_properties,
				'outer_id'=>$sku->outer_id,
				'spec_outer_id'=>$sku->outer_id,
				'price'=>$item->price,
				'stock_num'=>$sku->num,
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