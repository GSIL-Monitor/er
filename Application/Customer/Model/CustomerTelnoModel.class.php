<?php
namespace Customer\Model;

use Common\Common\UtilDB;
use Think\Model;

class CustomerTelnoModel extends Model {

    protected $tableName = "crm_customer_telno";
    protected $pk        = "rec_id";

    public function addTelno($arr_customer_telno) {
        try {
            $sql = $this->fetchSql(true)->add($arr_customer_telno);
            $this->execute('INSERT IGNORE ' . substr($sql, 6));
        } catch (\PDOException $e) {
            \Think\Log::write('addTelno:' . $e->getMessage());
            E(self::PDO_ERROR);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            E($e->getMessage());
        }
    }

    public function getTelno($id) {
        try {
            $sql = "SELECT cc.telno,cc.type,cc.customer_id FROM crm_customer_telno cc WHERE cc.rec_id=%d";
            $res = $this->query($sql, $id);
            $res = $res[0];
            //查看号码日志
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            if ($cfg_show_telno == 1) {
                UtilDB::checkNumber(array($res["0"]["customer_id"]), "crm_customer", get_operator_id());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E(self::PDO_ERROR);
            $res = array();
        } catch (\Exception $e) {
            E($e->getMessage());
            $res = array();
        }
        return $res;
    }

    /**
     *通过订单信息添加客户联系方式
     */
    public function addTelnoByTrade($trade)
    {
        try
        {
            if (!empty($trade['receiver_mobile'])) 
            {
                $customer_telno=array(
                    'customer_id'=>$trade['customer_id'],
                    'type'=>1,
                    'telno'=>set_default_value($trade['receiver_mobile'],''),
                    'created'=>array('exp','NOW()'),
                );
                $this->addTelno($customer_telno);
            }
            if(!empty($trade['receiver_telno']))
            {
                $customer_telno=array(
                    'customer_id'=>$trade['customer_id'],
                    'type'=>2,
                    'telno'=>set_default_value($trade['receiver_telno'],''),
                    'created'=>array('exp','NOW()'),
                );
                $this->addTelno($customer_telno);
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addTelnoByTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

}