<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;

class SalesLogisticsTraceModel extends Model
{
    protected $tableName = 'sales_logistics_trace';

    public function loadDataByCondition($page, $rows, $search , $sort , $order )
    {
        $data = array();
        $where = ' TRUE ';
        $page = intval($page);
        $rows = intval($rows);
        $limit=($page - 1) * $rows . "," . $rows;
        $sort = $sort.' '.$order;
        //过滤店铺仓库权限
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
        D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k => $v)
        {
            if ($v === '') continue;
            switch($k){
                case 'logistics_id':
                {//物流公司 stockout_order
                    set_search_form_value($where, $k, $v, 'slt', 2,' AND ');
                    break;
                }
                case 'logistics_trace_type'://物流追踪状态
                {
                    set_search_form_value($where, 'logistics_status', $v, 'slt', 2,' AND ');
                    break;
                }
                case 'logistics_no'://物流单号
                    set_search_form_value($where,$k,$v,'slt',1,'AND');
                    break;
                case 'shop_id'://店铺
                    set_search_form_value($where, $k, $v, 'slt', 2,' AND ');
                    break;
                case 'warehouse_id'://仓库
                    set_search_form_value($where,$k,$v,'slt',2,'AND');
                    break;
                case 'stockout_no'://出库单号
                    set_search_form_value($where,$k,$v,'slt',1,'AND');
                    break;
                case 'trade_no':
                    set_search_form_value($where,$k,$v,'slt',1,'AND');
                    break;
                case 'trade_consign_start_time'://发货时间
                    set_search_form_value($where, 'created', $v,'slt', 4,' AND ',' >= ');
                    break;
                case 'trade_consign_end_time':
                    set_search_form_value($where, 'created', $v,'slt', 4,' AND ',' < ');
                    break;
                case 'trade_get_start_time':
                    set_search_form_value($where, 'get_time', $v,'slt', 4,' AND ',' >= ');
                    break;
                case 'trade_get_end_time':
                    set_search_form_value($where, 'get_time', $v,'slt', 4,' AND ',' < ');
            }
        }
        try
        {
            $count = $this->alias('slt')->where($where)->fetchSql(false)->count();
            $res = $this->alias('slt')->field('slt.rec_id id,cs.shop_name,cw.name warehouse_name,slt.trade_no,slt.stockout_no,slt.src_tids,slt.buyer_nick,slt.receiver_name,slt.receiver_mobile,slt.receivable,slt.delivery_term,slt.receiver_area,slt.receiver_addr,slt.logistics_no,slt.logistics_status,slt.pay_time,slt.created,slt.get_time,slt.remark,slt.modified')->join('cfg_shop cs on cs.shop_id=slt.shop_id')->join('cfg_warehouse cw on cw.warehouse_id=slt.warehouse_id')->where($where)->limit($limit)->order($sort)->select();
            $data = array('total'=>$count,'rows'=>$res);

        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array(),'msg'=>$e->getMessage());
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array(),'msg'=>$e->getMessage());
        }
        return $data;
    }


    public function getLogisticsTraceDetail($id)
    {
        try
        {
            $res = M('sales_logistics_trace_detail')->field('accept_station,accept_time')->where(array('trace_id'=>$id))->fetchSql(false)->select();
            $data=array('total'=>count($res),'rows'=>$res);

        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array(),'msg'=>$e->getMessage());
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array(),'msg'=>$e->getMessage());
        }
        return $data;;

    }


}