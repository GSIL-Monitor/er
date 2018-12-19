<?php
namespace Home\Model;

use Think\Model;

class NotificationModel extends Model
{
    protected $tableName = 'sys_notification';
    protected $pk        = 'rec_id';
    
    /**
     * 消息相关的数据库的基本操作
     * */
    public function addNotification($data)
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
            \Think\Log::write($this->name.'-addNotification-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }
   
    public function deleteNotification($where=array())
    {
    	try {
    		$this->where($where)->delete();
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-deleteNotification-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }
    
    public function updateNotification($data,$where=array())
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateNotification-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }
    
    public function getNotification($fields,$where,$alias='',$join=array(),$order='')
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getNotification-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }

    public function getNotificationList($fields,$where,$alias='',$join=array(),$order='')
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getNotificationList-'.$e->getMessage());
    		E(self::PDO_ERROR);
    	}
    }
    
    public function addInvalidShopInfo()
    {
    	try {
    		$shop_db=D('Setting/Shop');
    		$res_shop_arr=$shop_db->field('shop_id,shop_name,auth_state')->where(array('auth_state'=>array('neq',1),'platform_id'=>array('neq',0),'is_disabled'=>array('eq',0)))->select();
			if(empty($res_shop_arr))
    		{
    			return;
    		}
    		$arr_auth_state=array('未授权','已授权','授权失效','授权停用');
    		$arr_notification=array();
    		foreach ($res_shop_arr as $shop)
    		{
    			$arr_notification[]=array(
    					'type'=>2,
    					'receiver'=>0,
    					'sender'=>0,
    					'priority'=>1,//优先级0普通1重要2紧急
    					'is_handled'=>0,
    					'handle_oper_id'=>0,
    					'message'=>'店铺--'.$shop['shop_name'].'--'.$arr_auth_state[$shop['auth_state']],
    					'created'=>date('y-m-d H:i:s',time())
    			);
    		}
    		$this->addNotification($arr_notification);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getInvalidShopInfo-'.$e->getMessage());
    	}
    }
    
    public function getInvalidGoodsInfo()
    {
    	try {
    		$res_invalid_goods=$this->query('SELECT COUNT(distinct ags.rec_id)  AS total FROM api_trade_order ato_1 LEFT JOIN  api_goods_spec ags ON (ags.shop_id=ato_1.shop_id AND ags.platform_id = ato_1.platform_id AND ags.goods_id=ato_1.goods_id AND ags.spec_id=ato_1.spec_id) WHERE  ato_1.is_invalid_goods=1 AND ato_1.process_status<40 AND ato_1.platform_id>0 AND ato_1.status <= 40 AND ags.is_deleted=0');
    		if($res_invalid_goods[0]['total']==0)
    		{
    			return;
    		}
    		$arr_notification[]=array(
    				'type'=>2,
    				'receiver'=>0,
    				'sender'=>0,
    				'priority'=>1,//优先级0普通1重要2紧急
    				'is_handled'=>0,
    				'handle_oper_id'=>0,
    				'message'=> '未匹配货品--'.$res_invalid_goods[0]['total'],
    				'created'=>date('y-m-d H:i:s',time())
    		);
    		$this->addNotification($arr_notification);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getInvalidGoodsInfo-'.$e->getMessage());
    	}
    }
    
    private function rc4($key, $pwd) {
    	$str=base64_decode($pwd);
    	$s = array();
    	for ($i = 0; $i < 256; $i++) {
    		$s[ $i ] = $i;
    	}
    	$j = 0;
    	for ($i = 0; $i < 256; $i++) {
    		$j = ($j + $s[ $i ] + ord($key[ $i % strlen($key) ])) % 256;
    		$x = $s[ $i ];
    		$s[ $i ] = $s[ $j ];
    		$s[ $j ] = $x;
    	}
    	$i = 0;
    	$j = 0;
    	$res = '';
    	for ($y = 0; $y < strlen($str); $y++) {
    		$i = ($i + 1) % 256;
    		$j = ($j + $s[ $i ]) % 256;
    		$x = $s[ $i ];
    		$s[ $i ] = $s[ $j ];
    		$s[ $j ] = $x;
    		$res .= $str[ $y ] ^ chr($s[ ($s[ $i ] + $s[ $j ]) % 256 ]);
    	}
    	return $res;
    }
    
    public function addNoteInfo($message)
    {
    	try {
    		$url='http://101.200.202.174/service/index.php?action=merchant_list&host=101.200.202.174';
    		$sid_info=file_get_contents($url);
    		$arr_sid=json_decode($sid_info);
    		if($arr_sid->status!=0)
    		{
    			E('请求卖家账号失败');
    		}
    		$arr_sid_db=array();
    		foreach ($arr_sid->info as $sid)
    		{
    			$url_db='http://101.200.202.174/service/index.php?action=conn&sid='.$sid;
    			$sid_db_info=file_get_contents($url_db);
    			$sid_db=json_decode($sid_db_info);
    			if($sid_db->status!=0)
    			{
    				\Think\Log::write($this->name.'-addNoteInfo-请求卖家('.$sid.')数据库失败');
    				continue;
    				//E('请求卖家数据库失败');
    			}
    			$arr_sid_db[]='mysql://'.$sid_db->info->db_user.':'.$this->rc4($sid_db->info->secret,$sid_db->info->db_pwd).'@'.$sid_db->info->db_host.':3306/'.$sid_db->info->db_name;
    		}
    		$arr_notification=array(
    				'type'=>1,
    				'receiver'=>0,
    				'sender'=>0,
    				'priority'=>2,//优先级0普通1重要2紧急
    				'is_handled'=>0,
    				'handle_oper_id'=>0,
    				'message'=> $message,
    				'created'=>date('y-m-d H:i:s',time())
    		);
    		foreach ($arr_sid_db as $sid_conn)
    		{
    			$this->connection=$sid_conn;
    			$this->addNotification($arr_notification);
    		}
    	}catch (\Exception $e){
    		\Think\Log::write($this->name.'-addNoteInfo-'.$e->getMessage());
    		E($e->getMessage());
    	}
    }
}

?>