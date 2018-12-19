<?php
/**
 * 飞牛api
 * @author fq
 */
class FnClient
{
    const   API_COMMON_URL_PREFIX = "http://open.feiniu.com/call.do"; //测试环境接口
    const   VERSON                = "1.0"; //接口版本
    const   CURL_TIMEOUT          = 30;  //curl访问超时时间
    private $appKey               ="";
    private $appSecret            ="" ;
    private $authSession          = "";

    
    function __construct($appKey="wdtERPMerchant" ,$appSecret = "5332b04b-93d5-4c13-a876-a74028474214"){
        if ('' == $appKey || '' == $appSecret)
             throw new Exception('appKey 、 appSecret不能为空');
        
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }
    
    /**
     * 生成auth_token
     * 供其他操作使用
     */
    function getAuthSession($userId = "hupan",$password='fn123456'){
        $account = array(
            'userID' => $userId,
            'password' => $password,
        );
        $params = array(
            'method'=> 'fn.user.seller.getsession',
            'params'=>@json_encode($account),
        );
        $retval = $this->sendDataByCurl($params);
        if($retval->code == 100){
            $auth_token = $retval->data->auth_token;
            return $auth_token ;
        }
    }
    
    /**
     * 设置auth_token
     * @param unknown $authSession
     */
    function setAuthSession($authSession){
        $this->authSession = $authSession;
    }
    
    /**
     * 整合参数生成验证签名
     * @param &array $map
     */
    function getSign(&$map = array()){
        $notNullArray = array();
        $paramList = $this->appSecret;
        $innerSign  = "";
        if($map != null && count($map)>0){ //数据不为空
            foreach ($map as $k=>$v){
                if(trim($k) != "sign"){//组织参数时去掉sign参数
                    if(!empty($v)){
                        $notNullArray[] = $k;  //将不为空值对应的key值 加入数组
                    }
                }
            }
            sort($notNullArray); //对key值数据进行排序
            foreach ($notNullArray as $value){
                $paramList .= $value;
                $paramList .= urlencode($map[$value]);
            }
            $paramList .= $this->appSecret;
            $innerSign = strtoupper(md5($paramList));
            $map['sign'] = $innerSign;
        }
    }
    
    /**
     * 组装数据
     * @param unknown $param
     * @param string $isNeedSign
     * @return Ambigous <multitype:, multitype:string NULL >
     */
    function formatParams($param,$isNeedSign = true){
        $params = array(
            'version'=> self::VERSON,
            'appKey'=>$this->appKey,
            'sign' =>"",
            'auth_token'=>$this->authSession,
        );
        if(is_array($param)){$params = array_merge($param,$params);}
        if($isNeedSign){
            $this->getSign($params);
        }
        return $params;
    }
    
    /**
     * 发送请求数据
     * @param unknown $params 携带的参数
     * @param number $method 发送方式 post =1/get other
     * @param string $isNeedFormatParams 是否需要将数据进行格式化 = true
     * @param string $isNeedSign    是否需要生成签名 =true
     * @param number $decode        编码方式 json =1/array 2/None other
     * @return Ambigous <mixed, unknown>
     */
    public function sendDataByCurl($params,$method = 1,$isNeedFormatParams = true,$isNeedSign = true, $decode = 1){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_COMMON_URL_PREFIX );
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
        if($isNeedFormatParams){
            $params = $this->formatParams($params);
        }
        if(is_array($params)){
            $params = http_build_query($params);
        }
        if($method === 1){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $content = curl_exec($ch);
        $content = ($decode == 1)?json_decode_safe($content):(($decode == 2)?json_decode_safe($content,true):$content);
        curl_close($ch);
        return $content;
    }

}

?>