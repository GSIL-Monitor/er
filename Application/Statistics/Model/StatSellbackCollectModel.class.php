<?php
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class StatSellbackCollectModel extends Model{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatSellbackCollect($page=1, $rows=20, $search = array(), $sort = 'spec_id', $order = 'desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$search['warehouse_id'].=',0';
		$order=$sort." ".$order;
		$where_sales_refund='';
		$where_goods_spec='';
		$where_goods_goods='';
		$where_sales_refund_log='';
		foreach ($search as $k=>$v){
			if($v==="") continue;
			switch ($k){
				case 'shop_id':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
					break;
				case 'warehouse_id':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
					break;
				case 'spec_no':
					set_search_form_value($where_goods_spec, $k, $v, 'gs',1,' AND ');
					break;
				case 'goods_no':
					set_search_form_value($where_goods_goods, $k, $v, 'gg',1,' AND ');
					break;
				case 'goods_name':
					set_search_form_value($where_goods_goods, $k, $v, 'gg',1,' AND ');
					break;
				case 'class_id':
					set_search_form_value($where_goods_goods, $k, $v, 'gg',7,' AND ');
					break;
				case 'brand_id':
					set_search_form_value($where_goods_goods, $k, $v, 'gg',2,' AND ');
					break;
				case 'process_status':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
					break;
				case 'remark':
					set_search_form_value($where_sales_refund, $k, $v,'sr',10,' AND ');
					break;
				case 'created_start':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '>=');
					break;
				case 'created_end':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '<=');
					break;
				case 'agree_start':
					set_search_form_value($where_sales_refund_log, 'created', $v,'srl',3,' AND ', '>=');
					break;
				case 'agree_end':
					set_search_form_value($where_sales_refund_log, 'created', $v,'srl',3,' AND ', '<=');
					break;
			}
		}
		$tmp_table="(SELECT sr.refund_id,sum(sro.total_amount) total_amount, sr.refund_amount,sr.type,sr.warehouse_id 
				FROM sales_refund_order sro
				LEFT JOIN sales_refund sr ON sro.refund_id=sr.refund_id 
				WHERE sr.process_status >= 30 ". $where_sales_refund .' 
				GROUP BY sr.refund_id  ) tm';
		if(!empty($where_sales_refund_log)){
			$inner_join=' INNER JOIN ( SELECT DISTINCT srl.refund_id FROM sales_refund_log srl WHERE srl.type=2 '
					.$where_sales_refund_log.') tmp_stl ON tmp_stl.refund_id =sro.refund_id ';
		}
		$sql_count="SELECT COUNT(1) AS total FROM( SELECT tm.refund_id FROM ".$tmp_table."
				LEFT JOIN sales_refund_order sro ON tm.refund_id = sro.refund_id ".
				$inner_join."
				LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id
				LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id 
				WHERE 1 ".$where_goods_goods.$where_goods_spec." 
				GROUP BY sro.spec_id,tm.warehouse_id) t";
		$sql="SELECT gs.spec_no, gg.goods_no, gg.goods_name, gc.class_name AS class_id, tm.warehouse_id, gb.brand_name AS brand_id, gs.spec_code, gs.spec_name, sro.spec_id, IF(tm.warehouse_id=0,'无',cw.name) AS warehouse_name,   
				SUM(if(tm.type = 1 ,sro.refund_num,0)) refund_num, CAST(SUM(if(tm.type=1,IF(tm.total_amount>0,tm.refund_amount*sro.total_amount/tm.total_amount,0),0)) AS DECIMAL(19,4)) refund_amount,
				SUM(if(tm.type=2 OR tm.type = 3 ,sro.refund_num,0)) return_num, CAST(SUM(if(tm.type=2 OR tm.type = 3,sro.cost_price*sro.refund_num,0)) AS DECIMAL(19,4)) return_cost,
				SUM(if(tm.type=2 OR tm.type = 3,sro.total_amount,0)) return_amount, SUM(sro.stockin_num) stockin_num, SUM(sro.stockin_amount) stockin_amount,
				SUM(if(tm.type=4,sro.refund_num,0)) refund_no_goods_num, CAST(SUM(if(tm.type=4,if(tm.total_amount>0,tm.refund_amount*sro.total_amount/tm.total_amount,0),0)) AS DECIMAL(19,4)) refund_no_goods_amount
				FROM ".$tmp_table."
				LEFT JOIN sales_refund_order sro ON tm.refund_id = sro.refund_id ".
				$inner_join."
				LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id
				LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id 
				LEFT JOIN goods_class gc ON gc.class_id = gg.class_id 
				LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id 
				LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = tm.warehouse_id  
				WHERE 1 ".$where_goods_brand.$where_goods_class.$where_goods_goods.$where_goods_spec." 
				GROUP BY sro.spec_id,tm.warehouse_id  ORDER BY ".$order." LIMIT ".$limit;
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
		$data = $this->getStatSellbackCollect(1,4001,$search);
		$finaldata = array();
		try{
			$num = workTimeExportNum();
			if($data['total']>$num){
				SE(self::OVER_EXPORT_ERROR);
			}
			//统计某些字段的合计值
			$amount = array();
			$amount['spec_no'] = '合计';
			foreach($data['rows'] as $k=>$v){
				$finaldata[] = $v;

				$amount['refund_num'] += $v['refund_num'];
				$amount['refund_amount'] += $v['refund_amount'];
				$amount['return_num'] += $v['return_num'];
				$amount['return_cost'] += $v['return_cost'];
				$amount['return_amount'] += $v['return_amount'];
				$amount['stockin_num'] += $v['stockin_num'];
				$amount['stockin_amount'] += $v['stockin_amount'];
				$amount['refund_no_goods_num'] += $v['refund_no_goods_num'];
				$amount['refund_no_goods_amount'] += $v['refund_no_goods_amount'];
			}
			$finaldata[] = $amount;
			$excel_header = D('Setting/UserData')->getExcelField('Statistics/StatSellbackCollect','stat_sellback_collect');
			$title = '售后退货汇总表';
			$filename = '售后退货汇总表';
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