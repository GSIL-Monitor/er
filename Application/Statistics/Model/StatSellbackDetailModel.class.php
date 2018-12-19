<?php
namespace Statistics\Model;

use Common\Common\ExcelTool;
use Common\Common\DatagridExtention;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class StatSellbackDetailModel extends Model{
	protected $tableName="sales_refund";
	protected $pk="refund_id";
	
	public function getStatSellbackDetail($page=1, $rows=20, $search = array(), $sort = 'sro.refund_order_id', $order = 'desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$search['warehouse_id'].=',0';
		switch ($sort){
			case "brand_id":
				$sort="gb.brand_id";break;
		}
		$order=$sort." ".$order;
		$where_sales_refund='';
		$where_goods_spec='';
		$where_goods_goods='';
		$displayWarehouse='';
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
				case 'created_start':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '>=');
					break;
				case 'created_end':
					set_search_form_value($where_sales_refund, 'created', $v,'sr',3,' AND ', '<=');
					break;
				case 'process_status':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
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
				case 'remark':
					set_search_form_value($where_sales_refund, $k, $v,'sr',10,' AND ');
					break;
				case 'reason_id':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
				    break;
				case 'type':
					set_search_form_value($where_sales_refund, $k, $v,'sr',2,' AND ');
				    break; 
				case 'tid':
					  set_search_form_value($where_sales_refund, $k, $v, 'sr',1,' AND ');
				    break; 
			}
		}
		$tmp_table="( SELECT sro.refund_order_id
		              FROM sales_refund_order sro 
		              LEFT JOIN sales_refund sr ON sr.refund_id = sro.refund_id 
		              WHERE sr.process_status >= 50 ". $where_sales_refund .") tmp";
		
		$sql_count="SELECT COUNT(sro.refund_order_id)  AS total 
		        FROM ".$tmp_table." 
		        LEFT JOIN sales_refund_order sro ON sro.refund_order_id = tmp.refund_order_id 
		        LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id 
		        LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
		        WHERE 1 ".$where_goods_goods.$where_goods_spec;		
		$sql="SELECT  gg.goods_name,sr.refund_no,sr.process_status,gs.spec_no,if(gmn.type = 1,'否','是') as suite,cs.shop_name as shop_id,sr.receiver_address,he.fullname AS salesman_id,sr.customer_id,sr.trade_no AS sales_tid,sr.logistics_name,sr.logistics_no,
		        sr.buyer_nick,sro.tid,IF(sr.warehouse_id=0,'无',cw.name) AS warehouse_name,gg.goods_no,gs.spec_code,gs.spec_name,gc.class_name AS class_id, gg.brand_id,gb.brand_name AS brand_id, sro.refund_num,sro.stockin_num,sro.stockin_amount,sr.remark,cor.title AS reason_id,sro.remark goods_remark,sr.type,
		        sro.stockin_num,sro.cost_price ,sro.price,sro.discount,sro.stockin_amount ,IF(sr.type=2 or sr.type=3,sro.total_amount,0) AS total_amount,sr.created, sr.refund_amount,sr.guarante_refund_amount,sr.direct_refund_amount,IF(sr.flag_id=0,'无',cf.flag_name) AS flag_id,
		        IF(so.warehouse_id=0,'无',cw1.name) AS send_warehouse_id,cl.logistics_name AS send_logistics_id,so.consign_time AS send_time  
				FROM ".$tmp_table." 				
				LEFT JOIN sales_refund_order sro ON sro.refund_order_id = tmp.refund_order_id
				LEFT JOIN sales_refund sr ON sr.refund_id = sro.refund_id
				LEFT JOIN stockout_order so ON sr.trade_no = so.src_order_no AND so.src_order_type = 1
			    LEFT JOIN goods_spec gs ON gs.spec_id = sro.spec_id
				LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
				LEFT JOIN goods_class gc ON gc.class_id = gg.class_id 
				LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
				LEFT JOIN cfg_flags cf ON cf.flag_id = sr.flag_id
				LEFT JOIN cfg_shop cs ON cs.shop_id = sr.shop_id
				LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr.reason_id AND cor.class_id=4
				LEFT JOIN hr_employee he ON he.employee_id= sr.salesman_id
				LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sr.warehouse_id
				LEFT JOIN cfg_warehouse cw1 ON cw1.warehouse_id = so.warehouse_id
				LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id 				
				LEFT JOIN goods_merchant_no gmn ON gmn.merchant_no = gs.spec_no
				WHERE 1 ".$where_goods_goods.$where_goods_spec." 
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
		$data = $this->getStatSellbackDetail(1,4001,$search);
		$refund_data = array();
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
			//统计某些字段的合计值
			$amount = array();
			$amount['spec_no'] = '合计';
			foreach($data['rows'] as $k=>$v){
				$v['type'] = isset($refund_type[$v['type']])? $refund_type[$v['type']]:$v['type'];
				$v['process_status'] = isset($refund_process_status[$v['process_status']])? $refund_process_status[$v['process_status']]:$v['process_status'];
				$refund_data[] = $v;

				$amount['refund_num'] += $v['refund_num'];
				$amount['stockin_num'] += $v['stockin_num'];
				$amount['cost_price'] += $v['cost_price'];
				$amount['price'] += $v['price'];
				$amount['discount'] += $v['discount'];
				$amount['stockin_amount'] += $v['stockin_amount'];
				$amount['total_amount'] += $v['total_amount'];
				$amount['guarante_refund_amount'] += $v['guarante_refund_amount'];
				$amount['direct_refund_amount'] += $v['direct_refund_amount'];
				$amount['refund_amount'] += $v['refund_amount'];
			}
			$refund_data[] = $amount;
			$excel_header = D('Setting/UserData')->getExcelField('Statistics/StatSellbackDetail','stat_sellback_detail');
			$title = '售后退货明细';
			$filename = '售后退货明细';
			foreach ($excel_header as $v) {
				$width_list[] = '20';
			}
			ExcelTool::Arr2Excel($refund_data,$title,$excel_header,$width_list,$filename,$creator);
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