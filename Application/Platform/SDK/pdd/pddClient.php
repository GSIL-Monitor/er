<?php

class pddClient{

	private $url = 'http://mms.yangkeduo.com/api/common';//正式地址
	//private  $url = 'http://mmsapi.yangkeduo.com:8080/api/common';

	/*接入码uCode，用于区分店铺*/
	private $key;

	private $dtype = 'json';

	/*密匙 用于加密使用*/
	private $secret;

	/*验证码，签名
	* 例如：将uCode=1, mType =2, Secret =ABCD，TimeStamp=123456789参数名和参数值链接后，得到拼装字符串
	* mType2TimeStamp123456789uCode1，最后将Secret拼接到头尾，ABCDmType2TimeStamp123456789uCode1ABCD。
	* 然后md5加密
	*/
	private $sign;

	/*时间戳，十分钟内有效*/
	private $timestamp;

	/*接口方法名*/
	private $method;

	public function execute($params){
		$sign = $this->setSign($params);

		$params['sign'] = $this->setSign($params);

		$retval = $this->http($params);

		return json_decode($retval,true);

	}

	/*构建签名*/
	private function setSign($params){
		$tmp = $params['secret'];
		$tmp .= 'mType'.$params['mType'];
		$tmp .= 'timeStamp'.$params['timeStamp'];
		$tmp .= 'uCode'.$params['uCode'];
		$tmp .= $params['secret'];
		return strtoupper(md5($tmp));
	}

	private function http($params){
		//echo '--'.$this->url.'?'.http_build_query($params).'--';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

		$retval = curl_exec($ch);
		curl_close($ch);

		return $retval;

	}

}


