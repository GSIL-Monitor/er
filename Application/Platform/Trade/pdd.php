<?php
//拼多多pdd
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Common/utils.php');
require_once(TOP_SDK_DIR . '/pdd/pddClient2.php');
require_once(TOP_SDK_DIR. '/pdd/request/OrderNumberListIncrGetRequest_pdd.php');
require_once(TOP_SDK_DIR. '/pdd/request/OrderInformationGetRequest_pdd.php');


function pddProvince($province)
{
    global $spec_province_map;

    if(empty($province))
        return $province;

    if(iconv_substr($province,-1,1,'UTF-8') != '省')
    {
        $prefix = iconv_substr($province,0,2,'UTF-8');
        if(isset($spec_province_map[$prefix]))
            return $spec_province_map[$prefix];

        return $prefix.'省';
    }
    return $province;
}

function pddTradeList(&$db,
                      $shop,
                      $appkey,
                      $appsecret,
                      $start_time,
                      $end_time,
                      $save_time,
                      $trade_detail_cmd,
                      &$total_count,
                      &$error_msg,
                      $type,
                      $scan_count=0,
                      $total_new=0,
                      $total_chg=0)
{
    $cbp = function(&$trades) use ($trade_detail_cmd)
    {
        pushTask($trade_detail_cmd,$trades);
        return true;
    };

    return pddTradeListImpl($db,$shop,$appkey,$appsecret,$start_time,$end_time,$save_time,$total_count,$error_msg,$cbp,$type,$scan_count,$total_new,$total_chg);
}

function pddTradeListSync(&$db,
                          $shop,
                          $appkey,
                          $appsecret,
                          $limit_count,
                          $start_time,
                          $end_time,
                          &$scan_count,
                          &$total_new,
                          &$total_chg,
                          &$error_msg,
                          $type)
{
    $scan_count = 0;
    $total_count = 0;
    $total_new = 0;
    $total_chg = 0;
    $cbp = function(&$trades) use (&$db,$shop,$appkey,$appsecret,$limit_count,&$scan_count,&$total_new,&$total_chg,&$error_msg)
    {
        pddTradeDetail($db,$trades,$appkey,$appsecret,$scan_count,$new_trade_count,$chg_trade_count,$error_msg);

        $total_new += $new_trade_count;
        $total_chg += $chg_trade_count;

        return ($scan_count < 200000);
    };

    return pddTradeListImpl($db,$shop,$appkey,$appsecret,$start_time,$end_time,false,$total_count,$error_msg,$cbp,$type,$scan_count,$total_new,$total_chg);
}

function pddTradeListImpl(&$db,
                          $shop,
                          $appkey,
                          $appsecret,
                          $start_time,
                          $end_time,
                          $save_time,
                          &$total_count,
                          &$error_msg,
                          &$cbp,
                          $type,
                          &$scan_count,
                          &$total_new,
                          &$total_chg
)
{
    if($save_time)
        $save_time = $end_time;

    $sid = $shop->sid;
    $shopid = $shop->shop_id;

    logx("pddTadeListImpl $shopid start_time:".date('Y-m-d H:i:s',$start_time).' end_time:'.date('Y-m-d H:i:s',$end_time),$sid . "/TradeSlow");

    $ptime = $start_time;
    $page_size = 50;
    $loop_count = 0;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $client = new pddClient2();
    $client->clientId = $appkey;
    $client->clientSecret = $appsecret;
    $client->dataType = 'JSON';
    $client->accessToken = $shop->session;

    while($ptime < $end_time)
    {
        $ptime = ($end_time - $ptime > 60*5) ? ($start_time + 60*5+1 )  : $end_time;
        logx("pddTadeListImpl $shopid query: start_time:".date('Y-m-d H:i:s',$start_time).' end_time:'.date('Y-m-d H:i:s',$ptime),$sid . "/TradeSlow");
        $loop_count++;
        if($loop_count > 0){
            if($type == 'auto'){
                resetAlarm();
            }
            else if($type == 'manual'){
                pddmanualresetAlarm();
            }
        }

        $req = new OrderNumberListIncrGetRequest_pdd();
        $req->setIsLuckyFlag(1);//1:非抽奖订单 2：抽奖订单
        $req->setOrderStatus(5);//1待发货 2已发货待签收 3已签收 5全部
        $req->setRefundStatus(5);//1无售后或售后关闭 2售后处理中 3退款中 4退款成功 5全部
        $req->setStartUpdatedAt($start_time);
        $req->setEndUpdatedAt($ptime);
        $req->setPage(1);
        $req->setPageSize($page_size);

        $retval = $client->execute($req);
        if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
            refreshPddToken($db,$appkey,$appsecret,$shop);
            $client->accessToken = $shop->session;
            $retval = $client->execute($req);
        }
        if(API_RESULT_OK != pddErrorTest($retval,$db,$shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            logx("ERROR {$sid} pddTadeListImpl error_msg:".$error_msg['info'],$sid. "/TradeSlow");
            return TASK_OK;
        }

        if(!isset($retval->order_sn_list) || count($retval->order_sn_list)==0)
        {
            $start_time =  $ptime - 1;
            logx("pddTadeListImpl {$sid} {$shopid} count:0",$sid."/TradeSlow");
            //continue;
        }

        $trades = $retval->order_sn_list;
        $trade_total = intval($retval->total_count);
        $trade_count = count($retval->order_sn_list);
        logx("pddTadeListImpl {$sid} {$shopid} count:{$trade_total}",$sid . "/TradeSlow");

        if($trade_total <= $page_size)
        {
            $tids = array();
            for($i=0; $i<$trade_count; $i++)
            {
                $tmp = $trades[$i];
                $tids[] = $tmp->order_sn;
                $total_count++;
            }

            if(count($tids) > 0)
            {
                $shop->tids = $tids;
                if( pddTradeDetail($db,$shop, $appkey, $appsecret, $scan_count, $new_trade_count, $chg_trade_count, $error_msg) != TASK_OK)
                {
                    return TASK_SUSPEND;
                }else{
                    $total_new += $new_trade_count;
                    $total_chg += $chg_trade_count;
                    logx("log_pdd {$shop->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $shop->sid.'/TradeSlow');
                }
            }
        }
        else
        {
            $page_count = ceil(floatval($trade_total)/$page_size);
            for($i=$page_count; $i>0; $i--)//拼多多页码从0开始
            {
                $req->setPage($i);
                $retval = $client->execute($req);
                if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
                    refreshPddToken($db,$appkey,$appsecret,$shop);
                    $client->accessToken = $shop->session;
                    $retval = $client->execute($req);
                }
                if($loop_count > 0){
                    if($type == 'auto'){
                        resetAlarm();
                    }
                    else if($type == 'manual'){
                        pddmanualresetAlarm();
                    }
                }
                logx("pdd 第{$i}页",$sid . "/TradeSlow");
                if(API_RESULT_OK != pddErrorTest($retval,$db,$shopid))
                {
                    $error_msg['status'] = 0;
                    $error_msg['info'] = $retval->error_msg;
                    logx("ERROR {$sid} pddTadeListImpl error_msg:".$error_msg['info'],$sid . "/TradeSlow");
                    return TASK_OK;
                }

                $tids = array();
                $trades = $retval->order_sn_list;
                for($j=0; $j<count($trades); $j++)
                {
                    $tmp = $trades[$j];
                    $tids[] = $tmp->order_sn;
                    $total_count++;
                }

                /*$miao = ceil(floatval(count($tids))/20);
                sleep((int)$miao);*/

                if(count($tids) > 0)
                {
                    $shop->tids = $tids;
                    if( pddTradeDetail($db,$shop, $appkey, $appsecret, $scan_count, $new_trade_count, $chg_trade_count, $error_msg) != TASK_OK)
                    {
                        return TASK_SUSPEND;
                    }else{
                        $total_new += $new_trade_count;
                        $total_chg += $chg_trade_count;
                        logx("log_pdd {$shop->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $shop->sid.'/TradeSlow');
                    }
                }
            }
        }
        $start_time = $ptime - 1;
        if($save_time)
        {
            setSysCfg($db, "order_last_synctime_{$shopid}", $ptime);
            logx("order_last_synctime_{$shopid}".'上次抓单时间保存 pdd平台 '.print_r($ptime,true),$sid. "/default");
        }
    }

    return TASK_OK;
}


function pddDownloadTradeList(&$db,$shop, $appkey, $appsecret, $start_time, $end_time, $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;
    if($save_time)
        $save_time = $end_time;
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    logx("pddTadeListImpl $shopid start_time:".date('Y-m-d H:i:s',$start_time).' end_time:'.date('Y-m-d H:i:s',$end_time),$sid . "/TradeSlow");

    $loop_count = 0;
    $page_size = 50;

    $client = new pddClient2();
    $client->clientId = $appkey;
    $client->clientSecret = $appsecret;
    $client->dataType = 'JSON';
    $client->accessToken = $shop->session;

    $total_trade_list = array();
    //这里的判断是 第一次抓单时会抓前三天订单，这时如果卖家单量很大的情况下会出现保存上次抓单时间时 数据库断开连接而未保存的情况。
    //为了避免这个情况在第一次抓单时 采取从前三天向 当前时间抓取，并每次保存完订单数据以后记录抓单时间。
    //非第一次保存时会获取上次抓单时间。这里是全部写入完数据库以后 再保存上次抓单时间。
    $ptime = $start_time;
    $total_count = 0;

    while($ptime<$end_time)
    {
        $ptime = ($end_time - $ptime > 60*5) ? ($start_time + 60*5+1 )  : $end_time;

        logx("pddTadeListImpl $shopid query: start_time:".date('Y-m-d H:i:s',$start_time).' end_time:'.date('Y-m-d H:i:s',$ptime),$sid . "/TradeSlow");
        $loop_count++;
        if($loop_count > 0){
			resetAlarm(240);
        }

        $req = new OrderNumberListIncrGetRequest_pdd();
        $req->setIsLuckyFlag(1);//1:非抽奖订单 2：抽奖订单
        $req->setOrderStatus(5);//1待发货 2已发货待签收 3已签收 5全部
        $req->setRefundStatus(5);//1无售后或售后关闭 2售后处理中 3退款中 4退款成功 5全部
        $req->setStartUpdatedAt($start_time);
        $req->setEndUpdatedAt($ptime);
        $req->setPage(1);
        $req->setPageSize($page_size);

        $retval = $client->execute($req);
        if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
            refreshPddToken($db,$appkey,$appsecret,$shop);
            $client->accessToken = $shop->session;
            $retval = $client->execute($req);
        }
        if(API_RESULT_OK != pddErrorTest($retval,$db,$shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            logx("ERROR {$sid} pddTadeListImpl error_msg:".$error_msg['info'],$sid. "/TradeSlow");
            return TASK_OK;
        }

        if(!isset($retval->order_sn_list) || count($retval->order_sn_list)==0)
        {
            logx("pddTadeListImpl {$sid} {$shopid} count:0",$sid."/TradeSlow");
            //continue;
        }
        //总条数
        $trade_total = intval($retval->total_count);
        //子订单总数
        $trade_count = count($retval->order_sn_list);
        $trades = $retval->order_sn_list;
        logx("pddTadeListImpl {$sid} {$shopid} count:{$trade_total}",$sid . "/TradeSlow");
        //如果不足一页，则不需要再抓了

        if($trade_total <= $page_size)
        {
            $tids = array();
            for($i=0; $i<$trade_count; $i++)
            {
                $tmp = $trades[$i];
                $tids[] = $tmp->order_sn;
                $total_count++;
            }

            if(count($tids) > 0)
            {
                $shop->tids = $tids;
                if( pddTradeDetail($db,$shop, $appkey, $appsecret, $scan_count, $new_trade_count, $chg_trade_count, $error_msg) != TASK_OK)
                {
                    return TASK_SUSPEND;
                }

            }
        }else
        {
            $page_count = ceil(floatval($trade_total)/$page_size);
            for($i=$page_count; $i>0; $i--)//拼多多页码从0开始
            {
                $req->setPage($i);
                $retval = $client->execute($req);
                if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
                    refreshPddToken($db,$appkey,$appsecret,$shop);
                    $client->accessToken = $shop->session;
                    $retval = $client->execute($req);
                }
                if($loop_count > 0){
					resetAlarm(240);
                }

                logx("pdd 第{$i}页",$sid . "/TradeSlow");
                if(API_RESULT_OK != pddErrorTest($retval,$db,$shopid))
                {
                    $error_msg['status'] = 0;
                    $error_msg['info'] = $retval->error_msg;
                    logx("ERROR {$sid} pddTadeListImpl error_msg:".$error_msg['info'],$sid . "/TradeSlow");
                    return TASK_OK;
                }

                $tids = array();
                $trades = $retval->order_sn_list;
                for($j=0; $j<count($trades); $j++)
                {
                    $tmp = $trades[$j];
                    $tids[] = $tmp->order_sn;
                    $total_count++;
                }

                /*$miao = ceil(floatval(count($tids))/20);
                sleep((int)$miao);*/

                if(count($tids) > 0)
                {
                    $shop->tids = $tids;
                    if( pddTradeDetail($db,$shop, $appkey, $appsecret, $scan_count, $new_trade_count, $chg_trade_count, $error_msg) != TASK_OK)
                    {
                        return TASK_SUSPEND;
                    }
                }
            }
        }
        $start_time = $ptime - 1;
        if($save_time)
        {
            setSysCfg($db, "order_last_synctime_{$shopid}", $ptime);
            logx("order_last_synctime_{$shopid}".'上次抓单时间保存 pdd平台 '.print_r($ptime,true),$sid. "/default");
        }
    }

    return TASK_OK;

}

function pddTradeLoad(&$db,$shop,$trade,&$trade_list,&$order_list,&$discount_list)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;

    $delivery_term = 1;         // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 0;            // 0未付款1部分付款2已付款
    $trade_refund_status = 0;   // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;   // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    //$paid = 0;                  // 已付金额, 发货前已付
    $trade_status = 10;         // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70;       // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消

    $t = & $trade;
    $tid= $t->order_sn;

    $status_trade = $t->order_status;//订单状态 拼多多现在只支持抓取订单状态是1已付款的订单
    $pay_status = 2;//口碑不支持货到付款，所有默认都是已付款

    /*if(isset($t['Chargetype']) && $t['Chargetype']=='货到付款')
    {
        $delivery_term = 2;
    }*/

    switch($status_trade)
    {
        case 0://异常单
        {
            $trade_status = 10;
            $process_status = 10;
            break;
        }
        case 1://待发货
        {
            $pay_status = 2;
            $trade_status = 30;
            $process_status = 10;
            break;
        }
        case 2://已发货待签收
        {
            $pay_status = 2;
            $trade_status = 50;
            break;
        }
        case 3://已签收
        {
            $pay_status = 2;
            $trade_status = 70;
            break;
        }
        default:
        {
            logx("invalid_trade_status tid:{$tid} status:{$status_trade}",$sid);
            break;
        }
    }
    if(isset($t->refund_status)){
        if($t->refund_status=="2" || $t->refund_status=="3"){
            $trade_refund_status = 1;
            $order_refund_status = 2;
        }
        if($t->refund_status=="4"){
            $trade_refund_status = 3;
            $order_refund_status = 5;
            $trade_status = 80;//退款成功, 更新订单状态为已退款
            logx("pdd_trade_refund $tid", $sid.'/TradeSlow');
        }
    }
    //应付金额 = 商品总额 + 货运费用 - 折扣
    $receivable = floatval(trim(@$t->goods_amount));
    $receivable = bcadd($receivable, floatval(trim(@$t->postage)));
    $receivable = bcsub($receivable, floatval(trim(@$t->seller_discount)));

    $receiver_address = iconv_substr(trim(@$t->address),0,255,'UTF-8');
    $province = pddProvince(trim(@$t->province));

    if($province == '黑龙省') $province = '黑龙江省';
    $city = trim(@$t->city);
    $district = trim(@$t->town);
    $receiver_area=@$province .' '. @$city .' '. @$district;

    getAddressID($province,$city,$district,$province_id,$city_id,$district_id);

    $total_goods_fee = floatval(trim(@$t->goods_amount));
    $total_post_fee = floatval(trim(@$t->postage));
    $total_discount_fee = floatval(trim(@$t->seller_discount));
    $total_other_fee = 0;

    $left_post = $total_post_fee;
    $left_discount = $total_discount_fee;
    $left_other = $total_other_fee;

    $orders = &$t->item_list;
    $order_count = count($orders);
    $goods_count = 0;

    $oidMap = array();
    $orderId = 1;
    for($i=0; $i<$order_count; $i++)
    {
        $o = & $orders[$i];

        $num = intval($o->goods_count);
        $price = floatval($o->goods_price);
        $goods_count += $num;

        $goods_fee = bcmul($num,$price);

        if($i == $order_count - 1)
        {
            $share_post = $left_post;
            $share_discount = $left_discount;
            $share_other = $left_other;
        }
        else
        {
            if($total_post_fee)
            {
                $share_post = bcdiv(bcmul($goods_fee,$total_post_fee),$total_goods_fee);
                $left_post = bcsub($left_post,$share_post);
            }
            if($total_discount_fee)
            {
                $share_discount = bcdiv(bcmul($goods_fee,$total_discount_fee),$total_goods_fee);
                $left_discount = bcsub($left_discount,$share_discount);
            }
            if($total_other_fee)
            {
                $share_other = bcdiv(bcmul($goods_fee,$total_other_fee),$total_goods_fee);
                $left_other = bcsub($left_other,$share_other);
            }
        }

        //分摊后金额
        $share_amount = bcsub($goods_fee,$share_discount);//单种商品总额-分摊折扣
        $share_amount = bcsub($share_amount,$share_other);// 再减去分摊的其他金额

        //订单已付金额
        if($delivery_term == 2)
        {
            $order_paid = 0;
        }
        else
        {
            $order_paid = bcadd($share_amount,$share_post);//子订单已付金额=分摊后金额+分摊运费
        }

        //子订单id
        $oid = $tid.':'.$o->goods_id;
        $oid2 = iconv_substr($oid,0,40,'UTF-8');
        if(isset($oidMap[$oid2]))
        {
            $oid2 = $tid.':'.$orderId;
            $orderId++;
        }
        $oidMap[$oid2] = 1;

        $order_list[] = array(
            'shop_id' => $shopid,
            'platform_id' => 33,
            'tid' => $tid,
            'oid' => $oid2,
            'status' => $trade_status,
            'refund_status' => $order_refund_status,
            'goods_id' => iconv_substr(trim(@$o->goods_id),0,40,'UTF-8'),
            'spec_id' => iconv_substr(trim(@$o->sku_id),0,40,'UTF-8'),
            'goods_no' => iconv_substr(trim(@$o->goods_id),0,40,'UTF-8'),
            'spec_no' => iconv_substr(trim(@$o->outer_id),0,40,'UTF-8'),
            'goods_name' => iconv_substr(@(str_replace(array("\r\n", "\r", "\n"), " ", $o->goods_name)),0,255,'UTF-8'),
            'spec_name' => iconv_substr(@(str_replace(array("\r\n", "\r", "\n"), " ", $o->goods_spec)),0,255,'UTF-8'),
            'num' => $num,
            'price' => $price,
            'discount' => 0,
            'share_discount' => $share_discount,//分摊折扣
            'share_post' => $share_post,//分摊油费
            'adjust_amount' => $share_other,//手工调整=分摊其他金额
            'total_amount' => $goods_fee,//货品金额
            'share_amount' => $share_amount,//商品分摊后金额
            'paid' => $order_paid,//分摊后应收
            'created' => array('NOW()'),
        );
    }

    if(empty($t->created_time)) //下单时间为空，说明和下单时间一致
        $t->created_time = $t->confirm_time;
    if(empty($t->confirm_time)) //下单时间为空，说明和下单时间一致
        $t->confirm_time = $t->created_time;

    //客服备注
    $buyer_message = '';//;身份信息：【buyerName+身份证姓名+身份证号】支付方式
    if(isset($t->id_card_name) && !empty($t->id_card_name) && isset($t->id_card_num) && !empty($t->id_card_num)){
        $buyer_message = ';身份信息:【'.trim(@$t->receiver_name).'+'.$t->id_card_name.'+'.$t->id_card_num.'】'.$t->pay_type.';';
    }
    //支付编号
    $pay_id = '';//$t['payNo'];
    if(isset($t->pay_no) && !empty($t->pay_no)){
        $pay_id = $t->pay_no;
    }

    //客户网名处理
    $buyer_nick = $t->id_card_num;
    $buyer_name = $t->id_card_name;
    if(empty($buyer_nick) || empty($buyer_name)){
        $buyer_nick = $t->receiver_name;
        $buyer_name = $t->receiver_name;
    }

    //备注处理
    $remark = @$t->remark;
    $remark = str_replace('商家生成订单下载报表，准备发货;', '', $remark);
    $remark = str_replace('商家生成订单下载报表，准备发货', '', $remark);

    $trade_list[] = array(
        'tid' => $tid,
        'platform_id' => 33,
        'shop_id' => $shopid,
        'process_status' => $process_status,
        'trade_status' => $trade_status,
        'refund_status' => $trade_refund_status,
        'pay_status' => $pay_status,

        'order_count' => $order_count,
        'goods_count' => $goods_count,
        'trade_time' => dateValue(@str_replace("T", " ", @$t->created_time)),
        'pay_time' => dateValue(@str_replace("T", " ", @$t->confirm_time)),

        'buyer_message' => iconv_substr(valid_utf8(@$buyer_message),0,1024,'UTF-8'),//买家备注
        //'buyer_email' => iconv_substr(@$t['email'],0,60,'UTF-8'),
        //"buyer_area" => iconv_substr(@$t->user_address->address,0,40,'UTF-8'),
        "buyer_nick" => iconv_substr(valid_utf8(@$buyer_nick),0,100,'UTF-8'),
        "buyer_name" => iconv_substr(valid_utf8(@$buyer_name),0,40,'UTF-8'),

        'receiver_name' => iconv_substr(valid_utf8($t->receiver_name),0,40,'UTF-8'),
        "receiver_province" => $province_id,      //省份id
        "receiver_city" => $city_id,              //市id
        "receiver_district" => $district_id,      //地区id
        "receiver_area" => iconv_substr(valid_utf8($receiver_area),0,128,'UTF-8'),       //省市区
        "receiver_address" => iconv_substr(valid_utf8($receiver_address),0,256,'UTF-8'),	//地址
        "receiver_zip" => @$t->zip,       //邮编
        "receiver_mobile" => @$t->receiver_phone,     //手机
        "receiver_telno" => @$t->receiver_phone,       //电话
        'to_deliver_time' => '',

        "receiver_hash" => md5(valid_utf8(@$t->receiver_name).valid_utf8(@$receiver_address).@$t->zip.@$t->receiver_phone),
        "logistics_type" => -1,     //创建交易的物流方法

        'goods_amount' => $total_goods_fee,
        'post_amount' => $total_post_fee,
        'other_amount' => $total_other_fee,
        'discount' => $total_discount_fee,
        'receivable' => $receivable,
        'paid' => 1==$delivery_term ? $receivable : 0,
        'received' => 1==$delivery_term ? $receivable : 0,
        'platform_cost' => 0,

        'invoice_type' => 0,
        'invoice_title' => '',
        'invoice_content' => '',

        'delivery_term' => $delivery_term,
        'pay_id' => $pay_id,
        'remark' => $remark,
        'remark_flag' => 0,//星标flag

        'cod_amount' => $delivery_term==2 ? $receivable : 0,
        'dap_amount' => $delivery_term==2 ? 0 : $receivable,
        'refund_amount' => 0,//isset($t->sale_refund_fee)&&!empty($t->sale_refund_fee) ? trim(@$t->sale_refund_fee) : 0,
        'score' => 0,
        'trade_mask' => 0,
        'real_score' => 0,
        'got_score' => 0,
        //'cust_data' => $t->agent_list->

        'created' => array('NOW()'),
    );

    return true;
}

function pddTradeDetail(&$db,
                            $shop,
                            $appkey,
                            $appsecret,

                            &$scan_count,
                            &$new_trade_count,
                            &$chg_trade_count,
                            &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $tids = $shop->tids;

    $app_key = $db->query_result("select app_key from cfg_shop where shop_id = {$shopid}");
    $secret = json_decode($app_key['app_key'],true);
    $session = $secret['session'];
    $shop->session = $session;
    global $pdd_app_config;
    $appkey = $pdd_app_config['app_key'];
    $appsecret = decodeDbPwd($pdd_app_config['app_secret'],$pdd_app_config['app_key']);
    $client = new pddClient2();
    $client->clientId = $appkey;
    $client->clientSecret = $appsecret;
    $client->dataType = 'JSON';
    $client->accessToken = $shop->session;

    $req = new OrderInformationGetRequest_pdd();

    for($i=0; $i<count($tids); $i++)
    {
        //if($i%5==0)	sleep(1);
        $tid = & $tids[$i];
        $req->setOrderSn($tid);

        $retval = $client->execute($req);
        if(isset($retval->error_msg) && $retval->error_msg == 'access_token已过期'){
            refreshPddToken($db,$appkey,$appsecret,$shop);
            $client->accessToken = $shop->session;
            $retval = $client->execute($req);
        }
        if(API_RESULT_OK != pddErrorTest($retval,$db,$shopid))
        {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            logx("ERROR {$sid} pddTradeDetail tid:{$tid} error_msg:".$error_msg['info'].print_r($retval,true),$sid. "/TradeSlow" );
            if(stripos($error_msg['info'], '调用过于频繁')!==false){
                sleep(1);
                $i--;
                continue;
            }
            if(isset($retval->error_msg) && $retval->error_msg == '订单不存在'){
                return TASK_OK;
            }
            return TASK_SUSPEND;
        }
        if(!isset($retval->order_info) || empty($retval->order_info))
        {
            $error_msg["status"] = 0;
            $error_msg["info"]   = '没有获取到订单信息';
            logx("pddTradeDetail fail tid:{$tid} error_msg:".$error_msg["info"],$sid . "/TradeSlow");
            return TASK_SUSPEND;
        }

        if(!pddTradeLoad($db,$shop,$retval->order_info,$trade_list,$order_list,$discount_list))
        {
            continue;
        }

        $scan_count++;
        if(count($trade_list) >= 100)
        {
            if(!putTradesToDb($db,$trade_list,$order_list,$discount_list,$new_trade_count,$chg_trade_count,$error_msg,$sid))
            {
                releaseDb($db);
                return TASK_SUSPEND;
            }
        }
    }

    if(count($trade_list) > 0)
    {
        if(!putTradesToDb($db,$trade_list,$order_list,$discount_list,$new_trade_count,$chg_trade_count,$error_msg,$sid))
        {
            releaseDb($db);
            return TASK_SUSPEND;
        }
    }

    releaseDb($db);
    return TASK_OK;
}