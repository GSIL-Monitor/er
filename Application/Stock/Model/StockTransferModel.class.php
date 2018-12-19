<?php
namespace Stock\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\ExcelTool;
/**
 * 库存单品调拨模型类
 * @package Stock\Model
 */
class StockTransferModel extends Model{
    protected $tableName = 'stock_transfer';
    protected $pk        = 'rec_id';
    protected $patchValidate = true;
    protected $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
		/*self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
         self::MUST_VALIDATE 或者1 必须验证
         self::VALUE_VALIDATE或者2 值不为空的时候验证*/
        array('type',array(0,1),' 调拨类型不正确! ',1,'in',3),
        array('mode',array(0,1,2),' 调拨方案不正确 ',1,'in',3),
//		array('spec_no','require','商家编码不能为空!'),
//		array('warehouse_name','require','调拨仓库不能为空!'),
        array('from_warehouse_id','checkWarehouse',' 出库仓库不存在! ',0,'callback',3),
        array('to_warehouse_id','checkWarehouse',' 入库仓库不存在! ',0,'callback',3),
        array('from_warehouse_id,to_warehouse_id,mode','checkWarehouseIsSame',' 入库仓库和出库仓库不能相同! ',1,'callback'),
        array('telno','checkTelno',' 电话号码不合法! ',2,'callback',3),
        array('logistics_id','checkLogistics',' 物流方式不存在! ',0,'callback',3),
    );
    protected function checkWarehouse($id)
    {
        try{
            return D('Setting/Warehouse')->checkWarehouse($id);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }
    }
	protected function checkLogistics($id)
	{
		try{
			if(intval($id)==0){
				return true;
			}
			return D('Setting/Logistics')->checkLogistics($id);
		}catch (\Exception $e) {
			\Think\Log::write($e->getMessage());
			return false;
		}
	}
    protected function checkTelno($no)
    {
        return check_regex('mobile_tel',$no);
    }
    protected function checkWarehouseIsSame($warehouse_info)
    {
        try {
            if($warehouse_info['mode'] != 1)
            {
                return $warehouse_info['to_warehouse_id'] == $warehouse_info['from_warehouse_id']?false:true;
            }
            return true;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return false;
        }

    }
    public function updateInfo($data,$conditions)
    {
        try {
            $res_update = $this->where($conditions)->save($data);
            return $res_update;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }

    }

    public function insert($data,$update = false,$options = '')
    {
        try {
            if(empty($data[0]))
            {
                $res = $this->add($data,$options,$update);

            }else{
                $res = $this->addAll($data,$options,$update);

            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
    }
	public function updateTransferOrder($transfer_info)
	{
		try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$operator_id = get_operator_id();
			$order_id = $transfer_info['order_id'];
			$order_fields = array('logistics_id','status','transfer_no');
			$order_where = array('rec_id'=>$order_id);
			$order_info = $this->getStockTransOrder($order_fields,$order_where);
			if(empty($order_info)){
				\Think\Log::write('未查询到调拨单信息');
				SE(self::PDO_ERROR);
			}

			$order_info = $order_info[0];
			if($order_info['status']!=20 && $order_info['status']!=42){
				SE('调拨单状不是编辑中');
			}
			$update_order_info = $transfer_info['search'];
			if(!$this->create($update_order_info)){
				SE(implode($this->getError()));
			}

			$this->field(array('telno','address','logistics_id','contact','remark'))->where(array('rec_id'=>$order_id))->save($update_order_info);
			$log_data = array(
					'order_type' => 3,
					'order_id' =>$order_id,
					'operator_id'=>$operator_id,
					'operator_type'=>11,
					'message'=>'更新保存调拨单',
			);

			$res_insert_log = D('Stock/StockInoutLog')->insertStockInoutLog($log_data);
			$detail_result = $this->saveTransferDetail($transfer_info['rows'],$order_id);
			if($detail_result['status'] ==1){
				$this->rollback();
				return $detail_result;
			}
			$update_result = $this->updateTransferInfo($order_id);
			if($update_result['status'] ==1){
				$this->rollback();
				return  $update_result;
			}
			$this->commit();
		}catch (\PDOException $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info'] = self::PDO_ERROR;
			$this->rollback();
		}catch(BusinessLogicException $e){
			$result['status']=1;
			$result['info'] = $e->getMessage();
			$this->rollback();
		}catch(\Exception $e){
			$result['status']=1;
			$result['info'] = self::PDO_ERROR;
			\Think\Log::write($e->getMessage());
			$this->rollback();
		}
		return $result;
	}
    public function saveTransferOrder($transfer_info)
    {
        try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'success','data'=>array());
            $operator_id = get_operator_id();
			$order = $transfer_info['search'];
            if(!$this->create($order))
            {
                $error = implode($this->getError());
                SE($error);
            }
            $order['transfer_no'] = array('exp',"FN_SYS_NO('transfer')");
			$in_warehouse = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$order['to_warehouse_id']))->find();
			$out_warehouse = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$order['from_warehouse_id']))->find();
			if((int)$out_warehouse['type'] == 11){
				$order['status']=42;
			}elseif((int)$in_warehouse['type'] == 11){
				$order['status']=62;
			}else{
				$order['status']=20;	
			}
            $order['creator_id']=$operator_id;
			$order['created'] = array('exp','NOW()');
			$order['modified'] = array('exp','NOW()');
            $res_order_id = $this->insert($order);
			$result['order_id'] = $res_order_id;
            //更新日志

            $log_data = array(
                'order_type' => 3,
                'order_id' =>$res_order_id,
                'operator_id'=>$operator_id,
                'operator_type'=>array('exp',"IF({$order['status']}=30,13,11)"),
                'message'=>array('exp',"IF({$order['status']}=30,'新建并递交调拨单','新建调拨单')"),
            );

            $res_insert_log = D('Stock/StockInoutLog')->insertStockInoutLog($log_data);
			if(empty($transfer_info['rows']['insert'])&&empty($transfer_info['rows']['delete'])&&empty($transfer_info['rows']['update'])){
				SE('货品信息不能为空');
			}
			$detail_result = $this->saveTransferDetail($transfer_info['rows'],$res_order_id);
			if($detail_result['status'] ==1){
				$this->rollback();
				return $detail_result;
			}
			$update_result = $this->updateTransferInfo($res_order_id);
			if($update_result['status'] ==1){
				$this->rollback();
				return  $update_result;
			}
			$this->commit();
		}catch (\PDOException $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info'] = self::PDO_ERROR;
			$this->rollback();
		}catch(BusinessLogicException $e){
			$result['status']=1;
			$result['info'] = $e->getMessage();
			$this->rollback();
		}catch(\Exception $e){
			$result['status']=1;
			$result['info'] = self::PDO_ERROR;
			\Think\Log::write($e->getMessage());
			$this->rollback();
		}
		return $result;
    }
	public function saveTransferDetail($details,$order_id){
		try{
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$detail_error_list = array();
			$order_detail_model = D('Stock/StockTransferDetail');
			$order_info = $this->getStockTransOrder(array('mode','status','to_warehouse_id'),array('rec_id'=>$order_id));
			$order_info = $order_info[0];
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
			foreach($details['delete'] as $key=>$detail){
				$detail_error_info =array('spec_no'=>$detail['spec_no']);
				$order_detail_model->where(array('rec_id'=>$detail['rec_id'],'transfer_id'=>$order_id))->delete();
				$log_data[] = array(
						'order_type' => 3,
						'order_id' =>$order_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$order_info['status']}=30,13,11)"),
						'message'=>"删除单品-".$detail['spec_no'],
				);
			}
			foreach($details['update'] as $key=>$detail){
				$detail_error_info =array('spec_no'=>$detail['spec_no']);
                if($order_info['mode'] !=1)
                {
                    $is_different = D('Stock/StockSpec')->checkIsAllocatedPosition($order_info['to_warehouse_id'],$detail['spec_id'],$detail['to_position']);
                    if($is_different){
                        SE('该货品在调入仓库已分配货位,货位与调入仓库货位不同');
                    }
                }

				$order_detail_model->where(array('rec_id'=>$detail['rec_id']))->field(array('stock_num','num','remark','to_position'))->save($detail);
                $log_data[] = array(
						'order_type' => 3,
						'order_id' =>$order_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$order_info['status']}=30,13,11)"),
						'message'=>"更新单品-".$detail['spec_no'],
				);
			}
			foreach($details['insert'] as $key=>$detail){
				$detail_error_info =array('spec_no'=>$detail['spec_no']);
                if($order_info['mode'] !=1)
                {
                    $is_different = D('Stock/StockSpec')->checkIsAllocatedPosition($order_info['to_warehouse_id'],$detail['spec_id'],$detail['to_position']);
                    if($is_different){
                        SE('该货品在调入仓库已分配货位,货位与调入仓库货位不同');
                    }
                }
                $item = array(
						'transfer_id'   => $order_id,
						'spec_id'       => $detail['spec_id'],
						'stock_num'     => $detail['stock_num'],
						'num'           => $detail['num'],
						'in_num'        => 0,
						'out_num'       => 0,
						'from_position' => $detail['from_position'],
						'to_position' => $detail['to_position'],
						'remark'        => $detail['remark'],
						'created'		=> array('exp','NOW()'),
						'modified'		=> array('exp','NOW()'),
				);
				$order_detail_model->add($item);
				$log_data[] = array(
						'order_type' => 3,
						'order_id' =>$order_id,
						'operator_id'=>$operator_id,
						'operator_type'=>array('exp',"IF({$order_info['status']}=30,13,11)"),
						'message'=>"添加单品-".$detail['spec_no'],
				);
			}
			$res_insert_log = D('Stock/StockInoutLog')->insertStockInoutLog($log_data);
			$detail_error_info = array();
		}catch(\PDOException $e){
			$result['status'] = 1;
			\Think\Log::write($e->getMessage());
			$detail_error_info['info'] = self::PDO_ERROR;
			array_push($result['data'],$detail_error_info);
		}catch(BusinessLogicException $e){
			$result['status'] = 1;
			$detail_error_info['info'] = $e->getMessage();
			array_push($result['data'],$detail_error_info);

		}catch(\Exception $e){
			$result['status'] = 1;
			\Think\Log::write($e->getMessage());
			$detail_error_info['info'] = self::PDO_ERROR;
			array_push($result['data'],$detail_error_info);
		}
		return $result;
	}
	private function updateTransferInfo($order_id){
		try{
			$result = array('status'=>0,'info'=>'success');
			//统计调拨信息
			$query_fields = array(
					'IFNULL(COUNT(spec_id),0) as goods_type_count',
					'IFNULL(SUM(num),0) as goods_count',
					'IFNULL(SUM(out_num),0) as goods_in_count',
					'IFNULL(SUM(in_num),0) as goods_out_count'
			);
			$query_conditons = array('transfer_id'=>$order_id);
			$res_query_detail = D('Stock/StockTransferDetail')->getDetailInfo($query_fields,$query_conditons);
			$res_update_order = $this->updateInfo($res_query_detail[0],array('rec_id'=>$order_id));
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=self::PDO_ERROR;
		}catch(BusinessLogicException $e){
			$result['status']=1;
			$result['info']=$e->getMessage();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status'] = 1;
			$result['info']   = self::PDO_ERROR;
		}
		return $result;
	}
	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc")
	{
		D('Setting/EmployeeRights')->setSearchRights($search,'from_warehouse_id',2);
		D('Setting/EmployeeRights')->setSearchRights($search,'to_warehouse_id',2);
		$rows=intval($rows);
		$page=intval($page);
		$where_arr = $this->searchForm($search);
		$where_stock_transfer_detail=$where_arr['where_stock_transfer_detail'];
		$where_stock_transfer=$where_arr['where_stock_transfer'];
		$where_stock_transfer=ltrim($where_stock_transfer, ' AND ');
		$where_stock_transfer_detail=ltrim($where_stock_transfer_detail, ' AND ');
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$order = $sort." ".$order;//排序
		$order=addslashes($order);
		try{
			if(!empty($where_stock_transfer_detail)){
				if(!empty($where_stock_transfer)){
					$where_stock_transfer_detail = $where_stock_transfer_detail.' and '.$where_stock_transfer;
				}
				$total=$this->distinct(true)->alias('st')->field('st.rec_id as id')->join('LEFT JOIN stock_transfer_detail std ON std.transfer_id=st.rec_id LEFT JOIN goods_spec gs ON gs.spec_id = std.spec_id  LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ')->where($where_stock_transfer_detail);
			}else{
				$total=$this->distinct(true)->alias('st')->field('st.rec_id as id')->where($where_stock_transfer);
			}
			$m=clone $total;
			$point_number=get_config_value('point_number',0);			
			$goods_in_count = 'CAST(st.goods_in_count AS DECIMAL(19,'.$point_number.'))';
			$goods_out_count = 'CAST(st.goods_out_count AS DECIMAL(19,'.$point_number.'))';
			$goods_count = 'CAST(st.goods_count AS DECIMAL(19,'.$point_number.'))';
			$note_count = 'CAST(st.note_count AS DECIMAL(19,'.$point_number.'))';
			$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num =$this->query( $m->fetchsql(true)->count());
			$list = $this->fetchSql(false)->distinct(true)->alias('st')->field(' st.rec_id AS id,cw1.warehouse_id,st.outer_no,st.error_info as wms_result,cw.warehouse_id,cw1.name as from_warehouse_id,cw.name as to_warehouse_id, he.fullname creator_id, cl.logistics_name logistics_id, st.rec_id, st.transfer_no,st.type,st.mode,st.status,st.contact, st.telno, st.remark, st.modified, st.created,'.$note_count.' note_count,'.$goods_count.' goods_count,st.goods_type_count,'.$goods_in_count.' goods_in_count,'.$goods_out_count.' goods_out_count')->join('INNER JOIN ('.$total_sql.') as sp ON sp.id = st.rec_id')->join('LEFT JOIN stock_transfer_detail std ON std.transfer_id=st.rec_id LEFT JOIN goods_spec gs ON gs.spec_id = std.spec_id  LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN cfg_logistics cl ON cl.logistics_id = st.logistics_id  LEFT JOIN hr_employee he ON he.employee_id = st.creator_id  LEFT JOIN cfg_warehouse cw1 on cw1.warehouse_id=st.from_warehouse_id LEFT JOIN cfg_warehouse cw on cw.warehouse_id = st.to_warehouse_id')->order($order)->select();
			$data=array('total'=>$num[0]['tp_count'],'rows'=>$list);
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$data=array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
			 SE(self::PDO_ERROR);
        }
			return $data;
	}
	public function searchForm($search){
		$where_stock_transfer_detail='';
		$where_stock_transfer='';
		foreach($search as $k=>$v){
			if($v==='') continue;
			switch($k){
				case 'from_warehouse_id'://原仓库
					set_search_form_value($where_stock_transfer, $k, $v, 'st', 2,' AND ');
					break;
				case 'mode'://原仓库
					set_search_form_value($where_stock_transfer, $k, $v, 'st', 2,' AND ');
					break;
				case 'to_warehouse_id'://目标仓库
					set_search_form_value($where_stock_transfer, $k, $v, 'st', 2,' AND ');
					break;
				case 'creator_id' ://经办人
					set_search_form_value($where_stock_transfer, $k,  $v, 'st', 2,' AND ');
					break;
				case 'transfer_no'://调拨单号
					set_search_form_value($where_stock_transfer, $k, $v, 'st',1,' AND ');
					break;
				case 'spec_no'://商家编码
					set_search_form_value($where_stock_transfer_detail, $k, $v, 'gs',1,' AND ');
					break;
				case 'goods_no'://货品编码
					set_search_form_value($where_stock_transfer_detail, $k, $v, 'gg',1,' AND ');
					break;
				case 'goods_name'://货品名称
					set_search_form_value($where_stock_transfer_detail, $k, $v, 'gg',1,' AND ');
					break;
				case 'short_name'://货品简称
					set_search_form_value($where_stock_transfer_detail, $k, $v, 'gg',1,' AND ');
					break;
				case 'brand_id'://品牌
					set_search_form_value($where_stock_transfer_detail, $k, $v, 'gg',2,' AND ');
					break;
				case 'contact'://联系人
					set_search_form_value($where_stock_transfer, $k, $v, 'st',1,' AND ');
					break;
				case 'telno'://联系电话
					set_search_form_value($where_stock_transfer, $k, $v, 'st',1,' AND ');
					break;
				case 'status'://调拨单状态
					set_search_form_value($where_stock_transfer, $k, $v, 'st', 2,' AND ');
					break;
			}
		}
		return array(
			'where_stock_transfer'=>$where_stock_transfer,
			'where_stock_transfer_detail'=>$where_stock_transfer_detail,
		);
	}
	public function cancelTransOrder($ids){
		  try{
				$data['status']=0;
				$data['info']="取消成功";
				$operator_id=get_operator_id();
				$field=array('status','type');
				$result=$this->getStockTransOrder($field,array('rec_id'=>array('in',$ids)));
				for($i = 0; $i < count($result); $i++){
					if($result[$i]['status'] != 20){
						$data['status']=1;
						$data['info']="调拨单类型不正确";
						return $data;
					   break;
					}
				}
				$this->startTrans();
				$update_status=array('status'=>10);
				$update_stockTrans=$this->where("rec_id in(".$ids.")")->save($update_status);
				  $ids = explode(',',$ids);
				  for($i = 0; $i < count($ids); $i++){
					  $update_log[]=array(
						  'operator_id'=>$operator_id,
						  'order_type' =>3,
						  'order_id'   =>$ids[$i],
						  'operate_type'=>'51',
						  'message'   =>"取消调拨单"
					  );
				  }
				D('StockInoutLog')->addAll($update_log);
				$this->commit();
				return $data;
			}catch(\PDOException $e){
				$msg=$e->getMessage();
				\Think\Log::write($msg);
				$this->rollback();
			}catch(\Exception $e){
				$msg = $e->getMessage();
				\Think\Log::write($msg);
			}
		}
	public function getStockTransOrder($field,$conditions=array()){
		try{
			$res=$this->field($field)->where($conditions)->select();
			return $res;
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
		}catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
        }
		return false;
	}

	public function submitStockTransOrder($id,$is_force = false,$is_return=false){
		try{
			$result['status']=0;
			$operator_id=get_operator_id();
			$this->startTrans();
			$log_str = '查询调拨单信息';
			$transfer_order_info =$this->field(array('from_warehouse_id','to_warehouse_id','rec_id','status','mode','transfer_no'))->where(array('rec_id'=>$id))->select();
			$status=$transfer_order_info[0]['status'];
			$transfer_order_info = $transfer_order_info[0];
			if($status!=20){
				if($is_return){
					$this->rollback();
					return array('status'=>1,'info'=>'调拨单类型不正确！');
				}else{
					SE("调拨单类型不正确！");
				}
			}
			//判断库存是否改变
            $is_chang_num = $this->query('select sum(IF(ss.stock_num<>std.num and ss.stock_num>=0,1,0)) as diff_count from stock_transfer_detail std left join stock_spec ss on ss.spec_id = std.spec_id and ss.warehouse_id='.$transfer_order_info['from_warehouse_id'].' and std.transfer_id = '.intval($id));
            $is_neg_num = $this->query('select sum(IF(ss.stock_num<0,1,0)) as neg_count from stock_transfer_detail std left join stock_spec ss on ss.spec_id = std.spec_id and ss.warehouse_id='.$transfer_order_info['from_warehouse_id'].' and std.transfer_id = '.intval($id));
            $update_log =array();
            if(!$is_neg_num[0]['neg_count']){
                if($is_chang_num[0]['diff_count'] && !$is_force && $transfer_order_info['mode'] == 1){
                    $this->rollback();
                    $result = array('status'=>1,'data'=>false,'info'=>'货位调拨中调出仓库数量已改变,是否<a href="javascript:void(0)"onclick = "stockTransManagement.submitStockTransOrder(1,'.$id.')">强制</a>把现在货位上的所有货品调拨到目标货位');
                    return $result;
                }

                if($is_force && $transfer_order_info['mode'] == 1)
                {
                    $this->execute("UPDATE stock_transfer_detail std,stock_spec ss SET std.num = ss.stock_num WHERE std.transfer_id =".intval($id)." AND ss.spec_id = std.spec_id AND std.num <> ss.stock_num  AND ss.stock_num >=0 AND ss.warehouse_id=".$transfer_order_info['from_warehouse_id']);
                    $update_log = $this->query("SELECT {$operator_id} as operator_id,3 AS order_type ,{$transfer_order_info['rec_id']} as order_id ,11 AS operate_type, CONCAT(gs.spec_no,'强制货位调拨,数量从:',std.num,' 变为:',ss.stock_num) as message FROM stock_transfer_detail std LEFT JOIN goods_spec gs ON gs.spec_id = std.spec_id LEFT JOIN stock_spec ss ON  ss.spec_id = std.spec_id AND ss.warehouse_id=".$transfer_order_info['from_warehouse_id']." WHERE std.num <> ss.stock_num  AND ss.stock_num >=0 AND std.transfer_id =".intval($id));
                }
            }
			$log_str = '更新调拨单状态';
			$update_status=array('status'=>90);
			$update_stockTrans=$this->where(array("rec_id"=>$id))->save($update_status);
			//----更新调拨日志
			$log_str = '更新调拨日志';
			$update_log[]=array(
			    'operator_id'	=> $operator_id,
				'order_type' 	=> 3,
				'order_id'   	=> $transfer_order_info['rec_id'],
				'operate_type'	=> '40',
				'message'   	=> "完成调拨单"
			 );
			D('StockInoutLog')->addALL($update_log);
			//-------插入出库单
			$log_str = '插入出库单';
			$res_stockout_data=array(
				'stockout_no'	=>array('exp',"FN_SYS_NO('stockout')"),
				'status'		=>48,
				'warehouse_id'	=>$transfer_order_info['from_warehouse_id'],
				'src_order_type'=>2,
				'src_order_id'	=>$transfer_order_info['rec_id'],
				'src_order_no'	=>$transfer_order_info['transfer_no'],
				'operator_id'	=>$operator_id,
				'created'		=>array('exp','NOW()')
			  );
			$res_stockout_order_id = D('Stock/StockOutOrder')->insertStockoutOrderForUpdate($res_stockout_data);
			//------更新出库单日志
			$log_str = '更新出库单日志';
			$stock_inout_log_data = array(
				"order_type" 	=> 2,
                "operat_type" 	=> 21,
                "order_id" 		=> $res_stockout_order_id,
                "message" 		=> "新建快速调拨出库单",
                "created" 		=> array('exp','NOW()'),
                "operator_id" 	=> $operator_id,
               );
            $res_stock_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($stock_inout_log_data);
			//-----插入出库单详情
			$log_str = '插入出库单详情';

			$stockout_detail_sql="SELECT {$res_stockout_order_id} as stockout_id,2 as src_order_type,std.from_position as position_id,std.rec_id as src_order_detail_id,std.num,ss.cost_price as price,ss.cost_price,ss.cost_price*std.num as total_amount,gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_id,gs.spec_no,gs.spec_code"
								  ." FROM stock_transfer_detail std"
								  ." LEFT JOIN goods_spec gs ON std.spec_id=gs.spec_id"
						          ." LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id"
								  ." LEFT JOIN stock_spec ss ON ss.spec_id=gs.spec_id "
								  ." WHERE ss.warehouse_id=".$transfer_order_info['from_warehouse_id'] ." AND " ."std.transfer_id=%d";
			$res_stockout_detail=$this->query($stockout_detail_sql,$id);
            foreach($res_stockout_detail as $stockin_detail){
                    $is_allocated_before = D('Stock/StockSpec')->checkIsAllocatedPosition($transfer_order_info['from_warehouse_id'],$stockin_detail['spec_id'],$stockin_detail['position_id']);
                    if($is_allocated_before){
						if($is_return){
							$this->rollback();
							return array('status'=>1,'info'=>$stockin_detail['spec_no'].'-调出货位已改变,重新添加该货品后再提交');
						}else{
							SE($stockin_detail['spec_no'].'-调出货位已改变,重新添加该货品后再提交');
						}
                    }
            }
            $res_stockout_order_detail = D('Stock/StockoutOrderDetail')->insertForUpdateStockoutOrderDetail($res_stockout_detail);

			//-----统计出库单详情中的货品信息
			$log_str ='统计出库单详情中的货品信息';
			$statistics_outdetail_field = array(
				"SUM(num) goods_count",
				"COUNT(DISTINCT spec_id) goods_type_count",
				"SUM(total_amount) goods_total_amount",
			);
			$statistics_outdetail_where = array(
				'stockout_id'=>$res_stockout_order_id
			);
			$res_statistics_outdetail = D('Stock/StockoutOrderDetail')->getStockoutOrderDetails($statistics_outdetail_field,$statistics_outdetail_where);
			$update_stockout_where = array('stockout_id'=>$res_stockout_order_id);
			$update_stockout = D('Stock/StockOutOrder')->updateStockoutOrder($res_statistics_outdetail[0],$update_stockout_where);
			//---------审核提交出库单信息
			$log_str = '审核提交出库单信息';
			$check_stockout_result = D('Stock/StockOutOrder')->submitStockOutOrder($res_stockout_order_id,'transfer');
			if($check_stockout_result['status']==1){
				if($is_return){
					$this->rollback();
					return array('status'=>1,'info'=>$check_stockout_result['info']);
				}else{
					SE($check_stockout_result['info']);
				}
			}

			//-----------------------------入库单处理逻辑----------------------------------------

			//-----生成入库单
			$log_str = '生成入库单';
			$res_query_stockin_no = $this->query("select FN_SYS_NO('stockin') as stockin_no");
			$res_stockin_data=array(
				'stockin_no'		=>array('exp',"FN_SYS_NO('stockin')"),
				'warehouse_id'		=>$transfer_order_info['to_warehouse_id'],
				'src_order_type'	=>2,
				'src_order_id'		=>$transfer_order_info['rec_id'],
				'src_order_no'		=>$transfer_order_info['transfer_no'],
				'operator_id'		=>$operator_id,
				'status'			=>20,
				'created'			=>array('exp','NOW()')
			);
			$res_stockin_order_id = D('Stock/StockInOrder')->insertStockinOrderfForUpdate($res_stockin_data);
			//--------更新入库单日志
			$log_str = '更新入库单日志';
			$stock_inout_log_data = array(
                "order_type" 		=> 1,
                "operat_type" 		=> 11,
                "order_id" 			=> $res_stockin_order_id,
                "message" 			=> "新建快速调拨入库单",
                "created" 			=> array('exp','NOW()'),
                "operator_id" 		=> $operator_id,
			);
            $res_stock_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($stock_inout_log_data);
			//------插入入库单详情
			$log_str = '插入入库单详情';

			$res_stockin_detail_data=D('Stock/StockoutOrderDetail')->alias('sod')->field(array("{$res_stockin_order_id} as stockin_id","std.to_position as position_id","2 as src_order_type",'std.rec_id as src_order_detail_id','std.spec_id','gs.spec_no','SUM(sod.num)*ss.cost_price as total_cost','ss.cost_price as tax_price','SUM(sod.num)*ss.cost_price as tax_amount','SUM(sod.num) as num','gs.unit as base_unit_id','ss.cost_price','ss.cost_price as src_price'))
									->join("LEFT JOIN stock_transfer_detail std ON std.rec_id=sod.src_order_detail_id
											LEFT JOIN goods_spec gs ON std.spec_id=gs.spec_id
											LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
											LEFT JOIN stock_spec ss ON ss.spec_id=gs.spec_id ")
									->where("sod.stockout_id=".$res_stockout_order_id." AND "."ss.warehouse_id=".$transfer_order_info['from_warehouse_id'])
									->group('std.rec_id')->select();
            //判断入库之前货位是否已分配到其他
            if($transfer_order_info['mode'] != 1)
            {
                foreach($res_stockin_detail_data as $stockin_detail){
                    $is_allocated_before = D('Stock/StockSpec')->checkIsAllocatedPosition($transfer_order_info['to_warehouse_id'],$stockin_detail['spec_id'],$stockin_detail['position_id']);
                    if($is_allocated_before){
						if($is_return){
							$this->rollback();
							return array('status'=>1,'info'=>$stockin_detail['spec_no'].'-已被分配了其他货位,重新编辑后再提交');
						}else{
							SE($stockin_detail['spec_no'].'-已被分配了其他货位,重新编辑后再提交');
						}
                    }
                }
            }

			$res_stockin_order_detail = D('Stock/StockinOrderDetail')->insertStockinOrderDetailfForUpdate($res_stockin_detail_data);
			//--------统计入库单详情中的货品信息
			$log_str = '统计入库单详情中的货品信息';
			$statistics_indetail_fields = array('SUM(num) goods_count','COUNT(DISTINCT spec_id) goods_type_count','SUM(src_price*num) goods_amount');
			$statistics_indetail_where = array('stockin_id'=>$res_stockin_order_id);
			$res_statistics_indetail = D('Stock/StockinOrderDetail')->getStockinOrderDetailList($statistics_indetail_fields,$statistics_indetail_where);
			$update_stockin_where = array('stockin_id'=>$res_stockin_order_id);
			$res_update_stockin = D('StockInOrder')->updateStockinOrder($res_statistics_indetail[0],$update_stockin_where);
			//---------审核提交入库单
			$log_str = '审核提交入库单';
			$order_detial = array(
					'id'				=>$res_stockin_order_id,
					'src_order_type'	=>2,
					'src_order_no'		=>$transfer_order_info['transfer_no'],
					'warehouse_id'		=>$transfer_order_info['to_warehouse_id']
			);
			$check_stockin_result = D('Stock/StockIn')->submitStockInOrder($order_detial,'transfer');
			if($check_stockin_result['status']==1){
				if($is_return){
					$this->rollback();
					return array('status'=>1,'info'=>$check_stockin_result['info']);
				}else{
					SE($check_stockin_result['info']);
				}
			}
			$res_goods_out_count=D('Stock/StockOutOrder')->field('goods_count')->where(array('stockout_id'=>$res_stockout_order_id))->select();
			$res_goods_in_count=D('Stock/StockInOrder')->field('goods_count')->where(array('stockin_id'=>$res_stockin_order_id))->select();
			$update_goods_count=array('goods_in_count'=>$res_goods_in_count[0]['goods_count'],'goods_out_count'=>$res_goods_out_count[0]['goods_count']);
			$update_stockTrans_goods_count_=$this->where(array("rec_id"=>$id))->save($update_goods_count);
			$result['goods_in_count']=$res_goods_in_count[0]['goods_count'];
			$result['goods_out_count']=$res_goods_out_count[0]['goods_count'];
			$this->commit();
			return $result;
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($log_str.$msg);
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			$this->rollback();
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($log_str.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
	}
	public function importPositionTrans($data,&$result){
		try{
			$operator_id = get_operator_id();
			\Think\Log::write('导入数据提示:'.print_r($data,true),\Think\Log::DEBUG);
			/*$check_fields_ar = array('spec_no','warehouse_name');
			foreach ($data as $import_index => $import_rows){
				if (!$this->create($import_rows)) {
					// 如果创建失败 表示验证没有通过 输出错误提示信息
					$data[$import_index]['status'] = 1;
					$data[$import_index]['result'] = '失败';
					foreach ($check_fields_ar as $check_field){
						$temp_error_ar = $this->getError();
						if(isset($temp_error_ar["{$check_field}"])){
							$data[$import_index]['message'] = $temp_error_ar["{$check_field}"];
							break;
						}
					}
				}
			}*/
			\Think\Log::write('导入数据验证:'.print_r($data,true),\Think\Log::DEBUG);
			$this->startTrans();
			//查入临时表信息包括验证完的信息
			foreach($data as $item){
				$this->execute("insert into tmp_import_detail(`spec_no`,`to_position_no`,`warehouse_name`,`status`,`result`,`message`,`line`) values('{$item['spec_no']}','{$item['to_position_no']}','{$item['warehouse_name']}',".(int)$item['status'].",'".$item['result']."','{$item['message']}',".(int)$item['line'].")");
			}
			//校验是否存在相应的仓库、商家编码和货位
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			$query_warehouse_id_list_sql = "SELECT * FROM tmp_import_detail " ;
			$res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
			\Think\Log::write('导入临时表的数据:'.print_r($res_query_warehouse_id_list,true),\Think\Log::DEBUG);
			//仓库是否存在
			$query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name"
				."  SET tid.warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'仓库不存在','') "
				."  WHERE tid.status=0 ;";
			$this->execute($query_warehouse_isset_sql);
			//仓库是否是有权限的仓库
			$rights_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.warehouse_id"
				." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'仓库不存在','') "
				." where tid.status = 0;";
			$this->execute($rights_warehouse_sql);
			//商家编码是否存在
			$query_goods_spec_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
				." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.spec_id IS NULL,1,0),tid.result= IF(gs.spec_id IS NULL,'失败',''),tid.message=IF(gs.spec_id IS NULL,'商家编码不存在','')"
				." WHERE tid.status=0;";
			$this->execute($query_goods_spec_isset_sql);
			//仓库中是否存在该商品
			$res_query_stock_sql = "UPDATE tmp_import_detail tid left join stock_spec ss on ss.spec_id=tid.spec_id and ss.warehouse_id = tid.warehouse_id"
				." set tid.stock_num = if(ss.rec_id is null,0,ss.stock_num),tid.status = if(ss.rec_id is null,1,0),tid.result = if(ss.rec_id is null,'失败',''), tid.message=if(ss.rec_id is null,'在该仓库内该商品不存在','')"
				." where tid.status = 0;";
			$this->execute($res_query_stock_sql);
			//更新默认货位
			$query_position_no_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec_position ssp ON ssp.spec_id = tid.spec_id and ssp.warehouse_id = tid.warehouse_id LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = -tid.warehouse_id"
				." SET tid.from_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id ),tid.to_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id )"
				." WHERE tid.status= 0;";
			$this->execute($query_position_no_sql);
			//仓库中是否存在货位
			/*$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse_position cwp ON cwp.position_no = tid.to_position_no and cwp.warehouse_id = tid.warehouse_id LEFT JOIN stock_spec_position ssp ON ssp.position_id = cwp.rec_id  and ssp.warehouse_id = cwp.warehouse_id"
				." SET tid.to_position_id=ssp.position_id ,tid.status = if(ssp.position_id is null,1,0),tid.result = if(ssp.position_id is null,'失败',''),tid.message=if(ssp.position_id is null,'该仓库内不存在该货位','')"
				." WHERE tid.status=0 and tid.to_position_no<>'';";*/
			$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse_position cwp ON cwp.position_no = tid.to_position_no and cwp.warehouse_id = tid.warehouse_id"
				." SET tid.to_position_id=cwp.rec_id ,tid.status = if(cwp.rec_id is null,1,0),tid.result = if(cwp.rec_id is null,'失败',''),tid.message=if(cwp.rec_id is null,'该仓库内不存在该货位','')"
				." WHERE tid.status=0;";
			$this->execute($query_position_no_isset_sql);
			//按导入仓库分组
			$query_warehouse_id_list_sql = "SELECT warehouse_id FROM tmp_import_detail WHERE STATUS=0 GROUP BY warehouse_id" ;
			$res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
			if(empty($res_query_warehouse_id_list)){
				$deal_import_data_result = $this->query("select line as id,message,status,result,spec_no from tmp_import_detail where status =1");
				\Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);
				if(!empty($deal_import_data_result)){
					$result['status'] = 2;
					$result['data'] = $deal_import_data_result;
				}
				return;
			}
			for($i=0; $i<count($res_query_warehouse_id_list); ++$i){
				$sql_get_no = 'select FN_SYS_NO("transfer") transfer_no';
				$res_stocktrans_no = $this->query($sql_get_no);
				$stocktrans_no = $res_stocktrans_no[0]['transfer_no'];
				$trans_data = $this->query("select * from tmp_import_detail tid where tid.status=0 and tid.warehouse_id=".$res_query_warehouse_id_list[$i]['warehouse_id']);
				$id = $res_query_warehouse_id_list[$i]['warehouse_id'];
				$fields = array('contact','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `telno`','CONCAT(province,city,district,address) as `address`');
				$where = array('warehouse_id'=>$id);
				$warehouse_info = D('Setting/Warehouse')->getWarehouseList($fields,$where);
				$stock_num = 0;
				$goods_type_count = 0;
				$add_data = array(
					'from_warehouse_id' => $trans_data[0]['warehouse_id'],
					'to_warehouse_id' => $trans_data[0]['warehouse_id'],
					'transfer_no' => $stocktrans_no,
					'mode' => 1,
					'type' => 1,
					'contact' => $warehouse_info[0]['contact'],
					'telno' => $warehouse_info[0]['telno'],
					'address' => $warehouse_info[0]['address'],
					'logistics_id' => 0,
					'creator_id' => $operator_id,
					'status' => 20,
					'remark' => '',
					'created' => array('exp', 'NOW()'),
					'modified' => array('exp', 'NOW()'),
				);
				/*if (empty($item['position_id']) || !isset($item['position_id'])) {
					$item['position_id'] = (-$item['warehouse_id']);
				}*/
				$add_result = $this->insert($add_data);
				$log_data = array(
					'order_type' => 3,
					'order_id' =>$add_result,
					'operator_id'=>$operator_id,
					'operator_type'=>array('exp',"IF({$add_data['status']}=30,13,11)"),
					'message'=>'导入货位调拨单',
				);

				D('Stock/StockInoutLog')->insertStockInoutLog($log_data);
				$stocktrans_id_sql = "SElECT rec_id FROM stock_transfer WHERE transfer_no = \"{$stocktrans_no}\"";
				$stocktrans_id = $this->query($stocktrans_id_sql);
				$stocktrans_id = $stocktrans_id[0]['rec_id'];
				$add_data_detail = array();
				$arr_spec_id=array();
				foreach($trans_data as $item) {
					if(!in_array($item['spec_id'], $arr_spec_id)){
						array_push($arr_spec_id, $item['spec_id']);
						$add_data_detail[] = array(
							'transfer_id'=>$stocktrans_id,
							'spec_id'=>$item['spec_id'],
							'from_position'=>$item['from_position_id'],
							'to_position'=>$item['to_position_id'],
							'stock_num'=>$item['stock_num'],
							'num'=>$item['stock_num'],
							'in_num'        => 0,
							'out_num'       => 0,
							'remark'=>'',
							'created'=>array('exp','NOW()'),
							'modified'      => array('exp','NOW()'),
						);
						$stock_num += $item['stock_num'];
						$goods_type_count += 1;
					}else{
						$spec_id_isset_sql = "UPDATE tmp_import_detail tid set tid.status = 1,tid.result = '失败', tid.message='该仓库(".$item['warehouse_name'].")内商家编码(".$item['spec_no'].")重复'"
							." where tid.status = 0 and tid.line = ".$item['line'].";";
						$this->execute($spec_id_isset_sql);
					}
				}
				$res_add = M('stock_transfer_detail')->addAll($add_data_detail);
				$add_count = array(
					'goods_count' => $stock_num,
					'goods_in_count' => $stock_num,
					'goods_out_count' => $stock_num,
					'goods_type_count' => $goods_type_count,
				);
				$this->updateInfo($add_count,array('rec_id'=>$stocktrans_id));
				$this->commit();
				$this->submitStockTransOrder($stocktrans_id);
			}
		}catch (\PDOException $e) {
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importPositionTrans--'.$msg);
			$result['status'] = 1;
			$result['msg'] = $msg;
			return;

		} catch (\Exception $e){
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importPositionTrans--'.$msg);
			$result['status'] = 1;
			$result['msg'] = $msg;
			return;
		}
		try {
			$deal_import_data_result = $this->query("select line as id,message,status,result from tmp_import_detail where status =1");
			if(!empty($deal_import_data_result)){
				$result['status'] = 2;
				$result['data'] = $deal_import_data_result;
			}
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['msg'] = '导入信息获取失败,联系管理员';
			$result['data'] = array();
			\Think\Log::write($this->name.'--importPositionTrans--'.$msg);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['msg'] = '导入信息获取失败,联系管理员';
			$result['data'] = array();
			\Think\Log::write($this->name.'--importPositionTrans--'.$msg);
		}

	}
	public function exportToExcel($id_list,$search,$type = 'excel'){
		try {
			D('Setting/EmployeeRights')->setSearchRights($search,'from_warehouse_id',2);
			D('Setting/EmployeeRights')->setSearchRights($search,'to_warehouse_id',2);
			$creator=session('account');
			$where_arr = $this->searchForm($search);
			$where_stock_transfer_detail=$where_arr['where_stock_transfer_detail'];
			$where_stock_transfer=$where_arr['where_stock_transfer'];
			$where_stock_transfer=ltrim($where_stock_transfer, ' AND ');
			$where_stock_transfer_detail=ltrim($where_stock_transfer_detail, ' AND ');
			if(!empty($id_list)){
				$where_stock_transfer .= " AND st.rec_id in (".$id_list.") ";
			}
			if(!empty($where_stock_transfer_detail)){
				if(!empty($where_stock_transfer)){
					$where_stock_transfer_detail = $where_stock_transfer_detail.' and '.$where_stock_transfer;
				}
				$total=$this->distinct(true)->alias('st')->field('st.rec_id as id')->join('LEFT JOIN stock_transfer_detail std ON std.transfer_id=st.rec_id LEFT JOIN goods_spec gs ON gs.spec_id = std.spec_id  LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ')->where($where_stock_transfer_detail);
			}else{
				$total=$this->distinct(true)->alias('st')->field('st.rec_id as id')->where($where_stock_transfer);
			}
			$m=clone $total;
			$point_number=get_config_value('point_number',0);
			$goods_in_count = 'CAST(st.goods_in_count AS DECIMAL(19,'.$point_number.'))';
			$goods_out_count = 'CAST(st.goods_out_count AS DECIMAL(19,'.$point_number.'))';
			$goods_count = 'CAST(st.goods_count AS DECIMAL(19,'.$point_number.'))';
			$note_count = 'CAST(st.note_count AS DECIMAL(19,'.$point_number.'))';
			$total_sql = $total->fetchsql(true)->select();
			$data = $this->fetchSql(false)->distinct(true)->alias('st')->field(' st.rec_id AS id,cw1.warehouse_id,st.outer_no,st.error_info as wms_result,cw.warehouse_id,cw1.name as from_warehouse_id,cw.name as to_warehouse_id, he.fullname creator_id, cl.logistics_name logistics_id, st.rec_id, st.transfer_no,st.type,st.mode,st.status,st.contact, st.telno, st.remark, st.modified, st.created,'.$note_count.' note_count,'.$goods_count.' goods_count,st.goods_type_count,'.$goods_in_count.' goods_in_count,'.$goods_out_count.' goods_out_count')->join('INNER JOIN ('.$total_sql.') as sp ON sp.id = st.rec_id')->join('LEFT JOIN stock_transfer_detail std ON std.transfer_id=st.rec_id LEFT JOIN goods_spec gs ON gs.spec_id = std.spec_id  LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN cfg_logistics cl ON cl.logistics_id = st.logistics_id  LEFT JOIN hr_employee he ON he.employee_id = st.creator_id  LEFT JOIN cfg_warehouse cw1 on cw1.warehouse_id=st.from_warehouse_id LEFT JOIN cfg_warehouse cw on cw.warehouse_id = st.to_warehouse_id')->order('st.rec_id desc')->select();
			$stocktrans_status=array(
				'10'=>'已取消',
				'20'=>'编辑中',
				'90'=>'调拨完成',
			);
			$stocktrans_type=array(
				'1'=>'快速调拨',
			);
			$stocktrans_mode=array(
				'0'=>'单品调拨',
				'1'=>'货位调拨',
			);
			for($i=0;$i<count($data);$i++){
				$data[$i]['status']=$stocktrans_status[$data[$i]['status']];
				$data[$i]['type']=$stocktrans_type[$data[$i]['type']];
				$data[$i]['mode']=$stocktrans_mode[$data[$i]['mode']];
			}
			$num = workTimeExportNum($type);
			if (count($data) > $num) {
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
				SE(self::OVER_EXPORT_ERROR);
			}
			$excel_header = D('Setting/UserData')->getExcelField('Stock/StockTransManagement', 'stocktransmanagement');
			$title = '调拨单';
			$filename = '调拨单';
			foreach ($excel_header as $v) {
				$width_list[] = 20;
			}
			if($type == 'csv') {
				$ignore_arr = array('原仓库','目标仓库','经办人','联系人','物流公司');
				ExcelTool::Arr2Csv($data, $excel_header, $filename, $ignore_arr);
			}else {
				ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
			}
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		} catch (BusinessLogicException $e) {
			SE($e->getMessage());
		} catch (\Exception $e) {
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
	}
	public function importSpecTrans($data,&$result){
		try{
			$operator_id = get_operator_id();
			\Think\Log::write('导入数据提示:'.print_r($data,true),\Think\Log::DEBUG);
			//查入临时表信息包括验证完的信息
			foreach($data as $item){
				$res_insert_temp_import_detail = $this->execute("insert into tmp_import_detail(`spec_no`,`to_position_no`,`from_warehouse_name`,`to_warehouse_name`,`num`,`status`,`result`,`message`,`line`) values('{$item['spec_no']}','{$item['to_position_no']}','{$item['from_warehouse_name']}','{$item['to_warehouse_name']}',NULLIF('{$item['num']}',''),".(int)$item['status'].",'".$item['result']."','{$item['message']}',".(int)$item['line'].")");
			}
			//校验相应的仓库、商家编码、货位和调拨数量
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			$query_warehouse_id_list_sql = "SELECT * FROM tmp_import_detail " ;
			$res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
			\Think\Log::write('导入临时表的数据:'.print_r($res_query_warehouse_id_list,true),\Think\Log::DEBUG);
			//调出仓库是否存在
			$query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.from_warehouse_name"
				."  SET tid.from_warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'调出仓库不存在','') "
				."  WHERE tid.status=0 ;";
			$res_query_warehouse_isset = $this->execute($query_warehouse_isset_sql);
			//调出仓库是否是有权限的仓库
			$rights_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.from_warehouse_id"
				." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'调出仓库没有权限','') "
				." where tid.status = 0;";
			$right_warehouse = $this->execute($rights_warehouse_sql);
			//目标仓库是否存在
			$query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.to_warehouse_name"
				."  SET tid.to_warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'目标仓库不存在','') "
				."  WHERE tid.status=0 ;";
			$res_query_warehouse_isset = $this->execute($query_warehouse_isset_sql);
			//目标仓库是否是有权限的仓库
			$rights_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.to_warehouse_id"
				." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'目标仓库没有权限','') "
				." where tid.status = 0;";
			$right_warehouse = $this->execute($rights_warehouse_sql);
			//商家编码是否存在
			$query_goods_spec_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
				." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.spec_id IS NULL,1,0),tid.result= IF(gs.spec_id IS NULL,'失败',''),tid.message=IF(gs.spec_id IS NULL,'商家编码不存在','')"
				." WHERE tid.status=0;";
			$res_query_goods_spec_isset = $this->execute($query_goods_spec_isset_sql);
			//调出仓库中是否存在该商品
			$res_query_stock_sql = "UPDATE tmp_import_detail tid left join stock_spec ss on ss.spec_id=tid.spec_id and ss.warehouse_id = tid.from_warehouse_id"
				." set tid.stock_num = if(ss.rec_id is null,0,ss.stock_num),tid.status = if(ss.rec_id is null,1,0),tid.result = if(ss.rec_id is null,'失败',''), tid.message=if(ss.rec_id is null,'在调出仓库内不存在该商品','')"
				." where tid.status = 0;";
			$res_query_stock_isset = $this->execute($res_query_stock_sql);
			//更新调出仓库默认货位
			$query_position_no_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec_position ssp ON ssp.spec_id = tid.spec_id and ssp.warehouse_id = tid.from_warehouse_id LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = -tid.from_warehouse_id"
				." SET tid.from_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id )"//,tid.to_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id )
				." WHERE tid.status= 0;";
			$res_query_position_no_isset = $this->execute($query_position_no_sql);
			//调入货位不填则更新目标仓库默认货位
			$query_position_no_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec_position ssp ON ssp.spec_id = tid.spec_id and ssp.warehouse_id = tid.to_warehouse_id LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = -tid.to_warehouse_id"
				." SET tid.to_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id )"//,tid.to_position_id=if(ssp.position_id is null,cwp.rec_id,ssp.position_id )
				." WHERE tid.status= 0 AND tid.to_position_no='';";
			$res_query_position_no_isset = $this->execute($query_position_no_sql);//更新目标仓库默认货位
			//目标仓库中是否存在货位，存在则更新
			$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse_position cwp ON cwp.warehouse_id = tid.to_warehouse_id and cwp.position_no = tid.to_position_no"
				." SET tid.to_position_id=cwp.rec_id,tid.status = if(cwp.rec_id is null,1,0),tid.result = if(cwp.rec_id is null,'失败',''),tid.message=if(cwp.rec_id is null,'该目标仓库内不存在该货位','')"
				." WHERE tid.status= 0 AND tid.to_position_no<>''";
			$this->execute($query_position_no_isset_sql);
			//查询调入仓库是否已存在商品货位
			$query_position_no_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec_position ssp ON ssp.spec_id = tid.spec_id and ssp.warehouse_id = tid.to_warehouse_id and ssp.position_id = tid.to_position_id"
				." SET tid.status = if(ssp.position_id is null,1,0),tid.result = if(ssp.position_id is null,'失败',''),tid.message=if(ssp.position_id is null,'该目标仓库已存在该商品对应货位','')"
				." WHERE tid.status= 0 AND tid.to_position_no<>'';";
			$res_query_position_no_isset = $this->execute($query_position_no_sql);
			//调拨数量是否大于库存量
			$res_query_num_sql = "UPDATE tmp_import_detail tid"
				." set tid.status = if(tid.num > tid.stock_num,1,0),tid.result = if(tid.num > tid.stock_num,'失败',''), tid.message=if(tid.num > tid.stock_num,'调拨数量不能超出调出仓库的库存量','')"
				." where tid.status = 0;";
			$this->execute($res_query_num_sql);
			//按调入仓库，目标仓库分组
			$query_warehouse_id_list_sql = "SELECT from_warehouse_id,to_warehouse_id FROM tmp_import_detail WHERE STATUS=0 GROUP BY from_warehouse_id , to_warehouse_id" ;
			$res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
			if(empty($res_query_warehouse_id_list)){
				$deal_import_data_result = $this->query("select line as id,message,status,result,spec_no from tmp_import_detail where status =1");
				\Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);
				if(!empty($deal_import_data_result)){
					$result['status'] = 2;
					$result['data'] = $deal_import_data_result;
				}
				return;
			}
			$operator_id = get_operator_id();
			for($i=0; $i<count($res_query_warehouse_id_list); ++$i){
				$sql_get_no = 'select FN_SYS_NO("transfer") transfer_no';
				$res_stocktrans_no = $this->query($sql_get_no);
				$stocktrans_no = $res_stocktrans_no[0]['transfer_no'];
				$trans_data = $this->query("select * from tmp_import_detail tid where tid.status=0 and tid.from_warehouse_id=".$res_query_warehouse_id_list[$i]['from_warehouse_id']." and tid.to_warehouse_id=".$res_query_warehouse_id_list[$i]['to_warehouse_id']);
				$id = $res_query_warehouse_id_list[$i]['to_warehouse_id'];
				$fields = array('contact','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `telno`','CONCAT(province,city,district,address) as `address`');
				$where = array('warehouse_id'=>$id);
				$warehouse_info = D('Setting/Warehouse')->getWarehouseList($fields,$where);
				$nums = 0;
				$lines = '';
				$goods_type_count = 0;
				$add_data = array(
					'from_warehouse_id' => $trans_data[0]['from_warehouse_id'],
					'to_warehouse_id' => $trans_data[0]['to_warehouse_id'],
					'transfer_no' => $stocktrans_no,
					'mode' => 0,
					'type' => 1,
					'contact' => $warehouse_info[0]['contact'],
					'telno' => $warehouse_info[0]['telno'],
					'address' => $warehouse_info[0]['address'],
					'logistics_id' => 0,
					'creator_id' => $operator_id,
					'status' => 20,
					'remark' => '',
					'created' => array('exp', 'NOW()'),
					'modified' => array('exp', 'NOW()'),
				);
				/*if (empty($item['position_id']) || !isset($item['position_id'])) {
					$item['position_id'] = (-$item['warehouse_id']);
				}*/
				$add_result = $this->insert($add_data);
				$log_data = array(
					'order_type' => 3,
					'order_id' =>$add_result,
					'operator_id'=>$operator_id,
					'operator_type'=>array('exp',"IF({$add_data['status']}=30,13,11)"),
					'message'=>'导入单品调拨单',
				);
				D('Stock/StockInoutLog')->insertStockInoutLog($log_data);
				$stocktrans_id_sql = "SElECT rec_id FROM stock_transfer WHERE transfer_no = \"{$stocktrans_no}\"";
				$stocktrans_id = $this->query($stocktrans_id_sql);
				$stocktrans_id = $stocktrans_id[0]['rec_id'];
				$add_data_detail = array();
				$arr_spec_id=array();
				foreach($trans_data as $item) {
					if(!in_array($item['spec_id'], $arr_spec_id)){
						array_push($arr_spec_id, $item['spec_id']);
						$add_data_detail[] = array(
							'transfer_id'	=>$stocktrans_id,
							'spec_id'		=>$item['spec_id'],
							'from_position' =>$item['from_position_id'],
							'to_position'	=>$item['to_position_id'],
							'stock_num'		=>$item['stock_num'],
							'num'			=>$item['num'],
							'in_num'        =>0,
							'out_num'       =>0,
							'remark'		=>'',
							'created'		=>array('exp','NOW()'),
							'modified'      =>array('exp','NOW()'),
						);
						$nums += $item['num'];
						$goods_type_count += 1;
					}else{
						//$lines .= $item['line'] . ',';
						$spec_id_isset_sql = "UPDATE tmp_import_detail tid set tid.status = 1,tid.result = '失败', tid.message='由(".$item['from_warehouse_name'].")调拨到(".$item['to_warehouse_name'].")的商家编码(".$item['spec_no'].")重复'"
							." where tid.status = 0 and tid.line = ".$item['line'].";";
						$this->execute($spec_id_isset_sql);
					}
				}
				/*$lines = substr($lines,0,-1);
				$spec_id_isset_sql = "UPDATE tmp_import_detail tid set tid.status = 1,tid.result = '失败', tid.message='商家编码，调出仓库，目标仓库重复'"
							." where tid.status = 0 and tid.line in ('".$lines."');";
				$this->execute($spec_id_isset_sql);*/
				$res_add = M('stock_transfer_detail')->addAll($add_data_detail);
				$add_count = array(
					'goods_count' => $nums,
					'goods_in_count' => $nums,
					'goods_out_count' => $nums,
					'goods_type_count' => $goods_type_count,
				);
				$this->updateInfo($add_count,array('rec_id'=>$stocktrans_id));
				$this->submitStockTransOrder($stocktrans_id);
			}
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importSpecTrans--'.$msg);
			$result['status'] = 1;
			$result['msg'] = $msg;
			return;

		} catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importSpecTrans--'.$msg);
			$result['status'] = 1;
			$result['msg'] = $msg;
			return;
		}
		try {
			$deal_import_data_result = $this->query("select line as id,message,status,result from tmp_import_detail where status =1");
			if(!empty($deal_import_data_result)){
				$result['status'] = 2;
				$result['data'] = $deal_import_data_result;
			}
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['msg'] = '导入信息获取失败,联系管理员';
			$result['data'] = array();
			\Think\Log::write($this->name.'--importSpecTrans--'.$msg);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['msg'] = '导入信息获取失败,联系管理员';
			$result['data'] = array();
			\Think\Log::write($this->name.'--importSpecTrans--'.$msg);
		}

	}
	public function cancel_st($id){
		try{
			$this->where(array('rec_id'=>$id))->save(array('status'=>10));
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
