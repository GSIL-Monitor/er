<?php

require_once(ROOT_DIR . '/Common/api_error.php');


$GLOBALS['zhi_xia_shi'] = array('北京', '天津', '上海', '重庆');

$GLOBALS['spec_province_map'] = array(
    '北京' => '北京',
    '天津' => '天津',
    '上海' => '上海',
    '重庆' => '重庆',
    '广西' => '广西壮族自治区',
    '内蒙' => '内蒙古自治区',
    '新疆' => '新疆维吾尔自治区',
    '宁夏' => '宁夏回族自治区',
    '西藏' => '西藏自治区',
    '香港' => '香港特别行政区',
    '澳门' => '澳门特别行政区',
    '海外' => '海外');

/*
	1 trade_status
	2 pay_status
	4 refund_status
	8 remark
	16 address
	32 inovice
	64 warehouse
*/
$GLOBALS['update_trade_sql'] = 'ON DUPLICATE KEY UPDATE '
    . 'modify_flag=IF((@old_trade_count:=(@old_trade_count+1))<0,0,IF((@modify_flag:=('
    . 'IF(trade_status=VALUES(trade_status),0,1)|IF(pay_status=VALUES(pay_status),0,2)|IF(refund_status>=VALUES(refund_status),0,4)|'
    . 'IF(remark=VALUES(remark) AND remark_flag=VALUES(remark_flag),0,8)|IF(receiver_hash=VALUES(receiver_hash),0,16)|'
    . 'IF(invoice_type=VALUES(invoice_type) AND invoice_title=VALUES(invoice_title) AND invoice_content=VALUES(invoice_content),0,32)|'
    . 'IF(wms_type=VALUES(wms_type) AND warehouse_no=VALUES(warehouse_no),0,64)|IF(buyer_message=VALUES(buyer_message),0,128)'
    . '))<0,0,modify_flag|@modify_flag)),'
    . 'pay_id=IF((@chg_trade_count:=(@chg_trade_count+if(@modify_flag=0,0,1)))<0,0,VALUES(pay_id)),'
    . 'trade_status=GREATEST(trade_status,VALUES(trade_status)),pay_status=GREATEST(pay_status,VALUES(pay_status)),pay_time=VALUES(pay_time),buyer_nick=VALUES(buyer_nick),'
    . 'refund_status=VALUES(refund_status),remark=VALUES(remark),remark_flag=VALUES(remark_flag),buyer_message=VALUES(buyer_message),'
    . 'purchase_id=VALUES(purchase_id),invoice_type=VALUES(invoice_type),invoice_title=VALUES(invoice_title),invoice_content=VALUES(invoice_content),'
    . 'receiver_name=VALUES(receiver_name),receiver_province=VALUES(receiver_province),receiver_city=VALUES(receiver_city),'
    . 'receiver_district=VALUES(receiver_district),receiver_address=VALUES(receiver_address),receiver_mobile=VALUES(receiver_mobile),'
    . 'receiver_telno=VALUES(receiver_telno),receiver_zip=VALUES(receiver_zip),receiver_area=VALUES(receiver_area),'
    . 'to_deliver_time=VALUES(to_deliver_time),receiver_hash=VALUES(receiver_hash),goods_amount=VALUES(goods_amount),'
    . 'post_amount=VALUES(post_amount),other_amount=VALUES(other_amount),discount=VALUES(discount),receivable=VALUES(receivable),paid=VALUES(paid),'
    . 'platform_cost=VALUES(platform_cost),received=VALUES(received),dap_amount=VALUES(dap_amount),cod_amount=VALUES(cod_amount),'
    . 'pi_amount=VALUES(pi_amount),refund_amount=VALUES(refund_amount),wms_type=VALUES(wms_type),warehouse_no=VALUES(warehouse_no),'
    . 'real_score=VALUES(real_score),got_score=VALUES(got_score),goods_count=VALUES(goods_count),order_count=VALUES(order_count),delivery_term=VALUES(delivery_term),bad_reason=0';

/*
	1 status
	2 refund_status
	4 invoice
	8 discount
	16 goods
	32 warehouse
*/
$GLOBALS['update_order_sql'] = 'ON DUPLICATE KEY UPDATE '
    . 'modify_flag=modify_flag|IF(status=VALUES(status),0,1)|IF(refund_status=VALUES(refund_status),0,2)|'
    . 'IF(invoice_type=VALUES(invoice_type) AND invoice_content=VALUES(invoice_content),0,4)|'
    . 'IF(adjust_amount=VALUES(adjust_amount) AND discount=VALUES(discount) AND share_discount=VALUES(share_discount),0,8)|'
    . "IF((@gn:=(VALUES(goods_id)='')) OR goods_id=VALUES(goods_id) AND spec_id=VALUES(spec_id),0,16)|"
    . 'IF(wms_type=VALUES(wms_type) AND warehouse_no=VALUES(warehouse_no),0,32),'
    . 'status=VALUES(status),refund_status=VALUES(refund_status),invoice_type=VALUES(invoice_type),'
    . 'invoice_content=VALUES(invoice_content),refund_id=VALUES(refund_id),adjust_amount=VALUES(adjust_amount),share_post=VALUES(share_post),'
    . 'share_discount=VALUES(share_discount),total_amount=VALUES(total_amount),share_amount=VALUES(share_amount),'
    . 'share_cost=VALUES(share_cost),paid=VALUES(paid),refund_amount=VALUES(refund_amount),goods_id=IF(@gn,goods_id,VALUES(goods_id)),'
    . 'spec_id=IF(@gn,spec_id,VALUES(spec_id)),goods_no=IF(@gn,goods_no,VALUES(goods_no)),spec_no=IF(@gn,spec_no,VALUES(spec_no)),'
    . 'goods_name=IF(@gn,goods_name,VALUES(goods_name)),spec_name=IF(@gn,spec_name,VALUES(spec_name)),'
    . 'wms_type=VALUES(wms_type),warehouse_no=VALUES(warehouse_no),is_auto_wms=VALUES(is_auto_wms)';

function putTradesToDb(&$db, &$trade_list, &$order_list, &$discount_list, &$new_trade_count, &$chg_trade_count, &$error_msg, $sid) {
    logx(print_r($trade_list,true));
    global $update_trade_sql, $update_order_sql;
    logx('step4: 开始写入数据库'.print_r(time(),true),$sid.'/TradeTaobao');

    if ($db->execute('set @old_trade_count=0,@chg_trade_count=0') !== false && $db->execute('BEGIN') !== false) {
        if ($db->execute("SELECT 1 FROM sys_lock WHERE `lock_name`='trade_deliver' FOR UPDATE") !== false) {
            if (putDataToTable($db, 'api_trade_discount', $discount_list, '') !== false) {
                if (putDataToTable($db, 'api_trade_order', $order_list, $update_order_sql) !== false) {
                    if (putDataToTable($db, 'api_trade', $trade_list, $update_trade_sql) !== false) {
                        if ($db->execute('COMMIT') !== false) {
                            $total_count = count($trade_list);

                            $trade_list = array();
                            $order_list = array();
                            $discount_list = array();

                            $row = $db->query_result('select @old_trade_count,@chg_trade_count');

                            $new_trade_count += $total_count - intval($row['@old_trade_count']);
                            $chg_trade_count += intval($row['@chg_trade_count']);

                            //标记一下有订单要递交
                            if ($new_trade_count > 0 || $chg_trade_count > 0)
                                setSysCfg($db, 'order_should_deliver', 1);
                            logx('step5: 写入数据库结束'.print_r(time(),true),$sid.'/TradeTaobao');

                            return true;
                        }
                    }
                }
            }
        }


        $error_msg = '数据库错误:' . $db->error_msg();
        logx("ERROR $sid putTradesToDb $error_msg", $sid . "/Trade",'error');

        $db->execute('ROLLBACK');
        logx("$sid WriteDB_Fail\n", $sid . "/Trade",'error');
        logx("V2-time:  " . date('Y-m-d H:i:s', time()) . "  sid:  $sid WriteDB_Fail, error_msg:  $error_msg  trade list: " . print_r($trade_list, true), $sid . "/Trade",'error');
        logx(print_r($trade_list, true), $sid . "/Trade");
    } else {
        $error_msg = '数据库错误:' . $db->error_msg();
        logx("ERROR $sid putTradesToDb2 $error_msg", $sid . "/Trade",'error');
    }

    $trade_list    = array();
    $order_list    = array();
    $discount_list = array();

    return false;
}


function deliverMerchantTrades(&$db, &$error_msg, $sid) {
    $now = time();
    //保存递交时间，降低递交频率
    $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('order_last_deliver_time',$now) ON DUPLICATE KEY UPDATE `value`=IF($now>LAST_INSERT_ID(`value`)+30,$now,`value`)");

    $lastDeliverTime = (int)$db->query_result_single("select LAST_INSERT_ID()", 0);
    if ($now <= $lastDeliverTime + 30 && $lastDeliverTime > 10000)
        return false;
    $result = $db->multi_query("call SP_SALES_DELIVER_ALL(0)");
    if (!$result) {
        logx("SP_SALES_DELIVER_ALL Fail Secs:" . (time() - $now), $sid . "/Trade");
        $error_msg["status"] = 0;
        $error_msg["info"]   = $db->error_msg();

        if ($db->error_code() != 1205) {
            //发即时消息
            $msg = array(
                'type'     => 10,
                'topic'    => 'trade_deliver_fail',
                'distinct' => 1,
                'msg'      => '递交出现异常'
            );
            SendMerchantNotify($db->get_tag(), $msg);
        }

        return false;
    }

    if ($result === true) {
        //标记递交完成
        setSysCfg($db, 'order_should_deliver', 0);
        return true;
    }

    $has_error     = false;
    $error_message = '';
    while ($row = $db->fetch_array($result)) {
        if (!$has_error) {
            $has_error = true;
            logx("SP_SALES_DELIVER_ALL ERROR", $sid . "/Trade");
            logx("tid\tnick\tcode\tmessage", $sid . "/Trade");
        }
        logx("{$row['tid']}\t{$row['buyer_nick']}\t{$row['error_code']}\t{$row['error_info']}", $sid . "/Trade");
        $error_message = "订单{$row['tid']}:{$row['error_info']}";
    }

    $db->free_result($result);
    if (!$has_error) {
        logx("SP_SALES_DELIVER_ALL SUCCESS SECS:" . (time() - $now), $sid . "/Trade");
        //标记递交完成
        setSysCfg($db, 'order_should_deliver', 0);

        $row = $db->query_result("SELECT IFNULL(@tmp_to_preorder_count,0) to_preorder,IFNULL(@tmp_to_check_count,0) to_check");
        if ($row) {
            $message = '';
            if ($row['to_preorder'] > 0)
                $message = "新增预订单: {$row['to_preorder']}单";

            if ($row['to_check'] > 0)
                $message = "新增待审核订单: {$row['to_check']}单";

            if (!empty($message)) {
                //发即时消息
                $msg = array(
                    'type'     => 10,
                    'topic'    => 'trade_deliver_success',
                    'distinct' => 1,
                    'msg'      => $message
                );
                SendMerchantNotify($sid, $msg);
            }
        }
    } else {
        //发即时消息
        $msg = array(
            'type'     => 10,
            'topic'    => 'trade_deliver_fail',
            'distinct' => 1,
            'msg'      => $error_message
        );
        SendMerchantNotify($sid, $msg);
    }
    checkTrade($sid,$db);
    return true;
}

function deliverSomeTrade(&$db, &$error_msg, $ids, $sid) {
    $now    = time();
    $result = $db->multi_query("call SP_SALES_DELIVER_SOME('" . addslashes($ids) . "')");
    if (!$result) {
        logx("$sid SP_SALES_DELIVER_SOME Fail Secs:" . (time() - $now), $sid . "/Trade",'error');
        $error_msg["status"] = 0;
        $error_msg["info"]   = $db->error_msg();
        return false;
    }

    if ($result === true)
        return true;

    $has_error = false;
    while ($row = $db->fetch_array($result)) {
        if (!$has_error) {
            $has_error = true;
            logx("$sid SP_SALES_DELIVER_SOME ERROR", $sid . "/Trade",'error');
            logx("tid\tnick\tcode\tmessage", $sid . "/Trade");
        }

        logx("$sid {$row['tid']}\t{$row['buyer_nick']}\t{$row['error_code']}\t{$row['error_info']}", $sid . "/Trade",'error');
    }

    $db->free_result($result);

    return true;
}

function checkTrade($sid,$db){
	$now=time();
	$info=$db->query_result('SELECT c1.`value` AS auto_check_is_open ,c2.`value` AS buyer_message_count,c3.`value` AS cs_remark_count,
							c4.`value` AS receiver_address,c5.`value` AS invoice_type,c6.`value` AS start_time,c7.`value` AS end_time, 
							c8.`value` AS under_weight, c9.`value` AS max_weight , c10.`value` AS time_type 
							FROM cfg_setting c1,cfg_setting c2,cfg_setting c3,cfg_setting c4,cfg_setting c5,cfg_setting c6,cfg_setting c7,cfg_setting c8,cfg_setting c9,cfg_setting c10   
							WHERE c1.`key`="auto_check_is_open" AND c2.`key`="auto_check_buyer_message" AND c3.`key`="auto_check_csremark"
							AND c4.`key`="auto_check_no_adr" AND c5.`key`="auto_check_no_invoice" AND c6.`key`="auto_check_start_time" 
							AND c7.`key`="auto_check_end_time" AND c8.`key`="auto_check_under_weight" AND c9.`key`="auto_check_max_weight"
							AND c10.`key`="auto_check_time_type"');
	if($info['auto_check_is_open']==0){
		return false;
	}
	if($info['under_weight']==0){
		unset ($info['max_weight']);
	}
	foreach ($info as $k=>$v){
		if($v==0){
			unset($info[$k]);
		}
	}
    $key=base64_encode(rc4(md5($sid.'!@#$'),strval($now-1000)));//加密时间戳，避免截取数据修改参数，限制每次请求的时间
    $post_data=array('info'=>json_encode($info),'key'=>$key,'sid'=>$sid,'action'=>'Trade/TradeCheck/quickCheckTrade');
    $postdata = http_build_query($post_data);  
    $options = array(  
        'http' => array(  
          'method' => 'POST',  
          'header' => 'Content-type:application/x-www-form-urlencoded',  
          'content' => $postdata,  
          'timeout' => 15 * 60 // 超时时间（单位:s）  
        )  
    );
    // $url='http://127.0.0.1'.$_SERVER['SCRIPT_NAME'].'/home/login/api.html';
    $url='http://127.0.0.1/index.php/home/login/api.html';
    $context = stream_context_create($options);  
    $result = file_get_contents($url, false, $context);
    $result = json_decode($result);
    if ($result->status==1) 
    {
        logx("AUTO_CHECK_TRADE ERROR", $sid . "/Trade");
        return false;
    }
    return true;
}

function  sync_remark_log(&$db,$trade,$bSuccess,$sid){

    $flagNames = array(
        -1 => '不回写',
        0  => '灰色',
        1  => '红色',
        2  => '黄色',
        3  => '绿色',
        4  => '蓝色',
        5  => '紫色',
    );

    $message = "追加备注:{$trade->cs_remark} 标旗:{$flagNames[$trade->flag]} ".($bSuccess?"成功":"失败") ;

    if(isset($trade->atur_rec_id) && $trade->atur_rec_id)
    {
        //异步模式               删除 api_trade_upload_remark 待回传记录
        if($bSuccess)
        {
            if(!$db->execute("DELETE FROM api_trade_upload_remark WHERE rec_id = {$trade->atur_rec_id}"))
            {
                logx("sync_remark_log 删除回传记录失败 tid:{$trade->tid}  rec_id:{$trade->atur_rec_id} ",$sid);
            }
        }else
        {
            if(!$db->execute("UPDATE api_trade_upload_remark SET status=2,remark='{$trade->error_msg}' WHERE rec_id = {$trade->atur_rec_id}"))
            {
                logx("sync_remark_log 更新回传记录状态失败 tid:{$trade->tid}  rec_id:{$trade->atur_rec_id} remark:{$trade->error_msg}",$sid);
            }
        }
        //添加系统订单日志/退款单日志
        if($trade->src_order_type == 1)
        {
            //退换单
            $srlog[] = array(
                "refund_id" => $trade->src_order_id,
                "operator_id" => $trade->uid,
                "type" => 65,
                "data" => (int)$bSuccess,
                "remark" => $message,
            );
            if(!putDataToTable($db, 'sales_refund_log', $srlog, ""))
            {
                logx("sync_remark_log 插入系统退换单日志失败 " . print_r($srlog,true), $sid);
            }
        }
        else
        {
            $stlog[] = array(
                "trade_id" => $trade->src_order_id,
                "operator_id" => $trade->uid,
                "type" => 200,
                "data" => (int)$bSuccess,
                "message" => $message,
            );
            if(!putDataToTable($db, 'sales_trade_log', $stlog, ""))
            {
                logx("sync_remark_log 插入系统订单日志失败 "  . print_r($stlog,true), $sid);
            }
        }

    }else
    {
        //手动模式
        //添加sales_trade_log 系统订单日志
        $stlog[] = array(
            "trade_id" => $trade->trade_id,
            "operator_id" => $trade->uid,
            "type" => 200,
            "data" => (int)$bSuccess,
            "message" => $message ,
        );
        if(!putDataToTable($db, 'sales_trade_log', $stlog, ""))
        {
            logx("sync_remark_log 插入系统订单日志失败 "  . print_r($stlog,true), $sid.'/TradeTaobao');
        }
    }

    //更新原始单备注以及标旗
    $updateSql = array();
    if($trade->flag != -1)
    {
        array_push($updateSql, "remark_flag = " . $trade->flag);
    }

    if(!empty($trade->memo))
    {
        array_push($updateSql, "remark = '" . $db->escape_string($trade->memo) ."'");
    }

    if(count($updateSql) > 0 && $bSuccess)
    {
        $upMemoSql = sprintf("UPDATE api_trade SET %s WHERE tid='%s' AND platform_id = %d",join(",", $updateSql),$trade->tid,$trade->platform_id);
        if(!$db->execute($upMemoSql)){
            logx("sync_remark_log  更新原始单备注标旗失败 sql:{$upMemoSql}", $sid.'/TradeTaobao');
        }
    }else
    {
        logx("sync_remark_log {$trade->tid} 无需更新原始单备注", $sid.'/TradeTaobao');
    }
}

?>