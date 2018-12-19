<?php

namespace Goods\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;

class GoodsMerchantNoController extends BaseController {
    public function getMerchantNoList($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc') {
        if (IS_POST) {
            $this->ajaxReturn(D("GoodsMerchantNo")->getGoodsMerchantNoList($page, $rows, $search, $sort, $order));
        } else {
            $id_list = array(
            	'form'=>'goods_merchantno_form',
                'toolbar'=> 'goods_merchantno_datagrid_toolar',
                'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid'),
            );
            $datagrid = array(
                'id'      => $id_list['id_datagrid'],
                'style'   => '',
                'class'   => '',
                'options' => array(
                    'url'     => U('GoodsMerchantNo/getMerchantNoList', array('grid' => 'datagrid')),
                    'toolbar' => "#{$id_list['toolbar']}",
                    "pagination"   => true,
                    "singleSelect" => false,
                    "fitColumns"   => true,
                    "rownumbers"   => true,
                    "method"       => "post",
                    "ctrlSelect"   => true
                ),
                'fields'  => get_field("GoodsMerchantNo", "goods_merchant_no")
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            $params = array(
                'datagrid' => array('id' => $id_list['id_datagrid']),
            	'search'=>array('form_id'=>$id_list['form']),
            );
            $this->assign('params', json_encode($params));
            $this->assign('id_list', $id_list);
            $this->assign('datagrid', $datagrid);
        }
        $this->display('show');
    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                D('GoodsMerchantNo')->exportToExcel('', $type);
            }
            else{
                D('GoodsMerchantNo')->exportToExcel($id_list, $type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        exit( $result['info']);

    }
}