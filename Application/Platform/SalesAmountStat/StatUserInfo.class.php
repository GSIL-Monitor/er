<?php

function userTradeStastics($db,$sid)
{
    $v_begin = getSysCfg($db,'last_user_trade_stat',0);
    if(!$v_begin)
    {
        $date =  date_create(date("Y-m-d"));
        $start = date_format(date_add($date,date_interval_create_from_date_string('-1 days')),'Y-m-d G:i:s');
    }else{
        $start = $v_begin;
    }
    $end = date('Y-m-d',time());
    $end = $end.' 00:00:00';
    if($start == $end)
    {
        logx('当日单量统计结束。','UserTrade');
        return TASK_OK;
    }
    logx($sid.'start trade_stastics start:'.$start.'end:'.$end,'UserTrade');

    $sql = "SELECT sales_date,sum(new_trades) new_trades,sum(new_trades_amount) new_trades_amount,sum(check_trades) check_trades,
                sum(check_trades_amount) check_trades_amount,sum(send_trades) send_trades,sum(send_trades_amount) send_trades_amount,
                sum(send_goods_cost) send_goods_cost,sum(send_trade_profit) send_trade_profit,sum(send_unknown_goods_amount) send_unknown_goods_amount,
                sum(post_amount) post_amount,sum(post_cost) post_cost,sum(commission) commission,sum(other_cost) other_cost,sum(package_cost) package_cost,
                sum(sales_drawback) sales_drawback FROM stat_daily_sales_amount WHERE sales_date>='{$start}' AND sales_date<'{$end}' GROUP BY sales_date";
    $trade_res = $db->query($sql);
    if (!$trade_res) {
        releaseDb($db);
        logx("query trade_res failed!", "UserTrade");
        return TASK_OK;
    }
    $main_db = get_trade_main_db();
    if(!$main_db)
    {
        logx('get main db failed',$sid);
        releaseDb($db);
        return TASK_OK;
    }
    while($row = $db->fetch_array($trade_res))
    {
        $row['merchant_no'] = $sid;
        putResultToDb($row,$main_db);
    }
    logx('卖家单量统计成功','UserTrade');
    setSysCfg($db,'last_user_trade_stat',$end);
    $db->free_result($trade_res);
    releaseDb($db);
    releaseDb($main_db);
    return TASK_OK;
}

function putResultToDb($t,$main_db)
{
    $sales_date = $t['sales_date'];
    $new_trades = $t['new_trades'];
    $new_trades_amount = $t['new_trades_amount'];
    $check_trades = $t['check_trades'];
    $check_trades_amount = $t['check_trades_amount'];
    $send_trades = $t['send_trades'];
    $send_trades_amount = $t['send_trades_amount'];
    $send_goods_cost = $t['send_goods_cost'];
    $send_trade_profit = $t['send_trade_profit'];
    $send_unknown_goods_amount = $t['send_unknown_goods_amount'];
    $post_amount = $t['post_amount'];
    $post_cost = $t['post_cost'];
    $commission = $t['commission'];
    $other_cost = $t['other_cost'];
    $package_cost = $t['package_cost'];
    $sales_drawback = $t['sales_drawback'];
    $merchant_no = $t['merchant_no'];
    $sql = "INSERT IGNORE INTO user_daily_trade_amount VALUES(NULL,'{$sales_date}','{$merchant_no}',' ','{$new_trades}','{$new_trades_amount}','{$check_trades}','{$check_trades_amount}','{$send_trades}',
                '{$send_trades_amount}','{$send_goods_cost}','{$send_trade_profit}','{$send_unknown_goods_amount}','{$post_amount}','{$post_cost}','{$commission}','{$other_cost}','{$package_cost}','{$sales_drawback}')";
    $res = $main_db->execute($sql);
    if(!$res)
    {
        logx($merchant_no.'put result to db fail,sql:'.print_r($sql,true));
        return TASK_OK;
    }
    logx($merchant_no.' trade_stastics successful time:'.print_r($sales_date,true),'UserTrade');
    return TASK_OK;
}

function UserUseStat($db,$sid)
{
    $v_begin = getSysCfg($db,'last_user_use_stat',0);
    if(!$v_begin)
    {
        $date =  date_create(date("Y-m-d"));
        $start = date_format(date_add($date,date_interval_create_from_date_string('-1 days')),'Y-m-d G:i:s');
    }else{
        $start = $v_begin;
    }
    $end = date('Y-m-d',time());
    $end = $end.' 00:00:00';
    if($start == $end)
    {
        logx('当日操作量统计结束。','UserTrade');
        return TASK_OK;
    }
    logx($sid.'start UserUseStat start:'.$start.'end:'.$end,'UserTrade');

    $sql = "SELECT * FROM stat_use WHERE `sid`='{$sid}' AND `date`>='{$start}' AND `date`<'{$end}'";
    $use_res = $db->query($sql);
    if (!$use_res) {
        releaseDb($db);
        logx("query use_res failed!", "UserTrade");
        return TASK_OK;
    }
    $main_db = get_trade_main_db();
    if(!$main_db)
    {
        logx('get main db failed',$sid);
        releaseDb($db);
        return TASK_OK;
    }
    while($row = $db->fetch_array($use_res))
    {

        $res = $main_db->execute("INSERT IGNORE INTO stat_use (`sid`,`module`,`controller`,`action`,`search_data`,`operator_id`,`date`,`prop1`,`prop2`,`prop3`,`num`,`modified`,`created`)
                          VALUES ('{$row['sid']}','{$row['module']}','{$row['controller']}','{$row['action']}','{$row['search_data']}','{$row['operator_id']}','{$row['date']}','{$row['prop1']}','{$row['prop2']}','{$row['prop3']}','{$row['num']}','{$row['modified']}','{$row['created']}')");
        if(!$res)
        {
            logx($row['sid'].'put result to db fail');
            releaseDb($main_db);
            return TASK_OK;
        }
        logx($row['sid'].' use_stastics successful time:'.print_r($row['date'],true),'UserTrade');
    }
    setSysCfg($db,'last_user_use_stat',$end);
    $db->free_result($use_res);
    releaseDb($db);
    releaseDb($main_db);
    logx('卖家操作量统计成功','UserTrade');
    return TASK_OK;
}
