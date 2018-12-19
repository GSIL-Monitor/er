<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;

class StockinCollectModel extends Model{
    protected $tableName = 'stockin_order_detail ';
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
                    set_search_form_value($where, 'modified', $v,'so', 3,' AND ',' >= ');
                    break;
                case 'day_end':
                    set_search_form_value($where, 'modified', $v,'so', 3,' AND ',' < ');
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
        $count_sql="select gs.spec_id from stockin_order_detail sod left join stockin_order so on so.stockin_id=sod.stockin_id  left join goods_spec gs on gs.spec_id=sod.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id {$where} group by gs.spec_id;";
		$sql = $this->joinsql($where,$sort,$limit);
		try{
            $data['rows'] = $this->query($sql);
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
        $where = ' WHERE so.status=80 ';
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        if($search['src_order_type']==0)unset($search['src_order_type']);
        $this->searchFormDeal($where,$search);
        $limit = " ";
		$sort = " gs.spec_id desc ";
		$sql = $this->joinsql($where,$sort,$limit);
		try{
            $arr = array();
            $arr = $this->query($sql);
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
                }else{
                    $row['spec_no'] = $v['spec_no'];
                    $row['goods_no'] = $v['goods_no'];
                    $row['goods_name'] = $v['goods_name'];
                    $row['spec_name'] = $v['spec_name'];
                    $row['num'] = $v['num'];
                    $row['total_cost'] = $v['total_cost'];
                    $row['price'] = $v['price'];

                    $amount['num'] += $row['num'];
                    $amount['total_cost'] += $row['total_cost'];
                    $amount['price'] += $row['price'];

                    $data[] = $row;
                }
            }
            $data[] = $amount;
            if(count($data)>$num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/StockinCollect','stockincollect');
            $title = '入库库单汇总表';
            $filename = '入库单汇总表';
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
	
	public function joinsql($where,$sort,$limit = ""){
		try{
			$sql="select sod.rec_id as id,sum(sod.num) as num,sum(sod.total_cost) as total_cost,round(sum(sod.total_cost)/sum(sod.num),4) as price,gg.goods_name,gg.goods_no,gs.spec_no,gs.spec_name from stockin_order_detail sod left join stockin_order so on so.stockin_id=sod.stockin_id left join goods_spec gs on gs.spec_id=sod.spec_id left join goods_goods gg on gg.goods_id=gs.goods_id {$where} group by gs.spec_id ORDER BY {$sort} ".$limit;
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