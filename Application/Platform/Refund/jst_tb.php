<?php

require_once(ROOT_DIR . '/Refund/util.php');
require_once(ROOT_DIR . '/Refund/top_tb.php');

function jstDownloadTbRefundList(&$db, $first_time, $shop, $start_time, $end_time, $save_time, &$total_refund_count, &$new_refund_count, &$chg_refund_count, &$error_msg)
{
	return jstDownloadTbRefundListImpl($db, $first_time, $shop, $start_time, $end_time, $save_time, $total_refund_count, $new_refund_count, $chg_refund_count, $error_msg);
}

function jstDownloadTbRefundListImpl(&$db, $first_time, $shop, $start_time, $end_time, $save_time, &$total_refund_count, &$new_refund_count, &$chg_refund_count, &$error_msg)
{
	$new_refund_count = 0;
	$chg_refund_count = 0;
	$total_refund_count = 0;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	
	$jst_db = getJstDb($sid, $shop->push_rds_id);
	if(!$jst_db)
	{
		logx("getJstDb Failed", $sid.'/Refund');
		logx("ERROR $sid getJstDb",$sid,'error');
		return TASK_OK;
	}
	
	logx("jstRefundTaobao $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) .
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Refund');
	
	$offset = 0;
	$refund_list = array();
	$goods_list = array();
	$total_results = 0;
	$once_get = 1000;
	
	$incr_end_time = $start_time;
	while($incr_end_time < $end_time)
	{
		// at most one day in case of too much data got
		if($end_time - $start_time > 3600*24)
		{
			$incr_end_time = $start_time + 3600*24;
		}
		else
		{
			$incr_end_time = $end_time;
		}
		
		// if the time is more than one day, it's needed to cost more than the default timeout, so we reset the timeout clock
		resetAlarm();
		
		$time_from = date('Y-m-d H:i:s', $start_time-1);
		$time_to = date('Y-m-d H:i:s', $incr_end_time+1);
		
		$trade_calc_sql = "select SQL_CALC_FOUND_ROWS jdp_response from jdp_tb_refund where seller_nick='" . $jst_db->escape_string($shop->account_nick);
		
		if($first_time)
			$trade_calc_sql .= "' and modified>='{$time_from}' and modified<='{$time_to}' order by seller_nick,modified asc";
		else
			$trade_calc_sql .= "' and jdp_modified>='{$time_from}' and jdp_modified<='{$time_to}' order by seller_nick,jdp_modified asc";
		
		$refunds = $jst_db->query($trade_calc_sql . " limit $once_get");
		if(!$refunds) //database error occur
		{
			$error_msg = '数据库错误';
			logx("ERROR $sid db_fail1",$sid,'error');
			return TASK_OK;
		}
		
		$total_results = $jst_db->query_result("SELECT FOUND_ROWS() as total");
		$total_results = intval($total_results['total']);
		
		logx(" jstDownloadTbRefundListImpl shop_id: {$shopId}  refund_total: {$total_results}", $sid.'/Refund');
		
		if ($total_results <= $once_get)
		{
			$total_refund_count += $total_results;
			
			while($refund = $jst_db->fetch_array($refunds))
			{
				$obj = json_decode_safe($refund['jdp_response']);
				if(!is_object($obj))
				{
					logx("invalid refund", $sid.'/Refund');
					continue;
				}
				
				if(!loadTbRefundImpl($db, $shop, $obj->refund_get_response->refund, $refund_list, $goods_list))
				{
					continue;
				}
			}
			$jst_db->free_result($refunds);
		}
		else
		{
			$jst_db->free_result($refunds);
			
			$trade_sql = "select jdp_response from jdp_tb_refund where seller_nick='" . $jst_db->escape_string($shop->account_nick);
			
			if($first_time)
				$trade_sql .= "' and modified>='{$time_from}' and modified<='{$time_to}' order by seller_nick,modified asc";
			else
				$trade_sql .= "' and jdp_modified>='{$time_from}' and jdp_modified<='{$time_to}' order by seller_nick,jdp_modified asc";
			
			//even if the $offset is equal to $total_results, it doesn't matter.
			$offset = $once_get * (ceil($total_results/$once_get) - 1);
			
			// get from last in case of missing trades
			while ($offset >= 0)
			{
				$refunds = $jst_db->query($trade_sql . " limit $offset, $once_get");
				if(!$refunds) //database error occur
				{
					$error_msg = '数据库错误';
					logx("ERROR $sid db_fail3", $sid,'error');
					return TASK_OK;
				}
				
				while($refund = $jst_db->fetch_array($refunds))
				{
					$obj = json_decode_safe($refund['jdp_response']);
					if(!is_object($obj))
					{
						logx("invalid refund", $sid.'/Refund');
						continue;
					}
					
					if(!loadTbRefundImpl($db, $shop, $obj->refund_get_response->refund, $refund_list, $goods_list))
					{
						continue;
					}
					
					++$total_refund_count;
					
					if(count($goods_list) >= 100)
					{
						if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
						{
							return TASK_OK;
						}
					}
				}
				$jst_db->free_result($refunds);
				
				$offset -= $once_get;
			}
		}
		
		if($save_time)
		{
			if(count($goods_list) > 0)
			{
				if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
				{
					return TASK_OK;
				}
			}
			
			if($first_time) $save_time = $incr_end_time-3600;
			else $save_time = $incr_end_time;
			
			setSysCfg($db, "refund_last_synctime_{$shopId}", $save_time);
		}
		
		$start_time = $incr_end_time;
	}
	
	if(count($goods_list) > 0)
	{
		if(!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid))
		{
			return TASK_OK;
		}
	}
	
	return TASK_OK;
}

?>