<?php

namespace Home\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilTool;
use Platform\Common\ManagerFactory;

class LoginController extends BaseController
{
	function _initialize()
	{
	}
 
    public function login($type='login')
    {
    	$is_logout='';
    	if($type=='logout'&&is_login())
    	{
    		session("sid", null);
    		session("account", null);
    		session("operator_id", null);
    		$is_logout='logout';
    	}else if(!empty(session('account')))
    	{
    		js_redirect(C('APP_INDEX_PAGE'));
    	}
    	//安全验证session是否存在，存在则清空session
    	$code=session('json_mobile_code');
    	if(!empty($code))
    	{
    		session("json_mobile_code", null);
    	}
    	$time=session('user_id_time');
    	if(!empty($time))
    	{
    		session("user_id_time", null);
    	}
        $log_update_time=filemtime(APP_PATH."Help/Controller/update.txt"); //获取日志文件的更新时间（时间戳类型）
        $this->assign('version_number', $log_update_time);//js、css版本号
    	$this->assign('logout',$is_logout);
	    $this->display('login');
    }
    public function check($user_num_id=0)
    {
    	$id=intval($user_num_id);
    	try {
    		//安全验证页面时效性
    		$json_user=session('user_id_time');
    		if(empty($json_user))
    		{
    			E('安全验证页面已失效');
    		}
    		$json_user=json_decode($json_user);
    		if(strtotime($json_user->time)+intval(C('CHECK_PAGE_MAX_INVALID_TIME'))<time())
    		{
    			E('安全验证页面已失效');
    		}
    		if($json_user->user_num_id!=$id)
    		{
    			$id=$json_user->user_num_id;
    		}
    		$employee=D('Setting/Employee')->getEmployee('INSERT(mobile_no,4,4,\'****\') mobile',array('employee_id'=>array('eq',$id)));
    		if(empty($employee))
    		{
    			E('用户不存在');
    		}
    		$this->assign('user_num_id',$id);
    		$this->assign('mobile',$employee['mobile']);
    		$this->display('check');
    	} catch (\Exception $e) {
    		\Think\Log::write('check:'.$e->getMessage(),\Think\Log::WARN);
    		session('user_id_time',null);
    		js_redirect(U('Home/Login/login'));
    	}
    }
    public function sendCode()
    {
    	$data=I('post.data','',C('JSON_FILTER'));
    	$result=array('status'=>0,'info'=>'');
    	try {
    		//安全验证页面时效性
    		$json_user=session('user_id_time');
    		if(empty($json_user))
    		{
    			E('安全验证页面已失效');
    		}
    		$json_user=json_decode($json_user);
    		if(strtotime($json_user->time)+intval(C('CHECK_PAGE_MAX_INVALID_TIME'))<time())
    		{
    			session('user_id_time',null);
    			E('安全验证页面已失效');
    		}
    		if($json_user->user_num_id!=$data['user_num_id'])
    		{
    			$data['user_num_id']=$json_user->user_num_id;
    		}
    		//每操作一次，增加安全验证页面的时效性(更新到session中)
    		session('user_id_time',json_encode(array('user_num_id'=>$data['user_num_id'],'time'=>date("Y-m-d H:i:s",time()+intval(C('MOBILE_CODE_INVALID_TIME'))))));
    		
    		$employee=D('Setting/Employee')->getEmployee('mobile_no AS mobile',array('employee_id'=>array('eq',$data['user_num_id'])));
    		if((empty($employee)||empty($employee['mobile']))&&!check_regex('mobile',$data['mobile']))
    		{
    			E('手机号码格式不正确');
    		}
    		$mobile=empty($employee['mobile'])?$data['mobile']:$employee['mobile'];
    		$mobile.=',';
    		$mobile_code = substr(microtime(), 2, 6);
    		$message='【E快帮】正在进行网页登录，验证码：'.$mobile_code.'。';
    		$res=UtilTool::SMS($mobile, $message,'checkcode');
			if(!is_array($res))
			{
				$result['status'] =1;
				$result['info'] = '未知错误,请联系管理员';
				$this->ajaxReturn($result);
			}
    		if($res['status']==0)
    		{
    			$json_mobile_code=array('mobile_code'=>$mobile_code,'send_time'=>date("Y-m-d H:i:s",time()));
    			if(empty($employee['mobile']))
    			{
    				$json_mobile_code['mobile']=$data['mobile'];
    			}
    			$json_mobile_code['times']=0;
    			session('json_mobile_code',json_encode($json_mobile_code));
    			//记录验证码日志
    			\Think\Log::write('mobile:'.$mobile.' code:'.$json_mobile_code['mobile_code'],'INFO');
    			$result['status']=0;
    			$result['info']='验证码发送成功，注意查收！';
    		}elseif($res['info']=='您的账户还没有充值,请先充值'){
				$result['status']=1;
				$result['info']='短信余额不足，请先联系管理员进行充值';
			}elseif($res['info']=='未查询到用户信息'){
				$result['status']=1;
				$result['info']='您的账户还没有充值,请先充值';
			}elseif($res['status']==15){
				$result['status']=1;
				$result['info']='短信账号未开,请联系管理员';
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
    public function checkCode()
    {
    	$data=I('post.data','',C('JSON_FILTER'));
    	$result=array('status'=>0,'info'=>'');
    	try {
    		//-----安全验证页面时效性--这里可不校验
    		$json_user=session('user_id_time');
    		if(empty($json_user))
    		{
    			E('安全验证页面已失效');
    		}
    		$json_user=json_decode($json_user);
    		if(strtotime($json_user->time)+intval(C('CHECK_PAGE_MAX_INVALID_TIME'))<time())
    		{
    			session('user_id_time',null);
    			E('安全验证页面已失效');
    		}
    		if($json_user->user_num_id!=$data['user_num_id'])
    		{
    			$data['user_num_id']=$json_user->user_num_id;
    		}
    		//-----验证校验码失效-----
    		$json_mobile_code=session('json_mobile_code');
    		if(empty($json_mobile_code))
    		{
    			E('验证码已过期，请重新获取');
    		}
    		$json_mobile_code=json_decode(session('json_mobile_code'));
    		if(strtotime($json_mobile_code->send_time)+intval(C('MOBILE_CODE_INVALID_TIME'))<time())
    		{
    			session("json_mobile_code", null);
    			E('验证码已过期，请重新获取');
    		}
    		//-----验证次数不能大于5次-------
    		$json_mobile_code->times=intval($json_mobile_code->times);
    		if(intval($json_mobile_code->times)<5)
    		{
    			$json_mobile_code->times+=1;
    			session('json_mobile_code',json_encode($json_mobile_code));
    		}else 
    		{
    			E('验证太频繁，请稍后再试...');
    		}
    		if(check_regex('zip',$data['code'])&&$data['code']==$json_mobile_code->mobile_code)
    		{
    			$employee_db=D('Setting/Employee');
    			$employee=$employee_db->getEmployee('employee_id,account',array('employee_id'=>array('eq',$data['user_num_id'])));
    			if(empty($employee['account'])||empty($employee['employee_id']))
    			{
    				E('验证失败，当前用户不存在或已删除');
    			}
    			$arr_employee=array(
    					//'employee_id' => $employee['employee_id'], 
    					'sso' =>array('timestamp'=>time(),'sid'=>get_sid()), 
    					//'last_login_time' => date('Y-m-d H:i:s',time()),
    					'last_login_ip'=>  get_client_ip()
    			);
    			if(!empty($json_mobile_code->mobile))
    			{
    				$arr_employee['mobile_no']=$json_mobile_code->mobile;
    			}
    			$employee_db->updateEmployee($arr_employee,array('employee_id'=>array('eq',$employee['employee_id'])));
    			
    			$result['info']=C('APP_INDEX_PAGE');
    		}else{
    			E('验证失败');
    		}
    	}catch (\Exception $e){
    		$msg=$e->getMessage();
    		\Think\Log::write('sendCode:'.$msg,\Think\Log::WARN);
    		$result['status']=1;//失败
    		$result['info']=$msg;
    	}
		$login_manager = ManagerFactory::getManager('Login');
		if (0 == $result['status']){
			$login_manager->log(true, '');
			try{
				session('account', $employee['account']);
            	session('operator_id', $employee['employee_id']);
				$url = $login_manager->computeRisk($employee['employee_id']);	
				if (!empty($url)){
					$result['status']=2;
					$result['info'] = $url;
				}
            	session("json_mobile_code", null);
            	session("user_id_time", null);
			} catch(\Exception $e) {
				$msg = $e->getMessage();
				$result['status']=1;
				$result['info'] = $msg;
				\Think\Log::write('computeRisk:'.$msg);
				session('account', null);
            	session('operator_id', null);
			}
		} else {
			try {
				$login_manager->log(false, $result['info']);
			} catch (\Exception $e) {
				$result['status']=1;
				$result['info']=$e->getMessage();
				\Think\Log::write('login_manager-log:'.$result['info'],\Think\Log::WARN);
			}
		}
    	$this->ajaxReturn($result);
    }
	
    public function access($data)
    {
		$data = json_decode($data);
        $username = trim_all($data->username,1);
        $password = trim_all($data->password,1);
        $sid = trim_all($data->sid,1);
		$status = 0;
		session("sid", $sid);
		$r = new \stdClass();
		$is_login = false;
		try {
        	if($username===''||!check_regex('english_num',$username))
        	{
        		\Think\Log::write('invalid user name:'.$username);
				E('非法用户名');
        	}
        	if($sid===''||!check_regex('english_num',$sid))
        	{
        		\Think\Log::write('invalid sid:'.$sid);
				E('非法卖家账号');
        	}
        	$scheme = I('server.REQUEST_SCHEME');
        	$scheme = empty($scheme)? 'http' : $scheme;
        	$url = $scheme.'://'. I('server.SERVER_NAME') . C('APP_INDEX_PAGE');
        	$ticket='';
			$employee_id = -1;
			if(!empty($data->gateway))
        	{
				if (empty($data->ticket)){
					$client_ip = get_client_ip();
					D('Setting/Employee')->login($username, $password, $sid, $client_ip, $ticket, $employee_id);
					$log = array('type'=>1, 'operator_id'=>get_operator_id(), 'data'=>$client_ip, 'message'=>'用户登录');
        			M('sys_other_log')->add($log);
        			$r->info->url = $url . '/Home/Login/access';
					$r->info->ticket = $ticket;
				} else{
					$employee_id = D('Setting/Employee')->sso($username, $data->ticket);
					if (!empty($employee_id)){
						$url = U('Home/Login/check').'?user_num_id='.$employee_id;
						session('user_id_time',json_encode(array('user_num_id'=>$employee_id,'time'=>date("Y-m-d H:i:s",time()))));
					}
					js_redirect($url);
				}
        	}else{
        		$client_ip = get_client_ip();
				$is_login=D('Setting/Employee')->login($username, $password, $sid, $client_ip, $ticket, $employee_id);
				if($is_login===true)
        		{
        			$log = array('type'=>1, 'operator_id'=>get_operator_id(), 'data'=>$client_ip, 'message'=>'用户登录');
        			M('sys_other_log')->add($log);
        			$r->info = $url;
        		}else 
        		{
        			$r->info = U('Home/Login/check').'?user_num_id='.$is_login;
        			session('user_id_time',json_encode(array('user_num_id'=>$is_login,'time'=>date("Y-m-d H:i:s",time()))));
        		}
        	}
		} catch (\Exception $e) {
			$status = 1;
			$r->info = $e->getMessage();
            \Think\Log::write('access:'.$r->info,\Think\Log::WARN,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
			if (!empty($data->ticket)){
				echo '未知错误,请联系管理员！';
				exit();
			}
        }
        $r->status = $status;
		header("Access-Control-Allow-Origin: *");
		$login_manager = ManagerFactory::getManager('Login');
		if ($is_login===true && 0 == $r->status){
			$_SESSION['user_name']=$username;
			$login_manager->log(true, '');
			try{
				session("account", $username);
				session("operator_id", $employee_id);
				$url = $login_manager->computeRisk($employee_id);
				if (!empty($url)){
					$status = 2;
					$r->info = $url;
				}
			} catch(\Exception $e) {
				$status=1;
				$r->info = $e->getMessage();
				\Think\Log::write('computeRisk:'.$r->info,\Think\Log::WARN,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
				session("account", null);
				session("operator_id", null);
			}
		} else {
			try {
				$login_manager->log(false, $r->info);
			} catch (\Exception $e) {
				$status=1;
				$r->info=$e->getMessage();
				\Think\Log::write('login_manager-log:'.$r->info,\Think\Log::WARN,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
			}
		}
		$r->status = $status;
		try{
			$account_valid_time = get_config_value('account_valid_time',0);
			if($account_valid_time != 0){
				$now = strtotime(date('Y-m-d'));
				$valid_time = strtotime($account_valid_time);
				$valid_day = floor(($valid_time - $now) / 86400);
				if($valid_day >= 0 && $valid_day < 30){
					$r->valid_day = $valid_day;
				}
			}
		} catch (\Exception $e) {
			\Think\Log::write('get valid days:'.$e->getMessage(),\Think\Log::WARN,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
		}
		$this->ajaxReturn($r);
    }

	public function verify(){
		try {
			$login_manager = ManagerFactory::getManager('Login');
			$token = I('get.token');
			$login_manager->verifyLogin($token);
		} catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg,\Think\Log::WARN);
			session("account", null);
			session("operator_id", null);
			js_redirect(U('Home/Login/login'));
        }
		js_redirect(C('APP_INDEX_PAGE'));
	}
	
    public function api()
    {
        $action=I('post.action');
        $sid=I('post.sid');
        $key=I('post.key');
        $info=I('post.info','',C('JSON_FILTER'));
        try 
        {
            //安全校验
            $ip=get_client_ip();
            if ($ip!='127.0.0.1') 
            {//非法IP
                $this->ajaxReturn(array('status'=>1,'message'=>'非法IP'));
            }
            $timestamp=intval(decrypt($key,md5($sid.'!@#$')))+1000;
            if (time()-$timestamp>180) 
            {//请求时间超过3分钟，终止请求
                $this->ajaxReturn(array('status'=>1,'message'=>'请求超时'));
            }
            //接口模拟登录
            session('account',  $sid);
            session('sid', $sid);
            session('operator_id', 0);
            //调用真正接口
            R($action,array($info));
        }catch (\Exception $e)
        {
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
        }
        //接口模拟退出
        session('account',null);
        session('sid', null);
        session('operator_id', null);
        exit();
    }

    //重置密码
    public function reset(){
        if(!empty(session('account')) || !empty(session('operator_id'))){
            session("sid", null);
            session("account", null);
            session("operator_id", null);
            //js_redirect(C('APP_INDEX_PAGE'));
        }
        //安全验证session是否存在，存在则清空session
        $code=session('json_mobile_code');
        if(!empty($code)){
            session("json_mobile_code", null);
        }
        $time=session('user_id_time');
        if(!empty($time)){
            session("user_id_time", null);
        }
        $log_update_time=filemtime(APP_PATH."Help/Controller/update.txt"); //获取日志文件的更新时间（时间戳类型）
        $this->assign('version_number', $log_update_time);//js、css版本号
        $this->display('reset');
    }
	public function reset_pwd(){
		//安全验证
		$is_reset=session('reset_pwd');
		if(empty($is_reset)){
			js_redirect(U('Home/Login/reset'));die;
		}else{
			session('reset_pwd',null);
		}
		$employee_id = I('get.id');
		$this->assign('employee_id',$employee_id);
		$this->display('reset_pwd');
	}
	public function reset_pwd_success(){
		//安全验证
		$is_reset=session('reset_pwd_success');
		if(empty($is_reset)){
			js_redirect(U('Home/Login/reset'));die;
		}else{
			session('reset_pwd_success',null);
		}
		$employee_id = I('get.id');
		$this->assign('employee_id',$employee_id);
		$this->display('reset_pwd_success');
	}
	//重置密码
	public function changePassword(){
		$data=I('post.data','',C('JSON_FILTER'));
		$password = md5($data['password']);
		$employee_id = trim_all($data['id'],1);
		try{
			$employee_db=D('Setting/Employee');
			$employee_db->changePassword($password,$employee_id);
			//修改信息
			$arr_employee=array(
					'sso' =>array('timestamp'=>time(),'sid'=>get_sid()),
					'last_login_ip'=>  get_client_ip()
			);
			$employee_db->updateEmployee($arr_employee,array('employee_id'=>array('eq',$employee_id)));
			$log = array('type'=>1, 'operator_id'=>$employee_id, 'data'=>get_client_ip(), 'message'=>'用户重置密码成功');
			M('sys_other_log')->add($log);
			//$result['info']=C('APP_INDEX_PAGE');
			$result['status']=0;
			$result['info']=U("Login/reset_pwd_success").'?id='.$employee_id;
			session('reset_pwd_success',1);
		}catch (\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write('changePassword:'.$msg,\Think\Log::WARN);
			$result['status']=1;//失败
			$result['info']=$msg;
		}
		$this->ajaxReturn($result);
	}
	//登陆跳转
	public function loginJump(){
		$data=I('post.data','',C('JSON_FILTER'));
		$employee_id = trim_all($data['id'],1);
		header("Access-Control-Allow-Origin: *");
        $login_manager = ManagerFactory::getManager('Login');
            $login_manager->log(true, '');
            try{
				$employee_db=D('Setting/Employee');
				$employee=$employee_db->getEmployee('employee_id,account',array('employee_id'=>array('eq',$employee_id)));
                session('account', $employee['account']);
                session('operator_id', $employee['employee_id']);
                $url = $login_manager->computeRisk($employee['employee_id']);
                if (!empty($url)){
                    $result['status']=2;
                    $result['info'] = $url;
                }
                session("json_mobile_code", null);
                session("user_id_time", null);
				$result['info']=C('APP_INDEX_PAGE');
				$result['status']=0;
            } catch(\Exception $e) {
                $msg = $e->getMessage();
                $result['status']=1;
                $result['info'] = $msg;
                \Think\Log::write('loginJump:'.$msg);
                session('account', null);
                session('operator_id', null);
            }
		$this->ajaxReturn($result);
	}
    //卖家信息验证
    public function checkResetInfo($data){
        //数据处理
		$data=I('post.data','',C('JSON_FILTER'));
        $account = trim_all($data['account'],1);
        $mobile = trim_all($data['mobile_no'],1);
        $sid = trim_all($data['sid'],1);
        //$password = md5($data['password']);
        $code = trim_all($data['code'],1);
        $result=array('status'=>0,'info'=>'');
        session("sid", $sid);
        $r = new \stdClass();
		try{
            if($account===''||!check_regex('english_num',$account)){ SE('非法用户名'); }
            if($sid==='' || !check_regex('english_num',$sid)){ SE('非法卖家账号！'); }
            if($code==='' || !check_regex('zip',$code)){ SE('验证码格式错误！'); }
            $employee_db=D('Setting/Employee');
            $employee=$employee_db->getEmployee('employee_id,account',array('account'=>array('eq',$account)));
            if(empty($employee['employee_id'])||empty($employee)){
                SE('验证失败，当前用户不存在或已删除');
            }
			//-----验证校验码失效-----
            $json_mobile_code=json_decode(session('json_mobile_code'),true);
            if(strtotime($json_mobile_code['send_time'])+intval(C('MOBILE_CODE_INVALID_TIME'))<time() || empty($json_mobile_code)){
                session("json_mobile_code", null);
                SE('验证码已过期，请重新获取');
            }
			//-----验证次数不能大于5次-------
            $json_mobile_code['times']=intval($json_mobile_code['times']);
            if(intval($json_mobile_code['times'])<5){
                $json_mobile_code['times']+=1;
                session('json_mobile_code',json_encode($json_mobile_code));
            }else{
                SE('验证太频繁，请稍后再试...');
            }
			if($code==$json_mobile_code['mobile_code']){
				session('reset_pwd',1);
				$result['status']=0;
				$result['info']=U("Login/reset_pwd").'?id='.$employee['employee_id'];
            }else{
                SE('验证失败');
            }
        }catch (\Exception $e){
            $msg=$e->getMessage();
            \Think\Log::write('reset-checkCode:'.$msg,\Think\Log::WARN);
            $result['status']=1;//失败
            $result['info']=$msg;
        }
        //登录跳转
//        header("Access-Control-Allow-Origin: *");
//        $login_manager = ManagerFactory::getManager('Login');
//        if ($result['status'] == 0){
//            $login_manager->log(true, '');
//            try{
//                session('account', $employee['account']);
//                session('operator_id', $employee['employee_id']);
//                $url = $login_manager->computeRisk($employee['employee_id']);
//                if (!empty($url)){
//                    $result['status']=2;
//                    $result['info'] = $url;
//                }
//                session("json_mobile_code", null);
//                session("user_id_time", null);
//            } catch(\Exception $e) {
//                $msg = $e->getMessage();
//                $result['status']=1;
//                $result['info'] = $msg;
//                \Think\Log::write('computeRisk:'.$msg);
//                session('account', null);
//                session('operator_id', null);
//            }
//        } else {
//            try {
//                $login_manager->log(false, $result['info']);
//            } catch (\Exception $e) {
//                $result['status']=1;
//                $result['info']=$e->getMessage();
//                \Think\Log::write('login_manager-log:'.$result['info'],\Think\Log::WARN);
//            }
//        }
        $this->ajaxReturn($result);
    }
    //发送重置验证码
    public function sendResetCode(){
        $result=array('status'=>0,'info'=>'');
        $data=I('post.data','',C('JSON_FILTER'));
        $account = trim_all($data['account'],1);
        $mobile = trim_all($data['mobile_no'],1);
        $sid = trim_all($data['sid'],1);
        session("sid", $sid);
		$r = new \stdClass();
        try {
            //验证输入信息
            if($account==='' || !check_regex('english_num',$account)){SE('非法用户名！');}
            if($sid==='' || !check_regex('english_num',$sid)){ SE('非法卖家账号！'); }
            if($mobile==='' || !check_regex('mobile',$mobile)){ SE('手机号码格式不正确'); }
            $employee_db=D('Setting/Employee');
            $employee=$employee_db->getEmployee('employee_id,mobile_no',array('account'=>array('eq',$account)));
            if(empty($employee['employee_id'])||empty($employee)){
                SE('当前用户不存在或已删除');
            }
            if($employee['mobile_no']==$mobile){
                //发送短信
                $mobile.=',';
                $mobile_code = substr(microtime(), 2, 6);
                //$result['code'] = $mobile_code;
                $message='【E快帮】正在进行账户密码重置，验证码：'.$mobile_code.'。请勿向他人泄露验证码。';
                $res=UtilTool::SMS($mobile, $message,'checkcode',$sid);
				/*$res=array(
					'code'=>$mobile_code,
					'info'=>'验证码发送成功，注意查收！',
					'status'=>0,
				);*/
                if(!is_array($res)){
                    $result['status'] =1;
                    $result['info'] = '未知错误,请联系管理员';
                    $this->ajaxReturn($result);
                }
                //发送结果处理
                if($res['status']==0){
                    $json_mobile_code=array('mobile_code'=>$mobile_code,'send_time'=>date("Y-m-d H:i:s",time()));
                    $json_mobile_code['mobile']=$mobile;
                    $json_mobile_code['times']=0;
                    session('json_mobile_code',json_encode($json_mobile_code));
                    //记录验证码日志
                    \Think\Log::write('reset-mobile:'.$mobile.' code:'.$json_mobile_code['mobile_code'],'INFO');
                    $result['status']=0;
                    $result['info']='验证码发送成功，注意查收！';
                }elseif($res['info']=='您的账户还没有充值,请先充值'){
                    $result['status']=1;
                    $result['info']='短信余额不足，请先联系管理员进行充值';
                }elseif($res['info']=='未查询到用户信息'){
                    $result['status']=1;
                    $result['info']='您的账户还没有充值,请先充值';
                }elseif($res['status']==15){
                    $result['status']=1;
                    $result['info']='短信账号未开,请联系管理员';
                }else{
                    $result['status']=1;
                    $result['info']='验证码发送失败，请稍后再试';
                }
            }else{
                SE('非绑定手机号，不能重置！');
            }
        } catch (\Exception $e) {
            $msg=$e->getMessage();
            \Think\Log::write('reset-sendCode:'.$msg,\Think\Log::WARN);
            $result['status']=1;//失败
            $result['info']=$msg;
        }
        $this->ajaxReturn($result);
    }
}