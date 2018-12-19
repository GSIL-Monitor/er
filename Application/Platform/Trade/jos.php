<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jos/JdException.php');
require_once(TOP_SDK_DIR . '/jd/request/PopOrderSearchRequest.php');
require_once(TOP_SDK_DIR . '/jd/request/PopOrderGetRequest.php');
require_once(TOP_SDK_DIR . '/jos/request/ware/WareSkuGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderLbpPrintDataGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderSoplPrintDataGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderPrintDataGetRequest.php');
require_once(TOP_SDK_DIR . '/jos/request/ware/WareListRequest.php');

//处理 省 字符串
function jdProvince($province)
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
//获取sku
function get_sku_info($sid, $db, $shopid, $appkey, $appsecret, $sessionKey, &$o)
{
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $sessionKey;
	
	$req = new WareSkuGetRequest();
	
	$req->setSkuId($o->skuId);
	$req->setFields('ware_id,outer_id,color_value,size_value');
	
	$retval = $jos->execute($req,$jos->accessToken);
	if(isset($result->code) && $result->code <> 100)
	{
		logx("$shopid jos get ware_id and outer_sku_id error in get_sku_info:".print_r($retval,true),  $sid.'/Trade');
		return;
	}
	
	$val = @$retval->color_value . @$retval->size_value;
	$o->wareId = empty($retval->ware_id) ? '' : $retval->ware_id;
	$o->outerSkuId = empty($retval->outer_id) ? '' : $retval->outer_id;
	$o->spec_name = empty($val) ? '' : $val;
	
	return;
}

function get_goods_info($sid, $db, $shopid, $appkey, $appsecret, $sessionKey, &$o)
{
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $sessionKey;
	
	$req = new WareListRequest();
	
	$req->setWareIds($o->wareId);
	$req->setFields('item_num');
				
	$retval = $jos->execute($req,$jos->accessToken);
	if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
	{
        logx("{$sid} get  get_goods_info:{$retval->error_msg}", $sid . "/Trade",'error');
		return;
	}
	
	$o->productNo = $retval->wares[0]->item_num;
		
	return;
	
}

//组装数据，并写库
function loadJdTrade($sid, $appkey, $appsecret, $sessionKey, $shopid, &$db, &$trade_list, &$order_list, &$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	if($t->returnOrder <> 0) {
		$order_auto_downloadjdsellback = getSysCfg($db, 'order_auto_downloadjdsellback', 0);
		if(!$order_auto_downloadjdsellback)
		{
            logx("jos loadJdTrade tid:{$t->order_id}, error_msg:退换单不下载", $sid . "/Trade");
			return true;
		}
	}
	global $zhi_xia_shi;
	$delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0;	//0未付款1部分付款2已付款
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; //已付金额, 发货前已付
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;	// is_processed is more reasonable 
	$voucher = 0;
	$voucher_type_list = array(39, 41, 52); //39-京豆优惠,41-京东券优惠, 52-礼品卡优惠,
	$tid = $t->orderId;
	$receivable = bcadd(@$t->orderSellerPrice,@$t->freightPrice);
	
	if($t->payType == '1-货到付款')
	{
		$delivery_term=2;
	}
	else if ('WAIT_SELLER_STOCK_OUT' == $t->orderState ||
			 'SEND_TO_DISTRIBUTION_CENER' == $t->orderState ||
			 'DISTRIBUTION_CENTER_RECEIVED' == $t->orderState ||
			 'WAIT_GOODS_RECEIVE_CONFIRM' == $t->orderState ||
			 'RECEIPTS_CONFIRM' == $t->orderState ||
			 'WAIT_SELLER_DELIVERY' == $t->orderState ||
			 'FINISHED_L' == $t->orderState ||
			 'LOCKED' == $t->orderState)
	{
		$pay_status = 2;
		$paid = $receivable;
	}
	
	switch($t->orderState)
	{
		case 'LOCKED':
		{
			if(!empty($t->logisticsId))
			{
				$trade_status = 50;
				$is_external = 1;
			}
			else
			{
				$trade_status = 30;
				$process_status = 10;
				$trade_refund_status = 1;
				$order_refund_status = 2;
			}
			break;
		}
		case 'TRADE_CANCELED':
		{
			$trade_status = 90;
			$trade_refund_status = 3;
			$order_refund_status = 5;
			break;
		}
		case 'WAIT_SELLER_STOCK_OUT':
		case 'SEND_TO_DISTRIBUTION_CENER':
		case 'WAIT_SELLER_DELIVERY':
		{
			$trade_status = 30;
			$process_status = 10;
			break;
		}
		case 'DISTRIBUTION_CENTER_RECEIVED':
		case 'WAIT_GOODS_RECEIVE_CONFIRM':
		{
			$trade_status = 50;
			$is_external = 1;
			break;
		}
		case 'FINISHED_L':
		{
			$trade_status = 70;
			$is_external = 1;
			break;
		}
		case 'POP_ORDER_PAUSE':
		{
			$process_status = 10;
			break;
		}
		default:
		{
            logx("ERROR $sid invalid_trade_status $tid {$t->order_state}", $sid . "/Trade",'error');
		}
	}

	$warehouse_no = '';
	//京仓订单  
	$wms_type = 0;
	if (isset($t->storeOrder) && $t->storeOrder == '京仓订单')
	{
		$warehouse_no = 1;
		if ($trade_status == 50 || $trade_status == 60 || $trade_status == 70)
		{
			$wms_type = 2;
		}else
		{
			logx("京仓订单未发货不下载 tid:{$tid}",$sid.'/Trade');
			return true;
		}
	}
	
	//总折扣
	$total_discount = $t->sellerDiscount;
	//邮费
	$post_fee = $t->freightPrice;
	
	$invoiceInfo = $t->invoiceInfo;
	
	if(strpos($invoiceInfo, ';') === false)
	{
		$invoiceType = '';
		$invoice_title = '';
		$invoiceContent = '';
		$invoice_type = 0;
	}
	else
	{
		$invoiceInfo = explode(';', $invoiceInfo);
		
		$invoiceType = substr(strstr($invoiceInfo[0], ':'), 1);
		$invoice_title = substr(strstr($invoiceInfo[1], ':'), 1);
		$invoiceContent = substr(strstr($invoiceInfo[2], ':'), 1);
		
		if(isset($t->invoiceCode))
		{
			$invoiceContent = "内容:".$invoiceContent.";纳税人识别号:".$t->invoiceCode.";";
		}
		
		$invoice_type = 1;
	}
	
	if (!empty($t->vatInfo))
	{
		$invoiceContent = "内容:".$invoiceContent.";纳税人识别号:".$t->vatInfo->vatNo.";地址:".$t->vatInfo->addressRegIstered." ".$t->vatInfo->phoneRegIstered.";开户银行:".$t->vatInfo->depositBank." ".$t->vatInfo->bankAccount.";";
		$invoice_type = 2;
	}
	
	$orders = & $t->itemInfoList;
	$coupons = $t->couponDetailList;
	
	$orderId = 1;
	$order_arr = array();
	
	$trade_share_discount = $total_discount;
	
	for ($i = 0; $i < count($coupons); ++$i)
	{
		$p = & $coupons[$i];
		
		if (empty($p->couponType))
		{
			continue;
		}
		
		$type = intval(explode('-', $p->couponType)[0]);
		
		if (!empty($p->skuId))
		{
			$trade_share_discount = bcsub($trade_share_discount, $p->couponPrice);
		}
		else if(in_array($type, $voucher_type_list))
		{
			$voucher = bcadd($voucher, $p->couponPrice);
		}
		
		if (41 == $type)
		{
			$is_bonus = 1;
		}
		else
		{
			$is_bonus = 0;
		}
		
		$discount_list[] = array
		(
			'platform_id' => 3,
			'tid' => $tid,
			'oid' => @$p->skuId,
			'sn' => '',
			'type' => $type,
			'name' => $p->couponType,
			'is_bonus' => $is_bonus,
			'detail' => '',
			'amount' => $p->couponPrice
		);
	}
	
	//voucher为了计算COD订单已支付多少
	$voucher = bcadd($voucher, @$t->balanceUsed);
	
	//以下为邮费、已付时行分摊
	$left_post = $post_fee;
	$left_share_discount = $trade_share_discount;
	$left_voucher = $voucher;
	$trade_fee = bcsub(bcadd($receivable, $trade_share_discount),@$t->freightPrice);
	$order_count = count($orders);
	$goods_count = 0;
	
	$filter = 1;//过滤赠品计算分摊 1不过滤 2过滤
	$k = $order_count -1;//最后一个数组的下标
	if ($k != 0) {
		for ($i=$k; $i >= 0 ; $i--) 
		{ 
			$jd_price = &$orders[$i]->jdPrice;
			$jd_num = &$orders[$i]->itemTotal;
			$jd_goods_fee = bcmul($jd_price, $jd_num);
			$jd_sku_id = &$orders[$i]->skuId;
			$order_discount = 0;
			for ($j = 0; $j < count($coupons); ++$j)
			{
				if (isset($coupons[$j]->couponPrice) && ($jd_goods_fee >= $coupons[$j]->couponPrice) && isset($coupons[$j]->skuId) && ($jd_sku_id == $coupons[$j]->skuId))
				{
					$order_discount = $coupons[$j]->couponPrice;
					break;
				}
			}
			if ($jd_price == 0 || $jd_goods_fee == $order_discount) {
				++$filter;
			}else
			{
				break;
			}
		}
	}
	
	$counter = 0;
	//特殊卖家配置--必须规格名 开启1
	$isDownGoodsSpec_jd = getSysCfg($db, 'isDownGoodsSpec_jd_hideCfg', 0);
	
	for($i = 0; $i < $order_count; $i++)
	{
		$o = & $orders[$i];

		if($isDownGoodsSpec_jd)
		{
			$counter++;
			if(0 == $counter%3)	resetAlarm();
			get_sku_info($sid, $db, $shopid, $appkey, $appsecret, $sessionKey, $o);
		}
		/*else if (empty($o->outer_sku_id))
		{
			$counter++;
			if(0 == $counter%3)	resetAlarm();
			
			@logx("tid: $tid sku_id: {$o->sku_id} ware_id: {$o->ware_id} product_no: {$o->product_no} outer_sku_id: {$o->outer_sku_id}", $sid);
			get_sku_info($sid, $db, $shopid, $appkey, $appsecret, $sessionKey, $o);
		}*/
		/*
		if (empty($o->product_no))
		{
			$counter++;
			if(0 == $counter%10)	resetAlarm();
			get_goods_info($sid, $db, $shopid, $appkey, $appsecret, $sessionKey,$o);
		}
		*/
		$spec_no = trim(@$o->outerSkuId);
		if(iconv_strlen($spec_no,'UTF-8')>40)
		{
            logx("GOODS_SPEC_NO_EXCEED\t{$spec_no}\t" . @$o->sku_name, $sid . "/Trade");
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
			
			$spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
		}

		$num = $o->itemTotal;
		$goods_count += (int)$num;
		$price = $o->jdPrice;
		$goods_fee = floatval($price) * $num;
		
		$oid = $tid . ':' . $o->skuId;
		if(isset($order_arr[$oid]))
		{
			$oid = $oid . ':' . $orderId;
			++$orderId;
		}
		$order_arr[$oid] = 1;
		
		$discount = 0;
		for ($j = 0; $j < count($coupons); ++$j)
		{
			if (isset($coupons[$j]->couponPrice) && ($goods_fee >= $coupons[$j]->couponPrice) && isset($coupons[$j]->skuId) && ($o->skuId == $coupons[$j]->skuId))
			{
				$discount = $coupons[$j]->couponPrice;
				array_splice($coupons, $j, 1);
				break;
			}
		}
		
		$goods_fee = bcsub(bcmul($price, $num), $discount);
		
		if ($i == $order_count - $filter)
		{
			$goods_share_amount = $left_share_discount;
			$share_post = $left_post;
			if (2 == $delivery_term)
			{
				$order_paid = $left_voucher;
			}
		}
		else
		{
			$goods_share_amount = ($trade_fee> 0)?(float)bcdiv(bcmul($trade_share_discount, $goods_fee), $trade_fee):0;
			$left_share_discount = bcsub($left_share_discount, $goods_share_amount);
			
			$share_post = ($trade_fee> 0)?(float)bcdiv(bcmul($post_fee, $goods_fee), $trade_fee):0;
			$left_post = bcsub($left_post, $share_post);
			
			if (2 == $delivery_term)
			{
				$order_paid = ($trade_fee> 0)?bcdiv(bcmul($voucher, $goods_fee), $trade_fee):0;
				$left_voucher = bcsub($left_voucher, $order_paid);
			}
		}
		
		$share_amount = bcsub($goods_fee, $goods_share_amount);
		
		if ($delivery_term != 2)
		{
			$order_paid = bcadd($share_amount, $share_post);
		}
		
		$order_list[] = array
		(
		    'shop_id' => $shopid,
			'platform_id' => 3,
			'tid' => $tid,
			'oid' => $oid,
			'status' => $trade_status,
			'refund_status' => $order_refund_status,
			'order_type' => 0,
			'invoice_type' => $invoice_type,
			'bind_oid' => '',
			'goods_id' => trim(@$o->wareId),
			'spec_id' => trim(@$o->skuId),
			'goods_no' => iconv_substr(@$o->productNo,0,40,'UTF-8'),
			'spec_no' => $spec_no,
			'goods_name' => iconv_substr(@$o->skuName,0,255,'UTF-8'),
			'spec_name' => $isDownGoodsSpec_jd? $o->spec_name : iconv_substr($o->skuName,0,100,'UTF-8'),
			'refund_id' => '',
			'num' => $num,
			'price' => $price,
			'adjust_amount' => 0,		//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $discount,			//子订单折扣
			'share_discount' => $goods_share_amount, 	//分摊优惠
			'total_amount' => $goods_fee,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,		//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $share_post,			//分摊邮费
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => $order_paid, // jd seems no refund in trade api
			'created' => array('NOW()')
		);
		
	}
	
	$receiver_address = @$t->consigneeInfo->fullAddress;//收货地址
	$receiver_city = @$t->consigneeInfo->city;			//城市
	$receiver_district = @$t->consigneeInfo->county;	//区县
	$receiver_mobile = @$t->consigneeInfo->mobile;		//手机
	$receiver_name = @$t->consigneeInfo->fullname;		//姓名
	$receiver_phone = @$t->consigneeInfo->telephone;	//电话
	$receiver_state = @$t->consigneeInfo->province;	//省份
	
	//将地址中省市区去掉
	$prefix = $receiver_state . $receiver_city . $receiver_district;
	$len = iconv_strlen($prefix, 'UTF-8');
	if(iconv_substr($receiver_address, 0, $len, 'UTF-8') == $prefix)
		$receiver_address = iconv_substr($receiver_address, $len, 256, 'UTF-8');
	
	$receiver_state = jdProvince($receiver_state);
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
	
	getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
	
	if(!empty($receiver_mobile) && !empty($receiver_phone) && $receiver_mobile != $receiver_phone)
		$mobile = $receiver_mobile . " " . $receiver_phone;
	else if(!empty($receiver_mobile))
		$mobile = $receiver_mobile;
	else
		$mobile = $receiver_phone;
		

	if(!empty($t->pin))
	{
		$nick = trim($t->pin);
	}
	else if(!empty($receiver_mobile)) 
	{
		$nick = 'JD'.$receiver_mobile;
	}
	else 
	{
		$nick = 'JD'.$receiver_phone;
	}

	$ext_cod_fee = 0;
	if ($delivery_term == 2 && (($t->orderPayment+$voucher) - $t->orderSellerPrice - $t->freightPrice == '0.5'))
	{
		$ext_cod_fee = '0.5'; 
	}
	$trade_list[] = array
	(
		'platform_id' => 3,
		'shop_id' => $shopid,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => ($wms_type == 2)?10:$process_status,
		
		'delivery_term' => $delivery_term,
		'trade_time' => dateValue($t->orderStartTime),
		'pay_time' => dateValue(@$t->paymentConfirmTime),
		
		'buyer_nick' => iconv_substr($nick,0,100,'UTF-8'),
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => '',
		'pay_account' => '',
		
		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
		'receiver_zip' => '',
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => @$t->deliveryType,
		
		'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
		'logistics_type' => -1,
		
		'invoice_type' => $invoice_type,
		'invoice_title' => iconv_substr($invoice_title,0,255,'UTF-8'),
		'invoice_content' => iconv_substr($invoiceContent,0,255,'UTF-8'),
		'buyer_message' => iconv_substr(@$t->orderRemark,0,1024,'UTF-8'),
		'remark' => iconv_substr(@$t->venderRemark,0,1024,'UTF-8'),
		'remark_flag' => 0,
		
		'end_time' => dateValue(@$t->orderEndTime),
		'wms_type' => $wms_type,
		'warehouse_no' => $warehouse_no,
		'stockout_no' => '',
		'logistics_no' => iconv_substr(@$t->waybill,0,40,'UTF-8'),
		'is_auto_wms' => ($wms_type == 2)?1:0,
		'is_external' => $is_external,
		
		'goods_amount' => @$t->orderTotalPrice,
		'post_amount' => $post_fee,
		'receivable' => $receivable,
		'discount' => $total_discount,
		'paid' => (2 == $delivery_term) ? $voucher : $receivable,
		'received' => 0,
		
		'platform_cost' => 0,
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		'ext_cod_fee'=>$ext_cod_fee,
		
		'cod_amount' => (2 == $delivery_term) ? @$t->orderPayment : 0,
		'dap_amount' => (2 == $delivery_term) ? 0 : $receivable,
		'refund_amount' => 0,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		
		'created' => array('NOW()')
	);

	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	
	return true;
}

//抓取单条订单
function downJosTradesDetail($db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;

	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = & $trades->tids;
	
	//API系统参数
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $trades->session;
	
	$req = new PopOrderGetRequest();
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		$req->setOptionalFields("orderId ,venderId ,orderType ,payType ,orderTotalPrice ,orderSellerPrice ,orderPayment ,freightPrice ,sellerDiscount "
						   .",orderState ,orderStateRemark ,deliveryType ,invoiceInfo ,invoiceCode ,orderRemark ,orderStartTime ,orderEndTime "
						   .",consigneeInfo ,itemInfoList ,couponDetailList ,venderRemark ,balanceUsed ,pin ,returnOrder ,paymentConfirmTime ,waybill "
						   .",logisticsId ,vatInfo ,modified ,customs ,customsModel ,orderSource ,storeOrder ,idSopShipmenttype ,serviceFee ,tuiHuoWuYou ");
		
		$req->setOrderId($tid);
		
		$retval = $jos->execute($req,$jos->accessToken);
		if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->error_msg;
			return TASK_SUSPEND;
		}
		if(empty($retval->orderDetailInfo->orderInfo))
		{
			$error_msg = '没有获取到订单信息';
			return TASK_OK;
		}
		
		if(!loadJdTrade($sid, $appkey, $appsecret, $trades->session, $shopid, $db, $trade_list, $order_list, $discount_list, $retval->orderDetailInfo->orderInfo, $new_trade_count, $chg_trade_count, $error_msg))
		{
			return TASK_SUSPEND;
		}
	}
	
	//保存剩下的到数据库
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
}

//京东订单下载
function josDownloadTradeList(&$db, $appkey, $appsecret, $shop, 
	$countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{

	$ptime = $end_time;
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
    logx("JosDownloadShop $shopid start_time:" .
        date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");

    $jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $shop->session;
	$req = new PopOrderSearchRequest();

	$loop_count = 0;
	$page_size = 40;

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	while($ptime > $start_time)
	{
		$loop_count++;
		
		if ($loop_count > 1) 
		{
			resetAlarm();
		}
		//时间间隔1天，按时间倒抓
		if ($ptime - $start_time > 3600 * 24)
		{
			$ptime = $end_time - 3600 * 24 + 1;
		}
		else
		{
			$ptime = $start_time;
		}

		$req->setStartDate(date('Y-m-d H:i:s', $ptime));
		$req->setEndDate(date('Y-m-d H:i:s', $end_time));
		$req->setOptionalFields("orderId ,venderId ,orderType ,payType ,orderTotalPrice ,orderSellerPrice ,orderPayment ,freightPrice ,sellerDiscount "
					   .",orderState ,orderStateRemark ,deliveryType ,invoiceInfo ,invoiceCode ,orderRemark ,orderStartTime ,orderEndTime "
					   .",consigneeInfo ,itemInfoList ,couponDetailList ,venderRemark ,balanceUsed ,pin ,returnOrder ,paymentConfirmTime ,waybill "
					   .",logisticsId ,vatInfo ,modified ,customs ,customsModel ,orderSource ,storeOrder ,idSopShipmenttype ,serviceFee ,tuiHuoWuYou ");
		
		if (0 == $shop->sub_platform_id) // 0:sop
		{
			$req->setOrderState('WAIT_SELLER_STOCK_OUT,WAIT_GOODS_RECEIVE_CONFIRM,FINISHED_L,TRADE_CANCELED,LOCKED');
		}
		else if (1 == $shop->sub_platform_id || 2 == $shop->sub_platform_id) //1:lbp, 2:sopl
		{
			$req->setOrderState('WAIT_SELLER_STOCK_OUT,SEND_TO_DISTRIBUTION_CENER,DISTRIBUTION_CENTER_RECEIVED,WAIT_GOODS_RECEIVE_CONFIRM,RECEIPTS_CONFIRM,FINISHED_L,TRADE_CANCELED,LOCKED');
		}
		else if (4 == $shop->sub_platform_id) //4:sopo
		{
			$req->setOrderState('WAIT_SELLER_STOCK_OUT,WAIT_GOODS_RECEIVE_CONFIRM,WAIT_SELLER_DELIVERY,FINISHED_L,TRADE_CANCELED,LOCKED');
		}
		else
		{
            logx("ERROR $sid josDownloadTradeList unknown subtype: {$shop->sub_platform_id}!!", $sid . "/Trade",'error');
			$req->setOrderState('WAIT_SELLER_STOCK_OUT,WAIT_GOODS_RECEIVE_CONFIRM,FINISHED_L,TRADE_CANCELED,LOCKED');
		}
		
		$req->setPageSize($page_size);
		
		$req->setPage(1);

		$retval = $jos->execute($req,$jos->accessToken);

		if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
		{
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
			return TASK_OK;
		}
		//总条数
		$total_results = intval($retval->searchorderinfo_result->orderTotal);
        logx("JosTrade $shopid count: $total_results", $sid . "/Trade");
		
		//just one page
		if ($total_results <= $page_size)
		{
			if(isset($retval->searchorderinfo_result->orderInfoList))
			{
				$trades = $retval->searchorderinfo_result->orderInfoList;
				$total_trade_count += count($trades);
				for($j =0; $j < count($trades); $j++)
				{
					$t = & $trades[$j];
					
					if(!loadJdTrade($sid, $appkey, $appsecret, $shop->session, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
					{
						return TASK_OK;
					}
				}
				
				if($countLimit && $total_trade_count >= $countLimit)
					return TASK_SUSPEND;
			}
		}
		// more than one page, get orders from last page in case of order-dropping
		else
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
		
			for($i=$total_pages; $i>=1; --$i)
			{
				$req->setPage($i);
				$retval = $jos->execute($req,$jos->accessToken);
				if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
				{
					$error_msg = $retval->error_msg;
					return TASK_OK;
				}
				
				resetAlarm();
				
				$trades = $retval->searchorderinfo_result->orderInfoList;
				$total_trade_count += count($trades);
				for($j =0; $j < count($trades); $j++)
				{
					$t = & $trades[$j];
					
					if(!loadJdTrade($sid, $appkey, $appsecret, $shop->session, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
					{
						return TASK_OK;
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
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_OK;
		}
	}
	
	//保存下载时间
	if($save_time)
	{
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 jos平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}
	
	return TASK_OK;
}

function lbp_print_data(&$db,$shop_id,$tid,$appkey,$appsecret,$session,&$rows,&$error_msg)
{
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $session;
			
	$req = new LbpOrderPrintDataGetRequest();
	$req->setOrderId($tid);
			
	$retval = $jos->execute($req,$jos->accessToken);
	if(API_RESULT_OK != josErrorTest($retval, $db, $shop_id))
	{
		$error_msg = $retval->error_msg;
		return false;
	}
	else
	{
		$rows = array(array(json_encode(array('status' => 0,
									  'bf_deli_good_glag' => $retval->bf_deli_good_glag,
									  'cod_time_name' => $retval->cod_time_name,
									  'cky2_name' => $retval->cky2_name,
									  'partner' => $retval->partner,
									  'msg' => ''))));
	}
	return true;
}
function sopl_print_data(&$db,$shop_id,$tid,$appkey,$appsecret,$session,&$rows,&$error_msg)
{
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret = $appsecret;
	$jos->accessToken = $session;
		
	$req = new SoplOrderPrintDataGetRequest();
	$req->setOrderId($tid);
			
	$retval = $jos->execute($req,$jos->accessToken);
	if(API_RESULT_OK != josErrorTest($retval, $db, $shop_id))
	{
		$error_msg = $retval->error_msg;
		return false;
	}
	else
	{
		$rows = array(array(json_encode(array('status' => 0,
									'bf_deli_good_glag' => $retval->bf_deli_good_glag,
									'cod_time_name' => $retval->cod_time_name,
									'cky2_name' => $retval->cky2_name,
									'partner' => $retval->partner,
									'msg' => ''))));
	}
	return true;
}


function jos_order_print_data(&$db,$sid,$shop_id,$appkey,$appsecret,$sessionkey,$order_id,&$data)
{
	$jos = new JdClient();
	$jos->appKey = $appkey;
	$jos->appSecret =  $appsecret;
	$jos->accessToken =  $sessionkey;
	
	$req = new OrderGetRequest();
	$req->setOrderId($order_id);
	
	$retval = $jos->execute($req,$jos->accessToken);
	if(API_RESULT_OK != josErrorTest($retval, $db, $shop_id))
	{
        logx("ERROR $sid get_should_pay {$retval->error_msg}", $sid . "/Trade",'error');

		return false;
	}
	$data['should_pay'] = $retval->order->orderInfo->order_payment;
	
	/*
	$req = new OrderPrintDataGetRequest();
	$req->setOrderId($order_id);
	
	$retval = $jos->execute($req,$jos->accessToken);
	if(API_RESULT_OK != josLogisticsErrorTest($retval,$db,$shop_id))
	{
		logx("get_should_pay failed: {$retval->error_msg} ", $sid);
        logx("ERROR $sid get_should_pay {$retval->error_msg}", $sid.'/Trade','error');

		return false;
	}
	$data['should_pay'] = $retval->should_pay;
	*/
	return true;
	
}
?>
