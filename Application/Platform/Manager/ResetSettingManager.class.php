<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/ResetSetting/CfgSetting.class.php");

class ResetSettingManager extends Manager{
	
	public static function register() {
		registerHandle("reset_setting_task", array("\\Platform\\Manager\\ResetSettingManager", "listResetSetting"));
		registerHandle("reset_cfg_setting", array("\\Platform\\Manager\\ResetSettingManager", "ResetCfgSetting"));
	}
	
	public static function ResetSetting_main() {
		return enumAllMerchant('reset_setting_task');
	}
	
    public static function listResetSetting($sid) {
        //删除该任务
        deleteJob();
        $db = getUserDb($sid);
        //加入任务队列
        pushTask("reset_cfg_setting", $sid, 0, 1024, 600, 300);

        releaseDb($db);
    }
    
    public static function ResetCfgSetting($sid){
    	//删除该任务
    	deleteJob();
    	//获取数据库连接
    	$db = getUserDb($sid);
    	//获取链接失败，记录错误日志
    	if (!$db) {
    		logx("getUserDb failed in resetCfgSetting!!", $sid . "/ResetSetting");
    		return TASK_OK;
    	}
    	ResetCfgSetting($db, $sid);
    	releaseDb($db);
    }
}