<?php
namespace Stock\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
class StockoutOrderDetailModel extends Model
{
    protected $tableName = 'stockout_order_detail';
    protected $pk        = 'rec_id';
    /*
     * 分为两种类型：负库存的量，正常库存的量
     * 
     * */
    public function getStockoutOrderDetailNumLeftToPosition($fields,$conditions=array())
    {
        try {
            $res = $this->alias('sod')->field($fields)->join('inner join stockout_order_detail_position sodp on sod.rec_id = sodp.stockout_order_detail_id')->where($conditions)->group('sod.rec_id')->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockoutOrderDetailNumLeftToPosition-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function  getStockoutOrderDetails($fields,$conditions=array())
    {
        try {
            $res = $this->field($fields)->where($conditions)->order('spec_id')->select();
            
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockoutOrderDetails-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function insertForUpdateStockoutOrderDetail($datalist,$update=false,$options='')
    {
        try {
            if(empty($datalist[0]))
            {
                //$res = $this->table('stockout_order_detail')->add($datalist,$options,$update);
                $res = $this->add($datalist,$options,$update);
                
            }else {
                //$res = $this->table('stockout_order_detail')->addAll($datalist,$options,$update);
                $res = $this->addAll($datalist,$options,$update);
                
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-insertForUpdateStockoutOrderDetail-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    /* 
     * 连接的goods_spec  查询有关货品的详细信息
     *  */
    public function getStockoutOrderDetailLeftStockSpec($fields,$conditions = array())
    {
        try {
            //$res = $this->table('stockout_order_detail')->alias('sod')->field($fields)->join('left JOIN stock_spec ss ON sod.spec_id=ss.spec_id')->where($conditions)->group('sod.spec_id')->select();
            $res = $this->alias('sod')->field($fields)->join('left JOIN stock_spec ss ON sod.spec_id=ss.spec_id')->where($conditions)->group('sod.spec_id')->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockoutOrderDetailLeftStockSpec-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    //出库是库存的变化对于组合装的库存变化的影响
    public function getGoodsSuiteChangeInfo($fields,$conditions)
    {
        try {
            $res = $this->alias('sod')->field($fields)->where($conditions)->join("inner join goods_suite_detail gsd ON gsd.spec_id = sod.spec_id")->join("inner join goods_suite gs on gs.suite_id = gsd.suite_id")->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-getGoodsSuiteChangeInfo-".$msg);
            E(self::PDO_ERROR);
        }
    }
    public function showStockoutOrderDetailData($rec_id,$field_type='')
    {
        try {
            $setting_config = get_config_value(array('point_number','stockout_field_goods_pic'),array(0,0));
            $print_price_point_num = D('Stock/StockoutOrderDetail')->savePrintPointNum();
            $print_price_point_num = empty($print_price_point_num['data'])&&!is_numeric($print_price_point_num['data'])?'4':$print_price_point_num['data'];
			$num = 'CAST(sto.num AS DECIMAL(19,'.$setting_config['point_number'].')) num';
			$suite_num = 'CAST(sto.suite_num AS DECIMAL(19,'.$setting_config['point_number'].')) suite_num';
            $price = 'CAST(sto.price AS DECIMAL(19,'.$print_price_point_num.')) price';
            $order_price = 'CAST(sto.order_price AS DECIMAL(19,'.$print_price_point_num.')) order_price';
            $paid = 'CAST(sto.paid AS DECIMAL(19,'.$print_price_point_num.')) paid';
            $stockout_order_detail_fields = array(
                'gb.brand_name as brand_id',
                'gs.barcode',
                'sto.spec_no',
                'sto.goods_no',
                'sto.goods_name',
                'sto.spec_code',
                'sto.spec_name',
                'sto.api_goods_name',
                'sto.api_spec_name',
                $num,
                $price,
                $order_price,
                $paid,
                'sto.suite_name',
                'sto.suite_no',
                $suite_num,
                'sto.weight',
                'sto.remark',
                'IFNULL(cwp.position_no,cwp2.position_no) position_no'
            );
            if($setting_config['stockout_field_goods_pic']&2 && $field_type =='stocksales_print_detail')
            {

               array_push($stockout_order_detail_fields,'gs.img_url');
            }
            $stockout_order_detail_cond = array(
                "sod.stockout_id"=>$rec_id,
            );
            $data = $this->alias('sod')->field($stockout_order_detail_fields)->join("LEFT JOIN sales_trade_order sto ON sod.src_order_detail_id = sto.rec_id")->join("LEFT JOIN goods_goods gg ON sto.goods_id = gg.goods_id")->join("LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id")->join("LEFT JOIN goods_brand gb on gb.brand_id = gg.brand_id")->join('LEFT JOIN stockout_order so ON sod.stockout_id = so.stockout_id')->join('LEFT JOIN stock_spec_position ssp ON ssp.spec_id = sod.spec_id and ssp.warehouse_id = so.warehouse_id')->join('LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id')->join('LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -so.warehouse_id')->where($stockout_order_detail_cond)->select();
     
           
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-showStockoutOrderDetailData-".$msg);
             $data = array('total' => 0, 'rows' => array());
        }
        return $data;
       
    }
    public function getSalesStockoutOrderDetailGoodsinfo($fields,$conditions=array())
    {
        try {
            $res = $this->alias('sod')->join('goods_spec gs on gs.spec_id = sod.spec_id')->where($conditions)->field($fields)->group('sod.spec_id')->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-getSalesStockoutOrderDetailGoodsinfo-".$msg);
            SE(self::PDO_ERROR);
        }
       
    }
    public function updateStockoutOrderDetail($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateStockoutOrderDetail-'.$msg);
        }
    }
    public function savePrintPointNum($point_num=''){
        try {
            $operator_id=get_operator_id();
            $cfg_user_data_model=M('cfg_user_data');
            $where=array(
                'user_id'=>$operator_id,
                'type'=>10,
                'code'=>'print_price_point_num_dialog'
            );
            if(empty($point_num)&&!is_numeric($point_num)){
                $get_cfg=$cfg_user_data_model->field('data')->where($where)->find();
                return $get_cfg;
            }else{
                $get_cfg=$cfg_user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$point_num;
                    $cfg_user_data_model->where($where)->save($save);
                }else{
                    $add=array(
                        'user_id'=>$operator_id,
                        'type'=>10,
                        'code'=>'print_price_point_num_dialog',
                        'data'=>$point_num,
                    );
                    $cfg_user_data_model->add($add);
                }
            }
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-savePrintPointNum-'.$msg);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-savePrintPointNum-'.$msg);
        }
    }
    public function savePreviewFormat($format=''){
        try {
            $operator_id=get_operator_id();
            $user_data_model=D('Setting/UserData');
            $where=array('user_id'=>$operator_id,'type'=>10,'code'=>'print_preview_format_dialog');
            if(empty($format)){
                $get_cfg = $user_data_model->getUserData('data',$where);
                return $get_cfg;
            }else{
                $get_cfg=$user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$format;
                    $user_data_model->updateUserData($save,$where);
                }else{
                    $add=array('user_id'=>$operator_id,'type'=>10,'code'=>'print_preview_format_dialog','data'=>$format);
                    $user_data_model->addUserData($add);
                }
            }
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-savePreviewFormat-'.$msg);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-savePreviewFormat-'.$msg);
        }
    }
    public function saveGoodsOrder($goods_order = ''){
        try {
            $operator_id=get_operator_id();
            $user_data_model=D('Setting/UserData');
            $where=array('user_id'=>$operator_id,'type'=>10,'code'=>'print_preview_goods_order_dialog');
            if(empty($goods_order)&&!is_numeric($goods_order)){
                $get_cfg = $user_data_model->getUserData('data',$where);
                return $get_cfg;
            }else{
                $get_cfg=$user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$goods_order;
                    $user_data_model->updateUserData($save,$where);
                }else{
                    $add=array('user_id'=>$operator_id,'type'=>10,'code'=>'print_preview_goods_order_dialog','data'=>$goods_order);
                    $user_data_model->addUserData($add);
                }
            }
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-saveGoodsOrder-'.$msg);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-saveGoodsOrder-'.$msg);
        }
    }
    
}
