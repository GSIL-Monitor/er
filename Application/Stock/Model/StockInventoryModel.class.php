<?php
namespace Stock\Model;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
class StockInventoryModel extends Model
{
	protected $tableName = 'stock_pd';
	protected $pk = 'rec_id';
	
	public function addStockInven($search, $rows, &$stockinven_id)
	{
		try {
		
		$result['info'] = "";
		$result['status'] = '0';
		$operator_id = get_operator_id();
		
		$this->startTrans();
		if($rows['update_spec']!= '' || $rows['update_spec']!= null){
			$rows = $rows['update_spec'];
		}
			
			$warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>$search['warehouse_id'])));
			
			
			
			$sql_get_no = 'select FN_SYS_NO("stockpd") pd_no';			//FN_SYS_NO不懂
            $res_stockpd_no = $this->query($sql_get_no);
            $stockpd_no = $res_stockpd_no[0]['pd_no'];
			
			$add_data = array(
			    'warehouse_id'=>$search['warehouse_id'],
			    'pd_no'=>$stockpd_no,
				'mode'=>$search['pd_mode'],
				'type'=>0,
				'note_count'=>0,
				'creator_id'=>$operator_id,
			    'status'=>1,
			    'remark'=>$search['remark'],
			    'created'=>array('exp','NOW()'),
			   
			);
			$add_result = M('stock_pd')->add($add_data);
			$stockinven_id = $add_result;
			$stockpd_id_sql = "SElECT rec_id FROM stock_pd WHERE pd_no = \"{$stockpd_no}\"";
			$stockpd_id = $this->query($stockpd_id_sql);
			$stockpd_id = $stockpd_id[0]['rec_id'];

		
			//插入盘点单详情
			foreach ($rows as $key => $value) {
				$value = (array)$value;
				if(empty($value['position_id']) || !isset($value['position_id'])){
					$value['position_id'] = (-$search['warehouse_id']);
				}
				$data = array(
				  'pd_id'=>  $stockpd_id,
				  'spec_id'=>$value['spec_id'],	
				  'position_id'=>$value['position_id'],
				  'old_num'=>$value['stock_num'],
				  'input_num'=>$value['pd_num'],
				  'new_num'=>$value['pd_num'],
					'cost_price'=>$value['market_price'],		   
				    'remark'=>$value['remark'],
				    'created'=>array('exp','NOW()'),
					);
				
				$res_add = M('stock_pd_detail')->add($data);
							
			}
		$update_log=array(
			'operator_id'=>$operator_id,
			'order_type' =>4,
			'order_id'   =>$stockpd_id,
			'operate_type'=>'11',
			'message'   =>"生成盘点单"
		);
		D('StockInoutLog')->add($update_log);
		$this->commit();
		$result['info'] = $stockpd_no;
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
			$this->rollback();
		
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			SE($msg);
			$this->rollback();
			
		}
		
		return $result;
	}
	
	public function updataStockInven($search, $rows)
	{
		$operator_id = get_operator_id();
		$result['info'] = "";
		$result['status'] = '2';
		if ($search['stockinven_no'] != null) {
			//编辑盘点单
			try {
				$this->startTrans();

				$stockpd_info = $this->fetchSql(false)->alias('pd')->field("pd.rec_id, pd.status,cw.warehouse_id,cw.name")->join('left join cfg_warehouse cw on cw.warehouse_id = pd.warehouse_id')->where(array('pd.pd_no'=>$search['stockinven_no']))->select();
				$stockpd_id = $stockpd_info[0]['rec_id'];
				$warehouse_id = $stockpd_info[0]['warehouse_id'];
				$pd_status = $stockpd_info[0]['status'];
				$updata_data = array(
				    'operator_id'=>$operator_id,
				    'remark'=>$search['remark'],
					'modified'=>array('exp','NOW()')
				);
				$this->where(array('rec_id'=>$stockpd_id))->save($updata_data);
				
				//删除stock_pd_detail表
				foreach ($rows['del_spec'] as $key => $value) {
					$value = (array)$value;
					$this->execute("DELETE FROM stock_pd_detail WHERE pd_id = %d AND spec_id = %d",$stockpd_id,$value['spec_id']);				
					$log_data[] = array(
						'order_type' => 4,
						'order_id' =>$stockpd_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$pd_status}=1,13,11)"),
						'message'=>"删除单品-".$value['spec_no'],
					);
				}
				
				//更新stock_pd_detail表
				foreach ($rows['update_spec'] as $key => $value) {
					$value = (array)$value;
					$this->execute("UPDATE stock_pd_detail SET new_num = %d,remark = '%s' WHERE pd_id = %d AND spec_id = %d",$value['pd_num'],$value['remark'],$stockpd_id,$value['spec_id']);						
					$log_data[] = array(
						'order_type' => 4,
						'order_id' =>$stockpd_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$pd_status}=1,13,11)"),
						'message'=>"更新单品-".$value['spec_no'],
					);	
					}
				//插入stock_pd_detail表
				foreach ($rows['add_spec'] as $key => $value) {
					$value = (array)$value;
					if(empty($value['position_id']) || !isset($value['position_id'])){
						//\Think\Log::write($stockpd_id.'-'.$value['spec_id']);
						$sql =$this->query("SELECT position_id FROM stock_pd_detail WHERE pd_id = %d AND spec_id = %d",$stockpd_id,$value['spec_id']);
						$position_id = $sql[0]['position_id'];
						$value['position_id'] = $position_id;
						//\Think\Log::write($position_id);
					}
					if($value['position_id'] == ''){
						$value['position_id'] = -($warehouse_id);
					}
					$insert_data = array(
					    'pd_id'=>$stockpd_id  ,
						'spec_id'=>$value['spec_id'],			
						'position_id'=>$value['position_id'],
					    'old_num'=>$value['stock_num'],
						'input_num'=>$value['pd_num'],
					    'new_num'=>$value['pd_num'],		   
				        'remark'=>$value['remark'],
				        'modified'=>array('exp','NOW()'),				        
					);
					$res_add = M('stock_pd_detail')->add($insert_data);
					$log_data[] = array(
						'order_type' => 4,
						'order_id' =>$stockpd_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$pd_status}=1,13,11)"),
						'message'=>"添加单品-".$value['spec_no'],
					);					
				}
				
				$res_insert_log = D('Stock/StockInoutLog')->insertStockInoutLog($log_data);	
				$this->commit();
			} catch (\PDOException $e) {
				$msg = $e->getMessage();
				\Think\Log::write($msg);
				SE(self::PDO_ERROR);
				$this->rollback();
				
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				\Think\Log::write($msg);
				SE($msg);
				$this->rollback();
				
			}
			
			return $result;
		}
	}
	
	public function loadSelectedData($id)
	{
		$id = intval($id);
		try {
			$result = $this->where('rec_id = ' . $id)->field('status')->select();

		$status = $result[0]["status"];
		if($status != 1){
			SE('盘点单状态不正确');
		}
		$point_number = get_config_value('point_number',0);
		
			$stock_num = "CAST(sod.old_num AS DECIMAL(19,".$point_number.")) stock_num";
			$pd_num = "CAST(sod.new_num AS DECIMAL(19,".$point_number.")) pd_num";
			$pl_num = "CAST((sod.new_num - sod.old_num) AS DECIMAL(19,".$point_number.")) pl_num";
	
			$data = $this->query("SELECT distinct sod.spec_id AS id,cgu.name as unit_name,
                         sp.remark form_remark,sp.pd_no, gg.goods_name, gs.spec_name,sp.warehouse_id,cw.name,
                          gs.spec_code,gs.barcode,gs.spec_no,gg.brand_id,".$stock_num.",sod.input_num ,
						   ".$pd_num.",".$pl_num.",sp.mode pd_mode,sod.cost_price,sp.creator_id operator_id,
                         sod.remark,gg.goods_id,gs.spec_id,IF(ssp.position_id,ssp.position_id,-sp.warehouse_id) position_id,	      
			              gb.brand_name,
			              IF(ssp.position_id,cwp2.position_no,cwp.position_no) position_no
                          FROM stock_pd_detail AS sod
                          LEFT JOIN goods_spec AS gs ON gs.spec_id = sod.spec_id
						  LEFT JOIN stock_pd AS sp ON sp.rec_id = sod.pd_id
						  LEFT JOIN goods_goods AS gg on gs.goods_id = gg.goods_id                                                   
						  LEFT JOIN cfg_goods_unit AS cgu ON gg.unit = cgu.rec_id
                          LEFT JOIN stock_spec_position as ssp ON ssp.spec_id = gs.spec_id and ssp.warehouse_id = sp.warehouse_id
                          LEFT JOIN stock_spec as ss ON ss.spec_id = gs.spec_id and ss.warehouse_id = sp.warehouse_id
                          LEFT JOIN cfg_warehouse_position as cwp ON cwp.rec_id = -sp.warehouse_id
                          LEFT JOIN cfg_warehouse_position as cwp2 ON cwp2.rec_id = ssp.position_id
			              LEFT JOIN goods_brand as gb ON gb.brand_id = gg.brand_id
						  LEFT JOIN cfg_warehouse as cw on cw.warehouse_id = sp.warehouse_id
			              WHERE sod.pd_id = %d",$id);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			SE($msg);
		}

		return $data;
	}
	public function deleteStockInvenOrder($ids){
		try{
		$data['status'] = 0;
		$data['info'] = "取消成功";
		$operator_id = get_operator_id();
		$sql = "SELECT pd.status,pd.pd_no FROM stock_pd AS pd WHERE rec_id in(%s)";
		$result = $this->query($sql,$ids);
		for($i = 0; $i < count($result); $i++){
			if($result[$i]['status'] != 1){
				SE('盘点单状态不正确!');
				break;
			}
		}
		$this->startTrans();
		//$this->execute("UPDATE stock_pd SET status = 3 WHERE rec_id = %d",$id);
		$this->execute("UPDATE stock_pd SET status = 3 WHERE rec_id in (%s)",$ids);
		$ids = explode(',',$ids);
		for($i = 0; $i < count($ids); $i++){
			$update_log[]=array(
				'operator_id'=>$operator_id,
				'order_type' =>4,
				'order_id'   =>$ids[$i],
				'operate_type'=>'15',
				'message'   =>"取消盘点单"
			);
		}
		//D('StockInoutLog')->add($update_log);
		D('StockInoutLog')->addAll($update_log);
		$this->commit();
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	
}