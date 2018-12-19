<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class HistorySalesTradeLogController extends BaseController{
	public function getSalesTradeLog($page = 1, $rows = 20, $search = array(), $sort = 'created', $order = 'desc'){
		if(IS_POST){
			if(empty($search)){
	            $search = array(
	                'start_time' => date('Y-m-d'),
	                'end_time' => date('Y-m-d'),
	             );
	        }
	        $where_sales_trade_log = " AND stlh_1.type <> -1 ";
			$data = D('HistorySalesTradeLog')->querySalesTradeLog($where_sales_trade_log,$page,$rows,$search,$sort,$order);			
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
					'url'          => U('HistorySalesTradeLog/getSalesTradeLog',array('grid'=>'datagrid')),
					'toolbar'      => "#{$id_list['toolbar']}",
					'fitcolumns'   => false,
					'singleSelect' => false,
					'method'        =>'post',
					'ctrlSelect'   => true,
				),
				'fields'=>get_field('SalesTradeLog','sales_trade_log'),
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