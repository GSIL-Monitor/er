<?php
namespace Stock\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Think\Log;
class HistoryApiLogisticsSyncModel extends Model{
    protected $tableName = 'api_logistics_sync_history';
    protected $pk        = 'rec_id';
    public function getHistoryApiLogisticsSyncList($page=1, $rows=20, $search, $sort, $order){
        $where_sales_trade        = "";
        $where_api_logistics_sync = "";
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {   //set_search_form_value->Common/Common/function.php
                case 'sync_status':// 同步状态  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                    break;
                case 'trade_no': // 订单号 trade_no  sales_trade
                    set_search_form_value($where_sales_trade, $k, $v, 'st', 1, 'AND');
                    break;
                case 'src_tid'://原始单号  tid api_logistics_sync
                    $k = "tid";
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
                case 'buyer_nick':// 客户网名  sales_trade
                    set_search_form_value($where_sales_trade, $k, $v, 'st', 6, 'AND');
                    break;
                case 'logistics_no':// 物流单号  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
                case 'shop_id'://店铺id   api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 2, 'AND');
                    break;
                case 'is_part_sync'://是否拆分  api_logistics_sync
                    set_search_form_value($where_api_logistics_sync, $k, $v, 'als', 1, 'AND');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $order = $sort . ' ' . $order;//排序
        $order = addslashes($order);
        try {
            $sql_total = "select als.rec_id"
                . " FROM  api_logistics_sync_history als"
                . " LEFT JOIN sales_trade st on st.trade_id = als.trade_id"
                . " where 1 " . $where_api_logistics_sync . " " . $where_sales_trade;
            $total     = count($this->query($sql_total));

            $sql_limit = $sql_total . ' ORDER BY ' . $order . ' LIMIT ' . $limit;

            $sql_show = "SELECT als.rec_id id,als.trade_id,als.platform_id,cs.shop_name shop_id,als.tid,st.trade_no,als.is_need_sync,"
                . "als.sync_status,als.error_msg,st.trade_time,als.sync_time,st.buyer_nick,cl.logistics_name logistics_id,als.logistics_type,"
                . "als.logistics_no,als.created,cl.bill_type"
                . " FROM ({$sql_limit}) tmp"
                . " LEFT JOIN api_logistics_sync_history als ON (als.rec_id=tmp.rec_id)"
                . " LEFT JOIN cfg_logistics cl ON (cl.logistics_id=als.logistics_id)"
                . " LEFT JOIN cfg_shop cs ON (cs.shop_id=als.shop_id)"
                . " LEFT JOIN sales_trade st ON (st.trade_id=als.trade_id)";

            $list = $total ? $this->query($sql_show) : array();
            if(count($list)>0){
                foreach($list as $k=>$v){
                    $id = $v['id'];
                    $list[$k]['error_msg'] ="<a href='javascript:void(0)'  onclick='historyApiLogisticsSync.solution($id)'>{$v['error_msg']}</a>" ;
                }
            }
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;

    }
    public function showTabInfor($id) {
        $model = M('sales_trade');
        try {
            $point_number = get_config_value('point_number',0);
            $num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";

            $res  = $model->query("SELECT sto.trade_id,sto.goods_id,sto.goods_no,sto.goods_name,sto.spec_id,sto.spec_no,sto.spec_name,sto.order_price,".$num.",sto.order_price*sto.num sum_price FROM sales_trade_order sto LEFT JOIN api_logistics_sync_history als on als.trade_id = sto.trade_id WHERE als.rec_id= %d", $id);
            $data = array('total' => count($res), 'rows' => $res);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }
}