<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Build;
use Think\Log;

class TradeCheckController extends BaseController
{
	public function getTradeList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{

		if(IS_POST)
		{ //sales_trade_check_query 
			$where_sales_trade=' AND st_1.trade_status=30 ';
			if ($search['remark_id']!='') {
				switch ($search['remark_id']) {
					case '0':
						$search['no_cs']=1;$search['no_client']=1;break;
					case '1':
						$where_sales_trade.=" AND (st_1.cs_remark <> '' OR st_1.buyer_message <> '' )";break;
					case '2':
						$search['cs']=1;break;
					case '3':
						$search['client']=1;break;
					case '4':
						$search['cs']=1;$search['client']=1;break;
					default:
						# code...
						break;
				}
			}	
			unset($search['remark_id']);
			$data=D('TradeCheck')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else 
		{
			$id_list=array(
					'form'=>'trade_check_form',
					'toolbar'=>'trade_check_toobbar',
					'tab_container'=>'trade_check_tab_container',
					'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
					'edit'=>'trade_check_dialog_edit',
					'add'=>'trade_check_dialog_add',
					'more_button'=>'trade_check_more_button',
					'more_content'=>'trade_check_more_content',
					'hidden_flag'=>'trade_check_hidden_flag',
					'set_flag'=>'trade_check_set_flag',
					'search_flag'=>'trade_check_search_flag',//标记作为搜索条件,不作为搜索条件不写
					//自定义id-非通用
					'invalid'=>'trade_check_dialog_invalid_goods',
					'invalid_goods'=>'trade_check_invalid_goods',
					'exchange'=>'trade_check_dialog_exchange_order',
					'fast_div'=>'trade_check_fast_div',
					'add_goods'=>'trade_check_dialog_add_goods',
					'suite_split'=>'trade_check_dialog_suite_split',
					'fileForm'=> 'trade_check_file_form',
                	'fileDialog'=>'trade_check_file_dialog',
					"sms"           => "sms_trade_check",
					'include_goods'=>'trade_check_include_goods',
			);
			$url_list=array(
					'merge_url'=>U('TradeCheck/mergeTrade'),//订单合并
					'split_url'=>U('TradeCheck/splitTrade'),//订单拆分
					'check_url'=>U('TradeCheck/checkTrade'),//订单审核
					'quick_check_url'=>U('TradeCheck/quickCheckTrade'),//快速审核
					'invalid_goods_url'=>U('TradeCheck/getInvalidGoods'),//未匹配货品
					'clear_revert_url'=>U('TradeCheck/clearRevertTrade'),//清除驳回
					'unfreeze_url'=>U('TradeCheck/freezeTrade'),//冻结-解冻
					'refund_url'=>U('TradeCheck/refundTrade'),//订单--全额退款
					'clear_bad_url'=>U('TradeCheck/clearBadTrade'),//清除异常
					'passel_split_url'=>U('TradeCheck/chooseSplitType'),//订单批量拆分
					'direct_consign_url'=>U('TradeCheck/directConsign'),//订单直接发货
					'delete_url'=>U('TradeCheck/deleteTrade'),//订单删除
					'change_url'=>U('TradeCheck/changeLogisWare'),//修改物流仓库
					'force_check_pwd_url'=>U('TradeCheck/forceCheckPwd'),//校验强制审核密码
					'remove_url'=>U('TradeCheck/removeTrade'),//无需系统处理
					'passel_exchange_url'=>U('TradeCheck/passelExchange'),//订单批量换货
					'batch_csremark_url'=>U('TradeCheck/showRemarkDialog'),//订单批量编辑客服备注
					'gift_not_send_reason'=>U('TradeCheck/showGiftDialog'),//赠品未赠原因
					'refund_split_url'=>U('TradeCheck/splitRefundTrade'),//拆分申请退款的单子
					'deep_split_url'=>U('TradeCheck/deepSplit'),//深度拆分
					'passel_add_goods'=>U('TradeCheck/passelAddGoods'),//订单批量添加货品
					'merge_split_url'=>U('TradeCheck/splitMergeTrade'),//一键拆分合并单
					'suite_split_url'=>U('TradeCheck/suiteSplit'),//按组合装拆分订单
					'search_src_tids_url'=>U('TradeCheck/searchSrcTids'),//按原始单号筛选订单
					'merge_and_check_trade_url'=>U('TradeCheck/mergeAndCheckTrade'),//一键合并且审核
					'deep_split_by_suite_url'=>U('TradeCheck/deepSplitBySuite'),//按组合装深度拆分
					'upload_remark_and_flag_url'=>U('TradeCheck/uploadRemarkAndFlag'),//回传备注和标旗
			);
			//获取配置
			$rows=get_config_value('page_limit',0);
			switch($rows){
				case '0':
					$rows=20;break;
				case '1':
					$rows=50;break;
				case '2':
					$rows=100;break;
				default:
					$rows=20;break;
			}
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('TradeCheck/getTradeList', array('grid'=>'datagrid')),
							'toolbar' => "#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'frozenColumns'=> D('Setting/UserData')->getDatagridField('Trade/TradeCheck','trade_check',1),
							'singleSelect'=>false,
							'ctrlSelect'=>true,
						    'pageSize'=>$rows,
							'pageList'=>[20,50,100,200],
					),
					'fields' => D('Setting/UserData')->getDatagridField('Trade/TradeCheck','trade_check'),//get_field('TradeCheck','trade_check')
					'setTabs'=>true,
					'setTabsClick'=>'tradeCheck.setTabField()',
           	);
			// $checkbox=array('field' => 'ck','checkbox' => true);
   //          array_unshift($datagrid['fields'],$checkbox);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'goods_list')).'?tab=goods_list&prefix=tradeCheck','title'=>'货品列表'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_detail')).'?tab=trade_detail&prefix=tradeCheck','title'=>'订单详情'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'src_trade')).'?tab=src_trade&prefix=tradeCheck','title'=>'原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'stock_list')).'?tab=stock_list&prefix=tradeCheck','title'=>'库存明细'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_refund')).'?tab=trade_refund&prefix=tradeCheck','title'=>'退换单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_merge')).'?tab=trade_merge&prefix=tradeCheck','title'=>'同名未合并订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_remark')).'?tab=trade_remark&prefix=tradeCheck','title'=>'备注记录'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_log')).'?tab=trade_log&prefix=tradeCheck','title'=>'订单日志')
			);
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			foreach ($arr_flag['list'] as $list) {  $arr[] = $list['id']; }
			foreach ($arr_flag['json'] as $k => $v) {
				if (!in_array($k, $arr)) { unset($arr_flag['json'][$k]); }
			}
			$system_info =M('dict_url')->alias('u')->field("u.url_id AS id, u.name AS text,IF(u.is_leaf=0 OR u.controller IS NULL OR u.controller='','', CONCAT('index.php/',u.module,'/',u.controller,'/',u.action,IF(u.type=2,CONCAT('?dialog=',LOWER(u.controller)),''))) href, u.parent_id,lower(module) module,is_leaf")->where(array('u.url_id'=>8))->order('u.parent_id ASC, u.sort_order DESC')->find();
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'edit'=>array('id'=>'flag_set_dialog','url'=>U('TradeCheck/editTrade').'?datagrid_id='.$id_list['id_datagrid'].'&exchange='.$id_list['exchange'],'heigth'=>560,'width'=>840,'title'=>'订单编辑'),
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
					'freeze_reason'=>array(
							'id'=>'flag_set_dialog',
							'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=2&model_type=tradecheck',
							'title'=>'冻结原因',
							'width'=>360,
							'height'=>'auto',
							'ismax'=>false,
							'form' =>array('url'=>U("Trade/TradeCheck/freezeTrade"),'dialog_type'=>'tradecheck','id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox')
					),
					'cancel_reason'=>array(
							'id'=>'flag_set_dialog',
							'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=3&model_type=tradecheck',
							'title'=>'取消原因',
							'width'=>360,
							'height'=>'auto',
							'form' =>array('url'=>U("Trade/TradeCheck/cancelTrade"),'dialog_type'=>'tradecheck','id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox')
					),
					"sms"        => array(
							"id"    => $id_list["sms"],
							"title" => "短信发送",
							"url"   => U("TradeCheck/SMS"),
					),
					'include_goods'=>array(
						'id'=>$id_list['include_goods'],
						'datagrid'=>'check_include_goods_list_datagrid',
						'url'=>U('Trade/TradeCheck/getDialogIncludeGoodsList').'?type=include',
						'title'=>'选择货品',
						'width'=>900,
						'height'=>510,
						'ismax'=>false,
					),
					'not_include_goods'=>array(
						'id'=>$id_list['include_goods'],
						'datagrid'=>'check_include_goods_list_datagrid',
						'url'=>U('Trade/TradeCheck/getDialogIncludeGoodsList').'?type=not_include',
						'title'=>'选择货品',
						'width'=>900,
						'height'=>510,
						'ismax'=>false,
					),
			);
			$params['set_conf']=array('title'  => $system_info['text'], 'url' =>$system_info['href']."&tab_type=订单设置&config_name=auto_check_is_open&info=是否开启自动审核");
			try
			{
				$list_form=UtilDB::getCfgRightList(array('shop','logistics','reason','brand','warehouse'),array('logistics'=>array('is_disabled'=>array('eq',0)),'reason'=>array('class_id'=>array('eq',1)),'warehouse'=>array('is_disabled'=>array('eq',0))));
				$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=1 AND is_builtin=0 AND is_disabled=0' );
				array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));				
				$id_list['invalid_goods_total']=D('ApiTradeOrder')->getInvalidGoods();
				$order_check_force_check_pwd_is_open=get_config_value('order_check_force_check_pwd_is_open','0');
				$params['force_check_pwd_is_open']=$order_check_force_check_pwd_is_open;
			}catch(BusinessLogicException $e)
			{
				$id_list['invalid_goods_total']=0;
			}
			$res_cfg=D('Setting/Flag')->getCfgFlags(
					'bg_color,font_color,font_name',
					array('flag_name'=>array('eq','退款'),'flag_class'=>array('eq',1))
			);
			$refund_color='background-color:' . $res_cfg['bg_color'] . ';color:' . $res_cfg['font_color'] . ';font-family:' . $res_cfg['font_name'] . ';';
			$bad_reason=C('bad_reason');
			$this->assign('list',$list_form);
			$this->assign('params', json_encode($params));
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign('url_list',$url_list);
			$this->assign('id_list',$id_list);
			$this->assign('datagrid', $datagrid);
			$this->assign('bad_reason',json_encode($bad_reason));
			$this->assign('refund_color',$refund_color);
			$this->display('show');
		}
	}
		
	public function mergeTrade($ids)
	{
		$arr_data=is_json($ids);
		$arr_ids_data=$arr_data['id'];
		if(IS_POST)
		{
			$arr_form_data=I('post.info','',C('JSON_FILTER'));
			$user_id=get_operator_id();
			try {
				if(empty($arr_form_data['mobile'])&&empty($arr_form_data['telno']))
				{
					$this->error('手机和电话至少一个不为空');
				}
				D('TradeCheck')->mergeTrade($arr_ids_data,$arr_form_data,$user_id,$arr_data['version']);
			}catch (BusinessLogicException $e)
			{
				$this->error($e->getMessage());
			}catch(\Think\Exception $e){
				$this->error($e->getMessage());
			}
			$this->success('合并成功');
		}else 
		{
			$list=array();
			if(!is_array($arr_ids_data)||count($arr_ids_data)<2)
			{
				$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'合并至少选择则两个订单');
			}
			$list_shop=array();
			$list_salesman=array();
			$list_receiver=array();
			$list_mobile=array();
			$list_telno=array();
			$list_logistics=array();
			$flag_buyer_nick='';
			$flag_receiver='';
			try {
				$res_trades_arr=D('TradeCheck')->getSalesTradeList(
						'st.trade_id,st.trade_no,st.platform_id,st.shop_id,sh.shop_name,st.warehouse_id,st.warehouse_type,
						 st.trade_status,st.check_step,st.delivery_term,st.freeze_reason,st.refund_status,st.unmerge_mask,
						 st.customer_id,st.buyer_nick,st.receiver_name,st.receiver_address,st.receiver_mobile,st.receiver_telno,
						 st.receiver_zip,st.receiver_area,st.receiver_ring,st.to_deliver_time,st.dist_center,st.dist_site,
						 st.logistics_id,clg.logistics_name,st.buyer_message,st.cs_remark,st.print_remark,st.salesman_id,
						 he.fullname,st.checker_id,st.checkouter_id,st.flag_id,st.bad_reason,st.is_sealed,st.split_from_trade_id,
						 st.stockout_no,st.revert_reason,st.cancel_reason,st.trade_mask,st.reserve,st.modified,st.created,st.version_id',
						array('st.trade_id'=>array('in',$arr_ids_data)),
						'st',
						'LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id 
						 LEFT JOIN hr_employee he ON st.salesman_id=he.employee_id 
						 LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id'
				);
				$res_cfg_val=get_config_value('order_edit_must_checkout');
				$trade_no=$res_trades_arr[0]['trade_no'];
				$trade_status=$res_trades_arr[0]['trade_status'];
				$warehouse_id=$res_trades_arr[0]['warehouse_id'];
				$delivery_term=$res_trades_arr[0]['delivery_term'];
				$platform_id=$res_trades_arr[0]['platform_id'];
				if($trade_status!=25&&$trade_status!=30)
				{
					$trade_status=30;
				}
				$i=0;
				foreach ($res_trades_arr as $v)
				{
					if($v['version_id']!=$arr_data['version'][$v['trade_id']])
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'订单被其他人修改，请打开重新编辑');
						break;
					}
					if($v['is_sealed']!=0)
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'为不可合并订单');
						break;
					}
					if($i!=0&&$v['trade_status']!=$trade_status)
					{
						$list[]=array('trade_no'=>$trade_no,'result_info'=>'状态不正确');
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'状态不正确');
						break;
					}
					if($v['revert_reason']!=0)
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'是被驳回订单,请先处理');
						break;
					}
					if($v['bad_reason']!=0)
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'是异常订单,请先处理');
						break;
					}
					if($v['warehouse_id']==0||$v['warehouse_id']!=$warehouse_id)
					{
						if ($v['warehouse_id']==0)
						{
							$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'无效仓库,无法合并');
						}else{
							$list[]=array('trade_no'=>$trade_no,'result_info'=>'仓库不同,无法合并');
							$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'仓库不同,无法合并');
						}
						break;
					}
					if($v['delivery_term']!=$delivery_term)
					{
						$list[]=array('trade_no'=>$trade_no,'result_info'=>'发货方式不同，无法合并');
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'发货方式不同，无法合并');
						break;
					}
					if($v['platform_id']!=$platform_id)
					{
						$list[]=array('trade_no'=>$trade_no,'result_info'=>'来源平台不同，无法合并');
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'来源平台不同，无法合并');
						break;
					}
					if($v['freeze_reason']!=0)
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'已冻结');
						break;
					}
					if($res_cfg_val!=0&&$v['checkouter_id']==0)
					{
						$list[]=array('trade_no'=>$v['trade_no'],'result_info'=>'必须签出才可编辑');
						break;
					}
				
					$receiver=trim($v['receiver_name']).' '.$v['receiver_area'].' '.trim($v['receiver_address']);
					if($i!=0)
					{
						if($list_shop[count($list_shop)-1]['id']!=$v['shop_id']&&$v['shop_id']!=0)
						{
							$list_shop[]=array('id'=>$v['shop_id'],'name'=>$v['shop_name']);
						}
						if($list_salesman[count($list_salesman)-1]['id']!=$v['salesman_id']&&$v['salesman_id']!=0)
						{
							$list_salesman[]=array('id'=>$v['salesman_id'],'name'=>$v['fullname']);
						}
						if($list_logistics[count($list_logistics)-1]['id']!=$v['logistics_id']&&$v['logistics_id']!=0)
						{
							$list_logistics[]=array('id'=>$v['logistics_id'],'name'=>$v['logistics_name']);
						}
						if($list_mobile[count($list_mobile)-1]['name']!=$v['receiver_mobile']&&(!empty($v['receiver_mobile'])))
						{
							$list_mobile[]=array('name'=>$v['receiver_mobile']);
						}
						if($list_telno[count($list_telno)-1]['name']!=$v['receiver_telno']&&(!empty($v['receiver_mobile'])))
						{
							$list_telno[]=array('name'=>$v['receiver_telno']);
						}
						if($list_receiver[count($list_receiver)-1]['name']!=$receiver)
						{
							$list_receiver[]=array('id'=>$v['trade_id'],'name'=>$receiver);
						}
						if((!$flag_buyer_nick)&&$res_trades_arr[$i-1]['buyer_nick']!=$v['buyer_nick'])
						{
							$flag_buyer_nick='isTure';
						}
						if((!$flag_receiver)&&$res_trades_arr[$i-1]['receiver_name']!=$v['receiver_name'])
						{
							$flag_receiver='isTure';
						}
					}else
					{
						if($v['shop_id']!=0)
						{
							$list_shop[]=array('id'=>$v['shop_id'],'name'=>$v['shop_name']);
						}
						if($v['salesman_id']!=0)
						{
							$list_salesman[]=array('id'=>$v['salesman_id'],'name'=>$v['fullname']);
						}
						if($v['logistics_id']!=0)
						{
							$list_logistics[]=array('id'=>$v['logistics_id'],'name'=>$v['logistics_name']);
						}
						if(!empty($v['receiver_mobile']))
						{
							$list_mobile[]=array('name'=>$v['receiver_mobile']);
						}
						if(!empty($v['receiver_telno']))
						{
							$list_telno[]=array('name'=>$v['receiver_telno']);
						}
						if(!empty($receiver))
						{
							$list_receiver[]=array('id'=>$v['trade_id'],'name'=>$receiver);
						}
					}
					$i++;
				}
				if(empty($list))
				{
					if(!empty($list_shop))
					{
						$list_shop[0]['selected']=true;
					}
					if(!empty($list_salesman))
					{
						$list_salesman[0]['selected']=true;
					}else 
					{
						$list_salesman[0]=array('id'=>0,'name'=>'无','selected'=>true);
					}
					if(!empty($list_logistics))
					{
						$list_logistics[0]['selected']=true;
					}
					if(!empty($list_mobile))
					{
						$list_mobile[0]['selected']=true;
					}
					if(!empty($list_telno))
					{
						$list_telno[0]['selected']=true;
					}
					$list_receiver[0]['selected']=true;
					$params=array('flag_buyer'=>$flag_buyer_nick,'flag_receiver'=>$flag_receiver,'shop'=>json_encode($list_shop),'salesman'=>json_encode($list_salesman),'logistics'=>json_encode($list_logistics),'mobile'=>json_encode($list_mobile),'telno'=>json_encode($list_telno),'receiver'=>json_encode($list_receiver));
					$this->assign('params_merge_trade',$params);
					$this->display('dialog_trade_merge');
				}else{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
				}
			}catch (BusinessLogicException $e){
				$list[]=array('trade_no'=>'','result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
			catch (\Exception $e)
			{
				$list[]=array('trade_no'=>'','result_info'=>$e->getMessage());
				\Think\Log::write($this->name.'-'.$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
		}
	}
	
	// 订单拆分
	public function splitTrade($id)
	{
		if(IS_POST)
		{
			$user_id=get_operator_id();
			try {
				if(intval($id)==0)
				{
					$this->error('订单不存在');
				}
				$arr_data_main=I('post.main_orders','',C('JSON_FILTER'));
				if(empty($arr_data_main))
				{
					$this->error('未拆分订单');
				}
				$list=array();
				if(!D('TradeCheck')->getSplitCheckInfo($id,$list,$weight))
				{
					$this->error($list[0]['trade_no'].$list[0]['result_info']);
				}
				//增加日志
				$trade_log[]=array(
					'trade_id'=>$id,
					'operator_id'=>$user_id,
					'type'=>37,
					'data'=>1,
					'message'=>'订单拆分',
					'created'=>date('y-m-d H:i:s',time()),
				);
				D('SalesTradeLog')->addTradeLog($trade_log);
				D('TradeCheck')->splitTrade($id,$arr_data_main,$user_id);
				$this->success('拆分成功');
			}catch (BusinessLogicException $e){
				$this->error($e->getMessage());
			}catch (\Think\Exception $e){
				$this->error($e->getMessage());
			}
		}else
		{
			if(intval($id)==0)
			{
				$this->error('订单不存在');
			}
			$sales_trade_model=D('TradeCheck');
			$list=array();
			if(!$sales_trade_model->getSplitCheckInfo($id,$list))
			{
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
				return ;
			}
			try {
				$point_number = get_config_value('point_number',0);
            	$left_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) left_num";
            	$num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
            	$sys_available_stock = get_config_value('sys_available_stock',640);
            	$stock=D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
            	$available_stock = "CAST(IFNULL(".$stock.",0) AS DECIMAL(19,".$point_number.")) available_stock";
				$res_sales_orders_arr=D('SalesTradeOrder')->getSalesTradeOrderList(
						"sto.rec_id AS id,sto.src_oid,sto.spec_no,sto.goods_name,sto.api_goods_name,sto.api_spec_name,sto.spec_name,
						".$num.",sto.actual_num,sto.price,sto.share_price,sto.order_price,sto.discount, sto.share_post,sto.share_amount,
						sto.remark,sto.weight,".$available_stock.",".$left_num.",0 AS split_num",
						array('sto.trade_id'=>array('eq',$id),'sto.actual_num'=>array('gt',0)),
						'sto',
						"LEFT JOIN stock_spec ss  ON ss.spec_id=sto.spec_id  AND  ss.warehouse_id=".$list['warehouse_id']
				);
				$datagrid=$sales_trade_model->getDialogView('split');
				$data=array('total'=>count($res_sales_orders_arr),'rows'=>$res_sales_orders_arr);
				$this->assign('split_trade_order_data',json_encode($data));
				$this->assign('split_trade_def_value',$list);
				$this->assign('datagrid',$datagrid['split_main_trade']);
				$this->assign('datagrid2',$datagrid['split_new_trade']);
				$this->assign('point_number',$point_number);
				$this->display('dialog_trade_split');
			} catch (BusinessLogicException $e) {
				unset($list['warehouse_id']);
				$list[]=array('trade_no'=>'','result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			} catch(Exception $e){
				unset($list['warehouse_id']);
				$list[]=array('trade_no'=>'','result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
		}
	}
	
	public function chooseSplitType($ids)
	{
		$count=count(is_json($ids));
		$url=U('TradeCheck/passelSplit');
		$this->assign('trade_count',$count);
		$this->assign('ids',$ids);
		$this->assign('url',$url);
		$this->display('dialog_passel_split');
	}

    public function splitRefundTrade($trade_id){
    	$user_id=get_operator_id();
		try {
			if(intval($trade_id)==0)
			{
				$this->error('订单不存在');
			}
			$list=array();
			if(!D('TradeCheck')->getSplitCheckInfo($trade_id,$list))
			{
				$this->error($list[0]['trade_no'].$list[0]['result_info']);
			}
			$point_number = get_config_value('point_number',0);
			$left_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) left_num";
			$num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
			$sys_available_stock = get_config_value('sys_available_stock',640);
        	$stock=D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
        	$available_stock = "CAST(IFNULL(".$stock.",0) AS DECIMAL(19,".$point_number.")) available_stock";
			$arr_data_main=D('SalesTradeOrder')->getSalesTradeOrderList(
						"sto.rec_id AS id,sto.src_oid,sto.spec_no,sto.goods_name,sto.api_goods_name,sto.api_spec_name,sto.spec_name,
						".$num.",sto.actual_num,sto.price,sto.share_price,sto.order_price,sto.discount, sto.share_post,sto.share_amount,
						sto.remark,sto.weight,".$available_stock.",".$left_num.",sto.num AS split_num",
						array('sto.trade_id'=>array('eq',$trade_id),'sto.actual_num'=>array('gt',0),'sto.refund_status'=>array('eq',2)),
						'sto',
						"LEFT JOIN stock_spec ss  ON ss.spec_id=sto.spec_id  AND  ss.warehouse_id=".$list['warehouse_id']
				);
			$sale_trade_data=D("SalesTrade")->getSalesTrade('goods_count',array('trade_id'=>array('eq',$trade_id)));
			if(empty($arr_data_main))
			{
				$this->error('子订单中没有申请退款单');
			}
			$sum_num=0;
			foreach ($arr_data_main as $v) {
				$sum_num+=$v['left_num'];
			}
			if ($sum_num==$sale_trade_data['goods_count']) {
				$this->error('所有货品都已申请退款，请线上同意退款');
			}
			//增加日志
			$trade_log[]=array(
				'trade_id'=>$trade_id,
				'operator_id'=>$user_id,
				'type'=>37,
				'data'=>1,
				'message'=>'拆分申请退款订单',
				'created'=>date('y-m-d H:i:s',time()),
			);
			D('SalesTradeLog')->addTradeLog($trade_log);
			D('TradeCheck')->splitTrade($trade_id,$arr_data_main,$user_id);
			$this->success('拆分成功');
		}catch (BusinessLogicException $e){
			$this->error($e->getMessage());
		}catch (\Think\Exception $e){
			$this->error($e->getMessage());
		}
    }

	// 订单批量拆分
	public function passelSplit($split){
		if(IS_POST){
			$data=I('post.data','',C('JSON_FILTER'));
			$type=$data['type'];//type=0:按订单中相同货品批量拆分，type=1:按重量批量拆分订单
			$ids=$data['ids'];
			$continue=set_default_value($data['continue'], 0);
			$list=array();
			$user_id=get_operator_id();
			//按订单相同货品进行批量拆分
			if($type==0){
				$split_orders=$data['info'];
				$split_num=$data['split_num'];
				if($split_num==1){//拆出订单数量为一条，即一拆二
					try{
						foreach($ids as $id){
							unset($list);
							if(!D('TradeCheck')->getSplitCheckInfo($id,$list,$weight))
							{
								$this->error($list[0]['trade_no'].$list[0]['result_info']);
							}
							for($i=0;$i<count($split_orders);$i++){
								$split_orders[$i]['id']=$split_orders[$i][$id]['rec_id'];
							}
							//增加日志
							$trade_log[]=array(
								'trade_id'=>$id,
								'operator_id'=>$user_id,
								'type'=>37,
								'data'=>1,
								'message'=>'订单批量拆分--按相同货品拆分（拆分数量为单条）',
								'created'=>date('y-m-d H:i:s',time()),
							);
							D('SalesTradeLog')->addTradeLog($trade_log);
							D('TradeCheck')->splitTrade($id,$split_orders,$user_id);
						}
						$this->success('拆分成功');
					}catch (BusinessLogicException $e){
						$this->error($e->getMessage());
					}catch(\Exception $e){
						$this->error($e->getMessage());
					}
				}else{//拆出订单数量为多条，即尽量多的拆分订单
					try{
						foreach ($ids as $id){
							unset($list);
							if(!D('TradeCheck')->getSplitCheckInfo($id,$list,$weight))
							{
								$this->error($list[0]['trade_no'].$list[0]['result_info']);
							}
							for($i=0;$i<count($split_orders);$i++){
								$split_orders[$i]['id']=$split_orders[$i][$id]['rec_id'];
								$split_orders[$i]['left_num']=$split_orders[$i][$id]['num'];
							}
							//增加日志
							$trade_log[]=array(
								'trade_id'=>$id,
								'operator_id'=>$user_id,
								'type'=>37,
								'data'=>1,
								'message'=>'订单批量拆分--按相同货品拆分（拆分数量为多条）',
								'created'=>date('y-m-d H:i:s',time()),
							);
							D('SalesTradeLog')->addTradeLog($trade_log);
							$continue=1;
							while ($continue) {
								D('TradeCheck')->splitTrade($id,$split_orders,$user_id);
								$count_num=0;$total_left=0;$total_split=0;
								for($i=0;$i<count($split_orders);$i++){
									$split_orders[$i]['left_num']-=$split_orders[$i]['split_num'];
									$total_left+=$split_orders[$i]['left_num'];
									$total_split+=$split_orders[$i]['split_num'];
									if($split_orders[$i]['left_num']<=$split_orders[$i]['split_num']){
										$continue=0;
									}
								}
								if($total_left<=$total_split){
									$continue=0;
								}
							}
						}
						$this->success('拆分成功');
					}catch (\Exception $e){
						$this->error($e->getMessage());
					}
				}
			}else if($type==1){//按重量批量拆分订单
				$list=array();
				$weight_list=array();
				$trade_no='';
				$message='';
				$weight=$data['max_weight'];
				$sales_trade_model=D('TradeCheck');
				for($i=0;$i<count($ids);$i++){
					unset($list);
					if(!$sales_trade_model->getSplitCheckInfo($ids[$i],$list,$weight,$weight_list))
					{
						$this->success($list[0]['trade_no'].$list[0]['result_info']);
						return false;
					}
				}
				if(count($weight_list)>0&&$continue==0){
					$result=array(
							'status'=>0,
							'rows'=>$weight_list,
							'total'=>count($weight_list),
							'ids'=>$ids,
							'max_weight'=>$weight,
					);
					$this->ajaxReturn($result);
				}else{
					try {
						foreach ($ids as $id){
							//增加日志
							$trade_log[]=array(
								'trade_id'=>$id,
								'operator_id'=>$user_id,
								'type'=>37,
								'data'=>1,
								'message'=>'订单批量拆分--按重量批量拆分',
								'created'=>date('y-m-d H:i:s',time()),
							);
							D('SalesTradeLog')->addTradeLog($trade_log);
							$no_split_trade=D('TradeCheck')->splitByWeight($id,$weight);
							$trade_no.=$no_split_trade==1?'':$no_split_trade.' ';
						}
					}catch (BusinessLogicException $e){
						$this->success($e->getMessage());
						return false;
					}catch (\Exception $e){
						$this->success($e->getMessage());
						return false;
					}
					$message=$trade_no==''?'':',订单'.$trade_no.'不需要拆分';
					$this->success('拆分成功'.$message);
				}
			}
		}else{//按订单相同货品拆分需要新界面显示订单相同货品
			$data=is_json($split);
			$ids=$data['ids'];
			$info=$data['info'];
			$length=count($ids);
			$list=array();
			for($i=0;$i<$length;$i++){
				unset($list);
				if(!D('TradeCheck')->getSplitCheckInfo($ids[$i],$list))
				{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
					return false;
				}
			}
			if ($info['type']==0){
				try{
					$common_orders=D('SalesTradeOrder')->getCommonOrders($ids);
				}catch (BusinessLogicException $e){
					unset($list);
					$list[]=array('result_info'=>'所选订单中无相同货品');
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
					return false;
				}
				$datagrid=D('TradeCheck')->getDialogView('passel_split');
				$data=array('total'=>count($common_orders),'rows'=>$common_orders);
				$this->assign('split',$split);
				$this->assign('passel_split_common_order',json_encode($data));
				$this->assign('datagrid',$datagrid['split_common_order']);
				$this->assign('datagrid2',$datagrid['split_new_order']);
				$this->display('dialog_passel_split_by_order');
			}
		}
	}
	// 按组合装拆分
	public function suiteSplit($id)
	{
		if (IS_POST) {
			$user_id=get_operator_id();
			if(intval($id)==0)
			{
				$this->error('订单不存在');
			}
			$data=I('post.data','',C('JSON_FILTER'));
			$split_orders=$data['info'];
			$list=array();
			try{				
				unset($list);
				if(!D('TradeCheck')->getSplitCheckInfo($id,$list))
				{
					$this->error($list[0]['trade_no'].$list[0]['result_info']);
				}
				//增加日志
				$trade_log[]=array(
					'trade_id'=>$id,
					'operator_id'=>$user_id,
					'type'=>37,
					'data'=>1,
					'message'=>'订单批量拆分--按组合装拆分',
					'created'=>date('y-m-d H:i:s',time()),
				);
				D('SalesTradeLog')->addTradeLog($trade_log);
				D('TradeCheck')->splitBySuite($id,$split_orders);	
				$this->success('拆分成功');
			}catch (BusinessLogicException $e){
				$this->error($e->getMessage());
			}catch(\Exception $e){
				$this->error($e->getMessage());
			}			
		}else{	
			if(intval($id)==0)
			{
				$this->error('订单不存在');
			}
			$sales_trade_model=D('TradeCheck');
			$list=array();
			if(!$sales_trade_model->getSplitCheckInfo($id,$list))
			{
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
				return false;
			}			
			try{
				$suite_orders=D('SalesTradeOrder')->getSuiteOrders($id);				
			}catch (BusinessLogicException $e){
				unset($list);
				$trade=M('sales_trade')->field('trade_no')->where(array('trade_id'=>array('eq',$id)))->find();
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单中不包含组合装');
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
				return false;
			}
			$sales_trade_db=D('SalesTrade');
			$id_list=array(
					'toolbar'=>'suite_split_toolbar',
					'form_id'=>'suite_split_edit_form',
					'add_suite_dialog'=>'trade_check_dialog_suite_split',
			);
			$datagrid=$sales_trade_db->getDialogView('suite_split',$id_list);
			$data=array('total'=>count($suite_orders),'rows'=>$suite_orders);
			$this->assign('suite_split_common_order',json_encode($data));
			$this->assign('id',json_encode($id));
			$this->assign('id_list',$id_list);
			$this->assign('datagrid',$datagrid);
			$this->display('dialog_suite_split');
		}
	}
	
	public function quickCheckTrade($search=array())
	{
		if(IS_POST)
		{
			$check_type=-1;
			if (empty($search)) 
			{
				$search=I('post.info','',C('JSON_FILTER'));
			}else
			{
				$check_type=2;
			}
			$arr_remark_flags='';
			for ($i=0;$i<6;$i++)
			{
				$arr_remark_flags.=(!isset($search['remark_flag_'.$i])?'':$i.',');
			}
			if(!empty($arr_remark_flags))
			{
				$search['remark_flag']=true;
			}
			$time_type='trade_time';
			if($search['time_type']==1){
				$time_type='pay_time';
			}
			$where_str=' WHERE st.trade_status=30 ';
			D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			foreach ($search as $k=>$v){
				if($v==='') continue;
				switch ($k)
				{
					case 'warehouse_id':
						set_search_form_value($where_str,$k,$v,'st',2,' AND ');
						break;
					case 'shop_id':
						set_search_form_value($where_str, $k, $v,'st', 2,' AND ');
						break;
					case 'buyer_message_count':
						set_search_form_value($where_str, $k, 0,'st',2,' AND ');
						break;
					case 'cs_remark_count':
						set_search_form_value($where_str, $k, 0,'st',2,' AND ');
						break;
					case 'discount':
						set_search_form_value($where_str, $k, 0,'st',2,' AND ');
						break;
					case 'invoice_type':
						set_search_form_value($where_str, $k, 0,'st',2,' AND ');
						break;
					case 'receiver_address':
						$where_str.=" AND st.receiver_address NOT LIKE '%村%' AND st.receiver_address NOT LIKE '%组%' ";
						break;
					case 'remark_flag':
						$where_str.=" AND st.remark_flag IN (".substr($arr_remark_flags,0,-1).") ";
						break;
					case 'start_time':
						set_search_form_value($where_str, $time_type, $v,'st',4,' AND ',' >= ');
						break;
					case 'end_time':
						set_search_form_value($where_str, $time_type, $v,'st',4,' AND ',' <= ');
						break;
					case 'max_weight':
						$v=floatval($v);
						$where_str.=" AND st.weight <= ".$v." ";
						break;
// 					case 'start':
// 						set_search_form_value($where_str, 'created', $v,'st',4,' AND ',' >= ');
// 						break;
// 					case 'end':
// 						set_search_form_value($where_str, 'created', $v,'st',4,' AND ',' <= ');
// 						break;
				}
			}
			$user_id=get_operator_id();
			$trade_check_db=D('Trade/TradeCheck');
			try {
				$sql_where='SELECT st.trade_id FROM sales_trade st '.$where_str;
				$data=$trade_check_db->checkTrade($sql_where,$check_type,$user_id);
				$result=array(
						'check'=>$data['check'],
						'status'=>$data['status'],
						'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
						'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
				);
			}catch (BusinessLogicException $e){
				$result=array('status'=>1,'message'=>$e->getMessage());
			}catch (Exception $e){
				$result=array('status'=>1,'message'=>$e->getMessage());
			}
			$this->ajaxReturn($result);
		}else{
			$this->display('dialog_quick_check');
		}
	}
	
	public function checkTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$check_type=I('post.type');//-1 快速审核，0普通审核，1强制审核
		$check_type=intval($check_type);
		$user_id=get_operator_id();
		$trade_check_db=D('TradeCheck');
		try {
			$sql_where=$trade_check_db->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('in',$arr_ids_data)))->select();
			$data=$trade_check_db->checkTrade($sql_where,$check_type,$user_id);
			$result=array(
					'check'=>$data['check'],
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
		
	public function getInvalidGoods($page=1, $rows=20, $search = array(), $sort = 'ags.rec_id', $order = 'desc')
	{	
		if(IS_POST)
		{//invalidgoods
			$where_str='';
			$data=D('TradeCheck')->queryInvalidGoods($where_str,$page, $rows, $search, $sort, $order);
			$this->ajaxReturn($data);
		}else
		{
			$type=I('get.type','tradecheck');
			$id_list = array(
					'form'=>$type.'_invalid_goods_form',
					'toolbar'=> $type.'_invalid_goods_datagrid_toolbar',
					'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . ACTION_NAME .'_'.$type. '_datagrid'),
			);
			$datagrid=array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
							'title'=>'',
							'toolbar' => "#{$id_list['toolbar']}",
							'url'   => U('TradeCheck/getInvalidGoods', array('grid'=>'datagrid')),
							'fitColumns'=>false,
							'rownumbers'=>true,
					),
					'fields' => get_field('TradeCheck','invalid_goods')
			);
			$params = array(
					'datagrid' => array('id' => $id_list['id_datagrid']),
					'search'=>array('form_id'=>$id_list['form']),
			);
			try{
				$invalid_num=D('ApiTradeOrder')->getInvalidGoods();
			}catch (BusinessLogicException $e){
				$invalid_num=0;
			}catch (Exception $e){
				$invalid_num=0;
			}
			$list_form=UtilDB::getCfgList(array('shop'));
			$this->assign('params', json_encode($params));
			$this->assign('list',$list_form);
			$this->assign('id_list', $id_list);
			$this->assign('datagrid',$datagrid);
			$this->assign('invalid_num',$invalid_num);
			$this->display('dialog_invalid_goods');
		}
	}

	public function getInvalidGoodsNum(){
		try{
				$invalid_num=D('ApiTradeOrder')->getInvalidGoods();
			}catch (BusinessLogicException $e){
				$invalid_num=0;
			}catch (Exception $e){
				$invalid_num=0;
			}
		$this->ajaxReturn($invalid_num);
	}
	
	// 编辑
	public function editTrade($id)
	{//sales_trade_modify_get
		if(IS_POST)
		{
			$arr_form_data=I('post.info','',C('JSON_FILTER'));
			$arr_orders_data=I('post.orders','',C('JSON_FILTER'));
			$arr_form_data['trade_id']=intval($id);
			$trade_db=D('SalesTrade');
			$user_id=get_operator_id();
			try {
				//查看修改号码权限
				$show_number_to_star=get_config_value('show_number_to_star',0);
				if($show_number_to_star==1&&!UtilDB::checkNumber(array($id), 'sales_trade', $user_id,null,2))
				{
					unset($arr_form_data['receiver_mobile']);
					unset($arr_form_data['receiver_telno']);
				}
				//订单表单校验
				$trade_db->validateTrade($arr_form_data);
				/*if(!$trade_db->validate($trade_db->getRules())->create($arr_form_data))
				{
					$this->error($trade_db->getError());
				}*/
				$trade_db->editTrade($arr_form_data,$arr_orders_data,$user_id);
				unset($trade_db);
			}catch (BusinessLogicException $e)
			{
				$this->error($e->getMessage());
			}catch (Exception $e){
				$this->error($e->getMessage());
			}
			$this->success('保存成功');
		}else
		{	$sales_trade_model=D('SalesTrade');
			$list=array();
			$user_id=get_operator_id();
			$res_trade=array();
			try {
				if(intval($id)<=0)
				{
					SE('无效订单');
				}
				$res_trade=$sales_trade_model->getSalesTrade(
						'st.trade_id AS id,st.trade_no, st.src_tids, st.shop_id, st.trade_status, st.warehouse_id, 
						 st.logistics_id, st.salesman_id, st.trade_type, st.invoice_type, st.invoice_title, st.invoice_content, 
						 st.delivery_term, st.buyer_nick, st.receiver_name, st.receiver_mobile, st.receiver_telno, 
						 st.receiver_province, st.receiver_city, st.receiver_district,  st.receiver_address, st.receiver_area, 
						 st.receiver_address, st.receiver_dtb, st.receiver_zip, st.goods_amount, st.post_amount, st.discount, st.post_cost, 
						 st.receivable, st.paid, st.dap_amount, st.cod_amount, st.commission, st.pay_account, st.buyer_message, 
						 st.cs_remark, st.print_remark, st.freeze_reason, cs.shop_name, cs.is_disabled AS dis1, 
						 cl.logistics_name, cl.is_disabled AS dis2, cw.name AS warehouse_name, cw.is_disabled AS dis3 ',
						array('st.trade_id'=>array('eq',$id)),
						'st',
						'LEFT JOIN cfg_shop cs ON cs.shop_id=st.shop_id 
						 LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id 
						 LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=st.warehouse_id '
				);
				if(!$sales_trade_model->getEditCheckInfo($res_trade,$list,$user_id))
				{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
				}
				if($res_trade['trade_status']!=30 && $res_trade['trade_status']!=25)
				{
					SE('订单非待审核状态，不可编辑');
				}
				$dialog_id=I('get.dialog_id');
				$datagrid_id=I('get.datagrid_id');
				$exchange=I('get.exchange');
				$id_list=array(
						'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
						'toolbar'=>'trade_edit_toolbar',
						'form_id'=>'trade_edit_form',
						'dialog_id'=>$dialog_id,
						'add_spec'=>'trade_edit_add_spec',
						'datagrid_id'=>$datagrid_id,
						'exchange'=>$exchange,
						'url_refund'=>U('TradeCheck/refundOrder'),
						'url_restore'=>U('TradeCheck/restoreOrder'),
						'url_exchange'=>U('TradeCheck/exchangeOrder'),
						'url_get_spec'=>U('TradeCheck/getGoodsSpec'),//获取货品的多规格
				);
				$datagrid=$sales_trade_model->getDialogView('edit',$id_list);
				 $checkbox=array('field' => 'ck','checkbox' => true);
				 array_unshift($datagrid['fields'],$checkbox);
				$list_form=UtilDB::getCfgRightList(array('shop','logistics','warehouse','employee'),array('warehouse'=>array('is_disabled'=>array('eq',0)),'logistics'=>array('is_disabled'=>array('eq',0)),'shop'=>array('is_disabled'=>array('eq',0))));
				$cod_logistics=UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>array('eq',0),'is_support_cod'=>array('eq',1))));
				
				$res_cfg=D('Setting/Flag')->getCfgFlags(
						'bg_color,font_color,font_name',
						array('flag_name'=>array('eq','退款'),'flag_class'=>array('eq',1))
				);
				//判断订单中的店铺、仓库、物流是否停用
				$dis_arr=array(
						1=>'shop',
						2=>'logistics',
						3=>'warehouse'
				);
				foreach ($dis_arr as $k=>$v){
					if($res_trade['dis'.$k]){
						$list_form[$v][]=array(
								'id'=>$res_trade[$v.'_id'],
								'name'=>$res_trade[$v.'_name'],
								'is_disabled'=>1
						);
						if($k==2){
							$cod_logistics[$v][]=array(
									'id'=>$res_trade[$v.'_id'],
									'name'=>$res_trade[$v.'_name'],
									'is_disabled'=>1
							);
						}
					}
				}
				$logistics['logistics']=$list_form['logistics'];
				$logistics['cod_logistics']=$cod_logistics['logistics'];
				$res_trade['bg_color']='background-color:' . $res_cfg['bg_color'] . ';color:' . $res_cfg['font_color'] . ';font-family:' . $res_cfg['font_name'] . ';';
				$point_number = get_config_value('point_number',0);
				$num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
				$actual_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
				$suite_num = "CAST(IF(sto.suite_num,sto.suite_num,'') AS DECIMAL(19,".$point_number.")) suite_num";
				$stock_num = "CAST(ss.stock_num AS DECIMAL(19,".$point_number.")) stock_num";
				$res_order_arr=D('SalesTradeOrder')->getSalesTradeOrderList(
						"sto.rec_id AS id,sto.rec_id AS sto_id,sto.trade_id,sto.spec_id,sto.platform_id,sto.shop_id,sto.src_oid,sto.suite_id,sto.src_tid,
						 ".$num.",sto.price,".$actual_num.",sto.order_price,sto.share_price, sto.share_amount,
						 sto.share_price,sto.discount,sto.share_post,sto.paid,sto.goods_name,sto.goods_id,
						 sto.goods_no,sto.spec_name, sto.spec_no,sto.spec_code,sto.gift_type,sto.suite_name,
						 ".$suite_num.",sto.suite_no, sto.weight,".$stock_num.",
						 0 AS is_suite,sto.remark,IF(sto.gift_type,1,IF(sto.platform_id,2,3)) edit,sto.refund_status, 
						 gs.img_url,gg.spec_count",
						 array('sto.trade_id'=>array('eq',$res_trade['id'])),
						'sto',
						"LEFT JOIN goods_spec gs on gs.spec_id = sto.spec_id LEFT JOIN stock_spec ss ON ss.warehouse_id=".intval($res_trade['warehouse_id'])." AND ss.spec_id=sto.spec_id LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id",
						'refund_status,id ASC'
				);
				//查看号码日志
				/* $ip=D('Setting/Employee')->getEmployee('last_login_ip',array('employee_id'=>array('eq',$user_id)));
				$log=array(
						'trade_id'=>$id,
						'operator_id'=>$user_id,
						'type'=>110,
						'message'=>'登录IP：'.$ip['last_login_ip'].' 查看了 订单-'.$res_trade['trade_no'].' 的号码',
    			 		'created'=>date('y-m-d H:i:s',time()),
				); 
				D('SalesTradeLog')->addTradeLog($log);
				*/
				$cfg_show_telno=get_config_value('show_number_to_star',1);
				$id_list['right_flag'] = 1;
				if($cfg_show_telno==1)
				{
					$right_flag=UtilDB::checkNumber(array($id), 'sales_trade', $user_id);
					$id_list['right_flag'] = $right_flag==false?0:1;
				}
				if ($id_list['right_flag']==0)
				{
					$res_trade['receiver_mobile']=empty($res_trade['receiver_mobile'])?$res_trade['receiver_mobile']:substr_replace($res_trade['receiver_mobile'],'*****',3,4);
					$res_trade['receiver_telno']=empty($res_trade['receiver_telno'])?$res_trade['receiver_telno']:substr_replace($res_trade['receiver_telno'],'*****',3,4);
				}
				$data=array('total'=>count($res_order_arr),'rows'=>$res_order_arr);
				$point_number = get_config_value('point_number',0);
				$this->assign('point_number',$point_number);
				$this->assign('json_orders',json_encode($data));
				$this->assign('trade',$res_trade);
				$this->assign('list',$list_form);
				$this->assign('logistics',$logistics);
				$this->assign('id_list',$id_list);
				$this->assign('datagrid',$datagrid);
				$this->display('dialog_trade_edit');
			}catch (BusinessLogicException $e){
				$list[]=array('trade_no'=>empty($res_trade['trade_no'])?'':$res_trade['trade_no'],'result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
		}
	}

	public function refundOrder($id)
	{
		$oids=I('post.oids');
		$shop_id=intval(I('post.shop_id'));
		$id=intval($id);
		$user_id=get_operator_id();
		$trade_db=D('SalesTrade');
		try{
			//array('st.trade_id'=>array('eq',$id),'sto.rec_id'=>array('eq',$oid))
// 			$data=$trade_db->refundTrade(array('sto.src_oid'=>array('eq',$oid)),array($id),$user_id);
			$data=$trade_db->refundTrade(array('sto.shop_id'=>array('eq',$shop_id),'sto.src_oid'=>array('in',$oids)),array($id),$user_id);
			if($data['status']!=0)
			{
				$this->error($data['fail'][0]['result_info']);
			}
			$res_trade=$trade_db->getSalesTrade('refund_status,version_id,goods_amount,post_amount,discount,receivable,paid,cod_amount',array('trade_id'=>array('eq',$id)));
			$result=array(
					'status'=>($data['status']==0?1:0),
					'actual_num'=>0,
					'stock_reserved'=>0,
					'refund_status'=>5,
					'remark'=>'退款',
					'trade'=>$res_trade,
					'info'=>$data['fail'][0]['result_info'],
			);
		}catch (BusinessLogicException $e){
			$this->error($e->getMessage());
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function restoreOrder($id)
	{
		$oids=I('post.oids');
		$user_id=get_operator_id();
		$trade_db=D('SalesTrade');
		try{
			$trade_db->restoreOrder(array('sto.trade_id'=>array('eq',$id),'sto.rec_id'=>array('in',$oids)),$user_id);
			$result=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id,actual_num,stock_reserved,refund_status,remark',array('trade_id'=>array('eq',$id),'rec_id'=>array('in',$oids)));
			foreach($result as $k=>$v){
				$result[$v['rec_id']]=$v;unset($result['$k']);
			}
			$res_trade=$trade_db->getSalesTrade('refund_status,version_id,goods_amount,post_amount,discount,receivable,paid,cod_amount',array('trade_id'=>array('eq',$id)));
			$result['status']=1;
			$result['trade']=$res_trade;
		}catch (BusinessLogicException $e){
			$this->error($e->getMessage());
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	// 换货
	public function exchangeOrder($ids)
	{
		$sto_ids=is_json($ids);
		if (IS_POST) 
		{
			$result=array();
			try
			{
				$orders=I('post.order');
				$version_id=I('post.version_id');
				D('SalesTrade')->execute('CALL I_DL_TMP_SUITE_SPEC()');
				$result=D('SalesTrade')->exchangeOrder($sto_ids,$orders,$version_id,get_operator_id());
				$result['info']=$result['info'][0]['result_info'];
			}catch(BusinessLogicException $e)
			{
				$result = array('status'=>1,'info'=>$e->getMessage());
			}catch (\PDOException $e){
				$result = array('status'=>1,'info'=>$e->getMessage());
			}
			$this->ajaxReturn($result);
		}else
		{
			$sales_trade_db=D('SalesTrade');
			$id_list=array(
					'toolbar'=>'trade_exchange_toolbar',
					'form_id'=>'trade_edit_form',
			);
			$datagrid=$sales_trade_db->getDialogView('exchange',$id_list);
			$is_passel=false;
			$this->assign('is_passel',$is_passel);
			$this->assign('id_list',$id_list);
			$this->assign('datagrid',$datagrid);
			$this->display('dialog_trade_exchange');
		}
	}
	//订单批量换货
	public function passelExchange($ids){
		$arr_ids_data=is_json($ids);
		if(IS_POST){
			$result=array();
			try{
				$old_orders=I('post.order');
				$new_orders=I('post.spec');
				$is_scale=I('post.scale');
				$version=I('post.version_id');
				if($is_scale=='true'){
					$is_scale=1;
				}else{
					$is_scale=0;
				}
				$result=D('TradeCheck')->passelExchange($arr_ids_data,$old_orders,$new_orders,$is_scale,$version,get_operator_id());
			}catch (BusinessLogicException $e){
				$result['status']=1;
			}catch (Exception $e){
				$result['status']=1;
			}
			$this->ajaxReturn($result);
		}else{
			$sales_trade_db=D('SalesTrade');
			$list=array();
			try{
				$res_trades_arr=D('TradeCheck')->getSalesTradeList(
						'st.trade_id,st.trade_no,st.platform_id,st.shop_id,sh.shop_name,st.warehouse_id,st.warehouse_type,
						 st.trade_status,st.check_step,st.delivery_term,st.freeze_reason,st.refund_status,st.unmerge_mask,
						 st.customer_id,st.buyer_nick,st.receiver_name,st.receiver_address,st.receiver_mobile,st.receiver_telno,
						 st.receiver_zip,st.receiver_area,st.receiver_ring,st.to_deliver_time,st.dist_center,st.dist_site,
						 st.logistics_id,clg.logistics_name,st.buyer_message,st.cs_remark,st.print_remark,st.salesman_id,
						 he.fullname,st.checker_id,st.checkouter_id,st.flag_id,st.bad_reason,st.is_sealed,st.split_from_trade_id,
						 st.stockout_no,st.revert_reason,st.cancel_reason,st.trade_mask,st.reserve,st.modified,st.created',
						array('st.trade_id'=>array('in',$arr_ids_data['id'])),
						'st',
						'LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id
						 LEFT JOIN hr_employee he ON st.salesman_id=he.employee_id
						 LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id'
				);
				$trade_no=$res_trades_arr[0]['trade_no'];
				$trade_status=$res_trades_arr[0]['trade_status'];
				$warehouse=$res_trades_arr[0]['warehouse_id'];
				if($trade_status!=25&&$trade_status!=30){
					$trade_status=30;
				}
				foreach ($res_trades_arr as $trade){
					if($trade['trade_status']!=$trade_status){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					}
					if($trade['bad_reason']!=0){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'异常订单，请先处理');
					}
					if($trade['freeze_reason']!=0){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'冻结订单，请先解冻');
					}
					if($trade['warehouse_id']!=$warehouse){
						$list[]=array('trade_no'=>$trade_no,'result_info'=>'订单仓库不同');
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单仓库不同');
					}
				}
				if(empty($list)){
					$id_list=array(
							'toolbar'=>'passel_exchange_toolbar',
							'toolbar_order'=>'passel_order_toolbar',
							'form_id'=>'passel_edit_form',
							'exchange_dialog'=>'trade_check_dialog_exchange_order',
					);
					$datagrid=$sales_trade_db->getDialogView('passel_exchange',$id_list);
					$is_pessal=true;
					try{
						$sales_trade_order_arrs=D('SalesTradeOrder')->getCommonOrders($arr_ids_data['id']);
						$sales_trade_order_list=array('total'=>count($sales_trade_order_arrs),'rows'=>$sales_trade_order_arrs);
					}catch (BusinessLogicException $e){
						$sales_trade_order_list=array('total'=>0,'rows'=>array());
					}
					
					$this->assign('sales_trade_order_list',json_encode($sales_trade_order_list));
					$this->assign('warehouse',$warehouse);
					$this->assign('is_passel',$is_pessal);
					$this->assign('ids',$ids);
					$this->assign('id_list',$id_list);
					$this->assign('datagrid',$datagrid);
					$this->display('dialog_trade_exchange');
				}else{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
				}
			}catch(BusinessLogicException $e){
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
		}
	}

	// 批量添加货品
	public function passelAddGoods($ids,$is_gift){
		$arr_ids_data=is_json($ids);
		$is_gift=is_json($is_gift);
		IF(IS_POST){
			$result=array();
			try{			
				$orders=I('post.orders');
				$version=I('post.version_id');	
				$result=D('TradeCheck')->passelAddGoods($arr_ids_data,$orders,$version,get_operator_id());
			}catch (BusinessLogicException $e){
				$result['status']=2;
				$result['info']='未知错误，请联系管理员';
			}catch (Exception $e){
				$result['status']=2;
				$result['info']='未知错误，请联系管理员';
			}
			$this->ajaxReturn($result);
		}else{
			$sales_trade_db=D('SalesTrade');
			$list=array();
			try{
				$res_trades_arr=D('TradeCheck')->getSalesTradeList(
						'st.trade_id,st.trade_no,st.platform_id,st.shop_id,st.warehouse_id,st.warehouse_type,
						 st.trade_status,st.freeze_reason',
						array('st.trade_id'=>array('in',$arr_ids_data['id'])),
						'st'
				);
				$trade_no=$res_trades_arr[0]['trade_no'];
				$trade_status=$res_trades_arr[0]['trade_status'];
				$warehouse=$res_trades_arr[0]['warehouse_id'];
				if($trade_status!=25&&$trade_status!=30){
					$trade_status=30;
				}
				foreach ($res_trades_arr as $trade){
					if($trade['trade_status']!=$trade_status){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单状态不正确');
					}
					if($trade['bad_reason']!=0){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'异常订单，请先处理');
					}
					if($trade['freeze_reason']!=0){
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'冻结订单，请先解冻');
					}
					if($trade['warehouse_id']!=$warehouse){
						$list[]=array('trade_no'=>$trade_no,'result_info'=>'订单仓库不同');
						$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单仓库不同');
					}
				}
				if(empty($list)){
					$id_list=array(
							'toolbar'=>'passel_add_goods_toolbar',
							'add_dialog'=>'trade_check_dialog_add_goods',
					);
					$datagrid=array(
						'id'=>'passel_add_goods',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar']}",
								'pagination'=>false,
								'fitColumns'=>true,
						),
						'fields' => get_field('TradeCheck','exchange')
					);
					$this->assign('is_gift',$is_gift);
					$this->assign('warehouse',$warehouse);
					$this->assign('ids',$ids);
					$this->assign('id_list',$id_list);
					$this->assign('datagrid',$datagrid);
					$this->display('dialog_trade_add_goods');
				}else{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
				}
			}catch(BusinessLogicException $e){
				$list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>$e->getMessage());
				$data=array('total'=>count($list),'rows'=>$list);
				$this->assign('sales_trade_result_info',json_encode($data));
				$this->display('dialog_sales_result_info');
			}
		}
	}

	public function refundTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		$trade_db=D('SalesTrade');
		try{
			/* $res_ids=$trade_db->getSalesTradeList(
						'sto.rec_id',
						 array('st.trade_id'=>array('in',$arr_ids_data)),
						'st',
						'LEFT JOIN sales_trade_order sto ON sto.trade_id=st.trade_id'
			); */
			$data=$trade_db->refundTrade(array('st.trade_id'=>array('in',$arr_ids_data)),$arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'flag_id'=>$data['flag_id'],
					'refund'=>true,
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function clearRevertTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('SalesTrade')->clearRevertTrade($arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'revert_reason'=>0,
					'flag_id'=>0,
					'flag'=>$data['flag'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function freezeTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$arr_form_data=I('post.form','',C('JSON_FILTER'));
		$freeze_reason=0;
		$user_id=get_operator_id();
		if (!empty($arr_form_data))
		{
			$freeze_reason=intval($arr_form_data['reason_id']);
		}
		try{
			$data=D('SalesTrade')->freezeTrade($arr_ids_data,$freeze_reason,$user_id);
			$result=array(
					'status'=>$data['status'],
					'freeze_reason'=>$freeze_reason,
					'flag_id'=>$data['flag_id'],
					'flag'=>$data['flag'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function cancelTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$arr_form_data=I('post.form','',C('JSON_FILTER'));
		$cancel_reason=intval($arr_form_data['reason_id']);
		$user_id=get_operator_id();
		try{
			$data=D('SalesTrade')->cancelTrade($arr_ids_data,$cancel_reason,$user_id);
			$result=array(
					'status'=>$data['status'],
					'cancel_reason'=>$cancel_reason,
					'flag_id'=>$data['flag_id'],
					'trade_status'=>5,
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function clearBadTrade($ids){
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('SalesTrade')->clearBadTrade($arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'bad_reason'=>0,
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	
	public function directConsign($ids){
		if(IS_POST){
			$ids=I('post.ids',C('JSON_FILTER'));
			$info=I('post.info',C('JSON_FILTER'));
			$is_force=I('post.is_force',C('JSON_FILTER'));
			$user_id=get_operator_id();
			$error_info=array();
			$trade_log=array();
			$waybill_ids=array();
			$trade_no=array();
			try{
				if($is_force==0){
					$logistics_no_data=array();
					foreach ($info as $v){
						$logistics_no_data[$v['trade_id']]=$v['logistics_no'];
						$res_dict_arr=M('cfg_logistics')->field('logistics_id AS id,logistics_name AS name')
										->where(array('logistics_id'=>array('in',array($v['logistics_id'],$v['old_logistics']))))
										->select();
						$res_dict_arr=UtilTool::array2dict($res_dict_arr);
						$res_update_trade=D('SalesTrade')->updateSalesTrade($v,array('trade_id'=>array('eq',$v['trade_id'])));
						if($res_update_trade==1){
							$trade_log[]=array(
									'trade_id'=>$v['trade_id'],
									'operator_id'=>$user_id,
									'type'=>20,
									'data'=>0,
									'message'=>'修改物流：从  '.$res_dict_arr[$v['old_logistics']].' 到  '.$res_dict_arr[$v['logistics_id']],
									'created'=>date('y-m-d H:i:s',time()),
							);
						}
						if($v['logistics_no']=='电子面单不支持直接发货'){
							$error_info['status']=2;
							$error_info['info']['total']++;
							$error_info['info']['rows'][]=array(
									'trade_no'=>$v['trade_no'],
									'result_info'=>'电子面单不支持直接发货'
							);
						}
					}
					D('Trade/SalesTradeLog')->addAll($trade_log);
					unset($trade_log);
					//订单审核
					if($ids[0]=='is_json'){
						unset($ids);
						$error_info['status']=1;
					}
					if(count($ids)>0){
					$sql_where=D('TradeCheck')->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('in',$ids)))->select();
					$check_data=D('TradeCheck')->checkTrade($sql_where,$check_type,$user_id,0,1);
					if(count($check_data['success'])==0||(count($check_data['financial'])==count($check_data['success']))){
						$check_data['status']=1;
					}
					//整理审核返回信息
					$error_info['status']=$check_data['status']==0&&$error_info['status']!=''?$error_info['status']:$check_data['status'];
					foreach ($check_data['fail'] as $v){
						$error_info['info']['total']++;
						$error_info['info']['rows'][]=$v;
					}
					$error_info['waybill_error']=array();
					foreach ($check_data['financial'] as $v){
						$error_info['info']['total']++;
						$error_info['info']['rows'][]=array('trade_no'=>$v,'result_info'=>'进入财审');
					}
					$error_info['waybill_error']['total']=0;
					//审核成功的订单添加相应物流单号
					$check_success_ids=array();
					foreach ($check_data['success'] as $v){
						if(!$check_data['financial'][$v['id']]){
							$check_success_ids[]=$v['id'];
						}
					}
					if(count($check_success_ids)>0){
						$waybill_data=D('Stock/StockOutOrder')->getStockoutOrders(
								'stockout_id,src_order_type,src_order_no,src_order_id,warehouse_id,stockout_no',
								array('src_order_id'=>array('in',$check_success_ids))
								);
					}
					$waybill_arr_data=array();
					foreach ($waybill_data as $k=>$v){
						$waybill_ids[]=$v['stockout_id'];
						$trade_no[$v['stockout_id']]=$v['src_order_no'];
						if($logistics_no_data[$waybill_data[$k]['src_order_id']]){
							$waybill_data[$k]['logistics_no']=$logistics_no_data[$waybill_data[$k]['src_order_id']];
						}
						$waybill_arr_data[$v['stockout_id']]=$waybill_data[$k];
					}
					}
				}else if($is_force==1){//继续保存物流单号
					$waybill_data=$info;
					foreach ($waybill_data as $v){
						$waybill_ids[]=$v['stockout_id'];
						$trade_no[$v['stockout_id']]=$v['src_order_no'];
					}
					$error_info['info']=I('post.error_info',C('JSON_FILTER'));
					$error_info['info']['total']>0?$error_info['status']=2:$error_info['status']=0;
				}
				foreach ($waybill_data as $k=>$v){//保存物流单号日志
					$trade_log[]=array(
							'trade_id'=>$waybill_data[$k]['src_order_id'],
							'operator_id'=>$user_id,
							'type'=>21,
							'data'=>0,
							'message'=>'修改物流单号：到'.$waybill_data[$k]['logistics_no'],
							'created'=>date('y-m-d H:i:s',time()),
					);
				}
				if(count($waybill_data)>0){
					$error_info['waybill_error']['total']=0;
					$n=count($waybill_ids);
					$w=array();
					$log=array();
					foreach ($waybill_data as $k=>$v){
						$w[0]=$v;
						$log[0]=$trade_log[$k];
						$add_waybill_result=D('Stock/StockOutOrder')->saveWaybill($w,$log,$is_force);
						unset($w);
						unset($log);
						if($add_waybill_result['status']!=0){
							$error_info['status']==0?$error_info['status']=$add_waybill_result['status']:$error_info['status'];
							foreach ($add_waybill_result['data'] as $v){
								if($is_force==0){
									$error_info['waybill_error']['rows'][]=array(
											'trade_no'=>$v['stock_no'],
											'result_info'=>'保存单号失败：'.$v['msg'],
											'stock_id'=>$v['stock_id'],
									);
									$error_info['waybill_error']['total']++;
									$error_info['continue_save'][]=$waybill_arr_data[$v['stock_id']];
								}else{
									$error_info['info']['total']++;
									$error_info['info']['rows'][]=array(
											'trade_no'=>$v['stockout_id'],
											'result_info'=>'保存单号失败：'.$v['msg'],
									);
								}
								for($i=0;$i<$n;$i++){
									if ($waybill_ids[$i]==$v['stock_id']){
										unset($waybill_ids[$i]);
									}
								}
							}
							$waybill_ids=array_values($waybill_ids);
						}
					}
				}
				//添加物流单号成功的订单，确认发货
				$consign_fail=array();
				$consign_success=array();
				if(count($waybill_ids)>0){
					foreach ($waybill_ids as $k=>$id){
						$consign_success[$k]=array();
						D('Stock/SalesStockOut')->consignStockoutOrder($id,$consign_fail,$consign_success[$k]);
						if(empty($consign_success[$k]))
						{
							unset($consign_success[$k]);
						}
					}
					//整理确认发货返回信息
					if(count($consign_fail)>0){
						$error_info['status']==0?$error_info['status']=2:$error_info['status']==0;
						foreach ($consign_fail as $v){
							$error_info['info']['rows'][]=array(
									'trade_no'=>$trade_no[$v['stock_id']],
									'result_info'=>'确认发货失败：'.$v['msg'],
							);
							$error_info['info']['total']++;
						}
					}
					if(count($consign_success)>0){
						foreach ($consign_success as $v){
							$error_info['info']['rows'][]=array(
									'trade_no'=>$trade_no[$v['id']],
									'result_info'=>'发货成功',
							);
							$error_info['info']['total']++;
						}
					}
				}
				//确认发货成功的订单的物流单打印状态置为已打印
				$consign_success_ids=array();
				foreach ($consign_success as $v){
					$consign_success_ids[]=$v['id'];
				}
				if(count($consign_success_ids)>0){
					D('Stock/StockOutOrder')->updateStockoutOrder(array('logistics_print_status'=>1),array('stockout_id'=>array('in',$consign_success_ids)));
				}
			}catch (BusinessLogicException $e){
				$error_info=array('status'=>1,'message'=>$e->getMessage());
			}catch (\Exception $e){
				$error_info=array('status'=>1,'message'=>$e->getMessage());
			}
			$this->ajaxReturn($error_info);
		}else{
			$sales_trade_model=D('Trade');
			$arr_ids_data=is_json($ids);
			$url=U('TradeCheck/directConsign');
			$sales_trade_data=D('TradeCheck')->getSalesTradeList(
						'st.trade_id,st.trade_no,sh.shop_name,st.receiver_name,st.logistics_no,st.logistics_id,st.logistics_id AS old_logistics',
						array('st.trade_id'=>array('in',$arr_ids_data)),
						'st',
						'LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id '
					);
			$logistics=D("Trade")->query("SELECT logistics_id AS id ,logistics_name AS name ,bill_type AS type FROM cfg_logistics WHERE is_disabled=0 ");
			$logistics_type=UtilTool::array2dict($logistics,'id','type');
			$datagrid=array(
					'id'=>'direct_consign_datagrid',
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
							'title'=>'',
							'toolbar' => "#direct_consign_toolbar",
							'pagination'=>false,
							'fitColumns'=>true,
					),
					'fields' => get_field('TradeCheck','consign')
			);
			$data=array('total'=>count($sales_trade_data),'rows'=>$sales_trade_data);
			$this->assign('direct_consign_trade',json_encode($data));
			$this->assign('logistics',json_encode($logistics));
			$this->assign('logistics_type',json_encode($logistics_type));
			$this->assign('datagrid',$datagrid);
			$this->assign('url',$url);
			$this->display('dialog_direct_consign');
		}
	}
	
	public function deleteTrade($ids)
	{
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('SalesTrade')->deleteTrade($arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'del'=>$data['del'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	//无需系统处理订单
	public function removeTrade($ids){
		if(IS_POST){
			$arr_ids_data=is_json($ids);
			$user_id=get_operator_id();
			$trade_model=D('SalesTrade');
			try{
				$data=$trade_model->removeTrade($arr_ids_data,$user_id);
				$result=array(
						'status'=>$data['status'],
						'remove'=>$data['remove'],
						'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
						'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
				);
			}catch (BusinessLogicException $e){
				$result=array('status'=>1,'message'=>$e->getMessage());
			}
			$this->ajaxReturn($result);
		}else{
			$arr_ids_data=is_json($ids);
			$str_id=implode(',',$arr_ids_data);
			try{
				$trade_model=D('Trade');
				$res_trade_arr=$trade_model->query('SELECT st.trade_id,st.trade_no,st.buyer_nick,cs.shop_name,ap.tid,ap.trade_status FROM sales_trade st 
						LEFT JOIN sales_trade_order sto ON st.trade_id=sto.trade_id 
						LEFT JOIN api_trade ap on ap.tid=sto.src_tid 
						LEFT JOIN cfg_shop cs ON st.shop_id=cs.shop_id WHERE st.trade_id IN ('.$str_id.') GROUP BY ap.tid ORDER BY st.trade_id');
			}catch (\PDOException $e ){
				\Think\Log::write('tradecheck-removeTrade'.$e->getMessage());
			}
			$datagrid=array(
					'id'=>'remove_trade_datagrid',
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
							'title'=>'',
							'pagination'=>false,
							'fitColumns'=>true,
					),
			);
			$data=array('total'=>count($res_trade_arr),'rows'=>$res_trade_arr);
			$this->assign('remove_trade',json_encode($data));
			$this->assign('datagrid',$datagrid);
			$this->assign('ids',json_encode($arr_ids_data));
			$this->display('dialog_trade_remove');
		}
	}
	
	public function getGoodsSpec($goods_id){
		if(IS_POST){
			$result=array();
			try{
				$arr_spec=D('Goods/GoodsSpec')->field('spec_id,spec_name AS id,spec_no,spec_code,retail_price AS price,weight,spec_name AS name')->where(array('goods_id'=>array('eq',$goods_id),'deleted'=>array('eq',0)))->select();
				$result['num']=count($arr_spec);
				$result['spec']=$arr_spec;
			}catch (\PDOException $e){
				\Think\Log::write('tradecheck-getGoodsSpec'.$e->getMessage());
				$result['num']=1;
			}
			$this->ajaxReturn($result);
		}
	}
	//修改订单  type=0：物流，type=1：仓库，type=2：客服备注。
	public  function changeTrades(){
		$data=I('post.data','',C('JSON_FILTER'));
		$trade_ids=$data['ids'];
		$type=$data['type'];
		$version=$data['version'];
		if($type == 2){
			$new_id=$data['new_remark'];
		}else{
			$new_id=intval($data['new_id']);
		}
		$user_id=get_operator_id();
		try{
			$result_data=D('SalesTrade')->changeTrades($trade_ids,$new_id,$type,$version,$user_id);		
			$result=array(
					'change'=>$result_data['change'],
					'status'=>$result_data['status'],
					'info'=>array('total'=>count($result_data['fail']),'rows'=>$result_data['fail']),//失败提示信息
					'data'=>array('total'=>count($result_data['success']),'rows'=>$result_data['success']),//成功的数据
					'type'=>$type,
					'new_name'=>$result_data['new_name'],
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	//校验强制审核密码
	public function forceCheckPwd($pwd){
		$order_check_force_check_pwd_is_open=get_config_value('order_check_force_check_pwd_is_open','0');
		$check_pwd=false;
		if(IS_POST){
			$pwd=is_json($pwd);
			try{
				$order_check_force_check_pwd=get_config_value('order_check_force_check_pwd','');
				if($order_check_force_check_pwd==$pwd){
					$check_pwd=true;
				}
			}catch (\Exception $e){
				\Think\Log::write('tradecheck-forceCheckPwd'.$e->getMessage());
			}
			$this->ajaxReturn($check_pwd);
		}else{
			$this->display('dialog_force_check_pwd');
		}
	}

	//批量修改客服备注显示
	public function showRemarkDialog(){
		$remark_params = array(
			'form_id'		=> 'passel_remark_form',
			'form_url'		=> U('TradeCheck/changeTrades')
		);
		$this->assign("remark_params", $remark_params);
		$this->display('dialog_batch_remark');
	}
	// 赠品未赠原因显示
	public function showGiftDialog(){		
		$trade_id=I('get.id');
		$id_list = array(
                        'form'=>'show_gift_dialog_form',
                        'toolbar'=> 'show_gift_dialog_datagrid_toolbar',
                        'id_datagrid' => 'show_gift_dialog_datagrid',
                );		
        $datagrid=array(
                    'id'=>$id_list['id_datagrid'],
                    'style'=>'',
                    'class'=>'easyui-datagrid',
                    'options'=> array(
                            'title'=>'',
                            'style'=>'',
                            'toolbar' => "#{$id_list['toolbar']}",
                            'url'   => U('TradeCheck/getGiftNotSendReason', array('trade_id'=>$trade_id)),
                            'singleSelect'=>true,
                            'pagination'=>false,
                            'fitColumns'=>true,
                            'rownumbers'=>true
                    ),
                    'fields' => get_field('TradeCheck','gift_not_send_reason')
            );
        $params=array(
				'datagrid'=>array('id'=>$id_list['id_datagrid']),
				'search'=>array('form_id'=>$id_list['form']),
		);
        $shop_list[] = array("id" => "all", "name" => "全部");
        $list_form=UtilDB::getCfgList(array('shop'));
        $list_form=array_merge($shop_list, $list_form["shop"]);
        $this->assign('shop',$list_form);
        $this->assign('id_list', $id_list);
        $this->assign('datagrid',$datagrid);
        $this->assign("params",json_encode($params));
		$this->display('dialog_gift_not_send_reason');
	}
	public function getGiftNotSendReason($search=array())
	{	
		try {			
			$trade_id=I('get.trade_id'); 
	        $data=D('tradeCheck')->getGiftNotSendReason($trade_id,$search);  
		}catch (\Exception $e){
			$data=array();
			\Think\Log::write('tradecheck-getGiftNotSendReason'.$e->getMessage());
		}          
        $this->ajaxReturn($data);
	}
	
	//深度拆分
	public function deepSplit($ids){
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('TradeCheck')->deepSplit($arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}

    //	按组合装深度拆分
	public function deepSplitBySuite($ids){
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('TradeCheck')->deepSplitBySuite($arr_ids_data,$user_id);
			$result=array(
				'status'=>$data['status'],
				'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	// 一键拆分合并单
	public function splitMergeTrade($ids){
		$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		try{
			$data=D('TradeCheck')->splitMergeTrade($arr_ids_data,$user_id);
			$result=array(
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
			);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}

	// 根据原始单号筛选订单
	public function searchSrcTids(){
		if (IS_POST) {
			$data=I('post.data','',C('JSON_FILTER'));	
			$src_tids_str=$data['passel_src_tids'];
			$src_tids_arr=array();
			switch ($data['separator']) {
				case '0':
					$src_tids_arr=explode("\r\n",$src_tids_str);
					$src_tids_str=preg_replace('/[\r\n]+/', ',', $src_tids_str);
					break;
				case '1':
					$src_tids_arr=explode(' ',$src_tids_str);
					$src_tids_str=preg_replace('/ +/', ',', $src_tids_str);
					break;
				case '2':
					$src_tids_arr=explode(',',$src_tids_str);
					$src_tids_str=preg_replace('/,+/', ',', $src_tids_str);
					break;
				case '3':
					$src_tids_arr=explode(';',$src_tids_str);
					$src_tids_str=preg_replace('/;+/', ",", $src_tids_str);
					break;
				default:
					$src_tids_str='';
					break;
			}
			$src_tids_str=rtrim($src_tids_str,",");
			$result_str='';$result=array();
			$result=explode(',',$src_tids_str);			
			foreach ($result as $v) {
				$result_str.="'".$v."',";
			}
			$result_str=rtrim($result_str,",");
			if(count($src_tids_arr)>200){
				$result=array('status'=>1,'info'=>'原始单号不能多于200条');				
			}else{
				$result=array('status'=>0,'info'=>$result_str);
			}			
			$this->ajaxReturn($result);		
		}else{
			$src_tids_params = array(
				'form_id'		=> 'search_src_tids_form',
				'form_url'		=> U('TradeCheck/searchSrcTids')
			);
			$this->assign("src_tids_params", $src_tids_params);
			$this->display('dialog_search_src_tids');
		}
	}
	// 下载模板--导入原始单号
	public function downloadTemplet(){
        $file_name = "原始单号导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }
    
	// 导入原始单号
	public function importSrcTids(){
		if(!self::ALLOW_EXPORT){
			 $res["status"] = 1;
			 $res["info"]   = self::EXPORT_MSG;
			 $this->ajaxReturn(json_encode($res), "EVAL");
		 }
		//获取Excel表格相关的数据
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
       	//读取表格数据
      	 try {      
      	 	$excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"TradeCheckImport");
            $count = $excelClass->getExcelCount();
            if($count>200){
                SE("最多导入200条查询");
            }
            $excelData = $excelClass->Excel2Arr($count);	 	        
        } catch (\Exception $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
            $this->ajaxReturn(json_encode($res), "EVAL");
            \Think\Log::write($e->getMessage());    
        }
        $err_msg = array(); // 记录插入数据的错误信息
        $tids=array();
        $src_tids='';
        foreach ($excelData as $sheet) { 
        	if($sheet[0][0]!='原始单号'){
                $res = array("status" => 1, "info" => '模板不正确，请重新下载模板');
                $this->ajaxReturn(json_encode($res), "EVAL");
            }
        	for ($k = 1; $k < count($sheet); $k++) {
        		 $row = $sheet[$k];
        		 if (UtilTool::checkArrValue($row)) continue;
        		 // 获取一条商品信息
        		 $i 	   = 0;        		 
        		 $tids[$k] = trim($row[$i++]);
        		 $src_tids.= $tids[$k].',';
	        }
        }
        $res = array("status" => 0, "info" => $src_tids);
        $this->ajaxReturn(json_encode($res), "EVAL");
	}

	// 一键合并且审核
	public function mergeAndCheckTrade($id){
		try{
			$data=D('TradeCheck')->mergeAndCheckTrade($id);
			$result=array(
					'check'=>$data['check'],
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);			
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}

	public function SMS() {
		if (IS_POST) {
			try{
				$res['status']=0;
				$res['info'] ='success';
				$sms     = I("post.sms");
				$ids = $sms['ids'];
				$message = $sms["message"];
				$data=D('Trade/TradeCheck')->getSMSData($ids);
				$templet=array(
						'{客户网名}','{客户姓名}','{原始单号}','{店铺名称}',
						'{物流单号}','{物流公司}','{下单时间}','{发货时间}',
						'{收货地区}','{收货地址}'
				);
				foreach($data as $row) {
					$data = array(
							$row['buyer_nick'], $row['receiver_name'], $row['src_tids'], $row['shop_name'],
							$row['logistics_no'], $row['logistics_name'], $row['trade_time'], $row['consign_time'],
							$row['receiver_area'], $row['receiver_address']
					);
					$send_msg = str_replace($templet, $data, $message);
					if (strpos($send_msg, "{")) {
						$return = array(
								'status' => 1,
								'info' => '该模板需要信息不完全，请更换其他模板！'
						);
						$this->ajaxReturn($return);
					}
					$id = D('Customer/CustomerFile')->addSMSList($row['receiver_mobile'], $send_msg);
					$msg = UtilTool::SMS($row['receiver_mobile'], $send_msg, '');
					D('Customer/CustomerFile')->updateSMSStatus($id, $msg);
					$res = array(
							'status' => $msg['status'],
							'info' => $msg['info']
					);
				}
			}catch (BusinessLogicException $e){
				$res['status']= 1;
				$res['info'] = $e->getMessage();
			}catch(\Exception $e){
				$res['status']= 1;
				$res['info'] = $e->getMessage();
			}
			$this->ajaxReturn($res);

		} else {
			$id_list  = array(
					"datagrid" => "trade_check_sms_datagrid",
					"list"     => "trade_check_sms_list",
					"message"  => "trade_check_sms_message",
					"show"     => "trade_check_sms_datagrid",
					"dialog"   => "sms_trade_check"
			);
			$datagrid = array(
					"id"      => $id_list["datagrid"],
					"options" => array(
							"pagination" => false,
							"fitColums"  => false,
							"rownumbers" => false,
							"border"     => true
					),
					"fields"  => D('Setting/UserData')->getDatagridField('Setting/SmsTemplate','SMS')
			);
			$template[] = array('id'=>'无','name'=>'无');
			$template_res = UtilDB::getCfgList(array("sms_template"));
			$template = array_merge($template,$template_res["sms_template"]);
			$this->assign('template',$template);
			$this->assign("id_list", $id_list);
			$this->assign("datagrid", $datagrid);
			$this->display("dialog_sms");
		}
	}

	//重算赠品
	public function recalculationGift(){
		$data=I('post.data','',C('JSON_FILTER'));
		$trade_ids=$data['id'];
		$version=$data['version'];
		$user_id=get_operator_id();
		try{
			$result=D('Trade/TradeCheck')->recalculationGift($trade_ids,$version,$user_id);
		}catch (BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}

	//回传备注与标旗
	public function uploadRemarkAndFlag(){
		IF(IS_POST){
			$data=I('post.data','',C('JSON_FILTER'));
			$trade_ids=$data['ids'];
			$version=$data['version'];
			$remark=$data['cs_remark'];
			$flag=$data['flag'];
			$user_id=get_operator_id();
			try{
				$data=D('Trade/TradeCheck')->uploadRemarkAndFlag($trade_ids,$remark,$flag,$user_id);
				$result=array(
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
				);
			}catch (BusinessLogicException $e){
				$result=array('status'=>1,'message'=>$e->getMessage());
			}catch (\Exception $e){
				$result=array('status'=>1,'message'=>$e->getMessage());
			}
			$this->ajaxReturn($result);
		}else{
			$upload_remark_flag_params = array(
				'form_id'		=> 'upload_remark_and_flag_form',
				'form_url'		=> U('TradeCheck/uploadRemarkAndFlag')
			);
			$this->assign("upload_remark_flag_params", $upload_remark_flag_params);
			$this->display('dialog_upload_remark_and_flag');
		}

	}

	//获取选择包含货品列表
	public function getDialogIncludeGoodsList(){
		$type = I('get.type');
		$id_list  = [
			"datagrid"      => "check_include_goods_list_datagrid",
			"toolbar"       => "check_include_goods_list_toolbar",
			"form"          => "check_include_goods_list_form",
			"add_spec"      => "check_include_show_dialog",
			"add_suite"      => "check_include_add_suite_dialog",
		];
		if($type == 'not_include'){
			foreach($id_list as $k => $v){
				if($k != 'add_spec'&&$k != 'add_suite')
					$id_list[$k] = 'not_' . $v;
			}
		}
		$fields = get_field("Stock/StockSalesPrint", 'include_goods');
		$checkbox=array('field' => 'ck','checkbox' => true);
		array_unshift($fields,$checkbox);
		$datagrid = [
			'id'      => $id_list["datagrid"],
			'style'=>'',
			'class'=>'easyui-datagrid',
			'options'   => array(
				'title'         =>  '',
				'toolbar'       =>  "#{$id_list['toolbar']}",
				'fitColumns'    =>  true,
				'singleSelect'  =>  false,
				'ctrlSelect'    =>  true,
				'pagination'    =>  false,
			),
			"fields"  => $fields
		];
		$params = $id_list;
		$this->assign("id_list", $id_list);
		$this->assign('datagrid', $datagrid);
		$this->assign('type', $type);
		$this->assign('params',json_encode($params));
		$this->display('include_goods_list');
	}

}