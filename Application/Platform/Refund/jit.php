<?php
require_once(ROOT_DIR . '/modules/refund_sync/util.php');
require_once(ROOT_DIR . '/inc/address.php');

$GLOBALS['warehouse_sn'] = array(
	'1' => 'VIP_NH',
	'2' => 'VIP_SH',
	'3' => 'VIP_CD',
	'4' => 'VIP_BJ',
	'5' => 'VIP_HZ',
	'7' => 'VIP_HH',
	'8' => 'VIP_ZZ',
	'9' => 'VIP_SE',
	'10' => 'VIP_JC',
	'11' => 'VIP_DA',
	'12' => 'VIP_MRC',
	'13' => 'VIP_ZZKG',
	'14' => 'VIP_GZNS',
	'15' => 'VIP_CQKG',
	'16' => 'VIP_SZGY',
	'17' => 'VIP_FZPT',
	'18' => 'VIP_QDHD',
	'19' => 'HT_GZZY',
	'20' => 'HT_GZFLXY',
	'21' => 'VIP_NBJCBS',
	'22' => 'HT_NBYC',
	'23' => 'HT_HZHD',
	'24' => 'HT_JPRT',
	'25' => 'HT_AUXNXY',
	'26' => 'HT_USALATM',
	'27' => 'HT_USANYTM',
	'28' => 'HT_SZQHBH',
	'29' => 'FJFZ'

);

function jitDownloadTbRefundList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, &$total_trade_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	global $warehouse_sn;
	$loop_count = 0;
	$total_trade_count = 0;
	$new_trade_count = 0; 
	$chg_trade_count = 0; 
	
	$ptime = $end_time;
	
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	//供应商ID
	$vendor_id = $shop->account_nick;
	$session = $shop->session;
	$warehouse_no = $shop->warehouse_no;
	$refund_no = @$shop->refund_no;

	if(!is_numeric($warehouse_no))
   	{
        	foreach ($warehouse_sn as $no => $sn)
        	{
        		if ($warehouse_no == $sn)
        		{
        			$warehouse_no = $no;
        			break;
        		}
        	}
    	}

	if (empty($refund_no))
	{
		$refund_no = null;
	}

	if (empty($start_time) || empty($end_time) || $start_time == $end_time)
	{
		$st_time = null;
		$en_time = null;
	}
	else
	{
		$st_time = date('Y-m-d H:i:s', $start_time);
		$en_time = date('Y-m-d H:i:s', $end_time);
	}
	
	logx("jitDownloadTbRefundList $shopid $warehouse_no start_time:" . $st_time . " end_time:" . $en_time . " refund_no: $refund_no", $sid);

	$refund_list = array();
	$goods_list = array();
	
	try
	{
		require_once TOP_SDK_DIR . "vipshop/vipapis/vreturn/VendorReturnServiceClient.php";
	    $service=\vipapis\vreturn\VendorReturnServiceClient::getService();
	    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
	    $page = 1;
	    $page_size = 50;
	    $ctx->setAppKey($appkey);
	    $ctx->setAppSecret($appsecret);
	    $ctx->setAppURL("http://gw.vipapis.com/");
	    $ctx->setAccessToken($session);
		$ctx->setLanguage("zh");
	    $retval = $service->getReturnInfo($vendor_id, $warehouse_no, $refund_no, $st_time, $en_time, $page, $page_size);
	}
	catch(\Osp\Exception\OspException $e)
	{
	    if(API_RESULT_OK != vipshopErrorTest($e, $db, $shopid))
		{
			$error_msg = $e->returnMessage;
			logx("jitDownloadTbRefundList vipshop->execute fail! error msg:$error_msg", $sid);
			logx("ERROR $sid jitDownloadTbRefundList:$error_msg", 'error');
			return TASK_OK;
		}

		$error_msg=$e->returnMessage;
		logx("getReturnInfo fail: $error_msg".print_r($e,true) ,$sid);
		return TASK_OK;
	}


	if (empty( $retval->returnInfos ))
	{
		logx ( "jitDownloadTbRefundList $shopid count: 0", $sid );
		return TASK_OK;
	}
	
	//总条数
	$total_results = intval($retval->total);
	logx("jitDownloadTbRefundList $shopid count: $total_results", $sid);
	
	$refunds = $retval->returnInfos;
	
	if ($total_results <= count($refunds))
	{
		$total_trade_count += count($refunds);
		for($j = 0; $j < count($refunds); $j++)
		{
			$t = $refunds[$j];
			if(!downJitRefundDetail($db, $appkey, $appsecret, $shop, $t, $refund_list, $goods_list, $error_msg))
			{
				continue;
			}
			if(!putRefundToDb($db, $refund_list, $goods_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
			{
				return TASK_OK;
			}
		}
	}
	else
	{
		$total_pages = ceil(floatval($total_results)/$page_size);

		for($i=$total_pages; $i>0; $i--)
		{
			resetAlarm();
			try
			{
				$retval = $service->getReturnInfo($vendor_id, $warehouse_no, $refund_no, $st_time, $en_time, $i, $page_size);
			}
			catch(\Osp\Exception\OspException $e)
			{

				if(API_RESULT_OK != vipshopErrorTest($e, $db, $shopid))
				{
					$error_msg = $e->returnMessage;
					logx("jitDownloadTbRefundList error : {$error_msg}!!", $sid);
					logx("ERROR $sid jitDownloadTbRefundList error : {$error_msg}", 'error');
					return TASK_OK;
				}
				$error_msg=$e->returnMessage;
					logx("getReturnInfo fail: $error_msg".print_r($e,true),$sid);

					return TASK_OK;
			}
			
			$refunds = &$retval->returnInfos;
			for($j =0; $j < count($refunds); $j++)
			{
				$t = & $refunds[$j];
				
				if(!downJitRefundDetail($db, $appkey, $appsecret, $shop, $t, $refund_list, $goods_list, $error_msg))
				{
					continue;
				}
				
				if(!putRefundToDb($db, $refund_list, $goods_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
				{
					return TASK_OK;
				}
			}
			$total_trade_count += count($refunds);
		}
	}

	if(count($goods_list) > 0)
	{
		if(!putRefundToDb($db, $refund_list, $goods_list, $new_trade_count, $chg_trade_count, $error_msg, $sid))
		{
			return TASK_OK;
		}
	}
	
	return TASK_OK;
}

function downJitRefundDetail(&$db, $appkey, $appsecret, $shop, &$r, &$refund_list, &$goods_list, &$error_msg)
{
	global $warehouse_sn;
	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	//供应商ID
	$vendor_id = $shop->account_nick;
	$session = $shop->session;
	$warehouse_no = $r->warehouse;
	$warehouse = $warehouse_sn[$warehouse_no];
	$return_sn = $r->return_sn;
	logx('downJitRefundDetail refund_no:'.$return_sn ,$sid);
	$return_type = $r->return_type;
	$page = 1;
	$page_size = 50;

	try
	{
		require_once TOP_SDK_DIR . "vipshop/vipapis/vreturn/VendorReturnServiceClient.php";
	    $service=\vipapis\vreturn\VendorReturnServiceClient::getService();
	    $ctx=\Osp\Context\InvocationContextFactory::getInstance();
	    $ctx->setAppKey($appkey);
	    $ctx->setAppSecret($appsecret);
	    $ctx->setAppURL("http://gw.vipapis.com/");
	    $ctx->setAccessToken($session);
		$ctx->setLanguage("zh");
	    $retval = $service->getReturnDetail($vendor_id, $warehouse_no, $return_sn, null, null, $page, $page_size);
	} catch(\Osp\Exception\OspException $e){
	    if(API_RESULT_OK != vipshopErrorTest($e, $db, $shopid))
		{
			$error_msg = $e->returnMessage;
			logx("downJitRefundDetail error : {$error_msg}!!", $sid);
			logx("ERROR $sid downJitRefundDetail error : {$error_msg}", 'error');
			return TASK_OK;
		}

		$error_msg=$e->returnMessage;
		logx("getReturnDetail fail: $error_msg".print_r($e,true),$sid);

		return TASK_OK;
	}

	$order_num = $retval->total;
	if ($order_num == 0)
	{
		logx ( "downJitRefundDetail $return_sn count: 0", $sid );
		return true;
	}

	$refund = $retval->returnDeliveryInfos;
	$orders = $refund['0']->delivery_list;
	$goods_count = 0;
	$goods_type = 0;
	$mark_arr = array();
	$count = 0;

	for ($i=0; $i < count($orders); $i++)
	{ 
		$o = $orders[$i];
		$barcode = $o->barcode;
		$num = $o->qty;
		$po_no = $o->po_no;
		$goods_count += $num;
		$goods_info = $db->query_result("select rec_id,price,tax_price from jit_po_goods where jit_spec_no = '".$db->escape_string($barcode)."' and po_no = '".$db->escape_string($po_no)."' and jit_warehouse = {$warehouse}");
		$rec_id = @$goods_info['rec_id'];

		$make = $o->po_no.$o->barcode;
		if (isset($mark_arr[$make]))
		{
			$goods_list[$mark_arr[$make]]['num'] += $o->qty;
		}
		else
		{
			$goods_list[$count] = array
			(
				'vph_refund_no' => $return_sn,
				'po_no' => $o->po_no,
				'num' => $o->qty,
				'price' => (float)@$goods_info['price'],
				'tax_price' => (float)@$goods_info['tax_price'],
				'spec_no' => $barcode,
				'box_no' => $o->box_no,
				'created' =>  array('NOW()')
			);
			$mark_arr[$make] = $count;
			$count++;
		}

		//更新退款数量
		if (!empty($rec_id))
		{
			if ($refund_type >= 3)
			{
				switch ($return_type)
				{
					case 2:
					{
						$sql = "update jit_po_goods set sr_num=sr_num+{$num} where rec_id = {$rec_id}";
						break;
					}
					case 3:
					{
						$sql = "update jit_po_goods set tr_num=tr_num+{$num} where rec_id = {$rec_id}";
						break;
					}	
					default:break;
				}

				if(!$db->query($sql))
				{
					$msg = '数据库错误';
					logx("update jit_po_goods refund_num fail: {$rec_id}" ,$sid);
				}

				//更新结算数量
				$sql = "update jit_po_goods set to_accounts_num=sales_count-accounts_num-sr_num-tr_num where rec_id = {$rec_id}";
				if(!$db->query($sql))
				{
					$msg = '数据库错误';
					logx("update jit_po_goods to_accounts_num fail: {$rec_id}" ,$sid);
				}
			}
		}		
	}

	$goods_type = count($goods_list);
	$refund_list[] = array
	(
		'vph_refund_no' => $return_sn,
		'status' => 10,
		'vph_warehouse' => $warehouse,
		'refund_type' => $r->return_type,
		'pay_type' => $r->pay_type,
		'receiver' => $r->consignee,
		'receive_count' => $r->country,
		'receive_province' => $r->state,
		'receive_city' => $r->city,
		'receive_area' => $r->region,
		'receive_town' => $r->town,
		'receive_address' => $r->address,
		'receive_phone' => $r->mobile,
		'receive_tel' => $r->telephone,
		'self_reference' => $r->self_reference,
		'goods_count' => $goods_count,
		'goods_type' => $goods_type,
		'created' =>  array('NOW()')
	);

	return true;
}
?>
