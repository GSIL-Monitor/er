<?php
//微盟API
class WmClient
{
    const API_COMMON_URL_PREFIX = "https://dopen.weimob.com/api/1_0/";
    const CURL_TIMEOUT = 30;  //curl访问超时时间
    
    private $_session = null;
    private $_url = '';

	public function execute($method, $params = array()){
        $this->_url = sprintf(self::API_COMMON_URL_PREFIX . '%s?accesstoken=%s', $method, $this->_session);
        
        $result = $this->StreamPostData($this->_url, $params);
        if($result){
            if(function_exists('json_decode_safe')){
                return json_decode_safe($result);
            }else {
                return json_decode($result);
            }
        }
        
        return $result;
    }
    
    public function refreshToken($appkey, $appSecret, $refreshSession){
        $params = array(
            'grant_type' => 'refresh_token',
            'client_id' => $appkey,
            'client_secret' => $appSecret,
            'refresh_token' => $refreshSession,
        );
        
        $url = 'https://dopen.weimob.com/fuwu/b/oauth2/token?' . http_build_query($params);
        
        return $this->methodRequest($url);
    }
    
    public function methodRequest($url, $params = array(), $method = 'POST'){
        $method = strtoupper($method);
        
        if($method == 'GET' && !empty($params))
        {
            $params = is_array($params) ? http_build_query($params) : $params ;
            $this->_url = (strpos($url, '?') !== false) ? $url . $params : $url;
        }else 
        {  
            $this->_url = $url;
        }
        
        $param = null;
        if($method == 'POST'){
            $param = $params;
        }
        $result = $this->StreamPostData($this->_url, $param);
        
        if($result){
            if(function_exists('json_decode_safe')){
                return json_decode_safe($result);
            }else {
                return json_decode($result);
            }
        }
        return $result;
    }
    
    /**
     * 发送数据
     * @param unknown $params
     * @param unknown $api_method
     * @param string $method = post 默认
     * @return string
     */
    public function StreamPostData($url, $params = null, $header = array("Content-Type: application/json")){
       // logx($url);
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        
        if(!is_null($params)){
            $params = is_array($params) ? json_encode($params) : $params;

            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
        }
        
        $data = curl_exec($ch);
	    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    if(empty($data) && $http_code == 0){
		    logx(print_r($params,true));
		    $data = curl_exec($ch);
	    }
	    list($header, $data) = explode("\r\n\r\n", $data);
        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = trim(array_pop($matches));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $data = curl_exec($ch);
        }
    
        curl_close($ch);

        return $data;
    }
   
    /**
     * @param !CodeTemplates.settercomment.paramtagcontent!
     */
    public function setSession($_session)
    {
        $this->_session = $_session;
    }
}

?>
