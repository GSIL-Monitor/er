<?php
namespace Purchase\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Thinl\Model;
use Stock\StockCommonField;
use Stock\StockPurchaseManagementField;
use Platform\Common\ManagerFactory;

class PurchaseManagementController extends BaseController{
	
	public function show(){
		try{
			$id_list = array();
			$need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete','file_form','file_dialog','print_dialog');
			$this->getIDList($id_list,$need_ids,'','');
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'url'=>U('PurchaseManagement/search'),
					'toolbar'=>$id_list['toolbar'],
					'fitColumns'=>false,
					"rownumbers" => true,
					"pagination" => true,
					'singleSelect'=>false,
					'ctrlSelect'=>true,
					"method"     => "post",
				),
				'fields'=>get_field('PurchaseOrder','purchasemanagement'),
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			array_unshift($datagrid['fields'],$checkbox);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'tabs'=>array(
					'id'=>$id_list['tab_container'],
					'url'=>U('Purchase/PurchaseCommon/showTabDatagridData'),
				),
				'edit'=>array('id'=>$id_list['edit'],'url'=>U('Purchase/PurchaseManagement/edit'),'title'=>'采购编辑'),
			);
			$arr_tabs = array(
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_order_detail")).'?prefix=purchasemanagment&tab=purchase_order_detail&app=Purchase/PurchaseOrder',"id"=>$id_list['tab_container'],"title"=>"采购单详情"),
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_order_log")).'?prefix=purchasemanagment&tab=purchase_order_log&app=Purchase/PurchaseOrder',"id"=>$id_list['tab_container'],"title"=>"日志"),
			);
			$list = UtilDB::getCfgRightList(array('warehouse','employee','provider'));
//			$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider();
//			$list['provider'] = $provider['data'];
			$warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
			$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
			$employee_default['0'] = array('id' => 'all','name'=>'全部');
			$employee_array = array_merge($employee_default,$list['employee']);
			$provider_default['0'] = array('id' => 'all','name'=>'全部');
			$provider_array = array_merge($provider_default,$list['provider']);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		try
		{
			$id_list['goods_amount_sum']=D('PurchaseOrder')->getGoodsAmount();
		}catch(BusinessLogicException $e)
		{
			$id_list['goods_amount_sum']=0;
		}
		$this->assign('warehouse_array',$warehouse_array);
		$this->assign('employee_array',$employee_array);
		$this->assign('provider_array',$provider_array);
		$this->assign('params',json_encode($params));
		$this->assign('arr_tabs',json_encode($arr_tabs));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->display('show');
		
	}
	public function search($page = 1,$rows = 20,$search = array(),$sort = 'id',$order = 'desc'){
			try{
				$result = D('PurchaseOrder')->search($page,$rows,$search,$sort,$order);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result = array('rows'=>array(),'total'=>1);
				
			}
			$this->ajaxReturn($result);
	}
	public function edit(){
		$edit_info = I('','',C('JSON_FILTER'));
		$id = $edit_info['id'];
		$management = $edit_info['management_info'];
		D('Purchase/PurchaseOrder','Controller')->edit($id,$management);
	}
	public function cancelPurchaseOrder($ids){
		try{
			$data = array('status'=>0,'info'=>'取消成功');
			$data = D('PurchaseOrder')->cancelPurchaseOrder($ids);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	public function submitPurchaseOrder($ids,$purchase_nos){
		try{
			$ids = explode(',',$ids);
			$purchase_nos = explode(',',$purchase_nos);
			$purchase_order_model = D('PurchaseOrder');
			$err_id = 0;
			for($i = 0; $i < count($ids); $i++){
				$err_id = $purchase_nos[$i];
				$info = $purchase_order_model->submitPurchaseOrder($ids[$i],true);
				if($info){$data[] = array('status'=>1,"purchase_no"=>$purchase_nos[$i],"info"=>$info);}
			}
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$data[] = array('status'=>1,"purchase_no"=>$err_id,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data[] = array('status'=>1,"purchase_no"=>$err_id,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	public function send($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择采购单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$purchase_info_fields = array("po.status","cw.type");
				$purchase_info_cond = array(
					'po.purchase_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/PurchaseOrder')->alias('po')->field($purchase_info_fields)->join('left join cfg_warehouse cw on cw.warehouse_id = po.warehouse_id ')->where($purchase_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 43 && (int)$res_so_info['status']!= 45)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['type']!=11 && (int)$res_so_info['type']!=15)
				{
					SE("仓库类型不正确");
				}
					
				$WmsManager = ManagerFactory::getManager("Wms");
				$WmsManager->manual_wms_adapter_add_po($sid, $uid, $id);
			}
		} catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
	
	public function cancel_po($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择采购单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$purchase_info_fields = array("po.status","cw.type");
				$purchase_info_cond = array(
					'po.purchase_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/PurchaseOrder')->alias('po')->field($purchase_info_fields)->join('left join cfg_warehouse cw on cw.warehouse_id = po.warehouse_id ')->where($purchase_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 48 && (int)$res_so_info['status']!= 43)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['type']!=11 && (int)$res_so_info['type']!=15)
				{
					SE("仓库类型不正确");
				}
				if((int)$res_so_info['status'] == 43){
					D('Purchase/PurchaseOrder')->cancel_po($id);
				}else{
					$WmsManager = ManagerFactory::getManager("Wms");
					$WmsManager->manual_wms_adapter_cancel_po($sid, $uid, $id);
				}
			}
		} catch (BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
	public function upload(){
		$result = array(
			'status' => 0,
			'msg' =>'成功',
			'data'=>array(),
		);
		$file = $_FILES['file']['tmp_name'];
		$name = $_FILES['file']['name'];
		try{
			$exceldata = UtilTool::Excel2Arr($name,$file,'StockSpecImport');
		}catch(\Exception $e){
			\Think\Log::write('采购导入-'.$e->getMessage());
			$result['status'] = 1;
			$result['msg'] = '文件错误，无法读取';
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		try{
			$model = D('Purchase/PurchaseOrder');
			$fail_result = array();
			$data = array();
			$merchant_no = array();
			foreach($exceldata as $sheet){
				$field_name_list = $sheet[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '仓库名称' || $field_name_list[2] != '采购数量' || $field_name_list[3] != '采购价' || $field_name_list[4] != '供应商'){
					SE('导入模板不正确，请先下载模板');
				}
				for($k = 1;$k<count($sheet);$k++){
					$row = $sheet[$k];
					$line = $k+1;
					$i = 0;
					$is_full_row = 1;
					$tmp_data = array(
						'spec_no'				=>$row[$i],
						'warehouse_name'		=>$row[++$i],
						'num'					=>$row[++$i],
						'price'					=>$row[++$i],
						'provider_name'			=>$row[++$i],
						'line'  				=>$line,
						'status'				=>0,
						'message'				=>'成功',
						'result'				=>'',
					); 
					if(isset($merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']][$tmp_data['provider_name']])){
						$merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']][$tmp_data['provider_name']] = false;
					}else{
						$merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']][$tmp_data['provider_name']] = true;
					}
					 foreach($tmp_data as $temp_key => $temp_value){
                        if(!empty($temp_value)&& $temp_key!='line'&& $temp_key!='message'&& $temp_key!='result'&& $temp_key!='status'){
                            $is_full_row = 0;
                        }
                      
                    }
					if($is_full_row == 0){
						$data[] = $tmp_data;
					}
				}
			}
			if(empty($data)){
				SE('读取导入的数据失败');
			}			
			foreach($data as $data_k=>$data_v){
				if(empty($data_v['warehouse_name']) || empty($data_v['spec_no'])){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '仓库或商品编码不能为空';
					$data[$data_k]['result'] = '失败';
				}elseif($merchant_no[$data_v['spec_no']][$data_v['warehouse_name']][$data_v['provider_name']] == false){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '导入仓库['.$data_v['warehouse_name'].']商家编码重复';
					$data[$data_k]['result'] = '失败';
				}elseif(empty($data_v['num'])){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '采购数量不能为空或者等于0';
					$data[$data_k]['result'] = '失败';
				}elseif(!is_numeric($data_v['price'])&&!empty($data_v['price']) || is_numeric($data_v['price'])&&!empty($data_v['price'])&&$data_v['price']<0){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '采购价必须是大于等于0的数字';
					$data[$data_k]['result'] = '失败';
					$data[$data_k]['price'] = 0;
				}
				if(empty($data_v['price'])){
					$data[$data_k]['price'] = 0;
				}
				if(empty($data_v['provider_name'])){
					$data[$data_k]['provider_name'] = '无';
				}
			}	
			
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['msg'] =$msg;
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		$sql_drop = "create temporary table if not exists `tmp_import_detail` (`rec_id` int(11) not null auto_increment,`spec_no` varchar(40),`spec_id` int(11),`warehouse_name` varchar(40),`warehouse_id` int(11),`num` decimal(19,4),`price` decimal(19,4),`stock_num` decimal(19,4),`provider_name` varchar(40),`provider_id` int(11),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),primary key (`rec_id`))engine = InnoDB default charset=utf8;";
		$model->execute($sql_drop);
		$model->upload($data,$result);
		$model->execute('delete from `tmp_import_detail`');
		
		$this->ajaxReturn(json_encode($result),'EVAL');
	}
	
	public function downloadTemplet(){
        $file_name = "采购导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }
	public function revertCheck($ids,$purchase_nos){
		try{
			if(empty($ids)){$this->ajaxReturn(array('status'=>1,'info'=>'没有获取到采购单信息，请刷新后重试！'));}
			$ids = explode(',',$ids);
			$purchase_nos = explode(',',$purchase_nos);
			$purchase_order_model = D('PurchaseOrder');
			$err_id = 0;
			for($i = 0; $i < count($ids); $i++){
				$err_id = $purchase_nos[$i];
				$info = $purchase_order_model->revertCheck($ids[$i],true);
				if($info){$data[] = array('status'=>1,"purchase_no"=>$purchase_nos[$i],"info"=>$info);}
			}
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data[] = array('status'=>1,"purchase_no"=>$err_id,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	public function stopPurchaseOrder($ids,$purchase_nos){
		try{
			$ids = explode(',',$ids);
			$purchase_nos = explode(',',$purchase_nos);
			$purchase_order_model = D('PurchaseOrder');
			$err_id = 0;
			for($i = 0; $i < count($ids); $i++){
				$err_id = $purchase_nos[$i];
				$info = $purchase_order_model->stopPurchaseOrder($ids[$i],true);
				if($info){$data[] = array('status'=>1,"purchase_no"=>$purchase_nos[$i],"info"=>$info);}
			}
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$data[] = array('status'=>1,"purchase_no"=>$err_id,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data[] = array('status'=>1,"purchase_no"=>$err_id,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	public function printPurchase(){
		try{
			$ids = I('get.ids');
			$fields = 'rec_id as id,title as name,content';
			$model = D('Setting/PrintTemplate');
			$dialog_div = 'purchase_print_dialog';
			$result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','采购单_%')))->order('is_default desc')->select();
			foreach($result as $key){
				$contents[$key['id']] = $key['content'];
			}
			$ids_arr = explode(',',$ids);
			foreach($ids_arr as $id){
				$purchase_goods = D('PurchaseOrder')->getPurchaseOrderDetailPrintData($id);
				foreach($purchase_goods as $v){
					if(!isset($no[$v['id']]))
						$no[$v['id']] = 0;
					$v['no'] = ++$no[$v['id']];
					$purchase_detail[$v['id']][] = $v;
				}
			}
			$list = UtilDB::getCfgRightList(array('warehouse'));
			$this->assign('warehouse_list', $list['warehouse']);
			$this->assign('contents',json_encode($contents));
			$this->assign('purchase_detail',json_encode($purchase_detail));
			$this->assign('dialog_div',$dialog_div);
			$this->assign('goods_template',$result);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
		}

		$this->display('print_purchase_order');
	}
	public function printPurchaseOrderLog($ids){
		try{
			if(empty($ids)){return;}
			$operator_id = get_operator_id();
			$ids = explode(',',$ids);
			for($i = 0; $i < count($ids); $i++) {
				$log_data[] = array(
					'purchase_id' => $ids[$i],
					'operator_id' => $operator_id,
					'type' => 13,
					'remark' => '打印采购单'
				);
			}
			D('PurchaseOrderLog')->addAll($log_data);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
	}
	public function exportToExcel(){
		if(!self::ALLOW_EXPORT){
			echo self::EXPORT_MSG;
			return false;
		}
		try{
			$id_list = I('get.id_list');
			$type = I('get.type');
			$result = array('status' => 0,'info' => '');
			$search = I('get.search','',C('JSON_FILTER'));
			foreach($search as $k => $v){
				$key = substr($k,7,strlen($k)-8);
				$search[$key] = $v;
				unset($search[$k]);
			}
			D('PurchaseOrder')->exportToExcel($id_list,$search,$type);
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
			\Think\Log::write($e->getMessage());
		}catch(\Exception $e){
			$result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
			\Think\Log::write(parent::UNKNOWN_ERROR);
		}
		echo $result['info'];
	}
	public function getGoodsAmount(){
		try{
			$search = I('get.search','',C('JSON_FILTER'));
			foreach ($search as $k => $v)
			{
				$key=substr($k,7,strlen($k)-8);
				$search[$key]=$v;
				unset($search[$k]);
			}
			$goods_amount_sum=D('PurchaseOrder')->getGoodsAmount($search);
		}catch (BusinessLogicException $e){
			$goods_amount_sum=0;
		}catch (Exception $e){
			$goods_amount_sum=0;
		}
		$this->ajaxReturn($goods_amount_sum);
	}
	
}