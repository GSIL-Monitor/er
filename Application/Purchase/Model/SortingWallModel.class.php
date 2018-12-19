<?php
namespace Purchase\Model;

use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;

class SortingWallModel extends Model
{
    protected $tableName = 'cfg_sorting_wall';
    protected $pk        = 'wall_id';
	protected $_validate = array(
		array('wall_no', '', '分拣墙编号重复，请重新填写!', 1, 'unique', 3),
	);

	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc"){
		try{
			$rows=intval($rows);
			$page=intval($page);
			$where_sorting_wall = '';
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'wall_no':
						set_search_form_value($where_sorting_wall,$k,$v,'csw',1,' AND ');
						break;
					case 'row_num':
						set_search_form_value($where_sorting_wall,$k,$v,'csw',1,' AND ');
						break;
					case 'column_num':
						set_search_form_value($where_sorting_wall,$k,$v,'csw',1,' AND ');
						break;
					case 'type':
						if($v!='all')
						set_search_form_value($where_sorting_wall,$k,$v,'csw',1,' AND ');
						break;
					case 'is_disabled':
						if($v!='all')
						set_search_form_value($where_sorting_wall,$k,$v,'csw',1,' AND ');
						break;
				}
			}
			$where_sorting_wall=ltrim($where_sorting_wall, ' AND ');
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			$total = $this->distinct(true)->alias('csw')->field('csw.wall_id as id')->where($where_sorting_wall);
			$m = clone $total;
			//$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->query($m->fetchsql(true)->count());
			$row = $this->fetchsql(false)->distinct(true)->alias('csw')->field('csw.wall_id AS id,csw.wall_no,csw.row_num,csw.column_num,IF(csw.type=1,\'分拣墙\',\'缺货墙\') as type,csw.is_disabled,csw.modified,csw.created')->join('sorting_wall_detail swd ON csw.wall_id=swd.wall_id')->where($where_sorting_wall)->group('swd.wall_id')->order($order)->select();
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
	public function saveSortingWall($sorting_wall_info){
		$data['status']=0;
		$data['info']="保存成功";
		$update_data=$sorting_wall_info;
		$sorting_wall_detail_model = M('sorting_wall_detail');
		try{
			if(!$this->create($update_data)){
				$data['status']=1;
				$data['info']=$this->getError();
				return $data;
			}
			$wall_type_map = ['缺货墙','分拣墙'];
			$this->startTrans();
			if($update_data['id']==0){//添加保存
				$add_data=array(
					'wall_no'=>$update_data['wall_no'],
					'row_num' =>$update_data['row_num'],
					'column_num' =>$update_data['column_num'],
					'type' =>$update_data['type'],
					'row_num' =>$update_data['row_num'],
					'is_disabled' =>$update_data['is_disabled'],
					'modified' => array('exp','NOW()'),
					'created' => array('exp','NOW()'),
				);
				$wall_type = $wall_type_map[$update_data['type']];
				if($update_data['type']){
//					$is_main_wall = $this->where(array('type'=>array('eq','1')))->find();
//					if(!empty($is_main_wall)){SE('目前系统中只能存在一个分拣墙！');}
				}else{
					$is_less_wall = $this->where(array('type'=>array('eq','0')))->find();
					if(!empty($is_less_wall)){SE('目前系统中只能存在一个缺货墙！');}
				}
				$wall_id = $this->add($add_data);
				$arr_sys_other_log = array(
					"type"        => "23",
					"operator_id" => get_operator_id(),
					"data"        => $wall_id,
					"message"     => "创建" . $wall_type . "--" . $wall_type . "编号--“" . $update_data['wall_no'].'”',
					"created"     => date("Y-m-d G:i:s")
				);
				M("sys_other_log")->add($arr_sys_other_log);
				for($i=1; $i<intval($update_data['row_num'])+1; $i++){
					for($j=1; $j<intval($update_data['column_num'])+1; $j++){
						$add_tetail[] = array(
							'wall_id'=>$wall_id,
							'box_no'=>$update_data['wall_no'].'-'.$i.'-'.$j,
							'row'=>$i,
							'column'=>$j,
							'modified' => array('exp','NOW()'),
							'created' => array('exp','NOW()'),
						);
					}
				}
				$sorting_wall_detail_model->addAll($add_tetail);
			}else{//编辑保存
				$add_data=array(
					'wall_id'=>$update_data['id'],
					'wall_no'=>$update_data['wall_no'],
					'row_num' =>$update_data['row_num'],
					'column_num' =>$update_data['column_num'],
					'type' =>$update_data['type'],
					'is_disabled' =>$update_data['is_disabled'],
					//'modified' => array('exp','NOW()'),
				);
				$is_use_condition = array(
					'wall_id'	=>$update_data['id'],
					'is_use'	=>'1',
				);
				$is_use = $sorting_wall_detail_model->where($is_use_condition)->find();
				if($update_data['is_disabled']){//若分拣墙正在使用中，则不能停用
					if(!empty($is_use)){SE('当前分拣墙(缺货墙)正在使用中，不可停用！');}
				}
				if($update_data['type']){//若已存在分拣墙，则不能添加
//					$is_main_wall = $this->where(array('type'=>array('eq','1')))->find();
//					$is_main_wall_now = $this->where(array('type'=>array('eq','1'),'wall_id'=>array('eq',$update_data['id'])))->find();
//					if(!empty($is_main_wall)&&empty($is_main_wall_now)){SE('目前系统中只能存在一个分拣墙！');}
				}else{
					$is_less_wall = $this->where(array('type'=>array('eq','0')))->find();
					$is_less_wall_now = $this->where(array('type'=>array('eq','0'),'wall_id'=>array('eq',$update_data['id'])))->find();
					if(!empty($is_less_wall)&&empty($is_less_wall_now)){SE('目前系统中只能存在一个缺货墙！');}
				}
				$oldSortingWall = $this->find($update_data['id']);
				$save_ret = $this->save($add_data);
				$arr_sys_other_log=array();
				$wall_type = $wall_type_map[$oldSortingWall['type']];
				if($oldSortingWall['wall_no']!= $add_data['wall_no']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--'.$wall_type.'编号--从“' . $oldSortingWall["wall_no"] .'”  到  “'. $add_data['wall_no'].'”'
					);
				}
				if($oldSortingWall['row_num']!= $add_data['row_num']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--排数--从“' . $oldSortingWall["row_num"] .'”  到  “'. $add_data['row_num'].'”'
					);
				}
				if($oldSortingWall['column_num']!= $add_data['column_num']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--列数--从“' . $oldSortingWall["column_num"] .'”  到  “'. $add_data['column_num'].'”'
					);
				}
				if($oldSortingWall['is_disabled']!= $add_data['is_disabled']){
					if($oldSortingWall["is_disabled"]=='0'){
						$old_is_disabled="否";
					}else{
						$old_is_disabled="是";
					}
					if($add_data["is_disabled"]=='0'){
						$is_disabled="否";
					}else{
						$is_disabled="是";
					}
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--停用--从“' . $old_is_disabled .'”  到  “'. $is_disabled.'”'
					);
				}
				/*if($oldSortingWall['type']!= $add_data['type']){
					if($oldSortingWall["type"]==1){
						$oldSortingWallType='分拣墙';
					}else{
						$oldSortingWallType='缺货墙';
					}
					if($add_data["type"]==1){
						$SortingWallType='分拣墙';
					}else{
						$SortingWallType='缺货墙';
					}
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑分拣墙--分拣墙属性--从“' . $oldSortingWallType .'”  到  “'. $SortingWallType.'”'
					);
				}*/
				M("sys_other_log")->addAll($arr_sys_other_log);
				if($save_ret){
					if(!empty($is_use)){SE('当前分拣墙(缺货墙)正在使用中，不可更改！');}
					$del_condition = array('wall_id'=>array('eq',$update_data['id']));
					$del_ret = $sorting_wall_detail_model->where($del_condition)->delete();
					if($del_ret !== false){
						for($i=1; $i<intval($update_data['row_num'])+1; $i++){
							for($j=1; $j<intval($update_data['column_num'])+1; $j++){
								$add_tetail[] = array(
									'wall_id'=>$update_data['id'],
									'box_no'=>$update_data['wall_no'].'-'.$i.'-'.$j,
									'row'=>$i,
									'column'=>$j,
									'modified' => array('exp','NOW()'),
									'created' => array('exp','NOW()'),
								);
							}
						}
						$sorting_wall_detail_model->addAll($add_tetail);
					}else{
						$this->rollback();
					}
				}
				$up_data=array(
					'wall_id'=>$update_data['id'],
					'modified' => array('exp','NOW()'),
				);
				$this->save($up_data);
			}
			$this->commit();
		}catch(BusinessLogicException $e){
			$data['status']=1;
			$data['info']=$e->getMessage();
			$this->rollback();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}
		return $data;
	}

	public function getEditSortingWallData($id){
		try{
			$res=$this->alias('csw')->field('csw.wall_id as id,csw.wall_no,csw.row_num,csw.column_num,csw.is_disabled,csw.type')->where(array('wall_id'=>$id))->find();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		} catch (BusinessLogicException $e) {
			$msg = $e->getMessage();
			SE($msg);
		} catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	public function delSortingWall($ids){
		try{
			$data['status'] = 0;
			$data['info'] = '删除成功';
			$del_data = '';
			foreach($ids as $id){$del_data .= $id . ',';}
			$del_data = substr($del_data,0,-1);
			$this->startTrans();
			$sorting_wall_detail_model = M('sorting_wall_detail');
			$is_use_condition = $del_condition = array('wall_id'=>array('in',$del_data));
			$is_use_condition['is_use'] = '1';
			$is_use = $sorting_wall_detail_model->where($is_use_condition)->find();
			if(!empty($is_use)){SE('当前分拣墙(缺货墙)正在使用中，不可删除！');}
			$del_wall_nos = $this->query("SELECT GROUP_CONCAT(wall_no) AS wall_nos FROM cfg_sorting_wall WHERE wall_id IN (%s)",$del_data);
			$del_ret = $sorting_wall_detail_model->where($del_condition)->delete();
			if($del_ret !== false){
				$this->delete($del_data);
				$arr_sys_other_log = array(
					"type"        => "23",
					"operator_id" => get_operator_id(),
					"data"        => $del_data,
					"message"     => "删除分拣墙--分拣墙编号--“" . $del_wall_nos[0]['wall_nos'].'”',
					"created"     => date("Y-m-d G:i:s")
				);
				M("sys_other_log")->add($arr_sys_other_log);
			}else{
				$this->rollback();
			}
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

	public function getSortingBoxGoodsDetail($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc"){
		try{
			$rows=intval($rows);
			$page=intval($page);
			//$sorting_box_goods_detail = ' swd.is_use=1 ';
			$sorting_box_goods_detail = ' true ';
			//$page_left_join = '';
			$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
			if($dynamic_allocation_box==1){
				$page_left_join = 'LEFT JOIN stockout_order so ON so.stockout_id=swd.stockout_id LEFT JOIN box_goods_detail bgd ON bgd.box_no=swd.box_no AND bgd.trade_id=so.src_order_id LEFT JOIN sales_trade st ON st.trade_no = so.src_order_no LEFT JOIN big_box_goods_map bbgm ON swd.box_no=bbgm.box_no LEFT JOIN cfg_dynamic_box cdb on bbgm.wall_id = cdb.wall_id';
				$sorting_box_goods_detail .= ' AND swd.is_use<>0 ';
			}else{
				$page_left_join = 'LEFT JOIN stockout_order so ON so.stockout_id=swd.stockout_id LEFT JOIN box_goods_detail bgd ON bgd.box_no=swd.box_no AND bgd.trade_id=so.src_order_id LEFT JOIN sales_trade st ON st.trade_no = so.src_order_no';
			}
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'box_no':
						if($dynamic_allocation_box==1){
							set_search_form_value($sorting_box_goods_detail,'sub_big_box_no',$v,'bbgm',10,' AND ');
						}else{
							set_search_form_value($sorting_box_goods_detail,$k,$v,'swd',10,' AND ');
						}
						break;
					case 'trade_no':
						set_search_form_value($sorting_box_goods_detail,'src_order_no',$v,'so',10,' AND ');
//						if(empty($page_left_join)) {
//							$page_left_join = 'LEFT JOIN box_goods_detail bgd ON bgd.box_no=swd.box_no';
//							$sorting_box_goods_detail .= ' AND bgd.sort_status=0 ';
//						}
						break;
					case 'stockout_no':
						set_search_form_value($sorting_box_goods_detail,'stockout_no',$v,'so',1,' AND ');
						break;
					case 'spec_no':
						set_search_form_value($sorting_box_goods_detail,$k,$v,'gs',10,' AND ');
//						if(empty($page_left_join)){
//							$page_left_join = 'LEFT JOIN box_goods_detail bgd ON bgd.box_no=swd.box_no LEFT JOIN stockout_order so ON so.src_order_no=bgd.trade_no LEFT JOIN stockout_order_detail sod ON sod.stockout_id=so.stockout_id  LEFT JOIN goods_spec gs ON gs.spec_id=sod.spec_id ';
							$sorting_box_goods_detail .= ' AND bgd.sort_status=0 ';
//						}else{
							$page_left_join .= ' LEFT JOIN stockout_order_detail sod ON sod.stockout_id=so.stockout_id LEFT JOIN goods_spec gs ON gs.spec_id=sod.spec_id ';
//						}
						break;
					case 'wall_id':
						if($v!='all')
							if($dynamic_allocation_box==1){
								set_search_form_value($sorting_box_goods_detail,$k,$v,'cdb',1,' AND ');
							}else{
								set_search_form_value($sorting_box_goods_detail,$k,$v,'csw',1,' AND ');
							}
						break;
					case 'wall_type':
						if($v!='all')
							if($dynamic_allocation_box==1){
								set_search_form_value($sorting_box_goods_detail,'type',$v,'cdb',1,' AND ');
							}else{
								set_search_form_value($sorting_box_goods_detail,'type',$v,'csw',1,' AND ');
							}	
						break;
					case 'use_status':
						if($v!='all'){
							switch($v){
								case 0 ://未占用
									set_search_form_value($sorting_box_goods_detail,'is_use',0,'swd',1,' AND ');
									break;
								case 1 ://已占用
									set_search_form_value($sorting_box_goods_detail,'is_use',1,'swd',1,' AND ');
									set_search_form_value($sorting_box_goods_detail,'sort_status',0,'bgd',1,' AND ');
									break;
								case 2 ://预占用
									set_search_form_value($sorting_box_goods_detail,'is_use',1,'swd',1,' AND ');
									$sorting_box_goods_detail .= ' AND bgd.sort_status IS NULL ';
									break;
							}
						}
						break;
				}
			}
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			$sorting_wall_detail_model = M('sorting_wall_detail');
			$num = $sorting_wall_detail_model->fetchSql(false)->alias('swd')->field('swd.box_id as id')->join('LEFT JOIN cfg_sorting_wall csw ON csw.wall_id = swd.wall_id')->join($page_left_join)->where($sorting_box_goods_detail)->group('swd.box_no')->select();
			if($dynamic_allocation_box==1){
				$fields = 'swd.box_id AS id,IF(bbgm.sub_big_box_no IS NULL,\'\',bbgm.sub_big_box_no) AS box_no,swd.modified,swd.created,cdb.wall_no,IF(cdb.type=0,\'缺货墙\',\'分拣墙\') AS wall_type,IF(swd.is_use=1,IF(bgd.sort_status=0,\'已占用\',\'预占用\'),\'未占用\') AS use_status,IF(so.stockout_no IS NULL,\'无\',so.stockout_no) AS stockout_no,IF(so.src_order_no IS NULL,\'无\',so.src_order_no) AS trade_no,st.version_id';
			}else{
				$fields = 'swd.box_id AS id,swd.box_no,swd.modified,swd.created,csw.wall_no,IF(csw.type=0,\'缺货墙\',\'分拣墙\') AS wall_type,IF(swd.is_use=1,IF(bgd.sort_status=0,\'已占用\',\'预占用\'),\'未占用\') AS use_status,IF(so.stockout_no IS NULL,\'无\',so.stockout_no) AS stockout_no,IF(so.src_order_no IS NULL,\'无\',so.src_order_no) AS trade_no,st.version_id';
			}
			$row = $sorting_wall_detail_model->fetchSql(false)->alias('swd')->field($fields)->join('LEFT JOIN cfg_sorting_wall csw ON csw.wall_id = swd.wall_id')->join($page_left_join)->where($sorting_box_goods_detail)->group('swd.box_no')->order($order)->limit($limit)->select();
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
	public function showSortingBoxDeatil($id){
		try{
			$data = M('sorting_wall_detail')->field('box_no,stockout_id')->find($id);
			$box_no = $data['box_no'];
			$stockout_id = $data['stockout_id'];
			if(empty($box_no)){
				\Think\Log::write('未查询到分拣框信息');
				SE(self::UNKNOWN_ERROR);
			}
			$box_goods_detail_model = M('box_goods_detail');
			$box_goods_detail_where = array(
				'bgd.box_no'=>array('eq',$box_no),
				'bgd.sort_status'=>array('eq',0),
			);
			$box_goods_detail = $box_goods_detail_model->alias('bgd')->field('trade_no,GROUP_CONCAT(spec_id) AS spec_ids')->where($box_goods_detail_where)->find();
			$box_trade_no = $box_goods_detail['trade_no'];
			$box_spec_ids = ','.$box_goods_detail['spec_ids'].',';
			$stockout_order_detail =array();
			if(empty($box_trade_no)){
				$box_trade_no = M('stockout_order')->field('src_order_no')->where(array('stockout_id'=>$stockout_id))->find();
				$box_trade_no = $box_trade_no['src_order_no'];
			}
			if(!empty($box_trade_no)){
				$stockout_order_detail = M('stockout_order_detail')->fetchSql(false)->alias('sod')->field('\''.$box_trade_no.'\' AS trade_no,SUM(sod.num) AS unsort_num,0.0000 AS sort_num,sod.spec_id,sod.spec_no,sod.goods_no,sod.goods_name,sod.spec_code,sod.spec_name,SUM(sod.num) AS num,sod.src_order_detail_id,gbc.barcode,so.stockout_no')->join('LEFT JOIN stockout_order so ON so.stockout_id=sod.stockout_id')->join('LEFT JOIN goods_barcode gbc ON gbc.target_id=sod.spec_id AND gbc.is_master = 1')->where(array('so.src_order_no'=>array('eq',$box_trade_no)))->group('sod.spec_id')->select();
				foreach($stockout_order_detail as $k=>$v){
					if(strpos($box_spec_ids,','.$v['spec_id'].',') !== false){
						$spec_id_num = $box_goods_detail_model->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$box_trade_no),))->find();
						$stockout_order_detail[$k]['sort_num'] = number_format($spec_id_num['num'],4);
						$stockout_order_detail[$k]['unsort_num'] = number_format($v['num']-$spec_id_num['num'],4);
					}
				}
			}
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $stockout_order_detail;
	}
	public function getBoxGoodsInfo($box_nos){
		try {
			$box_goods_detail_where = array(
				'box_no'=>array('in',$box_nos),
				'sort_status'=>array('eq',0),
			);
			$box_goods_detail = M('box_goods_detail')->field('trade_no,box_no')->where($box_goods_detail_where)->group('box_no')->order('rec_id desc')->select();
			$result = array('total' => count($box_goods_detail), 'rows' => $box_goods_detail);
			return $result;
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-getSortingWallInfo-'.$msg);
			E(self::PDO_ERROR);
		}
	}
	public function getAllWallBoxByNo($new_wall_no){
		try {
			$sorting_where = array(
				'csw.wall_no'=>array('eq',$new_wall_no),
				'swd.is_use'=>array('eq',0),
			);
			$sorting_ret = M('sorting_wall_detail')->alias('swd')->field('swd.box_no')->join('LEFT JOIN cfg_sorting_wall csw ON csw.wall_id=swd.wall_id')->where($sorting_where)->order('swd.box_id asc')->select();
			return $sorting_ret;
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-getAllWallBoxByNo-'.$msg);
			E(self::PDO_ERROR);
		}
	}
	public function submitBoxGoodsTrans($update_data){
		$data['status']=0;
		$data['info']="移动成功";
		$sorting_wall_detail_model = M('sorting_wall_detail');
		$box_goods_detail_model = M('box_goods_detail');
		$stockout_order_model = D('Stock/StockOutOrder');
		$sales_trade_model = D('Trade/sales_trade');
		$sales_trade_log_model = D('Trade/sales_trade_log');
		$operator_id       = get_operator_id();
		$error_log = '';
		$is_rollback = false;
		$log = [];
		try{
			$this->startTrans();
			foreach($update_data as $k => $v){
				if($v['new_box_no'] != ''){
					//更新出库单表box_no
					$stockout_order_where = array(
						'src_order_no' 	=> $v['trade_no'],
					);
					$error_log = 'stockout_order';
					$ret = $stockout_order_model->fetchSql(false)->where($stockout_order_where)->save(array('box_no'=>$v['new_box_no']));
					if($ret === false){$is_rollback = true;break;}
					//更新分拣货品表box_no
					$box_goods_detail_where = array(
						'trade_no' 	    => $v['trade_no'],
						'box_no' 	    => $v['box_no'],
						'sort_status' 	=> 0,
					);
					$error_log = 'box_goods_detail';
					$ret = $box_goods_detail_model->fetchSql(false)->where($box_goods_detail_where)->save(array('box_no'=>$v['new_box_no']));
					if($ret === false){$is_rollback = true;break;}
					//更新分拣框表is_use
					$error_log = 'sorting_wall_detail';
					$tmp_stockout_id = $sorting_wall_detail_model->field('stockout_id')->where(array('box_no'=>$v['box_no']))->find();
					$ret = $sorting_wall_detail_model->fetchSql(false)->where(array('box_no'=>$v['box_no']))->save(array('is_use'=>0,'stockout_id'=>0));
					if($ret === false){$is_rollback = true;break;}
					$ret = $sorting_wall_detail_model->fetchSql(false)->where(array('box_no'=>$v['new_box_no']))->save(array('is_use'=>1,'stockout_id'=>$tmp_stockout_id['stockout_id']));
					if($ret === false){$is_rollback = true;break;}
					$trade_id = $sales_trade_model->field('trade_id')->where(array('trade_no'=>$v['trade_no']))->find();
					$log[] = array(
						'trade_id'         => $trade_id['trade_id'],
						'operator_id'      => $operator_id,
						'type'             => 171,
						'message'          => '订单编号:'.$v['trade_no'].' 由 '.$v['box_no'] .'(拣货框) 移动至 '.$v['new_box_no'].'(拣货框)',
					);
				}
			}
			$sales_trade_log_model->addTradeLog($log);
			if($is_rollback){$this->rollback();$data['status']=1;$data['info']=self::PDO_ERROR;\Think\Log::write($this->name.'-submitBoxGoodsTrans-'.$error_log.'-更新失败');}
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-submitBoxGoodsTrans-'.$error_log.'-'.$e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}catch(BusinessLogicException $e){
			\Think\Log::write($this->name.'-submitBoxGoodsTrans-'.$error_log.'-'.$e->getMessage());
			$data['status']=1;
			$data['info']=$e->getMessage();
			$this->rollback();
		}catch(\Exception $e){
			\Think\Log::write($this->name.'-submitBoxGoodsTrans-'.$error_log.'-'.$e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}
		return $data;
	}
	public function getHasUseBoxByWallNo($wall_no){
		try {
			$sorting_where = array(
				'csw.wall_no'=>array('eq',$wall_no),
			);
			$ret = $this->fetchsql(false)->distinct(true)->alias('csw')->field('csw.wall_no,GROUP_CONCAT(IF(swd.is_use=\'1\',swd.box_no,\'\')) AS box_nos')->join('sorting_wall_detail swd ON csw.wall_id=swd.wall_id')->where($sorting_where)->group('swd.wall_id')->select();
			$ret_str = $ret[0]['box_nos'];
			$ret_str = str_replace(',','',$ret_str);
			$ret_str = str_replace($ret[0]['wall_no'].'-',',',$ret_str);
			$ret_str = trim($ret_str,',');
			$result=array('status'=>0,'info'=>$ret_str);
			return $result;
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-getHasUseBoxByWallNo-'.$msg);
			E(self::PDO_ERROR);
		}
	}
	public function boxRelease($box_ids){
		try{
			$data['status']=0;
			$data['info']="释放成功";
			$is_rollback = false;
			$error_log = '';
			$sorting_wall_detail_model = M('sorting_wall_detail');
			$box_goods_detail_model = M('box_goods_detail');
			$stalls_less_goods_detail_model = M('stalls_less_goods_detail');
			$box_ids = empty($box_ids)?'':$box_ids;
			$stockout_info = $sorting_wall_detail_model->alias('swd')->field('GROUP_CONCAT(so.stockout_id) AS stockout_ids,GROUP_CONCAT(so.src_order_id) AS trade_ids')->join('LEFT JOIN stockout_order so ON so.stockout_id=swd.stockout_id')->where(array('box_id'=>array('in',$box_ids)))->find();
			if(empty($stockout_info)){return array('status' => 1,'info' 	 => '没有查询到对应订单信息',);}
			$this->startTrans();
			//更新分拣框货品明细
			$box_goods_detail_where = array(
				'trade_id'=>array('in',$stockout_info['trade_ids']),
				'sort_status'=>array('eq',0),
			);
			$error_log = 'box_goods_detail';
			$ret = $box_goods_detail_model->where($box_goods_detail_where)->delete();
			if($ret === false){$is_rollback = true;}
			//更新分拣框详情
			$error_log = 'sorting_wall_detail';
			$ret = $sorting_wall_detail_model->where(array('box_id'=>array('in',$box_ids)))->save(array('is_use'=>0,'stockout_id'=>0));
			if($ret === false){$is_rollback = true;}
			//更新缺货明细
			$error_log = 'stalls_less_goods_detail';
			//$ret = $stalls_less_goods_detail_model->where(array('trade_id'=>array('in',$stockout_info['trade_ids'])))->save(array('trade_status'=>1,'stalls_id'=>0));
			$ret = $stalls_less_goods_detail_model->where(array('trade_id'=>array('in',$stockout_info['trade_ids'])))->save(array('stalls_id'=>0));
			if($ret === false){$is_rollback = true;}
			if($is_rollback){$this->rollback();$data['status']=1;$data['info']=self::PDO_ERROR;\Think\Log::write($this->name.'-boxRelease-'.$error_log.'-更新失败');}
			$this->commit();
			return $data;
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			$this->rollback();
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$this->rollback();
		}
	}
	public function dynamic_search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc"){
		try{
			$rows=intval($rows);
			$page=intval($page);
			$where_sorting_wall = '';
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'wall_no':
						set_search_form_value($where_sorting_wall,$k,$v,'cdb',1,' AND ');
						break;
					case 'box_num':
						set_search_form_value($where_sorting_wall,$k,$v,'cdb',1,' AND ');
						break;
					case 'goods_num':
						set_search_form_value($where_sorting_wall,$k,$v,'cdb',1,' AND ');
						break;
					case 'type':
						if($v!='all')
						set_search_form_value($where_sorting_wall,$k,$v,'cdb',1,' AND ');
						break;
					case 'is_disabled':
						if($v!='all')
						set_search_form_value($where_sorting_wall,$k,$v,'cdb',1,' AND ');
						break;
				}
			}
			$where_sorting_wall=ltrim($where_sorting_wall, ' AND ');
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			$order=addslashes($order);
			$total = M('cfg_dynamic_box')->distinct(true)->alias('cdb')->field('cdb.wall_id as id')->where($where_sorting_wall);
			$m = clone $total;
			//$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->query($m->fetchsql(true)->count());
			$row = M('cfg_dynamic_box')->fetchsql(false)->distinct(true)->alias('cdb')->field('cdb.wall_id AS id,cdb.wall_no,cdb.goods_num,cdb.box_num,IF(cdb.type=1,\'分拣墙\',\'缺货墙\') as type,cdb.is_disabled,cdb.modified,cdb.created')->where($where_sorting_wall)->group('cdb.wall_id')->order($order)->select();
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
	public function getEditDynamicData($id){
		try{
			$res=M('cfg_dynamic_box')->alias('csw')->field('csw.wall_id as id,csw.wall_no,csw.box_num,csw.goods_num,csw.is_disabled,csw.type')->where(array('wall_id'=>$id))->find();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		} catch (BusinessLogicException $e) {
			$msg = $e->getMessage();
			SE($msg);
		} catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	public function delDynamic($ids){
		try{
			$data['status'] = 0;
			$data['info'] = '删除成功';
			$del_data = '';
			foreach($ids as $id){$del_data .= $id . ',';}
			$del_data = substr($del_data,0,-1);
			$this->startTrans();
			$sorting_wall_detail_model = M('sorting_wall_detail');
			$is_use = M('big_box_goods_map')->fetchSql(false)->field('rec_id')->alias('bbgm')->join('left join sorting_wall_detail swd on swd.box_no = bbgm.box_no')->where(array('bbgm.wall_id'=>array('in',$del_data),'swd.is_use'=>'1'))->find();
			if(!empty($is_use)){SE('当前分拣墙(缺货墙)正在使用中，不可删除！');}
			$del_wall_nos = $this->query("SELECT GROUP_CONCAT(wall_no) AS wall_nos FROM cfg_dynamic_box WHERE wall_id IN (%s)",$del_data);
			M('cfg_dynamic_box')->delete($del_data);
			$arr_sys_other_log = array(
				"type"        => "23",
				"operator_id" => get_operator_id(),
				"data"        => $del_data,
				"message"     => "删除分拣墙--分拣墙编号--“" . $del_wall_nos[0]['wall_nos'].'”',
				"created"     => date("Y-m-d G:i:s")
			);
			M("sys_other_log")->add($arr_sys_other_log);
			
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
	
	public function saveDynamic($sorting_wall_info){
		$data['status']=0;
		$data['info']="保存成功";
		$update_data=$sorting_wall_info;
		$sorting_wall_detail_model = M('sorting_wall_detail');
		try{
			$wall_type_map = ['缺货墙','分拣墙'];
			$this->startTrans();
			if($update_data['id']==0){//添加保存
				$is_wall_no = M('cfg_dynamic_box')->field('wall_id')->where(array('wall_no'=>$update_data['wall_no']))->find();
				if(!empty($is_wall_no)){
					$data['status']=1;
					$data['info']='分拣墙编号重复，请重新填写!';
					return $data;
				}
				$add_data=array(
					'wall_no'=>$update_data['wall_no'],
					'box_num' =>$update_data['box_num'],
					'goods_num' =>$update_data['goods_num'],
					'type' =>$update_data['type'],
					'is_disabled' =>$update_data['is_disabled'],
					'modified' => array('exp','NOW()'),
					'created' => array('exp','NOW()'),
				);
				$wall_type = $wall_type_map[$update_data['type']];
				if($update_data['type']){
//					$is_main_wall = $this->where(array('type'=>array('eq','1')))->find();
//					if(!empty($is_main_wall)){SE('目前系统中只能存在一个分拣墙！');}
				}else{
					$is_less_wall = M('cfg_dynamic_box')->where(array('type'=>array('eq','0')))->find();
					if(!empty($is_less_wall)){SE('目前系统中只能存在一个缺货墙！');}
				}
				$wall_id = M('cfg_dynamic_box')->add($add_data);
				$arr_sys_other_log = array(
					"type"        => "23",
					"operator_id" => get_operator_id(),
					"data"        => $wall_id,
					"message"     => "创建" . $wall_type . "--" . $wall_type . "编号--“" . $update_data['wall_no'].'”',
					"created"     => date("Y-m-d G:i:s")
				);
				M("sys_other_log")->add($arr_sys_other_log);
				$wall_detail = $this->field('wall_id')->find();
				$box_detail = M('sorting_wall_detail')->field('box_id')->find();
				if(empty($wall_detail)){
					$add_base_data=array(
						'wall_no'=>'A',
						'row_num' =>100,
						'column_num' =>100,
						'type' =>1,
						'is_disabled' =>0,
						'modified' => array('exp','NOW()'),
						'created' => array('exp','NOW()'),
					);
					$old_wall_id = $this->add($add_base_data);
				}
				if(empty($box_detail)){
					for($i=1; $i<101; $i++){
						for($j=1; $j<101; $j++){
							$add_tetail[] = array(
								'wall_id'=>$old_wall_id,
								'box_no'=>'A'.'-'.$i.'-'.$j,
								'row'=>$i,
								'column'=>$j,
								'modified' => array('exp','NOW()'),
								'created' => array('exp','NOW()'),
							);
						}
					}
					$sorting_wall_detail_model->addAll($add_tetail);
				}
			}else{//编辑保存
				$add_data=array(
					'wall_id'=>$update_data['id'],
					'wall_no'=>$update_data['wall_no'],
					'box_num' =>$update_data['box_num'],
					'goods_num' =>$update_data['goods_num'],
					'type' =>$update_data['type'],
					'is_disabled' =>$update_data['is_disabled'],
				);
				
				$is_use = M('big_box_goods_map')->fetchSql(false)->field('rec_id')->alias('bbgm')->join('left join sorting_wall_detail swd on swd.box_no = bbgm.box_no')->where(array('bbgm.wall_id'=>$update_data['id'],'swd.is_use'=>'1'))->find();
				$oldSortingWall = M('cfg_dynamic_box')->find($update_data['id']);
				if(!empty($is_use)){
					if($update_data['is_disabled']){//若分拣墙正在使用中，则不能停用
						SE('当前分拣墙(缺货墙)正在使用中，不可停用！');
					}
					if($oldSortingWall['goods_num']!= $add_data['goods_num']){
						SE('当前分拣墙(缺货墙)正在使用中，不可修改货品数！');
					}
					if($oldSortingWall['type']!= $add_data['type']){
						SE('当前分拣墙(缺货墙)正在使用中，不可修改属性！');
					}
				}
				if($update_data['type']){//若已存在分拣墙，则不能添加
				}else{
					$is_less_wall = M('cfg_dynamic_box')->where(array('type'=>array('eq','0')))->find();
					$is_less_wall_now = M('cfg_dynamic_box')->where(array('type'=>array('eq','0'),'wall_id'=>array('eq',$update_data['id'])))->find();
					if(!empty($is_less_wall)&&empty($is_less_wall_now)){SE('目前系统中只能存在一个缺货墙！');}
				}
				$save_ret = M('cfg_dynamic_box')->save($add_data);
				$arr_sys_other_log=array();
				$wall_type = $wall_type_map[$oldSortingWall['type']];
				if($oldSortingWall['wall_no']!= $add_data['wall_no']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--'.$wall_type.'编号--从“' . $oldSortingWall["wall_no"] .'”  到  “'. $add_data['wall_no'].'”'
					);
				}
				if($oldSortingWall['box_num']!= $add_data['box_num']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--框数--从“' . $oldSortingWall["box_num"] .'”  到  “'. $add_data['box_num'].'”'
					);
				}
				if($oldSortingWall['goods_num']!= $add_data['goods_num']){
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--货品数--从“' . $oldSortingWall["goods_num"] .'”  到  “'. $add_data['goods_num'].'”'
					);
				}
				if($oldSortingWall['is_disabled']!= $add_data['is_disabled']){
					if($oldSortingWall["is_disabled"]=='0'){
						$old_is_disabled="否";
					}else{
						$old_is_disabled="是";
					}
					if($add_data["is_disabled"]=='0'){
						$is_disabled="否";
					}else{
						$is_disabled="是";
					}
					$arr_sys_other_log[]=array(
						'type'=>"23",
						'operator_id'=>get_operator_id(),
						'created'=>date("Y-m-d G:i:s"),
						'data'=>$save_ret,
						'message' =>'编辑'.$wall_type.'--停用--从“' . $old_is_disabled .'”  到  “'. $is_disabled.'”'
					);
				}
				M("sys_other_log")->addAll($arr_sys_other_log);
				$up_data=array(
					'wall_id'=>$update_data['id'],
					'modified' => array('exp','NOW()'),
				);
				M('cfg_dynamic_box')->save($up_data);
			}
			$this->commit();
		}catch(BusinessLogicException $e){
			$data['status']=1;
			$data['info']=$e->getMessage();
			$this->rollback();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data['status']=1;
			$data['info']=self::PDO_ERROR;
			$this->rollback();
		}
		return $data;
	}

	public function pickGoodsAutoStockOut($trade_ids,$type_str=''){
		try{
			$trade_ids = trim($trade_ids,',');
			$trade_ids_arr = explode(',',$trade_ids);
			$operator = get_operator_id();
			$stock_spec_model = D('Stock/StockSpec');
			$sales_trade_log_model = D('Trade/SalesTradeLog');
			$stock_spec_position_model = D('Stock/StockSpecPosition');
			$stalls_less_goods_detail_model = M('stalls_less_goods_detail');
			$setting_config = get_config_value(array('stalls_system_init','cancel_sorting_order_auto_stockout'),array(0,0));
			if($setting_config['stalls_system_init']==1&&$setting_config['cancel_sorting_order_auto_stockout']==1){
				foreach($trade_ids_arr as $trade_id){
					$stockin_info = $stalls_less_goods_detail_model->field('rec_id,spec_id,trade_id,trade_no,stalls_id,warehouse_id,trade_status,stockin_status,sort_status,sum(stockin_status) as stockout_num')->where(array('trade_id'=>array('eq',$trade_id),'stockin_status'=>array('eq','1')))->group('spec_id')->select();
					if(empty($stockin_info)){
						$is_insert_log = false;
					}else{
						foreach($stockin_info as $item){
							$stock_spec = $stock_spec_model->field('stock_num')->where(array("spec_id"=>$item['spec_id'],"warehouse_id"=>$item['warehouse_id']))->find();
							$stock_spec_model->where(array("spec_id"=>$item['spec_id'],"warehouse_id"=>$item['warehouse_id']))->save(array("stock_num"=>$stock_spec['stock_num']-$item['stockout_num']));

							$stock_spec_position = $stock_spec_position_model->field('stock_num')->where(array("spec_id"=>$item['spec_id'],"warehouse_id"=>$item['warehouse_id']))->find();
							$stock_spec_position_model->where(array("spec_id"=>$item['spec_id'],"warehouse_id"=>$item['warehouse_id']))->save(array("stock_num"=>$stock_spec_position['stock_num']-$item['stockout_num']));
						}
						$is_insert_log = true;
					}
					// 插入订单日志
					if($is_insert_log){
						$message = '订单'.$type_str.'--档口订单已分拣入库的货品自动完成出库';
						$insert_sales_log_data[] = array(
							'type' => '173',
							'trade_id' => $trade_id,
							'operator_id'=>$operator,
							'message'=>$message,
						);
						$sales_trade_log_model->addTradeLog($insert_sales_log_data);
					}
				}
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-pickGoodsAutoStockOut-'.$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($this->name.'-pickGoodsAutoStockOut-'.$e->getMessage());
		}
	}
}

?>