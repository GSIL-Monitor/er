<?php
namespace Statistics\Model;

use Think\Model;

class StatSalesModel extends Model
{
    protected $tableName = 'stat_daily_sales_amount';
    protected $pk = 'rec_id';
    public function loadDataByCondition($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc',$type='')
    {
        $page  = intval($page);
        $rows  = intval($rows);
        $sort  = addslashes($sort);
        $order = addslashes($order);
        $where_stat = '1 ';
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        foreach ($search as $k => $v) {
            if (!isset($v)) continue;
            switch ($k) {
                case 'shop_id':{
                    set_search_form_value($where_stat, $k, $v, 'stsl',2,'AND' );
                    break;
                }
				 case 'warehouse_id':{
                    set_search_form_value($where_stat, $k, $v, 'stsl',2,'AND' );
                    break;
                }
                case 'month_start':{
                    if(true !== check_regex('month',$v))
                    {
                        $v = '0000-00';
                    }
                    set_search_form_value($where_stat, 'sales_date', $v,'stsl',5,'AND','>=');
                    break;
                }
                case 'month_end':{
                    if(true !== check_regex('month',$v))
                    {
                        $v = date('Y-m');
                    }
                    set_search_form_value($where_stat, 'sales_date', $v,'stsl',5,'AND','<=');
                    break;
                }         
                case 'day_start':{
                    if(true !== check_regex('date',$v))
                    {
                        $v = '0000-00-00';
                    }
                    set_search_form_value($where_stat, 'sales_date', $v,'stsl',3,'AND','>=');
                    break;
                }
                case 'day_end':{
                    if(true !== check_regex('date',$v))
                    {
                        $v = date('Y-m-d');
                    }
                    set_search_form_value($where_stat, 'sales_date', $v,'stsl',3,'AND','<=');
                    break;
                }
                default:
                    \Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
                    break;
            }
        }
        switch($type)
        {
            case 'SalesAmountDailyStat' :
                $sort = 'sales_date';
                $fields = 'stsl.rec_id as id,stsl.sales_date,cs.shop_name as shop_id,wh.name as warehouse_name,stsl.warehouse_id,stsl.new_trades,stsl.new_trades_amount,stsl.check_trades,stsl.check_trades_amount,stsl.send_trades,stsl.send_trades_amount,stsl.send_unknown_goods_amount,send_trade_profit,stsl.post_amount,post_cost,commission, other_cost,package_cost,send_goods_cost,(stsl.post_amount-stsl.post_cost) as post_profit,IFNULL(cast((stsl.send_trades_amount/stsl.send_trades) as decimal(19,2)),0) as send_trade_avg_price';
                $data = D('Statistics/StatDailySalesAmount')->getSearchlist($fields,$where_stat,$page,$rows,'stsl',$sort,$order);
                break;
            case 'SalesAmountMonthlyStat' :
                $sort = 'sales_date';
                $fields = array( 'stsl.rec_id as id','stsl.sales_date', 'cs.shop_name shop_id','wh.name as warehouse_name', 'stsl.warehouse_id', 'stsl.new_trades', 'stsl.new_trades_amount', 'stsl.check_trades', 'stsl.check_trades_amount', 'stsl.send_trades', 'stsl.send_trades_amount', 'stsl.send_unknown_goods_amount', 'stsl.send_trade_profit', 'stsl.post_amount', 'stsl.post_cost', 'stsl.commission', 'stsl.other_cost', 'stsl.package_cost', 'stsl.send_goods_cost', 'stsl.post_amount-stsl.post_cost AS post_profit','paid_trades','paid_trades_amount','IFNULL(cast((stsl.send_trades_amount/stsl.send_trades) as decimal(19,2)),0) as send_trade_avg_price' );
                $data = D('Statistics/StatMonthlySalesAmount')->getSearchlist($fields,$where_stat,$page,$rows,'stsl',$sort,$order);
                break;
            default:
                \Think\Log::write("unknown type:" . print_r($type, true));
                $data = array('total'=>0,'rows'=>array());
                break;
        }
        return $data;
    }
}