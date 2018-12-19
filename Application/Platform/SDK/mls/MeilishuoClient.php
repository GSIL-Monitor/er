<?php 
define("MLS_URL","https://openapi.meilishuo.com/invoke?");
class MeilishuoClient
{
	private $app_key;
	private $secret;
	private $access_token;
	private $method;
	
	/**
	 * @return the $app_key
	 */
	public function getApp_key() {
		return $this->app_key;
	}

	public function getSecret() {
		return $this->secret;
	}
	/**
	 * @return the $session
	 */
	public function getAccess_token() {
		return $this->access_token;
	}

	/**
	 * @param field_type $app_key
	 */
	public function setApp_key($app_key) {
		$this->app_key = $app_key;
	}

	public function setSecret($secret) {
		$this->secret = $secret;
	}
	/**
	 * @param field_type $session
	 */
	public function setAccess_token($access_token) {
		$this->access_token = $access_token;
	}
	/**
	 * @return the $method
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param field_type $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	
	public function __construct($url,$app_key,$secret,$access_token,$method)
	{
		$this->app_key = $app_key;
		$this->secret = $secret;
		$this->access_token = $access_token;
		$this->method = $method;
	}
	
	public function sendByPost($params)
	{
		$params = http_build_query($params);
		
		$cl = curl_init(MLS_URL);
		curl_setopt($cl,CURLOPT_POST,true);
		curl_setopt($cl,CURLOPT_POSTFIELDS, $params);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
		$content = curl_exec($cl);
				
		curl_close($cl);
		
		return $content;
	}
	
	public function sendByGet($params)
	{
		$params = http_build_query($params);
		
		$url=MLS_URL.$params;
		
		$cl = curl_init($url);
		curl_setopt($cl, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($cl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($cl);
		
		curl_close($cl);
		
		return $content;
	}
	
	public function executeByPost($appParams)
	{
		$baseParams = [
			'app_key' => $this->app_key,
			'method' => $this->method,
			'access_token' => $this->access_token,
			'timestamp' => time(),
			'format' => 'json',
			//'sign_method' => 'md5',
			//'version' => '1.0',
			
		];
		$params = array_merge($baseParams, $appParams);
		$params['sign']=strtoupper($this->mls_sign($params));
		$content=$this->sendByPost($params);
		return json_decode($content);
	}
	
	public function executeByGet($appParams)
	{
		$baseParams = [
			'app_key' => $this->app_key,
			'method' => $this->method,
			'access_token' => $this->access_token,
			'timestamp' => time(),
			'format' => 'json',
			//'sign_method' => 'md5',
			//'version' => '1.0',
			
		];
		$params = array_merge($baseParams, $appParams);
		$params['sign']=strtoupper($this->mls_sign($params));
		
		$content=$this->sendByGet($params);
		
		
		return json_decode($content);
	}
	
	
	/**
	 * 对请求参数签名
	 * @var array
	 */
	private function mls_sign( $sign_params )
	{
		ksort( $sign_params );

		$str = $this->secret;
		foreach( $sign_params as $key => $value )
		{
			
			if( $value === null )
			{
					continue;
			}
			$str .= $key . $value;
		}
		$str .= $this->secret;

		return md5( $str );
	}
	
	
	
	
	
	
	
	
	
}




























