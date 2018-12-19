<?php
namespace Trade\Model;

use Think\Model;
class OriginalRefundModel extends Model{
	protected $tableName = "api_refund";
	protected $pk        = "refund_id";
	
	public function getApiRefund($fields,$where = array(),$alias='',$join=array()){
		try {
			$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getApiRefundList-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function queryApiRefund($page = 1, $rows = 20, $search = array(), $sort = 'ar.refund_id', $order = 'desc'){
		$where_api_refund=' true ';
		$where_sales_trade='';
		try{
			$shop_list = D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            $where = " true ";
            foreach ($search as $k=>$v){
            	if($v===""){continue;}
            	switch($k){
            		case "type":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 2,' AND ');
            			break;
            		case "process_status":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 2,' AND ');
            			break;
            		case "refund_no":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 1,' AND ');
            			break;
            		case "tid":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 1,' AND ');
            			break;
            		case "buyer_nick":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 1,' AND ');
            			break;
            		case "shop_id":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 2,' AND ');
            			break;
            		case "logistics_no":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 1,' AND ');
            			break;
            		case "status":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 2,' AND ');
            			break;
            		case "trade_status":
            			set_search_form_value($where_sales_trade, $k, $v, 'st', 2,' AND ');
            			break;
            		case "reason":
            			if($v=="全部") break;
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 1,' AND ');
            			break;
            		case "operator_id":
            			set_search_form_value($where_api_refund, $k, $v, 'ar', 2,' AND ');
            			break;
            		case "start_time":
            			set_search_form_value($where_api_refund, 'refund_time', $v, 'ar', 3,' AND ',' >= ');
            			break;
            		case "end_time":
            			set_search_form_value($where_api_refund, 'refund_time', $v, 'ar', 3,' AND ',' <= ');
            			break;
            		case "start_amount":
            			set_search_form_value($where_api_refund, 'refund_amount', $v, 'ar', 9,' AND ',' >= ');
            			break;
            		case "end_amount":
            			set_search_form_value($where_api_refund, 'refund_amount', $v, 'ar', 9,' AND ',' <= ');
            			break;
            	}
            }
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            $order = addslashes($order);
            $left_join='';
            if(!empty($where_sales_trade)){
            	$where_api_refund.=$where_sales_trade;
            	$left_join.=' LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid AND sto.shop_id=ar.shop_id 
            			LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id';
            }
            $sql_result = "SELECT ar.refund_id FROM api_refund ar $left_join WHERE $where_api_refund GROUP BY ar.refund_id ORDER BY $order LIMIT $limit";
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql="SELECT ar_1.refund_id AS id, ar_1.platform_id, cs.shop_name AS shop_id, ar_1.refund_no, ar_1.tid, ar_1.type, ar_1.status, 
            		ar_1.process_status, ar_1.guarantee_mode, ar_1.cs_status, ar_1.advance_status, ar_1.op_constraint, ar_1.refund_version, 
            		ar_1.pay_account, ar_1.pay_no, ar_1.refund_amount, ar_1.actual_refund_amount, ar_1.current_phase_timeout, ar_1.title, 
            		ar_1.logistics_name, ar_1.logistics_no, ar_1.buyer_nick, ar_1.address, ar_1.refund_time, ar_1.operator_id, ar_1.is_aftersale, 
            		ar_1.refund_mask, ar_1.is_external, ar_1.reason, ar_1.remark, ar_1.modify_flag, ar_1.tag, ar_1.modified, ar_1.created, 
            		he.fullname as operator_id 
            		FROM api_refund ar_1 
            		INNER JOIN (".$sql_result.") ar_2 ON ar_2.refund_id=ar_1.refund_id 
            		LEFT JOIN cfg_shop cs ON cs.shop_id=ar_1.shop_id 
            		LEFT JOIN hr_employee he ON he.employee_id=ar_1.operator_id ";
            $result         = $this->query($sql);
            $sql_count      = "SELECT COUNT(distinct(ar.refund_id)) AS total FROM api_refund ar LEFT JOIN cfg_shop cs ON (ar.shop_id=cs.shop_id)";
            $sql_count      = $where_api_refund == "" ? $sql_count : $sql_count . " $left_join where $where_api_refund ";
            $count          = $this->query($sql_count);
            $count          = $count[0]["total"];
            $data           = array();
            $data['rows']   = $result;
            $data['total']  = $count;
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-queryApiRefund-'.$e->getMessage());
			$data["rows"]  = "";
			$data["total"] = 0;
		}
		return $data;
	}
	
	//递交原始退款单
	public function submitOriginalRefund($id) {
		try {
			$ids = "";
			if (count($id) == 0) {
				$sql    = "SELECT at.refund_id AS id FROM api_refund at WHERE at.process_status=0 AND type=1 LIMIT 100";
				$result = $this->query($sql);
				$id     = array();
				foreach ($result as $k => $v) {
					$id[] = $v["id"];
				}
			}
			foreach ($id as $v) {
				$ids = $ids . $v . ",";
			}
			$sql = "CALL SP_SALES_DELIVER_REFUND_SOME(" . "'" . $ids . "'" . ")";
			$uid = get_operator_id();
			$this->execute("set @cur_uid=$uid");
			$result = $this->query($sql);
			$res    = array();
			// $result=D('DeliverTrade')->deliver(get_operator_id(),$id);
			if (count($result) == 0) {
				$res["status"] = 0;
				$res["info"]   = "";
			} else {
				$res["status"] = 1;
				$res["info"]   = $result;
			}
			return $res;
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			$res["status"] = 2;
			$res["info"]   =self::PDO_ERROR;
			return $res;
		}catch (\Exception $e) {
			Log::write($e->getMessage());
			$res["status"] = 2;
			$res["info"]   = self::PDO_ERROR;
			return $res;
		}
	}
	
	//原始退款单货品列表tabs
	public function getApiRefundGoods($id){
		$id=intval($id);
		$data=array();
		try{
			$sql="SELECT aro.order_id, aro.platform_id, aro.shop_id, aro.oid, aro.refund_no, aro.status, aro.goods_name, aro.spec_no, 
					aro.num, aro.price, aro.total_amount, aro.goods_id, aro.spec_id, aro.goods_no, aro.spec_no, aro.return_time, 
					aro.remark, aro.created, aro.modified 
					FROM api_refund_order aro 
					LEFT JOIN api_refund ar ON aro.shop_id=ar.shop_id AND aro.refund_no=ar.refund_no  
					WHERE ar.refund_id=".$id;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-getApiRefundGoods-'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function getSalesRefund($id){
		$id=intval($id);
		$data=array();
		try{
			$sql="SELECT sr.refund_no, sr.platform_id, sr.src_no, sr.type, sr.process_status, sr.status, sr.guarantee_mode, sr.cs_status, 
					cs.shop_name AS shop_id, he.fullname AS salesman_id, sr.advance_status, sr.op_constraint, sr.refund_version, sr.consign_mode, 
					sr.is_goods_received, sr.swap_trade_id, sr.pay_account, sr.pay_no, sr.goods_amount, sr.refund_amount, sr.exchange_amount, 
					sr.actual_refund_amount, sr.direct_refund_amount, sr.paid, sr.tid, sr.trade_no, sr.trade_id, sr.customer_id, sr.logistics_name, 
					sr.logistics_no, sr.buyer_nick, sr.receiver_name, sr.receiver_address, sr.receiver_telno, sr.return_name, sr.return_mobile, 
					sr.return_telno, sr.return_address, sr.swap_receiver, sr.swap_mobile, sr.swap_telno, sr.swap_province, sr.swap_city, 
					sr.swap_district, sr.swap_area, sr.swap_warehouse_id, sr.swap_address, sr.warehouse_id, sr.warehouse_type, sr.wms_status, 
					sr.wms_result, sr.outer_no, sr.refund_time, sr.reason_id, sr.flag_id, sr.remark, sr.from_type, sr.bad_reason, sr.return_mask, 
					sr.operator_id, sr.sync_status, sr.sync_result, sr.note_count, sr.version_id, sr.created, sr.modified, sr.stockin_pre_no 
					FROM sales_refund sr 
					LEFT JOIN api_refund ar ON sr.src_no=ar.refund_no AND sr.platform_id=ar.platform_id 
					LEFT JOIN cfg_shop cs ON cs.shop_id=sr.shop_id 
					LEFT JOIN hr_employee he ON he.employee_id=sr.salesman_id 
					WHERE ar.refund_id=".$id;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-getSalesRefund-'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function getSalesTrade ($id){
		$id=intval($id);
		$data=array();
		try{
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$point_number = get_config_value('point_number',0);
			$goods_count = "CAST(st.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
			$raw_goods_count = "CAST(st.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";
			$sql="SELECT  st.trade_id AS id,st.flag_id, st.trade_no, st.platform_id, sh.shop_name ,st.warehouse_id, 
					sw.name AS warehouse_name, st.warehouse_type, st.src_tids, st.pay_account, st.trade_status, st.check_step, 
					st.consign_status, st.trade_from, st.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st.delivery_term=2,st.trade_time,
					IF(st.pay_time>'1000-01-01 00:00:00',st.pay_time,st.trade_time))) handle_days,st.delivery_term, st.freeze_reason, 
					st.refund_status, st.unmerge_mask, st.fenxiao_type, st.fenxiao_nick, st.trade_time, st.pay_time, st.delay_to_time, 
					".$goods_count.", st.goods_type_count, 
					IF(st.single_spec_no='','多种货品',IF(gsu.suite_name!='',gsu.suite_name,gg.goods_name)) single_spec_no, 
					".$raw_goods_count.", st.raw_goods_type_count, st.customer_type, st.customer_id, st.buyer_nick, st.id_card_type, 
					st.id_card, st.receiver_name, st.receiver_country, st.receiver_province, st.receiver_city, st.receiver_district, 
					st.receiver_address,IF(".$cfg_show_telno."=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,
					IF(".$cfg_show_telno."=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno, st.receiver_zip, 
					st.receiver_area, st.receiver_ring, st.receiver_dtb, st.to_deliver_time, st.dist_center, st.dist_site, st.is_prev_notify, 
					clg.logistics_name AS logistics_id, st.logistics_no, st.buyer_message, st.cs_remark, st.remark_flag, st.print_remark, 
					st.note_count, st.buyer_message_count, st.cs_remark_count, st.cs_remark_change_count, st.goods_amount, st.post_amount, 
					st.other_amount, st.discount, st.receivable, st.discount_change, st.trade_prepay, st.dap_amount, st.cod_amount, 
					st.pi_amount, st.ext_cod_fee, st.goods_cost, st.post_cost, st.other_cost, st.profit, st.paid, st.weight, st.volume, 
					st.tax, st.tax_rate, st.commission, st.invoice_type, st.invoice_title, st.invoice_content, st.invoice_id,
					he.fullname AS salesman_id, st.sales_score, st.fchecker_id, st.checkouter_id, st.allocate_to, st.flag_id, st.bad_reason, 
					st.is_sealed, st.gift_mask, st.split_from_trade_id, st.large_type, st.stockout_no, st.logistics_template_id, 
					st.sendbill_template_id, st.revert_reason, st.cancel_reason, st.is_unpayment_sms, st.package_id, st.trade_mask,
					IF(st.flag_id=0,'无',fg.flag_name) flag_name, st.reserve, st.version_id, st.modified, st.created, st.stockout_no 
					FROM sales_trade st 
					LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id 
					LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id 
					LEFT JOIN hr_employee he ON he.employee_id= st.salesman_id 
					LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st.warehouse_id 
					LEFT JOIN cfg_flags fg ON fg.flag_id=st.flag_id 
					LEFT JOIN goods_spec gsp ON gsp.spec_no=st.single_spec_no AND gsp.deleted=0 
					LEFT JOIN goods_goods gg ON gg.goods_id=gsp.goods_id 
					LEFT JOIN goods_suite gsu ON gsu.suite_no=st.single_spec_no AND gsu.deleted=0 
					LEFT JOIN sales_trade_order sto ON st.trade_id=sto.trade_id  
					LEFT JOIN api_refund ar ON sto.src_tid=ar.tid AND sto.shop_id=ar.shop_id 
					WHERE ar.refund_id= ". $id ." GROUP BY st.trade_id";
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-getSalesTrade-'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
}