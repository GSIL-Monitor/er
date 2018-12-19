<?php
/**
 * Created by PhpStorm.
 * User: Asher
 * Date: 2016-11-01
 * Time: 17:03
 */

namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class GoodOperateLogController extends BaseController{
    public function getGoodOperateLogList($page = 1, $rows = 20, $search = array(), $sort = 'created', $order = 'desc'){
        if(IS_POST){
            if(empty($search)){
                $search = array(
                    'start_time' => date('Y-m-d'),
                    'end_time' => date('Y-m-d'),
                );
            }

            $data = D('GoodOperateLog')->queryGoodOperateLog($page,$rows,$search,$sort,$order);
            $this->ajaxReturn($data);
        }else{
            $id_list = array();
            $this->getIDList($id_list,array("form","toolbar","id_datagrid"));
            $datagrid = array(
                'id'=>$id_list['id_datagrid'],
                'style'=>'',
                'class'=>'',
                'options'=>array(
                    'title'        => '',
                    'url'          => U('GoodOperateLog/getGoodOperateLogList',array('grid'=>'datagrid')),
                    'toolbar'      => "#{$id_list['toolbar']}",
                    'fitcolumns'   => false,
                    'singleSelect' => false,
                    'ctrlSelect'   => true,
                ),
                'fields'=>get_field('GoodOperateLog','good_operate_log'),
            );
            $list_form = UtilDB::getCfgRightList(array('employee'));
            $params = array(
                'datagrid' => array('id'=>$id_list['id_datagrid']),
                'search'   => array('form_id'=>$id_list['form']),
            );
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $this->assign("start_time",$start_time);
            $this->assign("end_time",$end_time);
            $this->assign("list",$list_form);
            $this->assign("params",json_encode($params));
            $this->assign("id_list",$id_list);
            $this->assign("datagrid",$datagrid);
            $this->display("show");
        }
    }
}