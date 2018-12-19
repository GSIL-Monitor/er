<?php
namespace Trade\Model;
use Think\Model;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
class TradeDetailModel extends Model{
	protected $tableName = 'sales_trade_order';
	protected $pk        = 'rec_id';
	
	/**
	 * 订单查询（搜索）
	 * @param number $page
	 * @param number $rows
	 * @param array  $search
	 * @param string $sort
	 * @param string $order
	 * @param number|array $trade_status
	 */
	public function queryTradeDetail(&$where_sales_trade,$page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		//搜索表单-数据处理
		$where_sales_trade_order='';
		D('Trade/TradeDetail')->searchFormDeal($where_sales_trade,$where_sales_trade_order,$where_stockout_order,$search);
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$arr_sort=array('shop_name'=>'shop_id','flag_name'=>'flag_id','warehouse_name'=>'warehouse_id','handle_days'=>'trade_id');//用于映射排序
		$order = 'sto_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
		$order = addslashes($order);
		$sql_sel_limit='SELECT sto_1.rec_id FROM sales_trade_order sto_1 ';
		$sql_total='SELECT COUNT(1) AS total FROM sales_trade_order sto_1 ';
		$flag=false;
		$sql_where='';
		$sql_limit=' ORDER BY '.$order.' LIMIT '.$limit;
		if (!empty($where_stockout_order)) {
			$where_stockout_order.=' AND src_order_type=1  ';
			$sql_where.=' LEFT JOIN stockout_order so ON so.src_order_id = sto_1.trade_id ';							
		}
		if(!empty($where_sales_trade))
		{
			$sql_where.=' LEFT JOIN sales_trade st_1 ON st_1.trade_id=sto_1.trade_id ';
			$sql_limit=' GROUP BY sto_1.rec_id ORDER BY '.$order.' LIMIT '.$limit;
		}	
		connect_where_str($sql_where,$where_stockout_order,$flag);	
		connect_where_str($sql_where, $where_sales_trade_order, $flag);
		connect_where_str($sql_where, $where_sales_trade, $flag);
		$sql_sel_limit.=$sql_where;
		$sql_total.=$sql_where;
		$sql_sel_limit.=$sql_limit;
		$sql_fields_str='';
		$sql_left_join_str='';
		$cfg_show_telno=get_config_value('show_number_to_star',1);
		$point_number = get_config_value('point_number',0);
		$goods_count = "CAST(st_2.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
		$raw_goods_count = "CAST(st_2.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";
		$num = "CAST(sto_2.num AS DECIMAL(19,".$point_number.")) num";
		$actual_num = "CAST(sto_2.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			
		$sql_fields_str="SELECT ss.cost_price,sto_2.rec_id,sto_2.shop_id,sh.shop_name,sto_2.goods_no,sto_2.spec_no,sto_2.spec_code,sto_2.goods_name,sto_2.spec_name,
						sto_2.api_goods_name,sto_2.api_spec_name, ".$num.",sto_2.price,sto_2.order_price,".$actual_num.",
						sto_2.share_price,sto_2.share_amount,sto_2.share_post,round((sto_2.order_price/sto_2.price),2) AS discount_rate,
						sto_2.commission,sto_2.paid AS order_paid,sto_2.suite_name,sto_2.gift_type,sto_2.discount AS order_discount,
						sto_2.remark,sto_2.weight AS order_weight,st_2.trade_id AS id,st_2.trade_no, st_2.platform_id,st_2.warehouse_id,
						sw.name AS warehouse_name, st_2.warehouse_type, sto_2.src_tid AS src_tids, st_2.pay_account, st_2.trade_status, 
						st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, st_2.delivery_term, st_2.freeze_reason, 
						cor.title AS freeze_info,sto_2.refund_status AS order_refund_status, st_2.unmerge_mask, 
						st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time,
						".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.", 
						st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, 
						st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, 
						st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address,
						IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,
						INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring,
						st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, 
						clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, 
						st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, 
						st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change,
						st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, 
						st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, 
						st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, 
						he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id,he_2.fullname AS fchecker_id, 
						st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, 
						st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id,
						st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'无',fg.flag_name) flag_name, 
						st_2.reserve, st_2.version_id, 
						st_2.modified FROM sales_trade_order sto_2";
		$sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=sto_2.shop_id 
							LEFT JOIN sales_trade st_2 ON st_2.trade_id=sto_2.trade_id 
							LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id 
							LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id 
							LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id 
							LEFT JOIN hr_employee he_2 ON he_2.employee_id=st_2.fchecker_id 
							LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id 
							LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id 
							LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason
							LEFT JOIN stock_spec ss ON ss.spec_id=sto_2.spec_id AND st_2.warehouse_id=ss.warehouse_id';
		$sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') sto_3 ON sto_2.rec_id=sto_3.rec_id '.$sql_left_join_str.' ORDER BY st_2.trade_id DESC,sto_3.rec_id DESC';
		$data=array();
		try {
			$total=$this->query($sql_total);
			$total=intval($total[0]['total']);
			$list=$total?$this->query($sql):array();
			$data=array('total'=>$total,'rows'=>$list);
		} catch (\PDOException $e) {
			\Think\Log::write('search_trade_detail_sql:'.$sql);
			\Think\Log::write('search_trades:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}

	// 获取货品总金额
	public function getGoodsAmount($search){
		 try{
		 	if (!empty($search['trade_status'])) {
		 		$search['trade_status'] = implode(',',$search['trade_status']);
		 	}			
			$where_sales_trade=' AND st_1.trade_status <= 110 ';
			$where_sales_trade_order='';				
			D('Trade/TradeDetail')->searchFormDeal($where_sales_trade,$where_sales_trade_order,$where_stockout_order,$search);
			$flag=false;
			$sql_where='';
			if (!empty($where_stockout_order)) {
				$where_stockout_order.=' AND src_order_type=1  ';
				$sql_where.=' LEFT JOIN stockout_order so ON so.src_order_id = sto_1.trade_id ';							
			}
			if(!empty($where_sales_trade))
			{
				$sql_where.=' LEFT JOIN sales_trade st_1 ON st_1.trade_id=sto_1.trade_id ';
			}	
			connect_where_str($sql_where,$where_stockout_order,$flag);	
			connect_where_str($sql_where, $where_sales_trade_order, $flag);
			connect_where_str($sql_where, $where_sales_trade, $flag);
			$sql_amount='SELECT SUM(tmp.goods_amount) as amount FROM (SELECT st_1.goods_amount FROM sales_trade_order sto_1 '.$sql_where.' GROUP BY st_1.trade_id) tmp';
			$amount=$this->query($sql_amount);
			$amount=empty($amount[0]['amount'])?0:$amount[0]['amount'];
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
		return $amount;
	}

	public function exportToExcel($id_list,$search,$type='excel'){
		$search['trade_status'] = implode(',',$search['trade_status']);
		$user_id = get_operator_id();
        $creator=session('account');
        try{

        	if(empty($id_list)){

	            $where_sales_trade=' AND st_1.trade_status <= 110 ';
	            $where_sales_trade_order='';
				D('Trade/TradeDetail')->searchFormDeal($where_sales_trade,$where_sales_trade_order,$where_stockout_order,$search);
				$flag=false;				
				$left_join='';
				if (!empty($where_stockout_order)) {
					$where_stockout_order.=' AND src_order_type=1  ';
					$left_join.=' LEFT JOIN stockout_order so ON so.src_order_id = sto_1.trade_id ';
				}
				connect_where_str($sql_where,$where_stockout_order,$flag);
				connect_where_str($sql_where, $where_sales_trade_order, $flag);
				connect_where_str($sql_where, $where_sales_trade, $flag);
				$where="SELECT sto_1.rec_id FROM sales_trade_order sto_1 LEFT JOIN sales_trade st_1 ON st_1.trade_id=sto_1.trade_id ".$left_join.$sql_where."  GROUP BY sto_1.rec_id ORDER BY sto_1.trade_id desc";
				$rows  = $this->query($where);
            	for($i=0;$i<count($rows);$i++){
               		$id_list[$i]=$rows[$i]['rec_id'];
            	}
            	$where = array('sto_2.rec_id' => array('in', $id_list));
        	}
        	else{
          		$where = array('sto_2.rec_id' => array('in', $id_list));
        	}
			$num = workTimeExportNum($type);
        	if(count($where['sto_2.rec_id']['1'])>$num){
        		if($type == 'csv'){
        			SE(self::EXPORT_CSV_ERROR);
        		}
                SE(self::OVER_EXPORT_ERROR);
            }
        	$cfg_show_telno=get_config_value('show_number_to_star',1);
        	$point_number = get_config_value('point_number',0);
        	$num = "CAST(sto_2.num AS DECIMAL(19,".$point_number.")) num";
			$actual_num = "CAST(sto_2.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			$trade_detail = $this->alias('sto_2')->field("st_2.trade_id,st_2.trade_no,sto_2.spec_code,sh.shop_name,sto_2.src_tid AS src_tids, 
					st_2.buyer_nick,st_2.receiver_name,st_2.receiver_area,st_2.receiver_address,
					IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,
					IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno,
					st_2.receiver_zip,st_2.delivery_term,st_2.trade_status,sto_2.refund_status AS order_refund_status,
					sw.name AS warehouse_name,clg.logistics_name AS logistics_id,st_2.logistics_no,st_2.cs_remark,st_2.buyer_message,
					st_2.print_remark,sto_2.remark,st_2.post_amount,st_2.discount,st_2.receivable,st_2.paid,st_2.dap_amount,
					st_2.cod_amount,st_2.post_cost,st_2.goods_cost,st_2.weight,st_2.invoice_type,st_2.invoice_title,
					st_2.invoice_content,st_2.trade_time,st_2.pay_account, he.fullname AS salesman_id,he_1.fullname AS checker_id,
					st_2.trade_type,st_2.trade_from,sto_2.spec_no,sto_2.goods_no,sto_2.goods_name,sto_2.spec_name,
					sto_2.api_goods_name,sto_2.api_spec_name,".$num.",sto_2.price,sto_2.discount AS order_discount,
					sto_2.order_price,sto_2.share_price,round((sto_2.order_price/sto_2.price),2) AS discount_rate,
					".$actual_num.",sto_2.share_amount,sto_2.share_post,sto_2.paid AS order_paid,sto_2.commission,
					sto_2.suite_name,sto_2.weight AS order_weight,sto_2.gift_type,st_2.id_card,ss.cost_price ")
            		->where($where)
					->join('LEFT JOIN cfg_shop sh ON sh.shop_id=sto_2.shop_id 
							LEFT JOIN sales_trade st_2 ON st_2.trade_id=sto_2.trade_id 
							LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id 
							LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id 
							LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id 
							LEFT JOIN hr_employee he_2 ON he_2.employee_id=st_2.fchecker_id 
							LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id 
							LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason 
							LEFT JOIN stock_spec ss ON ss.spec_id=sto_2.spec_id AND st_2.warehouse_id=ss.warehouse_id')
					->order('st_2.trade_id DESC,sto_2.rec_id DESC')
					->select();
            //订单状态
            $trade_status=array(
                '5'=>'已取消',
                '10'=>'待付款',
                '12'=>'待尾款',
                '15'=>'等未付',
                '16'=>'延时审核',
                '19'=>'预订单前处理',
                '20'=>'前处理',
                '21'=>'委外前处理',
                '22'=>'抢单前处理',
                '25'=>'预订单',
                '27'=>'待抢单',
                '30'=>'待客审',
                '35'=>'待财审',
                '40'=>'待递交仓库',
                '45'=>'递交仓库中',
                '53'=>'已递交仓库',
                '55'=>'已审核',
                '95'=>'已发货',
                '100'=>'已签收',
                '105'=>'部分打款',
                '110'=>'已完成'
                );

            //发票类别
            $invoice_type=array(
                '0'=>'不需要',
                '1'=>'普通发票',
                '2'=>'增值税发票'
                );

            //订单来源
            $trade_from=array(
                '1'=>'API抓单',
                '2'=>'手工建单',
                '3'=>'excel导入'
                );

            //发货条件
            $delivery_term=array(
                '1'=>'款到发货',
                '2'=>'货到付款'
                );

            //订单类型
            $trade_type=array(
                '1'=>'网店销售',
                '2'=>'线下零售',
                '3'=>'售后换货'
                ); 

            //子订单退款状态
            $order_refund_status=array(
                '0'=>'无退款',
                '1'=>'取消退款',
                '2'=>'已申请退款',
                '3'=>'等待退货',
                '4'=>'等待收货',
                '5'=>'退款成功'
                );

            //赠品方式
            $gift_type=array(
                '0'=>'非赠品',
                '1'=>'自动赠品',
                '2'=>'手工赠送'
                );
            for($i=0;$i<count($trade_detail);$i++){
                $trade_detail[$i]['trade_status']=$trade_status[$trade_detail[$i]['trade_status']];
                $trade_detail[$i]['invoice_type']=$invoice_type[$trade_detail[$i]['invoice_type']];
                $trade_detail[$i]['trade_from']=$trade_from[$trade_detail[$i]['trade_from']];
                $trade_detail[$i]['delivery_term']=$delivery_term[$trade_detail[$i]['delivery_term']];
                $trade_detail[$i]['trade_type']=$trade_type[$trade_detail[$i]['trade_type']];
                $trade_detail[$i]['order_refund_status']=$order_refund_status[$trade_detail[$i]['order_refund_status']];
                $trade_detail[$i]['gift_type']=$gift_type[$trade_detail[$i]['gift_type']];
            }

            $excel_header=D('Setting/UserData')->getExcelField('Trade/TradeDetail','trade_detail');
            $title = '订单明细';
            $filename = '订单明细';
            foreach ($excel_header as $v) 
            {
                $width_list[]=20;
            }
        	$trade_log=array();
            for($j=0;$j<count($trade_detail);$j++)
            {
            	$trade_log[]=array(
						'trade_id'=>$trade_detail[$j]['trade_id'],
						'operator_id'=>$user_id,
						'type'=>'55',
						'data'=>'',
						'message'=>'导出订单'.':'.$trade_detail[$j]['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
				);
				unset($trade_detail[$j]['trade_id']);
            }
            D('SalesTradeLog')->addTradeLog($trade_log);
            if($type == 'csv'){
            	ExcelTool::Arr2Csv($trade_detail, $excel_header, $filename);
            }else{
				ExcelTool::Arr2Excel($trade_detail,$title,$excel_header,$width_list,$filename,$creator);
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

    public function searchFormDeal(&$where_sales_trade,&$where_sales_trade_order,&$where_stockout_order,$search){
		//设置店铺权限
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		foreach ($search as $k=>$v){
				if($v==='') continue;
				switch ($k)
				{   //set_search_form_value->Common/Common/function.php
					case 'trade_no'://sales_trade
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
						break;
					case 'warehouse_id':
						set_search_form_value($where_sales_trade ,$k,$v ,'st_1',2,' AND ');
						break;
					case 'src_tids'://手工建单原始单号在sales_trade是空->从子订单中搜索
						set_search_form_value($where_sales_trade_order, 'src_tid', $v,'sto_1', 6,' AND ');
						break;
					case 'buyer_nick':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
						break;
					case 'shop_id':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'receiver_mobile':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ( ');
						set_search_form_value($where_sales_trade, 'receiver_telno', $v,'st_1',1,' OR ',' ) ');
						break;
					case 'cs_remark':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 6,' AND ');
						break;
					case 'logistics_id':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'trade_type':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'trade_from':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'delivery_term':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'revert_reason':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'flag_id':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
						break;
					case 'refund_status':
						set_search_form_value($where_sales_trade_order, $k, $v,'sto_1', 2,' AND ');
						break;
					case 'freeze_reason':
						set_search_form_value($where_sales_trade, $k, $v,'st_1', 8,' AND ');
						break;
					case 'start_time':
						set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 4,' AND ',' >= ');
						break;
					case 'end_time':
						set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 4,' AND ',' <= ');
						break;
					case 'spec_no'://sales_trade_order
						set_search_form_value($where_sales_trade_order, $k, $v,'sto_1', 1,' AND ');
						break;
					case 'receiver_name':
						set_search_form_value($where_sales_trade_order, $k, $v,'st_1', 1,' AND ');
						break;
					case 'goods_no':
						set_search_form_value($where_sales_trade_order, $k, $v,'sto_1', 1,' AND ');
						break;
					case 'trade_status':
						if(strpos($v,'all')!==false&&strlen($v)>3){//包含all的多选条件清除all
							$v=str_replace('all,', '', $v);
							$v=str_replace(',all', '', $v);
						}
                    	set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
                    	break;
                    case 'sendbill_print_status':
                    	set_search_form_value($where_stockout_order, $k, $v,'so', 2,' AND ');
                    break;
                    case 'logistics_print_status':
                    	set_search_form_value($where_stockout_order, $k, $v,'so', 2,' AND ');
                    break;
                    case 'consign_time_start':
                    	set_search_form_value($where_stockout_order, 'consign_time', $v,'so', 4,' AND ',' >= ');
					break;
				    case 'consign_time_end':
				    	set_search_form_value($where_stockout_order, 'consign_time', $v,'so', 4,' AND ',' <= ');
					break;
					case 'pay_start_time':
                    	set_search_form_value($where_sales_trade, 'pay_time', $v,'st_1', 4,' AND ',' >= ');
					break;
				    case 'pay_end_time':
				    	set_search_form_value($where_sales_trade, 'pay_time', $v,'st_1', 4,' AND ',' <= ');
					break;
				}
			}
    }
}