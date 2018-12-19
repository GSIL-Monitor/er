<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilTool;
use Think\Log;

class TradeManageController extends BaseController
{
	public function getTradeList($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		if(IS_POST)
		{ 
			$where_sales_trade=' AND st_1.trade_status <= 115 ';
			$data=D('Trade')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order,'manage');
			$this->ajaxReturn($data);
		}else 
		{
			$id_list=array(
					'form'=>'trade_manage_form',
					'form_main'=>'trade_manage_form_main',
					'toolbar'=>'trade_manage_toobbar',
					'tab_container'=>'trade_manage_tab_container',
					'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
					'edit'=>'trade_manage_dialog_flag',
					'more_button'=>'trade_manage_more_button',
					'more_content'=>'trade_manage_more_content',
					'hidden_flag'=>'trade_manage_hidden_flag',
					'set_flag'=>'trade_manage_set_flag',
					'search_flag'=>'trade_manage_search_flag',//标记作为搜索条件,不作为搜索条件不写
					'fast_div'=>'trade_manage_fast_div',
					'fileForm'=> 'trade_manage_file_form',
				    'fileDialog'=>'trade_manage_file_dialog',
			);
			
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('TradeManage/getTradeList', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/Trade','trade_manage',1),
							'singleSelect'=>false,
							'ctrlSelect'=>true,
							'pageList'=>[20,50,100,200],
					),
					'fields'=>D('Setting/UserData')->getDatagridField('Trade/Trade','trade_manage'),
			);
			// $checkbox=array('field' => 'ck','checkbox' => true);
			// array_unshift($datagrid['fields'],$checkbox);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'goods_list')).'?tab=goods_list&prefix=tradeManage','title'=>'货品列表'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_detail')).'?tab=trade_detail&prefix=tradeManage','title'=>'订单详情'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'src_trade')).'?tab=src_trade&prefix=tradeManage','title'=>'原始订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'stock_list')).'?tab=stock_list&prefix=tradeManage','title'=>'库存明细'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_refund')).'?tab=trade_refund&prefix=tradeManage','title'=>'退换单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_merge')).'?tab=trade_merge&prefix=tradeManage','title'=>'同名未合并订单'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_remark')).'?tab=trade_remark&prefix=tradeManage','title'=>'备注记录'),
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'trade_log')).'?tab=trade_log&prefix=tradeManage','title'=>'订单日志')
			);
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			foreach ($arr_flag['list'] as $list) {  $arr[] = $list['id']; }
			foreach ($arr_flag['json'] as $k => $v) {
				if (!in_array($k, $arr)) { unset($arr_flag['json'][$k]); }
			}
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('form_id'=>$id_list['form'],'form_main_id'=>$id_list['form_main'],'more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			$res_cfg=D('Setting/Flag')->getCfgFlags(
					'bg_color,font_color,font_name',
					array('flag_name'=>array('eq','退款'),'flag_class'=>array('eq',1))
			);
			$refund_color='background-color:' . $res_cfg['bg_color'] . ';color:' . $res_cfg['font_color'] . ';font-family:' . $res_cfg['font_name'] . ';';
			$list_form=UtilDB::getCfgRightList(array('shop','logistics','reason','brand','warehouse'),array('reason'=>array('class_id'=>array('eq',1))));
			$list_form['flag']=D('Setting/Flag')->query('SELECT flag_id AS id ,flag_name AS name,font_color AS color,font_name AS family,bg_color FROM cfg_flags WHERE flag_class=1 AND is_builtin=0 AND is_disabled=0' );
			array_unshift($list_form['flag'],array('id'=>0,'name'=>'无'));
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign("id_list",$id_list);
			$this->assign('datagrid', $datagrid);
			$this->assign('refund_color',$refund_color);
			$this->display('show');
		}
	}
	
	public function getTradeListDialog($page=1, $rows=20, $search = array(), $sort = 'trade_id', $order = 'desc')
	{
		if(IS_POST)
		{
			$where_sales_trade=' AND st_1.trade_status>=95 AND st_1.trade_status<120 ';
			if($search['is_history']){
				$data=D('Trade')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order,'history');
			}else{
				$data=D('Trade')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order,'manage');
			}
			$this->ajaxReturn($data);
		}else{
			$id_list=array(
					'form'=>'trade_select_form',
					'toolbar'=>'trade_select_toobbar',
					'tab_container'=>'trade_select_tab_container',
					'id_datagrid'=>'sales_trade_dialog_datagrid',
					'more_button'=>'trade_select_more_button',
					'more_content'=>'trade_select_more_content',
					'hidden_flag'=>'trade_select_hidden_flag',
					//'set_flag'=>'trade_manage_set_flag',
					'search_flag'=>'trade_select_search_flag',//标记作为搜索条件,不作为搜索条件不写
			);
			$datagrid = array(
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'',
					'options'=> array(
							'title' => '',
							'url'   =>U('Trade/TradeManage/getTradeListDialog', array('grid'=>'datagrid')),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'singleSelect'=>true,
							'ctrlSelect'=>true,
					),
				   'fields' => D('Setting/UserData')->getDatagridField('Trade/Trade','trade_manage'),//'fields'=>get_field('Trade','trade_manage')
			);
			$arr_tabs=array(
					array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'order_list')).'?tab=order_list&prefix=tradeSelect','title'=>'货品列表'),
			);
			$arr_flag=D('Setting/Flag')->getFlagData(1);
			foreach ($arr_flag['list'] as $list) {  $arr[] = $list['id']; }
			foreach ($arr_flag['json'] as $k => $v) {
				if (!in_array($k, $arr)) { unset($arr_flag['json'][$k]); }
			}
			$params=array(
					'datagrid'=>array('id'=>$id_list['id_datagrid']),
					'search'=>array('more_button'=>$id_list['more_button'],'more_content'=>$id_list['more_content'],'hidden_flag'=>$id_list['hidden_flag'],'form_id'=>$id_list['form']),
					'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('TradeCommon/updateTabsData')),
					'flag'=>array(
							'set_flag'=>$id_list['set_flag'],
							'url'=>U('Setting/Flag/flag').'?flagClass=1',
							'json_flag'=>$arr_flag['json'],
							'list_flag'=>$arr_flag['list'],
							//'dialog'=>array('id'=>'flag_set_dialog','url'=>U('Setting/Flag/setFlag').'?flagClass=1','title'=>'颜色标记'),
							'search_flag'=>$id_list['search_flag']
					),
			);
			$list_form=UtilDB::getCfgList(array('shop','logistics'));
			$this->assign("list",$list_form);
			$this->assign("params",json_encode($params));
			$this->assign("id_list",$id_list);
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign('datagrid', $datagrid);
			$this->display('dialog_sales_trade');
		}
	}

	public function getRefundGoods($id){
		$id=intval($id);
		if(IS_POST)
		{
			$data=D('Trade')->getGoodsList($id,'');
			$this->ajaxReturn($data);
		}else{
			$id_list=array(
					'form'=>'refund_goods_select_form',
					'toolbar'=>'refund_goods_select_toobbar',
					'id_datagrid'=>'refund_goods_dialog_datagrid',
			);
			$datagrid = array(					
					'id'=>$id_list['id_datagrid'],
					'style'=>'',
					'class'=>'easyui-datagrid',
					'options'=> array(
							'title' => '',
							'url'   =>U('Trade/TradeManage/getRefundGoods', array('id'=>$id)),
							'toolbar' =>"#{$id_list['toolbar']}",
							'fitColumns'=>false,
							'singleSelect'=>false,
                            'pagination'=>false,
                            'checkOnSelect'=>true
					),
					'fields'=>get_field('TradeCommon','order_list')
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			array_unshift($datagrid['fields'],$checkbox);
            $this->assign('id_list', $id_list);
			$this->assign('datagrid', $datagrid);
			$this->display('dialog_select_refund_goods');
		}
	}

	public function exportToExcel(){
		if(!self::ALLOW_EXPORT){
			echo self::EXPORT_MSG;
			return false;
		}
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                	$key=substr($k,7,strlen($k)-8);
                	$search[$key]=$v;
                	unset($search[$k]);
                }
                D('TradeManage')->exportToExcel('',$search,$type);
            } 
            else{
                D('TradeManage')->exportToExcel($id_list,array(),$type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
    
    //恢复无需处理订单
    public function recoverTrade($ids){
    	if(IS_POST){
    		$arr_ids=is_json($ids);
    		$user_id=get_operator_id();
    		try{
    			$data=D('SalesTrade')->recoverTrade($arr_ids,$user_id);
    			$result=array(
    					'status'=>$data['status'],
    					'recover'=>$data['recover'],
    					'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),
    					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),
    			);
    		}catch (\Exception $e){
    			$result=array('status'=>1,'message'=>$e->getMessage());
    		}
    		$this->ajaxReturn($result);
    	}
    }
    // 恢复全额退款订单
    public function restoreTrade($ids){
    	$arr_ids_data=is_json($ids);
		$user_id=get_operator_id();
		$trade_db=D('SalesTrade');
		$condition=array('st.trade_id'=>array('in',$arr_ids_data));
		try{
				$data = $trade_db->restoreTrade($condition,$user_id);
				$result=array(
					'status'=>$data['status'],
					'info'=>array('total'=>count($data['list']),'rows'=>$data['list']),//失败提示信息
					'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
				);			
		}catch (\Think\Exception $e){
			$result=array('status'=>1,'message'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
    }
	/**
	 * 订单导入订单
	 */
	public function importTrade() {
		if(!self::ALLOW_EXPORT){
			$res["status"] = 1;
			$res["info"]   = self::EXPORT_MSG;
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
		//判断时间
		$checkDayStr = date('Y-m-d ',time());
		$startTime = strtotime($checkDayStr."08:00".":00");
		$endTime = strtotime($checkDayStr."19:00".":00");
		if(time()>$startTime && time()<$endTime ) {
			$res["status"] = 1;
			$res["info"]   = "工作时间不支持导入订单，请在19:00至次日8:00时间段内导入";
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
//		Log::write('start:'.date('Y-m-d H:i:s'));
		//获取Excel表格相关的数据
		$file = $_FILES["file"]["tmp_name"];
		$name = $_FILES["file"]["name"];
		//读取表格数据
		try {
			$excelClass = new ExcelTool();
			$excelClass->checkExcelFile($name,$file);
			$excelClass->uploadFile($file,"TradeManageImport");
			$count = $excelClass->getExcelCount();
			if($count>10000){
				SE("每次最多可导入一万条订单,请分批导入");
			}
			$excelData = $excelClass->Excel2Arr($count);
			/*$enable_check_by_template = true;//是否通过模板比对上传文件
            $template_file_name = "订单管理导入模板.xls";
            $template_file_sub_path = APP_PATH."Runtime/File/";
            $template_file_path = $template_file_sub_path.$template_file_name;
            if(file_exists($template_file_path)){
                $template_excelClass = new ExcelTool();
                $template_count = $template_excelClass->getExcelCount();
                $template_excelData = $template_excelClass->Excel2Arr($template_count);
            }else{
                //模板路径有问题，关闭校验，发送邮件
                $enable_check_by_template = false;
                \Think\Log::write ( '订单管理导入模板比对失败，订单管理导入模板路径有误，模板路径：'.$template_file_path ,\Think\Log::ERR);
            }*/
		} catch (\Exception $e) {
			$res = array("status" => 1, "info" => $e->getMessage());
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
		//记录插入数据的错误信息
		$err_msg = array();
		//记录订单数据
		$trade = array();
		$sheet_index = 0;
		foreach ($excelData as $sheet) {
			//表头校验
			//表头校验
			/*if( $enable_check_by_template==true ){
                if($sheet_index==0){
                    for ($t=0;$t<count($template_excelData['Sheet1'][0]);$t++){
                        if(!(trim($template_excelData['Sheet1'][0][$t]) == trim($sheet[0][$t]))){
                            $res['status'] = 1;
                            $res['info']   = '文件第一行数据有误，请参照模板文件';
                            $this->ajaxReturn(json_encode($res), "EVAL");
                        }
                    }
                }else{
                    $sheet_index++;
                    continue;
                }
            }*/
			if($sheet[0][0]!='订单编号'&&$sheet[0][1]!='当前状态'){
				$res = array("status" => 1, "info" => '导入模板不正确，请重新下载模板');
				$this->ajaxReturn(json_encode($res), "EVAL");
			}
			//数据填充
			for ($k = 1; $k < count($sheet); $k++) {
				$row = $sheet[$k];
				if (UtilTool::checkArrValue($row)) continue;
				//获取一条订单数据
				$i                 = 0;
				$trade_no          = trim($row[$i++]);//订单编号
				$current_status    = trim($row[$i++]);//当前状态
				$original_trade_no = trim($row[$i++]);//源订单号
				$original_sub_no   = trim($row[$i++]);//源子订单号
				$trade_type        = trim($row[$i++]);//订单类别
				$type_from         = trim($row[$i++]);//订单来源
				$paid              = trim($row[$i++]);//实收金额
//				$profit            = trim($row[$i++]);//利润
				$shop_name         = trim($row[$i++]);//所在店铺
				$warehouse_name    = trim($row[$i++]);//仓库
				$logistics_name    = trim($row[$i++]);//物流公司
				$post_amount       = trim($row[$i++]);//邮费
				$logistics_no      = trim($row[$i++]);//物流单号
				$merchant_no       = trim($row[$i++]);//商家编码
				$goods_num         = trim($row[$i++]);//货品数量
				$goods_price       = trim($row[$i++]);//货品价格
				$weight            = trim($row[$i++]);//重量
				$salesman          = trim($row[$i++]);//业务员
				$checker           = trim($row[$i++]);//审单员
				$invoice_type      = trim($row[$i++]);//发票类型
				$invoice_title     = trim($row[$i++]);//发票抬头
				$invoice_content   = trim($row[$i++]);//发票内容
				$buyer_nick        = trim($row[$i++]);//客户网名
				$receiver_name     = trim($row[$i++]);//收件人
				$receiver_province = trim($row[$i++]);//省
				$receiver_city     = trim($row[$i++]);//市
				$receiver_district = trim($row[$i++]);//区
				$receiver_address  = trim($row[$i++]);//地址
				$receiver_dtb      = trim($row[$i++]);//大头笔
				$receiver_zip      = trim($row[$i++]);//邮编
				$receiver_mobile   = trim($row[$i++]);//手机
				$receiver_telno    = trim($row[$i++]);//固话
				$buyer_message     = trim($row[$i++]);//买家备注
				$trade_remark      = trim($row[$i++]);//客服备注
				$pay_time          = trim($row[$i++]);//付款时间
				$gift_type         = trim($row[$i++]);//是否赠品
				//先构造子订单的数据
				$order = array(
					"src_tid"        => $original_trade_no,
					"src_oid"        => $original_sub_no,
					"merchant_no"    => $merchant_no,
					"num"            => $goods_num,
					"order_price"    => $goods_price,
					"paid"           => $paid,
					"share_post"     => $post_amount,
					"gift_type"      => $gift_type
				);
				//构造原始订单的数据
				if (!is_array($trade[$trade_no])) {
					$trade[$trade_no]  = array(
						"trade_no"          => $trade_no,
						"trade_status"      => $current_status,
						"trade_type"        => $trade_type,
						"type_from"         => $type_from,
//						"profit"            => $profit,
 						"shop_name"         => $shop_name,
						"warehouse_name"    => $warehouse_name,
						"logistics_name"    => $logistics_name,
						"logistics_no"      => $logistics_no,
						"weight"            => $weight,
						"salesman"          => $salesman,
						"checker"           => $checker,
						"invoice_type"      => $invoice_type,
						"invoice_title"     => $invoice_title,
						"invoice_content"   => $invoice_content,
						"buyer_nick"		=> $buyer_nick,
						"receiver_name"     => $receiver_name,
						"receiver_province" => $receiver_province,
						"receiver_city"     => $receiver_city,
						"receiver_district" => $receiver_district,
						"receiver_address"  => $receiver_address,
						"receiver_mobile"   => $receiver_mobile,
						"receiver_telno"    => $receiver_telno,
						"receiver_dtb"      => $receiver_dtb,
						"receiver_zip"      => $receiver_zip,
						"buyer_message"     => $buyer_message,
						"cs_remark"      => $trade_remark,
						"pay_time"          => $pay_time,
						"gift_type"         => $gift_type,
						"order"             => array()
					);
				}
				if (isset($trade[$trade_no]["order"][$original_sub_no])) {
					$err_msg[] = array("trade_no" => ''.$trade_no, "result" => "失败", "message" => "该订单包含多条重复的 " . $original_sub_no . " 子订单");
				} else {
					$trade[$trade_no]["order"][$original_sub_no] = $order;
				}
			}

		}
		//将订单插入数据库
		try {
			$count=count($trade);
			if($count>10000){
				SE('每次最多可导入一万条订单,请分批导入');
			}
			D("TradeManage")->importTrade($trade, $err_msg);
			$res = count($err_msg) > 0 ? array("status" => 2, "info" => $err_msg) : array("status" => 0, "info" => "操作成功");
		} catch (BusinessLogicException $e) {
			$res = array("status" => 1, "info" => $e->getMessage());
		} catch (\Exception $e) {
			Log::write($e->getMessage());
			$res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn(json_encode($res), "EVAL");
	}
	//下载模板
	public function downloadTemplet()
	{
		$file_name = "订单管理导入模板.xls";
		$file_sub_path = APP_PATH . "Runtime/File/";
		try {
			ExcelTool::downloadTemplet($file_name, $file_sub_path);
		} catch (BusinessLogicException $e) {
			Log::write($e->getMessage());
			echo '对不起，模板不存在，下载失败！';
		} catch (\Exception $e) {
			Log::write($e->getMessage());
			echo parent::UNKNOWN_ERROR;
		}
	}

}