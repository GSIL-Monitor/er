<?php
namespace Setting\Model;

use Think\Exception\BusinessLogicException;
use Think\Log;
use Think\Model;
class SmsTemplateModel extends Model{
    protected $tableName = 'cfg_sms_template';
    protected $pk        = 'rec_id';
    public function getSmsTemplate($page,$rows,$search,$sort,$order){
        try{
            $page  = intval($page);
            $rows  = intval($rows);
            $sort  = addslashes($sort);
            $order = addslashes($order);
            $template_db = M("cfg_sms_template");
            $where       = "WHERE true ";
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                switch ($k) {
                    case "title":
                        set_search_form_value($where, $k, $v, 'cst', 6, ' AND ');
                        break;
                    default:
                        break;
                }
            }
                $limit = ($page - 1) * $rows . "," . $rows;
                $sort  = $sort . " " . $order;
                $sql_result = "SELECT cst.rec_id FROM cfg_sms_template cst $where ORDER BY $sort LIMIT $limit";
                $sql = "SELECT cst1.rec_id id,cst1.is_marketing,cst1.title,cst1.content,cst1.sign,cst1.modified,cst1.created
                    FROM cfg_sms_template cst1
                    INNER JOIN(".$sql_result.") cst2 on(cst1.rec_id = cst2.rec_id)";
                $sql_count    = "SELECT cst.rec_id AS id FROM cfg_sms_template cst $where";
                $res["total"] = count($template_db->query($sql_count));
                $res["rows"]  = $template_db->query($sql);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        }
        return $res;

    }
    /**
     * @return bool
     * 验证模板名称是否存在
     * author:sy
     */
    public function checkSmsTitle($value, $key = 'title') {
        try {
            $map[$key] = $value;
            $result    = $this->field('rec_id')->where($map)->find();
            if (!empty($result)) {
                return true;
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }

    public function getTemplateInfo($value, $name = "rec_id", $fields = "rec_id",$alias='cst'){
        try {
            $map[$name]    = $value;
            $result        = $this->alias($alias)->field($fields)->where($map)->select();
            $res["status"] = 1;
            $res["info"]   = "操作成功";
            $res["data"]   = $result;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
            $res["data"]   = array();
        }
        return $res;
    }


    public function updateSmsTemplate($data){
        try{
            if (!$this->create($data)) {
                SE($this->getError());
            }
            $this->startTrans();
            if (isset($data["rec_id"])) {
                $this->data($data)->save();
            }else{
                $this->data($data)->add();
            }
            $this->commit();
        } catch(\PDOException $e){
            Log::write($e->getMessage());
            $this->rollback();
            SE(parent::PDO_ERROR);
        }catch (\Exception $e){
            $this->rollback();
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    public function checkUseTemplate($id){
        try{
            $sql = "SELECT rec_id FROM cfg_sms_send_rule WHERE template_id=%d";
            $res = D('SmsSendRule')->query($sql,$id);
        }catch (\PDOException $e){
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $res;
    }

    public function deleteSmsTemplateById($id){
        //后期考虑加上判断正在使用的模板无法删除
        try{
            $res = $this->checkUseTemplate($id);
            if(!empty($res)){
                SE('该模板正在被使用,无法删除！');
            }
            $sql = "DELETE FROM cfg_sms_template WHERE rec_id = %s";
            $this->execute($sql,$id);
        } catch (\PDOException $e){
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            SE($e->getMessage());
        }

    }

    public function getContentById($id){
        $res = array();
        if($id=='无'){
            return $res;
        }
        try{
            $where = array("rec_id"=>$id);
            $res=$this->field('content,sign')->where($where)->find();
            if(empty($res)){
                SE('未知错误，请联系管理员');
            }
        }catch (\PDOException $e){
            log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e){
            log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $res;
    }

}