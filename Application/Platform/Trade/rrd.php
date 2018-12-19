<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/rrd/RrdClient.php');

function rrdProvince($province)
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

//异步
function rrdDownloadTradeList(&$db,$appkey,$appsecret,$shop,$start_time,$end_time,$save_time,$trade_detail_cmd,&$total_count, &$error_msg){

	$cbp = function (&$trades) use($trade_detail_cmd)
	{
		pushTask ( $trade_detail_cmd, $trades );//加入队列
		return true;
	};
	return rrdDownloadTradeListImpl ( $db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp );
}

// 同步下载（手动抓单）
// countLimit 订单数限制
function rrdSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
	$total_count = 0;
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';
	
	$cbp = function (&$trades) use($appkey, $appsecret, &$db, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
	{
		downRrdTradesDetail ( $db, $appkey, $appsecret, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg );
		
		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;
		
		return ($scan_count < $countLimit);
	};
	
	return rrdDownloadTradeListImpl ( $db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp );
}

function rrdDownloadTradeListImpl ( &$db, $appkey, $appsecret, &$shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp )
{

	$ptime = $end_time;
	if ($save_time) {
		$save_time = $end_time;
	}


	$shop_id = $shop->shop_id;
	$sid = $shop->sid;
	$session = $shop->session;
	logx ( "rrdDownloadTradeList $shop_id start_time:" . date ( 'Y-m-d H:i:s', $start_time ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid .'/TradeSlow');

	$loop_count = 0;
	$total_count = 0;
	$mode = "GET";
	$page = 0;
	$page_size = 40;

	$rrdApi = new RrdClient();
	$rrdApi->appid = $appkey;
	$rrdApi->secret = $appsecret;
	$rrdApi->access_token = $session;
	$rrdApi->method = "weiba.wxrrd.trade.lists";

	$params = array();


	while ($ptime > $start_time)
	{
		
		$ptime = ($ptime - $start_time > 3600 * 24 ) ? ($end_time - 3600 * 24  + 1) : $start_time;
		$loop_count ++;

		if ($loop_count > 1)
			resetAlarm ();
		$params['created_at_start'] = date('Y-m-d H:i:s', $ptime);
		$params['created_at_end'] = date('Y-m-d H:i:s', $end_time);
		$params['offset'] = $page;
		$params['limit'] = $page_size;
		$retval = $rrdApi->execute($params,$mode);
		logx ( "rrdDownloadTradeList $shop_id start_time:" . date ( 'Y-m-d H:i:s', $ptime ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid.'/TradeSlow' );
		//logx("ret:".print_r($retval,true) ,$sid);
		if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id )) {
			if (30008 == intval(@$retval->errCode))
			{	
				releaseDb($db);
				refreshRrdToken($appkey, $appsecret, $shop);
				$error_msg['status'] = 0;
				$error_msg['info'] = $retval->error_msg;
				return TASK_OK; 
			}
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			logx ( "ERROR $sid rrdDownloadTradeListImpl errCode:{$retval->errCode} msg:".$error_msg['info'],$sid.'/TradeSlow' ,'error' );
			return TASK_OK;
		}
		if ($retval->_count == 0) {
			$end_time = $ptime + 1;
			logx ( "rrdDownloadTradeListImpl $shop_id count: 0", $sid .'/TradeSlow');
			continue;
		}

		$trades = $retval->data;
		$total_result = $retval->_count;
		logx("rrdDownloadTradeListImpl $shop_id count : $total_result" ,$sid.'/TradeSlow');
		
		if ($total_result <= count($trades))
		{
			$tids = array();
			foreach ( $trades as $t ){
				logx("rrd tid:".$t->order_sn,$sid.'/TradeSlow');
				$tids [] = $t->order_sn;
			}
			
			if (count ( $tids ) > 0)
			{
				$shop->tids = $tids;
				if (! $cbp ( $shop ))
					return TASK_SUSPEND;
			} 
		}
		else
		{
			$total_pages = floor(floatval($total_result)/$page_size);
			
			for ($i=$total_pages; $i >= 0; $i--)
			{ 
				logx("共{$total_pages} 页, 当前第 {$i} 页", $sid.'/TradeSlow');
				$params['offset'] = $i*$page_size;
				$retval = $rrdApi->execute($params,$mode);
				if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id ))
				{
					if (30008 == intval(@$retval->errCode))
					{	
						releaseDb($db);
						refreshRrdToken($appkey, $appsecret, $shop);
						$error_msg['status'] = 0;
						$error_msg['info'] = $retval->error_msg;
						return TASK_OK; 
					}
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					logx ( "ERROR $sid rrdDownloadTradeListImpl errCode:{$retval->errCode} error_msg:".$error_msg['info'], $sid.'/TradeSlow','error' );

					return TASK_OK;
				}
				$data = $retval->data;
				$tids = array();
				foreach ( $data as $t ){
					logx("rrd tid:".$t->order_sn,$sid.'/TradeSlow');
					$tids [] = $t->order_sn;
				}	
				if (count ( $tids ) > 0)
				{
					$shop->tids = $tids;
					if (! $cbp ( $shop ))
						return TASK_SUSPEND;
				}
			}
		}

		$end_time = $ptime + 1;
	}

	if($save_time)
	{
		logx("order_last_synctime_{$shop_id}".'上次抓单时间保存 rrd平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shop_id}", $save_time);
	}
	return TASK_OK;

}

function downRrdTradesDetail ( &$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg ){

	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	$shop_id = $trades->shop_id;
	$tids = & $trades->tids;//订单编号
	$session = $trades->session;

	$rrdApi = new RrdClient();
	$rrdApi->appid = $appkey;
	$rrdApi->secret = $appsecret;
	$rrdApi->access_token = $session;
	$rrdApi->method = "weiba.wxrrd.trade.details";
	$mode = "GET";

	$trade_list = array();
	$order_list = array();
	$discount_list = array();

	for ($i=0; $i < count($tids) ; $i++) { 
		$tid = $tids[$i];
		$params['order_sn'] = $tid;
		$retval = $rrdApi->execute($params,$mode);

		//logx("retval:".print_r($retval,true) ,$sid);

		if (API_RESULT_OK != rrdErrorTest ( $retval, $db, $shop_id ))
		{
			if (30008 == intval(@$retval->errCode))
			{	
				releaseDb($db);
				refreshRrdToken($appkey, $appsecret, $trades);
				$error_msg["status"] = 0;
				$error_msg["info"]   = $retval->error_msg;
				return TASK_OK; 
			}
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx ( "rrdDownloadTrade fail errCode:{$retval->errCode} error_msg: ".$error_msg["info"], $sid.'/TradeSlow' );
			return TASK_OK;
		}

		$ret = $retval->data;
		if (! loadRrdTradeImpl ( $db, $appkey, $appsecret, $trades, $ret, $trade_list, $order_list, $discount_list ))
		{
			logx("loadRrdTradeImpl false",$sid.'/TradeSlow');
			continue;
		}

		++$scan_count;
		// 写数据库
		if (count ( $order_list ) >= 100)
		{
			if (! putTradesToDb ( $db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid ))
			{
				return TASK_SUSPEND;
			}
		}

	}
	// 保存剩下的到数据库
	if (count ( $order_list ) > 0)
	{
		if (! putTradesToDb ( $db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid ))
		{
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
}


function loadRrdTradeImpl ( $db, $appkey, $appsecret, $shop, &$t, &$trade_list, &$order_list, &$discount_list ){

	$shopId = $shop->shop_id;
	$sid = $shop->sid;
	$tid = $t->order_sn;//平台订单编号

	$process_status = 70;//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$trade_status = 10;//订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭(付款前取消)
	$pay_status = 0;//0未付款1部分付款2已付款
	$delivery_term = 1;//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$trade_refund_status = 0;//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;// 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	
	$is_external = 0;//抓单时已发货，未经系统系统处理的订单
	$refund_amount = 0;//退款金额
	$goods_amount = $t->goods_amount;//货款,未扣除优惠,退款不变
	$receivable = $t->amount; 
	$discount=0;
	for ($d=0; $d < count($t->coupon_amount_details); $d++)
	{
		if (strpos(@$t->coupon_amount_details[$d]->memo, "秒杀") !== FALSE  || strpos(@$t->coupon_amount_details[$d]->memo, "拼团") !== FALSE) {
			continue;
		} 
		if (strpos(@$t->coupon_amount_details[$d]->memo, "余额抵扣") !== FALSE || strpos(@$t->coupon_amount_details[$d]->memo, "余额付款") !== FALSE)
		{
			$receivable = bcadd($receivable, $t->coupon_amount_details[$d]->amount);
		}
		else{
			$discount += $t->coupon_amount_details[$d]->amount;
		}
	}
	$discount -= $t->parent_order_ump;//优惠金额
	$post_amount = $t->shipment_fee;//邮费
	$orderState = $t->status;
	$paid = $t->amount;//买家已付金额，售前退款会变化(总金额包含运费)

	if ($t->payment_name == "货到付款") {
		$delivery_term = 2;
	}

	switch ($orderState) {
		case '10'://10 已关闭 ,交易自动取消(未付款情况)
			$trade_status = 90;
			$receivable = 0;
			$paid = 0;
			// $received = 0;
			break;
		case '11'://11 已关闭 ,买家自己取消订单(未付款情况)
			$trade_status = 90;
			$receivable = 0;
			$paid = 0;
			break;
		case '12'://12 已关闭 ,商家关闭（未付款情况）
			$trade_status = 90;
			$receivable = 0;
			$paid = 0;
			break;
		case '13'://13 已关闭 ,所有维权申请处理完毕
			$trade_status = 80;
			$receivable = 0;
			$paid = 0;
			break;
		case '19'://19 已下单 ,已经生成订单
			$process_status = 10;
			$paid = 0;
			break;
		case '20'://20 待付款
			$process_status = 10;
			$paid = 0;
			break;
		case '30'://30 待发货
			$process_status = 10;
			$trade_status = 30;
			if ($delivery_term == 1)//款到发货
			{
				$pay_status = 2;
				$receivable = 0;
			}
			break;
		case '31'://31 货到付款 待发货
			$process_status = 10;
			$trade_status = 30;
			$paid = 0;
			break;
		case '32'://32 上门自提，待取货
			logx("上门自提，不下载 订单号:{$tid}",$sid.'/TradeSlow');
			return true;
			break;
		case '40'://40 商家发货 买家待收货
			$process_status = 40;
			$trade_status = 50;
			if ($delivery_term == 1)
			{
				$pay_status = 2;
				$receivable = 0;
			}elseif ($delivery_term == 2) {
				$paid = 0;
			}
			break;
		case '50'://50 已完成 交易成功
			$process_status = 60;
			$trade_status = 70;
			if ($delivery_term == 1)
			{
				$pay_status = 2;
			} 
			$receivable = 0;
			break;
		default:
			logx ( "ERROR $sid invalid_trade_status $tid {$orderState}", $sid.'/TradeSlow','error' );
			break;
	}
	$trade_time = $t->created_at;//下单时间
	$pay_time = $t->pay_at;//支付时间
	$end_time = $t->finished_at;//交易结束时间
	$buyer_message = $t->memo;//买家备注
	$buyer_name = @$t->nickname;//买家姓名
	$remark = $t->remarks;//客服备注
	// $logistics_type = $t['logistics_type'];//物流类别，-1表示自由选择
	// $isSelfFetch = $t['is_selffetch'];//配送方式 0配送，1上门自提

	//收货信息
	$consigner_addr = $t->order_consigner_addr;
	$receiver_mobile = $consigner_addr->mobile;//收件人手机
	$receiver_name = $consigner_addr->consignee;//收件人姓名
	$receiver_country = $consigner_addr->country_name;//国家
	$receiver_province = @rrdProvince($consigner_addr->province_name);//省份
	$receiver_city = $consigner_addr->city_name;//城市
	$receiver_district = $consigner_addr->district_name;//地区
	$receiver_address = $consigner_addr->address;//地址，不包含省市区
	$receiver_zip = $consigner_addr->zipcode;//收件人邮编
	getAddressID ( $receiver_province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );
	$receiver_area = $receiver_province . " ".$receiver_city." ".$receiver_district;//省市区空格分隔

	///////////*********金额**********/////////////
	//子订单
	//分摊邮费和优惠
	$left_post = $post_amount;//邮费
	$left_share_discount = $discount;//优惠

	$package = $t->package;
	$allOrderArray = array();
	//package中商品个数
	for ($i=0; $i <count($package) ; $i++) { 
		$logistics_no = $package[$i]->logis_no;//发货后物流单号
		$orders = $package[$i]->order;
		$allOrderArray = array_merge($allOrderArray,$orders);
	}
	//order_goods中商品的个数
	$order_goods = $t->order_goods;
	$allOrderArray = array_merge($allOrderArray,$order_goods);
	
	$allOrderArray_count = count($allOrderArray);
	$order_count = count($allOrderArray);
	$goods_count = 0;

	for ($j=0; $j <count($allOrderArray) ; $j++) {
		$order = $allOrderArray[$j];
		$goods_id = $order->goods_id;
		$product_id = $order->product_id;
		$spec_name = $order->props;
		$goods_name = $order->goods_name;
		$order_num = $order->quantity;
		$order_price = $order->price;
		$order_discount = 0;
		if(!empty($t->son_order_ump))
		{
			$o_discounts = $t->son_order_ump;
			for ($od=0; $od < count($o_discounts); $od++)
			{
				$o_discount = $o_discounts[$od];
				if ($o_discount->goods_id == $goods_id && ($o_discount->product_id == $product_id))
				{
					$order_discount = $o_discount->amount;
				}
			}
		}
		$order_status = $trade_status;
		$order_refund_status = 0;
		//退款标记：0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
		if ($order->refund_status == 10 || $order->refund_status == 11 || $order->refund_status == 30 ) {
			$order_refund_status = 2;
			$trade_refund_status = 2;
		}elseif ($order->refund_status == 20) {
			$order_refund_status = 3;
			$trade_refund_status = 2;
		}elseif ($order->refund_status == 22) {
			$order_refund_status = 4;
			$trade_refund_status = 2;
		}elseif ($order->refund_status == 31) {
			$order_refund_status = 5;
			$trade_refund_status = 2;
			$order_status = 80;
		}elseif ($order->refund_status == 40 || $order->refund_status == 21) {
			$order_refund_status = 0;
		}elseif ($order->refund_status == 41) {
			$order_refund_status = 1;
		}
		$goods_count += $order-> quantity;
		
		$product_sn = $order->product_sn;
		$goods_fee = bcsub(bcmul($order_num, $order_price),$order_discount);
		
		if ($j == $allOrderArray_count - 1) {
			$share_post = $left_post;
			$goods_share_amount = $left_share_discount;
		}else{
			//分摊后的优惠
			$goods_share_amount = bcmul($discount, bcdiv($goods_fee, $goods_amount));
			$left_share_discount = bcsub($left_share_discount, $goods_share_amount);
			//分摊后的邮费
			$share_post = bcmul($post_amount, bcdiv($goods_fee, $goods_amount));
			$left_post = bcsub($left_post, $share_post);
		}

		$share_amount = bcsub($goods_fee, $goods_share_amount);
		//分摊后子订单价格,退款保持不变

		$order_paid = bcadd($share_amount, $share_post);

		$oid = $order->id;
		$order_list[] = array
		(
			'platform_id'=> 47,
			'shop_id' => $shopId,
			//交易编号
			'tid'=>$tid,
			//订单编号			
			'oid'=> $oid,
			'process_status' => $process_status,//处理订单状态
			'status'=> $order_status,
			'refund_status'=> $order_refund_status,//退款标记：0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
			'goods_id'=> $goods_id,//平台货品ID
			'spec_id'=>$product_id,//平台规格id
			//商家编码
			'goods_no'=> $product_sn,
			//规格商家编码
			'spec_no'=>'',
			//货品名
			'goods_name'=>iconv_substr(@$goods_name,0,255,'UTF-8'),
			//规格名	
			'spec_name'=>$spec_name,
			//数量
			'num'=>$order_num, 
			//商品单价			
			'price'=>$order_price,
			//优惠金额
			'discount'=>$order_discount,	
			'share_discount' => $goods_share_amount, 	//分摊优惠
			'share_amount'=>$share_amount,//分摊后子订单价格,退款保持不变
			'total_amount'=>$goods_fee,//总价格，不包含邮费\\n商品价格 * 商品数量 + 手工调整金额 - 订单优惠金额
			//分摊邮费
			'share_post'=>$share_post,
			//分摊优惠--相当于手工调价
			'paid'=>$order_paid,
			
			'created' => array('NOW()')
		);

	}

	$trade_list [] = array(
		'tid' => $tid,//订单号
		'platform_id' => 47,//平台id
		'shop_id' => $shopId,//店铺ID
		'process_status' => $process_status,//处理订单状态
		'trade_status' => $trade_status,//平台订单状态
		'refund_status'=>$trade_refund_status, 	//退货状态
		'pay_status' => $pay_status,

		'order_count' => $order_count,
		'goods_count' => $goods_count,//货品总数量，用于递交时检验

		'trade_time' => empty($trade_time)?"0000-00-00 00:00:00" : $trade_time,
		'pay_time' => empty($pay_time)?"0000-00-00 00:00:00" : $pay_time,
		'end_time'=>empty($end_time)?"0000-00-00 00:00:00" : $end_time,

		'buyer_message' => $buyer_message,
		'buyer_email' => '',
		'buyer_area' => '',
		'buyer_nick' => '',//买家帐号ID
		'buyer_name' => $buyer_name,

		'receiver_name' => $receiver_name,
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_area' => $receiver_area,
		'receiver_address' => $receiver_address,
		'receiver_zip' => $receiver_zip,
		'receiver_mobile' => $receiver_mobile,
		'receiver_telno' => '',
		'receiver_hash' => md5($receiver_province.$receiver_city.$receiver_district.$receiver_address.$receiver_mobile),//收件人的hash值；

		'logistics_type' => -1,

		'goods_amount' => $goods_amount,//货款,未扣除优惠,退款不变
		'post_amount' => $post_amount,
		'discount' => $discount,
		'receivable' => $receivable,//总金额
		'paid' => 2 == $delivery_term ? 0 : $paid,
		'received' => $paid,//已从平台收款的金额
		'cod_amount' => 2 == $delivery_term ? $paid : 0, //货到付款金额
		'dap_amount' => 2 == $delivery_term ? 0 : $paid, //款到发货金额
		'platform_cost' => 0,//平台费用

		'delivery_term' => $delivery_term,//
		'pay_id' => '',//平台支付订单ID,如支付宝的订单号
		'remark' => $remark,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,

		'created' => array('NOW()')
		);
	return true;

}





