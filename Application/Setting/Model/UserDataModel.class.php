<?php

namespace Setting\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;

class UserDataModel extends Model
{
    protected $tableName = 'cfg_user_data';
    protected $pk = 'rec_id';
    
	public function addUserData($data)
    {
        try
        {
            if (empty($data[0])) 
            {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addUserData-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function updateUserData($data,$where)
    {
        try
        {
            $res = $this->where($where)->save($data);
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-updateUserData-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
    public function getUserData($fields,$where=array(),$alias='')
    {
        try
        {
            $res = $this->alias($alias)->field($fields)->where($where)->find();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getUserData-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getUserDataList($fields,$where=array(),$alias='',$join=array(),$order='')
    {
        $res=array();
        try
        {
            $res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getUserDataList-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function setDatagridField($data)
    {
        try
        {
            $field=$this->getUserData('rec_id',array('user_id'=>array('eq',$data['user_id']),'type'=>array('eq',$data['type']),'code'=>array('eq',$data['code'])));
            if (empty($field)) 
            {//新增
                $now=date('Y-m-d H:i:s',time());
                $data['modified']=$now;
                $data['created']=$now;
                $this->addUserData($data);
            }else
            {//更新
                $this->updateUserData(array('rec_id'=>$field['rec_id'],'data'=>$data['data']));
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-setDatagridField-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function getDatagridField($mode,$key,$is_fro=0)
    {
        $datagrid_field=array();
        try
        {
            $code=strtolower(str_replace('/','_',$mode).'_'.$key.'_query');
            $user_id=get_operator_id();
            $field=$this->getUserData('data',array('user_id'=>array('eq',$user_id),'type'=>array('eq',1),'code'=>array('eq',$code)));
            $fields=get_field($mode,$key);
            $menu=array();//普通列
            $frozen_menu=array();//固定列
            $more=array();//默认不显示列
            $is_check=false;//是否后期添加的checkbox
			
			$field_no = array('cf_1.field_no');
			$field_right_no = array('GROUP_CONCAT(cf_1.field_no) as field_no');
			$field_where = array('cer.type'=>3,'cer.employee_id'=>get_operator_id(),'cer.is_denied'=>1);
			$join = ' left join cfg_fields cf on cer.right_id = cf.field_id left join cfg_fields cf_1 on cf_1.field_name = cf.field_name';
			$field_info =D('Setting/EmployeeRights')->getEmployeeRightsList($field_no,$field_where,'cer',$join);
			$field_where_show = array('cer.type'=>3,'cer.employee_id'=>get_operator_id(),'cer.is_denied'=>0);
			$field_info_right = D('Setting/EmployeeRights')->getEmployeeRightsList($field_right_no,$field_where_show,'cer',$join);
			if(!empty($field_info_right[0]['field_no'])){
				$field_info = M('cfg_fields')->field('field_no')->where(array('field_no'=>array('not in',$field_info_right[0]['field_no'])))->select();
			}
			if(!empty($field))
            {
                $field_data=json_decode($field['data']);
                foreach ($fields as $k => $v)
                {
                    $v['text']=$k;
                    $v['title']=$k;
                    $is_set_right = 0;
                    $i=0;
                    foreach ($field_data as $txt => $fd)
                    {
                    	$arr=explode('-', $fd);
                        if($txt==$v['field'])
                        {
                            $v['hidden']=($fd==0?true:false);
							if(in_array($v['field'],array_column($field_info,'field_no'))){
								$v['hidden'] = true;
							}
                            $is_set_right = 1;
                            @$arr[1]==1?@$frozen_menu[$i]=$v:$menu[$i]=$v;
                        }
                        $i++;
                    }
					if(in_array($v['field'],array_column($field_info,'field_no'))){
						$v['hidden'] = true;
					}
                    if(!$is_set_right){
                    	if($v['text']=='checkbox'){
                    		$is_check=true;
                    	}else if(@$v['frozen']==1){
                    		$frozen_menu[]=$v;
                    	}else{
                    		$mores[]=$v;
                    	}
                    }
                }
                ksort($menu);
            }else
            {
            	foreach ($fields as $k=>$v){
					if(in_array($v['field'],array_column($field_info,'field_no'))){
						$v['hidden'] = true;
					}
            		$v['title']=$k;
            		$v['text']=$k;
            		@$v['frozen']==1?$frozen_menu[]=$v:$menu[$k]=$v;
            	}

            }
            //根据$is_fro判断返回普通列或者固定列
            if(!$is_fro){
            	foreach($mores as $more){
            		$menu[]=$more;
            	}
            	foreach($menu as $row){
            		$datagrid_field[$row['text']]=$row;
            	}
            }else{
            	if($is_check){
            		$datagrid_field[]=array(
            				'field'=>'ck',
            				'checkbox'=>true,
            				'hidden'=>false,
            		);
            	}
            	foreach($frozen_menu as $row){
            		$datagrid_field[]=$row;
            	}
            	$datagrid_field=array($datagrid_field);
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getDatagridField-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $datagrid_field;
    }

    public function getExcelField($mode,$key)
    {
        $fields=get_field($mode,$key);
        $user_id=get_operator_id();
        $result=array();
        try
        {
            $list=array();

            $field_no = array('cf_1.field_no');
            $field_where = array('cer.type'=>3,'cer.employee_id'=>get_operator_id(),'cer.is_denied'=>1);
            $join = ' left join cfg_fields cf on cer.right_id = cf.field_id left join cfg_fields cf_1 on cf_1.field_name = cf.field_name';
            $field_info =D('Setting/EmployeeRights')->getEmployeeRightsList($field_no,$field_where,'cer',$join);

            foreach ($fields as $k => $v)
            {
                if ((isset($v['hidden'])&&$v['hidden']==true)||(isset($v['checkbox'])&&$v['checkbox']==true)) 
                {
                    continue;
                }
                if(in_array($v['field'],array_column($field_info,'field_no'))){
                    continue;
                }
                $list[$v['field']]=$k;
            }
            $code=strtolower(str_replace('/','_',$mode).'_'.$key.'_query');
            $field=$this->getUserData('data',array('user_id'=>array('eq',$user_id),'type'=>array('eq',1),'code'=>array('eq',$code)));
            if (!empty($field)) 
            {
                $field_data=json_decode($field['data']);
                foreach ($field_data as $k => $v) 
                {
                	$arr=explode('-', $v);
                    if ((!empty($list[$k])) && $arr[0]==1) 
                    {
                        $result[$k]=$list[$k];
                    }
                }
            }else
            {
                $result=$list;
            }
        }catch(BusinessLogicException $e)
        {
            $result=array();
        }
        return $result;
    }
	
	//获取通知框权限
	 public function getMessageRights($code){
		$operator_id = get_operator_id();
		$where = array('user_id'=>$operator_id,'type'=>10,'code'=>$code);
		$user_data = $this->field('rec_id')->where($where)->find();
		return $user_data;
		
	}
	
}