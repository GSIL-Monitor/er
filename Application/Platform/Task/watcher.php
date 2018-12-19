<?php
define("ROOT_DIR", dirname(__DIR__));
define("TOP_SDK_DIR", ROOT_DIR . "/SDK");
require_once(ROOT_DIR . '/Task/config.php');
require_once(ROOT_DIR . '/SDK/Pheanstalk/Pheanstalk_init.php');
require_once(ROOT_DIR . '/Common/utils.php');
require_once(ROOT_DIR . '/Task/global.php');

if(count($argv) < 3)
{
	echo "usage: php {argv[0]} tube_name task_count\n";
	exit();
}

date_default_timezone_set('PRC');
set_time_limit(0);

$tube = $argv[1];
$task_count = intval($argv[2]);

//日志
if(empty($g_log_dir)) $g_log_dir = ROOT_DIR;
if(substr($g_log_dir, -1, 1) != '/') $g_log_dir .= '/';
$g_log_dir_append = '';

//延时调用
$iv = 0;
if(count($argv) > 3) $iv = intval($argv[3])*60;
if($iv < 60) $iv = 60;

$sec = hexdec(substr(md5($current_front_host.$tube), 0, 4)) % $iv;
logx("$tube delay $sec seconds ...");
sleep($sec);
logx("$tube continue ...");
/*
function logx($msg, $f=NULL)
{
	global $tube, $g_log_dir, $g_log_dir_append;
	$tm = time();
	
	$dt = date('Y-m-d', $tm);
	
	$pid = getmypid();
	
	$pos = strpos($tube, '.');
	if($pos === FALSE)
	{
		$tube = $tube;
		$left = '';
	}
	else
	{
		$tube = substr($tube, 0, $pos);
		$left = substr($tube, $pos+1) . '_';
	}
	
	@mkdir("{$g_log_dir}{$tube}");
	@mkdir("{$g_log_dir}{$tube}/{$dt}");
	
	if($f) $g_log_dir_append = $f;
	
	if($g_log_dir_append)
		$log_file = "{$g_log_dir}{$tube}/{$dt}/{$left}{$g_log_dir_append}.log";
	else
		$log_file = "{$g_log_dir}{$tube}/{$dt}/{$left}default.log";
	
	file_put_contents($log_file, date('Y-m-d H:i:s', $tm) . "\t{$pid}\t{$msg}\n", FILE_APPEND);
}*/

function execInBg($cmd)
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		$WshShell = new COM("WScript.Shell");
		$oExec = $WshShell->Run("$cmd", 7, false);
	}
	else
		exec("$cmd >/dev/null &");
}

function lockPid()
{
	global $tube, $lock_pid;
	
	$lock_pid = fopen(ROOT_DIR . "/pids/$tube.pid", 'a+');
	if($lock_pid)
	{
		if(flock($lock_pid, LOCK_EX|LOCK_NB))
		{
			ftruncate($lock_pid, 0);
			fwrite($lock_pid, getmypid());
			fflush($lock_pid);
			return true;
		}
		
		fclose($lock_pid);
	}
	
	logx("Pid $tube Exists!!!");
	echo "Pid $tube Exists!!!\n";
	return false;
}

if(!lockPid())
{
	exit();
}

try
{
	$pheanstalk = new Pheanstalk($g_bt_config['host'], $g_bt_config['port'], $g_bt_config['connect_timeout']);
	$pheanstalk->useTube($tube);
	
	//加入一条空消息
	$msg = array('handle'=>'nothing', 'data'=>'');
	$pheanstalk->put(json_encode($msg));
	
	$task_id = mt_rand();
	
	//启动主任务
	if(!defined('PHP_BINARY'))
		$cmd = "php " . ROOT_DIR . "/Task/worker.php $tube $task_id $task_count";
	else
		$cmd = PHP_BINARY . " " . ROOT_DIR . "/Task/worker.php $tube $task_id $task_count";
	execInBg($cmd);
	
	$step = 0;
	$now = time();
	$start_time = $now;
	$log_time = $now;
	$i =0;
	while(true)
	{
		$result = $pheanstalk->statsTube($tube);
		
		$total_jobs = $result->offsetGet('total-jobs') - $result->offsetGet('cmd-delete') - $result->offsetGet('current-jobs-buried');
		
		if($total_jobs == 0 && $step < 2)
		{
			$current_watching = $result->offsetGet('current-watching');
			$msg = array('handle'=>'', 'data'=>array('consumer'=>$current_watching, 'inc'=>0, 'tube'=>$tube, 'before'=>($step==0?true:false), 'task_id'=>$task_id));
			
			$pheanstalk->put(json_encode($msg), 4096);
			
			$step++;
		}
		else if($result->offsetGet('current-using')==1 &&
			$result->offsetGet('current-watching')==0 &&
			$result->offsetGet('current-waiting')==0)
		{
			if($step == 2 || time() - $start_time > 5)
			{
				if($total_jobs > 0)
					logx("queue_status: " . print_r($result, true),'');
				break;
			}
		}
		if($tube == 'TradeSlow'){
			$c_cmd_delete = $result->offsetGet('cmd-delete');
			file_put_contents(ROOT_DIR . "/../Runtime/File/trade_slow_tube.txt",$c_cmd_delete); //获取当前队列deleted的值写入文件
			//每1分钟记录下队列当前deleted值，如果没发生变化，记录值i+1.
			if(time()-$log_time>=60){
				$o = file_get_contents(ROOT_DIR . "/../Runtime/File/trade_slow_tube.txt");
				if($o == $result->offsetGet('cmd-delete') && $o!=0){
					$i++;
				}
				file_put_contents(ROOT_DIR . "/../Runtime/File/trade_slow_tube.txt",$result->offsetGet('cmd-delete'));
				$log_time=time();
			}
			//如果10分钟了deleted值还未变化，记录队列状态，发送错误邮件。查看是否异常
			if($i>=30){
				logx("trade_slow_tube_status: " . print_r($result, true),'','error');
				$i = 0;
			}
		}elseif($tube == 'Stock'){
			$c_cmd_delete = $result->offsetGet('cmd-delete');
			file_put_contents(ROOT_DIR . "/../Runtime/File/stock_tube.txt",$c_cmd_delete); //获取当前队列deleted的值写入文件
			//每1分钟记录下队列当前deleted值，如果没发生变化，记录值i+1.
			if(time()-$log_time>=60){
				$o = file_get_contents(ROOT_DIR . "/../Runtime/File/stock_tube.txt");
				if($o == $result->offsetGet('cmd-delete') && $o!=0){
					$i++;
				}
				file_put_contents(ROOT_DIR . "/../Runtime/File/stock_tube.txt",$result->offsetGet('cmd-delete'));
				$log_time=time();
			}
			//如果4分钟了deleted值还未变化，记录队列状态，发送错误邮件。查看是否异常
			if($i>=10) {
				logx("stock_tube_status: " . print_r($result, true), '', 'error');
				$i = 0;
			}
		}
		
		sleep(1);
	}
}
catch(Pheanstalk_Exception $e)
{
	$error_msg = 'Pheanstalk Error in watcher: ' . $e->getMessage();
	logx($error_msg,'','error');
	echo $error_msg, "\n";
	
	exit();
}


?>