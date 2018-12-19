<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/TradesSoldIncrementGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TradesSoldGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TradeFullinfoGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbTradeorderGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/MaPackcodeCreateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TradeMemoUpdateRequest.php');


function downTopTradesDetail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg) {
    $new_trade_count = 0;
    $chg_trade_count = 0;

    $sid     = $trades->sid;
    $shopId  = $trades->shop_id;
    $session = $trades->session;

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new TradeFullinfoGetRequest();

    $req->setFields('tid,step_trade_status,adjust_fee,alipay_no,buyer_email,buyer_message,buyer_nick,created,pay_time,payment,post_fee'
        . ',receiver_address,receiver_city,receiver_district,receiver_mobile,receiver_name,receiver_phone,receiver_state,receiver_zip'
        . ',seller_cod_fee,seller_memo,seller_flag,shipping_type,snapshot_url,price,status,invoice_name,total_fee,type,refund_status'
        . ',buyer_area,buyer_alipay_no,end_time,received_payment,commission_fee,trade_from,rx_audit_status'
        . ',orders,service_orders,cod_fee,cod_status,buyer_cod_fee,seller_cod_fee,is_lgtype,promotion_details,step_paid_fee,is_daixiao'
        . ',invoice_kind,buyerTaxNO,modified');

    $tids = &$trades->tids;

    $trade_list    = array();
    $order_list    = array();
    $discount_list = array();

    for ($i = 0; $i < count($tids); $i++) {
        $tid = $tids[ $i ];
        $req->setTid($tid);
        $retval = $top->execute($req, $session);
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            logx("ERROR $sid top_detail $tid", $sid . "/TradeTaobao",'error');
            return TASK_SUSPEND;
        }

        logx('step2:开始解密',$sid.'/TradeTaobao');
        if (!loadTradeImpl($db, $appkey, $appsecret, $trades, $retval, $trade_list, $order_list, $discount_list)) {
            continue;
        }
        logx('step3:解密结束',$sid.'/TradeTaobao');
        ++$scan_count;

        //写数据库
        resetAlarm();
        if (count($order_list) >= 100) {
            if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
                return TASK_SUSPEND;
            }
        }
    }

    //保存剩下的到数据库
    if (count($order_list) > 0) {
        if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
            return TASK_SUSPEND;
        }
    }

    return TASK_OK;
}

//异步下载
function topDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg) {
    $cbp = function (&$trades) use ($trade_detail_cmd) {
        pushTask($trade_detail_cmd, $trades);
        return true;
    };

    return topDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//同步下载
//countLimit	订单数限制
function topSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time,
                                  &$scan_count, &$total_new, &$total_chg, &$error_msg) {
    $scan_count = 0;
    $total_new  = 0;
    $total_chg  = 0;
    $error_msg  = '';

    $cbp = function (&$trades) use ($appkey, $appsecret, &$db, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg) {
        downTopTradesDetail($db, $appkey,
            $appsecret,
            $trades,
            $scan_count,
            $new_trade_count,
            $chg_trade_count,
            $error_msg);

        $total_new += $new_trade_count;
        $total_chg += $chg_trade_count;

        return ($scan_count < $countLimit);
    };

    return topDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}

//taobao下载订单列表
function topDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp) {
    $ptime = $end_time;

    if ($save_time)
        $save_time = $end_time;

    $sid    = $shop->sid;
    $shopId = $shop->shop_id;
    logx("TopDownloadShop $shopId start_time:" .
        date('Y-m-d H:i:s', $start_time) .
        " end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/TradeTaobao");

    //taobao
    $session        = $shop->session;
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new TradesSoldIncrementGetRequest();
    $req->setFields('tid');
    $req->setPageSize(40);
    $req->setType("guarantee_trade,auto_delivery,ec,cod,step,nopaid,fixed");

    $total_count = 0;
    $loop_count  = 0;

    while ($ptime > $start_time) {
        $loop_count++;
        if ($loop_count > 1) resetAlarm();

        if ($ptime - $start_time > 3600 * 24) $ptime = $end_time - 3600 * 24 + 1;
        else $ptime = $start_time;

        $req->setStartModified(date('Y-m-d H:i:s', $ptime));
        $req->setEndModified(date('Y-m-d H:i:s', $end_time));

         //取总订单条数
        $req->setPageNo(1);

        $retval = $top->execute($req, $session);
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            logx("ERROR $sid topDownloadTradeList", $sid . "/TradeTaobao",'error');
            return TASK_OK;
        }

        if (!isset($retval->trades) || count($retval->trades) == 0) {
            $end_time = $ptime + 1;
            logx("TopTrade $shopId count: 0", $sid . "/TradeTaobao");
            continue;
        }

        $trades = $retval->trades->trade;
        //总条数
        $total_results = intval($retval->total_results);
        $total_count += $total_results;
        //echo "total_results: $total_results\n";
        logx("TopTrade $shopId count: $total_results", $sid . "/TradeTaobao");

        //如果不足一页，则不需要再抓了
        if ($total_results <= count($trades)) {
            $tids = array();
            for ($j = 0; $j < count($trades); $j++) {
                $tids[] = $trades[ $j ]->tid;
            }

            if (count($tids) > 0) {
                $shop->tids = $tids;
                if (!$cbp($shop)) return TASK_SUSPEND;
            }
        } else //超过一页，第一页抓的作废，从最后一页开始抓
        {
            $total_pages = ceil(floatval($total_results) / 40);

            //$req->setUseHasNext(1);
            for ($i = $total_pages; $i >= 1; $i--) {
                $req->setPageNo($i);
                $retval = $top->execute($req, $session);
                if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"]   = $retval->error_msg;
                    logx("ERROR $sid topDownloadTradeList2", $sid . "/TradeTaobao",'error');
                    return TASK_OK;
                }

                $trades = $retval->trades->trade;
                $tids   = array();
                for ($j = 0; $j < count($trades); $j++) {
                    $tids[] = $trades[ $j ]->tid;
                }

                if (count($tids) > 0) {
                    $shop->tids = $tids;
                    if (!$cbp($shop)) return TASK_SUSPEND;
                }
            }
        }

        $end_time = $ptime + 1;
    }

    if ($save_time) {
        logx("order_last_synctime_{$shopId}".'上次抓单时间保存 top平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
    }

    return TASK_OK;
}

//下载待发货订单
function topDownloadWaitSendTradeList(&$db, $appkey, $appsecret, $shop, $days, &$trade_count, &$error_msg) {
    $sid    = $shop->sid;
    $shopId = $shop->shop_id;
    logx("topDownloadWaitSendTradeList $shopId", $sid . "/TradeTaobao");

    //taobao
    $session        = $shop->session;
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new TradesSoldGetRequest();
    $req->setFields('tid');
    $req->setPageSize(100);
    $req->setStatus('WAIT_SELLER_SEND_GOODS');

    $trade_count = 0;

    $end_time   = time();
    $start_time = $end_time - 3600 * 24 * $days;

    $loop_count = 0;
    $ptime      = $end_time;

    while ($ptime > $start_time) {
        $loop_count++;
        if ($loop_count > 1) resetAlarm();

        if ($ptime - $start_time > 3600 * 24) $ptime = $end_time - 3600 * 24 + 1;
        else $ptime = $start_time;

        $req->setStartCreated(date('Y-m-d H:i:s', $ptime));
        $req->setEndCreated(date('Y-m-d H:i:s', $end_time));

        //取总订单条数
        $req->setPageNo(1);

        $retval = $top->execute($req, $session);
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            logx("ERROR $sid topDownloadWaitSendTradeList", $sid . "/TradeTaobao",'error');
            return TASK_OK;
        }

        if (!isset($retval->trades) || count($retval->trades) == 0) {
            $end_time = $ptime + 1;
            logx("TopTrade $shopId count: 0", $sid . "/TradeTaobao");
            continue;
        }

        $trades = $retval->trades->trade;
        //总条数
        $total_results = intval($retval->total_results);
        $trade_count += $total_results;
        logx("TopTrade $shopId count: $total_results", $sid . "/TradeTaobao");

        //如果不足一页，则不需要再抓了
        if ($total_results <= count($trades)) {
            $tids = array();
            for ($j = 0; $j < count($trades); $j++) {
                $tids[] = $trades[ $j ]->tid;
            }

            if (count($tids) > 0) {
                $shop->tids = $tids;
                pushTask('trade', 'trade_get', $shop);
            }
        } else //超过一页，第一页抓的作废，从最后一页开始抓
        {
            $total_pages = ceil(floatval($total_results) / 40);

            //$req->setUseHasNext(1);
            for ($i = $total_pages; $i >= 1; $i--) {
                $req->setPageNo($i);
                $retval = $top->execute($req, $session);
                if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"]   = $retval->error_msg;
                    logx("ERROR $sid topDownloadWaitSendTradeList", $sid . "/TradeTaobao",'error');
                    return TASK_OK;
                }

                $trades = $retval->trades->trade;
                $tids   = array();
                for ($j = 0; $j < count($trades); $j++) {
                    $tids[] = $trades[ $j ]->tid;
                }

                if (count($tids) > 0) {
                    $shop->tids = $tids;
                    pushTask('trade', 'trade_get', $shop);
                }
            }
        }

        $end_time = $ptime + 1;
    }

    return TASK_OK;
}

//taobao下载订单列表 (根据nickname)
function topDownloadTradeListByNickname(&$db, $appkey, $appsecret, $shop, $nick_name, &$scan_count, &$total_new, &$total_chg, &$error_msg) {
    $scan_count = 0;
    $total_new  = 0;
    $total_chg  = 0;

    $sid    = $shop->sid;
    $shopId = $shop->shop_id;
    logx("topDownloadTradeListByNickname shopId: $shopId nickname: $nick_name", $sid . "/TradeTaobao");

    //$total_count = 0;
    $now        = time();
    $start_time = date('Y-m-d H:i:s', $now - 3600 * 24 * 30);//搜最近30天
    $end_time   = date('Y-m-d H:i:s', $now);

    //taobao
    $session        = $shop->session;
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new TradesSoldGetRequest();
    $req->setFields('tid');
    $req->setPageSize(40);

    $req->setStartCreated($start_time);
    $req->setEndCreated($end_time);
    $req->setBuyerNick($nick_name);

    //取总订单条数
    $req->setPageNo(1);

    $retval = $top->execute($req, $session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
        $error_msg["status"] = 0;
        $error_msg["info"]   = $retval->error_msg;
        logx("ERROR $sid topDownloadTradeListByNickname", $sid . "/TradeTaobao",'error');
        return TASK_OK;
    }

    if (!isset($retval->trades) || count($retval->trades) == 0) {
        logx("ERROR $sid topDownloadTradeListByNickname2", $sid . "/TradeTaobao",'error');
        return TASK_OK;
    }

    $trades = $retval->trades->trade;
    //总条数
    $total_results = intval($retval->total_results);
    //$total_count += $total_results;
    //echo "total_results: $total_results\n";
    logx("topDownloadTradeListByNickname $shopId count: $total_results", $sid . "/TradeTaobao");

    //如果不足一页，则不需要再抓了
    if ($total_results <= count($trades)) {
        $tids = array();
        for ($j = 0; $j < count($trades); $j++) {
            $tids[] = $trades[ $j ]->tid;
        }

        if (count($tids) > 0) {
            $shop->tids = $tids;

            downTopTradesDetail($db,
                $appkey,
                $appsecret,
                $shop,
                $scan_count,
                $new_trade_count,
                $chg_trade_count,
                $error_msg);

            $total_new += $new_trade_count;
            $total_chg += $chg_trade_count;
        }
    } else //超过一页，第一页抓的作废，从最后一页开始抓
    {
        $total_pages = ceil(floatval($total_results) / 40);

        //$req->setUseHasNext(1);
        for ($i = $total_pages; $i >= 1; $i--) {
            $req->setPageNo($i);
            $retval = $top->execute($req, $session);
            if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
                $error_msg["status"] = 0;
                $error_msg["info"]   = $retval->error_msg;
                logx("ERROR $sid topDownloadTradeList", $sid . "/TradeTaobao",'error');
                return TASK_OK;
            }

            $trades = $retval->trades->trade;
            $tids   = array();
            for ($j = 0; $j < count($trades); $j++) {
                $tids[] = $trades[ $j ]->tid;
            }

            if (count($tids) > 0) {
                $shop->tids = $tids;

                downTopTradesDetail($db,
                    $appkey,
                    $appsecret,
                    $shop,
                    $scan_count,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                $total_new += $new_trade_count;
                $total_chg += $chg_trade_count;
            }
        }
    }

    return TASK_OK;
}

/*
	注意： 主订单如果处理关闭或退款状态，子订单一定要是关闭或退款状态
		如果所有子订单都退款，主订单要进入--80已退款
		主订单的申请退款或部分退款一定要落到子订单上
*/
function loadTradeImpl(&$db, $appkey, $appsecret, $shop, &$trade, &$trade_list, &$order_list, &$discount_list) {
    $sid    = $shop->sid;
    $t      = &$trade->trade;
    $shopId = $shop->shop_id;
    if($t->status =='PAID_FORBID_CONSIGN') {
        logx("loadtopTrade tid:{$t->tid}, error_msg:拼团中订单不下载", $sid.'/TradeTaobao');
        return true;
    }

    //buyer_alipay_no 支付宝 ，buyer_nick 买家昵称，receiver_mobile 收货人的手机号码，receiver_name 收货人的姓名 解密
    try{
        if(isset($t->buyer_alipay_no)&& !empty($t->buyer_alipay_no))
        {
            $t->buyer_alipay_no = top_decode($t->buyer_alipay_no, 'simple', $shop->session, $sid, $shopId);
        }

        if(isset($t->buyer_nick)&& !empty($t->buyer_nick))
        {
            $t->buyer_nick = top_decode($t->buyer_nick, 'nick', $shop->session, $sid, $shopId);
        }

        if(isset($t->receiver_mobile)&& !empty($t->receiver_mobile))
        {
            $t->receiver_mobile = top_decode($t->receiver_mobile, 'phone', $shop->session, $sid, $shopId);
        }

        if(isset($t->receiver_phone)&& !empty($t->receiver_phone))
        {
            $t->receiver_phone = top_decode($t->receiver_phone, 'simple', $shop->session, $sid, $shopId);
        }

        if(isset($t->receiver_name)&& !empty($t->receiver_name))
        {
            $t->receiver_name = top_decode($t->receiver_name, 'receiver_name', $shop->session, $sid, $shopId);
        }

        if(isset($t->buyer_email)&& !empty($t->buyer_email))
        {
            $t->buyer_email = top_decode($t->buyer_email, 'simple', $shop->session, $sid, $shopId);
        }
    }catch (Exception $e)
    {
        logx('订单解密失败,抛出异常'.print_r($e->getMessage(),true));
        logx('trade:'.print_r($t,true));
        return false;
    }


    if((isset($t->buyer_alipay_no)&&$t->buyer_alipay_no == 'ERROR')||(isset($t->buyer_nick)&&$t->buyer_nick == 'ERROR') ||
        (isset($t->receiver_mobile)&&$t->receiver_mobile == 'ERROR') || (isset($t->receiver_phone)&&$t->receiver_phone == 'ERROR')	||
        (isset($t->receiver_name)&&$t->receiver_name == 'ERROR') ||(isset($t->buyer_email)&&$t->buyer_email == 'ERROR'))
    {
        logx("订单号：{$t->tid},支付账号：{$t->buyer_alipay_no},昵称:{$t->buyer_nick},手机号：{$t->receiver_mobile},
		电话：{$t->receiver_phone},收件人:{$t->receiver_name},邮箱:{$t->buyer_email},解密失败",$sid.'/TradeTaobao');
        logx("sid:{$sid}, shopid:{$shopId},tid:{$t->tid},解密失败.",$sid.'/TradeTaobao','error');
        return false;
    }

    if(isset($t->receiver_mobile)&&!is_numeric(trim($t->receiver_mobile)))
    {
        logx("订单号：{$t->tid},手机号：{$t->receiver_mobile},电话：{$t->receiver_phone},手机号为空或不是纯数字！",$sid.'/TradeTaobao');
        logx("sid:{$sid}, shopid:{$shopId},tid:{$t->tid},手机号:{$t->receiver_mobile},电话:{$t->receiver_phone},手机号为空或不是纯数字！",$sid.'/TradeTaobao','error');
    }
    //这种状态订单不下载
    if ($t->status == 'TRADE_NO_CREATE_PAY')
        return false;
    $tid = $t->tid;

    $invoice_type  = @empty($t->invoice_name) ? 0 : 1;
    $invoice_title = @$t->invoice_name;
    $invoice_content = !empty(json_decode(@$t->trade_attr) -> buyerTaxNO)? "纳税人识别号:".json_decode(@$t->trade_attr) -> buyerTaxNO." " : '';

    $order_count = 0;
    $goods_count = 0;

    $trade_refund_status = 0;    //退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $pay_status          = 0;    //0未付款1部分付款2已付款
    $delivery_term       = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款

    $is_virtual = ($t->shipping_type == 'virtual');

    //已付金额, 发货前已付
    $paid = 0;

    //付款处理
    if ($t->type == 'cod') {
        $delivery_term = 2;
        if ($t->cod_status == 'SIGN_IN') {
            $pay_status = 2;
        }
    } else if ($t->type == 'step') {
        if (!empty($t->step_paid_fee)) {
            $paid = $t->step_paid_fee;
        }
        switch ($t->step_trade_status) {
            case 'FRONT_PAID_FINAL_NOPAID':
                $pay_status = 1;
                break;
            case 'FRONT_PAID_FINAL_PAID':    //paid == payment
                $pay_status = 2;
                break;
        }
    } else if ($t->status == 'WAIT_SELLER_SEND_GOODS' ||
        $t->status == 'WAIT_BUYER_CONFIRM_GOODS' ||
        $t->status == 'TRADE_BUYER_SIGNED' ||
        $t->status == 'TRADE_FINISHED' ||
        $t->status == 'SELLER_CONSIGNED_PART'
    ) {
        if (!empty($t->payment)) {
            $paid = $t->payment;
        }
        $pay_status = 2;
    } else if ($t->status == 'TRADE_CLOSED') {
        $paid = 0;
    }


    //订单当前状态
    $trade_status   = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    $is_external    = 0;    //

    //订单递交条件: process_status=待递交
    //订单递交到审核的条件: process_status=待递交 AND trade_status=待发货

    ////////////////////////////////////////////////////
    //订单状态
    switch ($t->status) {
        case 'TRADE_CLOSED_BY_TAOBAO': {
            $trade_status = 90;    //递交要针对货到付款单独处理
        }
            break;
        case 'WAIT_SELLER_SEND_GOODS': {
            if ($delivery_term == 2 && $t->cod_status != 'NEW_CREATED') {
                $trade_status = 50;
                $is_external  = 1;
            } else if ($pay_status == 2 || $delivery_term == 2) {
                $trade_status   = 30;
                $process_status = 10;
            } else {
                $trade_status   = 20;    //待尾款
                $process_status = 10;
            }
        }
            break;
        case 'TRADE_CLOSED': {
            $trade_status        = 80;
            $trade_refund_status = 3;
        }
            break;
        case 'WAIT_BUYER_CONFIRM_GOODS': {
            $trade_status = 50;
            $is_external  = 1;
        }
            break;
        case 'TRADE_BUYER_SIGNED': {
            $trade_status = 60;
            $is_external  = 1;
        }
            break;
        case 'TRADE_FINISHED': {
            $trade_status = 70;
            $is_external  = 1;
        }
            break;
        case 'SELLER_CONSIGNED_PART': {
            $trade_status = 40;
            $is_external  = 1;
        }
            break;
        case 'PAY_PENDING':
        case 'WAIT_BUYER_PAY': {
            $process_status = 10;
        }
            break;
        default: {
            logx("ERROR $sid invalid_trade_status $tid {$t->status}", $sid . "/TradeTaobao",'error');
        }
    }

    //货品信息
    $orders = &$t->orders->order;
    //总折扣
    $total_discount = 0;
    //邮费
    $post_fee = $t->post_fee;
    //未优惠总货款
    $total_fee = $t->total_fee;
    //以下为邮费、已付时行分摊
    $left_post = $t->post_fee;
    $left_paid = $paid;

    $order_rows      = count($orders);
    $no_refund_order = 0;


    if ($trade_status == 80) //只有一个子订单,且退款
    {
        $trade_refund_amount = $t->payment;
        $goods_amount        = 0;
    } else {
        //总退款金额
        $trade_refund_amount = 0;
        //算一下总共退款了多少
        $goods_amount = $total_fee;

        for ($k = 0; $k < $order_rows; $k++) {
            $o = &$orders[ $k ];
            if (($trade_status == 80 || $o->status == 'TRADE_CLOSED')) {
                $share_discount = ifEmpty(@$o->part_mjz_discount);
                $share_amount   = ifEmpty(@$o->divide_order_fee);
                //淘宝未付款前，不会分摊
                if (bccomp($share_amount, '0') <= 0) $share_amount = bcsub($o->total_fee, $share_discount);
                $refund_amount       = bcsub($share_amount, $o->payment);
                $trade_refund_amount = bcadd($trade_refund_amount, $refund_amount);
                //未折扣的货品总价
                $goods_amount = bcsub($goods_amount, bcmul($o->price, $o->num));
            }
        }
    }

    for ($k = 0; $k < $order_rows; $k++) {
        $o = &$orders[ $k ];

        if ($o->num <= 0) {
            logx("invalid_order " . print_r($o, true), $sid . "/TradeTaobao",'error');
            continue;
        }

        $goods_no = trim(@$o->outer_iid);
        $spec_no  = trim(@$o->outer_sku_id);
        if (iconv_strlen($goods_no, 'UTF-8') > 40 || iconv_strlen($spec_no, 'UTF-8') > 40) {
            logx("$sid GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t{@$o->title}", $sid . "/TradeTaobao",'error');

            $message = '';
            if (iconv_strlen($goods_no, 'UTF-8') > 40)
                $message = "货品商家编码超过40字符:{$goods_no}";
            if (iconv_strlen($spec_no, 'UTF-8') > 40)
                $message = "{$message}规格商家编码超过40字符:{$spec_no}";

            //发即时消息
            $msg = array(
                'type'     => 10,
                'topic'    => 'trade_deliver_fail',
                'distinct' => 1,
                'msg'      => $message
            );
            SendMerchantNotify($sid, $msg);

            $goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
            $spec_no  = iconv_substr($spec_no, 0, 40, 'UTF-8');
        }

        ++$order_count;
        $goods_count += (int)$o->num;

        $status        = 10;            //平台状态： 平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
        $refund_status = 0;        //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
        $order_type    = 0;        //0正常货品 1虚拟货品 2服务

        if ($trade_status == 80 || $trade_status == 90) {
            $status = $trade_status;
        } else {
            switch ($o->status) {
                case 'WAIT_SELLER_SEND_GOODS':
                    if ($delivery_term == 2 && $t->cod_status != 'NEW_CREATED')
                        $status = 50; //已发货
                    else if ($pay_status == 2 || $delivery_term == 2)
                        $status = 30; //待发货
                    else
                        $status = 20; //待尾款
                    break;
                case 'WAIT_BUYER_CONFIRM_GOODS':
                    $status = 50;
                    break;
                case 'TRADE_BUYER_SIGNED':
                    $status = 60;
                    break;
                case 'TRADE_FINISHED':
                    $status = 70;
                    break;
                case 'TRADE_CLOSED':
                    $status = 80;
                    break;
                case 'TRADE_CLOSED_BY_TAOBAO':
                    $status = 90;
                    break;
            }
        }

        //退款金额
        $refund_amount = 0;
        if ($trade_refund_status == 3) {
            $refund_status = 5;
        } else {
            switch (@$o->refund_status) {
                case 'WAIT_SELLER_AGREE':
                    $refund_status = 2;
                    if ($trade_refund_status == 0) $trade_refund_status = 1;
                    break;
                case 'WAIT_BUYER_RETURN_GOODS':
                    $refund_status = 3;
                    if ($trade_refund_status < 2) $trade_refund_status = 2;
                    break;
                case 'WAIT_SELLER_CONFIRM_GOODS':
                    $refund_status = 4;
                    if ($trade_refund_status < 2) $trade_refund_status = 2;
                    break;
                case 'SELLER_REFUSE_BUYER':
                case 'CLOSED':
                    $refund_status   = 1;
                    $no_refund_order = 1;
                    break;
                case 'SUCCESS':
                    $refund_status = 5;
                    if ($trade_refund_status < 2) $trade_refund_status = 2;
                    break;
                default:
                    $no_refund_order = 1;
            }
        }

        if ($is_virtual) $order_type = 1;
        else if (@$o->is_service_order) $order_type = 2;

        $share_discount = ifEmpty(@$o->part_mjz_discount);
        $share_amount   = ifEmpty(@$o->divide_order_fee);

        //淘宝未付款前，不会分摊
        if (bccomp($share_amount, '0') <= 0) {
            $share_amount = bcsub($o->total_fee, $share_discount);
        }
        //关于退款订单的$o->payment, 需要找一个有退款的子订单来分析

        //邮费\已付分摊
        //如果整单退款,sum(子订单邮费)!=主订单邮费
        if ($trade_status == 80) {
            $refund_amount = $share_amount;
            $share_post    = 0;
            $share_paid    = 0;
        } else if ($status == 80 && bccomp(bcadd($share_amount, $share_discount), $o->payment)) //已退款
        {
            //退款成功,客户支付还可以不为0
            $refund_amount = bcsub($share_amount, $o->payment);

            $share_post = 0;
            $share_paid = $o->payment;
            $paid       = bcsub($paid, $refund_amount);
            $left_paid  = bcsub($left_paid, $share_amount);
        } else if ($trade_status == 90) //主订单取消
        {
            $share_post = '0';
            $share_paid = '0';
        } else if ($k == $order_rows - 1) //最后一个子订单
        {
            $share_post = $left_post;
            $left_post  = '0';
            $share_paid = $left_paid;
            $left_paid  = '0';

            if ($status == 90 && (bccomp($share_post, '0') || bccomp($share_paid, '0'))) {
                logx("ERROR $sid closed_order_non_zero_share: $tid $share_post $share_paid", $sid . "/TradeTaobao",'error');
            }
        } else if ($status == 90) {
            $share_post = '0';
            $share_paid = '0';
        } else {
            if ($status == 80) {
                $share_post = 0;
            } else {
                $share_post = bcdiv(bcmul(bcmul($o->num, $o->price), $post_fee), $goods_amount);
                $left_post  = bcsub($left_post, $share_post);
            }

            $share_paid = bcadd($share_amount, $share_post);
            if (bccomp($left_paid, $share_paid) >= 0) {
                $left_paid = bcsub($left_paid, $share_paid);
            } else {
                $share_paid = $left_paid;
                $left_paid  = '0';
            }
        }

        if ($status != 90) //关闭的订单，支付、优惠都不计入
            $total_discount = bcsub(bcadd($total_discount, bcadd($o->discount_fee, $share_discount)), $o->adjust_fee);

        //保存至 g_api_tradegoods
        $order_list[] = array
        (
            'platform_id'    => 1,
            "shop_id"        => $shopId,
            'tid'            => $tid,
            'oid'            => $o->oid,
            'status'         => $status,
            'refund_status'  => $refund_status,
            'order_type'     => $order_type,
            'invoice_type'   => $invoice_type,
            'bind_oid'       => trim(@$o->bind_oid),
            'goods_id'       => trim($o->num_iid),
            'spec_id'        => trim(@$o->sku_id),
            'goods_no'       => $goods_no,
            'spec_no'        => $spec_no,
            'goods_name'     => iconv_substr(@$o->title, 0, 255, 'UTF-8'),
            'spec_name'      => iconv_substr(@$o->sku_properties_name, 0, 100, 'UTF-8'),
            'refund_id'      => @$o->refund_id,
            'num'            => $o->num,
            'price'          => $o->price,
            'adjust_amount'  => $o->adjust_fee,        //手工调整,特别注意:正的表示加价,负的表示减价
            'discount'       => $o->discount_fee,            //子订单折扣
            'share_discount' => $share_discount,    //分摊优惠
            'total_amount'   => $o->total_fee,        //分摊前扣除优惠货款num*price+adjust-discount
            'share_amount'   => $share_amount,        //分摊后货款num*price+adjust-discount-share_discount
            'share_post'     => $share_post,            //分摊邮费
            'refund_amount'  => $refund_amount,
            'is_auto_wms'    => 0,
            'wms_type'       => 0,
            'warehouse_no'   => '',
            'logistics_no'   => @$o->invoice_no,
            'paid'           => $share_paid,
            'cid'            => @$o->cid,
            'created'        => array('NOW()')
        );

        if (bccomp($o->adjust_fee, 0)) {
            $discount_list[] = array
            (
                'platform_id' => 1,
                'tid'         => $tid,
                'oid'         => $o->oid,
                'sn'          => '',
                'type'        => 'order_adjust',
                'name'        => '客服调价',
                'is_bonus'    => 0,
                'detail'      => '客服调价',
                'amount'      => bcsub(0, $o->adjust_fee)
            );
        }
    }

    //如果所有子订单都退款了，主订单状态为全部退款
    if ($trade_refund_status == 2 && $no_refund_order == 0)
        $trade_refund_status = 3;

    //订单信息
    $mobile = trim(@$t->receiver_mobile);
    $telno  = trim(@$t->receiver_phone);
    if ($mobile == $telno) $telno = '';

    //可选仓库类别
    $wms_type = 0;
    //物流宝货品编码
    $wlb_warehouse = '';
    //物流宝订单号
    $wlb_trade_no = '';

    /*弃用物流宝 去掉对物流宝判断
    //物流宝判断
    if (@$shop->wms_check && $t->status == 'WAIT_SELLER_SEND_GOODS')
    {
        $session = $shop->session;
        $top = new TopClient();
        $top->format = 'json';
        $top->appkey = $appkey;
        $top->secretKey = $appsecret;
        $req = new WlbTradeorderGetRequest();
        $req->setTradeType("TAOBAO");
        $req->setTradeId($tid);

        $retval = $top->execute($req, $session);

        if(API_RESULT_OK == topErrorTest($retval, $db, $shopId))
        {
            if (isset($retval->wlb_order_list->wlb_order) && count($retval->wlb_order_list->wlb_order) > 0)
            {
                $wms_type = 2;
                $wlb_warehouse = trim($retval->wlb_order_list->wlb_order[0]->store_code);
                $wlb_trade_no = trim($retval->wlb_order_list->wlb_order[0]->order_code);
            }
        }
        else
        {
            logx("WlbTradeorderGetRequest fail! $tid", $sid);
            logx("ERROR $sid WlbTradeorderGetRequest $tid", 'error');
        }
    }
    */

    //淘宝,县级市
    $city     = trim(@$t->receiver_city);
    $district = trim(@$t->receiver_district);
    if (empty($city)) {
        $city     = $district;
        $district = '';
    }

    //express_agency_fee
    //credit_card_fee

    /*
        goods_amount 货款总额，优惠前的 = sum(price*num)
        post_amount	 邮费
        other_amount 其它从买家的收费

        discount	 折扣，即优惠
        receivable	 应收
        paid		 买家已付
        received	 已收,买家已确认,已经转到支付宝

        platform_cost平台费用，佣金
        dap_amount	 款到发货金额
        cod_amount	 货到付款金额
        pi_amount	 分期付款金额

        receivable = goods_amount+post_amount+other_amount-discount
        receivable = dap_amount+cod_amount+pi_amount-ext_cod_fee(第三方COD费用)

        incoming = receivable - platform_cost;

        taobao:
        discount = total+post_amount-payment;
        discount = sum(order.discount+order.share_discount-order.adjust)

        应收金额
        receivable = sum(order.share_amount + order.share_post)

        goods_amount-discount = sum(order.share_amount)
        post_amount = sum(order.share_post)
        other_amount = sum(order.share_other)

        trade.paid = sum(order.paid)

        o.share_amount = o.total_amount-o.discount-o.share_discount+o.adjust;
        //退款后订单变化???
    */

    $dap_amount    = 0;
    $cod_amount    = 0;
    $buyer_cod_fee = 0;

    if ($delivery_term == 2) {
        $buyer_cod_fee = empty($t->buyer_cod_fee) ? 0 : $t->buyer_cod_fee;
        $cod_amount    = bcsub($t->payment, $trade_refund_amount); //cod_amount包含COD服务费
        $receivable    = bcsub($cod_amount, $cod_amount);
    } else {
        $dap_amount = bcsub($t->payment, $trade_refund_amount);
        $receivable = $dap_amount;
    }

    /*总折扣*/
    $discount = bcadd(bcsub(bcadd($total_fee, $t->post_fee), $t->payment), $buyer_cod_fee);

    //检查折扣平衡
    if ($pay_status && bccomp($discount, $total_discount)) {
        logx("$sid top_discount_error: $tid $discount $total_discount", $sid . "/TradeTaobao",'error');
        logx(print_r($t, true), $sid . "/TradeTaobao");
    }

    $province = trim(@$t->receiver_state);
    if ($province == '台湾') $province = '台湾省';
    getAddressID($province, $city, $district, $province_id, $city_id, $district_id);
    if (!empty($district))
        $receiver_area = "$province $city $district";
    else
        $receiver_area = "$province $city";

    //物流类别
    $logistics_type = -1;    //未知物流
    if (@$t->shipping_type == 'post')
        $logistics_type = 2;//平邮
    else if (@$t->shipping_type == 'ems')
        $logistics_type = 3;//ems
    else if (@$t->shipping_type == 'virtual')
        $logistics_type = 0;

    //订单内部来源
    $trade_mask = 0;
    if (stripos(@$t->trade_from, 'WAP') !== FALSE)
        $trade_mask = 1;
    if (stripos(@$t->trade_from, 'JHS') !== FALSE)
        $trade_mask |= 2;
    //家装判断
    $summary = 0;
    if (isset($t->orders->tmser_spu_code) && !empty($t->orders->tmser_spu_code)) {
        $summary = 1;
    }

    $trade_list[] = array
    (
        'platform_id'       => 1,
        'shop_id'           => $shopId,
        'tid'               => $tid,
        'trade_status'      => $trade_status,
        'pay_status'        => $pay_status,
        'refund_status'     => $trade_refund_status,
        'process_status'    => $process_status,

        'delivery_term'     => $delivery_term,
        'trade_time'        => dateValue($t->created),
        'pay_time'          => dateValue(@$t->pay_time),

        'buyer_nick'        => iconv_substr(trim($t->buyer_nick), 0, 100, 'UTF-8'),
        'buyer_email'       => iconv_substr(trim(@$t->buyer_email), 0, 60, 'UTF-8'),
        'buyer_area'        => iconv_substr(@$t->buyer_area, 0, 40, 'UTF-8'),
        'pay_id'            => @$t->alipay_no,
        'pay_account'       => @$t->buyer_alipay_no,

        'receiver_name'     => iconv_substr(@$t->receiver_name, 0, 40, 'UTF-8'),
        'receiver_province' => $province_id,
        'receiver_city'     => $city_id,
        'receiver_district' => $district_id,
        'receiver_address'  => iconv_substr(@$t->receiver_address, 0, 256, 'UTF-8'),
        'receiver_mobile'   => iconv_substr($mobile, 0, 40, 'UTF-8'),
        'receiver_telno'    => iconv_substr($telno, 0, 40, 'UTF-8'),
        'receiver_zip'      => @$t->receiver_zip,
        'receiver_area'     => iconv_substr($receiver_area, 0, 64, 'UTF-8'),
        'to_deliver_time'   => '',

        'receiver_hash'     => md5(@$t->receiver_name . $receiver_area . @$t->receiver_address . $mobile . $telno . @$t->receiver_zip),
        'logistics_type'    => $logistics_type,

        'invoice_type'      => $invoice_type,
        'invoice_title'     => iconv_substr($invoice_title, 0, 255, 'UTF-8'),
        'invoice_content'   => iconv_substr($invoice_content, 0, 255, 'UTF-8'),

        'buyer_message'     => iconv_substr(@$t->buyer_message, 0, 1024, 'UTF-8'),
        'remark'            => iconv_substr(@$t->seller_memo, 0, 1024, 'UTF-8'),
        'remark_flag'       => (int)@$t->seller_flag,

        'end_time'          => dateValue(@$t->end_time),
        'wms_type'          => $wms_type,
        'warehouse_no'      => iconv_substr($wlb_warehouse, 0, 40, 'UTF-8'),
        'stockout_no'       => $wlb_trade_no,
        'is_auto_wms'       => ($wms_type == 2 ? 1 : 0),
        'is_external'       => $is_external,

        'goods_amount'      => $goods_amount,
        'post_amount'       => $t->post_fee,
        'receivable'        => $receivable,
        'discount'          => $discount,
        'paid'              => $paid,
        'received'          => ifEmpty(@$t->received_payment),

        'platform_cost'     => (bcadd(@$t->commission_fee, @$t->seller_cod_fee)),

        'order_count'       => $order_count,
        'goods_count'       => $goods_count,

        'cod_amount'        => $cod_amount,
        'dap_amount'        => $dap_amount,
        'ext_cod_fee'       => $buyer_cod_fee,

        'refund_amount'     => $trade_refund_amount,
        'trade_mask'        => $trade_mask,
        'score'             => ifEmpty(@$t->point_fee),
        'real_score'        => ifEmpty(@$t->real_point_fee),
        'got_score'         => ifEmpty(@$t->buyer_obtain_point_fee),
        'logistics_no'      => '',
        'created'           => array('NOW()')
    );

    //优惠信息
    $promotion_details = &$t->promotion_details->promotion_detail;
    for ($k = 0; $k < count($promotion_details); $k++) {
        $p = &$promotion_details[ $k ];

        $arr = explode('-', $p->promotion_id);
        if (count($arr) > 0)
            $is_bonus = ($arr[0] == 'shopbonus') ? 1 : 0;
        else
            $is_bonus = 0;

        $discount_list[] = array
        (
            'platform_id' => 1,
            'tid'         => $tid,
            'oid'         => '',
            'sn'          => $p->id,
            'type'        => $p->promotion_id,
            'name'        => $p->promotion_name,
            'is_bonus'    => $is_bonus,
            'detail'      => $p->promotion_desc,
            'amount'      => $p->discount_fee
        );
    }

    //费用组成


    return true;
}

//生成包裹码二维码图片链接
function top_print_packcode(&$db, $shop_id, $appkey, $appsecret, $session, &$rows, &$error_msg) {
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;

    $req    = new MaPackcodeCreateRequest();
    $retval = $top->execute($req, $session);

    if (API_RESULT_OK != topErrorTest($retval, $db, $shop_id)) {
        $error_msg["status"] = 0;
        $error_msg["info"]   = $retval->error_msg;
        return false;
    } else {
        $rows = array(array(json_encode(array('status'    => 0,
            'qrcodeurl' => $retval->qrcodeurl,
            'msg'       => ''))));
    }

    return true;
}

//修改交易备注
function topUpdateTradeMemo(&$db, $appkey, $appsecret, $trades , &$error_msg)
{
    $opStatus = true;
    $sid = $trades->sid;
    $shop_id = $trades->shop_id ;
    $session = $trades->session;

    $trade = $trades->trade;

    $tid = $trade->tid;
    $memo = trim($trade->memo);
    $flag = intval($trade->flag);

    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;

    $req = new TradeMemoUpdateRequest();
    $req->setTid($tid);

    if(!empty($memo))
    {
        //备注更新模式默认为追加更新
        $req->setMemo($memo);
    }
    if($flag != -1)
    {
        $req->setFlag($flag);
    }

    logx("upload: memo[{$memo}],flag[{$flag}]", $sid.'/TradeTaobao');
    $retval = $top->execute($req, $session);
    logx('topUpdateTradeMemo retval:' . print_r($retval,true),$sid.'/TradeTaobao');
    if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
    {
        logx('topUpdateTradeMemo req:' . print_r($req,true),$sid.'/TradeTaobao');

        $error_msg = $retval->error_msg;
        $trade->error_msg = $error_msg;
        logx("ERROR $sid topUpdateTradeMemo  $tid  $flag  $memo {$error_msg} ",$sid.'/TradeTaobao', 'error');
        $opStatus = false;
    }

    sync_remark_log($db,$trade,$opStatus,$sid);

    return True;
}

?>