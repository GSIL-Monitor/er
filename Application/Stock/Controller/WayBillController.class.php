<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
include_once (APP_PATH . "Platform/Common/ManagerFactory.class.php");
include_once (APP_PATH . 'Platform/Common/utils.php');
include_once (APP_PATH . 'Platform/Manager/utils.php');
include_once (APP_PATH."Platform/WayBill/util.php");

class WayBillController extends BaseController{
	/* public function getWayBillList()
	{
		$this->display('way_bill_edit');
	} */
	/* 
	 * stockout_ids in string 2343,345345,23453
	 * logistics_id in string 12432423
	 * result_info out 	
	 *  使得整个流程完全获取成功物流单号 array(
	 * 						'status'=>0,
	 * 						'msg'=>'success',
	 *                      'data' =>array(
	 *                         'fail' =>array()
	 *                         'success' =>array(.....)
	 *                      )
	 * 						) 
	 * 使得整个流程无法执行下去错误 时返回格式 array(
	 * 						'status'=>'1',
	 * 						'msg'=>'.....',
	 *                      'data' =>array()
	 * 						) 
	 * 	流程执行完整，包括部分单号在对接接口时候获取不到单号的情况array(
	 * 						'status'=>'2'
	 * 						'waybill_info'=>array(
	 * 								'fail'=>array('0'=>array('stock_id'=>'12321','stock_no'=>'fsdfd','msg'=>'....'))，
	 * 								'success'=>array('stockout_id'=>array(...))
	 * 							)
	 * 						) 
	 *  */
	public function  getWayBill($stockout_ids,$logistics_id)
	{
		$result_info=array();
		try{
			$where = array('so.stockout_id'=>array('in',$stockout_ids));
		    $res = D('Stock/StockOutOrder')->getSalesStockoutOrderList('st.logistics_id,st.trade_id',$where);
		    foreach ($res as $key => $value) {
		    	if($value['logistics_id'] != $logistics_id){
		    		$key--;
		    		break;
		    	}
		    }
		    if($key < (count($res)-1)){
		    	$status = 1;
		    	SE("物流公司不一致，请刷新后再尝试");
		    }
			$conditions = array();
			$conditions['type'] = 'getWayBill';
			$conditions['stockout_ids'] = $stockout_ids;
			$conditions['logistics_id'] = $logistics_id;
            $waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
			$waybill->manual($result_info,$conditions);
	// 		$waybill->getWayBill($stockout_ids, $logistics_id,$result_info);
		}catch(\Exception $e){
			$result_info = array("status"=>1,'msg'=>$e->getMessage());
		}
 		$this->ajaxReturn($result_info);
	}  
	/*
	 * syncWayBill  同步电子面单到平台   菜鸟电子面单调用的print接口，京东调用的是send接口  目的都是同步面单到平台
	 * 
	 */
	public function printWayBill($stockout_ids,$logistics_id,&$result_info)
	{
		$result_info=array();
		$conditions = array();
		$conditions['type'] = 'printCheckWayBill';
		$conditions['stockout_ids'] = $stockout_ids;
		$conditions['logistics_id'] = $logistics_id;
		$waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
		$waybill->manual($result_info,$conditions);
// 		$waybill->printCheckWayBill($stockout_ids, $logistics_id,$result_info);
// 		$this->ajaxReturn($result_info);
	}
	public function sendWayBill($stockout_ids,$logistics_id)
	{
	    $result_info=array();
	    $conditions = array();
	    $conditions['type'] = 'sendWayBill';
	    $conditions['stockout_ids'] = $stockout_ids;
	    $conditions['logistics_id'] = $logistics_id;
	    $waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
	    $waybill->manual($result_info,$conditions);
	    // 		$waybill->printCheckWayBill($stockout_ids, $logistics_id,$result_info);
	    return $result_info;
	}
	public function updateWayBill($stockout_ids,$logistics_id,&$result_info=array())
	{

		$conditions = array();
		$conditions['type'] = 'queryWayBill';
		$conditions['stockout_ids'] = $stockout_ids;
		$conditions['logistics_id'] = $logistics_id;
		$waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
		$waybill->manual($result_info,$conditions);
// 		$waybill->queryWayBill($stockout_ids, $logistics_id,$result_info);
		$this->ajaxReturn($result_info);
		
	}
	
	
	public function productWayBill($logistics_id='',&$result_info=array())
	{
		$conditions = array();
		$conditions['type'] = 'productWayBill';
		$conditions['logistics_id'] = $logistics_id;
		$waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
		$waybill->manual($result_info,$conditions);
// 		$waybill->productWayBill( $logistics_id,$result_info);
		$this->ajaxReturn($result_info);
	}
	
	/**
	 * 
	 * @param string $id
	 * @param boolean $type 0-查询店铺所有物流的热敏信息 1-代表查询特定的热敏信息
	 * @param unknown $result_info
	 * @return boolean
	 */
	public function searchWayBill($logistics_id,&$result_info=array())
	{

		$conditions = array();
		$conditions['type'] = 'searchWayBill';
		$conditions['logistics_id'] = $logistics_id;
		$waybill = \Platform\Common\ManagerFactory::getManager('WayBill');
		$waybill->manual($result_info,$conditions);
// 		$waybill->searchWayBill( $logistics_id,$result_info);
//		$this->ajaxReturn($result_info);
	}
	public function cancelWayBill($stockout_ids,$logistics_id)
	{
		$result_info=array();
		$conditions = array();
		$conditions['type'] = 'cancelWayBill';
		$conditions['stockout_ids'] = $stockout_ids;
		$conditions['logistics_id'] = $logistics_id;
		$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
		$waybill->manual($result_info,$conditions);
		return $result_info;
	}

	public function getTemplates($logisticsId){
		try{
			$user_id = get_operator_id();
            if(isset($logisticsId)){//物流单模板
				$Logistics_db = D('Setting/Logistics');
	            $logistics_info = $Logistics_db->getLogisticsInfo($logisticsId);
	            $logistics_info = $logistics_info[0];
				if((int)$logistics_info['bill_type'] == 2){//菜鸟物流单模板(指getMyStdTemplates接口的)
					$model = D('Setting/PrintTemplate');
			        $fields = array('rec_id as id,type,title,content');
			        //$conditions = array('type'=>4);
			        $conditions['type'] = "getTemplates";
			        $conditions['logistics_id'] = $logisticsId;
			        $waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
					//$waybill -> manual($result_info,$conditions);
					$waybill -> getTemplates($result_info,$logisticsId);
					$this->assign('stdTemplates',json_encode($result_info));
					$templates = $model->getTemplateByLogistics($fields,'4,8,7',$logistics_info['logistics_type']);
					$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
//					$template_id = $template_id[0]['data'];
                    $ltData = json_decode($template_id[0]['data'],true);
                    if(array_key_exists($logisticsId,$ltData)){
                        $template_id = $ltData[$logisticsId];
                    }else{
                        $template_id = '';
                    }
					//$isvCustomArea = $model->get($fields,'7');
					//$templates = array_merge($templates,$isvCustomArea);
				}elseif((int)$logistics_info['bill_type'] == 9){
					$model = D('Setting/PrintTemplate');
			        $fields = array('rec_id as id,type,title,content');
			        $conditions = array(5,6,9);
					$templates = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','京东_%')))->order('is_default desc')->select();
					$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>6))->select();
//					$template_id = $template_id[0]['data'];
					$result_info = array('status'=>9);
                    $this->assign('stdTemplates',json_encode($result_info));
					$ltData = json_decode($template_id[0]['data'],true);
                    if(array_key_exists($logisticsId,$ltData)){
                        $template_id = $ltData[$logisticsId];
                    }else{
                        $template_id = '';
                    }
				}else{//普通物流单模板(getISVTemplates)
					$model = D('Setting/PrintTemplate');
			        $fields = array('rec_id as id,type,title,content');
			        $conditions = array(5,6,9);
					$templates = $model->get($fields,$conditions);
					$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>6))->select();
//					$template_id = $template_id[0]['data'];
                    $ltData = json_decode($template_id[0]['data'],true);
                    if(array_key_exists($logisticsId,$ltData)){
                        $template_id = $ltData[$logisticsId];
                    }else{
                        $template_id = '';
                    }
				}
			}else {//发货单模板
				$model = D('Setting/PrintTemplate');
		        $fields = array('rec_id as id,type,title,content');
		        $conditions = array(5,6,9);
				$templates = $model->get($fields,$conditions);
				$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
				$template_id = $template_id[0]['data'];
			}
			foreach($templates as $key){
				$contents[$key['id']] = $key['content'];
			}
			$goods = D('StockOutOrder')->getStockoutOrderDetailPrintData(I('get.ids'));
			foreach($goods as $v){
				if(!isset($no[$v['id']]))
					$no[$v['id']] = 0;
				$v['no'] = ++$no[$v['id']];
				$detail[$v['id']][] = $v;
				D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
			}
			$goods = $detail;
            D('StockSalesPrint','Controller')->composeSuiteData($goods);
            if(empty($template_id)) $template_id=$templates[0]['id'];
			if(empty($template_id)) $template_id='-1';
			$preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
			$preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
			$this->assign("preview_format", $preview_format);
			$this->assign('template_id',$template_id);
			$this->assign('goods',json_encode($goods));
			$this -> assign('contents',json_encode($contents));
			$this -> assign('templates',$templates);
            $this->assign('isMulti',I('get.isMulti'));
        }catch(\PDOException $e){
			\Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = $e->getMessage();
			$this->assign('ret',$ret);
		}catch(\Exception $e){
			\Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = $e->getMessage();
			$this->assign('ret',$ret);
        }
        $this -> display('templates_select');
	}
	
	public function newPrintDialog($logisticsId){
		try{
			$user_id = get_operator_id();
            $Logistics_db = D('Setting/Logistics');
			$logistics_info = $Logistics_db->getLogisticsInfo($logisticsId);
			$logistics_info = $logistics_info[0];
			$model = D('Setting/PrintTemplate');
			$fields = array('rec_id as id,type,title,content');
			if((int)$logistics_info['bill_type'] == 2){//菜鸟物流单模板(指getMyStdTemplates接口的)
				$conditions['type'] = "getTemplates";
				$conditions['logistics_id'] = $logisticsId;
				$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
				//$waybill -> manual($result_info,$conditions);
				$waybill -> getTemplates($result_info,$logisticsId);
				
				$this->assign('stdTemplates',json_encode($result_info));
				$waybill_templates = $model->getTemplateByLogistics($fields,'4,8,7',$logistics_info['logistics_type']);
				$waybill_template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7))->select();
				$ltData = json_decode($waybill_template_id[0]['data'],true);
				if(array_key_exists($logisticsId,$ltData)){
					$waybill_template_id = $ltData[$logisticsId];
				}else{
					$waybill_template_id = '';
				}
				
				foreach($waybill_templates as $key){
					$waybill_contents[$key['id']] = $key['content'];
				}
				if(empty($waybill_template_id)) $waybill_template_id=$waybill_templates[0]['id'];
				if(empty($waybill_template_id)) $waybill_template_id='-1';
			}
			   
			$conditions = array(5,6,9);
			$templates = $model->get($fields,$conditions);
			$template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
			$logistics_template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>6))->select();
			$ltiData = json_decode($logistics_template_id[0]['data'],true);
			if(array_key_exists($logisticsId,$ltiData)){
				$logistics_template_id = $ltiData[$logisticsId];
			}else{
				$logistics_template_id = '';
			}	
			$template_id = $template_id[0]['data'];
			
			foreach($templates as $key){
				$contents[$key['id']] = $key['content'];
			}
			$goods = D('StockOutOrder')->getStockoutOrderDetailPrintData(I('get.ids'));
			foreach($goods as $v){
				if(!isset($no[$v['id']]))
					$no[$v['id']] = 0;
				$v['no'] = ++$no[$v['id']];
				$detail[$v['id']][] = $v;
				D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
			}
			$goods = $detail;
			if(empty($template_id)) $template_id=$templates[0]['id'];
			if(empty($template_id)) $template_id='-1';
			if(empty($logistics_template_id)) $logistics_template_id=$templates[0]['id'];
			if(empty($logistics_template_id)) $logistics_template_id='-1';
			$preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
			$preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
			$this->assign("preview_format", $preview_format);
			$this->assign('template_id',$template_id);
			$this->assign('goods',json_encode($goods));
			$this -> assign('contents',json_encode($contents));
			$this -> assign('goods_template',$templates);
            if((int)$logistics_info['bill_type'] == 2){
				$this->assign('waybill_template_id',$waybill_template_id);
				$this -> assign('waybill_contents',json_encode($waybill_contents));
				$this -> assign('waybill_templates',$waybill_templates);
			}else{
				$this->assign('waybill_template_id',$logistics_template_id);
				$this -> assign('waybill_contents',json_encode($contents));
				$this -> assign('waybill_templates',$templates);
			}
            $this->assign('isMulti',I('get.isMulti'));
        }catch(\PDOException $e){
			\Think\Log::write(__CONTROLLER__."--printdialog--".$e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = $e->getMessage();
			$this->assign('ret',$ret);
		}catch(\Exception $e){
			\Think\Log::write(__CONTROLLER__."--printdialog--".$e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = $e->getMessage();
			$this->assign('ret',$ret);
        }
        $this -> display('print_dialog');

	
	}
    public function newPrintLogAndPickDialog($logisticsId){
        try{
            $user_id = get_operator_id();
            $Logistics_db = D('Setting/Logistics');
            $logistics_info = $Logistics_db->getLogisticsInfo($logisticsId);
            $logistics_info = $logistics_info[0];
            $model = D('Setting/PrintTemplate');
            $fields = array('rec_id as id,type,title,content');
            if((int)$logistics_info['bill_type'] == 2){//菜鸟物流单模板(指getMyStdTemplates接口的)
                $conditions['type'] = "getTemplates";
                $conditions['logistics_id'] = $logisticsId;
                $waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
                //$waybill -> manual($result_info,$conditions);
                $waybill -> getTemplates($result_info,$logisticsId);

                $this->assign('stdTemplates',json_encode($result_info));
                $waybill_templates = $model->getTemplateByLogistics($fields,'4,8,7',$logistics_info['logistics_type']);
                $waybill_template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7))->select();
                $ltData = json_decode($waybill_template_id[0]['data'],true);
                if(array_key_exists($logisticsId,$ltData)){
                    $waybill_template_id = $ltData[$logisticsId];
                }else{
                    $waybill_template_id = '';
                }

                foreach($waybill_templates as $key){
                    $waybill_contents[$key['id']] = $key['content'];
                }
                if(empty($waybill_template_id)) $waybill_template_id=$waybill_templates[0]['id'];
                if(empty($waybill_template_id)) $waybill_template_id='-1';
            }

            $conditions = array(5,6,9);
            $templates = $model->get($fields,$conditions);
            $template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>5))->select();
            $logistics_template_id = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>6))->select();
            $ltiData = json_decode($logistics_template_id[0]['data'],true);
            if(array_key_exists($logisticsId,$ltiData)){
                $logistics_template_id = $ltiData[$logisticsId];
            }else{
                $logistics_template_id = '';
            }
            $template_id = $template_id[0]['data'];

            foreach($templates as $key){
                $contents[$key['id']] = $key['content'];
            }
            $goods = D('StockOutOrder')->getStockoutOrderDetailPrintData(I('get.ids'));
            foreach($goods as $v){
                if(!isset($no[$v['id']]))
                    $no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
                $detail[$v['id']][] = $v;
                D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
            }
            $goods = $detail;
            if(empty($template_id)) $template_id=$templates[0]['id'];
            if(empty($template_id)) $template_id='-1';
            if(empty($logistics_template_id)) $logistics_template_id=$templates[0]['id'];
            if(empty($logistics_template_id)) $logistics_template_id='-1';
            $preview_format = D('Stock/StockoutOrderDetail')->savePreviewFormat();
            $preview_format = empty($preview_format['data'])?'image':$preview_format['data'];
            /********分拣*********/
            $stockout_ids = I('get.ids');
            $print_batch_model = M('stockout_print_batch');
            $batchData =  $print_batch_model->field('batch_no,order_mask,order_num,pick_list_no')->where(['queue' => $stockout_ids])->find();
            if($batchData['pick_list_no'] == ''){
                $pln_sql = 'select FN_SYS_NO("picklist") pick_list_no';
                $pick_list_no = $print_batch_model->query($pln_sql);
                $batchData['pick_list_no'] = $pick_list_no[0]['pick_list_no'];
            }

            $pickListData = D('StockOutOrder')->getPickListOrderDetailPrintData($stockout_ids);
            foreach($pickListData as $v){
                if(!isset($no[$v['id']]))
                    $no[$v['id']] = 0;
                $v['no'] = ++$no[$v['id']];
                $detail[$v['id']][] = $v;
                D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
            }
            $pickListData = $detail;
            D('StockSalesPrint','Controller')->composeSuiteData($pickListData);
            $pickListData[0] = $pickListData[''];
            array_splice($pickListData,0,1);
            $pickListData = array_pop($pickListData);
            $newPickListData[] = $pickListData;
            /********分拣*********/
            $this->assign('sortingData',json_encode($newPickListData));
            $this->assign('batchData',json_encode($batchData));
            $this->assign("preview_format", $preview_format);
            $this->assign('template_id',$template_id);
            $this->assign('goods',json_encode($goods));
            $this -> assign('contents',json_encode($contents));
            $this -> assign('goods_template',$templates);
            if((int)$logistics_info['bill_type'] == 2){
                $this->assign('waybill_template_id',$waybill_template_id);
                $this -> assign('waybill_contents',json_encode($waybill_contents));
                $this -> assign('waybill_templates',$waybill_templates);
            }else{
                $this->assign('waybill_template_id',$logistics_template_id);
                $this -> assign('waybill_contents',json_encode($contents));
                $this -> assign('waybill_templates',$templates);
            }
            $this->assign('isMulti',I('get.isMulti'));
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--printdialog--".$e->getMessage());
            $ret['status'] = 1;
            $ret['msg'] = $e->getMessage();
            $this->assign('ret',$ret);
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--printdialog--".$e->getMessage());
            $ret['status'] = 1;
            $ret['msg'] = $e->getMessage();
            $this->assign('ret',$ret);
        }
        $this -> display('print_logistics_picklist_dialog');
    }
	public function  newGetWayBill($stockout_ids,$logistics_id,$templateURL="",$isAjax = true)
	{
		$templateId = I('post.templateId');
		$oldTemplateId = I('post.oldTemplateId');
        $packageCount = I('post.packageCount');
		try{
			if(strlen(trim($templateId))>0){
				$data = array(array('content'=>'','title'=>'','rec_id'=>$templateId,'is_default'=>1,'type'=>'10'
					),array('content'=>'','title'=>'','rec_id'=>$oldTemplateId,'is_default'=>0,'type'=>'10'
					)
					);
				D('Setting/PrintTemplate')->addAll($data,'','is_default');
			}
            $stockoutIdArr = explode(',',$stockout_ids);
            $packageCountArr = explode(',',$packageCount);
            $packNoArr = $this->generatePackageNos($stockoutIdArr,$packageCountArr);
            $result_info=array();
	   		$conditions = array();
	   		$conditions['type'] = 'getWayBill';
	   		$conditions['stockout_ids'] = $stockout_ids;
	   		$conditions['logistics_id'] = $logistics_id;
	   		$conditions['templateURL'] = $templateURL;
            $conditions['packageNos'] = $packNoArr;
	   		$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
	   		$waybill->manual($result_info,$conditions);
	   		if(!empty($packNoArr)){
                $result_info['startNo'] =$packNoArr[0];
            }
	   	}catch(\Exception $e){
	   		\Think\Log::write(__CONTROLLER__."--getWayBill--".$e->getMessage());
			$result_info['status'] = 1;
			$result_info['msg'] = $e->getMessage();
	   	}catch(\PDOException $e){
	   		\Think\Log::write(__CONTROLLER__.'--getWaybill--'.$e->getMessage());
	   		$result_info['status'] =1;
	   		$result_info['msg'] = self::PDO_ERROR;
	   	}
	   	if($isAjax){
            $this->ajaxReturn($result_info);
        }else{
	   	    return $result_info;
        }

	}
	private function generatePackageNos($stockoutIdArr,$packageCountArr){
        if(empty($stockoutIdArr)) return ;
        if($packageCountArr[0] == 0) return array();
        $packNoArr = array();
        $tempNoArr = array();
        for($i=0;$i<count($stockoutIdArr);$i++ ){
            $startNo = 1;
            $where = array('stockout_id'=>$stockoutIdArr[$i]);
            $maxPackageNo = D('SalesMultiLogistics')->field("max(package_no) as max_p")->where($where)->find();
            if(!empty($maxPackageNo['max_p'])){
                $startNo = $maxPackageNo['max_p']+1;
            }
            for($j=0;$j<$packageCountArr[$i];$j++){
                $tempNo = $startNo+$j;
                $tempNoArr[] = $stockoutIdArr[$i].$tempNo;
            }
            $packNoArr[$stockoutIdArr[$i]] = $tempNoArr;
            $tempNoArr = array();
        }
        return $packNoArr;
    }
}