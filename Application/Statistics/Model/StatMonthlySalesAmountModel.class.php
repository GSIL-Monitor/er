<?php
namespace Statistics\Model;

use Common\Common\ExcelTool;
use Common\Common\DatagridExtention;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;

class StatMonthlySalesAmountModel extends Model
{
    protected $tableName = 'stat_monthly_sales_amount';
    protected $pk ='rec_id';
    public function ReflashSalesMonthsell($final_date,$now_date){
        try {
            $this->startTrans();
            $res_day_fields = array(
                "DATE_FORMAT(sales_date,'%Y-%m') sales_date",
        		"shop_id",
        		"warehouse_id",
        		"new_trades",
        		"new_trades_amount",
        		"check_trades",
        		"check_trades_amount",
        		"send_trades",
        		"send_trades_amount",
        		"send_goods_cost",
        		"send_trade_profit",
        		"send_unknown_goods_amount",
        		"post_amount",
        		"post_cost",
        		"commission",
        		"other_cost",
        		"package_cost",
        		"sales_drawback"
            );
            
            $res_day_where = array(
                'sales_date' => array(array('EGT',$final_date),array('lt',$now_date)),
            );
            $res_day_amount = D('Statistics/StatDailySalesAmount')->getStatDailySalesAmountList($res_day_fields,$res_day_where);
            if(!empty($res_day_amount))
            {
                $res_stat_month_update = array(
                    'new_trades'                => array('exp', 'new_trades + VALUES(new_trades)'),
                    'new_trades_amount'         => array('exp', 'new_trades_amount + VALUES(new_trades_amount)'),
                    'check_trades'              => array('exp', 'check_trades + VALUES(check_trades)'),
                    'check_trades_amount'       => array('exp', 'check_trades_amount + VALUES(check_trades_amount)'),
                    'send_trades'               => array('exp', 'send_trades +VALUES(send_trades)'),
                    'send_trades_amount'        => array('exp', 'send_trades_amount + VALUES(send_trades_amount)'),
                    'send_goods_cost'           => array('exp', 'send_goods_cost + VALUES(send_goods_cost)'),
                    'send_trade_profit'         => array('exp', 'send_trade_profit + VALUES(send_trade_profit)'),
                    'send_unknown_goods_amount' => array('exp', 'send_unknown_goods_amount + VALUES(                    send_unknown_goods_amount)'),
                    'post_amount'               => array('exp', 'post_amount + VALUES(post_amount)'),
                    'post_cost'                 => array('exp', 'post_cost + VALUES(post_cost)'),
                    'commission'                => array('exp', 'commission + VALUES(commission)'),
                    'other_cost'                => array('exp', 'other_cost + VALUES(other_cost)'),
                    'package_cost'              => array('exp', 'package_cost + VALUES(package_cost)'),
                    'sales_drawback'            => array('exp','sales_drawback + VALUES(sales_drawback)')
                );
                $res_insert_month_stat = $this->addStatMonthlySalesAmount($res_day_amount,$res_stat_month_update);
                $res_insert_final_stat_time = D('Setting/System')->addSystem(array('value'=>array('exp','CURDATE()'),'key'=>'cfg_statsales_date','class'=>'system'),array('value'=>array('exp','VALUES(value)')));
                \Think\Log::write('success insert day to month num:'.count($res_day_amount).'--\n'.print_r($res_day_amount,true),\Think\Log::DEBUG);
                
            }
            
            $this->commit();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-ReflashSalesMonthsell-'.$msg);
            $this->rollback();
        }
    }
    public function addStatMonthlySalesAmount($data,$update=false,$options='')
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
    public function getSearchlist($fields,$where,$page,$rows,$alias,$sort,$order)
    {
        try {
            $res_total = $this->alias($alias)->where($where)->count('*');

            $res_data = $this->alias($alias)->field($fields)->join('left join cfg_shop cs on cs.shop_id = stsl.shop_id left join cfg_warehouse wh on wh.warehouse_id = stsl.warehouse_id')->where($where)->order($sort.' '.$order)->page($page,$rows)->select();
            $data=array('total'=>$res_total,'rows'=>$res_data);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStatDailySalesAmountList-'.$msg);
            $data=array('total'=>0,'rows'=>array());
        }
        return $data;
    }

public function exportToExcel($search){
        $creator=session('account');
        $data = array();
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        $where=array(
            'ags.shop_id' => array('in',$search['shop_id']),
			'ags.warehouse_id' => array('in',$search['warehouse_id']),
            'sales_date' => array('between',array($search['month_start'],$search['month_end'])),
        );

        try{
	              $arr = $this->alias('ags')->field('ags.rec_id,ags.sales_date,ags.shop_id as shop_id,ags.warehouse_id,ags.new_trades,ags.new_trades_amount,ags.check_trades,ags.check_trades_amount,ags.send_trades,ags.send_trades_amount,ags.send_unknown_goods_amount,ags.send_goods_cost,ags.send_trade_profit,cs.shop_name,ags.post_cost ,cd.name,IFNULL(cast((ags.send_trades_amount/ags.send_trades) as decimal(19,2)),0) as send_trade_avg_price')
                                            ->where($where)->join('left join cfg_shop cs on cs.shop_id=ags.shop_id')
			->join('left join cfg_warehouse cd on cd.warehouse_id=ags.warehouse_id')->order('sales_date desc')->select();
            $num = workTimeExportNum();
            if(count($arr)>$num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $amount = array();
            $amount['sales_date']  =  "合计";
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
                $excel_header = $excel_header = D('Setting/UserData')->getExcelField('Statistics/SalesAmountMonthlyStat','sales_amount_monthly_stat');

            $title = '销售月统计';
            $filename = '销售月统计';
            $width_list = array('10','10','10','20','20','20','20','20','20','20','20','20','20','20');
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

?>