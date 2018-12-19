<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/yhd/YhdClient.php');

function yhdProvince($province)
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

function loadYhdTrade($url, $params, $appsecret, $shopid, &$db, &$trade_list, &$order_list,&$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid)
{
	global $zhi_xia_shi;
	$od = &$t->orderDetail;
    	$tid = $od->orderCode;
	$curStatus = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$pay_status=0;
	$trade_refund_status=0;//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;		//0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$trade_refund_amount=0;
	$process_status = 70;//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    	$updateTime=0;	
	$is_external=0;
	$order_count = 0; 
	$goods_count = 0;
	$trade_mask = $od->isMobileOrder;
	$paid = 0;
	$delivery_term = 1;
	$pay_method = 1;
	
	if (2 == $od->payServiceType || 9 == $od->payServiceType || 10 == $od->payServiceType || 5== $od->payServiceType ||12 == $od->payServiceType )
	{
		$delivery_term = 2;
		$pay_method = 2;
	}
	else if (7 == $od->payServiceType)
	{
		$delivery_term = 3;
	}

	if($od->orderStatus == 'ORDER_WAIT_PAY')
	{
		if (!empty($od->isDepositOrder))
		{
			$curStatus = 20;
			$paid = $od->orderDeposit;
		}
		
		$process_status = 10;
	}
	else if($od->orderStatus == 'ORDER_PAYED' ||
		$od->orderStatus == 'ORDER_TRUNED_TO_DO' )
	{   
		$pay_status=2;
		$curStatus = 30;
		$process_status = 10;
		$paid = $od->orderAmount - $od->orderCouponDiscount;
	}
	else if($od->orderStatus == 'ORDER_OUT_OF_WH' )
	{  
		$pay_status=2;
		$curStatus = 50;
		$is_external=1;//抓单时已发货，未经系统系统处理的订单
		$paid = $od->orderAmount - $od->orderCouponDiscount;
	}
	else if($od->orderStatus == 'ORDER_RECEIVED')
	{   
		$pay_status=2;
		$curStatus = 60;
		$is_external=1;
		$paid = $od->orderAmount - $od->orderCouponDiscount;
	}
	else if($od->orderStatus == 'ORDER_FINISH')
	{   
		$updateTime=$od->updateTime;
	    $pay_status=2;
		$curStatus = 70;
		$is_external=1;
		$paid = $od->orderAmount - $od->orderCouponDiscount;
	}
	else if($od->orderStatus == 'ORDER_CANCEL')
	{   
		$updateTime=$od->updateTime;
		$curStatus = 90;

	}

	
	$receiver_name = @$od->goodReceiverName;		//姓名
	$receiver_city = @$od->goodReceiverCity;			//城市
	$receiver_district = @$od->goodReceiverCounty;	//区县
	$receiver_state = $od->goodReceiverProvince;		//省份
	$receiver_state = yhdProvince($receiver_state);
	/*
	if(in_array($receiver_state, $zhi_xia_shi))
	{
		$receiver_district = $receiver_city;
		$receiver_city = $receiver_state . '市';
	}
	*/
	if(!empty($receiver_district))
	{
		$receiver_area = "$receiver_state $receiver_city $receiver_district";
	}
	else
	{
		$receiver_area = "$receiver_state $receiver_city";
	}
	
	 getAddressID($receiver_state, $receiver_city, $receiver_district,$province_id, $city_id, $district_id);
	 $receiver_address=$od->goodReceiverAddress;
	 $mobile = @$od->goodReceiverMoblie;		//手机
	 $telno = @$od->goodReceiverPhone;	//电话
	 $orderNeedInvoice=$od->orderNeedInvoice;
	 
	 if(isset($od->applyCancel) && $od->applyCancel == 1)
	 {
		 $trade_refund_status = 1;
		 $order_refund_status = 2;
	 }
	

	if($orderNeedInvoice == 0)
	{
		$invoice_type = 0;
	}elseif( $orderNeedInvoice == 1 || $orderNeedInvoice == 2 )
	{
		$invoice_type = 1;
	}else
	{
		$invoice_type=2;
	}
	 
	 $logistics_no= $od->merchantExpressNbr;
	 if (is_null($logistics_no)){$logistics_no=' ';}
	 
	 $trade_discount = bcsub(bcadd($od->productAmount,$od->orderDeliveryFee),$od->orderAmount)+(float)$od->orderCouponDiscount;
	 
	 $orders = $t->orderItemList->orderItem;
	 
	for($i=0; $i<count($orders); $i++)
	{   
		$o = & $orders[$i];
		$goods_no = trim(@$o->outerId);
		if(iconv_strlen($goods_no, 'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t".@$o->productCName, $sid. "/Trade");
			
			$message = '';
			if(iconv_strlen($goods_no, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goods_no}";
			
			//发即时消息
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
		}
		++$order_count;
		$num = $o->orderItemNum;
		$goods_count += (int)$o->orderItemNum;
		$share_discount = $o->promotionAmount+(float)$o->couponAmountMerchant;
		$share_post=$o->deliveryFeeAmount;
		// subsidyAmount is not used now
		$discount = bcmul(bcsub(bcsub($o->originalPrice,$o->orderItemPrice), $o->subsidyAmount), $num);
	    	$share_amount= bcsub($o->orderItemAmount,$share_discount);
	    
		if (10 == $curStatus || 20 == $curStatus)
		{
			$goods_paid = 0;
		}
		else
		{
			$goods_paid = bcadd($share_amount, $share_post);
		}
		
		$order_list[] = array
		(
			'platform_id' => 6,
			'tid' => $tid,
			'oid' => $o->id,
			'shop_id' => $shopid,
			//平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
			'status' => $curStatus,
			'refund_status' => $order_refund_status,
			'process_status' => $process_status,
			'order_type' => 0,
			'invoice_type' => $invoice_type,
			'bind_oid' => '',
			'goods_id' => trim($o->productId),
			'spec_id' => '',
			'goods_no' => $goods_no,
			'spec_no' => '',
			'goods_name' => iconv_substr(@$o->productCName,0,255,'UTF-8'),
			'spec_name' => '',
			'refund_id' =>'',
			'num' => $num,
			'price' => $o->originalPrice,//产品原价格 orderItemPrice
			'adjust_amount' => 0,//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $discount,			//子订单折扣
			'share_discount' => $share_discount, 	//分摊优惠
			'total_amount' => $o->orderItemAmount,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,	//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $share_post,			//分摊邮费
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => 2 == $delivery_term ? 0 : $goods_paid,
			'created' => array('NOW()')
		);
		
		if(bccomp($o->promotionAmount, 0))
		{
			$discount_list[] = array
			(
				'platform_id' => 6,
				'tid' => $tid,
				'oid' => $o->id,
				'sn' => '', 
				'type' => 'promotionAmount',
				'name' => '促销活动立减分摊金额',
				'is_bonus' => 0,
				'detail' => '促销活动立减分摊金额',
				'amount' => $o->promotionAmount
			);
		}
		
		if(bccomp($o->couponAmountMerchant, 0))
		{
			$discount_list[] = array
			(
				'platform_id' => 6,
				'tid' => $tid,
				'oid' => $o->id,
				'sn' => '', 
				'type' => 'couponAmountMerchant',
				'name' => '商家抵用券分摊金额',
				'is_bonus' => 0,
				'detail' => '商家抵用券分摊金额',
				'amount' => $o->couponAmountMerchant
			);
		}
		
		if(bccomp($o->couponPlatformDiscount, 0))
		{
			$discount_list[] = array
			(
				'platform_id' => 6,
				'tid' => $tid,
				'oid' => $o->id,
				'sn' => '', 
				'type' => 'couponPlatformDiscount',
				'name' => '1mall平台抵用券分摊金额',
				'is_bonus' => 0,
				'detail' => '1mall平台抵用券分摊金额',
				'amount' => $o->couponPlatformDiscount
			);
		}
		
	}
	
	$trade_list[] = array
	(
        		'platform_id' => 6,
	    	'shop_id' => $shopid,
	    	'tid' => $tid,
	    	'trade_status' => $curStatus,
	    	'pay_status' => 1 == $delivery_term ? $pay_status : 0,
	    	'refund_status' => $trade_refund_status,
	    	'process_status' => $process_status,
			'pay_method' => $pay_method,
	    	'delivery_term' => $delivery_term,
	    	'trade_time' => dateValue($od->orderCreateTime),
		'pay_time' => dateValue(@$od->orderPaymentConfirmDate),
		
		'buyer_nick' => iconv_substr(trim($od->endUserId),0,100,'UTF-8'),
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => '',
		'pay_account' =>'',
		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
		
		'receiver_mobile' => iconv_substr($mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($telno,0,40,'UTF-8'),
		'receiver_zip' => @$od->goodReceiverPostCode,
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => '',
		'receiver_hash' => md5(@$receiver_name.$receiver_area.@$t->receiver_address.$mobile.$telno.@$od->goodReceiverPostCode),
		'logistics_type' => -1,
		'invoice_type' => $invoice_type,
	
		'invoice_title' => iconv_substr($od->invoiceTitle,0,255,'UTF-8'),
		'invoice_content' => iconv_substr(@$od->invoiceContent,0,255,'UTF-8'),
		'buyer_message' => iconv_substr(@$od->deliveryRemark,0,1024,'UTF-8'),
		
		'remark' => iconv_substr(valid_utf8(@$od->merchantRemark),0,1024,'UTF-8'),
		'remark_flag' => 0,
		
		'end_time' =>dateValue($updateTime),

		'is_external' => $is_external,
		//货款,未扣除优惠,退款不变  @$od->orderAmount,//productAmount
		'goods_amount' =>$od->productAmount,//bcsub($t->total_fee,$trade_refund_amount),
		'post_amount' => $od->orderDeliveryFee,
		'receivable' =>@$od->orderAmount - $od->orderCouponDiscount,// bcsub($t->payment,$trade_refund_amount),
		'discount' =>$trade_discount,
		'paid' => 2 == $delivery_term ? 0 : $paid,
		'received' => 0,
		
		//'platform_cost' => '',
		
		'order_count' => $order_count,//子订单
		'goods_count' => $goods_count,//子订单

		'cod_amount' => 2 == $delivery_term ? @$od->collectOnDeliveryAmount : 0,
		'dap_amount' => 2 == $delivery_term ? 0 : $od->orderAmount - $od->orderCouponDiscount,
		'refund_amount' => $trade_refund_amount,
		//'trade_mask' =>'',
		//'score' => '',
		//'real_score' =>'',
		//'got_score' => '',
		'logistics_no' => iconv_substr($od->merchantExpressNbr,0,40,'UTF-8'),
		'warehouse_no' => '',//$od->warehouseId,
		'created' => array('NOW()')
	);
	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	
	return true;
}

function downYhdTradesDetail(&$db,$appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = $trades->tids;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	//API系统参数
	$params = array();
	$params['format'] = "json";
	$params['ver'] = "1.0";
	
	$params['appKey'] = $appkey;
	$params['sessionKey'] = $trades->session;
	$params['timestamp'] = date("Y-m-d H:i:s");
	$url = YHD_API_URL;

	
	$yhd = new YhdClient();
	$tid = implode(',', $tids);

	$params['method'] = "yhd.orders.detail.get";
	$params["orderCodeList"] = $tid;
	
	$retval = $yhd->sendByPost($url, $params, array(), $appsecret);

	if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
	{
		releaseDb($db);
		$error_msg["status"] = 0;
		$error_msg["info"]   = $retval->error_msg;
		
		logx("ERROR $sid downYhdTradesDetail ".$error_msg["info"],  $sid. "/Trade",'error');
		
		return TASK_SUSPEND;
	}

	if(!isset($retval->orderInfoList->orderInfo))
	{
		releaseDb($db);
		$error_msg["status"] = 0;
		$error_msg["info"]   = '读取订单信息失败';
		
		if($retval->code == 'yhd.order.detail.get.not_found') return TASK_OK;
		
		return TASK_SUSPEND;
	}

	$tradelist = $retval->orderInfoList->orderInfo;

	
	for($i=0; $i<count($tradelist); $i++)
	{
		$trade = $tradelist[$i];
	
		$result_code = loadYhdTrade($url,
			$params, 
			$appsecret, 
			$shopid, 
			$db, 
			$trade_list, 
			$order_list, 
			$discount_list,
			$trade, 
			$new_trade_count, 
			$chg_trade_count, 
			$error_msg,
			$sid);
		
		if($result_code === false)
		{
			releaseDb($db);
			return TASK_SUSPEND;
		}
		
		/*if($result_code === -1)
		{
			$tid = $retval->orderInfo->orderDetail->orderCode;
			logx("redown_yhd_trade_for_invoice $tid", $sid);
			
			//补抓
			$trades->tids = array($tid);
			pushTask('trade_get', $trades, 180);
		}*/
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
	
	releaseDb($db);
	return TASK_OK;
}

//异步下载
function yhdDownloadTradeList(&$db,$appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
	$cbp = function(&$trades) use($trade_detail_cmd)
	{
		pushTask($trade_detail_cmd, $trades);
	};
	
	return yhdDownloadTradeListImpl($db,$appkey, $appsecret, $shop,0,0 ,$start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//同步下载
function yhdSyncDownloadTradeList(&$db,$appkey, $appsecret, $shop,$countLimit, $start_time, $end_time, $total_trade_count,&$total_new, &$total_chg, &$error_msg)
{
	$cbp = function(&$trades) use($appkey, $appsecret,&$db,&$total_new, &$total_chg, &$error_msg)
	{
		downYhdTradesDetail($db,$appkey,
			$appsecret,
			$trades,
			$new_trade_count,
			$chg_trade_count,
			$error_msg);
		
		$total_new += $new_trade_count;
		$total_chg += $new_trade_count;
	};
	
	return yhdDownloadTradeListImpl($db,$appkey, $appsecret, $shop,$countLimit , $total_trade_count,$start_time, $end_time, false, $total_count, $error_msg, $cbp);
}

//1号店
function yhdDownloadTradeListImpl(&$db,$appkey, $appsecret, $shop ,$countLimit , $total_trade_count, $start_time, $end_time, $save_time , &$total_count, &$error_msg, $cbp)
{
	$ptime = $end_time;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	
	logx("yhdDownloadTradeListImpl $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");
	
	$total_count = 0;
	
	//API系统参数
	$params = array();
	$params['format'] = "json";
	$params['ver'] = "1.0";
	
	$params['appKey'] = $appkey;
	$params['sessionKey'] = $shop->session;
	$params['timestamp'] = date("Y-m-d H:i:s");
	$url = YHD_API_URL;
	
	
	$params['method'] = "yhd.orders.get";
	
	$params['dateType'] = 5; //增量
	$params["pageRows"] = 50;
	
	$yhd = new YhdClient();

	while($ptime > $start_time)
	{
		if($ptime - $start_time > 3600*24) $ptime = $end_time - 3600*24 + 1;
		else $ptime = $start_time;
		
		$params['orderStatusList'] = 'ORDER_WAIT_PAY,ORDER_PAYED,ORDER_WAIT_SEND,ORDER_ON_SENDING,ORDER_RECEIVED,ORDER_FINISH,ORDER_GRT,ORDER_CANCEL';
		$params["startTime"] = date('Y-m-d H:i:s', $ptime);
		$params["endTime"] = date('Y-m-d H:i:s', $end_time);
		$params["curPage"] = 1;

		$retval = $yhd->sendByPost($url, $params, array(), $appsecret);

		if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
		{
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] = 0;
			logx("ERROR $sid yhdDownloadTradeListImpl fail,error:".$error_msg['info'], $sid . "/Trade",'error');
			return TASK_OK;
		}
		
		if(!isset($retval->orderList) || !isset($retval->orderList->order) || count($retval->orderList->order) == 0)
		{
			$end_time = $ptime + 1;
			logx("YhdTrade $shopid count: 0", $sid . "/Trade");
			continue;
		}

		$trades = $retval->orderList->order;
		//总条数
		$total_results = intval($retval->totalCount);
		$total_count += $total_results;

		logx("YhdTrade $shopid count: $total_results", $sid . "/Trade");
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($trades))
		{
			$tids = array();
			for($j =0; $j < count($trades); $j++)
			{
				$tids[] = $trades[$j]->orderCode;
			}
			
			if(count($tids) > 0)
			{
				$shop->tids = $tids;
				$cbp($shop);
			}
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/50);
			
			for($i=$total_pages; $i>=1; $i--)
			{
				$params["curPage"] = $i;
				
				$retval = $yhd->sendByPost($url, $params, array(), $appsecret);
				
				if(API_RESULT_OK != yhdErrorTest($retval, $db, $shopid))
				{
					$error_msg['info'] = $retval->error_msg;
					$error_msg['status'] = 0;
					logx("ERROR $sid yhdDownloadTradeListImpl fail2", $sid . "/Trade",'error');
					return TASK_OK;
				}
				
				$tids = array();
				$trades = $retval->orderList->order;
				for($j =0; $j < count($trades); $j++)
				{
					$tids[] = $trades[$j]->orderCode;
				}
				
				if(count($tids) > 0)
				{
					$shop->tids = $tids;
					$cbp($shop);
				}
			}
		}
		
		$end_time = $ptime + 1;
	}
	
	if($save_time)
	{
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 yhd平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}

	return TASK_OK;
}

?>