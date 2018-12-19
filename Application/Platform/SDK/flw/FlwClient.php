<?php
//date_default_timezone_set('PRC');
//测试环境
//define('FLW_URL', "http://sandbox.open.shzyfl.cn:8080/api/1/");
//正式环境：
//define('FLW_URL', "http://open.shzyfl.cn/api/1/");

class FlwClient
{
	private $app_key;
	private $app_secret;
	private $user_key;
	private $user_secret;
	private $dirname;
	private $url;

	public function __construct($user_key,$user_secret,$session){
		$this->app_key = $session['app_key'];
		$this->app_secret = decodeDbPwd($session['app_secret'],$session['app_key']);
		$this->user_key = $user_key;
		$this->user_secret = $user_secret;
		$this->url = "http://open.shzyfl.cn/api/1/";
	}

	public function setDirname($dirname)
	{
		$this->dirname = $dirname;
		$this->url .= $this->dirname;
	}

	public function execute($params,$re='get')
	{
        $header = array();
        $header['appKey'] = $this->app_key;
        $header['params'] = json_encode($params);
        $header['userKey'] = $this->user_key;
        $header['timestamp'] = time();
        $header['sign'] = $this->getSign($header);
        $header = array_reverse($header);
        if($re == 'get'){
            $content = $this->sendByGet($header);
        }else{
            $content = $this->sendByPost($header);
        }
		return json_decode_safe($content);
	}

	public function sendByGet($header)
	{
		$non = "?";
		foreach ($header as $key => $val) {
			$non .= $key  . "=" . urlencode($val) . '&';
		}
		$url = $this->url . rtrim($non, '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	public function sendByPost($header){
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $this->url);
        curl_setopt ( $ch, CURLOPT_HEADER, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $header );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        $content = curl_exec ( $ch );
        return $content;
    }
	public function getSign($header)
	{
		$sign = '';
		ksort($header);
		foreach ($header as $key => $val) {
			$sign .= $key . $val;
		}
		return md5($sign . $this->app_secret . $this->user_secret);
	}

}



