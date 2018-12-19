<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;


class AreaAliasController extends BaseController
{
    public function getAreaAliasList($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	if(IS_POST)
    	{
            $path='0#,'.($search['province_id']==0?'':$search['province_id']).($search['city_id']==0?'':','.$search['city_id']).($search['district_id']==0?'':','.$search['district_id']).'%';
    		$where_logistics_area_alias=" AND laa_1.path LIKE '".addslashes($path)."' ";
			$data=D('AreaAlias')->queryAreaAlias($where_logistics_area_alias,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
    	}else 
    	{
            $id_list=array();
            $id_list=$this->getIDList($id_list, array('toolbar','id_datagrid','form','edit','add'));
            $datagrid = array(
                    'id'=>$id_list['id_datagrid'],
                    'style'=>'',
                    'class'=>'',
                    'options'=> array(
                            'title' => '',
                            'url'   =>U('AreaAlias/getAreaAliasList', array('grid'=>'datagrid')),
                            'toolbar' =>"#{$id_list['toolbar']}",
                            'fitColumns'=>false,
                            'singleSelect'=>false,
                            'ctrlSelect'=>true,
                    ),
                    'fields' => get_field('AreaAlias','logistics_area_alias')
            );
            $params=array(
                    'datagrid'=>array('id'=>$id_list['id_datagrid']),
                    'search'=>array('form_id'=>$id_list['form']),
                    'edit'=>array('id'=>$id_list['edit'],'url'=>U('AreaAlias/editAreaAlias'),'title'=>'编辑地区别名','height'=>210,'width'=>500,'ismax'=>false),
                    'add'=>array('id'=>$id_list['edit'],'url'=>U('AreaAlias/addAreaAlias'),'title'=>'新建地区别名','height'=>210,'width'=>500,'ismax'=>false),
                    'delete'=>array('url'=>U('AreaAlias/deleteAreaAlias')),
            );
            $faq_url=C('faq_url');
            $list_form=UtilDB::getCfgList(array('logistics'));
            $this->assign('faq_url',$faq_url['area_alias']);
            $this->assign("list",$list_form);
            $this->assign("params",json_encode($params));
            $this->assign("id_list",$id_list);
            $this->assign('datagrid', $datagrid);
    		$this->display('show');
    	}
    }

    public function editAreaAlias($id)
    {
         $id=intval($id);
         if(IS_POST)
         {  
            $alias=I('post.info','',C('JSON_FILTER'));
            $alias['rec_id']=$id;
            $this->saveLogisticsAreaAlias($alias);
         }else 
         {
            $alias=D('AreaAlias')->getLogisticsAreaAlias(
                    'rec_id AS id ,path ,logistics_id ,alias_name ,province_id ,city_id ,district_id',
                    array('rec_id'=>array('eq',$id))
            );
            $this->showAreaAliasDialog($alias);
         }
    }

    public function addAreaAlias()
    {
         if(IS_POST)
         {
            $alias=I('post.info','',C('JSON_FILTER'));
            $this->saveLogisticsAreaAlias($alias);
         }else 
         {
            $alias=array('province_id'=>0,'city_id'=>0,'district_id'=>0,'id'=>0);
            $this->showAreaAliasDialog($alias);
         }
    }

    private function saveLogisticsAreaAlias($alias)
    {
        $user_id=get_operator_id();
        $logistics_area_alias_db=D('AreaAlias');
        try 
        {   
            $logistics_area_alias_db->validateLogisticsAreaAlias($alias);
            $alias['level']=$alias['district_id']==0?($alias['city_id']==0?($alias['province_id']==0?0:1):2):3;
            $alias['city_id']=$alias['city_id']==0?'':','.$alias['city_id'];
            $alias['district_id']=$alias['district_id']==0?'':','.$alias['district_id'];
            $alias['path']='0#,'.$alias['province_id'].$alias['city_id'].$alias['district_id'].',#'.$alias['logistics_id'];
            $alias['modified']=date('Y-m-d H:i:s',time());
            //存储时去掉“，”
            $alias['city_id']=substr($alias['city_id'], 1);
            $alias['district_id']=substr($alias['district_id'], 1);
            if (isset($alias['rec_id'])) 
            {
                $logistics_area_alias_db->editalias($alias,$user_id);
            }else
            {
                $logistics_area_alias_db->addalias($alias,$user_id);
            }
        }catch (BusinessLogicException $e) 
        {
            $this->error($e->getMessage());
        }
        $this->success();
    }

    private function showAreaAliasDialog($alias)
    {
        try
        {
            $id_list=array('form_id'=>'logistics_area_alias_dialog');
            $list_form=UtilDB::getCfgList(array('logistics'));
            $this->assign("list",$list_form);
            $this->assign("alias",json_encode($alias));
            $this->assign("id",$alias['id']);
            $this->assign("id_list",$id_list);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_area_alias');
    }

    public function deleteAreaAlias()
    {
        $id=intval(I('post.id'));
        try 
        {
            D('AreaAlias')->deleteAreaAlias($id,get_operator_id());
        }catch (BusinessLogicException $e) 
        {
            $this->error($e->getMessage());
        }
        $this->success();
    }


}