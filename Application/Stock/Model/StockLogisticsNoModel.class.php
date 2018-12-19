<?php

namespace Stock\Model;

use Think\Model;
class StockLogisticsNoModel extends Model{
	protected $tableName = 'stock_logistics_no';
	protected $pk        = 'rec_id';
	public function getLogisticsNoLeftCfgLostics($fields,$conditions)
	{  
	    try {
	        $res = $this->alias('sln')->field($fields)->join('left join stockout_order so on so.stockout_id = sln.stockout_id')->join('left join cfg_logistics cl on cl.logistics_id = sln.logistics_id')->where($conditions)->select();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-getLogisticsNoLeftCfgLostics-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	//更新状态
	public function updateLogisticsNo($data,$conditions)
	{
	    try {
	        $res = $this->where($conditions)->save($data);
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-updateLogisticsNoStatus-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	public function getSalesMergeAboutStockout()
	{
	    try {
	        $res = $this->alias('sln')->field("sln.*,so.stockout_no,so.src_order_id")->join('left join stockout_order so on sln.stockout_id=so.stockout_id')->join('left join sales_trade str on str.trade_id = so.src_order_id')->join('left join cfg_logistics cl on cl.logistics_id = sln.logistics_id')->where(array('str.trade_status'=>120,'sln.status'=>array('in','3,4,5'),'cl.bill_type'=>2))->select(); 
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-getSalesMergeAboutStockout-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	//获取需要取消的物流单号的需要的信息
	public function getWaybillInfoLeftShopAndStockout($fields,$conditions)
	{
	    try {
	        $res = $this->alias('sln')->field($fields)->join("left join cfg_shop cs on cs.shop_id = sln.shop_id")->join("left join stockout_order so on so.stockout_id = sln.stockout_id")->where($conditions)->select();
	        return $res;
	    }catch (\PDOException $e){
	        $msg = $e->getMessage();
	        \Think\Log::write("StockLogisticsNoModel getCancelWaybillInfo line---".__LINE__."--".$msg);
	        return $msg;
	    }
		
	}
	/**
	 * 电子面单回收
	 * 
	 * @param int $stockout_id
	 */
	public function recoverLogisticsNO($stockout_id)
	{
			$sql_stockout_info = "SELECT so.logistics_no,so.stockout_id,so.logistics_id,so.src_order_type,so.src_order_id,so.stockout_no,so.receiver_area,so.receiver_dtb,so.receiver_name,so.receiver_mobile"
					.",cl.logistics_type,cl.bill_type"
					.",IF(st.trade_from=2 AND ( IFNULL(st.src_tids,1) OR IF(TRIM(st.src_tids)='',1,0)),st.trade_no,st.src_tids) AS src_tids "
					." FROM stockout_order so"
					." LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id"
					." LEFT JOIN cfg_logistics cl ON cl.logistics_id=so.logistics_id"
					." WHERE so.stockout_id=%d";
			$res_stockout_info = $this->query($sql_stockout_info,array($stockout_id));
			$res_stockout_info = $res_stockout_info[0];
			//\Think\Log::write(print_r($res_stockout_info,true),\Think\Log::INFO);
			if(empty($res_stockout_info['logistics_no']) || (int)$res_stockout_info['bill_type'] == 0)
			{
				return ;
			}
            $status = $res_stockout_info['bill_type'] == 1? 6:5; 
			$res_stockout_info['receiver_name'] = str_replace("'","''",$res_stockout_info['receiver_name']);
			$res_stockout_info['receiver_area'] = str_replace("'","''",$res_stockout_info['receiver_area']);
			$sql_logistics_no = "INSERT INTO stock_logistics_no(logistics_id,logistics_no,logistics_type,status,stockout_id,src_tids,sender_province,sender_city,sender_district,sender_address,receiver_dtb,receiver_info,created)"
								." SELECT so.logistics_id,'{$res_stockout_info['logistics_no']}',{$res_stockout_info['logistics_type']},{$status},{$res_stockout_info['stockout_id']},'{$res_stockout_info['src_tids']}',sw.province,sw.city,sw.district,sw.address,'{$res_stockout_info['receiver_dtb']}',CONCAT('{$res_stockout_info['receiver_area']}','{$res_stockout_info['receiver_name']}','{$res_stockout_info['receiver_mobile']}'),now()"
								."	FROM stockout_order so LEFT JOIN cfg_warehouse sw ON so.warehouse_id = sw.warehouse_id"
								." WHERE so.stockout_id={$stockout_id}"
								." ON DUPLICATE KEY UPDATE stockout_id=VALUES(stockout_id),src_tids = VALUES(src_tids),status = {$status},receiver_dtb = VALUES(receiver_dtb),receiver_info=VALUES(receiver_info);";
// 			\Think\Log::write($sql_logistics_no,\Think\Log::DEBUG);
			\Think\Log::write('更新订单电子面单信息',\Think\Log::INFO);
			$this->execute($sql_logistics_no);
			$sql_update_stockout = "UPDATE stockout_order SET logistics_no='' WHERE stockout_id={$stockout_id}";
			\Think\Log::write('清空出库电子面单信息',\Think\Log::INFO);
			$this->execute($sql_update_stockout);
			$sql_update_sales = "UPDATE sales_trade SET logistics_no='' WHERE trade_id={$res_stockout_info['src_order_id']}";
			\Think\Log::write('清空订单电子面单信息',\Think\Log::INFO);
			$this->execute($sql_update_sales);
	}
	//电子面单获取成功更新相关表
	function dealWaybillGetSuccess($logistics_info,&$result)
	{
		$logs = '';
	    foreach ($result['success'] as $stockout_id => $success_info)
	    {
    	    try {
    	        $this->startTrans();
    	        $operator = get_operator_id();
				$update_stockout_cond = array(
					'stockout_id' => $stockout_id,
				);
				$res_so_info = D('Stock/StockOutOrder')->getStockoutOrderLock('status',$update_stockout_cond);

				$update_stockout_data = array(
//    	            'status'       => array('exp','GREATEST(55,status)'),
    	            'logistics_no' => $success_info['logistics_no'],
    	            'receiver_dtb' => empty($success_info['receiver_dtb'])?'':$success_info['receiver_dtb'],
    	        );
				if((int)$res_so_info['status'] >= 55){
					$update_stockout_data['status'] = array('exp','GREATEST(55,status)');
				}
				$res_update_stockout_logistics_no = D('Stock/StockOutOrder')->updateStockoutOrder($update_stockout_data,$update_stockout_cond);

    	        $get_stockout_fields = array(
    	            'src_order_type',
    	            'src_order_id',
    	            'src_order_no',
                    'package_count',
                    'logistics_id'
    	        );
    	        $get_stockout_cond = array(
    	            'stockout_id' => $stockout_id  
    	        );
    	        $res_get_stockout = D('Stock/StockOutOrder')->getStockoutOrders($get_stockout_fields,$get_stockout_cond);
    	        
    	        if(!empty($res_get_stockout) && $res_get_stockout[0]['src_order_type'] == 1)
    	        {
    	            $update_sales_trade_data = array(
    	                'logistics_no' => $success_info['logistics_no'],
    	                'receiver_dtb' => set_default_value($success_info['receiver_dtb'],''),
    	                
    	            );
    	            $update_sales_trade_cond = array(
    	                'trade_id' => $res_get_stockout[0]['src_order_id']
    	            );
    	            D('Trade/Trade')->updateSalesTrade($update_sales_trade_data,$update_sales_trade_cond);

    	            //添加京邦达多物流单号
                    $packageCount = $res_get_stockout[0]['package_count'];
                    //大于一包 并且是 京东物流 才去添加多包裹
                    if($packageCount>1 && $logistics_info->logistics_type == 1311){
                        for($i=0;$i<$packageCount-1;$i++){
                            $currentPackage = $i+2;  //主物流单号是第一包,所以多包裹从第二包开始。
                            $insert_data = array(
                                'logistics_no' => $success_info['logistics_no'].'-'.$currentPackage.'-'.$packageCount.'-',
                                'stockout_id' => $stockout_id,
                                'logistics_id' => $res_get_stockout[0]['logistics_id'],
                                'trade_no' => $res_get_stockout[0]['src_order_no']
                            );
                            $res = D('SalesMultiLogistics')->addLogistics($insert_data);
                        }
                    }
    	        }
    	        
    	        //插入出库单日志
    	        if((int)$logistics_info->bill_type == 2)
    	        {
    	            $info = "云栈获取物流单号:".$success_info['logistics_no'];
    	        }elseif((int)$logistics_info->bill_type == 1)
    	        {
    	            $info = "线下获取物流单号:".$success_info['logistics_no'];
    	        }elseif((int)$logistics_info->bill_type == 9){
					$info = "京东获取物流单号:".$success_info['logistics_no'];
				}
    	        //更新日志记录
    	        $insert_sales_log_data = array(
    	            'type' => '155',
    	            'trade_id' => $res_get_stockout[0]['src_order_id'],
    	            'operator_id'=>$operator,
    	            'message'=>$info,
    	        );
    	        $res_update_trade_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
    	       //插入物流单号
    	        $insert_logistics_no_data = array(
    	            'logistics_id'     => $logistics_info->logistics_id,
    	            'shop_id'          => $success_info['shop_id'],
    	            'src_tids'         => $success_info['src_tids'],
    	            'logistics_type'   => $logistics_info->logistics_type,
    	            'logistics_no'     => $success_info['logistics_no'],
    	            'stockout_id'      => $stockout_id,
    	            'status'           => 1,
    	            'sender_province'  => $success_info['send_province'],
    	            'sender_city'      => $success_info['send_city'],
    	            'sender_district'  => $success_info['send_district'],
    	            'sender_address'   => $success_info['send_address'],
    	            'receiver_dtb'     => empty($success_info['receiver_dtb'])?'':$success_info['receiver_dtb'],
    	            'created'          => array('exp','NOW()'),
    	            'waybill_info'     => $success_info['waybill_info']
    	        );
    	        $insert_logistics_no_for_update = array(
    	            'logistics_id','shop_id','src_tids','stockout_id','status','sender_province','sender_city','sender_district','sender_address','receiver_dtb','waybill_info',
    	        );
    	        $this->addStockLogisticsNo($insert_logistics_no_data,$insert_logistics_no_for_update);
    	        
    	        
    	        
    	        //插入集包地信息
    	        /* if(isset($way_bill_detail['package_name']) && $way_bill_detail['package_name'] != '')
    	         {
    	         $db->execute("replace into stock_logistics_print(stockout_id,logistics_id,logistics_no,package_adr,package_wd,created)"."values({$stockout_id},{$logistics_info->logistics_id},'{$way_bill_detail['logistics_no']}','{$way_bill_detail['package_name']}','{$way_bill_detail['package_code']}',NOW())");
    	        } */

    	        $logs .= $stockout_id.':'.$success_info['logistics_no'].'成功 ';
    	        \Think\Log::write($logs,\Think\Log::INFO);
    	        $this->commit();
    	    }catch(\PDOException $e){
    			$msg = $e->getMessage();
    			$result['fail'][] = array(
    			    'stock_id'   => "{$stockout_id}",
    			    'stock_no'   => $success_info['stockout_no'],
    			    'msg'        => self::PDO_ERROR,
    			); 
    			\Think\Log::write('单号获取成功后，更新数据库失败：stockout_id-'.$stockout_id.';logistics_no-'.$success_info['logistics_no'].$msg);
    			$this->rollback();
    			unset($result['success']["{$stockout_id}"]);
                continue;
    	    }catch(\Think\Exception $e){
				$msg = $e->getMessage();
    			$result['fail'][] = array(
    			    'stock_id'   => "{$stockout_id}",
    			    'stock_no'   => $success_info['stockout_no'],
    			    'msg'        => "单号获取成功后，更新失败：".$msg,
    			); 
    			//\Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$success_info['logistics_no'].$msg);
    			$this->rollback();
    			unset($result['success']["{$stockout_id}"]);
    			continue;
			}catch(\Exception $e){
    			$msg = $e->getMessage();
    			$result['fail'][] = array(
    			    'stock_id'   => "{$stockout_id}",
    			    'stock_no'   => $success_info['stockout_no'],
    			    'msg'        => "单号获取成功后，更新失败：".$msg,
    			); 
    			\Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$success_info['logistics_no'].$msg);
    			$this->rollback();
    			unset($result['success']["{$stockout_id}"]);
    			continue;
    		}
	    }
	}
	function dealWaybillGetFail($logsitics_info,&$result)
	{
	    $operator = get_operator_id();
		$logs = '';
	    foreach($result['fail'] as $key=>$fail_info)
	    {
    	    try {
    	        //更新出库单状态
    	        $update_stockout_data = array(
    	            'status'       => array('exp','GREATEST(55,status)'),
    	        );
    	        $update_stockout_cond = array(
    	            'stockout_id' => $fail_info['stock_id'],
    	        );
				$res_so_info = D('Stock/StockOutOrder')->getStockoutOrderLock('status',$update_stockout_cond);

				if((int)$res_so_info['status'] >= 55){
					D('Stock/StockOutOrder')->updateStockoutOrder($update_stockout_data,$update_stockout_cond);
				}

    	        //查询销售出库单信息
    	        $get_stockout_fields = array(
    	            'src_order_type',
    	            'src_order_id',
    	        );
    	        $get_stockout_cond = array(
    	            'stockout_id' => $fail_info['stock_id']
    	        );
    	        $res_get_stockout = D('Stock/StockOutOrder')->getStockoutOrders($get_stockout_fields,$get_stockout_cond);
    	         
    	        //插入出库单日志
    	        $log_type = array('0'=>"获取普通物流单号失败:",'1'=>"线下获取物流单号失败:","2"=>"云栈获取物流单号失败:");
    	        $message = $log_type["{$logsitics_info->bill_type}"].$fail_info['msg'];
    	        $insert_sales_log_data = array(
    	            'type'         => 155,
    	            'trade_id'     => empty($res_get_stockout[0]['src_order_id'])?'':$res_get_stockout[0]['src_order_id'],
    	            'operator_id'  => $operator,
    	            'message'      => $message
    	        );
    	        $res_add_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
    	        	
    	        if((strpos($fail_info['msg'],'电子面单账户余额不足') === false) && (strpos($fail_info['msg'],'订单发货地址') === false)){
                    $logs .= $fail_info['stock_id'].':失败,原因:'.$fail_info['msg'].' ';
                    \Think\Log::write($this->name."-dealWaybillGetFail-".$logs);
                }
    	        $this->commit();
    	    }catch(\PDOException $e){
    	        $msg = $e->getMessage();
    	        $fail_info['msg'] = self::PDO_ERROR;
    	        \Think\Log::write($this->name."-dealWaybillGetFail-".$fail_info['stock_id']."更新数据失败:".$msg." ".$logs);
    	        $this->rollback();
    	        continue;
    	    }catch(\Think\Exception $e){
				$msg = $e->getMessage();
    	        $fail_info['msg'] = "更新失败,".$msg;
    	        
    	       // \Think\Log::write($this->name."-dealWaybillGetFail-".$fail_info['stock_id']."更新数据失败:".$msg." ".$logs);
    	        $this->rollback();
    	        continue;
			}catch(\Exception $e){
    	        $msg = $e->getMessage();
    	        $fail_info['msg'] = "更新失败,".$msg;
    	        
    	        \Think\Log::write($this->name."-dealWaybillGetFail-".$fail_info['stock_id']."更新数据失败:".$msg." ".$logs);
    	        $this->rollback();
    	        continue;
    	    }
    	   
	    }
	}
	function addStockLogisticsNo($data,$update=false,$options = '')
	{
	    
	   try {
            if (empty($data[0])) {
                $res = $this->add($data,$options,$update);
            }else
            {
                $res = $this->addAll($data,$options,$update);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write('更新电子面单列表失败,请注意!---'.$this->name.'-addStockLogisticsNo-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
	}
	function getLogisticsNo($fields,$conditions = array())
	{
	    try {
	         $res = $this->field($fields)->where($conditions)->select();
	         return $res;
	    } catch (\PDOException $e) {
	        \Think\Log::write($this->name.'-getLogisticsNo-'.$e->getMessage());
	        E(self::PDO_ERROR);
	    }
	}
	function getJosLogisticsNo($fields,$conditions= array(),$num=null)
	{
	    try {
	        $res = $this->field($fields)->where($conditions)->limit(0,$num)->order('status desc')->select();
	        return $res;
	    } catch (\PDOException $e) {
	        \Think\Log::write($this->name.'-getJosLogisticsNo-'.$e->getMessage());
	        E(self::PDO_ERROR);
	    }
	}
	public function retrieve($ids,&$result_info)
	{
		try{
			$error_list = array();
			$waybill_map = array();
			$cancel_info = array();
            $cancel_jos_info = array();
			$waybill_info =$this->fetchSql(false)->alias('sln')->field(array('sln.stockout_id','sln.status','st.src_order_id as trade_id','st.stockout_no','sln.logistics_id','sln.logistics_no','sln.rec_id','cl.logistics_name','cl.bill_type'))->join('left join stockout_order st on st.stockout_id = sln.stockout_id')->join('left join cfg_logistics cl on cl.logistics_id = sln.logistics_id')->where(array('sln.rec_id'=>array('in',$ids)))->select();
			foreach($waybill_info as $key =>$waybill_item)
			{
				$waybill_map["{$waybill_item['rec_id']}"] = $waybill_item;
				if($waybill_item['status'] != 5  && $waybill_item['status'] != 4){
					$error_list[] = array('id'=>$waybill_item['rec_id'],'logistics_name'=>$waybill_item['logistics_name'],'logistics_no'=>$waybill_item['logistics_no'],'msg'=>'必须是处于待回收、回收失败的电子面单');
					continue;
				}
				if($waybill_item['bill_type'] != 2 && $waybill_item['bill_type'] != 1 && $waybill_item['bill_type'] != 9){
					$error_list[] = array('id'=>$waybill_item['rec_id'],'logistics_name'=>$waybill_item['logistics_name'],'logistics_no'=>$waybill_item['logistics_no'],'msg'=>'非电子面单，不能回收');
					continue;
				}

                if($waybill_item['bill_type'] == 1){
                    $cancel_jos_info["{$waybill_item['logistics_id']}"][] = $waybill_item['rec_id'];
                    $jos_order_info[$waybill_item['rec_id']] = array('trade_id'=>$waybill_item['trade_id'],'logistics_no'=>$waybill_item['logistics_no']);
                    continue;
                }
				$cancel_info["{$waybill_item['logistics_id']}"][] = $waybill_item['rec_id'];
			}
            if(count($cancel_info) > 0){          
    			foreach($cancel_info as $key=>$value)
    			{
    				$result = D('Stock/WayBill','Controller')->cancelWayBill($value,$key);

    				if($result['status'] == 2)
    				{
    					$result_info['data']['fail'] = array_merge($result_info['data']['fail'],$result['data']['fail']);
    					$result_info['data']['success'] = array_merge($result_info['data']['success'],$result['data']['success']);

    				}else if ($result['status'] == 1)
    				{
    					$result_info['status'] = 2;
    					foreach($value as $k => $rec_id){
    						array_push($result_info['data']['fail'], array(
    								'id'   =>$waybill_map["{$rec_id}"]['rec_id'] ,
    								'logistics_no'   =>$waybill_map["{$rec_id}"]['logistics_no'] ,
    								'trade_id' =>$waybill_map["{$rec_id}"]['trade_id'],
    								'stock_id' =>$waybill_map["{$rec_id}"]['stockout_id'],
    								'stock_no' =>$waybill_map["{$rec_id}"]['stockout_no'],
    								'msg'      =>$result['msg']
    						));
    					}

    				}else if ($result['status'] == 0)
    				{
    					foreach($value as $k => $rec_id){
    						$result_info['data']['success'] = array_merge($result_info['data']['success'],$result['data']['success']);
    					}
    				}
    				$result_info['status'] = $result_info['status'] <= $result['status']?$result['status']:$result_info['status'];
    			
                }
        }else if(count($cancel_jos_info)>0){
                foreach ($cancel_jos_info as $key => $value) {
                   $ret = $this->where(array("logistics_id"=>$key,"rec_id"=>array("in",$value)))->save(array("status"=>6));
                   foreach ($value as $k => $v) {
                       $result_info['data']['success'][] = array("trade_id"=>$jos_order_info[$v]['trade_id'],'logistics_no'=>$jos_order_info[$v]['logistics_no'],'platform_id'=>1);
                   }
                }
            }
			if(!empty($result_info['data']['fail']) || !empty($result_info['data']['success'])){
				$cancel_result= array('cancel'=>$result_info['data']);
                include_once(APP_PATH."Platform/WayBill/util.php");
				waybill_cancel_handler($cancel_result);
			}

			if(!empty($error_list))
			{
				$result_info['status'] = 2;
				$result_info['data']['fail'] = array_merge($result_info['data']['fail'],$error_list);
			}
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}

	}
}
