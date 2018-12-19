<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\Factory;
use Think\Log;

/**
 * 员工控制器类
 * @package Setting\Controller
 */
class EmployeeController extends BaseController
{
    /**
     *初始化员工信息
     */
    public function getEmployee()
    {
        $idList = DatagridExtention::getRichDatagrid('Employee', 'employee', U("Employee/loadDataByCondition"));
		$user_id = get_operator_id();
        $role=D('Employee')->getRole($user_id);
        $permission= $role>=2?true:D('EmployeeRights')->getOperatePrivilege('Setting','Employee','changeRights',$user_id);
        $permission= $permission?1:0;
        $params = array();
        if ($role>0) 
        {
            $params = array(
                'add' => array(
                    'id' => $idList['id_list']['add'],
                    'title' => '添加员工',
                    'url' => U('Setting/Employee/addEmployee'),
                    'width' => '500',
                    'height' => '250',
					'ismax'	 =>  false
				),
                'edit' => array(
                    'id' => $idList['id_list']['edit'],
                    'title' => '编辑员工',
                    'url' => U('Setting/Employee/addEmployee'),
                    'width' => '500',
                    'height' => '250',
					'ismax'	 =>  false
				),
                "delete" => array(
                    "url" => U("Setting/Employee/delEmployee")
                ),
            );
        }else
        {
			$params = array(
			'edit' => array(
                    'id' => $idList['id_list']['edit'],
                    'title' => '编辑员工',
                    'url' => U('Setting/Employee/addEmployee'),
                    'width' => '500',
                    'height' => '250',
					'ismax'	 =>  false
			),
			);
			if($permission>0)
			{
				$params['add']= array(
                    'id' => $idList['id_list']['add'],
                );
			}
		}
		$arr_tabs=array(
				//array('id'=>$idList['id_list']['tab_container'],'url'=>U('Setting/SettingCommon/showTabsView',array('tabs'=>'employee_rights')).'?tab=employee_rights&prefix=employee','title'=>'设置权限'),
				array('id'=>$idList['id_list']['tab_container'],'url'=>U('Setting/SettingCommon/showTabsView',array('tabs'=>'login_log')).'?tab=login_log&prefix=employee','title'=>'登录日志'),
		);
        $params['datagrid'] = array(
            'id' => $idList['id_list']['datagrid'],
        );
        $params['search'] = array(
            'form_id' => $idList['id_list']['form'],
        );
        $params['tabs'] = array(
        	'id'=>$idList['id_list']['tab_container'],
        	'url'=>U('SettingCommon/updateTabsData')
        );
        $idList['datagrid']['options']['fitColumns'] = true;
		$idList['id_list']['fileDialog']='employee_fileDialog';
		$idList['id_list']['fileForm']='employee_fileForm';
        $this->assign('params', json_encode($params));
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign('templet_url', U('Employee/downloadTemplet'));
        $this->assign("id_list", $idList['id_list']);
        $this->assign('arr_tabs', json_encode($arr_tabs));
		$this->assign("now_employee_id",$user_id);
        $this->assign("role", $role);
        $this->assign("permission", $permission);
        $this->display('show');
    }

    /**
     * 获取员工信息
     * @param int $page
     * @param int $rows
     * @param array $search
     * @param string $sort
     * @param string $order
     */
    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'employee_id', $order = 'desc')
    {
    	$employee_db=D('Employee');
    	$role = $employee_db->getRole(get_operator_id());
    	$where_limit = array('employee_id'=>array('gt',0),'roles_mask'=>array('elt',$role));
    	$data=$employee_db->searchEmployee($page, $rows, $search, $sort, $order,$where_limit);
        $this->ajaxReturn($data);
    }

    /**
     * 添加员工
     * @param int $id
     */
    public function addEmployee($id = 0)
    {	
        $id = intval($id);
        $form = "'none'";
		$user_id = get_operator_id();
		$employee_db=D('Employee');
		$role=$employee_db->getRole($user_id);
		$employee_role=$employee_db->getRole($id);
		if($employee_role==$role && $role>=2)
		{
			$role=3;
		}
        if (0 != $id &&( $id==$user_id || $role>0)) {
            $this->loadSelectedData($id, $form);
		}
        $mark = (0 == $id ? 'add' : 'edit');
        $field_rights = 0;
        if(is_array($form)){
        	$field_rights = $form['field_rights'];
        	unset($form['field_rights']); 
        }
        $this->assign('field_rights', $field_rights);
        $this->assign('mark', $mark);
        $this->assign("role", $role);
        $this->assign('form', json_encode($form));
        $this->display('employee_edit');
    }

    /**
     * 获取选定员工的信息
     * @param $id
     * @param $form
     * @return string
     */
    public function loadSelectedData($id, &$form)
    {
        $re = D('Employee')->loadSelectedData($id);
        if ("" != $re['data']) 
        {
            $form = $re['data'];
        } else 
        {
            $form = "'err'";
        }
        return $form;
    }

    /**
     *保存员工信息
     */
    public function saveEmployee()
    {
    	$result=array('status'=>0,'info'=>'');
    	try {
    		$tmp = I('post.');
    		$user_id=get_operator_id();
    		$employee_db=D('Employee');
    		$role=$employee_db->getRole($user_id);
    		$employee_role=$employee_db->getRole(intval($tmp['id']));
    		if($user_id!=$tmp['id'] && $role<=$employee_role)
    		{
    			if($role>0)
    			{
    				E('不能编辑其他管理员用户！');
    			}else 
    			{
    				E('只能编辑当前用户信息');
    			}
    		}
    		$chang_arr="";
    		$arr['account']     = $this->dropBlack($tmp['account']);
    		$arr['fullname']    = $this->dropBlack($tmp['fullname']);
    		$arr['gender']      = $this->dropBlack($tmp['gender']);
    		$arr['position']    = $this->dropBlack($tmp['position']);
    		$arr['shortname']   = time();
    		$arr['mobile_no']   = $this->dropBlack($tmp['mobile_no']);
    		$arr['email']       = $this->dropBlack($tmp['email']);
    		$arr['qq']          = $this->dropBlack($tmp['qq']);
    		$arr['wangwang']    = $this->dropBlack($tmp['wangwang']);
    		$arr['type']        = $this->dropBlack($tmp['type']);
    		$arr['employee_id'] = $this->dropBlack($tmp['id']);
    		$arr['field_rights']= set_default_value($tmp['field_rights'], 0);
    		$chang_arr['password']=md5($tmp['password']);
    		if(isset($tmp['roles_mask']) && intval($tmp['roles_mask'])<2 && $employee_role<2)
    		{
    			$arr['roles_mask']  = set_default_value($tmp['roles_mask'], 0);
    		}
    		D('Employee')->saveEmployee($arr,$chang_arr);
    	}catch (\Exception $e)
    	{
    		$msg=$e->getMessage();
    		//\Think\Log::write($msg);
    		$result['status']=1;//失败
    		$result['info']=$msg;
    	}
        $this->ajaxReturn(json_encode($result),'EVAL');
    }


    /**
     * 删除员工
     * @param $id
     */
    public function delEmployee($id)
    {
    	try 
    	{
    		D('Employee')->delEmployee(intval($id));
    	} catch (\Think\Exception $e) {
    		$this->error($e->getMessage());
    	}
    	$this->success('删除成功');
    }
	/**
	 * 发送验证码
	 * @param unknown $id
	 * @param unknown $type
	 */
    public function sendCode()
    {
    	$new_mobile=I('post.mobile');
    	$id=intval(I('post.id'));
    	$type=I('post.type');
    	$result=array('status'=>0,'info'=>'');
    	$info=array('pwd'=>'您正在进行密码修改','tel'=>'您正在修改绑定的手机号码');
    	$session=array('pwd'=>'json_mobile_code_pwd','tel'=>'json_mobile_code_tel');
    	$message=empty($info[$type])?$info['pwd']:$info[$type];
    	$key=empty($session[$type])?$session['pwd']:$session[$type];
    	$user_id=get_operator_id();
    	try {
    		$role=D('Employee')->getRole($user_id);
    		if($id!=$user_id && $role==0)
    		{
    			E('非管理员，只能修改当前用户信息！');
    		}else if($role>0)
    		{
    			$id=$user_id;
    		}
    		$employee=D('Setting/Employee')->getEmployee('mobile_no AS mobile',array('employee_id'=>array('eq',$id)));
    		if((empty($employee)||empty($employee['mobile'])))
    		{
    			$employee['mobile']=$new_mobile;
    		}
    		if(!check_regex('mobile',$employee['mobile']))
    		{
    			E('手机号码格式不正确');
    		}
    		$mobile=$employee['mobile'];
    		$mobile.=',';
    		$mobile_code = substr(microtime(), 2, 6);
    		$message='【E快帮】'.$message.'，验证码：'.$mobile_code.'。请勿向他人泄露验证码。';
            $res=UtilTool::SMS($mobile, $message,'checkcode');
    		if($res->status==0)
    		{
    			$json_mobile_code=array('mobile'=>$employee['mobile'],'mobile_code'=>$mobile_code,'times'=>0,'send_time'=>date("Y-m-d H:i:s",time()));
    			session($key,json_encode($json_mobile_code));
    			//记录验证码日志
    			\Think\Log::write('mobile:'.$mobile.' code:'.$json_mobile_code['mobile_code'],\Think\Log::WARN);
    			$result['status']=0;
    			$result['info']='验证码发送成功，注意查收！';
    		}else
    		{
    			$result['status']=1;
    			$result['info']='验证码发送失败，请稍后再试';
    		}
    	} catch (\Exception $e) {
    		$msg=$e->getMessage();
    		\Think\Log::write('sendCode:'.$msg,\Think\Log::WARN);
    		$result['status']=1;//失败
    		$result['info']=$msg;
    	}
    	$this->ajaxReturn($result);
    }
    
    /**
     * 校验验证码
     * @param unknown $code
     * @param unknown $key
     */
    private function checkCode($code,$key)
    {
    	$session=array('pwd'=>'json_mobile_code_pwd','tel'=>'json_mobile_code_tel');
    	$key=$session[$key];
    	$json_mobile_code=session($key);
    	if(empty($json_mobile_code))
    	{
    		E('验证码已过期，请重新获取');
    	}
    	$json_mobile_code=json_decode(session($key));
    	if(strtotime($json_mobile_code->send_time)+intval(C('MOBILE_CODE_INVALID_TIME'))<time())
    	{
    		session($key, null);
    		E('验证码已过期，请重新获取');
    	}
    	$json_mobile_code->times=intval($json_mobile_code->times);
    	if(intval($json_mobile_code->times)<5)
    	{
    		$json_mobile_code->times+=1;
    		session($key,json_encode($json_mobile_code));
    	}else
    	{
    		E('验证太频繁，请稍后再试...');
    	}
    	if(!check_regex('zip',$code) || $code!=$json_mobile_code->mobile_code)
    	{
    		E('验证码不正确');
    	}
    }
    /**
     * 修改密码
     * @param $data
     */
    public function changePassword()
    {	
        $user_id=get_operator_id();
    	if (IS_POST)
    	{
    		$result=array('status'=>0,'info'=>'');
    		try {
    			$data = I('post.data','',C('JSON_FILTER'));
    			$data['id']=intval($data['id']);
                if (empty($data['password']))
                {
                    E('新密码不能为空！');
                }
    			$employee_db=D('Employee');
    			$role=$employee_db->getRole($user_id);
    			$employee_role=$employee_db->getRole(intval($data['id']));
    			if($data['id'] != $user_id && $role<=$employee_role)
    			{
    				if($role>0)
    				{
    					E('不能编辑其他管理员用户密码！');
    				}else
    				{
    					E('非管理员，只能修改当前用户密码！');
    				}
    			}
                $is_check_code=get_config_value('change_password_check_code',0);
                if(((!empty($data['code']))&&$is_check_code==0) || (empty($data['code'])&&$is_check_code==1))
                {
                    E('修改密码开启或关闭了短信验证，请打开重新编辑！');
                }
                $employee=$employee_db->getEmployee('password,salt',array('employee_id'=>array('eq',$data['id'])));
                if ($is_check_code==0 && $role==0) 
                {
                    if(empty($data['oldpassword']))
                    {
                        E('原密码不能为空！');
                    }
                    $password=$employee_db->encrypt($data['oldpassword'].$employee['salt']);
                    if ($password != $employee['password']) 
                    {
                        E('原密码不正确！');
                    }
                    if ($data['oldpassword'] == $data['password']) 
                    {
                        E('新密码不能与原密码相同！');
                    }
                }else if($is_check_code==1||$role>0)
                {
                    $password=$employee_db->encrypt($data['password'].$employee['salt']);
                    if ($password == $employee['password']) 
                    {
                        E('新密码不能与原密码相同！');
                    }
                    if($is_check_code==1)
                    {
                        $this->checkCode($data['code'], 'pwd');
                    }
                }
    			$employee_db->changePassword($data['password'],$data['id']);
    		} catch (\Exception $e) {
    			$msg=$e->getMessage();
    			\Think\Log::write($msg,\Think\Log::WARN);
    			$result['status']=1;//失败
    			$result['info']=$msg;
    		}
    		$this->ajaxReturn($result);
    	}else 
    	{
    		$id=intval(I('get.id'));
            $is_check_code=get_config_value('change_password_check_code',0);
            $view=$is_check_code==0?'modify_password':'change_password';
            $role=D('Employee')->getRole($user_id);
            $this->assign('role',$role);
    		$this->assign('id',$id);
    		$this->display($view);
    	}
    }
    /**
     * 修改手机号码
     */
    public function changeMobile()
    {
    	if(IS_POST){
    		$result=array('status'=>0,'info'=>'');
    		$user_id=get_operator_id();
    		try {
    			$data = I('post.data','',C('JSON_FILTER'));
    			$data['id']=intval($data['id']);
    			$employee_db=D('Employee');
    			$role=$employee_db->getRole($user_id);
    			$employee_role=$employee_db->getRole(intval($data['id']));
    			if($data['id'] != $user_id && $role<=$employee_role)
    			{
    				if($role>0)
    				{
    					E('不能编辑其他管理员用户密码！');
    				}else
    				{
    					E('非管理员，只能修改当前用户密码！');
    				}
    			}
    			$this->checkCode($data['code'], 'tel');
    			$employee=$employee_db->getEmployee('employee_id as id,mobile_no AS mobile,account',array('employee_id'=>array('eq',$data['id'])));
    			$employee_db->updateEmployee(array('mobile_no'=>$data['mobile_no']),array('employee_id'=>array('eq',$data['id'])));
    			$log=array(
    					'type'=>16,
    					'operator_id'=>$user_id,
    					'data'=>get_client_ip(),
    					'message'=>'修改用户'.$employee['account'].'手机号码：'.$employee['mobile'].' 到  '.$data['mobile_no'],
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    			M('sys_other_log')->add($log);
    		}catch (\PDOException $e){
    			$msg=$e->getMessage();
    			\Think\Log::write($msg);
    			$result['status']=1;//失败
    			$result['info']='未知错误，请联系管理员 ';
    		}catch (\Exception $e) {
    			$msg=$e->getMessage();
    			//\Think\Log::write($msg);
    			$result['status']=1;//失败
    			$result['info']=$msg;
    		}
    		$this->ajaxReturn($result);
    	}else 
    	{   
    		$id=intval(I('get.id'));
    		$employee=D('Setting/Employee')->getEmployee('employee_id as id,mobile_no AS mobile',array('employee_id'=>array('eq',$id)));
    		$this->assign('id',$employee['id']);
    		$this->assign('mobile',$employee['mobile']);
    		$this->display('change_mobile');
    	}
    }
    
    public function changeRights()
    {
    	if(IS_POST)
    	{
    		try 
    		{
    			$ids=I('post.ids','',C('JSON_FILTER'));
				$type=I('post.type','',C('JSON_FILTER'));
    			$employee_id=intval(I('post.id'));
    			D('Employee')->changeRights($ids,$employee_id,$type);
    		}catch (\Think\Exception $e)
    		{
    			$this->error($e->getMessage());
    		}
    		$this->success();
    	}else 
    	{   $id=I('get.id');
    		$this->assign('id',$id);
    		$this->display('change_rights');
    	}
    }
    
    public function getRightsTree()
    {
    	$employee_id=intval(I('post.id'));
    	$type=intval(I('post.type'));
    	$result=array();
    	try
    	{
    		//$result=M('dict_url')->field("url_id AS id, name AS text,parent_id")->order('parent_id ASC, sort_order DESC')->where(array('type'=>array(array('egt',0),array('lt',3))))->select();
    		$result=M('dict_url')->field("url_id AS id, name AS text,parent_id")->order('parent_id ASC, sort_order DESC')->where(array('type'=>array('in',array(0,1,2,4))))->select();
    		$rights=D('EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',0),'type'=>array('eq',$type)));
    		$len=count($result);
    		$map=array();
    		for($i=0;$i<$len;$i++)
    		{
    			foreach ($rights as $r)
    			{
    				if($result[$i]['id']==$r['right_id'] && $result[$i]['parent_id']!=0)
    				{
    					$result[$i]['checked']=true;
    					$map[$result[$i]['parent_id']]=true;
    				}
    			}
    		}
    		for($i=0;$i<$len;$i++)
    		{
    			if(isset($map[$result[$i]['id']]))
    			{
    				if(isset($result[$i]['checked']))
    				{
    					unset($result[$i]['checked']);
    				}
    			}
    		}
    		$result=UtilTool::array2tree($result, 'id', 'parent_id', 'children');
    	}catch (\PDOException $e)
    	{
    		\Think\Log::write($e->getMessage());
    	}
    	$this->ajaxReturn(array_reverse($result));
    }
	public function getRightsList()
	{
		$res = array('status'=>0,'info'=>'成功','data'=>array());
		$employee_id=intval(I('post.id'));
		$type=intval(I('post.type'));
		$result=array();
		try
		{
			$result=D('Setting/shop')->field("shop_id AS id, shop_name as name")->order('shop_id ASC')->select();
			$rights=D('EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',0),'type'=>array('eq',$type)));
			$rights_list_count=D('EmployeeRights')->where(array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',$type)))->count();
			$is_checked = false;
			if($rights_list_count == 0)
			{
				$is_checked = true;
			}
			$len=count($result);
			for($i=0;$i<$len;$i++)
			{
				if($is_checked)
				{
					$result[$i]['checked']=true;
					continue;
				}
				foreach ($rights as $r)
				{
					if($result[$i]['id']==$r['right_id'] )
					{
						$result[$i]['checked']=true;
					}
				}
			}
			$res['data']=$result;
		}catch (\PDOException $e)
		{
			\Think\Log::write($e->getMessage());
			$res['info'] = self::UNKNOWN_ERROR;
			$res['status']=1;
		}

		$this->ajaxReturn($res);
	}

	public function getRightsWarehouseList()
	{
		$res = array('status'=>0,'info'=>'成功','data'=>array());
		$employee_id=intval(I('post.id'));
		$type=intval(I('post.type'));
		$result=array();
		try
		{
			$result=D('Setting/Warehouse')->field("warehouse_id AS id,name")->order('warehouse_id ASC')->select();
			$rights=D('EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',0),'type'=>array('eq',$type)));
			$rights_list_count=D('EmployeeRights')->where(array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',$type)))->count();
			$is_checked = false;
			if($rights_list_count == 0)
			{
				$is_checked = true;
			}
			$len=count($result);
			for($i=0;$i<$len;$i++)
			{
				if($is_checked)
				{
					$result[$i]['checked']=true;
					continue;
				}
				foreach ($rights as $r)
				{
					if($result[$i]['id']==$r['right_id'] )
					{
						$result[$i]['checked']=true;
					}
				}
			}
			$res['data']=$result;
		}catch (\PDOException $e)
		{
			\Think\Log::write($e->getMessage());
			$res['info'] = self::UNKNOWN_ERROR;
			$res['status']=1;
		}

		$this->ajaxReturn($res);
	}
	public function getRightsField(){
		try{
			$res = array('status'=>0,'info'=>'成功','data'=>array());
			$employee_id=intval(I('post.id'));
			$type=intval(I('post.type'));
			$result=array();
			$field_info =M('cfg_fields')->field(array('field_id as id','field_name as name'))->where(array('type'=>0))->select(); 
			$field_rights_info = D('Setting/EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',0),'type'=>array('eq',$type)));
			$rights_list_count=D('EmployeeRights')->where(array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',$type)))->count();
			$field_no_rights = D('Setting/EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',1),'type'=>array('eq',$type)));
			$is_checked = false;
			if($rights_list_count == 0)
			{
				$is_checked = true;
			}
			foreach($field_info as $k=>$v){
				if($is_checked){
					$field_info[$k]['checked'] = true;
					continue;
				}
				if(empty($field_rights_info) && !empty($field_no_rights)){
					$field_info[$k]['checked'] = true;
					foreach($field_no_rights as $val){
						if($field_info[$k]['id'] == $val['right_id']){
							$field_info[$k]['checked'] = false;
						}
					}
				}else{
					foreach($field_rights_info as $val){
						if($field_info[$k]['id'] == $val['right_id']){
							$field_info[$k]['checked'] = true;
						}
					}
				}
			}
			$res['data'] = $field_info;
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$res['info'] = self::UNKNOWN_ERROR;
			$res['status'] = 1;
		}
		$this->ajaxReturn($res);
	}
	public function getRightsProviderList()
	{
		$res = array('status'=>0,'info'=>'成功','data'=>array());
		$employee_id=intval(I('post.id'));
		$type=intval(I('post.type'));
		$result=array();
		try
		{
			//$result=D('Setting/Warehouse')->field("warehouse_id AS id,name")->order('warehouse_id ASC')->select();
			$result=D('Setting/PurchaseProvider')->field("id,provider_name AS name")->where('id',array('neq',0))->order('id ASC')->select();
			$rights=D('EmployeeRights')->getEmployeeRightsList('rec_id,right_id', array('employee_id'=>array('eq',$employee_id),'is_denied'=>array('eq',0),'type'=>array('eq',$type)));
			$rights_list_count=D('EmployeeRights')->where(array('employee_id'=>array('eq',$employee_id),'type'=>array('eq',$type)))->count();
			$is_checked = false;
			if($rights_list_count == 0)
			{
				$is_checked = true;
			}
			$len=count($result);
			for($i=0;$i<$len;$i++)
			{
				if($is_checked)
				{
					$result[$i]['checked']=true;
					continue;
				}
				foreach ($rights as $r)
				{
					if($result[$i]['id']==$r['right_id'] )
					{
						$result[$i]['checked']=true;
					}
				}
			}
			$res['data']=$result;
		}catch (\PDOException $e)
		{
			\Think\Log::write($e->getMessage());
			$res['info'] = self::UNKNOWN_ERROR;
			$res['status']=1;
		}

		$this->ajaxReturn($res);
	}
	/*
	 * 批量导入员工
	 * */
	public function uploadExcel(){
		//获取表格相关信息
		$file = $_FILES["file"]["tmp_name"];
		$name = $_FILES["file"]["name"];
		try{
			$sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
			$sql      = "CREATE TABLE IF NOT EXISTS `tmp_import_detail` (`rec_id` INT NOT NULL AUTO_INCREMENT,`id` SMALLINT,`status` TINYINT,`result` VARCHAR(30),`message` VARCHAR(60),PRIMARY KEY(`rec_id`))";
			$M = M();
			$M->execute($sql_drop);
			$M->execute($sql);
			$importDB = M("tmp_import_detail");
			$excelClass = new ExcelTool();
			$excelClass->checkExcelFile($name,$file);
			$excelClass->uploadFile($file,"EmployeeImport");
			$count = $excelClass->getExcelCount();
			//建立临时表，存储数据处理的结果
			$excelData = $excelClass->Excel2Arr($count);
			//如果tmp_import_detail表已存在，就删除并重新创建
			$res = array();
			//处理数据，将数据插入数据库并返回信息
			$EmployeeDB = D("Employee");
			foreach ($excelData as $sheet) {
				for ($k = 1; $k < count($sheet); $k++) {
					$row = $sheet[$k];
					if (UtilTool::checkArrValue($row)) continue;
					//分类存储数据
					$i = 0;
					$data["account"] = $this->dropBlack($row[$i]);
					$data["fullname"] =  $this->dropBlack($row[++$i]);
					$data['password']=  $this->dropBlack($row[++$i]);
					$data["mobile_no"] =  $this->dropBlack($row[++$i]);
					$data["position"] = $this->dropBlack($row[++$i]);
					$data["qq"] = $this->dropBlack($row[++$i]);
					$data["wangwang"] = $this->dropBlack($row[++$i]);
					$data["email"] = $this->dropBlack($row[++$i]);
					$gender=0;
					$sex=$this->dropBlack($row[++$i]);
					if($sex=='男'){
						$gender=1;
					}elseif($sex=='女'){
						$gender=2;
					}
					$data["gender"] = $gender;
					$field_rights=0;
					$field=$this->dropBlack($row[++$i]);
					if($field=='是'){
						$field_rights=1;
					}
					$data["field_rights"] = $field_rights;
					$data["shortname"] = time();
					$data["type"] = 'add';
					$data["add_type"] = 'input';
					$M->startTrans();
					try{
						$EmployeeDB->saveEmployee($data);
						$M->commit();
					}catch (\Exception $e) {
						$M->rollback();
						$err_code = $e->getCode();
						if ($err_code == 0) {
							$err_msg = array("id" => $k + 1, "status" => $err_code, "message" => $e->getMessage(), "result" => "失败");
							$importDB->data($err_msg)->add();
						} else {
							$err_msg = array("id" => $k + 1, "status" => $err_code, "message" =>parent::UNKNOWN_ERROR, "result" => "失败");
							$importDB->data($err_msg)->add();
							Log::write($e->getMessage());
						}
					}

				}
			}
		}catch (BusinessLogicException $e){
			$res['status'] = 1;
			$res['info'] = $e->getMessage();
			$this->ajaxReturn(json_encode($res), "EVAL");
		}catch (\Exception $e){
			Log::write($e->getMessage());
			$res["status"] = 1;
			$res["info"]   = parent::UNKNOWN_ERROR;
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
		unset($data);
		try {
			$sql    = "SELECT id,status,result,message FROM tmp_import_detail";
			$result = $M->query($sql);
			if (count($result) == 0) {
				$res["status"] = 0;
				$res["info"]   = "操作成功";
			} else {
				$res["status"] = 2;
				$res["info"]   = $result;
			}
			$sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
			$M->execute($sql_drop);
		} catch (BusinessLogicException $e) {
			$res["status"] = 1;
			$res["info"] = $e->getMessage();
		}catch (\Exception $e) {
			$res["status"] = 1;
			$res["info"] = parent::UNKNOWN_ERROR;
		}

		/*header('Con')}tent-Type:text/plain; charset=utf-8');
        exit(json_encode($res, 0));*/
		$this->ajaxReturn(json_encode($res), "EVAL");
	}

	//去除空格函数
	function dropBlack($str){
		$str=str_replace(" ","",$str);
		$str=str_replace("　","",$str);
		$str=str_replace(" ","",$str);
		$str=str_replace("　","",$str);
		$str=str_replace(" ","",$str);
		return $str;
	}

	//下载员工导入模板
	public function downloadTemplet(){
		$file_name = "员工导入模板.xls";
		$file_sub_path = APP_PATH."Runtime/File/";
		try{
			ExcelTool::downloadTemplet($file_name,$file_sub_path);
		} catch (BusinessLogicException $e){
			Log::write($e->getMessage());
			echo '对不起，模板不存在，下载失败！';
		} catch (\Exception $e) {
			Log::write($e->getMessage());
			echo parent::UNKNOWN_ERROR;
		}


	}
	public function importRight()
    {
        try{
            $operator_id = get_operator_id();
            if($operator_id > 1){
                SE('非管理员，没有导入权限');
            }
        }catch (BusinessLogicException $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }catch(Exception $e){
            \Think\Log::write(__CONTROLLER__.'-importRight-'.$e->getMessage());
            $this->assign('message',self::UNKNOWN_ERROR);
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('import_right');
    }
    public function uploadRightExcel()
    {
        //获取表格相关信息
        $result = array(
            'status' => 0,
            'data'=>array(),
            'info'=>'成功'
        );
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        try {
            //将表格读取为数据
            $excelData = UtilTool::Excel2Arr($name, $file, "EmployeeRightImport");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
            $res["info"]   = "文件错误，无法读取";
            $this->ajaxReturn(json_encode($res),'EVAL');
        }
        try {
            $model = D('Setting/Employee');
            $fail_result = array();
            $data = array();
            $account_arr =array();
            foreach ($excelData as $sheet) {
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[ $k ];
                    //分类存储数据
                    $i                                  = 0;
                    $line = $k+1;
                    $is_full_row = 1; //1  标示为空行,需要屏蔽

                    $temp_data = array(
                        "account"           => trim($row[$i]),//员工账号
                        'shop'              => trim($row[++$i]),//店铺列表
                        'url'               => trim($row[++$i]),//菜单权限
                        'line'              => trim($line),//行号
                        'status'            => 0,
                        'message'           =>'',
                        'result'            =>'成功'
                    );
                    if(isset($account_arr["{$temp_data['account']}"])){
                        $account_arr["{$temp_data['account']}"] = false;
                    }else{
                        $account_arr["{$temp_data['account']}"] = true;
                    }
                    foreach($temp_data as $temp_key => $temp_value)
                    {
                        if(!empty($temp_value)&& $temp_key!='line'&& $temp_key!='message'&& $temp_key!='result'&& $temp_key!='status'){
                            $is_full_row = 0;
                        }
                    }
                    if($is_full_row == 0){
                        $data[] = $temp_data;
                    }

                }
            }
            //筛选重复的商家编码
            foreach($data as $key => $value)
            {
                if($account_arr["{$value['account']}"] == false){
                    $data[$key]['status'] = 1;
                    $data[$key]['message'] ='导入员工['.$value['account'].']账号重复';
                    $data[$key]['result'] ='失败';
                }
            }
            if(empty($data)){
                E('读取导入的数据失败!');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['info'] =$msg;
        }
        $model->importEmployeeRight($data,$result);
        $this->ajaxReturn(json_encode($result),'EVAL');
    }
    public function downloadRightTemplet()
    {
        $file_name = "员工权限导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }

}