<?php
require_once(ROOT_DIR .'/Trade/util.php');
require_once(TOP_SDK_DIR .'/jpw/JpwClient.php');

function jowProvince($province)
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

function jpwDownloadTradeList(&$db,$appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count=0;
    $ptime = $end_time;
    if ($save_time) $save_time = $end_time;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    logx("jpwDownloadTradeList $shopid start_time:".date('Y-m-d H:i:s',$start_time)."end_time:".date('Y-m-d H:i:s',$end_time),$sid.'/TradeSlow');
    $page = 1;
    $page_size = 50;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $loop_count = 0;

    $jpw = new jpwClient();
    $jpw->secret = $appsecret;
    //请求参数
    $params = array(
        'jOrderStatus' => '1,2,3,5,6,9',
        'jPagesize' => $page_size,
        'jPage' => $page,
        'jType' => 'order_list',
        'jCusKey' => $shop->session,
        'token' => $shop->refresh_token,
        'type' => 'json',
        'show_detail'=> '1'
    );
    $trade_list = array();
    $order_list = array();
    $discount_list = array();
    while ($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 3600*24)?($end_time -3600*24 +1):$start_time;
        $loop_count++;
        if ($loop_count > 1)	resetAlarm();

        $params['create_time'] = $ptime.'|'.$end_time;
        $retval = $jpw->execute($params);
        // logx("jpwDownloadTradeList ".print_r($retval,true) ,$sid);
        if(API_RESULT_OK != jpwErrorTest($retval,$db,$shopid))
        {
            if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info))
            {
                releaseDb($db);
                refreshJpwToken($appkey, $appsecret, $shop);
                $error_msg['status'] = 0;
                $error_msg['info'] = $retval->error_msg;
                return TASK_OK;
            }
            $error_msg['status'] = 0;
            $error_msg['info'] = $retval->error_msg;
            logx("jpwDownloadTradeList $shopid {$error_msg['info']}", $sid.'/TradeSlow');
            return TASK_OK;
        }

        if(!isset($retval->data->lists))
        {
            $end_time = $ptime + 1;
            logx("jpwDownloadTradeList $shopid count: 0", $sid.'/TradeSlow');
            continue;
        }
        //总条数
        $total_result = intval($retval->data->count);
        logx("jpwDownloadTradeList $shopid count : $total_result" ,$sid.'/TradeSlow');

        $trades = $retval->data->lists;

        if ($total_result <= count($trades))
        {
            for($j =0; $j < count($trades); $j++)
            {
                $t = $trades[$j];
                //logx("JPW {$t->orderno}" ,$sid);
                if(!loadJpwTrade($db ,$appkey, $appsecret, $shop, $trade_list, $order_list,$discount_lis, $t))
                {
                    continue;
                }
            }
        }
        else
        {
            $total_pages = ceil(floatval($total_result)/$page_size);

            for($i=$total_pages; $i>0; $i--)
            {
                //请求参数
                $params['jPage'] = $i;
                $retval = $jpw->execute($params);
                logx("共 $total_pages 页 准备抓第 $i 页");
                if(API_RESULT_OK != jpwErrorTest($retval,$db,$shopid))
                {

                    if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) ||10001 == intval(@$retval->info))
                    {
                        releaseDb($db);
                        refreshJpwToken($appkey, $appsecret, $shop);
                        return TASK_OK;
                    }
                    $error_msg['status'] = 0;
                    $error_msg['info'] = $retval->error_msg;
                    logx("jpwDownloadTradeList $shopid {$error_msg['info']}", $sid.'/TradeSlow');
                    return TASK_OK;
                }
                $trades = $retval->data->lists;
                for($j =0; $j < count($trades); $j++)
                {
                    $t = $trades[$j];
                    $total_trade_count += 1;
                    if(!loadJpwTrade($db ,$appkey, $appsecret, $shop, $trade_list, $order_list,$discount_lis, $t))
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

    if(count($order_list) > 0)
    {
        if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
        {

            return TASK_SUSPEND;
        }
    }
    if($save_time)
    {
        logx("order_last_synctime_{$shopid}".'上次抓单时间保存 jpw平台 '.print_r($save_time,true),$sid. "/default");
        setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
    }

    return TASK_OK;
}


function downJpwTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $sid = $trades->sid;

    if(!$db)
    {
        $error_msg["status"] = 0;
        $error_msg["info"] = '连接数据库失败';
        logx("downJpwTradesDetail $sid getUserDb failed!!", $sid.'/TradeSlow','error');
        return TASK_SUSPEND;
    }
    $shopid = $trades->shop_id;
    $tids =$trades->tids;
    $session = $trades->session;

    $trade_list = array();
    $order_list = array();
    $discount_list = array();
    for($i=0; $i<count($tids); $i++)
    {

        $trade = $tids[$i];

        $jpw = new jpwClient();
        $jpw->secret = $appsecret;

        //拼接参数
        $params = array(
            'jOrderNo' => $trade,
            'jType' => 'order_info',
            'jCusKey' => $session,
            'token' => $trades->refresh_token,
            'type' => 'json',
        );
        $retval = $jpw->execute($params);

        // logx("downJpwTradesDetail shopid: $shopid ".print_r($retval,true) ,$sid);
        if(API_RESULT_OK != jpwErrorTest($retval,$db,$shopid))
        {

            if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info))
            {
                releaseDb($db);
                refreshJpwToken($appkey, $appsecret, $trades);
                $error_msg['status'] = 0;
                $error_msg['info'] = $retval->error_msg;
                return TASK_OK;
            }
            $error_msg['status'] = 0;
            $error_msg['info'] = $retval->error_msg;
            logx("downJpwTradesDetail $shopid {$error_msg['info']}", $sid.'/TradeSlow');
            return TASK_OK;
        }

        if (! isset ( $retval->data))
        {
            $error_msg['status'] = 0;
            $error_msg['info'] = '没获取到订单信息';
            logx ( "downJpwTradesDetail fail $trade 错误信息:{$error_msg['info']}", $sid.'/TradeSlow' );
            return TASK_OK;
        }

        if(!loadJpwTrade($db, $appkey, $appsecret, $trades, $trade_list, $order_list,$discount_lis, $retval->data))
        {
            return TASK_SUSPEND;
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


    return TASK_OK;
}

function loadJpwTrade(&$db, $appkey, $appsecret, $shop, &$trade_list, &$order_list,&$discount_lis, &$t)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;

    $delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 0; // 0未付款1部分付款2已付款
    $trade_refund_status = 0; // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0; // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    $paid = 0; // 已付金额, 发货前已付
    $trade_status = 10; // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消

    // $t = $trade->data;
    $tid = ( string ) $t->orderno; // 订单编号
    $orderState = $t->status; // jpw订单状态 1:等待买家付款,2:等待发货,3:已发货,5:交易成功,6:交易已关闭,9:备货中
    $receivable = $t->payamount; // 应付金额

    switch (intval($orderState))
    {
        case 1 : // 待付款
        {
            $process_status = 10;
            break;
        }
        case 2 : // 待发货
        {
            $process_status = 10;
            $trade_status = 30;
            $pay_status = 2;
            break;
        }
        case 3 : // 已发货(待确认收货)
        {
            $trade_status = 50;
            $process_status = 40;
            $pay_status = 2;
            break;
        }
        case 5 : // 交易完成
        {
            $trade_status = 70;
            $process_status = 60;
            $pay_status = 2;
            break;
        }
        case 6 : // 交易关闭
        {
            $trade_status = 90;
            break;
        }

        case 9 : // 备货中
        {
            $process_status = 10;
            $trade_status = 30;
            $pay_status = 2;
            break;
        }
        default :
            logx ( "invalid_trade_status {$sid}  $tid{$orderState}", $sid.'/TradeSlow','error' );
            break;
    }

    //地址处理
    $receiver_address = $t->buyeraddress;
    $addrs = explode('|', $t->new_area);
    $receiver_state = @jowProvince($addrs[0]);	//省份
    $receiver_city = @$addrs[1];			//城市
    $receiver_district = @$addrs[2];	//区县
    $receiver_area = $receiver_state.' '.$receiver_city.' '.$receiver_district;

    // 得到 省份ID 城市ID 区县ID
    getAddressID ( $receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );
    $modules = $t->buyerphone;

    $tid = $t->orderno;
    $delivery_term = 1;//支付类型,是否货到付款
    $total_trade_fee = $t->payamount;//订单总额
    $post_fee = $t->payexpress;//邮费
    $total_discount = $t->discount;//优惠
    $trade_total_fee = bcsub((bcadd($total_trade_fee, $post_fee)), $total_discount);//订单价格

    //分摊邮费
    $left_post = $post_fee;
    $left_share_discount = $total_discount;

    $orders = &$t->goodslist;
    $order_count = count($orders);
    $goods_count = 0;

    $oidMap = array();
    $orderId = 1;
    for($k=0; $k<count($orders); $k++)
    {
        $o = & $orders[$k];
        $order_refund_status = 0;
        if ($o->backstatus == '售后完成')
        {
            $trade_refund_status = 2;
            $order_refund_status = 5;
        }elseif ($o->backstatus == '售后中')
        {
            $trade_refund_status = 2;
            $order_refund_status = 2;
        }elseif ($o->backstatus == '售后关闭')
        {
            $trade_refund_status = 2;
            $order_refund_status = 1;
        }
        $order_num = $o->goodsnum;
        $goods_count += (int)$order_num;
        $order_price = $o->goodsprice;
        $goods_fee = bcmul($order_price, $order_num);

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

        $oid = $tid.':'.$o->goodsid.':'.$o->goods_sku_id;
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
        $spec_name = @$o->goodszvalue.' '.@$o->goodsfvalue;
        $order_list[] = array
        (
            "shop_id" =>$shopid,
            "platform_id"=>29,
            //交易编号
            "tid"=>$tid,
            //订单编号
            "oid"=> $oid2,
            "status"=> $trade_status,	//状态
            "refund_status"=> $order_refund_status,
            //平台货品id
            "goods_id"=> $o->goodsid,
            //规格id
            "spec_id"=> $o->goods_sku_id,
            //商家编码
            "goods_no"=> iconv_substr(@$o->goodsno,0,40,'UTF-8'),
            //规格商家编码
            "spec_no"=>'',
            //货品名
            "goods_name"=>iconv_substr(@$o->goodsname,0,255,'UTF-8'),
            //规格名
            "spec_name"=> iconv_substr(@$spec_name,0,100,'UTF-8'),
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

    $trade_list[] = array
    (
        "tid"=>$tid,							//订单号
        "platform_id"=>29,						//平台id
        "shop_id"=>$shopid,				//店铺ID
        "process_status"=>$process_status, 		//处理订单状态
        "trade_status"=>$trade_status,			//平台订单状态
        "refund_status"=>$trade_refund_status, 	//退货状态
        'pay_status'=>$pay_status,

        'order_count'=>$order_count,
        'goods_count'=>$goods_count,

        "trade_time"=>$t->createtime,//下单时间
        "pay_time"=>dateValue($t->paytime), //支付时间

        "buyer_message"=>iconv_substr(valid_utf8(@$t->remark),0,1024,'UTF-8'), 	//买家购买附言
        "buyer_email"=>'',
        "buyer_area"=>'',
        "buyer_nick"=>'',

        "receiver_name"=>iconv_substr(valid_utf8($t->buyername),0,40,'UTF-8'),
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        "receiver_area"=> $receiver_area,		//省市区
        "receiver_address"=> iconv_substr($receiver_address,0,256,'UTF-8'),	//地址
        "receiver_zip"=> '',		//邮编
        "receiver_mobile"=>$modules, 			//电话
        'to_deliver_time' => '',

        "receiver_hash" => md5(@$t->receiver_name.$receiver_area.@$receiver_address.$modules),
        "logistics_type"=>-1,					//创建交易的物流方法$t->shipping_type

        'goods_amount'=>$total_trade_fee,
        'post_amount'=>$post_fee,
        'discount'=>$total_discount,
        'receivable'=>$trade_total_fee,
        'paid'=>$trade_total_fee,
        'received'=>$trade_total_fee,

        'platform_cost'=>0,

        'invoice_type'=>0,
        'invoice_title'=>'',

        "delivery_term"=>$delivery_term, 		//是否货到付款
        "pay_id"=>'', 							//支付宝账号
        "remark"=>iconv_substr(valid_utf8(@$t->sellremark),0,1024,'UTF-8'), 				//卖家备注
        // "remark_flag"=>'', 	//星标

        'cod_amount' => 2 == $delivery_term ? $total_trade_fee : 0,
        'dap_amount' => 2 == $delivery_term ? 0 : $total_trade_fee,
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
