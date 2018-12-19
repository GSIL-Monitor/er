<?php
namespace Trade\Model;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class SalesTradeModel extends TradeModel{
	
	/**
	 * 订单--编辑--验证
	 * @param array $trade
	 * @param array $list
	 * @param integer $userId
	 * @return boolean
	 */
	public function getEditCheckInfo(&$trade,&$list,$user_id)
	{
		$res_cfg_val=get_config_value('order_edit_must_checkout');
		if(empty($trade))
		{
			$list[]=array('trade_no'=>'','result_info'=>'无效订单');
			return false;
		}
		if($trade['freeze_reason']!=0)
		{
			$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
			return false;
		}
		/*if($res_cfg_val==1 && $trade['checkouter_id']==0)
		{
			$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'必须签出才能操作');
			return false;
		}
		if($trade['checkouter_id']!=0&&$trade['checkouter_id']!==$user_id)//未加是否是同一个人签出
		{
			$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'被其他用户签出');
			return false;
		} */
		return true;
	}
	
	/**
	 * 手工新建--订单
	 * @param array $arr_form_data
	 * @param array $arr_orders_data
	 * @param integer $user_id
	 */
	public function manualTrade($arr_form_data,$arr_orders_data,$user_id)
	{
		$is_rollback=false;
		$sql_error_info='';
		try {
			$cfg_arr=array(
					'order_allow_man_create_cod',//是否允许手工新建COD订单--0
					'order_check_warn_has_unmerge',//订单审核时提示同名未合并--1
					'order_check_merge_warn_mode',//审核时提示条件--0
					//货品映射map_trade_goods中用到,目前暂时无用
					'logistics_match_mode',//物流选择方式：全局唯一，按店铺，按仓库--0
					'sales_raw_count_exclude_gift',//订单中原始货品数量是否包含赠品--0
					'open_package_strategy',//包装策略--0
					'open_package_strategy_type',//包装策略(类型)--1
					'order_limit_real_price',//是否限制手工建单商品价格的修改--0
					'real_price_limit_value',//手工建单时商品价格修改限制值--retail_price
			);
			$res_cfg_val=get_config_value($cfg_arr,array(0,1,0,0,0,0,1,0,0,));
			if($res_cfg_val['order_allow_man_create_cod']==0 && $arr_form_data['delivery_term']==2)
			{
				SE('系统设置手工建单不能建货到付款单');
			}
			//判断系统设置的手工建单原价修改规则
			if($res_cfg_val['order_limit_real_price']==1){
				$limit_price_type = array(
					'0'=>array('id'=>'lowest_price','name'=>'最低价'),
					'1'=>array('id'=>'retail_price','name'=>'零售价'),
					'2'=>array('id'=>'market_price','name'=>'市场价')
					);
				$is_tips = '不满足系统设置的折后价限制条件，请修改有红色标注的折后价，不低于'.$limit_price_type[$res_cfg_val['real_price_limit_value']]['name'].'。';
				$res_cfg_val['real_price_limit_value'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['id'];
				for($i=0;$i<$arr_form_data['goods_type_count'];$i++){
					if($arr_orders_data[$i]['real_price'] < $arr_orders_data[$i][$res_cfg_val['real_price_limit_value']] && $arr_orders_data[$i]['gift_type'] !=2){
						SE($is_tips);
					}
				}
			}
			//-----------数据整理--系统订单--------------------
			$arr_form_data['trade_type']=set_default_value($arr_form_data['trade_type'],0);
			$arr_form_data['platform_id']=0;
			$arr_form_data['trade_status']=30;
			$arr_form_data['pay_time']=$arr_form_data['trade_time'];
			$arr_form_data['trade_from']=2;
			$arr_form_data['logistics_id']=set_default_value($arr_form_data['logistics_id'], 0);
			$arr_form_data['warehouse_id']=set_default_value($arr_form_data['warehouse_id'], 0);
			$arr_form_data['salesman_id']=set_default_value($arr_form_data['salesman_id'], 0);
			$arr_form_data['cs_remark']=set_default_value($arr_form_data['cs_remark'], '');
			$arr_form_data['invoice_type']=set_default_value($arr_form_data['invoice_type'], 0);
			$arr_form_data['cod_amount']=($arr_form_data['delivery_term']==2?$arr_form_data['receivable']-$arr_form_data['paid']:0);
			//$arr_form_data['created']=array('exp','NOW()');
			//-----------数据整理--原始订单--------------------
			$arr_api_trade=$arr_form_data;
			$arr_api_trade['tid']=set_default_value($arr_api_trade['src_tids'], '');
			$arr_api_trade['order_count']=$arr_form_data['goods_type_count'];
			$arr_api_trade['is_preorder']=0;
			$arr_api_trade['trade_status']=10;
			$arr_api_trade['guarantee_mode']=2;
			$arr_api_trade['process_status']=20;
			$arr_api_trade['received']=$arr_form_data['paid'];
			$arr_api_trade['buyer_area']=$arr_form_data['receiver_area'];
			$arr_api_trade['receiver_hash']=md5($arr_form_data['receiver_province'].' '.$arr_form_data['receiver_city'].' '.$arr_form_data['receiver_district']);
			$arr_api_trade['pay_method']=set_default_value($arr_form_data['pay_method'], 0);
			$arr_api_trade['invoice_type']=$arr_form_data['invoice_type'];
			$arr_api_trade['buyer_name']=$arr_form_data['receiver_name'];
			$arr_api_trade['dap_amount']=$arr_form_data['paid'];
			$arr_api_trade['pi_amount']=$arr_form_data['delivery_term']==4?$arr_form_data['receivable']-$arr_form_data['paid']:0;
			$arr_api_trade['x_logistics_id']=$arr_form_data['logistics_id'];
			$arr_api_trade['x_warehouse_id']=$arr_form_data['warehouse_id'];
			$arr_api_trade['x_salesman_id']=$arr_form_data['salesman_id'];
			$arr_api_trade['x_trade_flag']=$arr_form_data['flag_id'];
			$arr_api_trade['x_customer_id']=set_default_value($arr_api_trade['customer_id'], 0);
			$arr_api_trade['remark']=$arr_form_data['cs_remark'];
			unset($arr_api_trade['logistics_id']);
			unset($arr_api_trade['warehouse_id']);
			unset($arr_api_trade['salesman_id']);
			unset($arr_api_trade['customer_id']);
			unset($arr_api_trade['flag_id']);
			unset($arr_api_trade['goods_type_count']);
			unset($arr_api_trade['trade_type']);
			unset($arr_api_trade['src_tids']);
			unset($arr_api_trade['receiver_dtb']);
			unset($arr_api_trade['weight']);
			unset($arr_api_trade['cs_remark']);
			//-----------数据整理--原始子订单--------------------
			$arr_api_orders=array();
			$goods_count=0;
			$goods_count_round=0;
			for ($i=0;$i<$arr_api_trade['order_count'];$i++)
			{
				$goods_count+=$arr_orders_data[$i]['num'];
				$goods_count_round+=round($arr_orders_data[$i]['num']);
				$arr_api_orders[]=array(
						'platform_id'=>0,
						'shop_id'=>$arr_form_data['shop_id'],
						//'tid'=>$arr_api_trade['tid'],
						//'oid'=>get_sys_no('apiorder',1),
						'status'=>30,
						'process_status'=>10,
						'is_invalid_goods'=>0,
						'goods_id'=>set_default_value($arr_orders_data[$i]['goods_id'],''),
						'goods_no'=>set_default_value($arr_orders_data[$i]['goods_no'],''),
						'goods_name'=>set_default_value($arr_orders_data[$i]['goods_name'],''),
						'spec_id'=>set_default_value($arr_orders_data[$i]['spec_id'],''),
						'spec_no'=>set_default_value($arr_orders_data[$i]['spec_no'],''),
						'spec_name'=>set_default_value($arr_orders_data[$i]['spec_name'],''),
						'spec_code'=>set_default_value($arr_orders_data[$i]['spec_code'],''),
						'num'=>$arr_orders_data[$i]['num'],
						'price'=>$arr_orders_data[$i]['original_price'],
						'discount'=>($arr_orders_data[$i]['original_price']-$arr_orders_data[$i]['real_price'])*$arr_orders_data[$i]['num'],//$arr_orders_data[$i]['discount'],
						'share_amount'=>$arr_orders_data[$i]['real_price']*$arr_orders_data[$i]['num'],
						'total_amount'=>$arr_orders_data[$i]['total_price'],
						'gift_type'=>set_default_value($arr_orders_data[$i]['gift_type'], 0),
						'remark'=>set_default_value($arr_orders_data[$i]['cs_remark'],''),
						//'share_post'=>$arr_orders_data[$i]['share_post'],
						//'paid'=>$arr_orders_data[$i]['paid'],
						'created'=>array('exp','NOW()'),
				);
			}
			$arr_api_trade['goods_count']=$goods_count;
			$arr_form_data['goods_count']=$goods_count;
			$arr_form_data['goods_count_round']=$goods_count_round;
			//开启事务
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$is_rollback=true;
			$this->startTrans();
			//-----------整理--添加--客户信息----------------------
			if ($arr_form_data['customer_id']<=0)
			{
				/*$customer=array(
						'customer_no'=>get_sys_no('customer',1),
						'type'=>0,
						'nickname'=>$arr_form_data['buyer_nick'],
						'name'=>$arr_form_data['receiver_name'],
						'province'=>$arr_form_data['receiver_province'],
						'city'=>$arr_form_data['receiver_city'],
						'district'=>$arr_form_data['receiver_district'],
						'area'=>$arr_form_data['receiver_area'],
						'address'=>$arr_form_data['receiver_address'],
						'zip'=>$arr_form_data['receiver_zip'],
						'telno'=>$arr_form_data['receiver_telno'],
						'mobile'=>$arr_form_data['receiver_mobile'],
						'remark'=>$arr_form_data['cs_remark'],
						'trade_count'=>1,
						'trade_amount'=>$arr_form_data['receivable'],
						'last_trade_time'=>array('exp','NOW()'),
						'created'=>array('exp','NOW()'),
				);
				$sql_error_info='manualTrade-add_crm_customer';
				$arr_form_data['customer_id']=M('crm_customer')->add($customer);
				$sql_error_info='manualTrade-add_crm_platform_customer';
				M('crm_platform_customer')->add(array('platform_id'=>0,'account'=>$customer['nickname'],'customer_id'=>$arr_form_data['customer_id']));*/
				$arr_form_data['customer_id']=D('Customer/Customer')->addCustomerByTrade($arr_form_data);
			}else{
				$sql_error_info="manule_trade-update_customer_trade";
				$this->execute("UPDATE crm_customer SET trade_count=trade_count+1, trade_amount=trade_amount+".$arr_form_data['receivable']." WHERE customer_id=%d",$arr_form_data['customer_id']);
			}
			//-----------整理--添加--客户地址----------------------
			D('Customer/CustomerAddress')->addAddressByTrade($arr_form_data);
			/*$arr_customer_address=array(
					'customer_id'=>$arr_form_data['customer_id'],
					'name'=>$arr_form_data['receiver_name'],
					'addr_hash'=>md5($arr_form_data['receiver_province'].$arr_form_data['receiver_city'].$arr_form_data['receiver_district'].$arr_form_data['receiver_address']),
					'province'=>$arr_form_data['receiver_province'],
					'city'=>$arr_form_data['receiver_city'],
					'district'=>$arr_form_data['receiver_district'],
					'address'=>$arr_form_data['receiver_address'],
					'zip'=>$arr_form_data['receiver_zip'],
					'telno'=>$arr_form_data['receiver_telno'],
					'mobile'=>$arr_form_data['receiver_mobile'],
					'email'=>'',
					'created'=>array('exp','NOW()'),
			);
			$sql_error_info='manualTrade-add_crm_customer_address';
			D('Customer/CustomerAddress')->addAddress($arr_customer_address);*/
			//-----------整理--添加--固话和手机----------------------
			D('Customer/CustomerTelno')->addTelnoByTrade($arr_form_data);
			/*$arr_customer_telno=array(
					'customer_id'=>$arr_form_data['customer_id'],
					'type'=>1,
					'telno'=>$arr_form_data['receiver_mobile'],
					'created'=>array('exp','NOW()'),
			);
			if(!empty($arr_form_data['receiver_mobile']))
			{
				$sql_error_info='manualTrade-add_crm_customer_mobile';
				D('Customer/CustomerTelno')->addTelno($arr_customer_telno);
			}
			if(!empty($arr_form_data['receiver_telno']))
			{
				$arr_customer_telno['type']=2;
				$arr_customer_telno['telno']=$arr_form_data['receiver_telno'];
				$sql_error_info='manualTrade-add_crm_customer_telno';
				D('Customer/CustomerTelno')->addTelno($arr_customer_telno);
			}*/
			//-----------添加原始单->原始子订单->订单->子订单----------------------
			$this->addTrade($arr_form_data, $arr_api_trade, $arr_api_orders, $user_id,$res_cfg_val);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
	}
	/**
	 * 编辑--子订单
	 */
	public function editTradeOrder($trade,$trade_orders,&$trade_log,$user_id)
	{
		$sql_error_info='';
		try {
			$arr_order_ids=array();
			foreach ($trade_orders as $o)
			{
				$arr_order_ids[]=$o['id'];
			}
			$sql_error_info='editTradeOrder-get_old_orders';
			$trade_order_db=D('SalesTradeOrder');
			$res_trade_orders=$trade_order_db->getSalesTradeOrderList(
					'sto.rec_id,sto.platform_id,sto.spec_id,sto.actual_num, sto.spec_name,sto.share_amount,
					 sto.share_post,sto.paid,sto.gift_type,sto.trade_id,sto.is_print_suite,gs.weight',
					array('sto.rec_id'=>array('in',$arr_order_ids)),
					'sto',
					'LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id'
			);
			foreach ($res_trade_orders as $o)
			{
				$res_trade_orders[strval($o['rec_id'])]=$o;
			}
			foreach ($trade_orders as $o)
			{//后期加判断--目前暂时屏蔽不相关数据
				$arr_update=array();
				$arr_update_field=array();
				$o['rec_id']=strval($o['id']);
				unset($o['id']);
				if($res_trade_orders[$o['rec_id']]['trade_id']!=$o['trade_id'])
				{
					SE('子订单不属于主订单');
				}
				if($o['is_suite']!=0&&$o['spec_id']!=$res_trade_orders[$o['rec_id']]['spec_id'])
				{
					SE('无效参数');
				}
				if($res_trade_orders[$o['rec_id']]['actual_num']==$o['actual_num'] && $res_trade_orders[$o['rec_id']]['share_amount']==$o['share_amount'] && $res_trade_orders[$o['rec_id']]['share_post']==$o['share_post'] && $res_trade_orders[$o['rec_id']]['spec_name']==$o['spec_name'] && $res_trade_orders[$o['rec_id']]['remark']==$o['remark'])
				{//数量和总分摊没变->分摊价格没变->优惠没变(暂时修改规格名,后期改成修改spec_id->$res_order['spec_id']==$order['spec_id'])
					continue;
				}
				if(($res_trade_orders[$o['rec_id']]['actual_num']!=$o['actual_num']) && $trade['warehouse_id']!=0)
				{//数量变化或更换规格, 先收回库存
					$sql_error_info='editTradeOrder-stock_change_record';
					$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'编辑订单，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($trade['warehouse_id'])." WHERE sto.stock_reserved>=2 AND sto.rec_id=".intval($o['rec_id']).");");
					$sql_error_info='editTradeOrder-update_stock_spec';
					$this->execute('INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
						 (SELECT '.intval($trade['warehouse_id']).',spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
						 IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
						 FROM sales_trade_order WHERE rec_id=%d ORDER BY spec_id)
						 ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
						 sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num)',
				$o['rec_id']);
					$arr_update_field['stock_reserved']=0;
				}
				if($res_trade_orders[$o['rec_id']]['spec_name']!=$o['spec_name'])
				{
					$o['id']=$o['spec_id'];
					$change_order=array();
					$change_order[0]=$o;
					$this->exchangeOrder(array($o['sto_id']), $change_order, $trade['version_id'], $user_id);
// 					$trade_log[]=array(
// 							'trade_id'=>$trade['trade_id'],
// 							'operator_id'=>$user_id,
// 							'type'=>18,
// 							'data'=>$o['spec_id'],
// 							'message'=>'修改规格:'.$res_trade_orders[$o['rec_id']]['spec_name'].' 到 '.$o['spec_name'],
// 							'created'=>date('y-m-d H:i:s',time())
// 					);
// 					$arr_update=array(
// 							'spec_id'=>$o['spec_id'],
// 							'spec_no'=>$o['spec_no'],
// 							'spec_name'=>$o['spec_name'],
// 							'spec_code'=>$o['spec_code'],
// 							'price'=>$o['price'],
// 							'weight'=>$o['weight'],
// 							'discount'=>$o['discount'],
// 					);
				}else{
				//-----线上---------
					if($o['platform_id']>0)
					{
						if($res_trade_orders[$o['rec_id']]['actual_num']!=$o['num'])
						{
							SE('在线订单不可修改货品数量');
						}
						$arr_update=array(
								'spec_name'=>$res_trade_orders[$o['rec_id']]['spec_name'],
								'order_price'=>$o['share_price'],
								'remark'=>$o['remark'],
						);
						$trade_log[]=array(
								'trade_id'=>$trade['trade_id'],
								'operator_id'=>$user_id,
								'type'=>61,
								'data'=>$o['spec_id'],
								'message'=>'分摊货品 商家编码:'.$o['spec_no'].' 名称:'.$o['goods_name'].'--规格:'.$o['spec_name'].'--原分摊总价:'.$res_trade_orders[$o['rec_id']]['share_amount'],
								'created'=>date('y-m-d H:i:s',time())
						);
					}else
					{//------线下---------
						if($res_trade_orders[$o['rec_id']]['paid']>0&&($res_trade_orders[$o['rec_id']]['actual_num']!=$o['actual_num'] || $res_trade_orders[$o['rec_id']]['share_amount']!=$o['share_amount'] || $res_trade_orders[$o['rec_id']]['share_post']!=$o['share_post']))
						{
							SE('不能修改已付款子订单,请按退款流程处理');
						}
						
						$arr_update=array(
								'spec_name'=>$res_trade_orders[$o['rec_id']]['spec_name'],
								'order_price'=>$o['share_price'],
								'actual_num'=>$o['actual_num'],
								'num'=>$o['actual_num'],
								'share_price'=>$o['share_price'],
								'share_amount'=>$o['share_amount'],
								'discount'=>$o['discount'],
								'share_post'=>$o['share_post'],
								'remark'=>$o['remark'],
						);
						$trade_log[]=array(
								'trade_id'=>$trade['trade_id'],
								'operator_id'=>$user_id,
								'type'=>61,
								'data'=>$o['spec_id'],
								'message'=>'修改货品 商家编码:'.$o['spec_no'].' 名称:'.$o['goods_name'].'--规格:'.$o['spec_name'].'--数量:'.$res_trade_orders[$o['rec_id']]['actual_num'],
								'created'=>date('y-m-d H:i:s',time())
						);
					}
				}
				if(isset($arr_update_field['stock_reserved']))
				{
					$arr_update['stock_reserved']=$arr_update_field['stock_reserved'];
					unset($arr_update_field['stock_reserved']);
				}
				$sql_error_info='editTradeOrder-update_order';
				$trade_order_db->updateSalesTradeOrder(
						$arr_update,
						array('rec_id'=>array('eq',$o['rec_id']),'trade_id'=>array('eq',$trade['trade_id']))
				);
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	/**
	 * 添加--子订单(线下)
	 */
	public function addTradeOrder($trade,$trade_orders,$user_id,$api_trade_id=0,$tid='',$remark='')
	{
		$sql_error_info='';
		try {
			$i=0;
			$api_trade_db=D('ApiTrade');
			$api_order_db=D('ApiTradeOrder');
			$arr_trade_log=array();
			$arr_order_ids=array();
			foreach ($trade_orders as $o)
			{
				if($api_trade_id==0)
				{
					$tid=get_sys_no('apitrade');
					$arr_api_trade=array(
							'platform_id'=>0,
							'tid'=>$tid,
							'shop_id'=>$trade['shop_id'],
							'process_status'=>20,
							'trade_status'=>30,
							'guarantee_mode'=>2,
							'pay_status'=>2,
							'delivery_term'=>1,
							'pay_method'=>1,
							'order_count'=>1,
							'goods_count'=>$o['actual_num'],
							'trade_time'=>array('exp','NOW()'),
							'pay_time'=>array('exp','NOW()'),
							'buyer_nick'=>set_default_value($trade['buyer_nick'],''),
							'buyer_name'=>set_default_value($trade['receiver_name'], ''),
							'buyer_area'=>set_default_value($trade['receiver_area'],''),
							'pay_id'=>'',
							'receiver_name'=>set_default_value($trade['receiver_name'],''),
							'receiver_province'=>set_default_value($trade['receiver_province'],0),
							'receiver_city'=>set_default_value($trade['receiver_city'],0),
							'receiver_district'=>set_default_value($trade['receiver_district'],0),
							'receiver_address'=>$trade['receiver_address'],
							'receiver_mobile'=>set_default_value($trade['receiver_mobile'],''),
							'receiver_telno'=>set_default_value($trade['receiver_telno'],''),
							'receiver_zip'=>set_default_value($trade['receiver_zip'],''),
							'receiver_area'=>set_default_value($trade['receiver_area'],''),
							'receiver_hash'=>md5($trade['receiver_province'].' '.$trade['receiver_city'].' '.$trade['receiver_district']),
							'goods_amount'=>0,
							'post_amount'=>0,
							'discount'=>0,
							'receivable'=>0,
							'paid'=>0,
							'received'=>0,
							'invoice_type'=>0,
							'invoice_title'=>'',
							'invoice_content'=>'',
							'trade_from'=>2,
							'created'=>array('exp','NOW()'),
					);
					$sql_error_info='addTradeOrder-add_api_trade';
					$api_trade_id=$api_trade_db->addApiTrade($arr_api_trade);
				}
				$arr_api_order=array(
						'platform_id'=>0,
						'shop_id'=>$trade['shop_id'],
						'tid'=>$tid,
						'oid'=>array('exp','FN_SYS_NO("apiorder")'),
						'status'=>30,
						'process_status'=>10,
						'gift_type'=>$o['gift_type'],
						'num'=>$o['actual_num'],
						'price'=>set_default_value($o['price'],0),//set_default_value($o['retail_price'],set_default_value($o['price'],0)),
						'discount'=>floatval(set_default_value($o['price'],0))*$o['num']-$o['share_amount'],
						'total_amount'=>floatval(set_default_value($o['price'],0))*$o['num'],
						'share_amount'=>$o['share_amount'],
						'share_post'=>$o['share_post'],
						'paid'=>0,
						'remark'=>set_default_value($o['remark'],''),
						'created'=>array('exp','NOW()')
				);
				$arr_trade_log[$i]=array(
						'type'=>60,
						'trade_id'=>$trade['trade_id'],
						'data'=>$o['id'],
						'operator_id'=>$user_id,
						'created'=>date('y-m-d H:i:s',time())
				);
				if($o['is_suite']==0){
					$arr_api_order['goods_id']=intval($o['goods_id']);
					$arr_api_order['goods_no']=$o['goods_no'];
					$arr_api_order['spec_id']=intval($o['id']);
					$arr_api_order['spec_no']=$o['spec_no'];
					$arr_api_order['goods_name']=$o['goods_name'];
					$arr_api_order['spec_name']=$o['spec_name'];
					$arr_api_order['spec_code']=$o['spec_code'];
					if($o['gift_type']>0)
					{
						$arr_trade_log[$i]['message']='添加赠品，';
					}else{
						$arr_trade_log[$i]['message']='添加单品，';
					}
					$arr_trade_log[$i]['message'].='商家编码：'.$o['spec_no'].' 货品名称： '.$o['goods_name'].' 规格名称： '.$o['spec_name'].' 数量： '.$o['actual_num'];
				}else{
					$arr_api_order['goods_id']=intval($o['id']);
					$arr_api_order['goods_no']=intval($o['suite_no']);
					$arr_api_order['spec_id']=intval($o['id']);
					$arr_api_order['spec_no']=$o['suite_no'];
					$arr_api_order['goods_name']=$o['suite_name'];
					$arr_api_order['spec_name']='';
					$arr_api_order['spec_code']='';
					if($o['gift_type']>0)
					{
						$arr_trade_log[$i]['message']='添加赠品，';
					}else{
						$arr_trade_log[$i]['message']='添加货品，';
					}
					$arr_trade_log[$i]['message'].='组合装商家编码：'.$o['suite_no'].' 名称： '.$o['suite_name'].' 数量： '.$o['actual_num'];
				}
				$sql_error_info='addTradeOrder-add_api_trade_order';
				$res_api_order_id=$api_order_db->add($arr_api_order);
				$arr_order_ids[]=$res_api_order_id;
				$goods_count+=$o['actual_num'];
				$i++;
			}
			$sql_error_info='addTradeOrder-map_trade_goods';
			D('Trade/Trade')->map_trade_goods($trade['trade_id'], $api_trade_id,0,false);
			if(!empty($arr_order_ids))
			{
				$sql_error_info='addTradeOrder-update_api_trade_orders';
				$api_order_db->updateApiTradeOrder(
						array('process_status'=>20),
						array('rec_id'=>array('in',$arr_order_ids))
				);
			}
			D('SalesTradeLog')->addTradeLog($arr_trade_log);
			$sql_error_info='addTradeOrder-count_api_trade_orders';
			$res_count_arr=$this->query('SELECT SUM(share_amount+discount) goods_amount, SUM(share_post) post_amount,SUM(discount) discount FROM api_trade_order ato WHERE platform_id=0 AND tid=\'%s\'',array($tid));
			$sql_error_info='addTradeOrder-update_api_trade';
			$res_count_arr[0]['receivable']=$res_count_arr[0]['goods_amount']+$res_count_arr[0]['post_amount'];
			$this->execute('UPDATE api_trade `at` SET `at`.goods_amount ='.$res_count_arr[0]['goods_amount'].', `at`.post_amount ='.$res_count_arr[0]['post_amount'].', `at`.discount ='.$res_count_arr[0]['discount'].', `at`.receivable='.$res_count_arr[0]['receivable'].', `at`.modify_flag=0 WHERE rec_id='.intval($api_trade_id));
			return $api_trade_id;
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	/**
	 * 编辑--订单
	 */
	public function editTrade($trade,$trade_orders,$user_id)
	{
		$is_rollback=false;
		$sql_error_info='';
		$flag_changes_goods=false;//标记子订单有改变
		$flag_changes_csremark=false;//标记客服备注有改变
		try {
			$cfg_arr=array(
					'order_check_warn_has_unmerge',//订单审核时提示同名未合并--1
					'order_check_merge_warn_mode',//审核时提示条件--0
					//货品映射map_trade_goods中用到,目前暂时无用
					'logistics_match_mode',//物流选择方式：全局唯一，按店铺，按仓库--0  
					'open_package_strategy',//包装策略--0
					'order_check_alert_locked_warehouse',//仓库是按货品策略选出,修改时给出提醒--0
			);
			$res_cfg_val=get_config_value($cfg_arr,array(1,0,0,0,0));
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			//-----------------订单信息校验-------------------------
			$sql_error_info='editTrade-get_trade';
			$res_trade=$this->getSalesTradeOnLock(
					'trade_id, trade_status,freeze_reason,checkouter_id,buyer_nick,receiver_name,receiver_mobile, 
					 receiver_telno,receiver_zip,receiver_address, receiver_area,receiver_dtb,cs_remark,print_remark, 
					 shop_id,warehouse_id,logistics_id,salesman_id, platform_id,trade_type,delivery_term,goods_count,
					 package_id,customer_id, post_cost, invoice_type,invoice_title,invoice_content,version_id,paid,receivable',
					array('trade_id'=>array('eq',$trade['trade_id']))
			);
			if(empty($res_trade))
			{
				SE('订单不存在');
			}
			if($res_trade['version_id']!=$trade['version_id'])
			{
				SE('订单被其他人修改，请打开重新编辑');
			}
			if($res_trade['trade_status']!=30 && $res_trade['trade_status']!=25)
			{
				SE('订单非待审核状态，不可编辑');
			}
			if($res_trade['freeze_reason']>0)
			{
				SE('订单已冻结');
			}
			if(count($trade_orders['delete'])==$res_trade['goods_count']) 
			{
				SE('订单至少保留一件货品');
			}
			if($res_trade['platform_id']>0)
			{
				if($res_trade['shop_id']!=$trade['shop_id'])
				{
					SE('在线订单不可修改店铺');
				}
				if($res_trade['trade_type']!=$trade['trade_type'])
				{
					SE('在线订单不可修改订单类型');
				}
				if($res_trade['delivery_term']!=$trade['delivery_term'])
				{
					SE('在线订单不可修改发货条件');
				}
			}else{
				if($res_trade['trade_type']!=1 && $trade['trade_type']==1)
				{
					SE('不可手工新建在线订单');
				}
			}
			if($res_trade['paid']==$res_trade['receivable'] && $res_trade['delivery_term']!=$trade['delivery_term'] &&($trade['delivery_term']==2||$trade['delivery_term']==4))
			{
				SE('已经完全付款的不允许修改发货方式为分期付款或者货到付款');
			}
			/* 单仓库--暂时不考虑--修改库存 */
			/* $sql_error_info='editTrade-query_warehouse';
			 $res_warehouse=$model_db->query("SELECT 1 FROM sales_trade_order sto,api_trade_order ato,api_trade ax WHERE sto.trade_id=P_TradeID AND sto.actual_num>0 AND ato.platform_id=sto.platform_id AND ato.oid=sto.src_oid AND ax.platform_id=ato.platform_id AND ax.tid=ato.tid AND ax.x_warehouse_id=%d",array($trade['warehouse_id']));
			 if($res_cfg_val['order_check_alert_locked_warehouse']!=0 && $trade['warehouse_id']!=0 && $trade['warehouse_id']!=$res_trade['warehouse_id'] && !empty($res_warehouse))
			 {
			 E('仓库需要强制修改');
			 } */
			/* 校验不可拆分组合装的情况 --后期加上*/
			$trade_log=array();
			//-----------------子订单更新-------------------------
			$sql_error_info='editTrade-share_old_order';
			$res_share_old=$this->query('SELECT SUM(share_amount) AS share_amount,SUM(share_post) AS share_post FROM sales_trade_order WHERE trade_id=%d AND platform_id>0',array($trade['trade_id']));
			if (!empty($trade_orders['update']))
			{
				$this->editTradeOrder($res_trade, $trade_orders['update'], $trade_log, $user_id);
				$flag_changes_goods=true;
			}
			//-----------------子订单添加-------------------------
			$api_trade_id=0;
			if (!empty($trade_orders['add']))
			{
				$api_trade_id=$this->addTradeOrder($res_trade, $trade_orders['add'], $user_id);
				$flag_changes_goods=true;
			}
			//-----------------子订单删除-------------------------
			if (!empty($trade_orders['delete']))
			{
				$del_ids='';
				foreach ($trade_orders['delete'] as $o)
				{
					if($o['platform_id']>0)
					{
						SE('在线订单不可删除货品');
					}else{
						$del_ids.=$o['id'].',';
					}
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>23,
							'data'=>0,
							'message'=>'删除订单货品 货品名称：'.$o['goods_name'].' 商家编码：'.$o['spec_no'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				$sql_error_info='editTrade-stock_change_record';
				$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'编辑订单，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=%d WHERE sto.stock_reserved>=2 AND sto.rec_id IN(".intval(substr($del_ids,0,-1))."));",array($res_trade['warehouse_id']));
				$sql_error_info='editTrade-update_stock_spec';
				$this->execute('UPDATE stock_spec ss,sales_trade_order sto SET ss.unpay_num=ss.unpay_num-IF(stock_reserved=2,actual_num,0), ss.order_num=ss.order_num-IF(stock_reserved=3,actual_num,0), ss.sending_num=ss.sending_num-IF(stock_reserved=4,actual_num,0), ss.subscribe_num=ss.subscribe_num-IF(stock_reserved=5,actual_num,0) WHERE sto.rec_id IN('.substr($del_ids,0,-1).') AND ss.warehouse_id=%d AND ss.spec_id=sto.spec_id',array($res_trade['warehouse_id']));
				$sql_error_info='editTrade-delete_order';
				$this->execute('DELETE FROM sales_trade_order WHERE rec_id IN('.substr($del_ids,0,-1).')');
				$flag_changes_goods=true;
			}
			$sql_error_info='editTrade-share_new_order';
			$res_share_new=$this->query('SELECT SUM(share_amount) AS share_amount,SUM(share_post) AS share_post FROM sales_trade_order WHERE trade_id=%d AND platform_id>0',array($trade['trade_id']));
			if($res_share_new[0]['share_amount']!=$res_share_old[0]['share_amount'])
			{
				SE('货款分摊不正确');
			}
			if($res_share_new[0]['share_post']!=$res_share_old[0]['share_post'])
			{
				SE('邮费分摊不正确');
			}
			//-----------------订单更新-------------------------
			$sql_error_info='editTrade-save_trade';
			$trade['version_id']+=1;
			$res_update_trade=$this->updateSalesTrade($trade,array('trade_id'=>array('eq',$trade['trade_id'])));
			if($res_update_trade)
			{
				if($trade['receiver_name']!=$res_trade['receiver_name'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>40,
							'data'=>0,
							'message'=>'修改收货人姓名：从 '.$res_trade['receiver_name'].' 到 '.$trade['receiver_name'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				$cfg_show_telno=get_config_value('show_number_to_star',1);
				if(isset($trade['receiver_telno'])&&$trade['receiver_mobile']!=$res_trade['receiver_mobile'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>41,
							'data'=>0,
							'message'=>$cfg_show_telno==1?'修改手机号':'修改手机号码：从 '.$res_trade['receiver_mobile'].' 到 '.$trade['receiver_mobile'],
							'created'=>date('y-m-d H:i:s',time())
					);
					$arr_customer_telno=array(
							'customer_id'=>$res_trade['customer_id'],
							'data'=>0,
							'type'=>1,
							'telno'=>$trade['receiver_mobile'],
							'created'=>array('exp','NOW()'),
					);
					$sql_error_info='editTrade-add_crm_customer_mobile';
					D('Customer/CustomerTelno')->addTelno($arr_customer_telno);
				}
				if(isset($trade['receiver_telno'])&&$trade['receiver_telno']!=$res_trade['receiver_telno'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>42,
							'data'=>0,
							'message'=>$cfg_show_telno==1?'修改固话':'修改固话：从 '.$res_trade['receiver_telno'].' 到 '.$trade['receiver_telno'],
							'created'=>date('y-m-d H:i:s',time())
					);
					$arr_customer_telno=array(
							'customer_id'=>$res_trade['customer_id'],
							'type'=>2,
							'data'=>0,
							'telno'=>$trade['receiver_mobile'],
							'created'=>array('exp','NOW()'),
					);
					$sql_error_info='editTrade-add_crm_customer_telno';
					D('Customer/CustomerTelno')->addTelno($arr_customer_telno);
				}
				if($trade['receiver_address']!=$res_trade['receiver_address'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>44,
							'data'=>0,
							'message'=>'修改收货人地址：从 '.$res_trade['receiver_address'].' 到 '.$trade['receiver_address'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if(!empty($trade['receiver_area'])&&$trade['receiver_area']!=$res_trade['receiver_area'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>44,
							'data'=>0,
							'message'=>'修改收货人省市区：从 '.$res_trade['receiver_area'].' 到 '.$trade['receiver_area'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['receiver_address']!=$res_trade['receiver_address']||$trade['receiver_area']!=$res_trade['receiver_area'])
				{//更新地址库信息
					$arr_customer_address=array(
							'customer_id'=>$res_trade['customer_id'],
							'name'=>$trade['receiver_name'],
							'addr_hash'=>md5($trade['receiver_province'].$trade['receiver_city'].$trade['receiver_district'].$trade['receiver_address']),
							'province'=>$trade['receiver_province'],
							'city'=>$trade['receiver_city'],
							'district'=>$trade['receiver_district'],
							'address'=>$trade['receiver_address'],
							'zip'=>$trade['receiver_zip'],
							'telno'=>$trade['receiver_telno'],
							'mobile'=>$trade['receiver_mobile'],
							'email'=>'',
							'created'=>array('exp','NOW()'),
					);
					$sql_error_info='addTrade-add_crm_customer_address';
					D('Customer/CustomerAddress')->addAddress($arr_customer_address);
				}
				//---------更新标记同名未合并-------------------------------------------
				if($res_cfg_val['order_check_warn_has_unmerge']!=0)
				{
					$this->updateWarnUnmerge($trade['trade_id'],$res_trade['customer_id']);
				}
				if($trade['receiver_dtb']!=$res_trade['receiver_dtb'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>47,
							'data'=>0,
							'message'=>'修改收货人地址大头笔：从 '.$res_trade['receiver_dtb'].' 到 '.$trade['receiver_dtb'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['cs_remark']!=$res_trade['cs_remark'])
				{
					$flag_changes_csremark=true;
					$this->execute('UPDATE sales_trade SET cs_remark_change_count=cs_remark_change_count|2 WHERE trade_id = '.intval($trade['trade_id']));
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>43,
							'data'=>0,
							'message'=>'修改客服备注：从 '.$res_trade['cs_remark'].' 到 '.$trade['cs_remark'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['print_remark']!=$res_trade['print_remark'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>49,
							'data'=>0,
							'message'=>'修改打印备注：从 '.$res_trade['print_remark'].' 到 '.$trade['print_remark'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['shop_id']!=$res_trade['shop_id'])
				{
					if($trade['platform_id']==0){
						$this->execute('UPDATE sales_trade_order SET shop_id='.$trade['shop_id'].' WHERE trade_id='.$trade['trade_id']);
					}
					$res_dict_arr=M('cfg_shop')->field('shop_id AS id,shop_name AS name')
					                           ->where(array('shop_id'=>array('in',array($trade['shop_id'],$res_trade['shop_id']))))
					                           ->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>48,
							'data'=>0,
							'message'=>'修改店铺 ：从 '.$res_dict_arr[$res_trade['shop_id']].' 到 '.$res_dict_arr[$trade['shop_id']],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['warehouse_id']!=$res_trade['warehouse_id'])
				{
					$res_dict_arr=M('cfg_warehouse')->field('warehouse_id AS id,name')
					                                ->where(array('warehouse_id'=>array('in',array($trade['warehouse_id'],$res_trade['warehouse_id']))))
					                                ->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>34,
							'data'=>0,
							'message'=>'修改仓库 ：从 '.$res_dict_arr[$res_trade['warehouse_id']].' 到 '.$res_dict_arr[$trade['warehouse_id']],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['logistics_id']!=$res_trade['logistics_id'])
				{
					$res_dict_arr=M('cfg_logistics')->field('logistics_id AS id,logistics_name AS name')
					                                ->where(array('logistics_id'=>array('in',array($trade['logistics_id'],$res_trade['logistics_id']))))
					                                ->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>20,
							'data'=>0,
							'message'=>'修改物流 ：从 '.$res_dict_arr[$res_trade['logistics_id']].' 到 '.$res_dict_arr[$trade['logistics_id']],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['post_cost'] != $res_trade['post_cost']){
					$trade_log[] = array(
							'trade_id' => $trade['trade_id'],
							'operator_id' => $user_id,
							'type' => 170,
							'data' => 0,
							'message' => '修改预估邮费：从 '.$res_trade['post_cost'].' 到 '.$trade['post_cost'],
							'created' => date('y-m-d H:i:s',time())
						);
				}
				if($trade['salesman_id']!=$res_trade['salesman_id'])
				{
					$res_dict_arr=M('hr_employee')->field('employee_id AS id,fullname AS name')
													->where(array('employee_id'=>array('in',array($trade['salesman_id'],$res_trade['salesman_id']))))
													->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>53,
							'data'=>0,
							'message'=>'修改业务员 ：从 '.$res_dict_arr[$res_trade['salesman_id']].' 到 '.$res_dict_arr[$trade['salesman_id']],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['trade_type']!=$res_trade['trade_type'])
				{
					$arr_trade_type=array('网店销售','线下零售','售后换货','批发业务');
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>54,
							'data'=>0,
							'message'=>'修改订单类型：从 '.$arr_trade_type[$res_trade['trade_type']-1].' 到 '.$arr_trade_type[$trade['trade_type']-1],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['delivery_term']!=$res_trade['delivery_term'])
				{
					$arr_delivery_term=array('款到发货','货到付款','分期付款');
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>59,
							'data'=>0,
							'message'=>'修改发货条件：从 '.$arr_delivery_term[$res_trade['delivery_term']-1].' 到 '.$arr_delivery_term[$trade['delivery_term']-1],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['invoice_type']!=$res_trade['invoice_type'])
				{
					$arr_invoice_type=array('不需要','普通发票','增值发票');
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>56,
							'data'=>0,
							'message'=>'修改发票类型：从 '.$arr_invoice_type[$res_trade['invoice_type']].' 到 '.$arr_invoice_type[$trade['invoice_type']],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['invoice_title']!=$res_trade['invoice_title'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>57,
							'data'=>0,
							'message'=>'修改发票抬头 ：从 '.$res_trade['invoice_title'].' 到 '.$trade['invoice_title'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($trade['invoice_content']!=$res_trade['invoice_content'])
				{
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>58,
							'data'=>0,
							'message'=>'修改发票内容 ：从 '.$res_trade['invoice_content'].' 到 '.$trade['invoice_content'],
							'created'=>date('y-m-d H:i:s',time())
					);
				}
			}

			D('Trade/SalesTradeLog')->addTradeLog($trade_log);

			if(($flag_changes_goods||$res_trade['warehouse_id']!=$trade['warehouse_id'])&&$trade['warehouse_id']!=0)
			{//刷新库存
				$sql_error_info='editTrade-I_RESERVE_STOCK';
				$this->execute('CALL I_RESERVE_STOCK('.$trade['trade_id'].',IF('.$res_trade['trade_status'].'=30,3,5),'.intval($trade['warehouse_id']).','.$res_trade['warehouse_id'].')');
			}
			if($flag_changes_goods||$res_trade['logistics_id']!=$trade['logistics_id']||$flag_changes_csremark||$res_trade['warehouse_id']!=$trade['warehouse_id']||$trade['receiver_area']!=$res_trade['receiver_area'])
			{//刷新订单
				$sql_error_info='editTrade-I_DL_REFRESH_TRADE';
				$this->execute('CALL I_DL_REFRESH_TRADE('.$user_id.', '.$trade['trade_id'].', 2, 0)');
			}
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
	}

	/**
	 * 退款--子订单
	 * @param array $arr_ato
	 * @param array $dict_sto
	 * @param array $dict_tradeTrade
	 * @param integer $user_id
	 * @param array $list
	 * @param array $success
	 */
	public function refundOrder($arr_ato,$dict_sto,$dict_trade,$user_id,&$list,$is_refund_force=false)
	{
		$is_rollback=false;
		$sql_error_info='';
		try{
			//------------------正常退款操作---------------------------------
			$is_rollback=true;
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$refund_db=D('SalesRefund');
			$refund_order_db=D('SalesRefundOrder');
			$trade_order_db=D('SalesTradeOrder');
			$refund_log=array();
			$trade_log=array();
			$refund_tmp_order=array();
			$ato=array();
			foreach ($arr_ato as $v)
			{
				$ato=$v;
				$refund_paid=0;
				$refund_id=0;
				$left_share_post=0;
				//----------------------子订单信息验证-------------------
				foreach ($dict_sto[strval($v['ato_id']).'_'.strval($v['trade_id'])] as $sto)
				{
					$refund_paid+=$sto['share_amount'];
					$trade=$dict_trade[strval($sto['sto_id'])];
					if($sto['actual_num']==0)
					{
						$list[]=array('trade_no'=>'拆分订单：'.$trade['trade_no'],'result_info'=>'子订单：'.$v['oid'].',已取消');
						SE('continue');
					}
					if(!$is_refund_force&&!$this->getEditCheckInfo($trade, $list, $user_id))
					{
						SE('continue');
					}
					if($trade['trade_status']!=30&&$trade['trade_status']!=25&&$trade['trade_status']!=27)
					{//27-待抢单-暂时不做
						$list[]=array('trade_no'=>'拆分订单：'.$trade['trade_no'],'result_info'=>'包含子订单：'.$v['oid'].',但已审核');
						SE('continue');
					}
					if($v['platform_id']==0&&$sto['paid']==0&&$sto['share_amount']>0)
					{
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'子订单：'.$v['oid'].',未付款');
						SE('continue');
					}
					if($trade['warehouse_id']<=0)
					{
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'子订单：'.$v['oid'].',所在仓库为无');
						SE('continue');
					}
				}
				//---------------------------退款处理---------------------------------
				if($v['paid']>0)
				{
					//------------------------线上退款-----------------------------
					if($v['platform_id']>0)
					{
						$refund=$refund_db->getSalesRefund(
									'sr.refund_id,sro.refund_order_id',
									array('sr.process_status'=>array('neq',10),'sr.type'=>array('eq',1),'sro.platform_id'=>array('eq',0),'sro.oid'=>array('eq',$v['oid'])),
									'sr',
									'LEFT JOIN sales_refund_order sro ON sr.refund_id=sro.refund_id'
						);
						//------------查看退款单是否存在,如果存在则审核(同意退款)-----------------
						if($refund['refund_id']>0)
						{
							$refund_order_db->updateSalesRefundOrder(
									array('process_status'=>90),
									array('trade_order_id'=>array('eq',$refund['trade_order_id']))
							);
							$sql_error_info='refundOrder-get_refund_order_process_status';
							$res_process_status=$this->query('SELECT MIN(process_status) AS min_status FROM sales_refund_order WHERE refund_id='.intval($refund['refund_id']));
							if($res_process_status[0]['min_status']==30)
							{
								$refund_db->updateSalesRefund(
									array('process_status'=>90),
									array('refund_id'=>array('eq',$refund['refund_id']))
								);
								$refund_log[]=array(
										'refund_id'=>$refund['refund_id'],
										'type'=>2,
										'operator_id'=>$user_id,
										'remark'=>'快捷同意退款',
										'created'=>date('y-m-d H:i:s',time()),
								);
							}
						}else{
							//-------------不存在退换单，线上创建临时退款单--------------------
							//sales_tmp_refund_order platform_id => shop_id
							//$refund_tmp_order[]=array('platform_id'=>$v['platform_id'],'oid'=>$v['oid']);
							$refund_tmp_order[]=array('shop_id'=>$v['shop_id'],'oid'=>$v['oid']);
						}
					}else
					{
						//------------------------线下建退款单(添加退款单-->添加退货货品)----------------------
						$res_trade=$this->getSalesTrade(
								'trade_no,shop_id,pay_account,customer_id,buyer_nick,receiver_name,receiver_area,receiver_address,receiver_mobile,receiver_telno',
								array('trade_id'=>array('eq',$v['trade_id']))
						);
						if(empty($res_trade)){
							SE('无效订单！');
						}
						$refund=array(
								'refund_no'=>get_sys_no('refund',1),
								'platform_id'=>0,
								'src_no'=>'',
								'type'=>1,
								'process_status'=>90,
								'status'=>0,
								'shop_id'=>set_default_value($res_trade['shop_id'], 0),
								'pay_account'=>set_default_value($res_trade['pay_account'], ''),
								'goods_amount'=>set_default_value($refund_paid, 0),
								'refund_amount'=>set_default_value($refund_paid, 0),
								'actual_refund_amount'=>set_default_value($refund_paid, 0),
								'direct_refund_amount'=>set_default_value($refund_paid, 0),
								'tid'=>set_default_value($v['tid'], ''),
								'trade_id'=>$v['trade_id'],
								'trade_no'=>$res_trade['trade_no'],
								'customer_id'=>set_default_value($res_trade['customer_id'],0),
								'buyer_nick'=>set_default_value($res_trade['buyer_nick'],''),
								'receiver_name'=>set_default_value($res_trade['receiver_name'],''),
								'receiver_address'=>set_default_value($res_trade['receiver_area'].' '.$res_trade['receiver_address'],''),
								'return_mobile'=>set_default_value($res_trade['receiver_mobile'],''),
								'return_telno'=>set_default_value($res_trade['receiver_telno'],''),
								'refund_time'=>array('exp','NOW()'),
								'from_type'=>2,
								'operator_id'=>$user_id,
								'created'=>array('exp','NOW()'),
						);
						$refund_id=$refund_db->addSalesRefund($refund);
						$res_orders=$trade_order_db->getSalesTradeOrderList(
								'trade_id,rec_id,src_tid,actual_num,share_price,paid, share_amount,goods_id,spec_id,spec_no,goods_name,spec_name,suite_id,suite_no,suite_name,suite_num',
								array('trade_id'=>array('eq',$trade['trade_id']),'src_oid'=>array('eq',$v['oid']))
						);
						$refund_orders=array();
						foreach ($res_orders as $o)
						{
							$refund_orders[]=array(
									'refund_id'=>$refund_id,
									'platform_id'=>0,
									'shop_id'=>set_default_value($res_trade['shop_id'], 0),
									'oid'=>$v['oid'],
									'process_status'=>30,
									'trade_id'=>$v['trade_id'],
									'trade_order_id'=>$o['rec_id'],
									'tid'=>set_default_value($v['tid'], ''),
									'trade_no'=>$res_trade['trade_no'],
									'order_num'=>$o['actual_num'],
									'price'=>$o['share_price'],
									'refund_num'=>$o['actual_num'],
									'total_amount'=>$o['share_amount'],
									'goods_id'=>$o['goods_id'],
									'spec_id'=>$o['spec_id'],
									'spec_no'=>set_default_value($o['spec_no'],''),
									'goods_name'=>set_default_value($o['goods_name'],''),
									'spec_name'=>set_default_value($o['spec_name'],''),
									'suite_id'=>$o['suite_id'],
									'suite_no'=>set_default_value($o['suite_no'],''),
									'suite_name'=>set_default_value($o['suite_name'],''),
									'suite_num'=>$o['suite_num'],
									'tag'=>0,
									'created'=>array('exp','NOW()'),
							);
						}
						$refund_order_db->addSalesRefundOrder($refund_orders);
						$refund_log[]=array(
								'refund_id'=>$refund_id,
								'type'=>1,
								'operator_id'=>$user_id,
								'remark'=>'快捷同意退款',
								'created'=>date('y-m-d H:i:s',time()),
						);
					}
				}
				//----------------------------更新平台货品库存变化------------------------
				$arr_process_background=array();
				$share_post=0;
				$is_master=0;
				foreach ($dict_sto[strval($v['ato_id']).'_'.strval($v['trade_id'])] as $sto)
				{
					$trade=$dict_trade[strval($sto['sto_id'])];
					//----单品----
					$arr_process_background[]=array(
							'type'=>1,
							'object_id'=>$sto['spec_id'],
							'created'=>date('y-m-d H:i:s',time()),
					);
					//----组合装----
					$res_suit=$this->query(
								'SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto 
			  				 	WHERE sto.rec_id=%d AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id',
								array($sto['sto_id'])
					);
					$res_suit[0]['created']=date('y-m-d H:i:s',time());
					$arr_process_background[]=$res_suit[0];
					//----- 更新实发数量，回收库存------------
					$refund_db->returnStockSpec($trade['warehouse_id'],$sto['sto_id']);
					//------更新子订单--------------------
					$trade_order_db->updateSalesTradeOrder(
							array('actual_num'=>0,'stock_reserved'=>0,'refund_status'=>5,'remark'=>'退款'),
							array('rec_id'=>array('eq',$sto['sto_id']))
					);
					$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>15,
							'data'=>0,
							'message'=>'退款子订单:'.$v['oid'],
							'created'=>date('y-m-d H:i:s',time()),
					);
					$share_post+=$sto['share_post'];
					//$is_master=($sto['is_master']==0?$is_master:1);
					if ($is_master!=0)
					{
						$is_master=1;
					}
					$sql_error_info='refundOrder-I_DL_REFRESH_TRADE';
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade['trade_id'].",2,0)");
				}
				//------------------------重新分摊邮费-----------------
				if ($share_post>0||$is_master==1)
				{
					$left_share_post=$trade_order_db->reshareAmountByTid($v['shop_id'],$v['tid'],$is_master,$user_id,true);
				}
				//------------------------更新退款单数据-----------------
				if ($refund_id>0 && $left_share_post>0) 
				{
					$refund_db->updateSalesRefund(
						array('refund_amount'=>$refund_paid+$left_share_post,'actual_refund_amount'=>$refund_paid+$left_share_post,'direct_refund_amount'=>$refund_paid+$left_share_post,'post_amount'=>$left_share_post),
						array('refund_id'=>array('eq',$refund_id))
					);
				}
			}
			//---------------------子订单全部退款->取消订单------------------
			if(!empty($ato))
			{
				$sql_error_info='refundOrder-count_not_refund_num';
				$res_not_refund_num=$this->query('SELECT COUNT(1) AS num FROM sales_trade_order sto WHERE sto.trade_id='.intval($ato['trade_id']).' AND sto.actual_num>0 AND sto.gift_type=0');
				if ($res_not_refund_num[0]['num']==0)
				{//子订单全部退款->取消订单
					$this->updateSalesTrade(array('trade_status'=>5), array('trade_id'=>array('eq',$ato['trade_id'])));
					$res_gift_order=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id,platform_id,goods_name,spec_no',array('trade_id'=>array('eq',intval($ato['trade_id'])),'gift_type'=>array('gt',0)));
					$del_gift_ids='';
					foreach ($res_gift_order as $o)
					{
						if($o['platform_id']>0)
						{
							SE('在线订单不可删除货品');
						}else{
							$del_gift_ids.=$o['rec_id'].',';
						}
						$trade_log[]=array(
								'trade_id'=>$ato['trade_id'],
								'operator_id'=>$user_id,
								'type'=>23,
								'data'=>0,
								'message'=>'删除订单货品 货品名称：'.$o['goods_name'].' 商家编码：'.$o['spec_no'],
								'created'=>date('y-m-d H:i:s',time())
						);
					}
					if(strlen($del_gift_ids)>0){
//						$sql_error_info='refundOrder-stock_change_record';
//						$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'子订单退款，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=%d WHERE sto.stock_reserved>=2 AND sto.rec_id IN(".intval(substr($del_gift_ids,0,-1))."));",array($trade['warehouse_id']));
						$sql_error_info='refundOrder-update_gift_stock_spec';
						$this->execute('UPDATE stock_spec ss,sales_trade_order sto SET ss.unpay_num=ss.unpay_num-IF(stock_reserved=2,actual_num,0), ss.order_num=ss.order_num-IF(stock_reserved=3,actual_num,0), ss.sending_num=ss.sending_num-IF(stock_reserved=4,actual_num,0), ss.subscribe_num=ss.subscribe_num-IF(stock_reserved=5,actual_num,0) WHERE sto.rec_id IN('.substr($del_gift_ids,0,-1).') AND ss.spec_id=sto.spec_id AND  ss.warehouse_id=%d ',array($trade['warehouse_id']));
						$sql_error_info='refundOrder-delete_gift_order';
						$this->execute('DELETE FROM sales_trade_order WHERE rec_id IN('.substr($del_gift_ids,0,-1).')');
					}
					$success[]=array('id'=>$ato['trade_id']);//全额退款成功
				}
			}
			$sql_error_info='refundOrder-add_sales_tmp_refund_order';
			$sales_tmp_refund_order=substr(M('sales_tmp_refund_order')->fetchSql(true)->addAll($refund_tmp_order),6);
			if(!empty($sales_tmp_refund_order))
			{
				$this->execute('INSERT IGNORE '.$sales_tmp_refund_order);
			}
			D('SalesRefundLog')->addSalesRefundLog($refund_log);
			D('SalesTradeLog')->addTradeLog($trade_log);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			$msg=$e->getMessage();
			if($msg=='continue')
			{
				return false;
			}else{
				SE($msg);
			}
		}
		return true;
	}
	/**
	 * 退款--订单
	 * @param string|array $condition
	 * @param number $user_id
	 * @return multitype:
	 */
	public function refundTrade($condition,$trade_ids,$user_id)
	{
		$sql_error_info='';
		try {
			$condition['sto.actual_num']=array('gt',0);
			$sql_error_info='refundTrade-get_st_ato_sto_data';
			$res_data_arr=$this->alias('st')
							  ->field(
							  		'st.trade_id,st.trade_no,st.trade_status,st.refund_status,st.warehouse_id,st.freeze_reason,st.split_from_trade_id,
							  		 sto.rec_id AS sto_id,sto.actual_num,sto.paid AS sto_paid,sto.share_post,sto.share_amount,sto.is_master,sto.spec_id,
							  		 ato.rec_id AS ato_id,ato.platform_id,ato.shop_id,ato.tid,ato.oid,ato.paid AS ato_paid'
							  	)																								   //(ato.oid = sto.src_oid AND ato.platform_id = sto.platform_id)						
							  ->join('LEFT JOIN sales_trade_order sto ON st.trade_id=sto.trade_id LEFT JOIN api_trade_order ato ON ato.shop_id = sto.shop_id AND ato.oid = sto.src_oid')
							  ->where($condition)
							  //->group('ato.shop_id,ato.tid,ato.oid')
							  ->select();
			if(empty($res_data_arr)){
				SE('没有找到有效的订单，请刷新查看');
			}
			$arr_st_sto=array();//与原始单没法对应,是历史订单的退款处理--暂时没有历史订单(自动归档暂时未做)
			$arr_st_sto_ato=array();//正常退款处理
			$arr_tmp=array();
			foreach ($res_data_arr as $v)
			{
				if($v['trade_status']==5&&$v['refund_status']==3){
					SE('订单已全部退款，请刷新查看');
				}
				if (empty($v['ato_id']))
				{//与原始单没法对应,是历史订单的退款处理--暂时没有历史订单(自动归档暂时未做)
					$arr_st_sto['st'][strval($v['trade_id'])]=array(
							'trade_id'=>$v['trade_id'],
							'trade_no'=>$v['trade_no'],
							'trade_status'=>$v['trade_status'],
							'warehouse_id'=>$v['warehouse_id'],
							'freeze_reason'=>$v['freeze_reason'],
					);
					$arr_st_sto['sto'][strval($v['trade_id'])][]=array(
							'sto_id'=>$v['sto_id'],
							'actual_num'=>$v['actual_num'],
							'paid'=>$v['sto_paid'],
							'share_post'=>$v['share_post'],
							'share_amount'=>$v['share_amount'],
							'is_master'=>$v['is_master'],
					);
				}else{
					$arr_st_sto_ato['ato'][strval($v['trade_id'])][strval($v['ato_id'])]=array(
							'ato_id'=>$v['ato_id'],
							'trade_id'=>$v['trade_id'],
							'platform_id'=>$v['platform_id'],
							'shop_id'=>$v['shop_id'],
							'tid'=>$v['tid'],
							'oid'=>$v['oid'],
							'paid'=>$v['ato_paid'],
					);
					$arr_st_sto_ato['st'][strval($v['sto_id'])]=array(
							'trade_id'=>$v['trade_id'],
							'trade_no'=>$v['trade_no'],
							'trade_status'=>$v['trade_status'],
							'warehouse_id'=>$v['warehouse_id'],
							'freeze_reason'=>$v['freeze_reason'],
					);
					$arr_st_sto_ato['sto'][strval($v['ato_id']).'_'.strval($v['trade_id'])][]=array(
							'sto_id'=>$v['sto_id'],
							'actual_num'=>$v['actual_num'],
							'paid'=>$v['sto_paid'],
							'share_post'=>$v['share_post'],
							'share_amount'=>$v['share_amount'],
							'is_master'=>$v['is_master'],
							'spec_id'=>$v['spec_id'],
					);
					$arr_tmp['st_sto'][strval($v['trade_id'])][]=array(
							'sto_id'=>$v['sto_id'],
					);
					$arr_tmp['st'][strval($v['trade_id'])]=$v['trade_no']; 
				}
				
			} 
			foreach ($trade_ids as $id)
			{
				if (empty($arr_tmp['st_sto'][$id]))
				{
					$list[]=array('trade_no'=>$arr_tmp['st'][$id],'result_info'=>'订单无可退款货品');
					continue;
				}
			}
			$list=array();
			$success=array();
			foreach ($arr_st_sto_ato['ato'] as $k => $v)
			{
				$this->refundOrder($v,$arr_st_sto_ato['sto'],$arr_st_sto_ato['st'],$user_id,$list);
				$sql_error_info='refundOrder-count_not_refund_num';
				$res_not_refund_num=$this->query('SELECT COUNT(1) AS num FROM sales_trade_order sto WHERE sto.trade_id='.intval($k).' AND sto.actual_num>0 AND sto.gift_type=0');
				if ($res_not_refund_num[0]['num']==0)
				{//-----子订单全部退款---------------
					//$this->updateSalesTrade(array('trade_status'=>5), array('trade_id'=>array('eq',$arr_ato[0]['trade_id'])));
					$success[]=array('id'=>$k);//全额退款成功
				}
			}
			$trade_ids_str = implode(',',$trade_ids);
			D('Purchase/SortingWall')->pickGoodsAutoStockOut($trade_ids_str,'全额退款');
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		$result=array('success'=>$success,'fail'=>$list);
		$result['status']=(empty($list))?0:2;
		return $result;
	}
	/**
	 * 恢复--退款
	 * @param array $arr_ato
	 */
	public function restoreRefund($arr_ato,$user_id,$call_from,&$trade_log,&$refund_log,&$list=array())
	{
		$sql_error_info='';
		$err_info=array();
		try {
			if($arr_ato['status']>=60)
			{
				$arr_status=array('已签收','已完成','已退款','已关闭');
				if ($call_from=="restoreOrder") {
					SE('原始订单-'.$arr_ato['tid'].$arr_status[$arr_ato['status']/10-6]);
				}elseif ($call_from=="restoreTrade") {
					$err_info=array('trade_no'=>$arr_ato['trade_no'],'result_info'=>'原始单-'.$arr_ato['tid'].$arr_status[$arr_ato['status']/10-6]);
					return $err_info;
				}	
			}
			$trade_order_db=D('SalesTradeOrder');
			$sql_error_info='restoreRefund-get_trade_and_trade_order';
			$res_st_sto=$trade_order_db->alias('sto')
			 						   ->field('st.trade_id,st.trade_status,st.warehouse_id,st.trade_no,sto.src_tid,sto.actual_num,sto.gift_type')
			 						   ->join('LEFT JOIN sales_trade st on (st.trade_id=sto.trade_id)')
			 						   ->where(array('sto.platform_id'=>array('eq',$arr_ato['platform_id']),'sto.src_oid'=>array('eq',$arr_ato['oid']),'sto.actual_num'=>array('eq',0)))
			 						   ->group('st.trade_id,sto.platform_id,sto.src_oid')
			 						   ->select();
			$trade_log=array();
			foreach ($res_st_sto as $v)
			{
				if ($v['trade_status']!=30&&$v['trade_status']!=25&&$v['trade_status']!=5)
				{
					if ($call_from=="restoreOrder") {
						SE('订单:'.$v['trade_no'].'状态不正确');
					}elseif ($call_from=="restoreTrade") {
						$err_info=array('trade_no'=>$v['trade_no'],'result_info'=>'订单状态不正确');
						return $err_info;
					}		
				}
				if($v['trade_status']==5)
				{
					$this->updateSalesTrade(array('trade_status'=>30), array('trade_id'=>array('eq',$v['trade_id'])));
				}
				//-------------子订单信息---------------- 
				$sql_error_info='restoreRefund-update_trade_order';
				$this->execute("UPDATE sales_trade_order sto
								SET sto.actual_num=sto.num,sto.share_amount=IF(sto.share_amount=0,sto.share_amount2,sto.share_amount),
								sto.discount=sto.price*sto.num-sto.share_amount,sto.paid=IF(".$arr_ato['paid'].">0,IF(sto.paid=0,sto.paid+sto.share_amount,sto.paid),sto.paid),
								sto.weight=sto.num*(SELECT gs.weight FROM goods_spec gs WHERE gs.spec_id=sto.spec_id),
								sto.stock_reserved=0,sto.refund_status=1,sto.remark=''
								WHERE sto.trade_id=".$v['trade_id']." AND sto.platform_id=".$arr_ato['platform_id']." AND sto.src_oid='".$arr_ato['oid']."' AND sto.actual_num=0"
				);
				//-------------刷新库存-----------
				$sql_error_info='restoreRefund-I_RESERVE_STOCK';
				$this->execute('CALL I_RESERVE_STOCK('.$v['trade_id'].', IF('.$v['trade_status'].'=25,5,3), '.$v['warehouse_id'].', 0)');
				$trade_log[]=array(
						'trade_id'=>$v['trade_id'],
						'operator_id'=>$user_id,
						'type'=>13,
						'data'=>0,
						'message'=>'恢复子订单:'.$arr_ato['oid'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			//------------删除临时退款数据-----------
			$sql_error_info='restoreRefund-delete_tmp_refund_order';
			$this->execute("DELETE FROM sales_tmp_refund_order WHERE shop_id=".$arr_ato['shop_id']." AND oid='".$arr_ato['oid']."'");
			//-----------更新退换单信息-------------
			$refund_order_db=D('SalesRefundOrder');
			$res_refund_orders=$refund_order_db->getSalesRefundOrderList(
						'sro.refund_id',
						 array('sro.platform_id'=>array('eq',$arr_ato['platform_id']),'sro.oid'=>array('eq',$arr_ato['oid']),'sr.type'=>array('eq',1),'sr.process_status'=>array('neq',10)),
						'sro',
						'LEFT JOIN sales_refund sr ON sr.refund_id=sro.refund_id'
			);
			$refund_db=D('SalesRefund');
			foreach ($res_refund_orders as $refund_order) 
			{
				$refund_order_db->updateSalesRefundOrder(
						array('process_status'=>10),
						array('refund_id'=>array('eq',$refund_order['refund_id']),'platform_id'=>array('eq',$arr_ato['platform_id']),'oid'=>array('eq',$arr_ato['oid']))
				);
				$res_tmp_refund_order=$refund_order_db->getSalesRefundOrder('refund_order_id',array('refund_id'=>array('eq',$refund_order['refund_id']),'process_status'=>array('neq',10)));
				if(empty($res_tmp_refund_order))
				{
					$refund_db->updateSalesRefund(array('process_status'=>10,'status'=>1),array('refund_id'=>array('eq',$refund_order['refund_id'])));
					$refund_log[]=array('refund_id'=>$refund_order['refund_id'],
								'type'=>4,
								'operator_id'=>$user_id,
								'remark'=>'取消退款',
								'created'=>date('y-m-d H:i:s',time())
					);
				}
			}
			D('SalesRefundLog')->addSalesRefundLog($refund_log);
			D('SalesTradeLog')->addTradeLog($trade_log);
			/*if(!empty($res_refund_order['refund_id']))
			{
				$refund_order_db->updateSalesRefundOrder(
						array('process_status'=>10),
						array('refund_id'=>array('eq',$res_refund_order['refund_id']),'platform_id'=>array('eq',$arr_ato['platform_id']),'oid'=>array('eq',$arr_ato['oid']))
				);
				$res_tmp_refund_order=$refund_order_db->getSalesRefundOrder('refund_order_id',array('refund_id'=>array('eq',$res_refund_order['refund_id']),'process_status'=>array('neq',10)));
				if(empty($res_tmp_refund_order))
				{
					D('SalesRefund')->updateSalesRefund(array('process_status'=>10,'status'=>1),array('refund_id'=>array('eq',$res_refund_order['refund_id'])));
					$refund_log[]=array('refund_id'=>$res_refund_order['refund_id'],
								'type'=>4,
								'operator_id'=>$user_id,
								'remark'=>'取消退款',
								'created'=>date('y-m-d H:i:s',time())
					);
				}
			}*/
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	/**
	 * 恢复--子订单
	 * @param array|string $condition
	 * @param integer $user_id
	 */
	public function restoreOrder($condition,$user_id)
	{//----不能找到原始订单的，(暂时未做历史订单，后期再考虑)
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		try {
			$api_order_db=D('ApiTradeOrder');
			$res_ato=$api_order_db->getApiTradeOrderList(
						'ato.platform_id,ato.shop_id,ato.tid,ato.oid,ato.status,ato.refund_status,ax.paid',
						 $condition,
						'ato',								  //(ato.oid = sto.src_oid AND ato.platform_id = sto.platform_id)															
						'LEFT JOIN sales_trade_order sto ON  (ato.oid = sto.src_oid AND ato.shop_id = sto.shop_id ) LEFT JOIN api_trade ax ON (ax.platform_id=ato.platform_id AND ax.tid=ato.tid)'
			);
			$trade_order_db=D('SalesTradeOrder');
			$refund_log=array();
			$trade_log=array();
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$arr_ato=array('tid'=>'','shop_id'=>0); //临时存储
			foreach ($res_ato as $v)
			{
				if (!isset($v['platform_id']))
				{
					SE('订单不存在');
					//$list[]=array('trade_no'=>'子订单：'.$v['oid'],'result_info'=>'订单不存在');
					//continue;
				}
				if ($v['refund_status']>2)
				{
					SE('订单已完成退款,无法恢复');
					//$list[]=array('trade_no'=>'子订单：'.$v['oid'],'result_info'=>'订单已完成退款,无法恢复');
					//continue;
				}
				//------------------------回收退款-------------------
				/* if($this->restoreRefund($v, $list)==false)
				{
					continue;
				} */
				$this->restoreRefund($v,$user_id,'restoreOrder',$trade_log,$refund_log);
				//------------------------重新分摊邮费-----------------
				if (!empty($arr_ato['tid'])&&($arr_ato['tid']!=$v['tid']||$arr_ato['shop_id']!=$v['shop_id']))
				{
					$trade_order_db->reshareAmountByTid($v['shop_id'],$v['tid'],0,$user_id,true);
				}
				$arr_ato=$v;
			}
			if (!empty($arr_ato['tid']))
			{
				$trade_order_db->reshareAmountByTid($arr_ato['shop_id'],$arr_ato['tid'],0,$user_id,true);
			}
			//更新标记
			// 判断标记：如果原来的标记为自定义标记则不修改flag_id否则如果标记为空或内置标记则修改flag_id
			$this->execute('UPDATE sales_trade SET flag_id=IF( refund_status>0, 8,IF(flag_id>1000,flag_id,0)) WHERE trade_id='.$condition['sto.trade_id'][1]);
			// D('SalesRefundLog')->addSalesRefundLog($refund_log);
			// D('SalesTradeLog')->addTradeLog($trade_log);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-getRefundInfo'.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		/* $result=array('success'=>$success,'fail'=>$list);
		$result['status']=(empty($list))?0:2;
		return $result; */
	}
	/**
	 * 恢复--全额退款订单
	 * @param array|string $condition
	 * @param integer $user_id
	 */
	public function restoreTrade($condition,$user_id)
	{
		$list=array();
		$success=array();
		$trade_no=array();
		$is_rollback=false;
		try {
			$sql_error_info='';	
			$trade_db=D('SalesTrade');
			$sql_error_info='restoreTrade-get_api_trade_order';	
			$res_ato=$trade_db->alias('st')
							  ->field('st.trade_id,st.trade_no,st.refund_status as sales_refund_status,ato.platform_id,ato.shop_id,ato.tid,ato.oid,ato.status,ato.refund_status,ato.paid')																								   //(ato.oid = sto.src_oid AND ato.platform_id = sto.platform_id)						
							  ->join('LEFT JOIN sales_trade_order sto ON st.trade_id=sto.trade_id LEFT JOIN api_trade_order ato ON ato.shop_id = sto.shop_id AND ato.oid = sto.src_oid')
							  ->where($condition)
							  ->select();		
			$trade_order_db=D('SalesTradeOrder');
			$refund_log=array();
			$trade_log=array();
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$arr_ato=array('tid'=>'','shop_id'=>0); //临时存储
			foreach ($res_ato as $v)
			{
				if (!isset($v['platform_id']))
				{
					// E('订单不存在');
					$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'原始单不存在');
					continue;
				}
				if ($v['sales_refund_status']!=3) {
					$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'非全额退款订单');
					continue;
				}
				if ($v['refund_status']>2)
				{
					// E('订单已完成退款,无法恢复');
					$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'子订单'.$v['oid'].'已完成退款无法恢复,其余子订单正常恢复');
					continue;
				}
				$err_info=$this->restoreRefund($v,$user_id,'restoreTrade',$trade_log,$refund_log);
				if(!empty($err_info)){ 
					if (!in_array($err_info["trade_no"], $trade_no)) {
						$list[]=$err_info; 
						$trade_no[]=$err_info["trade_no"];
					}
				}
				// 判断原始子订单是否为申请退款单，是=>恢复为申请退款单
				if ($v['refund_status']==2) {
					// 订单更新为申请退款单
					$this->execute("UPDATE sales_trade st
								SET st.refund_status=1,st.flag_id=IF(st.flag_id>1000,st.flag_id,8) 
								WHERE st.trade_id=".$v['trade_id']);
					// 子订单更新为申请退款单
					$this->execute("UPDATE sales_trade_order sto
								SET sto.refund_status=2
								WHERE sto.trade_id=".$v['trade_id']." AND sto.platform_id=".$v['platform_id']." AND sto.src_oid='".$v['oid']."'");
				}

				//------------------------重新分摊邮费-----------------
				$trade_order_db->reshareAmountByTid($v['shop_id'],$v['tid'],0,$user_id,true);				
			}
			if (!empty($arr_ato['tid']))
			{
				$trade_order_db->reshareAmountByTid($arr_ato['shop_id'],$arr_ato['tid'],0,$user_id,true);
			}
			foreach ($res_ato as $v)
			{
				if (!in_array($v['trade_id'],$success)) {
					$success[]=array('id'=>$v['trade_id']);//恢复成功
				}				
			}
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-getRefundInfo'.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array('success'=>$success,'list'=>$list);
		$result['status']=(empty($list))?0:2;
		return $result; 

	}
	/**
	 * 换货--子订单
	 * @param integer $order_id
	 * @param array $orders
	 * @param integer $user_id
	 */
	public function exchangeOrder($order_id,$orders,$version_id,$user_id)
	{
		$result = array('status'=>1,'info'=>'');
		$sql_error_info = '';
		$list=array();
		$suite_id=array();
		try
		{
			$sto_db=D('SalesTradeOrder');
			$order = $sto_db->getSalesTradeOrderList(
				'trade_id,shop_id,platform_id,src_tid,src_oid,bind_oid,guarantee_mode,gift_type,invoice_type,suite_num,suite_amount,suite_discount,
				invoice_content,share_amount,discount,share_post,paid,tax_rate,is_master,actual_num,refund_status,
				is_print_suite,from_mask,flag,spec_id,suite_id,spec_no,spec_name,suite_name,goods_name,goods_no',
				array('rec_id'=>array('in',$order_id))
			);
// 			if (empty($order)) 
// 			{
// 				SE('无效子订单');
// 			}
// 			if ($order['refund_status']>1)
// 			{
// 				SE('不能替换退款货品');
// 			}
			$trade = $this->getSalesTrade('trade_no,trade_status,freeze_reason,checkouter_id,warehouse_id,delivery_term,version_id',array('trade_id'=>array('eq',$order[0]['trade_id'])));
			if (empty($order)){
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'无效子订单');
			}
			$src_tid=$order[0]['src_tid'];
			foreach ($order as $o){
				if($o['src_tid']!=$src_tid){
					SE('同一系统订单换货的多个货品，必须是同一原始单');
				}
				if ($o['refund_status']>2){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'不能替换退款货品');
				}
				if ($o['suite_id']!=0) {
					$suite_id[]=$o['suite_id'];
				}
			}
			if (empty($trade)) 
			{
// 				SE('订单不存在');
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单不存在');
			}
			if ($version_id!=$trade['version_id']) 
			{
				SE('订单被其他人修改，请打开重新编辑');
			}
			if ($trade['trade_status']!=30 && $trade['trade_status']!=25 && $trade['trade_status']!=27) 
			{
// 				SE('订单非待审核状态，请打开重新编辑');
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单非待审核状态，请打开重新编辑');
			}
			if ($trade['freeze_reason']>0) 
			{
// 				SE('订单已冻结');
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
			}
			if(empty($list)){
			$this->startTrans();
			$this->exchangeOrderNoTrans($order_id, $orders, $order, $trade, $suite_id, $user_id);
			$this->commit();
			$rows=$sto_db->getSalesTradeOrderList(
						"sto.rec_id AS id,sto.rec_id AS sto_id,sto.trade_id,sto.spec_id,sto.platform_id,sto.src_oid,sto.suite_id,
						 sto.num,sto.price,sto.actual_num,sto.order_price,sto.share_price, sto.share_amount,
						 sto.share_price,sto.discount,sto.share_post,sto.paid,sto.goods_name,sto.goods_id,
						 sto.goods_no,sto.spec_name, sto.spec_no,sto.spec_code,sto.gift_type,sto.suite_name,
						 IF(sto.suite_num,sto.suite_num,'') suite_num,sto.suite_no, sto.weight,ss.stock_num,
						 0 AS is_suite,sto.remark,IF(sto.gift_type,1,IF(sto.platform_id,2,3)) edit,sto.refund_status, 
						 gs.img_url,gg.spec_count",
						 array('sto.trade_id'=>array('eq',$order[0]['trade_id'])),
						'sto',
						"LEFT JOIN goods_spec gs on gs.spec_id = sto.spec_id LEFT JOIN stock_spec ss ON ss.warehouse_id=".intval($trade['warehouse_id'])." AND ss.spec_id=sto.spec_id LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id",
						'refund_status,id ASC'
			);
			$result['data'] = array('total'=>count($rows),'rows'=>$rows);
			$result['trade'] = $this->getSalesTrade('goods_amount,discount,receivable,version_id',array('trade_id'=>array('eq',$order[0]['trade_id'])));
		}
		}catch(\PDOException $e)
		{
			$this->rollback();
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e)
		{
			$this->rollback();
			SE($e->getMessage());
		}
		$result['status']=empty($list)?0:1;
		$result['info']=$list;
		return $result;
	}
	/**
	 * 换货--子订单--无事务
	 * @param integer $order_id
	 * @param array $orders
	 * @param integer $user_id
	 */
	public function exchangeOrderNoTrans($order_id, $orders, $order, $trade, $suite_id, $user_id){
		$sto_db=D('Trade/SalesTradeOrder');
		try{

			//--------------------回收库存------------------------
			$sql_error_info='exchangeOrder-stock_spec';
			foreach ($order_id as $o){
				$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'换货，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($trade['warehouse_id'])." WHERE sto.stock_reserved>=2 AND sto.rec_id =%d);",$o);
				$this->execute('INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
						 (SELECT '.intval($trade['warehouse_id']).',spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0),
						 IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW()
						 FROM sales_trade_order WHERE rec_id=%d ORDER BY spec_id)
						 ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
						 sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num)',
				$o);
			}
			//--------------------更新平台货品库存变化---------------
			$sql_error_info='exchangeOrder-sys_process_background';
			$total_order=array();
			$message='将货品：(';
			foreach ($order as $o){
				$message.=$o['spec_no'].' '.$o['goods_name'].' '.$o['spec_name'].' '.$o['actual_num'].'、';
				$total_order['share_amount']+=$o['share_amount'];
				$total_order['share_post']+=$o['share_post'];
				$total_order['paid']+=$o['paid'];
				if ($o['suite_id'] == 0)
				{
					M('sys_process_background')->add(array('type'=>1,'object_id'=>$o['spec_id']));
				}else
				{
					M('sys_process_background')->add(array('type'=>2,'object_id'=>$o['suite_id']));
				}
			}
			$message=substr($message,0,strlen($message)-3);
			$message.=')--更换为--';
			//--------------------删除原货品-----------------------
			$sql_error_info='exchangeOrder-delete-sales_trade_order';
			foreach ($order_id as $o){
				$this->execute('DELETE FROM sales_trade_order WHERE rec_id=%d',$o);
			}
// 			$this->execute('DELETE FROM sales_trade_order WHERE rec_id=%d',$order_id);
			// -------------------更新组合装情况------------------
			$suite=array(
			        'suite_id'=>0,
					'suite_no'=>'',
					'suite_name'=>'',
					'suite_num'=>0,
					'suite_amount'=>0,
					'suite_discount'=>0,
					'is_print_suite'=>0,
					);
			if (!empty($suite_id)) {
				$r=$sto_db->updateSalesTradeOrder($suite,array('trade_id'=>array('eq',$order[0]['trade_id']),'suite_id'=>array('in',$suite_id)));
			}
			//--------------------新增新货品-----------------------
			$total_price=0;
			$total_num=0;
			foreach ($orders as $o)
			{
				if($o['num']<=0)
				{
					continue;
				}
				$total_num+=floatval($o['num']);
				$total_price+=floatval($o['price']*$o['num']);
			}
			$left_amount=$total_order['share_amount'];
			$left_post=$total_order['share_post'];
			$left_paid=$total_order['paid'];
			$length=count($orders);
			$trade_orders=array();
			$count=1;
			$ratio=1;
			$share_amount=0;
			$share_post=0;
			$share_paid=0;
			$share_price=0;
// 			$message='将货品：'.$order['spec_no'].' '.$order['goods_name'].' '.$order['spec_name'].' '.$order['actual_num'].' --更换为-- ';
			foreach ($orders as $o) 
			{
				if($o['num']<=0)
				{
					++$count;
					continue;
				}
				if ($count == $length) 
				{
					$share_amount=$left_amount;
					$share_post=$left_post;
					$share_paid=$left_paid;
					$share_price=bcdiv($share_amount,$o['num'],4);
				}else
				{
					$ratio = $total_price<=0?bcdiv(1,$total_num,4):bcdiv($o['price']*$o['num'],$total_price,4);
					$share_amount=bcmul($total_order['share_amount'],$ratio,4);
					$share_post=bcmul($total_order['share_post'],$ratio,4);
					$share_paid=bcmul($total_order['paid'],$ratio,4);
					$share_price=bcdiv($share_amount,$o['num'],4);
					$left_amount-=$share_amount;
					$left_post-=$share_post;
					$left_paid-=$share_paid;
				}
				$trade_orders[]=array(
					//子订单共同数据
					'trade_id'=>$order[0]['trade_id'],
					'shop_id'=>$order[0]['shop_id'],
					'platform_id'=>$order[0]['platform_id'],
					'src_tid'=>set_default_value($order[0]['src_tid'],''),
					'src_oid'=>$order[0]['src_oid'],
					'bind_oid'=>set_default_value($order[0]['bind_oid'],''),
					'gift_type'=>$order[0]['gift_type'],
					'invoice_type'=>$order[0]['invoice_type'],
					'invoice_content'=>set_default_value($order[0]['invoice_content'],''),
					'is_master'=>$order[0]['is_master'],
					'from_mask'=>$order[0]['from_mask'],
					'flag'=>$order[0]['flag'],
					'suite_id'=>0,
					'suite_no'=>'',
					'suite_name'=>'',
					'suite_num'=>0,
					'suite_amount'=>0,
					'suite_discount'=>0,
					'is_print_suite'=>0,
					//分摊计算的
					'delivery_term'=>($share_paid>$share_amount+$share_post)?1:$trade['delivery_term'],
					'order_price'=>$share_price, 
					'share_price'=>$share_price,
					'discount'=>bcsub(bcmul($o['price'],$o['num'],4),$share_amount,4), 
					'share_amount'=>$share_amount, 
					'share_post'=>$share_post, 
					'paid'=>$share_paid, 
					//子订单单品数据
					'num'=>$o['num'],
					'actual_num'=>$o['num'],
					'price'=>$o['price'],
					'spec_id'=>$o['spec_id'],
					'spec_no'=>set_default_value($o['spec_no'],''),
					'spec_name'=>set_default_value($o['spec_name'],''),
					'spec_code'=>set_default_value($o['spec_code'],''),
					'goods_id'=>set_default_value($o['goods_id'],0),
					'goods_no'=>set_default_value($o['goods_no'],''),
					'goods_name'=>set_default_value($o['goods_name'],''),
					'goods_type'=>set_default_value($o['goods_type'],1),
					'weight'=>set_default_value($o['weight'],0)*$o['num'],
					'tax_rate'=>set_default_value($o['tax_rate'],0),
				);
				$message.='['.$o['spec_no'].' '.$o['goods_name'].' '.$o['spec_name'].' '.$o['num'].'] ';
				++$count;
			}
			$res_orders=$sto_db->getSalesTradeOrderList(
				'rec_id,share_amount,num,actual_num,share_post,paid,share_price,discount,weight,spec_id,suite_id',
				array ('trade_id'=>array('eq',$order[0]['trade_id']), 'shop_id'=>array('eq',$order[0]['shop_id']), 'src_oid'=>array('eq',$order[0]['src_oid']) ) 
			);
			foreach ($res_orders as $o) 
			{
				$res_orders[$o['spec_id'].'_'.$o['suite_id']]=$o;
			}
			//--------------------更新货品(添加的货品已存在)-----------------------
			$add_orders=array();
			foreach ($trade_orders as $o) 
			{
				if(isset($res_orders[$o['spec_id'].'_'.$o['suite_id']]))
				{
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['share_amount']+=$o['share_amount'];
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['share_post']+=$o['share_post'];
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['actual_num']+=$o['actual_num'];
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['num']+=$o['num'];
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['weight']+=$o['weight'];
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['share_price']=bcdiv($res_orders[$o['spec_id'].'_'.$o['suite_id']]['share_amount'],$res_orders[$o['spec_id'].'_'.$o['suite_id']]['actual_num'],4);
					$res_orders[$o['spec_id'].'_'.$o['suite_id']]['discount']=$o['price']*$res_orders[$o['spec_id'].'_'.$o['suite_id']]['actual_num']-$res_orders[$o['spec_id'].'_'.$o['suite_id']]['share_amount'];
					$sto_db->updateSalesTradeOrder($res_orders[$o['spec_id'].'_'.$o['suite_id']]);
					//处理库存占用
					$sql_error_info='exchangeOrder-stock_change_record';
					$this->execute(
						  'INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) 
						  (SELECT sto.trade_id,'.intval($o['spec_id']).',ss.warehouse_id,(sto.stock_reserved-1),'.intval($o['num']).',ss.unpay_num,ss.order_num,
						  ss.sending_num,ss.subscribe_num,\'换货，占用库存\'  FROM sales_trade_order sto 
					      LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id='.intval($trade['warehouse_id']).' 
					      WHERE sto.stock_reserved>=2 AND sto.trade_id = %d AND sto.spec_id = %d AND sto.shop_id=%d AND sto.src_oid=\'%s\' AND sto.suite_id= %d)',
			     		  array($order[0]['trade_id'],$o['spec_id'],$o['shop_id'],$o['src_oid'],$o['suite_id'])
			     		  );
					$sql_error_info='exchangeOrder-update_stock_spec';
					$this->execute(
						'INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
						(SELECT '.intval($trade['warehouse_id']).','.intval($o['spec_id']).',IF(stock_reserved=2,'.intval($o['num']).',0),IF(stock_reserved=3,'.intval($o['num']).',0),
						IF(stock_reserved=4,'.intval($o['num']).',0),IF(stock_reserved=5,'.intval($o['num']).',0),NOW()
						FROM sales_trade_order WHERE trade_id = %d AND spec_id = %d AND shop_id=%d AND src_oid=\'%s\' AND suite_id= %d)
						ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),
						sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num)',
						array($order[0]['trade_id'],$o['spec_id'],$o['shop_id'],$o['src_oid'],$o['suite_id'])
					);
					continue;
				}
				$add_orders[]=$o;
			}
			if(!empty($add_orders))
			{
				$sto_db->addSalesTradeOrder($add_orders);
			}
			//--------------------占用库存-----------------------
			$sql_error_info='exchangeOrder-I_RESERVE_STOCK';
			if(intval($trade['trade_status'])==55){//已审核
				$this->execute('CALL I_RESERVE_STOCK('.intval($order[0]['trade_id']).', 4 , '.intval($trade['warehouse_id']).', 0)');
			}else{
				$this->execute('CALL I_RESERVE_STOCK('.intval($order[0]['trade_id']).', IF('.intval($trade['trade_status']).'=30,3,5), '.intval($trade['warehouse_id']).', 0)');
			}
			//--------------------刷新订单-----------------------
			$sql_error_info='exchangeOrder-I_DL_REFRESH_TRADE';
			$res_cfg_val=get_config_value('open_package_strategy',0);
			$this->execute('CALL I_DL_REFRESH_TRADE('.intval($user_id).', '.intval($order[0]['trade_id']).',IF('.intval($res_cfg_val).',4,0), 0)');
			//--------------------添加日志-----------------------
			M('SalesTradeLog')->add(array('trade_id'=>$order[0]['trade_id'],'operator_id'=>$user_id,'type'=>19,'message'=>$message));

		}catch(\PDOException $e)
		{
			$this->rollback();
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e)
		{
			$this->rollback();
			SE($e->getMessage());
		}
	}

	/**
	 * 清除驳回--订单
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:multitype:string  multitype:string unknown
	 */
	public function clearRevertTrade($arr_ids_data,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$clear_flag=false;
		$flag_id=0;
		$flag=array();
		$flag_content='';
		try{
			$sql_error_info='clearRevertTrade-sales_trade_query';
			$res_trade_arr=$this->alias('st')
								->field('st.trade_id,st.trade_no,st.trade_status,st.freeze_reason,st.checkouter_id, st.revert_reason,cor.title')
						        ->join('LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st.revert_reason')
								->where(array('st.trade_id'=>array('in',$arr_ids_data)))
								->select();
			$arr_trade_log=array();
			$ids='';
			foreach ($res_trade_arr as $trade)
			{
				if($trade['revert_reason']==0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'不是驳回订单');
					continue;
				}
				if($trade['trade_status']!=30&&$trade['trade_status']!=25)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					continue;
				}
				if(!$this->getEditCheckInfo($trade,$list,$user_id))
				{
					continue;
				}
				if(empty($trade['title']))
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'驳回原因不存在');
					continue;
				}
				$arr_trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>35,
						'data'=>$trade['revert_reason'],
						'message'=>'订单清除驳回,其中清除的驳回原因是:'.$trade['title'],
				);
				$ids.=$trade['trade_id'].',';
				$success[]=array('id'=>$trade['trade_id']);
			}
			if (!empty($ids))
			{
				// 查找标记用于前端页面刷新使用
				$flag_id=D('Setting/Flag')->getFlagId('驳回订单');	
				$flag=$this->query('SELECT flag_id,flag_name,bg_color,font_color,font_name FROM cfg_flags WHERE flag_class=1 AND is_builtin=1 AND flag_id='.$flag_id);
				if (!empty($flag)) {
					$style = "display:block;float:left;border-radius:50px;width:20px;height:20px;line-height:20px;text-align:center;font-size:14px;";
					$flag_content="<span title='驳回订单' style='background-color:".$flag[0]['bg_color'].";color:".$flag[0]['font_color'].";font_family:".$flag[0]['font_name'].";".$style."'>驳</span>";
				}
				$is_rollback=true;
				$this->startTrans();
				$sql_error_info='clearRevertTrade-update_sales_trade';
				$res_trade_update=$this->execute("UPDATE sales_trade SET revert_reason = 0,flag_id=IF(flag_id>1000,flag_id,0),version_id = version_id +1 WHERE trade_id IN (".substr($ids,0,-1).")");
				$sql_error_info='clearRevertTrade-add_sales_trade_log';
				$res_trade_log=M('SalesTradeLog')->addAll($arr_trade_log);
				if($res_trade_update&&$res_trade_log)
				{
					$clear_flag=true;
				}
				$this->commit();
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		$result=array('success'=>$success,'fail'=>$list,'flag'=>$flag_content);
		$result['status']=(empty($list)&&$clear_flag)?0:2;
		return $result;
	}
	/**
	 * 冻结解冻--订单
	 * @param array $arr_ids_data
	 * @param integer $freeze_reason
	 * @param integer $user_id
	 * @return multitype:number multitype:multitype:string unknown   multitype:multitype:unknown
	 */
	public function freezeTrade($arr_ids_data,$freeze_reason,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$flag_id=0;
		$freeze_flag=false;
		$flag=array();
		$flag_content='';
		try{
			if($freeze_reason!=0)
			{
				$sql_error_info='freezeTrade-cfg_oper_reason_query';
				$res_title=M('cfg_oper_reason')->field('title')->where('reason_id='.$freeze_reason)->find();
				if(empty($res_title))
				{
					SE('订单选择的冻结原因不存在，请重新选择');
				}
			}
			$sql_error_info='freezeTrade-sales_trade_query';
			$res_trade_arr=$this->alias('st')
								->field('st.trade_id,st.trade_no,st.trade_status,st.freeze_reason,st.checkouter_id')
								->where(array('st.trade_id'=>array('in',$arr_ids_data)))
								->select();
			$arr_trade_log=array();
			$ids='';
			foreach ($res_trade_arr as $trade)
			{
				if($freeze_reason!=0&&$trade['freeze_reason']!=0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已被冻结');
					continue;
				}
				if($freeze_reason==0&&$trade['freeze_reason']==0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'非法冻结原因');
					continue;
				}
				if($trade['trade_status']>=55||$trade['trade_status']<=5)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					continue;
				}
				$arr_trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>$freeze_reason>0?28:29,
						'data'=>$freeze_reason,
						'message'=>$freeze_reason>0?'冻结订单,冻结原因:'.$res_title['title']:'解冻订单',
				);
				$ids.=$trade['trade_id'].',';
				$success[]=array('id'=>$trade['trade_id']);
			}
			if (!empty($ids))
			{
				$flag_id=D('Setting/Flag')->getFlagId('冻结');	
				$flag=$this->query('SELECT flag_id,flag_name,bg_color,font_color,font_name FROM cfg_flags WHERE flag_class=1 AND is_builtin=1 AND flag_id='.$flag_id);
				if (!empty($flag)) {
					$style = "display:block;float:left;border-radius:50px;width:20px;height:20px;line-height:20px;text-align:center;font-size:14px;";
					$flag_content="<span title='冻结' style='background-color:".$flag[0]['bg_color'].";color:".$flag[0]['font_color'].";font_family:".$flag[0]['font_name'].";".$style."'>冻</span>";
				}
				$is_rollback=true;
				$this->startTrans();
				$sql_error_info='freezeTrade-update_sales_trade';
				$res_trade_update=$this->execute("UPDATE sales_trade SET freeze_reason = ".$freeze_reason.",flag_id=IF(flag_id>1000,flag_id,".$flag_id.") WHERE trade_id IN (".substr($ids,0,-1).")");
				$sql_error_info='freezeTrade-add_sales_trade_log';
				$res_trade_log=M('sales_trade_log')->addAll($arr_trade_log);
				if($res_trade_update&&$res_trade_log)
				{
					$freeze_flag=true;
				}
				$this->commit();
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		$result=array('success'=>$success,'fail'=>$list,'flag_id'=>$flag_id,'flag'=>$flag_content);
		$result['status']=(empty($list)&&$freeze_flag)?0:2;
		return $result;
	}
	/**
	 * 取消--订单
	 * @param array $arr_ids_data
	 * @param integer $cancel_reason
	 * @param integer $user_id
	 */
	public function cancelTrade($arr_ids_data,$cancel_reason,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$cancel_flag=false;
		$flag_id=0;
		try{
			if($cancel_reason!=0)
			{ 
				$sql_error_info='cancelTrade-cfg_oper_reason_query';
				$res_title=M('cfg_oper_reason')->field('title')->where('reason_id='.$cancel_reason)->find();
				if(empty($res_title))
				{
					SE('选择的订单取消原因不存在，请重新选择');
				}
			}else {
				SE('请选择有效的取消原因');
			}
			$sql_error_info='cancelTrade-sales_trade_query';
			$res_trade_arr=$this->alias('st')
								->field('st.trade_id,st.trade_no,st.trade_status,st.freeze_reason,st.checkouter_id,st.warehouse_id,st.paid')
								->where(array('st.trade_id'=>array('in',$arr_ids_data)))
								->select();
			$arr_trade_log=array();
			$ids='';
			foreach ($res_trade_arr as $trade)
			{
				if($trade['trade_status']!=30&&$trade['trade_status']!=25)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					continue;
				}
				if($trade['paid']!=0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'已付款，如果要删除请按退款处理');
					continue;
				}
				if(!$this->getEditCheckInfo($trade,$list,$user_id))
				{
					continue;
				}
				$arr_trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>4,
						'data'=>$cancel_reason,
						'message'=>'关闭订单'.$trade['trade_no'].',关闭原因:'.$res_title['title'],
				);
				$ids.=$trade['trade_id'].',';
				$success[]=array('id'=>$trade['trade_id'],'warehouse_id'=>$trade['warehouse_id']);
			}
			if (!empty($ids))
			{
				$is_rollback=true;
				$this->startTrans();
				// 判断标记：如果原来的标记为自定义标记则不修改flag_id否则如果标记为空或内置标记则修改flag_id(这里应该判断被合并的所有子订单)
                $flag_id=D('Setting/Flag')->getFlagId('取消');							
				foreach ($success as $t)
				{
					$this->execute('CALL I_RESERVE_STOCK('.intval($t['id']).',0,'.intval($t['warehouse_id']).','.intval($t['warehouse_id']).')');
				}
				$sql_error_info='cancelTrade-update_sales_trade';
				$res_trade_update=$this->execute("UPDATE sales_trade SET trade_status = 5,cancel_reason = ".$cancel_reason.",flag_id=IF(flag_id>1000,flag_id,".$flag_id."),version_id = version_id +1 WHERE trade_id IN (".substr($ids,0,-1).")");
				$sql_error_info='cancelTrade-update_sales_trade_order';
				$this->execute("UPDATE sales_trade_order SET stock_reserved=0,actual_num=0 WHERE trade_id IN (".substr($ids,0,-1).")");
				$sql_error_info='cancelTrade-add_sales_trade_log';
				$res_trade_log=M('sales_trade_log')->addAll($arr_trade_log);
				if($res_trade_update&&$res_trade_log)
				{
					$cancel_flag=true;
				}
				$sql_error_info='cancelTrade-update_pick_goods_auto_stockout';
				D('Purchase/SortingWall')->pickGoodsAutoStockOut(substr($ids,0,-1),'取消');
				$this->commit();
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		$result=array('success'=>$success,'fail'=>$list,'flag_id'=>$flag_id);
		$result['status']=(empty($list)&&$cancel_flag)?0:2;//0全部成功，1异常错误，2部分成功
		return $result;
	}
	/**
	 * 清除异常--订单
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:multitype:string  multitype:string unknown
	 */
	public function clearBadTrade($arr_ids_data,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$clear_flag=false;
		try{
			$sql_error_info='clearBadTrade-sales_trade_query';
			$res_trade_arr=$this->alias('st')
								->field('st.trade_id,st.trade_no,st.trade_status,st.freeze_reason,st.checkouter_id, st.bad_reason, st.refund_status')
								->where(array('st.trade_id'=>array('in',$arr_ids_data)))
								->select();
			$res_order_arr=D('SalesTradeOrder')->getSalesTradeOrderList('trade_id AS id,refund_status',array('trade_id'=>array('in',$arr_ids_data),'refund_status'=>array('eq',2)));
			$res_order_arr=UtilTool::array2dict($res_order_arr,'id','');
			$arr_trade_log=array();
			$ids='';
			$refund_ids='';
			foreach ($res_trade_arr as $trade)
			{
				if($trade['bad_reason']==0&&$trade['refund_status']!=1&&($trade['refund_status']==2&&empty($res_order_arr[$trade['trade_id']])))
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'不是异常订单');
					continue;
				}
				if($trade['trade_status']!=30&&$trade['trade_status']!=25)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					continue;
				}
				if(!$this->getEditCheckInfo($trade,$list,$user_id))
				{
					continue;
				}
				$message='订单清除异常';
				if($trade['refund_status']==1||$trade['refund_status']==2){
					$message='订单清除退款';
					$refund_ids.=$trade['trade_id'].',';
				}
				$arr_trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>35,
						'data'=>$trade['bad_reason'],
						'message'=>$message,
				);
				$ids.=$trade['trade_id'].',';
				$success[]=array('id'=>$trade['trade_id']);
			}
			if (!empty($ids))
			{
				$is_rollback=true;
				$this->startTrans();
				//清除异常
				$sql_error_info='clearBadTrade-update_sales_trade';
				$res_trade_update=$this->execute("UPDATE sales_trade SET bad_reason = 0,version_id = version_id +1 WHERE trade_id IN (".substr($ids,0,-1).")");
				//清除退款状态
				if(strlen($refund_ids)>0){
					$sql_error_info='clearBadTrade-update_order_refund';
					$res_order_refund=$this->execute("UPDATE sales_trade_order SET refund_status=1 WHERE refund_status=2 AND trade_id IN (".substr($refund_ids,0,-1).")");
					$sql_error_info='clearBadTrade-update_trade_refund';
					$res_trade_refund=$this->execute("UPDATE sales_trade SET refund_status=IF(refund_status=1,0,refund_status) WHERE trade_id IN (".substr($refund_ids,0,-1).")");
				}
				//添加日志
				$sql_error_info='clearBadTrade-add_sales_trade_log';
				$res_trade_log=M('SalesTradeLog')->addAll($arr_trade_log);
				if($res_trade_update&&$res_trade_log)
				{
					$clear_flag=true;
				}
				$this->commit();
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		$result=array('success'=>$success,'fail'=>$list);
		$result['status']=(empty($list)&&$clear_flag)?0:2;
		return $result;
	}
	
	//删除订单
	public function deleteTrade($arr_ids_data,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		try{
			$sql_error_info='deleteTrade-salse_trade_query';
			$res_trade_arr=$this->alias('st')
								->field('st.trade_id,st.trade_no,st.platform_id,st.shop_id,st.trade_status,st.stockout_no,st.warehouse_id,split_from_trade_id')
								->where(array('st.trade_id'=>array('in',$arr_ids_data)))
								->select();
			$arr_trade_log=array();
			if(!empty($res_trade_arr))
			{
				$is_rollback=true;
				$this->startTrans();
				foreach ($res_trade_arr as $trade)
				{
					if($trade['trade_status']!=30)
					{
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
						continue;
					}
					if($trade['platform_id']!=0)
					{
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'线上订单不可删除');
						continue;
					}
					$sql_error_info='deleteTrade-sales_order_query';
					$res_order_arr=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id,trade_id,src_tid,src_oid,shop_id,actual_num,share_price,spec_id,suite_no',array('trade_id'=>array('eq',$trade['trade_id'])));
					$api_ids='';
					$spec_stock=array();//记录库存变化
					foreach ($res_order_arr as $v)
					{
						if($v['suite_no']!=''&&$trade['split_from_trade_id']!=0){
							$sql_error_info='deleteTrade-sales_order_split_query';
							$split_order=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id',array('shop_id'=>array('eq',$v['shop_id']), 'src_oid'=>array('eq',$v['src_oid']),'suite_no'=>array('eq',$v['suite_no']),'trade_id'=>array('neq',$v['trade_id'])));
							if(!empty($split_order)){
								$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单中存在被拆分的组合装，不可删除');
								continue 2;
							}
						}
						if($v['src_tid'])
						{
							$api_ids.=$v['src_tid'].',';
						}
						if($spec_stock[$v['spec_id']]){
							$spec_stock[$v['spec_id']]['num']+=$v['actual_num'];
						}else{
							$spec_stock[$v['spec_id']]['num']=$v['actual_num'];
							$spec_stock[$v['spec_id']]['spec_id']=$v['spec_id'];
						}
					}
					//删除出库单和出库单详情
					$sql_error_info='deleteTrade-delete_stockout_detail';
					$this->execute("DELETE FROM stockout_order_detail WHERE stockout_id = (SELECT so.stockout_id FROM stockout_order so WHERE stockout_no='".$trade['stockout_no']."')");
					$sql_error_info='deleteTrade-delete_stockout_order';
					$this->execute("DELETE FROM stockout_order WHERE stockout_no='".$trade['stockout_no']."'");
					//更新货品库存
					$sql_error_info='deleteTrade-stock_change_record';
					$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,2,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'删除订单，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($trade['warehouse_id'])." WHERE sto.trade_id=".$trade['trade_id'].");");
					$sql_error_info='deleteTrade-update_stock_spec';
					foreach ($spec_stock as $v){
						$this->execute('UPDATE stock_spec ss SET ss.order_num=ss.order_num-'.$v['num'].'  WHERE ss.spec_id='.$v['spec_id'].' AND ss.warehouse_id='.$trade['warehouse_id'] );
					}
					//删除订单和子订单
					$sql_error_info='deleteTrade-delete_sales_order';
					$this->execute('DELETE FROM sales_trade_order WHERE trade_id='.$trade['trade_id']);
					$sql_error_info='deleteTrade-delete_sales_trade';
					$this->execute('DELETE FROM sales_trade WHERE trade_id ='.$trade['trade_id']);
					//删除或者更新原始订单和原始子订单
					$sql_error_info='deleteTrade-api_order_query';
					$api_order_arr=D('ApiTradeOrder')->getApiTradeOrderList('oid,tid,num,price,discount,discount/num AS per_dis,share_amount,share_amount/num AS per_share,paid',
														array('shop_id'=>array('eq',$trade['shop_id']),'tid'=>array('in',$api_ids)));
					$api_order_arr=UtilTool::array2dict($api_order_arr,'oid','');
					$new_api_trade=array();//记录原始订单的更新
					foreach ($api_order_arr as $v)
					{
						if($new_api_trade[$v['tid']])
						{
							$new_api_trade[$v['tid']]['goods_count']+=$v['num'];
							$new_api_trade[$v['tid']]['order_count']++;
							$new_api_trade[$v['tid']]['discount']+=$v['discount'];
							$new_api_trade[$v['tid']]['goods_amount']+=$v['price']*$v['num'];
							$new_api_trade[$v['tid']]['receivable']+=$v['share_amount'];
						}else{
							$new_api_trade[$v['tid']]['tid']=$v['tid'];
							$new_api_trade[$v['tid']]['goods_count']=$v['num'];
							$new_api_trade[$v['tid']]['order_count']=1;
							$new_api_trade[$v['tid']]['discount']=$v['discount'];
							$new_api_trade[$v['tid']]['goods_amount']=$v['price']*$v['num'];
							$new_api_trade[$v['tid']]['receivable']=$v['share_amount'];
						}
					}
					$delete_num=0;
					foreach ($res_order_arr as $order)
					{
						if($api_order_arr[$order['src_oid']])
						{
							$new_api_order=array();
							if($new_num=$api_order_arr[$order['src_oid']]['num']-$order['actual_num'])
							{
								$new_api_order=array(//记录原始子订单的更新
										'num'			=>$new_num,
										'discount'		=>$api_order_arr[$order['src_oid']]['per_dis']*$new_num,
										'share_amount'	=>$api_order_arr[$order['src_oid']]['per_share']*$new_num,
										'total_amount'	=>$api_order_arr[$order['src_oid']]['per_share']*$new_num,
										'paid'			=>$api_order_arr[$order['src_oid']]['per_share']*$new_num,
								);
								$sql_error_info='deleteTrade-update_api_trade_order';
								D('api_trade_order')->updateApiTradeOrder($new_api_order,array('shop_id'=>array('eq',$order['shop_id']),'tid'=>array('eq',$order['src_tid']),'oid'=>array('eq',$order['src_oid'])));
							}else{
								$sql_error_info='deleteTrade-delete_api_trade_order';
								$this->execute("DELETE FROM api_trade_order WHERE shop_id=".$order['shop_id']." AND tid='".$order['src_tid']."' AND oid='".$order['src_oid']."'");
								$new_api_trade[$order['src_tid']]['order_count']--;
							}
							$new_api_trade[$order['src_tid']]['goods_count']-=$order['actual_num'];
							$new_api_trade[$order['src_tid']]['discount']-=$api_order_arr[$order['src_oid']]['per_dis']*$order['actual_num'];
							$new_api_trade[$order['src_tid']]['goods_amount']-=$api_order_arr[$order['src_oid']]['price']*$order['actual_num'];
							$new_api_trade[$order['src_tid']]['paid']=$new_api_trade[$order['src_tid']]['received']=$new_api_trade[$order['src_tid']]['dap_amount']=$new_api_trade[$order['src_tid']]['receivable']-=$api_order_arr[$order['src_oid']]['per_share']*$order['actual_num'];
						}
					}
					$delete_api_id='';
					foreach ($new_api_trade as $v)
					{
						if(!($v['goods_count']>0))
						{//该原始单下已没有货品，需要删除。
							$delete_api_id.="'".$v['tid']."',";
						}else{//该原始单下仍有货品，更新原始单。
							$sql_error_info='deleteTrade-update_api_trade';
							D('ApiTrade')->updateApiTrade($v,array('shop_id'=>array('eq',$trade['shop_id']),'tid'=>array('eq',$v['tid'])));
						}
					}
					if(strlen($delete_api_id)>1)
					{
						$delete_api_id = substr($delete_api_id,0,strlen($delete_api_id)-1);
						$sql_error_info='deleteTrade-delete_api_trade';
						$this->execute("DELETE FROM api_trade WHERE shop_id=".$trade['shop_id']." AND tid IN (".$delete_api_id.")");
					}
					//删除订单日志
					$sql_error_info='deleteTrade-delete_trade_log';
					$this->execute('DELETE FROM sales_trade_log WHERE trade_id='.$trade['trade_id']);
					$success[]=array('id'=>$trade['trade_id']);
					//添加删除订单的日志
					$arr_trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>161,
							'message'=>'删除订单'.$trade['trade_no'],
					);
				}
				M('SalesTradeLog')->addAll($arr_trade_log);
				$sql_error_info='deleteTrade-update_pick_goods_auto_stockout';
				$arr_ids_data = implode(',',$arr_ids_data);
				D('Purchase/SortingWall')->pickGoodsAutoStockOut($arr_ids_data,'删除');
				$this->commit();
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array(
				'success'=>$success,
				'fail'=>$list,
				'status'=>empty($list)?0:2,
				'del'=>empty($success)?false:true,
		);
		return $result;
	}
	
	public function getTradeOrderInfo($fields,$conditions){
		try{
			$sql = $this->alias('st')->fetchSql(true)->field($fields)->where($conditions)->select();
			$res = $this->query($sql);
			return $res;
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-getSql-'.$sql.'-getTradeOrderInfo-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	/**
	 * 批量修改订单
	 * @param trade_ids 
	 * @param new_id 
	 * @param type   0:物流，1:仓库，2:客服备注
	 * @return 
	 */
	public function changeTrades($trade_ids,$new_id,$type,$version,$user_id){
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$res_dict_arr=array();
		$success=array();
		try{
			$sql_error_info='getSalesTradeList';
			$res_trade=$this->getSalesTradeList('trade_id,trade_no,trade_status,freeze_reason,version_id,logistics_id AS old_logistics,warehouse_id AS old_warehouse,cs_remark as old_remark',array('trade_id'=>array('in',$trade_ids)));
			if(empty(($res_trade))){
				SE('订单不存在');
			}
			switch ($type){
				case 0:
					$sql_error_info='getLogisticsInfo';
					$new_data=D('Setting/Logistics')->getLogisticsInfo($new_id);
					foreach ($res_trade as $k=>$v){
						$res_trade[$k]['logistics_id']=$new_id;
					}
					$res_dict_arr=M('cfg_logistics')->field('logistics_id AS id,logistics_name AS name')->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					break;
				case 1:
					$sql_error_info='getWarehouseList';
					$new_data=D('Setting/Warehouse')->getWarehouseList('warehouse_id,name',array('warehouse_id'=>array('eq',$new_id)));
					foreach ($res_trade as $k=>$v){
						$res_trade[$k]['warehouse_id']=$new_id;
					}
					$res_dict_arr=M('cfg_warehouse')->field('warehouse_id AS id,name')->select();
					$res_dict_arr=UtilTool::array2dict($res_dict_arr);
					break;
				case 2:
					$sql_error_info='getRemarkList';
					$new_data = true;
					foreach ($res_trade as $k => $v) {
						$res_trade[$k]['cs_remark'] = $new_id;
					}
					break;
			}
			$error_message=array('物流','仓库','客服备注');
			if(empty($new_data)){
				SE('所选'.$error_message[$type].'不存在');
			}
			$is_rollback=true;
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$trade_log=array();
			foreach ($res_trade as $trade){
				$flag_changes_csremark = false;
				if ($trade['version_id']!=$version[$trade['trade_id']]) {
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'修改失败：订单已被他人修改,请刷新后重试');
					continue;
				}
				if($trade['trade_status']!=30&&$trade['trade_status']!=25){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'修改失败：订单状态不正确');
					continue;
				}
				if($trade['freeze_reason']>0){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'修改失败：订单已冻结');
					continue;
				}
				$sql_error_info='sales_trade-update';
				$trade_status=$trade['trade_status'];
				unset($trade['trade_status'],$trade['freeze_reason'],$trade['trade_no']);
				$this->updateSalesTrade($trade);
				if($type==2){
					$flag_changes_csremark=true;
					$this->execute('UPDATE sales_trade SET cs_remark_change_count=cs_remark_change_count|2 WHERE trade_id = '.intval($trade['trade_id']));
					$trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>43,
						'data'=>0,
						'message'=>'修改客服备注：从 '.$trade['old_remark'].' 到 '.$trade['cs_remark'],
						'created'=>date('y-m-d H:i:s',time())
					);
				}else{
					$old_id=$type==0?$trade['old_logistics']:$trade['old_warehouse'];
					$trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>$type==0?20:34,
						'data'=>0,
						'message'=>'修改'.$error_message[$type].'：从 '.$res_dict_arr[$old_id].' 到 '.$res_dict_arr[$new_id],
						'created'=>date('y-m-d H:i:s',time())
					);
				}
				if($type==1&&($trade['old_warehouse']!=$trade['warehouse_id'])&&$trade['warehouse_id']!=0)
				{//刷新库存
					$sql_error_info='editTrade-I_RESERVE_STOCK';
					$this->execute('CALL I_RESERVE_STOCK('.$trade['trade_id'].',IF('.$trade_status.'=30,3,5),'.intval($trade['warehouse_id']).','.$trade['old_warehouse'].')');
				}
				if($trade['old_logistics']!=$trade['logistics_id']||$trade['old_warehouse']!=$trade['warehouse_id']||$flag_changes_csremark)
				{//刷新订单
					$sql_error_info='editTrade-I_DL_REFRESH_TRADE';
					$this->execute('CALL I_DL_REFRESH_TRADE('.$user_id.', '.$trade['trade_id'].', 2, 0)');
				}
				$success[]=array('id'=>$trade['trade_id']);
			}
			D('SalesTradeLog')->addTradeLog($trade_log);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array(
				'change'=>empty($success)?false:true,
				'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
				'fail'=>$list,//失败提示信息
				'success'=>$success,//成功的数据
				'new_name'=>$res_dict_arr[$new_id],
		);
		if($type==2){$result['new_name']=$new_id;}
		return $result;
	}
	//无需系统处理订单
	public function removeTrade($trade_ids,$user_id){
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$res_dict_arr=array();
		try{
			$sql_error_info='getSalesTradeList';
			$res_trade=$this->getSalesTradeList('trade_id,trade_no,trade_status,freeze_reason,platform_id,warehouse_id',array('trade_id'=>array('in',$trade_ids)));
			if(empty(($res_trade))){
				SE('订单不存在');
			}
			$is_rollback=true;
			$this->startTrans();
			$trade_log=array();
			foreach ($res_trade as $trade){
				if($trade['trade_status']!=30&&$trade['trade_status']!=25){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					return false;
				}
				if($trade['freeze_reason']>0){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
					return false;
				}
				$sql_error_info='sales_trade-update';
				$trade['trade_status']=115;
				$this->updateSalesTrade($trade);
				$trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>166,
						'data'=>0,
						'message'=>'订单无需处理，清除。',
						'created'=>date('y-m-d H:i:s',time())
				);
				$arr_orders=D('SalesTradeOrder')->getSalesTradeOrderList('spec_id,actual_num',array('trade_id'=>array('eq',$trade['trade_id'])));
				$sql_error_info='stock_change_record-update';
				$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,2,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'无需处理订单，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($trade['warehouse_id'])." WHERE sto.trade_id=".$trade['trade_id'].");");
				$sql_error_info='stock_spec-update';
				foreach ($arr_orders as $v){
					$this->execute('UPDATE stock_spec SET order_num=order_num-'.$v['actual_num'].' WHERE spec_id='.$v['spec_id'].' AND warehouse_id='.$trade['warehouse_id']);
				}
				$success[]=array('id'=>$trade['trade_id']);
			}
			$sql_error_info='sales_trade_log-insert';
			D('SalesTradeLog')->addTradeLog($trade_log);
			$sql_error_info='pick_goods_auto_stockout-update';
			$trade_ids = implode(',',$trade_ids);
			D('Purchase/SortingWall')->pickGoodsAutoStockOut($trade_ids,'清除');
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array(
				'remove'=>empty($success)?false:true,
				'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
				'fail'=>$list,//失败提示信息
				'success'=>$success,//成功的数据
		);
		return $result;
	}
	
	//恢复订单
	public function recoverTrade($trade_ids,$user_id){
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		$res_dict_arr=array();
		try{
			$sql_error_info='getSalesTradeList';
			$res_trade=$this->getSalesTradeList('trade_id,trade_no,trade_status,warehouse_id',array('trade_id'=>array('in',$trade_ids)));
			if(empty(($res_trade))){
				SE('订单不存在');
			}
			$is_rollback=true;
			$this->startTrans();
			$trade_log=array();
			foreach ($res_trade as $trade){
				if($trade['trade_status']!=115){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					return false;
				}
				$sql_error_info='sales_trade-update';
				$trade['trade_status']=30;
				$this->updateSalesTrade($trade);
				$trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>167,
						'data'=>0,
						'message'=>'订单恢复。',
						'created'=>date('y-m-d H:i:s',time())
				);
				$arr_orders=D('SalesTradeOrder')->getSalesTradeOrderList('spec_id,actual_num',array('trade_id'=>array('eq',$trade['trade_id'])));
				//占用库存
				$sql_error_info='stock_change_record-update';
				$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,2,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'恢复订单，占用库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($trade['warehouse_id'])." WHERE sto.trade_id=".$trade['trade_id'].");");
				$sql_error_info='stock_spce-update';
				foreach ($arr_orders as $v){
					$this->execute('UPDATE stock_spec SET order_num=order_num+'.$v['actual_num'].' WHERE spec_id='.$v['spec_id'].' AND warehouse_id='.$trade['warehouse_id']);
				}
				$success[]=array('id'=>$trade['trade_id']);
			}
			$sql_error_info='sales_trade_log-insert';
			D('SalesTradeLog')->addTradeLog($trade_log);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array(
				'recover'=>empty($success)?false:true,
				'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
				'fail'=>$list,//失败提示信息
				'success'=>$success,//成功的数据
		);
		return $result;
	}
}