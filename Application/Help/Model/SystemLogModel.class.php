<?php
namespace Help\Model;

use Think\Model;

class SystemLogModel extends Model
{
    protected $tableName = 'sys_other_log';
    protected $pk        = 'rec_id';
    public function addSystemLog($data)
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
            $msg = $e->getMessage();
           \Think\Log::write($this->name.'-addSystemLog-'.$msg);
            E(self::PDO_ERROR);
        }
    }
   
    public function updateSystemLog($data,$conditions)
    {
        try {
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
           \Think\Log::write($this->name.'-updateSystemLog-'.$msg);
            E(self::PDO_ERROR);
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
    public function getSystemLogMaxCreated($conditions,$limit){
        try {
            $sql_limit_log = $this->fetchSql(true)->field('created')->where($conditions)->order('created asc')->limit($limit)->select();
            $sql_max_time_log = 'SELECT sol.created  as max_data_time FROM ('.$sql_limit_log.') sol ORDER BY sol.created DESC LIMIT 1';
            $max_time_log = $this->query($sql_max_time_log);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getSystemLogMaxCreated-'.$msg);
            E(self::PDO_ERROR);
        }
        return $max_time_log;
        
    }

    public function querySystemLog(&$where_system_log,$page=1,$rows=20,$search=array(),$sort='created',$order='desc'){
		//D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k=>$v){
            if($v==='') continue;
            switch ($k)
            {
                case 'type':
                    set_search_form_value($where_system_log, $k, $v,'sol_1', 2,' AND ');
                    break;
                case 'operator_id':
                    set_search_form_value($where_system_log, $k, $v, 'sol_1', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where_system_log, 'created', $v,'sol_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where_system_log, 'created', $v,'sol_1', 3,' AND ',' <= ');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $arr_sort=array('fullname'=>'operator_id');
        $in_order = 'sol_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
        $in_order = addslashes($in_order);
        $out_order = 'sol_2.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//外层排序
        $out_order = addslashes($out_order);
		$sql_sel_limit = 'SELECT sol_1.rec_id FROM sys_other_log sol_1';
        $sql_total = 'SELECT COUNT(1) AS total FROM sys_other_log sol_1';
        $flag = false;
        $sql_where = '';
        $sql_limit = ' ORDER BY '.$in_order.' LIMIT '.$limit;

        connect_where_str($sql_where, $where_system_log, $flag);
        $sql_sel_limit .= $sql_where;
        $sql_total .= $sql_where;
        $sql_sel_limit .= $sql_limit;
        $sql_fields_str = '';
        $sql_left_join_str = '';
		$sql_fields_str="SELECT sol_2.rec_id As id, sol_2.rec_id, sol_2.operator_id,sol_2.type,sol_2.message,
                      sol_2.created, he.fullname FROM sys_other_log sol_2 ";
		$sql_left_join_str="LEFT JOIN hr_employee he ON he.employee_id=sol_2.operator_id";
		$sql = $sql_fields_str.' INNER JOIN('.$sql_sel_limit.') sol_3 ON sol_2.rec_id = sol_3.rec_id '.$sql_left_join_str.' ORDER BY '.$out_order;
        //echo $sql;exit;
        $data = array($sql_fields_str);
        try {
            $total=$this->query($sql_total);
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql):array();
            $data=array('total'=>$total,'rows'=>$list);
        } catch (\PDOException $e) {
            \Think\Log::write('syste_log:'.$e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }
        return $data;
    }
    
    
}

?>