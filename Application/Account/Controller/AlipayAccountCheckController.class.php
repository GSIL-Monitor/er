<?php
namespace Account\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Think\Exception;
use Think\Log;

class AlipayAccountCheckController extends BaseController
{
    public function getAlipayAccountCheckList($page=1, $rows=20, $search = array(), $sort = 'faac.rec_id', $order = 'ASC')
    {
        if(IS_POST)
        {
            try
            {
                $data = D('AlipayAccountCheck')->getAlipayAccountCheckList($page, $rows, $search, $sort, $order);
            }catch(Exception $e)
            {
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else
        {
            $id_list  = array(
                'toolbar'       => 'alipay_account_check_toolbar',
                'tab_container' => 'alipay_account_check_container',
                'datagrid'      => 'alipay_account_check_datagrid',
                'edit'          => 'alipay_account_check_dialog',
                'form'          => 'alipay_account_check_form'
            );
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("AlipayAccountCheck/getAlipayAccountCheckList"),
                    'toolbar' => $id_list["toolbar"],
                    'fitColumns'   => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true
                ),
                'class' => 'easyui-datagrid',
                'style'=>"overflow:scroll",
                'fields' => get_field('AlipayAccountCheck','alipay_account_check'),
            );
            //联动
            $arr_tabs = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "订单详情",
                    "url"   => U("AccountCommon/showTabsView") . "?tab=sales_trade&prefix=alipayAccountCheck"
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "支付宝账单",
                    "url"   => U("AccountCommon/showTabsView") . "?tab=alipay_account_bill&prefix=alipayAccountCheck"
                ),
               /* array(
                    "id"    => $id_list["tab_container"],
                    "title" => "收款单详情",
                    "url"   => U("AccountCommon/getTabsView") . "?tab=payment_bill&prefix=alipayAccountCheck"
                ),*/
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "对账单日志",
                    "url"   => U("AccountCommon/showTabsView") . "?tab=alipay_account_check_log&prefix=alipayAccountCheck"
                )
            );
            /*$checkbox = array('field' => 'ck','checkbox' => true );
            array_unshift($datagrid['fields'],$checkbox);*/
            $params = array(
                'id_list' => $id_list,
                'datagrid' => array(
                    'url' =>  $datagrid['options']['url'],
                    'id' => $id_list['datagrid'],
                ),
                'search' => array('form_id'=> $id_list['form']),
                'tabs' => array('id'  => $id_list["tab_container"],'url' => U("AccountCommon/updateTabsData"))
            );
            $account_check_status = C("alipay_account_check_status");
            $check_status_all[0] = array('key'=>'all','value'=>'全部');
            $check_status = array_merge($check_status_all, $account_check_status);
            $list_form = UtilDB::getCfgList(array('shop'), array("shop" => array("is_disabled" => 0)));
            /*$current_date=date('Y-m-d',time());
            $query_start_date=date('Y-m-d',strtotime('-7 day'));
            $this->assign('current_date',$current_date);
            $this->assign('query_start_date',$query_start_date);*/
            $this->assign('account_status',$check_status);
            $this->assign('list', $list_form);
            $this->assign("id_list",$id_list);
            $this->assign("arr_tabs", json_encode($arr_tabs));
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
                $res = D('Account/AlipayAccountCheck')->showAccountSummary($page, $rows, $search, $sort, $order);
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
                "datagrid" => "alipay_account_check_summary_datagrid",
                'toolbar'  => 'alipay_account_check_summary_toolbar',
                "dialog"   => "alipay_account_check_summary_file",
                'form'     => 'alipay_account_check_summary_form'
            );
            $fields = D('Setting/UserData')->getDatagridField('Account/AlipayAccountCheck','account_summary');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("AlipayAccountCheck/showAccountSummary")."?start_time=$start_time&end_time=$end_time",
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

    public function alipayAccountCheckSetSuccess()
    {
        try
        {
            $id = I('post.id');
            $res = array('status'=>0,'info'=>'操作成功');
            D('AlipayAccountCheck')->alipayAccountCheckSetSuccess($id);
        }catch (\PDOException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch(\Exception $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }
        $this->ajaxReturn($res);
    }

}