<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;

class TradeCommonController extends BaseController
{
	public function showTabsView($tab,$prefix,$field='Trade/TradeCommon')
	{
		$arr_tab=array(
				//订单tabs
				'goods_list'=>'goods_list',
				'trade_detail'=>'trade_detail',
				'src_trade'=>'src_trade',
				'stock_list'=>'stock_list',
				'lack_stock_list'=>'lack_stock_list',
				'trade_refund'=>'trade_refund',
				'trade_merge'=>'trade_merge',
				'trade_remark'=>'trade_remark',
				'trade_log'=>'log',
				//退货单tabs
				'refund_order'=>'refund_order',
				'api_order'=>'api_order',
				'api_trade'=>'src_trade',
				'sales_trade'=>'sales_trade',
				'swap_api_trade'=>'src_trade',//换出原始单
				'refund_log'=>'log',
				//订单-选择tabs
				'order_list'=>'order_list',
				//销售出库-tabs
    		    'sales_stockout_log'=>'log',
    		    'sales_order_detail'=>'trade_detail',
    		    //财务审核-tabs
    		    'fc_reason'=>'fc_reason',
				//原始退款单-tabs
				'api_refund_goods'=>'api_refund_goods',
				'ar_sales_refund'=>'refund_manage',
				'ar_sales_trade'=>'trade_manage',
		);
		if(empty($arr_tab[$tab]))
		{
			\Think\Log::write('TradeCommon->联动未知的tabs:'.$tab);
			return null;
		}
		$tab_view='tabs_trade_common';
		$datagrid=array(
				'id'=>$prefix.'datagrid_'.$tab,
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
						'title'=>'',
						'pagination'=>false,
						'fitColumns'=>false,
				),
// 				'fields' => get_field($field, $arr_tab[$tab])
				'fields' => D('Setting/UserData')->getDatagridField($field,$arr_tab[$tab]),//get_field('TradeCheck','trade_check')
		);
		if($arr_tab[$tab]=='trade_detail')
		{
			$datagrid['options']=array( 'title'=>'', 'pagination'=>false, 'fitColumns'=>false, 'rownumbers'=>false, 'showHeader'=>false, 'methods'=>'onLoadSuccess:onLoadSuccessTradeDetail');
			$tab_view='tabs_trade_detail';
		}else if($arr_tab[$tab]=='goods_list'){
			$datagrid['options']['methods']='rowStyler:editTradeRowStyler';
		}else{
			if(count($datagrid['fields'])<12)
			{
				$datagrid['options']['fitColumns']=true;
			}
			if($arr_tab[$tab]=='order_list')
			{
				$datagrid['options']['singleSelect']=false;
				$datagrid['options']['ctrlSelect']=true;
			}
		}
		$this->assign('datagrid',$datagrid);
		$this->display($tab_view);
	}
	
	public function updateTabsData($id,$datagridId)
	{
		$data=array();
		if(($id=intval($id))==0)
		{//过滤非法字符（非数字字符串经过intval()方法后自动转换成0）和屏蔽第一次请求
			$data=array('total'=>0,'rows'=>array());
			$this->ajaxReturn($data);
		}
		$tab=substr($datagridId, strpos($datagridId, '_')+1);//得到tab
		switch ($tab)
		{
			case 'goods_list':{//订单tab
				$data=D('Trade')->getGoodsList($id);
				break;
			}
			case 'trade_detail':{
				$data=D('Trade')->getTradeDetail($id);
				break;
			}
			case 'src_trade':{
				$data=D('Trade')->getApiTrade($id);
				break;
			}
			case 'stock_list':{
				$data=D('Trade')->getStockList($id);
				break;
			}
			case 'lack_stock_list':{//缺货明细
				$data=D('Trade')->getStockList($id,1);
				break;
			}
			case 'trade_refund':{
				$data=D('Trade')->getTradeRefund($id);
				break;
			}
			case 'trade_merge':{
				$data=D('Trade')->getUnmergeTrade($id);
				break;
			}
			case 'trade_remark':{
				$data=D('Trade')->getTradRemark($id);
				break;
			}
			case 'trade_log':{
				$data=D('Trade')->getTradeLog($id);
				break;
			}
			case 'refund_order':{//退换单tab
				$data=D('RefundManage')->getRefundOrder($id);
				break;
			}
			case 'api_order':{
				$data=D('RefundManage')->getApiOrder($id);
				break;
			}
			case 'api_trade':{
				$data=D('Trade')->getApiTrade($id,'refund');
				break;
			}
			case 'sales_trade':{
				$data=D('RefundManage')->getSalesTrade($id);
				break;
			}
			case 'swap_api_trade':{
				$data=D('RefundManage')->getSwapApiTrade($id);
				break;
			}
			case 'refund_log':{
				$data=D('RefundManage')->getRefundLog($id);
				break;
			}
			case 'order_list':{//订单-选择
				$data=D('Trade')->getGoodsList($id);
				if($data['total']==0){
					$data=D('Trade')->getGoodsList($id,'history');
				}
				break;
			}
			case 'sales_order_detail':{//销售出库
			    $data = D('Trade/Trade')->getTradeDetail($id,2);
			    break;
			}
			case 'sales_stockout_log':{//销售出库
			    $data = D('Trade/Trade')->getTradeLog($id,"sales_stockout");
			    break;
			}
			case 'fc_reason':{//财务审核
				$data = D('TradeCheck')->getFinancialCheckReason($id);
				break;
			}
			//原始退款单
			case 'api_refund_goods':{
				$data = D('OriginalRefund')->getApiRefundGoods($id);
				break;
			}
			case 'ar_sales_refund':{
				$data = D('OriginalRefund')->getSalesRefund($id);
				break;
			}
			case 'ar_sales_trade':{
				$data = D('OriginalRefund')->getSalesTrade($id);
			}
			default:{
				$data=array('total'=>0,'rows'=>array());//没有数据请求的tabs返回空
				break;
			}
		}
		$this->ajaxReturn($data);
	}
	
	public function checkNumber($ids,$key='sales_trade')
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try {
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			if($cfg_show_telno==0)
			{
				E('未开启手机号码显示成星号，不需要查看号码');
			}
			$data=UtilDB::checkNumber($arr_ids_data, $key,$user_id,null,0);
			$result=array(
					'status'=>$data['status'],
					'check_number'=>true,
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
			);
		} catch (\Exception $e) {
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function checkNumberRight($key='sales_trade')
	{
		$user_id=get_operator_id();
		try {
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			if($cfg_show_telno==0)
			{
				$this->success();
			}
			$right_flag=UtilDB::checkNumber(array(0), $key,$user_id,null,2);
			if($right_flag==false)
			{
				E('您暂无此操作权限，请联系管理员。');
			}
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		$this->success();
	}
}