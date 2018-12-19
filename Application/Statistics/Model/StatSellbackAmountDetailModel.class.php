<?php
/**
 * 售后退款金额的表Model
 */
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;



class StatSellbackAmountDetailModel extends Model
{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatSellbackAmountDetail($page=1, $rows=20, $search = array(), $sort = 'modified', $order = 'desc'){
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
				case 'process_status':
					set_search_form_value($where_sales_refund, $k, $v, 'sr_1', 2, ' AND ');
					break;
				case 'created_start':
					set_search_form_value($where_sales_refund, 'created', $v,'sr_1',3,' AND ', '>=');
					break;
				case 'created_end':
					set_search_form_value($where_sales_refund, 'created', $v,'sr_1',3,' AND ', '<=');
					break;
				case 'finish_from':
					set_search_form_value($where_sales_refund, 'modified', $v,'sr_1',3,' AND ', '>=');
					break;
				case 'finish_to':
					set_search_form_value($where_sales_refund, 'modified', $v,'sr_1',3,' AND ', '<=');
					break;
			}
		}
		$sql_sel_limit="SELECT sr_1.refund_id FROM sales_refund sr_1 WHERE TRUE";
		$sql_sel_limit.=$where_sales_refund;
		$sql_count = "SELECT COUNT(1) AS total FROM sales_refund sr_1 WHERE TRUE".$where_sales_refund;
        $sql = "SELECT DISTINCT sr.refund_id,sr.pay_account,sr.refund_amount,sr.remark,sr.refund_no,cs.shop_name AS shop_id,sr.logistics_name as logistics_id,sr.type,he.fullname AS operator_id,cor.title AS reason_id,
	           sr.src_no,sr.process_status,sr.status,sr.tid, sr.trade_no,sr.created,sr.buyer_nick,sr.receiver_name,sr.goods_amount,sr.modified,st.warehouse_id, IF(st.warehouse_id=0,'无',cw.name) AS send_warehouse_id,
	           cl.logistics_name AS send_logistics_id,st.logistics_no AS send_logistics_no,st.consign_time AS send_time
               FROM sales_refund sr
               LEFT JOIN stockout_order st on st.src_order_no = sr.trade_no AND st.src_order_type=1 
               LEFT JOIN stockin_order so ON so.src_order_id=sr.refund_id AND so.src_order_type=3 
               LEFT JOIN cfg_shop cs ON cs.shop_id=sr.shop_id 
               LEFT JOIN hr_employee he ON he.employee_id=sr.operator_id 
               LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = st.warehouse_id
               LEFT JOIN cfg_logistics cl ON cl.logistics_id = st.logistics_id   
               LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr.reason_id AND cor.class_id=4  
               INNER JOIN (".$sql_sel_limit.") t ON t.refund_id=sr.refund_id          
               ORDER BY ".$order." LIMIT ".$limit;   
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
		//调用ExcelTool时传该参数值
		$creator = session('account');
		$data = array();
		$data = $this->getStatSellbackAmountDetail(1,4001,$search);
		$finaldata = array();
		try{
			$num = workTimeExportNum();
			if($data['total']>$num){
				SE(self::OVER_EXPORT_ERROR);
			}
			//某些需要替换的字段
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
			foreach($data['rows'] as $k=>$v){
				$v['type'] = isset($refund_type[$v['type']])? $refund_type[$v['type']]:$v['type'];
				$v['process_status'] = isset($refund_process_status[$v['process_status']])? $refund_process_status[$v['process_status']]:$v['process_status'];
				$v['status'] = isset($api_refund_status[$v['status']])? $api_refund_status[$v['status']]:$v['status'];
				$finaldata[] = $v;

				$amount['goods_amount'] += $v['goods_amount'];
				$amount['refund_amount'] += $v['refund_amount'];
			}
			$finaldata[] = $amount;
			$excel_header = D('Setting/UserData')->getExcelField('Statistics/StatSellbackAmountDetail','stat_sellback_amount_detail');
			$title = '售后退款明细表';
			$filename = '售后退款明细表';
			foreach ($excel_header as $v) {
				$width_list[] = '20';
			}
			ExcelTool::Arr2Excel($finaldata,$title,$excel_header,$width_list,$filename,$creator);
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