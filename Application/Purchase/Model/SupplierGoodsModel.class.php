<?php
namespace Purchase\Model;
use Think\Model;
use Think\Exception;
use Think\Exception\BusinessLogicException;
class SupplierGoodsModel extends Model{
	protected $tableName = 'purchase_provider_goods';
    protected $pk        = 'rec_id';
	protected $_validate = array(
        array('spec_no','require','商家编码不能为空!'),
        array('provider_name','require','供应商不能为空!'),
		array('price','checkPrice','成本价不合法!',1,'callback'),
    );
    protected $patchValidate = true;
	protected function checkPrice($cost_price)
    {
        return check_regex('positive_number',$cost_price);
    }
	
	public function search($page = 1, $rows = 20, $search = array(), $sort ="id", $order="desc"){
		try{
			$rows = intval($rows);
			$page = intval($page);
			$gs_where = '';
			$gg_where = '';
			D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					case 'provider_goods_no':
					set_search_form_value($gs_where,$k,$v,'ppg',1,' and ');
					break;
					case 'spec_no':
					set_search_form_value($gs_where,$k,$v,'gs',10,' and ');
					break;
					case 'provider':
					set_search_form_value($gs_where,'provider_id',$v,'ppg',2,' and ');
					break;
					case 'provider_ids':
					set_search_form_value($gs_where,'provider_id',$v,'ppg',2,' and ');
					break;
					case 'goods_name':
					set_search_form_value($gg_where,$k,$v,'gg',1,' and ');
					break;
					case 'goods_no':
					set_search_form_value($gg_where,$k,$v,'gg',1,' and ');
					break;
				}
			}
			$order = $sort.' '.$order;
			$limit = ($page-1)*$rows.','.$rows;
			$gs_where = ltrim($gs_where,' and ');
			$gg_where = ltrim($gg_where,' and ');
			if(!empty($gs_where)){
				if(!empty($gg_where)){
					$gg_where = $gg_where.' and '.$gs_where;
					$total = $this->distinct(true)->alias('ppg')->field('ppg.rec_id as id')->join('left join goods_spec gs on gs.spec_id=ppg.spec_id')->join('left join goods_goods gg on gg.goods_id=gs.goods_id')->where($gg_where);

				}else{
					$total = $this->distinct(true)->alias('ppg')->field('ppg.rec_id as id')->join('left join goods_spec gs on gs.spec_id=ppg.spec_id')->where($gs_where);
				}
			}else if(!empty($gg_where)){
				$total = $this->distinct(true)->alias('ppg')->field('ppg.rec_id as id')->join('left join goods_spec gs on gs.spec_id=ppg.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id')->where($gg_where);
			}else{
				$total = $this->distinct(true)->alias('ppg')->field('ppg.rec_id as id');
			}
		
			$m = clone $total;
			$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->query($m->fetchsql(true)->count());
			$num = $num[0]['tp_count'];
			$row =$num?$this->distinct(true)->alias('ppg')->field('ppg.rec_id as id,ppg.provider_goods_no,ppg.price,ppg.is_disabled,gs.spec_no,ppg.spec_id,ppg.provider_id,gg.goods_id,gg.goods_name,gg.goods_no,pp.provider_name,ppg.created')->join('inner join('.$total_sql.') t on t.id = ppg.rec_id left join goods_spec gs on gs.spec_id = ppg.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id left join purchase_provider pp on pp.id=ppg.provider_id')->order($order)->select():array();
			
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			 SE(self::PDO_ERROR);
		}
		return $data = array('total'=>$num,'rows'=>$row);
	} 
	
	
	
	public function saveSupplier($info){
		try{
			foreach($info['insert'] as $v){
				$provider_id = D('Setting/PurchaseProvider')->field('id')->where(array('provider_name'=>$v['provider_name']))->select();
				if(empty($provider_id) && empty($info['provider_id'])){
					SE('未获取到供应商，请添加供应商');
				}

				$insert_data = array(
					'spec_id'=>$v['spec_id'],
					'provider_id'=>$info['provider_id']?$info['provider_id']:$provider_id[0]['id'],
					//'provider_goods_no'=>isset($v['provider_goods_no'])?$v['provider_goods_no']:'',
					'price'=>$v['market_price'],
					'created'=>array('exp','NOW()'),
				);
				$search_data = $this->field('rec_id')->where(array('spec_id'=>$v['spec_id'],'provider_id'=>$info['provider_id']))->select();
				$this->startTrans();
				if(empty($search_data)){
					$this->add($insert_data);
				}else{
					$id = $search_data[0]['rec_id'];
					$this->where(array('rec_id'=>$id))->save($insert_data);
				}
			}	
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			 SE(self::PDO_ERROR);
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		$this->commit();
	}
	public function remove($id){
		try{
			$this->startTrans();
			$this->where(array('rec_id'=>$id))->delete();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			 SE(self::PDO_ERROR);
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			$this->rollback();
			 SE(self::PDO_ERROR);
		}
		$this->commit();
	}
	public function uploadExcel($data,&$result){
		try{
			$operator_id = get_operator_id();
			$check_field = array('spec_no','price','provider_name');
			foreach($data as $tmp_key=>$tmp_value){
				if(!$this->create($tmp_value)){
					$data[$tmp_key]['status']=1;
					$data[$tmp_key]['result']='失败';
					foreach($check_field as $field){
						$tmp_row = $this->getError();
						if(isset($tmp_row["{$field}"])){
							$data[$tmp_key]['message'] = $tmp_row[$field];
							break;
						}
					}
				}
			}
			foreach($data as $info){
				 
				$insert_data = $this->execute("insert into tmp_import_detail(`spec_no`,`provider_name`,`price`,`status`,`result`,`line`,`message`)values('{$info['spec_no']}','{$info['provider_name']}',".(float)$info['price'].",".(int)$info['status'].",'{$info['result']}',".(int)$info['line'].",'{$info['message']}')");

			}

			$exist_provider_sql = "update tmp_import_detail tid left join purchase_provider pp on pp.provider_name = tid.provider_name set "
								  ." tid.provider_id = pp.id ,tid.status = if(pp.id is null,1,0),tid.result = if(pp.id is null,'失败',''),tid.message = if(pp.id is null,'供应商不存在','') where tid.status=0 ";
			$exist_provider = $this->execute($exist_provider_sql);
			$exist_spec_sql = "update tmp_import_detail tid left join goods_spec gs on gs.spec_no = tid.spec_no set "
								  ." tid.spec_id = gs.spec_id ,tid.status = if(gs.spec_id is null,1,0),tid.result = if(gs.spec_id is null,'失败',''),tid.message = if(gs.spec_id is null,'商家编码不存在','') where tid.status=0 ";
			$exist_spec = $this->execute($exist_spec_sql);
			$exist_data = $this->execute('select spec_no from tmp_import_detail where status= 0');
			if(empty($exist_data)){
				$error_data = $this->query("select line as id,message,status,result,spec_no from tmp_import_detail where status =1");
				if(!empty($error_data)){
					$result['status'] = 2;
					$result['data'] = $error_data;
				}
				return;
			}
			$purchase_provider_goods_data = $this->query('select spec_id,provider_id,price,provider_goods_no from tmp_import_detail where status=0');
			foreach($purchase_provider_goods_data as $k=>$v){
				$add_data = array(
					'spec_id'=>$v['spec_id'],
					'provider_id'=>$v['provider_id'],
					'price'=>$v['price'],
					//'provider_goods_no'=>$v['provider_goods_no']
				);
				
				$this->add($add_data,$options=array(),$replace=true);
			}
			$error_all_data = $this->query('select line as id ,message,status,result,spec_no from tmp_import_detail where status=1');
			if(!empty($error_all_data)){
				$result['status'] = 2;
				$result['data'] = $error_all_data;
			}
		}catch (\PDOException $e) {
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
                 $result['status'] = 1;
                $result['msg'] = $msg;
				return;
				
                
            } catch (\Exception $e){
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
                $result['status'] = 1;
                $result['msg'] = $msg;
                return;
            }
	}
}