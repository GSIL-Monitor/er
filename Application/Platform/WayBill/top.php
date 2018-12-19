<?php
use Common\Common\Factory;
use Stock\Model\StockLogisticsNoModel;
use Common\Common\UtilTool;
include_once (APP_PATH . 'Platform/Common/api_error.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIProductRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIFullupdateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIPrintRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIQuerydetailRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillICancelRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillISearchRequest.php');

/**
 * 获取电子面单
 */
function topGetWaybill(&$db,$stockout_ids,$logistics_info,&$result)
{
    
	\Think\Log::write("topGetWaybill start {$stockout_ids}",\Think\Log::INFO);
	$result = array(
	    'status' => 0,
	    'msg'   => 'success',
	    'data'  =>array()  
	);
	try {
	    
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
	        $packed_stockout_info["$warehouseid"]['sender_province']   = $row['sender_province'];
	        $packed_stockout_info["$warehouseid"]['sender_city']       = $row['sender_city'];
	        $packed_stockout_info["$warehouseid"]['sender_district']   = $row['sender_district'];
	        $packed_stockout_info["$warehouseid"]['sender_address']    = $row['sender_address'];
	        
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['platform_id']     = $row['platform_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['app_key']         = $row['app_key'];  //
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['shop_id']         = $row['shop_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['src_tids']        = $row['src_tids'];
// 	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['src_order_no']    = $row['src_order_no'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_id']     = $row['stockout_id'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_no']     = $row['stockout_no'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_province'] = $receiver_province;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_city']   = $receiver_city;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_district'] = $receiver_district;
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_address'] = $row['receiver_address'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_name']   = $row['receiver_name'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_tel'] =
	        $row['receiver_mobile'] == ''?$row['receiver_telno']:$row['receiver_mobile'];
	        $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['package_items'][]= array(
	            'count' => (int)$row['num'],
	            'item_name' => $row['goods_name']
	        );
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['cod_amount'] = $row['cod_amount'];
            $packed_stockout_info["$warehouseid"]['order']["{$row['stockout_id']}"]['delivery_term'] = $row['delivery_term'];
	    }
	    //这个配置是否可以在各个模块下共用
	    
	    $result['cancel']  = array();
	    $result['success'] = array();
	    $result['fail']    = array();
	    $result['error']   = array();
	    //批量处理电子面单申请
		$stockout_map = topWaybillArrangeBeforeGet($db,$logistics_info,$packed_stockout_info,$result);
		$log_str  = "topGetWaybill 失败电子面单更新数据库start";
		if(!empty($result['fail']))
	    {
	        waybill_error_handle($logistics_info,$result);
		}
		$log_str  = "topGetWaybill 成功获取电子面单更新数据库start";
	    if(!empty($result['success']))
	    {
            $packageNos = array();
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
		if(topWaybillCancelUnused($db,$result['cancel']))
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
	}catch (\PDOException $e){
	    $msg = $e->getMessage();
	    $result = array(
	        'status' => 1,
	        'msg' => "未知错误，请联系管理员",
	    );
		\Think\Log::write($log_str."topGetWayBill ".$msg);
	    return false;
	}catch (\Think\Exception\BusinessLogicException $e){
	    $msg = $e->getMessage();
	    $result = array(
	        'status' => 1,
	        'msg' => $msg,
	    );
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
function topWaybillArrangeBeforeGet(&$db,$logistics_info,$apply_info,&$result)
{
	$platform_code = C('platform_code');
    $stockout_map = array();
    foreach($apply_info as $warehouse)
    {
    
        $shippingAddress = array();             //发货地址，即仓库的地址信息
        $tradeOrderInfoCols = array();          //订单信息，即收货人地址信息
        $shippingAddress['province']       = $warehouse['sender_province'];
        $shippingAddress['city']           = $warehouse['sender_city'];
        $shippingAddress['area']           = $warehouse['sender_district'];
        $shippingAddress['address_detail'] = $warehouse['sender_address'];
         
        $orderlist = $warehouse['order'];
        $count = 0;
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
                    'shop_id'      =>$order['shop_id']
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
                    'consignee_name'       => $order['receiver_name'],
                    'consignee_phone'      => $order['receiver_tel'],
                    'order_channels_type'  => isset($platform_code[$order['platform_id']])?$platform_code[$order['platform_id']]:"OTHERS",//获取订单来源平台对应编码配置//订单来源平台编码【非】 ,
                    'trade_order_list'     => explode(",", $order['src_tids']),
                    'consignee_address'    => array(
                        'province'       => $order['receiver_province'],
                        'city'           => $order['receiver_city'],
                        'area'           => $order['receiver_district'],
                        'address_detail' => $order['receiver_address']
                    ),
                    'package_items'        => $order['package_items'],
                    'product_type'         => $logistics_info->product_type,
                    'real_user_id'         => $seller_id,//区分对待  在物流配置之前必须通过授权的店铺赋值过来或是search查询过来
                    'package_id'           => $stockout_id,//已出库单号id值作为包裹号
                );
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
				if($order['delivery_term'] == 2){//货到付款
                    $tradeOrderInfoCol['logistics_service_list'] = array(
                        "service_code" => "SVC-COD",
                        "service_value4_json" => array(
                            "value" => $order['cod_amount'],
                            "currency" => "CNY",
                            "payment_type" => "CASH"
                            ),
                        );
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
                        'send_district'     => $shippingAddress['area'],
                        'send_address'      => $shippingAddress['address_detail'],
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
                        
                        if(!topWaybillCancelBeforeGet($db,$waybill_no_info[0],$result['cancel']))
                        {
                            array_push($result['fail'], $result['cancel']['fail']["{$waybill_no_info[0]['rec_id']}"]);
                            continue;
                        }
					}else
                    {
						$update_src_tids = $new_waybill_info['src_tids'];
                        topWaybillGetByUpdate($db,$logistics_info,$waybill_no_info[0],$tradeOrderInfoCol,$shippingAddress,$result,$update_src_tids);
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
            $tradeOrderInfoCols[] = $tradeOrderInfoCol;
			$get_src_tids = trim($order['src_tids'],',').',';
            if(++$count == 10)     //批量获取面单一次最多支持10条
            {
				$get_src_tids = trim($get_src_tids,',');
                topWaybillBatchDealApply($db,$logistics_info,$shippingAddress,$tradeOrderInfoCols,$stockout_map,$result,$get_src_tids);
                $count = 0;
				$get_src_tids = '';
                $tradeOrderInfoCols = array();
                $ordernos = array();
            }
		}
        $log_str  = "topWaybillArrangeBeforeGet 批量获取单据信息，已跳出循环";
        if(!empty($tradeOrderInfoCols))     //不足10条的一次获取
        {
			$get_src_tids = trim($get_src_tids,',');
			topWaybillBatchDealApply($db,$logistics_info,$shippingAddress,$tradeOrderInfoCols,$stockout_map,$result,$get_src_tids);
		}
	}
	return $stockout_map;
}
/**
 * 取消电子面单
 * $rec_ids  string 1,2,4,
 */
function topCancelWaybill(&$db,$rec_ids,$logistics_info,&$result=array())
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
					'real_user_id'     => $cancel_item->platform_id == 1?$cancel_item->user_id:$logistics_info->user_id,
					'trade_order_list' => explode(",",$cancel_item->src_tids),
					'waybill_code'     => $cancel_item->logistics_no,
					'package_id'       => $cancel_item->stockout_id,
			);
			$src_tids = $cancel_item->src_tids;
			$retval = top_cancel_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session, json_encode($waybillCancelRequest),$src_tids);
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

/**
 * 查询物流商的产品类型和服务能力
 */
function topProductWaybill(&$db,$logistics_info,&$result=array())
{
// 	$db = Factory::getModel('Stock/StockLogisticsNo');
	$result['status'] = 0;
	

	$WaybillProductRequest = array('cp_code'=>$logistics_info->code);
	$product_info = array();
	$retval = top_product_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session,json_encode($WaybillProductRequest) );
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
function topSearchWaybill(&$db,$logistics_info,&$result=array())
{
// 	$db = Factory::getModel('Stock/StockLogisticsNo');
	$result['status'] =0;
	
	$WaybillApplyRequest =  json_encode(array('cp_code'=>$logistics_info->code));
	$retval = top_search_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session, $WaybillApplyRequest);
	
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
			$search["{$value->cp_code}"]['cp_type'] = $value->cp_type == 1?'直营':'加盟';
			$search["{$value->cp_code}"]['cp_code'] = $value->cp_code;
			foreach ($branch as $branch_item)
			{
				$search["{$value->cp_code}"]['branch']["{$branch_item->branch_code}"]=array(
						'allocated_quantity'=> $branch_item->allocated_quantity,
						'branch_code'=> $branch_item->branch_code,
						'branch_name'=> @$branch_item->branch_name,
						'cancel_quantity'=> $branch_item->cancel_quantity,
						'print_quantity'=> $branch_item->print_quantity,
						'quantity'=> $branch_item->quantity,
						'user_id'=> $branch_item->seller_id,
						'waybill_address' => array()
				);
				foreach ($branch_item->shipp_address_cols->waybill_address  as $address )
				{
					$search["{$value->cp_code}"]['branch']["{$branch_item->branch_code}"]['waybill_address'][]=array(
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
function topAllSearchWaybill(&$db,$auth_shops,&$result=array())
{
	$result['status'] =0;
    $waybill_info_list = array();
	foreach($auth_shops as $k => $auth_shop)
	{
        $WaybillApplyRequest =  json_encode(array('cp_code'=>''));
        $retval = top_search_waybill($auth_shop->key, $auth_shop->secret, $auth_shop->session, $WaybillApplyRequest);

        if(API_RESULT_OK != topErrorTest($retval,$db,(int)$auth_shop->shop_id))
        {
            $result['msg'] = $retval->error_msg;
            $result['status'] =1;
            return false;
        }else{
            //用不用封装其他信息
            $resp = isset($retval->subscribtions->waybill_apply_subscription_info)?$retval->subscribtions->waybill_apply_subscription_info:array();
            foreach ($resp as $key => $value)
            {
                $branch = $value->branch_account_cols->waybill_branch_account;
                $search["{$value->cp_code}"]['cp_type'] = $value->cp_type == 1?'直营':'加盟';//是否直营
                $search["{$value->cp_code}"]['cp_code'] = $value->cp_code;//物流公司编码
                foreach ($branch as $branch_item)
                {
                    $search["{$value->cp_code}"]['branch']["{$branch_item->branch_code}"]=array(
                        'allocated_quantity'=> $branch_item->allocated_quantity,//已用单数
                        'branch_code'=> $branch_item->branch_code,
                        'branch_name'=> @$branch_item->branch_name,//网点名称
                        'cancel_quantity'=> $branch_item->cancel_quantity,
                        'print_quantity'=> $branch_item->print_quantity,
                        'quantity'=> $branch_item->quantity,//可用单数
                        'user_id'=> $branch_item->seller_id,
                        'waybill_address' => array()
                    );
                    foreach ($branch_item->shipp_address_cols->waybill_address  as $address )
                    {
                        $search["{$value->cp_code}"]['branch']["{$branch_item->branch_code}"]['waybill_address'][]=array(//网点下的地址
                            "area" => $address->area,
                            "city" => $address->city,
                            "province"  => $address->province,
                            "address_detail" => $address->address_detail,

                        );
                    }
                }
            }
            \Think\Log::write("topSearchWaybill:shop_id-".$auth_shop->shop_id.'   resp:'.json_encode($resp), \Think\Log::DEBUG);
            if(empty($resp) || empty($search)){
                continue;
            }else{
                $waybill_info_list[$auth_shop->shop_id] = array('shop_name'=>$auth_shop->shop_name,'waybill_info'=>$search);
            }

        }
    }
    $result['success'] = $waybill_info_list;
	return true;
}
/**
 * 打印面单前的校验
 */
function topPrintWaybill(&$db,$stockout_ids,$logistics_info,&$result)
{
	$result = array(
	    'status' => 0,
	    'msg' =>'success',
	    'data' => array()
	);
	\Think\Log::write('topPrintWaybill--'.$logistics_info->logistics_id.'--begin',\Think\Log::DEBUG);
	try {
	
		$model = D('Stock/StockOutOrder');
		$stockout_order_info      = $model->getStockoutInfoBeforeApplyWaybill($stockout_ids,true);
		$stockout_order           = $stockout_order_info;
		if(empty($stockout_order))
		{
			$result['msg'] = '没查询到订单信息';
			$result['status']=1;
			return false;
		}
		//放到数组里，获得分组效果（以仓库分组）
		$PushInfo = array();
		$stockout_id_to_no = array();
		foreach($stockout_order as $row )
		{
		    $stockout_id_to_no["{$row['stockout_id']}"] =$row['stockout_no'];
			splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
			$warehouseid = $row['warehouse_id'];
			$PushInfo["$warehouseid"]['sender_province'] = $row['sender_province'];
			$PushInfo["$warehouseid"]['sender_city'] = $row['sender_city'];
			$PushInfo["$warehouseid"]['sender_district'] = $row['sender_district'];
			$PushInfo["$warehouseid"]['sender_address'] = $row['sender_address'];
			$PushInfo["$warehouseid"]['order'][] = array(
					'stockout_id'          => $row['stockout_id'],
					'stockout_no'          => $row['stockout_no'],//接口未使用
					'src_order_no'         => $row['src_order_no'],//接口未使用
					'shop_id'              => $row['shop_id'],
					'platform_id'          => $row['platform_id'],
					'app_key'              => $row['app_key'],
					'src_tids'             => $row['src_tids'],
					'receiver_province'    => $receiver_province,
					'receiver_city'        => $receiver_city,
					'receiver_district'    => $receiver_district,
					'receiver_address'     => $row['receiver_address'],
					'receiver_name'        => $row['receiver_name'],
					'receiver_dtb'         => $row['receiver_dtb'],
					'logistics_no'         => $row['logistics_no'],
					'package_adr'          => @$row['package_adr'],
					'package_wd'           => @$row['package_wd'],
					'receiver_tel'         => $row['receiver_mobile'] == ''?$row['receiver_telno']:$row['receiver_mobile']
			);
		}
		
		topWaybillArrangeBeforePrint($db,$logistics_info,$PushInfo,$result);
		
	}catch(\PDOException $e){
		$msg = $e->getMessage();
		$result['msg'] = "未知错误，请联系管理员";
		$result['status'] =1;
		\Think\Log::write("topPrintWaybill---".$msg);
	}catch(\Exception $e){
		$msg = $e->getMessage();
		$result['msg'] = $msg;
		$result['status'] =1;
		\Think\Log::write('topPrintWaybill--'.$msg);
	}
	if(!empty($result['data']['fail']))
	{
	    $result['status'] = 2;
		waybill_error_print_handle($logistics_info->sid,$model,$result['data']['fail']);
		$temp_fail = $result['data']['fail'];
		$format_fail = array();
		foreach ($temp_fail as $key => $value){
		    $format_fail[] = array(
		        'stock_id' => $key,
		        'stock_no' => $stockout_id_to_no[$key],
		        'msg'      => $value
		    );
		}
		$result['data']['fail'] = $format_fail;
	}
	return true;
}
function topWaybillArrangeBeforePrint(&$db,$logistics_info, $Print_info, &$result)
{
    $print_fail = array();
    $print_success = array();
    $platform_code = C('platform_code');
    foreach($Print_info as $warehouse)
    {
        $shippingAddress = array();             //发货地址，即仓库的地址信息
        $print_check_infos = array();          //订单信息，即收货人地址信息
        	
        $shippingAddress['province']         = $warehouse['sender_province'];
        $shippingAddress['city']             = $warehouse['sender_city'];
        $shippingAddress['area']             = $warehouse['sender_district'];
        $shippingAddress['address_detail']   = $warehouse['sender_address'];
        $orderlist = $warehouse['order'];
        $count = 0;
		$src_tids = '';
        foreach($orderlist as $order)
        {

            try {
            
                $order_shop = array();
                $order_shop['app_key']      = $order['app_key'];
                $order_shop['shop_id']      = $order['shop_id'];
                $order_shop['platform_id']  = $order['platform_id'];
                \Platform\Manager\Manager::parse_app_key($order_shop);
                if(!isset($order_shop->user_id) && ($order_shop->platform_id == 1))
                {
                    $print_fail["{$order['stockout_id']}"] = "面单使用者的用户帐号不存在";
                    \Think\Log::write("topWaybillArrangeBeforePrint-出库单号为：{$order['stockout_id']}----面单使用者的用户帐号不存在");
                    continue;
                }
                $seller_id = $order_shop->platform_id == 1?$order_shop->user_id:$logistics_info->user_id;
                $print_check_info = array(
                    'consignee_name'          => $order['receiver_name'],
                    'consignee_phone'         => $order['receiver_tel'],
                    'waybill_code'            => $order['logistics_no'],
                    'order_channels_type'     => isset($platform_code[$order['platform_id']])?$platform_code[$order['platform_id']]:"OTHERS",
                    'trade_order_list'        => explode(",", $order['src_tids']),
                    'consignee_address'       => array(
                        'province'      => $order['receiver_province'],
                        'city'          => $order['receiver_city'],
                        'area'          => $order['receiver_district'],
                        'address_detail'=> $order['receiver_address']
                    ),
                    'shipping_address'        => $shippingAddress,
                    'product_type'            => $logistics_info->product_type,
                    'real_user_id'            => $seller_id,//区分对待  在物流配置之前必须通过授权的店铺赋值过来或是search查询过来,
                    'short_address'           => $order['receiver_dtb']
                );
				//护城河日志必传
				$src_tids = $order['src_tids'].',';
                if($order['package_adr'] != '')
                {   
                    $print_check_info['package_center_name'] = @$order['package_adr'];
                }
                if($order['package_wd'] != '')
                {
                    $print_check_info['package_center_code'] = @$order['package_wd'];
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $print_fail["{$order['stockout_id']}"] = $msg;
                \Think\Log::write("topWaybillArrangeBeforePrint-{$order['stockout_id']}出错".$msg);
                continue;
            }	
            $order_no               = $order['src_order_no'];
            $ordernos["$order_no"]  = $order['stockout_id'];
            $print_check_infos[]    = $print_check_info;
            if(++$count == 10)     //批量获取面单一次最多支持10条
            {
				$src_tids = trim($src_tids,',');
                topWaybillBatchPrint($db,$logistics_info, $print_check_infos, $ordernos, $print_fail, $print_success,$src_tids);
                $count = 0;
				$src_tids = '';
                $print_check_infos = array();
                $ordernos = array();
            }
        }
        if(!empty($print_check_infos))     //不足10条的一次获取
        {
			$src_tids = trim($src_tids,',');
            topWaybillBatchPrint($db,$logistics_info, $print_check_infos, $ordernos, $print_fail, $print_success,$src_tids);
        }
    }
    $result['data'] = array(
      'fail' =>$print_fail,
       'success' => $print_success  
    );
}
/**
 * 面单号的当前状态
 */
function topQueryWaybill(&$db,$stockout_ids,$logistics_info,&$result)
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
		$retval = top_query_waybill($logistics_info->key, $logistics_info->secret, $logistics_info->session,json_encode($waybillDetailQueryRequest) );
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
/*
	获取淘宝电子面单
*/
function top_get_waybill($appkey,$appsecret,$sessionkey,$WaybillApplyNewRequest,$src_tids,$platform_id)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new WlbWaybillIGetRequest();
    $req->setWaybillApplyNewRequest($WaybillApplyNewRequest);
	topWaybillProtectLog($top,$req,$src_tids,$platform_id);
    return $top->execute($req,$sessionkey);
}
function top_product_waybill($appkey,$appsecret,$sessionkey,$WaybillProductRequest)
{
	$top = new TopClient();
	$top->format = 'json';
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$req = new WlbWaybillIProductRequest();
	$req->setWaybillProductTypeRequest($WaybillProductRequest);

	return $top->execute($req,$sessionkey);
}
function top_update_waybill($appkey,$appsecret,$sessionkey,$tradeOrderInfoCols,$src_tids)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new WlbWaybillIFullupdateRequest();
	$req->setWaybillApplyFullUpdateRequest($tradeOrderInfoCols);
	topWaybillProtectLog($top,$req,$src_tids);
    return $top->execute($req,$sessionkey);
}

/*打印校验*/
function top_print_waybill($appkey,$appsecret,$sessionkey,$WaybillApplyPrintcheckRequest,$src_tids)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
   $req = new WlbWaybillIPrintRequest;
    $req->setWaybillApplyPrintCheckRequest($WaybillApplyPrintcheckRequest);
	topWaybillProtectLog($top,$req,$src_tids);
    return $top->execute($req,$sessionkey);
}

/*查询单号信息*/
function top_query_waybill($appkey,$appsecret,$sessionkey,$waybillDetailQueryRequest)
{
	$top = new TopClient();
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$top->format = 'json';
	$req = new WlbWaybillIQuerydetailRequest;
	$req->setWaybillDetailQueryRequest($waybillDetailQueryRequest);

	return $top->execute($req, $sessionkey);
}

function top_cancel_waybill($appkey,$appsecret,$sessionkey,$waybillCancelRequest,$src_tids)
{
	$top = new TopClient();
	$top->appkey = $appkey;
	$top->secretKey = $appsecret;
	$top->format = 'json';
	$req = new WlbWaybillICancelRequest;
	$req->setWaybillApplyCancelRequest($waybillCancelRequest);
	topWaybillProtectLog($top,$req,$src_tids);
	return $top->execute($req, $sessionkey);
}

/**
 * 查询面单余额，物流公司信息
 **/
function top_search_waybill($appkey,$appsecret,$sessionkey,$WaybillApplyRequest)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
   $req = new WlbWaybillISearchRequest;
    $req->setWaybillApplyRequest($WaybillApplyRequest);

    return $top->execute($req,$sessionkey);
}

function topWaybillGetByUpdate(&$db,$logistics_info,$waybill_info,$tradeOrderInfoCol,$send,&$result,$src_tids)
{
    try {
           $log_str  = "top-waybill-get function:deal_exist_logistics_no 比较成功开始对接更新接口";
            
            $full_update_request['waybill_code']    = $waybill_info['logistics_no'];
            $full_update_request['cp_code']         = $logistics_info->code;
            $full_update_request = array_merge($full_update_request,$tradeOrderInfoCol);
            
            $retval = top_update_waybill($logistics_info->key,$logistics_info->secret,$logistics_info->session,json_encode($full_update_request),$src_tids);
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
                
                $update_logistics_no_cond = array(
                    'logistics_type'    => $logistics_info->logistics_type,
                    'logistics_no'      => $retval->waybill_apply_update_info->waybill_code,
                );
                $update_logistics_no_data = array('status'=>1,'logistics_id' => $logistics_info->logistics_id,);//已使用
                $res_update_logistics_no = $stock_logistics_no_db->updateLogisticsNo($update_logistics_no_data,$update_logistics_no_cond);
                
                $receiver_dtb = @$retval->waybill_apply_update_info->short_address;  //大头笔
                //处理集包地
                $package_code = @$retval->waybill_apply_update_info->package_center_code;
                $package_name = @$retval->waybill_apply_update_info->package_center_name;
                $query_print_info = json_decode($waybill_info['waybill_info']);
                if(isset($query_print_info->config))
                {
                    $print_config = $query_print_info->config;
                }
                else 
                {
                    $print_config = "";
                    //\Think\Log::write("top-waybill-get function :deat_exist_logistics_no".print_r($update_logistics_no_cond,true)." 中waybill_info缺少config信息  ");
                }
                $log_str  = "top-waybill-get function :deat_exist_logistics_no 格式化相应的返回数据";
	            $print_info = array(
    // 	            'ali_waybill_cp_logo_up'			=>  '',
    // 	            'ali_waybill_cp_logo_down'			=>  '',
    	            'ali_waybill_short_address'			=>  $retval->waybill_apply_update_info->short_address,
    	            'ali_waybill_package_center_name'	=>  @$retval->waybill_apply_update_info->package_center_name,
    	            'ali_waybill_package_center_code'	=>  @$retval->waybill_apply_update_info->package_center_code,
    	            'ali_waybill_send_name'				=>  $retval->waybill_apply_update_info->trade_order_info->send_name,
    	            'ali_waybill_send_phone'			=>  $retval->waybill_apply_update_info->trade_order_info->send_phone,
    	            'ali_waybill_shipping_address'		=>  $send['address_detail'],
    	            'ali_waybill_consignee_name'		=>  $retval->waybill_apply_update_info->trade_order_info->consignee_name,
    	            'ali_waybill_consignee_phone'		=>  $retval->waybill_apply_update_info->trade_order_info->consignee_phone,
    	            'ali_waybill_consignee_address'		=>  $retval->waybill_apply_update_info->trade_order_info->consignee_address->address_detail,
    	            'ali_waybill_waybill_code'			=>  $retval->waybill_apply_update_info->waybill_code,
    	            'ali_waybill_shipping_branch_name'	=>  $retval->waybill_apply_update_info->shipping_branch_name,
    	            'ali_waybill_shipping_branch_code'	=>  $retval->waybill_apply_update_info->shipping_branch_code,
    	            'ali_waybill_ext_send_date'			=>  date("m/d/Y"),
    	            'ali_waybill_ext_sf_biz_type'		=>  '',
    	            'ali_waybill_shipping_address_city'	=>  $send['city'],
	                'config'                            =>  $print_config
	           );
                
                $result['success'][$full_update_request['package_id']] = array(
                    'shop_id'       => $waybill_info['shop_id'],
                    'src_tids'      => $waybill_info['src_tids'],
                    'logistics_no'  => $retval->waybill_apply_update_info->waybill_code,
                    'receiver_dtb'  => $receiver_dtb,
                    'package_code'  => $package_code,
                    'package_name'  => $package_name,
                    'send_province' => $send['province'],
                    'send_city'     => $send['city'],
                    'send_district' => $send['area'],
                    'send_address'  => $send['address_detail'],
                    'waybill_info'  => json_encode($print_info)
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
function topWaybillBatchDealApply(&$db,$logistics_info,$shippingAddress,$tradeOrderInfoCols,$stockout_map,&$result,$src_tids)
{
    try {
        $WaybillApplyNewRequest = array(
            "cp_code"                =>$logistics_info->code,
            "shipping_address"       =>$shippingAddress,
            "trade_order_info_cols"  =>$tradeOrderInfoCols
        );
        \Think\Log::write("topWaybillBatchDealApply start :  logistics_id----{$logistics_info->logistics_id}".print_r($WaybillApplyNewRequest,true),\Think\Log::INFO);
        
        \Think\Log::write("WaybillApplyNewRequest：".print_r($WaybillApplyNewRequest,true),\Think\Log::INFO);
        $log_str  = "top-waybill-get function:batch_get_logistics_no 接口开始获取单号";
//         \Think\Log::write(urldecode(json_encode(urlencodArr($WaybillApplyNewRequest))));
        $retval = top_get_waybill($logistics_info->key,$logistics_info->secret,$logistics_info->session,json_encode($WaybillApplyNewRequest),$src_tids,$logistics_info->platform_id);
        \Think\Log::write("返回结果：retval--".print_r($retval,true),\Think\Log::INFO);

		if(API_RESULT_OK != topErrorTest($retval,$db,(int)$logistics_info->shop_id))
        {
            if($retval->code ==15){
                if(stripos($retval->sub_code,'shipping address cannot match')!==false || stripos($retval->sub_msg,'发货地址没有匹配')!==false){
                    $retval->error_msg = '订单发货地址(使用仓库的地址)与申请电子面单服务时填写的发货地址不匹配';
					$solve_way = '请到仓库修改';
                }else if(stripos($retval->sub_code,'sendPhone and sendMobile can not be both null')!==false || stripos($retval->sub_msg,'发货人固话和手机不能同时为空')!==false){
                    $retval->error_msg = '订单发件人联系方式(使用仓库的联系方式)手机号和固话号不能同时为空';
					$solve_way = '请到仓库修改';
                }else if(stripos($retval->sub_code,'package item name can not be null')!==false || stripos($retval->sub_msg,'包裹商品名称不能为空')!==false){
					$retval->error_msg = '包裹商品名称不能为空，该订单中有的货品名称为空';
					$solve_way = '请到货品档案添加该货品的名称';
				}else if(stripos($retval->sub_code,'trade order can not apply mutil branch account')!==false || stripos($retval->sub_msg,'商家请求参数非法，同一个交易订单不能申请多个网点的面单')!==false){
					$retval->error_msg = '商家请求参数非法，同一个交易订单不能申请多个网点的面单';
					$solve_way = '未知错误,请联系管理员';
				}else if(stripos($retval->sub_code,'subscribe service not found')!==false || stripos($retval->sub_msg,'面单订购服务不存在')!==false){
					$retval->error_msg = '面单订购服务不存在';
					$solve_way = '请确认是否开启服务';
				}else if(stripos($retval->sub_code,'consigneePhone and consigneeMobile can not be both null')!==false || stripos($retval->sub_msg,'商家请求参数不完整,收货人固话和手机不能同时为空')!==false){
					$retval->error_msg = '商家请求参数不完整,收货人固话和手机不能同时为空';
					$solve_way = '请到CRM客户档案去填写固话或者手机号';
				}

            }

            $error_msg = $retval->error_msg;
            foreach($tradeOrderInfoCols as $value)
            {
                $stockout_id = $value['package_id'];
                //$fail_list["{$stockout_id}"] = $retval->error_msg;
                $result['fail'][] = array(
                    'stock_id' => $stockout_id,
                    'stock_no' => $stockout_map["{$stockout_id}"]['stockout_no'],
                    'msg'      =>$retval->error_msg,
					'solve_way'=>$solve_way,
                );
            }
		}
        else
        {
            $log_str  = "top-waybill-get function:batch_get_logistics_no 成功获取单号，开始拼接处理单号";
            $retDetails = $retval->waybill_apply_new_cols->waybill_apply_new_info;
            $retDetails = Obj2Arr($retDetails);
            foreach($retDetails as $retDetail)
            {
                $stockout_id    = $retDetail['trade_order_info']['package_id'];
                $src_tids       = $retDetail['trade_order_info']['trade_order_list']['string'];
                // 			sort($src_tids);
                //处理集包地信息
                $package_code = @$retDetail['package_center_code'];
                $package_name = @$retDetail['package_center_name'];
                $print_info = array(
                // 	            'ali_waybill_cp_logo_up'			=>  '',
                // 	            'ali_waybill_cp_logo_down'			=>  '',
                    'ali_waybill_short_address'			=>  $retDetail['short_address'],
                    'ali_waybill_package_center_name'	=>  @$retDetail['package_center_name'],
                    'ali_waybill_package_center_code'	=>  @$retDetail['package_center_code'],
                    'ali_waybill_send_name'				=>  @$retDetail['trade_order_info']['send_name'],
                    'ali_waybill_send_phone'			=>  @$retDetail['trade_order_info']['send_phone'],
                    'ali_waybill_shipping_address'		=>  $shippingAddress['address_detail'],
                    'ali_waybill_consignee_name'		=>  $retDetail['trade_order_info']['consignee_name'],
                    'ali_waybill_consignee_phone'		=>  $retDetail['trade_order_info']['consignee_phone'],
                    'ali_waybill_consignee_address'		=>  $retDetail['trade_order_info']['consignee_address']['address_detail'],
                    'ali_waybill_waybill_code'			=>  $retDetail['waybill_code'],
                    'ali_waybill_shipping_branch_name'	=>  $retDetail['shipping_branch_name'],
                    'ali_waybill_shipping_branch_code'	=>  $retDetail['shipping_branch_code'],
                    'ali_waybill_ext_send_date'			=>  date("m/d/Y"),
                    'ali_waybill_ext_sf_biz_type'		=>  '',
                    'ali_waybill_shipping_address_city'	=>  $shippingAddress['city'],
                    'config'                            =>  $retDetail['print_config'],
                );
                \Think\Log::write(print_r($print_info,true),\Think\Log::DEBUG);
                $result['success']["$stockout_id"] =array(
                    'shop_id'          => $stockout_map["{$stockout_id}"]['shop_id'],
                    'stockout_no'      => $stockout_map["{$stockout_id}"]['stockout_no'],
                    'src_tids'         => implode(",", $src_tids),
                    'logistics_no'     => $retDetail['waybill_code'],
                    'receiver_dtb'     => $retDetail['short_address'],
                    'package_code'     => $package_code,
                    'package_name'     => $package_name,
                    'send_province'    => $shippingAddress['province'],
                    'send_city'        => $shippingAddress['city'],
                    'send_district'    => $shippingAddress['area'],
                    'send_address'     => $shippingAddress['address_detail'],
                    'waybill_info'     => json_encode($print_info)
                );
            }
        }
		\Think\Log::write("topWaybillBatchDealApply end :  logistics_id----{$logistics_info->logistics_id}",\Think\Log::INFO);
    } catch (Exception $e) {
        $msg = $e->getMessage();
        foreach($tradeOrderInfoCols as $value)
        {
            $stockout_id = $value['package_id'];
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
function topWaybillCancelBeforeGet(&$db,$waybil_info,&$cancel_list )
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
function topWaybillCancelUnused(&$db,&$result)
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
function topWaybillBatchPrint(&$db,$logistics_info,$print_check_infos,$ordernos,&$fail,&$success,$src_tids)
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
function topWaybillProtectLog($top,$req,$src_tids,$platform_id=1){
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