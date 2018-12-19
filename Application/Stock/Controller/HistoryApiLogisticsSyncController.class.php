<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Common\Common;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class HistoryApiLogisticsSyncController extends Basecontroller{

    public function getHistoryApiLogisticsSyncList($page=1, $rows=20, $search = array(), $sort = 'sync_status', $order = 'ASC')
    {
        if (IS_POST) {
            $this->ajaxReturn(D('HistoryApiLogisticsSync')->getHistoryApiLogisticsSyncList($page, $rows, $search, $sort, $order));
        } else {
            $id_list = array();
            $id_list=$this->getIDList($id_list,array('toolbar','id_datagrid','search_form','tab_container','more_button','more_content','hidden_flag'));
            $datagrid=array(
                'id'=>$id_list['id_datagrid'],
                'style'=>'',
                'class'=>'',
                'options'=>array(
                    'title'=>'',
                    'url'=>U('HistoryApiLogisticsSync/getHistoryApiLogisticsSyncList'),
                    'toolbar' => "#{$id_list['toolbar']}",
                    'fitColumns'=>true,
                    'singleSelect'=>true,
                    'ctrlSelect'=>false,
                ),
                'fields'=>get_field('ApiLogisticsSync','apilogisticssync'),
            );

            $arr_tabs = array(
                array('url' => U('StockCommon/showTabsView', array('tabs' => 'api_logistics_sync_detail')) . '?prefix=ApiLogisticsSync&tab=api_logistics_sync_detail', 'id' => $id_list['tab_container'], 'title' => '详细信息')
            );
            $params = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid" => array(
                    'id' => $id_list['id_datagrid'],
                    'url' => U("HistoryApiLogisticsSync/getHistoryApiLogisticsSyncList")
                ),
                "tabs"       => array("id" => $id_list["tab_container"],'url'=>U('HistoryApiLogisticsSync/showTabInfor')),
                'form' =>array('id'=>$id_list['search_form']),
                'search' =>array(
                    'more_button' => $id_list['more_button'],
                    'more_content' => $id_list['more_content'],
                    'hidden_flag' => $id_list['hidden_flag'],
                    'form_id' => $id_list['search_form']
                )
            );

            $list = UtilDB::getCfgRightList(array('shop', 'logistics'));
            $setting = get_config_value('logistics_auto_sync');
            $form_list['list_sync_status'][0] = array('key' => 'all', 'value' => '全部');
            $list_apilogssync_sync_status = C('apilogssync_sync_status');
            $form_list['list_sync_status'] = array_merge($form_list['list_sync_status'], $list_apilogssync_sync_status);
            $form_list['list_shop'][0] = array('id' => 'all', 'name' => '全部');
            $form_list['list_shop'] = array_merge($form_list['list_shop'], $list['shop']);
            $form_list['list_logistics'] = $list['logistics'];
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign('params', json_encode($params));
            $this->assign('list_sync_status', $form_list['list_sync_status']);
            $this->assign('list_shop', $form_list['list_shop']);
            $this->assign('setting', $setting);
            $this->assign('formatter_shop', json_encode($list['shop']));
            $this->assign('formatter_logistics', json_encode($list['logistics']));
            $this->display("show");
        }
    }
    public function showTabInfor($id){
        $this->ajaxReturn(D('HistoryApiLogisticsSync')->showTabInfor($id));
    }
}