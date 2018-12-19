<?php
/**
 * 美丽说开放平台sdk
 * User: MLS
 * Date: 15-1-5
 * Time: 下午8:11
 */

class MlsClient{

    //接口地址
    public $apiurl = 'https://api.open.meilishuo.com/router/rest';
    public $secretKey;
    public $sessionKey;
    public $app_key;
    public $format = "json";

    protected $signMethod = "md5";
    protected $apiVersion = "1.0";
    protected $sdkVersion;

    public function execute( $request ){
          $sysParams['app_key'] = $this->app_key; //
          $sysParams["v"] = $this->apiVersion;
          $sysParams["format"] = $this->format;
          $sysParams["sign_method"] = $this->signMethod;
          $sysParams["timestamp"] = date("Y-m-d H:i:s");
          $sysParams['session'] = $this->sessionKey; //

          //获取业务参数
          $appParams = $request->getAppParams();
          $appParams['method'] = $request->getApiMethod();
          //签名
          $sysParams["sign"] = $this->generateSign(array_merge($appParams, $sysParams));
		  $sysParams = array_merge($appParams, $sysParams);
          //发起http请求
        if ($request->getRequestMode() == "GET") {
            $sysUrl =  $this->buildUrl($sysParams);
            $resp = $this->sendByGet($sysUrl);
        }

        if ($request->getRequestMode() == "POST") {
            $postFields = array();
            foreach ($sysParams as $key => $value) {
                $postFields[$key] = $value;
            }

            $resp = $this->sendByPost($this->apiurl, $postFields);
        }
		
		$resp = json_decode_safe($resp);
        return $resp;
    }


    /**
     * 签名
     * @param  $params 业务参数
     * @return
     */
    public function generateSign($params)
    {
        ksort($params);

        $signed = $this->secretKey;
        foreach ($params as $k => $v)
        {
            $signed .= "$k$v";
        }
        unset($k, $v);
        $signed .= $this->secretKey;

        return strtoupper(md5($signed));
    }

    /**
     * 拼接系统参数
     * @param $params
     * @return string
     */
    public function buildUrl($params) {
        $requestUrl = $this->apiurl . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;
    }


    /*
     * post方式
     *
     */

    public function sendByPost($url, $postFields = NULL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }

        curl_close($ch);

        return $reponse;
    }

    /*
     *get方式
     *
     */

    public function sendByGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $reponse = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        return $reponse;
    }

}
