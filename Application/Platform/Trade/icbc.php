<?php
require_once (ROOT_DIR . '/Trade/util.php');
require_once (TOP_SDK_DIR . '/icbc/icbcApiClient.php');

function icbcProvince($province)
{
	global $spec_province_map;
	if (empty ( $province ))
		return '';
	
	if (iconv_substr ( $province, - 1, 1, 'UTF-8' ) != '省')
	{
		$prefix = iconv_substr ( $province, 0, 2, 'UTF-8' );
		
		if (isset ( $spec_province_map [$prefix] ))
			return $spec_province_map [$prefix];
		
		return $province . '省';
	}
	
	return $province;
}

function downIcbcTradesDetail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$session = $trades->session;
	$tids = & $trades->tids; // 订单编号
	                         
	// API参数
	$icbcApi = new icbcApiClient ();
	$icbcApi->setApp_key ( $appkey );
	$icbcApi->setApp_secret ( $appsecret );
	$icbcApi->setAuth_code ( $session );
	$icbcApi->setMethod ( "icbcb2c.order.detail" );
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	$loop_count = 0;
	for($i = 0; $i < count ( $tids ); $i ++)
	{
		//防止订单量较大时执行超过2分钟，所以进行时钟重置
		$loop_count ++;
        if ($loop_count > 1)
                resetAlarm ();
		$tid = $tids [$i];
		$params ['order_ids'] = $tid;
		usleep(200000);//降低接口调用频率
		$retval = $icbcApi->sendByPost ( $params );

		if (API_RESULT_OK != icbcErrorTest ( $retval, $db, $shopid ))
		{
			$error_msg['info'] = $retval ['error_msg'];
			$error_msg['status'] = 0;
			logx ( "icbcDownloadTrade fail $tid 错误信息:{$error_msg['info']}", $sid . "/Trade" );
			return TASK_OK;
			// return TASK_SUSPEND;
		}
		
		
		if (! isset ( $retval ['body'] ['order_list'] ['order'] ))
		{
			$error_msg['info'] = '没获取到订单信息';
			$error_msg['status'] = 0;			
			logx ( "icbcDownloadTradeDetail fail $tid 错误信息:{$error_msg['info']}", $sid . "/Trade" );
			return TASK_OK;
		}
		$ret = $retval ['body'] ['order_list'] ['order'];
		if (! loadIcbcTradeImpl ( $db, $appkey, $appsecret, $trades, $ret, $trade_list, $order_list, $discount_list ))
		{
			continue;
		}
		
		++ $scan_count;
		
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

// 异步下载
function icbcDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
	$cbp = function (&$trades) use($trade_detail_cmd)
	{
		pushTask ( $trade_detail_cmd, $trades );
		return true;
	};
	
	return icbcDownloadTradeListImpl ( $db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp );
}

// 同步下载
// countLimit 订单数限制
function icbcSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';
	
	$cbp = function (&$trades) use($appkey, $appsecret, &$db, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
	{
		downIcbcTradesDetail ( $db, $appkey, $appsecret, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg );
		
		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;
		
		return ($scan_count < $countLimit);
	};
	
	return icbcDownloadTradeListImpl ( $db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp );
}

function icbcDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{
	$ptime = $end_time;
	if ($save_time)
	$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$session = $shop->session;
	logx ( "icbcDownloadTradeList $shopid start_time:" . date ( 'Y-m-d H:i:s', $start_time ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid . "/Trade" );
	
	$loop_count = 0;
	$total_count = 0;
	
	$icbcApi = new icbcApiClient ();
	$icbcApi->setApp_key ( $appkey );
	$icbcApi->setApp_secret ( $appsecret );
	$icbcApi->setAuth_code ( $session );
	$icbcApi->setMethod ( "icbcb2c.order.list" );
	
	$params = array();
	$tids_count = 0;	
	while ( $ptime > $start_time )
	{
		$ptime = ($ptime - $start_time > 3600 * 24) ? ($end_time - 3600 * 24 + 1) : $start_time; // 时间间隔待细分
		logx ( "icbcTradeList $shopid start_time:" . date ( 'Y-m-d H:i:s', $ptime ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid . "/Trade" );
		
		$loop_count ++;
		if ($loop_count > 1)
			resetAlarm ();
		$params ['modify_time_from'] = date ( 'Y-m-d H:i:s', $ptime );
		$params ['modify_time_to'] = date ( 'Y-m-d H:i:s', $end_time );
		$params ['order_status'] = ''; // 默认空为所有状态
		$retval = $icbcApi->sendByPost ( $params );
		if (API_RESULT_OK != icbcErrorTest ( $retval, $db, $shopid ))
		{
			$error_msg['info'] = $retval ['error_msg'];
			$error_msg['status'] = 0;
			logx ( "icbcDownloadTradeListImpl icbc->get fail error_msg:{$error_msg['info']}", $sid . "/Trade" );
			logx ( "ERROR $sid icbcDownloadTradeListImpl {$error_msg['info']}", $sid . "/Trade",'error' );
			return TASK_OK;
		}
		
		if (! isset ( $retval ['body'] ) || ! isset ( $retval ['body'] ['order_list'] ['order'] ))
		{
			$end_time = $ptime + 1;
			logx ( "icbcDownloadTradeListImpl $shopid count: 0", $sid . "/Trade" );
			continue;
		}
		$trades = $retval ['body'] ['order_list'] ['order'];
		if (empty ( $trades [0] ))
		{ // 只有抓到一个订单
			$tids = array();
			$tids [] = $trades ['order_id'];
			if (count ( $tids ) > 0)
			{
				$tids_count += count ( $tids );
				logx ( "icbcDownloadTradeList $shopid count: ".count($tids), $sid . "/Trade" );			
				$shop->tids = $tids;
				if (! $cbp ( $shop ))
					return TASK_SUSPEND;
			}
		}
		else
		{ // 抓到多个订单
			$tids = array();
			foreach ( $trades as $t )
				$tids [] = $t ['order_id'];
			
			if (count ( $tids ) > 0)
			{
				$tids_count += count ( $tids );
				logx ( "icbcDownloadTradeList $shopid count: ".count($tids), $sid . "/Trade" );					
				$shop->tids = $tids;
				if (! $cbp ( $shop ))
					return TASK_SUSPEND;
			}
		}
		$end_time = $ptime + 1;
	}
	logx ( "icbcDownloadTradeListTotal $shopid count: ".$tids_count, $sid . "/Trade" );
	

	if($save_time)
	{
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}
	return TASK_OK;
}

function loadIcbcTradeImpl(&$db, $appkey, $appsecret, $shop, &$t, &$trade_list, &$order_list, &$discount_list)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	
	$delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0; // 0未付款1部分付款2已付款
	$trade_refund_status = 0; // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0; // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; // 已付金额, 发货前已付
	$trade_status = 10; // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	$is_external = 0;
	$refund_amount = 0; // 退款金额
	
	$tid = ( string ) $t ['order_id']; // icbc订单编号
	$orderState = $t ['order_status']; // icbc订单状态 01:未付款 02:待发货 03:待确认收货 04:交易完成 05:交易取消 06:交易关闭
	$receivable = $t ['order_amount']; // 应付金额
	if ($orderState == '01')
	{
		return true;//未付款不下载
	}
	
	switch ($orderState)
	{
		case '01' : // 待付款
			{
				$process_status = 10;
				break;
			}
		case '02' : // 待发货
			{
				$process_status = 10;
				$trade_status = 30;
				$pay_status = 2;
				$paid = bcadd ( $t ['payment'] ['order_pay_amount'], bcadd($t ['order_credit_amount'] , $t ['order_coupon_amount']) ); // 已付金额 :支付金额+积分抵扣+电子券抵扣金额
				break;
			}
		case '03' : // 已发货(待确认收货)
			{
				$trade_status = 50;
				$pay_status = 2;
				$paid = bcadd ( $t ['payment'] ['order_pay_amount'], bcadd($t ['order_credit_amount'] , $t ['order_coupon_amount']) );
				break;
			}
		case '04' : // 交易完成
			{
				$trade_status = 70;
				$pay_status = 2;
				$paid = bcadd ( $t ['payment'] ['order_pay_amount'], bcadd($t ['order_credit_amount'] , $t ['order_coupon_amount']) );
				break;
			}
		case '05' : // 交易取消
			{
				$trade_status = 90;
				$paid = bcadd ( $t ['payment'] ['order_pay_amount'], bcadd($t ['order_credit_amount'] , $t ['order_coupon_amount']) );
				break;
			}
		case '06' : // 交易关闭
			{
				$trade_status = 90;
				$paid = bcadd ( $t ['payment'] ['order_pay_amount'], bcadd($t ['order_credit_amount'] , $t ['order_coupon_amount']) );
				break;
			}
		default :
			logx ( "invalid_trade_status $tid{$orderState}", $sid . "/Trade" );
			logx ( "ERROR $sid invalid_trade_status $tid {$orderState}", $sid . "/Trade", 'error' );
			break;
	}
	
	$total_discount = $t ['order_other_discount']; // 其他优惠总和(针对订单，icbc中单个商品优惠字段product_discount暂时无用)
	$discount = @$t['discounts']['discount'];
	if (!empty($discount)) 
	{	
		$num = count($t['discounts']['discount']);
		$discount_key = array_key_exists("discount_type",$discount);//对单个优惠的特殊判断
		if($discount_key)
		{
			if ($discount['discount_type'] == '08')
			{
				$total_discount += $discount['discount_amount'];
			}
		}else
		{
			for ($i=0; $i < $num; $i++) 
			{ 
				$d = $discount[$i];
				if ($d['discount_type'] == '08')
				{
					$total_discount += $d['discount_amount'];
				}	

			}
		}
	}
	$post_fee = ( float ) $t ['payment'] ['order_freight']; // 运费
	                                                        // 发票类别，0 不需要，1普通发票，2增值税发票
	
	$invoice = $t ['invoice']; // icbc 发票类别:1-增票 2-普通
	$invoice_type = 0;
	$invoice_title = '';
	$invoice_content = '';
	
	if (! empty ( $invoice ['invoice_type'] ))
	{
		if ($invoice ['invoice_type'] == 1)
		{
			$invoice_type = 2;
		}
		else if ($invoice ['invoice_type'] == 2)
		{
			$invoice_type = 1;
		}
		$invoice_title = trim ( $invoice ['invoice_title'] );
		$invoice_content = trim ( $invoice ['invoice_content'] );
	}
	
	$orderId = 1;
	$order_arr = array();
	$trade_share_discount = $total_discount; // 优惠的总金额
	
	$orders = &$t ['products'] ['product'];
	
	$left_post = $post_fee; // 邮费
	$left_share_discount = $trade_share_discount; // 折扣分摊
	
	$trade_fee = bcsub(bcadd($paid, $trade_share_discount), $post_fee); // 由于没有返回商品的优惠 订单总金额计算(应付+总优惠-运费)
	$goods_count = 0;
	$order_fee = 0; // 所有商品总金额
	
	$order_count = count ( $orders );
	
	$i = 0; // 遍历商品 下标标识
	$j = 0; // 遍历搭售商品 下标标示
	$s = 0;	// 统计搭售商品个数
	$tringproduct_flag = 0; // 搭售商品标识 0-搭售商品不存在 1-存在一种搭售商品 2-存在多种搭售商品
	while ( $i < $order_count )
	{
		switch ($tringproduct_flag)
		{
			case 0 : // 不存在搭售商品
			         
				if (($i == 0) && empty ( $orders [0] )) // 订单只有一种商品
				{
					
					$o = &$orders;
					$order_count = 1;
				}
				else // 订单有多种商品
				{
					
					$o = &$orders [$i];
				}
				break;
			case 1 : // 存在一种搭售商品
			         
				if ($order_count == 1)
				{
					$o = &$orders ['tringproducts'] ['tringproduct'];
				}
				else
				{
					$o = &$orders [$i] ['tringproducts'] ['tringproduct'];
				}
				$s++;
				break;
			case 2 : // 存在多种搭售商品
			         
				if ($order_count == 1)
				{
					$o = &$orders ['tringproducts'] ['tringproduct'] [$j];
				}
				else
				{
					$o = &$orders [$i] ['tringproducts'] ['tringproduct'] [$j];
				}
				$j ++;
				$s++;
				break;
		}
		$num = $o ['product_number']; // 商品数量
		$goods_count += $num;
		$price = $o ['product_price']; // 商品单价
		$order_fee += bcmul ( $price, $num );

		$order_refund_status = 0;
		$trade_refund_status = 0;
		
		$refund = @$o ['refund_process']; // 商品退款进度
		$refund_num = @$o ['refund_num']; // 商品退货数量

		if($refund == '已申请退款')
		{
			$trade_refund_status = 1;
			$order_refund_status = 2;
		}
		elseif ($refund == '退款处理中')
		{
			$trade_refund_status = 1;
			$order_refund_status = 2;
		}
		elseif ($refund == '退款完成')
		{
			$trade_refund_status = 3;
			$order_refund_status = 5;
		}
		
		$oid = $tid;
		if (isset ( $order_arr [$oid] ))
		{
			$oid = $oid . $orderId;
			++ $orderId;
		}
		$order_arr [$oid] = 1;
		
		$goods_fee = bcmul ( $price, $num ); // 一种商品的总金额
		
		if ($i == $order_count - 1) // 最后一种商品的 分摊邮费 和 分摊优惠
		{
			$share_post = $left_post;
			$goods_share_amount = $left_share_discount;
		}
		else
		{
			$share_post = bcdiv ( bcmul(( float ) $post_fee, ( float ) $goods_fee) , $trade_fee );
			$left_post = bcsub ( $left_post, $share_post );
			$goods_share_amount = bcdiv ( bcmul($trade_share_discount, ( float ) $goods_fee)  , $trade_fee );
			$left_share_discount = bcsub ( $left_share_discount, $goods_share_amount );
		}
		
		$share_amount = bcsub ( $goods_fee, $goods_share_amount ); // 一种商品优惠分摊后的总金额
		                                                           
		$spec_name = $o ['product_name']; // 规格名 暂时 和 商品名一致
		
		$goods_no = $o['product_code'];
		if(iconv_strlen($goods_no,'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$o['product_name']}",$sid . "/Trade", "error");
			$message = '';
			if(iconv_strlen($goods_no,'UTF-8')>40)
			{
				$message = "货品商家编码超过40个字符:{$goods_no}";
			}

			$msg = array(
					'type'=>10,
					'topic'=>'trade_deliver_fail',
					'distinct'=>1,
					'msg'=>$message
				);
			SendMerchantNotify($sid,$msg);
			$goods_no = iconv_substr($goods_no,0,40,'UTF-8');
		}
		
		$order_list [] = array( // api_trade_order
				'shop_id' => $shopId,			
				'platform_id' => 25,
				'tid' => $tid,
				'oid' => $oid,
				'status' => $trade_status,
				'refund_status' => $order_refund_status,
				'order_type' => 0,
				'invoice_type' => $invoice_type,
				'bind_oid' => '',
				'goods_id' => trim ( @$o ['product_id'] ), // 商品ID
				'spec_id' => trim ( @$o ['product_sku_id'] ), // 平台规格id
				'goods_no' => trim ( @$o ['product_code'] ), // 商家编码
				'spec_no' => '', // 规格商家编码
				'goods_name' => trim ( @$o ['product_name'] ), // 平台货品名
				'spec_name' => iconv_substr ( ( string ) $spec_name, 0, 40, 'UTF-8' ), // 平台规格名
				'refund_id' => '',
				'num' => $num,
				'price' => $price,
				'adjust_amount' => 0, // 手工调整,特别注意:正的表示加价,负的表示减价
				'discount' => 0, // 子订单折扣
				'share_discount' => $goods_share_amount, // 分摊优惠
				'total_amount' => $goods_fee, // 分摊前扣除优惠货款num*price+adjust-discount
				'share_amount' => $share_amount, // 分摊后货款num*price+adjust-discount-share_discount
				'share_post' => $share_post, // 分摊邮费
				'refund_amount' => 0,
				'is_auto_wms' => 0,
				'wms_type' => 0,
				'warehouse_no' => '',
				'logistics_no' => '',
				'paid' => bcadd($share_amount, $share_post),
				'created' => array(
						'NOW()' 
				) 
		);
		
		// icbc-response的orders中,其商品product含有搭售商品tringproducts属性,tringproducts含有商品的基本属性,即tringproducts也是订单中的商品,计入订单分摊中
		if (($order_count == 1 && empty ( $orders ['tringproducts'] )) || ($order_count > 1 && empty ( $orders [$i] ['tringproducts'] )))
		{ // 没有搭售商品
			$i ++;
			continue;
		}
		else
		{
			if (($order_count == 1 && empty ( $orders ['tringproducts'] ['tringproduct'] [0] )) || ($order_count > 1 && empty ( $orders [$i] ['tringproducts'] ['tringproduct'] [0] )))
			{ // 只含有一种搭售商品
				if ($tringproduct_flag == 1)
				{ // 已经遍历了该搭售商品，而且该搭售商品是最后一个，即进入下一个商品的遍历
					$tringproduct_flag = 0;
					$i ++;
				}
				else
				{
					$tringproduct_flag = 1;
				}
			}
			else
			{ // 含有多种搭售商品
				$tringproduct_flag = 2;
				if (($order_count == 1 && empty ( $orders ['tringproducts'] ['tringproduct'] [$j] )) || ($order_count > 1 && empty ( $orders [$i] ['tringproducts'] ['tringproduct'] [$j] )))
				{
					$j = 0;
					$tringproduct_flag = 0;
					$i ++;
				}
			}
		}
	}
	
	$cg = &$t ['consignee']; // 收货人信息 consignee
	
	$receiver_name = trim ( $cg ['consignee_name'] ); // 姓名
	
	$receiver_address = trim ( $cg ['consignee_address'] ); // 收货地址 （不包括省市区）
	$receiver_state = trim ( @$cg ['consignee_province'] ); // 省份
	$receiver_city = trim ( @$cg ['consignee_city'] ); // 城市
	$receiver_district = trim ( @$cg ['consignee_district'] ); // 区县
	                                                         

	$consigneePostcode = trim ( $cg ['consignee_zipcode'] ); // 邮编
	
	$receiver_mobile = trim ( @$cg ['consignee_mobile'] ); // 手机
	$receiver_phone = trim ( @$cg ['consignee_phone'] ); // 电话
	
	$buyer_area = $receiver_state . " " . $receiver_city . " " . $receiver_district;
	$receiver_state = icbcProvince ( $receiver_state );
	
	if (! empty ( $receiver_district ))
	{
		$receiver_area = "$receiver_state $receiver_city $receiver_district";
	}
	else
	{
		$receiver_area = "$receiver_state $receiver_city";
	}
	
	// 得到 省份ID 城市ID 区县ID
	getAddressID ( $receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );
	
	$logistics_type = - 1; // 未知物流
	$order_count = bcadd($order_count, $s);
	$trade_list [] = array( // api_trade
			'platform_id' => 25,
			'shop_id' => $shopId,
			'tid' => $tid,
			'trade_status' => $trade_status,
			'pay_status' => $pay_status,
			'pay_method' => 1, // 暂时不支持COD 支付方式: 1在线转帐 2现金，3银行转账，4邮局汇款 5预付款 6刷卡
			'refund_status' => $trade_refund_status,
			'process_status' => $process_status,
			'order_count' => $order_count,
			'goods_count' => $goods_count,
			'trade_time' => dateValue($t ['order_create_time']),
			'pay_time' => dateValue($t ['payment'] ['order_pay_time']),
			'buyer_nick' => valid_utf8 ( trim ( $t ['order_buyer_username'] ) ), // 买家ID
			'receiver_name' => $receiver_name,
			'receiver_address' => $receiver_address,
			'receiver_mobile' => iconv_substr ( $receiver_mobile, 0, 40, 'UTF-8' ),
			'receiver_telno' => iconv_substr ( $receiver_phone, 0, 40, 'UTF-8' ),
			'receiver_zip' => $consigneePostcode,
			'receivable' => ( float ) $paid, // 应收金额
			'buyer_email' => '',
			'buyer_area' => $buyer_area,
			'pay_id' => '',
			'pay_account' => '',
			'receiver_province' => $province_id,
			'receiver_city' => $city_id,
			'receiver_district' => $district_id,
			'receiver_area' => $receiver_area,
			'logistics_type' => $logistics_type,
			'invoice_type' => $invoice_type,
			'invoice_title' => @$invoice_title,
			'invoice_content' => @$invoice_content,
			'buyer_message' => valid_utf8(trim ( $t ['order_buyer_remark'] )),
			'remark' => valid_utf8(trim ( $t ['order_seller_remark'] )),
			'remark_flag' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'stockout_no' => '',
			'logistics_no' => '', // 物流单号
			'is_auto_wms' => 0,
			'is_external' => $is_external,
			'goods_amount' => $order_fee, // 货款,未扣除优惠,退款不变
			'post_amount' => $post_fee,
			'discount' => $total_discount,
			'paid' => 2 == $delivery_term ? 0 : $paid,
			'received' => $paid, // 已从平台收款的金额
			'platform_cost' => 0,
			'cod_amount' => 2 == $delivery_term ? ( float ) $paid : 0,
			'dap_amount' => 2 == $delivery_term ? 0 : ( float ) $paid,
			'refund_amount' => 0,
			'trade_mask' => 0,
			'score' => 0,
			'real_score' => 0,
			'got_score' => 0,
			'receiver_hash' => md5 ( $receiver_name . $receiver_area . $receiver_address . $receiver_mobile . $receiver_phone . '' ),
			'delivery_term' => $delivery_term,
			'created' => array(
					'NOW()' 
			) 
	);
	
	$discount = @$t['discounts']['discount'];
	if (!empty($discount)) 
	{	
		$num = count($t['discounts']);
		logx('优惠:'.print_r($t['discounts'],true).'个数:'.$num,$sid . "/Trade");
		if((int)$num == 1)
		{
			switch ($discount['discount_type']) 
			{
				case '03':
					$name = '人为优惠';
					break;
				case '06':
					$name = '人工优惠（运费）';
					break;
				case '07':
					$name = '包邮（满送活动）';
					break;	
				case '08':
					$name = '满送';
					break;
				case '09':
					$name = '满减';
					break;
				case '13':
					$name = '包邮（包邮活动)';
					break;	
			}

			$discount_list [] = array
			(
				'platform_id' => 25,
				'tid' => $tid,
				'oid' => $oid,
				'sn' => '',
				'type' => '',
				'name' => $name,
				'is_bonus' => 0,
				'detail' => '',
				'amount' => $discount['discount_amount'],
				'created' => array(
						'NOW()' 
				)
			);	
		}else
		{
			for ($i=0; $i < $num; $i++) 
			{ 
				$d = $discount[$i];
				switch ($d['discount_type']) 
				{
					case '03':
						$name = '人为优惠';
						break;
					case '06':
						$name = '人工优惠（运费）';
						break;
					case '07':
						$name = '包邮';
						break;	
					case '08':
						$name = '满送';
						break;
					case '09':
						$name = '满减';
						break;
					case '13':
					$name = '包邮（包邮活动)';
					break;	
				}

				$discount_list [] = array
				(
					'platform_id' => 25,
					'tid' => $tid,
					'oid' => '',
					'sn' => '',
					'type' => '',
					'name' => $name,
					'is_bonus' => 0,
					'detail' => '',
					'amount' => $d['discount_amount'],
					'created' => array(
							'NOW()' 
					)
				);	

			}
		}

		
	}
	return true;
}

?>