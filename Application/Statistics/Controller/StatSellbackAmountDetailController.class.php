<?php
/**
 * 售后退款金额明细的控制器
 */
namespace Statistics\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
class statSellbackAmountDetailController extends BaseController
{
   
    public function getstatSellbackAmountDetail($page=1, $rows=20, $search = array(), $sort = 'modified', $order = 'desc')
    {
    	if(IS_POST){
    		if(empty($search)){
    			$search=array(
    					'created_start'=>date('Y-m').'-01',
                        'created_end'=>date('Y-m-d')
    			);
    		}
    		$data=D("statSellbackAmountDetail")->getstatSellbackAmountDetail($page, $rows, $search, $sort, $order);
    		$this->ajaxReturn($data);
    	}else{
    		$id_list=$this->getIDList($id_list,array('id_datagrid','form','toolbar','tab_container','more_button','more_content','hidden_flag','dailystat_end'));
    		$fields = D('Setting/UserData')->getDatagridField('Statistics/statSellbackAmountDetail','stat_sellback_amount_detail');
    		$datagrid=array(
					'id' => $id_list['id_datagrid'],
                	'style' => '',
                	'class' => '',
                	'options' => array(
                    	'title' => '',
                    	'url' => U('statSellbackAmountDetail/getstatSellbackAmountDetail'),
                    	'toolbar' => "#{$id_list['toolbar']}",
                    	'fitColumns'=>false,
                    	'singleSelect'=>true,
                    	'ctrlSelect'=>false,
                	),
					'fields'=>D('Setting/UserData')->getDatagridField('Statistics/statSellbackAmountDetail','stat_sellback_amount_detail'),
			);
    		$params=array(
    				'controller'    =>strtolower(CONTROLLER_NAME),
    				'datagrid'  => array("id"  => $id_list["id_datagrid"],),
    				'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
    		);
    		$date=array(
    				'start' => date('Y-m').'-01',
    				'end'   => date('Y-m-d')
    		);
    		$list_form=UtilDB::getCfgRightList(array('shop'));
    		$this->assign('date',$date);
    		$this->assign('id_list',$id_list);
    		$this->assign('list',$list_form);
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
            D('StatSellbackAmountDetail')->exportToExcel($search);
        } catch (BusinessLogicException $e) {
            $result = array('status'=>1,'info'=> $e->getMessage());
        } catch (\Exception $e) {
            $result = array('status'=>1,'info'=> parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}