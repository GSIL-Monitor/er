<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2016/7/4
 * Time: 18:23
 */

namespace Stock\Model;


use Think\Model;

class StockSpecPositionModel extends Model
{
    protected $tableName = 'stock_spec_position';
    protected $pk = 'rec_id';
    public function getPositionSpecInfo($id,$page,$rows)
    {
        try{
            $page = intval($page);
            $rows = intval($rows);
            $data = array('total' => 0, 'rows' => array());
            $limit = ($page - 1) * $rows . "," . $rows;
            $warehouse_id = D('Setting/WarehousePosition')->field('warehouse_id')->where(array('rec_id'=>$id))->find();
            $list = $this->alias('ssp')->field(array('cw.name as warehouse_name','ssp.rec_id as id','gs.spec_id','gs.spec_no','gs.spec_name','gs.spec_code','cwp.position_no','gg.goods_name','gg.short_name','gg.goods_no'))->join('LEFT JOIN cfg_warehouse_position cwp ON ssp.`position_id`=cwp.`rec_id`')->join('LEFT JOIN goods_spec gs ON ssp.`spec_id` = gs.`spec_id`')->join('JOIN goods_goods gg ON gg.goods_id = gs.`goods_id`')->where('ssp.`warehouse_id`='.$warehouse_id['warehouse_id'].' AND cwp.rec_id=%d',$id)->join('cfg_warehouse cw on cw.warehouse_id = ssp.warehouse_id')->limit($limit)->select();
            $count = count($this->alias('ssp')->field(array('cw.name as warehouse_name','ssp.rec_id as id','gs.spec_id','gs.spec_no','gs.spec_name','gs.spec_code','cwp.position_no','gg.goods_name','gg.short_name','gg.goods_no'))->join('LEFT JOIN cfg_warehouse_position cwp ON ssp.`position_id`=cwp.`rec_id`')->join('LEFT JOIN goods_spec gs ON ssp.`spec_id` = gs.`spec_id`')->join('JOIN goods_goods gg ON gg.goods_id = gs.`goods_id`')->where('ssp.`warehouse_id`='.$warehouse_id['warehouse_id'].' AND cwp.rec_id=%d',$id)->join('cfg_warehouse cw on cw.warehouse_id = ssp.warehouse_id')->select());
            $data=array('total'=>$count,'rows'=>$list);
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }
}