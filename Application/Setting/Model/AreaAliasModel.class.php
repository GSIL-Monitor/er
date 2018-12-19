<?php

namespace Setting\Model;

use Think\Model;

class AreaAliasModel extends Model
{
    protected $tableName = 'cfg_logistics_area_alias';
    protected $pk = 'rec_id';

    public function queryAreaAlias(&$where_logistics_area_alias,$page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	//搜索表单-数据处理
    	$where='';
    	foreach ($search as $k=>$v){
    		if($v==='') continue;
    		switch ($k)
    		{   
    			case 'logistics_id':
    				set_search_form_value($where_logistics_area_alias, $k, $v,'laa_1', 2,' AND ');
    				break;
    		}
    	}
        $page=intval($page);
        $rows=intval($rows);
    	$limit = ($page - 1) * $rows . "," . $rows;//分页
    	$order = 'laa_1.'.$sort.' '.$order;
        $order = addslashes($order);
    	$logistics_area_alias_limit = 'SELECT laa_1.rec_id FROM cfg_logistics_area_alias laa_1 ';
        $logistics_area_alias_total = 'SELECT COUNT(1) AS total FROM cfg_logistics_area_alias laa_1 ';
        $flag = false;
        connect_where_str($where, $where_logistics_area_alias, $flag);
        $logistics_area_alias_limit .= $where . ' ORDER BY ' . $order . ' LIMIT ' . $limit;
        $logistics_area_alias_total .= $where;
        $logistics_area_alias_fields = 'SELECT laa_2.rec_id AS id, laa_2.path, cl.logistics_name AS logistics_id, laa_2.alias_name,laa_2.modified,laa_2.created FROM cfg_logistics_area_alias laa_2 ';
    	$sql = $logistics_area_alias_fields . 'INNER JOIN(' . $logistics_area_alias_limit . ') page ON page.rec_id=laa_2.rec_id LEFT JOIN cfg_logistics cl ON cl.logistics_id=laa_2.logistics_id ORDER BY laa_2.rec_id DESC';
        try 
    	{
    		$total=$this->query($logistics_area_alias_total);
    		$total=intval($total[0]['total']);
    		$list=$total?$this->query($sql):array();
    		$data=array('total'=>$total,'rows'=>$list);
    	}catch (\PDOException $e) 
    	{
			\Think\Log::write('search_logistics_area_alias:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
    }



    public function getRules()
    {
    	$rules=array(
    			//必须验证数据
                array('province_id','number','省市区不能为空！',1),
                array('city_id','number','省市区不能为空！',1),
                array('district_id','number','省市区不能为空！',1),
                //回调--验证
                array('logistics_id','checkLogistics','无效物流！',1,'callback',1),
                array('alias_name','require','地区别名不能为空！',0,'regex',2),
    	);
    	return $rules;
    }

    protected function checkLogistics($logistics_id)
    {
        return D('Setting/Logistics')->checkLogistics(intval($logistics_id));
    }

    public function validateLogisticsAreaAlias($alias)
    {
        try {
            if(!$this->validate($this->getRules())->create($alias))
            {
                SE($this->getError());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-validateLogisticsAreaAlias-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function getLogisticsAreaAlias($fields,$where=array(),$alias='')
    {
        try {
            $res = $this->alias($alias)->field($fields)->where($where)->find();
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-getLogisticsAreaAlias-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function addLogisticsAreaAlias($data)
    {
    	try {
    		if (empty($data[0])) {
    			$res = $this->add($data);
    		}else
    		{
    			$res = $this->addAll($data);
    		}
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-addLogisticsAreaAlias-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
        return $res;
    }

    public function updateLogisticsAreaAlias($data)
    {
    	try {
    		$res = $this->save($data);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-updateLogisticsAreaAlias-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
        return $res;
    }




    public function addAlias($alias,$user_id)
    {
    	$error_info='';
    	try 
    	{  
            $res_alias=$this->getLogisticsAreaAlias('rec_id',array('path'=>array('eq',$alias['path'])));
            $this->startTrans();
            if(empty($res_alias))
            {
                $alias['created']=date('Y-m-d H:i:s',time());
                $alias_id=$this->addLogisticsAreaAlias($alias);
                $log=array(
                    'type'=>8,
                    'operator_id'=>$user_id,
                    'data'=>$alias_id,
                    'message'=>'新增地区别名策略',
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }else if($res_alias['rec_id']>0)
            {
                $alias['rec_id']=$res_alias['rec_id'];
                $this->updateLogisticsAreaAlias($alias);
                $log=array(
                    'type'=>8,
                    'operator_id'=>$user_id,
                    'data'=>$alias['rec_id'],
                    'message'=>'更新地区别名策略',
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-addalias-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    public function editAlias($alias,$user_id)
    {
    	$error_info='';
    	try
    	{   
            $res_alias=$this->getLogisticsAreaAlias('rec_id',array('rec_id'=>array('neq',$alias['rec_id']),'path'=>array('eq',$alias['path'])));
            if(!empty($res_alias['rec_id']))
            {
                SE('同一物流公司的同一收货地址只能新建一个别名');
            }
    		$this->startTrans();
            $this->updateLogisticsAreaAlias($alias);
            $log=array(
                'type'=>8,
                'operator_id'=>$user_id,
                'data'=>$alias['rec_id'],
                'message'=>'更新地区别名策略',
                'created'=>date('Y-m-d H:i:s',time())
            );
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e)
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-editAlias-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    public function deleteAreaAlias($logistics_area_alias_id,$user_id)
    {
    	try
    	{
    		$this->startTrans();
            $this->where(array('rec_id'=>array('eq',$logistics_area_alias_id)))->delete();
            $log=array(
                'type'=>8,
                'operator_id'=>$user_id,
                'data'=>$logistics_area_alias_id,
                'message'=>'删除地区别名策略',
                'created'=>date('Y-m-d H:i:s',time())
            );
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-deleteAreaAlias-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
}