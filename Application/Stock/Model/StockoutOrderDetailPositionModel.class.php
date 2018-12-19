<?php
namespace Stock\Model;

use Think\Model;

class StockoutOrderDetailPositionModel extends Model
{
    protected $tableName = 'stockout_order_detail_position';
    protected $pk        = 'rec_id';
    public function deleteInfo($conditions=array())
    {        
        try {
            $res = $this->where($conditions)->delete();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-deleteInfo-".$msg);
            E(self::PDO_ERROR);
        }
    }
}
