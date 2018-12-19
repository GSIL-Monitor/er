<?php 
namespace Setting\Controller;
use Common\Controller\BaseController;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class AccountManagementController extends BaseController{
	
	public function show(){
		try{
			$user_id = get_operator_id();
			$data = D('Employee')->loadSelectedData($user_id);
			$form = $data['data'];
			$this->assign('user_id', $user_id);
			$this->assign('form',json_encode($form));
			$this->assign('mobile',$form['mobile_no']);
			$this->display('show');
		}catch(\PDOException $e){
	        \Think\Log::write($e->getMessage());
		}	
	}
	public function saveEmployee(){
		try{
			$result = array('status'=>0,'info'=>'修改成功');
			$data = I('post.');
			if($data['code'] != ''){
				$this->checkCode($data['code']);
			}
			$arr['account']     = $data['account'];
    		$arr['fullname']    = $data['fullname'];
    		$arr['gender']      = $data['gender'];
    		$arr['position']    = $data['position'];
    		$arr['mobile_no']   = $data['mobile_no'];
    		$arr['email']       = $data['email'];
    		$arr['qq']          = $data['qq'];
    		$arr['wangwang']    = $data['wangwang'];
    		$arr['employee_id'] = $data['id'];
			$arr['field_rights']= set_default_value($data['field_rights'], 0);
			$employee=D('Employee')->getEmployee('password,salt',array('employee_id'=>array('eq',$data['id'])));
			if($data['password'] != ''){
				$password=D('Employee')->encrypt($data['password'].$employee['salt']);
				$arr['password'] = $password;
			}
			$res = D('Setting/Employee')->saveAccountmanagement($arr);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result =  array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function checkChangeMobile(){
		$user_id = get_operator_id();
		$new_mobile = I('get.mobile');
		if($new_mobile != ''){
			$data['data']['mobile_no'] = $new_mobile;
		}else{
			$data = D('Employee')->loadSelectedData($user_id);
		}
		$this->assign('mobile',$data['data']['mobile_no']);
		$this->display('check_change_mobile');
	}
	public function sendCodeByMobile(){
		$mobile=I('post.mobile');
		$result['status']=0;
		$result['info']='';
		$message = '您正在修改绑定的手机号码';
		try {
			if(!check_regex('mobile',$mobile))
			{
				SE('手机号码格式不正确');
			}
			$key = 'json_mobile_code_tel';
			$mobile.=',';
			$mobile_code = substr(microtime(), 2, 6);
			$message='【E快帮】'.$message.'，验证码：'.$mobile_code.'。请勿向他人泄露验证码。';
			$res=UtilTool::SMS($mobile, $message,'checkcode');
			if($res->status==0)
			{
				$json_mobile_code=array('mobile'=>$mobile,'mobile_code'=>$mobile_code,'times'=>0,'send_time'=>date("Y-m-d H:i:s",time()));
				session($key,json_encode($json_mobile_code));
				//记录验证码日志
				\Think\Log::write('account_mobile:'.$mobile.' code:'.$json_mobile_code['mobile_code'],\Think\Log::WARN);
				$result['status']=0;
				$result['info']='验证码发送成功，注意查收！';
			}else
			{
				$result['status']=1;
				$result['info']='验证码发送失败，请稍后再试';
			}
		} catch (\Exception $e) {
			$msg=$e->getMessage();
			\Think\Log::write('Account_sendCode:'.$msg,\Think\Log::WARN);
			$result['status']=1;//失败
			$result['info']=$msg;
		}
		$this->ajaxReturn($result);
	}
	private function checkCode($code)
	{
		$key = 'json_mobile_code_tel';
		$json_mobile_code=session($key);
		if(empty($json_mobile_code))
		{
			SE('验证码已过期，请重新获取');
		}
		$json_mobile_code=json_decode(session($key));
		if(strtotime($json_mobile_code->send_time)+intval(C('MOBILE_CODE_INVALID_TIME'))<time())
		{
			session($key, null);
			SE('验证码已过期，请重新获取');
		}
		$json_mobile_code->times=intval($json_mobile_code->times);
		if(intval($json_mobile_code->times)<5)
		{
			$json_mobile_code->times+=1;
			session($key,json_encode($json_mobile_code));
		}else
		{
			SE('验证太频繁，请稍后再试...');
		}
		if(!check_regex('zip',$code) || $code!=$json_mobile_code->mobile_code)
		{
			SE('验证码不正确');
		}
	}
}