<?php
require_once(ROOT_DIR . '/Trade/util.php');

function vipshopProvince($province)
{
    global $g_province_map;
    if(empty($province)) return '';

    if(iconv_substr($province, -1, 1, 'UTF-8') == '区')
    {
        return $province;
    }

    if(iconv_substr($province, -1, 1, 'UTF-8') != '省')
    {
        $prefix = iconv_substr($province, 0, 2, 'UTF-8');

        if(isset($g_province_map[$prefix]))
            return $prefix;

        return $province . '省';
    }

    return $province;
}

//唯品会订单列表下载
function vipshopDownloadTradeList(&$db, $appkey, $appsecret, $shop, $count_limit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $loop_count = 0;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;

    $ptime = $end_time;
    if($save_time)
        $save_time = $end_time;

    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    //供应商ID
    $vendor_id = $shop->account_id;
    $session = $shop->session;

    logx("vipshopDownloadTradeList $shopid start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    while($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 3600*12)?($end_time - 3600*12 + 1):$start_time;
        $loop_count++;
        if($loop_count > 1) resetAlarm();

        try
        {
            require_once TOP_SDK_DIR."/vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
            $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
            $ctx= \Osp\Context\InvocationContextFactory::getInstance();
            $page = 1;
            $pageSize= 100;
            $s_time = date('Y-m-d H:i:s', $ptime);
            $e_time = date('Y-m-d H:i:s', $end_time);
            $ctx->setAppKey($appkey);
            $ctx->setAppSecret($appsecret);
            $ctx->setAccessToken($session);
            $ctx->setAppURL("https://gw.vipapis.com/");
            $ctx->setLanguage("zh");
            //获取结果
            $retval = $service->getOrderList($s_time,$e_time,null,null,null,$vendor_id,$page,$pageSize);
        }
        catch(\Osp\Exception\OspException $e)
        {
            if(API_RESULT_OK != vipshopErrorTest($e, $db, $shopid))
            {
                $error_msg['status'] = 0;
                $error_msg['info'] = $e->returnMessage;
                logx("vipshopDownloadTradeList {$sid} vipshop->execute fail! error code:{$e->returnCode} message:{$e->returnMessage}", $sid.'/TradeSlow','error');
                return TASK_OK;
            }

            $error_msg['status'] = 0;
            $error_msg['info'] = $e->returnMessage;
            logx("getOrderList fail: {$error_msg['info']} error_code: {$e->returnCode} error_msg: {$e->returnMessage}",$sid.'/TradeSlow');
            return TASK_OK;
        }

        if (empty( $retval->dvd_order_list ))
        {
            $end_time = $ptime + 1;
            logx ( "vipshopDownloadTradeList $shopid count: 0", $sid.'/TradeSlow' );
            continue;
        }
        //总条数
        $total_results = intval($retval->total);
        logx("vipshopDownloadTradeList $shopid count: $total_results", $sid.'/TradeSlow');

        $trades = $retval->dvd_order_list;

        if ($total_results <= count($trades))
        {
            $total_trade_count += count($trades);
            if(!vipshopDownloadTradeDetails($db, $sid, $appkey, $appsecret, $shop, $trades, $trade_list, $order_list, $discount_list))
            {
                continue;
            }
            if ($count_limit && $total_trade_count >= $count_limit) return TASK_SUSPEND;

        }
        else
        {
            $total_pages = ceil(floatval($total_results)/$pageSize);

            for($i=$total_pages; $i>0; $i--)
            {
                resetAlarm();
                try
                {
                    $retval=$service->getOrderList($s_time,$e_time,null,null,null,$vendor_id,$i,$pageSize);
                }
                catch(\Osp\Exception\OspException $e)
                {

                    if(API_RESULT_OK != vipshopErrorTest($e, $db, $shopid))
                    {
                        $error_msg['status'] = 0;
                        $error_msg['info'] = $e->returnMessage;
                        logx("vipshopDownloadTradeList {$sid} error : {$error_msg['info']}!", $sid.'/TradeSlow','error');
                        return TASK_OK;
                    }
                    $error_msg['status'] = 0;
                    $error_msg['info'] = $e->returnMessage;
                    logx("getOrderList fail: {$error_msg['info']} error_code: {$e->returnCode} error_msg: {$e->returnMessage}",$sid.'/TradeSlow');

                    return TASK_OK;
                }

                $trades = &$retval->dvd_order_list;

				if(!vipshopDownloadTradeDetails($db, $sid, $appkey, $appsecret, $shop, $trades, $trade_list, $order_list, $discount_list))
				{
					continue;
				}
				if ($count_limit && count($trades) >= $count_limit) return TASK_SUSPEND;

                if(count($order_list) >= 100)
                {
                    if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
                    {
                        return TASK_OK;
                    }
                }
                $total_trade_count += count($trades);
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
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 vipshop平台 '.print_r($save_time,true),$sid. "/default");
    }

    return TASK_OK;
}

/**************VipShop订单状态列表*******************

0	未支付订单	1	待审核订单
10	订单已审核	11	未处理	12	商品调拨中	13	缺货	14	订单发货失败
20	拣货中	21	已打包	22	已发货	23	售后处理	24	未处理	25	已签收	28	订单重发
30	未处理	31	未处理
40	货品回寄中	41	退换货服务不受理	42	无效换货	45	退款处理中	46	退换货未处理	47	修改退款资料	48	无效退货	49	已退款
51	退货异常处理中	52	退款异常处理中	53	退货未审核	54	退货已审核	55	拒收回访	56	售后异常	57	上门取件	58	退货已返仓	59	已退货
60	已完成	61	已换货
70	用户已拒收	71	超区返仓中	72	拒收返仓中
96	订单已修改	97	订单已取消	98	已合并	99	已删除	100	退货失败
refuse_unnormal	客退申请中的商品数量与供应商拒收商品 数量/品种 不一致

 **********此状态通过接口抓取获得(14-10-28)***********/

function loadVipShopTradeImpl($sid, $appkey, $appsecret, $shop, &$trade, &$trade_list, &$order_list, &$discount_list)
{
    $t = &$trade;
    $tid = $t->order_id;
    //供应商ID
    $vendor_id = $shop->account_id;
    $session = $shop->session;
    $shop_id = $shop -> shop_id;
    $delivery_term = 1;		//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
    $process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
    $pay_status = 0;		//0未付款 1部分付款 2已付款
    $trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功

    $refund_apply = array(45, 46, 47, 51, 52, 53, 70, 72);
    $refund_success = array(49, 58, 59);

    if(in_array($t->order_status, $refund_apply))
    {
        $trade_refund_states = 1;
    }
    if(in_array($t->order_status, $refund_success))
    {
        $trade_refund_states = 3;
    }

    $trade_no_pay = array(0, 1);
    $trade_unsend = array(10, 11, 12, 13, 14, 20, 21, 28, 30, 31, 45, 47, 52, 57, 96, 97, 98);
    $trade_send = array(22, 23, 24, 40, 41, 42, 45, 46, 48, 51, 53, 54, 58, 59, 100, "refuse_unnormal");
    $trade_sign = array(25);
    $trade_finished = array(60);
    $trade_refund = array(49);
    $trade_unnormal_closed = array(55, 56, 59, 61, 70, 71, 97, 99);

    if(in_array($t->order_status, $trade_no_pay))
    {
        $process_status = 10;
    }
    else if(in_array($t->order_status, $trade_unsend))
    {
        $trade_status = 30;
        $process_status = 10;
        $pay_status = 2;
    }
    else if(in_array($t->order_status, $trade_send))
    {
        $trade_status = 50;
        $pay_status = 2;
    }
    else if(in_array($t->order_status, $trade_sign))
    {
        $trade_status = 60;
        $pay_status = 2;
    }
    else if(in_array($t->order_status, $trade_finished))
    {
        $trade_status = 70;
        $pay_status = 2;
    }
    else if(in_array($t->order_status, $trade_refund))
    {
        $trade_status = 80;
        $pay_status = 2;
    }
    else if(in_array($t->order_status, $trade_unnormal_closed))
    {
        $trade_status = 90;
        $pay_status = 2;
    }
    else
    {
        logx("invalid_trade_status {$sid} $tid {$t->order_status}", $sid.'/TradeSlow','error');
    }

    $receiver_area = @$t->province . " " . @$t->city . " " . @$t->country;
    $receiver_address = $t->address;
    $province = vipshopProvince($t->province);
    $city = trim($t->city);
    $district = trim($t->country);
    getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

    $invoice_type = 0;
    $invoice_title = '';
    $invoice_content = '';
    if(!empty($t->invoice))
    {
        $invoice_type = 1;
        $invoice_title = $t->invoice;
        !empty($t->tax_pay_no) && $invoice_content .= "纳税人识别号:" . $t->tax_pay_no . " ";
    }

    $post_fee = $t->carriage;
    $trade_discount = $t->promo_discount_amount;
    $goods_amount = $t->product_money;
    $receivable = bcsub(bcadd($goods_amount, $post_fee), $trade_discount);
/*
    try
    {
        require_once TOP_SDK_DIR."/vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
        $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
        $ctx = \Osp\Context\InvocationContextFactory::getInstance();
        $ctx->setAppKey($appkey);
        $ctx->setAppSecret($appsecret);
        $ctx->setAccessToken($session);
        $ctx->setAppURL("https://gw.vipapis.com/");
        $ctx->setLanguage("zh");
        $retval=$service->getOrderDetail($vendor_id,$tid,null,null);
    }
    catch(\Osp\Exception\OspException $e)
    {
        if(API_RESULT_OK != vipshopErrorTest($e, $db, $shop->shop_id))
        {
            $error_msg['status'] = 0;
            $error_msg['info'] = $e->returnMessage;
            logx("loadVipShopTradeImpl {$sid} vipshop->execute fail! error msg:{$error_msg['info']}", $sid.'/TradeSlow','error');
            return TASK_OK;
        }
        $error_msg['status'] = 0;
        $error_msg['info'] = $e->returnMessage;
        logx("getOrderDetail fail !! error_code:{$e->returnCode} error_msg:{$e->returnMessage}",$sid.'/TradeSlow');
        return TASK_OK;
    }
*/
    $orders = $t->orderDetails;
    $order_count = count($orders);
    $goods_count = 0;

    //分摊费用处理
    $left_post = $post_fee;
    $left_share_discount = $trade_discount;

    for($k = 0; $k < count($orders); $k++)
    {
        $o = &$orders[$k];
        $goods_no = trim(@$o->art_no);
        $spec_no = trim(@$o->barcode);
        if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
        {
            logx("{$sid} GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t".@$o->product_name ,$sid.'/TradeSlow', 'error');

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
        $num = $o->amount;
        $price = $o->sell_price;
        $goods_count += $num;
        $goods_fee = bcmul($num, $price);

        if ($k == $order_count-1)
        {
            $share_discount = $left_share_discount;
            $share_post = $left_post;
        }
        else
        {
            $share_discount = bcdiv(bcmul($trade_discount, $goods_fee), $goods_amount);
            $left_share_discount = bcsub($left_share_discount, $share_discount);

            $share_post = bcdiv(bcmul($post_fee, $goods_fee), $goods_amount);
            $left_post = bcsub($left_post, $share_post);
        }
        $share_amount = bcsub($goods_fee, $share_discount);

        if(1 == $trade_refund_states)
        {
            $order_refund_status = 2;
        }
        if(3 == $trade_refund_states)
        {
            $order_refund_status = 5;
        }

        if (2 == $delivery_term){
            $order_paid = 0;
        }else{
            $order_paid = bcadd($share_amount, $share_post);
        }

        $order_list[] = array
        (
            'shop_id' => $shop_id,
            'rec_id' => 0,
            'platform_id' => 14,
            'tid' => $tid,
            'oid' => $o->order_id.$o->barcode,

            'status' => $trade_status,
            'refund_status' => $order_refund_status,

            'goods_id' => $o->art_no,
            'spec_id' => $o->barcode,
            'goods_no' => $goods_no,
            'spec_no' => $spec_no,
            'goods_name' => iconv_substr($o->product_name,0,255,'UTF-8'),
            'spec_name' => '',

            'num' => $num,
            'price' => $price,
            'discount' => 0,
            'adjust_amount' => 0,
            'share_post' => $share_post,
            'share_discount' => $share_discount,
            'total_amount' => $goods_fee,
            'share_amount' => $share_amount,
            'paid' => $order_paid,

            'created' => array('NOW()')
        );
    }

    $trade_list[] = array
    (
        'tid' => $tid,
        'platform_id' => 14,
        'shop_id' => $shop->shop_id,

        'process_status' => $process_status, 		//处理订单状态
        'trade_status' => $trade_status,			//平台订单状态
        'refund_status' => $trade_refund_states, 	//退货状态
        'pay_status' => $pay_status,

        'trade_time' => dateValue($t->add_time),	//下单时间
        'pay_time' => dateValue($t->add_time),		//支付时间

        'buyer_nick' => '',
        'buyer_message' => iconv_substr(@$t->remark,0,1024,'UTF-8'),				//买家备注
        'receiver_name' => iconv_substr($t->buyer,0,40,'UTF-8'),
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
        'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
        'receiver_zip' => $t->postcode,
        'receiver_mobile' => iconv_substr($t->mobile,0,40,'UTF-8'),
        'receiver_telno' => @$t->tel,
        'receiver_hash' => md5(@$t->buyer.$receiver_area.$receiver_address.$t->mobile.@$t->tel.@$t->postcode),

        'order_count' => $order_count,
        'goods_count' => $goods_count,

        'to_deliver_time' => $t->transport_day,
        'logistics_type' => -1,

        'goods_amount' => $goods_amount,
        'post_amount' => $post_fee,
        'discount' => $trade_discount,
        'receivable' => $receivable,
        'paid' => $receivable,

        'invoice_type' => $invoice_type,
        'invoice_title' => $invoice_title,
        'invoice_content' => $invoice_content,
        'platform_cost' => 0,
        'delivery_term' => $delivery_term, 	//是否货到付款
        'pay_id' => '', 					//支付宝账号
        'remark' => '',						//客服备注

        'cod_amount' => 2 == $delivery_term ? $receivable : 0,
        'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
        'refund_amount' => 0,
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,

        'created' => array('NOW()')
    );

    if(bccomp($t->promo_discount_amount, 0))
    {
        $discount_list[] = array
        (
            'platform_id' => 14,
            'tid' => $tid,
            'oid' => '',
            'sn' => '',
            'type' => 'sales promotion',
            'name' => '促销优惠',
            'is_bonus' => 0,
            'detail' => '',
            'amount' => $t->promo_discount_amount,
            'created' => array('NOW()')
        );
    }

    if(bccomp($t->discount_amount, 0))
    {
        $discount_list[] = array
        (
            'platform_id' => 14,
            'tid' => $tid,
            'oid' => '',
            'sn' => '',
            'type' => '',
            'name' => '优惠金额',
            'is_bonus' => 0,
            'detail' => '',
            'amount' => $t->discount_amount,
            'created' => array('NOW()')
        );
    }
    return true;
}
//批量获取订单详情
function vipshopDownloadTradeDetails($db, $sid, $appkey, $appsecret, $shop, &$trade, &$trade_list, &$order_list, &$discount_list)
{
    $pageSize = 100;
    $tids = array();
    for($j =0; $j < count($trade); $j++)
    {
        $tids[] = $trade[$j]->order_id;
    }

    $tid_avgs = implode(',',$tids);

    try
    {
        require_once TOP_SDK_DIR."/vipshop/vipapis/delivery/DvdDeliveryServiceClient.php";
        $service = \vipapis\delivery\DvdDeliveryServiceClient::getService();
        $ctx = \Osp\Context\InvocationContextFactory::getInstance();
        $ctx->setAppKey($appkey);
        $ctx->setAppSecret($appsecret);
        $ctx->setAccessToken($shop->session);
        $ctx->setAppURL("https://gw.vipapis.com/");
        $ctx->setLanguage("zh");
        $retval=$service->getOrderDetail($shop->account_id,$tid_avgs,1,$pageSize);
    }
    catch(\Osp\Exception\OspException $e)
    {
        if(API_RESULT_OK != vipshopErrorTest($e, $db, $shop->shop_id))
        {
            $error_msg = $e->returnMessage;
            logx("ERROR $sid vipshopDownloadTradeDetails vipshop->execute fail! error_code:{$e->returnCode} error_msg:{$e->returnMessage}", $sid.'/TradeSlow','error');
            return false;
        }
        logx("vipshop getOrderDetail fail !! error_code:{$e->returnCode} error_msg:{$e->returnMessage}",$sid.'/TradeSlow');
        return false;
    }
    $total_details = intval($retval->total);
    $pages = ceil(floatval($total_details)/$pageSize);
    if($total_details <= $pageSize)
    {
        $details = $retval->orderDetails;
    }
    else
    {
        for($i=$pages; $i>0; $i--)
        {
            try
            {
                $retval=$service->getOrderDetail($shop->account_id,$tid_avgs,$i,$pageSize);
            }
            catch(\Osp\Exception\OspException $e)
            {
                if(API_RESULT_OK != vipshopErrorTest($e, $db, $shop->shop_id))
                {
                    logx("ERROR $sid vipshopDownloadTradeDetails vipshop->execute fail2! error_code:{$e->returnCode} error_msg:{$e->returnMessage}", $sid.'/TradeSlow','error');
                    return false;
                }
                logx("getOrderDetail fail 2!! error_code:{$e->returnCode} error_msg:{$e->returnMessage}",$sid.'/TradeSlow');
                return false;
            }
            for($j = 0; $j < count($retval->orderDetails); $j++)
            {
                $details[] = $retval->orderDetails[$j];
            }
        }
    }


    for($i = 0; $i < count($trade); $i++)
    {
        $tid = $trade[$i]->order_id;

        for($j = 0; $j < count($details); $j++)
        {
            if($details[$j]->order_id == $tid)
            {
                $trade[$i]->orderDetails[] = $details[$j];
            }
        }

        if(!loadVipShopTradeImpl($sid, $appkey, $appsecret, $shop, $trade[$i], $trade_list, $order_list, $discount_list))
        {
            continue;
        }
    }
    return true;
}
