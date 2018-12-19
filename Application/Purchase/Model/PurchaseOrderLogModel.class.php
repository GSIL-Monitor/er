<?php
namespace Purchase\Model;

use Think\Model;

class PurchaseOrderLogModel extends Model
{
    protected $tableName = 'purchase_order_log';
    protected $pk        = 'rec_id';
    public function insertPurchaseOrderLogForUpdate($data,$update = false,$options = ''){
        try {
            if(empty($data[0]))
            {
                $res = $this->add($data,$options,$update);
    
            }else{
                $res = $this->addAll($data,$options,$update);
    
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-insertPurchaseOrderLogForUpdate-".$msg);
            E(self::PDO_ERROR);
        }
    }
}

?>