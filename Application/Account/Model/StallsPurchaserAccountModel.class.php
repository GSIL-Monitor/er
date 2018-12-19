<?php
namespace Account\Model;

use Think\Log;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class StallsPurchaserAccountModel extends Model
{
    protected $tableName = 'operator_stalls_pickup_log';
    protected $pk = 'rec_id';

    public function getStallsPurchaserAccountList($page=1, $rows=20, $search = array(), $sort = 'ospl.rec_id', $order = 'desc')
    {
        try
        {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' .$order;
            $this->searchFormDeal($where,$search);
            $limit = ($page - 1) * $rows . "," . $rows;
            $id_where = addslashes($where);
            //档口采购单详情中已完成和 编辑中已拿货的
            $sql = "SELECT DISTINCT DATE(ospl.pickup_time) pickup_date,IFNULL(SUM(sgd.stockin_status),0) in_num,IFNULL(SUM(sgd.pickup_status),0) put_num,
                    pp.provider_name,he.fullname purchaser_name,IFNULL(SUM(sgd.price),0) total_price,ospl.operator_id,CONCAT(ospl.operator_id,',',pp.id,',','{$id_where}',',',DATE(ospl.pickup_time)) id
                    FROM operator_stalls_pickup_log ospl
                    LEFT JOIN stalls_less_goods_detail sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN hr_employee he ON ospl.operator_id = he.employee_id
                    LEFT JOIN purchase_provider pp ON sgd.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
                    WHERE $where AND cw.type != 127
                    GROUP BY ospl.operator_id,pickup_date,ospl.purchase_id
                    ORDER BY $order LIMIT $limit";
            $sql_total = "SELECT DISTINCT DATE(ospl.pickup_time) pickup_date,IFNULL(SUM(sgd.stockin_status),0) in_num,IFNULL(SUM(sgd.pickup_status),0) put_num,
                    pp.provider_name,he.fullname purchase_name,IFNULL(SUM(sgd.price),0) total_price,ospl.operator_id
                    FROM operator_stalls_pickup_log ospl
                    LEFT JOIN stalls_less_goods_detail sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN hr_employee he ON ospl.operator_id = he.employee_id
                    LEFT JOIN purchase_provider pp ON sgd.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
                    WHERE $where AND cw.type != 127
                    GROUP BY operator_id,pickup_date";
            $data["total"] = count($this->query($sql_total));
            $data["rows"] = $this->query($sql);

        }catch(\PDOException $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $data;
    }

    public function getPurchaserGoodsDetail($id)
    {
        try
        {
            $data = explode(',',$id);
            $operator_id = $data[0];
            $provider_id = $data[1];
            $where = $data[2];
            $date = $data[3];
            $tmp_arr = explode(' and ', $where);
            foreach($tmp_arr as $k=>$v){
                if(strpos($v,'he.employee_id') !== false || strpos($v,'pp.id') !== false){
                    unset($tmp_arr[$k]);
                }
            }
            $where = implode(' and ', $tmp_arr);
            $sql = "SELECT so.stalls_no,gg.goods_no,gg.goods_name,gs.spec_name,gs.spec_no,IFNULL(SUM(sgd.stockin_status),0) in_num,
                    IFNULL(SUM(sgd.pickup_status),0) put_num,ospl.pickup_time,IFNULL(SUM(sgd.price),0) total_price,ospl.pickup_time purchase_time
                    FROM operator_stalls_pickup_log ospl
                    LEFT JOIN stalls_less_goods_detail sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN stalls_order so ON sgd.stalls_id = so.stalls_id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
                    WHERE DATE(ospl.pickup_time)='{$date}' AND ospl.operator_id = $operator_id AND ospl.purchase_id = $provider_id AND $where AND cw.type != 127
                    GROUP BY gs.spec_id,ospl.pickup_time";
            $data['rows'] = $this->query($sql);

        }catch (\PDOException $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
        catch (\Exception $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $data;
    }

    public function searchFormDeal(&$where, $search){
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search, 'shop_id', 1);
        foreach($search as $k => $v){
            if($v === "") continue;
            switch($k){
                case "goods_name":
                    //set_search_form_value($where, $k, $v, 'ag', 6, "AND");
                    //  $res = UtilTool::check_search_form_value($v, 1);

                    $res = trim($v);
                    if(empty($v) && ($v !== 0) && ($v !== '0')){
                        return false;
                    }
                    $res=strtr($res,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\'));
                    $res = addslashes($res);
                    $where = $res === false ? $where : $where . " and gg." . $k . " LIKE '%" . $res . "%'";
                    break;
                case 'purchaser_name':
                    set_search_form_value($where,'employee_id',$v,'he',2,'and');
                    break;
                case 'goods_no'://goods_goods
                    set_search_form_value($where, $k, $v, 'gg', 1, 'and');
                    break;
                case 'spec_no':
                    set_search_form_value($where, $k, $v, 'gs', 10, 'and');
                    break;
                case 'provider': //供货商
                    set_search_form_value($where,'id',$v,'pp',2,'and');
                    break;
                case 'brand_id':
                    set_search_form_value($where, $k, $v, 'gg', 2, 'and');
                    break;
                case 'class_id'://分类         goods_gooods
                    set_search_form_value($where, $k, $v, 'gg', 7, 'and');
                    break;
                case 'purchaser_start_time':
                    set_search_form_value($where, 'pickup_time', $v,'ospl', 4,'and',' >= ');
                    break;
                case 'purchaser_end_time':
                    set_search_form_value($where, 'pickup_time', $v,'ospl', 4,'and',' <= ');
                    break;
                case 'rec_id':
                    $where=$where.'and'.$k.' in('.addslashes($v).') ';
                    break;
                default:
                    continue;
            }
        }
    }
    public function exportToExcel($search){
        $creator = session('account');
        $excel_no = array();
        try {
            $where = "true ";
            $this->searchFormDeal($where,$search);
            $sql = "SELECT DISTINCT DATE(ospl.pickup_time) pickup_date,IFNULL(SUM(sgd.stockin_status),0) in_num,IFNULL(SUM(sgd.pickup_status),0) put_num,
                        pp.provider_name,he.fullname purchaser_name,IFNULL(SUM(sgd.price),0) total_price,ospl.operator_id,pp.id
                        FROM operator_stalls_pickup_log ospl
                        LEFT JOIN stalls_less_goods_detail sgd ON sgd.unique_code = ospl.unique_code
                        LEFT JOIN hr_employee he ON ospl.operator_id = he.employee_id
                        LEFT JOIN purchase_provider pp ON sgd.provider_id = pp.id
                        LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                        LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                        LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
                        WHERE $where AND cw.type != 127
                        GROUP BY ospl.operator_id,pickup_date,ospl.purchase_id
                        ORDER BY ospl.rec_id desc";
            $stalls_purchaser_data = $this->query($sql);
            if(count($stalls_purchaser_data) == 0){
                SE('导出数据不能为空，请重新搜索后导出');
            }
            $total = 0;
            $tmp_arr = explode(' and ', $where);
            foreach($tmp_arr as $k=>$v){
                if(strpos($v,'he.employee_id') !== false || strpos($v,'pp.id') !== false){
                    unset($tmp_arr[$k]);
                }
            }
            $where = implode(' and ', $tmp_arr);
            foreach($stalls_purchaser_data as $k=>$v){
                $provider_id = $v['id'];
                $operator_id = $v['operator_id'];
                $date = $v['pickup_date'];
                $stalls_purchaser_data[$k]['goods_detail'] = $this->query("SELECT so.stalls_no,gg.goods_no,gg.goods_name,gs.spec_name,gs.spec_no,IFNULL(SUM(sgd.stockin_status),0) in_num,
                    IFNULL(SUM(sgd.pickup_status),0) put_num,ospl.pickup_time,IFNULL(SUM(sgd.price),0) total_price,ospl.pickup_time purchase_time
                    FROM operator_stalls_pickup_log ospl
                    LEFT JOIN stalls_less_goods_detail sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN stalls_order so ON sgd.stalls_id = so.stalls_id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = sgd.warehouse_id
                    WHERE DATE(ospl.pickup_time)='{$date}' AND ospl.operator_id = $operator_id AND ospl.purchase_id = $provider_id AND $where AND cw.type != 127
                    GROUP BY gs.spec_id,ospl.pickup_time");
                $total += count($stalls_purchaser_data[$k]['goods_detail']);
            }
            $num = workTimeExportNum();
            if ($total > $num) {
                SE(self::OVER_EXPORT_ERROR);
            }
            $data = array();
            $merge_arr = array();
            foreach ($stalls_purchaser_data as $k => $v) {
                $tmp_count = count($v['goods_detail']);
                $merge_arr['purchaser_name'][] = $tmp_count;
                $merge_arr['provider_name'][] = $tmp_count;
                $merge_arr['in_num'][] = $tmp_count;
                $merge_arr['put_num'][] = $tmp_count;
                $merge_arr['total_price'][] = $tmp_count;
                $merge_arr['pickup_date'][] = $tmp_count;
                foreach($v['goods_detail'] as $gk=>$gv){
                    $row = array();
                    $row['purchaser_name'] = $v['purchaser_name'];
                    $row['provider_name'] = $v['provider_name'];
                    $row['in_num'] = $v['in_num'];
                    $row['put_num'] = $v['put_num'];
                    $row['total_price'] = $v['total_price'];
                    $row['pickup_date'] = $v['pickup_date'];
                    $row['stalls_no'] = $gv['stalls_no'];
                    $row['goods_no'] = $gv['goods_no'];
                    $row['goods_name'] = $gv['goods_name'];
                    $row['spec_no'] = $gv['spec_no'];
                    $row['s_in_num'] = $gv['in_num'];
                    $row['s_put_num'] = $gv['put_num'];
                    $row['s_total_price'] = $gv['total_price'];
                    $row['purchase_time'] = $gv['purchase_time'];
                    $data[] = $row;
                }
            }
            $total_keys_arr = array_keys($data[0]);
            foreach ($total_keys_arr as $k => $v) {
                switch ($v) {
                    case 'purchaser_name':
                        $excel_no['purchaser_name'] = '采购员';
                        break;
                    case 'provider_name':
                        $excel_no['provider_name'] = '供货商名称';
                        break;
                    case 'in_num':
                        $excel_no['in_num'] = '入库总数量';
                        break;
                    case 'put_num':
                        $excel_no['put_num'] = '取货总数量';
                        break;
                    case 'total_price':
                        $excel_no['total_price'] = '总金额';
                        break;
                    case 'pickup_date':
                        $excel_no['pickup_date'] = '取货日期';
                        break;
                    case 'stalls_no':
                        $excel_no['stalls_no'] = '采购单编号';
                        break;
                    case 'goods_no':
                        $excel_no['goods_no'] = '货品编码';
                        break;
                    case 'goods_name':
                        $excel_no['goods_name'] = '货品名称';
                        break;
                    case 'spec_no':
                        $excel_no['spec_no'] = '商家编码';
                        break;
                    case 's_in_num':
                        $excel_no['s_in_num'] = '入库数量';
                        break;
                    case 's_put_num':
                        $excel_no['s_put_num'] = '取货数量';
                        break;
                    case 's_total_price':
                        $excel_no['s_total_price'] = '金额';
                        break;
                    case 'purchase_time':
                        $excel_no['purchase_time'] = '取货时间';
                        break;
                }
            }
            $title = '档口采购员账单';
            $filename = '档口采购员账单';
            $width_list = array('10', '17', '12', '12', '10', '15', '17', '17', '17', '17', '10', '10', '10', '17');
            ExcelTool::Arr2Excel($data, $title, $excel_no, $width_list, $filename, $creator,$merge_arr);
        } catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
}