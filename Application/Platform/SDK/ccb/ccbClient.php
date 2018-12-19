<?php
require_once('AES.php');
/**
 * Class ccbClient
 */
class ccbClient{

    private $url = 'http://buy.ccb.com/integration/IntegrationService?wsdl';//正式
    //private $url = 'http://api.buy.ccb.com/integration/IntegrationService?wsdl';//测试
    //private  $url = '/data/personal/yyf/v2/sync/api/ccb/IntegrationService.wsdl';
    public $key;

    /**
     * @param $params
     * @return array|string
     */
    function execute($params){
        //生成流水号
        $params['head']['tran_sid'] = $this->sign();

        $tem = $this->arr2xml($params);     //array to xml
        //logx($tem,'arr2xml');

        $tem = iconv('UTF-8','GBK', $tem);  //utf-8 to gbk

        $tem = $this->encrypt($tem);        //加密 encrypt

        $temp = array();//整理参数
        $temp['ccbParam'] = $tem;
        $temp['tran_code'] = $params['head']['tran_code'];
        $temp['cust_id'] = $params['head']['cust_id'];
        $temp['tran_sid'] = $params['head']['tran_sid'];
        //logx($temp,'format');

        //http请求
        $retval = $this->soap($temp);       //http请求
        //logx($retval,'http');

        $retval = $this->decrypt($retval);  //解密 decrypt

        /*$encoding = mb_detect_encoding($retval,'UTF-8','GBK');
        if($encoding <> 'UTF-8')//判断编码并进行转码
        {
            $retval = iconv('GBK','UTF-8',$retval); //gbk to uft-8
        }*/

        try{
            $retval = iconv('GBK','UTF-8//IGNORE',$retval); //gbk to uft-8
        }catch(Exception $e){
                logx('ccb_sdk_error:'.$e->getMessage());
                return '';
        }
        
        //logx($retval,'utf-8');

        $retval = $this->xml2arr($retval);      //xml to array

        return $retval['response'];
    }

    //soap
    /**
     * @param $params
     * @return mixed
     */
    function soap($params){

        $tmp = array(
            //'proxy_host'=>'10.117.4.118',
            //'proxy_port'=>3128,
            //'trace' => 1,
            //'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            //'soap_version'   => SOAP_1_2,
            //'features' => SOAP_SINGLE_ELEMENT_ARRAYS
        );
        ini_set("soap.wsdl_cache_enabled", "0");
        //$client = new SoapClient($this->url);
        $client = new SoapClient($this->url,$tmp);

        try{
            $retval = $client->service($params);
        }catch (SoapFault $e){
            //echo $client->__getLastRequest();
            //echo $client->__getLastResponse();
            logx(print_r($e->getMessage(),true));
            exit;
        }

        return $retval;
    }

    //http请求
    /**
     * @param $params
     * @return mixed
     */
    function http($params){

        $header = array(
            "charset=GBK",
        );
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $retval = curl_exec($ch);
        /*if (curl_errno($ch)) {
            print curl_error($ch);
        }*/
        curl_close($ch);

        //解析返回结果
        return $retval;
    }

    function sign(){
        //$res =  uniqid();////13位的字符串
        $res = date('YmdHis',time());
        $res = 'WDT'.$res.rand(100,999);
        return $res;
    }

    //array 2 xml
    /**
     * @param $arr
     * @param int $dom
     * @param int $item
     * @return mixed|string
     */
    function arr2xml($arr,$dom=0,$item=0){
        if (!$dom){
            $dom = new DOMDocument('1.0','UTF-8');
        }
        if(!$item){
            $item = $dom->createElement("request");
            $dom->appendChild($item);
        }
        foreach ($arr as $key=>$val){
            $itemx = $dom->createElement(is_string($key)?$key:"item");
            $item->appendChild($itemx);
            if (!is_array($val)){
                $text = $dom->createTextNode($val);
                $itemx->appendChild($text);
            }else {
                $this->arr2xml($val,$dom,$itemx);   //递归
            }
        }
        $retval = $dom->saveXML();
        $retval = str_replace("\n","",$retval);
        $retval = str_replace("\r","",$retval);
        $retval = str_replace("\r\n","",$retval);
        return $retval;
    }

    //xml 2 array
    /**
     * @param $xml
     * @return array
     */
    function xml2arr($xml){
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches))
        {
            $count = count($matches[0]);
            $arr = array();
            for($i = 0; $i < $count; $i++)
            {
                $key= $matches[1][$i];
                $val = $this->xml2arr( $matches[2][$i] );   //递归
                if(array_key_exists($key, $arr))
                {
                    if(is_array($arr[$key]))
                    {
                        if(!array_key_exists(0,$arr[$key]))
                        {
                            $arr[$key] = array($arr[$key]);
                        }
                    }else{
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                }else{
                    $arr[$key] = $val;
                }
            }
            return $arr;
        }else {
            return $xml;
        }
    }

    /**
     * @param $params
     * @return string
     */
    function encrypt($params){
        $aes = new AES();
        $aes->set_key($this->key);

        //$aes->require_pkcs5();
        return $aes->encrypt($params);
    }

    /**
     * @param $params
     * @return string
     */
    function decrypt($params){
        $aes = new AES();
        $aes->set_key($this->key);
        return $aes->decrypt($params);
    }

    /**
     * @return mixed
     */
    function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    function setKey($key)
    {
        $this->key = $key;
    }


}
