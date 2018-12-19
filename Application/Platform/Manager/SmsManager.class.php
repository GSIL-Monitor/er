<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/SMS/SmsTradeUnpay.php");
require_once(ROOT_DIR . "/SMS/SendSms.php");

class SmsManager extends Manager{
    public static function Sms_main() {
        enumAllMerchant("sms_task");
    }
    public static function smsTask($sid) {
        deleteJob();
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in smsTask!", $sid . "/SMS");
            return false;
        }
        $sys_sms_rule = getSysCfg($db,'cfg_open_message_strategy',0);
        if($sys_sms_rule){
            //获取催未付款订单
            logx("$sid 短信策略",$sid.'/SMS');
            pushTask("crm_sms_trade_unpay", $sid, 0, 1024, 600, 300);
            pushTask("send_sms", $sid, 0, 1024, 600, 300);
        }else{
            logx("$sid 未开启使用短信策略",$sid.'/SMS');
        }
        releaseDb($db);
    }

    public static function smsTradeUnpay($sid){
        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getUserDb failed in smsTradeUnpay", $sid . "/SMS");
            return false;
        }
        crm_sms_trade_unpay($db,$sid);
        releaseDb($db);
        return TASK_OK;
    }

    public static function sendSms($sid){
        logx("$sid 发送短信",$sid.'/SMS');
        sendSms($sid,'',$msg);
        return TASK_OK;
    }

    public static function manualGetBalance($sid){
        $res = get_balance($sid);
        return $res;
    }

    public static function register() {
        registerHandle("sms_merchant", array("\\Platform\\Manager\\SmsManager", "Sms_main"));
        registerHandle("sms_task", array("\\Platform\\Manager\\SmsManager", "smsTask"));
        registerHandle("crm_sms_trade_unpay", array("\\Platform\\Manager\\SmsManager", "smsTradeUnpay"));
        registerHandle("send_sms", array("\\Platform\\Manager\\SmsManager", "sendSms"));

    }
}

