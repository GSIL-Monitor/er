<?php
namespace Trade\Model;

use Think\Model;

class SalesRefundLogModel extends Model
{
    protected $tableName = 'sales_refund_log';
    protected $pk        = 'rec_id';
    public function addSalesRefundLog($data)
    {
        try {
            if (empty($data[0])) {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-addRefundLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateRefundLog($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateRefundLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    
}

?>