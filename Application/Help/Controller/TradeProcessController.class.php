<?php

namespace Help\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
class TradeProcessController extends BaseController
{

	public function getTradeProcess()
	{
		try
		{
			$process=array(
				'trade'=>array(
					'down'=>28,
					'original'=>29,
					'manual'=>27,
					'check'=>26,
					'manage'=>30,
					'stock_out'=>32,
					'refund'=>31,
					'print'=>34,
					'advance'=>74,
					'financial'=>81,
					'logistics_sync'=>35,
					'gift'=>57,
					'logistics_m'=>67,
					'warehouse_r'=>69
				)
			);
			$process_menu=$this->getMenu($process);
			$text_arr=array(
					'excel'=>		'原始订单界面可以通过Excel批量导入原始订单。',
					'submit'=>		'递交原始单生成系统单并执行各种策略，如赠品策略、物流匹配、选仓策略等。',
					'check'=>		'订单审核对订单的仓库、物流、货品等进行人工确认之后，用户可执行“订单审核”操作，由系统来检验是否审核通过。',
					'consign'=>		'确认发货成功后订单状态为已出库，并且线上订单会执行物流同步操作。',
			);
			$list=D('Setting/System')->getSystemSetting();
			$auto_download_msg='1，开启自动下载订单';
			$auto_submit_msg='2，开启自动递交原始单';
			$auto_audit_msg='3，开启自动审核';
			$status='&nbsp;&nbsp;&nbsp;<i class="fa fa-check" style="color:#0a8ebb" title="已开启"></i>';
			if($list['order_auto_download']==1)$auto_download_msg.=$status;
			if($list['order_auto_submit']==1)$auto_submit_msg.=$status;
			if($list['auto_check_is_open']==1)$auto_audit_msg.=$status;
			$work_list=array(
				'auto_download'=>array(
					'text'=>$auto_download_msg,
					'href'=>'index.php/Setting/System/showSystemSetting?dialog=system&tab_type=基本设置&config_name=order_auto_download&info=开启自动下载订单'
				),
				'auto_submit'=>array(
					'text'=>$auto_submit_msg,
					'href'=>'index.php/Setting/System/showSystemSetting?dialog=system&tab_type=基本设置&config_name=order_auto_submit&info=开启自动递交原始单'
				),
				'auto_audit'=>array(
					'text'=>$auto_audit_msg,
					'href'=>'index.php/Setting/System/showSystemSetting?dialog=system&tab_type=订单设置&config_name=auto_check_is_open&info=开启自动审核',
				)
			);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('text',json_encode($text_arr));
		$this->assign('work_list',$work_list);
		$this->assign('trade',$process_menu['trade']);
		$this->display("show");
	}


	/*
	 * 退换流程界面
	 * */
	public function returnProcess()
	{
		try
		{
			$process=array(
					'return'=>array(
					'returnmanagement'=>31,
					'originalrefundtrade'=>132,
					'stockin'=>38,
					'inmanagement'=>39,
					'audit'=>26,
					'print'=>34
					)
			);
			$process_menu=$this->getMenu($process);
			$text_arr=array(
				'from'=>'每隔固定时间，自动从线上抓取，不需要人工操作。',
				'make'=>'在退换管理界面创建系统退货单、换货单或退款单。',
				'original_make'=>'在原始退款单界面递交时会弹出新建窗口，此时可以创建系统退换单或换货单，也可重复创建。',
			);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('text',json_encode($text_arr));
		$this->assign('return',$process_menu['return']);
		$this->display("return_show");
	}

	/*
	 * 初始化流程界面
	 * */
	public function initializeProcess()
	{
		try
		{
			$process=array(
				'initialize'=>array(
					'shop'=>9,
					'system'=>8,
					'goods'=>25,
					'goods_archives'=>20,
					'logistics'=>10,
					'warehouse'=>11,
					'inventory_management'=>40
				)
			);
			$process_menu=$this->getMenu($process);
			$text_arr=array(
					'excel'=>		'原始订单界面可以通过Excel批量导入原始订单。',
					'submit'=>		'递交原始单生成系统单并执行各种策略，如赠品策略、物流匹配、选仓策略等。',
					'check'=>		'订单审核对订单的仓库、物流、货品等进行人工确认之后，用户可执行“订单审核”操作，由系统来检验是否审核通过。',
					'consign'=>		'确认发货成功后订单状态为已出库，并且线上订单会执行物流同步操作。',
			);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('initialize',$process_menu['initialize']);
		$this->assign('text',json_encode($text_arr));
		$this->display("initialize_show");
	}


	public function showInitializeList(){
		$this->display("initialize_list");
	}
	/*
	 * 初始化流程界面之平台货品下载
	 * */
	public function platformProcess()
	{
		try
		{
			$process=array(
				'platform'=>array(
					'shop'=>9,
					'system'=>8,
					'goods'=>25
				)
			);
			$process_menu=$this->getMenu($process);
			$process_menu['platform']['system']['href']='index.php/Setting/System/showSystemSetting?dialog=system&tab_type=基本设置&config_name=goods_auto_download&info=自动下载淘宝货品';
			$work_msg=array(
					'shop'=>'新建店铺',
					'goods'=>'下载平台货品'
			);
			$work_list=$this->getWordList($process_menu,'platform',$work_msg);
			$text_arr=array(
					'down_goods'=>	'系统支持淘宝货品自动下载，其他平台可以手动下载'
			);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('platform',$process_menu['platform']);
		$this->assign('work_list',$work_list);
		$this->assign('text',json_encode($text_arr));
		$this->display("platform_show");
	}

	/*
	 * 初始化流程界面之初始化系统货品
	 * */
	public function goodsProcess()
	{
		try
		{
			$process=array(
				'goods'=>array(
					'class'=>18,
					'brand'=>19,
					'goods_archives'=>20,
					'goods'=>25
				)
			);
			$process_menu=$this->getMenu($process);
			$text_arr=array(
					'in_from_excel'=>	'可以在excel中完善货品的品牌和分类等信息。',
					'out_to_excel'=>	'推荐使用方式',
					'system_goods'=>	'在这里自动初始化的系统货品不包含品牌和分类信息。',
			);
			$work_msg=array(
				'class'=>'新建货品分类',
				'brand'=>'新建货品品牌',
				'goods'=>'下载平台货品',
				'goods_archives'=>'新建货品档案'
			);
			$work_list=$this->getWordList($process_menu,'goods',$work_msg);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('goods',$process_menu['goods']);
		$this->assign('text',json_encode($text_arr));
		$this->assign('work_list',$work_list);
		$this->display("goods_show");
	}

	/*
	 * 初始化流程界面之初始化物流和库存
	 * */
	public function stockProcess()
	{
		try
		{
			$process=array(
				'stock'=>array(
					'shop'=>9,
					'logistics'=>10,
					'warehouse'=>11,
					'goods'=>25,
					'inventory_management'=>40
				)
			);
			$process_menu=$this->getMenu($process);
			$work_msg=array(
					'logistics'=>'新建物流',
					'warehouse'=>'新建仓库',
					'goods'=>'下载平台货品',
					'inventory_management'=>'初始化库存'
			);
			$work_list=$this->getWordList($process_menu,'stock',$work_msg);
			$text_arr=array(
					'excel_in_stock'=>	'下载模板，完善信息（货品库存数量可以在平台货品导出的excel中查看），再导入即可完成',
					'stock_stock'=>	'此处只适用于单一仓库的商家，如果是多仓库的话，库存只会同步到第一个仓库中'
			);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('stock',$process_menu['stock']);
		$this->assign('text',json_encode($text_arr));
		$this->assign('work_list',$work_list);
		$this->display("stock_show");
	}


	/*
	 * 盘点流程界面
	 * */
	public function checkProcess()
	{
		try
		{
			$process=array(
				'check'=>array(
						'stock'=>40,
						'check'=>79,
						'check_management'=>80
				)
			);
			$process_menu=$this->getMenu($process);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('check',$process_menu['check']);
		$this->display("check_show");
	}

	/*
	 * 退换预入库流程界面
	 * */
	public function returnPreProcess()
	{
		try
		{
			$process=array(
				'returnPre'=>array(
						'stock_in'=>38,
						'stock_management'=>39,
						'refund_manage'=>31
				)
			);
			$text_arr=array(
					'make'=>	'新建“其他入库”的入库单',
					'relation'=>	'1.其他入库单必须是已完成状态的才能在退换单里面关联 2.其他入库单里面的货品必须包含在退换单的退货货品信息里面,不得含有其他的货品信息',
					'add_or_edit'=>	'只有新建或编辑待审核状态的退换单时才能够进行关联退换预入库',
					'examine'=>	'点击提交即完成预入库操作'
			);
			$process_menu=$this->getMenu($process);
		}catch(BusinessLogicException $e)
		{
			$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
		}
		$this->assign('text',json_encode($text_arr));
		$this->assign('returnPre',$process_menu['returnPre']);
		$this->display("returnPre_show");
	}

	//策略设置流程
	public function ruleProcess(){
		try{
			$process=array(
					'rule'=>array(
							'warehouse_rule'=>69,
							'goods'=>20,
							'spec'=>21,
							'logis_m'=>67,
							'logis_f'=>61,
							'remark'=>70,
							'gift'=>57,
					)
			);
			$work_msg=array(
					'warehouse_rule'=>'选仓策略',
					'logis_m'=>'物流匹配',
					'logis_f'=>'物流资费',
					'remark'=>'备注提取',
					'gift'=>'赠品策略'
			);
			$process_menu=$this->getMenu($process);
			$work_list=$this->getWordList($process_menu,'rule',$work_msg);
			$process_menu=$this->getMenu($process);
		}catch(BusinessLogicException $e){
			$process_menu=array('rule'=>array());
		}
		$faq_url=C('faq_url');
		$this->assign('faq_url',$faq_url);
		$this->assign('rule',$process_menu['rule']);
		$this->assign('work_list',$work_list);
		$this->display("rule_show");
	}
	
	//高级设置
	public function advancedSetting(){
		$role=D('Setting/Employee')->getRole(get_operator_id());
		$setting =D('Setting/System')->getSystemSetting();
		$this->assign("setting", json_encode($setting));
		$this->assign("role", $role);
		$this->display("advanced_setting");
	}

	//遍历出流程图中使用的菜单项
	public function getMenu($process){
		if(!$process)return array();
		$menu=D('Home/Menu')->getMenu();
		$dict_menu = array();
		foreach ($menu as $m)
		{
			$dict_menu[strval($m['id'])]=$m;
		}
		foreach ($process as $key => $pm)
		{
			$process_menu[$key]=array();
			foreach ($pm as $k => $v)
			{
				$process_menu[$key][$k]=$dict_menu[$v];
			}
		}
		return $process_menu;
	}

	public function getWordList($process_menu,$model,$work_msg){
		$work_list=array();
		$status='&nbsp;&nbsp;&nbsp;<i class="fa fa-check" style="color:#0a8ebb" title="已完成"></i>';
		$list=D('Home/Menu')->check_begin($work_msg);
		foreach($list as $key=>$row){
			if($row=='true')$work_msg[$key]=$work_msg[$key].$status;
			$work_list[$key]=array(
					'msg'=>$work_msg[$key],
					'text'=>$process_menu[$model][$key]['text'],
					'href'=>$process_menu[$model][$key]['href']
			);
		}
		return $work_list?$work_list:array();
	}

}