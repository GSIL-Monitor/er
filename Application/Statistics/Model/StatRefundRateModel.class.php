<?php
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class StatRefundRateModel extends Model{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatRefundRate($page=1, $rows=20, $search = array(), $sort = 'spec_id', $order = 'desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		$search['warehouse_id'].=',0';
		$order_table=' tmp.';
		switch($sort){
			case 'spec_no':
			case 'goods_no':
			case 'goods_name':
			case 'spec_code':
			case 'spec_name':
				$sort='spec_id';break;
			case 'class_id':
				$order_table=' gc.';break;
			case 'brand_id':
				$order_table=' gb.';break;
			case 'refund_rate':
				$order_table='';break;
			case 'return_rate':
				$order_table='';break;
		}
		$order=$order_table.$sort.' '.$order;
		$where_sales_trade='';
		$where_sales_log='';
		$where_sales_refund='';
		$where_goods_spec='';
		$where_goods_goods='';
		foreach ($search as $k=>$v){
			if($v==="") continue;
			switch ($k){
				case 'shop_id':
					set_search_form_value($where_sales_trade, $k, $v,'st',2,' AND ');
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
				case 'created_start':
					set_search_form_value($where_sales_log, 'created', $v,'stl',3,' AND ', '>=');
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '>=');
					break;
				case 'created_end':
					set_search_form_value($where_sales_log, 'created', $v,'stl',3,' AND ', '<=');
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '<=');
					break;
			}
		}
		$where=' true ';
		$tmp_table="CREATE TEMPORARY TABLE IF NOT EXISTS tmp_stat_table(
					rec_id INT(11) NOT NULL AUTO_INCREMENT,
					spec_id INT(11) NOT NULL ,
					num DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					refund_num DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					refund_amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					return_num DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					return_amount DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
					PRIMARY KEY (rec_id),
					UNIQUE INDEX UK_tmp_diff_table (spec_id)
					)";
		$where_sales_trade .=$where_goods_goods.$where_goods_spec.$where_sales_log;
		$where_sales_refund.=$where_goods_goods.$where_goods_spec;
		$left_trade  ="";
		$left_refund ="";
		if(!empty($where_goods_goods)||!empty($where_goods_spec)){
			$left_trade .=" LEFT JOIN goods_spec gs ON gs.spec_id =sto.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id ";
			$left_refund.=" LEFT JOIN goods_spec gs ON gs.spec_id =sro.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id ";
		}
		$tmp_table_trade="INSERT INTO tmp_stat_table(spec_id,num,amount) 
				SELECT sto.spec_id, sto.num, sto.num*sto.order_price FROM sales_trade_log stl 
				LEFT JOIN sales_trade st ON st.trade_id =stl.trade_id 
				LEFT JOIN sales_trade_order sto ON sto.trade_id = st.trade_id ".$left_trade.'
				WHERE TRUE '.$where_sales_trade." AND sto.spec_id<>'' AND sto.spec_id<>0 
				ON DUPLICATE KEY UPDATE num = tmp_stat_table.num + VALUES(num), amount = tmp_stat_table.amount + VALUES(amount)";
		$tmp_table_refund="INSERT INTO tmp_stat_table(spec_id,refund_num,refund_amount,return_num,return_amount) 
				SELECT sro.spec_id, IF(sr.type = 1,sro.refund_num,0) refund_num, if(sr.type = 1,sro.refund_num*sro.price,0) refund_amount,
				IF(sr.type = 2 OR sr.type = 3,refund_num,0) return_num, IF(sr.type = 2 OR sr.type = 3,sro.refund_num*sro.price,0) return_amount 
				FROM sales_refund sr LEFT JOIN sales_refund_order sro ON sr.refund_id = sro.refund_id ".$left_refund.' 
				WHERE TRUE '.$where_sales_refund." AND sro.spec_id<>'' AND sro.spec_id<>0 
				ON DUPLICATE KEY UPDATE refund_num = tmp_stat_table.refund_num + VALUES(refund_num), refund_amount = tmp_stat_table.refund_amount + VALUES(refund_amount), 
				return_num = tmp_stat_table.return_num + VALUES(return_num), return_amount = tmp_stat_table.return_amount + VALUES(return_amount)";
		$sql_count="SELECT COUNT(1) AS total FROM tmp_stat_table";
		$sql="SELECT gs.spec_no, gg.goods_no, gg.goods_name, gb.brand_name AS brand_id, gc.class_name AS class_id, gs.spec_code, gs.spec_name, tmp.num, tmp.amount, 
				tmp.refund_num, CAST(IF(tmp.num=0,0,tmp.refund_num/tmp.num) AS DECIMAL(19,4)) refund_rate, tmp.refund_amount,tmp.return_num, 
				CAST(IF(tmp.num=0,0,tmp.return_num/tmp.num) AS decimal(19,4)) return_rate,tmp.return_amount 
				FROM tmp_stat_table tmp 
				LEFT JOIN goods_spec gs ON tmp.spec_id=gs.spec_id 
				LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id 
				LEFT JOIN goods_brand gb ON gb.brand_id=gg.brand_id 
				LEFT JOIN goods_class gc ON gc.class_id=gg.class_id 
				ORDER BY ".$order." LIMIT ".$limit;
		try{
			$this->execute($tmp_table);
			$this->execute("DELETE FROM tmp_stat_table");
			$this->execute($tmp_table_trade);
			$this->execute($tmp_table_refund);
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
		$creator = session('account');
		$data = array();
		$data = $this->getStatRefundRate(1,4001,$search);
		$finaldata = array();
		try{
			$num = workTimeExportNum();
			if($data['total'] > $num){
				SE(self::OVER_EXPORT_ERROR);
			}
			//统计某些字段的合计值
			$amount = array();
			$amount['spec_no'] = '合计';
			foreach ($data['rows'] as $k=>$v) {
				$finaldata[] = $v;

				$amount['num'] += $v['num'];
				$amount['amount'] += $v['amount'];
				$amount['refund_num'] += $v['refund_num'];
				$amount['refund_amount'] += $v['refund_amount'];
				$amount['return_num'] += $v['return_num'];
				$amount['return_amount'] += $v['return_amount'];
			}
			$finaldata[] = $amount;
			$excel_header = D("Setting/UserData")->getExcelField("Statistics/StatRefundRate","stat_refund_rate");
			$title = "退换率统计分析表";
			$filename = "退换率统计分析表";
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