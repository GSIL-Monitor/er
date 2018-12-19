<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Common\Common;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class HistoryApiStockSyncController extends BaseController{
    public function getHistoryApiStockSyncList(){
        $id_list  = array(
            "toolbar"  => "stock_sync_history_toolbar",
            "datagrid" => "stock_sync_history_datagrid",
            "form"     => "stock_sync_history_form"
        );
        $datagrid = array(
            "id"      => $id_list["datagrid"],
            "options" => array(
                "url"        => U("HistoryApiStockSync/loadDataByCondition"),
                'queryParams' => array(
                    'search[start_time]'=>date("Y-m-d"),
                    'search[end_time]'=>date("Y-m-d")
                ),
                "toolbar"    => $id_list["toolbar"],
                "fitColumns" => false,
                "method"     => "post"
            ),
            "fields"  => get_field("StockSync", "stocksync")
        );
        $list     = UtilDB::getCfgRightList(array("shop", "warehouse"));
        $params   = array(
            "controller" => "HistoryApiStockSyncController",
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

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'assh_1.rec_id', $order = 'desc') {
        $this->ajaxReturn(D("HistoryApiStockSync")->loadDataByCondition($page, $rows, $search, $sort, $order));
    }
}