<?php
/* 
 * 原始退款单
 */

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;

class OriginalRefundController extends BaseController{
	
	public function getOriginalRefundList ($page = 1, $rows = 20, $search = array(), $sort = 'ar.refund_id', $order ='desc'){
		if(IS_POST){
			$data=D('OriginalRefund')->queryApiRefund($page, $rows, $search, $sort, $order);
			$this->ajaxReturn($data);
		}else{
			$id_list = array();
			$id_list=$this->getIDList($id_list, array('toolbar','id_datagrid','form','more_button','more_content','hidden_flag','tab_container','edit','add','sms_check_code'));
			$datagrid = array(
					'id'      =>$id_list['id_datagrid'],
					'style'   =>'',
					'class'   =>'',
					'options' => array(
							'title'			=> '',
							'url'   		=>U('OriginalRefund/getOriginalRefundList', array('grid'=>'datagrid')),
							'toolbar' 		=> "#{$id_list['toolbar']}",
							'fitColumns'	=>false,
							'frozenColumns'=> D('Setting/UserData')->getDatagridField('Trade/OriginalRefund','original_refund',1),
							'singleSelect'	=>false,
							'ctrlSelect'	=>true,
					),
					'fields' => D('Setting/UserData')->getDatagridField('Trade/OriginalRefund','original_refund'),
			);
			$arr_tabs=array(
					array("id"=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'api_refund_goods')).'?tab=api_refund_goods&prefix=originalRefund','title'=>'货品列表'),
					array("id"=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'ar_sales_refund')).'?tab=ar_sales_refund&prefix=originalRefund&field=Trade/RefundManage','title'=>'处理单'),
					array("id"=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'ar_sales_trade')).'?tab=ar_sales_trade&prefix=originalRefund&field=Trade/Trade','title'=>'系统订单'),
			);
			$params = array(
                	"controller" => strtolower(CONTROLLER_NAME),
					"datagrid"   => array("id" => $id_list["id_datagrid"]),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'edit'=>array('id'=>$id_list['edit']),
					'add'=>array('id'=>$id_list['add']),
					"search"     => array(
							"form_id"      => $id_list["form"],
							"hidden_flag"  => $id_list["hidden_flag"],
							"more_button"  => $id_list["more_button"],
							"more_content" => $id_list["more_content"]
					)
			);
			$right = UtilDB::actionRights(132);
			$operator_id = get_operator_id();
			$shop_list[] = array("id" => "all", "name" => "全部");
			$list_from   = UtilDB::getCfgRightList(array("shop","employee"));
			$list_from['shop']   = array_merge($shop_list,$list_from['shop']);
			$list_from['employee']   = array_merge($shop_list,$list_from['employee']);
			$this->assign("list", $list_from);
			$this->assign('right',json_encode($right));
			$this->assign('operator_id',$operator_id);
			$this->assign("params", json_encode($params));
			$this->assign("id_list", $id_list);
			$this->assign("arr_tabs", json_encode($arr_tabs));
			$this->assign("datagrid", $datagrid);
			$this->display('show');
		}
	}
	
	//递交原始退款单
	public function submitOriginalRefund() {
		$id = I('post.id', '', C('JSON_FILTER'));
		$this->ajaxReturn(D("OriginalRefund")->submitOriginalRefund($id));
	}
	
	public function checkIsSubmit($id){
		$result=0;//0:存在对应系统单且已发货   1：未找到系统单  2：系统单未发货
		try{
			$arr_trade=D('Trade')->getSalesTrade('st.trade_id, st.trade_status',
					array('ar.refund_id'=>array('eq',$id)),
					'st',
					'LEFT JOIN sales_trade_order sto ON sto.trade_id=st.trade_id
					 LEFT JOIN api_refund ar ON ar.tid=sto.src_tid');
			if(empty($arr_trade)){
				$result=1;
			}else if($arr_trade['trade_status']<95){
				$result=2;
			}
		}catch (BusinessLogicException $e){
			$result=1;
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			$result=1;
		}
		$this->ajaxReturn($result);
	}

	public function showSmsCheck()
	{
		$refund_id = I("get.id");
		$params = array(
				'rec_id'      => $refund_id,
				'dialog_id'  => 'originalrefund_sms_check_code',
				'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "check_code" . '_datagrid'),
				'form_id'     => 'refund_check_code',
				'form_url'    => U('OriginalRefund/agreeRefundApi'),
		);
		$this->assign("params", $params);
		$this->display('sms_check_code');
	}

	/*
	 * 原始退款单 同意退款，调用接口 直接退款
	 * */
	public function agreeRefundApi()
	{
		try
		{
			$ids = I('post.id');
			$code = I('post.check_code');
			$sid = get_sid();
			$operator_id = get_operator_id();
			$error_message = '';
			$result['status'] = 0;
			$result['info'] = '退款成功';
			$refundManager = ManagerFactory::getManager("Refund");
			$refundManager->manual_refund_agree($sid,$operator_id,$ids,$code,$error_message);
			if(count($error_message)>0)
			{
				$result['status'] = 2;
				$result['info'] = $error_message;
			}


		}catch (BusinessLogicException $e){
			$result['status'] = 1;
			$result['info'] = $e->getMessage();
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
			$result['status'] = 1;
			$result['info'] = $e->getMessage();
		}
		$this->ajaxReturn($result);

	}
}