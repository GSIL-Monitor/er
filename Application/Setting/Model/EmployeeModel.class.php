<?php


namespace Setting\Model;

use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;


class EmployeeModel extends Model
{
    protected $tableName = 'hr_employee';
    protected $pk = 'employee_id';

	protected $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        //          验证条件
        // 			self::EXIST S_VALIDAT E 或者0 存在字段就验证（默认）
        // 			self::MUST _VALIDAT E 或者1 必须验证
        // 			self::VALUE_VALIDAT E或者2 值不为空的时候验证
        //          验证时间
        // 			self::MODEL_INSERT或者1新增数据时候验证
        // 			self::MODEL_UPDAT E或者2编辑数据时候验证
        // 			self::MODEL_BOT H或者3全部情况下验证（默认）
        array('gender', array(2, 1, 0), '性别类型不正确!', 1, 'in', 3),
        array('account', '/^[\w]+$/', '员工账号只能包含英文数字和下划线!', 1, 'regex', 3),
        array('account', '', '员工账号重复，请重新填写!', 1, 'unique', 3),
        array('fullname', '/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u', '姓名格式错误!', 1, 'regex', 3),
		array('fullname', '', '员工姓名重复，请重新填写!', 1, 'unique', 3),
		array('mobile_no', '/(^([0\+]\d{2,3})\d{3,4}\-\d{3,8}$)|(^([0\+]\d{2,3})\d{3,4}\d{3,8}$)|(^([0\+]\d{2,3}){0,1}13\d{9}$)|(^\d{3,4}\d{3,8}$)|(^\d{3,4}\-\d{3,8}$)/', '非法手机号码', 1, 'regex', 1),
        array('qq', '/^[1-9]\d{4,10}$/', 'QQ不对', 2, 'regex', 3),
        array('wangwang', '/^[a-zA-Z0-9_]{1,}$/', '旺旺不对', 2, 'regex', 3),
        array('email', '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', '邮箱不对', 2, 'regex', 3),
        array('field_rights', 'number', '权限选项非法', 1),
    );
    protected $searchArray = array("employee_id" => "id", "account", "fullname", "gender", "position", "roles_mask","field_rights","mobile_no","qq","email","wangwang","last_login_time");

    public function searchEmployee($page, $rows, $search, $sort, $order,$where_limit=array('employee_id'=>array('gt',0)))
    {
        foreach ($search as $k => $v) {
            if ($v==='') continue;
            switch ($k) {
                case 'fullname':
                    set_search_form_value($where_limit, $k, $v, '', 1);
                    break;
                case 'account':
                    set_search_form_value($where_limit, $k, $v, '', 1);
                    break;
                default:
                    break;
            }
        }
        $page=intval($page);
        $rows=intval($rows);
        $order = $sort . ' ' . $order;//排序
        $order = addslashes($order);
        try {
            $total = $this->where($where_limit)->count();
            $list = $this->fetchSql(false)->field($this->searchArray)->where($where_limit)->page($page, $rows)->order($order)->select();
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }
	
    public function getLoginLogs($id)
    {
    	$data = array('total' => 0, 'rows' => array());
    	try {
    		$list[] = $this->getEmployee('employee_id AS id,last_login_time AS created,last_login_ip AS message',array('employee_id'=>array('eq',$id)));
    		$data=array('total'=>count($list),'rows'=>$list);
    	} catch (\Exception $e) {
    		\Think\Log::write($this->name.'-getLoginLogs-'.$e->getMessage(),\Think\Log::WARN);
    	}
    	return $data;
    }
    
    public function getRole($user_id)
    {
    	$employee=$this->getEmployee('roles_mask AS role',array('employee_id'=>array('eq',$user_id)));
    	return empty($employee)?0:$employee['role'];
    }
    
	public function getEmployee($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getEmployee-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }   

	public function updateEmployee($data,$where)
    {
    	try {
    		$res = $this->where($where)->save($data);
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-updateEmployee-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }

    public function delEmployee($id)
    {
        try {
        	$role=$this->getRole(get_operator_id());
        	$employee_role=$this->getRole($id);
        	if($role<=$employee_role&&$role>0)
        	{
        		E('不能删除其他管理员用户！');
        	}
			$oldEmployee = $this->getEmployeeById($id);
			$arr_sys_other_log = array(
				"type"        => "20",
				"operator_id" => get_operator_id(),
				"data"        => $id,
				"message"     => "删除员工--员工姓名--“" . $oldEmployee[0]["fullname"].'”',
				"careted"     => date("Y-m-d G:i:s")
			);
			M("sys_other_log")->data($arr_sys_other_log)->add();
            $this->where(array("employee_id" => $id))->delete();
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E(self::PDO_ERROR);
        }
    }

    public function loadSelectedData($id)
    {
		
        $re['status'] = 0;
        $re['data'] = "";
        try {
            $tmp = $this->where(array("employee_id" => $id))->field($this->searchArray)->select();
		   $re['data'] = $tmp[0];
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $re['status'] = 1;
            $re['data'] = self::PDO_ERROR;
        }
        return $re;

    }

    public function saveEmployee($arr,$chang_arr=array())
    {
        try{
			$oldEmployee = $this->getEmployeeById($arr['employee_id']);
        	if('add'==$arr['type'])
        	{
				unset($arr['type']);
				unset($arr['employee_id']);
				if($arr['add_type']=='input'){
					$this->checkData($arr);
					$chang_arr['password']=md5($arr['password']);
				}else{
					if(!$this->create($arr,1))
					{
						SE($this->getError());
					}
				}
        		$id=$this->add($arr);
        		$this->changePassword($chang_arr['password'],$id);
        		//新增员工 初始化  员工权限
        		$menus=M('dict_url')->field("url_id,parent_id")->where(array('type'=>array('eq',0)))->select();
        		$menu=M('dict_url')->field("url_id,parent_id")->where(array('module'=>array('eq','Setting'),'controller'=>array('eq','CfgOperReason')))->find();
        		if(!empty($menu))
        		{
        			$menus[]=$menu;
        		}
        		$rights=array();
        		$map=array();
        		foreach ($menus as $m)
        		{
        			if(!isset($map[$m['parent_id']]))
        			{
        				$map[$m['parent_id']]=true;
        				$rights[]=array(
        						'right_id'=>$m['parent_id'],
        						'employee_id'=>$id,
        						'is_denied'=>0,
        						'type'=>0,
        						'modified'=>date('Y-m-d H:i:s',time()),
        						'created'=>date('Y-m-d H:i:s',time()),
        				);
        			}
        			$rights[]=array(
        					'right_id'=>$m['url_id'],
        					'employee_id'=>$id,
        					'is_denied'=>0,
        					'type'=>0,
        					'modified'=>date('Y-m-d H:i:s',time()),
        					'created'=>date('Y-m-d H:i:s',time()),
        			);
        		}
        		$employee_id=D('EmployeeRights')->addEmployeeRights($rights);
				$arr_sys_other_log = array(
                    "type"        => "20",
                    "operator_id" => get_operator_id(),
                    "data"        => $employee_id,
                    "message"     => "新建员工--员工姓名--“" . $arr["fullname"].'”',
                    "careted"     => date("Y-m-d G:i:s")
                );
				M("sys_other_log")->data($arr_sys_other_log)->add();
        		/* $menus=M('dict_url')->field("url_id,CONCAT(module,IF(controller='','','_'),controller,IF(action='','','_'),action) mca,parent_id")->select();
        		$rights=array();
        		$roles_default_rigths=C('roles_default_rights');
        		$map=array();
        		foreach ($menus as $m)
        		{
        			isset($map[$m['parent_id']])?$map[$m['parent_id']]++:$map[$m['parent_id']]=1;
        			if(!isset($roles_default_rigths[$arr['roles_mask']][strtolower($m['mca'])]))
        			{
        				$rights[]=array(
        						'right_id'=>$m['url_id'],
        						'employee_id'=>$id,
        						'is_denied'=>0,
        						'type'=>0,
        						'modified'=>date('Y-m-d H:i:s',time()),
        						'created'=>date('Y-m-d H:i:s',time()),
        				);
        			}else
        			{
        				isset($map[$m['parent_id']])?$map[$m['parent_id']]--:$map[$m['parent_id']]=1;
        			}
        		}
        		$length=count($rights);
        		for ($i=0;$i<$length;$i++)
        		{
        			if(isset($map[$rights[$i]['right_id']]) && $map[$rights[$i]['right_id']]<=0)
        			{
        				unset($rights[$i]);
        			}
        		}
        		D('EmployeeRights')->addEmployeeRights($rights); */
        	}else 
        	{
        		unset($arr['mobile_no']);
        		unset($arr['type']);
        		if(!$this->create($arr,2))
        		{
        			E($this->getError());
        		}
        		$employee_id=$this->updateEmployee($arr, array('employee_id'=>array('eq',$arr['employee_id'])));
				$arr_sys_other_log=array();
				
				if($oldEmployee[0]['account']!= $arr['account']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--账号--从“' . $oldEmployee[0]["account"] .'”  到  “'. $arr["account"].'”'
                        );
				}
				if($oldEmployee[0]['fullname']!= $arr['fullname']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--姓名--从“' . $oldEmployee[0]["fullname"] .'”  到  “'. $arr["fullname"].'”'
                        );
				}
				if($oldEmployee[0]['position']!= $arr['position']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--职位--从“' . $oldEmployee[0]["position"] .'”  到  “'. $arr["position"].'”'
                        );
				}
				if($oldEmployee[0]['gender']!= $arr['gender']){
					if($oldEmployee[0]['gender']=="0"){
						$old_gender="不确定";
					}elseif($oldEmployee[0]['gender']=="1"){
						$old_gender="男";
					}else{
						$old_gender="女";
					}
					if($arr['gender']=="0"){
						$gender="不确定";
					}elseif($arr[0]['gender']=="1"){
						$gender="女";
					}else{
						$gender="男";
					}
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message'=>'编辑员工--性别--从“' . $old_gender.'”  到  “'. $gender.'”'
                        );
				}
				if($oldEmployee[0]['field_rights']!= $arr['field_rights']){
					if($oldEmployee[0]['field_rights']=="0"){
						$old_field_rights="否";
					}else{
						$old_field_rights="是";
					}
					if($arr['field_rights']=="0"){
						$field_rights="否";
					}else{
						$field_rights="是";
					}
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--查看号码权限--从“' . $old_field_rights.'”  到  “'. $field_rights.'”'
                        );
				}
				if($oldEmployee[0]['qq']!= $arr['qq']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--QQ--从“' . $oldEmployee[0]["qq"] .'  到  ”  到  “'. $arr["qq"].'”'
                        );
				}
				if($oldEmployee[0]['email']!= $arr['email']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--Email--从“' . $oldEmployee[0]["email"] .'”  到  “'. $arr["email"].'”'
                        );
				}
				if($oldEmployee[0]['wangwang']!= $arr['wangwang']){
                    $arr_sys_other_log[]=array(
                        'type'=>"20",
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$employee_id,
                        'message' =>'编辑员工--旺旺--从“ ' . $oldEmployee[0]["wangwang"] .'”  到  “'. $arr["wangwang"].'”'
                        );
				}
                M("sys_other_log")->addall($arr_sys_other_log);
				
        	}
        }catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E(self::PDO_ERROR);
        }
    }

	/*
	 * 批量导入数据验证
	 * */
	public function checkData($arr){
		if (!isset($arr["fullname"]) || $arr["fullname"] == "")SE("姓名不能为空");
		if(!preg_match('/^[\x{4e00}-\x{9fa5}0-9a-zA-Z]+$/u',$arr['fullname']))SE('姓名格式错误');
		$fullname['fullname']=$arr['fullname'];
		if($this->where($fullname)->find())SE("姓名重复，请重新填写!");
		if (!isset($arr["password"]) || $arr["password"] == "")SE("密码不能为空");
		if(!preg_match('/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).*$/',$arr["password"]))SE("您的密码强度较弱，请修改弱密码!");
		if (!isset($arr["mobile_no"]) || $arr["mobile_no"] == "")SE("手机号不能为空");
		if(!preg_match('/^(?:13\d|15\d|18\d|17\d|14\d)-?\d{5}(\d{3}|\*{3})$/',$arr['mobile_no']))SE('非法手机号码');
		$mobile_no['mobile_no']=$arr['mobile_no'];
		if($this->where($mobile_no)->find())SE("手机号重复，请重新填写!");
		if (!isset($arr["account"]) || $arr["account"] == "")SE("账号不能为空");
		if(!preg_match('/^[\w]+$/',$arr['account']))SE('员工账号只能包含英文数字和下划线!');
		$account['account']=$arr['account'];
		if($this->where($account)->find())SE("员工账号重复，请重新填写!");
		if (isset($arr["qq"]) && $arr["qq"] != "" && !preg_match('/^[1-9]\d{4,10}$/',$arr['qq']))SE("请填写正确的QQ!");
		if (isset($arr["wangwang"]) && $arr["wangwang"] != "" && !preg_match('/^[a-zA-Z0-9_]{1,}$/',$arr['wangwang']))SE("请填写正确的旺旺!");
		if (isset($arr["email"]) && $arr["email"] != "" ){
			if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',$arr['email'])){
				SE("请填写正确的email!");
			}
			$email['email']=$arr['email'];
			if($this->where($email)->find()){
				SE("邮箱重复，请重新填写!");
			}
		}
		if (isset($arr["field_rights"]) && $arr["field_rights"] != "" && !in_array($arr['field_rights'],[0,1]))SE("权限选项非法!");
		if (isset($arr["gender"]) && $arr["gender"] != "" && !in_array($arr['gender'],[0,1,2]) )SE("性别类型不正确!");
	}

	public function login($account, $password, $sid, $client_ip, &$ticket, &$employee_id){
		try{
			$employee = $this->where(array("account" => $account))->field('employee_id,password,salt')->select();
			if(empty($employee)){
				E("用户不存在或已删除!");
			}
			if ($this->encrypt($password . $employee[0]['salt'])!= $employee[0]['password']){
				E("密码不正确!");
			}
			$employee_id = $employee[0]['employee_id'];
			$sso = new \stdClass();
			$sso->timestamp = time();
			$sso->sid = $sid;
			$ticket = md5($sso->timestamp);
			$sso->ticket = $ticket;
			$sso = json_encode($sso);
			$this->save(
				array(//更新最后一次登录的ip和时间
					'employee_id' => $employee[0]['employee_id'], 
					'sso' => $sso, 
					//'last_login_time' => date('Y-m-d H:i:s',time()), 
					'last_login_ip'=> $client_ip
				)
			);
			/* D('SysOtherLog')->addSysOtherLog(
				array(
					'type'=>1,//记录用户最近的登录日志
					'operator_id'=>$employee[0]['employee_id'],
					'message'=> $client_ip,
					'created'=> date('Y-m-d H:i:s',time()),
				)
			); */
			$res_cfg_val=get_config_value('login_check_code',1);//安全验证是否开启--1表示开启(默认) --0表示不开启
			if($res_cfg_val!=0)
			{
				return $employee[0]['employee_id'];
			}
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write('login:'.$msg);
            E(self::PDO_ERROR);
        }
        return true;
	}
	
	public function sso($account, $ticket){
		try{
			$employee = $this->where(array("account" => $account))->field('employee_id,sso')->select();
			$this->save(array('employee_id' => $employee[0]['employee_id'], 'sso' => '', 'last_login_time' => date('Y-m-d H:i:s',time())));
			$sso = json_decode($employee[0]['sso']);
			if (empty($sso) || $sso->timestamp + 30 < time() || $sso->ticket != $ticket){
				\Think\Log::write("wrong sso, account:".$account);
				E("无效登陆,请尝试重新登陆!");
			}

			$auth=get_config_value('login_check_code',0);
			if (empty($auth)){
				session("sid", $sso->sid);
				session("account", $account);
				session("operator_id", $employee[0]['employee_id']);
				return 0;
			} else {
				return $employee[0]['employee_id'];
			}
			
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            E(self::PDO_ERROR);
        }
		
	}
	public function changePassword($password, $employee_id){
		try {
            $salt = md5(time());
			$pwd = $this->encrypt($password . $salt);
            $this->save(array('employee_id' => $employee_id, 'password' => $pwd, 'salt' => $salt));
			/*if(get_operator_id() == $employee_id||1 == get_operator_id())
            {
    			$this->save(array('employee_id' => $employee_id, 'password' => $pwd, 'salt' => $salt));
				$re['status'] = 0;
			}else{
				$re['status']=1;
				$re['data'] = '无法修改他人的信息';
			}*/
		} catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            E(self::PDO_ERROR);
        }
	}
	
	public function encrypt($data){
		return strtolower(md5($data));
	}
	public function saveAccountmanagement($arr)
    {
    	try {
        	if(!$this->create($arr,2))
        	{
        		SE($this->getError());
        	}
        	$this->updateEmployee($arr, array('employee_id'=>array('eq',$arr['employee_id'])));
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-saveAccountmanagement-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function importEmployeeRight($data,&$result)
    {
        try{
            $operator_id = get_operator_id();
            $rules = array(
                array('account','require','员工账号必须！'), //默认情况下用正则进行验证
                array('shop','checkShopList','请认真核对店铺名称，重新录入！',1,'callback'), // 在新增的时候验证name字段是否唯一
                array('account','checkAccount','员工账号不存在，请核对后重新录入！',1,'callback'), // 在新增的时候验证name字段是否唯一
            );
            $this->patchValidate = true;
            foreach ($data as $import_index => $import_rows)
            {
                if (!$this->validate($rules)->create($import_rows)) {
                    // 如果创建失败 表示验证没有通过 输出错误提示信息
                    $data[$import_index]['status'] = 1;
                    $data[$import_index]['result'] = '失败';
                    $data[$import_index]['message'] = implode('--',$this->getError());
                }
            }
            $fail = array();
            $this->startTrans();
            foreach($data as $key => $value)
            {
                if($value['status'] == 0)
                {
                    $shop_list = D('Setting/Shop')->field('GROUP_CONCAT(DISTINCT shop_id ORDER BY shop_id) shop_list')->where(array('shop_name' => array('in',$value['shop'])))->find();
                    $employee_id = D('Setting/Employee')->where(array('account' => $value['account']))->getField('employee_id');
                    $shop_ids =  explode(',',$shop_list['shop_list']);
                    if(isset($shop_ids[0]) && empty($shop_ids[0]))
                    {
                        $shop_ids = array();
                    }

                    D('EmployeeRights')->changeRights($shop_ids,$employee_id,1);
                    $url_list = explode(',',$value['url']);
                    if(isset($url_list[0]) && empty($url_list[0]))
                    {
                        $url_list = array();
                    }
                    D('EmployeeRights')->changeRights($url_list,$employee_id,0);
                }else{
                    $fail[] = array(
                        'id' => $value['line'],
                        'message' => $value['message'],
                        'status' => $value['status'],
                        'result' => $value['result']
                    );
                }
            }
            $this->commit();
            if(!empty($fail))
            {
                $result['status'] = 2;
                $result['data'] = $fail;

            }
        }catch (\PDOException $e) {
            \Think\Log::write($this->name.'-importEmployeeRight-'.$e->getMessage());
            $result['status'] = 1;
            $result['info'] = self::PDO_ERROR;
        }catch(Exception $e){
            \Think\Log::write($this->name.'-importEmployeeRight-'.$e->getMessage());
            $result['status'] = 1;
            $result['info'] =self::PDO_ERROR;
        }
    }
    public function changeRights($right_ids,$employee_id,$right_type)
    {
        try{
            $this->startTrans();
            D('EmployeeRights')->changeRights($right_ids,$employee_id,$right_type);
            $this->commit();
        }catch(\PDOException $e) {
            $this->rollback();
            \Think\Log::write($this->name.'-changeRights-'.$e->getMessage());
            E(self::PDO_ERROR);
        }catch(Exception $e){
            $this->rollback();
            \Think\Log::write($this->name.'-changeRights-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }
    protected  function checkShopList($shop)
    {
        try{
            if(empty($shop))
            {
                return true;
            }
            $shop_arr = explode(',',$shop);
            foreach ($shop_arr as $value)
            {
                $is_set = D('Setting/shop')->checkShop($value,'shop_name');
                if(!$is_set)
                {
                    return $is_set;
                }
            }
            return true;
        } catch (\PDOException $e) {
            E($e->getMessage());
        }
    }
    protected  function checkAccount($value,$key='account')
    {
        try {
            $map[$key]=$value;
            $result=$this->field('employee_id')->where($map)->find();
            if(!empty($result))
            {
                return true;
            }
        } catch (\PDOException $e) {
            E($e->getMessage());
        }
        return false;
    }
	public function getEmployeeById($employee_id){
        $res = array();
        try{
            $res=$this->field('fullname,account,gender,position,qq,email,wangwang,field_rights')->where(array('employee_id'=>$employee_id))->select();
        }catch (\PDOException $e){
            \Think\Log::write('getEmployeeById SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getEmployeeById'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

}