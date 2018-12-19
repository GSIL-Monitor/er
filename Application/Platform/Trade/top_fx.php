<?php

require_once(ROOT_DIR . '/Trade/util.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoOrdersGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoDealerRequisitionorderGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/FenxiaoDealerRequisitionorderQueryRequest.php');

//下载单条订单
function top_fenxiao_trades_detail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$sid = $trades->sid;
	
	$shop_id = $trades->shop_id;
	$tids = & $trades->tids;
	logx("top_fenxiao_trades_detail $shop_id", $sid . "/TradeTaobao");

	//taobao
	$session = $trades->session;
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new FenxiaoOrdersGetRequest();

	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	for($i=0; $i<count($tids); $i++)
	{
		$tid = $tids[$i];
		$req->setPurchaseOrderId($tid);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("top_fenxiao_trade_detail fail error:{$error_msg['info']}", $sid . "/TradeTaobao");
			return TASK_SUSPEND;
		}
		
		if(!isset($retval->purchase_orders) ||
			!isset($retval->purchase_orders->purchase_order) ||
			count($retval->purchase_orders->purchase_order) == 0)
		{
			continue;
		}
		
		if(!top_load_fenxiao_agent_order($db, $sid, $shop_id, 
			$retval->purchase_orders->purchase_order[0], 
			$trade_list, 
			$order_list, 
			$discount_list,
			$new_trade_count, 
			$chg_trade_count, 
			$error_msg))
		{
			return TASK_SUSPEND;
		}
		
		++$scan_count;
		
	}
	
	//代销订单到数据库
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}

	//FenxiaoDealerRequisitionorderQueryRequest 接口一次最多处理50个
	$req_jx = new FenxiaoDealerRequisitionorderQueryRequest();
	$total_count=count($tids);
	$total_pages = ceil(floatval($total_count)/50);


	$k=0;
	for($i=$total_pages; $i>=1; $i--)
	{
		$tmp_array2=array_slice($tids,$k,50);
		$dealerOrderIds= implode(",", $tmp_array2);
		$req_jx->setDealerOrderIds($dealerOrderIds);

		$retval = $top->execute($req_jx, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
		{
			$error_msg["status"] = 0;
			$error_msg["info"]   =$retval->error_msg;
            logx("top_fenxiao_trade_detail one_more_page fail error:{$error_msg['info']}", $sid . "/TradeTaobao");
			return TASK_SUSPEND;
		}
		
		if(!isset($retval->dealer_orders) ||
			!isset($retval->dealer_orders->dealer_order) ||
			count($retval->dealer_orders->dealer_order) == 0)
		{
			continue;
		}
		
		$trades = $retval->dealer_orders->dealer_order;
		foreach($trades as $t)
		{
			if(!top_load_fenxiao_dealer_order($db, $sid, $shop_id, $t, $trade_list, $order_list,$discount_list, $new_trade_count, $chg_trade_count, $error_msg))
			{
				logx("loadTopJingxiaoOrder failed 2 $error_msg", $sid . "/TradeTaobao");
				logx("ERROR $sid loadTopJingxiaoOrder2 $error_msg",$sid . "/TradeTaobao", 'error');
				return TASK_OK;
			}
		}
		$k+=50;
		
		++$scan_count;
	}

	//经销订单写入数据库
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	return TASK_OK;
}


function top_fenxiao_download_tradelist(&$db, $appkey, $appsecret, $shop, $limit, $start_time, $end_time, $save_time, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$sid = $shop->sid;
	
	$ptime = $end_time;
	//$jx_ptime = $end_time;

	//$jx_start_time = $start_time;
	//$jx_end_time = $end_time;
	
	if($save_time) 
		$save_time = $end_time;
		
	$shop_id = $shop->shop_id;
	logx("top_fenxiao_download_tradelist $shop_id start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/TradeTaobao");
	
	//taobao
	$session = $shop->session;
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	
	$trade_list = array();
	$order_list = array();
	$discount_list = array();
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	if (0 == $limit)
	{
		//代销订单
		if (empty($shop->order_type))
		{
			if(!top_fenxiao_agent_download_tradelist($db, $sid, $top, $session, $shop_id, $limit, $start_time, $end_time, $scan_count, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
			{
				return TASK_OK;
			}
			
			$key = "order_last_synctime_" . $shop_id;
			$sub_type = "0";
		}
		else if (1 == $shop->order_type)
		{
			//经销订单
			if (!top_fenxioa_dealer_download_tradelist($db, $sid, $top, $session, $shop_id, $limit, $start_time, $end_time, $scan_count, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
			{
				return TASK_OK;
			}
			
			$key = "order_last_synctime_" . $shop_id . "_" . $shop->order_type;
			$sub_type = "1";
		}
		else
		{
			logx("unknown order_type in jst_top_fenxiao_download_trade_list", $sid . "/TradeTaobao");
			logx("ERROR $sid unknown order_type in jst_top_fenxiao_download_trade_list",$sid . "/TradeTaobao", 'error');
			return TASK_OK;
		}
	}
	else
	{
		//代销订单

		if(!top_fenxiao_agent_download_tradelist($db, $sid, $top, $session, $shop_id, $limit, $start_time, $end_time, $scan_count, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
		{
			if ($scan_count >= $limit)
			{
				return TASK_SUSPEND;
			}
			else
			{
				return TASK_OK;
			}
		}

		//经销订单
		if (!top_fenxioa_dealer_download_tradelist($db, $sid, $top, $session, $shop_id, $limit, $start_time, $end_time, $scan_count, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
		{
			if ($scan_count >= $limit)
			{
				return TASK_SUSPEND;
			}
			else
			{
				return TASK_OK;
			}
		}

		//cmd
		$sub_type = "2";
		
	}
	
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			releaseDb($db);
			logx("top_load_fenxiao_agent_order failed 3 $error_msg", $sid . "/TradeTaobao");
			logx("ERROR $sid loadTopFenxiaoOrder3 $error_msg",$sid . "/TradeTaobao", 'error');
			return TASK_OK;
		}
	}
	
	logx("top_fenxiao_download_tradelist shop id:{$shop_id}, sub type:{$sub_type}, new: $new_trade_count, change: $chg_trade_count", $sid . "/TradeTaobao");
	
	if($save_time && !empty($key))
	{
		setSysCfg($db, $key, $save_time);
	}
	
	releaseDb($db);
	return TASK_OK;
}


function top_fenxiao_agent_download_tradelist(&$db, $sid, &$top, $session, $shop_id, &$limit, $start_time, $end_time, &$scan_count, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$page_size = 40;
	$req = new FenxiaoOrdersGetRequest();
	$req->setTimeType('update_time_type');
	$req->setPageSize($page_size);
	
	$ptime = $end_time;
	
	$total_trade_count = 0;
	
	while($ptime > $start_time)
	{
		if($ptime - $start_time > 3600*24)
		{
			$ptime = $end_time - 3600*24 + 1;
		}
		else 
		{
			$ptime = $start_time;
		}
		
		$req->setStartCreated(date('Y-m-d H:i:s', $ptime));
		$req->setEndCreated(date('Y-m-d H:i:s', $end_time));
		
		//取总订单条数
		$req->setPageNo(1);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
		{
			releaseDb($db);
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("top_fenxiao_agent_download_tradelist top execute failed in the first page", $sid . "/TradeTaobao");
			logx("ERROR $sid top_fenxiao_agent_download_tradelist top execute failed in the first page", $sid . "/TradeTaobao", 'error');
			return false;
		}

		if(empty($retval->purchase_orders) || empty($retval->purchase_orders->purchase_order) || 0 == count($retval->purchase_orders->purchase_order))
		{
			$end_time = $ptime + 1;
			logx("top_fenxiao_agent_download_tradelist $shop_id count: 0", $sid . "/TradeTaobao");
			continue;
		}
		
		$trades = $retval->purchase_orders->purchase_order;
		//总条数
		$total_results = intval($retval->total_results);

		logx("top_fenxiao_agent_download_tradelist $shop_id count: $total_results", $sid . "/TradeTaobao");
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($trades))
		{
			$total_trade_count += count($trades);
			foreach($trades as $t)
			{
				$scan_count++;
				if(!top_load_fenxiao_agent_order($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
				{	
					releaseDb($db);
					logx("top_load_fenxiao_agent_order failed when one page $error_msg", $sid . "/TradeTaobao");
					logx("ERROR $sid top_load_fenxiao_agent_order when one page $error_msg", $sid . "/TradeTaobao", 'error');
					return false;
				}
			}
			
			if($limit && $total_trade_count >= $limit)
					return false;
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			
			//$req->setUseHasNext(1);
			for($i=$total_pages; $i>=1; $i--)
			{
				$req->setPageNo($i);
				$retval = $top->execute($req, $session);
				if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
				{
					releaseDb($db);
					$error_msg["status"] = 0;
					$error_msg["info"]   = $retval->error_msg;
					logx("top_fenxiao_agent_download_tradelist top execute failed when more than one page ", $sid . "/TradeTaobao");
					logx("ERROR $sid top_fenxiao_agent_download_tradelist top execute failed when more than one page $error_msg", $sid . "/TradeTaobao", 'error');
					return false;
				}
				
				resetAlarm();
				$total_trade_count += count($trades);
				
				if(isset($retval->purchase_orders) && !empty($retval->purchase_orders->purchase_order))
				{
					$trades = $retval->purchase_orders->purchase_order;
					
					$scan_count++;
					foreach($trades as $t)
					{
						if(!top_load_fenxiao_agent_order($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
						{
							releaseDb($db);
							logx("top_load_fenxiao_agent_order failed when more than one page $error_msg", $sid . "/TradeTaobao");
							logx("ERROR $sid top_load_fenxiao_agent_order failed when more than one page $error_msg",$sid . "/TradeTaobao", 'error');
							return false;
						}
					}
				}
				
				if($limit && $total_trade_count >= $limit)
					return false;
			}
		}
		
		if(count($order_list) >= 100)
		{
			if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				releaseDb($db);
				logx("top_fenxiao_agent_download_tradelist save data failed $error_msg", $sid . "/TradeTaobao");
				logx("ERROR $sid top_fenxiao_agent_download_tradelist save data failed $error_msg", $sid . "/TradeTaobao", 'error');
				return false;
			}
		}
	
	
		$end_time = $ptime + 1;
	}
	
	$limit -= $total_trade_count;
	
	return true;
}


function top_fenxioa_dealer_download_tradelist(&$db, $sid, &$top, $session, $shop_id, &$limit, $start_time, $end_time, &$scan_count, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$page_size = 40;
	$req = new FenxiaoDealerRequisitionorderGetRequest();
	$req->setIdentity(1);
	$req->setPageSize($page_size);
	//now, get all the orders
	$req->setOrderStatus(7);
	
	$ptime = $end_time;
	
	$total_trade_count = 0;
	
	while($ptime > $start_time)
	{
		if($ptime - $start_time > 3600*24)
		{
			$ptime = $end_time - 3600*24 + 1;
		}
		else 
		{
			$ptime = $start_time;
		}
		
		$req->setStartDate(date('Y-m-d H:i:s', $ptime));
		$req->setEndDate(date('Y-m-d H:i:s', $end_time));
		
		//取总订单条数
		$req->setPageNo(1);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
		{
			releaseDb($db);
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("top_fenxioa_dealer_download_tradelist top execute failed in the first page", $sid . "/TradeTaobao");
			logx("ERROR $sid top_fenxioa_dealer_download_tradelist top execute failed in the first page:{$error_msg['info']}", $sid . "/TradeTaobao", 'error');
			return false;
		}

		if(empty($retval->dealer_orders) || empty($retval->dealer_orders->dealer_order) || 0 == count($retval->dealer_orders->dealer_order))
		{
			$end_time = $ptime + 1;
			logx("top_fenxioa_dealer_download_tradelist $shop_id count: 0", $sid . "/TradeTaobao");
			continue;
		}
		
		$trades = $retval->dealer_orders->dealer_order;
		//总条数
		$total_results = intval($retval->total_results);

		logx("top_fenxioa_dealer_download_tradelist $shop_id count: $total_results", $sid . "/TradeTaobao");
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($trades))
		{
			$total_trade_count += count($trades);
			foreach($trades as $t)
			{
				$scan_count++;
				if(!top_load_fenxiao_dealer_order($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
				{	
					releaseDb($db);
					logx("top_load_fenxiao_dealer_order failed when one page $error_msg", $sid . "/TradeTaobao");
					logx("ERROR $sid top_load_fenxiao_dealer_order when one page $error_msg", $sid . "/TradeTaobao", 'error');
					return false;
				}
			}
			
			if($limit && $total_trade_count >= $limit)
					return TASK_SUSPEND;
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/$page_size);
			
			//$req->setUseHasNext(1);
			for($i=$total_pages; $i>=1; $i--)
			{
				$req->setPageNo($i);
				$retval = $top->execute($req, $session);
				if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
				{
					releaseDb($db);
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					logx("top_fenxiao_dealer_download_tradelist top execute failed when more than one page ", $sid . "/TradeTaobao");
					logx("ERROR $sid top_fenxiao_dealer_download_tradelist top execute failed when more than one page: {$error_msg['info']}", $sid . "/TradeTaobao", 'error');
					return false;
				}
				
				resetAlarm();
				$total_trade_count += count($trades);
				
				if(isset($retval->dealer_orders) && !empty($retval->dealer_orders->dealer_order))
				{
					$trades = $retval->dealer_orders->dealer_order;
					
					$scan_count++;
					foreach($trades as $t)
					{
						if(!top_load_fenxiao_dealer_order($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg))
						{
							releaseDb($db);
							logx("top_load_fenxiao_dealer_order failed when more than one page $error_msg", $sid . "/TradeTaobao");
							logx("ERROR $sid top_load_fenxiao_dealer_order failed when more than one page $error_msg", $sid . "/TradeTaobao", 'error');
							return false;
						}
					}
				}
				
				if($limit && $total_trade_count >= $limit)
					return false;
			}
		}
		
		if(count($order_list) >= 100)
		{
			if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				releaseDb($db);
				logx("top_fenxiao_dealer_download_tradelist save data failed $error_msg", $sid . "/TradeTaobao");
				logx("ERROR $sid top_fenxiao_dealer_download_tradelist save data failed $error_msg", $sid . "/TradeTaobao", 'error');
				return false;
			}
		}
	
	
		$end_time = $ptime + 1;
	}
	
	$limit -= $total_trade_count;
	
	return true;
}

function top_load_fenxiao_agent_order(&$db, $sid, $shop_id, &$t, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$cn_num = 0;
	$seller_num = 0;

	$orders = $t->sub_purchase_orders->sub_purchase_order;
	//shipper：{cn:菜鸟仓发货，seller:商家仓发货，onhold:待确认发货仓库）
	for ($i = 0; $i < count($orders); $i++)
	{
		$o = @$orders[$i]->features->feature;
		print_r($o);
		if (empty($o))
		{
			++$seller_num;
			continue;
		}
		for ($s = 0; $s < count($o); $s++)
		{
			$shipper = $o[$s];
			if (@$shipper->attr_key == 'shipper' && $shipper->attr_value == 'cn')
			{
				++$cn_num;
				continue 2;
			}
		}
		++$seller_num;

	}
	
	if($seller_num > 0 && $cn_num > 0)
	{
		logx("Trade unknown shipper " . print_r($t, true), $sid . "/TradeTaobao");
		return false;
	}
	
	if ($seller_num > 0)
	{
		return top_load_fenxiao_agent_order_new($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, false);
	}
	
	if($t->status == 'WAIT_BUYER_CONFIRM_GOODS' || 
		$t->status == 'TRADE_BUYER_SIGNED' || 
		$t->status == 'WAIT_BUYER_CONFIRM_GOODS_ACOUNTED' || 
		$t->status == 'PAY_ACOUNTED_GOODS_CONFIRM' || 
		$t->status == 'TRADE_FINISHED')
	{
		return top_load_fenxiao_agent_order_new($db, $sid, $shop_id, $t, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, true);
	}
	
	logx("菜鸟订单未发货不进入系统: tid:{$t->id} status:{$t->status} " ,$sid . "/TradeTaobao");
	return true;
}


//分销
function top_load_fenxiao_agent_order_new(&$db, $sid, $shop_id, &$t, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg, $cn)
{
	// if not paid, the id is 0
	if (empty($t->id))
	{
		return true;
	}
	//淘宝分销解密
		//获取session
	$fx_session = $db->query("select app_key from cfg_shop where shop_id={$shop_id}");
	if(!$fx_session)
	{
			releaseDb($db);
			logx("query top_load_fenxiao_agent_order_new app_key failed!", $sid . "/TradeTaobao");
			return true;
	}

	while($row = $db->fetch_array($fx_session))
	{
			$res = json_decode($row['app_key'],true);
			$session = $res['session'];
	}
	
	
	if(isset($t->distributor_username)&& !empty($t->distributor_username))
	{
		$t->distributor_username = top_decode($t->distributor_username, 'nick', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->mobile_phone)&& !empty($t->receiver->mobile_phone))
	{
		$t->receiver->mobile_phone = top_decode($t->receiver->mobile_phone, 'phone', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->phone)&& !empty($t->receiver->phone))
	{
		$t->receiver->phone = top_decode($t->receiver->phone, 'simple', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->name)&& !empty($t->receiver->name))
	{
		$t->receiver->name = top_decode($t->receiver->name, 'receiver_name', $session, $sid, $shop_id);
	}
	
	if((isset($t->distributor_username)&&$t->distributor_username == 'ERROR') ||
	(isset($t->receiver->mobile_phone)&&$t->receiver->mobile_phone == 'ERROR') ||
	(isset($t->receiver->phone)&&$t->receiver->phone == 'ERROR')	|| 
	(isset($t->receiver->name)&&$t->receiver->name == 'ERROR'))
	{
		logx("订单号：{$t->id},昵称:{$t->distributor_username},手机号：{$t->receiver->mobile_phone},
		电话：{$t->receiver->phone},收件人:{$t->receiver->name},解密失败",$sid . "/TradeTaobao");
		logx("sid:{$sid},shopid:{$shop_id},tid:{$t->id},2.0淘宝分销解密失败。",$sid . "/TradeTaobao");
		return true;
	}
	
	if(isset($t->receiver->mobile_phone)&&!is_numeric(trim($t->receiver->mobile_phone)))
	{
		logx("订单号:{$t->id},手机号:{$t->receiver->mobile_phone},电话：{$t->receiver->phone},淘宝分销手机号为空或不是纯数字！",$sid . "/TradeTaobao");
		logx("sid:{$sid},shopid:{$shop_id},tid:{$t->id},手机号:{$t->receiver->mobile_phone},电话：{$t->receiver->phone},2.0淘宝分销手机号为空或不是纯数字！",$sid . "/TradeTaobao");
		//return true;
	}
	
	$tid = $t->id;
	
	$invoice_type = 0;
	$invoice_title = '';
	$invoice_content = '';
	
	if (!empty($t->features))
	{
		foreach ($t->features as $key => $value)
		{
			if ('orderNovice' == $key)
			{
				$invoice_type = 1;
				$invoice_title = $value;
			}
			else if ('orderNoviceContent' == $key)
			{
				$invoice_content = $value;
			}
		}
	}
	
	$order_count = 0; 
	$goods_count = 0;
	
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$pay_status = 0;	//0未付款1部分付款2已付款
	$delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	//订单当前状态
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;
	$paid = 0;
	
	if($t->status == 'WAIT_SELLER_SEND_GOODS' || 
		$t->status == 'WAIT_BUYER_CONFIRM_GOODS' || 
		$t->status == 'TRADE_FINISHED' ||
		$t->status == 'WAIT_BUYER_CONFIRM_GOODS_ACOUNTED' || 
		$t->status == 'PAY_ACOUNTED_GOODS_CONFIRM' ||
		$t->status == 'PAY_WAIT_ACOUNT_GOODS_CONFIRM')
	{
		$paid = bcadd($t->total_fee, $t->post_fee);
		$pay_status = 2;
	}
	
	switch($t->status)
	{
		case 'WAIT_SELLER_SEND_GOODS':
		{
			$trade_status = 30;
			$process_status = 10;
			break;
		}
		
		case 'TRADE_CLOSED':
		{
			$trade_status = 80;
			$trade_refund_status = 3;
			break;
		}
		case 'WAIT_BUYER_CONFIRM_GOODS':
		case 'WAIT_BUYER_CONFIRM_GOODS_ACOUNTED':
		case 'PAY_ACOUNTED_GOODS_CONFIRM':
		{
			$trade_status = 50;
			$is_external = 1;
			break;
		}
		case 'TRADE_FINISHED':
		{
			$trade_status = 70;
			$is_external = 1;
			break;
		}
		case 'WAIT_BUYER_PAY':
		{
			$process_status = 10;
			logx("淘宝分销未付款订单不下载.{$tid}",$sid . "/TradeTaobao");
			return true;
			break;
		}
		case 'TRADE_BUYER_SIGNED': 
		{
			$trade_status = 60;
			$is_external = 1;
			break;
		}
		default:
		{
			logx("invalid_trade_status $tid {$t->status} in top_load_fenxiao_agent_order",$sid . "/TradeTaobao");
			logx("ERROR $sid invalid_trade_status $tid {$t->status} in top_load_fenxiao_agent_order",$sid . "/TradeTaobao");
		}
	}
	
	//货品信息
	$orders = & $t->sub_purchase_orders->sub_purchase_order;
	//总折扣
	$total_discount = 0;
	//邮费
	$post_fee = $t->post_fee;
	//未优惠总货款
	$total_fee = $t->total_fee;
	//以下为邮费、已付时行分摊
	$left_post = $t->post_fee;
	$left_paid = $paid;
	
	$order_rows = count($orders);
	$no_refund_order = 0;
	
	//总退款金额
	$trade_refund_amount = 0;
	$trade_is_www = false;
	$trade_warehouse_no = '';
	
	for($k=0; $k<$order_rows; $k++)
	{
		$o = & $orders[$k];
		
		if($o->num <= 0)
		{
			logx("invalid_order in top_load_fenxiao_agent_order" . print_r($o, true),$sid . "/TradeTaobao");
			continue;
		}

		$goods_no = trim(@$o->item_outer_id);
		$spec_no = trim(@$o->sku_outer_id);
		if(iconv_strlen($goods_no, 'UTF-8')>40 || iconv_strlen($spec_no, 'UTF-8')>40)
		{
			logx("GOODS_SPEC_NO_EXCEED\t{$goods_no}\t{$spec_no}\t".@$o->title,$sid . "/TradeTaobao");
			
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

		++$order_count;
		$goods_count += (int)$o->num;
		
		$status = 10;			//平台状态： 平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
		$refund_status = 0;		//0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
		$order_type = 0;		//0正常货品 1虚拟货品 2服务
		
		if($trade_status == 80 || $trade_status == 90)
		{
			$status = $trade_status;
		}
		else
		{
			switch($o->status)
			{
				case 'WAIT_BUYER_PAY':
				case 'WAIT_CONFIRM':
				{
					$status = 10; 
					break;
				}
				case 'WAIT_SELLER_SEND_GOODS':
				case 'WAIT_CONFIRM_WAIT_SEND_GOODS':
				case 'CONFIRM_WAIT_SEND_GOODS':
				{
					$status = 30; 
					break;
				}
				case 'WAIT_CONFIRM_SEND_GOODS':
				case 'WAIT_BUYER_CONFIRM_GOODS_ACOUNTED':
				case 'WAIT_BUYER_CONFIRM_GOODS':
				case 'CONFIRM_SEND_GOODS':
				case 'PAY_ACOUNTED_GOODS_CONFIRM':
				{
					$status = 50;
					break;
				}
				case 'WAIT_CONFIRM_GOODS_CONFIRM':
				case 'PAY_WAIT_ACOUNT_GOODS_CONFIRM':
				{
					$status = 60;
					break;
				}
				case 'TRADE_FINISHED':
				{
					$status = 70;
					break;
				}
				case 'TRADE_CLOSED':
				{
					$status = 80;
					break;
				}
				case 'TRADE_REFUNDED':
				{
					$status = 80;
					$refund_status = 5;
					break;
				}
				case 'TRADE_REFUNDING':
				{
					$status = 30;
					$refund_status = 2;
					break;
				}
				default:
				{
					logx("invalid_trade_status tid:{$tid}, oid:{$o->fenxiao_id}, status: {$o->status} in top_load_fenxiao_agent_order",$sid . "/TradeTaobao");
					logx("ERROR $sid invalid_trade_status tid:{$tid}, oid:{$o->fenxiao_id}, status: {$o->status} in top_load_fenxiao_agent_order",$sid . "/TradeTaobao");
				}
			}
		}

		//退款金额
		$refund_amount = @$o->refund_fee;
		$trade_refund_amount = bcadd($trade_refund_amount, $refund_amount);
		
		if($trade_refund_status == 3)
		{
			$refund_status = 5;
		}
		
		$share_amount = @$o->distributor_payment;
		// no share discount found
		$share_discount = 0;
		
		
		//邮费\已付分摊
		//如果整单退款,sum(子订单邮费)!=主订单邮费
		if($status == 80) //未发货,已退款,不参与分摊
		{
			//退款成功,客户支付还可以不为0
			$share_post = 0;
			$share_paid = $o->distributor_payment - $refund_amount;
			$paid = bcsub($paid, $refund_amount);
			$left_paid = bcsub($left_paid, $refund_amount);
		}
		else if($k == $order_rows-1) //最后一个子订单
		{
			$share_post = $left_post;
			$left_post = '0';
			$share_paid = $left_paid;
			$left_paid = '0';
		}
		else
		{
			//num*price*post_fee/total_fee
			$share_post = bcdiv(bcmul($o->buyer_payment, $post_fee), $t->buyer_payment);
			$left_post = bcsub($left_post, $share_post);
			
			$share_paid = bcadd($share_amount, $share_post);
			if(bccomp($left_paid, $share_paid) >= 0)
			{
				$left_paid = bcsub($left_paid, $share_paid);
			}
			else
			{
				$share_paid = $left_paid;
				$left_paid = '0';
			}
		}
		//tc_discount_fee and tc_adjust_fee is used for buyer, not agent
		if($status != 90) //关闭的订单，支付、优惠都不计入
			$total_discount = bcadd($total_discount,$o->discount_fee);
		
		$order_is_www = false;
		$order_warehouse_no = '';
		
		if (!empty($t->features))
		{
			foreach ($t->features as $key => $value)
			{
				if ('www' == $key)
				{
					$order_is_www = true;
					$trade_is_www = true;
				}
				else if ('wwwStoreCode' == $key)
				{
					$order_warehouse_no = $value;
					$trade_warehouse_no = $value;
				}
			}
		}
		
		$order_list[] = array
		(
			'platform_id' => 2,
			'shop_id' => $shop_id,
			'tid' => $tid,
			'oid' => $o->fenxiao_id,
			'status' => $status,
			'refund_status' => $refund_status,
			'order_type' => $order_type,
			'invoice_type' => $invoice_type,
			'bind_oid' => '',
			'goods_id' => trim($o->item_id),
			'spec_id' => trim(@$o->sku_id),
			'goods_no' => $goods_no,
			'spec_no' => $spec_no,
			'goods_name' => iconv_substr(@$o->title,0,255,'UTF-8'),
			'spec_name' => iconv_substr(@$o->sku_properties,0,100,'UTF-8'),
			'refund_id' => '',
			'num' => $o->num,
			'price' => $o->price,
			'adjust_amount' => ((float)$o->tc_adjust_fee)/100,		//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $o->discount_fee,			//子订单折扣
			'share_discount' => $share_discount, 	//分摊优惠
			'total_amount' => $o->distributor_payment,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,		//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $share_post,			//分摊邮费
			'refund_amount' => $refund_amount,
			'is_auto_wms' => $order_is_www ? 1 : 0,
			'wms_type' => $order_is_www ? 2 : 0,
			'warehouse_no' => $order_warehouse_no,
			'logistics_no' => '',
			'paid' => $share_paid,
			'created' => array('NOW()')
		);
		
		// the tc_adjust_fee may be used for buyer not agent
		// auction_price = bill_fee + tc_discount_fee/100 - tc_adjust_fee/100
		/*
		if(bccomp($o->tc_adjust_fee, 0))
		{
			$discount_list[] = array
			(
				'platform_id' => 2,
				'tid' => $tid,
				'oid' => $o->fenxiao_id,
				'sn' => '', 
				'type' => 'order_adjust',
				'name' => '客服调价',
				'is_bonus' => 0,
				'detail' => '客服调价',
				'amount' => bcsub(0,$o->tc_adjust_fee)
			);
		}
		*/
	}
	
	$mobile = trim(@$t->receiver->mobile_phone);
	$telno = trim(@$t->receiver->phone);
	if($mobile == $telno) $telno = '';
	
	//淘宝,县级市
	$city = trim(@$t->receiver->city);
	$district = trim(@$t->receiver->district);
	if(empty($city))
	{
		$city = $district;
		$district = '';
	}
	
	$dap_amount = 0;
	$cod_amount = 0;
	if($delivery_term == 2)
	{
		$cod_amount = bcsub($t->distributor_payment,$trade_refund_amount);
	}
	else
	{
		$dap_amount = bcsub($t->distributor_payment,$trade_refund_amount);
	}
	
	/*总折扣*/
	$discount = bcsub(bcadd($t->total_fee, $t->post_fee), $t->distributor_payment);
	
	//检查折扣平衡
	if($pay_status && bccomp($discount, $total_discount))
	{
		logx("fx_agent_discount_error: $tid $discount $total_discount",$sid . "/TradeTaobao");
		logx(print_r($t, true),$sid . "/TradeTaobao");
	}
	
	$province = trim(@$t->receiver->state);
	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);
	if(!empty($district))
		$receiver_area = "$province $city $district";
	else
		$receiver_area = "$province $city";
	
	//物流类别
	$logistics_type = -1;	//未知物流
	if(@$t->shipping == 'ORDINARY')
	{
		$logistics_type = 2;//平邮
	}
	else if(@$t->shipping == 'EMS')
	{
		$logistics_type = 3;//ems
	}

	//订单内部来源
	$trade_mask = 0;
	
	$trade_list[] = array
	(
		'platform_id' => 2,
		'shop_id' => $shop_id,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' =>  $cn ? 10: $process_status,
		'fenxiao_type' => 1,
		
		'delivery_term' => $delivery_term,
		'trade_time' => dateValue($t->created),
		'pay_time' => dateValue(@$t->pay_time),
		
		'buyer_nick' => trim(@$t->distributor_username).'-'.$mobile,
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => @$t->alipay_no,
		'pay_account' => '',
		
		'receiver_name' => iconv_substr(@$t->receiver->name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => trim(@$t->receiver->address),
		'receiver_mobile' => $mobile,
		'receiver_telno' => $telno,
		'receiver_zip' => @$t->receiver->zip,
		'receiver_area' => $receiver_area,
		'to_deliver_time' => '',
		
		'receiver_hash' => md5(@$t->receiver->name.$receiver_area.@$t->receiver->address.$mobile.$telno.@$t->receiver->zip),
		'logistics_type' => $logistics_type,
		
		'invoice_type' => $invoice_type,
		'invoice_title' => $invoice_title,
		
		'buyer_message' => iconv_substr(@$t->memo,0,1024,'UTF-8'),
		'remark' => @$t->supplier_memo,
		'remark_flag' => (int)@$t->supplier_flag,
		
		'end_time' => dateValue(@$t->end_time),
		'wms_type' => $cn ? 2 : 0,
		'warehouse_no' => $trade_warehouse_no,
		'stockout_no' => '',
		'is_auto_wms' => $cn ? 1 : 0,
		'is_external' => $is_external,
		
		'goods_amount' => bcsub(bcadd($t->total_fee, $t->post_fee),$trade_refund_amount),
		'post_amount' => $t->post_fee,
		'receivable' => bcsub($t->distributor_payment,$trade_refund_amount),
		'discount' => $discount,
		'paid' => $paid,
		'received' => 0,
		
		'platform_cost' => 0,
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' => $cod_amount,
		'dap_amount' => $dap_amount,
		'refund_amount' => $trade_refund_amount,
		'trade_mask' => $trade_mask,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		'logistics_no' => @$t->logistics_id,
		'created' => array('NOW()')
	);
	
	//写数据库
	if(count($order_list) >= 100)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return false;
		}
	}
	
	return true;
}

//经销
function top_load_fenxiao_dealer_order(&$db, $sid, $shop_id, &$t, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	//淘宝经销解密
		//获取session
	$jx_session = $db->query("select app_key from cfg_shop where shop_id={$shop_id}");
	if(!$jx_session)
	{
			releaseDb($db);
			logx("query top_load_fenxiao_dealer_order app_key failed!",$sid . "/TradeTaobao");
			return true;
	}

	while($row = $db->fetch_array($jx_session))
	{
			$jx_res = json_decode($row['app_key'],true);
			$session = $jx_res['session'];
	}
	
	
	if(isset($t->applier_nick)&& !empty($t->applier_nick))
	{
		$t->applier_nick = top_decode($t->applier_nick, 'nick', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->mobile_phone)&& !empty($t->receiver->mobile_phone))
	{
		$t->receiver->mobile_phone = top_decode($t->receiver->mobile_phone, 'phone', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->phone)&& !empty($t->receiver->phone))
	{
		$t->receiver->phone = top_decode($t->receiver->phone, 'simple', $session, $sid, $shop_id);
	}
	
	if(isset($t->receiver->name)&& !empty($t->receiver->name))
	{
		$t->receiver->name = top_decode($t->receiver->name, 'receiver_name', $session, $sid, $shop_id);
	}
	
	if((isset($t->applier_nick)&&$t->applier_nick == 'ERROR') ||
	(isset($t->receiver->mobile_phone)&&$t->receiver->mobile_phone == 'ERROR') ||
	(isset($t->receiver->phone)&&$t->receiver->phone == 'ERROR')	|| 
	(isset($t->receiver->name)&&$t->receiver->name == 'ERROR'))
	{
		logx("订单号：{$t->dealer_order_id},昵称:{$t->applier_nick},手机号：{$t->receiver->mobile_phone},
		电话：{$t->receiver->phone},收件人:{$t->receiver->name},解密失败",$sid . "/TradeTaobao");
		logx("sid:{$sid},shopid:{$shop_id},tid:{$t->dealer_order_id},2.0淘宝经销解密失败。",$sid . "/TradeTaobao");
		return true;
	}
	
	if(isset($t->receiver->mobile_phone)&&!is_numeric(trim($t->receiver->mobile_phone)))
	{
		logx("订单号:{$t->dealer_order_id},手机号:{$t->receiver->mobile_phone},电话:{$t->receiver->phone},淘宝经销手机号为空或不是纯数字！",$sid . "/TradeTaobao");
		logx("sid:{$sid},shopid:{$shop_id},tid:{$t->dealer_order_id},手机号:{$t->receiver->mobile_phone},电话:{$t->receiver->phone},2.0淘宝经销手机号为空或不是纯数字！",$sid . "/TradeTaobao");
		//return true;
	}
	$tid = $t->dealer_order_id;
	
	$invoice_type = 0;
	$invoice_title = '';
	$invoice_content = '';
	
	$order_count = 0; 
	$goods_count = 0;
	
	$trade_refund_status = 0;	//退款状态 0无退款 1申请退款 2部分退款 3全部退款
	$pay_status = 0;	//0未付款1部分付款2已付款
	$delivery_term = 1; //发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	//订单当前状态
	$trade_status = 10; //订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
	$process_status = 70; //处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
	$is_external = 0;
	$paid = 0;
	
	if($t->order_status == 'WAIT_FOR_SUPPLIER_DELIVER' || 
		$t->order_status == 'WAIT_FOR_APPLIER_STORAGE' || 
		$t->order_status == 'TRADE_FINISHED')
	{
		$paid = $t->total_price; // total_price includes logistics_fee
		$pay_status = 2;
	}
	
	switch($t->order_status)
	{
		case 'WAIT_FOR_SUPPLIER_AUDIT1':
		case 'SUPPLIER_REFUSE':
		case 'WAIT_FOR_APPLIER_AUDIT':
		case 'WAIT_FOR_SUPPLIER_AUDIT2':
		case 'BOTH_AGREE_WAIT_PAY':
		{
			$process_status = 10;
			logx("经销商未付款订单不下载.{$tid}",$sid . "/TradeTaobao");
			return true;
			break;
		}
		case 'WAIT_FOR_SUPPLIER_DELIVER':
		{
			$trade_status = 30;
			$process_status = 10;
			break;
		}
		case 'WAIT_FOR_APPLIER_STORAGE':
		{
			$trade_status = 50;
			$is_external = 1;
			break;
		}
		case 'TRADE_FINISHED':
		{
			$trade_status = 70;
			$is_external = 1;
			break;
		}
		//dealer_order_id: 3858452912356, order_status: "TRADE_CLOSED", close_reason: "系统超时关闭"
		case 'TRADE_CLOSED':
		{
			$trade_status = 90;
			break;
		}
		default:
		{
			logx("invalid_trade_status $tid {$t->status} in top_load_fenxiao_dealer_order",$sid . "/TradeTaobao");
			logx("ERROR $sid invalid_trade_status $tid {$t->status} in top_load_fenxiao_dealer_order",$sid . "/TradeTaobao");
		}
	}
	
	//货品信息
	$orders = & $t->dealer_order_details->dealer_order_detail;
	//总折扣
	$total_discount = 0;
	//邮费
	$post_fee = $t->logistics_fee;
	//未优惠总货款
	$total_fee = $t->total_price;
	//以下为邮费、已付时行分摊
	$left_post = $t->logistics_fee;
	$left_paid = $paid;
	
	$order_rows = count($orders);
	$no_refund_order = 0;
	
	//总退款金额
	$trade_refund_amount = 0;
	
	for($k=0; $k<$order_rows; $k++)
	{
		$o = & $orders[$k];
		
		if($o->quantity <= 0 || !empty($o->is_deleted))
		{
			logx("abnormal in top_load_fenxiao_dealer_order, tid:{$tid}, data:" . print_r($o, true),$sid . "/TradeTaobao");
			continue;
		}
		
		++$order_count;
		$goods_count += (int)$o->quantity;
		
		$status = $trade_status;			//平台状态： 平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
		$refund_status = 0;                 //0无退款 1取消退款, 2已申请退款,3等待退货,4等待收货,5退款成功
		
		$order_type = 0;		//0正常货品 1虚拟货品 2服务

		//退款金额
		$refund_amount = 0;
		
		$share_amount = @$o->price_count;
		// no share discount found
		$share_discount = 0;
		//$o->original_price may be(dealer_order_id:5272478441982) smaller than $o->final_price
		//$discount = bcmul(bcsub($o->original_price,$o->final_price), $o->quantity);
		$discount = 0;
				
		//邮费\已付分摊
		//如果整单退款,sum(子订单邮费)!=主订单邮费
		if($k == $order_rows-1) //最后一个子订单
		{
			$share_post = $left_post;
			$left_post = '0';
			$share_paid = $left_paid;
			$left_paid = '0';
		}
		else
		{
			//num*price*post_fee/total_fee
			$share_post = bcdiv(bcmul($o->price_count, $post_fee), bcsub($total_fee, $post_fee));
			$left_post = bcsub($left_post, $share_post);
			
			$share_paid = bcadd($share_amount, $share_post);
			if(bccomp($left_paid, $share_paid) >= 0)
			{
				$left_paid = bcsub($left_paid, $share_paid);
			}
			else
			{
				$share_paid = $left_paid;
				$left_paid = '0';
			}
		}

		if($status != 90) //关闭的订单，支付、优惠都不计入
			$total_discount = bcadd($total_discount, $discount);
		
		//////////////////////////here
		//保存至 g_api_tradegoods
		$order_list[] = array
		(
			'platform_id' => 2,
			"shop_id"     => $shop_id,
			'tid' => $tid,
			'oid' => $o->dealer_detail_id,
			'status' => $status,
			'refund_status' => $refund_status,
			'order_type' => $order_type,
			'invoice_type' => $invoice_type,
			'bind_oid' => '',
			'goods_id' => trim($o->product_id),
			'spec_id' => trim(@$o->sku_id),
			'goods_no' => '',
			'spec_no' => trim(@$o->sku_number),
			'goods_name' => @$o->product_title,
			'spec_name' => '',
			'refund_id' => '',
			'num' => $o->quantity,
			'price' => $o->final_price,
			'adjust_amount' => 0,		//手工调整,特别注意:正的表示加价,负的表示减价
			'discount' => $discount,			//子订单折扣
			'share_discount' => $share_discount, 	//分摊优惠
			'total_amount' => $o->price_count,		//分摊前扣除优惠货款num*price+adjust-discount
			'share_amount' => $share_amount,		//分摊后货款num*price+adjust-discount-share_discount
			'share_post' => $share_post,			//分摊邮费
			'refund_amount' => $refund_amount,
			'is_auto_wms' => 0,
			'wms_type' => 0,
			'warehouse_no' => '',
			'logistics_no' => '',
			'paid' => $share_paid,
			'created' => array('NOW()')
		);
	}
	
	$mobile = trim(@$t->receiver->mobile_phone);
	$telno = trim(@$t->receiver->phone);
	if($mobile == $telno) $telno = '';
	
	//淘宝,县级市
	$city = trim(@$t->receiver->city);
	$district = trim(@$t->receiver->district);
	if(empty($city))
	{
		$city = $district;
		$district = '';
	}
	
	$dap_amount = 0;
	$cod_amount = 0;
	if($delivery_term == 2)
	{
		$cod_amount = bcsub($paid,$trade_refund_amount);
	}
	else
	{
		$dap_amount = bcsub($paid,$trade_refund_amount);
	}
	
	$province = trim(@$t->receiver->state);
	getAddressID($province, $city, $district, $province_id, $city_id, $district_id);
	if(!empty($district))
		$receiver_area = "$province $city $district";
	else
		$receiver_area = "$province $city";
	
	//物流类别
	$logistics_type = -1;	//未知物流

	//订单内部来源
	$trade_mask = 0;
	
	$trade_list[] = array
	(
		'platform_id' => 2,
		'shop_id' => $shop_id,
		'tid' => $tid,
		'trade_status' => $trade_status,
		'pay_status' => $pay_status,
		'refund_status' => $trade_refund_status,
		'process_status' => $process_status,
		'fenxiao_type' => 2,
		
		'delivery_term' => $delivery_term,
		'trade_time' => dateValue($t->applied_time),
		'pay_time' => dateValue(@$t->pay_time),
		
		'buyer_nick' => @iconv_substr(trim($t->applier_nick).'-'.$mobile,0,100,'UTF-8'),
		'buyer_email' => '',
		'buyer_area' => '',
		'pay_id' => @$t->alipay_no,
		'pay_account' => '',
		
		'receiver_name' => iconv_substr(@$t->receiver->name,0,40,'UTF-8'),
		'receiver_province' => $province_id,
		'receiver_city' => $city_id,
		'receiver_district' => $district_id,
		'receiver_address' => iconv_substr(trim(@$t->receiver->address),0,256,'UTF-8'),
		'receiver_mobile' => iconv_substr($mobile,0,40,'UTF-8'),
		'receiver_telno' => iconv_substr($telno,0,40,'UTF-8'),
		'receiver_zip' => @$t->receiver->zip,
		'receiver_area' => iconv_substr($receiver_area,0,64,'UTF-8'),
		'to_deliver_time' => '',
		
		'receiver_hash' => md5(@$t->receiver->name.$receiver_area.@$t->receiver->address.$mobile.$telno.@$t->receiver->zip),
		'logistics_type' => $logistics_type,
		
		'invoice_type' => $invoice_type,
		'invoice_title' => iconv_substr($invoice_title,0,255,'UTF-8'),
		
		'buyer_message' => iconv_substr(@$t->refuse_reason_applier,0,1024,'UTF-8'),
		'remark' => iconv_substr(@$t->supplier_memo,0,1024,'UTF-8'),
		'remark_flag' => (int)@$t->supplier_memo_flag,
		
		'end_time' => dateValue(@$t->modified_time),
		'wms_type' => 0,
		'warehouse_no' => '',
		'stockout_no' => '',
		'is_auto_wms' => 0,
		'is_external' => $is_external,
		
		'goods_amount' => bcsub(bcsub($t->total_price, $t->logistics_fee), $trade_refund_amount),
		'post_amount' => $t->logistics_fee,
		'receivable' => bcsub($t->total_price, $trade_refund_amount),
		'discount' => $total_discount,
		'paid' => $paid,
		'received' => 0,
		
		'platform_cost' => 0,
		
		'order_count' => $order_count,
		'goods_count' => $goods_count,
		
		'cod_amount' => $cod_amount,
		'dap_amount' => $dap_amount,
		'refund_amount' => $trade_refund_amount,
		'trade_mask' => $trade_mask,
		'score' => 0,
		'real_score' => 0,
		'got_score' => 0,
		'logistics_no' => '',
		'created' => array('NOW()')
	);
	
	//写数据库
	if(count($order_list) >= 100)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return false;
		}
	}
	
	return true;
}


?>
