<?php
define("ROOT_DIR", dirname(__DIR__));
define("TOP_SDK_DIR", ROOT_DIR . "/SDK");

require_once('config.php');
require_once(ROOT_DIR . "/Common/utils.php");
require_once(TOP_SDK_DIR . "/Pheanstalk/Pheanstalk_init.php");

$tube = (count($argv) > 1 ? $argv[1] : 'default');

try
{
	global $g_bt_config;
	$pheanstalk = new Pheanstalk($g_bt_config['host'], $g_bt_config['port'], $g_bt_config['connect_timeout']);

	$pheanstalk->useTube($tube);
	$pheanstalk->watch($tube);
	$pheanstalk->kick(1000000);
	
	/*while(true)
	{
		$job = $pheanstalk->reserve();
		$pheanstalk->delete($job);
	}*/
	exit();
}
catch(Pheanstalk_Exception $e)
{
	echo('Pheanstalk START Failed: ' . $e->getMessage());
	exit();
}


?>