<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

/**
 * 供应商控制器类
 * @package Setting\Controller
 */
class PurchaseProviderController extends BaseController
{
    /**
     *初始化员工信息
     */
    public function getPurchaseProvider($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        if(IS_POST){
            $this->ajaxReturn(D('PurchaseProvider')->loadDataByCondition($page, $rows, $_POST, $sort, $order));
        }else {
            $idList = DatagridExtention::getRichDatagrid('PurchaseProvider', 'purchase_provider', U("PurchaseProvider/getPurchaseProvider"));
            $idList['datagrid']['options']['singleSelect'] = false;
            $idList['datagrid']['options']['ctrlSelect'] = true;
            $params = array();
            $params['add'] = array();
            $params['add']['id'] = $idList['id_list']['add'];
            $params['add']['title'] = '添加供应商';
            $params['add']['width'] = 500;
            $params['add']['height'] = 260;
            $params['add']['url'] = U('Setting/PurchaseProvider/addPurchaseProvider');
            $params['edit'] = array();
            $params['edit']['id'] = $idList['id_list']['edit'];
            $params['edit']['title'] = '编辑供应商';
            $params['edit']['width'] = 500;
            $params['edit']['height'] = 260;
            $params['edit']['url'] = U('Setting/PurchaseProvider/editPurchaseProvider');
            $params['datagrid'] = array();
            $params['datagrid']['url'] = U('PurchaseProvider/getPurchaseProviderList', array('grid' => 'datagrid'));
            $params['datagrid']['id'] = $idList['id_list']['datagrid'];
            $params['delete']['url'] = U('Setting/PurchaseProvider/delProvider');
            $params['search']['form_id'] = $idList['id_list']['form'];
            $this->assign('id_list', $idList['id_list']);
            $this->assign('datagrid', $idList['datagrid']);
            $this->assign("params", json_encode($params));
            $this->display('show');
        }
    }

    /*
     * 调用model方法获取数据
     * */
    public function loadDataByCondition($id){
        try{
          return  D('PurchaseProvider')->loadDataByCondition($id);
        }catch (\Exception $e){
            $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
            $this->ajaxReturn($result);
        }
    }

    /*
     * 编辑供应商信息
     * */
    public function editPurchaseProvider($id){
        if (IS_POST) {
            $result = array('status' => 0, 'info' => '');
            try {
                $data = I('post.');
                if (!isset($data["is_disabled"])) {
                    $data["is_disabled"] = 0;
                }else{
                    $data["is_disabled"] = 1;
                }
                $arr['provider_name'] = $data['provider_name'];
                $arr['contact'] = $data['contact'];
                $arr['telno'] = $data['telno'];
                $arr['modified'] = date("Y-m-d G:i:s");
                $arr['mobile'] = $data['mobile'];
                $arr['address'] = $data['address'];
                $arr['remark'] = $data['remark'];
                $arr['wangwang'] = $data['wangwang'];
                $arr['type'] = $data['type'];
                $arr['is_disabled'] = $data['is_disabled'];
                $arr['id'] = $data['id'];
				$arr['provider_group_id'] = $data['provider_group_id'];

                $arr['province']  = $data['province'];
                $arr['city']  = $data['city'];
                $arr['district']  = $data['district'];

                D('PurchaseProvider')->editPurchaseProvider($arr);
            }catch (BusinessLogicException $e) {
                $result = array("status" => 1, "info" => $e->getMessage());
            } catch (\Exception $e) {
                $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
            }
            $this->ajaxReturn(json_encode($result), 'EVAL');
        }else{
            $id = intval($id);
            $form = array();
            if(0 != $id){
                $this->loadSelectedData($id, $form);
            }
            self::getIDList($address_id_list,array('province','city','district','dialog','address_object'),CONTROLLER_NAME,'dialog_edit');
            $dialog_list = array(
                'province'=>$address_id_list['province'],
                'city'=>$address_id_list['city'],
                'district'=>$address_id_list['district'],
                'source_js'=>$address_id_list['dialog'],
                'address_object' => $address_id_list['address_object'],
            );
	    $group = D('Setting/ProviderGroup')->field(array('id','provider_group_name as name'))->order('id')->select();
       	$this->assign('group',$group);
            $this->assign('dialog_list',$dialog_list);
            $this->assign('dialog_list_json',json_encode($dialog_list));
            $this->assign('mark', 'edit');
            $this->assign('form', $form);
            $this->assign('provider_info',json_encode($form));
            $this->display('save');
        }
    }

    /*
     * 添加供应商信息
     * */
    public function addPurchaseProvider(){
        if(IS_POST){
            $result=array('status'=>0,'info'=>'');
            try{
                $data = I('post.');
                if (!isset($data["is_disabled"])) {
                    $data["is_disabled"] = 0;
                }else{
                    $data["is_disabled"] = 1;
                }
                $arr['provider_name']    = $data['provider_name'];
                $arr['contact']      = $data['contact'];
                $arr['telno']    = $data['telno'];
                $arr['modified']   = date("Y-m-d G:i:s");
                $arr['mobile']   = $data['mobile'];
                $arr['address']   = $data['address'];
                $arr['remark']       = $data['remark'];
                $arr['wangwang']    = $data['wangwang'];
                $arr['type']        = $data['type'];
                $arr['is_disabled']  = $data['is_disabled'];
				$arr['provider_group_id'] = $data['provider_group_id'];
                $arr['province']  = $data['province'];
                $arr['city']  = $data['city'];
                $arr['district']  = $data['district'];

                D('PurchaseProvider')->addPurchaseProvider($arr);
            }catch (BusinessLogicException $e) {
                $result = array("status" => 1, "info" => $e->getMessage());
            } catch (\Exception $e){
                $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
            }
            $this->ajaxReturn(json_encode($result),'EVAL');
        }else{
            self::getIDList($address_id_list,array('province','city','district','dialog','address_object'),CONTROLLER_NAME,'dialog_add');
            $form = array();
            $dialog_list = array(
                'province'=>$address_id_list['province'],
                'city'=>$address_id_list['city'],
                'district'=>$address_id_list['district'],
                'source_js'=>$address_id_list['dialog'],
                'address_object' => $address_id_list['address_object'],
            );
			$group = D('Setting/ProviderGroup')->field(array('id','provider_group_name as name'))->order('id')->select();
            $this->assign('dialog_list',$dialog_list);
            $this->assign('dialog_list_json',json_encode($dialog_list));
            $this->assign('mark','add');
			$this->assign('group',$group);
            $this->assign('provider_info',json_encode($form));
            $this->display('save');
        }
    }

    /*
     * 删除供应商
     * */
    public function delProvider($id){
        $result=array('status'=>0,'info'=>'');
        try{
           D('PurchaseProvider')->delPurchaseProvider(intval($id[0]));
        }catch (BusinessLogicException $e) {
            $result=array('status'=>1,'info'=>$e->getMessage());
            $this->error($e->getMessage());
        } catch (\Exception $e){
            $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    }

    /*
     * 获取被选中供应商信息
     * */
    public function loadSelectedData($id, &$form){
        try{
            $result = D('PurchaseProvider')->loadSelectedData($id);
        }catch (BusinessLogicException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e){
            $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
            $this->ajaxReturn($result);
        }
        if ("" != $result['data']){
            $form = $result['data'];
        }else{
            $form = array();
        }
        return $form;
    }
	public function getProviderinfo()
	{
		$id_list = array();

		$need_ids = array('form','tool_bar','datagrid','tab_container');
		$get_info = I('get.','',C('JSON_FILTER'));
		$this::getIDList($id_list,$need_ids,$get_info['prefix'],'');
		$datagrid = array(
			'id'=>$id_list['datagrid'],
			'style'=>'',
			'class'=>'',
			'options'=> array(
				'title' => '',
				'url'   =>U("PurchaseProvider/getPurchaseProvider"),
				'toolbar' =>"#{$id_list['tool_bar']}",
				'fitColumns'=>false,
				'singleSelect'=>true,
				'ctrlSelect'=>false,
			),
			'fields'=>get_field('PurchaseProvider', 'purchase_provider'),
		);
		$params=array(
			'search'=>array(
				'form_id'=>$id_list['form'],
			),
			'datagrid'=>array('id'=>$id_list['datagrid']),
		);
		$this->assign("params",json_encode($params));
		$this->assign("id_list",$id_list);
		$this->assign('datagrid', $datagrid);
		$this->display('dialog_purchasr_provider');
	}

}
