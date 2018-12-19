<?php
require_once (TOP_SDK_DIR . '/youzan/YZApiProtocol.php');
require_once (TOP_SDK_DIR . '/youzan/YZHttpClient.php');
date_default_timezone_set('Asia/Shanghai'); 

class YZSignClient {
    const VERSION = '1.0';

    private static $signUrl = 'https://open.youzan.com/api/entry/';
    private $appId;
    private $appSecret;
    private $format = 'json';
    private $signMethod = 'md5';

    public function __construct($appId, $appSecret) {
        if ('' == $appId || '' == $appSecret) throw new Exception('appId 和 appSecret 不能为空');

        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function get($method, $methodVersion, $params = array()) {
        return $this->parseResponse(
            YZHttpClient::get($this->url($method,$methodVersion), $this->buildRequestParams($method, $params))
        );
    }

    public function post($method, $methodVersion, $params = array(), $files = array()) {
        return $this->parseResponse(
            YZHttpClient::post($this->url($method,$methodVersion), $this->buildRequestParams($method, $params), $files)
        );
    }

    public function url($method, $methodVersion){
        $method_array=explode(".", $method);
        $method='/'.$methodVersion.'/'.$method_array[count($method_array)-1];
        array_pop($method_array);
        $method=implode(".", $method_array).$method;
        $url=self::$signUrl.$method;
        return $url;
    }

    public function setFormat($format) {
        if (!in_array($format, YZApiProtocol::allowedFormat()))
            throw new Exception('设置的数据格式错误');

        $this->format = $format;

        return $this;
    }

    public function setSignMethod($method) {
        if (!in_array($method, YZApiProtocol::allowedSignMethods()))
            throw new Exception('设置的签名方法错误');

        $this->signMethod = $method;

        return $this;
    }

    private function parseResponse($responseData) {
        $data = json_decode($responseData, true);
        if (null === $data) throw new Exception('response invalid, data: ' . $responseData);
        return $data;
    }

    private function buildRequestParams($method, $apiParams) {
        if (!is_array($apiParams)) $apiParams = array();
        if ($this->appId){

        }
        $pairs = $this->getCommonParams($method);
        foreach ($apiParams as $k => $v) {
            if (isset($pairs[$k])) throw new Exception('参数名冲突');
            $pairs[$k] = $v;
        }
        $pairs[YZApiProtocol::SIGN_KEY] = YZApiProtocol::sign($this->appSecret, $pairs, $this->signMethod);
        return $pairs;
    }

    private function getCommonParams($method) {
        $params = array();
        $params[YZApiProtocol::APP_ID_KEY] = $this->appId;
        $params[YZApiProtocol::METHOD_KEY] = $method;
        $params[YZApiProtocol::TIMESTAMP_KEY] = date('Y-m-d H:i:s');
        $params[YZApiProtocol::FORMAT_KEY] = $this->format;
        $params[YZApiProtocol::SIGN_METHOD_KEY] = $this->signMethod;
        $params[YZApiProtocol::VERSION_KEY] = self::VERSION;
        return $params;
    }
}