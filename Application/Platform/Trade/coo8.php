<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/coo8/Coo8Client.php');

function coo8Province($province)
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
//抓取单条订单
function downcoo8TradesDetail($db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = & $trades->tids;
	$coo8 = new Coo8Client();
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		$params = array(
			'venderId' => $trades->account_nick,
			'method' => 'coo8.order.get',
			'orderId' => $tid
		);
		$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
		if(API_RESULT_OK != coo8ErrorTest($retval,$db,$shopid))
		{	
			$error_msg=array( 'status' => 0 , 'info' => "输入参数orderId长度必须在[10,11]位字符之间!");
			logx("downcoo8TradesDetail $shopid {$error_msg}", $sid . "/Trade");
			return TASK_OK;
		}
		if(!isset($retval->order))
		{	
			$error_msg=array( 'status' => 0 , 'info' => "订单不存在");
			logx("the order $tid not exist!", $sid . "/Trade");
			return TASK_OK;
		}
		if(!loadCoo8Trade($shopid, $db, $trade_list, $order_list, $retval->order, $new_trade_count, $chg_trade_count, $error_msg, $sid, $discount_list))
		{
			logx("loadCoo8Trade error in downcoo8TradesDetail", $sid . "/Trade");
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
function coo8DownloadTradeList(&$db,$appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{	
	$ptime = $end_time;
	$loop_count = 0;
	$page_size = 40;
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	$total_pages=1;
	if($save_time) 
	{
		$save_time = $end_time;
	}
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	logx("Coo8DownloadShop $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");
	$coo8 = new Coo8Client();

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	while($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
		$loop_count++;
		if($loop_count > 1) resetAlarm();
		$params = array(
			'venderId' => $shop->account_nick,
			'method' => 'coo8.orders.get',
			'startDate' => date('Y-m-d H:i:s', $ptime),
			'endDate' => date('Y-m-d H:i:s', $end_time),
			'pageSize' => $page_size,
			'pageNo' => $total_pages
		);
	    
		$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
		if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shopid))
		{
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] = 0;
			logx("ERROR $sid coo8DownloadTradeList". $error_msg['info'],  $sid  . "/Trade",'error');
			return TASK_OK;
		}
		logx("Coo8DownloadShop $shopid start_time:" . 
		date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");
		if(!isset( $retval->orders->order) || count( $retval->orders->order) == 0)
		{
			$end_time = $ptime + 1;
			logx("coo8DownloadTradeList $shopid count: 0", $sid . "/Trade");
			continue;
		}
		$trades = $retval->orders->order;

		$total_results = intval($retval->total_result);
		logx("Coo8Trade $shopid count: $total_results", $sid . "/Trade");

		if($total_results <= $page_size)
		{
			for($j =0; $j < count($trades); $j++)
			{
			   $t=$trades[$j];			   
			   if(!loadCoo8Trade($shopid, $db, $trade_list, $order_list, $t, $new_trade_count, $chg_trade_count, $error_msg, $sid, $discount_list))
				{
				
					continue;
				}
			}
		}
		else 
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			for($i=$total_pages; $i>=1; $i--)
			{
			    $params['pageNo'] =$i ;
				$retval = $coo8->sendByPost(COO8_API_URL, $params, $appsecret);
				if(API_RESULT_OK != coo8ErrorTest($retval, $db, $shopid))
				{
					$error_msg['info'] = $retval->error_msg;
					$error_msg['status'] = 0;
					logx("ERROR $sid coo8DownloadTradeList ".$error_msg['info'],  $sid . "/Trade",'error');
					return TASK_OK;
				}
					
				$trades = $retval->orders->order;

			    for($j =0; $j < count($trades); $j++)
			    {
			       $t=$trades[$j];	
									   
					if(!loadCoo8Trade($shopid, $db, $trade_list, $order_list, $t, $new_trade_count, $chg_trade_count, $error_msg, $sid, $discount_list))
					{
						continue;
					}
	
					if(count($order_list) >= 100)
					{
						
						if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
						{
							
							return TASK_SUSPEND;
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
			
			return TASK_SUSPEND;
		}
	}
	if($save_time)
	{
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 coo8平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}

	return TASK_OK;
}

function loadCoo8Trade($shopid, &$db, &$trade_list, &$order_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid, &$discount_list)
{
	$tid = $t->order_id;
	$favourable_money =bcadd(floatval($t->coupon_value),floatval($t->part_discount_price) );
	$receiver_mobile = @$t->consignee->mobilephone;		//手机
	$receiver_name = @$t->consignee->name;		//姓名
	$receiver_phone = @$t->consignee->telephone;	//电话
	$receiver_address = @$t->consignee->address;//收货地址
	$province=coo8Province(trim(@$t->consignee->province));
	$city=trim(@$t->consignee->city);	
	$district =trim(@$t->consignee->county);
	$receiver_area =@$t->consignee->province . " " . @$t->consignee->city . " " . @$t->consignee->county;
	$remark = @$t->opinion_desc;

	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);
	
	if($receiver_phone == '--') $receiver_phone = '';
	//物流类别
	$logistics_type = -1;

	if(@$t->tracking_company == 'EMS')
		$logistics_type = 3;

	$trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
	
	$process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	//trade退款状态
	$trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
	//order退款状态
	$order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
	
	/*****coo8平台订单状态*********
	* PR：初始	PP：订单处理中	EX：已出库	DL：已妥投
	* CWS：客服取消-待商家确认	CWC：商家申请取消
	* DFC：发货失败取消	RCC：客服取消	RV：拒收	RT：拒收已退回库房
	*******************************/
	
	if($t->status == 'PR' || $t->status == 'PP')
	{
		$trade_status = 30;
		$process_status = 10;
	}
	else if($t->status == 'CWC')
	{
		$trade_status = 30;
		$process_status = 10;
		$trade_refund_states = 1;
		$order_refund_status = 2;
	}
	else if($t->status == 'EX')
	{
		$trade_status = 50;
	}
	else if($t->status == 'DFC' || $t->status == 'RCC' || $t->status == 'RV' || $t->status == 'RT')
	{
		$trade_status = 50;
		$trade_refund_states = 1;
		$order_refund_status = 2;
	}
	else if($t->status == 'DL')
	{
		$trade_status = 70;
	}
	else if($t->status == 'CWS')
	{
		$trade_status = 80;
	}
	else
	{
		logx("ERROR $sid invalid_trade_status $tid {$t->status}",$sid . "/Trade", 'error');
	}
	
	$delivery_term = 1;
	$pay_status = 0;
	$pay_type = $t->pay_type;
	if($pay_type==1)//在线支付
	{
		$pay_status = 2;
		$delivery_term = 1;
		$paid = $t->payment;
	}
	else if($pay_type==2)//货到付款
	{
		$pay_status = 0;
		$delivery_term = 2;
		$paid = 0;
	}
	//发票类型
	$invoice_type = $t->consignee->invoice;
	$invoiceTitle = $t->consignee->invoice_title;
	$invoice_content =&$t->consignee->invoice_details->invoice_detail['0']->goods_name;

	if($invoice_type == '0')
	{
		$invoiceTitle = '';
		$invoice_content = '';
	}

	$goods_count = 0;
	$nick = 'COO' . $t->userid;
	$orders = &$t->order_details->order_detail;
	$left_post = $t->freight_price;
	$order_count = count($orders);
	$order_total_price = bcsub(bcadd($t->payment,$favourable_money),$t->freight_price);
	for($i=0; $i<count($orders); $i++)
	{	
		$o = & $orders[$i];
		$goods_no = trim(@$o->outId);
		if(iconv_strlen($goods_no, 'UTF-8')>40)	
		{
			logx("$sid GOODS_SPEC_NO_EXCEED\t{$goods_no}\t".@$o->item_name,$sid . "/Trade", 'error');
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

		$favourable_money_order =bcadd(floatval($o->coupon_value),floatval($o->part_discount_price));
		$num = $o->count;
		$goods_count += (int)$num;
		$price = $o->price;
		$total_fee = bcsub(bcmul(floatval($price),$num),floatval($favourable_money_order));		
		if ($i == $order_count - 1)
		{
			$share_post = $left_post;
		}
		else
		{
			$share_post = bcdiv(bcmul($t->freight_price, $total_fee), $order_total_price);
			$left_post = bcsub($left_post, $share_post);
		}
		$order_paid = bcadd($share_post ,$total_fee);
		//trade里的优惠是order优惠的总和，也就是总优惠。

		$order_list[] = array
		(
			"rec_id"=>0,
			'shop_id' => $shopid,
			"platform_id"=>8,
			"tid"=>$tid,					//交易编号
			"oid"=>'CO' . $tid . ':' . $o->main_id . ':' . $o->item_id,//订单编号
			"status"=>$trade_status,			//状态
			"refund_status"=>$order_refund_status,
			"goods_id"=>$o->main_id,			//平台货品id   
			"spec_id"=>'',							//规格id
			"goods_no"=>$goods_no,				//商家编码 
			"spec_no"=>'',							//规格商家编码  
			"goods_name"=>iconv_substr(@$o->item_name,0,255,'UTF-8'),			//货品名   
			"spec_name"=>'',						//规格名
			'num'=>$num, 							//数量
			'price'=>$price, 						//商品单价
			'discount'=>$favourable_money_order,	//优惠金额
			'total_amount'=>$total_fee,
			'share_amount' => $total_fee,
			'share_post'=>$share_post,				//分摊邮费
			'paid'=> 2 == $delivery_term ? 0 : $order_paid,
			'created' => array('NOW()')
		);

		if(bccomp($o->part_discount_price, 0))
		{
		
			$discount_list[] = array
			(
				'platform_id' => 8,
				'tid' => $tid,
				'oid' =>$o->main_id,
				'sn' => '',
				'type' => '',
				'name' => '店铺优惠（满减或店铺优惠券)',
				'is_bonus' => 0,
				'detail' => '',
				'amount' => $o->part_discount_price,
				'created' => array('NOW()')
			);
		}
	}

	$trade_list[] = array
	(
		"tid"=>$tid,								//订单号
		"platform_id"=>8,							//平台id
		"shop_id"=>$shopid,							//店铺ID
		"process_status"=>$process_status, 			//处理订单状态
		"trade_status"=>$trade_status,				//平台订单状态
		"refund_status"=>$trade_refund_states, 		//退货状态
		'order_count'=>$order_count,
		'goods_count'=>$goods_count,				
		"trade_time"=> dateValue($t->order_time), 	//下单时间
		"pay_time"=>dateValue($t->pay_time),		//付款时间
		"buyer_nick"=>iconv_substr($nick,0,100,'UTF-8'),
		"buyer_message"=>iconv_substr(@$t->customer_remark,0,1024,'UTF-8'),	//买家购买附言
		"buyer_email"=>iconv_substr(@$t->consignee->email,0,60,'UTF-8'),
		"buyer_area"=>'',
		"receiver_name"=>iconv_substr(@$t->consignee->name,0,40,'UTF-8'),
		"receiver_province"=>$province_id,			//省份id
		"receiver_city"=>$city_id, 					//市id
		"receiver_district"=>$district_id, 			//地区id
		"receiver_area"=>iconv_substr(@$receiver_area,0,64,'UTF-8'),			//省市区
		"receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
		"receiver_zip"=>@$t->consignee->post,		//邮编
		"receiver_mobile"=>iconv_substr($receiver_mobile,0,40,'UTF-8'), 		//手机
		"receiver_telno"=>iconv_substr($receiver_phone,0,40,'UTF-8'),			//电话
		'to_deliver_time' =>$t->consignee->want_send_time,
		'pay_status' =>	$pay_status,	
		"receiver_hash" => md5(@$t->consignee->name.$receiver_area.@$receiver_address.$receiver_mobile.@$receiver_phone.@$t->consignee->post),
		"logistics_type"=> $logistics_type,
		"warehouse_no"=>'',
		'goods_amount'=>$order_total_price,
		'post_amount'=>$t->freight_price,
		'discount'=>$favourable_money ,
		'receivable'=>$t->payment,					//应收金额
		'paid'=>$paid,								//买家已付金额
		'platform_cost'=>0,
		'invoice_type'=>$invoice_type,
		'invoice_title'=>iconv_substr($invoiceTitle,0,255,'UTF-8'),
		'invoice_content'=>iconv_substr($invoice_content,0,255,'UTF-8'),		
		"delivery_term"=>$delivery_term, 			//是否货到付款
		"pay_id"=>'', 								//支付宝账号
		"remark"=>$remark, 								//卖家备注
		"remark_flag"=>0, 							//星标
		'cod_amount' => 2 == $delivery_term ? @$t->payment : 0, //货到付款金额
		'dap_amount' => 2 == $delivery_term ? 0 : @$t->payment, //款到发货金额
		'refund_amount' => 0,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		'created' => array('NOW()')
	);
	return true;
}
