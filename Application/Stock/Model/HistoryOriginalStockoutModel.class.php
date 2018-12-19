<?php


namespace Stock\Model;

use Think\Exception;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
/**
 * @package Stock\Model
 */
class HistoryOriginalStockoutModel extends Model {
    protected $tableName = "stockout_order_history";
    protected $pk        = "stockout_id";

    public function getHistoryOriginalStockout($page = 1, $rows = 20, $search = array(), $sort = 'soh.stockout_id', $order = 'desc') {
        try {
            //设置店铺权限
			$warehouse_list = D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            $where=' soh_1.src_order_type<>1 ';
            foreach ($search as $k => $v) {
                if ($v === '') continue;
                switch ($k) {
                    case "stockout_no":
                        set_search_form_value($where, $k, $v, "soh_1", 1, "AND");
                        break;
                    case "src_order_no":
                        set_search_form_value($where, $k, $v, "soh_1", 1, "AND");
                        break;
                    case "status":
                        set_search_form_value($where, $k, $v, "soh_1", 2, "AND");
                        break;
                    case "warehouse_id":
                        set_search_form_value($where, $k, $v, "soh_1", 2, "AND");
                        break;
                    case "src_order_type":
                        set_search_form_value($where, $k, $v, "soh_1", 2, "AND");
                        break;
                    case "operator_id":
                        set_search_form_value($where, $k, $v, "soh_1", 2, "AND");
                        break;
                    default:
                        continue;
                }
            }
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            $order = addslashes($order);
            //查询出本次要输出的stockout_id
            $sql_result = "SELECT soh_1.stockout_id FROM stockout_order_history soh_1 WHERE $where ORDER BY $order LIMIT $limit";
            //再构造SQL查询完整的数据
            $point_number = get_config_value('point_number',0);
            $show_sql = "CAST(soh.goods_count AS DECIMAL(19,".$point_number."))";
            $sql = " select soh.stockout_id AS id,soh.warehouse_id ,soh.stockout_no, soh.src_order_type, soh.src_order_no, soh.status,"
                ." soh.logistics_no, soh.post_cost,".$show_sql." goods_count, soh.goods_type_count, soh.remark, soh.created, soh.consign_time,"
                ." he.fullname operator_id, cl.logistics_name logistics_id,"
                ." cw.name warehouse_name"
                ." FROM stockout_order_history soh INNER JOIN (" . $sql_result ." ) soh_2 ON soh_2.stockout_id = soh.stockout_id"
                ." LEFT JOIN hr_employee he ON he.employee_id = soh.operator_id"
                ." LEFT JOIN cfg_logistics cl ON cl.logistics_id = soh.logistics_id"
                ." LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = soh.warehouse_id"
                ." ORDER BY id DESC";
            $result         = $this->query($sql);
            $sql_count      = "SELECT COUNT(1) AS total FROM stockout_order_history soh_1 LEFT JOIN hr_employee he ON he.employee_id = soh_1.operator_id LEFT JOIN cfg_logistics cl ON cl.logistics_id = soh_1.logistics_id LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = soh_1.warehouse_id";
            $sql_count      = $where == "" ? $sql_count : $sql_count . " where $where";
            $count          = $this->query($sql_count);
            $count          = $count[0]["total"];
            $data           = array();
            $data['rows']   = $result;
            $data['total']  = $count;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["rows"]  = "";
            $data["total"] = 0;
        }
        return $data;
    }

    /*
     * 获取到tab中出库单详情的数据
     * */
    public function getStockoutOrderList($id) {
        try {
            $id = intval($id);
            $page_info = I('','',C('JSON_FILTER'));
            $limit = '';
            if(isset($page_info['page']))
            {
                $page = intval($page_info['page']);
                $rows = intval($page_info['rows']);
                $limit=" limit ".($page - 1) * $rows . "," . $rows;//分页
            }
            $page_sql = 'select sodh_2.rec_id from stockout_order_detail_history sodh_2 where sodh_2.stockout_id = '.intval($id).' '.$limit;
            $point_number = get_config_value('point_number',0);
            $num = "CAST(sodh.num AS DECIMAL(19,".$point_number.")) num";
            $sql = "SELECT sodh.rec_id,IFNULL(cwp.position_no,cwp2.position_no) position_no,sodh.spec_id,".
                "sodh.goods_id,sodh.goods_name,sodh.goods_no,sodh.spec_code,sodh.spec_name,sodh.spec_no,gs.barcode,
                  ".$num.",sodh.scan_type,sodh.remark,gg.brand_id,sodh.cost_price FROM stockout_order_detail_history sodh
                  JOIN (".$page_sql.") sodh_l on sodh_l.rec_id = sodh.rec_id
                  LEFT JOIN stockout_order_history soh ON sodh.stockout_id = soh.stockout_id
                  LEFT JOIN stock_spec_position ssp ON ssp.spec_id = sodh.spec_id and ssp.warehouse_id = soh.warehouse_id
                  LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id
                  LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -soh.warehouse_id
                  LEFT JOIN goods_spec gs ON sodh.spec_id = gs.spec_id
                  LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
                  WHERE sodh.is_package=0 AND sodh.stockout_id = ".$id;
            $sql_count = "SELECT COUNT(1) AS total FROM stockout_order_detail_history sodh WHERE sodh.stockout_id=%d";
            $result = $this->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $this->query($sql, $id);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }
}