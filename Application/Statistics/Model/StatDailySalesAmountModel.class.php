<?php
/**
 * 销售日统计的表Model
 *
 * @author gaosong
 * @date: 15/12/20
 * @time: 03:09
 */
namespace Statistics\Model;
use Common\Common\ExcelTool;
use Common\Common\DatagridExtention;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;



class StatDailySalesAmountModel extends Model
{
    protected $tableName = 'stat_daily_sales_amount';//'stat_sales_daysell';
    protected $pk = 'rec_id';
    //日志统计接口以后不用了
    public function ReflashSalesDaysell()
    {
        
        $now_current_time = date('Y-m-d H:i:s',time());//包含当前时分秒的
        
        $now_end_date = date('Y-m-d',time());
        //获取当前日期的时间 日期+00:00:00
        $now_end_time = date('Y-m-d H:i:s',strtotime(date('Y-m-d',strtotime($now_current_time))));
        
        $date_value = D('Setting/System')->getOneSysteSetting('cfg_statsales_date');
        if(empty($date_value))
        {
            $final_stat_sales_date = date('Y-m-d',strtotime('-10 day',strtotime($now_end_date)));
        }else{
            $final_stat_sales_date = $date_value[0]['value'];
        }
        $time_value = D('Setting/System')->getOneSysteSetting('cfg_statsales_date_time');
        if(empty($time_value))
        {
            $final_stat_sales_time = date('Y-m-d H:i:s',strtotime($final_stat_sales_date));
        }else{
            $final_stat_sales_time = $time_value[0]['value'];
        }
        //当前统计的时间节点
        $current_stat_time = $final_stat_sales_time;
               
        do {
            try {
                if(strtotime($current_stat_time)>=strtotime($now_end_time))
                {
                    break;
                }

                $this->startTrans();
                \Think\Log::write('current_stat_time:'.$current_stat_time,\Think\Log::DEBUG);
                $conditions = array(
                    'type'=>array('in','3,9,10,12,105'),
                    'created'=>array(array('gt',$current_stat_time),array('lt',$now_end_time)),
                );
                //每1000条，查询当前更新条数的最大时间
                $max_time_log = D('Trade/SalesTradeLog')->getSalesTradeLogMaxCreated($conditions,1000);
              
                if(empty($max_time_log) || strtotime($max_time_log[0]['max_data_time']) == strtotime($current_stat_time))
                {
                    $limit_max_time = $now_end_time;
                }else {
                    $limit_max_time = $max_time_log[0]['max_data_time'];
                }
                // 插入递交成功的订单数据信息
                $dl_log_fields = array(
                    'DATE(stl.created) AS sales_date',
                    'st.shop_id',
                    'st.warehouse_id',
                    'st.trade_id',
                    '1 AS new_trades',
                    'st.receivable AS new_trades_amount' //统计的是应收金额
                );
                //2016-1-11 去掉驳回的失效的日志 data=99
                $dl_log_conditions = array(
                    'type'=>3,
                    'stl.created'=>array(array('egt',$current_stat_time),array('lt',$limit_max_time)),
                    'data'=>array('neq',99)
                );
                $res_dl_log = M('sales_trade_log')->alias('stl')->distinct(true)->field($dl_log_fields)->join('inner join sales_trade st on st.trade_id = stl.trade_id')->where($dl_log_conditions)->select();
                
                if(!empty($res_dl_log))
                {
                    $dl_stat_daysell_update=array(
                        'new_trades'=>array('exp','new_trades+VALUES(new_trades)'),
                        'new_trades_amount'=>array('exp','new_trades_amount+VALUES(new_trades_amount)'),
                    );
                    $res_dl_stat_daysell_insert = $this->addStatSalesDaysell($res_dl_log,$dl_stat_daysell_update);
                    \Think\Log::write('success insert dl num:'.count($res_dl_log).'--\n'.print_r($res_dl_log,true),\Think\Log::DEBUG);
                }
                
                // 已审核订单
                $check_log_fields = array(
                    "DATE(stl.created) AS sales_date",
                    "st.shop_id",
                    "st.warehouse_id",
                    "st.trade_id",
                    "1 AS check_trades",
                    "st.receivable AS check_trades_amount"
                );
                $check_log_conditions = array(
                    'type'=>array('in','9,10,12'),
                    'stl.created'=>array(array('egt',$current_stat_time),array('lt',$limit_max_time)),
                    'data'=>array('neq',99)
                );
                $res_check_log = M('sales_trade_log')->alias('stl')->distinct(true)->field($check_log_fields)->join('inner join sales_trade st on st.trade_id = stl.trade_id')->where($check_log_conditions)->select();
                
                if(!empty($res_check_log))
                {
                    $check_stat_daysell_update=array(
                        'check_trades'=>array('exp','check_trades+VALUES(check_trades)'),
                        'check_trades_amount'=>array('exp','check_trades_amount+VALUES(check_trades_amount)'),
                    );
                    $res_check_stat_daysell_insert = $this->addStatSalesDaysell($res_check_log,$check_stat_daysell_update);
                    
                }
                \Think\Log::write('success insert check num:'.count($res_check_log).'--\n'.print_r($res_check_log,true),\Think\Log::DEBUG);
                
                // 已出库的
                $stockout_log_fields = array(
                    "DATE(so.consign_time) AS sales_date",
                    "so.src_order_id",
                    "st.shop_id",
                    "st.warehouse_id",
                    "1 AS send_trades",
                    "st.receivable AS send_trades_amount",  //发货订单金额
                    "st.post_amount AS post_amount", //实收邮资总和
                    //分摊后的金额+退货成本价（收的）+邮费分摊-退货金额-成本价-未知成本价-佣金
                    //amount+return_cost+post_amount-return_amount-goods_cost-unknown_goods_amount-commission-post_cost
                    
                    /* 分摊和应收之间的关系  */
                    /* 下单时间，付款时间，已发货时间统计的单品的信息有何区别，和递交，审核，出库之间的区别 */
                    //应收-成本-邮费-包装费-未知成本-佣金-其他花费
                    "(st.receivable-so.goods_total_cost-so.post_cost-so.package_cost-so.unknown_goods_amount-st.commission-st.other_cost) AS send_trade_profit",//发货订单毛利
                    "so.unknown_goods_amount AS send_unknown_goods_amount",  //未知成本销售总额
                    "st.commission",
                    "st.other_cost",
                    "so.goods_total_cost AS send_goods_cost",  //发货订单货品成本
                    "so.post_cost",  //邮资成本
                    "so.package_cost"
                
                );
                $stockout_log_conditions = array(
                    'so.consign_time'       => array(array('egt',$current_stat_time),array('lt',$limit_max_time)),
                    'so.status'             => array('egt',95),
                    'so.src_order_type'     => array('eq',1)
                );
                $res_stockout_log = M('stockout_order')->alias('so')->distinct(true)->field($stockout_log_fields)->join('INNER JOIN sales_trade st ON st.trade_id = so.src_order_id AND st.trade_no = so.src_order_no ')->where($stockout_log_conditions)->select();
                
                if(!empty($res_stockout_log))
                {
                    $stockout_stat_daysell_update=array(
                        'send_trades'               => array('exp', 'send_trades + VALUES(send_trades)'),
                        'send_trades_amount'        => array('exp', 'send_trades_amount + VALUES(send_trades_amount)'),
                        'post_amount'               => array('exp', 'post_amount +VALUES(post_amount)'),
                        'send_trade_profit'         => array('exp', 'send_trade_profit + VALUES(send_trade_profit)'),
                        'send_unknown_goods_amount' => array('exp', 'send_unknown_goods_amount + VALUES(send_unknown_goods_amount)'),
                        'commission'                => array('exp', 'commission +VALUES(commission)'),
                        'other_cost'                => array('exp', 'other_cost +VALUES(other_cost)'),
                        'send_goods_cost'           => array('exp', 'send_goods_cost + VALUES(send_goods_cost)'),
                        'post_cost'                 => array('exp', 'post_cost + VALUES(post_cost)'),
                        'package_cost'              => array('exp', 'package_cost + VALUES(package_cost)')
                    );
                    $res_stockout_stat_daysell_insert = $this->addStatSalesDaysell($res_stockout_log,$stockout_stat_daysell_update);
                }
                \Think\Log::write('success insert stockout num:'.count($res_stockout_log).'--\n'.print_r($res_stockout_log,true),\Think\Log::DEBUG);
                
                $current_stat_time = $limit_max_time;
                $res_insert_final_stat_time = D('Setting/System')->addSystem(array('value'=>$limit_max_time,'key'=>'cfg_statsales_date_time','class'=>'system'),array('value'=>array('exp','VALUES(value)')));
                $this->commit();
            } catch (\PDOException $e) {
                $this->rollback();
                \Think\Log::write($this->name.'-ReflashSalesDaysell-'.$e->getMessage());
                \Think\Log::write($this->name.'-ReflashSalesDaysell- current_stat_time:'.$current_stat_time.';current_time:'.$now_current_time);
                E(self::PDO_ERROR);
            }
       }while(1);
       
       if(strtotime($limit_max_time)<>strtotime($now_end_time))
       {
           return;
       }
       D('Statistics/StatMonthlySalesAmount')->ReflashSalesMonthsell($final_stat_sales_date,$now_end_date);
    }
    
    public function addStatSalesDaysell($data,$update=false,$options='')
    {
        try {
            if (empty($data[0])) {
                $res = $this->add($data,$options,$update);
                
            }else
            {
                $res = $this->addAll($data,$options,$update);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-addStatSalesDaysell-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }
   
    public function getStatDailySalesAmountList($fields,$conditions=array())
    {
        try {
            $res = $this->field($fields)->where($conditions)->select();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStatDailySalesAmountList-'.$msg);
            E(self::PDO_ERROR);
        }
        return $res;
    }
    
    public function getSearchlist($fields,$where,$page,$rows,$alias,$sort,$order)
    {
        try {
            $total = $this->alias($alias)->where($where)->count();
            $res_data = $this->alias($alias)->field($fields)->join('left join cfg_shop cs on cs.shop_id = stsl.shop_id left join cfg_warehouse wh on wh.warehouse_id = stsl.warehouse_id')->where($where)->order($sort.' '.$order)->page($page,$rows)->select();
            $data=array('total'=>$total,'rows'=>$res_data);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStatDailySalesAmountList-'.$msg);
            $data=array('total'=>0,'rows'=>array());
        }
        return $data;
    }
    public function exportToExcel($search){
        $creator=session('account');
        $data=array();
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        $where=array(
            'ags.shop_id' => array('in',$search['shop_id']),
			'ags.warehouse_id' => array('in',$search['warehouse_id']),
            'sales_date' => array('between',array($search['day_start'],$search['day_end'])),
        );

        try{
                  $arr = $this->alias('ags')->field('ags.rec_id,ags.sales_date,ags.shop_id,ags.warehouse_id,ags.new_trades,ags.new_trades_amount,ags.check_trades,ags.check_trades_amount,ags.send_trades,ags.send_trades_amount,ags.send_unknown_goods_amount,ags.send_goods_cost,ags.send_trade_profit,cs.shop_name,cd.name,ags.post_cost,IFNULL(cast((ags.send_trades_amount/ags.send_trades) as decimal(19,2)),0) as send_trade_avg_price')
                  ->where($where)->join('left join cfg_shop cs on cs.shop_id=ags.shop_id')
                  ->join('left join cfg_warehouse cd on cd.warehouse_id=ags.warehouse_id')->order('sales_date desc')->select();
            $num = workTimeExportNum();
            if(count($arr)>$num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $amount = array();
            $amount['sales_date']  =  "合计";
            $amount['shop_name']  =  ' ';
            $amount['warehouse_name'] = ' ';
            $amount['send_trade_avg_price'] = ' ';
            foreach($arr as $k=>$v){
                $row['sales_date'] = $v['sales_date'];
                $row['shop_id'] = $v['shop_name'];
                $row['warehouse_name'] = $v['name'];
                $row['new_trades'] = $v['new_trades'];
                $row['new_trades_amount'] = $v['new_trades_amount'];
                $row['check_trades'] = $v['check_trades'];
                $row['check_trades_amount'] = $v['check_trades_amount'];
                $row['send_trades'] = $v['send_trades'];
                $row['send_trades_amount'] = $v['send_trades_amount'];
                $row['send_unknown_goods_amount'] = $v['send_unknown_goods_amount'];
                $row['send_goods_cost'] = $v['send_goods_cost'];
                $row['send_trade_profit'] = $v['send_trade_profit'];
                $row['post_cost'] = $v['post_cost'];
                $row['send_trade_avg_price'] = $v['send_trade_avg_price'];

                $amount['new_trades'] += $row['new_trades'];
                $amount['new_trades_amount'] += $row['new_trades_amount'];
                $amount['send_trades'] += $row['send_trades'];
                $amount['send_trades_amount'] += $row['send_trades_amount'];
                $amount['check_trades'] +=$row['check_trades'];
                $amount['check_trades_amount'] += $row['check_trades_amount'];
                $amount['send_unknown_goods_amount'] += $row['send_unknown_goods_amount'];
                $amount['send_goods_cost'] += $row['send_goods_cost'];
                $amount['send_trade_profit'] += $row['send_trade_profit'];
                $amount['post_cost'] += $row['post_cost'];
                $data[]=$row;
            }
                $data[] = $amount;     
                $excel_header = D('Setting/UserData')->getExcelField('Statistics/SalesAmountDailyStat','stat_sales_daysell');
            $title = '销售日统计';
            $filename = '销售日统计';
            $width_list = array('10','10','10','20','20','20','20','20','20','30','20','20','20','20');
            ExcelTool::Arr2Excel($data,$title,$excel_header,$width_list,$filename,$creator);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
    
}