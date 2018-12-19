<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 12/1/15
 * Time: 10:05
 */
namespace Common\Model;

use Think\Model;

class SysProcessBackgroundModel extends Model
{
    protected $tableName = 'sys_process_background';
    protected $pk = 'rec_id';
    /* 
     * 入库库存改变时出入任务队列信息
     *  */
    public function stockinChange($stockin_id)
    {
        try {
            $res_goods_spec = D('Stock/StockinOrderDetail')->field(array('spec_id as object_id','1 as type'))->where(array('stockin_id' => $stockin_id))->select();

            $this->addAll($res_goods_spec);

            $res_goods_suite = $this->query("SELECT gs.suite_id as object_id ,2 as type FROM goods_suite gs, goods_suite_detail gsd,stockin_order_detail sod WHERE sod.stockin_id=%d AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sod.spec_id", $stockin_id);


            $this->addAll($res_goods_suite);

        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            E(self::PDO_ERROR);
        }

    }
    /*
     * 出库库存改变时出入任务队列信息
     *  */
    public function stockoutChange($stockout_id)
    {
        try {
            $res_goods_spec =D('Stock/StockoutOrderDetail')->field(array('1 as type','spec_id as object_id'))->where(array('stockout_id' => $stockout_id))->select();
            $this->addAll($res_goods_spec);

            $res_goods_suite = $this->query("SELECT 2 as type, gs.suite_id as object_id FROM goods_suite gs, goods_suite_detail gsd,stockout_order_detail sod WHERE sod.stockout_id=%d AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sod.spec_id;", $stockout_id);

            $this->addAll($res_goods_suite);

        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            E(self::PDO_ERROR);
        }

    }
    /*
     * 库存同步之前需要批量把修改的库存信息映射到平台货品
     *  */
    public function batchDealSysProcessBackground()
    {
        try {
            //sys_process_background
            //查出最新的库存信息
            $res_max = $this->field('IFNULL(MAX(rec_id),0) as max_rec_id')->where(array('type'=>array('in','1,2')))->find();
            $max_rec_id = $res_max['max_rec_id'];
            if($max_rec_id===0)
            {
                return true;
            }
            //将单品和组合装的库存信息分别查询出来
            $res_goods_spec = $this->distinct(true)->field('object_id')->where(array('rec_id'=>array('ELT',$max_rec_id),'type'=>1))->select();
                    $res_goods_suite = $this->distinct(true)->field('object_id')->where(array('rec_id'=>array('ELT',$max_rec_id),'type'=>2))->select();
            //检查每一组库存信息改变数据，并更新平台货品
            //单品
            foreach ($res_goods_spec as $goods_spec_key => $goods_spec)
            {

                $update_platform_mag_goodsspec_cond = array(
                    'is_deleted' => 0,
                    'match_target_type' => 1,
                    'match_target_id' => $goods_spec['object_id'],  
                );
                $update_platform_mag_goodsspec_data = array(
                    'is_stock_changed' => 1,
                    'stock_change_count' => array('exp','stock_change_count+1'),
                );
                D('Goods/PlatformGoods')->updateApiGoodsSpec($update_platform_mag_goodsspec_data,$update_platform_mag_goodsspec_cond);
            }
            //组合装
            foreach ($res_goods_suite as $goods_suite_key => $goods_suite)
            {
                 
                $update_platform_mag_goodssuite_cond = array(
                    'is_deleted' => 0,
                    'match_target_type' => 2,
                    'match_target_id' => $goods_suite['object_id'],
                );
                $update_platform_mag_goodssuite_data = array(
                    'is_stock_changed' => 1,
                    'stock_change_count' => array('exp','stock_change_count+1'),
                );
                D('Goods/PlatformGoods')->updateApiGoodsSpec($update_platform_mag_goodssuite_data,$update_platform_mag_goodssuite_cond);
            }
            //删除同步记录
            $res_delete = $this->where(array('rec_id'=>array('ELT',$max_rec_id),'type'=>array('in','1,2')))->delete();
            if ($res_delete === false)
            {
                \Think\Log::write($this->name."-batchDealSysProcessBackground-"."清除sys_process_background失败");
            }
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-batchDealSysProcessBackground-".$msg);
            E(self::PDO_ERROR);
        }
    }
    public function insertProcessBackground($data)
    {
        try {
            if (empty($data))
            {
                $res = $this->table('sys_process_background')->add($data);
    
            }else {
                $res = $this->table('sys_process_background')->addAll($data);
    
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-insertProcessBackground-".$msg);
            E('未知错误，请联系管理员');
        }
    }
}


