<?php
namespace Setting\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
class CfgOperReasonController extends BaseController
{
	public function getReasonList($class_id,$model_type)
	{
		$dict_class = array(
				'1'=>"订单驳回",
		        '2'=>'冻结原因',
		        '3'=>'取消原因',
				
		);
		$pre_fix = "CfgOperReaList";
		$id_list = array(
				"form_id" => "cfg_oper_reason_form",
				"reason_list" => "cfgoperreason_list_combobox",
				"add" => "reason_show_dialog",
				'class_id' =>$class_id
		);
		switch ((string)$class_id)
		{
			case "1":  //订单驳回
			{
				$id_list['keep_logisticsno_status'] = $pre_fix."_keep_lno_status";
				$id_list['keep_stockoutno_status'] = $pre_fix."_keep_sno_status";
				break;		
			}
		}
		$params = array(
			"dialog" =>array(
					"url"=>U("CfgOperReason/showReasonList")."?class_id={$class_id}&is_dialog=true",
					"id"=>$id_list['add'],
					"title"=>$dict_class["{$class_id}"],
					'cfgOperReasonRight'=>'dialogcfgOperReasonRight',
					'cfgOperReasonLeft'=>'dialogcfgOperReasonLeft',
			),
			"form"=>array(
					"id" => $id_list['form_id'],
			),
			"id_list" =>$id_list,	
		);
		$model = D('Setting/CfgOperReason');
		$reason = $model->getReasonData($class_id);
		//$this->assing("class_id",$class_id);
		$this->assign("data",json_encode($reason['list']));
		$this->assign("params",json_encode($params));
		$this->assign("id_list",$id_list);
		$this->assign("model_type",$model_type);
		$this->display("dialog_set_reason");
	}
	public function showReasonList($class_id="1",$is_dialog=false)
	{
		$is_dialog=is_bool($is_dialog)?$is_dialog:($is_dialog == "true"?true:false);
		$is_disabled = I('get.is_disabled'); //获取停用按钮value
		if(IS_POST)
		{
			try{
			    $model = D('Setting/CfgOperReason');
				$res_reason_arr = $model->getReasonData(intval($class_id),true, $is_disabled);
				$this->ajaxReturn(array("total"=>count($res_reason_arr['data']),"rows"=>$res_reason_arr['data']));
			}catch(\PDOException $e){
				$msg = $e->getMessage();
				$this->error($msg);
			}catch(BusinessLogicException $e){
				$this->error($e->getMessage());
			}catch(\Exception $e){
				$msg = $e->getMessage();
				$this->error($msg);
			}
		}else {
			
			$pre_dialog = $is_dialog?"dialog":""; 
			$pre_fix = $pre_dialog."CfgOperReaDetail";
			$id_list = array(
					"tool_bar" => $pre_fix."_datagrid_toolbar",
					"id_datagrid" => $pre_fix."_datagrid",
					"datalist_class" => $pre_fix."_datalist_class",
			);
			$data_list = array(
					array("class_id"=>1,"name"=>"订单驳回","group"=>"类别"),
					array("class_id"=>2,"name"=>"订单冻结","group"=>"类别"),
					array("class_id"=>3,"name"=>"订单取消","group"=>"类别"),
					array("class_id"=>4,"name"=>"退换原因","group"=>"类别"),
			);
			$field_grid = array(
					"id" =>array('field'=>'id','hidden'=>true,),
					"原因" => array('field'=>'title','width'=>'88%','editor' => '{type:"textbox",options:{required:true,validType:"reason_title_unique"}}'),
					"停用"=> array('field'=>'is_disabled','width'=>'7%','align'=>'center','editor'=>'{type:"checkbox",options:{on:"1",off:"0"}}','formatter'=>'formatter.boolen'),
					"优先级"=>array('field'=>'priority','hidden'=>true),
			);
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'options'=> array(
							'title' => '',
							'url'   => U("CfgOperReason/showReasonList")."?class_id={$class_id}",
							'toolbar' => "#{$id_list['tool_bar']}",
							'fitColumns'   => true,
							'singleSelect'=>true,
							'ctrlSelect'=>false,
							'pagination'=>false
					),
					'fields' => $field_grid,
					'class' => 'easyui-datagrid',
					'style'=>"overflow:scroll",
			);
			$params = array();
			$params['datagrid'] = array(
				"url" => U('CfgOperReason/setReason'),
				"id" => $id_list['id_datagrid'],
			    'refresh_url' =>U("CfgOperReason/showReasonList"),
			);
			$params['datalist'] = array(
// 					"url" => U('CfgOperReason/selectReason'),
					"id" => $id_list['datalist_class'],
			        'refresh_url' =>U("CfgOperReason/showReasonList"),
			);
			$params['is_dialog'] = $is_dialog;
			$params['class_id'] = $class_id;
			$this->assign("isDialog",$is_dialog);
			$this->assign('data_list',json_encode($data_list));
			$this->assign('datagrid', $datagrid);
			$this->assign("id_list",$id_list);
			$this->assign("dialog",$pre_dialog);
			$this->assign("params",json_encode($params));
			$this->display('cfg_oper_reason_edit');
		}
	}
	public function setReason($class_id)
	{
			try {
				$class_id=intval($class_id);
				$arr_data_add=I('post.add');
				$arr_data_update=I('post.update');
				$len_add=empty($arr_data_add)?0:count($arr_data_add);
				$len_update=empty($arr_data_update)?0:count($arr_data_update);
				if($len_add==0&&$len_update==0)
				{
					$this->success('null');
				}
				$reason_db=D('Setting/CfgOperReason');
				$reason_db->startTrans();
				for ($i=0;$i<$len_update;$i++)
				{
					if(intval($arr_data_update[$i]['id'])==0)
					{//过滤非法更新
						$this->error('非法更新！保存失败！');
					}else 
					{
						$where['reason_id']=array('eq',intval($arr_data_update[$i]['id']));
						$arr_data_update[$i]['font_name']=$arr_data_update[$i]['font_id'];
						$arr_data_update[$i]['class_id']=$class_id;
						unset($arr_data_update[$i]['id']);
						unset($arr_data_update[$i]['priority']);
						$res_update = $reason_db->updateReason($arr_data_update[$i],$where);
					}
				}
				$ids=array();
				for ($i=0;$i<$len_add;$i++)
				{
					$add_id=$arr_data_add[$i]['id'];
					unset($arr_data_add[$i]['id']);
					$arr_data_add[$i]['class_id'] = $class_id;
// 					unset($arr_data_update[$i]['priority']);
					$res_id = $reason_db->addReason($arr_data_add[$i]);
					$ids[$add_id]=$res_id;
				}
				$reason_db->commit();
			} catch (\PDOException $e) {
				\Think\Log::write($e->getMessage());
				$reason_db->rollback();
				$this->error(self::PDO_ERROR);
			}catch(BusinessLogicException $e){
				\Think\Log::write($e->getMessage());
				$reason_db->rollback();
				$this->error($e->getMessage());
			}catch (\Exception $e)
			{
			    $msg = $e->getMessage();
			    \Think\Log::write("cfgoperreason-setReason-".$msg);
			    $reason_db->rollback();
			    $this->error($msg);
			}
		
			$res_reason_arr=$reason_db->getReasonData(intval($class_id),false);
			$this->success($res_reason_arr['list']);
	}

}