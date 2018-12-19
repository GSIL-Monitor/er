<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class FinancialCheckController extends BaseController
{
	public function getFinancialCheckList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		if(IS_POST)
		{ 	$where_sales_trade=' AND st_1.trade_status = 35 ';
			$data=D('TradeCheck')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order);
			$this->ajaxReturn($data);
		}else 
		{
			$id_list=array();
			$this->getIDList($id_list,array("form","toolbar","tab_container","id_datagrid","hidden_flag","more_button","more_content","set_flag","search_flag"));
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('FinancialCheck/getFinancialCheckList', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'frozenColumns' => D('Setting/UserData')->getDatagridField('Trade/FinancialCheck','financial_check',1),
							'singleSelect'=>false,
							'ctrlSelect'=>true,
					),
					'fields' => D('Setting/UserData')->getDatagridField('Trade/FinancialCheck','financial_check'),
// 					'fields'=>get_field('FinancialCheck','financial_check')
			);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'fc_reason')).'?tab=fc_reason&prefix=FinancialCheck','title'=>'财审原因'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'goods_list')).'?tab=goods_list&prefix=FinancialCheck','title'=>'货品列表'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_detail')).'?tab=trade_detail&prefix=FinancialCheck','title'=>'订单详情'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'src_trade')).'?tab=src_trade&prefix=FinancialCheck','title'=>'原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'stock_list')).'?tab=stock_list&prefix=FinancialCheck','title'=>'库存明细'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_remark')).'?tab=trade_remark&prefix=FinancialCheck','title'=>'备注记录'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_log')).'?tab=trade_log&prefix=FinancialCheck','title'=>'订单日志')
			);
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('form_id'=>$id_list['form'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
					'revert_reason'=>array(
		                'id'=>'flag_set_dialog',
		                'url'=>U('setting/CfgOperReason/getReasonList').'?class_id=1&model_type=financialCheck',
		                'title'=>'驳回原因',
		                'width'=>300,
		                'height'=>'auto',
		                'form' =>array('url'=>U("Trade/FinancialCheck/revertTradeCheck"),'id'=>'cfg_oper_reason_form','list_id'=>'cfgoperreason_list_combobox','dialog_type'=>'tradecheck')
		            ),
			);
			$res_cfg=D('Setting/Flag')->getCfgFlags(
					'bg_color,font_color,font_name',
					array('flag_name'=>array('eq','退款'),'flag_class'=>array('eq',1))
			);
			$refund_color='background-color:' . $res_cfg['bg_color'] . ';color:' . $res_cfg['font_color'] . ';font-family:' . $res_cfg['font_name'] . ';';
			$list_form=UtilDB::getCfgRightList(array('shop','logistics','reason'),array('reason'=>array('class_id'=>array('eq',1))));
			$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=1 AND is_builtin=0 AND is_disabled=0' );
			$list_form['brand']=D('Goods/GoodsBrand')->field('brand_id,brand_name')->where(array('is_disabled'=>array('eq',0)))->select();
			array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign("id_list",$id_list);
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign('datagrid', $datagrid);
			$this->assign('refund_color',$refund_color);
			$this->display('show');
		}
	}
	
	public function financialCheck($ids)
	{
		$arr_ids_data=is_json($ids);
		$check_type=I('post.type');//0普通审核，1强制审核
		$check_type=intval($check_type);
		$user_id=get_operator_id();
		$trade_check_db=D('TradeCheck');
		try {
			$sql_where=$trade_check_db->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('in',$arr_ids_data)))->select();
			$data=$trade_check_db->checkTrade($sql_where,$check_type,$user_id,1);
			$result=array(
					'check'=>$data['check'],
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
			);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}catch (\Think\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}

	public function revertTradeCheck($ids)
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
        if(empty($ids))
        {
            $result['info'] ="请选择订单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
    
        $form_params['is_force'] = 0;//强制驳回标记
        $form_params['operator_id'] = get_operator_id();
        if (empty( $form_params['reason_id']) || (int) $form_params['reason_id'] == 0)
        {
            $result['info'] ="无效的原因";
            $result['status'] = 1;
            return $this->ajaxReturn($result);
        }
        $financialCheck_ids = explode(",", $ids);
        foreach ($financialCheck_ids as $key=>$id)
        {
            $success[$key] = array();
            D('Trade/FinancialCheck')->revertTradeCheck($id,$form_params,$fail,$success[$key]);
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
            'success' => $success,
        );
        $result['type']="new";
        $this->ajaxReturn($result);
	}

}