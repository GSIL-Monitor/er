<?php
/**
 * Created by PhpStorm.
 * User: asher
 * Date: 16/10/25
 * Time: 下午5:50
 */
namespace Stock\Controller;

use Common\Controller\BaseController;
//use Setting\Common\SettingFields;
use Think\Model;
//use Stock\Common\StockFields;
use Common\Common;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;

class HistoryOriginalStockoutController extends BaseController
{

    public function getHistoryOriginalStockout()
    {
        $idList = Common\DatagridExtention::getRichDatagrid('HistoryOriginalStockout','history_original_stockout',U("HistoryOriginalStockout/loadDataByCondition"));
        $arr_tabs = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order_detail_history")).'?prefix=originalstockout&tab=stockmanagementdetail&app=StockOut',"id"=>$idList['id_list']['tab_container'],"title"=>"出库单详情"),
        );
        $arr_tabs = json_encode($arr_tabs);
        $params['datagrid'] = array();
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $params['form']['id'] = 'history_original_stockout_search_form';
        $params['search']['form_id'] = 'history_original_stockout_search_form';

        $params['tabs']['id'] = $idList['id_list']['tab_container'];
        $params['tabs']['url'] = U('HistoryOriginalStockout/showTabDatagridData');
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("HistoryOriginalStockout/loadDataByCondition");
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $idList['datagrid']['fields'] =  D('Setting/UserData')->getDatagridField('Stock/HistoryOriginalStockout','history_original_stockout');

        $this->assign("params", json_encode($params));
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('tool_bar', $idList['id_list']['tool_bar']);
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->assign("datagrid_id", $idList['id_list']['datagrid']);

        $list = UtilDB::getCfgRightList(array('logistics','employee','warehouse'));
        $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
        $warehouse_array = array_merge($warehouse_default, $list['warehouse']);
        $logistics_default['0'] = array('id' => 'all', 'name' => '全部');
        $logistics_array = array_merge($logistics_default, $list['logistics']);
        $employee_default['0'] = array('id' => 'all', 'name' => '全部');
        $employee_array = array_merge($employee_default, $list['employee']);
        $this->assign('warehouse_array', $warehouse_array);
        $this->assign('logistics_array',$logistics_array);
        $this->assign('employee_array', $employee_array);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'stockout_id', $order = 'desc'){
        $this->ajaxReturn(D('HistoryOriginalStockout')->getHistoryOriginalStockout($page, $rows, $search, $sort, $order));
    }

    public function showTabDatagridData($id){
        $this->ajaxReturn(D('HistoryOriginalStockout')->getStockoutOrderList($id));
    }
}