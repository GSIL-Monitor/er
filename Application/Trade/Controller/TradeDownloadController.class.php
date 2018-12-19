<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Platform\Common\ManagerFactory;

class TradeDownloadController extends BaseController
{

	public function getShopList()
	{
		if (IS_POST)
		{
			$data = array();
			try
			{
				//设置店铺权限
				$shop_list = D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
				if(!empty($shop_list)){
					$res_cfg_shop_arr = M ( 'cfg_shop' )->alias ( 'csp' )->field ( 'csp.shop_id as id,csp.shop_name,csp.platform_id,csp.sub_platform_id,csp.account_nick,csp.auth_state' )->where ( 'csp.is_disabled=0 and csp.shop_id in ('.$shop_list.')')->select ();
				}
				$data = array(
						'total' => count ( $res_cfg_shop_arr ),
						'rows' => $res_cfg_shop_arr 
				);
			}
			catch ( \PDOException $e )
			{
				$data = array(
						'total' => 0,
						'rows' => null 
				);
				\Think\Log::write ( $e->getMessage () );
			}
			$this->ajaxReturn ( $data );
		}
		else
		{
			$id_list = array(
					'toolbar' => 'trade_download_toobbar',
					'tab_container' => 'trade_download_tab_container',
					'id_datagrid' => strtolower ( CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid' ) 
			);
			$datagrid_id = strtolower ( CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid' );
			$datagrid = array(
					'id' => $id_list ['id_datagrid'],
					'style' => '',
					'class' => '',
					'options' => array(
							'url' => U ( 'Trade/TradeDownload/getShopList', array(
									'grid' => 'datagrid' 
							) ),
							'toolbar' => "#{$id_list['toolbar']}",
							'pagination' => false,
							'methods' => 'onClickRow:onClickShopList' 
					),
					'fields' => get_field ( 'Trade', 'shop_list' ) 
			);
			$arr_tabs = array(
					0 => array(
							'id' => $id_list ['tab_container'],
							'url' => U ( 'Trade/TradeDownload/downTrade' ),
							'title' => '订单抓取日志' 
					) 
			);
			$now = date('Y-m-d H:i:s',time());
			$this->assign ( 'arr_tabs', json_encode ( $arr_tabs ) );
			$this->assign ('now',$now);
			$this->assign ( "id_list", $id_list );
			$faq_url=C('faq_url');
			$this->assign('faq_url',$faq_url['download']);
			$this->assign ( 'datagrid', $datagrid );
			$this->display ( 'show' );
		}
	}

	public function downTrade()
	{
		if (IS_POST)
		{
			$data = I ( 'post.data' );
			$arr_form_data = $data['form'];
			$type = I('post.type');
			switch (intval ( $arr_form_data ['radio'] ))
			{
				case 1 :
					if (empty ( $arr_form_data ['trade_id'] ))
					{
						$this->error ( '原始单号不能为空' );
					}
					break;
				case 2 :
					if (empty ( $arr_form_data ['buyer_nick'] ))
					{
						$this->error ( '买家账号不能为空' );
					}
					break;
				case 3 :
					/*if (! check_regex ( 'time', $arr_form_data ['start'] ) || ! check_regex ( 'time', $arr_form_data ['end'] ))
					{
						$this->error ( '时间格式不正确' );
					}
					else if (strtotime ( $arr_form_data ['start'] ) > strtotime ( $arr_form_data ['end'] ))
					{
						$this->error ( '开始时间不能大于结束时间' );
					}
					else if ((strtotime ( $arr_form_data ['start'] ) >= strtotime ( $arr_form_data ['end'] )) > 86400)
					{
						$this->error ( '时间跨度不能超过24小时' );
					}
					else if (strtotime ( $arr_form_data ['end'] ) > time ())
					{
						$this->error ( '结束时间不能大于当前时间' );
					}*/
					break;
				default :
					$this->error ( '非法输入' );
					break;
			}
			$arr_shop_data = $data['shop_datagrid'];
			if (empty ( $arr_shop_data ))
			{
				$this->error ( '请选择店铺' );
			}
			$msg = array(
					'status' => 0,
					'info' => '' 
			);
			try
			{
				if($type == '退款单'){
					$refundManager = ManagerFactory::getManager('Refund');
					$refundManager->manualSyncRefund( $arr_shop_data ['id'], $msg, $arr_form_data );
				}else{
					$tradeManager = ManagerFactory::getManager ( "Trade" );
					$tradeManager->manualSync ( $arr_shop_data ['id'], $msg, $arr_form_data );
				}
			}catch ( \PDOException $e )
			{
				$msg ['status'] = 0;
				$msg ['info'] = '未知错误，请联系管理员'; 
				\Think\Log::write ( 'sql_exception' . $e->getMessage () );
			}catch ( \Think\Exception $e )
			{
				$msg ['status'] = 0;
				$msg ['info'] = $e->getMessage ();
				\Think\Log::write ( 'my_exception' . $msg ['info'] ,\Think\Log::WARN);
			}
			$info = $msg;
			if(!is_array($msg)){
				$msg = array();
				$msg['status']=0;
				$msg['info'] = $info;
			}
			$this->ajaxReturn ( $msg );
		}else
		{
			$this->display('tabs_log');
        }
    }
}