<?php
//数据处理层
require_once(TOP_SDK_DIR . '/sf/sfClient.php');

function sf_get_waybill(&$db,$logistics_info,$stockout_ids,&$result,$status = 55)
{
    $result = array(
        'status' => 0,
        'msg'  =>"success",
        'data' =>array()
    );
    if($stockout_ids == '')
        return true;
    $logistics_info = (array)$logistics_info;
    $sid = $logistics_info['sid'];
    $sf = new sfClient();
    if(!$sf)
    {
        return false;
    }
    $logistics_auto = new StdClass();
    $logistics_auto->customer_code=$logistics_info['customer_code'];
    $logistics_auto->customer_pwd=$logistics_info['customer_pwd'];
    $logistics_auto->month_card=$logistics_info['banded_type'];
    $logistics_auto->pay_type=$logistics_info['pay_type'];
    $logistics_auto->type_sf=$logistics_info['type_sf'];
    $logistics_auto->insures_type=$logistics_info['insures_type'];
    $logistics_auto->insure_amount=$logistics_info['insure_amount'];

    if($logistics_auto->type_sf == 0)
        $logistic_type = '1';
    else if($logistics_auto->type_sf == 1)
        $logistic_type = '2';
    else if($logistics_auto->type_sf == 2)
        $logistic_type = '3';
    else if($logistics_auto->type_sf == 3)
        $logistic_type = '7';
    else if($logistics_auto->type_sf == 4)
        $logistic_type = '37';
    else if($logistics_auto->type_sf == 5)
        $logistic_type = '38';
    else if($logistics_auto->type_sf == 6)
        $logistic_type = '6';
    else if($logistics_auto->type_sf == 7)
        $logistic_type = '5';
    else if($logistics_auto->type_sf == 8)
        $logistic_type = '14';
    else if($logistics_auto->type_sf == 9)
        $logistic_type = '102';
    else if($logistics_auto->type_sf == 10)
        $logistic_type = '111';
    else
        $logistic_type = '1';

    if($logistics_auto->pay_type == 0)
        $pay_type='1';
    else if($logistics_auto->pay_type == 1)
        $pay_type='2';
    else if($logistics_auto->pay_type == 2)
        $pay_type='3';
    else
        $pay_type='1';

    $order_info = $db->query("SELECT st.cod_amount,st.delivery_term,so.stockout_id,so.receiver_area,so.package_count,so.src_order_no,
										 so.receiver_name, so.receiver_address, so.receiver_mobile, so.receiver_telno, so.receiver_province, 
										 so.receiver_city,so.receiver_district, so.receiver_zip,so.calc_weight, st.receivable,
										 so.stockout_no,sw.contact AS sender_name, sw.address AS sender_address, sw.telno AS sender_telno, 
										 sw.mobile AS sender_mobile, sw.zip AS sender_zip,sw.province AS sender_province,sw.city AS sender_city,
										 sw.district AS sender_district,
										 sod.goods_name,sod.num,sod.price,cs.shop_name
								 FROM stockout_order so
								 LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id
								 LEFT JOIN sales_trade st ON (so.src_order_id = st.trade_id)
								 LEFT JOIN cfg_warehouse sw ON sw.warehouse_id = so.warehouse_id
								 LEFT JOIN cfg_shop cs ON cs.shop_id = st.shop_id
								 WHERE so.stockout_id IN ({$stockout_ids}) AND so.status = {$status} AND so.logistics_no = '' ");

    if(!$order_info)
    {
        $result['status'] = 1;
        $result['msg'] = '查询订单信息出错';
        return false;
    }
    if($order_info->num_rows<1)
    {
        $result['status'] = 1;
        $result['msg'] = '请在单据打印界面查看出库单状态是“获取面单号”状态';
        return false;
    }
    $orderInfo = array();
    while($row = $db->fetch_array($order_info))
    {
        $stockout_id = $row['stockout_id'];
        $orderInfo["$stockout_id"]['stockout_id'] = $row ['stockout_id'];
        $orderInfo["$stockout_id"]['stockout_no'] = $row ['stockout_no'];
        //$orderInfo["$stockout_id"]['j_contact'] = $row['sender_name'];
        $orderInfo["$stockout_id"]['j_contact'] = $row['shop_name'];
        $orderInfo["$stockout_id"]['j_tel'] = $row['sender_telno'];
        $orderInfo["$stockout_id"]['j_mobile'] = $row['sender_mobile'];
        $orderInfo["$stockout_id"]['j_address'] = $row['sender_address'];
        $orderInfo["$stockout_id"]['d_contact'] = $row['receiver_name'];
        $orderInfo["$stockout_id"]['d_tel'] = $row['receiver_telno'] == ''?$row['receiver_mobile']:$row['receiver_telno'];
        $orderInfo["$stockout_id"]['d_mobile'] = $row['receiver_mobile'];
        $orderInfo["$stockout_id"]['d_address'] = $row['receiver_address'];
        $orderInfo["$stockout_id"]['j_province'] = $row['sender_province'];
        $orderInfo["$stockout_id"]['j_city'] = $row['sender_city'];
        splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
        $orderInfo["$stockout_id"]['d_province'] = $receiver_province;
        $orderInfo["$stockout_id"]['d_city'] = $receiver_city;
        $orderInfo["$stockout_id"]['d_county'] = $receiver_district;
        $orderInfo["$stockout_id"]['calc_weight'] = $row['calc_weight'];
        $orderInfo["$stockout_id"]['cargo_total_weight'] = $row['calc_weight'];
        $orderInfo["$stockout_id"]['items'][] = array(
            'goods_name' => $row['goods_name'],
            'num' => $row['num'],
            'price' => $row['price']
        );
        $orderInfo["$stockout_id"]['delivery_term'] = $row['delivery_term'];
        $orderInfo["$stockout_id"]['cod_amount'] = $row['cod_amount'];
        $orderInfo["$stockout_id"]['receivable'] = $row['receivable'];
        $orderInfo["$stockout_id"]['package_count'] = $row['package_count'];
        $orderInfo["$stockout_id"]['src_order_no'] = $row['src_order_no'];
    }
    $success_list = array();
    $fail_list = array();
    foreach($orderInfo AS $row)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="OrderService" lang="zh-CN"></Request>';
        $dom = new SimpleXMLElement($xml);
        $dom->Head = $logistics_auto->customer_code.','.$logistics_auto->customer_pwd;
        $body = $dom->addChild('Body');
        $order = $body->addChild('Order');
        $order_no = $row ['stockout_no'].time();
        $order->addAttribute('orderid',$order_no);
        $order->addAttribute('express_type',$logistic_type);
        $order->addAttribute('j_contact',$row['j_contact']);
        $order->addAttribute('j_tel',$row['j_tel']);
        $order->addAttribute('j_mobile',$row['j_mobile']);
        $order->addAttribute('j_address',$row['j_address']);
        $order->addAttribute('d_company','');
        $order->addAttribute('d_contact',$row['d_contact']);//escape_xml_string($row['d_contact'])
        $order->addAttribute('d_tel',$row['d_tel']);
        $order->addAttribute('d_mobile',$row['d_mobile']);
        $order->addAttribute('d_address',$row['d_address']);//escape_xml_string($row['d_address'])
        $order->addAttribute('parcel_quantity',$row['package_count']);
        $order->addAttribute('pay_method',$pay_type);
        $order->addAttribute('j_province',$row['j_province']);
        $order->addAttribute('j_city',$row['j_city']);
        $order->addAttribute('d_province',$row['d_province']);
        $order->addAttribute('d_city',$row['d_city']);
        $order->addAttribute('d_county',$row['d_county']);
        $order->addAttribute('is_gen_bill_no',1);
        //$order->addAttribute('need_return_tracking_no',1);
        if($row['calc_weight']>0)
        {
            $order->addAttribute('cargo_total_weight',$row['calc_weight']);
        }
        $OrderOption = $order->addChild('OrderOption');
        $OrderOption->addAttribute('custid',$logistics_auto->month_card);
        //$OrderOption->addAttribute('cargo','goods');
        $OrderOption->addAttribute('remark','');
        //判断是否需要传递商品
        if(isset($logistics_auto->transmit_goods) && intval($logistics_auto->transmit_goods)==1)
        {
            $items = $row['items'];
            foreach($items AS $item)
            {
                $Cargo = $order->addChild('Cargo');
                $Cargo->addAttribute('name',$item['goods_name']);
                $Cargo->addAttribute('count',$item['num']);
                $Cargo->addAttribute('amount',$item['price']);
                $Cargo->addAttribute('unit','');
                //$Cargo->addAttribute('weight','');
            }
        }
        else
        {
            //初始设置cargo为固定值goods
            //$OrderOption->addAttribute('cargo','goods');

            //以下是顺丰旧接口拼接cargo xml格式
            /*$items = $row['items'];
            $cargo_str = '';
            foreach($items AS $item)
            {
                $cargo_str .= $item['goods_name'].',';
            }
            $cargo_str = substr($cargo_str,0,-1);
            $OrderOption->addAttribute('cargo',$cargo_str);*/

            //顺丰新接口拼接cargo xml格式
            $items = $row['items'];
            foreach($items AS $item)
            {
                $Cargo = $order->addChild('Cargo');
                $Cargo->addAttribute('name',$item['goods_name']);
                $Cargo->addAttribute('count',$item['num']);
                $Cargo->addAttribute('amount',$item['price']);
                $Cargo->addAttribute('unit','');
                $Cargo->addAttribute('weight','');
            }
        }

        $printInfo = '';
        if($row['delivery_term'] == 2)
        {
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','COD');
            $AddedService->addAttribute('value',$row['cod_amount']);
            $AddedService->addAttribute('value1',$logistics_auto->month_card);
        }

        //以货品价格保价
        if(isset($logistics_auto->insures_type) && intval($logistics_auto->insures_type)==1)
        {
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','INSURE');
            $AddedService->addAttribute('value',$row['receivable']);
            $printInfo = urldecode(json_encode(array('insure_amount'=>$row['receivable'],'cod_amount'=>$row['cod_amount'])));
        }
        else if(isset($logistics_auto->insures_type) && intval($logistics_auto->insures_type)==2)
        {
            //以固定价格保价
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','INSURE');
            $AddedService->addAttribute('value',$logistics_auto->insure_amount);
            //以固定价格保价
            $printInfo = urldecode(json_encode(array('insure_amount'=>$logistics_auto->insure_amount,'cod_amount'=>$row['cod_amount'])));
        }
        else
        {
            //不保价
            $printInfo = urldecode(json_encode(array('insure_amount'=>0,'cod_amount'=>$row['cod_amount'])));
        }
        $order_xml = $dom->asXML();
        \Think\Log::write("sf_get_waybill--".$order_xml,$sid);
        $retval = $sf->get_waybill($order_xml);
        \Think\Log::write("sf_get_waybill--".print_r($retval,true),$sid);
        if($retval == false)
        {
            $db->query("UPDATE stockout_order SET status = GREATEST(55,status),error_info = '顺丰接口返回为空' WHERE stockout_id = {$row['stockout_id']}");
            continue;
        }
        if($retval['Head'] == 'OK')
        {
            if($retval['Body']['OrderResponse']['filter_result'] != 2)
            {
                if($retval['Body']['OrderResponse']['filter_result'] == 1) {
                    $error_msg = '待人工确认';
                    $result['status'] = 1;
                    $result['msg'] = $error_msg;
                }
                else{
                    $error_msg = '地址不到达';
                    $result['status'] = 1;
                    $result['msg'] = $error_msg;
                }
                $db->query("UPDATE stockout_order SET status = GREATEST(55,status),error_info = '{$error_msg}' WHERE stockout_id = {$row['stockout_id']} and status>=54");
                continue;
            }

            $mapping_mark = isset($retval['Body']['OrderResponse']['mapping_mark'])? $retval['Body']['OrderResponse']['mapping_mark']:"";
            //写道这里 还没改变价格
//            $db->query("replace into stock_logistics_print(stockout_id,logistics_id,logistics_no,position_no,original_code,destination_code,print_info,created)
//							values({$row['stockout_id']},{$logistics_info['logistics_id']},'{$retval['Body']['OrderResponse']['mailno']}','{$mapping_mark}','{$retval['Body']['OrderResponse']['origincode']}','{$retval['Body']['OrderResponse']['destcode']}','{$printInfo}',NOW())");

            // 提取多物流单号
            $logisticsArr = explode(',',$retval['Body']['OrderResponse']['mailno']);
            //判断下是否已经获取到单号了
            $has_logistics_no = $db->query_result_single("select logistics_no from stockout_order where stockout_id={$row['stockout_id']}");
            $destcode = empty($retval['Body']['OrderResponse']['destcode'])?'':$retval['Body']['OrderResponse']['destcode'];
            if(!isset($has_logistics_no) || $has_logistics_no =='')
            {
                $db->query("START TRANSACTION");
                $db->query("update stockout_order set status = GREATEST(55,status),logistics_no='$logisticsArr[0]',receiver_dtb='{$destcode}',logistics_id={$logistics_info['logistics_id']} where stockout_id={$row['stockout_id']} and status>=54");

                $order_type = $db->query_result("select src_order_type,src_order_id from stockout_order where stockout_id = {$row['stockout_id']}");
                if($order_type && $order_type['src_order_type'] == 1)
                {
                    $db->query("update sales_trade set logistics_no='$logisticsArr[0]',logistics_id={$logistics_info['logistics_id']} where trade_id={$order_type['src_order_id']}");
                }

                //插入出库单日志
                $message = "顺丰热敏获取物流单号:".$retval['Body']['OrderResponse']['mailno'];
                $db->query("insert into sales_trade_log(`type`,trade_id,operator_id,message)
							values(155,{$order_type['src_order_id']},0,'{$message}')");
                //添加多物流
                if(count($logisticsArr)>1){
                    for($i=0;$i<$row['package_count']-1;$i++){
                        $currentPackage = $i+2;  //主物流单号是第一包,所以多包裹从第二包开始。
                        $insert_data = array(
                            'logistics_no' => $logisticsArr[$i+1].'-'.$currentPackage.'-'.$row['package_count'],
                            'stockout_id' => $row['stockout_id'],
                            'logistics_id' => $logistics_info['logistics_id'],
                            'trade_no' => $row['src_order_no']
                        );
                        $res = D('SalesMultiLogistics')->addLogistics($insert_data);
                    }
                }
                $db->query("COMMIT");
            }
            //插入物流单号
            $db->query("insert into stock_logistics_no(logistics_id,logistics_type,logistics_no,stockout_id,status,sender_province,sender_city,sender_address,receiver_dtb,waybill_info,src_tids,created)
				values({$logistics_info['logistics_id']},{$logistics_info['logistics_type']},'$logisticsArr[0]',{$row['stockout_id']},1,'{$row['j_province']}','{$row['j_city']}','{$row['j_address']}','{$retval['Body']['OrderResponse']['destcode']}','{$printInfo}','{$row['src_order_no']}',NOW()) on duplicate key update status=1,stockout_id={$row['stockout_id']}");
            $db->query("UPDATE stockout_order SET reserve='{$printInfo}' WHERE stockout_id='{$row['stockout_id']}'");
            $success_list["{$row['stockout_id']}"] = array(
                'logistics_no' => $logisticsArr[0],
                'receiver_dtb' => $retval['Body']['OrderResponse']['destcode'],
                'waybill_info' => $printInfo
            );
        }
        else
        {
            $error_msg = $retval['ERROR'];
            $result['status'] = 1;
            $result['msg'] = $error_msg;
            $fail_list = $retval['ERROR'];
            $db->query("UPDATE stockout_order SET status = GREATEST(55,status),error_info = '{$retval['ERROR']}' WHERE stockout_id = {$row['stockout_id']} and status>=54");
        }
    }
    if($result['status'] != 0){
        return false;
    }
    else{
        $result['data'] = array(
            'success' => $success_list,
            'fail' =>$fail_list
        );
        return true;
    }
}

function sort_route_sf(&$status,&$msg,$logistics_no,$customerid,$customerpwd,$sid)
{
    $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="RouteService" lang="zh-CN"></Request>';
    $dom = new SimpleXMLElement($xml);
    $dom->Head = $customerid.','.$customerpwd;
    $body = $dom->addChild('Body');
    $routerequest = $body = $dom->addChild('RouteRequest');
    $routerequest->addAttribute('tracking_type',1);
    $routerequest->addAttribute('method_type',1);
    $routerequest->addAttribute('tracking_number',$logistics_no);
    $xml = $dom->asXML();

    logx($xml,$sid);
    $sf = new sfClient();
    $retval = $sf->get_waybill($xml);
    logx(print_r($retval,true),$sid);

    if($retval['Head'] == 'OK')
    {
        $status = 0;
        if(isset($retval['Body']))
        {
            $result['PostID'] = $retval['Body']['RouteResponse']['mailno'];
            $result['LogisticsName'] = '顺丰';
            $result['Status'] = '';
            foreach($retval['Body']['RouteResponse']['Route'] as $value)
            {
                $trace_list['status_time'] = $value['accept_time'];
                $trace_list['status_desc'] = $value['accept_address'];
                $result['trace_list'][] = $trace_list;
            }
        }
        else
        {
            $result['PostID'] = $logistics_no;
            $result['LogisticsName'] = '顺丰';
            $result['Status'] = '';
            $trace_list['status_time'] = '';
            $trace_list['status_desc'] = '顺丰还没收件';
            $result['trace_list'][] = $trace_list;
        }

        $msg = json_encode($result);
    }
    else
    {
        $status = 1;
        $msg = $retval['ERROR'];
    }
}

function deal_sales_back_sf(&$status,&$msg,&$db,$orderinfo,$ordertype)
{
    $sid = $orderinfo['Sid'];

    if($orderinfo['LogisticType'] == 1)
        $logistictype = '1';
    else if($orderinfo['LogisticType'] == 4)
        $logistictype = '2';
    else if($orderinfo['LogisticType'] == 5)
        $logistictype = '3';
    else if($orderinfo['LogisticType'] == 8)
        $logistictype = '7';
    else
        $logistictype = '1';

    if(isset($orderinfo['PayType']))
    {
        if($orderinfo['PayType'] == 0)
            $PayType='1';
        else if($orderinfo['PayType'] == 1)
            $PayType='3';
    }
    else
    {
        $PayType='1';
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="OrderReverseService" lang="zh-CN"></Request>';
    $dom = new SimpleXMLElement($xml);
    $dom->Head = $orderinfo['CustomerID'].','.$orderinfo['CustomerPwd'];
    $body = $dom->addChild('Body');
    $order = $body->addChild('Order');

    $tradeno = $db->query_result_single("SELECT FN_GENERATE_SYS_NO('g_trade_extend','LogisticNO')");
    $order->addAttribute('orderid',$tradeno);
    $order->addAttribute('express_type',$logistictype);
    $order->addAttribute('j_contact',$orderinfo['SenderName']);
    splitPhones($orderinfo['SenderTel'], $phone, $mobile);
    $order->addAttribute('j_tel',$mobile);
    $order->addAttribute('j_address',$orderinfo['SenderAdr']);
    $order->addAttribute('d_company','');
    $order->addAttribute('d_contact',$orderinfo['ReceiverName']);
    splitPhones($orderinfo['ReceiverTel'], $phone, $mobile);
    $order->addAttribute('d_tel',$mobile);
    $order->addAttribute('d_address',escape_xml_string($orderinfo['ReceiverAdr']));
    $order->addAttribute('parcel_quantity','1');
    $order->addAttribute('pay_method',$PayType);
    $order->addAttribute('j_province',$orderinfo['SenderProvince']);
    $order->addAttribute('j_city',$orderinfo['SenderCity']);
    $order->addAttribute('d_province',$orderinfo['ReceiverProvince']);
    $order->addAttribute('d_city',$orderinfo['ReceiverCity']);
    $order->addAttribute('custid',$orderinfo['MonthCard']);
    if(isset($orderinfo['Weight']) && $orderinfo['Weight'] > 0)
    {
        $order->addAttribute('cargo_total_weight',$orderinfo['Weight']);
    }

    if(isset($orderinfo['goodsinfo']))
    {
        foreach($orderinfo['goodsinfo'] as $value)
        {
            $Cargo = $order->addChild('Cargo');
            $Cargo->addAttribute('name',$value['GoodsName']);
            $Cargo->addAttribute('serl',$value['GoodsCount']);
            $Cargo->addAttribute('unit','个');
        }
    }
    else
    {
        $Cargo = $order->addChild('Cargo');
        $Cargo->addAttribute('name','-');
        $Cargo->addAttribute('serl','-');
        $Cargo->addAttribute('unit','个');
    }

    /*
    if($value['ChargeType'] == 4)
    {
        $AddedService = $OrderOption->addChild('AddedService');
        $AddedService->addAttribute('name','COD');
        $AddedService->addAttribute('value',$value['RcvTotal']-$value['DrawBackValue']);
        $AddedService->addAttribute('value1',$logisticdetail['CustomerID']);
    }
    */

    $tradexml = $dom->asXML();
    $pos = strpos($tradexml,'?>');
    $tradexml = substr($tradexml,$pos+2);
    logx($tradexml,$sid);
    $sf = new sfClient();
    $retval = $sf->deliverTrade($tradexml);
    logx(print_r($retval,true),$sid);
    if($retval == false)
    {
        logx(" sf deal_sales_back failed",$sid);
        $status = 1;
        $msg = '顺丰接口返回失败';
        return false;
    }
    if($retval['Head'] == 'OK')
    {
        if($retval['Body']['OrderReverseResponse']['filter_result'] != 2)
        {
            logx('DeliverShunFengTrade filter_result != 2 !',$sid);
            $status = 1;
            $msg = '地址不到达';
            return false;
        }
        if($ordertype == 2)
        {
            $db->query("update g_sellback_list set RcvPostID='{$retval['Body']['OrderReverseResponse']['mailno']}' where SellbackID={$orderinfo['SellbackID']}");
        }
        else if($ordertype == 1)
        {
            $db->query("update g_repair_list set FromPostID='{$retval['Body']['OrderReverseResponse']['mailno']}' where RepairID={$orderinfo['RepairID']}");
        }
        logx(" sf deal_sales_back success",$sid);
        return true;
    }
    else
    {
        logx(" sf deal_sales_back failed",$sid);
        $status = 1;
        $msg = $retval['ERROR'];
        return false;
    }
}

function repair_get_waybill_sf(&$status,&$msg,&$db,$orderinfo,$ordertype)
{
    $sid = $orderinfo['Sid'];
    $sf = new sfClient();
    if(!$sf)
    {
        logx('get sfClient failed!',$sid);
        $status = 1;
        $msg = '未知错误';
        return false;
    }
    $parse_logistics_info = array();
    if(isset($orderinfo['app_key']) && !empty($orderinfo['app_key']) )
    {
        $parse_logistics_info = json_decode($orderinfo['app_key'],true);
        if (!isset($parse_logistics_info['customer_pwd']) || !isset($parse_logistics_info['customer_code']) || !isset($parse_logistics_info['pay_type']) || !isset($parse_logistics_info['month_card']) )
        {
            // 授权信息无效
            logx('get authority_info failed!'.$orderinfo['app_key'],$sid);
            $status = 1;
            $msg = '物流公司授权信息异常';
            sales_repair_error_log($db,$orderinfo['repair_id'],"物流公司授权信息异常");
            return false;
        }
    }
    else
    {
        // 物流公司未授权
        logx('not authority!'.json_encode($orderinfo['app_key']),$sid);
        $status = 1;
        $msg = '物流公司未授权';
        sales_repair_error_log($db,$orderinfo['repair_id'],"物流公司未授权");
        return false;
    }
    $logistics_auto = $parse_logistics_info;
    if($logistics_auto['type_sf'] == 0)
        $logistic_type = '1';
    else if($logistics_auto['type_sf'] == 1)
        $logistic_type = '2';
    else if($logistics_auto['type_sf'] == 2)
        $logistic_type = '3';
    else if($logistics_auto['type_sf'] == 3)
        $logistic_type = '7';
    else if($logistics_auto['type_sf'] == 4)
        $logistic_type = '37';
    else if($logistics_auto['type_sf'] == 5)
        $logistic_type = '38';
    else
        $logistic_type = '1';

    if($logistics_auto['pay_type'] == 0)
        $pay_type='1';
    else if($logistics_auto['pay_type'] == 1)
        $pay_type='2';
    else if($logistics_auto['pay_type'] == 2)
        $pay_type='3';
    else
        $pay_type='1';

    $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="OrderReverseService" lang="zh-CN"></Request>';
    $dom = new SimpleXMLElement($xml);
    $dom->Head = $parse_logistics_info['customer_code'].','.$parse_logistics_info['customer_pwd'];
    $body = $dom->addChild('Body');
    $order = $body->addChild('Order');
    $repair_no = $orderinfo['repair_no'].date('His',time()).rand(0,9);
    $order->addAttribute('orderid',$repair_no);
    $order->addAttribute('express_type',$logistic_type);
    $order->addAttribute('j_contact',$orderinfo['SenderName']);
    $order->addAttribute('j_tel',$orderinfo['SenderTel']);
    $order->addAttribute('j_address',$orderinfo['SenderAdr']);
    $order->addAttribute('d_company','');
    $order->addAttribute('d_contact',$orderinfo['ReceiverName']);
    $order->addAttribute('d_tel',$orderinfo['ReceiverTel']);
    $order->addAttribute('d_address',escape_xml_string($orderinfo['ReceiverAdr']));
    $order->addAttribute('parcel_quantity','1');
    $order->addAttribute('pay_method',$pay_type);
    $order->addAttribute('j_province',$orderinfo['SenderProvince']);
    $order->addAttribute('j_city',$orderinfo['SenderCity']);
    $order->addAttribute('d_province',$orderinfo['ReceiverProvince']);
    $order->addAttribute('d_city',$orderinfo['ReceiverCity']);
    $order->addAttribute('custid',$parse_logistics_info['month_card']);
    if(isset($orderinfo['Weight']) && $orderinfo['Weight'] > 0)
    {
        $order->addAttribute('cargo_total_weight',$orderinfo['Weight']);
    }

    if(isset($orderinfo['goodsinfo']))
    {
        foreach($orderinfo['goodsinfo'] as $value)
        {
            $Cargo = $order->addChild('Cargo');
            $Cargo->addAttribute('name',$value['GoodsName']);
            $Cargo->addAttribute('serl',$value['GoodsCount']);
            $Cargo->addAttribute('unit','个');
        }
    }
    else
    {
        $Cargo = $order->addChild('Cargo');
        $Cargo->addAttribute('name','-');
        $Cargo->addAttribute('serl','-');
        $Cargo->addAttribute('unit','个');
    }


    $tradexml = $dom->asXML();
// 后续追加的内容
    /*$pos = strpos($tradexml,'?>');
    $tradexml = substr($tradexml,$pos+2);
    */
    logx($tradexml,$sid);
    $retval = $sf->get_waybill($tradexml);
    logx(print_r($retval,true),$sid);
    if($retval == false)
    {
        logx("repair_get_waybill_sf  failed",$sid);
        $status = 1;
        $msg = '顺丰接口返回错误';
        sales_repair_error_log($db,$orderinfo['repair_id'],"顺丰接口返回错误");
        return false;
    }
    if($retval['Head'] == 'OK')
    {
        if($retval['Body']['OrderReverseResponse']['filter_result'] == 1)
        {
            logx('repair_get_waybill_sf filter_result == 1 待人工筛单',$sid);
            $status = 1;
            $db->execute("update sales_repair set push_no='{$repair_no}',repair_status=25,error_msg='待人工筛选',sf_manual_type=1 where repair_id={$orderinfo['repair_id']}");
            $db->execute("insert into sales_repair_log(`repair_id`,`operator_type`,`operator_id`,`detail`)"
                ."values({$orderinfo['repair_id']},171,1,'获取物流单号ERROR:待人工筛单'))");
            $msg = '待人工筛单';
            return false;
        }
        if($retval['Body']['OrderReverseResponse']['filter_result'] == 3)
        {
            logx('repair_get_waybill_sf filter_result == 3 地址不可达',$sid);
            $db->execute("update sales_repair set push_no='{$repair_no}',repair_status=27,error_msg='地址不到达',sf_manual_type=2 where repair_id={$orderinfo['repair_id']}");
            $db->execute("insert into sales_repair_log(`repair_id`,`operator_type`,`operator_id`,`detail`)"
                ."values({$orderinfo['repair_id']},171,1,'获取物流单号ERROR:地址不可达'))");
            $status = 1;
            $msg = '地址不可达';
            return false;
        }
        if(!$db->execute("BEGIN"))
        {
            logx('开启事务失败',$sid);
            $status = 1;
            $msg = '开启事务失败';
            sales_repair_error_log($db,$orderinfo['repair_id'],"开启事务失败");
            return false;
        }
        if(!$db->execute("replace into stock_logistics_print(stockout_id,type,original_code,destination_code) values({$orderinfo['repair_id']},1,'{$retval['Body']['OrderReverseResponse']['filter_result']}','{$retval['Body']['OrderReverseResponse']['mailno']}')"))
        {
            logx('向打印信息表添加记录失败',$sid);
            $status = 1;
            $msg = '向打印信息表添加记录失败';
            sales_repair_error_log($db,$orderinfo['repair_id'],"向打印信息表添加记录失败");
            $db->execute('ROLLBACK');
            return false;
        }
        if(!$db->execute("update sales_repair set from_logistics_no='{$retval['Body']['OrderReverseResponse']['mailno']}',repair_status=30,error_msg='',sf_manual_type=0 where repair_id={$orderinfo['repair_id']}"))
        {
            logx('更新保修单失败',$sid);
            $status = 1;
            $msg = '更新保修单失败';
            sales_repair_error_log($db,$orderinfo['repair_id'],"更新保修单失败");
            $db->execute('ROLLBACK');
            return false;
        }
        $log_str = "获取顺丰热敏物流单号成功".$retval['Body']['OrderReverseResponse']['mailno'];
        if(!$db->execute("insert into sales_repair_log(`repair_id`,`operator_type`,`operator_id`,`detail`)values({$orderinfo['repair_id']},170,1,'{$log_str}')"))
        {
            logx('记录日志失败',$sid);
            $status = 1;
            $msg = '记录日志失败';
            sales_repair_error_log($db,$orderinfo['repair_id'],"记录日志失败");
            $db->execute('ROLLBACK');
            return false;
        }
        if( !$db->execute('COMMIT') )
        {
            logx("提交事务失败");
            $status = 1;
            $msg = '提交事务失败';
            sales_repair_error_log($db,$orderinfo['repair_id'],"提交事务失败");
            $db->execute('ROLLBACK');
            return false;
        }
        logx("repair_get_waybill_sf success",$sid);
    }
    else
    {
        logx(" repair_get_waybill_sf failed",$sid);
        $status = 1;
        $msg = $retval['ERROR'];
        return false;
    }
}
// repair_log
function sales_repair_error_log($db,$repair_id,$msg)
{
    $db->execute("UPDATE sales_repair SET error_msg='{$msg}' where repair_id = {$repair_id}");
    $db->execute("insert into sales_repair_log(`repair_id`,`operator_type`,`operator_id`,`detail`)"
        ."values({$repair_id},171,1,'获取物流单号ERROR:{$msg}')");
}
//顺丰子母单
function deal_sf_master($sid,&$db,$trade_infos,$logistics_info,&$msg)
{
    $sf = new sfClient();
    if(!$sf)
    {
        logx('get sfClient failed!',$sid);
        $msg = '对象新建失败';
        return false;
    }

    $logistics_auto = json_decode($logistics_info['app_key']);
    if (!$logistics_auto) {
        logx('授权失败',$sid);
        $msg = '授权失败';
        return false;
    }

    if($logistics_auto->type_sf == 0)
        $logistic_type = '1';
    else if($logistics_auto->type_sf == 1)
        $logistic_type = '2';
    else if($logistics_auto->type_sf == 2)
        $logistic_type = '3';
    else if($logistics_auto->type_sf == 3)
        $logistic_type = '7';
    else if($logistics_auto->type_sf == 4)
        $logistic_type = '37';
    else if($logistics_auto->type_sf == 5)
        $logistic_type = '38';
    else
        $logistic_type = '1';

    if($logistics_auto->pay_type == 0)
        $pay_type='1';
    else if($logistics_auto->pay_type == 1)
        $pay_type='2';
    else if($logistics_auto->pay_type == 2)
        $pay_type='3';
    else
        $pay_type='1';

    foreach($trade_infos as $orderinfo)
    {
        $tradecount = count($orderinfo);

        $trade_ids = array();
        $cod_amount = 0;
        foreach($orderinfo as $row)
        {
            $trade_ids[] = $row['stockout_id'];
            $cod_amount = $cod_amount + $row['cod_amount'];
        }

        //xml生成
        $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="OrderService" lang="zh-CN"></Request>';
        $dom = new SimpleXMLElement($xml);
        $dom->Head = $logistics_auto->customer_code.','.$logistics_auto->customer_pwd;
        $body = $dom->addChild('Body');
        $order = $body->addChild('Order');

        $order_no = $row ['stockout_no'].time();
        $order->addAttribute('orderid',$order_no);
        $order->addAttribute('express_type',$logistic_type);
        $order->addAttribute('j_contact',$row['sender_name']);
        $order->addAttribute('j_tel',$row['sender_telno']);
        $order->addAttribute('j_mobile',$row['sender_mobile']);
        $order->addAttribute('j_address',$row['sender_address']);
        $order->addAttribute('d_company','');
        $order->addAttribute('d_contact',$row['receiver_name']);
        $order->addAttribute('d_tel',$row['receiver_telno'] == ''?$row['receiver_mobile']:$row['receiver_telno']);
        $order->addAttribute('d_mobile',$row['receiver_mobile']);
        $order->addAttribute('d_address',escape_xml_string($row['receiver_address']));
        $order->addAttribute('parcel_quantity', $tradecount);
        $order->addAttribute('pay_method',$pay_type);
        $order->addAttribute('j_province',$row['sender_province']);
        $order->addAttribute('j_city',$row['sender_city']);
        splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
        $order->addAttribute('d_province',$receiver_province);
        $order->addAttribute('d_city',$receiver_city);
        $order->addAttribute('is_gen_bill_no',1);
        if($row['calc_weight']>0)
        {
            $order->addAttribute('cargo_total_weight',$row['calc_weight']);
        }
        $OrderOption = $order->addChild('OrderOption');
        $OrderOption->addAttribute('custid',$logistics_auto->month_card);
        $OrderOption->addAttribute('cargo','goods');
        $OrderOption->addAttribute('remark','');

        if($row['delivery_term'] == 2)
        {
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','COD');
            $AddedService->addAttribute('value',$cod_amount);
            $AddedService->addAttribute('value1',$logistics_auto->month_card);
        }

        $printInfo = '';
        //以货品价格保价
        if(isset($logistics_auto->insures_type) && intval($logistics_auto->insures_type)==1)
        {
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','INSURE');
            $AddedService->addAttribute('value',$row['receivable']);
            $printInfo = urldecode(json_encode(array('insure_amount'=>$row['receivable'])));

        }
        else if(isset($logistics_auto->insures_type) && intval($logistics_auto->insures_type)==2)
        {
            //以固定价格保价
            $AddedService = $OrderOption->addChild('AddedService');
            $AddedService->addAttribute('name','INSURE');
            $AddedService->addAttribute('value',$logistics_auto->insure_amount);
            $printInfo = urldecode(json_encode(array('insure_amount'=>$logistics_auto->insure_amount)));
        }
        else
        {
            //不保价
            $printInfo = urldecode(json_encode(array('insure_amount'=>0)));
        }


        $order_xml = $dom->asXML();
        $retval = $sf->get_waybill($order_xml);
        if($retval == false)
        {
            logx("deal_repair_sf  failed",$sid);
            $msg = '顺丰接口返回错误';
            return false;
        }

        $tradeidstr = implode(',',$trade_ids);

        if($retval['Head'] == 'OK')
        {
            if($retval['Body']['OrderResponse']['filter_result'] != 2)
            {
                $msg = '地址不到达';
                foreach($orderinfo as $rows)
                {
                    $db->query("UPDATE stockout_order SET status = GREATEST(55,status),error_info = '地址不到达' WHERE stockout_id = {$rows['stockout_id']}");
                }
                continue;
            }

            $mailno = $retval['Body']['OrderResponse']['mailno'];
            $mailno_arr = explode(',',$mailno);

            /*母单号*/
            $postid_master = $mailno_arr[0];
            $trade_mailno = array_combine($mailno_arr,$trade_ids);
            logx(print_r($trade_mailno,true));

            if($trade_mailno)
            {
                $count = 1;
                foreach($trade_mailno as $post_id=>$stockout_id)
                {
                    $master_sequence = $count.'/'.$tradecount;
                    $count = $count + 1;
                    $mapping_mark = isset($retval['Body']['OrderResponse']['mapping_mark'])? $retval['Body']['OrderResponse']['mapping_mark']:"";
                    $db->query("replace into stock_logistics_print(stockout_id,logistics_id,logistics_no,position_no,original_code,destination_code,master_logistics_no,master_logistics_sequence,print_info,created)
							values('{$stockout_id}',{$logistics_info['logistics_id']},'{$post_id}','{$mapping_mark}',
									'{$retval['Body']['OrderResponse']['origincode']}','{$retval['Body']['OrderResponse']['destcode']}',
									'{$postid_master}','{$master_sequence}','{$printInfo}',NOW())");

                    $db->query("update stockout_order set status = GREATEST(55,status),logistics_no='{$post_id}',logistics_id={$logistics_info['logistics_id']} 
									where stockout_id='{$stockout_id}'");

                    $order_type = $db->query_result("select src_order_type,src_order_id from stockout_order where stockout_id = '{$stockout_id}' ");
                    if($order_type && $order_type['src_order_type'] == 1)
                    {
                        $db->query("update sales_trade set logistics_no='{$post_id}',logistics_id={$logistics_info['logistics_id']} where trade_id={$order_type['src_order_id']}");
                    }

                    //插入出库单日志
                    $message = "顺丰子母单获取单号:".$post_id;
                    $db->query("insert into sales_trade_log(`type`,trade_id,operator_id,message)
									values(155,{$order_type['src_order_id']},0,'{$message}')");

                    //插入物流单号
                    $db->query("insert into stock_logistics_no(logistics_id,logistics_type,logistics_no,stockout_id,status,created)
						values({$logistics_info['logistics_id']},{$logistics_info['logistics_type']},'{$post_id}','{$stockout_id}',1,NOW()) on duplicate key update status=1,stockout_id='{$stockout_id}' ");
                }
            }
        }
        else
        {
            $msg = $retval['ERROR'];
            foreach($trade_mailno as $post_id=>$stockout_id)
            {
                $db->query("UPDATE stockout_order SET status = GREATEST(55,status),error_info = '{$retval['ERROR']}' WHERE stockout_id = {$stockout_id}");
            }
        }
    }

    releaseDb($db);
    return ;
}

function sf_multi_get_waybill(&$db, $logistics_info, $stockout_id, &$error_msg)
{
    if($stockout_id == '')
    {
        $error_msg = '查询订单信息出错';
        return true;
    }
    $row = $db->query_result("SELECT so.stockout_id, so.receiver_area, so.receiver_name, so.receiver_address, so.receiver_mobile, so.receiver_telno, so.receiver_province, 
										 so.receiver_city, so.receiver_district, so.receiver_zip, so.calc_weight, so.src_order_id, so.stockout_no, so.receiver_dtb,
										 st.cod_amount, st.delivery_term, 
										 sw.contact AS sender_name, sw.address  AS sender_address, sw.telno AS sender_telno, 
										 sw.mobile  AS sender_mobile, sw.zip    AS sender_zip,sw.province   AS sender_province,
										 sw.city 	AS sender_city, sw.district AS sender_district
								  FROM stockout_order so 
								  LEFT JOIN sys_warehouse sw ON sw.warehouse_id = so.warehouse_id
								  LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id
								  WHERE so.stockout_id = {$stockout_id}");

    if(!$row)
    {
        $error_msg = '查询订单信息出错';
        return false;
    }

    $sid = $logistics_info['SID'];
    $uid = $logistics_info['UID'];

    $sf = new sfClient();
    if(!$sf)
    {
        $error_msg = "顺丰授权失败";
        return false;
    }

    $logistics_auto = json_decode($logistics_info['app_key']);
    if($logistics_auto->type_sf == 0)
        $logistic_type = '1';
    else if($logistics_auto->type_sf == 1)
        $logistic_type = '2';
    else if($logistics_auto->type_sf == 2)
        $logistic_type = '3';
    else if($logistics_auto->type_sf == 3)
        $logistic_type = '7';
    else if($logistics_auto->type_sf == 4)
        $logistic_type = '37';
    else if($logistics_auto->type_sf == 5)
        $logistic_type = '38';
    else
        $logistic_type = '1';

    if($logistics_auto->pay_type == 0)
        $pay_type='1';
    else if($logistics_auto->pay_type == 1)
        $pay_type='2';
    else if($logistics_auto->pay_type == 2)
        $pay_type='3';
    else
        $pay_type='1';

    $logistics_no = $db->query_result_single("SELECT sln.logistics_no FROM stock_logistics_no sln WHERE sln.stockout_id = {$stockout_id} AND sln.logistics_id = '{$logistics_info['logistics_id']}' AND sln.status = 5 AND sln.type = 1 LIMIT 1");
    if ($logistics_no != '')
    {
        $db->query("update stock_logistics_no SET status = 1 WHERE stockout_id='{$stockout_id}' AND logistics_id='{$logistics_info['logistics_id']}' AND logistics_no='{$logistics_no}' AND STATUS=5 AND type = 1");

        //插入多物流单号
        $db->query("insert into sales_record_multi_logistics(operator_id,trade_id,logistics_no,logistics_id,created)
				values('{$uid}', {$row['src_order_id']}, '{$logistics_no}','{$logistics_info['logistics_id']}', NOW()) ON DUPLICATE KEY UPDATE operator_id='{$uid}' ");

        return true;
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?><Request service="OrderService" lang="zh-CN"></Request>';
    $dom = new SimpleXMLElement($xml);
    $dom->Head = $logistics_auto->customer_code.','.$logistics_auto->customer_pwd;
    $body = $dom->addChild('Body');
    $order = $body->addChild('Order');

    $order_no = $row ['stockout_no'].'@'.$sid.time();
    $order->addAttribute('orderid',$order_no);
    $order->addAttribute('express_type',$logistic_type);
    $order->addAttribute('j_contact',$row['sender_name']);
    $order->addAttribute('j_tel',$row['sender_telno']);
    $order->addAttribute('j_mobile',$row['sender_mobile']);
    $order->addAttribute('j_address',$row['sender_address']);
    $order->addAttribute('d_company','');
    $order->addAttribute('d_contact',$row['receiver_name']);
    $order->addAttribute('d_tel',$row['receiver_telno'] == ''?$row['receiver_mobile']:$row['receiver_telno']);
    $order->addAttribute('d_mobile',$row['receiver_mobile']);
    $order->addAttribute('d_address',escape_xml_string($row['receiver_address']));
    $order->addAttribute('parcel_quantity','1');
    $order->addAttribute('pay_method',$pay_type);
    $order->addAttribute('j_province',$row['sender_province']);
    $order->addAttribute('j_city',$row['sender_city']);
    splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
    $order->addAttribute('d_province',$receiver_province);
    $order->addAttribute('d_city',$receiver_city);
    if($row['calc_weight']>0)
    {
        $order->addAttribute('cargo_total_weight',$row['calc_weight']);
    }
    $OrderOption = $order->addChild('OrderOption');
    $OrderOption->addAttribute('custid',$logistics_auto->month_card);
    $OrderOption->addAttribute('cargo','goods');
    $OrderOption->addAttribute('remark','');


    if($row['delivery_term'] == 2)
    {
        $AddedService = $OrderOption->addChild('AddedService');
        $AddedService->addAttribute('name','COD');
        $AddedService->addAttribute('value',$row['cod_amount']);
        $AddedService->addAttribute('value1',$logistics_auto->month_card);
    }

    $order_xml = $dom->asXML();
    logx($order_xml,$sid);
    $retval = $sf->get_waybill($order_xml);
    logx(print_r($retval,true),$sid);

    if($retval == false)
    {
        $error_msg = '顺丰接口返回为空';
        return false;
    }
    if($retval['Head'] == 'OK')
    {
        if($retval['Body']['OrderResponse']['filter_result'] != 2)
        {
            $error_msg = '地址不到达';
            return false;
        }

        //插入物流单号
        $db->query("insert into stock_logistics_no(logistics_id,logistics_type,logistics_no,stockout_id,status,type,v_trade_no,send_province,send_city,send_district,send_address,receiver_dtb,receiver_info,created)
						values('{$logistics_info['logistics_id']}','{$logistics_info['logistics_type']}','{$retval['Body']['OrderResponse']['mailno']}','{$row['stockout_id']}',1,1,'{$order_no}','{$row['sender_province']}','{$row['sender_city']}','{$row['sender_district']}','{$row['sender_address']}','{$row['receiver_dtb']}','{$row['receiver_area']}',NOW())");

        $mapping_mark = isset($retval['Body']['OrderResponse']['mapping_mark'])? $retval['Body']['OrderResponse']['mapping_mark']:"";
        $db->query("replace into stock_logistics_print(stockout_id,logistics_id,logistics_no,position,original_code,destination_code,created)
						values({$row['stockout_id']},{$logistics_info['logistics_id']},'{$retval['Body']['OrderResponse']['mailno']}','{$mapping_mark}',
								'{$retval['Body']['OrderResponse']['origincode']}','{$retval['Body']['OrderResponse']['destcode']}',NOW())");

        //插入多物流单号
        $db->query("insert into sales_record_multi_logistics(operator_id,trade_id,logistics_no,logistics_id,created)
				values('{$uid}', {$row['src_order_id']}, '{$retval['Body']['OrderResponse']['mailno']}','{$logistics_info['logistics_id']}', NOW()) ON DUPLICATE KEY UPDATE operator_id='{$uid}' ");

        //插入出库单日志
        $message = "顺丰热敏获取多物流单号:".$retval['Body']['OrderResponse']['mailno'];
        $db->query("insert into sales_trade_log(`type`,trade_id,operator_id,message)
						values(155,{$row['src_order_id']},1,'{$message}')");

        $error_msg = $retval['Body']['OrderResponse']['mailno'];
        return true;
    }
    else
    {
        $error_msg = $retval['ERROR'];
        return false;
    }
}

?>