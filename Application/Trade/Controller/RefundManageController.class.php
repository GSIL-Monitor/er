<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;
class RefundManageController extends BaseController
{
	public function getSalesRefundList($page=1, $rows=20, $search = array(), $sort = 'refund_id', $order = 'desc')
	{
		
		if(IS_POST)
		{ //sales_refund_query
			$where_sales_refund_str='';//不同初始化搜索条件
			$data=D('RefundManage')->queryRefund($where_sales_refund_str,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else 
		{
			$id_list=array(
					'toolbar'=>'sales_refund_toobbar',
					'tab_container'=>'sales_refund_tab_container',
					'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
					'form'=>'sales_refund_form',
					'edit'=>'sales_refund_edit',
					'add'=>'sales_refund_add',
					'more_button'=>'sales_refund_more_button',
					'more_content'=>'sales_refund_more_content',
					'hidden_flag'=>'sales_refund_hidden_flag',
					'set_flag'=>'sales_refund_set_flag',
					'search_flag'=>'sales_refund_search_flag',//标记作为搜索条件,不作为搜索条件不写
					'exchange'=>'refund_dialog_exchange_order',
			);
			$url_list=array(
					'edit_url'=>U('RefundManage/editRefund'),//
					'agree_url'=>U('RefundManage/agreeRefund'),
					'reject_url'=>U('RefundManage/rejectRefund'),
					'cancel_url'=>U('RefundManage/cancelRefund'),
					'stockin_url'=>U('RefundManage/refundStockin'),
			);
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('RefundManage/getSalesRefundList', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'frozenColumns'=> D('Setting/UserData')->getDatagridField('Trade/RefundManage','refund_manage',1),
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields' => D('Setting/UserData')->getDatagridField('Trade/RefundManage','refund_manage'),//get_field('RefundManage','refund_manage')
			);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'refund_order')).'?tab=refund_order&prefix=refundManage','title'=>'系统货品'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'api_order')).'?tab=api_order&prefix=refundManage','title'=>'平台货品'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'api_trade')).'?tab=api_trade&prefix=refundManage','title'=>'原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'sales_trade')).'?tab=sales_trade&prefix=refundManage','title'=>'系统订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'swap_api_trade')).'?tab=swap_api_trade&prefix=refundManage','title'=>'换出原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'refund_log')).'?tab=refund_log&prefix=refundManage','title'=>'退款单日志')
			);
			
			$arr_flag=D('Setting/Flag')->getFlagData(9);
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/updateTabsData')),
					'edit'=>array('id'=>$id_list['edit'],'url'=>$url_list['edit_url'],'title'=>'退换单编辑','height'=>510,'width'=>1000),
					'add'=>array('id'=>$id_list['add'],'height'=>510,'width'=>1000),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=9',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=9','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
					'revert_reason'=>array(
						'id'=>'flag_set_dialog',
						'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=1&model_type=refundManage',
						'title'=>'驳回原因',
						'width'=>400,
						'height'=>'auto',
						'form' =>array('url'=>U("Trade/RefundManage/revertCheck"),'id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox','dialog_type'=>'refundmanage')
					),
			);
			$list_form=UtilDB::getCfgRightList(array('shop','logistics'));
			$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=9 AND is_builtin=0 AND is_disabled=0' );
			array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign("id_list",$id_list);
			$this->assign('url_list',$url_list);
			$faq_url=C('faq_url');
			$this->assign('faq_url',$faq_url['return']);
			$this->assign('datagrid', $datagrid);
			$this->display('show');
		}
	}
	
	/**
	 * 验证--子订单是否已退款 
	 * @param string $ids json
	 */
	public function checkIsRefund($ids)
	{
		$arr_ids_data=is_json($ids);
        $db_refund_order=D('SalesRefundOrder');
		$res_refund_total=$db_refund_order->getSalesRefundOrderList(
					'COUNT(refund_order_id) AS total',
					array('trade_order_id'=>array('in',$arr_ids_data))
		);
        if($res_refund_total[0]['total']>0)
		{
			$this->error();
		}else{
			$this->success();
		}
	}
	
	/**
	 * 编辑--新增
	 * @param int $id
	 */
	public function editRefund($id,$is_api=0)
	{
		$id=intval($id);
		$user_id=get_operator_id();
		if(IS_POST){
			$arr_form_data=I('post.info','',C('JSON_FILTER'));
			$arr_refund_order=I('post.refund_order','',C('JSON_FILTER'));
			$arr_return_order=I('post.return_order','',C('JSON_FILTER'));
			$is_api=I('post.is_api','',C('JSON_FILTER'));
			if($arr_form_data['type']==3||$arr_form_data['type']==5)
			{
				$arr_return_data=I('post.return_info','',C('JSON_FILTER'));
				$arr_form_data['shop_id']=$arr_return_data['shop_id'];
				$arr_form_data['swap_warehouse_id']=$arr_return_data['swap_warehouse_id'];
				$arr_form_data['goods_return_count']=$arr_return_data['goods_return_count'];
				unset($arr_return_data);
			}
			var_dump($arr_form_data);die;

			if($is_api!=2&&$id>0)
			{
				$arr_form_data['refund_id']=$id;
			}
			$refund_db=D('RefundManage');
			try {
				$refund_db->validateRefund($arr_form_data);

				/*if(!$refund_db->validate($refund_db->getRules())->create($arr_form_data))
				{
					$this->error($refund_db->getError());
				}*/
				//查看修改号码权限
				$cfg_show_telno=get_config_value('show_number_to_star',1);
				if(($arr_form_data['type']==3||$arr_form_data['type']==5) && !UtilDB::checkNumber(array($id), 'sales_refund', $user_id,null,2) && $cfg_show_telno==1){
					unset($arr_form_data['swap_mobile']);
					unset($arr_form_data['swap_telno']);
				}
				if($id>0){//编辑
					$refund_db->updateRefund($arr_form_data,$arr_refund_order,$arr_return_order,$user_id);
				}else{//新增
					$add_result=$refund_db->addRefund($arr_form_data,$arr_refund_order,$arr_return_order,$user_id,$is_api);
					$res_cfg_value=get_config_value('refund_auto_agree');
					if($arr_form_data['type']!=5&&$res_cfg_value==1){//开启了自动同意配置
					 	$this->ajaxReturn($add_result);
					}
				}
			}catch (BusinessLogicException $e){
				$this->error($e->getMessage());
			}catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			$this->success();
		}else{
			$refund_db=D('RefundManage');
			try {
				if ($is_api==1) {
					$prefix='api';$dialog_id='originalrefund_add';
				}elseif ($is_api==2) {
					$prefix='manage';$dialog_id='trade_manage_add';
				}else{
					$prefix='refund';$dialog_id='sales_refund_add';
				}
				// $is_api==1?$prefix='api':$prefix='refund';
				$id_list=array(
						'id_datagrid_refund'=>$prefix.'_editrefund_datagrid_refund',
						'id_datagrid_return'=>$prefix.'_editrefund_datagrid_return',
						'toolbar_refund'=>$prefix.'_toolbar_refund',
						'toolbar_return'=>$prefix.'_toolbar_return',
						'tab_container'=>$prefix.'_tab_container',
						'form_id'=>$prefix.'_form',
						'more_content_logistics'=>$prefix.'_more_content_logistics',
						'more_content_info'=>$prefix.'_more_content_info',
						'dialog_id'=>$dialog_id,
						'return_form'=>$prefix.'_return_form',
						'province'=>$prefix.'_refund_province',
						'city'=>$prefix.'_refund_city',
						'district'=>$prefix.'_refund_district',
						'url_exchange'=>U('RefundManage/exchangeRefundGoods'),
				);
                $add_sp_order_dialog = array('title'=>'其他入库单查询','id'=>'flag_set_dialog','url'=>U('Stock/StockInManagement/refundLinkSpOrder'),'height'=>'560','width'=>'1100');
				$list_form=UtilDB::getCfgRightList(array('shop','logistics','warehouse','reason'),array('logistics'=>array('is_disabled'=>array('eq',0)),'warehouse'=>array('is_disabled'=>array('eq',0)),'reason'=>array('class_id'=>array('eq',4),'is_disabled'=>array('eq',0))));
				$list_form['flags']=D('Setting/Flag')->getFlagData(9,'list');
				$datagrid=$refund_db->getDialogView('refund_edit',$id_list);
				$refund_data=array();
				//查看号码权限---用于判断号码可否编辑	
				$cfg_show_telno=get_config_value('show_number_to_star',1);
				$id_list['right_flag'] = 1;
				if($cfg_show_telno==1){
					$right_flag=UtilDB::checkNumber(array($id), 'sales_refund', $user_id);
					$id_list['right_flag'] = $right_flag==false?0:1;
				}
				//分类处理
				if($id>0&&$is_api==0){//编辑
					$refund_data['refund']=$refund_db->getSalesRefund(
							'r.refund_id,r.stockin_pre_no,r.refund_no,r.src_no,r.platform_id,r.shop_id,r.type, r.status,r.pay_account,r.pay_no,r.actual_refund_amount,r.goods_amount,r.paid, r.exchange_amount,r.post_amount,r.process_status,r.warehouse_type,r.outer_no, r.refund_amount,r.tid,r.trade_no,r.trade_id,r.logistics_name,r.logistics_no,r.buyer_nick, r.reason_id,r.flag_id,r.remark,r.swap_receiver,r.swap_mobile,r.swap_telno,r.swap_address, r.swap_province,r.swap_city,r.swap_district,r.swap_warehouse_id,r.swap_trade_id,r.swap_logistics_id,r.warehouse_id,r.customer_id, r.direct_refund_amount,r.guarante_refund_amount,r.shop_id as shop,st.trade_from',
							array('r.refund_id'=>array('eq',$id)),
							'r','LEFT JOIN sales_trade st ON st.trade_no = r.trade_no'
					);

					if($refund_data['refund']['type']==3&&$refund_data['refund']['refund_amount']<0)
					{
						$refund_data['refund']['flow_type']=2;
					}
					$res_refund_orders=D('SalesRefundOrder')->getSalesRefundOrderList(
							'sro.refund_order_id,sro.platform_id,sro.oid,sro.tid,sro.trade_id,sro.trade_order_id,sro.trade_no, sro.order_num,sro.price share_price,sro.original_price,sro.discount,sro.paid,sro.refund_num,sro.total_amount, sro.goods_id,gg.goods_no,sro.goods_name,sro.spec_id,sro.goods_name,sro.spec_name,sro.spec_no,sro.suite_id,sro.suite_name, sro.suite_num,sro.stockin_num,sro.remark',
							array('sro.refund_id'=>array('eq',$id)),
							'sro',
							'LEFT JOIN goods_goods gg ON sro.goods_id = gg.goods_id'
					);
					$refund_data['refund_data']=array('total'=>count($res_refund_orders),'rows'=>$res_refund_orders);
					if($refund_data['refund']['type']==3||$refund_data['refund']['type']==5)
					{
						$res_return_orders=D('SalesRefundOutGoods')->getSalesRefundOutGoodsList(
								"sg.rec_id,(sg.target_type=2) is_suite,sg.target_type,sg.target_id,sg.goods_name,IF(sg.target_type=2,'',gg.goods_no) goods_no,sg.spec_name,sg.merchant_no,sg.num,sg.remark,sg.retail_price",
								array('sg.refund_id'=>array('eq',$id)),
								'sg',
								'LEFT JOIN goods_spec gs ON gs.spec_id = sg.target_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id'
						);
						$refund_data['return_data']=array('total'=>count($res_return_orders),'rows'=>$res_return_orders);
						$id_list['type'] = 3;
						//号码是否显示为星号
						if ($id_list['right_flag']==0){
							$refund_data['refund']['swap_mobile']=empty($refund_data['refund']['swap_mobile'])?$refund_data['refund']['swap_mobile']:substr_replace($refund_data['refund']['swap_mobile'],'****',3,4);
							$refund_data['refund']['swap_telno']=empty($refund_data['refund']['swap_telno'])?$refund_data['refund']['swap_telno']:substr_replace($refund_data['refund']['swap_telno'],'****',3,4);
						}
					}
				}else if($id>0&&$is_api==1){//原始退款单递交
					$refund_data['refund']=D('OriginalRefund')->getApiRefund(
							"ar.refund_no AS src_no, ar.platform_id, ar.shop_id, ar.type, ar.pay_account, ar.pay_no, ar.refund_amount , ar.actual_refund_amount AS goods_amount, ar.remark , 
							 ar.logistics_name, ar.logistics_no, ar.buyer_nick, ar.tid, st.trade_no, ar.actual_refund_amount AS guarante_refund_amount, st.trade_id   ",
							array('refund_id'=>array('eq',$id)),
							"ar",
							"LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid 
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id");
					$refund_data['refund']['refund_id']=0;
					$refund_data['refund']['swap_province']=0;
					$refund_data['refund']['swap_city']=0;
					$refund_data['refund']['swap_district']=0;
					$res_refund_orders=D('SalesTradeOrder')->getSalesTradeOrderList(
							'sto.platform_id, sto.src_oid AS oid, sto.src_tid AS tid, sto.trade_id, sto.rec_id AS trade_order_id, st.trade_no, 
							sto.actual_num AS order_num, sto.share_price, sto.price AS original_price, sto.discount, sto.paid, aro.num AS refund_num, 
							sto.goods_id, sto.goods_name, sto.goods_no, sto.spec_id, sto.spec_name, sto.spec_no, sto.suite_id, sto.suite_name, 
							sto.suite_num, sto.order_price*aro.num AS total_amount,sto.rec_id, 
							st.receiver_province AS swap_province, st.receiver_city AS swap_city, st.receiver_district AS swap_district ',
							array('ar.refund_id'=>array('eq',$id)),
							'sto',
							'LEFT JOIN api_refund ar ON ar.tid=sto.src_tid 
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id 
							 LEFT JOIN api_refund_order aro ON aro.shop_id=ar.shop_id AND aro.refund_no=ar.refund_no');
					$refund_data['refund_data']=array('total'=>count($res_refund_orders),'rows'=>$res_refund_orders);
					var_dump($refund_data);die;
				}elseif($id>0&&$is_api==2){//订单管理页面新建退换单
					// 通过子订单id获取订单的原始单号
					$sto_ids=I('get.sto_ids'); 
					$refund_data['refund']=D("Trade")->getSalesTrade(
						 'st.trade_id,st.trade_no,st.buyer_nick,st.receiver_name as swap_receiver,st.receiver_country,
						  st.receiver_province as swap_province,st.receiver_city as swap_city,st.receiver_district as swap_district,
						  st.receiver_mobile as swap_mobile,st.receiver_telno as swap_telno,st.receiver_address as swap_address,st.trade_from',
						  array('st.trade_id'=>array('eq',$id)),
						  'st','');
					$refund_data['refund']['type']=$refund_data['refund']['trade_from']== 4 ? '2' : '3';
					$refund_data['refund']['direct_refund_amount']=0.0000;
					$refund_data['refund']['swap_warehouse_id']=1;
					$refund_data['refund']['shop_id']=1;					
					$res_sales_orders=D("SalesTradeOrder") ->getSalesTradeOrderList(
						    'sto.platform_id,sto.src_oid AS oid, sto.src_tid AS tid,sto.trade_id, sto.rec_id AS trade_order_id,st.trade_no,
						    sto.actual_num AS order_num, sto.share_price, sto.price AS original_price, sto.discount, sto.paid,sto.num AS refund_num,
						    sto.goods_id, sto.goods_name, sto.goods_no, sto.spec_id, sto.spec_name, sto.spec_no, sto.suite_id, sto.suite_name, 
						    sto.suite_num, sto.order_price*sto.num AS total_amount,sto.rec_id',
						     array('sto.trade_id'=>array('eq',$id),'sto.rec_id in '.$sto_ids),
						     'sto',
						     'LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id');
					$refund_data['refund_data']=array('total'=>count($res_sales_orders),'rows'=>$res_sales_orders);
					$refund_data['refund']['goods_amount']=0;
					$refund_data['refund']['tid']='';$tid='';
					foreach ($res_sales_orders as $v) {
						$refund_data['refund']['goods_amount']+=$v['share_price']*$v['refund_num'];
						$tid.=$v['tid'].',';
					}
					$refund_data['refund']['tid']=rtrim($tid,',');					
					$refund_data['refund']['exchange_amount']=$refund_data['refund']['goods_amount'];
					//号码是否显示为星号
					if ($id_list['right_flag']==0){
						$refund_data['refund']['swap_mobile']=empty($refund_data['refund']['swap_mobile'])?$refund_data['refund']['swap_mobile']:substr_replace($refund_data['refund']['swap_mobile'],'****',3,4);
						$refund_data['refund']['swap_telno']=empty($refund_data['refund']['swap_telno'])?$refund_data['refund']['swap_telno']:substr_replace($refund_data['refund']['swap_telno'],'****',3,4);
					}
				}else{//新建
					$refund_data['refund']=array('refund_id'=>0,'type'=>'3');
				}
				$id_list['refund_id']=$refund_data['refund']['refund_id'];
				$this->assign('list',$list_form);
				$this->assign('add_sp_order_dialog',$add_sp_order_dialog);
				$this->assign('id_list',$id_list);
				$this->assign('datagrid', $datagrid);
				$this->assign('is_api',$is_api);
				$this->assign('refund_data', json_encode($refund_data));
			}catch (BusinessLogicException $e){
				$this->assign('message',$e->getMessage());
				$this->display('Common@Exception:dialog');
				exit();
			}catch (\Exception $e){
				$this->assign('message',$e->getMessage());
				$this->display('Common@Exception:dialog');
				exit();
			}
			$this->display('dialog_refund_add_edit');
		}
	}
	
	/**
	 * 操作功能
	 */
	
	/**
	 * 同意--审核
	 * @param string $ids
	 */
	public function agreeRefund($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('RefundManage')->agreeRefund($arr_ids_data,$user_id);
			$result=array(
					'is_refresh'=>true,
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Think\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
    /**
     * 同时审核时验证--子订单是否已退款
     * @param string $ids json
     */
	public function checkAgree($ids){

        $arr_ids_data=is_json($ids);
        $trade_order_arr = array();
        $trade_order_ids = D('SalesRefundOrder')->field('trade_order_id')->where(array('refund_id'=>array('in',$arr_ids_data)))->select();
        foreach ($trade_order_ids as $tid){
            $trade_order_arr[] = $tid['trade_order_id'];
        }

        $db_refund_order=D('SalesRefundOrder');
        $res_refund_total=$db_refund_order->getSalesRefundOrderList(
            'COUNT(refund_order_id) AS total',
            array('trade_order_id'=>array('in',$trade_order_arr))
        );
        if($res_refund_total[0]['total']>1)
        {
            $this->error();
        }else{
            $this->success();
        }
    }
	/**
	 * 拒绝
	 * @param string $ids
	 */
	public function rejectRefund($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('RefundManage')->rejectRefund($arr_ids_data,$user_id);
			$result=array(
					'is_refresh'=>true,//用于前端标识
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Think\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	/**
	 * 取消
	 * @param string $ids
	 */
	public function cancelRefund($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('RefundManage')->cancelRefund($arr_ids_data,$user_id);
			$result=array(
					'process_status'=>10,
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Think\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	/**
	 * 驳回
	 * @param string $ids
	 */
	public function revertCheck($ids)
	{
		$result = array();
		$status=0;
		$fail = array();
		$success = array();
		$params = I('','',C('JSON_FILTER'));
		$form_params = $params['form'];
		$ids = $params['ids'];
		$revert_reason='';
		if(empty($ids))
		{
			$result['message'] ="请选择订单";
			$result['status'] = 1;
			$this->ajaxReturn($result);
		}
		$form_params['is_force'] = 0;//强制驳回标记
		$form_params['operator_id'] = get_operator_id();
		if (empty( $form_params['reason_id']) || (int) $form_params['reason_id'] == 0)
		{
			$result['message'] ="无效的原因";
			$result['status'] = 1;
			return $this->ajaxReturn($result);
		}
		$refundManage_ids = explode(",", $ids);
		foreach ($refundManage_ids as $key=>$id)
		{
			$success[$key] = array();
			D('Trade/RefundManage')->revertCheck($id,$form_params,$fail,$success[$key]);
			$revert_reason=$success[$key]['revert_reason'];
			if(empty($success[$key]))
			{
				unset($success[$key]);
			}
		}
		if (!empty($fail))
		{
			$status= 2;
			$revert_reason='';
		}
		$result=array(
			'process_status'=>20,
			'revert_reason'=>$revert_reason,
			'status'=>$status,
			'info'=>array('total'=>count($fail),'rows'=>$fail),//失败提示信息
			'data'=>array('total'=>count($success),'rows'=>$success),//成功的数据
		);
		$this->ajaxReturn($result);
	}
	
	public function getCustomerAddress($id){
		$id=is_json($id);
		$user_id=get_operator_id();
		try{
			$result=D('Trade')->getSalesTrade('receiver_name,receiver_province,receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno',
					array('trade_id'=>array('eq',$id)));
			if(empty($result)){
				$result=D('HistorySalesTrade')->getHistoryTrade('receiver_name,receiver_province,receiver_city,receiver_district,receiver_address,receiver_mobile,receiver_telno',
						array('trade_id'=>array('eq',$id)));
			}
			//验证查看号码权限，显示星号
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$result['right_flag'] = 0;
			if($cfg_show_telno==1&&!UtilDB::checkNumber(array($id), 'sales_refund', $user_id,null,2)){
				$result['right_flag'] = 1;
			}
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch(\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function refundStockin($id){
		$id=is_json($id);
		$user_id=get_operator_id();
		$result=array();
		$result['status']=0;
		try{
			D('RefundManage')->refundStockIn($id,$user_id);
		}catch (\Think\Exception\BusinessLogicException $e){
			$result['status']=1;
			$result['info']=$e->getMessage();
		}catch (Exception $e){
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}
	
	public function send($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择退换单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$refund_info_fields = array("sr.process_status","sr.warehouse_type");
				$refund_info_cond = array(
					'sr.refund_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Trade/SalesRefund')->alias('sr')->field($refund_info_fields)->where($refund_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['process_status']!= 63 && (int)$res_so_info['process_status']!= 64)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['warehouse_type']!=11 && (int)$res_so_info['warehouse_type']!=15)
				{
					SE("仓库类型不正确");
				}
					
				$WmsManager = ManagerFactory::getManager("Wms");
				$WmsManager->manual_wms_adapter_add_refund($sid, $uid, $id);
			}
		} catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
	public function cancel_sr($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择退换单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$refund_info_fields = array("sr.process_status","sr.warehouse_type");
				$refund_info_cond = array(
					'sr.refund_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Trade/SalesRefund')->alias('sr')->field($refund_info_fields)->where($refund_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['process_status']!= 65 && (int)$res_so_info['process_status']!= 63)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['warehouse_type']!=11 && (int)$res_so_info['warehouse_type']!=15)
				{
					SE("仓库类型不正确");
				}
				if((int)$res_so_info['process_status']== 63){
					D('RefundManage')->cancel_sr($id);
				}else{	
					$WmsManager = ManagerFactory::getManager("Wms");
					$WmsManager->manual_wms_adapter_cancel_refund($sid, $uid, $id);
				}
			}
		} catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
	
	public function exportToExcel(){
		if(!self::ALLOW_EXPORT){
			echo self::EXPORT_MSG;
			return false;
		}
		$id_list = I('get.id_list');
		$type = I('get.type');
		$result = array('status'=>0,'info'=>'');
		try{
			if($id_list == ''){
				$search = I('get.search','',C('JSON_FILTER'));
				foreach ($search as $k => $v) {
					$key = substr($k,7,strlen($k)-8);
					$search[$key] = $v;
					unset($search[$k]);
				}
				D('RefundManage')->exportToExcel('',$search,$type);
			}else{
				D('RefundManage')->exportToExcel($id_list,array(),$type);
			}
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			$result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
		}
		echo $result['info'];
	}

	public function exchangeRefundGoods($id){
		$id=intval($id);
		if (IS_POST) {
			$result=array();
			$data=array();
			try
			{
				$orders=I('post.order');
				$refund_id=I('post.refund_id');
				$data['goods_id']=$orders[0]['goods_id'];
				$data['goods_no']=$orders[0]['goods_no'];
				$data['goods_name']=$orders[0]['goods_name'];
				$data['spec_id']=$orders[0]['spec_id'];
				$data['spec_name']=$orders[0]['spec_name'];
				$data['spec_no']=$orders[0]['spec_no'];
				$data['order_num']=0;
				$data['refund_num']=$orders[0]['num'];
				$data['refund_order_id']=$id;
				D('SalesRefundOrder')->save($data);
				$result['status']=0;
			}catch(BusinessLogicException $e)
			{
				$result = array('status'=>1,'info'=>$e->getMessage());
			}catch (\PDOException $e){
				$result = array('status'=>1,'info'=>$e->getMessage());
			}
			$this->ajaxReturn($result);
		}else{
			$refund_db=D('RefundManage');
			$id_list=array(
					'toolbar'=>'refund_manage_exchange_toolbar',
					'form_id'=>'refund_manage_edit_form',
					'exchange_dialog'=>'refund_dialog_exchange_order',
			);
			$datagrid=$refund_db->getDialogView('refund_exchange',$id_list);
			$this->assign('id_list',$id_list);
			$this->assign('datagrid',$datagrid);
			$this->display('dialog_refund_exchange');
		}
	}


}