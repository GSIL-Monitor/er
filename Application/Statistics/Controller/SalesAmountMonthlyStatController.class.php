<?php
namespace Statistics\Controller;

use Common\Common\Factory;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

 class SalesAmountMonthlyStatController extends BaseController
{
    
    public function getStatList(){
        
        $id_list = DatagridExtention::getIdList(array('form','tool_bar','datagrid','month_start','month_end','help_id'));
        $fields = D('Setting/UserData')->getDatagridField('Statistics/SalesAmountMonthlyStat','sales_amount_monthly_stat');
        $datagrid = array(
    		'id'=>$id_list['datagrid'],
    		'options'=> array(
    				'title' => '',
    				'url'   => U("SalesAmountMonthlyStat/loadDataByCondition"),
    				'toolbar' => "#{$id_list['tool_bar']}",
    				'fitColumns'   => false,
    				'singleSelect'=>false,
    				'ctrlSelect'=>true
    		),
    		'fields' => $fields,
    		'class' => 'easyui-datagrid',
    		'style'=>"overflow:scroll",
    	);
//         D('Statistics/SalesAmountDailyStat','Controller')->ReflashSalesDaysell();
        $params['datagrid'] = array();
		$params['datagrid']['url'] = U("SalesAmountMonthlyStat/loadDataByCondition");
		$params['datagrid']['id'] = $id_list['datagrid'];
		$params['search']['form_id'] = $id_list['form'];
        $params['help']['id'] = $id_list['help_id'];
        $params['help']['url'] = U('StatisticsCommon/getHelpInfo?type=stastics_date');
         //用作获取form的作用的，用来提交查询信息

        $params['id_list'] = $id_list;
		//赋值form中的下拉列表的 list
		$list_form=UtilDB::getCfgRightList(array('shop'));
        
		$this->assign("list",$list_form);
		//最后统计时间
		$update_time = get_config_value('cfg_statsales_date');
		$this->assign('update_time',$update_time);
		//当前时间
		$this->assign('current_date',date('Y-m'));
		
		$this->assign("params",json_encode($params));
		$this->assign('datagrid', $datagrid);
		$this->assign("id_list",$id_list);
		$this->display('sales_amount_monthly_edit');
		
    }


    public function loadDataByCondition($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc'){
//         $this->ReflashSalesMonthsell();
        if(empty($search)){
            $search = array(
                'month_start' => date('Y-m',time()),
                'month_end' => date('Y-m',time())        
             );
        }
        $data = D('Statistics/StatSales')->loadDataByCondition($page, $rows, $search , $sort , $order,$type= 'SalesAmountMonthlyStat' );

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
                D('StatMonthlySalesAmount')->exportToExcel($search);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}