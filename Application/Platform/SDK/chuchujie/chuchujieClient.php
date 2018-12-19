<?php
define("CCJ_URL","https://parter.api.chuchujie.com/sqe");


class ChuchujieClient
{
    
    private $app_key;
    private $method;
    private $dirname;
    private $app_secret;
    private $session;

    /**
     * @return the $app_secret
     */
    public function getApp_secret() {
        return $this->app_secret;
    }

    /**
     * @return the $session
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * @param field_type $app_secret
     */
    public function setApp_secret($app_secret) {
        $this->app_secret = $app_secret;
    }

    /**
     * @param field_type $session
     */
    public function setSession($session) {
        $this->session = $session;
    }

    /**
     * @return the $dirname
     */
    public function getDirname() {
        return $this->dirname;
    }

    /**
     * @param field_type $dirname
     */
    public function setDirname($dirname) {
        $this->dirname = $dirname;
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

    /**
     * @return the $app_key
     */
    public function getApp_key() {
        return $this->app_key;
    }

    /**
     * @param field_type $app_key
     */
    public function setApp_key($app_key) {
        $this->app_key = $app_key;
    }

    public function sendByGet($params)
    {
        $params = http_build_query($params);
        $url=CCJ_URL.'?'.$params;

        $header = array();
        $nonce = rand();
        $now_time = time();
        $header[]="Org-Name:$this->session";
        $header[]="App-Key:$this->app_key";
        $header[]="Nonce:$nonce";
        $header[]="Timestamp:$now_time";
        $sig = $this->getSign($header,$this->app_secret);
        $header[]="Signature:$sig";
        $cl = curl_init($url);
        curl_setopt($cl, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cl,CURLOPT_HTTPHEADER,$header);
        curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, 0);
        $content = curl_exec($cl);
        curl_close($cl);
        return $content;
    }
    
    public function execute($params)
    {
        $params['s']=$this->dirname;

        $content=$this->sendByGet($params);
        
        return json_decode($content);
    }
    
    public function getSign($params,$secret_key)
    {
        unset($params[4]);
        $non = explode(":",$params[2]);
        $tim = explode(":",$params[3]);
        
        $sign=$non[1];
        $sign.=$secret_key;
        $sign.=$tim[1];
    
        $sign=sha1($sign);
        return $sign;
    }
}



























