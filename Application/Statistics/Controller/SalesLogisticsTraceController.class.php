<?php
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;

class SalesLogisticsTraceController extends BaseController
{
    public function getSalesLogisticsTraceList()
    {
        $id_list = DatagridExtention::getIdList(array('form','tool_bar','datagrid','tab_container'));
        $fields = D('Setting/UserData')->getDatagridField('Statistics/SalesLogisticsTrace','sales_logistics_trace');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("SalesLogisticsTrace/loadDataByCondition"),
                'toolbar' => "#{$id_list['tool_bar']}",
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
        $params['datagrid']['url'] = U("SalesLogisticsTrace/loadDataByCondition");
        $params['datagrid']['id'] = $id_list['datagrid'];
        $params['search']['form_id'] = 'logistics-trace-form';
        $params['id_list'] = $id_list;
        $params['tabs'] = array('id' => $id_list['tab_container'], 'url' => U('StatisticsCommon/updateTabsData'));

        $arr_tabs = array(
            array('id' => $id_list['tab_container'], 'url' => U('Statistics/StatisticsCommon/showTabsView') . '?tab=logistics_trace_detail&prefix=logisticstrace&field=Statistics/SalesLogisticsTrace', 'title' => '物流追踪详情'),
        );
        //店铺物流仓库下拉框
        $list_form=UtilDB::getCfgRightList(array('logistics','shop','warehouse'));
        $this->assign('list',$list_form);
        $this->assign('tool_bar',$id_list['tool_bar']);
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->assign("params",json_encode($params));
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list",$id_list);
        $this->display('show');

    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
        $data = D('SalesLogisticsTrace')->loadDataByCondition($page, $rows, $search , $sort , $order );
        $this->ajaxReturn($data);
    }
}