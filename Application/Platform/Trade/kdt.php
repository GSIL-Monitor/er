<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once (TOP_SDK_DIR . '/youzan/YZTokenClient.php');

function kdtProvince($province)
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

//单条订单下载
function downKdtTradesDetail(&$db, $appkey, $appsecret, $shop, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;

	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$session = $shop->session;
	$tids = & $shop->tids;

	//有赞新版上线通知授权失效--临时处理
	if($session == ''){
		markShopAuthExpired($db, $shopid);
		releaseDb($db);
		logx("kdt_sync_trade_detail shop not auth {$shopid}!!", $sid);
		return TASK_OK;
	}

    $client = new YZTokenClient($session);
    $method = 'youzan.trade.get';
    $methodVersion = '3.0.0';

    $trade_list = array();
    $order_list = array();
    $discount_list = array();
    $params = array();
    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        $params['tid'] = $tid;
        $params['with_childs'] = true;
        $retval = $client->post($method, $methodVersion, $params);
//        $retval = json_encode($retval);
//        $retval = json_decode($retval);
        if(API_RESULT_OK != kdtErrorTest($retval, $db, $shopid))
        {
            if (40010 == @$retval['code'])
            {
                releaseDb($db);
                refreshKdtToken($appkey, $appsecret, $shop);
                $error_msg = $retval['error_msg'];
                return TASK_OK;
            }
            $error_msg = $retval['error_msg'];
            logx("kdtDownloadTradeList kdt->get fail, error_msg: {$error_msg}", $sid . "/Trade");
            return TASK_OK;
        }

        if(!loadKdtTradeImpl($appkey, $appsecret, $shop, $retval['response']['trade'], $trade_list, $order_list, $discount_list))
        {
            return TASK_SUSPEND;
        }
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

//kdt下载订单列表
function kdtDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    //有赞新版上线通知授权失效--临时处理
    if($shop->session == ''){
        markShopAuthExpired($db, $shop->shop_id);
        releaseDb($db);
        logx("kdt_sync_trade_list shop not auth {$shop->shop_id}!!", $shop->sid);
        return TASK_OK;
    }

    $ptime = $end_time;
    $loop_count = 0;
    $page_size = 80;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;

    if($save_time)
        $save_time = $end_time;

    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $session = $shop->session;

    logx("kdtDownloadTradeList $shopid start_time:" .
        date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");

    $client = new YZTokenClient($session);
    $method = 'youzan.trades.sold.get';
    $methodVersion = '3.0.0';

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    while($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 300*1)?($end_time - 300*1 + 1):$start_time;
        $loop_count++;
        if($loop_count > 1) resetAlarm();
		$params = array();
        $params['start_update'] = date('Y-m-d H:i:s', $ptime);
        $params['end_update'] = date('Y-m-d H:i:s', $end_time);
        $params['page_size'] = $page_size;
        logx("kdtDownloadTradeList $shopid start_time:" .
            date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/Trade");

        $retval = $client->post($method, $methodVersion, $params);
//        $retval = json_encode($retval);
//        $retval = json_decode($retval);
        if(API_RESULT_OK != kdtErrorTest($retval, $db, $shopid))
        {
            if (40010 == @$retval['code'])
            {
                releaseDb($db);
                refreshKdtToken($appkey, $appsecret, $shop);
                $error_msg = $retval['error_msg'];
                return TASK_OK;
            }
            $error_msg = array("status" => 0, "info" => $retval['error_msg']);
            logx("ERROR $sid kdtDownloadTradeList, error_msg:".print_r($error_msg,true), $sid . "/Trade",'error');

            return TASK_OK;
        }

        if(!isset($retval['response']['trades']) || count($retval['response']['trades']) == 0)
        {
            $end_time = $ptime + 1;
            logx("kdtDownloadTradeList $shopid count: 0", $sid . "/Trade");
            continue;
        }
        $trades = $retval['response']['trades'];
        //总条数
        $total_results = intval($retval['response']['total_results']);
        logx("kdtDownloadTradeList $shopid count: $total_results", $sid . "/Trade");

        //如果不足一页，则不需要再抓了
        if($total_results <= $page_size)
        {
            $total_trade_count += count($trades);
            for($j =0; $j < count($trades); $j++)
            {
                $retval=$trades[$j];

                //logx("kdt：".$retval['tid'], $sid . "/Trade");
                if(!loadKdtTradeImpl($appkey, $appsecret, $shop, $retval, $trade_list, $order_list, $discount_list))
                {
                    continue;
                }
                if($countLimit && $total_trade_count >= $countLimit)
                {
                    return TASK_SUSPEND;
                }
            }
        }
        else
        {   //超过一页，第一页抓的作废，从最后一页开始抓
            $total_pages = ceil(floatval($total_results)/$page_size);
            for($i=$total_pages; $i>=1; $i--)
            {
                resetAlarm();
                $params['page_no'] =$i ;
                $retval = $client->post($method, $methodVersion, $params);
//                $retval = json_encode($retval);
//                $retval = json_decode($retval);
                if(API_RESULT_OK != kdtErrorTest($retval, $db, $shopid))
                {
                    if (40010 == @$retval['code'])
                    {
                        releaseDb($db);
                        refreshKdtToken($appkey, $appsecret, $shop);
                        $error_msg = $retval['error_msg'];
                        return TASK_OK;
                    }
                    $error_msg = array("status" => 0, "info" => $retval['error_msg']);
                    logx("ERROR $sid kdtDownloadTradeList, error_msg:".print_r($error_msg,true), $sid . "/Trade",'error');

                    return TASK_OK;//任务完成
                }
                $total_trade_count += count($trades);
                $trades = $retval['response']['trades'];
                for($j =0; $j < count($trades); $j++)
                {
                    $retval=$trades[$j];
                    if(!loadKdtTradeImpl($appkey, $appsecret, $shop, $retval, $trade_list, $order_list, $discount_list))
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
                            return TASK_SUSPEND;
                        }
                    }
                }
            }
        }
        $end_time = $ptime + 1;
    }
    //保存剩下的到数据库
    if(count($order_list) > 0)
    {
        if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
        {
            return TASK_SUSPEND;//状态为：任务挂起，下一次订时再处理
        }
    }

    if($save_time)
    {
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 kdt平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
    }

    return TASK_OK;
}

function loadKdtTradeImpl($appkey, $appsecret, $shop, &$trade, &$trade_list, &$order_list, &$discount_list)
{
    $sid = $shop->sid;
    $t = &$trade;
    $sub_trades = $t['sub_trades'];
    $main_tid = $t['tid'];

    //非送礼订单
//    if( empty($t['sub_trades'])&&(!empty($t['receiver_name'])||!empty($t['fetch_detail']['fetcher_name'])))
    if($t['type']!='GIFT')
    {
		$tid = $t['tid'];
        $delivery_term = 1;//支付类型,是否货到付款
		if($t['type'] == 'COD')//货到付款
        {
            $delivery_term = 2;
        }
		$fetch = $t['fetch_detail'];
        if(empty($fetch))
        {
            //地址处理
			$receiver_area = @$t['receiver_state'] . " " . @$t['receiver_city'] . " " . @$t['receiver_district'];
			$receiver_address = @$t['receiver_address'];
			$province = kdtProvince(trim(@$t['receiver_state']));
			$city = trim(@$t['receiver_city']);
			$district = trim(@$t['receiver_district']);

            getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

			$mobile = trim(@$t['receiver_mobile']);
			$receiver_name = @$t['receiver_name'];
        }
        else
        {
			$receiver_area = @$fetch['shop_state'] . " " . @$fetch['shop_city'] . " " . @$fetch['shop_district'];
			$receiver_address = @$fetch['shop_address']."	".@$fetch['shop_name'];
			$province = kdtProvince(trim(@$fetch['shop_state']));
			$city = trim(@$fetch['shop_city']);
			$district = trim(@$fetch['shop_district']);

            getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

			$mobile = trim(@$fetch['fetcher_mobile']);
			$receiver_name = @$fetch['fetcher_name'];
        }

        //订单状态
        $trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
        $process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
        $pay_status = 0;		//0未付款 1部分付款 2已付款
        //trade退款状态
        $trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
        //order退款状态
        $order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
		if($t['status'] == 'TRADE_NO_CREATE_PAY' || $t['status'] == 'WAIT_BUYER_PAY' || $t['status'] == 'WAIT_PAY_RETURN' || $t['status'] == 'WAIT_GROUP')
        {
            $process_status = 10; //未付款
        }
		else if($t['status'] == 'TRADE_CLOSED_BY_USER')
        {
            $trade_status = 90; //未付款就关闭
        }
		else if($t['status'] == 'WAIT_SELLER_SEND_GOODS')//等待卖家发货
        {
            $trade_status = 30;
            $process_status = 10;
            $pay_status = 2;
        }
		else if($t['status'] == 'TRADE_CLOSED')
        {
            $trade_status = 80;
            $pay_status = 2;
        }
		else if($t['status'] == 'WAIT_BUYER_CONFIRM_GOODS')
        {
            $trade_status = 50; //已发货
            $pay_status = 2;
        }
		else if($t['status'] == 'TRADE_BUYER_SIGNED')
        {
            $trade_status = 70; //已签收
            $pay_status = 2;
        }
        else
        {
            logx("ERROR $sid invalid_trade_status $tid {$t['status']}", $sid . "/Trade",'error');
        }


		if($t['refund_state'] == 'PARTIAL_REFUNDING'|| $t['refund_state'] == 'PARTIAL_REFUNDED'|| $t['refund_state'] == 'PARTIAL_REFUND_FAILED'|| $t['refund_state'] == 'FULL_REFUNDING'|| $t['refund_state'] == 'FULL_REFUNDED'|| $t['refund_state'] == 'FULL_REFUND_FAILED')
        {
            $trade_refund_states = 1;

        }

        $trade_refund_amount = 0;
		if($t['refund_state'] == 'PARTIAL_REFUNDED'|| $t['refund_state'] == 'FULL_REFUNDED')
        {
			$trade_refund_amount = $t['refunded_fee'];
        }

        //商品的优惠
        //有赞优惠信息不正确. 计算方式处理
        $goods_amount = 0;	//商品总金额
        $all_discount = 0; //订单总折扣
        $order_discount = 0;//商品总优惠
		for ($d = 0; $d < count($t['orders']); $d++)
        {
			$od = $t['orders'][$d];
			$goods_amount += bcmul($od['price'], $od['num']);
			$order_discount += bcsub(bcmul($od['price'], $od['num']), $od['payment']);
        }
        //总折扣 = 商品总价 + 邮费 - 实收
		$all_discount = bcadd($goods_amount, $t['post_fee']);
		$all_discount = bcsub($all_discount, $t['payment']);

		$post_fee = $t['post_fee'];
		$adjust_fee = $t['adjust_fee']['pay_change'];//由于change是pay_change+post_change，但post_change可以忽略，因为post_fee平台已经减去了post_change
        $trade_total_fee = bcsub(floatval($goods_amount), floatval($order_discount));
        $trade_discount_money = bcsub(floatval($all_discount), floatval($order_discount));//涨价或降价处理
		$receivable = floatval($t['payment']);
        //分摊费用处理
        $left_post = $post_fee;
        $left_share_discount = $trade_discount_money;
        //主订单退款金额分摊
        $left_trade_refund = $trade_refund_amount;

		$orders = &$t['orders'];
        $order_count = count($orders);
        $goods_count = 0;

        $oidMap = array();
        $orderId = 1;

        $filter = 1;//过滤赠品计算分摊 1不过滤 2过滤
        $k = $order_count -1;//最后一个数组的下标
        if ($k != 0 )
        {
            for ($f = $k; $f >= $k; $f--)
            {
				if ($orders[$f]['is_present'] == 1)
                {
                    ++$filter;
                }
                else
                {
                    break;
                }
            }
        }

        for($k=0; $k<count($orders); $k++)
        {
            $o = & $orders[$k];
			$goods_no = trim(@$o['outer_item_id']);
			$spec_no = trim(@$o['outer_sku_id']);
            if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
            {
                logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t" . @$o['title'], $sid . "/Trade");


                $message = '';
                if(iconv_strlen($goods_no, 'UTF-8')>40)
                    $message = "货品商家编码超过40字符:{$goods_no}";
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

                $goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
                $spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
            }

			$order_num = $o['num'];
            $goods_count += (int)$order_num;
			$order_price = $o['price'];

            /*$orderDiscount = 0;
            for($z = 0;$z < count($o->order_promotion_details);$z++)
            {
                $orderDiscount += $o->order_promotion_details[$z]->discount_fee;
            }
            $order_discount_money = bcmul($orderDiscount,$order_num);*/

			$order_discount_money = bcsub(bcmul($order_price, $order_num), $o['payment']);
			if ($o['is_present'] == 1)
            {
                $order_discount_money = bcmul($order_price, $order_num);
            }
            $goods_fee = bcsub(bcmul($order_price, $order_num), $order_discount_money);

            if ($k == $order_count - $filter){
                $share_discount = $left_share_discount;
                $share_post = $left_post;
                $share_refund = $left_trade_refund;
            }else{
                $share_discount = round(bcmul($trade_discount_money, $goods_fee)/ $trade_total_fee,2);
                $left_share_discount = bcsub($left_share_discount, $share_discount);

                $share_post = round(bcmul($post_fee, $goods_fee)/ $trade_total_fee,2);
                $left_post = bcsub($left_post, $share_post);

                $share_refund = round(bcmul($trade_refund_amount, $goods_fee)/ $trade_total_fee,2);
                $left_trade_refund = bcsub($left_trade_refund, $share_refund);
            }

            $share_amount = bcsub($goods_fee, $share_discount);

            if (2 == $delivery_term){
                $order_paid = 0;
            }else{
				$order_paid = bcadd($share_amount, $share_post);
            }

			$SpecName=$o['title'].':'.$o['sku_properties_name'];
			$oid = $tid.':'.$o['item_id'].':'.$o['sku_id'];
            $oid2 = iconv_substr($oid, 0, 40, 'UTF-8');
            if(isset($oidMap[$oid2]))
            {
                $oid2 = $tid.':'.$orderId;
                ++$orderId;
            }
            $oidMap[$oid2] = 1;

			if((empty($o['item_refund_state'])||($o['item_refund_state']=='NO_REFUND')) && ($o['state_str'] == '已发货' || $o['state_str'] == '待发货' || $o['state_str'] == '待付款'))
            {
                $order_refund_status = 0;
            }
			elseif($o['state_str'] == '退款中')
            {
                $order_refund_status = 2;
            }
			elseif($o['state_str'] == '退款关闭')
            {
                $order_refund_status = 1;
            }
			elseif($o['state_str'] == '退款成功')
            {
                $order_refund_status = 5;
            }
            else
            {
                logx("子订单退款状态 {$o['state_str']}", $sid . "/Trade");
            }

            $order_list[] = array
            (
                "shop_id"=>$shop->shop_id,				//店铺ID
                "rec_id"=>0,
                "platform_id"=>17,
                //交易编号
                "tid"=>$tid,
                //订单编号
                "oid"=>$oid2,
                "status"=>$trade_status,	//状态
                "refund_status"=>$order_refund_status,
                "bind_oid"=>$o['oid'],
                //平台货品id
				"goods_id"=>$o['item_id'],
                //规格id
				"spec_id"=>(@$o['sku_id'] == '0')?'':@$o['sku_id'],
                //商家编码
                "goods_no"=>$goods_no,
                //规格商家编码
                "spec_no"=>$spec_no,
                //货品名
				"goods_name"=>iconv_substr(@$o['title'],0,255,'UTF-8'),
                //规格名
                "spec_name"=>iconv_substr($SpecName,0,100,'UTF-8'),
                //数量
                'num'=>$order_num,
                //商品单价
                'price'=>$order_price,
                //优惠金额
                'discount'=>$order_discount_money,
				'total_amount'=>bcsub($o['total_fee'],$order_discount_money),
                'share_amount'=>$share_amount,
                //分摊邮费
                'share_post'=>$share_post,
                //分摊优惠--相当于手工调价
                //'adjust_amount'=>$share_discount,
                'share_discount' => $share_discount, 	//分摊优惠
                'refund_amount' => $share_refund,
                'paid'=>$order_paid,

                'created' => array('NOW()')
            );

            if(bccomp($order_discount_money, 0)){

				$discount_oid = $o['item_id'];
                $discount_list[] = array
                (
                    'platform_id' => 17,
                    'tid' => $tid,
                    'oid' => $discount_oid,
                    'sn' => '',
                    'type' => '',
                    'name' => '商品优惠',
                    'is_bonus' => 0,
                    'detail' => '',
                    'amount' => $order_discount_money,
                    'created' => array('NOW()')
                );
            }

        }

        $buyer_message = '';
		for($m=0; $m<count($t['orders']); $m++)
        {
			$o = & $t['orders'][$m];

			if(!empty($o['buyer_messages']))
            {
				$mess = $o['buyer_messages'];
                for($j = 0;$j < count($mess);$j++)
                {
					$buyer_message_title = $mess[$j]['title'];
					$buyer_message_content = $mess[$j]['content'];
                    $buyer_message .=$buyer_message_title.'=>'.$buyer_message_content.'	';
                }
            }
        }
		$buyer_message.=$t['buyer_message'];
        $trade_list[] = array
        (
            "tid"=>$tid,							//订单号
            "platform_id"=>17,						//平台id
            "shop_id"=>$shop->shop_id,				//店铺ID
            "process_status"=>$process_status, 		//处理订单状态
            "trade_status"=>$trade_status,			//平台订单状态
            "refund_status"=>$trade_refund_states, 	//退货状态
            'pay_status'=>$pay_status,

            'order_count'=>$order_count,
            'goods_count'=>$goods_count,

			"trade_time"=>dateValue($t['created']), 	//下单时间
			"pay_time"=>dateValue($t['pay_time']),	//付款时间

			"buyer_nick"=>empty($t['fans_info']['fans_nickname']) ? "kdt".iconv_substr($mobile,0,37,'UTF-8') : iconv_substr(valid_utf8(trim($t['fans_info']['fans_nickname'])),0,100,'UTF-8') ,
            "buyer_message"=>iconv_substr(valid_utf8(@$buyer_message),0,1024,'UTF-8'), 	//买家购买附言
            "buyer_email"=>'',
			"buyer_area"=>iconv_substr($t['buyer_area'],0,64,'UTF-8'),

            "receiver_name"=>iconv_substr(valid_utf8($receiver_name),0,40,'UTF-8'),
            "receiver_province"=>$province_id,		//省份id
            "receiver_city"=>$city_id, 				//市id
            "receiver_district"=>$district_id, 		//地区id
            "receiver_area"=> $receiver_area,		//省市区
            "receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
			"receiver_zip"=>@$t['receiver_zip'],		//邮编
            "receiver_mobile"=>iconv_substr($mobile,0,40,'UTF-8'), 			//电话
            'to_deliver_time' => '',

			"receiver_hash" => md5(@$receiver_name.$receiver_area.@$receiver_address.$mobile.''.@$t['receiver_zip']),
            "logistics_type"=>-1,					//创建交易的物流方法$t->shipping_type
			'warehouse_no' => ($t['shop_type'] == 3)?$t['shop_id']:'',
            'goods_amount'=>$goods_amount,
            'post_amount'=>$post_fee,
            'discount'=>$all_discount,
			'receivable'=>$receivable,
			'paid'=>$t['payment'],

            'platform_cost'=>0,

            'invoice_type'=>0,
            'invoice_title'=>'',

            "delivery_term"=>$delivery_term, 		//是否货到付款
            "pay_id"=>'', 							//支付宝账号
			"remark"=>iconv_substr(@$t['trade_memo'],0,1024,'UTF-8'), 				//卖家备注
			"remark_flag"=>(int)@$t['seller_flag'], 	//星标

			'cod_amount' => 2 == $delivery_term ? @$t['payment'] : 0,
			'dap_amount' => 2 == $delivery_term ? 0 : $t['payment'],
            'refund_amount' => $trade_refund_amount,
            'trade_mask' => 0,
            'score' => 0,
            'real_score' => 0,
            'got_score' => 0,

            'created' => array('NOW()')
        );

        /*
        if(bccomp($trade_discount_money, 0)){

            $discount_list[] = array
            (
                'platform_id' => 17,
                'tid' => $tid,
                'oid' => '',
                'sn' => '',
                'type' => '',
                'name' => '客服调价',
                'is_bonus' => 0,
                'detail' => '',
                'amount' => $trade_discount_money,
                'created' => array('NOW()')
            );
        }*/
		if (!empty($t['coupon_details']))
        {
			$coupon_details = $t['coupon_details'];
            foreach ($coupon_details as $coupon) {
                $discount_list[] = array
                (
                    'platform_id' => 17,
                    'tid' => $tid,
                    'oid' => '',
					'sn' => $coupon['coupon_id'],
					'type' => $coupon['coupon_type'],
					'name' => $coupon['coupon_name'],
                    'is_bonus' => 1,
					'detail' => $coupon['coupon_condition'],
					'amount' => $coupon['discount_fee'],
                    'created' => array('NOW()')
                );
            }

        }

		if (!empty($t['promotion_details']))
        {
			$promotion_details = $t['promotion_details'];
            foreach ($promotion_details as $promotion) {
                $discount_list[] = array
                (
                    'platform_id' => 17,
                    'tid' => $tid,
                    'oid' => '',
					'sn' => $promotion['promotion_id'],
					'type' => $promotion['promotion_type'],
					'name' => $promotion['promotion_name'],
                    'is_bonus' => 0,
					'detail' => $promotion['promotion_condition'],
					'amount' => $promotion['discount_fee'],
                    'created' => array('NOW()')
                );
            }
        }

		if (!empty($t['adjust_fee']['change']))
        {
            $discount_list[] = array
            (
                'platform_id' => 17,
                'tid' => $tid,
                'oid' => '',
                'sn' => '',
                'type' => '',
                'name' => '手工改价',
                'is_bonus' => 0,
                'detail' => '',
				'amount' => $t['adjust_fee']['pay_change'],//正值表示减价,负值是加价
                'created' => array('NOW()')
            );
        }

        return true;
    }
    else { //送礼订单
        for($m = 0;$m<count($sub_trades);$m++)
        {
            $tra = & $sub_trades[$m];
			$tid = $tra['tid'];
            $delivery_term = 1;//支付类型,是否货到付款
			if($tra['type'] == 'COD')//货到付款
            {
                $delivery_term = 2;
            }

			$fetch = $tra['fetch_detail'];
            if(empty($fetch))
            {
                //地址处理
				$receiver_area = @$tra['receiver_state'] . " " . @$tra['receiver_city'] . " " . @$tra['receiver_district'];
				$receiver_address = @$tra['receiver_address'];
				$province = kdtProvince(trim(@$tra['receiver_state']));
				$city = trim(@$tra['receiver_city']);
				$district = trim(@$tra['receiver_district']);

                getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

				$mobile = trim(@$tra['receiver_mobile']);
				$receiver_name = @$tra['receiver_name'];
            }
            else
            {
				$receiver_area = @$fetch['shop_state'] . " " . @$fetch['shop_city'] . " " . @$fetch['shop_district'];
				$receiver_address = @$fetch['shop_address']." ".@$fetch['shop_name'];
				$province = kdtProvince(trim(@$fetch['shop_state']));
				$city = trim(@$fetch['shop_city']);
				$district = trim(@$fetch['shop_district']);

                getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

				$mobile = trim(@$fetch['fetcher_mobile']);
				$receiver_name = @$fetch['fetcher_name'];
            }


			if(!empty($tra['trade_memo']))
            {
				$remark = $main_tid."	".$tra['trade_memo'];
            }
            else
            {
                $remark = $main_tid;
            }
            //订单状态
            $trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
            $process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
            $pay_status = 0;		//0未付款 1部分付款 2已付款
            //trade退款状态
            $trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
            //order退款状态
            $order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
			if($t['status'] == 'TRADE_NO_CREATE_PAY' || $t['status'] == 'WAIT_BUYER_PAY' || $t['status'] == 'WAIT_PAY_RETURN' || $t['status'] == 'WAIT_GROUP')
            {
                $process_status = 10; //未付款
            }
			else if($t['status'] == 'TRADE_CLOSED_BY_USER')
            {
                $trade_status = 90; //未付款就关闭
            }
			else if($t['status'] == 'WAIT_SELLER_SEND_GOODS')//等待卖家发货
            {
                $trade_status = 30;
                $process_status = 10;
                $pay_status = 2;
            }
			else if($t['status'] == 'TRADE_CLOSED')
            {
                $trade_status = 80;
                $pay_status = 2;
            }
			else if($t['status'] == 'WAIT_BUYER_CONFIRM_GOODS')
            {
                $trade_status = 50; //已发货
                $pay_status = 2;
            }
			else if($t['status'] == 'TRADE_BUYER_SIGNED')
            {
                $trade_status = 70; //已签收
                $pay_status = 2;
            }
            else
            {
                logx("ERROR $sid invalid_trade_status $tid {$t['status']}", $sid . "/Trade",'error');
            }


			if($tra['refund_state'] == 'PARTIAL_REFUNDING'|| $tra['refund_state'] == 'PARTIAL_REFUNDED'|| $tra['refund_state'] == 'PARTIAL_REFUND_FAILED'|| $tra['refund_state'] == 'FULL_REFUNDING'|| $tra['refund_state'] == 'FULL_REFUNDED'|| $tra['refund_state'] == 'FULL_REFUND_FAILED')
            {
                $trade_refund_states = 1;

            }

            $trade_refund_amount = 0;
			if($tra['refund_state'] == 'PARTIAL_REFUNDED'|| $tra['refund_state'] == 'FULL_REFUNDED')
            {
				$trade_refund_amount = $tra['refunded_fee'];
            }

            $all_discount = 0;
            $goods_amount = 0;
            $order_discount = 0;
			for ($d = 0; $d < count($tra['orders']); $d++)
            {
				$od = $tra['orders'][$d];
				$goods_amount += bcmul($od['price'], $od['num']);
				$order_discount += bcsub(bcmul($od['price'], $od['num']), $od['payment']);
            }
            //商品总价 + 邮费 - 折扣 = 应收
            //折扣 = 商品总价 + 邮费 - 应收
			$all_discount = bcadd($goods_amount, $tra['post_fee']);
			$all_discount = bcsub($all_discount, $tra['payment']);

			$post_fee = $tra['post_fee'];
            $trade_total_fee = bcsub(floatval($goods_amount), floatval($order_discount));
            $trade_discount_money = bcsub($all_discount, $order_discount);//涨价或降价处理
			$receivable = floatval($tra['payment']);
            //分摊费用处理
            $left_post = $post_fee;
            $left_share_discount = $trade_discount_money;
            //主订单退款金额分摊
            $left_trade_refund = $trade_refund_amount;
			$orders = &$tra['orders'];
            $order_count = count($orders);
            $goods_count = 0;

            $oidMap = array();
            $orderId = 1;
            for($k=0; $k<count($orders); $k++)
            {
                $o = & $orders[$k];
				$goods_no = trim(@$o['outer_item_id']);
				$spec_no = trim(@$o['outer_sku_id)']);
                if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
                {
                    logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t" . @$o['title'], $sid . "/Trade");


                    $message = '';
                    if(iconv_strlen($goods_no, 'UTF-8')>40)
                        $message = "货品商家编码超过40字符:{$goods_no}";
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

                    $goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
                    $spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
                }

				$order_num = $o['num'];
                $goods_count += (int)$order_num;
				$order_price = $o['price'];

                /*$orderDiscount = 0;
                for($z = 0;$z < count($o->order_promotion_details);$z++)
                {
                    $orderDiscount += $o->order_promotion_details[$z]->discount_fee;
                }
                $order_discount_money = bcmul($orderDiscount,$order_num);*/
				$order_discount_money = bcsub(bcmul($order_price, $order_num), $o['payment']);

                $goods_fee = bcsub(bcmul($order_price, $order_num), $order_discount_money);

                if ($k == $order_count - 1){
                    $share_discount = $left_share_discount;
                    $share_post = $left_post;
                    $share_refund = $left_trade_refund;
                }else{
                    $share_discount = round(bcmul($trade_discount_money, $goods_fee)/ $trade_total_fee,2);
                    $left_share_discount = bcsub($left_share_discount, $share_discount);

                    $share_post = round(bcmul($post_fee, $goods_fee)/ $trade_total_fee,2);
                    $left_post = bcsub($left_post, $share_post);

                    $share_refund = round(bcmul($trade_refund_amount, $goods_fee)/ $trade_total_fee,2);
                    $left_trade_refund = bcsub($left_trade_refund, $share_refund);
                }

                $share_amount = bcsub($goods_fee, $share_discount);

                if (2 == $delivery_term){
                    $order_paid = 0;
                }else{
					$order_paid = bcadd($share_amount, $share_post);
                }

				$SpecName=$o['title'].':'.$o['sku_properties_name'];
				$oid = $tid.':'.$o['item_id'].':'.$o['sku_id'];
                $oid2 = iconv_substr($oid, 0, 40, 'UTF-8');
                if($oid2 != $oid)
                {
                    if(isset($oidMap[$oid2]))
                    {
                        $oid2 = $tid.':'.$orderId;
                        ++$orderId;
                    }
                }
                $oidMap[$oid2] = 1;

				if((empty($o['item_refund_state'])||($o['item_refund_state']=='NO_REFUND')) && ($o['state_str'] == '已发货' || $o['state_str'] == '待发货' || $o['state_str'] == '待付款'))
                {
                    $order_refund_status = 0;
                }
				elseif($o['state_str'] == '退款中')
                {
                    $order_refund_status = 2;
                }
				elseif($o['state_str'] == '退款关闭')
                {
                    $order_refund_status = 1;
                }
				elseif($o['state_str'] == '退款成功')
                {
                    $order_refund_status = 5;
                }
                else
                {
					logx("子订单退款状态 {$o['state_str']}",$sid);
                }

                $order_list[] = array
                (
                    "shop_id"=>$shop->shop_id,				//店铺ID
                    "rec_id"=>0,
                    "platform_id"=>17,
                    //交易编号
                    "tid"=>$tid,
                    //订单编号
                    "oid"=>$oid2,
                    "status"=>$trade_status,	//状态
                    "refund_status"=>$order_refund_status,
					"bind_oid"=>$o['oid'],
                    //平台货品id
					"goods_id"=>$o['item_id'],
                    //规格id
					"spec_id"=>@$o['sku_id'],
                    //商家编码
                    "goods_no"=>$goods_no,
                    //规格商家编码
                    "spec_no"=>$spec_no,
                    //货品名
					"goods_name"=>iconv_substr(@$o['title'],0,255,'UTF-8'),
                    //规格名
                    "spec_name"=>iconv_substr($SpecName,0,100,'UTF-8'),
                    //数量
                    'num'=>$order_num,
                    //商品单价
                    'price'=>$order_price,
                    //优惠金额
                    'discount'=>$order_discount_money,
					'total_amount'=>bcsub($o['total_fee'],$order_discount_money),
                    'share_amount'=>$share_amount,
                    //分摊邮费
                    'share_post'=>$share_post,
                    //分摊优惠--相当于手工调价
                    //'adjust_amount'=>$share_discount,
                    'share_discount' => $share_discount, 	//分摊优惠
                    'refund_amount' => $share_refund,
                    'paid'=>$order_paid,

                    'created' => array('NOW()')
                );

                if(bccomp($order_discount_money, 0)){

					$discount_oid = $o['item_id'];
                    $discount_list[] = array
                    (
                        'platform_id' => 17,
                        'tid' => $tid,
                        'oid' => $discount_oid,
                        'sn' => '',
                        'type' => '',
                        'name' => '商品优惠',
                        'is_bonus' => 0,
                        'detail' => '',
                        'amount' => $order_discount_money,
                        'created' => array('NOW()')
                    );
                }

            }

            $buyer_message = '';
			for($n=0; $n<count($tra['orders']); $n++)
            {
				$o = & $tra['orders'][$n];

				if(!empty($o['buyer_messages']))
                {
					$mess = $o['buyer_messages'];
                    for($j = 0;$j < count($mess);$j++)
                    {
						$buyer_message_title = $mess[$j]['title'];
						$buyer_message_content = $mess[$j]['content'];
                        $buyer_message .=$buyer_message_title.'=>'.$buyer_message_content.'	';
                    }
                }
            }
			$buyer_message.=$tra['buyer_message'];
            $trade_list[] = array
            (
                "tid"=>$tid,							//订单号
                "platform_id"=>17,						//平台id
                "shop_id"=>$shop->shop_id,				//店铺ID
                "process_status"=>$process_status, 		//处理订单状态
                "trade_status"=>$trade_status,			//平台订单状态
                "refund_status"=>$trade_refund_states, 	//退货状态
                'pay_status'=>$pay_status,

                'order_count'=>$order_count,
                'goods_count'=>$goods_count,

				"trade_time"=>dateValue($tra['created']), 	//下单时间
				"pay_time"=>dateValue($tra['pay_time']),	//付款时间

				"buyer_nick"=>empty($t['fans_info']['fans_nickname']) ? "kdt".iconv_substr($mobile,0,37,'UTF-8') : iconv_substr(valid_utf8(trim($t['fans_info']['fans_nickname'])),0,100,'UTF-8'),
                "buyer_message"=>iconv_substr(valid_utf8(@$buyer_message),0,1024,'UTF-8'), 	//买家购买附言
                "buyer_email"=>'',
				"buyer_area"=>iconv_substr($tra['buyer_area'],0,64,'UTF-8'),

                "receiver_name"=>iconv_substr(valid_utf8($receiver_name),0,40,'UTF-8'),
                "receiver_province"=>$province_id,		//省份id
                "receiver_city"=>$city_id, 				//市id
                "receiver_district"=>$district_id, 		//地区id
                "receiver_area"=> $receiver_area,		//省市区
                "receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
				"receiver_zip"=>@$tra['receiver_zip'],		//邮编
                "receiver_mobile"=>iconv_substr($mobile,0,40,'UTF-8'), 			//电话
                'to_deliver_time' => '',

				"receiver_hash" => md5(@$receiver_name.$receiver_area.@$receiver_address.$mobile.''.@$tra['receiver_zip']),
                "logistics_type"=>-1,					//创建交易的物流方法$t->shipping_type
				'warehouse_no' => ($t['shop_type'] == 3)?$t['shop_id']:'',
                'goods_amount'=>$goods_amount,
                'post_amount'=>$post_fee,
                'discount'=>$all_discount,
                'receivable'=>$receivable,
				'paid'=>$tra['payment'],

                'platform_cost'=>0,

                'invoice_type'=>0,
                'invoice_title'=>'',

                "delivery_term"=>$delivery_term, 		//是否货到付款
                "pay_id"=>'', 							//支付宝账号
                "remark"=>iconv_substr(@$remark,0,1024,'UTF-8'), 				//卖家备注
				"remark_flag"=>(int)@$tra['seller_flag'], 	//星标

				'cod_amount' => 2 == $delivery_term ? @$tra['payment'] : 0,
                'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
                'refund_amount' => $trade_refund_amount,
                'trade_mask' => 0,
                'score' => 0,
                'real_score' => 0,
                'got_score' => 0,

                'created' => array('NOW()')
            );

            /*
            if(bccomp($trade_discount_money, 0)){

                $discount_list[] = array
                (
                    'platform_id' => 17,
                    'tid' => $tid,
                    'oid' => '',
                    'sn' => '',
                    'type' => '',
                    'name' => '客服调价',
                    'is_bonus' => 0,
                    'detail' => '',
                    'amount' => $trade_discount_money,
                    'created' => array('NOW()')
                );
            }*/
			if (!empty($tra['coupon_details']))
            {
				$coupon_details = $tra['coupon_details'];
                foreach ($coupon_details as $coupon) {
                    $discount_list[] = array
                    (
                        'platform_id' => 17,
                        'tid' => $tid,
                        'oid' => '',
						'sn' => $coupon['coupon_id'],
						'type' => $coupon['coupon_type'],
						'name' => $coupon['coupon_name'],
                        'is_bonus' => 1,
						'detail' => $coupon['coupon_condition'],
						'amount' => $coupon['discount_fee'],
                        'created' => array('NOW()')
                    );
                }

            }

			if (!empty($tra['promotion_details']))
            {
				$promotion_details = $tra['promotion_details'];
                foreach ($promotion_details as $promotion) {
                    $discount_list[] = array
                    (
                        'platform_id' => 17,
                        'tid' => $tid,
                        'oid' => '',
						'sn' => $promotion['promotion_id'],
						'type' => $promotion['promotion_type'],
						'name' => $promotion['promotion_name'],
                        'is_bonus' => 0,
						'detail' => $promotion['promotion_condition'],
						'amount' => $promotion['discount_fee'],
                        'created' => array('NOW()')
                    );
                }
            }

			if (!empty($tra['adjust_fee']['change']))
            {
                $discount_list[] = array
                (
                    'platform_id' => 17,
                    'tid' => $tid,
                    'oid' => '',
                    'sn' => '',
                    'type' => '',
                    'name' => '手工改价',
                    'is_bonus' => 0,
                    'detail' => '',
					'amount' => $tra['adjust_fee']['pay_change'],//正值表示减价,负值是加价
                    'created' => array('NOW()')
                );
            }




        }
        return true;

    }

}
