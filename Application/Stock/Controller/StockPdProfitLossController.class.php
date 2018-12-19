<?php


namespace Stock\Controller;


use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Model;
use Common\Common;
use Think\Exception\BusinessLogicException;

class StockPdProfitLossController extends BaseController{
    
    public function getStockPdProfitLossList()
        {
            $id_list=array();
            $id_list=$this->getidlist($id_list,array('tool_bar','select','form','tab_container','datagrid'));
            $datagrid=array(
                    'id'=>$id_list['datagrid'],
                    'options'=>array(
                            'title' => '',
                            'url'=>U("StockPdProfitLoss/loadDataByCondition"),
                            'toolbar'=>"#{$id_list['tool_bar']}",
                            'fitColumns' => false,                         
                        ),
                    'fields'=>D('Setting/UserData')->getDatagridField('Stock/StockPdProfitLoss','pd_profit_loss'),//get_field('StockPdProfitLoss','pd_profit_loss'),
                    'class' => 'easyui-datagrid',
                    'style' =>'',
                );
            $params=array();
            $params['datagrid'] = array();      
            $params['datagrid']['id'] = $id_list['datagrid'];
            $params['datagrid']['url'] = U("StockPdProfitLoss/loadDataByCondition");
            $params['search']['form_id'] = 'StockPdProfitLoss-form';        

            
            $params['tabs']['id'] = $id_list['tab_container'];
            $params['tabs']['url'] = U('StockCommon/showTabDatagridData');

            $arr_tabs = array(
                array('id'=>$id_list['tab_container'],'url'=>U('StockCommon/showTabsView',array('tabs'=>"stockpddetail_specifics")).'?prefix=stockpdprofitloss&tab=stockpddetail_specifics','title'=>'盘点单详情'),
            );

            $combobox_list = UtilDB::getCfgRightList(array('brand','warehouse','employee'));
            $day_start = date('Y-m-d',strtotime('-1 month'));
            $day_end = date('Y-m-d');
            
            $this->assign('day_start',$day_start);
            $this->assign('day_end',$day_end);
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->assign('params', json_encode($params));
            $this->assign('brand_array', $combobox_list['brand']);
            $this->assign('warehouse_array', $combobox_list['warehouse']);
            $this->assign('creator_array',$combobox_list['employee']);
            $this->assign('arr_tabs',json_encode($arr_tabs));

            $this->display('show');
            
        }

      public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'pd_id', $order = 'desc')
    {
        if(empty($search)){
            $search = array(
                'day_start' => date('Y-m-d',strtotime('-1 month')),
                'day_end' => date('Y-m-d')        
             );
        }

        try{
            $data = D('Stock/StockPdProfitLoss')->loadDataByCondition($page, $rows, $search, $sort,$order);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage()."-getStockPdProfitLossList-");
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage()."-getStockPdProfitLossList-");
        }
        
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
                $type = I('get.type');
                foreach ($search as $k => $v) {
                    $key=substr($k,$startnum,strlen($k)-$endnum);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                D('StockPdProfitLoss')->exportToExcel($search,$type);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}
