<?php
namespace Trade\Model;
use Platform\Common\ManagerFactory;
use Think\Exception\BusinessLogicException;
use Think\Exception;
class TradeCheckModel extends TradeModel{

	/**
	 * 订单--拆分--验证
	 * @param integer $id
	 * @param array $list
	 * @return boolean
	 */
	public function getSplitCheckInfo($id,&$list,$weight=0,&$weight_list=array())
	{
		try {
			$sales_trade_db=M('sales_trade');
			$res_cfg_val=get_config_value('order_edit_must_checkout');
			$trade=$sales_trade_db->alias('st')->field('st.trade_no,st.warehouse_id,st.src_tids,st.goods_amount,st.post_amount,st.discount,st.receivable,st.trade_status,st.goods_count,st.freeze_reason,st.bad_reason,st.delivery_term,st.checkouter_id')->where(array('st.trade_id'=>array('eq',$id)))->find();
			$trade_order=M('sales_trade_order')->alias('sto')->field('sto.spec_no,sto.weight/sto.actual_num as spec_weight')->where(array('sto.trade_id'=>array('eq',$id)))->select();
			if (empty($trade))
			{
				$list[]=array('trade_no'=>'','result_info'=>'订单不存在');
				return false;
			}
			if($trade['goods_count']<2)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单只有一个货品，不可拆分');
				return false;
			}
			if($trade['trade_status']!=30&&$trade['trade_status']!=25)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
				return false;
			}
			if($trade['freeze_reason']!=0)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
				return false;
			}
			if($trade['bad_reason']!=0)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单有异常标记，请先处理');
				return false;
			}
			if($trade['delivery_term']==2)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'货到付款订单不可拆分');
				return false;
			}
			if($trade['is_sealed']!=0)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单不可拆分');
				return false;
			}
			if($res_cfg_val==1&&$trade['checkouter_id']==0)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单必须签出才能操作');
				return false;
			}
			foreach ($trade_order as $v){
				if($weight>0&&$v['spec_weight']>$weight)
				{
					$weight_list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单存在大于限重的货品'.$v['spec_no']);
				}
			}
			
			$list=array('warehouse_id'=>$trade['warehouse_id'],'src_tids'=>$trade['src_tids'],'goods_amount'=>$trade['goods_amount'],'post_amount'=>$trade['post_amount'],'discount'=>$trade['discount'],'receivable'=>$trade['receivable']);
			return true;
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			$list[]=array('trade_no'=>'','result_info'=>'订单拆分-未知错误');
		}
		return false;
	}
	
	/**
	 * 拆分--订单
	 * @param integer $trade_id
	 * @param array $main_trade_orders
	 * @param integer $user_id
	 */
	public function splitTrade($trade_id,$main_trade_orders,$user_id,$is_noTrans=0)
	{
		$trade_id=intval($trade_id);
		$is_rollback=false;
		$sql_error_info='';
		try {
			$sql_error_info='splitTrade-sale_trade_query';
			$res_trade_arr=$this->getSalesTrade(
					'st.trade_id,st.trade_no, st.platform_id, st.shop_id, st.warehouse_id, st.warehouse_type, st.src_tids,
					st.trade_status, st.check_step, st.trade_from, st.trade_type, st.delivery_term, st.unmerge_mask,
					st.fenxiao_type, st.fenxiao_nick, st.trade_time, st.pay_time, st.delay_to_time, st.customer_type, 
					st.customer_id, st.buyer_nick, st.receiver_name, st.receiver_country, st.receiver_province, 
					st.receiver_city, st.receiver_district, st.receiver_address, st.receiver_mobile, st.receiver_telno, 
					st.receiver_zip, st.receiver_area, st.receiver_ring, st.receiver_dtb, st.to_deliver_time, st.dist_center, 
					st.dist_site, st.logistics_id, st.buyer_message, st.cs_remark, st.cs_remark_change_count, st.goods_amount, 
					st.post_amount, st.discount, st.receivable, st.remark_flag, st.print_remark, st.tax_rate, st.invoice_type, 
					st.invoice_title, st.invoice_content, st.invoice_id, st.salesman_id,0 AS flag_id, st.bad_reason, 
					st.is_sealed, st.gift_mask, st.trade_id AS split_from_trade_id, st.created',
					array('st.trade_id'=>array('eq',$trade_id)),
					'st'
			);
			$sql_error_info='splitTrade-sales_trade_order_query';
			$trade_order_db=D('Trade/SalesTradeOrder');
			$res_orders_arr=$trade_order_db->getSalesTradeOrderList(
					'sto.rec_id,sto.trade_id,sto.shop_id, sto.goods_id, sto.spec_id, sto.platform_id, sto.src_oid, 
					sto.suite_id, sto.src_tid, sto.gift_type, sto.refund_status, sto.guarantee_mode, sto.delivery_term, 
					sto.bind_oid, sto.num, sto.price, sto.actual_num, sto.order_price, sto.share_price, sto.discount, 
					sto.share_amount, sto.share_post, sto.weight, sto.commission, sto.paid, sto.goods_name, sto.goods_no, 
					sto.spec_name, sto.spec_no, sto.spec_code, sto.suite_no, sto.suite_name, sto.suite_num, sto.suite_amount, 
					sto.suite_discount, sto.cid, sto.is_print_suite, sto.api_goods_name, sto.api_spec_name, sto.goods_type, 
					sto.flag, sto.stock_reserved, sto.is_consigned, sto.large_type, sto.invoice_type, sto.invoice_content, 
					sto.from_mask, sto.is_master, sto.remark, sto.created',
					array('sto.trade_id'=>array('eq',$trade_id)),
					'sto'
			);
			$arr_orders=array();
			$goods_amount=0;
			$post_amount=0;
			$discount=0;
			$receivable=0;
			$orders_num=0;
			$orders_num_new=0;
			$length=count($res_orders_arr);
			//--------------整理得到-拆分订单的货品orders--------------------------
			for($i=0;$i<$length;$i++)
			{								 
				for($j=0;$j<count($main_trade_orders);$j++){
					$left_num=floatval($res_orders_arr[$i]['actual_num']);	
					if($res_orders_arr[$i]['rec_id']==$main_trade_orders[$j]['id'])
					{
						$split_num=floatval($main_trade_orders[$j]['split_num']);
						$orders_num_new+=$split_num;//1
						$arr_orders[$j]=$res_orders_arr[$i];
						unset($arr_orders[$j]['rec_id']);
						$arr_orders[$j]['num']=$split_num;
					    $arr_orders[$j]['actual_num']=$split_num;
						$actual_num=$left_num;
						$left_num=$actual_num-$split_num;
						$arr_orders[$j]['discount']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['discount']):floatval($res_orders_arr[$i]['discount'])*$split_num/$actual_num;
						$arr_orders[$j]['share_amount']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['share_amount']):floatval($res_orders_arr[$i]['share_amount'])*$split_num/$actual_num;
						$arr_orders[$j]['share_post']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['share_post']):floatval($res_orders_arr[$i]['share_post'])*$split_num/$actual_num;
						$arr_orders[$j]['weight']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['weight']):floatval($res_orders_arr[$i]['weight'])*$split_num/$actual_num;
						$arr_orders[$j]['commission']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['commission']):floatval($res_orders_arr[$i]['commission'])*$split_num/$actual_num;
						$arr_orders[$j]['is_master']=($split_num==$actual_num)?$res_orders_arr[$i]['is_master']:0;
						
						/* $left_num=floatval($arr_data_main[$j]['left_num']);
						 $res_orders_arr[$i]['discount']=floatval($res_orders_arr[$i]['discount'])-$arr_orders[$j]['discount'];
						 $res_orders_arr[$i]['share_amount']=floatval($res_orders_arr[$i]['share_amount'])-$arr_orders[$j]['share_amount'];
						 $res_orders_arr[$i]['share_post']=floatval($res_orders_arr[$i]['share_post'])-$arr_orders[$j]['share_post'];
						 $res_orders_arr[$i]['weight']=floatval($res_orders_arr[$i]['weight'])-$arr_orders[$j]['weight'];
						 $res_orders_arr[$i]['commission']=floatval($res_orders_arr[$i]['commission'])-$arr_orders[$j]['commission'];
						 $res_orders_arr[$i]['actual_num']=$left_num;
						 $res_orders_arr[$i]['num']=$left_num; */
						
						$goods_amount+=$arr_orders[$j]['share_amount'];
						$post_amount+=$arr_orders[$j]['share_post'];
						$discount+=$arr_orders[$j]['discount'];
						$receivable+=$arr_orders[$j]['share_amount'];
						// $j++;
					}
					$orders_num+=floatval($left_num);
				}				
			}
			if($orders_num==0)
			{
				SE('主订单必须包含货品');
			}
			if($orders_num_new==0)
			{
				SE('拆分订单必须包含货品');
			}
			$res_trade_arr['goods_amount']=$goods_amount;
			$res_trade_arr['post_amount']=$post_amount;
			$res_trade_arr['discount']=$discount;
			$res_trade_arr['receivable']=$receivable;
			$is_rollback=true;
			//调用公共拆分函数
			if($is_noTrans==1){
				$res_trade_id=D('Trade/Trade')->splitTradeCommonNoTrans($res_trade_arr,$arr_orders,$user_id,0);
			}else{
				$res_trade_id=D('Trade/Trade')->splitTradeCommon($res_trade_arr,$arr_orders,$user_id,0);
			}
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}catch (\Exception $e){
			SE($e->getMessage());
		}
		return $res_trade_id;
	}
	//按重量拆分订单
	public function splitByWeight($id,$weight){
		$is_rollback=false;
		$sql_error_info='';
		$user_id=get_operator_id();
		try{
			$res_trade=$this->getSalesTrade(
					'st.trade_no, st.platform_id, st.shop_id, st.warehouse_id, st.warehouse_type, st.src_tids,
					st.trade_status, st.check_step, st.trade_from, st.trade_type, st.delivery_term, st.unmerge_mask,
					st.fenxiao_type, st.fenxiao_nick, st.trade_time, st.pay_time, st.delay_to_time, st.customer_type,
					st.customer_id, st.buyer_nick, st.receiver_name, st.receiver_country, st.receiver_province,
					st.receiver_city, st.receiver_district, st.receiver_address, st.receiver_mobile, st.receiver_telno,
					st.receiver_zip, st.receiver_area, st.receiver_ring, st.receiver_dtb, st.to_deliver_time, st.dist_center,
					st.dist_site, st.logistics_id, st.buyer_message, st.cs_remark, st.cs_remark_change_count, st.goods_amount,
					st.post_amount, st.discount, st.receivable, st.remark_flag, st.print_remark, st.tax_rate, st.invoice_type,
					st.invoice_title, st.invoice_content, st.invoice_id, st.salesman_id,0 AS flag_id, st.bad_reason,
					st.is_sealed, st.gift_mask, st.trade_id AS split_from_trade_id, st.weight, st.created',
					array('st.trade_id'=>array('eq',$id)),
					'st'
					);
			if(!($res_trade['weight']>$weight)){//订单重量小于拆分重量，该单不需要拆分
				return $res_trade['trade_no'];
			}
			$trade_no=$res_trade['trade_no'];
			$res_orders_arr=D('SalesTradeOrder')->getSalesTradeOrderList(
					'sto.shop_id, sto.goods_id, sto.spec_id, sto.platform_id, sto.src_oid,
					sto.suite_id, sto.src_tid, sto.gift_type, sto.refund_status, sto.guarantee_mode, sto.delivery_term,
					sto.bind_oid, sto.num, sto.price, sto.actual_num, sto.order_price, sto.share_price, sto.discount,
					sto.share_amount, sto.share_post, sto.weight, sto.commission, sto.paid, sto.goods_name, sto.goods_no,
					sto.spec_name, sto.spec_no, sto.spec_code, sto.suite_no, sto.suite_name, sto.suite_num, sto.suite_amount,
					sto.suite_discount, sto.cid, sto.is_print_suite, sto.api_goods_name, sto.api_spec_name, sto.goods_type,
					sto.flag, sto.stock_reserved, sto.is_consigned, sto.large_type, sto.invoice_type, sto.invoice_content,
					sto.from_mask, sto.is_master, sto.remark, sto.created,
					sto.weight/sto.actual_num as spec_weight,sto.share_post/sto.actual_num as spec_post,sto.discount/sto.actual_num as spec_discount',
					array('sto.trade_id'=>array('eq',$id)),
					'sto','','sto.weight/sto.actual_num DESC'
					);
			$new=array();
			foreach ($res_orders_arr as $r){
				for ($i=0;$i<count($new);$i++){
					if(!($weight-$new[$i]['trade']['weight']<$r['spec_weight'])&&$r['actual_num']!=0){
						$new[$i][]=$r;
						$index=count($new[$i])-2;
						$new[$i][$index]['actual_num']=$r['spec_weight']==0?$r['actual_num']:(intval(($weight-$new[$i]['trade']['weight'])/$r['spec_weight'])<$r['actual_num']?intval(($weight-$new[$i]['trade']['weight'])/$r['spec_weight']):$r['actual_num']);
						$r['actual_num']-=$new[$i][$index]['actual_num'];
						//调整子订单数据
						$new[$i][$index]['weight']=$new[$i][$index]['actual_num']*$new[$i][$index]['spec_weight'];
						$new[$i][$index]['num']=$new[$i][$index]['actual_num'];
						$new[$i][$index]['share_amount']=$new[$i][$index]['actual_num']*$new[$i][$index]['share_price'];
						$new[$i][$index]['paid']=$new[$i][$index]['share_amount'];
						$new[$i][$index]['share_post']=$new[$i][$index]['actual_num']*$new[$i][$index]['spec_post'];
						$new[$i][$index]['discount']=$new[$i][$index]['actual_num']*$new[$i][$index]['spec_discount'];
						//调整订单数据
						$new[$i]['trade']['weight']+=$new[$i][$index]['weight'];
						$new[$i]['trade']['goods_amount']+=$new[$i][$index]['share_amount'];
						$new[$i]['trade']['post_amount']+=$new[$i][$index]['share_post'];
						$new[$i]['trade']['discount']+=$new[$i][$index]['discount'];
						$new[$i]['trade']['receivable']=$new[$i]['trade']['goods_amount'];
					}
				}
				while ($r['actual_num']!=0) {
					$new[][0]=$r;
					$index=count($new)-1;
					$new[$index][0]['actual_num']=$r['spec_weight']==0?$r['actual_num']:($r['spec_weight']>$weight?1:(intval($weight/$r['spec_weight'])<$r['actual_num']?intval($weight/$r['spec_weight']):$r['actual_num']));
					$r['actual_num']-=$new[$index][0]['actual_num'];
					//调整子订单数据
					$new[$index][0]['weight']=$new[$index][0]['actual_num']*$new[$index][0]['spec_weight'];
					$new[$index][0]['num']=$new[$index][0]['actual_num'];
					$new[$index][0]['share_amount']=$new[$index][0]['actual_num']*$new[$index][0]['share_price'];
					$new[$index][0]['paid']=$new[$index][0]['share_amount'];
					$new[$index][0]['share_post']=$new[$index][0]['actual_num']*$new[$index][0]['spec_post'];
					$new[$index][0]['discount']=$new[$index][0]['actual_num']*$new[$index][0]['spec_discount'];
					//调整订单数据
					$new[$index]['trade']['weight']+=$new[$index][0]['weight'];
					$new[$index]['trade']['goods_amount']+=$new[$index][0]['share_amount'];
					$new[$index]['trade']['post_amount']+=$new[$index][0]['share_post'];
					$new[$index]['trade']['discount']+=$new[$index][0]['discount'];
					$new[$index]['trade']['receivable']=$new[$index]['trade']['goods_amount'];
				}
			}
			$is_rollback=true;
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$trade_log=array();
			//获取配置值
			$res_cfg_val=get_config_value('open_package_strategy');
			for ($i=0;$i<count($new);$i++){
				if($i==0){
					//删除原订单货品并按照拆分后的第一条订单生成新货品
					$sql_error_info='splitByWeight-delete_res_order';
					M('sales_trade_order')->execute('DELETE FROM sales_trade_order WHERE trade_id = '.$id);
					$sql_error_info='splitByWeight-updete_res_trade';
					$this->updateSalesTrade(
							array(
									'weight'=>set_default_value($n[0]['trade']['weight'], 0),
									'goods_amount'=>set_default_value($n[0]['trade']['goods_amount'], 0),
									'post_amount'=>set_default_value($n[0]['trade']['post_amount'], 0),
									'discount'=>set_default_value($n[0]['trade']['discount'], 0),
									'receivable'=>set_default_value($n[0]['trade']['receivable'], 0),
									'split_from_trade_id'=>$id,
							),
							array('trade_id'=>array('eq',$id)));
					$sql_error_info='splitByWeight-add_new_order';
					unset($new[0]['trade']);
					$length=count($new[0]);
					for ($j=0;$j<$length;$j++)
					{
						$new[0][$j]['trade_id']=$id;
					}
					D('SalesTradeOrder')->addSalesTradeOrder($new[0]);
					$sql_error_info='splitTrade-split_trade_refresh_1';
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$id.", IF(".$res_cfg_val.",4,0)|2, 0)");
				}else{
					//生成新订单以及相应货品
					$res_trade['weight']=set_default_value($new[$i]['trade']['weight'], 0);
					$res_trade['goods_amount']=set_default_value($new[$i]['trade']['goods_amount'], 0);
					$res_trade['post_amount']=set_default_value($new[$i]['trade']['post_amount'], 0);
					$res_trade['discount']=set_default_value($new[$i]['trade']['discount'], 0);
					$res_trade['receivable']=set_default_value($new[$i]['trade']['receivable'], 0);
					$sql_error_info='splitByWeight-get_sys_no';
					//获取trade_no
					$res_trade['trade_no']=get_sys_no('sales');
					//订单拆分--加上内置标记
					$res_trade['flag_id']=D('Setting/Flag')->getFlagId('拆分订单',1);
					$sql_error_info="splitByWeight-add_sales_trade";
					$res_trade_id=$this->add($res_trade);
					unset($new[$i]['trade']);
					$length=count($new[$i]);
					for ($j=0;$j<$length;$j++)
					{
						$new[$i][$j]['trade_id']=$res_trade_id;
					}
					$sql_error_info="splitByWeight-add_sales_trade_order";
					D('SalesTradeOrder')->addSalesTradeOrder($new[$i]);
					$bind_order=$this->query(
							"SELECT sto1.bind_oid,sto1.is_print_suite FROM sales_trade_order sto1,sales_trade_order sto2
				    		WHERE sto1.trade_id=".$id." AND sto2.trade_id=".$res_trade_id." AND sto1.bind_oid<>''
							AND sto1.bind_oid=sto2.bind_oid AND sto1.actual_num>0 AND sto2.actual_num>0 LIMIT 1"
							);
					if(!empty($bind_order))
					{
						if($bind_order[0]['is_print_suite']!=0)
						{//包含不可拆分组合装,子订单ID
							SE('包含不可拆分组合装,子订单ID'.$bind_order[0]['bind_oid']);
						}else
						{//包含不可拆分子订单
							SE('包含不可拆分子订单:'.$bind_order[0]['bind_oid']);
						}
					}
					//订单进行刷新
					$sql_error_info='splitTrade-split_trade_refresh_2';
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$res_trade_id.", IF(".$res_cfg_val.",4,0)|2, 0)");
					//获取下单和支付时间
					$trade_pay=$this->getSalesTrade('trade_time,pay_time',array('trade_id'=>array('eq',$res_trade_id)));
					$trade_log[]=array(
							'trade_id'=>$res_trade_id,
							'operator_id'=>$user_id,
							'type'=>1,
							'data'=>0,
							'message'=>'拆分订单：'.$res_trade['trade_no'].',下单时间：'.$trade_pay['trade_time'],
							'created'=>date('y-m-d H:i:s',time()),
					);
					if(strtotime($trade_pay['pay_time'])>strtotime('1000-01-01'))
					{
						$trade_log[]=array(
								'trade_id'=>$res_trade_id,
								'operator_id'=>$user_id,
								'type'=>2,
								'data'=>0,
								'message'=>'拆分订单：'.$res_trade['trade_no'].',付款时间：'.$trade_pay['pay_time'],
								'created'=>date('y-m-d H:i:s',time()),
						);
					}
					$trade_log[]=array(
							'trade_id'=>$res_trade_id,
							'operator_id'=>$user_id,
							'type'=>37,
							'data'=>1,
							'message'=>'从'.$trade_no.' 拆分订单 '.$res_trade['trade_no'],
							'created'=>date('y-m-d H:i:s',time()),
					);
					$trade_log[]=array(
							'trade_id'=>$id,
							'operator_id'=>$user_id,
							'type'=>37,
							'data'=>1,
							'message'=>$trade_no.' 拆分订单到 '.$res_trade['trade_no'],
							'created'=>date('y-m-d H:i:s',time()),
					);
				}
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
		return true;
	}
	// 按组合装拆分订单
	public function splitBySuite($trade_id,$split_suites){
		$trade_id=intval($trade_id);
		$is_rollback=false;
		$sql_error_info='';
		$user_id=get_operator_id();		
		try{
			$sql_error_info='splitBySuite-sale_trade_query';
			$res_trade_arr=$this->getSalesTrade(
					'st.trade_id,st.trade_no, st.platform_id, st.shop_id, st.warehouse_id, st.warehouse_type, st.src_tids,
					st.trade_status, st.check_step, st.trade_from, st.trade_type, st.delivery_term, st.unmerge_mask,
					st.fenxiao_type, st.fenxiao_nick, st.trade_time, st.pay_time, st.delay_to_time, st.customer_type, 
					st.customer_id, st.buyer_nick, st.receiver_name, st.receiver_country, st.receiver_province, 
					st.receiver_city, st.receiver_district, st.receiver_address, st.receiver_mobile, st.receiver_telno, 
					st.receiver_zip, st.receiver_area, st.receiver_ring, st.receiver_dtb, st.to_deliver_time, st.dist_center, 
					st.dist_site, st.logistics_id, st.buyer_message, st.cs_remark, st.cs_remark_change_count, st.goods_amount, 
					st.post_amount, st.discount, st.receivable, st.remark_flag, st.print_remark, st.tax_rate, st.invoice_type, 
					st.invoice_title, st.invoice_content, st.invoice_id, st.salesman_id,0 AS flag_id, st.bad_reason, 
					st.is_sealed, st.gift_mask, st.trade_id AS split_from_trade_id, st.created',
					array('st.trade_id'=>array('eq',$trade_id)),
					'st'
			);
			$sql_error_info='splitBySuite-sales_trade_order_query';
			$trade_order_db=D('SalesTradeOrder');
			$res_orders_arr=$trade_order_db->getSalesTradeOrderList(
					'sto.rec_id,sto.trade_id,sto.shop_id, sto.goods_id, sto.spec_id, sto.platform_id, sto.src_oid, 
					sto.suite_id, sto.src_tid, sto.gift_type, sto.refund_status, sto.guarantee_mode, sto.delivery_term, 
					sto.bind_oid, sto.num, sto.price, sto.actual_num, sto.order_price, sto.share_price, sto.discount, 
					sto.share_amount, sto.share_post, sto.weight, sto.commission, sto.paid, sto.goods_name, sto.goods_no, 
					sto.spec_name, sto.spec_no, sto.spec_code, sto.suite_no, sto.suite_name, sto.suite_num, sto.suite_amount, 
					sto.suite_discount, sto.cid, sto.is_print_suite, sto.api_goods_name, sto.api_spec_name, sto.goods_type, 
					sto.flag, sto.stock_reserved, sto.is_consigned, sto.large_type, sto.invoice_type, sto.invoice_content, 
					sto.from_mask, sto.is_master, sto.remark, sto.created',
					array('sto.trade_id'=>array('eq',$trade_id)),
					'sto'
			);
			$is_rollback=true;
			$main_trade_orders=array();
			$arr_orders=array();
			$goods_amount=0;
			$post_amount=0;
			$discount=0;
			$receivable=0;
			$orders_num=0;
			$orders_num_new=0;
			$trade_no=$res_trade_arr['trade_no'];
			$length=count($res_orders_arr);
			// 查找组合装对应的单品
			foreach ($split_suites as $suite) {
				if($suite['split_num']==0){
					continue;
				}
				$suite_detail[$suite['suite_id']]=D('Goods/GoodsSuite')->getGoodsSuiteDetailById($suite['suite_id']);
				//从子订单表中查找
				foreach($res_orders_arr as $order) {
					if($order['suite_id']==$suite['suite_id']&&$order['src_oid']==$suite['src_oid']){
						$order['split_num']=$suite['split_num'];
						$main_trade_orders[]=$order;
					}
				}
			}
			for($i=0;$i<count($res_orders_arr);$i++)
			{		
				$left_num=floatval($res_orders_arr[$i]['actual_num']);						 
				for($j=0;$j<count($main_trade_orders);$j++){					
					if($res_orders_arr[$i]['suite_id']==$main_trade_orders[$j]['suite_id']&&$res_orders_arr[$i]['spec_id']==$main_trade_orders[$j]['spec_id']&&$res_orders_arr[$i]['src_oid']==$main_trade_orders[$j]['src_oid'])
					{
						$split_num=0;
						foreach($suite_detail[$res_orders_arr[$i]['suite_id']] as $k=>$v){
							if($v['spec_id']==$res_orders_arr[$i]['spec_id']){
								$split_num=floatval($main_trade_orders[$j]['split_num'])*$v['num'];
							}
						}
						$orders_num_new+=$split_num;//1
						$arr_orders[$j]=$res_orders_arr[$i];
						unset($arr_orders[$j]['rec_id']);
						$arr_orders[$j]['num']=$split_num;
					    $arr_orders[$j]['actual_num']=$split_num;
						$actual_num=$left_num;
						$left_num=$actual_num-$split_num;
						$arr_orders[$j]['discount']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['discount']):floatval($res_orders_arr[$i]['discount'])*$split_num/$actual_num;
						$arr_orders[$j]['share_amount']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['share_amount']):floatval($res_orders_arr[$i]['share_amount'])*$split_num/$actual_num;
						$arr_orders[$j]['share_post']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['share_post']):floatval($res_orders_arr[$i]['share_post'])*$split_num/$actual_num;
						$arr_orders[$j]['weight']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['weight']):floatval($res_orders_arr[$i]['weight'])*$split_num/$actual_num;
						$arr_orders[$j]['commission']=($split_num==$actual_num)?floatval($res_orders_arr[$i]['commission']):floatval($res_orders_arr[$i]['commission'])*$split_num/$actual_num;
						$arr_orders[$j]['is_master']=($split_num==$actual_num)?$res_orders_arr[$i]['is_master']:0;
						$goods_amount+=$arr_orders[$j]['share_amount'];
						$post_amount+=$arr_orders[$j]['share_post'];
						$discount+=$arr_orders[$j]['discount'];
						$receivable+=$arr_orders[$j]['share_amount'];
					}					
				}	
				$orders_num+=floatval($left_num);			
			}
			if($orders_num==0)
			{
				SE('主订单必须包含货品');
			}
			if($orders_num_new==0)
			{
				SE('拆分订单必须包含货品');
			}
			$res_trade_arr['goods_amount']=$goods_amount;
			$res_trade_arr['post_amount']=$post_amount;
			$res_trade_arr['discount']=$discount;
			$res_trade_arr['receivable']=$receivable;
			//调用刷新订单时先创建组合装临时表
			$res_trade_id=D('Trade/Trade')->splitTradeCommon($res_trade_arr,$arr_orders,$user_id,1);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return true;	
	}
	/**
	 * 合并--订单
	 * @param array $arr_ids_data
	 * @param array $arr_form_data
	 * @param integer $user_id
	 */
	public function mergeTrade($arr_ids_data,$arr_form_data,$user_id,$version=array())
	{
		$is_rollback=false;
		try {
			$is_rollback=true;
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$this->mergeTradeNoTrans($arr_ids_data,$arr_form_data,$user_id,$version);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
	}
		/**
	 * 合并--订单--无事务--调用前先CALL存储过程I_DL_TMP_SUITE_SPEC
	 * @param array $arr_ids_data
	 * @param array $arr_form_data
	 * @param integer $user_id
	 */
	public function mergeTradeNoTrans($arr_ids_data,$arr_form_data,$user_id,$version=array())
	{
		$trade_log=array();
		$trade_log_db=D('SalesTradeLog');
		try {
			$res_cfg_val=get_config_value(array('order_check_warn_has_unmerge','order_check_merge_warn_mode','open_package_strategy'),array(1,0,1));
			$trades=$this->getSalesTradeList(
					'st.trade_id,st.trade_no,st.split_from_trade_id,st.customer_id,st.receiver_name,
					 st.receiver_area,st.receiver_address,st.buyer_message,st.cs_remark,st.pay_account,st.version_id',
					array('st.trade_id'=>array('in',$arr_ids_data)),
					'st'
			);
			$trade_count=count($trades);
			$main_trade=array();
			if(count($version)>0){
				foreach ($trades as $v) {
					if($v['version_id']!=$version[$v['trade_id']]){
						SE('订单被其他人修改，请打开重新编辑');
					}
				}
			}
			//验证主订单是否存在
			for ($i=0;$i<$trade_count;$i++)
			{
				if($trades[$i]['trade_id']==$arr_form_data['receiver'])
				{
					$main_trade=$trades[$i];
					unset($trades[$i]);
					break;
				}
			}	
			if(empty($main_trade))
			{
				SE('未找到主订单');
			}
			//-------子订单合并操作过程--------------------------------------------
			foreach ($trades as $v)
			{
				//子订单合并
				$sql_error_info=$main_trade['trade_no'].' and '.$v['trade_no'].'-sub_orders_merge';
				$this->execute(
						"UPDATE sales_trade_order sto1,sales_trade_order sto2 SET sto1.num=sto1.num+sto2.num, sto1.actual_num=sto1.actual_num+sto2.actual_num, 
						 sto1.paid=sto1.paid+sto2.paid, sto1.share_amount=sto1.share_amount+sto2.share_amount, sto1.share_post=sto1.share_post+sto2.share_post, 
						 sto1.discount=sto1.discount+sto2.discount, sto1.weight=sto1.weight+sto2.weight, sto1.commission=sto1.commission+sto2.commission, 
						 sto1.is_master=GREATEST(sto1.is_master,sto2.is_master), sto1.tax_rate=GREATEST(sto1.tax_rate,sto2.tax_rate), 
						 sto1.share_price=IF(sto1.actual_num=0,0,sto1.share_amount/sto1.actual_num), sto1.remark = IF(sto1.remark = sto2.remark,sto1.remark,
						 CONCAT_WS(' ',sto1.remark,sto2.remark)) 
						 WHERE sto1.trade_id=".$main_trade['trade_id']." AND sto2.trade_id=".$v['trade_id']." AND sto2.spec_id=sto1.spec_id 
						 AND sto2.platform_id=sto1.platform_id AND sto2.src_oid=sto1.src_oid  AND sto2.suite_id=sto1.suite_id AND sto2.flag=sto1.flag"
				);
				//子订单删除
				$sql_error_info=$v['trade_no'].'-sub_orders_delete';
				$this->execute(
						"DELETE sto2 FROM sales_trade_order sto1,sales_trade_order sto2 WHERE sto1.trade_id=".$main_trade['trade_id']." 
						 AND sto2.trade_id=".$v['trade_id']." AND sto2.spec_id=sto1.spec_id AND sto2.platform_id=sto1.platform_id 
						 AND sto2.src_oid=sto1.src_oid AND sto2.suite_id=sto1.suite_id AND sto2.flag=sto1.flag"
				);
				//子订单更新
				$sql_error_info=$v['trade_no'].'-sub_orders_update';
				$this->execute("UPDATE sales_trade_order SET trade_id=".$main_trade['trade_id']." WHERE trade_id=".$v['trade_id']);
				//赠品更新
				$sql_error_info=$v['trade_no'].'-sub_orders_gift_update';
				$this->execute("UPDATE sales_gift_record SET trade_id=".$main_trade['trade_id']." WHERE trade_id=".$v['trade_id']);
				if(!empty($v['buyer_message'])||!empty($v['cs_remark'])||!empty($v['pay_account']))
				{
					//留言、备注、支付账户合并
					$sql_error_info=$v['trade_no'].'-messsage_remark_payaccout_merge';
					$this->execute(
							"UPDATE sales_trade SET 
							 buyer_message = IF(buyer_message<>'',IF(LOCATE('".$v['buyer_message']."',buyer_message,1)>0, buyer_message,LEFT(CONCAT_WS(';',buyer_message,'".$v['buyer_message']."'),1024)),'".$v['buyer_message']."'),
							 cs_remark = IF(cs_remark<>'',IF(LOCATE('".$v['cs_remark']."',cs_remark,1)>0,cs_remark,LEFT(CONCAT_WS(';',cs_remark,'".$v['cs_remark']."'),1024)),'".$v['cs_remark']."'), 
							 pay_account = IF(pay_account<>'',IF('".$v['pay_account']."' = pay_account,pay_account,LEFT(CONCAT_WS(';',pay_account,'".$v['pay_account']."'),128)),'".$v['pay_account']."') 
							 WHERE trade_id =".$main_trade['trade_id']
					);
				}
				if($main_trade['split_from_trade_id']==0&&$v['split_from_trade_id']!=0)
				{
					$main_trade['split_from_trade_id']=$v['split_from_trade_id'];
				}
				//删除被合并的订单--改成-->更新被合并订单(status:120)
				//$sql_error_info=$v['trade_no'].'-trade_merged_delete';
				//$res_flag=$this->execute("DELETE FROM sales_trade WHERE trade_id=".$v['trade_id']);
				$this->updateSalesTrade(array('trade_status'=>120),array('trade_id'=>array('eq',$v['trade_id'])));
				//日志合并
				$trade_log_db->updateTradeLog(
						array('trade_id'=>$main_trade['trade_id'],'data'=>array('exp','IF(type=1 OR type=2,-50,data)')),
						array('trade_id'=>array('eq',$v['trade_id']))
				);
				//添加新日志
				$trade_log[]=array(
					'trade_id'=>$main_trade['trade_id'],
					'operator_id'=>$user_id,
					'type'=>38,
					'data'=>0,
					'message'=>'合并订单：'.$v['trade_no'].'到'.$main_trade['trade_no'],
					'created'=>date('y-m-d H:i:s',time()),
				);
			}
			//--------------主订单信息合并---------------------------------
			$trade_info=array(
					'salesman_id'=>$arr_form_data['salesman'],
					'logistics_id'=>$arr_form_data['logistics'],
					'shop_id'=>$arr_form_data['shop'],
					'receiver_mobile'=>$arr_form_data['mobile'],
					'receiver_telno'=>$arr_form_data['telno'],
					'print_remark'=>empty($arr_form_data['print_remark'])?'':$arr_form_data['print_remark'],
					'split_from_trade_id'=>$main_trade['split_from_trade_id']
			);
			//加上合并--内置标记
			$old_flags = $this->alias('st')->field("flag_id")->where(array('st.trade_id'=>array('in',$arr_ids_data)))->select();
			$flag_value = 0;
			foreach ($old_flags as $of) {
				if ($of['flag_id']>1000) { $flag_value=$of['flag_id']; }
			}
			if ($flag_value==0) {
				$trade_info['flag_id']=D('Setting/Flag')->getFlagId('合并订单',1);
			}else{
				$trade_info['flag_id']=$flag_value;
			}
			$sql_error_info=$v['trade_no'].'-trade_merged_add';
			$this->updateSalesTrade(
					$trade_info, 
					array('trade_id'=>array('eq',$main_trade['trade_id']))
			);
			//合并后-订单刷新
			$sql_error_info=$v['trade_no'].'-trade_merged_refresh';
			$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$main_trade['trade_id'].", IF(".$res_cfg_val['open_package_strategy'].",22,18), 0)");
			//删除因拆分生成的下单日志
			$sql_error_info=$v['trade_no'].'-sales_trade_log_query';
			$res_sales_log_arr=$this->query("SELECT 1 FROM sales_trade_log WHERE trade_id=".$main_trade['trade_id']." AND `type`=1 AND `data`>0");
			if (!empty($res_sales_log_arr))
			{
				$sql_error_info=$v['trade_no'].'-sales_trade_log_delete_1';
				$this->execute("DELETE FROM sales_trade_log WHERE trade_id=".$main_trade['trade_id']." AND `type`=1 AND `data`=0");
			}
			//删除因拆分生成的付款日志
			$sql_error_info=$v['trade_no'].'-sales_trade_log_query_2';
			$res_sales_log_arr=$this->query("SELECT 1 FROM sales_trade_log WHERE trade_id=".$main_trade['trade_id']." AND `type`=2 AND `data`>0");
			if (!empty($res_sales_log_arr))
			{
				$sql_error_info=$v['trade_no'].'-sales_trade_log_delete_error2';
				$this->execute("DELETE FROM sales_trade_log WHERE trade_id=".$main_trade['trade_id']." AND `type`=2 AND `data`=0;");
			}
			//更新标记同名未合并
			if($res_cfg_val['order_check_warn_has_unmerge'])
			{
				$this->updateWarnUnmerge($main_trade['trade_id'],$main_trade['customer_id']);
			}
			//更新订单的拆分来源
			if($main_trade['split_from_trade_id']!=0)
			{
				$sql_error_info=$v['trade_no'].'-sales_trade_order_merged_query';
				$res_flag=$this->query("SELECT COUNT(DISTINCT sto2.trade_id) AS count_val FROM sales_trade_order sto1,sales_trade_order sto2 WHERE sto1.trade_id=".$main_trade['trade_id']." AND sto2.platform_id=sto1.platform_id AND sto2.src_tid=sto1.src_tid");
				if($res_flag[0]['count_val']<=0)
				{
			
					$sql_error_info=$v['trade_no'].'-split_from_trade_update_fail';
					$res_flag=$this->execute("UPDATE sales_trade SET split_from_trade_id = 0 WHERE trade_id = ".$main_trade['trade_id']);
				}
			}
			$trade_log_db->addTradelog($trade_log);			
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
	}

	/**
	 * 审核--订单
	 * @param string $sql_inner_join
	 * @param integer $check_type -1--快速审核  0--普通审核   1--强制审核
	 * @param integer $user_id
	 * @return multitype:multitype: number
	 * @param $check_pace 0--订单审核 1--财务审核
	 * @param $remain_info --需要保留的信息
	 */
	public function checkTrade($sql_inner_join,$check_type,$user_id,$check_pace=0,$is_direct_consign=0,$remain_info=array())
	{
		$list=array();
		$success=array();
		$finacial_trade=array();
		$stockout_orders=array();//用于获取电子面单
		$stockout_sync = array();//用于自动预物流同步
		$is_rollback=false;
		try{
			$this->startTrans();
			$result=$this->checkTradeNoTrans($sql_inner_join,$check_type,$user_id,$check_pace,$is_direct_consign,$remain_info,$list,$success,$finacial_trade,$stockout_orders,$stockout_sync);
			$this->commit();
			if(!empty($result)&&$result['check']==0){
				return $result;
			}
			//-----------------自动获取电子面单--------------------------
			$arr_cfg_key=array('order_check_get_waybill','order_check_synchronous_logistics');
			$arr_cfg_def_val=array(0,0);
			$res_cfg_val=get_config_value($arr_cfg_key,$arr_cfg_def_val);
			//-----------------自动获取电子面单--------------------------
			if ($res_cfg_val['order_check_get_waybill']!=0&&$is_direct_consign==0)
			{
                $this->getWayBill($stockout_orders,$success,$list);
			}
            //-----------------自动预物流同步---------------------------------------------
            if ($res_cfg_val['order_check_synchronous_logistics']!=0)
            {
                $this->synchronousLogistics($stockout_sync,$list);
            }
		}catch (\PDOException $e)
		{
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e)
		{
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}

		$result=array(
			'check'=>empty($success)?false:true,
			'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
			'fail'=>$list,//失败提示信息
			'success'=>$success,//成功的数据
			'financial'=>!empty($finacial_trade)?$finacial_trade:'',
		);
		return $result;
	}

	/**
	 * 审核--订单--无事务
	 * @param string $sql_inner_join
	 * @param integer $check_type -1--快速审核  0--普通审核   1--强制审核
	 * @param integer $user_id
	 * @return multitype:multitype: number
	 * @param $check_pace 0--订单审核 1--财务审核
	 * @param $remain_info --需要保留的信息
	 */
	public function checkTradeNoTrans($sql_inner_join,$check_type,$user_id,$check_pace=0,$is_direct_consign=0,$remain_info=array(),&$list=array(),&$success=array(),&$finacial_trade=array(),&$stockout_orders=array(),&$stockout_sync=array())
	{
		$sql_error_info='';
		try{
			$sql="SELECT st.trade_id,st.trade_no,st.shop_id,st.warehouse_id,st.warehouse_type,st.trade_status,st.trade_from,st.platform_id, 
				  st.trade_type,st.freeze_reason,st.refund_status, st.unmerge_mask,st.goods_type_count,st.customer_id,st.buyer_nick,
				  st.receiver_name,st.receiver_area,st.receiver_address, st.receiver_mobile,st.receiver_telno,st.logistics_id,
				  st.goods_amount,st.discount,st.receivable,st.discount_change,st.invoice_type, st.checkouter_id,st.post_amount,
				  st.delivery_term,st.buyer_nick,st.paid,st.revert_reason, st.bad_reason,split_from_trade_id,is_sealed,src_tids,
				  cl.is_support_cod,st.version_id,st.weight FROM sales_trade st INNER JOIN (".$sql_inner_join.") st2 ON st2.trade_id=st.trade_id 
				  LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id ORDER BY st.trade_id DESC LIMIT 300 FOR UPDATE";
			$trades=$this->query($sql);
			if (empty($trades))
			{
				if($check_type!=2){
					SE('未找到符合条件的订单');
				}else{
					$result=array(
							'check'=>0,
							'status'=>0,//0全部成功，1异常错误，2部分成功
							'fail'=>$list,//失败提示信息
							'success'=>$success,//成功的数据
							'financial'=>'',
					);
					return $result;
				}
			}
			$arr_cfg_key=array(
					'order_edit_must_checkout',//订单审核条件：必须签出订单才可编辑 --0
					'order_check_below_lowest_price',//订单审核条件：阻止低于最低售价的订单通过审核 --0
					'order_check_below_cost',//订单审核条件：阻止低于成本价的订单通过审核 --0
					'order_check_no_stock',//订单审核条件：阻止库存不足订单通过审核  --0
					'order_check_black_customer',//订单审核条件：阻止黑名单客户订单通过审核 --0
					'order_check_warn_has_unmerge',//订单审核条件：提示同名未合并订单  --0
					'order_check_merge_warn_mode',//订单审核条件：选择同名未合并订单的条件 --0
					'order_check_warn_has_unmerge_preorder',//订单审核条件：同名未合并(包含预订单) --0
					'order_check_warn_has_unmerge_checked',//订单审核条件：同名未合并(包含已审核订单) --1
					'order_check_warn_has_unmerge_freeze',//订单审核条件：同名未合并(包含冻结) --0
					'order_check_warn_has_unmerge_address',//订单审核条件：同名未合并(包含不同地址) --0
						
					'order_check_warn_has_unmerge_completed',//订单审核条件：订单审核条件:同名未合并(包含已完成订单) --0
					'order_check_warn_completed_min',//订单审核条件：时间间隔限制-转换成小数 --0
						
					'order_check_warn_has_unpay',//订单审核条件：提示有未付款的同名未合并订单 --0
					'order_check_warn_unpay_min',//订单审核条件：时间间隔限制-转换成小数 --'0'
						
					'order_check_api_trade_address_unequal',//订单审核条件：阻止多个原始单地址不相同的订单通过审核 --0
					'order_fa_condition',//财务审核原因:进财务审核条件-开启财审的标识 --0
					'order_fc_receivable_under_goodscost',//财务审核原因:实收小于货品成本 --0
					'order_fc_receivable_under_goodscost_type',//财务审核原因:实收小于货品成本-类型 --0
					'order_fc_man_order',//财务审核原因:手工建单 --0
					'order_fc_excel_import',//财务审核原因:EXCEL导入 --0
					'order_fc_receivable_outnumber',//财务审核原因:订单应收金额高于**元 --0
					'order_fc_discount',//财务审核原因:优惠金额高于**元 --0
					'order_fc_gift',//财务审核原因：全赠品进财审 --0
					'order_fc_below_costprice',//财务审核原因：订单中商品成交金额低于成本价时进财审 --0
						
					'order_check_stock_add_purchase',//订单审核条件：计算库存不足时加上采购到货量 --0
					'order_check_stock_sub_to_transfer',//订单审核条件：计算库存不足扣减待调拨量。默认不勾选 --0
						
					'sales_trade_trace_enable',//订单审核条件：是否开启订单全链路 --1
			
					'sales_raw_count_exclude_gift',//订单审核条件：订单中货品原始货品数量不包含赠品 --0
					
					'order_check_get_waybill',//是否自动获取电子面单 --0

					'sys_available_stock',//可发库存计算方式 --640
					
					'order_check_address_reachable',//订单审核是否进行物流是否可达的校验 --0

					'order_mark_color_by_weight',//订单审核判断订单是否超重 --0

					'order_mark_color_weight_range',//订单审核超重范围 --0

					'stall_wholesale_mode',//开启档口模式 --0

                    'order_check_synchronous_logistics',//是否自动预物流同步 --0
            );
			$arr_cfg_def_val=array(0,0,0,0,0,0,0,0,1,0,0,0,0,0,'0',0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,640,0,0,0,0,0);
			$res_cfg_val=get_config_value($arr_cfg_key,$arr_cfg_def_val);//多个
			$res_cfg_val['order_check_warn_completed_min']=number_format(floatval($res_cfg_val['order_check_warn_completed_min']),4);
			$res_cfg_val['order_check_warn_unpay_min']=number_format(floatval($res_cfg_val['order_check_warn_unpay_min']),4);
			//------------------------------审核机制-----------------------------
			$is_rollback=true;			
			$trade_log=array();
			$check_info=array('快速审核','普通审核','强制审核','自动审核','type'=>array(12,9,10,9));

			foreach ($trades as $trade)
			{
				$solve_way=array(
						'purchase'=>'<a href="javascript:void(0)" onClick="tradeCheckOpenMenuBefore(\'采购开单\', \''.U('Purchase/PurchaseOrder/show').'\')">采购货品</a>',
						'transfer'=>'<a href="javascript:void(0)" onClick="tradeCheckOpenMenuBefore(\'调拨开单\', \''.U('Stock/StockTransfer/show').'\')">调拨货品</a>',
						'stock_in'=>'<a href="javascript:void(0)" onClick="tradeCheckOpenMenuBefore(\'入库开单\', \''.U('Stock/StockInOrder/show').'\')">入库开单</a>',
						'is_black'=>'到<a href="javascript:void(0)" onClick="tradeCheckOpenMenuBefore(\'客户档案\', \''.U('Customer/CustomerFile/getCustomerList').'?wangwang='.$trade['buyer_nick'].'\')">客户档案</a>编辑客户，取消黑名单',
						'clear_bad'=>'<a href="javascript:void(0)" onClick="tradeCheck.clearBadTrade('.$trade['trade_id'].')">清除异常</a>',
						'clear_revert'=>'<a href="javascript:void(0)" onClick="tradeCheck.clearRevertTrade('.$trade['trade_id'].')">清除驳回</a>',
						'unfreeze'=>'<a href="javascript:void(0)" onClick="tradeCheck.unfreezeTrade('.$trade['trade_id'].')">解冻</a>',
						'edit'=>'<a href="javascript:void(0)" onClick="tradeCheck.edit()">编辑</a>',
						'refund'=>'<a href="javascript:void(0)" onClick="tradeCheck.clearBadTrade('.$trade['trade_id'].')">清除退款异常</a>，线上同意退款',
						'cod'=>'修改物流公司',
						'invalid_goods'=>'<a href="javascript:void(0)" onClick="tradeCheck.invalidGoods()">匹配未匹配货品</a>',
						'merge'=>'按照昵称或地址搜出同名订单然后合并',
						'stalls_merge'=>'<a href="javascript:void(0)" onClick="tradeCheck.mergeAndCheckTrade('.$trade['trade_id'].')">一键合并且审核</a>',
						'split'=>'拆分订单',
						'setting'=>'',
						'no_goods'=>'检查订单货品信息是否正确。',
						'status'=>'检查订单是否已被其他人审核。',
						'shop'=>'检查店铺信息是否正确。',
						'warehouse'=>'检查仓库信息是否正确。',
						'edit_goods'=>'修改货品信息。',
						'gift'=>'检查货品信息。',
						'logistics_shop'=>'检查店铺是否授权，物流是否已映射',
						'weight'=>'检查订单估重',
				);
				$force_check=array(
						'yes'=>'<a href="javascript:void(0)" onClick="checkByType(1,\'\','.$trade['trade_id'].')">是（点击强审）</a>',
						'no'=>'否',
				);
				if (empty($remain_info)) {				
					//------------------------订单相关信息校验-------------------------
					if(!$this->checkSalesTrade($trade,$list,$check_pace,$solve_way,$force_check))
					{
						continue;
					}

					//-------------------------根据审核类型进行校验------------------------
					if(!$this->checkTradeByType($trade,$res_cfg_val,$list,$check_type,$solve_way,$force_check))
					{
						continue;
					}
					$order_count = 0;
					$lower_costprice_count = 0;
					//----------------------------子订单校验------------------------
					if(!$this->checkSalesOrder($trade,$res_cfg_val,$list,$check_type,$order_count,$solve_way,$force_check,$lower_costprice_count))
					{
						continue;
					}

					//-------------------检查同名未合并的订单(强制审核跳过)--------------
					if(!$this->checkUnmergeTrade($trade,$res_cfg_val,$list,$check_type,$solve_way,$force_check))
					{
						continue;
					}
				}

				//-----------------------------财务审核-------------------------
				if(!$this->financialCheckTrade($trade,$res_cfg_val,$user_id,$check_type,$check_info,$success,$check_pace,$order_count,$lower_costprice_count))
				{
					$finacial_trade[$trade['trade_id']]=$trade['trade_no'];
					continue;
				}
				//------------------------销售积分计算 ---------------------
				$sql_error_info='sales_trade_order_query_sum-sales_score-'.$trade['trade_no'];
				$sales_score=$this->query("SELECT SUM(IFNULL(gs.sale_score,0)*sto.actual_num) AS total_sales_score FROM sales_trade_order sto LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id WHERE sto.trade_id =".$trade['trade_id']);
				
				//-----------------------订单全链路-------------------------
				$this->traceTrade($user_id,$trade['trade_id'],2,'',$trade['split_from_trade_id'],$res_cfg_val['sales_trade_trace_enable']);
				// ----------------------生成缺货明细-----------------------
				$box_no='';//分拣框传值
				if ($res_cfg_val['stall_wholesale_mode']!=0){
					$stall=$this->generateStallsLessGoodsDetail($user_id,$trade['trade_id'],$box_no,$remain_info);
					if ($stall['status']) {
						$box_no=$stall['box_no'];
						$trade_log[]=array(
								'trade_id'=>$trade['trade_id'],
								'operator_id'=>$user_id,
								'type'=>$check_info['type'][$check_type+1],
								'data'=>$check_type==2?1:$check_type+1,
								'message'=>$check_info[$check_type+1].'--'.$trade['trade_no'].'--生成档口缺货明细单',
								'created'=>date('y-m-d H:i:s',time())
						);
					}
				}

				//-----------------------生成出库单-------------------------
				$stock_out=$this->generateStockOut($user_id, $trade['trade_id'], $trade['warehouse_id'], 55,'',$box_no,$res_cfg_val['sales_raw_count_exclude_gift']);
				//------------------------完成审核--------------------------
				if(empty($stock_out['stockout_no']))
				{//----------------------驳回订单处理---------------
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$stock_out['result']);
					continue;
				}else
				{//---------------------生成新的出库单号--------------
					//订单状态变化
					if($check_pace==0){
						$sql_error_info='sales_trade_update-status';
						$update=$this->execute("UPDATE sales_trade SET trade_status=55,checker_id=".$user_id.",stockout_no='".$stock_out['stockout_no']."',checkouter_id=0,sales_score=".$sales_score[0]['total_sales_score'].",check_step=1,version_id=version_id+1 WHERE trade_id =".$trade['trade_id'].' AND version_id='.$trade['version_id']);
						if($update==0){
							SE('订单'.$trade['trade_no'].'被其他人审核或者修改，请重新选择订单进行审核');
						}
						//日志
						$trade_log[]=array(
								'trade_id'=>$trade['trade_id'],
								'operator_id'=>$user_id,
								'type'=>$check_info['type'][$check_type+1],
								'data'=>$check_type==2?1:$check_type+1,
								'message'=>$check_info[$check_type+1].'--'.$trade['trade_no'].'--完成审核',
								'created'=>date('y-m-d H:i:s',time())
						);
					}else if($check_pace==1){
						$sql_error_info='financial_check_update-status';
						$this->execute("UPDATE sales_trade SET trade_status=55,stockout_no='".$stock_out['stockout_no']."',checkouter_id=0,fchecker_id=".$user_id.",sales_score=".$sales_score[0]['total_sales_score'].",check_step=2,version_id=version_id+1 WHERE trade_id =".$trade['trade_id']);
						//日志
						$trade_log[]=array(
								'trade_id'=>$trade['trade_id'],
								'operator_id'=>$user_id,
								'type'=>45,
								'data'=>$check_type==2?1:$check_type+1,
								'message'=>$check_info[$check_type+1].'--'.$trade['trade_no'].'--财务审核',
								'created'=>date('y-m-d H:i:s',time())
						);
					}
					//-----------------自动获取电子面单-数据整理-----------------------------------
					if ($res_cfg_val['order_check_get_waybill']!=0&&$is_direct_consign==0)
					{
						$success[]=array('id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'stockout_id'=>$stock_out['stockout_id']);
						if(empty($stockout_orders[strval($trade['logistics_id'])]))
						{
							$stockout_orders[strval($trade['logistics_id'])]='';
						}
						$stockout_orders[strval($trade['logistics_id'])].=($stock_out['stockout_id'].',');
					}else{
						$success[]=array('id'=>$trade['trade_id']);
					}
                    //-----------------自动预物流同步---------------------------------------------
                    if ($res_cfg_val['order_check_synchronous_logistics']!=0)
                    {
                        if($trade['platform_id'] !=0){
                            if($stock_out['bill_type'] !=0){
                                $stockout_sync[] = $stock_out['stockout_id'];
                            }
                        }
                    }

                }
			}
			//-----------------添加日志信息-------------------------------
			D('Trade/SalesTradeLog')->addTradeLog($trade_log);
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e)
		{
			SE($e->getMessage());
		}
	}

	/**
	 *订单检验
	 *@param trade array
	 *@param list array
	 *@param $check_pace 0--订单审核 1--财务审核
	 */
	private function checkSalesTrade($trade,&$list,$check_pace,$solve_way,$force_check)
	{
		$check=true;
		try
		{
			if($trade['trade_id']<=0)
			{
				$list[]=array('trade_no'=>'','result_info'=>'审核失败：订单不存在');
				return false;
			}
			if(($trade['trade_status']!=30&&$check_pace==0)||($trade['trade_status']!=35&&$check_pace==1))
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单状态不正确','solve_way'=>$solve_way['status'],'force_check'=>$force_check['no']);
				return false;
			}
			if($trade['shop_id']<=0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：无效店铺','solve_way'=>$solve_way['shop'],'force_check'=>$force_check['no']);
				$check=false;
			}
			if($trade['warehouse_id']<=0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：无效仓库','solve_way'=>$solve_way['warehouse'],'force_check'=>$force_check['no']);
				$check=false;
			}
			if($trade['warehouse_type']<0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：仓库类型不确定','solve_way'=>$solve_way['warehouse'],'force_check'=>$force_check['no']);
				$check=false;
			}
			if(empty($trade['receiver_name']))
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：收件人不能为空','solve_way'=>$solve_way[''],'force_check'=>$force_check['no']);
				$check=false;
			}

			if(empty($trade['receiver_area'])||empty($trade['receiver_address']))
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：收件地址不能为空','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['no']);
				$check=false;
			}
			if ($trade['delivery_term']==1 && $trade['trade_status']<16 && $trade['trade_status']>5)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：款到发货订单，未付款');
				return false;
			}
			/* if($trade['warehouse_type']>1)
			 {//验证外部存储
				 $sql_error_info='sys_warehouse_query_error-'.$trade['trade_no'];
				 $warehouse=M('cfg_warehouse')->field('is_outer_stock')->where('warehouse_id='.$trade['warehouse_id'])->find();
				 if(empty($warehouse))
				 {
					 $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：无效仓库');
					 $check=false;
				 }
			 } */
			if($trade['freeze_reason']!=0)
			{
				$sql_error_info='cfg_oper_reason_query_error-freeze-'.$trade['trade_no'];
				$reason=M('cfg_oper_reason')->field('title')->where(array('reason_id'=>array('eq',$trade['freeze_reason'])))->find();
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单冻结-'.$reason['title'],'solve_way'=>$solve_way['unfreeze'],'force_check'=>$force_check['no']);
				return false;
			}
			if($trade['refund_status']!=0)
			{//
				$sql_error_info='sales_trade_order_refund_query_error-'.$trade['trade_no'];
				$count_refund=$this->query("SELECT COUNT(refund_status) AS count_refund FROM sales_trade_order WHERE trade_id = %d AND refund_status = 2",array($trade['trade_id']));
				if($count_refund[0]['count_refund']>0)
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：有申请退款货品','solve_way'=>$solve_way['refund'],'force_check'=>$force_check['no']);
					return false;
				}
			}
			if($trade['delivery_term']==2&&$trade['logistics_id']!=0&&$trade['is_support_cod']==0)
			{
				//$sql_error_info='cfg_logistics_query_error-'.$trade['trade_no'];
				//$logistics=M('cfg_logistics')->field('is_support_cod')->where(array('logistics_id'=>array('eq',$trade['logistics_id'])))->find();
				//if($logistics['is_support_cod']==0)
				//{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：物流公司不支持货到付款','solve_way'=>$solve_way['cod'],'force_check'=>$force_check['no']);
					return false;
				//}
			}
			if($trade['revert_reason']!=0)
			{
				$sql_error_info='cfg_oper_reason_query_error-revert-'.$trade['trade_no'];
				$reason=M('cfg_oper_reason')->field('title')->where(array('reason_id'=>array('eq',$trade['revert_reason'])))->find();
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单驳回，'.(empty($reason['title'])?'驳回原因未知':$reason['title']),'solve_way'=>$solve_way['clear_revert'],'force_check'=>$force_check['no']);
				return false;
			}
			if($trade['refund_status']==1)//$trade['refund_status']!=0
			{
				//$sql_error_info='sales_trade_order_query_error-refund-'.$trade['trade_no'];
				//$refund_orders=M('sales_trade_order')->field('COUNT(rec_id) AS total')->where(array('trade_id'=>array('eq',$trade['trade_id']),'refund_status'=>array('eq',2)))->select();
				//if($refund_orders[0]['tatol']>0)
				//{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：申请退款','solve_way'=>$solve_way['refund'],'force_check'=>$force_check['no']);
					return false;
				//}
			}
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $check;
	}

	/**
	 *审核类型检验
	 *@param type -1-快速审核 0-普通审核 1-强制审核
	 *@param trade array
	 *@param config array
	 *@param list array
 	 */
	private function checkTradeByType($trade,$config,&$list,$type=0,$solve_way,$force_check)
	{
		$check=true;
		if($type==1) return $check;
		try
		{
			//检查订单对应的多个订单的收货地址是否相同，如果配置未开启则不检查
			if($config['order_check_api_trade_address_unequal'] && strpos($trade['src_tids'],',')!==false)
			{
				$sql_error_info='sales_trade_order_query_error-receiver_hash-'.$trade['trade_no'];
				$count_receiver=$this->query('SELECT COUNT(DISTINCT att.receiver_hash) AS receiver_hash_num FROM sales_trade_order sto LEFT JOIN api_trade att ON sto.src_tid = att.tid AND sto.shop_id = att.shop_id WHERE  sto.trade_id = %d AND sto.platform_id > 0',array($trade['trade_id']));
				if(intval($count_receiver[0]['receiver_hash_num'])>1)
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：该订单对应的多个原始单的收货人信息不同','solve_way'=>$solve_way['split'],'force_check'=>$force_check['yes']);
					$check=false;
				}
			}
			if ($config['order_mark_color_by_weight']==1&&$trade['weight']>$config['order_mark_color_weight_range']) {
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单估重超过指定范围','solve_way'=>$solve_way['weight'],'force_check'=>$force_check['yes']);
				$check=false;
			}
			if($config['order_check_black_customer']!=0)
			{
				$sql_error_info='crm_platform_customer_query_error-is_black-'.$trade['trade_no'];
				$is_black=$this->query("SELECT 1 FROM crm_platform_customer cpc left join crm_customer cc ON cpc.customer_id = cc.customer_id WHERE cpc.account='%s' AND cc.is_black>0 LIMIT 1",array($trade['buyer_nick']));
				if($is_black[0]['1']==1)
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：客户“'.$trade['buyer_nick'].'”在黑名单中','solve_way'=>$solve_way['is_black'],'force_check'=>$force_check['yes']);
					$check=false;
				}
			}
			if(empty($trade['receiver_mobile'])&&empty($trade['receiver_telno']))
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：收件人手机、电话全为空','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
				$check=false;
			}
			if($trade['logistics_id']<=0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：无效的物流方式','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
				$check=false;
			}
			if($trade['trade_type']==1&&$trade['customer_id']<=0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：无效的客户','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
				$check=false;
			}
			if($trade['bad_reason']!=0)
			{//异常订单(bit位)，1无库存记录 2地址发生变化 4发票变化 8仓库变化16备注变化32平台更换货品64退款128拦截赠品
				$intercepts=array('1'=>'无货品记录','2'=>'修改地址','4'=>'修改发票','8'=>'更换仓库','16'=>'修改备注','32'=>'更换货品','64'=>'退款','128'=>'拦截赠品');
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单异常-'.$intercepts[''.$trade['bad_reason']],'solve_way'=>$solve_way['clear_bad'],'force_check'=>$force_check['yes']);
				$check=false;
			}
			/* if($config['order_edit_must_checkout']!=0&&$trade['checkouter_id']==0)
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单必须签出');
				$check=false;
			}else if($trade['checkouter_id']>0&&$trade['checkouter_id']!=$user_id)//1当前操作员工的id
			{
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：已被其他用户签出');
				$check=false;
			} */
			if($trade['trade_type']==3){
				$sales_refund=D('SalesRefund')->query('SELECT sr.process_status FROM sales_refund sr
								INNER JOIN api_trade ap ON sr.swap_trade_id=ap.rec_id
								INNER JOIN sales_trade_order sto ON ap.tid=sto.src_tid AND sto.trade_id='.$trade['trade_id'].'
								WHERE sr.customer_id='.$trade['customer_id']);
				if($sales_refund[0]['process_status']<90){
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：该换货销售单所对应的退货单未入库。','solve_way'=>$solve_way['stock_in'],'force_check'=>$force_check['yes']);
					$check=false;
				}
			}
			if($trade['trade_from']==1||$trade['trade_from']==3){
				$sql_error_info='get_api_trade_refund_status';
				$api_trade=$this->query('SELECT ap.refund_status,ap.tid from api_trade ap LEFT JOIN sales_trade_order sto ON sto.src_tid=ap.tid WHERE sto.trade_id='.$trade['trade_id'].' GROUP BY ap.tid');
				foreach ($api_trade as $k=>$v){
					if($v['refund_status']>0){
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'该订单对应的原始单'.$v['tid'].'已退款或申请退款。','solve_way'=>$solve_way['api_refund'],'force_check'=>$force_check['yes']);
						$check=false;
					}
				}
			}
			if($config['order_check_address_reachable']==1){
				$sql_error_info="get_logistics_partner_id";
				$partner_id=$this->query("SELECT cls.logistics_id,cls.logistics_type FROM cfg_logistics_shop cls
						LEFT JOIN cfg_logistics cl ON cls.logistics_type=cl.logistics_type AND cls.shop_id=".$trade['shop_id']." 
						WHERE cl.logistics_id=".$trade['logistics_id']);
				$logistics_id = $partner_id[0]['logistics_id'];
				$logistics_type = $partner_id[0]['logistics_type'];
				if(empty($logistics_type))
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'该物流对应的线上物流编号为空,请检查系统物流公司是否与平台物流匹配','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
					$check=false;
				}else{
					if(empty($logistics_id)){
						//非淘宝平台logistics_id都为空,这里去查询对应淘宝店铺映射物流的logistics_id
						$top_shop = $this->query("SELECT shop_id FROM cfg_shop WHERE platform_id =1");
						if(empty($top_shop))
						{
							$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'该物流对应的线上物流编号为空,请检查系统物流公司是否与平台物流匹配','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
							$check=false;
						}else{
							$top_logistics_sql = "SELECT cls.logistics_id FROM cfg_logistics_shop cls
									   				  WHERE cls.logistics_type = %d AND cls.shop_id = %d";
							foreach($top_shop as $v)
							{
								$top_logistics = $this->query($top_logistics_sql,$logistics_type,$v['shop_id']);
								if(!empty($top_logistics[0]['logistics_id']))
								{
									$logistics_id = $top_logistics[0]['logistics_id'];
									break;
								}
							}
						}
					}

					if(empty($logistics_id))
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'该物流对应的线上物流编号为空,,请检查系统物流公司是否与平台物流匹配','solve_way'=>$solve_way['edit'],'force_check'=>$force_check['yes']);
						$check=false;
					}else
					{
						$logistics_manager=ManagerFactory::getManager('Logistics');
						$error_msg='';
						$reachable=$logistics_manager->top_address_reachable($logistics_id,$trade['receiver_area'],$error_msg);
						if(!empty($error_msg)){
							$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'判断物流是否可达失败，'.$error_msg,'force_check'=>$force_check['yes']);
							$check=false;
						}else if($reachable!=1){
							$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'订单所选物流不能到达订单收件地址','solve_way'=>$solve_way['edit']."，修改物流",'force_check'=>$force_check['yes']);
							$check=false;
						}
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

	/**
	 *财务审核
	 *@param trade array
	 *@param config array
	 *@param user_id 当前操作员工id
	 *@param check_type -1-快速审核 0-普通审核 1-强制审核
	 *@param check_info 审核类型(记录日志)
	 *@param success 成功的数据
	 *@param check_pace 0--订单审核 1--财务审核
	*/
	private function financialCheckTrade($trade,$config,$user_id,$check_type,$check_info,&$success,$check_pace,$order_count,$lower_costprice_count)
	{
		$check=true;
		if($check_pace==1){return $check;}
		//是否开启财务审核
		if($config['order_fa_condition'])
		{
			try
			{	
				$messages=[];
				//优惠金额达到**元
				if($config['order_fc_discount']!=0&&$trade['discount']>=$config['order_fc_discount'])
				{
					$messages[]='进财审原因:优惠金额达到'.$config['order_fc_discount'].'元';
					$messages[]=8;
				}
				//订单应收金额高于**元进财审
				if($config['order_fc_receivable_outnumber']!=0&&$trade['receivable']>$config['order_fc_receivable_outnumber'])
				{
					$messages[]='进财审原因:订单金额高于'.$config['order_fc_receivable_outnumber'].'元';
					$messages[]=6;
				}
				//是否开启EXCEL导入订单
				if($config['order_fc_excel_import']==1&&$trade['trade_from']==3)
				{
					$messages[]='进财审原因:EXCEL导入';
					$messages[]=9;
				}
				//是否开启手工建单
				if($config['order_fc_man_order']==1&&$trade['trade_from']==2)
				{
					$messages[]='进财审原因:手工建单';
					$messages[]=4;
				}
				if($config['order_fc_gift']==1&&$order_count==0){
					$messages[]='进财审原因:订单全是赠品';
					$messages[]=10;
				}
				//订单中商品成交金额低于成本价进财审
				if($config['order_fc_below_costprice']==1&&$lower_costprice_count!=0){
					$messages[]='进财审原因:订单中商品成交金额低于成本价';
					$messages[]=11;
				}
				if(!empty($messages))
				{
					//订单状态变化
					$sql_error_info='financial_check_update-status';
					$this->execute("UPDATE sales_trade SET trade_status=35,checker_id=".$user_id.",checkouter_id=0,check_step=1,version_id=version_id+1 WHERE trade_id =".$trade['trade_id']);
					//日志
					for($i=0;$i<count($messages);$i=$i+2)
					{
						$trade_log[]=array(
							'trade_id'=>$trade['trade_id'],
							'operator_id'=>$user_id,
							'type'=>46,
							'data'=>$messages[$i+1],
							'message'=>$check_info[$check_type+1].'--'.$messages[$i],
							'created'=>date('y-m-d H:i:s',time())
						);
					}
					//-----------------添加日志信息-------------------------------
					D('Trade/SalesTradeLog')->addTradeLog($trade_log);
					$success[]=array('id'=>$trade['trade_id']);
					$check=false;
				}	
			}catch (\PDOException $e)
			{
				\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
				SE(self::PDO_ERROR);
			}
		}
		return $check;
	}

	/**
	 *子订单检验
	 *@param trade array
	 *@param config array
	 *@param list array
	 *@param type -1-快速审核 0-普通审核 1-强制审核
	 */
	private function checkSalesOrder($trade,$config,&$list,$type=0,&$order_count,$solve_way,$force_check,&$lower_costprice_count)
	{
		$check=true;
		try
		{
			/**
			 *sales_trade_order_query_sql使用外部仓库 不考虑    IF(".$trade['is'].",IFNULL(ss.stock_num+ss.wms_stock_diff,0),IFNULL(ss.stock_num,0))
			 *sales_trade_order_query_sql暂时不考虑了采购到货数量  IFNULL(ss.purchase_arrive_num,0)
			 *$orders=$this->query("SELECT COUNT(1) AS orders_num,sto.spec_id,sto.gift_type,sto.num,sto.price,sto.actual_num,sto.order_price,sto.share_price,sto.discount, sto.share_amount,sto.share_post,sto.goods_name,sto.spec_no,sto.spec_name,IFNULL(ss.stock_num,0),IFNULL(ss.purchase_arrive_num,0), IFNULL(ss.sending_num,0),IFNULL(ss.lock_num,0),gs.lowest_price,IFNULL(ss.cost_price,0),gs.is_allow_lower_cost,gs.is_allow_neg_stock,IFNULL(ss.to_transfer_num,0) FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id  AND ss.warehouse_id = ".$trade['warehouse_id']." LEFT JOIN  goods_spec gs ON gs.spec_id = sto.spec_id WHERE sto.trade_id = ".$trade['trade_id']." AND sto.actual_num>0"); 
			 */
			$sql_error_info='sales_trade_order_query_error-query_detail-'.$trade['trade_no'];			
			// 订单审核时判断库存与可发库存配置无关，按照实际库存-待发货量计算
			// $available_stock=D('Stock/StockSpec')->getAvailableStrBySetting($config['sys_available_stock']);
			$count_order=$this->query('SELECT COUNT(1) AS orders_num FROM sales_trade_order WHERE trade_id=%d AND actual_num>0',array($trade['trade_id']));
			$orders=$this->query(
					"SELECT sto.spec_id,sto.gift_type,sto.num,sto.price,sto.actual_num,
					 sto.order_price,sto.share_price,sto.discount, sto.share_amount,sto.share_post,sto.goods_name,
					 sto.spec_no,sto.spec_name,sto.is_consigned,IFNULL(ss.stock_num,0) stock_num,IFNULL(ss.purchase_arrive_num,0) purchase_arrive_num, IFNULL(ss.sending_num,0) sending_num,
					 IFNULL(ss.lock_num,0) lock_num,gs.lowest_price,IFNULL(ss.cost_price,0) cost_price,gs.is_allow_lower_cost,gs.is_allow_neg_stock,(ss.stock_num-ss.sending_num) available_stock 
					 FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id  AND ss.warehouse_id = %d 
					 LEFT JOIN  goods_spec gs ON gs.spec_id = sto.spec_id WHERE sto.trade_id = %d AND sto.actual_num>0",
					array($trade['warehouse_id'],$trade['trade_id'])
			);
			if(empty($orders))
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单无货品','solve_way'=>$solve_way['no_goods'],'force_check'=>$force_check['no']);
				return false;
			}
			$all_cost_price=0;
			$order_count=0;
			$lower_costprice_count=0;
			foreach ($orders as $order)
			{
				$order_count+=$order['gift_type']==0?1:0;
				//非赠品的商品，成交价低于成本价时计入
				if($order['gift_type']==0 && $order['share_price']<$order['cost_price']){
					$lower_costprice_count+=1;
				}
				$order['orders_num']=intval($count_order[0]['orders_num']);
				if ($order['spec_id']==0 && empty($order['$order']))
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：未匹配货品-'.$order['goods_name'].'，规格-'.$order['spec_name'],'solve_way'=>$solve_way['invalid_goods'],'force_check'=>$force_check['no']);
					$check=false;
				}
				if ($order['is_consigned']==1 && $type!=1)
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'子订单异常：平台已发货','solve_way'=>'清除订单','force_check'=>$force_check['yes']);
					$check=false;
				}
				//--------------------赠品是否计入实收金额------------
				if($config['order_fc_receivable_under_goodscost']!=0 && $config['order_fa_condition']==2)
				{
					switch (intval($config['order_fc_receivable_under_goodscost_type']))
					{
						case 0:
							$all_cost_price+=$order['cost_price'];
							break;
						case 1:
							if($order['gift_type']!=2)
							{
								$all_cost_price+=$order['cost_price'];
							}
							break;
						case 2:
							if($order['gift_type']!=1)
							{
								$all_cost_price+=$order['cost_price'];
							}
							break;
						case 3:
							if($order['gift_type']==0)
							{
								$all_cost_price+=$order['cost_price'];
							}
							break;
						default:
							$all_cost_price+=$order['cost_price'];
							break;
					}
				}
				//-----------库存判断-强制审核且允许负库存出库时不检查库存------------
				if($type!=1&&($config['order_check_no_stock']!=0 && $order['is_allow_neg_stock']==0))
				{
					$actual_num=$order['actual_num'];
					if($order['orders_num']>$trade['goods_type_count'])
					{
						$sql_error_info='sales_trade_order_query-getSUM(actual_num)-'.$trade['trade_no'];
						$tmp_order=$this->query("SELECT SUM(actual_num) AS actual_sum FROM sales_trade_order WHERE trade_id = ".$trade['trade_id']." AND spec_id = ".$order['spec_id']." AND actual_num >0");
						$actual_num=empty($tmp_order)?$actual_num:$tmp_order[0]['actual_sum'];
					}
					//$order['stock_num']=$order['stock_num']-$order['sending_num']-$order['lock_num'];
					$order['stock_num']=$order['stock_num']-$order['sending_num'];//减去待发货量
					/* if($config['order_check_stock_add_purchase']==1)
					{//加上采购到货量
						$order['stock_num']+=$order['purchase_arrive_num'];
					}
					if($config['order_check_stock_sub_to_transfer'])
					{//计算库存不足扣减待调拨量
					 	$trade['stock_num']-=$order['to_transfer_num'];
					} */
					// 计算库存与可发库存的配置无关
					// if($config['sys_available_stock']&1<<7&&$order['available_stock']<0){
					// 	$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>($config['order_check_no_stock']!=0)?'库存不足:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name']:'不允许负库存:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name'],'solve_way'=>$solve_way['purchase'].' 或 '.$solve_way['transfer'],'force_check'=>$force_check['yes']);
					// 	$check=false;
					// }else if(!($config['sys_available_stock']&1<<7)&&$actual_num>$order['available_stock']){
					// 	$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>($config['order_check_no_stock']!=0)?'库存不足:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name']:'不允许负库存:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name'],'solve_way'=>$solve_way['purchase'].' 或 '.$solve_way['transfer'],'force_check'=>$force_check['yes']);
					// 	$check=false;
					// }
					if($order['available_stock']<0||$actual_num>$order['available_stock'])
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>($config['order_check_no_stock']!=0)?'库存不足:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name']:'不允许负库存:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name'],'solve_way'=>$solve_way['purchase'].' 或 '.$solve_way['transfer'],'force_check'=>$force_check['yes']);
						$check=false;
					}
				}
				//-----------判断是否低于最低售价---------------------------------
				if($order['share_price']<$order['lowest_price'])
				{
					if($type!=1 && $config['order_check_below_lowest_price']==1 && $order['gift_type']==0)
					{//强制审核--售价是否低于最低售价，如果是赠品则跳过检查
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'低于最低售价:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name'].'  分摊后价格-'.$order['share_price'].' 最低价-'.$order['lowest_price'],'solve_way'=>$solve_way['edit_goods'],'force_check'=>$force_check['yes']);
						$check=false;
					}
					/*是否进财务审核-现在暂不进入财审*/
					/* if($config['order_fa_condition']==2 && $config['order_fc_receivable_under_goodscost']!=0 && $trade['receivable']<$all_cost_price)
					 {
						 $gift_info=array('(包含赠品)','(仅包含自动赠送)','(仅包含手工赠送)','(不包含赠品)');
						 $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'财审信息:订单实收低于货品成本,实收-'.$all_cost_price.' 货品成本-'.$gift_info[$config['order_fc_receivable_under_goodscost_type']]);
						 $check=false;
					 } */
				}
				//-------------实际收入成本---------------------
				if($order['share_price']<$order['cost_price'] && $order['is_allow_lower_cost']==0)
				{
					if($type!=1 && $config['order_check_below_cost']==1 && $order['gift_type']==0)
					{//强制审核--售价是否低于成本价，如果是赠品则跳过检查
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'低于成本价:'.'单品-'.$order['spec_no'].' 名称-'.$order['goods_name'].' 规格-'.$order['spec_name'].'  分摊后价格-'.$order['share_price'].' 成本价-'.$order['cost_price'],'solve_way'=>$solve_way['edit_goods'],'force_check'=>$force_check['yes']);
						$check=false;
					}
					/*是否进财务审核-现在暂不进入财审*/
					/* if($config['order_fa_condition']==2 && $config['order_fc_receivable_under_goodscost']!=0 && $trade['receivable']<$all_cost_price)
					 {
						 $gift_info=array('(包含赠品)','(仅包含自动赠送)','(仅包含手工赠送)','(不包含赠品)');
						 $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'财审信息:订单实收低于货品成本,实收-'.$all_cost_price.' 货品成本-'.$gift_info[$config['order_fc_receivable_under_goodscost_type']]);
					 	 $check=false;
					 } */
				}
			}
			if($type!=1&&$order_count==0)
			{
				$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'审核失败：订单全是赠品','solve_way'=>$solve_way['gift'],'force_check'=>$force_check['yes']);
				$check=false;
			}
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (Exception $e){
			SE($e->getMessage());
		}
		return $check;
	}

	/**
	 *同名未合并订单校验
	 *@param trade array
	 *@param config array
	 *@param list array
	 *@param type -1-快速审核 0-普通审核 1-强制审核
	 */
	private function checkUnmergeTrade($trade,$config,&$list,$type=0,$solve_way,$force_check)
	{
		$check=true;
		try
		{
			$end_status=0;
			$sum_unmerge_num=0;
			if($type!=1 && $config['order_check_warn_has_unmerge']!=0 && $trade['unmerge_mask']!=0 && !empty($trade['buyer_nick']) && $trade['split_from_trade_id']<=0 && $trade['freeze_reason']==0 && $trade['delivery_term']==1 && $trade['is_sealed']==0)
			{
				if($config['order_check_warn_has_unmerge_checked']==1)
				{
					$end_status=95;
				}else
				{
					$end_status=55;
				}
				$solve=$solve_way['merge'];
				if ($config['stall_wholesale_mode']==1) {//档口模式下
					$solve=$solve_way['stalls_merge'];
				}
				//暂时考虑预订单-后期再确定修改
				//跨店铺合并
				$sql_error_info='sales_trade_query_cross_shop-getSUM(trade_num)-'.$trade['trade_no'];
				$count_unmerge_trade=$this->query(
						"SELECT COUNT(1) AS trade_num FROM sales_trade WHERE customer_id = ".$trade['customer_id']." AND buyer_nick='".addslashes($trade['buyer_nick'])."' 
						 AND IF(".$config['order_check_merge_warn_mode']."=2 OR ".$config['order_check_merge_warn_mode']." = 3,TRUE,
						 receiver_name = '".addslashes($trade['receiver_name'])."') AND receiver_area = '".$trade['receiver_area']."' AND receiver_address = '".addslashes($trade['receiver_address'])."' 
						 AND trade_status>=15 AND trade_status<".$end_status." AND IF(".$config['order_check_warn_has_unmerge_preorder'].",1,trade_status<>25) 
						 AND split_from_trade_id <=0 AND freeze_reason=0 AND delivery_term=1 AND is_sealed = 0 AND IF(".$config['order_check_merge_warn_mode']."=1 
						 OR ".$config['order_check_merge_warn_mode']." = 2,shop_id=".$trade['shop_id'].",TRUE)"
				);
				if($count_unmerge_trade[0]['trade_num']>1)
				{
					$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'存在'.$count_unmerge_trade[0]['trade_num'].'未合并订单'.($config['order_check_warn_has_unmerge_checked']?'，包含已审核订单':'，不包含已审核订单'),'solve_way'=>$solve,'force_check'=>$force_check['yes']);
					$sum_unmerge_num+=$count_unmerge_trade[0]['trade_num'];
				}
				//未合并订单-冻结
				if($config['order_check_warn_has_unmerge_freeze']!=0)
				{
					$sql_error_info='sales_trade_query_unmerge_freeze-getSUM(trade_num)-'.$trade['trade_no'];
					$count_unmerge_trade=$this->query(
							"SELECT COUNT(1) AS trade_num FROM sales_trade WHERE customer_id = ".$trade['customer_id']." AND buyer_nick='".addslashes($trade['buyer_nick'])."' 
							 AND trade_status>=15 AND trade_status < 55 AND IF(".$config['order_check_warn_has_unmerge_preorder'].",1,trade_status<>25) 
							 AND split_from_trade_id <=0 AND freeze_reason > 0 AND delivery_term = 1 AND is_sealed = 0 AND IF(".$config['order_check_merge_warn_mode']."=1 
							 OR ".$config['order_check_merge_warn_mode']." = 2,shop_id=".$trade['shop_id'].",TRUE) AND IF(".$config['order_check_merge_warn_mode']."=2 
							 OR ".$config['order_check_merge_warn_mode']." = 3,TRUE,receiver_name = '".addslashes($trade['receiver_name'])."') 
							 AND IF(".$config['order_check_warn_has_unmerge_address'].",1,receiver_area = '".$trade['receiver_area']."' 
							 AND receiver_address='".addslashes($trade['receiver_address'])."')"
					);
					if($count_unmerge_trade[0]['trade_num']>0)
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'存在'.$count_unmerge_trade[0]['trade_num'].'条已冻结的同名未合并的订单','solve_way'=>$solve_way['merge'],'force_check'=>$force_check['yes']);
						$sum_unmerge_num+=$count_unmerge_trade[0]['trade_num'];
					}
				}
				//地址不同同名未合并
				if($config['order_check_warn_has_unmerge_address']!=0)
				{
					$sql_error_info='sales_trade_query_unmerge_address-getSUM(trade_num)-'.$trade['trade_no'];
					$count_unmerge_trade=$this->query(
							"SELECT COUNT(1) AS trade_num FROM sales_trade WHERE customer_id = ".$trade['customer_id']." AND buyer_nick='".addslashes($trade['buyer_nick'])."' 
							 AND trade_status>=15 AND trade_status < ".$end_status." AND IF(".$config['order_check_warn_has_unmerge_preorder'].",1,trade_status<>25) 
							 AND split_from_trade_id <=0 AND delivery_term = 1 AND is_sealed = 0 AND IF(".$config['order_check_merge_warn_mode']."=1 
							 OR ".$config['order_check_merge_warn_mode']."= 2,shop_id=".$trade['shop_id'].",TRUE) AND IF(".$config['order_check_merge_warn_mode']."=2 
							 OR ".$config['order_check_merge_warn_mode']."= 3,TRUE,receiver_name = '".addslashes($trade['receiver_name'])."') 
							 AND (receiver_area <> '".$trade['receiver_area']."' OR receiver_address <> '".addslashes($trade['receiver_address'])."')"
					);
					if($count_unmerge_trade[0]['trade_num']>0)
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'存在'.$count_unmerge_trade[0]['trade_num'].'条地址不同的同名未合并的订单'.($config['order_check_warn_has_unmerge_checked']?'，包含已审核订单':'，不包含已审核订单'),'solve_way'=>$solve,'force_check'=>$force_check['yes']);
						$sum_unmerge_num+=$count_unmerge_trade[0]['trade_num'];
					}
				}
				//已发货同名未合并
				if($config['order_check_warn_has_unmerge_completed']!=0)
				{
					$sql_error_info='sales_trade_query_unmerge_completed-getSUM(trade_num)-'.$trade['trade_no'];
					$count_unmerge_trade=$this->query(
							"SELECT COUNT(1) AS trade_num FROM sales_trade st,stockout_order so WHERE st.customer_id = ".$trade['customer_id']." AND st.buyer_nick='".addslashes($trade['buyer_nick'])."' 
							 AND st.trade_status=95 AND st.delivery_term=1 AND st.is_sealed = 0 AND st.split_from_trade_id <=0 AND so.src_order_id=st.trade_id AND so.src_order_type=1 
							 AND IF(".$config['order_check_merge_warn_mode']."=1 OR ".$config['order_check_merge_warn_mode']."= 2,st.shop_id=".$trade['shop_id'].",TRUE) 
							 AND IF(".$config['order_check_merge_warn_mode']."=2 OR ".$config['order_check_merge_warn_mode']."= 3,TRUE,st.receiver_name = '".addslashes($trade['receiver_name'])."') 
							 AND IF(".$config['order_check_warn_has_unmerge_address'].",1,st.receiver_area='".addslashes($trade['receiver_name'])."' AND st.receiver_address='".addslashes($trade['receiver_address'])."') 
							 AND IF(".$config['order_check_warn_completed_min'].">0,TIMESTAMPDIFF(MINUTE,so.consign_time,NOW())<= ".$config['order_check_warn_completed_min'].",TRUE)"
					);
					if($count_unmerge_trade[0]['trade_num']>0)
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'存在'.$count_unmerge_trade[0]['trade_num'].'条已发货的同名未合并的订单','solve_way'=>$solve_way['merge'],'force_check'=>$force_check['yes']);
						$sum_unmerge_num+=$count_unmerge_trade[0]['trade_num'];
					}
				}
				//有未付款的
				if($config['order_check_warn_has_unpay']!=0)
				{
					$sql_error_info='sales_trade_query_unmerge_unpay-getSUM(trade_num)-'.$trade['trade_no'];
					$count_unmerge_trade=$this->query(
							"SELECT MIN(trade_time) AS trade_time FROM sales_trade WHERE customer_id=".$trade['customer_id']." AND trade_status=10 
							 AND buyer_nick='".addslashes($trade['buyer_nick'])."' AND IF(".$config['order_check_merge_warn_mode']."=1 
							 OR ".$config['order_check_merge_warn_mode']."=2,shop_id=".$trade['shop_id'].",TRUE) AND IF(".$config['order_check_merge_warn_mode']."=2 
							 OR ".$config['order_check_merge_warn_mode']."=3,TRUE,receiver_name = '".addslashes($trade['receiver_name'])."') 
							 AND IF(".$config['order_check_warn_has_unmerge_address'].",1,receiver_area = '".$trade['receiver_area']."' 
							 AND receiver_address='".addslashes($trade['receiver_address'])."')"
					);
					if(!empty($count_unmerge_trade[0]['trade_time']) && ($config['order_check_warn_unpay_min']==0 || (time()-strtotime($trade['trade_time'])/60<$config['order_check_warn_unpay_min'])))
					{
						$list[]=array('trade_id'=>$trade['trade_id'],'trade_no'=>$trade['trade_no'],'result_info'=>'有未付款同名未合并订单','solve_way'=>$solve_way['merge'],'force_check'=>$force_check['yes']);
						$sum_unmerge_num+=1;
					}
				}
			}
			$check=$sum_unmerge_num>0?false:$check;
		}catch (\PDOException $e)
		{
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $check;
	}

	/**
	 *审核自动获取电子面单
	 *@param trade array
	 *@param config array
	 *@param list array
	 *@param type -1-快速审核 0-普通审核 1-强制审核
	 */
	private function getWayBill($stockout_orders,&$success,&$list)
	{
		$way_bill_manager=ManagerFactory::getManager('WayBill');
		$new_way_bill_manager=ManagerFactory::getManager('NewWayBill');
		$list_bill=array();
		$db_logistics=M('cfg_logistics');
		foreach ($stockout_orders as $k=>$v)
		{
			$logistics=$db_logistics->field('bill_type')->where(array('logistics_id'=>array('eq',$k)))->find();
			if($logistics['bill_type']==0)
			{
				continue;
			}
			$result=array();
			if($logistics['bill_type']==2){

                $result_info = array();
                $new_way_bill_manager->getTemplates($result_info,$k);
                $templateUrl = $result_info['success'][0]->standard_template_url;
                $packageNosArr = array();
                $conditions=array('type'=>'getWayBill','stockout_ids'=>substr($v,0,-1),'logistics_id'=>$k,'templateURL'=>$templateUrl,'packageNos'=>$packageNosArr);
                $new_way_bill_manager->manual($result,$conditions);

            }else{
                $conditions=array('type'=>'getWayBill','stockout_ids'=>substr($v,0,-1),'logistics_id'=>$k);
                $way_bill_manager->manual($result,$conditions);
            }
			if ($result['status']==1)
			{
				$stockout_ids=explode(',', substr($v,0,-1));
				foreach ($stockout_ids as $so_id)
				{
					$list_bill[strval($so_id)]=$result['msg'];
				}
			}else if($result['status']==2)
			{
				foreach ($result['data']['fail'] as $r)
				{
					$list_bill[strval($r['stock_id'])]=$r['msg'];
				}
			}
	 	}
	 	$i=0;
	 	foreach ($success as $s)
	 	{
	 		if(!empty($list_bill[$s['stockout_id']]))
	 		{
			 	$list[]=array('trade_no'=>$s['trade_no'],'result_info'=>'完成审核-获取电子面单失败：'.$list_bill[$s['stockout_id']]);
	 		}
	 		unset($success[$i]['trade_no']);
	 		unset($success[$i]['stockout_id']);
	 		$i++;
	 	}
	}

	// 自动预物流同步
    private function synchronousLogistics($stockout_sync,&$list){

        $error = array();
        foreach($stockout_sync as $v){
            $result = D('Stock/SalesStockOut')->synchronousLogistics($v,$error);
        }
        if(!empty($error)){
            foreach ($error as $k=>$v){
                $list[]=array('trade_no'=>$v['order_no'],'result_info'=>'完成审核-预物流同步失败：'.$v['msg']);
            }
        }
    }
	/**
	 * 查询--未匹配货品(递交过程产生的)
	 * @param unknown $where_str
	 * @param number $page
	 * @param number $rows
	 * @param unknown $search
	 * @param string $sort
	 * @param string $order
	 * @return Ambigous <multitype:number multitype: , multitype:number Ambigous <string, multitype:, \Think\mixed> >
	 */
	public function queryInvalidGoods(&$where_str,$page=1, $rows=20, $search = array(), $sort = 'ags.rec_id', $order = 'desc')
	{
		foreach ($search as $k=>$v){
			if($v==='') continue;
			switch ($k)
			{   
				case 'shop_id':
					set_search_form_value($where_str, $k, $v,'ato_1',2,' AND ');
					break;
				case 'tid':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
				case 'spec_code':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
				case 'goods_name':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
				case 'spec_name':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
				case 'goods_no':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
				case 'spec_no':
					set_search_form_value($where_str, $k, $v,'ato_1',1,' AND ');
					break;
			}
		}
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$order = $sort.' '.$order;//排序
		$where_str=substr($where_str.' AND ', 5);
		$sql_total='SELECT COUNT(distinct ags.rec_id)  AS total FROM api_trade_order ato_1 LEFT JOIN  api_trade at ON (at.tid=ato_1.tid and at.platform_id=ato_1.platform_id) LEFT JOIN  api_goods_spec ags ON (ags.shop_id=ato_1.shop_id AND ags.platform_id = ato_1.platform_id AND ags.goods_id=ato_1.goods_id AND ags.spec_id=ato_1.spec_id) WHERE '.$where_str.' ato_1.is_invalid_goods=1 AND at.process_status<20 AND ato_1.platform_id>0 AND ato_1.status <= 40 AND ags.is_deleted=0';
		$where_str='SELECT ato_1.rec_id AS ato_id,ags.rec_id AS ags_id FROM api_trade_order ato_1 LEFT JOIN  api_trade at ON (at.tid=ato_1.tid and at.platform_id=ato_1.platform_id) LEFT JOIN  api_goods_spec ags ON (ags.shop_id=ato_1.shop_id AND ags.platform_id = ato_1.platform_id AND ags.goods_id=ato_1.goods_id AND ags.spec_id=ato_1.spec_id) WHERE '.$where_str.' ato_1.is_invalid_goods=1 AND at.process_status<20 AND ato_1.platform_id>0 AND ato_1.status <= 40 AND ags.is_deleted=0 GROUP BY ags.rec_id ORDER BY '.$order.' LIMIT '.$limit;// GROUP BY ags.rec_id';
		$data=array();
		try {
			$sql_fields_str="SELECT IFNULL(ag.rec_id,0) AS ags_id,If(ag.match_target_id>0,'待递交 或 存在停用货品','未匹配') reason,ag.outer_id,ag.spec_outer_id,sh.shop_name AS shop_id,ato.rec_id AS id,ato.goods_id,ato.spec_id,ato.goods_no,ato.spec_no,ato.spec_code,ato.goods_name,ato.spec_name ";
			$sql=$sql_fields_str.'FROM api_trade_order ato INNER JOIN ('.$where_str.') ato_2 ON ato.rec_id=ato_2.ato_id LEFT JOIN api_goods_spec ag ON ato_2.ags_id=ag.rec_id LEFT JOIN cfg_shop sh ON sh.shop_id = ag.shop_id';
			$rec_total=$this->query($sql_total);
			$total=intval($rec_total[0]['total']);
			$list=$total?$this->query($sql):array();
			if(!empty($list))
			{//FN_SPEC_NO_CONV
				$res_cfg_val=get_config_value(array('sys_goods_match_concat_code','goods_match_split_char'),array(0,'_'));
				$length=count($list);
				$len_split_char=strlen($res_cfg_val['goods_match_split_char']);
				for ($i=0;$i<$length;$i++)
				{
					if($len_split_char>0)
					{
						if(($pos=strpos($list[$i]['outer_id'],$res_cfg_val['goods_match_split_char']))!==false)
						{
							$list[$i]['outer_id']=substr($list[$i]['outer_id'], 0,$pos);
						}
						if(($pos=strpos($list[$i]['spec_outer_id'],$res_cfg_val['goods_match_split_char']))!==false)
						{
							$list[$i]['spec_outer_id']=substr($list[$i]['spec_outer_id'], 0,$pos);
						}
						$list[$i]['merchant_no']=intval($res_cfg_val['sys_goods_match_concat_code'])?$list[$i]['outer_id'].$list[$i]['spec_outer_id']:(empty($list[$i]['spec_outer_id'])?$list[$i]['outer_id']:$list[$i]['spec_outer_id']);
						unset($list[$i]['outer_id']);
						unset($list[$i]['spec_outer_id']);
					}
				}
			}
			$data=array('total'=>$total,'rows'=>$list);
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}

	public function getFinancialCheckReason($id){
        $id=intval($id);
        $data=array();
        try{
            $sql="SELECT stl.operator_id,stl.message,stl.created,he.fullname AS checker FROM sales_trade_log stl LEFT JOIN hr_employee he ON he.employee_id=stl.operator_id WHERE stl.trade_id=".$id." AND stl.type=46 ORDER BY stl.created DESC";
            $list=$this->query($sql);
            $data=array('total'=>count($list),'rows'=>$list);
        }catch(\PDOException $e)
        {
            $data=array('total'=>0,'rows'=>array());
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }
    
    public function passelExchange($ids,$old_orders,$new_orders,$is_scale=0,$version,$user_id){
    	$list=array();
    	$result=array();
    	$sql_error_info='';
    	try{
    		//按订单号筛选出对应子订单
    		$sql_error_info='getSalesTradeOrderList';
    		$res_orders_arr=D('SalesTradeOrder')->getSalesTradeOrderList('sto.rec_id,sto.trade_id,sto.spec_id,sto.actual_num,st.trade_no',
    				array('sto.trade_id'=>array('in',$ids)),
    				'sto',
    				' LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id ');
    		$orders_by_trade=array();
    		foreach ($res_orders_arr as $v){
    			$orders_by_trade[$v['trade_id']][$v['spec_id']]=$v;
    			$orders_by_trade[$v['trade_id']]['trade_no']=$v['trade_no'];
    		}
    		foreach ($orders_by_trade as $k=>$v){
    			if(count($v)<count($old_orders)){//订单货品种类小于原货品种类
    				$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'订单中不包含符合条件的货品。');
    			}else{
    				$exchange_ids=array();
    				$scale=0;//按比例循环的话，记录比例
    				foreach ($old_orders as $old){
    					if($v[$old['spec_id']]){
    						if($is_scale){
    							if($v[$old['spec_id']]['actual_num']%$old['num']==0){
    								$scale==0?$scale=$v[$old['spec_id']]['actual_num']/$old['num']:true;
    								if($scale==$v[$old['spec_id']]['actual_num']/$old['num']){
    									$exchange_ids[]=$v[$old['spec_id']]['rec_id'];
    								}
    							}
    						}else{
    							if($v[$old['spec_id']]['actual_num']==$old['num']){
    								$exchange_ids[]=$v[$old['spec_id']]['rec_id'];
    							}
    						}
    					}
    				}
    				if(count($exchange_ids)!=count($old_orders)){
    					$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'订单中不包含符合条件的货品。');
    				}else{
    					$scale_new=$new_orders;
    					if($is_scale!=0){//需要按比例循环，新货品数量按比例加倍
    						foreach ($scale_new as $sn=>$s){
    							$scale_new[$sn]['num']*=$scale;
    						}
    					}
    					$order_v=$version[$k];
						D('SalesTrade')->execute('CALL I_DL_TMP_SUITE_SPEC()');
    					$order_result=D('SalesTrade')->exchangeOrder($exchange_ids,$scale_new,$order_v,$user_id);
    					if($order_result['status']==1){
							foreach($order_result['info'] as $info){
								$list[]=$info;
							}
						}

					}
    			}
    		}
    	}catch (\PDOException $e){
    		\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
    		SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e)
		{
			$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>$e->getMessage());
		}
    	$result['status']=empty($list)?0:1;
    	$result['info']['rows']=$list;
    	$result['info']['length']=count($list);
    	return $result;
    }

    // 赠品未赠原因
    public function getGiftNotSendReason($trade_id=0,$search=array()){
    	try{
    		$sql_error_info='';//记录错误信息
    		$where_gift_rule=''; //记录搜索条件
    		$where_rec_id='';
    		$trade_info=array();//记录订单信息
    		$sgr_list=array();//订单已执行的策略
    		$cgr_list=array();//订单未执行策略 
    		$data=array();  		
    		// 搜索条件
    		foreach ($search as $k=>$v){
	    		if($v==='') continue;
	    		switch ($k)
	    		{   
	    			case 'rule_no':
	    				set_search_form_value($where_gift_rule, $k, $v,'cgr', 1,' AND ');
	    				break;
	    			case 'rule_name':
	    				set_search_form_value($where_gift_rule, $k, $v,'cgr', 6,' AND ');
	    				break;
	    			case 'shop_list':
	    				$v=intval($v);
	    				$where_gift_rule.=$v==0?'':' AND FIND_IN_SET('.$v.',cgr.shop_list) ';
	    				break;
	    		}
	    	}
	    	// 按照订单号查出来订单的信息
	    	if ($trade_id==0) { return $data;  }
	    	$sql_error_info='getGiftNotSendReason-get_trade_info';
	    	$trade_info=M("sales_trade")->WHERE("trade_id=".$trade_id)->find();	 	
	    	if(empty($trade_info)){ return $data; }
    		// 按照订单号查出来已经执行了的策略
    		$sql_error_info='getGiftNotSendReason-get_sgr_list';
	    	$sgr_list=M("sales_gift_record")->alias('sgr')->field('sgr.rule_id,cgr.rule_group')->where("trade_id=".$trade_id)->join('cfg_gift_rule cgr ON cgr.rec_id=sgr.rule_id')->select();
	    	$sgr_str='(';$rule_group=array();
	    	foreach ($sgr_list as $sgr) { $sgr_str.=$sgr['rule_id'].",";$rule_group[]=$sgr['rule_group']; }
	    	$sgr_str=rtrim($sgr_str,",").")";
    		// 选出所有的策略排除掉已经执行了的
			if (!empty($sgr_list)) {
				$where_rec_id=" AND rec_id not in ".$sgr_str." ";
			}
			$cfg_sql="SELECT cgr.rec_id,cgr.rule_no,cgr.rule_name,cgr.rule_priority,cgr.rule_group,cgr.is_enough_gift,
				         cgr.limit_gift_stock,cgr.rule_multiple_type,cgr.rule_type,cgr.gift_is_random,cgr.time_type,
				         cgr.start_time,cgr.end_time,cgr.shop_list,cgr.min_goods_count,cgr.max_goods_count,
				         cgr.min_specify_count,cgr.max_specify_count,cgr.specify_count,cgr.bspecify_multiple,
				         cgr.limit_specify_count,cgr.min_goods_amount,cgr.max_goods_amount,cgr.is_specify_sum,cgr.min_receivable,cgr.max_receivable,
				         cgr.min_specify_amount,cgr.max_specify_amount,cgr.is_specify_sum,cgr.pay_start_time, cgr.pay_end_time,cgr.spec_key_word,cgr.goods_key_word,cgr.csremark_key_word,
				         cgr.trade_start_time,cgr.trade_end_time,cgr.is_disabled,cgr.created 
				         FROM cfg_gift_rule cgr 
				         WHERE cgr.is_disabled=0 ".$where_rec_id.$where_gift_rule." 
				         ORDER BY cgr.rec_id DESC";
			$sql_error_info='getGiftNotSendReason-get_cgr_list';
			$cgr_list=$this->query($cfg_sql);
			if (empty($cgr_list)&&$where_gift_rule=='') {
				$cgr_list[0]['rule_no']='<span style="color:red;">无</span>';
				$cgr_list[0]['rule_name']='<span style="color:red;">无</span>';
				$cgr_list[0]['reason']='<span style="color:red;">没有未被执行的赠品策略或没有可用的赠品策略</span>';
				$data=array('total'=>count($cgr_list),'rows'=>$cgr_list);
				return $data;
			}
			// 查找赠品未赠送的原因
			foreach ($cgr_list as $k => $v) {				
				// 该订单递交后新建的策略
				if (strtotime($v['created'])>strtotime($trade_info['created'])) {
					$cgr_list[$k]['reason']='此订单递交时尚未建立该策略';continue;
				}		
				// 分组
				if (in_array($v['rule_group'], $rule_group)) {
					$cgr_list[$k]['reason']='该策略的同一分组已有策略被执行';continue;
				}	
				// 店铺
				if ($v['rule_type'] & (1<<4)) {
					$shop_list=explode(",", $v['shop_list']);
					if (!in_array($trade_info['shop_id'],$shop_list)) {
						$cgr_list[$k]['reason']='此订单的店铺不在该策略的店铺列表中';continue;
					}
				}					
				// 时间
				if ($v['rule_type'] & (1<<3)) {
					if (($v['time_type']==1)&&($trade_info['delivery_term']==1)) {//付款时间、款到发货
						if (strtotime($trade_info['pay_time'])<strtotime($v['start_time'])||strtotime($trade_info['pay_time'])>strtotime($v['end_time'])) {
							$cgr_list[$k]['reason']='此订单的付款时间不在该策略的有效期中';continue;
						}
					}elseif (($v['time_type']==3)||($trade_info['delivery_term']==2)) {//下单时间、货到付款
						if (strtotime($trade_info['trade_time'])<strtotime($v['start_time'])||strtotime($trade_info['trade_time'])>strtotime($v['end_time'])) {
							$cgr_list[$k]['reason']='此订单的下单时间不在该策略的有效期中';continue;
						}
					}elseif ($v['time_type']==2) {
						if (strtotime($trade_info['created'])<strtotime($v['start_time'])||strtotime($trade_info['created'])>strtotime($v['end_time'])) {
							$cgr_list[$k]['reason']='此订单的递交时间不在该策略的有效期中';continue;
						}
					}else{
						$cgr_list[$k]['reason']='该策略设置的时间类型有误';continue;
					}
				}	
				// 货品总数
				if ($v['rule_type']&(1<<7)) {
					if ($v['max_goods_count']==0) {
						if ($trade_info['raw_goods_count']<$v['min_goods_count']) {
							$cgr_list[$k]['reason']='此订单的货品总数不满足该策略要求';continue;
						}						
					}else{
						if ($trade_info['raw_goods_count']<$v['min_goods_count']||$trade_info['raw_goods_count']>$v['max_goods_count']) {
							$cgr_list[$k]['reason']='此订单的货品总数不满足该策略要求';continue;
						}	
					}
				}
				// 货款总额
				if ($v['rule_type']&(1<<15)) {
					if ($v['max_goods_amount']==0) {
						if ($trade_info['goods_amount']<$v['min_goods_amount']) {
							$cgr_list[$k]['reason']='此订单的货款总额不满足该策略要求';continue;
						}						
					}else{
						if ($trade_info['goods_amount']<$v['min_goods_amount']||$trade_info['goods_amount']>$v['max_goods_amount']) {
							$cgr_list[$k]['reason']='此订单的货款总额不满足该策略要求';continue;
						}	
					}
				}
				// 实收金额
				if ($v['rule_type']&(1<<16)) {
					if ($v['max_receivable']==0) {
						if ($trade_info['receivable']<$v['min_receivable']) {
							$cgr_list[$k]['reason']='此订单的实收金额不满足该策略要求';continue;
						}						
					}else{
						if ($trade_info['receivable']<$v['min_receivable']||$trade_info['receivable']>$v['max_receivable']) {
							$cgr_list[$k]['reason']='此订单的实收金额不满足该策略要求';continue;
						}	
					}
				}
				// 指定货品共用
				if (($v['rule_type']&(1<<9))||($v['rule_type']&(1<<12))||($v['rule_type']&(1<<21))) {// 查看该订单所有子订单
					$sto_sql="SELECT IF(sto.suite_id=0,0,1) AS is_suite,IF(sto.suite_id=0,sto.spec_id,sto.suite_id) AS spec_id,IF(sto.suite_id=0,sto.actual_num,sto.suite_num) AS num,IF(sto.suite_id=0,sto.discount,SUM(sto.discount)) AS  discount,IF(sto.suite_id=0,sto.share_amount,SUM(sto.share_amount)) AS amount
							FROM sales_trade_order sto 
							WHERE sto.trade_id=".$trade_id." AND sto.actual_num>0 AND sto.gift_type=0";
					$sql_error_info='getGiftNotSendReason-get_sto_list';
					$sto_list=$this->query($sto_sql);
					if (empty($sto_list)) { $cgr_list[$k]['reason']='此订单下无有效货品';continue; }
				}
				// 指定货品数量
				if ($v['rule_type']&(1<<9)) {
					// 参加活动的单品集合为空
					$sql_error_info='getGiftNotSendReason-get_cgag_list_1';					
					$cgag_list1=M('cfg_gift_attend_goods')->where("rule_id=".$v['rec_id']." and goods_type=1")->select();
					if (empty($cgag_list1)) { $cgr_list[$k]['reason']='该策略下没有设置指定货品';continue; }
					$num=0;//记录是否符合策略
					foreach ($cgag_list1 as $cgag) {
						foreach ($sto_list as  $sto) {													
							if ($v['max_specify_count']==0) {
								if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']&&!empty($sto['num'])&&$sto['num']>=$v['min_specify_count']) {
									$num++;
								}
							}else{ 
								if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']&&!empty($sto['num'])&&$sto['num']>=$v['min_specify_count']&&$sto['num']<=$v['max_specify_count']) {
									$num++;
								}
							}
						}	
					}	
					if ($num==0) {
						$cgr_list[$k]['reason']='此订单的指定货品数量不满足该策略要求';continue;
					}	
				}
				// 指定货品倍增
				if ($v['rule_type']&(1<<12)) {
					// 参加活动的单品集合为空
					$sql_error_info='getGiftNotSendReason-get_cgag_list_2';					
					$cgag_list2=M('cfg_gift_attend_goods')->where("rule_id=".$v['rec_id']." and goods_type=2")->select();
					if ($v['specify_count']<=0||empty($cgag_list2)) { $cgr_list[$k]['reason']='该策略下没有设置指定货品或没有指定倍增数';continue; }
					$goods_num=0;//记录货品数量
					$specify_mutiple=0;//记录倍数
					foreach ($cgag_list2 as $cgag) {
						foreach ($sto_list as  $sto) {
							if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']) {
								$goods_num+=$sto['num'];
							}
						}	
					}	
					$specify_mutiple=FLOOR($goods_num/$v['specify_count']);					
					if ($specify_mutiple==0) {
						$cgr_list[$k]['reason']='此订单的指定货品倍增不满足该策略要求';continue;
					}	
				}
				// 指定货品金额范围
				if ($v['rule_type']&(1<<21)) {
					// 参加活动的单品集合为空
					$sql_error_info='getGiftNotSendReason-get_cgag_list_3';					
					$cgag_list3=M('cfg_gift_attend_goods')->where("rule_id=".$v['rec_id']." and goods_type=3")->select();
					if (empty($cgag_list3)) { $cgr_list[$k]['reason']='该策略下没有设置指定货品';continue; }
					$num=0;//记录是否符合策略
					$goods_amount=0;//记录货品金额
					if ($v['is_specify_sum']==1) {
						foreach ($cgag_list3 as $cgag) {
							foreach ($sto_list as  $sto) {
								if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']) {
									$goods_amount+=$sto['amount'];
								}
							}	
						}
						if ($v['max_specify_amount']==0&&$goods_amount>=$v['min_specify_amount']) {
							$num++;
						}else if($goods_amount>=$v['min_specify_amount']&&$goods_amount<=$v['max_specify_amount']){ 
							$num++;
						}	
					}else{
						foreach ($cgag_list3 as $cgag) {
							foreach ($sto_list as  $sto) {													
								if ($v['max_specify_amount']==0) {
									if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']&&!empty($sto['amount'])&&$sto['amount']>=$v['min_specify_amount']) {
										$num++;
									}
								}else{ 
									if ($sto['is_suite']==$cgag['is_suite']&&$sto['spec_id']==$cgag['spec_id']&&!empty($sto['amount'])&&$sto['amount']>=$v['min_specify_amount']&&$sto['amount']<=$v['max_specify_amount']) {
										$num++;
									}
								}
							}	
						}
					}
						
					if ($num==0) {
						$cgr_list[$k]['reason']='此订单的指定货品金额不满足该策略要求';continue;
					}	
				}
				if ($v['rule_type']&(1<<29)) {
					if ($v['goods_key_word']==''&&$v['spec_key_word']=='') {
						$cgr_list[$k]['reason']='该策略下没有设置平台货品关键词和平台规格关键词';continue;
					}					
					if ($v['goods_key_word']!='') {
						$keyword_sql="SELECT 1 FROM api_trade_order ato LEFT JOIN sales_trade_order sto ON (ato.shop_id=sto.shop_id AND  ato.oid=sto.src_oid) WHERE sto.trade_id=".$trade_id." AND sto.gift_type=0 AND ato.goods_name LIKE CONCAT_WS('','%','".$v['goods_key_word']."','%')";
						$sql_error_info='getGiftNotSendReason-get_sto_list';
						$is_exists=$this->query($keyword_sql);
						if (empty($is_exists)) {
							$cgr_list[$k]['reason']='此订单的平台货品名称不包含策略中的指定关键词';continue;
						}
					}
					if ($v['spec_key_word']!='') {
						$keyword_sql="SELECT 1 FROM api_trade_order ato LEFT JOIN sales_trade_order sto ON (ato.shop_id=sto.shop_id AND  ato.oid=sto.src_oid) WHERE sto.trade_id=".$trade_id." AND sto.gift_type=0 AND ato.spec_name LIKE CONCAT_WS('','%','".$v['spec_key_word']."','%')";
						$sql_error_info='getGiftNotSendReason-is_exists_spec_key_word';
						$is_exists=$this->query($keyword_sql);
						if (empty($is_exists)) {
							$cgr_list[$k]['reason']='此订单的平台规格名称不包含策略中的指定关键词';continue;
						}
					}
				}
				//客服备注
				if ($v['rule_type']&(1<<30)) {
					if($trade_info['cs_remark']==''){
						$cgr_list[$k]['reason']='该订单没有客服备注';continue;
					}
					if ($v['csremark_key_word']=='') {
						$cgr_list[$k]['reason']='该策略下客服备注关键词为空';continue;
					}else{
						$keyword_sql="SELECT 1 FROM sales_trade st WHERE st.trade_id=".$trade_id." AND st.cs_remark LIKE CONCAT_WS('','%','".$v['csremark_key_word']."','%')";
						$sql_error_info='getGiftNotSendReason-is_exists_spec_key_word';
						$is_exists=$this->query($keyword_sql);
						if (empty($is_exists)) {
							$cgr_list[$k]['reason']='此订单的客服备注不包含策略中的指定关键词';continue;
						}
					}
				}
				// 此规则下没有设置赠品
				$sql_error_info='getGiftNotSendReason-get_cgsg_list';
				$cgsg_list=M('cfg_gift_send_goods')->where('rule_id='.$v['rec_id']." AND priority=11")->select();
				if (empty($cgsg_list)) {
					$cgr_list[$k]['reason']='该策略下没有有效赠品';continue;
				}
				// 库存不足
				if ($v['is_enough_gift']) {
					$num=0;//记录库粗不足的个数
					foreach ($cgsg_list as $cgsg) {
						if ($cgsg['is_suite']==0) {
							$stock_sql="SELECT IFNULL(SUM(stock_num-order_num-sending_num),0) AS stock  FROM stock_spec WHERE spec_id=".$cgsg['spec_id'];
						}else{
							$stock_sql="SELECT SUM(tmp.suite_stock)  AS stock FROM (
										SELECT FLOOR(IFNULL(MIN(IFNULL(stock_num-order_num-sending_num, 0)/gsd.num),0)) AS suite_stock 
										FROM  goods_suite_detail gsd 
										LEFT JOIN  stock_spec ss ON ss.spec_id=gsd.spec_id 
										WHERE gsd.suite_id=".$cgsg['spec_id']." GROUP BY ss.warehouse_id
										) tmp";
						}
						$sql_error_info='getGiftNotSendReason-get_stock_list';	
						$stock=$this->query($stock_sql);	
						if ($stock[0]['stock']-$v['limit_gift_stock']<$cgsg['gift_num']) {$num++;}			
					}
					if ($num>0) { $cgr_list[$k]['reason']='该策略中存在赠品库存不足';continue; }
				}
				// 修改过赠品策略，订单递交时策略停用后又启用等情况
				$cgr_list[$k]['reason']='该策略可能已修改，请检查';continue;
			}
			$data=array('total'=>count($cgr_list),'rows'=>$cgr_list);
    	}catch (\PDOException $e){    		
    		$data=array('total'=>0,'rows'=>array());
    		\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
    	}catch(\Exception $e){
			$data=array('total'=>0,'rows'=>array());
    		\Think\Log::write($this->name.'-getGiftNotSendReason-'.$e->getMessage());
		}
    	return $data;
    }

    public function deepSplit($ids,$user_id){
    	$list=array();
    	$result=array();
    	foreach ($ids as $id){
    		if($this->getSplitCheckInfo($id,$list)){
    			//计算订单货品数量
    			$orders=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id AS id,src_oid,spec_no,spec_name,goods_name,api_goods_name,
    					api_spec_name,num,actual_num,price,share_price,order_price,discount,share_post,share_amount,remark,weight',
    					array('trade_id'=>array('eq',$id)));
    			$goods_num=count($orders);
				//增加日志
				$trade_log[]=array(
					'trade_id'=>$id,
					'operator_id'=>$user_id,
					'type'=>37,
					'data'=>1,
					'message'=>'按单品拆为一单一货',
					'created'=>date('y-m-d H:i:s',time()),
				);
				D('SalesTradeLog')->addTradeLog($trade_log);
    			foreach ($orders as $k=>$order){
    				while ($order['actual_num']>($k==$goods_num-1?1:0)){
    					$order['split_num']=1;
    					$order['left_num']=$order['actual_num']-1;
    					$order['share_post_one']=$order['share_post']/$order['actual_num'];
    					$order['discount_one']=$order['discount']/$order['actual_num'];
    					$order['share_amount_one']=$order['share_amount']/$order['actual_num'];
    					try{
    						$this->splitTrade($id,array($order),$user_id);
    					}catch (BusinessLogicException $e){
    						$trade=$this->getSalesTrade('trade_no',array('trade_id'=>array('eq'=>$id)));
							$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$e->getMessage());    					
    					}
    					$order['actual_num']-=1;
    					$order['share_post']-=$order['share_post_one'];
    					$order['discount']-=$order['discount_one'];
    					$order['share_amount']-=$order['share_amount_one'];
    				}
    			}
    		}
    	}
    	unset($list['warehouse_id']);unset($list['src_tids']);unset($list['goods_amount']);unset($list['post_amount']);unset($list['discount']);unset($list['receivable']);
    	$result=array(
    		'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
    		'fail'=>$list,//失败提示信息
    	);
    	return $result;
    }

	public function deepSplitBySuite($ids,$user_id){
		$list=array();
		$result=array();
		$suites=array();
		$specs=array();
		$sum_suite_num=0;
		foreach ($ids as $id){
			if($this->getSplitCheckInfo($id,$list)){
				//计算订单货品数量
				$trade_orders=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id AS id,src_oid,spec_no,spec_name,goods_name,api_goods_name,
    					api_spec_name,num,actual_num,price,share_price,order_price,discount,share_post,share_amount,remark,weight,spec_id,suite_id,suite_num',
					array('trade_id'=>array('eq',$id)));
				if(empty($trade_orders)){
					$trade = $this->getSalesTrade('trade_no', array('trade_id' => array('eq' => $id)));
					SE('订单'.$trade['trade_no'].'中没有有效货品！');
				}
				//把组合装和单品列出来
				foreach ($trade_orders as $k=>$order){
					if($order['suite_id']==0){
						$specs[]=$order;
					}else{
						if(!empty($suites[$order['src_oid']])){
							continue;
						}
						$suites[$order['src_oid']]['suite_id']=$order['suite_id'];
						$suites[$order['src_oid']]['src_oid']=$order['src_oid'];
						$suites[$order['src_oid']]['suite_num']=$order['suite_num'];
						$sum_suite_num+=$order['suite_num'];
					}
				}
				if(empty($suites)||$sum_suite_num==0){
					$trade = $this->getSalesTrade('trade_no', array('trade_id' => array('eq' => $id)));
					SE('订单'.$trade['trade_no'].'中不包含组合装！');
				}
				$suite_num=count($suites);
				if($suite_num==1&&$sum_suite_num==1&&empty($specs)){
					$trade = $this->getSalesTrade('trade_no', array('trade_id' => array('eq' => $id)));
					SE('订单'.$trade['trade_no'].'中只有一个组合装，不可拆分！');
				}
				//增加日志
				$trade_log[]=array(
					'trade_id'=>$id,
					'operator_id'=>$user_id,
					'type'=>37,
					'data'=>1,
					'message'=>'按组合装拆为一单一货',
					'created'=>date('y-m-d H:i:s',time()),
				);
				D('SalesTradeLog')->addTradeLog($trade_log);
				//先拆分组合装
				$num=0;
				$suites=array_values($suites);
				foreach($suites as $k=>$order){
					//判断订单中是否存在单品
					if(empty($specs)){
						$num=($k==$suite_num-1)?1:0;
					}
					while ($order['suite_num']>$num){
						$order['split_num']=1;
						try{
							$this->splitBySuite($id,array($order));
						}catch (BusinessLogicException $e){
							$trade=$this->getSalesTrade('trade_no',array('trade_id'=>array('eq'=>$id)));
							$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$e->getMessage());
						}
						$order['suite_num']-=1;
					}
				}
				//再拆分单品
				$spec_num=count($specs);
				foreach ($specs as $k=>$spec_order) {
					while ($spec_order['actual_num'] > ($k == $spec_num - 1 ? 1 : 0)) {
						$spec_order['split_num'] = 1;
						$spec_order['left_num'] = $spec_order['actual_num'] - 1;
						$spec_order['share_post_one'] = $spec_order['share_post'] / $spec_order['actual_num'];
						$spec_order['discount_one'] = $spec_order['discount'] / $spec_order['actual_num'];
						$spec_order['share_amount_one'] = $spec_order['share_amount'] / $spec_order['actual_num'];
						try {
							$this->splitTrade($id, array($spec_order), $user_id);
						} catch (BusinessLogicException $e) {
							$trade = $this->getSalesTrade('trade_no', array('trade_id' => array('eq' => $id)));
							$list[] = array('trade_no' => $trade['trade_no'], 'result_info' => $e->getMessage());
						}
						$spec_order['actual_num'] -= 1;
						$spec_order['share_post'] -= $spec_order['share_post_one'];
						$spec_order['discount'] -= $spec_order['discount_one'];
						$spec_order['share_amount'] -= $spec_order['share_amount_one'];
					}
				}
			}
		}
		unset($list['warehouse_id']);unset($list['src_tids']);unset($list['goods_amount']);unset($list['post_amount']);unset($list['discount']);unset($list['receivable']);
		$result=array(
			'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
			'fail'=>$list,//失败提示信息
		);
		return $result;
	}

    // 一键拆分合并单
    public function splitMergeTrade($ids,$user_id){
    	$list=array();
    	$result=array();
    	foreach ($ids as $id){
    		if($this->getSplitCheckInfo($id,$list)){
    			//查找订单原始单号
    			$orders=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id AS id,src_tid,src_oid,spec_no,spec_name,goods_name,api_goods_name,
    					api_spec_name,num,actual_num,price,share_price,order_price,discount,share_post,share_amount,remark,weight,gift_type',
    					array('trade_id'=>array('eq',$id)));
    			// 将原始订单号拿出来，判断个数，如果原始单号只有一个的话，那么非合并单，不可拆分
    			$split_orders=array();
    			$src_tids=array();
    			foreach ($orders as $k => $v) {
					if($v['gift_type']==1){continue;}
    				$v['split_num']=$v['actual_num'];
    				$v['left_num']=0;
					$v['share_post_one']=$v['share_post']/$v['actual_num'];
					$v['discount_one']=$v['discount']/$v['actual_num'];
					$v['share_amount_one']=$v['share_amount']/$v['actual_num'];
    				$split_orders[$v['src_tid']][]=$v;					
    			}
    			$count=count($split_orders);
    			if ($count<=1) {
    				SE('存在非合并订单，不可一键拆分');
    			}
				//增加日志
				$trade_log[]=array(
					'trade_id'=>$id,
					'operator_id'=>$user_id,
					'type'=>37,
					'data'=>1,
					'message'=>'一键拆分合并单',
					'created'=>date('y-m-d H:i:s',time()),
				);
				D('SalesTradeLog')->addTradeLog($trade_log);
    			$num=1;
    			foreach ($split_orders as $o) {
    				if($num<$count) {
    					try{
	    					$trade_ids[]=$this->splitTrade($id,$o,$user_id);
	    					$num++;
	    				}catch (BusinessLogicException $e){
	    						$trade=$this->getSalesTrade('trade_no',array('trade_id'=>array('eq'=>$id)));
								$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$e->getMessage());    					
	    				}	    				
    				}
    			}
				$trade_ids[]=$id;
    		}
    	}
		//获取配置值
		$config=get_config_value('split_merge_trade_auto_recalculation_gift',0);
		//自动重算赠品==前提不是手工建单
		if($config==1){
			$sql_error_info='recalculationGift-getSalesTrade';
			$trade_list=D('SalesTrade')->getSalesTradeList(
				'st.trade_id, st.trade_no,st.trade_status,st.warehouse_id,st.customer_id,st.trade_from',
				array('st.trade_id'=>array('in',$trade_ids)),
				'st'
			);
			if(!empty($trade_list)){
				$this->recalculation($trade_list,$user_id);
			}
		}
    	unset($list['warehouse_id']);unset($list['src_tids']);unset($list['goods_amount']);unset($list['post_amount']);unset($list['discount']);unset($list['receivable']);
    	$result=array(
    		'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
    		'fail'=>$list,//失败提示信息
    	);
    	return $result;
    }   

    public function passelAddGoods($ids,$orders,$version,$user_id){
    	$list=array();
    	$result=array();
    	$spec_id=array();
    	$suite_id=array();
    	$trade_order_list=array();
    	$sql_error_info='';
		$is_rollback=fales;
    	try{
    		//按订单号查找出订单相应的信息
    		$sql_error_info='getSalesTrade';
    		$res_trade_list=D('SalesTrade')->getSalesTradeList(
					'st.trade_id, st.trade_no,st.trade_status,st.freeze_reason,st.version_id,st.shop_id,
					 st.buyer_nick,st.receiver_name,st.receiver_mobile,st.receiver_telno,st.receiver_zip,
					 st.receiver_address, st.receiver_area,st.receiver_dtb, st.warehouse_id, st.platform_id,
					 st.delivery_term,st.goods_count, st.invoice_type,st.invoice_title,st.invoice_content,
					 st.paid,st.receivable',
					array('st.trade_id'=>array('in',$ids)),
					'st'
			);		
			if(empty($res_trade_list))
			{
				SE('没有符合条件的订单');
			}

			$is_gift=($orders[0]['gift_type']==0)?0:1;//是否为赠品
			
			foreach ($orders as $k=>$o) {
				if ($o['is_suite']==1) {
					$suite_id[]=$o['id'];
				}else{
					$spec_id[]=$o['id'];
				}
				$orders[$o['id']]=$o;unset($orders[$k]);
			}
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');

			$this->startTrans();
			foreach ($res_trade_list as $trade) {
				if($version[$trade['trade_id']]!=$trade['version_id'])
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单被其他人修改，请打开重新编辑');
					continue;
				}
				if ($trade['trade_status']!=30 && $trade['trade_status']!=25 && $trade['trade_status']!=27) 
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单非待审核状态，请打开重新编辑');
					continue;
				}
				if ($trade['freeze_reason']>0) 
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
					continue;
				}	

				$spec_tip="";
				$suite_tip="";
				$trade_orders=$orders;//针对单一订单修改要添加的货品

				//按订单号筛选出对应子订单
				$sql_error_info='getSalesTradeOrder';		
				$res_trade_order_list=D('SalesTradeOrder')->getSalesTradeOrderList(
						'sto.trade_id,sto.spec_id,sto.goods_name,sto.suite_name,IF(sto.gift_type=0,0,1) as is_gift,at.rec_id,sto.suite_id',
						array('sto.trade_id'=>array('eq',$trade['trade_id'])),
						'sto',
						'LEFT JOIN api_trade at ON sto.src_tid=at.tid'
				);
				if (empty($res_trade_order_list)) {
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单中没有货品');
				}

				foreach ($res_trade_order_list as $order) {	//需要考虑一下赠品类型是否相同				
					// 单品
					if (!empty($spec_id)&&$order['spec_id']!=0&&$order['is_gift']==$is_gift&&in_array($order['spec_id'], $spec_id)) {
						if (!in_array($order['spec_id'], $spec)) {
							if ($is_gift==1) {$spec_tip='赠品：';}else{$spec_tip='单品：';}
							$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$spec_tip.$order['goods_name'].'-已存在');
							unset($trade_orders[$order['spec_id']]);
						}
					}
					// 组合装
					if (!empty($suite_id)&&$order['suite_id']!=0&&$order['is_gift']==$is_gift&&in_array($order['suite_id'], $suite_id)) {
						if (!in_array($order['suite_id'], $suite)) {
							if ($is_gift==1) {$suite_tip='组合装赠品：';}else{$suite_tip='组合装：';}
							$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$suite_tip.$order['suite_name'].'-已存在');
						    unset($trade_orders[$order['suite_id']]);
						}							
					}
				}

				if (!empty($trade_orders)) {
					// 添加新货品
					$sql_error_info='addTradeOrder';
					$order_result=D('SalesTrade')->addTradeOrder($trade, $trade_orders, $user_id);
					 //刷新库存
					$sql_error_info='I_RESERVE_STOCK';
				    $this->execute('CALL I_RESERVE_STOCK('.$trade['trade_id'].',IF('.$trade['trade_status'].'=30,3,5),'.intval($trade['warehouse_id']).','.intval($trade['warehouse_id']).')');
					//刷新订单
					$sql_error_info='I_DL_REFRESH_TRADE';
					$this->execute('CALL I_DL_REFRESH_TRADE('.$user_id.', '.$trade['trade_id'].', 2, 0)');
				}

			}
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
    	$result['status']=empty($list)?0:1;
    	$result['info']['rows']=$list;
    	$result['info']['length']=count($list);
    	return $result;
    }
	//重算赠品
	public function recalculationGift($trade_ids,$version,$user_id){
		$sql_error_info='';
		$list=array();
		$result=array();
		try{
			//根据trade_id查找订单信息
			$sql_error_info='recalculationGift-getSalesTrade';
			$res_trade_list=D('SalesTrade')->getSalesTradeList(
				'st.trade_id, st.trade_no,st.trade_status,st.freeze_reason,st.version_id,st.shop_id,
				 st.warehouse_id, st.platform_id,st.customer_id,st.trade_from',
				array('st.trade_id'=>array('in',$trade_ids)),
				'st'
			);
			if(empty($res_trade_list)){
				SE("没有有效订单");
			}
			$this->execute('CALL I_DL_TMP_GIFT_TRADE_ORDER()');
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			foreach($res_trade_list as $trade){
				if($version[$trade['trade_id']]!=$trade['version_id'])
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单被其他人修改，请打开重新编辑');
					continue;
				}
				if ($trade['trade_status']!=30 && $trade['trade_status']!=25 && $trade['trade_status']!=27)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单非待审核状态，请打开重新编辑');
					continue;
				}
				if ($trade['freeze_reason']>0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结，如需操作请先解冻');
					continue;
				}
				if ($trade['bad_reason']>0)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单有异常标记，请先处理');
					continue;
				}
				if ($trade['trade_from']==2)
				{
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'手工建单不能重算赠品');
					continue;
				}
				$trade_list[]=$trade;
			}
			$this->recalculation($trade_list,$user_id);
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			SE($e->getMessage());
		}
		$result['status']=empty($list)?0:2;
		$result['info']['rows']=$list;
		$result['info']['length']=count($list);
		return $result;
	}
	//重算赠品
	public function recalculation($trade_list,$user_id){
		$sql_error_info='';
		$list=array();
		$result=array();
		try{
			$this->execute('CALL I_DL_TMP_GIFT_TRADE_ORDER()');
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			foreach($trade_list as $trade){
				if($trade['trade_from']==2){
					continue;
				}
				//删除原自动赠品
				$is_rollback=true;
				$this->startTrans();
				$sql_error_info='recalculationGift-I_RESERVE_STOCK';
				$this->execute('CALL I_RESERVE_STOCK('.$trade['trade_id'].',IF('.$trade['trade_status'].'=30,3,5),0,'.intval($trade['warehouse_id']).')');
				$sql_error_info='recalculationGift-delete_gift';
				$this->execute('DELETE FROM sales_trade_order WHERE trade_id=%d AND gift_type=1',$trade['trade_id']);
				$trade_log=array(
					'trade_id'=>$trade['trade_id'],
					'operator_id'=>$user_id,
					'type'=>174,
					'message'=>'重算赠品--删除订单原赠品--增加新赠品',
					'created'=>date('y-m-d H:i:s',time())
				);
				D('Trade/SalesTradeLog')->addTradeLog($trade_log);
				//重新计算新赠品
				$sql_error_info='recalculationGift-send_gift';
				$sql = "CALL I_DL_SEND_GIFT(".$user_id.",".$trade['trade_id'].",".$trade['customer_id'].",@sendOk)";
				$this->execute($sql);
				//刷新库存
				$sql_error_info='recalculationGift-I_RESERVE_STOCK';
				$this->execute('CALL I_RESERVE_STOCK('.$trade['trade_id'].',IF('.$trade['trade_status'].'=30,3,5),'.intval($trade['warehouse_id']).',0)');
				//刷新订单
				$sql_error_info='recalculationGift-I_DL_REFRESH_TRADE';
				$this->execute('CALL I_DL_REFRESH_TRADE('.$user_id.', '.$trade['trade_id'].', 2, 0)');
				$this->commit();
			}
		}catch(\PDOException $e){
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
		}catch(\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
	}

    // 一键合并且审核（只针对档口单）
	public function mergeAndCheckTrade($id){
		$sql_error_info='';
		$id=intval($id);
		$unmerge_trade_id=array();//未合并的订单id
		$checked_trade=array();	//已审核的订单
		$remain_info=array();//需要保留的信息
		$finacial_trade = array();
		$stockout_orders = array();
		$stockout_sync = array();
		$list = array();
		$success = array();
		try {
			$box_goods_detail_model    = M('box_goods_detail');
			$sorting_wall_detail_model = M('sorting_wall_detail');
		    $is_rollback=true;			
			$user_id=get_operator_id();	
			//调用刷新订单时先创建组合装临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			// 根据id找到对应的订单
			$sql_error_info='getSalesTrade';
			$trade=$this->getSalesTrade(
						'st.trade_id,st.trade_no, st.platform_id, st.shop_id, st.warehouse_id, st.warehouse_type, st.src_tids,
						st.trade_status,st.trade_from, st.trade_type, st.delivery_term, st.unmerge_mask,st.customer_id,
						st.buyer_nick, st.receiver_name, st.receiver_country, st.receiver_province,st.salesman_id,st.print_remark,
						st.receiver_city, st.receiver_district, st.receiver_address, st.receiver_mobile, st.receiver_telno,
						st.receiver_zip, st.receiver_area, st.receiver_ring, st.receiver_dtb, st.to_deliver_time, st.dist_center,
						st.dist_site, st.logistics_id, st.buyer_message, st.cs_remark, st.cs_remark_change_count, st.goods_amount,
						st.post_amount, st.discount, st.receivable,st.bad_reason,st.is_sealed,st.weight',
						array('st.trade_id'=>array('eq',$id)),
						'st'
						);	
			if (empty($trade)) {
				SE('无效订单');
			}
			if(empty($trade['receiver_mobile'])&&empty($trade['receiver_telno']))
			{
				SE('收件人手机和电话至少一个不为空');
			}
			// 根据订单信息找到同名未匹配的订单
			$config=get_config_value(array('order_check_warn_has_unmerge','order_check_merge_warn_mode','open_package_strategy','order_check_warn_has_unmerge_preorder','order_check_warn_has_unmerge_checked','order_check_warn_has_unmerge_address'),array(1,0,1,0,1,0));
			if($config['order_check_warn_has_unmerge_checked']==1)
			{
				$end_status=95;
			}else
			{
				$end_status=55;
			}
			//跨店铺合并
			$sql_error_info='query_unmerge_trade_shop';
			$unmerge_trade=$this->query(
					"SELECT st.trade_id,st.trade_status,st.stockout_no,so.stockout_id,st.is_stalls,st.warehouse_id,st.warehouse_type FROM sales_trade st 
					 LEFT JOIN stockout_order so ON so.stockout_no=st.stockout_no 
					 WHERE st.customer_id = ".$trade['customer_id']." AND st.buyer_nick='".addslashes($trade['buyer_nick'])."' 
					 AND IF(".$config['order_check_merge_warn_mode']."=2 OR ".$config['order_check_merge_warn_mode']." = 3,TRUE,
					 st.receiver_name = '".addslashes($trade['receiver_name'])."') AND st.receiver_area = '".$trade['receiver_area']."' AND st.receiver_address = '".addslashes($trade['receiver_address'])."' 
					 AND st.trade_status>=15 AND st.trade_status<".$end_status." AND IF(".$config['order_check_warn_has_unmerge_preorder'].",1,st.trade_status<>25) 
					 AND st.split_from_trade_id <=0 AND st.freeze_reason=0 AND st.delivery_term=1 AND st.is_sealed = 0 AND IF(".$config['order_check_merge_warn_mode']."=1 
					 OR ".$config['order_check_merge_warn_mode']." = 2,st.shop_id=".$trade['shop_id'].",TRUE) ORDER BY so.stockout_id DESC"
			);

			if(empty($unmerge_trade)&&$config['order_check_warn_has_unmerge_address']!=0)
			{//含有不同地址的
				$sql_error_info='query_unmerge_trade_address';
				$unmerge_trade=$this->query(
						"SELECT st.trade_id,st.trade_status,st.stockout_no,so.stockout_id,st.is_stalls,st.warehouse_id,st.warehouse_type  FROM sales_trade st
						 LEFT JOIN stockout_order so ON so.stockout_no=st.stockout_no 
						 WHERE st.customer_id = ".$trade['customer_id']." AND st.buyer_nick='".addslashes($trade['buyer_nick'])."' 
						 AND st.trade_status>=15 AND st.trade_status < ".$end_status." AND IF(".$config['order_check_warn_has_unmerge_preorder'].",1,st.trade_status<>25) 
						 AND st.split_from_trade_id <=0 AND st.delivery_term = 1 AND st.is_sealed = 0 AND IF(".$config['order_check_merge_warn_mode']."=1 
						 OR ".$config['order_check_merge_warn_mode']."= 2,st.shop_id=".$trade['shop_id'].",TRUE) AND IF(".$config['order_check_merge_warn_mode']."=2 
						 OR ".$config['order_check_merge_warn_mode']."= 3,TRUE,st.receiver_name = '".addslashes($trade['receiver_name'])."') 
						 AND (receiver_area <> '".$trade['receiver_area']."' OR receiver_address <> '".addslashes($trade['receiver_address'])."')  ORDER BY so.stockout_id DESC"
				);
			}
			if (empty($unmerge_trade)) {
				SE('没有找到有效的同名未合并订单');
			}
			// 判断是否有已审核的订单==如果多个已审核的只取最近的一个
			foreach ($unmerge_trade as $k => $v) {
				if ($v['trade_status']>=55) {
					if (!empty($checked_trade)) {
						continue;
					}
					$unmerge_trade_id[]=$v['trade_id'];
					$checked_trade=array(
						'trade_id'       =>$v['trade_id'],
						'stockout_id'    =>$v['stockout_id'],
						'is_stalls'      =>$v['is_stalls'],
						'warehouse_id'   =>$v['warehouse_id'],
						'warehouse_type' =>$v['warehouse_type'],
					);
				}else{
					$unmerge_trade_id[]=$v['trade_id'];
				}
			}
			//	判断是否存在已审核的订单，分情况进行处理
			if (empty($checked_trade)) {//没有
				$result_1=$this->revertMergeCheck($trade,$unmerge_trade_id,$user_id,$checked_trade,$remain_info,$list,$success,$finacial_trade,$stockout_orders,$stockout_sync);
			}else{//存在
				// 原订单已分拣且没有框，直接提示，返回，不再合并
				$sql_error_info='get-box_goods_info';
				$box_goods_info=$box_goods_detail_model->field('box_no,spec_id,trade_id,trade_no,sort_status,num')->where(array('trade_id'=>array('eq',$checked_trade['trade_id'])))->select();
				if (!empty($box_goods_info)&&$box_goods_info[0]['sort_status']==1) {
					$envaliable_box = $sorting_wall_detail_model->where(array("is_use"=>0))->select();
					if (empty($envaliable_box)) {
						SE('已审核订单已分拣完成并且没有可用的分拣框');	
					}	
				}
				if ($checked_trade['warehouse_id']!=$trade['warehouse_id']) {//如果仓库不一致，使用已审核的仓库
					SE('待合并订单与此订单仓库不一致，无法合并');
				}	
				//根据id找到该订单对应的子订单
				$sql_error_info='getSalesTradeOrder';
				$trade_orders=D('SalesTradeOrder')->alias('sto')->field('sto.spec_id,sto.trade_id,st.trade_no,
				      st.warehouse_id,SUM(sto.actual_num) as actual_num,IFNULL(ss.cost_price,0) cost_price,
				      (ss.stock_num-ss.sending_num) as available_stock')->join('
				      LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id 
					  LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=st.warehouse_id
				      ')->where(array('sto.trade_id'=>array('eq',$id),'sto.actual_num'=>array('gt',0)))->group('sto.spec_id')->select(); 
				if (empty($trade_orders)) {
					SE('订单中没有有效货品');
				}
				// 首先，判断已审核的订单是否存在生成缺货明细了(是否为档口单)==判断分拣状态
				$remain_info['old_order']=array();
				if ($checked_trade['is_stalls']==1) {
					$remain_info['old_order']=$checked_trade['trade_id'];
				}	
				//其次，判断新订单需要的库存是否充足
				$is_stalls=0; 
				$remain_info['new_orders']=array();
				foreach ($trade_orders as $sto) {				
					$odd_num=$sto['available_stock']-$sto['actual_num'];				
					if($odd_num>=0){continue;}					
					//查找对应的供应商
					$sql_error_info='get_provider_id';
					$provider_id=M('purchase_provider_goods')->field('provider_id')->where('spec_id='.$sto['spec_id'].' and is_disabled=0')->order('price')->find();
					// 库存不足
					$sto['provider_id']=empty($provider_id)?0:$provider_id['provider_id'];
					$sto['less_num']=abs($odd_num)>$sto['actual_num']?$sto['actual_num']:abs($odd_num);
					$remain_info['new_orders'][]=$sto;
					$is_stalls=1;		
				}				
				// 驳回->合并->审核
			    $this->revertMergeCheck($trade,$unmerge_trade_id,$user_id,$checked_trade,$remain_info,$list,$success,$finacial_trade,$stockout_orders,$stockout_sync);
				//更新分拣状态
				$this->updateSortStatus($checked_trade['trade_id'],$checked_trade['stockout_id'],$trade['trade_id'],$is_stalls,$checked_trade['is_stalls']);
			}
			$this->commit();
			//-----------------自动获取电子面单--------------------------
			$arr_cfg_key=array('order_check_get_waybill','order_check_synchronous_logistics');
			$arr_cfg_def_val=array(0,0);
			$res_cfg_val=get_config_value($arr_cfg_key,$arr_cfg_def_val);
			if ($res_cfg_val['order_check_get_waybill']!=0)
			{
				$this->getWayBill($stockout_orders,$success,$list);
			}
			//-----------------自动预物流同步---------------------------------------------
			if ($res_cfg_val['order_check_synchronous_logistics']!=0)
			{
				$this->synchronousLogistics($stockout_sync,$list);
			}
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
		$result=array(
			'check'=>empty($success)?false:true,
			'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
			'fail'=>$list,//失败提示信息
			'success'=>$success,//成功的数据
			'financial'=>!empty($finacial_trade)?$finacial_trade:'',
		);
		return $result;
		
	}

	// 驳回--合并--审核(一键操作)
	public function revertMergeCheck($trade,$unmerge_trade_id,$user_id,$checked_trade=array(),$remain_info=array(),&$list=array(),&$success=array(),&$finacial_trade=array(),&$stockout_orders=array(),&$stockout_sync=array()){
		$form_params   = array();//驳回所需信息
		$arr_form_data = array();//合并订单的主订单信息
		$sql_error_info='';
		try{
	        if (empty($unmerge_trade_id)) {
	        	SE("没有有效的同名未合并订单");
	        }
			//驳回
			if (!empty($checked_trade)) {
					$form_params=array(
							'reason_id'              =>0,
							'class_id'               =>1,
							'hold_logisticsno_status'=>0,
							'hold_sendbill_status'   =>0,
							'is_force'               =>0,
							'operator_id'            =>$user_id,
						);
		        	$success[$key] = array();
					$res_so_info = D('Stock/StockOutOrder')->field('stockout_no,status')->where(array('stockout_id'=>$checked_trade['stockout_id']))->find();
					if((int)$res_so_info['status'] ==5){
						SE('待合并订单状态发生变化，请刷新页面重试');
					}
					$sql_error_info='revertSalesStockout';
		            D('Stock/SalesStockOut')->revertSalesStockoutNoTrans($checked_trade['stockout_id'],$form_params,$list,$success[$key],false);
		            if(empty($success[$key])){ unset($success[$key]); }
					if (!empty($list)){ SE($list[0]['msg']); }
			}
			if (empty($fail)) {
				// 调用合并订单
				$arr_form_data=array(
					'shop'         =>$trade['shop_id'],
					'salesman'     =>$trade['salesman_id'],
					'receiver'     =>$trade['trade_id'],
					'mobile'       =>$trade['receiver_mobile'],
					'telno'        =>$trade['receiver_telno'],
					'logistics'    =>$trade['logistics_id'],
					'print_remark' =>$trade['print_remark']
				);
				$sql_error_info='mergeTrade';
				$this->mergeTradeNoTrans($unmerge_trade_id,$arr_form_data,$user_id);
				// 调用审核
				$sql_error_info='checkTrade';
				$sql_where=$this->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('eq',$trade['trade_id'])))->select();
				$data=$this->checkTradeNoTrans($sql_where,0,$user_id,0,0,$remain_info,$list,$success,$finacial_trade,$stockout_orders,$stockout_sync);
				if (!empty($list)){ SE($list[0]['result_info']); }
			}
		} catch (\PDOException $e){    		
    		\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
	}
	/**
	 * 更新分拣状态
	 * @param integer $checked_trade_id
	 * @param integer $checked_stockout_id
	 * @param integer $trade_id
	 */
	public function updateSortStatus($checked_trade_id,$checked_stockout_id,$trade_id,$is_stalls,$old_is_stalls){		
		$box_goods_info=array();
		$spec_id=array();
		$sql_error_info='';
		$checked_trade_id=intval($checked_trade_id);
		$checked_stockout_id=intval($checked_stockout_id);
		$trade_id=intval($trade_id);
		try{
			$box_goods_detail_model    = M('box_goods_detail');
			$sorting_wall_detail_model = M('sorting_wall_detail');

			// 查找合并后的订单信息
			$sql_error_info='getSalesTrade';
			$trade=$this->getSalesTrade(
						'st.trade_id,st.trade_no,st.stockout_no,so.stockout_id',
						array('st.trade_id'=>array('eq',$trade_id)),
						'st',
						'LEFT JOIN stockout_order so ON so.stockout_no=st.stockout_no'
						);	
			// 获取配置值
			$order_check_give_storting_box  = get_config_value('order_check_give_storting_box', 0);
			// 查看原订单分拣状态
			$sql_error_info='get_box_goods_info';
			$box_goods_info=$box_goods_detail_model->field('box_no,spec_id,trade_id,trade_no,sort_status,num')->where(array('trade_id'=>array('eq',$checked_trade_id)))->select();
			
			$data['trade_id']=$trade['trade_id'];
			$data['trade_no']=$trade['trade_no'];

			$trade_num=0;//订单中的货品总数
			// 查找订单对应的货品数量
			$sql_error_info='get_trade_num';
			$trade_num=D('SalesTrade')->field('goods_count')->where(array('trade_id'=>array('eq',$trade_id)))->find();

			// ======================================没有分拣================================================
			if (empty($box_goods_info)) {//没有分拣
				if ($order_check_give_storting_box==0) {//配置不开启	
					return true;
				}else{
					// 根据原订单的出库单id判断原订单是否已预占用了框
					$preoccupation_box_no=M('sorting_wall_detail')->field('box_no')->where('stockout_id='.$checked_stockout_id.' AND is_use=1')->find();
					if (!empty($preoccupation_box_no)) {//已经预占用
						// sorting_wall_detail
						$sql_error_info='update-preoccupation-sorting_wall_detail-------';
						$sql_error_info.="UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id']."  WHERE box_no='".$preoccupation_box_no['box_no']."'";
						$this->execute("UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id']."  WHERE box_no='".$preoccupation_box_no['box_no']."'");
						// 更新出库单
						$sql_error_info='update-preoccupation-stockout_order_box_no';
						$this->execute("UPDATE stockout_order SET box_no='".$preoccupation_box_no['box_no']."' where stockout_id='".$trade['stockout_id']."'");
						return true;
					}

					// 自动分配box_no
					$sql_error_info='get_sort_wall';
					$sort_wall = D('Purchase/SortingWall')->where(array('type'=>1))->select();				
					if(empty($sort_wall)){ SE('没有可用的分拣框');}
					$box_no=$this->allotSortingWall($trade['trade_id'],$trade['trade_no'],$trade_num['goods_count']);
					if ($box_no!='') {
						// 更新出库单
						$sql_error_info='update_stockout_order_box_no_1';
						$this->execute("UPDATE stockout_order SET box_no='".$box_no."' where stockout_id='".$trade['stockout_id']."'");
						// 更新分拣框状态为预占用
						$this->execute("UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id'].",is_use=1  WHERE box_no='".$box_no."'");
					}
					return true;
				}
			}
			// ======================================分拣过================================================
			foreach ($box_goods_info as $v) {
				$spec_id[]=$v['spec_id'];
			}			
			$so_update_data['box_no']=$box_goods_info[0]['box_no'];			
			// 更新分拣状态，如果已分拣完成，判断分拣框是否释放了，如果释放了，重新分配框
			if ($box_goods_info[0]['sort_status']==1) {//分拣完成
				$data['sort_status']=0;//分拣状态
				// 判断分拣框现在的状态
				$sql_error_info='get_sorting_wall_detail_is_use';
				$is_use=$sorting_wall_detail_model->field('is_use')->where(array('box_no'=>array('eq',$box_goods_info[0]['box_no']),'stockout_id'=>array('eq',$trade['stockout_id'])))->select();
				if (empty($is_use)||$is_use[0]['is_use']==0) {//重新分配框
					$data['box_no']=$this->allotSortingWall($trade['trade_id'],$trade['trade_no'],$trade_num['goods_count']);
					$so_update_data['box_no']=$data['box_no'];
				}				
				// 更新已审核订单的缺货明细分拣状态为已分拣==再确认一下更新状态的这块
				if ($is_stalls==1) {//档口单
					$sql_error_info='update-sort-stalls_less_goods_detail';
					$this->execute('UPDATE stalls_less_goods_detail SET sort_status=1 WHERE trade_id='.intval($trade_id).' AND sort_status in (2,3)');
				}else{
					// 判断已审核订单是否是档口单，如果是,更新状态为档口单已分拣完成
					if ($old_is_stalls==1) {
						$sql_error_info='update-sort-stalls_less_goods_detail';
					    $this->execute('UPDATE stalls_less_goods_detail SET sort_status=2 WHERE trade_id='.intval($trade_id).' AND sort_status=3');
					}else{
						$sql_error_info='update-sort-stalls_less_goods_detail';
						$this->execute('UPDATE stalls_less_goods_detail SET sort_status=1 WHERE trade_id='.intval($trade_id).' AND sort_status=3');
					}					
				}
				$sql_error_info='update-sort-sorting_wall_detail';
				$this->execute("UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id'].",is_use=1  WHERE box_no='".$so_update_data['box_no']."'");
				$sql_error_info='update-sort-stockout_order_box_no';
				$this->execute("UPDATE stockout_order SET box_no='".$so_update_data['box_no']."' where stockout_id='".$trade['stockout_id']."'");
				$sql_error_info='update-sort-box_goods_detail';
				$box_goods_detail_model->where(array('trade_id'=>array('eq',$checked_trade_id)))->save($data);
			}else{
				if ($is_stalls==1) {//档口单
					$sql_error_info='update-sort-stalls_less_goods_detail';
					$this->execute('UPDATE stalls_less_goods_detail SET sort_status=1 WHERE trade_id='.intval($trade_id).' AND sort_status=2');
				}
				$sql_error_info='update-unsort-sorting_wall_detail';
				$this->execute("UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id']."  WHERE box_no='".$so_update_data['box_no']."'");
				$sql_error_info='update-unsort-stockout_order_box_no';
				$this->execute("UPDATE stockout_order SET box_no='".$so_update_data['box_no']."' where stockout_id='".$trade['stockout_id']."'");
				$sql_error_info='update-unsort-box_goods_detail';
				$box_goods_detail_model->where(array('trade_id'=>array('eq',$checked_trade_id)))->save($data);
			}
			return true;
		} catch (\PDOException $e){    		
    		\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
	}

	public function getSMSData($ids)
	{
		try{
			$sql="SELECT st.src_tids,cs.shop_name,st.buyer_nick,st.receiver_name,st.receiver_area,st.receiver_address,st.receiver_mobile
				  FROM sales_trade st LEFT JOIN cfg_shop cs ON st.shop_id=cs.shop_id WHERE st.trade_id IN ($ids)";
			$data=$this->query($sql);
			return $data;
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name."-getSMSData-".$msg);
			SE(self::PDO_ERROR);
		}

	}

	//回传备注和标旗
	public function uploadRemarkAndFlag($trade_ids,$remark='',$flag=-1,$user_id){
		$sid=get_sid();
		$message=array();
		$error_list=array();
		$list=array();
		try{
			if($remark==''&& $flag==-1){
				SE('请填写有效的回传信息');
			}
			$trade_manage=ManagerFactory::getManager('Trade');
			foreach($trade_ids as $trade_id){
				if(empty($trade_id)){continue;}
				$trade=$this->getSalesTrade('trade_no',array('trade_id'=>array('eq',$trade_id)));
				$trade_manage->manual_upload_remark($sid,$user_id,$trade_id,$flag,$remark,$message,$error_list,1);
				$log_message='';
				if($message['status']==0){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'回传备注失败，'.$message['info']);
					$log_message='回传备注和标旗失败，'.$message['info'];
				}else if(!empty($error_list)){
					$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'回传备注失败，'.$error_list[0]['info']);
					$log_message='回传备注和标旗失败，'.$error_list[0]['info'];
				}else{
					$log_message='回传备注和标旗到线上订单';
					//更新系统订单的备注和标旗
					$this->execute("UPDATE sales_trade SET cs_remark=concat(ifnull(cs_remark,''),'".$remark."'),remark_flag=if({$flag}=-1,remark_flag,{$flag}) WHERE trade_id=%d",$trade_id);
				}
				$trade_log[]=array(
					'trade_id'=>$trade_id,
					'operator_id'=>$user_id,
					'type'=>1,
					'data'=>0,
					'message'=>$log_message,
					'created'=>date('y-m-d H:i:s',time()),
				);
			}
			D('SalesTradeLog')->addTradeLog($trade_log);
			$result=array(
				'status'=>empty($list)?0:2,//0全部成功，1异常错误，2部分成功
				'fail'=>$list,//失败提示信息
			);
		}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			SE($e->getMessage());
		}
		return $result;
	}
}                                      