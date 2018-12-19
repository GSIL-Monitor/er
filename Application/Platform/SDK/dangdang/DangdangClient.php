<?php
class DangdangClient{
	private $base_url;
	
	private $app_key;
	private $format = 'xml';
	private $sign_method = 'md5';
	private $v = '1.0';
	private $method;
	private $session;
	private $app_secret;
	
	public function __construct($base_url)
	{
		$this->base_url = $base_url;
	}
	
	public function setAppKey($app_key)
	{
		$this->app_key = $app_key;
	}
	
	public function setAppSecret($app_secret)
	{
		$this->app_secret = $app_secret;
	}
	
	public function setMethod($method)
	{
		$this->method = $method;
	}
	
	public function setSession($session)
	{
		$this->session = $session;
	}
	
	public function setV($v)
	{
		$this->v = $v;
	}
		
	private function toGBK($params)
	{
		$paramArray = array();
		foreach($params as $k => $v)
		{
			$paramArray[$k] = iconv('UTF-8', 'GBK', $v);
		}
		
		return $paramArray;
	}
	
	//sign签名算法
	private function sign($allParam, $secret) {
		ksort($allParam);
		reset($allParam);
		
		$sign_str = '';
		foreach ( $allParam as $key => $val)
		{
			if (empty($this->app_key))
			{
				$sign_str .= trim($val);
			}
			else
			{
				$sign_str .= "$key$val";
			}
		}
		
		if (!empty($this->app_key))
		{
			$sign_str = $secret . $sign_str;
		}
		
		$sign_str .= $secret;
		
		return md5(iconv('UTF-8', 'GBK', $sign_str));
	}
	
	public function sendByPost($url, $params, $secret, $fileArray = false)
	{
		if (!empty($this->method))
		{
			$params["app_key"] = $this->app_key;
			$params["format"] = $this->format;
			$params["sign_method"] = $this->sign_method;
			$params["v"] = $this->v;
			$params["method"] = $this->method;
			$params["session"] = $this->session;
			$params["timestamp"] = date("Y-m-d H:i:s");
			
			// just use system parameters to generate sign 
			$sign_params["app_key"] = $this->app_key;
			$sign_params["format"] = $this->format;
			$sign_params["sign_method"] = $this->sign_method;
			$sign_params["v"] = $this->v;
			$sign_params["method"] = $this->method;
			$sign_params["session"] = $this->session;
			$sign_params["timestamp"] = date("Y-m-d H:i:s");

			$paramArray = $this->toGBK($params);
			$paramArray['sign'] = strtoupper($this->sign($sign_params, $this->app_secret));//签名生成sign
			
			$url = $this->base_url . '?' . http_build_query($paramArray);
		}
		else
		{
			$paramArray = $this->toGBK($params);
			$paramArray['validateString'] = $this->sign($paramArray, $secret);//签名生成sign
			$url = $this->base_url . $url . '?' . http_build_query($paramArray);
		}
		
		$post = ($fileArray != false);
		
		$cl = curl_init($url);
		curl_setopt($cl, CURLOPT_POST, $post);
		
		if($post)
		{
			if(is_array($fileArray)){
				$boundary = md5(time());
			
				$postData = $this->addFileInfo($fileArray, $boundary);
			
				$postData .= "--".$boundary."--\r\n";
			
				curl_setopt($cl,CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=".$boundary));
			}else{
				$postData = $fileArray;
			}
			curl_setopt($cl,CURLOPT_POSTFIELDS, $postData);
		}
		
		
		curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);
		
		$xml = simplexml_load_string($content,'SimpleXMLElement', LIBXML_NOCDATA);

		if(!$xml)
		{
			$content = iconv('UTF-8', 'GBK//IGNORE', iconv('GBK', 'UTF-8//IGNORE', $content));
			$xml = simplexml_load_string($content);
		}
		
		return $xml;
	}
	
	private function addFormData($paramArray, $boundary)
	{
		$postInfo  = "";
		
		if($paramArray == null){
			return $postInfo;
		}
		
		foreach ($paramArray as $key => $value) {
			$postInfo .= "--".$boundary."\r\n";
			$postInfo .= "Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n";
			$postInfo .= iconv('UTF-8', 'GBK', $value) . "\r\n";
		}
		
		return $postInfo;
	}
	
	
	private function addFileInfo($filePathArray, $boundary) {
		$postFileInfo = "";

		if(count($filePathArray) == 0){
			return $postFileInfo;
		}
		
		foreach ($filePathArray as $fileName => $fileContent) {
			$postFileInfo .="--".$boundary."\r\n";
			$postFileInfo .="Content-Disposition: form-data; name=\"{$fileName}\"; filename=\"{$fileName}\"\r\n";
			$postFileInfo .="Content-Type: text/xml\r\n\r\n";
			$postFileInfo .= iconv('UTF-8', 'GBK', $fileContent) . "\r\n";
		}
		
		return $postFileInfo;
	}
}
?>
