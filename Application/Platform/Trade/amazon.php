<?php

require_once(ROOT_DIR . '/Trade/util.php');

//亚马逊

require_once (TOP_SDK_DIR . '/amazon/Client.php');
require_once (TOP_SDK_DIR . '/amazon/Model/MarketplaceIdList.php');

require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersRequest.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersResponse.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersResult.php');

require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsRequest.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsResponse.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsResult.php');

require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersByNextTokenRequest.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersByNextTokenResponse.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrdersByNextTokenResult.php');
require_once (TOP_SDK_DIR . '/amazon/Model/FulfillmentChannelList.php');

require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsByNextTokenRequest.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsByNextTokenResponse.php');
require_once (TOP_SDK_DIR . '/amazon/Model/ListOrderItemsByNextTokenResult.php');

require_once (TOP_SDK_DIR . '/amazon/Model/GetOrderRequest.php');
//转时间格式
//2013-02-22T03:01:58Z
function formatAmazonTime($tm)
{
	$dt = new DateTime((string)$tm);
	return date('Y-m-d H:i:s', $dt->getTimestamp());
}

function amazonProvince($province)
{
	global $spec_province_map;
	
	if(empty($province)) return '';
	
	if(iconv_substr($province, -1, 1, 'UTF-8') != '省')
	{
		$prefix = iconv_substr($province, 0, 2, 'UTF-8');
		
		if(isset($spec_province_map[$prefix]))
			return $spec_province_map[$prefix];
		
		return $province . '省';
	}
	
	return $province;
}

function amazonAmount($cy)
{
	if(!$cy)
		return 0;
	
	if($cy->getCurrencyCode() == 'CNY')
		return $cy->getAmount();
	
	logx("amazon_invalid_current: " . print_r($cy, true));
	
	return 0;
}

function amazonLoadTrade(&$t,$sid)
{
	global $spec_province_map;
	$tid = $t->getAmazonOrderId();
	
	if($t->getFulfillmentChannel() != 'MFN')
	{
		logx("amazon trade channel: $tid " . $t->getFulfillmentChannel(),$sid.'/TradeSlow');
		return false;
	}
	
	$shippingAddress = $t->getShippingAddress();
	if(!$shippingAddress)
	{
		logx("amazon_trade no_address: $tid ",$sid.'/TradeSlow');
		return false;
	}
	$trade_status = 10;			//订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$pay_status = 0;			//0未付款1部分付款2已付款
	$process_status = 70;		//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
   
	$is_external = 0;
	$status = $t->getOrderStatus();
	if($status == 'Pending')
	{
		$process_status = 10; 
	}
	else if($status == 'Unshipped')
	{
		$trade_status = 30;
		$process_status = 10;
		$pay_status = 2;
	}
	else if($status == 'PartiallyShipped')
	{
		$trade_status = 40;
		$is_external = 1;
		$pay_status = 1;
	}
	else if($status == 'Shipped' || $status == 'InvoiceUnconfirmed')
	{
		$trade_status = 50;
        $is_external = 1;
	}
	else if($status == 'Canceled')
	{
		$trade_status = 90;
	}
	else if($status == 'Unfulfillable')
	{
		$trade_status = 10;
	}
	else
	{
		logx("ERROR $sid invalid_trade_status $tid {$status}",$sid.'/TradeSlow' ,'error');
	}
    
    $tradeTime = formatAmazonTime($t->getPurchaseDate());
	$payTime = formatAmazonTime($t->getLastUpdateDate());
	
	$delivery_term = 1;		//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	if ('COD' == $t->getPaymentMethod())
	{
		$delivery_term = 2;
	}
	logx("tid:$tid status:$status delivertyTerm:{$t->getPaymentMethod()}",$sid.'/TradeSlow');
	
	$logistics_type = -1;	//未知物流
	$shipingType = $t->getShipServiceLevel();
	if($shipingType == 'Std CN D2D' || $shipingType == 'Std CN Postal')
	{
		$logistics_type = '2';
	}
	else if($shipingType == 'Exp CN EMS')
	{
		$logistics_type = '3';
	}
	
	$province = amazonProvince($shippingAddress->getStateOrRegion());

	
	$receiver_city = $shippingAddress->getCity();	//城市
	$receiver_district = $shippingAddress->getCounty();	//区县
	getAddressID($province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
	$receiver_address = $shippingAddress->getAddressLine1();
	$addr2 = $shippingAddress->getAddressLine2();
	if(!is_null($addr2))
	{
		$receiver_address .= $addr2;
		$addr3 = $shippingAddress->getAddressLine3();
		if(!is_null($addr3))
		{
			$receiver_address .= $addr3;
		}
	}
	$receiver_name= $shippingAddress->getName();
	
	$receiver_mobile= $shippingAddress->getPhone();
	$receiver_phone=$shippingAddress->getPhone();
	$zip= $shippingAddress->getPostalCode();
	$receiver_area=$province." ".$receiver_city." ".$receiver_district;
	$buyer_email= $t->getBuyerEmail();
	$OrderTotal= amazonAmount($t->getOrderTotal());

	
	$trade = array
	(
		'platform_id' => 5,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => $process_status,
		'trade_time' => $tradeTime,
		'pay_time' => $payTime,
		'delivery_term' => $delivery_term,
		'logistics_type' => $logistics_type,
		
		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr(valid_utf8($receiver_address),0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
		'receiver_zip' => $zip,
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'buyer_email' => iconv_substr($buyer_email,0,60,'UTF-8'),
		'to_deliver_time' => '',
		'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.$zip),
		'buyer_message' => '',
		'remark' =>'',
		'remark_flag' => 0,
	    'end_time' => dateValue(0),
		
		'buyer_nick' => '',
		'buyer_area' => '',
		'pay_id' => '',
		'pay_account' => '',
		'logistics_no' => '',
		'is_auto_wms' => 0,
		'is_external' => $is_external,
		'receivable' => $OrderTotal,
		'created' => array('NOW()')

	);
	
	return $trade;
}

function loadAmazonOrder($shopid, &$db, &$trade_list, &$order_list, &$trade, &$orders, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid)
{
	$tid = $trade['tid'];
	
	$post_fee = 0;
	$trade_discout = 0;
	$order_total_price = 0;
	$order_count=count($orders);
	$goods_count=0;
	for($i=0; $i<count($orders); $i++)
	{
	    $o = & $orders[$i];
	    $goods_no = trim(@$o->getSellerSKU());
	    if(iconv_strlen($goods_no,'UTF-8')>40)
	    {
	    	logx("$sid GOODS_SPEC_NO_EXCEED\t{$goods_no}\t".@$o->getTitle(), $sid.'/TradeSlow','error');
			
			$message = '';
			if(iconv_strlen($goods_no, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goods_no}";
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
	    }
	    
	    $invoice_type=0;	//发票类别，0 不需要，1普通发票，2增值税发票
	    $InvoiceTitle='';
		$invoice_content='';
	    $invoice=$o->getInvoiceData();
	    if ($invoice->getInvoiceRequirement()=='Individual' || $invoice->getInvoiceRequirement()=='Consolidated' )
		{
			$invoice_type=1;
			$InvoiceTitle=$invoice->getInvoiceTitle();
			$invoice_content=$invoice->getBuyerSelectedInvoiceCategory();
		}
		
		$num = $o->getQuantityOrdered();
		$goods_count += $num;
		if($num <= 0)
		{
			$order_count--;
			logx("amazon_zero_goods: " . print_r($o, true), $sid.'/TradeSlow');
			continue;
		}
		
		$total_fee = amazonAmount($o->getItemPrice());	//num*price
		$order_post_fee = bcsub(amazonAmount($o->getShippingPrice()), amazonAmount($o->getShippingDiscount()));	//配送费用
		$order_giftwrap_fee = amazonAmount($o->getGiftWrapPrice());	//礼品包装费用
		
		$price = $total_fee / $num;
		$order_total_price += bcadd($total_fee, $order_giftwrap_fee);
		$post_fee += $order_post_fee;
		$trade_discout += bcadd(amazonAmount($o->getPromotionDiscount()), amazonAmount($o->getShippingDiscount()));
		
		$order_discount = amazonAmount($o->getPromotionDiscount());
		$total_amount = bcsub(bcadd($total_fee, $order_giftwrap_fee), $order_discount);
		$share_amount = bcsub(bcadd($total_fee, $order_giftwrap_fee), $order_discount);
		
		$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
		
		$order_list[] = array
		(
			'platform_id' => 5,
			'shop_id'=>$shopid,
			'tid' => $tid,
			'oid' => 'A' . $o->getOrderItemId(),
			'status' => $trade['trade_status'],
			'refund_status' => $order_refund_status,
			
			'goods_id' => $o->getASIN(),
			'spec_id' =>'',
			'goods_no' => $goods_no,
			'spec_no' => '',
			'goods_name' => iconv_substr($o->getTitle(),0,255,'UTF-8'),
			'spec_name' => '',
			
			'num' => $num,
			'price' => $price,
			'adjust_amount' => 0,				//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $order_discount,		//子订单折扣
			'share_discount' => 0,				//分摊优惠
			'total_amount' => $total_amount,	//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,	//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $order_post_fee,	//分摊邮费
			
			'invoice_type' => $invoice_type,
			'invoice_content' => $invoice_content,
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'order_type' => 0,
			'paid' => (2 == $trade['delivery_term']) ? 0 : bcadd($share_amount,$order_post_fee),
			'created' => array('NOW()')
		);
		
	}

	$trade['shop_id'] = $shopid;
	$trade['invoice_type'] = $invoice_type;
	$trade['invoice_title'] = iconv_substr($InvoiceTitle,0,255,'UTF-8');
    	$trade['invoice_content'] = iconv_substr($invoice_content,0,255,'UTF-8'); 
	$trade['post_amount'] = $post_fee;
	$trade['order_count'] = $order_count;
	$trade['goods_count'] = $goods_count;
	$trade['discount'] = $trade_discout;
	$trade['goods_amount'] = $order_total_price;
	$trade['cod_amount'] = (2 == $trade['delivery_term']) ? $trade['receivable'] : 0;
	$trade['dap_amount'] = (2 == $trade['delivery_term']) ? 0 : $trade['receivable'];
	
	$trade_list[] = $trade;
	
	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	
	return true;
}

function downAmazonTradeByTid(&$db, $appkey, $appsecret, &$shop, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	
	$account = explode(',', $shop->account_nick);
	$merchantID = $account[0];
	$marketID = $account[1];

	$config = array (
		'ServiceURL' => AMAZON_API_URL,
		'ProxyHost' => null,
		'ProxyPort' => -1,
		'MaxErrorRetry' => 1,
		);

	$service = new MarketplaceWebServiceOrders_Client(
		$appkey,
		$shop->session,
		'WdtERP',
		'2.0',
		$config);

	$shopid = $shop->shop_id;
	$tids = & $shop->tids;
	$sid = $shop->sid;
	
	$trade_list = array();
	$order_list = array();
	
	$nextToken = '';
	$scan_count = count($tids);
	for($i=0; $i<count($tids); $i++)
	{
		$trade = (array)$tids[$i];
		$tid = $trade['tid'];
	
		try
		{
			$request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
			$request->setSellerId($merchantID);
			$request->setAmazonOrderId($tid);

			$response = $service->getOrder($request);

		}
		catch (MarketplaceWebServiceOrders_Exception $ex) 
		{
			$error_msg['status'] = 0;
			$error_msg['info'] = $ex->getMessage();
			
			logx("ERROR $sid amazon downAmazonTradeByTid failed: msg=".$error_msg['info'] ."code=". $ex->getErrorCode(),$sid.'/TradeSlow', 'error');
			return TASK_OK;
		}
		
		$trades = $response->getGetOrderResult()->getOrders()->getOrder();
		
		
		foreach($trades as $t)
		{
			$trade = amazonLoadTrade($t,$sid);
			if(!$trade) continue;
			
			$trade_list[] = $trade;
		}

		if(count($trade_list) > 0)
		{
			$shop->tids = $trade_list;
			downAmazonTradesDetail($db,$appkey, $appsecret, $shop, $scan_count,$new_trade_count, $chg_trade_count, $error_msg);
		}
	}

}

function downAmazonTradesDetail(&$db, $appkey, $appsecret, &$trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	
	$account = explode(',', $trades->account_nick);
	$merchantID = $account[0];
	$marketID = $account[1];

	$config = array (
		'ServiceURL' => AMAZON_API_URL,
		'ProxyHost' => null,
		'ProxyPort' => -1,
		'MaxErrorRetry' => 1,
		);

	$service = new MarketplaceWebServiceOrders_Client(
		$appkey,
		$trades->session,
		'WdtERP',
		'2.0',
		$config);

	$shopid = $trades->shop_id;
	$tids = & $trades->tids;
	$sid = $trades->sid;
	
	$trade_list = array();
	$order_list = array();
	
	$nextToken = '';
	$scan_count = count($tids);
	$loop_count_detail = 0;
	for($i=0; $i<count($tids); $i++)
	{
		$trade = (array)$tids[$i];
		$tid = $trade['tid'];
		$loop_count_detail++;
		if($loop_count_detail > 1) resetAlarm();
		sleep(21);
	
		try
		{
			if(empty($nextToken))
			{
				$request = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
				$request->setSellerId($merchantID);
				$request->setAmazonOrderId($tid);
				
				$response = $service->listOrderItems($request);
				$result = $response->getListOrderItemsResult();
			}
			else
			{
				$request = new MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest();
				$request->setSellerId($merchantID);
				$request->setNextToken($nextToken);
				
				$response = $service->listOrderItemsByNextToken($request);
				
				$result = $response->getListOrderItemsByNextTokenResult();
			}
		}
		catch (MarketplaceWebServiceOrders_Exception $ex) 
		{
			$error_msg['info'] = $ex->getMessage();
			$error_msg['status'] = 0;
			if($ex->getErrorCode() == 'SignatureDoesNotMatch')	markShopAuthExpired($db, $shopid);
			logx("ERROR $sid amazon listOrderItems failed: msg=".$error_msg['info']." code = ". $ex->getErrorCode(),$sid.'/TradeSlow', 'error');
			return TASK_OK;
		}
		
		$nextToken = $result->getNextToken();
		$orders = $result->getOrderItems()->getOrderItem();
		
		if(!loadAmazonOrder($shopid, $db, $trade_list, $order_list, $trade, $orders, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}

	//保存剩下的到数据库
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list,$discount_list,  $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
}

//异步下载
function amazonDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$error_msg)
{
	$cbp = function(&$trades) use($trade_detail_cmd)
	{
		pushTask($trade_detail_cmd, $trades);
		return true;
	};
	
	return amazonDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $error_msg, $cbp);
}

//同步下载
function amazonSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $count_limit, $start_time, $end_time, &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';
	
	$cbp = function(&$trades) use($db,$appkey, $appsecret, $count_limit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
	{
		downAmazonTradesDetail(
			$db, 
			$appkey, 
			$appsecret, 
			$trades, 
			$scan_count, 
			$new_trade_count, 
			$chg_trade_count, 
			$error_msg);
		
		$total_new += $new_trade_count;
		$total_chg += $new_trade_count;
		
		return ($scan_count < $count_limit);
	};
	
	return amazonDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $error_msg, $cbp);
}

function amazonDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$error_msg, $cbp)
{
	if($end_time - $start_time > 3600*24*3) //跨度超过3天，无法抓，一分钟只有6次调用
	{
		$end_time = $start_time + 3600*24*3;
	}

	$ptime = $start_time;

	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	logx("1amazonDownloadShop $shopid start_time:" .
		date('Y-m-d H:i:s', $ptime) .
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
	
	$config = array (
		'ServiceURL' => AMAZON_API_URL,
		'ProxyHost' => null,
		'ProxyPort' => -1,
		'MaxErrorRetry' => 1,
		);
	
	$service = new MarketplaceWebServiceOrders_Client(
		$appkey,
		$shop->session,
		'WdtERP',
		'2.0',
		$config);

	$account = explode(',', $shop->account_nick);
	@$merchantID = $account[0];
	@$marketID = $account[1];
	
	if(empty($merchantID) || empty($marketID))
	{
		logx("shop account_nick: {$shop->account_nick}", $sid.'/TradeSlow');
		markShopAuthExpired($db, $shopid);
		return TASK_OK;
	}
	
	$marketplaceIdList = new MarketplaceWebServiceOrders_Model_MarketplaceIdList();
	$marketplaceIdList->setId(array($marketID));
	$loop_count = 0;
	while($ptime < $end_time)
	{
		$loop_count++;
		if($loop_count > 1) resetAlarm();
		if($end_time - $start_time > 60*10) $ptime = $start_time + 60*10;
		else $ptime = $end_time;
		logx("2amazon $shopid start_time:" .
				date('Y-m-d H:i:s', $start_time) .
				" end_time:" . date('Y-m-d H:i:s', $ptime), $sid.'/TradeSlow');

		sleep(31);
		$nextToken = '';
		
		do
		{
			try
			{
				if(empty($nextToken))
				{
					$request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
					$request->setSellerId($merchantID);
					$request->setMaxResultsPerPage(20);
					$request->setMarketplaceId($marketplaceIdList);

					//$request->setLastUpdatedAfter(new DateTime(date('Y-m-d H:i:s', $start_time-8*3600), new DateTimeZone('UTC')));
					//$request->setLastUpdatedBefore(new DateTime(date('Y-m-d H:i:s', $ptime-8*3600), new DateTimeZone('UTC')));
					$request->setLastUpdatedAfter($start_time);
					$request->setLastUpdatedBefore($ptime);

					$fulfillmentChannels = new MarketplaceWebServiceOrders_Model_FulfillmentChannelList();
					$fulfillmentChannels->setChannel(array('MFN')); //MFN:卖家自行配送 AFN:亚马逊配送
					$request->setFulfillmentChannel($fulfillmentChannels);

					$response = $service->listOrders($request);

					if(!$response->isSetListOrdersResult())
					{
						break;
					}

					$listOrdersResult = $response->getListOrdersResult();
				}
				else
				{
					$request = new MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest();
					$request->setSellerId($merchantID);
					$request->setNextToken($nextToken);
					
					$response = $service->listOrdersByNextToken($request);
					
					if(!$response->isSetListOrdersByNextTokenResult())
					{
						break;
					}
					
					$listOrdersResult = $response->getListOrdersByNextTokenResult();
				}
			}
			catch (MarketplaceWebServiceOrders_Exception $ex)
			{
				$error_msg['info'] = $ex->getMessage();
				$error_msg['status'] = 0;
				if($ex->getErrorCode() == 'SignatureDoesNotMatch')	markShopAuthExpired($db, $shopid);
				logx("ERROR $sid amazon listOrders failed: msg=".$error_msg['info']. "code = ". $ex->getErrorCode(), $sid.'/TradeSlow','error');
				return TASK_OK;
			}
			
			$nextToken = $listOrdersResult->getNextToken();
			
			$trades = $listOrdersResult->getOrders()->getOrder();

			$tids = array();
			foreach($trades as $t)
			{
				$trade = amazonLoadTrade($t,$sid);
				if(!$trade) continue;
				
				$tids[] = $trade;
			}

			if(count($tids) > 0)
			{
				$shop->tids = $tids;
				if(!$cbp($shop)) return TASK_SUSPEND;
			}
		} while(!empty($nextToken));

		if($save_time)
		{
			$save_time = $ptime;
			logx("order_last_synctime_{$shopid}".'上次抓单时间保存 amazon平台 '.print_r($save_time,true),$sid. "/default");
			setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
		}

		$start_time = $ptime;
	}

	return TASK_OK;
}

?>