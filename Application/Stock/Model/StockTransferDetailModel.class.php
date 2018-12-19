<?php
namespace Stock\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;

class StockTransferDetailModel extends Model{
    protected $tableName = 'stock_transfer_detail';
    protected $pk        = 'rec_id';
    protected $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('num','checkNumberType','调拨数量不合法!',1,'callback',3),
        array('stock_num','checkStockNum','负库存不能出库!',1,'callback',3),
        array('num,stock_num','checkTransferNum','调拨数量不能大于库存数量!',1,'callback',3),
    );
    protected function checkStockNum($stock_num){
        $stock_num = intval($stock_num);
        return $stock_num<=0?false:true;
    }
    protected function checkNumberType($num){
        $is_num  = is_numeric($num);
        return $is_num;
    }
    protected function checkTransferNum($nums){
        $res =  intval($nums['num'])>intval($nums['stock_num'])? false:true;
        return $res;
    }
    public function insert($data,$update = false,$options = '')
    {
        try {
            if(empty($data[0]))
            {
                $res = $this->add($data,$options,$update);

            }else{
                $res = $this->addAll($data,$options,$update);

            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
    }
    public function getDetailInfo($fields,$condtions=array())
    {
        try {
            $result = $this->field($fields)->where($condtions)->select();
            return $result;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
    }
	public function getStockTransDetailData($id){
		try{
				$point_number=get_config_value('point_number',0);
				$data=$this->query("select std.transfer_id,gg.goods_id,gg.goods_name,gg.goods_no,gs.spec_code,gs.spec_name,gs.spec_no,gg.short_name,gb.brand_name AS brand_id,gs.barcode,CAST(std.stock_num AS DECIMAL(19,".$point_number.")) stock_num,CAST(std.num AS DECIMAL(19,".$point_number.")) num,CAST(std.in_num AS DECIMAL(19,".$point_number.")) in_num,CAST(std.out_num AS DECIMAL(19,".$point_number.")) out_num,std.remark,cwp_f.position_no as from_position_no,cwp_t.position_no as to_position_no 
									FROM stock_transfer_detail std
									LEFT JOIN goods_spec gs ON std.spec_id=gs.spec_id
									LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
									LEFT JOIN goods_brand gb ON gb.brand_id=gg.brand_id
									LEFT JOIN cfg_warehouse_position cwp_f ON cwp_f.rec_id=std.from_position
									LEFT JOIN cfg_warehouse_position cwp_t ON cwp_t.rec_id=std.to_position
									WHERE std.transfer_id=%d",$id);
									
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}
}
