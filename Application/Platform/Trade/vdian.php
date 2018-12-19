<?php
//微店
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Common/utils.php');

require_once(TOP_SDK_DIR . '/vdian/vdianClient.php');

function vdianProvince($province)
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
function vdianDownloadTradeList(&$db, $shop, $start_time, $end_time, $save_time,
                                $trade_detail_cmd, &$total_count, &$error_msg)
{
    $cbp = function (&$trades) use($trade_detail_cmd)
    {
        $trades->vdianwei=0;
        pushTask ( $trade_detail_cmd, $trades );
        return true;
    };

    return vdianDownloadTradeListImpl($db, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp );
}

// 同步下载
// countLimit 订单数限制
function vdianSyncDownloadTradeList(&$db, $shop, $countLimit, $start_time, $end_time,
                                    &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
    $scan_count = 0;
    $total_new = 0;
    $total_chg = 0;
    $error_msg = '';

    $cbp = function (&$trades) use(&$db, $shop, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
    {
        vdianDownloadTradeDetail ( $db, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg );

        $total_new += $new_trade_count;
        $total_chg += $chg_trade_count;

        return ($scan_count < $countLimit);
    };
    return vdianDownloadTradeListImpl ( $db, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp );
}


function vdianDownloadTradeListImpl(&$db, $shop, $start_time, $end_time, $save_time,
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
    $client->method = 'vdian.order.list.get';
    $client->token = $shop->session;

    while ($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 60*1)?($end_time - 60*1 + 1):$start_time;
        $loop_count++;
        if($loop_count > 1) resetAlarm();

        $params = array(
            'page_num' => 1,
            'page_size' => $page_size,
            'update_start' => date("Y-m-d H:i:s",$ptime),
            'update_end' => date("Y-m-d H:i:s",$end_time),
        );
        $retval = $client->execute($params,'1.2');
        logx("vdian_query ".date("Y-m-d H:i:s",$ptime)." ".date("Y-m-d H:i:s",$end_time), $sid.'/TradeSlow');

        if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            if(isset($retval->status) && (10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code))
            {
                releaseDb($db);
                refreshVdianToken($shop);
                return TASK_OK;
            }
            logx("vdianDownloadTradeListImpl sid: ".$sid." vdian->execute fail 1, error_msg: {$retval->error_msg}", $sid.'/TradeSlow','error');

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
        $total_result = intval($retval->result->total_num);
        logx("vdianDownloadTradeListImpl $shopid count: $total_result", $sid.'/TradeSlow');

        if ($total_result <= $page_size)
        {
            $tids = array();
            for($j =0; $j < count($trades); $j++)
            {
                $tids[] = $trades[$j]->order_id;
                $temp = $trades[$j]->order_id;
                logx("vdian tid: {$temp}", $sid.'/TradeSlow');
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
                $params['page_num'] = $i;
                $retval = $client->execute($params,'1.2');
                if($loop_count > 1) resetAlarm();
                if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
                {
                    $error_msg['info'] = $retval->error_msg;
                    $error_msg['status'] = 0;
                    if(isset($retval->status) && (10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code))
                    {
                        releaseDb($db);
                        refreshVdianToken($shop);
                        return TASK_OK;
                    }
                    logx("vdianDownloadTradeListImpl vdian->execute fail 2, sid:". $sid ."error_msg: {$retval->error_msg}", $sid.'/TradeSlow','error');

                    return TASK_OK;
                }
                $trades = $retval->result->orders;
                $tids = array();
                for($j =0; $j < count($trades); $j++)
                {
                    $tids[] = $trades[$j]->order_id;
                    $temp = $trades[$j]->order_id;
                    logx("vdian tid: {$temp}", $sid.'/TradeSlow');
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
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 vdian平台 '.print_r($save_time,true),$sid. "/default");
    }

    return TASK_OK;
}


function vdianDownloadTradeDetail(&$db, $trades, &$scan_count,
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

    //重新授权
    $result = $db->query("select app_key from cfg_shop where shop_id={$shopid}");

    if(!$result)
    {
        releaseDb($db);
        logx("query app_key failed!", $sid.'/TradeSlow');
        return TASK_OK;
    }

    while($row = $db->fetch_array($result))
    {
        $res = json_decode($row['app_key'],true);
        $session = $res['session'];
        $trades->session = $session;
    }

    //请求参数
    $client = new vdianClient();
    $client->method = 'vdian.order.get';
    $client->token = $trades->session;

    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        //参数
        $params = array(
            'order_id' => $tid
        );
        if($tid=='') {
            logx("vdianDownloadTradeDetail continue tid: $tid 单号为空,进行过滤" ,$sid.'/TradeSlow');
            continue;
        }
        $retval = $client->execute($params);

        if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            if(isset($retval->status) && (10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code))
            {
                releaseDb($db);
                refreshVdianToken($trades);
                return TASK_SUSPEND;
            }
            logx("vdianDownloadTradeDetail vdian->execute fail 1, shopid:{$shopid} tid:{$tid} error_msg:{$retval->error_msg}", $sid.'/TradeSlow');
            
            return TASK_SUSPEND;
        }
        if(empty($retval->result))
        {
            $error_msg['info'] = '没有获取到订单信息';
            $error_msg['status'] = 0;
            logx("vdianDownloadTradeDetail vdian->execute fail 2, error_msg:{$error_msg['info']}", $sid.'/TradeSlow');
            return TASK_SUSPEND;
        }

        if(!loadVdianTrade($db, $trades, $trade_list, $order_list,$discount_list, $retval))
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


function loadVdianTrade(&$db,$trades, &$trade_list, &$order_list, &$discount_list, &$retval)
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
    $tid = ( string ) $t->order_id; // 订单编号

    $tmp_shop_name = valid_utf8(@$t->f_shop_name);
    $sell_shop_name = valid_utf8(@$t->seller_name);//判断自己分销自己的
    if(isset($trades->account_nick) && isset($tmp_shop_name) && $trades->account_nick==$tmp_shop_name && $trades->account_nick!=$sell_shop_name){
        logx("分销订单不下载 shopid:{$shopid} tid:{$tid}",$sid.'/TradeSlow');
        return true;
    }

    // 应付金额 = 商品总价 + 运费 - 折扣

    //微店 还有分销分成金额和推广金额，先将分成金额计算在内   //
    $other_amount = bcadd(floatval(abs($t->fx_fee_value)), floatval(abs($t->total_fee)));//因为微店返回的是负数,所以这里求一下绝对值

    $receivable = bcadd(floatval($t->price), floatval($t->express_fee));//(应付总金额=商品总金额(已包括折扣)+邮费)
    $receivable = bcsub($receivable, $other_amount); //应付金额减去其他收费

    if($t->is_close == 1 || $t->order_type == 1)//is_close 1表示货到付款订单，可关闭， 0表示不可关闭
    {
        $delivery_term = 2;
    }

    $orderState = $t->status_ori;
    $orderRefundState = $t->refund_status_ori;

    switch ($orderState) {
        case 10://待付款
            $process_status = 10;
            $pay_status = 0;
            break;
        case 20://已付款,待发货
            $process_status = 10;
            $trade_status = 30;
            if($delivery_term == 1) $pay_status = 2;
            break;
        case 21://部分付款
            $process_status = 10;
            $pay_status = 1;
            $trade_status = 20;
            break;
        case 30://已发货
            $process_status = 40;
            $trade_status = 50;
            if($delivery_term == 1) $pay_status = 2;
            break;
        case 31://部分发货
            $process_status = 30;
            $trade_status = 40;
            if($delivery_term == 1) $pay_status = 2;
            break;
        case 40://已确认收货
            $process_status = 40;
            $trade_status = 60;
            if($delivery_term == 1) $pay_status = 2;
            break;
        case 50://已完成
            $process_status = 60;
            $trade_status = 70;
            break;
        case 60://已关闭
            $trade_status = 90;
            break;
        default:
            logx ( "ERROR $sid invalid_trade_status $tid {$orderState}", $sid.'/TradeSlow', 'error');
            break;
    }
    $trade_refund_status = 0;   // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;   // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    switch ($orderRefundState) {
        case 0://没有退款
            $trade_refund_status = 0;
            $order_refund_status = 0;
            break;
        case 1://退款中
            $trade_refund_status = 1;
            //$order_refund_status = 2;
            break;
        case 2://退款成功
            $trade_refund_status = 3;
           // $order_refund_status = 5;
            break;
        case 3://退款关闭

            break;
        default:
            logx ( "ERROR $sid invalid_trade_refund_status $tid {$orderRefundState}", $sid.'/TradeSlow','error');
            break;
    }

    $id_card = iconv_substr(trim(@$t->buyer_info->idCardNo), 0,40,'UTF-8');
    //地址处理
    $receiver_address = @trim($t->buyer_info->address);
    $province=vdianProvince(trim(@$t->buyer_info->province));
    $city=trim(@($t->buyer_info->city.'市'));
    $district=trim(@$t->buyer_info->region);
    $receiver_area=@$province .' '. @$city .' '. @$district;

    getAddressID($province, $city, $district, $province_id, $city_id, $district_id);

    $tid = $t->order_id;
    $total_trade_fee = floatval($t->total);//订单总额
    $post_fee = floatval($t->express_fee);//邮费
    //$discount_fee = bcadd(floatval($t->discount), floatval($t->discount_amount));//折扣
    $discount_fee = 0;
    if(isset($t->discount_list) && !empty($t->discount_list)){
        foreach ($t->discount_list as $discount) {
            if(strpos($discount->discount_info,'包邮')===false){
                $discount_fee = bcadd($discount_fee, $discount->discount_price);
            }
        }
    }
    $trade_total_fee = bcadd(floatval($t->price), $discount_fee) ;//商品总价=num*price=商品总金额加上折扣

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
    $refund_flag = 0;
    for($k=0; $k<count($orders); $k++)
    {
        $o = & $orders[$k];

        $order_num = floatval($o->quantity);
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
        $oid2 = iconv_substr($oid, 0, 40, 'UTF-8');
        if(isset($oidMap[$oid2]))
        {
            $oid2 = $tid.':'.$orderId;
            $orderId++;
        }
        $oidMap[$oid2] = 1;

        //商品部分退款
        if(isset($o->refund_info->refund_status) && $o->refund_info->refund_status <> ''){
            if($o->refund_info->refund_status=='10'){
                $refund_order = 5;//退款成功
                $refund_part++;
                $refund_flag=1;
            }else{
                $refund_order = 2;//申请退款
                $refund_flag=1;
            }
        }else{
            $refund_order = $order_refund_status;
        }

        $order_list[] = array
        (
            'shop_id' =>$shopid,
            "platform_id"=>32,
            //交易编号
            "tid"=> $tid,
            //订单编号
            "oid"=> $oid2,
            "status"=> $trade_status,   //状态
            "refund_status"=> $refund_order,
            //平台货品id
            "goods_id"=> $o->item_id,
            //规格id
            "spec_id"=> $o->sku_id,
            //商家编码
            "goods_no"=> iconv_substr(str_replace(array(" ","　","\t","\n","\r"),array("","","","",""),$o->merchant_code),0,40,'UTF-8'),
            //规格商家编码
            "spec_no"=>iconv_substr(trim(@$o->sku_merchant_code), 0,40,'UTF-8'),
            //货品名,微店的商品名称可能出现回车，需要进行替换
            "goods_name"=>iconv_substr(@(str_replace(array("\r\n", "\r", "\n"), " ", valid_utf8($o->item_name))),0,255,'UTF-8'),
            //规格名
            "spec_name"=>iconv_substr(valid_utf8(@$o->sku_title),0,255,'UTF-8'),
            //数量
            'num'=>$order_num,
            //商品单价
            'price'=>$order_price,
            //优惠金额
            'share_amount'=>$share_amount,
            'total_amount'=>$goods_fee,
            'adjust_amount'=>$share_other,//手工调整金额
            //分摊邮费
            'share_post'=>$share_post,
            'share_discount'=>$share_discount,

            //分摊优惠--相当于手工调价
            'paid'=>$order_paid,

            'created' => array('NOW()')
        );
    }

    if($refund_flag=="1"){
        if($refund_part == count($orders)){//全部退款
            $trade_refund_status = 3;
            $trade_status = 80;//全部退款 更新订单状态为已退款
        }else if($refund_part!="0"){//部分退款
            $trade_refund_status = 2;
        }else{//申请退款
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
        "buyer_nick"=>iconv_substr($t->user_phone,0,100,'UTF-8'),
        "buyer_name"=>'',   //iconv_substr($t->user_phone,0,40,'UTF-8'),

        "receiver_name"=>iconv_substr(valid_utf8($t->buyer_info->name),0,40,'UTF-8'),
        "receiver_province"=>$province_id,      //省份id
        "receiver_city"=>$city_id,              //市id
        "receiver_district"=>$district_id,      //地区id
        "receiver_area"=> iconv_substr($receiver_area,0,128,'UTF-8'),           //省市区
        "receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'), //地址
        "receiver_zip"=> @$t->buyer_info->post,     //邮编
        "receiver_mobile"=>@$t->buyer_info->phone,          //手机
        "receiver_telno"=>@$t->buyer_info->phone,           //电话
        'to_deliver_time' => '',

        "receiver_hash" => md5(@$t->buyer_info->name.@$receiver_area.@$receiver_address.@$t->buyer_info->phone.@$t->buyer_info->post),
        "logistics_type"=>-1,                   //创建交易的物流方法$t->shipping_type

        'goods_amount'=>$trade_total_fee,//商品总价格num*price
        'post_amount'=>$post_fee,
        'other_amount' => -$other_amount,//其他金额
        'discount'=>$discount_fee,
        'receivable'=>$receivable,//应付金额
        'paid'=> 1 == $delivery_term ? $receivable : 0,
        'received'=> 1 == $delivery_term ? $receivable : 0,
        'id_card' => $id_card,

        'platform_cost'=>0,

        'fenxiao_nick'=>iconv_substr($tmp_shop_name,0,40,'UTF-8'),

        'invoice_type'=>0,
        'invoice_title'=>'',

        "delivery_term"=>$delivery_term,        //是否货到付款
        "pay_id"=>'',                           //支付宝账号
        "remark"=>iconv_substr(valid_utf8(@$t->express_note),0,1024,'UTF-8'),               //卖家备注
        "remark_flag"=>0,   //星标int

        'cod_amount' => 2 == $delivery_term ? $receivable : 0,
        'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
        'refund_amount' => floatval(@$t->buyer_refund_fee),
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,

        'created' => array('NOW()')
    );

    if(isset($t->discount_list) && !empty($t->discount_list)){
        foreach ($t->discount_list as $discount) {
            $discount_list[] = array(
                'platform_id' => 32,
                'tid' => $tid,
                'oid' => '',
                'sn' => '',
                'type' => @$discount->discount_type,
                'name' => @$discount->discount_info,
                'is_bonus' => 0,
                'detail' => '',
                'amount' => floatval(@$discount->discount_price),
                'created' => array('NOW()')
            );
        }
    }

    return true;

}