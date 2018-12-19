<?php

class RrdClient{
    public $apiUrl = "http://apis.wxrrd.com/router/rest";
    public $appid;
    public $secret;
    public $method;
    public $access_token;
    public $refresh_token;



    //获取accessToken
    public function getAccessToken(){
        $tokenParams['appid'] = $this->appid;
        $tokenParams['secret'] = $this->secret;
        $tokenParams['grant_type'] = "refresh_token";
        $tokenParams['refresh_token'] = $this->refresh_token;
        $tokenParams['format'] = "json";
        $tokenParams['redirect_uri'] = "http://ekb.wangdian.cn/auth.php";
        //拼接url
        $resp = $this->sendByPost($this->apiUrl, $tokenParams);
        $res = json_decode($resp);
        return $res;
    }

    public function execute($sysParams=NULL,$mode){
        $sysParams['appid']  = $this->appid;
        $sysParams['secret'] = $this->secret;
        $sysParams['method'] = $this->method;
        $sysParams['timestamp'] = date("Y-m-d H:i:s");
        $sysParams['access_token'] = $this->access_token;
        $sysParams['sign'] = $this->generateSign($sysParams);
        //http请求
        if ($mode == "GET") {
            $sysUrl =  $this->buildUrl($sysParams);
            $resp = $this->sendByGet($sysUrl);
        }
        if ($mode == "POST") {
            $resp = $this->sendByPost($this->apiUrl, $sysParams);
        }
        $res = json_decode($resp);
        return $res;
    }

//签名
    public function generateSign($params){
        ksort($params);
        $signed = "";
        foreach ($params as $key => $value) {
            $signed .= "$key=$value&";
        }
        $signed = substr($signed, 0,-1);
        return strtoupper(md5($signed));
    }

//拼接url
    public function buildUrl($params) {
        $requestUrl = $this->apiUrl . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;
    }

//get请求
    public function sendByGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $reponse = curl_exec($ch);
        curl_close($ch);
        return $reponse;
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