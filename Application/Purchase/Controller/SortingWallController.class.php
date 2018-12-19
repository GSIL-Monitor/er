<?php
namespace Purchase\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Think\Log;

class SortingWallController extends BaseController{
	
	public function show(){
		$sys_config =  get_config_value(array('dynamic_allocation_box'),array(0));
		if($sys_config['dynamic_allocation_box'] == 0){
			try{
				$id_list = array();
				$need_ids = array('form','toolbar','datagrid','add','edit');
				$this->getIDList($id_list,$need_ids,'','');
				$datagrid = array(
					'id'=>$id_list['datagrid'],
					'options'=>array(
						'url'=>U('SortingWall/search'),
						'toolbar'=>$id_list['toolbar'],
						'fitColumns'=>false,
						"rownumbers" => true,
						"pagination" => true,
						'singleSelect'=>false,
						'ctrlSelect'=>true,
						"method"     => "post",
					),
					'fields'=>get_field('SortingWall','sortingwall'),
				);
				$checkbox=array('field' => 'ck','checkbox' => true);
				array_unshift($datagrid['fields'],$checkbox);
				$params = array(
					'datagrid'=>array(
						'id'=>$id_list['datagrid'],
					),
					'search'=>array(
						'form_id'=> $id_list['form'],
					),
					'add' 		=> array(
						'id' => $id_list['add'],
						'url' => U('Purchase/SortingWall/addSortingWall'),
						'title' => '新建拣货墙',
						'width'  =>  '1000',
						'height' =>  '600',
						'ismax'	 =>  false
					),
					'edit'		=>array(
						'id'=>$id_list['edit'],
						'url'=>U('Purchase/SortingWall/editSortingWall'),
						'title'=>'编辑拣货墙',
						'width'  =>  '1000',
						'height' =>  '600',
						'ismax'	 =>  false
					),
					'delete'   	=> array(
						'url' => U('Purchase/SortingWall/delSortingWall')
					),
				);
				$sorting_wall_type = array(
					array('id' => 'all', 'name' => '全部'),
					array('id' => '1', 'name' => '分拣墙'),
					array('id' => '0', 'name' => '缺货墙'),
				);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
			}
			$this->assign('params',json_encode($params));
			$this->assign('datagrid',$datagrid);
			$this->assign('id_list',$id_list);
			$this->assign('sorting_wall_type',$sorting_wall_type);
			$this->display('show');
		}else{
			try{
				$id_list = array();
				$need_ids = array('form','toolbar','datagrid','add','edit');
				$this->getIDList($id_list,$need_ids,'','dynamic');
				$datagrid = array(
					'id'=>$id_list['datagrid'],
					'options'=>array(
						'url'=>U('SortingWall/dynamic_search'),
						'toolbar'=>$id_list['toolbar'],
						'fitColumns'=>false,
						"rownumbers" => true,
						"pagination" => true,
						'singleSelect'=>false,
						'ctrlSelect'=>true,
						"method"     => "post",
					),
					'fields'=>get_field('SortingWall','dynamic'),
				);
				$checkbox=array('field' => 'ck','checkbox' => true);
				array_unshift($datagrid['fields'],$checkbox);
				$params = array(
					'datagrid'=>array(
						'id'=>$id_list['datagrid'],
					),
					'search'=>array(
						'form_id'=> $id_list['form'],
					),
					'add' 		=> array(
						'id' => $id_list['add'],
						'url' => U('Purchase/SortingWall/addDynamic'),
						'title' => '新建拣货墙',
						'width'  =>  '300',
						'height' =>  '260',
						'ismax'	 =>  false
					),
					'edit'		=>array(
						'id'=>$id_list['edit'],
						'url'=>U('Purchase/SortingWall/editDynamic'),
						'title'=>'编辑拣货墙',
						'width'  =>  '300',
						'height' =>  '260',
						'ismax'	 =>  false
					),
					'delete'   	=> array(
						'url' => U('Purchase/SortingWall/delDynamic')
					),
				);
				$sorting_wall_type = array(
					array('id' => 'all', 'name' => '全部'),
					array('id' => '1', 'name' => '分拣墙'),
					array('id' => '0', 'name' => '缺货墙'),
				);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
			}
			$this->assign('params',json_encode($params));
			$this->assign('datagrid',$datagrid);
			$this->assign('id_list',$id_list);
			$this->assign('sorting_wall_type',$sorting_wall_type);
			$this->display('dynamic_show');
		}
		
	}


	public function search($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
			try{
				$result = D('SortingWall')->search($page,$row,$search,$sort,$order);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result = array('rows'=>array(),'total'=>0);
			}
			$this->ajaxReturn($result);
	}

	public function addSortingWall(){
		try{
			$need_ids = array('add_form','add_id');
			$this->getIDList($id_list,$need_ids,'','');
			$sorting_wall_info=array('id'=>0);
			$dialog_list=array('form'=>$id_list['add_form'],'id'=>$id_list['add_id']);
			$sorting_wall_type = array(
				array('id' => '1', 'name' => '分拣墙'),
				array('id' => '0', 'name' => '缺货墙'),
			);
			$sorting_wall_no = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$this->assign('sorting_wall_type', $sorting_wall_type);
			$this->assign('sorting_wall_no', $sorting_wall_no);
			$this->assign('sorting_wall_info', json_encode($sorting_wall_info));
			$this->assign('dialog_list',$dialog_list);
			$this->assign('dialog_list_json',json_encode($dialog_list));
			$this->display('dialog_sorting_wall_edit');
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
	}

	public function editSortingWall($id){
		$id=intval($id);
		try{
			$sorting_wall_info=D('SortingWall')->getEditSortingWallData($id);
			$sorting_wall_type = array(
				array('id' => '1', 'name' => '分拣墙'),
				array('id' => '0', 'name' => '缺货墙'),
			);
			$sorting_wall_no = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$this->assign('sorting_wall_type', $sorting_wall_type);
			$this->assign('sorting_wall_no', $sorting_wall_no);
			$this->getIDList($id_list,array('edit_form','edit_id'),'','');
			$dialog_list=array('form'=>$id_list['edit_form'],'id'=>$id_list['edit_id']);
			$this->assign('sorting_wall_info',json_encode($sorting_wall_info));
			$this->assign('dialog_list',$dialog_list);
			$this->assign('dialog_list_json',json_encode($dialog_list));
			$this->display('dialog_sorting_wall_edit');
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
	}

	public function saveSortingWall(){
		$data = I('post.');
		$result=array('status'=>0,'info'=>"保存成功");
		$data['wall_id'] = $data['id'];
		try{
			$result=D('SortingWall')->saveSortingWall($data);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}

	public function delSortingWall($id){
		$result=array('status'=>0,'info'=>"删除成功");
		try{
			$sorting_wall_info=D('SortingWall')->delSortingWall($id);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}

	public function getSortingBoxGoodsList(){
		try{
			$id_list = array();
			$need_ids = array('form','toolbar','tab_container','datagrid','more_content','box_goods_trans','dialog','trade_edit');
			$this->getIDList($id_list,$need_ids,'','box');
			$stockout_no 	= I('get.stockout_no');
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'url'=>U('SortingWall/getSortingBoxGoodsDetail').'?stockout_no='.$stockout_no,
					'toolbar'=>$id_list['toolbar'],
					'fitColumns'=>false,
					"rownumbers" => true,
					"pagination" => true,
					'singleSelect'=>false,
					'ctrlSelect'=>true,
					"method"     => "post",
				),
				'fields'=>get_field('SortingWall','sortingbox'),
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			array_unshift($datagrid['fields'],$checkbox);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'tabs'=>array(
					'id'=>$id_list['tab_container'],
					'url'=>U('Purchase/PurchaseCommon/showTabDatagridData'),
				),
				'box_goods_trans'=>array(
					'id'     =>  $id_list['box_goods_trans'],
					'trans_id'=>  'sortingwall_datagrid_boxtrans',
					'title'  =>  '货品移位',
					'url'    =>  U('SortingWall/boxGoodsTransDialog'),
					'width'  =>  '650',
					'height' =>  '500',
					'ismax'  => false
				),
				'trade_edit'		=>array(
					'id'=>$id_list['trade_edit'],
					'url'=>U('SortingWall/editTrade').'?datagrid_id='.$id_list['datagrid'],
					'title'=>'订单编辑',
					'width'  =>  '840',
					'height' =>  '450',
					'ismax'	 =>  false
				),
				'id_list'=>$id_list,
			);
			$arr_tabs = array(
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"sorting_box_detail")).'?prefix=sortingwall&tab=sorting_box_detail&app=Purchase/SortingWall',"id"=>$id_list['tab_container'],"title"=>"分拣框货品详情"),
				//array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"sorting_box_log")).'?prefix=sortingwall&tab=sorting_box_log&app=Purchase/SortingWall',"id"=>$id_list['tab_container'],"title"=>"日志"),
			);
			$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
			if($dynamic_allocation_box==1){
				$list = M('cfg_dynamic_box')->field('wall_id AS id,wall_no AS name')->select();
			}else{
				$list = M('cfg_sorting_wall')->field('wall_id AS id,wall_no AS name')->select();
			}	
			$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->assign('params',json_encode($params));
		$this->assign('arr_tabs',json_encode($arr_tabs));
		$this->assign('datagrid',$datagrid);
		$this->assign('dynamic_allocation_box',$dynamic_allocation_box);
		$this->assign('id_list',$id_list);
		$this->assign('list',$list);
		$this->display('sorting_box_goods_list');

	}
	public function editTrade($trade_no){
		if(IS_POST)
		{
			$arr_form_data=I('post.info','',C('JSON_FILTER'));
			$arr_orders_data=I('post.orders','',C('JSON_FILTER'));
			$arr_id_data=I('post.id','',C('JSON_FILTER'));
			$arr_form_data['trade_id']=intval($arr_id_data);
			$trade_db=D('Trade/SalesTrade');
			$stockout_order_db=D('Stock/StockOutOrder');

			$user_id=get_operator_id();
			try {
				//查看修改号码权限
				$show_number_to_star=get_config_value('show_number_to_star',0);
				if($show_number_to_star==1&&!UtilDB::checkNumber(array($arr_id_data), 'sales_trade', $user_id,null,2))
				{
					unset($arr_form_data['receiver_mobile']);
					unset($arr_form_data['receiver_telno']);
				}
				//订单表单校验
				$trade_db->validateTrade($arr_form_data);

				$trade_status = $trade_db->field('trade_status')->where(array('trade_id'=>$arr_id_data))->find();
				$trade_status = $trade_status['trade_status'];
				$trade_db->save(array('trade_id'=>$arr_id_data,'trade_status'=>30));
				$trade_db->editTrade($arr_form_data,$arr_orders_data,$user_id);
				$trade_db->save(array('trade_id'=>$arr_id_data,'trade_status'=>$trade_status));
				$stockout_data = array(
					'warehouse_id'	=> $arr_form_data['warehouse_id'],
					'logistics_id'	=> $arr_form_data['logistics_id'],
					'calc_post_cost'	=> $arr_form_data['post_cost'],
					'receiver_name'	=> $arr_form_data['receiver_name'],
					'receiver_mobile'	=> $arr_form_data['receiver_mobile'],
					'receiver_telno'	=> $arr_form_data['receiver_telno'],
					'receiver_province'	=> $arr_form_data['receiver_province'],
					'receiver_city'	=> $arr_form_data['receiver_city'],
					'receiver_district'	=> $arr_form_data['receiver_district'],
					'receiver_dtb'	=> $arr_form_data['receiver_dtb'],
					'receiver_address'	=> $arr_form_data['receiver_address'],
					'receiver_zip'	=> $arr_form_data['receiver_zip'],
					'receiver_area'	=> $arr_form_data['receiver_area'],
				);
				$stockout_order_db->where(array('src_order_id'=>$arr_id_data))->save($stockout_data);
				unset($trade_db);
				unset($stockout_order_db);
				unset($arr_form_data);
			}catch (BusinessLogicException $e)
			{
				$this->error($e->getMessage());
			}catch (Exception $e){
				$this->error($e->getMessage());
			}
			$this->success('保存成功');
		}else
		{
			$sales_trade_model=D('Trade/SalesTrade');
			$list=array();
			$user_id=get_operator_id();
			$res_trade=array();

			try {
				if($trade_no == '无')
				{
					SE('无效订单');
				}
				$res_trade=$sales_trade_model->getSalesTrade(
					'st.trade_id AS id,st.trade_no, st.trade_from, st.version_id, st.src_tids, st.shop_id, st.trade_status, st.warehouse_id,
						 st.logistics_id, st.salesman_id, st.trade_type, st.invoice_type, st.invoice_title, st.invoice_content,
						 st.delivery_term, st.buyer_nick, st.receiver_name, st.receiver_mobile, st.receiver_telno,
						 st.receiver_province, st.receiver_city, st.receiver_district,  st.receiver_address, st.receiver_area,
						 st.receiver_address, st.receiver_dtb, st.receiver_zip, st.goods_amount, st.post_amount, st.discount, st.post_cost,
						 st.receivable, st.paid, st.dap_amount, st.cod_amount, st.commission, st.pay_account, st.buyer_message,
						 st.cs_remark, st.print_remark, st.freeze_reason, cs.shop_name, cs.is_disabled AS dis1,
						 cl.logistics_name, cl.is_disabled AS dis2, cw.name AS warehouse_name, cw.is_disabled AS dis3 ',
					array('st.trade_no'=>array('eq',$trade_no)),
					'st',
					'LEFT JOIN cfg_shop cs ON cs.shop_id=st.shop_id
						 LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id
						 LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=st.warehouse_id '
				);
				$id = $res_trade['id'];
				$datagrid_id=I('get.datagrid_id');
				if(!$sales_trade_model->getEditCheckInfo($res_trade,$list,$user_id))
				{
					$data=array('total'=>count($list),'rows'=>$list);
					$this->assign('sales_trade_result_info',json_encode($data));
					$this->display('dialog_sales_result_info');
				}
//				if($res_trade['trade_status']!=30 && $res_trade['trade_status']!=25)
//				{
//					SE('订单非待审核状态，不可编辑');
//				}
				$id_list=array(
					'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
					'toolbar'=>'trade_edit_toolbar',
					'form_id'=>'trade_edit_form',
					'add_spec'=>'trade_edit_add_spec',
					'datagrid_id'=>$datagrid_id,
//					'url_refund'=>U('SortingWall/refundOrder'),
//					'url_restore'=>U('SortingWall/restoreOrder'),
					'url_exchange'=>U('SortingWall/exchangeOrder'),
//					'url_get_spec'=>U('SortingWall/getGoodsSpec'),//获取货品的多规格
				);
				$datagrid=array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
						'title'=>'',
						'toolbar' => "#{$id_list['toolbar']}",
						'pagination'=>false,
						'fitColumns'=>false,
//						'methods'=>'onEndEdit:endEditTrade,onBeginEdit:beginEditTrade,rowStyler:editTradeRowStyler',
						//'methods'=>'onEndEdit:tradeCheck.endEditTrade,onBeginEdit:tradeCheck.beginEditTrade',
					),
						'fields' => D('Setting/UserData')->getDatagridField('StallsOrder','edit')
					);

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

				$res_order_arr=D('Trade/SalesTradeOrder')->getSalesTradeOrderList(
					"sto.rec_id AS id,sto.rec_id AS sto_id,sto.trade_id,sto.spec_id,sto.platform_id,sto.shop_id,sto.src_oid,sto.suite_id,
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

	public function getSortingBoxGoodsDetail($page = 1,$rows = 20,$search = array(),$sort = 'id',$order = 'desc'){
		try{
			$stockout_no 	= I('get.stockout_no');
			if(!empty($stockout_no)  && empty($search)){
				$search['stockout_no'] = $stockout_no;
			}
			$result = D('SortingWall')->getSortingBoxGoodsDetail($page,$rows,$search,$sort,$order);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('rows'=>array(),'total'=>0);
		}
		$this->ajaxReturn($result);
	}

	public function boxGoodsTransDialog(){
		if(IS_POST){
			try{
				$wall_no = I('get.wall_no','',C('JSON_FILTER'));
				$box_nos = I('get.box_nos','',C('JSON_FILTER'));
				$result = D('SortingWall')->getBoxGoodsInfo($box_nos);
			}catch (BusinessLogicException $e){
				$result = array('total'=>0,'rows'=>array());
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result = array('total'=>0,'rows'=>array());
			}
			$this->ajaxReturn($result);
		}else{
			$form_data = array();
			$wall_no = I('get.wall_no','',C('JSON_FILTER'));
			$box_nos = I('get.box_nos','',C('JSON_FILTER'));
			$fields = get_field('SortingWall','boxgoodstrans');
			$form_data = M('cfg_sorting_wall')->field('wall_no,type')->select();
			foreach($form_data as $v){$form_data_map[$v['wall_no']] = $v['type']==1?'分拣墙':'缺货墙';}
			$params_url = '?wall_no=' . $wall_no . '&box_nos=' . $box_nos;
			$id_list = self::getIDList($id_list,array('form','tool_bar','datagrid'),'','boxtrans');
			$params = array(
				'datagrid'=>array('id'=>$id_list['datagrid']),
				'form'=>array('id'=>$id_list['form'])
			);
			$datagrid = array(
				'id' => $id_list['datagrid'],
				'options' => array(
					'title' => '',
					'url' => U('SortingWall/boxGoodsTransDialog').$params_url,
					'toolbar' => "#{$id_list['tool_bar']}",
					'fitColumns' => false,
					'pagination' => false,
				),
				'fields' => $fields,
				'class' => 'easyui-datagrid',
			);
		}
		$old_wall_no = array('old_wall_no'=>$wall_no);
		$this->assign('id_list',$id_list);
		$this->assign('params',json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign('old_wall_no',json_encode($old_wall_no));
		$this->assign('form_data',$form_data);
		$this->assign('form_data_map',json_encode($form_data_map));
		$this->display('dialog_box_goods_trans');
	}
	public function getAllWallBoxByNo($new_wall_no){
		try{
			$result = D('SortingWall')->getAllWallBoxByNo($new_wall_no);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array();
		}
		$this->ajaxReturn(json_encode($result));
	}
	public function submitBoxGoodsTrans(){
		$data = I('post.data');
		$result=array('status'=>0,'info'=>"移动成功");
		try{
			$result=D('SortingWall')->submitBoxGoodsTrans($data);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}
	public function getHasUseBoxByWallNo($wall_no){
		try{
			$result=D('SortingWall')->getHasUseBoxByWallNo($wall_no);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}
	public function consignStockoutOrder()
	{
		try{
			set_time_limit(0);
			$consign_info = I('post.ids');
			$is_force = I('post.is_force');
			$result = array(
				'status'=>0,
				'info'=>'success',
				'data'=>array()
			);
			$consign_info = explode(',',$consign_info);
			$fail = array();
			$success = array();
			$stockout_ids = array();
			$box_goods_detail_model = M('box_goods_detail');
			$sorting_wall_detail_model = M('sorting_wall_detail');
			if($is_force==0){
				$stockout_ids = $box_goods_detail_model->fetchSql(false)->alias('bgd')->field('so.stockout_id')->join('LEFT JOIN sorting_wall_detail swd ON swd.box_no=bgd.box_no')->join('LEFT JOIN stockout_order so ON so.src_order_id=bgd.trade_id')->where(array('swd.box_id'=>array('in',$consign_info),'bgd.sort_status'=>array('eq',0)))->group('so.stockout_id')->select();
				if(empty($stockout_ids)){
					$stockout_ids = $sorting_wall_detail_model->fetchSql(false)->alias('swd')->field('swd.stockout_id')->where(array('swd.box_id'=>array('in',$consign_info)))->select();
				}
			}else{
				foreach($consign_info as $id){
					$stockout_ids[]['stockout_id'] = $id;
				}
				//$stockout_ids[0]['stockout_id'] = $consign_info;
			}
			if(empty($stockout_ids)||empty($stockout_ids[0]['stockout_id']))
			{
				$result['info'] ="未查询到订单信息，请刷新后重试";
				$result['status'] = 1;
				$this->ajaxReturn($result);
			}
			foreach ($stockout_ids as $key=>$id)
			{
				$success[$key] = array();
				D('Stock/SalesStockOut')->consignStockoutOrder($id['stockout_id'],$fail,$success[$key],$is_force);
				if(empty($success[$key]))
				{
					unset($success[$key]);
				}
			}
			if(!empty($fail))
			{
				$result['status']=2;
			}
			$result['data']=array(
				'fail' => $fail,
				'success' => $success
			);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function printGoods(){
		try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'sorting_wall_goods_info';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','缺货明细_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
            $this->assign('dialog_div',$dialog_div);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
		$this->display('print_goods');
	}
	public function oneSplit(){
		$split_ids = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
		$info = array();
        if(empty($split_ids))
        {
            $result['info'] ="请选择分拣框";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        foreach ($split_ids as $key=>$id)
        {
            $success[$key] = array();
            $info[$key] = D('Purchase/StallsOrderDetail')->oneSplit($id,$fail);
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
		$result['stock_id'] = $info; 
        $result['data']=array(
            'fail' => $fail,
        );
        $this->ajaxReturn($result);
	}
	public function boxRelease(){
		$box_ids = I('post.box_ids');
		$data = array(
			'status'=>0,
			'info'=>'释放成功',
		);
		try{
			$data=D('SortingWall')->boxRelease($box_ids);
		}catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
		}
		$this->ajaxReturn($data);
	}
//	换货
	public function exchangeOrder($id){
		$id=intval($id);
		if (IS_POST)
		{
			$result=array();
			try
			{
				$orders=I('post.order');
				D('StallsOrderDetail')->execute('CALL I_DL_TMP_SUITE_SPEC()');
				$result=D('StallsOrderDetail')->exchangeOrder(array($id),$orders,get_operator_id());
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
			$id_list=array(
				'toolbar'=>'box_trade_exchange_toolbar',
				'form_id'=>'box_trade_edit_form',
			);
			$datagrid['spec']=array(
				'id'=>'box_exchange_spec',
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
					'title'=>'',
					'toolbar' => "#{$id_list['toolbar']}",
					'pagination'=>false,
					'fitColumns'=>true,
				),
				'fields' => get_field('StallsOrder','exchange')
			);
			$datagrid['order']=array(
				'id'=>'box_exchange_order',
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
					'title'=>'',
					// 'toolbar' => "#{$id_list['toolbar_order']}",
					'pagination'=>false,
					'fitColumns'=>false,
				),
				'fields' => get_field('StallsOrder','order')
			);
			$is_passel=false;
			$this->assign('is_passel',$is_passel);
			$this->assign('id_list',$id_list);
			$this->assign('datagrid',$datagrid);
			$this->display('dialog_trade_exchange');
		}
	}
	public function getSortingBoxIsFinish($value=0){
		$result['value'] = $value;
		if($value==0){
			$use_box_count = M('sorting_wall_detail')->where('is_use',1)->count();
			$result['count'] = $use_box_count;
		}else{
			$use_big_box_count = M('big_box_goods_map')->count();
			$result['count'] = $use_big_box_count;
		}
		$this->ajaxReturn($result);
	}
	
	
	public function dynamic_search($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
			try{
				$result = D('SortingWall')->dynamic_search($page,$row,$search,$sort,$order);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result = array('rows'=>array(),'total'=>0);
			}
			$this->ajaxReturn($result);
	}

	public function addDynamic(){
		try{
			$need_ids = array('add_form','add_id');
			$this->getIDList($id_list,$need_ids,'','dynamic');
			$sorting_wall_info=array('id'=>0);
			$dialog_list=array('form'=>$id_list['add_form'],'id'=>$id_list['add_id']);
			$sorting_wall_type = array(
				array('id' => '1', 'name' => '分拣墙'),
				array('id' => '0', 'name' => '缺货墙'),
			);
			$sorting_wall_no = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$this->assign('sorting_wall_type', $sorting_wall_type);
			$this->assign('sorting_wall_no', $sorting_wall_no);
			$this->assign('dynamic_info', json_encode($sorting_wall_info));
			$this->assign('dialog_list',$dialog_list);
			$this->assign('dialog_list_json',json_encode($dialog_list));
			$this->display('dialog_dynamic_edit');
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
	}

	public function editDynamic($id){
		$id=intval($id);
		try{
			$sorting_wall_info=D('SortingWall')->getEditDynamicData($id);
			$sorting_wall_type = array(
				array('id' => '1', 'name' => '分拣墙'),
				array('id' => '0', 'name' => '缺货墙'),
			);
			$sorting_wall_no = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$this->assign('sorting_wall_type', $sorting_wall_type);
			$this->assign('sorting_wall_no', $sorting_wall_no);
			$this->getIDList($id_list,array('edit_form','edit_id'),'','dynamic');
			$dialog_list=array('form'=>$id_list['edit_form'],'id'=>$id_list['edit_id']);
			$this->assign('dynamic_info',json_encode($sorting_wall_info));
			$this->assign('dialog_list',$dialog_list);
			$this->assign('dialog_list_json',json_encode($dialog_list));
			$this->display('dialog_dynamic_edit');
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
	}

	public function saveDynamic(){
		$data = I('post.');
		$result=array('status'=>0,'info'=>"保存成功");
		$data['wall_id'] = $data['id'];
		try{
			$result=D('SortingWall')->saveDynamic($data);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}

	public function delDynamic($id){
		$result=array('status'=>0,'info'=>"删除成功");
		try{
			\Think\Log::write($id);
			$sorting_wall_info=D('SortingWall')->delDynamic($id);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}


}