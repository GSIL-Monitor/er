<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;


class WarehouseRuleController extends BaseController
{
    public function showWarehouseRule()
    {
        try
        {
            $id_list=array(
                    'tab_container'=>'warehouse_rule_tab_container',
            );
            $arr_tabs=array(
                    array('id'=>$id_list['tab_container'],'url'=>U('WarehouseRule/showTabs',array('tabs'=>'shop_warehouse')).'?tab=shop_warehouse','title'=>'店铺使用仓库'),
                    array('id'=>$id_list['tab_container'],'url'=>U('WarehouseRule/showTabs',array('tabs'=>'warehouse_area')).'?tab=warehouse_area','title'=>'仓库覆盖范围'),
            );
            $this->assign("id_list",$id_list);
            $this->assign('arr_tabs', json_encode($arr_tabs));
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $faq_url=C('faq_url');
        $this->assign('faq_url',$faq_url['warehouse_rule']);
        $this->display('dialog_warehouse_rule');
    }

    function showTabs($tab)
    {
        switch ($tab) {
            case 'shop_warehouse':
                $this->editShopWarehouse();
                break;
            case 'warehouse_area':
                $this->editWarehouseArea();
                break;
            default:
                $this->assign('message','Not Found Tab');
                $this->display('Common@Exception:dialog');
                exit();
                break;
        }
    }

    public function editShopWarehouse()
    {
    	 if(IS_POST)
    	 {
            $shop_warehouse=I('post.shop_warehouse','',C('JSON_FILTER'));
            $shop_id=I('post.shop_id');
            try
            {
                if (intval($shop_id)==0)
                {
                    SE('请选择店铺！');
                }
                if (count($shop_warehouse)==0)
                {
                    SE('仓库列表没有更新数据！');
                }
                D('ShopWarehouse')->editShopWarehouse($shop_warehouse,$shop_id,get_operator_id());
            }catch(BusinessLogicException $e)
            {
                $this->error($e->getMessage());
            }
            $this->success();
    	 }else
    	 {
            $id_list=array();
            $id_list=$this->getIDList($id_list, array('id_datagrid'),'shop');
            $datagrid = array(
                    'id'=>$id_list['id_datagrid'],
                    'style'=>'',
                    'class'=>'',
                    'options'=> array(
                            'title' => '仓库列表',
                            // 'url'   =>U('WarehouseRule/getShopWarehouseList', array('grid'=>'datagrid')),
                            // 'toolbar' =>"#{$id_list['toolbar']}",
                            'singleSelect'=>true,
                            'pagination'=>false,
                            'fitColumns'=>true,
                            'fit'=>true,
                    ),
                    'fields' => get_field('WarehouseRule','shop_warehouse')
            );
            $this->assign('datagrid', $datagrid);
            $this->display('dialog_shop_warehouse');
    	 }
    }

    public function editWarehouseArea()
    {
         if(IS_POST)
         {
            try
            {
                $warehouse_area=I('post.warehouse_area','',C('JSON_FILTER'));
                $warehouse_id=I('post.warehouse_id');
                if (intval($warehouse_id)==0)
                {
                    SE('请选择仓库！');
                }
                D('WarehouseArea')->editWarehouseArea($warehouse_area,$warehouse_id,get_operator_id());
            }catch(BusinessLogicException $e)
            {
                $this->error($e->getMessage());
            }
            $this->success();
         }else
         {
            $id_list=array();
            $id_list=$this->getIDList($id_list, array('id_datagrid'),'area');
            $datagrid = array(
                    'id'=>$id_list['id_datagrid'],
                    'style'=>'',
                    'class'=>'',
                    'options'=> array(
                            'title' => '仓库列表',
                            'url'   =>U('WarehouseRule/getWarehouseList', array('grid'=>'datagrid')),
                            // 'toolbar' =>"#{$id_list['toolbar']}",
                            'fit'=>true,
                            'singleSelect'=>true,
                            'pagination'=>false,
                            'fitColumns'=>true,
                            'rownumbers'=>false,
                    ),
                    'fields' => get_field('WarehouseRule','warehouse') );
            $this->assign('datagrid', $datagrid);
            $this->display('dialog_warehouse_area');
         }
    }

    public function getShopWarehouseList($shop_id=0)
    {
        $result=array();
        try
        {
            $shop_warehouse=D('ShopWarehouse')->getShopWarehouseByShopId($shop_id);
            $result=array('total'=>count($shop_warehouse),'rows'=>$shop_warehouse);
        }catch(BusinessLogicException $e)
        {
            $result=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($result);
    }

    public function getShopList()
    {
        $shop_list=array();
        $list=array();
        try
        {
            $list=UtilDB::getCfgList(array('shop'),array('shop'=>array('is_disabled'=>array('eq',0))));
            $shop_list=array('total'=>count($list['shop']),'rows'=>$list['shop']);
        }catch(\Exception $e)
        {
            $shop_list=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($shop_list);
    }

    public function getWarehouseList()
    {
        $warehouses=array();
        $warehouse_list=array();
        try
        {
            $warehouses=D('Warehouse')->getWarehouseList('warehouse_id AS id, name, province,city,district',array('is_disabled'=>array('eq',0)));
            $warehouse_list=array('total'=>count($warehouses),'rows'=>$warehouses);
        }catch(\Exception $e)
        {
            $warehouse_list=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($warehouse_list);
    }

    public function getAreaTree()
    {
        $tree=array();
        try
        {
            $tree=D('WarehouseArea')->getAreaTree();
        }catch(BusinessLogicException $e)
        {
            $tree=array();
        }
        $this->ajaxReturn($tree);
    }

    public function getWarehouseAreaList($warehouse_id=0)
    {
        $warehouse_id=intval($warehouse_id);
        $warehouse_area=array();
        try
        {
            $warehouse_area=D('WarehouseArea')->getWarehouseAreaList('warehouse_id,path,level,province_id,city_id,district_id',array('warehouse_id'=>array('eq',$warehouse_id)));
        }catch(BusinessLogicException $e)
        {
            $warehouse_area=array();
        }
        $this->ajaxReturn($warehouse_area);
    }
}