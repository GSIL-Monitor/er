<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;


class GiftRuleController extends BaseController
{
    public function getGiftRuleList($page=1, $rows=20, $search = array(), $sort = 'rec_id', $order = 'desc')
    {
    	if(IS_POST)
    	{
    		$where_gift_rule='';//不同初始化搜索条件
			$data=D('GiftRule')->queryGiftRules($where_gift_rule,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
    	}else 
    	{
    		$id_list=array(
    				'toolbar'=>'gift_rule_toobbar',
    				'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
    				'form'=>'gift_rule_form',
    				'edit'=>'gift_rule_edit',
    				'add'=>'gift_rule_add',
    				'more_button'=>'gift_rule_more_button',
    				'more_content'=>'gift_rule_more_content',
    				'hidden_flag'=>'gift_rule_hidden_flag',
    				'set_flag'=>'gift_rule_set_flag',
    				'search_flag'=>'gift_rule_search_flag',
    		);
    		$datagrid = array(
    				'id'=>$id_list['id_datagrid'],
    				'style'=>'',
    				'class'=>'',
    				'options'=> array(
    						'title' => '',
    						'url'   =>U('GiftRule/getGiftRuleList', array('grid'=>'datagrid')),
    						'toolbar' =>"#{$id_list['toolbar']}",
    						'fitColumns'=>false,
    						'singleSelect'=>false,
    						'ctrlSelect'=>true,
    				),
    				'fields' => get_field('GiftRule','gift_rule')
    		);
    		$params=array(
    				'datagrid'=>array('id'=>$id_list['id_datagrid']),
    				'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
    				'edit'=>array('id'=>$id_list['edit'],'url'=>U('GiftRule/editGiftRule'),'title'=>'赠品策略编辑','height'=>560,'width'=>875),
    				'add'=>array('id'=>$id_list['edit'],'url'=>U('GiftRule/editGiftRule'),'title'=>'新建赠品策略','height'=>560,'width'=>875),
    				'delete'=>array('url'=>U('GiftRule/deleteGiftRule')),
    		);
    		$list_form=UtilDB::getCfgList(array('shop'));
    		$faq_url=C('faq_url');
    		$this->assign('faq_url',$faq_url['gift_rule']);
    		$this->assign("list",$list_form);
    		$this->assign("params",json_encode($params));
    		$this->assign("id_list",$id_list);
    		$this->assign('datagrid', $datagrid);
    		$this->display('show');
    	}
    }
    
    public function editGiftRule($id=0)
    {
    	 $id=intval($id);
    	 if(IS_POST)
    	 {
    	 	$user_id=get_operator_id();
    	 	$rule=I('post.info','',C('JSON_FILTER'));
    	 	$send_goods=I('post.send_goods','',C('JSON_FILTER'));
    	 	$attend_goods=I('post.attend_goods','',C('JSON_FILTER'));
    	 	if($id>0)
    	 	{
    	 		$rule['rec_id']=$id;
    	 	}
    	 	$gift_rule_db=D('GiftRule');
    	 	try 
    	 	{
    	 		if(!isset($rule['time_type'])||!isset($rule['shop_list']))
    	 		{
    	 			SE('赠品策略必须设置店铺和策略有效期');
    	 		}
    	 		$rule_types=C('gift_rule_type');
    	 		$rule_type=0;
    	 		foreach ($rule['gift_rules'] as $k)
    	 		{
    	 			$rule_type+=(1<<intval($rule_types[$k]));
    	 		}
    	 		$rule['rule_type']=$rule_type;
    	 		unset($rule['gift_rules']);
    	 		$rule['is_enough_gift']=set_default_value($rule['is_enough_gift'], 0);
    	 		$rule['limit_gift_stock']=set_default_value($rule['limit_gift_stock'], 0);
    	 		$rule['gift_is_random']=set_default_value($rule['gift_is_random'], 0);
                $rule['is_specify_sum']=set_default_value($rule['is_specify_sum'], 0);
                $gift_rule_db->validateRule($rule);
    	 		/*if(!$gift_rule_db->validate($gift_rule_db->getRules())->create($rule))
    	 		{
    	 			$this->error($gift_rule_db->getError());
    	 		}*/
    	 		if($id>0)
    	 		{//编辑
    	 			$gift_rule_db->editRule($rule,$send_goods,$attend_goods,$user_id);
    	 		}else
    	 		{//新增
    	 			$gift_rule_db->addRule($rule,$send_goods,$attend_goods,$user_id);
    	 		}
    	 	}catch (BusinessLogicException $e) {
    	 		$this->error($e->getMessage());
    	 	}
    	 	$this->success();
    	 }else 
    	 {
    	 	$id_list=array(
    	 			'tab_container'=>'gift_rule_edit_tab_container',
    	 			'form_id'=>'gift_rule_edit_form',
    	 			'add'=>'gift_rule_add',
    	 	);
    	 	$arr_tabs=array(
    	 			array('id'=>$id_list['tab_container'],'url'=>U('GiftRule/editGiftRuleTabs',array('tabs'=>'goods_range')).'?tab=goods_range&pre=range','title'=>'指定货品数量列表'),
    	 			array('id'=>$id_list['tab_container'],'url'=>U('GiftRule/editGiftRuleTabs',array('tabs'=>'goods_amount')).'?tab=goods_amount&pre=amount','title'=>'指定货品金额列表'),
    	 			array('id'=>$id_list['tab_container'],'url'=>U('GiftRule/editGiftRuleTabs',array('tabs'=>'goods_multiple')).'?tab=goods_multiple&pre=multiple','title'=>'指定货品倍增列表'),
    	 			array('id'=>$id_list['tab_container'],'url'=>U('GiftRule/editGiftRuleTabs',array('tabs'=>'gift_list')).'?tab=gift_list&pre=gift','title'=>'赠品列表'),
    	 	);
    	 	$rule=array('id'=>$id,'gift_rules'=>array('shop_list','time_type'));
    	 	if($id>0)
    	 	{
    	 		$rule=D('GiftRule')->getGiftRule(
    	 				'rec_id AS id,rule_no,rule_name,rule_type,rule_group,rule_priority,is_enough_gift,limit_gift_stock,is_disabled,remark,time_type,start_time,end_time,shop_list,min_goods_count,max_goods_count,min_specify_count,max_specify_count,min_goods_amount,max_goods_amount,min_specify_amount,max_specify_amount,min_receivable,max_receivable,specify_count,is_specify_sum,limit_specify_count,gift_is_random,goods_key_word,spec_key_word,csremark_key_word',
    	 				array('rec_id'=>array('eq',$id))
    	 		);
    	 		$rule_types=C('gift_rule_type');
    	 		$gift_rule=array();
    	 		foreach ($rule_types as $k => $v)
    	 		{
    	 			if($rule['rule_type'] & 1<<intval($v))
    	 			{
    	 				$gift_rule[]=$k;
    	 			}
    	 		}
    	 		$rule['gift_rules']=$gift_rule;
    	 		$rule['tab_range_url']=U('GiftRule/getRuleTabData');//.'?tab=range&type=1&id='.$id;
    	 		$rule['tab_amount_url']=U('GiftRule/getRuleTabData');//.'?tab=amount&type=3&id='.$id;
    	 		$rule['tab_multiple_url']=U('GiftRule/getRuleTabData');//.'?tab=multiple&type=2&id='.$id;
    	 		$rule['tab_gift_url']=U('GiftRule/getRuleTabData');//.'?tab=gift&id='.$id;
    	 	}
    	 	
    	 	$list_form = UtilDB::getCfgList(array('shop'));
    	 	$list_form['shop'] = json_encode($list_form['shop']);
            $faq_url=C('faq_url');
            $this->assign('faq_url',$faq_url['gift_rule']);
    	 	$this->assign("today",date('Y-m-d',time()).' 00:00:00');
    	 	$this->assign("list",$list_form);
    	 	$this->assign("rule",json_encode($rule));
    	 	$this->assign("id_list",$id_list);
    	 	$this->assign('arr_tabs', json_encode($arr_tabs));
    	 	$this->display('dialog_rule_add_edit');
    	 }
    }
    
    public function editGiftRuleTabs($tab,$pre)
    {
    	$tabs=array(
    			'goods_range'=>'tabs_goods_range',
    			'goods_amount'=>'tabs_goods_amount',
    			'goods_multiple'=>'tabs_goods_multiple',
    			'gift_list'=>'tabs_gift_list'
    	);
    	if(empty($tabs[$tab]))
    	{
    		return false;
    	}
    	$id_list=array(
    			'toolbar'=>'gift_rule_toolbar_'.$pre,
    			'id_datagrid'=>strtolower('gift_rule_datagrid_'.$pre),
    			'add'=>'gift_rule_add',
    	);
    	$gift_rule_db=D('GiftRule');
    	$datagrid=$gift_rule_db->getDialogView($pre,$id_list);
    	$this->assign("id_list",$id_list);
    	$this->assign('datagrid', $datagrid);
    	$this->display($tabs[$tab]);
    }
    
    public function getRuleTabData($tab,$id)
    {
    	$id=intval($id);
    	$type=intval(I('post.type'));
    	$data=array();
    	switch ($tab)
    	{
    		case 'range':
    			$data['rows']=D('GiftRule')->getAttendGoods($id,$type);
    			$data['total']=count($data['rows']);
    			break;
    		case 'amount':
    			$data['rows']=D('GiftRule')->getAttendGoods($id,$type);
    			$data['total']=count($data['rows']);
    			break;
    		case 'multiple':
    			$data['rows']=D('GiftRule')->getAttendGoods($id,$type);
    			$data['total']=count($data['rows']);
    			break;
    		case 'gift':
    			$data['rows']=D('GiftRule')->getSendGoods($id);
    			$data['total']=count($data['rows']);
    			break;
    	}
    	$this->ajaxReturn($data);
    }
    
    public function deleteGiftRule($id)
    {
    	try 
    	{
            $result=array('status'=>0,'info'=>'');
    		$user_id=get_operator_id();
            foreach ($id as $v) {
               D('GiftRule')->deleteRule($v,$user_id);
            }    		
    	} catch (BusinessLogicException $e) {
             $result=array('status'=>1,'info'=>$e->getMessage());
    	}
    	 $this->ajaxReturn($result);
    }
}