<?php
namespace Trade\Model;

use Think\Model;

class SalesTradeLogModel extends Model
{
    protected $tableName = 'sales_trade_log';
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
            $sql_max_time_log = 'SELECT stl.created  as max_data_time FROM ('.$sql_limit_log.') stl ORDER BY stl.created DESC LIMIT 1';
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
        if(get_operator_id()>1){
        	D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
        	D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        }
        foreach ($search as $k=>$v){
            if($v==='') continue;
            switch ($k)
            {   
                case 'trade_no':
                    set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
                    break;
				case 'warehouse_id':
                    set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
                    break;
                case 'shop_id':
                    set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
                    break;
                case 'message':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stl_1', 6, ' AND ');
                    break;
                case 'type':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stl_1', 2, ' AND ');
                    break;
                case 'operator_id':
                    set_search_form_value($where_sales_trade_log, $k, $v, 'stl_1', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where_sales_trade_log, 'created', $v,'stl_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where_sales_trade_log, 'created', $v,'stl_1', 3,' AND ',' <= ');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $arr_sort=array('trade_no'=>'trade_id','fullname'=>'operator_id');
        $in_order = 'stl_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
        $in_order = addslashes($in_order);
        $out_order = 'stl_2.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//外层排序
        $out_order = addslashes($out_order);
        $sql_sel_limit = 'SELECT stl_1.rec_id FROM sales_trade_log stl_1 ';
        $sql_total = 'SELECT COUNT(1) AS total FROM sales_trade_log stl_1 ';
        $flag = false;
        $sql_where = '';
        $sql_limit = ' ORDER BY '.$in_order.' LIMIT '.$limit;
        if(!empty($where_sales_trade)){
            $sql_where .= ' LEFT JOIN sales_trade st_1 ON st_1.trade_id = stl_1.trade_id ';
            $sql_limit = ' GROUP BY stl_1.rec_id ORDER BY '.$in_order.' LIMIT '.$limit;
        }
        connect_where_str($sql_where, $where_sales_trade_log, $flag);
        connect_where_str($sql_where, $where_sales_trade, $flag);
        $sql_sel_limit .= $sql_where;
        $sql_total .= $sql_where;
        $sql_sel_limit .= $sql_limit;
        $sql_fields_str = '';
        $sql_left_join_str = '';

        $sql_fields_str = "SELECT stl_2.rec_id AS id, stl_2.trade_id, stl_2.operator_id, stl_2.type, stl_2.message, stl_2.created, st_2.trade_no, he.fullname FROM sales_trade_log stl_2";
        $sql_left_join_str = "LEFT JOIN sales_trade st_2 ON st_2.trade_id = stl_2.trade_id LEFT JOIN hr_employee he ON he.employee_id = stl_2.operator_id";
        $sql = $sql_fields_str.' INNER JOIN('.$sql_sel_limit.') stl_3 ON stl_2.rec_id = stl_3.rec_id '.$sql_left_join_str.' ORDER BY '.$out_order.',stl_2.rec_id ';
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
    //根据日志获取操作订单量
    function getTradeNumByLog($type,$date){
    	if($date=='')$date=date('Y-m-d');
    	$trade_num=array();
    	D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
    	D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',2);
    	if(empty($where['warehouse_id'])) $where['warehouse_id'] = '0';
    	if(empty($where['shop_id'])) $where['shop_id'] = '0';
    	$setting_config = get_config_value(array('sales_print_time_range'),array(7));
    	try{
    		$type=array(
    				'checked'=>9,
    				'printed'=>91,
    				'examed'=>100,
    				'weighted'=>102,
    				'consigned'=>105,
    				'check'=>30,
    				'f_check'=>35,
    				'exam'=>1,
    				'weight'=>2,
    				'print'=>1
    		);
    		foreach ($type as $k=>$v){
    			switch($k){
    				case "checked":
    					$sql="SELECT COUNT(DISTINCT(stl.trade_id)) AS num FROM sales_trade_log stl LEFT JOIN sales_trade st ON st.trade_id=stl.trade_id WHERE (stl.type=9 OR stl.type=10 OR stl.type=12) AND stl.created>'".$date."' AND st.trade_status>30 ";
    					break;
    				case "printed":
    				case "examed":
    				case "weighted":
    				case "consigned":
    					$sql="SELECT COUNT(DISTINCT(stl.trade_id)) AS num FROM sales_trade_log stl WHERE type='".$v."' AND created>'".$date."'";
    					break;
    				case "check":
    				case "f_check":
    					$sql="SELECT COUNT(st.trade_id) AS num FROM sales_trade st WHERE trade_status='".$v."'";
    					break;
    				case "exam":
    				case "weight":
    					$sql="SELECT COUNT(*) AS num FROM stockout_order so LEFT JOIN sales_trade st ON st.trade_id=so.src_order_id 
    							WHERE so.src_order_type=1 AND so.warehouse_id IN (".$where['warehouse_id'].") AND status>=55 
    							AND (so.consign_time='1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <=".$setting_config['sales_print_time_range'].") 
    							AND so.consign_status&".$v."<>".$v." AND st.shop_id IN (".$where['shop_id'].")";
    					break;
    				case "print":
    					$sql="SELECT COUNT(*) AS num FROM stockout_order so LEFT JOIN sales_trade st ON st.trade_id=so.src_order_id 
    							WHERE so.src_order_type=1 AND so.warehouse_id IN (".$where['warehouse_id'].") AND status>=55 
    							AND (so.consign_time='1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <=".$setting_config['sales_print_time_range'].") 
    							AND (so.logistics_print_status=0 OR so.sendbill_print_status=0) AND st.shop_id IN (".$where['shop_id'].")";
    					break;
    			}
    			$num=$this->query($sql);
    			$trade_num[$k]=$num[0]['num'];
    		}
    	}catch (\PDOException $e){
    		\Think\Log::write($this->name."-".$e->getMessage());
    	}
    	return $trade_num;
    }
}

?>