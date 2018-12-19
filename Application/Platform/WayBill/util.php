<?php
/**
 * @param array $shop
 */
function waybill_success_handle($logistics_info,$packageNos,&$result)
{
    if(!empty($packageNos)){
        $res_handle = D('Stock/SalesMultiLogistics')->dealWaybillGetSuccess($logistics_info,$packageNos,$result);
    }else{
        $res_handle = D('Stock/StockLogisticsNo')->dealWaybillGetSuccess($logistics_info,$result);
    }
}
function waybill_error_handle($logistics_info,&$result)
{
    $res_handle = D('Stock/StockLogisticsNo')->dealWaybillGetFail($logistics_info,$result);
}
function waybill_error_print_handle($sid,&$db,$order_fail)
{
	$log = '打印异常:';
	foreach($order_fail as $key=>$value)
		$log = $log.$key.':'.$value.' ';
	// 		logx($log,$sid);
	\Think\Log::write($log);
}
function waybill_cancel_handler(&$result)
{
    try {
        $operator = get_operator_id();
        $insert_sales_log_data = array();
        foreach (@$result['cancel']['fail'] as $fk => $fv)
        {
            //更新日志记录
            $insert_sales_log_data[] = array(
                'type'          => '155',
                'trade_id'      => empty($fv['trade_id'])?'':$fv['trade_id'],
                'operator_id'   =>$operator,
                'message'       =>'云栈电子面单取消失败：'.$fv['msg'],
            );
        }
        foreach (@$result['cancel']['success'] as $sk => $sv)
        {
            $insert_sales_log_data[] = array(
                'type'          => '165',
                'trade_id'      => empty($sv['trade_id'])?'':$sv['trade_id'],
                'operator_id'   =>$operator,
                'message'       => isset($sv['platform_id'])?'成功取消京东电子面单：'.$sv['logistics_no']:'成功取消云栈电子面单：'.$sv['logistics_no'],
            );
        
        }
        $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
    } catch (\PDOException $e) {
        $msg = $e->getMessage();
        \Think\Log::write('waybill_cancel_handler-记录日志失败：'.print_r($result['cancel'],true));
    }
    
}


