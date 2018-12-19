<?php
/**
 * 入库汇总账的控制器
 */
namespace Statistics\Controller;

use Think\Exception\BusinessLogicException;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
class StockinCollectController extends BaseController
{
    public function getStockinCollect(){
        $id_list = DatagridExtention::getIdList(array('form','tool_bar','datagrid','day_start','day_end','help_id','dailystat_start','dailystat_end'));
        $fields = D('Setting/UserData')->getDatagridField('Statistics/StockinCollect','stockincollect');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                    'title' => '',
                    'url'   => U("StockinCollect/loadDataByCondition"),
                    'toolbar' => "#{$id_list['tool_bar']}",
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Statistics/StockinCollect','stockincollect',1),
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
        $params['datagrid']['url'] = U("StockinCollect/loadDataByCondition");
        $params['datagrid']['id'] = $id_list['datagrid'];
        $params['search']['form_id'] = 'statistics-form-stock-in';
        $params['help']['id'] = $id_list['help_id'];
        $params['help']['url'] = U('StatisticsCommon/getHelpInfo?type=stastics_date');
        $this->assign('tool_bar', $id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);
        $this->assign("params", json_encode($params));
        $current_date=date('Y-m-d',strtotime('-1 day'));
        $this->assign('current_date',$current_date);
        $list = UtilDB::getCfgRightList(array('warehouse','brand','employee'));
        $this->assign('list',$list);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'spec_id', $order = 'desc'){
        if(empty($search)){
            $search = array(
                'day_start' => date('Y-m-d',strtotime('-1 day')),
                'day_end' => date('Y-m-d',strtotime('-1 day'))        
             );
        }
        $data = D('Statistics/StockinCollect')->loadDataByCondition($page, $rows, $search , $sort , $order);
        $this->ajaxReturn($data);
    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $result = array('status'=>0,'info'=>'');
        $id_list = I('get.id_list');
        try{
            $search = I('get.search','',C('JSON_FILTER'));
            $startnum = strlen('search[');
            $endnum = strlen('search[]');
            foreach ($search as $k => $v) {
                $key=substr($k,$startnum,strlen($k)-$endnum);
                $search[$key]=$v;
                unset($search[$k]);
            }
            D('StockinCollect')->exportToExcel($search, $id_list);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }

}