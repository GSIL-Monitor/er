<?php
namespace Help\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Think\Exception;

class ImplementCleanController extends BaseController {
	public function showImplementClean(){
		$this->display('dialog_implement_clean');
	}
	
	public function implementClean($type){
		$result=array();
		try{
			$result=D('Help/ImplementClean')->implementClean($type,get_operator_id());
		}catch(BusinessLogicException $e){
			$result['status']=1;
			$result['info']=self::UNKNOWN_ERROR;
		}catch (Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			$result['status']=1;
			$result['info']=self::UNKNOWN_ERROR;
		}
		$this->ajaxReturn($result);
	}

	/**
	 * 关闭隐藏实施助手
	 * @return [type] [description]
	 */
	public function hideImCSetting(){
		try{
            $res["status"] = 1;
            $res["info"] = "操作成功";
            $temp = array();
            $temp["key"] = "sys_init";
            $temp["value"] = 0;
            $temp["class"] = "system";
            $temp["value_type"] = 2;
            $temp["log_type"] = 5;
            $result = D('Setting/System')->hideSystemSetting($temp);
			if(!$result){
                $res["status"] = 0;
                $res["info"] = "操作失败";
            }
		} catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = "操作失败";
        }
        $this->ajaxReturn($res);
	}
}