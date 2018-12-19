<?php
require_once (TOP_SDK_DIR . '/youzan/YZApiProtocol.php');
require_once (TOP_SDK_DIR . '/youzan/YZHttpClient.php');

class YZTokenClient {
    private static $tokenUrl = 'https://open.youzan.com/api/oauthentry/';

    public function __construct($accesstoken) {
        if ('' == $accesstoken) throw new Exception('accesstoken不能为空');
        $this->accesstoken = $accesstoken;
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
        $url=self::$tokenUrl.$method;
        return $url;
    }

    private function parseResponse($responseData) {
        $data = json_decode($responseData, true);
        if (null === $data) throw new Exception('response invalid, data: ' . $responseData);
        return $data;
    }

    private function buildRequestParams($method, $apiParams) {
        if (!is_array($apiParams)) $apiParams = array();
        $pairs = $this->getCommonParams($this->accesstoken, $method);
        foreach ($apiParams as $k => $v) {
            if (isset($pairs[$k])) throw new Exception('参数名冲突');
            $pairs[$k] = $v;
        }

        return $pairs;
    }

    private function getCommonParams($accessToken, $method) {
        $params = array();
        $params[YZApiProtocol::TOKEN_KEY] = $accessToken;
        $params[YZApiProtocol::METHOD_KEY] = $method;
        return $params;
    }
}
