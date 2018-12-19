<?php
namespace Setting\Model;

use Think\Exception;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;
use Common\Common\UtilDB;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/27/15
 * Time: 10:47
 */

class LogisticsModel extends Model {
    protected $tableName = 'cfg_logistics';
    protected $pk = 'logistics_id';
    protected $searchArray = array('logistics_id'=>'id',  'logistics_name', 'logistics_type', 'contact', 'telno', 'address', 'bill_type','is_manual', 'is_disabled', 'remark','is_support_cod','app_key','IF(LOCATE("shop_id",app_key),1,0)'=>'is_authorized');//'logistics_no',
    protected  $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('logistics_name','checkName','请不要包括特殊字符,如:\\,/,[,],%,`,~,<,>等',0,'callback',3),
    );
    protected  function checkName($logistics_name)
    {
        return !check_regex('specialexceptbracket',$logistics_name);
    }
    public function searchLogistics($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc'){
        $page  = intval($page);
        $rows  = intval($rows);
        $sort  = addslashes($sort);
        $order = addslashes($order);
        $where_limit = array();
        foreach ($search as $k => $v) {
            if ($v==='') continue;
            switch ($k) {
                case 'logistics_name':
                    set_search_form_value($where_limit, $k, $v, '', 1);
                    break;
                default:
                    break;
            }
        }
        if($search['show_disabled']!=1){
        	$where_limit['is_disabled']=array('eq','0');
        }
        $page = intval($page);
        $rows = intval($rows);
        $order = $sort . ' ' . $order;//排序
        $order = addslashes($order);
        try {
            $total = $this->where($where_limit)->count();
            $list = $this->fetchSql(false)->field($this->searchArray)->where($where_limit)->page($page, $rows)->order($order)->select();
		} catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
			 $data = array('total' => 0, 'rows' => array());
            return 1;
        }catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
        $data = array('total' => $total, 'rows' => $list);
        return $data;
    }

    public function loadSelectedData($id){
        $id = intval($id);
        try {
            $re = $this->fetchSql(false)->where("logistics_id = $id")->field($this->searchArray)->select();
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            return 0;
        } catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
        if(count($re) == 1){
            return $re;
        }else{
            return 0;
        }
    }

    public function saveData($arr,&$error_list){
		$oldlogistics = $this->getLogisticsById($arr['id']);
        try{
            $re = $this->fetchSql(false)->where(array('logistics_name'=> $arr['logistics_name'] ))->select();// or logistics_no =\"" . $arr['logistics_no'] . "\"
        }catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }

        if($arr['id'] != ""){
            if (!empty($re)) {
                if($arr['id'] != $re[0]['logistics_id'] || 1 != count($re)){
                    SE('物流名重复，请重新填写!');
                }
            }
        }
        if(!$this->create($arr)){
            SE($this->getError());
        }
        try {
            if("" == $arr['id']){
                if (!empty($re)) {
                    SE("物流名重复，请重新填写!");
                }
                $logistics_id=$this->fetchSql(false)->data($arr)->add();
                $data['type'] = "add";
				$arr_sys_other_log = array(
						"type"        => "21",
						"operator_id" => get_operator_id(),
						"data"        => $logistics_id,
						"message"     => "创建物流--物流名称--“" . $arr["logistics_name"].'”',
						"careted"     => date("Y-m-d G:i:s")
			    );
				M("sys_other_log")->data($arr_sys_other_log)->add();
                if($logistics_id){
                    $this->uponLogistics($logistics_id,$error_list);
                }

            }else{
                $logisticsId=$this->fetchSql(false)->where(array('logistics_id'=>$arr['id']))->save($arr);
                $data['type'] = "edit";
				$arr_sys_other_log=array();
				
				if($oldlogistics['logistics_name']!= $arr['logistics_name']){
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--物流名称--从“' . $oldlogistics["logistics_name"] .'”  到  “'. $arr["logistics_name"].'”'
                        );
				}
				if($oldlogistics['contact']!= $arr['contact']){
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--联系人--从“' . $oldlogistics["contact"] .'”  到  “'. $arr["contact"].'”'
                        );
				}
				if($oldlogistics['address']!= $arr['address']){
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--地址--从“' . $oldlogistics["address"] .'”  到  “'. $arr["address"].'”'
                        );
				}
				if($oldlogistics['telno']!= $arr['telno']){
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--联系电话--从“' . $oldlogistics["telno"] .'”  到  “'. $arr["telno"].'”'
                        );
				}
				if($oldlogistics['remark']!= $arr['remark']){
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--备注--从“' . $oldlogistics["remark"] .'”  到  “'. $arr["remark"].'”'
                        );
				}
				if($oldlogistics['is_support_cod']!= $arr['is_support_cod']){
					if($oldlogistics["is_support_cod"]=='0'){
						$old_is_support_cod="否";
					}else{
						$old_is_support_cod="是";
					}
					if($arr["is_support_cod"]=='0'){
						$is_support_cod="否";
					}else{
						$is_support_cod="是";
					}
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--支持货到付款--从“' . $old_is_support_cod.'”  到  “'. $is_support_cod.'”'
                        );
				}
				if($oldlogistics['is_disabled']!= $arr['is_disabled']){
					if($oldlogistics["is_disabled"]=='0'){
						$old_is_disabled="否";
					}else{
						$old_is_disabled="是";
					}
					if($arr["is_disabled"]=='0'){
						$is_disabled="否";
					}else{
						$is_disabled="是";
					}
                    $arr_sys_other_log[]=array(
                        'type'=>'21',
                        'operator_id'=>get_operator_id(),
                        'careted'=>date("Y-m-d G:i:s"),
                        'data'=>$logisticsId,
                        'message' =>'编辑物流--停用--从“' . $old_is_disabled .'”  到  “'. $is_disabled.'”'
                        );
				}
                M("sys_other_log")->addall($arr_sys_other_log);
            }
			
        } catch (\PDOException $e) {
            \Think\Log::write( $e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
		}
    }
    
	public function checkLogistics($value,$key='logistics_id')
    {
    	try {
    		$map[$key]=$value;
    		$result=$this->field('logistics_id')->where($map)->find();
    		if(!empty($result))
    		{
    			return true;
    		}
    	} catch (\PDOException $e) {
    		\Think\Log::write($e->getMessage());
    	}
    	return false;
    }
	
	public function getLogisticsInfo($logistics_id)
    {
        try {
            $sql = " SELECT cl.logistics_id,cl.logistics_type,cl.app_key,cl.address,cl.bill_type,cl.is_support_cod,cl.logistics_name"
                    ." FROM cfg_logistics cl"
                    ." WHERE cl.logistics_id= %d";
            $res = $this->query($sql,array($logistics_id));
            
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getLogisticsInfo-'.$msg);
            E(self::PDO_ERROR);
        }
        return $res;
    }
	
	public function get($field, $id = ''){
		if (!empty($id)){
			$condition['logistics_id'] = intval($id);
		}
		try {
			$result = $this->field($field)->where($condition)->select();
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			$result = array();
		}
		return $result;
	}
	public function getPrintAuthInfo($logistics_id)
	{
	    if (!empty($logistics_id)){
	        $condition['logistics_id'] = intval($logistics_id);
	    }
	    try {
	        $logistics_app     = $this->field(array('app_key'))->where($condition)->find();
	        $logistics_app_ar  = json_decode($logistics_app['app_key'],true);
	        $shop_auth_info    = D('Setting/Shop')->field(array('app_key'))->where(array('shop_id'=>$logistics_app_ar['shop_id']))->find();
	        $shop_auth_ar = json_decode($shop_auth_info['app_key'],true);
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'--'.$msg);
	        $shop_auth_ar = array();
	    }
	    return $shop_auth_ar;
	}
	public function getLogisticsById($logistics_id){
        $res = array();
        try{
             $res=$this->find($logistics_id);			
		}catch (\PDOException $e){
            \Think\Log::write('getLogisticsById SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getLogisticsById'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function uponLogistics($id,&$error_list)
    {
        try
        {
            $logistics_type_res = $this->field('logistics_type')->where(array('logistics_id'=>$id))->find();
            $logistics_type = $logistics_type_res['logistics_type'];
            $shop_arr = M('cfg_shop')->select();
            $dict_logistics_name = M('dict_logistics')->field('logistics_name')->where(array('logistics_type'=>$logistics_type))->find();
            $logistics_name = $dict_logistics_name['logistics_name'];
            if(empty($shop_arr)){
                SE('物流映射失败,店铺列表为空,请先新建店铺并授权店铺');
            }

            foreach($shop_arr as $v)
            {
                $shop_id = $v['shop_id'];
                $shop_name = $v['shop_name'];
                $msg    = array("status" => 0, "info" => "下载物流公司失败");
                //如果该店铺未授权或授权失效或者停用跳过
                if($v['auth_state']!=1 || $v['is_disabled']==1){
                    $error_list[] = array('shop_name'=>$shop_name,'info'=>'不是已授权状态或已停用');
                    continue;
                }
                //查询该店铺是否下载了物流公司，如果未下载物流公司先下载物流公司
                $logistics_shop_res = M('cfg_logistics_shop')->where(array('shop_id'=>$shop_id))->select();
                if(empty($logistics_shop_res)){
                    $db = M('api_logistics_shop');
                    $LogisticsManager = ManagerFactory::getManager("LogisticsCompany");
                    $LogisticsManager->manualDownloadShopLogistics($db, get_sid(), "", $shop_id, $msg);
                    if($msg['status']==0){
                        $error_list[] = array('shop_name'=>$shop_name,'info'=>'映射失败,下载物流公司时失败,请联系管理员');
                        continue;
                    }
                }
                //检查该物流类别是否在该仓库有匹配的平台物流,如果没有的话去深度匹配
                $is_match_arr = M('cfg_logistics_shop')->where(array('shop_id'=>$shop_id,'logistics_type'=>$logistics_type))->find();
                //没有自动匹配上的再去截取物流公司名前面双字匹配。还没有匹配行的话可能是需要修改系统内置物流类别名称或者个别平台的返回数据小众化

                if(empty($is_match_arr))
                {
                    $api_logistics_arr = M('api_logistics_shop')->where(array('shop_id' => $shop_id))->select();
                    $count=0;

                    foreach ($api_logistics_arr as $k => $n)
                    {

                        //先根据全名匹配
                        if($n['name'] == $logistics_name){

                            $count++;
                            $data = array('shop_id' => $shop_id, 'logistics_code' => $n['logistics_code'], 'logistics_id' => $n['logistics_id'], 'logistics_name' => $logistics_name, 'logistics_type' => $logistics_type, 'cod_support' => $n['cod_support']);
                            M('cfg_logistics_shop')->add($data);
                            $error_list[] = array('shop_name' => $shop_name, 'info' => '物流映射成功,映射平台物流:'.$n['name']);
                            break;
                        }

                        $match_name = mb_substr($logistics_name, 0, 2,'utf-8');
                        if (mb_strpos($n['name'], $match_name) !==false) {

                            //若平台物流在cfg中已经有匹配，跳过此次匹配
                            $cfg_where['shop_id'] = $shop_id;
                            $cfg_where['logistics_name'] = $n['name'];
                            $cfg_res = M('cfg_logistics_shop')->where($cfg_where)->find();
                            if(!empty($cfg_res)){
                                continue;
                            }

                            $count++;
                            $model = M('cfg_logistics_shop');
                            $data = array('shop_id' => $shop_id, 'logistics_code' => $n['logistics_code'], 'logistics_id' => $n['logistics_id'], 'logistics_name' => $logistics_name, 'logistics_type' => $logistics_type, 'cod_support' => $n['cod_support']);
                            $res = $model->add($data);


                            $error_list[] = array('shop_name' => $shop_name, 'info' => '物流映射成功,映射平台物流:'.$n['name']);
                            break;// 这里 是  物流公司名前面双字 匹配上了,但是分快递、快运、速运、物流这样
                        }
                    }
                    if($count ==0)
                    {
                        $error_list[] = array('shop_name' => $shop_name, 'info' => '映射失败,此店铺下载的物流公司未能找到与该物流类别匹配的物流');
                    }
                }else{
                    //查找出来之前的平台物流名称给出提示。
                    $api_logistics_res = M('api_logistics_shop')->field('name')->where(array('shop_id'=>$is_match_arr['shop_id'],'logistics_code'=>$is_match_arr['logistics_code']))->find();
                    $api_logistics_name = $api_logistics_res['name'];
                    $error_list[] = array('shop_name' => $shop_name, 'info' => '物流映射成功,映射平台物流:'.$api_logistics_name);
                }
            }
        }catch (\PDOException $e)
        {
            \Think\Log::write('uponLogistics SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (BusinessLogicException $e)
        {
            SE($e->getMessage());
        }catch (\Exception $e)
        {
            \Think\Log::write('uponLogistics ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function getUponLogistics($id)
    {
        $data = array('total' => 0, 'rows' => array());
        try{
            //id 为cfg_logistics 表中的logistics_id 需要先去查询出该物流公司的物流类别
            $logistics_type_res = $this->field('logistics_type')->where(array('logistics_id'=>$id))->find();
            $logistics_type = $logistics_type_res['logistics_type'];
            $shop_list        = UtilDB::getCfgRightList(array("shop"));
            $shop             = $shop_list['shop'];
            $list = array();
            foreach($shop as $v)
            {
                $shop_id = $v['id'];
                //要显示平台的物流名称，需要根据物流类别先查找是否在cfg_logistics_shop表中有对应的物流。
                $sys_logistics_res = M('cfg_logistics_shop')->field('logistics_code')->where(array('shop_id'=>$shop_id,'logistics_type'=>$logistics_type))->find();
                if(!$sys_logistics_res){
                    $api_logistics_name = '无';
                }else{
                    $name_res = M('api_logistics_shop')->field('name')->where(array('shop_id'=>$shop_id,'logistics_code'=>$sys_logistics_res['logistics_code']))->find();
                    $api_logistics_name = $name_res['name']==null?'无':$name_res['name'];
                }
                $list[] = array('id'=>$shop_id,'shop_name'=>$v['name'],'api_logistics'=>$api_logistics_name);
            }
            $data = array('total'=>count($list),'rows'=>$list);
        }catch (\PDOException $e)
        {
            \Think\Log::write('getUponLogistics SQL ERR'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('getUponLogistics ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $data;

    }

}
