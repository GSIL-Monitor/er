<?php

class vdianClient{

    public $token;
    public $method;
    public $version;//还有1.1版本，但是1.1版本商品接口还无法使用
    public $format = 'json';
    public $url = 'https://api.vdian.com/api';

    const API_SELF_TOKEN = 'https://api.vdian.com/token';//自用型获取token
    const API_SERVER_CODE = 'https://api.vdian.com/oauth2/authorize';//服务型获取code
    const API_SERVER_TOKEN = 'https://api.vdian.com/oauth2/access_token';//服务型获取token
    const API_SERVER_REFRESHTOKEN = 'https://api.vdian.com/oauth2/refresh_token';//服务型refreshtoken

    public function execute($request, $version='1.0')
    {
        $this->version = $version;
        //拼装系统参数
        $public = array(
            'method' => $this->method,
            'access_token' => $this->token,
            'version' => $this->version,//可选
            'format' => $this->format//可选
        );
        //参数组合
        $params = array(
            'param' => json_encode($request),
            'public' => json_encode($public)
        );
        //发送http请求
        try{
            $retval = $this->http($params);
        }catch (Exception $e) {
            //todo  要处理异常，记录日志
            //print_r($e->getMessage());
            return false;
        }
        return $retval;
    }

    public function http($params=array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if(empty($params))//GET
        {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        else {//POST
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $retval = curl_exec($ch);
        curl_close($ch);
        //解析返回结果
        $retval = json_decode($retval);
        return $retval;
    }
    public function code()//获取code
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);// 不需要页面内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不直接输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// 返回最后的Location
        curl_exec($ch);
        $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);// 获取有效的URL
        curl_close($ch);
        $code = parse_url($info);
        $code = explode('&', $code['query']);
        foreach ($code as $v)
        {
            $code = explode('=', $v);
            if($code[0] == 'code')break;
        }
        return $code[0] == 'code' ? $code[1]: 'no code';
    }

}