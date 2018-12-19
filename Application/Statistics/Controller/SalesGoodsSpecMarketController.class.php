<?php

namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception;

class SalesGoodsSpecMarketController extends BaseController{
    public function getGoodsSpecStat($page=1, $rows=20, $search = array(), $sort = 'num', $order = 'desc'){
        if(IS_POST){
            try{
                if(empty($search)){
                    $search = array(
                        'start_time' => date('Y-m-d',strtotime('-7 day')),
                        'end_time' => date('Y-m-d')        
                    );
                }
                $data=D("SalesGoodsSpecMarket")->loadDataByCondition($page, $rows, $search, $sort, $order);
            }catch (Exception $e){
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else{
            $id_list = array(
                'form'  =>'sales_goods_spec_market_form',
                'toolbar'   =>'sales_goods_spec_market_toolbar',
                'tab_container' =>'sales_goods_spec_market_tab_container',
                'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
                'edit'  =>'sales_goods_spec_market_dialog',
                'more_button'=>'sales_goods_spec_market_more_button',
                'more_content'=>'sales_goods_spec_market_more_content',
                'hidden_flag'=>'sales_goods_spec_market_hidden_flag',
                'help_id'=>'sales_goods_spec_market_help_id',
            );
            $datagrid = array(
                'id' => $id_list['id_datagrid'],
                'style' => '',
                'class' => '',
                'options' => array(
                    'title' => '',
                    'url' => U('SalesGoodsSpecMarket/getGoodsSpecStat'),
                    'toolbar' => "#{$id_list['toolbar']}",
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Statistics/SalesGoodsSpecMarket','sales_goods_spec_market',1),
                    'fitColumns'=>false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true,
                ),
                'fields' => D('Setting/UserData')->getDatagridField('Statistics/SalesGoodsSpecMarket','sales_goods_spec_market')
            );
//            $checkbox=array('field' => 'ck','checkbox' => true);
//            array_unshift($datagrid['fields'],$checkbox);
            $params = array(                                             
                'controller'    =>strtolower(CONTROLLER_NAME),
                'datagrid'  => array("id"  => $id_list["id_datagrid"],),
                'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
                'help' => array(
                    'id' => $id_list['help_id'],
                    'url' => U('StatisticsCommon/getHelpInfo?type=stastics_goods')
                )
            );
            $list_form=UtilDB::getCfgRightList(array('shop','brand'), ['brand' => ['is_disabled' => 0]]);
            $date = array(
                'start' => date('Y-m-d',strtotime('-7 day')),
                'end'   => date('Y-m-d')
                );
            $this->assign('date',$date);
            $this->assign('list',$list_form);
            $this->assign('goods_brand', $list_form['brand']);
            $this->assign('id_list',$id_list);
            $this->assign('datagrid',$datagrid);
            $this->assign('params',json_encode($params));
            $this->display('show');
        }
    }
    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $result = array('status'=>0,'info'=>'');
        $id_list = I('get.id_list');
        try{
                $search = I('get.search','',C('JSON_FILTER'));
                $startnum = strlen('search[');
                $endnum = strlen('search[]');
                foreach ($search as $k => $v) {
                    $key=substr($k,$startnum,strlen($k)-$endnum);
                    $search[$key]=$v;
                    unset($search[$k]);
                }

                D('SalesGoodsSpecMarket')->exportToExcel($search, $id_list);
        }

        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }


}