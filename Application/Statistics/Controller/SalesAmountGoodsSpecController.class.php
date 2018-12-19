<?php

namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception;

class SalesAmountGoodsSpecController extends BaseController{
    public function getGoodsSpecStat($page=1, $rows=20, $search = array(), $sort = 'ssps.spec_id', $order = 'desc'){
        if(IS_POST){
            try{
                if(empty($search)){
                    $search = array(
                        'start_time' => date('Y-m-d',strtotime('-30 day')),
                        'end_time' => date('Y-m-d')        
                    );
                }
                $data=D("SalesAmountGoodsSpec")->loadDataByCondition($page, $rows, $search, $sort, $order);
            }catch (Exception $e){
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else{
            $id_list = array(
                'form'  =>'sales_amount_goods_spec_form',
                'toolbar'   =>'sales_amount_goods_spec_toolbar',
                'tab_container' =>'sales_amount_goods_spec_tab_container',
                'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
                'edit'  =>'sales_amount_goods_spec_dialog',
                'more_button'=>'sales_amount_goods_spec_more_button',
                'more_content'=>'sales_amount_goods_spec_more_content',
                'hidden_flag'=>'sales_amount_goods_spec_hidden_flag',
                'help_id' => 'sales_amount_goods_spec_help_id'
            );
            $datagrid = array(
                'id' => $id_list['id_datagrid'],
                'style' => '',
                'class' => '',
                'options' => array(
                    'title' => '',
                    'url' => U('SalesAmountGoodsSpec/getGoodsSpecStat'),
                    'toolbar' => "#{$id_list['toolbar']}",
                    'fitColumns'=>false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true,
                ),
                'fields' => get_field('Statistics/SalesAmountGoodsSpec','sales_amount_goods_spec')
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            $params = array(
                'controller'    =>strtolower(CONTROLLER_NAME),
                'datagrid'  => array("id"  => $id_list["id_datagrid"],),
                'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
                'help' =>array(
                    'id' => $id_list['help_id'],
                    'url' =>  U('StatisticsCommon/getHelpInfo?type=stastics_goods')
                ),
            );
            $list_form=UtilDB::getCfgRightList(array('shop','brand','warehouse'), ['brand' => ['is_disabled' => 0]]);
            $date = array(
                'start' => date('Y-m-d',strtotime('-1 month')),
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
        $id_list['id'] = I('get.id_list');
        $id_list['s_id'] = I('get.shopid_list');
        try{
            $search = I('get.search','',C('JSON_FILTER'));
            $type = I('get.type','',C('JSON_FILTER'));
            $startnum = strlen('search[');
            $endnum = strlen('search[]');
            foreach ($search as $k => $v) {
                $key=substr($k,$startnum,strlen($k)-$endnum);
                $search[$key]=$v;
                unset($search[$k]);
            }

            D('SalesAmountGoodsSpec')->exportToExcel($search, $id_list,$type);
        }

        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }


}