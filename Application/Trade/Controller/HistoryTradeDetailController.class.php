<?php
// 历史订单明细
namespace Trade\Controller;
use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;
use Think\Log;
class HistoryTradeDetailController extends BaseController {
    public function getHistoryTradeDetailList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc'){
        if(IS_POST){
			$where_sales_trade_history=' AND sth_1.trade_status <= 110 ';
			// 判断是否含有start_time值,首次加载时显示
			if(!$search['start_time']){
				$search['start_time'] = date('Y-m-d',strtotime('-6 months'));
				$search['end_time']= date('Y-m-d',strtotime('-3 months'));
			}
			$data=D('HistoryTradeDetail')->queryHistoryTradeDetail($where_sales_trade_history,$page,$rows,$search,$sort,$order);
            $this->ajaxReturn($data);
        }else{
            $id_list=array();
			$this->getIDList($id_list,array("form","toolbar","id_datagrid","hidden_flag","more_button"));
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('HistoryTradeDetail/getHistoryTradeDetailList', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/TradeDetail','trade_detail',1),
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields'=>D('Setting/UserData')->getDatagridField('Trade/TradeDetail','trade_detail')
            );
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array(
						'form_id'		=>$id_list['form'],	
						'more_button'	=>$id_list['more_button'],
						'more_content'	=>$id_list['more_content'],
						'hidden_flag'	=>$id_list['hidden_flag']
					),
					'flag'=>array(
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
					),
			);
			$list_form=UtilDB::getCfgRightList(array('shop'));
			$trade_time['start_time'] = date('Y-m-d',strtotime('-6 months'));
			$trade_time['end_time'] = date('Y-m-d',strtotime('-3 months'));
			$this->assign('trade_time',$trade_time);
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign("id_list",$id_list);
			$this->assign('datagrid', $datagrid);
            $this -> display('show');
        }
	}
	public function exportToExcel(){
        $id_list = I('get.id_list');
        $type = I('get.type');
		$result = array('status'=>0,'info'=>'');
        try
        {
            if($id_list=='')
            {
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v)	//重新更新key,value
                {
                	$key=substr($k,7,strlen($k)-8);	//从[search[trade_no]]中获取 trade_no
                	$search[$key]=$v;
                	unset($search[$k]);
				}
                D('HistoryTradeDetail')->exportToExcel('',$search,$type);
            }else{
                D('HistoryTradeDetail')->exportToExcel($id_list,array(),$type);
			}
			
        }
        catch (BusinessLogicException $e)
        {
            $result = array('status'=>1,'info'=> $e->getMessage());
        }
        catch (\Exception $e) 
        {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}