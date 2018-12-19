<?php

namespace Setting\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\UtilTool;

class EmployeeRightsModel extends Model {
    protected $tableName   = 'cfg_employee_rights';
    protected $pk          = 'rec_id';
    
    public function addEmployeeRights($data)
    {
    	try {
    		if (empty($data[0])) {
    			$res = $this->add($data);
    		}else
    		{
    			$res = $this->addAll($data);
    		}
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-addEmployeeRights-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }
    
    public function updateEmployeeRights($data,$where)
    {
    	try {
    		$res = $this->where($where)->save($data);
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-updateEmployeeRights-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }
    
    public function getEmployeeRightsList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getEmployeeRightsList-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }
    
    public function changeRights($right_ids,$employee_id,$right_type)
    {
    	//add check later, just like getRightsTree on js.
    	//$right_ids=UtilTool::array2dict($right_ids,'','');
		if($right_type=='4'){$right_ids[]='0';}
		$rigths=$this->getEmployeeRightsList('rec_id,right_id,is_denied', array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',$right_type)));
    	$rigths=UtilTool::array2dict($rigths,'right_id','');
    	$add=array();
    	$update=array();
    	foreach ($right_ids as $v)
    	{
    		if(isset($rigths[$v]))
    		{
    			$rigths[$v]['checked']=true;
    			if($rigths[$v]['is_denied']!=0)
    			{
    				$update[]=array(
    						'rec_id'=>$rigths[$v]['rec_id'],
    						//'right_id'=>$rigths[$v]['menu_id'],
    						'is_denied'=>0,
    						'modified'=>date('Y-m-d H:i:s',time())
    				);
    			}
    		}else 
    		{
    			$add[]=array(
    					'right_id'=>$v,
    					'employee_id'=>$employee_id,
    					'is_denied'=>0,
    					'type'=>$right_type,
    					'modified'=>date('Y-m-d H:i:s',time()),
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    		}
    	}
    	foreach ($rigths as $r)
    	{
    		if(!isset($r['checked']) && $r['is_denied']==0)
    		{
    			$update[]=array(
    					'rec_id'=>$r['rec_id'],
    					//'right_id'=>$r['menu_id'],
    					'is_denied'=>1,
    					'modified'=>date('Y-m-d H:i:s',time())
    			);
    		}
    	}
		if(empty($rigths) && empty($right_ids))
		{
			switch ($right_type)
			{
				case '1':
					$rights = D('Setting/Shop')->field('shop_id as right_id')->select();
					break;
				case '2':
					$rights = D('Setting/Warehouse')->field('warehouse_id as right_id')->select();
					break;
				case '3':
					$rights = M('cfg_fields')->field('field_id as right_id')->where(array('type'=>0))->select();
					break;
				default:
					SE('位置的权限类别!');
			}
			foreach($rights as $va)
			{
				$add[]=array(
					'right_id'=>$va['right_id'],
					'employee_id'=>$employee_id,
					'is_denied'=>1,
					'type'=>$right_type,
					'modified'=>date('Y-m-d H:i:s',time()),
					'created'=>date('Y-m-d H:i:s',time()),
				);
			}

		}
    	try 
    	{
    		$this->startTrans();
    		if(!empty($add))
    		{
    			$this->addEmployeeRights($add);
    		}
    		if(!empty($update))
    		{
    			foreach ($update as $right)
    			{
    				$this->updateEmployeeRights($right);
    			}
    		}
    		$this->commit();
    	} catch (BusinessLogicException $e)
		{
			$this->rollback();
			E($e->getMessage());
		}catch (\Think\Exception $e)
    	{
    		$this->rollback();
    		E($e->getMessage());
    	}
    }
    
    public function checkPrivileges($module,$controller,$action,$user_id)
    {
    	$is_priv=false;
    	try {
    		$role=D('Setting/Employee')->getRole($user_id);
    		if($role>=2)
    		{
    			return true;
    		}
    		$merge=array( 
//    				'Stock_SalesStockOut'=>'StockSalesPrint',
    				'Stock_WayBill'=>'StockSalesPrint',
    		);
    		if (isset($merge[$module.'_'.$controller]))
    		{
    			$controller=$merge[$module.'_'.$controller];
    		}
    		$common=array(//模块共用url，并且Controller下所有的action全部共用
    				'Setting_Flag'=>true,
    				'Setting_SettingCommon'=>true,
    				'Goods_GoodsCommon'=>true,
    				'Trade_TradeCommon'=>true,
    				'Stock_StockCommon'=>true,
    				'Purchase_PurchaseCommon'=>true,
    		);
    		$where=isset($common[$module.'_'.$controller])?array('module'=>array('eq',$module),'controller'=>array('eq',$controller)):array('module'=>array('eq',$module),'controller'=>array('eq',$controller),'action'=>array('eq',$action));
    		$menu=M('dict_url')->field("url_id")->where($where)->find();
    		if(empty($menu))
    		{
    			$right=$this->alias('cer')->field("rec_id")->join('LEFT JOIN dict_url m ON cer.right_id=m.url_id')->where(array('m.module'=>array('eq',$module),'m.controller'=>array('eq',$controller),'m.type'=>array('gt',0),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0),'cer.type'=>array('eq',0)))->find();
    			if(!empty($right))
    			{
    				$is_priv=true;
    			}
    		}else 
    		{
    			$right=$this->field("rec_id")->where(array('right_id'=>array('eq',intval($menu['url_id'])),'employee_id'=>array('eq',$user_id),'is_denied'=>array('eq',0),'type'=>array('eq',0)))->find();
    			if(!empty($right))
    			{
    				$is_priv=true;
    			}
    		}
    	} catch (\PDOException $e)
    	{
    		\Think\Log::write($e->getMessage());
    	}
    	return $is_priv;
    }
    
    public function getOperatePrivilege($module,$controller,$action,$user_id)
    {
    	$is_priv=false;
    	try {
    		$right=$this->alias('cer')->field("rec_id")->join('LEFT JOIN dict_url m ON cer.right_id=m.url_id')->where(array('m.module'=>array('eq',$module),'m.controller'=>array('eq',$controller),'m.action'=>array('eq',$action),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0),'cer.type'=>array('eq',0)))->find();
    		if(!empty($right))
    		{
    			$is_priv=true;
    		}
    	} catch (\PDOException $e) {
    		\Think\Log::write($e->getMessage());
    	}
    	return $is_priv;
    }

	/**
	 * @param array $search 拼接搜索的数组，可以为空
	 * @param $key 设置返回数组的key值
	 * @param $type 用于区分搜索权限的类型 1.店铺  2.仓库  4.供应商
	 * @return array 返回$search
	 */
	public function setSearchRights(&$search=array(),$key,$type)
	{
		$operator_id = get_operator_id();
		if($operator_id>1)
		{
			$list = $this->field('GROUP_CONCAT(DISTINCT right_id ORDER BY right_id) list')->where(array('employee_id'=>$operator_id,'type'=>$type,'is_denied'=>0))->find();
			$denied_list = $this->field('GROUP_CONCAT(DISTINCT right_id ORDER BY right_id) list')->where(array('employee_id'=>$operator_id,'type'=>$type,'is_denied'=>1))->find();
			if(!empty($list['list']) || !empty($denied_list['list']))
			{
				if(empty($search) || !is_numeric($search[$key]) || $search[$key] === '')
				{
					$search[$key] = empty($list['list'])?'0':$list['list'];
				}else{
					$s_list = explode(',',$list['list']);
					if(!in_array($search[$key],$s_list))
					{
						$search[$key] = '0';
					}
				}
			}else{
				if(empty($search) || !is_numeric($search[$key]) || $search[$key] === '') {
					if($type == 1){
						$list = D('Setting/Shop')->field('GROUP_CONCAT(DISTINCT shop_id ORDER BY shop_id) list')->find();
					}else if($type == 2){
						$list = D('Setting/Warehouse')->field('GROUP_CONCAT(DISTINCT warehouse_id ORDER BY warehouse_id) list')->find();
					}else if($type == 4){
						$list = D('Setting/PurchaseProvider')->field('GROUP_CONCAT(DISTINCT id ORDER BY id) list')->find();
					}
					$search[$key] = $list['list'];
				}
			}
		}else{
			if(empty($search) || !is_numeric($search[$key]) || $search[$key] === '') {
				if($type == 1){
					$list = D('Setting/Shop')->field('GROUP_CONCAT(DISTINCT shop_id ORDER BY shop_id) list')->find();
				}else if($type == 2){
					$list = D('Setting/Warehouse')->field('GROUP_CONCAT(DISTINCT warehouse_id ORDER BY warehouse_id) list')->find();
				}else if($type == 4){
					$list = D('Setting/PurchaseProvider')->field('GROUP_CONCAT(DISTINCT id ORDER BY id) list')->find();
				}
				$search[$key] = $list['list'];
			}
		}
		return $search[$key];
	}
	
	public function getFieldsRight($table_alias='',$field_content=array()){  //获取字段权限，第一个参数是表别名，后面要加".";第二个参数是指当前字段需要经过运算得出，则将字段名对应的算式写成数组传入
		try{
			$field_result = array();
			$field_no = array('cf_1.field_no');
			$field_where = array('cer.type'=>3,'cer.employee_id'=>get_operator_id(),'cer.is_denied'=>1);
			$join = ' left join cfg_fields cf on cer.right_id = cf.field_id left join cfg_fields cf_1 on cf_1.field_name = cf.field_name';
			$field_right_info =D('Setting/EmployeeRights')->getEmployeeRightsList($field_no,$field_where,'cer',$join);
			$field_info = M('cfg_fields')->field('field_no')->select();
			
			foreach($field_info as $val){
				$right = 0;
				foreach($field_right_info as $v){
					if($v['field_no'] == $val['field_no']){
						$field_result[$val['field_no']] ='"无权限" as '.$val['field_no']; 
						$right = 1;
						break;
					}	
				}
				if(!$right){
					if(isset($field_content[$val['field_no']]) && !empty($field_content[$val['field_no']])){
						$field_result[$val['field_no']] = $field_content[$val['field_no']];
					}else{
						$field_result[$val['field_no']] =$table_alias.$val['field_no'];
					}
				}
			}
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE('获取字段权限错误');
		}
		return $field_result;
	}
}

