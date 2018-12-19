<?php

class Mia
{
	//API请求地址(测试)
	// public $apiurl = 'http://gw-test.api.miyabaobei.com/openapi/app';
	public $apiurl = 'gw.api.miyabaobei.com/openapi/app';

	//API接口名称
	public $method;

	//商家ID
	public $vendor_key;

	//秘钥
	public $secret_key;

	//API版本
	public $version = '1.0';

	public $format = 'json';


	public function  execute($sysParams = null)
	{
		//组装系统参数
		$sysParams['method'] = $this->method;
		$sysParams['vendor_key'] = $this->vendor_key;
		$sysParams['version'] = $this->version;
		$sysParams['format'] = $this->format;
		$sysParams['timestamp'] = time();

		//获取签名
		$sysParams['sign'] = $this->generateSign($sysParams);

		//发送http请求
		try
		{
			$resp = $this->sendHttpSync($this->apiurl, $sysParams);
		}
		catch(Exception $e)
		{
			return false;
		}

		//解析返回结果
		$respObject = json_decode_safe($resp);
		return $respObject;

	}

	/**
	* 签名
	* @param $params 参数 
	* @return string
	*/
	public function generateSign($params)
	{
		if (!empty($params))
		{
			//所有参数按照字符顺序排序
			ksort($params);
			
			$stringToBeSigned = '';
			//把所有参数名和参数值拼在一起
			foreach ($params as $k => $v)
			{
				if (is_array($v))
				{
					$v = json_encode($v);
				}
				$stringToBeSigned .=  "$k$v";
			}

			unset($k,$v);

			//把secret_key加在字符串的尾部
			$stringToBeSigned .= $this->secret_key;
		}

		return md5($stringToBeSigned);
	}

	/**
	* 发送http请求
	*
	* @param $url
	* @param $apiParams
	* @return mixed
	* @throws Exception
	*/
	public function sendHttpSync($url, $apiParams)
	{
		$ch = curl_init();
		$header = array(
				'Content_type: application/x-www-form-urlencoded; charset=utf-8'
			);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiParams));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		if (!$response)
		{
			logx("The mia response's content is null,and the reason is-->".curl_errno($ch));
			return;
		}

		curl_close($ch);

		return $response;

	}
}

?>