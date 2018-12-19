<?php
require_once(ROOT_DIR .'/Trade/util.php');
require_once(TOP_SDK_DIR .'/mia/MiaClient.php');

function miaProvince($province)
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

function miaDownloadTradeList(&$db,$appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;

	if ($save_time) $save_time = $end_time;

	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	logx("miaDownloadTradeList $shopid start_time:".date('Y-m-d H:i:s',$start_time)."end_time:".date('Y-m-d H:i:s',$end_time),$sid.'/TradeSlow');
	//$end_time = $end_time -3600;
	$ptime = $end_time;
	$start_time = $start_time - 3600;
	logx("miaDownloadTradeList $shopid start_time:".date('Y-m-d H:i:s',$start_time)."end_time:".date('Y-m-d H:i:s',$ptime),$sid.'/TradeSlow');

	$page = 1;
	$page_size = 50;
	$loop_count = 0;

	$mia = new Mia();
	$mia->vendor_key = $appkey;
	$mia->secret_key = $appsecret;
	$mia->method = 'mia.orders.search';

	//应用参数
	$params = array(
			'order_state'	=> '1,2,4,5,6',
			'page'			=> $page,
			'page_size'		=> $page_size,
			'date_type'		=> 2
		);

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	while ($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24+1):$start_time;
		$loop_count++;
		if ($loop_count >1) resetAlarm();
		logx("miaDownloadTradeList $shopid start_time:".date('Y-m-d H:i:s',$ptime)."end_time:".date('Y-m-d H:i:s',$end_time),$sid.'/TradeSlow');
		$params['start_date'] = date('Y-m-d H:i:s',$ptime);
		$params['end_date'] = date('Y-m-d H:i:s',$end_time);
		$retval = $mia->execute($params);
		//logx(print_r($retval,true) ,$sid);
		if (API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
		{
			$error_msg = $retval->msg;
			logx("miaDownloadTradeList $shopid $error_msg", $sid.'/TradeSlow');
			return TASK_OK;
		}

		if (empty($retval->content->orders_list_response->order_list) )
		{
			$end_time = $ptime + 1;
			logx("miaDownloadTradeList $shopid count: 0", $sid.'/TradeSlow');
			continue;
		}

		//总条数
		$total_result = $retval->content->orders_list_response->total;
		logx("miaDownloadTradeList $shopid count : $total_result" ,$sid.'/TradeSlow');

		$trades = $retval->content->orders_list_response->order_list;

		if ($total_result <= $page_size)
		{
			for($j = 0; $j < count($trades); $j++)
			{
				$t = $trades[$j];
				if (!loadMiaTrade($db ,$appkey, $appsecret, $shop, $trade_list, $order_list,$discount_list, $t))
				{
					continue;
				}
			}
		}
		else
		{
			$total_pages = ceil(floatval($total_result)/$page_size);

			for($i = $total_pages; $i>0; $i--)
			{
				//请求参数
				$params['page'] = $i;
				$retval = $mia->execute($params);
				if (API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
				{
					$error_msg = $retval->msg;
					logx("miaDownloadTradeList $shopid $error_msg", $sid.'/TradeSlow');
					return TASK_OK;
				}

				$trades = $retval->content->orders_list_response->order_list;

				for($j =0; $j < count($trades); $j++)
				{
					$t = $trades[$j];
					$total_trade_count += 1;
					if(!loadMiaTrade($db ,$appkey, $appsecret, $shop, $trade_list, $order_list,$discount_list, $t))
					{
						continue;
					}
					if($countLimit && $total_trade_count >= $countLimit)
					{
						return TASK_SUSPEND;
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
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 mia平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}
	
	return TASK_OK;

}

function downMiaTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	$shopid = $trades->shop_id;

	$tids =$trades->tids;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
		
	$tid = $tids[0];

	$mia = new Mia();
	$mia->vendor_key = $appkey;
	$mia->secret_key = $appsecret;
	$mia->method = 'mia.order.get';

	$params = array(
		'order_id'	=> $tid
	);
	$retval = $mia->execute($params);

	//logx("downMiaTradesDetail shopid: $shopid ".print_r($retval,true) ,$sid);
	if (API_RESULT_OK != miaErrorTest($retval,$db,$shopid))
	{
		$error_msg['info'] = $retval->msg;
		$error_msg['status'] = 0;
		logx("downMiaTradesDetail $shopid $error_msg", $sid.'/TradeSlow');
		return TASK_OK;
	}

	if (empty($retval->content->order_response) )
	{
		$error_msg['info'] = '没获取到订单信息';
		$error_msg['status'] = 0;
		logx ( "downMiaTradesDetail fail $trade 错误信息:$error_msg", $sid.'/TradeSlow' );
		return TASK_OK;
	}
	$t =  $retval->content->order_response[0];
	if(!loadMiaTrade($db, $appkey, $appsecret, $trades, $trade_list, $order_list,$discount_list, $t))
	{
		return TASK_SUSPEND;
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
	
	
	return TASK_OK;
}

function loadMiaTrade(&$db, $appkey, $appsecret, $shop, &$trade_list, &$order_list,&$discount_list, &$t)
{
	global $g_mia_crossborder_sid;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;

	$crossborder = 0;
	if (in_array($sid, $g_mia_crossborder_sid))
	{
		$crossborder = 1;
	}

	$delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0; // 0未付款1部分付款2已付款
	$trade_refund_status = 0; // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0; // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; // 已付金额, 发货前已付
	$trade_status = 10; // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消

	$tid = $t->order_id;//订单编号
	$status = $t->order_state;//mia订单状态 1. 待付款 2. 已付款待发货 4. 发货完成 5. 订单完结 6. 已取消

	if (intval($t->pay_type) == 2)
	{
		$delivery_term = 2;
	}

	$paid = $t->pay_price; //实际支付金额（包含运费）

	switch (intval($status))
	{
		case 1://待付款
			{
				$process_status = 10;
				break;
			}
		case 2://已付款待发货
			{
				$process_status = 10;
				$trade_status = 30;
				if ($delivery_term == 1)
				{
					$pay_status = 2;
				}
				break;
			}	
		case 4://发货完成
			{
				// $process_status = 40;
				$trade_status = 50;
				if ($delivery_term == 1)
				{
					$pay_status = 2;
				}
				break;
			}
		case 5://订单完结
			{
				$process_status = 60;
				$trade_status = 70;
				if ($delivery_term == 1)
				{
					$pay_status = 2;
				}
				break;
			}
		case 6://已取消
			{
				$trade_status = 90;
				break;
			}		
		default:
			logx ( "invalid_trade_status tid : {$tid}  status : {$status}", $sid.'/TradeSlow' );
			logx ( "ERROR $sid invalid_trade_status tid : {$tid}  status : {$status}",$sid.'/TradeSlow', 'error' );
			break;
	}

	$receivable = $t->order_total_price;//订单总额(不包含运费)
	$post_fee = $t->ship_price;//邮费
	$total_trade_fee = $t->order_payment;//货款
	$total_discount = $t->order_seller_discount;//优惠


	//分摊邮费
	$left_post = $post_fee;
	$left_share_discount = $total_discount;

	$orders = $t->item_info_list;
	$order_count = count($orders);
	$goods_count = 0;

	for($k = 0; $k<count($orders); $k++)
	{
		$o = $orders[$k];
		$order_num = $o->item_total;
		$goods_count += (int)$order_num;
		$order_price = $o->sale_price;
		$goods_fee = bcmul($order_num, $order_price);

		/*$goods_share_amount = $o->coupon_price;
		$share_post = $o->ship_price;
		$discount_order = $o->seller_discount;
		$total_amount = bcsub($goods_fee, $discount_order);
		$share_amount = bcsub($total_amount,$goods_share_amount);*/


		//分摊优惠
		if ($k == $order_count - 1){
			$share_post = $left_post;
			$goods_share_amount = $left_share_discount;
		}else{			
			$goods_share_amount = bcdiv(bcmul($total_discount, $goods_fee), $total_trade_fee);
			$left_share_discount = bcsub($left_share_discount, $goods_share_amount);
			$share_post = bcdiv(bcmul($post_fee, $goods_fee), $total_trade_fee);
			$left_post = bcsub($left_post, $share_post);
		}

		$share_amount = bcsub($goods_fee, $goods_share_amount);

		$order_paid = bcadd($share_amount, $share_post);

		$oid = 'mia'.$o->item_id;
		$order_list[] = array
		(
			"platform_id"=>34,
			'shop_id' => $shopid,
			//交易编号
			"tid"=>$tid,
			//订单编号			
			"oid"=> $oid,
			"status"=> $trade_status,	//状态
			"refund_status"=> $order_refund_status,
			//平台货品id
			"goods_id"=> $o->sku_id,
			//规格id
			"spec_id"=>@$o->sku_item_size,
			//商家编码
			"goods_no"=> $o->item_code,
			//规格商家编码
			"spec_no"=>$o->barcode,
			//货品名
			"goods_name"=>iconv_substr(@$o->item_name,0,255,'UTF-8'),
			//规格名	
			"spec_name"=> @$o->sku_item_size,
			//数量
			'num'=>$order_num, 
			//商品单价			
			'price'=>$order_price,
			//优惠金额
			'discount'=>0,	
			'share_discount' => $goods_share_amount, 	//分摊优惠
			'share_amount'=>$share_amount,
			'total_amount'=>$goods_fee,
			//分摊邮费
			'share_post'=>$share_post,
			//分摊优惠--相当于手工调价
			'paid'=>$order_paid,
			
			'created' => array('NOW()')
		);
	}

	//地址处理
	$address = $t->address_info;
	$receiver_address = @$address->dst_street.@$address->dst_address;
	$receiver_state = @miaProvince($address->dst_province);	//省份
	$receiver_city = @$address->dst_city;			//城市
	$receiver_district = @$address->dst_area;	//区县
	$receiver_area = $receiver_state.' '.$receiver_city.' '.$receiver_district;
	// 得到 省份ID 城市ID 区县ID
	getAddressID ( $receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );

	$modules = @$address->dst_mobile;
	$phone = @$address->dst_tel;
	if (empty($modules))
	{
		$buyer_nick = 'MIA'.$phone;
	}else
	{
		$buyer_nick = 'MIA'.$modules;
	}

	//发票
	$invoice_type = 0;
	$invoice_title = '';
	$invoice_content = '';
	if ($t->need_invoice)
	{
		$invoice_type = 1;
		$invoice_title = $t->invoice_info->invoice_title;
		$invoice_content = '';
	}


	//身份信息获取
	$id_card = '';
	$id_card_type = 0;
	if ($crossborder)
	{
		$mia = new Mia();
		$mia->vendor_key = $appkey;
		$mia->secret_key = $appsecret;
		$mia->method = 'mia.order.get.identification';

		$params = array(
			'order_id'	=> $tid
		);
		$retval = $mia->execute($params);
		logx("mia_card:".print_r($retval,true) ,$sid.'/TradeSlow');
		if ($retval->code == 200)
		{
			$ret = $retval->content;
			$id_card = $ret->id_number;
			$id_card_type = 1;
		}
	}

	$trade_list[] = array
	(
		"tid"=>$tid,							//订单号
		"platform_id"=>34,						//平台id
		"shop_id"=>$shopid,				//店铺ID
		"process_status"=>$process_status, 		//处理订单状态
		"trade_status"=>$trade_status,			//平台订单状态
		"refund_status"=>$trade_refund_status, 	//退货状态
		'pay_status'=>$pay_status,
		
		'order_count'=>$order_count,
		'goods_count'=>$goods_count,
		
		"trade_time"=>$t->order_time,//下单时间
		"pay_time"=>dateValue($t->confirm_time), //支付时间
		
		"buyer_message"=>iconv_substr(valid_utf8(@$t->order_remark),0,1024,'UTF-8'), 	//买家购买附言
		"buyer_email"=>'',
		"buyer_area"=>'',
		"buyer_nick"=>$buyer_nick,
		
		"receiver_name"=>iconv_substr(valid_utf8($address->dst_name),0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		"receiver_area"=> $receiver_area,		//省市区用空格分隔
		"receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
		"receiver_zip"=> '',		//邮编
		"receiver_mobile"=>$modules, 			//电话
		'receiver_telno' => $phone,
		"receiver_hash" => md5(@$address->dst_name.$receiver_area.@$receiver_address.$modules),

		'to_deliver_time' => '',
		"logistics_type"=>-1,					//创建交易的物流方法$t->shipping_type
		
		'goods_amount'=>$total_trade_fee, //货款,未扣除优惠
		'post_amount'=>$post_fee, //邮费
		'discount'=>$total_discount, //优惠金额
		'receivable'=>$receivable, //应收金额
		'paid'=>2 == $delivery_term ? 0 : $paid, //买家已付金额
		'received'=>$paid, //已从平台收款的金额
		'cod_amount' => 2 == $delivery_term ? $paid : 0, //货到付款金额
		'dap_amount' => 2 == $delivery_term ? 0 : $paid, //款到发货金额
		
		'platform_cost'=>0,
		
		'invoice_type'=>$invoice_type, //发票类别，0 不需要，1普通发票，2增值税发票
		'invoice_title'=>$invoice_title, //发票抬头
		'invoice_content' => $invoice_content,//发票内容
		
		"delivery_term"=>$delivery_term, 		//是否货到付款
		"pay_id"=>'', 							//支付宝账号
		"remark"=>iconv_substr(valid_utf8(@$t->sellremark),0,1024,'UTF-8'), 				//卖家备注
		// "remark_flag"=>'', 	//星标

		'id_card' => $id_card,
		'id_card_type' => $id_card_type,		

		'refund_amount' => 0,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		
		'created' => array('NOW()')
	);
	return true;
}


?>
