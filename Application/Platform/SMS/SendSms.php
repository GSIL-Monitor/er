<?php
require_once TOP_SDK_DIR."/sms/sms_client.php";

function SendSms($sid,$batch_no,&$msg){
    $count_sended = 0;//已发送条数
    //先获取卖家账户余额
    $res = get_balance($sid);
    logx('调用接口查询余额,result:'.print_r($res['info'],true),$sid.'/SMS');
    $balance = $res['info']['balance'];
    $num = $res['info']['sms_num'];
    $db = getUserDb($sid);
    if (!$db) {
        logx("$sid getUserDb failed in smsTradeUnpay", $sid . "/SMS");
        return false;
    }
    if($res['status'] || $balance<=0){
        $msg['info'] = '余额不足';
        $msg['status'] = 0;
        if(!empty($batch_no)){
            $db->execute("delete from crm_sms_record where batch_no='$batch_no'");
        } else{
            $db->execute("update crm_sms_record set status=3,error_msg='{$msg['info']}' where status=0 and sms_type=1 and timer_time<=now()");
        }
        releaseDb($db);
        return false;
    }
    if(empty($batch_no)) //定时发送
    {
        $to_time = date('Y-m-d H:i:s', time());
        $sql = "select rec_id,send_type,UNIX_TIMESTAMP(timer_time) timer_time,phones,message,success_people,phone_num from crm_sms_record where status=0 and sms_type=1 and timer_time<='$to_time'";
        $result = $db->query($sql);
    }
    else	//前端发送
    {
        $sql = "select rec_id,send_type,phones,message,success_people,phone_num from crm_sms_record where batch_no=%s and status=0";
        $result = $db->query($sql, $batch_no);
    }
    $conti = true;		//循环控制
    $status = 0;		//状态 0待发送  1发送中 2已发送 3发送失败4,取消发送

    $rows = array();
    $now = time();
    while($row = $db->fetch_array($result))
    {
        if(empty($batch_no))
        {
            $timer_time = (int)$row['timer_time'];
            if($timer_time < $now-3600*24) //订时超过24小时未发,自动取消
            {
                $db->execute("update crm_sms_record set status=4 where rec_id={$row['rec_id']}");
                continue;
            }
        }

        $rows[] = $row;
    }
    $db->free_result($result);

    $i=0;
    while($conti && $i < count($rows))
    {
        $row =& $rows[$i];
        ++$i;

        $phones = $row['phones'];
        $sendMsg = $row['message'];
        //把空电话号码去掉
        $sms_num = countSMS($sendMsg);
        $arr_phone = explode(',', $phones);
        $count = count($arr_phone);
        $phones = array_filter($arr_phone, 'validPhone');
        if($phones==''){
            $db->execute("update crm_sms_record set status=3,error_msg='电话号码无效' where rec_id={$row['rec_id']}");
            continue;
        }

        //余额不足
        if($sms_num*$count > $num - $count_sended)
        {
            logx("sms_num:$sms_num,count:$count,num:$num,count_sended:$count_sended");
            $db->execute("update crm_sms_record set status=3,error_msg='余额不足' where status=0 and sms_type=1 and timer_time<=now()");
            return false;
        }

        $phone = implode($arr_phone);
        //调用短信平台的接口发送短信
        $res = sendSmsImpl( $phone,$sendMsg,$sid,'');
        $count_sended += $sms_num;



        //延长执行时间
        resetAlarm();
        $msg = $res['info'];
        $msg = $db->escape_string($msg);
        //更新状态
        $add_try_times = ($res['info'] == 0?0:1);
        $status = $res['info'] ==0?2:3;
        if(!$db->execute("update crm_sms_record set status={$status},success_people=success_people+{$count},success_count={$sms_num},try_times=try_times+{$add_try_times},error_msg=LEFT('$msg',40),send_time=now() where rec_id={$row['rec_id']}"))
        {
            $msg = '数据库错误';
            logx("update crm_sms_record fail: status=$status,success_people=$count phones=" . print_r($arr_phone, true),$sid.'/SMS');
        }
        logx('发送成功:'.print_r('号码:'.$arr_phone.'，内容:'.$sendMsg,true),$sid.'/SMS');
        releaseDb($db);
    }

    return true;


}
function sendSmsImpl($mobiles, $message,$sid, $code) {
    $client = new  sms_client();
    $client->appKey = '999999';
    $client->appSecret = 'e063bed69948a2566e3de55250c815af';
    $client->apiMethod = 'wdt.sms.send.send';
    if(substr($mobiles,'-1')==','){
        $mobiles=substr($mobiles,0,strlen($mobiles)-1);
    }
    $params = array(
        array('sid'=>$sid,'version'=>0,'msg'=>$message,'phone'=>$mobiles,'ext'=>$code)
    );
    $p=array('sms'=>json_encode($params));
    $result = $client->execute($p);
    if($result->code==0){
        $result->msg = "发送成功";
    }
    $res['status']=$result->code;
    $res['info'] = $result->msg;
    return $res;
}

function validPhone($phone)
{
    return !empty($phone);
}

function countSMS($msg)
{
    $one_message_count = 66;
    $len = iconv_strlen($msg, 'UTF-8');
    $count = (int)($len / $one_message_count);
    if($len % $one_message_count) ++$count;
    return $count;
}
function get_balance($sid){
    $client = new  sms_client();
    $client->appKey = '999999';
    $client->appSecret = 'e063bed69948a2566e3de55250c815af';
    $client->apiMethod = 'wdt.sms.send.check';
    $params = array(
        array('sid'=>$sid,'version'=>0)
    );
    $p=array('sms'=>json_encode($params));
    $result = $client->execute($p);
    $res['status'] = $result->code;
    $balance = 0;
    $sms_num = 0;
    if(property_exists($result, 'balance')){
        $balance = $result->balance;
    }
    if(property_exists($result, 'sms_num')){
        $sms_num = $result->sms_num;
    }
    $res['info'] = array('balance'=>$balance,'sms_num'=>$sms_num);
    
    return $res;

}