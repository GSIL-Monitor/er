<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR.'/kl/klClient.php');

function klProvince($province)
{
    global $spec_province_map;
    if (empty($province)) {
        return '';
    }
    if (iconv_substr($province, -1, 1, 'UTF-8') != '省') {
        $prefix = iconv_substr($province, 0, 2, 'UTF-8');
        if (isset($spec_province_map[$prefix])) {
            return $spec_province_map[$prefix];
        }
        return $province.'省';
    }
    return $province;
}

function klDownloadTradeList(&$db, $appkey, $appsecret, &$shop,$countLimit, $start_time, $end_time, $save_time, &$total_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $ptime = $end_time;
    if ($save_time) {
        $save_time = $end_time;
    }

    $shop_id = $shop->shop_id;
    $sid = $shop->sid;
    $session = $shop->session;
    logx ( "klDownloadTradeList $shop_id start_time:" . date ( 'Y-m-d H:i:s', $start_time ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid.'/TradeSlow' );

    $new_trade_count = 0;
    $chg_trade_count = 0;
    $loop_count = 0;
    $total_count = 0;
    $page = 1;
    $page_size = 40;

    $kl = new klClient();
    $kl->app_key = $appkey;
    $kl->app_secret = $appsecret;
    $kl->access_token = $session;
    $kl->method = "kaola.order.search";

    $params = array();
    while ($ptime > $start_time) {
        $ptime = ($ptime - $start_time > 3600*24) ? ($end_time - 3600 * 24 + 1) : ($start_time);
        $loop_count++;
        if ($loop_count > 1) {
            resetAlarm();
        }
        $params['order_status'] = 1;
        $params['date_type'] = 1;
        $params['start_time'] = date('Y-m-d H:i:s',$ptime);
        $params['end_time'] = date('Y-m-d H:i:s',$end_time);
        $params['page_no'] = $page;
        $params['page_size'] = $page_size;


        $retval = $kl->execute($params);

        logx ( "klDownloadTradeList $shop_id start_time:" . date ( 'Y-m-d H:i:s', $ptime ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ), $sid.'/TradeSlow' );


        if (API_RESULT_OK != klErrorTest( $retval, $db, $shop_id )) {
            $error_msg['status'] = 0;
            $error_msg['info'] = $retval -> error_response -> msg;
            logx ( "klDownloadTradeList fail  sid:{$sid} errCode:{$retval->code} msg:{$error_msg['info']}", $sid.'/TradeSlow','error');
            return TASK_OK;
        }
        if ($retval->kaola_order_search_response->total_count == 0) {
            $end_time = $ptime + 1;
            logx ( "klDownloadTradeList $shop_id count: 0", $sid.'/TradeSlow' );
            continue;
        }

        $trades = $retval->kaola_order_search_response->orders;
        $total_result = $retval->kaola_order_search_response->total_count;
        logx("klDownloadTradeList $shop_id count : $total_result" ,$sid.'/TradeSlow');


        if ($total_result <= count($trades))
        {

            for ($i=0; $i < count($trades); $i++) {
                $trade = $trades[$i];

                $total_count += 1;
                if (! loadKlTrade( $db, $appkey, $appsecret, $shop, $trade, $trade_list, $order_list, $discount_list ))
                {
                    logx("loadKlTrade false",$sid.'/TradeSlow');
                    continue;
                }
            }
            if($countLimit && $total_count >= $countLimit)
                return TASK_SUSPEND;
        }
        else
        {
            $total_pages = ceil(floatval($total_result)/$page_size);

            for ($i=$total_pages; $i > 0; $i--)
            {
                logx("共{$total_pages} 页, 当前第 {$i} 页");
                $params['page_no'] = $i;
                $retval = $kl->execute($params);
                if (API_RESULT_OK != klErrorTest($retval, $db, $shop_id)) {
                    $error_msg['status'] = 0;
                    $error_msg['info'] = $retval -> error_response -> msg;
                    logx ( "klDownloadTradeList fail sid:{$sid} errCode:{$retval->code} msg:{$error_msg['info']}", $sid.'/TradeSlow','error' );
                    return TASK_OK;
                }
                $trades = $retval->kaola_order_search_response->orders;
                for ($s=0; $s < count($trades); $s++) {
                    $trade = $trades[$s];
                    $total_count += 1;
                    if (! loadKlTrade( $db, $appkey, $appsecret, $shop, $trade, $trade_list, $order_list, $discount_list ))
                    {
                        logx("loadKlTrade false",$sid.'/TradeSlow');
                        continue;
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
        logx("order_last_synctime_{$shop_id}".'上次抓单时间保存 kl平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shop_id}", $save_time);
    }
    return TASK_OK;
}

function downKlTradesDetail(&$db, $appkey, $appsecret, &$trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $sid = $trades->sid;
    $shop_id = $trades->shop_id;
    $tids = & $trades->tids;//订单编号
    $session = $trades->session;

    $kl = new klClient();
    $kl->app_key = $appkey;
    $kl->app_secret = $appsecret;
    $kl->access_token = $session;
    $kl->method = "kaola.order.get";

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $tid = $tids[0];
    $params['order_id'] = $tid;
    $retval = $kl->execute($params);

    if (API_RESULT_OK != klErrorTest($retval, $db, $shop_id ))
    {
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval -> error_response -> msg;
        logx ( "klDownloadTrade fail errCode:{$retval->errCode} error_msg:{$error_msg['info']} ", $sid.'/TradeSlow' );
        return TASK_OK;
    }
    $ret = $retval->kaola_order_get_response->order;
    if (! loadKlTrade($db, $appkey, $appsecret, $trades, $ret, $trade_list, $order_list, $discount_list ))
    {
        logx("loadKlTrade false",$sid.'/TradeSlow');
    }

    // 保存到数据库
    if (count ( $order_list ) > 0)
    {
        if (! putTradesToDb ( $db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid ))
        {
            return TASK_SUSPEND;
        }
    }

    return TASK_OK;
}

function loadKlTrade($db, $appkey, $appsecret, $trades, &$t, &$trade_list, &$order_list, &$discount_list )
{
    $shopId = $trades->shop_id;
    $sid = $trades->sid;
    $tid = $t->order_id;


    $process_status = 70;//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    $trade_status = 10;//订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭(付款前取消)
    $pay_status = 0;//0未付款1部分付款2已付款
    $delivery_term = 1;//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $trade_refund_status = 0;//退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;// 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功

    $paid = $t->order_real_price;//订单实际价格（用户实际支付价格）
    $receivable = 0;

    if (isset($t->order_status)) {
        $order_status = $t->order_status;
    }
    if (isset($t->order_status_name)) {
        $order_status = $t->order_status_name;
    }
    //订单状态
    switch ($order_status) {
        case '1'://已付款
            $process_status = 10;
            $trade_status = 30;
            $pay_status = 2;
            break;
        case '2'://已发货
            $process_status = 40;
            $trade_status = 50;
            $pay_status = 2;
            break;
        case '3'://已签收
            $process_status = 60;
            $trade_status = 60;
            $pay_status = 2;
            break;
        case '5'://取消待确认
            $process_status = 10;
            $pay_status = 2;
            $trade_refund_status = 1;
            $order_refund_status = 2;
            break;
        case '6'://已取消
            $trade_status = 90;
            $trade_refund_status = 3;
            $order_refund_status = 5;
            break;
        case '8'://发货处理中(8是处于已付款和待发货的一个中间状态,当查询条件为1的时候会返回该状态)
            $process_status = 10;
            $trade_status = 30;
            $pay_status = 2;
            break;
        default:
            logx ( "invalid_trade_status  sid:{$sid} $tid{$orderState}", $sid.'/TradeSlow','error' );
            break;
    }

    $receiver_name = $t->receiver_name;
    $receiver_mobile = $t->receiver_phone;
    $receiver_province = @klProvince($t->receiver_province_name);
    $receiver_city = $t->receiver_city_name;
    $receiver_district = $t->receiver_district_name;
    $receiver_address = $t->receiver_address_detail;
    $receiver_zip = @$t->receiver_post_code;
    $receiver_area = $receiver_province . " ".$receiver_city." ".$receiver_district;//省市区空格分隔
    getAddressID ( $receiver_province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );
    //deliver_time发货时间
    $trade_time = $t->pay_success_time;//下单时间
    $pay_time = $t->pay_success_time;//支付成功时间

    $invoice_type = 0;//默认不需要发票
    $post_amount = $t->express_fee;//邮费
    $invoice_content = '';
    $invoice_title = '';
    if (isset($t->need_invoice) && $t->need_invoice == 1) {
        $invoice_type = 1;
        $invoice_title = $t->invoice_title;//发票抬头
        $invoice_content = $t->invoice_amount;//发票金额
    }

    $order_count = count($t->order_skus);
    $goods_count = 0;
    $left_post = $post_amount;//邮费
    $spec_no = '';
    $activity_amount = 0;
    $goods_amount = 0;
    $total_trade_fee = bcsub($t->order_origin_price,$post_amount);

    $oidMap = array();
    $orderId = 1;

    for ($i=0; $i < count($t->order_skus); $i++) {
        $order_sku = $t->order_skus[$i];

        $goods_id = $order_sku->sku_key;
        $goods_no = $order_sku->goods_no;
        $spec_no = $order_sku->barcode;
        $goods_name = $order_sku->product_name;
        $price = $order_sku->origin_price;
        $num = $order_sku->count;
        $goods_count += $num;
        $goods_fee = $num * $price;
        $goods_amount += $goods_fee;//货款
        $goods_share_amount = $order_sku->activity_totle_amount + $order_sku->coupon_totle_amount;//子单分摊优惠=子单活动优惠总金额+优惠券优惠总金额
        $activity_amount += $order_sku->activity_totle_amount;
        if ($i == count($t->order_skus) - 1) {
            $share_post = $left_post;
        }else{
            $share_post = bcdiv(bcmul($post_amount, $goods_fee), $total_trade_fee);
            $left_post = bcsub($left_post, $share_post);
        }
        $share_amount = bcsub($goods_fee, $goods_share_amount);

        $order_paid = bcadd($share_amount, $share_post);

        $oid = $tid.':'.$goods_id;
        $oid2 = iconv_substr($oid, 0, 40, 'UTF-8');
        if(isset($oidMap[$oid2]))
        {
            $oid2 = $tid.':'.$orderId;
            ++$orderId;
        }
        $oidMap[$oid2] = 1;

        $order_list[] = array
        (
            'shop_id' => $shopId,
            'platform_id'=> 50,
            //交易编号
            'tid'=>$tid,
            //订单编号
            'oid'=> $oid2,
            'process_status' => $process_status,//处理订单状态
            'status'=> $trade_status,
            'refund_status'=> $order_refund_status,//退款标记：0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
            'goods_id'=> $goods_id,//平台货品ID
            'spec_id'=>'',//平台规格id
            //商家编码
            'goods_no'=> iconv_substr(@$goods_no,0,40,'UTF-8'),
            //规格商家编码
            'spec_no'=> $spec_no,
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
            'share_discount' => $goods_share_amount,//分摊优惠
            'share_amount'=>$share_amount,//分摊后子订单价格 单价*数量-分摊优惠
            'total_amount'=>$goods_fee,//总价格，不包含邮费 //商品价格 * 商品数量
            'share_post'=>$share_post,//分摊邮费
            'paid'=>$order_paid,//分摊已付金额+运费

            'created' => array('NOW()')
        );
    }

    $buyer_name = $t->cert_name;//买家姓名
    $id_card = $t->tax_fee;//身份证
    $discount = $t->coupon_amount + $activity_amount;//优惠券优惠金额


    $trade_list [] = array(
        'tid' => $tid,//订单号
        'platform_id' => 50,//平台id
        'shop_id' => $shopId,//店铺ID
        'process_status' => $process_status,//处理订单状态
        'trade_status' => $trade_status,//平台订单状态
        'refund_status'=>$trade_refund_status, 	//退货状态
        'pay_status' => $pay_status,

        'order_count' => $order_count,
        'goods_count' => $goods_count,//货品总数量，用于递交时检验

        'trade_time' => $trade_time,
        'pay_time' => $pay_time,

        'buyer_name' => $buyer_name,
        'id_card' => $id_card,
        'buyer_message' => '',
        'buyer_email' => '',
        'buyer_area' => '',
        'buyer_nick' => '',//买家帐号ID
        'invoice_type' => $invoice_type,//发票类别：0不需要，1普通发票,2增值税发票
        'invoice_title' => $invoice_title,//发票抬头
        'invoice_content' => $invoice_content,//发票内容

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
        'receivable' => $receivable,//应收金额
        'paid' => 2 == $delivery_term ? 0 : $paid,
        'received' => $paid,//已从平台收款的金额
        'cod_amount' => 2 == $delivery_term ? $paid : 0, //货到付款金额
        'dap_amount' => 2 == $delivery_term ? 0 : $paid, //款到发货金额
        'platform_cost' => 0,//平台费用

        'delivery_term' => $delivery_term,//
        'pay_id' => '',//平台支付订单ID,如支付宝的订单号
        'remark' => '',//客服备注
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,

        'created' => array('NOW()')
    );

    return true;

}















