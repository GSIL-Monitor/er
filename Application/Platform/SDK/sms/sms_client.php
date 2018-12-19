<?php
class sms_client
{
    public $appKey;
    public $appSecret;
    public $sid;
    public $gateUrl = "smsapi.wangdian.cn/api.php";
    public $apiMethod;
    public $format = "json";

    public function execute($apiParams = array())
    {
        $sysParams["timestamp"] = time();
        $sysParams["appKey"] = $this->appKey;
        $sysParams["sid"] = $this->sid;
        $sysParams["apiMethod"] = $this->apiMethod;

        if(!is_array($apiParams)) die('the apiParams is not array!');

        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));
        $requestUrl = $this->gateUrl."?";
        foreach ($sysParams as $sysParamKey => $sysParamValue)
        {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }

        $requestUrl = substr($requestUrl, 0, -1);
        $resp = $this->curl($requestUrl, $apiParams);
        unset($apiParams);

        $respObject = json_decode($resp);
        return $respObject;
    }

    protected function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (is_array($postFields) && 0 < count($postFields))
        {
            $postBodyString = "";
            foreach ($postFields as $k => $v)
            {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            $header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));

        }
        $reponse = curl_exec($ch);

        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($reponse,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }

    protected function generateSign($params)
    {
        ksort($params);

        $stringToBeSigned = $this->appSecret;
        foreach ($params as $k => $v)
        {
            $stringToBeSigned .= "$k$v";
        }
        unset($k, $v);
        $stringToBeSigned .= $this->appSecret;

        return strtoupper(md5($stringToBeSigned));
    }

}