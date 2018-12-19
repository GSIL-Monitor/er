<?php

require_once(ROOT_DIR . '/Refund/util.php');
require_once(ROOT_DIR . '/Common/address.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/TmallEaiOrderRefundMgetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TmallEaiOrderRefundGoodReturnMgetRequest.php');

function topDownloadTmRefundList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg)
{
	return topDownloadTmRefundListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg);
}


function topDownloadTmRefundListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg)
{
	$ptime = $end_time;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	logx("topRefundTianmao $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid);
	
	//taobao
	$session = $shop->session;
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new TmallEaiOrderRefundMgetRequest();
	
	$req->setPageSize(40);
	
	$total_count = 0;
	$loop_count = 0;
	$new_refund_count = 0;
	$chg_refund_count = 0;
	
	$refund_list = array();
	$goods_list = array();
	
	while($ptime > $start_time)
	{
		$loop_count++;
		if($loop_count > 1) resetAlarm();
		
		if($ptime - $start_time > 3600*24) $ptime = $end_time - 3600*24 + 1;
		else $ptime = $start_time;
		
		$req->setStartTime(date('Y-m-d H:i:s', $ptime));
		$req->setEndTime(date('Y-m-d H:i:s', $end_time));
		
		//取总订单条数
		$req->setPageNo(1);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shopId))
		{
			$error_msg = $retval->error_msg;
			logx("topDownloadTmRefundList top->execute fail", $sid);
			logx("ERROR $sid topDownloadTmRefundList", 'error');
			return TASK_OK;
		}
		
		if(!isset($retval->refund_bill_list) || count($retval->refund_bill_list) == 0)
		{
			$end_time = $ptime + 1;
			logx("TmRefund $shopId count: 0", $sid);
			continue;
		}
		
		$refunds = $retval->refund_bill_list->refund_bill;
		//总条数
		$total_results = intval($retval->total_results);
		$total_count += $total_results;
		//echo "total_results: $total_results\n";
		logx("TmRefund $shopId count: $total_results", $sid);
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($refunds))
		{
			$tids = array();
			for($j =0; $j < count($refunds); $j++)
			{
				if(!loadTmRefundImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
				{
					continue;
				}
			}
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/40);
			
			//$req->setUseHasNext(1);
			for($i=$total_pages; $i>=1; $i--)
			{
				$req->setPageNo($i);
				$retval = $top->execute($req, $session);
				if(API_RESULT_OK != topErrorTest($retval, $db, $shopId))
				{
					$error_msg = $retval->error_msg;
					logx("topDownloadTmRefundList2 top->execute fail2", $sid);
					logx("ERROR $sid topDownloadTmRefundList2", 'error');
					return TASK_OK;
				}
				
				$refunds = $retval->refund_bill_list->refund_bill;
				for($j =0; $j < count($refunds); $j++)
				{
					if(!loadTmRefundImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
					{
						continue;
					}
					
					if(count($goods_list) >= 100)
					{
						if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
						{
							return TASK_SUSPEND;
						}
					}
				}
			}
		}
		
		$end_time = $ptime + 1;
	}
	
	if(count($goods_list) > 0)
	{
		if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	if($save_time)
	{
		setSysCfg($db, "refund_last_synctime_{$shopId}", $save_time);
	}
	
	return TASK_OK;
}


function loadTmRefundImpl(&$db, $shop, &$refund, &$refund_list, &$goods_list)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$platformId = $shop->platform_id;
	
	$refundId = $refund->refund_id;
	
	$status = 0;
	switch($refund->status)
	{
		case 'wait_seller_agree': //买家已经申请退款，等待卖家同意
			$status=2;
			break;
		case 'goods_returning': //卖家已经同意退款，等待买家退货
			$status=3;
			break;
		case 'seller_refuse': //卖家拒绝退款 
			$status=1;
			break;
		case 'closed': //退款关闭
			$status=1;
			break;
		case 'success': //退款成功
			$status=5;
			break;
	}
	
	$aftersale = 0;
	$type = 1;
	switch($refund->trade_status)
	{
		case 'wait_send_good':
			$type = 1;
			break;
		case 'finished':
		case 'wait_confirm_good':
			$aftersale = 1;
			if($refund->refund_type=='refund')
				$type = 4;
			else
				$type = 2;
			break;
	}
	
	$amount = bcdiv($refund->refund_fee,100);
	
	$refundRow = array(
		'platform_id' => $platformId,
		'shop_id' => $shopId,
		'refund_no' => $refundId,
		'tid' => $refund->tid,
		'type' => $type,
		'status' => $status,
		'process_status' => 0,
		'guarantee_mode' => 1,
		'refund_amount' => $amount,
		'actual_refund_amount' => $amount,
		'pay_no' => $refund->alipay_no,
		'buyer_nick' => $refund->buyer_nick,
		'refund_time' => $refund->created,
		'current_phase_timeout' => dateValue(@$refund->current_phase_timeout),
		'is_aftersale' => $aftersale,
		'reason' => $refund->reason,
		'remark' => @$refund->desc,
		'refund_version' => $refund->refund_version,
		'created' => array('NOW()')
	);
	
	$refund_list[] = $refundRow;
	
	$itemList = $refund->item_list->refund_item;
	for($i=0; $i<count($itemList); ++$i)
	{
		$item = $itemList[$i];
		$num = intval(@$item->num);
		if($num <= 0) $num = -1;
		
		$goodsRow = array(
			'platform_id' => $platformId,
			'shop_id' => $shopId,
			'refund_no' => $refundId,
			'oid' => $refund->oid,
			'status' => $status,
			'num' => $num,
			'created' => array('NOW()')
		);
		
		$goods_list[] = $goodsRow;
	}
	
	return true;
}


function topDownloadTmReturnListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg)
{
	$ptime = $end_time;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	logx("topDownloadTmReturnList $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid);
	
	//taobao
	$session = $shop->session;
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new TmallEaiOrderRefundGoodReturnMgetRequest();
	
	$req->setPageSize(40);
	
	$total_count = 0;
	$loop_count = 0;
	$new_refund_count = 0;
	$chg_refund_count = 0;
	
	$refund_list = array();
	$goods_list = array();
	
	while($ptime > $start_time)
	{
		$loop_count++;
		if($loop_count > 1) resetAlarm();
		
		if($ptime - $start_time > 3600*24) $ptime = $end_time - 3600*24 + 1;
		else $ptime = $start_time;
		
		$req->setStartTime(date('Y-m-d H:i:s', $ptime));
		$req->setEndTime(date('Y-m-d H:i:s', $end_time));
		
		//取总订单条数
		$req->setPageNo(1);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shopId))
		{
			$error_msg = $retval->error_msg;
			logx("topDownloadTmReturnList top->execute fail", $sid);
			logx("ERROR $sid topDownloadTmReturnList", 'error');
			return TASK_OK;
		}
		
		if(!isset($retval->return_bill_list) || count($retval->return_bill_list) == 0)
		{
			$end_time = $ptime + 1;
			logx("TmRefund $shopId count: 0", $sid);
			continue;
		}
		
		$refunds = $retval->return_bill_list->return_bill;
		//总条数
		$total_results = intval($retval->total_results);
		$total_count += $total_results;
		//echo "total_results: $total_results\n";
		logx("TmRefund $shopId count: $total_results", $sid);
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($refunds))
		{
			$tids = array();
			for($j =0; $j < count($refunds); $j++)
			{
				if(!loadTmReturnImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
				{
					continue;
				}
			}
		}
		else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results)/40);
			
			//$req->setUseHasNext(1);
			for($i=$total_pages; $i>=1; $i--)
			{
				$req->setPageNo($i);
				$retval = $top->execute($req, $session);
				if(API_RESULT_OK != topErrorTest($retval, $db, $shopId))
				{
					$error_msg = $retval->error_msg;
					logx("topDownloadTmReturnList2 top->execute fail2", $sid);
					logx("ERROR $sid topDownloadTmReturnList2", 'error');
					return TASK_OK;
				}
				
				$refunds = $retval->return_bill_list->return_bill;
				for($j =0; $j < count($refunds); $j++)
				{
					if(!loadTmReturnImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
					{
						continue;
					}
					
					if(count($goods_list) >= 100)
					{
						if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
						{
							return TASK_SUSPEND;
						}
					}
				}
			}
		}
		
		$end_time = $ptime + 1;
	}
	
	if(count($goods_list) > 0)
	{
		if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
		{
			return TASK_SUSPEND;
		}
	}
	
	if($save_time)
	{
		setSysCfg($db, "refund_last_synctime_{$shopId}_1", $save_time);
	}
	
	return TASK_OK;
}


function loadTmReturnImpl(&$db, $shop, &$refund, &$refund_list, &$goods_list)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$platformId = $shop->platform_id;
	
	$refundId = $refund->refund_id;
	
	$status = 0;
	switch($refund->status)
	{
		case 'wait_seller_agree': //买家已经申请退款，等待卖家同意
			$status=2;
			break;
		case 'goods_returning': //卖家已经同意退款，等待买家退货
			$status=3;
			break;
		case 'seller_refuse': //卖家拒绝退款 
			$status=1;
			break;
		case 'closed': //退款关闭
			$status=1;
			break;
		case 'success': //退款成功
			$status=5;
			break;
	}
	
	$aftersale = 1;
	$type = 2;
	
	$amount = bcdiv($refund->refund_fee,100);
	
	$refundRow = array(
		'platform_id' => $platformId,
		'shop_id' => $shopId,
		'refund_no' => $refundId,
		'tid' => $refund->tid,
		'type' => $type,
		'status' => $status,
		'process_status' => 0,
		'guarantee_mode' => 1,
		'refund_amount' => 0,
		'actual_refund_amount' => 0,
		'buyer_nick' => $refund->buyer_nick,
		'refund_time' => $refund->created,
		'current_phase_timeout' => dateValue(@$refund->current_phase_timeout),
		'is_aftersale' => $aftersale,
		'reason' => $refund->reason,
		'remark' => @$refund->desc,
		'refund_version' => $refund->refund_version,
		'created' => array('NOW()')
	);
	
	$refund_list[] = $refundRow;
	
	$goodsRow = array(
		'platform_id' => $platformId,
		'refund_no' => $refundId,
		'oid' => $refund->oid,
		'status' => $status,
		'num' => -1,
		'created' => array('NOW()')
	);
	
	$goods_list[] = $goodsRow;
	
	return true;
}

?>