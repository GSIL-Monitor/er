<?php

namespace Setting\Model;

use Think\Model;

class RemarkExtractModel extends Model
{
	
	protected $tableName='cfg_trade_remark_extract';
	protected $pk='rec_id';
	
	public function getRules($type){
		$rules=array(
				array('keyword','require','关键词不能为空!',1),
				array('type',array(1,2,3,4,5,6),'无效处理类型',1,'in'),
		);
		switch ($type){
			case 1:$rules[]=array('target','checkLogistics','无效物流',1,'callback',1);break;
			case 2:$rules[]=array('target','checkFlag','无效标记',1,'callback');break;
			case 4:$rules[]=array('target','checkWarehouse','无效仓库',1,'callback');break;
			case 6:$rules[]=array('target','checkFreezeReason','无效冻结原因',1,'callback');break;
		}
		return $rules;
	}
	
	protected function checkLogistics($logistics_id)
    {
        return D('Setting/Logistics')->checkLogistics(intval($logistics_id));
    }
	protected function checkFlag($flag_id)
	{
		return D('Setting/Flag')->checkFlag($flag_id,1);
	}
	protected function checkWarehouse($warehouse_id)
	{
		return D('Setting/Warehouse')->checkWarehouse(intval($warehouse_id));
	}
	protected function checkFreezeReason($reason_id)
	{
		return D('Setting/CfgOperReason')->checkFreezeReason(intval($reason_id));	
	}
	
	public function validateRemarkExtract($remark)
	{
		try
		{
			if(!$this->validate($this->getRules($remark['type']))->create($remark))
			{
				SE($this->getError());
			}
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-validateRemarkExtract-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function addRemarkExtract($data){
		try{
			if(empty($data[0])){
				$res=$this->add($data);
			}else{
				$res=$this->addAll($data);
			}
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-addRemarkExtract-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function updateRemarkExtract($data,$where){
		try{
			$res=$this->where($where)->save($data);
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-updateRemarkExtract-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function getRemarkExtract($fields,$where=array(),$alias=''){
		try
		{
			$res = $this->alias($alias)->field($fields)->where($where)->find();
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-getRemarkExtract-'.$e->getMeaasge());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	
	public function queryRemarkExtract($where,$page=1,$rows=20,$sort='rec_id',$order='desc'){
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . ',' . $rows ;
		$order= 're_t.'.$sort.' '.$order;
		$order=addslashes($order);
		$cs_remark_limit = 'SELECT re_t.rec_id FROM cfg_trade_remark_extract re_t ';
		$cs_remark_total = 'SELECT COUNT(1) AS total FROM cfg_trade_remark_extract re_t ';
		$flag=false;
		$cs_remark_limit .=$where . ' ORDER BY ' . $order . ' LIMIT ' . $limit;
		$cs_remark_total .=$where;
		$cs_remark_fields = 'SELECT re.rec_id AS id, re.keyword, re.class, re.type, re.target, re.remark, re.is_disabled, re.created, re.modified 
							FROM cfg_trade_remark_extract re ';
		$sql = $cs_remark_fields . ' INNER JOIN ('.$cs_remark_limit.') page ON page.rec_id = re.rec_id ORDER BY re.rec_id DESC';
		try{
			$total=$this->query($cs_remark_total);
			$total=intval($total[0]['total']);
			$list=$total?$this->query($sql):array();
			$data=array('total'=>$total,'rows'=>$list);
		}catch(\PDOException $e){
			\Think\Log::write('search_cs_remark_extract'.$e->getMessage());
			$data=array('totla'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function addRemark($remark,$user_id){
		$error_info='';
		try{
			$res_remark=$this->getRemarkExtract('rec_id',array('keyword'=>array('eq',$remark['keyword']),'class'=>array('eq',$remark['class']),'type'=>array('eq',$remark['type'])));
			if(!empty($res_remark['rec_id']))
			{
				SE('相同关键字的策略已经存在');
			}
			$this->startTrans();
			$log_message=array('新建客服备注提取策略','新建客户备注提取策略');
			$remark['created']=date('Y-m-d H:i:s',time());
			$remark_id=$this->addRemarkExtract($remark);
			$log=array(
					'type'=>9,
					'operator_id'=>$user_id,
					'data'=>$remark_id,
					'message'=>$log_message[$remark['class']-1],
					'created'=>date('Y-m-d H:i:s',time()),
			);
			M('sys_other_log')->add($log);
			$this->commit();
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-'.$error_info.'-addRemark-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function editRemark($remark,$user_id){
		$error_info='';
		try{
			$this->startTrans();
			$this->updateRemarkExtract($remark);
			$log_message=array('更新客服备注提取','更新客户备注提取');
			$log=array(
					'type'=>9,
					'operator_id'=>$user_id,
					'data'=>$remark['rec_id'],
					'message'=>$log_message[$remark['class']-1],
					'created'=>date('Y-m-d H:i:s',time())
			);
			M('sys_other_log')->add($log);
			$this->commit();
		}catch(\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-'.$error_info.'-editRemark-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	public function deleteRemark($remark_extract_id,$user_id,$class){
		try{
			$this->startTrans();
			$this->where(array('rec_id'=>array('eq',$remark_extract_id)))->delete();
			$log_message=array('删除客服备注提取策略','删除客户备注提取策略');
			$log=array(
					'type'=>9,
					'operator_id'=>$user_id,
					'data'=>$remark_extract_id,
					'message'=>$log_message[$class-1],
					'created'=>date('Y-m-d H:i:s',time()),
			);
			M('sys_other_log')->add($log);
			$this->commit();
		}catch (\PDOException $e){
			$this->rollback();
			\Think\Log::write($this->name.'-deleteRemarkExtract-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
}