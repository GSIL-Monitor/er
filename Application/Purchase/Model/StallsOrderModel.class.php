<?php
namespace Purchase\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

class StallsOrderModel extends Model
{
    protected $tableName = 'stalls_order';
    protected $pk        = 'stalls_id';
	protected $_validate = array(
		array('warehouse_name','require','仓库不能为空'),
		array('spec_no','require','商家编码不能为空'),
		array('num','require','档口数量不能为空'),
		array('num','checknum','档口数量不合法',1,'callback'),
		array('price','checkPrice','档口价不合法',1,'callback'),
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
    public function insertStallsOrderForUpdate($data,$update = false,$options = ''){
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
            \Think\Log::write($this->name."-insertStallsOrderForUpdate-".$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function updateStallsOrder($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateStallsOrder-'.$msg);
        }
    }
	public function saveStallsOrder($data){
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
			$order['stalls_no'] = array('exp',"FN_SYS_NO('stalls')");
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
				'stalls_id' => $res_order_id,
				'operator_id' => $operator_id,
				'type' => 1,
				'remark' => '新建档口档口单',
				'created' => array('exp','NOW()'),
			);
			M('stalls_order_log')->add($log_data);
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
	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc",$type=""){
		try{	
			if($search['warehouse_id'] == 'all' || !isset($search['warehouse_id'])){
				$is_warehouse = 1;
			}
			$warehouse_list = D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			if($is_warehouse == 1){
				$search['warehouse_id'] .= ',-1';
			}
			$rows=intval($rows);
			$page=intval($page);
			$where_stalls = '';
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'status':
						set_search_form_value($where_stalls,$k,$v,'so',2,' AND ');
						break;
					case 'warehouse_id':
						set_search_form_value($where_stalls,$k,$v,'so',2,' AND ');
						break;
					case 'stalls_no':
						set_search_form_value($where_stalls,$k,$v,'so',1,' AND ');
						break;
					case 'provider_id':
						set_search_form_value($where_stalls,$k,$v,'sod',2,' AND ');
						break;
					case 'unique_print_status':
						set_search_form_value($where_stalls,$k,$v,'so',2,' AND ');
						break;
					case 'purchaser_id':
						set_search_form_value($where_stalls,$k,$v,'so',2,' AND ');
						break;
				}
			}
			$where_stalls=ltrim($where_stalls, ' AND ');
			
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			$total = $this->alias('so')->field('so.stalls_id as id')->join('left join stalls_less_goods_detail sod on sod.stalls_id=so.stalls_id')->where($where_stalls)->group('so.stalls_id');
			$point_number=get_config_value('point_number',0);
			
			$goods_count = 'CAST(if(sum(sod.num) is NULL,0,sum(sod.num)) AS DECIMAL(19,'.$point_number.')) goods_count';
			$in_num = 'CAST(if(sum(sod.stockin_status) is NULL,0,sum(sod.stockin_status)) AS DECIMAL(19,'.$point_number.')) in_num';
			$put_num = 'CAST(if(sum(sod.pickup_status) is NULL,0,sum(sod.pickup_status)) AS DECIMAL(19,'.$point_number.')) put_num';
			$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->alias('so')->field('so.stalls_id as id')->join('left join stalls_less_goods_detail sod on sod.stalls_id=so.stalls_id')->where($where_stalls)->group('so.stalls_id')->select();
			$row = $this->fetchsql(false)->distinct(true)->alias('so')->field('so.stalls_id AS id,so.unique_print_status,so.detail_print_status,cw.warehouse_id,cw.type as warehouse_type,cw.name as warehouse_name,so.stalls_no,if(so.is_hot,"是","否") as is_hot,so.hot_print_status,so.hot_print_num,so.status,he1.fullname as creator_name,he2.fullname as purchaser_name,cl.logistics_name as logistics_type,sum(sod.price) goods_fee,so.post_fee,so.other_fee,so.goods_type_count,(sum(sod.price)+so.post_fee+so.other_fee) tax_fee,'.$goods_count.','.$in_num.','.$put_num.',so.remark,so.created,so.modified')->join('INNER JOIN ('.$total_sql.') ts ON ts.id = so.stalls_id')->join('LEFT JOIN stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id')->join('LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_type LEFT JOIN hr_employee he1 ON he1.employee_id = so.creator_id LEFT JOIN hr_employee he2 ON he2.employee_id = so.purchaser_id LEFT JOIN cfg_warehouse cw on cw.warehouse_id=so.warehouse_id ')->group('id')->order($order)->select();
			foreach($row as $key=>$val){
				$spec_id_info = D('StallsOrderDetail')->fetchSql(false)->field('spec_id')->where(array('stalls_id'=>$val['id']))->group('spec_id')->select(); 
				$row[$key]['goods_type_count'] = count($spec_id_info);
			}
			$data = array('total'=>count($num),'rows'=>$row);
			
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
	
	public function getStallsLessGoodsDetail($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc",$type = ''){
		try{
			$rows=intval($rows);
			$page=intval($page);
			$where_stalls_less_goods = ' slgd.trade_status=0 ';
			$page_left_join = '';
			$where_goods_spec = '';
			$where_stockout_order = '';
			$page_left_join_status = false;
			D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'unique_code':
						set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',10,' AND ');
						break;
					case 'box_no':
						set_search_form_value($where_stockout_order,$k,$v,'so',10,' AND ');
						$page_left_join .= ' LEFT JOIN stockout_order so ON so.src_order_id=slgd.trade_id';
						break;
					case 'trade_no':
						set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',10,' AND ');
						break;
					case 'spec_no':
						set_search_form_value($where_goods_spec,$k,$v,'gs',10,' AND ');
						$page_left_join .= ' LEFT JOIN goods_spec gs ON gs.spec_id=slgd.spec_id';
						$page_left_join_status = true;
						break;
					case 'warehouse_id':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'provider_id':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',2,' AND ');
						break;
					case 'provider_ids':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,'provider_id',$v,'slgd',2,' AND ');
						break;
//					case 'trade_status':
//						if($v!='all')
//							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
//						break;
					case 'stockin_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'pickup_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'unique_print_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'hot_print_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'logistics_print_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'tag_print_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
					case 'generate_status':
						if($v!='all')
							set_search_form_value($where_stalls_less_goods,$k,$v,'slgd',1,' AND ');
						break;
				}
			}
			$where_stalls_less_goods=ltrim($where_stalls_less_goods, ' AND ');
			$sort_map = array('spec_no'=>'gs.spec_no','id'=>'id');
			$order = $sort_map[$sort].' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			$stalls_less_goods_model = M('stalls_less_goods_detail');
			$where_stalls_less_goods .= $where_goods_spec;
			$where_stalls_less_goods .= $where_stockout_order;
			$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
			if($type == 'add'){
				$where_stalls_less_goods .= ' and block_reason = 0 and stockin_status = 0 and pickup_status = 0 and generate_status = 0 and hot_status = 0 ';
			}
			if($sort == 'spec_no' && $page_left_join_status == false){
				$page_left_join .= ' LEFT JOIN goods_spec gs ON gs.spec_id=slgd.spec_id';
			}
			$num = $stalls_less_goods_model->fetchSql(false)->alias('slgd')->field('slgd.rec_id as id')->join($page_left_join)->where($where_stalls_less_goods)->count();
			$page_join = $stalls_less_goods_model->fetchSql(true)->alias('slgd')->field('slgd.rec_id AS id')->join($page_left_join)->where($where_stalls_less_goods)->order($order)->limit($limit)->select();
			if($dynamic_allocation_box==1){
				$fields = 'slgd.rec_id AS id,slgd.unique_code,slgd.hot_print_status,if(slgd.hot_status,"已生成","未生成") as hot_status,slgd.trade_no,slgd.block_reason,IF(slgd.sort_status=0,\'未分拣\',IF(slgd.sort_status=1,\'已分拣\',IF(slgd.sort_status=2,\'档口货品分拣完成\',\'订单分拣完成\'))) AS sort_status,IF(slgd.stockin_status=1,\'已入库\',\'未入库\') AS stockin_status,IF(slgd.pickup_status=1,\'已取货\',\'未取货\') AS pickup_status,IF(slgd.unique_print_status=1,\'已打印\',\'未打印\') AS unique_print_status,IF(slgd.logistics_print_status=1,\'已打印\',\'未打印\') AS logistics_print_status,IF(slgd.tag_print_status=1,\'已打印\',\'未打印\') AS tag_print_status,IF(slgd.generate_status=1,\'已生成\',\'未生成\') AS generate_status,pp.provider_name,slgd.remark,slgd.modified,slgd.created,gs.spec_no,gs.spec_code,gs.spec_name,gg.goods_no,gg.goods_name,cw.name AS warehouse_name,IF(bbgm.sub_big_box_no IS NULL,\'\',bbgm.sub_big_box_no) AS box_no';
			}else{
				$fields = 'slgd.rec_id AS id,slgd.unique_code,slgd.hot_print_status,if(slgd.hot_status,"已生成","未生成") as hot_status,slgd.trade_no,slgd.block_reason,IF(slgd.sort_status=0,\'未分拣\',IF(slgd.sort_status=1,\'已分拣\',IF(slgd.sort_status=2,\'档口货品分拣完成\',\'订单分拣完成\'))) AS sort_status,IF(slgd.stockin_status=1,\'已入库\',\'未入库\') AS stockin_status,IF(slgd.pickup_status=1,\'已取货\',\'未取货\') AS pickup_status,IF(slgd.unique_print_status=1,\'已打印\',\'未打印\') AS unique_print_status,IF(slgd.logistics_print_status=1,\'已打印\',\'未打印\') AS logistics_print_status,IF(slgd.tag_print_status=1,\'已打印\',\'未打印\') AS tag_print_status,IF(slgd.generate_status=1,\'已生成\',\'未生成\') AS generate_status,pp.provider_name,slgd.remark,slgd.modified,slgd.created,gs.spec_no,gs.spec_code,gs.spec_name,gg.goods_no,gg.goods_name,cw.name AS warehouse_name,so.box_no';
			}
			$row = $stalls_less_goods_model->fetchSql(false)->alias('slgd')->field($fields)->join('INNER JOIN ('.$page_join.' ) page ON page.id=slgd.rec_id ')->join('LEFT JOIN goods_spec gs ON gs.spec_id=slgd.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = slgd.warehouse_id')->join('LEFT JOIN purchase_provider pp ON pp.id=slgd.provider_id')->join('LEFT JOIN stockout_order so ON so.src_order_id=slgd.trade_id')->join('LEFT JOIN big_box_goods_map bbgm ON bbgm.stockout_id=so.stockout_id')->order($order)->select();
			$data = array('total'=>$num,'rows'=>$row);
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
	public function splitStalls($id,$arr_data_main,$user_id){
		try{
			$this->startTrans();
			$where = array('stalls_id'=>$id);
			$order_info = $this->field(array('warehouse_id','unique_print_status','purchaser_id','contact','telno','receive_address','logistics_type','check_operator_id','check_time','facheck_operator_id','facheck_time','post_fee'))->where($where)->find();
			$order_info['src_order_id'] = $id;
			$order_info['stalls_no'] = array('exp',"FN_SYS_NO('stalls')");
			$order_info['creator_id'] = $user_id;
			$order_info['status'] = 20;
			$order_info['goods_count'] = 0;
			$order_info['goods_type_count'] = 0;
			$order_info['goods_fee'] = 0;
			foreach($arr_data_main as $val){
				$order_info['goods_count'] += $val['split_num'];
				$order_info['goods_type_count'] +=1;
				$order_info['goods_fee'] += $val['split_num']*$val['price'];
			}
			$order_info['tax_fee'] = $order_info['goods_fee'];
			$order_id = $this->add($order_info);
			foreach($arr_data_main as $v){
				D('StallsOrderDetail')->where(array('spec_id'=>$v['spec_id'],'stalls_id'=>$id))->limit($v['split_num'])->save(array('stalls_id'=>$order_id));
			}
			$update_order = D('StallsOrderDetail')->fetchsql(false)->field(array('sum(num) as goods_count','sum(price) as goods_fee','sum(price) as tax_fee'))->where($where)->find();
			$goods_type_count = D('StallsOrderDetail')->field('count(spec_id) as goods_type_count')->where($where)->group('spec_id')->find();
			$update_order['goods_type_count'] = $goods_type_count['goods_type_count'];
			$this->where($where)->save($update_order);
			$this->commit();
		}catch(\PDOException $e){	
			\Think\Log::write($e->getMessage());
			$this->rollback;
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
	}
	public function postAddOrder($data,$type){
			switch($type){
				case 'selected':
					try{
						if(empty($data)){
							SE('未选中货品');
						}
						$this->startTrans();
						$where = array('sod.rec_id'=>array('in',$data));
						$info = D('StallsOrderDetail')->alias('sod')->field(array('sum(price) as goods_fee','sum(tax_price) as tax_fee','sum(num) as goods_count'))->where($where)->find();
						$info['stalls_no'] = array('exp',"FN_SYS_NO('stalls')");
						$warehouse_info = D('StallsOrderDetail')->fetchSql(false)->alias('sod')->field(array('sod.warehouse_id','cw.address as receive_address','cw.contact','cw.telno'))->join('left join cfg_warehouse cw on cw.warehouse_id  = sod.warehouse_id')->where($where)->group('sod.warehouse_id')->select();
						if(count($warehouse_info) == 1){
							$info = array_merge($info,$warehouse_info[0]);
						}
						$goods_count = D('StallsOrderDetail')->alias('sod')->field(array('spec_id'))->where($where)->group('spec_id')->select();
						$info['goods_type_count'] = count($goods_count);
						$info['creator_id'] = $info['purchaser_id'] = get_operator_id();
						$order_id = $this->add($info);
						D('StallsOrderDetail')->alias('sod')->where($where)->save(array('stalls_id'=>$order_id,'generate_status'=>1));
						$log_data = array(
							'stalls_id' => $order_id,
							'operator_id' => get_operator_id(),
							'type' => 1,
							'remark' => '新建档口单',
							'created' => array('exp','NOW()'),
						);
						M('stalls_order_log')->add($log_data);
						$this->commit();
					}catch (BusinessLogicException $e){
						\Think\Log::write($e->getMessage());
						$this->rollback();
						SE($e->getMessage());
					}catch (\Think\Exception $e){
						\Think\Log::write($e->getMessage());
						$this->rollback();
						SE(self::PDO_ERROR);
					}
					break;
				case 'search':
					try{
						$where_stalls_less_goods = '';
						$where_goods_spec = '';
						foreach($data as $k=>$v){
							if($v === ''){
								continue;
							}
							switch($k){
								case 'unique_code':
									set_search_form_value($where_stalls_less_goods,$k,$v,'sod',10,' AND ');
									break;
								case 'trade_no':
									set_search_form_value($where_stalls_less_goods,$k,$v,'sod',10,' AND ');
									break;
								case 'spec_no':
									set_search_form_value($where_stalls_less_goods,$k,$v,'gs',10,' AND ');
									break;
								case 'warehouse_id':
									if($v!='all')
										set_search_form_value($where_stalls_less_goods,$k,$v,'sod',1,' AND ');
									break;
								case 'provider_id':
									if($v!='all')
										set_search_form_value($where_stalls_less_goods,$k,$v,'sod',1,' AND ');
									break;
							}
						}
						
						$where_stalls_less_goods=ltrim($where_stalls_less_goods, ' AND ');
						if(empty($where_stalls_less_goods)){
							$where_stalls_less_goods .= ' trade_status = 0 and block_reason = 0 and stockin_status = 0 and pickup_status = 0 and generate_status = 0';
						}else{
							$where_stalls_less_goods .= ' and trade_status = 0 and block_reason = 0 and stockin_status = 0 and pickup_status = 0 and generate_status = 0';
						}
						$date_info = D('StallsOrderDetail')->alias('sod')->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->field('sod.rec_id')->where($where_stalls_less_goods)->select();
						if(empty($date_info)){
							SE('搜索数据为空');
						}
						$this->startTrans();
						$info = D('StallsOrderDetail')->fetchSql(false)->alias('sod')->field(array('sum(sod.price) as goods_fee','sum(sod.tax_price) as tax_fee','sum(sod.num) as goods_count'))->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->where($where_stalls_less_goods)->find();
						$info['stalls_no'] = array('exp',"FN_SYS_NO('stalls')");
						$warehouse_info = D('StallsOrderDetail')->alias('sod')->field(array('sod.warehouse_id','cw.address as receive_address','cw.contact','cw.telno'))->join('left join cfg_warehouse cw on cw.warehouse_id  = sod.warehouse_id')->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->where($where_stalls_less_goods)->group('sod.warehouse_id')->select();
						if(count($warehouse_info) == 1){
							$info = array_merge($info,$warehouse_info[0]);
						}
						$goods_count = D('StallsOrderDetail')->alias('sod')->field(array('sod.spec_id'))->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->where($where_stalls_less_goods)->group('sod.spec_id')->select();
						$info['goods_type_count'] = count($goods_count);
						$info['creator_id'] = $info['purchaser_id'] = get_operator_id();
						$order_id = $this->add($info);
						$this->execute('update stalls_less_goods_detail sod left join goods_spec gs on gs.spec_id = sod.spec_id set sod.generate_status = 1,sod.stalls_id = '.$order_id.' where '.$where_stalls_less_goods);
						$log_data = array(
							'stalls_id' => $order_id,
							'operator_id' => get_operator_id(),
							'type' => 1,
							'remark' => '新建档口单',
							'created' => array('exp','NOW()'),
						);
						M('stalls_order_log')->add($log_data);
						$this->commit();
					}catch (BusinessLogicException $e){
						\Think\Log::write($e->getMessage());
						$this->rollback();
						SE($e->getMessage());
					}catch (\Exception $e){
						\Think\Log::write($e->getMessage());
						$this->rollback();
						SE(self::PDO_ERROR);
					}
					break;
			}
	}
	public function delStallsLessGoods($ids){
			$data['status'] = 0;
			$data['info'] = '删除成功';
			$stalls_less_goods_detail_model = M('stalls_less_goods_detail');
			$is_return = false;
		try{
			$this->startTrans();
			foreach($ids as $id){
				$del_where = array('slgd.rec_id'=>array('eq',$id));
				$ret = $this->alias('so')->fetchSql(false)->field('so.status,slgd.stalls_id,slgd.pickup_status,slgd.stockin_status')->join('RIGHT JOIN stalls_less_goods_detail slgd ON slgd.stalls_id=so.stalls_id')->where($del_where)->find();
				if($ret['pickup_status']==='0'&&$ret['stockin_status']==='0'){
					if(empty($ret['status'])){
						$del_ret = $stalls_less_goods_detail_model->where(array('rec_id'=>array('eq',$id)))->delete();
						$is_return = $del_ret !== false ? false : true;
					}else{
						$del_ret = $stalls_less_goods_detail_model->where(array('rec_id'=>array('eq',$id)))->delete();
						$is_return = $del_ret !== false ? false : true;
						$count = $stalls_less_goods_detail_model->fetchSql(false)->where(array('stalls_id'=>array('eq',$ret['stalls_id'])))->count();
						if($count==0){$del_ret = $this->delete($ret['stalls_id']);$is_return = $del_ret !== false ? false : true;}
					}
				}else{
					SE('不能删除已入库或已取货的缺货明细');
				}
				if($is_return) break;
			}
			if($is_return){$this->rollback();}
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
		return $data;
	}
	
	public function getPrintSortingGoods($ids)
    {
        try {
			$no = "";
			$ids = explode(',',$ids);
			foreach($ids as $v){
				$no .= "'".$v."',";
			}
			$no = substr($no,0,strlen($no)-1);
            $res = $this->query("select t.spec_id,sum(t.num - IFNULL(bgd.num,0)) as   num,group_concat(concat('[',t.box_no,'] : ',t.num - IFNULL(bgd.num,0))) as distribution,gg.goods_name,gs. spec_no,gs.spec_code,gs.spec_name,gs.barcode,gg.goods_no,gg.short_name,gb.brand_name from 
			(select sod.spec_id,sod.stockout_id,so.src_order_id,sod.num,so.box_no  from stockout_order so inner join (select trade_id from box_goods_detail where  box_no in(".$no.")  and sort_status  = 0 group by trade_id) bgd on bgd.trade_id  = so.src_order_id left join stockout_order_detail sod on sod.stockout_id = so.stockout_id ) t left join box_goods_detail bgd on bgd.trade_id = t.src_order_id and bgd.spec_id = t.spec_id left join goods_spec gs on gs.spec_id = t.spec_id left join goods_goods gg on  gg.goods_id = gs.goods_id left join goods_brand gb on gb.brand_id = gg.brand_id where t.num - IFNULL(bgd.num,0) > 0 group by t.spec_id");
			
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getPrintSortingGoods-'.$msg);
			SE(self::PDO_ERROR);
        }
		return $res;
    }
	public function oneSplit($id,&$fail){
		 try {
           $order_info = $this->alias('so')->fetchSql(false)->field('so.stalls_no,so.status,sum(sod.num) goods_count,sum(stockin_status) in_num')->join('left join stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id')->where(array('so.stalls_id'=>$id))->group('so.stalls_id')->find();
		   if($order_info['status'] != 20){
			   SE('档口单状态不正确,只能拆分编辑中的单子');
		   }
		   if($order_info['goods_count'] <= $order_info['in_num']){
			   SE('档口单已全部入库，不能拆分');
		   }
		   if($order_info['in_num'] == 0){
			   SE('档口单尚未入库，不能拆分');
		   }
		   $this->startTrans();
		   $new_order_info = $this->fetchSql(false)->alias('so')->field('so.unique_print_status,so.src_order_id,so.creator_id,so.purchaser_id,so.status,so.flag_id,so.contact,so.warehouse_id,so.telno,so.receive_address,so.logistics_type,so.check_operator_id,so.check_time,so.facheck_operator_id,so.facheck_time,so.expect_arrive_time,so.post_fee,so.other_fee,so.revert_reason,so.remark,sum(sod.price) as goods_fee,sum(sod.price) as tax_fee,sum(sod.num) as goods_count')->join('left join stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id')->where(array('so.stalls_id'=>$id,'sod.stockin_status'=>0))->group('so.stalls_id')->find();
		   $spec_num = D('StallsOrderDetail')->field('rec_id')->where(array('stalls_id'=>$id,'stockin_status'=>0))->group('spec_id')->select();
		   $new_order_info['goods_type_count'] = count($spec_num);
           $new_order_info['stalls_no'] = array('exp',"FN_SYS_NO('stalls')");
		   $new_id = $this->add($new_order_info);
		   D('StallsOrderDetail')->where(array('stalls_id'=>$id,'stockin_status'=>0))->save(array('stalls_id'=>$new_id));
		   $warehouse_id_info = D('StallsOrderDetail')->field('warehouse_id')->where(array('stalls_id'=>$new_id))->group('warehouse_id')->select();
		   if(count($warehouse_id_info) == 1){
			   $this->where(array('stalls_id'=>$new_id))->save(array('warehouse_id'=>$warehouse_id_info[0]['warehouse_id']));
		   }
			$old_warehouse_id_info = D('StallsOrderDetail')->field('warehouse_id')->where(array('stalls_id'=>$id))->group('warehouse_id')->select();
			if(count($old_warehouse_id_info) == 1){
			   $this->where(array('stalls_id'=>$id))->save(array('warehouse_id'=>$old_warehouse_id_info[0]['warehouse_id']));
		   }
		   $this->where(array('stalls_id'=>$id))->save(array('status'=>90));
		   $this->commit();
        }catch(BusinessLogicException $e){
	        $msg = $e->getMessage();
			$this->rollback();
	        $fail[] = array(
	            'stalls_id' =>$id,
	            'stalls_no' => $order_info['stalls_no'],
	            'msg'      => $msg,
	        );
	        return false;
	    }catch(\PDOException $e){
	        $msg = $e->getMessage();
			$this->rollback();
	        $fail[] = array(
	            'stalls_id' =>$id,
	            'stalls_no' => $order_info['stalls_no'],
	            'msg'      => self::PDO_ERROR,
	        );
	        \Think\Log::write($this->name.'-oneSplit-'.$msg);

	        return false;
	    }catch(\Exception $e){
	        $msg = $e->getMessage();
			$this->rollback();
	        $fail[] = array(
	            'stalls_id' =>$id,
	            'stalls_no' => $order_info['stalls_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	        \Think\Log::write($this->name.'-oneSplit-'.$msg);
	        return false;
	    }
		return true;
	}
	public function addHotOrder(){
		try{
			$hot_num = get_config_value('hot_num',0);
			$open_hot = get_config_value('open_hot',0);
			
			if($open_hot){
				$order_detail_info = $this->query("select count(slgd.spec_id) goods_count,GROUP_CONCAT(slgd.rec_id) rec_id_str,GROUP_CONCAT(slgd.trade_id) trade_id_str,sum(slgd.price) as goods_fee,sum(slgd.tax_price) as tax_fee,slgd.spec_id from  stalls_less_goods_detail slgd left join goods_spec gs on slgd.spec_id = gs.spec_id left join sales_trade st on st.trade_id = slgd.trade_id  where  slgd.trade_status = 0 and slgd.block_reason = 0 and slgd.stockin_status = 0 and slgd.pickup_status = 0 and slgd.generate_status = 0 and slgd.hot_status = 0  and st.goods_count = 1 and gs.is_hotcake = 1 and slgd.sort_status = 0 and  slgd.unique_print_status = 0 group by slgd.spec_id union select count(slgd.spec_id) goods_count,GROUP_CONCAT(slgd.rec_id) rec_id_str,GROUP_CONCAT(slgd.trade_id) trade_id_str,sum(slgd.price) as goods_fee,sum(slgd.tax_price) as tax_fee,slgd.spec_id from stalls_less_goods_detail slgd left join sales_trade st on st.trade_id = slgd.trade_id  where  slgd.trade_status = 0 and slgd.block_reason = 0 and slgd.stockin_status = 0 and slgd.pickup_status = 0 and slgd.generate_status = 0 and slgd.hot_status = 0  and st.goods_count = 1 and slgd.sort_status = 0 and slgd.unique_print_status = 0 group by slgd.spec_id having count(slgd.spec_id)>".$hot_num);	
				if(empty($order_detail_info)){
					$less_order_detail_info = $this->query("select count(slgd.spec_id) goods_count,GROUP_CONCAT(slgd.rec_id) rec_id_str,GROUP_CONCAT(slgd.trade_id) trade_id_str,sum(slgd.price) as goods_fee,sum(slgd.tax_price) as tax_fee,slgd.spec_id from stalls_less_goods_detail slgd left join sales_trade st on st.trade_id = slgd.trade_id  where  slgd.trade_status = 0 and slgd.block_reason = 0 and slgd.stockin_status = 0 and slgd.pickup_status = 0 and slgd.generate_status = 0 and slgd.hot_status = 0  and st.goods_count = 1 and slgd.sort_status = 0 and slgd.unique_print_status = 0 group by slgd.spec_id ");
					if(!empty($less_order_detail_info)){
						$this->addHotOrderDeal($less_order_detail_info,$open_hot);
					}else{
						SE('没有爆款货品!');
					}
				}else{
					$this->addHotOrderDeal($order_detail_info);
				}
				
			}else{
				$order_detail_info = $this->query("select count(slgd.spec_id) goods_count,GROUP_CONCAT(slgd.rec_id) rec_id_str,GROUP_CONCAT(slgd.trade_id) trade_id_str,sum(slgd.price) as goods_fee,sum(slgd.tax_price) as tax_fee,slgd.spec_id from  stalls_less_goods_detail slgd left join goods_spec gs on slgd.spec_id = gs.spec_id left join sales_trade st on st.trade_id = slgd.trade_id  where  slgd.trade_status = 0 and slgd.block_reason = 0 and slgd.stockin_status = 0 and slgd.pickup_status = 0 and slgd.generate_status = 0 and slgd.hot_status = 0  and st.goods_count = 1 and gs.is_hotcake = 1 and slgd.sort_status = 0 and slgd.unique_print_status = 0 group by slgd.spec_id"); 
				if(empty($order_detail_info)){
					SE('没有爆款货品');
				}
				$this->addHotOrderDeal($order_detail_info);
			}
			
			
		}catch(BusinessLogicException $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-addHotOrder-'.$msg);
			SE($e->getMessage());
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-addHotOrder-'.$msg);
			SE(self::PDO_ERROR);
        }catch (\Think\Exception $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-addHotOrder-'.$msg);
			SE($e->getMessage());
		}
	}
	
	public function addHotOrderDeal($order_detail_info,$open_hot = 0){
		try{
			$sid = get_operator_id();
			$not_goods = 0;
			foreach($order_detail_info as $k=>$v){
				$hot_order_info = $this->query('select so.goods_count,so.stalls_id from stalls_order so left join stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id where so.hot_print_status = 1 and  so.status = 20 and so.is_hot = 1 and sod.spec_id = '.$v['spec_id']);
				if(empty($hot_order_info) && !$open_hot){
						$not_goods = 1;
						$stalls_hot_order = array(
							'stalls_no'=>array('exp',"FN_SYS_NO('stalls')"),
							'creator_id'=>$sid,
							'purchaser_id'=>$sid,
							'goods_fee'=>$v['goods_fee'],
							'tax_fee'=>$v['tax_fee'],
							'goods_count'=>$v['goods_count'],
							'goods_type_count'=>1,
							'is_hot'=>1,
							'hot_print_status'=>1,
						);
						$stalls_id = $this->add($stalls_hot_order);
						$this->execute('update stalls_less_goods_detail set stalls_id = '.$stalls_id.',hot_status = 1 ,hot_print_status = 1 where rec_id in('.$v['rec_id_str'].')');
						$warehouse_id_info = D('Purchase/StallsOrderDetail')->field('warehouse_id')->where(array('stalls_id'=>$stalls_id))->group('warehouse_id')->select();
						if(count($warehouse_id_info) == 1){
							$this->where(array('stalls_id'=>$stalls_id))->save(array('warehouse_id'=>$warehouse_id_info[0]['warehouse_id']));
						}
						$this->execute('update sales_trade set stalls_id = '.$stalls_id.' where trade_id in('.$v['trade_id_str'].')');
						$log_data = array(
							'stalls_id' => $stalls_id,
							'operator_id' => get_operator_id(),
							'type' => 1,
							'remark' => '新建爆款单',
							'created' => array('exp','NOW()'),
						);
						M('stalls_order_log')->add($log_data);
						
				}else if(!empty($hot_order_info)){
					$not_goods = 1;
					$this->where(array('stalls_id'=>$hot_order_info[0]['stalls_id']))->save(array('goods_count'=>$hot_order_info[0]['goods_count']+$v['goods_count']));
					$this->execute('update stalls_less_goods_detail set stalls_id = '.$hot_order_info[0]['stalls_id'].',hot_status = 1 ,hot_print_status = 1 where rec_id in('.$v['rec_id_str'].')');
					$this->execute('update sales_trade set stalls_id = '.$hot_order_info[0]['stalls_id'].' where trade_id in('.$v['trade_id_str'].')');
					$warehouse_id_info = D('Purchase/StallsOrderDetail')->field('warehouse_id')->where(array('stalls_id'=>$hot_order_info[0]['stalls_id']))->group('warehouse_id')->select();
					if(count($warehouse_id_info) == 1){
						$this->where(array('stalls_id'=>$hot_order_info[0]['stalls_id']))->save(array('warehouse_id'=>$warehouse_id_info[0]['warehouse_id']));
					}
				}
			}
			if($not_goods == 0){
				SE('没有爆款货品!');
			}
		}catch(BusinessLogicException $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-addHotOrderDeal-'.$msg);
			SE($e->getMessage());
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-addHotOrderDeal-'.$msg);
			SE(self::PDO_ERROR);
        }catch (\Think\Exception $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-addHotOrderDeal-'.$msg);
			SE($e->getMessage());
		}
	}
	public function getHotOrdersInfo($stalls_no,$order){

        try{
            $hot_code = $this->where(array('stalls_no'=>$stalls_no,'is_hot'=>1))->select();
            $orderStr = 'order by slto.refund_status desc,sto.logistics_print_status desc,st.pay_time';
            if(empty($hot_code)){SE('该爆款码不存在');}
            if($order == 'asc'){
                $orderStr = 'order by slto.refund_status asc,sto.logistics_print_status asc,st.pay_time';
            }
            $sql = 'select st.trade_no as src_order_no,st.trade_id,st.src_tids,sto.src_order_id,cl.logistics_name,cl.logistics_id,cl.bill_type,sto.logistics_no,sto.receiver_name,sto.logistics_print_status,sto.stockout_id,st.buyer_nick,st.paid,sto.weight,st.pay_time,st.trade_time,slto.refund_status from stalls_order so '.
                'left join sales_trade st on so.stalls_id = st.stalls_id '.
                'left join sales_trade_order slto on slto.trade_id = st.trade_id '.
                'left join stockout_order sto on st.trade_no = sto.src_order_no '.
                'left join cfg_logistics cl on cl.logistics_id = sto.logistics_id '.
                'where so.stalls_no ='."'".$stalls_no."'".$orderStr;
            $hot_orders =  $this->query($sql);
            for($i=0;$i<count($hot_orders);$i++){
                if(empty($hot_orders[$i]['src_order_no'])){
                    unset($hot_orders[$i]);
                }
            }
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE($e->getMessage());
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE(self::PDO_ERROR);
        }catch (\Think\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE($e->getMessage());
        }
        return $hot_orders;
    }
    public function hotGoodsPickList($printOrder){
        try{

            $this->startTrans();
            $stallsOrderDetailModel = D('Purchase/StallsOrderDetail');
            $stockSpecModel = D('Stock/StockSpec');
            $stockSpecPositionModel = D('Stock/StockSpecPosition');

            $detailData = $stallsOrderDetailModel->field('rec_id,unique_code,spec_id,trade_id,trade_no,stalls_id,warehouse_id,trade_status,block_reason,stockin_status,sort_status,pickup_status,generate_status,provider_id,price')->where(array('trade_no'=>$printOrder['src_order_no']))->select();
            $detailData = $detailData[0];
            $unicode = $detailData['unique_code'];
            if($detailData['stockin_status'] ==0){
                $stockSpec = $stockSpecModel->field('stock_num')->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->select();
                $stockSpecModel->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->save(array("stock_num"=>$stockSpec[0]['stock_num']+1));

                $stockSpecPosition = $stockSpecPositionModel->field('stock_num')->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->select();
                $stockSpecPositionModel->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->save(array("stock_num"=>$stockSpecPosition[0]['stock_num']+1));
                $date = date('Y-m-d H:i:s', time());
                $updateData = array(
                    'stockin_status'=>1,
                    'stockin_time'=>$date,
                    'pickup_status'=>1,
                    'sort_status'=>3
                );
                $stallsOrderDetailModel->where(array('unique_code'=>$unicode))->save($updateData);

                // 插入订单日志
//                $message = '爆款货品分拣入库--'.$detailData['unique_code'];
//                $this->insertToOrderLog($detailData['trade_id'],$message);
            }
            $this->commit();

        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE($e->getMessage());
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE(self::PDO_ERROR);
        }catch (\Think\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getHotOrdersInfo-'.$msg);
            SE($e->getMessage());
        }
    }
	public function clearHotPrintStatus($id){
		try{
			if(empty($id)){
				SE('请选择行!');
			}
			$data = $this->field('is_hot,hot_print_status')->where(array('stalls_id'=>$id))->find();
			if($data['is_hot'] == 0){
				SE('该订单不是爆款单，不能清除!');
			}
			if($data['hot_print_status'] == 1){
				SE('该爆款单打印状态为未打印，无需清除!');
			}
			$this->where(array('stalls_id'=>$id))->save(array('hot_print_status'=>1));
		}catch(BusinessLogicException $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			SE($e->getMessage());
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			SE(self::PDO_ERROR);
        }catch (\Think\Exception $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			SE($e->getMessage());
		}
	}
	public function exportToExcel($type = 'excel',$id){
		try {
			if(empty($id)){SE('没有查询到数据，请刷新后重试！');}
			$creator=session('account');
			$point_number=get_config_value('point_number',0);
			$num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) num';
			$in_num = 'CAST(sum(sod.stockin_status) AS DECIMAL(19,'.$point_number.')) in_num';
			$put_num = 'CAST(sum(sod.pickup_status) AS DECIMAL(19,'.$point_number.')) put_num';
			$where = array('sod.stalls_id'=>$id);
			$fields =  array('sod.rec_id as id','pp.provider_name','pp.id as provider_id','gs.spec_no',$in_num,$put_num,'sod.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'CAST(sum(sod.price)/count(sod.spec_id) AS DECIMAL(19,4)) as price','CAST(sum(sod.price) AS DECIMAL(19,4)) as amount','sod.remark','cgu.name as unit_name');
			$data = D('StallsOrderDetail')->fetchsql(false)->alias('sod')->field($fields)->join('LEFT JOIN purchase_provider pp ON pp.id = sod.provider_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->where($where)->group('gs.spec_no')->select();
			$num = workTimeExportNum($type);
			if (count($data) > $num) {
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
				SE('导出的详情数据超过设定值，8:00-19:00可以导出1000条，其余时间可以导出4000条!');
			}
			$excel_header = D('Setting/UserData')->getExcelField('Purchase/StallsOrder', 'stalls_order_detail');
			$title = '档口单明细';
			$filename = '档口单明细';
			foreach ($excel_header as $v) {
				$width_list[] = 20;
			}
			if($type == 'csv') {
				$ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','品牌','供应商','备注');
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
}

?>