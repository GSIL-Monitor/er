<?php
namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

class AdvanceTradeController extends BaseController
{
	public function getAdvanceTradeList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		if(IS_POST)
		{
			$where_sales_trade=' AND st_1.trade_status=25 ';
			$data=D('TradeCheck')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else
		{
			$id_list=array();
			$id_list=$this->getIDList($id_list,array('form','toolbar','tab_container','id_datagrid','edit','add','more_button','more_content','hidden_flag','set_flag','search_flag','dialog_exchange_order','exchange'));
			$url_list=array(
					'turn_check_url'=>U('AdvanceTrade/turnCheck'),//转入审核
			);
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('AdvanceTrade/getAdvanceTradeList', array('grid'=>'datagrid')),
							'toolbar' => "#{$id_list['toolbar']}",
							'frozenColumns'=> D('Setting/UserData')->getDatagridField('Trade/TradeCheck','trade_check',1),
							'fitColumns'=>false,
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields' => D('Setting/UserData')->getDatagridField('Trade/TradeCheck','trade_check'),//get_field('TradeCheck','trade_check')
					);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'goods_list')).'?tab=goods_list&prefix=advanceTrade','title'=>'货品列表'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_detail')).'?tab=trade_detail&prefix=advanceTrade','title'=>'订单详情'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'src_trade')).'?tab=src_trade&prefix=advanceTrade','title'=>'原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'stock_list')).'?tab=stock_list&prefix=advanceTrade','title'=>'库存明细'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'lack_stock_list')).'?tab=lack_stock_list&prefix=advanceTrade','title'=>'缺货明细'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_refund')).'?tab=trade_refund&prefix=advanceTrade','title'=>'退换单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_remark')).'?tab=trade_remark&prefix=advanceTrade','title'=>'备注记录'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_log')).'?tab=trade_log&prefix=advanceTrade','title'=>'订单日志')
			);
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'edit'=>array('id'=>'flag_set_dialog','url'=>U('TradeCheck/editTrade').'?datagrid_id='.$id_list['id_datagrid'].'&exchange='.$id_list['exchange'],'heigth'=>560,'width'=>840,'title'=>'预订单编辑'),
					'add'=>array('id'=>$id_list['add']),
					'exchange'=>array('id'=>$id_list['exchange']),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			$res_cfg=D('Setting/Flag')->getCfgFlags(
					'bg_color,font_color,font_name',
					array('flag_name'=>array('eq','退款'),'flag_class'=>array('eq',1))
			);
			$refund_color='background-color:' . $res_cfg['bg_color'] . ';color:' . $res_cfg['font_color'] . ';font-family:' . $res_cfg['font_name'] . ';';
			$list_form=UtilDB::getCfgRightList(array('shop','logistics'));
			$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=1 AND is_builtin=0 AND is_disabled=0' );
			array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));
			$this->assign('list',$list_form);
			$this->assign('params', json_encode($params));
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign('url_list',$url_list);
			$this->assign('id_list',$id_list);
			$this->assign('datagrid', $datagrid);
			$this->assign('refund_color',$refund_color);
			$this->display('show');
		}
	}
	public function turnCheck($ids){
		$arr_ids_data=is_json($ids);
		$turn_type=I('post.type');//-0转入审核，1强制转入审核。
		$turn_type=intval($turn_type);
		$user_id=get_operator_id();
		$advance_trade_db=D('AdvanceTrade');
		try {
			$data=$advance_trade_db->turnCheck($arr_ids_data,$turn_type,$user_id);
			$result=array(
					'turn'=>$data['turn'],
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
}