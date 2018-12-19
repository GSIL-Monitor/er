<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2016/5/31
 * Time: 15:48
 */

namespace Account\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Log;


class LogisticsFeeManagementController extends BaseController
{
    public function getLogisticsFeeList($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc'){
        if(IS_POST){
            try{
                $data = array('total'=>0,'rows'=>array());
                D("LogisticsFeeManagement")->getLogisticsFeeList($page, $rows, $search, $sort, $order,$data);
            }catch(BusinessLogicException $e){
                $data = array('total'=>0,'rows'=>array());
            }catch(Exception $e){
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else{
            $id_list = array(
                'form'  =>'logistics_cost_management_form',
                'toolbar'   =>'logistics_cost_management_toolbar',
                'tab_container' =>'logistics_cost_management_tab_container',
                'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
                'edit'  =>'logistics_cost_management_edit_dialog',
                'more_button'=>'logistics_cost_management_more_button',
                'more_content'=>'logistics_cost_management_more_content',
                'hidden_flag'=>'logistics_cost_management_hidden_flag',
                'tab_container'=>'logistics_cost_management_tab_container'
            );
            $datagrid = array(
                'id' => $id_list['id_datagrid'],
                'style' => '',
                'class' => '',
                'options' => array(
                    'title' => '',
                    'url' => U('LogisticsFeeManagement/getLogisticsFeeList'),
                    'toolbar' => "#{$id_list['toolbar']}",
                    'fitColumns'=>false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true,
                ),
                'fields' => D('Setting/UserData')->getDatagridField('Account/LogisticsFeeManagement','logistics_fee_management')
            );
            $checkbox = array('field' => 'ck','checkbox' => true );
            array_unshift($datagrid['fields'],$checkbox);

            $arr_tabs=array(
                array('id'=>$id_list['tab_container'],'url'=>U('Account/AccountCommon/showTabsView',array('tabs'=>'goods_list')).'?tab=goods_list&prefix=logisticsFeeManagement&app=Trade/TradeCommon','title'=>'货品列表')
            );
            $params = array(
                'controller'    =>strtolower(CONTROLLER_NAME),
                'datagrid'  => array("id"  => $id_list["id_datagrid"],),
                'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
                'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('Account/LogisticsFeeManagement/showTabDatagridData')),
            );
            $list_form=UtilDB::getCfgRightList(array('logistics','shop','warehouse'));
            $this->assign('list',$list_form);
            $this->assign('id_list',$id_list);
            $this->assign('datagrid',$datagrid);
            $this->assign('arr_tabs', json_encode($arr_tabs));
            $this->assign('params',json_encode($params));
            $this->display('show');
        }

    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                $startnum = strlen('search[');
                $endnum = strlen('search[]');
                foreach ($search as $k => $v) {
                    $key=substr($k,$startnum,strlen($k)-$endnum);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
            }else{
                $search['rec_id'] = $id_list;
            }
            D('LogisticsFeeManagement')->exportToExcel($search, $type);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }

    public function showTabDatagridData($id){
        $id=intval($id);
        $data=D('Account/LogisticsFeeManagement')->getGoodsList($id);
        $this->ajaxReturn($data);
    }
}