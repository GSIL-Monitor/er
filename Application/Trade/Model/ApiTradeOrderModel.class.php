<?php
namespace Trade\Model;

use Think\Model;

class ApiTradeOrderModel extends Model
{
    protected $tableName = 'api_trade_order';
    protected $pk        = 'rec_id';
    
    /**
     * 平台货品相关的数据库的基本操作
     * */
    public function addApiTradeOrder($data)
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
            \Think\Log::write($this->name.'-addApiTradeOrder-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateApiTradeOrder($data,$where=array())
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateApiTradeOrder-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function getApiTradeOrder($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getApiTradeOrder-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    public function getApiTradeOrderList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getApiTradeOrderList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    /**
     * 获取无效货品数量的方法
     */
    public function getInvalidGoods()
    {
        $result=0;
        try
        {
            $invalid_goods=$this->query("SELECT COUNT(distinct ags.rec_id)  AS total FROM api_trade_order ato_1 LEFT JOIN  api_trade at ON (at.tid=ato_1.tid and at.platform_id=ato_1.platform_id) LEFT JOIN  api_goods_spec ags ON (ags.shop_id=ato_1.shop_id AND ags.platform_id = ato_1.platform_id AND ags.goods_id=ato_1.goods_id AND ags.spec_id=ato_1.spec_id) WHERE  ato_1.is_invalid_goods=1 AND at.process_status<20 AND ato_1.platform_id>0 AND ato_1.status <= 40 AND ags.is_deleted=0");
            $result=empty($invalid_goods)?0:$invalid_goods[0]['total'];
        }catch(\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $result; 
    }
}

?>