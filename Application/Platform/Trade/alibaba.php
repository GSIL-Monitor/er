<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/alibaba/AlibabaApi.class.php');

$GLOBAL['g_goods_cache'] = array();

function loadAliTrade(&$db, $sid, $appkey, $appsecret, $session, $shopid, &$trade_list, &$order_list, &$t, $trade_info, &$error_msg,&$discount_list)
{
	global $g_goods_cache;
	$trade = $t->orderModel;
	$order_count = 0;
	$goods_count = 0;
	$post_amount = 0;
	$pay_status = 0;
	$refund_amount = 0;
	$cod_amount = 0;
	$trade_status = 10;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
	$process_status = 70;	//处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	//trade退款状态
	$trade_refund_states = 0;	//0无退款 1申请退款 2部分退款 3全部退款
	
	$paid = 0;
	$payTime = $trade_info->pay_time;
	if(empty($payTime)) $payTime = '0000-00-00 00:00:00';

	//平台交易类型tradeTypeStr。6统一交易流程，7分阶段交易，8货到付款交易，9信用凭证支付交易，10帐期支付交易

	/*平台退款状态refundStatus。	
	WAIT_SELLER_AGREE		等待卖家同意退款协议，
	REFUND_SUCCESS			退款成功，
	REFUND_CLOSED			退款关闭，
	WAIT_BUYER_MODIFY		等待买家修改，
	WAIT_BUYER_SEND			等待买家退货，
	WAIT_SELLER_RECEIVE		等待卖家确认收货*/

	/*平台状态status:
	CANCEL                      交易关闭，
    SUCCESS                     交易成功，
    WAIT_BUYER_PAY              等待买家付款，
    WAIT_SELLER_SEND            等待卖家发货，
    WAIT_BUYER_RECEIVE          等待买家确认收货，
    WAIT_SELLER_ACT             分阶段等待卖家操作，
    WAIT_BUYER_CONFIRM_ACTION   分阶段等待买家确认卖家操作，
    WAIT_SELLER_PUSH            分阶段等待卖家推进，
    WAIT_LOGISTICS_TAKE_IN      等待物流公司揽件
    COD，WAIT_BUYER_SIGN        等待买家签收
    COD，SIGN_IN_SUCCESS        买家已签收COD，
    SIGN_IN_FAILED              签收失败COD*/


    //平台支付状态payStatus: 1:等待买家付款，2:已付款，4:交易关闭，6:交易成功，8:交易被系统关闭

	if($trade->tradeTypeStr== 8)
	{
		$delivery_term = 2;
		if(@$trade->refundStatus=='WAIT_SELLER_AGREE' || @$trade->refundStatus=='WAIT_BUYER_MODIFY' || @$trade->refundStatus=='WAIT_BUYER_SEND' || @$trade->refundStatus=='WAIT_SELLER_RECEIVE')
		{
			$trade_refund_states = 1;
		}
		else if(@$trade->refundStatus=='REFUND_SUCCESS')
		{
			$trade_status = 80;
			$trade_refund_states = 3;
		}
	
		if ($trade->status == 'WAIT_BUYER_PAY')
		{
			$process_status = 10;
		}
		else if ($trade->status == 'WAIT_SELLER_SEND')
		{
			$trade_status = 30;
			$process_status = 10;
		}
		else if ($trade->status == 'WAIT_BUYER_RECEIVE')
		{
			$trade_status = 50;
		//	$process_status = 40;
		}
		else if ($trade->status == 'SUCCESS')
		{
			$trade_status = 70;
		}
		else if ($trade->status == 'CANCEL')
		{
			$trade_status = 90;
		}
	}
	else if($trade->tradeTypeStr == 7 || $trade->tradeTypeStr == 2)
	{
		$delivery_term = 3;
		if(@$trade->refundStatus=='WAIT_SELLER_AGREE' || @$trade->refundStatus=='WAIT_BUYER_MODIFY' || @$trade->refundStatus=='WAIT_BUYER_SEND' || @$trade->refundStatus=='WAIT_SELLER_RECEIVE')
		{
			$trade_refund_states = 1;
		}
		else if(@$trade->refundStatus=='REFUND_SUCCESS')
		{
			$trade_status = 80;
			$trade_refund_states = 3;
		}
	
		if ($trade->status == 'WAIT_BUYER_PAY')
		{
			$process_status = 10;
		}
		else if ($trade->status == 'WAIT_SELLER_SEND')
		{
			$trade_status = 30;
			$process_status = 10;
		}
		else if ($trade->status == 'WAIT_BUYER_RECEIVE')
		{
			$trade_status = 50;
		//	$process_status = 40;
		}
		else if ($trade->status == 'SUCCESS')
		{
			$trade_status = 70;
		}
		else if ($trade->status == 'CANCEL')
		{
			$trade_status = 90;
		}
		if($trade->payStatus  == 2)
		{
			$pay_status = 2;
			$paid = ((float)$trade_info->sumPayment/100);  //付款总金额，单位（分）订单需要支付的总金额=产品总金额+运费-折扣金额-抵价券（如果有的话），如果是COD订单，不包括COD服务费
			$refund_amount = ((float)$trade->refundPayment/100);  //退款金额 单位(分)
			$post_amount = bcadd(((float)$trade->stepOrderList[0]->postFee/100),((float)$trade->stepOrderList[1]->postFee/100));
		} 
		else if($trade->payStatus == 8)
		{
			$pay_status = 0;
		}
		else
		{
			$pay_status = 1;
			$paid = ((float)$trade->stepOrderList[0]->actualPayFee/100);
			$refund_amount = ((float)$trade->stepOrderList[0]->actualPayFee/100);
			$post_amount = bcadd(((float)$trade->stepOrderList[0]->postFee/100),((float)$trade->stepOrderList[1]->postFee/100));
		}
	}
	else
	{
		$delivery_term = 1;
		if(@$trade->refundStatus=='WAIT_SELLER_AGREE' || @$trade->refundStatus=='WAIT_BUYER_MODIFY' || @$trade->refundStatus=='WAIT_BUYER_SEND' || @$trade->refundStatus=='WAIT_SELLER_RECEIVE')
		{
			$trade_refund_states = 1;
		}
		else if(@$trade->refundStatus=='REFUND_SUCCESS')
		{
			$trade_status = 80;
			$trade_refund_states = 3;
		}
		
		if ($trade->status == 'WAIT_BUYER_PAY')
		{
			$process_status = 10;
		}
		else if ($trade->status == 'WAIT_SELLER_SEND')
		{
			$trade_status = 30;
			$process_status = 10;
			$paid = ((float)$trade_info->sumPayment/100);

		}
		else if ($trade->status == 'WAIT_BUYER_RECEIVE')
		{
			$trade_status = 50;
		//	$process_status = 40;
			$paid = ((float)$trade_info->sumPayment/100);
		}
		else if ($trade->status == 'SUCCESS')
		{
			$trade_status = 70;
			$paid = ((float)$trade_info->sumPayment/100);
		}
		else if ($trade->status == 'CANCEL')
		{
			$trade_status = 90;
		}	
		$refund_amount = ((float)$trade->refundPayment/100);	
		$post_amount = ((float)$trade_info->post_amount/100);
		if($trade->payStatus  == 2 || $trade->payStatus  == 6)
		{
			$pay_status = 2;
		}
		else
		{
			$pay_status = 0;
		}
		
		/*if($trade->status == 'waitsellersend' || $trade->status == 'waitbuyerreceive' || $trade->status == 'success')
		{
			$pay_status = 2;
		}
		else
		{
			$pay_status = 0;
		}*/
	}
	$tid = (string)$trade->id;
	
	$post_fee = ((float)$trade->carriage/100);
	$left_post = $post_fee;
	$discount_o = -((float)$trade_info->discount/100);//商品总优惠或涨价
	$discount_t = bcsub((bcsub(((float)$trade->sumProductPayment/100), (bcsub(((float)$trade_info->sumPayment/100),$post_fee)))),$discount_o);//订单的优惠 由于接口没有返回该字段 通过计算 货品总额-运费-商品优惠
	$left_discount = $discount_t;
	$trade_fee = bcsub((float)$trade->sumProductPayment/100, $discount_o);//货款总额
	//总货品价格
	//$order_total_ price = 0;
	$orders = $trade->orderEntries;

	$filter = 1;//过滤取消商品计算分摊 1不过滤 2过滤
	$k = count($orders) -1;//最后一个数组的下标

	for ($i=$k; $i >= 0 ; $i--) 
	{
		$entryStatus = $orders[$i]->entryStatusStr;
		if ($entryStatus == 'cancel')
		{
			++$filter;
		}
		else
		{
			break;
		}
	}

	for($k=0; $k<count($orders); $k++)
	{
		$o = & $orders[$k];
		++$order_count;
		$num = (int)$o->quantity;
		$goods_count += (int)$o->quantity;
		$price = ((int)$o->price);
		$total_fee = bcmul($price , $num);
		$discount = -((int)$o->entryDiscount);
		$goods_fee = bcsub($total_fee, $discount);//单个货品的货款  num*price-discount
		$orderRefundStatus = 0;
			
		//取货号
		//这里效率太低，要想办法解决
		if(isset($g_goods_cache[$o->sourceId]))
		{
			$goodsInfo = $g_goods_cache[$o->sourceId];
		}
		else
		{
			$retval = AlibabaApi::getGoodsInfo($appkey, $appsecret, $session, $o->sourceId);
			
			if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
			{
				$error_msg['info'] = $retval->error_msg;
				$error_msg['status'] = 0;
				logx("ERROR $sid ali_get_goods fail {$o->sourceId} ".$error_msg['info'],$sid. "/TradeSlow",'error');
				
				if(strpos($error_msg['info'], 'java.lang.NullPointerException') !== FALSE) return true;
				
				return false;
			}
			
			$goodsInfo = $retval->toReturn[0];
			$g_goods_cache[$o->sourceId] = $goodsInfo;
		}
		
		//货号
		$goodsNO = '';
		foreach($goodsInfo->productFeatureList  as &$prop)
		{
			if($prop->name == '货号')
			{
				$goodsNO = $prop->value;
				break;
			}
		}
		
		//规格名
		$specName = '';
		//规格货号
		$specCode = '';
		if(isset($o->specId))
		{
			foreach($goodsInfo->skuArray  as &$sku)
			{
				if(isset($sku->children))
				{
					foreach($sku->children  as &$ssku)
					{
						if(@$ssku->specId == $o->specId)
						{
							$specCode = $ssku->cargoNumber;
							$specName = $ssku->value." : ".$sku->value;
							break 2;
						}
					}
				}
				else if(@$sku->specId == $o->specId)
				{
					$specCode = $sku->cargoNumber;
					$specName = $sku->value;
					break;
				}
			}
		}
		if(empty($specCode))
		{
			$specCode = $goodsNO;
		}

		if(iconv_strlen($goodsNO,'UTF-8')>40 || iconv_strlen($specCode,'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goodsNO}\t{$specCode}\t".@$o->productName, $sid . "/TradeSlow");
			
			$message = '';
			if(iconv_strlen($goodsNO, 'UTF-8')>40)
				$message = "货品商家编码超过40字符:{$goodsNO}";
			if(iconv_strlen($specCode, 'UTF-8')>40)
				$message = "{$message}规格商家编码超过40字符:{$specCode}";
			
			//发即时消息
			$msg = array(
				'type' => 10,
				'topic' => 'trade_deliver_fail',
				'distinct' => 1,
				'msg' => $message
			);
			SendMerchantNotify($sid, $msg);
			
			$goodsNO = iconv_substr($goodsNO, 0, 40, 'UTF-8');
			$specCode = iconv_substr($specCode, 0, 40, 'UTF-8');
		}
		$orderstatus=0;
        if($o->entryStatusStr == 'cancel')
        {
            $orderstatus = 1;
            $share_post = 0;
            $share_discount = 0;
        }
        if (!$orderstatus)
        {
        	if ($k == count($orders) - $filter)
			{
				$share_post = $left_post;
				$share_discount = $left_discount;
			}else
			{
				$share_post = bcdiv(bcmul($post_fee, ($goods_fee/100)), $trade_fee);
				$left_post = bcsub($left_post, $share_post);
				
				$share_discount = bcdiv(bcmul($discount_t, ($goods_fee/100)), $trade_fee);
				$left_discount = bcsub($left_discount, $share_discount);
			}
			
        }

		$share_amount = bcsub(($goods_fee/100), $share_discount);
		if($pay_status == 2 && $delivery_term == 1 )
		{
			$order_paid = bcadd($share_amount, $share_post);
		}
		else
		{
			$order_paid = 0;
		}

		//子订单状态
		$order_status = $trade_status;		//10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
		if ($o->entryStatus == 'WAIT_BUYER_PAY')
		{
			$order_status = 10;
		}
		else if ($o->entryStatus == 'WAIT_SELLER_SEND')
		{
			$order_status = 30;
		}
		else if ($o->entryStatus == 'WAIT_BUYER_RECEIVE')
		{
			$order_status = 50;
		}
		else if ($o->entryStatus == 'SUCCESS')
		{
			$order_status = 70;
		}
		else if ($o->entryStatus == 'CANCEL')
		{
			$order_status = 90;
		}

		//order退款状态
		$order_refund_status = 0;	//0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
		if(@$o->entryRefundStatus=='WAIT_SELLER_AGREE' || @$o->entryRefundStatus=='WAIT_BUYER_MODIFY')
		{
			$order_refund_status = 2;
		}
		else if(@$o->entryRefundStatus=='REFUND_SUCCESS')
		{
			$order_refund_status = 5;
			$order_status = 80;
		}
		else if(@$o->entryRefundStatus=='REFUND_CLOSED')
		{
			$order_refund_status = 1;
		}
		else if(@$o->entryRefundStatus=='WAIT_BUYER_SEND')
		{
			$order_refund_status = 3;
		}
		else if(@$o->entryRefundStatus=='WAIT_SELLER_RECEIVE')
		{
			$order_refund_status = 4;
		}

		$order_list[] = array
		(
			"rec_id"=>0,
			"platform_id"=>9,
			"tid"=>$tid,				//交易编号
			'shop_id' => $shopid,
			"oid"=>'ALI' . $o->id,			//订单编号
			"status"=> $order_status,		//状态
			"refund_status"=>$order_refund_status,
			"goods_id"=>$o->sourceId,		//平台货品id   
			"spec_id"=>@$o->specId,		//规格id
			"goods_no"=>$goodsNO,		//商家编码 
			"spec_no"=>$specCode,		//规格商家编码  
			"goods_name"=>iconv_substr($o->productName,0,255,'UTF-8'),			//货品名   
			"spec_name"=>iconv_substr($specName,0,100,'UTF-8'),					//规格名
			'num'=>$num, 							//数量
			'price'=>((float)$price/100), 			//商品单价
			'discount'=>(float)$discount/100,		//优惠金额
			'total_amount'=>((float)$goods_fee/100),
			'share_post'=>$share_post,				//分摊邮费
			'share_discount'=>$share_discount,		//分摊优惠
			'paid' => $order_paid,
			'share_amount'=> $share_amount,
			'created' => array('NOW()')
			
		);
		
	}
	
	$tradeTime = $trade_info->trade_time;
	
	//拆分地址
	$receiver_address = $trade_info->receiver_area;
	$addrs = explode(' ', $trade_info->receiver_area, 4);
	if(count($addrs) > 3)
	{
		$province = $addrs[0];
		$city = $addrs[1];
		$town = $addrs[2];
	}
	else
	{
		if(iconv_substr($addrs[0], 2, 1, 'UTF-8') == '省' &&
			iconv_substr($addrs[0], 5, 1, 'UTF-8') == '市' &&
			(iconv_substr($addrs[0], 8, 1, 'UTF-8') == '市' || iconv_substr($addrs[0], 8, 1, 'UTF-8') == '县'))
		{
			$province = iconv_substr($addrs[0], 0, 3, 'UTF-8');
			$city = iconv_substr($addrs[0], 3, 3, 'UTF-8');
			$town = iconv_substr($addrs[0], 6, 3, 'UTF-8');
		}else if(count($addrs) == 3
				&& (iconv_substr($addrs[0], 2, 1, 'UTF-8') == '省' || iconv_substr($addrs[0], -1, 3, 'UTF-8') == '自治区')
				&& iconv_substr($addrs[1], -1, 1, 'UTF-8') == '市'
		)
		{
			$province = $addrs[0];
			$city = $addrs[1];
			$town = '';
		}
		else
		{
			$province = '';
			$city = '';
			$town = '';
		}
	}
	$prefix = $province.' '. $city.' '. $town;
	$len = iconv_strlen($prefix, 'UTF-8');
	if(iconv_substr($receiver_address, 0, $len, 'UTF-8') == $prefix)
		$receiver_address = iconv_substr($receiver_address, $len, 256, 'UTF-8');
	 getAddressID($province, $city, $town,$province_id, $city_id, $district_id);
	//备注
	$Remark = '';
	$RemarkFlag = 0;
	if(isset($trade->sellerOrderMemo))
	{
		$memo = $trade->sellerOrderMemo;
		$Remark = $memo->remark;
		$RemarkFlag = $memo->remarkIcon;
	}
	$trade_discount =(-(float)$trade_info->discount/100);
	$trade_list[] = array
	(
		'platform_id' => 9,
		'shop_id' => $shopid,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_states,
		'process_status' => $process_status,
		
		'delivery_term' =>$delivery_term,
		'trade_time' =>$tradeTime,
		'pay_time' => $payTime,
		
		'buyer_nick' => iconv_substr(trim($trade->buyerLoginId),0,100,'UTF-8'),
		'buyer_email' =>iconv_substr(@$trade->sellerEmail,0,60,'UTF-8'),
		'buyer_area' => '',
		'pay_id' => @$trade->alipayTradeId,
		'pay_account' => @$trade->buyerAlipayId,
		
		'receiver_name' =>  iconv_substr($trade_info->receiver_name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' =>iconv_substr($receiver_address,0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($trade_info->receiver_mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr(@$trade_info->receiver_telno,0,40,'UTF-8'),
		'receiver_zip' => @ $trade_info->receiver_zip,
		'receiver_area' => iconv_substr(@$province ." ".@$city ." ".@$town,0,64,'UTF-8'),
		'to_deliver_time' => '',
		
		'receiver_hash' => md5(@$trade_info->receiver_name.$trade_info->receiver_area . $receiver_address.$trade_info->receiver_mobile.$trade_info->receiver_telno.@$trade_info->receiver_zip),
		'logistics_type' => -1,

		'buyer_message' => iconv_substr(@$trade->buyerFeedback,0,1024,'UTF-8'),
		'remark' =>iconv_substr(@$Remark,0,1024,'UTF-8'),
		'remark_flag' => (int)@$RemarkFlag,
		
		//'end_time' => @$trade->gmtCompleted,
		
		'goods_amount' =>$trade_fee,
		'post_amount' =>$post_amount,
		'receivable' =>  ((float)$trade_info->sumPayment/100),
		'discount' =>$discount_t ,
		'paid' => $paid,
		'received' =>0,
		
		//'platform_cost' => (bcadd(@$trade->commission_fee, @$trade->seller_cod_fee)),
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' =>2 == $delivery_term ? $cod_amount : 0,
		'dap_amount' =>1 == $delivery_term ? ((float)$trade_info->sumPayment/100): 0, 
		
		'refund_amount' =>$refund_amount,
		//'trade_mask' => '',
		//'score' =>'',
		//'real_score' =>'',
		//'got_score' =>'',
		'logistics_no' => iconv_substr(@$trade->logisticsOrderList[0]->logisticsBillNo,0,40,'UTF-8'),
		'created' => array('NOW()')
	);
	if(count($order_list) >= 100)
	{
		return putTradesToDb($db, $trade_list, $order_list, $discount_list , $match_list, $new_trade_count, $chg_trade_count, $error_msg, $sid);
	}
	
	return true;
}
function downalibabaTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$orderids = & $trades->tids;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	//重新授权
	$result = $db->query("select app_key from cfg_shop where shop_id={$shopid}");
	if(!$result)
	{
		releaseDb($db);
		logx("queryy app_key failed!", $sid. "/TradeSlow");
		return TASK_OK;
	}

	while($row = $db->fetch_array($result))
	{
		$res = json_decode($row['app_key'],true);
		$session = $res['session'];
		$trades->session = $session;
	}
	
	for($i=0; $i<count($orderids); $i++)
	{
		$orderid = $orderids[$i];
	
		$retval = AlibabaApi::getonetrade(
							$appkey, 
							$appsecret,
							$trades->session,
							$trades->account_id,
							$orderid
							);
		if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("ERROR $sid downalibabaTradesDetail fail {$trades->tids}, error message: {$error_msg['info']} ", $sid . "/TradeSlow",'error');
			if (401 == intval(@$retval->error_code))
			{
				refreshAliToken($appkey, $appsecret, $trades);
				return TASK_OK; //会丢失
			}
			
			return TASK_SUSPEND;
		}
		if(isset($retval->total)&&$retval->total == 0)
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = '未读取到订单信息';
			logx("downalibabaTradesDetail $shopid ".$error_msg['info'], $sid."/TradeSlow");
			return TASK_OK;
		}
		$tids = array();
		$orders = $retval->toReturn;
		for($j =0; $j < count($orders); $j++)
		{

			if (empty($orders[$j]->toArea))
			{
				$error_msg['info'] = '收件人地址为空';
				logx("downalibabaTradesDetail tid : {$orders[$j]->id} ".$error_msg['info'],$sid . "/TradeSlow");
				continue;
			}
			if (!empty($orders[$j]->gmtPayment))
			{
				$payTime = @$orders[$j]->gmtPayment;
				$strtotime = strtotime($payTime);
				$time = date("Y-m-d H:i:s", $strtotime);
				if ($time != $payTime)
				{
					$error_msg['info'] = '付款时间不对哦';
					logx("downalibabaTradesDetail tid : {$orders[$j]->id} gmtPayment : {$payTime} ".$error_msg['info'],$sid . "/TradeSlow");
					continue;
				}
			}

			if (!empty($orders[$j]->gmtCreate))
			{
				$tradeTime = @$orders[$j]->gmtCreate;
				$strtotime = strtotime($tradeTime);
				$time = date("Y-m-d H:i:s", $strtotime);
				if ($time != $tradeTime)
				{
					$error_msg['info'] = '创建时间不对哦';
					logx("downalibabaTradesDetail tid : {$orders[$j]->id} gmtCreate : {$tradeTime} ".$error_msg['info'],$sid . "/TradeSlow");
					continue;
				}
			}

			$tids[] = array (
						'tid' => $orders[$j]->id,
						'buyerMemberId' => $orders[$j]->buyerMemberId,
						'receiver_zip' => @$orders[$j]->toPost,
						'receiver_name' => @$orders[$j]->toFullName,
						'receiver_area' => @$orders[$j]->toArea,
						'receiver_mobile' => @$orders[$j]->toMobile,
						'receiver_telno' => @$orders[$j]->toPhone,
						'trade_time' => $orders[$j]->gmtCreate,
						'pay_account' => @$orders[$j]->alipayTradeId,
						'pay_time' => @$orders[$j]->gmtPayment,
						'sumPayment' => @$orders[$j]->sumPayment,
						'post_amount' => @$orders[$j]->carriage,
						'discount' => @$orders[$j]->discount
						);
		}
		if(count($tids) > 0)
		{
			$trades->tids = $tids;
			if(! downAliTradesDetail($db, $appkey, $appsecret, $trades, $new_trade_count, $chg_trade_count, $error_msg))
			{
				releaseDb($db);
				return TASK_SUSPEND;
			}
		}
	}
	return TASK_OK;
}
function downAliTradesDetail(&$db, $appkey, $appsecret, $trades, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$sid = $trades->sid;
	
	if(!$db)
	{
		$error_msg['status'] = 0;
		$error_msg['info'] = '连接数据库失败';
		logx("downAliTradesDetail getUserDb failed!!", $sid."/TradeSlow");
		logx("ERROR $sid downAliTradesDetail ".$error_msg['info'], $sid . "/TradeSlow",'error');
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
		
		$trade = (object)$tids[$i];

		$retval = AlibabaApi::getOrderDetailNew($appkey, $appsecret, $session, $trade->tid);

		if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg['info'] = $retval->error_msg;
			$error_msg['status'] =0;
			logx("ERROR $sid downAliTradesDetail fail {$trade->tid}, error message: ".$error_msg['info'], $sid . "/TradeSlow",'error');
			if (401 == intval(@$retval->error_code))
			{
				refreshAliToken($appkey, $appsecret, $trades);
				return TASK_OK; //会丢失
			}
			
			return TASK_SUSPEND;
		}
		if ($retval->orderModel->status == 'WAIT_SELLER_SEND' && empty($trade->pay_time))
		{
			logx(print_r($retval,true),$sid."/TradeSlow");
			logx(print_r($trade,true),$sid."/TradeSlow");
		}
		
		if(!loadAliTrade($db, $sid, $appkey, $appsecret, $session, $shopid, $trade_list, $order_list, $retval, $trade, $error_msg,$discount_list))
		{
			releaseDb($db);
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
	
	releaseDb($db);
	
	return TASK_OK;
}


function aliDownloadTradeList(&$db,$appkey, $appsecret, $shop, $countLimit, $start_time, $end_time, $save_time, $trade_detail_cmd ,&$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$ptime = $end_time;
	if($save_time) 
	{
		$save_time = $end_time;
	}
	$sid = $shop->sid;
	$shopid = $shop->shop_id;

	logx("aliDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid."/TradeSlow");
	
	$total_count = 0;
	while($ptime > $start_time)
	{
		$ptime = ($ptime - $start_time > 3600*24)?($end_time -3600*24 +1):$start_time;

		$retval = AlibabaApi::getOrderList(
							$appkey, 
							$appsecret,
							$shop->session,
							$shop->account_id,
							date('Y-m-d H:i:s', $ptime), 
							date('Y-m-d H:i:s', $end_time),
							1,
							20
							);
		logx("aliDownloadTradeList $shopid start_time:" . 
		date('Y-m-d H:i:s', $ptime) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid."/TradeSlow");
		if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
		{
			releaseDb($db);
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			if (401 == intval($retval->error_code))
			{
				
				refreshAliToken($appkey, $appsecret, $shop);
			}
			logx("ERROR $sid aliDownloadTradeList, error message: ".$error_msg['info'], $sid . "/TradeSlow");
			if ($error_msg['info'] == 'Beyond the app call frequency limit')
			{
				return TASK_OK;
			}
			return TASK_SUSPEND;
		}
		if(isset($retval->total) && $retval->total == 0)
		{
			$end_time = $ptime + 1;
			logx("aliDownloadTradeList $shopid count: 0", $sid."/TradeSlow");
			continue;
		}
		
		//总条数
		$total_results = intval($retval->total);
		$total_count += $total_results;
		logx("aliTrade $shopid count: $total_results", $sid."/TradeSlow");
		
		$trades = $retval->toReturn;
		//如果不足一页，则不需要再抓了
		if($total_results <= count($trades))
		{
			$tids = array();
			for($j =0; $j < count($trades); $j++)
			{

				if (empty($trades[$j]->toArea))
				{
					$error_msg['info'] = '收件人地址为空';
					logx("aliDownloadTradeList tid : {$trades[$j]->id} ".$error_msg['info'],$sid . "/TradeSlow");
					continue;
				}

				if (!empty($trades[$j]->gmtPayment))
				{
					$payTime = @$trades[$j]->gmtPayment;
					$strtotime = strtotime($payTime);
					$time = date("Y-m-d H:i:s", $strtotime);
					if ($time != $payTime)
					{
						$error_msg['info'] = '付款时间不对哦';
						logx("aliDownloadTradeList tid : {$trades[$j]->id} gmtPayment : {$payTime} ".$error_msg['info'],$sid . "/TradeSlow");
						continue;
					}
				}
				if (!empty($trades[$j]->gmtCreate))
				{
					$tradeTime = @$trades[$j]->gmtCreate;
					$strtotime = strtotime($tradeTime);
					$time = date("Y-m-d H:i:s", $strtotime);
					if ($time != $tradeTime)
					{
						$error_msg['info'] = '创建时间不对哦';
						logx("aliDownloadTradeList tid : {$trades[$j]->id} gmtCreate : {$tradeTime} ".$error_msg['info'],$sid . "/TradeSlow");
						continue;
					}
				}
				
				$tids[] = array (
						'tid' => $trades[$j]->id,
						'buyerMemberId' => $trades[$j]->buyerMemberId,
						'receiver_zip' => @$trades[$j]->toPost,
						'receiver_name' => @$trades[$j]->toFullName,
						'receiver_area' => @$trades[$j]->toArea,
						'receiver_mobile' => @$trades[$j]->toMobile,
						'receiver_telno' => @$trades[$j]->toPhone,
						'trade_time' => $trades[$j]->gmtCreate,
						'pay_account' => @$trades[$j]->alipayTradeId,
						'pay_time' => @$trades[$j]->gmtPayment,
						'sumPayment' => @$trades[$j]->sumPayment,
						'post_amount' => @$trades[$j]->carriage,
						'discount' => @$trades[$j]->discount
					);
			}
			if(count($tids) > 0)
			{
				$shop->tids = $tids;
				if(empty($trade_detail_cmd))
				{
					if(! downAliTradesDetail($db, $appkey, $appsecret, $shop, $new_trade_count, $chg_trade_count, $error_msg))
					{
						return TASK_OK;
					}
				}
				else
				{
					pushTask($trade_detail_cmd, $shop);
				}
			}
			if($countLimit && $total_count >= $countLimit)
				return TASK_SUSPEND;
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/20);
			
			for($i=$total_pages; $i>=1; $i--)
			{
				$retval = AlibabaApi::getOrderList(
							$appkey, 
							$appsecret,
							$shop->session,
							$shop->account_id,
							date('Y-m-d H:i:s', $ptime), 
							date('Y-m-d H:i:s', $end_time),
							$i,
							20
							);
				
				if(API_RESULT_OK != alibabaErrorTest($retval,$db,$shopid))
				{
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					logx("ERROR $sid aliDownloadTradeList, error message: ".$error_msg['info'], $sid . "/TradeSlow",'error');
					if ($error_msg['info'] == 'Beyond the app call frequency limit')
					{
						return TASK_OK;
					}
					return TASK_SUSPEND;
				}
				
				$tids = array ();
				$trades = $retval->toReturn;
				
				for($j =0; $j < count($trades); $j++)
				{

					if (empty($trades[$j]->toArea))
					{
						$error_msg['info'] = '收件人地址为空';
						logx("aliDownloadTradeList tid : {$trades[$j]->id} ".$error_msg['info'],$sid . "/TradeSlow");
						continue;
					}

					if (!empty($trades[$j]->gmtPayment))
					{
						$payTime = @$trades[$j]->gmtPayment;
						$strtotime = strtotime($payTime);
						$time = date("Y-m-d H:i:s", $strtotime);
						if ($time != $payTime)
						{
							$error_msg['info'] = '付款时间不对哦';
							logx("aliDownloadTradeList tid : {$trades[$j]->id} gmtPayment : {$payTime} ".$error_msg['info'],$sid . "/TradeSlow");
							continue;
						}
					}
					if (!empty($trades[$j]->gmtCreate))
					{
						$tradeTime = @$trades[$j]->gmtCreate;
						$strtotime = strtotime($tradeTime);
						$time = date("Y-m-d H:i:s", $strtotime);
						if ($time != $tradeTime)
						{
							$error_msg['info'] = '创建时间不对哦';
							logx("aliDownloadTradeList tid : {$trades[$j]->id} gmtCreate : {$tradeTime} ".$error_msg['info'],$sid . "/TradeSlow");
							continue;
						}
					}

					$tids[] = array (
						'tid' => $trades[$j]->id,
						'buyerMemberId' => $trades[$j]->buyerMemberId,
						'receiver_zip' => @$trades[$j]->toPost,
						'receiver_name' => @$trades[$j]->toFullName,
						'receiver_area' => @$trades[$j]->toArea,
						'receiver_mobile' => @$trades[$j]->toMobile,
						'receiver_telno' => @$trades[$j]->toPhone,
						'trade_time' => $trades[$j]->gmtCreate,
						'pay_account' => @$trades[$j]->alipayTradeId,
						'pay_time' => @$trades[$j]->gmtPayment,
						'sumPayment' => @$trades[$j]->sumPayment,
						'post_amount' => @$trades[$j]->carriage,
						'discount' => @$trades[$j]->discount
					);
				}
				
				if(count($tids) > 0)
				{
					$shop->tids = $tids;
					if(empty($trade_detail_cmd))
					{
						if(! downAliTradesDetail($db, $appkey, $appsecret, $shop, $new_trade_count, $chg_trade_count, $error_msg))
						{
							return TASK_OK;
						}
					}
					else
					{
						pushTask($trade_detail_cmd, $shop);
					}
				}	
			}
			if($countLimit && $total_count >= $countLimit)
				return TASK_SUSPEND;
		}
		
		$end_time = $ptime + 1;
	}
	if($save_time)
	{
		
		if(!$db)
		{
			logx("aliDownloadTradeList getUserDb failed!!", $sid."/TradeSlow");
			logx("ERROR $sid aliDownloadTradeList getUserDb", $sid . "/TradeSlow",'error');
			$error_msg['info'] = '连接数据库失败';
			return TASK_OK;
		}

		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 alibaba平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
		releaseDb($db);
	}

	return TASK_OK;
}
?>
