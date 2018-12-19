<?php


require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/sos/SosClient.php');

function sosProvince($province)
{
	global $spec_province_map;
	
	if(empty($province)) return '';
	
	if(iconv_substr($province, -1, 1, 'UTF-8') != '省')
	{
		$prefix = iconv_substr($province, 0, 2, 'UTF-8');
		
		if(isset($spec_province_map[$prefix]))
			return $spec_province_map[$prefix];
		
		return $province . '省';
	}
	
	return $province;
}


function loadSosTrade($appkey, $appsecret, $sessionKey, $shopid, &$db, &$trade_list, &$order_list,&$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid)
{
	global $zhi_xia_shi;
	$delivery_term = 1; //发货条件 1款到发货
	$pay_status = 2;	//0未付款1部分付款2已付款 默认抓到的都是已付款
	$trade_refund_status = 0;	// 退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	
	if($t->orderTotalStatus == 10)
	{
		$trade_status = 30;
		$process_status = 10;
	}
	else if($t->orderTotalStatus == 20)
	{
		$trade_status = 50;

	}
	else if($t->orderTotalStatus == 21)
	{
		$trade_status = 40;
		$process_status = 10;
	}
	else if($t->orderTotalStatus == 30)
	{
		$trade_status = 70;
	}
	else if($t->orderTotalStatus == 40)
	{
		$trade_status = 90;
	}
	else if($t->orderTotalStatus == 5)
	{
		$process_status = 10;
	}
	else
	{
		logx("ERROR $sid invalid_trade_status {$t->orderCode} {$t->orderTotalStatus}", $sid."/Trade", 'error');
	}
	

	$tid = $t->orderCode;
	
	$invoice_title =@$t->invoiceHead;
	$invoiceContent = @$t->invoice;
	if($t->invoiceType=='01')
	{
		$invoice_type = 2;
	}
	else if($t->invoiceType=='02')
	{
		$invoice_type = 1;
	}else if($t->invoiceType=='05')
	{
		$invoice_type = 0;
	}else{
		$invoice_type = 0;
		logx("invalid_invoiceType {$t->orderCode} {$t->invoiceType}", $sid."/Trade");
	}
	
	$orders = & $t->orderDetail;
	$order_count = count($orders);
	$goods_count = 0;
	$goods_amount = 0;
	$post_amount = 0;
	$receivable = 0;
	$total_discount = 0;
	$refund = false;

	$orderId = 1;
    $order_arr = array();
	for($i = 0; $i < $order_count; $i++)
	{
		$o = & $orders[$i];
		$spec_no = trim(@$o->itemCode);
		if(iconv_strlen($spec_no,'UTF-8')>40)
		{
			logx("$sid GOODS_SPEC_NO_EXCEED\t{$spec_no}\t".@$o->title, $sid."/Trade", 'error');
			$message = '';
			if(iconv_strlen($spec_no, 'UTF-8')>40)
				$message = "{$message}规格商家编码超过40字符:{$spec_no}";
			
			//发即时消息
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);

			$spec_no = iconv_strlen($spec_no,0,40,'UTF-8');
		}
		if (1 == $o->returnOrderFlag)
		{
			$refund = true;
		}
		else
		{
			$refund = false;
			$order_refund_status = 0;
		}
		
		if ($refund)
		{
			if($t->orderTotalStatus == 40)
			{
				$order_refund_status = 5;
			}
			else //if($t->orderTotalStatus == 10)
			{
				$order_refund_status = 2;
			}
		}
		
		$num = $o->saleNum;
		$goods_count += (int)$num;
		$price = $o->unitPrice;
		$goods_amount += bcmul($price, $num);
		
		$post_amount += $o->transportFee;
		$receivable += $o->payAmount;
		$oid = 'SOS' . $tid . '_' . $o->productCode;
		if(isset($order_arr[$oid]))
        {
            $oid = $oid . ':' . $orderId;
            ++$orderId;
        }
        $order_arr[$oid] = 1;


		$goods_share_amount =$o->coupontotalMoney;
		$total_discount += $goods_share_amount;
		$goods_fee = bcsub(bcmul($price, $num), $goods_share_amount);
		$order_paid = bcadd($goods_fee , $o->transportFee);
		$order_list[] = array
		(
			'rec_id' => 0,
			'platform_id' => 13,
			'shop_id'=>$shopid,
			'tid' => $tid,
			'oid' => $oid,
			'status' => $trade_status,
			'refund_status' => $order_refund_status,
			'order_type' => 0,
			'invoice_type' => $invoice_type,
			'bind_oid' => '',
			'goods_id' => trim(@$o->productCode),
			'spec_id' => '',
			'goods_no' => '',
			'spec_no' => $spec_no,
			'goods_name' => '',
			'spec_name' => iconv_substr(@$o->productName,0,100,'UTF-8'),
			'refund_id' => '',
			'num' => $num,
			'price' => $price,
			'adjust_amount' => 0,		//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' =>$goods_share_amount ,			//平台折扣   
			'share_discount' => $goods_share_amount,
			'total_amount' => $goods_fee,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $goods_fee,		//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $o->transportFee,			//分摊邮费
			'paid' => $order_paid,
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' =>@$o->invCode,
			'logistics_no' => '',
			'created' => array('NOW()')
		);
		
	}
	

	
	$receiver_address = valid_utf8(@$t->customerAddress);//收货地址
	$receiver_city = @$t->cityName;			//城市
	$receiver_district = @$t->districtName;	//区县
	$receiver_mobile = @$t->mobNum;		//手机
	$receiver_name = @$t->customerName;	//姓名
	$receiver_phone = '';	//电话
	$receiver_state =@$t->provinceName;	//省份
	
	//将地址中省市区去掉
	$prefix = $receiver_state . $receiver_city . $receiver_district;
	$len = iconv_strlen($prefix, 'UTF-8');
	if(iconv_substr($receiver_address, 0, $len, 'UTF-8') == $prefix)
		$receiver_address = iconv_substr($receiver_address, $len, 256,'UTF-8');
	
	$receiver_state = sosProvince($receiver_state);
	
	if(in_array($receiver_state, $zhi_xia_shi))
	{
		//$receiver_district = $receiver_city;
		$receiver_city = $receiver_state . '市';
	}
	
	if(!empty($receiver_district))
	{
		$receiver_area = "$receiver_state $receiver_city $receiver_district";
	}
	else
	{
		$receiver_area = "$receiver_state $receiver_city";
	}
		
	getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
	if ($refund)
	{
		if($t->orderTotalStatus == 40)
		{
			$trade_refund_status = 3;
		}
		else if($t->orderTotalStatus == 10)
		{
			$trade_refund_status = 1;
		}
	}
	else
	{
		$trade_refund_status = 0;
	}
	$trade_list[] = array
	(
		'tid' => $tid,
		'platform_id' => 13,
		'shop_id' => $shopid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => $process_status,
		
		'delivery_term' => $delivery_term,
		'trade_time' => dateValue($t->orderSaleTime),
		'pay_time' => dateValue($t->orderSaleTime),
		
		'buyer_nick' => iconv_substr(@$t->userName,0,100,'UTF-8'),
		'buyer_name'=> '',
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => '',
		'pay_account' => '',
		
		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' =>iconv_substr(@$receiver_address,0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
		'receiver_zip' => '',
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => '',
		
		'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
		'logistics_type' => -1,
		
		'invoice_type' => $invoice_type,
		'invoice_title' => iconv_substr(valid_utf8($invoice_title),0,255,'UTF-8'),
		'invoice_content'=> iconv_substr($invoiceContent,0,255,'UTF-8'),
		
		'buyer_message' =>iconv_substr(@$t->buyerOrdRemark,0,1024,'UTF-8') ,
		'remark' =>iconv_substr(@$t->sellerOrdRemark,0,1024,'UTF-8'),
		'remark_flag' => 0,
		
		'wms_type' => 0,
		'warehouse_no' => '',
		'stockout_no' => '',
		'logistics_no' => '',
		'is_auto_wms' => 0,
		
		'goods_amount' => $goods_amount,
		'post_amount' => $post_amount,
		'receivable' => $receivable,
		'discount' => $total_discount,
		'received' => 0,
		
		'platform_cost' => 0,
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' => 0,
		'dap_amount' => $receivable,//款到发货金额
		'refund_amount' => 0,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		
		'created' => array('NOW()')
	);
	
	return true;
}

//抓取单条订单

function downsosTradesDetail($db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;

	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = & $trades->tids;
	
	//API系统参数
	$sos = new SosClient();
		
	$sos->setAppKey($appkey);
	$sos->setAppSecret($appsecret);
	$sos->setAccessToken($trades->session);
	$sos->setAppMethod("suning.custom.order.get");
	 
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		$params['sn_request']['sn_body']['orderGet']['orderCode'] =$tid;
		$params = json_encode($params);
		$retval = $sos->execute($params);
		if(API_RESULT_OK != sosErrorTest($retval,$db,$shopid))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("downsosTradesDetail $shopid ".$error_msg['info'], $sid. "/Trade");
			return TASK_OK;
		}
		
		if(!isset($retval->sn_body->orderGet))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = '没有获取到订单信息';
			logx("downsosTradesDetail $shopid ".$error_msg['info'], $sid. "/Trade");
			return TASK_SUSPEND;
		}

		if(!loadSosTrade($appkey, $appsecret, $trades->session, $shopid, $db, $trade_list, $order_list, $discount_list, $retval->sn_body->orderGet, $new_trade_count, $chg_trade_count, $error_msg,$sid))
		{
			logx("loadSosTrade error in downsosTradesDetail", $sid. "/Trade");
			return TASK_SUSPEND;
		}
	}
	//保存剩下的到数据库
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			releaseDb($db);
			return TASK_SUSPEND;
		}
	}

	
	return TASK_OK;
}

function sosDownloadTradeList(&$db,$appkey, $appsecret, $shop,$countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$ptime = $end_time;
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	logx("sosDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid. "/Trade");
	
	if(!$db)
	{
		logx("sosDownloadTradeList getUserDb failed!!", $sid. "/Trade");
		$error_msg['status'] = 0;
		$error_msg['info'] = '连接数据库失败';
		return TASK_OK;
	}
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();	
	
	
	while($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
		
		//sos
		$sos = new SosClient();
		$sos->setAppKey($appkey);
		$sos->setAppSecret($appsecret);
		$sos->setAccessToken($shop->session);
		$sos->setAppMethod("suning.custom.order.query");
		$params['sn_request']['sn_body']['orderQuery']['startTime'] = date('Y-m-d H:i:s', $ptime);
		$params['sn_request']['sn_body']['orderQuery']['endTime'] = date('Y-m-d H:i:s', $end_time);
		$params['sn_request']['sn_body']['orderQuery']['pageNo'] = "1";
		$params['sn_request']['sn_body']['orderQuery']['pageSize'] = "50";
		$params = json_encode($params);

		logx("sosDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid. "/Trade");
		$retval = $sos->execute($params);
		unset($params);

		if(API_RESULT_OK != sosErrorTest($retval, $db, $shopid))
		{
			if(isset($retval->sn_error))
			{
				if ('biz.handler.data-get:no-result' == $retval->sn_error->error_code)
				{
					$end_time = $ptime + 1;
					continue;
				}
				if($retval->error_msg == 'sys.check.api-permission:authority'|| $retval->error_msg == 'sys.check.oauth-permission:authority' || $retval->error_msg == 'sys.oauth.check.access_token:overdue' || $retval->error_msg == 'sys.check.method-permission:authority' || $retval->error_msg == 'sys.oauth.check.access_token:inexistence'){
					refreshSosToken($appkey,$appsecret,$shop,$db);
				}
			}
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			releaseDb($db);
			return TASK_SUSPEND;
		}
		
		logx("sosTrade $shopid total page: {$retval->sn_head->pageTotal}", $sid. "/Trade");
		$trades = $retval->sn_body->orderQuery;
		// just one page
		if (1 == $retval->sn_head->pageTotal)
		{	
			$total_trade_count += count($trades);
			
			for($j =0; $j < count($trades); $j++)
			{
				$t = $trades[$j];
				if(!loadSosTrade($appkey, $appsecret, $shop->session, $shop->shop_id, $db, $trade_list, $order_list,$discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg, $sid))
				{
					logx("loadSosTrade error in one page", $sid. "/Trade");
					releaseDb($db);
					return TASK_OK;
				}
				
			}
			if($countLimit && $total_trade_count >= $countLimit)
				return TASK_SUSPEND;
		}
		//超过一页，第一页抓的作废，从最后一页开始抓
		else
		{
			$total_pages = $retval->sn_head->pageTotal;

			for($i=$total_pages; $i>0; $i--)
			{
				$params['sn_request']['sn_body']['orderQuery']['startTime'] = date('Y-m-d H:i:s', $ptime);
				$params['sn_request']['sn_body']['orderQuery']['endTime'] = date('Y-m-d H:i:s', $end_time);
				$params['sn_request']['sn_body']['orderQuery']['pageNo'] = strval($i);
				$params['sn_request']['sn_body']['orderQuery']['pageSize'] = "50";
				$params = json_encode($params);
				
				$retval = $sos->execute($params);
				unset($params);
			
				if(API_RESULT_OK != sosErrorTest($retval,$db,$shopid))
				{
					if(isset($retval->sn_error))
					{
						if ('biz.handler.data-get:no-result' == $retval->sn_error->error_code)
						{
							$end_time = $ptime + 1;
							continue;
						}
					}
					releaseDb($db);
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					return TASK_SUSPEND;
				}
			
				$trades = &$retval->sn_body->orderQuery;
				$total_trade_count += count($trades);
				for($j =0; $j < count($trades); $j++)
				{
					$t =$trades[$j];
					if(!loadSosTrade($appkey, $appsecret, $shop->session, $shop->shop_id, $db, $trade_list, $order_list,$discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg, $sid))
					{
						releaseDb($db);
						return TASK_OK;
					}
					if(count($order_list) >= 100)
					{
						if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
						{
							return TASK_SUSPEND;
						}
					}
				}
				if($countLimit && $total_trade_count >= $countLimit)
					return TASK_SUSPEND;
			}
		}

			$end_time = $ptime + 1;
	}
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list,$new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}

	if($save_time)
	{
		logx("order_last_synctime_{$shop->shop_id}".'上次抓单时间保存 sos平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shop->shop_id}", $save_time);
	}

	
	return TASK_OK;
}

?>
