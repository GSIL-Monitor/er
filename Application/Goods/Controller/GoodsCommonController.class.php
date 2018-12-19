<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/24
 * Time: 16:48
 */
namespace Goods\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;

class GoodsCommonController extends BaseController {

    /**
     * 返回复用tabs的页面
     * author:luyanfeng
     * @param        $tab
     * @param string $prefix
     * @param string $field
     * @return null
     */
    public function getTabsView($tab, $prefix = '', $field = 'Goods/GoodsCommon') {
        $arr_tab = array(
            //货品档案tabs
            "spec_list"             => "spec_list",
            "goods_log"             => "goods_log",
            "goods_suite_log"       => "goods_suite_log",
            "goods_suite_detail"    => "goods_suite_detail",
            "platform_goods"        => "platform_goods",
            "system_goods"          => "system_goods",
            "platform_goods_log"    => "platform_goods_log",
            "goods_spec_log"        => "goods_spec_log",
            "spec_platform_goods"   => "spec_platform_goods",
            "api_stock_sync_record" => "stocksync",
            "cfg_stock_sync_rule"   => "cfg_stock_sync_rule",
            "spec_set_out_warehouse"=>"goods_set_out_warehouse",
            "spec_set_out_logistics"=>"goods_set_out_logistics",
            "goods_set_out_warehouse"=>"goods_set_out_warehouse",
            "goods_set_out_logistics"=>"goods_set_out_logistics",
            "suite_set_out_logistics"=>"goods_set_out_logistics",
        );
        if (empty($arr_tab[ $tab ])) {
            \Think\Log::write("GoodsCommon->联动未知的tabs：" . $tab);
            return null;
        }
        $tabs_view = "tabs_goods_common";
        if ($arr_tab[ $tab ] == "stocksync" || $arr_tab[ $tab ] == "cfg_stock_sync_rule") {
            $tabs_view = "tabs_stock_sync_record";
            $list      = UtilDB::getCfgList(array("warehouse"));
            $this->assign("warehouse", json_encode($list["warehouse"]));
        }
        $fields = get_field($field, $arr_tab[ $tab ]);
        if($arr_tab[ $tab ] == 'spec_list')
        {
            propFildConv($fields,'prop','goods_spec');
        }
        $datagrid = array(
            "id"      => $prefix . "datagrid_" . $tab,
            "style"   => "",
            "class"   => "easyui-datagrid",
            "options" => array(
                "title"      => "",
                "pagination" => true,
                "fitColumns" => false
            ),

            "fields"  => $fields
        );
        if (count($datagrid['fields']) < 12) {
            $datagrid['options']['fitColumns'] = true;
        }
        $this->assign("datagrid", $datagrid);
        $this->display($tabs_view);
    }

    /**
     * 返回复用tabs的数据
     * author:luyanfeng
     * @param $id
     * @param $datagridId
     */
    public function updateTabsData($id, $datagridId) {
        if (intval($id) == 0) {
            $data = array("total" => "0", "rows" => array());
            $this->ajaxReturn($data);
        }
        $page = intval(I('post.page'));
        $rows = intval(I('post.rows'));
        $tab = substr($datagridId, strpos($datagridId, "_") + 1);
        switch ($tab) {
            case "spec_list":
                $data = D("GoodsGoods")->getSpecList($id);
                break;
            case "goods_log":
                $data = D("GoodsGoods")->getGoodsLog($id);
                break;
            case "goods_suite_log":
                $data = D("GoodsSuite")->getGoodsSuiteLog($id);
                break;
            case "platform_goods":
                $data = D("GoodsSuite")->getTabsPlatformGoods($id);
                break;
            case "goods_suite_detail":
                $data = D("GoodsSuite")->getGoodsSuiteDetailById($id);
                break;
            case "system_goods":
                $data = D("PlatformGoods")->getTabsSystemGoods($id);
                break;
            case "platform_goods_log":
                $data = D("PlatformGoods")->getTabsMatchLog($id);
                break;
            case "spec_platform_goods":
                $data = D("GoodsSuite")->getTabsPlatformGoods($id, 'goods_spec');
                break;
            case "goods_spec_log":
                $data = D("GoodsGoods")->getGoodsLog($id, 'goods_spec');
                break;
            case "api_stock_sync_record":
                $data = D("Stock/ApiStockSyncRecord")->getStockSyncRecord($id, $page, $rows);
                break;
            case "cfg_stock_sync_rule":
                $data = D("Goods/PlatformGoods")->getCfgStockSyncRule($id);
                break;
            case 'spec_set_out_warehouse':
                $data = D("GoodsGoods")->getGoodsOutInfo($id,'spec','warehouse');
                break;
            case 'goods_set_out_warehouse':
                $data = D("GoodsGoods")->getGoodsOutInfo($id,'goods','warehouse');
                break;
            case 'spec_set_out_logistics':
                $data = D("GoodsGoods")->getGoodsOutInfo($id,'spec','logistics');
                break;
            case 'goods_set_out_logistics':
                $data = D("GoodsGoods")->getGoodsOutInfo($id,'goods','logistics');
                break;
            case 'suite_set_out_logistics':
                $data = D("GoodsGoods")->getGoodsOutInfo($id,'suite','logistics');
                break;
            default:
                $data = array("total" => "0", "rows" => array());
                break;
        }
        $this->ajaxReturn($data);
    }

}