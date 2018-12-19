<?php
namespace Statistics\Model;

use Think\Model;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
class StatSalesmanPerformanceModel extends Model{
	protected $tableName='stat_salesman_performance';
	protected $pk='trade_id';
	
	public function searchFormDeal(&$where,&$where_time,$search,&$left_join_goods_str,&$group_by_str){
		foreach ($search as $k=>$v){
			if($v==="") continue;
			switch ($k){
				case 'shop_id':
					set_search_form_value($where, $k, $v,'st',2,'AND');
					break;
				case 'warehouse_id':
					set_search_form_value($where, $k, $v,'st',2,'AND');
					break;
				case 'brand_id':
					set_search_form_value($where, $k, $v,'gg',2,'AND');
					break;
				case 'class_id':
					$left_join_goods_class_str=set_search_form_value($where, $k, $v,'gc',7,'AND');
					break;
				case 'spec_no':
					set_search_form_value($where, $k, $v,'gs',1,'AND');
					break;
				case 'goods_no':
					set_search_form_value($where, $k, $v,'gg',1,'AND');
					break;
				case 'start_time':
					set_search_form_value($where_time, 'created', $v,'ssp', 3,' AND ',' >= ');
					break;
				case 'end_time':
					set_search_form_value($where_time, 'created', $v,'ssp', 3,' AND ',' <= ');
					break;
				case 'stat_as_day':
					if($v==1){
						$group_by_str.=", DATE(ssp.created) ";
					}
					break;
			}
			
		}
	}
	
	public function loadDataByCondition($page,$rows,$search,$sort,$order){
		$page=intval($page);
		$rows=intval($rows);
		$where=" WHERE st.trade_id=ssp.trade_id ";
		$where_time='';
		$count_num=0;
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$left_join_goods_class='';
		$group_by_str=" GROUP BY ssp.salesman_id ";
 		$this->searchFormDeal($where,$where_time,$search,$left_join_goods_class,$group_by_str);
		$limit=($page-1)*$rows.','.$rows;
		$sql="SELECT DATE(ssp.created) AS sales_date,he.fullname AS salesman_id, ssp.salesman_id AS id, IFNULL(COUNT(st.trade_id),0) AS trade_count,IFNULL(SUM(st.sales_score),0) AS total_sales_score, 0.0000 AS total_refund_price, 
			IFNULL(SUM(st.receivable),0) AS total_receivable,IFNULL(SUM(st.profit),0) AS total_profit,IFNULL(SUM(st.goods_count),0) AS total_goods_count,IFNULL(SUM(st.goods_type_count),0) AS total_goods_type_count,IFNULL(SUM(st.post_amount),0) AS total_post_amount,
			CAST(IFNULL(SUM(st.receivable),0)/(CASE WHEN COUNT(st.trade_id) IS NULL OR COUNT(st.trade_id) = 0 THEN 1 ELSE COUNT(1) END) AS DECIMAL(19,4)) AS trade_avg , IFNULL(SUM(st.receivable),0) AS trade_total
			FROM sales_trade st,stat_salesman_performance ssp 
			LEFT JOIN hr_employee he ON he.employee_id=ssp.salesman_id 
			LEFT JOIN sales_trade_order sto ON ssp.trade_id = sto.trade_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = sto.spec_id 
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.spec_id 
			LEFT JOIN goods_class gc ON gc.class_id = gg.class_id "
			.$left_join_goods_class.$where.$where_time.$group_by_str.
			" ORDER BY ssp.salesman_id DESC LIMIT {$limit}";
		$sql_count="SELECT COUNT(1) total FROM (SELECT DATE(ssp.created) AS sales_date,he.fullname AS salesman_id, IFNULL(COUNT(st.trade_id),0) AS trade_count,IFNULL(SUM(st.sales_score),0) AS total_sales_score,
			IFNULL(SUM(st.receivable),0) AS total_receivable,IFNULL(SUM(st.profit),0) AS total_profit,IFNULL(SUM(st.goods_count),0) AS total_goods_count,IFNULL(SUM(st.goods_type_count),0) AS total_goods_type_count,IFNULL(SUM(st.post_amount),0) AS total_post_amount,
			CAST(IFNULL(SUM(st.receivable),0)/(CASE WHEN COUNT(st.trade_id) IS NULL OR COUNT(st.trade_id) = 0 THEN 1 ELSE COUNT(1) END) AS DECIMAL(19,4)) AS trade_avg , IFNULL(SUM(st.receivable),0) AS trade_total
			FROM sales_trade st,stat_salesman_performance ssp 
			LEFT JOIN hr_employee he ON he.employee_id=ssp.salesman_id 
			LEFT JOIN sales_trade_order sto ON ssp.trade_id = sto.trade_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = sto.spec_id 
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.spec_id 
			LEFT JOIN goods_class gc ON gc.class_id = gg.class_id "
			.$left_join_goods_class.$where.$where_time.$group_by_str.") tmp";
		$sql_refund="SELECT DATE(ssp.created) AS sales_date,ssp.salesman_id,sr.refund_amount AS total_refund_price,-sr.refund_amount AS trade_total 
					FROM sales_refund sr,sales_refund_order sro,stat_salesman_performance ssp 
					WHERE sr.process_status>=80 AND sr.process_status<=90 AND sr.type>1 AND sro.refund_id = sr.refund_id AND sro.trade_id=ssp.trade_id ".$where_time." GROUP BY sr.refund_id";
		try{
			$refund=$this->query($sql_refund);
			$rows=$this->query($sql);
			$count=$this->query($sql_count);
			if($search['stat_as_day']!=1){
				$rows_new=UtilTool::array2dict($rows,'id','');
				foreach ($refund as $v){
					if($rows_new[$v['salesman_id']]){
						$rows_new[$v['salesman_id']]['total_refund_price']+=floatval($v['total_refund_price']);
						$rows_new[$v['salesman_id']]['trade_total']-=floatval($v['total_refund_price']);
					}
				}
			}else{
				foreach ($rows as $v){
					$rows_new[$v['id'].'_'.$v['sales_date']]=$v;
				}
				foreach ($refund as $v){
					if($rows_new[$v['salesman_id'].'_'.$v['sales_date']]){
						$rows_new[$v['salesman_id'].'_'.$v['sales_date']]['total_refund_price']+=floatval($v['total_refund_price']);
						$rows_new[$v['salesman_id'].'_'.$v['sales_date']]['trade_total']-=floatval($v['total_refund_price']);
					}
				}
			}
			$rows_new=array_values($rows_new);
			$data['rows']=$rows_new;
			$data['total']=$count[0]['total'];
		}catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }
        return $data;
	}
	
	public function exportToExcel($search){
		$user_id = get_operator_id();
		$creator=session('account');
		$where=' WHERE TRUE  ';
		$where_time='';
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$left_join_goods_class_str='';
		$group_by_str=" GROUP BY ssp.salesman_id ";
		$this->searchFormDeal($where,$where_time,$search,$left_join_goods_class,$group_by_str);
		$sql = "SELECT DATE(ssp.created) AS sales_date,he.fullname AS salesman_id, ssp.salesman_id AS id, IFNULL(COUNT(st.trade_id),0) AS trade_count,IFNULL(SUM(st.sales_score),0) AS total_sales_score, 0.0000 AS total_refund_price, 
			IFNULL(SUM(st.receivable),0) AS total_receivable,IFNULL(SUM(st.profit),0) AS total_profit,IFNULL(SUM(st.goods_count),0) AS total_goods_count,IFNULL(SUM(st.goods_type_count),0) AS total_goods_type_count,IFNULL(SUM(st.post_amount),0) AS total_post_amount,
			CAST(IFNULL(SUM(st.receivable),0)/(CASE WHEN COUNT(st.trade_id) IS NULL OR COUNT(st.trade_id) = 0 THEN 1 ELSE COUNT(1) END) AS DECIMAL(19,4)) AS trade_avg , IFNULL(SUM(st.receivable),0) AS trade_total
			FROM sales_trade st,stat_salesman_performance ssp 
			LEFT JOIN hr_employee he ON he.employee_id=ssp.salesman_id 
			LEFT JOIN sales_trade_order sto ON ssp.trade_id = sto.trade_id 
			LEFT JOIN goods_spec gs ON gs.spec_id = sto.spec_id 
			LEFT JOIN goods_goods gg ON gg.goods_id = gs.spec_id 
			LEFT JOIN goods_class gc ON gc.class_id = gg.class_id "
			.$left_join_goods_class_str.$where.$where_time.$group_by_str.
			" ORDER BY ssp.salesman_id DESC";
		$sql_refund="SELECT DATE(ssp.created) AS sales_date,ssp.salesman_id,sr.refund_amount AS total_refund_price,-sr.refund_amount AS trade_total
			FROM sales_refund sr,sales_refund_order sro,stat_salesman_performance ssp
			WHERE sr.process_status>=80 AND sr.process_status<=90 AND sr.type>1 AND sro.refund_id = sr.refund_id AND sro.trade_id=ssp.trade_id ".$where_time." GROUP BY sr.refund_id";
			
		try{
			$refund=$this->query($sql_refund);
			$rows=$this->query($sql);
			if($search['stat_as_day']!=1){
				$rows_new=UtilTool::array2dict($rows,'id','');
				foreach ($refund as $v){
					if($rows_new[$v['salesman_id']]){
						$rows_new[$v['salesman_id']]['total_refund_price']+=floatval($v['total_refund_price']);
						$rows_new[$v['salesman_id']]['trade_total']-=floatval($v['total_refund_price']);
					}
				}
			}else{
				foreach ($rows as $v){
					$rows_new[$v['id'].'_'.$v['sales_date']]=$v;
				}
				foreach ($refund as $v){
					if($rows_new[$v['salesman_id'].'_'.$v['sales_date']]){
						$rows_new[$v['salesman_id'].'_'.$v['sales_date']]['total_refund_price']+=floatval($v['total_refund_price']);
						$rows_new[$v['salesman_id'].'_'.$v['sales_date']]['trade_total']-=floatval($v['total_refund_price']);
					}
				}
			}
			$rows_new=array_values($rows_new);
			$data['rows'] = $rows_new;
			$excel_header = D('Setting/UserData')->getExcelField('Statistics/StatSalesmanPerformance','stat_salesman_performance');
			$width_list = array();
			foreach ($excel_header as $v)
			{
				$width_list[]=20;
			}
			$title = '业务员绩效统计';
			$filename = '业务员绩效统计';
			ExcelTool::Arr2Excel($data['rows'],$title,$excel_header,$width_list,$filename,$creator);
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