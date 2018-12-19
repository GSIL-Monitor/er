<?php
namespace Account\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;
use Think\Exception\BusinessLogicException;
use Think\Exception;
use Common\Common\ExcelTool;
use Think\Log;
use Common\Common\UtilTool;

class StallsPurchaserAccountController extends BaseController
{
    public function getStallsPurchaserAccountList($page=1, $rows=20, $search = array(), $sort = 'ospl.rec_id', $order = 'desc')
    {
        if(IS_POST) {
            try
            {
            if(empty($search)){
                $search = array(
                    'purchaser_start_time' => date('Y-m-d ',time()).'00:00:00',
                    'purchaser_end_time' => date('Y-m-d H:i:s'),
                );
            }
                $data = array('total'=>0,'rows'=>array());
                $this->ajaxReturn(D('Account/StallsPurchaserAccount')->getStallsPurchaserAccountList($page, $rows, $search, $sort, $order));
            }catch(Exception $e)
            {
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else
        {
            $id_list  = array(
                'toolbar'       => 'stalls_purchaser_account_toolbar',
                'tab_container' => 'stalls_purchaser_account_container',
                'datagrid'      => 'stalls_purchaser_account_datagrid',
                'edit'          => 'stalls_purchaser_account_dialog',
                'form'          => 'stalls_purchaser_account_form'
            );
            $fields = D('Setting/UserData')->getDatagridField('Account/StallsPurchaserAccount','stalls_purchaser_account');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("StallsPurchaserAccount/getStallsPurchaserAccountList"),
                    'toolbar' => $id_list["toolbar"],
                    'fitColumns'   => false,
                    'singleSelect'=>true,
                    'ctrlSelect'=>false,
                ),
                'class' => 'easyui-datagrid',
                'style'=>"overflow:scroll",
                'fields' => $fields,
            );

            $arr_tabs = array(
                array(
                    'id' => $id_list['tab_container'],
                    'url' => U('Account/AccountCommon/showTabsView') . '?tab=stalls_purchaser_goods_detail&prefix=StallsPurchaserAccount',
                    'title' => '货品详情'),
            );
            $params  = array();
            $params['datagrid'] = array();
            $params['datagrid']['url'] = U("getStallsPurchaserAccountList/getStallsPurchaserAccountList");
            $params['datagrid']['id'] = $id_list['datagrid'];
            $params['search']['form_id'] = $id_list['form'];
            $params['id_list'] = $id_list;
            $params['tabs'] = array(
                'id' => $id_list['tab_container'],
                'url' => U('AccountCommon/updateTabsData')
            );

            $purchaser = UtilDB::getCfgList(array('employee'));
            $provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
            $provider_default['0'] = array('id' => 'all','name'=>'全部');
            $purchaser_array = array_merge($provider_default, $purchaser['employee']);
            $provider_array = array_merge($provider_default,$provider['data']);
            $current_date=date('Y-m-d H:i:s',time());
            $query_start_date=date('Y-m-d ',time()).' 00:00:00';
            $this->assign('current_date',$current_date);
            $this->assign('query_start_date',$query_start_date);
            $this->assign('provider',$provider_array);
            $this->assign('purchaser', $purchaser_array);
            $this->assign("id_list",$id_list);
            $this->assign('tool_bar',$id_list['tool_bar']);
            $this->assign('datagrid', $datagrid);
            $this->assign("params",json_encode($params));
            $this->assign('arr_tabs', json_encode($arr_tabs));
            $this->display('show');
        }
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
            D('StallsPurchaserAccount')->exportToExcel($search);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }

}