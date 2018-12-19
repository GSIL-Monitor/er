<?php
class XhsClient
{
	public function sendByPost($short_url, $secret, $system_param,$express_company_code,$express_no) {
		
		$sign = $this->getSign($short_url, $secret, $system_param);
		@$url = $this->creatUrl($short_url);


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

		curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		  \"status\": \"shipped\",
		  \"express_company_code\": \"$express_company_code\",
		  \"express_no\": \"$express_no\"
		}");

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		 "app-key:".$system_param['app-key'],
		  "timestamp:".$system_param['timestamp'],
		  "sign:$sign",
		  "Content-Type: application/json;charset=utf-8"
		));

		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);
		return $response;
	}

	public function stockByPost($short_url, $secret, $system_param,$qty) 
	{
	
		$sign = $this->getSign($short_url, $secret, $system_param);
		@$url = $this->creatUrl($short_url);


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

		curl_setopt($ch, CURLOPT_POSTFIELDS, "{
	
		  \"qty\": $qty
		}");

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		 "app-key:".$system_param['app-key'],
		  "timestamp:".$system_param['timestamp'],
		  "sign:$sign",
		  "Content-Type: application/json;charset=utf-8"
		));

		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);
		return $response;
	}

	public function sendByGet($short_url,$secret,$system_param,$def_param){
		if(is_array($def_param) && !empty($def_param)){
    		$param = array_merge($system_param,$def_param);
		}else{
    		$param = $system_param;
		}
		$sign = $this->getSign($short_url, $secret, $param);
		$url = $this->creatUrl($short_url, $def_param);
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "app-key:".$system_param['app-key'],
		  "timestamp:".$system_param['timestamp'],
		  "sign:$sign",
		  "Content-Type: application/json;charset=utf-8"
		));

		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);
		return $response;
	}
	function getSign($url, $secret, $param) {
	    ksort($param);
	    $sign_str = $url."?";
	    foreach ( $param as $key => $val) {
	        $sign_str .= "$key=$val"."&";
	    }
	    $sign_str = rtrim($sign_str, "&");
	    $sign_str = $sign_str . $secret;
	    
	    return md5($sign_str);
	}
	function creatUrl($short_url,$param){
	    $url = "https://ark.xiaohongshu.com".$short_url;
	    
	    if($param){
	        $url = $url."?";
	        foreach ( $param as $key => $val) {
	            $url .= "$key=$val"."&";
	        }
	        $url = rtrim($url, "&");
	    }
	    return $url;
	}
	
}

?>