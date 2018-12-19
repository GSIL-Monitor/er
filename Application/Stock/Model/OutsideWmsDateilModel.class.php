<?php 
namespace Stock\Model;
use Think\Model;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;
class OutsideWmsDateilModel extends Model{
	protected $tableName = 'outside_wms_order_detail';
	protected $pk = 'rec_id';
	public function getOutsideWmsDateil($id){
		try{
			$sql = "select gs.spec_no,gg.goods_name,gg.goods_no,gs.spec_code,gs.spec_name,gs.barcode,gb.brand_name,owd.num,".
			"cgu.name,owd.price,owd.inout_num,cwp.position_no from outside_wms_order_detail owd left join goods_spec gs on ".
			"  owd.spec_id = gs.spec_id left join goods_goods gg on gs.goods_id=gg.goods_id left join cfg_warehouse_position ".
			" cwp on cwp.rec_id = owd.position_id left join goods_brand gb on gb.brand_id = gg.brand_id left join cfg_goods_unit".
			" cgu on cgu.rec_id = owd.base_unit_id where owd.order_id = %d ";
			$data['rows'] = $this->query($sql,$id);
			$data['total'] = count($data['rows']);
			
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$data = array('rows'=>array(),'total'=>0);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data = array('rows'=>array(),'total'=>0);
			
		}
		return $data;
	}
	public function getOutsideWmsLog($id){
		try{
			$data = M('outside_wms_order_log')->alias('owl')->field('hr.fullname,owl.message,owl.created')->join('left join hr_employee hr on owl.operator_id = hr.employee_id')->where(array('order_id'=>$id))->select();
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$data = array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data = array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	public function getOutsideWmsDetail($field,$where,$join = '',$alias = ''){
		try{
			if(empty($join)){
				$data['rows'] = $this->field($field)->where($where)->select();
			}else{
				$data['rows'] = $this->alias($alias)->fetchSql(false)->field($field)->join($join)->where($where)->select();
			}
			
			$data['total'] = count($data['rows']);
		}catch(\Exception $e){
			\Think\Log::write('getOutsideWmsDetail--'.$e->getMessage());
			$data=array();
		}
		return $data;
	}
}