<?php
class AlibabaApi
{
	public static function sendRequest($appkey, $appsecret, $method, $params)
	{
		$sign_str = '';
		ksort($params);
		foreach ($params as $key => $val)
		{
			$sign_str .= $key . $val;
		}
		$sign_str = 'param2/1/cn.alibaba.open/' . $method . '/' . $appkey . $sign_str;
		
		$code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appsecret, true)));
		$url = 'http://gw.open.1688.com/openapi/param2/1/cn.alibaba.open/' . $method . '/' . $appkey . '?' . 
				http_build_query($params) . '&_aop_signature=' . $code_sign;

		if($method == 'e56.logistics.offline.send')
		{
			logx('url:'.print_r($url,true),'alibaba/Logistics');
		}
		$cl = curl_init($url);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);
		
		$obj = json_decode_safe($content);
		if(is_object($obj) && isset($obj->result))
			return $obj->result;
		
		return $obj;
	}
	
	public static function syncLogistics($appkey,
								  $appsecret,
								  $session,
								  $memberId, 
								  $tid, 
								  $orderEntryIds, 
								  $logisticsBillNo,
								  $logisticsCompanyId = 8, 
								  $selfCompanyName = '申通'
								  )
	{
		$params['access_token'] = $session;
		$params['orderId'] = $tid;
		$params['memberId'] = $memberId;
		$params['orderEntryIds'] = $orderEntryIds;
		$params['tradeSourceType'] = 'cbu-trade';
		$params['logisticsCompanyId'] = $logisticsCompanyId;
		
		if($logisticsCompanyId == 8)
		{
			if(empty($selfCompanyName)) $selfCompanyName = '自有物流';
			$params['selfCompanyName'] = $selfCompanyName;
		}
		
		$params['logisticsBillNo'] = $logisticsBillNo;
		$params['gmtSystemSend'] = date('Y-m-d H:i:s');
		$params['gmtLogisticsCompanySend'] = date('Y-m-d H:i:s');

		logx('params:'.print_r($params,true),'alibaba/Logistics');
		return self::sendRequest($appkey, $appsecret, 'e56.logistics.offline.send', $params);
	} 
	
	//虚拟发货
	public static function syncDummyLogistics($appkey,
								  $appsecret,
								  $session,
								  $memberId, 
								  $tid, 
								  $orderEntryIds
								  )
	{
		$params['access_token'] = $session;
		$params['orderId'] = $tid;
		$params['memberId'] = $memberId;
		$params['orderEntryIds'] = $orderEntryIds;
		$params['tradeSourceType'] = 'cbu-trade';
		
		$params['gmtSystemSend'] = date('Y-m-d H:i:s');
		$params['gmtLogisticsCompanySend'] = date('Y-m-d H:i:s');
		
		return self::sendRequest($appkey, $appsecret, 'e56.logistics.dummy.send', $params);
	}
	
	public static function getGoodsInfo($appkey,
								  $appsecret, 
								  $session,
								  $goodsId)
	{
		$params['access_token'] = $session;
		$params['offerId'] = $goodsId;
		$params['returnFields'] = 'offerId,saledCount,offerStatus,unit,priceUnit,amountOnSale,subject,details,unitPrice,skuArray,amount,priceRanges,freightTemplateId,productUnitWeight,productFeatureList,imageList';
		
		return self::sendRequest($appkey, $appsecret, 'offer.get', $params);
	}
	
	public static function getGoodsList($appkey,
								  $appsecret, 
								  $session,
								  $page,
								  $pageSize)
	{
		$params['access_token'] = $session;
		$params['type'] = 'SALE';
		$params['page'] = $page;
		$params['pageSize'] = $pageSize;
		$params['returnFields'] = 'offerId,saledCount,offerStatus,unit,priceUnit,amountOnSale,skuPics,subject,details,unitPrice,skuArray,amount,priceRanges,freightTemplateId,productUnitWeight,productFeatureList,imageList';
		
		return self::sendRequest($appkey, $appsecret, 'offer.getAllOfferList', $params);
	}
	
	public static function getOrderDetail($appkey,
								  $appsecret, 
								  $session,
								  $tid)
	{
		$params['access_token'] = $session;
		$params['orderId'] = $tid;
		
		return self::sendRequest($appkey, $appsecret, 'trade.order.orderDetail.get', $params);
	}

	public static function getOrderDetailNew($appkey,
								  $appsecret, 
								  $session,
								  $tid)
	{
		$params['access_token'] = $session;
		$params['id'] = $tid;
		
		return self::sendRequest($appkey, $appsecret, 'trade.order.detail.get', $params);
	}
	
	public static function syncStock($appkey,
							  $appsecret, 
							  $session,
							  $goods_id,
							  $syn_stock,
							  $sku_stock)
	{
		$params['access_token'] = $session;
		$params['offerId'] = $goods_id;
		$params['offerAmountChange'] = $syn_stock;
		if(!empty($sku_stock))
			$params['skuAmountChange'] = $sku_stock;
		
		return self::sendRequest($appkey, $appsecret, 'offer.modify.stock', $params);
	}
	
	public static function getLogisticsCompanies($appkey,
										$appsecret, 
										$session,
										$memberId)
	{
		$params['access_token'] = $session;
		$params['memberId'] = $memberId;
		return self::sendRequest($appkey, $appsecret, 'trade.logisticsCompany.logisticsCompanyList.get', $params);
	}
	public static function getonetrade($appkey,
							$appsecret, 
							$session,
							$memberId, 
							$orderId )
	{
		$params['access_token'] = $session;
		$params['sellerMemberId'] = $memberId;
		$params['orderId'] = $orderId; 
		
		return self::sendRequest($appkey, $appsecret, 'trade.order.orderList.get', $params);
	}
	public static function getOrderList($appkey,
							$appsecret, 
							$session,
							$memberId, 
							$modifyStartTime, 
							$modifyEndTime,
					//		$orderId ,
							$pageNO, 
							$pageSize)
	{
		$params['access_token'] = $session;
		$params['sellerMemberId'] = $memberId;
		$params['pageNO'] = $pageNO; 
	//	$params['orderId'] = $orderId; 
		$params['pageSize'] = $pageSize; 
		$params['modifyStartTime'] = $modifyStartTime; 
		$params['modifyEndTime'] = $modifyEndTime;
		
		return self::sendRequest($appkey, $appsecret, 'trade.order.orderList.get', $params);
	}
}

?>