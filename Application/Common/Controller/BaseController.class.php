<?php
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller{
	const UNKNOWN_ERROR = '未知错误,请联系管理员';
	const ALLOW_EXPORT = true; //设置是否允许导入和导出操作
	const EXPORT_MSG = '双十二期间不允许导入和导出操作，敬请谅解！';

	protected $id_list = array();

	/**
	 * 控制器初始化
	 */
	function _initialize()
	{
 		self::check_login();
 		self::check_priv();
	}
	
	/**
	 * 验证是否登录
	 */
	final public function check_login()
	{
		/* if(CONTROLLER_NAME =='Login' && in_array(ACTION_NAME, array('login', 'access', 'check', 'sendCode', 'checkCode')) ) {
			return true;
		} */
		try{
			if(!is_login())
			{
				$url = U('Home/Login/login');
				if(IS_AJAX && IS_GET)
				{
					exit('<div style="padding:6px">请先<a href="'.$url.'">登录</a>E快帮系统</div>');
				}else if(IS_AJAX && IS_POST)
				{
					$data = array('info'=>'请先<a href="'.$url.'">登录</a>E快帮系统', 'status'=>0, 'total'=>0, 'rows'=>array());
					$this->ajaxReturn($data);
				}else
				{
					js_redirect($url);
				}
			}
		}catch (\Exception $e) 
		{
            $msg = $e->getMessage();
            \Think\Log::write($msg);
			$this->display('Home@Login:login');
        }
	}
	
	/**
	 * 权限判断
	 */
	final public function check_priv()
	{
		//过滤不需要权限控制的页面
		switch (strtolower(CONTROLLER_NAME))
		{
			case 'login':
				return true;
				break;
			case 'index':
				switch (strtolower(ACTION_NAME)){
					case 'index':
					case 'exception':
					case 'getnotification':
					case 'getnotificationlist':
					case 'shownotificationlist':
					case 'showelectronicsheetdetial':
					case 'check_log':
					case 'showupdatelog':
					case 'showsystemalarm':
					case 'reloaddata':
					case 'getnewcomer':
					case 'getbrowserandresolution':
						return true;
						break;
				}
				break;
			case 'processmenu':
				switch (strtolower(ACTION_NAME)){
					case 'getprocessmenu':
						return true;
						break;
				}
				break;
			case 'datagridfield':
				switch (strtolower(ACTION_NAME)){
					case 'getfield':
					case 'setfield':
						return true;
						break;
				}
				break;
			case 'tradeprocess':
				return true;
				break;
			case 'statisticscommon':
				return true;
				break;
			case 'accountcommon':
				return true;
				break;
			case 'stockmanagement':
				$operator_id = get_operator_id();
				$right = M('dict_url')->alias('du')->fetchSql(false)->field('du.action')->join('left join cfg_employee_rights cef on du.url_id = cef.right_id ')->where(array('cef.is_denied'=>0,'cef.employee_id'=>$operator_id,'du.parent_id'=>40))->select();
				if(empty($right)){
					$employee_data = D('Setting/EmployeeRights')->field('rec_id')->where(array('right_id'=>40,'employee_id'=>$operator_id,'is_denied'=>0))->select();
					if(!empty($employee_data)){
						return true;
						break;
					}
				}
				
			/* case 'goodsspec':
				switch (strtolower(ACTION_NAME)){
					case 'selectgoodsspec':
						return true;
						break;
				}
				break;
			case 'goodssuite':
				switch (strtolower(ACTION_NAME)){
					case 'getdialoggoodssuitelist':
						return true;
						break;
				}
				break;
			case 'customerfile':
				switch (strtolower(ACTION_NAME)){
					case 'getcustomerlistdialog':
						return true;
						break;
				}
				break;
			case 'settingcommon':
				return true;
				break;
			case 'goodscommon':
				return true;
				break;
			case 'tradecommon':
				return true;
				break;
			case 'stockcommon':
				return true;
				break;
			case 'flag':
				return true;
				break;
			case 'cfgoperreason':
				return true;
				break; */
		}
		$is_priv=D('Setting/EmployeeRights')->checkPrivileges(MODULE_NAME,CONTROLLER_NAME,ACTION_NAME,get_operator_id());
		
		
			if(!$is_priv)
			{
				if(IS_AJAX && IS_GET)
				{
					exit('<div style="padding:6px">您没有权限操作该项</div>');
				}elseif(IS_POST){
					$this->ajaxReturn(array('status'=>1,'msg'=>'您没有权限操作该项'));
				}
				else 
				{
					$this->error('您没有权限操作该项');
				}
			}
			
		//统计客户操作
		if(IS_POST){
			$params=$_POST;
			D('Statistics/StatUse')->statUse(MODULE_NAME,CONTROLLER_NAME,ACTION_NAME,$params,get_operator_id());
		}
			
	}
	
	/**
	 * 空操作，用于输出404页面
	 */
	public function _empty()
	{
		
		if(!IS_AJAX) send_http_status(404);//ajax特殊处理
		if (IS_AJAX && IS_POST)
		{
			$data = array('info'=>'请求地址不存在或已经删除', 'status'=>0, 'total'=>0, 'rows'=>array());
			$this->ajaxReturn($data);
		}else{
			$this->display('Common@Exception:404');
		}
	}


	/**
	 * @param        $id_list
	 * @param array  $key_list
	 * @param string $prefix
	 * @param string $suffix
	 * @return array
	 */
	static public function getIDList(&$id_list, $key_list = array("form", "toolbar", "datagrid"), $prefix = "", $suffix = "") {
		if (!is_array($id_list)) $id_list = array();
		$prefix  = $prefix != "" ? $prefix . "_" : $prefix;
		$suffix  = $suffix != "" ? "_" . $suffix : $suffix;
		$context = strtolower(CONTROLLER_NAME) . "_";
		foreach ($key_list as $v) {
			$id_list[$v] = $prefix . $context . $v . $suffix;
		}
		return $id_list;
	}

}

?>