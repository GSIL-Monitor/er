<?php
/*require_once(ROOT_DIR . '/modules/trade_sync/util.php');
require_once(TOP_SDK_DIR . 'mls/MeilishuoClient.php');*/

require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/mls/MeilishuoClient.php');;
require_once(TOP_SDK_DIR . '/mls/request/OrderDetailsRequest.php');
require_once(TOP_SDK_DIR . '/mls/request/OrderListInfoRequest.php');

function meilishuoTradeList(&$db,$appkey,$appsecret,$shop,$countLimit,$start_time,$end_time, $save_time, &$total_count,&$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;

	$page_size =50;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$platform_id = (int)$shop->platform_id;
	if($save_time) 
		$save_time = $end_time;
	
	$ptime = $end_time;
	
	logx("meilishuoTradeList $shopid start_time:" .
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
	if($platform_id == 20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $shop->session, 'xiaodian.trade.sold.get');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke?', $appkey, $appsecret, $shop->session, 'xiaodian.trade.sold.get');
	}

	
	$loop_count = 0;
	$total_count = 0;
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	while($ptime > $start_time){
		$page = 1;
		$ptime = ($ptime - $start_time > 3600*12)?($end_time -3600*12 +1):$start_time;
		$loop_count++;
		if ($loop_count > 1)	resetAlarm();
		$params = array(
			'startUpdated' =>date('Y-m-d H:i:s', $ptime),
			'endUpdated' =>date('Y-m-d H:i:s', $end_time),
			'page' =>$page,
			'pageSize' =>$page_size,
		);
		$app_params['Params']=json_encode($params);
		$retval = $mls->executeByPost($app_params);
		logx("meilishuoTradeList $shopid start_time:" . date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
		if(API_RESULT_OK != meilishuoErrorTest($retval,$db,$shopid)){
			releaseDb($db);
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] = 0;
			if ('0000010' == $retval->status->code){
				refreshMlsToken($appkey, $appsecret, $shop);
				return TASK_OK; 
			}
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] = 0;
			logx("ERROR $sid meilishuoTradeList, error message: ".$error_msg['info'],$sid.'/TradeSlow', 'error');
			return TASK_OK;
		}
		if(!isset($retval->result->data->openApiOrderDetailResDtos)){
			$end_time = $ptime + 1;
			logx("meilishuoTradeList $shopid count: 0", $sid.'/TradeSlow');
			continue;
		}
		$hasNext = $retval->result->data->hasNext;
		//总条数
		//$trades = $retval->order_list_get_response->info;
		$total_results = $retval->result->data->totalNum;
		logx("meilishuoTradeList $shopid total count:$total_results", $sid.'/TradeSlow');
		if(count($retval->result->data->openApiOrderDetailResDtos)>0 && !$hasNext){
			$trades_count = count($retval->result->data->openApiOrderDetailResDtos);
			logx("meilishuoTradeList $shopid count:{$trades_count}", $sid.'/TradeSlow');
			for($j =0; $j < $trades_count; $j++){
				$t = $retval->result->data->openApiOrderDetailResDtos[$j];
				if(!loadmeilishuoTrade($db,$appkey,$appsecret,$platform_id,$shopid,$t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)){
					return TASK_OK;
				}
			}
			$total_count += $trades_count;
		}else {
			$trades_count = 0;
			while($hasNext == 1){
				logx("第".$page."页",$sid.'/TradeSlow');
				$params = array(
					'startUpdated' =>date('Y-m-d H:i:s', $ptime),
					'endUpdated' =>date('Y-m-d H:i:s', $end_time),
					'page' =>$page,
					'pageSize' =>$page_size,
				);
				$app_params['Params']=json_encode($params);
				$retval = $mls->executeByPost($app_params);
				if(API_RESULT_OK != meilishuoErrorTest($retval,$db,$shopid)){
					$error_msg['info'] = $retval->error_msg;
					$error_msg['status'] = 0;
					logx("meilishuoTradeList error: ".$error_msg['info'], $sid.'/TradeSlow');
					return TASK_OK;
				}
				$hasNext = $retval->result->data->hasNext;
				$page= $page + 1;
				if(!isset($retval->result->data->openApiOrderDetailResDtos)){
					logx("meilishuoTradeList3 $shopid count: 0", $sid.'/TradeSlow');
					continue;
				}
				$trades_count += count($retval->result->data->openApiOrderDetailResDtos);
				for($j =0; $j < count($retval->result->data->openApiOrderDetailResDtos); $j++){
					$t = $retval->result->data->openApiOrderDetailResDtos[$j];
					if(!loadmeilishuoTrade($db,$appkey,$appsecret,$platform_id,$shopid,$t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)){
						releaseDb($db);
						return TASK_OK;
					}
				}
			}
			$total_count += $trades_count;
			logx("meilishuoTradeList2 $shopid count:{$trades_count}", $sid.'/TradeSlow');
		}
		$end_time = $ptime + 1;
	}
	if(count($order_list) > 0){
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list,$new_trade_count, $chg_trade_count, $error_msg, $sid)) {
			return TASK_SUSPEND;
		}
	}

	//保存下载时间
	if($save_time) {
		logx("order_last_synctime_{$shop->shop_id}".'上次抓单时间保存 mls平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shop->shop_id}", $save_time);
	}
	return TASK_OK;
}


function loadmeilishuoTrade(&$db,$appkey,$appsecret,$platform_id,$shopid,&$t, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid)
{

	$delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0;	//0未付款1部分付款2已付款
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; //已付金额, 发货前已付
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;	// is_processed is more reasonable 
	
	$tid= $t->shopOrderId;
	//订单状态
	/*ORDER_CREATED": "已下单", 	ORDER_CANCELLED": "订单取消",	ORDER_PAID":"已支付",	ORDER_SHIPPED": "已发货",	
	ORDER_RECEIVED": "已收货",	ORDER_COMPLETED": "订单完成",	ORDER_CLOSED":"订单关闭"*/
	if($t->orderStatus == 'ORDER_CREATED')//订单已创建
	{
		$process_status=10;
	}
	else if($t->orderStatus == 'ORDER_CANCELLED')//未付款就取消
	{
		$trade_status=90;
	}
	else if($t->orderStatus == 'ORDER_PAID')//已支付
	{
		$process_status=10;
		$trade_status=30;
		
	}
	else if($t->orderStatus == 'ORDER_SHIPPED')//已发货
	{
		$trade_status=50;
		$is_external = 1;
	}
	else if($t->orderStatus == 'ORDER_RECEIVED')//已收货
	{
		$trade_status = 60;
		$is_external = 1;
	}
	else if($t->orderStatus == 'ORDER_COMPLETED')//订单完成
	{
		$trade_status=70;
		$is_external = 1;
	}
	else if($t->orderStatus == 'ORDER_CLOSED')//订单关闭:已付款的订单申请退款了
	{
		$trade_status = 80;
	}
	else
	{
		logx("ERROR $sid mls_invalid_trade_status $tid {$t->orderStatus}",$sid.'/TradeSlow','error');
	}

	//收件人信息
	$province = $t->receiverProvince;			//省份
	$city = $t->receiverCity;					//城市
	$district = $t->receiverArea;				//区县
	$receiver_address = $t->receiverAddress;	//收货地址
	$receiver_mobile = trim($t->receiverMobile);//手机
	$receiver_telno = trim(@$t->receiverPhone);
	$receiver_name = $t->receiverName;			//姓名

	if(iconv_substr($province,-1,1,'UTF-8')=='市'){
        $province_t = iconv_substr($province,0,-1,'UTF-8');
        logx("loadmeilishuoTrade province_deal tid:{$tid} province:{$province}=>{$province_t}",$sid.'/TradeSlow');
        $province = $province_t;
    }

	//买家昵称为空处理
	if(empty($t->buyerAccountId))
	{
		$buyer_nick = 'mls';
	}
	else
	{
		$buyer_nick = iconv_substr(valid_utf8(@$t->buyerAccountId),0,100,'UTF-8');
	}

	//地址或昵称为空处理
	if(!isset($t->receiverAddress)||empty($t->receiverAddress)||empty($buyer_nick))
	{
		logx("地址或昵称为空".print_r($t,true),$sid.'/TradeSlow');
		return false;
	}
		
	
	$receiver_area = @$province . " " . @$city . " " . @$district;
	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

	
	//付款状态
	if(strtotime($t->payTimeStr)>0)
	{
		$pay_status = 2;
		$paid = ((float)$t->shopOrderPrice)/100;
	}
	
	
	//$total_discount = (float)$t->shopPromotionAmount/100;
	//订单价格
	$trade_price = (float)$t->shopOrderPrice/100;
	
	
	//邮费
	$post_fee = (float)@$t->shipExpense/100;
	
	//分摊费用处理
	$left_post = $post_fee;
	
	$goods_count = 0;
	$orders = &$t->itemOrderInfos;
	$order_count = count($orders);
	
	//由于接口无直接总商品金额，故计算此金额
	$total_goods_price = 0.00;		//总货款
	for($n=0; $n<count($orders); $n++)
	{
		$o = &$orders[$n];
		
		$total_goods_price += ($o->number*(float)$o->nowPrice/100);
	}	
	//总折扣
	$total_discount = $total_goods_price -  $trade_price;//由于平台优惠中 红包 优惠平台返还给商家，且订单价格shopOrderPrice中是扣除红包的，红包金额接口不可见，故重新计算订单价格
	$left_share_discount = $total_discount;
	for($i=0; $i<count($orders); $i++)
	{
		
		$o = &$orders[$i];
		$oid = $o->itemOrderId;
		
		$num = $o->number;			//数量
		$goods_count += (int)$num;
		$price = (float)$o->nowPrice/100;//单价
		$goods_fee = bcmul($num, $price);//子单商品金额
		
		//退款状态
		/*("return_goods_requested", "买家发起退货，等待卖家同意"),
            ("return_goods_agreed", "卖家同意，等待买家退货"),
            ("return_goods_shipped", "买家发货，等待卖家收货"),
            ("refund_requested", "买家发起退款，等待卖家同意"),
            ("refund_or_return_deal_agreed", "买卖家协商一致，等待资金退款"),
            ("refund_or_return_seller_refused", "买家退款或退货被卖家拒绝"),
            ("return_goods_seller_declare_not_received", "买家发货后，卖家未收到货",),
            ("refund_completed", "退款完成"),
            ("refund_cancelled", "退款取消");*/
			
		if(!empty($o->refundStatus))
		{
			$trade_refund_status = 1;//主订单退款状态
			switch($o->refundStatus)
			{
				case 'return_goods_requested':
				case 'refund_requested':
				case 'refund_or_return_deal_agreed':
					$order_refund_status = 2;
					break;
				case 'return_goods_agreed':
					$order_refund_status = 3;
					break;
				case 'return_goods_shipped':
				case 'return_goods_seller_declare_not_received':
					$order_refund_status = 4;
					break;
				case 'refund_or_return_seller_refused':
				case 'refund_cancelled':
					$order_refund_status = 1;
					break;
				case 'refund_completed':
					$order_refund_status = 5;
					break;
				default:
					$order_refund_status = 0;
					logx("退款状态未定义:{$tid},{$oid},{$o->refundStatus}",$sid.'/TradeSlow');
			}
		
		}
		else
		{
			$order_refund_status = 0;
		}
		
		$order_status = 10;			//平台状态： 平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭

		$order_status = $trade_status;
		if($order_refund_status == 5)
		{
			$order_status = 80;
		}


		//邮费和优惠分摊
		if($i == $order_count - 1)
		{
			$share_discount = $left_share_discount;
			$share_post = $left_post;
		}
		else
		{
			$share_discount = bcdiv(bcmul($total_discount, $goods_fee), $total_goods_price);
			$left_share_discount = bcsub($left_share_discount, $share_discount);
			
			$share_post = round(bcmul($post_fee, $goods_fee)/ $total_goods_price,2);
			$left_post = bcsub($left_post, $share_post);
		}
		$share_amount = bcsub($goods_fee, $share_discount);
		
		$order_paid = bcsub(bcadd($goods_fee, $share_post), $share_discount); //计算子订单已付
			
		//规格名
		$sku_name = '';
		for($m = 0; $m < count($o->skuAttributes); $m++)
		{
			$sku_name .= $o->skuAttributes[$m]->key.":".$o->skuAttributes[$m]->value." ";
		}
		
		$order_list[] = array
		(
			'platform_id' => (int)$platform_id,
				'shop_id' => $shopid,
			'tid' => $tid,
			'oid' => $oid,
			'status' => $order_status,
			'process_status' => $process_status,
			'refund_status' => $order_refund_status,
			'order_type' => 0,
			'invoice_type' => 0,
			'invoice_content' => '',
			'bind_oid' => '',
			'goods_id' => $o->itemId,
			'spec_id' => $o->skuId,
			'goods_no' => iconv_substr(@$o->itemCode,0,40,'UTF-8'),
			'spec_no' => @$o->skuCode,
			'goods_name' =>iconv_substr($o->title,0,255,'UTF-8'),
			'spec_name' => iconv_substr($sku_name,0,100,'UTF-8'),
			'refund_id' =>'',
			'num' => $num,
			'price' => $price,
			'adjust_amount' => 0,
			//'discount' => $discount,			
			'share_discount' => (float)$share_discount, 	
			'total_amount' => (float)$goods_fee,	//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => (float)$share_amount,	//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => (float)$share_post,		//分摊邮费
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => 0 == $pay_status ? 0 : $order_paid,
			'created' => array('NOW()')
		);
		
	}
	
	
	
	
	$trade_list[] = array
	(
        'platform_id' => (int)$platform_id,
	    'shop_id' => $shopid,
	    'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => $process_status,

		'delivery_term' => $delivery_term,		//无发货条件，默认在线下单，无货到付款
		'trade_time' =>$t->createdStr,
		'pay_time' => empty($t->payTimeStr)?'0000-00-00 00:00:00':$t->payTimeStr,
		
		'buyer_nick' => $buyer_nick,//iconv_substr(valid_utf8(@$t->buyerName ),0,100,'UTF-8'),
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => '',
		'pay_account' =>'',
		'receiver_name' =>  iconv_substr(valid_utf8($receiver_name),0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
		
		'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr(@$receiver_telno,0,40,'UTF-8'),
		'receiver_zip' => @$t->receiverZip,
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => '',
		'receiver_hash' => md5(@$receiver_name.$receiver_area.@$receiver_address.$receiver_mobile.@$receiver_telno.@$t->receiverZip),
		'logistics_type' => -1,

		'buyer_message' =>  iconv_substr(valid_utf8(@$t->buyerComment),0,1024,'UTF-8'),
		
		'remark' =>iconv_substr(valid_utf8(@$t->sellerComment),0,1024,'UTF-8'),
		'remark_flag' => 0,

		'goods_amount' =>$total_goods_price,	//货款,未扣除优惠,退款不变
		'post_amount' =>$post_fee,
		'receivable' => $trade_price,
		'discount' => $total_discount,
		'paid' =>  0 == $pay_status ? 0 : $trade_price,
		'received' => 0,
		
		//'platform_cost' => '',
		
		'order_count' => $order_count,//子订单
		'goods_count' => $goods_count,//子订单

		//'cod_amount' => '',
		'dap_amount' =>  $trade_price,
		
		'refund_amount' => 0,
		'logistics_no' => iconv_substr(@$t->shipExpressId,0,40,'UTF-8'),
		'warehouse_no' => '',
		'created' => array('NOW()')
	);
	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	return true;
}


function downmeilishuoTradesDetail($db,$appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
    $chg_trade_count = 0;
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = &$trades->tids;
	$platform_id = (int)$trades->platform_id;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	if($platform_id == 20)
	{
		$mls = new MeilishuoClient('https://openapi.meilishuo.com/invoke?', $appkey, $appsecret, $trades->session, 'xiaodian.trade.get');
	}
	else
	{
		$mls = new MeilishuoClient('https://openapi.mogujie.com/invoke', $appkey, $appsecret, $trades->session, 'xiaodian.trade.get');
	}
	
	
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		
		$params = array(
			'shopOrderId' =>$tid,
		);

		$app_params['openApiOrderDetailReqDto']=json_encode($params);

		$retval = $mls->executeByPost($app_params);
				
		if(API_RESULT_OK != meilishuoErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg["status"] = 0;
			$error_msg["info"] = $retval->status->msg;
			if ('0000010' == $retval->status->code)
			{
				refreshMlsToken($appkey, $appsecret, $trades);
				return TASK_OK; 
			}
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("downmeilishuoTradesDetail $shopid ".$error_msg["info"], $sid.'/TradeSlow');
			return TASK_OK;
		}
		if(!isset($retval->result->data))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = '读取订单信息失败';
			logx("downmeilishuoTradesDetail $shopid ".$error_msg["info"], $sid.'/TradeSlow');
			return TASK_OK;
		}
		if(!loadmeilishuoTrade($db,$appkey,$appsecret,$platform_id,$shopid,$retval->result->data, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_OK;
		}
	}
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list,$new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	return TASK_OK;
}