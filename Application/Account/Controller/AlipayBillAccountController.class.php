<?php
namespace Account\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;
use Think\Exception\BusinessLogicException;
use Think\Exception;
use Common\Common\ExcelTool;
use Think\Log;

class AlipayBillAccountController extends BaseController
{
    public function getAlipayBillAccountList($page=1, $rows=20, $search = array(), $sort = 'abd.rec_id', $order = 'ASC')
    {
        if(IS_POST)
        {
            if(empty($search))
            {
                $search = array
                (
                    'start_time' => date('Y-m-d',strtotime('-7 day')),
                    'end_time' => date('Y-m-d')
                );
            }
            try
            {
                $data = D('AlipayBillAccount')->getAlipayBillAccountList($page, $rows, $search, $sort, $order);
            }catch(Exception $e)
            {
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else
        {
            $id_list  = array(
                'toolbar'       => 'alipay_bill_account_toolbar',
                'tab_container' => 'alipay_bill_account_container',
                'account_summary' => 'alipay_bill_account_summary',
                'datagrid'   => 'alipay_bill_account_datagrid',
                'edit'          => 'alipay_bill_account_dialog',
                'form'          => 'alipay_bill_account_form'
            );
            $fields = D('Setting/UserData')->getDatagridField('Account/AlipayBillAccount','alipay_bill_account');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("AlipayBillAccount/getAlipayBillAccountList"),
                    'toolbar' => $id_list["toolbar"],
                    'fitColumns'   => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true
                ),
                'class' => 'easyui-datagrid',
                'style'=>"overflow:scroll",
                'fields' => $fields,
            );
            $params  = array();
            $params['datagrid'] = array();
            $params['datagrid']['url'] = $datagrid['options']['url'];
            $params['datagrid']['id'] = $id_list['datagrid'];
            $params['search']['form_id'] = $id_list['form'];
            $params['id_list'] = $id_list;
            $list_form = UtilDB::getCfgList(array('shop'), array("shop" => array("is_disabled" => 0)));
            $current_date=date('Y-m-d',time());
            $query_start_date=date('Y-m-d',strtotime('-7 day'));
            $this->assign('current_date',$current_date);
            $this->assign('query_start_date',$query_start_date);
            $this->assign('list', $list_form);
            $this->assign("id_list",$id_list);
            $this->assign('tool_bar',$id_list['tool_bar']);
            $this->assign("params",json_encode($params));
            $this->assign('datagrid', $datagrid);
            $this->display('show');

        }
    }

    public function showAccountSummary($page=1, $rows=20, $search = array(), $sort = 'abd.rec_id', $order = 'ASC')
    {
        if(IS_POST)
        {
            try{
                $res = D('Account/AlipayBillAccount')->showAccountSummary($page, $rows, $search, $sort, $order);
            }catch (BusinessLogicException $e){
                $res['status']= 1;
                $res['info'] = $e->getMessage();
            }catch(\Exception $e){
                $res['status']= 1;
                $res['info'] = $e->getMessage();
            }
            $this->ajaxReturn($res);

        }else
        {
            $start_time     = I("get.start_time");
            $end_time       = I('get.end_time');
            $id_list  = array(
                "datagrid" => "alipay_bill_account_summary_datagrid",
                'toolbar'  => 'alipay_bill_account_summary_toolbar',
                "dialog"   => "alipay_bill_account_summary_file",
                'form'     => 'alipay_bill_account_summary_form'
            );
            $fields = D('Setting/UserData')->getDatagridField('Account/AlipayBillAccount','account_summary');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("AlipayBillAccount/showAccountSummary")."?start_time=$start_time&end_time=$end_time",
                    'toolbar' => $id_list["toolbar"],
                    'fitColumns'   => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true
                ),
                'class' => 'easyui-datagrid',
                'style'=>"overflow:scroll",
                'fields' => $fields,
            );

            $params  = array();
            $params['datagrid'] = array();
            $params['datagrid']['url'] = $datagrid['options']['url'];
            $params['datagrid']['id'] = $id_list['datagrid'];
            $params['search']['form_id'] = $id_list['form'];
            $params['id_list'] = $id_list;
            $list_form = UtilDB::getCfgList(array('shop'), array("shop" => array("is_disabled" => 0)));
            $this->assign('list', $list_form);
            $this->assign('summary_start_time',$start_time);
            $this->assign('summary_end_time',$end_time);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("params",json_encode($params));
            $this->display("account_summary");
        }

    }


}