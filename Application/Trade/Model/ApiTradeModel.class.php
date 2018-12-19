<?php
namespace Trade\Model;

use Think\Model;

class ApiTradeModel extends Model
{
    protected $tableName = 'api_trade';
    protected $pk        = 'rec_id';
    
    /**
     * 平台订单相关的数据库的基本操作
     * */
    public function addApiTrade($data)
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
            \Think\Log::write($this->name.'-addApiTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateApiTrade($data,$where=array())
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateApiTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function getApiTrade($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getApiTrade-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    public function getApiTradeList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getApiTradeList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
}

?>