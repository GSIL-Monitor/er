<?php
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;

class StatSellbackAnalysisModel extends Model{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatSellbackAnalysis($page=1, $rows=20, $search = array(), $sort = 'shop_id', $order = 'desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		$order=$sort." ".$order;
		$where_sales_refund='';
		foreach ($search as $k=>$v){
			if($v==='') continue;
			switch ($k){
				case 'stat_type':
					if($v=='shop_id'){
						set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
					}
					break;
				case 'created_start':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',4,' AND ', '>=');
					break;
				case 'created_end':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',4,' AND ', '<=');
					break;
			}
		}
		$group=' shop_id ';
		$search['stat_type']==''?true:$group=$search['stat_type'];
		$tmp_table="( SELECT sr.refund_id, SUM(stockin_amount) AS stockin_amount, CAST(SUM(sro.cost_price*sro.refund_num) AS DECIMAL(19,4)) AS return_cost, 
					SUM(refund_num) refund_num, SUM(stockin_num) stockin_num 
					FROM sales_refund_order sro 
					LEFT JOIN sales_refund sr ON sr.refund_id = sro.refund_id 
					WHERE sr.process_status >=30 ".$where_sales_refund." 
					GROUP BY sr.refund_id ) AS tm ";
		$sql_count="SELECT COUNT(1) AS total FROM (SELECT sr.shop_id FROM sales_refund sr , ".$tmp_table." 
					WHERE tm.refund_id = sr.refund_id GROUP BY shop_id) tm1";
		$sql="SELECT COUNT(DISTINCT sr.refund_id) as refund_count, sum(IF(sr.type = 4,sr.refund_amount,0)) AS refund_amount, 
				SUM(if(sr.type = 1,0,sr.goods_amount)) AS return_amount, sum(tm.return_cost) AS return_cost,
				sum(tm.stockin_amount) AS stockin_amount, sum(tm.stockin_num) AS stockin_num, sum(tm.refund_num) AS refund_num,
				cs.shop_name AS shop_id, he.fullname AS operator_id, IF(sr.reason_id=0,'无',cor.title) AS reason_id  
				FROM sales_refund sr LEFT JOIN cfg_shop cs ON cs.shop_id=sr.shop_id 
				LEFT JOIN hr_employee he ON he.employee_id=sr.operator_id 
				LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr.reason_id , 
				".$tmp_table." 
				WHERE tm.refund_id = sr.refund_id GROUP BY ".$group." ORDER BY ".$order." LIMIT ".$limit;
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
		$data = $this->getStatSellbackAnalysis(1,4001,$search);
		$finaldata = array();
		try{
			$num = workTimeExportNum();
			if($data['total']>$num){
				SE(self::OVER_EXPORT_ERROR);
			}
			//统计某些字段的合计值
			$amount = array();
			$amount[$search['stat_type']] = '合计';
			foreach($data['rows'] as $k=>$v){
				$finaldata[] = $v;

				$amount['refund_count'] += $v['refund_count'];
				$amount['refund_num'] += $v['refund_num'];
				$amount['refund_amount'] += $v['refund_amount'];
				$amount['return_amount'] += $v['return_amount'];
				$amount['return_cost'] += $v['return_cost'];
				$amount['stockin_num'] += $v['stockin_num'];
			}
			$finaldata[] = $amount;
			$excel_header = D('Setting/UserData')->getExcelField('Statistics/StatSellbackAnalysis','stat_sellback_analysis');
			if($search['stat_type'] == 'operator_id'){
				unset($excel_header['shop_id']);
				$excel_header = array_merge(array('operator_id'=>'业务员'),$excel_header);
			}elseif ($search['stat_type'] == 'reason_id') {
				unset($excel_header['shop_id']);
				$excel_header = array_merge(array('reason_id'=>'退换原因'),$excel_header);
			}
			$title = '退换统计分析表';
			$filename = '退换统计分析表';
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