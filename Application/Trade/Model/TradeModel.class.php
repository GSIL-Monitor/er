<?php
namespace Trade\Model;
use Think\Model;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception\BusinessLogicException;
class TradeModel extends Model{
	protected $tableName = 'sales_trade';
	protected $pk        = 'trade_id';
	
	/**
	 * 订单验证
	 */
	public function getRules()
	{
		
		// 			array(验证字段1,验证规则,错误提示,[验证条件(0),附加规则,验证时间(3)])
		//          验证条件
		// 			self::EXIST S_VALIDAT E 或者0 存在字段就验证（默认）
		// 			self::MUST _VALIDAT E 或者1 必须验证
		// 			self::VALUE_VALIDAT E或者2 值不为空的时候验证
		//          验证时间
		// 			self::MODEL_INSERT或者1新增数据时候验证
		// 			self::MODEL_UPDAT E或者2编辑数据时候验证
		// 			self::MODEL_BOT H或者3全部情况下验证（默认）
		$rules=array(
				array('shop_id','number','无效店铺！',1), //默认情况下用正则进行验证
				array('trade_type',C('trade_type'),'订单类型未找到！',0,'in'), //
				array('delivery_term',C('delivery_term'),'发货条件未找到！',0,'in'),
				array('invoice_type',C('invoice_type'),'发票类型未找到！',1,'in'),
				array('pay_method',C('pay_method'),'支付方式未找到！',1,'in',1),
				array('trade_time',check_regex('time'),'请填写正确的时间格式！',1,'regex',1),
				array('receiver_mobile',check_regex('mobile'),'请填写正确的手机号码！',2,'regex'),
				array('receiver_telno',check_regex('mobile_tel'),'请填写正确的固话！',2,'regex'),
				array('receiver_zip',check_regex('zip'),'请填写正确的邮政编码！',2,'regex'),
				array('receiver_province','number','省市区不能为空！',0),
				array('receiver_city','number','省市区不能为空！',0),
				array('receiver_district','number','省市区不能为空！',0),
				array('receiver_name','require','收货人姓名不能为空！',0,'regex',2),
				array('receiver_address','require','收货地址不能为空！',0,'regex',2),
				array('goods_type_count','1,1000','至少添加一个订单货品！',0,'length'),
				
				array('goods_amount',check_regex('double'),'货款格式不正确！',0,'regex'),
				array('post_amount',check_regex('double'),'邮费格式不正确！',0,'regex'),
				array('discount',check_regex('double'),'优惠格式不正确！',0,'regex'),
				array('receivable_amount',check_regex('double'),'应收格式不正确！',0,'regex'),
				array('receivable',check_regex('double'),'实收格式不正确！',0,'regex'),
				array('paid',check_regex('double'),'本次收款格式不正确！',0,'regex'),
				array('goods_count',check_regex('double'),'货品数量格式不正确！',0,'regex'),
				array('weight',check_regex('double'),'重量格式不正确！',0,'regex'),
				array('post_cost',check_regex('double'),'预估邮费格式不正确！',0,'regex'),
				//回调--验证
				array('flag_id','checkFlag','无效标记！',0,'callback'),
				array('shop_id','checkShop','无效店铺！',0,'callback'),
				array('logistics_id','checkLogistics','无效物流！',1,'callback'),
				array('warehouse_id','checkWarehouse','无效仓库！',1,'callback'),
				array('src_tids','checkTids','原始单号已存在！',2,'callback',1),
		);
		return $rules;
	}
	protected function checkFlag($flag_id)
	{
		return D('Setting/Flag')->checkFlag($flag_id,1);
	}
	protected function checkShop($shop_id)
	{
		return D('Setting/Shop')->checkShop(intval($shop_id));
	}
	protected function checkWarehouse($warehouse_id)
	{
		return D('Setting/Warehouse')->checkWarehouse(intval($warehouse_id));
	}
	protected function checkLogistics($logistics_id)
	{
		return D('Setting/Logistics')->checkLogistics(intval($logistics_id));
	}
	protected function checkTids($src_tids)
	{
		return !D('Trade/OriginalTrade')->checkTids($src_tids);
	}
	public function validateTrade($trade)
    {
        try {
            if(!$this->validate($this->getRules())->create($trade))
            {
                SE($this->getError());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-validateTrade-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    } 
	/**
	 * 订单相关的数据库的基本操作
	 * */
	public function getSalesTrade($fields,$where = array(),$alias='',$join=array())
	{
		try {
			$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getSalesTrade-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $res;
	}
	public function getSalesTradeList($fields,$where = array(),$alias='',$join=array())
	{
		try {
			$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getSalesTradeList-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	public function getSalesTradeOnLock($fields,$where = array())
	{
		try {
			$res = $this->field($fields)->where($where)->lock(true)->find();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getSalesTradeOnLock-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	public function updateSalesTrade($data,$where)
	{
		try {
			$res = $this->where($where)->save($data);
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-updateSalesTrade-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	/**
	 * 订单操作相关--对话框--datagrid数据
	 * @param string $dialog
	 * @param array $id_list
	 * @return Ambigous <multitype:multitype:string multitype:string boolean  Ambigous <multitype:, object>  , multitype:string multitype:string boolean  Ambigous <multitype:, object, unknown, \Common\Common\Field> >
	 */
	public function getDialogView($dialog,$id_list=null)
	{
		$datagrid=array();
		switch($dialog){
			case 'split':{
				$datagrid['split_main_trade']=array(
						'id'=>'main_trade_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#split_main_trade_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onEndEdit:endSplitTradeEdit,onSelect:getSplitTradeSelect',
						),
						'fields' => D('Setting/UserData')->getDatagridField('TradeCheck','split_main_trade')
				);
				$datagrid['split_new_trade']=array(
						'id'=>'new_trade_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#new_sub_trade_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
						),
						'fields' => get_field('TradeCheck','split_new_trade')
				);
				break;
			}
			case 'edit':{
				$datagrid=array(
						'id'=>$id_list['id_datagrid'],
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar']}",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onEndEdit:endEditTrade,onBeginEdit:beginEditTrade,rowStyler:editTradeRowStyler',
								'singleSelect'=>false,
								'ctrlSelect'=>true,
								//'methods'=>'onEndEdit:tradeCheck.endEditTrade,onBeginEdit:tradeCheck.beginEditTrade',
						),
						'fields' => D('Setting/UserData')->getDatagridField('TradeCheck','edit')
				);
				break;
			}
			case 'exchange':{
				$datagrid['spec']=array(
						'id'=>'exchange_spec',
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
				$datagrid['order']=array(
						'id'=>'exchange_order',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								// 'toolbar' => "#{$id_list['toolbar_order']}",
								'pagination'=>false,
								'fitColumns'=>false,
						),
						'fields' => get_field('TradeCheck','order')
				);
				break;
			}
			case 'passel_split':{
				$datagrid['split_common_order']=array(
						'id'=>'common_passel_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#passel_split_common_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onEndEdit:endSplitTradeEdit,onSelect:getSplitTradeSelect',
						),
						'fields' => get_field('TradeCheck','split_common_order')
				);
				$datagrid['split_new_order']=array(
						'id'=>'new_passel_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#passel_split_new_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onSelect:getSplitTradeSelect',
						),
						'fields' => get_field('TradeCheck','split_new_trade')
				);
				break;
			}
			case 'suite_split':{
				$datagrid=array(
						'id'=>'trade_suite_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar']}",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onEndEdit:endSplitTradeEdit,onSelect:getSplitTradeSelect',
						),
						'fields' => D('Setting/UserData')->getDatagridField('TradeCheck','suite_split')
				);
				break;
			}
			case passel_exchange:{
				$datagrid['spec']=array(
						'id'=>'passel_exchange_spec',
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
				$datagrid['order']=array(
						'id'=>'passel_exchange_order',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#{$id_list['toolbar_order']}",
								'pagination'=>false,
								'fitColumns'=>false,
						),
						'fields' => get_field('TradeCheck','exchange')
				);
				break;
			}
		}
		return $datagrid;
	}
	
	/**
	 * 订单查询（搜索）
	 * @param number $page
	 * @param number $rows
	 * @param array  $search
	 * @param string $sort
	 * @param string $order
	 * @param number|array $trade_status
	 */
	public function queryTrade(&$where_sales_trade,$page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc',$type='check')
	{
		//设置店铺权限
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_arr = $this->searchForm($where_sales_trade,$search);
		$where_sales_trade_order=$where_arr['where_sales_trade_order'];
		$where_goods_goods=$where_arr['where_goods_goods'];
		$where_left_join_goods_class=$where_arr['where_left_join_goods_class'];
		$where_stockout_other=$where_arr['where_stockout_other'];
		$where_sales_trade_order_except=$where_arr['where_sales_trade_order_except'];
		$page=intval($page);
		$rows=intval($rows);
		$limit=($page - 1) * $rows . "," . $rows;//分页
		$arr_sort=array('shop_name'=>'shop_id','flag_name'=>'flag_id','warehouse_name'=>'warehouse_id','handle_days'=>'trade_id');//用于映射排序
		$in_order = 'st_1.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//排序
		$in_order = addslashes($in_order);
		$out_order = 'st_2.'.(empty($arr_sort[$sort])?$sort:$arr_sort[$sort]).' '.$order;//外层排序
		$out_order = addslashes($out_order);
		$from_table = $type=='history'?'sales_trade_history':'sales_trade';
		$sub_table = $type == 'history'?'sales_trade_order_history':'sales_trade_order';
		$sql_sel_limit="SELECT st_1.trade_id FROM $from_table st_1 ";
		$sql_total="SELECT COUNT(DISTINCT st_1.trade_id) AS total FROM $from_table st_1 ";
		//目前针对订单审核搜索
		$sql_except_order=" SELECT exp_st.trade_id FROM $from_table exp_st LEFT JOIN $sub_table exp_sto ON exp_st.trade_id = exp_sto.trade_id WHERE exp_st.trade_status=30 ";
		$flag=false;
		$sql_where='';
		$sql_limit=' ORDER BY '.$in_order.' LIMIT '.$limit;
// 		if(!empty($where_sales_trade_order))
// 		{
// 			$sql_where.=' LEFT JOIN sales_trade_order sto_1 ON sto_1.trade_id=st_1.trade_id ';
// 			$sql_limit=' GROUP BY st_1.trade_id ORDER BY '.$order.' LIMIT '.$limit;
// 		}
		if((!empty($where_goods_goods))||(!empty($where_sales_trade_order)))
		{
			$sql_where.=' LEFT JOIN '. $sub_table .' sto_1 ON sto_1.trade_id=st_1.trade_id LEFT JOIN goods_goods gg_1 ON gg_1.goods_id=sto_1.goods_id ';
			$sql_limit=' GROUP BY st_1.trade_id ORDER BY '.$in_order.' LIMIT '.$limit;
		}		
		if(!empty($where_stockout_other)){
			$sql_where.=' LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_1.trade_id';
			$sql_limit=' GROUP BY st_1.trade_id ORDER BY '.$in_order.' LIMIT '.$limit;
            if(!empty($search['unique_code'])){
                $sql_where.=' LEFT JOIN stalls_less_goods_detail slgd on so_1.src_order_id = slgd.trade_id';
            }
			if(!empty($search['multi_logistics_no'])){
				$sql_where.=' LEFT JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
			}else{
				if(isset($search['multi_logistics'])&&$search['multi_logistics']==1){
					$sql_where.=' INNER JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
				}
			}
		}else{
			if(isset($search['multi_logistics'])&&$search['multi_logistics']==1){
				$sql_where.=' LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_1.trade_id INNER JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
				$sql_limit=' GROUP BY st_1.trade_id ORDER BY '.$in_order.' LIMIT '.$limit;
			}
		}
		//不包含类左连接（仅审核页面）
		if(!empty($where_sales_trade_order_except)){
			$sql_except_order .= $where_sales_trade_order_except;
			$sql_where.=' LEFT JOIN('.$sql_except_order.' GROUP BY exp_st.trade_id) exp_2 ON exp_2.trade_id=st_1.trade_id ';
		}

		$sql_where .= $where_left_join_goods_class;
		connect_where_str($sql_where, $where_sales_trade, $flag);
		connect_where_str($sql_where, $where_sales_trade_order, $flag);
		connect_where_str($sql_where, $where_goods_goods, $flag);
		connect_where_str($sql_where, $where_stockout_other, $flag);
		$sql_sel_limit.=$sql_where;
		$sql_total.=$sql_where;
		$sql_sel_limit.=$sql_limit;
		$sql_fields_str='';
		$sql_left_join_str='';
		$cfg_show_telno=get_config_value('show_number_to_star',1);
		$point_number = get_config_value('point_number',0);
		$goods_count = "CAST(st_2.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
		$raw_goods_count = "CAST(st_2.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";
		switch ($type){
			case 'check':
				$sql_fields_str="SELECT  st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, sh.shop_name AS shop_id ,st_2.warehouse_id, sw.name AS warehouse_name, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days,st_2.delivery_term, st_2.freeze_reason, st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, single_spec_no, ".$raw_goods_count.", st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address,IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, st_2.trade_mask, IF(st_2.flag_id=0,'',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created, st_2.stockout_no FROM sales_trade st_2";
				$sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id AND is_builtin=0 ';
				break;
			case 'manage':
				$sql_fields_str="SELECT so_1.consign_time,st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, st_2.shop_id ,sh.shop_name,st_2.warehouse_id, sw.name AS warehouse_name,st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type,TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days,st_2.delivery_term, st_2.freeze_reason, cor.title AS freeze_info,st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick,st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.",st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name,st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address,IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area,st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify,clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark,st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created FROM sales_trade st_2";
				$sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id AND is_builtin=0 LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_2.trade_id';
				break;
			case 'history':
				$sql_fields_str="SELECT st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, st_2.shop_id ,sh.shop_name,st_2.warehouse_id, sw.name AS warehouse_name, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days, st_2.delivery_term, st_2.freeze_reason, cor.title AS freeze_info,st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.", st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address, IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'无',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created FROM sales_trade_history st_2";
				$sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason';
				break;

		}
		$sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') st_3 ON st_2.trade_id=st_3.trade_id '.$sql_left_join_str.' ORDER BY '.$out_order;
		//echo $sql;die;
		$data=array();
		try {
			$flag=$this->query('SELECT flag_id,flag_name,bg_color,font_color,font_name FROM cfg_flags WHERE flag_class=1 AND is_builtin=1');
			$flag=UtilTool::array2dict($flag,'flag_id','');
			$total=$this->query($sql_total);
			$total=intval($total[0]['total']);
			$list=$total?$this->query($sql):array();
			$is_check_weight=get_config_value('order_mark_color_by_weight',0);
			$weight_range = get_config_value('order_mark_color_weight_range',0);
			foreach ($list as $k1 => $v1) {
				if($list[$k1]['platform_id']==1){
					$buyer_nick = urlencode($list[$k1]['buyer_nick']);
					$list[$k1]['buyer_nick'] = "<a target='_blank' href='http://www.taobao.com/webww/ww.php?ver=3&touid=".$buyer_nick."&siteid=cntaobao&status=2&charset=utf-8'><img border='0' src='http://amos.alicdn.com/realonline.aw?v=2&uid=".$buyer_nick."&site=cntaobao&s=2&charset=utf-8' />".$list[$k1]['buyer_nick']."</a>";
				}
				//标旗图片展示
				if($list[$k1]['remark_flag'] == 0){
					$list[$k1]['remark_flag'] = "";
				}else{
					$list[$k1]['remark_flag'] = "<img src='./Public/Image/Icons/op_memo_".$list[$k1]['remark_flag'].".png' >";
				}
				$style = "display:block;float:left;border-radius:50px;width:20px;height:20px;line-height:20px;text-align:center;font-size:14px;";
				$style_two="display:block;float:left;border-radius:20px;width:40px;height:20px;line-height:20px;text-align:center;font-size:14px;";//两个字的样式
				if ($list[$k1]['refund_status']>0) {//退款订单
					$list[$k1]['flag'].="<span title='退款' style='background-color:".$flag[8]['bg_color'].";color:".$flag[8]['font_color'].";font_family:".$flag[8]['font_name'].";".$style."'>退</span>";
			    }
			    if ($list[$k1]['freeze_reason']!=0) {//冻结订单
					$list[$k1]['flag'].="<span title='冻结' style='background-color:".$flag[1]['bg_color'].";color:".$flag[1]['font_color'].";font_family:".$flag[1]['font_name'].";".$style."'>冻</span>";
			    }
			    if ($list[$k1]['bad_reason']>0) {//异常订单
					$list[$k1]['flag'].="<span title='异常' style='background-color:".$flag[30]['bg_color'].";color:".$flag[30]['font_color'].";font_family:".$flag[30]['font_name'].";".$style."'>异</span>";
			    }
				if ($list[$k1]['revert_reason']>0) {//驳回订单
					$list[$k1]['flag'].="<span title='驳回订单' style='background-color:".$flag[5]['bg_color'].";color:".$flag[5]['font_color'].";font_family:".$flag[5]['font_name'].";".$style."'>驳</span>";
			    }
		    	if($list[$k1]['split_from_trade_id']!=0){//拆分的单子
		    	 	$list[$k1]['flag'].="<span title='拆分订单' style='background-color:".$flag[3]['bg_color'].";color:".$flag[3]['font_color'].";font_family:".$flag[3]['font_name'].";".$style."'>拆</span>";
		    	 }
			    if ($list[$k1]['src_tids']=='') {//手工 合并订单
			    	// 根据订单日志判断是否有自动合并订单或用户合并订单
			    	$log = M("SalesTradeLog")->where("type=38 and trade_id=".$list[$k1]['id'])->select();
			    	if (!empty($log)) {
			    		$list[$k1]['flag'].="<span title='合并订单' style='background-color:".$flag[4]['bg_color'].";color:".$flag[4]['font_color'].";font_family:".$flag[4]['font_name'].";".$style."'>合</span>";
			        }
			    }elseif (strpos($list[$k1]['src_tids'],',')) {//线上或导入 合并订单 excel导入
					$list[$k1]['flag'].="<span title='合并订单' style='background-color:".$flag[4]['bg_color'].";color:".$flag[4]['font_color'].";font_family:".$flag[4]['font_name'].";".$style."'>合</span>";
			    }
			    if ($list[$k1]['trade_from']==2) {//手工订单
					$list[$k1]['flag'].="<span title='手工建单' style='background-color:".$flag[7]['bg_color'].";color:".$flag[7]['font_color'].";font_family:".$flag[7]['font_name'].";".$style."'>手</span>";
				}
				if ($list[$k1]['trade_from']==4) {//现款销售
					$list[$k1]['flag'].="<span title='现款销售' style='background-color:".$flag[43]['bg_color'].";color:".$flag[43]['font_color'].";font_family:".$flag[43]['font_name'].";".$style."'>现</span>";
			    }
			    if ($list[$k1]['delivery_term']==2) {//货到付款
					$list[$k1]['flag'].="<span title='货到付款' style='background-color:".$flag[6]['bg_color'].";color:".$flag[6]['font_color'].";font_family:".$flag[6]['font_name'].";".$style_two."'>到付</span>";
			    }
				if($list[$k1]['trade_type']==3){//换货订单
					$list[$k1]['flag'].="<span title='换货销售单' style='background-color:".$flag[19]['bg_color'].";color:".$flag[19]['font_color'].";font_family:".$flag[19]['font_name'].";".$style."'>换</span>";
				}
				if($list[$k1]['cancel_reason']>0){//取消订单
					$list[$k1]['flag'].="<span title='取消' style='background-color:".$flag[2]['bg_color'].";color:".$flag[2]['font_color'].";font_family:".$flag[2]['font_name'].";".$style."'>消</span>";
			    }
			    // 判断配置是否开启，估重是否大于配置值
			    if ($is_check_weight==1) {
			    	 if($list[$k1]['weight']>$weight_range){//估重是否大于
						$list[$k1]['flag'].="<span title='超重' style='background-color:".$flag[41]['bg_color'].";color:".$flag[41]['font_color'].";font_family:".$flag[41]['font_name'].";".$style."'>重</span>";
			    	}
			    }
			    if($list[$k1]['flag_id']==42){//售后订单
					$list[$k1]['flag'].="<span title='售后' style='background-color:".$flag[42]['bg_color'].";color:".$flag[42]['font_color'].";font_family:".$flag[42]['font_name'].";".$style_two."'>售后</span>";
			    }			   
			}
			$data=array('total'=>$total,'rows'=>$list);
		} catch (\PDOException $e) {
			\Think\Log::write('search_trades_sql:'.$sql);
			\Think\Log::write('search_trades:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	public function searchForm(&$where_sales_trade,$search){
		//搜索表单-数据处理
		$where_sales_trade_order='';
		$where_sales_trade_order_except='';
		$where_goods_goods='';
		$where_left_join_goods_class='';
		$where_stockout_other='';
		foreach ($search as $k=>$v){
			if($v==='') continue;
			switch ($k)
			{   //set_search_form_value->Common/Common/function.php
				case 'trade_no'://sales_trade
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
					break;
				case 'warehouse_id':
					set_search_form_value($where_sales_trade,$k,$v,'st_1',2,' AND ');
					break;
				case 'stockout_no':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
					break;
				case 'logistics_no':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
					break;
				case 'multi_logistics_no':
					set_search_form_value($where_stockout_other, 'logistics_no', $v,'sml', 1,' AND ');
					break;
				case 'multi_logistics':
					break;
				case 'src_tids'://手工建单原始单号在sales_trade是空->从子订单中搜索
					set_search_form_value($where_sales_trade_order, 'src_tid', $v,'sto_1', 6,' AND ');
					break;
				case 'buyer_nick':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ');
					break;
				case 'shop_id':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'receiver_mobile':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 1,' AND ( ');
					set_search_form_value($where_sales_trade, 'receiver_telno', $v,'st_1',1,' OR ',' ) ');
					break;
				case 'cs_remark':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 6,' AND ');
					break;
				case 'buyer_message':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 10,' AND ');
					break;
				case 'receiver_name':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 6,' AND ');
					break;
				case 'logistics_id':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'trade_type':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'trade_from':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'delivery_term':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'revert_reason':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'flag_id':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'refund_status':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'remark_flag':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'freeze_reason':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 8,' AND ');
					break;
				case 'start_time':
					set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 3,' AND ',' >= ');
					break;
				case 'end_time':
					set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 3,' AND ',' <= ');
					break;
				case 'consign_start_time'://按照发货时间查询
					set_search_form_value($where_stockout_other, 'consign_time', $v,'so_1', 3,' AND ',' >= ');
					break;
				case 'consign_end_time':
					set_search_form_value($where_stockout_other, 'consign_time', $v,'so_1', 3,' AND ',' <= ');
					break;
				case 'trade_start_time'://订单审核、订单管理详细到秒查询时间
					set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 4,' AND ',' >= ');
					break;
				case 'trade_end_time'://订单审核、订单管理详细到秒查询时间
					set_search_form_value($where_sales_trade, 'trade_time', $v,'st_1', 4,' AND ',' <= ');
					break;
				case 'pay_start_time'://订单审核详细到秒按支付时间查询
					set_search_form_value($where_sales_trade, 'pay_time', $v, 'st_1', 4, ' AND ', ' >= ');
					break;
				case 'pay_end_time'://订单审核详细到秒按支付时间查询
					set_search_form_value($where_sales_trade, 'pay_time', $v, 'st_1', 4, ' AND ', ' <= ');
					break;
				case 'trade_consign_start_time'://订单审核、订单管理详细到秒查询发货时间
					set_search_form_value($where_stockout_other, 'consign_time', $v,'so_1', 4,' AND ',' >= ');
					break;
				case 'trade_consign_end_time'://订单审核、订单管理详细到秒查询发货时间
					set_search_form_value($where_stockout_other, 'consign_time', $v,'so_1', 4,' AND ',' <= ');
					break;
				case 'spec_no'://sales_trade_order
					set_search_form_value($where_sales_trade_order, $k, $v,'sto_1', 1,' AND ');
					break;
				case 'goods_no':
					set_search_form_value($where_sales_trade_order, $k, $v,'sto_1', 1,' AND ');
					break;
				case 'goods_name_include':
					set_search_form_value($where_sales_trade_order, 'goods_name', $v,'sto_1', 10,' AND ');
					break;
				case 'goods_name_exclude':
					$v = trim_all($v,1);
					if(empty($v)&&($v!==0)&&($v!=='0')){ break; }//为了判断$where_sales_trade是否需要
					set_search_form_value($where_sales_trade_order_except, 'goods_name', $v,'exp_sto', 10,' AND ');
					$where_sales_trade .= " AND exp_2.trade_id is NULL ";
					break;
				case 'brand_id'://goods_goods
					set_search_form_value($where_goods_goods, $k, $v, 'gg_1',2,' AND ');
					break;
				case 'class_id':
					$where_left_join_goods_class = set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 7, ' AND ');
					break;
				case 'small_number':
					set_search_form_value($where_sales_trade, 'goods_count', $v, 'st_1',9,' AND ',' >= ');
					break;
				case 'big_number':
					set_search_form_value($where_sales_trade, 'goods_count', $v, 'st_1',9,' AND ',' <= ');
					break;
				case 'one_goods_num':
					if(!check_regex('number',$v)){ break; }
					if($v==0){
						$where_sales_trade.=" AND st_1.goods_count <> 1 ";
					}else if($v==1){
						set_search_form_value($where_sales_trade, 'goods_count', $v, 'st_1',1,' AND ');
					}
					break;
				case 'small_type':
					set_search_form_value($where_sales_trade, 'goods_type_count', $v, 'st_1',9,' AND ',' >= ');
					break;
				case 'big_type':
					set_search_form_value($where_sales_trade, 'goods_type_count', $v, 'st_1',9,' AND ',' <= ');
					break;
				case 'small_weight'://预估重量区间小
					//set_search_form_value($where_sales_trade, 'weight', $v, 'st_1',9,' AND ',' >= ');
					$where_sales_trade.=' AND st_1.weight >= '.addslashes($v).' ';
					break;
				case 'big_weight'://预估重量区间大
					//set_search_form_value($where_sales_trade, 'weight', $v, 'st_1',9,' AND ',' <= ');
					$where_sales_trade.=' AND st_1.weight <= '.addslashes($v).' ';
					break;
				case 'small_paid'://实付金额区间小
					//set_search_form_value($where_sales_trade, 'paid', $v, 'st_1',9,' AND ',' >= ');
					$where_sales_trade.=' AND st_1.paid >= '.addslashes($v).' ';
					break;
				case 'big_paid'://实付金额区间大
					//set_search_form_value($where_sales_trade, 'paid', $v, 'st_1',9,' AND ',' <= ');
					$where_sales_trade.=' AND st_1.paid <= '.addslashes($v).' ';
					break;
				case 'trade_status':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
					break;
				case 'bad_reason_detail':
					set_search_form_value($where_sales_trade, 'bad_reason', $v,'st_1', 2,' AND ');
					break;
				case 'revert':
					set_search_form_value($where_sales_trade, 'revert_reason', $v,'st_1', 8,' AND ');
					break;
				case 'bad_reason':
					set_search_form_value($where_sales_trade, $k, $v,'st_1', 8,' AND ');
					break;
				case 'merge':
					set_search_form_value($where_sales_trade, 'src_tids', ',','st_1', 10,' AND ');
					break;
				case 'split':
					set_search_form_value($where_sales_trade, 'split_from_trade_id', $v,'st_1', 8,' AND ');
					break;
				// case 'auto_merge':
				// 	set_search_form_value($where_sales_trade, 'flag_id', '20','st_1', 2,' AND ');
				// 	break;
				case 'refund':
					set_search_form_value($where_sales_trade, 'refund_status', $v,'st_1', 8,' AND ');
					break;
				case 'return':
					set_search_form_value($where_sales_trade, 'trade_type', '3','st_1', 2,' AND ');
					break;
				case 'cs':
					$where_sales_trade.=" AND st_1.cs_remark <> '' ";
					break;
				case 'no_cs':
					$where_sales_trade.=" AND st_1.cs_remark = '' ";
					break;
				case 'client':
					$where_sales_trade.=" AND st_1.buyer_message <> '' ";
					break;
				case 'no_client':
					$where_sales_trade.=" AND st_1.buyer_message = '' ";
					break;
				case 'one_day':
					set_search_form_value($where_sales_trade, 'trade_time', date('Y-m-d'),'st_1', 3,' AND ',' >= ');
					break;
				case 'tow_day':
					set_search_form_value($where_sales_trade, 'trade_time', date('Y-m-d',strtotime('-1 day')),'st_1', 3,' AND ',' >= ');
					break;
				case 'one_week':
					set_search_form_value($where_sales_trade, 'trade_time', date('Y-m-d',strtotime('-1 week')),'st_1', 3,' AND ',' >= ');
					break;
				case 'one_month':
					set_search_form_value($where_sales_trade, 'trade_time', date('Y-m-d',strtotime('-1 month')),'st_1', 3,' AND ',' >= ');
					break;
				case 'receiver_province':{//省份 receiver_province
					$v = ','.$v.',';
					if(strpos($v,',0,')!==false){$v = str_replace(',0,','',$v);}
					$v=trim($v,',');
					if($v==0||$v==''){
						 break;
					}
	                set_search_form_value($where_sales_trade, $k, $v, 'st_1',2,' AND ');
	                break;
	            }
	            case 'receiver_city':{//城市 receiver_city
					if($v==0){
						 break;
					}
	                set_search_form_value($where_sales_trade, $k, $v, 'st_1',1,' AND ');
	                break;
	            }
	            case 'receiver_district':{//区县 receiver_district
					if($v==0){
						 break;
					}
	                set_search_form_value($where_sales_trade, $k, $v, 'st_1',1,' AND ');
	                break;
	            }
	            case 'passel_src_tids':
	            	$where_sales_trade_order.=' AND sto_1.src_tid in ('.$v.') ';
	            	break;
                case 'unique_code':
                    set_search_form_value($where_stockout_other, 'unique_code', $v,'slgd', 1,' AND ');
                    break;
                 case 'invoice_type':
                 	set_search_form_value($where_sales_trade, $k, $v,'st_1', 2,' AND ');
                 	break;
				case 'include_goods_type_count':{ //包含货品
					$ret = $this->includeGoodsIds($v);
					set_search_form_value($where_sales_trade,'trade_id',$ret,'st_1',2,' AND ');
					break;
				}
				case 'not_include_goods_type_count':{ //不包含货品
					$ret = $this->includeGoodsIds($v);
					if($ret!=''){
						$where_sales_trade.= " AND st_1.trade_id NOT IN (".$ret.") ";
					}
					break;
				}
			}
		}
		return array(
			'where_sales_trade'=>$where_sales_trade,
			'where_sales_trade_order'=>$where_sales_trade_order,
			'where_sales_trade_order_except'=>$where_sales_trade_order_except,
			'where_goods_goods'=>$where_goods_goods,
			'where_left_join_goods_class'=>$where_left_join_goods_class,
			'where_stockout_other'=>$where_stockout_other,
		);
	}
	public function includeGoodsIds($where){
		try{
			$where = json_decode($where,true);
			$tmpArr = [];
			$conditionMap = ['<','=','>'];
			//$includeRelationMap = ['<','=','>'];
			$includeRelationCount = substr_count($where['include_val'],',')+1;
			$includes = explode(',',$where['include_val']);
			foreach($includes as $v){
				$tmpArr[] = explode('-',$v);
			}
			switch($where['include_relation']){
				case '0':
					if($tmpArr[0][3]==1){
						$where_sql = " AND sto.suite_id = '".$tmpArr[0][0]."' AND sto.suite_num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND st.goods_type_count>=".$includeRelationCount;
					}else{
						$where_sql = " AND sto.spec_id = '".$tmpArr[0][0]."' AND sto.actual_num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND st.goods_type_count>=".$includeRelationCount;
					}
					break;
				case '1':
					foreach($tmpArr as $k=>$v){
						if($v[3]==1){
							if($k==0){
								$where_sql = " AND sto.suite_id = '".$v[0]."' AND sto.suite_num ".$conditionMap[$v[1]]." '".$v[2]."'";
							}else{
								$where_sql .= " OR (sto.suite_id = '".$v[0]."' AND sto.suite_num ".$conditionMap[$v[1]]." '".$v[2]."')";
							}
						}else{
							if($k==0){
								$where_sql = " AND sto.spec_id = '".$v[0]."' AND sto.actual_num ".$conditionMap[$v[1]]." '".$v[2]."'";
							}else{
								$where_sql .= " OR (sto.spec_id = '".$v[0]."' AND sto.actual_num ".$conditionMap[$v[1]]." '".$v[2]."')";
							}
						}
					}
					break;
				case '2':
					if($tmpArr[0][3]==1){
						$where_sql = " AND sto.suite_id = '".$tmpArr[0][0]."' AND sto.suite_num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND st.goods_type_count>=".$includeRelationCount;
					}else{
						$where_sql = " AND sto.spec_id = '".$tmpArr[0][0]."' AND sto.actual_num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND st.goods_type_count=".$includeRelationCount;
					}
					break;
				default:
					\Think\Log::write("unknown include_relation:" . $where['include_relation']);
					break;
			}
			$sales_print_time_range = get_config_value('sales_print_time_range',7);
			$sql = "SELECT GROUP_CONCAT(DISTINCT st.trade_id) AS id"
				." FROM sales_trade st"
				." INNER JOIN sales_trade_order sto ON sto.trade_id = st.trade_id"
				." WHERE st.trade_status=30 ".$where_sql;
			$tmpTradeIds = $this->query($sql);
			$tmpTradeIds = $tmpTradeIds[0]['id'];
			if(($tmpTradeIds!=''&&!empty($tmpTradeIds))&&empty($tmpArr[1])&&$tmpArr[0][3]==1&&$where['include_relation']==2){//对于是组合装的仅包含关系做特殊处理
				//判断对应订单下是否存在其他货品
				$del_sql="SELECT sto.trade_id FROM sales_trade_order sto WHERE sto.trade_id IN(".$tmpTradeIds.") AND sto.suite_id!=".$tmpArr[0][0];
				$delTradeIds = $this->query($del_sql);
				$delIds=array();
				foreach($delTradeIds as $v){$delIds[]=$v['trade_id'];}
				$TradeIds = explode(",",$tmpTradeIds);
				foreach($TradeIds as $k=>$v){
					if(in_array($v,$delIds)){unset($TradeIds[$k]);}
				}
				$tmpTradeIds=implode(",",$TradeIds);
			}
			if(($tmpTradeIds!=''&&!empty($tmpTradeIds))&&$where['include_relation']!=1){
				array_shift($tmpArr);
				foreach($tmpArr as $k=>$v){
					if($v[3]==1){
						$query = "SELECT GROUP_CONCAT(DISTINCT sto.trade_id) AS id FROM sales_trade_order sto"
							." WHERE sto.trade_id IN (".$tmpTradeIds.") AND sto.suite_id = '".$v[0]."' AND sto.suite_num ".$conditionMap[$v[1]]." '".$v[2]."'";
					}else{
						$query = "SELECT GROUP_CONCAT(DISTINCT sto.trade_id) AS id FROM sales_trade_order sto"
						." WHERE sto.trade_id IN (".$tmpTradeIds.") AND sto.spec_id = '".$v[0]."' AND sto.actual_num ".$conditionMap[$v[1]]." '".$v[2]."'";
					}
					$tmpTradeIds = $this->query($query);
					$tmpTradeIds = $tmpTradeIds[0]['id'];
					if($tmpTradeIds==''||empty($tmpTradeIds)){
						break;
					}
				}
			}
			return empty($tmpTradeIds)?'':$tmpTradeIds;
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-includeGoodsIds-'.$msg);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-includeGoodsIds-'.$msg);
		}
	}
	/**
	 * updateTabData方法集
	 */
	public function getGoodsList($id,$type='')
	{
		$id=intval($id);
		$data=array();
		$table=$type=='history'?'sales_trade_order_history':'sales_trade_order';
		try {
			//sales_trade_order_stock_query  IF(".$res_trade_arr['status'].",IF(".$res_cfg_val.",' +ss.purchase_arrive_num ',''),'-ss.order_num')->-ss.order_num
			//$res_cfg_val=get_config_value($model_db, 'order_check_stock_add_purchase');
			$res_trade_arr=$this->field('warehouse_id,src_tids')
			                    ->where(array('trade_id'=>array('eq',$id)))
			                    ->find();
			$point_number = get_config_value('point_number',0);
			$sys_available_stock = get_config_value('sys_available_stock',640);
			$stock=D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('ss.');
			$num = "CAST(sto.num AS DECIMAL(19,".$point_number.")) num";
			$actual_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			$stock_num_all = "CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,".$point_number.")) stock_num_all";
			$stock_num = "CAST(".$stock." AS DECIMAL(19,".$point_number.")) stock_num";
			$suite_num = "CAST(IF(sto.suite_num,sto.suite_num,'') AS DECIMAL(19,".$point_number.")) suite_num";
			$sql="SELECT sto.rec_id, sto.spec_id, sto.platform_id, sto.goods_name, sto.spec_id,sto.spec_name, sto.src_tid, 
				  sto.src_oid, sto.spec_no, sto.goods_no, sto.spec_code, sto.price, sto.order_price, sto.share_price,
				  sto.discount,".$field_right['cost_price'].", ".$num.", ".$actual_num.", ".$stock_num_all.", 
				  ".$stock_num.", sto.share_amount, sto.share_post, sto.paid, 
				  sto.commission, sto.suite_name, ".$suite_num.", sto.suite_no, sto.weight, 
				  sto.guarantee_mode, sto.refund_status, sto.gift_type, sto.invoice_type, sto.api_goods_name, sto.api_spec_name, 
				  sto.remark ,IF(sto.is_consigned,'是','否') is_consigned,gs.img_url FROM $table sto LEFT JOIN goods_spec gs ON sto.spec_id=gs.spec_id LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id  AND
				  ss.warehouse_id=".intval($res_trade_arr['warehouse_id'])."  WHERE sto.trade_id=".$id." ORDER BY sto.refund_status,sto.rec_id ASC";
			$list=$this->query($sql);
			// LEFT JOIN goods_goods gg ON sto.goods_id = gg.goods_id 
			// LEFT JOIN goods_brand gb on gb.brand_id = gg.brand_id
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}catch (BusinessLogicException $e){
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
		
	}	
	
	public function getTradeDetail($id,$type = 1)
	{
		$id=intval($id);
		$data=array();
		try{
			$arr_tmp_trade=array("name4"=>"","name5"=>"","trade_no"=>"订单号","shop_id"=>"店铺名称","src_tids"=>"原始单号","buyer_nick"=>"客户网名","receiver_name"=>"收件人","receiver_area"=>"地区","receiver_address"=>"地址","receiver_mobile"=>"手机","receiver_telno"=>"固话","receiver_zip"=>"邮编","delivery_term"=>"发货条件","refund_status"=>"退货状态","warehouse_id"=>"仓库","logistics_id"=>"物流公司","cs_remark"=>"客服备注","buyer_message"=>"买家留言","print_remark"=>"打印备注","goods_type_count"=>"货品种类数","fenxiao_type"=>"分销类别","goods_count"=>"货品总量","goods_amount"=>"总货款","post_amount"=>"邮费","discount"=>"优惠","receivable"=>"应收","paid"=>"已付","dap_amount"=>"款到发货金额","cod_amount"=>"买家COD费用","commission"=>"佣金","post_cost"=>"邮费成本","goods_cost"=>"货品估算成本","weight"=>"估重","invoice_type"=>"发票类型","invoice_title"=>"发票抬头","invoice_content"=>"发票内容","trade_time"=>"下单时间","pay_time"=>"付款时间","pay_account"=>"支付账户","to_deliver_time"=>"送货时间","salesman_id"=>"业务员","trade_type"=>"订单类型","handle_days"=>"处理天数","trade_from"=>"订单来源","spec_no"=>"货品商家编码","raw_goods_type_count"=>"原始货品种类","raw_goods_count"=>"原始货品数量","remark_flag"=>"标旗","created"=>"递交时间","stockout_no" => "出库单号","platform_id"=>"","logistics_no"=>"物流单号","buyer_message"=>"客户备注");
			$where = array();
			if($type == 1)
			{
				$where['trade_id'] = $id;
			}elseif ($type == 2)
			{
				$where['stockout_id'] = $id;
			}
			//st.fenxiao_type,st.commission,
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$fields = "st.trade_no,st.src_tids,st.stockout_no,sh.shop_name AS shop_id,st.platform_id,sw.name AS warehouse_id,clg.logistics_name AS logistics_id,st.logistics_no,st.cs_remark,st.buyer_message,st.buyer_nick,
					   st.receiver_name, st.receiver_area, st.receiver_address, st.receiver_zip, IF(".$cfg_show_telno."=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno,
					   st.to_deliver_time,st.delivery_term,st.refund_status, st.trade_time, st.pay_time,st.invoice_type, st.invoice_title,st.invoice_content, st.goods_amount, st.post_amount,
					   st.discount, st.receivable,st.dap_amount, st.post_cost,st.goods_cost, st.weight,st.cod_amount, st.paid,st.pay_account, st.trade_type,st.remark_flag, he.fullname AS salesman_id, st.created";
			$list_tmp = $this->alias('st')->field($fields)->join("LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id")->join(" LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id ")->join("LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st.warehouse_id ")->join("LEFT JOIN hr_employee he ON st.salesman_id=he.employee_id")->join("LEFT JOIN stockout_order so on so.src_order_id = st.trade_id")->where($where)->find();
			$list=UtilTool::array2show($list_tmp, $arr_tmp_trade,5,C('detail_formatter'));
			$list[0]['value5'] = "";
			$data=array('total'=>count($list),'rows'=>$list);
			
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
		
	public function getApiTrade($id,$type='trade')
	{
		$id=intval($id);
		$data=array();
		try {
			//api_trades_by_sales_trade
			if($type=='refund')
			{//退换单
				$res_sales_trade_id=M('sales_refund')->field('trade_id')
				                                     ->where('refund_id='.$id)
				                                     ->find();
				$id=$res_sales_trade_id['trade_id'];
			}
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$sql="SELECT DISTINCT at.rec_id,at.platform_id,sh.shop_name AS shop_id ,at.tid,at.process_status,at.trade_status,
				  at.guarantee_mode,at.pay_status,at.delivery_term,at.pay_method,IF(at.platform_id=0,null,at.refund_status) refund_status,at.purchase_id,at.bad_reason,
				  at.trade_time,at.pay_time,at.buyer_message,at.remark,at.remark_flag,at.buyer_nick,at.pay_account, at.receiver_name,
				  at.receiver_country,at.receiver_area,at.receiver_ring,at.receiver_address,IF(".$cfg_show_telno."=0,at.receiver_mobile,INSERT( at.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,at.receiver_telno,INSERT(at.receiver_telno,4,4,'****')) receiver_telno,
				  at.receiver_zip, at.receiver_area,at.to_deliver_time,at.receivable,at.goods_amount,at.post_amount,at.other_amount,
				  at.discount,at.paid,at.platform_cost,at.received, at.dap_amount,at.cod_amount,at.pi_amount,at.refund_amount,
				  at.logistics_type,at.invoice_type,at.invoice_title,at.invoice_content,at.trade_from, at.fenxiao_type,at.fenxiao_nick,
				  he.fullname AS x_salesman_id,at.end_time,at.modified,at.created FROM api_trade `at` LEFT JOIN sales_trade_order sto 
				  ON (sto.shop_id=at.shop_id AND sto.src_tid=at.tid) LEFT JOIN cfg_shop sh ON sh.shop_id=at.shop_id LEFT JOIN hr_employee he 
				  ON he.employee_id=at.x_salesman_id WHERE sto.trade_id=".$id;
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e){
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	
	public function getStockList($id,$lack=0)
	{
		$where='';
		if($lack==1){
			$where=' AND (ss.stock_num-ss.sending_num-tmp_table.actual_num=0 OR ss.stock_num-ss.sending_num-tmp_table.actual_num<0)';
		}
		$search = array();
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_warehouse_id = $search['warehouse_id'];
		$id=intval($id);
		$data=array();
		try {
			//spec_lack_list_by_trade  IF(ss.last_position_id,ss.last_position_id,ss.default_position_id) position_id,
			//$res_cfg_val=get_config_value('sys_available_stock',648);//可发库存数量
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('ss.');
			$point_number = get_config_value('point_number',0);
			$sys_available_stock = get_config_value('sys_available_stock',640);
			$stock=D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
			$actual_num = "CAST(tmp_table.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			$stock_num_all = "CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,".$point_number.")) stock_num_all";
			$stock_num = "CAST(IFNULL(".$stock.",0) AS DECIMAL(19,".$point_number.")) stock_num";
			$purchase_arrive_num = "CAST(IFNULL(ss.purchase_arrive_num,0) AS DECIMAL(19,".$point_number.")) purchase_arrive_num";
			$sending_num = "CAST(IFNULL(ss.sending_num,0) AS DECIMAL(19,".$point_number.")) sending_num";
			$order_num = "CAST(IFNULL(ss.order_num,0) AS DECIMAL(19,".$point_number.")) order_num";
			$subscribe_num = "CAST(IFNULL(ss.subscribe_num,0) AS DECIMAL(19,".$point_number.")) subscribe_num";
			$sql="SELECT gs.spec_no,gg.goods_name,gg.short_name,gg.goods_no,gs.spec_name, gs.spec_code,".$actual_num.",ss.warehouse_id, 
				  sw.name AS warehouse_id,".$purchase_arrive_num.", ".$stock_num_all.", ".$order_num.", ".$subscribe_num.",
				  ".$sending_num.",".$field_right['cost_price'].", ".$stock_num.",gs.remark AS spec_remark 
				  FROM stock_spec ss LEFT JOIN goods_spec gs ON gs.spec_id = ss.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id 
				  LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=ss.warehouse_id, (SELECT sto.spec_id,sto.actual_num FROM sales_trade st 
				  LEFT JOIN sales_trade_order sto ON sto.trade_id = st.trade_id LEFT JOIN stock_spec ss ON ss.spec_id = sto.spec_id AND ss.warehouse_id=st.warehouse_id 
				  WHERE st.trade_id =".$id." )  
				  AS tmp_table WHERE ss.spec_id = tmp_table.spec_id".$where." and ss.warehouse_id in ($where_warehouse_id)";// AND ss.status = 1
			//AND  0 > (ss.stock_num-ss.lock_num-ss.sending_num-ss.subscribe_num-sto.num)
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}catch (BusinessLogicException $e){
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
	}
	
	public function getUnmergeTrade($id)
	{
		$id=intval($id);
		$data=array();
		try {
			//SP_SALES_UNMERGE_BY_TRADE
			$res_trade_arr=$this->field('platform_id,shop_id,warehouse_id,customer_id,buyer_nick,receiver_name,receiver_area,receiver_address')->where('trade_id ='.$id.' AND is_sealed=0 AND delivery_term = 1 AND (unmerge_mask & 2) AND split_from_trade_id <= 0')->find();
			if(empty($res_trade_arr))
			{
				$data=array('total'=>0,'rows'=>array());
				return $data;
			}
			//同名未合并的方式 0 买家+收件人+地址  1店铺+买家+收件人+地址 2店铺+买家+地址 3买家+地址-order_check_merge_warn_mode
			//查看冻结的同名未合并-order_check_warn_has_unmerge_freeze
			//查看地址不同的同名未合并-order_check_warn_has_unmerge_address
			//查看已审核的同名未合并-order_check_warn_has_unmerge_checked
			//查看同名未合并订单是否包含预订单-order_check_warn_has_unmerge_preorder
			//查看已发货的同名未合并-order_check_warn_has_unmerge_completed
			//时间限制-order_check_warn_completed_min
			$res_cfg_val=get_config_value(array('order_check_merge_warn_mode','order_check_warn_has_unmerge_freeze','order_check_warn_has_unmerge_address','order_check_warn_has_unmerge_checked','order_check_warn_has_unmerge_preorder','order_check_warn_has_unmerge_completed','order_check_warn_completed_min','show_number_to_star'),array(0,0,0,1,0,0,0,1));
			$sql="SELECT DISTINCT  st.trade_id, IF(st.trade_id =".$id.",1,0) rec_id,st.trade_no,st.platform_id,sh.shop_name AS shop_id, sw.name AS warehouse_id,st.trade_status,st.customer_id, st.buyer_nick,st.receiver_name,st.receiver_address,st.receiver_area,st.created,st.delivery_term,st.checkouter_id, clg.logistics_name AS logistics_id,he.fullname AS salesman_id,IF(".$res_cfg_val['show_number_to_star']."=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$res_cfg_val['show_number_to_star']."=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno,st.print_remark FROM sales_trade st ";
			$sql.=" LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st.salesman_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st.warehouse_id ";
			if($res_cfg_val['order_check_warn_has_unmerge_completed']&&$res_cfg_val['order_check_warn_completed_min'])
			{
				$sql.=" LEFT JOIN sales_trade_log stl ON stl.trade_id = st.trade_id ";
			}
			$sql.=" WHERE st.`customer_id` = ".$res_trade_arr['customer_id']." AND  st.`trade_status`>=25 AND  st.delivery_term = 1 AND  st.is_sealed = 0 AND  st.split_from_trade_id <= 0 ";
			if($res_trade_arr['platform_id'])
			{
				$sql.=" AND (st.platform_id=".$res_trade_arr['platform_id']." or st.platform_id = 0) AND st.buyer_nick ='".addslashes($res_trade_arr['buyer_nick'])."'";
			}
			if($res_cfg_val['order_check_merge_warn_mode']==1||$res_cfg_val['order_check_merge_warn_mode']==2)
			{
				$sql.="  AND st.shop_id = ".$res_trade_arr['shop_id'];
			}
			if(!($res_cfg_val['order_check_merge_warn_mode']==2||$res_cfg_val['order_check_merge_warn_mode']==3))
			{
				$sql.=" AND st.receiver_name = '".addslashes($res_trade_arr['receiver_name'])."'";
			}
			if(!$res_cfg_val['order_check_warn_has_unmerge_address'])
			{
				$sql.=" AND  st.`receiver_area`= '".addslashes($res_trade_arr['receiver_area'])."' AND st.`receiver_address` = '".addslashes($res_trade_arr['receiver_address'])."'";
			}
			if(!$res_cfg_val['order_check_warn_has_unmerge_freeze'])
			{
				$sql.=" AND st.freeze_reason = 0";
			}
			if(!$res_cfg_val['order_check_warn_has_unmerge_preorder'])
			{
				$sql.=" AND st.trade_status>=30";
			}
			if($res_cfg_val['order_check_warn_has_unmerge_checked'])
			{
				$sql.=" AND st.trade_status < 95";
			}else
			{
				$sql.=" AND st.trade_status < 55";
			}
			if($res_cfg_val['order_check_warn_has_unmerge_completed'])
			{
				if($res_cfg_val['order_check_warn_completed_min'])
				{
					$sql.=" AND (st.`trade_status`<=55 OR (st.trade_status = 95 AND stl.type=105 AND stl.data = 0 AND stl.created>=NOW()-INTERVAL ".$res_cfg_val['order_check_warn_completed_min']." MINUTE) )";
				}else
				{
					$sql.=" AND st.`trade_status`<=95";
				}
			}else 
			{
				$sql.=" AND st.`trade_status`<=55";
			}
			$sql.=" ORDER BY rec_id desc, st.trade_status ASC , st.created DESC LIMIT 100";
			$list=$this->query($sql);
			$data= count($list)>1?array('total'=>count($list),'rows'=>$list):$data=array('total'=>0,'rows'=>array());
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	} 
	
	public function getTradeRefund($id)
	{
		$id=intval($id);
		$data=array();
		try {//21169-cmd.txt
			$cfg_show_telno=get_config_value('show_number_to_star',1);
			$point_number = get_config_value('point_number',0);
			$refund_num = "CAST(sro.refund_num AS DECIMAL(19,".$point_number.")) refund_num";
			$sql='SELECT sr.refund_id,sr.refund_no, sh.shop_name AS shop_id, sr.type, he.fullname AS operator_id, sr.src_no, sr.process_status, 
				  IF(sr.status=0,null,sr.status) status, sr.tid, sr.trade_no, sr.buyer_nick, sr.return_name, IF('.$cfg_show_telno.'=0,sr.return_mobile,INSERT( sr.return_mobile,4,4,\'****\')) return_mobile, IF('.$cfg_show_telno.'=0,sr.return_telno,INSERT( sr.return_telno,4,4,\'****\')) return_telno, sr.return_address, 
				  '.$refund_num.', sr.refund_amount, sr.actual_refund_amount, sro.total_amount, sr.logistics_name, sr.logistics_no, 
				  sr.return_address, sr.from_type, cor.title AS reason_id, sr.sync_result, sr.remark FROM  sales_refund sr INNER JOIN
				  (SELECT str_1.refund_id FROM sales_refund str_1 WHERE str_1.trade_id='.$id.')str_2 ON str_2.refund_id=sr.refund_id 
				  LEFT JOIN cfg_oper_reason cor ON (cor.reason_id=sr.reason_id AND cor.class_id=4) LEFT JOIN cfg_shop sh ON  sr.shop_id=sh.shop_id 
				  LEFT JOIN sales_refund_order sro ON sr.refund_id=sro.refund_id LEFT JOIN hr_employee he ON sr.operator_id=he.employee_id
				  ORDER BY sr.refund_id desc';
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	
	public function getTradRemark($id)
	{
		$id=intval($id);
		$data=array();
		try{
			$res_tids_str=M('sales_trade')->alias('st')->field('st.src_tids')->where('trade_id='.$id)->find();
			$res_tids_arr=explode(',', $res_tids_str['src_tids']);
			if(empty($res_tids_arr))
			{
				$res_tids_arr[]=$res_tids_str['src_tids'];
			}
			$list=array();
			foreach ($res_tids_arr as $tid)
			{
				$sql='SELECT atrh.tid,atrh.remark,atrh.created  FROM api_trade_remark_history atrh WHERE atrh.tid='."'".trim($tid)."' LIMIT 1";
				$tmp_list=$this->query($sql);
				$list=array_merge($list,$tmp_list);
			}			
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data;
	}
	
	public function getTradeLog($id,$type = "sales_trade")
	{	
		$id=intval($id);
		$data=array();
		try {//stl_1.type<100 AND 
		    switch ($type) {
		        case "sales_stockout":
			        $sql='SELECT stl_2.message,stl_2.created,he.fullname AS operator_id FROM sales_trade_log stl_2 LEFT JOIN hr_employee he ON stl_2.operator_id=he.employee_id LEFT JOIN stockout_order so on so.src_order_id = stl_2.trade_id  WHERE so.stockout_id='.$id.' ORDER BY stl_2.rec_id DESC';
		            //INNER JOIN(SELECT stl_1.rec_id FROM sales_trade_log stl_1 left join stockout_order so on so.src_order_id = stl_1.trade_id WHERE so.stockout_id='.$id.' ORDER BY stl_1.rec_id DESC) stl_3 ON stl_3.rec_id=stl_2.rec_id 
			        break;
		        case "sales_trade":
			        $sql='SELECT stl_2.message,stl_2.created,he.fullname AS operator_id FROM sales_trade_log stl_2 LEFT JOIN hr_employee he ON stl_2.operator_id=he.employee_id WHERE stl_2.trade_id='.$id.' ORDER BY stl_2.rec_id DESC';
			        //INNER JOIN(SELECT stl_1.rec_id FROM sales_trade_log stl_1 WHERE stl_1.trade_id='.$id.' ORDER BY stl_1.rec_id DESC) stl_3 ON stl_3.rec_id=stl_2.rec_id
			        break;
		        default:
		            \Think\Log::write("getTradeLog does't have log type");
		        break;
		    }
			$list=$this->query($sql);
			$data=array('total'=>count($list),'rows'=>$list);
		}catch(\PDOException $e)
		{
			$data=array('total'=>0,'rows'=>array());
			\Think\Log::write($e->getMessage());
		}
		return $data; 
	}

	/**
	 * 订单相关的共通功能方法(被调用)
	 */

	/**
	 * 更新标记同名未合并
	 * @param integer $trade_id
	 * @param integer $customer_id
	 */
	public function updateWarnUnmerge($trade_id,$customer_id)
	{
		$trade_id=intval($trade_id);
		$customer_id=intval($customer_id);
		$sql_error_info='';
		try {
			$sql_error_info='updateWarnUnmerge-flag_sales_trade_merged_update_error_1';
			$res_flag=$this->execute("UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2),modified=IF(modified=NOW(),NOW()+INTERVAL 1 SECOND,NOW()) WHERE trade_status>=15 AND trade_status<=95 AND customer_id=".intval($customer_id)." AND is_sealed=0 AND delivery_term=1 AND split_from_trade_id<=0 AND trade_id <> ".intval($trade_id));
			if($res_flag>0)
			{
				$sql_error_info='updateWarnUnmerge-flag_sales_trade_merged_update_error_2';
				$res_flag=$this->execute("UPDATE sales_trade SET unmerge_mask=(unmerge_mask|2) WHERE trade_id=".intval($trade_id));
			}else
			{
				$sql_error_info='updateWarnUnmerge-flag_sales_trade_merged_update_error_3';
				$this->execute("UPDATE sales_trade SET unmerge_mask=(unmerge_mask & ~2) WHERE trade_id=".intval($trade_id));
			}
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	/**
	 * 订单全链路
	 * @param object $db
	 * @param integer $trade_id 订单id 
	 * @param integer $trade_status 订单状态
	 * @param string $remark 订单备注
	 * @param integer $split_from_trade_id 是否拆分订单
	 * @param integer $sales_trade_trace_enable 是否开启全链路   默认为null
	 */
	public function traceTrade($user_id,$trade_id,$trade_status,$remark,$split_from_trade_id,$sales_trade_trace_enable=NULL)
	{//I_SALES_TRADE_TRACE
		$sql_error_info='';
		try {
			$res_cfg_val=array();
			if(empty($sales_trade_trace_enable))
			{
				$res_cfg_val=get_config_value(array('sales_trade_trace_enable','sales_trade_trace_operator'),array(1,0));
			}
			if ($res_cfg_val['sales_trade_trace_enable']==0)
			{
				return;
			}
			//$split_from_trade_id为空，从数据中获取
			if(empty($split_from_trade_id))
			{
				$sql_error_info='sales_trade_query-get_split_from_trade_id';
				$split_trade=$this->getSalesTrade('split_from_trade_id', array('trade_id'=>array('eq',$trade_id)));
				$split_from_trade_id=$split_trade['split_from_trade_id'];
			}
			$sql_error_info='sales_trade_order_query-get_oids';
			$trade_trace=$this->query("SELECT sto.src_tid AS tid,IF(".$split_from_trade_id.",GROUP_CONCAT(sto.src_oid),'') AS oids,ax.shop_id FROM sales_trade_order sto, api_trade ax WHERE sto.trade_id=".$trade_id." AND sto.shop_id=ax.shop_id AND sto.actual_num>0 AND ax.platform_id=1 AND ax.tid=sto.src_tid GROUP BY sto.src_tid");
			//$trade_trace=$this->query("SELECT sto.src_tid AS tid,IF(".$split_from_trade_id.",GROUP_CONCAT(sto.src_oid),'') AS oids,ax.shop_id FROM sales_trade_order sto INNER JOIN api_trade ax ON (sto.trade_id=".$trade_id." AND sto.shop_id=ax.shop_id AND sto.actual_num>0 AND ax.platform_id=1 AND ax.tid=sto.src_tid) GROUP BY sto.src_tid");
			
			$operator='';
			if ($res_cfg_val['sales_trade_trace_operator']!=0)
			{
				$sql_error_info='hr_employee_query-get_account';
				$employee=M('hr_employee')->field('fullname')->where(array('employee_id'=>array('eq',$user_id)))->find();
				$operator=$employee['fullname'];
			}
			$length=count($trade_trace);
			$ati=empty($_COOKIE['_ati']) ? '0000000000' : $_COOKIE['_ati'];
			for ($i=0;$i<$length;$i++)
			{
				$trade_trace[$i]['trade_id']=$trade_id;
				$trade_trace[$i]['status']=$trade_status;
				$trade_trace[$i]['operator']=$operator;
				$trade_trace[$i]['ati']=$ati;
				$trade_trace[$i]['remark']=$remark;
			}
			$sql_error_info='sales_trade_trace_add';
			M('sales_trade_trace')->addAll($trade_trace);
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
	}
	
	/**
	 * 生成出库单
	 * @param object $db
	 * @param integer $trade_id
	 * @param integer $warehouse_id
	 * @param integer $trade_status
	 * @param string $outer_no
	 */
	public function generateStockOut($user_id,$trade_id,$warehouse_id,$trade_status,$outer_no='',$box_no='',$sales_raw_count_exclude_gift=NULL)
	{//I_SALES_TRADE_GENERATE_STOCKOUT
		$trade_id=intval($trade_id);
		$warehouse_id=intval($warehouse_id);
		$trade_status=intval($trade_status);
		$outer_no=addslashes($outer_no);
		$box_no=addslashes($box_no);
		$sql_error_info='';
		try {
			$result=array();
			if(empty($sales_raw_count_exclude_gift))
			{//订单中货品原始货品数量不包含赠品
				$sales_raw_count_exclude_gift=get_config_value('sales_raw_count_exclude_gift');
			}
			
			//计算原始货品货品数量--种类数量        --  逻辑重构
			$sql_error_info='generateStockOut-sales_orders_query-get_goods_count-get_goods_type_count';
			$orders=$this->query(
					'SELECT COUNT(DISTINCT spec_no) AS goods_type_count,SUM(num) AS goods_count FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,
					IF(suite_id,suite_num,actual_num) num FROM sales_trade_order WHERE trade_id=%d AND actual_num>0 AND 
					IF('.$sales_raw_count_exclude_gift.',gift_type=0,1) GROUP BY platform_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp',
					array($trade_id)
			);
			//$orders=$db->query("SELECT COUNT(DISTINCT spec_no) AS goods_type_count,SUM(num) AS goods_count FROM (SELECT IF(suite_id,suite_no,spec_no) spec_no,IF(suite_id,suite_num,actual_num) num FROM sales_trade_order sto LEFT JOIN cfg_shop sh ON sh.shop_id=sto.shop_id WHERE trade_id=".$trade_id." AND actual_num>0 AND IF(".$sales_raw_count_exclude_gift.",gift_type=0,1) GROUP BY sh.platform_id,src_oid,IF(suite_id,suite_no,spec_no)) tmp");
			$single_spec_no='';
			if($orders[0]['goods_type_count']==1)
			{
				//判断配置
				$single_spec_no_code=get_config_value('single_spec_no_code',0);
				if($single_spec_no_code==1){
					$sql_error_info='generateStockOut-sales_orders_query-get_single_spec_no';
					$res_single_spec_no=$this->query(
						'SELECT IF(suite_id,suite_name,CONCAT(spec_no,"-",spec_name)) AS single_spec_no FROM sales_trade_order WHERE trade_id=%d AND actual_num>0 AND
					IF('.$sales_raw_count_exclude_gift.',gift_type=0,1) LIMIT 1',array($trade_id)
					);
				}else{
					$sql_error_info='generateStockOut-sales_orders_query-get_single_spec_no';
					$res_single_spec_no=$this->query(
						'SELECT IF(suite_id,suite_name,CONCAT(goods_name,"-",spec_name)) AS single_spec_no FROM sales_trade_order WHERE trade_id=%d AND actual_num>0 AND
					IF('.$sales_raw_count_exclude_gift.',gift_type=0,1) LIMIT 1',array($trade_id)
					);
				}

				$single_spec_no=$res_single_spec_no[0]['single_spec_no'];
			}else{
				$single_spec_no='多种货品';
			}
			if(empty($orders[0]['goods_count']))
			{
				$orders[0]['goods_count']=0;
			}
			
			//------------------------------判断订单的库存,物流----------------------------
			$sql_error_info='generateStockOut-sales_trade_query-get_sales_trade_arr';
			$stockout_trade=$this->getSalesTrade(
					'cl.logistics_type,cl.bill_type,st.trade_id AS src_order_id, st.trade_no AS src_order_no, wh.type AS warehouse_type, 
					st.warehouse_id, st.customer_id, st.goods_count, st.goods_type_count, st.receiver_name, st.receiver_country, st.receiver_province, 
					st.receiver_city, st.receiver_district, st.receiver_address, st.receiver_mobile, st.receiver_telno, st.receiver_zip, st.receiver_area, 
					st.receiver_dtb, st.to_deliver_time, st.dist_center, st.dist_site, st.logistics_id, st.package_id, st.goods_amount AS goods_total_amount, 
					st.goods_cost AS goods_total_cost, st.post_cost AS calc_post_cost, st.weight AS calc_weight, st.invoice_type AS has_invoice,
					'.$user_id.' operator_id,st.flag_id,st.is_stalls,NOW() created',
					array('trade_id'=>array('eq',$trade_id)),
					'st',
					'LEFT JOIN cfg_logistics cl ON cl.logistics_id=st.logistics_id LEFT JOIN cfg_warehouse wh ON wh.warehouse_id=st.warehouse_id'
			);
			$bill_type=$stockout_trade['bill_type'];
			$logistics_type=$stockout_trade['logistics_type'];
			unset($stockout_trade['bill_type']);
			unset($stockout_trade['logistics_type']);
			//热敏优先级低于仓储,如果是仓储的话，状态会为52(待推送)-- 处理韵达、顺丰、云栈电子面单
			if($stockout_trade['warehouse_type']>1 && $stockout_trade['warehouse_type'] !=127)
			{
				$trade_status=52;
				$sql_error_info='generateStockOut-sys_asyn_task_add';
				\Think\Log::write($sql_error_info,\Think\Log::ERR,'',get_log_path('notes_info'));
				//$db->execute("INSERT INTO sys_asyn_task(task_type,target_type,target_id,target_no,created) VALUES(1,1,".$trade_id.",'".$stockout_trade['trade_no']."',NOW()) ON DUPLICATE KEY UPDATE status=0,rand_flag=0");
			}else if($bill_type==2 || ($logistics_type==6 || $logistics_type==8 || $logistics_type==9 || $logistics_type==10))
			{
				$trade_status=55;//$trade_status=54;//驳回的订单--暂不设置出库单号--状态54(获取面单号)
				$sql_error_info='generateStockOut-sys_setting_query';
				$is_manual_get_logistics=$this->query("SELECT 1 FROM cfg_setting WHERE `key` = 'stock_get_logistics_no_manual' AND value = '1'");
				$sql_error_info='generateStockOut-cfg_logistics_query';
				$is_manual_get_logistics_id=$this->query('SELECT 1 FROM cfg_logistics WHERE logistics_id=%d AND is_manual = 1',array($stockout_trade['logistics_id']));
				if(!empty($is_manual_get_logistics)||empty($is_manual_get_logistics_id))
				{
					$trade_status=55;
				}
			}
			
			//------------------查找是否有之前驳回的出库单,如果有则恢复---------------
			$sql_error_info='generateStockOut-stockout_order_query';
			$stockout_order_db=M('stockout_order');
			$stockout_order=$stockout_order_db->field('stockout_id,stockout_no,logistics_id,`status`')->where(array('src_order_type'=>array('eq',1),'src_order_id'=>array('eq',$trade_id)))->find();
			$stockout_id=$stockout_order['stockout_id'];
			if($stockout_order['stockout_id']!=0)
			{//-----------------------------更新出库单-----------------------
				if($stockout_order['status']!=5)
				{
					$result['result']='订单已审核';
					return $result;
				}			
				//回收电子面单
				/* if($stockout_order['logistics_id']!=$stockout_trade['logistics_id'] && $stockout_order['logistics_id']!=0)
				{
					//回收电子面单(后期改该存储过程 I_STOCK_LOGISTICS_NO_RECYCLE)
					$sql_error_info='generateStockOut-I_STOCK_LOGISTICS_NO_RECYCLE';
					$db->execute("CALL I_STOCK_LOGISTICS_NO_RECYCLE(".$stockout_order['stockout_id'].")");
					//清空物流单号
					$sql_error_info='generateStockOut-stockout_order_update';
					$db->execute("UPDATE stockout_order SET logistics_no='' WHERE stockout_id=".$stockout_order['stockout_id']);
					$sql_error_info='generateStockOut-sales_trade_update';
					$db->execute("UPDATE sales_trade SET logistics_no='' WHERE trade_id=".$trade_id);
				} */
				$pos=strpos($stockout_order['stockout_no'],'-');
				if ($pos===false)
				{
					$stockout_order['stockout_no'].='-1';
				}else 
				{
					$stockout_order['stockout_no']=substr($stockout_order['stockout_no'], 0, $pos+1).(intval(substr($stockout_order['stockout_no'], $pos+1))+1);
				}
				//恢复原来的出库单
				$sql_error_info='generateStockOut-stockout_order_reply';
				//$db->execute("UPDATE stockout_order so ,sales_trade st SET so.`status`=".$trade_status.",so.stockout_no='".$stockout_order['stockout_no']."',so.warehouse_type=st.warehouse_type,so.warehouse_id=st.warehouse_id, so.single_spec_no='".$single_spec_no."',so.raw_goods_count=".$orders[0]['goods_count'].",so.raw_goods_type_count=".$orders[0]['goods_type_count'].", so.goods_count=st.goods_count,so.goods_type_count=st.goods_type_count,so.receiver_name=st.receiver_name,so.error_info = '', so.receiver_country=st.receiver_country,so.receiver_province=st.receiver_province,so.receiver_city=st.receiver_city, so.receiver_district=st.receiver_district,so.receiver_address=st.receiver_address,so.receiver_mobile=st.receiver_mobile, so.receiver_telno=st.receiver_telno,so.receiver_zip=st.receiver_zip,so.receiver_area=st.receiver_area,so.receiver_dtb=st.receiver_dtb, so.to_deliver_time=st.to_deliver_time,so.dist_center=st.dist_center,so.dist_site=st.dist_site,so.logistics_id=st.logistics_id,so.package_id=st.package_id, so.goods_total_amount=st.goods_amount,so.goods_total_cost=st.goods_cost,so.calc_post_cost=st.post_cost,so.calc_weight=st.weight, so.operator_id = ".$user_id.",so.outer_no='".$outer_no."',so.wms_status=0,so.flag_id=st.flag_id,so.created = NOW() WHERE st.trade_id = so.src_order_id AND so.stockout_id=".$stockout_order['stockout_id']);
				$order_deliver_block_consign=get_config_value('order_deliver_block_consign',0);
				$block_reason=0;
				if($order_deliver_block_consign){
					$api_consign=$this->query('SELECT ap.trade_status FROM api_trade ap LEFT JOIN sales_trade st ON ap.tid in(st.src_tids) WHERE st.trade_id='.$trade_id);
					$consign_status=$this->query('SELECT consign_status FROM stockout_order WHERE src_order_type=1 AND src_order_id='.$trade_id);
					foreach ($api_consign as $v){
						if($v['trade_status']>=50 ){
							if (!empty($consign_status[0]['consign_status'])&&($consign_status[0]['consign_status'] & 128)) {
								$block_reason=0;
							}else{
								$block_reason=4096;
							}
						}
					}
				}
				$this->execute(
					"UPDATE stockout_order so ,sales_trade st,cfg_warehouse wh SET so.`status`=".$trade_status.",so.stockout_no='".$stockout_order['stockout_no']."',so.warehouse_type=wh.type,so.warehouse_id=st.warehouse_id, 
					so.single_spec_no='%s',so.raw_goods_count=".$orders[0]['goods_count'].",so.raw_goods_type_count=".$orders[0]['goods_type_count'].", so.goods_count=st.goods_count,
					so.goods_type_count=st.goods_type_count,so.receiver_name=st.receiver_name,so.error_info = '', so.receiver_country=st.receiver_country,so.receiver_province=st.receiver_province,
					so.receiver_city=st.receiver_city, so.receiver_district=st.receiver_district,so.receiver_address=st.receiver_address,so.receiver_mobile=st.receiver_mobile, so.receiver_telno=st.receiver_telno,
					so.receiver_zip=st.receiver_zip,so.receiver_area=st.receiver_area,so.receiver_dtb=st.receiver_dtb, so.to_deliver_time=st.to_deliver_time,so.dist_center=st.dist_center,so.dist_site=st.dist_site,
					so.logistics_id=st.logistics_id,so.package_id=st.package_id, so.goods_total_amount=st.goods_amount,so.goods_total_cost=st.goods_cost,so.calc_post_cost=st.post_cost,so.calc_weight=st.weight, 
					so.operator_id = ".$user_id.",so.outer_no='".$outer_no."',so.wms_status=0,so.flag_id=st.flag_id,so.block_reason=".$block_reason.",so.box_no='".$box_no."',so.is_stalls=".$stockout_trade['is_stalls'].",so.created = NOW() 
					WHERE st.trade_id = so.src_order_id  AND st.warehouse_id = wh.warehouse_id AND so.stockout_id=".$stockout_order['stockout_id'],$single_spec_no
				);
				$stockout_trade['stockout_no']=$stockout_order['stockout_no'];
				//删除出库单货品
				$sql_error_info='generateStockOut-stockout_order_detail_delete';
				$this->execute('DELETE FROM stockout_order_detail WHERE stockout_id='.intval($stockout_order['stockout_id']));
			}else 
			{//-----------------------------生成出库单-----------------------
				$sql_error_info='generateStockOut-FN_SYS_NO';
				$stockout_no=get_sys_no('stockout');
				
				$stockout_trade['stockout_no']=$stockout_no;
				$stockout_trade['src_order_type']=1;
				$stockout_trade['status']=$trade_status;
				$stockout_trade['single_spec_no']=$single_spec_no;
				$stockout_trade['raw_goods_count']=$orders[0]['goods_count'];
				$stockout_trade['raw_goods_type_count']=$orders[0]['goods_type_count'];
				$stockout_trade['outer_no']=$outer_no;
				$stockout_trade['consign_status'] = 16;
				$stockout_trade['box_no']=$box_no;
				//根据配置判断出库单是否需要加标记
				$order_deliver_block_consign=get_config_value('order_deliver_block_consign',0);
				if($order_deliver_block_consign){
					$api_consign=$this->query('SELECT ap.trade_status FROM api_trade ap LEFT JOIN sales_trade st ON ap.tid in(st.src_tids) WHERE st.trade_id='.$trade_id);
					$consign_status=$this->query('SELECT consign_status FROM stockout_order WHERE src_order_type=1 AND src_order_id='.$trade_id);
					foreach ($api_consign as $v){
						if($v['trade_status']>=50 ){
							if (!empty($consign_status[0]['consign_status'])&&($consign_status[0]['consign_status'] & 128)) {
								$block_reason=0;
							}else{
								$block_reason=4096;
							}
						}
					}
				}
				$sql_error_info='generateStockOut-stockout_order_add';
				$stockout_id=$stockout_order_db->add($stockout_trade);
				//-----------------生成确认收货地址的短信记录-----------------
				$cfg_open_message_strategy = get_config_value('cfg_open_message_strategy',0);
				if($cfg_open_message_strategy){
					UtilTool::crm_sms_record_insert(9,$trade_id);
				}
			}
			//-----------------------------电子面单回收(暂时不处理云栈等)-----------------------
			$logistics=array();
			if($stockout_trade['warehouse_type']<=1)
			{
				if($bill_type==2)
				{//云栈
					$sql_error_info='generateStockOut-yunzhan';
					$logistics=$this->query("SELECT sln.logistics_no,sln.receiver_dtb FROM stockout_order so LEFT JOIN stock_logistics_no sln ON sln.stockout_id=so.stockout_id LEFT JOIN cfg_warehouse sw ON so.warehouse_id=sw.warehouse_id WHERE so.stockout_id=".$stockout_id." AND sln.logistics_id=so.logistics_id AND sln.status=5 AND sln.sender_province=sw.province AND sln.sender_city=sw.city AND sln.sender_district=sw.district AND sln.sender_address=sw.address AND sln.type = 0 AND sln.receiver_info=CONCAT(so.receiver_area,so.receiver_name,so.receiver_mobile) LIMIT 1");
				}else if($bill_type==1 && ($logistics_type==8 || $logistics_type==9 || $logistics_type==6 || $logistics_type==10))
				{//韵达顺丰申通百世汇通
					$sql_error_info='generateStockOut-yunda-shunfeng-shentong';
					$logistics=$this->query("SELECT sln.logistics_no,sln.receiver_dtb FROM stockout_order so LEFT JOIN stock_logistics_no sln ON sln.stockout_id=so.stockout_id WHERE so.stockout_id=".$stockout_id." AND sln.logistics_id=so.logistics_id AND sln.status=5 AND sln.receiver_info=so.receiver_area AND sln.type = 0 LIMIT 1");
				}else if($bill_type==1 && ($logistics_type==3 || $logistics_type==4 || $logistics_type==5))
				{// EMS、ZTO、YTO
					$sql_error_info='generateStockOut-EMS-ZTO-YTO';
					$logistics=$this->query("SELECT sln.logistics_no FROM stockout_order so LEFT JOIN stock_logistics_no sln ON sln.stockout_id=so.stockout_id WHERE so.stockout_id=".$stockout_id." AND sln.logistics_id=so.logistics_id AND sln.status=5 AND sln.type = 0 LIMIT 1");
				}else if($bill_type==1 && $logistics_type==1311)
				{//JBD
					$sql_error_info='generateStockOut-JBD';
					$logistics=$this->query("SELECT sln.logistics_no FROM stockout_order so LEFT JOIN stock_logistics_no sln ON sln.stockout_id=so.stockout_id LEFT JOIN sales_trade st ON so.src_order_id = st.trade_id WHERE so.stockout_id=".$stockout_id." AND sln.logistics_id=so.logistics_id AND sln.status=5 AND st.shop_id=sln.shop_id AND sln.type = 0 LIMIT 1");
				}
			}
			/*if(!empty($logistics[0]['logistics_no']))
			{//找到了上次使用的物流单号，则更新到相应的表里
				$sql_error_info='generateStockOut-stockout_order_update_logistics_no';
				$db->execute("UPDATE stockout_order SET logistics_no =  '".$logistics[0]['logistics_no']."',status=55,receiver_dtb = IF('".$logistics[0]['receiver_dtb']."' = '',receiver_dtb,'".$logistics[0]['receiver_dtb']."') WHERE stockout_id=".$stockout_id);
				$sql_error_info='generateStockOut-sales_trade_update_logistics_no';
				$db->execute("UPDATE sales_trade SET logistics_no = '".$logistics[0]['logistics_no']."',receiver_dtb = IF('".$logistics[0]['receiver_dtb']."' = '',receiver_dtb,'".$logistics[0]['receiver_dtb']."') WHERE trade_id=".$stockout_id);
				$sql_error_info='generateStockOut-stock_logistics_no_update';
				$db->execute("UPDATE stock_logistics_no SET status = 1 WHERE stockout_id=".$stockout_id." AND logistics_id=".$stockout_trade['logistics_id']." AND logistics_no='".$logistics[0]['logistics_no']."' AND `status`=5 AND `type`=0");
				if($bill_type==2 || ($bill_type==1 && ($logistics_type==8 || $logistics_type==9 || $logistics_type==6 || $logistics_type==10)))
				{
					$sql_error_info='generateStockOut-stock_logistics_sync_update';
					\Think\Log::write($sql_error_info,\Think\Log::ERR,'',get_log_path('notes_info'));
					//$db->execute("UPDATE stock_logistics_sync SET sync_status=2,logistics_no='".$logistics[0]['logistics_no']."' WHERE stockout_id = ".$stockout_id." AND logistics_type = ".$logistics_type." AND logistics_no = ''");
				}
			}*/
			
			//-----------------------------生成出库单货品-----------------------
			$sql_error_info='generateStockOut-sales_trade_order_query-get_stockout_order_detail';
			// $trade_orders=$this->query("SELECT ".$stockout_id." stockout_id, 1 src_order_type,rec_id AS src_order_detail_id,actual_num AS num,share_price AS price,share_amount AS total_amount,goods_name,goods_id,goods_no, spec_name,spec_id,spec_no,spec_code,weight,is_allow_zero_cost,remark,NOW() created FROM sales_trade_order WHERE trade_id = ".$trade_id." AND actual_num>0");
			$sales_order_db=D('Trade/SalesTradeOrder');
			$trade_orders=$sales_order_db->getSalesTradeOrderList(
				$stockout_id.' AS stockout_id, 1 src_order_type,if(ssp.position_id is NULL,-'.$stockout_trade['warehouse_id'].',ssp.position_id) position_id,so.rec_id AS src_order_detail_id,so.actual_num AS num,so.share_price AS price,so.share_amount AS total_amount,
				so.goods_name,so.goods_id,so.goods_no, so.spec_name,so.spec_id,so.spec_no,so.spec_code,so.weight,so.is_allow_zero_cost,so.remark,NOW() created',
				array('so.trade_id'=>array('eq',$trade_id),'so.actual_num'=>array('gt',0)),'so','left join stock_spec_position ssp on ssp.spec_id = so.spec_id and ssp.warehouse_id = '.$stockout_trade['warehouse_id']
			);
			$sql_error_info='generateStockOut-sales_trade_order_add-get_stockout_order_detail';
			M('stockout_order_detail')->addAll($trade_orders);
	
			//库存信息变化
			$sql_error_info='generateStockOut-I_RESERVE_STOCK';
			$this->execute("CALL I_RESERVE_STOCK(".$trade_id.",4,".$warehouse_id.",".$warehouse_id.")");
			
			//更改库存保留字段
			$sql_error_info='generateStockOut-sales_trade_order_update';
			$sales_order_db->updateSalesTradeOrder(array('stock_reserved'=>4),array('trade_id'=>array('eq',$trade_id),'actual_num'=>array('gt',0)));
			//仓库接单
			if($trade_status>54)
			{
				$this->traceTrade($user_id,$trade_id, 4,'');
			}
			if($stockout_trade['warehouse_type'] == 11){
				$this->execute('INSERT INTO sys_asyn_task(task_type,target_type,target_id,target_no,operator_id,created)
							values(1,1,'.$trade_id.',"'.$stockout_trade['src_order_no'].'",'.$user_id.',NOW())');
				$this->execute('update stockout_order set status = 57,wms_status = 3 where stockout_id = '.$stockout_id);
			}
			// 更新分拣墙
			if ($box_no!='') {
				$this->execute("update sorting_wall_detail set is_use = 1, stockout_id= ".$stockout_id." where box_no = '".$box_no."'");
				$this->execute("update big_box_goods_map set stockout_id= ".$stockout_id.",stockout_no= '".$stockout_trade['stockout_no']."' where box_no = '".$box_no."'");
			}
			$result['stockout_no']=$stockout_trade['stockout_no'];
			$result['stockout_id']=$stockout_id;
			$result['bill_type']=$bill_type;
		}catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $result;
	}

	/**
	 * 生成档口缺货明细
	 * @param integer $trade_id
	 * @param string  $box_no
	 * @param array   $remain_info
	 */
	public function generateStallsLessGoodsDetail($user_id,$trade_id,$box_no='',$remain_info=array())
	{
		// 一个货品一条数据
		$block_reason=0;
		$sql_error_info='';		
		$trade_id=intval($trade_id);
		$update_result=false;
		$add_result=false;
		$result=array();		
		try{
			$result['status']=false;
			$result['box_no']='';
			// 获取当前订单的信息
			$sql_error_info='generateStallsLessGoodsDetail-getSalesTradeOrderList';
			$trade_orders=D('SalesTradeOrder')->alias('sto')->field('sto.spec_id,sto.trade_id,st.trade_no,
				      st.warehouse_id,SUM(sto.actual_num) as actual_num,IFNULL(ss.cost_price,0) cost_price,
				      (ss.stock_num-ss.sending_num) as available_stock')->join('
				      LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id 
					  LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=st.warehouse_id
				      ')->where(array('sto.trade_id'=>array('eq',$trade_id),'sto.actual_num'=>array('gt',0)))->group('sto.spec_id')->select();
			// 查找订单对应的货品数量
			$trade_num=0;//订单中的货品总数
		    $trade_num=D('SalesTrade')->field('goods_count')->where(array('trade_id'=>array('eq',$trade_id)))->find();
		   // 判断是否为驳回订单 
			$less_goods_db=M('stalls_less_goods_detail');
			$less_goods_detail_order=$less_goods_db->field('spec_id,SUM(num) as total_num,trade_id,trade_no,stalls_id,warehouse_id,trade_status,unique_code')->where(array('trade_id'=>array('eq',$trade_id)))->group('spec_id')->select();
			$less_spec_order=array();
			foreach ($less_goods_detail_order as $lo) {
				$less_spec_order[$lo['spec_id']]=$lo;
				$less_spec_id[]=$lo['spec_id'];
			}
			// 平台已发货配置			
			$order_deliver_block_consign=get_config_value('order_deliver_block_consign',0);
			if($order_deliver_block_consign){
				$api_consign=$this->query('SELECT ap.trade_status FROM api_trade ap LEFT JOIN sales_trade st ON ap.tid in(st.src_tids) WHERE st.trade_id='.$trade_id);
				$consign_status=$this->query('SELECT consign_status FROM stockout_order WHERE src_order_type=1 AND src_order_id='.$trade_id);
				foreach ($api_consign as $v){
					if($v['trade_status']>=50 ){
						if (!empty($consign_status[0]['consign_status'])&&($consign_status[0]['consign_status'] & 128)) {
							$block_reason=0;
						}else{
							$block_reason=4096;
						}
					}
				}
			}
			//=====================一键合并且审核，保留缺货明细====================================
			if (!empty($remain_info)) {						
				// 更新原订单的缺货明细$remain_info['old_trade']
				$sql_error_info='generateStallsLessGoodsDetail-update_stalls_less_detail_remain';
				$update_data['trade_id']=$trade_id;
				$update_data['trade_no']=$trade_orders[0]['trade_no'];
				$update_data['warehouse_id']=$trade_orders[0]['warehouse_id'];
				$update_data['trade_status']=0;
				if (!empty($remain_info['old_order'])) {
					$update_result=M('stalls_less_goods_detail')->where(array('trade_id'=>array('eq',$remain_info['old_order'])))->save($update_data);
				}				
				// 更新订单为档口单
				$sql_error_info='generateStallsLessGoodsDetail-update_sales_trade';
				$st_update_result=$this->execute('UPDATE sales_trade SET is_stalls=1 where trade_id='.$trade_id);
				// 新订单生成缺货明细$remain_info['new_trade']PS：需要在合并之前判断新订单的缺货情况
				foreach ($remain_info['new_orders'] as $sto) {
					 $sto['block_reason']=$block_reason;
					 $add_result=$this->addStallsLessGoodsDetail($sto,$sto['less_num'],100);
				}
				if ($update_result||$add_result) { $result['status']=true;}	
				// 返回值
				return $result;
			}	
			//=============================正常处理订单====================================		
			// 查找订单对应的货品数量
			$trade_num=0;//订单中的货品总数
		    $trade_num=D('SalesTrade')->field('goods_count')->where(array('trade_id'=>array('eq',$trade_id)))->find();
		   // 判断是否为驳回订单 
			$less_goods_db=M('stalls_less_goods_detail');
			$less_goods_detail_order=$less_goods_db->field('spec_id,SUM(num) as total_num,trade_id,trade_no,stalls_id,warehouse_id,trade_status,unique_code')->where(array('trade_id'=>array('eq',$trade_id)))->group('spec_id')->select();
			$less_spec_order=array();
			foreach ($less_goods_detail_order as $lo) {
				$less_spec_order[$lo['spec_id']]=$lo;
				$less_spec_id[]=$lo['spec_id'];
			}			
			// 轮询每个货品判断子订单货品库存是否充足
			$is_less_good=0;
			foreach ($trade_orders as $sto) {	
				$order_spec_id[]=$sto['spec_id'];		
				$odd_num=$sto['available_stock']-$sto['actual_num'];				
				if($odd_num>=0){
					if (!empty($less_spec_order[$sto['spec_id']])) {
		 			$less_goods_db->where('trade_id='.$trade_id.' and spec_id='.$sto['spec_id'])->delete(); 
					}
					continue;
				}					
				//查找对应的供应商
				$sql_error_info='generateStallsLessGoodsDetail-get_provider_id';
				$provider_id=M('purchase_provider_goods')->field('provider_id')->where('spec_id='.$sto['spec_id'].' and is_disabled=0')->order('price')->find();
				// 库存不足
				$sto['provider_id']=empty($provider_id)?0:$provider_id['provider_id'];
				$sto['block_reason']=$block_reason;
				$is_less_good=1;
				// 更新订单为档口单
				$sql_error_info='generateStallsLessGoodsDetail-update_sales_trade';
				$st_update_result=$this->execute('UPDATE sales_trade SET is_stalls=1 where trade_id='.$trade_id);

				$less_num=abs($odd_num)>$sto['actual_num']?$sto['actual_num']:abs($odd_num);	
				if (!empty($less_spec_order[$sto['spec_id']])) {//驳回订单
					$exist_num=$less_spec_order[$sto['spec_id']]['total_num'];//已存在数量
					// 更新已存在明细单
					$sql_error_info='generateStallsLessGoodsDetail-update_stalls_less_detail';
					$update_result=$less_goods_db->execute(
						'UPDATE stalls_less_goods_detail SET stalls_id=0,warehouse_id='.$sto['warehouse_id'].',tag=0,unit_ratio=1,trade_status=0,
						block_reason='.$block_reason.',unit_id=0,sort_status=0,stockin_status=0,pickup_status=0,logistics_print_status=0,tag_print_status=0,generate_status=0,hot_status=0,
						provider_id='.$sto['provider_id'].',base_unit_id=0,price='.$sto['cost_price'].',discount=1,tax=0,tax_price='.$sto['cost_price'].',tax_amount='.$sto['cost_price'].',remark=\'\',prop1=\'\',prop2=\'\'
						WHERE spec_id='.$sto['spec_id'].' and trade_id='.$sto['trade_id']
					);					
					if ($exist_num<=$less_num) {//添加缺少的数量
						$lack_num=$less_num-$exist_num;					
						$this->addStallsLessGoodsDetail($sto,$lack_num,$exist_num);
					}else{//删除多出的数量
						$surplus_num=$exist_num-$less_num;						
						for ($i=0; $i<$surplus_num ; $i++) { 
							$unique_code=$sto['trade_id'].'-'.$sto['spec_id'].'-'.($exist_num-$i);
							$sql_error_info='generateStallsLessGoodsDetail-delete_stalls_less_detail_surplus';
						    $less_goods_db->where('unique_code=\''.$unique_code.'\'')->delete(); 
						}	
					}
				}else{//新生成明细单	
					$add_result=$this->addStallsLessGoodsDetail($sto,$less_num,0);					
				}
				if ($update_result||$add_result) { $result['status']=true;}				
			}
			if($is_less_good==1){
				// 判断配置是否开启，并分配框（单件不分配，多件分配）
				$order_check_give_storting_box  = get_config_value('order_check_give_storting_box', 0);
				if($order_check_give_storting_box==1){//预分配
					$box_no=$this->allotSortingWall($trade_id,$trade_orders[0]['trade_no'],$trade_num['goods_count']);
				}
			}
			$result['box_no']=$box_no;
		}catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $result;
	}
	/**
	 * 添加档口缺货明细
	 * @param array $data
	 * @param integer $num
	 * @param integer $init_num
	 */
	public function addStallsLessGoodsDetail($data,$num=0,$init_num=0){
		$add_less_goods=array();
		$sql_error_info='';
		$less_goods_db=M('stalls_less_goods_detail');
		try{
			for ($i=0; $i<$num; $i++) { 
				$code=$init_num+$i+1;
				$add_less_goods[$i]['spec_id']=$data['spec_id'];
				$add_less_goods[$i]['trade_id']=$data['trade_id'];
				$add_less_goods[$i]['trade_no']=$data['trade_no'];
				$add_less_goods[$i]['warehouse_id']=$data['warehouse_id'];
				$add_less_goods[$i]['provider_id']=$data['provider_id'];	
				$add_less_goods[$i]['block_reason']=$data['block_reason'];	
				$add_less_goods[$i]['price']=$data['cost_price'];
				$add_less_goods[$i]['tax_price']=$data['cost_price'];
				$add_less_goods[$i]['tax_amount']=$data['cost_price'];	
				$add_less_goods[$i]['num']=1;
				$add_less_goods[$i]['unique_code']=$data['trade_id'].'-'.$data['spec_id'].'-'.$code;
			}
			$sql_error_info='addStallsLessGoodsDetail-add_stalls_less_detail';			
			$result=$less_goods_db->addAll($add_less_goods);
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $result;
	}
	/**
	 * 分配分拣框
	 * @param integer $goods_count
	 */
	public function  allotSortingWall($trade_id,$trade_no,$goods_count){
		$box_no='';
		$sorting_wall_detail_model = M('sorting_wall_detail');
		$big_box_goods_map_model = M('big_box_goods_map');
		$dynamic_box_model = M('cfg_dynamic_box');
		$sys_config =  get_config_value(array('dynamic_allocation_box'),array(0));
		//单品或没有货品不分配分拣框
		if($goods_count<=1){return $box_no;}
		// 分配框（单件不分配，多件分配）
		
		if($sys_config['dynamic_allocation_box']==1){
			$dynamic_box_info = $dynamic_box_model->field('wall_id,wall_no,box_num,goods_num')->where(array('type'=>1,'is_disabled'=>0))->order('wall_no')->select();
			$not_use_box = $sorting_wall_detail_model->where(array("is_use"=>0))->select();
			if(!empty($dynamic_box_info) && !empty($not_use_box)){
				$box_no = $not_use_box[0]['box_no'];
				$is_big_box = 0;
				$out_big_box_no = '';$out_sub_big_box_no = '';
				foreach($dynamic_box_info as $k=>$v){
					$valiable_box = $big_box_goods_map_model->field('box_no,big_box_no,sub_big_box_no,sum(goods_num) as num,count(big_box_no) as big_box_no_num')->where(array('big_box_no'=>array('like',$v['wall_no']."_%")))->group('big_box_no')->select();
					$big_box_no = '';$sub_big_box_no = '';
					$big_box_no_arr = array();
					if(empty($valiable_box)){
						$big_box_no = $v['wall_no']."-1"; // 'A-1';
						$sub_big_box_no = $v['wall_no']."-1-1";//'A-1-1'; 
					}else{
						foreach($valiable_box as $box){
							$arr_push_num = substr($box['big_box_no'],strrpos($box['big_box_no'],'-')+1);
							if(!in_array($arr_push_num,$big_box_no_arr,true)){
								$big_box_no_arr[]=$arr_push_num;
							}
							if($box['num']+$goods_count>$v['goods_num']){
								continue;
							}else{
								$big_box_no = $box['big_box_no'];
								//$sub_big_box_no = substr($box['sub_big_box_no'],strrpos($box['sub_big_box_no'],'-')+1)+1;
								$sub_big_box_no = $box['big_box_no_num']+1;
								$sub_big_box_no = $box['big_box_no'].'-'.$sub_big_box_no;
								break;
							}
						}
					}
					if($big_box_no==''||$sub_big_box_no==''){
						if($v['box_num'] <= count($valiable_box)){
							continue;
						}
						$tmp_num = 1;
						while(true){
							if(!in_array($tmp_num,$big_box_no_arr)){
								break;
							}
							$tmp_num++;
						}
						$big_box_no = $v['wall_no'].'-'.$tmp_num;
						$sub_big_box_no = $big_box_no.'-1';
						$out_big_box_no = $v['wall_no'].'-'.$tmp_num;
						$out_sub_big_box_no = $big_box_no.'-1';
						$wall_id = $v['wall_id'];
						if($v['goods_num'] < $goods_count){
							continue;
						}
						
					}
					$is_big_box = 1;
					$data = array('wall_id'=>$v['wall_id'],'box_no'=>$box_no,'big_box_no'=>$big_box_no,'sub_big_box_no'=>$sub_big_box_no,'trade_id'=>$trade_id,'trade_no'=>$trade_no,'goods_num'=>$goods_count);
					$big_box_goods_map_model->add($data);
					break;
				}
				if(!$is_big_box){
					$valiable_box_info = $big_box_goods_map_model->field('box_no')->group('big_box_no')->select();
					$dynamic_box_info = $dynamic_box_model->field('sum(box_num) as box_num')->where(array('type'=>1,'is_disabled'=>0))->find();
					if(count($valiable_box_info) >= $dynamic_box_info['box_num']){
						SE('分拣框不够用,请添加分拣框!');
					}
					$data = array('wall_id'=>$wall_id,'box_no'=>$box_no,'big_box_no'=>$out_big_box_no,'sub_big_box_no'=>$out_sub_big_box_no,'trade_id'=>$trade_id,'trade_no'=>$trade_no,'goods_num'=>$goods_count);
					$big_box_goods_map_model->add($data);
				}
				
			}
		}else{
			$sort_wall = D('Purchase/SortingWall')->where(array('type'=>1))->select();
			if (!empty($sort_wall)&&$goods_count>1) {
				$sort_wall_box = D('Purchase/SortingWall')->alias('sw')->field('swd.box_id')->join('inner join sorting_wall_detail swd on swd.wall_id = sw.wall_id')->where(array('swd.is_use'=>0,'sw.type'=>1,'is_disabled'=>0))->select();
				if(empty($sort_wall_box)){
					SE('分拣框不够用,请添加分拣框!');
				}
				foreach ($sort_wall as $wall){
					$envaliable_box = $sorting_wall_detail_model->where(array("wall_id"=>$wall['wall_id'],"is_use"=>0))->select();
					if(empty($envaliable_box)){
						continue;
					}else{
						$box_no = $envaliable_box[0]['box_no'];
						break;
					}
				}
				
			}
		}
		return $box_no;
	}
	/**
	 * 刷新库存信息
	 * @param object $db
	 * @param string $sql_error_info
	 * @param integer $trade_id
	 * @param integer $type
	 * @param integer $new_warehouse_id
	 * @param integer $old_warehouse_id
	 */
	public function refreshReserveStock($trade_id,$type,$new_warehouse_id,$old_warehouse_id)
	{//I_RESERVE_STOCK
		$trade_id=intval($trade_id);
		$type=intval($type);
		$new_warehouse_id=intval($new_warehouse_id);
		$old_warehouse_id=intval($old_warehouse_id);
		$sql_error_info='';
		try {
			if($old_warehouse_id!=0)
			{
				$sql_error_info='refreshReserveStock-stock_change_record';
				$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($old_warehouse_id)." WHERE sto.trade_id=".intval($trade_id).");");

				$sql_error_info='refreshReserveStock-stock_spec_add';
				$this->execute("INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created) (SELECT ".intval($old_warehouse_id).",spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0), IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW() FROM sales_trade_order WHERE trade_id=".intval($trade_id)." ORDER BY spec_id) ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num), sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num)");
	
				$sql_error_info='refreshReserveStock-sales_trade_order_update';
				$this->execute("UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=".$trade_id);
			}
			if($new_warehouse_id!=0)
			{
				switch ($type)
				{
					case 2://未付款库存
						$sql_error_info='refreshReserveStock-stock_change_record_2';
						$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,1,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待付款库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($new_warehouse_id)." WHERE sto.trade_id=".intval($trade_id).");");
						$sql_error_info='refreshReserveStock-type_2';
						$this->execute("INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num) (SELECT ".$new_warehouse_id.",spec_id,actual_num FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id) ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num)");
						$this->execute("UPDATE sales_trade_order SET stock_reserved=2 WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2");
						break;
					case 3://已保留待审核
						$sql_error_info='refreshReserveStock-stock_change_record_3';
						$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,2,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待审核库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($new_warehouse_id)." WHERE sto.trade_id=".intval($trade_id).");");
						$sql_error_info='refreshReserveStock-type_3';
						$this->execute("INSERT INTO stock_spec(warehouse_id,spec_id,order_num) (SELECT ".$new_warehouse_id.",spec_id,actual_num FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id) ON DUPLICATE KEY UPDATE order_num=order_num+VALUES(order_num)");
						$this->execute("UPDATE sales_trade_order SET stock_reserved=3 WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2");
						break;
					case 4://待发货
						$sql_error_info='refreshReserveStock-stock_change_record_4';
						$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,3,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用待发货库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($new_warehouse_id)." WHERE sto.trade_id=".intval($trade_id).");");
						$sql_error_info='refreshReserveStock-type_4';
						$this->execute("INSERT INTO stock_spec(warehouse_id,spec_id,sending_num,status) (SELECT ".$new_warehouse_id.",spec_id,actual_num,1 FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id) ON DUPLICATE KEY UPDATE sending_num=sending_num+VALUES(sending_num),status=1");
						$this->execute("UPDATE sales_trade_order SET stock_reserved=4 WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2");
						break;
					case 5://预订单库存
						$sql_error_info='refreshReserveStock-stock_change_record_5';
						$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,4,sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'占用预订单库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($new_warehouse_id)." WHERE sto.trade_id=".intval($trade_id).");");
						$sql_error_info='refreshReserveStock-type_5';
						$this->execute("INSERT INTO stock_spec(warehouse_id,spec_id,subscribe_num) (SELECT ".$new_warehouse_id.",spec_id,actual_num FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2 ORDER BY spec_id) ON DUPLICATE KEY UPDATE subscribe_num=subscribe_num+VALUES(subscribe_num)");
						$this->execute("UPDATE sales_trade_order SET stock_reserved=5 WHERE trade_id=".$trade_id." AND actual_num>0 AND stock_reserved<2");
						break;
				}
			}
			//更新平台货品库存变化
			$sql_error_info='refreshReserveStock-sys_process_background-sales_trade_order';
			$this->execute("INSERT INTO sys_process_background(type,object_id) SELECT 1,spec_id FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num>0");
			
			//组合装
			$sql_error_info='refreshReserveStock-sys_process_background-suite';
			$this->execute("INSERT INTO sys_process_background(type,object_id) SELECT 2,gs.suite_id FROM goods_suite gs, goods_suite_detail gsd,sales_trade_order sto WHERE sto.trade_id=".$trade_id." AND sto.actual_num>0 AND gs.suite_id=gsd.suite_id AND gsd.spec_id=sto.spec_id");
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
	/**
	 * 添加--订单( 添加原始单->原始子订单->订单->子订单)
	 * @param array $trade
	 * @param array $api_trade
	 * @param array $api_orders
	 * @param integer $user_id
	 */
	public function addTrade($trade,$api_trade,$api_orders,$user_id,$cfg_val=array())
	{
		$sql_error_info='';
		//添加方式: 0-手工建单,1-换货订单,2-补发订单,3-现款销售,4-后期添加
		$add_trade_type=set_default_value($trade['add_trade_type'], 0);
		unset($trade['add_trade_type']);
		$trade['created']=array('exp','NOW()');
		$api_trade['created']=array('exp','NOW()');
		$api_trade_id=0;
		try {
			//-------------原始单付款状态--------------
			if($api_trade['paid']>=$api_trade['receivable'])
			{
				$api_trade['pay_status']=2;//已付款
			}else if($api_trade['paid']<=0){
				$api_trade['pay_status']=0;//未付款
				$api_trade['pay_time']='1000-01-01 00:00:00';//付款时间置为0
			}else{
				$api_trade['pay_status']=1;//部分付款
			}
			//-------------生成原始单号---------------
			if (empty($api_trade['tid']))
			{
				$sql_error_info='addTrade-get_sys_no';
				$api_trade['tid']=get_sys_no('apitrade');
			}
			//-------------分摊--邮费--已付--------------
			$api_trade['order_count']=empty($api_trade['order_count'])?count($api_orders):$api_trade['order_count'];
			$left_paid=$api_trade['paid'];
			$left_post=$api_trade['post_amount'];
			$left_share=$api_trade['receivable']-$api_trade['post_amount'];
			for ($i=0;$i<$api_trade['order_count'];$i++)
			{
				$api_orders[$i]['tid']=$api_trade['tid'];
				$api_orders[$i]['oid']=get_sys_no('apiorder',1);
				//赠品--不需要--分摊
				if($api_orders[$i]['gift_type']==2)
				{
					//$api_orders[$i]['discount']=0;
					$api_orders[$i]['real_price']=0.0000;
					$api_orders[$i]['total_amount']=0.0000;
				}else if($api_orders[$i]['gift_type']==1)
				{
					SE('手工新建赠品必须是手工赠送');
				}
				//分摊--邮费--已付--(价格-新建换货订单)
				if ($i+1==$api_trade['order_count'])
				{
					$api_orders[$i]['share_post']=$left_post;
					$api_orders[$i]['paid']=$left_paid;
					if($add_trade_type==1||$add_trade_type==2)
					{
						$api_orders[$i]['share_amount']=$left_share;
					}
				}else {
					if($add_trade_type==0)
					{//手工建单
						$share_post=$api_orders[$i]['total_amount']*$api_trade['post_amount']/($api_trade['receivable']-$api_trade['post_amount']);
						$share_paid=($api_orders[$i]['total_amount']+$share_post)*$api_trade['paid']/$api_trade['receivable'];
						if ($left_paid>0){
							$share_paid=($api_orders[$i]['total_amount']+$share_post)<$left_paid?($api_orders[$i]['total_amount']+$share_post):$left_paid;
						}else{
							$share_paid=0;
						}
					}else if($add_trade_type==1||$add_trade_type==2){//换货订单
						if($api_trade['goods_amount']>0){
							$share_post=($api_orders[$i]['total_amount']/($api_trade['receivable']-$api_trade['post_amount']))*$api_trade['post_amount'];
							$share_paid=($api_orders[$i]['total_amount']/($api_trade['receivable']-$api_trade['post_amount']))*$api_trade['paid'];
							$share_amount=$share_paid-$share_post;
						}else{
							$share_post=($api_orders[$i]['num']/$api_trade['goods_count'])*$api_trade['post_amount'];
							$share_paid=($api_orders[$i]['num']/$api_trade['goods_count'])*$api_trade['paid'];
							$share_amount=$share_paid-$share_post;
						}
					}else if($add_trade_type==3){//现款销售
						$share_post=0;
						$share_paid=($api_orders[$i]['total_amount'])*$api_trade['paid']/$api_trade['receivable'];
						if ($left_paid>0){
							$share_paid=($api_orders[$i]['total_amount'])<$left_paid?($api_orders[$i]['total_amount']):$left_paid;
						}else{
							$share_paid=0;
						}
					}
					$api_orders[$i]['share_post']=$share_post;
					$api_orders[$i]['paid']=$share_paid;
					$left_post-=$share_post;
					$left_paid-=$share_paid;
					if($add_trade_type==1||$add_trade_type==2)
					{
						$api_orders[$i]['share_amount']=$share_amount;
						$left_share-=$share_amount;
					}
				}
			}
			//-------------添加原始单和原始子订单--------------
			$api_trade_db=D('Trade/ApiTrade');
			$api_trade_id=$api_trade_db->addApiTrade($api_trade);
			$api_order_db=D('Trade/ApiTradeOrder');
			$api_order_db->addApiTradeOrder($api_orders);
			//-------------整理订单数据并添加-----------------
			$trade['trade_no']=get_sys_no('sales',1);
			$trade['src_tids']=($add_trade_type==0?'':$api_trade['tid']);
			$trade['cs_remark_count']=empty($trade['cs_remark'])?0:1;
			$trade['warehouse_type']=0;//暂时默认为0 -- 审核生成出库单改变出库单的仓库类型
			if(!isset($trade['flag_id'])||$trade['flag_id']==0)
			{
				$trade['flag_id']=D('Setting/Flag')->getFlagId('手工建单',1);
			}
			$sql_error_info='addTrade-add_sales_trade';
			$trade_id=$this->add($trade);
			//-------------货品映射---------------------
			$sql_error_info='addTrade-map_trade_goods';
			$res_count_arr=D('Trade/Trade')->map_trade_goods($trade_id, $api_trade_id);
			if($res_count_arr['order_count']!=$trade['goods_type_count']||$res_count_arr['goods_count']!=(isset($trade['goods_count_round'])?$trade['goods_count_round']:$trade['goods_count']))
			{
				SE('订单货品数量不一致');
			}
			unset($trade['goods_count_round']);
			//-------------订单刷新---------------------
			$sql_error_info='addTrade-refresh_trade';
			$single_spec_no_code=get_config_value('single_spec_no_code',0);
			$this->execute("set @cfg_single_spec_no_code=$single_spec_no_code");
			if($add_trade_type==0){
				$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade_id.",2|".$cfg_val['open_package_strategy']."<<2|IF(".intval($trade_id['logistics_id']).",0,1),0)");
			}else if($add_trade_type==1){
				if(empty($trade['logistics_id']) ){
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade_id.",3|".$cfg_val['open_package_strategy']."<<2,0)");
				}else{
					$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade_id.",0,0)");
				}				
			}else if($add_trade_type==2){
				$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade_id.",3|".$cfg_val['open_package_strategy']."<<2,0)");
			}else if($add_trade_type==3){
				$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.",".$trade_id.",0,0)");
			}
			//-------------库存刷新---------------------
			if(intval($trade['warehouse_id'])>0)
			{
				$sql_error_info='addTrade-reserve_stock';
				$this->execute("CALL I_RESERVE_STOCK(".$trade_id.", 3, ".intval($trade['warehouse_id']).", 0)");
			}
			//-------------更新原始单------------------
			$api_order_db->updateApiTradeOrder(array('process_status'=>20,'modify_flag'=>0),array('platform_id'=>array('eq',0),'tid'=>array('eq',$api_trade['tid'])));
			//-------------添加日志---------------------
			$arr_msg_dict=array('手工新建订单','新建换货订单','新建补发订单','新建现款销售');
			$trade_log[]=array(
					'trade_id'=>$trade_id,
					'operator_id'=>$user_id,
					'type'=>1,
					'data'=>$api_trade_id,
					'message'=>$arr_msg_dict[$add_trade_type].':'.$api_trade['tid'],
					'created'=>date('y-m-d H:i:s',time()),
			);
			if($api_trade['pay_method']!=5&&$api_trade['paid']>0)
			{
				$trade_log[]=array(
						'trade_id'=>$trade_id,
						'operator_id'=>$user_id,
						'type'=>2,
						'data'=>$api_trade_id,
						'message'=>'客户付款:'.$api_trade['paid'].'-'.$api_trade['tid'],
						'created'=>date('y-m-d H:i:s',time()),
				);
			}
			$trade_log[]=array(
					'trade_id'=>$trade_id,
					'operator_id'=>$user_id,
					'type'=>3,
					'data'=>$api_trade_id,
					'message'=>'递交订单:'.$api_trade['tid'],
					'created'=>date('y-m-d H:i:s',time()),
			);
			D('Trade/SalesTradeLog')->addTradeLog($trade_log);
			//-------------更新原始单---------------------
			$arr_api_trade=array(
					'process_status'=>20,
					'deliver_trade_id'=>$trade_id,
					'x_salesman_id'=>($add_trade_type==0?$trade['salesman_id']:$user_id),
					'x_customer_id'=>$trade['customer_id'],
					'x_trade_flag'=>$trade['flag_id'],
					'modify_flag'=>0,
			);
			$api_trade_db->updateApiTrade($arr_api_trade,array('rec_id'=>array('eq',$api_trade_id)));
			//-------------更新标记同名未合并---------------------
			if(isset($cfg_val['order_check_warn_has_unmerge'])&&$cfg_val['order_check_warn_has_unmerge']!=0)
			{
				$this->updateWarnUnmerge($trade_id,$trade['customer_id']);
			}
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
		return $api_trade_id;
	}
	/**
	 * 货品映射
	 * @param number $trade_id
	 * @param number $api_trade_id
	 * @param number $type
	 * @param string $is_need
	 * @return Ambigous <multitype:NULL , \Think\mixed>
	 */
	public function map_trade_goods($trade_id,$api_trade_id,$type=0,$is_need=true)
	{
		$result=array();
		$model_db=M();
		$model_db->execute('CALL I_DL_MAP_TRADE_GOODS('.intval($trade_id).','.intval($api_trade_id).','.intval($type).',@order_count,@goods_count)');
		if($is_need)
		{
			$result=$model_db->query('SELECT @order_count AS order_count,@goods_count AS goods_count');
			$result['order_count']=$result[0]['order_count'];
			$result['goods_count']=$result[0]['goods_count'];
			unset($result[0]);
		}
		return $result;
	}

	/**
	 * 拆分--订单
	 * @param array $res_trade_arr
	 * @param array $arr_orders
	 * @param integer $user_id
	 */
	public function splitTradeCommon($trade_arr,$arr_orders,$user_id,$is_suite=0)
	{
		$is_rollback=false;
		$sql_error_info='';
		try {
			$is_rollback=true;
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$this->startTrans();
			$res_trade_id=$this->splitTradeCommonNoTrans($trade_arr,$arr_orders,$user_id,$is_suite);
			$this->commit();
		}catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}catch (\Exception $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}
		return $res_trade_id;
	}

	/**
	 * 拆分--订单--无事务--供调用
	 * @param array $res_trade_arr
	 * @param array $arr_orders
	 * @param integer $user_id
	 */
	public function splitTradeCommonNoTrans($trade_arr,$arr_orders,$user_id,$is_suite=0)
	{
		$sql_error_info='';
		try {
			$trade_no=$trade_arr['trade_no'];
			$trade_id=$trade_arr['trade_id'];
			unset($trade_arr['trade_id']);
			$trade_order_db=D('Trade/SalesTradeOrder');

			$sql_error_info='splitTrade-get_sys_no';
			//获取trade_no
			$trade_arr['trade_no']=get_sys_no('sales');
			//-------------添加新拆分的订单----------------------------
			//订单拆分--加上内置标记
			$flag_id = D('Setting/Flag')->getFlagId('拆分订单',1);
			$trade_arr['flag_id']= $flag_id;
			$sql_error_info="splitTrade-add_sales_trade";
			$res_trade_id=$this->add($trade_arr);
			//订单拆分--标记原订单split_from_trade_id
			$this->updateSalesTrade(array('split_from_trade_id'=>$trade_id,'flag_id'=>array("exp","IF(flag_id>1000,flag_id,{$flag_id})")), array('trade_id'=>array('eq',$trade_id)));
			$length=count($arr_orders);
			for ($i=0;$i<$length;$i++)
			{
				$arr_orders[$i]['trade_id']=$res_trade_id;
			}
			$sql_error_info="splitTrade-add_sales_trade_order";
			$trade_order_db->addSalesTradeOrder($arr_orders);
			//更新已付
			$sql_error_info='splitTrade-updata_sales_trade_order_1';
			$this->execute("UPDATE sales_trade_order SET delivery_term=IF(paid>=share_amount+share_post,1,delivery_term),paid=LEAST(paid,share_amount+share_post) WHERE trade_id=".$res_trade_id);
			//更新主订单的货品数量
			$sql_error_info='splitTrade-updata_sales_trade_order_2';
			$this->execute(
				"UPDATE sales_trade_order st1, sales_trade_order st2 SET st1.num=st1.num-st2.num, st1.actual_num=st1.actual_num-st2.actual_num,
					st1.discount=st1.discount-st2.discount, st1.share_amount=st1.share_amount-st2.share_amount, st1.share_post=st1.share_post-st2.share_post,
					st1.paid=st1.paid-st2.paid, st1.weight=st1.weight-st2.weight, st1.commission=st1.commission-st2.commission
					WHERE st1.trade_id=".$trade_id." AND st2.trade_id=".$res_trade_id." AND st1.platform_id=st2.platform_id
					AND st1.src_oid=st2.src_oid and st1.spec_id = st2.spec_id and st1.suite_no = st2.suite_no"
			);
			//删除数量为0的货品
			$sql_error_info='splitTrade-delete_sales_trade_order_1';
			$this->execute("DELETE FROM sales_trade_order WHERE trade_id=".$trade_id." AND actual_num=0 AND refund_status<3");
			$sql_error_info='splitTrade-delete_sales_trade_order_2';
			$this->execute("DELETE FROM sales_trade_order WHERE trade_id=".$res_trade_id." AND actual_num=0");
			$sql_error_info='splitTrade-query_sales_trade_order_2';
			$bind_order=$this->query(
				"SELECT sto1.bind_oid,sto1.is_print_suite FROM sales_trade_order sto1,sales_trade_order sto2
				    WHERE sto1.trade_id=".$trade_id." AND sto2.trade_id=".$res_trade_id." AND sto1.bind_oid<>''
					AND sto1.bind_oid=sto2.bind_oid AND sto1.actual_num>0 AND sto2.actual_num>0 LIMIT 1"
			);
			if($is_suite!=1&&!empty($bind_order))
			{
				//校验该关联子订单ID在系统中是否存在
				$bind_id=$this->query('SELECT rec_id FROM sales_trade_order WHERE src_oid=\'%s\'',$bind_order[0]['bind_oid']);
				if(!empty($bind_id)&&$bind_order[0]['is_print_suite']!=0)
				{//包含不可拆分组合装,子订单ID
					SE('包含不可拆分组合装,子订单ID'.$bind_order[0]['bind_oid']);
				}else if(!empty($bind_id))
				{//包含不可拆分子订单
					SE('包含不可拆分子订单:'.$bind_order[0]['bind_oid']);
				}
			}
			//获取配置值
			$res_cfg_val=get_config_value('open_package_strategy');
			//订单进行刷新
			$sql_error_info='splitTrade-split_trade_refresh_1';
			$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$trade_id.", IF(".$res_cfg_val.",4,0)|18, 0)");
			$sql_error_info='splitTrade-split_trade_refresh_2';
			$this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$res_trade_id.", IF(".$res_cfg_val.",4,0)|18, 0)");
			//更新订单的来源
			$sql_error_info='splitTrade-refresh_trade_from1';
			$new_trade_from1=$this->query('SELECT trade_from FROM api_trade ap
						WHERE ap.tid IN (SELECT src_tid FROM sales_trade_order sto WHERE sto.trade_id=%d AND gift_type=0 )',
				$trade_id);
			$trade_from1='';
			foreach ($new_trade_from1 as $k=>$v){
				if($v['trade_from']==1){
					$trade_from1=1;
					continue;
				}
				if($v['trade_from']==3){
					$trade_from1=3;
				}
			}
			$trade_from1==''?$trade_from1=2:true;
			$this->execute('UPDATE sales_trade SET trade_from=%d WHERE trade_id=%d',$trade_from1,$trade_id);
			$sql_error_info='splitTrade-refresh_trade_from2';
			$new_trade_from2=$this->query('SELECT trade_from FROM api_trade ap
						WHERE ap.tid IN (SELECT src_tid FROM sales_trade_order sto WHERE sto.trade_id=%d AND gift_type=0 )',
				$res_trade_id);
			$trade_from2='';
			foreach ($new_trade_from2 as $k=>$v){
				if($v['trade_from']==1){
					$trade_from2=1;
					continue;
				}
				if($v['trade_from']==3){
					$trade_from2=3;
				}
			}
			$trade_from2==''?$trade_from2=2:true;
			$this->execute('UPDATE sales_trade SET trade_from=%d WHERE trade_id=%d',$trade_from2,$res_trade_id);
			//获取下单和支付时间
			$trade_pay=$this->getSalesTrade('trade_time,pay_time',array('trade_id'=>array('eq',$res_trade_id)));
			$trade_log[]=array(
				'trade_id'=>$res_trade_id,
				'operator_id'=>$user_id,
				'type'=>1,
				'data'=>0,
				'message'=>'拆分订单：'.$trade_arr['trade_no'].',下单时间：'.$trade_pay['trade_time'],
				'created'=>date('y-m-d H:i:s',time()),
			);
			if(strtotime($trade_pay['pay_time'])>strtotime('1000-01-01'))
			{
				$trade_log[]=array(
					'trade_id'=>$res_trade_id,
					'operator_id'=>$user_id,
					'type'=>2,
					'data'=>0,
					'message'=>'拆分订单：'.$trade_arr['trade_no'].',付款时间：'.$trade_pay['pay_time'],
					'created'=>date('y-m-d H:i:s',time()),
				);
			}
			$trade_log[]=array(
				'trade_id'=>$res_trade_id,
				'operator_id'=>$user_id,
				'type'=>37,
				'data'=>1,
				'message'=>'从'.$trade_no.' 拆分订单 '.$trade_arr['trade_no'],
				'created'=>date('y-m-d H:i:s',time()),
			);
			$trade_log[]=array(
				'trade_id'=>$trade_id,
				'operator_id'=>$user_id,
				'type'=>37,
				'data'=>1,
				'message'=>$trade_no.' 拆分订单到 '.$trade_arr['trade_no'],
				'created'=>date('y-m-d H:i:s',time()),
			);
			D('Trade/SalesTradeLog')->addTradeLog($trade_log);
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			SE($e->getMessage());
		}catch (\Exception $e){
			SE($e->getMessage());
		}
		return $res_trade_id;
	}


}