<?php
class Smt
{
	public static function sendRequest($appkey, $appsecret, $method, $params)
	{
		$sign_str = '';
		ksort($params);
		foreach ($params as $key => $val)
		{
			$sign_str .= $key . $val;
		}
		$sign_str = 'param2/1/aliexpress.open/' . $method . '/' . $appkey . $sign_str;
		$code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appsecret, true)));
		$url = 'http://gw.api.alibaba.com/openapi/param2/1/aliexpress.open/' . $method . '/' . $appkey . '?' . 
				http_build_query($params) . '&_aop_signature=' . $code_sign;

		$cl = curl_init($url);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);
		
		$obj = json_decode_safe($content);
		// print_r($obj);
		if(is_object($obj) && isset($obj->result))
			return $obj;
		
		return $obj;
	}
	
	public static function syncLogistics($appkey,
								  $appsecret,
								  $session,
								  $tid, 
								  $logisticsBillNo,
								  $logisticsCompanyId
								  )
	{
		$params['access_token'] = $session;
		$params['outRef'] = $tid;
		$params['sendType'] = 'all';
		$params['serviceName'] = $logisticsCompanyId;
		$params['logisticsNo'] = $logisticsBillNo;
		
		return self::sendRequest($appkey, $appsecret, 'api.sellerShipment', $params);
	} 
	
	
	public static function getTradeDetail($appkey,
								  $appsecret, 
								  $session,
								  $tid)
	{
		$params['access_token'] = $session;
		$params['orderId'] = $tid;
		
		return self::sendRequest($appkey, $appsecret, 'api.findOrderById', $params);
	}
	
	
	public static function getLogisticsCompanies($appkey,
										$appsecret, 
										$session)
	{
		$params['access_token'] = $session;
		return self::sendRequest($appkey, $appsecret, 'api.listLogisticsService', $params);
	}

	public static function getTradeList($appkey,
							$appsecret, 
							$session,
							$createDateStart, 
							$createDateEnd,
							$page, 
							$pageSize)
	{
		$params['access_token'] = $session;
		$params['page'] = $page; 
		$params['pageSize'] = $pageSize; 
		$params['createDateStart'] = $createDateStart; 
		$params['createDateEnd'] = $createDateEnd;
		
		return self::sendRequest($appkey, $appsecret, 'api.findOrderListQuery', $params);
	}

	public static function getGoodsDetail($appkey,
								  $appsecret, 
								  $session,
								  $id)
	{
		$params['access_token'] = $session;
		$params['productId'] = $id;
		
		return self::sendRequest($appkey, $appsecret, 'api.findAeProductById', $params);
	}

	public static function syncStock($appkey,
							$appsecret,
							$session,
							$goods_id,
							$spec_id,
							$stock)
	{
		$params['access_token'] = $session;
		$params['productId'] = $goods_id;
		$params['skuId'] = $spec_id;
		$params['ipmSkuStock'] = $stock;

		return self::sendRequest($appkey, $appsecret, 'api.editSingleSkuStock', $params);
	}

	public static function sendByPost($url,$params)
	{

		$ch=curl_init($url);
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$postBodyString = "";
		if (is_array($params) && 0 < count($params))
		{
			foreach ($params as $k => $v)
			{
				$postBodyString .= "$k=" . urlencode($v) . "&";
			}
			unset($k, $v);
			
			$postBodyString = substr($postBodyString,0,-1);
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postBodyString);
		}
		
		$content = curl_exec ( $ch );
		if(!$content)
		{
			logx("The icbc response's content is null,and the reason is-->".curl_error($ch));
			return;
		}
		curl_close ( $ch );
		// print_r($content);
		$obj = json_decode($content);
		if (!$obj)
		{
			return $content;
		}
        return $obj;
	}
}

?>
