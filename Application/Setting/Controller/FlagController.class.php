<?php

namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;

class FlagController extends BaseController
{
	public function setFlag($flagClass)
	{
		if(IS_POST)
		{
			try {
				$flagClass=intval($flagClass);
				$arr_data_add=I('post.add');
				$arr_data_update=I('post.update');
				$len_add=empty($arr_data_add)?0:count($arr_data_add);
				$len_update=empty($arr_data_update)?0:count($arr_data_update);
				$ids=array();
				if($len_add==0&&$len_update==0)
				{
					$this->success($ids);
				}
				$flag_db=M('cfg_flags');
				$flag_db->startTrans();
				for ($i=0;$i<$len_update;$i++)
				{
					if(intval($arr_data_update[$i]['id'])==0)
					{//过滤非法更新
						E('保存失败');
					}else 
					{
						$flag_data=$flag_db->field('flag_id,flag_name')->where("flag_class=".$arr_data_update[$i]['flag_class'])->select();
						$flag_name=array();
						$where['flag_id']=array('eq',intval($arr_data_update[$i]['id']));
						$arr_data_update[$i]['flag_name']=trim_all($arr_data_update[$i]['flag_name'],1);
						if(!isset($arr_data_update[$i]['flag_name']))
						{
							E('标记名称不能为空');
						}
						foreach ($flag_data as $k => $v) {
							if ($arr_data_update[$i]['flag_name']==$v['flag_name']&&$arr_data_update[$i]['id']!=$v['flag_id']) {
								E("标记名称'".$arr_data_update[$i]['flag_name']."'已存在!");
							}
						}						
						$arr_data_update[$i]['font_name']=$arr_data_update[$i]['font_id'];
						unset($arr_data_update[$i]['id']);
						unset($arr_data_update[$i]['flag_class']);
						unset($arr_data_update[$i]['font_id']);
						$flag_db->where($where)->save($arr_data_update[$i]);
					}
				}
				for ($i=0;$i<$len_add;$i++)
				{
					$flag_data=$flag_db->field('flag_name')->where("flag_class=".$arr_data_add[$i]['flag_class'])->select();
					$flag_name=array();
					foreach ($flag_data as $k => $v) { $flag_name[]=$v['flag_name'];}
					$arr_data_add[$i]['flag_name']=trim_all($arr_data_add[$i]['flag_name'],1);
					if(!isset($arr_data_add[$i]['flag_name']))
					{
						E('标记名称不能为空');
					}
					if (in_array($arr_data_add[$i]['flag_name'], $flag_name)) {
						E("标记名称'".$arr_data_add[$i]['flag_name']."'已存在!");
					}
					$add_id=$arr_data_add[$i]['id'];
					$arr_data_add[$i]['font_name']=$arr_data_add[$i]['font_id'];
					unset($arr_data_add[$i]['id']);
					unset($arr_data_add[$i]['font_id']);
					$res_id=$flag_db->add($arr_data_add[$i]);
					$ids[$add_id]=$res_id;
				}
				$flag_db->commit();
				$this->success(json_encode($ids));
			} catch (\PDOException $e) {
				\Think\Log::write($e->getMessage());
				$flag_db->rollback();
				$this->error('保存失败');
			} catch (\Exception $e) {
				\Think\Log::write($e->getMessage());
				$flag_db->rollback();
				$this->error($e->getMessage());
			}
		}else
		{
			$res_flag_arr=Factory::getModel('Flag')->getFlagData(intval($flagClass),'edit');
			$data=array('total'=>count($res_flag_arr),'rows'=>$res_flag_arr);
			$is_builtin_count=0;
			foreach($data['rows'] as $k=>$v){
				if($v['is_builtin']==1){
					$is_builtin_count++;
				}
			}
			$this->assign('is_builtin_count',$is_builtin_count);
			$this->assign('flag_class',$flagClass);
			$this->assign('flag_datagrid_data',json_encode($data));
			$this->display('Flag/dialog_flag');
		}
	}
	public function flag($flagClass)
	{
		try{
			$flag_id=intval(I('post.flag_id'));
			$sql='SELECT flag_id,flag_name FROM cfg_flags WHERE flag_id= '.$flag_id.' AND flag_class='.$flagClass.' LIMIT 1';
			$res_flag_id=M('cfg_flags')->query($sql);
			if(empty($res_flag_id)&&$flag_id!=0)
			{
				$this->error('你所选择的标记不存在');
			}
			$data['flag_id']=$flag_id;
			$arr_flag_class=array(
					'1'=>array('trade_id','sales_trade'), //flag_class->对应的表(主键,表明)
					'2'=>array('goods_id','goods_goods'),//货品
					'5'=>array('stockout_id','stockout_order'),//出库单
					'9'=>array('refund_id','sales_refund'),//退换单
					'23'=>array('rec_id','api_goods_spec'),//平台货品
			);
			$error_message=array(
					'1'=>'订单',
					'2'=>'货品',
					'5'=>'出库单',
					'9'=>'退换单',
					'23'=>'平台货品',
			);
			if(is_array($arr=I('post.id')))
			{
				$where[$arr_flag_class[$flagClass][0]]=array('in',$arr);
			}else
			{
				$where[$arr_flag_class[$flagClass][0]]=array('eq',$arr);
			}
			$res_val=M($arr_flag_class[$flagClass][1])->where($where)->save($data);
			if($res_val)
			{
				$this->success('标记成功');
			}else
			{
				$this->error('该'.$error_message[$flagClass].'已被标记为'.$res_flag_id[0]['flag_name'].'。');
			}
		}catch (\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->error('标记失败-未知错误');
		}
	}
}