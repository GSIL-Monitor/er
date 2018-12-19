<?php
namespace Help\Model;

use Think\Model;
use Think\Exception;
class ImplementCleanModel extends Model{
	protected $tableName="cfg_setting";
	protected $pk="key";
	
	public function implementClean($type,$user_id){
		$result=array();
		$sys_log=array();
		try{
			$sys_init=get_config_value('sys_init',0);
			$role=D('Setting/Employee')->getRole($user_id);
			if($sys_init==0){
				$result['status']=1;
				$result['info']="实施助手未开启。";
			}else if($role<2){
				$result['status']=1;
				$result['info']="非admin用户不能使用实施工具。";
			}else{
				$log_message=array(
				    1=>'全清(清除：货品、订单、档口、采购、库存、客户、员工、账款、统计相关)',
					2=>'清除货品信息(清除：货品、订单、档口、采购、库存、账款、统计相关，保留:客户、员工相关）',
					3=>'清除客户资料(清除：客户、订单、档口、库存、账款、统计相关及采购单等信息，保留：货品、员工相关及供应商、供应商货品等信息)',
					4=>'清除员工资料(清除：员工、订单、档口、库存、账款、统计相关及采购单等信息，保留：货品、客户相关及供应商、供应商货品等信息)',
					5=>'保留库存、货品、员工、客户信息（清除：订单、档口、账款、统计相关及采购单等信息,库存只保留库存量，保留：供应商、供应商货品）',
					6=>'',
					7=>'清除客户营销信息(清除：短信策略、模板)',
					8=>'清除订单信息(清除：订单、档口、账款、统计相关信息，与订单相关的出库单、入库单的类型将变为其他出库、其他入库)',
					9=>'系统恢复初始化状态',
					);
				$sys_log[]=array(
						'type'=>13,
						'operator_id'=>$user_id,
						'data'=>1,
						'message'=>'【开始清理】'.$log_message[$type],
						'created'=>date('Y-m-d H:i:s'),
				);
				$sql="CALL SP_IMPLEMENT_CLEAN(".$type.");";
				set_time_limit(1800);//实施助手用时较长，暂设置超时时间为30分钟。
				$this->execute("CALL SP_IMPLEMENT_CLEAN(".$type.");");
				$sys_log[]=array(
						'type'=>13,
						'operator_id'=>$user_id,
						'data'=>1,
						'message'=>'【结束清理】'.$log_message[$type],
						'created'=>date('Y-m-d H:i:s'),
				);
				M('sys_other_log')->addAll($sys_log);
				$result['status']=0;
				$result['info']='清理成功';
			}
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			$result['status']=1;
			$result['info']=self::PDO_ERROR;
		}catch (Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
		}
		return $result;
	}
}