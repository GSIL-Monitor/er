<?php 
define("ZHE_URL","https://openapi.zhe800.com/api/erp/v2/");
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
		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($cl);
		
		if(!curl_errno($cl))
		{
		 $info = curl_getinfo($cl);

		 $http_code = $info['http_code'];
		}
		
		curl_close($cl);
		
		$content_data = json_decode($content);
		
		if(!is_object($content_data))
		{
			$content_data = array(
								'errors'=>array($content)
							);
		}
		
		$result = array(
						'code'=>$http_code,
						'data'=>$content_data
			);
					
		$result = json_encode($result);
		
		return $result;
	}
	
	public function sendByGet($params)
	{
		$params = http_build_query($params);
		$method=$this->method;
		$url=ZHE_URL.$method.'?'.$params;
		
		$cl = curl_init($url);
		//curl_setopt($cl, CURLOPT_HTTPHEADER, array('Host: openapi.zhe800.com'));
		curl_setopt($cl, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($cl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($cl);
		if(!curl_errno($cl))
		{
		 $info = curl_getinfo($cl);

		 $http_code = $info['http_code'];
		}
		
		curl_close($cl);
		
		$content_data = json_decode($content);
		
		if(!is_object($content_data))
		{
			$content_data = array(
								'errors'=>array($content)
							);
		}
		
		$result = array(
						'code'=>$http_code,
						'data'=>$content_data
			);
					
		$result = json_encode($result);
		return $result;
	}
	
	public function executeByPost($params)
	{
		$params['app_key']=$this->app_key;
		$params['access_token']=$this->session;
		
		$params['sign']=strtoupper($this->zhe_sign($params));

		$content=$this->sendByPost($params);
	
		return json_decode($content);
	}
	
	public function executeByGet($params)
	{
		$params['app_key']=$this->app_key;
		$params['access_token']=$this->session;
		
		$params['sign']=strtoupper($this->zhe_sign($params));
		
		$content=$this->sendByGet($params);
		
		
		return json_decode($content);
	}
	
	
	/**
	 * 对请求参数签名
	 * @var array
	 */
	private function zhe_sign( $params )
	{
		

		ksort( $params );

		$str = $this->session;
		foreach( $params as $key => $value )
		{
			
			if( $value === null )
			{
					continue;
			}
			$str .= $key . $value;
		}
		$str .= $this->session;

		return md5( $str );
	}
	
	
	
	
	
	
	
	
	
}




























