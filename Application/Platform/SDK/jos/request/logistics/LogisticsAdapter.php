<?php
define('LOGISTICS_GET_WAYBILL',1);//获取物流单号
define('LOGISTICS_GET_DTB',2);//获取大头笔
define('LOGISTICS_STOCK_QUERY',3);//商家单号库存查询
define('LOGISTICS_WAYBILL_UNBIND',4);//单号取消
define('LOGISTICS_USER_ID',5);//淘宝商家店铺授权 user_id
define('LOGISTICS_UPDATE_WAYBILL',6);//走物流更新接口
define('LOGISTICS_TEMPLATE_URL',7);//获取菜鸟url模板
define('LOGISTICS_SEARCH_WAYBILL',8);//商家物流信息查询
define('GET_SELLER_VENDER_INFO', 9);//获取商家基本信息


class LogisticsAdapter
{
    public $retryCount = 0;
    public $logistics_bill_type = 0; //物流类型
    public $logisticsClient = null;  //物流对象
    private $sendParams = '';  //发送给物流接口的数据参数
    private $received = '';  //接收到的接口返回数据
    private $connet_error = '';//连接异常信息

    function __construct($bill_type,$logistics_auto)
    {
        //物流对象
        $logistics_company = &$this->logisticsClient;
        //物流类型
        $this->logistics_bill_type = $bill_type;
        switch($bill_type)
        {
            case 9://京东阿尔法电子面单
            {
                require_once(ROOT_DIR . '/SDK/jos/request/logistics/logistics_alpha.php');
                $logistics_company = new LogisticsALPHA($logistics_auto);
                break;
            }
			case 2://菜鸟电子面单
			{
				require_once(ROOT_DIR . '/SDK/jos/request/logistics/logistics_yz.php');
                $logistics_company = new LogisticsYZ($logistics_auto);
                break;
			}
            default: break;
        }

    }

    public function sendByPost($url,$params_array) //retry: post失败之后重试次数,重试一次(旧机制：如果要重试，则要求超时时间*请求次数<120；新机制：重新放到任务队列中)
    {
        $this->sendParams = $params_array;
        try
        {
            $result = $this->postData($url,$params_array);
            return $result;
        }
        catch (Exception $e)//出现异常的话,重新放到任务队列中
        {
            $this->connet_error = $e->getMessage();
            $result['code'] = -1;
            $result['error_msg'] = $e->getMessage();
            $result['retry_flag'] = 1;

            return $result;
        }
    }
    public function postData($url,$params_array) //post数据
    {
        $post_params = @$params_array['post_params'];
        if(!isset($post_params))
            $post_params = array();

        $headers = @$params_array['headers'];
        if(!isset($headers))
            $headers = array();

        if(is_array($post_params))
            $post_data = http_build_query($post_params);
        else
            $post_data = $post_params;
        $length = strlen($post_data);
        $cl = curl_init($url);
        curl_setopt($cl, CURLOPT_POST, true);
        curl_setopt($cl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        curl_setopt($cl,CURLOPT_HTTPHEADER,array_merge(array("Content-Type: application/x-www-form-urlencoded","Content-length: ".$length),$headers));
        curl_setopt($cl,CURLOPT_TIMEOUT,60);  //设置响应超时时间
        curl_setopt($cl,CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);

        $response = curl_exec($cl);
        if (curl_errno($cl)) //有可能接口响应超时
        {
            throw new Exception(curl_error($cl),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($cl, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new Exception($response,$httpStatusCode);
            }
        }
        curl_close($cl);

        //京东阿尔法不需要对响应信息进行urldecode 
        if($this->logistics_bill_type != 9)
        {
            $response = urldecode($response);
        }
        return $response;
    }

    public function sendRequest($api_method,$data,$sid='',&$db = '') //执行请求
    {
        $logisticsCompany = &$this->logisticsClient;
        if($logisticsCompany == null)
        {
            return array('code' => -99, 'error_msg' => '不支持的物流类型', 'retry_flag' => 0);
        }
        if(!$logisticsCompany->formatParams($api_method,$data))
        {
            return array('code' => -99, 'error_msg' => '该物流不支持此功能', 'retry_flag' => 0);
        }
        switch($logisticsCompany->apiType)
        {
            case 0: //使用http post发送请求
            {
                $resp = $this->sendByPost($logisticsCompany->apiUrl, $logisticsCompany->sendParams);
                $this->received = $resp;
                $format_result  = $logisticsCompany->formatResult($api_method,$resp,$db);
                break;
            }
            default: return array('code' => -99, 'error_msg' => '类型不正确', 'retry_flag' => 0);
        }
        return $format_result;
    }
    public function getSendParams()
    {
        return $this->sendParams;
    }
    public function getReceived()
    {
        return $this->received;
    }

    //获取第一次连接失败的原因
    public function getConError()
    {
        return $this->connet_error;
    }

}

