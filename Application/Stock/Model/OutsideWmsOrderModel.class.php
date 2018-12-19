<?php
namespace Stock\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;
class OutsideWmsOrderModel extends model{
	protected $tableName = "outside_wms_order";
	protected $pk = "order_id";
	public function saveWmsOrder($data){
		try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$operator_id = get_operator_id();
			$order = $data['search'];
			$where = array('warehouse_id'=>$order['warehouse_id']);
			$warehouse_field = array('type as warehouse_type','province as receiver_province','city as receiver_city','district as receiver_district','CONCAT(province," ",city," ",district) as receiver_area');
			$warehouse_info = D('Setting/Warehouse')->field($warehouse_field)->where($where)->find();
			if((int)($warehouse_info['warehouse_type'] != 11)){
				SE('仓库类型不是委外仓库！');
			}
			$order = array_merge($order,$warehouse_info);
			
			$order['total_price'] = $order['amount'];
			unset($order['amount']);
			if(!$this->create($order)){
				$error = implode($this->getError());
				SE($error);
			}
			$order['receiver_mobile'] = $order['receiver_telno'];
			$order['order_no'] = array('exp',"FN_SYS_NO('outside_wms')");
			$order['status'] = 40;
			$order['operator_id'] = $operator_id;
			$order['check_operator_id'] = $operator_id;
			$order['created'] = array('exp','NOW()');
			$order['modified'] = array('exp','NOW()');
			$error_log = 'add_outside_wms_order';
			$res_order_id = $this->add($order);
			$log = ($order['order_type']=1)?"出库单":"入库单";
			//更新日志
			$log_data = array(
				'order_id' => $res_order_id,
				'operator_id' => $operator_id,
				'message' => "新建委外".$log,
				'operate_type' => $order['order_type'],
				'created' => array('exp','NOW()'),
			);
			$error_log = 'add_outside_wms_order_log';
			M('outside_wms_order_log')->add($log_data);
			if(empty($data['rows']['insert'])&&empty($data['rows']['delete'])&&empty($data['rows']['update'])){
				SE('货品信息不能为空');
			}
			$detail_result = $this->saveWmsOrderDetail($data['rows'],$res_order_id);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-saveWmsOrder-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-saveWmsOrder-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE($e->getMessage());	
		}catch(\Exception $e){
			\Think\Log::write($this->name.'-saveWmsOrder-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
					
		}
		return $result;
		
		
	}
	public function saveWmsOrderDetail($details,$order_id){
		try{
			$this->startTrans();
			$warehouse_id = $this->field('warehouse_id,order_type')->where(array('order_id' => $order_id))->select();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$detail_error_list = array();
			//$order_detail_model = D('Stock/OutsideWmsOrderDetail');
			$order_detail_model = D('OutsideWmsOrderDetail');
			$log_data = array();
			$operator_id = get_operator_id();
			foreach($details['insert'] as $key=>$detail){
				$model = clone $order_detail_model ;
				if(!$model->create($detail)){
					$detail_error_list[]=array('spec_no'=>$detail['spec_no'],'info'=>$model->getError());
				}
			}
			if(!empty($detail_error_list)){
				$result['status'] = 1;
				$result['data']=$detail_error_list;
				return $result;
			}
			$error_log = 'delete_OutsideWmsOrderDetail';
			foreach($details['delete'] as $key=>$detail){
				$order_detail_model->where(array('rec_id'=>$detail['id']))->delete();
				$log_data[] = array(
					'order_id' => $order_id,
					'operator_id' => $operator_id,
					'operate_type' => $warehouse_id[0]['order_type'],
					'message' => "删除单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			$error_log = 'update_OutsideWmsOrderDetail';
			foreach($details['update'] as $key=>$detail){
				if($warehouse_id[0]['order_type'] == 1 && $detail['stock_num'] < $detail['num']){
					SE('出库数量不能大于库存数量!');
				}
				$detail['price'] = $detail['cost_price'];
				$order_detail_model->where(array('rec_id'=>$detail['id']))->field(array('num','price','position_id'))->save($detail);
				$log_data[] = array(
					'order_id' => $order_id,
					'operator_id' => $operator_id,
					'operate_type' => $warehouse_id[0]['order_type'],
					'message' => "更新单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			$error_log = 'insert_OutsideWmsOrderDetail';
			foreach($details['insert'] as $key=>$detail){
				if(!isset($detail['remark']) || empty($detail['remark'])){
					$detail['remark'] = '';
				}
				if($warehouse_id[0]['order_type'] == 1 && $detail['stock_num'] < $detail['num']){
					SE('出库数量不能大于库存数量!');
				}
				$insert_data = array(
					'order_id' => $order_id,
					'spec_id' =>$detail['spec_id'],
					'spec_no' =>$detail['spec_no'],
					'num' =>empty($detail['num'])?0:$detail['num'],
					'num2'=>empty($detail['stock_num'])?0:$detail['stock_num'],
					'unit_id'=>empty($detail['base_unit_id'])?0:$detail['base_unit_id'],					
					'base_unit_id' =>empty($detail['base_unit_id'])?0:$detail['base_unit_id'],
					'price' =>empty($detail['cost_price'])?0:$detail['cost_price'],
					'position_id' =>empty($detail['position_id'])?0:$detail['position_id'],
					'remark' =>empty($detail['remark'])?'':$detail['remark'],
					'modified'=>array('exp','NOW()'),
					'created'=>array('exp','NOW()'),	
				);
				$order_detail_model->add($insert_data);
				$log_data[] = array(
					'order_id' => $order_id,
					'operator_id' => $operator_id,
					'operate_type' =>$warehouse_id[0]['order_type'],
					'message' => "添加单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			$error_log = 'insert_outside_wms_order_log';
			if(empty($log_data[0])){
				M('outside_wms_order_log')->add($log_data);
			}else{
				M('outside_wms_order_log')->addAll($log_data);
			}
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-saveWmsOrderDetail-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-saveWmsOrderDetail-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE($e->getMessage);
		}catch(\Exception $e){
			\Think\Log::write($this->name.'-saveWmsOrderDetail-'.$error_log.'-'.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
	}
	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc"){
		try{
			$where = '';
			$this->searchform($where,$search);
			$where=ltrim($where, ' and ');
			$limit = (intval($page)-1)*intval($row).','.intval($rows);
			$order = $sort.' '.$order;
			$fields = array('ow.order_id as id','ow.order_no','ow.outer_no','ow.order_type','ow.status','ow.wms_outer_no','ow.wms_status','ow.error_info','ow.operator_id','ow.warehouse_id','ow.warehouse_type','ow.transport_mode','ow.logistics_id','ow.logistics_no','ow.receiver_name','ow.receiver_area','ow.receiver_address','ow.receiver_telno','ow.receiver_zip','ow.modified','ow.created','ow.remark','cw.name','cl.logistics_name','hr.fullname');
			$data['rows'] = $this->alias('ow')->fetchSql(false)->field($fields)->join('left join cfg_warehouse cw on cw.warehouse_id = ow.warehouse_id')->join('left join cfg_logistics cl on cl.logistics_id = ow.logistics_id')->join('left join hr_employee hr on hr.employee_id = ow.operator_id')->where($where)->order($order)->limit($limit)->select();
			$data['total'] = count($data['rows']);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$data = array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			$data = array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function searchform(&$where,&$search){
		$warehouse_list = D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		foreach($search as $k=>$v){
			if(!isset($v)) continue;
			switch($k){
				case 'order_no':
					set_search_form_value($where,$k,$v,'ow',1,' and ');
					break;
				case 'wms_outer_no':
					set_search_form_value($where,$k,$v,'ow',1,' and ');
					break;
				case 'status':
					set_search_form_value($where,$k,$v,'ow',2,' and ');
					break;
				case 'warehouse_id':
					set_search_form_value($where,$k,$v,'ow',2,' and ');
					break;
				case 'order_type':
					set_search_form_value($where,$k,$v,'ow',2,' and ');
					break;	
				case 'operator_id':
					set_search_form_value($where,$k,$v,'ow',2,' and ');
					break;	
			}
		}
		
	}
	public function getOutsideWms($field,$where){
		try{
			$data = $this->field($field)->fetchSql(false)->where($where)->select();
		}catch(\Exception $e){
			\Think\Log::write('getOutsideWms--'.$e->getMessage());
			$data = array();
		}
		return $data;
	}
	public function updateWmsOrder($data,$id){
		try{
			$result = array('status'=>0);
			$operator_id = get_operator_id();
			$this->startTrans();
			$order = $data['search'];
			$where = array('order_id'=>$id);
			$order['total_price'] = $order['amount'];
			$order_info = $this->field('status,order_no,order_type')->where(array('order_id'=>$id))->find();
			if($order_info['status'] != 40){
				SE('不是待推送的订单!');
			}
			unset($order['amount']);
			if(!$this->create($order)){
				$error = implode($this->getError());
				SE($error);
			}
			$this->where($where)->save($order);
			$log = array(
				'order_id' => $id,
				'operator_id' => $operator_id,
				'message' => "更新订单".$order_info['order_no'],
				'operate_type' => $order_info['order_type'],
				'created' => array('exp','NOW()'),
			);
			M('outside_wms_order_log')->add($log);
			$detail_result = $this->saveWmsOrderDetail($data['rows'],$id);
			$this->commit();
			
		}catch(BusinessLogicException $e){
			$this->rollback();
			SE($e->getMessage());
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			SE(self::UNKNOWN_ERROR);
		}
		return $result;
	}
	public function revert($id,$type){
		try{
			$data = array('status'=>0);
			$operator_id = get_operator_id();
			$this->startTrans();
			$this->where(array('order_id'=>$id))->save(array('status'=>40,'check_time' => "0000-00-00 00:00:00",'wms_status'=>0,'error_info'=>""));
			$log = array(
				'order_id' => $id,
				'operator_id' => $operator_id,
				'message' => "驳回委外单",
				'operate_type' => 5,
				'created' => array('exp','NOW()'),
			);
			M('outside_wms_order_log')->add($log);
			$this->commit();
		}catch(BusinessLogicException $e){
			$this->rollback();
			$data = array('status'=>1,'info'=>$e->getMessage());
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}
		return $data;
	}
	public function cancel_wo($id){
		try{
			$this->where(array('order_id'=>$id))->save(array('status'=>10));
		}catch(BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
		}catch(\PDOException $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}catch(\Exception $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}
		 echo json_encode(array('updated'=>0));
	}
}