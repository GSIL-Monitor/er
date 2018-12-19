<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/xhs/XhsClient.php');
function xhsProvince($province)
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


function loadXhsTrade(&$db,$trades, &$trade_list, &$order_list, &$discount_list, &$retval)
{
    $sid = $trades->sid;
    $shopid = $trades->shop_id;
    $t = &$retval->data;
    if( !isset($t->package_id) || empty($t->package_id)) {
            logx("xhs loadXhsTrade error_msg:订单为空",$sid.'/TradeSlow');
            return true;
    }
    if(substr($t->package_id,0,1)=='R') {
            logx("xhs loadXhsTrade tid:{$t->package_id}, error_msg:退换单不下载", $sid.'/TradeSlow');
            return true;
    }

    $delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
    $pay_status = 2; // 0未付款1部分付款2已付款
    $trade_refund_status = 0; // 退款状态 0无退款 1申请退款 2部分退款 3全部退款
    $order_refund_status = 0; // 0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
    $paid = 0; // 已付金额, 发货前已付
    $trade_status = 10; // 订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
    $process_status = 70; // 处理状态 10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消

    $tid = $t->package_id;//订单编号
    $status = $t->status;//小红书平台订单状态 waiting 待配货 ,shipped 已发货, received 已收货
    

    $paid = $t->pay_amount; //实际支付金额（包含运费）
   
    //支付方式处理
    
    $pay_method = 1;
    switch ($status)//订单状态
    {
        case 'waiting'://待配货
            {
                $process_status = 10;//订单进程状态（系统的）
                $trade_status = 30;
                $pay_status = 2;
                break;
            }
        case 'shipped'://已发货
            {
                $process_status = 40;
                $trade_status = 50;//订单的平台状态
                $pay_status = 2;//支付状态
                break;
            } 
        case 'received'://已收货
            {

                $process_status = 60;
                $trade_status = 60;
                $pay_status = 2;
                break;
            }
        
        default:
            logx ( "invalid_trade_status tid : {$tid}  status : {$status}", $sid .'/TradeSlow');
            break;
    }

    //除以100是因为接口返回的数据是以分为单位的
    //$post_fee = bcdiv($t->dealShippingFee, 100, 2);//邮费
    $receivable = $t->pay_amount;//订单总额

    $total_trade_fee = 0;//货款
    $total_discount =0;//总优惠金额

    //分摊邮费

    $orders = $t->item_list;//订单信息
    $logistics_no = $t->express_no;
    $order_count = count($orders);//子订单数量
    $goods_count = 0;//商品数量

    for($k = 0; $k<count($orders); $k++)
    {
        $o = $orders[$k];
        $order_num = $o->qty;//购买数量
        $goods_count += (int)$order_num;//购买商品总数量
        $order_price = $o->price;//单价
        $goods_fee = bcmul($order_num, $order_price);//此函数将二个高精确度的数字相乘，传入二个字符串，以左边的数字字符串 (left operand) 乘以右边的 (right operand) 数字字符串。结果亦以字符串>返回  当前商品总价格

        //分摊优惠
        //$share_post = bcdiv(bcmul($post_fee, $goods_fee), $total_trade_fee);//（邮费*货款）/应付金额
        $share_amount = bcmul($order_num, @$o->pay_price);//分摊后子订单价格=数量*实际支付单价
        $share_discount = bcmul($order_num , bcsub(@$o->price,@$o->pay_price));//子订单分摊优惠
        $total_trade_fee += $goods_fee;
        $total_discount += $share_discount;
        $oid = "xhs".$tid.@$o->barcode;
        $order_list[] = array
        (
            "platform_id"=>56,//平台id
            "tid"=>$tid,//订单号
            'shop_id'=>$shopid,
            //订单编号          
            "oid"=> iconv_substr($oid,0,40,'UTF-8'),
            'process_status' => $process_status,//处理订单状态
            "status"=> $trade_status,   //平台订单状态
            "refund_status"=> $order_refund_status,//订单退款状态
            //平台货品id
            "goods_id"=> $o->barcode,//商品sku
            "spec_id" => '',
            "goods_no" => $o->barcode,
            "spec_no" => '',
            "spec_name" =>$o->specification,

            //货品名
            "goods_name"=>iconv_substr(@$o->item_name,0,255,'UTF-8'),//商品名称

            //数量
            'num'=>$order_num, //购买数量
            //商品单价          
            'price'=>$order_price,//单价
            //优惠金额
            'discount'=>0,
            'share_discount' => $share_discount,    //分摊优惠
            'share_amount'=>$share_amount,//分摊后子订单价格
            'total_amount'=>$goods_fee,//货款
            //分摊邮费
            'share_post'=>0,//分摊邮费
            //分摊优惠--相当于手工调价
            'paid'=>$share_amount,//分摊已付金额
            'logistics_no'=>$logistics_no,

            'created' => array('NOW()')
        );
    }//子订单处理结束

    //地址处理
    $receiver_province = xhsProvince($t->province);//对省进行处理
    $receiver_city = @$t->city;
    $receiver_district = @$t->district;
    if(empty($t->district)){
        $receiver_address=substr(strchr( @$t->receiver_address, @$t->city), strlen(@$t->city));
    }elseif($t->district=='其它区'){
        $receiver_address = @$t->receiver_address;
    }else{
        $receiver_address=substr(strchr( @$t->receiver_address, @$t->district), strlen(@$t->district));
    }
    
    $receiver_area = @$receiver_province.' '.$receiver_city.' '.$receiver_district;//省 市 区
    getAddressID ( $receiver_province, $receiver_city, $receiver_district, $province_id, $city_id, $district_id );


    $trade_list[] = array
    (
        "tid"=>$tid,                            //订单号
        "platform_id"=>56,                      //平台id
        "shop_id"=>$shopid,             //店铺ID
        "process_status"=>$process_status,      //处理订单状态
        "trade_status"=>$trade_status,          //平台订单状态
        "refund_status"=>$trade_refund_status,  //退货状态
        'pay_status'=>$pay_status,//支付状态
        'pay_method'=>$pay_method,//支付方式

        'order_count'=>$order_count,//子订单数量
        'goods_count'=>$goods_count,//总商品数量

        "trade_time"=>date('Y-m-d H:i:s',@$t->time),     //下单时间s
        'pay_time' => date('Y-m-d H:i:s',@$t->pay_time),  //付款时间

        "buyer_message"=>'',    //买家购买附言
        "buyer_email"=>'',
        "buyer_area"=>'',
        "buyer_nick"=>'',//买家昵称

        "receiver_name"=>iconv_substr(@$t->receiver_name,0,40,'UTF-8'),//收货人姓名
        'receiver_province' => $province_id,
        'receiver_city' => $city_id,
        'receiver_district' => $district_id,
        "receiver_area"=> $receiver_area,       //省市区用空格分隔
        "receiver_address"=> $receiver_address, //地址
        "receiver_zip"=> '',        //邮编
        "receiver_mobile"=> @$t->receiver_phone,            //电话
        'receiver_telno' => @$t->receiver_phone,
        "receiver_hash" => md5($receiver_province.$receiver_city.$receiver_district.$receiver_address.$t->receiver_phone),//（收获省市区 收获地址 手机）

        'to_deliver_time' => '',
        "logistics_type"=>-1,
        "logistics_no"=> @$t->express_no,

        'goods_amount'=>$total_trade_fee, //货款（应付金额）,未扣除优惠
        'post_amount'=>0, //邮费
        'discount'=>$total_discount, //优惠金额
        'receivable'=>$receivable, //应收金额
        'paid'=>2 == $delivery_term ? 0 : $paid, //买家已付金额
        'received'=>$paid, //已从平台收款的金额
        'cod_amount' => 2 == $delivery_term ? $paid : 0, //货到付款金额
        'dap_amount' => 2 == $delivery_term ? 0 : $paid, //款到发货金额

        'platform_cost'=>0,

        'invoice_type'=>0, //发票类别，0 不需要，1普通发票，2增值税发票


        "delivery_term"=>$delivery_term,        //是否货到付款
        "pay_id"=>'',                           //支付宝账号

        'refund_amount' => 0,//退款
        'trade_mask' => 0,
        'score' => 0,
        'real_score' => 0,
        'got_score' => 0,

        'created' => array('NOW()')
    );
    return true;
}

function downXhsTradesDetail (&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $trade_list = array();
    $order_list = array();
    $discount_list = array();

    $sid = $trades->sid;
    $shopid = $trades->shop_id;
    $tids =$trades->tids;
 //请求参数
    $xhs = new XhsClient();

    $url = "/ark/open_api/v0/packages/";
    

    for($i=0; $i<count($tids); $i++)
    {
        $tid = $tids[$i];
        //参数
        $url = $url.$tid;
        $system_param['timestamp'] = time();
        $system_param['app-key'] = $appkey;
        @$retval = $xhs->sendByGet($url,$appsecret,$system_param);
        $url = "/ark/open_api/v0/packages/";

        if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
        {
            $error_msg = $retval->error_msg;
                        logx ( "downXhsTradesDetail fail errCode:{$retval->error_code} error_msg:{$error_msg} ", $sid.'/TradeSlow' );

            return TASK_SUSPEND;
        }
        if(empty($retval->data))
        {
            $error_msg = '没有获取到订单信息';
            logx("downXhsTradesDetail xhs->sendByGet fail 2, error_msg:{$error_msg}", $sid.'/TradeSlow');
            return TASK_SUSPEND;
        }

        if(!loadXhsTrade($db, $trades, $trade_list, $order_list, $discount_list, $retval))
        {
            continue;
        }



        if(count($trade_list) >= 100)
        {
            if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
            {
                releaseDb($db);
                return TASK_SUSPEND;
            }
        }
    }

    //保存剩下的到数据库
    if(count($trade_list) > 0)
    {
        if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
        {
            releaseDb($db);
            return TASK_SUSPEND;
        }
    }

    releaseDb($db);

    return TASK_OK;
}

//异步下载（自动抓单）
function xhsDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
        $cbp = function(&$trades) use($trade_detail_cmd)
        {
                pushTask($trade_detail_cmd, $trades);
        };

        return xhsDownloadTradeListImpl($db, $appkey, $appsecret, $shop, 0, 0, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//同步下载(手动抓单)
function xhsSyncDownloadTradeList(&$db,$appkey, $appsecret, $shop,$countLimit, $start_time, $end_time, $total_trade_count,&$total_new, &$total_chg, &$error_msg)
{
        $total_trade_count = 0;
        $total_new = 0;
        $total_chg = 0;
        $error_msg = '';

        $cbp = function(&$trades) use($appkey, $appsecret, &$db, &$total_new, &$total_chg, &$error_msg)
        {
                downXhsTradesDetail($db,$appkey,
                        $appsecret,
                        $trades,
                        $new_trade_count,
                        $chg_trade_count,
                        $error_msg);

                $total_new += $new_trade_count;
                $total_chg += $chg_trade_count;
        };

        return xhsDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $countLimit, $total_trade_count, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}


//小红书
function xhsDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $countLimit, $total_trade_count, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{
        $ptime = $end_time;

        if($save_time)
            $save_time = $end_time;

        $sid = $shop->sid;
        $shopid = $shop->shop_id;

        logx("xhsDownloadTradeListImpl $shopid start_time:" . date('Y-m-d H:i:s', $start_time) . "end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/TradeSlow');

        $total_count = 0;

        $url = "/ark/open_api/v0/packages";
        $system_param['timestamp'] = time();
        $system_param['app-key'] = $appkey;

        $def_param['page_size'] = 50;

        $xhs = new XhsClient();

        while($ptime > $start_time)
        {
                if($ptime - $start_time > 3600*24) $ptime = $end_time - 3600*24 + 1;
                else $ptime = $start_time;

                $def_param['start_time'] = $ptime;
                $def_param['end_time'] = $end_time;
                $def_param['page_no'] = 1;
                
               $retval = $xhs->sendByGet($url, $appsecret,$system_param, $def_param);

                if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
                {
                        $error_msg = $retval->error_msg;
                        logx ( "xhsDownloadTradeListImpl $shop_id start_time:" . date ( 'Y-m-d H:i:s', $ptime ) . " end_time:" . date ( 'Y-m-d H:i:s', $end_time ).$error_msg, $sid.'/TradeSlow' );
                        return TASK_OK;
                }

                if(!isset($retval->data) || count($retval->data) == 0)
                {
                        $end_time = $ptime + 1;
                        logx("XhsTrade $shopid count: 0", $sid.'/TradeSlow');
                        continue;
                }

                $trades = $retval->data->package_list;
                //总条数
                $total_results = intval($retval->data->total_number);
                $total_count += $total_results;

                logx("XhsTrade $shopid count: $total_results", $sid.'/TradeSlow');

                //如果不足一页，则不需要再抓了
                if($total_results <= count($trades))
                {
                        $tids = array();
                        for($j =0; $j < count($trades); $j++)
                        {
                                $tids[] = $trades[$j]->package_id;
                      }

                        if(count($tids) > 0)
                        {
                                $shop->tids = $tids;
                                $cbp($shop);
                        }
                }else //超过一页，第一页抓的作废，从最后一页开始抓
                {
                        $total_pages = ceil(floatval($total_results)/50);

                        for($i=$total_pages; $i>=1; $i--)
                        {
                                $def_param['page_no'] = $i;

                                $retval = $xhs->sendByGet($url,$appsecret, $system_param, $def_param);

                                if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
                                {
                                        $error_msg = $retval->error_msg;
                                        logx("xhsDownloadTradeListImpl fail2 $error_msg", $sid.'/TradeSlow');
                                        return TASK_OK;
                                }

                                $tids = array();
                                $trades = $retval->data->package_list;
                                for($j =0; $j < count($trades); $j++)
                                {
                                        $tids[] = $trades[$j]->package_id;
                                }
                                if(count($tids) > 0)
                                {
                                        $shop->tids = $tids;
                                        $cbp($shop);
                                }
                        }
                }

                $end_time = $ptime + 1;
        }

        if($save_time)
        {
                setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
        }

        return TASK_OK;
}

?>