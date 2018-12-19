<?php
namespace Platform\Wms;
require_once(ROOT_DIR . '/Wms/OuterUtils.php');
define('WMS_METHOD_TRADE_ADD',1);//推送订单
define('WMS_METHOD_PURCHASE_ADD',2);//推送采购单
define('WMS_METHOD_REFUND_ADD',3);//推送退货单
define('WMS_METHOD_TRADE_CANCEL',4);//取消订单
define('WMS_METHOD_PURCHASE_CANCEL',5);//取消采购单
define('WMS_METHOD_REFUND_CANCEL',6);//取消退货单
define('WMS_METHOD_SKU_ADD',7);//添加货品
define('WMS_METHOD_SKU_MODIFY',8);//修改货品
define('WMS_METHOD_STOCKOUT_ADD',9);//推送其他出库单
define('WMS_METHOD_STOCKOUT_CANCEL', 10); //取消其他出库单

define('WMS_METHOD_STOCKOUT_QUERY_MULTI', 11);
define('WMS_METHOD_STOCKIN_QUERY_MULTI', 12);
define('WMS_METHOD_STOCKOUT_CONFIRM', 13);
define('WMS_METHOD_STOCKIN_CONFIRM', 14);
define('WMS_METHOD_PANDIAN_QUERY', 15);
define('WMS_METHOD_PANDIAN_CONFIRM', 16);

define('WMS_METHOD_STOCKIN_ADD', 17);	//推送其他入库单
define('WMS_METHOD_STOCKIN_CANCEL', 18);	//取消其他入库单
define('WMS_METHOD_TRANSFEROUT_SAME_ADD', 19); //推送调拨出库单(出入仓库同类型)
define('WMS_METHOD_TRANSFEROUT_DIVERSE_ADD', 20); //推送调拨出库单(出入仓库不同类型)
define('WMS_METHOD_TRANSFERIN_SAME_ADD', 21);  //推送调拨入库单(出入仓库同类型)
define('WMS_METHOD_TRANSFERIN_DIVERSE_ADD', 22);  //推送调拨入库单(出入仓库不同类型)
define('WMS_METHOD_TRANSFEROUT_CANCEL', 23); //取消调拨出库单
define('WMS_METHOD_TRANSFERIN_CANCEL', 24); //取消调拨入库单
define('WMS_METHOD_TRANSFEROUT_DIVERSE_CANCEL',25);//取消调拨出库单,(出入仓库不同类型)
define('WMS_METHOD_TRANSFERIN_DIVERSE_CANCEL',26);//取消调拨入库单,(出入仓库不同类型)
define('WMS_METHOD_SYNC_STOCK',27);//库存查询接口
define('WMS_METHOD_STOCKOUT_STATUS', 28);//出库单在仓库流转状态查询
define('WMS_METHOD_PURCHASE_RETURN_ADD', 29);//推送采购退货
define('WMS_METHOD_PURCHASE_RETURN_CANCEL',30);//取消采购退货单

define('WMS_METHOD_JIT_REFUND_ADD',31);//推送jit退货
define('WMS_METHOD_JIT_REFUND_CANCEL', 32);//取消jit退货
define('WMS_METHOD_JIT_PICK_ADD',33);// 推送JIT出库单
define('WMS_METHOD_JIT_PICK_CANCEL', 34);//取消推送JIT出库单
define('WMS_METHOD_SKUS_ADD', 35);//批量推送货品信息
define('WMS_METHOD_PURCHASE_STOP_WAITING', 36);//采购单停止等待

define('WMS_METHOD_PURCHASE_PLAN_ORDER' ,41);//计划单
define('WMS_METHOD_PURCHASE_PLAN_CANCEL',42);//取消计划单
//TOP全渠道
define('TOP_ADD_STORE',101);//TOP创建门店
define('TOP_MODIFY_STORE', 102);//TOP更新门店
define('TOP_DELETE_STORE',103);//TOP删除门店
define('TOP_ADD_STORE_GOODS', 104);//TOP商品绑定门店
define('TOP_DELETE_STORE_GOODS', 105);//TOP商品解除门店绑定
define('TOP_GET_STORECATEGORY',106);//TOP获取门店类目
define('TOP_SYNC_TRADE_STATUS', 107);//TOP同步单据状态
define('TOP_SYNC_SPEC_INVENTORY', 108);//TOP同步库存

//电子发票
define('INVOICE_GET_SERIALNO',130);//获取流水号
define('INVOICE_ADD',131);//发送开票申请
define('INVOICE_GET_RESULT',132);//获取开票结果
define('INVOICE_SYNC',133);//发票同步至平台


class WmsAdapter
{
    public $retryCount = 0;
    public $wmsType = 0; //wms仓储的类型
    public $wmsClient = null;  //wms仓储对象
    private $sendParams = '';  //发送给wms接口的数据参数
    private $received = '';  //接收到的接口返回数据
    private $connet_error = '';//连接异常信息

    function __construct($wms_type,$wms_info)
    {
        $wms = &$this->wmsClient;
		$this->wmsType = $wms_type;

		switch($wms_type)
        {
            case 5://百世
            {
                require_once(ROOT_DIR . '/modules/adapter/wms_bs.php');
                $wms = new WmsBS($wms_info);
                break;
            }
	        case 6://SKU360
	        {
		        require_once(ROOT_DIR . '/modules/adapter/wms_sku360.php');
		        $wms = new WmsSKU360($wms_info);
		        break;
	        }

            case 7://通天晓
            {
				require_once(ROOT_DIR . '/modules/adapter/wms_ttx.php');
                $wms = new WmsTTX($wms_info);
                break;
            }

            case 8://中联网仓
            {
            	require_once(ROOT_DIR . '/modules/adapter/wms_zlwc.php');
                $wms = new WmsZLWC($wms_info);
                break;
            }
            case 9://顺丰
            {
                require_once(ROOT_DIR . '/modules/adapter/wms_sf.php');
                $wms = new WmsSF($wms_info);
                break;
            }
            case 10://网仓2
            {
                require_once(ROOT_DIR . '/modules/adapter/wms_wc2.php');
                $wms = new WmsWC2($wms_info);
                break;
            }
            case 11://奇门
            {
            	require_once(ROOT_DIR . '/Wms/wms_qm.php');
                $wms = new WmsQM($wms_info);
				break;
            }
            case 12://旺店通仓储
            {
            	require_once(ROOT_DIR . '/modules/adapter/wms_wdt.php');
                $wms = new WmsWDT($wms_info);
            	break;
            }
            case 13://心怡仓储
            {
	            require_once(ROOT_DIR . '/modules/adapter/wms_xy.php');
	            $wms = new WmsXY($wms_info);
	            break;
            }
            case 14://力威仓储
            {
                require_once(ROOT_DIR . '/modules/adapter/wms_others.php');
                $wms = new WmsOTHERS($wms_info);
	            break;
            }
            case 15://京东沧海
            {
                require_once(ROOT_DIR . '/modules/adapter/wms_jd.php');
                $wms = new WmsJD($wms_info);
                break;
            }
            case -1://TOP全渠道
            {
                require_once(ROOT_DIR . '/modules/adapter/top_qimen.php');
                $wms = new TopQimen($wms_info);
                break;
            }
            case -2://电子发票
            {
            	require_once(ROOT_DIR . '/modules/adapter/invoice_taobao.php');
                $wms = new InvoiceTaobao($wms_info);
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
		$headers = @$params_array['headers']; //通天晓比较恶心,非要把参数拼到headers里...
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
        //奇门不需要对响应信息进行urldecode
        if($this->wmsType != 11)
        {
            $response = urldecode($response);
        }
		return $response;
	}

	public function sendRequest($api_method,$data,$sid = '') //执行请求
	{
		$wms = &$this->wmsClient;
		logx("wms :".print_r($wms,true),$sid.'/WMS');
		if($wms == null)
		{
			return array('code' => -99, 'error_msg' => '不支持的wms类型', 'retry_flag' => 0);
		}
        if(!$wms->formatParams($api_method,$data))
    	{
    		return array('code' => -99, 'error_msg' => 'wms不支持此方法', 'retry_flag' => 0);
    	}
		logx("apiType".$wms->apiType,'/WMS');
		switch($wms->apiType)
        {
            case 0: //使用http post发送请求
            {
				logx("sendParams :".print_r($wms->sendParams,true),$sid.'/WMS');
				logx("apiUrl :".print_r($wms->apiUrl,true),$sid.'/WMS');
				$resp = $this->sendByPost($wms->apiUrl, $wms->sendParams);
				$this->received = $resp;
                $format_result  = $wms->formatResult($api_method,$resp);
				logx("format_result1 :".print_r($format_result,true),$sid.'/WMS');

				break;
            }
            case 1: //使用webservice发送请求(默认对方wbservice有wsdl并且wsdl样式为document/literal/wrapped样式)
            {
		        $datagram = array();
                $resp = OuterUtils::getResultBySoap($wms->wsdl, $wms->sendParams['method'], $wms->sendParams['params'],$datagram);
	            $this->received   = $datagram['response'];
	            $this->sendParams = $datagram['request'];
                $format_result    = $wms->formatResult($api_method, $resp);
				logx("format_result2 :".print_r($format_result,true),$sid.'/WMS');
                break;
            }
            default: return array('code' => -99, 'error_msg' => '类型不正确', 'retry_flag' => 0);
        }

        if ($this->wmsType > 0)
        {
            //向weblog服务器任务队列发送一条插入任务
            $outer_no = '';
            $order_no = '';
			logx("api_method :".print_r($api_method,true),$sid.'/WMS');
            switch ($api_method)
            {
                CASE WMS_METHOD_TRADE_ADD:
                CASE WMS_METHOD_TRADE_CANCEL:
                    $outer_no = $outer_no == '' ? $data['trade']['stockout_no'] : $outer_no;
                    $order_no = $order_no == '' ? $data['trade']['stockout_no'] : $order_no;
                CASE WMS_METHOD_PURCHASE_ADD:
                CASE WMS_METHOD_PURCHASE_CANCEL:
                    $outer_no = $outer_no == '' ? $data['purchase']['outer_no']    : $outer_no;
                    $order_no = $order_no == '' ? $data['purchase']['purchase_no'] : $order_no;
                CASE WMS_METHOD_PURCHASE_RETURN_ADD:
                    $outer_no = $outer_no == '' ? $data['otherOut']['outer_no']  : $outer_no;
                    $order_no = $order_no == '' ? $data['otherOut']['return_no'] : $order_no;
                CASE WMS_METHOD_PURCHASE_RETURN_CANCEL:
                    $outer_no = $outer_no == '' ? $data['return']['outer_no']  : $outer_no;
                    $order_no = $order_no == '' ? $data['return']['return_no'] : $order_no;
                CASE WMS_METHOD_REFUND_ADD:
                CASE WMS_METHOD_REFUND_CANCEL:
                    $outer_no = $outer_no == '' ? $data['refund']['outer_no']  : $outer_no;
                    $order_no = $order_no == '' ? $data['refund']['refund_no'] : $order_no;
                CASE WMS_METHOD_TRANSFERIN_SAME_ADD:
                CASE WMS_METHOD_TRANSFERIN_DIVERSE_ADD:
                    $outer_no = $outer_no == '' ? $data['transfer']['outer_no']    : $outer_no;
                    $order_no = $order_no == '' ? $data['transfer']['transfer_no'] : $order_no;
                CASE WMS_METHOD_TRANSFERIN_CANCEL:
                CASE WMS_METHOD_TRANSFERIN_DIVERSE_CANCEL:
                    $outer_no = $outer_no == '' ? $data['transfer']['outer_no2'] : $outer_no;
                    $order_no = $order_no == '' ? $data['transfer']['transfer_no'] : $order_no;
                CASE WMS_METHOD_TRANSFEROUT_SAME_ADD:
                CASE WMS_METHOD_TRANSFEROUT_DIVERSE_ADD:
                CASE WMS_METHOD_TRANSFEROUT_CANCEL:
                CASE WMS_METHOD_TRANSFEROUT_DIVERSE_CANCEL:
                    $outer_no = $outer_no == '' ? $data['transfer']['outer_no'] : $outer_no;
                    $order_no = $order_no == '' ? $data['transfer']['transfer_no'] : $order_no;
                CASE WMS_METHOD_STOCKIN_ADD:
                CASE WMS_METHOD_STOCKIN_CANCEL:
                    $outer_no = $outer_no == '' ? $data['otherIn']['outer_no'] : $outer_no;
                    $order_no = $order_no == '' ? $data['otherIn']['order_no'] : $order_no;
                CASE WMS_METHOD_STOCKOUT_ADD:
                CASE WMS_METHOD_STOCKOUT_CANCEL:
                {
                    $outer_no = $outer_no == '' ? $data['otherOut']['outer_no'] : $outer_no;
                    $order_no = $order_no == '' ? $data['otherOut']['order_no'] : $order_no;

                    $url            = $wms->apiType == 0 ? $wms->apiUrl : $wms->wsdl;
                    $flag           = $format_result['code'] == 0 ? 'success' : 'failure';
                    $wms_type       = $this->wmsType;
                    $request_body   = $wms->apiType == 0 ? $wms->weblogParams['request_body'] : $this->sendParams;
                    $response_body  = $this->received;
                    $interface_name = $wms->apiType == 0 ? $wms->weblogParams['interface_name'] : $wms->sendParams['method'];

                    /*$weblog_result  = OuterUtils::weblogSend2('wdt2.0', $sid, $wms_type, $api_method, $interface_name, $order_no, $outer_no, $url, $request_body, $response_body, $flag);
                    if (!$weblog_result)
                    {
                       	    logx("Weblog failed!", $sid);
                    }*/
                    break;
                }
                default:
                    break;
            }
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
    public function getWmsLogistics()
    {
        $wms = $this->wmsClient;
        return $wms->getLogisticsCompanies();
    }

    //获取第一次连接失败的原因
    public function getConError()
    {
        return $this->connet_error;
    }

	
	//参数解释：仓库类型，所属仓库,调用方法，错误信息
	public static function getTransferFlag($wms_type, $wms_info_type, $method, &$error_info,$api_url='')
	{

		//支持调拨单仓库类型不同的出库单 WMS_METHOD_TRANSFEROUT_DIVERSE_ADD 其他出库单
		$suport_transferout_diverse = array(
				'KJ'=>1,//科捷
				'FW'=>1,//发网
				'ST'=>1,//申通
				'BS'=>1,//百世
				'YT'=>1,//云擎
				'CN'=>1,//菜鸟
				'YMX'=>1,//亚马逊
				'JY'=>1,//九曳
				'XY'=>1,//心怡
				'EMS'=>1,//EMS
				'TTX'=>1,//通天晓
				'MTTWMS'=>1,//美淘淘WMS
				'WC2'=>1, //网仓2
				'NC'=>1, //能容
				'YD'=>1,//韵达
                'BG'=>1,//标杆
                'JDY'=>1,//筋斗云
                'WDT'=>1,//旺店通
                'HM'=>1,//慧美
				'ZT'=>1,//中通
				'WC'=>1,//微仓
				'FL'=>1,//富勒
				);

		//支持调拨单仓库类型不同的入库单 WMS_METHOD_TRANSFERIN_DIVERSE_ADD 其他入库单
		$suport_transferin_diverse = array(
				'KJ'=>1,//科捷
				'FW'=>1,//发网
				'ST'=>1,//申通
				'BS'=>1,//百世
				'YT'=>1,//云擎
				'CN'=>1,//菜鸟
				'YMX'=>1,//亚马逊
				'JY'=>1,//九曳
				'XY'=>1,//心怡
				'EMS'=>1,//EMS
				'MTTWMS'=>1,//美淘淘WMS
				'TTX'=>1,//通天晓
				'WC2'=>1, //网仓2
				'NC'=>1, //能容
				'YD'=>1,//韵达
                'BG'=>1,//标杆
                'JDY'=>1,//筋斗云
                'WDT'=>1,//旺店通
                'HM'=>1,//慧美
				'FL'=>1,//富勒
				);

		//支持采购退货的仓库WMS_METHOD_PURCHASE_RETURN_ADD
		$purchase_return_suport = array(
				'JW'=>1,//巨沃
				'MTTWMS'=>1,//美淘淘WMS
				'YD'=>1,//韵达
				'FW'=>1,//发网
				'YTWMS'=>1,//圆通
				'GY'=>1,//管易
				'TTX'=>1, //通天晓
				'WC2'=>1, //网仓2
				'NC'=>1, //能容
				'WDT'=>1,//旺店通
				'XY'=>1,//心怡
				'EMS'=>1,//EMS
                'BG'=>1,//标杆
                'WCH'=>1,//物产
                'HM'=>1,//慧美
                'DSWL'=>1,//电商物流
				'FL'=>1,//富勒
				);

		//委外停止等待业务WMS_METHOD_PURCHASE_STOP_WAITING
		$stop_waiting_config = array(
			//	'FW' => 1,//发网
				'JW' => 1,//巨沃
				);
		
		//采购计划开单
		$purchase_plan_order = array(
			//	'FW' => 1,//发网
				'TTX'=> 1,//通天晓
				);
		
		//支持jit出库的仓库WMS_METHOD_JIT_PICK_ADD
		$jit_pick_suport = array(
				'JW'=>1,//巨沃   
				'BS'=>1,//百世
			//	'FW'=>1,//发网
				'TTX'=>1,//通天晓
				);

		//奇门批量上传货品配置
		$suport_addsku_batch = array(
			//	'FW'=>1,
				);

		switch($wms_type)//仓库类型
			//if($wms_type == 11)//奇门
		{
			case 11://奇门
				{

					switch($method)
					{
						case WMS_METHOD_TRANSFEROUT_DIVERSE_ADD://调拨单仓库类型不同的出库单
							{
								if(!array_key_exists($wms_info_type,$suport_transferout_diverse)) 
								{
									$error_info = '该仓库不支持其他出库单接口';
									return 0;
								}
								return 1;
							}

						case WMS_METHOD_TRANSFERIN_DIVERSE_ADD://调拨单仓库类型不同的入库单
							{
								if(!array_key_exists($wms_info_type,$suport_transferin_diverse)) 
								{
									$error_info = '该仓库不支持调拨入库单接口';
									return 0;
								}
								return 1;
							}

						case WMS_METHOD_PURCHASE_RETURN_ADD://采购退货单
							{
								if(!array_key_exists($wms_info_type,$purchase_return_suport)) 
								{
									$error_info = '该仓库不支持采购退货单接口';
									return 0;
								}
								return 1;
							}
						case WMS_METHOD_JIT_PICK_ADD://JIT出库
						{
							if(!array_key_exists($wms_info_type,$jit_pick_suport)) 
								{
									$error_info = '该仓库不支持JIT出库接口';
									return 0;
								}
								return 1;
						}
						case WMS_METHOD_PURCHASE_STOP_WAITING://采购单停止等待
						{
							if(!array_key_exists($wms_info_type,$stop_waiting_config)) 
								{
									$error_info = '委外仓不支持委外停止等待，转内部逻辑处理';
									return 0;
								}
								return 1;							
						}
						case WMS_METHOD_SKUS_ADD://奇门批次管理
							{
								if(!array_key_exists($wms_info_type,$suport_addsku_batch))
								{
									$error_info = '该仓库不支持批量上传货品接口';
									return 0;
								}
								return 1;
							}
						case WMS_METHOD_PURCHASE_PLAN_ORDER://奇门采购计划单
							{
								if(!array_key_exists($wms_info_type,$purchase_plan_order))
								{
									$error_info = '该仓库暂不支持采购计划开单接口';
									return 0;
								}
								return 1;
							}
						default:
							return 1;
					}   

				}
				break;
			case 9:				
				if($method == WMS_METHOD_SKUS_ADD)
				{
					if ($api_url == 'http://scs-drp-core-out.sf-express.com/webservice/OutsideToLscmService?wsdl') 
					{
				return 1;
				}							
					else
					{
						return 0;
					}
				}							
				else
				{
				    return 1;
				}
				break;
			default:
				if($method == WMS_METHOD_SKUS_ADD)
				{
					return 0;
				}
				else
				{
				return 1;	
				}
				

		}

	}
}

