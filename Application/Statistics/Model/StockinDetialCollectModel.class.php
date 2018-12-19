<?php
namespace Statistics\Model;

use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;

class StockinDetialCollectModel extends Model{
    protected $tableName = 'stockin_order_detail ';
    protected $pk = 'rec_id' ;

    public function searchFormDeal(&$where,$search){
        foreach($search as $k=>$v){
            if (!isset($v)) continue;
            switch($k){
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'so',2,'AND');
                    break;
                case 'operator_id':
                    set_search_form_value($where,$k,$v,'so',2,'AND');
                    break;
                case 'stockin_no':
                    set_search_form_value($where,$k,$v,'so',1,'AND');
                    break;
                case 'src_order_type':
                    set_search_form_value($where,$k,$v,'so',1,'AND');
                    break;
                case 'goods_no':
                    set_search_form_value($where,$k,$v,'gg',1,'AND');
                    break;
                case 'goods_name':
                    set_search_form_value($where,$k,$v,'gg',1,'AND');
                    break;
                case 'spec_no':
                    set_search_form_value($where,$k,$v,'gs',1,'AND');
                    break;
                case 'class_id':
                    set_search_form_value($where, $k, $v, 'gg',7,' AND ');
                    break;
                case 'brand_id':
                    set_search_form_value($where, $k, $v, 'gg',2,' AND ');
                    break;
                case 'day_start':
                    set_search_form_value($where, 'check_time', $v,'so', 3,' AND ',' >= ');
                    break;
                case 'day_end':
                    set_search_form_value($where, 'check_time', $v,'so', 3,' AND ',' < ');
            }
        }
    }

    public function loadDataByCondition($page, $rows, $search, $sort, $order){
        $page  = intval($page);
        $rows  = intval($rows);
        $where = ' WHERE so.status=80 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']==0)unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
        $limit = " limit ".($page - 1) * $rows . "," . $rows;//分页
        $sort  = 'sod.'.$sort . " " . $order;
        $sort  = addslashes($sort);
		$get_ids_sql = '';
		$sql = $this->joinsql($where,$sort,$limit,$get_ids_sql);

        try{
            $data['rows'] = $this->query($sql);
            $count =$this->query($get_ids_sql);
            $data['total'] = count($count);
        }catch (\PDOException $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }
        return $data;
    }

    public function exportToExcel($search, $id_list){
        $creator=session('account');
        $stockin_type=array(
            '1'=>'采购入库',
            '2'=>'调拨入库',
            '3'=>'退货入库',
            '4'=>'盘盈入库',
            //'5'=>'生产入库',
            '6'=>'其他入库',
            //'7'=>'少发入库',
            //'8'=>'纠错入库',
            '9'=>'初始化入库'
        );
        $data=array();
        $where = ' WHERE so.status=80 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']==0)unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
		$sort = " sod.rec_id desc ";
		$limit = "";
		$get_ids_sql = "";
		$sql = $this->joinsql($where,$sort,$limit,$get_ids_sql);
        try{
            $arr = array();
            $arr = $this->query($sql);
            $num = workTimeExportNum();
            $amount = array();
            $amount['stockin_no'] = "合计";
            $amount['src_order_type'] = ' ';
            $amount['warehouse_name'] = ' ';
            $amount['position_no'] = ' ';
            $amount['fullname'] = ' ';
            $amount['logistics_no'] = ' ';
            $amount['spec_no'] = ' ';
            $amount['goods_no'] = ' ';
            $amount['goods_name'] = ' ';
            $amount['spec_name'] = ' ';
            $amount['spec_right_num'] = ' ';
            $amount['right_num'] = ' ';
            $amount['right_price'] = ' ';
            $amount['src_price'] = ' ';
            $amount['right_cost'] = ' ';
            $amount['total_right_price'] = ' ';
            $amount['check_time'] = ' ';
            $amount['remark'] = ' ';
            $list = array();
            if(!empty($id_list)){
                $list = explode(",",$id_list);
            }
            foreach($arr as $k=>$v){
                if(!empty($list) && !in_array($v['id'],$list)) {
                    //导出选中数据
                    continue;
                }else {
                    $row['stockin_no'] = $v['stockin_no'];
                    $row['src_order_type'] = $stockin_type[$v['src_order_type']];
                    $row['warehouse_name'] = $v['warehouse_name'];
                    $row['position_no'] = $v['position_no'];
                    $row['fullname'] = $v['fullname'];
                    $row['logistics_no'] = $v['logistics_no'];
                    $row['spec_no'] = $v['spec_no'];
                    $row['goods_no'] = $v['goods_no'];
                    $row['goods_name'] = $v['goods_name'];
                    $row['spec_name'] = $v['spec_name'];
                    $row['num'] = $v['num'];
                    $row['spec_right_num'] = $v['spec_right_num'];
                    $row['right_num'] = $v['right_num'];
                    $row['cost_price'] = $v['cost_price'];
                    $row['total_cost'] = $v['total_cost'];
                    $row['right_price'] = $v['right_price'];
                    $row['src_price'] = $v['src_price'];
                    $row['right_cost'] = $v['right_cost'];
                    $row['total_right_price'] = $v['total_right_price'];
                    $row['check_time'] = $v['check_time'];
                    $row['remark'] = $v['remark'];

                    $amount['num'] += $row['num'];
                    $amount['cost_price'] += $row['cost_price'];
                    $amount['total_cost'] += $row['total_cost'];

                    $data[] = $row;
                }
            }
            $data[] = $amount;
            if(count($data)>$num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/StockinDetialCollect','stockindetialcollect');
            $title = '入库明细表';
            $filename = '入库明细表';
            $width_list = array('20','20','20','30','15','15','15',
                                '25','25','30','15','15','15','15',
                                '15','15','15','15','15','20','20' );
            ExcelTool::Arr2Excel($data,$title,$excel_header,$width_list,$filename,$creator);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
	
	public function joinsql($where,$sort,$limit = "",&$get_ids_sql = ""){
		try{
		$point_number = get_config_value('point_number',0);
        $num = "CAST(sod.num AS DECIMAL(19,".$point_number.")) num";

        $get_ids_sql=" select sod.rec_id From stockin_order_detail sod
              LEFT JOIN stockin_order so ON sod.stockin_id=so.stockin_id
              LEFT JOIN cfg_warehouse cw ON so.warehouse_id=cw.warehouse_id
              LEFT JOIN goods_spec gs ON gs.spec_id=sod.spec_id
              LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
              LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = sod.position_id
              LEFT JOIN hr_employee he ON he.employee_id = so.operator_id {$where}";

        $sql="select sod.rec_id as id,he.fullname,sod.total_cost,sod.cost_price,{$num},(sod.cost_price+sod.adjust_price) AS right_price,cwp.position_no,sod.src_price,so.adjust_num,so.operator_id,so.remark,so.check_time,so.stockin_no,so.src_order_type,so.logistics_no,cw.name as warehouse_name,gg.goods_name,gg.goods_no,gs.spec_no,gs.spec_name,gs.retail_price,
              CONVERT((sod.cost_price+sod.adjust_price)*(sod.num+sod.adjust_num),DECIMAL(19,4)) AS right_cost,
              (so.total_price+so.adjust_price) AS total_right_price,
              (so.goods_count+so.adjust_num) AS right_num,(sod.num+sod.adjust_num) AS spec_right_num
              From stockin_order_detail sod
              INNER JOIN ({$get_ids_sql}) temp ON temp.rec_id= sod.rec_id
              LEFT JOIN stockin_order so ON sod.stockin_id=so.stockin_id
              LEFT JOIN cfg_warehouse cw ON so.warehouse_id=cw.warehouse_id
              LEFT JOIN goods_spec gs ON gs.spec_id=sod.spec_id
              LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
              LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = sod.position_id
              LEFT JOIN hr_employee he ON he.employee_id = so.operator_id {$where} ORDER BY {$sort}  {$limit}";
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
		return $sql;
	}
}