<?php
/**
 * author: Bi Jintao
 * DataTime 2016-05-11 08:40:00
 */

//电商ID
defined('EBusinessID') or define('EBusinessID', 1312982);
//电商加密私钥，快递鸟提供
defined('AppKey') or define('AppKey', '6091a095-985d-489d-b7a1-beeaff443c2b');
//请求url
defined('PostAdr') or define('PostAdr', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');

class kdnClient{
	//实时获取物流追踪信息
	function GetTraces($requestData){
		$requestData = json_encode($requestData);
		$param_info = array(
			'EBusinessID' => EBusinessID,
			'RequestType' => '1002',
			'RequestData' => urlencode($requestData),
			'DataType' => '2',
		);
		$param_info['DataSign'] = $this->encrypt($requestData, AppKey);
		$postdata = http_build_query($param_info);
		
		return $this->sendPost($postdata);
	}

	//向快递鸟推送已发货的物流单号
	function syncLogisticNo($requestData)
	{
		$requestData = json_encode($requestData);
		$param_info = array(
			'EBusinessID' => EBusinessID,
			'RequestType' => '1005',
			'RequestData' => urlencode($requestData),
			'DataType' => '2',
		);
		$param_info['DataSign'] = $this->encrypt($requestData, AppKey);
		$postdata = http_build_query($param_info);
		
		return $this->sendPost($postdata);
	}

	function sendPost( $postdata) {
		$length = strlen($postdata);
		$cl = curl_init(PostAdr);
		curl_setopt($cl,CURLOPT_POST,true);
		curl_setopt($cl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		curl_setopt($cl,CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded;charset=utf-8","Content-length: ".$length));
		curl_setopt($cl,CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
		$content = curl_exec($cl);
		curl_close($cl);
		if($content == '')
			return false;
		return json_decode($content,true);
	}

	function encrypt($data, $appkey) {
		return urlencode(base64_encode(md5($data.$appkey)));
	}
}
?>