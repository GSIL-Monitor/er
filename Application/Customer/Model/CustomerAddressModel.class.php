<?php
namespace Customer\Model;

use Think\Model;
use Common\Common\UtilDB;

class CustomerAddressModel extends Model {

    protected $tableName = "crm_customer_address";
    protected $pk        = "rec_id";

    public function addAddress($arr_customer_address) {
        try {
            $sql = $this->fetchSql(true)->add($arr_customer_address);
            $this->execute('INSERT IGNORE ' . substr($sql, 6));
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E(self::PDO_ERROR);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            E($e->getMessage());
        }
    }

    public function getAddress($id) {
        try {
            $sql = "SELECT cc.customer_id,cc.telno,cc.mobile,cc.address,cc.province,cc.city,cc.district,cc.name,cc.area FROM crm_customer_address cc WHERE cc.rec_id=%d";
            $res = $this->query($sql, $id);
            $res = $res["0"];
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
     *通过订单信息添加客户地址
     */
    public function addAddressByTrade($trade)
    {
        try
        {
            $address=array(
                'customer_id'=>$trade['customer_id'],
                'name'=>set_default_value($trade['receiver_name'],''),
                'addr_hash'=>md5($trade['receiver_province'].$trade['receiver_city'].$trade['receiver_district'].$trade['receiver_address']),
                'province'=>set_default_value($trade['receiver_province'],0),
                'city'=>set_default_value($trade['receiver_city'],0),
                'district'=>set_default_value($trade['receiver_district'],0),
                'address'=>set_default_value($trade['receiver_address'],''),
                'zip'=>set_default_value($trade['receiver_zip'],''),
                'telno'=>set_default_value($trade['receiver_telno'],''),
                'mobile'=>set_default_value($trade['receiver_mobile'],''),
                'email'=>set_default_value($trade['buyer_email'],''),
                'created'=>array('exp','NOW()'),
            );
            $this->addAddress($address);
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addAddressByTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

}