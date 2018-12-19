<?php
use Common\Common\UtilTool;
include_once (APP_PATH . 'Platform/Common/api_error.php');
require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jos/JdException.php');
require_once(TOP_SDK_DIR . '/jos/JosRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/logistics/EtmsWaybillSendRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/logistics/EtmsWaybillcodeGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderGetRequest.php');

function josGetWaybill(&$db,$stockout_ids,$logistics_info,&$result)
{
	$result = array(
	    'status' => 0,
	    'msg'  =>"success",
	    'data' =>array()
	);
	\Think\Log::write('josGetWaybill--'.$logistics_info->logistics_id.'--begin',\Think\Log::DEBUG);
	try {

		$model = D('Stock/StockLogisticsNo');
		//查询的platform_id 是从cfg_shop 表中查询的,如果出故障需要审查一下
		$stockout_order_info = D('Stock/StockOutOrder')->getStockoutInfoBeforeApplyWaybill($stockout_ids,true);

		if(empty($stockout_order_info))
		{
			\Think\Log::write("josGetWaybill---数据库没有信息!!");
			SE('没有查询到出库单相关信息');
		}

		$fail_list             = array();   //记录错误信息的列表

        //-------记录获取数量
        $need_waybill = count($stockout_order_info);
        //SE('need_waybill================'.$need_waybill);
		//-------记录获取的电子面单的信息
		$waybill_list = array();
		//-------记录分配成功的电子面单的信息
		$success_list = array();

		$available_list_fields = array(
		      "shop_id",
		      "logistics_no",
		      "logistics_id",
		      "logistics_type",
		);
		$available_list_cond = array(
		    "logistics_type" =>$logistics_info->logistics_type,
		    "shop_id" => $logistics_info->shop_id,
		    "status" =>array('in','0,5'),
		);
		//查询可利用的jos单数
		$waybill_list = $model->getJosLogisticsNo($available_list_fields,$available_list_cond,$need_waybill);
		//-------记录可利用的电子面单的数量
		$available_count = count($waybill_list);
		//-------记录缺少的电子面单的数量
		$lack_waybill = $need_waybill-$available_count;
		//-------记录申请的电子面单的数组
		$apply_waybill_list = array();
		if($lack_waybill >  0)
		{
    		$jos = new JdClient();
    		$jos->appKey          = $logistics_info->key;
    		$jos->appSecret       = $logistics_info->secret;
    		$jos->accessToken     = $logistics_info->session;
            \Think\Log::write("-josGetWaybill- jos: ".print_r($jos,true),\Think\Log::INFO);
            $req = new EtmsWaybillcodeGetRequest();

    		$max = 100;
    		$i = 0;

    			if ($lack_waybill > $max)
    			{
    				for ($i = 0; $i < floor($lack_waybill / $max); ++$i)
    				{
    					$req->setPreNum($max);
    					$req->setCustomerCode($logistics_info->customer_code);
    					\Think\Log::write("-josGetWaybill- req: ".print_r($req,true),\Think\Log::INFO);
    					$retval = $jos->execute($req);
    			        \Think\Log::write("-josGetWaybill- logistics_id: {$logistics_info->logistics_id}-api-result:".print_r($retval,true),\Think\Log::INFO);
    					if(API_RESULT_OK != josErrorTest($retval, $db, $logistics_info->shop_id))
    					{
    						\Think\Log::write("-josGetWaybill- logistics_id: {$logistics_info->logistics_id}- failed: {$retval->resultInfo->message}");
    						$waybill_fail_info = $retval->resultInfo->message;
    						continue;
    					}
    					foreach ($retval->resultInfo->deliveryIdList as $delivery_id)
    					{
    						//记录请求多少个失败多少个
    						$apply_waybill_list[] = array(
    						        "logistics_id"       => $logistics_info->logistics_id,
    								"logistics_type"     => $logistics_info->logistics_type,
    								"logistics_no"       => $delivery_id,
    								"shop_id"            => $logistics_info->shop_id
    						);
    					}
    				}
    			}

    			$rest = $lack_waybill - $i * $max;

    			if ($rest > 0)
    			{
    				$req->setPreNum($rest);
    				$req->setCustomerCode($logistics_info->customer_code);
                    \Think\Log::write("-josGetWaybill- req: ".print_r($req,true),\Think\Log::INFO);
                    $retval = $jos->execute($req);
    				\Think\Log::write("-josGetWaybill- logistics_id: {$logistics_info->logistics_id}-api-result:".print_r($retval,true),\Think\Log::INFO);

    				if(API_RESULT_OK != josErrorTest($retval, $db, $logistics_info->shop_id))
    				{
    					\Think\Log::write("josGetWaybill failed: {$retval->resultInfo->message}");
    					$waybill_fail_info = $retval->resultInfo->message;
    				}
                    if($retval->resultInfo->code == 119){
                        \Think\Log::write("josGetWaybill failed: {$retval->resultInfo->message}");
                        $waybill_fail_info = $retval->resultInfo->message;
                    }
    				foreach ($retval->resultInfo->deliveryIdList as $delivery_id)
    				{
    					//记录请求多少个失败多少个
    					$apply_waybill_list[] = array(
    					        "logistics_id"    => $logistics_info->logistics_id,
    							"logistics_type"  =>  $logistics_info->logistics_type,
    							"logistics_no"    => $delivery_id,
    							"shop_id"         =>  $logistics_info->shop_id
    					);

    				}
    			}

		}
		//addAll好像只支持  mysql
		//更新获取的电子面单信息
		$res_insert_apply = D("Stock/StockLogisticsNo")->addStockLogisticsNo($apply_waybill_list);
		//合并原来获取的 与  刚请求的电子面单信息
		foreach($apply_waybill_list as $apply_key => $apply_value){
		    $waybill_list[] = $apply_value;
		}

		//考虑到获取失败的情况，所以再次判断获取的最终面单数量
		$get_count = count($waybill_list);
		if($get_count>=0)
		{
		    $list_count = $get_count>$need_waybill?$need_waybill:$get_count;
			for ($key=0;$key<$list_count;$key++)
			{

			    $stockout_item = $stockout_order_info["{$key}"];
			    $print_data = array(
			        'delivery_term' =>$stockout_item['delivery_term'],
			    );
			    // 整理打印信息,把相关的打印数据整理一下,并获取一下应收款数据
			    if (2 == $stockout_item['delivery_term'])
			    {
			            //这里看能不能使用cod_amount优化?
			        $data = array();
                    $temp_sid = get_sid();
                    if($temp_sid == "lbdq" ){
                        $order_app_key  = array('app_key'=>$stockout_item['app_key']);
                        \Platform\Manager\Manager::parse_app_key($order_app_key);
                        $result_data = josOrderPrintData($db,$logistics_info->sid,$stockout_item["shop_id"],$logistics_info->key,$logistics_info->secret,$order_app_key->session,$stockout_item['src_tids'],$data);
                    }else{
                        $result_data = josOrderPrintData($db,$logistics_info->sid,$logistics_info->shop_id,$logistics_info->key,$logistics_info->secret,$logistics_info->session,$stockout_item['src_tids'],$data);
                    }
			        if($result_data!==true)
			        {
			            $fail_list[] =array(
			                'stock_id'  => $stockout_item['stockout_id'],
			                'stock_no'  => $stockout_item['stockout_no'],
			                'msg'       => '获取应收款信息失败:'.$result_data,
			            );
			            \Think\Log::write("get should_pay fail: {$stockout_item['src_tids']} {$logistics_info->shop_id}!!". $logistics_info->sid);
			            continue;
			        }
			        $print_data['receivable'] = number_format(round($data['should_pay'],2),2);
			        \Think\Log::write("jd_cod: " . print_r($data, true).$logistics_info->sid,\Think\Log::INFO);
			        \Think\Log::write("cod_amount: {$stockout_item['cod_amount']}". $logistics_info->sid,\Think\Log::INFO);
			    }

			    $print_data['package_sequence']      = "1/1";
				$print_data['package_no_code']       = $waybill_list[$key]['logistics_no']."-1-1-";//单包裹
				$print_data['logistics_no_code']     = $waybill_list[$key]['logistics_no'];
				$print_data['receiver_phone']        = !empty($stockout_item['receiver_mobile'])?$stockout_item['receiver_mobile']:$stockout_item['receiver_telno'];
				$print_data['phone']                 = !empty($stockout_item['sender_mobile'])?$stockout_item['sender_mobile']:$stockout_item['sender_telno'];
// 				$print_data['print_date'] = "yyyy-MM-dd,date";//Now-当前日期及其时间、Date当前日期、Time当前时间  ','号之前为格式化类型
				$success_list["{$stockout_item['stockout_id']}"] =array(
						'shop_id'         =>$stockout_item["shop_id"],
						'src_tids'        =>$stockout_item['src_tids'],
						'logistics_no'    =>$waybill_list[$key]['logistics_no'],
				        'stockout_no'     =>$stockout_item["stockout_no"],
						'receiver_dtb'    =>$stockout_item['receiver_dtb'],
						'package_code'    =>'',
						'package_name'    =>'',
						'send_province'   =>$stockout_item['sender_province'],
						'send_city'       =>$stockout_item['sender_city'],
						'send_district'   =>$stockout_item['sender_district'],
						'send_address'    =>$stockout_item['sender_address'],
				        'waybill_info'    =>json_encode($print_data),
				);
			}
			for($j = count($success_list)+count($fail_list);$j<$need_waybill; $j++)
			{
				$stockout_item = $stockout_order_info["{$j}"];
				$fail_list[] = array(
				    'stock_id' =>$stockout_item['stockout_id'],
				    'stock_no' =>$stockout_item['stockout_no'],
				    'msg'      =>$waybill_fail_info,
				);
			}
		}else {
		    SE('未获取到单号');
		}
	}catch(\Think\Exception\BusinessLogicException $e){
		$msg = $e->getMessage();
		$result['msg'] = $msg;
		$result['status'] =1;
		return false;
	}catch(\PDOException $e){
		$msg = $e->getMessage();
		$result['msg'] = $msg;
		$result['status'] =1;
		\Think\Log::write("josGetWaybillpdo exceptions:".$msg);
		return false;
	}catch(\Exception $e){
		$msg = $e->getMessage();
		$result['msg'] = $msg;
		$result['status'] =1;
		\Think\Log::write("josGetWaybill---".$msg);
		return false;
	}
	$result['fail']= $fail_list;
	$result['success'] = $success_list;
	if(!empty($fail_list))
	{
	    $result['stauts'] = 2;
		waybill_error_handle($logistics_info,$result);
	}
	if(!empty($success_list))
	{
        $packageNos = array();
        waybill_success_handle($logistics_info, $packageNos, $result);
	}
	$result['data'] = array(
	    'fail' => $result['fail'],
	    'success' => $result['success']
	);

	unset($result['fail']);
	unset($result['success']);
 	return true;


}
function josSendWaybill(&$db,$stockout_ids,$logistics_info,&$result_info)
{
	$result_info = array(
	    'status' =>0,
	    'msg' =>'success',
	    'data'=>array()
	);
	\Think\Log::write('josSendWaybill--'.$logistics_info->logistics_id.'--begin',\Think\Log::DEBUG);

	$order_fail    = array();
	$order_success = array();
	$salePlatId = '';
	try {
		$stockout_db = D('Stock/StockOutOrder');
		$stockout_order_info = $stockout_db->getStockoutInfoBeforeApplyWaybill($stockout_ids,true);
		foreach ($stockout_order_info as $stockout_order)
		{

            if(empty($stockout_order['platform_id'])){

                $order_fail[] =array(
                    'stock_id'=>$stockout_order['stockout_id'],
                    'stock_no' =>$stockout_order['stockout_no'],
                    'msg' =>  '平台类型不正确',
                );
                continue;
            }

            switch ($stockout_order['platform_id']){

                case 1:{

                    if($stockout_order['sub_platform_id'] ==1){
                        $salePlatId = "0010002";  //天猫
                    }else{
                        $salePlatId = "0030001";  //其他
                    }
                    break;
                }
                case 3:
                    $salePlatId = "0010001";   //京东
                    break;
                case 13:
                    $salePlatId = "0010003";   //苏宁
                    break;
                case 5:
                    $salePlatId = "0010004";   //亚马逊
                    break;
                default:
                    $salePlatId = "0030001";  //其他
                    break;
            }

			if(empty($stockout_order['sender_name']))
			{
			    $order_fail[] =array(
			         'stock_id'=>$stockout_order['stockout_id'],
			         'stock_no' =>$stockout_order['stockout_no'],
			         'msg' =>  '仓库缺少联系人信息',
			    );
				continue;

			}

			if(empty($stockout_order['sender_address']))
			{
			    $order_fail[] =array(
			        'stock_id'=>$stockout_order['stockout_id'],
			        'stock_no' =>$stockout_order['stockout_no'],
			        'msg' =>  '仓库缺少联系地址信息',
			    );
				continue;
			}
			if(empty($stockout_order['sender_telno'])&&empty($stockout_order['sender_mobile']))
			{
			    $order_fail[] =array(
			        'stock_id'=>$stockout_order['stockout_id'],
			        'stock_no' =>$stockout_order['stockout_no'],
			        'msg' =>  '寄件人电话、手机至少有一个不为空',
			    );
				continue;
			}
			if(empty($stockout_order['receiver_telno'])&&empty($stockout_order['receiver_mobile']))
			{
			    $order_fail[] =array(
			        'stock_id'=>$stockout_order['stockout_id'],
			        'stock_no' =>$stockout_order['stockout_no'],
			        'msg' =>  '收件人电话、手机至少有一个不为空',
			    );
				continue;
			}

            splitArea($stockout_order['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
            $jos = new JdClient();
			$jos->appKey = $logistics_info->key;
			$jos->appSecret = $logistics_info->secret;
			$jos->accessToken = $logistics_info->session;
			$req = new EtmsWaybillSendRequest();

			$req->setDeliveryId($stockout_order['logistics_no']);
			$req->setSalePlat($salePlatId);
			$req->setCustomerCode($logistics_info->customer_code);
			$req->setOrderId($stockout_order['src_order_no']);
			$req->setThrOrderId($stockout_order['src_tids']);
			$req->setSenderName ($stockout_order['sender_name']);
			$req->setSenderAddress($stockout_order['sender_address']);

			$req->setSenderTel($stockout_order['sender_telno']);
			$req->setSenderMobile($stockout_order['sender_mobile']);

			$req->setReceiveName($stockout_order['receiver_name']);
			$req->setReceiveAddress($stockout_order['receiver_address']);
			$req->setProvince($receiver_province);
			$req->setCity($receiver_city);
			$req->setCounty($receiver_district);

			$req->setReceiveTel($stockout_order['receiver_telno']);
			$req->setReceiveMobile($stockout_order['receiver_mobile']);

			$packageCount = $stockout_order['package_count'] == 0?1:$stockout_order['package_count'];
			$req->setPackageCount(intval($packageCount));
			$req->setWeight(round((float)$stockout_order['calc_weight'],2));
			$req->setVloumn(0);
			if (2 == $stockout_order['delivery_term'])
			{
				//这里看能不能使用cod_amount优化?
                $temp_sid = get_sid();
                if($temp_sid == "lbdq" ){
                    $order_app_key  = array('app_key'=>$stockout_order['app_key']);
                    \Platform\Manager\Manager::parse_app_key($order_app_key);
                    $result_data = josOrderPrintData($model,$logistics_info->sid,$stockout_order["shop_id"],$logistics_info->key,$logistics_info->secret,$order_app_key->session,$stockout_order['src_tids'],$data);
                }else{
                    $result_data = josOrderPrintData($model,$logistics_info->sid,$logistics_info->shop_id,$logistics_info->key,$logistics_info->secret,$logistics_info->session,$stockout_order['src_tids'],$data);
                }
                if($result_data!==true)
				{

				    $order_fail[] =array(
				        'stock_id'=>$stockout_order['stockout_id'],
				        'stock_no' =>$stockout_order['stockout_no'],
				        'msg' =>  $result_data,
				    );
					\Think\Log::write("get should_pay fail: {$stockout_order['src_tids']} {$logistics_info->shop_id}!!". $logistics_info->sid);
					continue;
				}

				\Think\Log::write("jd_cod: " . print_r($data, true).$logistics_info->sid,\Think\Log::INFO);
				\Think\Log::write("cod_amount: {$stockout_order['cod_amount']}". $logistics_info->sid,\Think\Log::INFO);

				$req->setCollectionValue(1);
				$req->setCollectionMoney($data['should_pay']);
			}
			else
			{
				$req->setCollectionValue(0);
			}
            \Think\Log::write("jos: ".print_r($jos,true),\Think\Log::INFO);
            \Think\Log::write("req: ".print_r($req,true),\Think\Log::INFO);
            $retval = $jos->execute($req);
            \Think\Log::write("retval: ".print_r($retval,true),\Think\Log::INFO);

            if(API_RESULT_OK != josLogisticsErrorTest($retval, $db, $logistics_info->shop_id))
			{
			    $order_fail[] =array(
			        'stock_id'=>$stockout_order['stockout_id'],
			        'stock_no' =>$stockout_order['stockout_no'],
			        'msg' =>  $retval->error_msg,
			    );

				\Think\Log::write("josSendWaybill failed: {$stockout_order['src_tids']} {$retval->error_msg} ");
				\Think\Log::write("josSendWaybill {$stockout_order['src_tids']} {$retval->error_msg}");
			}else {
				$order_success["{$stockout_order['stockout_id']}"] = true;
			}
			// 		logx("jos_send_waybill ok: $tid", $sid);
			\Think\Log::write("josSendWaybill ok: {$stockout_order['src_tids']}",\Think\Log::INFO);
		}
		//当前结果值时保存了成功结果和失败的结果的数据并没有修改相关的同步表信息，这里只做打印时的验证，在后期需要添加整理

		if (!empty($order_fail))
		{
		    $result_info['status'] = 2;
		}
		$result_info['data'] = array(
		    'fail' => $order_fail,
		    'success' => $order_success
		);
		return true;
	}catch(\PDOException $e){
		$msg = $e->getMessage();
		$result_info['msg'] = $msg;
		$result_info['status'] =1;
		\Think\Log::write("josSendWaybill".$msg, \Think\Log::ERR);
		return false;
	}catch(\Exception $e){
		$msg = $e->getMessage();
		$result_info['msg'] = $msg;
		$result_info['status'] =1;
		\Think\Log::write("josSendWaybill".$msg, \Think\Log::ERR);
		return false;
	}
}
function josOrderPrintData(&$db,$sid,$shop_id,$appkey,$appsecret,$sessionkey,$order_id,&$data=array())
{
	$jos = new JdClient();
    $jos->appKey        =  $appkey;
	$jos->appSecret     =  $appsecret;
	$jos->accessToken   =  $sessionkey;

	$req = new OrderGetRequest();
	$req->setOrderId($order_id);

	$retval = $jos->execute($req);
	if(API_RESULT_OK != josLogisticsErrorTest($retval,$db,$shop_id))
	{
		\Think\Log::write("get_should_pay failed: {$retval->error_msg} ". $sid);
		\Think\Log::write("ERROR $sid get_should_pay {$retval->error_msg}", $sid);

		return $retval->error_msg;
	}
	$data['should_pay'] = $retval->order->orderInfo->order_payment;
	return true;
}