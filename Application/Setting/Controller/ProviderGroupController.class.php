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
class ProviderGroupController extends BaseController
{
    /**
     *初始化员工信息
     */
    public function getProviderGroup($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        if(IS_POST){
            $this->ajaxReturn(D('ProviderGroup')->loadDataByCondition($page, $rows, $_POST, $sort, $order));
        }else {
            $id_list = array();
			$needs = array('add','datagrid','tool_bar','edit','form');
			$this->getIDList($id_list,$needs,'','');
			$fields = get_field('PurchaseProvider','provider_group'); 
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'toolbar'=>"#{$id_list['tool_bar']}",
					'fitColumns'=>true,
					'url'=>U('Setting/ProviderGroup/getProviderGroup'),
					'singleSelect'=>false,
					'ctrlSelect'=>true,
					'pagination'=>false
				),
				'fields'=>$fields,
			);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
					'url'=>U('Setting/ProviderGroup/getProviderGroup'),
					
				),
				'search'=>array('form_id'=>$id_list['form']),
				'form'=>$id_list['form'],
				'add'=>array(
					'id'=>$id_list['add'],
					'title'=>'添加供应商分组',
					'url'=>U('Setting/ProviderGroup/add_provider_group'),
					'width'=>500,
					'height'=>240,
				),
				'edit'=>array(
					'id'=>$id_list['edit'],
					'title'=>'编辑供应商分组',
					'url'=>U('Setting/ProviderGroup/edit_provider_group'),
					'width'=>500,
					'height'=>240,
				),
				'id_list'=>$id_list,
				'delete'=>array('url'=>U('Setting/ProviderGroup/delProviderGroup')),
			);
            $this->assign('id_list', $id_list);
            $this->assign('datagrid', $datagrid);
            $this->assign("params", json_encode($params));
            $this->display('show');
        }
    }

    /*
     * 编辑供应商信息
     * */
    public function edit_provider_group($id){
        if (IS_POST) {
            $result = array('status' => 0, 'info' => '');
            try {
                $data = I('post.');
                if (!isset($data["is_disabled"])) {
                    $data["is_disabled"] = 0;
                }else{
                    $data["is_disabled"] = 1;
                }
                $arr['provider_group_name']    = $data['provider_group_name'];
				$arr['provider_group_no']    = $data['provider_group_no'];
				$arr['modified']   = date("Y-m-d G:i:s");
				$arr['address']   = $data['address'];
				$arr['remark']       = $data['remark'];
				$arr['type']        = $data['type'];
				$arr['is_disabled']  = $data['is_disabled'];
				$arr['province']  = $data['province'];
				$arr['city']  = $data['city'];
				$arr['district']  = $data['district'];
				$arr['id'] = $data['id'];
				$is_exist = D('Setting/ProviderGroup')->field('id')->where(array('provider_group_no'=>$arr['provider_group_no'],'id'=>array('neq',$data['id'])))->select();
				if(!empty($is_exist)){
					SE('供应商编号重复，请重新填写!');
				}
                D('Setting/ProviderGroup')->editProviderGroup($arr);
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
			self::getIDList($id_list,array('province','city','district','dialog','is_disabled','form','tool_bar','table'),CONTROLLER_NAME,'dialog_edit');
            $this->assign('id_list',$id_list);
            $this->assign('dialog_list_json',json_encode($id_list));
            $this->assign('provider_info',json_encode($form));
            $this->assign('mark', 'edit');
            $this->assign('form', $form);
            $this->display('add_provider_group');
        }
    }

   
    public function delProviderGroup($id){
        $result=array('status'=>0,'info'=>'');
        try{
           D('Setting/ProviderGroup')->delProviderGroup(intval($id[0]));
        }catch (BusinessLogicException $e) {
            $result=array('status'=>1,'info'=>$e->getMessage());
            $this->error($e->getMessage());
        } catch (\Exception $e){
            $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    }

    
    public function loadSelectedData($id, &$form){
        try{
            $result = D('ProviderGroup')->loadSelectedData($id);
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
	
	public function add_provider_group(){
		$id_list = array();
		$need_ids = array('province','city','district','form','tool_bar','table','is_disabled');
		$this->getIDList($id_list,$need_ids,CONTROLLER_NAME,'add');
		$params = array(
			'id_list'=>$id_list
		);
		$form = array();
		$provider_info = array();
		$this->assign('params',json_encode($params));
		$this->assign('dialog_list_json',json_encode($id_list));
		$this->assign('id_list',$id_list);
		$this->assign('mark','add');
		$this->assign('provider_info',$provider_info);
		$this->assign('form',$form);
		$this->display('add_provider_group');
	}
	 public function addProviderGroup(){       
		$result=array('status'=>0,'info'=>'');
		try{
			$data = I('post.');
			if (!isset($data["is_disabled"])) {
				$data["is_disabled"] = 0;
			}else{
				$data["is_disabled"] = 1;
			}
			$arr['provider_group_name']    = $data['provider_group_name'];
			$arr['provider_group_no']    = $data['provider_group_no'];
			$arr['modified']   = date("Y-m-d G:i:s");
			$arr['address']   = $data['address'];
			$arr['remark']       = $data['remark'];
			$arr['type']        = $data['type'];
			$arr['is_disabled']  = $data['is_disabled'];
			$arr['province']  = $data['province'];
			$arr['city']  = $data['city'];
			$arr['district']  = $data['district'];
			$is_exist = D('Setting/ProviderGroup')->field('id')->where(array('provider_group_no'=>$arr['provider_group_no']))->select();
			if(!empty($is_exist)){
				SE('供应商编号重复，请重新填写!');
			}
			D('Setting/ProviderGroup')->addProviderGroup($arr);
		}catch (BusinessLogicException $e) {
			$result = array("status" => 1, "info" => $e->getMessage());
		} catch (\Exception $e){
			$result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
		}
		$this->ajaxReturn(json_encode($result),'EVAL');
    }

}
