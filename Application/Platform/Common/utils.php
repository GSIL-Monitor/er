<?php

define('TOP_SDK_WORK_DIR', ROOT_DIR . '/logs/api/');
define('EMAIL_URL',  ROOT_DIR . "/../Runtime/Logs/email/");

//任务完成
define('TASK_OK', 0);
//任务挂起，下一次订时再处理
define('TASK_SUSPEND', 1);
//立即重试
define('TASK_RETRY', 2);

date_default_timezone_set('PRC');
set_time_limit(0);
bcscale(2);

require_once(ROOT_DIR . '/Task/global.php');
require_once(ROOT_DIR . '/Task/config.php');
require_once(TOP_SDK_DIR . '/Pheanstalk/Pheanstalk_init.php');
require_once(ROOT_DIR . '/Common/db.inc.php');
require_once(ROOT_DIR . '/Common/api_error.php');
require_once(ROOT_DIR . '/Common/PHPMailer.class.php');

//计算本机的外部IP
if (empty($current_front_host)) {
    $current_front_host = exec("/sbin/ip addr show eth1|grep 'inet '|awk '{print $2}'|cut -d/ -f1");
}

//是否是WINDOWS系统
$g_os_win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

//日志
/*var_dump($g_log_dir);
if (empty($g_log_dir)) $g_log_dir = ROOT_DIR . "/../Runtime/Logs/default/Platform";*/
$g_log_dir = ROOT_DIR . "/../Runtime/Logs/default/Platform/";
/*if (substr($g_log_dir, -1, 1) != '/') $g_log_dir .= '/';*/
$g_log_dir_append = '';

//禁止写日志
$g_disable_logx = false;

//写入日志
function logx($msg, $dir = "default/default", $level = "warning") {
    /* global $g_master_tube, $g_log_dir, $g_log_dir_append, $g_disable_logx;
     if ($g_disable_logx) return;*/
    global $g_log_dir;
    if($dir=='error'){
        $dir = 'default/default';
        $level = 'error';
    }
    if($dir == ''){
        $dir ='default/default';
    }
    $arr  = explode("/", $dir);
    $dir  = "";
    $flag = 0;
    foreach ($arr as $v) {
        if ($flag == 0) {
            $dir .= $v;
        } else if ($flag == 1) {
            $dir = $dir . "/Platform/" . $v;
        } else {
            $dir = $dir . "/" . $v;
        }
        $flag++;
    }
    if (is_array($msg)) $msg = json_encode($msg);
    $g_log_dir = ROOT_DIR . "/../Runtime/Logs/" . $dir . "/";
    if (!is_dir($g_log_dir)) {
        try {
            mkdir($g_log_dir, 0755, true);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    $time     = date("y_m_d", time());
    $log_file = "{$g_log_dir}{$time}.log";
    //echo date('Y-m-d H:i:s', $tm) . "\t{$pid}\t{$msg}\n";
    file_put_contents($log_file, date('Y-m-d H:i:s', time()) . "\t{$msg}\n", FILE_APPEND);
    global $g_email_sentto_list, $g_email_num;
    if ($level == "fatal" || $level == "error") {
        $email_cache_file = EMAIL_URL."/$time.log";
        @file_put_contents($email_cache_file, "{$msg}\n", FILE_APPEND);
        if (checkEmail($g_email_num, $msg)) {
            smtp_mail($g_email_sentto_list, "server error", $msg);
        }
    }
}

function disableLogx($diable = TRUE) {
    global $g_disable_logx;
    $g_disable_logx = $diable;
}

function _error_handler($severity, $message, $filepath, $line) {
    if ($severity == E_STRICT) {
        return;
    }

    $error_reporting = error_reporting();
    if ($error_reporting == 0) {
        return;
    }

    logx("PHP Error Severity: $severity --> $message $filepath $line");
}

set_error_handler('_error_handler');

function decodeDbPwd($pwd, $key) {
    return rc4($key, base64_decode($pwd));
}

function valid_utf8_short($str) {
    $result = '';
    while (!empty($str)) {
        if (preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|' .
                       '\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|' .
                       '\xED[\x80-\x9F][\x80-\xBF])*%xs', $str, $matches)) {
            $result .= $matches[0];
            $str = substr($str, strlen($matches[0]) + 1);
        } else {
            $str = substr($str, 1);
        }
    }
    return $result;
}

function internal_substr($str, $start, $len, $encoding) {
    if (function_exists('mb_substr')) {
        return mb_substr($str, $start, $len, $encoding);
    } else {
        return iconv_substr($str, $start, $len, $encoding);
    }
}

function valid_utf8($str, $mb = false) {
    if (function_exists('mb_convert_encoding')) {
        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        if ($str && $mb) return $str;
    }

    if (function_exists('mb_strlen')) {
        $len = mb_strlen($str, 'UTF-8');
    } else {
        $len = iconv_strlen($str, 'UTF-8');
    }

    if ($len <= 1024) return valid_utf8_short($str);

    $s = internal_substr($str, 0, 1024, 'UTF-8');
    $l = 1024;

    $result = '';
    while (1) {
        $result .= valid_utf8_short($s);

        $len -= $l;
        if ($len == 0) break;

        $str = internal_substr($str, $l, $len, 'UTF-8');
        if ($len >= 1024) $l = 1024;
        else $l = $len;
        $s = internal_substr($str, 0, $l, 'UTF-8');
    }

    return $result;
}

function json_decode_safe($str, $assoc = false) {
    $o = json_decode($str, $assoc, 512, JSON_BIGINT_AS_STRING);
    if (!$o && !empty($str)) {
        $str = valid_utf8($str);
        $o   = json_decode($str, $assoc, 512, JSON_BIGINT_AS_STRING);
        if (!$o) {
            $str = str_replace(array("\r", "\n", "\t"), '', $str);
            $o   = json_decode($str, $assoc, 512, JSON_BIGINT_AS_STRING);
            if (!$o) {
                $str = str_replace("\\", "\\\\", $str);
                $o   = json_decode($str, $assoc, 512, JSON_BIGINT_AS_STRING);
            }
        }
    }

    return $o;
}

//database
//host:port
//	conn, used? db_name, host
global $g_db_conns;
$g_db_conns = array();
//user_id, host, db_name
global $g_user_cache;
$g_user_cache = array();

function checkDbConn(&$db, $host, $db_name, $db_user, $db_pwd) {
    if ($db->ping()) return true;

    $db->close();
    $db = NULL;

    $db = new MySQLdb($host, $db_user, $db_pwd, false, $db_name);
    if (!$db->connect()) {
        $db = NULL;

        logx("Mysql Connect Fail in checkDbConn: {$host} {$db_user} {$db_name}",'','error');
        return false;
    }

    if ($db->execute('SET NAMES UTF8') === false) {
        $db->close();
        $db = NULL;

        logx("Mysql set encodeing Fail in checkDbConn",'','error');
        return false;
    }

    return true;
}

function getUserDb($sid) {
    global $g_user_cache, $g_db_conns, $g_eshop_router_url;
    $g_user_cache = array(
        "user:erp_dev" => array(
            "host" => "101.200.202.174",
            "instance" => "",
            "db_name" => "erp_dev",
            "db_user" => "ekb",
            "db_pwd" => "hHysbyj!@#$1234"
        )
    );
    if (isset($g_user_cache["user:$sid"])) {
        $cache = &$g_user_cache["user:$sid"];

        $host     = $cache['host'];
        $instance = $cache['instance'];
        $db_name  = $cache['db_name'];
        $db_user  = $cache['db_user'];
        $db_pwd   = $cache['db_pwd'];
    } else {
        static $cache = '';
		if (empty($cache)){
			$cache = new Memcached();
			$cache->addServer('10.132.180.236', 30001);
		} 
		$merchant = $cache->get($sid);
		if (!is_array($merchant)){
			$str = file_get_contents($g_eshop_router_url . "?action=conn&sid=" . urlencode($sid));
			//$str = file_get_contents("http://121.199.38.85:10000/api/get_userdb?sid=" . urlencode($sid));
			$result = json_decode($str);
			if ($result->status != 0) {
				logx($sid." router fail: $str", $sid . "/default",'error');
				return NULL;
			}
			$host     = $result->info->db_host;
			$db_name  = $result->info->db_name;
			$instance = '';

			$db_user = $result->info->db_user;
			$db_pwd  = decodeDbPwd($result->info->db_pwd, $result->info->secret);
			$merchant = (array)$result->info;
			$cache->set($sid, $merchant);
		} else {
			$host     = $merchant['db_host'];
			$db_name  = $merchant['db_name'];
			$instance = '';

			$db_user = $merchant['db_user'];
			$db_pwd  = decodeDbPwd($merchant['db_pwd'], $merchant['secret']);
		}
		
        //$result = array("0" => "101.200.202.174", "1" => "erp_dev", "2" => "", "3" => "dev", "4" => "hhysbyj!@#$");
        
        //$db_pwd = $result[4];
        /*$hash = $result[6];

        if(md5(strtolower($sid).$host.$db_name) != $hash)
        {
            logx("userdb hash fail", $sid);
            return NULL;
        }*/

        $g_user_cache["user:$sid"] = array('host' => $host, 'instance' => $instance, 'db_name' => $db_name, 'db_user' => $db_user, 'db_pwd' => $db_pwd);
    }

    if (empty($db_name)) {
        logx($sid ." invalid db config", $sid . "/default",'error');
        return FALSE;
    }

    $cache_key = $sid;
    if (!empty($instance)) $cache_key .= '_' . $instance;

    //
    if (isset($g_db_conns[ $cache_key ])) {
        $conn = &$g_db_conns[ $cache_key ];
        if ($conn['used']) {
            logx($sid." conntion in use", $sid . "/default",'error');
            return NULL;
        }

        $now = time();
        if ($conn['db_name'] == $db_name && $now - $conn['time'] < 120) {
            $conn['time'] = $now;
            $conn['used'] = TRUE;
            return $conn['conn'];
        }

        if ($now - $conn['time'] >= 120) {
            if (!checkDbConn($conn['conn'],
                             $host,
                             $db_name,
                             $db_user,
                             $db_pwd)
            ) {
                unset($g_db_conns[ $cache_key ]);
                logx($sid." connection close", $sid . "/default",'error');
                return NULL;
            }
        }
        $conn['time'] = $now;

        // if (!$conn['conn']->select_db($db_name)) {
            // logx("use db fail: $db_name", $sid . "/default");
            // return NULL;
        // }
        $conn['conn']->set_tag($sid);

        $conn['used']    = TRUE;
        $conn['db_name'] = $db_name;

        return $conn['conn'];
    }

    $mysql = new MySQLdb($host, $db_user, $db_pwd, false, $db_name, $sid);
    if (!$mysql->connect()) {
        logx($sid." Mysql Connect Fail: {$host} {$instance} {$db_user} {$db_name}", $sid . "/default",'error');
        return NULL;
    }

    if ($mysql->execute('SET NAMES UTF8') === false) {
        $mysql->close();
        logx($sid." Mysql set encodeing Fail", $sid . "/default",'error');
        return NULL;
    }

    $conn = array
    (
        'conn'    => $mysql,
        'used'    => true,
        'db_name' => $db_name,
        'host'    => $host,
        'time'    => time()
    );

    $mysql->db_instance = $instance;

    $g_db_conns[ $cache_key ] = $conn;

    return $mysql;
}

function releaseDb($conn) {
    global $g_db_conns;

    $cache_key = $conn->get_tag();
    $instance  = @$conn->db_instance;

    if (!empty($instance)) $cache_key .= '_' . $instance;

    $g_db_conns[ $cache_key ]['used'] = false;
    $g_db_conns[ $cache_key ]['time'] = time();
}

function get_trade_main_db()
{
    global $g_trade_main_db_config;
    if(!$g_trade_main_db_config)
    {
        logx("g_trade_main_db_config is null!");
        return NULL;
    }
    $host = $g_trade_main_db_config['host'];
    $db_user = $g_trade_main_db_config['db_user'];
    $db_name = $g_trade_main_db_config['db_name'];
    $mysql = new MySQLdb($host, $db_user, $g_trade_main_db_config['db_pwd'], false, $db_name,@$g_trade_main_db_config['tag']);
    if(!$mysql->connect())
    {
        logx("Mysql Connect Fail: {$host} {$db_user} {$db_name}");
        return NULL;
    }
    if($mysql->execute('SET NAMES UTF8') === false)
    {
        $mysql->close();
        logx("Mysql set encodeing Fail");
        return NULL;
    }
    return $mysql;
}


function clearDbList() {
    global $g_db_conns, $g_jst_db_conn;

    $now = time();
    if (is_array($g_db_conns)) {
        foreach ($g_db_conns as $key => &$conn) {
            if ($now - $conn['time'] > 120) {
                $conn['conn']->close();
                unset($g_db_conns[ $key ]);
            }
        }
    }

    if (is_array($g_jst_db_conn)) {
        foreach ($g_jst_db_conn as $key => &$conn) {
            if ($now - $conn['time'] > 20) {
                $conn['conn']->close();
                unset($g_jst_db_conn[ $key ]);
            }
        }
    }
}

function getJstDb($sid, $rds_id) {
    global $g_jst_db_config_list, $g_jst_db_conn;

    $idx  = $rds_id - 1;
	/*
    $url  = 'http://10.132.180.236/index.php?action=rds&index=' . $idx;
    $conn = json_decode(file_get_contents($url));
    if ($conn->status != 0) {
        return NULL;
    } else {
        $conn->info->pwd = decodeDbPwd($conn->info->pwd, $conn->info->salt);

        $jst_db_config = $conn->info;
    }
	*/
	$jst_db_config = (object)$g_jst_db_config_list[ $idx ];
	$jst_db_config->pwd = decodeDbPwd($jst_db_config->pwd, $jst_db_config->salt);

    $key = 'db_' . $idx;
    if (is_array($g_jst_db_conn) && isset($g_jst_db_conn[ $key ]) && $g_jst_db_conn[ $key ]) {
        $conn = &$g_jst_db_conn[ $key ]['conn'];

        if (checkDbConn($conn,
                        $jst_db_config->host,
                        $jst_db_config->db_name,
                        $jst_db_config->user,
                        $jst_db_config->pwd)) {
            $g_jst_db_conn[ $key ]['time'] = time();
            return $conn;
        }
        return NULL;
    }
    $mysql = new MySQLdb($jst_db_config->host, $jst_db_config->user, $jst_db_config->pwd, false, $jst_db_config->db_name);
    if (!$mysql->connect()) {
        logx("JstDb Connect Fail: {$jst_db_config->host} {$jst_db_config->user} {$jst_db_config->db_name}", $sid . "/default",'error');
        return NULL;
    }

    if ($mysql->execute('SET NAMES UTF8') === false) {
        $mysql->close();
        logx("JstDb set encodeing Fail", $sid . "/default",'error');
        return NULL;
    }

    if (!is_array($g_jst_db_conn)) $g_jst_db_conn = array();

    $g_jst_db_conn[ $key ] = array('conn' => $mysql, 'time' => time());

    return $mysql;
}

function rowToSQL(&$row, &$db) {
    $s = array();
    foreach ($row as $k => $v) {
        if (is_int($v))
            $s[] = $v;
        else if (is_array($v))
            $s[] = $v[0];
        else
            $s[] = "'" . addslashes($v) . "'";

    }

    return '(' . implode(',', $s) . ')';
}

function putDataToTable(&$db, $tab, &$rows, $update = '', $log_sql = false) {
    if (count($rows) == 0)
        return true;

    $keys = array();
    foreach ($rows[0] as $k => $v)
        $keys[] = $k;

    $data = array();
    for ($i = 0; $i < count($rows); $i++) {
        $data[] = rowToSQL($rows[ $i ], $db);
    }
    $data = implode(',', $data);

    if (!empty($update))
        $sql = "insert into " . $tab . " (" . implode(',', $keys) . ") values " . $data . ' ' . $update;
    else
        $sql = "insert ignore into " . $tab . " (" . implode(',', $keys) . ") values " . $data;

    if ($log_sql)
        logx($sql);
    return $db->execute($sql);
}

function UpdateData(&$db, $table, $arr, $where) {
    $sql  = "update $table set ";
    $flag = 0;
    foreach ($arr as $k => $v) {
        $sql .= ($flag == 0 ? "" : ", ") . $k . " = '" . addslashes . "'";
        $flag = 1;
    }
    $sql .= " " . $where;

    return $db->execute($sql);
}

function UpdateDataEx(&$db, $table, $arr, $change, $where) {
    $sql  = "update $table set ";
    $flag = 0;
    foreach ($arr as $k => $v) {
        $sql .= ($flag == 0 ? "" : ", ") . $k . " = '" . addslashes . "'";
        $flag = 1;
    }
    foreach ($change as $k => $v) {
        if (is_numeric($v)) {
            $sql .= sprintf("%s %s=%s%s%s", ($flag == 0 ? "" : ","),
                            $k, $k, ($v >= 0 ? "+" : " "), $v);
            $flag = 1;
        } else {
            $sql .= sprintf("%s %s=CONCAT(%s,'%s')", ($flag == 0 ? "" : ","),
                            $k, $k, addslashes($v));
            $flag = 1;
        }
    }
    $sql .= " " . $where;

    return $db->execute($sql);
}

function testPid($tube, $return = false) {
    $lock_pid = fopen(ROOT_DIR . "/pids/$tube.pid", 'a+');
    if ($lock_pid) {
        if (flock($lock_pid, LOCK_EX | LOCK_NB)) {
            if (!$return) {
                fclose($lock_pid);
                return true;
            }

            return $lock_pid;
        }
        fclose($lock_pid);
    }

    return false;
}

//给客户端发通知消息
function SendNotify($msg) {
    $fp = fsockopen('udp://127.0.0.1', 12345, $errno, $errstr);
    if ($fp) {
        fwrite($fp, json_encode($msg));
        fclose($fp);
        return TRUE;
    }
    return FALSE;
}

//发员工发通知
function SendEmployeeNotify($sid, $userId, $msg) {
    $data = array
    (
        "method"  => "send_to_employee",
        "sid"     => $sid,
        "to"      => $userId,
        "message" => $msg
    );

    return SendNotify($data);
}

//给卖家所有在线员工发通知
function SendMerchantNotify($sid, $msg) {
    $data = array
    (
        "method"  => "send_to_merchant",
        "sid"     => $sid,
        "message" => $msg
    );

    return SendNotify($data);
}

//发送邮件
function smtp_mail($sendto_email, $subject, $msg,$resend='false',$count=0) {
    global $g_email_host;
    global $g_email_user_name;
    global $g_email_password;
    global $g_email_sender_account;
    global $g_email_sender_name;
    global $g_re_email_user_name;
    global $g_re_email_password;

    $mail = new \Platform\Common\PHPMailer();
    $mail->IsSMTP();
    $mail->Host     = $g_email_host;
    $mail->SMTPAuth = true;
    if(!$resend){
        $mail->Username = $g_re_email_user_name;
        $mail->Password = $g_re_email_password;
        $mail->From     = $g_re_email_user_name;
    }else{
        $mail->Username = $g_email_user_name;
        $mail->Password = $g_email_password;
        $mail->From     = $g_email_sender_account;
    }
    //$mail->Username = $resend?$g_re_email_user_name:$g_email_user_name;     //发件人邮箱
    //$mail->Password = $resend?$g_re_email_password:$g_email_password; // 发件人邮箱密码
    //$mail->From     = $resend?$g_re_email_user_name:$g_email_sender_account;      // 发件人邮箱
    $mail->FromName = $g_email_sender_name;

    $mail->CharSet = "UTF-8";
    $mail->setLanguage('zh_cn');
    $mail->Encoding = "base64";
    foreach ($sendto_email as $val) {
        $mail->AddAddress($val, "");
    }

    //$mail->AddReplyTo("","");
    //$mail->WordWrap = 50;
    //$mail->AddAttachment("/var/tmp/file.tar.gz");
    //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");
    //$mail->IsHTML(true);  // send as HTML

    $mail->Subject = $subject;
    $mail->Body    = $msg;
    //$mail->AltBody ="text/html";
    $date = date('Y-m-d', time());
    if (!$mail->Send()) {
        $count++;
        if($count>1){
            logx("邮件错误信息: " . $mail->ErrorInfo);
        }else{
            smtp_mail($sendto_email, $subject, $msg,true,$count);
        }
    } else {
        file_put_contents(EMAIL_URL . "/{$date}.log", date('Y-m-d H:i:s', time()) . "邮件发送成功 \n", FILE_APPEND);
    }

}

function checkEmail($num, $msg) {
    $date = date('y_m_d', time());
    @mkdir(EMAIL_URL);
    $file = @file(EMAIL_URL . "/$date.log");
    $file = (array)($file);
    $res = array_count_values($file);
    if ($res["$msg\n"] >= $num || strpos($msg, "Deadlock") || strpos($msg, "timeout"))
        return false;
    else
        return true;

}

//读取环境变量
function env($name, $def = '') {
    $val = getenv($name);
    if ($val === FALSE) return $def;

    return $val;
}

//护城河签名
function hchSign($appSecret, $paramArr) {
    $data = $appSecret;
    ksort($paramArr);

    foreach ($paramArr as $key => $val) {
        if (!empty($key)) {
            $data .= $key . $val;
        }
    }
    $data .= $appSecret;

    $sign = md5($data);

    return $sign;
}

function get_user_id() {
    if (function_exists('get_operator_id') && function_exists('get_sid')) {
        $sid         = get_sid();
        $operator_id = get_operator_id();
        $use_id      = $sid . ':' . $operator_id;
    } else {
        $use_id = '系统';
    }
    return $use_id;
}

function get_ip() {
    if (!empty($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = exec("/sbin/ip addr show eth1|grep 'inet '|awk '{print $2}'|cut -d/ -f1");
    }
    return $ip;
}

function get_ati() {
	/*
	$ati = $_COOKIE['_ati'];
	if (!empty($ati)){
		session('hch_ati', $ati);
		return $ati;
	} else {
		return '0000000000';
	}
	*/
    return empty($_COOKIE['_ati']) ? '0000000000' : $_COOKIE['_ati'];
}

//护城河请求
function hchRequest($url, $params) {
    $ip = get_ip();
    if ($ip=='127.0.0.1' || $ip=='::1' || $ip=='localhost'){
        return;
    }
    $appkey    = 68756660;
    $appsecret = "WeF778rCV40RwjydABHO";

    $millsecs = round(microtime(true) * 1000);

    $params["topAppKey"] = '23305776';
    $params["appKey"]    = $appkey;
    $params["appName"]   = 'E快帮';
	if (!isset($params["userId"])){
		$params["userId"]    = get_user_id();
	}
    if (!isset($params["ati"])){
		$params["ati"]    = get_ati();
	}
    $params["userIp"]    = get_ip();
    $params["time"]      = $millsecs;
    $log_param['user_name'] = $_SESSION['user_name'];
    $log_param = array_merge($params,$log_param);
    $error_level= (!stristr(PHP_OS, 'WIN'))?'':'error';
    if ('http://account.ose.aliyun.com/login' == $url && '0000000000' == $params["ati"]){
		logx("unknown hch login  params: " . print_r($log_param, true),'',$error_level);
		return;
	}
    $params["sign"]      = hchSign($appsecret, $params);

    $data = http_build_query($params);

    $context_options = array(
        'http' => array(
            'method'  => 'POST',
            "timeout" => 2,
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($data) . "\r\n",
            'content' => $data
        )
    );

    $context  = stream_context_create($context_options);
    $response = file_get_contents($url, false, $context);
    $json     = json_decode($response);

    //if (is_array($json) && isset($json['result']) && $json['result'] == 'success') return $json;
    if (empty($json->result) || $json->result != 'success') {
        $json->username = $_SESSION['user_name'];
        $json->sid = empty($params["userId"])?'':$params['userId'];
        logx('hch params:'.print_r($params,true),'');
        logx("hch fail : " . print_r($json, true),'');
    } else {
        logx("hch success : " . print_r($json, true));
    }
    return $json;
}

/**
 * 去除空格
 * @param string $str
 * @param number $type
 * @return string
 */
function trim_all_space($str,$type=0)
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

//2.0中移过来的一部分初始化代码
define('TYPE_DT_STRING', 0);
define('TYPE_DT_DATE', 1);
define('TYPE_DT_DOUBLE', 2);
define('TYPE_DT_DECIMAL', 3);
define('TYPE_DT_INTEGER', 4);
define('TYPE_DT_UNSIGNED_LONG', 5);
define('TYPE_DT_LONG_LONG', 6);
define('TYPE_DT_UNSIGNED_LONG_LONG', 7);

?>