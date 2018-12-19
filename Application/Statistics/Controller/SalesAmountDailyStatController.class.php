<?php
/**
 * 销售日统计的控制器
 *
 * @author gaosong
 * @date: 15/12/20
 * @time: 下午02:09
 */
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
class SalesAmountDailyStatController extends BaseController
{
    /**
     *获取销售日统计
     */
    public function getDailystat()
    {
//         $this->ReflashSalesDaysell();
        $id_list = DatagridExtention::getIdList(array('form','tool_bar','datagrid','day_start','day_end','help_id','dailystat_start','dailystat_end'));
        $fields = D('Setting/UserData')->getDatagridField('Statistics/SalesAmountDailyStat','stat_sales_daysell');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                    'title' => '',
                    'url'   => U("SalesAmountDailyStat/loadDataByCondition"),
                    'toolbar' => "#{$id_list['tool_bar']}",
                    'fitColumns'   => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("SalesAmountDailyStat/loadDataByCondition");
        $params['datagrid']['id'] = $id_list['datagrid'];
        $params['search']['form_id'] = 'statistics-form';
        $params['help']['id'] = $id_list['help_id'];
        $params['help']['url'] = U('StatisticsCommon/getHelpInfo?type=stastics_date');
        $this->assign('tool_bar', $id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);
        $this->assign("params", json_encode($params));
        $current_date=date('Y-m-d',strtotime('-1 day'));
        $list = UtilDB::getCfgRightList(array('shop'));
        $shop_array = $list['shop'];
        $this->assign('current_date',$current_date);
        $this->assign('shop_array', $shop_array);
        $this->assign('shop', json_encode($list['shop']));
        $update_time = get_config_value('cfg_statsales_date_time');
        $this->assign('update_time',$update_time);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        if(empty($search)){
            $search = array(
                'day_start' => date('Y-m-d',strtotime('-1 day')),
                'day_end' => date('Y-m-d',strtotime('-1 day'))        
             );
        }
//        $this->ajaxReturn(D("StatDailySalesAmount")->loadDataByCondition($page, $rows, $search, $sort, $order));
        $data = D('Statistics/StatSales')->loadDataByCondition($page, $rows, $search , $sort , $order,$type= 'SalesAmountDailyStat' );
        $this->ajaxReturn($data);
    }
    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $result = array('status'=>0,'info'=>'');
        try{
                $search = I('get.search','',C('JSON_FILTER'));
                $startnum = strlen('search[');
                $endnum = strlen('search[]');
                foreach ($search as $k => $v) {
                    $key=substr($k,$startnum,strlen($k)-$endnum);
                    $search[$key]=$v;
                    unset($search[$k]);
                }

                D('StatDailySalesAmount')->exportToExcel($search);
                        }

        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
   /*  public function ReflashSalesDaysell()
    {
        try {
            D('Statistics/StatDailySalesAmount')->ReflashSalesDaysell();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-ReflashSalesDaysell-'.$msg);
        }
       
    } */
    
}