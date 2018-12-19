<?php
namespace Setting\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;

class ShopWarehouseModel extends Model {
    protected $tableName = 'cfg_shop_warehouse';
    protected $pk = 'rec_id';
    /**
     *基本操作方法集
     */
    public function addShopWarehouse($data)
    {
        try
        {
            if (empty($data[0])) {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-addShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function updateShopWarehouse($data,$where)
    {
        try
        {
            $res = $this->where($where)->save($data);
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-updateShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
    public function getShopWarehouse($fields,$where=array(),$alias='')
    {
        try
        {
            $res = $this->alias($alias)->field($fields)->where($where)->find();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getShopWarehouseList($fields,$where=array(),$alias='',$join=array(),$order='')
    {
        $res=array();
        try
        {
            $res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getShopWarehouseList-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getShopWarehouseByShopId($shop_id=0)
    {
        $shop_warehouse=array();
        try
        {
            $shop_id=intval($shop_id);
            $warehouse_db=D('Warehouse');
            $res_shop_warehouse=$warehouse_db->alias('w')
                                         ->field('w.warehouse_id,IF(sw.shop_id='.$shop_id.',sw.rec_id,0) AS rec_id,IF(IF(sw.shop_id='.$shop_id.',sw.warehouse_id,0),1,0) AS is_select,IF(sw.shop_id='.$shop_id.',sw.priority,\'\') AS priority,w.name,w.province,w.city,w.district')
                                         ->join('LEFT JOIN cfg_shop_warehouse sw ON w.warehouse_id=sw.warehouse_id')
                                         ->where(array('w.is_disabled'=>array('eq',0)))
                                         ->order('w.warehouse_id ASC')
                                         ->select();

            $map=array();
            foreach ($res_shop_warehouse as $sw)
            {
                if (isset($map[strval($sw['warehouse_id'])]))
                {
                    $map[strval($sw['warehouse_id'])]=$sw['is_select']!=0?$sw:$map[strval($sw['warehouse_id'])];
                }else
                {
                    $map[strval($sw['warehouse_id'])]=$sw;
                }
            }
            foreach ($map as $sw) 
            {
                $shop_warehouse[]=$sw;
            }
         } catch (\PDOException $e)
         {
            \Think\Log::write($this->name.'-getShopWarehouseByShopId-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $shop_warehouse;
    }

    public function editShopWarehouse($shop_warehouse,$shop_id,$user_id)
    {
        try
        {
            $shop_id=intval($shop_id);
            $add_shop_warehouse=array();
            $del_shop_warehouse=array();
            $now=date('Y-m-d H:i:s',time());
            $add_ids='';
            $del_ids='';
            foreach ($shop_warehouse as $sw)
            {
                if ($sw['is_select']==1)
                {
                    $add_shop_warehouse[]=array(
                        'shop_id'=>$shop_id,
                        'warehouse_id'=>intval($sw['warehouse_id']),
                        'priority'=>set_default_value($sw['priority'],1),
                        'modified'=>$now,
                    );
                    $add_ids.=$sw['warehouse_id'].',';
                }else if($sw['is_select']==0)
                {
                    $del_shop_warehouse[]=array(
                        'shop_id'=>$shop_id,
                        'warehouse_id'=>intval($sw['warehouse_id']),
                    );
                    $del_ids.=$sw['warehouse_id'].',';
                }
            }
            $log=array();
            if (!empty($add_ids))
            {
                $log[]=array(
                    'type'=>7,
                    'operator_id'=>$user_id,
                    'data'=>$shop_id,
                    'message'=>'新增店铺使用仓库策略,shop_id:'.$shop_id.',warehouse_id:'.$add_ids,
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }
            if (!empty($del_ids))
            {
                $log[]=array(
                    'type'=>7,
                    'operator_id'=>$user_id,
                    'data'=>$shop_id,
                    'message'=>'删除店铺使用仓库策略,shop_id:'.$shop_id.',warehouse_id:'.$del_ids,
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }
            $this->startTrans();
            //取消选中的仓库
            foreach ($del_shop_warehouse as $v)
            {
                $this->where(array('shop_id'=>array('eq',$v['shop_id']),'warehouse_id'=>array('eq',$v['warehouse_id'])))->delete();
            }
            //添加或更新选中的仓库
            // $this->addShopWarehouse($add_shop_warehouse);
            $sql=$this->addAll($add_shop_warehouse,array(),array('priority'));
//             if (!empty($sql))
//             {
//                 $this->execute($sql.' ON DUPLICATE KEY UPDATE priority=VALUES(priority)');
//             }
            M('sys_other_log')->addALL($log);
            $this->commit();
        } catch (\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write($this->name.'-editShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
}