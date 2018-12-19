<?php
    function get_db_connection() {
		$sid = get_sid();
		if ($sid == 'default'){
			SE("无效的卖家账号！");
		}
		// get db connection from cache
		static $cache = '';
		if (empty($cache)){
			$cache = new Memcached();
			$cache->addServer('10.132.180.236', 30001);
		} 
		$merchant = $cache->get($sid);
		if (!is_array($merchant)){
			// not cached or cache crashed!
			$params['sid'] = $sid;
			$max_tries = 2;
			for ($i = 0; $i < $max_tries; ++$i){
				$data = get_service_data('conn', $params);
				if (!is_object($data)){
					\Think\Log::write("get_db_connection get error data:". print_r($data, true).", sid :".$sid,\Think\Log::ERR,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
					continue;
				} else if (1== $data->status){
					\Think\Log::write("get_db_connection get error data:". print_r($data, true).", sid :".$sid,\Think\Log::WARN,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
					SE("无效的卖家账号！");
				} else if (2 == $data->status){
					\Think\Log::write("get_db_connection get error data:". print_r($data, true).", sid :".$sid,\Think\Log::ERR,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
					SE("无效请求,请联系管理员！");
				} 
				$merchant = (array)$data->info;
				$cache->set($sid, $merchant);
				break;
			}
		} 
		
		if (!is_array($merchant)){
			//service crashed!
			\Think\Log::write("get_db_connection get error data:". print_r($data, true).", sid :".$sid,\Think\Log::ERR,'', C('SYSTEM_LOGS_PATH') . date('y_m_d') . '.log');
			SE("无法连接数据库,请联系管理员！");
		}
		
		$merchant['db_pwd'] = decrypt($merchant['db_pwd'], $merchant['secret']);
		return 'mysql://'.$merchant['db_user'].':'.$merchant['db_pwd'].'@'.$merchant['db_host'].':3306/'.$merchant['db_name'];
	}
	function get_service_data($action, $params){
		$url = 'http://10.132.180.236/index.php?action=' . $action . '&';
		foreach ($params as $key => $val){
			$url .= $key . '=' . urlencode($val) . '&';
		}
		$url = substr($url, 0, -1);
		return json_decode(file_get_contents($url));
	}
	function get_internet_ip(){
		static $ip = '';
		if (empty($ip)){
			$ip = exec("/sbin/ip addr show eth1|grep 'inet '|awk '{print $2}'|cut -d/ -f1");
		}
		
		return $ip;
	}
	function get_intranet_ip(){
		static $ip = '';
		if (empty($ip)){
			$ip = exec("/sbin/ip addr show eth0|grep 'inet '|awk '{print $2}'|cut -d/ -f1");
		}
		
		return $ip;
	}
	function is_login()
	{
		$account = session('account');
		if (empty($account)){
			\Think\Log::write('is-login:session account empty!', 'WARN');
			return false;
		}else{
			return true;
		}	
	}
	function decrypt($data, $key='')
	{
		if(empty($key)){
			$key = '123456';
		}
		$decode = base64_decode($data);
		return rc4($key, $decode);
	}
	function rc4($key, $str) {
		$s = array();
		for ($i = 0; $i < 256; $i++) {
			$s[$i] = $i;
		}
		$j = 0;
		for ($i = 0; $i < 256; $i++) {
			$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
		}
		$i = 0;
		$j = 0;
		$res = '';
		for ($y = 0; $y < strlen($str); $y++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
			$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
		}
		return $res;
	}
	function js_redirect($url)
	{
		header("Content-Type: text/html; charset=utf-8");
		die("<script>window.top.location = '{$url}'; </script>");
	}
	function get_sid(){
		$sid = session('sid');
		return empty($sid) ? 'default' : $sid;
	}
	function get_operator_id(){
		return intval(I('session.operator_id'));
	}

    function get_log_path($sub_module_path = '') {
        $sid = get_sid();
        if (!empty($sub_module_path)) {
            $sub_module_path = $sub_module_path . DIRECTORY_SEPARATOR;
        }
        return RUNTIME_PATH . 'Logs' . DIRECTORY_SEPARATOR . $sid . DIRECTORY_SEPARATOR . MODULE_NAME . DIRECTORY_SEPARATOR . $sub_module_path . date('y_m_d') . '.log';
    }
	
	/**
	 * 过滤json中的html
	 * @param string $json
	 * @return Ambigous <unknown, mixed>
	 */
	function is_json($json) {
		$arr=json_decode($json,true);
		return (json_last_error() == JSON_ERROR_NONE) ? $arr : $json;
	}
	/**
	 * 转义json
	 * @param unknown $val
	 * @return string
	 */
	function html_filter($val){
		return htmlspecialchars($val,ENT_QUOTES);
	}

	/**
	 * @param string $name
	 * @param string $key
	 * @param string $layer
	 * @return array|object
	 */
	function get_field($name,$key='',$layer='')
	{
		$layer = $layer? : C('DEFAULT_F_LAYER');
		$k = $name.$layer;
		$field = \Common\Common\Register::get($k);
		$arr_fields_data=array();
		if (!$field)
		{
			$class = parse_res_name($name,$layer);
			if(class_exists($class)) {
				$class='\\'.$class;
				$field=new $class();
				\Common\Common\Register::set($k, $field);
				
			}else{
				$field=new \Common\Common\Field();
				\Think\Log::write('get_field时没找到字段类'.$class);
			}
		}
		if(!empty($key))
		{
			$arr_fields_data=$field->getFields($key);
			return $arr_fields_data;
		}else{
			return $field;
		}
	}

	/**
	 * 去除空格
	 * @param string $str
	 * @param number $type
	 * @return string
	 */
	function trim_all($str,$type=0)
	{
		switch ($type)
		{
			case 0://去除全部空格
				$str=str_replace(array(" ","　","\t","\n","\r"),array("","","","",""),$str);
				break;
			case 1://去除连续空格
				$str=preg_replace('#\s+#', ' ',trim($str));
				break;
			default:
				$str=trim($str);
				break;
		}
		return $str;
	}


	/**
	 * 对用户的密码进行加密
	 * @param string $password
	 * @param string $encrypt 传入加密串，在修改密码时做认证
	 * @return array/password
	 */
	function password($password, $encrypt='') {
		$pwd = array();
		$pwd['encrypt'] =  $encrypt ? $encrypt : Org\Util\String::randString(6);
		$pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
		return $encrypt ? $pwd['password'] : $pwd;
	}

    /**
     * 设置默认值
     * @param string $value
     * @param string|integer $def_value
     * @return string|integer
     */
    function set_default_value($value, $def_value) {
        return empty($value)&&($value!==0)&&($value!=='0') ? $def_value : $value;
    }
	
	/**
	 * 获取配置值-sys_seting
	 * @param object $db
	 * @param array|string $key
	 * @param array|string|number $def_value
	 * @return array|string|number
	 */
	function get_config_value($key,$def_value=0)
	{
		$sys_set_db=M('cfg_setting');
		if (is_array($key))
		{
			$arr_cfg_val=array();
			for ($i=0;$i<count($key);$i++)
			{
				if(is_array($def_value))
				{
					$arr_cfg_val[$key[$i]]=$def_value[$i];
				}else 
				{
					$arr_cfg_val[$key[$i]]=$def_value;
				}
			}
			$where['key']=array('in',$key);
			$sql=$sys_set_db->fetchSql(true)->field('`value`,`key`')->where($where)->select();
			$sql.=' LOCK IN SHARE MODE';
			$res_val_arr=$sys_set_db->query($sql);
			foreach ($res_val_arr as $v)
			{
				$arr_cfg_val[$v['key']]=(($v['value'] === null || $v['value'] == '')?$arr_cfg_val[$v['key']]:$v['value']);
			}
			return $arr_cfg_val;
		}else 
		{
			$res_val=$sys_set_db->query("SELECT `value` FROM cfg_setting WHERE `key`='%s' LOCK IN SHARE MODE",array($key));
			if(empty($res_val)||empty($res_val[0])||(empty($res_val[0]['value'])&&$res_val[0]['value']!==0&&$res_val[0]['value']!='0'))
			{
				$res_val=$def_value;
			}else 
			{
				$res_val=$res_val[0]['value'];
			}
			return $res_val;
		}
	}

	/**
	 * 获取系统生成的编号
	 * @param string $name
	 * @param number $type
	 * @return multitype:string
	 */
	function get_sys_no($name,$type=0)
	{
		if($type==0)
		{
			$res_sys_no=M()->query("SELECT FN_SYS_NO('".$name."') AS sys_no");
			if (empty($res_sys_no))
			{
				$result='';
				\Think\Log::write('get_sys_no->empty');
			}else{
				$result=$res_sys_no[0]['sys_no'];
			}
		}else{
			$result=array('exp',"FN_SYS_NO('".$name."')");
		}
		return $result;
	}

    /**
     * 获得系统生成自增长的编号
     * @param $name
     * @return mixed
     */
    function get_seq($name) {
        try {
            $result = M()->query("SELECT FN_SEQ('" . $name . "') AS seq_no");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return $result[0]["seq_no"];
    }

	/**
	 * 多表连接查询
	 * @param string $sql_where
	 * @param string $where_str
	 * @param boolean $flag
	 * @param number $length
	 */
	function connect_where_str(&$sql_where,$where_str,&$flag,$length=5)
	{
		if(!empty($where_str))
		{
			if(!$flag)
			{
				$sql_where.=' WHERE '.substr($where_str, $length);
				$flag=true;
			}else
			{
				$sql_where.=$where_str;
			}
		}
	}

    /**
     * 正则验证方法集合
     * @param string $value
     * @param string $rule
     * @return boolean
     */
    function check_regex($rule,$value=null)
	{
		$validate = array(
				'require'   =>  '/\S+/',
				'number'    =>  '/^[-+]?[0-9]*$/',
		        'positive_number'  => '/^[0-9]*\.?[0-9]*$/',
				'date'      =>  '/^\d{4}(-\d{2}){2}$/',
				'time'      =>  '/^(1|2\d{3}-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3([0|1]))))( (\d{2}):(\d{2}):(\d{2}))?$/',
				'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
				'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
				'currency'  =>  '/^\d+(\.\d+)?$/',//货币
				'integer'   =>  '/^[-\+]?\d+$/',
				'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
				'english'   =>  '/^[A-Za-z]+$/',
				'mobile'    =>  '/^[\d]{11}$/',
				'telno'     =>  '/[\d]{3}-[\d]{8}|[\d]{4}-[\d]{7,8}/',
				'mobile_tel'=>  '/(^([0\+]\d{2,3})\d{3,4}\-\d{3,8}$)|(^([0\+]\d{2,3})\d{3,4}\d{3,8}$)|(^([0\+]\d{2,3}){0,1}13\d{9}$)|(^\d{3,4}\d{3,8}$)|(^\d{3,4}\-\d{3,8}$)/',//手机和电话
				'zip'       =>  '/^[0-9]\d{5}$/',
				'num_let'   =>  '/^[A-Za-z0-9]+$/',//数字字母
				'char'      =>  '/^[\w]+$/',
		        'month'     =>  '/^\d{4}(-\d{2}){1}$/',
		        'english_chinese'=>  '/^[\x{4e00}-\x{9fa5}A-Za-z\-\d_]+$/u',
				'english_num'   =>'/^[a-zA-Z0-9_]{1,}$/',
                'specialcharacter'  => '/[\\\`~!@#\$%\^\&\*\(\)\+<>\?:"\{\},\.\/\;\'\[\]]/im',
                'specialexceptbracket'  => '/[\\\`~!@#\$%\^\&\*\+<>\?:"\{\},\.\/\;\'\[\]]/im',
				'check_merchant_no' => '/[!@#\$%\&\*:"\\/\']/im'
		);
		// 检查是否有内置的正则表达式
		if(isset($validate[strtolower($rule)]))
			$rule = $validate[strtolower($rule)];
		if($value===null)
		{//返回正则表达式
			return $rule;
		}else 
		{//返回正则匹配的结果
			return preg_match($rule,$value)===1;
		}
	}

    /**
     * 搜索表单的数据验证过滤和整理
     * @param array|string $where
     * @param string $key
     * @param string $value
     * @param string $alias
     * @param number $type
     * @param boolean $isEscape 自定义转义
     * @param string $joinWord 自定义转义连接的条件连接 字符-> WHERE AND OR
     * @param string $condition 特殊使用 暂未用到
     *
     */
	function set_search_form_value(&$where,$key,$value,$alias='',$type=1,$joinWord='',$condition='')
	{ 
		$key=empty($alias)?$key:$alias.".".$key;
		switch ($type)
		{
			case 1: //textbox 文本框数据处理  精确查询
				$value=trim_all($value,1);
				if(empty($value)&&($value!==0)&&($value!=='0'))
				{
					return;
				}
				if(!is_array($where))
				{
					$where=$where.$joinWord.' '.$key.' = '."'".addslashes($value)."' ".$condition;
				}
				else 
				{
					$where[$key]=array('eq',$value);
				}
				break;
			case 2: //combox  下拉列表数据处理
				if(stripos($value,','))
				{
					if(!is_array($where))
					{
						$where=$where.$joinWord.' '.$key.' in('.addslashes($value).') '.$condition;
					}
					else
					{
						$where[$key]=array('in',$value);
					}
				}
				if(!check_regex('number',$value))
				{
					return;
				}
				if(!is_array($where))
				{
					$where=$where.$joinWord.' '.$key.'='.intval($value).' '.$condition;
				}
				else
				{
					$where[$key]=array('eq',intval($value));
				}
				break;
			case 3: //datebox 日期数据处理
				if(!check_regex('date',$value))
				{
					return;
				}
				if(!is_array($where))
				{//字符连接时：$condition为 > = < >= <=  <>(不等于)  等字符串
					if($condition == ' < '||$condition == ' <= '){
						//如果是截止时间，加一天：60*60*24
						$value = strtotime($value);
			        	$value = $value + 60*60*24;
			        	$value = date('Y-m-d',$value);
					}
					$where=$where.$joinWord.' '.$key.$condition."'".addslashes($value)."'".' ';
				}
				else
				{//数组形式：$condition为 eq(等于) neq(不等于) gt(大于) egt(大于等于) lt(小于) elt(小于等于)
					if($condition == ' lt '||$condition == ' elt '){
						//如果是截止时间，加一天：60*60*24
						$value = strtotime($value);
			        	$value = $value + 60*60*24;
			        	$value = date('Y-m-d',$value);
					}
					$where[$key]=array($condition,$value);
				}
				break;
			case 4: //timebox 时间数据处理
				if(!check_regex('time',$value))
				{
					return;	
				}
				if(!is_array($where))
				{//字符连接时：$condition为 > = < >= <=  <>(不等于)  等字符串 
					$where=$where.$joinWord.' '.$key.$condition."'".addslashes($value)."'".' ';
				}
				else
				{//数组形式：$condition为 eq(等于) neq(不等于) gt(大于) egt(大于等于) lt(小于) elt(小于等于) 
					$where[$key]=array($condition,$value);
				}
				break;
			case 5: //mothbox 日期数据处理
				if(!check_regex('month',$value))
				{
				    return;
				}
				if(!is_array($where))
				{//字符连接时：$condition为 > = < >= <=  <>(不等于)  等字符串
				    $where=$where.$joinWord.' '.$key.$condition."'".addslashes($value)."'".' ';
				}
				else
				{//数组形式：$condition为 eq(等于) neq(不等于) gt(大于) egt(大于等于) lt(小于) elt(小于等于)
				    $where[$key]=array($condition,$value);
				}
				break;
			case 6://textbox 文本框数据处理  模糊查询 
				$value=trim_all($value,1);
				if(empty($value)&&($value!==0)&&($value!=='0'))
				{
					return;
				}
				if(!is_array($where))
				{
					$where=$where.$joinWord.' '.$key.' like '."'".addslashes($value).'%'."' ".$condition;
				}
				else
				{
					$where[$key]=array('like',$value.'%');
				}
				break;
			case 7://tree 树形下来列表数据处理  货品分类查询专用
				if(!check_regex('number',$value)||$value==-1)
				{
					return;
				}
				$res_goods_class=M('goods_class')->field('is_leaf,path')->where(array('class_id'=>array('eq',$value)))->find();
				$left_join_goods_class='';
				if(!empty($res_goods_class))
				{
					if(!is_array($where))
					{
						if($res_goods_class[is_leaf]==1)
						{
							$where=$where.$joinWord.' '.$key.'='.intval($value).' '.$condition;
						}else{
							$where=$where.$joinWord.' gc_1.path like '."'".addslashes($res_goods_class['path']).',%'."' ".$condition;
							$left_join_goods_class=' LEFT JOIN goods_class gc_1 ON gc_1.class_id = '.$key.' ';
						}
					}
					else
					{
						if($res_goods_class[is_leaf]==1)
						{
							$where[$key]=array('eq',$value);
						}else{
							$where['gc_1.path']=array('like',$res_goods_class['path'].',%');
							$left_join_goods_class=' LEFT JOIN goods_class gc_1 ON gc_1.class_id = '.$key.' ';
						}
					}
					return $left_join_goods_class;
				}
				break;
			case 8: //checkbox  等于0  或者  大于0 (不是布尔型0,1的情况)
				if(!check_regex('number',$value))
				{
					return;
				}
				if(!is_array($where))
				{
					$value=intval($value);
					if($value==0)
					{
						$where=$where.$joinWord.' '.$key.'='.$value.' '.$condition;
					}else{
						$where=$where.$joinWord.' '.$key.'>0 '.$condition;
					}
				}
				else
				{
					$value=intval($value);
					if($value==0)
					{
						$where[$key]=array('eq',$value);
					}else{
						$where[$key]=array('gt',0);
					}
				}
				break;
			case 9: //范围查询处理
				if(!check_regex('number',$value))
				{
				    return;
				}
				if(!is_array($where))
				{//字符连接时：$condition为 > = < >= <=  <>(不等于)  等字符串
				    $where=$where.$joinWord.' '.$key.$condition."'".addslashes($value)."'".' ';
				}
				else
				{//数组形式：$condition为 eq(等于) neq(不等于) gt(大于) egt(大于等于) lt(小于) elt(小于等于)
				    $where[$key]=array($condition,$value);
				}
				break;
			case 10://textbox 文本框数据处理  全模糊查询
				$value=trim_all($value,1);
				if(empty($value)&&($value!==0)&&($value!=='0'))
				{
					return;
				}
				if(!is_array($where))
				{
					$where=$where.$joinWord.' '.$key.' like '."'%".addslashes($value).'%'."' ".$condition;
				}
				else
				{
					$where[$key]=array('like','%'.$value.'%');
				}
				break;
		}
	}
    /**
     * 对数组urlencode编码
     * @param $ar array
     *
     */
    function urlencodArr($ar)
    {
        $temp = array();
        if(is_array($ar))
        {
            foreach ($ar as $key=>$value)
            {
                $temp[urlencodArr($key)] = urlencodArr($value);
            }
            return $temp;
        }
        return urlencode($ar);
    }

	function SE($msg, $type = "", $code = 0) {
		throw new \Think\Exception\BusinessLogicException($msg, $code);
	}

	/**
	 * @param        $message 记录的日志信息
	 * @param string $level   日志级别
	 
	 */
	function logExtent($message, $level = \Think\Log::DEBUG) {
		\Think\Log::write(is_array($message)||is_object($message)?print_r($message,true):$message,$level);
	}

	/**
	 * 文件下载
	 * @param string $file_url 文件位置 
	 */
	function downloadFile($file_url){
        $file_name = basename($file_url);  
        $file_type = explode('.',$file_url);  
        $file_type = $file_type[count($file_type)-1];  
        $file_name = trim($new_name=='')?$file_name:urlencode($new_name);  
        $file_type = fopen($file_url,'r'); //打开文件  
        //输入文件标签  
        header("Content-type: application/octet-stream");  
        header("Accept-Ranges: bytes");  
        header("Accept-Length: ".filesize($file_url));  
        header("Content-Disposition: attachment; filename=".$file_name);  
        //输出文件内容  
        echo fread($file_type,filesize($file_url));  
        fclose($file_type);  
    }
	/*
	 * 检查是否为工作时间 8：00 - 18：00
 	 */
	function workTimeExportNum($file_type = 'excel'){
		$now = time();
		$hour = getdate($now);
		if( ($hour['hours']>=8 && $hour['hours']<12) || ($hour['hours']>=13 &&$hour['hours']<=18) ){
			if($file_type == 'csv'){
				return 10000;
			}
			return 1000;//上班时间  这里最好给配置项的。
		}
		if($file_type == 'csv'){
			return 20000;
		}
		return 4000;
	}

	function workTimeUploadNum(){
		$now = time();
		$hour = getdate($now);
		if( ($hour['hours']>=8 && $hour['hours']<12) || ($hour['hours']>=13 &&$hour['hours']<=18) ){
			return 10000;//上班时间  这里最好给配置项的。
		}
		return 50000;
	}

	function propFildConv(&$fields,$conv_field,$prefix,$type=1)
	{
		$value = array_values($fields);
		foreach($fields as $k=>$v)
		{
			$field = empty($v['field'])?$v:$v['field'];
			if(strpos($field,$conv_field)!==false)
			{
				$prop = substr($field,strpos($field,$conv_field));
				if($conv_field=='prop')
				{
					$prop = substr($field,strpos($field,$conv_field),5);
				}

				$prop_value = get_config_value($prefix.'_'.$prop,0);

				if($prop_value!==0)
				{
					$key[] = $prop_value;
				}else
				{
					$key[]=$k;
				}
			}else{
				$key[] = $k;
			}
		}
		$new_array = array_combine($key,$value);
		$fields = $new_array;
	}

	function getAddress($province, $city, $district, &$province_id, &$city_id, &$district_id) {
		require_once(APP_PATH.'/Platform/Common/address.php');
		global $g_province_map, $g_city_map, $g_district_map;

		$province_id = 0;
		$city_id     = 0;
		$district_id = 0;

		$tmp = @$g_province_map[ $province ];
		if (!$tmp) {
			\Think\Log::write("invalid_province $province",'WARN');
			return;
		}

		$province_id = $tmp;
		$tmp         = @$g_city_map["{$province_id}-{$city}"];
		if (!$tmp) {
			\Think\Log::write("invalid_city $city",'WARN');
			return;
		}

		$city_id = $tmp;
		$tmp     = @$g_district_map["{$city_id}-{$district}"];
		if (!$tmp) {
			if (!empty($district)) {
				\Think\Log::write("invalid_district $district",'WARN');
			}
			return;
		}
		$district_id = $tmp;
	}




?>
