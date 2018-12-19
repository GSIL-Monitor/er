<?php

/**
 * vipshop.com OpenAPI 处理类 for Php SDK
 * @author:zo.zhao@vipshop.com
 * 2013.5
 */
class vipshopOpenApiHandler {

    /**
     * 唯品会Open Api请求类
     * @param type $sid             授权ID
     * @param type $source          授权资源
     * @param type $api_name        接口名称
     * @param type $api_url         接口地址
     * @param type $params          key=>value 格式请求参数
     * @param type $result_format   返回数据格式 json/xml
     * @return type
     */
    public static function get($app_key, $app_secret, $api_name, $api_url, $params = array(), $result_format = 'json') {
        $call_time = time();
        $token = md5($app_secret . $api_name . $call_time);

        $request_params = '';
        foreach ($params as $key => $value) {
            $request_params .='&' . $key . '=' . urlencode($value);
        }

        $api_request = $api_url . '?call_time=' . $call_time . '&sid=' . $app_key . '&token=' . $token . '&o=' . $result_format . $request_params;
        $result = self::getc($api_request);

        return $result;
    }

    /**
     * 请求接口并返回数据
     * 请根据您网站的php配置自行修改本方法，默认调用file_get_contents函数
     * @param type $url
     * @return type
     */
    public static function getc($url) {
        return file_get_contents($url);
    }

}

?>
