<?php
namespace Customer\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\UtilTool;

class MarketManagementModel extends Model
{
    protected $tableName = "crm_marketing_plan";
    protected $pk        = "rec_id";

    public function getMarketList($page, $rows, $search, $sort, $order)
    {
        try
        {
            $page  = intval($page);
            $rows  = intval($rows);
            $sort  = addslashes($sort);
            $order = addslashes($order);
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            $sql_result = "SELECT cmp.rec_id FROM crm_marketing_plan cmp ORDER BY $order LIMIT $limit";
            //再构造SQL查询完整的数据
            $sql            = "SELECT cmp1.rec_id AS id,cmp1.plan_name,cmp1.plan_type,he.fullname operator_id,cmp1.msg_content,cmp1.status,cmp1.remark,cmp1.created,cmp1.modified,cmp1.error_msg
                               FROM crm_marketing_plan cmp1 INNER JOIN (".$sql_result.")cmp2 ON (cmp1.rec_id = cmp2.rec_id) LEFT JOIN hr_employee he ON(he.employee_id = cmp1.operator_id)";
            $sql_count      = "SELECT COUNT(1) AS total FROM crm_marketing_plan cmp";
            $result         = $this->query($sql_count);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql);
        }catch (\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    public function getMarketPlanById($id)
    {
        try
        {
            $data = $this->where(array('rec_id'=>$id))->find();
        }catch (\PDOException $e)
        {
            \Think\Log::write('getMarketPlanById pdo ERR:'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('getMarketPlanById ERR:'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $data;
    }

    public function getMarketDetail($id,$page,$rows,$sort, $order)
    {
        $sort  = addslashes($sort);
        $order = addslashes($order);
        $limit = ($page - 1) * $rows . "," . $rows;
        $order = $sort . " " . $order;
        try
        {
            $sql_result = "SELECT cmr.rec_id FROM crm_marketing_result cmr WHERE cmr.plan_id = $id ORDER BY $order LIMIT $limit";
            $sql = "SELECT cmr1.rec_id AS id,cmr1.mobile,cmr1.nickname ,cmr1.name,he.fullname operator_id,cmr1.marketing_date,cmr1.created
                    FROM crm_marketing_result cmr1 INNER JOIN(".$sql_result.") cmr2 ON (cmr1.rec_id=cmr2.rec_id) LEFT JOIN hr_employee he ON(he.employee_id = cmr1.operator_id)";
            $sql_count      = "SELECT COUNT(1) AS total FROM crm_marketing_result WHERE plan_id=$id";
            $result         = $this->query($sql_count);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql);
        }catch (\PDOException $e)
        {
            \Think\Log::write('getMarketDetail pdo ERR:'.$e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('getMarketDetail ERR:'.$e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
            SE(parent::PDO_ERROR);
        }
        return $data;
    }

    public function addMarketPlan($sms,$type)
    {
        try
        {
            if($type == 'add')
            {
                $repeat_name = $this->where(array('plan_name'=>$sms['name']))->find();
                if($repeat_name)
                {
                    SE('名称已存在');
                }
                $data['operator_id'] = get_operator_id();
                $data['created'] = date('Y-m-d H:i:s' , time());
                $data['modified'] = date('Y-m-d H:i:s' , time());
                $data['plan_name'] = $sms['name'];
                $data['msg_content'] = $sms['message'];
                $data['remark'] = $sms['remark'];
                $this->data($data)->fetchSql(false)->add();
            }elseif($type == 'update')
            {
                $where = array(
                    'rec_id' => array('neq',$sms['id']),
                    'plan_name' => array('eq',$sms['name'])
                );
                $repeat_name = $this->where($where)->fetchSql(false)->find();
                if($repeat_name)
                {
                    SE('名称已存在');
                }
                $status = $this->field('status')->where(array('rec_id'=>$sms['id']))->find();
                $status = $status['status'];
                if($status ==1)
                {
                    SE('该方案已营销，无法编辑');
                }
                $data['operator_id'] = get_operator_id();
                $data['modified'] = date('Y-m-d H:i:s' , time());
                $data['plan_name'] = $sms['name'];
                $data['msg_content'] = $sms['message'];
                $data['remark'] = $sms['remark'];
                $this->data($data)->where(array('rec_id'=>$sms['id']))->save();
            }

        }catch (BusinessLogicException $e)
        {
            SE($e->getMessage());
        }catch (\PDOException $e)
        {
            \Think\Log::write('add_market_plan pdo err:'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    public function delPlanById($id,$table)
    {
        try
        {
            if($table == 'result'){
                M('crm_marketing_result')->where(array('rec_id'=>$id))->delete();
            }else{
                M('crm_marketing_result')->where(array('plan_id'=>$id))->delete();
                $this->where(array('rec_id'=>$id))->delete();
            }
        }catch (\PDOException $e)
        {
            \Think\Log::write('delPlanById pdo err:'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('delPlanById :'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    public function importCustomer($id,$data,&$error_list,$line)
    {
        $operator_id = get_operator_id();
        $current_time = date('Y-m-d H:i:s',time());
        try{
            foreach($data as $k=>$v)
            {
                if(empty($v['customer_no']) && empty($v['mobile']))
                {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'客户编码和手机号不能都为空');
                    continue;
                }
                if(!empty($v['customer_no'])) //先根据客户编码去查客户信息。
                {
                    $customer_res = D('CustomerFile')->where(array('customer_no'=>$v['customer_no']))->find();
                    if($customer_res)
                    {
                        $import_data['customer_id'] = $customer_res['customer_id']; //crm_customer表 主键
                        $import_data['name'] = $customer_res['name'];//用户名
                        $import_data['nickname'] = $customer_res['nickname'];//用户网名
                        $import_data['mobile'] = $customer_res['mobile'];//用户手机号
                        $import_data['flag_id'] = $customer_res['flag_id'];//标记ID
                    }else
                    {
                        $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'客户编码不存在');
                        continue;
                    }
                }elseif(!empty($v['mobile']))//客户编码为空的话再根据手机号去查找
                {
                    $cm_id = M('crm_customer_telno')->field('customer_id')->where(array('telno'=>$v['mobile']))->find();
                    $customer_id = $cm_id['customer_id'];
                    $customer_res = D('CustomerFile')->where(array('customer_id'=>$customer_id))->find();
                    if($customer_res)
                    {
                        $import_data['customer_id'] = $customer_id; //crm_customer表 主键
                        $import_data['name'] = $customer_res['name'];//用户名
                        $import_data['nickname'] = $customer_res['nickname'];//用户网名
                        $import_data['mobile'] = $customer_res['mobile'];//用户手机号
                        $import_data['flag_id'] = $customer_res['flag_id'];//标记ID
                    }else
                    {
                        $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'手机号码不存在');
                        continue;
                    }
                }
                //导入前查询下 唯一主键
                $market_result = M('crm_marketing_result')->where(array('plan_id'=>$id,'mobile'=>$import_data['mobile']))->find();
                if($market_result)
                {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'手机号'.$import_data['mobile'].'已在此营销计划中');
                    continue;
                }
                $import_data['plan_id'] = $id;
                $import_data['operator_id'] = $operator_id;
                $import_data['created'] = $current_time;
                M('crm_marketing_result')->data($import_data)->fetchSql(false)->add();
            }
        }catch (\PDOException $e)
        {
            \Think\Log::write('importCustomer PDO ERR:'.print_r($e->getMessage(),true));
        }catch (\Exception $e)
        {
            \Think\Log::write('importCustomer ERR:'.print_r($e->getMessage(),true));
            SE(parent::PDO_ERROR);
        }

    }

    public function addMarketById($ids,$plan_id)
    {
        try
        {
            $operator_id = get_operator_id();
            $current_time = date('Y-m-d H:i:s',time());
            if($ids!='')
            {
                foreach($ids as $v)
                {
                    //代码可以共用。
                    $customer_res = D('CustomerFile')->where(array('customer_id'=>$v))->find();
                    //判断是否有重复
                    $repet_res = M('crm_marketing_result')->where(array('plan_id'=>$plan_id,'mobile'=>$customer_res['mobile']))->find();
                    if($repet_res){
                        continue;//重复直接跳过，不提示
                    }
                    $import_data['customer_id'] = $v; //crm_customer表 主键
                    $import_data['name'] = $customer_res['name'];//用户名
                    $import_data['nickname'] = $customer_res['nickname'];//用户网名
                    $import_data['mobile'] = $customer_res['mobile'];//用户手机号
                    $import_data['flag_id'] = $customer_res['flag_id'];//标记ID
                    $import_data['plan_id'] = $plan_id;
                    $import_data['operator_id'] = $operator_id;
                    $import_data['created'] = $current_time;
                    M('crm_marketing_result')->data($import_data)->fetchSql(false)->add();
                }
            }else
            {
                $sql = file_get_contents(APP_PATH."/Runtime/File/Customer");
                $data = $this->query($sql);
                foreach($data as $v)
                {
                    //判断是否有重复
                    $repet_res = M('crm_marketing_result')->where(array('plan_id'=>$plan_id,'mobile'=>$v['mobile']))->find();
                    if($repet_res){
                        continue;//重复直接跳过，不提示
                    }
                    $import_data['customer_id'] = $v['id']; //crm_customer表 主键
                    $import_data['name'] = $v['name'];//用户名
                    $import_data['nickname'] = $v['nickname'];//用户网名
                    $import_data['mobile'] = $v['mobile'];//用户手机号
                    $import_data['flag_id'] = $v['flag_id'];//标记ID
                    $import_data['plan_id'] = $plan_id;
                    $import_data['operator_id'] = $operator_id;
                    $import_data['created'] = $current_time;
                    M('crm_marketing_result')->data($import_data)->fetchSql(false)->add();
                }
            }

        }catch (\PDOException $e)
        {
            \Think\Log::write('addMarketById PDO ERR:'.print_r($e->getMessage(),true));
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('addMarketById ERR:'.print_r($e->getMessage(),true));
            SE(parent::PDO_ERROR);
        }
    }

    public static function count_sms($msg)
    {
        $one_message_count = 66;
        $len = iconv_strlen($msg, 'UTF-8');
        $count = (int)($len / $one_message_count);
        if($len % $one_message_count) ++$count;
        return $count;
    }

    public function sendMarketSms($id)
    {
        $res = array('status'=>0,'info'=>'营销成功');
        $operator_id = get_operator_id();
        $current_time = date('Y-m-d H:i:s',time());
        $marketdb = M('crm_marketing_result');
        $where = array('plan_id'=>$id);
        $data = $marketdb->where($where)->select();
        if(!$data)
        {
            $res['status'] = 1;
            $res['info'] = '请先添加需要营销的客户';
            return $res;
        }
        $mobile = '';
        try{
            foreach($data as $k=>$v)
            {
                $mobile .= $v['mobile'].',';
            }
            if(empty($mobile))
            {
                $res['status'] = 1;
                $res['info'] = '营销用户手机号为空,请检查';
                return $res;
            }
            $message = $this->field('msg_content')->where(array('rec_id'=>$id))->find();
            $message = $message['msg_content'];
            //过滤掉头尾是逗号的情况
            if(substr($mobile,-1)==',')
            {
                $mobile = substr($mobile,0,-1);
            }
            if(substr($mobile,0,1) == ',')
            {
                $mobile = substr($mobile,1);
            }
            $mobile_array = explode(',',$mobile);
            $each_sms_num = self::count_sms($message);
            $batch_no = $this->query("SELECT FN_SYS_NO('sms')");
            $batch_no = $batch_no[0]["fn_sys_no('sms')"];
            if(!strpos($message,'回T退订') || !strpos($message,'退订回T'))
            {
                $message.=' 回T退订';
            }
            $error = false;
            if(count($mobile_array)>0)
            {
                while(count($mobile_array)>0)
                {
                    $arr = array_splice($mobile_array,0,50);
                    $phones = implode(',',$arr);
                    //先向发送记录表插入一条待发送的记录
                    $count = count($arr);
                    $sms_record_data = array(
                        'status' => 0,
                        'sms_type' => 0,
                        'operator_id' => $operator_id,
                        'phone_num' => $count,
                        'phones' => $phones,
                        'message' => $message,
                        'send_time' => date('Y-m-d H:i:s', time()),
                        'batch_no' => $batch_no,
                        'created' => date('Y-m-d H:i:s', time()),
                        'pre_count' => $each_sms_num*$count,
                        'success_people' => $count,
                        'success_count' => $each_sms_num*$count,
                        'send_type' => 0
                    );
                    $sms_id = M('crm_sms_record')->data($sms_record_data)->fetchSql(false)->add();
                    $ids['id'] = $sms_id;

                    $sms_res = UtilTool::SMS($phones,$message,'market');
                    D('Customer/CustomerFile')->updateSMSStatus($ids,$sms_res);

                    if($sms_res['status'])
                    {
                        //状态大于0为失败的情况。直接返回短信平台返回的结果。
                        $error = true;
                    }
                    continue;
                }
                $this->startTrans();
                $result_data['operator_id'] = $operator_id;
                $result_data['marketing_date'] = $current_time;
                $plan_data['status'] = 1;
                $plan_data['modified'] = $current_time;

                $marketdb->where($where)->data($result_data)->fetchSql(false)->save();
                $this->where(array('rec_id'=>$id))->data($plan_data)->fetchSql(false)->save();
                $this->commit();
                if($error)
                {
                    $res['status']=1;
                    $res['info'] = '营销完成,请在短信发送界面查看短信发送情况';
                }
            }
        }catch (\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write('sendMarketSms ERR:'.print_r($e->getMessage(),true));
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            $this->rollback();
            \Think\Log::write('sendMarketSms ERR:'.print_r($e->getMessage(),true));
            SE(parent::PDO_ERROR);
        }

        return $res;
    }

}