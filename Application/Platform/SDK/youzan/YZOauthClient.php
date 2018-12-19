<?php
require_once (TOP_SDK_DIR . '/youzan/YZHttpClient.php');

class YZOauthClient{

    private static $oauthUrl = 'https://open.youzan.com/oauth/token';

    public function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    /**
     * 获取access_token
     */
    public function getToken( $type, $keys ) {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        }
        return $this->parseResponse(
            YZHttpClient::post(self::$oauthUrl, $params)
        );
    }

    private function parseResponse($responseData) {
        $data = json_decode($responseData, true);
        if (null === $data) throw new Exception('response invalid, data: ' . $responseData);
        return $data;
    }
}
