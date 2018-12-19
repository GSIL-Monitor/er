<?php

class klClient{
    public $url = "http://openapi.kaola.com/router";
    public $app_secret;
    public $method;
    public $access_token;
    public $app_key;

    public function execute($params)
    {
        $params['method'] = $this->method;
        $params['access_token'] = $this->access_token;
        $params['app_key'] = $this->app_key;
        $params['timestamp'] = date('Y-m-d H:i:s');
        $params['sign'] = $this->generateSign($params);
        $params['secret'] = $this->app_secret;

        $url = $this->buildUrl($params, $this->url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($resp);
        return $res;
    }

    function generateSign($params)
    {
        ksort($params);
        $signed = "";
        foreach ($params as $key => $value) {
            $signed .= "$key"."$value";
        }
        $signed = $this->app_secret.$signed.$this->app_secret;
        $signed = strtoupper(md5($signed));
        return $signed;
    }

    function buildUrl($params, $url)
    {
        $url .= "?";
        foreach ($params as $key => $value) {
            $url .= "$key=".urlencode($value)."&";
        }
        $url = substr($url, 0, -1);
        return $url;
    }

}










