<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/10/29
 * Time: 10:12
 */
namespace Setting\Model;

use Think\Model;

class SystemModel extends Model {

    protected $tableName = "cfg_setting";
    protected $pk        = "key";

    /**
     * 获取系统设置
     * author:luyanfeng
     */
    public function getSystemSetting() {
        try {
            $sql = "SELECT ss.key,ss.value FROM cfg_setting ss WHERE log_type=5";
            $data = $this->query($sql);
            $res = array();
            foreach ($data as $v) {
                $res[ $v["key"] ] = $v["value"];
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array();
        }
        return $res;
    }

    /**
     * 获取某一个特定的设置参数
     * author:luyanfeng
     * @param $name
     * @return array|mixed
     */
    public function getOneSysteSetting($name) {
        try {
            $sql = "SELECT ss.key,ss.value FROM cfg_setting ss WHERE ss.key='%s'";
            $res = $this->query($sql, $name);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array();
        }
        return $res;
    }

    /**
     * @param $data
     * @return array
     * 更新系统设置
     * author:luyanfeng
     */
    public function updateSystemSetting($data) {
        try {
            $operator_id=get_operator_id();
            if($data['key']=='menu'){
                $menu=$data['value'];
                $model=M('cfg_user_data');
                $where=array(
                    'user_id'=>$operator_id,
                    'type'=>2
                );
                $result=$model->where($where)->find();
                if($result){
                    $save['data']=$menu;
                    $model->where($where)->save($save);
                }else{
                    $add=array(
                        'user_id'=>$operator_id,
                        'type'=>2,
                        'data'=>$menu
                    );
                    $model->add($add);
                }
            }else{
                $sql = "SELECT COUNT(1) AS total, value FROM cfg_setting WHERE `key`='%s'";
                $result = $this->query($sql, $data["key"]);
                if($data['value'] == $result[0]["value"]){return true;}
                $result = $result[0];
                $data['modified']=date("Y-m-d G:i:s");
                if ($result['total'] != 0) {
                    $this->data($data)->save();
                } else {
                    $this->data($data)->add();
                }
                if($data["key"]=='dynamic_allocation_box'&&$data['value']==1&&$data['value']!=$result["value"]){
                    $this->execute('DELETE FROM sorting_wall_detail');
                    $this->execute('DELETE FROM cfg_sorting_wall');
                }
                if($data["key"]=='dynamic_allocation_box'&&$data['value']==0&&$data['value']!=$result["value"]){
                    $this->execute('DELETE FROM cfg_dynamic_box');
                    $this->execute('DELETE FROM sorting_wall_detail');
                    $this->execute('DELETE FROM cfg_sorting_wall');
                }
                $bool_value=array(
                        '0'=>'否',
                        '1'=>'是',
                );
                $setting_log=array(
                		'order_preorder_lack_stock'=>array(
                        	'0'=>'关闭',
                        	'1'=>'实际库存-待发货-待审核',
                        	'2'=>'实际库存-待发货-待审核-预订量',
                		),
                		'sys_available_stock'=>array(
                				'0'=>'实际库存',
                				'128'=>'实际库存-待审核量',
                				'512'=>'实际库存-待发货量',
                				'640'=>'实际库存-待审核量-待发货量',
                		),
                        'sys_available_purchase'=>array(
                				'0'  =>'实际库存-警戒库存',
                				'1'  =>'实际库存-警戒库存+采购在途量',
                				'64' =>'实际库存-警戒库存-预订单量',
                				'65' =>'实际库存-警戒库存-预订单量+采购在途量',
                				'128'=>'实际库存-警戒库存-待审核量',
                				'129'=>'实际库存-警戒库存-待审核量+采购在途量',
                				'192'=>'实际库存-警戒库存-待审核量-预订单量',
                				'193'=>'实际库存-警戒库存-待审核量-预订单量+采购在途量',
                                '256'=>'实际库存-警戒库存-未付款量',
                                '257'=>'实际库存-警戒库存-未付款量+采购在途量',
                                '320'=>'实际库存-警戒库存-未付款量-预订单量',
                                '321'=>'实际库存-警戒库存-未付款量-预订单量+采购在途量',
                                '384'=>'实际库存-警戒库存-待审核量-未付款量',
                                '385'=>'实际库存-警戒库存-待审核量-未付款量+采购在途量',
                                '448'=>'实际库存-警戒库存-待审核量-未付款量-预订单量',
                                '449'=>'实际库存-警戒库存-待审核量-未付款量-预订单量+采购在途量',
                                '512'=>'实际库存-警戒库存-待发货量',
                                '513'=>'实际库存-警戒库存-待发货量+采购在途量',
                                '576'=>'实际库存-警戒库存-待发货量-预订单量',
                                '577'=>'实际库存-警戒库存-待发货量-预订单量+采购在途量',
                                '640'=>'实际库存-警戒库存-待审核量-待发货量',
                                '641'=>'实际库存-警戒库存-待审核量-待发货量+采购在途量',
                                '704'=>'实际库存-警戒库存-待审核量-待发货量-预订单量',
                                '705'=>'实际库存-警戒库存-待审核量-待发货量-预订单量+采购在途量',
                                '768'=>'实际库存-警戒库存-待发货量-未付款量',
                                '769'=>'实际库存-警戒库存-待发货量-未付款量+采购在途量',
                                '832'=>'实际库存-警戒库存-待发货量-未付款量-预订单量',
                                '833'=>'实际库存-警戒库存-待发货量-未付款量-预订单量+采购在途量',
                                '896'=>'实际库存-警戒库存-待审核量-待发货量-未付款量',
                                '897'=>'实际库存-警戒库存-待审核量-待发货量-未付款量+采购在途量',
                                '960'=>'实际库存-警戒库存-待审核量-待发货量-未付款量-预订单量',
                                '961'=>'实际库存-警戒库存-待审核量-待发货量-未付款量-预订单量+采购在途量',
                		),
                		'auto_check_time_type'=>array(
                				'0'=>'下单时间',
                				'1'=>'付款时间',
                		),
                		'stockout_sendbill_print_status'=>array(
                				'0'=>'否',
                				'1'=>'是',
                				'3'=>'不限制',
                		),
                		'stockout_logistics_print_status'=>array(
                				'0'=>'否',
                				'1'=>'是',
                				'3'=>'不限制',
                		),
                		'order_auto_merge_mode'=>array(
                				'0'=>'店铺+买家+收件人+地址 ',
                				'1'=>'分组+买家+收件人+地址 ',
                		),
                        'real_price_limit_value'=>array(
                                '0' => '最低价',
                                '1' => '零售价',
                                '2' => '市场价',
                        ),
                        'order_logistics_sync_time'=>array(
                            '1'=>'只发一个子订单即可发货',
                            '2'=>'全部子订单发货才可发货',
                        ),
                );
                $system_setting=C('system_setting');
                switch($data['key']){
                    case 'point_number':
                    case 'goods_match_split_char':
                    case 'order_sync_interval':
                    case 'auto_check_start_time':
                    case 'auto_check_end_time':
                    case 'order_fc_receivable_outnumber':
                    case 'order_check_force_check_pwd':
                    case 'auto_check_max_weight':
                    case 'stock_auto_submit_time':
                    case 'sales_print_time_range':
                    case 'crm_member_send_sms_limit_time':
                    case 'return_agree_auto_remark':
                    case 'return_order_auto_remark':
                    case 'goods_spec_prop1':
                    case 'goods_spec_prop2':
                    case 'goods_spec_prop3':
                    case 'goods_spec_prop4':
                    case 'order_fc_discount':$new_value=$data['value'];$old_value=$result['value'];break;//配置值为数值或者字符
                    case 'sys_available_stock':
                    case 'sys_available_purchase':
                    case 'order_preorder_lack_stock':
                    case 'auto_check_time_type':
                    case 'order_auto_merge_mode':
                    case 'stockout_logistics_print_status':
                    case 'order_logistics_sync_time':
                    case 'real_price_limit_value':$new_value=$setting_log[$data['key']][$data['value']];$old_value=$setting_log[$data['key']][$result['value']];break;
                    case 'stockout_sendbill_print_status':$new_value=$setting_log[$data['key']][$data['value']];$old_value=$setting_log[$data['key']][$result['value']];break;
                    case 'cfg_login_interval':$new_value=$data['value'];$old_value=$result['value'];break;
                    case 'overflow_weight_small':$new_value=$data['value'];$old_value=$result['value'];break;
                    case 'overflow_weight_big':$new_value=$data['value'];$old_value=$result['value'];break;
                    default:$new_value=$bool_value[$data['value']];$old_value=$bool_value[$result['value']];break;//配置值为布尔值
                }
                $arr_other_log = array(
                        "type"        => 5,
                        "operator_id" => $operator_id,
                        "data"        => "",
                        "message"     => "修改了系统设置--" .$system_setting[$data["key"]].'--从“'.$old_value.'”到“'.$new_value.'”',
                        "created"     => date("Y-m-d G:i:s")
                );
                M("sys_other_log")->data($arr_other_log)->add();
            }
            $res = true;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = false;
        }
        return $res;
    }

    /**
     * 隐藏实施助手 修改sys_init配置
     * @return [type] [description]
     */
    public function hideSystemSetting($data){
        try{
            $operator_id=get_operator_id();
            $sql = "SELECT COUNT(1) AS total, value FROM cfg_setting WHERE `key`='%s'";
            $result = $this->query($sql, $data["key"]);
            if($data['value'] == $result[0]["value"]){return true;}
            $data['modified']=date("Y-m-d G:i:s");
            if ($result[0]['total'] != 0) {
                $this->data($data)->save();
            } else {
                $this->data($data)->add();
            }
            $arr_other_log = array(
                "type"        => 5,
                "operator_id" => $operator_id,
                "data"        => "",
                "message"     => "修改了系统设置--隐藏【系统清理】成功！",
                "created"     => date("Y-m-d G:i:s")
            );
            M("sys_other_log")->data($arr_other_log)->add();
            $res = true;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = false;
        }
        return $res;
    }

    /**
     * @param $data
     * @param $update
     * @param $options
     * 更新系统设置
     * author:changtao
     */
    public function addSystem($data,$update=false,$options='')
    {
        try {
            if (empty($data[0])) {
                $res = $this->add($data,$options,$update);

            }else
            {
                $res = $this->addAll($data,$options,$update);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-addSystem-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }

    public function commonMenu(){
        $operator_id=get_operator_id();
        $where=array(
            'user_id'=>$operator_id,
            'type'=>2,
        );
        $rights=M('cfg_user_data')->field('data')->where($where)->find();
        $right=explode(',',$rights['data']);
        if($operator_id!=1){
            $where=array(
                'type'=>0,
                'employee_id'=>$operator_id,
                'is_denied'=>0
            );
            $menus=M('cfg_employee_rights')->field("right_id")->where($where)->select();
            foreach($menus as $row){
                if(in_array($row['right_id'],$right))$menu[]=$row['right_id'];
            }
        }else{
            $menu=$right;
        }
        if(empty($menu))return [];
        $result=M('dict_url')->field("url_id AS id,name AS text,module,controller,action,type")->order('parent_id ASC, sort_order DESC')->where(array('type'=>array('gt',0),'action'=>array('neq',''),'url_id'=>array('in',$menu)))->select();
        return $result;
    }

    /**
     * 关闭档口模式
     * @return bool
     */
    public function hideStallsSetting($data){
        try{
            $operator_id=get_operator_id();
            $sql = "SELECT COUNT(1) AS total, value FROM cfg_setting WHERE `key`='%s'";
            $result = $this->query($sql, $data["key"]);
            if($data['value'] == $result[0]["value"]){return true;}
            $data['modified']=date("Y-m-d G:i:s");
            if ($result[0]['total'] != 0) {
                $this->data($data)->save();
            } else {
                $this->data($data)->add();
            }
            $arr_other_log = array(
                "type"        => 5,
                "operator_id" => $operator_id,
                "data"        => "",
                "message"     => "修改了系统设置--关闭【档口模式】成功！",
                "created"     => date("Y-m-d G:i:s")
            );
            M("sys_other_log")->data($arr_other_log)->add();
            $res = true;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = false;
        }
        return $res;
    }
	
	public function getOrderBalance($page = 1,$rows = 20,$search = array(),$sort = 'rec_id',$order = 'desc'){
		try{	
			\Think\Log::write($rows);
			$rows=intval($rows);
			\Think\Log::write($rows);
			$page=intval($page);
			$where = '';
			if(empty($search)){
				$search['start_time'] = date('Y-m-d',strtotime("-30 day"));
				$search['end_time'] = date('Y-m-d',time());
			}
			foreach($search as $k=>$v){
				if($v === ''){
					continue;
				}
				switch($k){
					 case 'start_time':
                    set_search_form_value($where, 'created', $v,'osl', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'created', $v,'osl', 3,' AND ',' < ');
                    break;
				}
			}
			$where=ltrim($where, ' AND ');
			
			$order = $sort.' '.$order;
			$limit = ($page - 1)*$rows.','.$rows;
			
			$order=addslashes($order);
			$point_number=get_config_value('point_number',0);
			$total = M('order_surplus_log')->alias('osl')->field('osl.rec_id as id')->where($where);
			$m = clone $total;
			$total_sql = $total->order($order)->limit($limit)->fetchsql(true)->select();
			$num = $this->query($m->fetchsql(true)->count());
			$row = M('order_surplus_log')->fetchsql(false)->distinct(true)->alias('osl_1')->field('if(osl_1.type,concat("+",osl_1.put_num),concat("-",osl_1.put_num)) as put_num,osl_1.data,osl_1.message,osl_1.created,osl_1.rec_id as id')->join('inner join ('.$total_sql.') t on t.id = osl_1.rec_id ')->order($order)->select();
			$data = array('total'=>$num[0]['tp_count'],'rows'=>$row);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$data = array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write($msg);
			 SE(self::PDO_ERROR);
		}
		return $data;
	}
}