<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;

class HistoryStockSpecLogController extends BaseController{

    public function getHistoryStockSpecLog(){
        $id_list = self::getIDList($id_list,array('tool_bar', 'tab_container', 'datagrid','more_button', 'more_content','hidden_flag', 'dialog','form'));
        $fields = get_field('Stock/HistoryStockSpecLog','historystockspeclog');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("HistoryStockSpecLog/search"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>false,
                'ctrlSelect'=>true,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );

        $params=array(
            'datagrid'=>array('id'=>$id_list['datagrid'],'url' => U("HistorySaleStockout/search")),
            'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
        );
        $list_form = UtilDB::getCfgRightList(array('employee','warehouse'));

        $this->assign("list",$list_form);
        $this->assign("params",json_encode($params));
        $this->assign("id_list",$id_list);
        $this->assign("datagrid",$datagrid);
        $this->display("show");
    }

    public function search($page = 1, $rows = 20, $search = array(), $sort = 'created', $order = 'desc'){
        $this->ajaxReturn(D('StockSpecLog')->search($page, $rows, $search, $sort, $order,'history'));
    }
}