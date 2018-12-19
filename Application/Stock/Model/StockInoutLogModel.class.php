<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/18/15
 * Time: 13:42
 */
namespace Stock\Model;
use Think\Model;
class StockInoutLogModel extends Model
{
    protected $tableName = "stock_inout_log";
    protected $pk        = "rec_id";
    public function showLog($type,$id){
        try{
            $data = $this->query("SELECT  he.account  operator_id, sil.message, sil.created FROM stock_inout_log AS sil LEFT JOIN hr_employee as he ON he.employee_id = sil.operator_id WHERE sil.order_type = %d AND sil.order_id = %d  ORDER BY sil.created DESC",$type,$id);
        }catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return 1;
        }
        return $data;
    }
    public function insertStockInoutLog($dataList)
    {
        try {
            if (empty($dataList[0]))
            {
                $res = $this->add($dataList);
                
            }else {
                $res = $this->addAll($dataList);
                
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-insertStockInoutLogArr-".$msg);
            E(self::PDO_ERROR);
        }
    }
}