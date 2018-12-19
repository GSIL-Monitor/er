<?php
namespace Trade\Model;
class FinancialCheckModel extends TradeModel{
	public function revertTradeCheck($trade_id,$form,&$fail=array(),&$success=array()){
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
					array('st.trade_id'=>array('eq',$trade_id)),
					'st'
					);
	        if(empty($res_trade)){
	        	SE("订单不存在");
	        }
	        if((int)$res_trade['trade_status'] != 35){
	        	SE("订单状态错误，禁止驳回");
	        }
	        $flag_id = D('Setting/Flag')->getFlagId('驳回订单',1);
	        $update_sales_trade_data = array(
	            "trade_status"=>"30",
	            "fchecker_id"=>"0",
	            "check_step"=>"0",
	            "consign_status"=>"0",
	            "revert_reason"=>"{$form['reason_id']}",
	            "flag_id"=>"{$flag_id}",	             
	        );
	        $update_sales_trade_cond = array(
	            'trade_id' => $res_trade['split_from_trade_id'],
	            'trade_status' => array('neq',5),
	        );
	        D('Trade/Trade')->updateSalesTrade($update_sales_trade_data,$update_sales_trade_cond);

	        $debug_point_str = 'financial_check-revert_trade_check-call update_log';
	        $sales_trade_log_data = array(
	           'data'=>'98',
	        );
	        $sales_trade_log_cond = array(
	           'trade_id'  => $res_trade['split_from_trade_id'],
	           '_complex'  => array(
	               'type'=>array('eq','46'),
	            ),
	        );
	       	D('Trade/SalesTradeLog')->updateTradeLog($sales_trade_log_data,$sales_trade_log_cond);

	       	//插入订单日志
	       	$stl_insert_data = array(
	            "trade_id" => $res_trade['split_from_trade_id'],
	            "operator_id"=>$form['operator_id'],
	            "type"=>30,
	            "message"=>array("exp","CONCAT(IF({$form['is_force']},'强制驳回已发货出库单到客审,驳回原因:','财审驳回到客审,驳回原因:'),'{$res_query_reason['title']}')")
	        );
	        D('Trade/SalesTradeLog')->addTradeLog($stl_insert_data);
	       	$success = array(
	       		'id' => $res_trade['split_from_trade_id'],
	       		'revert_reason' => "驳回到客审,驳回原因:".$res_query_reason['title'],
	       	);
		}catch(\PDOException $e){
	        $result_info = $e->getMessage();
	        $fail[] = array(
	            'trade_id' => empty($res_trade['split_from_trade_id'])?$trade_id:$res_trade['split_from_trade_id'],
	            'trade_no' => $res_trade['trade_no'],
	            'result_info'      => self::PDO_ERROR,
	        );
	        $this->rollback();
	        \Think\Log::write($debug_point_str.$result_info."-".$res_trade['trade_no']);
	        return false;
	    }catch(\Think\Exception $e){
	        $result_info = $e->getMessage();
	        $fail[] = array(
	            'trade_id' => $res_trade['split_from_trade_id'],
	            'trade_no' => $res_trade['trade_no'],
	            'result_info' =>$result_info,
	        );
	        $this->rollback();
	        \Think\Log::write($debug_point_str.$result_info."-".$res_trade['trade_no']);
	        return false;
	    }catch(\Exception $e){
	        $result_info = $e->getMessage();
	        $fail[] = array(
	            'trade_id' => $res_trade['split_from_trade_id'],
	            'trade_no' => $res_trade['trade_no'],
	            'result_info' =>$result_info,
	        );
	        $this->rollback();
	        \Think\Log::write($debug_point_str.$result_info."-".$res_trade['trade_no']);
	        return false;
	    }
	    $this->commit();
	    return true;
	}
}