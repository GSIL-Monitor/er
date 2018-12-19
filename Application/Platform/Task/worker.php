?<?php
//cmdline
//php worker.php {module} main
//php worker.php {module}

if (count($argv) < 3) {
    echo "php worker.php {module} {task_id}\n";
    exit();
}
define("ROOT_DIR", dirname(__DIR__));
define("TOP_SDK_DIR", ROOT_DIR . "/SDK");
require_once(ROOT_DIR . "/Common/utils.php");
require_once(TOP_SDK_DIR . "/Pheanstalk/Pheanstalk.php");
require_once(TOP_SDK_DIR . "/Pheanstalk/Pheanstalk_init.php");

function rc4($key, $str) {
    $s = array();
    for ($i = 0; $i < 256; $i++) {
        $s[ $i ] = $i;
    }
    $j = 0;
    for ($i = 0; $i < 256; $i++) {
        $j       = ($j + $s[ $i ] + ord($key[ $i % strlen($key) ])) % 256;
        $x       = $s[ $i ];
        $s[ $i ] = $s[ $j ];
        $s[ $j ] = $x;
    }
    $i   = 0;
    $j   = 0;
    $res = '';
    for ($y = 0; $y < strlen($str); $y++) {
        $i       = ($i + 1) % 256;
        $j       = ($j + $s[ $i ]) % 256;
        $x       = $s[ $i ];
        $s[ $i ] = $s[ $j ];
        $s[ $j ] = $x;
        $res .= $str[ $y ] ^ chr($s[ ($s[ $i ] + $s[ $j ]) % 256 ]);
    }
    return $res;
}

//����ģ��
$g_handle_map      = array();
$g_exit_map        = array();
$g_before_exit_map = array();

function registerHandle($handle, $fp) {
    global $g_handle_map;
    $g_handle_map[ $handle ] = $fp;
}


function registerExit($qp) {
    global $g_exit_map;
    $g_exit_map[] = $qp;
}

function registerBeforeExit($qp) {
    global $g_before_exit_map;
    $g_before_exit_map[] = $qp;
}

//tube
$g_master_tube = $argv[1];
$g_used_tube   = $g_master_tube;

$g_task_id = $argv[2];
function setLogSid($sid) {
    global $g_log_dir_append;
    $g_log_dir_append = $sid;
}

/*$g_disable_alarm = false;
function resetAlarm($secs = 120) {
    global $g_os_win, $g_disable_alarm;

    if ($g_disable_alarm) return;
    if (!$g_os_win) pcntl_alarm($secs);
}*/

function connectBeanstalk() {
    global $pheanstalk, $g_bt_config, $g_master_tube, $g_used_tube;
    try {
        $pheanstalk = new Pheanstalk($g_bt_config['host'], $g_bt_config['port'], $g_bt_config['connect_timeout']);

        $pheanstalk->useTube($g_master_tube);
        $pheanstalk->watch($g_master_tube);

        $g_used_tube = $g_master_tube;
    } catch (Pheanstalk_Exception $e) {
        logx('Pheanstalk START Failed: ' . $e->getMessage());
        return false;
    }

    return true;
}

//beanstalk
$pheanstalk = null;
if (!connectBeanstalk())
    exit();

function pushTask($handle, $data, $delay = 0, $pri = 1024, $ttr = 600, $validity = 0) {
    global $pheanstalk, $g_used_tube, $g_master_tube;

    if ($g_used_tube != $g_master_tube) {
        $pheanstalk->useTube($g_master_tube);
        $g_used_tube = $g_master_tube;
    }

    if ($validity)
        $msg = json_encode(array('handle' => $handle, 'data' => $data, 'validity' => time() + $validity));
    else
        $msg = json_encode(array('handle' => $handle, 'data' => $data));

    return $pheanstalk->put($msg, $pri, $delay, $ttr);
}

function putMsg($tube, $data, $ttr = 600, $pri = 1024, $delay = 0) {
    global $pheanstalk, $g_used_tube;
    try {
        if ($g_used_tube != $tube) {
            $pheanstalk->useTube($tube);
            $g_used_tube = $tube;
        }

        $msg = json_encode($data);
        $pheanstalk->put($msg, $pri, $delay, $ttr);
    } catch (Pheanstalk_Exception $e) {
        logx('Pheanstalk put2 Failed: ' . $e->getMessage());
        return false;
    }

    return true;
}

function execInBg($cmd) {
    global $g_os_win;
    if ($g_os_win) {
        $WshShell = new COM("WScript.Shell");
        $oExec    = $WshShell->Run("$cmd", 7, false);
    } else
        exec("$cmd >/dev/null &");
}

function openTask($proc_num, $tube, $handle, $data, $ttr = 60, $pri = 1024, $delay = 0) {
    global $pheanstalk, $g_used_tube;
    try {
        $old_tube = NULL;
        if ($g_used_tube != $tube) {
            $pheanstalk->useTube($tube);
            $old_tube    = $g_used_tube;
            $g_used_tube = $tube;
        }

        $msg = json_encode(array('handle' => $handle, 'data' => $data));
        $pheanstalk->put($msg, $pri, $delay, $ttr);

        if (!defined('PHP_BINARY'))
            $cmd = "php " . ROOT_DIR . "/watcher.php $tube $proc_num";
        else
            $cmd = PHP_BINARY . " " . ROOT_DIR . "/watcher.php $tube $proc_num";

        execInBg($cmd);

        if ($old_tube) {
            $pheanstalk->useTube($old_tube);
            $g_used_tube = $old_tube;
        } else {
            $pheanstalk->useTube('default');
        }

        logx("cmd $cmd");
        logx("new_task $tube \t" . print_r($data, true));

        return true;
    } catch (Pheanstalk_Exception $e) {
        logx('openTask Failed: ' . $e->getMessage());
        return false;
    }

    return true;
}

/*function closeTask($tube)
{
	global $pheanstalk, $g_used_tube, $g_master_tube;
	try
	{
		if($g_used_tube == $tube)
		{
			$pheanstalk->useTube($g_master_tube);
			$g_used_tube = $g_master_tube;
		}
		
		$pheanstalk->ignore($tube);
		
		$list = $pheanstalk->listTubes();
		var_dump($list);
	}
	catch(Pheanstalk_Exception $e)
	{
		logx('closeTask Failed: ' . $e->getMessage());
		return false;
	}
	
	return true;
}*/

function lockPid() {
    global $g_master_tube, $lock_pid;

    $lock_pid = fopen(ROOT_DIR . "/pids/$g_master_tube.pid", 'a+');
    if ($lock_pid) {
        if (flock($lock_pid, LOCK_EX | LOCK_NB)) {
            ftruncate($lock_pid, 0);
            fwrite($lock_pid, getmypid());
            fflush($lock_pid);
            return;
        }

        fclose($lock_pid);
    }

    logx("Pid $g_master_tube Exists!!!");
    notifyQuit();
}

$pos = strpos($g_master_tube, '.');
if ($pos === FALSE)
    $module = $g_master_tube;
else
    $module = substr($g_master_tube, 0, $pos);

if (isset($g_tube_mod_map[ $module ]))
    $module = $g_tube_mod_map[ $module ];

require_once(ROOT_DIR . "/Manager/{$module}Manager.class.php");
//引用对应的Manager文件之后，受限调用该类的注册方法
$name = "\\Platform\\Manager\\{$module}Manager";
$name::register();

//������
$is_master = false;
//������������
if (count($argv) > 3) {
    $is_master  = true;
    $task_count = intval($argv[3]) - 1;

    $kicked = $pheanstalk->kick(1000000);
    resetAlarm();
    $has_task = call_user_func(array($name, "{$module}_main"), $kicked);
    resetAlarm();

    if ($has_task || $kicked > 0) {
        if (!defined('PHP_BINARY'))
            $cmd = "php " . ROOT_DIR . "/Task/worker.php $g_master_tube $g_task_id";
        else
            $cmd = PHP_BINARY . " " . ROOT_DIR . "/Task/worker.php $g_master_tube $g_task_id";

        for ($i = 0; $i < $task_count; $i++) {
            execInBg($cmd);
        }
    }
}

$g_task_count      = 0;
$g_task_begin_time = time();
$g_fail_count      = 0;
$g_cur_job         = NULL;

function deleteJob() {
    global $g_cur_job, $pheanstalk;
    if ($g_cur_job) {
        try {
            $pheanstalk->delete($g_cur_job);
            $g_cur_job = NULL;
            return TRUE;
        } catch (Pheanstalk_Exception $e) {
            logx('deleteJob Failed: ' . $e->getMessage(),'','error');
        }
    }
    return FALSE;
}

while (true) {

    try {
        resetAlarm();
        $g_cur_job = $pheanstalk->reserve();
        resetAlarm();
    } catch (Pheanstalk_Exception $e) {
        logx('Pheanstalk reserve Failed: ' . $e->getMessage(),'','error');

        if (!$g_disable_alarm)
            break;

        while (!connectBeanstalk()) sleep(1);
        continue;
    }

    $data = $g_cur_job->getData();
    $obj  = json_decode_safe($data);
    //��Ч�ڼ��?
    if (isset($obj->validity) && $obj->validity) {
        if (time() > $obj->validity) {
            try {
                $pheanstalk->delete($g_cur_job);
                $g_cur_job = NULL;
            } catch (Pheanstalk_Exception $e) {
                logx('Pheanstalk delete Failed: ' . $e->getMessage(),'','error');
                logx("Task_data: $data");
            }
            continue;
        }
    }

    if (isset($obj->handle) && isset($g_handle_map[ $obj->handle ])) {
        ++$g_task_count;
        $result = call_user_func($g_handle_map[ $obj->handle ], $obj->data);
        if (!$g_cur_job)
            continue;

        if ($result > 0) {
            try {
                switch ($result) {
                    case TASK_SUSPEND: {
                        $pheanstalk->bury($g_cur_job);
                        break;
                    }
                    case TASK_RETRY: {
                        sleep(1);
                        $pheanstalk->release($g_cur_job);
                        break;
                    }
                }
            } catch (Pheanstalk_Exception $e) {
                logx('Pheanstalk release Failed: ' . $e->getMessage(),'','error');
                logx("Task_data: $data");
            }
            continue;
        }
    } else {
        if (isset($obj->handle) &&
            empty($obj->handle) &&
            isset($obj->data->before) &&
            @$obj->data->task_id == $g_task_id
        ) //�������?
        {
            //data--> {consumer:n, inc: n, tube:t, before:before}
            $obj->data->inc = intval($obj->data->inc) + 1;
            $complete       = ($obj->data->inc >= $obj->data->consumer);
            $before         = $obj->data->before;

            try {
                if ($g_used_tube != $g_master_tube) {
                    $pheanstalk->useTube($g_master_tube);
                    $g_used_tube = $g_master_tube;
                }

                //ǰ����
                if ($before) {
                    foreach ($g_before_exit_map as $mod => $qp) {
                        call_user_func($qp, $obj->data->tube, $complete);
                    }
                }

                if (!$complete) {
                    $pheanstalk->put(json_encode($obj), 4096);
                }

                if (!$before) {
                    foreach ($g_exit_map as $mod => $qp) {
                        call_user_func($qp, $obj->data->tube, $complete);
                    }

                    if ($g_cur_job)
                        $pheanstalk->delete($g_cur_job);
                    $pheanstalk->ignore($obj->data->tube);

                    break;
                }
            } catch (Pheanstalk_Exception $e) {
                logx('Pheanstalk release Failed: ' . $e->getMessage(),'','error');
                logx("Task_data: $data");
            }
        } else if (isset($obj->handle)) {
            if ($obj->handle != 'nothing') {
                logx("Handle {$obj->handle} not found!");
                logx("Task_data: $data");
            }
        } else {
            logx("InvalidTask: $data",'','error');
        }
    }

    try {
        if ($g_cur_job) {
            $pheanstalk->delete($g_cur_job);
            $g_cur_job = NULL;
        }
    } catch (Pheanstalk_Exception $e) {
        logx('Pheanstalk delete Failed: ' . $e->getMessage(),'','error');
        logx("Task_data: $data");
    }
}

$g_task_begin_time = time() - $g_task_begin_time;
echo "QUIT $g_task_count $g_task_begin_time\n";

?>