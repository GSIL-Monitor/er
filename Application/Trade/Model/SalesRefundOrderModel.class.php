<?php
namespace Trade\Model;

use Think\Model;

class SalesRefundOrderModel extends Model
{
    protected $tableName = 'sales_refund_order';
    protected $pk        = 'refund_order_id';
    
    /**
     * 退货货品相关的数据库的基本操作
     * */
    public function addSalesRefundOrder($data)
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
            \Think\Log::write($this->name.'-addSalesRefundOrder-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateSalesRefundOrder($data,$where=array())
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateSalesRefundOrder-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function getSalesRefundOrder($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundOrder-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    public function getSalesRefundOrderList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundOrderList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
}

?>