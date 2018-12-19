<?php
namespace Purchase\Model;

use Think\Model;

class PurchaseReturnLogModel extends Model
{
    protected $tableName = 'purchase_return_log';
    protected $pk        = 'rec_id';
    public function insertPurchaseReturnLogForUpdate($data,$update = false,$options = ''){
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
            \Think\Log::write($this->name."-PurchaseReturnLogForUpdate-".$msg);
            E(self::PDO_ERROR);
        }
    }
}

?>