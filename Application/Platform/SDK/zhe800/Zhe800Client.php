<?php 
define("ZHE_URL","http://seller.zhe800.com/api/erp/v1/");
class Zhe800Client
{
	private $app_key;
	private $session;
	private $method;
	/**
	 * @return the $app_key
	 */
	public function getApp_key() {
		return $this->app_key;
	}

	/**
	 * @return the $session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * @param field_type $app_key
	 */
	public function setApp_key($app_key) {
		$this->app_key = $app_key;
	}

	/**
	 * @param field_type $session
	 */
	public function setSession($session) {
		$this->session = $session;
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

	
	public function sendByPost($params)
	{
		$params = http_build_query($params);
		$method=$this->method;
		$cl = curl_init(ZHE_URL.$method.'?');
		curl_setopt($cl,CURLOPT_POST,true);
		curl_setopt($cl,CURLOPT_POSTFIELDS, $params);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);
		return $content;
	}
	
	public function sendByGet($params)
	{
		$params = http_build_query($params);
		$method=$this->method;
		$url=ZHE_URL.$method.'?'.$params;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		
		$content = curl_exec($ch);
		/*if (curl_errno($ch)) {
			throw new Exception(curl_error($ch), 0);
		} else {
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new Exception($reponse, $httpStatusCode);
			}
		}
		*/
		curl_close($ch);
		
		return $content;
	}
	
	public function executeByPost($params)
	{
		$params['api_token']=$this->app_key;
		$params['authorize_token']=$this->session;
	
		$content=$this->sendByPost($params);
	
		return json_decode($content);
	}
	
	public function executeByGet($params)
	{
		$params['api_token']=$this->app_key;
		$params['authorize_token']=$this->session;
	
		$content=$this->sendByGet($params);

		return json_decode($content);
	}
	
	
	
	
	
	
	
	
	
	
	
	
}




























