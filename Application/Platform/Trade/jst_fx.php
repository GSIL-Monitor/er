<?php

require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . '/Trade/top_fx.php');


function top_jst_fenxiao_get_data(&$db, &$jst_db, $sid, $shop, $table, $nick_field, $time_from, $time_to, $func_data, $func_impl, &$new_trade_count, &$chg_trade_count)
{
	$trade_calc_sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$table} ".
						" where {$nick_field} ='" . addslashes($shop->account_nick) .
						"'  and jdp_modified>='{$time_from}' and jdp_modified<='{$time_to}' order by jdp_modified asc";
	$once_get = 1000;					
	$trades = $jst_db->query($trade_calc_sql . " limit $once_get");
	if(!$trades) //database error occur
	{
		$error_msg = '数据库错误';
		logx("ERROR $sid top_jst_fenxiao_get_data query jst db failed",$sid . "/TradeTaobao", 'error');
		return false;
	}
	
	$result = $jst_db->query_result("SELECT FOUND_ROWS() as total");
	$total_results = intval($result['total']);

	if ($total_results <= $once_get)
	{
		if(!top_jst_fenxiao_get_data_impl($trades, $trade_list, $order_list, $jst_db, $db, $shop, $new_trade_count, $chg_trade_count, $error_msg, $func_data, $func_impl))
		{
			$error_msg = '数据库错误';
			logx("ERROR $sid top_jst_fenxiao_get_data call_user_func_array return false",$sid . "/TradeTaobao", 'error');
			return false;
		}
	}
	else
	{
		$trade_sql = "select jdp_response from {$table} " .
					" where {$nick_field}='" . addslashes($shop->account_nick) .
					"'  and jdp_modified>='{$time_from}' and jdp_modified<='{$time_to}' order by jdp_modified asc";
					
		//even if the $offset is equal to $total_results, it doesn't matter.
		$offset = $once_get * (ceil($total_results/$once_get) - 1);
		
		// get from last in case of missing trades
		while ($offset >= 0)
		{
			$trades = $jst_db->query($trade_sql . " limit $offset, $once_get");
			if(!$trades) //database error occur
			{
				$error_msg = '数据库错误';
				logx("ERROR $sid top_jst_fenxiao_get_data jst db failed when more than one page",$sid . "/TradeTaobao", 'error');
				return false;
			}
			
			if(!top_jst_fenxiao_get_data_impl($trades, $trade_list, $order_list, $jst_db, $db, $shop, $new_trade_count, $chg_trade_count, $error_msg, $func_data, $func_impl))
			{
				$error_msg = '数据库错误';
				logx("ERROR $sid top_jst_fenxiao_get_data call_user_func_array return false when more than one page",$sid . "/TradeTaobao", 'error');
				return false;
			}
			
			$offset -= $once_get;
		}
	}
		
	return true;
}

function jst_top_fenxiao_download_trade_list(&$db, &$shop, $start_time, $end_time, $save_time, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$sid = $shop->sid;
	$shop_id = $shop->shop_id;
	
	if($save_time) 
		$save_time = $end_time;
		
	$jst_db = getJstDb($sid, $shop->push_rds_id);
	if(!$jst_db)
	{
		logx("getJstDb Failed in jst_top_fenxiao_download_trade_list", $sid . "/TradeTaobao");
		logx("ERROR $sid jst_top_fenxiao_download_trade_list getJstDb",$sid . "/TradeTaobao", 'error');
		return TASK_OK;
	}
	
	logx("jst_top_fenxiao_download_trade_list $shop_id start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid . "/TradeTaobao");
	
	$offset = 0;
	$trade_list = array();
	$order_list = array();
	$total_results = 0;
	$new_trade_count = 0;
	$chg_trade_count = 0;
	
	$incr_end_time = $start_time;
	while($incr_end_time < $end_time)
	{
		// at most one day in case of too much data
		if($end_time - $start_time > 3600*24)
		{
			$incr_end_time = $start_time + 3600*24;
		}
		else
		{
			$incr_end_time = $end_time;
		}
		
		resetAlarm();
		
		$time_from = date('Y-m-d H:i:s', $start_time-1);
		$time_to = date('Y-m-d H:i:s', $incr_end_time+1);

		if (empty($shop->order_type))
		{
			if (!top_jst_fenxiao_get_data($db, $jst_db, $sid, $shop, 'jdp_fx_trade', 'supplier_username', $time_from, $time_to, 'top_jst_fenxiao_get_agent_structure', 'top_load_fenxiao_agent_order', $new_trade_count, $chg_trade_count))
			{
				logx("top_jst_fenxiao_get_data agent failed in jst_top_fenxiao_download_trade_list", $sid . "/TradeTaobao");
				logx("ERROR $sid top_jst_fenxiao_get_data agent failed in jst_top_fenxiao_download_trade_list",$sid . "/TradeTaobao", 'error');
				releaseDb($jst_db);
				return TASK_OK;
			}
			$key = "order_last_synctime_" . $shop_id;
			$sub_type = "0";
		}
		else if (1 == $shop->order_type)
		{
			if (!top_jst_fenxiao_get_data($db, $jst_db, $sid, $shop, 'jdp_jx_trade', 'supplier_nick', $time_from, $time_to, 'top_jst_fenxiao_get_dealer_structure', 'top_load_fenxiao_dealer_order', $new_trade_count, $chg_trade_count))
			{
				logx("top_jst_fenxiao_get_data dealer failed in jst_top_fenxiao_download_trade_list", $sid . "/TradeTaobao");
				logx("ERROR $sid top_jst_fenxiao_get_data dealer failed in jst_top_fenxiao_download_trade_list",$sid . "/TradeTaobao", 'error');
				releaseDb($jst_db);
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
		
		if($save_time)
		{
			setSysCfg($db, $key, $save_time);
		}
		
		$start_time = $incr_end_time;
	}
	
	logx("jst_top_fenxiao_download_trade_list shop id:{$shop_id}, sub type:{$sub_type}, new $new_trade_count chg $chg_trade_count", $sid . "/TradeTaobao");
	
	releaseDb($db);
	return TASK_OK;
}

function top_jst_fenxiao_get_agent_structure(&$data)
{
	return $data->fenxiao_orders_get_response->purchase_orders->purchase_order;
}

function top_jst_fenxiao_get_dealer_structure(&$data)
{
	return $data->fenxiao_dealer_requisitionorder_query_response->dealer_orders->dealer_order;
}

function top_jst_fenxiao_get_data_impl(&$trades, &$trade_list, &$order_list, &$jst_db, &$db, $shop, &$new_trade_count, &$chg_trade_count, &$error_msg, $func_data, $func_impl)
{
	$sid = $shop->sid;
	$shop_id = $shop->shop_id;

	while($trade = $jst_db->fetch_array($trades))
	{	
		$response_trade = json_decode_safe($trade['jdp_response']);
		if(!is_object($response_trade))
		{
			logx("invalid json:" . print_r($trade, true), $sid . "/TradeTaobao");
			continue;
		}
		
		//$trade_info_list = $response_trade->fenxiao_orders_get_response->purchase_orders->purchase_order;

		$trade_info_list = call_user_func_array($func_data, array(&$response_trade));
		
		foreach ($trade_info_list as $trade_info)
		{
			$params = array(&$db, $sid, $shop_id, &$trade_info, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg);
			if(!call_user_func_array($func_impl, $params))
			{
				logx("load order failed in $func_impl" . print_r($trade_info, true), $sid . "/TradeTaobao");
				return false;
			}
		}
	}
	
	$jst_db->free_result($trades);
	
	if(count($order_list) > 0)
	{
		if(!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return false;
		}
	}

	return true;

}

?>