<?php
/**
 * Created by PhpStorm.
 * User: asher
 * Date: 16/10/25
 * Time: 下午5:50
 */
namespace Stock\Controller;

use Common\Controller\BaseController;
use Think\Model;
use Common\Common;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;

class HistorySaleStockoutController extends BaseController{

    public function getHistorySaleStockout(){
        $id_list = DatagridExtention::getIdList(array('tool_bar', 'tab_container', 'datagrid', 'tab_stockout_order_detail',
            'tab_sales_trade_detail', 'tab_log', 'tab_note', 'more_button', 'more_content',
            'hidden_flag', 'set_flag','search_flag', 'dialog','form'));
        $fields = D('Setting/UserData')->getDatagridField('Stock/HistorySaleStockout','history_sale_stockout');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("HistorySaleStockout/loadDataByCondition"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>false,
                'ctrlSelect'=>true,
                //'idField'=>'id',
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        try{
            $flag=D('Setting/Flag')->getFlagData(5);
            $search_condition = UtilDB::getCfgRightList(array('shop','logistics','warehouse'));
            $chg_logistics_list = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>0)));
            $search_condition['chg_logistics_list'] = $chg_logistics_list['logistics'];
            $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $search_condition['warehouse']);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
        }

        $params=array(
            'datagrid'=>array('id'=>$id_list['datagrid'],'url' => U("HistorySaleStockout/search")),
            'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
            'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('HistorySaleStockout/showTabDatagridData')),
            'flag'=>array('set_flag'=>$id_list['set_flag'],'url'=>U('Setting/Flag/flag').'?flagClass=5','json_flag'=>$flag['json'],'list_flag'=>$flag['list'],'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=5','title'=>'颜色标记'),'search_flag'=>$id_list['search_flag']),
        );

        $tab_list = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order_detail")).'?prefix=historySalesStockout&tab=stockout_order_detail','id' =>$id_list['tab_container'],'title' => '出库单详情'),
        );
        $arr_tabs = json_encode($tab_list);

        $this->assign('list',$search_condition);
        $this->assign('warehouse_array', $warehouse_array);
        $this->assign("params",json_encode($params));
        $this->assign('center_container',$id_list['tab_container']);
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('tool_bar',$id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list",$id_list);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'stockout_id', $order = 'desc'){
        $this->ajaxReturn(D('HistorySaleStockout')->getHistorySaleStockout($page, $rows, $search, $sort, $order));
    }

    public function showTabDatagridData($id){
        $this->ajaxReturn(D('HistorySaleStockout')->getStockoutOrderList($id));
    }
}