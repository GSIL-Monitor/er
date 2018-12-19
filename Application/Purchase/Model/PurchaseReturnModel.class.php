<?php
namespace Purchase\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;
class PurchaseReturnModel extends model{
	protected $tableName = 'purchase_return';
	protected $pk = 'return_id';
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
	public function savePurchaseReturn($data){
		try{
			$this->startTrans();
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$operator_id = get_operator_id();
			$order = $data['search'];
			if(!$this->create($order)){
				$error = implode($this->getError());
				SE($error);
			}
			
			$order['type'] = 1;
			$order['receive_address'] = $order['address'];
			$order['creator_id'] = get_operator_id();
			$order['return_no'] = array('exp',"FN_SYS_NO('return')");
			$warehouse = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$order['warehouse_id']))->find();
			if((int)($warehouse['type'] == 11)){
				if(empty($order['province']) || empty($order['city']) || empty($order['district'])){
					SE('委外采购退货省市区不能为空');
				}
				$order['status'] = 42;
				$address = $this->query('select dp.name as province ,dc.name as city,dd.name as district from dict_province dp inner join dict_city dc on 
					dc.province_id = dp.province_id inner join dict_district dd on dd.city_id = dc.city_id 
					where dp.province_id = %d and dc.city_id = %d and dd.district_id = %d',$order['province'],$order['city'],$order['district']);
				$order['province'] = $address[0]['province'];
				$order['city'] = $address[0]['city'];
				$order['district'] = $address[0]['district'];
			}else{
				$order['status'] = 20;
			}
			$order['created'] = array('exp','NOW()');
			$order['modified'] = array('exp','NOW()');
			$res_order_id = $this->add($order);
			//更新日志
			$log_data = array(
				'return_id' => $res_order_id,
				'operator_id' => $operator_id,
				'type' => 1,
				'remark' => '新建采购退货单',
				'created' => array('exp','NOW()'),
			);
			D('Purchase/PurchaseReturnLog')->add($log_data);
			if(empty($data['rows']['insert'])&&empty($data['rows']['delete'])&&empty($data['rows']['update'])){
				SE('货品信息不能为空');
			}
			$detail_result = $this->savePurchaseReturnDetail($data['rows'],$res_order_id);
			$this->commit();
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

	public function savePurchaseReturnDetail($details,$order_id){
		try{
			$result = array('status'=>0,'info'=>'success','data'=>array());
			$detail_error_list = array();
			$order_detail_model = D('Purchase/PurchaseReturnDetail');
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
					'return_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 1,
					'remark' => "删除单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			foreach($details['update'] as $key=>$detail){
				$order_detail_model->where(array('rec_id'=>$detail['id']))->field(array('num','price','amount','remark'))->save($detail);
				$log_data[] = array(
					'return_id' => $order_id,
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
					'return_id' => $order_id,
					'spec_id' =>$detail['spec_id'],
					'num' =>$detail['num'],
					'out_num'=>0,
					'out_amount'=>0,
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
					'return_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 1,
					'remark' => "添加单品-".$detail['spec_no'],
					'created' => array('exp','NOW()'),
				);
			}
			if(empty($log_data[0])){
				D('PurchaseReturnLog')->add($log_data);
			}else{
				D('PurchaseReturnLog')->addAll($log_data);
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
            $where_purchase = '';
            if($type == 'return'){
                $where_purchase .= ' (pcr.status = 40 or pcr.status = 50) ';
            }
            foreach($search as $k=>$v){
                if($v === ''){
                    continue;
                }
                switch($k){
                    case 'status':
                        set_search_form_value($where_purchase,$k,$v,'pcr',2,' AND ');
                        break;
                    case 'warehouse_id':
                        set_search_form_value($where_purchase,$k,$v,'pcr',2,' AND ');
                        break;
                    case 'return_no':
                        set_search_form_value($where_purchase,$k,$v,'pcr',1,' AND ');
                        break;
                    case 'provider_id':
                        set_search_form_value($where_purchase,$k,$v,'pcr',2,' AND ');
                        break;
                    case 'provider_ids':
                        set_search_form_value($where_purchase,'provider_id',$v,'pcr',2,' AND ');
                        break;
                }
            }
            $where_purchase=ltrim($where_purchase, ' AND ');

            $order = $sort.' '.$order;
            $limit = ($page - 1)*$rows.','.$rows;
            $order=addslashes($order);

            $total = $this->distinct(true)->alias('pcr')->field('pcr.return_id as id')->where($where_purchase);

            $point_number=get_config_value('point_number',0);

            $goods_count = 'CAST(pcr.goods_count AS DECIMAL(19,'.$point_number.')) goods_count';
            $total_fee = "CAST((pcr.goods_fee+pcr.post_fee+pcr.other_fee) AS DECIMAL(19,".$point_number.")) as total_fee";

            $m = clone $total;
            $total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
            $num = $this->query($m->fetchsql(true)->count());
            $row = $this->fetchsql(false)->distinct(true)->alias('pcr')->field('pcr.return_id AS id,pcr.outer_no,pcr.error_info,cw.warehouse_id,cw.name as warehouse_name,cw.contact as warehouse_contact,cw.telno as warehouse_telno,cw.address as warehouse_address,pcr.goods_out_count,pv.provider_name,pv.mobile as provider_mobile,pv.address as provider_address,pcr.return_no,pcr.status,he1.fullname as creator_name,he2.fullname as purchaser_name,he3.fullname as check_name,pcr.check_time,cl.logistics_name as logistics_type,pcr.goods_fee,pcr.post_fee,pcr.other_fee,'.$total_fee.',pcr.goods_type_count,'.$goods_count.',pcr.remark,pcr.created,pcr.modified')->join('INNER JOIN ('.$total_sql.') ts ON ts.id = pcr.return_id')->join('LEFT JOIN cfg_logistics cl ON cl.logistics_id = pcr.logistics_type LEFT JOIN hr_employee he1 ON he1.employee_id = pcr.creator_id LEFT JOIN hr_employee he2 ON he2.employee_id = pcr.purchaser_id LEFT JOIN hr_employee he3 ON he3.employee_id = pcr.check_operator_id LEFT JOIN cfg_warehouse cw on cw.warehouse_id=pcr.warehouse_id LEFT JOIN purchase_provider pv ON pv.id = pcr.provider_id')->order($order)->select();
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
    public function updatePurchaseReturn($purchase_info){
        try{
            $this->startTrans();
            $result = array('status'=>0,'info'=>'保存成功','data'=>array());
            $operator_id = get_operator_id();
            $id = $purchase_info['return_id'];
            $field = array('status','return_no');
            $where = array('return_id'=>$id);
            $order_info = $this->getPurchase($field,$where);
            if(empty($order_info)){
                \Think\Log::write('没查询到采购退货单信息');
                SE(self::PDO_ERROR);
            }
            if($order_info[0]['status']!=20 && $order_info[0]['status']!=43){
                SE('采购退货单状态不正确，只能编辑采购退货状态为编辑中的采购退货单！');
            }
            $update_info = $purchase_info['search'];

//            $update_info['receive_address'] = $update_info['address'];
            $update_info['tax_fee'] = $update_info['amount'];
            unset($update_info['amount']);
//            unset($update_info['address']);
            if(!$this->create($update_info)){
                SE(implode($this->getError()));
            }
            $this->where($where)->save($update_info);
            $log_data = array(
                'return_id' => $id,
                'operator_id' => $operator_id,
                'type' => 1,
                'remark' => "更新保存采购退货单-".$order_info[0]['purchase_no'],
                'created' => array('exp','NOW()'),
            );
            D('PurchaseReturnLog')->add($log_data);
            $purchase_info['rows']['update'][0]['price'] = $purchase_info['rows']['update'][0]['cost_price'];
            $detail_result = $this->savePurchaseReturnDetail($purchase_info['rows'],$id);
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
    public function submitPurchaseReturn($id){
        try{
            $data['status'] = 0;
            $data['info'] = '审核成功';
            $operator_id = get_operator_id();
            $where = array('return_id'=>$id);
            $result=$this->fetchsql(false)->field('status')->where($where)->select();
            if($result[0]['status'] != 20){
                SE('采购退货单状态不正确，只能审核采购退货状态为编辑中的采购退货单！');
            }
            $fields = array('spec_id','num');
            $purchase_detail = D('PurchaseReturnDetail')->field($fields)->where($where)->select();
            if(empty($purchase_detail)){
                SE('采购退货单中尚未添加退货明细，请驳回编辑');
            }

            $this->startTrans();
            foreach($purchase_detail as $key=>$value){
                $stock_spec = D('StockSpec')->field('spec_id,return_num')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->select();
                if(empty($stock_spec)){
                    $spec_info = array(
                        'spec_id'=>$purchase_detail[$key]['spec_id'],
                        'created'=>array('exp','NOW()'),
                        'modified'=>array('exp','NOW()'),
                        'return_num'=>$purchase_detail[$key]['num'],
                        'status'=>1
                    );
                    //M('stock_spec_position')->add($spec_info);
                    D('StockSpec')->add($spec_info);
                }else{
                    $info = array('return_num'=>$purchase_detail[$key]['num']+$stock_spec[0]['return_num'],'status'=>1);
                    D('StockSpec')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->save($info);
                }
            }

            $sql_api_goods_spec = "UPDATE api_goods_spec SET is_stock_changed=1,stock_change_count=stock_change_count+1 "
                ."WHERE is_deleted=0 AND match_target_type=1 AND match_target_id IN (SELECT spec_id FROM purchase_return_detail WHERE return_id = {$id});";
            $this->execute($sql_api_goods_spec);

            $update_status=array('status'=>40,'check_operator_id'=>$operator_id,'check_time'=>array('exp','NOW()'),'version_id'=>$result[0]['version_id']+1);
            $updata_purchase = $this->where($where)->save($update_status);
            $log_data = array(
                'return_id'=>$id,
                'operator_id'=>$operator_id,
                'type'=>1,
                'remark'=>'审核采购退货单'
            );
            D('PurchaseReturnLog')->add($log_data);
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
            SE($e->getMessage());
        }
        return $data;
    }
    public function cancelPurchaseReturn($id){
        try{
            $data['status'] = 0;
            $data['info'] = '取消成功';
            $operator_id = get_operator_id();
            $where = array('return_id'=>$id);
            $result=$this->fetchsql(false)->field('status')->where($where)->select();
            if($result[0]['status'] != 20){
                SE('采购退货单状态不正确,只能取消编辑中的采购退货单');
            }
            $this->startTrans();
            $update_status=array('status'=>10);
            $updata_purchase = $this->where($where)->save($update_status);
            $log_data = array(
                'return_id'=>$id,
                'operator_id'=>$operator_id,
                'type'=>8,
                'remark'=>'取消采购单'
            );
            D('PurchaseReturnLog')->add($log_data);
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
            SE($e->getMessage());
        }
        return $data;

    }
    public function revertCheck($id){
        try{
            $data['status'] = 0;
            $data['info'] = '驳回成功';
            $operator_id = get_operator_id();
            $where = array('return_id'=>$id);
            $result=$this->fetchsql(false)->field('status')->where($where)->select();
            if($result[0]['status'] != 40){
                SE('采购退货单状态不正确，只能驳回采购退货状态为已审核的采购退货单！');
            }
            $this->startTrans();
            $fields = array('spec_id','num');
            $purchase_detail = D('PurchaseReturnDetail')->field($fields)->where($where)->select();
            foreach($purchase_detail as $key=>$value){
                $stock_spec = D('StockSpec')->field('spec_id,return_num')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->select();
                $info = array('return_num'=>$purchase_detail[$key]['num']-$stock_spec[0]['return_num']);
                D('StockSpec')->where(array('spec_id'=>$purchase_detail[$key]['spec_id']))->save($info);
            }

            $sql_api_goods_spec = "UPDATE api_goods_spec SET is_stock_changed=1,stock_change_count=stock_change_count+1 "
                ."WHERE is_deleted=0 AND match_target_type=1 AND match_target_id IN (SELECT spec_id FROM purchase_return_detail WHERE return_id = {$id});";
            $this->execute($sql_api_goods_spec);

            $update_status=array('status'=>20,'version_id'=>$result[0]['version_id']+1);
            $updata_purchase = $this->where($where)->save($update_status);
            $log_data = array(
                'return_id'=>$id,
                'operator_id'=>$operator_id,
                'type'=>6,
                'remark'=>'驳回采购退货单'
            );
            D('PurchaseReturnLog')->add($log_data);
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
            SE($e->getMessage());
        }
        return $data;
    }
	public function cancelWmsPurchaseReturnOrder($id){
		try{
			$data['status'] = 0;
			$data['info'] = '取消成功';
			$operator_id = get_operator_id();
			$where = array('return_id'=>$id);
			$result=$this->fetchsql(false)->field('status')->where($where)->select();
			if($result[0]['status'] != 46){
				SE('采购退货单状态不正确,只能取消推送成功的采购退货单');
			}
			$this->startTrans();
			$update_status=array('status'=>42);
			$updata_purchase = $this->where($where)->save($update_status);
			$log_data = array(
				'return_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>6,
				'remark'=>'取消采购退货委外单'
			);
			D('PurchaseReturnLog')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用采购退货model失败';
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = $e->getMessage();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用采购退货model失败';
		}
		return $data;
	}
	public function cancel_po($id){
		try{
			$this->where(array('return_id'=>$id))->save(array('status'=>10));
		}catch(BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
		}catch(\PDOException $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}catch(\Exception $e){
			echo json_encode(array('error'=>self::UNKNOWN_ERROR));
		}
		 echo json_encode(array('updated'=>0));
	}
    public function getPurchaseReturnDetailPrintData($id_list){
        try {
            $point_number=get_config_value('point_number',0);
            $num = 'CAST(pc.num AS DECIMAL(19,'.$point_number.')) num';
            $stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
            //$arrive_num = 'CAST(IFNULL(pc.arrive_num,0) AS DECIMAL(19,'.$point_number.')) arrive_num';
            //$stop_num = 'IF(po.status=90,CAST((pc.num-IFNULL(pc.arrive_num,0)) AS DECIMAL(19,'.$point_number.')),0.000) stop_num';
            $where = array('return_id'=>$id_list);
            $fields =  array('po.return_id as id','gs.spec_no','pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'pc.price as cost_price','pc.amount','pc.discount','pc.remark','cgu.name as unit_name',$stock_num);
            $order_field = array('warehouse_id');
            $form_data = D('Purchase/PurchaseReturn')->getPurchase($order_field,$where);
            if(empty($form_data)){
                \Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
            }
            $form_data = $form_data[0];
            $data = D('Purchase/PurchaseReturnDetail')->fetchsql(false)->alias('pc')->field($fields)->join('LEFT JOIN purchase_return po ON po.return_id = pc.return_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['warehouse_id'].')')->where('pc.return_id='.$id_list)->select();
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }
}