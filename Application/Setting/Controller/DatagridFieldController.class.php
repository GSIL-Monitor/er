<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;

class DatagridFieldController extends BaseController
{
	public function getField()
	{
		$mode=I('get.mode');
		$key=I('get.key');
		$frozen=I('get.frozen');
		$checkBox=false;//是否有checkbox，默认没有
		$fields=$this->getDatagridField($mode,$key);
		if($fields[0]['name']=='ck'){//设置表头界面不显示checkbox
			$checkBox=true;
			unset($fields[0]);
		}
		$this->assign('fields',$fields);
		$this->assign('frozen',$frozen);
		$this->assign('check',$checkBox);
		$this->display('dialog_field');
	}

	public function setField()
	{
		$code=I('post.code');
		$fields=I('post.fields');
		try
		{
			$user_data=array(
				'code'=>$code.'_query',
				'data'=>json_encode($fields),
				'user_id'=>get_operator_id(),
				'type'=>1,//界面表头字段
			);
			D('UserData')->setDatagridField($user_data);
		}catch(BusinessLogicException $e)
		{
			$this->error($e->getMessage());
		}
		$this->success();
	}

	private function getDatagridField($mode,$key)
	{
		$fields=get_field($mode,$key);
		$result=array();
		$is_check=false;//是否是后期添加的checkbox
		$menu=array();//存储新加的列
		$fro_menu=array();//存储新加的冻结列
		try
		{
			$list=array();
			$field_no = array('cf_1.field_no');
			$field_where = array('cer.type'=>3,'cer.employee_id'=>get_operator_id(),'cer.is_denied'=>1);
			$join = ' left join cfg_fields cf on cer.right_id = cf.field_id left join cfg_fields cf_1 on cf_1.field_name = cf.field_name';
			$field_info =D('Setting/EmployeeRights')->getEmployeeRightsList($field_no,$field_where,'cer',$join);
			foreach ($fields as $k => $v) 
			{
				if (isset($v['hidden'])&&$v['hidden']==true) 
				{
					continue;
				}
				if(in_array($v['field'],array_column($field_info,'field_no'))){
					continue;
				}
				$list[]=array('name'=>$v['field'],'value'=>1,'text'=>$k,'frozen'=>$v['frozen']==1?1:0);
			}
			$code=strtolower(str_replace('/','_',$mode).'_'.$key.'_query');
            $user_id=get_operator_id();
            $field=D('UserData')->getUserData('data',array('user_id'=>array('eq',$user_id),'type'=>array('eq',1),'code'=>array('eq',$code)));
			if (!empty($field))
			{
				$field_data=json_decode($field['data']);
				foreach ($list as $lt)
				{
					$is_set_right = 0;
					$i=0;
					foreach ($field_data as $k => $v)
					{
						if ($k==$lt['name'])
						{
							$arr=explode('-', $v);
							$arr[1]=($arr[1]==1?1:0);
							$result[$i]=array('name'=>$k,'value'=>$arr[0],'text'=>$lt['text'],'frozen'=>$arr[1]);
							$is_set_right = 1;
							break;
						}
						$i++;
					}
					if(!$is_set_right){
						if($lt['text']=='checkbox'){
							$is_check=true;
						}else if($lt['frozen']==1){
							$fro_menu[]=array('name'=>$lt['name'],'value'=>1,'text'=>$lt['text'],'frozen'=>1);
						}else{
							$menu[]=array('name'=>$lt['name'],'value'=>1,'text'=>$lt['text'],'frozen'=>0);
						}
					}
				}
				if(!empty($fro_menu)){
					foreach ($fro_menu as $k=>$f){
						array_unshift($result, $f);
					}
				}
				if($is_check){
					$ck=array(
							'name'=>'ck',
							'field'=>'ck',
            				'checkbox'=>true,
            				'hidden'=>false,
					);
					array_unshift($result, $ck);
				}
				ksort($result);
				foreach($menu as $row){
					$result[]=$row;
				}
			}else{
            	$result=$list;
            }
		}catch(BusinessLogicException $e)
		{
			$result=array();
		}
		return $result;
	}

}