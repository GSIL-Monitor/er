<?php
namespace Stock\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;
use Think\Log;

class ApiLogisticsSyncModel extends Model {
    protected $tableName = 'api_logistics_sync';
    protected $pk        = 'rec_id';
    protected $_validate = array(
        array("delivery_term", array(1, 2, 3), "发货条件不正确", 1, "in"),
        array("is_part_sync", array(0, 1), "拆分发货状态不正确", 1, "in"),
        array("is_need_sync", array(0, 1), "是否需要同步状态不正确", 1, "in"),
        array("sync_status", array(-3, -2, -1, 0, 1, 2, 3, 4, 5), "同步状态不正确", 1, "in"),
    );

    public function getApiLogisticsSyncList($page=1, $rows=20, $search, $sort, $order) {
        $sync_status = I('get.sync_status');
        $where_sales_trade        = "";
        $where_api_logistics_sync = "";
        if(!empty($sync_status)  && empty($search))$search['sync_status']=$sync_status;

        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {   //set_search_form_value->Common/Common/function.php
                case 'sync_status':// 同步状态  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                    break;
                case 'trade_no': // 订单号 trade_no  sales_trade
                    set_search_form_value($where_sales_trade, $k, $v, 'st', 1, 'AND');
                    break;
				case 'warehouse_id': // 仓库
                    set_search_form_value($where_sales_trade, $k, $v, 'st', 2, 'AND');
                    break;
                case 'src_tid'://原始单号  tid api_logistics_sync
                    $k = "tid";
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
                case 'buyer_nick':// 客户网名  sales_trade
                    set_search_form_value($where_sales_trade, $k, $v, 'st', 6, 'AND');
                    break;
                case 'logistics_no':// 物流单号  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
                case 'shop_id'://店铺id   api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                    break;
                case 'is_part_sync'://是否拆分  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $order = $sort . ' ' . $order.',trade_time desc ';//排序
        $order = addslashes($order);
        //查询条件数组整理-
        /* $where_sales_trade = ltrim($where_sales_trade, ' AND ');
        $where_api_logistics_sync = ltrim($where_api_logistics_sync, ' AND '); */
        try {
            $sql_total = "select als.rec_id,st.trade_time"
                . " FROM  api_logistics_sync als"
                . " LEFT JOIN sales_trade st on st.trade_id = als.trade_id"
                . " where 1 " . $where_api_logistics_sync . " " . $where_sales_trade;
            $total     = count($this->query($sql_total));

            $sql_limit = $sql_total . ' ORDER BY ' . $order . ' LIMIT ' . $limit;

            $sql_show = "SELECT als.rec_id id,als.trade_id,als.platform_id,cs.shop_name shop_id,als.tid,st.trade_no,als.is_need_sync,"
                . "als.sync_status,als.error_msg,st.trade_time,als.sync_time,st.buyer_nick,cl.logistics_name logistics_id,als.logistics_type,"
                . "als.logistics_no,als.created,cl.bill_type"
                . " FROM ({$sql_limit}) tmp"
                . " LEFT JOIN api_logistics_sync als ON (als.rec_id=tmp.rec_id)"
                . " LEFT JOIN cfg_logistics cl ON (cl.logistics_id=als.logistics_id)"
                . " LEFT JOIN cfg_shop cs ON (cs.shop_id=als.shop_id)"
                . " LEFT JOIN sales_trade st ON (st.trade_id=als.trade_id)";

            $list = $total ? $this->query($sql_show) : array();
            if(count($list)>0){
                foreach($list as $k=>$v){
                    $id = $v['id'];
                    $list[$k]['error_msg'] ="<a href='javascript:void(0)'  onclick='apiLogisticsSync.solution($id)'>{$v['error_msg']}</a>" ;
                }
            }
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }

    //获取物流同步失败的条数
    public function getApiLogisticsSyncNumber($search){
        try {
            $where_sales_trade        = "";
            $where_api_logistics_sync = "";
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            $search['sync_status']=2;
            foreach ($search as $k => $v) {
                if ($v === '') continue;
                switch ($k) {   //set_search_form_value->Common/Common/function.php
                    case 'sync_status':// 同步状态  api_logistics_sync
                        set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                        break;
                    case 'warehouse_id': // 仓库
                        set_search_form_value($where_sales_trade, $k, $v, 'st', 2, 'AND');
                        break;
                    case 'shop_id'://店铺id   api_logistics_sync
                        set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                        break;
                }
            }
            $sql_total = "select als.rec_id"
                . " FROM  api_logistics_sync als"
                . " LEFT JOIN sales_trade st on st.trade_id = als.trade_id"
                . " where 1 " . $where_api_logistics_sync . " " . $where_sales_trade;
            $total     = count($this->query($sql_total));
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $total = false;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $total = false;
        }
        return $total;
    }

    public function showTabInfor($id) {
        $model = M('sales_trade');
        try {
			$point_number = get_config_value('point_number',0);
			$num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
			
            $res  = $model->query("SELECT sto.trade_id,sto.goods_id,sto.goods_no,sto.goods_name,sto.spec_id,sto.spec_no,sto.spec_name,sto.order_price,".$num.",sto.order_price*sto.num sum_price FROM sales_trade_order sto LEFT JOIN api_logistics_sync als on als.trade_id = sto.trade_id WHERE als.rec_id= %d", $id);
            $data = array('total' => count($res), 'rows' => $res);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }
    public function cancelSync($ids,&$result) {
	   $ids=$ids;
	   $fail=array();
	   $success=array();
       $user_id = get_operator_id();
	   foreach($ids as $key=>$value){
			$sync_id=$value['id'];
			try{
				 $this->startTrans();
				 $res_trade_id = $this->where("rec_id = " . $sync_id)->field("trade_id")->find();
				 $update_data     = array(
                 'is_need_sync' => 0,
                 'sync_status'  => 5,
                );
				$this->where("rec_id = " . $sync_id)->save($update_data);
				unset($update_data);
				$update_data = array(
					"trade_id"    => $res_trade_id['trade_id'],
					"operator_id" => $user_id,
					"type"        => 142,
					"message"     => "人工取消物流同步",
					"created"     => date("Y-m-d G:i:s"),
				);
				D("SalesTradeLog")->add($update_data);
				D('Stock/SalesStockOut')->update_als_status("als.trade_id = " . $res_trade_id['trade_id']);
				unset($update_data);
				$res_status=$this->where('rec_id=' . $sync_id)->field('is_need_sync,sync_status')->find();
				$success[]=array(
					'index'=>$value['index'],
					'id'=>$value['id'],
					'is_need_sync'=>$res_status['is_need_sync'],
					'sync_status'=>$res_status['sync_status'],
				);
				 $this->commit();
			} catch (\PDOException $e) {
				$msg = $e->getMessage();
				\Think\Log::write(__CONTROLLER__.'cancelSync'.$msg);
				$fail[]=array(
					'result_info'=>self::PDO_ERROR,
					'trade_no'   =>$value['trade_no'],
				);
				$this->rollback();
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				\Think\Log::write(__CONTROLLER__.'cancelSync'.$msg);
				$fail[]=array(
					'$result_info'=>'未知错误,请联系管理员',
					'trade_no'    =>$value['trade_no'],
				);
				$this->rollback();
			}
		}
		if(!empty($fail)){
			$result['status']=2;
		}
		$result['data']=array('fail'=>$fail,'success'=>$success);
        return $result;
    }
    public function setSyncSuccess($ids,&$result) {
       $ids=$ids;
	   $fail=array();
	   $success=array();
       $user_id = get_operator_id();
	   foreach($ids as $key=>$value){
			$sync_id=$value['id'];
			try{
			    $this->startTrans();
			    $res_trade_id = $this->where("rec_id = " . $sync_id)->field("trade_id")->find();
				$update_data     = array(
                'is_need_sync' => 0,
                'sync_status'  => 4,
			);
            $this->where("rec_id = " . $sync_id)->save($update_data);
            unset($update_data);
            $update_data = array(
                "trade_id"    => $res_trade_id['trade_id'],
                "operator_id" => $user_id,
                "type"        => 143,
                "message"     => "人工设置物流同步成功",
                "created"     => date("Y-m-d G:i:s"),
            );
			
			D('Stock/SalesStockOut')->update_als_status("als.trade_id = " . $res_trade_id['trade_id']);
            D("SalesTradeLog")->add($update_data);
            unset($update_data);
			$res_status=$this->where('rec_id=' . $sync_id)->field('is_need_sync,sync_status')->find();
			$success[]=array(
				'index'=>$value['index'],
				'id'=>$value['id'],
				'is_need_sync'=>$res_status['is_need_sync'],
				'sync_status'=>$res_status['sync_status'],
			);
			 $this->commit();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'setSyncSuccess'.$msg);
			$fail[]=array(
				'result_info'=>self::PDO_ERROR,
				'trade_no'   =>$value['trade_no'],
			);
			$this->rollback();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'setSyncSuccess'.$msg);
        	$fail[]=array(
				'$result_info'=>'未知错误,请联系管理员',
				'trade_no'    =>$value['trade_no'],
			);
			$this->rollback();
			}
		}
		if(!empty($fail)){
			$result['status']=2;
		}
		$result['data']=array('fail'=>$fail,'success'=>$success);
		
        return $result;
    }
    public function retrySync($ids,&$result) {
        $ids = $ids;
        $fail = array();
        $success =array();
        $user_id          = get_operator_id();
        foreach($ids as $key=>$value){
            $sync_id = $value['id'];
            try {
                $this->startTrans();
                $res_trade_id = $this->where("rec_id = " . $sync_id)->field("trade_id,sync_status")->find();
                if($res_trade_id['sync_status']==3){
                    $fail[]=array(
                        'result_info'=>'该订单已经物流同步成功',
                        'trade_no'=>$value['trade_no'],
                    );
                    continue;
                }
                $update_data = array(
                    "is_need_sync" => 1,
                );
                $this->where("rec_id =" . $sync_id)->save($update_data);
                unset($update_data);
                $update_data = array(
                    "trade_id"    => $res_trade_id['trade_id'],
                    "operator_id" => $user_id,
                    "type"        => 141,
                    "message"     => "人工重新同步物流",
                    "created"     => date("Y-m-d G:i:s"),
                );
                D("SalesTradeLog")->add($update_data);
                unset($update_data);
				$res_status=$this->where('rec_id=' . $sync_id)->field('is_need_sync')->find();
                $success[] = array(
                    'index'=>$value['index'],
                    'id' =>$value['id'],
                   'is_need_sync'=>$res_status['is_need_sync'],
                );
                 $this->commit();
            } catch (\PDOException $e) {
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'-retrySync-'.$msg);
                $fail[]=array(
                    'result_info'=>self::PDO_ERROR,
                    'trade_no'=>$value['trade_no'],
                );
                $this->rollback();
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'-retrySync-'.$msg);
                $fail[]=array(
                    'result_info'=>'未知错误,请联系管理员',
                    'trade_no'=>$value['trade_no'],
                );
                $this->rollback();
            }
			
        }
        if(!empty($fail)){
            $result['status'] = 2;
        }
        $result['data'] = array('fail'=>$fail,'success'=>$success);
		
        return $result;
    }
    public function chgLogistics($logistics, $rec_id) {
        $logistics_info   = trim_all($logistics);
        $sync_id          = (int)$rec_id;
        $result['info']   = "修改成功";
        $result['status'] = 1;
        $is_changed = false;
        $operator_id = get_operator_id();
        $this->startTrans();
        try {
            if(empty($logistics_info['logistics_no'])){
                SE('物流单号不能为空');
            }
            $bill_type = $this->query("SELECT cs.bill_type,logistics_name FROM cfg_logistics cs LEFT JOIN api_logistics_sync als ON als.logistics_id = cs.logistics_id WHERE als.rec_id = %d", $sync_id);
            $o_logistics_name = $bill_type[0]['logistics_name'];
            $bill_type = $bill_type[0]['bill_type'];
            if (0 != $bill_type && 1 != $bill_type && 9 != $bill_type && 2 != $bill_type) {
                $result['info']   = '物流单号类型不正确!';
                $result['status'] = 0;
                $this->rollback();
                return $result;
            }
            $tmp_data                 = array("logistics_type", "logistics_name");
            $res_query_logistics_type = D("CfgLogistics")->where("logistics_id = " . $logistics_info['logistics_id'])->field($tmp_data)->find();
            unset($tmp_data);
            $api_logistics_res = $this->where("rec_id =" . $sync_id)->field("trade_id,tid,logistics_no,logistics_id")->find();

            $n_logistics_no = $logistics_info['logistics_no'];
            $n_logistics_id = $logistics_info['logistics_id'];
            $tmp_data = array(
                "logistics_id"   => $n_logistics_id,
                "logistics_type" => $res_query_logistics_type['logistics_type'],
                "logistics_no"   => $n_logistics_no,
            );
            $this->where("rec_id =" . $sync_id)->save($tmp_data);
            unset($tmp_data);

            $tmp_data = array(
                "trade_id"     => $api_logistics_res["trade_id"],
                "logistics_id" => $n_logistics_id,
                "logistics_no" => $n_logistics_no,
            );
            $sql      = "UPDATE `sales_trade` SET `logistics_id`=%d,`logistics_no`=%s WHERE `trade_id` = %d";
            //如果更改物流的话记录订单日志。
            if($n_logistics_id<>$api_logistics_res['logistics_id'])
            {
                $is_changed = true;
                $log_array[] = array(
                    'trade_id' => $api_logistics_res["trade_id"],
                    'operator_id' => $operator_id,
                    'type' => 20,
                    'message' => '物流同步--修改物流：从'.$o_logistics_name.'到'.$res_query_logistics_type['logistics_name']
                );
            }
            if($n_logistics_no<>$api_logistics_res['logistics_no'])
            {
                $is_changed = true;
                $log_array[] = array(
                    'trade_id' => $api_logistics_res["trade_id"],
                    'operator_id' => $operator_id,
                    'type' => 21,
                    'message' => '物流同步--修改物流单号：从'.$api_logistics_res['logistics_no'].'到'.$n_logistics_no
                );
            }

            M("sales_trade")->execute($sql, array($tmp_data["trade_id"], $tmp_data["logistics_id"], $tmp_data["logistics_no"]));
            if($is_changed)
            {
                //插入订单日志
                M('sales_trade_log')->addAll($log_array);
            }
            /*\Think\Log::write(M("sales_trade")->fetchSql(true)->data($tmp_data)->save());
            M("sales_trade")->fetchSql(false)->data($tmp_data)->save();*/
            //unset($tmp_data);
        } catch (\PDOException $e) {
            $msg              = $e->getMessage();
            $result['info']   = self::PDO_ERROR;
            $result['status'] = 0;
            \Think\Log::write($msg);
        } catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['info'] = $msg;
            $result['status'] = 0;
        } catch (\Exception $e) {
            $msg              = $e->getMessage();
            $result['info']   = self::E_ERROR;
            $result['status'] = 0;
            \Think\Log::write($msg);
        }
        if ($result['status'] == 0) {
            $this->rollback();
        } else {
            $logistics['logistics_id'] = $res_query_logistics_type['logistics_name'];
            $result['info']            = $logistics;
            $this->commit();
        }
        return $result;
    }

    public function getErrorMsgSolution($id,&$result){
        try{
            $error_msg = $this->field('error_msg')->where(array('rec_id'=>$id))->find();
            $error_msg = $error_msg['error_msg'];
            //$error_msg = substr($error_msg['error_msg'],0,45);
            if(stripos($error_msg,'Invalid')!==false && stripos($error_msg,'Invalid')==0 && stripos($error_msg,'session')==false){
                $error_msg = 'Invalid';
            }elseif(strpos($error_msg,'订单已出库')!=false){
                $error_msg = '订单已出库';
            }elseif(strpos($error_msg,'session')!=false){
                $error_msg = 'Invalid session #invalid-sessionkey';
            }
            $error_msg = trim(str_replace("'","''",$error_msg));
            $sql = "SELECT * FROM dict_logistics_solution WHERE error_msg like '$error_msg%' LIMIT 1";
            $res=$this->query($sql);
            if(!$res){
                $result['status'] = 1;
                $result['info'] ['rows']= '未找到相应处理方式,请联系管理员处理';
                return;
            }
            $result = array();
            foreach ($res as $v){
                $result['status'] = 0;
                $v['error_msg'] = str_replace("'","",$v['error_msg']);
                $list = array('error_msg'=>$v['error_msg'],'reason'=>$v['reason'],'solution'=>$v['solution'],'msg_id'=>$v['rec_id'],'id'=>$id);
                $result['info'] = array('total'=>1,'rows'=>$list);
            }
        }catch(\PDOException $e){
            \Think\Log::write('getErrorMsgSolution SQL ERR:'.$e->getMessage());
            $result['status'] = 1;
            $result['info']['rows'] = parent::PDO_ERROR;
        }catch(BusinessLogicException $e){
            \Think\Log::write('getErrorMsgSolution BUSINESS ERR:'.$e->getMessage());
            $result['status'] = 1;
            $result['info']['rows'] = $e->getMessage();
        }catch(\Exception $e){
            \Think\Log::write('getErrorMsgSolution:'.$e->getMessage());
            $result['status'] = 1;
            $result['info'] ['rows']= parent::PDO_ERROR;
        }
    }

    public function getApiLogisticsInfoById($id){
        try{
            $res = $this->where(array('rec_id'=>$id))->find();
        }catch (\PDOException $e){
            \Think\Log::write('getApiLogisticsInfoById SQL ERR'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $res;
    }
    public function solveSolution($id){
        try{
            $logistics_info=$this->getApiLogisticsInfoById($id);
            $shop_id = $logistics_info['shop_id'];
            $logistics_shop_res = M('cfg_logistics_shop')->where(array('shop_id'=>$shop_id))->select();
            if(empty($logistics_shop_res)){
                $res = array('status'=>1,'info'=>array('shop_id'=>$shop_id));
                return $res;
            }
        }catch (\PDOException $e){
            \Think\Log::write('solveSolution SQL ERR'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return;
    }
}