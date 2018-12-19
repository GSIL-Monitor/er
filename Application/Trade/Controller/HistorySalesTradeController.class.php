<?php
namespace Trade\Controller;
use Common\Controller\BaseController;
use Common\Common\Factory;
use Trade\Common\TradeFields;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class HistorySalesTradeController extends BaseController{
    public function getHistorySalesTradeList($page = 1, $rows = 20, $search = array(), $sort = 'st.trade_id', $order = 'desc'){
        if(IS_POST){
            $this->ajaxReturn(D('HistorySalesTrade')->getHistorySalesTradeList($page,$rows,$search,$sort,$order));
        }else{
            $id_list = array(
                'datagrid_id'   =>'history_sales_trade_datagrid',
                'toolbar'       =>'history_sales_trade_toolbar',
                'tab_container' =>'history_sales_trade_tab_container',
                'form'          =>'history_sales_trade_search_form'
            );
            $datagrid = array(
                'id'        => $id_list['datagrid_id'],
                'options'   =>array(
                    'title'         =>'',
                    'toolbar'       =>$id_list['toolbar'],
                    'url'           =>U('HistorySalesTrade/getHistorySalesTradeList'),
                    'style'         =>'',
                    'class'         =>'easyui-datagrid',
                    'pagination'    =>true,
                    'singleSelect'  =>false,
                    'fitColumns'    =>false,
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/Trade','trade_manage',1),
                    'rownumbers'    =>true,
                    'method'        =>'post',
                    'ctrlSelect'    =>true,
                ),
                'fields'  =>D('Setting/UserData')->getDatagridField('Trade/Trade','trade_manage')
            );
            $arr_tabs = array(
                array(
                    'id'    =>$id_list['tab_container'],
                    'title' =>'货品列表',
                    'url'   =>U('HistorySalesTrade/getGoodsListTabs')
                )
            );
            $params = array(
                'controller'    =>strtolower(CONTROLLER_NAME),
                'datagrid'      => array('id' => $id_list['datagrid_id']),
                'tabs'          => array('id' => $id_list['tab_container']),
                'search'        => array(
                    'form_id'      => $id_list['form'],
                )
            );
            $list_form       = UtilDB::getCfgRightList(array("shop"));
            $this->assign("list",$list_form);
            $this->assign("params", json_encode($params));
            $this->assign("id_list", $id_list);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("datagrid", $datagrid);
            $this->display('show');
        }
    }
    public function getGoodsListTabs() {
        if (IS_POST) {
            $id = I("post.id");
            $type = 'history';
            $this->ajaxReturn(D("Trade")->getGoodsList($id,$type));
        } else {
            $id_list  = array(
                "datagrid" => "tabs_sales_trade_history_datagrid_goods",
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U('HistorySalesTrade/getGoodsListTabs'),
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "pagination"   => false,
                    "rownumbers"   => false
                ),
                'fields' => get_field('Trade/TradeCommon', 'goods_list')
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
                D('HistorySalesTrade')->exportToExcel('',$search);
            }
            else{
                D('HistorySalesTrade')->exportToExcel($id_list);
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
