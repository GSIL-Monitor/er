<?php
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;

class StatSellbackAnalysisController extends BaseController{
	
	public function getStatSellbackAnalysis($page=1, $rows=20, $search = array(), $sort = 'shop_id', $order = 'desc'){
		if(IS_POST){
			if(empty($search)){
				$search=array(
						'created_start'=>date('Y-m-d').' 00:00:00',
						'created_end'  =>date('Y-m-d').' 23:59:59',
						'stat_typs'	   =>'shop_id',
				);
			}
			$data=D("StatSellbackAnalysis")->getStatSellbackAnalysis($page, $rows, $search, $sort, $order);
			$this->ajaxReturn($data);
		}else{
			$id_list=array();
			$id_list=$this->getIDList($id_list,array('id_datagrid','form','toolbar','tab_container',));
			$datagrid=array(
					'id' => $id_list['id_datagrid'],
                	'style' => '',
                	'class' => '',
                	'options' => array(
                    	'title' => '',
                    	'url' => U('StatSellbackAnalysis/getStatSellbackAnalysis'),
                    	'toolbar' => "#{$id_list['toolbar']}",
                    	'fitColumns'=>false,
                    	'singleSelect'=>true,
                    	'ctrlSelect'=>false,
                	),
					'fields'=>D('Setting/UserData')->getDatagridField('Statistics/StatSellbackAnalysis','stat_sellback_analysis'),
			);
			$params=array(
					'controller'    =>strtolower(CONTROLLER_NAME),
                	'datagrid'  => array("id"  => $id_list["id_datagrid"],),
                	'search'=>array('form_id'=>$id_list['form']),
			);
			$date=array(
					'start' => date('Y-m-d').' 00:00:00',
					'end'   => date('Y-m-d').' 23:59:59',
			);
			$this->assign('date',$date);
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
        try{
            $search = I('get.search','',C('JSON_FILTER'));
            $startnum = strlen('search[');
            $totalnum = strlen('search[]');
            foreach ($search as $k => $v){
                $key=substr($k,$startnum,strlen($k)-$totalnum);
                $search[$key]=$v;
                unset($search[$k]);
            }
            D('StatSellbackAnalysis')->exportToExcel($search);
        } catch (BusinessLogicException $e) {
            $result = array('status'=>1,'info'=> $e->getMessage());
        } catch (\Exception $e) {
            $result = array('status'=>1,'info'=> parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}