<?php
//微店
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Common/utils.php');

require_once(TOP_SDK_DIR . '/vdian/vdianClient.php');
function vdianweiProvince($province)
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
function vdianweiDownloadTradeList(&$db, $shop, $start_time, $end_time, $save_time,
                                $trade_detail_cmd, &$total_count, &$error_msg)
{
    $cbp = function (&$trades) use($trade_detail_cmd)
    {
        $trades->vdianwei=1;
        pushTask ( $trade_detail_cmd, $trades );
        return true;
    };
    
	return vdianweiDownloadTradeListImpl($db, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp );
}

// 同步下载
// countLimit 订单数限制
function vdianweiSyncDownloadTradeList(&$db, $shop, $countLimit, $start_time, $end_time,
                                    &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
    $scan_count = 0;
    $total_new = 0;
    $total_chg = 0;
    $error_msg = '';

    $cbp = function (&$trades) use(&$db, $shop, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
    {
        vdianweiDownloadTradeDetail ( $db, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg );

        $total_new += $new_trade_count;
        $total_chg += $chg_trade_count;

        return ($scan_count < $countLimit);
    };
    return vdianweiDownloadTradeListImpl ( $db, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp );
}


function vdianweiDownloadTradeListImpl(&$db, $shop, $start_time, $end_time, $save_time,
                                    &$total_count, &$error_msg, $cbp)
{
    $ptime = $end_time;
    if ($save_time) $save_time = $end_time;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;

    logx("vdianDownloadTradeListImpl $shopid start_time:".date('Y-m-d H:i:s',$start_time)." end_time:".date('Y-m-d H:i:s',$end_time),$sid.'/TradeSlow');
    $page_size = 50;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $loop_count = 0;

    //请求参数
    $client = new vdianClient();
    $client->method = 'wei.order.list.get';
    $client->token = $shop->session;

    while ($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
        $loop_count++;
        if($loop_count > 1) resetAlarm();

        $params = array(
            'page_num' => 0,
            'page_size' => 100,
            'order_type' => 'unship,unpay,shiped,refunding,finish,close',
            'start_time' => date("Y-m-d H:i:s",$ptime),
            'end_time' => date("Y-m-d H:i:s",$end_time),
        );
        $retval = $client->execute($params,'1.0');
        logx("vdianwei_query ".date("Y-m-d H:i:s",$ptime)." ".date("Y-m-d H:i:s",$end_time), $sid.'/TradeSlow');

        if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
            {
                releaseDb($db);
                refreshVdianToken($shop);
                return TASK_OK;
            }
            logx("vdianDownloadTradeListImpl sid: ".$sid." vdian->execute fail 1, error_msg: {$error_msg['info']}", $sid.'/TradeSlow','error');

            return TASK_OK;
        }

        if(!isset($retval->result->orders) || count($retval->result->orders) == 0)
        {
            $end_time = $ptime + 1;
            logx("vdianDownloadTradeListImpl $shopid count: 0", $sid.'/TradeSlow');
            continue;
        }

        $trades = $retval->result->orders;
        //总条数
        $total_result = count($retval->result->orders);
        logx("vdianweiDownloadTradeListImpl $shopid count: $total_result", $sid.'/TradeSlow');

        if ($total_result <= $page_size)
        {
            $tids = array();
            for($j =0; $j < count($trades); $j++)
            {
                $tids[] = $trades[$j]->order_id;
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
            for($i=$total_pages-1; $i>=0; $i--)
            {
                logx("vdianweiDownloadTradeListImpl page:$i",$sid.'/TradeSlow');
                $params = array(
                    'page_num' => $i,
                    'page_size' => $page_size,
                    'start_time' => date("Y-m-d H:i:s",$ptime),
                    'end_time' => date("Y-m-d H:i:s",$end_time),
                    'order_type' => 'unship,unpay,shiped,refunding,finish,close',
                );
                $retval = $client->execute($params,'1.0');

                if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
                {
                    $error_msg['info'] = $retval->error_msg;
                    $error_msg['status'] = 0;
                    if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
                    {
                        releaseDb($db);
                        refreshVdianToken($shop);
                        return TASK_OK;
                    }
                    logx("vdianDownloadTradeListImpl vdian->execute fail 2, sid:". $sid ."error_msg: {$error_msg['info']}", $sid.'/TradeSlow','error');

                    return TASK_OK;
                }
                $loop_count++;
            	if($loop_count > 1) resetAlarm();

                $trades = $retval->result->orders;
                $tids = array();
                for($j =0; $j < count($trades); $j++)
                {
                    $tids[] = $trades[$j]->order_id;
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
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 vdian_wei平台 '.print_r($save_time,true),$sid. "/default");
    }

    return TASK_OK;
}


function vdianweiDownloadTradeDetail(&$db, $trades, &$scan_count,
                                  &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $sid = $trades->sid;
    $shopid = $trades->shop_id;
    $tids =$trades->tids;

    $result = $db->query("select app_key from cfg_shop where shop_id={$shopid}");
    if(!$result)
    {
        releaseDb($db);
        logx("query SessionKey2 failed!", $sid.'/TradeSlow');
        return TASK_OK;
    }

    while($row = $db->fetch_array($result))
    {
        $SessionKey = $row['SessionKey2'];
        $trades->session =$SessionKey;
    }

    //请求参数
    $client = new vdianClient();
    $client->method = 'wei.order.get';
    $client->token = $trades->session;

    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        //参数
        $params = array(
            'orderID' => $tid
        );
        $retval = $client->execute($params,'1.0');
	
        if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            if(10013 == $retval->status->status_code||10016 == $retval->status->status_code || 10026 == $retval->status->status_code)
            {
                releaseDb($db);
                refreshVdianToken($trades);
                return TASK_OK;//微中心非增量
            }
            logx("vdianDownloadTradeDetail vdian->execute fail 1, shopid:{$shopid} tid:{$tid} error_msg:{$error_msg['info']}", $sid.'/TradeSlow');

            return TASK_OK;//微中心非增量
        }
	
        if(empty($retval->result))
        {
            $error_msg = '没有获取到订单信息';
            logx("vdianweiDownloadTradeDetail vdian->execute fail 2, error_msg:{$error_msg}", $sid.'/TradeSlow');
            return TASK_OK;//微中心非增量
        }

        if(!loadVdianweiTrade($db, $trades, $trade_list, $order_list,$discount_list, $retval))
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


function loadVdianweiTrade(&$db,$trades, &$trade_list, &$order_list, &$discount_lis, &$retval)
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

    $t = &$retval->result;
    $tid = ( string ) $t->orderID; // 订单编号
    $orderState = $t->status;

    $other_amount = bcadd(floatval($t->fx_fee_value), floatval($t->total_fee));

    $receivable = bcadd(floatval($t->price), floatval($t->express_fee));//(应付总金额=商品总金额(已包括折扣)+邮费)
    $receivable = bcsub($receivable, floatval(@$t->discount)); //应付金额减去其他收费

    if($t->isClose == 1 || $t->order_type == 1)//is_close 1表示货到付款订单，可关闭， 0表示不可关闭
    {
        $delivery_term = 2;
    }

    if($t->status2 == '已退款'){
        $trade_refund_status = 3;
        $order_refund_status = 5;
    }
    if($t->status2 == '已付款'){
        if($delivery_term = 1) $pay_status = 2;
    }


    switch ($orderState)
    {
        case 'unpay' : // 未付款
        {
            $process_status = 10;
            $pay_status = 0;
            break;
        }
        case 'pay' : // 待发货
        {
            $process_status = 10;
            $trade_status = 30;
            if($delivery_term = 1) $pay_status = 2;
            break;
        }
        case 'unship_refunding' : // 未发货，申请退款
        {
            $trade_status = 30;
            $process_status = 10;
            $pay_status = 2;
            $trade_refund_status = 1;
            $order_refund_status = 2;
            break;
        }
        case 'ship': // 已发货
        {
            $trade_status = 50;
            if($delivery_term = 1) $pay_status = 2;
            break;
        }
        case 'unship_refuse_refunding' : // 未发货，拒绝退款   ps暂时标记为申请退款
        case 'shiped_refuse_refunding' : // 已发货，拒绝退款   ps暂时标记为申请退款
        {
            $trade_status = 30;
            $process_status = 10;
            $pay_status = 2;
            $trade_refund_status = 1;
            $order_refund_status = 2;
            break;
        }
        case 'shiped_refunding' : // 已发货，申请退款
        {
            $trade_status = 50;
            $pay_status = 2;
            $trade_refund_status = 1;
            $order_refund_status = 2;
            break;
        }
        case 'accept' : // 已确认收货
        {
            $trade_status = 60;
            if($delivery_term = 1) $pay_status = 2;
            break;
        }
        case 'accept_refunding' : // 已确认收货，申请退款
        {
            $trade_status = 60;
            $pay_status = 2;
            $trade_refund_status = 1;
            $order_refund_status = 2;
            break;
        }
        case 'finish' : // 订单完成
        {
            $trade_status = 70;
            break;
        }
        case 'close' : // 订单关闭
        {
            $trade_status = 90;
            break;
        }
        default :
            logx ( "sid ".$sid."invalid_trade_status $tid {$orderState}", $sid.'/TradeSlow');
            break;
    }

    //地址处理
    $receiver_address=@$t->buyerInfo->address;
    $province=vdianweiProvince(trim(@$t->buyerInfo->province));
    $city=trim(@($t->buyerInfo->city.'市'));
    $district=trim(@$t->buyerInfo->region);
    $receiver_area=@$province .' '. @$city .' '. @$district;

    getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

    $tid = $t->orderID;
    $total_trade_fee = $t->finalTotal;//订单总额
    $post_fee = floatval($t->express_fee);//邮费
    $discount_fee = floatval(@$t->discount);//折扣
    $trade_total_fee = bcadd(floatval($t->price), floatval(@$t->discount)) ;//商品总价=num*price=商品总金额加上折扣

    //分摊邮费
    $left_post = $post_fee;
    $left_discount = $discount_fee;
    $left_other = $other_amount;

    $orders = &$t->items; //商品列表
    $order_count = count($orders);
    $goods_count = 0;

    $oidMap = array();
    $orderId = 1;
    $refund_part = 0;
    $refund_order = 0;
    for($k=0; $k<count($orders); $k++)
    {
        $o = & $orders[$k];

        $order_num = intval($o->quantity);
        $goods_count += (int)$order_num;
        $order_price = floatval($o->price);
        $goods_fee = bcmul($order_price, $order_num);

        if ($k == $order_count - 1){
            $share_post = $left_post;
            $share_discount = $left_discount;
            $share_other = $left_other;
        }else{
            $share_post = bcdiv(bcmul($post_fee, $goods_fee), $trade_total_fee);
            $left_post = bcsub($left_post, $share_post);

            $share_discount = bcdiv(bcmul($discount_fee, $goods_fee), $trade_total_fee);
            $left_discount = bcsub($left_discount, $share_discount);

            $share_other = bcdiv(bcmul($other_amount, $goods_fee), $trade_total_fee);
            $left_other = bcsub($left_other, $share_other);
        }

        $share_amount = bcsub($goods_fee, $share_discount);//商品总价(num*price) - 分摊折扣
        $share_amount = bcsub($share_amount, $share_other);//商品总价(num*price) - 分摊

        if(2 == $delivery_term)
        {
            $order_paid = 0;
        }
        else
        {
            $order_paid = bcadd($share_amount, $share_post);
        }

        $oid = $tid.':'.$o->item_id;//订单id和商品id组合
        $oid2 = iconv_substr($oid, 0, 50, 'UTF-8');
        if(isset($oidMap[$oid2]))
        {
            $oid2 = $tid.':'.$orderId;
            $orderId++;
        }
        $oidMap[$oid2] = 1;

        //商品部分退款
        if(isset($o->refund_info->refund_status) && $o->refund_info->refund_status >'1'){
            if($o->refund_info->refund_status=='2'){
                $refund_order = 5;//退款成功
                $refund_part++;
            }else{
                $refund_order = 2;//申请退款
            }
        }else{
            $refund_order = $order_refund_status;
        }

        $order_list[] = array
        (
            "shop_id"=>$shopid,
            "platform_id"=>32,
            //交易编号
            "tid"=> $tid,
            //订单编号
            "oid"=> $oid2,
            "status"=> $trade_status,   //状态
            "refund_status"=> $refund_order,
            //平台货品id
            "goods_id"=> @$o->item_id,
            //规格id
            "spec_id"=> @$o->sku_id,
            //商家编码
            "goods_no"=> @$o->merchant_code,
            //规格商家编码
            "spec_no"=> @$o->sku_merchant_code,
            //货品名,微店的商品名称可能出现回车，需要进行替换
            "goods_name"=>iconv_substr(@(str_replace(array("\r\n", "\r", "\n"), " ", $o->item_name)),0,255,'UTF-8'),
            //规格名
            "spec_name"=>iconv_substr(@$o->sku_title,0,255,'UTF-8'),
            //数量
            'num'=>$order_num,
            //商品单价
            'price'=>$order_price,
            //优惠金额
            'share_amount'=>$share_amount,
            'total_amount'=>$goods_fee,
            //分摊邮费
            'share_post'=>$share_post,
            'share_discount'=>$share_discount,
            //分摊优惠--相当于手工调价
            'paid'=>$order_paid,

            'discount' => 0,

            'created' => array('NOW()')
        );
    }

    if(in_array($refund_order, array(2,5))){
        if($refund_part == count($orders)){
            $trade_refund_status = 3;
        }else{
            $trade_refund_status = 1;
        }
    }

    $trade_list[] = array
    (
        "tid"=>$tid,                            //订单号
        "platform_id"=>32,                      //平台id
        "shop_id"=>$shopid,                 //店铺ID
        "process_status"=>$process_status,  //处理订单状态
        "trade_status"=>$trade_status,          //平台订单状态
        "refund_status"=>$trade_refund_status,//退货状态
        'pay_status'=>$pay_status,

        'order_count'=>$order_count,
        'goods_count'=>$goods_count,

        "trade_time"=>dateValue(@str_replace("T", " ", @$t->add_time )),    //下单时间s
        'pay_time' => dateValue(@str_replace("T", " ", @$t->pay_time )),  //付款时间

        "buyer_message"=>iconv_substr(valid_utf8(@$t->note),0,1024,'UTF-8'),    //买家购买附言
        "buyer_email"=>'',  //iconv_substr($t->name,0,60,'UTF-8'),
        "buyer_area"=>'',   //iconv_substr($t->name,0,40,'UTF-8'),
        "buyer_nick"=>iconv_substr($t->user_telephone,0,100,'UTF-8'),
        "buyer_name"=>'',   //iconv_substr($t->user_phone,0,40,'UTF-8'),

        "receiver_name"=>iconv_substr(valid_utf8($t->buyerInfo->name),0,40,'UTF-8'),
        "receiver_province"=>$province_id,        //省份id
        "receiver_city"=>$city_id,              //市id
        "receiver_district"=>$district_id,      //地区id
        "receiver_area"=> iconv_substr($receiver_area,0,128,'UTF-8'),           //省市区
        "receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'), //地址
        //"receiver_zip"=> @$t->buyerInfo->post,     //邮编
        "receiver_mobile"=>@$t->buyerInfo->telephone,          //手机
        "receiver_telno"=>@$t->buyerInfo->telephone,           //电话
        'to_deliver_time' => '',

        "receiver_hash" => md5(@$t->buyerInfo->name.@$receiver_area.@$receiver_address.@$t->buyerInfo->telephone),
        "logistics_type"=>-1,                   //创建交易的物流方法$t->shipping_type

        'goods_amount'=>$trade_total_fee,//商品总价格num*price
        'post_amount'=>$post_fee,
        'discount'=>$discount_fee,
        'receivable'=>$receivable,//应付金额
        'paid'=> 1 == $delivery_term ? $receivable : 0,
        'received'=> 1 == $delivery_term ? $receivable : 0,

        'platform_cost'=>0,

        'invoice_type'=>0,
        'invoice_title'=>'',

        "delivery_term"=>$delivery_term,        //是否货到付款
        "pay_id"=>'',                           //支付宝账号
        "remark"=>iconv_substr(valid_utf8(@$t->express_note),0,1024,'UTF-8'),               //卖家备注
        "remark_flag"=>0,   //星标

        'cod_amount' => 2 == $delivery_term ? $receivable : 0,
        'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
        'refund_amount' => floatval(@$t->refund_info->buyer_refund_fee),
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,
        'cust_data' => '1:',//微中心订单特殊标记

        'created' => array('NOW()')
    );
    if(bccomp($discount_fee, 0)){
        $discount_list[] = array
        (
            'platform_id' => 32,
            'tid' => $tid,
            'oid' => '',
            'sn' => '',
            'type' => '',
            'name' => '订单优惠',
            'is_bonus' => 0,
            'detail' => '',
            'amount' => $discount_fee,
            'created' => array('NOW()')
        );
    }

    return true;

}       
