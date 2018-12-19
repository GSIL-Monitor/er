<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;
use Think\Exception;

class RemarkExtractController extends BaseController
{
	public function showRemarkExtract()
	{
		try{
			$id_list=array(
					'tab_container'=>'remark_extract_tab_container',
			);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('RemarkExtract/showTabs',array('tabs'=>'cs_remark')).'?tab=cs_remark','title'=>'客服备注'),
					array('id'=>$id_list['tab_container'],'url'=>U('RemarkExtract/showTabs',array('tabs'=>'client_remark')).'?tab=client_remark','title'=>'客户备注'),
			);
			$this->assign('tab_type',I('get.tab'));
			$this->assign('id_list',$id_list);
			$this->assign('arr_tabs',json_encode($arr_tabs));
		}catch(\Exception $e){
			$this->assign('message',$e->getMessage());
			$this->display('Common@Exception:dialog');
			exit();
		}
		$this->display('dialog_remark_extract');
	}
	
	function showTabs($tab){
		switch ($tab){
			case 'cs_remark':
				$this->getCsRemarkList();
				break;
			case 'client_remark':
				$this->getClientRemarkList();
				break;
			default:
				$this->assign('message','Not Found Tabs');
				$this->display('Common@Exception:dialog');
				exit();
				break;
		}
	}
	
	public function getCsRemarkList($page=1, $rows=20,$sort = 'rec_id', $order = 'desc'){
		if(IS_POST){
			$where = 'WHERE re_t.class=1';
			$data=D('RemarkExtract')->queryRemarkExtract($where,$page,$rows,$sort,$order);
			$this->ajaxReturn($data);
		}else{
			$cs_id_list=array();
			$cs_id_list=$this->getIDList($cs_id_list,array('toolbar','id_datagrid','form','edit','add'),'cs');
			$cs_datagrid=array(
					'id'=>$cs_id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=>array(
							'title'=>'',
							'url'=>U('RemarkExtract/getCsRemarkList',array('grid'=>'datagrid')),
							'toolbar' => "#{$cs_id_list['toolbar']}",
							'singleselete'=>true,
							'fitClumns'=>true,
					),
					'fields'=>get_field('RemarkExtract','remark'),
			);
			$cs_params=array(
					'datagrid'=>array('id'=>$cs_id_list['id_datagrid']),
					'search'=>array('form_id'=>$cs_id_list['form']),
					'edit'=>array('id'=>$cs_id_list['edit'],'url'=>U('RemarkExtract/editCsRemarkExtract'),'title'=>'编辑客服备注提取','height'=>180,'width'=>430),
					'add'=>array('id'=>$cs_id_list['add'],'url'=>U('RemarkExtract/addCsRemarkExtract'),'title'=>'新建客服备注提取','height'=>180,'width'=>430),
					'delete'=>array('url'=>U('RemarkExtract/deleteCsRemarkExtract'))
			);
			$salesman_macro=get_config_value(array('salesman_macro_begin','salesman_macro_end'),array('',''));
			$faq_url=C('faq_url');
			$this->assign('faq_url',$faq_url['remark_extract']);
			$this->assign('salesman_macro',$salesman_macro);
			$this->assign('cs_params',json_encode($cs_params));
			$this->assign('cs_id_list',$cs_id_list);
			$this->assign('datagrid',$cs_datagrid);
			$this->display('dialog_cs_remark');
		}
	}
	
	public function getClientRemarkList($page=1, $rows=20,$sort = 'rec_id', $order = 'desc'){
		if(IS_POST){
			$where = 'where re_t.class=2';
			$data=D('RemarkExtract')->queryRemarkExtract($where,$page,$rows,$sort,$order);
			$this->ajaxReturn($data);
		}else{
			$client_id_list=array();
			$client_id_list=$this->getIDList($client_id_list,array('toolbar','id_datagrid','form','edit','add'),'client');
			$client_datagrid=array(
					'id'=>$client_id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=>array(
							'title'=>'',
							'url'=>U('RemarkExtract/getClientRemarkList',array('grid'=>'datagrid')),
							'toolbar'=>$client_id_list['toolbar'],
							'singleselete'=>true,
							'fitClumns'=>true,
					),
					'fields'=>get_field('RemarkExtract','remark'),
			);
			$client_params=array(
					'datagrid'=>array('id'=>$client_id_list['id_datagrid']),
					'edit'=>array('id'=>$client_id_list['edit'],'url'=>U('RemarkExtract/editClientRemarkExtract'),'title'=>'编辑客户备注提取','height'=>180,'width'=>430),
					'add'=>array('id'=>$client_id_list['add'],'url'=>U('RemarkExtract/addClientRemarkExtract'),'title'=>'新建客户备注提取','height'=>180,'width'=>430),
					'delete'=>array('url'=>U('RemarkExtract/deleteClientRemarkExtract'))
			);
			$this->assign('client_params',json_encode($client_params));
			$this->assign('client_id_list',$client_id_list);
			$this->assign('datagrid',$client_datagrid);
			$this->display('dialog_client_remark');
		}
	}
	
	public function editCsRemarkExtract($id){
		$id=intval($id);
		if(IS_POST){
			$cs_remark=I('post.info','',C('JSON_FILTER'));
			$cs_remark['rec_id']=$id;
			$this->saveRemarkExtract($cs_remark);
		}else{
			$cs_remark=D('RemarkExtract')->getRemarkExtract(
					'rec_id AS id, keyword, class, type, target, remark, is_disabled, modified, created ',
					array('rec_id'=>array('eq',$id))
					);
			$this->showCsRemark($cs_remark);
		}
	}
	
	public function addCsRemarkExtract(){
		if(IS_POST){
			$cs_remark=I('post.info','',C('JSON_FILTER'));
			$this->saveRemarkExtract($cs_remark);
		}else{
			$this->showCsRemark();
		}
	}
	
	public function showCsRemark($cs_remark){
		try{
			$id_list=array(
					'form_id'=>empty($cs_remark)?'cs_remark_add_dialog':'cs_remark_edit_dialog',
					'select_id'=>empty($cs_remark)?'cs_remark_add_select':'cs_remark_edit_select',
					'flag_id'=>empty($cs_remark)?'cs_remark_add_flag':'cs_remark_edit_flag',
					'set_flag'=>'cs_remark_set_flag',
			);
			$list_form=UtilDB::getCfgList(
					array('logistics','warehouse','employee','reason'),
					array(
							'logistics'=>array('is_disabled'=>array('eq',0)),
							'warehouse'=>array('is_disabled'=>array('eq',0)),
							'employee'=>array('is_disabled'=>array('eq',0),'delete'=>array('eq',0)),
							'reason'=>array('is_disabled'=>array('eq',0),'class_id'=>array('eq',2)),
			));
 			$arr_flag=Factory::getModel('Setting/Flag')->getFlagData(1);
 			unset($arr_flag['list'][0]['selected']);
 			$arr_flag['list'][0]['name']='';
			$params=array(
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			if(empty($cs_remark)){
				$cs_remark['type']=0;
				$cs_remark['target']=0;
			}
			$this->assign('params',json_encode($params));
			$this->assign('list_form',json_encode($list_form));
			$this->assign('id_list',$id_list);
			$this->assign('cs_remark',$cs_remark);
			$this->assign('id',$cs_remark['id']);
		}catch(\Exception $e){
			$this->assign("message",$e->getMessage());
			$this->display("Common@Exception:dialog");
			exit();
		}
		$this->display('dialog_cs_add_edit');
	}
	
	public function editClientRemarkExtract($id){
		$id=intval($id);
		if(IS_POST){
			$client_remark=I('post.info','',C('JSON_FILTER'));
			$client_remark['rec_id']=$id;
			$this->saveRemarkExtract($client_remark);
		}else{
			$client_remark=D('RemarkExtract')->getRemarkExtract(
					'rec_id AS id, keyword, class, type, target, remark, is_disabled, modified, created ',
					array('rec_id'=>array('eq',$id))
					);
			$this->showClientRemark($client_remark);
		}
	}
	
	public function addClientRemarkExtract(){
		if(IS_POST){
			$client_remark=I('post.info','',C('JSON_FILTER'));
			$this->saveRemarkExtract($client_remark);
		}else{
			$this->showClientRemark();
		}
	}
	
	public function showClientRemark($client_remark){
		try{
			$id_list=array(
					'form_id'=>empty($client_remark)?'client_remark_add_dialog':'client_remark_edit_dialog',
					'select_id'=>empty($client_remark)?'client_remark_add_select':'client_remark_edit_select',
					'flag_id'=>empty($client_remark)?'client_remark_add_flag':'client_remark_edit_flag',
					'set_flag'=>'client_remark_set_flag',
			);
			$list_form=UtilDB::getCfgList(
					array('logistics','warehouse','reason'),
					array(
							'logistics'=>array('is_disabled'=>array('eq',0)),
							'warehouse'=>array('is_disabled'=>array('eq',0)),
							'reason'=>array('is_disabled'=>array('eq',0),'class_id'=>array('eq',2)),
					));
			$arr_flag=Factory::getModel('Setting/Flag')->getFlagData(1);
			unset($arr_flag['list'][0]['selected']);
 			$arr_flag['list'][0]['name']='';
			$params=array(
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			if(empty($client_remark)){
				$client_remark['type']=0;
				$client_remark['target']=0;
			}
			$this->assign('params',json_encode($params));
			$this->assign('list_form',json_encode($list_form));
			$this->assign('id_list',$id_list);
			$this->assign('client_remark',$client_remark);
			$this->assign('id',$client_remark['id']);
		}catch(\Exception $e){
			$this->assign("message",$e->getMessage());
			$this->display("Common@Exception:dialog");
			exit();
		}
		$this->display('dialog_client_add_edit');
	}
	
	//$class=1:客服备注提取，$class=2:客户备注提取。
	private function saveRemarkExtract($remark_extract){
		$user_id=get_operator_id();
		$remark_extract_db=D('RemarkExtract');
		try{
			$remark_extract['target_name']=$remark_extract['type']==5?'':$remark_extract['target_name'];
			$extract_message=array('修改物流方式为：','修改订单标记为：','修改业务员为：','修改仓库为：','转预订单','冻结订单：原因');
			$remark_extract['remark']=$extract_message[$remark_extract['type']-1].$remark_extract['target_name'];
			$remark_extract['is_disabled']=set_default_value($remark_extract['is_disabled'], 0);
			$remark_extract['modified']=date('Y-m-d H:i:s',time());
			$remark_extract_db->validateRemarkExtract($remark_extract);
			if (isset($remark_extract['rec_id'])){
				$remark_extract_db->editRemark($remark_extract,$user_id);
			}else{
				$remark_extract_db->addRemark($remark_extract,$user_id);
			}
		}catch(BusinessLogicException $e){
			$this->error($e->getMessage());
		}
		$this->success();
	}
	
	public function deleteCsRemarkExtract(){
		$id=intval(I('post.id'));
		try{
			D('RemarkExtract')->deleteRemark($id,get_operator_id(),1);
		}catch(BusinessLogicException $e){
			$this->error($e->getMessage());
		}
		$this->success();
	}
	public function deleteClientRemarkExtract(){
		$id=intval(I('post.id'));
		try{
			D('RemarkExtract')->deleteRemark($id,get_operator_id(),2);
		}catch(BusinessLogicException $e){
			$this->error($e->getMessage());
		}
		$this->success();
	}
	
	public function saveSalesmanMacro(){
		$salesman_macro=I('post.info','',C('JSON_FILTER'));
		$salesman_macro_begin=array(
				'key'=>'salesman_macro_begin',
				'value'=>$salesman_macro['macro_begin'],
		);
		$salesman_macro_end=array(
				'key'=>'salesman_macro_end',
				'value'=>$salesman_macro['macro_end'],
		);
		try{
			D('System')->updateSystemSetting($salesman_macro_begin);
			D('System')->updateSystemSetting($salesman_macro_end);
		}catch(BusinessLogicException $e){
			$this->error($e->getMessage());
		}
		$this->success();
	}
}