<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/smt/SmtClient.php');

$GLOBAL['g_goods_specid'] = array();
function time_date($time)
{
	$time = substr($time,0,14);
	$time = strtotime($time);
	$time = date("Y-m-d H:i:s" ,$time);
	return $time;
}

function loadAliTrade(&$db, $sid, $appkey, $appsecret, $session, $shopid, &$trade_list, &$order_list, &$t, &$error_msg,&$discount_list)
{
	global $g_goods_specid;
	$trade = $t;
	$order_count = 0;
	$goods_count = 0;
	$post_amount = 0;
	$pay_status = 0;
	$refund_amount = 0;
	$cod_amount = 0;
	$delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0; // 0未付款1部分付款2已付款
	$trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
	$process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	//trade退款状态
	$trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
	$paid = 0;
	$payTime = @$trade->gmtPaySuccess;
	if(empty($payTime))
	{
		$payTime = '0000-00-00 00:00:00';
	}else
	{
		$payTime = time_date($payTime);
	}	
	$tid = (string)$trade->id;
	$status = $trade->orderStatus;//订单状态
	if ($status == 'PLACE_ORDER_SUCCESS' || $status == 'RISK_CONTROL')//等待买家付款
	{
		$process_status = 10;
	}
	elseif ($status == 'WAIT_SELLER_SEND_GOODS')//等待发货
	{
		$trade_status = 30;
		$process_status = 10;
		$pay_status=2;
	}
	elseif ($status == 'SELLER_PART_SEND_GOODS')//部分发货
	{
		$pay_status=2;
		$trade_status = 40;
	}
	elseif ($status == 'WAIT_BUYER_ACCEPT_GOODS')//等待买家收货
	{
		$pay_status=2;
		$trade_status = 50;
	}
	elseif ($status == 'IN_CANCEL' || ($status == 'FINISH' && $trade->fundStatus == 'NOT_PAY'))//买家申请取消 || 订单自动关闭
	{
		$trade_status = 90;
	}
	else
	{
		logx ( "invalid_trade_status tid : $tid  status : {$status}", $sid.'/TradeSlow' );
		logx ( "ERROR $sid invalid_trade_status $tid {$status}", $sid.'/TradeSlow', 'error' );
	}

	//退款状态
	$refund_states = $trade->issueStatus;
	if ($refund_states == 'IN_ISSUE')
	{
		$trade_refund_states = 1;
	}
	//子订单
	$orders = $trade->childOrderList;
	$goods_fee = 0;//货款总额
	for($k=0; $k<count($orders); $k++)
	{
		$o = & $orders[$k];
		$goods_fee += ((float)$o->initOrderAmt->amount);
	}
	$post_fee = ((float)$trade->logisticsAmount->amount);//运费
	$trade_fee = ((float)$trade->orderAmount->amount);//订单金额
	// $goods_fee = ((float)$trade->initOderAmount->amount);//货款总额
	$left_post = $post_fee;
	$discount_t = (bcsub($goods_fee, (bcsub($trade_fee,$post_fee))));//订单的优惠 由于接口没有返回该字段 通过计算 货品总额-(订单金额-运费)
	$left_discount = $discount_t;
	//商品信息
	$goods = $trade->childOrderExtInfoList;
	
	for($k=0; $k<count($orders); $k++)
	{
		$o = & $orders[$k];
		++$order_count;
		$goodsid = $o->productId;
		$num = (int)$o->productCount;
		$goods_count += (int)$o->productCount;
		$price = ((int)$o->productPrice->cent);
		$total_fee = bcmul($price , $num);


		//获取商品skuid
		$goodsNO = @$o->skuCode;
		$goods_sku = $goodsid.$goodsNO;
		if(isset($g_goods_specid[$goods_sku]))
		{
			$spec_id = $g_goods_specid[$goods_sku];
		}
		else
		{
			$retval = Smt::getGoodsDetail($appkey, $appsecret, $session, $goodsid);
			$goods_info = $retval->aeopAeProductSKUs;
			for ($s = 0; $s < count($goods_info); $s++)
			{ 
				$goods = $goods_info[$s];
				$skuCode = @$goods->skuCode;
				if ($skuCode == $goodsNO)
				{
					$spec_id = $goods->id;
					$g_goods_specid[$goods_sku] = $spec_id;
					break;
				}
			}
		}

		$specName = @$o->productStandard;
		if(iconv_strlen($goodsNO,'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goodsNO}\t{$specCode}\t".@$o->productName, $sid.'/TradeSlow', 'error');
			
			$message = '';
			if(iconv_strlen($goodsNO, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goodsNO}";
			// if(iconv_strlen($specCode, 'UTF-8')>40)
			// 	$message = "{$message}规格商家编码超过40字符:{$specCode}";
			
			//发即时消息
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$goodsNO = iconv_substr($goodsNO, 0, 40, 'UTF-8');
			// $specCode = iconv_substr($specCode, 0, 40, 'UTF-8');
		}
		
		if ($k == count($orders) - 1)
		{
			$share_post = $left_post;
			$share_discount = $left_discount;
		}else
		{
			$share_post = bcdiv(bcmul($post_fee, ($total_fee/100)), $goods_fee);
			$left_post = bcsub($left_post, $share_post);
			
			$share_discount = bcdiv(bcmul($discount_t, ($total_fee/100)), $goods_fee);
			$left_discount = bcsub($left_discount, $share_discount);
		}
		
		$share_amount = bcsub(($total_fee/100), $share_discount);
		if($pay_status == 2 && $delivery_term == 1 )
		{
			$order_paid = bcadd($share_amount, $share_post);
		}
		else
		{
			$order_paid = 0;
		}

		//order退款状态
		$order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
		$o_refund_status = $o->issueStatus;
		if ($o_refund_status == 'IN_ISSUE')
		{
			$order_refund_status = 2;
		}elseif ($o_refund_status == 'END_ISSUE')
		{
			$order_refund_status = 5;
		}

		$order_list[] = array
		(
			"rec_id"=>0,
			"platform_id"=>37,
			'shop_id'=>$shopid,
			"tid"=>$tid,				//交易编号
			"oid"=>'ALI' . $o->id,			//订单编号
			"status"=>$trade_status,		//状态
			"refund_status"=>$order_refund_status,
			"goods_id"=>$goodsid,		//平台货品id   
			"spec_id"=>iconv_substr(@$spec_id,0,40,'UTF-8'),		//规格id
			"goods_no"=>$goodsNO,		//商家编码 
			"spec_no"=>'',		//规格商家编码  
			"goods_name"=>iconv_substr($o->productName,0,255,'UTF-8'),			//货品名   
			"spec_name"=>iconv_substr($specName,0,100,'UTF-8'),					//规格名
			'num'=>$num, 							//数量
			'price'=>((float)$price/100), 			//商品单价
			'discount'=>0,		//优惠金额
			'total_amount'=>((float)$total_fee/100),
			'share_post'=>$share_post,				//分摊邮费
			'share_discount'=>$share_discount,		//分摊优惠
			'paid' => $order_paid,
			'share_amount'=> $share_amount,
			'created' => array('NOW()')
			
		);
		
	}
	
	
	$tradeTime = time_date($trade->gmtCreate);
	
	$receiptAddress = $trade->receiptAddress;//收货信息
	$province = $receiptAddress->province;
	$city = $receiptAddress->city;
	$country = $receiptAddress->country;

	$receiver_address = iconv_substr($receiptAddress->detailAddress,0, 256, 'UTF-8');
	// getAddressID($province, $city, $town,$province_id, $city_id, $district_id);


	$orderMsgList = $trade->orderMsgList;//备注
	for ($i=0; $i <count($orderMsgList) ; $i++)
	{
		$Msg = $orderMsgList[$i];
		if ($Msg->poster == 'buyer')
		{
			$buyer_message = $Msg->content;
		} else {
			$remark = $Msg->content;
		}
	}

	
	$trade_list[] = array
	(
		'platform_id' => 37,
		'shop_id' => $shopid,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_states,
		'process_status' => $process_status,
		
		'delivery_term' =>$delivery_term,
		'trade_time' =>$tradeTime,
		'pay_time' => $payTime,
		
		'buyer_nick' => iconv_substr(trim($trade->buyerloginid),0,100,'UTF-8'),
		'buyer_email' =>iconv_substr(@$trade->buyerInfo->email,0,60,'UTF-8'),
		'buyer_area' => '',
		// 'pay_id' => @$trade->alipayTradeId,
		// 'pay_account' => @$trade->buyerAlipayId,
		
		'receiver_name' =>  iconv_substr($receiptAddress->contactPerson,0,40,'UTF-8'),
		/*'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,*/
		'receiver_address' =>iconv_substr($receiver_address,0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr(@$receiptAddress->mobileNo,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr(@$receiptAddress->phoneNumber,0,40,'UTF-8'),
		'receiver_zip' => iconv_substr(@$receiptAddress->zip,0,20,'UTF-8'),
		'receiver_area' => iconv_substr(@$country .", ".@$province .", ".@$city,0,64,'UTF-8'),
		'to_deliver_time' => '',
		
		'receiver_hash' => md5(@$receiptAddress->contactPerson. @$receiver_address.@$receiptAddress->mobileNo.@$receiptAddress->phoneNumber.@$receiptAddress->zip),
		'logistics_type' => -1,

		'buyer_message' => iconv_substr(@$buyer_message,0,1024,'UTF-8'),
		'remark' =>iconv_substr(@$remark,0,1024,'UTF-8'),
		'remark_flag' => 0,
		
		//'end_time' => @$trade->gmtCompleted,
		
		'goods_amount' =>$goods_fee,
		'post_amount' =>$post_amount,
		'receivable' =>$trade_fee,
		'discount' =>$discount_t ,
		'paid' => $trade_fee,
		'received' =>0,
		
		//'platform_cost' => (bcadd(@$trade->commission_fee, @$trade->seller_cod_fee)),
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' =>2 == $delivery_term ? $trade_fee : 0,
		'dap_amount' =>1 == $delivery_term ? $trade_fee : 0, 
		
		'refund_amount' =>$refund_amount,
		'currency'=>$trade->orderAmount->currency->symbol,
		//'trade_mask' => '',
		//'score' =>'',
		//'real_score' =>'',
		//'got_score' =>'',
		'logistics_no' => @$trade->logisticInfoList->logisticsNo,
		'created' => array('NOW()')
	);
	
	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list , $match_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	
	return true;
}

function downSmtTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;

	if(!$db)
	{
		$error_msg = '连接数据库失败';
		logx("downSmtTradesDetail getUserDb failed!!", $sid.'/TradeSlow');
		logx("ERROR $sid downSmtTradesDetail $error_msg",$sid.'/TradeSlow', 'error');
		return TASK_SUSPEND;
	}
	$shopid = $trades->shop_id;
	$tids =$trades->tids;
	//异步的过程中可能丢失session  重新获取session
	$app_key = $db->query_result("select app_key from cfg_shop where shop_id = {$shopid}");
	$secret = json_decode($app_key['app_key'],true);
	$session = $secret['session'];

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	for($i=0; $i<count($tids); $i++)
	{
		
		$tid = $tids[$i];

		$retval = Smt::getTradeDetail($appkey, $appsecret, $session, $tid);
		// logx("downSmtTradesDetail ".print_r($retval,true) ,$sid);
		if(API_RESULT_OK != smtbabaErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg = $retval->error_msg;
			logx("downSmtTradesDetail fail {$tid}, error message: {$error_msg} ", $sid.'/TradeSlow');
			logx("ERROR $sid downSmtTradesDetail fail {$tid}, error message: {$error_msg} ", $sid.'/TradeSlow' , 'error');
			if (401 == intval(@$retval->error_code))
			{
				refreshSmtToken($appkey, $appsecret, $trades);
				return TASK_OK; //会丢失
			}
			
			return TASK_SUSPEND;
		}
		
		if(!loadAliTrade($db, $sid, $appkey, $appsecret, $session, $shopid, $trade_list, $order_list, $retval, $error_msg,$discount_list))
		{
			releaseDb($db);
			return TASK_SUSPEND;
		}
		
	}
	
	//保存剩下的到数据库
	if(count($trade_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list,$discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			releaseDb($db);
			return TASK_SUSPEND;
		}
	}
	
	releaseDb($db);
	
	return TASK_OK;
}


function smtDownloadTradeList(&$db,$appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count,&$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$ptime = $end_time;
	if($save_time) 
	{
		$save_time = $end_time;
	}
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	
	logx("smtDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
	
	$total_count = 0;
	while($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time -3600*24 +1):$start_time;

		$retval = Smt::getTradeList(
							$appkey, 
							$appsecret,
							$shop->session,
							date('m/d/Y H:i:s', $ptime), 
							date('m/d/Y H:i:s', $end_time),
							1,
							40
							);
		/*logx("smtDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $ptime) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid);
		logx("smtDownloadTradeList ".print_r($retval,true) ,$sid);*/
		if(API_RESULT_OK != smtbabaErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg = $retval->error_msg;
			if (401 == intval($retval->error_code))
			{
				
				refreshSmtToken($appkey, $appsecret, $shop);
			}
			logx("smtDownloadTradeList smt->execute fail, error message: {$error_msg} ", $sid.'/TradeSlow');
			logx("ERROR $sid smtDownloadTradeList, error message: {$error_msg} ",$sid.'/TradeSlow', 'error');
			return TASK_OK;
		}
		if(isset($retval->totalItem) && $retval->totalItem == 0)
		{
			$end_time = $ptime + 1;
			logx("smtDownloadTradeList $shopid count: 0", $sid.'/TradeSlow');
			continue;
		}
		
		//总条数
		$total_results = intval($retval->totalItem);
		logx("smtTrade $shopid count: $total_results", $sid.'/TradeSlow');
		
		$trades = $retval->orderList;
		//如果不足一页，则不需要再抓了
		if($total_results <= count($trades))
		{
			$tids = array();
			for($j =0; $j < count($trades); $j++)
			{
				$total_count += 1;
				$tids[] = $trades[$j]->orderId;
			}
			if(count($tids) > 0)
			{
				$shop->tids = $tids;
				if(empty($trade_detail_cmd))
				{
					if(! downSmtTradesDetail($db, $appkey, $appsecret, $shop, $new_trade_count, $chg_trade_count, $error_msg))
					{
						return TASK_OK;
					}
				}
				else
				{
					pushTask($trade_detail_cmd, $shop);
				}
			}
			if($countLimit && $total_count >= $countLimit)
				return TASK_SUSPEND;
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/40);
			
			for($i=$total_pages; $i>=1; $i--)
			{

				$retval = Smt::getTradeList(
							$appkey, 
							$appsecret,
							$shop->session,
							date('m/d/Y H:i:s', $ptime), 
							date('m/d/Y H:i:s', $end_time),
							$i,
							40
							);
				if(API_RESULT_OK != smtbabaErrorTest($retval,$db,$shopid))
				{
					$error_msg = $retval->error_msg;
					logx("smtDownloadTradeList smt->execute fail, error message: {$error_msg} ", $sid.'/TradeSlow');
					logx("ERROR $sid smtDownloadTradeList, error message: {$error_msg} ", $sid.'/TradeSlow', 'error');
					return TASK_OK;
				}
				$tids = array ();
				$trades = $retval->orderList;
				
				for($j =0; $j < count($trades); $j++)
				{
					$total_count += 1;
					$tids[] = $trades[$j]->orderId;
				}
				
				if(count($tids) > 0)
				{
					$shop->tids = $tids;
					if(empty($trade_detail_cmd))
					{
						if(! downSmtTradesDetail($db, $appkey, $appsecret, $shop, $new_trade_count, $chg_trade_count, $error_msg))
						{
							return TASK_OK;
						}
					}
					else
					{
						pushTask($trade_detail_cmd, $shop);
					}
				}	
			}
			if($countLimit && $total_count >= $countLimit)
				return TASK_SUSPEND;
		}
		
		$end_time = $ptime + 1;
	}
	if($save_time)
	{
		
		if(!$db)
		{
			logx("smtDownloadTradeList getUserDb failed!!",  $sid.'/TradeSlow');
			logx("ERROR $sid smtDownloadTradeList getUserDb",$sid.'/TradeSlow', 'error');
			$error_msg = '连接数据库失败';
			return TASK_OK;
		}
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 smt平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
		releaseDb($db);
	}

	return TASK_OK;
}
?>
