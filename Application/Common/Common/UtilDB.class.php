<?php
namespace Common\Common;
/**
 * @author Citying
 * 数据库公共工具类
 */
class UtilDB
{

    /**
     * 获取form表单下拉列表
     * @param array $keys | 根据key值获取对应的下拉列表list
     */
    static public function getCfgList($keys,$where=array())
    {
        $list=array();
        $model_db=M();
        try {
            foreach ($keys as $v)
            {
                switch ($v){
                    case 'shop':
						$disabled = D('Setting/System')->getOneSysteSetting('shop_disabled_search');
                        if(empty($where['shop']) && !$disabled[0]['value'])
                        {
                            $list[$v]=$model_db->table('cfg_shop')->field('shop_id AS id,shop_name AS name')->select();
                        }else
						{
							if(empty($where['shop'])){
								$where['shop'] = array();
							}
							if($disabled[0]['value']){
								$where['shop'] = array_merge($where['shop'],array('is_disabled'=>0));
							}
                            $list[$v]=$model_db->table('cfg_shop')->field('shop_id AS id,shop_name AS name')->where($where['shop'])->select();
                        }
                        break;
                    case 'logistics':
						$disabled = D('Setting/System')->getOneSysteSetting('logistics_disabled_search');
                    	if (empty($where['logistics']) && !$disabled[0]['value'])
                    	{
                    		$list[$v]=$model_db->table('cfg_logistics')->field('logistics_id AS id,logistics_name AS name,bill_type AS type')->select();
                    	}else 
                    	{
							if(empty($where['logistics'])){
								$where['logistics'] = array();
							}
							if($disabled[0]['value']){
								$where['logistics'] = array_merge($where['logistics'],array('is_disabled'=>0));
							}
                    		$list[$v]=$model_db->table('cfg_logistics')->field('logistics_id AS id,logistics_name AS name,bill_type AS type')->where($where['logistics'])->select();
                    	}
                        break;
                    case 'warehouse':
						$disabled = D('Setting/System')->getOneSysteSetting('warehouse_disabled_search');
                    	if(empty($where['warehouse']) && !$disabled[0]['value'])
                    	{
	                        $list[$v]=$model_db->table('cfg_warehouse')->field('warehouse_id AS id,name')->select();
                    	}else
                    	{
							if(empty($where['warehouse'])){
								$where['warehouse'] = array();
							}
							if($disabled[0]['value']){
								$where['warehouse'] = array_merge($where['warehouse'],array('is_disabled'=>0));
							}
                    		$list[$v]=$model_db->table('cfg_warehouse')->field('warehouse_id AS id,name')->where($where['warehouse'])->select();

                    	}
                        break;
					case 'provider':
						$disabled = D('Setting/System')->getOneSysteSetting('provider_disabled_search');
                    	if(empty($where['provider']) && !$disabled[0]['value'])
                    	{
	                        $list[$v]=$model_db->table('purchase_provider')->field('id,provider_name AS name')->select();
                    	}else
                    	{
							if(empty($where['provider'])){
								$where['provider'] = array();
							}
							if($disabled[0]['value']){
								$where['provider'] = array_merge($where['provider'],array('is_disabled'=>0));
							}
                    		$list[$v]=$model_db->table('purchase_provider')->field('id,provider_name AS name')->where($where['provider'])->select();

                    	}
                        break;
                    case 'employee':
                    	$list[$v]=$model_db->table('hr_employee')->field('employee_id AS id,fullname AS name')->select();
                    	break;
                    case 'brand':
                        if(empty($where["brand"])){
                            $list[$v]=$model_db->table('goods_brand')->field('brand_id AS id,brand_name AS name')->where(array('is_disabled'=>array('eq',0)))->select();
                        } else {
                            $list[$v]=$model_db->table('goods_brand')->field('brand_id AS id,brand_name AS name')->where($where["brand"])->select();
                        }
                        break;
                    case 'unit':
                        $list[$v]=$model_db->table('cfg_goods_unit')->field('rec_id AS id, name')->select();;
                        break;
                    case "goods_class":
                        $list[$v] = $model_db->table("goods_class")->field("class_id as id,class_name as name")->select();
                        break;
                    case "reason":
						$disabled = D('Setting/System')->getOneSysteSetting('reason_disabled_search');
                    	if(empty($where['reason']) && !$disabled[0]['value'])
                    	{
	                        $list[$v] = $model_db->table("cfg_oper_reason")->field("reason_id as id,title as name")->select();
                    	}else{
							if(empty($where['reason'])){
								$where['reason'] = array();
							}
							if($disabled[0]['value']){
								$where['reason'] = array_merge($where['reason'],array('is_disabled'=>0));
							}
                    		$list[$v] = $model_db->table("cfg_oper_reason")->field("reason_id as id,title as name")->where($where['reason'])->select();
                    	}
                        break;
					case "sms_template":
						if(empty($where["is_marketing"])){
							$list[$v] = $model_db->table('cfg_sms_template')->field('rec_id AS id,title AS name')->select();
						}else{
							$list[$v] = $model_db->table('cfg_sms_template')->where($where)->field('rec_id AS id,title AS name')->select();
						}
						break;
					case "customer_class":
						$list[$v]=$model_db->table('crm_customer_class')->field('class_id AS id, class_name AS name')->order('class_id asc')->select();
						break;
                }
            }
        } catch (\PDOException $e) {
            \Think\Log::write('UtilDB->getCfgList: '.$e->getMessage());
        }
        return $list;
    }
	static public function getCfgRightList($keys,$where=array())
	{
		$list=array();
		$operator_id = get_operator_id();
		$auth_type = array('shop'=>1,'warehouse'=>2,'provider'=>4);
		$auth_type_key = array('shop'=>'shop_id','warehouse'=>'warehouse_id','provider'=>'id');
		try {
			foreach ($keys as $v) {
				if(!isset($auth_type[$v]))
				{
					continue;
				}
				if ($operator_id > 1) {
					$auth_list = D('Setting/EmployeeRights')->field('GROUP_CONCAT(DISTINCT right_id ORDER BY right_id) list')->where(array('employee_id' => $operator_id, 'type' => $auth_type[$v], 'is_denied' => 0))->find();
					$denied_list = D('Setting/EmployeeRights')->field('GROUP_CONCAT(DISTINCT right_id ORDER BY right_id) list')->where(array('employee_id' => $operator_id, 'type' => $auth_type[$v], 'is_denied' => 1))->find();
					if (!empty($auth_list['list']) || !empty($denied_list['list'])) {
						if(empty($where[$v]))
						{
							$where[$v] = array("{$auth_type_key[$v]}"=>array('in',empty($auth_list['list'])?'0':$auth_list['list']));
						}else{
							$where[$v] = array_merge($where[$v],array("{$auth_type_key[$v]}"=>array('in',empty($auth_list['list'])?'0':$auth_list['list'])));
						}
					}
				}
			}
			$list = self::getCfgList($keys,$where);
		} catch (\PDOException $e) {
			\Think\Log::write('UtilDB->getCfgRightList: '.$e->getMessage());
		}
		return $list;
	}
	 /**
     * @param integer $reason_class 分类:分类:1订单驳回2订单冻结3订单取消4退款原因 5来电类别 6 保修类型 8 保修单冻结原因 9保修结束语 100出库单冻结101出库单取消原因
     * @param bool $set_reason 标识-请求的数据：true-编辑标记，flase-标记数据
     */
    static public function getReasonData($reason_class, $set_reason = false)
    {
    	$reason = array();
    	$where['cor.class_id'] = array('eq', $reason_class);//标记分类
    	try {
    		if ($set_reason) {
    			$data = M('cfg_oper_reason')->alias('cor')->field('cor.reason_id as id,cor.title,cor.is_disabled,cor.priority')->where($where)->order('cor.is_disabled ASC,cor.reason_id ASC')->select();
    			$reason['data'] = $data;
    		} else {
    			$list_reason = array();
    			$where['cor.is_disabled'] = array('eq', 0);//是否停用
    			$where_list['cor.is_builtin'] = array('eq', 0);//是否内置原因
    			
    			$res_list_reason_arr = M('cfg_oper_reason')->alias('cor')->field('cor.reason_id as id,cor.title as name')->where(array_merge($where, $where_list))->select();
    			
    			$default_reason[] = array('id' => 0, 'name' => '无','select'=>true);//原因的下拉列表
    			$list_reason = array_merge($default_reason,$res_list_reason_arr);
    			$reason['list'] = $list_reason;
    		}
    		
    	} catch (\PDOException $e) {
    		\Think\Log::write('UtilDB->getReasonData: ' . $e->getMessage());
    	}
    	
    	return $reason;
    }
    /**
     * 查看号码
     * @param array $arr_ids_data
     * @param string $key
     * @param integer $user_id
     * @return multitype:|multitype:\Think\mixed
     */
    static public function checkNumber($arr_ids_data,$key,$user_id,$success=array(),$type=1)
    {
    	$keys=array(
    			'sales_trade'=>array('model'=>'sales_trade','pk'=>'trade_id','field'=>'trade_id as id,trade_no,receiver_mobile,receiver_telno','log'=>'sales_trade_log'),
    			'stockout_order'=>array('model'=>'stockout_order','pk'=>'stockout_id','field'=>'stockout_id as id,src_order_id as trade_id,stockout_no,receiver_mobile,receiver_telno','log'=>'sales_trade_log'),
    			'api_trade'=>array('model'=>'api_trade','pk'=>'rec_id','field'=>'rec_id as id,tid,deliver_trade_id as trade_id,tid,receiver_mobile,receiver_telno','log'=>'sys_other_log'),
    			'sales_refund'=>array('model'=>'sales_refund','pk'=>'refund_id','field'=>'refund_id as id,refund_no,return_mobile,return_telno','log'=>'sales_refund_log'),
    			'crm_customer'=>array('model'=>'crm_customer','pk'=>'customer_id','field'=>'customer_id as id,nickname,mobile,telno','log'=>'crm_customer_log'), 
                'api_trade_history'=>array('model'=>'api_trade_history','pk'=>'rec_id','field'=>'rec_id as id,tid,deliver_trade_id as trade_id,tid,receiver_mobile,receiver_telno','log'=>'sys_other_log'),
                'stockout_order_history'=>array('model'=>'stockout_order_history','pk'=>'stockout_id','field'=>'stockout_id as id,src_order_id as trade_id,stockout_no,receiver_mobile,receiver_telno','log'=>'sales_trade_log'),

    	);
    	$result=array();
        $list=array();//存放错误数据
    	if(empty($keys[$key]))
    	{
    		return $result;
    	}
    	try {
    		$employee=M('hr_employee')->field('last_login_ip,field_rights,roles_mask AS role')->where(array('employee_id'=>array('eq',$user_id)))->find();
    		//号码权限判断
    		if($employee['field_rights']==0 && $employee['role']==0)
    		{
    			if($type==0)
    			{
	    			E('您暂无此操作权限，请联系管理员。');
    			}else if ($type==1)
    			{
    				return false;
    			}
    		}
    		if($type==2 && $employee['field_rights']==0 && $employee['role']==0)
    		{
    			return false;
    		}else if($type==2){
    			return true;
    		}
    		//日志写入和数据整理
    		if(empty($success))
    		{
		    	$success=M($keys[$key]['model'])->field($keys[$key]['field'])->where(array($keys[$key]['pk']=>array('in',$arr_ids_data)))->select();
    		}
    		$logs=array();
    		foreach ($success as $s)
    		{
    			 if($keys[$key]['log']=='sales_trade_log')
    			 {
    			 	if($s['receiver_mobile']==''&&$s['receiver_telno']==''){
                        		$list[]=array('trade_no'=>$s['trade_no'],'result_info'=>'手机和固话均为空！');
                        		continue;
                    		}
                    		$log=array(
    			 		'sales_trade'=>array('id'=>$s['id'],'message'=>'登录IP：'.$employee['last_login_ip'].' 查看了 订单-'.$s['trade_no'].' 的号码'),
    			 		'stockout_order'=>array('id'=>$s['trade_id'],'message'=>'登录IP：'.$employee['last_login_ip'].' 查看了 出库单-'.$s['stockout_no'].' 的号码'),
                        		'stockout_order_history'=>array('id'=>$s['trade_id'],'message'=>'登录IP：'.$employee['last_login_ip'].' 查看了 历史销售出库单-'.$s['stockout_no'].' 的号码'),
    			 	);
    			 	$logs[]=array(
						'trade_id'=>$log[$keys[$key]['model']]['id'],
						'operator_id'=>$user_id,
						'type'=>110,
						'message'=>$log[$keys[$key]['model']]['message'],
    			 		'created'=>date('y-m-d H:i:s',time()),
					);
    			 }else if($keys[$key]['log']=='sales_refund_log')
    			 {
    			 	$logs[]=array(
    			 			'refund_id'=>$s['id'],
    			 			'operator_id'=>$user_id,
    			 			'type'=>10,
    			 			'remark'=>'登录IP：'.$employee['last_login_ip'].' 查看了 退换单-'.$s['refund_no'].' 的号码',
    			 			'created'=>date('y-m-d H:i:s',time()),
    			 	);
    			 }else if($keys[$key]['log']=='crm_customer_log')
    			 {
    			 	$logs[]=array(
    			 			'customer_id'=>$s['id'],
    			 			'operator_id'=>$user_id,
    			 			'type'=>3,
    			 			'message'=>'登录IP：'.$employee['last_login_ip'].' 查看了 网名-'.$s['nickname'].' 的号码',
    			 			'data'=>1,//是否是重复
    			 			'created'=>date('y-m-d H:i:s',time()),
    			 	);
    			 }else if($keys[$key]['log']=='sys_other_log')
				 {
		                    if($s['receiver_mobile']==''&&$s['receiver_telno']==''){
		                        $list[]=array('trade_no'=>$s['tid'],'result_info'=>'手机和固话均为空！');
		                        continue;
		                    }
					$logs[]=array(
						 'type' => 17,
						 'operator_id' => $user_id,
						 'data' => $s['id'],
						 'message' => '登录IP：'.$employee['last_login_ip'].' 查看了 原始订单-'.$s['tid'].' 的号码',
						 'created' => date('Y-m-d H:i:s')
					 );
				 }
    		}
            if(!empty($logs)){
                M($keys[$key]['log'])->addAll($logs);
            }
    	} catch (\PDOException $e) {
    		\Think\Log::write('UtilDB->checkNumber: ' . $e->getMessage());
    		E('PDO错误');
    	}
        $result=array(
            'success'   => $success,//成功的数据
            'status'    => empty($list)?0:2,//0全部成功，1异常错误，2部分成功
            'fail'      => $list,//失败提示信息
            );
    	return $result;
    }
	//获取功能权限
	static public function actionRights($parent_id){
		$operator_id = get_operator_id();
		$right = M('dict_url')->alias('du')->fetchSql(false)->field('du.action')->join('left join cfg_employee_rights cef on du.url_id = cef.right_id ')->where(array('cef.is_denied'=>0,'cef.employee_id'=>$operator_id,'du.parent_id'=>$parent_id))->select();
		if(empty($right)){
			$employee_data = D('Setting/EmployeeRights')->field('rec_id')->where(array('right_id'=>$parent_id,'employee_id'=>$operator_id,'is_denied'=>0))->select();
			if(!empty($employee_data)){	
				$right = M('dict_url')->field('action')->where(array('parent_id'=>$parent_id))->select();
			}
		}
		return $right;
	} 
	
}

?>
