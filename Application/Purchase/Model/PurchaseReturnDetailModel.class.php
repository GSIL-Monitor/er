<?php
namespace Purchase\Model;

use Think\Model;

class PurchaseReturnDetailModel extends Model
{
    protected $tableName = 'purchase_return_detail';
    protected $pk        = 'rec_id';
    public function insertPurchaseReturnDetailForUpdate($data,$update = false,$options = ''){
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
            \Think\Log::write($this->name."-insertPurchaseReturnDetailForUpdate-".$msg);
            E(self::PDO_ERROR);
        }
    }
    public function getPurchaseReturnDetailList($fields,$conditions=array())
    {
        try {
            $res = $this->field($fields)->where($conditions)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getPurchaseReturnDetailList-'.$msg);
            E(self::PDO_ERROR);
        }
        return false;
    }
    public function getPurchaseReturnDetailLogInfoLeftGoodsSpec($fields,$conditions)
    {
        try {
            $res = $this->alias('pod')->field($fields)->join('left join goods_spec gs on gs.spec_id = pod.spec_id')->where($conditions)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getPurchaseReturnDetailLogInfoLeftGoodsSpec-'.$msg);
            E(self::PDO_ERROR);
        }
        return false;
    }
	public function showPurchasedetail($id){
		try{
			$point_number=get_config_value('point_number',0);
			
			$num = 'CAST(pc.num AS DECIMAL(19,'.$point_number.')) num';
			$num2 = 'CAST(pc.num2 AS DECIMAL(19,'.$point_number.')) num2';
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$out_num = 'CAST(IFNULL(pc.out_num,0) AS DECIMAL(19,'.$point_number.')) out_num';
			$where = array('return_id'=>$id);
			$fields =  array('pc.rec_id as id','pc.discount','pc.price as cost_price','gs.spec_no',$out_num,'pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,$num2,'pc.price as cost_price','pc.discount','pc.amount','pc.remark','cgu.name as unit_name','gs.img_url',$stock_num);
			$order_field = array('warehouse_id');
			$form_data = D('Purchase/PurchaseReturn')->getPurchase($order_field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购退货单信息');
                SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			$data = $this->fetchsql(false)->alias('pc')->field($fields)->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['warehouse_id'].')')->where($where)->select();
		}catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function showPurchaseReturnlog($id){
		try{
			$field = array('he.account','pol.remark','pol.created');
			$where = array('return_id'=>$id);
			$data = D('Purchase/PurchaseReturnLog')->alias('pol')->field($field)->join('LEFT JOIN hr_employee he ON he.employee_id = pol.operator_id')->where($where)->order('pol.created desc')->select();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $data;
	}
}

?>