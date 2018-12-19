<?php

require_once(ROOT_DIR.'/Common/api_error.php');


/*
	1 status
	2 amount
	4 version
	8 logistics
*/
$GLOBALS['update_refund_sql'] = 'ON DUPLICATE KEY UPDATE '
	. 'modify_flag=IF((@old_refund_count:=(@old_refund_count+1))<0,0,IF((@modify_flag:=('
		. 'IF(status=VALUES(status),0,1)|IF(refund_version=VALUES(refund_version),0,4)'
		. '|IF((@an:=(VALUES(actual_refund_amount)=0)) OR actual_refund_amount=VALUES(actual_refund_amount),0,2)'
		. "|IF((@ln:=(VALUES(logistics_no)='')) OR logistics_no=VALUES(logistics_no) AND logistics_name=VALUES(logistics_name),0,8)"
	. '))<0,0,modify_flag|@modify_flag)),' 
	. 'refund_version=IF((@chg_refund_count:=(@chg_refund_count+if(@modify_flag=0,0,1)))<0,0,VALUES(refund_version)),'
	. 'status=IF(VALUES(status)=0,status,VALUES(status)),'
	. 'reason=VALUES(reason),remark=VALUES(remark),'
	. 'refund_amount=IF(@an,refund_amount,VALUES(refund_amount)),' 
	. 'actual_refund_amount=IF(@an,actual_refund_amount,VALUES(actual_refund_amount)),'
	. 'logistics_name=IF(@ln,logistics_name,VALUES(logistics_name)),logistics_no=IF(@ln,logistics_no,VALUES(logistics_no))';


$GLOBALS['update_refund_goods_sql'] = 'ON DUPLICATE KEY UPDATE status=VALUES(status),num=VALUES(num)';

//jit
$GLOBALS['update_refund_jit_sql'] = 'ON DUPLICATE KEY UPDATE '
	. 'rec_id= LAST_INSERT_ID(IF(@old_trade_count:=@old_trade_count+1,rec_id,rec_id)),'
	. 'rec_id= LAST_INSERT_ID(IF(@chg_trade_count:=@chg_trade_count+IF(refund_type=VALUES(refund_type),0,1)|IF(pay_type=VALUES(pay_type),0,1)|IF(receiver=VALUES(receiver),0,1)|'
	. 'IF(receive_count=VALUES(receive_count),0,1)|IF(receive_province=VALUES(receive_province),0,1)|IF(receive_city=VALUES(receive_city),0,1)|'
	. 'IF(receive_area=VALUES(receive_area),0,1)|IF(receive_town=VALUES(receive_town),0,1)|IF(receive_address=VALUES(receive_address),0,1)|'
	. 'IF(receive_tel=VALUES(receive_tel),0,1)|IF(receive_phone=VALUES(receive_phone),0,1)|IF(self_reference=VALUES(self_reference),0,1)|'
	. 'IF(goods_count=VALUES(goods_count),0,1)|IF(goods_type=VALUES(goods_type),0,1)|IF(status=VALUES(status),0,1),rec_id,rec_id)),'
	. 'goods_count=VALUES(goods_count),goods_type=VALUES(goods_type)';

$GLOBALS['update_refund_goods_jit_sql'] = 'ON DUPLICATE KEY UPDATE num=VALUES(num),return_reason=VALUES(return_reason),price=VALUES(price),tax_price=VALUES(tax_price)';

function putRefundsToDb(&$db, &$refund_list, &$goods_list, &$new_refund_count, &$chg_refund_count, &$error_msg, $sid)
{	
	global $update_refund_sql, $update_refund_goods_sql;
	
	if($db->execute('set @old_refund_count=0,@chg_refund_count=0') !== false && $db->execute('BEGIN') !== false)
	{
		if($db->execute("SELECT 1 FROM sys_lock WHERE `lock_name`='refund_deliver' FOR UPDATE") !== false)
		{
			if(putDataToTable($db, 'api_refund_order', $goods_list, $update_refund_goods_sql) !== false)
			{
				if(putDataToTable($db, 'api_refund', $refund_list, $update_refund_sql) !== false)
				{
					if($db->execute('COMMIT') !== false)
					{
						$total_count = count($refund_list);
						
						$refund_list = array();
						$goods_list = array();
						
						$row = $db->query_result('select @old_refund_count,@chg_refund_count');
						
						$new_refund_count += $total_count - intval($row['@old_refund_count']); //2.0 原始退款单不支持手动，所以这里没有用到。先改成退款单的字段。
						$chg_refund_count += intval($row['@chg_refund_count']);
						
						//标记一下有订单要递交
						setSysCfg($db, 'refund_should_deliver_open', 1);
						return true;
					}
				}
			}
		}
		
		$error_msg = '数据库错误:' . $db->error_msg();
		logx("ERROR $sid putRefundsToDb $error_msg",$sid.'/Refund', 'error');
		
		$db->execute('ROLLBACK');
		logx("WriteDB_Fail\n", $sid.'/Refund');
		logx(print_r($refund_list, true), $sid.'/Refund');
	}
	else
	{
		$error_msg = '数据库错误:' . $db->error_msg();
		logx("ERROR $sid putRefundsToDb2 $error_msg", $sid.'/Refund','error');
	}
	
	$refund_list = array();
	$goods_list = array();
	
	return false;
}


function deliverMerchantRefunds(&$db, &$error_msg, $sid)
{
	$now = time();
	//保存递交时间，降低递交频率
	$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('refund_last_deliver_time',$now) ON DUPLICATE KEY UPDATE `value`=IF($now>LAST_INSERT_ID(`value`)+30,$now,`value`)");
	
	$lastDeliverTime = (int)$db->query_result_single("select LAST_INSERT_ID()", 0);
	if($now <= $lastDeliverTime + 30 && $lastDeliverTime>10000)
		return false;
	
	$now = time();
	
	$result = $db->multi_query("call SP_SALES_DELIVER_REFUND(0)");
	if(!$result)
	{
		logx("SP_SALES_DELIVER_REFUND Fail Secs:" . (time() - $now), $sid.'/Refund');
		$error_msg = $db->error_msg();
		return false;
	}
	
	if($result === true)
	{
		//标记递交完成
		setSysCfg($db, 'refund_should_deliver_open', 0);
		return true;
	}
	
	$has_error = false;
	while($row = $db->fetch_array($result))
	{
		if($row['error_code'] == 2)
			continue;
		
		if(!$has_error)
		{
			$has_error = true;
			logx("SP_SALES_DELIVER_REFUND ERROR", $sid.'/Refund');
			logx("refund_id\t\tcode\tmessage",$sid.'/Refund');
		}
		
		logx("{$row['tid']}\t{$row['error_code']}\t{$row['error_info']},$sid.'/Refund'");
	}
	
	$db->free_result($result);
	if(!$has_error)
	{
		logx("SP_SALES_DELIVER_REFUND SUCCESS SECS:" . (time() - $now), $sid.'/Refund');
		
		//标记递交完成
		setSysCfg($db, 'refund_should_deliver_open', 0);
	}
	
	return true;
}

function putRefundToDb(&$db, &$refund_list, &$goods_list, &$new_refund_count, &$chg_refund_count, &$error_msg, $sid)
{	
	global $update_refund_jit_sql, $update_refund_goods_jit_sql;
	
	if($db->execute('set @old_trade_count=0,@chg_trade_count=0') !== false && $db->execute('BEGIN') !== false)
	{
		if($db->execute("SELECT 1 FROM sys_lock WHERE `lock_name`='refund_deliver' FOR UPDATE") !== false)
		{
			if(putDataToTable($db, 'jit_refund_detail', $goods_list, $update_refund_goods_jit_sql) !== false)
			{
				if(putDataToTable($db, 'jit_refund', $refund_list, $update_refund_jit_sql) !== false)
				{
					if($db->execute('COMMIT') !== false)
					{
						$total_count = count($refund_list);
						
						$refund_list = array();
						$goods_list = array();
						
						$row = $db->query_result('select @old_trade_count,@chg_trade_count');
						
						$new_refund_count += $total_count - intval($row['@old_trade_count']);
						$chg_refund_count += intval($row['@chg_trade_count']);
						
						return true;
					}
				}
			}
		}
		
		$error_msg = '数据库错误:' . $db->error_msg();
		logx("ERROR $sid putRefundsToDb $error_msg",$sid.'/Refund', 'error');
		
		$db->execute('ROLLBACK');
		logx("WriteDB_Fail\n", $sid);
		logx(print_r($refund_list, true), $sid.'/Refund');
		logx(print_r($goods_list, true), $sid.'/Refund');
	}
	else
	{
		$error_msg = '数据库错误:' . $db->error_msg();
		logx("ERROR $sid putRefundsToDb2 $error_msg", $sid.'/Refund','error');
	}
	
	$refund_list = array();
	$goods_list = array();
	
	return false;
}
?>
