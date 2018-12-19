<?php
namespace Trade\Model;

use Think\Model;

class HistorySalesTradeLogModel extends Model
{	
    protected $tableName = 'sales_trade_log_history';
    protected $pk        = 'rec_id';
    public function addTradeLog($data)
    {
        try {
            if (empty($data[0])) {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-addTradeLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function updateTradeLog($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateTradeLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
	 /**
     * @param $data
     * @param $update
     * @param $options
     * @return array
     * 查询统计时，查询的日志当前条数中的最大时间
     * author:changtao
     */
    public function getSalesTradeLogMaxCreated($conditions,$limit){
        try {
            $sql_limit_log = $this->fetchSql(true)->field('created')->where($conditions)->order('created asc')->limit($limit)->select();
            $sql_max_time_log = 'SELECT stlh.created  as max_data_time FROM ('.$sql_limit_log.') stlh ORDER BY stlh.created DESC LIMIT 1';
            $max_time_log = $this->query($sql_max_time_log);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getSalesTradeLogMaxCreated-'.$msg);
            SE(self::PDO_ERROR);
        }
        return $max_time_log;
        
    }

    public function querySalesTradeLog(&$where_sales_trade_log,$page=1,$rows=20,$search=array(),$sort='created',$order='desc'){
        $where_sales_trade = '';
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k=>$v){
            if($v==='') continue;
            switch ($k)
            {   
                case 'trade_no':
                    set_search_form_value($where_sales_trade, $k, $v,'sth_1', 1,' AND ');
                    break;
				case 'warehouse_id':
                    set_search_form_value($where_sales_trade, $k, $v,'sth_1', 2,' AND ');
                    break;
                case 'message':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stlh_1', 6, ' AND ');
                    break;
                case 'type':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stlh_1', 2, ' AND ');
                    break;
                case 'operator_id':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stlh_1', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where_sales_trade_log, 'created', $v,'stlh_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where_sales_trade_log, 'created', $v,'stlh_1', 3,' AND ',' <= ');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $arr_sort=array('trade_no'=>'trade_id','fullname'=>'operator_id');
        $in_order = 'stlh_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
        $in_order = addslashes($in_order);
        $out_order = 'stlh_2.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//外层排序
        $out_order = addslashes($out_order);
        $sql_sel_limit = 'SELECT stlh_1.rec_id FROM sales_trade_log_history stlh_1 ';
        $sql_total = 'SELECT COUNT(1) AS total FROM sales_trade_log_history stlh_1 ';
        $flag = false;
        $sql_where = '';
        $sql_limit = ' ORDER BY '.$in_order.' LIMIT '.$limit;
        if(!empty($where_sales_trade)){
            $sql_where .= ' LEFT JOIN sales_trade_history sth_1 ON sth_1.trade_id = stlh_1.trade_id ';
            $sql_limit = ' GROUP BY stlh_1.rec_id ORDER BY '.$in_order.' LIMIT '.$limit;
        }
        connect_where_str($sql_where, $where_sales_trade_log, $flag);
        connect_where_str($sql_where, $where_sales_trade, $flag);
        $sql_sel_limit .= $sql_where;
        $sql_total .= $sql_where;
        $sql_sel_limit .= $sql_limit;
        $sql_fields_str = '';
        $sql_left_join_str = '';

        $sql_fields_str = "SELECT stlh_2.rec_id AS id, stlh_2.trade_id, stlh_2.operator_id, stlh_2.type, stlh_2.message, stlh_2.created, sth_2.trade_no, he.fullname FROM sales_trade_log_history stlh_2";
        $sql_left_join_str = "LEFT JOIN sales_trade_history sth_2 ON sth_2.trade_id = stlh_2.trade_id LEFT JOIN hr_employee he ON he.employee_id = stlh_2.operator_id";
        $sql = $sql_fields_str.' INNER JOIN('.$sql_sel_limit.') stlh_3 ON stlh_2.rec_id = stlh_3.rec_id '.$sql_left_join_str.' ORDER BY '.$out_order;
       // echo $sql;exit;
		$data = array();
        try {
            $total=$this->query($sql_total);
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql):array();
            $data=array('total'=>$total,'rows'=>$list);
        } catch (\PDOException $e) {
            \Think\Log::write('search_trade_log:'.$e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }
        return $data;
    }
    
}

?>