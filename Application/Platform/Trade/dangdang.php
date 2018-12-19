<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Common/utils.php');
require_once(TOP_SDK_DIR . '/dangdang/DangdangClient.php');

function ddProvince($province)
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

function downDdTradesDetail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $sid = $trades->sid;
    
    $shopId = $trades->shop_id;
    $session = $trades->session;
        
    //API参数
    $params = array();
    
    $dd = new DangdangClient(DD_NEW_API_URL);
    $dd->setAppKey($appkey);
    $dd->setAppSecret($appsecret);
    $dd->setMethod('dangdang.order.details.get');
    $dd->setSession($session);
    $tids = & $trades->tids;//订单编号
    
    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        $params["o"] = $tid;
        
        $retval = $dd->sendByPost('getOrderDetail.php', $params, $appsecret);
        if(API_RESULT_OK != ddErrorTest($retval, $db, $shopId))
        {
            $error_msg['status'] = 0;
            $error_msg['info'] = (string)$retval->error_msg;
            logx("{$sid} downDdTradesDetail fail $tid 错误信息:{$error_msg['info']}", $sid.'/Trade','error');
            return TASK_SUSPEND;
        }
        
        if(!isset($retval->ItemsList) || !isset($retval->buyerInfo) || !isset($retval->sendGoodsInfo))
        {
            $error_msg['status'] = 0;
            $error_msg['info'] = '读取订单信息失败';
            logx("{$sid} downDdTradesDetail fail $tid 错误信息:{$error_msg['info']}", $sid.'/Trade','error');
            return TASK_SUSPEND;
        }
        
        if(!loadTradeImpl($db, 
        $appkey,
        $appsecret, 
        $trades, 
        $retval, 
        $trade_list, 
        $order_list, 
        $discount_list))
        {
            continue;
        }
        
        ++$scan_count;
        //写数据库
        if(count($order_list) >= 100)
        {
            if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
            {
                return TASK_SUSPEND;
            }
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

//异步下载
function ddDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
    $cbp = function(&$trades) use($trade_detail_cmd)
    {
        pushTask($trade_detail_cmd, $trades);
        return true;
    };
    
    return ddDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//同步下载
//countLimit    订单数限制
function ddSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, 
    &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
    $scan_count=0;
    $total_new=0;
    $total_chg=0;
    $error_msg='';
    
    $cbp = function(&$trades) use($appkey, $appsecret, &$db, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
    {
        downDdTradesDetail($db, $appkey, 
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
    
    return ddDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}

//dd下载订单列表
function ddDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{
    $ptime = $end_time;
    
    if($save_time) 
        $save_time = $end_time;
    
    $sid = $shop->sid;
    $shopId = $shop->shop_id;
    
    $session = $shop->session;
    logx("ddDownloadTradeListImpl $shopId start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');
    
    //API参数
    $params = array();
    $params['os'] = 9999;   //订单状态,全部
    $params['pageSize'] = 20;
    
    $dd = new DangdangClient(DD_NEW_API_URL);
    $dd->setAppKey($appkey);
    $dd->setAppSecret($appsecret);
    $dd->setMethod('dangdang.orders.list.get');
    $dd->setSession($session);
    
    $total_count = 0;
    $loop_count = 0;
    
    while($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 60*5)?($end_time - 60*5 + 1):$start_time;
        $loop_count++;
        if($loop_count > 1) resetAlarm();
        
        $params["lastModifyTime_start"] = date('Y-m-d H:i:s', $ptime);
        $params["lastModifyTime_end"] = date('Y-m-d H:i:s', $end_time);
        $params["p"] = 1;
        
        $retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
        
        if(API_RESULT_OK != ddErrorTest($retval, $db, $shopId))
        {
            $error_msg['status'] = 0;
            $error_msg['info'] = (string)$retval->Error->operation;
            logx("{$sid} ddDownloadTradeListImpl dd->execute fail 错误信息:{$error_msg['info']}", $sid.'/Trade','error');
            return TASK_OK;
        }
        
        if(!isset($retval->OrdersList) || !isset($retval->OrdersList->OrderInfo))
        {
            $end_time = $ptime + 1;
            logx("ddDownloadTradeListImpl $shopId count: 0", $sid.'/Trade');
            continue;
        }
        
        $trades = $retval->OrdersList->OrderInfo;
        //总条数
        $total_results = intval($retval->totalInfo->orderCount);
    
        $total_count += $total_results;
        
        logx("ddDownloadTradeListImpl $shopId count: $total_results", $sid.'/Trade');
        
        //如果不足一页，则不需要再抓了
        if($total_results <= count($trades))
        {
            $tids = array();
            foreach($trades as $t) $tids[] = (string)$t->orderID;
            
            if(count($tids) > 0)
            {
                $shop->tids = $tids;
                if(!$cbp($shop)) return TASK_SUSPEND;
            }
        }
        else //超过一页，第一页抓的作废，从最后一页开始抓
        {
            $total_pages = ceil(floatval($total_results)/20);
            
            for($i=$total_pages; $i>=1; $i--)
            {
                $params["p"] = $i;
                
                $retval = $dd->sendByPost('searchOrders.php', $params, $appsecret);
                
                if(API_RESULT_OK != ddErrorTest($retval, $db, $shopId))
                {
                    $error_msg['status'] = 0;
                    $error_msg['info'] = (string)$retval->Error->operation;
                    logx("{$sid} ddDownloadTradeListImpl fail2 错误信息:{$error_msg['info']}", $sid.'/Trade','error');
                    return TASK_OK;
                }
                
                $tids = array();
                $trades = $retval->OrdersList->OrderInfo;
                foreach($trades as $t)
                {
                    $tids[] = (string)$t->orderID;
                }
                if(count($tids) > 0)
                {
                    $shop->tids = $tids;
                    if(!$cbp($shop)) return TASK_SUSPEND;
                }
            }
        }
        
        $end_time = $ptime + 1;
    }
    
    if($save_time)
    {
        logx("order_last_synctime_{$shopId}".'上次抓单时间保存 dangdang平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
    }
    
    return TASK_OK;
}


/*
    注意： 主订单如果处理关闭或退款状态，子订单一定要是关闭或退款状态
        如果所有子订单都退款，主订单要进入--80已退款
        主订单的申请退款或部分退款一定要落到子订单上
*/

function loadTradeImpl(&$db,
 $appkey, 
 $appsecret,
 $shop, 
 &$t, 
 &$trade_list, 
 &$order_list, 
 &$discount_list)
{

    global $zhi_xia_shi;
    
    $sid = $shop->sid;
    $shopId = $shop->shop_id;
    
    $delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 0;    //0未付款1部分付款2已付款
    $trade_refund_status = 0;   //退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    $paid = 0; //已付金额, 发货前已付
    $trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    $is_external = 0;   // is_processed is more reasonable 
    
    $tid = (string)$t->orderID;//订单编号
    $orderState=$t->orderState;//订单状态
    $tb=$t->buyerInfo;//买家信息

    $receivable =bcadd(bcadd( $tb->goodsMoney,$tb->giftCardMoney),$tb->accountBalance)+(float)$tb->pointDeductionAmount;

    if ($tb->buyerPayMode== '货到付款')
       $delivery_term=2; 
    else if ($orderState==101 ||$orderState==300 ||$orderState==400 ||$orderState==1000)
    {
        $pay_status = 2;
        $paid = $tb->realPaidAmount;
    }
    if($delivery_term==2){
        $pay_method=2;
    }else{
        $pay_method=1;
    }
    
    switch ($orderState)
    {
       case 100://等待到款
        case 50://等待审核
        {
    
        $process_status=10;
        break;
        }
       case 101:   //等待发货（商家后台页面中显示为“等待配货”状态的订单也会返回为“等待发货”）
        {
        if ($delivery_term<>2){
        $pay_status=2;
        }//不是货到付款就说明已经付款
        $trade_status=30;
        $process_status=10;
        break;
        }
       case 300:  //已发货
        { 
        $trade_status=50;
        $is_external = 1;
        break;
        }
       case 400 :  //已送达
        { 
        $trade_status=60;
        $pay_status=2;
        $is_external = 1;
        break;
        }
       case 1000 :  //交易成功
        {
        $pay_status=2;//交易成功说明已经付款
        $trade_status=70;
        $is_external = 1;
        break;
        }
       case -100:   //取消
        {
        $trade_status=90;
        break;
        }
        case 1100:   //交易失败
        {
        $trade_status=90;
        break;
        }
        case -200:   //已拆单
        {
        $trade_status=30;
        
        break;
        }
        
    }   
    $coupons = $t->PromoList;//促销信息
    
    
    $promodiscount = 0;
    for ($i = 0; $i < count($coupons); ++$i)
    {
        $p = & $coupons[$i]->promoItem;
        $promodiscount =(float)$promodiscount + (float)$p->promoDicount*(float)$p->promoAmount;
        
    }
    //总折扣
    $total_discount = bcadd($tb->promoDeductAmount,$tb->activityDeductAmount)+(float)$tb->giftCertMoney+(float)$promodiscount;//满额减、满额打折 订单级促销优惠金额。
    //邮费
    $post_fee = (float)$tb->postage;
    $tr=$t->receiptInfo;//发票信息
    $invoice_type=0;
    if ($tr)
    {
        $invoice_type=1;
        $invoice_title=trim($tr->receiptName);//发票抬头
        $invoice_content=trim($tr->receiptDetails);//发票内容 
    }
    $orders = & $t->ItemsList->ItemInfo;
    
    
    $orderId = 1;
    $order_arr = array();
    $trade_share_discount = $total_discount;
    
    for ($i = 0; $i < count($coupons); ++$i)
    {
        $p = & $coupons[$i]->promoItem;
        
        if (empty($p->promotionType ))
        {
            continue;
        }
        
        $type=$p->promotionType;
        /*
        if (!empty($p->promotionType))
        {
            $trade_share_discount = bcsub($trade_share_discount, ($p->promoDicount*$p->promoAmount));
        }
*/
        $is_bonus = 0;
        
        $discount_list[] = array
        (
            'platform_id' => 7,
            'tid' => $tid,
            'oid' => $t->orderID,
            'sn' => @$p->promotionID,
            'type' => $type,
            'name' => $p->promotionName,
            'is_bonus' => $is_bonus,
            'detail' => '',
            'amount' => $p->promoDicount        //单个促销的优惠金额
        );
    }
    
    //以下为邮费、已付时行分摊
    $left_post = $post_fee;
    $left_share_discount = $trade_share_discount;
    $left_googs_paid = $tb->realPaidAmount;
    $trade_fee =bcsub( bcadd($receivable,$trade_share_discount),$post_fee);
    
    $order_count = count($orders);
    $goods_count = 0;
    $order_fee=0;
    
    for($i = 0; $i < $order_count; $i++)
    {
        $o = & $orders[$i];
        $goods_no = trim(@$o->outerItemID);
        if(iconv_strlen($goods_no,'UTF-8')>40)
        {
            logx("{$sid} GOODS_SPEC_NO_EXCEED\t{$goods_no}\t".@$o->itemName, 'error');
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
    
        $num = (int)$o->orderCount;
        $goods_count += $num;
        $price = (float)$o->unitPrice;
        $order_fee+= bcmul($price ,$num);

        $oid = $tid . ':' . $o->itemID;
        if(isset($order_arr[$oid]))
        {
            $oid = $oid . ':' . $orderId;
            ++$orderId;
        }
        $order_arr[$oid] = 1;
        
        $discount = 0;
        for ($j = 0; $j < count($coupons); ++$j)
        {
            if ($o->itemID == $coupons[$j]->itemID)
            {
                $discount = $coupons[$j]->promoDicount;
                array_splice($coupons, $j, 1);
                break;
            }
        }
        
        $goods_fee = bcsub(bcmul($price, $num), $discount);
    
        if ($i == $order_count - 1)
        {
            $goods_share_amount = $left_share_discount;
            $share_post = $left_post;
        }
        else
        {
            $goods_share_amount = bcdiv(bcmul($trade_share_discount, $goods_fee), $trade_fee);
            $left_share_discount = bcsub($left_share_discount, $goods_share_amount);
            $share_post = bcdiv(bcmul($post_fee, $goods_fee), $trade_fee);
            $left_post = bcsub($left_post, $share_post);
        }
    
        $share_amount = bcsub($goods_fee, $goods_share_amount);
        
        if (2 == $delivery_term)
        {
            if ($i == $order_count - 1)
            {
                $goods_paid_amount = $left_googs_paid;
            }
            else
            {
                $goods_paid_amount = bcdiv(bcmul($tb->realPaidAmount, $goods_fee), $trade_fee);
                $left_googs_paid = bcsub($left_googs_paid, $goods_paid_amount);
            }
            $goods_paid = $goods_paid_amount;
        }
        else
        {
            $goods_paid = bcadd($share_amount,$share_post);
        }
        $spec_name=$o->itemName."_".$o->specialAttribute;
        $order_list[] = array
        (
            'shop_id' => $shopId,
            'platform_id' => 7,
            'tid' => $tid,
            'oid' => $oid,
            'status' => $trade_status,
            'refund_status' => $order_refund_status,
            'order_type' => 0,
            'invoice_type' => $invoice_type,
            'bind_oid' => '',
            'goods_id' => trim(@$o->itemID),
            //'spec_id' => trim(@$o->itemID),
            'spec_id' => '',
            'goods_no' => $goods_no,
            //'spec_no' => trim($o->outerItemID),
            'spec_no' =>'',
            'goods_name' => iconv_substr((string)$o->itemName,0,255,'UTF-8'),
            'spec_name' => iconv_substr((string)$spec_name,0,100,'UTF-8'),
            'refund_id' => '',
            'num' => $num,
            'price' => $price,
            'adjust_amount' => 0,       //手工调整,特别注意:正的表示加价,负的表示减价
            'discount' => $discount,            //子订单折扣
            'share_discount' => (float)$goods_share_amount,     //分摊优惠
            'total_amount' => $goods_fee,       //分摊前扣除优惠货款num*price+adjust-discount
            'share_amount' => $share_amount,        //分摊后货款num*price+adjust-discount-share_discount
            'share_post' => (float)$share_post,         //分摊邮费
            'refund_amount' => 0,
            'is_auto_wms' => 0,
            'wms_type' => 0,
            'warehouse_no' => '',
            'logistics_no' => '',
            'paid' => $goods_paid, // jd seems no refund in trade api
            'created' => array('NOW()')
        );
        
        
    }
    
    $ts=$t->sendGoodsInfo;//收货人信息
    $receiver_address=$ts->consigneeAddr;//收货地址
    $receiver_state = @$ts->consigneeAddr_Province; //省份
    $receiver_city = @$ts->consigneeAddr_City;  //城市
    $receiver_district = @$ts->consigneeAddr_Area;  //区县
    $receiver_mobile = trim($ts->consigneeMobileTel);   //手机
    $receiver_name = trim($ts->consigneeName);  //姓名
    $receiver_phone = trim($ts->consigneeTel);  //电话
    
    $consigneePostcode=trim($ts->consigneePostcode);//邮编
    //将地址中省市区去掉
    $buyer_area=$receiver_state.",".$receiver_city.",".$receiver_district;
    $receiver_address=str_replace($buyer_area.",","",$receiver_address);
    $receiver_state = ddProvince($receiver_state);
    
    /*if(in_array($receiver_state, $zhi_xia_shi))
    {
        $receiver_district = $receiver_city;
        $receiver_city = $receiver_state . '市';
    }
    */
    if(!empty($receiver_district))
    {
        $receiver_area = "$receiver_state $receiver_city $receiver_district";
    }
    else
    {
        $receiver_area = "$receiver_state $receiver_city";
    }
        
    getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
    
    if(!empty($receiver_mobile) && !empty($receiver_phone) && $receiver_mobile != $receiver_phone)
        $mobile = $receiver_mobile . " " . $receiver_phone;
    else if(!empty($receiver_mobile))
        $mobile = $receiver_mobile;
    else
        $mobile = $receiver_phone;
        
    if(!empty($ts->dangdangAccountID))
    {
        $nick = trim($ts->dangdangAccountID);
    }
    else if(!empty($receiver_mobile)) 
    {
        $nick = 'DD'.$receiver_mobile;
    }
    else 
    {
        $nick = 'DD'.$receiver_phone;
    }
     $logistics_type = -1;  //未知物流
     if(strstr(@$ts->sendGoodsMode,"邮政平邮"))
        $logistics_type = 2;//平邮
     else if(strstr(@$ts->sendGoodsMode,"邮政EMS"))
        $logistics_type = 3;//ems
        
    $tb=$t->buyerInfo;//支付信息
    //$receivable=(float)$tb->goodsMoney;//本订单商家应收金额
    $discount=$tb->promoDeductAmount+$tb->deductAmount;//优惠金额(订单满等)+网银支付满额减
    //$goods_amount=$receivable+$discount;//货款,未扣除优惠,退款不变
    //$goods_amount=bcsub($receivable,$tb->postage);
    $paid=$tb->realPaidAmount;//网银支付金额+礼品卡支付金额+当当账户余额支付金额
    $received=$tb->realPaidAmount;//已从平台收款的金额
    $post_amount=$tb->postage;//邮费
    $platform_cost='';//平台费用 需要探讨
    $process_refund_status=0;//退款处理状态，1待审核 2已同意 3已拒绝 4待收货 5已完成 6已关闭
     //$refund_amount//退款金额
    $tr=$t->receiptInfo;//发票信息
    $invoice_type=0;
    
    if (!empty($tr->receiptName))
    {
        $invoice_type=1;
        $invoice_title=trim($tr->receiptName);//发票抬头
        $invoice_content=trim($tr->receiptDetails);//发票内容 
    }
     $tradeTime = '0000-00-00 00:00:00';

    foreach($t->OrderOperateList->OperateInfo as $oi)
    {
        if(strpos($oi->operateDetails, '下单') !== FALSE)
        {
            $tradeTime = (string)$oi->operateTime;
        }
    }
    $trade_list[] = array
    (
        'platform_id' => 7,
        'shop_id' => $shopId,
        'tid' => $tid,
        'trade_status' => $trade_status,
        'pay_status' => $pay_status,
        'pay_method'=>$pay_method,
        'refund_status' => $trade_refund_status,
        'process_status' => $process_status,
        'order_count' => $order_count,
        'goods_count' => $goods_count,
        'trade_time' =>$tradeTime,
        'pay_time' => dateValue((string)$t->paymentDate),
        'buyer_nick' => iconv_substr($nick,0,100,'UTF-8'),
        'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
        'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
        'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
        'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
        'receiver_zip'=>$consigneePostcode,
        'receivable' => $receivable,
        'buyer_email' => '',
        'buyer_area' => iconv_substr($buyer_area,0,64,'UTF-8'),
        'pay_id' => '',
        'pay_account' => '',        
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        'receiver_area' => iconv_substr($receiver_area, 0,64,'UTF-8'),
        'logistics_type' => $logistics_type,    
        'invoice_type' => $invoice_type,
        'invoice_title' => iconv_substr($invoice_title,0,255,'UTF-8'),
        'invoice_content'=>iconv_substr($invoice_content,0,255,'UTF-8'),
        'buyer_message' => iconv_substr(trim($t->message),0,1024,'UTF-8'),
        'remark' => iconv_substr(trim($t->remark),0,1024,'UTF-8'),
        'remark_flag' => 0, 
        'wms_type' => 0,
        'warehouse_no' => '',
        'stockout_no' => '',
        'logistics_no' =>iconv_substr((string)$ts->sendOrderID,0,40,'UTF-8'),
        'is_auto_wms' => 0,
        'is_external' => $is_external,
        'goods_amount' => $order_fee,
        'post_amount' => $post_fee,
        'discount' => $total_discount,
        'paid' => 2 == $delivery_term ? bcsub($receivable,$tb->goodsMoney) : $paid,
        'received' => 2 == $delivery_term ? bcsub($receivable,$tb->goodsMoney) : $paid,
        'platform_cost' => 0,
        'cod_amount' => 2 == $delivery_term ? $tb->goodsMoney : 0,
        'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
        'refund_amount' => 0,
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,
        'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
        'delivery_term' => $delivery_term,
        'cust_data'=>$t->orderMode,
        'created' => array('NOW()')
    );
    
    return true;
}


function dd_get_print_data(&$db,$shopid,$tid,$appkey,$appsecret,$session,&$rows,&$error_msg)
{
    $dd=new DangdangClient(DD_NEW_API_URL);
    $dd->setAppKey($appkey);
    $dd->setAppSecret($appsecret);
    $dd->setMethod('dangdang.order.receipt.details.list');
    $dd->setSession($session);
    $params["o"] = (int)($tid);
    $retval = $dd->sendByPost('', $params, $appsecret);
    if(API_RESULT_OK != ddErrorTest($retval, $db, $shopid))
    {
        $error_msg['status'] = 0;
        $error_msg['info'] = (string)$retval->Error->operation;
        return false;
        
    }
    else
    {
        $shopid = (string)$retval->orderCourierReceiptDetails->courierReceiptDetail->shopID;
        $sendGoodsTime = (string)($retval->orderCourierReceiptDetails->courierReceiptDetail->sendGoodsTime);
        $rows = array(array(json_encode(array(
                                    'status' => 0,
                                    'sendGoodsTime' => $sendGoodsTime,
                                    'shopID' => $shopid,
                                    'msg' =>''
                        ))));
    }       
    return true;
}

?>