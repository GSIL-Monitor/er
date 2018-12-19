<?php
namespace Stock\Model;
use Think\Model;
class SalesMultiLogisticsModel extends Model{
	protected $tableName = 'sales_multi_logistics';
	protected $pk = "rec_id";

	public function getStockPrintTabInfo($id){
		try{
			$data = $this->alias('sml')->field('sml.rec_id id,sml.operator_id,sml.weight,sml.post_cost,sml.created,sml.modified,sml.logistics_id,sml.logistics_no,sml.stockout_id,sml.print_status,he.fullname operator,cl.logistics_name logistics,cl.bill_type,cl.logistics_type')->join('hr_employee he,cfg_logistics cl')->where('sml.operator_id=he.employee_id AND sml.logistics_id=cl.logistics_id AND stockout_id='.$id)->select();
			$total = count($data);
			return array('total'=>$total,'rows'=>$data);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
		}
		return ;
	}

	public function saveLogistics($data){
		$data['operator_id'] = session('operator_id');
        $mul_data = $data;
        $mul_data['print_status']=2;
		try{
				$this->startTrans();
				$mul_data['post_cost'] = D('Setting/LogisticsFee')->calculPostCost($data['stockout_id'],$data['weight'],$data['logistics_id']);
				$this->where('rec_id='.$data['id'])->save($mul_data);
				$trade_id = M('sales_trade')->alias('st')->field('st.trade_id')->where('st.trade_no =\''.$data['trade_no'].'\'')->select();
				$data['message'] = "修改多物流单号".$data['oldLogistics_no']." 到 ".$data['logistics_no'];
				$data['type'] = 163;
				$data['trade_id'] = $trade_id[0]['trade_id'];
				$ret = D("Trade/SalesTradeLog")->addTradeLog($data);
				$this->commit();
				return true;
			}
			catch(\PDOException $e){
				\Think\Log::write($e->getMessage());
				$this->rollback();
				return false;
			}
	}

	public function addLogistics($data){
		$data['created'] = date("Y-m-d H:i:s");
		$data['operator_id'] = session('operator_id');
        $mul_data = $data;
        $mul_data['print_status']=2;
		$mul_data['weight']=(int)$mul_data['weight'];
		if($mul_data['weight'] < 0){
			$mul_data['weight'] = 0;
		}
        try{
			$this->startTrans();
			$mul_data['post_cost'] = D('Setting/LogisticsFee')->calculPostCost($data['stockout_id'],$data['weight'],$data['logistics_id']);
			$trade_id = M('sales_trade')->alias('st')->field('st.trade_id')->where('st.trade_no =\''.$data['trade_no'].'\'')->select();
			$this->add($mul_data,'',true);
			$data['message'] = "添加多物流单：".$data['logistics_no'];
			$data['type'] = 162;
			$data['trade_id'] = $trade_id[0]['trade_id'];
			$ret = D("Trade/SalesTradeLog")->addTradeLog($data);
			$this->commit();
			return true;
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			return false;
		}
	}

	public function deleteLogisticsNo($data){
		try{
			$this->startTrans();
			$where['rec_id'] = $data['id'];
			$this->where($where)->delete();
			$data['type'] = 164;
			$data['message'] = "删除多物流单号：".$data['logistics_no'];
			$data['operator_id'] = session('operator_id');
			$ret = D("Trade/SalesTradeLog")->addTradeLog($data);
			$this->commit();
			return array('status'=>0,'info'=>'');
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			return array('status'=>1,'info'=>"删除数据失败");
		}
	}

	public function getLogisticsNo($logistics_no,$rec_id){
		try{
			$where['logistics_no'] = $logistics_no;
			$data = $this->field('rec_id')->where($where)->select();
			if(count($data)>0&&$data[0]['rec_id']!=$rec_id)
				return true;
			else return false;
		}
		catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			return true;
		}
	}
    // 更新数据
    public function updateMultiLogistics($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateStockoutOrder-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    // 获取同一$stockout_id下包裹号
    public function getPackageNo($where){
        try{
            $data = $this->field('logistics_no')->where($where)->select();
            if(count($data)>0){
                return ['status'=>false,'data'=>0];
            }else{
                $where = ['stockout_id'=>$where['stockout_id']];
                $data = $this->field('package_no')->where($where)->select();
                return ['status'=>true,'data'=>$data];
            }
        }
        catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            return 0;
        }
    }
    //电子面单获取成功更新相关表
    function dealWaybillGetSuccess($logistics_info,$packageNos,&$result)
    {
        $logs = '';
        foreach ($result['success'] as $stockout_id => $success_info)
        {
            foreach ($success_info as $package_no => $package_info){

                try {
                    $this->startTrans();
                    $operator = get_operator_id();
                    $add_data = array(
                        'logistics_no' => $package_info['logistics_no'],
                        'logistics_id' => $package_info['logistics_id'],
                        'stockout_id' => $stockout_id,
                        'package_no' => $package_no,
                        'operator_id' =>$operator,
                        'created' => date("Y-m-d H:i:s")
                    );
                    $res = $this->add($add_data);

                    $get_stockout_fields = array(
                        'src_order_type',
                        'src_order_id'
                    );
                    $get_stockout_cond = array(
                        'stockout_id' => $stockout_id
                    );
                    $res_get_stockout = D('Stock/StockOutOrder')->getStockoutOrders($get_stockout_fields,$get_stockout_cond);

                    $data['created'] = date("Y-m-d H:i:s");
                    $data['operator_id'] = session('operator_id');
                    $data['message'] = "添加并打印多物流单：".$package_info['logistics_no'];
                    $data['type'] = 162;
                    $data['trade_id'] = empty($res_get_stockout[0]['src_order_id'])?'':$res_get_stockout[0]['src_order_id'];
                    D("Trade/SalesTradeLog")->addTradeLog($data);

                    $logs .= $res.':'.$package_info['logistics_no'].'成功 ';
                    \Think\Log::write($logs,\Think\Log::INFO);
                    $this->commit();

                }catch(\PDOException $e){
                    $msg = $e->getMessage();
                    $result['fail'][] = array(
                        'stock_id'   => "{$stockout_id}",
                        'stock_no'   => $package_info['stockout_no'],
                        'msg'        => self::PDO_ERROR,
                    );
                    \Think\Log::write('单号获取成功后，更新数据库失败：stockout_id-'.$stockout_id.';logistics_no-'.$package_info['logistics_no'].$msg);
                    $this->rollback();
                    unset($result['success']["{$stockout_id}"]);
                    continue;
                }catch(\Think\Exception $e){
                    $msg = $e->getMessage();
                    $result['fail'][] = array(
                        'stock_id'   => "{$stockout_id}",
                        'stock_no'   => $package_info['stockout_no'],
                        'msg'        => "单号获取成功后，更新失败：".$msg,
                    );
                    //\Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$success_info['logistics_no'].$msg);
                    $this->rollback();
                    unset($result['success']["{$stockout_id}"]);
                    continue;
                }catch(\Exception $e){
                    $msg = $e->getMessage();
                    $result['fail'][] = array(
                        'stock_id'   => "{$stockout_id}",
                        'stock_no'   => $package_info['stockout_no'],
                        'msg'        => "单号获取成功后，更新失败：".$msg,
                    );
                    \Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$package_info['logistics_no'].$msg);
                    $this->rollback();
                    unset($result['success']["{$stockout_id}"]);
                    continue;
                }
            }
        }
    }
    //京邦达电子面单获取成功更新相关表
    function dealJosWaybillGetSuccess($waybill_list,$jdLogistics_no,$stockout_id,&$result){
        $logs = '';
        for($i=0;$i<count($waybill_list);$i++)
        {
            $packageNo = $i+1;
            try {
                $this->startTrans();
                $insert_data = array(
                    'logistics_no' => $waybill_list[$i]['logistics_no'].'-'.$packageNo.'-'.$jdLogistics_no.'-',
                    'stockout_id' => $stockout_id,
                    'logistics_id' => $waybill_list[$i]['logistics_id']
                );
                $res = $this->addLogistics($insert_data);

                $logs .= $stockout_id.':'.$waybill_list[$i]['logistics_no'].'成功 ';
                \Think\Log::write($logs,\Think\Log::INFO);
                $this->commit();
            }catch(\PDOException $e){
                $msg = $e->getMessage();
                $result['fail'][] = array(
                    'stock_id'   => "{$stockout_id}",
                    'stock_no'   => $result['success'][$i]['stockout_no'],
                    'msg'        => self::PDO_ERROR,
                );
                \Think\Log::write('单号获取成功后，更新数据库失败：stockout_id-'.$stockout_id.';logistics_no-'.$waybill_list[$i]['logistics_no'].$msg);
                $this->rollback();
                unset($result['success']["{$stockout_id}"]);
                continue;
            }catch(\Think\Exception $e){
                $msg = $e->getMessage();
                $result['fail'][] = array(
                    'stock_id'   => "{$stockout_id}",
                    'stock_no'   => $result['success'][$i]['stockout_no'],
                    'msg'        => "单号获取成功后，更新失败：".$msg,
                );
                //\Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$success_info['logistics_no'].$msg);
                $this->rollback();
                unset($result['success']["{$stockout_id}"]);
                continue;
            }catch(\Exception $e){
                $msg = $e->getMessage();
                $result['fail'][] = array(
                    'stock_id'   => "{$stockout_id}",
                    'stock_no'   => $result['success'][$i]['stockout_no'],
                    'msg'        => "单号获取成功后，更新失败：".$msg,
                );
                \Think\Log::write('单号获取成功后，更新数据失败：stockout_id-'.$stockout_id.';logistics_no-'.$result['success'][$i]['stockout_no'].$msg);
                $this->rollback();
                unset($result['success']["{$stockout_id}"]);
                continue;
            }

        }
    }
    public function changeMultiPrintStatus($multiIds,$value=1){
        try{
            $data['print_status'] = $value;
            //$this->where("rec_id in ( " . $multiIds . ")")->save($data);
            $this->where(array('rec_id'=>array('in',$multiIds)))->save($data);
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-changeMultiPrintStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($this->name.'-changeMultiPrintStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    public function changeMultiLogisticsStatus($logisticsNos){
        try{
            $data['print_status'] = 1;
            $this->where("logistics_no in ( " . $logisticsNos . ")")->save($data);
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-changeMultiLogisticsStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($this->name.'-changeMultiLogisticsStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    public function getMultiPrintStatus($fields,$conditions=array()){
        try {
            $res = $this->fetchSql(false)->where($conditions)->field($fields)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getMultiPrintStatus-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
}