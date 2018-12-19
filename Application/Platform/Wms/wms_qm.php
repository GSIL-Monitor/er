<?php
namespace Platform\Wms;
use Platform\Wms\OuterUtils;
/*
 * 奇门接口通讯类.
 * 接口调用方式: http post
 * 数据格式: xml
 */
class WmsQM
{
	public $appKey;	//授权key
	public $appSecret;	//授权密码
	public $customerId;	//客户id （wms颁发）
	public $extWarehouseNo;	//外部仓库编码
    public $warehouseType;//外部仓库类型
    public $warehouseCode;//仓库编码
    public $ownerCode;//货主编码
    public $ownerWarehouse;//所属仓库
    public $curTime;
	public $baseUrl = 'http://qimen.api.taobao.com/router/qimen/service';//基础地址 正式环境
	//public $baseUrl = 'http://qimenapi.tbsandbox.com/router/qimen/service';//基础地址 正式环境
	public $apiUrl;	//接口地址
    public $method;  //请求方法
    public $apiType = 0; //接口调用形式 0为http post 1为webservice
    public $dataType = 0; //数据格式 0-xml  1-json
    public $sendParams = '';
    public $weblogParams = ''; //记录weblog所需要的参数

	//构造函数,初始化仓储的授权信息
    function __construct($wms_info)
    {
        global $ekb_top_app_config;
    	$this->appKey = $ekb_top_app_config['app_key'];
    	//$this->appSecret = $ekb_top_app_config['app_secret'];
		$this->appSecret = decodeDbPwd($ekb_top_app_config['app_secret'],$ekb_top_app_config['app_key']);
		//$this->appKey = 1023305776;                                 //测试参数
    	//$this->appSecret = 'sandbox22b344a188de240bf240f4dc0';
    	//$this->customerId = 'c1494918781561';
        //$this->extWarehouseNo = 'Test';

        $this->customerId = isset($wms_info['customerId'])?$wms_info['customerId']:'';
        $this->ownerCode = isset($wms_info['ownerCode'])?$wms_info['ownerCode']:'';
        $this->extWarehouseNo = isset($wms_info['warehouseCode'])?$wms_info['warehouseCode']:'';
        $this->ownerWarehouse = isset($wms_info['customerId'])?$wms_info['ownerWarehouse']:'';
        $this->apiUrl = isset($wms_info['api_url'])?$wms_info['api_url']:'';
        $this->warehouseType = isset($wms_info['wms_type'])?$wms_info['wms_type']:'';
        $this->curTime = date("Y-m-d H:i:s");

    	if(isset($wms_info['api_url']) && !empty($wms_info['api_url'])) //有的wms可能会根据不同客户配置不同的接口地址,所以这里需要预留这种处理,让客户在erp里授权时填写接口地址
        {
            $this->baseUrl = $wms_info['api_url'];
        }
    }


    //把数据格式化成对方需要格式,然后组成一个post请求参数列表
    public function formatParams($api_method,$data)
    {
        switch($api_method) //根据不同的业务类型(即接口) 格式化该业务所需的数据内容.
        {
            case WMS_METHOD_TRADE_ADD: //推送订单
            {
                $content = $this->getTradeAddContent($data);
                $this->method = 'taobao.qimen.deliveryorder.create';
                break;
            }
            case WMS_METHOD_PURCHASE_ADD: //推送采购单
            {
                $content = $this->getPurchaseAddContent($data);
                $this->method = 'taobao.qimen.entryorder.create';
                break;
            }
            case WMS_METHOD_REFUND_ADD: //推送退货单
            {
                $content = $this->getRefundAddContent($data);
                $this->method = 'taobao.qimen.returnorder.create';
                break;
            }
            case WMS_METHOD_TRADE_CANCEL: //取消订单
            {
                $content = $this->getOrderCancelContent($data,'JYCK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_PURCHASE_CANCEL: //取消采购单
            {
                $content = $this->getOrderCancelContent($data,'CGRK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_PURCHASE_RETURN_CANCEL://取消采购退货单
            {
                $content = $this->getOrderCancelContent($data,'CGTH');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_REFUND_CANCEL: //取消退货单
            {
                $content = $this->getOrderCancelContent($data,'XTRK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_SKUS_ADD: //推送商品信息(批量)
            {
                $content = $this->getSkuInfosContent($data,'add');
                $this->method = 'taobao.qimen.items.synchronize';
                break;
            }
            case WMS_METHOD_SKU_ADD: //推送商品信息(单品)
            {   
                $content = $this->getSkuInfoContent($data,'add');
                $this->method = 'taobao.qimen.singleitem.synchronize';
                break;
            }
            case WMS_METHOD_SKU_MODIFY: //修改商品信息
            {
                $content = $this->getSkuInfoContent($data,'update');
                $this->method = 'taobao.qimen.singleitem.synchronize';
                break;
            }

            case WMS_METHOD_TRANSFEROUT_SAME_ADD://推送调拨出库单(出入仓库同类型)
            {
                $content = $this->getStockOutContent($data,'DBCK');
                $this->method = 'taobao.qimen.stockout.create';
                break;
            }

            case WMS_METHOD_TRANSFEROUT_DIVERSE_ADD://推送调拨出库单(出入仓库不同类型)相当于其他出库单
            {
                $content = $this->getStockOutContent($data,'QTCK');
                $this->method = 'taobao.qimen.stockout.create';
                break;
            }

            case WMS_METHOD_TRANSFERIN_SAME_ADD://推送调拨入库单(出入仓库同类型)
            {
                $content = $this->getStockInContent($data,'DBRK');
                $this->method = 'taobao.qimen.entryorder.create';
                break;
            }

            case WMS_METHOD_TRANSFERIN_DIVERSE_ADD://推送调拨入库单(出入仓库不同类型)相当于其他入库单
            {
                $content = $this->getStockInContent($data,'QTRK');
                $this->method = 'taobao.qimen.entryorder.create';
                break;
            }

            case WMS_METHOD_TRANSFEROUT_DIVERSE_CANCEL://取消调拨出库单,(出入仓库不同类型)
            {
                $content = $this->getOrderCancelContent($data,'QTCK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }

            case WMS_METHOD_TRANSFERIN_DIVERSE_CANCEL://取消调拨入库单,(出入仓库不同类型)
            {
                $content = $this->getOrderCancelContent($data,'QTRK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }

            case WMS_METHOD_TRANSFEROUT_CANCEL://取消调拨出库单(出入仓库同类型)
            {
                $content = $this->getOrderCancelContent($data,'DBCK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_TRANSFERIN_CANCEL://取消调拨入库单(出入仓库同类型)
            {
                $content = $this->getOrderCancelContent($data,'DBRK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_STOCKOUT_ADD:   //推送其他出库单
            {
                $content = $this->getOtherOutAddContent($data);
                $this->method = 'taobao.qimen.stockout.create';
                break;
            }
            case WMS_METHOD_STOCKOUT_CANCEL:    //取消其他出库单
            {
                $content = $this->getOrderCancelContent($data,'QTCK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
            case WMS_METHOD_STOCKIN_ADD:    //推送其他入库单
            {
                $content = $this->getOtherInAddContent($data);
                $this->method = 'taobao.qimen.entryorder.create';
                break;
            }
            case WMS_METHOD_STOCKIN_CANCEL: //取消其他入库单
            {
                $content = $this->getOrderCancelContent($data,'QTRK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }

            case WMS_METHOD_SYNC_STOCK://库存查询接口
            {
                $content = $this->getWarehouseSpecStock($data);
                $this->method = 'taobao.qimen.inventory.query';
                break;
            }

             case WMS_METHOD_STOCKOUT_STATUS: //出库单仓库流转状态
            {
                //出库单流转信息字段映射
                $content = $this->getStockoutAddContent($data);
                $this->method = 'taobao.qimen.orderprocess.query';
                break;
            }

            case WMS_METHOD_PURCHASE_RETURN_ADD: //推送采购退货单
            {
                $content = $this->getPurchaseOutContent($data);
                $this->method = 'taobao.qimen.stockout.create';
                break;
            }
            case WMS_METHOD_JIT_PICK_ADD:  // 推送JIT出库单
            {
                $content = $this->getJitAddOutContent($data);
                $this->method = 'taobao.qimen.stockout.create';
                break;
            }
            case WMS_METHOD_JIT_PICK_CANCEL: //取消推送JIT出库单
            {
                $content = $this->getOrderCancelContent($data,'B2BCK');
                $this->method = 'taobao.qimen.order.cancel';
                break;
            }
			case WMS_METHOD_PURCHASE_STOP_WAITING://采购入库单停止等待
			{
				$content = $this->getOrderStopWaitingContent($data,'CGRK');
				$this->method = 'taobao.qimen.order.pending';
				break;
			}
            default : break;
        }

        //生成url
        $params = array(
            'method'        =>  $this->method,
            'timestamp'     =>  $this->curTime,
            'format'        =>  'xml',
            'app_key'       =>  $this->appKey,
            'v'             =>  '2.0',
            'sign_method'   =>  'md5',
            'customerId'    =>  $this->customerId
            );
        //获取签名
        $sign = $this->generateSign($params,$content);
        $params['sign'] = $sign;
        //更新url
		$this->apiUrl = $this->baseUrl;
        $this->apiUrl .= '?'.http_build_query($params); 

        $this->sendParams['post_params']= $content;

        $this->weblogParams     = array(
            'interface_name'    => $this->method,
            'request_body'      => $content
        );
        return true;
    }

    //生成数字签名
    protected function generateSign($params,$contentInfo)
    {
        $str = $this->appSecret;
        ksort($params);
        foreach($params as $key=>$value)
        {
            if(!empty($key) && !empty($value))
                $str .= $key.$value;
        }
        $str .= $contentInfo;
        $str .= $this->appSecret;
        return strtoupper(md5($str));
    }

	private function getOrderStopWaitingContent($data,$type)
	{
		switch($type)
		{
			case 'CGRK'://采购入库
			$orderNo = $data['waiting']['outer_no'];
			$wmsOrderNo = $data['waiting']['wms_outer_no'];
			$subCustomerId = $data['waiting']['api_object_id'];
			break;
		}
		
		$orderInfo = array(
			'actionType'     => 'close',//协议type
			'warehouseCode'  => $this->extWarehouseNo,//外部仓库编码
			'ownerCode'      => isset($subCustomerId)?$subCustomerId:'',
			'orderCode'      => $orderNo,//单据编号
			'orderId'        => $wmsOrderNo,//WMS单据编号
			'orderType'      => $type,//单据类型
			'reason'         => '停止等待',
		);
		
		//格式化数据
		return OuterUtils::GetXMLBodyFromArray(array('request' => $orderInfo));
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

        $otherOutInfo['deliveryOrder'] = array(
            'deliveryOrderCode'     =>  $otherOut['outer_no'],//外部编号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'createTime'            =>  $otherOut['created'],//订单创建时间
            'orderType'             =>  $orderType,
            'logisticsCode'         =>  $otherOut['logistics_code'], //物流公司编码
            'supplierCode'          =>  $otherOut['provider_no'],//供应商编码
            'supplierName'          =>  $otherOut['provider_name'],//供应商名称
            'remark'                =>  OuterUtils::escape_xml_string($otherOut['remark'])//备注
        );
        //收件人信息
        $otherOutInfo['deliveryOrder']['receiverInfo'] = array(
            'name'                  =>  OuterUtils::escape_xml_string($otherOut['contact']),//姓名
            'mobile'                =>  $otherOut['telno'], //电话
            'province'              =>  $otherOut['province'], //省份
            'city'                  =>  $otherOut['city'],  //市 
            'detailAddress'         =>  OuterUtils::escape_xml_string($otherOut['receive_address'])  //详细地址
        );

        foreach($details as $detail)
        {
            $otherOutInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//其他出库单明细id
                'ownerCode'         =>  $otherOut['api_object_id'] == ''?$this->customerId:$otherOut['api_object_id'],//货主编码
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//条码
                'inventoryType'     =>  $otherOut['inventory_type'] == 0?'ZP':'CC',//库存类型
                'planQty'           =>  intval($detail['num']),//商品数量
            );
        }
        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $otherOutInfo));
    }

     //出库单流转信息字段映射
    private function getStockoutAddContent($data)
    {
        $stockout = $data['stockout'];
        
        $stockoutInfo = array(
            'orderCode'             =>  $stockout['stockout_no'],//单据号
            'orderId'               =>  $stockout['outer_no'],//仓储系统单据号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'orderType'             =>  'JYCK',//单据类型, 交易出库
            );

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $stockoutInfo));
    }

    //查询仓库库存
    private function getWarehouseSpecStock($data)
    {
        $wms            = $data['wms'];
        $warehouseSpecs = $data['spec'];
        foreach ($warehouseSpecs as $warehouseSpec) {
            $stockSpecInfo['criteriaList']['criteria'][] = array(
            'warehouseCode'     =>  $this->extWarehouseNo,//外部仓库编码
            'ownerCode'         =>  $wms['api_object_id'] == ''?$this->customerId:$wms['api_object_id'],//货主编码
            'itemCode'          =>  $warehouseSpec['spec_no'],//商家编码
            'itemId'            =>  $warehouseSpec['spec_wh_no2'],//仓库商家id
            );
        }
        /*
        $stockSpecInfo['criteriaList']['criteria'] = array(
            'warehouseCode'     =>  $this->extWarehouseNo,//外部仓库编码
            'ownerCode'         =>  $this->customerId,//货主编码
            'itemCode'          =>  $data['spec_no'],//商家编码
            'itemId'            =>  $data['spec_wh_no2'],//仓库商家id
            );
*/
        //$this->spec_no = $data['spec_no'];
        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $stockSpecInfo));
    }

    //推送调拨入库单
    private function getStockInContent($data,$type)
    {
        $transfer   = $data['transfer'];
        $details    = $data['details'];

        $tmpRemark = OuterUtils::escape_xml_string($transfer['remark']);
        $stockInInfo['entryOrder'] = array(
            'entryOrderCode'        =>  $transfer['outer_no'],//入库单号
            'ownerCode'             =>  $transfer['to_api_object_id'] == ''?$this->customerId:$transfer['to_api_object_id'],//货主编码
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'orderCreateTime'       =>  $transfer['created'],//订单创建时间
            'orderType'             =>  $type,//入库类型
            'remark'                =>  $tmpRemark//备注
            );
        $stockInInfo['entryOrder']['senderInfo'] = array(
                'company'               =>  '不详',//公司名称
                'name'                  =>  '不详',//姓名
                'province'              =>  '浙江',
                'city'                  =>  '杭州',
                'area'                  =>  '上城区',
                'detailAddress'         =>  '浙江 杭州 上城区'
                );
		$stockInInfo['entryOrder']['extendProps'] = array(
				'plan_flag' 			=> $transfer['plan_flag']==1?'plan':'planin',
				'src_warehouse'			=>	$transfer['from_ext_warehouse_no'],//来源仓库
				);

        foreach($details as $detail)
        {
            $stockInInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//明细id
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
                'ownerCode'         =>  $transfer['to_api_object_id'] == ''?$this->customerId:$transfer['to_api_object_id'],//货主编码
                'planQty'           =>  intval($detail['num']),//商品数量
                'inventoryType'     =>  'ZP'//类型正品
            );
        }

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $stockInInfo));
    }

    //推送调拨出库单
    private function getStockOutContent($data,$type)
    {
        $stockout_order = $data['transfer'];
        $stockout_details = $data['details'];
        $tmpRemark = OuterUtils::escape_xml_string($stockout_order['remark']);

        //菜鸟仓是PTCK
        if(($this->warehouseType === 'CN'||$this->warehouseType === 'XY')&&($type == 'QTCK'))//菜鸟只支持PTCK
        {
            $type = 'PTCK';
        }

        $orderInfo['deliveryOrder'] = array(
            'deliveryOrderCode' =>  $stockout_order['outer_no'],//出库单号
            'orderType'         =>  $type,//出库单类型
            'warehouseCode'     =>  $stockout_order['from_ext_warehouse_no'],//仓库编码
            'createTime'        =>  $stockout_order['created'],//创建时间
            'logisticsCode'     =>  $stockout_order['logistics_code'],//物流公司编码
            'remark'            =>  $tmpRemark
            );

		$orderInfo['deliveryOrder']['extendProps']['plan_flag'] = $stockout_order['plan_flag']==1?'plan':'planout';

        //收件人信息
        $tmpWarehouseAdr = OuterUtils::escape_xml_string($stockout_order['address']);//对中文进行特殊处理
        $tmpWarehouseName = OuterUtils::escape_xml_string($stockout_order['contact']);//对中文进行特殊处理
        $orderInfo['deliveryOrder']['receiverInfo'] = array(
            'name'                  =>  $tmpWarehouseName,//姓名
            'mobile'                =>  ($stockout_order['mobile']=='')?$stockout_order['telno']:$stockout_order['mobile'],//手机
            'tel'                   =>  $stockout_order['telno'],//固话
            'province'              =>  $stockout_order['province'],//省
            'city'                  =>  $stockout_order['city'],//市 
            'area'                  =>  $stockout_order['district'],//区
            'detailAddress'         =>  $tmpWarehouseAdr//详细地址
            );

        if($type == 'DBCK')//调拨出库，目标仓库编码
        {
            $orderInfo['deliveryOrder']['receiverInfo']['name'] = $stockout_order['to_ext_warehouse_no'];
        }

         //货品信息
        foreach($stockout_details as $detail)
        {
            $orderInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//明细id
                'ownerCode'         =>  $stockout_order['from_api_object_id'] == ''?$this->customerId:$stockout_order['from_api_object_id'],//货主编码
                'itemCode'          =>  $detail['spec_no'],//商家编码
                'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
             	'inventoryType'		=>  'ZP',
                'planQty'           =>  intval($detail['num']),//数量
                
                );
        }

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $orderInfo));
    }

    // Jit出库单字段映射
    private function getJitAddOutContent($data)
    {
        $trade = $data['trade'];
        $details = $data['details'];
        $type = 'B2BCK';

        $orderInfo['deliveryOrder'] = array(
            'deliveryOrderCode' =>  $trade['outer_no'],//出库单号
            'orderType'         =>  $type,//出库单类型
            'warehouseCode'     =>  $this->extWarehouseNo,//仓库编码
            'createTime'        =>  $trade['modified'],//推送审核时间
            'logisticsCode'     =>  $trade['logistics_code'],//物流公司编码
            'remark'            =>  $trade['po_no'].''.$trade['remark'],//po单号推送
            );
        //收件人信息
        $tmpWarehouseAdr = OuterUtils::escape_xml_string($trade['address']);//对中文进行特殊处理
        $tmpWarehouseName = OuterUtils::escape_xml_string($trade['contact']);//对中文进行特殊处理
        $orderInfo['deliveryOrder']['receiverInfo'] = array(
            'name'                  =>  $trade['name'],//姓名
            'mobile'                =>  ($trade['mobile']=='')?$trade['telno']:$trade['mobile'],//手机
            'province'              =>  $trade['province'],//省
            'city'                  =>  $trade['city'],//市 
            'area'                  =>  $trade['district'],//区
            'detailAddress'         =>  $tmpWarehouseAdr,//详细地址
            );
		$orderInfo['deliveryOrder']['extendProps'] = array(
				'vipWarehouseCode'  => $trade['warehouse_no'],   //巨沃定制需求warehousecode
				);
         //货品信息
        foreach($details as $detail)
        {
            $orderInfo['orderLines']['orderLine'][] = array(
                'ownerCode'         =>  $trade['api_object_id'] == ''?$this->customerId:$trade['api_object_id'],//货主编码
                'itemCode'          =>  $detail['spec_no'],//商家编码
                'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
                'inventoryType'     =>  'ZP',
                'planQty'           =>  intval($detail['num']),//数量
                
                );
        }

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $orderInfo));
    }

    //订单的字段映射
    private function getTradeAddContent($data)
    {
        $trade = $data['trade'];
        $details = $data['details'];
        //来源平台编码
        $PlatformCode = array(
            '1'     =>  'TB',
            '2'     =>  'TB',//淘宝分销
            '3'     =>  'JD',
            '4'     =>  'PP',
            '5'     =>  'AMAZON',
            '6'     =>  'YHD',
            '7'     =>  'DD',
            '9'     =>  '1688',
            '13'    =>  'SN',
            '14'    =>  'WPH',
            '15'    =>  'YX',
            '16'    =>  'JM',
            '21'    =>  'MGJ',
            '34'    =>  'MIA',
            '39'    =>  'PDD',
            );
		$send_type =array(
				0  => '标准快递',
				1  => '生鲜速配',
				2  => '冷运宅配',
				3  => '顺丰特惠',
				4  => '电商特惠',
				5  => '顺丰次晨',
				6  => '即日件',
				7  => '电商速配',
				8  => '顺丰宝平邮',
				9  => '顺丰宝挂号',
				10 => '医药常温',
				11 => '医药温控',
				12 => '物流普运',
				13 => '大闸蟹专递',
				14 => '汽配专线',
				15 => '汽配吉运',
				16 => '全球顺',
				17 => '行邮专列',
				18 => '医药专运（常温）',
				19 => '医药专运（温控）',
				20 => '国际特惠-文件',
				21 => '国际特惠-B 类包裹',
				22 => '国际特惠-D 类包裹',
				23 => '全球顺保税',
				24 => '全球顺商家代理',
				25 => '电商专配',
				27 => '云仓专配隔日',
				28 => '云仓专配次日'
				);
        $platFrom = '';
        if (array_key_exists($trade['shop_platform_id'],$PlatformCode))
        {
            if ($trade['shop_platform_id'] == 1 && $trade['sub_platform_id'] == 1)
            {
                $platFrom = 'TM';
            }
            else
            {
                $platFrom = $PlatformCode[$trade['shop_platform_id']];
            }
        }
        else
        {
            $platFrom = 'OTHER';
        }

		//顺丰货运方式
		$tSendType = '';
		if ($trade['logistics_code'] == 'SF' && array_key_exists($trade['send_type'],$send_type))
		{
			$tSendType = $send_type[$trade['send_type']];
		}

        //货到付款
        if($trade['delivery_term'] == 2)
        {
            $chargeType = 'COD';
            $chargecount = $trade['cod_amount'];
        }

		if(empty($trade['logistics_code']))
		{
			$trade['logistics_code'] = 'OTHER';
		}

        $trade_split_remark = '';
        $orderSplitFlag = 0;
        if($trade['split_from_trade_id'] != 0)
        {
            $orderSplitFlag = 1;
            $trade_split_remark = ' 注意：订单已拆分';
        }
	
	$tmpShopName = OuterUtils::escape_xml_string($trade['shop_name']);
	$tmpCsRemare = OuterUtils::escape_xml_string($trade['cs_remark']);
    $tmpBuyerNick = OuterUtils::escape_xml_string($trade['buyer_nick']);
    $buyerMessage = OuterUtils::escape_xml_string($trade['buyer_message']);
        //字段映射
        $orderInfo['deliveryOrder'] = array(
            'deliveryOrderCode'     =>  $trade['stockout_no'],//出库单号
            'orderType'             =>  'JYCK',//一般交易出库
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'orderFlag'             =>  isset($chargeType)?$chargeType:'',//货到付款
            'sourcePlatformCode'    =>  $platFrom,//订单来源平台编码
            'createTime'            =>  date("Y-m-d H:i:s"),//出库单创建时间
            'placeOrderTime'        =>  $trade['trade_time']=='0000-00-00 00:00:00'?date("Y-m-d H:i:s"):$trade['trade_time'],//下单时间
            //'payNo'                 =>  $trade['ChargeID'], //支付单号
            'payTime'               =>  $trade['pay_time']=='0000-00-00 00:00:00'?date("Y-m-d H:i:s"):$trade['pay_time'],//支付时间
            'operateTime'           =>  date("Y-m-d H:i:s"),//操作时间
            'shopNick'              =>  $tmpShopName,//店铺名称
            'buyerNick'             =>  $tmpBuyerNick,//买家昵称
            'totalAmount'           =>  round($trade['receivable'],2),//订单总金额
            'freight'               =>  round($trade['post_amount'],2),//邮资
            'logisticsCode'         =>  $trade['logistics_code'],//物流公司编码
            'invoiceFlag'           =>  ($trade['invoice_type']==0)?'N':'Y',//是否需要发票
            'buyerMessage'          =>  $buyerMessage,//买家留言
            'arAmount'              =>  isset($chargecount)?round($chargecount,2):0,
            'serviceFee'            =>  0,
            'sellerMessage'         => $tmpCsRemare.$trade_split_remark,//卖家留言
            'remark'                => $trade_split_remark,//是否拆单了
            'expressCode'           => @$trade['logistics_no'],//运单号
            );
        
        //发件人信息
        $tmpWarehouseAdr = OuterUtils::escape_xml_string($trade['warehouse_address']);//对中文进行特殊处理
        $tmpWarehouseName = OuterUtils::escape_xml_string($trade['warehouse_contact']);//对中文进行特殊处理
		$orderInfo['deliveryOrder']['senderInfo'] = array(
            'name'                  =>  $tmpWarehouseName,//姓名
            'mobile'                =>  $trade['warehouse_mobile'],//手机
            'province'              =>  $trade['warehouse_province'],//省
            'city'                  =>  $trade['warehouse_city'],//市 
            'area'                  =>  $trade['warehouse_district'],//区
            'detailAddress'         =>  $tmpWarehouseAdr//详细地址
            );

        //收件人信息
        $tmpAdr = OuterUtils::escape_xml_string($trade['receiver_address']);
       	$tmpName = OuterUtils::escape_xml_string($trade['receiver_name']); 
		$orderInfo['deliveryOrder']['receiverInfo'] = array(
            'name'                  =>  $tmpName,//姓名
            'mobile'                =>  $trade['receiver_mobile'],//手机
            'tel'                   =>  $trade['receiver_telno'],//电话号码
            'province'              =>  $trade['province'],//省
            'city'                  =>  $trade['city'],//市
            'area'                  =>  $trade['district'],//区
            'zipCode'               =>  $trade['receiver_zip'],//邮编
            'detailAddress'         =>  $trade['province'].' '.$trade['city'].' '.$trade['district'].' '.$tmpAdr//详细地址
            );

		//配送方式，冷链配送
		$tmpSendType = 'PTPS';
		switch($trade['send_type'])
		{
			case 0:
				$tmpSendType = 'PTPS';
				break;
			case 1:
			case 2:
				$tmpSendType = 'LLPS';
				break;
			case 26:
				$tmpSendType = 'HBP';
				break;
		}

		$orderInfo['deliveryOrder']['deliveryRequirements'] = array(
				'deliveryType'			=>	$tmpSendType,
				);

        //发票信息
	$tmpInvoiceTitle = OuterUtils::escape_xml_string($trade['invoice_title']);
	$tmpInvoiceContent = OuterUtils::escape_xml_string($trade['invoice_content']);
        $orderInfo['deliveryOrder']['invoices']['invoice'] = array(
            'type'                  =>  ($trade['invoice_type']==0)?'':'INVOICE' ,//'INOICE',//发票类型 普通发票
            'header'                =>  $tmpInvoiceTitle,//发票抬头
            'amount'                =>  round($trade['receivable'],2),//总金额
            'content'               =>  $tmpInvoiceContent//发票内容
            );
		if($trade['invoice_type']==0){
			unset($orderInfo['deliveryOrder']['invoices']);
		}//如果订单标签为空，unset订单字段

        //订单拆分标记
        $orderInfo['deliveryOrder']['extendProps'] = array(
                'key1'              => $orderSplitFlag, //0没有拆分，1拆分  和 巨沃对接的时候他们只能用key1，所以咱们这边随了他们那边用了key1,标记的是订单是否已经拆分过 
                'tax'               => isset($trade['tax'])?$trade['tax']:'',//海豚，税款
                'pay_id'            => isset($trade['pay_id'])?$trade['pay_id']:'',//海豚，支付交易号
                'pay_account'       => isset($trade['pay_account'])?$trade['pay_account']:'',//海豚，支付账号
                'buyer_name'        => isset($trade['buyer_name'])?$trade['buyer_name']:'',//海豚，买家姓名
                'id_card_type'      => isset($trade['id_card_type'])?$trade['id_card_type']:'',//海豚，买家证件类型
                'id_card'           => isset($trade['id_card'])?$trade['id_card']:'',//海豚，买家证件号
                'buyer_phone'       => isset($trade['buyer_phone'])?$trade['buyer_phone']:'',//海豚，买家手机号
                'receiver_id_card'  => isset($trade['receiver_id_card'])?$trade['receiver_id_card']:'',//海豚，收货人证件号
                'pay_ent_name'      => isset($trade['pay_ent_name'])?$trade['pay_ent_name']:'',//海豚，支付企业名称
                'pay_ent_no'        => isset($trade['pay_ent_no'])?$trade['pay_ent_no']:'',//海豚，支付企业编号
                'hz_purchaser_id'   => isset($trade['hz_purchaser_id'])?$trade['hz_purchaser_id']:'',//海豚，购买人平台ID
                'paid'              => isset($trade['paid'])?$trade['paid']:'',//海豚，已付款
                'insure_amount'     => isset($trade['insure_amount'])?$trade['insure_amount']:'',//保税
                'key2'              => isset($trade['trade_from'])?$trade['trade_from']:'', //巨沃的订单类型：接口抓取 1,手工建单 2,EXCEL导入3,现款销售4,接口推送 5   
                'trade_flag'        => isset($trade['flag_id'])?$trade['flag_id']:"", //发票标记字段   如果是500代表开发票
				'erpTradeNo'	    => $trade['trade_no'],//系统订单编号
				'trade_type'		=> isset($trade['trade_type'])?$trade['trade_type']:0,
				'send_type'			=> $tSendType, //配送方式
                'trade_shop_tel'    =>$trade['shop_mobile'],//店铺手机号
				);
		//货品信息
		$id = 1;
		$tmp_pay_ids ='';
		$pay_id = array();
		foreach($details as $detail)
		{
			//去除赠品原始单号的判断
			if(isset($detail['deleted_jod_gift_srctid_flag']) && ($detail['deleted_jod_gift_srctid_flag']=== 1) && isset($detail['gift_type']) && ($detail['gift_type']>0))
			{
				$detail['src_tid'] = '';//赠品去除原始单号
				if(isset($detail['pay_id']))
				{
					$detail['pay_id'] = '';
				} 
			}   

			//if($this->warehouseType === 'CN' && $detail['gift_type']>0 && $trade['trade_from']>1)
			if($this->warehouseType === 'CN' && $detail['gift_type']>0 )
			{
				$tmp_src_tids = explode(',',$trade['src_tids']);

						foreach($tmp_src_tids as $tmp_src_tid)
						{
							if(!strstr($tmp_src_tid,'AT'))
							{
								$detail['src_tid'] = $tmp_src_tid;
								break;
							}
						}

					    $detail['src_oid']= $trade['stockout_no'].$id;
			}
			$tmpName =  OuterUtils::escape_xml_string($detail['goods_name']);
			$orderInfo['orderLines']['orderLine'][] = array(
					'orderLineNo'       =>  $id,//明细id $detail['rec_id']
                'ownerCode'         =>  $trade['api_object_id'] == ''?$this->customerId:$trade['api_object_id'],//货主编码
					'itemCode'          =>  $detail['spec_no'],//商家编码
					'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
					'itemName'			=>  $tmpName,//商品名称
					'sourceOrderCode'   =>  $detail['src_tid'],//原始单号
					'subSourceOrderCode'=>	str_replace(':','-',$detail['src_oid']),//原始子单号
					'payNo'             =>  isset($detail['pay_id'])?$detail['pay_id']:'', //支付单号
					'planQty'           =>  intval($detail['num']),//数量
					'actualPrice'       =>  round($detail['price'],2),//卖出价格
					'discountAmount'    =>  round($detail['discount']/$detail['num'],2),//单件商品优惠金额
					);
			$id++;
			if(isset($detail['pay_id'])&&!empty($detail['pay_id']))//如果存在且不为空，生成新数组
			{
				$pay_id[] = $detail['pay_id'];
			}
		}
		if(!empty($pay_id))
		{
			$pay_id = array_unique($pay_id);//去重
			$tmp_pay_ids = rtrim(implode(",",$pay_id),",");//拼接
		}
        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $orderInfo));
        
    }

    //采购单的字段映射
    private function getPurchaseAddContent($data)
    {
        $purchase   = $data['purchase'];
        $details    = $data['details'];

	$tmpProviderName = OuterUtils::escape_xml_string($purchase['provider_name']);
	$tmpRemark = OuterUtils::escape_xml_string($purchase['remark']);
        $purchaseInfo['entryOrder'] = array(
            'entryOrderCode'        =>  $purchase['outer_no'],//入库单号
            'purchaseOrderCode'     =>  $purchase['outer_no'],//采购单号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'ownerCode'             =>  $purchase['api_object_id'] == ''?$this->customerId:$purchase['api_object_id'],//货主编码
            'orderCreateTime'       =>  $purchase['created'],//订单创建时间
            'orderType'             =>  'CGRK',//采购入库
			'expectStartTime'       =>  $purchase['expect_arrive_time'],//预期到货时间
			'supplierCode'          =>  $purchase['provider_no'],//供应商编码
			'supplierName'          =>  $tmpProviderName,//供应商名称
			'remark'                =>  $tmpRemark.' '.$purchase['purchase_no']//备注
			);
		$purchaseInfo['entryOrder']['senderInfo'] = array(
				'company'               =>  '不详',//公司名称
				'name'                  =>  '不详',//姓名
				'province'				=>	'浙江',
				'city'					=>	'杭州',
				'area'					=>	'上城区',
				'detailAddress'			=>	'浙江 杭州 上城区'
				);
        $purchaseInfo['entryOrder']['extendProps'] = array(
                'purchase_wms_add'      => $purchase['receive_address'], //收货地址
				'oms_purchase_no'		=> $purchase['purchase_no'],//采购单号
            );

        foreach($details as $detail)
        {
            $purchaseInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//采购单明细id
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
                'ownerCode'         =>  $purchase['api_object_id'] == ''?$this->customerId:$purchase['api_object_id'],//货主编码
                'planQty'           =>  intval($detail['num']),//商品数量
                'purchasePrice'     =>  round($detail['price'],2),//采购价格
                'inventoryType'     =>  'ZP'//类型正品
            );
        }

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $purchaseInfo));
    }

    //退货单的字段映射
    private function getRefundAddContent($data)
    {
        $refund   = $data['refund'];
        $details  = $data['details'];

        if(empty($refund['logistics_code']))
        {
            $refund['logistics_code'] = 'OTHER';
        }
	$tmpRemark = OuterUtils::escape_xml_string($refund['remark']); 
        $refundInfo['returnOrder'] = array(
            'returnOrderCode'       =>  $refund['outer_no'],//入库单号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'orderType'             =>  'THRK',//退货入库
            'preDeliveryOrderCode'  =>  $refund['src_stockout_no'],//原erp出库单号 
            'preDeliveryOrderId'	=>	$refund['src_outer_no'],//wms出库单号
			'logisticsCode'         =>  $refund['logistics_code'],//物流公司
            'expressCode'           =>  $refund['logistics_no'],//运单号
            'remark'                =>  $tmpRemark//备注
            );
        $refundInfo['returnOrder']['extendProps']['erpRefundNo']=$refund['refund_no'];//退货单号
        $refundInfo['returnOrder']['extendProps']['orderShopNo']=$refund['shop_name'];//店铺名称
	$tmpAdr = OuterUtils::escape_xml_string($refund['receiver_address']);
	$tmpName = OuterUtils::escape_xml_string($refund['receiver_name']);
        $refundInfo['returnOrder']['senderInfo'] = array(
            'name'                  =>  $tmpName,//姓名
            'tel'                   =>  $refund['receiver_telno'],//固话
            'mobile'                =>  $refund['receiver_mobile'],//手机
            'province'              =>  $refund['province'],//省份
            'city'                  =>  $refund['city'],//市
            'area'                  =>  $refund['district'],//区
            'detailAddress'         =>  $refund['province'].$refund['city'].$refund['district'].$tmpAdr//详细地址
            );

        foreach($details as $detail)
        {
            $refundInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['order_id'],//退货单明细id
                'ownerCode'         =>  $refund['api_object_id'] == ''?$this->customerId:$refund['api_object_id'],//货主编码
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//仓库的商家编码
                'inventoryType'     =>  'ZP',
                'planQty'           =>  intval($detail['refund_num'])//商品数量
            );
        }

        return OuterUtils::GetXMLBodyFromArray(array('request' => $refundInfo));
    }

    //其他入库单的字段映射
    private function getOtherInAddContent($data)
    {
        $otherIn    = $data['otherIn'];
        $details    = $data['details'];

        $otherInInfo['entryOrder'] = array(
            'entryOrderCode'        =>  $otherIn['outer_no'],//入库单号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'ownerCode'             =>  $otherIn['api_object_id'] == ''?$this->customerId:$otherIn['api_object_id'],//货主编码
            'orderCreateTime'       =>  $otherIn['order_created'],//订单创建时间
            'orderType'             =>  'QTRK',//其他入库
            'expectStartTime'       =>  '',//预期到货时间
            'supplierCode'          =>  '',//供应商编码
            'supplierName'          =>  '',//供应商名称
            'remark'                =>  OuterUtils::escape_xml_string($otherIn['order_remark'])//备注
        );
        $otherInInfo['entryOrder']['senderInfo'] = array(
            'company'               =>  '不详',//公司名称
            'name'                  =>  '不详',//姓名
            'province'				=>	'浙江',
            'city'					=>	'杭州',
            'area'					=>	'上城区',
            'detailAddress'			=>	'浙江 杭州 上城区'
        );

        if (!empty($otherIn['order_prop1']))
        {
            switch ($otherIn['order_prop1'])
            {
                case '加工入库':
                    $receipt_type = 'JGRK';
                    break;
                case '报溢入库':
                    $receipt_type = 'BYRK';
                    break;
                case '样品入库':
                    $receipt_type = 'YPRK';
                    break;
                case '客退疑难件入库':
                    $receipt_type = 'KTRK';
                    break;
                default:
                    $receipt_type = '';
            }

            $otherInInfo['entryOrder']['extendProps'] = array(
                'receipt_type' => $receipt_type
            );
        }

        foreach($details as $detail)
        {
            $otherInInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//其他入库单明细id
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//条码
                'ownerCode'         =>  $otherIn['api_object_id'] == ''?$this->customerId:$otherIn['api_object_id'],//货主编码
                'planQty'           =>  intval($detail['num']),//商品数量
                'inventoryType'     =>  $otherIn['inventory_type'] == 0?'ZP':'CC',//库存类型
                'batchCode'         =>  '',//批次
            );
        }

        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $otherInInfo));
    }

    //其他出库单的字段映射
    private function getOtherOutAddContent($data)
    {
        $otherOut    = $data['otherOut'];
        $details     = $data['details'];
        $transportMode = array(
            0 => '到仓自提',
            1 => '快递',
            2 => '干线物流'
        );
        //菜鸟,心怡走PTCK
        $orderType = 'QTCK';
        if($this->warehouseType === 'CN'||$this->warehouseType === 'XY')
		{
			$orderType = 'PTCK';
		}
        $otherOutInfo['deliveryOrder'] = array(
            'deliveryOrderCode'     =>  $otherOut['outer_no'],//外部编号
            'warehouseCode'         =>  $this->extWarehouseNo,//外部仓库编码
            'createTime'            =>  $otherOut['order_created'],//订单创建时间
            'orderType'             =>  $orderType,
            'logisticsCode'         =>  $otherOut['logistics_code'],
            'transportMode'         =>  $transportMode[$otherOut['transport_mode']],
            'remark'                =>  OuterUtils::escape_xml_string($otherOut['order_remark'])//备注
        );
        $otherOutInfo['deliveryOrder']['receiverInfo'] = array(
            'name'                  =>  $otherOut['receiver_name'],//姓名
            'zipCode'               =>  $otherOut['receiver_zip'],
            'mobile'                =>  $otherOut['receiver_mobile'],
            'province'				=>	$otherOut['receiver_province'],
            'city'					=>	$otherOut['receiver_city'],
            'area'					=>	$otherOut['receiver_district'],
            'detailAddress'			=>	$otherOut['receiver_address']
        );

        if (!empty($otherOut['order_prop1']))
        {
            switch ($otherOut['order_prop1'])
            {
                case '加工出库':
                    $shipment_type = 'JGCK';
                    break;
                case '样品出库':
                    $shipment_type = 'YPCK';
                    break;
                case '正品报损出库':
                    $shipment_type = 'ZPBSCK';
                    break;
                case '次品报损出库':
                    $shipment_type = 'CPBSCK';
                    break;
                case '猫超供货':
                    $shipment_type = 'TMCK';
                    break;
                case '分销供货':
                    $shipment_type = 'FXCK';
                    break;
                default:
                    $shipment_type = '';
            }
			$otherOutInfo['deliveryOrder']['extendProps']['shipment_type'] = $shipment_type;
        }

		if (!empty($otherOut['order_prop2']))
		{
			$otherOutInfo['deliveryOrder']['extendProps']['platform_code'] = $otherOut['order_prop2'];
		}

        foreach($details as $detail)
        {
            $otherOutInfo['orderLines']['orderLine'][] = array(
                'orderLineNo'       =>  $detail['rec_id'],//其他出库单明细id
                'ownerCode'         =>  $otherOut['api_object_id'] == ''?$this->customerId:$otherOut['api_object_id'],//货主编码
                'itemCode'          =>  $detail['spec_no'],//商品编码
                'itemId'            =>  $detail['spec_wh_no2'],//条码
                'inventoryType'     =>  $otherOut['inventory_type'] == 0?'ZP':'CC',//库存类型
                'planQty'           =>  intval($detail['num']),//商品数量
            );
        }
        //格式化数据
        return OuterUtils::GetXMLBodyFromArray(array('request' => $otherOutInfo));
    }

    //取消单据的字段映射
    private function getOrderCancelContent($data,$type)
    {
        switch($type)
        {
            case 'JYCK'://一般交易出库
                $orderno = $data['trade']['stockout_no'];
                $wmsOrderNo = $data['trade']['outer_no'];
                $subCustomerId = $data['trade']['api_object_id'];
                break;
            case 'CGRK'://采购入库
                $orderno = $data['purchase']['outer_no'];
                $wmsOrderNo = $data['purchase']['wms_outer_no'];
                $subCustomerId = $data['purchase']['api_object_id'];
                break;
            case 'XTRK'://销退入库
                $orderno = $data['refund']['outer_no'];
                $wmsOrderNo = $data['refund']['wms_outer_no'];
                $subCustomerId = $data['refund']['api_object_id'];
                break;
            case 'CGTH'://采购退货
                $orderno = $data['return']['outer_no'];
                $wmsOrderNo = $data['return']['wms_outer_no'];
                $subCustomerId = $data['return']['api_object_id'];
                break;
            case 'DBCK':
            case 'QTCK'://to_wms_order_no
                
                //菜鸟仓是PTCK
                if( ($this->warehouseType === 'CN' ||$this->warehouseType === 'XY')&&($type == 'QTCK'))//菜鸟只支持PTCK
                {
                    $type = 'PTCK';
                }

                if (isset($data['otherOut']))
                {
                    $orderno = $data['otherOut']['outer_no'];
                    $wmsOrderNo = $data['otherOut']['wms_outer_no'];
                    $subCustomerId = $data['otherOut']['api_object_id'];
                }
                if (isset($data['transfer']))
                {
                    $orderno = $data['transfer']['outer_no'];
                    $wmsOrderNo = $data['transfer']['from_wms_order_no'];
                    $subCustomerId = $data['transfer']['from_api_object_id'];
                }
                break;
            case 'DBRK':
            case 'QTRK':
                if (isset($data['otherIn']))
                {
                    $orderno = $data['otherIn']['outer_no'];
                    $wmsOrderNo = $data['otherIn']['wms_outer_no'];
                    $subCustomerId = $data['otherIn']['api_object_id'];
                }
                if (isset($data['transfer']))
                {
                    $orderno = $data['transfer']['outer_no2'];
                    $wmsOrderNo = $data['transfer']['to_wms_order_no'];
                    $subCustomerId = $data['transfer']['to_api_object_id'];
                }
                break;
            case 'B2BCK':
                if (isset($data['JitOut']))
                {
                    $orderno = $data['JitOut']['outer_no'];
                    $wmsOrderNo = $data['JitOut']['wms_outer_no'];
                    $subCustomerId = $data['JitOut']['api_object_id'];
                }
                break;
        }
        $cancelInfo = array(
            'warehouseCode'     =>  $this->extWarehouseNo,//外部仓库编码
            'ownerCode'         =>  $subCustomerId == ''?$this->customerId:$subCustomerId,//货主编码
            'orderCode'         =>  $orderno,//单据编号
            'orderId'           =>  $wmsOrderNo,//仓库单据编号
            'orderType'         =>  $type//单据类型
            );
 
        return OuterUtils::GetXMLBodyFromArray(array('request' => $cancelInfo));
    }

    //奇门批量上传货品字段
    private function getSkuInfosContent($data,$type)
    {
        $spec_info = $data['spec'];

        if(!array_key_exists(0,$spec_info))
        {
            $spec_info = array($spec_info);
        }

        $goodsInfos = array(
            'actionType'    =>  $type,
            'warehouseCode' =>  $this->extWarehouseNo,//仓库编码, string (50)，必填
            'ownerCode'     =>  $spec_info[0]['api_object_id'] == ''?$this->customerId:$spec_info[0]['api_object_id']
            );

		foreach($spec_info as $spec)
        {
          $tmpName      = OuterUtils::escape_xml_string($spec['goods_name']);
          $tmpGoodsName = OuterUtils::escape_xml_string($spec['goods_name']);
          $tmpPorpety   = OuterUtils::escape_xml_string($spec['spec_name']);
          $tmpBrandName = OuterUtils::escape_xml_string($spec['brand_name']);
          $volume       = round(($spec['length']*$spec['width']*$spec['height'])/1000,3);

		       $goodsInfo['item'][]   =   array(
                    'itemCode'          =>$spec['spec_no'],//商品编码,  string (50) ,  必填 
                    'itemId'            =>isset($spec['spec_wh_no2'])?$spec['spec_wh_no2']:'',//仓储系统商品编码, string (50) , 条件必填, 条件为商品同步接口, 出参itemId不为空 
                    'goodsCode'         =>$spec['goods_no'],//货号，string（50）
                    'itemName'          =>$tmpName,//商品名称,  string (200) , 必填
                    'shortName'         =>$tmpGoodsName,//商品简称,  string (200) 
                    'barCode'           =>$spec['barcode'],//条形码,  string (500) , 可多个，用分号（;）隔开，必填
                    'skuProperty'       =>$tmpPorpety,//商品属性 (如红色, XXL) ,  string (200)   
                    'length'            =>round($spec['length'],2),//长 (厘米) ,  double (18, 2) 
                    'width'             =>round($spec['width'],2),//宽 (厘米) ,  double (18, 2)
                    'height'            =>round($spec['height'],2),//高 (厘米) ,  double (18, 2) 
                    'volume'            =>$volume,//体积 (升) ,  double (18, 3)
                    'grossWeight'       =>round($spec['weight'],2),//毛重 (千克) ,  double (18, 3) 
                    'netWeight'         =>round($spec['weight'],2),//净重 (千克) ,  double (18, 3) 
                    'categoryId'        =>$spec['class_id'],//商品类别ID, string (50) 
                    'categoryName'      =>$spec['class_name'],//商品类别名称,  string (200)  
                    'itemType'          =>'ZC',//商品类型 (ZC=正常商品, FX=分销商品, ZH=组合商品, ZP=赠品, BC=包材, HC=耗材, FL=辅料, XN=虚拟品, FS=附属品, CC=残次品, OTHER=其它) ,  string (10) , 必填,  (只传英文编码) 
                    'retailPrice'       =>round($spec['retail_price'],2),//零售价, double (18, 2) 
                    'brandCode'         =>'',//品牌代码,  string (50)
                    'brandName'         =>$tmpBrandName,//品牌名称,  string (50)   
                    'remark'            =>$spec['remark'],//备注,  string (500) 
                    'extendProps'     => array(
                        'spec_property' => isset($spec['auxUnitName'])?$spec['auxUnitName']:"",//辅助单位名称
                        'spec_ratio'    => isset($spec['auxUnitRatio'])?$spec['auxUnitRatio']:'',//辅助单位换算系数
                        'specprop1'     =>  $spec['specProp1'], //单品自定义属性1
                        'goodsprop1'    =>  $spec['goodsProp1'],//货品自定义属性1
                        'specprop2'     =>  $spec['specProp2'], //单品自定义属性2
                        'goodsprop2'    =>  $spec['goodsProp2'],//货品自定义属性2
                        'specprop3'     =>  $spec['specProp3'], //单品自定义属性3
                        'goodsprop3'    =>  $spec['goodsProp3'],//货品自定义属性3
                        'specprop4'     =>  $spec['specProp4'], //单品自定义属性4
                        'goodsprop4'    =>  $spec['goodsProp4'],//货品自定义属性4
                        'specprop5'     =>  $spec['specProp5'], //单品自定义属性5
                        'goodsprop5'    =>  $spec['goodsProp5'],//货品自定义属性5
                        'specprop6'     =>  $spec['specProp6'], //单品自定义属性6
                        'goodsprop6'    =>  $spec['goodsProp6'],//货品自定义属性6
                       ), 
            	);	
		}
		$goodsInfos['items'] = $goodsInfo;
		return OuterUtils::GetXMLBodyFromArray(array('request' => $goodsInfos));   
    }

    //商品信息的字段映射
    private function getSkuInfoContent($data,$type)
    {
        $spec_info  = $data;
        $goodsInfo = array(
                'actionType'    =>  $type == 'add' && $spec_info['spec']['spec_wh_no'] == ''?'add':'update',
                'warehouseCode' =>  $this->extWarehouseNo,//外部仓库编码
                'ownerCode'     =>  $spec_info['spec']['api_object_id'] == ''?$this->customerId:$spec_info['spec']['api_object_id'],//货主编码
                );
        foreach($spec_info as $spec)
        {
		  $tmpName      = OuterUtils::escape_xml_string($spec['goods_name']);
          $tmpGoodsName = OuterUtils::escape_xml_string($spec['goods_name']);
          $tmpPorpety   = OuterUtils::escape_xml_string($spec['spec_name']);
          $tmpBrandName = OuterUtils::escape_xml_string($spec['brand_name']);
          $volume       = round(($spec['length']*$spec['width']*$spec['height'])/1000,3);

            $goodsInfo['item'][] = array(
                'itemCode'      =>  $spec['spec_no'],//商家编码
                'itemId'        =>  $spec['spec_wh_no2'],//仓库编码
				'itemName'      =>  $tmpName,//商品名称
                'shortName'     =>  $tmpGoodsName,//货品名称
				'goodsCode'		=>	$spec['goods_no'],//货品编码
                'skuProperty'   =>  $tmpPorpety,//规格
                'stockUnit'     =>  isset($spec['unitName'])?$spec['unitName']:'',//基本单位
                'barCode'       =>  $spec['barcode'],//条形码
                'length'        =>  round($spec['length'],2),//长
                'width'         =>  round($spec['width'],2),//宽
                'height'        =>  round($spec['height'],2),//高
                'volume'        =>  $volume,//体积 升
                'itemType'      =>  'ZC',//正常商品
                'netWeight'     =>  round($spec['weight'],2),//净重
                'grossWeight'   =>  round($spec['weight'],2),//毛重
                'retailPrice'   =>  round($spec['retail_price'],2),//零售价
				'categoryId'	=>	$spec['class_id'],//分类id
				'categoryName'	=>	$spec['class_name'],//分类名称
                'brandCode'     =>  '',//品牌编码
				'brandName'		=>	$tmpBrandName,//品牌名称
                'shelfLife'     =>  $spec['validity_days']*24,//保质期 (小时)
                'originAddress' =>  $spec['origin'], //原产地
                'extendProps'	=> array(
					'spec_property' => isset($spec['auxUnitName'])?$spec['auxUnitName']:"",//辅助单位名称
                    'spec_ratio'    =>isset($spec['auxUnitRatio'])?$spec['auxUnitRatio']:'',//辅助单位换算系数
					'specprop1'	=>	$spec['specProp1'],//单品自定义属性1
					'goodsprop1'=>  $spec['goodsProp1'],//货品自定义属性1
					'specprop2'	=>	$spec['specProp2'],//单品自定义属性2
					'goodsprop2'=>  $spec['goodsProp2'],//货品自定义属性2
					'specprop3'	=>	$spec['specProp3'],//单品自定义属性3
					'goodsprop3'=>  $spec['goodsProp3'],//货品自定义属性3
					'specprop4'	=>	$spec['specProp4'],//单品自定义属性4
					'goodsprop4'=>  $spec['goodsProp4'],//货品自定义属性4
					'specprop5'	=>	$spec['specProp5'],//单品自定义属性5
					'specprop6'	=>	$spec['specProp6'],//单品自定义属性6
					'goodsprop5'=>  $spec['goodsProp5'],//货品自定义属性5
					'goodsprop6'=>  $spec['goodsProp6'],//货品自定义属性6
					),
				);
            //$goodsInfo['item']['extendProps']['spec_property'] = isset($spec['auxUnitName'])?$spec['auxUnitName']:"";
        }

        return OuterUtils::GetXMLBodyFromArray(array('request' => $goodsInfo));
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
        if(empty($retval))
        {
            return array('code' => -1, 'error_msg' => '奇门服务器失败', 'retry_flag' => 0);
        }
        if(is_array($retval))
        {
            return $retval;
        }        
        $retval = OuterUtils::ReplaceHtmlSpecialCharacter($retval);
        $data = OuterUtils::Xml2Array($retval);

        if($data === false)
        {
            return array('code' => -2, 'error_msg' => '奇门返回信息异常:'.print_r($retval,true), 'retry_flag' => 0);
        }
        $retval = $data;


        //以下验证应用级别错误

        if(array_key_exists('flag',$retval))
        {
            $message = isset($retval['message'])?$retval['message']:'';
            $code = isset($retval['code'])?$retval['code']:'';
            
            if($retval['flag'] == 'success')
            {
                $revinfo = '';
                switch($api_method)
                {
                    case WMS_METHOD_SKU_ADD:   //添加商品
					case WMS_METHOD_SKU_MODIFY://修改商品
                        $revinfo = isset($retval['itemId'])?$retval['itemId']:'';   
                        break;
                    case WMS_METHOD_PURCHASE_ADD: //采购单
                    case WMS_METHOD_STOCKIN_ADD://其他入库单
                    case WMS_METHOD_TRANSFERIN_SAME_ADD://调拨入库单
                    case WMS_METHOD_TRANSFERIN_DIVERSE_ADD://调拨入库单
                        $revinfo = isset($retval['entryOrderId'])?$retval['entryOrderId']:'';
                        break;
                    case WMS_METHOD_REFUND_ADD:   //退货单 returnOrderId
                        $revinfo = isset($retval['returnOrderId'])?$retval['returnOrderId']:'';
                        break;
                    case WMS_METHOD_TRADE_ADD:     //订单 
                    case WMS_METHOD_STOCKOUT_ADD:   //其他出库单
                    case WMS_METHOD_TRANSFEROUT_SAME_ADD:   //调拨单
					case WMS_METHOD_TRANSFEROUT_DIVERSE_ADD://调拨单
                    case WMS_METHOD_PURCHASE_RETURN_ADD: //采购退货单
                    case WMS_METHOD_JIT_PICK_ADD: // B2B出库单   JIT出库单
                        $revinfo = isset($retval['deliveryOrderId'])?$retval['deliveryOrderId']:'';
                        break;

                    case WMS_METHOD_SYNC_STOCK://查询库存接口
                        $revinfo = $retval;
                        break;

                    case WMS_METHOD_STOCKOUT_STATUS: //出库单仓库流转状态
                        //判断字段有没有存在 然后转换成汉字
                        $tmp = $this->getOrderProcess($retval,$revinfo);
                        if($tmp === -1)
                        {
                            return array('code' => 1, 'error_msg' => '仓库返回信息'.$message);
                        }
                        break;
                }
                return array('code' => 0, 'error_msg' => $message,'rev_info' => $revinfo, 'retry_flag' => 0);
            }
            else if($retval['flag'] == 'failure')
            {
                if(($code === 'TOP15') && (!empty($message)))
                {
                    if($message === "Remote service error | isp.http-read-timeout")//处理超时
                    {
                        return array('code' => 99, 'error_msg' => "仓库处理超时，请联系仓库人员以防单据重复", 'retry_flag' => 1);
                    }
                    else if(($message === "Remote service error | isp.http-connection-timeout" ) || ($message === "Remote service error | isp.http-connection-refuse") || ($message === "Remote service error | isp.http-closed"))//套接字建立失败或连接被拒
                    {
                        return array('code' => 98, 'error_msg' => "仓库服务器连接出错，以防重复推单,请联系仓库人员确认单据是否创建", 'retry_flag' => 1);
                    }
                }
				if($api_method == WMS_METHOD_SKUS_ADD)
				{
					$items = isset($retval['items'])?$retval['items']:'';
					return array('code' => 1, 'error_msg' => $message, 'items' => $items, 'retry_flag' => 0);
            }
				return array('code' => 1, 'error_msg' => $message, 'retry_flag' => 0);
			}
                
        }
        
        return array('code' => 2, 'error_msg' => '奇门返回信息异常:'.print_r($retval,true), 'retry_flag' => 0);
    }

    //查询出库单在仓库内流转信息的反馈信息处理
    private function getOrderProcess($retval,&$revinfo)
    {
        //对$revinfo，$message进行赋值
        $order_pos = isset($retval['orderProcess']['processes']['process'])? $retval['orderProcess']['processes']['process']:-1;
        //
        if($order_pos ===-1)
		{
			return -1;
		}

        $temp = '';
        $processStatusInfo = array(
            'NEW'               => '新增',
            'ACCEPT'            => '仓库接单',
            'PRINT'             => '打印',
            'PICK'              => '捡货',
            'CHECK'             => '复核',
            'PACKAGE'           => '打包',
            'WEIGH'             => '称重',
            'READY'             => '待提货',
            'DELIVERED'         => '已发货',
            'EXCEPTION'         => '捡货',
            'CLOSED'            => '关闭',
            'CANCELED'          => '取消',
            'REJECT'            => '仓库拒单',
            'REFUSE'            => '客户拒签',
            'CANCELEDFAIL'      => '取消失败',
            'SIGN'              => '签收',
            'TMSCANCELED'       => '快递拦截',
            'PARTFULFILLED'     => '部分收货完成',
            'FULFILLED'         => '收货完成',
            'PARTDELIVERED'     => '部分发货完成',
            'OTHER'             => '其他',
            'ERROR'             =>  '未知状态'
        );
        //创建 数值数组
        if(!array_key_exists(0, $order_pos))
        {
            $order_pos = array($order_pos);
        }

        foreach($order_pos as $order_po)
        {
            $revinfo = isset($order_po['processStatus'])? $order_po['processStatus']: -1;
            if($revinfo === -1)
            {
                return -1;
            }
            
            //遍历数组
            foreach($processStatusInfo as $key => $value)
            {
                if( $revinfo == $key)
                {
                    $temp .= $value;
                    $temp .= ' ';
                    break;
                }

                if($key === 'ERROR')
                {
                    $temp .= $value;
                    $temp .= ' ';
                    break;
                }
            }
            //  打印，复核，
        }
        
        $revinfo = $temp;

        return 0;


    }

    //物流匹配
    public function getLogisticsCompanies()
    {

        $log_arr = array(
                'SF'        => '顺丰速运',
                'EMS'       => 'EMS',
                'EYB'       => 'EMS经济快递',
                'ZJS'       => '宅急送',
                'YTO'       => '圆通速递',
                'ZTO'       => '中通快递',
                'HTKY'      => '百世汇通',
                'BEST'      => '百世物流',
                'STO'       => '申通快递',
                'TTKDEX'    => '天天快递',
                'QFKD'      => '全峰快递',
                'POST'     =>  '中国邮政',
                'POSTB'     => '邮政国内小包',
                'GTO'       => '国通快递',
                'YUNDA'     => '韵达快递',
                'JD'        => '京东配送',
				'JBD'		=> '京邦达(京东快递)',
                'DD'        => '当当宅配',
				'AMAZON'	=> '亚马逊物流',
				'CRE'	    => '中铁快运',
				'ZY'	    => '中远',
				'LB'	    => '龙邦速递',
				'FAST'	    => '快捷快递',
				'QRT'	    => '增益速递',
				'UNIPS'	    => '发网',
				'LTS'	    => '联昊通',
				'FEDEX'	    => '联邦快递',
				'DBL'	    => '德邦物流',
				'SHQ'	    => '华强物流',
				'WLB-STARS' => '星辰急便',
				'AIR'	    => '亚风',
				'CYEXP'	    => '长宇',
				'DTW'	    => '大田',
				'YUD'	    => '长发',
				'ANTO'	    => '安得',
				'CCES'	    => 'CCES',
				'DFH'	    => '东方汇',
				'SY'	    => '首业',
				'YC'	    => '远长',
				'XB'	    => '新邦物流',
				'UC'	    => '优速快递',
				'NEDA'	    => '港中能达',
				'UAPEX'	    => '全一快递',
				'YCT'	    => '黑猫宅急便',
				'WLB-ABC'	=> '浙江ABC',
				'HZABC'	    => '飞远(爱彼西)配送',
				'SCKJ'	    => '四川快捷',
				'GZLT'	    => '飞远配送',
				'SCWL'	    => '尚橙物流',
				'GDEMS'	    => '广东EMS',
				'BJCS'	    => '城市100',
				'ZHQKD'	    => '汇强快递',
				'ESB'	    => 'E速宝',
				'BJEMS'	    => '同城快递',
                //''	    => '贝业新兄弟',
                //''	    => '北京EMS',
				//''	    => '佳吉快递',
				//''	    => '凡宇速递',
				//''	    => '天地华宇',
				//''	    => '居无忧',
				//''	    => '美国速递',
				//''	    => '派易国际物流77',
				//''	    => 'RUSTON',
				//''	    => '速尔',
				//''	    => '信丰物流',
				//''	    => '燕文北京',
				//''	    => '燕文广州',
				//''	    => '燕文国际',
				//''	    => '燕文上海',
				//''	    => '燕文深圳',
				//''	    => '燕文义乌',
				//''	    => '合众阳晟',
				//''	    => 'ZTOGZ',
				//''	    => 'ZTOSH',
                //''	=> '青岛日日顺',
                'ESBLL'	    => '保宏物流',
                //''	    => '圆通航运',
                'DISTRIBUTOR_12017865'=> '安能物流',
				'B2B-1669519933'	  => '卡行天下',
				'DISTRIBUTOR_13323734'=> '九曳鲜配',
                'OTHER'     => '其他'
                    );
        //手动加载匹配物流公司
        foreach($log_arr as $code=>$name)
        {
            $logistics_companies[] = array
                (
                 'logistics_code'   => $code,
                 'name'             => $name,
                 'created'          => array('NOW()')
                );
        }

        return $logistics_companies;
    }
}

?>
