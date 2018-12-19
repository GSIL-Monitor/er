<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;


class LogisticsFeeController extends BaseController
{
    public function getLogisticsFeeList($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	if(IS_POST)
    	{
            $path='%#,'.($search['province']==0?'':$search['province'].',').($search['city']==0?'':$search['city'].',').($search['district']==0?'':$search['district'].',').'%';
    		$where_logistics_fee=" AND lf_t.path LIKE '".addslashes($path)."' ";//不同初始化搜索条件
			$data=D('LogisticsFee')->queryLogisticsFee($where_logistics_fee,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
    	}else 
    	{
    		// $id_list=$this->getIdList();
            $id_list=array();
            $id_list=$this->getIDList($id_list, array('toolbar','id_datagrid','form','edit','add'));
            $datagrid = array(
                    'id'=>$id_list['id_datagrid'],
                    'style'=>'',
                    'class'=>'',
                    'options'=> array(
                            'title' => '',
                            'url'   =>U('LogisticsFee/getLogisticsFeeList', array('grid'=>'datagrid')),
                            'toolbar' =>"#{$id_list['toolbar']}",
                            'fitColumns'=>false,
                            'singleSelect'=>false,
                            'ctrlSelect'=>true,
                    ),
                    'fields' => get_field('LogisticsFee','logistics_fee')
            );
            $params=array(
                    'datagrid'=>array('id'=>$id_list['id_datagrid']),
                    'search'=>array('form_id'=>$id_list['form']),
                    'edit'=>array('id'=>$id_list['edit'],'url'=>U('LogisticsFee/editLogisticsFee'),'title'=>'编辑物流邮资','height'=>580,'width'=>900,'ismax'=>false),
                    'add'=>array('id'=>$id_list['edit'],'url'=>U('LogisticsFee/addLogisticsFee'),'title'=>'新建物流邮资','height'=>580,'width'=>900,'ismax'=>false),
                    'delete'=>array('url'=>U('LogisticsFee/deleteLogisticsFee')),
            );
            $faq_url=C('faq_url');
            $this->assign('faq_url',$faq_url['logistics_fee']);
            $list_form=UtilDB::getCfgList(array('logistics'));
            $this->assign("list",$list_form);
            $this->assign("params",json_encode($params));
            $this->assign("id_list",$id_list);
            $this->assign('datagrid', $datagrid);
    		$this->display('show');
    	}
    }
    
    public function editLogisticsFee($id)
    {
    	 if(IS_POST)
    	 {
            $postage=I('post.info','',C('JSON_FILTER'));
            $postage['rec_id']=$id;
//             unset($postage['logistics_id']);
            $this->saveLogisticsFee($postage);
    	 }else 
    	 {
            $type=I('get.type');
            $postage_arr=D('LogisticsFee')->getLogisticsFee(
                    'rec_id AS id ,path ,target_id ,level ,logistics_id ,first_weight ,first_price ,weight_step1 ,
                     unit_step1 ,price_step1 ,weight_step2 , unit_step2 ,price_step2 ,weight_step3 ,unit_step3 ,
                     price_step3 ,weight_step4 ,unit_step4 ,price_step4 ,special_weight1,special_fee1,
                     special_weight2,special_fee2,special_weight3,special_fee3,trunc_mode ,modified ,created',
                    array('rec_id'=>array('in',$id))
            );
            if($type=='copy')
            {
                $postage['id']=0;
            }
            $postage=$postage_arr[0];
            unset($postage['path']);
            unset($postage['id']);
            foreach ($postage_arr as $k=>$v){
            	$postage['id'].=$v['id'].',';
            	$path=substr($v['path'],strpos($v['path'],'#,')+2,strpos($v['path'],',#')-strpos($v['path'],'#,')-2);
            	$area=explode(',', $path);
            	$postage['area'][$k]['province']=isset($area[0])?$area[0]:0;
            	$postage['area'][$k]['city']=isset($area[1])?$area[1]:0;
            	$postage['area'][$k]['district']=isset($area[2])?$area[2]:0;
            	$postage['area'][$k]['level']=$v['level'];
            	$postage['area'][$k]['id']=$v['id'];
            }
            $postage['id']=substr($postage['id'],0, strlen($postage['id'])-1);
    	 	$this->showPostageDialog($postage);
    	 }
    }

    public function addLogisticsFee()
    {
         if(IS_POST)
         {
            $postage=I('post.info','',C('JSON_FILTER'));
            $this->saveLogisticsFee($postage);
         }else 
         {
            $postage=array('province'=>0,'city'=>0,'district'=>0,'id'=>0);
            $this->showPostageDialog($postage);
         }
    }

    private function saveLogisticsFee($postage)
    {
        $user_id=get_operator_id();
        $old_area=array();
        $logistics_fee_db=D('LogisticsFee');
        unset($postage['weigth_step0']);
        try 
        {
            /*if($postage['province']==0)
            {
                SE('请选择地址');
            }*/
        	$postage['logistics_id']=set_default_value($postage['logistics_id'],0);
            $postage['weight_step2']=set_default_value($postage['weight_step2'],0);
            $postage['weight_step3']=set_default_value($postage['weight_step3'],0);
            $postage['weight_step4']=set_default_value($postage['weight_step4'],0); 
            $postage['unit_step2']=set_default_value($postage['unit_step2'],0);
            $postage['unit_step3']=set_default_value($postage['unit_step3'],0);
            $postage['unit_step4']=set_default_value($postage['unit_step4'],0);
            $postage['price_step2']=set_default_value($postage['price_step2'],0);
            $postage['price_step3']=set_default_value($postage['price_step3'],0);
            $postage['price_step4']=set_default_value($postage['price_step4'],0);
            $postage['special_weight1']=set_default_value($postage['special_weight1'],0);
            $postage['special_weight2']=set_default_value($postage['special_weight2'],0);
            $postage['special_weight3']=set_default_value($postage['special_weight3'],0);
            $postage['special_fee1']=set_default_value($postage['special_fee1'],0);
            $postage['special_fee2']=set_default_value($postage['special_fee2'],0);
            $postage['special_fee3']=set_default_value($postage['special_fee3'],0);
//             $logistics_fee_db->validateLogisticsFee($postage);
            $postage['level']=$postage['district']==0?($postage['city']==0?($postage['province']==0?0:1):2):3;
//             $postage['target_id']=0;
//             $postage['city']=$postage['city']==0?'':','. $postage['city'];
//             $postage['district']=$postage['district']==0?'':','. $postage['district'];
//             $postage['path']=$postage['target_id'].'#,'.$postage['province'].$postage['city'].$postage['district'].',#'.$postage['logistics_id'];
//             unset($postage['province']);
//             unset($postage['city']);
//             unset($postage['district']);
//             $postage['modified']=date('Y-m-d H:i:s',time());
//             if (isset($postage[rec_id])) 
//             {
//                 $logistics_fee_db->editPostage($postage,$user_id);
//             }else
//             {
//                 $logistics_fee_db->addPostage($postage,$user_id);
//             }
			foreach ($postage['old_area'] as $v){
				$old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']]=$v[id];
			}
            if (isset($postage[rec_id]))
            {
            	foreach ($postage['area'] as $k=>$v)
				{
					$postage['level']=$v['district']==0?($v['city']==0?($v['province']==0?0:1):2):3;
					$postage['province']=$v['province'];
					$postage['city']=$v['city'];
					$postage['district']=$v['district'];
					$logistics_fee_db->validateLogisticsFee($postage);
					$postage['city']=$v['city']==0?'':','.$v['city'];
					$postage['district']=$v['district']==0?'':','.$v['district'];
					$postage['path']=$postage['target_id'].'#,'.$v['province'].$postage['city'].$postage['district'].',#'.$postage['logistics_id'];
					if($old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']])
					{
						$postage['rec_id']=$old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']];
						unset($old_area[$v['level'].'_'.$v['province'].'_'.$v['city'].'_'.$v['district']]);
						$logistics_fee_db->editPostage($postage,$user_id);
					}else
					{//有新加地址，新建一条策略
						unset($postage['rec_id']);
						$logistics_fee_db->addPostage($postage,$user_id);
					}
				}
				if(!empty($old_area))
				{//有删掉的地址，删除该条地址下的策略
					$delete_ids=implode(',',$old_area);
					$logistics_fee_db->deletePostage($delete_ids,$user_id);
				}
            }else
            {
            	foreach ($postage['area'] as $v){
            		$postage['level']=$v['district']==0?($v['city']==0?($v['province']==0?0:1):2):3;
            		$postage['province']=$v['province'];
            		$postage['city']=$v['city'];
            		$postage['district']=$v['district'];
            		$logistics_fee_db->validateLogisticsFee($postage);
            		$v['city']=$v['city']==0?'':','.$v['city'];
            		$v['district']=$v['district']==0?'':','.$v['district'];
            		$postage['path']=$postage['target_id'].'#,'.$v['province'].$v['city'].$v['district'].',#'.$postage['logistics_id'];
            		$logistics_fee_db->addPostage($postage,$user_id);
            	}
            }
        }catch (BusinessLogicException $e) 
        {
            $this->error($e->getMessage());
        }
        $this->success();
    }

    private function showPostageDialog($postage)
    {
        try
        {
            $id_list=array('form_id'=>'logistics_fee_dialog');
            $list_form=UtilDB::getCfgList(array('logistics','shop'),array('logistics'=>array('is_disabled'=>array('eq',0)),'shop'=>array('is_disabled'=>array('eq',0))));
            array_unshift($list_form['shop'], array('id'=>0,'name'=>'全部'));
            $this->assign("list",$list_form);
            $this->assign("postage",json_encode($postage));
            $this->assign("id",$postage['id']);
            $this->assign("id_list",$id_list);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_postage');
    }

    public function deleteLogisticsFee()
    {
    	$id=I('post.id');
        try 
        {
            D('LogisticsFee')->deletePostage($id,get_operator_id());
        }catch (BusinessLogicException $e) 
        {
            $this->error($e->getMessage());
        }
        $this->success();
    }
    
}