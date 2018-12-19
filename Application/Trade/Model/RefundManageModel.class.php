<?php
namespace Trade\Model;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;
class RefundManageModel extends SalesRefundModel{
	
	/**
	 * 对话框datagrid
	 * @param unknown $dialog
	 * @param string $id_list
	 * @return multitype:multitype:string multitype:string boolean  Ambigous <multitype:, object>  multitype:string multitype:string boolean  Ambigous <multitype:, object, unknown, \Common\Common\Field>
	 */
	public function getDialogView($dialog,$id_list=null)
	{
		$datagrid=array();
		switch($dialog){
			case 'refund_edit':
				$datagrid['refund_order']=array(
				        'title'=>'退回货品',
						'id'=>$id_list['id_datagrid_refund'],
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar_refund']}",
								'pagination'=>false,
								'fitColumns'=>false,
								'fit'=>true,
								'methods'=>'onEndEdit:endEditRefundOrder,onBeginEdit:beginEditRefundOrder',
						),
						'fields' =>D('Setting/UserData')->getDatagridField('RefundManage','refund_order'),
				);
				$datagrid['return_order']=array(
						'title'=>'换出(补发)货品',
						'id'=>$id_list['id_datagrid_return'],
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar_return']}",
								'pagination'=>false,
								'fitColumns'=>true,
								'fit'=>true,
								'methods'=>'onEndEdit:endEditReturnOrder,onBeginEdit:beginEditReturnOrder',
						),
						'fields' =>get_field('RefundManage','return_order'),
				);
				break;
			case 'refund_exchange':
				$datagrid['spec']=array(
						'id'=>'refund_exchange_spec',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar']}",
								'pagination'=>false,
								'fitColumns'=>true,
						),
						'fields' => get_field('RefundManage','exchange')
				);
				$datagrid['order']=array(
						'id'=>'refund_exchange_order',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								// 'toolbar' => "#{$id_list['toolbar_order']}",
								'pagination'=>false,
								'fitColumns'=>false,
						),
						'fields' => get_field('RefundManage','order')
				);
				break;
		}
		return $datagrid;
	}
	
	/**
	 * 退换单updateTabData--方法集
	 * */
	public function getRefundOrder($id)
	{//$id->refund_id
		$id=intval($id);
		$data=array();
		try {
			$point_number =get_config_value('point_number',0);
			$order_num = 'CAST(sro.order_num AS DECIMAL(19,'.$point_number.')) order_num';
			$refund_num = 'CAST(sro.refund_num AS DECIMAL(19,'.$point_number.')) refund_num';
			$suite_num = 'CAST(sro.suite_num AS DECIMAL(19,'.$point_number.')) suite_num';
			$stockin_num = 'CAST(sro.stockin_num AS DECIMAL(19,'.$point_number.')) stockin_num';
			$sql='SELECT sro.refund_order_id,sro.platform_id,sro.oid,sro.tid,sro.trade_id,sro.trade_order_id,sro.trade_no,'.$order_num.',sro.price,sro.original_price,sro.discount,sro.paid,'.$refund_num.',sro.total_amount, sro.goods_id,gg.goods_no,sro.spec_id,sro.goods_name,sro.spec_name,sro.spec_no,sro.suite_id,sro.suite_name, '.$suite_num.','.$stockin_num.',sro.remark FROM sales_refund_order sro LEFT JOIN goods_goods gg ON sro.goods_id = gg.goods_id WHERE refund_id='.$id;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	public function getApiOrder($id)
	{//$id->refund_id
		$id=intval($id);
		$data=array();
		try {
			$point_number = get_config_value('point_number',0);
			$num = 'CAST(num AS DECIMAL(19,'.$point_number.')) num';
			$sql='SELECT rec_id,ato.oid,goods_name,spec_name,goods_id,spec_id,goods_no,spec_no,'.$num.',price,share_amount,(discount+adjust_amount-share_discount) discount FROM api_trade_order ato,(SELECT DISTINCT platform_id,oid FROM sales_refund_order WHERE refund_id='.$id.') sro WHERE ato.platform_id=sro.platform_id AND ato.oid=sro.oid';
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	public function getSalesTrade($id)
	{
		$id=intval($id);
		$data=array();
		try {//st_2.raw_goods_type_count, st_2.raw_goods_count, st_2.trade_mask,
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$point_number = get_config_value('point_number',0);
			$goods_count = 'CAST(st_2.goods_count AS DECIMAL(19,'.$point_number.')) goods_count';
			$sql_fields_str='SELECT st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, sh.shop_name AS shop_id , wh.name AS warehouse_id, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, st_2.delivery_term, st_2.freeze_reason, st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, '.$goods_count.', st_2.goods_type_count, st_2.single_spec_no, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address,IF('.$cfg_show_telno.'=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,\'****\')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, st_2.reserve, st_2.version_id, st_2.modified, st_2.created FROM sales_trade st_2';
			$sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.checker_id LEFT JOIN cfg_warehouse wh ON wh.warehouse_id=st_2.warehouse_id';
			$sql=$sql_fields_str.' INNER JOIN (SELECT r.trade_id FROM sales_refund r WHERE r.refund_id='.$id.') st_1 ON st_1.trade_id=st_2.trade_id '.$sql_left_join_str;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	public function getSwapApiTrade($id)
	{//换出原始单
		$id=intval($id);
		$data=array();
		try {
			if($id==0)
			{
				SE('没有换出原始单');
			}
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$sql="SELECT at.rec_id,at.platform_id,sh.shop_name AS shop_id ,at.tid,at.process_status,at.trade_status,
				  at.guarantee_mode,at.pay_status,at.delivery_term,at.pay_method,IF(at.platform_id=0,null,at.refund_status) refund_status,at.purchase_id,at.bad_reason,
				  at.trade_time,at.pay_time,at.buyer_message,at.remark,at.remark_flag,at.buyer_nick,at.pay_account, at.receiver_name,
				  at.receiver_country,at.receiver_area,at.receiver_ring,at.receiver_address,IF(".$cfg_show_telno."=0,at.receiver_mobile,INSERT( at.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,at.receiver_telno,INSERT(at.receiver_telno,4,4,'****')) receiver_telno,
				  at.receiver_zip, at.receiver_area,at.to_deliver_time,at.receivable,at.goods_amount,at.post_amount,at.other_amount,
				  at.discount,at.paid,at.platform_cost,at.received, at.dap_amount,at.cod_amount,at.pi_amount,at.refund_amount,
				  at.logistics_type,at.invoice_type,at.invoice_title,at.invoice_content,at.trade_from, at.fenxiao_type,at.fenxiao_nick,
				  he.fullname AS x_salesman_id,at.end_time,at.modified,at.created FROM api_trade `at` LEFT JOIN sales_refund sr
				  ON sr.swap_trade_id=at.rec_id LEFT JOIN cfg_shop sh ON sh.shop_id=at.shop_id LEFT JOIN hr_employee he
				  ON he.employee_id=at.x_salesman_id WHERE sr.refund_id=".$id;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}catch (\Exception $e){
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	public function getRefundLog($id)
	{
		$id=intval($id);
		$data=array();
		try {
			//$sql='SELECT srl_2.remark,he.account AS operator_id,srl_2.created FROM sales_refund_log srl_2 INNER JOIN(SELECT srl_1.rec_id FROM sales_refund_log srl_1 WHERE srl_1.refund_id='.$id.' ORDER BY srl_1.rec_id DESC) srl_3 ON srl_3.rec_id=srl_2.rec_id LEFT JOIN hr_employee he ON srl_2.operator_id=he.employee_id';
			$sql='SELECT srl.remark AS message,srl.type,he.fullname AS operator_id,srl.created FROM sales_refund_log srl LEFT JOIN hr_employee he ON srl.operator_id=he.employee_id WHERE srl.refund_id='.$id.' ORDER BY srl.rec_id DESC ';
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}	
	
	/**
	 * gaosong need and he did this function
	 * @param integer $id
	 * @return multitype:Ambigous <multitype:number multitype: , multitype:number \Think\mixed > Ambigous <multitype:, \Think\mixed>
	 */
	public function getSalesRefundInfo($id)
	{//gaosong did this
		$id=intval($id);
		try{
			$point_number = get_config_value('point_number',0);
			
			$sql_sales_refund = "select sr.refund_no `src_order_no`,sr.logistics_no `logistics_no`,sr.warehouse_id as `warehouse_id` from sales_refund sr where sr.refund_id = %d";
			$res_sales_refund = $this->query($sql_sales_refund,$id);
			$sql_sales_refund_order = "select sro.refund_order_id id,CAST(SUM((sro.refund_num-sro.stockin_num)) AS DECIMAL(19,".$point_number.")) AS expect_num,sro.cost_price,CAST(IFNULL(sro.price,0) AS DECIMAL(19,4)) AS src_price,CAST(0 AS DECIMAL(19,".$point_number.")) AS num,"
		                          ." CAST(IF(sro.cost_price,IF(IFNULL(sro.price,0),CAST(sro.cost_price/sro.price AS DECIMAL(19,4)),1),1) AS DECIMAL(19,4)) AS rebate,CAST(0 AS DECIMAL(19,4)) AS tax_rate,CAST(0 AS DECIMAL(19,4)) AS total_cost,sro.cost_price AS tax_price,"
		                          ." gs.spec_id,gs.spec_name,gs.lowest_price,gs.market_price,gs.retail_price,gs.spec_no,gs.spec_code ,gs.barcode,gs.unit base_unit_id,"
		                          ." gg.goods_no,gg.goods_name,gg.brand_id,gg.spec_count,"
		                          ." cgu.name as unit_name,"
							      ." (CASE  WHEN ss.last_position_id THEN ss.last_position_id  ELSE -{$res_sales_refund[0]['warehouse_id']} END) AS position_id,"
                        		  ." (CASE  WHEN ss.last_position_id THEN cwp.position_no ELSE cwp2.position_no END) AS position_no,"
								  ." IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated,"
		                          ." gb.brand_name"
		                          ." from sales_refund_order sro"
		                          ." left join goods_spec gs on gs.spec_id = sro.spec_id"
		                          ." left join goods_goods gg on gg.goods_id = gs.goods_id"
		                          ." left join goods_brand gb on gb.brand_id = gg.brand_id"
		                          ." left join cfg_goods_unit cgu on cgu.rec_id = gs.unit"
		                          ." LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$res_sales_refund[0]['warehouse_id']})"
		                          ." LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)"
		                          ." LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$res_sales_refund[0]['warehouse_id']})"
		                          ." where (sro.refund_num-sro.stockin_num)>0 AND sro.refund_id =%d  group by sro.refund_order_id";
			$res_sales_refund_order = $this->query($sql_sales_refund_order,$id);
			$data=array('total'=>count($res_sales_refund_order),'rows'=>$res_sales_refund_order);
			$form_data = $res_sales_refund[0];
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			$form_data=array();
			\Think\Log::write($e->getMessage());
		}
		$info=array('data'=>$data,'form'=>$form_data);
		return $info;
	}
	
	
	/**
	 * 新建--编辑--退换单
	 */
	public function addRefundOrders($refund_orders,$trade,$refund_id,&$refund_log,$user_id)
	{
		$refund_id=intval($refund_id);
		$sql_error_info='';
		try {
			$arr_tmp_order=array();
            $goods_num_arr = array();
            $temp_res_orders = array();

            foreach ($refund_orders as $o)
			{
				if(intval($o['rec_id'])==0)
				{
                    SE('未匹配货品');
                }
				// if(intval($o['refund_num'])>intval($o['order_num']))
				// {
				// 	SE('退货量不能大于实发数量');
				// }								
				$cost_price=M('stockout_order')->alias('so')
											   ->field('sod.cost_price')
				                               ->join('LEFT JOIN stockout_order_detail sod ON so.stockout_id = sod.stockout_id')
				                               ->where('so.src_order_type=1 AND so.src_order_id='.$trade['trade_id'].' AND sod.src_order_detail_id='.intval($o['rec_id']))
				                               ->find();
				if($cost_price==''){
					$cost_price=M('stockout_order_history')->alias('so')
					->field('sod.cost_price')
					->join('LEFT JOIN stockout_order_detail_history sod ON so.stockout_id = sod.stockout_id')
					->where('so.src_order_type=1 AND so.src_order_id='.$trade['trade_id'].' AND sod.src_order_detail_id='.intval($o['rec_id']))
					->find();
				}
				$arr_order_id[]=$o['rec_id'];
				$o['cost_price']=$cost_price['cost_price'];
				$arr_tmp_order[strval($o['rec_id'])][]=$o;//字典映射
			}
			$refund_orders=array();
			$res_orders=D('Trade/SalesTradeOrder')->getSalesTradeOrderList(
					'sto.rec_id,sto.shop_id,sto.platform_id,sto.src_oid,sto.src_tid,sto.share_price,sto.actual_num, sto.goods_id,sto.spec_id,sto.goods_name,sto.spec_name,sto.spec_no,sto.price original_price,sto.discount,sto.paid, sto.suite_id,sto.suite_no,sto.suite_name,sto.suite_num,sto.share_amount',
					array('sto.rec_id'=>array('in',$arr_order_id)),
					'sto'
			);
			if(empty($res_orders)){
				$ids='';
				foreach ($arr_order_id as $o){
					$ids.=$o.',';
				}
				
				$ids=substr($ids, 0,strlen($ids)-1);
				$res_orders=$this->query('SELECT hto.rec_id,hto.shop_id,hto.platform_id,hto.src_oid,hto.src_tid,hto.share_price,hto.actual_num, hto.goods_id,hto.spec_id,hto.goods_name,hto.spec_name,hto.spec_no,hto.price original_price,hto.discount,hto.paid, hto.suite_id,hto.suite_no,hto.suite_name,hto.suite_num,hto.share_amount 
						FROM sales_trade_order_history hto WHERE hto.rec_id in ('.$ids.')');
			}


            foreach ($arr_tmp_order as $key => $val) {
                $goods_num_arr[$key] = count($val);
            }
            foreach ($goods_num_arr as $nk => $nv) {

                foreach($res_orders as $rk => $ro) {
                    if($ro['rec_id'] == $nk){
                        for ($i=0;$i<$nv;$i++){
                            $ro['goods_index'] = $i;
                            $temp_res_orders[] = $ro;
                        }
                    }
                }
            }

            foreach($temp_res_orders as $k => $o)
			{
				$o['rec_id']=strval($o['rec_id']);//作为下标
				$refund_orders[]=array(
						'refund_id'=>$refund_id,
						'shop_id'=>$o['shop_id'],
						'platform_id'=>$o['platform_id'],
						'oid'=>$o['src_oid'],
						'tid'=>$o['src_tid'],
						'trade_id'=>$trade['trade_id'],
						'trade_order_id'=>intval($o['rec_id']),
						'trade_no'=>$trade['trade_no'],
						'order_num'=>$arr_tmp_order[$o['rec_id']][$o['goods_index']]['order_num'],
						'refund_num'=>$arr_tmp_order[$o['rec_id']][$o['goods_index']]['refund_num'],
						'price'=>$o['share_price'],
						'original_price'=>set_default_value($o['original_price'],0),
						'discount'=>set_default_value($o['discount'],0),
						'paid'=>set_default_value($o['paid'],0),
						'cost_price'=>$arr_tmp_order[$o['rec_id']][$o['goods_index']]['cost_price']==0?$o['share_price']:$arr_tmp_order[$o['rec_id']][$o['goods_index']]['cost_price'],
						'total_amount'=>$arr_tmp_order[$o['rec_id']][$o['goods_index']]['refund_num']*$o['share_price'],
						'goods_id'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['goods_id'],$o['goods_id']),
						'spec_id'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['spec_id'],0),
						'spec_no'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['spec_no'], ''),
						'goods_name'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['goods_name'],''),
						'spec_name'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['spec_name'],''),
						'suite_id'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['suite_id'],0),
						'suite_no'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['suite_no'], ''),
						'suite_name'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['suite_name'],''),
						'suite_num'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['suite_num'],0),
						'remark'=>set_default_value($arr_tmp_order[$o['rec_id']][$o['goods_index']]['remark'],''),
						'created'=>array('exp','NOW()'),
				);
				$refund_log[]=array(
						'refund_id'=>$refund_id,
						'type'=>52,
						'operator_id'=>$user_id,
						'remark'=>'增加退款或破损货品 货品名称：'.$o['goods_name'].' 商家编码：'.$o['spec_no'].' 数量：'.$arr_tmp_order[$o['rec_id']][$o['goods_index']]['refund_num'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			$sql_error_info='add_sales_refund_order';
            D('Trade/SalesRefundOrder')->addSalesRefundOrder($refund_orders);
		} catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	public function addReturnOrders($return_orders,$refund_id,&$refund_log,$user_id)
	{
		$refund_id=intval($refund_id);
		$sql_error_info='';
		try {
			foreach ($return_orders as $o)
			{
				$arr_return_orders[]=array(
						'refund_id'=>$refund_id,
						'target_type'=>$o['is_suite']==1?2:1,
						'target_id'=>$o['id'],
						'goods_name'=>set_default_value($o['goods_name'],''),
						'spec_name'=>set_default_value($o['spec_name'], ''),
						'merchant_no'=>set_default_value($o['merchant_no'], ''),
						'num'=>set_default_value($o['num'], 1),
						'retail_price'=>$o['retail_price'],
						'remark'=>set_default_value($o['remark'], ''),
						'created'=>array('exp','NOW()'),
				);
				$refund_log[]=array(
						'refund_id'=>$refund_id,
						'type'=>51,
						'operator_id'=>$user_id,
						'remark'=>'增加换出或补发货品 '.($o['is_suite']==0?'单品':'组合装').' 货品名称：'.$o['goods_name'].' 商家编码：'.$o['merchant_no'].' 数量：'.$o['num'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			$sql_error_info='add_sales_refund_out_goods';
			D('Trade/SalesRefundOutGoods')->addSalesRefundOutGoods($arr_return_orders);
		} catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	public function editRefundOrders($refund,$refund_orders,&$refund_log,$user_id)
	{
		$sql_error_info='';
		$refund_order_db=D('SalesRefundOrder');
		try {
			if (!empty($refund['flag'])&&$refund['flag'])
			{//重新选择了退货货品
				$sales_refund_orders=$refund_order_db->getSalesRefundOrderList(
						'refund_order_id,trade_order_id,goods_name,spec_no',
						array('refund_id'=>array('eq',$refund['refund_id']))
						);
				$res_refund_orders=array();
				$new_refund_orders=array();
				foreach($sales_refund_orders as $res)
				{
					$res_refund_orders[$refund['refund_id'].'_'.$res['trade_order_id']]=array(
							'trade_order_id'=>$res['trade_order_id'],
							'refund_order_id'=>$res['refund_order_id']
					);
				}
				foreach($refund_orders as $o)
				{
					// if(intval($o['refund_num'])>intval($o['order_num']))
					// {
					// 	SE('退货量不能大于实发数量');
					// }
					$new_refund_orders[$refund['refund_id'].'_'.$o['rec_id']]=array(
							'trade_order_id'=>$o['rec_id'],
					);
					if(!empty($res_refund_orders[$refund['refund_id'].'_'.$o['rec_id']]))
					{
						$arr_refund_update=array(
								'refund_order_id'=>intval($res_refund_orders[$refund['refund_id'].'_'.$o['rec_id']]['refund_order_id']),
								'refund_num'=>set_default_value($o['refund_num'],0),
								'remark'=>set_default_value($o['remark'], ''),
						);
						if($res_refund_orders[$refund['refund_id'].'_'.$o['rec_id']]['trade_order_id']!=0)
						{
							$res_order=D('SalesTradeOrder')->getSalesTradeOrderList('share_price',array('rec_id'=>array('eq',$res_refund_orders[$refund['refund_id'].'_'.$o['rec_id']]['trade_order_id'])));
							$arr_refund_update['total_amount']=($refund['type']==4?0:$arr_refund_update['refund_num']*$res_order[0]['share_price']);
						}
						$sql_error_info='update_sales_refund_order';
						$refund_order_db->save($arr_refund_update);
					}else{
						$refund_order=array();
						array_push($refund_order, $o);
						$this->addRefundOrders($refund_order, array('trade_id'=>$refund['trade_id'],'trade_no'=>$refund['trade_no']), $refund['refund_id'],$refund_log,$user_id);
					}
				}
				foreach($sales_refund_orders as $res)
				{
					if(empty($new_refund_orders[$refund['refund_id'].'_'.$res['trade_order_id']]))
					{
						$sql_error_info='delete_sales_refund_order';
						$refund_order_db->where(array('refund_id'=>array('eq',$refund['refund_id']) ,'trade_order_id'=>array('eq',$res['trade_order_id'])))
										->delete();
						$refund_log[]=array(
								'refund_id'=>$refund['refund_id'],
								'type'=>52,
								'operator_id'=>$user_id,
								'remark'=>'删除退款货品 货品名称：'.$res['goods_name'].' 商家编码：'.$res['spec_no'],
								'created'=>date('y-m-d H:i:s',time()),
						);
					}
				}
			}else{//编辑了退货货品
				if(!empty($refund_orders['update']))
				{//更新
					foreach($refund_orders['update'] as $o)
					{
						// if(intval($o['refund_num'])>intval($o['order_num']))
						// {
						// 	SE('退货量不能大于实发数量');
						// }
						$arr_update_ids[]=$o['refund_order_id'];
					}
					$sql_error_info='get_sales_refund_order';
					$res_refund_orders=$refund_order_db->getSalesRefundOrderList(
							'refund_order_id,trade_order_id,refund_num,remark',
							array('refund_order_id'=>array('in',$arr_update_ids))
					);
					foreach ($res_refund_orders as $o)
					{
						$arr_refund_orders[strval($o['refund_order_id'])]=$o;
					}
					foreach($refund_orders['update'] as $o)
					{
						$o['refund_order_id']=strval($o['refund_order_id']);
						$arr_refund_update=array(
								'refund_order_id'=>intval($o['refund_order_id']),
								'refund_num'=>set_default_value($o['refund_num'],0),
								'remark'=>set_default_value($o['remark'], ''),
								'edit_mask'=>0,
								'tag'=>0,
						);
						if($res_refund_orders[$o['refund_order_id']]['trade_order_id']!=0)
						{
							$res_order=D('SalesTradeOrder')->getSalesTradeOrderList('share_price',array('rec_id'=>array('eq',$res_refund_orders[$o['refund_order_id']]['trade_order_id'])));
							$arr_refund_update['total_amount']=($refund['type']==4?0:$arr_refund_update['refund_num']*$res_order['share_price']);
						}
						$sql_error_info='update_sales_refund_order';
						$refund_order_db->save($arr_refund_update);
						$refund_log[]=array(
								'refund_id'=>$refund['refund_id'],
								'type'=>52,
								'operator_id'=>$user_id,
								'remark'=>'修改退货货品  货品名称：'.$o['goods_name'].' 商家编码：'.$o['spec_no']
								.($res_refund_orders[$o['refund_order_id']]['refund_num']==$o['refund_num']?'':' 数量:'.$res_refund_orders[$o['refund_order_id']]['refund_num'].'-->'.$o['refund_num'])
								.($res_refund_orders[$o['refund_order_id']]['remark']==$o['remark']?'':' 备注:'.$res_refund_orders[$o['refund_order_id']]['remark'].'-->'.$o['remark']),
								'created'=>date('y-m-d H:i:s',time()),
						);
					}
				}
				if(!empty($refund_orders['delete']))
				{//删除
					foreach($refund_orders['delete'] as $o)
					{
						$arr_refund_del[]=$o['refund_order_id'];
						$refund_log[]=array(
								'refund_id'=>$refund['refund_id'],
								'type'=>52,
								'operator_id'=>$user_id,
								'remark'=>'删除退款货品 货品名称：'.$o['goods_name'].' 商家编码：'.$o['spec_no'],
								'created'=>date('y-m-d H:i:s',time()),
						);
					}
					$sql_error_info='delete_sales_refund_order';
					$refund_order_db->where(array('refund_order_id'=>array('in',$arr_refund_del)))
									->delete();
				}
			}

		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			SE($e->getMessage());
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	public function editReturnOrders($return_orders,$refund_id,&$refund_log,$user_id)
	{
		$refund_id=intval($refund_id);
		$sql_error_info='';
		try {
			$return_order_db=D('SalesRefundOutGoods');
			if (!empty($return_orders['add']))
			{//新增
				$this->addReturnOrders($return_orders['add'],$refund_id,$refund_log,$user_id);
			}
			if (!empty($return_orders['update']))
			{//更新
				foreach($return_orders['update'] as $o)
				{
					$arr_update_ids[]=$o['rec_id'];
				}
				$sql_error_info='get_return_order';
				$res_return_orders=$return_order_db->getSalesRefundOutGoods(
						'rec_id,retail_price,num,remark',
						array('rec_id'=>array('in',$arr_update_ids))
				);
				foreach ($res_return_orders as $o)
				{
					$arr_return_orders[strval($o['rec_id'])]=$o;
				}
				foreach($return_orders['update'] as $o)
				{
					$o['rec_id']=strval($o['rec_id']);
					$return_order=array(
							'rec_id'=>$o['rec_id'],
							'retail_price'=>set_default_value($o['retail_price'], 0),
							'num'=>set_default_value($o['num'], 0),
							'remark'=>set_default_value($o['num'], ''),
					);
					$sql_error_info='update_return_order';
					$return_order_db->save($return_order);
					$refund_log[]=array(
							'refund_id'=>$refund_id,
							'type'=>51,
							'operator_id'=>$user_id,
							'remark'=>'修改换出货品  货品名称：'.$o['goods_name'].' 商家编码：'.$o['merchant_no']
							.($res_return_orders[$o['rec_id']]['retail_price']==$o['retail_price']?'':' 售价:'.$res_return_orders[$o['rec_id']]['retail_price'].'-->'.$o['retail_price'])
							.($res_return_orders[$o['rec_id']]['num']==$o['num']?'':' 数量:'.$res_return_orders[$o['rec_id']]['num'].'-->'.$o['num'])
							.($res_return_orders[$o['rec_id']]['remark']==$o['remark']?'':' 备注:'.$res_return_orders[$o['rec_id']]['remark'].'-->'.$o['remark']),
							'created'=>date('y-m-d H:i:s',time()),
					);
				}
			}
			if (!empty($return_orders['delete']))
			{//删除
				foreach($return_orders['delete'] as $o)
				{
					$arr_return_del[]=$o['rec_id'];
					$refund_log[]=array(
							'refund_id'=>$refund_id,
							'type'=>51,
							'operator_id'=>$user_id,
							'remark'=>'删除换出货品 货品名称：'.$o['goods_name'].' 商家编码：'.$o['merchant_no'],
							'created'=>date('y-m-d H:i:s',time()),
					);
				}
				$sql_error_info='delete_return_order';
				$return_order_db->where(array('rec_id'=>array('in',$arr_return_del)))->delete();
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			SE($e->getMessage());
		}
	}
	
	public function addRefund($refund,$refund_orders,$return_orders,$user_id,$is_api=0)
	{
		$sql_error_info='';
		$refund_id='';
		$is_rollback=false;
		try {
			if ($refund['type']==5) {
				$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
				$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			}
			$is_rollback=true;
			$this->startTrans();
			$this->addRefundNoTrans($refund,$refund_orders,$return_orders,$user_id,$is_api,$refund_id);
			$this->commit();
			$res_cfg_value=get_config_value('refund_auto_agree');
			if($res_cfg_value==1){//开启了自动同意配置
			 	$agree_result=$this->agreeRefund($refund_id,$user_id);
			 	if ($agree_result['status']==0) {
			 		$agree_result['status']=1;
			 	}elseif($agree_result['status']==1){
			 		$agree_result['status']=0;
			 	}
			 	return $agree_result;
			}					
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
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

	public function addRefundNoTrans($refund,$refund_orders,$return_orders,$user_id,$is_api=0,&$refund_id='')
	{
		$sql_error_info='';
		try{
			if(intval($refund['trade_id'])==0)
			{
				SE('无效订单不能新建退换单');
			}
			//--------------------添加sales_refund 退货单信息-----------------------------------
			$res_trade=D('Trade/Trade')->getSalesTrade('trade_id,trade_no,trade_status,buyer_nick, shop_id, receiver_name,receiver_area,receiver_address,customer_id,receiver_mobile,receiver_telno,receiver_province,receiver_city,receiver_district,platform_id',array('trade_id'=>array('eq',$refund['trade_id'])));
			if(empty($res_trade)){
				$res_trade=D('Trade/HistorySalesTrade')->getHistoryTrade('trade_id,trade_no,trade_status,buyer_nick, shop_id, receiver_name,receiver_area,receiver_address,customer_id,receiver_mobile,receiver_telno,receiver_province,receiver_city,receiver_district,platform_id',array('trade_id'=>array('eq',$refund['trade_id'])));
			}
			if($res_trade['trade_status']<95)
			{
				SE('订单未发货，无法创建退款订单');
			}
			$sql_error_info='get_warehouse_type';
			$res_warehouse=M('cfg_warehouse')->field('type')->where(array('warehouse_id'=>array('eq',$refund['warehouse_id'])))->find();
			if(empty($res_warehouse))
			{
				$res_warehouse['type']=0;
			}
			if($res_warehouse['type'] == 11){
				$process_status = 63;
			}else{
				$process_status = 20;
			}
			$arr_refund=array(
					'refund_no'=>get_sys_no('refund',1),
					'guarantee_mode'=>2,
					'type'=>set_default_value($refund['type'], 1),
					'status'=>0,
					'process_status'=>$process_status,
					'platform_id'=>$res_trade['platform_id'],
					'tid'=>$refund['tid'],
					'trade_id'=>$res_trade['trade_id'],
					'trade_no'=>$res_trade['trade_no'],
					'shop_id'=>$refund['shop_id']!=0?$refund['shop_id']:$res_trade['shop_id'],
					'refund_amount'=>($refund['flow_type']==2&&$refund['type']==3)?-$refund['refund_amount']:$refund['refund_amount'],//$refund['refund_amount'],//flow_type==2 -refund_amount
					'goods_amount'=>$refund['goods_amount'],
					'exchange_amount'=>set_default_value($refund['exchange_amount'], 0),
					'post_amount'=>set_default_value($refund['post_amount'],0),
					'refund_time'=>array('exp','NOW()'),
					'reason_id'=>$refund['reason_id'],
					'flag_id'=>set_default_value($refund['flag_id'],0),
					'buyer_nick'=>$res_trade['buyer_nick'],
					'logistics_name'=>set_default_value($refund['logistics_name'],''),
					'logistics_no'=>set_default_value($refund['logistics_no'],''),
					'from_type'=>2,
					'src_no'=>set_default_value($refund['src_no'], ''),
					'pay_account'=>set_default_value($refund['pay_account'],''),
					'receiver_name'=>set_default_value($res_trade['receiver_name'],''),
					'receiver_address'=>set_default_value($res_trade['receiver_address'],''),
					'remark'=>set_default_value($refund['remark'],''),
					'operator_id'=>$user_id,
					'customer_id'=>$res_trade['customer_id'],
					'return_name'=>set_default_value($res_trade['receiver_name'],''),
					'return_mobile'=>set_default_value($res_trade['receiver_mobile'],''),
					'return_telno'=>set_default_value($res_trade['receiver_telno'],''),
					'swap_receiver'=>set_default_value($refund['swap_receiver'],''),
					'swap_mobile'=>set_default_value($refund['swap_mobile'],$res_trade['receiver_mobile']),
					'swap_telno'=>set_default_value($refund['swap_telno'],$res_trade['receiver_telno']),
					'swap_province'=>set_default_value($refund['swap_province'],0),
					'swap_city'=>set_default_value($refund['swap_city'],0),
					'swap_district'=>set_default_value($refund['swap_district'],0),
					'swap_area'=>set_default_value($refund['swap_area'],$res_trade['receiver_area']),
					'swap_address'=>set_default_value($refund['swap_address'],''),
					'swap_warehouse_id'=>set_default_value($refund['swap_warehouse_id'],0),
					'swap_logistics_id'=>set_default_value($refund['swap_logistics_id'],0),
					'warehouse_id'=>set_default_value($refund['warehouse_id'],0),
					'stockin_pre_no'=>set_default_value($refund['stockin_pre_no'],''),
					'warehouse_type'=>set_default_value($res_warehouse['type'],0),
					'created'=>array('exp','NOW()'),
					'direct_refund_amount'=>($refund['flow_type']==2&&$refund['type']==3)?-$refund['direct_refund_amount']:set_default_value($refund['direct_refund_amount'],0),//set_default_value($refund['direct_refund_amount'],0),
					'guarante_refund_amount'=>($refund['flow_type']==2&&$refund['type']==3)?-$refund['guarante_refund_amount']:set_default_value($refund['guarante_refund_amount'],0),//set_default_value($refund['guarante_refund_amount'],0),
			);			$sql_error_info='add_refund';
			$refund_id=$this->addSalesRefund($arr_refund);
			$refund_log[]=array(
				'refund_id'=>$refund_id,
				'type'=>1,
				'operator_id'=>$user_id,
				'remark'=>'手工建单',
				'created'=>date('y-m-d H:i:s',time()),
			);
			//-------------添加sales_refund_order 退货货品---------------------
			$this->addRefundOrders($refund_orders, $res_trade, $refund_id,$refund_log,$user_id);
			//-------------添加sales_refund_out_goods 换出货品-----------------
			if($refund['type']==3)
			{
				if(empty($return_orders))
				{
					SE('换货不能没有换货货品');
				}
				$this->addReturnOrders($return_orders, $refund_id,$refund_log,$user_id);
			}
			//-------------添加sales_refund_out_goods 补发货品-----------------
			if($refund['type']==5)
			{
				if(empty($return_orders))
				{
					SE('破损补发不能没有补发货品');
				}
				$this->addReturnOrders($return_orders, $refund_id,$refund_log,$user_id);
			}
            //--------------添加关联预入库单逻辑:校验是否包含其他货品或者已经做过关联了其他退换单---
            if($refund['type']!=4&&$refund['type']!=5)
            {
                $this->checkStockinPre($refund_id);
            }
			D('Trade/SalesRefundLog')->addSalesRefundLog($refund_log);
			//如果是原始退款单递交的，将原始退款单状态置为已递交
			if($is_api){
				$sql_error_info='update_api_refund';
				D('Trade/OriginalRefund')->execute("UPDATE api_refund SET process_status=20 WHERE refund_no='".$arr_refund['src_no']."' AND platform_id='".$arr_refund['platform_id']."'");
			}
			// 标记订单为售后订单
			$flag_id = D('Setting/Flag')->getFlagId('售后订单',1);
			D('Trade/Trade')->updateSalesTrade(array('flag_id'=>$flag_id), array('trade_id'=>array('eq',$refund['trade_id'])));
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			SE($e->getMessage());
		}
	}
	public function updateRefund($refund,$refund_orders,$return_orders,$user_id)
	{
		$sql_error_info='';
		$is_rollback=false;
		try {
			$is_rollback=true;
			if ($refund['type']==5) {//创建临时表-调用货品映射前需要创建临时表
				$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
				$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			}			
			$this->startTrans();
			$sql_error_info='get_sales_refund';
			$res_refund=$this->getSalesRefundOnLock(
					'guarantee_mode,status,process_status,type,from_type,refund_no,trade_no,trade_id ,post_amount, tid,logistics_name,logistics_no,buyer_nick,swap_receiver,swap_mobile,swap_telno,swap_area,swap_address,warehouse_id,reason_id,flag_id,remark,guarante_refund_amount,direct_refund_amount',
					 array('refund_id'=>array('eq',$refund['refund_id']))
			);
			if(empty($res_refund))
			{
				SE('无效退换单');
			}
			//----------------更新--sales_refund----------------------------------
			$res_trade=D('Trade')->getSalesTrade(
					'platform_id,trade_id,trade_status,trade_no',
					array('trade_id'=>array('eq',$refund['trade_id']))
			);
			if($res_trade['trade_status']<95)
			{
				SE('订单未发货，无法创建退款订单');
			}
			$arr_refund=$refund;
			if($res_refund['guarantee_mode']==1||$res_refund['process_status']>20)
			{
				unset($arr_refund['buyer_nick']);
				unset($arr_refund['trade_no']);
				unset($arr_refund['trade_id']);
				unset($arr_refund['src_no']);
				unset($arr_refund['warehouse_id']);
			}else{
				if(isset($refund['warehouse_id'])&&$refund['warehouse_id']!=$res_refund['warehouse_id'])
				{
					$sql_error_info='get_cfg_warehouse';
					$res_warehouse=M('cfg_warehouse')->field('type')->where(array('warehouse_id'=>array('eq',$refund['warehouse_id'])))->find();
					$arr_refund['warehouse_id']=set_default_value($arr_refund['warehouse_id'],0);
					$arr_refund['warehouse_type']=set_default_value($res_warehouse['type'],0);
				}else{
					$refund['warehouse_id']=$res_refund['warehouse_id'];
				}
				$arr_refund['src_no']=set_default_value($arr_refund['src_no'],'');
				$arr_refund['buyer_nick']=set_default_value($arr_refund['buyer_nick'],'');
				$arr_refund['trade_no']=set_default_value($arr_refund['trade_no'],$res_refund['trade_no']);
				$arr_refund['trade_id']=set_default_value($arr_refund['trade_id'],$res_refund['trade_id']);
				$refund['trade_no']=$arr_refund['trade_no'];
				$refund['trade_id']=$arr_refund['trade_id'];
			}
			$arr_refund['tid']=set_default_value($arr_refund['tid'],'');
			$arr_refund['platform_id']=$res_trade['platform_id'];
			$arr_refund['refund_amount']=($arr_refund['flow_type']==2&&$arr_refund['type']==3)?-$arr_refund['refund_amount']:$arr_refund['refund_amount'];
			$arr_refund['direct_refund_amount']=($arr_refund['flow_type']==2&&$arr_refund['type']==3)?-$arr_refund['direct_refund_amount']:$arr_refund['direct_refund_amount'];
			$arr_refund['guarante_refund_amount']=($arr_refund['flow_type']==2&&$arr_refund['type']==3)?-$arr_refund['guarante_refund_amount']:$arr_refund['guarante_refund_amount'];
			$arr_refund['logistics_name']=set_default_value($arr_refund['logistics_name'],'');
			$arr_refund['logistics_no']=set_default_value($arr_refund['logistics_no'],'');
			$arr_refund['swap_receiver']=set_default_value($arr_refund['swap_receiver'],'');
			$arr_refund['swap_mobile']=set_default_value($arr_refund['swap_mobile'],$res_refund['swap_mobile']);
			$arr_refund['swap_telno']=set_default_value($arr_refund['swap_telno'],$res_refund['swap_telno']);
			$arr_refund['swap_area']=set_default_value($arr_refund['swap_area'],'无 无 无');
			$arr_refund['swap_province']=set_default_value($arr_refund['swap_province'],0);
			$arr_refund['swap_city']=set_default_value($arr_refund['swap_city'],0);
			$arr_refund['swap_district']=set_default_value($arr_refund['swap_district'],0);
			$arr_refund['remark']=set_default_value($arr_refund['remark'],'');
			$arr_refund['stockin_pre_no']=set_default_value($arr_refund['stockin_pre_no'],'');
			unset($arr_refund['goods_refund_count']);
			unset($arr_refund['goods_return_count']);
			unset($arr_refund['flag']);
			$sql_error_info='update_sales_refund';
			$this->where(array('refund_id'=>array('eq',$arr_refund['refund_id'])))->save($arr_refund);
			$arr_refund_orders=array(
					'edit_mask'=>1,
					'tag'=>array('exp','UNIX_TIMESTAMP()'),
			);
			$refund_order_db=D('SalesRefundOrder');
			$refund_order_db->updateSalesRefundOrder(
					$arr_refund_orders,
					array('refund_id'=>array('eq',$arr_refund['refund_id']))
			);
			//---------更新退换单--日志记录---------------------------------------
			if($arr_refund['type']!=$res_refund['type'])
			{
				$arr_tmp=array('退款','退货','换货','退款不退货','破损补发');
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>62,
						'operator_id'=>$user_id,
						'remark'=>'修改退换类型:'.$arr_tmp[$res_refund['type']-1].'-->'.$arr_tmp[$arr_refund['type']-1],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['trade_no']!=$res_refund['trade_no'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>63,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单订单:'.$res_refund['trade_no'].'-->'.$arr_refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['pay_account']!=$res_refund['pay_account'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>63,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单订单:'.$res_refund['pay_account'].'-->'.$arr_refund['pay_account'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['warehouse_id']!=$res_refund['warehouse_id'])
			{
				$res_dict_arr=M('cfg_warehouse')->field('warehouse_id AS id,name')->where(array('warehouse_id'=>array('in',array($arr_refund['warehouse_id'],$res_refund['warehouse_id']))))->select();
				$res_dict_arr=UtilTool::array2dict($res_dict_arr);
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>67,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单退货入库:'.$res_dict_arr[$res_refund['warehouse_id']].'-->'.$res_dict_arr[$arr_refund['warehouse_id']],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['logistics_name']!=$res_refund['logistics_name'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>68,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单物流:'.$res_refund['logistics_name'].'-->'.$arr_refund['logistics_name'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['logistics_no']!=$res_refund['logistics_no'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>68,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单物流单号:'.$res_refund['logistics_no'].'-->'.$arr_refund['logistics_no'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['tid']!=$res_refund['tid'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>63,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单原始单:'.$res_refund['tid'].'-->'.$arr_refund['tid'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['swap_receiver']!=$res_refund['swap_receiver'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>69,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单收货人:'.$res_refund['swap_receiver'].'-->'.$arr_refund['swap_receiver'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['swap_mobile']!=$res_refund['swap_mobile'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>69,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单收货手机:'.$res_refund['swap_mobile'].'-->'.$arr_refund['swap_mobile'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['swap_telno']!=$res_refund['swap_telno'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>69,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单收货电话:'.$res_refund['swap_telno'].'-->'.$arr_refund['swap_telno'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['swap_area']!=$res_refund['swap_area'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>69,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单收货地区:'.$res_refund['swap_area'].'-->'.$arr_refund['swap_area'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['swap_address']!=$res_refund['swap_address'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>69,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单收货地址:'.$res_refund['swap_address'].'-->'.$arr_refund['swap_address'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['reason_id']!=$res_refund['reason_id'])
			{
				$res_dict_arr=M('cfg_oper_reason')->field('reason_id AS id,title AS name')->where(array('reason_id'=>array('in',array($arr_refund['reason_id'],$res_refund['warehouse_id']))))->select();
				$res_dict_arr=UtilTool::array2dict($res_dict_arr);
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>64,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单原因:'.$res_dict_arr[$res_refund['reason_id']].'-->'.$res_dict_arr[$arr_refund['reason_id']],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['flag_id']!=$res_refund['flag_id'])
			{
				$res_dict_arr=D('Setting/Flag')->getFlagData(9,'list');
				$res_dict_arr=UtilTool::array2dict($res_dict_arr);
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>68,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单标记:'.$res_dict_arr[$res_refund['flag_id']].'-->'.$res_dict_arr[$arr_refund['flag_id']],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['remark']!=$res_refund['remark'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>66,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单备注:'.$res_refund['remark'].'-->'.$arr_refund['remark'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['guarante_refund_amount']!=$res_refund['guarante_refund_amount'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>65,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单平台退款:'.$res_refund['guarante_refund_amount'].'-->'.(($arr_refund['flow_type']==2&&$arr_refund['type']==3)?-$arr_refund['guarante_refund_amount']:$arr_refund['guarante_refund_amount']),
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			if($arr_refund['direct_refund_amount']!=$res_refund['direct_refund_amount'])
			{
				$refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'type'=>65,
						'operator_id'=>$user_id,
						'remark'=>'修改退换单线下退款:'.$res_refund['direct_refund_amount'].'-->'.(($arr_refund['flow_type']==2&&$arr_refund['type']==3)?-$arr_refund['direct_refund_amount']:$arr_refund['direct_refund_amount']),
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			//---------编辑--退货货品------------------------------------------------
			$this->editRefundOrders($refund, $refund_orders, $refund_log, $user_id);
			//------------------编辑--换出货品-------------------------------------
			$this->editReturnOrders($return_orders, $refund['refund_id'], $refund_log, $user_id);
            //--------------添加关联预入库单逻辑:校验是否包含其他货品或者已经做过关联了其他退换单---
            if($arr_refund['type']!=4&&$arr_refund['type']!=5)
            {
                $this->checkStockinPre($refund['refund_id']);
            }
            D('SalesRefundLog')->addSalesRefundLog($refund_log);
			$this->commit();
		}catch (\PDOException $e) {
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
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
	 * 同意(审核)--拒绝--取消 等操作方法集
	 */
	
	/**
	 * 同意(审核)--审核退款货品
	 * @param array $refund
	 * @param array $list
	 * @param integer $user_id
	 * @return multitype:number unknown
	 */
	public function checkRefundOrder($refund,&$list,$user_id)
	{
		$sql_error_info='';
		$result=array();
		try {
			$refund_order_db=D('SalesRefundOrder');
			$sql_error_info='checkRefundOrder-sales_refund_query';
			$res_refund_order=$refund_order_db->getSalesRefundOrderList(
					'platform_id,shop_id,oid,trade_id,trade_order_id,trade_no,tid,refund_num,price,order_num',
					array('refund_id'=>array('eq',$refund['refund_id']))
			);
			foreach ($res_refund_order as $o)
			{
				$sql_error_info='checkRefundOrder-sales_trade_query';
				$res_trade=D('Trade')->getSalesTrade(
						'trade_status,warehouse_id',
						array('trade_id'=>array('eq',$o['trade_id']))
				);
				if (empty($res_trade))
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'订单不存在');
					continue;
				}
				if($res_trade['trade_status']>=40)
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'订单'.$o['trade_no'].'已审核，请先驳回');
					continue;
				}
				$sql_error_info='checkRefundOrder-sales_trade_order_query';
				$res_trade_order=D('SalesTradeOrder')->getSalesTradeOrder(
						'actual_num,is_master',
						array('rec_id'=>array('eq',$o['trade_order_id']))
				);
				if(intval($o['refund_num'])>intval($res_trade_order['actual_num']))
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'退款数量不能大于实发数量');
					continue;
				}
				$sql_error_info='checkRefundOrder-stock_spec_add';
				$this->returnStockSpec($res_trade['warehouse_id'], $o['trade_order_id']);
				$sql_error_info='checkRefundOrder-sales_trade_order_update';
				D('SalesTradeOrder')->updateSalesTradeOrder(
						array('actual_num'=>0,'stock_reserved'=>0,'refund_status'=>5),
						array('rec_id'=>array('eq',$o['trade_order_id']))
				);
				$sql_error_info='checkRefundOrder-I_DL_REFRESH_TRADE';
				$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",'".$o['trade_id']."',2,0)");
				$result['platform_id']=$o['platform_id'];
				$result['tid']=$o['tid'];
				$result['shop_id']=$o['shop_id'];
				if ($res_trade_order['is_master']!=0)
				{
					$result['is_master']=1;
				}
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $result;
	}
	/**
	 * 同意(审核)--添加换出订单
	 * @param array $refund
	 */
	public function addReturnTrade($refund_id,$user_id)
	{
		$refund_id=intval($refund_id);
		$sql_error_info='';
		try {
			//------------从退换单中获取--原始单--系统订单--所需要的数据------
			$sql_error_info='addReturnTrade-get_sales_refund';
			$api_trade=$this->getSalesRefund(
					"sr.type,sr.swap_warehouse_id AS warehouse_id,
					 sr.swap_receiver AS receiver_name,sr.swap_province AS receiver_province,sr.swap_city AS receiver_city, sr.swap_district AS receiver_district,
					 sr.swap_address AS receiver_address,sr.swap_mobile AS receiver_mobile,sr.swap_telno AS receiver_telno,sr.swap_area AS receiver_area,'' receiver_zip,
					 NOW() trade_time,NOW() pay_time,0 platform_id,sr.shop_id,sr.customer_id,sr.buyer_nick,2 trade_from,
					 sr.exchange_amount AS goods_amount,sr.post_amount,0 discount,sr.exchange_amount+sr.post_amount AS receivable,sr.exchange_amount+sr.post_amount AS paid,
					 sr.exchange_amount+sr.post_amount AS received,IF(cc.name,cc.name,'无') AS buyer_name,IF(cc.area,cc.area,'无') AS buyer_area,sr.swap_logistics_id AS logistics_id", //这行是api_trade单独需要的数据
					array('sr.refund_id'=>array('eq',$refund_id)),
					'sr',
					'LEFT JOIN crm_customer cc ON sr.customer_id = cc.customer_id'
			);
			$trade=$api_trade;
			unset($api_trade['logistics_id']);
			//------------原始订单--数据整理--------------------
			$api_trade['process_status']=20;
			$api_trade['trade_status']=10;
			$api_trade['guarantee_mode']=2;
			$api_trade['pay_status']=2;
			$api_trade['delivery_term']=1;
			$api_trade['pay_method']=1;
			$api_trade['pay_id']='';
			$api_trade['remark']=($api_trade['type']==5)?'破损补发单':'换货销售单';
			$api_trade['receiver_hash']=md5($api_trade['receiver_province'].' '.$api_trade['receiver_city'].' '.$api_trade['receiver_district']);
			$api_trade['created']=array('exp','NOW()');
			//--------------订单--数据整理--------------------
			$trade['trade_status']=30;
			$trade['trade_type']=($api_trade['type']==5)?2:3;
			$trade['cs_remark']='';
			if ($api_trade['type']==3) { $trade['flag_id']=19; }
			$trade['salesman_id']=$user_id;
			unset($trade['received']);
			unset($trade['buyer_name']);
			unset($trade['buyer_area']);
			//--------------原始子订单--数据整理------------------
			$sql_error_info='addReturnTrade-get_sales_refund_order';
			$this->execute("SET @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='', @tmp_goods_id=0,@tmp_spec_id=0,@tmp_barcode='',@tmp_retail_price=0");
			$api_orders=$this->query("
					SELECT 0 platform_id,".$api_trade['shop_id']." shop_id,30 `status`,10 process_status,0 is_invalid_goods,FN_GOODS_NO(target_type,target_id) goods_no,
					@tmp_goods_id goods_id,@tmp_spec_id spec_id,@tmp_merchant_no spec_no,@tmp_goods_name goods_name,@tmp_spec_name spec_name,
					@tmp_spec_code spec_code,@tmp_retail_price price,num,0 discount,0 share_amount,0 share_post,0 paid,NOW() created
					FROM sales_refund_out_goods WHERE refund_id=%d",array($refund_id)
			);
			$api_trade['order_count']=count($api_orders);
			$trade['goods_type_count']=$api_trade['order_count'];
			$goods_amount=0;
			$goods_count=0;
			foreach ($api_orders as $o)
			{
				$goods_count+=$o['num'];
				$goods_amount+=($o['num']*$o['price']);
			}
			$api_trade['goods_count']=$goods_count;
			$trade['goods_count']=$goods_count;
			for ($i=0;$i<$api_trade['order_count'];$i++)
			{
				$api_orders[$i]['total_amount']=$api_orders[$i]['num']*$api_orders[$i]['price']/$goods_amount*($api_trade['receivable']-$api_trade['post_amount']);
			}
			//--------------获取配置信息------------------
			$cfg_arr=array(
					'sales_refund_auto_pre_order',//是否是预定的--1    暂时不用
					'logistics_match_mode',//物流选择方式：全局唯一，按店铺，按仓库--0  暂时不用 
					'calc_logistics_by_weight',//根据重量计算物流--0   暂时不用
					'open_package_strategy',//包装策略--0  暂时不用
					'open_package_strategy_type',//包装策略(类型 1重量  2  体积)--1  暂时不用  
			);
			$res_cfg_val=get_config_value($cfg_arr,array(1,0,0,0,1));
			$trade['add_trade_type']=($api_trade['type']==5)?2:1;
			//调用刷新订单时先创建组合装临时表
			$api_trade_id=D('Trade/Trade')->addTrade($trade,$api_trade,$api_orders,$user_id,$res_cfg_val);
			$this->updateSalesRefund(array('swap_trade_id'=>$api_trade_id), array('refund_id'=>array('eq',$refund_id)));
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	/**
	 * 同意(审核)--退换单
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:number multitype:multitype:string unknown   multitype:multitype:number unknown
	 */
	public function agreeRefund($arr_ids_data,$user_id,$is_stockin=0)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		try{
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			$this->startTrans();
			$result=$this->agreeRefundNoTrans($arr_ids_data,$user_id,$is_stockin);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			if($is_rollback){
				$this->rollback();
			}
			SE($e->getMessage());
		}catch (\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		return $result;
	}
	/**
	 * 同意(审核)--退换单--无事务--调用前需要创建临时表
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:number multitype:multitype:string unknown   multitype:multitype:number unknown
	 */
	public function agreeRefundNoTrans($arr_ids_data,$user_id,$is_stockin=0,$flag=-1,$stockin_remark='',&$stockin_id='')
	{
		$list=array();
		$success=array();
		$sql_error_info='';
		try{
			$res_cfg_value=get_config_value('order_swap_create_stockin_check');
			$sql_error_info='agreeRefund-sales_refund_query_on_lock';
			$res_refund_arr=$this->getSalesRefundListOnLock(
					'refund_id,`type`,refund_no,`platform_id`,`status`,tid,process_status,guarantee_mode,exchange_amount,refund_amount,warehouse_id,warehouse_type,direct_refund_amount,guarante_refund_amount,shop_id,customer_id,trade_id,remark,from_type,src_no',
					array('refund_id'=>array('in',$arr_ids_data))
			);
			$arr_refund_log=array();
			$trade_order_db=D('Trade/SalesTradeOrder');
			$refund_order_db=D('Trade/SalesRefundOrder');
			foreach ($res_refund_arr as $refund)
			{
				$refund_order_ret = $refund_order_db->getSalesRefundOrderList(
					array('spec_id'),
					array('refund_id'=>array('eq',$refund['refund_id']))
				);
				$goods_spec = M('goods_spec')->field('spec_no,deleted')->where(array('spec_id'=>$refund_order_ret[0]['spec_id']))->find();
				if(empty($goods_spec)||$goods_spec['deleted']!=0){SE('该退换单里的货品('.$goods_spec['spec_no'].')已被删除，请取消该退换单！');}
				//--------------添加关联预入库单逻辑:校验是否包含其他货品或者已经做过关联了其他退换单---需要赵玲审查一下
                if($refund['type']!=4&&$refund['type']!=5)
                {
                    $this->checkStockinPre($refund['refund_id']);
                }
				if($refund['process_status']!=20)
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'退换单状态不正确');
					continue;
				}
				//退款--退货
				if($refund['type']==1)
				{//退款货品审核
					$result=$this->checkRefundOrder($refund, $list, $user_id);
					if(!empty($list)){continue;}
					$trade_order_db->reshareAmountByTid($result['shop_id'], $result['tid'], $result['is_master'],$user_id, true);
				}else if(($refund['type']==3||$refund['type']==5)&&$res_cfg_value['order_swap_create_stockin_check']==0)
				{//换货--换出的货品--添加对应的--原始单--订单
					$this->addReturnTrade($refund['refund_id'],$user_id);
					//continue;
				}
				//更新退换单--退换货品
				if($refund['warehouse_type']>=3 && $refund['warehouse_type'] != 127)
				{
					if ($refund['warehouse_type']==3)
					{
						$process_status=($refund['type']==2?63:60);
					}else{
						$process_status=63;//待推送
					}
				}else{
					$process_status=60;//待收货
				}
				if($refund['type']!=4&&$refund['type']!=5){
					$sql_error_info='agreeRefund-update_sales_refund_1';
					$this->updateSalesRefund(
							array('process_status'=>($refund['guarantee_mode']==2?$process_status:90)),
							array('refund_id'=>array('eq',$refund['refund_id']))
					);
					$sql_error_info='agreeRefund-update_sales_refund_order_1';
					$refund_order_db->updateSalesRefundOrder(
							array('process_status'=>($refund['guarantee_mode']==2?$process_status:90)),
							array('refund_id'=>array('eq',$refund['refund_id']))
					);
				}else{
					$sql_error_info='agreeRefund-update_sales_refund_2';
					$this->updateSalesRefund(//不做结算
							array('process_status'=>90),
							array('refund_id'=>array('eq',$refund['refund_id']))
					);
					$sql_error_info='agreeRefund-update_sales_refund_order_1';
					$refund_order_db->updateSalesRefundOrder(
							array('process_status'=>90),
							array('refund_id'=>array('eq',$refund['refund_id']))
					);
				}	
				if ($refund['type']==5) {
					$arr_refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'operator_id'=>$user_id,
						'type'=>2,
						'remark'=>'同意补发'.$refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
					);
				}elseif ($refund['type']==3) {
					$arr_refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'operator_id'=>$user_id,
						'type'=>2,
						'remark'=>'同意换货'.$refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
					);
				}else{
					$arr_refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'operator_id'=>$user_id,
						'type'=>2,
						'remark'=>'同意退款'.$refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
					);
				}			
				
// 				$success[]=array('id'=>$refund['refund_id'],'process_status'=>($refund['type']==4?90:($refund['guarantee_mode']==2?$process_status:90)));
                //--------------添加关联预入库单逻辑:更新退换单状态和相关数量---
                if($refund['type']!=4&&$refund['type']!=5)
                {
                    $this->updateSalesRefundByStockinPre($refund['refund_id']);
                }
                //--------------退换单、换货单一键入库-------------------
                if($is_stockin==1&&($refund['type']==2||$refund['type']==3)){
                	D('Stock/StockInOrder')->refundStockIn($refund['refund_id'],$stockin_id);
                }
                $process_status=$this->getSalesRefund('process_status', array('refund_id'=>array('eq',$refund['refund_id'])));
                $success[]=array('id'=>$refund['refund_id'],'process_status'=>$process_status['process_status']);
                //--------------退货、换货单回传备注到线上---------------------
                if($refund['type']==2||$refund['type']==3){
                	$sid=get_sid();
                	$cfg_refund_sync=get_config_value(array('return_agree_auto_remark','return_agree_auto_sync_remark','return_order_auto_remark','return_order_auto_sync_remark'),array(0,0,0,0));
					$is_return_remark 		= $cfg_refund_sync['return_order_auto_sync_remark'];
					$return_remark_value 	= $cfg_refund_sync['return_order_auto_remark'];
					$is_exchange_remark 	= $cfg_refund_sync['return_agree_auto_sync_remark'];
					$exchange_remark_value	= $cfg_refund_sync['return_agree_auto_remark'];
                	if($is_return_remark&&$refund['type']==2||$is_exchange_remark&&$refund['type']==3){
                		$message=array();
                		$error_list=array();
						if(empty($stockin_remark)){
							$return_remark = empty($refund['remark'])?($refund['type']==2?$return_remark_value:$exchange_remark_value):$refund['remark'];
						}else{
							$return_remark = $stockin_remark;
						}
                		$trade_manage=ManagerFactory::getManager('Trade');
                		$trade_manage->manual_upload_remark($sid,$user_id,$refund['trade_id'],$flag,$return_remark,$message,$error_list,1);
                		$log_message='';
                		if($message['status']==0){
                			$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'回传备注失败，'.$message['info']);
                			$log_message='回传备注失败，'.$message['info'];
                		}else if(!empty($error_list)){
                			$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'回传备注失败，'.$error_list[0]['info']);
                			$log_message='回传备注失败，'.$error_list[0]['info'];
                		}else{
							$log_msg = $refund['type']==2?$return_remark_value:$exchange_remark_value;
                			$log_message='回传备注到线上订单：'.$log_msg;
                		}
                		$arr_refund_log[]=array(
                				'refund_id'=>$refund['refund_id'],
                				'operator_id'=>$user_id,
                				'type'=>10,
                				'remark'=>$log_message,
                				'created'=>date('y-m-d H:i:s',time())
                		);
                	}
                }
				//---------------------记录售中退款金额到平台对账--------------------
				//type=1 退款单，这种属于售前退款。不处理
				//合并单不处理
				if($refund['type']<>1 && $refund['platform_id']<>0 && !strpos(',',$refund['tid']))
				{
					$account_sync = get_config_value('account_sync',0);
					//平台担保退款金额，订单未完成金额由淘宝担保不为0 并开启支付宝对账
					if($refund['guarante_refund_amount']<>0 && $account_sync<>0) //线上退款
					{
						$v_online_amount = 0;
						$tid = $refund['tid'];
						$src_no = $refund['src_no'];
						$from_type = $refund['from_type'];
						$platform_id = $refund['platform_id'];
						$shop_id = $refund['shop_id'];
						//是否开启退款明细配置   目前不支持
						$sales_refund_account_detail = get_config_value('sales_refund_account_detail',0);
						if($sales_refund_account_detail)
						{
							/*
							 * SELECT IF(flow_direction=1,refund_amount,-refund_amount) drawback_amount,cs_status FROM sales_refund_account_detail WHERE refund_id=P_RefundID AND refund_way=1
							 * OPEN alipay_cursor;
			     				ALIPAY_LABEL:LOOP
								SET V_NOT_FOUND=0;
				 				FETCH alipay_cursor INTO V_DrawBackAmount,V_CsStatus;
				 				IF V_NOT_FOUND THEN
								SET V_NOT_FOUND=0;
								LEAVE ALIPAY_LABEL;
				 			END IF;
				 				IF V_CsStatus=2 THEN
									SET V_OnLineAmount=V_OnLineAmount+V_DrawBackAmount;
				 				END IF;
			     			END LOOP;
			     			CLOSE alipay_cursor;
							 * */
						}else
						{
							//递交的退换单要关联原始退款单确定售中售后
							$res = D('OriginalRefund')->field('refund_id')->where(array('refund_no'=>$src_no,'is_aftersale'=>1))->find();
							if($from_type==1 && !empty($res))
							{
								$v_online_amount = 0;
							}else
							{
								$v_online_amount = $refund['guarante_refund_amount'];
							}
						}
						$v_refund_month = date('Y-m',time());
						if($v_online_amount<>0)//平台退款明细阶段中没有售中
						{
							$o_alipay_check = M('fa_aliapay_account_check')->field('rec_id')->where(array('tid'=>$tid,'platform_id'=>$platform_id))->find();
							if(!empty($o_alipay_check))
							{
								$this->execute("UPDATE fa_alipay_account_check SET refund_amount=refund_amount+$v_online_amount
					 							WHERE tid='$tid' AND platform_id=$platform_id");
							}else
							{
								$this->execute("INSERT INTO fa_alipay_account_check(account_check_no,tid,refund_amount,shop_id,platform_id,created)
												VALUES (FN_SYS_NO('account_check'),'{$tid}',$v_online_amount,$shop_id,$platform_id,NOW())");
							}

							$o_platform_detail_month = M('fa_platform_check_detail_month')->field('rec_id')->where(array('check_month'=>$v_refund_month,'tid'=>$tid,'platform_id'=>$platform_id))->find();
							if(!empty($o_platform_detail_month))
							{
								$this->execute("UPDATE fa_platform_check_detail_month SET refund_amount=refund_amount+V_OnLineAmount,diff_amount=diff_amount-V_OnLineAmount,
												`status`=IF(diff_amount=0,3,1)
												WHERE check_month='{$v_refund_month}' AND tid='{$tid}' AND platform_id=$platform_id");
								$data = M('fa_aliapay_account_check')->field('diff_amount,refund_amount,last_refund_amount,is_transfer,`status`')->where(array('check_month'=>$v_refund_month,'tid'=>$tid,'platform_id'=>$platform_id))->find();
								$v_diff_amount = $data['diff_amount'];
								$v_check_refund_amount = $data['refund_amount'];
								$v_check_last_amount = $data['last_refund_amount'];
								$v_transfer = $data['is_transfer'];
								$v_after_status = $data['status'];
								if($v_after_status==3)
								{
									$v_sub_status = $v_transfer==1?3:2; //3 结转成功  2 对账成功
								}else
								{
									if($v_diff_amount == (-$v_check_refund_amount-$v_check_last_amount))
									{
										$v_sub_status = 5;//阶段错误
									}else
									{
										$v_sub_status = $v_transfer==1?8:9;//8结转失败 9对账失败
									}
								}
								$this->execute("UPDATE fa_platform_check_detail_month SET `sub_status`= $v_sub_status,wait_refund_amount=0 WHERE check_month='{$v_refund_month}' AND tid='{$tid}' AND platform_id=$platform_id");
							}
						}else
						{
							$this->execute("INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,refund_amount,diff_amount,created,`status`,`sub_status`)
				   							VALUES ('{$tid}',$platform_id,$shop_id,'{$v_refund_month}',$v_online_amount,-$v_online_amount,NOW(),1,9)");
						}
						$this->faAlipayAccountCheck($tid,$platform_id);

					}

				}
			}
			D('Trade/SalesRefundLog')->addSalesRefundLog($arr_refund_log);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch (\Exception $e){
			SE($e->getMessage());
		}
		$result=array('success'=>$success,'fail'=>$list);
		$result['status']=empty($list)?0:2;
		return $result;
	}
	
	/**
	 * 拒绝--退换单
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:number multitype:multitype:string unknown   multitype:multitype:number unknown
	 */
	public function rejectRefund($arr_ids_data,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		try{
			$is_rollback=true;
			$this->startTrans();
			$sql_error_info='rejectRefund-sales_refund_query';
			$res_refund_arr=$this->getSalesRefundListOnLock(
					'refund_id,type,refund_no,status,process_status,guarantee_mode', 
					array('refund_id'=>array('in',$arr_ids_data))
			);
			$arr_refund_log=array();
			$refund_log_db=D('SalesRefundLog');
			$refund_order_db=D('SalesRefundOrder');
			foreach ($res_refund_arr as $refund)
			{
				if($refund['process_status']!=20&&$refund['process_status']!=30)
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'退换单状态不正确');
					continue;
				}
				if($refund['type']==1)
				{
					$sql_error_info='rejectRefund-sales_trade_order-sales_refund_order-update';
					$this->execute('UPDATE sales_trade_order sto,sales_refund_order sro SET sto.refund_status=0 WHERE sto.rec_id=sro.trade_order_id AND sro.refund_id=%d',array($refund['refund_id']));
					//取消出库单拦截处理
					$sql_error_info='rejectRefund-stockout_id_query';
					$res_stockout_ids=$this->query('SELECT DISTINCT sod.stockout_id FROM sales_refund_order sro, stockout_order_detail sod WHERE sro.refund_id = %d AND sod.src_order_type=1 AND sod.src_order_detail_id=sro.trade_order_id',array($refund['refund_id']));
					foreach ($res_stockout_ids as $stockout)
					{
						$sql_error_info='rejectRefund-max_refund_status_query';
						$refund_status=$this->query('SELECT MAX(sto.refund_status) AS max_status FROM sales_trade_order sto,stockout_order_detail sod WHERE sod.stockout_id=%d AND sto.rec_id=sod.src_order_detail_id',array($stockout['stockout_id']));
						$block=0;
						if($refund_status[0]['max_status']>2)
						{
							$block=2;
						}else if($refund_status[0]['max_status']>1){
							$block=1;
						}
						$sql_error_info='rejectRefund-stockout_order-update';
						M('stockout_order')->where(array('stockout_id'=>array('eq',$stockout['stockout_id'])))
										   ->save(array('block_reason'=>'block_reason|'.$block));
					}
				}
				$this->updateSalesRefund(
						array('process_status'=>($refund['guarantee_mode']==2?10:40)), 
						array('refund_id'=>array('eq',$refund['refund_id']))
				);
				$sql_error_info='refund_order-process_status-update';
				$refund_order_db->updateSalesRefundOrder(
						array('process_status'=>($refund['guarantee_mode']==2?10:40)),
						array('refund_id'=>array('eq',$refund['refund_id']))
				);
				if($refund['process_status']==30)
				{
					$refund_log_db->updateRefundLog(array('data'=>1),array('refund_id'=>array('eq',$refund['refund_id']),'type'=>array('eq',2),'data'=>array('eq',0)));
				}
				$arr_refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'operator_id'=>$user_id,
						'type'=>$refund['process_status']==20?3:8,
						'remark'=>'拒绝退款'.$refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
				);
				$success[]=array('id'=>$refund['refund_id'],'process_status'=>($refund['guarantee_mode']==2?10:40));
			}
			$refund_log_db->addSalesRefundLog($arr_refund_log);
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
		$result=array('success'=>$success,'fail'=>$list);
		$result['status']=empty($list)?0:2;
		return $result;
	}
	/**
	 * 取消--退换单
	 * @param array $arr_ids_data
	 * @param integer $user_id
	 * @return multitype:number multitype:multitype:string unknown   multitype:multitype:number unknown
	 */
	public function cancelRefund($arr_ids_data,$user_id)
	{
		$list=array();
		$success=array();
		$is_rollback=false;
		$sql_error_info='';
		try{
			$sql_error_info='cancelRefund-sales_refund_query';
			$res_refund_arr=$this->getSalesRefundList(
					'refund_id, refund_no, process_status', 
					array('refund_id'=>array('in',$arr_ids_data))
			);
			$arr_refund_log=array();
			$ids=array();
			foreach ($res_refund_arr as $refund)
			{
				if($refund['process_status']!=20)
				{
					$list[]=array('refund_no'=>$refund['refund_no'],'result_info'=>'退换单状态不正确');
					continue;
				}
				$arr_refund_log[]=array(
						'refund_id'=>$refund['refund_id'],
						'operator_id'=>$user_id,
						'type'=>4,
						'remark'=>'关闭订单'.$refund['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
				);
				$ids[]=$refund['refund_id'];
				$success[]=array('id'=>$refund['refund_id']);
			}
			if (!empty($ids))
			{
				$is_rollback=true;
				$this->startTrans();
				$sql_error_info='update_sales_refund';
				$this->updateSalesRefund(array('process_status'=>10), array('refund_id'=>array('in',$ids)));
				D('SalesRefundLog')->addSalesRefundLog($arr_refund_log);
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
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		$result=array('success'=>$success,'fail'=>$list);
		$result['status']=empty($list)?0:2;
		return $result;
	}
	public function checkStockinPre($refund_id)
    {
        try{
            $refund_info = $this->where(array('refund_id'=>$refund_id))->find();
            $refund_detail_info = D('Trade/SalesRefundOrder')->field('spec_id')->where(array('refund_id'=>$refund_id))->select();
            $refund_spec_list = array_column($refund_detail_info,'spec_id');
            if(empty($refund_info)){
                SE('查询退换单失败');
            }
            if(empty($refund_info['stockin_pre_no']))
            {
                return;
            }
            $stockin_pre_no_ar = explode(',',$refund_info['stockin_pre_no']);
            $check_error = '';
            foreach($stockin_pre_no_ar as $stockin_no)
            {

                $stockin_order_info = D('Stock/StockInOrder')->where(array('stockin_no'=>$stockin_no))->find();
                if(empty($stockin_order_info))
                {
                    $check_error.='入库单号：'.$stockin_no.'-不存在!</br>';
                }else if($stockin_order_info['status']!=80)
                {
                    $check_error.='入库单号：'.$stockin_no.'-状态不准确!</br>';
                }else if($stockin_order_info['src_order_id']!=0)
                {
                    $check_error.='入库单号：'.$stockin_no.'-已绑定其他退换出库单,请重新编辑!</br>';
                }
                $stockin_order_detail = D('Stock/StockinOrderDetail')->alias('sod')->field('sod.spec_id,gs.spec_no')->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->where(array('stockin_id'=>$stockin_order_info['stockin_id']))->select();
                foreach ($stockin_order_detail as $stockin_detail_item)
                {
                    if(!in_array($stockin_detail_item['spec_id'],$refund_spec_list))
                    {
                        $check_error.='入库单号：'.$stockin_no.'-中包含了退换单中不存在的货品'.$stockin_detail_item['spec_no'].'!</br>';
                    }
                }
            }
            if(!empty($check_error)){
                SE($check_error);
            }
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-checkStockinPre:'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
        	SE($e->getMessage());
        }catch (\Exception $e){
            SE($e->getMessage());
        }
    }
    public function updateSalesRefundByStockinPre($refund_id)
    {
        try{
            $operator_id = get_operator_id();
            $refund_info = $this->where(array('refund_id'=>$refund_id))->find();
            $refund_details = D('Trade/SalesRefundOrder')->where(array('refund_id'=>$refund_id))->order('spec_id asc,refund_num asc,stockin_num asc')->getField('refund_order_id,spec_id,refund_id,refund_num,stockin_num,stockin_amount',true);
            if(empty($refund_info['stockin_pre_no']))
            {
                return;
            }
            $stockin_pre_no_ar = explode(',',$refund_info['stockin_pre_no']);
            foreach($stockin_pre_no_ar as $stockin_no)
            {
                $stockin_order = D('Stock/StockInOrder')->field(array('stockin_id'))->where(array('stockin_no'=>$stockin_no))->find();
                $stockin_order_detail = D('Stock/StockinOrderDetail')->where(array('stockin_id'=>$stockin_order['stockin_id']))->order('spec_id asc,num')->getField('spec_id,rec_id,num,stockin_id,cost_price',true);
                $res = D("Stock/StockInOrder")->where(array('stockin_id'=>$stockin_order['stockin_id']))->save(array('src_order_id'=>$refund_info['refund_id'],'src_order_no'=>$refund_info['refund_no']));

                foreach($refund_details as $refund_order_id =>$refund_detail_item)
                {
                    if(isset($stockin_order_detail["{$refund_detail_item['spec_id']}"]) && ((int)$refund_detail_item['stockin_num']<(int)$refund_detail_item['refund_num']) &&(int)$stockin_order_detail["{$refund_detail_item['spec_id']}"]['num']!=0)
                    {
                        if((int)$stockin_order_detail["{$refund_detail_item['spec_id']}"]['num']+(int)$refund_detail_item['stockin_num']<=(int)$refund_detail_item['refund_num'] )
                        {
                            $refund_details[$refund_order_id]['stockin_num'] = $refund_details[$refund_order_id]['stockin_num']+$stockin_order_detail["{$refund_detail_item['spec_id']}"]['num'];
                            $stockin_order_detail["{$refund_detail_item['spec_id']}"]['num'] =0;
                            $refund_details[$refund_order_id]['stockin_amount'] = $refund_details[$refund_order_id]['stockin_amount']+$stockin_order_detail["{$refund_detail_item['spec_id']}"]['num']*$stockin_order_detail["{$refund_detail_item['spec_id']}"]['cost_price'];

                        }else{
                            $refund_details[$refund_order_id]['stockin_num'] = $refund_details[$refund_order_id]['refund_num'];
                            $stockin_order_detail["{$refund_detail_item['spec_id']}"]['num'] =$stockin_order_detail["{$refund_detail_item['spec_id']}"]['num']- ($refund_details[$refund_order_id]['refund_num']-$refund_details[$refund_order_id]['stockin_num']);
                            $refund_details[$refund_order_id]['stockin_amount'] = $refund_details[$refund_order_id]['stockin_amount']+($refund_details[$refund_order_id]['refund_num']-$refund_details[$refund_order_id]['stockin_num'])*$stockin_order_detail["{$refund_detail_item['spec_id']}"]['cost_price'];
                        }

                    }
                }
                foreach ($refund_details as $key =>$data)
                {
                    $data['refund_order_id'] = $key;
                    if($stockin_order_detail["{$data['spec_id']}"]['num']>0){
                        $data['stockin_num'] = $data['stockin_num']+$stockin_order_detail["{$data['spec_id']}"]['num'];
                        $data['stockin_amount'] = $data['stockin_amount']+$stockin_order_detail["{$data['spec_id']}"]['num']*$stockin_order_detail["{$data['spec_id']}"]['cost_price'];
                        $stockin_order_detail["{$data['spec_id']}"]['num'] = 0;
                    }
                    D('Trade/SalesRefundOrder')->save($data);
                }

                if($refund_info['process_status']<>60&&$refund_info['process_status']<>65&&$refund_info['process_status']<>70&&$refund_info['process_status']<>69){
                    SE('入库单对应的退货单状态错误');
                }
                $refund_id 		= $refund_info['refund_id'];
                $refund_status 	= $refund_info['process_status'];

                //-------------------更新退换详单 入库单详单信息：入库数量 入库总价

                //--------------------更新退货入库单的状态
                $refund_detail_info = D('Trade/SalesRefundOrder')->field("SUM(IF(refund_num-stockin_num<0,0,refund_num-stockin_num)) count,trade_id")->where(array("refund_id"=> $refund_id))->find();
                $count = (int)$refund_detail_info['count'];

                if ($count > 0) {
                    if ($refund_status == 69 || $refund_status == 71) {
                        D('Trade/SalesRefund')->where(array('refund_no'=>$refund_info['refund_no']))->save(array('process_status'=>71));
                    } else {
                        D('Trade/SalesRefund')->where(array('refund_no'=>$refund_info['refund_no']))->save(array('process_status'=>70));
                    }
                } else {
                    if ($refund_status == 69 || $refund_status == 71) {
                        D('Trade/SalesRefund')->where(array('refund_no'=>$refund_info['refund_no']))->save(array('process_status'=>90));
                    } else {
                        D('Trade/SalesRefund')->where(array('refund_no'=>$refund_info['refund_no']))->save(array('process_status'=>90));
                    }
                }
                //---------`type`  '操作类型: 1创建退款单2同意3拒绝4取消退款 5平台同意6平台取消7平台拒绝8停止等待9驳回',
                $sales_refund_data = array("refund_id" => $refund_id, "type" => 10, "operator_id" => $operator_id, "remark" => array('exp',"IF({$count}>0,'退换预入库部分到货','退换预入库全部到货')"));
                D("Trade/SalesRefundLog")->add($sales_refund_data);
            }
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-updateSalesRefundByStockinPre:'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            SE($e->getMessage());
        }catch (BusinessLogicException $e){
        	SE($e->getMessage());
        }
    }
    
    public function refundStockIn($id,$user_id){
    	$result=array();
    	$sql_error_info='';
    	try{
    		$refund_data=D('Trade/SalesRefund')->getSalesRefund('refund_id,type,process_status',array('refund_id'=>array('eq',$id)));
    		if($refund_data['type']!=2&&$refund_data['type']!=3){
    			SE("该退换单不需要入库，请选择退货单或者换货单");
    		}
    		if($refund_data['process_status']>70){
    			SE("该退换单的货品已全部入库，不需要再次入库");
    		}
    		if($refund_data['process_status']!=20&&$refund_data['process_status']!=60&&$refund_data['process_status']!=70){
    			SE("该退换单处理状态不正确，不需要入库。");
    		}
    		if($refund_data['process_status']==20){
    			$id_arr=array();
    			$id_arr[]=$id;
    			$data=$this->agreeRefund($id_arr,$user_id,1);
    		}else if($refund_data['process_status']==60||$refund_data['process_status']==70){
    			$is_rollback=true;
    			$this->startTrans();
    			D('Stock/StockInOrder')->refundStockIn($id);
    			$this->commit();
    		}
    	}catch (\PDOException $e){
    		if($is_rollback){
    			$this->rollback();
    		}
    		\Think\Log::write($this->name.$sql_error_info.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}catch (BusinessLogicException $e){
    		if($is_rollback){
    			$this->rollback();
    		}
    		SE($e->getMessage());
    	}catch (\Think\Exception $e){
    		if($is_rollback){
    			$this->rollback();
    		}
    		SE(self::PDO_ERROR);
    	}
    }

    //退换管理导出功能
    public function exportToExcel($id_list,$search,$type='excel'){
        $user_id = get_operator_id();
        $creator = session('account');
        try{
            //若为search条件，进行搜索转化为相应id
            if(empty($id_list)){
                $where_sales_refund_str='';//不同初始化搜索条件
                $where_goods_goods_str='';
                $where_goods_spec_str='';
                D('Trade/SalesRefund')->searchFormDeal($where_sales_refund_str,$where_goods_goods_str,$where_goods_spec_str,$search);
                $sql_where='';
                if(!empty($where_goods_goods_str)){
                    $sql_where.=' LEFT JOIN sales_refund_order sro ON sro.refund_id=sr_1.refund_id LEFT JOIN goods_spec gs ON gs.spec_id=sro.spec_id LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id ';
                }else{
                    if(!empty($where_goods_spec_str)){
                        $sql_where.=' LEFT JOIN sales_refund_order sro ON sro.refund_id=sr_1.refund_id LEFT JOIN goods_spec gs ON gs.spec_id=sro.spec_id ';
                    }
                }
                $flag=false;
                connect_where_str($sql_where, $where_sales_refund_str, $flag);
                connect_where_str($sql_where, $where_goods_goods_str, $flag);
                connect_where_str($sql_where, $where_goods_spec_str, $flag);
                $select_id_sql = "SELECT sr_1.refund_id FROM sales_refund sr_1 ".$sql_where." ORDER BY sr_1.refund_id";
                $rows = $this->query($select_id_sql);
                for ($i=0; $i < count($rows); $i++) {
                    $id_list[$i] = $rows[$i]['refund_id'];
                }
                $where = array('refund_id' => array('in', $id_list));
            }else{
                $where = array('refund_id' => array('in', $id_list));
            }
            //限制导出条数
            $num = workTimeExportNum($type);
            if(count($where['refund_id']['1']) > $num){
            	if($type == 'csv'){
            		SE(self::EXPORT_CSV_ERROR);
            	}
                SE(self::OVER_EXPORT_ERROR);
            }
            //查询所需数据
            $cfg_show_telno=get_config_value('show_number_to_star',1);
            $export_data = D('Trade/SalesRefund')->alias('sr_2')->field("sr_2.refund_id, sr_2.flag_id, sr_2.refund_no, sr_2.type,sh.shop_name AS shop_id, sr_2.platform_id, he.fullname AS operator_id, sr_2.src_no, sr_2.process_status, sr_2.status, wh.name AS warehouse_id, sr_2.warehouse_type, sr_2.wms_status, sr_2.wms_result, sr_2.outer_no, sr_2.tid, sr_2.trade_no, sr_2.buyer_nick, sr_2.receiver_name, sr_2.swap_area, IF(".$cfg_show_telno."=0,sr_2.return_mobile,INSERT( sr_2.return_mobile,4,4,'****')) return_mobile, IF(".$cfg_show_telno."=0,sr_2.return_telno,INSERT( sr_2.return_telno,4,4,'****')) return_telno, sr_2.receiver_address, sr_2.pay_account, sr_2.goods_amount, sr_2.post_amount, sr_2.refund_amount, sr_2.guarante_refund_amount, sr_2.direct_refund_amount, sr_2.exchange_amount, sr_2.logistics_name, sr_2.logistics_no, sr_2.return_address, sr_2.created, sr_2.refund_time, sr_2.from_type, cor.title AS reason_id, sr_2.sync_result, sr_2.remark, sr_2.stockin_pre_no")->where($where)->join('LEFT JOIN cfg_shop sh ON sh.shop_id=sr_2.shop_id LEFT JOIN hr_employee he ON he.employee_id=sr_2.operator_id LEFT JOIN cfg_warehouse wh ON wh.warehouse_id=sr_2.warehouse_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr_2.reason_id')->order('sr_2.refund_id DESC')->select();
            //退款类型----------相关文字展示信息替换
            $refund_type = array(
                '1'=>'退款',//（未发货，退款申请）
                '2'=>'退货',
                '3'=>'换货',
                '4'=>'退款不退货'
            );
			//退换-处理状态
			$refund_process_status = array(
				'10'=>'已取消',
				'20'=>'待审核',
				'30'=>'已同意',
				'40'=>'已拒绝',
				'50'=>'待财审',
				'60'=>'待收货',
				'63'=>'待推送',
				'64'=>'推送失败',
				'65'=>'推送成功',
				'70'=>'部分到货',
				'80'=>'待结算',
				'90'=>'已完成'
			);
			//原始退款单平台状态
			$api_refund_status = array(
				'1'=>'取消退款',
				'2'=>'已申请退款',
				'3'=>'等待退货',
				'4'=>'等待收货',
				'5'=>'退款成功'
			);
			//仓库类型
			$warehouse_type = array(
				'0'=>'',
				'1'=>'普通仓库',
				'11'=>'奇门仓储'
			);
			//订单来源
			$trade_from = array(
				'1'=>'API抓单',
				'2'=>'手工建单',
				'3'=>'excel导入'
			);
			for ($i=0; $i < count($export_data); $i++) { 
				$export_data[$i]['type'] = $refund_type[$export_data[$i]['type']];
				$export_data[$i]['process_status'] = $refund_process_status[$export_data[$i]['process_status']];
				$export_data[$i]['status'] = $api_refund_status[$export_data[$i]['status']];
				$export_data[$i]['warehouse_type'] = $warehouse_type[$export_data[$i]['warehouse_type']];
				$export_data[$i]['from_type'] = $trade_from[$export_data[$i]['from_type']];
			}

            $excel_header = D('Setting/UserData')->getExcelField('Trade/RefundManage','refund_manage');
            $title = "退换管理";
            $filename = "退换管理";
            foreach ($excel_header as $v) { $width_list[] = 20; }
			//用sales_refund_log日志记录导出操作
			$refund_log = array();
			for ($j=0; $j < count($export_data); $j++) { 
				$refund_log[]=array(
					'refund_id'=>$export_data[$j]['refund_id'],
					'type'=>11,
					'operator_id'=>$user_id,
					'remark'=>'导出退换单'.':'.$export_data[$j]['refund_no'],
					'created'=>date('y-m-d H:i:s',time()),
				);
			}
			D('SalesRefundLog')->addSalesRefundLog($refund_log);
			if($type == 'csv'){
				ExcelTool::Arr2Csv($export_data, $excel_header, $filename);
			}else{
				ExcelTool::Arr2Excel($export_data,$title,$excel_header,$width_list,$filename,$creator);
			}
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
	}
	public function cancel_sr($id){
		try{
			D('Trade/SalesRefund')->where(array('refund_id'=>$id))->save(array('process_status'=>10));
		}catch(BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
		}catch(\PDOException $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}catch(\Exception $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}
		 echo json_encode(array('updated'=>0));
	}
	//驳回审核
	public function revertCheck($id,$form,&$fail=array(),&$success=array()){
		$this->startTrans();
		try{
			$reason_fields = array("title");
			$reason_cond = array(
				'reason_id' => array('eq',$form['reason_id']),
			);
			$reason_info = D('Setting/CfgOperReason')->getOperReason($reason_fields,$reason_cond);
			$res_query_reason = $reason_info[0];
			if(empty($res_query_reason))
			{
				SE("驳回原因不存在");
			}
			$sql_error_info='revertCheck-sales_refund_query';
			$res_refund_arr=$this->getSalesRefund(
				'refund_id,`type`,refund_no,`status`,tid,process_status,guarantee_mode,exchange_amount,refund_amount,warehouse_id,warehouse_type,direct_refund_amount,guarante_refund_amount,shop_id,customer_id,trade_id',
				array('refund_id'=>array('eq',$id))
			);
			if(empty($res_refund_arr)){
				SE("退换单不存在");
			}
			if((int)$res_refund_arr['type'] == 1){
				SE("退款单，禁止驳回");
			}
			if((int)$res_refund_arr['process_status'] != 60){
				SE("退换单状态不正确");
			}
			//判断换货单对应的订单是否已取消
			if((int)$res_refund_arr['type'] == 3){
				$sales_refund=D('Trade/SalesRefund')->query('SELECT st.trade_no,st.trade_status FROM sales_refund sr
								INNER JOIN api_trade ap ON sr.swap_trade_id=ap.rec_id
								INNER JOIN sales_trade_order sto ON ap.tid=sto.src_tid
								INNER JOIN sales_trade st ON st.trade_id=sto.trade_id
								WHERE sr.refund_id='.$id);
				if(!empty($sales_refund)&&$sales_refund[0]['trade_status']!=5){
					SE("该换货单对应的订单尚未取消");
				}
			}
			$flag_id = D('Setting/Flag')->getFlagId('驳回订单',1);
			$this->updateSalesRefund(
				array(
					"process_status"=>"20",
					"revert_reason"=>"{$form['reason_id']}",
					"flag_id"=>"{$flag_id}",
					),
				array('refund_id'=>array('eq',$id))
			);
			//插入日志
			$arr_refund_log=array(
				'refund_id'=>$id,
				'operator_id'=>$form['operator_id'],
				'type'=>30,
				'remark'=>array("exp","CONCAT(IF({$form['is_force']},'强制驳回退换单,驳回原因:','驳回退换单,驳回原因:'),'{$res_query_reason['title']}')"),
				'created'=>date('y-m-d H:i:s',time())
			);
			D('SalesRefundLog')->addSalesRefundLog($arr_refund_log);
			$success= array(
				'id'=>$id,
				'revert_reason' => $res_query_reason['title'],
			);
		}catch(\PDOException $e){
			$result_info = $e->getMessage();
			$fail[] = array(
				'refund_no' => $res_refund_arr['refund_no'],
				'result_info'      => self::PDO_ERROR,
			);
			$this->rollback();
			\Think\Log::write($sql_error_info.$result_info."-".$res_refund_arr['refund_no']);
			return false;
		}catch(BusinessLogicException $e){
			$result_info = $e->getMessage();
			$fail[] = array(
				'refund_no' => $res_refund_arr['refund_no'],
				'result_info'      => $result_info,
			);
			$this->rollback();
			return false;
		}catch(\Exception $e){
			$result_info = $e->getMessage();
			$fail[] = array(
				'refund_no' => $res_refund_arr['refund_no'],
				'result_info'      => $result_info,
			);
			$this->rollback();
			\Think\Log::write($sql_error_info.$result_info."-".$res_refund_arr['refund_no']);
			return false;
		}
		$this->commit();
		return true;
	}

	/*
	 *
	 * 单据平台对账
	 * I_FA_ALIPAY_ACCOUNT_CHECK
	 * */
	public function faAlipayAccountCheck($tid,$platform_id)
	{
		try
		{
			$alipay_check_db = M('fa_alipay_account_check');
			$trade_by_confirm = get_config_value('trade_pay_check_by_confirm',0);
			$alipay_days_to_fail = get_config_value('alipay_days_to_fail',10);
			$where = array('tid'=>$tid,'platform_id'=>$platform_id);
			$exists = $alipay_check_db->field('rec_id')->where($where)->find();
			if(empty($exists))
			{
				SE('不存在单号为'.$tid.'的对账单');
			}
			$status = $alipay_check_db->field('status')->where($where)->find();
			$status = $status['status'];
			/*	--  已最终状态为准
				--  如果对账单已经对账成功了，作为终止状态，不再更新对账状态。
        		--   这里 1.退货已经成功了 又有退货  --不更新退货状态 -
        			-- 2. 确认收货成功了
			*/
			// 0 未对账 1 失败 2部分 3.成功
			if($trade_by_confirm>0)
			{
				$this->execute("UPDATE fa_alipay_account_check SET check_time=NOW(),`status`=IF(pay_amount=confirm_amount,3,2) WHERE tid=$tid AND platform_id = $platform_id");
			}else
			{
				$this->execute("UPDATE fa_alipay_account_check SET check_time=NOW(),`status`=IF(pay_amount=send_amount-refund_amount,3,2) WHERE tid=$tid AND platform_id = $platform_id");
			}
			if($alipay_days_to_fail==0)
			{
				$this->execute("update fa_alipay_account_check set `status`=1 WHERE tid=$tid AND platform_id = $platform_id and `status`=2");
			}

		}catch (\PDOException $e)
		{
			\Think\Log::write('faAlipayAccountCheck SQL ERR'.print_r($e->getMessage(),true));
			return false;
		}catch(BusinessLogicException $e)
		{
			return false;
		}catch(\Exception $e)
		{
			\Think\Log::write('faAlipayAccountCheck ERR'.print_r($e->getMessage(),true));
			return false;
		}
		return true;
	}

}