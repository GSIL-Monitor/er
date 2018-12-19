<?php
namespace Home\Model;

use Think\Model;

class MenuModel extends Model
{
	protected $tableName = 'dict_url';
    protected $pk        = 'url_id';

	public function getMenuByUserId($user_id)
	{
		$menu=array();
		try
		{
			$role=D('Setting/Employee')->getRole($user_id);
			//判断实施助手和档口模式是否开启
			$config_value=get_config_value(array('sys_init','stalls_system_init'),array(0,0));
			if($role>=2)
			{
				if($config_value['sys_init']&&$config_value['stalls_system_init'])
				{//全部开启
					$menu_ids=M('dict_url')->field("url_id,parent_id")->where(array('type'=>array('gt',0)))->order('parent_id ASC,sort_order DESC')->select();
				}
				elseif(!$config_value['sys_init']&&$config_value['stalls_system_init'])
				{//只开启档口模式
					$menu_ids=M('dict_url')->field("url_id,parent_id")->where(array('type'=>array(array('gt',0),array('neq',3))))->order('parent_id ASC,sort_order DESC')->select();
				}
				elseif($config_value['sys_init']&&!$config_value['stalls_system_init'])
				{//只开启实施助手
					$menu_ids=M('dict_url')->field("url_id,parent_id")->where(array('type'=>array(array('gt',0),array('neq',4))))->order('parent_id ASC,sort_order DESC')->select();
				}
				else
				{//全部不开启
					$menu_ids=M('dict_url')->field("url_id,parent_id")->where(array('type'=>array(array('gt',0),array('lt',3))))->order('parent_id ASC,sort_order DESC')->select();
				}
			}else
			{
				//$menu_ids=M('dict_url')->alias('u')->field("u.url_id,u.parent_id")->join('LEFT JOIN cfg_employee_rights cer ON cer.right_id=u.url_id')->where(array('u.type'=>array(array('gt',0),array('lt',3)),array('cer.type'=>array('eq',0)),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0)))->order('u.parent_id ASC, u.sort_order DESC')->select();
				if($config_value['stalls_system_init']){
					$menu_ids=M('dict_url')->alias('u')->field("u.url_id,u.parent_id")->join('LEFT JOIN cfg_employee_rights cer ON cer.right_id=u.url_id')->where(array('u.type'=>array('in',array(1,2,4)),array('cer.type'=>array('eq',0)),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0)))->order('u.parent_id ASC, u.sort_order DESC')->select();
				}else{
					$menu_ids=M('dict_url')->alias('u')->field("u.url_id,u.parent_id")->join('LEFT JOIN cfg_employee_rights cer ON cer.right_id=u.url_id')->where(array('u.type'=>array('in',array(1,2)),array('cer.type'=>array('eq',0)),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0)))->order('u.parent_id ASC, u.sort_order DESC')->select();
				}
				//$menu=M('dict_url')->alias('u')->field("u.url_id AS id, u.name AS text,IF(u.is_leaf=0 OR u.controller IS NULL OR u.controller='','', CONCAT('index.php/',u.module,'/',u.controller,'/',u.action,IF(u.type=2,CONCAT('?dialog=',LOWER(u.controller)),''))) href, u.parent_id,lower(module) module,img,is_leaf")->join('LEFT JOIN cfg_employee_rights cer ON cer.right_id=u.url_id')->where(array('u.type'=>array('gt',0),array('cer.type'=>array('eq',0)),'cer.employee_id'=>array('eq',$user_id),'cer.is_denied'=>array('eq',0)))->order('u.parent_id ASC, u.sort_order DESC')->select();
			}
			if(empty($menu_ids)){
				$menu = array();
			}else{
				$children=array();
				foreach($menu_ids as $ids){
					$children[]=$ids['url_id'];
					if(!in_array($ids['parent_id'],$children)){
						$children[]=$ids['parent_id'];
					}
				}
				$menu=M('dict_url')->alias('u')->field("u.url_id AS id, u.name AS text,IF(u.is_leaf=0 OR u.controller IS NULL OR u.controller='','', CONCAT('index.php/',u.module,'/',u.controller,'/',u.action,IF(u.type=2||u.type=3,CONCAT('?dialog=',LOWER(u.controller)),''))) href, u.parent_id,lower(module) module,img,is_leaf")->where(array('u.url_id'=>array('in',$children)))->order('u.parent_id ASC, u.sort_order DESC')->select();
			}
		}catch (\PDOException $e)
		{
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $menu;
	}

    public function getMenu()
    {
        $menu=array();
        try 
        {
            $menu=M('dict_url')->field("img,url_id AS id, name AS text, IF(is_leaf=0 OR controller IS NULL OR controller='','', CONCAT('index.php/',module,'/',controller,'/',action,IF(`type`=2,CONCAT('?dialog=',LOWER(controller)),''))) href, parent_id")->where(array('type'=>array('gt',0)))->order('parent_id ASC,sort_order DESC')->select();
        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $menu;
    }

	//监测初始化流程图相关任务
	public function check_begin($list){
		if(!$list)return false;
		$model=array(
			'goods'=>'api_goods_spec',
			'class'=>'goods_class',
			'brand'=>'goods_brand',
			'goods_archives'=>'goods_goods',
			'shop'=>'cfg_shop',
			'logistics'=>'cfg_logistics',
			'warehouse'=>'cfg_warehouse',
			'inventory_management'=>'stock_spec',
			'warehouse_rule'=>'cfg_shop_warehouse',
			'logis_m'=>'cfg_logistics_match',
			'logis_f'=>'cfg_logistics_fee',
			'gift'=>'cfg_gift_rule',
			'remark'=>'cfg_trade_remark_extract',
		);
		$return=array();
		foreach($list as $key=>$row){
			$return[$key]='true';
			if($model[$key]=='goods_class' || $model[$key]=='goods_brand'){
				$sql="select count(*) as num from ".$model[$key];
				$result=$this->query($sql);
				if($result[0]['num']<=1)$return[$key]='false';
			}else{
				$sql="select * from ".$model[$key]." limit 1";
				$result=$this->query($sql);
				if(!$result)$return[$key]='false';
			}
		}
		return $return;
	}

}
?>