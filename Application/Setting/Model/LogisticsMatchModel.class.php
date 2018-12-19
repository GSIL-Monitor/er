<?php

namespace Setting\Model;

use Think\Model;

class LogisticsMatchModel extends Model
{
	protected $tableName='cfg_logistics_match';
	protected $pk='rec_id';

	public function getRules()
	{
		$rules=array(
				array('logistics_id','checkLogistics','无效默认物流',1,'callback',1),
				array('amount_logistics_id','checkLogistics','已付金额1无效物流',1,'callback',1),
				array('amount_logistics_id2','checkLogistics','已付金额2无效物流',1,'callback',1),
				array('amount_logistics_id3','checkLogistics','已付金额3无效物流',1,'callback',1),
				array('weight_logistics_id','checkLogistics','重量等级1无效物流',1,'callback',1),
				array('weight_logistics_id2','checkLogistics','重量等级2无效物流',1,'callback',1),
				array('weight_logistics_id3','checkLogistics','重量等级3无效物流',1,'callback',1),
				array('except_logistics_id','checkLogistics','不到改用1无效物流',1,'callback',1),
				array('except_logistics_id2','checkLogistics','不到改用2无效物流',1,'callback',1),
				array('paid_amount',check_regex('double'),'非法已付金额1！',1,'regex'),
				array('paid_amount2',check_regex('double'),'非法已付金额2！',1,'regex'),
				array('paid_amount3',check_regex('double'),'非法已付金额3！',1,'regex'),
				array('weight',check_regex('double'),'非法重量等级1！',1,'regex'),
				array('weight2',check_regex('double'),'非法重量等级2！',1,'regex'),
				array('weight3',check_regex('double'),'非法重量等级3！',1,'regex'),
				array('province','number','省市区不能为空！',1),
				array('city','number','省市区不能为空！',1),
				array('district','number','省市区不能为空！',1),
		);
		return $rules;
	}
	
	protected function checkLogistics($logistics_id)
	{
		if($logistics_id==0)
		{
			return true;
		}else
		{
			return D('Setting/Logistics')->checkLogistics(intval($logistics_id));
		}
	}
	
	public function validateLogisticsMatch($match)
	{
		try
		{
			if(!$this->validate($this->getRules())->create($match))
			{
				SE($this->getError());
			}
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-validateLogisticsMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function addLogisticsMatch($data)
	{
		try
		{
			if(empty($data[0]))
			{
				$res=$this->add($data);
			}else
			{
				$res=$this->addAll($data);
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-addLogisticsMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function updateLogisticsMatch($data,$where)
	{
		try
		{
			$res=$this->where($where)->save($data);
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-updateLogisticsMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function getLogisticsMatch($fields,$where=array(),$alias='')
	{
		try
		{
			$res = $this->alias($alias)->field($fields)->where($where)->select();
		}catch(\PODEcxeption $e){
			\Think\Log::write($this->name.'-getLogisticsMatch-'.$e->getMeaasge());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function queryLogisticsMatch(&$where_logistics_match,$page=1, $rows=20, $search=array(), $sort='path', $order='asc') 
	{
		$where='';
		$page=intval($page);
		$rows=intval($rows);
		if($sort=='shop_target_id'||$sort=='warehouse_target_id'){
			$sort='target_id';
		}
		$limit=($page - 1) * $rows . ',' . $rows ;
		$order= 'lm_t.'.$sort.' '.$order;
		$order=addslashes($order);
		$group='logistics_id,amount_logistics_id,amount_logistics_id2,amount_logistics_id3,weight_logistics_id,weight_logistics_id2,weight_logistics_id3,
				paid_amount, paid_amount2, paid_amount3, weight, weight2, weight3';
		$logistics_match_limit = 'SELECT lm_t.rec_id FROM cfg_logistics_match lm_t';
		$logistics_match_total = 'SELECT COUNT(*) AS total FROM (SELECT COUNT(1) FROM cfg_logistics_match lm_t';
		$flag=false;
		connect_where_str($where, $where_logistics_match, $flag);
		$logistics_match_limit .= $where . ' ORDER BY ' . $order ;
		$logistics_match_total .= $where . ' GROUP BY ' . $group .')tmp';
		$logistics_match_fields = 'SELECT GROUP_CONCAT(lm.rec_id) AS id, GROUP_CONCAT(lm.path) AS path, lm.level, lm.target_id AS target, cl.logistics_name AS logistics_id, 
									cl1.logistics_name AS amount_logistics_id, cl2.logistics_name AS amount_logistics_id2, cl3.logistics_name AS amount_logistics_id3, 
									cl4.logistics_name AS weight_logistics_id, cl5.logistics_name AS weight_logistics_id2, cl6.logistics_name AS weight_logistics_id3,
									lm.paid_amount, lm.paid_amount2, lm.paid_amount3, lm.weight, lm.weight2, lm.weight3, lm.created, lm.modified, 
									lm.except_words_weight, lm.except_words_weight2, lm.except_words_weight3, lm.except_words, lm.except_words2, 
									cl7.logistics_name AS except_logistics_id, cl8.logistics_name AS except_logistics_id2, 
									IF(lm.target_id=0,"全部",cs.shop_name) AS shop_target_id,IF(lm.target_id=0,"全部",cw.name) AS warehouse_target_id 
									FROM cfg_logistics_match lm 
									LEFT JOIN cfg_logistics cl1 ON cl1.logistics_id=lm.amount_logistics_id 
									LEFT JOIN cfg_logistics cl2 ON cl2.logistics_id=lm.amount_logistics_id2 
									LEFT JOIN cfg_logistics cl3 ON cl3.logistics_id=lm.amount_logistics_id3 
									LEFT JOIN cfg_logistics cl4 ON cl4.logistics_id=lm.weight_logistics_id 
									LEFT JOIN cfg_logistics cl5 ON cl5.logistics_id=lm.weight_logistics_id2 
									LEFT JOIN cfg_logistics cl6 ON cl6.logistics_id=lm.weight_logistics_id3 
									LEFT JOIN cfg_logistics cl7 ON cl7.logistics_id=lm.except_logistics_id 
									LEFT JOIN cfg_logistics cl8 ON cl8.logistics_id=lm.except_logistics_id2
									LEFT JOIN cfg_shop cs ON cs.shop_id=lm.target_id
									LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=lm.target_id';
		$sql = $logistics_match_fields . ' INNER JOIN(' . $logistics_match_limit . ')page ON page.rec_id = lm.rec_id LEFT JOIN cfg_logistics cl ON cl.logistics_id = lm.logistics_id 
				GROUP BY logistics_id,target_id,amount_logistics_id,amount_logistics_id2,amount_logistics_id3,weight_logistics_id,weight_logistics_id2,weight_logistics_id3,
				lm.paid_amount, lm.paid_amount2, lm.paid_amount3, lm.weight, lm.weight2, lm.weight3 
				ORDER BY lm.path ASC  LIMIT ' . $limit;
		try
		{
			$total=$this->query($logistics_match_total);
			$total=intval($total[0]['total']);
			$this->execute("SET SESSION group_concat_max_len=26000");
			$list=$total?$this->query($sql):array();			
			$this->execute("SET SESSION group_concat_max_len=1024");
			$data= array('total'=>$total,'rows'=>$list);
		}catch (\PDOException $e){
			$this->execute("SET SESSION group_concat_max_len=1024");
			\Think\Log::write('search_logistics_match:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function addMatch($match,$user_id)
	{
		unset($match['area']);
		$error_info='';
		try
		{
			$res_match=$this->getLogisticsMatch('rec_id',array('path'=>array('eq',$match['path'])));
			$this->startTrans();
			if(empty($res_match))
			{
				$match['created']=date('Y-m-d H:i:s',time());
				$match_id=$this->addLogisticsMatch($match);
				$log=array(
					'type'=>8,
					'operator_id'=>$user_id,
					'data'=>$match_id,
					'message'=>'新建物流匹配策略',
					'created'=>date('Y-m-d H:i:s',time())
				);
			}else if($res_match[0]['rec_id']>0)
			{
				$match['rec_id']=$res_match[0]['rec_id'];
				$this->updateLogisticsMatch($match);
				$log=array(
					'type'=>8,
					'operator_id'=>$user_id,
					'data'=>$match['rec_id'],
					'message'=>'更新物流匹配策略',
					'created'=>date('Y-m-d H-i-s',time())
				);
			}
			M('sys_other_log')->add($log);
			$this->commit();
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-'.$error_info.'-addMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function editMatch($match,$user_id){
		$error_info='';
		try
		{
			$res_match=$this->getLogisticsMatch('rec_id',array('rec_id'=>array('neq',$match['rec_id']),'path'=>array('eq',$match['path'])));
			if(!empty($res_match))
			{
				SE('同一地址只能建一条物流匹配策略');
			}
			$this->startTrans();
			$this->updateLogisticsMatch($match);
			$log=array(
					'type'=>8,
					'operator_id'=>$user_id,
					'data'=>$match['rec_id'],
					'message'=>'更新物流匹配策略',
					'created'=>date('Y-m-d H:i:s',time())
			);
			M('sys_other_log')->add($log);
            $this->commit();
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-'.$error_info.'-editMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function deleteMatch($logistics_match_id,$user_id)
	{
		try 
		{
			$this->startTrans();
			$this->where(array('rec_id'=>array('in',$logistics_match_id)))->delete();
			$log=array(
					'type'=>8,
					'operator_id'=>$user_id,
					'data'=>$logistics_match_id,
					'message'=>'删除物流匹配策略',
					'created'=>date('Y-m-d H:i:s',time())
			);
			M('sys_other_log')->add($log);
			$this->commit();
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-deleteLogisticsMatch-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}

	public function changeLogisticsMatchModel($data)
	{
		try 
		{
			$this->startTrans();
			$user_id=get_operator_id();
			$result = D('System')->getOneSysteSetting('logistics_match_mode');			
			if($data == $result[0]["value"]){return true;}
			// 更新配置值
			$update_data['value']=$data;
			M('cfg_setting')->where('`key`=\'logistics_match_mode\'')->save($update_data);
			// 清空现有所有配置
			$this->where('rec_id!=0')->delete();
			$model_value=array(
                    '1'=>'按仓库独立设置',
                    '2'=>'按店铺独立设置',
            );
			$log = array(
                        "type"        => 8,
                        'operator_id'=>$user_id,
						'data'=>$data,
						'message'=>'修改物流匹配模式--从'.$model_value[$result[0]["value"]].'到'.$model_value[$data],
						'created'=>date('Y-m-d H:i:s',time())
             );
            M('sys_other_log')->add($log);
			$this->commit();
			return true;
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-changeLogisticsMatchModel-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}

}
