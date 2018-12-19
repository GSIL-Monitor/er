<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class TradeDetailController extends BaseController
{
	public function getTradeDetailList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		if(IS_POST)
		{ 	$where_sales_trade=' AND st_1.trade_status <= 110 ';
			$data=D('TradeDetail')->queryTradeDetail($where_sales_trade,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else 
		{
			$id_list=array();
			$this->getIDList($id_list,array("form","toolbar","id_datagrid","hidden_flag","more_button","more_content","edit","set_flag","search_flag"));
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('TradeDetail/getTradeDetailList', array('grid'=>'datagrid')),
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
					'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			try
			{			
				$id_list['goods_amount_sum']=D('TradeDetail')->getGoodsAmount();
			}catch(BusinessLogicException $e)
			{
				$id_list['goods_amount_sum']=0;
			}
			//获取显示打印状态配置
			$print_status = get_config_value(array('stockout_sendbill_print_status','stockout_logistics_print_status'),array(0,0));
			$list_form=UtilDB::getCfgRightList(array('shop','logistics','reason','warehouse'),array('reason'=>array('class_id'=>array('eq',1))));
			$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=1 AND is_builtin=0 AND is_disabled=0' );
			array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));
			$this->assign("list",$list_form);
			$this->assign('print_status',$print_status);
			$this->assign("params",json_encode($params));
			$this->assign("id_list",$id_list);
			$this->assign('datagrid', $datagrid);
			$this->display('show');
		}
	}

	public function getGoodsAmount(){
		try{
			$search = I('get.search','',C('JSON_FILTER'));
            foreach ($search as $k => $v)
            {
            	$key=substr($k,7,strlen($k)-8);
            	$search[$key]=$v;
            	unset($search[$k]);
            }
			$goods_amount_sum=D('TradeDetail')->getGoodsAmount($search);			
		}catch (BusinessLogicException $e){
			$goods_amount_sum=0;
		}catch (Exception $e){
			$goods_amount_sum=0;
		}
		$this->ajaxReturn($goods_amount_sum);
	}

	public function exportToExcel(){
//		if(!self::ALLOW_EXPORT){
//			echo self::EXPORT_MSG;
//			return false;
//		}
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try
        {
            if($id_list=='')
            {
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v)
                {
                	$key=substr($k,7,strlen($k)-8);
                	$search[$key]=$v;
                	unset($search[$k]);
                }
                D('TradeDetail')->exportToExcel('',$search,$type);
            }else{
                D('TradeDetail')->exportToExcel($id_list,array(),$type);
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