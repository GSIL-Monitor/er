<?php
namespace Account\Model;

use Think\Log;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class HistoryStallsPurchaserAccountModel extends Model
{
    protected $tableName = 'operator_stalls_pickup_log_history';
    protected $pk = 'rec_id';

    public function getHistoryStallsPurchaserAccountList($page=1, $rows=20, $search = array(), $sort = 'ospl.rec_id', $order = 'desc')
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
                    FROM operator_stalls_pickup_log_history ospl
                    LEFT JOIN stalls_less_goods_detail_history sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN hr_employee he ON ospl.operator_id = he.employee_id
                    LEFT JOIN purchase_provider pp ON sgd.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                    WHERE $where
                    GROUP BY ospl.operator_id,pickup_date,ospl.purchase_id
                    ORDER BY $order LIMIT $limit";
            $sql_total = "SELECT DISTINCT DATE(ospl.pickup_time) pickup_date,IFNULL(SUM(sgd.stockin_status),0) in_num,IFNULL(SUM(sgd.pickup_status),0) put_num,
                    pp.provider_name,he.fullname purchase_name,IFNULL(SUM(sgd.price),0) total_price,ospl.operator_id
                    FROM operator_stalls_pickup_log_history ospl
                    LEFT JOIN stalls_less_goods_detail_history sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN hr_employee he ON ospl.operator_id = he.employee_id
                    LEFT JOIN purchase_provider pp ON sgd.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
                    WHERE $where
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

    public function getHistoryPurchaserGoodsDetail($id)
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
                    FROM operator_stalls_pickup_log_history ospl
                    LEFT JOIN stalls_less_goods_detail_history sgd ON sgd.unique_code = ospl.unique_code
                    LEFT JOIN stalls_order_history so ON sgd.stalls_id = so.stalls_id
                    LEFT JOIN goods_spec gs ON sgd.spec_id = gs.spec_id AND gs.deleted=0
                    LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id AND gg.deleted=0
                    WHERE DATE(ospl.pickup_time)='{$date}' AND ospl.operator_id = $operator_id AND ospl.purchase_id = $provider_id AND $where
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
}