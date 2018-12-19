<?php
/**
 * 库存台账的控制器
 */
namespace Statistics\Controller;

use Think\Exception\BusinessLogicException;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
class StockLedgerController extends BaseController
{
    public function getStockLedger(){
        $id_list = DatagridExtention::getIdList(array('form','tool_bar','datagrid','day_start','day_end','help_id','edit','dailystat_start','dailystat_end'));
        $fields = D('Setting/UserData')->getDatagridField('Statistics/StockLedger','stockledger');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("StockLedger/loadDataByCondition"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'frozenColumns'=>D('Setting/UserData')->getDatagridField('Statistics/StockLedger','stockledger',1),
                'fitColumns'   => false,
                'singleSelect'=>false,
                'ctrlSelect'=>true
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
//        $checkbox=array('field' => 'ck','checkbox' => true);
//        array_unshift($datagrid['fields'],$checkbox);
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("StockLedger/loadDataByCondition");
        $params['datagrid']['id'] = $id_list['datagrid'];
        $params['search']['form_id'] = 'statistics-form';
        $params['help']['id'] = $id_list['help_id'];
        $params['edit']['id'] = $id_list['edit'];
        $params['help']['url'] = U('StatisticsCommon/getHelpInfo?type=stastics_date');
        $this->assign('tool_bar', $id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);
        $this->assign("params", json_encode($params));
        $current_date=date('Y-m-d',strtotime('-1 day'));
        $this->assign('current_date',$current_date);
        $list = UtilDB::getCfgRightList(array('warehouse','brand'));
        $this->assign('list',$list);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc'){
        $time=date('H');
        if($time>=8&&$time<19){//工作时间不可查询
            $data=array('total'=>0,'rows'=>array());
        }else{
            if(empty($search)){
                $search = array(
                    'day_start' => date('Y-m-d',strtotime('-1 day')),
                    'day_end' => date('Y-m-d',strtotime('-1 day'))
                );
            }
            $data = D('Statistics/StockLedger')->loadDataByCondition($page, $rows, $search , $sort , $order);
        }
        $this->ajaxReturn($data);
    }

    public function showLedgerDetial(){
        $spec_id=$_GET['id'];
        $id_list=array(
            'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
            'tool_bar'=>'ledger_detial_toolbar',
            'form_id'=>'ledger_detial_form'
        );
        $fields = D('Setting/UserData')->getDatagridField('Statistics/StockLedger','stockledgerdetial');
        $goods_data=D('Goods/GoodsSpec')->getGoodSpecData($spec_id);
        $datagrid=array(
            'id'=>$id_list['id_datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("StockLedger/getLedgerDetial"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'rownumbers'=>true,
                'ctrlSelect'=>true
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $params = array(
            'datagrid' => array('id' => $id_list['id_datagrid']),
            'search'=>array('form_id'=>$id_list['form_id'])
        );
        $this->assign('params', json_encode($params));
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $current_date=date('Y-m-d',strtotime('-1 day'));
        $this->assign('current_date',$current_date);
        $list = UtilDB::getCfgRightList(array('warehouse'));
        $this->assign('list',$list);
        $this->assign('goods_data',$goods_data[0]);
        $this->display('show_ledger_detial');
    }

    public function getLedgerDetial($page = 1, $rows = 20, $search, $sort = 'rec_id', $order = 'desc'){
        $data=D('Statistics/StockLedger')->getLedgerDetial($page, $rows, $search , $sort , $order);
        $this->ajaxReturn($data);
    }

}