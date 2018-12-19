<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/ccjpt/ccjptClient.php');
require_once(ROOT_DIR . '/Manager/utils.php');


function ccjptProvince($province)
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

function ccjptCity($city)
{
	global $g_city_map;
	if(empty($city)) return '';
	
	if(iconv_substr($city, -1, 1, 'UTF-8') != '市')
	{
		$prefix = iconv_substr($city, 0, 2, 'UTF-8');
		
		if(isset($g_city_map[$prefix]))
			return $g_city_map[$prefix];
		
		return $city . '市';
	}
	
	return $city;
}

function ccjptDownloadTradeList(&$db, $appkey, $appsecret, &$shop, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$trade_list = array();
	$order_list = array();
	$discount_list = array();

	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;

	$ptime = $end_time;
	if($save_time) {
		$save_time = $end_time;
	}
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	logx("ccjptDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');

	$loop_count = 0;
	$page = 1;
	$page_size = 50;

	$ccjpt = new ccjptClient;
	$ccjpt->app_key = $appkey;
	$ccjpt->method = "orderList";
	$ccjpt->appsecret = $appsecret;

	while($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24) ? ($end_time - 3600 * 24 + 1) : ($start_time);
		$loop_count++;
		if($loop_count > 1) 
		{
			resetAlarm();
		}
		
		$params = array(
		'start_time' => $ptime,
		'end_time' => $end_time,
		'page' => $page,
		'page_size' => $page_size
		);
	
		$retval=$ccjpt->excute($params);
		if(API_RESULT_OK != ccjptErrorTest($retval, $db, $shopid))
		{
			$error_msg = $retval->msg;
			logx("ERROR $sid ccjptDownloadTradeList retval fail:{$error_msg}", $sid.'/TradeSlow');
			return TASK_OK;
		}

		if ($retval->data->record_count == 0) {
			$end_time = $ptime + 1;
			logx ( "ccjptDownloadTradeList $shopid count: 0", $sid.'/TradeSlow' );
			continue;
		}
		
		//总条数
		$total_results = intval(@$retval->data->record_count);
		$trades = $retval->data->orders;
		
		logx("ccjptTrade $shopid count: $total_results", $sid.'/TradeSlow');
		
		if ($total_results <= count($trades))
		{
			$total_trade_count += count($trades);
			for($j =0; $j < count($trades); $j++)
			{
				$t = $trades[$j];
				if(!loadCcjptTrade($sid, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
				{
					return TASK_OK;
				}
			}
		}
		else
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			for($i=$total_pages; $i>0; $i--)
			{
				$params['page'] = $i;
				$retval=$ccjpt->excute($params);
				
				if(API_RESULT_OK != ccjptErrorTest($retval, $db, $shopid))
				{
					$error_msg = $retval->msg;
					logx("ERROR $sid ccjptDownloadTradeList retval fail:{$error_msg}", $sid.'/TradeSlow','error');
					return TASK_OK;
				}

				$trades = $retval->data->orders;
				$total_trade_count += count($trades);
		
				for($j =0; $j < count($trades); $j++)
				{
					$t = $trades[$j];
					
					if(!loadCcjptTrade($sid, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
					{
						return TASK_OK;
					}
					if(count($order_list) >= 100)
					{
						if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
						{
							return TASK_OK;
						}
					}
				}
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
	
	if($save_time)
	{
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 楚楚街拼团平台 '.print_r($save_time,true),$sid. "/default");
	}
	
	return TASK_OK;
}



function downCcjptTradesDetail($db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;

	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = & $trades->tids;
	
	
	$ccjpt = new ccjptClient;
	$ccjpt->app_key = $appkey;
	$ccjpt->method = "orderList";
	$ccjpt->appsecret = $appsecret;

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	$tid = $tids[0];

	$params['order_sn'] = $tid;
	
	$retval=$ccjpt->excute($params);
	logx('ret: '.print_r($retval,true), $sid.'/TradeSlow');

	if(API_RESULT_OK != ccjptErrorTest($retval, $db, $shopid))
	{
		$error_msg = $retval->msg;
		logx("ERROR $sid downCcjptTradesDetail fail:{$error_msg}", $sid.'/TradeSlow','error');
		return TASK_OK;
	}

	$t = $retval->data->orders[0];

	if(!loadCcjptTrade($sid, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
	{
		return TASK_SUSPEND;
	}

	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
}


function loadCcjptTrade($sid, $shopid, &$db, &$trade_list, &$order_list, &$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$tid = $t->order_sn;

	$process_status = 70;//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$trade_status = 10;//订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭(付款前取消)
	$pay_status = 0;//0未付款1部分付款2已付款
	$delivery_term = 1;//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$trade_refund_status = 0;//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;// 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功

	$total_fee = $t->total_fee;//订单金额
	$pay_id = $t->pay_id;
	$paid = $total_fee;

	//组团中和已失败的不抓取
	if (intval($t->team_status) == 1 || intval($t->team_status) == 3) {
		return true;
	}

	//付款状态
	switch (intval($t->pay_status)) {
		case 0://未付款
		case 1://付款中
			$pay_status = 0;
			$paid = 0;
			break;
		case 2://已付款
			$pay_status = 2;
			break;
		case 3://已退款
			$trade_refund_status = 3;
			$order_refund_status = 5;
			$process_status = 70;
			$trade_status = 80;
			break;
		default:
			logx("ERROR $sid 订单付款状态错误{$tid}{$t->pay_status}",$sid.'/TradeSlow', 'error');
			break;
	}

	 //订单状态
	switch (intval($t->order_status)) {
		case 0://未确认
		case 3://无效 -----未付款 未发货都是无效订单
			$process_status = 10;
			$trade_status = 10;
			break;
		case 1://已确认
			$process_status = 10;
			//$trade_status = 30;
			break;
		case 2://已取消
			$process_status = 70;
			$trade_status = 90;
			break;
		case 4://退货或已退款
			$process_status = 70;
			$trade_status = 80;
			break;
		case 8://已完成
			$process_status = 60;
			$trade_status = 70;
			break;
		default:
			logx("ERROR $sid 订单状态错误{$tid}{$t->order_status}",$sid.'/TradeSlow','error');
			break;
	}

	switch (intval($t->shipping_status)) {
		case 1://已发货
			$process_status = 40;
			$trade_status = 50;
			break;
		case 2://已收货
			$process_status = 60;
			$trade_status = 60;
			break;
		default:
			break;
	}

	//可发货
	if (intval($t->shipping_status) == 0 && intval($t->pay_status) == 2 && intval($t->order_status) == 1 && (intval($t->order_type) == 1 || intval($t->order_type) == 2 || intval($t->order_type) == 9) && (intval($t->team_status) == 0||intval($t->team_status) == 2)) {
		$process_status = 10;
		$trade_status = 30;
	}

	
	$trade_time = date('Y-m-d H:i:s',$t->add_time);
	$pay_time = date('Y-m-d H:i:s', $t->pay_time);
	$receiver_name = @$t->consignee;
	$receiver_province = ccjptProvince(@$t->province);
	$receiver_city = ccjptCity(@$t->city);
	$receiver_district = @$t->district;
	getAddressID ( $receiver_province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );
	$receiver_area = $receiver_province . " ".$receiver_city." ".$receiver_district;//省市区空格分隔
	$receiver_address = @$t->address;
	$receiver_mobile = iconv_substr(@$t->mobile,0,40,'UTF-8');


	$orders = $t->goods_info;
	$order_count = count($orders);
	$goods_count = 0;

	for ($i=0; $i < count($orders); $i++) { 
		$order = $orders[$i];
		$oid = $t->order_id.".".$order->goods_id;
		$goods_id = $order->goods_id;
		$goods_no = $order->goods_sn;
		$goods_name = $order->goods_name;
		$num = $order->goods_number;
		$goods_count += $num;
		$price = $order->goods_price;
		$total_amount = $num * $price;

		$order_list[] = array
		(
			'platform_id'=> 53,
			'shop_id' => $shopid,
			//交易编号
			'tid'=>$tid,
			//订单编号			
			'oid'=> $oid,
			'process_status' => $process_status,//处理订单状态
			'status'=> $trade_status,
			'refund_status'=> $order_refund_status,//退款标记：0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
			'goods_id'=> $goods_id,//平台货品ID
			'spec_id'=>'',//平台规格id
			//商家编码
			'goods_no'=> iconv_substr(@$goods_no,0,40,'UTF-8'),
			//规格商家编码
			'spec_no'=> @$order->goods_code,
			//货品名
			'goods_name'=>iconv_substr(@$goods_name,0,255,'UTF-8'),
			//规格名	
			'spec_name'=>'',
			//数量
			'num'=>$num, 
			//商品单价			
			'price'=>$price,
			//优惠金额
			'discount'=>0,//针对单个商品的优惠
			'share_discount' => 0,//分摊优惠
			'share_amount'=>$total_amount,//分摊后子订单价格 单价*数量-分摊优惠 
			'total_amount'=>$total_amount,//总价格=商品价格 * 商品数量
			'share_post'=>0,//分摊邮费
			'paid'=>2 == $pay_status ? $total_amount : 0,//分摊已付金额+运费
			'created' => array('NOW()')
		);
	}



	$trade_list [] = array(
		'tid' => $tid,//订单号
		'platform_id' => 53,//平台id
		'shop_id' => $shopid,//店铺ID
		'process_status' => $process_status,//处理订单状态
		'trade_status' => $trade_status,//平台订单状态
		'refund_status'=>$trade_refund_status, 	//退货状态
		'pay_status' => $pay_status,

		'order_count' => $order_count,
		'goods_count' => $goods_count,//货品总数量，用于递交时检验

		'trade_time' => empty($trade_time) ? "0000-00-00 00:00:00" : $trade_time,//下单时间
		'pay_time' => empty($pay_time) ? "0000-00-00 00:00:00" : $pay_time,
		'end_time' => "0000-00-00 00:00:00",
		'buyer_nick' => '',
		'buyer_name' => '',
		'buyer_message' => iconv_substr(@$t->how_oos,0,1024,'UTF-8'),
		'buyer_email' => '',
		'buyer_area' => '',

		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_area' => $receiver_area,
		'receiver_address' => $receiver_address,
		'receiver_zip' => '',
		'receiver_mobile' => $receiver_mobile,
		'receiver_telno' => $receiver_mobile,
		'receiver_hash' => md5($receiver_province.$receiver_city.$receiver_district.$receiver_address.$receiver_mobile),//收件人的hash值；

		'logistics_type' => -1,//物流类别：自由选择

		'goods_amount' => $total_fee,//（单价*数量）之和
		'post_amount' => 0,
		'discount' => 0,
		'receivable' => $total_fee,//应收金额
		'paid' => $paid,
		'received' => $paid,//已从平台收款的金额
		'cod_amount' => 0, //货到付款金额
		'dap_amount' => $total_fee, //款到发货金额
		'delivery_term' => 1,//
		'pay_id' => $pay_id,//平台支付订单ID,如支付宝的订单号
		'remark' => iconv_substr(@$t->postscript,0,1024,'UTF-8'),//客服备注
		'trade_mask' => 0,
		'got_score' => 0,

		'created' => array('NOW()')
		);

	return true;
}







