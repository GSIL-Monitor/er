<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Common/utils.php');

require_once(TOP_SDK_DIR . '/fn/FnClient.class.php');

//根据订单号下载详情
function downFnTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	$sid = $trades->sid;
	$shopId = $trades->shop_id;
	$session = $trades->session;
	$tids = & $trades->tids;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	$client = new FnClient($appkey,$appsecret);
	$client->setAuthSession($session);
	$method= "fn.trades.sold.getOrderDetail";
	$data = array(
	    "ogNo"=>"", //订单号
	);
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		$data['ogNo'] = $tid;
		$params = array(
    	    'method'=> $method,
    	    'params'=>@json_encode($data),
	    );
		$retval = $client->sendDataByCurl($params);
		if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("ERROR $sid downFnTradesDetail ".$error_msg['info'], $sid.'/Trade','error');
			return TASK_SUSPEND;
		}
		$pageVoList = $retval->data->pageVoList;
		if(is_array($pageVoList) && count($pageVoList)>0){
		    foreach ($pageVoList as $value) {
        	    if(!fnloadTradeImpl($trades,$value,$trade_list,$order_list,$discount_list))
            	{
            	    logx("fnloadTradeImpl error in downFnTradesDetail ogNo:{$tid}  packNo:".$value->packNo, $sid.'/Trade');
            	    continue;
            	}
		    }
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

//下载订单列表
function fnDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, 
	$save_time,&$scan_count, &$total_new, &$total_chg, &$error_msg)
{
    $trade_list = array();
    $order_list = array() ; 
    $discount_list = array() ;
	$ptime = $end_time;
	
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	
	
	$pagesize = 200;
	$currPage = 1;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$session = $shop->session;
	logx("fnDownloadTradeListImpl $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');
	
	$client = new FnClient($appkey,$appsecret);
	$client->setAuthSession($session);
	$method= "fn.trades.sold.get";//获取订单列表
    
	$total_count = 0;
	$loop_count = 0;	
	
	$trades_data = array(
	    "pageCount" => "{$pagesize}",       //每页条数
	    "orderType" => "",              //订单状态，（1-未付款 2-待发货 3-已发货 4-已完成 5-已取消）
	    "currPage" => "{$currPage}",        //当前页
	    "dateType" => "7",              //查询时间类型: 1-订单生成时间、2-支付时间、3-发货时间、4-收货时间、5-取消时间、6-交易成功时间、7-最新更新时间
	    "dateStart" => "" ,
	    "dateEnd" => "",
	);
	
	while($ptime > $start_time)
	{
		$loop_count++;
		if($loop_count > 1) resetAlarm();
		
		$ptime = ($ptime - $start_time > 3600*24)?($end_time - 3600*24 + 1):$start_time;
		logx(" $shopId start_time:".date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');
		
		$trades_data['dateStart'] = date('Y-m-d H:i:s', $ptime);
		$trades_data['dateEnd'] = date('Y-m-d H:i:s', $end_time);
		$params = array(
		    'method'=> $method,
		    'params'=>@json_encode($trades_data),
		);
		$retval = $client->sendDataByCurl($params);
		if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
		{
			if( $retval->code == 200 && (strpos($retval->error_msg, "查无资料") !== false)){ //转单厂商
				$end_time = $ptime + 1;
			    logx("fnTrade $shopId count: 0 go next one  msg:".$retval->error_msg, $sid.'/Trade');
		        continue;
		    }
			if( $retval->code == 200 && (strpos($retval->error_msg, "订单过于频繁") !== false)){
		        logx("fnTrade $shopId count: 0  this run over msg:".$retval->error_msg, $sid.'/Trade');
		        if(count($order_list) > 0)
		        {
		            if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $total_new, $total_chg, $error_msg, $sid))
		            {
		                return TASK_SUSPEND;
		            }
		        }
		        if($save_time)
		        {
		            logx("fnTrade $shopId time limited net run time:".$ptime, $sid.'/Trade');
					logx("order_last_synctime_{$shopId}".'上次抓单时间保存 fn平台 '.print_r($ptime,true),$sid. "/default");
					setSysCfg($db, "order_last_synctime_{$shopId}", $ptime);
		        }  
		    }
			
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] = 0;
			logx("ERROR $sid fnDownloadTradeListImpl fn.trades.sold.get fail ".$error_msg['info'], $sid.'/Trade' ,'error');
			return TASK_OK;
		}
		if(isset($retval->data) && (!isset($retval->data->totalRows) || !isset($retval->data->pageVoList)))
		{
			$end_time = $ptime + 1;
			logx("fnTrade $shopId count: 0", $sid.'/Trade');
			continue;
		}
		if(isset($retval->data->pageVoList) && count($retval->data->pageVoList) == 0)
		{
			$end_time = $ptime + 1;
			logx("fnTrade $shopId count: 0", $sid.'/Trade');
			continue;
		}
		
		$trades = $retval->data->pageVoList;
		//总条数
		$total_results = $retval->data->totalRows;
		
		logx("fnTrade $shopId count: $total_results", $sid.'/Trade');
		
		//不足一页，不需获取
		if($total_results <= count($trades) )
		{
			$tids = array();
			for($j =0; $j < count($trades); $j++)
			{
				$scan_count += 1;
			    $trade=$trades[$j];
				if(!fnloadTradeImpl($shop, $trade, $trade_list, $order_list, $discount_list)){
				    continue;
				}
			}
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/$pagesize);
			
			for($i=$total_pages; $i>=1; $i--)
			{
			    $trades_data['currPage'] = "{$i}";
				$params = array(
        		    'method'=> $method,
        		    'params'=>@json_encode($trades_data),
        		);
		        $retval = $client->sendDataByCurl($params);
				if(API_RESULT_OK != fnErrorTest($retval, $db, $shopId))
				{
					$error_msg['info'] = $retval->error_msg;
					$error_msg['status'] = 0;
					logx("fnDownloadTradeListImpl  fail2 ".$error_msg['info'], $sid.'/Trade');
					logx("ERROR $sid fnDownloadTradeListImpl-more than one page",$sid.'/Trade','error');
					return TASK_OK;
				}
				
				$trades = $retval->data->pageVoList;
				$tids = array();
				for($j =0; $j < count($trades); $j++)
				{
				    $scan_count += 1;
    				$trade=$trades[$j];
    				if(!fnloadTradeImpl($shop,$trade, $trade_list, $order_list, $discount_list)){
    				    continue;
    				}
    				if(count($order_list) >= 100)
    				{
    				    if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $total_new, $total_chg, $error_msg, $sid))
    				    {
    				        return TASK_SUSPEND;
    				    }
    				}
    				if($countLimit && $scan_count >= $countLimit)
    				    return TASK_SUSPEND;
				}
			}
		}
		
		$end_time = $ptime + 1;
		
	}
	if(count($order_list) > 0)
	{
	    if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $total_new, $total_chg, $error_msg, $sid))
	    {
	        return TASK_SUSPEND;
	    }
	}
	
	if($save_time)
	{
		logx("order_last_synctime_{$shopId}".'上次抓单时间保存 fn平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
	}
	
	return TASK_OK;
		
}


//下载订单模板
function fnloadTradeImpl($shop, &$trade, &$trade_list, &$order_list, &$discount_list)
{
	$platform_id = 31;//平台id
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$t = & $trade;
	$tid = $t->ogNo;//原始订单号
	$order_count = 0; 
	$goods_count = 0;
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$pay_status = 0;	        //0未付款1部分付款2已付款
	$delivery_term = 1;         //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	
	$paid = 0;//已付金额, 发货前已付
	
	//订单当前状态
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;	
	
	$paytime = "0000-00-00 00:00:00";
	$createtime = format_fn_data(@$t->insDt);
	//邮费
	$post_fee = 0;
	if(isset($t->freight)){
		$post_fee = @$t->freight;
	}
	if(!empty($t->payDt))
	{
		$pay_status = 2;
		$paid = $t->totalPrice;
		$paytime = format_fn_data(@$t->payDt);
	}

	$receivable =  $t->totalPrice;
	
	$status = $t->status;
	
	if($status == 1)//1.未付款
	{
		$trade_status=10;//未确认
		$process_status=10;
	}
	else if($status == 2)//2.未发货
	{
		$trade_status=30;
		$process_status=10;
	}
	else if($status == 3)//3.已发货
	{
		$trade_status=50;//已发货
		$is_external = 1;
	}
	else if($status == 4)//4.订单结束
	{
		$trade_status=70;//已完成 
		$is_external = 1;
	}
	else if($status == 5)//5.已取消 --用户取消订单后，如果付款，默认处理流程还是会把取消掉的订单复活，然后变成已付款
	{
		$trade_status=90;
	}
	else if($status == 6)//6.退款完成
	{
	    $trade_refund_status = 3;
	    $order_refund_status = 5;
	    $trade_status=80;
	}
	else if($status == 7)//7.未发货用户申请退款
	{
	    $trade_status=30;//未发货
	    $trade_refund_status = 1;
	    $order_refund_status = 2;
	}
	else if($status == 8)//8.已发货申请退款
	{
	    $trade_status=50;//已发货
	    $trade_refund_status = 1;
	    $order_refund_status = 2;
	}
	else if($status == 9)//9.交易关闭/已取消（逾期未付款）
	{
		$trade_status=90;
	}
	else if($status == 14 || $status == 15)
	{
		$trade_status=70;
	}
	else
	{
		logx("ERROR $sid invalid_trade_status {$tid} {$status}",$sid.'/Trade','error');
	}
	
	//发票
	if(isset($t->needInvoice) && $t->needInvoice == 1){
		$invoice_type = 1;
		$invoice_title = trim(@$t->invoiceName);//发票抬头
		$invoice_content = trim(@$t->invoiceContext);//发票内容
	}elseif(strpos(trim(@$t->invoiceKing),"_") === false &&  trim(@$t->invoiceKing) != "" && !isset($t->needInvoice)){
		$invoice_type = 1;
		$invoice_title = trim(@$t->invoiceName);//发票抬头
		$invoice_content = trim(@$t->invoiceContext);//发票内容
	}else{
		$invoice_type = 0;
		$invoice_title = "";
		$invoice_content = "";
	}

	
	//总优惠
	$total_discount = 0;
	if(isset($t->discountPrice)){
		$total_discount = $t->discountPrice;
	}
	
	$trade_share_discount = $total_discount;
	//以下为邮费、优惠时行分摊
	$left_post = $post_fee;
	$left_share_discount = $trade_share_discount;
	
	
	$trade_fee = bcadd(bcsub($t->totalPrice,$post_fee),(float)$total_discount);
	
	$orders = $t->itemList;
	$order_count = count($orders);
	
	$orderId = 1;
	$order_arr = array();
	
	for($i = 0; $i < $order_count; $i++)
	{
		$o = & $orders[$i];
		$spec_no = trim(@$o->barcode);//规格商家编码
		if(iconv_strlen($spec_no,'UTF-8')>40)
		{
			logx("$sid GOODS_SPEC_NO_EXCEED\t{$spec_no}\t{$o->name}", $sid.'/Trade','error');
			
			$message = '';
			if(iconv_strlen($spec_no, 'UTF-8')>40)
				$message = "{$message}规格商家编码超过40字符:{$spec_no}";

			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$spec_no = iconv_substr($spec_no, 0, 40, 'UTF-8');
		}

		$oid = $tid;
		if(isset($order_arr[$oid]))
		{
			$oid = $oid . ':' . $orderId;
			++$orderId;
		}
		$order_arr[$oid] = 1;
		
		if(isset($o->price)){
			$price = $o->price;
		}else{
			$price = $o->unitPrice;
		}
		$num = $o->qty;
		$goods_count += (int)$num;
		$suitDiscountPrice = $o->unitPrice;
		$order_discount = bcmul(bcsub($price,$suitDiscountPrice),$num);
		
		$goods_fee = bcmul($price, $num);
		
		if ($i == $order_count - 1)
		{
			$goods_share_amount = $left_share_discount;
			$share_post = $left_post;
		}
		else
		{
			$goods_share_amount = $trade_fee > 0?bcdiv(bcmul($trade_share_discount, $goods_fee), $trade_fee) : 0;
			$left_share_discount = bcsub($left_share_discount, $goods_share_amount);
			
			$share_post = $trade_fee > 0? bcdiv(bcmul($post_fee, $goods_fee), $trade_fee) : 0;
			$left_post = bcsub($left_post, $share_post);
		}
		
		$share_amount = bcsub($goods_fee, $goods_share_amount);
		
		if ($delivery_term != 2)
		{
			$order_paid = bcadd($share_amount, $share_post);
		}
		
		$sku_name = @$o->color." ".@$o->salerprop;
		$order_list[] = array
		(
			'platform_id' => $platform_id,
			'shop_id' =>$shopId,
			'tid' => $tid,
			'oid' => $oid,
			'status' => $trade_status,
			'refund_status' => $order_refund_status,
			'order_type' => 0,
			
			'bind_oid' => '',
			'goods_id' => trim(@$o->goodsId),
			'spec_id' => trim(@(string)$o->smSeq),//规格id
			'goods_no' => trim(@$t->vondorSeq),//商家编码
			'spec_no' => $spec_no,//规格商家编码 barcode
			'goods_name' => iconv_substr(@$o->name,0,255,'UTF-8'),
			'spec_name' => iconv_substr($sku_name,0,100,'UTF-8'),
			'refund_id' => '',
			'num' => $num,
			'price' => $price,
			'adjust_amount' => 0,		//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $order_discount,			//子订单折扣
			'share_discount' => $goods_share_amount, 	//分摊优惠
			'total_amount' => $goods_fee,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,		//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $share_post,			//分摊邮费
			'refund_amount' => 0,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => $order_paid, // jd seems no refund in trade api
			'created' => array('NOW()')
		);
		
	}
	$t->memberPostArea = trim(@$t->memberPostArea);
	if(strpos($t->memberPostArea,'&') !== false)
	{
		$tmpArea = explode('&',$t->memberPostArea);
		$t->memberPostArea = $tmpArea[0];
	}
	
	$receiver_address = @$t->memberAdd;//收货地址
	$receiver_state = fnProvince(@$t->memberProvince);	//省份
	$receiver_city = @$t->memberCity;			//城市
	$receiver_district = $t->memberPostArea;	//区县
	$receiver_mobile = @$t->memberCellphone;		//手机
	$receiver_name = @$t->memberName;		//姓名
	$receiver_phone = @$t->memberTel;  //电话
	$zipCode = @$t->memberPostCode;
	
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
		'platform_id' => $platform_id,
		'shop_id' => $shopId,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => $process_status,
		
		'delivery_term' => $delivery_term,
		'trade_time' => dateValue(@$createtime),
		'pay_time' => dateValue(@$paytime),
		
		'buyer_nick' => iconv_substr($receiver_name,0,100,'UTF-8'),
		'buyer_email' => @$t->memberEmail,
		'buyer_area' => iconv_substr($receiver_area,0,40,'UTF-8'),


		'pay_id' => '',
		'pay_account' => '',
		
		'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
		'receiver_zip' => $zipCode,
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => @$t->delivery_type,
		
		'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
		'logistics_type' => -1,
		
		'invoice_type' => $invoice_type,
	    'invoice_title' => $invoice_title,
	    'invoice_content'=> iconv_substr($invoice_content,0,255,'UTF-8'),
		
		'buyer_message' => iconv_substr(valid_utf8(@$t->memMsg),0,1024,'UTF-8'),
		'remark' => iconv_substr(@$t->merchantRemark,0,1024,'UTF-8'),
		'remark_flag' => 0,
		
		'end_time' => '0000-00-00 00:00:00',
		'wms_type' => 0,
		'warehouse_no' => '',
		'stockout_no' => '',
		'logistics_no' => iconv_substr(@$t->expressNo,0,40,'UTF-8'),
		'is_auto_wms' => 0,
		'is_external' => $is_external,
		
		'goods_amount' => @$trade_fee,
		'post_amount' => $post_fee,
		'receivable' => $receivable,
		'discount' => $total_discount,
		'paid' => (2 == $delivery_term) ? 0 : $paid,
		'received' => (2 == $delivery_term) ? 0 : $paid,
		
		'platform_cost' => 0,
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' => (2 == $delivery_term) ? @$receivable : 0,
		'dap_amount' => (2 == $delivery_term) ? 0 : $receivable,
		'refund_amount' => 0,
		'trade_mask' => 0,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		
		'created' => array('NOW()')
	);
	return true;	
}

//时间日期格式处理
function format_fn_data($data){
	$dt = new DateTime((string)$data);
	return date('Y-m-d H:i:s', $dt->getTimestamp());
}

function fnProvince($province)
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




















