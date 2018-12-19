<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/bbw/bbwClient.php');

function bbwDownloadTradeList(&$db,$appkey, $appsecret, $shop,$countLimit,$start_time, $end_time, $save_time,&$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;
	$page_size = 300;
	if($save_time) 
		$save_time = $end_time;
	$end_time = $end_time - 1800;//延时30分钟，原因是在11点钟拉取 10点到11点的订单，那么接口只能返回 10:00-10:30分钟的，10点30分钟下单后30分钟内的订单需要在11点30才能获取到
	$ptime = $end_time;
	$start_time = $start_time - 1800;
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	logx("bbwDownloadTradeList $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time),  $sid.'/Trade');
	$session = $shop->session;
	$bbw = new bbwClient($appkey,$appsecret,$session);
	$order_list = array();
	$trade_list = array();
	$discount_list = array();
	$loop_count = 0;
	while($ptime > $start_time)
	{
		$loop_count++;
		if($loop_count > 1) resetAlarm();

		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
		logx("bbwDownloadTradeList $shopId start_time:" . date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time),$sid.'/Trade');
		$retval=$bbw->getOrder(-1,1,$page_size,"modified_time",$ptime,$end_time);

		if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
		{
			$error_msg = $retval->error_msg;
			if($error_msg == '没有订单数据')
			{
				$end_time = $ptime + 1;
				continue;
			}
            $error_msg['info'] = $error_msg;
            $error_msg['status'] = 0;
            logx("bbwDownloadTradeList bbw->getOrder fail", $sid.'/Trade','error');

			return TASK_OK;
		}			
		$trades = $retval->data;
		$total_results = $retval->count;
		if($total_results <= $page_size)
		{
			for($j =0; $j < count($trades); $j++)
			{
				$retval=$trades[$j];	   
				if(!loadBbwTradeImpl($shop, $retval, $trade_list, $order_list, $discount_list))
				{
					continue;
				}
			}
		}
		else
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			for($k=$total_pages; $k>=1; $k--)
			{
				$retval=$bbw->getOrder(-1,$k,$page_size,"modified_time",$ptime,$end_time);
				//sleep(1);//频率限制
				if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
				{
					$error_msg = $retval->error_msg;

                    $error_msg['info'] = $error_msg;
                    $error_msg['status'] = 0;
                    logx("bbwDownloadTradeList bbw->getOrder fail", $sid.'/Trade','error');

					return TASK_OK;
				}

				$trades = $retval->data;
					
				for($j =0; $j < count($trades); $j++)
				{
					$total_trade_count += 1;
					$retval=$trades[$j];	   
					if(!loadBbwTradeImpl($shop, $retval, $trade_list, $order_list, $discount_list))
					{
						continue;
					}
					if(count($order_list) >= 100)
					{
						if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
						{
								return TASK_SUSPEND;
						}
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
			return TASK_SUSPEND;
		}
	}	
	if($save_time)
	{
		logx("order_last_synctime_{$shopId}".'上次抓单时间保存 bbw平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
	}
	return TASK_OK;
}
function loadBbwTradeImpl($shop,&$trade,&$trade_list,&$order_list,&$discount_list)
{
	$province = trim(@$trade->province);
	$city = trim(@$trade->city);
	$district = trim(@$trade->county);
	$receiver_area=$province." ".$city." ".$district;
	
		
	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);
	
	$trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
	$process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	$pay_status = 0;	//0未付款 1部分付款 2已付款
	$paid = 0;
	//trade退款状态
	$trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
	//order退款状态
	$order_refund_status = 0;	//0无退款 1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
	$tid = $trade->oid;
	switch($trade->status)
	{
		case 1:
		{
			$trade_status = 30;
			$process_status = 10;
			$pay_status = 2;
			break;
		}
		case 2:
		{
			$trade_status = 50;
			$pay_status = 2;
			break;
		}
		case 3:
		{
			$trade_status = 70;
			$pay_status = 2;
			break;
		}
		case 4:
		{
			$trade_status = 90;
			break;
		}
		default:
		{
			logx("ERROR $shop->sid bbw invalid_trade_status $tid {$trade->status}", $shop->sid.'/Trade'.'error');

		}
	}
	$goods_amount = bcsub($trade->total_fee,$trade->shipping_fee);
	$other_amount = bcsub($trade->total_fee,$trade->payment);
	$left_post = $trade->shipping_fee;
	$orders=$trade->item;
	$goods_count = 0;
	$refund_count =0;//订单退款数量
	$refund_amount=0;
	
	if(empty($orders)) return true;	
	
	if(!empty($trade->invoice_type))
		$invoice_type=1;
	else
		$invoice_type=0;
	
	$orderId = 1;
    $order_arr = array();
	
	for($i = 0; $i < count($orders); $i++)
	{
		$o=$orders[$i];
		$goods_no = trim(@$o->outer_id);
		$spec_no = trim(@$o->outer_id);
		if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
		{

			$message = '';
			if(iconv_strlen($goods_no, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goods_no}";
			if(iconv_strlen($spec_no, 'UTF-8')>40)
				$message = "{$message}规格商家编码超过40字符:{$spec_no}";

            $error_msg['info'] = $message;
            $error_msg['status'] = 0;

            logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t".@$o->title, $shop->sid.'/Trade'.'error');


			//发即时消息
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$goods_no = iconv_substr($goods_no, 0, 40, 'UTF-8');
			$spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
		}
		if( 0 !=$o->refund_status)
		{
			switch($o->refund_status)
			{
				case 1: $order_refund_status = 3; break;//退款中
				case 2: 
				{	
					$order_refund_status = 5;
					$refund_amount=$o->subtotal;
					$trade_status=80;
					break;//退款成功
				}
				case 3: $order_refund_status = 1; break;//取消退款
			}
			$refund_count++;
		}
		else
		{
			$order_refund_status = 0;
		}
		$discount =	bcsub($o->origin_price,$o->price);
		//$total_amount =bcmul($o->num,$o->price);
		if ($i == count($orders) - 1)
		{
			$share_post = $left_post;
		}
		else
		{			
			$share_post = bcdiv(bcmul($trade->shipping_fee,$o->subtotal), $goods_amount);
			$left_post = bcsub($left_post, $share_post);
		}
		//没有货到付款，所以货到$order_paid=0没添加
			$order_paid = bcadd($o->subtotal, $share_post);

		
		$goods_count += $o->num;
		
		$oid = iconv_substr('BBW'.$trade->oid.$o->outer_id,0,40,'UTF-8');
		if(isset($order_arr[$oid]))
        {
            $oid = $oid . ':' . $orderId;
            if (mb_strlen($oid,'utf8') > 40)
            {
            	$oid = $trade->oid . ':' . $orderId;
            }
            ++$orderId;
        }
        $order_arr[$oid] = 1;



        $order_list[] = array
		(
			"shop_id"=> intval($shop->shop_id),
			"rec_id"=>0,
			"platform_id"=>22,
			"tid"=>$trade->oid,		
			"oid"=>$oid,
			"status"=>$trade_status,
			"process_status"=>$process_status,
			"refund_status"=>$order_refund_status,
			"invoice_type"=>$invoice_type,
			"invoice_content"=>@$trade->invoice_name,
			"goods_id"=>$o->iid,  //$o->goods_num
			"spec_id"=>$o->sku_id,
			"goods_no"=>$goods_no,
			"spec_no"=>$spec_no,
			"goods_name"=>iconv_substr($o->title,0,255,'UTF-8'),
			"spec_name"=>iconv_substr($o->sku_properties,0,100,'UTF-8'),
			"num"=>$o->num,
			"price"=>$o->price,
			"discount"=>0,//$discount
			"total_amount"=>$o->total_fee,
			"share_amount"=>$o->subtotal,
			"share_post"=>$share_post,
			'share_discount' => (float)$o->total_fee-(float)$o->subtotal,
			"refund_amount"=>$refund_amount,
			"remark"=>@$trade->remark,
			"paid"=>$order_paid,
			"created" => array('NOW()')
		);


        /*
        if(bccomp($discount, 0))
        {
            $discount_list[] = array
            (
                'platform_id' => 22,
                'tid' => $trade->oid,
                'oid' => 'BBW'.$o->outer_id,
                'sn' => '',
                'type' => '特卖',
                'name' => '特卖优惠',
                'is_bonus' => 0,
                'detail' =>'特卖专场ID:'.@$trade->event_id,
                'amount' => $discount,
                'created' => array('NOW()')
            );
        }
        */
	}
	
	if($refund_count != count($orders) && $refund_count != 0)
	{
		$trade_refund_states = 2;
	}
	else if($refund_count == 0)
	{
		$trade_refund_states = 0;
	}
	else
	{
		$trade_refund_states = 3;
	}
	$trade_list[] = array
	(
		"platform_id"=>22,						
		"shop_id"=>$shop->shop_id,	
		"tid"=>$trade->oid,
		"process_status"=>$process_status, 		
		"trade_status"=>$trade_status,
		"delivery_term"=>1,                    //贝贝网目前不支持货到付款		
		"pay_status"=>$pay_status,
		"refund_status"=>$trade_refund_states,
		"order_count"=>$trade->item_num,       //贝贝网购买总数为总子订单数
		"goods_count"=>$goods_count,
		"trade_time"=>$trade->create_time,
		"pay_time"=>@$trade->pay_time,
		"end_time"=>($trade->end_time ==''? '0000-00-00 00:00:00':$trade->end_time),
		"buyer_message"=>@iconv_substr($trade->remark,0,1024,'UTF-8'),
		"remark"=>@iconv_substr($trade->seller_remark,0,1024,'UTF-8'),
		"buyer_nick"=>iconv_substr($trade->nick,0,100,'UTF-8'),
		"id_card"=>@$trade->member_card,
		"receiver_name"=>iconv_substr($trade->receiver_name,0,40,'UTF-8'),
		"receiver_province"=>$province_id,
		"receiver_city"=>$city_id,
		"receiver_district"=>$district_id,
		"receiver_address"=>@iconv_substr($trade->address,0,255,'UTF-8'),
		"receiver_mobile"=>iconv_substr($trade->receiver_phone,0,40,'UTF-8'),
		"receiver_area"=>iconv_substr($receiver_area,0,64,'UTF-8'),
		"receiver_hash"=>md5($trade->nick.$receiver_area.$trade->address.$trade->receiver_phone.''.''),
		"goods_amount"=>$goods_amount,
		"post_amount"=>$trade->shipping_fee,
		//"other_amount"=>$other_amount,
		"discount"=>(float)$goods_amount-(float)$trade->payment,
		"receivable"=>$trade->payment,
		"invoice_type"=>$invoice_type,
		"invoice_title"=>iconv_substr(@$trade->invoice_name,0,255,'UTF-8'),
		"received"=>0,
		"dap_amount"=>$trade->payment,
		"refund_amount"=>$trade->payment,
		"paid"=>$trade->payment,
		"created" => array('NOW()')
	);
	return true;
}
function bbwTradesDetail(&$db, $appkey, $appsecret,$trades,&$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	$shopId = $trades->shop_id;
	$tids = $trades->tids;
	$session = $trades->session;
	
	$trade_list = array();
    $order_list = array();
	$discount_list=array();
		
	$bbw = new bbwClient($appkey,$appsecret,$session);
	
	for($i=0 ;$i<count($tids);$i++)
	{
		$retval=$bbw->getOrderByNo($tids[$i]);
		if(API_RESULT_OK != bbwErrorTest($retval,$db,$shopId))
		{
			$error_msg = $retval->error_msg;

            $error_msg['info'] = $error_msg;
            $error_msg['status'] = 0;

            logx("bbwTradesDetail ERROR $sid bbw->getOrderByNo fail ".$error_msg['info'], $sid. "/Trade",'error');

			return TASK_OK;
		}
		
		if(!isset($retval->data))
		{
			releaseDb($db);
			$error_msg = '读取订单信息失败';

            $error_msg['info'] =  $error_msg;
            $error_msg['status'] = 0;

            logx("bbwTradesDetail $shopId ".$error_msg['info'], $sid. "/Trade",'error');

			return TASK_SUSPEND;
		}
		
		if(!loadBbwTradeImpl($trades,$retval->data,$trade_list,$order_list,$discount_list))
		{
            $error_msg['info'] = $retval->error_msg;;
            $error_msg['status'] = 0;

            logx("loadBbwTradeImpl error in bbwTradesDetail  $shopId ".$error_msg['info'], $sid. "/Trade",'error');

			return TASK_SUSPEND;
		}
	}
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			releaseDb($db);
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
	
}
?>
