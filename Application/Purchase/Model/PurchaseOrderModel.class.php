<?php
namespace Purchase\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

class PurchaseOrderModel extends Model
{
    protected $tableName = 'purchase_order';
    protected $pk        = 'purchase_id';
	protected $_validate = array(
		array('warehouse_name','require','仓库不能为空'),
		array('spec_no','require','商家编码不能为空'),
		array('num','require','采购数量不能为空'),
		array('num','checknum','采购数量不合法',1,'callback'),
		array('price','checkPrice','采购价不合法',1,'callback'),
	);
	 protected $patchValidate = true;
    protected function checkNum($num)
    {
        return check_regex('positive_number',$num);
    }
	 protected function checkPrice($price)
    {
        return check_regex('positive_number',$price);
    }
    public function insertPurchaseOrderForUpdate($data,$update = false,$options = ''){
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
            \Think\Log::write($this->name."-insertPurchaseOrderForUpdate-".$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function updatePurchaseOrder($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updatePurchaseOrder-'.$msg);
        }
    }
	public function savePurchaseOrder($data){
		try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$operator_id = get_operator_id();
			$order = $data['search'];
			$order['receive_address'] = $order['address'];
			//$order['goods_fee'] = $order['amount'];
			$order['tax_fee'] = $order['amount'];
			unset($order['amount']);
			unset($order['address']);
			if(!$this->create($order)){
				$error = implode($this->getError());
				SE($error);
			}
			$order['purchase_no'] = array('exp',"FN_SYS_NO('purchase')");
			$warehouse = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$order['warehouse_id']))->find();
			if((int)($warehouse['type'] == 11)){
				$order['status'] = 43;
			}else{
				$order['status'] = 20;
			}
			$order['creator_id'] = $operator_id;
			$order['created'] = array('exp','NOW()');
			$order['modified'] = array('exp','NOW()');
			$order['goods_arrive_count'] = 0;
			$res_order_id = $this->add($order);
			//更新日志
			$log_data = array(
				'purchase_id' => $res_order_id,
				'operator_id' => $operator_id,
				'type' => 1,
				'remark' => '新建采购单',
				'created' => array('exp','NOW()'),
			);
			D('Purchase/PurchaseOrderLog')->add($log_data);
			if(empty($data['rows']['insert'])&&empty($data['rows']['delete'])&&empty($data['rows']['update'])){
				SE('货品信息不能为空');
			}
			$detail_result = $this->savePurchaseDetail($data['rows'],$res_order_id);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE($e->getMessage());	
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
					
		}
		return $result;
		
	}
	public function savePurchaseDetail($details,$order_id){
		try{
			
			$warehouse_id = $this->field('warehouse_id')->where(array('purchase_id' => $order_id))->select();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$detail_error_list = array();
			$order_detail_model = D('Purchase/PurchaseOrderDetail');
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
				$order_detail_model->where(array('rec_id'=>$detail['id']))->delete();
				$log_data[] = array(
					'purchase_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 1,
					'remark' => "删除单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			foreach($details['update'] as $key=>$detail){
				$order_detail_model->where(array('rec_id'=>$detail['id']))->field(array('num','price','amount','remark'))->save($detail);
				$log_data[] = array(
					'purchase_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 1,
					'remark' => "更新单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			foreach($details['insert'] as $key=>$detail){
				if(!isset($detail['remark']) || empty($detail['remark'])){
					$detail['remark'] = '';
				}
				$insert_data = array(
					'warehouse_id'=>$warehouse_id[0]['warehouse_id'],
					'purchase_id' => $order_id,
					'spec_id' =>$detail['spec_id'],
					'num' =>$detail['num'],
					'arrive_num'=>0,
					'unit_id'=>$detail['base_unit_id'],					
					'base_unit_id' =>$detail['base_unit_id'],
					'price' =>$detail['cost_price'],
					'discount' =>$detail['discount_rate'],
					'amount' =>$detail['amount'],
					'remark' =>$detail['remark'],
					'modified'=>array('exp','NOW()'),
					'created'=>array('exp','NOW()'),	
				);
				$order_detail_model->add($insert_data);
				$log_data[] = array(
					'purchase_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 1,
					'remark' => "添加单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			if(empty($log_data[0])){
				D('PurchaseOrderLog')->add($log_data);
			}else{
				D('PurchaseOrderLog')->addAll($log_data);
			}
			
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			SE($e->getMessage);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc",$type=""){
		try{	
			$warehouse_list = D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
			$rows=intval($rows);
			$page=intval($page);
			$searchForm = $this->searchForm($search,$type);
			$where_stockin=ltrim($searchForm['where_stockin'], ' AND ');
			$where_purchase=ltrim($searchForm['where_purchase'], ' AND ');
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			if(!empty($where_stockin)){
				if(!empty($where_purchase)){
					$where_stockin = $where_purchase.' AND '.$where_stockin;
				}
				$total = $this->distinct(true)->alias('pc')->field('pc.purchase_id as id')->join('LEFT JOIN stockin_order so ON pc.purchase_no = so.src_order_no ')->where($where_stockin);
			}else{
				$total = $this->distinct(true)->alias('pc')->field('pc.purchase_id as id')->where($where_purchase);
			}
			$point_number=get_config_value('point_number',0);
			
			$goods_count = 'CAST(pc.goods_count AS DECIMAL(19,'.$point_number.')) goods_count';
			
			$m = clone $total;
			$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->query($m->fetchsql(true)->count());
			$row = $this->fetchsql(false)->distinct(true)->alias('pc')->field('pc.purchase_id AS id,pc.outer_no,pc.error_info,pc.check_time,cw.warehouse_id,cw.type as warehouse_type,cw.name as warehouse_name,cw.contact as warehouse_contact,IF(TRIM(cw.telno)=\'\' OR cw.telno=NULL,cw.mobile,cw.telno) as warehouse_telno,CONCAT(cw.province,cw.city,cw.district,cw.address) as warehouse_address,pv.provider_name,pv.mobile as provider_mobile,pv.address as provider_address,pc.purchase_no,pc.status,he1.fullname as creator_name,he2.fullname as purchaser_name,he3.account as check_name,cl.logistics_name as logistics_type,pc.goods_fee,pc.post_fee,pc.other_fee,pc.tax_fee,pc.goods_type_count,'.$goods_count.',pc.remark,pc.created,pc.modified')->join('INNER JOIN ('.$total_sql.') ts ON ts.id = pc.purchase_id')->join('LEFT JOIN cfg_logistics cl ON cl.logistics_id = pc.logistics_type LEFT JOIN hr_employee he1 ON he1.employee_id = pc.creator_id LEFT JOIN hr_employee he2 ON he2.employee_id = pc.purchaser_id LEFT JOIN hr_employee he3 ON he3.employee_id = pc.check_operator_id LEFT JOIN cfg_warehouse cw on cw.warehouse_id=pc.warehouse_id LEFT JOIN stockin_order so ON so.src_order_no = pc.purchase_no LEFT JOIN purchase_provider pv ON pv.id = pc.provider_id')->order($order)->select();
			$data = array('total'=>$num[0]['tp_count'],'rows'=>$row);
			
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$data = array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			 SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function searchForm($search,$type=''){
		$where_purchase = '';
		$where_stockin = '';
		if($type == 'purchase'){
			$where_purchase .= ' (pc.status = 40 or pc.status = 50) ';
		}elseif($type == 'return'){
			$where_purchase .= ' (pc.provider_id <> 0) ';
		}
		foreach($search as $k=>$v){
			if($v === ''){
				continue;
			}
			switch($k){
				case 'status':
					set_search_form_value($where_purchase,$k,$v,'pc',2,' AND ');
					break;
				case 'warehouse_id':
					set_search_form_value($where_purchase,$k,$v,'pc',2,' AND ');
					break;
				case 'purchase_no':
					set_search_form_value($where_purchase,$k,$v,'pc',1,' AND ');
					break;
				case 'provider_id':
					set_search_form_value($where_purchase,$k,$v,'pc',2,' AND ');
					break;
				case 'provider_ids':
					set_search_form_value($where_purchase,'provider_id',$v,'pc',2,' AND ');
					break;
				case 'stockin_no':
					set_search_form_value($where_stockin,$k,$v,'so',1,' AND ');
					break;
				case 'purchaser_id':
					set_search_form_value($where_purchase,$k,$v,'pc',2,' AND ');
					break;
				case 'start_time':
					set_search_form_value($where_purchase, 'modified', $v,'pc', 3,' AND ',' >= ');
					break;
				case 'end_time':
					set_search_form_value($where_purchase, 'modified', $v,'pc', 3,' AND ',' <= ');
					break;
			}
		}
		return array(
			'where_stockin'		=> $where_stockin,
			'where_purchase'	=> $where_purchase,
		);
	}
	public function cancelPurchaseOrder($ids){
		try{
			$data['status'] = 0;
			$data['info'] = '取消成功';
			$operator_id = get_operator_id();
			$where = array('purchase_id'=>array('in',$ids));
			$result=$this->fetchsql(false)->field('status')->where($where)->select();
			for($i = 0; $i < count($result); $i++){
				if($result[$i]['status'] != 20){
					SE('采购单状态不正确!');
					break;
				}
			}
			$this->startTrans();
			$update_status=array('status'=>10);
			$updata_purchase = $this->where($where)->save($update_status);
			$ids = explode(',',$ids);
			for($i = 0; $i < count($ids); $i++) {
				$log_data[] = array(
					'purchase_id' => $ids[$i],
					'operator_id' => $operator_id,
					'type' => 8,
					'remark' => '取消采购单'
				);
			}
			D('PurchaseOrderLog')->addAll($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function getPurchase($field,$where){
		try{
			$res=$this->fetchsql(false)->field($field)->alias('po')->join('LEFT JOIN purchase_provider pp ON pp.id =po.provider_id ')->where($where)->select();
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
	public function updatePurchase($purchase_info){
		try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'保存成功','data'=>array());
			$operator_id = get_operator_id();
			$id = $purchase_info['order_id'];
			$field = array('status','purchase_no');
			$where = array('purchase_id'=>$id);
			$order_info = $this->getPurchase($field,$where);
			if(empty($order_info)){
				\Think\Log::write('没查询到采购单信息');
				SE(self::PDO_ERROR);
			}
			if($order_info[0]['status']!=20 && $order_info[0]['status']!=43){
				SE('采购单状态不正确，只能编辑采购状态为编辑中的采购单！');
			}
			$update_info = $purchase_info['search'];
			
			$update_info['receive_address'] = $update_info['address'];
			$update_info['tax_fee'] = $update_info['amount'];
			unset($update_info['amount']);
			unset($update_info['address']);
			if(!$this->create($update_info)){
				SE(implode($this->getError()));
			}
			$this->where($where)->save($update_info);
			$log_data = array(
				'purchase_id' => $id,
				'operator_id' => $operator_id,
				'type' => 1,
				'remark' => "更新保存采购单-".$order_info[0]['purchase_no'],
				'created' => array('exp','NOW()'),
			);
			D('PurchaseOrderLog')->add($log_data);
			$purchase_info['rows']['update'][0]['price'] = $purchase_info['rows']['update'][0]['cost_price'];
			$detail_result = $this->savePurchaseDetail($purchase_info['rows'],$id);
			$this->commit();
		}catch(\PDOException $e){	
			\Think\Log::write($e->getMessage());
			$this->rollback;
			SE(self::PDO_ERROR);
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		return $result;
	}
	public function submitPurchaseOrder($id,$is_return=false){
		try{
			$operator_id = get_operator_id();
			$where = array('purchase_id'=>$id);
			$result=$this->fetchsql(false)->field('status')->where($where)->select();
			if($result[0]['status'] != 20){
				if($is_return){
					return '采购单状态不正确，只能审核采购状态为编辑中的采购单！';
				}else{
					SE('采购单状态不正确，只能审核采购状态为编辑中的采购单！');
				}
			}
			$this->startTrans();
			$update_status=array(
				'status'=>40,
				'check_operator_id'=>$operator_id,
				'check_time'=>array('exp','NOW()'),
			);
			$updata_purchase = $this->where($where)->save($update_status);
			$log_data = array(
				'purchase_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>1,
				'remark'=>'审核采购单'
			);
			$fields = array('spec_id','warehouse_id','num');
			$purchase_detail = D('PurchaseOrderDetail')->field($fields)->where($where)->select();
			foreach($purchase_detail as $key=>$value){
				$stock_spec = D('StockSpec')->field('spec_id,purchase_num')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->select();
				$goods_spec = M('goods_spec')->field('spec_no,deleted')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->find();
				if(empty($goods_spec)||$goods_spec['deleted']!=0){
					if($is_return){
						return '该采购单里的货品('.$goods_spec['spec_no'].')已被删除，请取消该采购单！';
					}else{
						SE('该采购单里的货品('.$goods_spec['spec_no'].')已被删除，请取消该采购单！');
					}
				}
				if(empty($stock_spec)){
					$spec_info = array(
						'spec_id'=>$purchase_detail[$key]['spec_id'],
						'warehouse_id'=>$purchase_detail[$key]['warehouse_id'],
						'created'=>array('exp','NOW()'),
						'modified'=>array('exp','NOW()'),
						'purchase_num'=>$purchase_detail[$key]['num'],
						'purchase_arrive_num'=>0,
					);
					//M('stock_spec_position')->add($spec_info);
					D('StockSpec')->add($spec_info);
				}else{
					$info = array('purchase_num'=>$purchase_detail[$key]['num']+$stock_spec[0]['purchase_num']);
					D('StockSpec')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->save($info);
				}
			}
			
			D('PurchaseOrderLog')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return $e->getMessage();
			}else{
				SE($e->getMessage());
			}
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}
	}
	public function getAlarmPurchaseInfo($spec_ids,$warehouse_id)
    {
        try{
			$sys_config =  get_config_value(array('sys_available_stock','point_number','sys_available_purchase'),array(640,0,0));
			$available_str_stock = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_stock']);
			$available_str_purchase = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_purchase'],'ss','stock_num');
			$available_str_purchase .= '-ss.safe_stock';
            $point_number = $sys_config['point_number'];
            $expect_num = "CAST(0 AS DECIMAL(19,".$point_number.")) expect_num";
            //$num = "CAST((SUM(IF(".intval($warehouse_id)." <> 0,IF( ss.warehouse_id = {$warehouse_id},IFNULL(ss.safe_stock,0),0),IFNULL(ss.safe_stock,0)))-SUM(IF(".intval($warehouse_id)." <> 0,IF( ss.warehouse_id = {$warehouse_id},IFNULL(ss.stock_num,0),0),IFNULL(ss.stock_num,0))) ) AS DECIMAL(19,".$point_number.")) AS num";
			$num = 'CAST(sum(IF('.$available_str_purchase.'<0,-('.$available_str_purchase.'),0)) AS DECIMAL(19,'.$point_number.')) num';
            $stock_num = "CAST(SUM(IF(ss.warehouse_id = {$warehouse_id},IFNULL(ss.stock_num,0),0)) AS DECIMAL(19,".$point_number.")) stock_num";
            $orderable_num = "CAST(IFNULL(".$available_str_stock.",0) AS DECIMAL(19,".$point_number.")) orderable_num";
            $sql= "SELECT gs.spec_no,gs.unit base_unit_id, gs.spec_id as id,gs.spec_id,gs.spec_name, gs.market_price, gs.spec_code, gs.barcode, gs.lowest_price, gs.barcode,gs.retail_price, gs.weight,
                 gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name, gg.goods_id,gg.brand_id, 
                 gb.brand_name,
                 cgu.name as unit_name,
                 CAST((SUM(IF(".intval($warehouse_id)." <> 0,IF( ss.warehouse_id = {$warehouse_id},IFNULL(ss.safe_stock,0),0),IFNULL(ss.safe_stock,0)))-SUM(IF(".intval($warehouse_id)." <> 0,IF( ss.warehouse_id = {$warehouse_id},IFNULL(ss.stock_num,0),0),IFNULL(ss.stock_num,0))) )*MAX(IFNULL(IF( ss.warehouse_id = {$warehouse_id},IFNULL(ss.cost_price,0),0),0)) AS DECIMAL(19,4)) amount,
                 ".$stock_num.",".$orderable_num.",
                 ".$expect_num.", ".$num.",CAST(0 AS DECIMAL(19,4)) AS total_cost,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS cost_price
                 FROM stock_spec ss
                 LEFT JOIN goods_spec gs ON gs.spec_id=ss.spec_id
                 LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                 LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
                 LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
                 where gs.spec_id in (%s) and ss.stock_num<ss.safe_stock "
                    .($warehouse_id==0?"":" and ss.warehouse_id = %d")
                   ." group by ss.spec_id
                 order by id desc";
            $data =$this->query($sql,implode(',',$spec_ids),$warehouse_id);
            return $data;
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-getAlarmPurchaseInfo-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($this->name.'-getAlarmPurchaseInfo-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
	
	public function cancelWmsPurchaseOrder($id){
		try{
			$data['status'] = 0;
			$data['info'] = '取消成功';
			$operator_id = get_operator_id();
			$where = array('purchase_id'=>$id);
			$result=$this->fetchsql(false)->field('status')->where($where)->select();
			if($result[0]['status'] != 48){
				SE('采购单状态不正确,只能取消推送成功的采购单');
			}
			$this->startTrans();
			$update_status=array('status'=>43);
			$updata_purchase = $this->where($where)->save($update_status);
			$log_data = array(
				'purchase_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>8,
				'remark'=>'取消采购单'
			);
			D('PurchaseOrderLog')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用采购model失败';
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = $e->getMessage();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用采购model失败';
		}
		return $data;
	}
	
	public function upload($data,&$result){
		try{
			$operator_id = get_operator_id();
			\Think\Log::write('导入数据提示:'.print_r($data,true),\Think\Log::DEBUG);
			$fields  = array('num','price','spec_no','warehouse_name');
			foreach($data as $import_index => $import_value){
				if(!$this->create($import_value)){
					$data[$import_index]['status']=1;
					$data[$import_index]['result']='失败';
					foreach($fields as $field){
						$error = $this->getError();
						if(isset($error[$field])){
							$data[$import_index]['msg'] = $error[$field];
							break;
						}
					}
				}
			}
			\Think\Log::write('导入数据验证:'.print_r($data,true),\Think\Log::DEBUG);
			foreach($data as $item){
				$this->execute("insert into tmp_import_detail(`spec_no`,`warehouse_name`,`num`,`price`,`provider_name`,`status`,`line`,`result`,`message`)values('{$item['spec_no']}','{$item['warehouse_name']}','{$item['num']}','{$item['price']}','{$item['provider_name']}','{$item['status']}','{$item['line']}','{$item['result']}','{$item['message']}')");
			}
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			if(empty($right_warehouse_id)) SE('没有新建仓库，请到仓库界面新建仓库');
			$query_isset_warehouse = "update tmp_import_detail tid left join cfg_warehouse cw on cw.name = tid.warehouse_name and cw.type=1 and cw.warehouse_id in (".$right_warehouse_id.") set ".
									" tid.status = if(cw.warehouse_id is null,1,0),tid.warehouse_id = cw.warehouse_id,tid.message = if(cw.warehouse_id is null,'仓库不存在',''),".
									" tid.result = if(cw.warehouse_id is null,'失败','') where tid.status = 0 ";
			$this->execute($query_isset_warehouse);
			$quert_isset_spec_no = "update tmp_import_detail tid left join goods_spec gs on gs.spec_no = tid.spec_no ".
									" set tid.spec_id = gs.spec_id,tid.status = if(gs.spec_id is null,1,0),".
									"tid.message = if(gs.spec_id is null,'商家编码不存在',''),tid.result=if(gs.spec_id is null,'失败','') where tid.status= 0";
			$this->execute($quert_isset_spec_no);
			//判断货品是否删除
			$quert_isdel_spec_no = "update tmp_import_detail tid left join goods_spec gs on gs.spec_no = tid.spec_no ".
									" set tid.spec_id = gs.spec_id,tid.status = if(gs.deleted<>0,1,0),".
									"tid.message = if(gs.deleted<>0,'商家编码不存在(该货品已删除)',''),tid.result=if(gs.deleted<>0,'失败','') where tid.status= 0";
			$this->execute($quert_isdel_spec_no);
			$query_provider = "update tmp_import_detail tid left join purchase_provider pp on pp.provider_name = tid.provider_name".
							  " set tid.provider_id = pp.id,tid.status= if(pp.id is null,1,0),tid.result=if(pp.id is null,'失败',''),".
							  "tid.message=if(pp.id is null,'供应商不存在','') where tid.status= 0";
			$this->execute($query_provider);
			//判断供应商对应的货品是否存在
			$query_isset_provider_spec = "update tmp_import_detail tid left join purchase_provider_goods ppg on ppg.provider_id = tid.provider_id AND tid.spec_id=ppg.spec_id".
				" set tid.status= if(ppg.rec_id is null,1,0),tid.result=if(ppg.rec_id is null,'失败',''),".
				"tid.message=if(ppg.rec_id is null,'该供应商下不存在对应的货品','') where tid.status= 0";
			$this->execute($query_isset_provider_spec);

			$info = $this->query('select warehouse_id,provider_id from tmp_import_detail where status=0 group by warehouse_id,provider_id');
			if(empty($info)){
				$error_info = $this->query('select status,message,result,line as id from tmp_import_detail where status = 1');
				\Think\Log::write(print_r($error_info,true),\Think\Log::INFO);
				if(!empty($error_info)){
					$result['status'] = 2;
					$result['data'] = $error_info;
				}
				return;
			}
			foreach($info as $info_value){
				try{
					$this->startTrans();
					$warehouse_info = D('Setting/Warehouse')->field(array('contact,IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `telno`,CONCAT(province,city,district,address) as `address`'))->where(array('warehouse_id'=>$info_value['warehouse_id']))->find();
					$purchase_data = array(
						'purchase_no' => array('exp',"FN_SYS_NO('purchase')"),
						'warehouse_id'=>$info_value['warehouse_id'],
						'creator_id' =>$operator_id,
						'purchaser_id'=>$operator_id,
						'status' =>20,
						'provider_id'=>$info_value['provider_id'],
						'contact'=>$warehouse_info['contact'],
						'telno'=>$warehouse_info['telno'],
						'receive_address'=>$warehouse_info['address'],
						'created'=>array('exp','NOW()'),
						'modified'=>array('exp','NOW()'),	
					);
					$res_order_id = $this->add($purchase_data);
					$log_data = array(
						'purchase_id' => $res_order_id,
						'operator_id' => $operator_id,
						'type' => 1,
						'remark' => '新建采购单-',
						'created' => array('exp','NOW()'),
					);
					D('Purchase/PurchaseOrderLog')->add($log_data);
					$purchase_detail = $this->query("select {$res_order_id} as purchase_id,spec_id,warehouse_id,IF(num IS NULL,0,num) as num,0 as arrive_num,0 as unit_id,0 as base_unit_id,IF(price IS NULL,0,price) as price,IF(num*price IS NULL,0,num*price) as amount,NOW() as created from tmp_import_detail where status =0 and warehouse_id=".$info_value['warehouse_id']." and provider_id=".$info_value['provider_id']);
					D('Purchase/PurchaseOrderDetail')->addAll($purchase_detail);

					$purchase_detail_field = array(
						'count(spec_id) as goods_type_count',
						'sum(num) as goods_count',
						'sum(amount) as goods_fee',
					);
					$purchase_detail_where = array(
						'purchase_id'=>$res_order_id,
					);
					$update_purchase = D('Purchase/PurchaseOrderDetail')->getPurchaseOrderDetailList($purchase_detail_field,$purchase_detail_where);
					$this->updatePurchaseOrder($update_purchase[0],$purchase_detail_where);
					$this->commit();
				}catch (\Exception $e){
					$msg = $e->getMessage();
					\Think\Log::write($this->name.'--importStockSpec--'.$msg);
					$result['status'] = 1;
					$result['msg'] = $msg;
					$this->rollback();
					return;
				}
				$this->submitPurchaseOrder($res_order_id);
			}
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importStockSpec--'.$msg);
			$result['status'] = 1;
			$result['msg'] = $msg;
				return;
		}catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'--importStockSpec--'.$msg);
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
			\Think\Log::write($this->name.'--importStockSpec--'.$msg);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['msg'] = '导入信息获取失败,联系管理员';
			$result['data'] = array();
			\Think\Log::write($this->name.'--importStockSpec--'.$msg);
		}
	}
	public function revertCheck($id,$is_return=false){
		try{
			$operator_id = get_operator_id();
			$where = array('purchase_id'=>$id);
			$result=$this->fetchsql(false)->field('status')->where($where)->select();
			if($result[0]['status'] != 40){
				if($is_return){
					return '采购单状态不正确，只能驳回采购状态为已审核的采购单！';
				}else{
					SE('采购单状态不正确，只能驳回采购状态为已审核的采购单！');
				}
			}
			$this->startTrans();
			$update_status=array('status'=>20);
			$updata_purchase = $this->where($where)->save($update_status);
			$log_data = array(
				'purchase_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>6,
				'remark'=>'驳回采购单'
			);
			$fields = array('spec_id','warehouse_id','num');
			$purchase_detail = D('PurchaseOrderDetail')->field($fields)->where($where)->select();
			foreach($purchase_detail as $key=>$value){
				$stock_spec = D('StockSpec')->field('spec_id,purchase_num')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->select();
				$purchase_num =  $stock_spec[0]['purchase_num']-$purchase_detail[$key]['num'].'';
				$info = array('purchase_num'=>$purchase_num);
				D('StockSpec')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->save($info);
			}
			D('PurchaseOrderLog')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return $e->getMessage();
			}else{
				SE($e->getMessage());
			}
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}
	}
	public function stopPurchaseOrder($id,$is_return=false){
		try{
			$operator_id = get_operator_id();
			$where = array('purchase_id'=>$id);
			$result=$this->fetchsql(false)->field('purchase_no,status')->where($where)->select();
			if($result[0]['status'] != 50){
				if($is_return){
					return '采购单状态不正确，只能停止采购状态是部分到货的订单！';
				}else{
					SE('采购单状态不正确，只能停止采购状态是部分到货的订单！');
				}
			}
			$this->startTrans();
			$stockin_order = M('stockin_order')->field('stockin_no')->where(array('src_order_no'=>$result[0]['purchase_no'],'src_order_type'=>1,'status'=>20))->select();
			if(!empty($stockin_order)){
				if($is_return){
					return '采购单'.$result[0]['purchase_no'].'存在与之相关联的处于编辑中状态的入库单!';
				}else{
					SE('采购单'.$result[0]['purchase_no'].'存在与之相关联的处于编辑中状态的入库单!');
				}
			}
			$update_status=array('status'=>90);
			$updata_purchase = $this->where($where)->save($update_status);
			$log_data = array(
				'purchase_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>1,
				'remark'=>'停止采购，完成采购单'
			);
			$fields = array('spec_id','warehouse_id','num','arrive_num');
			$purchase_detail = D('PurchaseOrderDetail')->field($fields)->where($where)->select();
			foreach($purchase_detail as $key=>$value){
				$stock_spec = D('StockSpec')->field('spec_id,purchase_num')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->select();
				$goods_spec = M('goods_spec')->field('spec_no,deleted')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->find();
				if(empty($goods_spec)||$goods_spec['deleted']!=0){
					if($is_return){
						return '该采购单里的货品('.$goods_spec['spec_no'].')已被删除，请取消该采购单！';
					}else{
						SE('该采购单里的货品('.$goods_spec['spec_no'].')已被删除，请取消该采购单！');
					}
				}
				if(empty($stock_spec)){
					if($is_return){
						return '没有查询到库存详情，请刷新后重试！';
					}else{
						SE('没有查询到库存详情，请刷新后重试！');
					}
				}
					$info = array('purchase_num'=>$stock_spec[0]['purchase_num']-$purchase_detail[$key]['num']+$purchase_detail[$key]['arrive_num']);
					D('StockSpec')->where(array('spec_id'=>$purchase_detail[$key]['spec_id'],'warehouse_id'=>$purchase_detail[$key]['warehouse_id']))->save($info);
			}
			/*$stockin_detail_info = D('Stock/StockinOrderDetail')->alias('sod')->field(array('sod.num,sod.spec_id,sod.expect_num'))->where(array('sod.stockin_id'=>$id))->select();
			foreach($stockin_detail_info as $key=>$value){
				$in_where = array('purchase_id'=>$purchase_info[0]['purchase_id'],'spec_id'=>$stockin_detail_info[$key]['spec_id']);
				$purchase_detail_info = D('Purchase/PurchaseOrderDetail')->field('arrive_num')->where($in_where)->select();
				D('Purchase/PurchaseOrderDetail')->where($in_where)->save(array('arrive_num'=>$purchase_detail_info[0]['arrive_num']+$stockin_detail_info[$key]['num']));
				$stockspec = D('StockSpec')->field('purchase_arrive_num,purchase_num')->where(array('spec_id'=>$stockin_detail_info[$key]['spec_id'],'warehouse_id'=>$purchase_info[0]['warehouse_id']))->select();
				$info = array(
					'purchase_num'=>$stockspec[0]['purchase_num']-$stockin_detail_info[$key]['num'],
					'purchase_arrive_num'=>$stockspec[0]['purchase_arrive_num']+$stockin_detail_info[$key]['num']
				);
				D('Stock/StockSpec')->where(array('spec_id'=>$stockin_detail_info[$key]['spec_id'],'warehouse_id'=>$purchase_info[0]['warehouse_id']))->save($info);
			}*/
			D('PurchaseOrderLog')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return $e->getMessage();
			}else{
				SE($e->getMessage());
			}
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			if($is_return){
				return self::PDO_ERROR;
			}else{
				SE(self::PDO_ERROR);
			}
		}
	}
	public function getPurchaseOrderDetailPrintData($id_list){
//		$sql = "SELECT  pod.purchase_id,pod.spec_id,pod.warehouse_id,pod.num,pod.arrive_num,pod.stockin_amount,pod.unit_id,pod.base_unit_id,pod.price,pod.amount,pod.remark,pod.modified,pod.created
// 				 FROM purchase_order_detail pod
// 				 left join purchase_order po on po.purchase_id = pod.purchase_id
//				 WHERE pod.purchase_id IN (%s)";
		try {
			$point_number=get_config_value('point_number',0);
			$num = 'CAST(pc.num AS DECIMAL(19,'.$point_number.')) num';
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$arrive_num = 'CAST(IFNULL(pc.arrive_num,0) AS DECIMAL(19,'.$point_number.')) arrive_num';
			$stop_num = 'IF(po.status=90,CAST((pc.num-IFNULL(pc.arrive_num,0)) AS DECIMAL(19,'.$point_number.')),0.000) stop_num';
			$where = array('purchase_id'=>$id_list);
			$fields =  array('po.purchase_id as id','gs.spec_no','gs.img_url',$arrive_num,$stop_num,'pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'pc.price as cost_price','pc.amount','pc.remark','cgu.name as unit_name',$stock_num);
			$order_field = array('warehouse_id');
			$form_data = D('Purchase/PurchaseOrder')->getPurchase($order_field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购单信息');
				SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			$data = D('Purchase/PurchaseOrderDetail')->fetchsql(false)->alias('pc')->field($fields)->join('LEFT JOIN purchase_order po ON po.purchase_id = pc.purchase_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['warehouse_id'].')')->where('pc.purchase_id='.$id_list)->select();
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			$data = array('total' => 0, 'rows' => array());
		}
		return $data;
	}
	public function cancel_po($id){
		try{
			$this->where(array('purchase_id'=>$id))->save(array('status'=>10));
		}catch(BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
		}catch(\PDOException $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}catch(\Exception $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}
		 echo json_encode(array('updated'=>0));
	}
	public function exportToExcel($id_list,$search,$type = 'excel'){
		try {
			D('Setting/EmployeeRights')->setSearchRights($search, 'warehouse_id', 2);
			D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
			$creator=session('account');
			$searchForm = $this->searchForm($search);
			$where_stockin=ltrim($searchForm['where_stockin'], ' AND ');
			$where_purchase=ltrim($searchForm['where_purchase'], ' AND ');
			if(!empty($id_list)){
				$select_ids_sql = " pc.purchase_id in (".$id_list.") ";
				$where_purchase = $where_purchase .' AND ' . $select_ids_sql;
			}
			if(!empty($where_stockin)){
				if(!empty($where_purchase)){
					$where_stockin = $where_purchase.' AND '.$where_stockin;
				}
				$total = $this->distinct(true)->alias('pc')->field('pc.purchase_id as id')->join('LEFT JOIN stockin_order so ON pc.purchase_no = so.src_order_no ')->where($where_stockin);
			}else{
				$total = $this->distinct(true)->alias('pc')->field('pc.purchase_id as id')->where($where_purchase);
			}
			$total_sql = $total->fetchsql(true)->select();
			$point_number=get_config_value('point_number',0);
			$num = 'CAST(pod.num AS DECIMAL(19,'.$point_number.')) num';
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$arrive_num = 'CAST(IFNULL(pod.arrive_num,0) AS DECIMAL(19,'.$point_number.')) arrive_num';
			$stop_num = 'IF(pc.status=90,CAST((pod.num-IFNULL(pod.arrive_num,0)) AS DECIMAL(19,'.$point_number.')),0.000) stop_num';
			$sql = "select pc.purchase_id AS id,pc.purchase_no,pc.status,"
				 . " pv.provider_name,pc.created,pc.modified,cw.name as warehouse_name,"
				 . " he1.fullname as creator_name,he2.fullname as purchaser_name,"
				 . " gs.spec_no,".$arrive_num.",".$stop_num.",".$num.",".$stock_num.","
				 . " pod.spec_id,gg.goods_no,gg.goods_name,gb.brand_name,gs.spec_code,gs.spec_name,gs.barcode,"
				 . " pod.price as cost_price,pod.discount,pod.amount,pod.remark,cgu.name as unit_name"
				 . " FROM purchase_order pc"
				 . " INNER JOIN (".$total_sql.") ts ON ts.id = pc.purchase_id"
				 . " LEFT JOIN cfg_warehouse cw on cw.warehouse_id=pc.warehouse_id"
				 . " LEFT JOIN hr_employee he1 ON he1.employee_id = pc.creator_id"
				 . " LEFT JOIN hr_employee he2 ON he2.employee_id = pc.purchaser_id"
				 . " LEFT JOIN purchase_provider pv ON pv.id = pc.provider_id"
				 . " LEFT JOIN purchase_order_detail pod ON pod.purchase_id = pc.purchase_id"
				 . " LEFT JOIN goods_spec gs ON gs.spec_id = pod.spec_id"
				 . " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id"
				 . " LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id"
				 . " LEFT JOIN cfg_goods_unit cgu ON cgu.rec_id = gs.unit"
				 . " LEFT JOIN stock_spec ss ON gs.spec_id=ss.spec_id AND ss.warehouse_id=pc.warehouse_id ORDER BY id DESC";
			$data = $this->query($sql);
			$purchase_status=array(
				'10'=>'已取消',
				'20'=>'编辑中',
				'30'=>'待审核',
				'43'=>'待推送',
				'45'=>'推送失败',
				'48'=>'推送成功',
				'40'=>'已审核',
				'50'=>'部分到货',
				//'60'=>'已到货',
				//'70'=>'待结算',
				//'80'=>'部分结算',
				'90'=>'已完成',
			);
			for($i=0;$i<count($data);$i++){
				$data[$i]['status']=$purchase_status[$data[$i]['status']];
			}
			$num = workTimeExportNum($type);
			if (count($data) > $num) {
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
				SE('导出的详情数据超过设定值，8:00-19:00可以导出1000条，其余时间可以导出4000条!');
			}
			$excel_header = D('Setting/UserData')->getExcelField('Purchase/PurchaseOrder', 'purchase_order_export');
			$title = '采购单';
			$filename = '采购单';
			foreach ($excel_header as $v) {
				$width_list[] = 20;
			}
			if($type == 'csv') {
				$ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','品牌','备注','供应商','收货仓库','建单人','采购员');
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
	//获取采购总金额
	public function getGoodsAmount($search){
		try{
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
			$searchForm = $this->searchForm($search);
			$where_stockin=ltrim($searchForm['where_stockin'], ' AND ');
			$where_purchase=ltrim($searchForm['where_purchase'], ' AND ');
			if(!empty($where_stockin)){
				if(!empty($where_purchase)){
					$where_stockin = $where_purchase.' AND '.$where_stockin;
				}
				$total = $this->distinct(true)->alias('pc')->field('SUM(pc.tax_fee) AS amount')->join('LEFT JOIN stockin_order so ON pc.purchase_no = so.src_order_no ')->where($where_stockin);
			}else{
				$total = $this->distinct(true)->alias('pc')->field('SUM(pc.tax_fee) AS amount')->where($where_purchase);
			}
			$amount = $total->fetchsql(false)->select();
			$amount=empty($amount[0]['amount'])?0:$amount[0]['amount'];
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
		return $amount;
	}
}

?>