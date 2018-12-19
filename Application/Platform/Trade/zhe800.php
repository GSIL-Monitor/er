<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Common/address.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/zhe800/ZheClient.php');


//异步下载
function zheDownloadTradeList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)
{
	$cbp=function (&$trades) use ($trade_detail_cmd)
	{
		pushTask($trade_detail_cmd, $trades);
		return true;
	};
	
	return zheDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//同步下载
function zheSyncDownloadTradeList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time,&$scan_count, &$total_new, &$total_chg, &$error_msg)
{
	$scan_count=0;
	$total_new=0;
	$total_chg=0;
	$error_msg='';
	$cbp = function(&$trades) use($appkey, $appsecret, &$db, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg)
	{
		downZheTradesDetail($db, $appkey,$appsecret, $trades, $scan_count,$new_trade_count, $chg_trade_count, $error_msg);
		
		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;
		
		return ($scan_count < $countLimit);
	};
	
	return zheDownloadTradeListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}


function zheDownloadTradeListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{
	$ptime = $end_time;
	$page_size=50;
	$page=1;
	if($save_time)
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$authorize_token=$shop->session;
	
	logx("zheDownloadTradeListImpl $shopId start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time),  $sid.'/Trade');
	
	$params = array();
	
	$zhe=new Zhe800Client();
	$zhe->setApp_key($appkey);
	$zhe->setSession($authorize_token);
	$zhe->setMethod('orders.json');
	
	$total_count=0;
	
	while($ptime>$start_time)
	{
		$ptime=($ptime-$start_time>3600*24)?($end_time-3600*24+1):$start_time;
		
		$params['upstart_time']= date('Y-m-d H:i:s', $ptime);
		$params['upend_time']= date('Y-m-d H:i:s', $end_time);
		$params['per_page']=$page_size;
		//$params['sort_rule']=11;
		$params['page']=$page;
		$params['order_state']=0;
		logx("zhe800 $shopId start_time:".date('Y-m-d H:i:s', $ptime) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');
		
		$retval = $zhe->executeByGet($params);
		
		//print_r($retval);
		if(API_RESULT_OK != zheErrorTest($retval,$db,$shopId))
		{
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            logx("zheDownloadTradeListImpl zhe->executeByGet fail error massage:{$error_msg}", $sid.'/Trade','error');


			return TASK_OK;
		}
		
		$trades = $retval->data->orders;
		//总条数
		$total_results = intval($retval->data->total_num);
		//总页数
		$total_pages = ceil(floatval($total_results)/$page_size);
		
		logx("zheDownloadTradeListImpl $shopId total_results_count: {$total_results}",$sid.'/Trade');
		logx("zheDownloadTradeListImpl $shopId total_pages: {$total_pages}",$sid.'/Trade');
		//若不足一页，不需抓取
		if(1==$total_pages)
		{
			//logx("{$shopId} 第1页",$sid);
			$tids=array();
			foreach ($trades as $t) $tids[]=$t->id;
			
			if(count($tids)>0)
			{
				$shop->tids=$tids;
				if(!$cbp($shop)) return TASK_SUSPEND;
			}
		}
		else
		{
			for($i=$total_pages;$i>=1;$i--)
			{
				$params['upstart_time']= date('Y-m-d H:i:s', $ptime);
				$params['upend_time']= date('Y-m-d H:i:s', $end_time);
				$params['per_page']=$page_size;
				//$params['sort_rule']=11;
				$params['page']=$i;
				$params['order_state']=0;
				//logx("shopid: {$shopId} 第{$i}页 ",$sid);
				$retval = $zhe->executeByGet($params);
				
				if(API_RESULT_OK != zheErrorTest($retval,$db,$shopId))
				{

                    $error_msg['info'] = $retval->error_msg;
                    $error_msg['status'] = 0;
                    logx("zheDownloadTradeListImpl zhe->executeByGet fail error massage:{$error_msg}", $sid.'/Trade','error');

					return TASK_OK;
				}
				
				$tids=array();
				$trades = $retval->data->orders;
				
				foreach ($trades as $t)
				{
					$tids[] = $t->id;
				}
				if(count($tids) > 0)
				{
					$shop->tids=$tids;
					if(!$cbp($shop)) return TASK_SUSPEND;
				}
				
				
				
				
			}
		}
		$end_time = $ptime + 1;
	}
	
	if($save_time)
	{
		logx("order_last_synctime_{$shopId}".'上次抓单时间保存 zhe800平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopId}", $save_time);
	}
	return TASK_OK;
}

function downZheTradesDetail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	
	$shopId = $trades->shop_id;
	//$authorize_token=$trades->session;

	$result = $db->query("select app_key from cfg_shop where shop_id={$shopId}");
	if(!$result)
	{
			releaseDb($db);
			logx("queryy app_key failed!", $sid.'/Trade');
			return TASK_OK;
	}

	while($row = $db->fetch_array($result))
	{
			$res = json_decode($row['app_key'],true);
			$session = $res['session'];
			$authorize_token = $session;
	}
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	
	$params = array();
	
	$zhe=new Zhe800Client();
	$zhe->setApp_key($appkey);
	$zhe->setSession($authorize_token);

	$tids=&$trades->tids;
	
	for($i=0;$i<count($tids);$i++)
	{
		$tid=$tids[$i];
		//$params["order_id"]=$tid;
		$zhe->setMethod('orders/'.$tid.'.json');
		$retval= $zhe->executeByGet($params);
		
		if(API_RESULT_OK!= zheErrorTest($retval,$db,$shopId))
		{
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            logx("downZheTradesDetail fail $tid 错误信息: {$error_msg['info']}", $sid.'/Trade','error');
			return TASK_SUSPEND;
		}
		
		if(!isset($retval->data)||!isset($retval->data->products))
		{
            $error_msg['info'] ='读取订单信息失败';
            $error_msg['status'] = 0;
            logx("downZheTradesDetail fail $tid 错误信息:{$error_msg['info']}", $sid.'/Trade','error');
			return TASK_SUSPEND;
		}
		
		if(!loadZheImpl($db,$appkey,$appsecret,$trades,$retval,$trade_list,$order_list,$discount_list))
		{
			continue;
		}
		
		++$scan_count;
		
		if(count($order_list) >= 100)
		{
			if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				return TASK_SUSPEND;
			}
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

function zheProvince($province)
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
//因折800发货会更改地址去除省市区，所做过滤处理
function zheCity($city)
{
	/*$zhixiashi=array('北京','上海','天津','重庆');
	if(in_array($city, $zhixiashi))
	{
		$receiver_city = $city . '市';
		return $receiver_city;
	}*/
	
	if(iconv_substr($city, -1, 1, 'UTF-8') == '市')
	{
		$receiver_city = mb_substr($city, 0, -1, 'UTF-8');
		return $receiver_city;
	}
	
	return $city;
}
/*
function zheDistrict($district)
{
	if(iconv_substr($district, -1, 1, 'UTF-8') == '区' || iconv_substr($district, -1, 1, 'UTF-8') == '县' || iconv_substr($district, -1, 1, 'UTF-8') == '市')
	{
		$receiver_district = mb_substr($district, 0, -1, 'UTF-8');
		return $receiver_district;
	}
	
	return $district;
}*/
/*function str($str)
{
	if($str)
	{
		$str=str_replace('T',' ',$str);
		//$str=str_replace('+08:00','',$str);
		$str=substr($str,0,19);
		return $str;
	}
	else
	{
		return '0000-00-00 00:00:00';
	}
	
}*/

function loadZheImpl(&$db,$appkey, $appsecret,$shop,&$retval,&$trade_list,&$order_list,&$discount_list)
{
	$sid=$shop->sid;
	$shopId=$shop->shop_id;
	
	$t = $retval->data;
	if(!isset($t->id))
	{
		return false;
	}
	
	$delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	$pay_status = 0;	//0未付款1部分付款2已付款
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$order_refund_status = 0;   //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
	$paid = 0; //已付金额, 发货前已付
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;
	$refund_amount=0;//退款金额
	
	$tid=$t->id;
	$status=$t->status;//订单状态 1-待付款 2-等待发货 3-已发货(待确认收货) 5-交易成功（买家确认收货） 7-交易关闭（原因：交易取消/超时未支付/售后完成）
	if($status==1)
	{
		$process_status=10;
	}
	else if($status==2)
	{
		$process_status=10;
		$trade_status=30;
		$pay_status=2;
		$paid=$t->order_price;
	}
	else if($status==3)
	{
		$trade_status=50;
		$pay_status=2;
		$paid=$t->order_price;
	}
	else if($status==5)
	{
		$trade_status=70;
		$pay_status=2;
		$paid=$t->order_price;
	}
	else if($status==7)
	{
		$trade_status=90;
	}
	else 
	{
        logx("invalid_trade_status $tid{$status}", $sid.'/Trade','error');
	}
	

	
	
	$discount=$t->discount_price;
	$post_fee=(float)$t->postage;
	
	$invoice=$t->invoice;
	$invoice_type=0;
	$invoice_title='';
	$invoice_content='';
	
	if(!empty($invoice->receiver))
	{
		$invoice_type=1;
		$invoice_title=trim($invoice->receiver);
		$invoice_content=trim($invoice->content);
	}
	
	$orderId = 1;
	$order_arr = array();
	$trade_share_discount=$discount;
	$orders=&$t->products;
	//邮费，折扣分摊
	$left_post=$post_fee;
	$left_share_discount=$trade_share_discount;
	
	$trade_fee=$t->goods_price;
	$goods_count=0;
	$order_fee=0;
	$order_count = count($orders);
	
	for($i=0;$i<$order_count;$i++)
	{
		$o=&$orders[$i];
		
		$num=$o->count;//商品数量
		$goods_count+=$num;
		$price=$o->price;//商品单价
		$order_fee+=bcmul($price,$num);
		
		$order_refund_status=0;
		//最后一次退款状态	1退款中	2退款成功	3退款关闭	10折800介入	11维权-退款成功	12维权-退款关闭
		if(isset($o->refund_status))
		{
			$trade_refund_status=1;
			
			if($o->refund_status == 2)
			{
				$order_refund_status = 5;
			}
			else if($o->refund_status == 3)
			{
				$order_refund_status = 1;
			}
			else
			{
				$order_refund_status = 2;
			}
			
			//$process_status=70;
			$pay_status=2;
			$refund_amount=$t->order_price;
		}
		
		$oid=$tid;
		if(isset($order_arr[$oid]))
		{
			$oid = $oid . ':' . $orderId;
			++$orderId;
		}
		$order_arr[$oid] = 1;
		
		$goods_fee=bcmul($price,$num);
		
		if($i==$order_count-1)
		{
			$share_post=$left_post;
			$goods_share_amount=$left_share_discount;
		}
		else 
		{
			$share_post=(float)$post_fee*((float)$goods_fee/(float)$trade_fee);
			$left_post=$left_post-$share_post;
			$goods_share_amount=bcmul(bcdiv($goods_fee,$trade_fee),$trade_share_discount);
			$left_share_discount=bcsub($left_share_discount,$goods_share_amount);
		}
		$share_amount = bcsub($goods_fee, $goods_share_amount);
		
		$sku=$o->sku;
		$sku_name='';
		if($sku)
		{
			for($k=0;$k<count($sku);$k++)
			{
				$sku_name.=$sku[$k]->value;
			}
		}
		$spec_name=$sku_name;

		if(!empty($o->seller_no))
		{
			$speccode=@trim($o->seller_no);
		}
		else
		{
			$speccode=@trim($o->num);
		}
		$goods_no = trim(@$o->num);
		$spec_no = $speccode;
		if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t".@$o->name,$sid.'/Trade','error');
			$message = '';
			if(iconv_strlen($goods_no, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goods_no}";
			if(iconv_strlen($spec_no, 'UTF-8')>40)
				$message = "{$message}规格商家编码超过40字符:{$spec_no}";
			
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
		$order_paid = (float)$share_amount+(float)$share_post;
		$order_list[]=array
		(
                'shop_id' => $shopId,
				'platform_id' => 24,
				'tid' => $tid,
				'oid' => $oid,
				'status' => $trade_status,
				'refund_status' => $order_refund_status,
				'order_type' => 0,
				'invoice_type' => $invoice_type,
				'bind_oid' => '',
				'goods_id' => trim(@$o->id),
				'spec_id' => trim(@$o->sku_num),
				'goods_no' => $goods_no,
				'spec_no' => $spec_no,
				'goods_name' => iconv_substr($o->name,0,255,'UTF-8'),
				'spec_name' => iconv_substr((string)$spec_name,0,40,'UTF-8'),
				'refund_id' => '',
				'num' => $num,
				'price' => $price,
				'adjust_amount' => 0,		//手工调整,特别注意:正的表示加价,负的表示减价
				'discount' => 0,			//子订单折扣
				'share_discount' => $goods_share_amount, 	//分摊优惠
				'total_amount' => $goods_fee,		//分摊前扣除优惠货款num*price+adjust-discount
				'share_amount' => $share_amount,		//分摊后货款num*price+adjust-discount-share_discount
				'share_post' => $share_post,			//分摊邮费
				'refund_amount' => 0,
				'is_auto_wms' => 0,
				'wms_type' => 0,
				'warehouse_no' => '',
				'logistics_no' => '',
				'paid' => $order_paid, 
				'created' => array('NOW()')
		);
		
	}
	$receiver=$t->address;
	$province=zheProvince(trim($receiver->province));
	$receiver_state = $province;	//省份
	$receiver_city = zheCity(trim($receiver->city));	//城市
	$receiver_district = trim($receiver->county);	//区县
	$receiver_address=trim($receiver->address);//地址(⽆无省市区)
	
	if(!empty($receiver_district))
	{
		$receiver_area="$receiver_state $receiver_city $receiver_district";
	}
	else
	{
		$receiver_area="$receiver_state $receiver_city";
	}
	
	getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);
	
	$receiver_mobile=trim(@$receiver->phone);//手机
	$receiver_name=trim($receiver->name);//姓名
	$receiver_phone=trim(@$receiver->tel);//电话
	$consigneePostcode=trim(@$receiver->postcode);//邮编
	
	$logistics_type = -1;
	
	$paytime = '0000-00-00 00:00:00';
	if(!empty($t->pay_time))
	{
		$paytime = $t->pay_time;
	}
	
	$trade_list[] = array
	(
			'platform_id' => 24,
			'shop_id' => $shopId,
			'tid' => $tid,
			'trade_status' => $trade_status,
			'pay_status' => $pay_status,
			'pay_method'=>1,//暂时不支持COD
			'refund_status' => $trade_refund_status,
			'process_status' => $process_status,
			'order_count' => $order_count,
			'goods_count' => $goods_count,
			'trade_time' =>$t->created_at,
			'pay_time' => $paytime,
			'buyer_nick' => iconv_substr(valid_utf8(trim($t->nickname)),0,100,'UTF-8'),
			'receiver_name' => iconv_substr($receiver_name,0,40,'UTF-8'),
			'receiver_address' => iconv_substr($receiver_address,0,256,'UTF-8'),
			'receiver_mobile' => iconv_substr($receiver_mobile,0,40,'UTF-8'),
			'receiver_telno' => iconv_substr($receiver_phone,0,40,'UTF-8'),
			'receiver_zip'=>$consigneePostcode,
			'receivable' => (float)$t->order_price,
			'buyer_email' => '',
			'buyer_area' => iconv_substr($receiver_area,0,40,'UTF-8'),
			'pay_id' => '',
			'pay_account' => '',
			'receiver_province' => $province_id,
			'receiver_city' => $city_id,
			'receiver_district' => $district_id,
			'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
			'logistics_type' => $logistics_type,
			'invoice_type' => $invoice_type,
			'invoice_title' => iconv_substr(@$invoice_title,0,255,'UTF-8'),
			'invoice_content'=>iconv_substr(@$invoice_content,0,255,'UTF-8'),
			'buyer_message' => iconv_substr(trim($t->buyer_comment),0,1024,'UTF-8'),
			'remark' => iconv_substr(trim($t->seller_comment),0,1024,'UTF-8'),
			'remark_flag' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'stockout_no' => '',
			'logistics_no' =>(string)@$t->express_no,
			'is_auto_wms' => 0,
			'is_external' => $is_external,
			'goods_amount' => $order_fee,
			'post_amount' => $post_fee,
			'discount' => $discount,
			'paid' => 2 == $delivery_term ? 0 : $paid,
			'received' => $paid,
			'platform_cost' => 0,
			'cod_amount' => 2 == $delivery_term ? (float)$t->order_price : 0,
			'dap_amount' => 2 == $delivery_term ? 0 : (float)$t->order_price,
			'refund_amount' => 0,
			'trade_mask' => 0,
			'score' => 0,
			'real_score' => 0,
			'got_score' => 0,
			'receiver_hash' => md5($receiver_name.$receiver_area.$receiver_address.$receiver_mobile.$receiver_phone.''),
			'delivery_term' => $delivery_term,
			'created' => array('NOW()')
			
	);
	
	return true;	
}
















