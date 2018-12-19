<?php

include_once(ROOT_DIR . '/Common/api_error.php');
function sales_logistics_sync(&$db,$sync_id,$status,$error_msg,$sid)
{
    //logx('changtao sales_logistics_sync start:',$sid . "/Logistics");
    
    try {
        if( $db->execute('BEGIN')!==false ){
            $api_logistics_info = $db->query_result("SELECT platform_id,tid,trade_id,stockout_id,is_part_sync,is_last FROM api_logistics_sync WHERE rec_id={$sync_id};");
            if($status == 0){
                if(!$db->execute("UPDATE api_trade SET process_status=GREATEST(40,process_status) WHERE platform_id={$api_logistics_info['platform_id']} AND tid='{$api_logistics_info['tid']}';") ||
                !$db->execute("UPDATE api_logistics_sync SET sync_status=3, is_need_sync=0, error_msg='', sync_time=NOW() WHERE rec_id={$sync_id};") ||
                !$db->execute("INSERT INTO sales_trade_log(trade_id, operator_id, type, message) VALUES({$api_logistics_info['trade_id']}, 0, 140, CONCAT('物流同步成功:','{$api_logistics_info['tid']}'));")){
                    throw new Exception('sales_logistics_sync sql error');
                }
            }else if($status == -100){
                
                if(!$db->execute("UPDATE api_logistics_sync SET try_times=try_times+1,error_msg='{$error_msg}' WHERE rec_id={$sync_id};")){
                    throw new Exception('sales_logistics_sync sql error');
                }
            }else{
                if(!$db->execute("UPDATE api_logistics_sync SET sync_status={$status}, is_need_sync=0, error_msg='{$error_msg}' WHERE rec_id={$sync_id};")){
                    throw new Exception('sales_logistics_sync sql error');
                }
            }
        }
       // logx('changtao sales_logistics_sync end:',$sid . "/Logistics");
        $db->execute('COMMIT');
    } catch (Exception $e) {
        $msg = $e->getMessage();
       // logx('changtao sales_logistics_sync:'.$msg,$sid . "/Logistics");
        $db->execute('ROLLBACK');
        return false;
    }
    return true;
   
}
function set_sync_succ(&$db, $sid, $rec_id) {
    /*$sql = "update api_logistics_sync als, sales_trade st " .
           " set als.sync_status=3, als.is_need_sync=0, als.error_msg='', als.sync_time=now(), st.trade_status = 95 ".
           " where als.rec_id={$rec_id} and st.trade_id = als.trade_id;";*/
    /* if ($db->multi_query("call SP_SALES_LOGISTICS_SYNC({$rec_id},0,'')")) {
        return;
    } */
    if(sales_logistics_sync($db,$rec_id,0,'',$sid)){
        return;
    }

    usleep(100000 + rand(0, 100000));
    /* if (!$db->multi_query("call SP_SALES_LOGISTICS_SYNC({$rec_id},0,'')")) {
        logx("WARNING $sid set_sync_succ failed when updating status, rec_id:{$rec_id}", $sid . "/Logistics", 'error');
    } */
    if(!sales_logistics_sync($db,$rec_id,0,'',$sid)){
        logx("WARNING $sid set_sync_succ failed when updating status, rec_id:{$rec_id}", $sid . "/Logistics",'error');
        
    }
}

function set_sync_fail(&$db, $sid, $rec_id, $status, $error_msg) {
    if($error_msg == 'Invalid arguments:sub_tid'){
        $status = 5;
    }
    $error_msg = addslashes(iconv_substr($error_msg, 0, 200, 'UTF-8'));
    //$sql = "update api_logistics_sync set sync_status={$status}, is_need_sync=0, error_msg='{$error_msg}' where rec_id={$rec_id}";

    //if(!$db->execute($sql))
    if(!sales_logistics_sync($db,$rec_id,$status,"{$error_msg}",$sid)){
        logx("WARNING $sid set_sync_fail failed when updating status, rec_id: {$rec_id}", $sid . "/Logistics",'error');
    }else{
        if (-100 <> $status) {
            $message = "物流同步失败:{$error_msg}";
            $msg = array(
                'type'     => 10,
                'topic'    => 'logistics_sync_fail',
                'distinct' => 1,
                'msg'      => $message
            );
            SendMerchantNotify($sid, $msg);
        }
    }
    /* if (!$db->multi_query("call SP_SALES_LOGISTICS_SYNC({$rec_id},{$status},'{$error_msg}')")) {
        logx("WARNING $sid set_sync_fail failed when updating status, rec_id: {$rec_id}", $sid . "/Logistics", 'error');
    } else {
        if (-100 <> $status) {
            $message = "物流同步失败:{$error_msg}";
            $msg = array(
                'type'     => 10,
                'topic'    => 'logistics_sync_fail',
                'distinct' => 1,
                'msg'      => $message
            );
            SendMerchantNotify($sid, $msg);
        }
    } */
}

function set_sync_reset($db,$sid,$rec_id){
    try{
        if( $db->execute('BEGIN')!==false ){
            $api_logistics_info = $db->query_result("SELECT platform_id,tid,trade_id,stockout_id,is_part_sync,is_last FROM api_logistics_sync WHERE rec_id={$rec_id};");
            if(!$db->execute("UPDATE api_logistics_sync SET try_times=try_times+1,is_need_sync =1 WHERE rec_id={$rec_id};")) {
                throw new Exception('set_sync_reset sql error');
            }
        }
        $db->execute('COMMIT');
    }catch (Exception $e){
        $msg = $e->getMessage();
        logx('set_sync_reset:'.$msg,$sid . "/Logistics");
        $db->execute('ROLLBACK');
        return false;
    }
    return true;
}

function handle_special_oid(&$db, $sid, $platform_id, $tid, $trade_id, &$oids, &$error_msg) {
    if ('!!!!' == substr($oids, 0, 4)) //oids start with magic_code
    {
        $oid_list = $db->query("select distinct src_oid as oid from sales_trade_order where platform_id=%d and src_tid=%s and trade_id=%d",
                               (int)$platform_id, $tid, (int)$trade_id);

        if (!$oid_list) {
            logx("$sid query src_oid error in handle_special_oid!", $sid . "/Logistics",'error');
            $error_msg = '获取子订单号失败';
            return false;
        }

        $oid_arr = array();
        while ($row = $db->fetch_array($oid_list)) {
            $oid_arr[] = $row['oid'];
        }
        $db->free_result($oid_list);

        $oids = implode(',', $oid_arr);
    }

    return true;
}

//物流同步参数是否为空
function is_empty(&$db, $sid, $rec_id) {
    if (count(func_get_args()) < 4) return false;

    $args = array_slice(func_get_args(), 3);
    foreach ($args as $v) {
        if (empty($v) || is_null($v)) {
            set_sync_fail($db, $sid, $rec_id, 2, '请检查物流公司编码或其他信息是否完整！');
            return true;
        }
    }
    return false;
}
//判断是否重复值
function is_repeat($params){
    $arr = array();
    foreach($params as $v){
        if(in_array($v,$arr)){
            return true;
        }
        $arr[]=$v;
    }
    return false;
}

function update_als_status($trade,$db,$sid){
	try{
		$id = $trade->stockout_id;
		$sync_status_info = $db->query_result("select IF(so.status <=55 and als.sync_status is NULL,16,IF(als.sync_status is NULL,32,IF(sum(IF(als.sync_status<0,1,0))>0,64,IF(sum(als.sync_status)=3*count(1),128,IF(sum(als.sync_status)=0,256,IF(sum(als.sync_status)=2*count(1),512,1024)))))) as update_als_status,als.sync_status,als.stockout_id,so.status from stockout_order so LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id where  so.stockout_id=".$id." group by so.stockout_id");
		if(!empty($sync_status_info)){
			$db->execute("update stockout_order so set so.consign_status = if(so.consign_status & ".$sync_status_info['update_als_status'].",so.consign_status,so.consign_status+".$sync_status_info['update_als_status'].") where so.stockout_id=".$id);
			if($sync_status_info['sync_status']>=3 && ($sync_status_info['status']>95||$sync_status_info['status']==55)){
				$db->execute("update stock_logistics_no sln LEFT JOIN stockout_order so ON sln.logistics_id = so.logistics_id AND sln.logistics_no = so.logistics_no set sln.status = 7 where so.stockout_id=".$sync_status_info['stockout_id']." AND sln.status = 1");
        	}
		}
	}catch(Exception $e){
		$error_info = $e->getMessage();
		logx("ERROR update_als_status failed!!---$sid---$error_info", "/Logistics",'error');
		return false;
	}
	return true;
}
?>