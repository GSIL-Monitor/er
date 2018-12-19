<?php

namespace Platform\Manager;

use Setting\Model\ShopModel;
use Setting\Model\EmployeeModel;

class LoginManager {

    static public function log($success, $msg) {
        global $g_jst_hch_enable;
		if (empty($g_jst_hch_enable)){
			return;
		}

		$params = array();
		$where = array();
		$where['platform_id'] = 1;
		$where['is_disabled'] = 0;
		$where['auth_state'] = 1;
		$nick_list = D('Setting/Shop')->getShopInfo($where, 'account_nick');
		//\Think\Log::write('shop:'.print_r($nick_list, true));
		if ($nick_list['status'] != 0){
			$params['tid'] = get_user_id();
		} else {
			$c = count($nick_list['info']);
			$params['tid'] = '';
			for ($i = 0; $i < $c; ++$i){
				if ($i != $c -1){
					$params['tid'] .= $nick_list['info'][$i]['account_nick'] . ",";
				} else {
					$params['tid'] .= $nick_list['info'][$i]['account_nick'];
				}
			}
		}

		$params['loginResult'] = !empty($success) ? 'success' : 'fail';
		$params['loginMessage'] = $msg;
		if(!stristr(PHP_OS, 'WIN')){
			hchRequest('http://account.ose.aliyun.com/login', $params);
		}
    }
	
	static public function computeRisk($employee_id){
		global $g_jst_hch_enable;
		if (empty($g_jst_hch_enable)){
			return '';
		}
		$params = array();
		$result = hchRequest('http://account.ose.aliyun.com/computeRisk', $params);
		
		if (empty($result->risk) || $result->risk <= 0.5){
			return '';
		}
		
		\Think\Log::write('computeRisk:'.print_r($result, true));
		$employee=D('Setting/Employee')->getEmployee('mobile_no',array('employee_id'=>$employee_id));
		$params = array();
		$params['sessionId'] = microtime(true) . get_user_id();
		$params['mobile'] = $employee['mobile_no'];
		$params['redirectURL'] = urlencode('http://ekb.wangdian.cn/index.php/Home/Login/verify');
		$result = hchRequest('http://account.ose.aliyun.com/getVerifyUrl', $params);
		
		if (empty($result->result) || $result->result != 'success'){
			E($result->errMsg);
		} else {
			//js_redirect($result->verifyUrl);
			return $result->verifyUrl;
		}
		
		
	}
	
	static public function verifyLogin($token){
		global $g_jst_hch_enable;
		if (empty($g_jst_hch_enable)){
			return true;
		}
		$params['token'] = $token;
		$result = hchRequest('http://account.ose.aliyun.com/isVerifyPassed', $params);
		
		if (empty($result->result) || $result->result != 'success' || empty($result->verifyResult) || $result->verifyResult != 'success'){
			E($result->errMsg);
		} else {
			return true;
		}

	}

}

?>