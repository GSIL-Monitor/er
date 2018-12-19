<?php
namespace Stock\Controller;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;
class SalesStockOutController extends BaseController {
    public function getStockoutList(){
        $id_list = DatagridExtention::getIdList(array('tool_bar', 'tab_container', 'datagrid', 'tab_stockout_order_detail',
            'tab_sales_trade_detail', 'tab_log', 'tab_note', 'more_button', 'more_content',
            'hidden_flag', 'set_flag','search_flag', 'dialog','form','fast_div','SMS'));
        $fields = D('Setting/UserData')->getDatagridField('SalesStockOut','sales_stockout');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("SalesStockOut/search"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>false,
                'ctrlSelect'=>true,
                //'idField'=>'id',
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
		$checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($datagrid['fields'],$checkbox);
        try{
            $flag=D('Setting/Flag')->getFlagData(5);
            $search_condition = UtilDB::getCfgRightList(array('shop','logistics','warehouse'));
            $chg_logistics_list = UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>0)));
            $search_condition['chg_logistics_list'] = $chg_logistics_list['logistics'];
            $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $search_condition['warehouse']);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
        }

        $params=array(
            'datagrid'=>array('id'=>$id_list['datagrid'],'url' => U("SalesStockOut/search")),
            'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
            "SMS"=> array("id"=> $id_list['SMS'],"title" => "短信发送","url"=> U("SalesStockOut/SMS")),
            'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('StockCommon/showTabDatagridData')),
            //'edit'=>array('id'=>$id_list['edit'],'url'=>U('TradeManage/setTradeFlag'),'title'=>'颜色标记'),
            'flag'=>array('set_flag'=>$id_list['set_flag'],'url'=>U('Setting/Flag/flag').'?flagClass=5','json_flag'=>$flag['json'],'list_flag'=>$flag['list'],'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=5','title'=>'颜色标记'),'search_flag'=>$id_list['search_flag']),
            'new'=>array('form'=>array('dialog_type'=>'stockout')),
			'revert_reason'=>array(
                'id'=>'stocksalesout_revertreason_id',
                'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=1&model_type=salesstockout',
                'title'=>'驳回原因',
                'width'=>400,
                'height'=>'auto',
                'ismax'=>false,
                'form' =>array('url'=>U("Stock/SalesStockOut/revertStockoutOrder"),'id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox','dialog_type'=>'stockout')
            )

        );

        $tab_list = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order_detail")).'?prefix=salesStockout&tab=stockout_order_detail','id' =>$id_list['tab_container'],'title' => '出库单详情'),
            array('url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>"stockout_order")).'?prefix=salesStockout&tab=sales_order_detail',"id"=>$id_list['tab_container'],"title"=>"订单详情"),
            array('url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>"sales_stockout_log")).'?prefix=salesStockout&tab=sales_stockout_log','id' =>$id_list['tab_container'],'title' => '日志',),
        );
        $arr_tabs = json_encode($tab_list);
        //获取打印授权
        /*$params['host'] = get_intranet_ip();
        $str = get_service_data('license', $params);
        $str_licenses = $str->info->print_license;*/
        $query_config_values = get_config_value(array('order_deliver_block_consign','prevent_online_block_consign_stockout'),array(0,0));
		$faq_url=C('faq_url');
        $this->assign('faq_url',$faq_url['sale_question']);
        $this->assign('list',$search_condition);
        $this->assign('warehouse_array', $warehouse_array);
        $this->assign("params",json_encode($params));
        $this->assign('center_container',$id_list['tab_container']);
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('online_consign_block', $query_config_values['order_deliver_block_consign']);
        $this->assign('prevent_online_consign_block_stockou', $query_config_values['prevent_online_block_consign_stockout']);
        $this->assign('tool_bar',$id_list['tool_bar']);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list",$id_list);
        $this->display('sales_stockout_edit');

    }
    public function search($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc') {
        try{
            $data = D('StockOutOrder')->searchStockoutList($page, $rows, $search, $sort, $order, 'salesstockout');
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }

        $this->ajaxReturn($data);
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
			$res_so_info = D('Stock/StockOutOrder')->field('warehouse_type,stockout_no,status')->where(array('stockout_id'=>$id))->find();
			if((int)$res_so_info['warehouse_type'] == 11 && (int)$res_so_info['status'] ==60 )
            {
                $fail[] = array(
					'stock_id' => $id,
					'stock_no' => $res_so_info['stockout_no'],
					'msg'      => '待出库的委外订单请点击取消委外单',
				);
				continue;
            }
            if((int)$res_so_info['status'] ==5){
                $fail[] = array(
                    'stock_id' => $id,
                    'stock_no' => $res_so_info['stockout_no'],
                    'msg'      => '该订单已经驳回审核，请刷新页面后重试',
                );
                continue;
            }
			D('Stock/SalesStockOut')->revertSalesStockout($id,$form_params,$fail,$success[$key],true,'stockSalesout');
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
        $result['type']="new";
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

    public function consignStockoutOrder($ids)
    {
        set_time_limit(0);
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
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        foreach ($consign_info as $key=>$id)
        {   $success[$key] = array();
            D('Stock/SalesStockOut')->consignStockoutOrder($id,$fail,$success[$key]);
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

    /*
     * 导出销售出库单
     *
     */
    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $result = array('status'=>0,'info'=>'');
        try{
			$id_list = I('get.id_list');
            $type = I('get.type');
			if($id_list == ''){
				$search = I('get.search','',C('JSON_FILTER'));
				$startnum = strlen('search[');
				$endnum = strlen('search[]');
				foreach ($search as $k => $v) {
					$key=substr($k,$startnum,strlen($k)-$endnum);
					$search[$key]=$v;
					unset($search[$k]);
				}
				D('SalesStockOut')->exportToExcel($search,$id_list,$type);
			}else{
				D('SalesStockOut')->exportToExcel('',$id_list,$type);
			}
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }

    /**
     * 发送短信
     */
    public function SMS() {
        if (IS_POST) {
            $sms     = I("post.sms");
            $ids = $sms['ids'];
            $message = $sms["message"];
            try{
                if($sms['template_id']=='无'){
                    $data = D('SalesStockOut')->getSMSData($ids);
                    $mobiles = array();
                    foreach($data as $row){
                        $mobiles[] = $row['receiver_mobile'];
                    }
                    $mobiles = implode(',',$mobiles);
                    $id=D('Customer/CustomerFile')->addSMSList($mobiles,$message);
                    $message=$message.' 回T退订';
                    $res= UtilTool::SMS($mobiles, $message, 'market');
                    D('Customer/CustomerFile')->updateSMSStatus($id,$res);
                    $this->ajaxReturn($res);
                }else{
                    $data=D('SalesStockOut')->getSMSData($ids);
                    $templet=array(
                        '{客户网名}','{客户姓名}','{原始单号}','{店铺名称}',
                        '{物流单号}','{物流公司}','{下单时间}','{发货时间}',
                        '{收货地区}','{收货地址}'
                    );
                    foreach($data as $row){
                        $data=array(
                            $row['buyer_nick'],$row['receiver_name'],$row['src_tids'],$row['shop_name'],
                            $row['logistics_no'],$row['logistics_name'],$row['trade_time'],$row['consign_time'],
                            $row['receiver_area'],$row['receiver_address']
                        );
                        $send_msg=str_replace($templet,$data,$message);
                        if(strpos($send_msg,"{")){
                            $return=array(
                                'status'=>1,
                                'info'=>'该模板需要信息不完全，请更换其他模板！'
                            );
                            $this->ajaxReturn($return);
                        }
                        $id=D('Customer/CustomerFile')->addSMSList($row['receiver_mobile'],$send_msg);
                        $msg=UtilTool::SMS($row['receiver_mobile'], $send_msg, '');
                        D('Customer/CustomerFile')->updateSMSStatus($id,$msg);
                        $return=array(
                            'status'=>$msg['status'],
                            'info'=>$msg['info']
                        );
                        /*  if($msg['status']!=0){
                            $return=array(
                                'status'=>1,
                                'info'=>$msg['info']
                            );
                        }
                         $res[]=array(
                            'mobile'=>$row['receiver_mobile'],
                            'message'=>$msg['info']
                        );*/
                    }
                    /* $return=array(
                         'status'=>2,
                         'info'=>$res
                     );*/
                    $this->ajaxReturn($return);
                }
            }catch (BusinessLogicException $e) {
                $res = array('status'=>1,'info'=>$e->getMessage());
            }catch(\Exception $e){
                $res = array('status'=>1,'info'=>$e->getMessage());
            }
            $this->ajaxReturn($res);

        } else {
            $id_list  = array(
                "datagrid" => "SalesStockOut_SMS_datagrid",
                "list"     => "SalesStockOut_SMS_list",
                "message"  => "SalesStockOut_SMS_message",
                "show"     => "salesstockout_datagrid",
                "dialog"   => "salesstockout_SMS"
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
            $this->display("dialog_SMS");
        }
    }

    public function templateToContent()
    {
        $sms_id = I('post.id');
        try{
            $data=D('Setting/SmsTemplate')->getContentById($sms_id);
            if(!empty($data['content'])&&!empty($data['sign'])){
                $data['sign'] = "【".$data['sign']."】";
                $content = $data['sign'].$data['content'];
            }
            $this->ajaxReturn($content);
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $res['status'] = 1;
            $res['info'] = '未知错误，请联系管理员';
            $this->ajaxReturn($res) ;
        }
    }
	
	 public function cancel($ids)
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
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			$result_info = array();
			foreach ($consign_info as $key=>$id){   
				$stockout_order_info_fields = array("so.stockout_id","so.stockout_no","st.platform_id","st.shop_id","st.trade_type","st.src_tids","so.stockout_no","so.warehouse_id","so.warehouse_type","so.src_order_type","so.src_order_id","so.status","so.consign_status","so.logistics_id","so.logistics_no","so.customer_id","cl.logistics_type","cl.bill_type","so.post_cost","so.weight","so.receiver_area","so.checkouter_id","so.freeze_reason","so.block_reason","so.src_order_no","so.is_allocated","so.pos_allocate_mode");
				$stockout_order_info_cond = array(
					'stockout_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/StockOutOrder')->getSalesStockoutOrder($stockout_order_info_fields,$stockout_order_info_cond);
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				if ((int)$res_so_info['src_order_type']<>1)
				{
					SE("出库单不是销售出库单");
				}
				if ((int)$res_so_info['status']!= 57 && (int)$res_so_info['status']!= 60)
				{
					SE("订单状态不正确");
				}
				if ((int)$res_so_info['freeze_reason']<>0)
				{
					SE("出库单已冻结");
				}
				if((int)$res_so_info['consign_status']&4)
				{
					SE("订单已出库");
				}
				if((int)$res_so_info['warehouse_type']!=11 && (int)$res_so_info['warehouse_type']!=15)
				{
					SE("仓库类型不正确");
				}
				$reason_id = 1;
			
				$WmsManager = ManagerFactory::getManager("Wms");
				$WmsManager->manual_wms_adapter_cancel_order($sid, $uid, $id, $reason_id,$res_so_info['stockout_no'],$result_info);//$reason_id'驳回原因'
			}
			if(empty($result_info)){
				$result['info'] ="取消成功";
				$result['status'] = 0;
			}else{
				$result['info'] =$result_info;
				$result['status'] = 2;
			}
		} catch (BusinessLogicException $e){
           \Think\Log::write('cancel-'.$e->getMessage());
			 $result['info'] =$e->getMessage();
             $result['status'] = 1;
        }catch (\Exception $e) {
            \Think\Log::write('cancel-'.$e->getMessage());
			 $result['info'] =$e->getMessage();
             $result['status'] = 1;
        }
        $this->ajaxReturn($result);
        
    }
	
	 public function hand($ids)
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
            $result['info'] ="请选择出库单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$stockout_order_info_fields = array("so.stockout_id","st.platform_id","st.shop_id","st.trade_type","st.src_tids","so.stockout_no","so.warehouse_id","so.warehouse_type","so.src_order_type","so.src_order_id","so.status","so.consign_status","so.logistics_id","so.logistics_no","so.customer_id","cl.logistics_type","cl.bill_type","so.post_cost","so.weight","so.receiver_area","so.checkouter_id","so.freeze_reason","so.block_reason","so.src_order_no","so.is_allocated","so.pos_allocate_mode");
				$stockout_order_info_cond = array(
					'stockout_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/StockOutOrder')->getSalesStockoutOrder($stockout_order_info_fields,$stockout_order_info_cond);
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				if ((int)$res_so_info['src_order_type']<>1)
				{
					SE("出库单不是销售出库单");
				}
				if ((int)$res_so_info['status']!= 56)
				{
					SE("不是推送失败订单");
				}
				if ((int)$res_so_info['freeze_reason']<>0)
				{
					SE("出库单已冻结");
				}
				if((int)$res_so_info['consign_status']&4)
				{
					SE("订单已出库");
				}
				if((int)$res_so_info['warehouse_type']!=11)
				{
					SE("仓库类型不正确");
				}
				D('Stock/SalesStockOut')->execute("UPDATE stockout_order SET `status`=57,wms_status=0,error_info='' WHERE stockout_id=".$id);
			    D('Stock/SalesStockOut')->execute('INSERT INTO sys_asyn_task(task_type,target_type,target_id,target_no,operator_id,created) values(1,1,'.$res_so_info['src_order_id'].',"'.$res_so_info['src_order_no'].'",'.$uid.',NOW()) ON DUPLICATE KEY UPDATE `status`=0,rand_flag=0,other_info=VALUES(other_info)');
					
				
			}
		} catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
        
    }
}