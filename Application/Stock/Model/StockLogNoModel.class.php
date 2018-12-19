<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 11:10
 */
namespace Stock\Model;

use Think\Model;

/**
 * 电子面单model类
 * @package Stock\Model
 */
class StockLogNoModel extends Model
{
    protected $tableName = 'stock_logistics_no';
    protected $pk = 'rec_id';

    /**
     * 根据条件获取电子面单号
     * @param $page string
     * @param $rows string
     * @param $search array
     * @param $sort string
     * @param $order string
     * @return array
     * @throws array('total' => 0, 'rows' => array())
     */
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
                    set_search_form_value($where_limit, $k, $v, 'sln', 1);
                    break;
                case 'logistics_id':
                    if($v!='all')
                    set_search_form_value($where_limit, $k, $v, 'sln', 1);
                    break;
                case 'status':
                    set_search_form_value($where_limit, $k, $v, 'sln', 2);
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
                $total = $this->alias('sln')->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = sln.logistics_id")->where($where_limit)->count();
            }else{
                $total = $this->alias('sln')->count();
            }
            $sql_pretreatment = $this->fetchSql()->alias('sln')->field("rec_id")->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = sln.logistics_id")->where($where_limit)->page($page, $rows)->select();
            $list = $this->fetchSql(false)->alias('sln')->field("sln.rec_id as id,cl.logistics_name as logistics_id,sln.logistics_no,IF(cl.bill_type=1,'线下热敏','云栈热敏') bill_type_name,cl.bill_type,sln.status,sln.error_info,sln.created,sln.modified ")->join("INNER JOIN (".$sql_pretreatment.") as sp ON sp.rec_id = sln.rec_id")->join("LEFT JOIN cfg_logistics cl ON cl.logistics_id = sln.logistics_id")->order($order)->select();
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }

}