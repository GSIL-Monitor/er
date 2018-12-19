<?php
date_default_timezone_set('PRC');
class ccjptClient{
//	public $url= "http://rapid-productgroup.chuchujie.com/interface.php?c=delivery&m="; //测试地址
	public $url= "http://pintuan.chuchujie.com/interface.php?c=delivery&m=";//正式地址
	public $app_key;
	public $method;
	public $appsecret;


	public function excute($params){
		$params['app_key'] = $this->app_key;
		$params['stamp'] = time();
		$params['sign'] = $this->generateSign($params);
		//http请求
		$api_url = $this->url.$this->method;
        $resp = $this->sendByPost($api_url, $params);
        $res = json_decode($resp);
		return $res;

	}

	public function generateSign($params)
	{
		ksort($params);
		$buff = "";
		foreach ($params as $key => $value) 
		{
			$buff .= $key."=".$value."&";
		}
		$str = trim($buff,"&");
		$str = $str."&key=".$this->appsecret;
		$result = strtoupper(md5($str));
		return $result;
	}

	//post请求
    public function sendByPost($url, $postFields = NULL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $reponse = curl_exec($ch);
        curl_close($ch);
        return $reponse;
    }

}





