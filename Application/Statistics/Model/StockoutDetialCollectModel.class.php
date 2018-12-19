<?php
namespace Statistics\Model;

use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;

class StockoutDetialCollectModel extends Model{
    protected $tableName = 'stockout_order_detail';
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
                case 'stockout_no':
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
                    set_search_form_value($where, 'consign_time', $v,'so', 3,' AND ',' >= ');
                    break;
                case 'day_end':
                    set_search_form_value($where, 'consign_time', $v,'so', 3,' AND ',' < ');
            }
        }
    }

    public function loadDataByCondition($page, $rows, $search, $sort, $order){
        $page  = intval($page);
        $rows  = intval($rows);
        $where = ' WHERE so.status>=95 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']==0)unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
        $limit = " limit ".($page - 1) * $rows . "," . $rows;//分页
        $sort  = $sort . " " . $order;
        $sort  = addslashes($sort);
		$get_ids_sql = "";
		$sql = $this->joinsql($where,$sort,$limit,$search,$get_ids_sql);
       
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
        $src_order_type =[
            '1'=>'销售出库',
            '2'=>'调拨出库',
            //'3'=>'采购退货出库',
            '4'=>'盘亏出库',
            //'5'=>'生产出库',
            //'6'=>'现款销售出库',
            '7'=>'其它出库',
            '8'=>'多发出库',
            '9'=>'纠错出库',
            '10'=>'保修配件出库',
            '11'=>'初始化出库'
        ];
        $data=array();
        $where = ' WHERE so.status>=95 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']==0)unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
		$sort = "";
		$limit = "";
		$get_ids_sql = "";
        $sql = $this->joinsql($where,$sort,$limit,$search,$get_ids_sql);
  
		try{
            $arr = array();
            $arr = $this->query($sql);
            $num = workTimeExportNum();
            $amount = array();
            $amount['stockout_no']  =  "合计";
            $row['warehouse_name'] = ' ';
            $row['fullname'] = ' ';
            $row['src_order_type'] = ' ';
            $row['src_order_no'] = ' ';
            $row['logistics_no'] = ' ';
            $row['spec_no'] = ' ';
            $row['spec_name'] =' ';
            $row['spec_code'] = ' ';
            $row['goods_no'] = ' ';
            $row['goods_name'] = ' ';
            $row['class_id'] =' ';
            $row['brand_id'] = ' ';
            $row['position_no'] = ' ';
            $row['consign_time'] = ' ';
            $row['remark'] =' ';
            $row['goods_remark'] = ' ';
            $list = array();
            if(!empty($id_list)){
                $list = explode(",",$id_list);
            }
            foreach($arr as $k=>$v){
                if(!empty($list) && !in_array($v['id'],$list)) {
                    //导出选中数据
                    continue;
                }else {
                    $row['stockout_no'] = $v['stockout_no'];
                    $row['warehouse_name'] = $v['warehouse_name'];
                    $row['fullname'] = $v['fullname'];
                    $row['src_order_type'] = $src_order_type[$v['src_order_type']];
                    $row['src_order_no'] = $v['src_order_no'];
                    $row['logistics_no'] = $v['logistics_no'];
                    $row['spec_no'] = $v['spec_no'];
                    $row['spec_name'] = $v['spec_name'];
                    $row['spec_code'] = $v['spec_code'];
                    $row['goods_no'] = $v['goods_no'];
                    $row['goods_name'] = $v['goods_name'];
                    $row['class_id'] = $v['class_id'];
                    $row['brand_id'] = $v['brand_id'];
                    $row['num'] = $v['num'];
                    $row['position_no'] = $v['position_no'];
                    $row['price'] = $v['price'];
                    $row['total_amount'] = $v['total_amount'];
                    $row['cost_price'] = $v['cost_price'];
                    $row['total_cost_price'] = $v['total_cost_price'];
                    $row['consign_time'] = $v['consign_time'];
                    $row['remark'] = $v['remark'];
                    $row['goods_remark'] = $v['goods_remark'];

                    $amount['num'] += $row['num'];
                    $amount['total_amount'] += $row['total_amount'];
                    $amount['cost_price'] += $row['cost_price'];
                    $amount['price'] += $row['price'];
                    $amount['total_cost_price'] += $row['total_cost_price'];

                    $data[] = $row;
                }
            }
            $data[] = $amount;
            if(count($data)>$num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/StockoutDetialCollect','stockoutdetialcollect');
            $title = '出库单明细表';
            $filename = '出库单明细表';
            $width_list = array('20','25','10','10','20','20',
                                 '25','25','25','30','30','15',
                                 '15','15','15','25','15','15',
                                 '15','20','20','20');
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
	public function joinsql($where,$sort,$limit = "",$search,&$get_ids_sql = ""){
		try{
			 //判断查询区间，是否需要查询历史表
        $old=strtotime('-3month');
        $start=strtotime($search['day_start']);

        //连表的公共sql
        $join_sql=" LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id
				    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
				    LEFT JOIN goods_class gc ON gc.class_id = gg.class_id
				    LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
                  LEFT JOIN cfg_warehouse cw ON so.warehouse_id=cw.warehouse_id
                  LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = sod.position_id
                  LEFT JOIN hr_employee he ON he.employee_id = so.operator_id  ";

        if($start>$old){
            $get_ids_sql="SELECT sod.rec_id
                  FROM stockout_order_detail sod
                  LEFT JOIN stockout_order so ON so.stockout_id=sod.stockout_id
                  {$join_sql}
                  {$where}
                  ";
            $sql="SELECT sod.rec_id as id,gc.class_name AS class_id, gb.brand_name AS brand_id, gs.spec_code,so.stockout_no,so.src_order_no,so.logistics_no,so.consign_time,so.src_order_type,so.logistics_no,so.remark,
                      sod.remark AS goods_remark,sod.cost_price,sod.price,sod.goods_name,sod.goods_no,sod.spec_name,sod.spec_no,sod.num,sod.total_amount,round((sod.cost_price*sod.num),4) AS total_cost_price,
                      he.fullname,cw.name AS warehouse_name,cwp.position_no
                      FROM stockout_order_detail sod
                      INNER JOIN ({$get_ids_sql}) temp ON temp.rec_id= sod.rec_id
                      LEFT JOIN stockout_order so ON so.stockout_id=sod.stockout_id
                      {$join_sql}
                       {$limit}";
        }else{
            $get_new_ids_sql="SELECT sod.rec_id
                FROM stockout_order_detail sod
                LEFT JOIN stockout_order so ON so.stockout_id=sod.stockout_id
                {$join_sql} {$where}";
            $get_old_ids_sql="SELECT sod.rec_id
                FROM stockout_order_detail_history sod
                LEFT JOIN stockout_order_history so ON so.stockout_id=sod.stockout_id
                {$join_sql} {$where}";
            $sql="(SELECT sod.rec_id AS id,gc.class_name AS class_id, gb.brand_name AS brand_id, gs.spec_code,so.stockout_no,so.src_order_no,so.logistics_no,so.consign_time,so.src_order_type,so.logistics_no,so.remark,
                 sod.remark AS goods_remark,sod.cost_price,sod.price,sod.goods_name,sod.goods_no,sod.spec_name,sod.spec_no,sod.num,sod.total_amount,round((sod.cost_price*sod.num),4) AS total_cost_price,
                 he.fullname,cw.name AS warehouse_name,cwp.position_no
                 FROM stockout_order_detail sod
                 INNER JOIN ($get_new_ids_sql) temp ON temp.rec_id= sod.rec_id 
				 LEFT JOIN stockout_order so ON so.stockout_id=sod.stockout_id  
                 {$join_sql})
                 union all
                 (SELECT sod.rec_id AS id,gc.class_name AS class_id, gb.brand_name AS brand_id, gs.spec_code,so.stockout_no,so.src_order_no,so.logistics_no,so.consign_time,so.src_order_type,so.logistics_no,so.remark,
                 sod.remark AS goods_remark,sod.cost_price,sod.price,sod.goods_name,sod.goods_no,sod.spec_name,sod.spec_no,sod.num,sod.total_amount,round((sod.cost_price*sod.num),4) AS total_cost_price,
                 he.fullname,cw.name AS warehouse_name,cwp.position_no
                 FROM stockout_order_detail_history sod
                 INNER JOIN ($get_old_ids_sql) temp ON temp.rec_id= sod.rec_id
                 LEFT JOIN stockout_order_history so ON so.stockout_id=sod.stockout_id
                 {$join_sql})  {$limit}";
			$get_ids_sql = "({$get_new_ids_sql}) union all ({$get_old_ids_sql})"; 
        }
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