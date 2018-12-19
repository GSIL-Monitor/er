<?php
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;

class StatisticsCommonController extends BaseController
{
    public function showTabsView($tab,$prefix,$field='Statistics/LogisticsFeeManagement'){
        $arr_tab = array(
            'logistics_order_detail' => 'logistics_order_detail',
            'logistics_order_log'   => 'logistics_order_log',
            'sales_logistics_trace' => 'sales_logistics_trace',
            'logistics_trace_detail' => 'logistics_trace_detail'
        );
        if(empty($arr_tab[$tab]))
        {
            \Think\Log::write('TradeCommon->联动未知的tabs:'.$tab);
            return null;
        }
        $tab_view = 'tabs_statistics_fee_management';
        $datagrid = array(
            'id' => $prefix.'datagrid_'.$tab,
            'style' => '',
            'class' => 'easyui-datagrid',
            'options' => array(
                'title'=>'',
                'pagination'=>false,
                'fitColumns'=>false
            ),
            'fields' =>get_field($field,$arr_tab[$tab])
        );
        if(count($datagrid['fields'])<12)
        {
            $datagrid['options']['fitColumns']=true;
        }
        $this->assign('datagrid',$datagrid);
        $this->display($tab_view);

    }
    public function updateTabsData($id,$datagridId){
        $data = array();
        if(intval($id) == 0){
            $data = array('total'=>0,'rows'=>array());
            $this->ajaxReturn($data);
        }
        $tab=substr($datagridId, strpos($datagridId, '_')+1);
        switch($tab){
            case 'logistics_order_detail':
                $data = D('LogisticsFeeManagement')->getLogisticsOrderDetail($id);
                break;
            case 'logistics_order_log':
                $data = D('LogisticsFeeManagement')->getLogisticsOrderLog($id);
                break;
            case 'logistics_trace_detail':
                $data = D('SalesLogisticsTrace')->getLogisticsTraceDetail($id);
                break;
        }
        $this->ajaxReturn($data);
    }
    public function getHelpInfo(){
        $type = $_GET['type'];
        $this->assign('type',$type);
        $this->display('help_info');
    }

}