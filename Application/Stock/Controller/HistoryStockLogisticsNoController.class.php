<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;

class HistoryStockLogisticsNoController extends BaseController
{
    public function getHistoryStockLogisticsNoList()
    {
        $idList = DatagridExtention::getRichDatagrid('StockLogNo','stocklogno',U("HistoryStockLogisticsNo/loadDataByCondition"));
        $params = array(
            'datagrid'=>array(
                'id'    =>    $idList['id_list']['datagrid'],
            ),
            'search'=>array(
                'form_id'    =>    $idList['id_list']['form'],
            ),
        );
		$idList['datagrid']['options']['ctrlSelect'] = true;
		$idList['datagrid']['options']['singleSelect'] = false;
        $idList['datagrid']['options']['fitColumns'] = true;
		$checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($idList['datagrid']['fields'],$checkbox);
        $this->assign('params', json_encode($params));
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        $this->ajaxReturn(D('StockLogisticsNoHistory')->searchStockLogNo($page,$rows,$search,$sort,$order));
    }

}