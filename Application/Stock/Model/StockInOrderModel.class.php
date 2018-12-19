<?php
namespace Stock\Model;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\ExcelTool;

/**
 * 入库单管理模型类
 * @package Stock\Model
 */
class StockInOrderModel extends Model{
	protected $tableName = 'stockin_order';
	protected $pk = 'stockin_id';

	 protected $_validate = array(
        array('spec_no','require','商家编码不能为空!'),
        array('warehouse_name','require','仓库名称不能为空!'),
        array('num','checkNum','入库数量不合法!',1,'callback'),
        array('price','checkPrice','入库价不合法!',1,'callback'),
        array('spec_no,position_no,warehouse_name','checkIssetPosition','该货品已分配货位,请填写空或者填写原库存货位!',1,'callback'),
    );
    protected $patchValidate = true;
    protected function checkNum($num)
    {
        return check_regex('positive_number',$num);
    }
    protected function checkPrice($cost_price)
    {
        return check_regex('positive_number',$cost_price);
    }
    protected function checkIssetPosition($position_info)
    {
        try{
            if(!empty($position_info['position_no']))
            {
                $res = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->join('left join cfg_warehouse_position cwp on cwp.warehouse_id = ssp.warehouse_id and cwp.rec_id = ssp.position_id')->where(array('gs.spec_no'=>$position_info['spec_no'],'cw.name'=>$position_info['warehouse_name'],'cwp.position_no'=>$position_info['position_no']))->select();
                $res_or = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->where(array('gs.spec_no'=>$position_info['spec_no'],'cw.name'=>$position_info['warehouse_name']))->select();

                if(!empty($res_or) && empty($res)){
                    return false;
                }
            }

            return true;
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            return false;
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            return false;
        }
    }
	
	
	protected function checkWarehouse($warehouse_id)
	{
		try{
			return  D('Setting/Warehouse')->checkWarehouse($warehouse_id);
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-checkWarehouse-'.$e->getMessage());
			return false;
		}
	}
	/**
	 * 加载选中的入库单数据
	 * @param $id
	 * @return mixed
	 * @throws $data = array('status'=>0,'msg'=>\PDOException)|$data = array('status'=>0,'msg'=>\Exception)
     */
	function loadSelectedData($id,&$result){
		$stockin_id = intval($id);
		try{
			$point_number = get_config_value('point_number',0);
			$expect_num = "CAST(sod.expect_num AS DECIMAL(19,".$point_number.")) expect_num";
			$num = "CAST(sod.num AS DECIMAL(19,".$point_number.")) num";
//			$order_info = M('StockInOrder')->field('warehouse_id')->where(array('stockin_id'=>$id))->find();
			$sql = "SELECT so.src_order_id ,so.warehouse_id,so.stockin_id, so.src_order_no, so.stockin_no, so.status, so.src_order_type, so.remark, so.logistics_id, so.logistics_no, so.discount,so.goods_amount,so.total_price, so.other_fee, so.post_fee,"
				." gs.spec_no, gs.spec_id, gs.spec_name, gs.spec_code, gs.barcode,gs.lowest_price,gs.wholesale_price, gs.market_price, gs.member_price,gs.retail_price,"
				." gg.goods_name, gg.brand_id,"
				." gb.brand_name,"
				." cgu.name unit_name,"
				." sod.src_order_detail_id AS id, ".$num.", sod.src_price, sod.cost_price, sod.total_cost, sod.tax_price,".$expect_num.", sod.tax_amount,sod.tax tax_rate, sod.num2, sod.cost_price2, cgu.name as base_unit_id, sod.unit_id, sod.unit_ratio, CAST(IF(sod.src_price,sod.cost_price/sod.src_price,1) AS DECIMAL(19,4)) AS rebate,sod.share_post_cost, sod.share_post_total,"
				." (CASE  WHEN sod.position_id THEN sod.position_id  ELSE -so.warehouse_id END) AS position_id,"
                ." (CASE  WHEN sod.position_id THEN cwp.position_no  ELSE 'ZANCUN' END) AS position_no,"
                ." IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated"
				." FROM stockin_order AS so"
				." LEFT JOIN stockin_order_detail AS sod ON so.stockin_id = sod.stockin_id"
				." LEFT JOIN goods_spec AS gs ON sod.spec_id = gs.spec_id"
				." LEFT JOIN goods_goods AS gg ON gs.goods_id = gg.goods_id"
				." LEFT JOIN cfg_goods_unit as cgu ON cgu.rec_id = sod.base_unit_id"
				." LEFT JOIN stock_spec as ss ON ss.spec_id = sod.spec_id and ss.warehouse_id=so.warehouse_id"
				." LEFT JOIN goods_brand as gb ON gb.brand_id = gg.brand_id"
				." LEFT JOIN cfg_warehouse_position cwp ON(sod.position_id = cwp.rec_id)"
				." WHERE so.stockin_id = ".$stockin_id;
			$data = $this->query($sql);
			if((int)$data[0]['status'] != 20)
			{
				SE("入库单状态不正确");
			}

		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name."-loadSelectedData-".$msg);
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name."-loadSelectedData-".$msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}


	/**
	 * 根据条件获取入库单
	 * @param $page
	 * @param $rows
	 * @param $search
	 * @param $sort
	 * @param $order
	 * @return array|int
	 * @throws array('total' => 0, 'rows' => array())
     */
	public function loadDataByCondition($page=1, $rows=20, $search, $sort="", $order,$search_type='',$where_type=array()) {
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_limit = '';
        if(!empty($search_type) && $search_type == 'refund_sp_order'){
            $where_limit.=' AND '.'so.src_order_id =0 ';
            $search['src_order_type'] = 6;
            $search['status'] = 80;
        }
		$where_limit = $this->searchForm($search);
        $where_limit = ltrim($where_limit, ' AND ');
		$page = intval($page);
        $rows = intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$order = $sort." ".$order;//排序
		$order = addslashes($order);
        try{
			if($where_limit){
                if(!empty($search_type) && $search_type == 'refund_sp_order'){
                    $sql_not_in = $this->fetchSql(true)->alias('so')->field('so.stockin_id AS id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->where($where_limit)->where($where_type)->group('so.stockin_id')->select();
                    $total = $this->alias('so')->field('so.stockin_id AS id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->join("left join ({$sql_not_in}) so_1 on so_1.id = so.stockin_id")->where($where_limit)->where('so_1.id is null')->group('so.stockin_id')->count('distinct so.stockin_id');
                    $sql_pretreatment = $this->fetchSql()->distinct(true)->alias('so')->field('so.stockin_id as id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->join("left join ({$sql_not_in}) so_1 on so_1.id = so.stockin_id")->where($where_limit)->where(array('so_1.id'=>array('exp','is null')))->group('so.stockin_id')->order($order)->limit($limit)->select();
                }else{
                    $total = $this->alias('so')->field('so.stockin_id AS id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->where($where_limit)->count('distinct so.stockin_id');
                    $sql_pretreatment = $this->fetchSql()->distinct(true)->alias('so')->field('so.stockin_id as id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->where($where_limit)->order($order)->limit($limit)->select();
                }

            }else{
				$total = $this->alias('so')->field('so.stockin_id AS id')->count('distinct so.stockin_id');
                $sql_pretreatment = $this->fetchSql()->distinct(true)->alias('so')->field('so.stockin_id as id')->order($order)->limit($limit)->select();
            }
			$point_number = get_config_value('point_number',0);
			$goods_count = 'CAST(so.goods_count AS DECIMAL(19,'.$point_number.')) goods_count';
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('so.');
            $list = $this->distinct(true)->alias('so')->field('cw.warehouse_id,cw.name as warehouse_name,so.stockin_no, so.stockin_id AS id, so.status, so.src_order_type, so.src_order_no, he.fullname operator_id, cl.logistics_name logistics_id, so.logistics_no, so.goods_amount, '.$field_right['total_price'].', so.discount, so.post_fee, so.other_fee, so.adjust_price, '.$goods_count.', so.goods_type_count, so.adjust_num, so.remark, so.created, so.modified, so.check_time, (so.total_price+so.adjust_price) AS right_fee, (so.goods_count+so.adjust_num) AS right_num')->join('INNER JOIN ('.$sql_pretreatment.') as sp ON sp.id = so.stockin_id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id LEFT JOIN hr_employee he ON he.employee_id = so.operator_id left join cfg_warehouse cw on cw.warehouse_id = so.warehouse_id')->order($order)->select();
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return array('total' => 0, 'rows' => array());
		}
		$data = array('total' => $total, 'rows' => $list);
		return $data;
	}
	public function getStockinOrder($fields,$conditions=array())
	{
	    try {
	        $res = $this->field($fields)->where($conditions)->find();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-getStockinOrder-'.$msg);
	        E(self::PDO_ERROR);
	    }
	    return false;
	}
	public function getStockinOrderList($fields,$conditions=array())
	{
	    try {
	        $res = $this->field($fields)->where($conditions)->select();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-getStockinOrderList-'.$msg);
	        E(self::PDO_ERROR);
	    }
	    return false;
	}
    public function insertStockinOrderfForUpdate($data, $update=false, $options='')
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
            \Think\Log::write($this->name.'-insertStockinOrderfForUpdate-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    
    public function updateStockinOrder($data,$conditions)
    {
        try {
            $res_update = $this->where($conditions)->save($data);
            return $res_update;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateStockinOrder-'.$msg);
            E(self::PDO_ERROR);
        }
    
    }
	public function saveOrder($rows,$form,&$result)
	{
		try{
			$this->startTrans();
			$order_no = $this->checkSaveOrder($rows,$form,$result);
			$this->commit();
			return $order_no;
		}catch (BusinessLogicException $e){
			$this->rollback();
			SE($e->getMessage());
		}catch (\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-saveOrder-'.$msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-saveOrder-'.$msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		}
	}

	public function checkSaveOrder($rows,$form,&$result)
	{
		try {
			$operator_id = get_operator_id();
			$order_info = array(
				'warehouse_id'		=>$form['warehouse_id'],
				'src_order_type'	=>$form['src_order_type'],
				'discount'			=>$form['discount'],
				'post_fee'			=>$form['post_fee'],
				'other_fee'		=>$form['other_fee'],
				'goods_amount'		=>$form['src_price'],
				'total_price'		=>$form['total_price'],
				'logistics_id'		=>$form['logistics_id'],
				'logistics_no'		=>$form['logistics_no'],
				'operator_id'		=>$operator_id,
				'remark'			=>$form['remark'],
			);
			switch ((string)$form['src_order_type']) {
				case '1': {//purchase
					$sql = 'select FN_SYS_NO("purchase") stockin_no';
					$res = $this->query($sql);
					$order_info['src_order_no'] = $res[0]['stockin_no'];
					break;
				}
				case '6': {//OtherRefund   无原始单号
					$order_info['src_order_no'] = '';
					break;
				}
				case '3': {
					$refund_info = D('Trade/SalesRefund')->field(array('process_status','refund_id'))->where(array('refund_no'=>$form['src_order_no']))->find();
					$refund_status = (int)$refund_info['process_status'];
					$order_info['src_order_id'] = $refund_info['refund_id'];
					$order_info['src_order_no'] = $form['src_order_no'];
					//判断入库单对应的退货单状态
					if ($refund_status <> 60 && $refund_status <> 65
						&& $refund_status <> 70 && $refund_status <> 69)
					{
						SE( "入库单对应的退货单状态错误");
					}
					break;
				}
				case '11':{
					$purchase_info = D('Purchase/PurchaseOrder')->field(array('status','purchase_id'))->where(array('purchase_no'=>$form['src_order_no']))->find();
					$purchase_status = (int)$purchase_info['status'];
					$order_info['src_order_id'] = $purchase_info['purchase_id'];
					$order_info['src_order_no'] = $form['src_order_no'];
					if($purchase_status < 40 || $purchase_status > 50){
						SE( "入库单对应的采购单状态错误");
					}
					$form['src_order_type'] = 1;
					$order_info['src_order_type'] = 1;
					break;
				}
				default:
					SE("入库单类型填写错误");
					break;
			}

			$sql = 'select FN_SYS_NO("stockin") stockin_no';
			$res = $this->query($sql);
			$order_no = $res[0]['stockin_no'];

			$rules = array(
				array('warehouse_id','checkWarehouse','仓库不存在',1,'function'),
			);
			if (!$this->validate($rules)->create($form)){
				// 如果创建失败 表示验证没有通过 输出错误提示信息
				SE($this->getError());
			}

			//logic 新建入库单
			$order_info['stockin_no'] = $order_no;
			$order_info['goods_amount'] = $form['src_price'];
			$warehouse_status = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$form['warehouse_id']))->select();
			if((int)$warehouse_status[0]['type'] == 11){
				$order_info['status'] = 32;
			}else{
				$order_info['status'] = 20;
			}
			$order_id = D("Stock/StockInOrder")->add($order_info);
			$result['order_id'] = $order_id;
			//logic 插入新建入库单日志
			$log_arr = array(
				"order_type"    => 1,
				"order_id"      => $order_id,
				"operator_id"   => $operator_id,
				"operate_type"  => 11,
				"message"       => "创建入库单",
			);
			D("Stock/StockInoutLog")->add($log_arr);
			//新建入库单详情
			$detail_data = array();
			foreach ($rows['insert'] as $key => $value) {
				$value = (array)$value;
				$share_post_total = (int)$from['total_price']?$form['post_fee']*($value['total_cost']/$form['total_price']):$form['post_fee']*($value['num']/$from['total_num']);
				$share_post_cost = (int)$from['total_price']?($form['post_fee']*($value['total_cost']/$form['total_price']))/$value['num']:($form['post_fee']*($value['num']/$from['total_num']))/$value['num'];
				$detail_data[] = array(
					"stockin_id"            => $order_id,
					"src_order_type"        => $form['src_order_type'],
					"src_order_detail_id"   => $value['id'],
					"spec_id"               => $value['spec_id'],
					"expect_num"            => $value['expect_num'],        //预期入库数量（基本单位数量，显示可自动转换成辅助单位）
					"base_unit_id"          => $value['base_unit_id'],      //基本单位
					"num"                   => $value['num'],               //库存单位量
					"src_price"             => $value['src_price'],         //原价
					"cost_price"            => $value['cost_price'],        //成本价,为空时表示成本不确定
					"total_cost"            => isset($value['total_cost'])?$value['total_cost']:$value['num']*$value['cost_price'],        //'总成本num*cost_price'
					"remark"                => isset($value['remark'])?$value['remark']:"",
					'position_id'           => $value['position_id'],
					'share_post_total'       =>(int)$share_post_total,
					'share_post_cost'       =>(int)$share_post_cost,
				);
			}
			D('Stock/StockinOrderDetail')->addAll($detail_data);
			//logic 根据入库详单更新入库单
			$calc_amount = D('Stock/StockinOrderDetail')->field("COUNT(spec_id) AS spec_type_count,SUM(num) goods_count,SUM(total_cost) total_cost")->where(array('stockin_id'=>$order_id))->find();
			$amount_arr = array(
				"goods_count"       => $calc_amount['goods_count'],
				"goods_type_count"  => $calc_amount['spec_type_count'],
				"created"           => array('exp',"NOW()"),
			);
			D("Stock/StockInOrder")->where(array("stockin_id"=>$order_id))->save($amount_arr);
			return $order_no;
		}catch (BusinessLogicException $e) {
			SE($e->getMessage());
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-checkOrder-'.$msg);
			SE(self::PDO_ERROR);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-checkOrder-'.$msg);
			SE(self::PDO_ERROR);
		}
	}
	public function updateOrder($rows,$form,&$result)
	{

		try {
			$this->startTrans();
			$operator_id = get_operator_id();
			$order_fields = array(
				"sio.logistics_id",
				"sio.warehouse_id",
				"sio.logistics_no",
				"cw.name",
				"sio.post_fee",
				"sio.other_fee",
				"sio.remark",
				"sio.stockin_id",
				"sio.src_order_type"
			);
			$order_info = $this->alias('sio')->field($order_fields)->join("left join cfg_warehouse cw on cw.warehouse_id = sio.warehouse_id")->where(array('stockin_no'=>$form['stockin_no']))->find();
			$order_id = $order_info['stockin_id'];
			$rules = array(
				array('warehouse_id','checkWarehouse','仓库不存在',1,'function'),
			);
			if (!$this->validate($rules)->create($form)){
				// 如果创建失败 表示验证没有通过 输出错误提示信息
				SE($this->getError());
			}
			//更新入库单
			$update_data = array(
				"logistics_id"      => $form['logistics_id'],
				"warehouse_id"      => $form['warehouse_id'],
				"logistics_no"      => $form['logistics_no'],
				"goods_amount"      => $form['src_price'],
				"total_price"       => $form['total_price'],
				"discount"          => $form['discount'],
				"post_fee"          => $form['post_fee'],
				"other_fee"         => $form['other_fee'],
				"remark"            => $form['remark'],
				"modified"          => array('exp','NOW()'),
				"operator_id"       => $operator_id,
			);

			$this->where(array("stockin_no"=>$form['stockin_no']))->save($update_data);

			$order_log = array(
				"order_type"        => 1,
				"order_id"          => $order_id,
				"operator_id"       => $operator_id,
				"operate_type"      => 13,
				"message"           => "编辑入库单",
			);
			D("StockInoutLog")->add($order_log);

			if ($order_info['logistics_id'] != $form['logistics_id']) {
				$logistics_list = D('Setting/Logistics')->field(array('logistics_id','logistics_name'))->select();
				$logistics_dict = UtilTool::array2dict($logistics_list,'logistics_id','logistics_name');
				$old_logistics_name = empty($logistics_dict["{$order_info['logistics_id']}"])?'无':$logistics_dict["{$order_info['logistics_id']}"];
				$new_logistics_name = empty($logistics_dict["{$form['logistics_id']}"])?'无':$logistics_dict["{$form['logistics_id']}"];
				$msg['logistics_id'] = "物流公司：由[" . $old_logistics_name . "]变为[" . $new_logistics_name . "];";
			}

			if ($order_info['logistics_no'] != $form['logistics_no']) {
				$msg['logistics_no'] = "物流单号：由[" . $order_info['logistics_no'] . "]变为[" . $form['logistics_no'] . "];";
			}

			if ($order_info['post_fee'] != $form['post_fee']) {
				$msg['post_fee'] = "邮费：由[" . $order_info['post_fee'] . "]变为[" . $form['post_fee'] . "];";
			}

			if ($order_info['other_fee'] != $form['other_fee']) {
				$msg['other_fee'] = "其他费用：由[" . $order_info['other_fee'] . "]变为[" . $form['other_fee'] . "];";
			}

			if ($order_info['remark'] != $form['remark']) {
				$msg['remark'] = "评论：由[" . $order_info['remark'] . "]变为[" . $form['remark'] . "];";
			}

			if ($order_info['warehouse_id'] != $form['warehouse_id']) {
				$warehouse_list = D('Setting/Warehouse')->field(array('warehouse_id','name'))->select();
				$warehouse_dict = UtilTool::array2dict($warehouse_list,'warehouse_id','name');
				$msg['warehouse'] = "仓库：由[" . $warehouse_dict["{$order_info['warehouse_id']}"] . "]变为[" . $warehouse_dict["{$form['warehouse_id']}"] . "];";
			}
			$chang_log = implode("", $msg);
			if (!empty($chang_log)) {
				$chang_data = array(
					"order_type"	=> 1,
					"order_id" 		=> $order_id,
					"operator_id" 	=> $operator_id,
					"operate_type" 	=> 13,
					"message" 		=> $chang_log,
				);
				D("Stock/StockInoutLog")->add($chang_data);
			}

			//更显入库单详情
			D('Stock/StockinOrderDetail')->updateDetail($rows,$order_id,$result);
			//logic 根据入库详单更新入库单
			$calc_amount = D('Stock/StockinOrderDetail')->field("COUNT(spec_id) AS spec_type_count,SUM(num) goods_count,SUM(total_cost) total_cost")->where(array("stockin_id"=>$order_id))->find();

			$amount_arr = array(
				"goods_count" => $calc_amount['goods_count'],
				"goods_type_count" => $calc_amount['spec_type_count'],
			);
			D("Stock/StockInOrder")->where(array("stockin_id"=>$order_id))->save($amount_arr);

			$this->commit();
		} catch (BusinessLogicException $e) {
			$this->rollback();
			SE($e->getMessage());
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-updateOrder-'.$msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-updateOrder-'.$msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		}

	}
	//入库单提交逻辑
	public function submit($id,&$result)
	{
		try{
			$this->startTrans();
			$this->checkOrder($id,$result);
			$result['data'] = $this->field(array('stockin_id as id','status','check_time'))->where(array('stockin_id'=>$id))->find();
			$this->commit();
		}catch (BusinessLogicException $e) {
			$this->rollback();
			SE($e->getMessage());
		}catch (\PDOException $e) {
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-submit-'.$msg);
			SE(self::PDO_ERROR);
		} catch (\Exception $e) {
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-submit-'.$msg);
			SE(self::PDO_ERROR);
		}
	}
	//完整的处理逻辑,没有添加事务,可以直接调用
	public function checkOrder($id,&$result)
	{
		try {
			$operator_id 	= get_operator_id();
			$order_info		= $this->where(array('stockin_id'=>$id))->find();
			$rules = array(
				array('warehouse_id','checkWarehouse','仓库不存在',1,'function'),
			);
			if (!$this->validate($rules)->create($order_info))
			{
                SE($this->getError());
			}
			$type_map = C('stockin_type');
			$stock_message = $type_map["{$order_info['src_order_type']}"].'-'.$order_info['stockin_no'];
			switch ((string)$order_info['src_order_type'] ) {
				case '1':{//快速采购入库
					$purchase_info = D('Purchase/PurchaseOrder')->alias('po')->field(array('po.purchase_id,po.purchase_no,po.warehouse_id'))->where(array('po.purchase_no'=>$order_info['src_order_no']))->select();
					if(!empty($purchase_info)){
						$stockin_detail_info = D('Stock/StockinOrderDetail')->alias('sod')->field(array('sod.num,sod.spec_id,sod.expect_num'))->where(array('sod.stockin_id'=>$id))->select();
						$where = array('purchase_no'=>$order_info['src_order_no']);
						$status = D('Purchase/PurchaseOrder')->field(array('status,goods_arrive_count,goods_count'))->where($where)->select();
						$stockin_order_info = $this->field(array('goods_count'))->where(array('stockin_id'=>$id))->select();
						if($status[0]['status'] == 20){
							SE('该入库单对应的采购单已驳回审核，请先审核采购单或者取消该入库单');
						}
						if($status[0]['status'] == 90){
							SE('该入库单对应的采购单已完成');
						}
						$in_status = 0;
						$purchase_all_detail_info = D('Purchase/PurchaseOrderDetail')->field(array('arrive_num,num,spec_id'))->where(array('purchase_id'=>$purchase_info[0]['purchase_id']))->select();
						foreach($purchase_all_detail_info as $key=>$value){
							$stockin_info = D('Stock/StockinOrderDetail')->field(array('num'))->where(array('stockin_id'=>$id,'spec_id'=>$purchase_all_detail_info[$key]['spec_id']))->select();
							if(empty($stockin_info)){
								if($purchase_all_detail_info[$key]['arrive_num']<$purchase_all_detail_info[$key]['num']){
									$in_status = 1;
								}
							}else{
								if($purchase_all_detail_info[$key]['arrive_num']+$stockin_info[0]['num']<$purchase_all_detail_info[$key]['num']){
									$in_status = 1;
								}
							}
						}
						if($in_status){
							D('Purchase/PurchaseOrder')->where($where)->save(array('status'=>50,'goods_arrive_count'=>$stockin_order_info[0]['goods_count']+$status[0]['goods_arrive_count']));
																				
							$data_log = array(
								'purchase_id'=>$purchase_info[0]['purchase_id'],
								'operator_id'=>$operator_id,
								'type'=>1,
								'remark'=>'采购单部分到货入库',
								'created'=>array('exp','NOW()')
							);
						}else{
							D('Purchase/PurchaseOrder')->where($where)->save(array('status'=>90,'goods_arrive_count'=>$stockin_order_info[0]['goods_count']+$status[0]['goods_arrive_count']));
																			
							$data_log = array(
								'purchase_id'=>$purchase_info[0]['purchase_id'],
								'operator_id'=>$operator_id,
								'type'=>1,
								'remark'=>'采购单全部到货入库',
								'created'=>array('exp','NOW()')
							);
						}
						D('Purchase/PurchaseOrderLog')->add($data_log);
						foreach($stockin_detail_info as $key=>$value){
							$in_where = array('purchase_id'=>$purchase_info[0]['purchase_id'],'spec_id'=>$stockin_detail_info[$key]['spec_id']);
							$purchase_detail_info = D('Purchase/PurchaseOrderDetail')->field('arrive_num')->where($in_where)->select();
							D('Purchase/PurchaseOrderDetail')->where($in_where)->save(array('arrive_num'=>$purchase_detail_info[0]['arrive_num']+$stockin_detail_info[$key]['num']));
							$stockspec = D('StockSpec')->field('purchase_arrive_num,purchase_num')->where(array('spec_id'=>$stockin_detail_info[$key]['spec_id'],'warehouse_id'=>$purchase_info[0]['warehouse_id']))->select();
							$info = array(
								'purchase_num'=>$stockspec[0]['purchase_num']-$stockin_detail_info[$key]['num']>0?$stockspec[0]['purchase_num']-$stockin_detail_info[$key]['num']:0,
								'purchase_arrive_num'=>$stockspec[0]['purchase_arrive_num']+$stockin_detail_info[$key]['num']
							);
							D('Stock/StockSpec')->where(array('spec_id'=>$stockin_detail_info[$key]['spec_id'],'warehouse_id'=>$purchase_info[0]['warehouse_id']))->save($info);
						}
						break;
					}
					$purchase_no = $order_info['src_order_no'];
					//-----------------获取物流类型
					$logistics_fields = array('logistics_type');
					$logistics_info= D('Setting/Logistics')->field($logistics_fields)->where(array('logistics_id'=>$order_info['logistics_id']))->find();
					if($order_info['logistics_id']!=0 && !empty($order_info['logistics_id']))
					{
						if(!empty($logistics_info))
						{
							$logistics_type = $logistics_info['logistics_type'];
						}else{
                            SE('未查询到物流信息!');
                        }
					}else{
						$logistics_type = '';
					}
					$purchase_data = array(
						'purchase_no'       =>	$purchase_no,
						'creator_id'        =>	$operator_id,
						'purchaser_id'      =>  0,
						'status'            =>	'90',
						'provider_id'       =>	0,
						'warehouse_id'      =>  $order_info['warehouse_id'],
						'logistics_type'    =>	$logistics_type,
						'expect_arrive_time'=>	array('exp','NOW()'),
						'other_fee'         =>	$order_info['other_fee'],
						'post_fee'          =>	$order_info['post_fee'],
						'remark'            =>	'',
						'flag_id'           =>	0,
						'created'           =>	array('exp','NOW()')
					);
					$purchase_id = D('Purchase/PurchaseOrder')->insertPurchaseOrderForUpdate($purchase_data);
					//---------------插入采购日志
					$purch_log = array(
						'purchase_id'	=> $purchase_id,
						'operator_id'	=> $operator_id,
						'type'       	=> 0,
						'remark'     	=> '快速采购入库创建采购单---'.$purchase_no
					);
					$purch_log_res = D('Purchase/PurchaseOrderLog')->insertPurchaseOrderLogForUpdate($purch_log);
					$order_detail_field = array(
						"{$purchase_id} as purchase_id",
						'spec_id',
						"{$order_info['warehouse_id']} as warehouse_id",
						'0 AS tag',
						'SUM(num) as num',
						'SUM(num) AS arrive_num',
						'base_unit_id',
						'unit_id',
						'1 AS unit_ratio',
						'src_price AS price',
						'IF(src_price=0,0,src_price*num) AS amount',
						'IF(src_price=0,1,cost_price/src_price) as discount',
						'CONCAT_WS(remark,"由入库明细引入采购明细") AS remark',
						'NOW() AS created'
					);
					$order_detail_conditions = array(
						'stockin_id'=>$id
					);
					$order_detail_res = D('Stock/StockinOrderDetail')->getStockinOrderDetailList($order_detail_field,$order_detail_conditions,'rec_id');
					$insert_purch_detail_res = D('Purchase/PurchaseOrderDetail')->insertPurchaseOrderDetailForUpdate($order_detail_res);
					//------------更新采购单信息 统计货品信息
					$amount_purch_fields = array(
						'COUNT(spec_id) as goods_type_count',
						'SUM(num) as goods_count',
						'SUM(arrive_num) as goods_arrive_count',
						'SUM(tax_amount) as tax_fee',
						'SUM(price*num) as goods_fee'
					);
					$amount_purch_conditons = array(
						'purchase_id'=>$purchase_id
					);
					$amount_purch_res = D('Purchase/PurchaseOrderDetail')->field($amount_purch_fields)->where($amount_purch_conditons)->find();
					$update_purch_conditions = array(
						'purchase_id'  =>$purchase_id
					);
					$update_purchase_order_res = D('Purchase/PurchaseOrder')->updatePurchaseOrder($amount_purch_res,$update_purch_conditions);
					//---------------更新入库单详情中来源单id
					$update_stockin_order_detail_res = $this->execute("UPDATE stockin_order_detail sod,purchase_order_detail pod SET sod.src_order_detail_id = pod.rec_id WHERE sod.stockin_id = '%d' AND sod.spec_id = pod.spec_id AND pod.purchase_id = '%d';",$id,$purchase_id);
					//---------------更新采购单详情日志
					$purch_log_fields = array(
						"{$purchase_id} as purchase_id",
						"{$operator_id} as operator_id",
						'5 as type',
						"CONCAT('添加单品----商家编码----',spec_no) as remark"
					);
					$purch_log_conditions = array(
						'pod.purchase_id'=>$purchase_id,
					);
					$purch_log_res = D('Purchase/PurchaseOrderDetail')->getPurchaseOrderDetailLogInfoLeftGoodsSpec($purch_log_fields,$purch_log_conditions);
					$insert_purch_log_res = D('Purchase/PurchaseOrderLog')->insertPurchaseOrderLogForUpdate($purch_log_res);

					break;
				}
				case '2'://调拨
				{
					$from_warehouse_res = D('Stock/StockTransfer')->field(array('from_warehouse_id'))->where(array('rec_id'=>$order_info['src_order_id']))->find();
					$from_warehouse_id = $from_warehouse_res['from_warehouse_id'];
					//--------------更新调拨单详情中   注:货位没有设置,统一设置为0
					$order_detail_info = D('Stock/StockinOrderDetail')->getStockinOrderDetailList(array('src_order_detail_id as rec_id',"{$order_info['src_order_id']} as transfer_id",'num','0 as from_position','0 as to_position','num as in_num' ),array('stockin_id'=>$order_info['stockin_id']));
					$update_transfer_detail_dup = array('in_num'=>array('exp','in_num+VALUES(in_num)'));
					$update_transfer_detail_res = D('Stock/StockTransferDetail')->insert($order_detail_info,$update_transfer_detail_dup);
					//--------------更新带调拨量:没做
					//---------------更新调拨单总入库数量
					$tansfer_goods_count = D('Stock/StockTransferDetail')->getDetailInfo(array('SUM(in_num) as goods_in_count'),array('transfer_id'=>$order_info['src_order_id']));
					$update_transfer = D('Stock/StockTransfer')->where(array('rec_id'=>$order_info['src_order_id']))->save($tansfer_goods_count[0]);
					break;
				}
				case '3'://退款入库
				{
					$refund_field = "type,swap_trade_id,refund_id,process_status,trade_id";//is_trade_charged
					$refund_info = D("Trade/SalesRefund")->where("refund_no = '{$order_info['src_order_no']}'")->field($refund_field)->find();

					if($refund_info['process_status']<>60&&$refund_info['process_status']<>65&&$refund_info['process_status']<>70&&$refund_info['process_status']<>69){
                        SE('入库单对应的退货单状态错误');
                    }
					$refund_id 		= $refund_info['refund_id'];
					$refund_status 	= $refund_info['process_status'];

					//-------------------更新退换详单 入库单详单信息：入库数量 入库总价
					$this->execute("UPDATE sales_refund_order sro,stockin_order_detail sod SET sro.stockin_num=sro.stockin_num+sod.num,sro.stockin_amount = sro.stockin_amount + sod.num *sod.cost_price WHERE sod.src_order_detail_id = sro.refund_order_id AND sod.stockin_id=%d", $id);

					//--------------------更新退货入库单的状态
					$refund_detail_info = D('Trade/SalesRefundOrder')->field("SUM(IF(refund_num-stockin_num<0,0,refund_num-stockin_num)) count,trade_id")->where(array("refund_id"=> $refund_id))->find();
					$count = (int)$refund_detail_info['count'];

					if ($count > 0) {
						if ($refund_status == 69 || $refund_status == 71) {
							D('Trade/SalesRefund')->where(array('refund_no'=>$order_info['src_order_no']))->save(array('process_status'=>71));
						} else {
							D('Trade/SalesRefund')->where(array('refund_no'=>$order_info['src_order_no']))->save(array('process_status'=>70));
						}
					} else {
						if ($refund_status == 69 || $refund_status == 71) {
							D('Trade/SalesRefund')->where(array('refund_no'=>$order_info['src_order_no']))->save(array('process_status'=>90));
						} else {
							D('Trade/SalesRefund')->where(array('refund_no'=>$order_info['src_order_no']))->save(array('process_status'=>90));
						}
					}
					$cfg_open_message_strategy = get_config_value('cfg_open_message_strategy',0);
					if($cfg_open_message_strategy){
						UtilTool::crm_sms_record_insert('2',$refund_info['trade_id']);
					}
					//---------`type`  '操作类型: 1创建退款单2同意3拒绝4取消退款 5平台同意6平台取消7平台拒绝8停止等待9驳回',
					$sales_refund_data = array("refund_id" => $refund_id, "type" => 10, "operator_id" => $operator_id, "remark" => array('exp',"IF({$count}>0,'退换入库部分到货','退换入库全部到货')"));
					D("Trade/SalesRefundLog")->add($sales_refund_data);
					break;
				}
				case '4'://盘点
				{
					$this->execute("UPDATE stock_spec ss, stockin_order_detail sod SET ss.last_pd_time=NOW() WHERE ss.spec_id=sod.spec_id AND ss.warehouse_id={$order_info['warehouse_id']} AND sod.src_order_type=4 AND sod.stockin_id={$order_info['stockin_id']};");

					$pd_sid_info = D('Stock/StockinOrderDetail')->alias('sod')->field("IF(ssp.position_id IS NULL,-{$order_info['warehouse_id']},ssp.position_id) as position_id,sod.spec_id,NOW() as last_pd_time,{$order_info['warehouse_id']} as warehouse_id")->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$order_info['warehouse_id'])->where(array('sod.stockin_id'=>$order_info['stockin_id']))->select();
					$for_update_ssp = array('last_pd_time'=>array('exp','VALUES(last_pd_time)'));
					D('Stock/StockSpecPosition')->addAll($pd_sid_info,array(),$for_update_ssp);
					$update_status = array(
						'status'=>80
					);

					$this->where(array('stockin_id'=>$order_info['stockin_id']))->save($update_status);
					break;
				}
				
			}


			/*	先根据入库单id找到stockin_order_detail中的$new_num和$new_price
			 *	再从stock_spec中根据spec_id查出$old_num和$old_price
			*	最后调用costPriceCalc得出最后的num和cost_price插入到stock_spec中*/

			$tmp_arr = D('Stock/StockinOrderDetail')->fetchSql(false)->alias('sod')->where(array('sod.stockin_id'=>$id,))->field(array('sod.remark','sod.position_id','gs.spec_no','sod.num','cwz.zone_id','sod.cost_price','sod.spec_id','sod.position_id',"{$order_info['warehouse_id']} as warehouse_id",'sod.share_post_cost'))->join('left join cfg_warehouse_position cwp on cwp.rec_id = sod.position_id')->join('goods_spec gs on gs.spec_id = sod.spec_id')->join('left join cfg_warehouse_zone cwz on cwz.zone_id = cwp.zone_id')->select();
            foreach($tmp_arr as $stockin_detail){
                $is_allocated_before = D('Stock/StockSpec')->checkIsAllocatedPosition($order_info['warehouse_id'],$stockin_detail['spec_id'],$stockin_detail['position_id']);
                if($is_allocated_before){
                    SE($stockin_detail['spec_no'].'-已被分配了其他货位,重新添加该货品后再提交');
                }
            }
			$share_post_fee = 0;
			$share_post_fee_cfg = get_config_value('share_post_fee_to_cost_price',0);
			if($share_post_fee_cfg)
			{
				$tmp =$this->where(array('stockin_id'=>$id,))->field(array('warehouse_id,post_fee,other_fee,goods_count'))->find();
				$tmp['goods_count'] = empty($tmp['goods_count'])?1:$tmp['goods_count'];
				$share_post_fee = ($tmp['post_fee']+$tmp['other_fee'])/$tmp['goods_count'];
			}
			$stockin_change_history =array();
			for($i=0;$i<count($tmp_arr);$i++)
			{

				//------------------查询当前库存量
				$stock_spec_info = D('Stock/StockSpec')->field(array('rec_id', 'stock_num', 'neg_stockout_num', 'cost_price', 'stock_diff', 'default_position_id'))->where(array('warehouse_id'=>$order_info['warehouse_id'],'spec_id'=>$tmp_arr[$i]['spec_id']))->find();
				$tmp_arr[$i]['cost_price'] = $tmp_arr[$i]['cost_price']+$tmp_arr[$i]['share_post_cost']+$share_post_fee;
				if(!empty($stock_spec_info)){
					$new_arr = $this->costPriceCalc($stock_spec_info['stock_num'],$stock_spec_info['cost_price'],$tmp_arr[$i]['num'],$tmp_arr[$i]['cost_price']);
					M('stock_spec')->where(array('spec_id'=>$tmp_arr[$i]['spec_id'],'warehouse_id'=>$tmp_arr[$i]['warehouse_id']))->save(array('stock_num'=>$new_arr['stock_num'],'cost_price'=>$new_arr['cost_price'],'last_position_id'=>$tmp_arr[$i]['position_id']));
				}else{
					$new_arr = array('stock_num' =>$tmp_arr[$i]['num']);
					$stock_spec_id = M('stock_spec')->add(array('warehouse_id'=>$tmp_arr[$i]['warehouse_id'],'spec_id'=>$tmp_arr[$i]['spec_id'],'status'=>1,'stock_num'=>$tmp_arr[$i]['num'],'cost_price'=>$tmp_arr[$i]['cost_price'],'last_position_id'=>$tmp_arr[$i]['position_id']));
				}
                D('Stock/StockSpecLog')->add(array('operator_type'=>2,'operator_id'=>$operator_id,'stock_spec_id'=>empty($stock_spec_info['rec_id'])?$stock_spec_id:$stock_spec_info['rec_id'],'message'=>$stock_message,'stock_num'=>empty($stock_spec_info['stock_num'])?0:$stock_spec_info['stock_num'],'num'=>$tmp_arr[$i]['num']));

                //----------------更新stock_spec_position信息
				$ssp_position = M('stock_spec_position')->fetchSql(false)->field('position_id')->where(array('warehouse_id'=>array('eq',$tmp_arr[$i]['warehouse_id']),'spec_id'=>array('eq',$tmp_arr[$i]['spec_id'])))->find();
				$ssp_position_id  = $tmp_arr[$i]['position_id'];
				if(!empty($ssp_position)){
					$ssp_position_id = $ssp_position['position_id'];
				}
				$ssp_data = array(
					'warehouse_id'     => $tmp_arr[$i]['warehouse_id'],
					'spec_id'          => $tmp_arr[$i]['spec_id'],
					'position_id'      => $ssp_position_id,
					'last_position_id' => $ssp_position_id,
					'zone_id'          => $tmp_arr[$i]['zone_id'],
					'stock_num'        => $new_arr['stock_num'],
					'last_inout_time'  => array('exp', 'NOW()'),
					'created'          => array('exp', 'NOW()')
				);
				$ssp_update_data = array(
					'position_id'      => $tmp_arr[$i]['position_id'],
					'stock_num'       => array('exp', 'VALUES(stock_num)'),
					'last_inout_time' => array('exp', 'NOW()'),
				);
				$res_ssp = D('Stock/StockSpecPosition')->add($ssp_data,'',$ssp_update_data);
				//$res_ss_u = D('Stock/StockSpec')->add($ssp_data,'',array('last_position_id'=>array('exp','VALUES(last_position_id)')));
				$res_ss_u = D('Stock/StockSpec')->add($ssp_data,'',array('last_position_id'=>$tmp_arr[$i]['position_id']));

				$stockin_change_history[] = array(
					'src_order_type' => $order_info['src_order_type'],
					'stockio_id'   => $id,
					'stockio_no'   =>$order_info['stockin_no'],
					'src_order_id' => empty($order_info['src_order_id'])?0:$order_info['src_order_id'],
					'src_order_no' =>empty($order_info['src_order_no'])?"":$order_info['src_order_no'],
					'stockio_detail_id' =>empty($tmp_arr[$i]['src_order_detail_id'])?0:$tmp_arr[$i]['src_order_detail_id'],
					'spec_id'  =>$tmp_arr[$i]['spec_id'],
					'warehouse_id' =>$tmp_arr[$i]['warehouse_id'],
					'type' =>1,
					'cost_price_old' =>empty($stock_spec_info)?0:$stock_spec_info['cost_price'],
					'stock_num_old' =>empty($stock_spec_info)?0:$stock_spec_info['stock_num'],
					'price' =>$tmp_arr[$i]['cost_price'],
					'num' =>$tmp_arr[$i]['num'],
					'amount' =>$tmp_arr[$i]['cost_price']*$tmp_arr[$i]['num'],
					'cost_price_new' =>empty($new_arr['cost_price'])?0:$new_arr['cost_price'],
					'stock_num_new' =>$new_arr['stock_num'],
					'operator_id' =>$operator_id,
					'created' =>array('exp', 'NOW()'),
					
				);
				
			}
			D('Stock/StockChangeHistory')->addAll($stockin_change_history);
			//--------------------更新入库单审核时间
			$update_order_data = array(
				"status"	=>80,
				"check_time"=>array('exp',"NOW()"),
			);
			$this->where(array('stockin_id'=>$id))->save($update_order_data);

			D('Common/SysProcessBackground')->stockinChange($id);
			
			
			//----------------完成日志更新
			$finish_log_data = array(
				"order_type"   => 1,
				"order_id"     => $id,
				"operator_id"  => $operator_id,
				"operate_type" => 17,
				"message"      => "完成入库单",
			);
			D('Stock/StockInoutLog')->add($finish_log_data);

		}catch (BusinessLogicException $e) {
			SE($e->getMessage());
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-checkOrder-'.$msg);
			SE(self::PDO_ERROR);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-checkOrder-'.$msg);
			SE(self::PDO_ERROR);
		}
	}
	public function costPriceCalc($old_num, $old_price, $new_num, $new_price)
	{
		$arr = array();
		$old_price = (float)$old_price;
		$new_price = (float)$new_price;
		if ($old_num >= 0) {
			//没有负库存
			if($old_num+$new_num >0){
				$arr['stock_num'] = $old_num+$new_num;
				//加权平均
				$arr['cost_price'] = ($old_num*$old_price+$new_num*$new_price)/($old_num+$new_num);
			}else{
				//之前库存为0，入库数量也是0，特殊处理，否则会除0异常
				$arr['stock_num'] = 0;
				$arr['cost_price'] = $new_price;
			}
		}else{
			//有负库存
			if(abs($old_num)>=$new_num){
				//如果负库存量大于 该次入库数量
				$arr['stock_num'] = $old_num+$new_num;
				//cost_price等于原来的cost_price
				$arr['cost_price'] = $old_price;
			}else{
				//如果负库存量 小于 该次入库数量
				$arr['stock_num'] = $old_num+$new_num;
				//cost_price等于新的的cost_price
				$arr['cost_price'] = $new_price;
			}
		}
		return $arr;
	}
	/*
	 * 一键退换入库接口
	 * @param $refund_id  退换单id
	 * @param $stockin_id 回写入库id
	 */
	public function refundStockIn($refund_id,&$stockin_id=''){
		try{
			$id = intval($refund_id);
			$auth = array();
			D('Setting/EmployeeRights')->setSearchRights($auth,'shop_id',1);
			D('Setting/EmployeeRights')->setSearchRights($auth,'warehouse_id',2);
			$auth['shop_id'] = ','.$auth['shop_id'].',';
			$auth['warehouse_id'] = ','.$auth['warehouse_id'].',';
			$refund_info = D('Trade/SalesRefund')->field('refund_id,process_status,type,shop_id,warehouse_id')->where(array('refund_id'=>array('eq',$id)))->find();
			if(strpos($auth['shop_id'],','.$refund_info['shop_id'].',')===false){
				SE('该店铺没有操作权限');
			}
			if(strpos($auth['warehouse_id'],','.$refund_info['warehouse_id'].',')===false){
				SE('该仓库没有操作权限');
			}
			if($refund_info['type'] != 2 && $refund_info['type'] != 3){
				SE('一键退换入库只能操作退货和换货的退换单');
			}
			if($refund_info['process_status'] != 60 && $refund_info['process_status'] != 70){
				SE('当前退换单的处理状态不正确');
			}

			//选的退换单的货品信息
			$data=D('Trade/RefundManage')->getSalesRefundInfo($id);
			//保存之前处理数据
			$result = array('status'=>0,'info'=>'成功','data'=>array());
			$rows = array();
			$form = array();
			$form = $data['form'];
			$rows['insert'] = $data['data']['rows'];
			for($i=0; $i<count($rows['insert']); $i++){
				$rows['insert'][$i]['num'] = $rows['insert'][$i]['expect_num'];
				$rows['insert'][$i]['total_cost'] = $rows['insert'][$i]['num']*$rows['insert'][$i]['cost_price'].'';
				$form['src_price'] +=  $rows['insert'][$i]['num']*$rows['insert'][$i]['src_price'];
				$form['total_price'] += $rows['insert'][$i]['total_cost'];
			}
			$form['total_price'] .= '';
			$form['src_price'] .= '';
			$form['src_order_type'] = '3';
			$form['discount'] = $form['src_price'] - $form['total_price'].'';

			$refund_data['refund']=D('Trade/RefundManage')->getSalesRefund(
				'r.stockin_pre_no,r.remark,r.post_amount,r.other_amount,r.logistics_name',
				array('r.refund_id'=>array('eq',$id)),
				'r'
			);
			//$logistics_id = M('ApiLogisticsShop')->field('logistics_id')->where(array('name'=>array('eq',$refund_data['refund']['logistics_name'])))->find();
			$form['post_fee'] = $refund_data['refund']['post_amount'];
			$form['other_fee'] = $refund_data['refund']['other_amount'];
			$form['stockin_no'] = '';
			$form['provider'] = '';
			$form['logistics_id'] = '0';
			$form['remark'] = $refund_data['refund']['remark'];
			//新建入库单
			$stockin_no = $this->checkSaveOrder($rows,$form,$result);
			//提交
			$stockin_id = $this->field('stockin_id')->where(array('stockin_no'=>array('eq',$stockin_no)))->find();
			$stockin_id = $stockin_id['stockin_id'];
			$this->checkOrder($stockin_id,$result);
		}catch (BusinessLogicException $e) {
			SE($e->getMessage());
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-refundStockIn-'.$msg);
			SE(self::PDO_ERROR);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-refundStockIn-'.$msg);
			SE(self::PDO_ERROR);
		}
	}
	
	 public function upload($data,&$result)
    {

            try{
                $operator_id = get_operator_id();
                \Think\Log::write('导入数据提示:'.print_r($data,true),\Think\Log::DEBUG);
                $check_fields_ar = array('num','price','spec_no','warehouse_name','position_no,warehouse_name','spec_no,position_no,warehouse_name');
                foreach ($data as $import_index => $import_rows)
                {
                    if (!$this->create($import_rows)) {
                        // 如果创建失败 表示验证没有通过 输出错误提示信息
                        $data[$import_index]['status'] = 1;
                        $data[$import_index]['result'] = '失败';
                        foreach ($check_fields_ar as $check_field)
                        {
                            $temp_error_ar = $this->getError();
                            if(isset($temp_error_ar["{$check_field}"]))
                            {
                                $data[$import_index]['message'] = $temp_error_ar["{$check_field}"];
                                break;
                            }
                            
                        }
                        
                    }
                }
                \Think\Log::write('导入数据验证:'.print_r($data,true),\Think\Log::DEBUG);
                //查入临时表信息包括验证完的信息
                foreach($data as $item)
                {
					$res_insert_temp_import_detail = $this->execute("insert into tmp_import_detail(`spec_no`,`position_no`,`num`,`warehouse_name`,`price`,`status`,`result`,`message`,`line`) values('{$item['spec_no']}','{$item['position_no']}',".(float)$item['num'].",'{$item['warehouse_name']}',".(float)$item['price'].",".(int)$item['status'].",'".$item['result']."','{$item['message']}',".(int)$item['line'].")");
				}
                //校验是否存在相应的仓库和商家编码
                $query_warehouse_id_list_sql = "SELECT * FROM tmp_import_detail " ;
                $res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
                \Think\Log::write('导入临时表的数据:'.print_r($res_query_warehouse_id_list,true),\Think\Log::DEBUG);
                $query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name"
                             ."  SET tid.warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'仓库不存在','') "
                             ."  WHERE tid.status=0 ;";
                $res_query_warehouse_isset = $this->execute($query_warehouse_isset_sql);
                $search = array();
				D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
				$right_warehouse_id = $search['warehouse_id'];
				if(empty($right_warehouse_id)) SE('没有新建仓库，请到仓库界面新建仓库');
				$right_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.warehouse_id"
							 ." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'仓库不存在','') "
							 ." where tid.status = 0;";
				$right_warehouse = $this->execute($right_warehouse_sql);
				
				$query_goods_spec_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
                                              ." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.spec_id IS NULL,1,0),tid.result= IF(gs.spec_id IS NULL,'失败',''),tid.message=IF(gs.spec_id IS NULL,'商家编码不存在','')"
                                              ." WHERE tid.status=0;";
                $res_query_goods_spec_isset = $this->execute($query_goods_spec_isset_sql);

				//判断货品是否删除
				$query_goods_spec_isdel_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
                                              ." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.deleted<>0,1,0),tid.result= IF(gs.deleted<>0,'失败',''),tid.message=IF(gs.deleted<>0,'商家编码不存在(该货品已删除)','')"
                                              ." WHERE tid.status=0;";
                $this->execute($query_goods_spec_isdel_sql);
				
				$info = $this->query("select rec_id,line, spec_no,position_no,warehouse_name from tmp_import_detail where status = 0");
				foreach($info as $value){
					if(!$value['position_no'] == ''){					
						$res_or = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->where(array('gs.spec_no'=>$value['spec_no'],'cw.name'=>$value['warehouse_name']))->select();
						if(empty($res_or)){
							$position_sql = "select tid.position_no,cwp.rec_id,sw.warehouse_id from tmp_import_detail tid left join cfg_warehouse sw on tid.warehouse_name = sw.name left join cfg_warehouse_position cwp on cwp.warehouse_id = sw.warehouse_id and cwp.position_no = tid.position_no where tid.position_no = '".$value['position_no']."' and tid.warehouse_name = '".$value['warehouse_name']."'";
							$position = $this->query($position_sql);
							if(!empty($position[0]['position_no']) && empty($position[0]['rec_id'])){
								$updata_data = array();
								$updata_data['warehouse_id'] = $position[0]['warehouse_id'];
								$updata_data['position_no'] = $position[0]['position_no'];
								$updata_data['id'] = 0;
								$updata_data['is_disabled'] = 0;
								$result = D('Setting/warehousePosition')->savePosition($updata_data);
							}
						}
					}					
				}
				
				$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name LEFT JOIN cfg_warehouse_position cwp ON cwp.position_no=tid.position_no and cwp.warehouse_id=sw.warehouse_id"
                                              ." SET tid.position_id=IF(tid.position_no = '',-tid.warehouse_id,cwp.rec_id),tid.status=IF(cwp.rec_id IS NULL AND tid.position_no <> '',1,0),tid.result= IF(cwp.rec_id IS NULL AND tid.position_no <> '','失败',''),tid.message=IF(cwp.rec_id IS NULL AND tid.position_no <> '','货位不存在','')"
                                              ." WHERE tid.status=0;";
                $res_query_position_no_isset = $this->execute($query_position_no_isset_sql);
				
				$query_warehouse_id_list_sql = "SELECT warehouse_id FROM tmp_import_detail WHERE STATUS=0 GROUP BY warehouse_id" ;
                $res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
                if(empty($res_query_warehouse_id_list))
                {
                    $deal_import_data_result = $this->query("select line as id,message,status,result,spec_no from tmp_import_detail where status =1");
                    \Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);

                    if(!empty($deal_import_data_result)){
                        $result['status'] = 2;
                        $result['data'] = $deal_import_data_result;
                    }

                    return;
                }
            }catch (\PDOException $e) {
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--入库导入--'.$msg);
                 $result['status'] = 1;
                $result['msg'] = $msg;
				return;
				
                
            } catch (\Exception $e){
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--入库导入--'.$msg);
                $result['status'] = 1;
                $result['msg'] = $msg;
                return;
            }
			\Think\Log::write(print_r($res_query_warehouse_id_list,true),\Think\Log::INFO);
			 foreach($res_query_warehouse_id_list as $warehouse_key => $warehouse_info)
            {      
				$warehouse_id = $warehouse_info['warehouse_id'];	
				  try {
					$this->startTrans();
					$query_stockin_no_fields = array("FN_SYS_NO('stockin') as stockin_no");
					$res_query_stockin_no = $this->query("select FN_SYS_NO('stockin') as stockin_no");
					$res_query_stockin_no = $res_query_stockin_no[0];
					$test_str_in = 'insert stockin order';
					$insert_stockin_order_data = array(
						'stockin_no' => $res_query_stockin_no['stockin_no'],
						'warehouse_id' => $warehouse_id,
						'src_order_type' => 6,
						'status'  => 20,
						'operator_id' =>$operator_id,
						'created'=>array('exp','NOW()')
					);
					$res_insert_stockin_order = $this->insertStockinOrderfForUpdate($insert_stockin_order_data);
					$test_str_in = 'query stockin order detail';
					$query_stockin_order_detail_sql = "SELECT {$res_insert_stockin_order} as stockin_id,6 as src_order_type,tid.spec_id,tid.position_id,IF(LENGTH(tid.price),tid.price,0) as src_price,IF(LENGTH(tid.price),tid.price,0) as cost_price,tid.num,'' as remark,NOW() as created"
													 ." FROM tmp_import_detail tid"
													 ." WHERE tid.status=0  AND tid.warehouse_id={$warehouse_id};";
					$res_query_stockin_order_detail = $this->query($query_stockin_order_detail_sql);
					if(empty($res_query_stockin_order_detail))
					{
						E('未知错误,请联系管理员!');
					}
					$res_insert_stockin_order_detail = D('Stock/StockinOrderDetail')->insertStockinOrderDetailfForUpdate($res_query_stockin_order_detail);
					$query_statistics_num_fields = array(
						'COUNT(spec_id) as goods_type_count' ,
						'SUM(num) as goods_count',
						'SUM(cost_price*num) as goods_amount',
						'SUM(cost_price*num) as total_price'
					);
					$query_statistics_num_cond = array(
						'stockin_id'   => $res_insert_stockin_order
					);
					$res_query_statistics_num = D('Stock/StockinOrderDetail')->getStockinOrderDetailList($query_statistics_num_fields,$query_statistics_num_cond);
					$res_query_statistics_num = $res_query_statistics_num[0];
					$update_stockin_order_num_cond = array(
						'stockin_id' => $res_insert_stockin_order
					);
					$res_update_stockin_order_num = D('Stock/StockInOrder')->updateStockinOrder($res_query_statistics_num,$update_stockin_order_num_cond);
					$test_str_in = 'insert stockin order log';
					$insert_stock_inout_log_data = array(
						'order_type'=>1,
						'order_id'=>$res_insert_stockin_order,
						'operator_id'=>$operator_id,
						'operate_type'=>11,
						'message'=>'其他入库',
						'created'=>array('exp','NOW()'),
						'data'=>0
					);
					$res_insert_stock_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_stock_inout_log_data);
					$test_str_in = 'insert stockin order submit';
					
					$check_stockin_result = $this->checkOrder($res_insert_stockin_order,$result);
					$this->commit();
			   
					
				} catch (\PDOException $e) {
					$msg = $e->getMessage();
					\Think\Log::write($this->name.'--入库导入--'.$test_str_in."--入库导入--".$msg);
					$this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num>tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
					$this->rollback();
					$result['status'] = 1;
					$result['msg'] = $msg;
					$result['data'] = array();
				} catch (\Exception $e) {
					$msg = $e->getMessage();
					$this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num>tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
					$this->rollback();
					$result['status'] = 1;
					$result['msg'] = $msg;
					$result['data'] = array();
					\Think\Log::write($this->name.'--入库导入--'.$test_str_in."--入库导入--".$msg);
				}
			}
            try {
                $deal_import_data_result = $this->query("select line as id,message,status,result from tmp_import_detail where status =1");
                \Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);
                if(!empty($deal_import_data_result)){
                    $result['status'] = 2;
                    $result['data'] = $deal_import_data_result;
                }
             } catch (\PDOException $e) {
                $msg = $e->getMessage();
                $result['status'] = 1;
                $result['msg'] = '导入信息获取失败,联系管理员';
                $result['data'] = array();
                \Think\Log::write($this->name.'--入库导入--'.$msg);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $result['status'] = 1;
                $result['msg'] = '导入信息获取失败,联系管理员';
                $result['data'] = array();
                \Think\Log::write($this->name.'--入库导入--'.$msg);
            }
    }
    public function exportToExcel($id_list,$search,$type = 'excel'){
        try {
            D('Setting/EmployeeRights')->setSearchRights($search, 'warehouse_id', 2);

            $creator=session('account');
            $where_limit = $this->searchForm($search);
            $where_limit = ltrim($where_limit, ' AND ');

            if(empty($id_list)){
                $select_ids_sql = '';
            }else{
                $select_ids_sql = " where so.stockin_id in (".$id_list.") ";
            }

            $point_number = get_config_value('point_number',0);
            $num = "CAST(siod.num AS DECIMAL(19,".$point_number.")) num,";
            $sql_pretreatment = $this->fetchSql()->distinct(true)->alias('so')->field('so.stockin_id as id')->join('LEFT JOIN stockin_order_detail siod ON  siod.stockin_id = so.stockin_id LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->where($where_limit)->select();

            $sql = " select so.stockin_id AS id,so.warehouse_id ,so.stockin_no, so.status,so.remark,"
                . " so.modified,so.operator_id, so.src_order_type mode,so.created, "
                . " he.fullname creator_name,cgu.name AS base_unit_id, gb.brand_name AS brand_id,siod.cost_price, ".$num
                . " cw.name warehouse_name,cwp.position_no,"
                . " gs.spec_no,gs.spec_code,gs.barcode,gs.spec_name,gg.goods_no,gg.goods_name"
                . " FROM stockin_order so"
                . " LEFT JOIN stockin_order_detail siod ON so.stockin_id = siod.stockin_id "
                . " LEFT JOIN goods_spec gs ON gs.spec_id = siod.spec_id "
                . " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id "
                . " LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id "
                . " LEFT JOIN cfg_goods_unit cgu ON siod.base_unit_id = cgu.rec_id "
                . " LEFT JOIN hr_employee he ON he.employee_id = so.operator_id"
                . " LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id"
                . " LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = siod.position_id and cwp.warehouse_id = so.warehouse_id"
                . " JOIN (" . $sql_pretreatment . ") page ON page.id = so.stockin_id ".$select_ids_sql." ORDER BY id DESC";
            $data = $this->query($sql);
            $stockin_type=array(
                '1'=>'采购入库',
                '2'=>'调拨入库',
                '3'=>'退货入库',
                '4'=>'盘盈入库',
                '6'=>'其他入库',
                '9'=>'初始化入库',
                '12'=>'委外入库',
            );
            $stockin_status=array(
                '10'=>'已取消',
                '20'=>'编辑中',
                '30'=>'待审核',
                '32'=>'待推送',
                '35'=>'委外待入库',
                '40'=>'待关联',
                '50'=>'待价格确认',
                '60'=>'待结算',
                '70'=>'暂估结算',
                '80'=>'已完成',
            );
            for($i=0;$i<count($data);$i++){
                $data[$i]['mode']=$stockin_type[$data[$i]['mode']];
                $data[$i]['status']=$stockin_status[$data[$i]['status']];
            }
            $num = workTimeExportNum($type);
            if (count($data) > $num) {
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
                SE('导出的详情数据超过设定值，8:00-19:00可以导出1000条，其余时间可以导出4000条!');
            }

            $excel_header = D('Setting/UserData')->getExcelField('Stock/StockIn', 'stockinexport');
            $title = '入库单';
            $filename = '入库单';
            foreach ($excel_header as $v) {
                $width_list[] = 20;
            }
			if($type == 'csv') {
				$ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','品牌','入库人员','仓库名称','货位');
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
	public function searchForm($search){
        $where_limit = '';
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case 'stockin_no'://入库单号
                    set_search_form_value($where_limit, $k, $v, 'so', 1,  ' AND ');
                    break;
                case 'spec_no': //商家编码
                    set_search_form_value($where_limit, $k, $v, 'gs', 1,  ' AND ');
                    break;
                case 'warehouse_id': //仓库
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'goods_no'://货品编号
                    set_search_form_value($where_limit, $k, $v, 'gg', 1,  ' AND ');
                    break;
                case 'barcode'://条形码
                    set_search_form_value($where_limit, $k, $v, 'gs', 1,  ' AND ');
                    break;
                case 'status'://入库单状态
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'src_order_type'://入库单类别
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'logistics_id'://物流类别
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'operator_id'://经办人
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'src_order_no'://源单号
                    set_search_form_value($where_limit, $k, $v, 'so', 1,  ' AND ');
                    break;
				case 'day_start':
					set_search_form_value($where_limit, 'created', $v,'so', 4,' AND ',' > ');
					break;
				case 'day_end':
					set_search_form_value($where_limit, 'created', $v,'so', 4,' AND ',' <= ');

            }
        }
        return $where_limit;
    }
	public function submitReturnStockIn($arr_form_data,$arr_refund_order,$arr_return_order,$id,$is_api){
		$is_rollback=false;
		$refund_id='';
		$stockin_id='';
		$result['status'] = 0;
		$result['msg']='换货入库成功';
		try{
			$is_rollback=true;
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			$this->startTrans();
			$user_id=get_operator_id();

			$refund_data=D('Trade/OriginalRefund')->getApiRefund(
				"ar.refund_no AS src_no, ar.platform_id, ar.shop_id, ar.type, ar.pay_account, ar.pay_no, ar.refund_amount , ar.actual_refund_amount AS goods_amount, ar.remark ,
							 ar.logistics_name, ar.logistics_no, ar.buyer_nick, ar.tid, st.trade_no, ar.actual_refund_amount AS guarante_refund_amount, st.trade_id   ",
				array('refund_id'=>array('eq',$arr_form_data['refund_id'])),
				"ar",
				"LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id");
//            $refund_data['refund']['refund_id']=0;
//            $refund_data['refund']['swap_province']=0;
//            $refund_data['refund']['swap_city']=0;
//            $refund_data['refund']['swap_district']=0;
			if($arr_form_data['type'] == 2){
				$refund_data['logistics_no'] = $arr_form_data['refund_info_logistics'];
				$refund_data['logistics_name'] = $arr_form_data['refund_info_logistics_name'];
				$refund_data['buyer_nick'] = $arr_form_data['refund_info_buyer_nick'];
				$refund_data['remark'] = $arr_form_data['refund_info_remark'];
				$refund_data['goods_refund_count'] = $arr_form_data['goods_refund_count'];
				$refund_data['warehouse_id'] = $arr_form_data['warehouse_id'];
				$refund_data['refund_no'] = '';
				$refund_data['reason_id'] = '0';
				$refund_data['direct_refund_amount'] = '0.0000';
				$refund_data['pay_method'] = '1';
				$refund_data['stockin_pre_no'] = '';
				$refund_data['flag_id'] = '0';
				$refund_data['post_amount'] = '0.0000';
				unset($refund_data['platform_id']);unset($refund_data['shop_id']);unset($refund_data['pay_no']);
			}elseif($arr_form_data['type'] == 3){
				$refund_data = $arr_form_data;
			}
			foreach($arr_refund_order as $k => $v){
				$arr_refund_order[$k]['refund_num'] = $v['stockin_num'];
				if((int)$v['expect_num'] == 0){
					unset($arr_refund_order[$k]);
					unset($arr_return_order[$k]);
				}
			}
			//D('Trade/RefundManage')->validateRefund($refund_data);
			D('Trade/RefundManage')->addRefundNoTrans($refund_data,$arr_refund_order,$arr_return_order,$user_id,$is_api,$refund_id);
//            $res_cfg_value=get_config_value('refund_auto_agree');
//            if($res_cfg_value!=1){
//                $agree_result=$this->agreeRefund($refund_id,$user_id);
//                if ($agree_result['status']==0) {
//                    $agree_result['status']=1;
//                }elseif($agree_result['status']==1){
//                    $agree_result['status']=0;
//                }
//                return $agree_result;
//            }
			//审核退换单并入库
			if($arr_form_data['singal_flag']==null){
				$agree_result = D('Trade/RefundManage')->agreeRefundNoTrans($refund_id,$user_id,1,-1,$arr_form_data['refund_in_remark'],$stockin_id);
			}else{
				$agree_result = D('Trade/RefundManage')->agreeRefundNoTrans($refund_id,$user_id,1,$arr_form_data['singal_flag'],$arr_form_data['refund_in_remark'],$stockin_id);
			}
			if(!empty($agree_result['fail']))
			{
				$this->rollback();
				$result['status'] = 1;
				//$result['msg']='未知错误,请联系管理员';
				$result['msg']=$agree_result['fail'][0]['result_info'];
			}
			//D('Stock/StockInOrder')->refundStockIn($refund_id,$stockin_id);
			//更新入库备注
			$stockin_remark = array(
				'stockin_id'	=>	$stockin_id,
				'remark'		=>	$arr_form_data['refund_in_remark'],
			);
			M('stockin_order')->save($stockin_remark);
			$this->commit();
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
//			$this->error($e->getMessage());
			$result['status'] = 1;
			$result['msg']=$e->getMessage();
            \Think\Log::write($this->name.'-submitReturnStockIn-'.$e->getMessage());
        }catch (\PDOException $e) {
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-submitReturnStockIn-'.$e->getMessage());
			$result['status'] = 1;
			$result['msg']='未知错误,请联系管理员';
		}catch (\Exception $e) {
			if($is_rollback)
			{
				$this->rollback();
			}
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-submitReturnStockIn-'.$msg);
			$result['status'] = 1;
			$result['msg']='未知错误,请联系管理员';
		}
		return $result;
	}
}