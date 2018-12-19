<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Trade/jos.php');

require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jos/JdException.php');
require_once(TOP_SDK_DIR . '/jos/request/order/OrderFbpSearchRequest.php');
require_once(TOP_SDK_DIR . '/jos/request/order/OrderFbpGetRequest.php');

//jd_fbp订单列表
function josFbpDownloadTradeList(&$db, $shop, $countLimit, $start_time, $end_time,
                                 $save_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $page_size =40;
    $loop_count = 0;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;

    $ptime = $end_time;
    if($save_time)
        $save_time = $end_time;

    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    logx("josFbpDownloadTradeList $shopid start_time:" .
        date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');

    $page_size =40;
    $loop_count = 0;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $jos = new JdClient();
    $jos->appKey = $shop->key;
    $jos->appSecret = $shop->secret;
    $jos->accessToken = $shop->session;

    $req = new OrderFbpSearchRequest();

    while($ptime > $start_time)
    {
        $ptime = ($ptime - $start_time > 3600*24) ? ($end_time - 3600*24 + 1) : $start_time;
        $loop_count++;
        if($loop_count > 1)resetAlarm();

        $req->setStartDate(date('Y-m-d H:i:s', $ptime));
        $req->setEndDate(date('Y-m-d H:i:s', $end_time));
        $req->setOptionalFields("order_id,vender_id,pay_type,order_total_price,order_payment,return_order"
            .",order_seller_price,freight_price,seller_discount,order_state,order_state_remark"
            .",delivery_type,invoice_info,order_remark,order_start_time,order_end_time"
            .",consignee_info,item_info_list,coupon_detail_list,pin,vender_remark");

        $req->setPageSize($page_size);
        $req->setPage(1);
        $retval = $jos->execute($req);
        //print_r($retval);
        if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
        {
            $error_msg = $retval->error_msg;
            logx("ERROR $sid josfbpDownloadTradeList,josfbp->execute fail, error_msg: {$error_msg}",$sid.'/Trade', 'error');
            return TASK_OK;
        }
        if(!isset($retval->order_info_list) || count($retval->order_info_list) == 0)
        {
            $end_time = $ptime + 1;
            logx("josfbpDownloadTradeList $shopid count: 0", $sid.'/Trade');
            continue;
        }

        $total_results = intval($retval->order_total);
        logx("JosfbpTrade $shopid count: $total_results", $sid.'/Trade');
        if($total_results <= $page_size)
        {
            if(isset($retval->order_info_list))
            {
                $trades = $retval->order_info_list;
                for($j = 0; $j < count($trades); $j++)
                {
                    $t= & $trades[$j];
                    $total_trade_count++;
                    if(!josFbpDownloadTradeImpl($db, $shop, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
                    {
                        return TASK_OK;
                    }
                    if($countLimit && $total_trade_count >= $countLimit)
                        return TASK_SUSPEND;
                }
            }
        }
        //超过一页的
        else
        {
            $total_pages = ceil(floatval($total_results)/$page_size);
            for($i=$total_pages; $i>=1; --$i)
            {
                $req->setPage($i);
                $retval = $jos->execute($req);
                if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
                {
                    $error_msg = $retval->error_msg;
                    logx("ERROR $sid josfbpDownloadTradeList, error_msg: {$error_msg}", $sid.'/Trade','error');
                    return TASK_OK;
                }
                if(!isset($retval->order_info_list) || count($retval->order_info_list) == 0)
                {
                    $end_time = $ptime + 1;
                    logx("josfbpDownloadTradeList $shopid count: 0", $sid.'/Trade');
                    continue;
                }
                resetAlarm();
                $trades = $retval->order_info_list;

                for($j =0; $j < count($trades); $j++)
                {
                    $t = & $trades[$j];
                    $total_trade_count++;
                    if(!josFbpDownloadTradeImpl($db, $shop, $trade_list, $order_list, $discount_list, $t, $new_trade_count, $chg_trade_count, $error_msg))
                    {
                        return TASK_OK;
                    }
                    if($countLimit && $total_trade_count >= $countLimit)
                        return TASK_SUSPEND;
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
    //保存下载时间
    if($save_time)
    {
        setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
    }
    return TASK_OK;
}

//jos_fbp单条订单抓取
function josFbpDownloadTradesDetail(&$db, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;

    $sid = $trades->sid;
    $shopid = $trades->shop_id;
    $tids = & $trades->tids;

    //API系统参数
    $jos = new JdClient();
    $jos->appKey = $trades->key;
    $jos->appSecret = $trades->secret;
    $jos->accessToken = $trades->session;

    $req = new OrderFbpGetRequest();

    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        $req->setOptionalFields("order_id,vender_id,pay_type,order_total_price,order_payment,return_order"
            .",order_seller_price,freight_price,seller_discount,order_state,order_state_remark"
            .",delivery_type,invoice_info,order_remark,order_start_time,order_end_time,balance_used"
            .",consignee_info,item_info_list,coupon_detail_list,vender_remark,vat_invoice_info,pin,payment_confirm_time");
        $req->setOrderId($tid);
        $retval = $jos->execute($req);

        if(API_RESULT_OK != josErrorTest($retval, $db, $shopid))
        {
            $error_msg = $retval->error_msg;
            return TASK_SUSPEND;
        }
        if(empty($retval->orderInfo))
        {
            $error_msg = '没有获取到订单信息';
            return TASK_OK;
        }

        if(!josFbpDownloadTradeImpl($db, $trades, $trade_list, $order_list, $discount_list, $retval->orderInfo, $new_trade_count, $chg_trade_count, $error_msg))
        {
            return TASK_SUSPEND;
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


function josFbpDownloadTradeImpl(&$db, $shop, &$trade_list, &$order_list, &$discount_list, &$t, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $sid = $shop->sid;
    $shop_id = $shop->shop_id;
    if($t->return_order <> 0) {
        $order_auto_downloadjdsellback = getSysCfg($db, 'order_auto_downloadjdsellback', 0);
        if(!$order_auto_downloadjdsellback)
        {
            logx("josfbp loadJdTrade tid:{$t->order_id}, error_msg:退换单不下载", $sid.'/Trade');
            return true;
        }
    }

    global $zhi_xia_shi;
    $delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 0;    //0未付款1部分付款2已付款
    $trade_refund_status = 0;   //退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    //$paid = 0; //已付金额, 发货前已付
    $trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
    $is_external = 0;   // is_processed is more reasonable
    $voucher = 0;
    //$voucher_type_list = array(39, 41, 52);
    $tid = $t->order_id;

    $coupons = $t->coupon_detail_list;
    //总折扣 = 商家优惠金额 
    $total_discount = $t->seller_discount;
    //应收金额 = 订单货款金额(order_seller_price) + 运费(freight_price)
    $receivable = bcadd(@$t->order_seller_price,@$t->freight_price);

    //根据付款方式，确定已付金额paid
    if($t->pay_type == '1-货到付款')
    {
        $delivery_term=2;
    }
    else if ('HuoDaoFuKuanQueRen' != $t->order_state)
    {//订单状态中除了货到付款确认，其他都是已付款
        $pay_status = 2;
    }
    if('DengDaiTuiKuan' == $t->order_state || 'TuiKuanZhong' == $t->order_state)
    {
        $trade_refund_status = 1;
        $order_refund_status = 2;
    }

    //订单状态处理
    switch($t->order_state)
    {
        case 'LOCKED'://锁定
        case 'ZiTiTuiHuo'://自提退货
        case 'PeiSongTuiHuo'://配送退货
        case 'SuoDing'://锁定
        case 'DengDaiTuiKuan'://等待退款
        case 'TuiKuanZhong'://退款中
        {
            if(!empty($t->logistics_id))//物流公司不为空，表示已发货
            {
                $trade_status = 50;//已发货
                $is_external = 1;//抓单时已发货，未经系统系统处理的订单
            }
            else//物流公司为空，未发货
            {
                $trade_status = 30;//待发货
                $process_status = 10;//待递交
                $trade_refund_status = 1;//
                $order_refund_status = 2;
            }
            break;
        }
        case 'TRADE_CANCELED'://订单取消
        {
            $trade_status = 90;
            $trade_refund_status = 3;//全部退款
            $order_refund_status = 5;//退款成功
            break;
        }
        case 'DianZhangZuiZhongShenHe'://店长最终审核
        case 'DengDaiDaYin'://等待打印
        case 'DengDaiChuKu'://等待出库
        case 'DengDaiDaBao'://等待打包
        case 'DengDaiFaHuo'://等待发货
        case 'DengDaiKeHuHuiFu'://等待客户回复
        case 'ChangShangWanCheng'://
        case 'DengDaiZaiShenHe'://等待再审核
        case 'DuiZhangZhong'://对账中
        {
            $trade_status = 30;
            $process_status = 10;
            break;
        }
        case 'ZiTiTuZhong'://自提途中
        case 'ShangMenTiHuo'://上门提货
        case 'DengDaiHuiZhi'://等待回执
        case 'DengDaiQueRenShouHuo'://等待确认收货
        {
            $trade_status = 50;
            $is_external = 1;//抓单时已发货
            break;
        }
        case 'HuoDaoFuKuanQueRen'://货到付款确认
        {
            $trade_status = 60;
            $process_status = 10;
            $is_external = 1;//抓单时已发货
            break;
        }
        case 'WanCheng'://完成
        case 'ServiceFinished'://服务完成
        {
            $trade_status = 70;
            $is_external = 1;//抓单时已发货
            break;
        }
        default:
        {
            logx("ERROR $sid invalid_trade_status $tid {$t->order_state}",$sid.'/Trade', 'error');
        }
    }

    if($trade_status<50){
        return true;
    }else if($trade_status>=50 && $trade_status<=70){
        $process_status = 10;
    }

    //邮费
    $post_fee = $t->freight_price;
    //发票
    $invoiceInfo = $t->invoice_info;//发票信息
    if(strpos($invoiceInfo, ';') === false)//发票信息为空
    {
        $invoiceType = '';
        $invoice_title = '';
        $invoiceContent = '';
        $invoice_type = 0;//无发票类型
    }
    else//发票信息不为空
    {
        $invoiceInfo = explode(';', $invoiceInfo);

        $invoiceType = substr(strstr($invoiceInfo[0], ':'), 1);
        $invoice_title = substr(strstr($invoiceInfo[1], ':'), 1);
        $invoiceContent = substr(strstr($invoiceInfo[2], ':'), 1);
        $invoice_type = 1;//普通发票
    }
    if (!empty($t->vat_invoice_info))//增值税发票
    {
        $invoice_type = 2;
    }

    $orders = & $t->item_info_list;

    $orderId = 1;
    $order_arr = array();

    $trade_share_discount = $total_discount;//分摊折扣

    //cpupons优惠信息处理
    for ($i = 0; $i < count($coupons); ++$i)
    {
        $p = & $coupons[$i];
        if (empty($p->coupon_type))
        {
            continue;
        }

        $type = substr($p->coupon_type, 0, 2);

        if (!empty($p->sku_id))
        {
            $trade_share_discount = (float)$trade_share_discount- (float)$p->coupon_price;
        }
        /*else if(in_array($type, $voucher_type_list))
        {
            $voucher = bcadd($voucher, $p->coupon_price);
        }*/
        if("100-店铺优惠" == $p->coupon_type)
        {
            $merchant_discount = (float)$p->coupon_price;//店铺优惠
        }

        if (41 == $type)
        {
            $is_bonus = 1;//京东券优惠
        }
        else
        {
            $is_bonus = 0;
        }

        $discount_list[] = array
        (
            'platform_id' => 3,
            'tid' => $tid,
            'oid' => @$p->sku_id,
            'sn' => '',
            'type' => $type,
            'name' => $p->coupon_type,
            'is_bonus' => $is_bonus,//是否是京东优惠券
            'detail' => '',
            'amount' => $p->coupon_price
        );
    }
    //voucher为了计算COD订单已支付多少
    $voucher = bcadd($voucher, @$t->balance_used);

    //以下为邮费、已付时行分摊
    $left_post = $post_fee;//剩下的分担邮费
    $left_share_discount = $trade_share_discount;//剩余的分担折扣
    $left_voucher = $voucher;//已付款分担
    $trade_fee = bcsub(bcadd($receivable, $trade_share_discount),@$t->freight_price);
    //(float)$receivable + (float)$trade_share_discount - (float)@$t->freight_price;
    $order_count = count($orders);
    $goods_count = 0;
    $counter = 0;
    
    for($i = 0; $i < $order_count; $i++)
    {
        $o = & $orders[$i];

        if(empty($o->outer_sku_id))
        {
            $counter++;
            if(0 == $counter%10)    resetAlarm();
            
            //@logx("jos_fbp tid: $tid sku_id: {$o->sku_id} ware_id: {$o->ware_id} product_no: {$o->product_no} outer_sku_id: {$o->outer_sku_id}", $sid);
            get_sku_info($sid, $db, $shop->shop_id, $shop->key, $shop->secret, $shop->session, $o);
        }
        
        $spec_no = trim(@$o->outer_sku_id);
        if(iconv_strlen($spec_no,'UTF-8')>40)
        {
            logx("GOODS_SPEC_NO_EXCEED\t{$spec_no}\t".@$o->sku_name,$sid.'/Trade', 'error');
            $message = '';
            if(iconv_strlen($spec_no, 'UTF-8')>40)
                $message = "{$message}规格商家编码超过40字符:{$spec_no}";
            //发即时消息
            $msg = array(
                'type' => 10,
                'topic' => 'trade_deliver_fail',
                'distinct' => 1,
                'msg' => $message,
            );
            SendMerchantNotify($sid, $msg);

            $spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
        }

        $num = $o->item_total;
        $goods_count += (int)$num;
        $price = $o->jd_price;
        $goods_fee = floatval($price) * $num;

        $oid = $tid . ':' . $o->sku_id;
        if(isset($order_arr[$oid]))
        {
            $oid = $oid . ':' . $orderId;
            ++$orderId;
        }
        $order_arr[$oid] = 1;

        $discount = 0;//子订单折扣
        for ($j = 0; $j < count($coupons); ++$j)
        {//订单商品list的规格id和折扣list的规格id
            if ($o->sku_id == $coupons[$j]->sku_id)
            {
                $discount = $coupons[$j]->coupon_price;
                array_splice($coupons, $j, 1);//益处当前的商品折扣信息
                break;
            }
        }

        $goods_fee = (float)$price* $num - (float)$discount;

        if ($i == $order_count - 1)
        {
            $goods_share_amount = $left_share_discount;
            $share_post = $left_post;
            if (2 == $delivery_term)
            {
                $order_paid = $left_voucher;
            }
        }
        else
        {
            $goods_share_amount = $trade_fee>0?(float)bcdiv(bcmul($trade_share_discount, $goods_fee), $trade_fee):0;
            $left_share_discount = (float)$left_share_discount - (float)$goods_share_amount;

            $share_post = $trade_fee>0?(float)bcdiv(bcmul($post_fee, $goods_fee), $trade_fee):0;
            $left_post = (float)$left_post- (float)$share_post;

            if (2 == $delivery_term)
            {
                $order_paid = $trade_fee>0?bcdiv(bcmul($voucher, $goods_fee), $trade_fee):0;
                $left_voucher = (float)$left_voucher- (float)$order_paid;
            }
        }
        //share_amount分摊后子订单价格
        $share_amount = (float)$goods_fee - (float)$goods_share_amount;

        if ($delivery_term != 2)
        {
            $order_paid = bcadd($share_amount, $share_post);
        }

        $order_list[] = array
        (
            'platform_id' => 3,
            "shop_id"        => $shop_id,
            'tid' => $tid,
            'oid' => $oid,
            'status' => $trade_status,
            'refund_status' => $order_refund_status,
            'order_type' => 0,
            'invoice_type' => $invoice_type,
            'bind_oid' => '',
            'goods_id' => trim(@$o->ware_id),//商品id
            'spec_id' => trim(@$o->sku_id),//规格id
            'goods_no' => @$o->product_no,//商品编码
            'spec_no' => $spec_no,//规格编码
            'goods_name' => iconv_substr(@$o->sku_name,0,255,'UTF-8'),
            'spec_name' => iconv_substr($o->sku_name,0,100,'UTF-8'),
            'refund_id' => '',
            'num' => $num,
            'price' => $price,
            'adjust_amount' => 0,       //手工调整,特别注意:正的表示加价,负的表示减价
            'discount' => $discount,            //子订单折扣
            'share_discount' => $goods_share_amount,    //分摊优惠
            'total_amount' => $goods_fee,       //分摊前扣除优惠货款num*price+adjust-discount
            'share_amount' => $share_amount,        //分摊后货款num*price+adjust-discount-share_discount
            'share_post' => $share_post,    //分摊邮费
            'refund_amount' => 0,
            'is_auto_wms' => 1,
            'wms_type' => 2,
            'warehouse_no' => '',
            'logistics_no' => '',
            'paid' => $order_paid, // jd seems no refund in trade api
            'created' => array('NOW()')
        );

    }

    $receiver_address = @$t->consignee_info->full_address;//收货地址
    $receiver_city = @$t->consignee_info->city;         //城市
    $receiver_district = @$t->consignee_info->county;   //区县
    $receiver_mobile = @$t->consignee_info->mobile;     //手机
    $receiver_name = @$t->consignee_info->fullname;     //姓名
    $receiver_phone = @$t->consignee_info->telephone;   //电话
    $receiver_state = @$t->consignee_info->province;    //省份

    //将地址中省市区去掉
    $prefix = $receiver_state . $receiver_city . $receiver_district;
    $len = iconv_strlen($prefix, 'UTF-8');
    if(iconv_substr($receiver_address, 0, $len, 'UTF-8') == $prefix)
        $receiver_address = iconv_substr($receiver_address, $len, 256, 'UTF-8');

    $receiver_state = jdProvince($receiver_state);

    if(in_array($receiver_state, $zhi_xia_shi))
    {
        $receiver_district = $receiver_city;
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

    if(!empty($receiver_mobile) && !empty($receiver_phone) && $receiver_mobile != $receiver_phone)
        $mobile = $receiver_mobile . " " . $receiver_phone;
    else if(!empty($receiver_mobile))
        $mobile = $receiver_mobile;
    else
        $mobile = $receiver_phone;


    if(!empty($t->pin))
    {
        $nick = trim($t->pin);
    }
    else if(!empty($receiver_mobile))
    {
        $nick = 'JD'.$receiver_mobile;
    }
    else
    {
        $nick = 'JD'.$receiver_phone;
    }

    $trade_list[] = array
    (
        'platform_id' => 3,
        'shop_id' => $shop->shop_id,
        'tid' => $tid,
        'trade_status' => $trade_status,
        'pay_status' => $pay_status,
        'refund_status' => $trade_refund_status,
        'process_status' => $process_status,

        'delivery_term' => $delivery_term,
        'trade_time' => dateValue($t->order_start_time),
        'pay_time' => dateValue(@$t->payment_confirm_time),

        'buyer_nick' => iconv_substr($nick,0,100,'UTF-8'),
        'buyer_email' => '',
        'buyer_area' => '',
        'pay_id' => '',
        'pay_account' => '',

        'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
        'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
        'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
        'receiver_zip' => '',
        'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
        'to_deliver_time' => @$t->delivery_type,

        'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
        'logistics_type' => -1,

        'invoice_type' => $invoice_type,
        'invoice_title' => iconv_substr($invoice_title,0,255,'UTF-8'),

        'buyer_message' => iconv_substr(@$t->order_remark,0,1024,'UTF-8'),
        'remark' => iconv_substr(@$t->vender_remark,0,1024,'UTF-8'),
        'remark_flag' => 0,

        'end_time' => dateValue(@$t->order_end_time),
        'wms_type' => 2,
        'warehouse_no' => '',
        'stockout_no' => '',
        'logistics_no' => iconv_substr(@$t->waybill,0,40,'UTF-8'),
        'is_auto_wms' => 1,
        'is_external' => $is_external,

        'goods_amount' => @$t->order_total_price,
        'post_amount' => $post_fee,
        'receivable' => $receivable,
        'discount' => $total_discount,
        'paid' => (2 == $delivery_term) ? $voucher : $receivable,
        'received' => (2 == $delivery_term) ? $voucher : $receivable,

        'platform_cost' => 0,

        'order_count' => $order_count,
        'goods_count' => $goods_count,

        'cod_amount' => (2 == $delivery_term) ? @$t->order_payment : 0,
        'dap_amount' => (2 == $delivery_term) ? 0 : $receivable,
        'refund_amount' => 0,
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,

        'created' => array('NOW()')
    );
    if(count($order_list) >= 100)
    {
        return putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
    }

    return true;
}
