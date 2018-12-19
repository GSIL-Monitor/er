<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/chuchujie/chuchujieClient.php');
require_once(ROOT_DIR . '/Manager/utils.php');
function ccjDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{

    $ptime = $end_time;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;
    if($save_time) 
        $save_time = $end_time;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    logx("ccjDownloadTradeList $shopid start_time:" . 
        date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');

    $loop_count = 0;
    $page_size = 50;
    
    global $ccj_app_config; 
    $session = $ccj_app_config['org_name']; 
    
    $ccj=new ChuchujieClient();
    $ccj->setApp_key($appkey);
    $ccj->setDirname('/Order/get_order_list_v2');
    $ccj->setApp_secret($appsecret);
    $ccj->setSession($session);
    while($ptime > $start_time)
    {
        $loop_count++;
        
        if($loop_count > 1) resetAlarm();
        if($ptime - $start_time > 3600*6) $ptime = $end_time - 3600*6 + 1;
        else $ptime = $start_time;
        $params = array();
        $params['ctime_start']=date('Y-m-d H:i:s', $ptime);
        $params['ctime_end'] = date('Y-m-d H:i:s', $end_time);
        $params['page']=0;
        $params['page_size']=$page_size;
        $retval=$ccj->execute($params);
        if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid))
        {
            $error_msg = $retval->error_msg;
            logx("ccjDownloadTradeList ccj->execute fail", $sid.'/Trade','error');
            return TASK_OK;
        }
        
        $trade_list = array();
        $order_list = array();
        $discount_list = array();

        //总条数
        $total_results = intval($retval->total_num);
        
        logx("CcjTrade $shopid count: $total_results", $sid.'/Trade');
        
        if ($total_results <= $page_size)
        {
            if(isset($retval->info))
            {
                $trades = $retval->info;
                $total_trade_count += count($trades);
                for($j =0; $j < count($trades); $j++)
                {
                    $t = & $trades[$j];
                    
                    if(!loadCcjTrade($sid, $appkey, $appsecret, $session, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
                    {
                        return TASK_OK;
                    }
                }
                
                if($countLimit && $total_trade_count >= $countLimit)
                    return TASK_SUSPEND;
                
            }
        }
        else
        {
            $total_pages = ceil(floatval($total_results)/$page_size);
            for($i=$total_pages-1; $i>=0; $i--)
            {
                $params['page']=$i;
                $retval=$ccj->execute($params);
                
                if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid))
                {
                    $error_msg = $retval->error_msg;
                    logx("ccjDownloadTradeList ccj->execute fail", $sid.'/Trade','error');
                    return TASK_OK;
                }
        
                resetAlarm();
        
                $trades = $retval->info;
                $total_trade_count += count($trades);
        
                for($j =0; $j < count($trades); $j++)
                {
                    $t = & $trades[$j];
                    
                    if(!loadCcjTrade($sid, $appkey, $appsecret, $session, $shopid, $db, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
                    {
                        return TASK_OK;
                    }
                }
                
                if($countLimit && $total_trade_count >= $countLimit)
                    return TASK_SUSPEND;
                
                
        
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
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 ccj平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
    }
    
    return TASK_OK;
    
}

function ccjProvince($province)
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


function loadCcjTrade($sid, $appkey, $appsecret, $sessionKey, $shopid, &$db, &$trade_list, &$order_list, &$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    global $zhi_xia_shi;
    $delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 0;    //0未付款1部分付款2已付款
    $trade_refund_status = 0;   //退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    $paid = 0; //已付金额, 发货前已付
    $trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    $is_external = 0;   // is_processed is more reasonable 
    
    $tra = $t->order;
    $orders = $t->goods;
    $addr = $t->address;
    
    $tid = $tra->order_id;
    
    if($tra->status == 1)
    {
        $trade_status=10;//未确认
        $process_status=10;
    }
    else if($tra->status == 2 || $tra->status == 7)
    {
        $trade_status=30;
        $process_status=10;
        $paid = $tra->order_pay_price;
        $pay_status = 2;
    }
    else if($tra->status == 3)
    {
        $trade_status=50;//已发货
        $is_external = 1;
        $paid = $tra->order_pay_price;
        $pay_status = 2;
    }
    else if($tra->status == 4)
    {
        $trade_status=70;//已完成 
        $is_external = 1;
        $paid = $tra->order_pay_price;
        $pay_status = 2;
    }
    else if($tra->status == 5)  //楚楚街解释：支付了然后退款，然后成了交易关闭
    {
        $trade_status=90;
    }
    else if($tra->status == 6)  
    {
        $trade_status=90;
    }
    else
    {
        logx("ccj_invalid_trade_status $tid  状态：{$tra->status}",$sid.'/Trade'.'error');
    }
    
    if($tra->status_refund == 1 || $tra->status_refund ==2)
    {
        $trade_refund_status = 1;
    }
    else if($tra->status_refund == 3)
    {
        $trade_refund_status = 0;
    }
    
    $trade_goods_fee = floatval($tra->total_price)-floatval($tra->express_price);
    //邮费
    $post_fee = $tra->express_price;
    //优惠
    $trade_discount = (float)$tra->shop_coupon_price + (float)$tra->shop_off_price;
    $paid = bcadd($paid, $tra->all_conpon_price);
    //以下为邮费分摊
    $left_post = $post_fee;
    $left_share_discount = $trade_discount;
    
    $order_count = count($orders);
    $goods_count = 0;
    
    $orderId = 1;
    $order_arr = array();
    
    for($i = 0; $i < $order_count; $i++)
    {
        $o = & $orders[$i];

        $num = $o->amount;
        $tmp = preg_match('/(\d+)/', $o->amount, $match);
        $num = $match[0];
        $goods_count += (int)$num;
        $price = $o->price;
        
        $goods_fee = floatval($price) * $num;
    
        $oid = $tid;
        if(isset($order_arr[$oid]))
        {
            $oid = $oid . ':' . $orderId;
            ++$orderId;
        }
        $order_arr[$oid] = 1;
    
        if ($i == $order_count - 1)
        {
            $share_post = $left_post;
            $share_discount = $left_share_discount;
        }
        else
        {
            $share_discount = ((float)$trade_discount*(float)$goods_fee)/(float)$trade_goods_fee;
            $left_share_discount = $left_share_discount - $share_discount;
                
            
            $share_post = ((float)$post_fee* (float)$goods_fee)/ (float)$trade_goods_fee;
            $left_post = (float)$left_post- (float)$share_post;
        }
        
        $order_paid = 0;
        //order已收金额
        if($tra->status == 2||$tra->status == 3||$tra->status == 4||$tra->status == 5||$tra->status == 7)
        {
            $order_paid = (float)$goods_fee+(float)$share_post-(float)$share_discount;
        }
        
        $name = '';
        $props = $o->prop;
        foreach($props as $prop)
        {
            $name .= $prop->value;
        }
        
        if($o->refund_status_text == '申请退款中')
        {
            $order_refund_status =2;
        }
        else if($o->refund_status_text == '退款已完成')
        {
            $order_refund_status = 5;
        }
        else if($o->refund_status_text == '退款关闭')
        {
            $order_refund_status = 1;
        }
        $share_amount = $goods_fee - $share_discount;   
        
        $order_list[] = array
        (
            'shop_id' => $shopid,
            'platform_id' => 27,
            'tid' => $tid,
            'oid' => $oid,
            'status' => $trade_status,
            'refund_status' => $order_refund_status,
            'order_type' => 0,
            'bind_oid' => '',
            'goods_id' => trim(@$o->goods_id),
            'spec_id' => @$o->propN->sku_id,
            'goods_no' => trim(@$o->goods_no),
            'spec_no' => iconv_substr(trim(@$o->outer_id),0,40,'UTF-8'),
            'goods_name' => iconv_substr(@$o->goods_title,0,255,'UTF-8'),
            'spec_name' => iconv_substr($name,0,100,'UTF-8'),
            'refund_id' => '',
            'num' => $num,
            'price' => $price,
            'adjust_amount' => 0,       //手工调整,特别注意:正的表示加价,负的表示减价
            'discount' => 0,            //子订单折扣
            'share_discount' => $share_discount,    //分摊优惠
            'total_amount' => $goods_fee,       //分摊前扣除优惠货款num*price+adjust-discount
            'share_amount' => $share_amount ,       //分摊后货款num*price+adjust-discount-share_discount
            'share_post' => $share_post,            //分摊邮费
            'refund_amount' => 0,
            'is_auto_wms' => 0,
            'wms_type' => 0,
            'warehouse_no' => '',
            'logistics_no' => '',
            'paid' => $order_paid, // jd seems no refund in trade api
            'created' => array('NOW()')
        );
    }
    
    $receiver_address = @$addr->street;//收货地址
    $receiver_state = ccjProvince(@$addr->province);//省
    $receiver_city = @$addr->city;          //城市
    $receiver_district = @$addr->district;  //区县
    
    $receiver_name = @$addr->nickname;      //姓名
    $receiver_mobile = iconv_substr(@$addr->phone,0,40,'UTF-8');    //电话
    $receiver_phone = '';
    
    if(in_array($receiver_state, $zhi_xia_shi))
    {
        $receiver_city = $receiver_state . '市';
    }
    
    if(!empty($receiver_district))
    {
        $receiver_area = "$receiver_state $receiver_city $receiver_district";
    }
    else
    {
        $receiver_area = "$receiver_state $receiver_city";
    }
        
    getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
    
    $trade_list[] = array
    (
        'platform_id' => 27,
        'shop_id' => $shopid,
        'tid' => $tid,
        'trade_status' => $trade_status,
        'pay_status' => $pay_status,
        'refund_status' => $trade_refund_status,
        'process_status' => $process_status,
        
        'delivery_term' => $delivery_term,
        'trade_time' => dateValue($tra->ctime),
        'pay_time' => dateValue(@$tra->pay_time),
        
        'buyer_nick' => iconv_substr("ccj".$receiver_mobile,0,100,'UTF-8'),
        'buyer_email' => '',
        'buyer_area' => '',
        'pay_id' => '',
        'pay_account' => '',
        
        'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
        'receiver_mobile' => $receiver_mobile,
        'receiver_telno' => '',
        'receiver_zip' => '',
        'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
        
        
        'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
        'logistics_type' => -1,
        
        'buyer_message' => iconv_substr(@$tra->comment,0,1024,'UTF-8'),
        'remark' => iconv_substr(@$tra->seller_note,0,1024,'UTF-8'),
        'remark_flag' => 0,
        
        'end_time' => dateValue(@$tra->last_status_time),
        'wms_type' => 0,
        'warehouse_no' => '',
        'stockout_no' => '',
        'logistics_no' => iconv_substr(@$tra->express_id,0,40,'UTF-8'),
        'is_auto_wms' => 0,
        'is_external' => $is_external,
        
        'goods_amount' => $trade_goods_fee,
        'post_amount' => $post_fee,
        'receivable' => bcsub($tra->total_price,$trade_discount),
        'discount' => $trade_discount,
        'paid' => (2 == $delivery_term) ? 0 : $paid,
        'received' => (2 == $delivery_term) ? 0 : $paid,
        
        'platform_cost' => 0,
        
        'order_count' => $order_count,
        'goods_count' => $goods_count,
        
        'cod_amount' => (2 == $delivery_term) ? bcsub($tra->total_price,$trade_discount) : 0,
        'dap_amount' => (2 == $delivery_term) ? 0 : bcsub($tra->total_price,$trade_discount),
        'refund_amount' => 0,
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,
        
        'created' => array('NOW()')
    );
    
    
    
    if(count($order_list) >0)
    {
        return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
    }
    
    return true;
}


function downCcjTradesDetail($db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;

    $sid = $trades->sid;
    $shopid = $trades->shop_id;
    $tids = & $trades->tids;
    
    global $ccj_app_config;
            
    $session = $ccj_app_config['org_name']; 
    
    $ccj=new ChuchujieClient();
    
    $ccj->setApp_key($appkey);
    $ccj->setDirname('/Order/get_order_list_v2');
    $ccj->setApp_secret($appsecret);
    $ccj->setSession($session);

    $trade_list = array();
    $order_list = array();
    $discount_list = array();
    
    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
    
        $end_time=time();
        $start_time=$end_time - 3600*24*30;
        
        $params['order_id'] = $tid;
        
        
        $retval=$ccj->execute($params);
        $a = ccjErrorTest($retval, $db, $shopid);
        if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid))
        {
            $error_msg = $retval->error_msg;
            logx("downCcjTradesDetail ccj->execute fail", $sid.'/Trade','error');
            return TASK_OK;
        }

        if(empty($retval->info))
        {
            $error_msg = '读取订单信息失败';
            return TASK_OK;
        }

        if(!loadCcjTrade($sid, $appkey, $appsecret, $session, $shopid, $db, $trade_list, $order_list, $discount_list, $retval->info[0], $new_trade_count, $chg_trade_count, $error_msg))
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







