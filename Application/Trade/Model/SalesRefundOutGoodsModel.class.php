<?php
namespace Trade\Model;

use Think\Model;

class SalesRefundOutGoodsModel extends Model
{
    protected $tableName = 'sales_refund_out_goods';
    protected $pk        = 'rec_id';
    
    /**
     * 换出货品相关的数据库的基本操作
     * */
    public function addSalesRefundOutGoods($data)
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
            \Think\Log::write($this->name.'-addSalesRefundOutGoods-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateSalesRefundOutGoods($data,$where)
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateSalesRefundOutGoods-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function getSalesRefundOutGoods($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundOutGoods-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function getSalesRefundOutGoodsList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundOutGoodsList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
}

?>