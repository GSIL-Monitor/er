<?php
namespace Stock\Model;

use Think\Model;

class StockSpecDetailModel extends Model
{
    protected $tableName = 'stock_spec_detail';
    protected $pk        = 'rec_id';
    public function insertStockSpecDetailForUpdate($data,$update=false,$options='')
    {
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
            \Think\Log::write($this->name.'-insertStockSpecDetailForUpdate-'.$msg);
            E(self::PDO_ERROR);
        }
    }
}

?>