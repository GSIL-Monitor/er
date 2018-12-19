<?php
namespace Setting\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;

class SmsSendRuleModel extends Model{
    protected $tableName = 'cfg_sms_send_rule';
    protected $pk        = 'rec_id';

    public function getSmsSendRuleList($page = 1, $rows = 20, $sort = 'rec_id', $order = 'desc')
    {
        try
        {
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;
            $sort  = $sort . " " . $order;
            //先查出rec_id
            $rec_sql = "SELECT rec_id FROM cfg_sms_send_rule ORDER BY $sort LIMIT $limit";
            $sql = "SELECT cssr.rec_id id,cs.shop_name,cssr.event_type,cst.title as template_id,cssr.delay_time,cssr.end_time,cssr.is_disabled,
                    cssr.modified,cssr.created FROM cfg_sms_send_rule cssr
                    INNER JOIN (".$rec_sql.") cssr1 ON (cssr.rec_id = cssr1.rec_id)
                    LEFT JOIN cfg_shop  cs ON (cssr.shop_id = cs.shop_id)
                    LEFT JOIN cfg_sms_template cst ON (cssr.template_id = cst.rec_id)";
            $sql_count    = "SELECT rec_id AS id FROM cfg_sms_send_rule";
            $res["total"] = count($this->query($sql_count));
            $res["rows"]  = $this->query($sql);
        }catch (BusinessLogicException $e)
        {
            SE($e->getMessage());
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $res;
    }

    public function getSmsTemplate(){
        $sql = "SELECT rec_id,rec_id template_id,title template_name FROM cfg_sms_template";
        $data = D('SmsTemplate')->query($sql);
        return $data;
    }
    public function getRuleById($id){
        try
        {
            $sql = "SELECT cssr.rec_id id,cssr.shop_id,cssr.event_type,cssr.template_id,cssr.delay_time,cssr.end_time,cssr.is_disabled,
                    cssr.modified,cssr.created
                    FROM cfg_sms_send_rule cssr
                    WHERE cssr.rec_id =%d";
            $result = $this->query($sql,$id);
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            $result = array();
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array();
            SE(parent::PDO_ERROR);
        }
        return $result;
    }
    public function checkRule($data){
        try{
            $sql = "SELECT rec_id FROM cfg_sms_send_rule WHERE shop_id=%d AND event_type=%d AND template_id=%d";
            $res = $this->query($sql,$data['shop_id'],$data['event_type'],$data['template_id']);
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $res;
    }

    public function addSmsSendRule($data){
        try
        {
            $operator_id = get_operator_id();
            $now = date('Y-m-d H:i:s',time());
            $data['created'] = $now;
            //检查是否有相同店铺的触发条件短信
            $res = $this->checkRule($data);
            if(!empty($res)){
                SE('相同店铺相同模板不能设置同一触发条件');
            }
            $this->startTrans();
            $rule_id=$this->data($data)->add();
            //插入系统日志
            $sys_log = array(
                'type' => 18,
                'operator_id' =>$operator_id,
                'data' => $rule_id,
                'message' => '新建短信策略--id:'.$rule_id,
                'created' => $now
            );
            $sys_log_db = M('sys_other_log');
            $sys_log_db->data($sys_log)->add();
            $this->commit();
        }catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }catch(\Exception $e){
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
    public function updateSmsSendRule($data){
        try
        {
            $operator_id = get_operator_id();
            $now = date('Y-m-d H:i:s',time());
            $data['is_disabled'] = $data['is_disabled'] ==1?1:0;
            $oldRule = $this->getRuleById($data['rec_id']);
            $oldRule = $oldRule[0];
            $sys_log = array();
            $sys_log['type']=18;
            $sys_log['operator_id'] = $operator_id;
            $sys_log['data'] = $data['rec_id'];
            $sys_log['created'] = $now;
            $sys_log['message'] = '';
            if($oldRule['shop_id']!=$data['shop_id']){
                $sys_log['message'] .= '更改短信策略店铺从'.$oldRule['shop_id'].'到'.$data['shop_id'];
            }elseif($oldRule['event_type']!=$data['event_type']){
                $sys_log['message'] .= '更改短信策略触发事件从'.$oldRule['event_type'].'到'.$data['event_type'];
            }elseif($oldRule['template_id']!=$data['template_id']){
                $sys_log['message'] .= '更改短信策略模板从'.$oldRule['template_id'].'到'.$data['template_id'];
            }elseif($oldRule['delay_time']!=$data['delay_time']){
                $sys_log['message'] .= '更改短信策略延迟时间从'.$oldRule['delay_time'].'到'.$data['delay_time'];
            }elseif($oldRule['end_time']!=$data['end_time']){
                $sys_log['message'] .= '更改短信策略截止时间从'.$oldRule['end_time'].'到'.$data['end_time'];
            }elseif($oldRule['is_disabled']!=$data['is_disabled']){
                $sys_log['message'] .= '更改短信策略是否停用从'.$oldRule['is_disabled'].'到'.$data['is_disabled'];
            }
            $this->checkRule($data);
            if(!empty($res)){
                SE('修改后的策略与原有策略重复，请检查店铺名、触发事件、模板是否都重复');
            }
            $this->startTrans();
            $this->data($data)->save();
            $sys_log_db = M('sys_other_log');
            $sys_log_db->data($sys_log)->add();
            $this->commit();
        }catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }catch (\Exception $e){
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
    public function deleteSmsSendRuleById($id){
        try{
            $sql = "UPDATE cfg_sms_send_rule SET is_disabled = 1 WHERE rec_id = %d";
            $this->execute($sql,$id);
        } catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }

    }

}