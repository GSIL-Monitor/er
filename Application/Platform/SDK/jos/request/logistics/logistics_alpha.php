<?php
/*
 * 物流接口通讯类.
 * 接口调用方式: http post  物流需要更改
 * 数据格式: xml
 */
class LogisticsALPHA
{
    
    public $app_key;             //授权app_key
    public $app_secret;          //授权密钥
    public $access_token;        //授权令牌
    public $baseUrl = 'https://api.jd.com/routerjson'; //基础地址
    public $apiUrl;             //接口地址
    public $method;             //请求方法
    public $apiType = 0;        //接口调用形式 0为http post 1为webservice
    public $dataType = 1;       //数据格式 0-xml  1-json
    public $sendParams = '';
    public $weblogParams = '';  //记录weblog所需要的参数

    //构造函数,初始化仓储的授权信息
    function __construct($logistics_auto)
    {
        //物流初始化信息
        $this->curTime      = date("Y-m-d H:i:s");
        $this->app_key      = isset($logistics_auto['appkey'])?$logistics_auto['appkey']:'';
        $this->app_secret   = isset($logistics_auto['appsecret'])?$logistics_auto['appsecret']:'';
        $this->access_token = isset($logistics_auto['sessionkey'])?$logistics_auto['sessionkey']:'';
    }


    //把数据格式化成对方需要格式,然后组成一个post请求参数列表
    public function formatParams($api_method,$data)
    {
        switch($api_method) //根据不同的业务类型(即接口) 格式化该业务所需的数据内容.
        {
            
            case LOGISTICS_GET_WAYBILL: //推送订单
            {
                $content = $this->logisticsGetWaybill($data);
                $this->method = 'jingdong.ldop.alpha.waybill.receive';
                break;
            }
            case LOGISTICS_GET_DTB: //获取大头笔
            {
                $content = $this->logisticsGetDtb($data);
                $this->method = 'jingdong.ldop.alpha.vendor.bigshot.query';
                break;
            }
            case LOGISTICS_STOCK_QUERY://查询库存剩余量
            {
                $content = $this->getQueryAddContent($data);
                $this->method = 'jingdong.ldop.alpha.vendor.stock.queryByProviderCode';
                break;
            }
            case LOGISTICS_SEARCH_WAYBILL://根据商家编码查询商家所有审核成功的签约信息
            {
                $content = $this->getQuerySignSuccess($data);
                $this->method = 'jingdong.ldop.alpha.provider.sign.success';
                break;
            }
            case GET_SELLER_VENDER_INFO://查询商家基本信息
            {
                $content = $this->getSellerVenderInfo($data);
                $this->method = 'jingdong.seller.vender.info.get';
                break;
            }
            case LOGISTICS_WAYBILL_UNBIND://  解绑订单
            {
                $content = $this->cancelTradeWaybill($data);
                $this->method = 'jingdong.ldop.alpha.waybill.api.unbind';
                break;
            }
            default : break;
        }
		
        //生成url
        $params = array(
            'app_key'       =>  $this->app_key,
            'app_secret'    =>  $this->app_secret,
            'access_token'  =>  $this->access_token,
            'v'             =>  '2.0',
            'method'        =>  $this->method,
            );

        if ($api_method == GET_SELLER_VENDER_INFO) {
        $app_params = array(
                'ext_json_param ' => json_encode($content)
            );
        }
        else
        {
            $app_params = array(
            '360buy_param_json' => json_encode($content)
            );
        }
        //更新url
        $this->apiUrl = $this->baseUrl;
        $this->apiUrl .= '?'.http_build_query($params); 

        $this->sendParams['post_params']= $app_params;
        return true;
    }
    private function logisticsGetWaybill($data)
    {
		$Push_info= array();
        $orderInfo = $data['order_data'];
        $sender_address = $orderInfo['sender_address'];
        $receiver_address = $orderInfo['receiver_address'];
        $logisticsAuto = $data['logistics_auto'];
        $PushInfo = array(
            'waybillType'           =>      $orderInfo['send_type'],  //*运单类型
            'waybillCount'          =>      '1',  //*所需运单的数量
            'providerCode'          =>      $logisticsAuto['logistics'],  //*承运商编码
            'branchCode'            =>      isset($logisticsAuto['branch_name'])?$logisticsAuto['branch_name']:'',  //*承运商发货网点编码加盟型快递公司必传
            'settlementCode'        =>      isset($logisticsAuto['settlementCode'])?$logisticsAuto['settlementCode']:'',  //*财务结算编码，直营型快递公司必传
            'salePlatform'          =>      $orderInfo['salePlatform'],  //*销售平台
            'platformOrderNo'       =>      $orderInfo['platformOrderNo'],  //*平台订单号，即pop订单号
            'vendorCode'            =>      $logisticsAuto['customer_code'],  //*商家编码
            'vendorName'            =>      $logisticsAuto['vendorName'],  //*商家名称
            'vendorOrdercode'       =>      $orderInfo['stockout_no'],  //*商家自有订单号
            'weight'                =>      sprintf("%.2f",$orderInfo['calc_weight']),  //*重量，单位为千克 两位小数
            'volume'                =>      '0.00',
          //  'goodsName'             =>      substr($orderInfo['goodsinfo'],0,100),  //商品名称
            'promiseTimeType'       =>      0,        //*承诺时效类型
            'payType'               =>      0,  //*付款方式0-在线支付
            'goodsMoney'            =>      $orderInfo['goods_total_amount'],  //*商品金额 两位小数
            'shouldPayMoney'        =>      '0.00',  //*代收金额 两位小数
            'needGuarantee'         =>      false,  //*是否要保价（系统暂不开放报价业务）
            'guaranteeMoney'        =>      '0.00',        //*保价金额 两位小数
            'receiveTimeType'       =>      0,  //*收货时间类型，0任何时间，1工作日2节假日
            'warehouseCode'         =>      isset($orderInfo['warehouse_no'])?$orderInfo['warehouse_no']:'',  //发货仓编码
            'secondSectionCode'     =>      '',  //二段码
            'thirdSectionCode'      =>      '',  //三段码
            'remark'                =>      '',  //备注
            'expressPayMethod'      =>      $logisticsAuto['pay_type'],  //快递费付款方式(顺丰必填)
            'expressType'           =>      $logisticsAuto['logistic_type'],  //产品类型(顺丰必填)
            );
        $PushInfo['fromAddress'] = array(
            'provinceId'            =>      $sender_address['province_id'],     //省/直辖市id
            'provinceName'          =>      $sender_address['province'],     //省/直辖市名称
            'cityId'                =>      $sender_address['city_id'],     //市id
            'cityName'              =>      $sender_address['city'],     //市名称
            'countryId'             =>      $sender_address['district_id'],     //区/县id
            'countryName'           =>      $sender_address['district'],     //区/县名称
            'address'               =>      $sender_address['address'],     //发货详细地址
            'contact'               =>      $orderInfo['sender_name'],     //发货联系人
            'phone'                 =>      empty($orderInfo['sender_telno'])?$orderInfo['sender_mobile']:$orderInfo['sender_telno'],     //发货人电话
            'mobile'                =>      empty($orderInfo['sender_mobile'])?$orderInfo['sender_telno']:$orderInfo['sender_mobile'],     //发货人手机     
        );
        $PushInfo['toAddress'] = array(
            'provinceId'            =>      $receiver_address['province_id'],     //省/直辖市id
            'provinceName'          =>      $receiver_address['province'],     //省/直辖市名称
            'cityId'                =>      $receiver_address['city_id'],     //市id
            'cityName'              =>      $receiver_address['city'],     //市名称
            'countryId'             =>      $receiver_address['district_id'],     //区/县id
            'countryName'           =>      $receiver_address['district'],     //区/县名称
            'address'               =>      $receiver_address['address'],     //发货详细地址
            'contact'               =>      $orderInfo['receiver_name'],     //收货联系人
            'phone'                 =>      ($orderInfo['receiver_telno']!='')?$orderInfo['receiver_telno']:$orderInfo['receiver_mobile'],     //收货人电话
            'mobile'                =>      $orderInfo['receiver_mobile'],     //收货人手机 
        );
        $Push_info['content'] = $PushInfo;
        return $Push_info;
    }
    private function logisticsGetDtb($data)
    {
        $PushInfo = array(
            'waybillCode'   =>   $data['waybillCode'],
            'providerCode'  =>   $data['providerCode']
        );
        return $PushInfo;
    }
    //查询接口单量情况
     private function getQueryAddContent($data)
     {
         
          $queryInfo =  array(
                "vendorCode"        =>    $data['logistics_key']['customer_code'], //商家编码
                "providerCode"      =>    $data['logistics_key']['logistics'],//承运商编码
                "branchCode"        =>    $data['logistics_key']['branch_name'] //网点编码
          );
          
          //直接传递是json格式的数据
          return $queryInfo;
     }
     //查询商家签约成功的物流信息
    private function getQuerySignSuccess($data)
    {
     
        $queryInfo =  array(
            "vendorCode"    =>  $data['vendorCode'], //商家编码
        );
        return $queryInfo;
    }
    //查询商家基本信息
    private function getSellerVenderInfo($date)
    {
        $queryInfo = array();
        return $queryInfo;
    }     
    //解绑订单
     private function cancelTradeWaybill($data)
     {
         
          $queryInfo =  array(
               // "platformOrderNo"        =>    $data['platformOrderNo'], //平台订单号
                "providerCode"           =>    $data['providerCode'],//承运商编码
                "waybillCode"        =>    $data['waybillCodeList'], //物流单号
                //"operatorTime"           =>    $data['operatorTime'],//操作时间
                //"operatorName"           =>    $data['operatorName']//操作人
          );
          
          return $queryInfo;
          
     }
    
    
    public function getSendParams()
    {
        return $this->sendParams;
    }

    //对方接口反馈信息的格式化
    public function formatResult($api_method,$retval,&$db)
    {
        //code<0时为系统级别错误,code>0时为应用级别错误

        //先验证系统级别错误
        if (empty($retval))
        {
            return array('code' => -1, 'error_msg' => '京东服务器失败', 'retry_flag' => 0);
        }
        if (is_array($retval))
        {
            return $retval;
        }
		$data = json_decode_safe($retval,true);
		if ($data === false || $data === null)
        {
            return array('code' => -2, 'error_msg' => '京东返回信息异常:'.print_r($retval,true), 'retry_flag' => 0);
        }
        $retval = $data;
        $error_response = isset($data['error_response'])?$data['error_response']:"";
        if($error_response!='')
        {
            $en_desc = isset($error_response['en_desc'])?$error_response['en_desc']:"";
            return array('code' => 1, 'error_msg' => '基础信息异常:'.$error_response['zh_desc'].','.$en_desc, 'retry_flag' => 0);
        }
        $revinfo = '';
        switch($api_method)
        {
            case LOGISTICS_GET_WAYBILL:
                if($retval['statusCode'] == 0)
                {
                    $revinfo['waybillCode'] = isset($retval['data']['waybillCodeList']['0'])?$retval['data']['waybillCodeList']['0']:'';
                    $revinfo['platformOrderNo'] = isset($retval['data']['platformOrderNo'])?$retval['data']['platformOrderNo']:'';
                }
                elseif ($retval['statusCode'] == 1) 
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 1, 'error_msg' => '获取单号参数异常，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 2, 'error_msg' => '获取单号失败，京东返回:'.$error_msg, 'retry_flag' => 0 );
                }
                break;
            case LOGISTICS_GET_DTB:
                if(!isset($retval['jingdong_ldop_alpha_vendor_bigshot_query_responce']['resultInfo']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval = $retval['jingdong_ldop_alpha_vendor_bigshot_query_responce']['resultInfo'];
                if ($retval['statusCode'] == 0)
                {
                    $revinfo = array(
                        'waybillCode' => isset($retval['data']['waybillCode'])?$retval['data']['waybillCode']:'',//运单号
                        'bigShotName' => isset($retval['data']['bigShotName'])?$retval['data']['bigShotName']:'',//大头笔名称
                        'bigShotCode' => isset($retval['data']['bigShotCode'])?$retval['data']['bigShotCode']:'',//大头笔编码
                        'gatherCenterName' => isset($retval['data']['gatherCenterName'])?$retval['data']['gatherCenterName']:'',//集包地名称
                        'gatherCenterCode' => isset($retval['data']['gatherCenterCode'])?$retval['data']['gatherCenterCode']:'',//集包地编码
                        'branchName' => isset($retval['data']['branchName'])?$retval['data']['branchName']:'',//目的网点名称
                        'branchCode' => isset($retval['data']['branchCode'])?$retval['data']['branchCode']:'',//目的网点编码
                        'secondSectionCode' => isset($retval['data']['secondSectionCode'])?$retval['data']['secondSectionCode']:'',//二段码
                        'thirdSectionCode' => isset($retval['data']['thirdSectionCode'])?$retval['data']['thirdSectionCode']:'',//三段码
                    );
                }                
                else if ($retval['statusCode'] == 1) 
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 1, 'error_msg' => '获取大头笔参数异常，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 2, 'error_msg' => '获取大头笔失败，京东返回:'.$error_msg, 'retry_flag' => 0 );
                }
                break;
            case LOGISTICS_STOCK_QUERY:
                
                if(!isset($retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusCode']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                else if($retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusCode']==1)
                {
                    $error_msg = $retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusMessage'];
                    return array('code' => 1, 'error_msg' => '查询单号参数异常，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusCode']==-1)
                {
                    $error_msg = $retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusMessage'];
                    return array('code' => 2, 'error_msg' => '查询单号失败，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['statusCode']==0)
                {
                    if(!isset($retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['data'][0]))
                    {
                        $error_msg = "接口返回数据为空";
                        return array('code' => 1, 'error_msg' => '查询单号参数异常,'.$error_msg, 'retry_flag' => 0);
                    }
                    $data =  $retval['jingdong_ldop_alpha_vendor_stock_queryByProviderCode_responce']['resultInfo']['data'][0];
                    $revinfo['data']['providerName'] = $data['providerName'];
                    $revinfo['data']['providerCode'] = $data['providerCode'];
                    $revinfo['data']['amount']       = $data['amount'];
                    $revinfo['data']['branchCode']   = $data['branchCode'];  
                    $revinfo['data']['vendorCode']   = isset($data['vendorCode'])?$data['vendorCode']:"";  
                }
                break;
            case GET_SELLER_VENDER_INFO:
                if(!isset($retval['jingdong_seller_vender_info_get_responce']['code']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval = $retval['jingdong_seller_vender_info_get_responce'];
                if($retval['code'] == 0)
                {
                    if(!isset($retval['vender_info_result']))
                    {
                        $error_msg = "接口返回数据为空";
                        return array('code' => 1, 'error_msg' => '查询商家信息失败，京东返回:'.$error_msg, 'retry_flag' => 0);
                    }
                    $revinfo['venderId'] =  $retval['vender_info_result']['vender_id'];
                }
                else
                {
                    return array('code' => 2, 'error_msg' => '查询商家信息失败', 'retry_flag' => 0);
                }
                break;
            case LOGISTICS_SEARCH_WAYBILL:
                if(!isset($retval['jingdong_ldop_alpha_provider_sign_success_responce']['resultInfo']['statusCode']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval =$retval['jingdong_ldop_alpha_provider_sign_success_responce']['resultInfo'];
                if($retval['statusCode']==1)
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 1, 'error_msg' => '查询签约物流参数异常，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['statusCode']==-1)
                {
                    $error_msg = $retval['statusMessage'];
                    return array('code' => 2, 'error_msg' => '查询签约物流失败，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['statusCode']==0)
                {
                    if(!isset($retval['data']))
                    {
                        $error_msg = "接口返回数据为空";
                        return array('code' => 1, 'error_msg' => '查询签约物流参数异常,'.$error_msg, 'retry_flag' => 0);
                    }
                    $datainfo =  $retval['data'];
                    foreach ($datainfo as $value) {
                       // $value = $value['data'];
                        $revinfo[]= array(
                            'providerName'   => isset($value['providerName'])?$value['providerName']:'',
                            'providerCode'   => isset($value['providerCode'])?$value['providerCode']:'',
                            'branchCode'     => isset($value['branchCode'])?$value['branchCode']:'',
                            'settlementCode' => isset($value['settlementCode'])?$value['settlementCode']:'',
                            'province'       => $value['address']['provinceName'],
                            'city'           => $value['address']['cityName'],
                            'district'       => $value['address']['countryName'],
                            'detail'         => $value['address']['address'],
                        );
                    }
                }  
                break;
            case LOGISTICS_WAYBILL_UNBIND:
                if(!isset($retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusCode']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                else if($retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusCode']==1)
                {
                    $error_msg = $retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusMessage'];
                    return array('code' => 1, 'error_msg' => '取消参数异常，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusCode']==-1)
                {
                    $error_msg = $retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusMessage'];
                    return array('code' => 2, 'error_msg' => '单号取消失败，京东返回:'.$error_msg, 'retry_flag' => 0);
                }
                else if($retval['jingdong_ldop_alpha_waybill_api_unbind_responce']['resultInfo']['statusCode']==0)
                {
                    //一个一个调的 只需要返回单号的成功就行
                    $revinfo['success'] = 1;
                }
                break;
            default:
                break;
        }
        return array('code' => 0, 'error_msg' => '','rev_info' => $revinfo, 'retry_flag' => 0);
    }

}

?>
