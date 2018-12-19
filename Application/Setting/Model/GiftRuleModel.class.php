<?php

namespace Setting\Model;

use Think\Model;
use Common\Common\UtilDB;

class GiftRuleModel extends Model
{
    protected $tableName = 'cfg_gift_rule';
    protected $pk = 'rec_id';
    
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
    			//必须验证数据
    			array('rule_name','require','规则名称不能为空！',1), //默认情况下用正则进行验证
    			array('rule_group','number','分组请填写整数！',1),
    			array('rule_priority',array(1,2,3,4,5),'非法优先级！',1,'in'),
    			array('is_disabled',array(0,1),'停用数据非法！',1,'in'),
    			array('rule_type','number','非法规则条件！',1),
    			array('limit_gift_stock','number','非法赠品库存数量！',1),
    			//存在字段就验证
    			array('time_type',array(1,2,3),'非法时间类型！',0,'in'), 
    			array('start_time',check_regex('time'),'请填写正确的时间！',0,'regex'),
    			array('end_time',check_regex('time'),'请填写正确的时间！',0,'regex'),
    			array('min_goods_count','number','货品总数请填写整数！',0),
    			array('max_goods_count','number','货品总数请填写整数！',0),
    			array('min_specify_count','number','指定货品请填写整数！',0),
    			array('max_specify_count','number','指定货品请填写整数！',0),
    			array('min_goods_amount',check_regex('double'),'非法货品总款！',0,'regex'),
    			array('max_goods_amount',check_regex('double'),'非法货品总款！',0,'regex'),
    			array('min_specify_amount',check_regex('double'),'非法指定货品金额！',0,'regex'),
    			array('max_specify_amount',check_regex('double'),'非法指定货品金额！',0,'regex'),
    			array('min_receivable',check_regex('double'),'非法订单实收金额！',0,'regex'),
    			array('max_receivable',check_regex('double'),'非法订单实收金额！',0,'regex'),
    			array('specify_count','number','指定货品请填写整数！',0),
    			array('limit_specify_count','number','倍赠最大数量请填写整数！',0),
    			//回调--验证
    			array('rule_no','checkRuleNo','规则编号已存在！',0,'callback'),
    	);
    	return $rules;
    }
    protected function checkRuleNo($rule_no)
    {
    	$check=true;
    	try {
    		$rule=$this->field('rec_id')->where(array('rule_no'=>array('eq',$rule_no)))->find();
    		$check= empty($rule)?true:false;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-checkRuleNo-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	return $check;
    }
    public function validateRule($rule)
    {
        try {
            if(!$this->validate($this->getRules())->create($rule))
            {
                SE($this->getError());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-validateRule-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    /**
     *基本操作方法集
     */
    public function addGiftRule($data)
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
    		\Think\Log::write($this->name.'-addGiftRule-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function updateGiftRule($data,$where)
    {
    	try {
    		$res = $this->where($where)->save($data);
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-updateGiftRule-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function getGiftRuleList($fields,$where=array(),$alias='',$join=array(),$order='')
    {
    	$res=array();
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getGiftRuleList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	return $res;
    }
    
    public function getAttendGoods($rule_id,$goods_type)
    {
    	$attends=array();
    	try {
    		$attends=$this->query('
    				(SELECT gag.rec_id AS ag_id,gs.spec_id AS id, gs.spec_no AS merchant_no, gg.goods_no, gg.goods_name, gs.spec_name, gs.spec_code,gag.is_suite
					FROM goods_spec gs LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id LEFT JOIN cfg_gift_attend_goods gag ON gag.spec_id=gs.spec_id
					WHERE gag.goods_type=%d  AND gag.is_suite=0 AND gag.rule_id=%d)UNION
					(SELECT gag.rec_id AS ag_id,gs.suite_id AS id, gs.suite_no AS goods_no, gs.suite_no AS merchant_no, gs.suite_name AS goods_name, \'\' AS  spec_name, \'\' AS spec_code,gag.is_suite
					FROM goods_suite gs LEFT JOIN cfg_gift_attend_goods gag ON gag.spec_id=gs.suite_id
					WHERE gag.goods_type=%d  AND gag.is_suite=1 AND gag.rule_id=%d)',
    				array($goods_type,$rule_id,$goods_type,$rule_id)
    		);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getAttendGoods-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	return $attends;
    }
    
    public function getSendGoods($rule_id)
    {
    	$attends=array();
    	try {
    		$attends=$this->query('
    				(SELECT gsg.rec_id AS sg_id, gs.spec_id AS id, gs.spec_no AS merchant_no, gg.goods_no, gg.goods_name, gs.spec_name, gs.spec_code,gsg.gift_num,gsg.is_suite,gsg.gift_group 
					FROM goods_spec gs LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id LEFT JOIN cfg_gift_send_goods gsg ON gsg.spec_id=gs.spec_id
					WHERE gsg.is_suite=0 AND gsg.rule_id=%d)UNION
					(SELECT gsg.rec_id AS sg_id, gs.suite_id AS id, gs.suite_no AS goods_no, gs.suite_no AS merchant_no, gs.suite_name AS goods_name, \'\' AS  spec_name, \'\' AS spec_code,gsg.gift_num,gsg.is_suite,gsg.gift_group 
					FROM goods_suite gs LEFT JOIN cfg_gift_send_goods gsg ON gsg.spec_id=gs.suite_id
					WHERE gsg.is_suite=1 AND gsg.rule_id=%d)',
    				array($rule_id,$rule_id)
    		);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getSendGoods-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	return $attends;
    }
    
    public function getGiftRule($fields,$where=array(),$alias='')
    {
    	try {
    		$res = $this->alias($alias)->field($fields)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getGiftRule-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function getDialogView($dialog,$id_list=null)
    {
    	$datagrid=array();
    	switch($dialog){
    		case 'range':
    		case 'amount':
    		case 'multiple':
    			$datagrid=array(
    					'id'=>$id_list['id_datagrid'],
    					'style'=>'',
    					'class'=>'easyui-datagrid',
    					'options'=> array(
    							'title'=>'',
    							'toolbar' => "#{$id_list['toolbar']}",
    							'pagination'=>false,
    							'fitColumns'=>true,
    							'fit'=>true,
    					),
    					'fields' =>get_field('GiftRule','goods_list'),
    			);
    			break;
    		case 'gift':
    			$datagrid=array(
    					'id'=>$id_list['id_datagrid'],
    					'style'=>'',
    					'class'=>'easyui-datagrid',
    					'options'=> array(
    							'title'=>'',
    							'toolbar' => "#{$id_list['toolbar']}",
    							'pagination'=>false,
    							'fitColumns'=>true,
    							'fit'=>true,
    							//'methods'=>'onEndEdit:endEditReturnOrder,onBeginEdit:beginEditReturnOrder',
    					),
    					'fields' =>get_field('GiftRule','gift_list'),
    			);
    			break;
    	}
    	return $datagrid;
    }
    
    public function queryGiftRules(&$where_gift_rule,$page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	//搜索表单-数据处理
    	$where_attend_goods='';//参与活动的货品
    	$where_gift_goods='';//赠品
    	$where='';
    	foreach ($search as $k=>$v){
    		if($v==='') continue;
    		switch ($k)
    		{   
    			case 'rule_no':
    				set_search_form_value($where_gift_rule, $k, $v,'gr_t', 1,' AND ');
    				break;
    			case 'rule_name':
    				set_search_form_value($where_gift_rule, $k, $v,'gr_t', 6,' AND ');
    				break;
    			case 'status':
    				switch ($v){
    					case '0'://未开始
    						set_search_form_value($where_gift_rule, 'start_time', date('Y-m-d H:i:s',time()),'gr_t', 4,' AND ',' >= ');
    						break;
    					case '1'://执行中
    						$time=date('Y-m-d H:i:s',time());
    						set_search_form_value($where_gift_rule, 'start_time', $time,'gr_t', 4,' AND ',' <= ');
    						set_search_form_value($where_gift_rule, 'end_time', $time,'gr_t', 4,' AND ',' >= ');
    						break;
    					case '2'://已结束
    						set_search_form_value($where_gift_rule, 'end_time', date('Y-m-d H:i:s',time()),'gr_t', 4,' AND ',' <= ');
    						break;
    				}
    				break;
    			case 'shop_list':
    				$v=intval($v);
    				$where_gift_rule.=$v==0?'':' AND FIND_IN_SET('.$v.',gr_t.shop_list) ';
    				break;
    			case 'start_time':
    				set_search_form_value($where_gift_rule, $k, $v,'gr_t', 4,' AND ',' >= ');
    				break;
    			case 'end_time':
    				set_search_form_value($where_gift_rule, $k, $v,'gr_t', 4,' AND ',' <= ');
    				break;
    			case 'is_disabled':
    				set_search_form_value($where_gift_rule, $k, $v,'gr_t', 2,' AND ');
    				break;
    			case 'goods_no':
    				set_search_form_value($where_attend_goods, $k, $v,'gg1', 1,' AND ');
    				$where.=' left join cfg_gift_attend_goods cgag1 on gr_t.rec_id = cgag1.rule_id left join goods_spec gs1 on (gs1.spec_id = cgag1.spec_id and cgag1.is_suite=0) left join goods_goods gg1 on gg1.goods_id = gs1.goods_id ';
    				break;
    			case 'merchant_no':
    				set_search_form_value($where_attend_goods, $k, $v,'gmn1', 1,' AND ');
    				$where.=' left join cfg_gift_attend_goods cgag2 on gr_t.rec_id = cgag2.rule_id left join goods_merchant_no gmn1 on cgag2.spec_id = gmn1.target_id ';
    				break;
    			case 'gift_goods_no':
    				set_search_form_value($where_gift_goods, 'goods_no', $v,'gg2', 1,' AND ');
    				$where.=' left join cfg_gift_send_goods cgsg1 on gr_t.rec_id = cgsg1.rule_id left join goods_spec gs2 on (gs2.spec_id = cgsg1.spec_id and cgsg1.is_suite=0) left join goods_goods gg2 on gg2.goods_id = gs2.goods_id ';
                    break;
                case 'gift_merchant_no':
                    set_search_form_value($where_gift_goods, 'merchant_no', $v,'gmn2', 1,' AND ');
                    $where.=' left join cfg_gift_send_goods cgsg2 on gr_t.rec_id = cgsg2.rule_id left join goods_merchant_no gmn2 on cgsg2.spec_id = gmn2.target_id ';
    				break;
    		}
    	}
        $page=intval($page);
        $rows=intval($rows);
    	$limit = ($page - 1) * $rows . "," . $rows;//分页
    	$order = 'gr_t.'.$sort.' '.$order;
        $order = addslashes($order);
    	$gift_rule_limit = 'SELECT gr_t.rec_id FROM cfg_gift_rule gr_t ';
    	$gift_rule_total = 'SELECT COUNT(1) AS total FROM cfg_gift_rule gr_t ';
    	$flag = false;
    	$order_limit = ' ORDER BY ' . $order . ' LIMIT ' . $limit;
    	connect_where_str($where, $where_attend_goods, $flag);
    	connect_where_str($where, $where_gift_goods, $flag);
    	connect_where_str($where, $where_gift_rule, $flag);
    	if(!empty($where_attend_goods) || !empty($where_gift_goods))
    	{
    		$where.= ' GROUP BY gr_t.rec_id ';
    	}
    	$gift_rule_limit.= $where;
    	$gift_rule_limit.= $order_limit;
    	$gift_rule_total.= $where;
    	
    	$gift_rule_fields = ' SELECT  cgr.rec_id AS id,cgr.rule_no,cgr.rule_name,cgr.time_type,cgr.start_time,cgr.end_time,cgr.rule_priority,cgr.rule_group,cgr.remark,cgr.is_disabled,cgr.created,cgr.modified FROM cfg_gift_rule cgr ';
    	$sql = $gift_rule_fields . 'INNER JOIN(' . $gift_rule_limit . ') page ON page.rec_id=cgr.rec_id ORDER BY cgr.rec_id DESC';
    	
    	try 
    	{
    		$total=$this->query($gift_rule_total);
    		$total=intval($total[0]['total']);
    		$list=$total?$this->query($sql):array();
    		$data=array('total'=>$total,'rows'=>$list);
    	}catch (\PDOException $e) 
    	{
			\Think\Log::write('search_gift_rule:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
    }
    
    public function editRule($rule,$send_goods,$attend_goods,$user_id)
    {
    	$error_info='';
    	try
    	{
    		$logs=array();
    		$this->startTrans();
    		//更新赠品策略
    		unset($rule['rule_no']);//不更新赠品规则编号
    		$rule['shop_list']=set_default_value($rule['shop_list'], '');
    		$rule['rule_group']=set_default_value($rule['rule_group'], 0);
    		$rule['rule_priority']=set_default_value($rule['rule_priority'], 1);
    		$rule['remark']=set_default_value($rule['remark'], '');
    		$rule['is_disabled']=set_default_value($rule['is_disabled'], 0);
    		$rule['is_enough_gift']=set_default_value($rule['is_enough_gift'], 0);
    		$res_rule=$this->getGiftRule('rule_no,rule_name,rule_group,rule_priority,rule_type,remark,is_enough_gift,limit_gift_stock,is_disabled,
    									time_type,start_time,end_time,shop_list,min_goods_count,max_goods_count,min_specify_count,max_specify_count,
    									specify_count,limit_specify_count,min_receivable,max_receivable,min_specify_amount,max_specify_amount',
    									array('rec_id'=>array('eq',$rule['rec_id'])));
    		$res_flag=$this->updateGiftRule($rule);
    		$bool_message=array(0=>'否',1=>'是');
    		$message='更新赠品策略：'.$res_rule['rule_no'];
    		if($res_flag>0){
    			if($res_rule['rule_name']!=$rule['rule_name'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--名称：从“'.$res_rule['rule_name'].'”到“'.$rule['rule_name'].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['rule_group']!=$rule['rule_group'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--分组：从“'.$res_rule['rule_group'].'”到“'.$rule['rule_group'].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['rule_priority']!=$rule['rule_priority'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--优先级：从“'.$res_rule['rule_priority'].'”到“'.$rule['rule_priority'].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['remark']!=$rule['remark'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--备注：从“'.$res_rule['remark'].'”到“'.$rule['remark'].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['is_enough_gift']!=$rule['is_enough_gift'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--是否校验库存：从“'.$bool_message[$res_rule['is_enough_gift']].'”到“'.$bool_message[$rule['is_enough_gift']].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['limit_gift_stock']!=$rule['limit_gift_stock'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--赠品库存底线值：从“'.$res_rule['limit_gift_stock'].'”到“'.$rule['limit_gift_stock'].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($res_rule['is_disabled']!=$rule['is_disabled'])
    			{
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--停用：从“'.$bool_message[$res_rule['is_disabled']].'”到“'.$bool_message[$rule['is_disabled']].'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			$new_type='';//记录新的策略类型
    			$type_array=array();
    			$rule_type_name=C('gift_rule_type_name');
    			foreach ($rule_type_name as $k=>$v){
    				if($rule['rule_type'] & 1<<intval($v))
    				{
    					$new_type.=$k.' ';
    					$type_array[$v]=$k;
    				}
    			}
    			if($res_rule['rule_type']!=$rule['rule_type'])
    			{
    				$old_type='';//记录原有策略类型
    				foreach ($rule_type_name as $k => $v)
    				{
    					if($res_rule['rule_type'] & 1<<intval($v))
    					{
    						$old_type.=$k.' ';
    					}
    				}
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--规则条件：从“'.$old_type.'”到“'.$new_type.'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($type_array[4]&&($res_rule['shop_list']!=$rule['shop_list']))//店铺变化
    			{
    				$new_shop=$this->getShopName($rule['shop_list']);
    				$old_shop=$this->getShopName($res_rule['shop_list']);
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.'--店铺：从“'.$old_shop.'”到“'.$new_shop.'”',
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($type_array[3]&&($rule['time_type']!=$res_rule['time_type']||$rule['start_time']!=$res_rule['start_time']||$rule['end_time']!=$res_rule['end_time']))
    			{//策略有效期变化
    				$time_message='--策略有效期：';
    				if($rule['time_type']!=$res_rule['time_type'])
    				{
    					$type=array(1=>'付款时间',3=>'下单时间');
    					$time_message.='时间类型从“'.$type[$res_rule['time_type']].'”到“'.$type[$rule['time_type']].'”；';
    				}
    				if($rule['start_time']!=$res_rule['start_time'])
    				{
    					$time_message.='开始时间从“'.$res_rule['start_time'].'”到“'.$rule['start_time'].'”；';
    				}
    				if($rule['end_time']!=$res_rule['end_time'])
    				{
    					$time_message.='结束时间从“'.$res_rule['end_time'].'”到“'.$rule['end_time'].'”；';
    				}
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.$time_message,
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($type_array[7]&&($rule['min_goods_count']!=$res_rule['min_goods_count']||$rule['max_goods_count']!=$res_rule['max_goods_count']))
    			{//货品总数
    				$goods_amount_message='--货品总数：';
    				if($rule['min_goods_count']!=$res_rule['min_goods_count'])
    				{
    					$goods_amount_message.='最小值从“'.$res_rule['min_goods_count'].'”到“'.$rule['min_goods_count'].'”；';
    				}
    				if($rule['max_goods_count']!=$res_rule['max_goods_count'])
    				{
    					$goods_amount_message.='最大值从“'.$res_rule['max_goods_count'].'”到“'.$rule['max_goods_count'].'”；';
    				}
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.$goods_amount_message,
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($type_array[9]&&($rule['min_specify_count']!=$res_rule['min_specify_count']||$rule['max_specify_count']!=$res_rule['max_specify_count']))
    			{//指定货品数量
    				$specify_count_message='--指定货品数量：';
    				if($rule['min_specify_count']!=$res_rule['min_specify_count'])
    				{
    					$specify_count_message.='最小值从“'.$res_rule['min_specify_count'].'”到“'.$rule['min_specify_count'].'”；';
    				}
    				if($rule['max_specify_count']!=$res_rule['max_specify_count'])
    				{
    					$specify_count_message.='最大值从“'.$res_rule['max_specify_count'].'”到“'.$rule['max_specify_count'].'”；';
    				}
    				$logs[]=array(
    						'type'=>6,
    						'operator_id'=>$user_id,
    						'data'=>$rule['rec_id'],
    						'message'=>$message.$specify_count_message,
    						'created'=>date('Y-m-d H:i:s',time()),
    				);
    			}
    			if($type_array[21]&&($rule['min_specify_amount']!=$res_rule['min_specify_amount']||$rule['max_specify_amount']!=$res_rule['max_specify_amount']))
    			{//指定货品金额
	    			$specify_amount_message='--指定货品金额：';
	    			if($rule['min_specify_amount']!=$res_rule['min_specify_amount'])
	    			{
	    				$specify_amount_message.='最小值从“'.$res_rule['min_specify_amount'].'”到“'.$rule['min_specify_amount'].'”；';
	    			}
	    			if($rule['max_specify_amount']!=$res_rule['max_specify_amount'])
	    			{
	    				$specify_amount_message.='最大值从“'.$res_rule['max_specify_amount'].'”到“'.$rule['max_specify_amount'].'”；';
	    			}
	    			$logs[]=array(
	    					'type'=>6,
	    					'operator_id'=>$user_id,
	    					'data'=>$rule['rec_id'],
	    					'message'=>$message.$specify_amount_message,
	    					'created'=>date('Y-m-d H:i:s',time()),
	    			);
    			}
    			if($type_array[16]&&($rule['min_receivable']!=$res_rule['min_receivable']||$rule['max_receivable']!=$res_rule['max_receivable']))
    			{//订单实收金额
	    			$receivable_message='--订单实收金额：';
	    			if($rule['min_receivable']!=$res_rule['min_receivable'])
	    			{
	    				$receivable_message.='最小值从“'.$res_rule['min_receivable'].'”到“'.$rule['min_receivable'].'”；';
	    			}
	    			if($rule['max_receivable']!=$res_rule['max_receivable'])
	    			{
	    				$receivable_message.='最大值从“'.$res_rule['max_receivable'].'”到“'.$rule['max_receivable'].'”；';
	    			}
	    			$logs[]=array(
	    					'type'=>6,
	    					'operator_id'=>$user_id,
	    					'data'=>$rule['rec_id'],
	    					'message'=>$message.$receivable_message,
	    					'created'=>date('Y-m-d H:i:s',time()),
	    			);
    			}
    			if($type_array[12]&&($rule['specify_count']!=$res_rule['specify_count']||$rule['limit_specify_count']!=$res_rule['limit_specify_count']))
    			{//指定货品倍增
	    			$specify_multiple_message='--指定货品倍增：';
	    			if($rule['specify_count']!=$res_rule['specify_count'])
	    			{
	    				$specify_multiple_message.='倍增值从“'.$res_rule['specify_count'].'”到“'.$rule['specify_count'].'”；';
	    			}
	    			if($rule['limit_specify_count']!=$res_rule['limit_specify_count'])
	    			{
	    				$specify_multiple_message.='最大倍增次数从“'.$res_rule['limit_specify_count'].'”到“'.$rule['limit_specify_count'].'”；';
	    			}
	    			$logs[]=array(
	    					'type'=>6,
	    					'operator_id'=>$user_id,
	    					'data'=>$rule['rec_id'],
	    					'message'=>$message.$specify_multiple_message,
	    					'created'=>date('Y-m-d H:i:s',time()),
	    			);
    			}
    		}
    		$attend_type=array(1=>'指定货品数量',2=>'指定货品倍增',3=>'指定货品金额');
    		//删除参加活动货品
            $attends_delete='';
            foreach ($attend_goods['delete'] as $v)
            {
                $attends_delete.=intval($v['ag_id']).',';
                $logs[]=array(
                		'type'=>6,
                		'operator_id'=>$user_id,
                		'data'=>$rule['rec_id'],
                		'message'=>$message.'--删除“'.$attend_type[$v['goods_type']].'”货品，商家编码:'.$v['merchant_no'],
                		'created'=>date('Y-m-d H:i:s',time()),
                );
            }
            if(!empty($attends_delete))
            {
                $error_info='delete_attend_goods';
                $this->execute('DELETE FROM cfg_gift_attend_goods WHERE rec_id IN (%s) AND rule_id=%d',array(substr($attends_delete,0,-1),$rule['rec_id']));
            }
    		//添加参加活动货品
    		$attends=array();
    		foreach ($attend_goods['insert'] as $v)
    		{
    			$attends[]=array(
    					'rule_id'=>$rule['rec_id'],
    					'goods_type'=>$v['goods_type'],
    					'spec_id'=>$v['id'],
    					'is_suite'=>set_default_value($v['is_suite'], 0),
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    			$logs[]=array(
    					'type'=>6,
    					'operator_id'=>$user_id,
    					'data'=>$rule['rec_id'],
    					'message'=>$message.'--添加“'.$attend_type[$v['goods_type']].'”货品，商家编码:'.$v['merchant_no'],
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    		}
    		$error_info='add_attend_goods';
    		M('cfg_gift_attend_goods')->addAll($attends);
            //删除赠品
            $sends_delete='';
            foreach ($send_goods['delete'] as $v)
            {
                $sends_delete.=intval($v['sg_id']).',';
                $logs[]=array(
                		'type'=>6,
                		'operator_id'=>$user_id,
                		'data'=>$rule['rec_id'],
                		'message'=>$message.'--删除赠品，商家编码:'.$v['merchant_no'],
                		'created'=>date('Y-m-d H:i:s',time()),
                );
            }
            if(!empty($sends_delete))
            {
                $error_info='delete_send_goods';
                $this->execute('DELETE FROM cfg_gift_send_goods WHERE rec_id IN (%s) AND rule_id=%d',array(substr($sends_delete,0,-1),$rule['rec_id']));
            }
    		//添加赠品
    		$gifts=array();
    		foreach ($send_goods['insert'] as $v)
    		{
    			$gifts[]=array(
    					'rule_id'=>$rule['rec_id'],
    					'priority'=>11,
    					'spec_id'=>$v['id'],
                        'gift_group'=>set_default_value($v['gift_group'], 0),
    					'gift_num'=>set_default_value($v['gift_num'],1),
    					'is_suite'=>set_default_value($v['is_suite'], 0),
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    			$logs[]=array(
    					'type'=>6,
    					'operator_id'=>$user_id,
    					'data'=>$rule['rec_id'],
    					'message'=>$message.'--添加赠品，商家编码:'.$v['merchant_no'],
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    		}
    		$error_info='add_send_goods';
    		M('cfg_gift_send_goods')->addAll($gifts);
    		//更新赠品
    		foreach ($send_goods['update'] as $v)
    		{
    			// 判断是更新了数量还是更新了分组
                $gift_send_goods=M("cfg_gift_send_goods")->where('rec_id='.$v['sg_id'])->find();
                if ($gift_send_goods['gift_num']!=$v['gift_num']) {
                    $logs[]=array(
                        'type'=>6,
                        'operator_id'=>$user_id,
                        'data'=>$rule['rec_id'],
                        'message'=>$message.'--更新赠品，商家编码:'.$v['merchant_no'].',数量更新为'.$v['gift_num'],
                        'created'=>date('Y-m-d H:i:s',time()),
                    );
                }
                if ($gift_send_goods['gift_group']!=$v['gift_group']) {
                    $logs[]=array(
                        'type'=>6,
                        'operator_id'=>$user_id,
                        'data'=>$rule['rec_id'],
                        'message'=>$message.'--更新赠品，商家编码:'.$v['merchant_no'].',分组更新为'.$v['gift_num'],
                        'created'=>date('Y-m-d H:i:s',time()),
                    );
                }
                M('cfg_gift_send_goods')->save(array('rec_id'=>$v['sg_id'],'gift_num'=>$v['gift_num'],'gift_group'=>set_default_value($v['gift_group'], 0)));
            }
    		//添加日志
    		$error_info='add_sys_other_log';
    		M('sys_other_log')->addAll($logs);
    		$this->commit();
    	} catch (\PDOException $e)
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-addRule-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function addRule($rule,$send_goods,$attend_goods,$user_id)
    {
    	$error_info='';
    	try 
    	{
    		if(empty($send_goods))
    		{
    			SE('请选择赠品');
    		}
			$logs=array();
    		$this->startTrans();
    		//添加赠品策略
    		$rule['rule_no']=empty($rule['rule_no'])?get_sys_no('gift_rule'):$rule['rule_no'];
    		$rule['shop_list']=set_default_value($rule['shop_list'], '');
    		$rule['rule_group']=set_default_value($rule['rule_group'], 0);
    		$rule['rule_priority']=set_default_value($rule['rule_priority'], 1);
    		$rule['is_enough_gift']=set_default_value($rule['is_enough_gift'], 0);//赠品库存充足时才送
    		$rule['limit_gift_stock']=set_default_value($rule['limit_gift_stock'], 0);//赠品库存的底线值
    		$rule['remark']=set_default_value($rule['remark'], '');
    		$rule['buyer_rank']=set_default_value($rule['buyer_rank'], '');//表字段没有设置默认值
    		$rule['terminal_type']=set_default_value($rule['terminal_type'], 1);//表字段没有设置默认值
    		$rule['is_disabled']=set_default_value($rule['is_disabled'], 0);
    		$rule['created']=array('exp','NOW()');
    		$rule['gift_is_random']=set_default_value($rule['gift_is_random'], 0);
    		$rule_id=$this->addGiftRule($rule);
    		$logs[]=array(
    				'type'=>6,
    				'operator_id'=>$user_id,
    				'data'=>$rule_id,
    				'message'=>'新建赠品策略：'.$rule['rule_no'],
    				'created'=>date('Y-m-d H:i:s',time()),
    		);
    		//添加参加活动货品
    		$attends=array();
    		foreach ($attend_goods['add'] as $v)
    		{
    			$attends[]=array(
    					'rule_id'=>$rule_id,
    					'goods_type'=>$v['goods_type'],
    					'spec_id'=>$v['id'],
    					'is_suite'=>set_default_value($v['is_suite'], 0),
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    		}
    		$error_info='add_attend_goods';
    		M('cfg_gift_attend_goods')->addAll($attends);
    		//添加赠品
    		$gifts=array();
    		foreach ($send_goods as $v)
    		{
    			$gifts[]=array(
    					'rule_id'=>$rule_id,
    					'priority'=>11,
    					'spec_id'=>$v['id'],
    					'gift_group'=>set_default_value($v['gift_group'], 0),
    					'gift_num'=>set_default_value($v['gift_num'],1),
    					'is_suite'=>set_default_value($v['is_suite'], 0),
    					'created'=>date('Y-m-d H:i:s',time()),
    			);
    		}
    		$error_info='add_send_goods';
    		M('cfg_gift_send_goods')->addAll($gifts);
    		//添加日志
    		$error_info='add_sys_other_log';
    		M('sys_other_log')->addAll($logs);
    		$this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-addRule-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    function deleteRule($rule_id,$user_id)
    {
    	try
    	{
            $sql_error_info='';
    		$this->startTrans();
    		$rule=$this->getGiftRule('rule_no',array('rec_id'=>array('eq',$rule_id)));
            if (empty($rule)) { 
                SE('该策略不存在,可能已被其他人删除');
             }
            $sql_error_info='deleteRule-delete_cfg_gift_attend_goods';
    		$this->execute('DELETE FROM cfg_gift_attend_goods WHERE rule_id=%d',array($rule_id));
            $sql_error_info='deleteRule-delete_cfg_gift_send_goods';
    		$this->execute('DELETE FROM cfg_gift_send_goods WHERE rule_id=%d',array($rule_id));
            $sql_error_info='deleteRule-delete_cfg_gift_rule';
    		$this->execute('DELETE FROM cfg_gift_rule WHERE rec_id=%d',array($rule_id));
    		M('sys_other_log')->add(array('type'=>6,'operator_id'=>$user_id,'data'=>$rule_id,'message'=>'删除赠品策略：'.$rule['rule_no'],'created'=>array('exp','NOW()')));
    		$this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$sql_error_info.'-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    private function getShopName($shop_id)
    {
    	$shop_name='';
    	$shop_name_arr=array();
    	$shop_id_arr=explode(',', $shop_id);
    	$shop_list=UtilDB::getCfgList(array('shop'));
    	foreach ($shop_list['shop'] as $v)
    	{
    		$shop_name_arr[$v['id']]=$v['name'];
    	}
    	foreach ($shop_id_arr as $v)
    	{
    		if($shop_name_arr[$v])
    		{
    			$shop_name.=$shop_name_arr[$v].' ';
    		}
    	}
    	return $shop_name;
    }
}