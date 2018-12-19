<?php

require_once(ROOT_DIR . '/Refund/util.php');
require_once(ROOT_DIR . '/Common/address.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/RefundsReceiveGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/RefundRefuseRequest.php');
require_once(TOP_SDK_DIR . '/top/request/RefundAgreenRequest.php');
require_once(TOP_SDK_DIR . '/top/request/RpRefundReviewRequest.php');
require_once (TOP_SDK_DIR . '/top/request/RpReturngoodsAgreeRequest.php');
require_once (TOP_SDK_DIR . '/top/request/RpReturngoodsRefuseRequest.php');
require_once (TOP_SDK_DIR . '/top/request/LogisticsAddressSearchRequest.php');
require_once (TOP_SDK_DIR . '/top/request/RefundGetRequest.php');

function topDownloadTbRefundDetail(&$db, $appkey, $appsecret, $trades, &$scan_count, &$new_refund_count, &$chg_refund_count, &$error_msg)
{
	$new_refund_count = 0;
	$chg_refund_count = 0;

	$sid     = $trades->sid;
	$shopId  = $trades->shop_id;
	$session = $trades->session;

	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req            = new RefundGetRequest();

	$req->setFields('refund_id,tid,title,buyer_nick,seller_nick,total_fee,status,created,refund_fee,oid,good_status,company_name,sid,payment,reason,desc,has_good_return,modified,order_status');

	$tids = &$trades->tids;

	$refund_list    = array();
	$goods_list    = array();

	for ($i = 0; $i < count($tids); $i++) {
		$tid = $tids[ $i ];
		$req->setRefundId($tid);
		$retval = $top->execute($req, $session);
		if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
			$error_msg["status"] = 0;
			$error_msg["info"]   = $retval->error_msg;
			logx("ERROR $sid top_detail $tid", $sid . "/Refund",'error');
			return TASK_SUSPEND;
		}

		if (!loadTbRefundImpl($db, $trades, $retval->refund, $refund_list, $goods_list)) {
			continue;
		}

		++$scan_count;

		/*//写数据库
		if (count($order_list) >= 100) {
			if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
				return TASK_SUSPEND;
			}
		}*/
	}

	//保存剩下的到数据库
	if (count($goods_list) > 0) {
		if (!putRefundsToDb($db, $refund_list, $goods_list, $new_refund_count, $chg_refund_count, $error_msg, $sid)) {
			return TASK_SUSPEND;
		}
	}

	return TASK_OK;
}

//异步下载
function topDownloadTbRefundList(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg)
{
	return topDownloadTbRefundListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg,$new_refund_count,$chg_refund_count);
}

//同步下载
//countLimit	订单数限制
function topSyncDownloadTbRefundList(&$db, $appkey, $appsecret, $shop, $countLimit, $start_time, $end_time,
								  &$scan_count, &$total_new, &$total_chg, &$error_msg) {
	$scan_count = 0;
	$total_new  = 0;
	$total_chg  = 0;
	$error_msg  = '';

	return topDownloadTbRefundListImpl($db, $appkey, $appsecret, $shop, $start_time, $end_time, false, $scan_count, $error_msg,$total_new,$total_chg);
}
//taobao下载订单列表
function topDownloadTbRefundListImpl(&$db, $appkey, $appsecret, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg,&$new_refund_count,&$chg_refund_count)
{
	$ptime = $end_time;
	
	if($save_time) 
		$save_time = $end_time;
	
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	logx("topRefundTaobao $shopId start_time:" . 
		date('Y-m-d H:i:s', $start_time) . 
		" end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Refund');

	//taobao
	$session = $shop->session;
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new RefundsReceiveGetRequest();
	
	$req->setFields('refund_id,tid,title,buyer_nick,seller_nick,total_fee,status,created,refund_fee,oid,good_status,company_name,sid,payment,reason,desc,has_good_return,modified,order_status');
	
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
		
		$req->setStartModified(date('Y-m-d H:i:s', $ptime));
		$req->setEndModified(date('Y-m-d H:i:s', $end_time));
		
		//取总订单条数
		$req->setPageNo(1);
		
		$retval = $top->execute($req, $session);
		if(API_RESULT_OK != topErrorTest($retval, $db, $shopId))
		{
			$error_msg = $retval->error_msg;
			logx("topDownloadTbRefundList top->execute fail", $sid.'/Refund');
			logx("ERROR $sid topDownloadRefundList",$sid.'/Refund', 'error');
			return TASK_OK;
		}
		
		if(!isset($retval->refunds) || count($retval->refunds) == 0)
		{
			$end_time = $ptime + 1;
			logx("TbRefund $shopId count: 0", $sid.'/Refund');
			continue;
		}
		
		$refunds = $retval->refunds->refund;
		//总条数
		$total_results = intval($retval->total_results);
		$total_count += $total_results;
		//echo "total_results: $total_results\n";
		logx("TbRefund $shopId count: $total_results", $sid.'/Refund');
		
		//如果不足一页，则不需要再抓了
		if($total_results <= count($refunds))
		{
			for($j =0; $j < count($refunds); $j++)
			{
				if(!loadTbRefundImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
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
					logx("topDownloadTbRefundList2 top->execute fail2", $sid.'/Refund');
					logx("ERROR $sid topDownloadTbRefundList2",$sid.'/Refund', 'error');
					return TASK_OK;
				}
				
				$refunds = $retval->refunds->refund;
				for($j =0; $j < count($refunds); $j++)
				{
					if(!loadTbRefundImpl($db, $shop, $refunds[$j], $refund_list, $goods_list))
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


function loadTbRefundImpl(&$db, $shop, &$refund, &$refund_list, &$goods_list)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$platformId = $shop->platform_id;
	
	$refundId = $refund->refund_id;
	
	//淘宝退款单解密
	if(isset($refund->buyer_nick)&& !empty($refund->buyer_nick))
	{
		$refund->buyer_nick = top_decode($refund->buyer_nick, 'nick', $shop->session, $sid, $shopId);
	}

	//var_dump($refund);
	$status = 0;
	switch($refund->status)
	{
		case 'WAIT_SELLER_AGREE': //买家已经申请退款，等待卖家同意
			$status=2;
			break;
		case 'WAIT_BUYER_RETURN_GOODS': //卖家已经同意退款，等待买家退货
			$status=3;
			break;
		case 'WAIT_SELLER_CONFIRM_GOODS': //买家已经退货，等待卖家确认收货
			$status=4;
			break;
		case 'SELLER_REFUSE_BUYER': //卖家拒绝退款 
			$status=1;
			break;
		case 'CLOSED': //退款关闭
			$status=1;
			break;
		case 'SUCCESS': //退款成功
			$status=5;
			break;
	}
	
	$aftersale = 0;
	switch($refund->order_status)
	{
		case 'TRADE_NO_CREATE_PAY':
		case 'PAY_PENDING':
		case 'WAIT_BUYER_PAY':
		case 'WAIT_SELLER_SEND_GOODS':
		case 'TRADE_CLOSED':
		case 'TRADE_CLOSED_BY_TAOBAO':
			$type = 1;
			break;
		default:
			$aftersale = 1;
			if($refund->has_good_return)
			{
				$type = 2;
			}
			else
			{
				$type = 4; //退款不退货
			}
			
			logx("loadTbRefundImpl sid: {$sid}  refund_id: {$refundId}  refund_order_status: {$refund->order_status} sys_type:{$type}"  , $sid.'/Refund');
	}

	if($refund->refund_phase == 'onsale'){
		$aftersale = 0;
	}elseif($refund->refund_phase == 'aftersale'){
		$aftersale = 1;
	}
	
	$refundRow = array(
		'platform_id' => $platformId,
		'shop_id' => $shopId,
		'refund_no' => $refundId,
		'tid' => $refund->tid,
		'type' => $type,
		'status' => $status,
		'process_status' => 0,
		'guarantee_mode' => 1,
		'refund_amount' => $refund->refund_fee,
		'actual_refund_amount' => $refund->refund_fee,
		'title' => $refund->title,
		'logistics_name' => iconv_substr(@$refund->company_name,0,40,'UTF-8'),
		'logistics_no' => @$refund->sid,
		'buyer_nick' => $refund->buyer_nick,
		'refund_time' => $refund->created,
		'is_aftersale' => $aftersale,
		'reason' => $refund->reason,
		'remark' => @$refund->desc,
		'refund_version' => $refund->refund_version,
		'created' => array('NOW()')
	);
	
	$refund_list[] = $refundRow;
	
	$num = intval(@$refund->num);
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
	
	return true;
}

//拒绝退款
function topSyncRefundRefuse(&$db, $appkey, $appsecret, $shop, $refund_id, $refuse_message, $refuse_proof, &$error_msg)
{
	$sid = $shop->sid;
	$shop_id = $shop->shop_id;
	$session = $shop->session;
	
	$result = $db->query_result("select refund_no,refund_version,type,is_aftersale from api_refund where refund_id={$refund_id} ");
	if (!$result)
	{
		$error_msg = '无效的退款单';
		logx("topSyncRefundRefuse RecID: {$refund_id} {$error_msg}", $sid);
		return TASK_OK;
	}
	
	$type = '';
	if($result['is_aftersale'] == 0){
		$type = 'onsale';
	}else if($result['is_aftersale'] == 1){
		$type = 'aftersale';
	}else {
		$type = 'onsale';
		logx("topSyncRefundRefuse RecID: {$refund_id} {$result['is_aftersale']} 未知 默认售中", $sid);
	}
	
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new  RefundRefuseRequest();
	
	$req->setRefundId($result['refund_no']);
	$req->setRefuseMessage($refuse_message);
	$req->setRefuseProof(@$refuse_proof);
	$req->setRefundPhase($type);
	$req->setRefundVersion($result['refund_version']);
	logx(print_r($req, true), $sid);
	$retval = $top->execute($req, $session);
	logx("refuse:".print_r($retval, true), $sid.'/Refund');
	if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
	{
		$error_msg = $retval->error_msg;
		logx("topSyncRefundRefuse top->execute fail $error_msg", $sid);
		return TASK_OK;
	}
	
	$error_msg = '成功';
	if (false == $db->execute("update api_refund set status = 1 where refund_id={$refund_id} "))
	{
		logx("update api_refund failed in topSyncRefundRefuse!", $sid.'/Refund');
		return TASK_OK;
	}
	
	return TASK_OK;
}


//同意退款
function topSyncRefundAgreen(&$db, $appkey, $appsecret, $shop, $refund_no, $code='', &$error_msg)
{
	$sid = $shop->sid;
	$shop_id = $shop->shop_id;
	$session = $shop->session;
	$uid = $shop->uid;

	$sub_result = $db->query_result("select sub_app_key,sub_account from cfg_shop where shop_id={$shop_id} and sub_auth_state = 1");
	if (!$sub_result)
	{
		$error_msg = '抱歉子账号不存在或未授权，请重新授权绑定一下';
		logx("topSyncRefundAgreen refund_no: {$refund_no}  uid: {$uid}  error: {$error_msg}", $sid.'/Refund');
		return TASK_OK;
	}else
	{
		$subData = json_decode($sub_result['sub_app_key']);
		$subSession = $subData->session;
	}
	
	$refund_nos = explode(',' , $refund_no);
	$select_refund_sql = sprintf("select refund_no,refund_version,type,is_aftersale,refund_amount from api_refund where refund_no in ('%s') and platform_id = %d and tag = 0" , join("','" , $refund_nos), $shop->platform_id);
	$results = $db->query($select_refund_sql);
	if (!$results)
	{
		$error_msg = '无效的退款单';
		logx("topSyncRefundAgreen uid: {$uid} refund_no: {$refund_no} {$error_msg} SQL:{$select_refund_sql}", $sid.'/Refund');
		return TASK_OK;
	}

	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	
	$refund_info = '';
    $refund_no_array = array();

	while($result = $db->fetch_array($results))
	{

		$update_refund_orerate_sql = "update api_refund set operator_id={$uid} where refund_no='{$result['refund_no']}' and platform_id ={$shop->platform_id} and tag = 0";
		if(false == $db->execute($update_refund_orerate_sql)){
			logx("topSyncRefundAgreen Update Operator Failed SQL:{$update_refund_orerate_sql}", $sid.'/Refund');
		}else{
			logx("topSyncRefundAgreen Update Operator Successed SQL:{$update_refund_orerate_sql}", $sid.'/Refund');
		}
	    
	    // 兼容时间格式/时间戳
	   	$refund_version = $result['refund_version'];
	   	if(strpos($result['refund_version'], '-') !== false){
	   		$refund_version = str_pad(strtotime($result['refund_version']), 13,0);
	   	}

	    if($shop->sub_platform_id == 1)
	    {
	        if($result['is_aftersale'] == 1)
	        {
				$type = 'aftersale'; //售后
	        }elseif ($result['is_aftersale'] == 0) 
	        {
	        	$type = 'onsale'; //售中
	        }else 
	        {
	        	$type = 'onsale'; //售中

	        	logx("topSyncRefundAgreen refund_no: {$result['refund_no']}  type: {$result['type']} is_aftersale: {$result['is_aftersale']}   退款阶段未知 默认售中", $sid.'/Refund');
	        }
	        
	        if(empty($code))  //进行审核
	        {
	            $shop->type = $type;
	            $shop->sub_account = $sub_result['sub_account'];
	            topSyncRefundCheck($db, $top, $shop, $result,$error_msg);
	        }
	        
	        $refund_info .= $result['refund_no'] .'|'. ($result['refund_amount']*100) .'|'. $refund_version . '|' . $type . ',';
	    }else 
	    {
	        //c店退款成功不返回refund_no
	        array_push($refund_no_array,$result['refund_no']);
	        $refund_info .= $result['refund_no'] .'|'. ($result['refund_amount']*100) .'|'. $refund_version . ',';
	    }
	}
	
	$req = new  RefundAgreenRequest();
	$refund_info = rtrim($refund_info,',');
	
	//退款金额到达一定需要二次验证码
    if(!empty($code))
    {
        $req->setCode($code);
    }
	
	$req->setRefundInfos($refund_info);
	
	$retval = $top->execute($req, $subSession);

	if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
	{
		logx("agreen_request:".print_r($req, true), $sid.'/Refund');
		logx("agreen_result:".print_r($retval, true), $sid.'/Refund');
		
		$error_msg = $retval->error_msg;
		
		if($retval->code==53)
		{
			$error_msg = "请使用子账号操作退款或对其重新授权,有效期相对较短";
		}

		logx("topSyncRefundAgreen top->execute fail $uid $error_msg", $sid.'/Refund');
		return TASK_OK;
	}
	
	if($shop->sub_platform_id == 1) //天猫返回数据
	{
	    if(!isset($retval->results))
	    {
	        $error_msg = $retval->message;
	        logx("topSyncRefundAgreen  failed  $uid  $error_msg", $sid.'/Refund');
	        return TASK_OK;
	    }
	    
	    if(isset($retval->results->refund_mapping_result))
	    {
	        $refund_mapping_result = $retval->results->refund_mapping_result;
	        $error_msg = $refund_mapping_result;
	        foreach ($refund_mapping_result as $refund_info)
	        {
	            if( $refund_info->message == "操作成功")
	            {
					$operate_id = sprintf(', operator_id=%d',$uid);

					$update_status_sql = "update api_refund set status = 5 {$operate_id} where refund_no='{$refund_info->refund_id}' and platform_id ={$shop->platform_id} and tag = 0";
	                if (false == $db->execute($update_status_sql))
	                {
	                    logx("topSyncRefundAgreen Update Stauts Failed SQL:{$update_status_sql}", $sid.'/Refund');
	                }
	            }else{
					logx("topSyncRefundAgreen uid:$uid refund_no:{$refund_info->refund_id} error:{$refund_info->message}", $sid.'/Refund');
				}
	        }
	        return TASK_OK;
	    }
	}else //C店返回
	{
	    if(!isset($retval->results))
	    {
	        $error_msg = $retval->message;
	        logx("topSyncRefundAgreen  failed  $uid  $error_msg", $sid.'/Refund');
	        return TASK_OK;
	    }
	    
	    if(isset($retval->msg_code))
	    {
	        if($retval->msg_code == 'SEND_CODE_SUCC')
	        {
	            $retval->msg_code = '发送二次验证短信成功';
	        }
	        if($retval->msg_code == 'OP_SUCC')
	        {
	            $retval->msg_code = '操作成功';
	        }
	    }
	    
	    if(isset($retval->results->refund_mapping_result))
	    {
	        $refund_mapping_result = $retval->results->refund_mapping_result;
	        $error_msg = $refund_mapping_result;
	        for ($i = 0; $i < count($error_msg); $i++) 
	        {
	            if(!isset($error_msg[$i]->refund_id))
	            {
	                $error_msg[$i]->refund_id =  $refund_no_array[$i];
	            }
	            
	            if(!isset($error_msg[$i]->message))
	            {
	                $error_msg[$i]->message = $retval->msg_code;
	            }
	            
	            if( $error_msg[$i]->message == "succ")
	            {
	                $error_msg[$i]->message = $retval->msg_code;
	                $refund_id = $error_msg[$i]->refund_id;

					$operate_id = sprintf(', operator_id=%d',$uid);

					$update_status_sql = "update api_refund set status = 5 {$operate_id} where refund_no='{$refund_id}' and platform_id ={$shop->platform_id} and tag = 0";
	                if (false == $db->execute($update_status_sql))
	                {
	                    logx("topSyncRefundAgreen Update Stauts SQL:{$update_status_sql}", $sid.'/Refund');
	                }
	            }
	        }
	        return TASK_OK;
	    }
	}
	
	logx("agreen_request:".print_r($req, true), $sid.'/Refund');
	logx("agreen_result:".print_r($retval, true), $sid.'/Refund');
	
	$error_msg = (isset($retval->message) && !empty($retval->message)) ? $retval->message: '未知错误';
	
	return TASK_OK;
}

//对退款单进行审核
function topSyncRefundCheck(&$db, $top, $shop, $result,&$error_msg)
{
	// 兼容时间格式/时间戳
   	$refund_version = $result['refund_version'];
   	if(strpos($result['refund_version'], '-') !== false){	
   		$refund_version = str_pad(strtotime($result['refund_version']), 13,0);
   	}

	$req = new  RpRefundReviewRequest();
	
	$req->setRefundId($result['refund_no']);
    $req->setOperator($shop->sub_account);
    $req->setRefundPhase($shop->type);
    $req->setRefundVersion($refund_version);
    $req->setResult("true");
    $req->setMessage("同意退款");
	
	$retval = $top->execute($req, $shop->session);

	
	if(API_RESULT_OK != topErrorTest($retval, $db, $shop->shop_id))
	{
		$error_msg = $retval->error_msg;
		logx("topSyncRefundCheck top->execute fail  refund_no: {$result['refund_no']} type: {$shop->type}   error: {$error_msg}", $shop->sid.'/Refund');
		return false;
	}

	return true;
}

//同意退货
function topSyncRefundGoodsAgree(&$db, $appkey, $appsecret, $shop, $refund_id, &$error_msg)
{
    $sid = $shop->sid;
    $shop_id = $shop->shop_id;
    $session = $shop->session;
    //$uid = $shop->uid;
    
    $remark = trim($shop->remark);
    
    $result = $db->query_result("select refund_no,refund_version,type,is_aftersale from api_refund where refund_id={$refund_id} ");
    if (!$result)
    {
        $error_msg = '无效的退款单';
        logx("topSyncRefundGoodsAgree RecID: {$refund_id} {$error_msg}", $sid.'/Refund');
        return TASK_OK;
    }
    
    $type = '';
    if($result['is_aftersale'] == 1)
    {
        $type = 'aftersale';
    }else if($result['is_aftersale'] == 0)
    {
        $type = 'onsale';
    }


    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    
    //获取默认收货地址
    $defaultAddr = topSyncLogisticsAddressSearch($top , $shop ,$error_msg);
    if(!$defaultAddr){
        logx("topSyncRefundGoodsAgree get default address info fail $error_msg", $sid.'/Refund');
        return TASK_OK;
    }
    if(!isset($defaultAddr[0]) || !isset($defaultAddr[0]->cancel_def) )
    {
        logx("topSyncRefundGoodsAgree get default address info fail default_address:" . print_r($defaultAddr , true), $sid.'/Refund');
    }
    $defaultAddr = $defaultAddr[0];
    
    $req = new RpReturngoodsAgreeRequest();
    $req->setRefundId($result['refund_no']);
    $selleradressid = $defaultAddr->contact_id;
    if($shop->sub_platform_id == 1)
    {
        //如果传过来为空 则使用 默认信息中的退款备注
        $remark = empty($remark)? $defaultAddr->memo : $remark;

        // 兼容时间格式/时间戳
	   	$refund_version = $result['refund_version'];
	   	if(strpos($result['refund_version'], '-') !== false){	
	   		$refund_version = str_pad(strtotime($result['refund_version']), 13,0);
	   	}
        
        $phase = $type;
        $version = $refund_version;
        
        
        $req->setRemark( $remark );                     // 卖家备注 - m
        $req->setRefundPhase( $type );                  // 退货阶段 - m
        $req->setRefundVersion( $version );             // 退款版本 - m
        $req->setSellerAddressId( $selleradressid );    // 卖家地址编号 - m
    }else
    {
        $name = $defaultAddr->contact_name;
        $address = $defaultAddr->province . $defaultAddr->city . $defaultAddr->country .  $defaultAddr->addr;
        $post = $defaultAddr->zip_code;
        $tel = $defaultAddr->phone;
        $mobile = $defaultAddr-> mobile_phone;
        
        $req->setName( $name );                         // 卖家姓名 - c
        $req->setAddress( $address );                   // 卖家退货地址 - c
        $req->setPost( $post );                         // 卖家邮编 - c
        $req->setTel( $tel );                           // 卖家座机 - c
        $req->setMobile( $mobile );                     // 卖家手机 - c
        $req->setSellerAddressId( $selleradressid );    // 卖家地址编号 - m
    }

    $retval = $top->execute($req, $session);
    logx("topSyncRefundGoodsAgree retval:".print_r($retval, true), $sid.'/Refund');
    if(API_RESULT_OK != topErrorTest($retval, $db, $shop_id))
    {
        $error_msg = $retval->error_msg;
        logx("topSyncRefundGoodsAgree top->execute fail $error_msg", $sid.'/Refund');
        return TASK_OK;
    }
    
    $error_msg = '成功';
    //1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功
    if (false == $db->execute("update api_refund set status = 4 where refund_id={$refund_id} "))
    {
        logx("update api_refund failed in topSyncRefundGoodsAgree!", $sid.'/Refund');
        return TASK_OK;
    }
    
    return TASK_OK;
   
}

//查询卖家地址
function topSyncLogisticsAddressSearch(&$top , &$shop ,&$error_msg)
{
    $req = new LogisticsAddressSearchRequest();
    $req->setRdef("cancel_def"); //查询默认退货地址
    
    $retval = $top->execute($req, $shop->session);
	
	if(API_RESULT_OK != topErrorTest($retval, $db, $shop->shop_id))
	{
		$error_msg = $retval->error_msg;
		logx("topSyncLogisticsAddressSearch top->execute fail error: {$error_msg}", $shop->sid.'/Refund');
		return false;
	}

	return $retval->addresses->address_result;
}

?>
