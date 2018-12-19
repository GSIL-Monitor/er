<?php
namespace Stock\Controller;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Controller\BaseController;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Log;
use Think\Model;
// use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;
include_once (APP_PATH . "Platform/Common/ManagerFactory.class.php");
// use Stock\Common\StockFields;

class StockSalesPrintController extends BaseController {
    public function getPrintList(){
		$stockout_no 	= I('get.stockout_no');
		$id_list = DatagridExtention::getIdList(array('tool_bar', 'tab_container', 'datagrid', 'tab_stockout_order_detail', 'tab_sales_trade_detail', 'tab_log', 'tab_note', 'more_button', 'more_content', 'hidden_flag', 'set_flag','search_flag', 'dialog','form','form_main','fast_div','logistics_dialog','logistics_dialog_datagrid','logistics_dialog_datagrid_toolbar','file_dialog','file_form','continue_print_result','print_batch','print_dialog','include_goods','fileDialog','fileForm'));
		$fields = D('Setting/UserData')->getDatagridField('Stock/StockSalesPrint','salesstockout_order');//get_field('StockSalesPrint','salesstockout_order');
		$is_show_pic = get_config_value('stockout_field_goods_pic',0);
        if(!($is_show_pic&1))
        {
            if(isset($fields['图片'])){
                unset($fields['图片']['formatter']);
            }
        }
		//获取配置
		$page_limit=get_config_value('page_limit',0);
		switch($page_limit){
			case '0':
				$limit_count=20;
				break;
			case '1':
				$limit_count=50;
				break;
			case '2':
				$limit_count=100;
				break;
			default:
				$limit_count=20;
				break;
		}
        $datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=> array(
						'title' => '',
						'url'   => U("StockSalesPrint/search").'?stockout_no='.$stockout_no,
						'toolbar' => "#{$id_list['tool_bar']}",
						'frozenColumns'=>D('Setting/UserData')->getDatagridField('Stock/StockSalesPrint','salesstockout_order',1),
						'fitColumns'   => false,
						'singleSelect'=>false,
						'ctrlSelect'=>true,
						'remoteSort'=>true,
						'pageSize'=>$limit_count,
						'pageList'=>[20,50,100,200],
						//'idField'=>'id',
				),
				'fields' => $fields,
				'class' => 'easyui-datagrid',
				'style'=>"overflow:scroll",
		);
		//$checkbox=array('field' => 'ck','checkbox' => true);
        //array_unshift($datagrid['fields'],$checkbox);

		try{
			//获取显示打印状态配置
			$print_status_list = get_config_value(array('stockout_sendbill_print_status','stockout_logistics_print_status'),array(0,0));
			$flag=D('Setting/Flag')->getFlagData(0);
			$search_condition = UtilDB::getCfgRightList(array('shop','logistics','warehouse'));
			$goods_brand = UtilDB::getCfgList(array('brand'), array("brand" => array("is_disabled" => 0)));
			$chg_logistics_list = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>0)));
			$search_condition['chg_logistics_list'] = $chg_logistics_list['logistics'];
			$warehouse_default[0] = array('id' => 'all', 'name' => '全部');
			$warehouse_array = array_merge($warehouse_default, $search_condition['warehouse']);
			}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}

		$params=array(
				'id_list'=>$id_list,
				'datagrid'=>array('id'=>$id_list['datagrid'],'url' => U("StockSalesPrint/search")),
				'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form'],'form_main_id'=>$id_list['form_main']),
				'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('StockCommon/showTabDatagridData')),
				//'edit'=>array('id'=>$id_list['edit'],'url'=>U('TradeManage/setTradeFlag'),'title'=>'颜色标记'),
				'flag'=>array('set_flag'=>$id_list['set_flag'],'url'=>U('Setting/Flag/flag').'?flagClass=5','json_flag'=>$flag['json'],'list_flag'=>$flag['list'],'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=5','title'=>'颜色标记'),'search_flag'=>$id_list['search_flag']),
				'edit'=>array('id'=>'flag_set_dialog'),
		        'revert_reason'=>array(
    		        'id'=>'stocksalesprint_revertreason_id',
    		        'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=1&model_type=salesstockout',
    		        'title'=>'驳回原因',
    		        'width'=>400,
    		        'height'=>'auto',
					'ismax'=>false,
    		        'form' =>array('url'=>U("Stock/StockSalesPrint/revertStockoutOrder"),'id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox','dialog_type'=>'stockout')
		         ),
                'set_field_pic'=>array(
    		        'id'=>'reason_show_dialog',
    		        'url'=>U('Stock/StockSalesPrint/setFieldGoodsPic'),
    		        'title'=>'设置显示货品图片',
    		        'width'=>350,
    		        'height'=>150,
					'ismax'=>false,
		         ),
				'chg_print_status'=>array(
					'id'=>'stocksalesprint_chgprintstatus_id',
					'url'=>U('StockSalesPrint/displayChgPrintStatus').'?datagrid_id=stocksalesprint_chgprintstatus_datagridId',
					'title'=>'设置打印状态',
					'datagrid_id'=>'stocksalesprint_chgprintstatus_datagridId',
				),
				'message'=>array(
					'id'=>'messager',
					'url'=>U('StockSalesPrint/message'),
					'title'=>'通知',
					'width'=>450,
					'height'=>200,
					'ismax'=>false,
				),
				'searchInitData'=>array('search[sendbill_print_status]'=>0,'search[logistics_print_status]'=>0),
				'operator_id'=>get_operator_id(),
				'print_batch'=>array(
					'id'=>$id_list['print_batch'],
					'datagrid'=>'print_batch_list_datagrid',
//					'tab'=>'print_batch_tabs_detail_datagrid',
					'url'=>U('Stock/StockSalesPrint/getDialogPrintBatchList'),
//					'taburl'=>U('Stock/StockSalesPrint/getPrintBatchDetailList'),
					'title'=>'打印批次',
					'width'=>764,
					'height'=>510,
					'ismax'=>false,
				),
				'include_goods'=>array(
					'id'=>$id_list['include_goods'],
					'datagrid'=>'include_goods_list_datagrid',
					'url'=>U('Stock/StockSalesPrint/getDialogIncludeGoodsList').'?type=include',
					'title'=>'选择货品',
					'width'=>900,
					'height'=>510,
					'ismax'=>false,
				),
				'not_include_goods'=>array(
					'id'=>$id_list['include_goods'],
					'datagrid'=>'include_goods_list_datagrid',
					'url'=>U('Stock/StockSalesPrint/getDialogIncludeGoodsList').'?type=not_include',
					'title'=>'选择货品',
					'width'=>900,
					'height'=>510,
					'ismax'=>false,
				),

		);

		$tab_list = array(
		    array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order_detail")).'?prefix=StockSalesPrint&tab=stocksales_print_detail','id' =>$id_list['tab_container'],'title' => '出库单详情'),
		    array('url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>"stockout_order")).'?prefix=StockSalesPrint&tab=sales_order_detail',"id"=>$id_list['tab_container'],"title"=>"订单详情"),
		    array('url'=>U('Stock/StockSalesPrint/showMultiLogistics',array('tabs'=>"sales_multi_logistics")).'?prefix=StockSalesPrint&tab=sales_multi_logistics','id' =>$id_list['tab_container'],'title' => '多物流单号',),
		    array('url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>"sales_stockout_log")).'?prefix=StockSalesPrint&tab=sales_stockout_log','id' =>$id_list['tab_container'],'title' => '日志',),
		);
		$arr_tabs = json_encode($tab_list);
		//获取打印授权
		/*$params['host'] = get_intranet_ip();
		$str = get_service_data('license', $params);
		if($str->status===0){
			$str_licenses = $str->info->print_license;
			$this->assign('print_licenses',$str_licenses);
		}else{
			\Think\Log::write('菜鸟打印组件授权失败:'.print_r($str,true));
			$this->assign('print_licenses','');
		}*/
        $query_config_values = get_config_value(array('order_deliver_block_consign','prevent_online_block_consign_stockout'),array(0,0));

		
        $this->assign('goods_brand',$goods_brand['brand']);
		$this->assign('list',$search_condition);
		$this->assign('print_status_list',$print_status_list);
		$this->assign("params",json_encode($params));
		$this->assign('center_container',$id_list['tab_container']);
		$this->assign('arr_tabs', $arr_tabs);
        $this->assign('online_consign_block', $query_config_values['order_deliver_block_consign']);
        $this->assign('prevent_online_consign_block_stockou', $query_config_values['prevent_online_block_consign_stockout']);
        $this->assign('tool_bar',$id_list['tool_bar']);
		$faq_url=C('faq_url');
		$this->assign('faq_url',$faq_url['stock_print']);
		$this->assign('datagrid', $datagrid);
		$this->assign("id_list",$id_list);
		$this->assign('warehouse_array', $warehouse_array);
		$this->display('print');

    }
	public function showMultiLogistics()
	{
			$tab 	= I('get.tab');
			$prefix = I('get.prefix');
			$app 	= I('get.app');
			$id = $prefix.'datagrid_'.$tab;
			$toolbar = $prefix.'toolbar_'.$tab;
			$form = $prefix.'form_'.$tab;
			$fields = get_field('StockCommon','sales_multi_logistics');
			
			$datagrid = array(
				'id' => $id,
				'options' => array(
					'title' => '',
					'url' => null,
					'toolbar' => "#{$toolbar}",
					'fitColumns' => false,
					'pagination' => false,
					'singleSelect'=>false,
					'ctrlSelect'=>true,
				),
				'fields' => $fields,
				'class' => 'easyui-datagrid',
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			 array_unshift($datagrid['fields'],$checkbox);
			$id_list = array(
				'datagrid'=>$id,
				'toolbar'=>$toolbar,
				'form'=>$form,
				'add'=>'ssp_add_multi_dialog',
				'edit'=>'ssp_add_multi_dialog',
				'writeWeight'=>'writeWeight',

			);
			$params = array(
				'add'=>array(
					'id'     =>  $id_list['add'],
					'title'  =>  '添加多物流单号',
					'url'    =>  U('StockSalesPrint/dialogAddMultiLogistics'),
					'width'  =>  '620',
					'height' =>  '250',
					'ismax'=>false
				),
				'edit'=>array(
					'id'     =>  $id_list['edit'],
					'title'  =>  '编辑多物流单号',
					'url'    =>  U('StockSalesPrint/dialogEditMultiLogistics'),
					'width'  =>  '620',
					'height' =>  '250',
					'ismax'=>false
				),
				'datagrid'=>array(
					'id'    =>    $id_list["datagrid"],
				),
				'search'=>array(
					'form_id'    =>   $id_list['form'],
				),
				'writeWeight'=>array(
					'id'     =>  $id_list['writeWeight'],
					'title'  =>  '填写重量',
					'url'    =>  U('StockSalesPrint/writeWeight'),
					'width'  =>  '350',
					'height' =>  '350',
					'ismax'=>false
				),
			);
		$this->assign('id_list',$id_list);
		$this->assign('datagrid',$datagrid);
		$this->assign('params',json_encode($params));
		$this->display('add_multi_logistics');
	}
	public function dialogAddMultiLogistics()
	{
        $stockout_id = intval(I('get.stockout_id'));
        $logistics_id = intval(I('get.logistics_id'));
        try{
            /**************添加相关***************/
            $stockout_info = D('Stock/StockOutOrder')->where(array('stockout_id'=>$stockout_id))->find();
            //判断物流单号是否为空
            //判断出库单状态
            $dialog_list = array(
                'form' => 'ssp_add_multi_form',
                'source_js' => 'dialog_ssp_add_multi',
            );
            $list = UtilDB::getCfgList(array('logistics'),array('logistics'=>"bill_type = 0 or logistics_id = $logistics_id"));
            $selectLog = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('logistics_id'=>(int)$logistics_id)));
            $firstType = $selectLog['logistics'][0]['type'];
            $form_info = array(
                'logistics_id'=>$stockout_info['logistics_id'],
                'trade_no'=>$stockout_info['src_order_no'],
            );
//            if(intval(I('get.bill_type')) != 0){
//                unset($form_info['logistics_id']);
//            }
            /**************打印相关***************/
            $user_id = get_operator_id();
            if(isset($logistics_id)){//物流单模板
                $Logistics_db = D('Setting/Logistics');
                $logistics_info = $Logistics_db->getLogisticsInfo($logistics_id);
                $logistics_info = $logistics_info[0];
                if((int)$logistics_info['bill_type'] == 2){//菜鸟物流单模板(指getMyStdTemplates接口的)
                    $model = D('Setting/PrintTemplate');
                    $fields = array('rec_id as id,type,title,content');
                    //$conditions = array('type'=>4);
                    $conditions['type'] = "getTemplates";
                    $conditions['logistics_id'] = $logistics_id;
                    $waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
                    //$waybill -> manual($result_info,$conditions);
                    $waybill -> getTemplates($result_info,$logistics_id);
                    $this->assign('stdTemplates',json_encode($result_info));
                    $templates = $model->getTemplateByLogistics($fields,'4,8,7',$logistics_info['logistics_type']);
                    $template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7))->select();
//                    $template_id = $template_id[0]['data'];
                    $ltData = json_decode($template_id[0]['data'],true);
                    if(array_key_exists($logistics_id,$ltData)){
                        $template_id = $ltData[$logistics_id];
                    }else{
                        $template_id = '';
                    }
                }else{//普通物流单模板(getISVTemplates)
                    $model = D('Setting/PrintTemplate');
                    $fields = array('rec_id as id,type,title,content');
                    $conditions = array(5,6,9);
                    $templates = $model->get($fields,$conditions);
                    $template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>6))->select();
//                    $template_id = $template_id[0]['data'];
                    $ltData = json_decode($template_id[0]['data'],true);
                    if(array_key_exists($logistics_id,$ltData)){
                        $template_id = $ltData[$logistics_id];
                    }else{
                        $template_id = '';
                    }
                }
            }
            foreach($templates as $key){
                $contents[$key['id']] = $key['content'];
            }
            $goods = D('StockOutOrder')->getStockoutOrderDetailPrintData($stockout_id);
            foreach($goods as $v){
                if(!isset($no[$v['id']]))
                    $no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
                $detail[$v['id']][] = $v;
                D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
            }
            $goods = $detail;
            if(empty($template_id)) $template_id=$templates[0]['id'];
            if(empty($template_id)) $template_id='-1';
            $preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
            $preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
            //打印相关
            $this->assign("preview_format", $preview_format);
            $this->assign('template_id',$template_id);
            $this->assign('goods',json_encode($goods));
            $this->assign('contents',json_encode($contents));
            $this->assign('templates',$templates);
            //添加相关
            $this->assign('form_info',json_encode($form_info));
            $this->assign('stockout_id',$stockout_id);
            $this->assign('dialog_list_info',json_encode($dialog_list));
            $this->assign('list',$list);
            $this->assign('firstType',$firstType);
            $this->assign('dialog_list',$dialog_list);
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
            $ret['status'] = 1;
            $ret['msg'] = $e->getMessage();
            $this->assign('ret',$ret);
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
            $ret['status'] = 1;
            $ret['msg'] = $e->getMessage();
            $this->assign('ret',$ret);
        }
		$this->display('dialog_add_multi_logis');
	}
    public function getBillType($logistics_id){

        $selectLog = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('logistics_id'=>(int)$logistics_id)));
        $type = $selectLog['logistics'][0]['type'];
        $result_info = array("status"=>1,'msg'=>$type);
        $this->ajaxReturn($result_info);
    }
	public function saveMultiLogistics($is_force=false)
	{
		$result = array('status'=>0,'info'=>'成功');
		try{
			$multi_logistics_info = I('','',C('JSON_FILTER'));
			if(D('Stock/StockOutOrder')->getLogisticsNo($multi_logistics_info['logistics_no'])||D('SalesMultiLogistics')->getLogisticsNo($multi_logistics_info['logistics_no'],$multi_logistics_info['id']))
				$res = "物流单号重复";
			else {
				if(empty($multi_logistics_info['id']))
					$res = D('SalesMultiLogistics') -> addLogistics($multi_logistics_info);
				else $res = D('SalesMultiLogistics') -> saveLogistics($multi_logistics_info);
			}
			if($res !== true){
				$result = array('status'=>1,'info'=>$res);
			}
		}catch (BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn($result);

	}
	public function deleteMultiLogisticsNo(){
		$ids = I('post.ids');
		$data['trade_id'] = I('post.trade_id');
		$logistics_no = I('post.logistics_no');
		$logistics_id = I('post.logistics_id');
		foreach($ids as $k=>$v){
			$data['id'] = $ids[$k];
			$data['logistics_no'] = $logistics_no[$k];
			$type = D('Setting/Logistics')->field('bill_type')->where(array('logistics_id'=>$logistics_no[$k]))->find();
			if($type['bill_type'] == 2){
				$res = array('status'=>1,'info'=>'菜鸟电子面单不能删除');
				break;
			}
			$res = D('Stock/SalesMultiLogistics')->deleteLogisticsNo($data);
		}
		
		$this->ajaxReturn($res);
	}
	public function dialogEditMultiLogistics()
	{
		$id = intval(I('get.id'));
		$stockout_id = intval(I('get.stockout_id'));
		//$multi_info = D('Stock/SalesRecordMultiLogistics')->alias('srml')->field('st.trade_no,srml.logistics_id,srml.logistics_no')->join('left join sales_trade st on st.trade_id = srml.trade_id')->where(array('srml.trade_id'=>$id))->find();
		//判断物流单号是否为空
		//判断出库单状态

		$dialog_list = array(
			'form' => 'ssp_edit_multi_form',
			'source_js' => 'dialog_ssp_edit_multi',
		);
		$list = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('bill_type'=>0)));
		$form_info = array(
			'logistics_id'=>I('get.logistics_id'),
			'trade_no'=>I('get.src_order_no'),
			'logistics_no'=>I('get.logistics_no'),
			'weight'=>I('get.weight'),
		);
		$this->assign('stockout_id',$stockout_id);
		$this->assign('rec_id',$id);
		$this->assign('form_info',json_encode($form_info));
		$this->assign('dialog_list_info',json_encode($dialog_list));
		$this->assign('list',$list);
		$this->assign('dialog_list',$dialog_list);
		$this->display('dialog_edit_multi_logis');
	}
    public function search($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc') {
        try{
			$stockout_no 	= I('get.stockout_no');
			if(!empty($stockout_no)  && empty($search)){
				switch($stockout_no){
					case 'fast_is_blocked':
						$search['fast_is_blocked']=1;
						break;
					case 'fast_stockout_not_printed':
						$search['fast_stockout_not_printed']=1;
						break;
					case 'fast_printed_not_stockout':
						$search['fast_printed_not_stockout']=1;
						break;
					case 'examine_num':
						$search['examine_num']=1;
						break;
					default:
						$search['stockout_no'] = $stockout_no;
				}
			}
			$data = D('StockOutOrder')->searchStockoutList($page, $rows, $search, $sort, $order, 'stockoutPrint');
        }catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		$this->ajaxReturn($data);
    }
	public function getGoodsPrintData($template_id,$ids) {
    	try{
    	    $data = array();
    	    $model = D('Setting/PrintTemplate');
    	    $goods_template_info_fields = array('rec_id as id,type,title,content');
    	    $goods_template_info_conditions = array('type'=>3);
    	    $goods_template_info = $model->get($goods_template_info_fields,$goods_template_info_conditions,$template_id);
    	    if(empty($goods_template_info)){
    	        $goods_template_info = array(
    	            'id' => 0,
    	            'type' => 3,
    	            'title'=>'默认发货单模板',
    	            'content'=>"{}"
    	        );
    	    }else {
    	        $goods_template_info = $goods_template_info[0];
    	    }
    	    $data['content'] = $goods_template_info['content'];
			$data['goods'] = D('StockOutOrder')->getStockoutOrderDetailPrintData($ids);
			$this->updatePrintLog(0,$ids,0,1,$data);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data=array();
		}
		
		$this->ajaxReturn($data);
    }
	
	public function addWaybill(){

	    $logisticsId = I('logisticsId');
        $Logistics_db = D('Setting/Logistics');
        $logistics_info = $Logistics_db->getLogisticsInfo($logisticsId);
        $logistics_info = $logistics_info[0];
        if((int)$logistics_info['bill_type'] == 2){//菜鸟物流单模板(指getMyStdTemplates接口的)
            $waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
            $result_info = array();
            $waybill -> getTemplates($result_info,$logisticsId);
            $this->assign('stdTemplates',json_encode($result_info));
        }

		$id_list = array(
		'tool_bar'                       => 'add_waybill_datagrid_toolbar',    //tool_bar id
		'form'                       	 => 'add_waybill_datagrid_form',    //tool_bar id
		'datagrid'                       => 'add_waybill_datagrid',            //当前模块 datagrid id
		'generate'                       => 'add_waybill_generate',
		'save'                           => 'add_waybill_save',
		);	                
		
		$fields = get_field('StockSalesPrint','stockout_add_waybill');

        $datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=> array(
						'title' => '',
						'toolbar' => "#{$id_list['tool_bar']}",
						'fitColumns'   => false,
						'singleSelect'=>true,
						'ctrlSelect'=>false,
						'methods'=>'loader:getWaybillList',
						'pagination'=>false
				),
				'fields' => $fields,
				'class' => 'easyui-datagrid',
				'style'=>"overflow:scroll",	
		);
		$this->assign('tool_bar',$id_list['tool_bar']);
		$this->assign('form',$id_list['form']);
		$this->assign('generate',$id_list['generate']);
		$this->assign('save',$id_list['save']);
		//$this->assign('logistics_name', 'name');
		//$this->assign('logistics_rule', 'rule');
		$this->assign('datagrid', $datagrid);
		$this->display('add_waybill');
	}
	public function addMultiLogistics(){
		
		$this->display('add_multi_logistics');
	}

	public function updatePackageCount($stockout_ids,$packages){
        $result = array(
            'status'=>0,
            'msg'=>'success',
            'data'=>array($packages)
        );
        $stockoutIdsArr =  explode(',',$stockout_ids);
        $packagesArr =  explode(',',$packages);
        if(count($stockoutIdsArr) == count($packagesArr)){
            try{
                $res_update_stockout = D('Stock/StockOutOrder')->updateBatchStockoutOrder($packagesArr,$stockoutIdsArr);
            }catch (\PDOException $e) {
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'--updatePackageCount--'.$msg);
                $result['status'] =1;
                $result['msg'] = $msg;
                $this->ajaxReturn($result);
            }catch (\Exception $e){
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'--updatePackageCount--'.$msg);
                $result['status'] =1;
                $result['msg'] = $msg;
                $this->ajaxReturn($result);
            }
        }else{
            $result['status'] =1;
            $result['msg'] = '出库单和包裹数不对应';
        }
        $this->ajaxReturn($result);
    }
	public function getHasPrintedInfo($ids,$type,$multiIds='',$all_ids=''){
		$result = array(
			'status'=>0,
			'msg'=>'success',
			'data'=>array(
				'has_printed'=>array(
					'logistics' 		=> array(),
					'goods' 			=> array(),
					'sroting' 			=> array(),
					'multipleLogistics' => array(),
				)
			)
		);
		try{
			D('Stock/StockOutOrder')->getHasPrintedInfo($ids,$type,$multiIds,$result,$all_ids);
		}catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write(__CONTROLLER__.'--getHasPrintedInfo--'.$msg);
			$result['status'] =1;
			$result['msg'] = $msg;
		}
		$this->ajaxReturn($result);
	}
	public function showHasPrintedInfo($type,$isMulti,$flag="old",$print_dialog_type){
	    $id_list = array(
	        'tool_bar'                       => 'has_printed_datagrid_toolbar',    //tool_bar id
	        'datagrid'                       => 'has_printed_datagrid',            //当前模块 datagrid id
	        'continue_print'                 => 'has_printed_continued',
	    );

	    $fields = get_field('StockSalesPrint','stockout_has_printed_info');
		if($type == 'multipleLogistics'){
			$fields = get_field('StockSalesPrint','multilogistic_has_printed_info');
		}
	    $datagrid = array(
	        'id'=>$id_list['datagrid'],
	        'options'=> array(
	            'title' => '',
	            'toolbar' => "#{$id_list['tool_bar']}",
	            'fitColumns'   => false,
	            'singleSelect'=>true,
	            'ctrlSelect'=>false,
	            'methods'=>'loader:getHasPrintedInfo',
	            'pagination'=>false
	        ),
	        'fields' => $fields,
	        'class' => 'easyui-datagrid',
	        'style'=>"overflow:scroll",
	    );
	    $this->assign('tool_bar',$id_list['tool_bar']);
	    $this->assign('continue_print',$id_list['continue_print']);
	    $this->assign('datagrid', $datagrid);
	    $this->assign('type',$type);
	    $this->assign('isMulti',$isMulti);
		$this->assign('print_dialog_type',$print_dialog_type);
	    $this->assign('flag',$flag);
	    $this->display('has_printed_info');
	
	}
	
	public function printGoods($logistics_id){
		//$this->assign('preview','print_goods_preview');
		$logistics_id = intval($logistics_id);
		$fields = 'rec_id as id,title as name,content';
		$model = D('Setting/PrintTemplate');
		$result = $model->getTemplateBylogistics($fields,'3',$logistics_id);
		$contents = array();
		if(!empty($result)){
			foreach ($result as $key => $value) {
				$contents[$value['id']] = stripslashes($value['content']);
			}
		}
		$contents = json_encode($contents);
		$this->assign('contents',addslashes($contents));
		$this->assign('goods_template',$result);
		$this->display('print_data');
	}
	
	public function printLogistics($logistics_id){
	    $logistics_id = intval($logistics_id);
	    
		$fields = 'rec_id as id,title as name,content';
		$model = D('Setting/PrintTemplate');
		$result = $model->getTemplateByLogistics($fields, '0,2',$logistics_id);
		$contents = array();
		if(!empty($result)){
			foreach ($result as $key => $value) {
				$contents[$value['id']] = stripslashes($value['content']);
			}
		}
		$contents = json_encode($contents);
		$this->assign('contents',addslashes($contents));
		//\Think\Log::write('result: '.print_r($result, true));
		$this->assign('logistics_template', $result);
		$this->display('print_logistics_data');
	}
	public function getLogisticsPrintTemplate($id) {
		$fields = 'type, content';
		$model = D('Setting/PrintTemplate');
		$data = $model->get($fields, '', $id);
		$this->ajaxReturn($data);
    }
    //因为有返回的电话号码等数据，所以也算预览了相关数据
	public function prepareLogisticsPrint($template_id, $stockout_ids,$is_print){
	    try {
	        $fields = 'type, content, logistics_list';
	        $model = D('Setting/PrintTemplate');
	        $data = $model->get($fields, '', $template_id);
	        $data['status'] =0;
	        if (2 == $data[0]['type']){
	            $result =array();
	            list($logistics_id )  = explode(',', $data[0]['logistics_list']);
	            $print_auth_info = D('Setting/Logistics')->getPrintAuthInfo($logistics_id);
	           
	            if(empty($print_auth_info) || empty($print_auth_info['user_id']))
	            {
	                SE('请核对新建模板界面,所使用的的模板中【保存】按钮下方,物流公司下拉菜单中匹配的物流授权是否失效或者不是菜鸟物流');
	            }else{
	                $print_auth_data['app_key'] =  '21363512';
	                $print_auth_data['user_id'] = $print_auth_info['user_id'];
	            }
	            $stockout_ids          = trim($stockout_ids,',');
	            $waybill = D('Stock/WayBill','Controller')->printWayBill($stockout_ids, $logistics_id,$result);
	            
	            $data['status'] = $result['status'];
	            $data[0]['auth'] = $print_auth_data;
	            if ($result['status'] == 1){
	                $data['msg'] = $result['msg'];
	            }elseif($result['status'] == 2){
	                $data['fail'] = $result['data']['fail'];
	            }
	        }
			$this->updatePrintLog(0,$stockout_ids,1,1,$data);
			$stockout_ids          = trim($stockout_ids,',');
			$data['print_info']['goods'] = D('StockOutOrder')->getStockoutOrderDetailPrintData($stockout_ids);

		}catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            $data['status'] =1;
            $data['msg'] = $msg;
	    }catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write(__CONTROLLER__.'-prepareLogisticsPrint-'.$msg);
	        E($model::PDO_ERROR);
	    }catch (\Exception $e) {
	        $msg = $e->getMessage();
	        $data['status'] =1;
	        $data['msg'] = $msg;
	        \Think\Log::write(__CONTROLLER__.'-prepareLogisticsPrint-'.$msg);
	    }
		
		$this->ajaxReturn($data);
		
	}
	public function saveWaybill() {
		try{
		    $params = I('','',C('JSON_FILTER'));
		    $src_tids = "";
		    $logistics_id = $params['logistics_info']['data'][0]['logistics_id'];
		    foreach ($params['logistics_info']['data'] as $key => $value) {
		    	$src_tids .= $value['src_order_id'].",";
		    }
		    $src_tids = substr($src_tids,0,-1);
		    $where = array('trade_id'=>array('in',$src_tids));
		    $res = D('Trade/SalesTrade')->getTradeOrderInfo('st.logistics_id,st.trade_id',$where);
		    foreach ($res as $key => $value) {
		    	if($value['logistics_id'] != $logistics_id){
		    		$key--;
		    		break;
		    	}
		    }
		    if($key < (count($res)-1)){
		    	$status = 1;
		    	SE("物流公司不一致，请刷新后再尝试");
		    }
			$model = D('StockOutOrder');
			$ret = $model->saveWaybill($params['logistics_info']['data'], $params['logistics_info']['log'],$params['logistics_info']['is_force']);
		}catch(\Exception $e){
			Log::write(__CONTROLLER__.'-saveWaybill-'.$e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = "保存物流单号失败";
			if(isset($status)){
				$ret['msg'] = $e->getMessage();
			}
		}
		$this->ajaxReturn($ret);
    }
    public function saveWayBillMultiLogistics(){

        $result = array('status'=>0,'info'=>'成功');
        $multi_logistics_info = I('','',C('JSON_FILTER'));
        $stockout_id = $multi_logistics_info['stockout_id'];
        $where = array('stockout_id'=>$stockout_id,'logistics_no'=>'');
        $packageNos = D('SalesMultiLogistics')->getPackageNo($where);

        if(!$packageNos['status']){
            $result = array('status'=>1,'info'=>'请先打印未打印的物流单');
            $this->ajaxReturn($result);
        }else{
            if(!empty($packageNos['data'])){
                $packNoArr = array();
                foreach ($packageNos['data'] as $v){
                    $packNoArr[] = $v['package_no'];
                }
                $pos = array_search(max($packNoArr),$packNoArr);
                $multi_logistics_info['package_no'] = $packNoArr[$pos]+1;
            }else{
                $multi_logistics_info['package_no'] = 1;
            }
        }
        try{
            $res = D('SalesMultiLogistics') -> addLogistics($multi_logistics_info);
            if($res !== true){
                $result = array('status'=>1,'info'=>$res);
            }
        }catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=>$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
	public function saveLogisticsPrintResult($ids){
		$model = D('StockOutOrder');
		if ($model->updatePrintStatus($ids, 'logistics_print_status', 1)){
		    $this->updatePrintLog(1,$ids,1,0);
			$ret['status'] = 0;
		}else{
			$ret['status'] = 1;
			$ret['msg'] = "保存打印状态失败";
		}
		$this->ajaxReturn($ret);
	}
	
	public function saveGoodsPrintResult($ids){
		$model = D('StockOutOrder');
		if ($model->updatePrintStatus($ids, 'sendbill_print_status', 1)){
		    $this->updatePrintLog(1,$ids,0,0);
			$ret['status'] = 0;
		}else{
			$ret['status'] = 1;
			$ret['msg'] = "保存打印状态失败";
		}
		$this->ajaxReturn($ret);
	}
	public function updatePrintLog($is_print,$stockout_ids,$is_logistics,$is_get_data,&$print_data=array())
	{
	    
	        $operator_id       = get_operator_id();
	        $stockout_ids      = trim($stockout_ids,',');
	        $stockout_id_list  = explode(',', $stockout_ids);
	        $is_print_str      = $is_print == 0? '预览了':'打印';  //判断打印还是预览
	        $is_logistics_str  = $is_logistics == 1?'物流单':'发货单'; //判断是发货单还是物流单
	        $operator_ip = get_client_ip();
	        $stockout_order_info = D('Stock/StockOutOrder')->getSalesStockoutOrderList(array('st.trade_id','so.stockout_id id','st.trade_no','so.receiver_mobile','so.receiver_telno','cl.logistics_name'),array('so.stockout_id'=>array('in',$stockout_id_list)));

	        foreach ($stockout_order_info as $key =>$value)
	        {
	            $print_info["{$value['id']}"] = array(
	                'receiver_mobile' => $value['receiver_mobile'],
	                'receiver_telno'  => $value['receiver_telno'],
	                'id'              => $value['id']
	            );
	            $add_logistics_message = $is_logistics == 1?'--物流公司：'.$value['logistics_name']:'';
	            $log[] = array(
	                'trade_id'         => $value['trade_id'],
	                'operator_id'      => $operator_id,
	                'type'             => 91,
	                'data'             => $is_logistics==1?2:1,
	                'message'          => '登陆IP:'.$operator_ip.'--'.$is_print_str.$is_logistics_str.'：订单编号--'.$value['trade_no'].$add_logistics_message,
	            );
	        }
	       if(($is_get_data == 1 && $is_print == 0) || ($is_get_data == 0 && $is_print == 1) ){
	           $res_update_log = D('Trade/SalesTradeLog')->addTradeLog($log);
	        }
	        $print_data['print_info'] = $print_info;
	   
	}
	public function chgLogistics()
	{
		set_time_limit(0);
		$chg_logistics_info = I('','',C('JSON_FILTER'));
		$chg_logistics_info = $chg_logistics_info['ids'];
		$result = array(
				'status'=>0,
				'info'=>'success',
				'data'=>array()
		);
		$fail = array();
		$success = array();
		if(empty($chg_logistics_info))
		{
			$result['info'] ="请选择出库单";
			$result['status'] = 1;
			$this->ajaxReturn($result);
		}
		foreach ($chg_logistics_info as $key=>$chg_info)
		{   $success[$key] = array();
			D('Stock/SalesStockOut')->chgLogistics($chg_info,$fail,$success[$key]);
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
		$this->ajaxReturn($result);
	}
	public function chgPrintStatus()
	{
		set_time_limit(0);
		$chg_print_status_info = I('','',C('JSON_FILTER'));

		$result = array(
				'status'=>0,
				'info'=>'success',
				'data'=>array()
		);

		if(empty($chg_print_status_info))
		{
			$result['info'] ="请选择出库单";
			$result['status'] = 1;
			$this->ajaxReturn($result);
		}

		D('Stock/SalesStockOut')->chgPrintStatus($chg_print_status_info,$result);

		$this->ajaxReturn($result);
	}
	public function displayChgPrintStatus(){
		$datagrid_id = I('datagrid_id');
		$fields = get_field('StockSalesPrint','sales_print_status');
		$datagrid = array(
				'id'=>$datagrid_id,
				'options'=> array(
						'title' => '',
						'fitColumns'   => false,
						'singleSelect'=>false,
						'ctrlSelect'=>true,
						'pagination'=>false

				),
				'fields' => $fields,
				'class' => 'easyui-datagrid',
				'style'=>"overflow:scroll",
		);

		$this->assign('datagrid',$datagrid);
		$this->assign('datagrid_id',$datagrid['id']);
		$this->display('dialog_print_status_chg');
	}
	public function consignStockoutOrder($ids)
	{
		set_time_limit(0);
		$consign_info = I('post.ids','',C('JSON_FILTER'));
		$is_force = I('post.is_force');
		$result = array(
				'status'=>0,
				'info'=>'success',
				'data'=>array()
		);
		$fail = array();
		$success = array();
		if(empty($consign_info))
		{
			$result['info'] ="请选择出库单";
			$result['status'] = 1;
			$this->ajaxReturn($result);
		}
		foreach ($consign_info as $key=>$id)
		{   $success[$key] = array();
			D('Stock/SalesStockOut')->consignStockoutOrder($id,$fail,$success[$key],$is_force);
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
        $this->ajaxReturn($result);

    }
	public function getOrdinaryNo()
	{
		$result = array('status'=>0,'info'=>'成功','data'=>array('success'=>array(),'fail'=>array()));
		$data = I('','',C('JSON_FILTER'));
		try{
			$result['data']['success'] = D('Stock/StockOutOrder')->getOrdinaryLogisticsNo($data);
		}catch(BusinessLogicException $e){
			$result['info'] = $e->getMessage();
			$result['status'] = 1;
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['info'] = self::UNKNOWN_ERROR;
			$result['status'] = 1;
		}
		$this->ajaxReturn($result);
	}
	public function importLogisticsNo($is_force)
	{
		//获取Excel表格相关的数据
		$file = $_FILES["file"]["tmp_name"];
		$name = $_FILES["file"]["name"];
		$is_force = intval($is_force);
//		$is_force = intval(I('post.is_force'));
		$res = array('status'=>0,'msg'=>'成功');
		//读取表格数据
		try {
			$excelData = UtilTool::Excel2Arr($name, $file, "importLogisticsNo");
		} catch (\Exception $e) {
		    \Think\Log::write(CONTOLLER_NAME.'-importLogisticsNo-'.$e->getMessage());
			$res = array("status" => 1, "info" => $e->getMessage());
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
		$import_data = array();
		$logistics_data = array();
		try {
			//字段映射
			foreach ($excelData as $sheet)
			{//遍历工作簿
				for ($k = 1; $k < count($sheet); $k++) {
					$row = $sheet[$k];
					//分类存储数据
					$i = 0;
					$import_data[] = array(
						"trade_no" 			=> addslashes(trim($row[$i])),//订单编号
						'logistics_no' 		=> addslashes(trim($row[++$i])),//物流单号
						'logistics_name' 	=> addslashes(trim($row[++$i])),//物流公司名称
					);
				}
			}
			foreach ($import_data as $key => $value)
			{
				$is_null_row = true;
				foreach($value as $f=>$f_v){
					if(!empty($f_v)){
						$is_null_row = false;
						break;
					}
				}
				if($is_null_row){
					continue;
				}else{
					$logistics_data[] = $value;
				}
			}
			if(empty($logistics_data)){
				SE('导入的数据不能为空');
			}

			$res = D("StockOutOrder")->importLogisticsNo($logistics_data,$is_force);
		} catch (BusinessLogicException $e) {
			$res = array("status" => 1, "msg" => $e->getMessage());
		} catch (\Exception $e) {
			Log::write($e->getMessage());
			$res = array("status" => 1, "msg" => self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn(json_encode($res), "EVAL");
	}
	public function downloadTemplet(){
		$type = I('get.type');
		$file_name = "物流单号导入模板.xls";
		switch($type){
			case 'import_logistics' :
				$file_name = "物流单号导入模板.xls";
				break;
			case 'search_logistics' :
				$file_name = "物流单号(搜索)导入模板.xls";
				break;
		}
//		$file_name = iconv('utf-8','gbk',$file_name);
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

	public function setDefaultPrinter($content,$templateId,$is_ajax = true){
		$data['content'] = json_decode($content);
		$data['rec_id'] = $templateId;
        $operator_id = get_operator_id();
        $template_info = D('Setting/PrintTemplate')->field('title,content')->where(array('rec_id'=>$templateId))->find();
        $old_content = json_decode($template_info['content']);
        if(isset($old_content->operator_to_printer)){
            $data['content']->operator_to_printer = is_array($old_content->operator_to_printer)?(object)$old_content->operator_to_printer:$old_content->operator_to_printer;
        }else{
            if(!isset($data['content']->operator_to_printer)){
                $data['content']->operator_to_printer = (object)array();
            }
        }
        if(is_array($data['content']->operator_to_printer)){
            $data['content']->operator_to_printer[$operator_id] = $data['content']->default_printer;
            $data['content']->operator_to_printer = (object)$data['content']->operator_to_printer;
        }else{
            $data['content']->operator_to_printer->$operator_id = $data['content']->default_printer;
        }
        $data['content'] = json_encode($data['content']);
		$data['type'] = "";
		$data['title'] = "";
		$ret = array("status"=>0,"msg"=>"成功");
		try{
			$res = D('Setting/PrintTemplate')->save($data,'content');
		}catch(\Exception $e){
			$ret['status'] = 1;
			$ret['meg'] = self::UNKNOWN_ERROR;
		}
		if($is_ajax){$this->ajaxReturn($ret);}
		return $ret;

	}
	public function setFieldGoodsPic(){
        try
        {
            $id_list = self::getIDList($id_list,array('form'),'','setfieldpic');
            $form_data = array();
            $show_pic_config = get_config_value('stockout_field_goods_pic',0);
            if($show_pic_config & 1){
                $form_data['stockout_order_field_goods_pic'] = 1;
            }
            if($show_pic_config & 2){
                $form_data['stockout_detail_field_goods_pic'] = 2;
            }
            $this->assign('form_data',json_encode($form_data));
            $this->assign("form_id", $id_list['form']);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_set_goods_pic');
    }
    public function saveGoodsPicSetting()
    {
        try{
            $result = array('status'=>0,'info'=>'成功');
            $set_data = I('post.stockout_field_goods_pic','',C('JSON_FILTER'));
            $show_goods_pic_setting = array('key'=>'stockout_field_goods_pic','value'=>$set_data,'class'=>'system','value_type'=>2,'log_type'=>5);
            $res = D("Setting/System")->updateSystemSetting($show_goods_pic_setting);
            if(!$res){
                $result['status'] = 1;
                $result['info'] = self::UNKNOWN_ERROR;
            }
        }catch (BusinessLogicException $e){
            $result['status'] = 1;
            $result['info'] = self::UNKNOWN_ERROR;
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-saveGoodsPicSetting-'.$e->getMessage());
            $result['status'] = 1;
            $result['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }

    public function changePrintStatus(){
        $stockout_ids = I('post.stockout_ids');
        $print_type = I('post.print_type');
        $is_print = I('post.is_print');
		$multiIds = I('post.multiIds');
		$value = I('post.value');
        $res = array('status'=>0,'info'=>'成功');
        try{
			if($print_type == 'multipleLogistics'){
				if($value == 1) {
					D("StockOutOrder")->newUpdatePrintLog($is_print, $stockout_ids, $print_type, $multiIds);
				}
				D("SalesMultiLogistics")->changeMultiPrintStatus($multiIds,$value);
			}else if($print_type == 'multiplePrintLogistics'){
			    $logisticsArr = explode(',',$multiIds);
			    $logisticsNOs = "'".implode("','",$logisticsArr)."'";
                D("SalesMultiLogistics")->changeMultiLogisticsStatus($logisticsNOs);
            }else if($print_type == 'multiplePrintSfOrderLogistics'){

                $print_type = 'logistics';
                $logisticsArr = explode(',',$multiIds);
                $logisticsNOs = "'".implode("','",$logisticsArr)."'";
                D("SalesMultiLogistics")->changeMultiPrintStatus($multiIds,$value);
                D("StockOutOrder")->changePrintStatus($stockout_ids,$print_type,$value);
                if($value == 1){
                    D("StockOutOrder")->newUpdatePrintLog($is_print,$stockout_ids,$print_type,$multiIds);
                }

            }else{
//				if(!empty($stockout_ids)){
//					D("StockOutOrder")->addPrintBatch($stockout_ids,$print_type);
//				}
				if($value == 1){
					D("StockOutOrder")->newUpdatePrintLog($is_print,$stockout_ids,$print_type);
				}
                D("StockOutOrder")->changePrintStatus($stockout_ids,$print_type,$value);
			}
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
			\Think\Log::write('stockout_ids--'.$stockout_ids.'--print_type--'.$print_type.'--is_print'.$is_print);
            $res = array('status'=>1,'info'=>$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME."--changePrintStatus--".$e->getMessage());
            $res = array('status'=>1,'info'=>$e->getMessage());
        }
        $this->ajaxReturn($res);
    }
	public function addPrintBatch($stockout_ids,$print_type,$is_log=0,$pick_list_no=''){
		$res = array('status'=>0,'info'=>'成功');
		try{
			$batch_no = D("StockOutOrder")->addPrintBatch($stockout_ids,$print_type,$pick_list_no);
			if($is_log==1){D("StockOutOrder")->newUpdatePrintLog('1',$stockout_ids,$print_type);}
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$res = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write(CONTROLLER_NAME."--addPrintBatch--".$e->getMessage());
			$res = array('status'=>1,'info'=>$e->getMessage());
		}
		$res['batch_no'] = empty($batch_no['batch_no'])?'':$batch_no['batch_no'];
		$res['picklist_no'] = empty($batch_no['picklist_no'])?'':$batch_no['picklist_no'];
		$this->ajaxReturn($res);
	}
	public function templatesSelect(){
		$this->display('templates_select');
	}
	public function onWSError(){
		$this->display('waybillDownload');
	}
	public function newPrintGoods($ids){
		//$this->assign('preview','print_goods_preview');
		try{
            $fields = 'rec_id as id,title as name,content';
			$model = D('Setting/PrintTemplate');
			$result = $model->get($fields,array(5,6,9));
			foreach($result as $key){
				$contents[$key['id']] = $key['content'];
			}
			$goods = D('StockOutOrder')->getStockoutOrderDetailPrintData(I('get.ids'));
            foreach($goods as $v){
				if(!isset($no[$v['id']]))
					$no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
				$detail[$v['id']][] = $v;
				$this->judgeConditions($detail[$v['id']],$v);
			}
			$user_id = get_operator_id(); 
			$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
			$template_id = $template_id[0]['data'];
			if(empty($template_id)) $template_id=$result[0]['id'];
			if(empty($template_id)) $template_id='-1';
			$goods = $detail;
            $this->composeSuiteData($goods);
            $preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
			$preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
			$this->assign("preview_format", $preview_format);
			$this->assign('goods',json_encode($goods));
			$this->assign('contents',json_encode($contents));
			$this->assign('goods_template',$result);
			$this->assign('template_id',$template_id);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
		}
		$this->display('new_print_data');
	}
	public function composeSuiteData(&$goods){
        foreach($goods as $key => &$val){
            $nameAndDetailSuites = array();
            if($val['suite_ids'] == ''){
                continue;
            }else{
                $suite_ids = $val['suite_ids'];
                $suite_ids_length = strlen($suite_ids);
                $suite_ids = substr($suite_ids,1,$suite_ids_length-2);
                $suitList =  D('GoodsSuite')->where(['suite_id' => ['in',$suite_ids]])->select();
                foreach ($suitList as $v){
                    $v['goods_name'] = '【'.$v['suite_name'].'】';
                    $v['spec_no'] = $v['suite_no'];

                    if($v['is_print_suite'] == 1){

                        foreach ($val as $goodsKey => $goodsVal){

                            if(@$goodsVal['suite_id'] == $v['suite_id']){
                                $v['distribution'] = $goodsVal['distribution'];
                                $v['num'] = $goodsVal['suite_num'] == 0?$goodsVal['num']:$goodsVal['suite_num'];
                                unset($val[$goodsKey]);
                            }
                        }
                        array_unshift($val,$v);

                    }elseif($v['is_print_suite'] == 2){

                        $suits = array();
                        foreach ($val as $goodsKey => $goodsVal){

                            if(@$goodsVal['suite_id'] == $v['suite_id']){
                                $v['distribution'] = $goodsVal['distribution'];
                                $v['num'] = $goodsVal['suite_num'] == 0?$goodsVal['num']:$goodsVal['suite_num'];
                                $goodsVal['no'] = '';
                                $goodsVal['not_show_no'] = '1';
                                $suits[] = $goodsVal;
                                unset($val[$goodsKey]);
                            }
                        }
                        array_unshift($suits,$v);
                        $nameAndDetailSuites = array_merge($nameAndDetailSuites,$suits);
                        $suits = array();
                    }elseif($v['is_print_suite'] == 0){

                        foreach ($val as $goodsKey => $goodsVal){
                            if(@$goodsVal['suite_id'] == $v['suite_id']){
                                foreach ($val as $gk=>&$gv){
                                    if((@$goodsVal['spec_id'] == $gv['spec_id']) && is_array($gv) && empty($gv['suite_id'])){
                                        $gv['num'] = $gv['num'] + $goodsVal['num'];
                                        unset($val[$goodsKey]);
                                    }
                                }
                            }
                        }
                    }
                }
                $val =  array_merge($val);
            }
            $val = array_merge($val,$nameAndDetailSuites);
            $no = 1;
            for($k=0;$k<count($val)-2;$k++){
                if(@$val[$k]['not_show_no'] != 1){
                    $val[$k]['no'] = $no;
                    $no++;
                }
            }
        }
    }
    public function printSfOrder($ids,$logisticsId){
        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $result = $model->get($fields,array(5,6,9));
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $goods = D('StockOutOrder')->getStockoutOrderDetailPrintData(I('get.ids'));
            foreach($goods as $v){
                if(!isset($no[$v['id']]))
                    $no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
                $detail[$v['id']][] = $v;
                $this->judgeConditions($detail[$v['id']],$v);
            }
            $goods = $detail;
			$this->composeSuiteData($goods);
            $multiLogisticsNo = D('SalesMultiLogistics')->field('logistics_no,stockout_id,rec_id')->where(['stockout_id'=>array('in',$ids)])->select();
            $user_id = get_operator_id();
            $template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
            $template_id = $template_id[0]['data'];
            if(empty($template_id)) $template_id=$result[0]['id'];
            if(empty($template_id)) $template_id='-1';
            $this->assign('contents',json_encode($contents));
            $this->assign('multiLogisticsNo',json_encode($multiLogisticsNo));
            $this->assign('goods',json_encode($goods));
            $this->assign('goods_template',$result);
            $this->assign('template_id',$template_id);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
        $this->display('print_sf');

    }
    public function printPickList()
    {
        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $result = $model->get($fields,array(5,6,9));
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $stockout_ids = I('get.ids');
            $print_batch_model = M('stockout_print_batch');
            $batchData =  $print_batch_model->field('batch_no,order_mask,order_num,pick_list_no')->where(['queue' => $stockout_ids])->find();
            if($batchData['pick_list_no'] == ''){
                $pln_sql = 'select FN_SYS_NO("picklist") pick_list_no';
                $pick_list_no = $print_batch_model->query($pln_sql);
                $batchData['pick_list_no'] = $pick_list_no[0]['pick_list_no'];
            }
            $pickListData = D('StockOutOrder')->getPickListOrderDetailPrintData($stockout_ids);
            foreach($pickListData as $v){
                if(!isset($no[$v['id']]))
                    $no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
                $detail[$v['id']][] = $v;
                $this->judgeConditions($detail[$v['id']],$v);
            }
            $user_id = get_operator_id();
            $template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
            $template_id = $template_id[0]['data'];
            if(empty($template_id)) $template_id=$result[0]['id'];
            if(empty($template_id)) $template_id='-1';
            $pickListData = $detail;
            $this->composeSuiteData($pickListData);
            $pickListData[0] = $pickListData[''];
            array_splice($pickListData,0,1);
            $preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
            $preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
            $this->assign("preview_format", $preview_format);
            $this->assign('sortingData',json_encode($pickListData));
            $this->assign('contents',json_encode($contents));
            $this->assign('goods_template',$result);
            $this->assign('template_id',$template_id);
            $this->assign('batchData',json_encode($batchData));

        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
        $this->display('print_sorting');
    }
	public function printerSetting(){
		$print_price_point_num = D('Stock/StockoutOrderDetail')->savePrintPointNum();
		$preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
		$goods_order = D('Stock/StockoutOrderDetail')->saveGoodsOrder();
		$print_price_point_num = empty($print_price_point_num['data'])&&!is_numeric($print_price_point_num['data'])?'4':$print_price_point_num['data'];
		$preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
		$goods_order = empty($goods_order['data'])&&!is_numeric($goods_order['data'])?'0':$goods_order['data'];
		$this->assign("print_price_point_num", $print_price_point_num);
		$this->assign("preview_format", $preview_format);
		$this->assign("goods_order", $goods_order);
		$this->display('printerSetting');
	}
	public function setPrinter(){
        $this->display('setPrinter');
    }
	public function printPointNum($point_num,$preview_format,$goods_order){
		$point_num = (empty($point_num)&&!is_numeric($point_num))?'4':$point_num;
		$preview_format = empty($preview_format)?'image':$preview_format;
        $goods_order = (empty($goods_order)&&!is_numeric($goods_order))?'0':$goods_order;
		try{
			D('Stock/StockoutOrderDetail')->savePrintPointNum($point_num);
			D('Stock/StockoutOrderDetail')->savePreviewFormat($preview_format);
			D('Stock/StockoutOrderDetail')->saveGoodsOrder($goods_order);
        }catch(Exception $e){
			\Think\Log::write(CONTROLLER_NAME.'-printPointNum-'.$e->getMessage());
		}
	}
    public function revertStockout()
    {
        $revert_check_info = I('post.ids','',C('JSON_FILTER'));
        $is_force = I('post.is_force','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($revert_check_info))
        {
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        $is_force = empty($is_force)?0:$is_force;//强制驳回标

        foreach ($revert_check_info as $key=>$id)
        {
            $success[$key] = array();
            D('Stock/SalesStockOut')->revertStockout($id,$is_force,$fail,$success[$key]);
            if(empty($success[$key]))
            {
                unset($success[$key]);
            }
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['data']=array(
            'fail' => $fail,
            'success' => $success
        );
        $this->ajaxReturn($result);
    }
    public function unblockStockout($ids)
    {
        $revert_check_info = I('post.ids','',C('JSON_FILTER'));
//         \Think\Log::write('changtao '.print_r($revert_check_info,true));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($ids))
        {
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
//         $params['is_force'] = 0;//强制驳回标记
        $params['operator_id'] = get_operator_id();


        foreach ($revert_check_info as $key=>$id)
        {
            $success[$key] = array();
            D('Stock/SalesStockOut')->unblockStockout($id,$params,$fail,$success[$key]);
            if(empty($success[$key]))
            {
                unset($success[$key]);
            }
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['data']=array(
            'fail' => $fail,
            'success' => $success
        );
        $this->ajaxReturn($result);
    }
    public function revertConsignStatus($ids)
    {
        $revert_check_info = I('post.ids','',C('JSON_FILTER'));
        $revert_check_type = I('post.type','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($ids))
        {
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        $params['is_force'] = 0;//强制驳回标记
        $params['operator_id'] = get_operator_id();


        foreach ($revert_check_info as $key=>$id)
        {
            $success[$key] = array();
            D('Stock/SalesStockOut')->revertConsignStatus($revert_check_type,$id,$params,$fail,$success[$key]);
            if(empty($success[$key]))
            {
                unset($success[$key]);
            }
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['data']=array(
            'fail' => $fail,
            'success' => $success
        );
        $this->ajaxReturn($result);
    }
    public function revertStockoutOrder($ids)
    {
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        $params = I('','',C('JSON_FILTER'));
        $form_params = $params['form'];
        $ids = $params['ids'];
		$is_force = empty($params['is_force'])?0:1;
        if(empty($ids))
        {
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        $form_params['hold_logisticsno_status'] = isset($form_params['hold_logisticsno_status'])?$form_params['hold_logisticsno_status']:0;
        $form_params['hold_sendbill_status'] = isset($form_params['hold_sendbill_status'])?$form_params['hold_sendbill_status']:0;

        $form_params['is_force'] = $is_force;//强制驳回标记
        $form_params['operator_id'] = get_operator_id();
        if (empty( $form_params['reason_id']) || (int) $form_params['reason_id'] == 0)
        {
            $result['info'] ="无效的原因,请先添加驳回原因";
            $result['status'] = 1;
            return $this->ajaxReturn($result);
        }
        $stockout_ids = explode(",", $ids);
        foreach ($stockout_ids as $key=>$id)
        {
            $success[$key] = array();
			$res_so_info = D('Stock/StockOutOrder')->field('stockout_no,status')->where(array('stockout_id'=>$id))->find();
			if((int)$res_so_info['status'] ==5){
				$fail[] = array(
					'stock_id' => $id,
					'stock_no' => $res_so_info['stockout_no'],
					'msg'      => '该订单已经驳回审核，请刷新页面后重试',
				);
				continue;
			}
            D('Stock/SalesStockOut')->revertSalesStockout($id,$form_params,$fail,$success[$key]);
            if(empty($success[$key]))
            {
                unset($success[$key]);
            }
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['data']=array(
            'fail' => $fail,
            'success' => $success
        );
        $result['type']="revert_reason";
        $this->ajaxReturn($result);
    }

    /**
     * [judgeConditions description]
     * @param  [array] $goods [订单货品信息列表]
     * @param  [string] $spec [订单中某个组合装或者货品]
     */
    function judgeConditions(&$goods,$spec){
    	$sum = 0;
    	if(!isset($goods['suite_info']))
    		$goods['suite_info'] = "";
    	$goods['suite_ids'] = isset($goods['suite_ids'])?$goods['suite_ids']:"";
    	$sum += $goods['suite_ids'] == ""?0:1;
    	$sum += $spec['suite_id'] == 0?0:2;
    	$sum += ($sum<=2)?0:(strpos($goods['suite_ids'],",".$spec['suite_id'].",")!==false?0:4);
    	switch ($sum) {
    		case 0:
    		case 1://0、1是单品
                $goods['suite_info'] .= $spec['short_name']."  ".$spec['num']."  ".$spec['spec_name'].";";
            break;
    		case 2://组合装，且suite_ids为空
    			$goods['suite_info'] .= $spec['suite_name']."     ".$spec['suite_num'].";";
    			$goods['suite_ids'] .= ",".$spec['suite_id'].",";
    			break;
    		case 3://组合装，且组合装已经在suite_ids中了
    			break;
    		case 7://组合装，且组合装不在suite_ids中
    			$goods['suite_ids'] .= $spec['suite_id'].',';
    			$goods['suite_info'] .= $spec['suite_name']."     ".$spec['suite_num'].";";
    			break;
    	}
    }
    public function previewPrintTemplateData()
    {
        $stockout_ids = I('post.stockout_ids');
        $print_type = I('post.print_type');
        $is_print = I('post.is_print');

        $result = array('status'=>0,'info'=>'成功');
        try{
            D("StockOutOrder")->newUpdatePrintLog($is_print,$stockout_ids,$print_type);
        }catch(BusinessLogicException $e){
            $result['info'] = $e->getMessage();
        }catch(Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-previewPrintTemplateData-'.$e->getMessage());
            $result['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    public function getFastSearchNum()
    {
        $result = array('status'=>0,'info'=>'成功','data'=>array());
        try{
            $update_num_list = I('post.list','',C('JSON_FILTER'));
            $result['data']=D('StockOutOrder')->getFastSearchNum($update_num_list);
        }catch (BusinessLogicException $e){
            $result['status']   = 1;
            $result['info']     = $e->getMessage();
        }catch (\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-getFastSearchNum-'.$e->getMessage());
            $result['status']   = 1;
            $result['info']     = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
 public	function dialogEditWarehouse($id)
    {
        $id = intval($id);
		$id_list = DatagridExtention::getIdList(array('edit_form'));
		
        try{
            self::getIDList($address_id_list,array('province','city','district','dialog','address_object'),CONTROLLER_NAME,'dialog_edit');
            try{
                $warehouse_info = D('Setting/Warehouse')->getEditWarehouseInfo($id);
            }catch(BusinessLogicException $e){
                $this->error($e->getMessage());
            }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
                $this->error(self::UNKNOWN_ERROR);
            }
            $this->addOrEditShowCommon($id_list['edit_form'],$warehouse_info,$address_id_list);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->error(self::UNKNOWN_ERROR);
        }
    }
	
	
	
    private function addOrEditShowCommon($form_id,$warehouse_info,$address_id_list)
    {
		$user_id = get_operator_id();
		if($user_id>1){
			$right = D('Setting/EmployeeRights')->field('is_denied')->where(array('employee_id'=>$user_id,'type'=>0,'right_id'=>11))->select();
			if(empty($right) || $right[0]['is_denied'] == 1){
				exit('<div style="padding:6px">您没有权限操作该项</div>');
			}
		}

        $dialog_list = array(
            'form'=>$form_id,
            'province'=>$address_id_list['province'],
            'city'=>$address_id_list['city'],
            'district'=>$address_id_list['district'],
            'source_js'=>$address_id_list['dialog'],
            'address_object' => $address_id_list['address_object'],
        );


        $this->assign('warehouse_info',json_encode($warehouse_info));
        $this->assign('dialog_list',$dialog_list);
        $this->assign('dialog_list_json',json_encode($dialog_list));
        $this->display('edit_warehouse');
    }
    private function compJsonStr($userId,$type,$lid,$tid,$code){
        if($type == 5) return $tid;
        $jsonData = [];
        $data = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$userId,'type'=>$type,'code'=>$code))->select();
        $data = $data[0]['data'];
        if(is_object(json_decode($data))){
            $jsonData = json_decode($data,true);
            $jsonData[$lid] = $tid;
        }else{
            $jsonData[$lid] = $tid;
        }
        return json_encode($jsonData);
    }
	public function saveDefaultTemplate(){
		$template_id  = (int)I('post.template_id');
		$type = (int)I('post.template_type');
		$logistics_id = I('post.logistics_id');
		$result = array('status'=>0,'msg'=>'');
		try{
			if($type == 6){
				$template_db = D('Setting/PrintTemplate');
				$template_info = $template_db->getTemplateType($template_id);
				$template_info = $template_info[0];
				if(((int)$template_info['type'] == 2)||((int)$template_info['type'] == 8)){
					$type = 7;
				}
			}
            $user_id = get_operator_id();
			$code = '';
            $template_id = $this->compJsonStr($user_id,$type,$logistics_id,$template_id,$code);
            $template_data = array(
				'type'=>$type,
				'data'=>$template_id,
				'user_id'=>$user_id,
                'code'=>$code
			);
			$res = D('Setting/UserData')->add($template_data,'',true);
			\Think\Log::write('保存模板---'.print_r($res,true),\Think\Log::WARN);
		 }catch(BusinessLogicException $e){
               $result = array('status'=>1,'msg'=>$e->getMessage());
         }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
               $result = array('status'=>1,'msg'=>self::UNKNOWN_ERROR);
         }
			$this->ajaxReturn($result);
	}
	public function  synchronousLogistics(){
		try{
			$params = I('','',C('JSON_FILTER'));
			$ids = $params['ids'];
			$error = array();
			 if(empty($ids))
			{
				$result['info'] ="请选择出库单";
				$result['status'] = 1;
				$this->ajaxReturn($result);
			}
			$id_array = D('Stock/StockOutOrder')->field('stockout_id')->where(array('stockout_id'=>array('in',$ids)))->order('stockout_id')->select();
			foreach($id_array as $k=>$v){					
				$result = D('Stock/SalesStockOut')->synchronousLogistics($v['stockout_id'],$error);
			}
			if(empty($error)){
				$result = array('status'=>0,'info'=>'预物流同步成功，正在等待同步(无物流单号类型的物流不进行同步)');
			}else{
				$result = array('status'=>2,'data'=>$error);
			}
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write('tongbu--'.$e->getMessage());
			$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn($result);
		
	}
	public function getMessageRight(){
		try{
			$result = array('status'=>0,'info'=>'');
			$code = 'print_message';
			$user_data = D('Setting/UserData')->getMessageRights($code);
			if(empty($user_data)){
				$result['status'] = 1;
			}
		}catch(\Exception $e){
			\Think\Log::write('message--'.$e->getMessage());
			$result['status'] = 2;
			$result['info'] = self::UNKNOWN_ERROR;
		}
		$this->ajaxReturn($result);
	}
	
	public function message(){
		$id_list = array();
		$id_list = self::getIDList($id_list,array('form'),'','message');
		$content = '确认发货前可以先使用预同步物流功能，这样可以避免确认发货之后物流还没同步到平台而恰好此时用户执行退款操作等流程造成的订单问题！';
		$this->assign('content',$content);
		$this->assign('form_id',$id_list['form']);
		$this->assign('name','print_consign');
		$this->display('message');
	}
	
	public function addMessage(){
		try{
			$result = array('status'=>0,'info'=>'');
			$operator_id = get_operator_id();
			$data = array(
				'user_id'=>$operator_id,
				'type'=>10,
				'code'=>'print_message',
				'data'=>'0',
			);
			D('Setting/UserData')->add($data);
		}catch(\Exception $e){
			\Think\Log::write('message--'.$e->getMessage());
			$result['status'] = 1;
			$result['info'] = self::UNKNOWN_ERROR;
		}
		$this->ajaxReturn($result);
	}
	
	public function checkBlock(){
		$consign_info = I('post.ids','',C('JSON_FILTER'));
		$is_force = I('post.is_force');
		$result = array(
				'status'=>0,
				'info'=>'success',
				'data'=>array()
		);
		$fail = array();
		$success = array();
		if(empty($consign_info))
		{
			$result['info'] ="请选择出库单";
			$result['status'] = 1;
			$this->ajaxReturn($result);
		}
		 $query_config_values = get_config_value(array('order_deliver_block_consign','prevent_online_block_consign_stockout'),array(0,0));
          
		foreach ($consign_info as $key=>$id)
		{  
			$block_info = D('Stock/StockOutOrder')->field('block_reason,stockout_no')->where(array('stockout_id'=>$id))->find();
			if((int)$block_info['block_reason'] != 0 && (!((int)$block_info['block_reason'] & 4096) || ((int)$block_info['block_reason'] & 4096 && ($query_config_values['order_deliver_block_consign'] != 1 ||  ($query_config_values['order_deliver_block_consign'] == 1 && $query_config_values['prevent_online_block_consign_stockout'] == 0))))){
				$solve_way = '请将订单驳回审核到订单审核界面进行处理';
				switch((int)$block_info['block_reason']){
					case 1 :
						$msg = '出库单已经被拦截，拦截原因:申请退款';
						break;
					case 2 :
						$msg = '出库单已经被拦截，拦截原因:已退款';
						break;
					case 4 :
						$msg = '出库单已经被拦截，拦截原因:地址被修改';
						break;
					case 8 :
						$msg = '出库单已经被拦截，拦截原因:发票被修改';
						break;
					case 16 :
						$msg = '出库单已经被拦截，拦截原因:物流被修改';
						break;
					case 32 :
						$msg = '出库单已经被拦截，拦截原因:仓库变化';
						break;
					case 64 :
						$msg = '出库单已经被拦截，拦截原因:备注修改';
						break;
					case 128 :
						$msg = '出库单已经被拦截，拦截原因:更换货品';
						break;
					case 256 :
						$msg = '出库单已经被拦截，拦截原因:取消退款';
						break;	
					case 4100 :
						$msg = '出库单已经被拦截，拦截原因:地址被修改,平台已发货';
						break;
					case 4096:
						$msg = '出库单已经被拦截，拦截原因:原始单已发货';
						break;
					case 66:
						$msg = '出库单已经被拦截，拦截原因:已退款,备注修改';
						break;
					case 3:
						$msg = '出库单已经被拦截，拦截原因:申请退款,已退款';
						break;
					case 4101:
						$msg = '出库单已经被拦截，拦截原因:申请退款,地址被修改,平台已发货';
						break;
					default :
						$msg = '出库单已经被拦截，拦截原因:多种拦截原因，请到页面查看原因';
						break;
				}
				
				if((int)$block_info['block_reason'] & (1|2|4|32|128|256)){
					$solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'.$id.')">驳回到订单审核重新审核</a>';
				
				}else{
					if((int)$block_info['block_reason'] & 4096){
						$solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'.$id.')">取消拦截</a>';
					
					}else{
						$solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'.$id.')">驳回到订单审核重新审核</a>';
						$solve_way .= ' 或 ';
						$solve_way .= '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'.$id.')">取消拦截</a>';
					}
				}
				
				$fail[] = array(
					'stock_id'=>$id,
					'stock_no'=>$block_info['stockout_no'],
					'msg'=>$msg,
					'solve_way'=>$solve_way,
				);
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
		$this->ajaxReturn($result);
	}

	//获取打印批次列表
	public function getDialogPrintBatchList(){
		$id_list  = [
			"datagrid"      => "print_batch_list_datagrid",
			"toolbar"       => "print_batch_list_toolbar",
			"form"          => "print_batch_list_form",
			"tab_container" => "print_batch_list_tab_container",
		];
		$datagrid = [
			'id'      => $id_list["datagrid"],
//			"style"   => "height:200px",
//			"class"    =>'',
			'options' => [
				'url'        => U("StockSalesPrint/getPrintBatchData"),
				'toolbar'    => $id_list["toolbar"],
				'fitColumns' => false,
				'rownumbers' => true,
				'pagination' => true,
				'method'     => 'post',
				"fit"          => true,
			],
			"fields"  => get_field("StockSalesPrint", 'print_batch')
		];
		$arr_tabs = [
			[
				'id'    => $id_list["tab_container"],
				'url'   => U("StockSalesPrint/getPrintBatchDetailList"),
				'title' => '订单详情'
			],
		];
		$params   = [
			"controller" => strtolower(CONTROLLER_NAME),
			"datagrid"   => ["id" => $id_list["datagrid"]],
			"tabs"       => [
				'id'    => $id_list["tab_container"],
				'url'   => U("StockSalesPrint/getPrintBatchDetailList"),
				'title' => '订单详情'
			],
			"search"     => ["form_id" => $id_list["form"]]
		];
		$this->assign("id_list", $id_list);
		$this->assign('datagrid', $datagrid);
		$this->assign('arr_tabs', json_encode($arr_tabs));
		$this->assign('params', json_encode($params));
		$this->display('print_batch_list');
	}
	//获取打印批次数据
	public function getPrintBatchData($page = 1, $rows = 20, $search = [], $sort = 'rec_id', $order = 'desc') {
		$res = D("StockOutOrder")->getPrintBatchList($page, $rows, $search, $sort, $order);
		$this->ajaxReturn($res);
	}
	//获取打印批次订单详情
	public function getPrintBatchDetailList() {
		if (IS_POST) {
			$id = I("post.id");
			$sort = I("post.sort");
			$order = I("post.order");
			$page = I("post.page");
			$rows = I("post.rows");
			if(empty($sort)){$sort='id';}
			if(empty($order)){$order='desc';}
			if(empty($page)){$page=1;}
			if(empty($rows)){$rows=20;}
			if(empty($id) || $id == ''){$this->ajaxReturn(array('total' => 0, 'rows' => array()));}
			$id_list = M('stockout_print_batch')->fetchSql(false)->field('queue')->find($id);
			if(empty($id_list['queue']) || $id_list == null){$this->ajaxReturn(array('total' => 0, 'rows' => array()));}
			$search = array('print_batch'=>$id_list['queue']);
			$this->ajaxReturn(D('StockOutOrder')->searchStockoutList($page, $rows, $search, $sort, $order, 'stockoutPrint'));
		} else {
			$fields = get_field("StockSalesPrint", "salesstockout_order");
			unset($fields['checkbox']);
			$id_list  = [
				"datagrid" => "print_batch_tabs_detail_datagrid"
			];
			$datagrid = [
				"id"      => $id_list["datagrid"],
//				"style"   => "height:200px",
//				"class"    =>'',
				"options" => [
					//'url'        => U("StockSalesPrint/getPrintBatchDetailList"),
					'fitColumns' => false,
					'rownumbers' => true,
					'pagination' => true,
					'method'     => 'post',
					"fitColumns"   => false,
					"fit"          => true,
				],
				"fields"  => $fields
			];
			$this->assign("id_list", $id_list);
			$this->assign("datagrid", $datagrid);
			$this->display("tabs_print_batch_detail");
		}
	}

	public function writeWeight(){
		try{
		$id_list = array();
		$pagenumber = I('get.pagenumber');
		$id_list = self::getIDList($id_list,array('form'),'','writeWeight');
		$this->assign('pagenumber',(int)$pagenumber+1);
		$this->assign('id_list',$id_list);
		}catch(Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->display('weight');
	}
	public function writePackages(){
		$id_list = array(
			'tool_bar'                       => 'add_package_datagrid_toolbar',
			'form'                       	 => 'add_package_datagrid_form',
			'datagrid'                       => 'add_package_datagrid',
		);

		$fields = get_field('StockSalesPrint','stockout_add_package');

		$datagrid = array(
			'id'=>$id_list['datagrid'],
			'options'=> array(
				'title' => '',
				'toolbar' => "#{$id_list['tool_bar']}",
				'fitColumns'   => false,
				'singleSelect'=>true,
				'ctrlSelect'=>false,
				'methods'=>'loader:getPackageList',
				'pagination'=>false
			),
			'fields' => $fields,
			'class' => 'easyui-datagrid',
			'style'=>"overflow:scroll",
		);
		$this->assign('tool_bar',$id_list['tool_bar']);
		$this->assign('form',$id_list['form']);
		//$this->assign('logistics_name', 'name');
		//$this->assign('logistics_rule', 'rule');
		$this->assign('datagrid', $datagrid);
		$this->display('add_package_list');
	}
	public function saveWeight(){
		try{
			$result = array('status'=>0);
			$weights = I('post.weights');
			$logistics_nos = I('post.logisticsno');
			$stockout_ids = I('post.stockout_ids');
			$logistics_id = I('post.logistics_id');
			$weight_list = explode(',',$weights);
			$logistics_nos_list = explode(',',$logistics_nos);
			foreach($logistics_nos_list as $k=>$v){
				$post_cost = D('Setting/LogisticsFee')->calculPostCost($stockout_ids,$weight_list[$k],$logistics_id);
				$weight = array('weight'=>$weight_list[$k],'post_cost'=>$post_cost);
				D('Stock/SalesMultiLogistics')->where(array('stockout_id'=>$stockout_ids,'logistics_no'=>$v))->save($weight);
			}
		}catch(Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1);
		}
		$this->ajaxReturn($result);
	}
	//获取选择包含货品列表
	public function getDialogIncludeGoodsList(){
		$type = I('get.type');
		$id_list  = [
			"datagrid"      => "include_goods_list_datagrid",
			"toolbar"       => "include_goods_list_toolbar",
			"form"          => "include_goods_list_form",
			"add_spec"      => "include_show_dialog",
		];
		if($type == 'not_include'){
			foreach($id_list as $k => $v){
				if($k != 'add_spec')
				$id_list[$k] = 'not_' . $v;
			}
		}
		$fields = get_field("StockSalesPrint", 'include_goods');
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
		//$params['fields_to_editor_type'] = array('num'=>'numberbox');
		$this->assign("id_list", $id_list);
		$this->assign('datagrid', $datagrid);
		$this->assign('type', $type);
		$this->assign('params',json_encode($params));
		$this->display('include_goods_list');
	}
	public function validatePick(){
		try{
			$result = array('status'=>0);
			$order_num = I('post.num');
			$pick_num = M('cfg_sorting_wall')->alias('csw')->field('count(swd.box_id) as pick_num')->join('left join sorting_wall_detail swd on csw.wall_id = swd.wall_id')->where(array('csw.type'=>1))->select();
			if(!empty($pick_num) && $pick_num[0]['pick_num'] != 0 && $order_num > $pick_num[0]['pick_num']){
				SE('订单数量不能大于分拣框数量');
			}
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch(Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn($result);
	}
	// 根据物流单号筛选订单
	public function searchLogisticsNos(){
		if (IS_POST) {
			$data=I('post.data','',C('JSON_FILTER'));
			$logistics_nos_str=$data['passel_logistics_nos'];
			$logistics_nos_arr=array();
			switch ($data['search_logistics_nos_separator']) {
				case '0':
					$logistics_nos_arr=explode("\r\n",$logistics_nos_str);
					$logistics_nos_str=preg_replace('/[\r\n]+/', ',', $logistics_nos_str);
					break;
				case '1':
					$logistics_nos_arr=explode(' ',$logistics_nos_str);
					$logistics_nos_str=preg_replace('/ +/', ',', $logistics_nos_str);
					break;
				case '2':
					$logistics_nos_arr=explode(',',$logistics_nos_str);
					$logistics_nos_str=preg_replace('/,+/', ',', $logistics_nos_str);
					break;
				case '3':
					$logistics_nos_arr=explode(';',$logistics_nos_str);
					$logistics_nos_str=preg_replace('/;+/', ",", $logistics_nos_str);
					break;
				default:
					$logistics_nos_str='';
					break;
			}
			$logistics_nos_str=rtrim($logistics_nos_str,",");
			$result_str='';$result=array();
			$result=explode(',',$logistics_nos_str);
			foreach ($result as $v) {
				$result_str.="'".trim($v)."',";
			}
			$result_str=rtrim($result_str,",");
			if(count($logistics_nos_arr)>200){
				$result=array('status'=>1,'info'=>'物流单号不能多于200条');
			}else{
				$result=array('status'=>0,'info'=>$result_str);
			}
			$this->ajaxReturn($result);
		}else{
			$logistics_nos_params = array(
				'form_id'		=> 'search_logistics_nos_form',
				'form_url'		=> U('StockSalesPrint/searchLogisticsNos')
			);
			$this->assign("logistics_nos_params", $logistics_nos_params);
			$this->display('dialog_search_logistics_nos');
		}
	}
	// 导入物流单号
	public function importLogisticsNos(){
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
			$excelClass->uploadFile($file,"StockSalesPrintImport");
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
			if($sheet[0][0]!='物流单号'){
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
	
}