<?php

class SosClient
{
	private $appMethod;
	private $appKey;
	private $appSecret;
	private $accessToken;
	private $serverUrl = "http://open.suning.com/api/http/sopRequest";
	//private $serverUrl = "http://apipre.cnsuning.com/api/http/sopRequest";
	private $format = 'json';
	private $appRequestTime;
	private $sign;
	private $version = "v1.2";
	private $CHARSET_UTF8 = "UTF-8";

	public function setAppMethod($appMethod)
	{
		$this->appMethod = $appMethod;
	}
	
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
	}
	
	public function setAppKey($appKey)
	{
		$this->appKey = $appKey;
	}
	
	public function setAppSecret($appSecret)
	{
		$this->appSecret = $appSecret;
	}

	public function execute($params)
	{
		//签名
		$this->sign = $this->generateSign($params);

		//发送http请求
		try 
		{
			$resp = $this->sendHttpRequest($params);
		} 
		catch (Exception $e) 
		{
			//todo  要处理异常，记录日志
			//print_r($e->getMessage());
			return false;
		}

		//解析返回结果
		$respObject = json_decode_safe($resp);
		if (null !== $respObject)
		{
			$respWellFormed = true;
			foreach ($respObject as $propKey => $propValue)
			{
				$respObject = $propValue;
			}
		}
		
		if (null !== $respObject && isset($respObject->code) && $respObject->code == 0 && count((array)$respObject) == 2)
		{
			foreach ($respObject as $propKey => $propValue)
			{
				if(is_object($propValue)) $respObject = $propValue;
			}
		}
		
		return $respObject;
	}

	/**
	 *
	 * 发送http请求
	 *
	 * @param $url
	 * @param null $apiParams
	 * @return mixed
	 * @throws Exception
	 */
	public function sendHttpRequest($params)
	{
		$header = array (
         "AppMethod:{$this->appMethod}",
         "AppRequestTime:{$this->appRequestTime}",
         "Format:{$this->format}",
         "AppKey:{$this->appKey}" ,
         "signInfo:{$this->sign}" ,
         "VersionNo:{$this->version}",
		 "access_token:{$this->accessToken}",
         "Content-Type: text/{$this->format}; charset=utf-8"
         );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->serverUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  //设置头信息的地方
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		$response = curl_exec($ch);

		if (curl_errno($ch)) 
		{
			throw new Exception(curl_error($ch), 0);
		} 
		else 
		{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) 
			{
				throw new Exception($response, $httpStatusCode);
			}
		}
		curl_close($ch);
		return $response;
	}

	/**
	 * 签名
	 * @param  $params 业务参数
	 * @return void
	 */
	private function generateSign($params)
	{
		$base64 = base64_encode($params);
		$this->appRequestTime = date("Y-m-d H:i:s");
		$stringToBeSigned = $this->appSecret.$this->appMethod.$this->appRequestTime.$this->appKey.$this->version.$base64;
		return md5($stringToBeSigned);
	}
}


?>

