<?php

class jpwClient
{
	public $secret;
	// public $gwUrl = "http://119.97.143.29:8109/erpapi/index";//测试
	// public $gwUrl = "http://seller.juanpi.com/erpapi/index";
	public $gwUrl = "http://open.juanpi.com/erpapi/index";

	public function execute($request)
	{
		//获取业务参数
		$apiParams = $request;
		//签名
		$apiParams["sign"] = $this->generateSign($request);
		//发送http请求
		try {
			$resp = $this->sendHttpRequest($this->gwUrl, $apiParams);
		} catch (Exception $e) {
			return false;
		}
		//解析返回结果
		$respObject = json_decode_safe($resp);
		return $respObject;
	}

	public function getToken($params){
		$url = "http://open.juanpi.com/erpapi/authorize";
		//发送http请求
		try {
			$token = $this->sendHttpRequest($url, $params);
		} catch (Exception $e) {
			return false;
		}
		//解析返回结果
		$token = json_decode_safe($token);
		return $token;
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
	public function sendHttpRequest($url, $apiParams = null)
	{//初始化一个URL会话
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POST, TRUE);
        curl_setopt ( $ch, CURLOPT_HEADER, FALSE);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $apiParams);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiParams));//print_r($apiParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $reponse = curl_exec($ch);

        if(!$reponse)
		{
			logx("The jpw response's content is null,and the reason is-->".curl_error($ch));
			return;
		}

        curl_close($ch);

        return $reponse;
	}

	/**
	 * 签名
	 * @param  $params 业务参数
	 * @return void
	 */
	private function generateSign(&$params)
	{ 
		if ($params != null) {
			ksort($params);
        	$params['code'] = $this->secret;
		}
		//使用MD5进行加密，再转化成小写
		return md5(http_build_query($params));
	}

}

?>