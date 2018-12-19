<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

class StockoutCollectModel extends Model{
    protected $tableName = 'stockout_order_detail';
    protected $pk = 'rec_id' ;

    public function searchFormDeal(&$where,$search){
        foreach($search as $k=>$v){
            if (!isset($v)) continue;
            switch($k){
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'so',2,'AND');
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
                case 'spec_name':
                    set_search_form_value($where,$k,$v,'gs',1,'AND');
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
        if($search['src_order_type']=='all')unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
        $limit = " limit ".($page - 1) * $rows . "," . $rows;//分页
        $sort  = $sort . " " . $order;
        $sort  = addslashes($sort);
		$sql = "";
        $data_sql = $this->joinsql($where,$sort,$limit,$search,$sql);

  	    $count_sql="select count(*) from ({$sql}) temp group by spec_id;";

        try{
            $data['rows'] = $this->query($data_sql);
            $count =$this->query($count_sql);
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
        $data=array();
        $where = ' WHERE so.status>=95 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']=='all')unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
        $sort = " spec_id desc ";
	    $limit = "";
	    $sql = "";
	    $data_sql = $this->joinsql($where,$sort,$limit,$search,$sql);

	   try{
            $arr = array();
            $arr = $this->query($data_sql);
            $num = workTimeExportNum();
            $amount = array();
            $amount['spec_no']  =  "合计";
            $amount['goods_no']  =  ' ';
            $amount['goods_name'] = ' ';
            $amount['spec_name'] = ' ';
            $list = array();
            if(!empty($id_list)){
               $list = explode(",",$id_list);
            }
            foreach($arr as $k=>$v){
               if(!empty($list) && !in_array($v['id'],$list)) {
                   //导出选中数据
                   continue;
               }else {
                   $row['spec_no'] = $v['spec_no'];
                   $row['goods_no'] = $v['goods_no'];
                   $row['goods_name'] = $v['goods_name'];
                   $row['spec_name'] = $v['spec_name'];
                   $row['num'] = $v['num'];
                   $row['total_price'] = $v['total_price'];
                   $row['cost_price'] = $v['cost_price'];

                   $amount['num'] += $row['num'];
                   $amount['total_price'] += $row['total_price'];
                   $amount['cost_price'] += $row['cost_price'];

                   $data[] = $row;
               }
            }
            $data[] = $amount;
           if(count($data)>$num){
               SE(self::OVER_EXPORT_ERROR);
           }
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/StockoutCollect','stockoutcollect');
            $title = '出库单汇总表';
            $filename = '出库单汇总表';
            $width_list = array('25','25','30','30','15','15','15');
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
	
	public function joinsql($where,$sort,$limit = "",$search,&$sql = ""){
		try{
			//判断查询区间，是否需要查询历史表
			$old=strtotime('-3month');
			$start=strtotime($search['day_start']);
			if($start>$old){
				$sql="select sod.rec_id as id,sum(sod.total_amount) as total_price,sum(sod.num) as num,gg.goods_name,gg.goods_no,gs.spec_id,gs.spec_no,gs.spec_name,round(sum(sod.cost_price*sod.num),4) as cost_price from stockout_order_detail sod left join stockout_order so on so.stockout_id=sod.stockout_id left join goods_spec gs on gs.spec_id=sod.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id {$where} group by gs.spec_id";
			}else{
				$sql="(select sod.rec_id as id,sum(sod.total_amount) as total_price,sum(sod.num) as num,gg.goods_name,gg.goods_no,gs.spec_no,gs.spec_id,gs.spec_name,round(sum(sod.cost_price*sod.num),4) as cost_price from stockout_order_detail_history sod left join stockout_order_history so on so.stockout_id=sod.stockout_id left join goods_spec gs on gs.spec_id=sod.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id {$where} group by gs.spec_id) union all (select sod.rec_id as id,sum(sod.total_amount) as total_price,sum(sod.num) as num,gg.goods_name,gg.goods_no,gs.spec_no,gs.spec_id,gs.spec_name,round(sum(sod.cost_price*sod.num),4) as cost_price from stockout_order_detail sod left join stockout_order so on so.stockout_id=sod.stockout_id  left join goods_spec gs on gs.spec_id=sod.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id {$where} group by gs.spec_id)";
			}
			$data_sql="select id,sum(total_price) as total_price,sum(num) as num,goods_name,goods_no,spec_no,spec_name,sum(cost_price) as cost_price,spec_id from ({$sql}) temp group by spec_id order by $sort  $limit;";
			
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
		return $data_sql;
	}

}