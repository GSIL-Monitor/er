<?php
namespace Stock\Controller;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/29
 * Time: 下午3:00
 */
use Stock\Model\StockModel;
use Common\Controller\BaseController;
use Common\Common\UtilDB;

class StockSyncController extends BaseController {
    /*public function getStocksync()
    {
        $idList = DatagridExtention::getRichDatagrid('StockSync','stocksync',U("StockSync/loadDataByCondition"));

        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("StockSync/loadDataByCondition");
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $params['search']['form_id'] = 'stocksync-form';
        $this->assign('tool_bar', $idList['id_list']['tool_bar']);
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->assign("params",json_encode($params));
        $list = UtilDB::getCfgList(array('shop','warehouse'));
        $shop_default['0'] = array('id' => 'all', 'name' => '全部');
        $shop_array = array_merge($shop_default, $list['shop']);
        $this->assign('shop_array', $shop_array);
        $this->assign('shop', json_encode($list['shop']));
        $this->assign('warehouse', json_encode($list['warehouse']));
        $this->display('show');
    }*/

    public function getStockSync() {
        $id_list  = array(
            "toolbar"  => "stock_sync_record_toolbar",
            "datagrid" => "stock_sync_record_datagrid",
            "form"     => "stock_sync_record_form"
        );
        $datagrid = array(
            "id"      => $id_list["datagrid"],
            "options" => array(
                "url"        => U("StockSync/loadDataByCondition"),
                'queryParams' => array(
                    'search[start_time]'=>date("Y-m-d"),
                    'search[end_time]'=>date("Y-m-d")
                ),
                "toolbar"    => $id_list["toolbar"],
                "fitColumns" => false,
                "methid"     => "post"
            ),
            "fields"  => get_field("StockSync", "stocksync")
        );
        $list     = UtilDB::getCfgRightList(array("shop", "warehouse"));
        $params   = array(
            "controller" => "StockSyncController",
            "datagrid"   => array("id" => $id_list["datagrid"]),
            "search"     => array("form_id" => $id_list["form"])
        );
        $this->assign("shop", $list["shop"]);
        $this->assign("warehouse", json_encode($list["warehouse"]));
        $this->assign("params", json_encode($params));
        $this->assign("id_list", $id_list);
        $this->assign("datagrid", $datagrid);
        $this->display("show");
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'ass_1.rec_id', $order = 'desc') {
        $this->ajaxReturn(D("ApiStockSyncRecord")->loadDataByCondition($page, $rows, $search, $sort, $order));
    }
}