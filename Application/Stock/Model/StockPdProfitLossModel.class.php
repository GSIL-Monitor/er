<?php
namespace Stock\Model;
use Common\Common\ExcelTool;
use Think\Model;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;

class StockPdProfitLossModel extends Model
{
    protected $tableName = 'stock_pd_detail';//'stat_sales_daysell';
    protected $pk = 'rec_id';

    public function searchFormDeal(&$where,$search){
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k => $v) {
            if ($v === '') continue;
                 switch ($k) {
                    case "creator_id":
                        set_search_form_value($where, $k, $v, 'sp',2, 'AND');
                        break;
                    case "pd_no":
                        set_search_form_value($where, $k, $v, 'sp', 1, 'AND');
                        break;
                    case "goods_name":
                        set_search_form_value($where, $k, $v, 'gg', 1, 'AND');
                        break;
                    case "class_id":
                        set_search_form_value($where, $k, $v, 'gc_1', 7, 'AND');
                        break;
                    case "brand_id":
                        set_search_form_value($where, $k, $v, 'gg', 2, 'AND');
                        break;
                    case "warehouse_id":
                        set_search_form_value($where, $k, $v, 'sp', 2, 'AND');
                        break;
                    case "day_start":{
                        set_search_form_value($where, 'created', $v,'spd',3,'AND','>=');
                        break;
                        }
                    case "day_end":{
                        set_search_form_value($where, 'created', $v,'spd',3,'AND','<=');
                        break;
                        }
                    default:
                        \Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
                        break;
            }
        }
    }

    public function loadDataByCondition($page=1, $rows=20, $search = array(), $sort = 'pd_id', $order = 'desc')
    {
        $page  = intval($page);
        $rows  = intval($rows);
        $sort  = addslashes($sort);
        $order = addslashes($order);
        $where = 'true ';

        $this->searchFormDeal($where,$search);

		$point_number = get_config_value('point_number',0);        
		$old_num = 'CAST(spd.old_num AS DECIMAL(19,'.$point_number.')) old_num';
		$new_num = 'CAST(spd.new_num AS DECIMAL(19,'.$point_number.')) new_num';
		$pd_num = 'CAST((spd.new_num - spd.old_num) AS DECIMAL(19,'.$point_number.'))  yk_num';		
		$sort = 'pd_id';
        $fields = array('sp.rec_id as id',
                        'sp.pd_no',
                        'sp.creator_id',
                        'he.fullname',
                        'sp.created',
                        'sp.warehouse_id',
                        'gg.goods_no',
                        'gg.goods_name',
                        'gs.spec_code',
                        'gs.spec_name',
                        $old_num,
                        $new_num,
                        $pd_num,
                        'cwp.position_no',
                        '(spd.new_num-spd.old_num)*spd.cost_price AS total_price',
                        'gg.class_id',
                        'gg.brand_id',
                        'spd.created',
                        'gs.spec_no',
                        'cw.name',
                        'sp.remark'
                        );
        $data = $this->getSearchlist($fields,$where,$page,$rows,'spd',$sort,$order);

        return $data;
    }



    public function getSearchlist($fields,$where,$page,$rows,$alias,$sort,$order)
    {
        try {

            $res_data = $this->alias('spd')->field($fields)
                    ->join('left join stock_pd sp on sp.rec_id=spd.pd_id
                    left join goods_spec gs on gs.spec_id=spd.spec_id
                    left join goods_goods gg on gg.goods_id=gs.goods_id
                    left join goods_class gc_1 on gg.class_id=gc_1.class_id
                    left join cfg_warehouse_position cwp on cwp.rec_id=spd.position_id
                    left join cfg_warehouse cw on sp.warehouse_id=cw.warehouse_id
                    left join hr_employee he on sp.creator_id=he.employee_id'
                    )
            ->where($where)->order($sort.' '.$order)->page($page,$rows)->select();
            $total = $this->alias('spd')->field($fields)
                    ->join('left join stock_pd sp on sp.rec_id=spd.pd_id
                    left join goods_spec gs on gs.spec_id=spd.spec_id
                    left join goods_goods gg on gg.goods_id=gs.goods_id
                    left join goods_class gc_1 on gg.class_id=gc_1.class_id
                    left join cfg_warehouse_position cwp on cwp.rec_id=spd.position_id
                    left join cfg_warehouse cw on sp.warehouse_id=cw.warehouse_id
                    left join hr_employee he on sp.creator_id=he.employee_id'
                    )->where($where)->count();

            $data=array('total'=>$total,'rows'=>$res_data);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockPdProfitLossList-'.$msg);
            $data=array('total'=>0,'rows'=>array());
        }

        return $data;
    }



    public function exportToExcel($search,$type = 'excel'){
        $user_id = get_operator_id();
        $creator=session('account');
        try{
            $where='1 ';
            $this->searchFormDeal($where,$search);
            $sort = 'pd_id';
            $fields = array(
                'sp.pd_no','cw.name','cwp.position_no','gs.spec_no','gg.goods_no','gg.goods_name','gs.spec_code','gs.spec_name','spd.old_num','spd.new_num','(spd.new_num-spd.old_num) AS yk_num','(new_num-old_num)*spd.cost_price AS total_price','he.fullname','sp.remark'
            );
            $res = $this->getSearchlist($fields,$where,$page=1,$rows=1000,'spd',$sort,$order='desc');
            $total = $res['total'];
            $num = workTimeExportNum($type);
            if($total>$num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }

            $amount = array();
            $amount['pd_no']  =  "合计";
            foreach($res['rows'] as $k=>$v){
                $amount['old_num'] += $v['old_num'];
                $amount['new_num'] += $v['new_num'];
                $amount['yk_num'] += $v['yk_num'];
                $amount['total_price'] += $v['total_price'];
            }
            $res['rows'][] = $amount;

            $excel_header = D('Setting/UserData')->getExcelField('Stock/StockPdProfitLoss','pd_profit_loss');
            $width_list = array();
            foreach ($excel_header as $v)
            {
                $width_list[]=20;
            }
            $title = '盘点盈亏统计';
            $filename = '盘点盈亏统计';
            if($type == 'csv') {
                $ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','仓库','货位','盘点人','分类','品牌');
                ExcelTool::Arr2Csv($res['rows'], $excel_header, $filename, $ignore_arr);
            }else {
                ExcelTool::Arr2Excel($res['rows'], $title, $excel_header, $width_list, $filename, $creator);
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
    }



    public function getStockPdProfitLossTabInfo($id)
    {
        try {
			$point_number = get_config_value('point_number',0);			
			$old_num = 'CAST(spd.old_num AS DECIMAL(19,'.$point_number.')) old_num';
			$new_num = 'CAST(spd.new_num AS DECIMAL(19,'.$point_number.')) new_num';
			$yk_num = 'CAST((spd.new_num-spd.old_num) AS DECIMAL(19,'.$point_number.')) yk_num';			
            $pd_detail_fields = array(
                'gs.spec_no',
                'gs.barcode',
                'gg.goods_no',
                'goods_name',
                'gs.spec_code',
                'gs.spec_name',
                'cwp.position_no',
                $old_num,
                $new_num,
                $yk_num,
                'CAST((new_num-old_num)*cost_price AS DECIMAL(19,4)) total_price',
                'spd.remark',
            );
            $pd_detail_cond = array(
                "spd.pd_id"=>$id,
            );

            $data_res = $this->alias('spd')->field($pd_detail_fields)->join("left join stock_pd sp on sp.rec_id = spd.pd_id")->join("left join goods_spec gs on gs.spec_id = spd.spec_id")->join("left join goods_goods gg on gs.goods_id = gg.goods_id")->join("left join cfg_warehouse_position cwp on cwp.rec_id = spd.position_id")->where($pd_detail_cond)->select();
            $data['rows'] = $data_res;
            $data['total'] = $this->alias('spd')->field($pd_detail_fields)->join("left join stock_pd sp on sp.rec_id = spd.pd_id")->join("left join goods_spec gs on gs.spec_id = spd.spec_id")->join("left join goods_goods gg on gs.goods_id = gg.goods_id")->join("left join cfg_warehouse_position cwp on cwp.rec_id = spd.position_id")->where($pd_detail_cond)->count();
        } catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-getStockPdProfitLossTabInfo-".$msg);
            $data = array('total' => '0', 'rows' => array());
        }
        return $data;
    }

}