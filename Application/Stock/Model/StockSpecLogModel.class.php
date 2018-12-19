<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/12
 * Time: 14:54
 */

namespace Stock\Model;


use Think\Model;

class StockSpecLogModel extends Model
{
    protected $tableName = 'stock_spec_log';
    protected $pk = 'rec_id';
    public function getStockGoodsLog($id)
    {
        try{
            $page_info = I('','',C('JSON_FILTER'));
            $limit = '';
            if(isset($page_info['page']))
            {
                $page = intval($page_info['page']);
                $rows = intval($page_info['rows']);
                $limit=" ".($page - 1) * $rows . "," . $rows;//分页
            }
            $warehouse_id = I('get.warehouse_id','',C('JSON_FILTER'));
            $where = array('spec_id'=>intval($id));
            if($warehouse_id != 0)
            {
                $where['warehouse_id'] = intval($warehouse_id);
            }
            $stock_spec_lst = D('Stock/StockSpec')->field('rec_id')->where($where)->select();
            $id_ar = array_column($stock_spec_lst,'rec_id');
            $id_str = implode(',',$id_ar);
            $total = $this->where(array('stock_spec_id'=>array('in',$id_str)))->count();
            $data = $this->fetchSql(false)->alias('sl')->field("sl.rec_id id,he.fullname,cw.name warehouse_name,IF(sl.operator_type=1,sl.message,CONCAT(sl.message,'---操作前库存:“',sl.stock_num,'”---操作数量:“',sl.num,'”')) message,sl.created")->join('left join stock_spec ss on ss.rec_id = sl.stock_spec_id')->join('left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id')->join('left join hr_employee he on sl.operator_id = he.employee_id')->where(array('stock_spec_id'=>array('in',$id_ar)))->order('sl.created desc')->limit($limit)->select();
        }catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return array('total'=>0,'rows'=>array());
        }
        return array('total'=>$total,'rows'=>$data);
    }
    public function search($page = 1, $rows = 20, $search = array(), $sort = 'created', $order = 'desc',$type = 'log')
    {
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$table = 'stock_spec_log';
        if($type == 'history'){
            $table = 'stock_spec_log_history';
        }
        $sys_config =  get_config_value(array('point_number'),array(0));
        $point_number = $sys_config['point_number'];
        $where_goods_spec = '';
        $where_goods_goods = '';
        $where_stock_spec = '';
        $where_stock_spec_log = '';
        set_search_form_value($where_goods_spec, 'deleted', 0, 'gs_1', 2, ' AND ');
        set_search_form_value($where_goods_goods, 'deleted', 0, 'gg_1', 2, ' AND ');
        foreach ($search as $k => $v) {
            if ($v === '')  continue;
            switch ($k) {
                case 'spec_no'://商家编码      goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'spec_name'://规格名称   goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'goods_no': //货品编号     goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 1, ' AND ');
                    break;
                case 'goods_name'://货品名称  goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 6, ' AND ');
                    break;
                case 'warehouse_id'://仓库   stock_spec
                    set_search_form_value($where_stock_spec, $k, $v, 'ss_1', 2, ' AND ');
                    break;
                case 'type':
                    set_search_form_value($where_stock_spec_log, 'operator_type', $v, 'ssl_1', 2, ' AND ');
                    break;
                case 'operator_id':
                    set_search_form_value($where_stock_spec_log, $k, $v, 'ssl_1', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where_stock_spec_log, 'created', $v,'ssl_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where_stock_spec_log, 'created', $v,'ssl_1', 3,' AND ',' <= ');
                    break;
            }
        }
        $page = intval($page);
        $rows = intval($rows);
        $arr_sort=array('created'=>'rec_id');
        $limit=($page - 1) * $rows . "," . $rows;//分页
        $order_in = 'ssl_1.'.$arr_sort[$sort].' '.$order;//排序
        $order_out = 'ssl_2.'.$arr_sort[$sort].' '.$order;//排序
        $order_in = addslashes($order_in);
        $order_out = addslashes($order_out);
        $sql_where = ' left join stock_spec ss_1 on ss_1.rec_id = ssl_1.stock_spec_id ';
        $flag = false;
        $sql_total = "select count(1) as total from {$table} ssl_1";
        $sql_limit = "select ssl_1.rec_id from {$table} ssl_1";
        if(!empty($where_goods_goods))
        {
            $sql_where .= " left join goods_spec gs_1 on gs_1.spec_id = ss_1.spec_id  left join goods_goods gg_1 on gg_1.goods_id = gs_1.goods_id ";
        }else if(!empty($where_goods_spec)) {
            $sql_where .= " left join goods_spec gs_1 on gs_1.spec_id = ss_1.spec_id ";
        }
        connect_where_str($sql_where, $where_stock_spec_log,$flag);
        connect_where_str($sql_where, $where_stock_spec,$flag);
        connect_where_str($sql_where, $where_goods_spec,$flag);
        connect_where_str($sql_where, $where_goods_goods,$flag);
        $sql_total .= $sql_where;
        $sql_limit .= $sql_where;
        $sql_limit .= ' order by '.$order_in.' limit '.$limit;

        $sql_select = "select he.fullname,gg_2.goods_name,gg_2.goods_no,gs_2.spec_no,gs_2.spec_name,cw.name warehouse_name,IF(ssl_2.operator_type = 2,CONCAT('+',CAST(ssl_2.num AS DECIMAL(19,".$point_number."))),-CAST(ssl_2.num AS DECIMAL(19,".$point_number."))) num,CONCAT(ssl_2.message,'---操作前库存:“',CAST(ssl_2.stock_num  AS DECIMAL(19,".$point_number.")),'”') message,case ssl_2.operator_type when 1 THEN '更新警戒库存' when 2 THEN '入库' when 3 THEN '出库' when 4 THEN '刷新平台库存' END operator_name,ssl_2.operator_type,ssl_2.created from {$table} ssl_2 "//gs.class_name,
                    ." join (".$sql_limit.") ss_3 on ss_3.rec_id = ssl_2.rec_id  "
                    ." left join stock_spec ss_2 on ss_2.rec_id = ssl_2.stock_spec_id "
                    ." left join cfg_warehouse cw on cw.warehouse_id = ss_2.warehouse_id "
                    ." left join hr_employee he ON he.employee_id = ssl_2.operator_id "
                    ." left join goods_spec gs_2 on gs_2.spec_id = ss_2.spec_id "
                    ." left join goods_goods gg_2 on gg_2.goods_id = gs_2.goods_id "
//                    ." left join goods_class gs on gs.class_id = gg_2.class_id "
                    ." order by ".$order_out;
        $data = array();
        try{
            $total=$this->query($sql_total);
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql_select):array();
            $data=array('total'=>$total,'rows'=>$list);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data=array('total' => 0, 'rows' => array());
        }
        $data = array('total' => $total, 'rows' => $list);
        return($data);
    }
}