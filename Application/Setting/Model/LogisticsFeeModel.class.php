<?php

namespace Setting\Model;

use Think\Model;

class LogisticsFeeModel extends Model
{
    protected $tableName = 'cfg_logistics_fee';
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
                array('first_weight',check_regex('double'),'非法首重！',1,'regex'),
                array('first_price',check_regex('double'),'非法首重资费！',1,'regex'),
                array('weight_step1',check_regex('double'),'非法重量区间1！',1,'regex'),
                array('weight_step2',check_regex('double'),'非法重量区间2！',1,'regex'),
                array('weight_step3',check_regex('double'),'非法重量区间3！',1,'regex'),
                array('weight_step4',check_regex('double'),'非法重量区间4！',1,'regex'),
                array('unit_step1',check_regex('double'),'非法续重单位1！',1,'regex'),
                array('unit_step2',check_regex('double'),'非法续重单位2！',1,'regex'),
                array('unit_step3',check_regex('double'),'非法续重单位3！',1,'regex'),
                array('unit_step4',check_regex('double'),'非法续重单位4！',1,'regex'),
                array('price_step1',check_regex('double'),'非法单位资费1！',1,'regex'),
                array('price_step2',check_regex('double'),'非法单位资费2！',1,'regex'),
                array('price_step3',check_regex('double'),'非法单位资费3！',1,'regex'),
    			array('price_step4',check_regex('double'),'非法单位资费4！',1,'regex'),
                array('special_weight1',check_regex('double'),'非法特殊重量区间1！',1,'regex'),
                array('special_weight2',check_regex('double'),'非法特殊重量区间2！',1,'regex'),
                array('special_weight3',check_regex('double'),'非法特殊重量区间3！',1,'regex'),
                array('special_fee1',check_regex('double'),'非法特殊区间1邮资！',1,'regex'),
                array('special_fee2',check_regex('double'),'非法特殊区间2邮资！',1,'regex'),
                array('special_fee3',check_regex('double'),'非法特殊区间3邮资！',1,'regex'),
                array('province','number','省市区不能为空！',1),
                array('city','number','省市区不能为空！',1),
                array('district','number','省市区不能为空！',1),
                array('trunc_mode',array(1,2,3,4),'取整方式未找到！',1,'in'),
                //回调--验证
                array('logistics_id','checkLogistics','无效物流！',1,'callback',1),
                
    	);
    	return $rules;
    }
    protected function checkLogistics($logistics_id)
    {
        return D('Setting/Logistics')->checkLogistics(intval($logistics_id));
    }
    public function validateLogisticsFee($postage)
    {
        try {
            if(!$this->validate($this->getRules())->create($postage))
            {
                SE($this->getError());
            }
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-validateLogisticsFee-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    /**
     *基本操作方法集
     */
    public function addLogisticsFee($data)
    {
    	try {
    		if (empty($data[0])) {
    			$res = $this->add($data);
    		}else
    		{
    			$res = $this->addAll($data);
    		}
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-addLogisticsFee-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
        return $res;
    }
    
    public function updateLogisticsFee($data,$where)
    {
    	try {
    		$res = $this->where($where)->save($data);
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-updateLogisticsFee-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
        return $res;
    }
    public function getLogisticsFee($fields,$where=array(),$alias='')
    {
        try {
            $res = $this->alias($alias)->field($fields)->where($where)->select();
        } catch (\PDOException $e) {
            \Think\Log::write($this->name.'-getLogisticsFee-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function getLogisticsFeeList($fields,$where=array(),$alias='',$join=array(),$order='')
    {
    	$res=array();
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->order($order)->select();
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getLogisticsFeeList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	return $res;
    }
    
    
    public function queryLogisticsFee(&$where_logistics_fee,$page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	//搜索表单-数据处理
    	$where='';
    	foreach ($search as $k=>$v){
    		if($v==='') continue;
    		switch ($k)
    		{   
    			case 'logistics_id':
    				set_search_form_value($where_logistics_fee, $k, $v,'lf_t', 2,' AND ');
    				break;
    		}
    	}
        $page=intval($page);
        $rows=intval($rows);
    	$limit = ($page - 1) * $rows . "," . $rows;//分页
    	$order = 'lf_t.'.$sort.' '.$order;
        $order = addslashes($order);
    	$logistics_fee_limit = 'SELECT lf_t.rec_id FROM cfg_logistics_fee lf_t ';
        $logistics_fee_total = 'SELECT COUNT(1) AS total FROM (SELECT rec_id FROM cfg_logistics_fee lf_t ';
        $flag = false;
        connect_where_str($where, $where_logistics_fee, $flag);
        $logistics_fee_limit .= $where . ' ORDER BY ' . $order;
        $logistics_fee_total .= $where . ' GROUP BY logistics_id,lf_t.first_weight, lf_t.first_price, lf_t.weight_step1, lf_t.unit_step1, lf_t.price_step1, lf_t.weight_step2, lf_t.unit_step2, lf_t.price_step2, lf_t.weight_step3, lf_t.unit_step3, lf_t.price_step3, lf_t.weight_step4, lf_t.unit_step4, lf_t.price_step4,lf_t.special_weight1 , lf_t.special_fee1 , lf_t.special_weight2 , lf_t.special_fee2 , lf_t.special_weight3 , lf_t.special_fee3 , lf_t.target_id ) t ';
        $logistics_fee_fields = 'SELECT GROUP_CONCAT(lf.rec_id) AS id, GROUP_CONCAT(lf.path) AS path, lf.target_id AS target, lf.level, cl.logistics_name AS logistics_id, lf.first_weight, lf.first_price, lf.weight_step1, lf.unit_step1, lf.price_step1, lf.weight_step2, lf.unit_step2, lf.price_step2, lf.weight_step3, lf.unit_step3, lf.price_step3, lf.weight_step4, lf.unit_step4, lf.price_step4,lf.special_weight1,lf.special_fee1,lf.special_weight2,lf.special_fee2,lf.special_weight3,lf.special_fee3,lf.modified,lf.created,IF(lf.target_id=0,"全部",cs.shop_name) AS target_id FROM cfg_logistics_fee lf ';
    	$sql = $logistics_fee_fields . 'INNER JOIN(' . $logistics_fee_limit . ') page ON page.rec_id=lf.rec_id LEFT JOIN cfg_logistics cl ON cl.logistics_id=lf.logistics_id LEFT JOIN cfg_shop cs ON cs.shop_id=lf.target_id  
    			GROUP BY logistics_id,lf.first_weight, lf.first_price, lf.weight_step1, lf.unit_step1, lf.price_step1, lf.weight_step2, lf.unit_step2, lf.price_step2, lf.weight_step3, lf.unit_step3, lf.price_step3, lf.weight_step4, lf.unit_step4, lf.price_step4, lf.special_weight1 , lf.special_fee1 , lf.special_weight2 , lf.special_fee2 , lf.special_weight3 , lf.special_fee3 ,lf.target_id  
    			ORDER BY lf.rec_id DESC LIMIT '.$limit;
        try 
    	{
    		$total=$this->query($logistics_fee_total);
    		$total=intval($total[0]['total']);
    		$list=$total?$this->query($sql):array();
    		$data=array('total'=>$total,'rows'=>$list);
    	}catch (\PDOException $e) 
    	{
			\Think\Log::write('search_logistics_fee:'.$e->getMessage());
			$data=array('total'=>0,'rows'=>array());
		}
		return $data;
    }
    
    public function editPostage($postage,$user_id)
    {
    	$error_info='';
    	try
    	{
            $res_postage=$this->getLogisticsFee('logistics_id',array('rec_id'=>array('eq',$postage['rec_id'])));
            $postage['logistics_id']=$res_postage[0]['logistics_id'];
//             $postage['path'].=$postage['logistics_id'];
            $res_postage=$this->getLogisticsFee('rec_id',array('rec_id'=>array('neq',$postage['rec_id']),'path'=>array('eq',$postage['path'])));
            if(!empty($res_postage['rec_id']))
            {
                SE('同一物流公司的同一收货地址只能新建一个策略');
            }
    		$this->startTrans();
            $this->updateLogisticsFee($postage);
            $log=array(
                'type'=>8,
                'operator_id'=>$user_id,
                'data'=>$postage['rec_id'],
                'message'=>'更新物流资费策略',
                'created'=>date('Y-m-d H:i:s',time())
            );
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e)
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-editPostage-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function addPostage($postage,$user_id)
    {
    	$error_info='';
    	try 
    	{
            $res_postage=$this->getLogisticsFee('rec_id',array('path'=>array('eq',$postage['path'])));
            $this->startTrans();
            //同一物流的同一地址只能新建一个策略，如果路径策略已存在，则更新改策略
            if(empty($res_postage))
            {
                $postage['created']=date('Y-m-d H:i:s',time());
                $postage_id=$this->addLogisticsFee($postage);
                $log=array(
                    'type'=>8,
                    'operator_id'=>$user_id,
                    'data'=>$postage_id,
                    'message'=>'新增物流资费策略',
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }else if($res_postage[0]['rec_id']>0)
            {
                $postage['rec_id']=$res_postage[0]['rec_id'];
                $this->updateLogisticsFee($postage);
                $log=array(
                    'type'=>8,
                    'operator_id'=>$user_id,
                    'data'=>$postage['rec_id'],
                    'message'=>'更新物流资费策略',
                    'created'=>date('Y-m-d H:i:s',time())
                );
            }
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-'.$error_info.'-addPostage-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    
    public function deletePostage($logistics_fee_id,$user_id)
    {
    	try
    	{
    		$this->startTrans();
            $this->where(array('rec_id'=>array('in',$logistics_fee_id)))->delete();
            $log=array(
                'type'=>8,
                'operator_id'=>$user_id,
                'data'=>$logistics_fee_id,
                'message'=>'删除物流资费策略',
                'created'=>date('Y-m-d H:i:s',time())
            );
            M('sys_other_log')->add($log);
            $this->commit();
    	} catch (\PDOException $e) 
    	{
    		$this->rollback();
    		\Think\Log::write($this->name.'-deletePostage-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }

    /**
     *根据策略计算邮资成本
     *
     */
    public function calculPostCost($stockout_id,$weight,$logistics_id=0)
    {
        $post_cost=0;
        try
        {
            $stockout_db=M('stockout_order');
            $stockout=$stockout_db->alias('so')
                        ->field('so.warehouse_id,st.shop_id,so.logistics_id,so.receiver_country,so.receiver_province,so.receiver_city,so.receiver_district')
                        ->join('left join sales_trade st on(st.trade_id=so.src_order_id and so.src_order_type=1)')
                        ->where(array('so.stockout_id'=>array('eq',$stockout_id)))
                        ->find();
            if(empty($stockout)) return $post_cost;
            $stockout['logistics_id']=$logistics_id==0?$stockout['logistics_id']:$logistics_id;
            $post_cost=$this->calculPostage($weight,$stockout['logistics_id'],$stockout['shop_id'],$stockout['warehouse_id'],$stockout['receiver_province'],$stockout['receiver_city'],$stockout['receiver_district']);
        } catch (\PDOException $e) 
        {
            $this->rollback();
            \Think\Log::write($this->name.'-calculPostCost-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $post_cost;
    }

    /**
     *根据策略计算邮资
     *
     */
    public function calculPostage($weight,$logistics_id,$shop_id,$warehouse_id,$province,$city,$district)
    {
        $postfee=0;
        try
        {
        	//如果有按照店铺设置的策略，优先执行按店铺执行的策略
        	$path_tmp1=$shop_id.'#';
        	$path_tmp2=$path_tmp1.','.$province;
        	$path_tmp3=$path_tmp2.','.$city;
        	$path_tmp4=$path_tmp3.','.$district;
        	$path_tmp1=$path_tmp1.',0,#'.$logistics_id;
        	$path_tmp2=$path_tmp2.',#'.$logistics_id;
        	$path_tmp3=$path_tmp3.',#'.$logistics_id;
        	$path_tmp4=$path_tmp4.',#'.$logistics_id;
        	$path=array(0=>$path_tmp1, 1=>$path_tmp2, 2=>$path_tmp3, 3=>$path_tmp4);
        	$postage=$this->field('first_weight,first_price,weight_step1,unit_step1,price_step1,
        	                       special_weight1,special_weight2,special_weight3,special_fee1,special_fee2,special_fee3,
                                   weight_step2,unit_step2,price_step2,weight_step3,unit_step3,
                                   price_step3,weight_step4,unit_step4,price_step4,trunc_mode')
        	              ->force('UK_cfg_logistics_fee_path')
        	              ->where(array('path'=>array('in',$path)))
        	              ->order('level DESC')
        	              ->find();
        	if(empty($postage)){
        		//$logistics_match_mode=get_config_value('logistics_match_mode',0);
                //如果$postage为空，则没有搜到对应店铺的邮资策略，应该搜全部店铺的邮资策略，和I_DL_DECIDE_LOGISTICS_FEE存储过程统一了
                $shop_id=0;//0表示匹配所有店铺
        		$path_tmp1=$shop_id.'#';
        		$path_tmp2=$path_tmp1.','.$province;
        		$path_tmp3=$path_tmp2.','.$city;
        		$path_tmp4=$path_tmp3.','.$district;
        		$path_tmp1=$path_tmp1.',0,#'.$logistics_id;
        		$path_tmp2=$path_tmp2.',#'.$logistics_id;
        		$path_tmp3=$path_tmp3.',#'.$logistics_id;
        		$path_tmp4=$path_tmp4.',#'.$logistics_id;
        		$path=array(0=>$path_tmp1, 1=>$path_tmp2, 2=>$path_tmp3, 3=>$path_tmp4);
        		$postage=$this->field('first_weight,first_price,weight_step1,unit_step1,price_step1,
        		                   special_weight1,special_weight2,special_weight3,special_fee1,special_fee2,special_fee3,
                                   weight_step2,unit_step2,price_step2,weight_step3,unit_step3,
                                   price_step3,weight_step4,unit_step4,price_step4,trunc_mode')
        		              ->force('UK_cfg_logistics_fee_path')
        		              ->where(array('path'=>array('in',$path)))
        		              ->order('level DESC')
        		              ->find();
        	}
            if(empty($postage) || $postage['first_weight']<=0 || $postage['first_price']<=0)
            {
                return $postfee;
            }
            //特殊重量区间逻辑
            if($postage['special_weight1']>0&&$postage['special_fee1']>0&&$weight<=$postage['special_weight1'])
            {
                return $postage['special_fee1'];
            }
            if($postage['special_weight2']>0&&$postage['special_fee2']>0&&$weight<=$postage['special_weight2'])
            {
                return $postage['special_fee2'];
            }
            if($postage['special_weight3']>0&&$postage['special_fee3']>0&&$weight<=$postage['special_weight3'])
            {
                return $postage['special_fee3'];
            }
            //首重和重量区间逻辑
            $postfee=floatval($postage['first_price']);
            if($weight<=$postage['first_weight']||$postage['first_weight']<=0)
            {
                return sprintf("%.2f",$postfee);
            }
            $postfee+=$this->calculSubPostage($weight,$postage['first_weight'],$postage['weight_step1'],$postage['unit_step1'],$postage['price_step1'],$postage['trunc_mode']);
            if($weight<=$postage['weight_step1']||$postage['weight_step1']<=0)
            {
                return sprintf("%.2f",$postfee);
            }
            $postfee+=$this->calculSubPostage($weight,$postage['weight_step1'],$postage['weight_step2'],$postage['unit_step2'],$postage['price_step2'],$postage['trunc_mode']);
            if($weight<=$postage['weight_step2']||$postage['weight_step2']<=0)
            {
                return sprintf("%.2f",$postfee);
            }
            $postfee+=$this->calculSubPostage($weight,$postage['weight_step2'],$postage['weight_step3'],$postage['unit_step3'],$postage['price_step3'],$postage['trunc_mode']);
            if($weight<=$postage['weight_step3']||$postage['weight_step3']<=0)
            {
                return sprintf("%.2f",$postfee);
            }
            $postfee+=$this->calculSubPostage($weight,$postage['weight_step3'],0,$postage['unit_step4'],$postage['price_step4'],$postage['trunc_mode']);
        } catch (\PDOException $e) 
        {
            $this->rollback();
            \Think\Log::write($this->name.'-calculPostCost-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
        return sprintf("%.2f",$postfee);
    }

    /**
     *公式计算邮资
     *
     */
    private function calculSubPostage($weight,$pre_weight_step,$weight_step,$unit_step,$price_step,$mode=1)
    {
        if($unit_step<=0)
        {
            return 0;
        }
        $incl_weight=0;
        if($weight_step>0 && $weight>$weight_step)
        {
            $incl_weight=$weight_step-$pre_weight_step;
        }else
        {
            $incl_weight=$weight-$pre_weight_step;
        }
        $postage=0;
        switch ($mode) {
            case 2://向下取整
                $postage=bcmul(floor(bcdiv($incl_weight,$unit_step)),$price_step);
                break;
            case 3://四舍五入
                $postage=bcmul(round(bcdiv($incl_weight,$unit_step)),$price_step);
                break;
            case 4://按比例
                $postage=bcmul(bcdiv($incl_weight,$unit_step),$price_step);
                break;
            default://向上取整
                $postage=bcmul(ceil(bcdiv($incl_weight,$unit_step)),$price_step);
                break;
        }
        return floatval(sprintf("%.4f",$postage));
    }

}