<?php
/**
 * 售后退款金额的表Model
 */
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;



class StatSellbackAmountModel extends Model
{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatSellbackAmount($page=1, $rows=20, $search = array(), $sort = 'modified', $order = 'desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		$order=$sort." ".$order;
		$where_sales_refund='';
		foreach ($search as $k=>$v){
			if($v==="") continue;
			switch ($k){
				case 'shop_id':
					set_search_form_value($where_sales_refund, $k, $v, 'sr_1', 2, ' AND ');
					break;
				case 'type':
					set_search_form_value($where_sales_refund, $k, $v, 'sr_1', 2, ' AND ');
					break;
				case 'time_start':
					set_search_form_value($where_sales_refund, 'modified', $v,'sr_1',3,' AND ', '>=');
					break;
				case 'time_end':
					set_search_form_value($where_sales_refund, 'modified', $v,'sr_1',3,' AND ', '<=');
					break;
			}
		}
		$sql_sel_limit="SELECT sr_1.refund_id FROM sales_refund sr_1 WHERE sr_1.process_status=90";
		$sql_sel_limit.=$where_sales_refund.' LIMIT '.$limit;
		$sql_count='SELECT COUNT(1) AS total FROM sales_refund sr_1 WHERE sr_1.process_status=90';
		$sql_count.=$where_sales_refund;
		$sql="SELECT sr.refund_id, sr.refund_no, sr.src_no, sr.platform_id, cs.shop_name AS shop_id, sr.type, sr.process_status, sr.status, 
				sr.post_amount, sr.goods_amount,IF( sr.refund_amount>=0, sr.paid,0) paid,IF( sr.refund_amount<0, sr.paid,0) received, 
				sr.sync_status, sr.sync_result, sr.pay_account, sr.modified, sr.pay_no, sr.actual_refund_amount,
				IF( sr.refund_amount>=0, sr.refund_amount,0) refund_amount,IF( sr.refund_amount>=0,0,0- sr.refund_amount) receive_amount, 
				sr.post_amount, sr.other_amount, sr.tid, sr.buyer_nick, sr.receiver_name, sr.refund_time, cor.title AS reason_id, sr.flag_id, 
				sr.remark, sr.customer_id, he.fullname AS operator_id, sr.trade_no, sr.note_count FROM sales_refund sr 
				LEFT JOIN cfg_shop cs ON cs.shop_id=sr.shop_id 
				LEFT JOIN hr_employee he ON he.employee_id=sr.operator_id 
				LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr.reason_id AND cor.class_id=4 
				INNER JOIN (".$sql_sel_limit.") t ON t.refund_id=sr.refund_id 
				ORDER BY  ".$order;
		try{
			$count=$this->query($sql_count);
			$list=$this->query($sql);
			$data=array('total'=>$count[0]['total'],'rows'=>$list);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}

	public function exportToExcel($search){
		$creator = session("account");
		$data = array();
		$data = $this->getStatSellbackAmount(1,4001,$search);
		$finaldata = array();
		try{
			$num = workTimeExportNum();
			if($data['total'] > $num){
				SE(self::OVER_EXPORT_ERROR);
			}
			//需要替换的某些字段
			$refund_type = array(
				'1' => '退款',
				'2' => '退货',
				'3' => '换货',
				'4' => '退款不退货'
				);
			$refund_process_status = array(
				'10' => '已取消',
				'20' => '待审核',
				'30' => '已同意',
				'40' => '已拒绝',
				'50' => '待财审',
				'60' => '待收货',
				'63' => '待推送',
				'64' => '推送失败',
				'65' => '推送成功',
				'70' => '部分到货',
				'80' => '待结算',
				'90' => '已完成'
				);
			$api_refund_status = array(
				'0' => '',
				'1' => '取消退款',
				'2' => '已申请退款',
				'3' => '等待退款',
				'4' => '等待收货',
				'5' => '退款成功'
				);
			//统计某些字段的合计值
			$amount = array();
			$amount['refund_no'] = '合计';
			foreach($data["rows"] as $k=>$v){
				$v['type'] = isset($refund_type[$v['type']])? $refund_type[$v['type']]:$v['type'];
				$v['process_status'] = isset($refund_process_status[$v['process_status']])? $refund_process_status[$v['process_status']]:$v['process_status'];
				$v['status'] = isset($api_refund_status[$v['status']])? $api_refund_status[$v['status']]:$v['status'];
				$finaldata[] = $v;

				$amount['goods_amount'] += $v['goods_amount'];
				$amount['refund_amount'] += $v['refund_amount'];
				$amount['paid'] += $v['paid'];
				$amount['receive_amount'] += $v['receive_amount'];
				$amount['received'] += $v['received'];
				$amount['post_amount'] += $v['post_amount'];
			}
			$finaldata[] = $amount;
			$excel_header = D("Setting/UserData")->getExcelField("Statistics/StatSellbackAmount","stat_sellback_amount");
			$title = "售后退款金额表";
			$filename = "售后退款金额表";
			foreach ($excel_header as $k) {
				$width_list[] = '20';
			}
			ExcelTool::Arr2Excel($finaldata,$title,$excel_header,$width_list,$filename,$creator);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}catch(\BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
	}
}