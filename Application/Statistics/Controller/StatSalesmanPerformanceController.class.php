<?php
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception;
class StatSalesmanPerformanceController extends BaseController{
	public function getStatSalesmanPerformance($page=1,$rows=20,$search=array(),$sort="",$order="desc"){
		if(IS_POST){
			try{
				if(empty($search)){
					$date=date('Y-m-d', strtotime('-1 days'));
					$search=array(
							'start_time'=>date('Y-m-d', strtotime('-1 days')),
							'end_time'=>date('Y-m-d'),
					);
				}
				$data=D("StatSalesmanPerformance")->loadDataByCondition($page,$rows,$search,$sort,$order);
			}catch (\Exception $e){
				$data=array('total'=>0,'rows'=>array());
			}
			$this->ajaxReturn($data);
		}else{
			$id_list=array();
			$id_list=$this->getIDList($id_list,array('toolbar','id_datagrid','form','tab_container','edit','help_id'));
			$datagrid=array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=>array(
							'title'=>'',
							'url'=>U('StatSalesmanPerformance/getStatSalesmanPerformance'),
							'toolbar' => "#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields'=>get_field('Statistics/StatSalesmanPerformance','stat_salesman_performance'),
			);
			$params=array(
					'controller'=>strtolower(CONTROLLER_NAME),
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('form_id'=>$id_list['form']),
					'help' => array(
							'id' => $id_list['help_id'],
							'url' => U('StatisticsCommon/getHelpInfo?type=stastics_performance')
					)
			);
			$list_form=UtilDB::getCfgList(array('shop','brand'),array('brand'=>array('is_disabled'=>array('eq',0))));
			$date=array(
					'start'=>date('Y-m-d', strtotime('-1 days')),
					'end'=>date('Y-m-d'),
			);
			$this->assign('date',$date);
			$this->assign('list',$list_form);
			$this->assign('id_list',$id_list);
			$this->assign('goods_brand', $list_form['brand']);
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
		try{
			$search = I('get.search','',C('JSON_FILTER'));
			$startnum = strlen('search[');
			$endnum = strlen('search[]');
			foreach ($search as $k => $v) {
				$key=substr($k,$startnum,strlen($k)-$endnum);
				$search[$key]=$v;
				unset($search[$k]);
			}
	
			D('StatSalesmanPerformance')->exportToExcel($search);
		}
	
		catch (BusinessLogicException $e){
			$result = array('status'=>1,'info'=> $e->getMessage());
		}catch (\Exception $e) {
			$result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
		}
		echo $result['info'];
	}
}