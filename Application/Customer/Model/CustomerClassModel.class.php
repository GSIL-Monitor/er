<?php
namespace Customer\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\UtilDB;

class CustomerClassModel extends Model
{

    protected $tableName = "crm_customer_class";
    protected $pk = "class_id";

    public function getCustomerClassList()
    {
        try {
            $sql = "SELECT class_id AS id, class_name, created, modified FROM crm_customer_class";
            $res = $this->query($sql);
            $data = array('total' => 1, 'rows' => $res);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array();
        }
        return $data;
    }
    public function updateCustomerClass($data){
        foreach($data as $k=>$v){
            $this->execute("UPDATE crm_customer_class SET class_name='{$v['class_name']}' WHERE class_id='{$v['id']}'");
        }
    }
    public function deleteCustomerClass($data){
        foreach($data as $k=>$v){
            $this->execute("UPDATE crm_customer SET class_id=0 WHERE class_id='{$v['id']}'");
            $this->execute("DELETE FROM crm_customer_class WHERE class_id='{$v['id']}'");
        }
    }
    public function addCustomerClass($data){
        foreach($data as $k=>$v){
            $this->execute("INSERT INTO crm_customer_class(class_name) VALUES('{$v['class_name']}')");
        }
    }
    public function batchEditCustomerClass($id_list, $class_id){
        $res = array('status'=>0,'info'=>'修改成功');
        $where = ' true ';
        if($id_list != 'all'){
            $where .= "AND customer_id IN (".$id_list.")";
        }
        try{
            $this->execute("UPDATE crm_customer SET class_id={$class_id} WHERE {$where}");
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $res = array('status'=>1,'info'=>'未知错误，请联系管理员');
        }
        return $res;
    }

}