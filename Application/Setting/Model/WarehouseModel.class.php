<?php
namespace Setting\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/27/15
 * Time: 15:27
 */
/*
self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
self::MUST_VALIDATE 或者1 必须验证
self::VALUE_VALIDATE或者2 值不为空的时候验证
*/
/*
self::MODEL_INSERT或者1新增数据时候验证
self::MODEL_UPDATE或者2编辑数据时候验证
self::MODEL_BOTH或者3全部情况下验证（默认）
*/

class WarehouseModel extends Model {
    protected $tableName = 'cfg_warehouse';
    protected $pk = 'warehouse_id';
    protected  $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('type',array(1,2,3,11,127),'仓库类型错误!',1,'in',3),
        array('is_disabled',array(1,0),'是否停用状态错误!',1,'in',3),
        array('is_defect',array(1,0),'是否是残次品库类型错误!',1,'in',3),
        array('name','checkName','请不要包括特殊字符,如:\\,/,[,],%,`,~,<,>等',0,'callback',3),
        array('name','','仓库名重复，请重新填写!！',0,'unique',3), // 在新增的时候验证name字段是否唯一
        array('province,city,district,division_id','checkAddress','省、市、区县填写不一致',1,'callback',3), // 区县不能为空
//        array('division_id',0,'请填写完整地址',1,'notequal',3), // 区县不能为空

    );
    protected  function checkName($warehouse_name)
    {
        return !check_regex('specialcharacter',$warehouse_name);
    }
    protected function checkAddress($address_info)//$address_info 是验证规则的0的字段分割以后的数组
    {
        try{
            if($address_info['division_id']==0 || empty($address_info['division_id'])){
                $sql = "select dp.name province_name , dc.name city_name  from dict_city dc  left join dict_province dp on dp.province_id = dc.province_id where dc.name = '%s'";
                $res = $this->query($sql,$address_info['city']);
                if(empty($res)){
                    return false;
                }
                if($address_info['province'] == $res[0]['province_name']){
                    return true;
                }else{
                    return false;
                }
            }else{
                $sql = "select dp.name province_name , dc.name city_name , dd.name district_name from dict_district dd left join dict_city dc on dc.city_id = dd.city_id left join dict_province dp on dp.province_id = dc.province_id where dd.district_id = %d";
                $res = $this->query($sql,$address_info['division_id']);
                if(empty($res)){
                    return false;
                }
                if($address_info['province'] == $res[0]['province_name'] && $address_info['city'] == $res[0]['city_name'] && $address_info['district'] == $res[0]['district_name']){
                    return true;
                }else{
                    return false;
                }
            }
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return false;
        }
    }
    public function search($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        $where_limit = array();
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case 'name':
                    set_search_form_value($where_limit, $k, $v, '', 1);
                    break;
                case 'type':
                    set_search_form_value($where_limit, $k, $v, '', 2);
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
            $fields = array("warehouse_id"=>"id","name","type","address","contact","zip","mobile","telno","ext_warehouse_no","is_defect","is_disabled","province","city","district","division_id","remark","api_key");
            $total  = $this->where($where_limit)->count();
            $list   = $this->field($fields)->where($where_limit)->page($page,$rows)->order($order)->select();
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            $msg = $e->getMessage() ;
            \Think\Log::write($this->name.'-search-'.$msg);
           SE(self::PDO_ERROR);
        } catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write($this->name.'--search--'.$msg);
			SE(self::PDO_ERROR);
		}

        return $data;
    }
    public function getEditWarehouseInfo($id)
    {
        try {
            $conditions = array("warehouse_id" => $id);
            $fields = array('warehouse_id','name','type','division_id','province','city','district','address','contact','zip','mobile','telno','is_defect','is_disabled','remark');
            $res = $this->getWarehouseList($fields,$conditions);
            if(empty($res)){
                SE('查询不到仓库信息');
            }else{
                $res = $res[0];
            }
        } catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            SE($msg);
        } catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
        return $res;
    }
    function saveWarehouse($warehouse_info)
    {
		
        $data['status'] = 0;
        $data['info'] = 'success';

        //地址拼接
        $address_names = $warehouse_info['address_names'];
        $address_codes = $warehouse_info['address_codes'];

        $update_data = $warehouse_info;
        $update_data['province']      = $address_names['province'];
        $update_data['city']          = $address_names['city'];
        $update_data['district']      = $address_names['district'];
        $update_data['division_id']   = $address_codes['district'];
        $update_data['tag'] = array('exp','UNIX_TIMESTAMP()');
		
		$oldWarehouse=$this->getWarehouseById($warehouse_info['warehouse_id']);
		
        if(!$this->create($update_data)){
            $data['status'] = 1;
            $data['info'] = $this->getError();
            return $data;
        }
        try {
            $this->startTrans();
            if($warehouse_info['warehouse_id'] == 0){
                $warehouse_id=$this->add($update_data);
				$arr_sys_other_log = array(
						"type"        => "19",
						"operator_id" => get_operator_id(),
						"data"        => $warehouse_id,
						"message"     => "创建仓库--仓库名称--“" . $warehouse_info["name"].'”',
						"careted"     => date("Y-m-d G:i:s")
				);
				$zone_data=array(
					'type'=>1,
					'warehouse_id'=>$warehouse_id,
					'zone_no'=>'ZC',
					'name'=>'暂存',
					'is_disabled'=>0,
					'created'=> date("Y-m-d H:i:s", time()),
				);
				$zone_id=D('Setting/WarehouseZone')->insertZone($zone_data);
				$position_data=array(
					'rec_id'=>-$warehouse_id,
					'warehouse_id'=>$warehouse_id,
					'zone_id'=>$zone_id,
					'position_no'=>'暂存',
					'is_disabled'=>0,
					'created'=> date("Y-m-d H:i:s", time()),
				);
				M("sys_other_log")->data($arr_sys_other_log)->add();
				$position_id=D('Setting/WarehousePosition')->insertPosition($position_data);	
				}else {
					$warehouseId=$this->save($update_data);
					
					$arr_sys_other_log=array();
					
					if($oldWarehouse['name']!= $warehouse_info['name']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--仓库名称--从“' . $oldWarehouse["name"] .'”  到  “'. $warehouse_info["name"].'”'
                            );
					}
					if($oldWarehouse['type']!= $warehouse_info['type']){
                        if($oldWarehouse["type"]==1){
                            $oldWarehousetype='普通仓库';
                        }else if($oldWarehouse["type"]==11){
                            $oldWarehousetype='奇门仓储';
                        }
                        if($warehouse_info["type"]==1){
                            $Warehousetype='普通仓库';
                        }else if($warehouse_info["type"]==11){
                            $Warehousetype='奇门仓储';
                        }
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--仓库类别--从“' . $oldWarehousetype .'”  到  “'. $Warehousetype.'”'
                            );
					}
					if($oldWarehouse['mobile']!= $warehouse_info['mobile']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--手机号码--从“' . $oldWarehouse["mobile"] .'”  到  “'. $warehouse_info["mobile"].'”'
                            );
					}
					if($oldWarehouse['telno']!= $warehouse_info['telno']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--固话号码--从“' . $oldWarehouse["telno"] .'”  到  “'. $warehouse_info["telno"].'”'
                            );
					}
					if($oldWarehouse['remark']!= $warehouse_info['remark']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--备注--从“' . $oldWarehouse["remark"] .'”  到  “'. $warehouse_info["remark"].'”'
                            );
					}
					if($oldWarehouse['contact']!= $warehouse_info['contact']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--联系人--从“' . $oldWarehouse["contact"] .'”  到  “'. $warehouse_info["contact"].'”'
                            );
					}
					if($oldWarehouse['zip']!= $warehouse_info['zip']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--邮编--从“' . $oldWarehouse["zip"] .'”  到  “'. $warehouse_info["zip"].'”'
                            );
					}
					if(($oldWarehouse['province']!=$warehouse_info['address_names']['province'])||($oldWarehouse['city']!=$warehouse_info['address_names']['city'])||($oldWarehouse['district']!=$warehouse_info['address_names']['district'])){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--省市区--从“' . $oldWarehouse['province'] .' '. $oldWarehouse['city'] .' '.$oldWarehouse['district'] .'” 到 “'. $warehouse_info['address_names']['province'].' '. $warehouse_info['address_names']['city'].' '. $warehouse_info['address_names']['district'].'”'
                            );

					}
					if($oldWarehouse['address']!=$warehouse_info['address']){
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--地址--从“' . $oldWarehouse["address"] .'” 到 “'. $warehouse_info["address"].'”'
                            );
					}
					if($oldWarehouse['is_disabled']!= $warehouse_info['is_disabled']){
						if($oldWarehouse["is_disabled"]=='0'){
							$old_is_disabled="否";
						}else{
							$old_is_disabled="是";
						}
						if($warehouse_info["is_disabled"]=='0'){
							$is_disabled="否";
						}else{
							$is_disabled="是";
						}
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--停用--从“' . $old_is_disabled .'”  到  “'. $is_disabled.'”'
                            );
					}
					if($oldWarehouse['is_defect']!= $warehouse_info['is_defect']){
						if($oldWarehouse["is_defect"]=='0'){
							$old_is_defect="否";
						}else{
							$old_is_defect="是";
						}
						if($warehouse_info["is_defect"]=='0'){
							$is_defect="否";
						}else{
							$is_defect="是";
						}
                        $arr_sys_other_log[]=array(
                            'type'=>"19",
                            'operator_id'=>get_operator_id(),
                            'careted'=>date("Y-m-d G:i:s"),
                            'data'=>$warehouseId,
                            'message' =>'编辑仓库--残次品库--从“' . $old_is_defect .'”  到  “'. $is_defect.'”'
                            );
					}
                    M("sys_other_log")->addall($arr_sys_other_log);
				}
			
            $this->commit();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-saveWarehouse-'.$msg);
            $data['status'] = 1;
            $data['info'] = self::PDO_ERROR;
            $this->rollback();
        } catch(BusinessLogicException $e){
			$data['status'] = 1;
            $data['info'] = $e->getMessage();
			$this->rollback();
		} catch(\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-saveWarehouse-'.$msg);
            $data['status'] = 1;
            $data['info'] = self::PDO_ERROR;
            $this->rollback();
        }

        return $data;
    }

	public function checkWarehouse($value,$key='warehouse_id')
    {
    	try {
    		$map[$key]=$value;
    		$result=$this->field('warehouse_id')->where($map)->find();
    		if(!empty($result))
    		{
    			return true;
    		}
    	} catch (\PDOException $e) {
    		\Think\Log::write($e->getMessage());
    	}
    	return false;
    }
    public function getWarehouseList($fields,$condtions = array())
    {
        try {
            $result = $this->field($fields)->where($condtions)->select();
            return $result;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
    }
	public function getWarehouseById($warehouse_id){
        $res = array();
        try{
             $res=$this->find($warehouse_id);			
		}catch (\PDOException $e){
            \Think\Log::write('getWarehouseById SQL ERR'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('getWarehouseById'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
	
}