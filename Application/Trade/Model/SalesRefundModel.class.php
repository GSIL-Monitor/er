<?php
namespace Trade\Model;

use Think\Model;

class SalesRefundModel extends Model
{
    protected $tableName = 'sales_refund';
    protected $pk        = 'refund_id';
    
    /**
     * 退换单验证
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
    			array('shop_id','number','无效店铺！',0), //默认情况下用正则进行验证
    			array('type',C('refund_type'),'退换单类型未找到！',1,'in'), //
    			array('flow_type',C('flow_type'),'金额流向未找到！',0,'in'), //
    			array('pay_method',C('pay_method'),'支付方式未找到！',1,'in',1),
    			array('swap_mobile',check_regex('mobile'),'请填写正确的手机号码！',2,'regex'),
    			array('swap_telno',check_regex('mobile_tel'),'请填写正确的固话！',2,'regex'),
    			array('swap_province','number','省市区不能为空！',0),
    			array('swap_city','number','省市区不能为空！',0),
    			array('swap_district','number','省市区不能为空！',0),
    			array('swap_receiver','require','收货人姓名不能为空！',0),
    			array('swap_address','require','收货地址不能为空！',0),
    			array('goods_refund_count','1,1000','退货货品不能为0！',1,'length'),
    			array('goods_return_count','1,1000','换出货品不能为0！',0,'length'),
    
    			array('goods_amount',check_regex('double'),'退货金额格式不正确！',0,'regex'),
    			array('refund_amount',check_regex('double'),'退款金额格式不正确！',0,'regex'),
    			array('return_amount',check_regex('double'),'换出金额格式不正确！',0,'regex'),
    			array('guarante_refund_amount',check_regex('double'),'平台退款格式不正确！',0,'regex'),
    			array('direct_refund_amount',check_regex('double'),'线下退款格式不正确！',0,'regex'),
    			//回调--验证
    			array('flag_id','checkFlag','无效标记！',0,'callback'),
    			array('shop_id','checkShop','无效店铺！',0,'callback'),
    			array('logistics_id','checkLogistics','无效物流！',0,'callback'),
    			array('warehouse_id','checkWarehouse','退货入库-无效仓库！',0,'callback'),
    			array('swap_warehouse_id','checkSwapWarehouse','换出货品-无效仓库！',0,'callback'),
    	);
    	return $rules;
    }
    protected function checkFlag($flag_id)
    {
    	return D('Setting/Flag')->checkFlag($flag_id,9);
    }
    protected function checkShop($shop_id)
    {
    	return D('Setting/Shop')->checkShop(intval($shop_id));
    }
    protected function checkWarehouse($warehouse_id)
    {
    	$result=false;
    	if(intval($warehouse_id)==0){
    		$result=true;
    	}else{
    		$result=D('Setting/Warehouse')->checkWarehouse(intval($warehouse_id));
    	}
    	return $result;
    }
    protected function checkSwapWarehouse($swap_warehouse_id)
    {
    	return D('Setting/Warehouse')->checkWarehouse(intval($swap_warehouse_id));
    }
    protected function checkLogistics($logistics_id)
    {
    	$result=false;
    	if(intval($logistics_id)==0){
    		$result=true;
    	}else{
    		$result=D('Setting/Logistics')->checkLogistics(intval($logistics_id));
    	}
    	return $result;
    }
    public function validateRefund($refund)
    {
        try {
            if(!$this->validate($this->getRules())->create($refund))
            {
                SE($this->getError());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-validateRefund-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    } 
    /**
     * 退换单搜索
     * @param unknown $where_sales_refund_str
     * @param number $page
     * @param number $rows
     * @param unknown $search
     * @param string $sort
     * @param string $order
     * @param string $type
     * @return Ambigous <multitype:number multitype: , multitype:number Ambigous <multitype:, \Think\mixed> >
     */
    public function queryRefund(&$where_sales_refund_str,$page=1, $rows=20, $search = array(), $sort = 'refund_id', $order = 'desc',$type='manage')
    {
    	$page=intval($page);
        $rows=intval($rows);
        //搜索表单-数据处理
    	$where_goods_goods_str='';
    	$where_goods_spec_str='';
        D('Trade/SalesRefund')->searchFormDeal($where_sales_refund_str,$where_goods_goods_str,$where_goods_spec_str,$search);
    	$limit=($page - 1) * $rows . "," . $rows;//分页
    	$order = 'sr_1.'.$sort.' '.$order;//排序
        $order = addslashes($order);
    	$sql_sel='SELECT sr_1.refund_id FROM sales_refund sr_1 ';
    	$sql_total='SELECT COUNT(1) AS total FROM sales_refund sr_1 ';
    	$flag=false;
    	$sql_where='';
    	if(!empty($where_goods_goods_str)||!empty($where_goods_spec_str))
    	{
    		$sql_where.=' LEFT JOIN sales_refund_order sro ON sro.refund_id=sr_1.refund_id LEFT JOIN goods_spec gs ON gs.spec_id=sro.spec_id LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id ';
    	}
    	connect_where_str($sql_where, $where_sales_refund_str, $flag);
    	connect_where_str($sql_where, $where_goods_goods_str, $flag);
    	connect_where_str($sql_where, $where_goods_spec_str, $flag);
        if($search['show_disabled']!=1){
            //是否显示取消的退款单
            $sql_where = $sql_where.' AND sr_1.process_status!=10';
         }
    	$sql_sel.=$sql_where;
    	$sql_total.=$sql_where;
    	//得到该条件下的分页的sql
    	$sql_limit=$sql_sel.' ORDER BY '.$order.' LIMIT '.$limit;
    	$cfg_show_telno=get_config_value('show_number_to_star',1);
    	switch ($type){
    		case 'manage':
    			$sql_fields_str='SELECT sr_2.refund_id AS id, sr_2.flag_id, sr_2.refund_no, sr_2.type,sh.shop_name AS shop_id, sr_2.platform_id, he.fullname AS operator_id, sr_2.src_no, sr_2.process_status, sr_2.status, wh.name AS warehouse_id, IF(sr_2.warehouse_type=0,\'\',sr_2.warehouse_type) warehouse_type, sr_2.wms_status, sr_2.wms_result, sr_2.outer_no, sr_2.tid, sr_2.trade_no, sr_2.buyer_nick, sr_2.receiver_name, sr_2.swap_area, IF('.$cfg_show_telno.'=0,sr_2.return_mobile,INSERT( sr_2.return_mobile,4,4,\'****\')) return_mobile, IF('.$cfg_show_telno.'=0,sr_2.return_telno,INSERT( sr_2.return_telno,4,4,\'****\')) return_telno, sr_2.receiver_address, sr_2.pay_account, sr_2.goods_amount, sr_2.post_amount, sr_2.refund_amount, sr_2.guarante_refund_amount, sr_2.direct_refund_amount, sr_2.exchange_amount, sr_2.logistics_name, sr_2.logistics_no, sr_2.return_address, sr_2.created, sr_2.refund_time, sr_2.from_type, cor.title AS reason_id,cor1.title AS revert_reason, sr_2.sync_result, sr_2.remark, sr_2.stockin_pre_no,sr_2.outer_no,sr_2.wms_result FROM sales_refund sr_2 ';//sr_2.return_goods_count,
    			$sql_left_join_str=' LEFT JOIN cfg_shop sh ON sh.shop_id=sr_2.shop_id LEFT JOIN hr_employee he ON he.employee_id=sr_2.operator_id LEFT JOIN cfg_warehouse wh ON wh.warehouse_id=sr_2.warehouse_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=sr_2.reason_id LEFT JOIN cfg_oper_reason cor1 ON cor1.reason_id=sr_2.revert_reason ';
    			break;
    			/* case 'add':
    				$sql_fields_str='';
    				$sql_left_join_str='';
    				break; */
    	}
    	$sql=$sql_fields_str.'INNER JOIN('.$sql_limit.')sr_3 ON sr_3.refund_id=sr_2.refund_id '.$sql_left_join_str.' ORDER BY sr_2.refund_id DESC';
    	$data=array();
    	try {
    		$res_total=$this->query($sql_total);
    		$total=intval($res_total[0]['total']);
    		$list=$total?$this->query($sql):array();
    		$data=array('total'=>$total,'rows'=>$list);
    	} catch (\PDOException $e) {
    		\Think\Log::write($e->getMessage());
    		$data=array('total'=>0,'rows'=>array());
    	}
    	return $data;
    }
    //查询条件单独提取整理
    public function searchFormDeal(&$where_sales_refund_str,&$where_goods_goods_str,&$where_goods_spec_str,$search){
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
        D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        $search['warehouse_id'].=',0';
        foreach ($search as $k=>$v){
            if($v==='') continue;
            switch ($k){
                case 'tid':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'trade_no':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'warehouse_id':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'buyer_nick':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'receiver_name':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'type':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'status':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'process_status':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'refund_no':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'logistics_no':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ');
                    break;
                case 'return_mobile':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',1,' AND ( ');
                    set_search_form_value($where_sales_refund_str, 'return_telno', $v,'sr_1',1,' OR ',' ) ');
                    break;
                case 'shop_id'://
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'receiver_address':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',6,' AND ');
                    break;
                case 'flag_id':
                    set_search_form_value($where_sales_refund_str, $k, $v,'sr_1',2,' AND ');
                    break;
                case 'goods_name':
                    set_search_form_value($where_goods_goods_str, $k, $v,'gg',6,' AND ');
                    break;
                case 'goods_no':
                    set_search_form_value($where_goods_goods_str, $k, $v,'gg',1,' AND ');
                    break;
                case 'spec_no':
                    set_search_form_value($where_goods_spec_str, $k, $v,'gs',1,' AND ');
                    break;
            }
        }
    }

    /**
     * 退换单相关的数据库的基本操作
     * */
    public function addSalesRefund($data)
    {
        try {
            if (empty($data[0])) {
                $res = $this->add($data);
            }else
            {
                $res = $this->addAll($data);
            }
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-addSalesRefund-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
    public function updateSalesRefund($data,$where)
    {
        try {
            $res = $this->where($where)->save($data);
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-updateSalesRefund-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    public function getSalesRefund($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefund-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function getSalesRefundOnLock($fields,$where)
    {
    	try {
    		$res = $this->field($fields)->lock(true)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundOnLock-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function getSalesRefundList($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function getSalesRefundListOnLock($fields,$where,$alias='',$join=array())
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->lock(true)->join($join)->where($where)->select();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSalesRefundListOnLock-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    /**
     * 退换单功能操作(被调用)
     * */
    
    /**
     * 更新实发数量，回收库存--后期调整到库存stock_spec的Model中
     * @param integer $warehouse_id
     * @param integer $sto_id
     */
    public function returnStockSpec($warehouse_id,$sto_id)
    {
    	try {
            //$this->execute("INSERT INTO stock_change_record (trade_id,spec_id,warehouse_id,operator_type,num,unpay_num,order_num,sending_num,subscribe_num,message) (SELECT sto.trade_id,sto.spec_id,ss.warehouse_id,(sto.stock_reserved-1),sto.actual_num,ss.unpay_num,ss.order_num,ss.sending_num,ss.subscribe_num,'退款，释放库存' FROM sales_trade_order sto LEFT JOIN stock_spec ss ON ss.spec_id=sto.spec_id AND ss.warehouse_id=".intval($warehouse_id)." WHERE sto.stock_reserved>=2 and sto.rec_id=".$sto_id.");");
	    	$this->execute(
	    			'INSERT INTO stock_spec(warehouse_id,spec_id,unpay_num,order_num,sending_num,subscribe_num,created)
					(SELECT '.intval($warehouse_id).',spec_id,IF(stock_reserved=2,-actual_num,0),IF(stock_reserved=3,-actual_num,0), IF(stock_reserved=4,-actual_num,0),IF(stock_reserved=5,-actual_num,0),NOW() FROM sales_trade_order WHERE rec_id='.intval($sto_id).' ORDER BY spec_id)
					ON DUPLICATE KEY UPDATE unpay_num=unpay_num+VALUES(unpay_num),order_num=order_num+VALUES(order_num),sending_num=sending_num+VALUES(sending_num),subscribe_num=subscribe_num+VALUES(subscribe_num)'
	    	);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-returnStockSpec-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
	
	public function cancelWmsRefund($id){
		try{
			$data['status'] = 0;
			$data['info'] = '取消成功';
			$operator_id = get_operator_id();
			$where = array('refund_id'=>$id);
			$result=$this->fetchsql(false)->field('process_status')->where($where)->select();
			if($result[0]['process_status'] != 65){
				SE('退换单状态不正确,只能取消推送成功的退换单');
			}
			$this->startTrans();
			$update_status=array('process_status'=>63);
			$updata_refund = $this->where($where)->save($update_status);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用退换model失败';
		}catch(\BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = $e->getMessage();
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$data['status'] = 1;
			$data['info'] = '调用退换model失败';
		}
		return $data;
	}
	
}

?>