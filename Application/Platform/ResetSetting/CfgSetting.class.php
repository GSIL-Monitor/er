<?php
function ResetCfgSetting(&$db, $sid, &$msg = ""){
	$cfg = getSysCfg($db, "alarm_not_prompt_today", 0);
	if($cfg==1){
		try{
			$sql="UPDATE cfg_setting SET `value`=0 WHERE `key`='alarm_not_prompt_today'";
			$db->execute($sql);
			logx("重置“当天不再弹窗提示余额不足”配置成功", $sid . "/ResetSetting");
		}catch (\Exception $e){
			$msg = $e->getMessage();
			logx($msg, $sid . "/SalesAmountStat");
			logx("重置“当天不再弹窗提示余额不足”配置失败", $sid . "/ResetSetting");
		}
	}else{
		return true;
	}
}