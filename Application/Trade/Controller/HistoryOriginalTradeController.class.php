<?php
/**
 * 历史原始订单
 */
namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Trade\Common\TradeFields;
use Common\Common\UtilTool;
use Think\Log;

class HistoryOriginalTradeController extends BaseController {

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * 返回原始订单列表
     * author:luyanfeng
     * table:api_trade
     */
    public function getHistoryOriginalTradeList($page = 1, $rows = 20, $search = array(), $sort = 'ate.rec_id', $order =
    'desc') {
        if (IS_POST) {
            $this->ajaxReturn(D("HistoryOriginalTrade")->getHistoryOriginalTradeList($page, $rows, $search, $sort, $order));
        } else {
            $id_list     = array(
                "datagrid"          => "history_original_trade_list_datagrid",
                "toolbar"           => "history_original_trade_list_toolbar",
                "tab_container"     => "history_original_trade_tab_container",
                "form"              => "history_original_trade_search_form",
                "response"          => "history_original_trade_submit_response",
                "response_datagrid" => "history_original_trade_submit_response_datagrid",
                "more_button"       => "history_original_trade_more_button",
                "more_content"      => "history_origianl_trade_more_content",
                "hidden_flag"       => "history_original_trade_hidden_flag",
                "fileForm"          => "history_original_trade_file_form",
                "fileDialog"        => "history_original_trade_file_dialog"
            );
            $datagrid    = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "title"        => "",
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("HistoryOriginalTrade/getHistoryOriginalTradeList"),
                    "style"        => "",
                    "class"        => "easyui-datagrid",
                    "pagination"   => true,
                    "singleSelect" => false,
                    "fitColumns"   => false,
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/Trade','originalorder',1),
                    "rownumbers"   => true,
                    "ctrlSelect"   => true,
                    "method"       => "post"
                ),
                "fields"  =>D('Setting/UserData')->getDatagridField('Trade/Trade','originalorder')
            );
            $arr_tabs    = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "货品列表",
                    "url"   => U("HistoryOriginalTrade/getGoodsListTabs")
                )
            );
            $params      = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"],),
                "tabs"       => array("id" => $id_list["tab_container"]),
                "search"     => array(
                    "form_id"      => $id_list["form"],
                    "hidden_flag"  => $id_list["hidden_flag"],
                    "more_button"  => $id_list["more_button"],
                    "more_content" => $id_list["more_content"]
                )
            );
            $shop_list[] = array("id" => "all", "name" => "全部");
            $list        = UtilDB::getCfgRightList(array("shop"));
            $shop_list   = array_merge($shop_list, $list["shop"]);
            $process_status = array(
                    array('id' => 'all' , 'name' => '全部'),
                    array('id' => '40' ,  'name' => '已发货'),
                    array('id' => '60' , 'name' => '已完成'),
                    array('id' => '70' , 'name' => '已取消')
                );
            $this->assign("shop_list", json_encode($shop_list));
            $this->assign("process_status",  json_encode($process_status));
            $this->assign("params", json_encode($params));
            $this->assign("id_list", $id_list);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("datagrid", $datagrid);
            $this->display('show');
        }
    }

    /**
     * tabs:货品列表
     * author:luyanfeng
     * table:api_trade_order
     */
    public function getGoodsListTabs() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(D("HistoryOriginalTrade")->getGoodsList($id));
        } else {
            $id_list  = array(
                "datagrid" => "tabs_trade_order_datagrid_history",
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U('HistoryOriginalTrade/getGoodsListTabs'),
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "pagination"   => false,
                    "rownumbers"   => false
                ),
                "fields"  => TradeFields::getTradeFields("GoodsListTabs")
            );
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->display("tabs_trade_order");
        }
    }
    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                    $key=substr($k,7,strlen($k)-8);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                D('HistoryOriginalTrade')->exportToExcel('',$search);
            }
            else{
                D('HistoryOriginalTrade')->exportToExcel($id_list);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }

}