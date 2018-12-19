<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/flw/FlwClient.php');

function flwDelSpace($str)
{
	return str_replace(array("\t","\n","\r","\r\n",' '),"",$str);
}

function flwProvince($province)
{
	global $spec_province_map;
	if(empty($province)) return '';
	$$province = flwDelSpace($province);
	if(iconv_substr($province, -1, 1, 'UTF-8') != '省')
	{
		$prefix = iconv_substr($province, 0, 2, 'UTF-8');
		if(isset($spec_province_map[$prefix]))
			return $spec_province_map[$prefix];
		
		return $province . '省';
	}
	
	return $province;
}

function loadflwTrade(&$db,$trades, &$trade_list, &$order_list, &$discount_list, &$retval)
{
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$t = &$retval->result;
	
	
	$delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 2; // 0未付款1部分付款2已付款
	$trade_refund_status = 0; // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0; // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; // 已付金额, 发货前已付
	$trade_status = 10; // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	$is_external = 0;    // is_processed is more reasonable
	$tid = $t->orderCode;   //订单编号
	$orders = $t->orderItems;
	$status = $t->orderStatus;
	
	switch ($status) {
		case 0 : {
			$trade_status = 10;//未确认
			$process_status = 10;
            $pay_status = 0;
			break;
		}
		case 1 : {
			$trade_status = 30;
			$process_status = 10;
			$pay_status = 2;
			break;
		}
		case 2 : {
			$trade_status = 50;//已发货
			$process_status = 40;
			$is_external = 1;
			$pay_status = 2;
			break;
		}
		case 3 : {
			$trade_status = 30;
			$process_status = 10;
			$pay_status = 2;
			$trade_refund_status = 1;
			break;
		}
		case 4 : {
			$trade_status = 90;
			break;
		}
		case 5 : {
			$trade_status = 70;
			$process_status = 60;
			$pay_status = 2;
			break;
		}
		case 6 : {
			$trade_status = 80;
			$pay_status = 0;
			$trade_refund_status = 3;
			break;
		}
		case 7 :
		case 8 :{ //已付款 需要等开团变状态为待发货
			$trade_status = 10;//未确认
			$process_status = 10;
			$pay_status = 2;
			break;
		}
		default:
            $error_msg['info'] = 'invalid_trade_status';
            $error_msg['status'] = 0;
            logx("ERROR $sid invalid_trade_status $tid :{$status}", $sid.'/TradeSlow','error');
	}
	//货款 不带邮费
	$trade_goods_fee = floatval($t->orderPayment);
	//邮费
	$post_fee = $t->expressFee;
	//总优惠 = 商家优惠 + 平台优惠
	$trade_discount = bcadd( @$t->couponAmount , @$t->platformCoupon);
	
	//应付 =  货款 + 邮费 - 优惠 //
	$receivable = bcsub(bcadd($trade_goods_fee ,@$post_fee) , @$trade_discount);
	
	$left_post = $post_fee;
	$left_share_discount = $trade_discount;
	
	//支付方式处理
	$pay_method = 1;
	$order_count = count($orders);
	$goods_count = 0;
	$orderId = 1;
	
	for ($i = 0; $i < $order_count; $i++) {
		$o = &$orders[$i];
		$num = $o->quantity;
		$goods_count += (int)$num;
		$price = $o->originPrice; //销售单价
        $goods_fee = bcmul($price , $num);
		switch ($o->itemState){
			case 1 :{
				$trade_refund_status = 1;
				$order_refund_status = 2;
				break;
			}
			case 2 :{
				$trade_refund_status = 2;
				$order_refund_status = 2;
				break;
			}
			case 3 :{
				$trade_refund_status = 1;
				$order_refund_status = 3;
				break;
			}
			case 11 :
				//$trade_refund_status = 3;
				$order_refund_status = 5;
				break;
			case 12 :{
				$trade_refund_status = 2;
				$order_refund_status = 5;
				break;
			}
			
		}
		
		$oid = $tid . ':' . $orderId;
		++$orderId;

		if ($i == $order_count - 1) {
			$share_post = $left_post;
			//$share_discount = $left_share_discount;
		} else {
			/*$share_discount = ((float)$trade_discount * (float)$goods_fee) / (float)$trade_goods_fee;
			$left_share_discount = $left_share_discount - $share_discount;*/
			$share_post = ((float)$post_fee * (float)$goods_fee) / (float)$trade_goods_fee;
			$left_post = (float)$left_post - (float)$share_post;
		}
        $share_discount = bcadd(@$o->platformCoupon , @$o->couponAmount);
        $share_amount = bcsub($goods_fee, $share_discount); //分摊货款

        if(2 != $delivery_term){
            $order_paid = bcadd(bcsub(bcmul(@$o->payAmount, $num) , @$share_discount) , @$share_post);
        }else{
            $order_paid = 0;
        }

        $paid += $order_paid;

		$order_list[] = array
		(
            'shop_id' =>$shopid,
			'platform_id' => 60,
			'tid' => $tid,
			'oid' => $oid,//子订单编号
			'status' => $trade_status, //订单在平台的状态
			'refund_status' => $order_refund_status, //退款标记
			'order_type' => 0,      //0正常货品 1虚拟 2服务
			'bind_oid' => '', //关联子订单（部分平台使用）
			'goods_id' => $o->productCode,  //平台货品id 不得为空
			'spec_id' =>  @$o->skuId,       //平台规格id
			'goods_no' =>  @$o->sellerEan,  //商家编码
			'spec_no' => @$o->barCode,      //规格商家编码
			'goods_name' => iconv_substr(@$o->productName, 0, 255, 'UTF-8'), //平台货品名称
			'spec_name' => iconv_substr(@$o->skuName, 0, 100, 'UTF-8'),   //平台规格名称
			'refund_id' => '',  //平台退款单id
			'num' => $num,      //货品数量
			'price' => $price,  //货品单价
			'adjust_amount' => 0,       //手工调整优惠金额,特别注意:正的表示加价,负的表示减价
			'discount' => 0,            //子订单折扣
			'share_discount' => $share_discount,//分摊优惠
			'total_amount' => $goods_fee,       //总货款
			'share_amount' => $share_amount,    //分摊后货款num*price-share_discount
			'share_post' => $share_post,        //分摊邮费
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => $order_paid, // jd seems no refund in trade api
			'created' => array('NOW()')
		);
	}
	//地址处理

	$receiver_province = flwProvince(@$t->receiverProvince);//对省进行处理
	$receiver_city = flwDelSpace(@$t->receiverCity);
	$receiver_district =flwDelSpace(@$t->receiverDistrict);
	$receiver_address = flwDelSpace(@$t->receiverAddress);
	$receiver_name = @$t->receiverName;      //姓名
	$receiver_mobile = iconv_substr(@$t->receiverMoblie, 0, 40, 'UTF-8');    //电话
	$receiver_phone = '';
	$receiver_area = $receiver_province.' '.$receiver_city.' '.$receiver_district;
	getAddressID ( $receiver_province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );

	$trade_list[] = array
	(
		'platform_id' => 60,
		'shop_id' => $shopid,   //店家编号
		'tid' => $tid,          //订单编号
		'trade_status' => $trade_status,    //平台订单状态
		'pay_status' => $pay_status,        //平台付款状态
		'refund_status' => $trade_refund_status,    //平台退款状态
		'process_status' => $process_status,        //平台处理状态
		
		'delivery_term' => $delivery_term,          //发货条件
		'trade_time' => dateValue($t->orderTime),     //下单时间
		'pay_time' => dateValue(@$t->paySuccessTime),   //支付时间
		
		'buyer_nick' => iconv_substr(@$t->buyerId, 0, 100, 'UTF-8'), //买家账号
		'buyer_email' => '',    //买家邮箱
		'buyer_area' => '',
		'pay_id' => '',         //平台支付id
		'pay_account' => '',    //买家支付账号
		
		'receiver_name' => iconv_substr($receiver_name, 0, 40, 'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr($receiver_address, 0, 256, 'UTF-8'),
		'receiver_mobile' => $receiver_mobile,
		'receiver_telno' => '',
		'receiver_zip' => @$t->receiverPostCode,
		'receiver_area' => iconv_substr($receiver_area, 0, 64, 'UTF-8'), //省市区
		
		//收件人的hash 值
		'receiver_hash' => md5($receiver_name . $receiver_area . $receiver_address . $receiver_mobile . $receiver_phone . ''),
		'logistics_type' => -1, //物流类别 -1 自由选择
		
		'buyer_message' => iconv_substr(@$t->orderRemark, 0, 1024, 'UTF-8'), //买家备注
		'remark' => iconv_substr(@$t->sellerComments, 0, 1024, 'UTF-8'),
		'remark_flag' => 0,                          //标旗
		
		'end_time' => dateValue(@$t->completeTime),   //交易结束时间
		'wms_type' => 0,              //0 表示任意仓库
		'warehouse_no' => '',         //外部仓库编号
		'stockout_no' => '',          //wms 中订单编号
		'logistics_no' => iconv_substr(@$t->expressNo, 0, 40, 'UTF-8'), //发货后物流单号
		'is_auto_wms' => 0,     //是否自动流转到wms
		'is_external' => $is_external,  //抓单时已发货，未经系统处理的订单
		
		'goods_amount' => $trade_goods_fee, //减去邮费未扣除优惠的原始货款
		'post_amount' => $post_fee,         //邮费
		'receivable' => $receivable, //应收金额，总价
		'discount' => $trade_discount,      //优惠金额
		'paid' => (2 == $delivery_term) ? 0 : $paid,  //买家已付金额     2-货到付款
		'received' => (2 == $delivery_term) ? 0 : $paid,    //已从平台收款的金额
		
		'platform_cost' => 0,   //平台费用
		
		'order_count' => $order_count,  //子订单个数
		'goods_count' => $goods_count,  //商品数量
		
		'cod_amount' => (2 == $delivery_term) ? $receivable : 0,   //货到付款金额
		'dap_amount' => (2 == $delivery_term) ? 0 : $receivable,   //款到发货金额
		'refund_amount' => 0,   //退款金额
		'trade_mask' => 0,      //订单内部来源
		'score' => 0,           //下单使用积分
		'real_score' => 0,      //实际使用的积分
		'got_score' => 0,       //交易获得的积分
		
		'created' => array('NOW()') //订单创建时间
	);
	
	return true;

}

function downflwTradesDetail (&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	global $flw_app_config;
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids =$trades->tids;
	//请求参数
	$fl = new FlwClient($appkey,$appsecret,$flw_app_config);
	$fl->setDirname('order/trade/getone');
	
	
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];

		$params['orderCode'] = $tid;
		$retval = $fl->execute($params);
		
		if(API_RESULT_OK != flwErrorTest($retval, $db, $shopid))
		{
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
			logx ( "downflwTradesDetail fail errCode:{$retval->error_code} error_msg:{$retval->error_msg} ", $sid.'/TradeSlow','error');

			return TASK_SUSPEND;
		}
		if(empty($retval->result))
		{
			$error_msg = '没有获取到订单信息';
			logx("downflwTradesDetail  fail 2, error_msg:{$error_msg}", $sid.'/TradeSlow');
			return TASK_SUSPEND;
		}
		
		if(!loadflwTrade($db, $trades, $trade_list, $order_list, $discount_list, $retval))
		{
			logx("flwload , tid: $tid", $sid.'/TradeSlow');
			continue;
		}
		
		if(count($order_list) >= 100)
		{
			if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				releaseDb($db);
				return TASK_SUSPEND;
			}
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
	
	releaseDb($db);
	
	return TASK_OK;
}

function flwDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
	$cbp = function(&$trades) use($trade_detail_cmd)
	{
		pushTask($trade_detail_cmd, $trades);
	};
	
	return flwDownloadTradeListImpl($db, $appkey, $appsecret, $shop, 0, 0, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

function flwSyncDownloadTradeList(&$db,$appkey, $appsecret, $shop,$countLimit, $start_time, $end_time, &$total_count,&$total_new, &$total_chg, &$error_msg)
{
	$total_trade_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';
	
	$cbp = function(&$trades) use($appkey, $appsecret, &$db, &$total_new, &$total_chg, &$error_msg)
	{
		downflwTradesDetail($db,$appkey,
			$appsecret,
			$trades,
			$new_trade_count,
			$chg_trade_count,
			$error_msg);
		
		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;
	};
	
	return flwDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $countLimit, $total_trade_count, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}

function flwDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $countLimit, $total_trade_count, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{
	$ptime = $end_time;
	
	if($save_time)
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;

	logx($sid.'返利网下载订单，shop_id:'.print_r($shopid,true),'Platform/flw');
	logx("flwDownloadTradeListImpl $shopid start_time:" . date('Y-m-d H:i:s', $start_time) . "end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
	
	$total_count = 0;
	$page = 1;
	$page_size = 50;
	global $flw_app_config;
	$fl = new FlwClient($appkey,$appsecret,$flw_app_config);
	$fl->setDirname('order/trade/getlist');

	while($ptime > $start_time)
	{
		if($ptime - $start_time > 3600*24) $ptime = $end_time - 3600*24 + 1;
		else $ptime = $start_time;
		logx("flwDownloadTradeListImpl $shopid  query: start_time:" . date('Y-m-d H:i:s', $ptime) . "end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');
		
		$params = array();
		$params['count'] = $page_size;
		$params['page'] = $page;
		$params['startModify'] = date('Y-m-d H:i:s', $ptime);
		$params['endModify'] = date('Y-m-d H:i:s', $end_time);
		$retval = $fl->execute($params);
		
		if(API_RESULT_OK != flwErrorTest($retval, $db, $shopid))
		{
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            logx("flwTadeListImpl fail 1 error_msg:{$retval->error_msg}",$sid.'/TradeSlow','error');

			return TASK_OK;
		}
		
		if (!isset($retval->totalResults) || count($retval->totalResults) == 0) {
			$end_time = $ptime + 1;
			logx("flwTrade $shopid.':'. count: 0", $sid.'/TradeSlow');
			continue;
		}
		
		$trades = $retval->result;
		$total_results = intval($retval->totalResults);
		$total_count += $total_results;
		
		logx("flwTrade $shopid count: $total_results", $sid.'/TradeSlow');
		
		//如果不足一页，则不需要再抓了
		if($total_results <= $page_size)
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
		}else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			
			for($i=$total_pages; $i>=1; $i--)
			{
				$params['page'] = $i;
				$retval = $fl->execute($params);
				
				if(API_RESULT_OK != flwErrorTest($retval, $db, $shopid))
				{
                    $error_msg['info'] = $retval->error_msg;
                    $error_msg['status'] = 0;
					logx ( "flwDownloadTradeListImpl $shopid start_time:" . date ( 'Y-m-d H:i:s', $ptime ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ).$retval->error_msg, $sid.'/TradeSlow','error');

					return TASK_OK;
				}
				
				$tids = array();
				$trades = $retval->result;
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
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 flw平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}
	
	return TASK_OK;
}



?>