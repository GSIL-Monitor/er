<?php
namespace Stock\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/18/15
 * Time: 13:55
 */
class StockinOrderDetailModel extends Model
{
    protected $tableName = 'stockin_order_detail';
    public function showData($id)
    {

        try {
            $page_info = I('','',C('JSON_FILTER'));
            $limit = '';
            if(isset($page_info['page']))
            {
                $page = intval($page_info['page']);
                $rows = intval($page_info['rows']);
                $limit=" limit ".($page - 1) * $rows . "," . $rows;//分页
            }
			$point_number = get_config_value('point_number',0);
			
			$num = "CAST(siod.num AS DECIMAL(19,".$point_number.")) num";
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('siod.');
            $count = $this->where(array('stockin_id'=>$id))->count();
            $page_sql = 'select sid.rec_id from stockin_order_detail sid where sid.stockin_id = '.intval($id).' '.$limit;
            $data = $this->query("SELECT siod.rec_id, siod.spec_id ,gg.goods_id,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_code,gs.spec_name,gs.spec_no,gs.barcode,siod.expire_date,
            ".$field_right['cost_price'].", cgu.name AS base_unit_id, ".$num.",(siod.num+siod.adjust_num) AS right_num,gb.brand_name AS brand_id,
            (siod.cost_price+siod.adjust_price) AS  right_price, siod.production_date,siod.position_id, 
            siod.tax,siod.tax_price AS tax_price,siod.tax_amount,
            siod.position_id AS position_id,cwp.position_no,IF(gbc.is_master,'是','否') is_master
            FROM stockin_order_detail siod
            JOIN ($page_sql) siod_l ON siod_l.rec_id = siod.rec_id
            LEFT JOIN goods_spec gs ON siod.spec_id = gs.spec_id
            LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
            LEFT JOIN goods_barcode gbc ON gbc.target_id=gs.spec_id AND gbc.is_master = 1
            LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id
            LEFT JOIN cfg_goods_unit cgu ON siod.base_unit_id = cgu.rec_id
            LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = siod.position_id");//CONVERT((siod.cost_price+siod.adjust_price)*(siod.num+siod.adjust_num),DECIMAL(19,4)) AS right_cost,cwp.position_no,LEFT JOIN cfg_warehouse_position cwp ON siod.position_id=cwp.rec_id
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return 1;
        }
        return array('total'=>$count,'rows'=>$data);
    }
    public function getStockinOrderDetailList($fields,$conditions=array(),$group_str='')
    {
        try {
            $res = $this->field($fields)->where($conditions)->group($group_str)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockinOrderDetailList-'.$msg);
            E(self::PDO_ERROR);
        }
        return false;
    }
    public function insertStockinOrderDetailfForUpdate($data, $update=false, $options='')
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
            \Think\Log::write($this->name.'-insertStockinOrderDetailfForUpdate-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function updateDetail($rows,$order_id,&$result)
    {
        try{
            $operator_id = get_operator_id();
            $order_info = D('Stock/StockInOrder')->field('src_order_type')->where(array('stockin_id'=>$order_id))->find();
            //更新入库详单
            foreach ($rows['update'] as $key => $value) {

                $old_data = $this->field(array("spec_id", "num","src_price", "cost_price", "remark","rec_id"))->where(array('stockin_id'=>$order_id,'spec_id'=>$value['spec_id']))->find();

                $update_data = array(
                    "src_price"             => $value['src_price'],
                    "cost_price"            => $value['cost_price'],
                    "tax"                   => $value['tax_rate'],
                    "tax_price"             => $value['tax_price'],
                    "tax_amount"            => $value['tax_amount'],
                    "total_cost"            => $value['total_cost'],
                    "remark"                => '',
                    'num'                   => $value['num'],
                    'position_id'           => $value['position_id'],
                    "modified"              => array('exp',"NOW()"),
                );
                $this->where("rec_id = " . $old_data['rec_id'])->save($update_data);

                $update_msg     = array();
                if ((int)$value['num'] != (int)$old_data['num'])
                {
                    $update_msg[] = "数量由[" . $old_data['num'] . "]变为[" . $value['num'] . "]";
                }
                if ($value['cost_price'] != $old_data['cost_price'])
                {
                    $update_msg[] = "入库价由[" . $old_data['cost_price'] . "]变为[" . $value['cost_price'] . "]";
                }

                if ($value['src_price'] != $old_data['src_price'])
                {
                    $update_msg[] = "原价由[" . $old_data['src_price'] . "]变为[" . $value['src_price'] . "]";
                }

                $msg = implode('-',$update_msg);

                if (!empty($msg)) {
                    $update_log = array(
                        "order_type"   => 1,
                        "order_id"     => $order_id,
                        "operator_id"  => $operator_id,
                        "operate_type" => 13,
                        "message"      => "修改单品，商家编码" . $value['spec_no'] . "" . $msg,
                    );
                    D("StockInoutLog")->add($update_log);
                }
            }
            //删除已删除单品
            foreach ($rows['delete'] as $key => $value) {

                $this->where(array('stockin_id'=>$order_id,'spec_id'=>$value['spec_id']))->delete();
                $del_log = array(
                    "order_type"    => 1,
                    "order_id"      => $order_id,
                    "operator_id"   => $operator_id,
                    "operate_type"  => 13,
                    "message"       => "删除单品，商家编码:" . $value['spec_no'],
                );
                D("Stock/StockInoutLog")->add($del_log);
            }
            //添加
            foreach ($rows['insert'] as $key => $value) {
                $add_data = array(
                    "stockin_id"            => $order_id,
                    "src_order_type"        => $order_info['src_order_type'],
                    "src_order_detail_id"   => $value['id'],
                    "spec_id"               => $value['spec_id'],
                    "num"                   => $value['num'],
                    "expect_num"            => $value['expect_num'],
                    "base_unit_id"          => $value['base_unit_id'],
                    "src_price"             => $value['src_price'],
                    "cost_price"            => $value['cost_price'],
                    "total_cost"            => $value['total_cost'],
                    "remark"                => '',
                    'position_id'           => $value['position_id'],
                    "created"               => date('y-m-d H:i:s', time()),
                );

                $this->add($add_data);

                $add_log = array(
                    "order_type"    => 1,
                    "order_id"      => $order_id,
                    "operator_id"   => $operator_id,
                    "operate_type"  => 13,
                    "message"       => "增加单品，商家编码" . $value['spec_no'],
                );
                D("Stock/StockInoutLog")->add($add_log);
            }
        }catch (BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateDetail-'.$msg);
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateDetail-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
}