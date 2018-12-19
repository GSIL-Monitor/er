<?php
function crm_sms_trade_unpay($db,$sid){
    $db->execute('BEGIN');
    $cfg_member_send_sms_limit_time = getSysCfg($db,'crm_member_send_sms_limit_time',0);
    //
    $rule_sql = "SELECT cr.template_id,cr.delay_time,cr.shop_id,cr.end_time,ss.shop_name,cst.content,cst.sign
	                 FROM cfg_sms_send_rule cr LEFT JOIN cfg_shop ss ON cr.shop_id = ss.shop_id
	                 LEFT JOIN cfg_sms_template cst ON cst.rec_id = cr.template_id
	                 WHERE  cr.event_type=4 AND cr.is_disabled =0";
    $rule_res = $db->query($rule_sql);
    if(!$rule_res)return;
    $count = 0;
    while($v = $db->fetch_array($rule_res)){
        if(empty($v['content'])){
            continue;
        }
        $template_id = $v['template_id'];
        $shop_id = $v['shop_id'];
        $delay_time = $v['delay_time'];
        $shop_name = $v['shop_name'];
        $content = $v['content'];
        $sign = $v['sign'];
        $sign = "【{$sign}】";
        $trade_begin_time = date('Y-m-d H:i:s',time()-24*60*60);//开始时间为前一天
        $trade_end_time = date('Y-m-d H:i:s',time()-$delay_time*60);//结束时间为当前时间减去延迟时间
        $order_sql = "SELECT st.trade_id,st.receiver_mobile,st.src_tids,st.buyer_nick,st.receiver_name,'' AS logistics_name, st.logistics_no,
		                         st.trade_time,st.pay_time,st.goods_amount,st.discount,st.receivable,st.post_amount,st.weight,'' AS logistics_type
	                      FROM sales_trade st FORCE INDEX(IX_sales_trade_trade_status)
	                      WHERE st.trade_status = 10 AND st.check_step=0 AND st.is_unpayment_sms=0 AND st.shop_id = $shop_id
		                  AND st.trade_time BETWEEN '{$trade_begin_time}' AND '{$trade_end_time}'
		                  AND st.trade_from<> 4 AND delivery_term = 1";
        $order_res = $db->query($order_sql);
        $order_res = $db->fetch_array($order_res);
        if(!$order_res){
            continue;
        }
        if($order_res['receiver_mobile']=''){
            $db->execute("UPDATE sales_trade SET is_unpayment_sms=2 WHERE trade_id={$order_res['trade_id']}");
            continue;
        }
        //替换模板变量,催未付款订单没有物流跟发货时间
        $message=$content;
        $message = str_replace('{客户网名}',$order_res['buyer_nick'],$message);
        $message = str_replace('{原始单号}',$order_res['src_tids'],$message);
        $message = str_replace('{客户姓名}',$order_res['receiver_name'],$message);
        $message = str_replace('{店铺名称}',$shop_name,$message);
        $message = str_replace('{物流单号}',$order_res['logistics_no'],$message);
        $message = str_replace('{物流公司}','',$message);
        $message = str_replace('{下单时间}',$order_res['trade_time'],$message);
        $message = str_replace('{发货时间}','',$message);
        $message.=$sign;

        if($message==NULL || $message==''){
            $db->execute("UPDATE sales_trade SET is_unpayment_sms=2 WHERE trade_id={$order_res['trade_id']}");
            continue;
        }
        //校验是否满足发短信条件
        if($cfg_member_send_sms_limit_time>0){
            $limit_time = date('Y-m-d H:i:s',time()-$cfg_member_send_sms_limit_time*60);
            $res = $db->query("SELECT 1 FROM crm_sms_record WHERE (status=0 or status = 1 or status = 2) AND
				(timer_time>=$limit_time AND timer_time< NOW()) AND phone_num=1 AND phones='{$order_res['receiver_mobile']}'");
            if(count($res)>0)continue;
        }
        $batch_no=$db->query("SELECT FN_SYS_NO('sms')");
        $batch_no = $batch_no[0]["fn_sys_no('sms')"];
        //插入短信批次表
        $res=$db->execute("INSERT INTO crm_sms_record(`status`,sms_type,send_type,operator_id,phones,phone_num,message,timer_time,
				          batch_no,pre_count,try_times,created)
			              VALUES(0,1,1,1,'{$order_res['receiver_mobile']}',1,'{$content}',NOW(),'{$batch_no}',1,0,NOW())");
        //更新sales_trade 中 是否催付款短信字段
        $res1=$db->execute("UPDATE sales_trade SET is_unpayment_sms=1 WHERE trade_id={$order_res['trade_id']}");
        if(!$res || !$res1){
            $db->execute('ROLLBACK');
            logx('统计催未付款订单时插入crm_sms_record失败 或 更新sales_trade 失败',$sid.'/SMS');
            return false;
        }
        $count ++;
    }

    $db->execute('COMMIT');
    logx('统计催未付款订单成功'.$count,$sid.'/SMS');
}