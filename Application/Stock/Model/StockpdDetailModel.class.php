<?php
namespace Stock\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
class StockpdDetailModel extends Model
{
    protected $tableName = 'stock_pd_detail';
    protected $pk        = 'pd_id';

	public function showStockpdDetailData($rec_id)
    {
        try {
			$point_number = get_config_value('point_number',0);			
			$old_num = 'CAST(sod.old_num AS DECIMAL(19,'.$point_number.')) old_num';
			$new_num = 'CAST(sod.new_num AS DECIMAL(19,'.$point_number.')) new_num';
			$pd_num = 'CAST((sod.new_num - sod.old_num) AS DECIMAL(19,'.$point_number.')) pd_num';			
            $stockpd_detail_fields = array(                     
                'gs.barcode',
				'gs.spec_no',
                'gg.goods_no',
                'gg.goods_name',
                'gs.spec_code',
				'gs.spec_name',
				$old_num,
				$new_num,
				'sod.remark',
				$pd_num,
                'IFNULL(cwp.position_no,cwp2.position_no) position_no'
            );
            $stockpd_detail_cond = array(
                "sod.pd_id"=>$rec_id,
            );
            $data = $this->alias('sod')->field($stockpd_detail_fields)->join("LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id")->join("LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id")->join('LEFT JOIN stock_pd so ON sod.pd_id = so.rec_id')->join('LEFT JOIN stock_spec_position ssp ON ssp.spec_id = sod.spec_id and ssp.warehouse_id = so.warehouse_id')->join('LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id')->join('LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -so.warehouse_id')->where($stockpd_detail_cond)->select();
     
           
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-showStockpdDetailData-".$msg);
             $data = array('total' => 0, 'rows' => array());
        }
        return $data;
       
    }
	

}