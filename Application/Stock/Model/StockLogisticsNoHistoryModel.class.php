<?php
namespace Stock\Model;

use Think\Model;

class StockLogisticsNoHistoryModel extends Model
{
    protected $tableName = 'stock_logistics_no_history';
    protected $pk = 'rec_id';

    public function searchStockLogNo($page, $rows, $search, $sort, $order)
    {
        $where_limit = array();
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case 'bill_type':
                    set_search_form_value($where_limit, $k, $v, 'cl', 2);
                    break;
                case 'logistics_no':
                    set_search_form_value($where_limit, $k, $v, 'slnh', 1);
                    break;
                case 'status':
                    set_search_form_value($where_limit, $k, $v, 'slnh', 2);
                    break;
                default:
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $order = $sort . ' ' . $order;//排序
        $order = addslashes($order);
        try {
            if($where_limit){
                $total = $this->alias('slnh')->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = slnh.logistics_id")->where($where_limit)->count();
            }else{
                $total = $this->alias('slnh')->count();
            }
            $sql_pretreatment = $this->fetchSql(true)->alias('slnh')->field("rec_id")->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = slnh.logistics_id")->where($where_limit)->page($page, $rows)->select();
            $list = $this->fetchSql(false)->alias('slnh')->field("slnh.rec_id as id,cl.logistics_name as logistics_id,slnh.logistics_no,IF(cl.bill_type=1,'线下热敏','云栈热敏') bill_type_name,cl.bill_type,slnh.status,slnh.error_info,slnh.created,slnh.modified ")->join("INNER JOIN (".$sql_pretreatment.") as sp ON sp.rec_id = slnh.rec_id")->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = slnh.logistics_id")->order($order)->select();
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }

}