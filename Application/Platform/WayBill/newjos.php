<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/jos/request/logistics/LogisticsAdapter.php');
require_once(ROOT_DIR . '/Task/global.php');
require_once(ROOT_DIR . '/WayBill/util.php');
function logistics_get_waybill(&$db,$logistics_info,$stockout_ids,&$message,$status = 55)
{
     
	global $top_app_config;
	$message = array(
	    'status' => 0,
	    'msg'   => 'success',
	    'data'  =>array(
			'fail'=>array(),
			'success'=>array(),
		),  
	);
    if($stockout_ids == '')
        return true;
	//$result = array('success'=>array(),'fail'=>array());
	$logistics_code = $logistics_info->code;
    $sid = $logistics_info->sid;
   // $logistics_info = json_decode($logistics_info->app_key);
	// logx("logistics_info--".print_r($logistics_info,true),$sid);
    //顺丰付款方式和快件类别
    if ($logistics_info->bill_type==9)
    {
        if($logistics_info->type_sf == 0)
            $logistic_type = '1';
        else if($logistics_info->type_sf == 1)
            $logistic_type = '2';
        else if($logistics_info->type_sf == 2)
            $logistic_type = '3';
        else if($logistics_info->type_sf == 3)
            $logistic_type = '7';
        else if($logistics_info->type_sf == 4)
            $logistic_type = '37';
        else if($logistics_info->type_sf == 5)
            $logistic_type = '38';
        else if($logistics_info->type_sf == 6)
            $logistic_type = '6';
        else if($logistics_info->type_sf == 7)
            $logistic_type = '5';
        else if($logistics_info->type_sf == 8)
            $logistic_type = '14';
        else if($logistics_info->type_sf == 9)
            $logistic_type = '102';
        else if ($logistics_info->type_sf == 10)
            $logistic_type = '111';
        else if($logistics_info->type_sf == 11)
            $logistic_type = '125';
        else if($logistics_info->type_sf == 12)
            $logistic_type = '13';
        else
            $logistic_type = '1';        
        if($logistics_info->pay_type == 0)
            $pay_type='1';
        else if($logistics_info->pay_type == 1)
            $pay_type='2';
        else if($logistics_info->pay_type == 2)
            $pay_type='3';
        else
            $pay_type='1';
    }
    $construct_data = array();
    //京东阿尔法电子面单 和菜鸟电子面单需要到店铺中获取授权信息
    if ($logistics_info->bill_type == 9 )
    {
        $shop_api = getShopAuth($sid, $db, $logistics_info->shop_id);
        if(!$shop_api)
        {
			$message['status'] = 1;
            $message['msg'] = '店铺未授权，请检查授权信息!';
            logx("stockout_ids : {$stockout_ids} , shop[$logistics_info->shop_id] not auth! logistics_get_waybill",$sid);
            $db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$error_msg}' where stockout_id in ({$stockout_ids})");

            return false;
        }
        $construct_data['appkey'] = $shop_api->key;
        $construct_data['appsecret'] = $shop_api->secret;
        $construct_data['sessionkey'] = $shop_api->session; 
    }

    $order_info = $db->query("SELECT st.cod_amount,st.delivery_term,so.stockout_id,so.receiver_area,
                                     so.receiver_name,so.receiver_address, so.receiver_mobile,so.receiver_telno,
                                     so.receiver_province,so.receiver_city,so.receiver_district,so.receiver_zip,
                                     so.calc_weight,so.goods_total_amount,so.stockout_no,sw.contact AS sender_name,
                                     sw.address AS sender_address,sw.telno AS sender_telno,
                                     sw.mobile AS sender_mobile,sw.zip AS sender_zip,sw.province AS sender_province,
                                     sw.city AS sender_city,sw.district AS sender_district,sw.warehouse_id,sw.name,
                                     sw.division_id,st.src_tids,st.platform_id,st.shop_id,
                                     IFNULL(dp.province_id,0) AS sender_province_id,IFNULL(dc.city_id,0) AS sender_city_id,IFNULL(dd.district_id,0) AS sender_district_id
                                FROM stockout_order so
                                LEFT JOIN cfg_warehouse sw ON sw.warehouse_id = so.warehouse_id 
                                LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id
                                LEFT JOIN dict_province dp ON  dp.name = sw.province
                                LEFT JOIN dict_city dc ON  dc.name = sw.city AND dc.province_id = dp.province_id
                                LEFT JOIN dict_district dd ON  dd.name = sw.district AND dd.city_id = dc.city_id
                               WHERE so.stockout_id IN ({$stockout_ids}) AND so.status = {$status} ");
    
    if(!$order_info)
    {
        $message['status'] = 1;
        $message['msg']  = '查询订单信息出错';
        return false;
    }
    if($order_info->num_rows<1)
    {
       $message['status'] = 1;
       $message['msg']  = '请在单据打印界面查看出库单状态是否“获取面单号”状态';
        return false;
    }
	
    while($row = $db->fetch_array($order_info))
    {
		
        $stockout_id = $row['stockout_id'];
        $stockout_no = $row['stockout_no'];
		if($row['delivery_term'] == 2){
			logx("stockout no:{$stockout_no}, 京东阿尔法电子面单不支持货到付款", $sid);
			$message['data']['fail'][$stockout_id] = array(
				'stock_id'=>$stockout_id,
				'stock_no'=>$stockout_no,
				'msg'=>'京东阿尔法电子面单不支持货到付款'
			);
			continue;
		}
        splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
        //收件人地址
        $receiver_address = array(
            'province_id'   =>  $row['receiver_province'],
            'province'      =>  $receiver_province,
            'city_id'       =>  $row['receiver_city'],
            'city'          =>  $receiver_city,
            'district_id'   =>  $row['receiver_district'],
            'district'      =>  $receiver_district,
            'address'   =>  $row['receiver_address'],
			'detail'    =>$row['receiver_address'],
			'town'    =>$row['receiver_address'],
        );
        //发货地址
        $sender_address = array(
            'province_id'   =>  $row['sender_province_id'],
            'province'      =>  $row['sender_province'],
            'city_id'       =>  $row['sender_city_id'],
            'city'          =>  $row['sender_city'],
            'district_id'   =>  $row['sender_district_id'],
            'district'      =>  $row['sender_district'],
            'address'       =>  $row['sender_address'],
			'detail'          =>  $row['sender_address'],
			'town'          =>  $row['sender_address'],
        );
        //销售平台编码
        if ($row['platform_id'] == 3)
        {
            $row['salePlatform'] = '0010001';
        }
        else if ($row['platform_id'] == 1 || $row['platform_id'] == 2) 
        {
            $row['salePlatform'] = '0010002';
        }        
        else
        {
            $row['salePlatform'] = '0010003';
        }
		$substr_id = $row['stockout_id'];
		$platformOrdertid = str_replace(',','',$row['src_tids']);
		$platformOrdertid = $substr_id."@".$sid.$platformOrdertid;
        $row['platformOrderNo'] = substr($platformOrdertid,0,30);
        $data['order_data'] = $row;
        $data['order_data']['sender_address'] = $sender_address;
        $data['order_data']['receiver_address'] = $receiver_address;
        $data['order_data']['send_type'] = isset($logistics_info->send_type) && $logistics_info->send_type==1?2:1;
        $data['logistics_auto'] = Obj2Arr($logistics_info);
        $data['logistics_auto']['logistic_type'] = $logistic_type;
        $data['logistics_auto']['pay_type'] = $pay_type;
		$data['logistics_auto']['logistics'] = $logistics_code;

        //多商品的，拼接商品信息，用逗号分隔
        /* $goods_names = $db->query("SELECT goods_name FROM stockout_order_detail WHERE stockout_id = {$stockout_id}");
        $cargoName = '';
        if(!$goods_names)
        {
            $params['cargoName'] = 'goods';
        }
        else
        {
            $cargoName = ''; 
            while($goods = $db->fetch_array($goods_names))
            {
                $cargoName .= $goods['goods_name'].",";
            }
            $cargoName = substr($cargoName,0,strlen($cargoName)-1);
        }
        $data['order_data']['goodsinfo'] = $cargoName; */
        //未修改收件人信息的 从单号池获取单号
        $logistics_datas = $db->query_result("select logistics_no,receiver_dtb from stock_logistics_no where status = 5 and logistics_id = {$logistics_info->logistics_id} and stockout_id = {$stockout_id} and  logistics_type = {$logistics_info->logistics_type} and type=0 and receiver_info = CONCAT('{$row['receiver_area']}','{$row['receiver_address']}','{$row['receiver_name']}','{$row['receiver_mobile']}') limit 1");
        $waybill_info = array(
					'recipient'=>array(
						'address'=>$receiver_address,
						'destination_code'=>$row['receiver_dtb'],
					),
					'sender'=>array(
						'address'=>$sender_address,
					),
					'waybillCode'=>$logistics_code,
				);
		if($logistics_datas!='')
        {
            $message['data']['success']["$stockout_id"] = array(
				'shop_id'=>$row['shop_id'],
				'src_tids'=> $row['src_tids'],				
				'stockout_no'=>$stockout_no,
				'logistics_id'=>$logistics_info->logistics_id ,
				'logistics_no'=>$logistics_datas['logistics_no'],
				'receiver_dtb'=>$logistics_datas['receiver_dtb'],
				'waybill_info'=>$waybill_info,
				'routingInfo'=>array(
						'consolidation'=>array(
							'name'=>'',
							'code'=>'',
						),
						'origin'=>array('code'=>$logistics_code,'name'=>$logistics_info->logistics_name),
						'sortation'=>array('name'=>$logistics_datas['receiver_dtb']),
					),
				'have_print_data'=>0,
				'package_wd'=>'',
				'package_adr'=>'',
				'printInfo'=>'',
				'send_province'=>'',
				'send_city'=>'',
				'send_district'=>'',
				'send_address'=>'',
				'print_datas'=>''
				);
        }
        else 
        {   
			
			//调用接口获取单号
            $logistics_adapter = new LogisticsAdapter( $logistics_info->bill_type,$construct_data);
            $result = $logistics_adapter->sendRequest(LOGISTICS_GET_WAYBILL, $data, $sid);
            $send   = $logistics_adapter->getSendParams();
            $resv   = $logistics_adapter->getReceived();
            $code = $result['code'];
            $error_msg = $result['error_msg'];
           // $error_msg = substr($error_msg,0,200);
           // $error_msg = $db->escape_string($error_msg);
            //获取单号失败
            if ($code != 0) 
            {
                if($code<0)
                {
                    logx("stockout no:{$stockout_no}, 系统级别错误system_error:{$error_msg}! logistics_get_waybill", $sid);
                    $message['data']['fail'][$stockout_id] = array(
                                'stock_id'=>$stockout_id,
								'stock_no'=>$stockout_no,
                                'msg'=>$error_msg
                            );
                }
                else
                {
                    logx("stockout no:{$stockout_no}, 应用级别错误app_error:{$error_msg}! logistics_get_waybill", $sid);
                    $message['data']['fail'][$stockout_id] = array(
                                'stock_id'=>$stockout_id,
								'stock_no'=>$stockout_no,
                                'msg'=>$error_msg
                            );
                }
            }
            else //成功
            {
                $result_info = $result['rev_info'];
                //京东阿尔法需要调用接口获取大头笔
                if ($logistics_info->bill_type == 9)
                {
                    $waybill_code    = $result_info['waybillCode'];
                    $platformOrderNo = $result_info['platformOrderNo'];
                    //调用接口获取大头笔
                    $providerCode = $logistics_code;
                    $logistics_dtb = array(
                        'waybillCode'  =>  $waybill_code,
                        'providerCode' =>  $providerCode,
                        'stockoutNo'   =>  $stockout_no
                    );
                    $result = logistics_get_dtb($db,$sid,$logistics_adapter,$logistics_dtb,$error_msg);          
					if ($result['flag'] == false)
                    {
                        logx("stockout no:{$stockout_no}, 获取大头笔失败:{$result['error_msg']}! logistics_get_dtb", $sid);
                    }
                    $result = $result['dtb_info'];
                    $bigShotName = isset($result['bigShotName'])?$result['bigShotName']:'';
                    $secondSectionCode = isset($result['secondSectionCode'])?$result['secondSectionCode']:'';
                    $thirdSectionCode = isset($result['thirdSectionCode'])?$result['thirdSectionCode']:'';
                    $gatherCenterName = isset($result['gatherCenterName'])?$result['gatherCenterName']:'';
                    $gatherCenterCode = isset($result['gatherCenterCode'])?$result['gatherCenterCode']:'';
                    $receiver_dtb = $bigShotName." ".$secondSectionCode." ".$thirdSectionCode;
                }			
				$waybill_info = array(
					'recipient'=>array(
						'address'=>$receiver_address,
						'destination_code'=>$receiver_dtb,
					),
					'sender'=>array(
						'address'=>$sender_address,
					),
					'waybillCode'=>$logistics_code,
				);
                //拼接数组 调用存储过程
                $message['data']['success'][$stockout_id] = array(
						'shop_id'=>$row['shop_id'],
						'src_tids'=> $row['src_tids'],
						'stockout_no'=>$stockout_no,
						'logistics_id'=>$logistics_info->logistics_id,
						'logistics_no'=>$waybill_code,
						'receiver_dtb'=>$receiver_dtb,
						'waybill_info'=>$waybill_info,
						'routingInfo'=>array(
							'consolidation'=>array(
								'name'=>$gatherCenterName,
								'code'=>$gatherCenterCode,
							),
							'origin'=>array('code'=>$logistics_code,'name'=>$logistics_info->logistics_name),
							'sortation'=>array('name'=>$receiver_dtb),
						),
						'signature'=>'',
						'send_province'=>$sender_address['province'],
						'send_city'=>$sender_address['city'],
						'send_district'=>$sender_address['district'],
						'send_address'=>$sender_address['address'],
					);
            }
        }
    }
    if(!empty($message['data']['fail']))
    {
        waybill_error_handle($logistics_info,$message['data']);
    }
    if(!empty($message['data']['success']))
    {
        waybill_success_handle($logistics_info,'',$message['data']);
    }
    if($error_msg != '')
    {
        $error_msg = $db->escape_string($error_msg);
        return false;
    }
    else
        return true;
}
//获取以仓库分类的物流
function logistics_get_waybills(&$db,$logistics_info,$stockout_ids,&$error_msg,$status = 55)
{
	global $top_app_config;
	if($stockout_ids == '')
		return true;
	$sid = $logistics_info['SID'];
	
	$logistics_auto = json_decode($logistics_info['app_key']);//店铺授权
	$cpCode = '';//物流编码
	$seller_id_logistics = '';//物流授权店铺
	$logistics_adapter = null;//适配器
	$construct_data = array();//物流构造数据
	
	if($logistics_info['bill_type']==2)
	{
		//云栈需要进行店铺是否授权判断
		$shop_api = getShopAuth($sid, $db, (int)$logistics_auto->shop_id);
		if(!$shop_api)
		{
			
			$error_msg = '店铺未授权，请检查授权信息!';
			logx("stockout_ids : {$stockout_ids} , shop[$logistics_auto->shop_id] not auth! logistics_get_waybill",$sid);
			$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$error_msg}' where stockout_id in ({$stockout_ids})");

			return false;
		}
		/*
		$construct_data['appkey']             = 'test';
		$construct_data['appsecret']          = 'test';
		$construct_data['sessionkey']         = '610070021675c46a34501a5082cdc0cbbb829d7785431992054718218';
		*/
		//授权信息
		$construct_data['appkey']             = $shop_api->key;
		$construct_data['appsecret']          = $shop_api->secret;
		$construct_data['sessionkey']         = $shop_api->session;
		$construct_data['logistics_auto_key'] = $logistics_auto;
		
		//判断物流授权店铺是否用淘宝店铺授权
		if($construct_data['appkey'] != $top_app_config['app_key'])
		{
			$error_msg = '云栈物流只能使用淘宝店铺授权';
			logx("stockout_ids : {$stockout_ids} , shop[$logistics_auto->shop_id] not auth! logistics_get_waybill",$sid);
			$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$error_msg}' where stockout_id in ({$stockout_ids})");
			return false;
		}
		
		//淘宝面单获取 物流编码
		$cpCode = $logistics_auto->logistics;
		
		$logistics_adapter = new LogisticsAdapter( $logistics_info['bill_type'],$construct_data);
		
		//获取物流授权店铺的 user_id
		$seller_id_logistics = $db->query_result_single("SELECT account_id FROM sys_shop WHERE shop_id={$logistics_auto->shop_id}");
			
		if(!isset($seller_id_logistics) || $seller_id_logistics=='' || $seller_id_logistics==0)
		{
			logx("start to get logsitics user_id,shop_id:{$logistics_auto->shop_id}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
			logx("******************");
			//获取user_id 的时候使用
			$logistics_info['shop_id']  = $logistics_auto->shop_id;
			$result = logistics_user_id($db,$sid,$logistics_adapter,$logistics_info,1,$error_msg);
			$seller_id_logistics = $result['user_info']['user_id'];
			if ($result['flag'] == false || $seller_id_logistics=='' )
			{
				$error_msg = $result['error_msg'];
				logx("logistics_id:{$logistics_info['logistics_id']}, 获取物流授权店铺user_id失败:{$result['error_msg']}! logistics_user_id", $sid);
				$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$result['error_msg']}' where stockout_id in ({$stockout_ids})");
				return false;
			}
		}
	}
	
	$shoptype = array(0=>'OTHERS',1=>'TB',2=>'TB',3=>'JD',4=>'PP',5=>'AMAZON',6=>'YHD',7=>'DD',8=>'OTHERS',9=>'1688',10=>'OTHERS',11=>'OTHERS',12=>'OTHERS',13=>'SN',14=>'WPH',15=>'YX',16=>'JM');
	$order_info = $db->query("SELECT st.cod_amount,st.delivery_term,so.stockout_id,so.receiver_area,
									 so.receiver_name,so.receiver_address, so.receiver_mobile,so.receiver_telno,
									 so.receiver_province,so.receiver_city,so.receiver_district,so.receiver_zip,
									 so.calc_weight,so.stockout_no,sw.contact AS sender_name,
									 sw.address AS sender_address,sw.telno AS sender_telno,
									 sw.mobile AS sender_mobile,sw.zip AS sender_zip,sw.province AS sender_province,
									 sw.city AS sender_city,sw.district AS sender_district,sw.warehouse_id,
									 sw.division_id,sod.goods_name,sod.num,st.src_tids,st.platform_id,st.shop_id
								FROM stockout_order so
								LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id 
								LEFT JOIN sys_warehouse sw ON sw.warehouse_id = so.warehouse_id 
								LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id 
							   WHERE so.stockout_id IN ({$stockout_ids}) AND so.status = {$status} AND so.logistics_no = '' ");
	
	if(!$order_info)
    {
        $error_msg = '查询订单信息出错';
        return false;
    }
    if($order_info->num_rows<1)
    {
        $error_msg = '请在单据打印界面查看出库单状态是否“获取面单号”状态';
        return false;
    }
	//是否重复获取单号
	$noRepeatNo=0;
	if(isset($logistics_info['un_repeat_logistics_no']))
	{
		$noRepeatNo = $logistics_info['un_repeat_logistics_no'];
	}
	else
	{
		$noRepeatNo = 0;
	}
	
	//放到数组里，获得分组效果（以仓库分组）    
	$PushInfo = array();
	$count = 0;
	$stockout_no = '';
	while($row = $db->fetch_array($order_info))
	{
		//每个订单最多100 个商品
		if($stockout_no != '' && $stockout_no != $row['stockout_no'])
		{
			$count = 0;
		}

		if(++$count>100)
		{
			continue;
		}
		
		//这个主要是菜鸟用来分组的 
		$stockout_no = $row['stockout_no'];
		splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
		$warehouseid = $row['warehouse_id'];
		$PushInfo["$warehouseid"]['warehouseid']    = $warehouseid;
		$PushInfo["$warehouseid"]['sender_province']= $row['sender_province'];
		$PushInfo["$warehouseid"]['sender_city']    = $row['sender_city'];
		$PushInfo["$warehouseid"]['sender_district']= $row['sender_district'];
		$PushInfo["$warehouseid"]['division_id']    = $row['division_id'];
		$PushInfo["$warehouseid"]['sender_address'] = $row['sender_address'];
		$PushInfo["$warehouseid"]['sender_name']    = $row['sender_name'];
		$PushInfo["$warehouseid"]['sender_phone']   = $row['sender_mobile'] == ''?$row['sender_telno']:$row['sender_mobile'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['shop_id']     = $row['shop_id'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_id'] = $row['stockout_id'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['stockout_no'] = $row['stockout_no'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['src_tids']    = $row['src_tids'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['platform_id'] = $row['platform_id'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['address']     = array(
				'province' => $receiver_province,
				'city' => $receiver_city,
				'district' => $receiver_district,
				'detail' => $row['receiver_address']
				);
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_name']= $row['receiver_name'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['weight']       = $row['calc_weight']*1000;
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['receiver_tel'] = 
		$row['receiver_mobile'] == ''?$row['receiver_telno']:$row['receiver_mobile'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['delivery_term'] = $row['delivery_term'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['cod_amount']    = $row['cod_amount'];
		$PushInfo["$warehouseid"]['order']["{$row['stockout_id']}"]['items'][]         = array(
				'count' => (int)ceil($row['num']),
				'name' => $row['goods_name']
				);
	}
	
	if(empty($PushInfo))
	{
		$error_msg = '没查询到订单信息';
		return false;
	}
	$order_fail = array();
	$order_success = array();
	$warehouseinfo ='';
	//适用于用菜鸟的物流  以仓库分类的物流 比如菜鸟配 菜鸟智选物流
	foreach($PushInfo as $warehouse)
	{
		
		$shippingAddress = array();             //发货地址，即仓库的地址信息
		$tradeOrderInfoCols = array();          //获取单号需要信息
		
		$batch_tradeOrderInfoCols = array();             //发货地址，即仓库的地址信息
		
		
		//订单信息，即收货人地址信息
		$shippingAddress['address']['province']    = $warehouse['sender_province'];
		$shippingAddress['address']['city']        = $warehouse['sender_city'];
		$shippingAddress['address']['district']    = $warehouse['sender_district']==='无'?'':$warehouse['sender_district'];
		$shippingAddress['address']['detail']      = $warehouse['sender_address'];
		
		$shippingAddress['name']                   = $warehouse['sender_name'];
		$shippingAddress['phone']                  = $warehouse['sender_phone'];
		
		
		$count = 0;
		$orderlist = $warehouse['order'];
		
		foreach($orderlist as $order)
		{	
			$object_id = strpos($order['stockout_no'],'-')==0?$order['stockout_no']:strstr($order['stockout_no'],'-',true);
			
			$stockout_no                                   = $order['stockout_no'];
			$stockout_id                                   = $order['stockout_id'];
			
			$order['tradeOrder']['object_id']              = ($noRepeatNo==0?$object_id:$order['stockout_no']).'@'.$sid;
			
			/*先从物流单号池里取单号*/
			$logistics_no = '';
			if(!isset($logistics_info['un_repeat_logistics_no']) || $logistics_info['un_repeat_logistics_no'] == 0 )
			{
				$logistics_no = $db->query_result_single("SELECT logistics_no FROM stock_logistics_no WHERE logistics_type={$logistics_info['logistics_type']} AND logistics_id={$logistics_info['logistics_id']} AND send_province='{$shippingAddress['address']['province']}' AND send_city = '{$shippingAddress['address']['city']}' AND send_district = '{$warehouse['sender_district']}' AND send_address = '{$shippingAddress['address']['detail']}' AND status = 5 AND type = 0 AND stockout_id={$order['stockout_id']} limit 1");
			}
			
			if($logistics_no && $logistics_no != '')
			{
				
				$order['tradeOrder']['waybill_code'] = $logistics_no;
				$order['tradeOrder']['cp_code']      = $cpCode;
				
				//走更新接口
				logx("start to update waybill,stockout_no:{$order['stockout_no']}  logistics_no:{$logistics_no} ",$sid);
				
				$result = $logistics_adapter->sendRequest(LOGISTICS_UPDATE_WAYBILL,$order, $sid,$db);
				$send   = $logistics_adapter->getSendParams();
				$resv   = $logistics_adapter->getReceived();
				
				logx("stockout_no:{$stockout_no} send:    ".print_r(json_decode($send['post_params']['param_waybill_cloud_print_update_request']),true),$sid);
				logx("stockout_no:{$stockout_no} receive: ".print_r($resv,true),$sid);
				
				$code = $result['code'];
				$error_msg = $result['error_msg'];
				$error_msg = substr($error_msg,0,200);
				$error_msg = $db->escape_string($error_msg);
				if ($code != 0) 
				{
					if($code<0)
					{
						logx("stockout no:{$stockout_no}, 系统级别错误system_error:{$error_msg}! logistics_get_waybills", $sid);
						$order_fail[$stockout_id] = array(
									'stockout_id'=>$stockout_id,
									'error_msg'=>$error_msg
								);
					}
					else
					{
						logx("stockout no:{$stockout_no}, 应用级别错误app_error:{$error_msg}! logistics_get_waybills", $sid);
						$order_fail[$stockout_id] = array(
									'stockout_id'=>$stockout_id,
									'error_msg'=>$error_msg
								);
					}
				}
				else//更新单号成功
				{
					$db->query("UPDATE stock_logistics_no SET status = 1,v_trade_no = '{$order['stockout_no']}' WHERE logistics_type={$logistics_info['logistics_type']} AND logistics_id={$logistics_info['logistics_id']} AND logistics_no = '{$logistics_no}' ");
					$result_info = $result['rev_info'];
					$order_success[$stockout_id] = array(
							'stockout_no'=>$stockout_no,
							'logistics_id'=>$logistics_info['logistics_id'],
							'logistics_no'=>$logistics_no,
							'receiver_dtb'=>$result_info['receiver_dtb'],
							'have_print_data'=>'1',
							'package_wd'=>$result_info['package_code'],
							'package_adr'=>$result_info['package_name'],
							'printInfo' => $result_info['printInfo'],
							'send_province'=>$shippingAddress['address']['province'],
							'send_city'=>$shippingAddress['address']['city'],
							'send_district'=>$shippingAddress['address']['district'],
							'send_address'=>$shippingAddress['address']['detail'],
							'print_datas'=>$result_info['print_datas']
							);
				}			
				continue;
			}
			
			//用来批量获取单号的时候区分数据
			$order_no=($noRepeatNo==0?$object_id:$order['stockout_no']).'@'.$sid;
			$ordernos["$order_no"] = $order['stockout_id'];
			
			if($logistics_info['bill_type']==2)
			{
				//提示错误的时候直接提示填写的发货仓库地址信息 不使用接口返回的了
				$warehouseinfo = '&'.$warehouse['sender_province'].'&'.$warehouse['sender_city'].'&'.($warehouse['sender_district']==='无'?'':$warehouse['sender_district'])."&".$warehouse['sender_address'];
		
				//获取模板的url
				logx("start to logistics search template url,shop_id:{$logistics_auto->shop_id}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
			
				$result               = logistics_search_template_url($db,$sid,$logistics_adapter,$logistics_info,$cpCode,$error_msg);
				$template_url  		  = $result['template_url_info']['template_url'];
				$template_url_cp_code = $result['template_url_info']['template_url_cp_code'];
				$template_id          = $result['template_url_info']['template_id'];
				
				if ($result['flag'] == false || $template_url=='' )
				{
					$error_msg = $result['error_msg'];
						logx("logistics_id:{$logistics_info['logistics_id']}, 获取物流模板url失败:{$result['error_msg']}! template_url", $sid);
						
						$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$result['error_msg']}' where stockout_id in ({$stockout_ids})");
						return false;
				}
				
				//判断使用单号
				$src_tids_no = ($order['src_tids'] === ''?$object_id .'@'.$sid :explode(',',$order['src_tids'])[0]);
				
				$platform_type = false;
				if(isset($shoptype[(int)$order['platform_id']]) && $shoptype[(int)$order['platform_id']] === 'TB')
				{
					$platform_type = true;
				}
				
				//判断订单店铺是发货类型  获取订单的 user_id
				$trade_shop_id = $order['shop_id'];//发货店铺user_id
				if($platform_type )
				{
					$account_id = $db->query_result_single("SELECT account_id FROM sys_shop WHERE  shop_id ={$trade_shop_id}");
					if(!$account_id || $account_id=='' || $account_id==0)
					{
						logx("start to get logsitics user_id,shop_id:{$logistics_auto->shop_id}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
						logx("###################");
						$logistics_info['shop_id']  = $trade_shop_id;
						$result = logistics_user_id($db,$sid,$logistics_adapter,$logistics_info,0,$error_msg);
						$account_id = $result['user_info']['user_id'];
						if ($result['flag'] == false || $account_id=='' )
						{
							$error_msg = $result['error_msg'];
							logx("logistics_id:{$logistics_info['logistics_id']}, 获取物流授权店铺user_id失败:{$result['error_msg']}! logistics_user_id", $sid);
								$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$result['error_msg']}' where stockout_id in ({$stockout_ids})");
								return false;
						}
						
					}
				}
	
				$order['tradeOrder']['order_channels_type'] = isset($shoptype[(int)$order['platform_id']])  ?$shoptype[(int)$order['platform_id']]:'OTHERS';
				$order['tradeOrder']['trade_order_list']    = $platform_type ? $src_tids_no : $object_id.'@'.$sid;
				$order['tradeOrder']['id']                  = ($noRepeatNo==0?$object_id:$order['stockout_no']).'@'.$sid;
				$order['tradeOrder']['template_url']        = $template_url.'';
				$order['tradeOrder']['user_id']             = $platform_type?$account_id:$seller_id_logistics;
				$tradeOrderInfoCols['shippingAddress']      = $shippingAddress; 
				$tradeOrderInfoCols['orders'][] = $order;
				//淘宝每次获最多批量 10 个订单获取单号 因为需要 按仓库来批量获取单号
				if(++$count == 10) 
				{
					//数据  每10 个 保存一下
					$batch_tradeOrderInfoCols[] = array(
								'ordernos'          =>$ordernos,
								'tradeOrderInfoCols'=>$tradeOrderInfoCols
								);   
					$count = 0;
					$tradeOrderInfoCols = array();
					$ordernos = array();
					
				}	
			}
			
		}
		//不满足 10 单的物流
		if(!empty($tradeOrderInfoCols))     //不足10条的一次获取
		{
			$batch_tradeOrderInfoCols[] = array(
								'ordernos'          =>$ordernos,
								'tradeOrderInfoCols'=>$tradeOrderInfoCols
								);
			$tradeOrderInfoCols = array();
		}
		
		
		//通过接口 获取单号
		foreach($batch_tradeOrderInfoCols AS $batch_tradeOrderInfoCol)
		{
			$ordernos = $batch_tradeOrderInfoCol['ordernos'];
			$tradeOrderInfoCols = $batch_tradeOrderInfoCol['tradeOrderInfoCols'];
			
			//走获取单号接口
			logx("start to get logsitics get waybills,warehouseid:{$warehouseid}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
			
			$result = $logistics_adapter->sendRequest(LOGISTICS_GET_WAYBILL,$tradeOrderInfoCols, $sid,$db);
			$send   = $logistics_adapter->getSendParams();
			$resv   = $logistics_adapter->getReceived();
			
			logx("send:    ".print_r(json_decode($send['post_params']['param_waybill_cloud_print_apply_new_request']),true),$sid);
			logx("receive: ".print_r($resv,true),$sid);
			
			$code = $result['code'];
			$error_msg = $result['error_msg'];
			$error_msg = substr($error_msg,0,200);
			$error_msg = $db->escape_string($error_msg);
			if ($code != 0) 
			{
				if($code<0)
				{
					logx("stockout no:{$stockout_no}, 系统级别错误system_error:{$error_msg}! logistics_get_waybills", $sid);
					foreach($ordernos as $value)
					{
						$order_fail[$value] = array(
								'stockout_id'=>$value,
								'error_msg'  =>$error_msg.$warehouseinfo
								);				
					}
				}
				else
				{
					logx(" 应用级别错误app_error:{$error_msg}! logistics_get_waybills", $sid);
					if($logistics_info['bill_type']==2)
					{
						if(stripos($error_msg,'templateURL url not found'))
						{
							
							$judge_exist_table = $db->query("SHOW TABLES LIKE '". "cfg_top_print_template"."'");
							if($judge_exist_table->num_rows==1)
							{
								$template_url = $db->query("delete from cfg_top_print_template where template_id = {$template_id} ");
							}
							else
							{
								//清空数据库的模板信息
								$db->query("delete from sys_setting where `key`='{$template_url_cp_code}' ");
							}
						}
					}
					foreach($ordernos as $value)
					{
						$order_fail[$value] = array(
								'stockout_id'=>$value,
								'error_msg'  =>$error_msg.$warehouseinfo
								);				
					}
				}
			}
			else
			{
				$result_infos = $result['rev_info'];
				foreach($result_infos AS $result_info)
				{
					$stockout_id = $ordernos["{$result_info['object_id']}"];
					$stockout_no = $db->query_result_single("SELECT stockout_no FROM stockout_order WHERE stockout_id={$stockout_id}");
					$order_success["$stockout_id"] =array(
						'stockout_no'=>$stockout_no,
						'logistics_id'=>$logistics_info['logistics_id'],
						'logistics_no'=>$result_info['waybill_code'],
						'receiver_dtb'=>$result_info['receiver_dtb'],
						'have_print_data'=>'1',
							'package_wd'=>$result_info['package_code'],
							'package_adr'=>$result_info['package_name'],
							'printInfo' => $result_info['printInfo'],
							'send_province'=>$shippingAddress['address']['province'],
							'send_city'=>$shippingAddress['address']['city'],
							'send_district'=>$shippingAddress['address']['district'],
							'send_address'=>$shippingAddress['address']['detail'],
							'print_datas'=>$result_info['print_datas']
							);
				}
			}
				
		}
	}

	
	
	if(!empty($order_fail))
	{
		error_handle($sid,$db,$order_fail);
	}
	if(!empty($order_success))
	{
		success_handle($sid,$db,$order_success,$logistics_info);
	}

	if($error_msg != '')
	{
		$error_msg = $db->escape_string($error_msg);
		return false;
	}
		
	else
		return true;
}

//多物流获取单号
function logistics_multi_get_waybill(&$db, $logistics_info, $stockout_id, &$error_msg)
{
	if($stockout_id == '')
	{
		$error_msg = '查询订单信息出错';
		return false;
	}
	$sid = $logistics_info['SID'];
	$uid = $logistics_info['UID'];
	$logistics_auto = json_decode($logistics_info['app_key']);
	
	$cpCode = '';//物流编码
	$seller_id_logistics = '';//物流授权店铺
	$logistics_adapter = null;//适配器
	$construct_data = array();//物流构造数据
	
	
	if($logistics_info ['bill_type'] == 2)
	{
		global $top_app_config;
		//店铺授权
		$shop_api = getShopAuth($sid, $db, (int)$logistics_auto->shop_id);
		if(!$shop_api)
		{
			$error_msg = '店铺未授权';
			return false;
		}
		
		$construct_data['appkey']             = $shop_api->key;
		$construct_data['appsecret']          = $shop_api->secret;
		$construct_data['sessionkey']         = $shop_api->session;
		$construct_data['logistics_auto_key'] = $logistics_auto;
		
		//判断物流授权店铺是否用淘宝店铺授权
		if($construct_data['appkey'] != $top_app_config['app_key'])
		{
			$error_msg = '云栈物流只能使用淘宝店铺授权';
			logx("stockout_ids : {$stockout_ids} , shop[$logistics_auto->shop_id] not auth! logistics_multi_get_waybill",$sid);
			$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$error_msg}' where stockout_id in ({$stockout_ids})");
			return false;
		}
		
		//淘宝面单获取 物流编码
		$cpCode = $logistics_auto->logistics;
	}
	$logistics_adapter = new LogisticsAdapter( $logistics_info['bill_type'],$construct_data);
	if($logistics_info ['bill_type'] == 2)
	{
		//获取物流授权店铺的 user_id
		$seller_id_logistics = $db->query_result_single("SELECT account_id FROM sys_shop WHERE shop_id={$logistics_auto->shop_id}");
			
		if(!isset($seller_id_logistics) || $seller_id_logistics=='' || $seller_id_logistics==0)
		{
			logx("start to get logsitics user_id,shop_id:{$logistics_auto->shop_id}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
			logx("******************");
			//获取user_id 的时候使用
			$logistics_info['shop_id']  = $logistics_auto->shop_id;
			$result = logistics_user_id($db,$sid,$logistics_adapter,$logistics_info,1,$error_msg);
			$seller_id_logistics = $result['user_info']['user_id'];
			if ($result['flag'] == false || $seller_id_logistics=='' )
			{
				$error_msg = $result['error_msg'];
				logx("logistics_id:{$logistics_info['logistics_id']}, 获取物流授权店铺user_id失败:{$result['error_msg']}! logistics_user_id", $sid);
				$db->query("update stockout_order set `status`=GREATEST(55,status),error_info = '{$result['error_msg']}' where stockout_id in ({$stockout_ids})");
				return false;
			}
		}
	}
	
	$row = $db->query_result("SELECT so.stockout_id,so.receiver_area,so.receiver_name,so.receiver_address,
									 so.receiver_mobile,so.receiver_telno,so.receiver_province,so.receiver_city,
									 so.receiver_district,so.receiver_zip,so.calc_weight,so.src_order_id,so.stockout_no,
									 st.cod_amount, st.delivery_term, sod.goods_name, sod.num, 
									 sw.contact AS sender_name,sw.address AS sender_address,sw.telno AS sender_telno, 
									 sw.mobile  AS sender_mobile,sw.zip AS sender_zip,sw.province AS sender_province,
									 sw.city AS sender_city,sw.district AS sender_district,sw.warehouse_id,sw.division_id
							    FROM stockout_order so 
						   LEFT JOIN sys_warehouse sw ON sw.warehouse_id = so.warehouse_id 
						   LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id
						   LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id
							   WHERE so.stockout_id = {$stockout_id}");
	if(!$row)
    {
        $error_msg = '查询订单信息出错';
        return false;
    }
	
	$order_no = $row['stockout_no'].'@'.$sid.rand(1000,9999);;
	$ordernos["$order_no"] = $row['stockout_id'];
	splitArea($row['receiver_area'],$receiver_province,$receiver_city,$receiver_district);
	$row['items']          = array(
					'count'=> (int)ceil($row['num']),
					'name' => $row['goods_name']
					);
	$row['address']        =   array(
					'province' => $receiver_province,
					'city'     => $receiver_city,
					'district' => $receiver_district,
					'detail'   => $row['receiver_address']
					);
	$row['sender_phone']   =  $row['sender_mobile'] == ''?$row['sender_telno']:$row['sender_mobile'];
	$row['weight']	       =  $row['calc_weight']*1000;
	$row['receiver_tel']   =  $row['receiver_mobile'] == ''?$row['receiver_telno']:$row['receiver_mobile'];
	$row['sender_phone']   =  $row['sender_mobile'] == ''?$row['sender_telno']:$row['sender_mobile'];
	
	//订单信息，即收货人地址信息
	$shippingAddress['address']['province']    = $row['sender_province'];
	$shippingAddress['address']['city']        = $row['sender_city'];
	$shippingAddress['address']['district']    = $row['sender_district']==='无'?'':$row['sender_district'];
	$shippingAddress['address']['detail']      = $row['sender_address'];
	
	$shippingAddress['name']                   = $row['sender_name'];
	$shippingAddress['phone']                  = $row['sender_phone'];
		
	
	
	if($logistics_info['bill_type']==2)
	{
			//提示错误的时候直接提示填写的发货仓库地址信息 不使用接口返回的了
		$warehouseinfo = '&'.$row['sender_province'].'&'.$row['sender_city'].'&'.($row['sender_district']==='无'?'':$row['sender_district'])."&".$row['sender_address'];
	
		//获取模板的url
		logx("start to logistics multi get waybill search template url,shop_id:{$logistics_auto->shop_id}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
		$result               = logistics_search_template_url($db,$sid,$logistics_adapter,$logistics_info,$cpCode,$error_msg);
		$template_url  		  = $result['template_url_info']['template_url'];
		$template_url_cp_code = $result['template_url_info']['template_url_cp_code'];
		$template_id          = $result['template_url_info']['template_id'];
		
		if ($result['flag'] == false || $template_url=='' )
		{
			$error_msg = $result['error_msg'];
			logx("logistics_id:{$logistics_info['logistics_id']}, 获取物流模板url失败:{$result['error_msg']}! template_url", $sid);
			return false;
		}
		
		$row['tradeOrder']['object_id'] = $order_no;
		
		/*先从物流单号池里取单号*/
		$logistics_no = '';
		$logistics_no = $db->query_result_single("SELECT logistics_no FROM stock_logistics_no WHERE logistics_id={$logistics_info['logistics_id']} AND send_province='{$row['sender_province']}' AND send_city = '{$row['sender_city']}' AND send_district = '{$row['sender_district']}' AND send_address = '{$row['sender_address']}' AND status = 5 AND type = 1 AND stockout_id={$row['stockout_id']} limit 1");
		
		
		if($logistics_no && $logistics_no != '')
		{
			//菜鸟需要走下更新接口
			
			$row['tradeOrder']['waybill_code'] = $logistics_no;
			$row['tradeOrder']['cp_code']      = $cpCode;
			
			//走更新接口
			logx("start to update waybill,stockout_no:{$row['stockout_no']}  logistics_no:{$logistics_no} ",$sid);
			
			$result = $logistics_adapter->sendRequest(LOGISTICS_UPDATE_WAYBILL,$row, $sid,$db);
			$send   = $logistics_adapter->getSendParams();
			$resv   = $logistics_adapter->getReceived();
			
			logx("stockout_no:{$row['stockout_no']} send:    ".print_r(json_decode($send['post_params']['param_waybill_cloud_print_update_request']),true),$sid);
			logx("stockout_no:{$row['stockout_no']} receive: ".print_r($resv,true),$sid);
			
			$code = $result['code'];
			$error_msg = $result['error_msg'];
			$error_msg = substr($error_msg,0,200);
			$error_msg = $db->escape_string($error_msg);
			if ($code != 0) 
			{
				if($code<0)
				{
					logx("stockout no:{$row['stockout_no']}, 系统级别错误system_error:{$error_msg}! logistics_get_waybills", $sid);
					$db->query("UPDATE stock_logistics_no SET status = 3 WHERE logistics_type={$logistics_info['logistics_type']} AND logistics_id={$logistics_info['logistics_id']} AND logistics_no = '{$logistics_no}' ");			
					return false;
				}
				else
				{
					logx("stockout no:{$row['stockout_no']}, 应用级别错误app_error:{$error_msg}! logistics_get_waybills", $sid);
					$db->query("UPDATE stock_logistics_no SET status = 3 WHERE logistics_type={$logistics_info['logistics_type']} AND logistics_id={$logistics_info['logistics_id']} AND logistics_no = '{$logistics_no}' ");			
					return false;
					
				}
			}
			else//更新单号成功
			{
				$db->query("UPDATE stock_logistics_no SET status = 1,v_trade_no = '{$row['stockout_no']}' WHERE logistics_type={$logistics_info['logistics_type']} AND logistics_id={$logistics_info['logistics_id']} AND logistics_no = '{$logistics_no}' ");
				$print_datas = $result->print_data;
				$print_data = json_decode(stripslashes($print_datas));
				if(!$print_data)
				{
					$print_data = json_decode($print_datas);
				}
				
				//插入多物流单号
				$db->query("insert into sales_record_multi_logistics(operator_id,trade_id,logistics_no,logistics_id,created)
					values('{$uid}', {$row['src_order_id']}, '{$logistics_no}','{$logistics_info['logistics_id']}', NOW()) ON DUPLICATE KEY UPDATE operator_id='{$uid}' ");				

				$error_msg = $logistics_no;
				return true;
			}			
			
		}
	}
	$row['tradeOrder']['order_channels_type'] = 'OTHERS';
	$row['tradeOrder']['trade_order_list']    = $order_no;
	$row['tradeOrder']['id']                  = $order_no;
	$row['tradeOrder']['template_url']        = $template_url.'';
	$row['tradeOrder']['user_id']             = $seller_id_logistics;
	$tradeOrderInfoCols['shippingAddress']      = $shippingAddress; 
	$tradeOrderInfoCols['orders'][] = $row;
	if($tradeOrderInfoCols)
	{
		//走获取单号接口
		logx("start to get logsitics get waybills,warehouseid:{$row['warehouse_id']}  logistics_id:{$logistics_info['logistics_id']} ",$sid);
		
		$result = $logistics_adapter->sendRequest(LOGISTICS_GET_WAYBILL,$tradeOrderInfoCols, $sid,$db);
		$send   = $logistics_adapter->getSendParams();
		$resv   = $logistics_adapter->getReceived();
		
		logx("send:    ".print_r(json_decode($send['post_params']['param_waybill_cloud_print_apply_new_request']),true),$sid);
		logx("receive: ".print_r($resv,true),$sid);
		
		$code = $result['code'];
		$error_msg = $result['error_msg'];
		$error_msg = substr($error_msg,0,200);
		$error_msg = $db->escape_string($error_msg);
		if ($code != 0) 
		{
			if($code<0)
			{
				logx("stockout no:'{$row['stockout_no']}', 系统级别错误system_error:{$error_msg}! logistics_get_waybills", $sid);
				return false;
			}
			else
			{
				logx(" 应用级别错误app_error:{$error_msg}! logistics_get_waybills", $sid);
				if($logistics_info['bill_type']==2)
				{
					if(stripos($error_msg,'templateURL url not found'))
					{
						
						$judge_exist_table = $db->query("SHOW TABLES LIKE '". "cfg_top_print_template"."'");
						if($judge_exist_table->num_rows==1)
						{
							$template_url = $db->query("delete from cfg_top_print_template where template_id = {$template_id} ");
						}
						else
						{
							//清空数据库的模板信息
							$db->query("delete from sys_setting where `key`='{$template_url_cp_code}' ");
						}
					}
				}
				return false;
				
			}
		}
		else
		{
			$result_infos = $result['rev_info'];
			foreach($result_infos AS $result_info)
			{
				logx("====");
				$package_name =$result_info['package_name'];
				$package_code =$result_info['package_code'];
				$printInfo    =$result_info['printInfo'];
				$waybill_code =$result_info['waybill_code'];
				$print_datas  =$result_info['print_datas'];
				//插入物流单号
				$db->QUERY("insert into stock_logistics_no(logistics_id,logistics_type,logistics_no,stockout_id,status,type,v_trade_no,send_province,send_city,send_district,send_address,receiver_dtb,created)
							values('{$logistics_info['logistics_id']}','{$logistics_info['logistics_type']}','{$waybill_code}','{$row['stockout_id']}',1,1,'{$order_no}','{$row['sender_province']}','{$row['sender_city']}','{$row['sender_district']}','{$row['sender_address']}','',NOW())");		

				if($package_name or $package_code or $printInfo)
				{
					$db->query("replace into stock_logistics_print(stockout_id,logistics_id,logistics_no,package_adr,package_wd,print_info,created)
						values('{$row['stockout_id']}', '{$logistics_info['logistics_id']}', '{$waybill_code}', '{$package_name}','{$package_code}','{$printInfo}',NOW())");
				}
				//判断是否存在菜鸟打印组件这张表
				$judge_exist_print_table = $db->query("SHOW TABLES LIKE '". "stock_logistics_print_ext"."'");
				if($judge_exist_print_table->num_rows==1)
				{
					$db->query("replace into stock_logistics_print_ext(stockout_id,logistics_id,logistics_no,print_data,created)
						values('{$row['stockout_id']}', '{$logistics_info['logistics_id']}', '{$waybill_code}', '{$print_datas}',NOW())");
				
				}
				//插入多物流单号
				$db->query("insert into sales_record_multi_logistics(operator_id,trade_id,logistics_no,logistics_id,created)
					values('{$uid}', '{$row['src_order_id']}', '{$waybill_code}','{$logistics_info['logistics_id']}', NOW()) ON DUPLICATE KEY UPDATE operator_id='{$uid}' ");				

				//插入出库单日志
				$message = "热敏获取多物流单号:".$waybill_code;
				$db->query("insert into sales_trade_log(`type`,trade_id,operator_id,message)
							values(155,{$row['src_order_id']},$uid,'{$message}')");
				
				$error_msg = $waybill_code;
				logx("logistics_id:{$logistics_info['logistics_id']}, 获取多物流:{$error_msg}", $sid);
				return true;
			}
			
		}
	}	
	else 
	{
		$error_msg = '信息获取失败';
		return false;
	}

}


//淘宝获取模板url
function logistics_search_template_url(&$db,$sid,&$logistics_adapter,$logistics_info,$cpCode,&$error_msg)
{
	$template_url_cp_code = "template_url_".$cpCode;
	//获取模板的url
	$template_url = '';
	$judge_exist_table = $db->query("SHOW TABLES LIKE '". "cfg_top_print_template"."'");
	$template_id = 0;
	if($judge_exist_table->num_rows==1)
	{
		//判断表中有没有数据 没有通过接口获取
		$template_data = $db->query_result("select user_std_template_url,template_id from cfg_top_print_template where logistics_cp_code = '{$cpCode}' and type=-1 and user_std_template_url!='' limit 1 ");
		$template_urls = array();
		
		if(!$template_data['user_std_template_url'])
		{
			$data = array();
			//从接口获取模板url
			$result = $logistics_adapter->sendRequest(LOGISTICS_TEMPLATE_URL,$data, $sid,$db);
			$send   = $logistics_adapter->getSendParams();
			$resv   = $logistics_adapter->getReceived();
			
			logx("logistics_id:{$logistics_info['logistics_id']} send:    ".print_r($send,true),$sid);
			logx("logistics_id:{$logistics_info['logistics_id']} receive: ".print_r($resv,true),$sid);
			
			$code = $result['code'];
			$error_msg = $result['error_msg'];
			$error_msg = substr($error_msg,0,200);
			$error_msg = $db->escape_string($error_msg);
			$rev_info  = isset($result['rev_info'])?$result['rev_info']:'';
			
			if ($code <> 0)//获取模板url失败
			{
				$rev_info['template_id']   = '';
				$rev_info['template_name'] = '';
				$rev_info['template_url'] = '';
				return array('flag' => false, 'error_msg' => $error_msg,'template_url_info' => $rev_info);
			}
			else //成功
			{
				//向表中添加数据
				$template_id                      = $rev_info['template_id'];
				$template_name                    = $rev_info['template_name'];
				$template_url                     = $rev_info['template_url'];
				
				$rev_info['template_url_cp_code'] = '';
				
				$db->query("insert into cfg_top_print_template (template_id,type,template_name,logistics_cp_code,user_std_template_url,custom_fields,created) values ({$template_id},-1,'{$template_name}','{$cpCode}','{$template_url}','',now())");
				return array('flag' => true, 'error_msg' =>'','template_url_info' => $rev_info);
			} 
		}
		else
		{
			$rev_info['template_url']         = $template_data['user_std_template_url'];
			$rev_info['template_id']          = $template_data['template_id'];
			$rev_info['template_url_cp_code'] = '';
			
			return array('flag' => true, 'error_msg' =>'','template_url_info' => $rev_info);
		}
	}
	else
	{
		$template_url = $db->query_result_single("select `value` from sys_setting where `key` = '{$template_url_cp_code}'");
		$template_urls = array();
		if(!$template_url)
		{
			$data = array();
			//从接口获取模板url
			$result = $logistics_adapter->sendRequest(LOGISTICS_TEMPLATE_URL,$data, $sid,$db);
			$send   = $logistics_adapter->getSendParams();
			$resv   = $logistics_adapter->getReceived();
			
			logx("logistics_id:{$logistics_info['logistics_id']} send:    ".print_r($send,true),$sid);
			logx("logistics_id:{$logistics_info['logistics_id']} receive: ".print_r($resv,true),$sid);
			
			$code      = $result['code'];
			$error_msg = $result['error_msg'];
			$error_msg = substr($error_msg,0,200);
			$error_msg = $db->escape_string($error_msg);
			$rev_info  = isset($result['rev_info'])?$result['rev_info']:'';
			
			if ($code <> 0)//获取user_id失败
			{
				$rev_info['template_id']   = '';
				$rev_info['template_name'] = '';
				$rev_info['template_url'] = '';
				return array('flag' => false, 'error_msg' => $error_msg,template_url_info=>$rev_info);
			}
			else //成功
			{
				
				//向表中添加数据
				$template_id   = $rev_info['template_id'];
				$template_name = $rev_info['template_name'];
				$template_url  = $rev_info['template_url'];
				
				$rev_info['template_url_cp_code'] = $template_url_cp_code;
				$rev_info['template_id']          = 0;
				
				$db->query("insert into sys_setting  values ('{$template_url_cp_code}','{$template_url}','','',0,0,'',0,now())");
				return array('flag' => true, 'error_msg' =>'','template_url_info' => $rev_info);
			} 
		}
		else
		{
			$rev_info['template_url']         = $template_url;
			$rev_info['template_url_cp_code'] = $template_url_cp_code;
			$rev_info['template_id']          = 0;
			
			return array('flag' => true, 'error_msg' =>'','template_url_info' => $rev_info);
		}
	}
}

//获取授权店铺user_id
function logistics_user_id(&$db,$sid,&$logistics_adapter,$logistics_info,$type,&$error_msg)
{
	global $top_app_config;
	$data['user_id'] = 'user_id';
	$result = '';
	$send   = array();
	$resv   = array();
	$shop_id = $logistics_info['shop_id'];
	//类型判断
	if($type==0)
	{
		$trade_shop = getShopAuth($sid, $db,(int)$shop_id);
		if($trade_shop)
		{
			$trade_data['appkey']             = $trade_shop->key;
			$trade_data['appsecret']          = $trade_shop->secret;
			$trade_data['sessionkey']         = $trade_shop->session;
			$trade_data['logistics_auto_key'] = json_decode($logistics_info['app_key']);
			
			$trade_logistics_adapter = new LogisticsAdapter( $logistics_info['bill_type'],$trade_data);
			if($trade_data['appkey'] == $top_app_config['app_key'])
			{
				$result = $trade_logistics_adapter->sendRequest(LOGISTICS_USER_ID,$data, $sid,$db);
				$send   = $trade_logistics_adapter->getSendParams();
				$resv   = $trade_logistics_adapter->getReceived();
				
				logx("logistics_id:{$logistics_info['logistics_id']} send:    ".print_r($send,true),$sid);
				logx("logistics_id:{$logistics_info['logistics_id']} receive: ".print_r($resv,true),$sid);
    
			}
			else
			{   $rev_info['user_id'] = '';
				$error_msg='发货店铺授权时效,请检查授权信息';
				return array('flag' => false, 'error_msg' => $error_msg,'user_info' => $rev_info);
			}
		}
		else
		{
			$rev_info['user_id'] = '';
			$error_msg='发货店铺授权时效,请检查授权信息';
			return array('flag' => false, 'error_msg' => $error_msg,'user_info' => $rev_info);
		}
	}
	else
	{
		$result = $logistics_adapter->sendRequest(LOGISTICS_USER_ID,$data, $sid,$db);
		$send   = $logistics_adapter->getSendParams();
		$resv   = $logistics_adapter->getReceived();
		logx("logistics_id:{$logistics_info['logistics_id']} send:    ".print_r($send,true),$sid);
		logx("logistics_id:{$logistics_info['logistics_id']} receive: ".print_r($resv,true),$sid);
    
	}

	$code = $result['code'];
    $error_msg = $result['error_msg'];
    $error_msg = substr($error_msg,0,200);
    $error_msg = $db->escape_string($error_msg);
    $rev_info  = isset($result['rev_info'])?$result['rev_info']:'';
	
	if ($code <> 0)//获取user_id失败
    {
		$rev_info['user_id'] = '';
        return array('flag' => false, 'error_msg' => $error_msg,'user_info' => $rev_info);
    }
    else //成功
    {
		$db->query("UPDATE sys_shop SET account_id = '{$rev_info['user_id']}' WHERE shop_id={$shop_id} ");
        return array('flag' => true, 'error_msg' =>'','user_info' => $rev_info);
    } 
	
}



function logistics_get_dtb(&$db,$sid,&$logistics_adapter,$logistics_dtb,&$error_msg)
{

    $data = array(
        'waybillCode' => $logistics_dtb['waybillCode'],
        'providerCode' => $logistics_dtb['providerCode']
    );
    $result = $logistics_adapter->sendRequest(LOGISTICS_GET_DTB, $data, $sid);
    $send   = $logistics_adapter->getSendParams();
    $resv   = $logistics_adapter->getReceived();

    $code = $result['code'];
    $error_msg = $result['error_msg'];
  //  $error_msg = substr($error_msg,0,200);
  //  $error_msg = $db->escape_string($error_msg);
    $rev_info  = isset($result['rev_info'])?$result['rev_info']:'';
    if ($code <> 0)//获取大头笔失败
    {
        return array('flag' => false, 'error_msg' => $error_msg);
    }
    else //成功
    {
        return array('flag' => true, 'error_msg' => $error_msg,'dtb_info' => $rev_info);
    } 
}

function logistics_cancel_waybill(&$db, $logistics_info, $stockout_ids, &$result)
{
    $cancel_logistics_fail = array();
    $cancel_logistics_succ = array();
   // $logistics_auto = json_decode($logistics_info['app_key']);
    $sid = $logistics_info->sid;
	$logistics_data = array();
	$cpCode = '';
	
	if($logistics_info->bill_type==9 || $logistics_info->bill_type==2)
	{//京东阿尔法和淘宝需要店铺授权
		$shop = getShopAuth($sid, $db, (int)$logistics_info->shop_id);
		if(!$shop)
		{
			$result['status'] = 1;
            $result['msg']  = '店铺未授权';
			return;
			
		}
		$cpCode = $logistics_info->code;
		$logistics_data['appkey'] 			  = $shop->key;
		$logistics_data['appsecret'] 		  = $shop->secret;
		$logistics_data['sessionkey']         = $shop->session;
		$logistics_data['logistics_auto_key'] = $logistics_info;
	}
	
    $logistics_adapter = new LogisticsAdapter($logistics_info->bill_type,$logistics_data);
    
    //查询接口需要信息  京东阿尔法 比较特殊  需要取消的操作人
    $order_info = $db->query("SELECT sln.rec_id,so.stockout_id,so.stockout_no,so.src_order_id,st.trade_id,sln.logistics_no,sln.logistics_id,st.src_tids,st.platform_id
                              FROM stock_logistics_no sln
                              LEFT JOIN stockout_order so ON sln.stockout_id = so.stockout_id
                              LEFT JOIN sales_trade st on (so.src_order_type=1 AND so.src_order_id=st.trade_id)
                              WHERE sln.rec_id IN({$stockout_ids[0]})");
    
    if(!$order_info)
    {
        $result['status'] = 1;
        $result['msg']  = "运单号数据为空 stockout_ids: {$stockout_ids[0]} ";
        logx("rec_id:$stockout_ids, Get cancel orders info failed:$error_msg! ", $sid);
        return false;
    }
    if($order_info->num_rows<1)
    {
        $result['status'] = 1;
        $result['msg'] = '订单信息为空';
		logx("rec_id:$stockout_ids, Get cancel orders info failed:订单信息为空 ", $sid);
        return false;
    }
    
    while($row = $db->fetch_array($order_info))
    {
        //$data['platformOrderNo']   = $row['src_tids'];	
        $data['providerCode']      = $logistics_info->code;
        $data['waybillCodeList']   = $row['logistics_no'];
        //$data['operatorTime']      = date("Y-m-d H:i:s",time());
        //$data['operatorName']      = '系统';
		//$data['cp_code']           = $cpCode;
        
        //调用接口, 多次调用接口,根据bill_type  进行函数分装      
        $result = $logistics_adapter->sendRequest(LOGISTICS_WAYBILL_UNBIND,$data,$sid);
        $send   = $logistics_adapter->getSendParams();
        $resv   = $logistics_adapter->getReceived();
        
        //记录接口日志
        logx("logistics_no:{$row['logistics_no']}".' send:    '.print_r($send,true),$sid);
        logx("logistics_no:{$row['logistics_no']}".' receive: '.print_r($resv,true),$sid);
        $code = (int)$result['code'];
        $error_msg = $result['error_msg'];
        $error_msg = substr($error_msg,0,200);
        
        if($code!=0)
        {
            if($code<0)
            {
                //失败的订单只需要把失败的原因和失败的出库单传递过来就可以了
                
                logx("logistics_no:{$row['logistics_no']}, 系统级别错误system_error: {$error_msg} in logistics_cancel_waybill", $sid);
                $cancel_logistics_fail[$row['rec_id']] = array(
					'id'=>$row['rec_id'],
					'logistics_no' =>$row['logistics_no'],
					'stock_id' => $row['stockout_id'],
					'stock_no' => $row['stockout_no'],
					'msg'=>$error_msg,
					'trade_id' => $row['src_order_id'],
				);
				$result['status'] = 2;
				$db->query("UPDATE stock_logistics_no SET status=4 WHERE logistics_type = {$logistics_info->logistics_type} AND logistics_id = {$logistics_info->logistics_id} AND logistics_no in({$row['logistics_no']})");
            }
            else 
            {
                logx("logistics_no:{$row['logistics_no']}, 应用级别错误app_error: {$error_msg} in logistics_cancel_waybill ", $sid);
                $cancel_logistics_fail[$row['rec_id']] = array(
					'id'=>$row['rec_id'],
					'logistics_no' =>$row['logistics_no'],
					'stock_id' => $row['stockout_id'],
					'stock_no' => $row['stockout_no'],
					'msg'=>$error_msg,
					'trade_id' => $row['src_order_id'],
				);
				$result['status'] = 2;
				$db->query("UPDATE stock_logistics_no SET status=4 WHERE logistics_type = {$logistics_info->logistics_type} AND logistics_id = {$logistics_info->logistics_id} AND logistics_no in({$row['logistics_no']})");
            }
            
        }
        else
        {
            $cancel_logistics_succ[$row['rec_id']] = array(
					'id'=>$row['rec_id'],
					'logistics_no' =>$row['logistics_no'],
					'stock_id' => $row['stockout_id'],
					'stock_no' => $row['stockout_no'],
					'trade_id' => $row['src_order_id'],
				);
			$result['status'] = 0;	
			$db->query("UPDATE stock_logistics_no SET status=6 WHERE logistics_type = {$logistics_info->logistics_type} AND logistics_id = {$logistics_info->logistics_id}  AND logistics_no in({$row['logistics_no']})");
  
        }
    }
	$result['data'] = array(
	    'fail'     => $cancel_logistics_fail,
	    'success'  => $cancel_logistics_succ
	);

    return true;
}
function logistics_search_waybill(&$db,&$sid,&$rows,&$shop_info,&$error_msg)
{
    global $top_app_config;
    global $jos_app_config_new;
    $rows = array();
    $data = array();
    $logisticsName = array("YUNDA"=>"韵达快递","ZJS"=>"宅急送","EMS"=>"EMS","STO"=>"申通快递","EYB"=>"EMS","YTO"=>"圆通速递","TTKDEX"=>"天天快递","QFKD"=>"全峰快递","SF"=>"顺丰速运","UC"=>"优速快递","ZTO"=>"中通快递","HTKY"=>"百世汇通","POSTB"=>"邮政国内小包","FAST"=>"快捷快递","GTO"=>"国通快递","DBKD"=>"德邦物流","BESTQJT"=>"百世快运","5000000007756"=>"中国邮政国内标快","CN7000001003751"=>"跨越速递","2608021499_235"=>"安能快递");
    $shop_id = $shop_info['shop_id'];
    $shop = getShopAuth($sid, $db,$shop_id);
    if(!$shop)
    {
        $error_msg = '店铺未授权，请检查授权信息!';
        logx("shop_id : {$shop_id} not auth! logistics_search_waybill",$sid);
        return false;
    }
    $construct_data['appkey'] =   $shop->key;
    $construct_data['appsecret'] = $shop->secret;
    $construct_data['sessionkey'] = isset($shop->session)?$shop->session:'';
    //淘宝
    if($shop_info['platform_id'] == 1||$shop_info['platform_id'] == 2)
    {
        if($construct_data['appkey'] != $top_app_config['app_key'])
        {
            $error_msg = '云栈物流只能使用淘宝店铺授权';
            logx("shop_id :{$shop_id}, {$error_msg} in logistics_search_waybill",$sid);
            return;
        }
        $bill_type = 2;
    }
    else if($shop_info['platform_id'] == 3)//京东
    {
        if($construct_data['appkey'] != $jos_app_config_new['app_key'])
        {
            $error_msg = '京东物流只能使用京东店铺授权';
            logx("shop_id :{$shop_id}, {$error_msg} in logistics_search_waybill",$sid);
            return;
        }
        $bill_type = 9;
        //调用接口查询商家编码
        $result = get_seller_vender_info($sid,$db,$shop_id,$construct_data,$error_msg);
        if ($result['flag'] == false)
        {
            logx("查询商家编码失败:{$result['error_msg']}! get_seller_vender_info", $sid);
        }
		$data['vendorCode']=$result['venderInfo']['venderId'];
    }
    else
    {
        $error_msg = '该平台暂不支持此查询';
        logx("shop_id :{$shop_id}, {$error_msg} in logistics_search_waybill", $sid);
        return;
    }
    $construct_data['logistics_auto_key'] = $shop_info;
    //传入适配器
    logx("start to search waybill,shop_id:{$shop_id} ",$sid);
    $logistics_adapter = new LogisticsAdapter($bill_type,$construct_data);     
    $result = $logistics_adapter->sendRequest(LOGISTICS_SEARCH_WAYBILL,$data,$sid,$db);
    $send   = $logistics_adapter->getSendParams();
    $resv   = $logistics_adapter->getReceived();

    //记录接口日志
    logx(' send:    '.print_r($send,true),$sid);
    logx(' receive: '.print_r($resv,true),$sid);
    
    //处理返回格式化后的数据  接口返回的数据调用存储过程  既然是数据格式化  直接回传 过来时个数据

    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $error_msg = substr($error_msg,0,200);

    if($code!=0)
    {
        if($code<0)
        {
            logx("shop_id :{$shop_id}, 系统级别错误system_error: {$error_msg} in logistics_search_waybill", $sid);
            return false;
        }
        else 
        {
            logx("shop_id :{$shop_id}, 应用级别错误app_error: {$error_msg} in logistics_search_waybill ", $sid);
            return false;
        }
    }
    else
    {//成功的情况下 怎么添加
    
        if($bill_type == 9)
        {
            foreach ($result['rev_info'] as $value) {
                $others = '网点编码:'.$value['branchCode'].' 财务结算编码:'.$value['settlementCode'];
                $rows[]=array($value['providerName'],$value['province'] ,$value['city'] , isset($value['district']) ? $value['district']:'' , $value['detail'] ,$others);
            }
        }
        else if($bill_type == 2)
        {
            foreach($result['rev_info'] as $value)
            {
                $others = '';
                $cp_code = $value['cp_code'];
                $rows[]=array($logisticsName[$cp_code],$value['province'] ,$value['city'] , isset($value['district']) ? $value['district']:'' , $value['detail'],$others);
            }
        }
        logx("shop_id :{$shop_id}, 查询成功 in logistics_search_waybill ", $sid);
        
    }
    return true;
}

function logistics_search_abled_waybill(&$db,&$rows,$logistics_info,&$error_msg)
{
    $sid = $logistics_info['SID'];
    $logistics_auto=json_decode($logistics_info['app_key']);
    $shop = getShopAuth($sid, $db,(int)$logistics_auto->shop_id);
    if(!$shop)
    {
		$error_msg = "该店铺获取授权失败";
		return;
	}
	
	/*
		$construct_data['appkey'] = 'test';
		$construct_data['appsecret'] = 'test';
		$construct_data['sessionkey'] = '6100d04564e41d011ee1d91f901261b78970cd6ff5479222054718218';
	*/
	
	$construct_data['appkey']             = $shop->key;
	$construct_data['appsecret']          = $shop->secret;
	$construct_data['sessionkey']         = $shop->session;
	$construct_data['logistics_auto_key'] = $logistics_auto;
    //传入适配器
    $logistics_adapter     = new LogisticsAdapter($logistics_info['bill_type'],$construct_data);
    $data['logistics_key'] = Obj2Arr(json_decode($logistics_info['app_key']));
    //调用接口, 多次调用接口,根据bill_type  进行函数分装      
    $result = $logistics_adapter->sendRequest(LOGISTICS_STOCK_QUERY,$data,$sid,$db);
    $send   = $logistics_adapter->getSendParams();
    $resv   = $logistics_adapter->getReceived();
    
    //记录接口日志
    logx(' send:    '.print_r($send,true),$sid);
    logx(' receive: '.print_r($resv,true),$sid);
    
    //处理返回格式化后的数据  接口返回的数据调用存储过程  既然是数据格式化  直接回传 过来时个数据 
    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $error_msg = substr($error_msg,0,200);
    
    if($code!=0)
    {
        if($code<0)
        {
            logx("logistics_id :{$logistics_info['logistics_id']}, 系统级别错误system_error: {$error_msg} in logistics_search_abled_waybill", $sid);
            return false;
        }
        else 
        {
            logx("logistics_id :{$logistics_info['logistics_id']}, 应用级别错误app_error: {$error_msg} in logistics_search_abled_waybill ", $sid);
            return false;
        }
    }
    else
    {//成功的情况下 怎么添加
	
    //  $rows[] = array($result['rev_info']['data']['providerCode'],$result['rev_info']['data']['providerName'],$result['rev_info']['data']['branchCode'],$result['rev_info']['data']['amount']);
		if($logistics_info['bill_type']==9)
		{
			$rows[] = array($result['rev_info']['data']['providerName'],$result['rev_info']['data']['providerCode'],$result['rev_info']['data']['branchCode'],$result['rev_info']['data']['vendorCode'],strval($result['rev_info']['data']['amount']));
		}
		else
		{
			foreach($result['rev_info']['send_addr'] as $value)
			{
				$address=$value->shipp_address_cols->address_dto['0'];
				$address = Obj2Arr($address);
				$rows[]=array($address['province'] ,isset($address['city'])? $address['city']:'' , isset($address['district']) ? $address['district']:'' , $address['detail'] , strval($value->quantity));
			}
		}
		logx("logistics_id :{$logistics_info['logistics_id']}, 查询单号成功 in logistics_search_abled_waybill ", $sid);
		
    }
    return true;
}

function get_seller_vender_info($sid,&$db,$shop_id,$construct_data,$error_msg)
{
    $bill_type = 9;
    $data=array();
    logx("start to get venderInfo,shop_id:{$shop_id} ",$sid);
    $logistics_adapter = new LogisticsAdapter($bill_type,$construct_data);
    $result = $logistics_adapter->sendRequest(GET_SELLER_VENDER_INFO,$data,$sid,$db);
    $send   = $logistics_adapter->getSendParams();
    $resv   = $logistics_adapter->getReceived();
    //记录接口日志
    logx(' receive: '.print_r($resv,true),$sid);
    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $error_msg = substr($error_msg,0,200);
    $error_msg = $db->escape_string($error_msg);
    $rev_info  = isset($result['rev_info'])?$result['rev_info']:'';
    if ($code <> 0)//查询商家信息失败
    {
        return array('flag' => false, 'error_msg' => $error_msg);
    }
    else //成功
    {
        return array('flag' => true, 'error_msg' => $error_msg,'venderInfo' => $rev_info);
    }

}

function success_handle($sid,&$db,$order_success,$logistics_info)
{
    $logs = '';
    foreach($order_success as $key=>$value)
    {   
        $query_params = $db->escape_string((to_query_params($value)));
        logx($query_params,$sid);
        $db->query("CALL SP_STOCK_LOGISTICS_NO_GET(0,'{$query_params}')");
        $logs .= $key.':'.$value['logistics_no'].'成功 ';
    }
    logx($logs,$sid);
}

function error_handle($sid,&$db,$order_fail)
{
    $logs = '';
    foreach($order_fail as $key=>$value)
    {
        $query_params = $db->escape_string((to_query_params($value)));
        logx($query_params,$sid);
        $db->query("CALL SP_STOCK_LOGISTICS_NO_GET(1,'{$query_params}')");
        $logs .= $key.':失败,原因:'.$value['error_msg'].' ';
    }
    logx($logs,$sid);
}








