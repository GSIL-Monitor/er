<?php
namespace Account\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
use Think\Log;
use Common\Common\ExcelTool;
use Common\Common\DatagridExtention;
use Think\Exception;

class LogisticsFeeManagementModel extends Model {
    protected $tableName = 'fa_logistics_fee';
    protected $pk = 'order_id';

    public function getLogisticsFeeList($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc',&$data){
        try{
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            $page = intval($page);
            if(is_array($rows) && $rows['type']=='export')
            {
                $num = $rows['num'];
                $type = $rows['type'];
            }else
            {
                $type = 'list';
                $rows = intval($rows);
            }
            $where = 'true ';
            foreach($search as $k=>$v){
                if($v == '') continue;
                switch($k){
                    case 'logistics_id':
                        set_search_form_value($where,$k,$v,'ff',2,'AND');
                        break;
                    case 'logistics_no':
                        set_search_form_value($where,$k,$v,'ff',1,'AND');
                        break;
                    case 'shop_id':
                        set_search_form_value($where,$k,$v,'ff',2,'AND');
                        break;
                    case 'warehouse_id':
                        set_search_form_value($where,$k,$v,'ff',2,'AND');
                        break;
                    case 'start_time':
                        set_search_form_value($where, 'created', $v,'ff', 3,' AND ',' >= ');
                        break;
                    case 'end_time':
                        set_search_form_value($where, 'created', $v,'ff', 3,' AND ',' <= ');
                        break;
                    case 'rec_id':
                        $where=$where.' AND '.$k.' in('.addslashes($v).') ';
                        break;
                    default:
                        continue;
                }
            }

            $order = $sort . " " . $order;
            $order = addslashes($order);
            $data_sql = "SELECT ff.rec_id as id,ff.logistics_no,ff.status,cl.logistics_name AS logistics_id,ff.weight,ff.import_weight,(ff.import_weight-ff.weight) AS weight_diff,ff.postage,ff.import_postage,
                        (ff.import_postage-ff.postage) AS postage_diff,he1.fullname AS make_oper_id,he2.fullname AS charge_oper_id,ff.charge_time,ff.modified,ff.created,ff.area,cs.shop_name,cw.name AS warehouse_name
                        FROM fa_logistics_fee ff
                        LEFT JOIN cfg_logistics cl ON cl.logistics_id = ff.logistics_id
                        LEFT JOIN hr_employee he1 ON he1.employee_id = ff.make_oper_id
                        LEFT JOIN hr_employee he2 ON he2.employee_id = ff.charge_oper_id
                        LEFT JOIN cfg_warehouse cw ON ff.warehouse_id = cw.warehouse_id
                        LEFT JOIN cfg_shop cs ON ff.shop_id = cs.shop_id
                        WHERE $where";

            if($type =='export'){
                $limit = $num;
                $data['rows'] = $this->query($data_sql." ORDER BY $order LIMIT $limit");
            }else{
                $limit=($page - 1) * $rows . "," . $rows;//分页
                $data['rows'] = $this->query($data_sql." ORDER BY $order LIMIT $limit");
                /*foreach ($data['rows'] as $k => $v) {
                    $id = intval($v['id']);
                    $goods_list = $this->getGoodsList($id)['rows'];
                    $goods_arr = [];
                    $max_num = count($goods_list)>4?4:count($goods_list);
                    for($index=0;$index<$max_num;$index++){
                        $goods_arr[] = $goods_list[$index]['goods_name'];
                    }
                    $data['rows'][$k]['goods_str'] = join(',', $goods_arr);
                    $data['rows'][$k]['goods_str'] .= count($goods_list)>4?' 等':'';
                }*/
            }

            $count_sql = "SELECT COUNT(1) AS total FROM ($data_sql)tmp_1";
            $res_total = $this->query($count_sql);
            $data['total'] = $res_total[0]['total'];
        }catch(\PDOException $e){
            SE(parent::PDO_ERROR);
            Log::write($e->getMessage());
        }catch (\Exception $e) {
            SE($e->getMessage());
            Log::write($e->getMessage());
        }
        return $data;
    }

    public function exportToExcel($search, $type){
        $creator=session('account');
        $logistics_status =[
            '1'=>'已结算',
            '2'=>'已冲销',
            '0'=>'待结算'
        ];
        try{
            $count=D('LogisticsFeeManagement')->getOutputCount($search);
            $num = workTimeExportNum($type);
            if($count>$num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }
            $rows['num'] = $num;
            $rows['type'] = 'export';
            $data = $this->getLogisticsFeeList(1, $rows, $search, 'rec_id','desc');
            $row=array();
            $arr=array();
            $count_postage = 0;
            $count_import_postage = 0;
            foreach($data['rows'] as $k=>$v){
                $row['logistics_no']=$v['logistics_no'];
                $row['status']=$logistics_status[$v['status']];
                $row['logistics_id']=$v['logistics_id'];
                $row['area']=$v['area'];
                $row['shop_name']=$v['shop_name'];
                $row['warehouse_name']=$v['warehouse_name'];
                $row['weight']=$v['weight'];
                $row['import_weight']=$v['import_weight'];
                $row['weight_diff']=$v['weight_diff'];
                //$row['goods_str']=$v['goods_str'];
                $row['postage']=$v['postage'];
                $row['import_postage']=$v['import_postage'];
                $row['postage_diff']=$v['postage_diff'];
                $row['make_oper_id']=$v['make_oper_id'];
                $row['charge_oper_id']=$v['charge_oper_id'];
                $row['charge_time']=$v['charge_time'];
                $row['modified']=$v['modified'];
                $row['created']=$v['created'];
                $arr[]=$row;
                $count_postage +=  $v['postage'];
                $count_import_postage +=  $v['import_postage'];
            }
            $blank = [
                'logistics_no'  =>  '',
                'status'        =>  '',
                'logistics_id'  =>  '',
                'area'          =>  '',
                'shop_name'     =>  '',
                'warehouse_name'=>  '',
                'weight'        =>  '',
                'import_weight' =>  '',
                'weight_diff'   =>  '',
                //'goods_str'     =>  '',
                'postage'       =>  '',
                'import_postage'=>  '',
                'postage_diff'  =>  '',
                'make_oper_id'  =>  '',
                'charge_oper_id'=>  '',
                'charge_time'   =>  '',
                'modified'      =>  '',
                'created'       =>  ''
            ];
            $arr[]=$blank;
            $sum = [
                'logistics_no'  =>  '合计',
                'status'        =>  '',
                'logistics_id'  =>  '',
                'area'          =>  '',
                'shop_name'     =>  '',
                'warehouse_name'=>  '',
                'weight'        =>  '',
                'import_weight' =>  '',
                'weight_diff'   =>  '',
                //'goods_str'     =>  '',
                'postage'       =>  $count_postage,
                'import_postage'=>  $count_import_postage,
                'postage_diff'  =>  '',
                'make_oper_id'  =>  '',
                'charge_oper_id'=>  '',
                'charge_time'   =>  '',
                'modified'      =>  '',
                'created'       =>  ''
            ];

            $arr[]=$sum;

            $title = '物流资费';
            $filename = '物流资费';
            $excel_header = D('Setting/UserData')->getExcelField('Account/LogisticsFeeManagement','logistics_fee_management');
            $width_list = array(
                '20','10','20','25','20',
                '20','15','15','15','15','15',
                '15','15','10','10','18',
                '18','18');
            if($type == 'csv') {
                ExcelTool::Arr2Csv($arr, $excel_header, $filename);
            }else {
                ExcelTool::Arr2Excel($arr, $title, $excel_header, $width_list, $filename, $creator);
            }
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }


    public function getOutputCount($search){
        $where = 'true ';
        foreach($search as $k=>$v){
            if($v == '') continue;
            switch($k){
                case 'logistics_id':
                    set_search_form_value($where,$k,$v,'ff',2,'AND');
                    break;
                case 'logistics_no':
                    set_search_form_value($where,$k,$v,'ff',1,'AND');
                    break;
                case 'shop_id':
                    set_search_form_value($where,$k,$v,'ff',2,'AND');
                    break;
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'ff',2,'AND');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'created', $v,'ff', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'created', $v,'ff', 3,' AND ',' <= ');
                    break;
                case 'rec_id':
                    $where=$where.' AND '.$k.' in('.addslashes($v).') ';
                    break;
                default:
                    continue;
            }
        }
        $data_sql = "SELECT count(ff.logistics_no) as ids FROM fa_logistics_fee ff
                        LEFT JOIN cfg_logistics cl ON cl.logistics_id = ff.logistics_id
                        LEFT JOIN hr_employee he1 ON he1.employee_id = ff.make_oper_id
                        LEFT JOIN hr_employee he2 ON he2.employee_id = ff.charge_oper_id
                        LEFT JOIN cfg_warehouse cw ON ff.warehouse_id = cw.warehouse_id
                        LEFT JOIN cfg_shop cs ON ff.shop_id = cs.shop_id
                        WHERE $where";
        $number = $this->query($data_sql);
        return $number[0]['ids'];
    }

    /*获取物流资费tab中货品信息（订单货品）
     * $id 物流资费id
     * */
    function getGoodsList($id){
        $id=intval($id);
        $data=array();
        try {
            $res_logisticsFee_arr=M('fa_logistics_fee')->field('warehouse_id,logistics_id,logistics_no')->where(array('rec_id'=>array('eq',$id)))->find();
            //组织字段
            $point_number = get_config_value('point_number',0);
            $sys_available_stock = get_config_value('sys_available_stock',640);
            $stock=D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
            $num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
            $actual_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
            $stock_num_all = "CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,".$point_number.")) stock_num_all";
            $stock_num = "CAST(".$stock." AS DECIMAL(19,".$point_number.")) stock_num";
            $suite_num = "CAST(IF(sto.suite_num,sto.suite_num,'') AS DECIMAL(19,".$point_number.")) suite_num";

            //查询订单表中的货品
            $sto_ids="SELECT trade_id as id FROM sales_trade where logistics_id={$res_logisticsFee_arr['logistics_id']} and logistics_no={$res_logisticsFee_arr['logistics_no']}";
            $sto_list=$this->query($sto_ids);
            $str_id='';
            foreach($sto_list as $row){
                $str_id.=$str_id==''?$row['id']:','.$row['id'];
            }
            if(!empty($str_id))
            {
                $sto_sql="SELECT sto.rec_id, sto.spec_id, sto.platform_id, sto.goods_name, sto.spec_id,sto.spec_name, sto.src_tid,
				  sto.src_oid, sto.spec_no, sto.goods_no, sto.spec_code, sto.price, sto.order_price, sto.share_price,
				  sto.discount,ss.cost_price, ".$num.", ".$actual_num.", ".$stock_num_all.",
				  ".$stock_num.", sto.share_amount, sto.share_post, sto.paid,
				  sto.commission, sto.suite_name, ".$suite_num.", sto.suite_no, sto.weight,
				  sto.guarantee_mode, sto.refund_status, sto.gift_type, sto.invoice_type, sto.api_goods_name, sto.api_spec_name,
				  sto.remark FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND
				  ss.warehouse_id=".intval($res_logisticsFee_arr['warehouse_id'])."  WHERE sto.trade_id in(".$str_id.") ORDER BY sto.refund_status,sto.rec_id ASC";
                $sales_trade_order_data=$this->query($sto_sql);

            }
            //查询历史订单中的货品
            $stoh_ids="SELECT trade_id as id FROM sales_trade_history where logistics_id={$res_logisticsFee_arr['logistics_id']} and logistics_no={$res_logisticsFee_arr['logistics_no']}";
            $stoh_list=$this->query($stoh_ids);
            foreach($stoh_list as $row){
                $str_id.=$str_id==''?$row['id']:','.$row['id'];
            }
            if(!empty($str_id))
            {
                $stoh_sql="SELECT sto.rec_id, sto.spec_id, sto.platform_id, sto.goods_name, sto.spec_id,sto.spec_name, sto.src_tid,
                sto.src_oid, sto.spec_no, sto.goods_no, sto.spec_code, sto.price, sto.order_price, sto.share_price,
                sto.discount,ss.cost_price, ".$num.", ".$actual_num.", ".$stock_num_all.",
                ".$stock_num.", sto.share_amount, sto.share_post, sto.paid,
                sto.commission, sto.suite_name, ".$suite_num.", sto.suite_no, sto.weight,
                sto.guarantee_mode, sto.refund_status, sto.gift_type, sto.invoice_type, sto.api_goods_name, sto.api_spec_name,
                sto.remark FROM sales_trade_order_history sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND
                ss.warehouse_id=".intval($res_logisticsFee_arr['warehouse_id'])."  WHERE sto.trade_id in(".$str_id.") ORDER BY sto.refund_status,sto.rec_id ASC";
                $sales_trade_order_history_data=$this->query($stoh_sql);
            }


            //合并数组
            $data=array_merge($sales_trade_order_data,$sales_trade_order_history_data);
            $data=array('total'=>count($data),'rows'=>$data);
        }catch(\PDOException $e)
        {
            $data=array('total'=>0,'rows'=>array());
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }
}