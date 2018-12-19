<?php
/*
 * 京东接口通讯类.
 * 接口调用方式: http post
 * 数据格式: json
 */
class WmsJD
{
    public $app_key;             //授权app_key
    public $app_secret;          //授权密钥
    public $access_token;        //授权令牌
    public $department_no;       //事业部编号
    public $ext_warehouse_no;    //外部仓库编码
    public $baseUrl = 'https://api.jd.com/routerjson'; //基础地址
    public $apiUrl;             //接口地址
    public $method;             //请求方法
    public $apiType = 0;        //接口调用形式 0为http post 1为webservice
    public $dataType = 1;       //数据格式 0-xml  1-json
    public $sendParams = '';
    public $weblogParams = '';  //记录weblog所需要的参数

    //构造函数,初始化仓储的授权信息
    function __construct($wms_info)
    {
        $this->cur_time          = date("Y-m-d H:i:s");
        $this->app_key           = isset($wms_info['appKey'])?$wms_info['appKey']:'';
        $this->app_secret        = isset($wms_info['appSecret'])?$wms_info['appSecret']:'';
        $this->access_token      = isset($wms_info['accessToken'])?$wms_info['accessToken']:'';
        $this->department_no     = isset($wms_info['deptNo'])?$wms_info['deptNo']:'';
        $this->ext_warehouse_no  = isset($wms_info['ext_warehouse_no'])?$wms_info['ext_warehouse_no']:'';
    }

    //把数据格式化成对方需要格式,然后组成一个post请求参数列表
    public function formatParams($api_method,$data)
    {
        switch($api_method) //根据不同的业务类型(即接口) 格式化该业务所需的数据内容.
        {
            case WMS_METHOD_TRADE_ADD: //推送订单
            {
                $content = $this->getTradeAddContent($data);
                $this->method = 'jingdong.eclp.order.addOrder';
                break;
            }

            case WMS_METHOD_TRADE_CANCEL: //取消订单
            {
                $content = $this->getOrderCancelContent($data,WMS_METHOD_TRADE_CANCEL);
                $this->method = 'jingdong.eclp.order.cancelOrder';
                break;
            }

            CASE WMS_METHOD_STOCKOUT_STATUS: //查询订单流水状态
            {
                $content = $this->getTradeStatusQueryContent($data);
                $this->method = 'jingdong.eclp.order.queryOrderStatus';
                break;
            }

            case WMS_METHOD_TRADE_QUERY: //查询订单
            {
                $content = $this->getOrderQueryContent($data,WMS_METHOD_TRADE_QUERY);
                $this->method = 'jingdong.eclp.order.queryOrder';
                break;
            }

            case WMS_METHOD_VMI_STOCKCHANGE_QUERY: //查询VMI库存流水
            {
                $content = $this->getVMIStockchangeQueryContent($data);
                $this->method = 'jingdong.eclp.stock.queryVmiShopStockFlow';
                break;
            }
            
            case WMS_METHOD_SKU_ADD: //推送商品信息(单品)
            {
                $content = $this->getSkuInfoAddContent($data);
                $this->method = 'jingdong.eclp.goods.transportGoodsInfo';
                break;
            }

            case WMS_METHOD_SKU_MODIFY: //修改商品信息
            {
                $content = $this->getSkuInfoUpdateContent($data);
                $this->method = 'jingdong.eclp.goods.updateGoodsInfo';
                break;
            }

            case WMS_METHOD_PURCHASE_ADD: //推送采购单
            {
                $content = $this->getPurchaseAddContent($data);
                $this->method = 'jingdong.eclp.po.addPoOrder';
                break;
            }

            case WMS_METHOD_PURCHASE_CANCEL: //取消采购单
            {
                $content = $this->getOrderCancelContent($data,WMS_METHOD_PURCHASE_CANCEL);
                $this->method = 'jingdong.eclp.po.cancalPoOrder';
                break;
            }

            case WMS_METHOD_SALES_REFUND_QUERY: //销售退货入库单查询
            {
                $content = $this->getOrderQueryContent($data,WMS_METHOD_SALES_REFUND_QUERY);
                $this->method = 'jingdong.eclp.rtw.queryRtw';
                break;
            }

            case WMS_METHOD_PURCHASE_QUERY: //采购单详情查询
            {
                $content = $this->getOrderQueryContent($data,WMS_METHOD_PURCHASE_QUERY);
                $this->method = 'jingdong.eclp.po.queryPoOrder';
                break;
            }
            case WMS_METHOD_PURCHASE_RETURN_ADD: //推送采购退货单
            {
                $content = $this->getPurchaseOutContent($data);
                $this->method = 'jingdong.eclp.rts.isvRtsTransfer';
                break;
            }
            case WMS_METHOD_PURCHASE_RETURN_CANCEL://取消采购退货单
            {
                $content = $this->getOrderCancelContent($data,WMS_METHOD_PURCHASE_RETURN_CANCEL);
                $this->method = 'jingdong.eclp.rts.isvRtsCancel';
                break;
            }
            case WMS_METHOD_PURCHASE_RETURN_QUERY://取消采购退货单查询
            {
                $content = $this->getOrderQueryContent($data,WMS_METHOD_PURCHASE_RETURN_QUERY);
                $this->method = 'jingdong.eclp.rts.isvRtsQuery';
                break;
            }
            case WMS_METHOD_WAREHOUSE_GET: //获取仓库信息
            {
                $content = $this->getWarehouseQueryContent($data);
                $this->method = 'jingdong.eclp.master.queryWarehouse';
                break;
            }
            case WMS_METHOD_SHIPPER_GET: //获取物流公司信息
            {
                $content = $this->getShipperQueryConten($data);
                $this->method = 'jingdong.eclp.master.queryShipper';
                break;
            }
            case WMS_METHOD_GOODSINFO_QUERY://查询商品信息
            {
                $content = $this->getGoodsinfoQueryConten($data);
                $this->method = 'jingdong.eclp.goods.queryGoodsInfo';
                break;
            }

            default :
                break;
        }

        //生成url
        $sys_params = array(
            'method'        =>  $this->method,
            'access_token'  =>  $this->access_token,
            'app_key'       =>  $this->app_key,
            'timestamp'     =>  $this->cur_time,
            'format'        =>  'json',
            'v'             =>  '2.0',
            );
        $app_params = array(
            '360buy_param_json' => json_encode($content)
        );

        //获取签名
        $sign = $this->generateSign($sys_params,$app_params);
        $sys_params['sign'] = $sign;
        //更新url
        $this->apiUrl = $this->baseUrl;
        $this->apiUrl .= '?'.http_build_query($sys_params);
        logx("the url is :".$this->apiUrl);

        $this->sendParams['post_params']= $app_params;

        $this->weblogParams     = array(
            'interface_name'    => $this->method,
            'request_body'      => $app_params['360buy_param_json']
        );
        return true;
    }

    //生成数字签名
    protected function generateSign($sys_params,$app_params)
    {
        $str = '';
        $params = array_merge($sys_params,$app_params);
        ksort($params);
        foreach($params as $key=>$value)
        {
            $str .= $key.$value;
        }
        $str = $this->app_secret.$str.$this->app_secret;
        return strtoupper(md5($str));
    }

    //订单的字段映射
    private function getTradeAddContent($data)
    {
        $trade   = $data['trade'];
        $details = $data['details'];
        //来源平台编码
        $dict_platform = array(
            '1'     =>  '8',
            '2'     =>  '8',//淘宝分销
            '3'     =>  '1',
            '4'     =>  '9',
            '5'     =>  '4',
            '6'     =>  '10',
            '7'     =>  '7',
            '11'    =>  '12',
            '12'    =>  '11',
            '13'    =>  '3',
            '14'    =>  '17',
            '16'    =>  '19',
            '20'    =>  '20',
            '21'    =>  '21',
            );
        if (array_key_exists($trade['shop_platform_id'],$dict_platform))
        {
            $platform_code = $dict_platform[$trade['shop_platform_id']];
            //天猫
            if ($trade['shop_platform_id'] == 1 && $trade['sub_platform_id'] == 1)
            {
                $platform_code = '2';
            }
        }
        else
        {
            $platform_code = '6';
        }
        $src_tids = explode(',', $trade['src_tids']);

        //货到付款标记
        $order_mark = str_pad('',50,'0');
        if ($trade['delivery_term'] == 2)
        {
            $order_mark[0] = '1';
            
            if($trade['platform_id']==3)
            {
                $trade['cod_amount'] = $trade['api_tmp_cod_amount']; 
            }
        }

        //字段映射
        $order_info = array(
            'bdOwnerNo'                 =>      $trade['bdOwnerNo'],        //青龙业务号
            'isvUUID'                   =>      $trade['stockout_no'],      //ISV出库单号
            'isvSource'                 =>      'ISV0000000000005',         //ISV来源编号，线下获取写死
            'shopNo'                    =>      $trade['shop_remark'],      //店铺编号
            'departmentNo'              =>      $this->department_no,       //事业部编号
            'warehouseNo'               =>      $this->ext_warehouse_no,    //仓库编号
            'shipperNo'                 =>      $trade['logistics_code'] == ''?'CYS0000010':$trade['logistics_code'],//承运商编号,默认为京东快递
            'salesPlatformOrderNo'      =>      $src_tids[0],                //销售平台订单号
            'salePlatformSource'        =>      $platform_code,              //销售平台来源
            'salesPlatformCreateTime'   =>      $trade['trade_time']=='0000-00-00 00:00:00'?date("Y-m-d H:i:s"):$trade['trade_time'],//销售平台下单时间
            'consigneeName'             =>      $trade['receiver_name'],    //收货人姓名
            'consigneeMobile'           =>      $trade['receiver_mobile'],  //收货人手机
            'consigneePhone'            =>      $trade['receiver_telno'],   //收货人电话
            'addressProvince'           =>      $trade['province'],         //收货人省
            'addressCity'               =>      $trade['city'],             //收货人市
            'addressCounty'             =>      $trade['district'],         //收货人区/县
            'consigneeAddress'          =>      $trade['receiver_area'].' '.$trade['receiver_address'], //收货人地址
            'consigneePostcode'         =>      $trade['receiver_zip'],     //收货人邮编
            'receivable'                =>      $trade['cod_amount'],       //货到付款金额
            'consigneeRemark'           =>      $trade['buyer_message'],    //客户留言
            'orderMark'                 =>      $order_mark,                 //订单标记位，首位为1代表货到付款
            'invoiceTitle'              =>      $trade['invoice_type']==0?'':$trade['invoice_title'],   //发票抬头
            'invoiceContent'            =>      $trade['invoice_type']==0?'':$trade['invoice_content'], //发票内容
            );

        foreach($details as $detail)
        {
            $order_info['goodsNo'][]     = $detail['spec_wh_no2'];   //商品编号
            $order_info['quantity'][]    = intval($detail['num']);   //商品的出库数量
        }
        $order_info['goodsNo']  = implode(',',$order_info['goodsNo']);
        $order_info['quantity'] = implode(',',$order_info['quantity']);

        return $order_info;
    }
    //取消集合
    private function getOrderCancelContent($data,$type)
    {
        $key = '';
        $wmsOrderNo = '';

        switch ($type)
        {
            case WMS_METHOD_TRADE_CANCEL:
                $key = 'eclpSoNo';
                $wmsOrderNo = $data['trade']['outer_no'];
                break;
            case WMS_METHOD_PURCHASE_CANCEL://采购单
                $key='poOrderNo';
                $wmsOrderNo = $data['purchase']['wms_outer_no'];
                break;
            case WMS_METHOD_PURCHASE_RETURN_CANCEL://采购退货单
                $key = 'eclpRtsNo';
                $wmsOrderNo = $data['return']['wms_outer_no'];
                break;

            default:
                break;
        }

        $cancel_body = array(
            $key  =>  $wmsOrderNo,
        );

        return $cancel_body;
    }
    //查询集合
    private function getOrderQueryContent($data,$type)
    {
        $key = '';
        $wmsOrderNo = '';

        switch ($type)
        {
            case WMS_METHOD_SALES_REFUND_QUERY:
                $key = 'eclpSoNo';
                $wmsOrderNo = $data['refund']['stockout_outer_no'];//stockout_outer_no是stockout_order中的outer_no
                break;
            case WMS_METHOD_TRADE_QUERY:
                $key = 'eclpSoNo';
                $wmsOrderNo = $data['trade']['outer_no'];
                break;
            case WMS_METHOD_PURCHASE_QUERY://采购单
                $key = 'poOrderNo';
                $wmsOrderNo =$data['purchase']['wms_outer_no'];
                break;
            case WMS_METHOD_PURCHASE_RETURN_QUERY://采购退货单
                $key = 'eclpRtsNo';
                $wmsOrderNo =$data['return']['wms_outer_no'];
                break;

            default:
                break;
        }

        $query_body = array(
            $key => $wmsOrderNo,
        );

        return $query_body;
    }

    private function getTradeStatusQueryContent($data)
    {
        $query_body = array(
          'eclpSoNo'    =>  $data['trade']['outer_no'],
        );

        return $query_body;
    }

    private function getVMIStockchangeQueryContent($data)
    {
        $query_body = array(
            'startTime'     =>  $data['start_time'],
            'endTime'       =>  $data['end_time'],
            'deptNo'        =>  $this->department_no,
            'warehouseNo'   =>  $this->ext_warehouse_no,
            'shopNo'        =>  $data['shop_no'],
            'startPage'     =>  $data['page_no'],
            'onePageNum'    =>  $data['page_size'],
        );

        return $query_body;
    }
    
    //采购单的字段映射
    private function getPurchaseAddContent($data)
    {
        $purchase   = $data['purchase'];
        $details    = $data['details'];
        $purchaseInfo = array(
            'spPoOrderNo'         =>  $purchase['outer_no'],//外部采购订单号
            'deptNo'              =>  $this->department_no,  //事业部编号
            'whNo'                =>  $this->ext_warehouse_no,//入库库房编号
            'supplierNo'          =>  $purchase['provider_no'],//供应商编号
        );
        foreach($details as $detail)
        {
            $purchaseInfo['numApplication'][] = intval($detail['num']);//申请入库数量
            $purchaseInfo['deptGoodsNo'][]    = $detail['spec_wh_no2'];//事业部商品编号
            $purchaseInfo['goodsStatus'][]    = 1;//商品状态
        }
        $purchaseInfo['numApplication']  = implode(',',$purchaseInfo['numApplication']);
        $purchaseInfo['deptGoodsNo']     = implode(',',$purchaseInfo['deptGoodsNo']);
        $purchaseInfo['goodsStatus']     = implode(',',$purchaseInfo['goodsStatus']);

        return $purchaseInfo;
    }

    //商品信息的字段映射(新增)
    private function getSkuInfoAddContent($data)
    {
        $spec=$data['spec'];
        
        $goodsInfo       = array(
            'deptNo'            =>  $this->department_no,//事业部编号
            'isvGoodsNo'        =>  $spec['spec_no'],//ISV主商品编码
            'barcodes'          =>  $spec['barcode'],//商品条码，以英文逗号隔开
            'thirdCategoryNo'   =>  '4159',//三级分类名称，京东方要求我们传固定（4159代表 其他）
            'safeDays'          =>  0,//保质期天数，我们传对方不能解析，又字段必传，所以传0
            'goodsName'         =>  $spec['goods_name'],//商品名称
            'brandNo'           =>  $spec['brand_no'],//品牌编码
            'brandName'         =>  $spec['brand_name'],//品牌名称
            'length'            =>  intval($spec['length']),//长
            'width'             =>  intval($spec['width']),//宽
            'height'            =>  intval($spec['height']),//高
            'netWeight'         =>  intval($spec['weight']),//净重
            'grossWeight'       =>  intval($spec['weight']),//毛重
            'reserve1'          =>  $spec['specProp1'], //预留字段
            'reserve2'          =>  $spec['specProp2'], //预留字段
            'reserve3'          =>  $spec['specProp3'], //预留字段
            'reserve4'          =>  $spec['specProp4'], //预留字段
            'reserve5'          =>  $spec['specProp5'], //预留字段
        );
        
        return $goodsInfo;
    }

    //商品信息的字段映射(修改)
    private function getSkuInfoUpdateContent($data)
    {
        $spec  = $data['spec'];
            $goodsInfo       = array(
                'goodsNo'           =>  $spec['spec_wh_no2'],//ECLP事业部商品编码
                'barcodes'          =>  $spec['barcode'],//商品条码，以英文逗号隔开
                'goodsName'         =>  $spec['goods_name'],//商品名称
                'brandNo'           =>  $spec['brand_no'],//品牌编码
                'brandName'         =>  $spec['brand_name'],//品牌名称
                'length'            =>  intval($spec['length']),//长
                'width'             =>  intval($spec['width']),//宽
                'height'            =>  intval($spec['height']),//高
                'netWeight'         =>  intval($spec['weight']),//净重
                'grossWeight'       =>  intval($spec['weight']),//毛重
                'reserve1'          =>  $spec['specProp1'], //预留字段
                'reserve2'          =>  $spec['specProp2'], //预留字段
                'reserve3'          =>  $spec['specProp3'], //预留字段
                'reserve4'          =>  $spec['specProp4'], //预留字段
                'reserve5'          =>  $spec['specProp5'], //预留字段
            );
        
        return $goodsInfo;
    }
    //推送采购退货单
    private function getPurchaseOutContent($data)
    {
        $otherOut    = $data['otherOut'];
        $details     = $data['details'];
        $orderType = 'CGTH';

        if(empty($otherOut['logistics_code']))
        {
            $otherOut['logistics_code'] = 'OTHER';
        }
        //是否使用京东配送
          
        $deliveryMode   =   $otherOut['deliverymode'];

        $otherOutInfo = array(
            'deptNo'            =>  $this->department_no,
            'isvRtsNum'         =>  $otherOut['outer_no'],//外部编号
            'warehouseNo'       =>  $this->ext_warehouse_no,//外部仓库编码
            'deliveryMode'      =>  $deliveryMode,//提货方式,1是商家自提,2是京东配送
        );
        //收件人信息
        $otherOutInfo =array_merge($otherOutInfo, array(
            'receiver'              =>  OuterUtils::escape_xml_string($otherOut['contact']),//姓名
            'receiverPhone'         =>  $otherOut['telno'], //电话
            'province'              =>  $otherOut['province'], //省份
            'city'                  =>  $otherOut['city'],  //市
            'county'                =>  $otherOut['district'],  //县
            'address'               =>  OuterUtils::escape_xml_string($otherOut['receive_address'])  //详细地址
        ));
        $otherOutInfo['deptGoodsNo'] = '';
        $otherOutInfo['quantity'] = '';

        foreach($details as $detail)
        {
            $otherOutInfo['deptGoodsNo'] = $otherOutInfo['deptGoodsNo'].$detail['spec_wh_no2'].',';//事业部商品编码
            $otherOutInfo['quantity']    = $otherOutInfo['quantity'].(int)$detail['num'].',';
        }
        $otherOutInfo['deptGoodsNo'] = rtrim($otherOutInfo['deptGoodsNo'],',');
        $otherOutInfo['quantity']    = rtrim($otherOutInfo['quantity'],',');
        return $otherOutInfo;
    }

    //仓库信息的字段映射
    private function getWarehouseQueryContent($data)
    {
        $para =array(
            'deptNo'        => $data['deptNo'],
            'warehouseNos'  => '',
            'status'        => '',
        );

        return $para;
    }

    //承运商的字段映射
    private function getShipperQueryConten($data)
    {
        $para =array(
            'shipperNos' => '',
        );

        return $para;
    }

    //查询商品信息的字段映射
    private function getGoodsinfoQueryConten($data)
    {
        $query_body = array(
            'deptNo'        =>  $this->department_no,
            'barcodes'   =>  $data['barcode_str'],
            'queryType'     =>  $data['queryType'],
        );
        return $query_body;
    }

    public function getSendParams()
    {
        return $this->sendParams;
    }

    //对方接口反馈信息的格式化
    public function formatResult($api_method,$retval)
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

        //以下验证应用级别错误
        if (array_key_exists('error_response',$retval))
        {
            $code    = isset($retval['error_response']['code']) ? $retval['error_response']['code'] : '';
            $message = isset($retval['error_response']['zh_desc']) ? $retval['error_response']['zh_desc'] : '';

            //VMI库存流水分页查询结束标识
            if($api_method == WMS_METHOD_VMI_STOCKCHANGE_QUERY && $message == '库存系统 根据 入参，查询 VMI店铺库存流水 信息为空，无法处理')
            {
                return array('code' => 911, 'error_msg'  => "", 'retry_flag' => 0 );
            }

            return array('code' => 1, 'error_msg'  => "code:$code message:$message", 'retry_flag' => 0 );
        }
        $revinfo = '';
        switch($api_method)
        {
            case WMS_METHOD_TRADE_ADD:
                if(!isset($retval['jingdong_eclp_order_addOrder_responce']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval = $retval['jingdong_eclp_order_addOrder_responce'];

                $revinfo = isset($retval['eclpSoNo'])?$retval['eclpSoNo']:'';
                break;

            case WMS_METHOD_TRADE_CANCEL:
                if(!isset($retval['jingdong_eclp_order_cancelOrder_responce']['cancelorder_result']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval = $retval['jingdong_eclp_order_cancelOrder_responce']['cancelorder_result'];

                $code    = isset($retval['code']) ? $retval['code'] : 2;
                $message = isset($retval['msg']) ? $retval['msg'] : '';
                if ($code == 2)
                {
                    return array('code' => 2, 'error_msg'  => "code:$code message:$message", 'retry_flag' => 0 );
                }
                break;

            case WMS_METHOD_STOCKOUT_STATUS:
                if(!isset($retval['jingdong_eclp_order_queryOrderStatus_responce']['queryorderstatus_result']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo = $retval['jingdong_eclp_order_queryOrderStatus_responce']['queryorderstatus_result'];

                break;

            case WMS_METHOD_TRADE_QUERY:
                if(!isset($retval['jingdong_eclp_order_queryOrder_responce']['queryorder_result']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $trade_info = $retval['jingdong_eclp_order_queryOrder_responce']['queryorder_result'];

                $outer_no           = isset($trade_info['isvUUID'])?$trade_info['isvUUID']:'';
                $logistics_code     = isset($trade_info['shipperNo'])?$trade_info['shipperNo']:'';
                //物流单号
                $logistics_no       = '';
                $logistics_list     = '';
                $logistics_no_list  = isset($trade_info['wayBill'])?$trade_info['wayBill']:'';
                if(!empty($logistics_no_list))
                {
                    $logistics_no_list = explode(',',$logistics_no_list);
                    foreach ($logistics_no_list as $key => $value)
                    {
                        if ($key == 0)
                        {
                            $logistics_no = $value;
                        }
                        else
                        {
                            $revinfo['logistics'][] = array(
                                'order_no'          => $outer_no,
                                'logistics_code'    => $logistics_code,
                                'logistics_no'      => $value
                            );
                            $logistics_list .= $logistics_code.':'.$value.';';
                        }
                    }
                }
                //包裹重量
                $package_weight = 0;
                $package_list   = isset($trade_info['orderPackageList'])?$trade_info['orderPackageList']:'';
                if(!empty($package_list))
                {
                    foreach ($package_list as $package)
                    {
                        $package_weight += $package['packWeight'];
                    }
                }
                //是否已出库('10018'-打包，代表已出库)
                $status         = STATUS_OTHER;
                $status_name    = $trade_info['currentStatus'];
                $status_list    = isset($trade_info['orderStatusList'])?$trade_info['orderStatusList']:'';
                if(!empty($status_list))
                {
                    foreach ($status_list as $status_val)
                    {
                        $status_code = isset($status_val['soStatusCode'])?$status_val['soStatusCode']:'';
                        if(($status_code == '10018') || ($status_code == '10019'))
                        {
                            $status      = STATUS_FINISH;
                            $status_name = $status_val['soStatusName'];
                            break;
                        }
                        else if ($status_code == '10028')
                        {
                            $status      = STATUS_CANCELED;
                            $status_name = $status_val['soStatusName'];
                        }
                        else if ($status_code == '10029')
                        {
                            $status      = STATUS_CANCELFAIL;
                            $status_name = $status_val['soStatusName'];
                        }
                        else if ($status_code == $status_name && $status == STATUS_OTHER)
                        {
                            $status_name = $status_val['soStatusName'];
                        }
                    }
                }
                //商品明细
                $detail_list = isset($trade_info['orderDetailList'])?$trade_info['orderDetailList']:'';
                if(!empty($detail_list))
                {
                    foreach ($detail_list as $detail)
                    {
                       $revinfo['details'][]    =   array(
                           'order_no'       =>  $outer_no,
                           'spec_no'        =>  $detail['goodsNo'],
                           'num'            =>  $detail['quantity']
                       );
                    }
                }
                $revinfo['order'] = array(
                    'outer_no'          => $outer_no,
                    'status'            => $status,
                    'status_name'       => $status_name,
                    'logistics_code'    => $logistics_code,
                    'logistics_no'      => $logistics_no,
                    'logistics_list'    => $logistics_list,
                    'weight'            => $package_weight,
                );
                break;

            case WMS_METHOD_VMI_STOCKCHANGE_QUERY:
                if(!isset($retval['jingdong_eclp_stock_queryVmiShopStockFlow_responce']['vmiShopStockFlowList']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $stockchange_list = $retval['jingdong_eclp_stock_queryVmiShopStockFlow_responce']['vmiShopStockFlowList'];
                
                foreach ($stockchange_list as $stockchange_info)
                {
                    $warehouse_no = isset($stockchange_info['warehouseNo'])?$stockchange_info['warehouseNo']:'';
                    $biz_code     = isset($stockchange_info['flowId'])?$stockchange_info['flowId']:'';
                    $spec_no      = isset($stockchange_info['goodsNo'])?$stockchange_info['goodsNo']:'';
                    $old_num      = isset($stockchange_info['formerNum'])?$stockchange_info['formerNum']:'';
                    $new_num      = isset($stockchange_info['nowNum'])?$stockchange_info['nowNum']:'';
                    $remark       = isset($stockchange_info['bizTypeName'])?$stockchange_info['bizTypeName']:'';

                    $revinfo['details'][]=   array(
                        'warehouse_no'   =>  $warehouse_no,
                        'biz_code'       =>  $biz_code,
                        'spec_no'        =>  $spec_no,
                        'old_num'        =>  $old_num,
                        'new_num'        =>  $new_num,
                        'remark'         =>  $remark,
                    );
                }
                break;

            case WMS_METHOD_SKU_ADD:  //新增商品
                if(!isset($retval['jingdong_eclp_goods_transportGoodsInfo_responce']['goodsNo']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo = $retval['jingdong_eclp_goods_transportGoodsInfo_responce']['goodsNo'];
                break;

            case WMS_METHOD_SKU_MODIFY: //修改商品信息
                if(!isset($retval['jingdong_eclp_goods_updateGoodsInfo_responce']['updateResult']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                if($retval['jingdong_eclp_goods_updateGoodsInfo_responce']['updateResult']===false)
                {
                    return array('code' => 1, 'error_msg' => "商品修改失败", 'retry_flag' => 0);
                }
                break;

            case WMS_METHOD_PURCHASE_ADD:  //采购单
                if(!isset($retval['jingdong_eclp_po_addPoOrder_responce']['poOrderNo']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo = $retval['jingdong_eclp_po_addPoOrder_responce']['poOrderNo'];
                break;

            case WMS_METHOD_PURCHASE_CANCEL: //取消采购单
                if(!isset($retval['jingdong_eclp_po_cancalPoOrder_responce']['poResult']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $retval = $retval['jingdong_eclp_po_cancalPoOrder_responce']['poResult'];
                $code    = isset($retval['code']) ? $retval['code'] : '';
                $message = isset($retval['msg']) ? $retval['msg'] : '';
                if($code == 2)
                {
                      return array('code' => 2, 'error_msg' => "code:$code message:$message", 'retry_flag' => 0);
                }
                break;

            case WMS_METHOD_PURCHASE_QUERY:// 采购单查询结果
                if(!isset($retval['jingdong_eclp_po_queryPoOrder_responce']['queryPoModelList'][0]))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $purchase_info   = $retval['jingdong_eclp_po_queryPoOrder_responce']['queryPoModelList']['0'];
                $outer_no        = isset($purchase_info['isvPoOrderNo'])?$purchase_info['isvPoOrderNo']:'';
                //是否已入库('70'-完成，代表已入库)
                $status         = STATUS_OTHER;
                $status_name    = $purchase_info['poOrderStatus'];

                switch($status_name)
                {
                    case 10:
                        $status_name='新建';
                    break;

                    case 20:
                        $status_name='初始化';
                    break;

                    case 70:
                        $status_name='完成';
                        $status     = STATUS_FINISH;
                    break;

                    case 90:
                        $status_name='取消中';
                    break;

                    case 91:
                        $status_name='取消成功';
                    break;

                    case 92:
                        $status_name='取消失败';
                    break;

                }
                //商品明细
                $detail_list = isset($purchase_info['poItemModelList'])?$purchase_info['poItemModelList']:'';
                if(!empty($detail_list))
                {
                    foreach($detail_list as $value)
                    {
                        if($value['realInstoreQty']==0)
                        {
                            continue;
                        }                       
                        $revinfo['details'][]    =   array(
                            'order_no'       =>  $outer_no,
                            'spec_no'        =>  $value['goodsNo'],
                            'num'            =>  $value['realInstoreQty']
                        );
                    }
                }
                $revinfo['order']       =  array(
                    'outer_no'          => $outer_no,
                    'status'            => $status,
                    'status_name'       => $status_name,
                );

                break;
            case WMS_METHOD_SALES_REFUND_QUERY:// 销售退货入库查询结果
                if(!isset($retval['jingdong_eclp_rtw_queryRtw_responce']['queryrtw_result'][0]))
                {
                    return array('code' => 2, 'error_msg' => '该订单未入京东仓', 'retry_flag' => 0 );
                }
                $sales_refund_info   = $retval['jingdong_eclp_rtw_queryRtw_responce']['queryrtw_result'][0];
                $wms_outer_no        = isset($sales_refund_info['eclpSoNo'])?$sales_refund_info['eclpSoNo']:'';

                //是否已入库('70'-完成，代表已入库)
                $status         = STATUS_OTHER;
                $status_name    = $sales_refund_info['status'];
                $warehouseNo    = $sales_refund_info['warehouseNo'];

                switch($status_name)
                {
                    case 0:
                        $status_name='新建';
                        break;

                    case 100:
                        $status_name='初始化';
                        break;

                    case 200:
                        $status_name='完成';
                        $status     = STATUS_FINISH;
                        break;

                    case 300:
                        $status_name='取消中';
                        break;

                    case 400:
                        $status_name='已取消';
                        break;

                    case 600:
                        $status_name='拒收';
                        break;

                }

                $revinfo['order']       =  array(
                    'outer_no'          => $wms_outer_no,
                    'status'            => $status,
                    'status_name'       => $status_name,
                    'warehouse_no'      => $warehouseNo,
                );

                break;
            case WMS_METHOD_WAREHOUSE_GET: //获取仓库信息
                if(!isset($retval['jingdong_eclp_master_queryWarehouse_responce']['querywarehouse_result']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo['warehouse'] = $retval['jingdong_eclp_master_queryWarehouse_responce']['querywarehouse_result'];
                break;

            case WMS_METHOD_SHIPPER_GET: //获取物流公司信息
                if(!isset($retval['jingdong_eclp_master_queryShipper_responce']['queryshipper_result']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $shipper_list  = $retval['jingdong_eclp_master_queryShipper_responce']['queryshipper_result'];
                $revinfo['shipper'] = array();
                foreach($shipper_list as $key => $shipper)
                {   
                    if($shipper['status'] == 0)
                    {
                        continue;
                    }
                    $revinfo['shipper'][$key]['name']           = $shipper['shipperName'];
                    $revinfo['shipper'][$key]['logistics_code'] = $shipper['shipperNo'];
                    $revinfo['shipper'][$key]['cod_support']    = $shipper['isCod'] == 0?2:1;
                }
                break;
            case WMS_METHOD_GOODSINFO_QUERY://查询商品信息
                if(!isset($retval['jingdong_eclp_goods_queryGoodsInfo_responce']['goodsInfoList'][0]['goodsNo']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $goodsInfoList  = $retval['jingdong_eclp_goods_queryGoodsInfo_responce']['goodsInfoList'];
                $spec_list = array();
                foreach ($goodsInfoList as $key => $value)
                {
                    $spec_list[$key]['spec_wh_no2'] = $value['goodsNo'];
                    $spec_list[$key]['barcode'] = $value['barcodes'];
                }
                $revinfo['spec_list'] = $spec_list;
                break;

            case WMS_METHOD_PURCHASE_RETURN_ADD: //推送采购退货单
                if(!isset($retval['jingdong_eclp_rts_isvRtsTransfer_responce']['rtsResult']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo = $retval['jingdong_eclp_rts_isvRtsTransfer_responce']['rtsResult']['eclpRtsNo'];
                break;

            case WMS_METHOD_PURCHASE_RETURN_CANCEL://取消采购退货单
                if(!isset($retval['jingdong_eclp_rts_isvRtsCancel_responce']['rtsResult']))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $revinfo = $retval['jingdong_eclp_rts_isvRtsCancel_responce']['rtsResult'];
                $code    = isset($revinfo['resultCode']) ? $revinfo['resultCode'] : '';
                $message = isset($revinfo['msg']) ? $revinfo['msg'] : ''; 
                if($code == 2)
                {
                   return array('code' => 2, 'error_msg' => "code:$code message:$message", 'retry_flag' => 0);
                }

                break;

            case WMS_METHOD_PURCHASE_RETURN_QUERY://采购退货单查询
                if(!isset($retval['jingdong_eclp_rts_isvRtsQuery_responce']['rtsResultList'][0]))
                {
                    return array('code' => 2, 'error_msg' => '京东返回信息异常', 'retry_flag' => 0 );
                }
                $return_info = $retval['jingdong_eclp_rts_isvRtsQuery_responce']['rtsResultList'][0];
                $outer_no = isset($return_info['isvRtsNum'])?$return_info['isvRtsNum']:'';
                //是否已出库(400.已取消;)
                $status         = STATUS_OTHER;
                $status_list    = isset($return_info['rtsOrderStatus'])?$return_info['rtsOrderStatus']:'';
                switch($status_list)
                {
                    case 0:
                        $status_name='新建';
                        break;

                    case 100:
                        $status_name='初始化';
                        break;

                    case 150:
                        $status_name='复核完成';
                        break;

                    case 200:
                        $status_name='完成';
                        $status     = STATUS_FINISH;
                        break;

                    case 300:
                        $status_name='取消中';
                        break;

                    case 400:
                        $status_name='已取消';
                        break;

                    case 500:
                        $status_name='取消失败';
                        break;
                    default:
                        $status_name='返回状态有错,联系京东wms进行处理';

                }

                //商品明细
                $detail_list = isset($return_info['rtsDetailList'])?$return_info['rtsDetailList']:'';
                if(!empty($detail_list))
                {
                    foreach ($detail_list as $spec)
                    {
                        if ($spec['quantity'] == 0)
                        {
                            continue;
                        }
                        $revinfo['details'][]=   array(
                            'order_no'       =>  $outer_no,
                            'spec_no'        =>  $spec['deptGoodsNo'],
                            'num'            =>  $spec['quantity']
                        ) ;
                    }

                    $revinfo['order']       = array(
                        'outer_no'          => $outer_no,
                        'status'            => $status,
                        'status_name'       => $status_name
                    );
                }
                break;

            default:
                break;
        }
        return array('code' => 0, 'error_msg' => '','rev_info' => $revinfo, 'retry_flag' => 0);
    }
}

?>
