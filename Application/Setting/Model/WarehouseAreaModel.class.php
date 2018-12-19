<?php
namespace Setting\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilTool;

class WarehouseAreaModel extends Model {
    protected $tableName = 'cfg_warehouse_area';
    protected $pk = 'rec_id';

    /**
     *基本操作方法集
     */
    public function addWarehouseArea($data)
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
            \Think\Log::write($this->name.'-addWarehouseArea-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function updateWarehouseArea($data,$where)
    {
        try
        {
            $res = $this->where($where)->save($data);
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-updateWarehouseArea-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
    public function getWarehouseArea($fields,$where=array(),$alias='')
    {
        try
        {
            $res = $this->alias($alias)->field($fields)->where($where)->find();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getWarehouseArea-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getWarehouseAreaList($fields,$where=array(),$alias='',$join=array(),$order='')
    {
        $res=array();
        try
        {
            $res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
        } catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getWarehouseAreaList-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getAreaTree()
    {
        $tree=array();
        try
        {
            $province=M('dict_province')->field('province_id AS id,name AS text,country_id AS parent_id')->order('province_id ASC')->select();
            $city=M('dict_city')->field('city_id AS id,name AS text,province_id AS parent_id')->select();//->order('province_id ASC,city_id ASC')
            $district=M('dict_district')->field('district_id AS id,name AS text,city_id AS parent_id')->select();
            $area=$province;
            foreach ($city as $v)
            {
               $area[]=$v;
            }
            foreach ($district as $v)
            {
               $area[]=$v;
            }
            $result=UtilTool::array2tree($area, 'id', 'parent_id', 'children');
            $tree=array(array('id'=>0,'parent_id'=>-1,'text'=>'全国','children'=>$result));
        }catch (\PDOException $e)
        {
            \Think\Log::write($this->name.'-getAreaTree-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $tree;
    }

    public function editWarehouseArea($warehouse_area,$warehouse_id,$user_id)
    {
        $add_warehouse_area=array();
        $del_warehouse_area=array();
        $tmp_warehouse_area=array();
        $log=array();
        $warehouse_id=intval($warehouse_id);
        try
        {
            foreach ($warehouse_area as $wa)
            {
                $level=intval($wa['level']);
                $path='';
                switch ($level) {
                    case 1:
                        $path.=$wa['province_id'].',';
                        break;
                    case 2:
                        $path.=$wa['province_id'].','.$wa['city_id'].',';
                        break;
                    case 3:
                        $path.=$wa['province_id'].','.$wa['city_id'].','.$wa['district_id'].',';
                        break;
                    default:
                        $path='';
                        break;
                }
                $tmp_warehouse_area[]=array(
                    'warehouse_id'=>intval($wa['warehouse_id']),
                    'level'=>$level,
                    'province_id'=>intval($wa['province_id']),
                    'city_id'=>intval($wa['city_id']),
                    'district_id'=>intval($wa['district_id']),
                    'path'=>$path
                );
            }
            $res_warehouse_area=$this->getWarehouseAreaList('rec_id,warehouse_id,path',array('warehouse_id'=>array('eq',$warehouse_id)));
            $res_wa_map=array();
            foreach ($res_warehouse_area as $w)
            {
                $res_wa_map[$w['warehouse_id'].'_'.$w['path']]=true;
            }
            $wa_map=array();
            foreach ($tmp_warehouse_area as $w)
            {
                $wa_map[$w['warehouse_id'].'_'.$w['path']]=true;
                if (!isset($res_wa_map[$w['warehouse_id'].'_'.$w['path']]))
                {
                    $add_warehouse_area[]=$w;
                }else
                {
                     $log[]=array(
                        'type'=>7,
                        'operator_id'=>$user_id,
                        'data'=>$warehouse_id,
                        'message'=>'新增仓库覆盖范围策略,warehouse_id:'.$warehouse_id.',path:'.$w['path'],
                        'created'=>date('Y-m-d H:i:s',time())
                    );
                }
            }
            foreach ($res_warehouse_area as $w)
            {
                if (!isset($wa_map[$w['warehouse_id'].'_'.$w['path']]))
                {
                    $del_warehouse_area[]=intval($w['rec_id']);
                    $log[]=array(
                        'type'=>7,
                        'operator_id'=>$user_id,
                        'data'=>$warehouse_id,
                        'message'=>'删除仓库覆盖范围策略,warehouse_id:'.$warehouse_id.',path:'.$w['path'],
                        'created'=>date('Y-m-d H:i:s',time())
                    );
                }
            }
            $this->startTrans();
            //取消仓库选中的地址
            if (!empty($del_warehouse_area))
            {
                $this->where(array('rec_id'=>array('in',$del_warehouse_area)))->delete();
            }
            //新增仓库选中的地址
            $this->addWarehouseArea($add_warehouse_area);
            M('sys_other_log')->addAll($log);
            $this->commit();
        }catch (\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write($this->name.'-editWarehouseArea-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
}