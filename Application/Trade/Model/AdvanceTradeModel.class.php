<?php
namespace Trade\Model;
use Platform\Common\ManagerFactory;
use Think\Exception\BusinessLogicException;
class AdvanceTradeModel extends TradeModel
{
	public function turnCheck($arr_ids,$turn_type,$user_id)
	{
		$list=array();
		$success=array();
		$sql_error_info='';
		$sql_inner_join=$this->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('in',$arr_ids)))->select();
		try{
			$sql="SELECT st.trade_id,st.trade_no,st.shop_id,st.warehouse_id,st.warehouse_type,st.trade_status,st.trade_from,
				  st.trade_type,st.freeze_reason,st.refund_status, st.unmerge_mask,st.goods_type_count,st.customer_id,st.buyer_nick,
				  st.receiver_name,st.receiver_area,st.receiver_address, st.receiver_mobile,st.receiver_telno,st.logistics_id,
				  st.goods_amount,st.discount,st.receivable,st.discount_change,st.invoice_type, st.checkouter_id,st.post_amount,
				  st.delivery_term,st.buyer_nick,st.paid,st.revert_reason, st.bad_reason,split_from_trade_id,is_sealed,src_tids,
				  cl.is_support_cod FROM sales_trade st INNER JOIN (".$sql_inner_join.") st2 ON st2.trade_id=st.trade_id
				  LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id";
			$trades=$this->query($sql);
			if (empty($trades))
			{
				SE('未找到符合条件的订单');
			}
			$arr_cfg_key=array(
				'order_preorder_lack_stock',//库存不足转预订单
			);
			$arr_cfg_def_val=array(0);
			$res_cfg_val=get_config_value($arr_cfg_key,$arr_cfg_def_val);
			$this->startTrans();
			$trade_log=array();
			$trade_db=D('SalesTrade');
			foreach ($trades as $trade)
			{
				//订单相关信息校验
				if(!$this->checkSalesTrade($trade,$turn_type,$list))
				{
					continue;
				}
				//子订单相关信息校验
				if(!$this->checkSalesOrder($trade,$list,$turn_type,$res_cfg_val))
				{
					continue;
				}
				//转入审核
				$turnCheck=$trade_db->execute('UPDATE sales_trade SET trade_status = 30 WHERE trade_id = '.$trade['trade_id']);
				if($turnCheck)
				{
					$success[]=array('id'=>$trade['trade_id']);
				}
				//库存信息变化
				$this->execute("CALL I_RESERVE_STOCK(".$trade['trade_id'].",3,".$trade['warehouse_id'].",".$trade['warehouse_id'].")");
				//添加日志信息
				$turn_info=array('转入审核','强制转入审核','type'=>array(33,33));
				$trade_log[]=array(
						'trade_id'=>$trade['trade_id'],
						'operator_id'=>$user_id,
						'type'=>$turn_info['type'][$turn_type],
						'data'=>$turn_type,
						'message'=>$turn_info[$turn_type].':'.$trade['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
				);
			}
			D('SalesTradeLog')->addTradeLog($trade_log);
			$this->commit();
		}catch (\PDOException $e)
		{
			$this->rollback();
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			$this->rollback();
			SE($e->getMessage());
		}catch (\Exception $e)
		{
			$this->rollback();
			SE($e->getMessage());
		}
		$result=array(
				'turn'=>empty($success)?false:true,
				'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
				'fail'=>$list,//失败提示信息
				'success'=>$success,//成功的数据
		);
		return $result;
	}
	//订单检验
	private function checkSalesTrade($trade,$type,&$list)
	{
		$check=true;
		try
		{
			if($trade['trade_id']<=0)
			{
				$list[]=array('trade_no'=>'','result_info'=>'转入失败：订单不存在');
				return false;
			}
			if($trade['trade_status']!=25)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'转入失败：订单状态不正确');
				return false;
			}
			if($type!=1){
				if($trade['freeze_reason']!=0)
				{
					$sql_error_info='cfg_oper_reason_query_error-freeze-'.$trade['trade_no'];
					$reason=M('cfg_oper_reason')->field('title')->where(array('reason_id'=>array('eq',$trade['freeze_reason'])))->find();
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'转入失败：订单冻结-'.$reason['title']);
					return false;
				}
			}
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $check;
	}
	//子订单检验
	private function checkSalesOrder($trade,&$list,$type=0,$config)
	{
		$check=true;
		try
		{
			$sql_error_info='sales_trade_order_query_error-query_detail-'.$trade['trade_no'];
			$count_order=$this->query('SELECT COUNT(1) AS orders_num FROM sales_trade_order WHERE trade_id=%d AND actual_num>0',array($trade['trade_id']));
			$orders=$this->query(
					"SELECT sto.spec_id,sto.gift_type,sto.num,sto.price,sto.actual_num,
					 sto.order_price,sto.share_price,sto.discount, sto.share_amount,sto.share_post,sto.goods_name,
					 sto.spec_no,sto.spec_name,IFNULL(ss.stock_num,0) stock_num,IFNULL(ss.purchase_arrive_num,0) purchase_arrive_num, IFNULL(ss.sending_num,0) sending_num,
					 IFNULL(ss.lock_num,0) lock_num,gs.lowest_price,IFNULL(ss.cost_price,0) cost_price,gs.is_allow_lower_cost,gs.is_allow_neg_stock,IFNULL(ss.order_num,0) order_num,IFNULL(ss.subscribe_num,0) subscribe_num
					 FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id  AND ss.warehouse_id = %d
					 LEFT JOIN  goods_spec gs ON gs.spec_id = sto.spec_id WHERE sto.trade_id = %d AND sto.actual_num>0",
					array($trade['warehouse_id'],$trade['trade_id'])
					);
			if(empty($orders))
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'转入失败：订单无货品');
				return false;
			}
			foreach ($orders as $order)
			{
				$order['orders_num']=intval($count_order[0]['orders_num']);
				//-----------库存判断-强制转入不检查库存------------
				if($type!=1)
				{
					$actual_num=$order['actual_num'];
					if($order['orders_num']>$trade['goods_type_count'])
					{
						$sql_error_info='sales_trade_order_query-getSUM(actual_num)-'.$trade['trade_no'];
						$tmp_order=$this->query("SELECT SUM(actual_num) AS actual_sum FROM sales_trade_order WHERE trade_id = ".$trade['trade_id']." AND spec_id = ".$order['spec_id']." AND actual_num >0");
						$actual_num=empty($tmp_order)?$actual_num:$tmp_order[0]['actual_sum'];
						
					}
					if($config['order_preorder_lack_stock']==0){
						$order['stock_num']=$order['stock_num']-$order['sending_num'];//减去待发货量
					}else if($config['order_preorder_lack_stock']==1){
						$order['stock_num']=$order['stock_num']-$order['sending_num']-$order['order_num'];//减待发货减待审核
					}else{
						$order['stock_num']=$order['stock_num']-$order['sending_num']-$order['order_num']-$order['subscribe_num'];
						//减待发货减待审核减预定量
					}
					//$order['stock_num']=$order['stock_num']-$order['sending_num']-$order['lock_num'];
					//$order['stock_num']=$order['stock_num']-$order['sending_num'];//减去待发货量
					/* if($config['order_check_stock_add_purchase']==1)
					 {//加上采购到货量
					 $order['stock_num']+=$order['purchase_arrive_num'];
					 }
					 if($config['order_check_stock_sub_to_transfer'])
					 {//计算库存不足扣减待调拨量
					 $trade['stock_num']-=$order['to_transfer_num'];
					 } */
					if($actual_num>$order['stock_num'])
					{
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'库存不足:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name']);
						$check=false;
					}
				}
			}
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $check;
	}
}