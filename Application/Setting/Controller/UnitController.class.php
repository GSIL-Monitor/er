<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 09:24
 */

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;

class UnitController extends BaseController {
    public function getUnit() {
        $idList = DatagridExtention::getRichDatagrid('Unit', 'unit', U("Unit/loadDataByCondition"));
        $params = array(
            'add'      => array(
                'id'     => $idList['id_list']['add'],
                'title'  => '添加单位',
                'url'    => U('Setting/Unit/addUnit'),
                'width'  => '250',
                'height' => '200',
                'ismax'	 =>  false
            ),
            'edit'     => array(
                'id'     => $idList['id_list']['edit'],
                'title'  => '编辑单位',
                'url'    => U('Setting/Unit/addUnit'),
                'width'  => '250',
                'height' => '200',
                'ismax'	 =>  false
            ),
            'datagrid' => array(
                'id' => $idList['id_list']['datagrid'],
            ),
            'search'   => array(
                'form_id' => $idList['id_list']['form'],
            ),
        );

        $idList['datagrid']['options']['fitColumns'] = true;

        $this->assign('params', json_encode($params));
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->display('show');

    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        $this->ajaxReturn(D('Unit')->searchUnit($page, $rows, $search, $sort, $order));
    }

    public function addUnit($id = 0) {
        $id   = (int)$id;
        $form = "'none'";
        if (0 != $id) {
            $this->loadSelectedData($id, $form);
        }
        0 == $id ? $mark = 'add' : $mark = 'edit';
        $this->assign('mark', $mark);
        $this->assign('form', json_encode($form));
        $this->display('unit_edit');
    }

    public function loadSelectedData($id, &$form) {
        $re = D('Unit')->loadSelectedData($id);
        if ("" != $re['data']) {
            $form = $re['data'];
        } else {
            $form = "'err'";
        }
        return $form;
    }

    public function saveUnit() {
    	$result=array('status'=>0,'info'=>'');
    	try {
    		$tmp = I('post.');
    		$arr['rec_id']      = $tmp['id'];
    		$arr['name']        = $tmp['name'];
    		$arr['is_disabled'] = $tmp['is_disabled'];
    		$arr['remark']      = $tmp['remark'];
    		$arr['type']        = $tmp['type'];
    		$res = D("Unit")->saveUnit($arr);
    	}catch (\Exception $e)
    	{
    		$msg=$e->getMessage();
    		\Think\Log::write($msg);
    		$result['status']=1;//失败
    		$result['info']=$msg;
    	}
        $this->ajaxReturn(json_encode($result),'EVAL');
    }

}
