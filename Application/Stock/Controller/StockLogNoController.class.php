<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 10:56
 */
namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;

class StockLogNoController extends BaseController
{
    public function getStockLogNo()
    {
        $idList = DatagridExtention::getRichDatagrid('StockLogNo','stocklogno',U("StockLogNo/loadDataByCondition"));
        $params = array(
            'datagrid'=>array(
                'id'    =>    $idList['id_list']['datagrid'],
            ),
            'search'=>array(
                'form_id'    =>    $idList['id_list']['form'],
            ),
        );
        $logistics_list = UtilDB::getCfgRightList(array('logistics'));
        $idList['datagrid']['options']['ctrlSelect'] = true;
		$idList['datagrid']['options']['singleSelect'] = false;
        $idList['datagrid']['options']['fitColumns'] = true;
		$checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($idList['datagrid']['fields'],$checkbox);
		$faq_url=C('faq_url');
        $this->assign('faq_url',$faq_url['print_question']);
        $this->assign('params', json_encode($params));
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->assign('logistics_list', $logistics_list);
        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        $this->ajaxReturn(D('StockLogNo')->searchStockLogNo($page,$rows,$search,$sort,$order));
    }

    /**
     * @param $id  取消的电子面单接口
     */
    public function retrieve()
    {
        try{
            $result_info = array('status'=>0,'info'=>'成功','data'=>array('fail'=>array(),'success'=>array()));
            $ids = I('post.ids','',C('JSON_FILTER'));
            D('Stock/StockLogisticsNo')->retrieve($ids,$result_info);
        }catch(BusinessLogicException $e){
            $result_info['status'] = 1;
            $result_info['info'] = $e->getMessage();
        }
        $this->ajaxReturn($result_info);

    }
}