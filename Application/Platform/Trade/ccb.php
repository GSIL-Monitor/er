<?php
//建行
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Manager/utils.php');

require_once(TOP_SDK_DIR . '/ccb/ccbClient2.php');

function ccbProvince($province)
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

// 异步下载
function ccbDownloadTradeList(&$db,
                              $shop,
                              $start_time,
                              $end_time,
                              $save_time,
                              $trade_detail_cmd,
                              &$total_count,
                              &$error_msg)
{
	$cbp = function (&$trades) use($trade_detail_cmd)
	{
		pushTask ( $trade_detail_cmd, $trades );
		return true;
	};

	return ccbDownloadTradeListImpl($db, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp );
}

// 同步下载
// countLimit 订单数限制
function ccbSyncDownloadTradeList(&$db,
                                  $shop,
                                  $countLimit,
                                  $start_time,
                                  $end_time,
                                  &$scan_count,
                                  &$total_new,
                                  &$total_chg,
                                  &$error_msg)
{
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';

	$cbp = function (&$trades) use(&$db, $shop, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
	{
		ccbDownloadTradeDetail ( $db, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg );

		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;

		return ($scan_count < $countLimit);
	};
	return ccbDownloadTradeListImpl ( $db, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp );
}


function ccbDownloadTradeListImpl(&$db,
                                  $shop,
                                  $start_time,
                                  $end_time,
                                  $save_time,
                                  &$total_count,
                                  &$error_msg,
                                  $cbp)
{
	$ptime = $end_time;
	if ($save_time) $save_time = $end_time;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;

	logx("ccbDownloadTradeListImpl $shopid start_time:".date('Y-m-d H:i:s',$start_time)." end_time:".date('Y-m-d H:i:s',$end_time),$sid.'/Trade');
	$page_size = 50;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$loop_count = 0;

	//请求参数
	$client = new ccbClient();
	$client->setKey($shop->secret);

	$params = array();
	$params['head'] = array(
		'tran_code' => 'T0008',//T0008
		'cust_id' => $shop->key,//客户编号
		'tran_sid' => '',//流水号，相当于签名
	);

	while ($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
		$loop_count++;
		if($loop_count > 1) resetAlarm();

		$params['body'] = array(
			'order' => array(
				'start_update' => date("Y-m-d H:i:s",$ptime),
				'end_update' => date("Y-m-d H:i:s",$end_time),
				'start_created' => '',
				'end_created' => '',
				'status' => '',
				'page_no' => 1,
				'page_size' => $page_size,
			),
		);

		$retval = $client->execute($params);

		logx("ccb_query ".date("Y-m-d H:i:s",$ptime)." ".date("Y-m-d H:i:s",$end_time), $sid.'/Trade');
		logx('retval: '.print_r($retval,true), $sid.'/Trade');
		if(API_RESULT_OK != ccbErrorTest($retval, $db, $shopid))
		{
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval['error_msg'];
			logx("ERROR $sid ccbDownloadTradeList, error_msg: {$error_msg['info']}",$sid.'/Trade', 'error');
			return TASK_OK;
		}


		if(!isset($retval['body']['orders']['order']) || count($retval['body']['orders']['order']) == 0)
		{
			$end_time = $ptime + 1;
			logx("ccbDownloadTradeList $shopid count: 0", $sid.'/Trade');
			continue;
		}
		$trades = $retval['body']['orders']['order'];
		if(isset($trades['order_id'])){
			$tmp[0] = $trades;
			$trades = $tmp;
			unset($tmp);
			logx("ccbDownloadTradeListImpl $shopid One_trades_deal:".print_r($trades,true),$sid.'/Trade');
		}


		//总条数
		$total_result = intval($retval['body']['total_results_count']);
		logx("ccbDownloadTradeListImpl $shopid count: $total_result", $sid.'/Trade');

		if ($total_result <= $page_size)
		{
			$tids = array();
			for($j =0; $j < count($trades); $j++)
			{
				$tids[] = $trades[$j]['order_id'];
				$temp = $trades[$j]['order_id'];
				logx("ccb tid: {$temp}", $sid.'/Trade');
			}
			if(count($tids) > 0)
			{
				$shop->tids = $tids;
				if (!$cbp($shop))
					return TASK_SUSPEND;
			}
		}
		else
		{
			$total_pages = ceil(floatval($total_result)/$page_size);

			for($i=$total_pages; $i>0; $i--)
			{
				unset($params['body']);
				$params['body'] = array(
					'order' => array(
						'start_update' => date("Y-m-d H:i:s",$ptime),
						'end_update' => date("Y-m-d H:i:s",$end_time),
						'start_created' => '',
						'end_created' => '',
						'status' => '',
						'page_no' => $i,
						'page_size' => $page_size,
					),
				);

				$retval = $client->execute($params);

				if(API_RESULT_OK != ccbErrorTest($retval, $db, $shopid))
				{
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval['error_msg'];
					logx("ERROR $sid ccbDownloadTradeListImpl, error_msg: {$error_msg['info']}",$sid.'/Trade', 'error');
					return TASK_OK;
				}
				$trades = $retval['body']['orders']['order'];
				if(isset($trades['order_id'])){
					$tmp[0] = $trades;
					$trades = $tmp;
					unset($tmp);
					logx("ccbDownloadTradeListImpl $shopid One_trades_deal:".print_r($trades,true),$sid.'/Trade');
				}

				$tids = array();
				for($j =0; $j < count($trades); $j++)
				{
					$tids[] = $trades[$j]['order_id'];
					$temp = $trades[$j]['order_id'];
					logx("ccb tid: {$temp}", $sid.'/Trade');
				}
				if(count($tids) > 0)
				{
					$shop->tids = $tids;
					if (!$cbp($shop))
						return TASK_SUSPEND;
				}
			}
		}
		$end_time = $ptime + 1;
	}

	if($save_time)
	{
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}

	return TASK_OK;
}


function ccbDownloadTradeDetail(&$db,
                                $trades,
                                &$scan_count,
                                &$new_trade_count,
                                &$chg_trade_count,
                                &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$trade_list = array();
	$order_list = array();
	$discount_list = array();

	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids =$trades->tids;

	//请求参数
	$client = new ccbClient();
	$client->setKey($trades->secret);

	$params = array();
	$params['head'] = array(
		'tran_code' => 'T0007',
		'cust_id' => $trades->key,
		'tran_sid' => '',
	);

	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		if(empty($tid)){
			logx("ccbDownloadTradeDetail empty_tid:$tid",$sid.'/Trade');
			continue;
		}

		//参数
		$params['body'] = array(
			'order' => array(
				'order_id' => $tid,
			),
		);
		$retval = $client->execute($params);

		//logx("ccbDownloadTradeDetail shopid: $shopid ".print_r($retval,true) ,$sid);
		if(API_RESULT_OK != ccbErrorTest($retval, $db, $shopid))
		{
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval['error_msg'];
			logx("ccbDownloadTradeDetail ccb->execute fail 1, tid:$tid error_msg:{$error_msg['info']}", $sid.'/Trade');
			return TASK_SUSPEND;
		}
		if(!isset($retval['body']['order_items']) || empty($retval['body']['order_items']))
		{
			$error_msg['status'] = 0;
			$error_msg['info'] = '没有获取到订单信息';
			logx("ccbDownloadTradeDetail ccb->execute fail 2, tid:$tid error_msg:{$error_msg['info']}", $sid.'/Trade');
			return TASK_SUSPEND;
		}

		if(!loadCcbTrade($db, $trades, $trade_list, $order_list,$discount_list, $retval['body']['order_items']['order_info']))
		{
			continue;
		}

		$scan_count++;

		if(count($trade_list) >= 100)
		{
			if(!putTradesToDb($db, $trade_list, $order_list,$discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				releaseDb($db);
				return TASK_SUSPEND;
			}
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


function loadCcbTrade(&$db,$trades, &$trade_list, &$order_list, &$discount_list, &$retval)
{
	$sid = $trades->sid;
	$shopid = $trades->shop_id;

	$delivery_term = 1;         // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0;            // 0未付款1部分付款2已付款
	$trade_refund_status = 0;   // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	//$paid = 0;                  // 已付金额, 发货前已付
	$trade_status = 10;         // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70;       // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消

	$t = &$retval;
	$tid = ( string ) $t['order_id']; // 订单编号
	$orderState = $t['status'];
	// 应付金额 = 已付金额 + 电子券金额
	$receivable = bcadd(floatval($t['order_pay_amt']),floatval($t['order_coupon']));

	/*if($t->is_close == 1 || $t->order_type == 1)//is_close 1表示货到付款订单，可关闭， 0表示不可关闭
	{
		$delivery_term = 2;
	}*/

	switch ($orderState)
	{
		case 'WAIT_BUYER_PAY' : // 等待买家付款
		{
			$process_status = 10;
			$pay_status = 0;
			break;
		}
		case 'WAIT_SELLER_SEND_GOODS' : // 待发货
		{
			$process_status = 10;
			$trade_status = 30;
			if($delivery_term = 1) $pay_status = 2;
			break;
		}
		case 'WAIT_BUYER_CONFIRM_GOODS': // 等待买家确认收货,即:卖家已发货
		{
			$trade_status = 50;
			if($delivery_term = 1) $pay_status = 2;
			break;
		}
		case 'TRADE_FINISHED' : // 订单完成
		{
			$trade_status = 70;
			break;
		}
		case 'TRADE_CLOSED' : // 订单关闭
		{
			$trade_status = 90;
			break;
		}
		case 'TRADE_CANCELLED' : // 交易取消
		{
			$trade_status = 90;
			break;
		}
		case 'ORDER_DELETED' : // 订单删除
		{
			$trade_status = 90;
			break;
		}
		case 'WAIT_SELLER_REFUND_SEND_GOODS' ://等待卖家退款,未发货
		{
			$trade_status = 30;
			$process_status = 10;
			$trade_refund_status = 1;
			$order_refund_status = 2;
			break;
		}
		case 'WAIT_SELLER_REFUND_CONFIRM_GOODS' ://等待卖家退款,已发货
		{
			$trade_status = 50;
			$trade_refund_status = 1;
			$order_refund_status = 2;
			break;
		}
		case 'PART_AFFIRM_PAY'://部分确认收款
		case 'PART_PAY' ://部分付款
		{
			$trade_status = 10;
			$pay_status = 1;
			break;
		}
		case 'PART_REFUND'://部分退款
		{
			$trade_refund_status = 2;
			$order_refund_status = 3;
			break;
		}
		default :
			logx ( "ERROR $sid invalid_trade_status $tid {$orderState}",$sid.'/Trade', 'error' );
			break;
	}

	//地址处理
	$receiver_address=substr(trim(@$t['shipping_info']['consignee_address']),9,-3);
	$province=ccbProvince(trim(@$t['shipping_info']['consignee_province']));
	$city=trim(@($t['shipping_info']['consignee_city']));
	$district=trim(@$t['shipping_info']['consignee_county']);
	$receiver_area=@$province .' '. @$city .' '. @$district;

	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

	//发票信息
	if(@$t['invoice_info']['is_invoice'] == 0)
	{
		$invoice_type = 0;//不需要发票
	}else{
		if(@$t['invoice_info']['invoice_type'] == 0)
		{
			$invoice_type = 1;//普通发票
		}
		else //(@$t['invoice_info']['invoice_type'] == 1)
		{
			$invoice_type = 2;//增值税发票
		}
	}


	$total_trade_fee = bcsub(floatval($t['order_prod_amt']),floatval($t['delivery_fee']));//订单总额-运费
	$post_fee = floatval($t['delivery_fee']);//邮费
	$discount_fee = floatval($t['merchant_discount']);//折扣 = 商户优惠金额(电子券不算做优惠)
	$trade_total_fee = $total_trade_fee;//商品总价=num*price

	//分摊邮费
	$left_post = $post_fee;
	$left_discount = $discount_fee;

	$orders_t = $t['product_items']['item']; //商品列表

	if(isset($orders_t['pro_id'])){//只有一个商品的时候 ，直接就是商品信息
		$orders[0] = $orders_t;
	}else{
		$orders = $orders_t;
	}

	$order_count = count($orders);
	$goods_count = 0;

	$oidMap = array();
	$orderId = 1;
	$goods_dicount_amount = 0;
	for($k=0; $k<count($orders); $k++)
	{
		$o = $orders[$k];

		$order_num = floatval($o['prod_buy_amt']);
		$order_price = bcadd(floatval($o['prod_price']),floatval($o['prod_discount']));

		$order_discount_money = bcmul(floatval($o['prod_discount']),$order_num);//平台折扣，不包含手工调整和分摊优惠
		$goods_dicount_amount = bcadd($goods_dicount_amount, $order_discount_money);

		$adjust_amount = floatval(0);
		$goods_dicount_amount = bcadd($goods_dicount_amount, $adjust_amount);

		$goods_count = bcadd($goods_count, $order_num);//计算总货品数量
		//货品金额=
		$goods_fee = bcadd(bcsub(bcmul($order_price, $order_num), $order_discount_money), $adjust_amount);

		if ($k == $order_count - 1){
			$share_post = $left_post;
			$share_discount = $left_discount;
		}else{
			$share_post = bcdiv(bcmul($post_fee, $goods_fee), $trade_total_fee);
			$left_post = bcsub($left_post, $share_post);

			$share_discount = bcdiv(bcmul($discount_fee, $goods_fee), $trade_total_fee);
			$left_discount = bcsub($left_discount, $share_discount);
		}

		$share_amount = bcsub($goods_fee, $share_discount);//商品总价(num*price) - 分摊折扣

		if(2 == $delivery_term)
		{
			$order_paid = 0;
		}
		else
		{
			$order_paid = bcadd($share_amount, $share_post);
		}

		$oid = $tid.':'.$o['pro_id'];//订单id和商品id组合
		$oid2 = iconv_substr($oid, 0, 40, 'UTF-8');
		if(isset($oidMap[$oid2]))
		{
			$oid2 = $tid.':'.$orderId;
			$orderId++;
		}
		$oidMap[$oid2] = 1;

		$order_list[] = array
		(
			"platform_id"=>36,
			"shop_id"=>$shopid,
			//交易编号
			"tid"=>$tid,
			//订单编号
			"oid"=>$oid2,
			//状态
			"status"=>$trade_status,
			//退款标记
			"refund_status"=>$order_refund_status,
			//平台货品id
			"goods_id"=>$o['pro_id'],
			//规格id
			"spec_id"=> $o['sku_id'],
			//商家编码
			"goods_no"=>$o['pro_id'],
			//规格商家编码
			"spec_no"=>$o['sku_id'],
			//货品名
			"goods_name"=>iconv_substr(@$o['prod_name'],0,255,'UTF-8'),
			//规格名
			"spec_name"=>iconv_substr(@$o['prod_desc'],0,100,'UTF-8'),
			//数量
			'num'=>$order_num,
			//商品单价
			'price'=>$order_price,
			//优惠金额
			'discount'=>$order_discount_money,
			//总价格，不包含邮费=商品价格 * 商品数量 + 手工调整金额 - 订单优惠金额
			'total_amount'=>$goods_fee,
			'share_amount'=>$share_amount,
			//分摊邮费
			'share_post'=>$share_post,
			'share_discount'=>$share_discount,
			//分摊优惠--相当于手工调价
			'adjust_amount'=>$adjust_amount,
			'paid'=>$order_paid,

			'created' => array('NOW()')
		);

		if(bccomp(floatval($o['prod_discount']), 0)){
			$discount_list[] = array
			(
				'platform_id' => 36,
				'tid' => $tid,
				'oid' => '',
				'sn' => '',
				'type' => '',
				'name' => '商品折扣',
				'is_bonus' => 0,
				'detail' => '',
				'amount' => floatval($o['prod_discount']),
				'created' => array('NOW()')
			);
		}
	}

	$trade_list[] = array
	(
		"tid"=>$tid,            				//订单号
		"platform_id"=>36,						//平台id
		"shop_id"=>$shopid,			        //店铺ID
		"process_status"=>$process_status, 	//处理订单状态
		"trade_status"=>$trade_status,			//平台订单状态
		"refund_status"=>$trade_refund_status,//退货状态
		'pay_status'=>$pay_status,

		'order_count'=>$order_count,
		'goods_count'=>$goods_count,

		"trade_time"=>dateValue(@str_replace("T", " ", @$t['order_time'] )), 	//下单时间
		'pay_time' => dateValue(@str_replace("T", " ", @$t['payment_time'] )),  //付款时间

		"buyer_message"=>iconv_substr(valid_utf8(@$t['order_memo']),0,1024,'UTF-8'), 	//买家购买附言
		"buyer_email"=>iconv_substr($t['buyer_email'],0,60,'UTF-8'),
		"buyer_area"=>'',   //iconv_substr($t->name,0,40,'UTF-8'),
		"buyer_nick"=>iconv_substr($t['buyer_id'],0,100,'UTF-8'),
		"buyer_name"=>iconv_substr($t['buyer_name'],0,40,'UTF-8'),

		"receiver_name"=>iconv_substr(valid_utf8($t['shipping_info']['consignee_name']),0,40,'UTF-8'),
		"receiver_province"=>$province_id,		//省份id
		"receiver_city"=>$city_id, 				//市id
		"receiver_district"=>$district_id, 		//地区id
		"receiver_area"=> iconv_substr($receiver_area,0,128,'UTF-8'),       	//省市区
		"receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
		"receiver_zip"=> trim(@$t['shipping_info']['consignee_zip']),		//邮编
		"receiver_mobile"=>trim(@$t['shipping_info']['consignee_mobile']), 			//手机
		"receiver_telno"=>trim(@$t['shipping_info']['consignee_phone']), 			//电话
		'to_deliver_time' => '',

		"receiver_hash" => md5(@$t['shipping_info']['consignee_name'].@$receiver_area.@$receiver_address.@$t['shipping_info']['consignee_mobile'].@$t['shipping_info']['consignee_zip']),
		"logistics_type"=>-1,					//创建交易的物流方法$t->shipping_type

		'goods_amount'=>$trade_total_fee,//商品总价格num*price
		'post_amount'=>$post_fee,
		'discount'=>$discount_fee,
		'receivable'=>$receivable,//应付金额
		'paid'=> 1 == $delivery_term ? $receivable : 0,
		'received'=> 1 == $delivery_term ? $receivable : 0,

		'platform_cost'=>0,

		'invoice_type'=> $invoice_type,
		'invoice_title'=> @$t['invoice_info']['invoice_title'],

		"delivery_term"=>$delivery_term, 		//是否货到付款
		"pay_id"=>'', 							//支付宝账号
		"remark"=>'',//iconv_substr(valid_utf8(@$t->express_note),0,1024,'UTF-8'), 				//卖家备注
		"remark_flag"=>0, 	//星标int

		'cod_amount' => 2 == $delivery_term ? $receivable : 0,
		'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
		'refund_amount' => 0,//floatval(@$t->buyer_refund_fee),
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,

		'created' => array('NOW()')
	);
	if(bccomp(floatval($t['order_coupon']), 0)){
		$discount_list[] = array
		(
			'platform_id' => 36,
			'tid' => $tid,
			'oid' => '',
			'sn' => '',
			'type' => '',
			'name' => '电子券优惠',
			'is_bonus' => 0,
			'detail' => '算作已付金额，不算做优惠',
			'amount' => floatval($t['order_coupon']),
			'created' => array('NOW()')
		);
	}

	if(bccomp(floatval($t['merchant_discount']), 0)){
		$discount_list[] = array
		(
			'platform_id' => 36,
			'tid' => $tid,
			'oid' => '',
			'sn' => '',
			'type' => '',
			'name' => '用户优惠',
			'is_bonus' => 0,
			'detail' => '',
			'amount' => floatval($t['merchant_discount']),
			'created' => array('NOW()')
		);
	}

	return true;

}