<?php 
use Common\Common\Factory;
include TOP_SDK_DIR."/top/TopSdk.php";
date_default_timezone_set('Asia/Shanghai');

/**
 * 获取电子面单
 */
function newtopGetWaybill(&$db,$stockout_ids,$logistics_info,$templateURL,$packageNos,&$result)
{
	\Think\Log::write("topGetWaybill start {$stockout_ids}",\Think\Log::INFO);
	$result = array(
	    'status' => 0,
	    'msg'   => 'success',
	    'data'  =>array()  
	);
	try {
	    $sender_from_type = get_config_value('stock_print_sender_from',0);
	    //查询出库单信息
	    if (empty($stockout_ids))
	    {
	        $log_str = "topGetWaybill--- 没有获取到出库单信息，传入的出库单信息为stockout_ids :{$stockout_ids}!!";
	        SE('没有获取到出库单信息');
	       
	    }
	    $log_str  = "topGetWaybill---查询出库单相关信息";
	    $model = D('Stock/StockOutOrder');
	    $stockout_order_info = $model->getStockoutInfoBeforeApplyWaybill($stockout_ids);
	    \Think\Log::write("topGetWaybill--- 请求的电子面单详情".print_r($stockout_order_info,true),\Think\Log::DEBUG);
	    if(empty($stockout_order_info))
	    {
	        SE('没有查询到相关信息');
	    }
	    foreach($stockout_order_info as $row)
	    {
	        //因为stockout_order中保存的是地址编号
	        splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
	        $warehouseid = $row['warehouse_id'];
            $packed_stockout_info["$warehouseid"]['template_url']   = $templateURL;
	        $packed_stockout_info["$warehouseid"]['sender_province']   = $row['sender_province'];
	        $packed_stockout_info["$warehouseid"]['sender_city']       = $row['sender_city'];
	        $packed_stockout_info["$warehouseid"]['sender_district']   = $row['sender_district'];
	        $packed_stockout_info["$warehouseid"]['sender_address']    = $row['sender_address'];
            $packed_stockout_info["$warehouseid"]['sender_name']    = $row['sender_name'];
            $packed_stockout_info["$warehouseid"]['sender_telno']    = $row['sender_telno'];
	        $packed_stockout_info["$warehouseid"]['sender_mobile']    = $row['sender_mobile'];
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['sender_name']    = $sender_from_type==0?$row['sender_name']:$row['shop_contact'];
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['sender_telno']    = $sender_from_type==0?$row['sender_telno']:$row['shop_telno'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['sender_mobile']    = $sender_from_type==0?$row['sender_mobile']:$row['shop_mobile'];

	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['platform_id']     = $row['platform_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['app_key']         = $row['app_key'];  //
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['shop_id']         = $row['shop_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['src_tids']        = $row['src_tids'];
             $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['delivery_term'] = $row['delivery_term'];
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['cod_amount'] = $row['cod_amount'];
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['delivery_term'] = $row['delivery_term'];
// 	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['src_order_no']    = $row['src_order_no'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_id']     = $row['stockout_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_no']     = $row['stockout_no'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_province'] = $receiver_province;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_city']   = $receiver_city;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_district'] = $receiver_district;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_address'] = $row['receiver_address'];
			$packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['warehouse_id']   = $row['warehouse_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_name']   = $row['receiver_name'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_tel'] =
	        $row['receiver_mobile'] == ''?$row['receiver_telno']:$row['receiver_mobile'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['package_items'][]= array(
	            'count' => (int)$row['num'] == 0?1:(int)$row['num'],
	            'name' => $row['goods_name']
	        );
	    }
	    //这个配置是否可以在各个模块下共用
	    
	    $result['cancel']  = array();
	    $result['success'] = array();
	    $result['fail']    = array();
	    $result['error']   = array();

        //批量处理电子面单申请
	    $stockout_map = newtopWaybillArrangeBeforeGet($db,$logistics_info,$packed_stockout_info,$packageNos,$result);
	    $log_str  = "topGetWaybill 失败电子面单更新数据库start";
	    if(!empty($result['fail']))
	    {
	        waybill_error_handle($logistics_info,$result);
	    }
	    $log_str  = "topGetWaybill 成功获取电子面单更新数据库start";
	    if(!empty($result['success']))
	    {
            waybill_success_handle($logistics_info,$packageNos,$result);

            foreach ($result['fail'] as $key => $fail_item)
	        {
	            if(!isset($fail_item['stock_no']))
	            {
	                $fail_item['stock_no'] = $stockout_map["{$fail_item['stock_id']}"]['stockout_no'];
	            }
	        }
	    }
	    $log_str  = "topGetWaybill 需要取消的电子面单开始请求处理start";
	    
	    if(newtopWaybillCancelUnused($db,$result['cancel']))
	    {
	        waybill_cancel_handler($result);
	    }
	    $result['data'] = array(
	        'fail'         =>array_merge_recursive($result['fail'],$result['error']),
	        'success'      =>$result['success'],
	    );
	    if (!empty( $result['data']['fail'] ))
	    {
	        $result['status'] = 2;
	    }
	    unset($result['error']);
	    unset($result['fail']);
	    unset($result['success']);
	    unset($result['cancel']);
	}catch (\Think\Exception\BusinessLogicException $e){
	    $msg = $e->getMessage();
	    $result = array(
	        'status' => 1,
	        'msg' => $msg,
	    );
	    return false;
	}catch (\PDOException $e){
	    $msg = $e->getMessage();
	    $result = array(
	        'status' => 1,
	        'msg' => "未知错误，请联系管理员",
	    );
	    \Think\Log::write($log_str."topGetWayBill ".$msg);
	    return false;
	}catch (\Exception $e){
	    $msg = $e->getMessage();
	    $result = array(
	        'status' => 1,
	        'msg' => $msg,
	    );
	    \Think\Log::write($log_str."topGetWayBill ".$msg);
	    return false;
	}
	return true;
}
function newtopWaybillArrangeBeforeGet(&$db,$logistics_info,$apply_info,$packageNos,&$result)
{
    $platform_code = C('platform_code');
    $stockout_map = array();
    foreach($apply_info as $warehouse)
    {
        $sender = array();             //发货地址，即仓库的地址信息
        $tradeOrderInfoCols = array();          //订单信息，即收货人地址信息
        $shippingAddress = array();
        $shippingAddress['province']       = $warehouse['sender_province'];
        $shippingAddress['city']           = $warehouse['sender_city'];
        $shippingAddress['district']           = $warehouse['sender_district'] == '无'?'':$warehouse['sender_district'];
        $shippingAddress['detail'] = $warehouse['sender_address'];
        $sender['address'] = $shippingAddress;
        $sender['name'] = $warehouse['sender_name'];
        $sender['phone'] = $warehouse['sender_telno'];
        $sender['mobile'] = $warehouse['sender_mobile'];
        $orderlist = $warehouse['order'];
        $count = 0;
        $object_id = 0;
		//护城河日志
		$get_src_tids = '';
        foreach($orderlist as $stockout_id => $order)
        {
            try {

                //记录当前出库单的一些额外信息  如real_user_id 需要从订单对应的店铺app_key查询出来
                $sales_shop = array();
                $sales_shop['app_key']       = $order['app_key'];
                $sales_shop['shop_id']       = $order['shop_id'];
                $sales_shop['platform_id']   = $order['platform_id'];
                \Platform\Manager\Manager::parse_app_key($sales_shop);
                //记录出库单对应的相关信息的映射关系
                $stockout_map["{$stockout_id}"] = array(
                    'stockout_no'  => $order['stockout_no'],
                    'shop_id'      =>$order['shop_id'],
                    'sender_name'      =>$order['sender_name'],
                    'sender_telno'      =>$order['sender_telno'],
                    'sender_mobile'      =>$order['sender_mobile'],
					'warehouse_id'      =>$order['warehouse_id'],
                );
                if(!isset($sales_shop->user_id) && ($sales_shop->platform_id == 1))
                {
                    $result['fail'][] = array(
                        'stock_id' => "{$stockout_id}",
                        'stock_no' => $order['stockout_no'],
                        'msg'      => "面单使用者的用户帐号不存在"
                    );
                    \Think\Log::write("topWaybillArrangeBeforeGet-出库单号为：{$stockout_id}----面单使用者的用户帐号user_id不存在");
                    continue;
                }
                 
                $seller_id = $sales_shop->platform_id == 1?$sales_shop->user_id:$logistics_info->user_id;
                $tradeOrderInfoCol = array(
                    'recipient'=> array(
                            'name'       => $order['receiver_name'],
                            'mobile'      => $order['receiver_tel'],
                            //'phone'     => $order['receiver_telno'];//固定电话
                            'address'    => array(
                                'province'       => $order['receiver_province'],
                                'city'           => $order['receiver_city'],
                                'district'           => $order['receiver_district'],
                                'detail' => $order['receiver_address']
                                ),
                            ),
                    'order_info'    => array(
                        'order_channels_type'  => isset($platform_code[$order['platform_id']])?$platform_code[$order['platform_id']]:"OTHERS",//获取订单来源平台对应编码配置//订单来源平台编码【非】 ,
                        'trade_order_list'     => explode(",", $order['src_tids']),
                        ),
                    'package_info'      => array(
                        'items'         =>  $order['package_items'],
                        'id'            =>  $stockout_id,//已出库单号id值作为包裹号
                        ),
                    
                    //'logistics_services'         => array($logistics_info->product_type),
                    'user_id'         => $seller_id,//区分对待 
                    'template_url'    => $warehouse['template_url'],//缺少模板URL
                    'object_id'       => $object_id,
                );
                $stockoutId = array(
                    'id' => $stockout_id
                );
                if($logistics_info->logistics_type ==8)
                {
                    $logistics_ar = Obj2Arr($logistics_info);
                    logExtent('sdfsd 111:'.$logistics_ar['TIMED-DELIVERY']);
                    if($logistics_ar['TIMED-DELIVERY'] != '0')
                    {
                        if(isset($tradeOrderInfoCol['logistics_services']) && is_array($tradeOrderInfoCol['logistics_services']))
                        {
                            $tradeOrderInfoCol['logistics_services']['TIMED-DELIVERY'] = array('value'=>$logistics_ar['TIMED-DELIVERY']);

                        }else{
                            $tradeOrderInfoCol['logistics_services'] = array('TIMED-DELIVERY'=>array('value'=>$logistics_ar['TIMED-DELIVERY']));
                        }
                    }
                }
                  //判断物流公司是否支持货到付款
                if($logistics_info->is_support_cod == 0&&$order['delivery_term'] == 2)
                {
                    $result['fail'][] = array(
                        'stock_id' => "{$stockout_id}",
                        'stock_no' => $order['stockout_no'],
                        'msg'      => "物流公司不支持货到付款"
                    );
                    \Think\Log::write("topWaybillArrangeBeforeGet-出库单号为：{$stockout_id}----物流公司不支持货到付款");
                    continue;
                }
                if($order['delivery_term'] == 2)
                {
                    if(isset($tradeOrderInfoCol['logistics_services']) && is_array($tradeOrderInfoCol['logistics_services']))
                    {
                        $tradeOrderInfoCol['logistics_services']['SVC-COD'] = array("value"=>$order['cod_amount']);

                    }else{
                        $tradeOrderInfoCol['logistics_services'] = array("SVC-COD"=>array("value"=>$order['cod_amount']));
                    }
                }

                
                //查询出库单在面单池里面是否有信息 ：如果请求的是菜鸟的话，必须要把其原来请求的菜鸟的单据回收掉，才能请求下一次的菜鸟电子面单
                $old_waybill_cond = array(
                    'sln.stockout_id'       => $stockout_id,
                    'sln.status'            => array('in','3,5,4'),//3更新有故障，4回收失败，5待回收
                    'cl.bill_type'          => 2,
                );
                $old_waybill_fields = array(
                    'sln.rec_id','sln.status','sln.src_tids','sln.shop_id','sln.logistics_type','sln.stockout_id','sln.logistics_id','sln.sender_province','sln.sender_city','sln.sender_district','sln.sender_address','sln.logistics_no','sln.waybill_info','cl.bill_type','so.src_order_id'
                );
                $waybill_no_info = D('Stock/StockLogisticsNo')->getLogisticsNoLeftCfgLostics($old_waybill_fields,$old_waybill_cond);

                if (count($waybill_no_info)>1)
                {
                    $result['fail'][] = array(
                        'stock_id'  => $stockout_id,
                        'stock_no'  => $order['stockout_no'],
                        'msg'       => "电子面单使用异常，请联系管理员",
                    );
                    \Think\Log::write('topWaybillArrangeBeforeGet-含有两个以上的未回收的的电子面单：'.print_r($waybill_no_info,true));
                    continue;
                }

				//护城河日志
				$update_src_tids = '';
                if(count($waybill_no_info)==1)
                {
                    $new_waybill_info = array(
                        'src_tids'          => $order['src_tids'],
                        'shop_id'           => $order['shop_id'],
                        'logistics_type'    => $logistics_info->logistics_type,
                        'stockout_id'       => $stockout_id,
                        'send_province'     => $shippingAddress['province'],
                        'send_city'         => $shippingAddress['city'],
                        'send_district'     => $shippingAddress['district'],
                        'send_address'      => $shippingAddress['detail'],
                        //'logistics_id'    => $logistics_info->logistics_id,
                    
                    );
                    $old_waybill_info = array(
                        'src_tids'	         => $waybill_no_info[0]['src_tids'],
                        'shop_id'	         => $waybill_no_info[0]['shop_id'],
                        'logistics_type'	 => $waybill_no_info[0]['logistics_type'],//不以logistics_id为区分是因为在同一个bill_type可能logistics_type一样的话不需要取消
                        'stockout_id'	     => $waybill_no_info[0]['stockout_id'],
                        'send_province'	     => $waybill_no_info[0]['sender_province'],
                        'send_city'	         => $waybill_no_info[0]['sender_city'],
                        'send_district'	     => $waybill_no_info[0]['sender_district'],
                        'send_address'       => $waybill_no_info[0]['sender_address']
                    );
                    $new_comp_new_arr = array_diff_assoc($new_waybill_info,$old_waybill_info);
                    if (!empty($new_comp_new_arr))
                    {

                        if(!newtopWaybillCancelBeforeGet($db,$waybill_no_info[0],$result['cancel']))
                        {
                            array_push($result['fail'], $result['cancel']['fail']["{$waybill_no_info[0]['rec_id']}"]);
                            continue;
                        }

                    }else
                    {
						$update_src_tids = $new_waybill_info['src_tids'];
                        newtopWaybillGetByUpdate($db,$logistics_info,$waybill_no_info[0],$tradeOrderInfoCol,$sender,$stockout_map,$result,$update_src_tids);

                        continue;
                    }
                }
                
            }catch (\PDOException $e){
                $msg = $e->getMessage();
                $result['fail'][] = array(
                    'stock_id' => "{$stockout_id}",
                    'stock_no' => $order['stockout_no'],
                    'msg'      => "未知错误，请联系管理员"
                );
                \Think\Log::write("top-get-waybill-出库单号为：{$stockout_id}-".$msg);
                continue;
                
            }catch (\Exception $e) {
                $msg = $e->getMessage();
                $result['fail'][] = array(
                    'stock_id' => "{$stockout_id}",
                    'stock_no' => $order['stockout_no'],
                    'msg'      => $msg,
                );
                \Think\Log::write("topWaybillArrangeBeforeGet-出库单号为：{$stockout_id}-".$msg);
                continue;
            }
            $ordernos[] = $stockout_id;
            $stockoutIds[$object_id] = $stockoutId;
            $tradeOrderInfoCols[$object_id] = $tradeOrderInfoCol;
			$object_id ++;
			$get_src_tids = trim($order['src_tids'],',').',';

            if((++$count == 10)&&(empty($packageNos)))     //批量获取面单一次最多支持10条
            {
				$get_src_tids = trim($get_src_tids,',');
                newtopWaybillBatchDealApply($db,$logistics_info,$sender,$tradeOrderInfoCols,$stockoutIds,$stockout_map,$result,$packageNos,$get_src_tids);
                $count = 0;
                $object_id = 0;
				$get_src_tids = '';
                $tradeOrderInfoCols = array();
                $ordernos = array();
            }
        }
        if(empty($packageNos)){
            $log_str  = "topWaybillArrangeBeforeGet 批量获取单据信息，已跳出循环";
            if(!empty($tradeOrderInfoCols))     //不足10条的一次获取
            {
                $get_src_tids = trim($get_src_tids,',');
                newtopWaybillBatchDealApply($db,$logistics_info,$sender,$tradeOrderInfoCols,$stockoutIds,$stockout_map,$result,$packageNos,$get_src_tids);
            }
        }else{
            $tradeOrderTemp = array();
            $count = 0;
            $obj_id = 0;
            for($k=0;$k<count($tradeOrderInfoCols);$k++){

                $tradeOrderInfoC = $tradeOrderInfoCols[$k];
                $sid = $tradeOrderInfoC['package_info']['id'];
                for($i=0;$i<count($packageNos[$sid]);$i++){
                    $tc = $tradeOrderInfoC;
                    $tc['package_info']['id'] = $packageNos[$sid][$i];
                    $tc['object_id'] = $obj_id;
                    $stockoutIds[$obj_id] = ['id'=>$sid];
                    $tradeOrderTemp[$obj_id] = $tc;
                    $obj_id ++;
                    if(++$count == 10){
                        newtopWaybillBatchDealApply($db,$logistics_info,$sender,$tradeOrderTemp,$stockoutIds,$stockout_map,$result,$packageNos,$get_src_tids);
                        $count = 0;
                        $tradeOrderTemp = array();
                        $obj_id = 0;
                    }
                }
            }
            $log_str  = "topWaybillArrangeBeforeGet 批量获取多物流单据信息，已跳出循环";
            if(!empty($tradeOrderTemp))     //不足10条的一次获取
            {
                $get_src_tids = trim($get_src_tids,',');
                newtopWaybillBatchDealApply($db,$logistics_info,$sender,$tradeOrderTemp,$stockoutIds,$stockout_map,$result,$packageNos,$get_src_tids);
            }
        }
    }
    return $stockout_map;
}
/**
 * 取消电子面单
 * $rec_ids  string 1,2,4,
 */
function newtopCancelWaybill(&$db,$rec_ids,$logistics_info,&$result=array())
{
    $result=array(
        'status' => 0,
        'msg'    => 'success',
        'data'   => array()  
    );
    $cancel_success = array();
    $cancel_fail = array();
    

    $log_str  = "top-waybill-cancel M获取取消的电子面单信息";
    $waybill_info_fields = array('sln.rec_id','sln.logistics_no','sln.logistics_id','sln.src_tids','sln.shop_id','sln.stockout_id','cs.app_key','cs.platform_id','so.stockout_no','so.src_order_id');
    $waybill_info_cond = array(
        'sln.rec_id' => array('in',$rec_ids),
    );
    $stock_logistics_no_db = D('Stock/StockLogisticsNo');
    $cancel_info = $stock_logistics_no_db->getWaybillInfoLeftShopAndStockout($waybill_info_fields,$waybill_info_cond);        
    if(empty($cancel_info) || $cancel_info === false)
    {
        $result=array(
            'status'    => 1,
             'msg'      => '没有查询电子面单信息',
             'data'     => $rec_ids
        );
        \Think\Log::write($log_str."topCancelWaybill logistics_id:{$logistics_info->logsitics_id} rec_ids:{$rec_ids}没有查询电子面单信息");
        return false;
    }
    	
	$log_str  = "top-waybill-cancel 对接接口取消电子面单start";
	foreach ($cancel_info as $key => $cancel_item)
	{
		\Platform\Manager\Manager::parse_app_key($cancel_item);
		try {
		    
			$waybillCancelRequest = array(
					'cp_code'          => $logistics_info->code,
					'waybill_code'     => $cancel_item->logistics_no,
			);
			$src_tids = $cancel_item->src_tids;
			$retval = newtop_cancel_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session, $waybillCancelRequest,$src_tids);
		    \Think\Log::write("top-waybill-cancel----logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---retavl:".print_r($retval,true),\Think\Log::INFO);
							
			if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
			{
				E($retval->error_msg);
			}else{
				if(is_bool($retval->cancel_result))
				{
					$is_success = $retval->cancel_result;
				}else if(is_string($retval->cancel_result))
				{
					$is_success = $retval->cancel_result=='true'?true:false;
				}
				if($is_success == true)
				{
				    $cancel_success["{$cancel_item->rec_id}"] =array(
						'id'		=> $cancel_item->rec_id,
				        'logistics_no'  => $cancel_item->logistics_no,
				        'stock_id'      => $cancel_item->stockout_id,
				        'stock_no'      => $cancel_item->stockout_no,
				        'trade_id'      => $cancel_item->src_order_id,
				    );
					//$cancel_success[] = $cancel_item->logistics_no;
					\Think\Log::write("logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---取消成功:",\Think\Log::INFO);
					$success_data = array(
					      'status' => 6
					);
					$success_cond = array(
					    'logistics_type'   => $logistics_info->logistics_type,
					    'logistics_no'     => $cancel_item->logistics_no,
					);
					$res_success = $stock_logistics_no_db->updateLogisticsNo($success_data,$success_cond);
						
				}
				else
				{
				    \Think\Log::write("logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---取消失败:",\Think\Log::INFO);
				    
				    SE('取消失败');
						
				}
			}
		}catch(\Think\Exception\BusinessLogicException $e){
			$msg = $e->getMessage();
			$result['status'] = 2;
			$cancel_fail["{$cancel_item->rec_id}"] =array(
						'id'		=> $cancel_item->rec_id,
			            'logistics_no' =>$cancel_item->logistics_no,
				        'stock_id' => $cancel_item->stockout_id,
				        'stock_no' => $cancel_item->stockout_no,
				        'msg'=>$msg,
			            'trade_id' => $cancel_item->src_order_id,
		    );
			
			\Think\Log::write($log_str."logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---取消失败:".$msg,\Think\Log::INFO);
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			$result['status'] = 2;
			$cancel_fail["{$cancel_item->rec_id}"] =array(
						'id'		=> $cancel_item->rec_id,
			            'logistics_no' =>$cancel_item->logistics_no,
				        'stock_id' => $cancel_item->stockout_id,
				        'stock_no' => $cancel_item->stockout_no,
				        'msg'=>"未知错误，请联系管理员",
			            'trade_id' => $cancel_item->src_order_id,
		    );

			\Think\Log::write($log_str."logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---取消失败:".$msg,\Think\Log::INFO);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$result['status'] = 2;
				
			$cancel_fail["{$cancel_item->rec_id}"] =array(
						'id'		=> $cancel_item->rec_id,
				        'stock_id' => $cancel_item->stockout_id,
				        'stock_no' => $cancel_item->stockout_no,
				        'msg'=>$msg,
			            'trade_id' => $cancel_item->src_order_id,
			            'logistics_no' =>$cancel_item->logistics_no,
		    );
			\Think\Log::write($log_str."logisics_id: {$logistics_info->logistics_id}---logistics_no: {$cancel_item->logistics_no}---取消失败:".$msg,\Think\Log::INFO);
		}
		if (!empty($cancel_fail["{$cancel_item->rec_id}"]))
		{
		    $fail_data = array(
		        'status' => 4
		    );
		    $fail_cond = array(
		        'logistics_type'   => $logistics_info->logistics_type,
		        'logistics_no'     => $cancel_item->logistics_no,
		    );
		    $res_fail = $stock_logistics_no_db->updateLogisticsNo($fail_data,$fail_cond);
		    
		}
		
	}
	$result['data'] = array(
	    'fail'     => $cancel_fail,
	    'success'  => $cancel_success
	);
	\Think\Log::write("sid:{$logistics_info->sid}     topCancelWaybill  end:----/n授权物流公司为：{$logistics_info->logistics_id}",\Think\Log::INFO);

	return true;
}

function topStdTemplatesGet(&$db,$logistics_info,&$result=array(),$isAll=false){
    $result['status'] = 0;
    $templatesURL = array();
    $retval = top_stdTemplates_get($logistics_info->key, $logistics_info->secret, $logistics_info->session);
//    \Think\Log::write("topStdTemplatesGet-----".print_r($retval,1),\Think\Log::INFO);
    if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
    {
        $result['msg'] = (array)($retval->error_msg);
        $result['msg'] = $result['msg'][0];
        $result['status'] = 1;
        releaseDb($db);
        return false;
    }else {
        $retval = $retval->result->datas->standard_template_result;
        if($isAll){
            $result['success'] = $retval;
            return;
        }
        foreach ($retval as $key ) {
            if($key->cp_code == $logistics_info->code){
               if(is_array($key->standard_templates->standard_template_do)){ 
                foreach ($key->standard_templates->standard_template_do as $template) {
                       $templateURL[] = $template; 
                               }
                }else {
                    $templateURL[] = $key->standard_templates->standard_template_do;
                }
            }
        }
        $result['success'] = $templateURL;
        releaseDb($db);
        return true;
    }
}

/**
 *获取ISV定义的模板，这个用来打发货单和普通物流单
 * 
 */
function topGetISVTemplates(&$db,$logistics_info,&$result=array()){
    $result['status'] = 0;
    $templatesURL = array();
    $retval = top_ISVTemplates_get($logistics_info->key, $logistics_info->secret, $logistics_info->session);
    if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
    {
        $result['msg'] = $retval->error_msg;
        $result['status'] = 1;
        return false;
    }else {
        \Think\Log::write("topGetISVTemplates--- 获得的模板为：".print_r($retval,true),\Think\Log::DEBUG);
       /* $retval = json_encode($retval);
        $retval = json_decode($retval);*///没有找到更好地解决方案，暂时先用这个
        $retval = $retval->result->datas->custom_template_result;
        if(is_array($retval)){
                foreach ($retval as $key ) {
                        $key->template_id = $key->isv_template_id;
                        $key->template_name = $key->isv_template_name;
                        $key->customarea_id = $key->isv_template_id;
                        $key->customarea_url = $key->isv_template_url;
                        $templateURL[] = $key; 
                }
            }else {
                $result['status'] = 1;
                $result['msg'] = "您没有设置该类型的模板！";
                return false;
            }
        }
        $result['success'] = $templateURL;
        return true;
}

/**
 * 获取商家自定义模板，需要商家设计过，需要商家自定义区
 * 这个一般是获取电子面单模板
 */
function topGetMyStdTemplates(&$db,$logistics_info,&$result=array()){
    $result['status'] = 0;

    $templateURL = array();
    $retval = top_myStdTemplates_get($logistics_info->key, $logistics_info->secret, $logistics_info->sessionkey); 
    if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
    {
        $result['msg'] = $retval->error_msg;
        $result['status'] = 1;
        return false;
    }else {
        \Think\Log::write("topGetMyStdTemplates--- 获得的模板为：".print_r($retval,true),\Think\Log::DEBUG);
        $retval = $retval->result->datas->user_template_result;

        if(isset($logistics_info->code)&&$logistics_info->code != ""){
            foreach ($retval as $key ) {
                if($key->cp_code == $logistics_info->code){
                    foreach ($key->user_template_do as $template) {
                        $templateURL[] = $template; 
                    }
                }
            }
        }else {
            $templateURL = $retval;
        }
        $result['success'] = $templateURL;
        return true;
    } 
}

/**
 *获取ISV自定义模板
 *包括ISV自定义模板、自定义区、打印项
 */
function topGetISVResource(&$db,$logistics_info,$type,&$result=array()){
    $result['status'] = 0;  
    $myStdTemplates = array();
    $retval = top_ISVResource_get($logistics_info->key, $logistics_info->secret, $logistics_info->session,$type);
    if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
    {   
        $result['msg'] = (array)($retval->error_msg);
        $result['msg'] = $result['msg'][0];
        $result['status'] = 1;
        return false;
    }else {
     \Think\Log::write("topGetISVResource--- 获得的".$type."模板为：".print_r($retval,true),\Think\Log::DEBUG);
        /*$retval = json_encode($retval);
        $retval = json_decode($retval);*/
        $retval = $retval->result->resource_list->isv_resource_do;
        foreach($retval as $key){
            $key->template_name = $key->resource_name;
            $key->template_id   = $key->resource_id;
            $key->customarea_id = $key->resource_id;
            $key->customarea_url = $key->resource_url;
            $result['success'][] = $key;
        }
    }
    return true;
}

/**
 * 获取商家自定义区
 * 采用2个接口，topGetMyStdtemplates和topGetCustomareas
 */
function topGetUserAreas(&$db,$logistics_info,&$result=array()){
    $myStdTemplates = array();
    $logistics_info->sessionkey = $logistics_info->session;

    if(!topGetMyStdTemplates($db, $logistics_info,$myStdTemplates)){ 
        $result['status'] = 1 ;
        $result['msg'] = $myStdTemplates['msg'];
        return false;
    }
    \Think\Log::write("topGetUserAreas--- 获得的用户模板为：".print_r($myStdTemplates,true),\Think\Log::DEBUG);
    $myStdTemplates = $myStdTemplates['success'];
    $i = 0;
    $j = 0;
    $result['status'] = 0;
    if(!!$myStdTemplates)
    foreach ($myStdTemplates as $key) {
        if(is_array($key->user_std_templates->user_template_do))
            {
                foreach ($key->user_std_templates->user_template_do as $value) {
                            topGetCustomareas($db, $logistics_info,$value->user_std_template_id,$message);
                            if($message['status'] == 1){
                                $result['status'] = 2;
                                $result['fail'][$i]['template_id'] = $value->user_std_template_id;
                                $result['fail'][$i]['template_name'] = $value->user_std_template_name;
                                $result['fail'][$i]['msg'] = $message['msg'];
                                $i++;
                            }else {
                                $result['success'][$j]['cp_code'] = $key->cp_code;
                                $result['success'][$j]['template_id'] = $value->user_std_template_id;
                                $result['success'][$j]['template_url'] = $value->user_std_template_url;
                                $result['success'][$j]['template_name'] = $value->user_std_template_name;
                                $result['success'][$j]['customarea_id'] = $message['success']->custom_area_id;
                                $result['success'][$j]['customarea_url'] = $message['success']->custom_area_url;
                                $j++;
                            }
                        }
            }else {
                topGetCustomareas($db, $logistics_info,$key->user_std_templates->user_template_do->user_std_template_id,$message);
                if($message['status'] == 1){
                    $result['status'] == 2;
                    $result['fail'][$i]['template_id'] = $key->user_std_templates->user_template_do->user_std_template_id;
                    $result['fail'][$i]['msg'] = $message['msg'];
                    $i++;
                }else {
                    $result['success'][$j]['cp_code'] = $key->cp_code;
                    $result['success'][$j]['template_id'] = $key->user_std_templates->user_template_do->user_std_template_id;
                    $result['success'][$j]['template_url'] = $key->user_std_templates->user_template_do->user_std_template_url;
                    $result['success'][$j]['template_name'] = $key->user_std_templates->user_template_do->user_std_template_name;
                    $result['success'][$j]['customarea_id'] = $message['success']->custom_area_id;
                    $result['success'][$j]['customarea_url'] = $message['success']->custom_area_url;
                    $j++;
                }
        }
    }
    else {
        $result['status'] = 1;
        $result['msg'] = "您没有设置该类型的模板！"; 
    }
}

/**
 *获取商家自定义区
 * 这个配合getmystdtemplate使用,需要传template_id
 */
function topGetCustomareas(&$db,$logistics_info,$template_id,&$result=array()){
    $result['status'] = 0;
    $retval = top_customareas_get($logistics_info->key, $logistics_info->secret, $logistics_info->sessionkey,$template_id);
    if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
    {
        $result['msg'] = $retval->error_msg;
        $result['status'] = 1;
        return false;
    }else {
        \Think\Log::write("topGetCustomareas--- 获得的自定义区模板为：".print_r($retval,true),\Think\Log::DEBUG);
       /* $retval = json_encode($retval);
        $retval = json_decode($retval);*/
        $retval = $retval->result->datas->custom_area_result;
        $result['success'] = $retval[0];
    }
        return true;
}

/**
 * 查询物流商的产品类型和服务能力
 */
function newtopProductWaybill(&$db,$logistics_info,&$result=array())
{
// 	$db = Factory::getModel('Stock/StockLogisticsNo');
	$result['status'] = 0;
	

	$WaybillProductRequest = array('cp_code'=>$logistics_info->code);
	$product_info = array();
	$retval = newtop_product_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session,json_encode($WaybillProductRequest) );
	if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
	{
		$result['msg'] = $retval->error_msg;
		$result['status'] =1;
		return false;
	}else{
		$retval = $retval->product_types->waybill_product_type;
		foreach ($retval as $key => $product_item)
		{
			$product_info["{$product_item->code}"]["name"] = $product_item->name;
			foreach ($product_item->service_types->waybill_service_type as $st_key => $service_item)
			{
				$product_info["{$product_item->code}"]['service_type']["{$service_item->code}"] = $service_item->name;
			}
		}
		$result['success'] = $product_info;
		return true;
	}
	
}
/**
 * 获取发货地&CP开通状态&账户的使用情况
 */
function newtopSearchWaybill(&$db,$logistics_info,&$result=array())
{
// 	$db = Factory::getModel('Stock/StockLogisticsNo');
	$result['status'] =0;
	
	$WaybillApplyRequest =  json_encode(array('cp_code'=>$logistics_info->code));
	$retval = newtop_search_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session, $WaybillApplyRequest);
	
	if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
	{
		$result['msg'] = $retval->error_msg;
		$result['status'] =1;
		return false;
	}else{
		//用不用封装其他信息
		$resp = $retval->subscribtions->waybill_apply_subscription_info;
		foreach ($resp as $key => $value)
		{
			$branch = $value->branch_account_cols->waybill_branch_account;
			$search_info["{$value->cp_code}"]['cp_type'] = $value->cp_type;
			foreach ($branch as $branch_item)
			{
				$search["{$value->cp_code}"]["{$branch_item->branch_code}"]=array(
						'allocated_quantity'=> $branch_item->allocated_quantity,
						'branch_code'=> $branch_item->branch_code,
						'cancel_quantity'=> $branch_item->cancel_quantity,
						'print_quantity'=> $branch_item->print_quantity,
						'quantity'=> $branch_item->quantity,
						'user_id'=> $branch_item->seller_id,
						'waybill_address' => array()
				);
				foreach ($branch_item->shipp_address_cols->waybill_address  as $address )
				{
					$search["{$value->cp_code}"]["{$branch_item->branch_code}"]['waybill_address'][]=array(
							"area" => $address->area,
							"city" => $address->city,
							"province"  => $address->province,
							"address_detail" => $address->address_detail,
							
					);
				}
			}
		}
		\Think\Log::write("topSearchWaybill:".json_encode($resp), \Think\Log::DEBUG);
		$result['success']= $search;
	}
	return true;
}

/**
 * 面单号的当前状态
 */
function newtopQueryWaybill(&$db,$stockout_ids,$logistics_info,&$result)
{

	$result['status']  = 1;
	$waybill_info = array();
	$waybill_item = array();
	try{
		$mode = Factory::getModel('Stock/StockOutOrder');
		$quer_info = $mode->queryWayBillInfo($stockout_ids);
		$waybill_codes = array();
		foreach ($quer_info as $key => $value)
		{
			$waybill_codes[]=$value['logistics_no'];
		}
		$waybillDetailQueryRequest = array(
			'query_by' => "0",
			'cp_code' => $logistics_info->code,
			'waybill_codes' => $waybill_codes	
		);
		$retval = newtop_query_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session,json_encode($waybillDetailQueryRequest) );
		if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
		{
			$result['msg'] = $retval->error_msg;
			$result['status'] = 0;
			return false;
		}else{
			if(!$retval->query_success)
			{
				$waybill_info['inexistent_waybill'] = explode(",", $retval->inexistent_waybill_codes);
			}
				foreach ($retval->waybill_details as $key=> $item)
				{
					$stockout_id = $item['package_id'];
					$waybill_item["{$stockout_id}"] = array(
							"product_type" => $item->product_type,
							"shipping_branch_code" => $item->shipping_branch_code, //发货网点编码
							"create_time" => $item->create_time,//创建时间
							"status" => $item->status,//面单状态-1：已取消； 2：已分配；3：已确认打印； 4：已揽收； 5：已签
							"print_count" => $item->print_count,//打印次数
							"pickup_time" => $item->pickup_time,//揽收时间
							"cp_code" => $item->cp_code,
							"last_print_time" => $item->last_print_time,//最后一次打印时间
							"waybill_code" => $item->waybill_code,//电子面单号
							"short_address" => $item->short_address,//大头笔
							"sign_time" => $item->sign_time,//签收时间
							"real_user_id" => $item->real_user_id,//面单使用者的用户账号
							"shipping_branch_name" => $item->shipping_branch_name,//网点信息 名称 及 编码
							"consignee_branch_code" => $item->consignee_branch_code,
							"trade_order_list" => $item->trade_order_list,//原始单号
							"consignee_branch_name" => $item->consignee_branch_name,
							"consignee_phone" => $item->consignee_phone,
							"consignee_address" => $item->consignee_address,
							"shipping_address" => $item->shipping_address,
							"logistics_service_list" => $item->logistics_service_list,//物流服务能力
							"product_type" => $item->product_type,
							"package_id" => $item->package_id,//出库单号
							
							"package_center_code" => $item->package_center_code,//集包地
							"package_center_name" => $item->package_center_name,//集包地名称
							"print_config" => $item->print_config,//打印配置项
					) ;
				
				$waybill_info['waybill_itme'] = $waybill_item;
			}
		}
		\Think\Log::write("topQueryWaybill:".json_encode($retval), \Think\Log::INFO);
	}catch (\PDOException $e){
		$msg = $e->getMessage();
		$result['msg'] = "未知错误，请联系管理员";
		$result['status'] = 1;
		\Think\Log::write($msg);
		return false;
	}catch (\Exception $e){
		$msg = $e->getMessage();
		$result['msg'] = $msg;
		$result['status'] = 1;
		return false;
	}
	$result['success']=$waybill_info;
	return true;
	
}

/* 独立的接口 */
/**
 *获取ISV模板、打印项、自定义区
 * TEMPLATE/PRINT_ITEM/CUSTOM_AREA
 */
function top_ISVResource_get($key,$secret,$session,$type){
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $key;
    $top->secretKey = $secret;
    $req = new  CainiaoCloudprintIsvResourcesGetRequest;
    $req->setIsvResourceType($type);
    return $top->execute($req, $session);
}

/**
 * 获取isv定义由商家编辑的模板
 */
function top_ISVTemplates_get($key,$secret,$session){
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $key;
    $top->secretKey = $secret;
    $req = new CainiaoCloudprintIsvtemplatesGetRequest;
    return $top->execute($req, $session);
}

/**
 *获取公共模板URL
 */
function top_stdTemplates_get($key,$secret,$session){
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $key;
    $top->secretKey = $secret;
    $req = new CainiaoCloudprintStdtemplatesGetRequest;
    return $top->execute($req, $session);
}
    
/**
*获取商家可用的模板URL
*/
function top_myStdTemplates_get($key,$secret,$session){
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $key;
    $top->secretKey = $secret;
    $req = new CainiaoCloudprintMystdtemplatesGetRequest;
    return $top->execute($req, $session);
}
    
/**
*获取自定义区
*/
function top_customareas_get($key,$secret,$session,$template_id){
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $key;
    $top->secretKey = $secret;
    $req = new CainiaoCloudprintCustomaresGetRequest;
    $req->setTemplateId($template_id."");//模板template_id
    return $top->execute($req, $session);
}

/*
获取淘宝电子面单
*/
function newtop_get_waybill($appkey,$appsecret,$sessionkey,$WaybillApplyNewRequest,$src_tids,$platform_id)
{
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new CainiaoWaybillIiGetRequest;
    $req->setParamWaybillCloudPrintApplyNewRequest($WaybillApplyNewRequest);
	newtopWaybillProtectLog($top,$req,$src_tids,$platform_id);
    return $top->execute($req,$sessionkey);
}
function newtop_product_waybill($appkey,$appsecret,$sessionkey,$WaybillProductRequest)
{
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new WlbWaybillIProductRequest();
	$req->setWaybillProductTypeRequest($WaybillProductRequest);

	return $top->execute($req,$sessionkey);
}
function newtop_update_waybill($appkey,$appsecret,$sessionkey,$tradeOrderInfoCols,$src_tids)
{
    $top = new TopClient;
    $top->format = "json";
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new CainiaoWaybillIiUpdateRequest;
	$req->setParamWaybillCloudPrintUpdateRequest($tradeOrderInfoCols);
	newtopWaybillProtectLog($top,$req,$src_tids);
    return $top->execute($req,$sessionkey);
}


/*查询单号信息*/
function newtop_query_waybill($appkey,$appsecret,$sessionkey,$waybillDetailQueryRequest)
{
	$top = new TopClient();
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$top->format = 'json';
	$req = new WlbWaybillIQuerydetailRequest;
	$req->setWaybillDetailQueryRequest($waybillDetailQueryRequest);

	return $top->execute($req, $sessionkey);
}

function newtop_cancel_waybill($appkey,$appsecret,$sessionkey,$waybillCancelRequest,$src_tids)
{
	$top = new TopClient;
    $top->format = "json";
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new CainiaoWaybillIiCancelRequest;
	$req->setCpCode($waybillCancelRequest['cp_code']);
    $req->setWaybillCode($waybillCancelRequest['waybill_code']);
	newtopWaybillProtectLog($top,$req,$src_tids);
	return $top->execute($req, $sessionkey);
}

/**
 * 查询面单余额，物流公司信息
 **/
function newtop_search_waybill($appkey,$appsecret,$sessionkey,$WaybillApplyRequest)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
   $req = new WlbWaybillISearchRequest;
    $req->setWaybillApplyRequest($WaybillApplyRequest);

    return $top->execute($req,$sessionkey);
}

function newtopWaybillGetByUpdate(&$db,$logistics_info,$waybill_info,$tradeOrderInfoCol,$sender,$stockout_map,&$result,$src_tids)
{
    $send = $sender['address'];
    $send['area'] = $send['district'];
    $send['address_detail'] = $send['detail'];
    try {
           $log_str  = "top-waybill-get function:deal_exist_logistics_no 比较成功开始对接更新接口";
            
            $full_update_request['waybill_code']    = $waybill_info['logistics_no'];
            $full_update_request['cp_code']         = $logistics_info->code;
            $full_update_request = array_merge($full_update_request,$tradeOrderInfoCol);
            $retval = newtop_update_waybill($logistics_info->key,$logistics_info->secret,$logistics_info->session,json_encode($full_update_request),$src_tids);
            
            \Think\Log::write('top_update_waybill--'.print_r($retval,true),\Think\Log::INFO);
            $stock_logistics_no_db = D('Stock/StockLogisticsNo');
            if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
            {
                $error_msg = $retval->error_msg;
                $update_logistics_no_cond = array(
                    'logistics_type' => $logistics_info->logistics_type,
                    'logistics_no' => $waybill_info['logistics_no'],
                );
                $update_logistics_no_data = array('status'=>3);//更新故障的情况
                $log_str  = "top-waybill-get function :deat_exist_logistics_no API更新失败，修改相应的数据库";
                D('Stock/StockLogisticsNo')->updateLogisticsNo($update_logistics_no_data,$update_logistics_no_cond);

                $result['fail'][] = array(
                    'stock_id' => $waybill_info['stockout_id'],
                    'stock_no' => $waybill_info['stockout_no'],
                    'msg'      => $retval->error_msg,
                );
            }
            else
            {
                
                $log_str  = "top-waybill-get function :deat_exist_logistics_no API更新成功修改相应的数据库";
                $retval = json_decode($retval->print_data);
                $signature = $retval -> signature;
                $templateURL = $retval -> templateURL;
                $retval = $retval -> data;
                $update_logistics_no_cond = array(
                    'logistics_type'    => $logistics_info->logistics_type,
                    'logistics_no'      => $retval->waybillCode,
                );
                $update_logistics_no_data = array('status'=>1,'logistics_id' => $logistics_info->logistics_id,);//已使用
                $res_update_logistics_no = $stock_logistics_no_db->updateLogisticsNo($update_logistics_no_data,$update_logistics_no_cond);
                
                $receiver_dtb = @$retval->routingInfo->sortation->name;  //大头笔
                //处理集包地
                $package_code = @$retval->routingInfo->consolidation->code;
                $package_name = @$retval->routingInfo->consolidation->name;
                $query_print_info = json_decode($waybill_info['waybill_info']);

                $log_str  = "top-waybill-get function :deat_exist_logistics_no 格式化相应的返回数据";
	            $print_info = $retval;//$full_update_request;
                $print_info->sender->mobile =  $stockout_map["{$full_update_request['package_info']['id']}"]['sender_mobile'];
                $print_info->sender->phone =  $stockout_map["{$full_update_request['package_info']['id']}"]['sender_telno'];
                $print_info->sender->name =  $stockout_map["{$full_update_request['package_info']['id']}"]['sender_name'];
                $result['success'][$full_update_request['package_info']['id']] = array(
                    'shop_id'       => $waybill_info['shop_id'],
                    'src_tids'      => $waybill_info['src_tids'],
                    'logistics_no'  => $retval->waybillCode,
                    'receiver_dtb'  => $receiver_dtb,
                    'package_code'  => $package_code,
                    'package_name'  => $package_name,
                    'send_province' => $send['province'],
                    'send_city'     => $send['city'],
                    'send_district' => $send['area'],
                    'send_address'  => $send['address_detail'],
                    'waybill_info'  => $print_info,
                    'templateURL'   => $templateURL,
                    'signature'     => $signature
                );
        
            }
            return true;
    }catch (\PDOException $e){
	    $msg = $e->getMessage();
	    $result['fail'][] = array(
            'stock_id' => $waybill_info['stockout_id'],
            'stock_no' => $waybill_info['stockout_no'],
            'msg'      => "未知错误，请联系管理员",
        );
	    \Think\Log::write($log_str."topGetWayBill ".$msg);
	    return false;
	}catch (\Exception $e){
	    $msg = $e->getMessage();
	    $result['fail'][] = array(
            'stock_id' => $waybill_info['stockout_id'],
            'stock_no' => $waybill_info['stockout_no'],
            'msg'      => $msg,
        );
	    \Think\Log::write($log_str."topGetWayBill ".$msg);
	    return false;
	}
	
}
//批量获取单号
function newtopWaybillBatchDealApply(&$db,$logistics_info,$sender,$tradeOrderInfoCols,$stockoutIds,$stockout_map,&$result,$packageNos,$src_tids)
{
    $shippingAddress = $sender['address'];
    $shippingAddress['area'] = $shippingAddress['district'];
    $shippingAddress['address_detail'] = $shippingAddress['detail'];
    try {
        $WaybillApplyNewRequest = array(
            "cp_code"                =>$logistics_info->code,
            "sender"       =>$sender,
            "trade_order_info_dtos"  =>$tradeOrderInfoCols
        );
        \Think\Log::write("topWaybillBatchDealApply start :  logistics_id----{$logistics_info->logistics_id}----src_tids ---".$src_tids."---platform_id--".$logistics_info->platform_id."---".print_r($WaybillApplyNewRequest,true),\Think\Log::INFO);
       
//        \Think\Log::write("WaybillApplyNewRequest：".print_r($WaybillApplyNewRequest,true),\Think\Log::INFO);
        $log_str  = "top-waybill-get function:batch_get_logistics_no 接口开始获取单号";
        $retval = newtop_get_waybill($logistics_info->key,$logistics_info->secret,$logistics_info->session,json_encode($WaybillApplyNewRequest),$src_tids,$logistics_info->platform_id);
        \Think\Log::write("返回结果：retval--".print_r($retval,true),\Think\Log::INFO);

		if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
        {
			if($retval->code ==15){
				if(stripos($retval->sub_code,'shipping address cannot match')!==false || stripos($retval->sub_msg,'发货地址没有匹配')!==false){
					$retval->error_msg = '订单发货地址(使用仓库的地址)与申请电子面单服务时填写的发货地址不匹配,请到仓库修改';
					$stockSalesPrint_solve_way_type = 'warehouse';
				}else if(stripos($retval->sub_code,'sendPhone and sendMobile can not be both null')!==false || stripos($retval->sub_msg,'发货人固话和手机不能同时为空')!==false){
					$retval->error_msg = '订单发件人联系方式(使用仓库的联系方式)手机号和固话号不能同时为空,请到仓库修改';
					$stockSalesPrint_solve_way_type = 'warehouse';
				}else if(stripos($retval->sub_code,'sendName can not be null')!==false || stripos($retval->sub_msg,'商家请求参数不完整,发货人姓名为空')!==false){
					$retval->error_msg = '订单发货人姓名(使用仓库的联系人)不能为空,请到仓库修改';
					$stockSalesPrint_solve_way_type = 'warehouse';
				}else if(stripos($retval->sub_code,'package item name can not be null')!==false || stripos($retval->sub_msg,'包裹商品名称不能为空')!==false){
					$retval->error_msg = '该订单中有的货品名称为空';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改货品档案信息，在订单审核页面换货';
				}else if(stripos($retval->sub_code,'trade order can not apply mutil branch account')!==false || stripos($retval->sub_msg,'商家请求参数非法，同一个交易订单不能申请多个网点的面单')!==false){
					$retval->error_msg = '商家请求参数非法，同一个交易订单不能申请多个网点的面单';
				}else if(stripos($retval->sub_code,'subscribe service not found')!==false || stripos($retval->sub_msg,'面单订购服务不存在')!==false){
					$retval->error_msg = '面单订购服务不存在';
					$stockSalesPrint_solve_way = '请确认是否开启服务';
				}else if(stripos($retval->sub_code,'consigneePhone and consigneeMobile can not be both null')!==false || stripos($retval->sub_msg,'商家请求参数不完整,收货人固话和手机不能同时为空')!==false){
					$retval->error_msg = '商家请求参数不完整,收货人固话和手机不能同时为空';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面进行编辑填写收货人固话或手机号';
				}else if(stripos($retval->sub_code,'trade item name too long')!==false || stripos($retval->sub_msg,'商品名称太长')!==false){
					$retval->error_msg = '该货品的名称太长，不能超过100个字符';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改货品档案信息，在订单审核页面换货';
				}else if(stripos($retval->sub_code,'consignee_address_area_too_long') !== false || stripos($retval->sub_msg,'收货地址地区信息过长') !== false){
					$retval->error_msg = '收货地址地区信息过长';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改收货地址地区信息';
				}else if(stripos($retval->sub_code,'consignee_address_city_too_long') !== false || stripos($retval->sub_msg,'收货地址城市信息过长') !== false){
					$retval->error_msg = '收货地址城市信息过长';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改收货地址城市信息';
				}else if(stripos($retval->sub_code,'name of consignee too long') !== false || stripos($retval->sub_msg,'收货人姓名信息过长') !== false){
					$retval->error_msg = '收货人姓名信息过长';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改收货人姓名信息';
				}else if(stripos($retval->sub_code,'name of send too long') !== false || stripos($retval->sub_msg,'发货人姓名信息过长') !== false){
					$retval->error_msg = '发货人姓名信息过长';
					$stockSalesPrint_solve_way = '修改仓库的联系人姓名信息';
				}else if(stripos($retval->sub_code,'phone of send too long') !== false || stripos($retval->sub_msg,'发货人手机号信息过长') !== false){
					$retval->error_msg = '发货人手机号信息过长';
					$stockSalesPrint_solve_way = '修改仓库的联系人手机号信息';
				}else if(stripos($retval->sub_code,'mobile of send too long') !== false || stripos($retval->sub_msg,'发货人固话信息过长') !== false){
					$retval->error_msg = '发货人固话信息过长';
					$stockSalesPrint_solve_way = '修改仓库的联系人固话信息';
				}else if(stripos($retval->sub_code,'phone of consignee too long') !== false || stripos($retval->sub_msg,'收货人电话信息过长') !== false){
					$retval->error_msg = '收件人电话信息过长';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改收货人电话信息';
				}else if(stripos($retval->sub_code,'mobile of consignee too long') !== false || stripos($retval->sub_msg,'收货人固话信息过长') !== false){
					$retval->error_msg = '收件人固话信息过长';
					$stockSalesPrint_solve_way = '把该单子驳回到订单审核页面，修改收货人固话信息';
				}
			}

            $error_msg = $retval->error_msg;
            $error_msg = Obj2Arr($error_msg);
            $error_msg = $error_msg[0];
            foreach($stockoutIds as $value)
            {
				$stockout_id = $value['id'];
                //$fail_list["{$stockout_id}"] = $retval->error_msg;
				if($stockSalesPrint_solve_way_type == 'warehouse'){
					$stockSalesPrint_solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.editWarehouse('.$stockout_map["{$stockout_id}"]['warehouse_id'].')">编辑仓库信息</a>';
				}
			   $result['fail'][] = array(
                    'stock_id' => $stockout_id,
                    'stock_no' => $stockout_map["{$stockout_id}"]['stockout_no'],
                    'msg'      => $error_msg,//$retval->error_msg,
					'solve_way'=>isset($stockSalesPrint_solve_way)?$stockSalesPrint_solve_way:'',
                );
            }
        }
        else
        {
            $log_str  = "top-waybill-get function:batch_get_logistics_no 成功获取单号，开始拼接处理单号";
            $retDetails = $retval->modules->waybill_cloud_print_response;
            foreach($retDetails as $retDetail)
            {
                $object_id = $retDetail->object_id;
                $retDetail = json_decode($retDetail->print_data);
                $templateURL = $retDetail -> templateURL;
                $signature = $retDetail -> signature ;
                $retDetail = Obj2Arr($retDetail)['data'];

                //$stockout_id = $tradeOrderInfoCols[$i]['package_info']['id'];
				//$src_tids = $tradeOrderInfoCols[$i]['order_info']['trade_order_list'];

                $stockout_id = $stockoutIds[$object_id]['id'];
                $src_tids = $tradeOrderInfoCols[$object_id]['order_info']['trade_order_list'];
                //处理集包地信息
                $package_code = @$retDetail['package_center_code'];
                $package_name = @$retDetail['package_center_name'];
                $print_info = $retDetail;
                $shop_id = $stockout_map["{$stockout_id}"]['shop_id'];
                $shop_info = D('Setting/Shop')->where(array('shop_id'=>$shop_id))->find();

                $senderProvince = M('dict_province')->field('name')->where(array('province_id'=>$shop_info['province']))->find();
                $senderCity = M('dict_city')->field('name')->where(array('city_id'=>$shop_info['city']))->find();
                $senderDistrict = M('dict_district')->field('name')->where(array('district_id'=>$shop_info['district']))->find();

                $print_info['sender']['mobile'] =  $stockout_map["{$stockout_id}"]['sender_mobile'];
                $print_info['sender']['phone'] =  $stockout_map["{$stockout_id}"]['sender_telno'];
                $print_info['sender']['name'] =  $stockout_map["{$stockout_id}"]['sender_name'];

                $sender_from_type = get_config_value('stock_print_sender_from',0);
                if($sender_from_type ==1){
                    $print_info['sender']['address']['province'] = $senderProvince['name'];
                    $print_info['sender']['address']['city'] = $senderCity['name'];
                    $print_info['sender']['address']['district'] = $senderDistrict['name'];
                    $print_info['sender']['address']['detail'] = $shop_info['address'];
                }

                /*array(
                    "cp_code"                =>$logistics_info->code,
                    "sender"       =>$sender,
                    "trade_order_info_dtos"  =>$tradeOrderInfoCols[$i]);*/
                if(empty($packageNos)){
//                    \Think\Log::write(print_r($print_info,true),\Think\Log::DEBUG);
                    $result['success']["$stockout_id"] =array(
                        'shop_id'          => $stockout_map["{$stockout_id}"]['shop_id'],
                        'stockout_no'      => $stockout_map["{$stockout_id}"]['stockout_no'],
                        'src_tids'         => implode(",", $src_tids),
                        'logistics_no'     => $retDetail['waybillCode'],
                        'receiver_dtb'     => $retDetail['routingInfo']['sortation']['name'],
                        'package_code'     => $retDetail['routingInfo']['origin']['code'],//$package_code,
                        'package_name'     => $retDetail['routingInfo']['origin']['name'],//$package_name,
                        'send_province'    => $shippingAddress['province'],
                        'send_city'        => $shippingAddress['city'],
                        'send_district'    => $shippingAddress['area'],
                        'send_address'     => $shippingAddress['address_detail'],
                        'waybill_info'     => $print_info,
                        'templateURL'      => $templateURL,
                        'signature'        => $signature
                    );
                }else{
                    $packageNo = $tradeOrderInfoCols[$object_id]['package_info']['id'];
                    $result['success']["$stockout_id"]["$packageNo"] =array(
                        'shop_id'          => $stockout_map["{$stockout_id}"]['shop_id'],
                        'stockout_no'      => $stockout_map["{$stockout_id}"]['stockout_no'],
                        'src_tids'         => implode(",", $src_tids),
                        'logistics_no'     => $retDetail['waybillCode'],
                        'logistics_id'      => $logistics_info->logistics_id,
                        'receiver_dtb'     => $retDetail['routingInfo']['sortation']['name'],
                        'package_code'     => $retDetail['routingInfo']['origin']['code'],//$package_code,
                        'package_name'     => $retDetail['routingInfo']['origin']['name'],//$package_name,
                        'send_province'    => $shippingAddress['province'],
                        'send_city'        => $shippingAddress['city'],
                        'send_district'    => $shippingAddress['area'],
                        'send_address'     => $shippingAddress['address_detail'],
                        'waybill_info'     => $print_info,
                        'templateURL'      => $templateURL,
                        'signature'        => $signature
                    );
                }
            }
        }
        \Think\Log::write("topWaybillBatchDealApply end :  logistics_id----{$logistics_info->logistics_id}",\Think\Log::INFO);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        foreach($stockoutIds as $value)
        {
            $stockout_id = $value['id'];
            //$fail_list["{$stockout_id}"] = $retval->error_msg;
            $result['fail'][] = array(
                'stock_id' => $stockout_id,
                'stock_no' => $stockout_map["{$stockout_id}"]['stockout_no'],
                'msg'      => $msg,
            );
        }
        \Think\Log::write("topWaybillBatchDealApply-exception:".print_r($tradeOrderInfoCols,true));
    }
}
//
function newtopWaybillCancelBeforeGet(&$db,$waybil_info,&$cancel_list )
{
    try {
        $cancel_result = array();
        $waybill_manager = \Platform\Common\ManagerFactory::getManager('WayBill');
        $waybill_manager->cancelWayBill($db,$waybil_info['rec_id'], $waybil_info['logistics_id'], $cancel_result);
        if($cancel_result['status'] == 2)
        {
            if (isset($cancel_result['data']['fail']["{$waybil_info['rec_id']}"]))
            {
                 unset($cancel_result['data']['fail']["{$waybil_info['rec_id']}"]['logsitics_no']);
                // unset($cancel_result['data']['fail']["{$waybil_info['rec_id']}"]['trade_id']);
                 $cancel_list['fail']["{$waybil_info['rec_id']}"] = $cancel_result['data']['fail']["{$waybil_info['rec_id']}"];
            }else {
                SE("取消电子面单故障");
            }
            return false;
        }else if ($cancel_result['status'] == 1)
        {
            $cancel_list['fail']["{$waybil_info['rec_id']}"] = array(
				'trade_id' =>$waybil_info['src_order_id'],
                'stock_id' =>$waybil_info['stockout_id'],
                'stock_no' =>$waybil_info['stockout_no'],
                'msg'      =>$cancel_result['msg']
            );
            return false;
        }
        $cancel_list['success']["{$waybil_info['rec_id']}"] = $cancel_result['data']['success']["{$waybil_info['rec_id']}"];
        return true;
    } catch (\Think\Exception\BusinessLogicException $e) {
        $msg = $e->getMessage();
        $cancel_list['fail']["{$waybil_info['rec_id']}"] = array(
			'trade_id' =>$waybil_info['src_order_id'],
            'stock_id' =>$waybil_info['stockout_id'],
            'stock_no' =>$waybil_info['stockout_no'],
            'msg'      =>$msg
        );
        return false;
    }catch (Exception $e) {
        $msg = $e->getMessage();
        $cancel_list['fail']["{$waybil_info['rec_id']}"] = array(
			'trade_id' =>$waybil_info['src_order_id'],
            'stock_id' =>$waybil_info['stockout_id'],
            'stock_no' =>$waybil_info['stockout_no'],
            'msg'      =>$msg
        );
        \Think\Log::write("topWaybillCancelBeforeGet-".$msg."-".print_r($cancel_result,true));
        return false;
    }
}
//result:为获取对应的传值为
function newtopWaybillCancelUnused(&$db,&$result)
{
    try {
        
        $cancel_result = array();
        
        $logistics_no_rec_map = array();
        $log_str  = "top-waybill-get function:cancel_logistics_no 处理那些因为合并而及时取消的订单数据";
        //取消合并以后的物流编号
        $res_merge_logistics = D('Stock/StockLogisticsNo')->getSalesMergeAboutStockout();
        if(!empty($res_merge_logistics))
        {
            foreach ($res_merge_logistics as $value)
            {
                $cancel_merge_list["{$value['logistics_id']}"][] = $value['rec_id'] ;
                $logistics_no_rec_map["{$value['rec_id']}"] = $value;
            }
            foreach ($cancel_merge_list as $logistics_id => $cancel_item)
            {
                $log_str  = "top-waybill-get function:cancel_logistics_no 开始请求接口取消面单";
                $rec_ids = implode(",",$cancel_item);
                $waybill_manager = \Platform\Common\ManagerFactory::getManager('WayBill');
                $waybill_manager->cancelWayBill($db,$rec_ids, $logistics_id, $cancel_result);
                if($cancel_result['status'] == 1)
                {
                    foreach ($cancel_item as $key => $rec_id)
                    {
                        $result['fail'][$rec_id]=array(
                            'stock_id'      => $logistics_no_rec_map[$rec_id]['stockout_id'],
                            'stock_no'      => $logistics_no_rec_map[$rec_id]['stockout_no'],
                            'logistics_no'  => $logistics_no_rec_map[$rec_id]['logistics_no'],
                            'msg'           => $cancel_result['msg'],
                            'trade_id'      => $logistics_no_rec_map[$rec_id]['src_order_id'],
                        );
                    }
                }else{
                    $result = array_merge_recursive($result,$cancel_result['data']);
                }
                
            }
                
        }
        return true;        
    }catch (\PDOException $e){
        $msg = $e->getMessage();
        $result['fail']['msg'] = "未知错误，请联系管理员";
        \Think\Log::write('cancel_logistics_no-error:'.$msg);
        return false;
    }catch (\Exception $e){
        $msg = $e->getMessage();
        $result['fail']['msg'] = $msg;
        \Think\Log::write('cancel_logistics_no-error:'.$msg);
        return false;
    }

}
function newtopWaybillBatchPrint(&$db,$logistics_info,$print_check_infos,$ordernos,&$fail,&$success,$src_tids)
{
    try {
        $WaybillApplyPrintcheckRequest = array("cp_code"=>$logistics_info->code,"print_check_info_cols"=>$print_check_infos);
        \Think\Log::write(print_r($WaybillApplyPrintcheckRequest,true),\Think\Log::INFO);
        
        $retval = top_print_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session, json_encode($WaybillApplyPrintcheckRequest),$src_tids);
        
        \Think\Log::write(print_r($retval,true),\Think\Log::INFO);
        if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
        {
            $error_msg = $retval->error_msg;
            foreach($ordernos as $value)
            {
                $fail[$value] = $retval->error_msg;
            }
        }
        else {
            foreach($ordernos as $value)
            {
                $success[$value] = true;
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        foreach($ordernos as $value)
        {
            $fail[$value] = $msg;
        }
        \Think\Log::write("topWaybillBatchPrint--API批量打印校验出错：".print_r($ordernos,true).$msg);
    }
	
}
function newtopWaybillProtectLog($top,$req,$src_tids,$platform_id=1){
	global $g_jst_hch_enable;
	if (empty($g_jst_hch_enable)){
		return;
	}
 	if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| $_SERVER['SERVER_PORT'] == 443)
	{
		$sys_protocal = 'https://';
	}else{
		$sys_protocal = 'http://';
	}
	$server_name = $_SERVER['SERVER_NAME'];
	$request_path = $_SERVER['REQUEST_URI'];
	$params['url'] = $sys_protocal.$server_name.$request_path;
	$params['tradeIds'] = $src_tids;
	$params['sendTo'] = $top->gatewayUrl.'?api_request='.$req->getApiMethodName();
    if($platform_id ==1){
        logx($params['tradeIds']);
        hchRequest('http://gw.ose.aliyun.com/event/sendOrder',$params);
    }
}
