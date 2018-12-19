<?php
require_once(TOP_SDK_DIR . '/amazon/Model.php');
require_once(TOP_SDK_DIR . '/amazon/Client.php');
require_once(TOP_SDK_DIR . '/amazon/RequestType.php');
require_once(TOP_SDK_DIR . '/amazon/Model/SubmitFeedRequest.php');
require_once(TOP_SDK_DIR . '/amazon/Model/SubmitFeedResponse.php');
require_once(TOP_SDK_DIR . '/amazon/Model/SubmitFeedResult.php');
require_once(TOP_SDK_DIR . '/amazon/Model/FeedSubmissionInfo.php');
require_once(TOP_SDK_DIR . '/amazon/Model/MarketplaceIdList.php');

function amazon_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
	//和拍拍一样,取自系统
	$result = $db->query("select dl.logistics_name as name, dlc.logistics_code as code ".
						" from dict_logistics_code dlc ".
						" left join dict_logistics dl using(logistics_type) ".
						" where dlc.platform_id = 4");
	foreach($result as $k=>$v){
		$companies[]=array(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $v['code'],
			'name' => $v['name'],
			'created' => date('Y-m-d H:i:s',time())
		);
	}
	/*while($row = $db->fetch_array($result))
	{
		$companies[]=array
		(
			'shop_id' => $shop->shop_id,
			'logistics_code' => $row['code'],
			'name' => $row['name'],
			'created' => date('Y-m-d H:i:s',time())
		);
	}*/
	return true;
}

function amazon_sync_logistics(&$db, &$trade, $sid)
{
	getAppSecret($trade, $appkey, $appsecret);

	$shop = getShopAuth($sid, $db, $trade->shop_id);

	/*
	if(is_empty($db, $sid, $trade->rec_id, $shop->account_nick, $trade->tid, $trade->logistics_no, $trade->logistics_code))
	{
		logx("amazon_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid);
		return false;
	}*/
	
	$result = $db->query(sprintf("select oid,CAST(num AS UNSIGNED) num from api_trade_order where platform_id = %d and tid='%s' ", $trade->platform_id, $trade->tid));
	$goods = '';
	while($row = $db->fetch_array($result))
	{
		//去掉订单编号前面的A
		$oid = substr($row['oid'], 1);
		
		$goods .= '<Item><AmazonOrderItemCode>' . $oid . '</AmazonOrderItemCode><Quantity>' . $row['num'] . '</Quantity></Item>';
	}
	$db->free_result($result);

$feed = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<Header>
		<DocumentVersion>1.01</DocumentVersion>
		<MerchantIdentifier>%s</MerchantIdentifier>
	</Header>
	<MessageType>OrderFulfillment</MessageType>
	<Message>
		<MessageID>1</MessageID>
		<OperationType>Update</OperationType>
		<OrderFulfillment>
			<AmazonOrderID>%s</AmazonOrderID>
			<FulfillmentDate>%s</FulfillmentDate>
			<FulfillmentData>
				<CarrierName>%s</CarrierName>
				<ShippingMethod>快速</ShippingMethod>
				<ShipperTrackingNumber>%s</ShipperTrackingNumber>
			</FulfillmentData>
			%s
		</OrderFulfillment>
	</Message>
</AmazonEnvelope>
EOD;
	
	$config = array (
		'ServiceURL' => 'https://mws.amazonservices.com.cn',
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
	$merchantID = $account[0];
	$marketID = $account[1];
	
	$date = date('Y-m-d\TH:i:s\Z', time() - 8*3600 - 60); //东8区, 同时向前移一分钟
	$feed = sprintf($feed, $merchantID, $trade->tid, $date, $trade->logistics_code, $trade->logistics_no, $goods);
	
	$feedHandle = @fopen('php://temp', 'rw+');
	fwrite($feedHandle, $feed);
	rewind($feedHandle);
	
	$request = new MarketplaceWebService_Model_SubmitFeedRequest();
	$request->setMerchant($merchantID);
	$request->setMarketplaceIdList(array("Id" => array($marketID)));
	
	$request->setFeedType('_POST_ORDER_FULFILLMENT_DATA_');
	$request->setContentMd5(base64_encode(md5($feed, true)));
	
	$request->setPurgeAndReplace(false);
	$request->setFeedContent($feedHandle);
	
	try
	{
		$response = $service->submitFeed($request);
		//logx("amazon_response: " . print_r($response, true), $sid);
		
		set_sync_succ($db, $sid, $trade->rec_id);
		logx("amazon_sync_ok: tid {$trade->tid}", $sid. "/Logistics");
	}
	catch (MarketplaceWebServiceOrders_Exception $ex)
	{
        if($ex->getErrorCode()=='RequestThrottled'){
            logx("amazon_reset: tid {$trade->tid}", $sid. "/Logistics");
            set_sync_reset($db, $sid, $trade->rec_id);
            return true;
        }else{
            $error_msg = $ex->getMessage();
            set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
        }

		logx("amazon_sync_failed: msg = $error_msg code = ". $ex->getErrorCode(), $sid. "/Logistics");
		logx("WARNING $sid amazon_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$error_msg}", $sid. "/Logistics");
		return false;
	}
	
	return true;
}