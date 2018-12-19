<?php
namespace Stock\Model;

use Think\Model;

class StockChangeHistoryModel extends Model
{
    protected  $tableName = 'stock_change_history';
    protected  $pk = 'rec_id';
    /*
     * 
     *   更新出入库记录
     *   */
    public function insertStockChangeHistory($data,$update = false,$options = '')
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
            \Think\Log::write($this->name."-insertStockChangeHistory-".$msg);
            E(self::PDO_ERROR);
        }
    }
}
