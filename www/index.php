<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',false);

// 定义应用目录
define('APP_PATH','../Application/');

//require '../ThinkPHP/ThinkPHP.php';

// 引入ThinkPHP入口文件
try{
	require '../ThinkPHP/ThinkPHP.php';
}catch (\Exception $e){
	$sid=get_sid();
	$sid=empty($sid)?'':',SID:'.$sid;
	\Think\Log::write('IP:'.get_client_ip().$sid.'=>'.$e->getMessage(),\Think\Log::ERR,'',C('SYSTEM_LOGS_PATH'). date('y_m_d') . '.log');
	echo C('ERROR_PAGE');
	exit;
}

// 亲^_^ 后面不需要任何代码了 就是如此简单