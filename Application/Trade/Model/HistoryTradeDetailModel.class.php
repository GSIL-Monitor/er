<?php
namespace Trade\Model;
use Think\Model;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Think\Log;
class HistoryTradeDetailModel extends Model{
    protected $tableName = 'sales_trade_order_history';
    protected $pk        = 'rec_id';

    public function queryHistoryTradeDetail(&$where_sales_trade_history,$page,$rows,$search,$sort,$order){
		$where_sales_trade_order_history='';
		D('Trade/HistoryTradeDetail')->searchFormDeal($where_sales_trade_history,$where_sales_trade_order_history,$where_stockout_order,$search);
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;
		$arr_sort=array(
			'shop_name'=>'shop_id',
			'flag_name'=>'flag_id',
			'warehouse_name'=>'warehouse_id',
			'handle_days'=>'trade_id'
		);
		$order = 'stoh_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
		// 在预定义字符之前添加反斜杠的字符串。
		$order = addslashes($order);
		$sql_sel_limit='SELECT stoh_1.rec_id FROM sales_trade_order_history stoh_1 '; //历史订单详细id
		$sql_total='SELECT COUNT(1) AS total FROM sales_trade_order_history stoh_1 ';    //总的记录数
		$flag=false;
		$sql_where='';
		$sql_limit=' ORDER BY '.$order.' LIMIT '.$limit;
		if (!empty($where_stockout_order)) {
			$where_stockout_order.=' AND src_order_type=1  ';
			$sql_where.=' LEFT JOIN stockout_order so ON so.src_order_id = stoh_1.trade_id ';
        }
		if(!empty($where_sales_trade_history))
		{
			$sql_where.=' LEFT JOIN sales_trade_history sth_1 ON sth_1.trade_id=stoh_1.trade_id ';
			$sql_limit=' GROUP BY stoh_1.rec_id ORDER BY '.$order.' LIMIT '.$limit;
        }
		connect_where_str($sql_where, $where_stockout_order,$flag);	
		connect_where_str($sql_where, $where_sales_trade_order_history, $flag);
		connect_where_str($sql_where, $where_sales_trade_history, $flag);
        $sql_sel_limit.=$sql_where;
		$sql_total.=$sql_where;
        $sql_sel_limit.=$sql_limit;	
		$sql_fields_str='';
		$sql_left_join_str='';
		// 获取配置 
		$cfg_show_telno=get_config_value('show_number_to_star',1);
		$point_number = get_config_value('point_number',0);
		$goods_count = "CAST(sth_2.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
		$raw_goods_count = "CAST(sth_2.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";
		$num = "CAST(stoh_2.num AS DECIMAL(19,".$point_number.")) num";
		$actual_num = "CAST(stoh_2.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
		$sql_fields_str="SELECT ss.cost_price,
            stoh_2.rec_id,stoh_2.shop_id,sh.shop_name,stoh_2.goods_no,stoh_2.spec_no,stoh_2.goods_name,stoh_2.spec_name,
						stoh_2.api_goods_name,stoh_2.api_spec_name, ".$num.",stoh_2.price,stoh_2.order_price,".$actual_num.",
						stoh_2.share_price,stoh_2.share_amount,stoh_2.share_post,round((stoh_2.order_price/stoh_2.price),2) AS discount_rate,
						stoh_2.commission,stoh_2.paid AS order_paid,stoh_2.suite_name,stoh_2.gift_type,stoh_2.discount AS order_discount,
						stoh_2.remark,stoh_2.weight AS order_weight,sth_2.trade_id AS id,sth_2.trade_no, sth_2.platform_id,sth_2.warehouse_id,
						sw.name AS warehouse_name, sth_2.warehouse_type, stoh_2.src_tid AS src_tids, sth_2.pay_account, sth_2.trade_status, 
						sth_2.check_step, sth_2.consign_status, sth_2.trade_from, sth_2.trade_type, sth_2.delivery_term, sth_2.freeze_reason, 
						cor.title AS freeze_info,stoh_2.refund_status AS order_refund_status, sth_2.unmerge_mask, 
						sth_2.fenxiao_type, sth_2.fenxiao_nick, sth_2.trade_time, sth_2.pay_time, sth_2.delay_to_time,
						".$goods_count.", sth_2.goods_type_count, sth_2.single_spec_no, ".$raw_goods_count.", 
						sth_2.raw_goods_type_count, sth_2.customer_type, sth_2.customer_id, sth_2.buyer_nick, 
						sth_2.id_card_type, sth_2.id_card, sth_2.receiver_name, sth_2.receiver_country, 
						sth_2.receiver_province, sth_2.receiver_city, sth_2.receiver_district, sth_2.receiver_address,
						IF(".$cfg_show_telno."=0,sth_2.receiver_mobile,INSERT( sth_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,sth_2.receiver_telno,
						INSERT(sth_2.receiver_telno,4,4,'****')) receiver_telno, sth_2.receiver_zip, sth_2.receiver_area, sth_2.receiver_ring,
						sth_2.receiver_dtb, sth_2.to_deliver_time, sth_2.dist_center, sth_2.dist_site, sth_2.is_prev_notify, 
						clg.logistics_name AS logistics_id, sth_2.logistics_no, sth_2.buyer_message, sth_2.cs_remark, sth_2.remark_flag, 
						sth_2.print_remark, sth_2.note_count, sth_2.buyer_message_count, sth_2.cs_remark_count, sth_2.cs_remark_change_count, 
						sth_2.goods_amount, sth_2.post_amount, sth_2.other_amount, sth_2.discount, sth_2.receivable, sth_2.discount_change,
						sth_2.trade_prepay, sth_2.dap_amount, sth_2.cod_amount, sth_2.pi_amount, sth_2.ext_cod_fee, sth_2.goods_cost, 
						sth_2.post_cost, sth_2.other_cost, sth_2.profit, sth_2.paid, sth_2.weight, sth_2.volume, sth_2.tax, sth_2.tax_rate, 
						sth_2.commission, sth_2.invoice_type, sth_2.invoice_title, sth_2.invoice_content, sth_2.invoice_id, 
						he.fullname AS salesman_id, sth_2.sales_score, he_1.fullname AS checker_id,he_2.fullname AS fchecker_id, 
						sth_2.checkouter_id, sth_2.allocate_to, sth_2.flag_id, sth_2.bad_reason, sth_2.is_sealed, sth_2.gift_mask, 
						sth_2.split_from_trade_id, sth_2.large_type, sth_2.stockout_no, sth_2.logistics_template_id, sth_2.sendbill_template_id,
						sth_2.revert_reason, sth_2.cancel_reason, sth_2.is_unpayment_sms, sth_2.package_id, IF(sth_2.flag_id=0,'无',fg.flag_name) flag_name, 
						sth_2.reserve, sth_2.version_id, 
                        sth_2.modified 
                        FROM sales_trade_order_history stoh_2";

		$sql_left_join_str='LEFT JOIN cfg_shop sh           ON sh.shop_id=stoh_2.shop_id 
							LEFT JOIN sales_trade_history sth_2      ON sth_2.trade_id=stoh_2.trade_id 
							LEFT JOIN cfg_logistics clg     ON clg.logistics_id=sth_2.logistics_id 
							LEFT JOIN hr_employee he        ON he.employee_id= sth_2.salesman_id 
							LEFT JOIN hr_employee he_1      ON he_1.employee_id=sth_2.checker_id 
							LEFT JOIN hr_employee he_2      ON he_2.employee_id=sth_2.fchecker_id 
							LEFT JOIN cfg_warehouse sw      ON sw.warehouse_id=sth_2.warehouse_id 
							LEFT JOIN cfg_flags fg          ON fg.flag_id=sth_2.flag_id 
							LEFT JOIN cfg_oper_reason cor   ON cor.reason_id=sth_2.freeze_reason
                            LEFT JOIN stock_spec ss         ON ss.spec_id=stoh_2.spec_id 
                            AND sth_2.warehouse_id=ss.warehouse_id';
		$sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') stoh_3 ON stoh_2.rec_id=stoh_3.rec_id '.$sql_left_join_str.' ORDER BY sth_2.trade_id DESC,stoh_3.rec_id DESC';
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

    public function searchFormDeal(&$where_sales_trade_history,&$where_sales_trade_order_history,&$where_stockout_order,$search){
		//设置店铺权限
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);

		foreach ($search as $k=>$v){
				if($v==='') continue;
				switch ($k)
				{   //set_search_form_value->Common/Common/function.php
					case 'trade_no'://sales_trade
						set_search_form_value($where_sales_trade_history, $k, $v,'sth_1', 1,' AND ');
						break;
					case 'buyer_nick':
						set_search_form_value($where_sales_trade_history, $k, $v,'sth_1', 1,' AND ');
						break;
					case 'shop_id':
						set_search_form_value($where_sales_trade_history, $k, $v,'sth_1', 2,' AND ');
						break;
					case 'end_time':
						set_search_form_value($where_sales_trade_history, 'trade_time', $v,'sth_1', 4,' AND ',' <= ');
						break;
					case 'start_time':
						set_search_form_value($where_sales_trade_history, 'trade_time', $v,'sth_1', 4,' AND ',' >= ');
						break;
					case 'trade_status':
						if(strpos($v,'all')!==false&&strlen($v)>3){//包含all的多选条件清除all
							$v=str_replace('all,', '', $v);
							$v=str_replace(',all', '', $v);
						}
                    	set_search_form_value($where_sales_trade_history, $k, $v,'sth_1', 2,' AND ');
                    	break;
				}
			}
	}
	
	public function exportToExcel($id_list,$search,$type='excel'){
		$user_id = get_operator_id();	
        $creator=session('account');	

        try{
        	if(empty($id_list)){
				$where_sales_trade_history=' AND sth_1.trade_status <= 110 ';
				if(!$search['start_time']){
					$search['start_time'] = date('Y-m-d',strtotime('-6 months'));
					$search['end_time']= date('Y-m-d',strtotime('-3 months'));
				}
	            $where_sales_trade_order_history='';
				D('Trade/HistoryTradeDetail')->searchFormDeal($where_sales_trade_history,$where_sales_trade_order_history,$where_stockout_order,$search);
				$flag=false;
				$left_join='';
				if (!empty($where_stockout_order)) {
					$where_stockout_order.=' AND src_order_type=1  ';
					$left_join.=' LEFT JOIN stockout_order so ON so.src_order_id = stoh_1.trade_id ';
				}
				connect_where_str($sql_where,$where_stockout_order,$flag);
				connect_where_str($sql_where, $where_sales_trade_order_history, $flag);
				connect_where_str($sql_where, $where_sales_trade_history, $flag);
				$where="SELECT stoh_1.rec_id FROM sales_trade_order_history stoh_1 LEFT JOIN sales_trade_history sth_1 ON sth_1.trade_id=stoh_1.trade_id ".$left_join.$sql_where."  GROUP BY stoh_1.rec_id ORDER BY stoh_1.trade_id desc";
				$rows  = $this->query($where);
            	for($i=0;$i<count($rows);$i++){
               		$id_list[$i]=$rows[$i]['rec_id'];
				}
            	$where = array('stoh_2.rec_id' => array('in', $id_list));
        	}
        	else{
          		$where = array('stoh_2.rec_id' => array('in', $id_list));
        	}
			$num = workTimeExportNum($type);	//获取工作时间
        	if(count($where['stoh_2.rec_id']['1'])>$num){
        		if($type == 'csv'){
        			SE(self::EXPORT_CSV_ERROR);
        		}
                SE(self::OVER_EXPORT_ERROR);
            }
        	$cfg_show_telno=get_config_value('show_number_to_star',1);
        	$point_number = get_config_value('point_number',0);
        	$num = "CAST(stoh_2.num AS DECIMAL(19,".$point_number.")) num";
			$actual_num = "CAST(stoh_2.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			$trade_detail = $this->alias('stoh_2')->field("sth_2.trade_id,sth_2.trade_no,sh.shop_name,stoh_2.src_tid AS src_tids, 
					sth_2.buyer_nick,sth_2.receiver_name,sth_2.receiver_area,sth_2.receiver_address,
					IF(".$cfg_show_telno."=0,sth_2.receiver_mobile,INSERT( sth_2.receiver_mobile,4,4,'****')) receiver_mobile,
					IF(".$cfg_show_telno."=0,sth_2.receiver_telno,INSERT(sth_2.receiver_telno,4,4,'****')) receiver_telno,
					sth_2.receiver_zip,sth_2.delivery_term,sth_2.trade_status,stoh_2.refund_status AS order_refund_status,
					sw.name AS warehouse_name,clg.logistics_name AS logistics_id,sth_2.logistics_no,sth_2.cs_remark,sth_2.buyer_message,
					sth_2.print_remark,stoh_2.remark,sth_2.post_amount,sth_2.discount,sth_2.receivable,sth_2.paid,sth_2.dap_amount,
					sth_2.cod_amount,sth_2.post_cost,sth_2.goods_cost,sth_2.weight,sth_2.invoice_type,sth_2.invoice_title,
					sth_2.invoice_content,sth_2.trade_time,sth_2.pay_account, he.fullname AS salesman_id,he_1.fullname AS checker_id,
					sth_2.trade_type,sth_2.trade_from,stoh_2.spec_no,stoh_2.goods_no,stoh_2.goods_name,stoh_2.spec_name,
					stoh_2.api_goods_name,stoh_2.api_spec_name,".$num.",stoh_2.price,stoh_2.discount AS order_discount,
					stoh_2.order_price,stoh_2.share_price,round((stoh_2.order_price/stoh_2.price),2) AS discount_rate,
					".$actual_num.",stoh_2.share_amount,stoh_2.share_post,stoh_2.paid AS order_paid,stoh_2.commission,
					stoh_2.suite_name,stoh_2.weight AS order_weight,stoh_2.gift_type,sth_2.id_card,ss.cost_price ")
            		->where($where)
					->join('LEFT JOIN cfg_shop sh ON sh.shop_id=stoh_2.shop_id 
							LEFT JOIN sales_trade_history sth_2 ON sth_2.trade_id=stoh_2.trade_id 
							LEFT JOIN cfg_logistics clg ON clg.logistics_id=sth_2.logistics_id 
							LEFT JOIN hr_employee he ON he.employee_id= sth_2.salesman_id 
							LEFT JOIN hr_employee he_1 ON he_1.employee_id=sth_2.checker_id 
							LEFT JOIN hr_employee he_2 ON he_2.employee_id=sth_2.fchecker_id 
							LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=sth_2.warehouse_id 
							LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sth_2.freeze_reason 
							LEFT JOIN stock_spec ss ON ss.spec_id=stoh_2.spec_id AND sth_2.warehouse_id=ss.warehouse_id')
					->order('sth_2.trade_id DESC,stoh_2.rec_id DESC')
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
            $title = '历史订单明细';
            $filename = '历史订单明细';
            foreach ($excel_header as $v) 	//excel表头宽度
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
	
}