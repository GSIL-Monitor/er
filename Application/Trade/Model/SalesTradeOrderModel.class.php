<?php
namespace Trade\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;
class SalesTradeOrderModel extends Model{
	protected $tableName = 'sales_trade_order';
	protected $pk        = 'rec_id';
	
	/**
	 *基本操作方法集 
	 */
	public function addSalesTradeOrder($data)
	{
		try {
			if (empty($data[0])) {
				$res = $this->add($data);
			}else
			{
				$res = $this->addAll($data);
			}
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-addSalesTradeOrder-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function updateSalesTradeOrder($data,$where)
	{
		try {
			$res = $this->where($where)->save($data);
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-updateSalesTradeOrder-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function getSalesTradeOrderList($fields,$where=array(),$alias='',$join=array(),$order='')
	{
		try {
			$res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getSalesTradeOrderList-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function getSalesTradeOrder($fields,$where=array(),$alias='')
	{
		try {
			$res = $this->alias($alias)->field($fields)->where($where)->find();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getSalesTradeOrder-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	/**
	 * 功能方法集（基本被调用）
	 */
	public function reshareAmountByTid($shop_id,$tid,$is_master,$user_id,$is_refresh_trade=false)
	{//刷新邮费分摊-->tid
		$left_share_post=0;
		$sql_error_info='';
		try {
			$sql_error_info='refreshAmountByTid-sales_trade_order_query';
			$res_orders=$this->field('trade_id,rec_id,share_amount,share_post,paid')
							 ->where(array('src_tid'=>array('eq',$tid),'shop_id'=>array('eq',$shop_id),'actual_num'=>array('gt',0),'gift_type'=>array('eq',0)))
							 ->order('trade_id asc')
							 ->select();
			$sql_error_info='refreshAmountByTid-api_trade_query';
			$res_api_trade=M('api_trade')->field('post_amount,paid')
			     		  				 ->where(array('shop_id'=>array('eq',$shop_id),'tid'=>array('eq',$tid)))
			     		  				 ->find();
			if(empty($res_api_trade))
			{
				SE('原始单'.$tid.'不存在');
			}
			if($res_api_trade['post_amount']==0&&(!$is_refresh_trade))
			{
				return $left_share_post;
			}
			$order_count=count($res_orders);
			if($order_count==0)
			{
				$left_share_post+=$res_api_trade['post_amount'];
				return $left_share_post;
			}
			$total_share_amount=0;
			$total_share_post=0;
			foreach($res_orders as $o)
			{
				$total_share_amount+=$o['share_amount'];
				$total_share_post+=$o['share_post'];
			}
			$left_share_post=$res_api_trade['post_amount'];
			$order=array();
			for ($i=0;$i<$order_count;$i++)
			{
				//---分摊邮费=分摊金额/总价*总邮费  ---总价为0时 分摊邮费=总邮费/货品数量
				$is_master=0;
				$order['rec_id']=$res_orders[$i]['rec_id'];
				if($i+1==$order_count)
				{
					$post_amount=$left_share_post;
					$order['paid']=($res_api_trade['paid']>0?$res_orders[$i]['paid']+$post_amount-$res_orders[$i]['share_post']:$res_orders[$i]['paid']);
					$order['share_post']=$post_amount;
					unset($order['is_master']);
				}else{
					$post_amount=($total_share_amount>0?($res_orders[$i]['share_amount']*$res_api_trade['post_amount']/$total_share_amount):($total_share_post/$order_count));
					$order['paid']=($res_api_trade['paid']>0?$res_orders[$i]['paid']+$post_amount-$res_orders[$i]['share_post']:$res_orders[$i]['paid']);
					$order['share_post']=$post_amount;
					$order['is_master']=array('exp','GREATEST('.$is_master.',is_master)');
				}
				$left_share_post-=$post_amount;
				$sql_error_info='refreshAmountByTid-sales_trade_order_update';
				$this->save($order);
				if($is_refresh_trade)
				{
					$sql_error_info='refreshAmountByTid-I_DL_REFRESH_TRADE';
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",'".$res_orders[$i]['trade_id']."',0,0)");
				}
			}
			
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $left_share_post;
	}
	/**
	 * 获取订单相同货品
	 */
	public function getCommonOrders($ids){
		$trade_num=count($ids);
		$sql_error_info='';
		$common_orders=array();
		try{
			$sql_error_info='getCommonOrders-get_sales_trade_order';
			$res_orders_arr=$this->getSalesTradeOrderList(
					"sto.rec_id AS id,sto.trade_id,sto.src_oid,sto.spec_no,sto.goods_name,sto.api_goods_name,sto.api_spec_name,sto.spec_name,
					sto.spec_id,sto.num,sto.actual_num,sto.price,sto.share_price,sto.order_price,sto.discount, sto.share_post,sto.share_amount,
					sto.remark,sto.weight,IFNULL(ss.stock_num - ss.sending_num,0) AS available_stock,sto.actual_num AS left_num,0 AS split_num,
					sto.weight/sto.actual_num as spec_weight,sto.share_post/sto.actual_num as spec_post,sto.discount/sto.actual_num as spec_discount,sto.share_amount/sto.actual_num as spec_amount",
					array('sto.trade_id'=>array('in',$ids),'sto.actual_num'=>array('gt',0)),
					'sto',
					"LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id LEFT JOIN stock_spec ss  ON ss.spec_id=sto.spec_id  AND  ss.warehouse_id=st.warehouse_id ",
					"sto.trade_id,sto.actual_num desc"
					);
			foreach($res_orders_arr as $r){
				if($common_orders[$r['spec_id']]){
					if($r['trade_id']!=$common_orders[$r['spec_id']]['trade_id']){
						//计算重复货品
						$common_orders[$r['spec_id']]['left_num']=min(array($common_orders[$r['spec_id']]['left_num'],$r['left_num']));
						$common_orders[$r['spec_id']]['trade_id']=$r['trade_id'];
						$common_orders[$r['spec_id']]['trade_num']++;
						$common_orders[$r['spec_id']]['num']=$common_orders[$r['spec_id']]['left_num'];
						//记录每条订单中该重复货品的子订单号和实发数量
						$common_orders[$r['spec_id']][$r['trade_id']]['rec_id']=$r['id'];
						$common_orders[$r['spec_id']][$r['trade_id']]['num']=$r['actual_num'];
					}else{
// 						$common_orders[$r['spec_id']]['left_num']+=$r['left_num'];
// 						$common_orders[$r['spec_id']]['num']=$common_orders[$r['spec_id']]['left_num'];
					}
				}else{
					$common_orders[$r['spec_id']]=$r;
					$common_orders[$r['spec_id']][$r['trade_id']]['rec_id']=$r['id'];
					$common_orders[$r['spec_id']][$r['trade_id']]['num']=$r['actual_num'];
					$common_orders[$r['spec_id']]['trade_num']=1;
				}
			}
			foreach ($common_orders as $n){
				if($n['trade_num']<$trade_num){
					unset($common_orders[$n['spec_id']]);
				}else{
					$common_orders[$n['spec_id']]['discount']=(float)$common_orders[$n['spec_id']]['spec_discount']*$common_orders[$n['spec_id']]['left_num'];
					$common_orders[$n['spec_id']]['weight']=floatval($common_orders[$n['spec_id']]['spec_weight']*$common_orders[$n['spec_id']]['left_num']);
					$common_orders[$n['spec_id']]['share_post']=floatval($common_orders[$n['spec_id']]['spec_post']*$common_orders[$n['spec_id']]['left_num']);
					$common_orders[$n['spec_id']]['share_amount']=$common_orders[$n['spec_id']]['spec_amount']*floatval($common_orders[$n['spec_id']]['left_num']);
				}
			}
			$common_orders=array_values($common_orders);
			if(count($common_orders)==0){
				SE('所选订单中无相同货品');
			}
		}catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $common_orders;
	}
	// 获取订单中的组合装信息
	public function getSuiteOrders($id){
		$sql_error_info='';
		$common_suite_orders=array();
		try{
			$sql_error_info='getCommonSuiteOrders-get_sales_trade_order';
			$res_orders_arr=$this->alias('sto')->field('sto.trade_id,sto.suite_id,sto.suite_no,sto.suite_name,sto.suite_num as num,sto.suite_num as left_num,sto.goods_name,sto.api_goods_name,sto.api_spec_name,sto.src_oid')
			     ->where(array('sto.trade_id'=>array('eq',$id),'sto.actual_num'=>array('gt',0)))->select();
			foreach($res_orders_arr as $r){
				if ($r['suite_id']==0) {
					continue;
				}				
				$common_suite_orders[$r['src_oid']]=$r;
				$common_suite_orders[$r['src_oid']]['split_num']=0;
			}
			$common_suite_orders=array_values($common_suite_orders);
			if(count($common_suite_orders)==0){
				SE('订单中没有组合装');
			}
		}catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $common_suite_orders;
	}
}