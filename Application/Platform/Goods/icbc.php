<?php
require_once (ROOT_DIR . '/Goods/utils.php');
require_once (TOP_SDK_DIR . '/icbc/icbcApiClient.php');

function icbcDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$params = array();
	$spec_list = array();
	
	if ($mode == 1)
	{
		$start_time = $condition;
		$end_time = time ();
		logx ( "icbcDownloadGoodsList shopid: $shopId mode: $mode start_time:" . date ( 'Y-m-d H:i:s',  $start_time) . " end_time: " . date ( 'Y-m-d H:i:s', $end_time ), $sid . "/Goods" );
		$params ['modify_time_from'] = date ( 'Y-m-d H:i:s', $start_time );
		$params ['modify_time_to'] = date ( 'Y-m-d H:i:s', $end_time );
	}
	elseif ($mode == 4)
	{
		$timeArray = explode(',', $condition);
		$start_time = strtotime($timeArray[0]);
		$end_time = strtotime($timeArray[1]);
		logx ( "icbcDownloadGoodsList shopid: $shopId mode: $mode start_time:" . date ( 'Y-m-d H:i:s',  $start_time) . " end_time: " . date ( 'Y-m-d H:i:s', $end_time ), $sid . "/Goods" );
	}elseif ($mode == 3)
	{
		$num_iids = array();
		$num_iids [] = $condition;
		$shop->num_iids = $num_iids;
		logx ( "icbcDownloadGoodsList shopid: $shopId mode: $mode goods_id: $condition", $sid . "/Goods" );
		$result = downIcbcGoodsDetail ( $db, $shop, $spec_list, $new_count, $chg_count, $error_msg );
		return $result;
	}
	
	$result = splitTime ( $start_time, $end_time, 3600 * 24, function ($from_time, $to_time) use(&$db, &$params, &$appkey, &$error_msg, &$spec_list, $session, $sid, $shopId, $appsecret, $mode, &$new_count, &$chg_count, &$shop)
	{
		if ($from_time && $to_time)
		{
			$params ['modify_time_from'] = date ( 'Y-m-d H:i:s', $from_time );
			$params ['modify_time_to'] = date ( 'Y-m-d H:i:s', $to_time );
		}
		
		$params ['product_status'] = '05'; // 默认空为所有状态
		// icbc API 参数
		$icbcApi = new icbcApiClient ();
		$icbcApi->setApp_key ( $appkey );
		$icbcApi->setApp_secret ( $appsecret );
		$icbcApi->setAuth_code ( $session );
		$icbcApi->setMethod ( "icbcb2c.product.list" );
		$retval = $icbcApi->sendByPost ( $params );
		
		if (API_RESULT_OK != icbcErrorTest ( $retval, $db, $shopId ))
		{
			$error_msg['info'] = $retval ['error_msg'];
			$error_msg['status'] = 0;
			logx ( "icbcDownloadGoodsList icbc->execute fail  错误信息：{$error_msg['info']}", $sid . "/Goods" );
			return false;
		}
		
		if (! isset ( $retval ['body'] ) || ! isset ( $retval ['body'] ['products'] ))
		{
			logx ( "icbcDownloadGoodsList $shopId count: 0", $sid . "/Goods" );
			return true;
		}
		
		$trades = $retval ['body'] ['products'] ['product'];
		
		if (empty ( $trades [0] ))
		{ // 只有抓到一个商品
			$num_iids = array();
			$num_iids [] = $trades ['product_id'];
			$shop->num_iids = $num_iids;
			if (! downIcbcGoodsDetail ( $db, $shop, $spec_list, $new_count, $chg_count, $error_msg ))
				return false;
		}
		else
		{ // 抓到多个商品
			$num_iids = array();
			foreach ( $trades as $t )
			$num_iids [] = $t ['product_id'];
			$shop->num_iids = $num_iids;
			if (! downIcbcGoodsDetail ( $db, $shop, $spec_list, $new_count, $chg_count, $error_msg ))
				return false;
		}
		
		return true;
	} );

	if ($result)
	{
		if ($mode != 4) {
			$db->execute ( "INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)" );
		}
	}
	
	return $result;	
	
}

function downIcbcGoodsDetail( &$db, $shop, &$spec_list, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$num_iids = $shop->num_iids;
	// icbc API 参数
	$icbcApi = new icbcApiClient ();
	$icbcApi->setApp_key ( $appkey );
	$icbcApi->setApp_secret ( $appsecret );
	$icbcApi->setAuth_code ( $session );
	$icbcApi->setMethod ( "icbcb2c.product.detail" );
	
	for($i = 0; $i < count ( $num_iids ); $i ++)
	{
		$params ['product_ids'] = $num_iids [$i];
		usleep(200000);//降低接口调用频率
		$retval = $icbcApi->sendByPost ( $params );
		
		if (API_RESULT_OK != icbcErrorTest ( $retval, $db, $shopId ))
		{
			$error_msg['info'] = $retval ['error_msg'];
			$error_msg['status'] = 0;
			logx ( "downIcbcGoodsDetail icbc->sendByPost fail ", $sid . "/Goods" );
			return false;
		}
		
		if (! isset ( $retval ['body'] ) || ! isset ( $retval ['body'] ['products'] ))
		{
			logx ( "downIcbcGoodsDetail $shopId GoodsDetail is null", $sid . "/Goods" );
			return false;
		}
		
		$product = $retval ['body'] ['products'] ['product'];
		
		if (! loadGoodsDetailImpl ( $shopId, $product, $spec_list ))
			continue;
		// 超过100条写一次库
		if (count ( $spec_list ) >= 100 && ! putGoodsToDb ( $sid, $db, $spec_list, $new_count, $chg_count, $error_msg ))
		{
			return false;
		}
	}
	// 保存数据
	if (count ( $spec_list ) > 0)
	{
		if (!putGoodsToDb ( $sid, $db, $spec_list, $new_count, $chg_count, $error_msg ))
		{
			return false;
		}
	}
	return true;
}

function loadGoodsDetailImpl($shopId, &$product, &$spec_list)
{
	switch ($product ['product_status'])
	{
		case '02' : // 审核中
			$status = 2;
			break;
		case '04' : // 待上架
			$status = 2;
			break;
		case '05' : // 已上架
			$status = 1;
			break;
	}
	
	$spec = array(
			'status' => $status,
			'platform_id' => 25,
			'shop_id' => $shopId,
			'goods_id' => trim ( $product ['product_id'] ),
			'outer_id' => trim ( @$product ['skuproducts'] ['skuproduct'] ['product_merchant_id'] ),
			'goods_name' => trim ( $product ['product_name'] ),
			'price' => ( float ) $product ['product_emall_price'], // icbc 商品商城价格
			'stock_num' => ( int ) $product ['product_storage'],
			'pic_url' => '',
			'spec_id' => '',
			'spec_code' => '',
			'spec_name' => '',
			'spec_outer_id' => '',
			'is_stock_changed' => '1',
			'created' => date ( 'Y-m-d H:i:s', time () ) 
	);
	
	$skus = $product ['skuproducts'] ['skuproduct'];
	
	if (empty ( $skus ))
	{
		$spec_list [] = $spec;
	}
	else
	{
		if (empty ( $skus [0] ))
		{
			$nspec = $spec;
			$nspec ['spec_id'] = @$skus ['product_sku_id'];
			$nspec ['spec_code'] = '';
			$nspec ['spec_name'] = trim ( @$skus [''] );
			$nspec['outer_id'] = trim ( $skus ['product_merchant_id'] );
			$nspec ['spec_outer_id'] = '';
			$nspec ['price'] = @$skus['product_emall_price'];
			$nspec ['stock_num'] = @$skus ['product_storage'];
			$nspec ['pic_url'] = '';
			
			$spec_list [] = $nspec;
		}
		else
		{
			foreach ( $skus as $sku )
			{
				$nspec = $spec;
				$nspec ['spec_id'] = @$sku ['product_sku_id'];
				$nspec['outer_id'] = trim ( $sku ['product_merchant_id'] );
				$nspec ['spec_code'] = '';
				$nspec ['spec_name'] = '';
				$nspec ['spec_outer_id'] = '';
				$nspec ['price'] = @$sku['product_emall_price'];
				$nspec ['stock_num'] = @$sku ['product_storage'];
				$nspec ['pic_url'] = '';
				
				$spec_list [] = $nspec;
			}
		}
	}
	
	return true;
}

?>