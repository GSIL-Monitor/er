<?php
/**
 * Created by PhpStorm.
 * User: Asher
 * Date: 2016-11-01
 * Time: 17:02
 */
namespace Stock\Model;

use Think\Model;

class GoodOperateLogModel extends Model
{
    protected $tableName = 'goods_log';
    protected $pk = 'rec_id';

    public function queryGoodOperateLog($page=1,$rows=20,$search=array(),$sort='created',$order='desc'){
        $where_good_operate = '';
        $where_good_good = '';
        $where_good_operate_log = "  and gl_1.goods_type=1";
        foreach ($search as $k=>$v){
            if($v==='') continue;
            switch ($k)
            {
                case 'spec_no':
                    set_search_form_value($where_good_operate, $k, $v,'gs_1', 1,' AND ');
                    break;
                case 'goods_no':
                    set_search_form_value($where_good_good, $k, $v,'gg_1', 1,' AND ');
                    break;
                case 'message':
                    set_search_form_value($where_good_operate_log, $k, $v, 'gl_1', 6, ' AND ');
                    break;
                case 'operate_type':
                    set_search_form_value($where_good_operate_log, $k, $v, 'gl_1', 2, ' AND ');
                    break;
                case 'operator_id':
                    set_search_form_value($where_good_operate_log, $k, $v, 'gl_1', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where_good_operate_log, 'created', $v,'gl_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where_good_operate_log, 'created', $v,'gl_1', 3,' AND ',' <= ');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $arr_sort=array('spec_no'=>'spec_id','fullname'=>'operator_id');
        $in_order = 'gl_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
        $in_order = addslashes($in_order);
        $out_order = 'gl_2.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//外层排序
        $out_order = addslashes($out_order);
        $sql_sel_limit = 'SELECT gl_1.rec_id FROM goods_log gl_1 ';
        $sql_total = 'SELECT COUNT(1) AS total FROM goods_log gl_1 ';
        $flag = false;
        $sql_where = '';
        $sql_limit = ' ORDER BY '.$in_order.' LIMIT '.$limit;
        if(!empty($where_good_operate) || !empty($where_good_good)){
            if(!empty($where_good_operate)){
                $sql_where .= ' LEFT JOIN goods_spec gs_1 ON gs_1.spec_id = gl_1.spec_id ';
            }
            if(!empty($where_good_good)){
                $sql_where .= ' LEFT JOIN goods_goods gg_1 ON gg_1.goods_id = gl_1.goods_id ';
            }
            $sql_limit = ' GROUP BY gl_1.rec_id ORDER BY '.$in_order.' LIMIT '.$limit;
        }
        connect_where_str($sql_where, $where_good_operate_log, $flag);
        connect_where_str($sql_where, $where_good_operate, $flag);
        connect_where_str($sql_where, $where_good_good, $flag);
        $sql_sel_limit .= $sql_where;
        $sql_total .= $sql_where;
        $sql_sel_limit .= $sql_limit;

        $sql_fields_str = "SELECT gl_2.rec_id AS id, gl_2.goods_id, gl_2.operator_id, gl_2.operate_type, gl_2.message, gl_2.created, gs_2.spec_no, he.fullname, gg.goods_no FROM goods_log gl_2";
        $sql_left_join_str = "LEFT JOIN goods_spec gs_2 ON gs_2.spec_id = gl_2.spec_id LEFT JOIN hr_employee he ON he.employee_id = gl_2.operator_id LEFT JOIN goods_goods gg ON gg.goods_id = gl_2.goods_id";
        $sql = $sql_fields_str.' INNER JOIN('.$sql_sel_limit.') gl_3 ON gl_2.rec_id = gl_3.rec_id '.$sql_left_join_str.' ORDER BY '.$out_order;
        $data = array();
        try {
            $total=$this->query($sql_total);
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql):array();
            $data=array('total'=>$total,'rows'=>$list);
        } catch (\PDOException $e) {
            \Think\Log::write('search_goods_log:'.$e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }
        return $data;
    }
}