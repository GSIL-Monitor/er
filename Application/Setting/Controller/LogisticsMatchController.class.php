<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilTool;

class LogisticsMatchController extends BaseController
{
	public function getLogisticsMatchList ($page=1, $rows=20, $search=array(), $sort='path', $order='asc')
	{
		if(IS_POST)
		{
			$path='%#,'.($search['province']==0?'':$search['province']).($search['city']==0?'':','.$search['city']).($search['district']==0?'':','.$search['district']).'%';
			$where_logistics_match=" AND lm_t.path LIKE '".addslashes($path)."' ";
			$data=D('LogisticsMatch')->queryLogisticsMatch($where_logistics_match,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else{
			$id_list=array();
			$id_list=$this->getIDList($id_list,array('toolbar','id_datagrid','form','edit','add','change'));
			$model=get_config_value('logistics_match_mode','2');
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('LogisticsMatch/getLogisticsMatchList', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields' => get_field('LogisticsMatch','logistics_match')
					);
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('form_id'=>$id_list['form']),
					'edit'=>array('id'=>$id_list['edit'],'url'=>U('LogisticsMatch/editLogisticsMatch'),'title'=>'编辑物流匹配','height'=>500,'width'=>895,'ismax'=>false),
					'add'=>array('id'=>$id_list['edit'],'url'=>U('LogisticsMatch/addLogisticsMatch'),'title'=>'新建物流匹配','height'=>500,'width'=>895,'ismax'=>false),
					'delete'=>array('url'=>U('LogisticsMatch/deleteLogisticsMatch'))
			);
			$list_form=UtilDB::getCfgList(array('logistics'));
			$faq_url=C('faq_url');
			$this->assign('faq_url',$faq_url['logistics_match']);
			$this->assign("model",$model);
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign("id_list",$id_list);
			$this->assign("datagrid",$datagrid);
			$this->display('show');
		}
	}
	
	public function editLogisticsMatch($id)
	{
	 	if(IS_POST)
    	{
            $match=I('post.info','',C('JSON_FILTER'));
            $match['rec_id']=$id;
            $this->saveLogisticsMatch($match);
    	}else
		{
			$type=I('get.type');
			$match_arr=D('LogisticsMatch')->getLogisticsMatch(
					'rec_id AS id, path, level, target_id, logistics_id, amount_logistics_id AS amount_logistics_id1, amount_logistics_id2, amount_logistics_id3, 
					paid_amount AS paid_amount1, paid_amount2, paid_amount3, weight_logistics_id AS weight_logistics_id1, weight_logistics_id2, weight_logistics_id3,
					weight AS weight1, weight2, weight3, except_words_weight AS except_words_weight1, except_words_weight2, except_words_weight3, 
					except_words, except_words2, except_logistics_id, except_logistics_id2, modified, created',
					array('rec_id'=>array('in',$id))
			);
			if($type=='copy')
			{
				$match['id']=0;
			}
			$match=$match_arr[0];
			$model=get_config_value('logistics_match_mode','2');
			if ($model==1) {
				$match['warehouse_target_id']=$match['target_id'];
			}else{
				$match['shop_target_id']=$match['target_id'];
			}
			unset($match['path']);
			unset($match['id']);
			unset($match['target_id']);
			foreach ($match_arr as $k=>$v){
				$match['id'].=$v['id'].',';
				$path=substr($v['path'],strpos($v['path'],'#,')+2,strlen($v['path'])-strpos($v['path'],'#,')-2);
				$area=explode(',', $path);
				$match['area'][$k]['province']=isset($area[0])?$area[0]:0;
				$match['area'][$k]['city']=isset($area[1])?$area[1]:0;
				$match['area'][$k]['district']=isset($area[2])?$area[2]:0;
				$match['area'][$k]['level']=$v['level'];
				$match['area'][$k]['id']=$v['id'];
			}
			$match['id']=substr($match['id'],0, strlen($match['id'])-1);
			$this->showMatchDialog($match);
		}
	}
	
	public function addLogisticsMatch()
	{
		if(IS_POST)
		{
			$match=I('post.info','',C('JSON_FILTER'));
			$this->saveLogisticsMatch($match);
		}else
		{
			$match=array('province'=>0,'city'=>0,'district'=>0);
			$this->showMatchDialog($match);
		}
	}
	
	private function showMatchDialog($match)
	{
		try
		{
			$id_list=array('form_id'=>'logistics_match_dialog');
			$list_form=UtilDB::getCfgList(array('logistics','shop','warehouse'),array('logistics'=>array('is_disabled'=>array('eq',0)),'shop'=>array('is_disabled'=>array('eq',0)),'warehouse'=>array('is_disabled'=>array('eq',0))));
            array_unshift($list_form['shop'], array('id'=>0,'name'=>'全部'));
            array_unshift($list_form['warehouse'], array('id'=>0,'name'=>'全部'));
			$list_form['logistics'][]=array('id'=>0,'name'=>'无');
			$faq_url=C('faq_url');			
			$match['except_words']=preg_replace("/[\n\r\t\f]/", "  ", $match['except_words']);
			$match['except_words2']=preg_replace("/[\n\r\t\f]/", "  ", $match['except_words2']);
			$model=get_config_value('logistics_match_mode','2');
			$this->assign('model',$model);
			$this->assign('faq_url',$faq_url);
			$this->assign("list",$list_form);
			$this->assign("match",json_encode($match));
			$this->assign("id",$match['id']);
			$this->assign("id_list",$id_list);
		}catch (\Exception $e){
			$this->assign("message",$e->getMessage());
			$this->display('Common@Exception:dialog');
			exit();
		}
		$this->display('dialog_match');
	}
	
	private function saveLogisticsMatch($match)
	{
		$user_id=get_operator_id();
		$old_area=array();
		$logistics_match_db=D('LogisticsMatch');
		try{
			$match['amount_logistics_id']=set_default_value($match['amount_logistics_id1'], 0);
			$match['amount_logistics_id2']=set_default_value($match['amount_logistics_id2'], 0);
			$match['amount_logistics_id3']=set_default_value($match['amount_logistics_id3'], 0);
			$match['paid_amount']=set_default_value($match['paid_amount1'],0);
			$match['paid_amount2']=set_default_value($match['paid_amount2'],0);
			$match['paid_amount3']=set_default_value($match['paid_amount3'],0);
			$match['weight_logistics_id']=set_default_value($match['weight_logistics_id1'], 0);
			$match['weight_logistics_id2']=set_default_value($match['weight_logistics_id2'], 0);
			$match['weight_logistics_id3']=set_default_value($match['weight_logistics_id3'], 0);
			$match['weight']=set_default_value($match['weight1'],0);
			$match['weight2']=set_default_value($match['weight2'],0);
			$match['weight3']=set_default_value($match['weight3'],0);
			$match['except_words_weight']=set_default_value($match['except_words_weight1'], '');
			$match['except_words_weight2']=set_default_value($match['except_words_weight2'], '');
			$match['except_words_weight3']=set_default_value($match['except_words_weight3'], '');
			$match['except_words']=set_default_value($match['except_words'],'');
			$match['except_logistics_id']=set_default_value($match['except_logistics_id'], 0);
			$match['except_words2']=set_default_value($match['except_words2'],'');
			$match['except_logistics_id2']=set_default_value($match['except_logistics_id2'], 0);
			$match['remark']=set_default_value($match['remark'], '');
// 			$match['target_id']=0;
			$match['modified']=date('Y-m-d H:i:s',time());
			
			if (isset($match['rec_id']))
			{
				// 根据rec_id查找old_area
				$old_match_arr=D('LogisticsMatch')->getLogisticsMatch(
					'rec_id AS id, path, level',
					array('rec_id'=>array('in',$match['rec_id']))
				);
				foreach ($old_match_arr as $k=>$v){
					$path=substr($v['path'],strpos($v['path'],'#,')+2,strlen($v['path'])-strpos($v['path'],'#,')-2);
					$area=explode(',', $path);
					$match['old_area'][$k]['province']=isset($area[0])?$area[0]:0;
					$match['old_area'][$k]['city']=isset($area[1])?$area[1]:0;
					$match['old_area'][$k]['district']=isset($area[2])?$area[2]:0;
					$match['old_area'][$k]['level']=$v['level'];
					$match['old_area'][$k]['id']=$v['id'];
				}
				foreach ($match['old_area'] as $v){
					$old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']]=$v[id];
				}
				foreach ($match['area'] as $k=>$v)
				{
					$match['level']=$v['district']==0?($v['city']==0?($v['province']==0?0:1):2):3;
					$match['province']=$v['province'];
					$match['city']=$v['city'];
					$match['district']=$v['district'];
					$logistics_match_db->validateLogisticsMatch($match);
					$match['city']=$v['city']==0?'':','.$v['city'];
					$match['district']=$v['district']==0?'':','.$v['district'];
					$match['path']=$match['target_id'].'#,'.$v['province'].$match['city'].$match['district'];
					if($old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']])
					{
						$match['rec_id']=$old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']];
						unset($old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']]);
						$logistics_match_db->editMatch($match,$user_id);
					}else
					{//有新加地址，新建一条策略
						unset($match['rec_id']);
						$logistics_match_db->addMatch($match,$user_id);
					}
				}
				if(!empty($old_area))
				{//有删掉的地址，删除该条地址下的策略
					$delete_ids=implode(',',$old_area);
					D('LogisticsMatch')->deleteMatch($delete_ids,$user_id);
				}
			}else
			{
				foreach ($match['area'] as $v){
					$match['level']=$v['district']==0?($v['city']==0?($v['province']==0?0:1):2):3;
					$match['province']=$v['province'];
					$match['city']=$v['city'];
					$match['district']=$v['district'];
					$logistics_match_db->validateLogisticsMatch($match);
					$v['city']=$v['city']==0?'':','.$v['city'];
					$v['district']=$v['district']==0?'':','.$v['district'];
					$match['path']=$match['target_id'].'#,'.$v['province'].$v['city'].$v['district'];
					$logistics_match_db->addMatch($match,$user_id);
				}
			}
		}catch (BusinessLogicException $e) 
        {
            $this->error($e->getMessage());
        }
        $this->success();
	}
	
	public function deleteLogisticsMatch()
	{
		$id=I('post.id');
		try 
		{
			D('LogisticsMatch')->deleteMatch($id,get_operator_id());
		}catch (BusinessLogicException $e)
		{
			$this->error($e->getMessage());
		}
		$this->success();
	}
	public function changeLogisticsMatchModel()
	{
		if(IS_POST){
			$model=I('post.logistics_match_mode');	
			try{
				D('LogisticsMatch')->changeLogisticsMatchModel($model);
			}catch (BusinessLogicException $e)
			{
				$this->error($e->getMessage());
			}		
			$this->success();
		}else{
			$model=get_config_value('logistics_match_mode','2');
			$change_params = array(
				'form_id'		=> 'change_model_form',
				'form_url'		=> U('LogisticsMatch/changeLogisticsMatchModel')
			);
			$this->assign("change_params", $change_params);
			$this->assign("model",$model);
			$this->display('dialog_match_model');
		}
	}
}