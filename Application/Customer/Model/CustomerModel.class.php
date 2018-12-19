<?php
namespace Customer\Model;

use Think\Model;

class CustomerModel extends Model
{
    protected $tableName = 'crm_customer';
    protected $pk        = 'customer_id';

    /**
     * 客户档案相关的数据库的基本操作
     * */
    public function addCustomer($data)
    {
        try
        {
            if (empty($data[0]))
            {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addCustomer-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function updateCustomer($data,$where=array())
    {
        try
        {
            $res = $this->where($where)->save($data);
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-updateCustomer-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getCustomer($fields,$where,$alias='',$join=array())
    {
    	try
        {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    	} catch (\PDOException $e)
        {
    		\Think\Log::write($this->name.'-getCustomer-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
        return $res;
    }

    public function getCustomerList($fields,$where,$alias='',$join=array())
    {
    	try
        {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getCustomerList-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
		return $res;
    }

    /**
     *通过订单添加客户信息
     */
    public function addCustomerByTrade($trade)
    {
        $customer_id=0;
        try
        {
            $trade['fenxiao_type']=set_default_value( $trade['fenxiao_type'],0);
            $customer=array(
                'customer_no'=>get_sys_no('customer',1),
                'type'=>$trade['fenxiao_type']>1?1:0,
                'nickname'=>set_default_value($trade['buyer_nick'],''),
                'name'=>set_default_value($trade['receiver_name'],''),
                'province'=>set_default_value($trade['receiver_province'],0),
                'city'=>set_default_value($trade['receiver_city'],0),
                'district'=>set_default_value($trade['receiver_district'],0),
                'area'=>set_default_value($trade['receiver_area'],''),
                'address'=>set_default_value($trade['receiver_address'],''),
                'zip'=>set_default_value($trade['receiver_zip'],''),
                'telno'=>set_default_value($trade['receiver_telno'],''),
                'mobile'=>set_default_value($trade['receiver_mobile'],''),
                'remark'=>set_default_value($trade['cs_remark'],''),
                'trade_count'=>1,
                'trade_amount'=>set_default_value($trade['receivable'],0),
                'last_trade_time'=>array('exp','NOW()'),
                'created'=>array('exp','NOW()'),
            );
            $customer_id=$this->addCustomer($customer);
            $this->addPlatFormCustomer(array('platform_id'=>set_default_value($trade['platform_id'],0),'account'=>$customer['nickname'],'customer_id'=>$customer_id));
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addCustomerByTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $customer_id;
    }

    public function addPlatFormCustomer($data)
    {
        try 
        {
            $sql = M('crm_platform_customer')->fetchSql(true)->add($data);
            if (!empty($sql)) 
            {
                $this->execute('INSERT IGNORE ' . substr($sql, 6));
            }
        } catch (\PDOException $e) 
        {
            \Think\Log::write('addTelno:' . $e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
}

?>