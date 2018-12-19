<?php
namespace Goods\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;

class GoodsMatchController extends BaseController{
    public function getGoodsMatchList($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc'){
        if(IS_POST){
            $this->ajaxReturn(D('GoodsMatch')->getGoodsMatchList($page,$rows,$search,$sort,$order));
        }else{
            $id_list  = array(
                'toolbar'       => 'goods_match_toolbar',
                'id_datagrid'   => strtolower(CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid'),
                'form'          => 'goods_match_search_form',
                'goods_spec'    => 'goods_match_goods_spec',
                'goods_suite'    => 'goods_suite_goods_suite'
            );
            $datagrid = array(
                'id'      => $id_list['id_datagrid'],
                'style'   => '',
                'class'   => '',
                'options' => array(
                    'url'     => U('GoodsMatch/getGoodsMatchList', array('grid' => 'datagrid')),
                    'toolbar' => "#{$id_list['toolbar']}",
                ),
                'fields'  => get_field("GoodsMatch", "goods_match_merchant_no")
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            $params = array(
                'datagrid' => array('id' => $id_list['id_datagrid']),
                'search'=>array('form_id'=>$id_list['form']),
            );
            $shop_list[] = array("id" => "all", "name" => "全部");
            $list        = UtilDB::getCfgRightList(array("shop"));
            $shop_list   = array_merge($shop_list, $list["shop"]);
            $this->assign("list_shop", $shop_list);
            $this->assign('params', json_encode($params));
            $this->assign('id_list', $id_list);
            $this->assign('datagrid', $datagrid);
        }
        $this->display('show');
    }

}