<?php
namespace Home\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilTool;
use Platform\Common\ManagerFactory;
class IndexController extends BaseController
{
    public function index()
    {
    	$time=array();
    	$notification_db=D('Notification');
    	$res_note_info=$notification_db->getNotification('nt.message',array('nt.type'=>array('eq',1)),'nt',array(),'nt.rec_id DESC');
    	$menu_list = $this->getLeftMenu();
		$stalls_system_init=get_config_value('stalls_system_init',0);
		if($stalls_system_init == 0){
			$stalls_where = array('key' => array('in',array('stall_wholesale_mode','order_check_give_storting_box','dynamic_allocation_box')));
			$stalls_update = array('value'=>'0','modified'=>array('exp','NOW()'));
			D('Setting/System')->where($stalls_where)->save($stalls_update);
		}
		//$role=D('Setting/Employee')->getRole(get_operator_id());
    	//$roles=array('普通用户','管理员','超级管理员');
        //$this->assign('role', $roles[$role]);
        $this->assign('note', $res_note_info['message']);
		$common_menu=D('Setting/System')->commonMenu();
		$this->assign('common_menu', $common_menu);
        $this->assign('menu_list', $menu_list);
		$log_update_time=filemtime(APP_PATH."Help/Controller/update.txt"); //获取日志文件的更新时间（时间戳类型）
		$now = time();
		$special_notify ='';
		if(date('m',$now) ==11 && date('d',$now)>8 && date('d',$now)<11){
			$special_notify ='special_notify';
		}
		$show_log=$this->checkLogin($log_update_time,$special_notify);
		//若中途修改了js、css文件升级到线上，可以打开下面一行，每变更一次改变下后面数字即可，更改版本号，以刷新缓存，但不会提醒升级日志，下次迭代升级时可以注释掉
		$log_update_time += 4;

        $this->assign('version_number', $log_update_time);//js、css版本号
        $faq_url=C('faq_url');
//		$todo=$this->getTodo();
//		$this->assign('todo',$todo);//代办事项显示数据
		$this->assign('faq_url', $faq_url['faq']);//faq的地址
		$this->assign('show_log',$show_log);//是否显示更新信息
		$this->assign('menu_list_data', json_encode($menu_list));
		//获取提示信息
		$alarm=$this->getAlarmData($todo);
		$this->assign('alarm', json_encode($alarm));
		$this->assign('alarm_num', $alarm['type']);
		//订单余额信息
		$order_cost = get_config_value('order_cost',0);
		$this->assign('order_cost', $order_cost);
		if($order_cost != 0){
			$where = array(
					'user_id' =>0,
					'tag' => 0,
					'type' => 7,
					'code' => 'stalls_order_num'
			);
			$tmp_num = M('cfg_user_data')->field('data')->where($where)->find();
			$order_send_num = empty($tmp_num)? 0: intval($tmp_num['data']);
			$order_total_num = get_config_value('order_total_num',0);
			$order_num_set = get_config_value('order_balance',0);
			if($order_num_set){
				$set_order_num = get_config_value('order_balance_num',0);
				$order_hint = intval($order_total_num)-$order_send_num < $set_order_num ?1:0;
			}else{
				$order_hint = 0;
			}
			$this->assign('order_hint', $order_hint);
			$this->assign('order_balance', intval($order_total_num)-$order_send_num);
		}
		//图表信息
		$chart=$this->getChartData();
		$this->assign('chart',json_encode($chart));
		//订单实时数据
// 		$trade_num=D('Trade/SalesTradeLog')->getTradeNumByLog();
// 		$this->assign('trade_num',$trade_num);
		//判断新老手模式显示
		$is_new=$this->checkNewcomer();
		$this->assign('is_new',$is_new);
		if($is_new){
			$newcomer=$this->getNewcomer();
		}else{
			$newcomer=array(
					'platform'=>array(
						'shop'=>9,
						'goods'=>25
				),
				'goods'=>array(
						'class'=>18,
						'brand'=>19,
						'goods_archives'=>20,
				),
				'stock'=>array(
						'logistics'=>10,
						'warehouse'=>11,
						'inventory_management'=>40
				)
			);
		}
		$role=D('Setting/Employee')->getRole(get_operator_id());
		$this->assign("role", $role);
		$this->assign('platform',$newcomer['platform']);
		$this->assign('goods',$newcomer['goods']);
		$this->assign('stock',$newcomer['stock']);
		$this->display('Home@Index:index');
	}

	/*获取代办事项数据
	 * $type data 返回数组给后台调用函数  ajax 使用ajaxReturn返回前台页面
	 * */
	public function getTodo(){
// 		try{
// 			//获取订单相关信息
// 			$types=array(
// // 				'fast_printed_not_stockout',
// // 				'fast_stockout_not_printed',
// 				'alarmperday',
// 				'fast_is_blocked'
// 			);
// 			$trade_number=D('Stock/StockOutOrder')->getFastSearchNum($types);
// 			$data['fast_printed_not_stockout']=$trade_number['fast_printed_not_stockout']?$trade_number['fast_printed_not_stockout']:0;
// 			$data['fast_stockout_not_printed']=$trade_number['fast_stockout_not_printed']?$trade_number['fast_stockout_not_printed']:0;
// 			$data['alarmperday']=$trade_number['alarmperday']?$trade_number['alarmperday']:0;
// 			$data['fast_is_blocked']=$trade_number['fast_is_blocked']?$trade_number['fast_is_blocked']:0;
// 		}catch (\Exception $e){
// 			$data['fast_printed_not_stockout']='--';
// 			$data['fast_stockout_not_printed']='--';
// 			$data['alarmperday']='--';
// 			$data['fast_is_blocked']='--';
// 		}
 		try{
 			//获取店铺相关信息
 			$shop_number=$this->getNotificationList('total');
 			$data['shop_not_authorize']=$shop_number;
 		}catch (\Exception $e){
 			$data['shop_not_authorize']='--';
 		}
		try{
			//获取货品相关信息
			$goods_number=D('Trade/ApiTradeOrder')->getInvalidGoods();
			$data['not_mate_goods']=$goods_number;
		}catch (\Exception $e){
			$data['not_mate_goods']='--';
		}
		try{
			//获取物流同步相关信息
			$logistics_count=D('Stock/ApiLogisticsSync')->getApiLogisticsSyncNumber();
			if($logistics_count!==false)$data['logistics_count']=$logistics_count;
		}catch (\Exception $e){
			$data['logistics_count']='--';
		}
		try{
			//获取电子面单相关信息
			$electronic_sheet_number=$this->getWayBillCount('count');
			$data['electronic_sheet_number']=$electronic_sheet_number;
		}catch (\Exception $e){
			$data['electronic_sheet_number']='--';
		}
		try{
			//获取短信相关信息
			$sid = get_sid();
			$SmsManager = ManagerFactory::getManager ("Sms");
			$sms_count = $SmsManager->manualGetBalance ($sid);
			//判断短信接口状态，如果状态不为0的话则返回值为0
			$sms_count = $sms_count['status']?0:$sms_count['info']['sms_num'];
			$data['sms_number']=$sms_count;
		}catch (\Exception $e){
			$data['sms_number']='查询失败';
		}
		return $data;
	}

	public function exception()
	{
		$this->display('Common@Exception:404');
	}

	public function showNotificationList(){
		$id_list = array(
				'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid'),
				'toolbar'=> 'not_auth_shop_datagrid_toolbar',
		);
		$datagrid=array(
				'id'=>$id_list['id_datagrid'],
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
						'title'=>'',
						'toolbar' => "#{$id_list['toolbar']}",
						'url'   => U('Home/Index/getNotificationList'),
						'fitColumns'=>false,
						'rownumbers'=>true,
						'pagination'=>false,
				),
				'fields' => get_field('Home/Index','dialog_not_authorize_shop')
		);
		$params = array(
				'datagrid' => array('id' => $id_list['id_datagrid'])
		);
		$this->assign('params', json_encode($params));
		$this->assign('id_list', $id_list);
		$this->assign('datagrid',$datagrid);
		$this->display('dialog_not_authorize_shop');
	}

	public function showElectronicSheetDetial(){
		$id_list = array(
				'id_datagrid' => 'electronic_sheet_detial_datagrid',
				'toolbar'=> 'electronic_sheet_detial_datagrid_toolbar',
		);
		$datagrid=array(
				'id'=>$id_list['id_datagrid'],
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
						'title'=>'',
						'toolbar' => "#{$id_list['toolbar']}",
						'url'   => U('Home/Index/getWayBillCount'),
						'fitColumns'=>false,
						'rownumbers'=>true,
						'pagination'=>false,
				),
				'fields' => get_field('Home/Index','electronic_sheet_detial')
		);
		$params = array(
				'datagrid' => array('id' => $id_list['id_datagrid'])
		);
		$this->assign('params', json_encode($params));
		$this->assign('id_list', $id_list);
		$this->assign('datagrid',$datagrid);
		$this->display('electronic_sheet_detial');
	}

	//获取未授权的店铺信息
    public function getNotificationList($type=''){
    	$data=array();
    	$notification_db=D('Notification');
    	try {
    		$now=date('y-m-d H:i:s',time());
    		$notification_db->addInvalidShopInfo();
    		$notification_db->getInvalidGoodsInfo();
    		$notification_db->deleteNotification(array('type'=>array('eq',2),'created'=>array('lt',$now)));
    		$notification_list=$notification_db->getNotificationList(
    				'IF(nt.sender=0,\'系统\',he.fullname) sender,nt.message,IF(nt.handle_oper_id=0,\'无\',he.fullname) handle_oper_id,ELT(nt.type,\'ERP通知\',\'异常通知\') type,nt.created',
    				array('nt.type'=>array('eq',2),'nt.is_handled'=>array('eq',0),'nt.created'=>array('egt',$now)),
    				'nt',
    				'LEFT JOIN hr_employee he ON he.employee_id=nt.handle_oper_id',
    				'nt.priority DESC'
    		);
    		foreach ( $notification_list as $k=> $v){
				if(strpos($v['message'],'店铺--')!==false){
					$message = $notification_list[$k]['message'];
					$start = strpos($message,'--');
					$end = strrpos($message,'--');
					$shop_name =substr($message,$start+2,$end-$start-2);
					$shop_info = D('Setting/Shop')->getShopByName($shop_name);
					$notification_list[$k]['message']="<a href='javascript:void(0)' style='color: red;' onclick='shopAuthor($shop_info)'>$message".('(点击授权)')."</a>";
					$notification[]=$notification_list[$k];
				}else{
					unset($notification_list[$k]);
				}
    		}
    		$notification_db->updateNotification(array('is_handled'=>1),array('type'=>array('eq',2),'created'=>array('egt',$now)));
			$data=array('rows'=>$notification,'total'=>count($notification));
    	}catch (\Exception $e){
    		$data=array('rows'=>array(),'total'=>0);
    	}
		if($type)return $data['total'];
    	$this->ajaxReturn($data);
    }
    
    public function getNotification()
    {
    	$res_note_info=D('Notification')->getNotification('nt.message',array('nt.type'=>array('eq',1)),'nt',array(),'nt.rec_id DESC');
    	$this->success($res_note_info['message']);
    }
    
    public function addNoteInfo()
    {
    	$message=I('get.message');
    	$secret=I('get.secret');
    	try {
    		if (empty($message))
    		{
    			E('消息不能为空');
    		}
    		if($secret!='admin')
    		{
    			return;
    		}
    		D('Notification')->addNoteInfo($message);
    	}catch (\Exception $e){
    		$this->error($e->getMessage());
    	}
    	$this->success();
    }
    
    private function getLeftMenu()
    {
    	$result=array();
    	$user_id=get_operator_id();
    	try 
    	{
            $result=D('Menu')->getMenuByUserId($user_id);
            $this->menu=$result;
			$result=UtilTool::array2tree($result, 'id', 'parent_id', 'children');
//    		foreach ($result as $k => $v)
//    		{
//    				$result[$k]['children']=json_encode($result[$k]['children']);
//    		}
            $result=array_reverse($result);
    	}catch (\Exception $e)
    	{
    		\Think\Log::write($e->getMessage());
    	}
    	return $result;
    }

	/*
	 * 函数：判断用户是否是迭代后初次登录系统
	 * 参数：$log_update_time 为 日志文件的最后更新时间 类型为时间戳
	 * 		$special_notify 特殊提示的参数。每天显示一次。（双十一使用增加的）
	 * 返回值：如果是初次登录返回true 不是返回false
	 * */
	public function checkLogin($log_update_time,$special_notify = ''){
		$employee_db=D('Setting/Employee');
		$user_id=get_operator_id();
		$employee=$employee_db->getEmployee('employee_id,last_login_time',array('employee_id'=>array('eq',$user_id)));
		$last_login_time=strtotime($employee['last_login_time']);
		$rst=false;
		$now = time();
		$arr_employee=array(
				'last_login_time' => date('Y-m-d H:i:s',$now),
		);
		if($special_notify == 'special_notify'){
			if(date('d',$last_login_time)<date('d',$now)){
				$rst=true;
				$employee_db->updateEmployee($arr_employee,array('employee_id'=>array('eq',$employee['employee_id'])));
				return $rst;
			}
			return $rst;
		}
		if($last_login_time<=$log_update_time){
			$rst=true;
		}

		$employee_db->updateEmployee($arr_employee,array('employee_id'=>array('eq',$employee['employee_id'])));
		return $rst;
	}

	/*
	 * 展示更新提示框页面函数
	 * */
	public function showUpdateLog(){
		$file = APP_PATH."Help/Controller/update.txt";
		$content = file_get_contents($file) or die("无法显示升级日志请联系管理员!");
		$text=explode("<br>",$content);
		$content="<span style='line-height:23px;font-size:14px;letter-spacing:1.5px'>".$text[1]."</span>";
		$this->assign('content',$content);
		$this->display('show_log');
	}
    //获取面单信息
    public function searchWayBill()
    {
        $result_info = array();
        $conditions = array();
        $conditions['type'] = 'searchAllWayBill';
        $waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
        $waybill->manual($result_info,$conditions);
        /*$result_info[status]  $result_info[success] 返回我给你传的数组
           返回的 $result_info[success]有可能为空要做好判断一下
        */
		if($result_info['status']!=0 || !$result_info['success'])return false;
		return $result_info['success'];
    }

	/*对电子面单数据进行处理
	 * $type  需要获取的数据类型  count 为面单数  data为详细信息
	 * */
	public function getWayBillCount($type=''){
		$data=$this->searchWayBill();
		if($type=='count'){
			$number=0;
			if(!$data)return '查询失败';
			foreach($data as $row){
				foreach($row['waybill_info'] as $kk=>$rr){
					foreach($rr['branch'] as $kkk=>$rrr){
						$number+=$rrr['quantity'];
					}
				}
			}
			return $number;
		}else{
			$return=array();
			if(!$data)$this->ajaxReturn($return);
			foreach($data as $row) {
				foreach ($row['waybill_info'] as $waybill_info_key => $waybill_info) {
					foreach ($waybill_info['branch'] as $branch_key=> $branch) {
						$res['shop_name'] = $row['shop_name'];
						$res['cp_code'] = $waybill_info['cp_code'];
						$res['branch_name'] = $branch['branch_name'];
						$res['allocated_quantity'] = $branch['allocated_quantity'];
						$res['quantity'] = $branch['quantity'];
						$return[] = $res;
					}
				}
			}
			$this->ajaxReturn($return) ;
		}
	}
	
	public function getWayBillAddress(){
		$data=$this->searchWayBill();
		$return=array();
		if(!$data)$this->ajaxReturn($return);
		foreach($data as $row) {
			foreach ($row['waybill_info'] as $waybill_info_key => $waybill_info) {
				foreach ($waybill_info['branch'] as $branch_key=> $branch) {
					foreach($branch['waybill_address'] as $address_key =>$address){
						//$res['shop_name'] = $row['shop_name'];
						$res['cp_code'] = $waybill_info['cp_code'];
						$res['branch_name'] = $branch['branch_name'];
						$res['province'] = $address['province'];
						$res['city'] = $address['city'];
						$res['district'] = $address['area'];
						$res['address'] = $address['address_detail'];
						
						$return[] = $res;
					}	
				}
			}
			break;
		}
		$this->ajaxReturn($return) ;
	}
	
	//判断新老手模式
	function checkNewcomer(){
		$is_new=0;
		try{
			$sql="SELECT trade_id FROM sales_trade WHERE trade_status>=95 LIMIT 1";
			$trade=M('sales_trade')->query($sql);
			if(empty($trade)){
				$is_new=1;
			}
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
		}
		return $is_new;
	}
	//获取订单图表数据
	function getChartData($type=1,$shop=array()){
			$chart=array();
			try{
				$where=array();
				$user_id=get_operator_id();
				$where_sds='';
				$where_sr='';
				if($user_id>1){
					 $where = array();
            		D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
//             		D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',2);
					set_search_form_value($where_sds, 'shop_id', $where['shop_id'],  '', 2,' AND ');
				}
				$goods_num=array();
				//从单品销售统计表中获取前6天的静态销售量
				$sql="SELECT sales_date AS date,SUM(num) AS num FROM stat_daily_sales_spec_shop WHERE sales_date>='".date('Y-m-d',strtotime('-30 days'))."'".$where_sds." GROUP BY sales_date ";
				$goods_num['consign']=M('stat_daily_sales_spec_shop')->query($sql);
				//获取退货入库量
				$sql="SELECT SUM(num) AS return_num ,left(sil.created,10) AS date FROM `stockin_order_detail` sod 
						LEFT JOIN stockin_order so ON so.stockin_id = sod.stockin_id 
						LEFT JOIN stock_inout_log sil ON sil.order_id=sod.stockin_id 
						WHERE so.status=80 AND so.src_order_type=3 AND operate_type=17 AND sil.order_type=1 AND sil.created>='".date('Y-m-d',strtotime('-30 days'))."'
						GROUP BY left(sil.created,10)";
				$goods_num['return']=M('sales_trade_order')->query($sql);
				//动态获取当天发货数量
// 				$sql="SELECT st.trade_id FROM sales_trade st
// 							LEFT JOIN sales_trade_log stl ON stl.trade_id=st.trade_id
// 							WHERE st.trade_status>=95 ".$where_st." AND stl.type=105 AND stl.created>'".date('Y-m-d')."'";
// 				$consign_id_arr=M('sales_trade')->query($sql);
// 				$consign_id='';
// 				foreach ($consign_id_arr as $k=>$v){
// 					$consign_id.=$v['trade_id'].',';
// 				}
// 				if($consign_id!=''){
// 					$consign_id=substr($consign_id, 0,strlen($consign_id)-1);
// 					$sql="SELECT SUM(sto.actual_num) AS num FROM sales_trade_order sto
// 						WHERE sto.trade_id IN (".$consign_id.")";
// 					$consign_num=M('sales_trade_order')->query($sql);
// 				}else{
// 					$consign_num[0]['num']==0;
// 				}
// 				$consign_num=M('stat_daily_sales_spec_shop')->query($sql);
// 				$goods_num['consign'][]=array(
// 						'date'=>date('Y-m-d'),
// 						'num'=>$consign_num[0]['num']==''?0:$consign_num[0]['num'],
// 				);
				$goods_num['consign']=UtilTool::array2dict($goods_num['consign'],'date','');
				$goods_num['return']=UtilTool::array2dict($goods_num['return'],'date','');
				for($i=30;$i>0;$i--){
					$date=date('Y-m-d',strtotime('-'.$i.' days'));
					$chart[]=array(
							'date'=>$date,
							'num'=>@$goods_num['consign'][$date]['num']?$goods_num['consign'][$date]['num']:0,
							'return_num'=>@$goods_num['return'][$date]['return_num']?$goods_num['return'][$date]['return_num']:0,
					);
				}
			}catch (\PDOException $e){
				\Think\Log::write($this->name.'-'.$e->getMessage());
			}
			return $chart;
	}
	//获取新手任务的完成情况和对应界面的链接
	function getNewcomer(){
		$newcomer=array();
		$work_list=array();
		$work_url=array(
				'platform'=>array(
						'shop'=>9,
						'goods'=>25
				),
				'goods'=>array(
						'class'=>18,
						'brand'=>19,
						'goods_archives'=>20,
				),
				'stock'=>array(
						'logistics'=>10,
						'warehouse'=>11,
						'inventory_management'=>40
				)
		);
		$menu=D('Home/Menu')->getMenu();
		$dict_menu = array();
		foreach ($menu as $m){
			$dict_menu[strval($m['id'])]=$m;
		}
		foreach ($work_url as $key => $pm){
			$work_list[$key]=array();
			foreach ($pm as $k => $v){
				$work_list[$key][$k]=$dict_menu[$v];
			}
		}
		$work_msg=array(
				'shop'=>'新建店铺',
				'goods'=>'下载平台货品',
				'class'=>'新建货品分类',
				'brand'=>'新建货品品牌',
				'goods_archives'=>'新建货品档案',
				'logistics'=>'新建物流',
				'warehouse'=>'新建仓库',
				'inventory_management'=>'初始化库存'
		);
		$status='<i class="fa fa-check" style="color:#0a8ebb;float:right;margin-top:3px;margin-right:20px;" title="已完成"></i>';
		$list=D('Home/Menu')->check_begin($work_msg);
		$work_list_new=array();
		foreach ($work_list as $m=>$u){
			foreach ($u as $k=>$v){
				if($list[$k]){
					$work_list[$m][$k]['msg']=($list[$k]=='true'?$work_msg[$k].$status:$work_msg[$k]);
					$work_list_new[]=$work_list[$m][$k];
				}
			}
		}
		if(IS_POST){
			$this->ajaxReturn($work_list_new);		
		}else{
			return $work_list;
		}
	}
	//重载数据
	function reloadData(){
		$data=array();
		$data['todo']=$this->getTodo();
// 		$data['trade_num']=D('Trade/SalesTradeLog')->getTradeNumByLog();
		$data['chart']=$this->getChartData();
		$this->ajaxReturn($data);
	}
	public function showSystemAlarm(){
		if(IS_POST){
			$data=array();
			try{
				$electronic_sheet_number=$this->getWayBillCount('count');
			}catch(\Exception $e){
				\Think\Log::write($this->name.'-'.$e->getMessage());
				$data['electronic_sheet_number']=0;
			}
			$data['electronic_sheet_number']=$electronic_sheet_number;
			try{
				$sid = get_sid();
				$SmsManager = ManagerFactory::getManager ("Sms");
				$sms_count = $SmsManager->manualGetBalance ($sid);
			}catch(\Exception $e){
				\Think\Log::write($this->name.'-'.$e->getMessage());
				$sms_count['info']['sms_num'] = 0;
			}
			$sms_count = $sms_count['status']?0:$sms_count['info']['sms_num'];
			$data['sms_number']=$sms_count;
			$alarm_data=array();
			$alarm_data=$this->getAlarmData($data);
			$this->ajaxReturn($alarm_data['info']);
		}else{
			$id_list = array(
					'id_datagrid' => 'alarm_datagrid',
					'toolbar'=> 'alarm_toolbar',
			);
			$datagrid=array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
							'title'=>'',
							'toolbar' => "#{$id_list['toolbar']}",
							'url'   => U('Home/Index/showSystemAlarm'),
							'fitColumns'=>false,
							'rownumbers'=>true,
							'pagination'=>false,
					),
					'fields' => get_field('Home/Index','system_alarm')
					);
			$params = array(
					'datagrid' => array('id' => $id_list['id_datagrid'])
			);
			$this->assign('params', json_encode($params));
			$this->assign('id_list', $id_list);
			$this->assign('datagrid',$datagrid);
			$this->display('system_alart');
		}
	}
	//获取提示信息
	function getAlarmData($data=''){
		$alarm=array();
		$alarm['status']=0;
		$alarm['info']=array();
		$alarm['type'] = array('sms_num'=>0,'waybill'=>0);
		try{
			$arr_key=array('sms_num_alarm','sms_num_alarm_num','waybill_num_alarm','waybill_num_alarm_num','alarm_not_prompt_today');
			$arr_val=array(0,0,0,0,0);
			$cfg=get_config_value($arr_key,$arr_val);
			$where = array('platform_id' => 1, 'auth_state'=> 1);
			$shop_exist = M('cfg_shop')->where($where)->count();
			if($cfg['sms_num_alarm']==1&&($cfg['sms_num_alarm_num']>$data['sms_number']||$data['sms_number']==0)){
				$alarm['status']=1;
				$alarm['info'][]['msg']='短信余额不足'.$cfg['sms_num_alarm_num'].'条，请及时充值！';
				$alarm['type']['sms_num'] = 1;
			}
			if($cfg['waybill_num_alarm']==1&&($cfg['waybill_num_alarm_num']>$data['electronic_sheet_number']||$data['electronic_sheet_number']==0)&&$shop_exist>0){
				$alarm['status']=1;
				$alarm['info'][]['msg']='电子面单余额不足'.$cfg['waybill_num_alarm_num'].'条，请及时充值！';
				$alarm['type']['waybill'] = 1;
			}
			$alarm['alarm_not_prompt_today']=$cfg['alarm_not_prompt_today'];
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
		}
		return $alarm;
	}
	//当天不再提示
	function notAlarmToday(){
		$cfg=array(
				'key'=>'alarm_not_prompt_today',
				'value'=>1
		);
		try{
			D('Setting/System')->updateSystemSetting($cfg);
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-'.$e->getMessage());
		}
	}
	function getBrowserAndResolution($data){
		D('Statistics/StatUse')->statUse(MODULE_NAME,CONTROLLER_NAME,ACTION_NAME,$data,get_operator_id());
	}
	function showOrderBalance(){
		$id_list = array('datagrid'=>'order_balance_datagrid','form'=>'order_balance_form','toolbar'=>'order_balance_toolbar');
		$datagrid = array(
			'id'=>$id_list['datagrid'],
			'class'=>'easyui-datagrid',
			'options'=> array(
					'title'=>'',
					'toolbar' => "#{$id_list['toolbar']}",
					'url'   => U('Home/Index/getOrderBalance'),
					'fitColumns'=>false,
					'rownumbers'=>true,
					'pagination'=>true,
			),
			'fields' => get_field('Home/Index','order_balance'),
		);
		$params = array(
			'datagrid'=>array('id'=>$datagrid['id']),
			'search'=>array('form_id'=>$id_list['form']),
		);
		$current_month_date = date('Y-m-d',strtotime("-30 day"));
		$current_date = date('Y-m-d',time());
		$this->assign('current_date',$current_date);
		$this->assign('current_month_date',$current_month_date);
		$this->assign('params',json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->display('show_balance');
		
	}
	 function getOrderBalance($page = 1,$rows = 20,$search = array(),$sort = 'rec_id',$order = 'desc'){
		 try{
			  $result = D('Setting/System')->getOrderBalance($page,$rows,$search,$sort,$order);	  
		 }catch(\Exception $e){
			 \Think\Log::write('getOrderBalance--'.$e->getMessage());
			 $result = array('total'=>0,'rows'=>array());
		 }
		 $this->ajaxReturn($result);
	 }
}