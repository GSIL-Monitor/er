<?php
namespace Setting\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;
class WarehousePositionModel extends Model{
	protected $tableName = 'cfg_warehouse_position';
    protected $pk = 'rec_id';
	protected $_validate   = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('is_disabled', array(1, 0), '停用类型不正确!', 1, 'in', 3),
        array('position_no,warehouse_id', '', '货位编号重复，请重新填写!', 1, 'unique', 3),
    );
	public function insertPosition($position_data){
		try{
			if(empty($position_data[0])){
				$res=$this->add($position_data);
			}else{
				$res=$this->addAll($position_data);
			}
			return $res;
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
	}
	public function searchPosition($page,$rows,$search=array(),$sort='cwp.warehouse_id',$order='desc'){

		$where_warehouse_position=array();
		foreach($search as $k=>$v){
			if($v==="")continue;
			switch($k){
				case 'warehouse_id':
					set_search_form_value($where_warehouse_position,$k,$v,'cwp',2);
					break;
				case 'position_no':
					set_search_form_value($where_warehouse_position,$k,$v,'cwp',1);
					break;
				case 'is_disabled':
					set_search_form_value($where_warehouse_position,$k,$v,'cwp',1);
					break;
				default:
					continue;
			}
		}
		if($search['show_disabled']!=1){
			//是否显示停用的货位
			$where_warehouse_position['is_disabled']=array('eq','0');
		}
		$page  = intval($page);
        $rows  = intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$order = $sort . ' ' . $order.' ,cwp.created desc';//排序
		$order=addslashes($order);
		try{
			$m=$this->alias('cwp');
			$m=$this->fetchSql(true)->where($where_warehouse_position);
			$page=clone $m;
			$sql_total=$m->fetchSql(true)->count();
			$total=$m->query($sql_total);
			$total=$total[0]['tp_count'];
			$sql_page=$page->fetchSql(true)->field('cwp.rec_id id')->order($order)->limit($limit)->group('cwp.rec_id')->select();
			$sql="SELECT cwp.rec_id as id,cw.warehouse_id,cw.name,cwp.position_no,cwp.is_disabled,cwp.created,cwp.modified FROM cfg_warehouse_position cwp LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=cwp.warehouse_id ". 'JOIN ('. $sql_page .') page on page.id=cwp.rec_id'
				 . ' order by '. $order;
			$list=$total?$m->query($sql):array();
			$data=array('total'=>$total,'rows'=>$list);
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function savePosition($position_info){
		$data['status']=0;
		$data['info']="";
		$update_data=$position_info;
		try{
			if(!$this->create($update_data)){
				$data['status']=1;
				$data['info']=$this->getError();
				return $data;
			}
			$this->startTrans();
			if($update_data['id']==0){
                $zone_id=D('Setting/WarehouseZone')->alias('cwz')->field('cwz.zone_id')->where(array('warehouse_id'=>$update_data['warehouse_id']))->find();
                $add_data=array(
                    'zone_id'=>$zone_id['zone_id'],
                    'warehouse_id'=>$update_data['warehouse_id'],
                    'position_no' =>$update_data['position_no'],
                    'is_disabled' =>$update_data['is_disabled'],
                    'created'     =>date("Y-m-d H:i:s", time()),
                );
				$this->add($add_data);
			}else{
				$add_data=array(
					'rec_id'=>$update_data['id'],
					'position_no' =>$update_data['position_no'],
					'is_disabled' =>$update_data['is_disabled'],
				);
				if($update_data['is_disabled']){
					$num = D('Stock/StockSpecPosition')->where(array('position_id'=>$update_data['id']))->sum('stock_num');
					$count = D('Stock/StockSpecPosition')->where(array('position_id'=>$update_data['id']))->count();
					if((is_numeric($num) && $num > 0) )
					{
						SE('当前货位存在货品,不能停用');
					}else{
						if($update_data['id']<0)
						{
							SE('默认货位不能停用');
						}
						$dele_res = D('Stock/StockSpecPosition')->where(array('position_id'=>$update_data['id'],'stock_num'=>array('EQ',0)))->delete();
						if($dele_res != $count){
							SE('当前货位存在货品,不能停用!');
						}
						$update_spec_res = D('Stock/StockSpec')->where(array('last_position_id'=>$update_data['id']))->save(array('last_position_id'=>0));
					}
				}
				$this->save($add_data);
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
	public function getEditPositionData($id){
		try{
			$res=$this->alias('cwp')->field('cwp.rec_id as id,cwp.position_no,cwp.zone_id,cw.name,cwp.is_disabled,cwp.warehouse_id')->join("LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=cwp.warehouse_id")->where(array('rec_id'=>$id))->find();
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

	/*
	 * 货位导入函数
	 * */
	public function importSpec($data){
		try {
			$wh=D('Warehouse');
			$wz=D('WarehouseZone');
			$wp=D('WarehousePosition');
			$wh_find['name']=$data['name'];
			$wh_find_rst=$wh->field('warehouse_id')->where($wh_find)->find();
			if(!$wh_find_rst){
				SE("该仓库不存在，请先创建仓库");
			}else{
				$wp_find=array(
						'warehouse_id'=>$wh_find_rst['warehouse_id'],
						'position_no'=>$data['position_no']
				);
				$wp_find_rst=$wp->where($wp_find)->find();
				if($wp_find_rst){
					SE("该货位已经存在");
				}else{
					$wz_find['warehouse_id']=$wh_find_rst['warehouse_id'];
					$wz_find_rst=$wz->field('zone_id')->where($wz_find)->find();
					$wp_add=array(
							'warehouse_id'=>$wh_find_rst['warehouse_id'],
							'zone_id'=>$wz_find_rst['zone_id'],
							'position_no'=>$data['position_no'],
							'is_disabled'=>0
					);
					$wp_add_rst=$this->insertPosition($wp_add);
					if(!$wp_add_rst){
						SE("货位分区添加失败");
					}
				}
			}
		} catch (\PDOException $e) {
			SE($e->getMessage());
		} catch (\Exception $e) {
			SE($e->getMessage());
		}
	}


}
