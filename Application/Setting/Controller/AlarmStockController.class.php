<?php
/**
 * Created by PhpStorm.
 * User: changtao
 * Date: 2016/11/29
 * Time: 14:20
 */

namespace Setting\Controller;


use Common\Controller\BaseController;
use Platform\Manager\AlarmStockManager;
use Think\Exception;
use Think\Exception\BusinessLogicException;

defined('ROOT_DIR') || define('ROOT_DIR', APP_PATH.'Platform');
include_once (APP_PATH ."Platform/Common/ManagerFactory.class.php");
include_once (APP_PATH .'Platform/Common/utils.php');
include_once (APP_PATH .'Platform/Manager/utils.php');
include_once (APP_PATH."Platform/Manager/AlarmStockManager.class.php");
require_once(ROOT_DIR . "/AlarmStock/AlarmStock.php");


class AlarmStockController extends BaseController
{
    public function ShowAlarmStockRule()
    {
        try
        {
            $id_list=array(
                'form'=>'alarmstock_rule_form',
            );
            $rule_data = get_config_value(array('purchase_rate_type','purchase_fixrate_value','purchase_rate_cycle','alarm_stock_days'),array(1,1,7,7));
            $this->assign('rule_data',json_encode($rule_data));
            $this->assign('id_list',$id_list);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_alarmstock_rule');
    }
    public function saveAlarmRule()
    {
        try{
            $result = array('status'=>0,'info'=>'保存成功！');
            $rule_data = I('','',C('JSON_FILTER'));
            D('Setting/AlarmStock')->saveAlarmRule($rule_data);
        }catch(BusinessLogicException $e) {
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(Exception $e)
        {
            \Think\Log::write(CONTORLLER_NAME.'-saveAlarmRule-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    public function refreshAlarmStock()
    {
        try{
            $result= array('status'=>0,'info'=>'更新成功！');
            $rule_data = I('','',C('JSON_FILTER'));
            $sid = get_sid();
            $operator_id = get_operator_id();
            D('Setting/AlarmStock')->saveAlarmRule($rule_data);
            $result = AlarmStockManager::manualRefreshAlarmStock($sid,$operator_id);
            if($result['status'] ==2 ){
                E($result['info']);
            }
        }catch(BusinessLogicException $e) {
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(Exception $e)
        {
            \Think\Log::write(CONTORLLER_NAME.'-refreshAlarmStock-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
}