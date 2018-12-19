<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Think\Model;
use Stock\StockCommonField;
use Stock\StockTransManagementField;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Platform\Common\ManagerFactory;
/**
 *调拨单管理类
 *@package Stock\Controller
 */
 class StockTransManagementController extends BaseController{
	public function _initialize(){
			parent::_initialize();
			parent::getIDList($this->id_list,array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete','position_import_dialog','position_import_form','spec_import_dialog','spec_import_form'));
		}
	/**
     *初始化调拨单管理
     */
	public function getTransferList(){ 
		$id_list=$this->id_list;
		$datagrid = array(
                    "id"      => $id_list["datagrid"],
                    "options" => array(
						"url"        =>U("StockTransManagement/search"),
						"toolbar"    => $id_list["toolbar"],
						"fitColumns" => false,
						"rownumbers" => true,
						"pagination" => true,
						"method"     => "post",
						'singleSelect'=>false,
						'ctrlSelect'=>true,
                ), 
                "fields"  => get_field('StockTransManagement','stocktransmanagement'),
            );
		$checkbox=array('field' => 'ck','checkbox' => true);
		array_unshift($datagrid['fields'],$checkbox);
			
			$params = array(
				'datagrid'=>array( 
					'id'     =>    $id_list['datagrid'],
				),
				'search'=>array(
					'form_id'    =>  $id_list['form'],
					'more_content' => $id_list['more_content'],
					'hidden_flag' => $id_list['hidden_flag'],
				),
				'form'  =>array(
					'id'=>$id_list['form'],
					),
				'tabs' =>array(
					'id' =>$id_list['tab_container'],
					'url'=>U('StockCommon/showTabDatagridData'),
				),
				'edit'     => array('id' => $id_list['edit'], 'url' => U('Stock/StockTransManagement/edit'), 'title' => '调拨编辑'),

			);
			$arr_tabs = array(
					 array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stock_trans_detail")).'?prefix=stocktransmanagement&tab=stock_trans_detail',"id"=>$id_list['tab_container'],"title"=>"调拨单详情"),
					 array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stock_trans_log")).'?prefix=stocktransmanagement&tab=stock_trans_log',"id"=>$id_list['tab_container'],"title"=>"日志"),
				 );  
		   
		    $list = UtilDB::getCfgRightList(array('employee','brand','warehouse'));
			$warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
			$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
			$brand_default['0'] = array('id' => 'all', 'name' => '全部');
			$brand_array = array_merge($brand_default, $list['brand']);
			$employee_default['0'] = array('id' => 'all', 'name' => '全部');
			$employee_array = array_merge($employee_default, $list['employee']);
			
			$this->assign('warehouse_array', $warehouse_array);
			$this->assign('employee_array', $employee_array);
			$this->assign('brand_array', $brand_array);
			$this->assign('params', json_encode($params));
			$this->assign('datagrid',$datagrid);
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign("id_list", $id_list);
			$this->display('show');
		}
		
	 public function search($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
		 try{
            $data = D('StockTransfer')->search($page,$rows,$search,$sort,$order);
        }catch (\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array('total' => 0, 'rows' => array());
        }
        $this->ajaxReturn($data);
	}
	
	/**
	 *取消调拨单
	 *@param int $id
	 */
	public function cancelTransOrder($ids){
		try{
			$data=D('StockTransfer')->cancelTransOrder($ids);
		}catch (\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
        }
			$this->ajaxReturn($data);
	}
	
	/**
	 *提交调拨单
	 *@param $data
	 */
	   public function submitStockTransOrder($ids,$is_force=false,$transfer_nos=0){
		try{
			$result['status']=0;
			$result['info']="提交成功";
			$ids = explode(',',$ids);
			$transfer_nos = explode(',',$transfer_nos);
			$stock_trans_model = D("StockTransfer");
			for($i = 0; $i < count($ids); $i++){
				$result=$stock_trans_model->submitStockTransOrder($ids[$i],$is_force,true);
				if($result['status']==1){
					$rst[] = array("transfer_no"=>$transfer_nos[$i],"status"=>1,"info"=>$result['info']);
				}else{
					$rst[] = array("transfer_no"=>$transfer_nos[$i],"status"=>0,"info"=>'提交成功！',"goods_in_count"=>$result['goods_in_count'],"goods_out_count"=>$result['goods_out_count']);
				}
			}
		}catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info'] = $e->getMessage();
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result['status'] = 1;
            $result['info'] = self::UNKNOWN_ERROR;
        }
	    //if($result['status']==1){$this->ajaxReturn($rst);}
        $this->ajaxReturn($rst);
	 }
	 public function edit(){

		 $edit_info = I('','',C('JSON_FILTER'));
		 $id = $edit_info['id'];
		 $management_info = $edit_info['management_info'];
		 D('Stock/StockTransfer','Controller')->edit($id,$management_info);
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
            $result['info'] ="请选择调拨单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$trans_info_fields = array("st.status");
				$trans_info_cond = array(
					'st.rec_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/StockTransfer')->alias('st')->field($trans_info_fields)->where($trans_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				$WmsManager = ManagerFactory::getManager("Wms");
				if ((int)$res_so_info['status'] == 42 || (int)$res_so_info['status'] == 44)
				{
					$WmsManager->manual_wms_adapter_add_transfer_out($sid, $uid, $id);
				}
				elseif((int)$res_so_info['status'] == 50 || (int)$res_so_info['status'] == 62 || (int)$res_so_info['status'] == 64)
				{
					$WmsManager->manual_wms_adapter_add_transfer_in($sid, $uid, $id);
				}else{
					SE('调拨单状态不对！');
				}
					
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
	
	public function cancel_st($ids)
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
            $result['info'] ="请选择调拨单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$trans_info_fields = array("st.status");
				$trans_info_cond = array(
					'st.rec_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/StockTransfer')->alias('st')->field($trans_info_fields)->where($trans_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				$WmsManager = ManagerFactory::getManager("Wms");
				if ((int)$res_so_info['status'] == 46)
				{
					$WmsManager->manual_wms_adapter_cancel_transfer_out($sid, $uid, $id);
				}
				elseif((int)$res_so_info['status'] == 66)
				{
					$WmsManager->manual_wms_adapter_cancel_transfer_in($sid, $uid, $id);
				}elseif((int)$res_so_info['status'] == 62 || (int)$res_so_info['status'] == 42){
					D('Stock/StockTransfer')->cancel_st($id);
				}else{
					SE('调拨单状态不对！');
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
			 D('StockTransfer')->exportToExcel($id_list,$search,$type);
		 }catch(BusinessLogicException $e){
			 $result = array('status'=>1,'info'=>$e->getMessage());
			 \Think\Log::write($e->getMessage());
		 }catch(\Exception $e){
			 $result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
			 \Think\Log::write(parent::UNKNOWN_ERROR);
		 }
		 echo $result['info'];
	 }
	 public function position_import_upload(){
		 if(!self::ALLOW_EXPORT){
			 $res["status"] = 1;
			 $res["msg"]   = self::EXPORT_MSG;
			 $this->ajaxReturn(json_encode($res), "EVAL");
		 }
		 //获取表格相关信息
		 $result = array('status' => 0,'data'=>array(),'msg'=>'成功');
		 $file = $_FILES["file"]["tmp_name"];
		 $name = $_FILES["file"]["name"];
		 try {
			 //将表格读取为数据
			 $excelData = UtilTool::Excel2Arr($name, $file, "PositionTransImport");
		 } catch (\Exception $e) {
			 \Think\Log::write($e->getMessage());
			 $res["status"] = 1;
			 $res["msg"]   = "文件错误，无法读取";
			 $this->ajaxReturn(json_encode($res),'EVAL');
		 }
		 try {
			 $model = D('Stock/StockTransfer');
			 $fail_result = array();
			 $data = array();
			 $merchant_no =array();
			 foreach ($excelData as $sheet) {
				 $field_name_list = $sheet[0];
				 if($field_name_list[0] != '商家编码' || $field_name_list[1] != '调拨仓库' || $field_name_list[2] != '调入货位'){
					 SE('导入模板不正确，请先下载模板');
				 }
				 for ($k = 1; $k < count($sheet); $k++) {
					 $row = $sheet[ $k ];
					 //分类存储数据
					 $i                                  = 0;
					 $line = $k+1;
					 $is_full_row = 1; //1  标示为空行,需要屏蔽
					 $temp_data = array(
						 "spec_no"           => trim($row[$i]),//商家编码
						 'warehouse_name'    => trim($row[++$i]),//调拨仓库
						 'to_position_no'    => trim($row[++$i]),//调入货位
						 'line'              => trim($line),//行号
						 'status'            => 0,
						 'message'           =>'',
						 'result'            =>'成功'
					 );
					 if(isset($merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"])){
						 $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = false;
					 }else{
						 $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = true;
					 }
					 foreach($temp_data as $temp_key => $temp_value){
						 if(!empty($temp_value)&& $temp_key!='line'&& $temp_key!='message'&& $temp_key!='result'&& $temp_key!='status'){
							 $is_full_row = 0;
						 }
					 }
					 if($is_full_row == 0){
						 $data[] = $temp_data;
					 }
				 }
			 }
			 //筛选重复的商家编码
			 foreach($data as $key => $value)
			 {
				 if(empty($value['warehouse_name']) && empty($value['spec_no'])){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='调拨仓库，商家编码不存在';
					 $data[$key]['result'] ='失败';
				 }elseif($merchant_no["{$value['warehouse_name']}"]["{$value['spec_no']}"] == true){
					 if(empty($value['warehouse_name']) && !empty($value['spec_no'])){
						 $data[$key]['status'] = 1;
						 $data[$key]['message'] ='调拨仓库不存在';
						 $data[$key]['result'] ='失败';
					 }elseif(!empty($value['warehouse_name']) && empty($value['spec_no'])){
						 $data[$key]['status'] = 1;
						 $data[$key]['message'] ='商家编码不存在';
						 $data[$key]['result'] ='失败';
					 }elseif(empty($value['warehouse_name']) && empty($value['spec_no'])){
						 $data[$key]['status'] = 1;
						 $data[$key]['message'] ='调拨仓库，商家编码不存在';
						 $data[$key]['result'] ='失败';
					 }elseif(empty($value['to_position_no'])){
						 $data[$key]['status'] = 1;
						 $data[$key]['message'] ='调入货位不能为空';
						 $data[$key]['result'] ='失败';
					 }
					 /*else{
						 $data[$key]['status'] = 1;
						 $data[$key]['message'] ='调拨仓库['.$value['warehouse_name'].']商家编码重复';
						 $data[$key]['result'] ='失败';
					 }*/
				 }
			 }
			 if(empty($data)){
				 E('读取导入的数据失败!');
			 }
		 } catch (\Exception $e) {
			 $msg = $e->getMessage();
			 \Think\Log::write(__CONTROLLER__.'--position_import_upload--'.$msg);
			 $result['status'] = 1;
			 $result['msg'] =$msg;
			 $this->ajaxReturn(json_encode($result),'EVAL');
		 }
		 $sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_no` varchar(40),`from_position_id` int(11),`to_position_no` varchar(40) DEFAULT '',`to_position_id` int(11),`warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`warehouse_id` smallint(6),`spec_id` int(11),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		 $model->execute($sql_drop);
		 $model->importPositionTrans($data,$result);
		 $model->execute("DELETE FROM tmp_import_detail");
		 $this->ajaxReturn(json_encode($result),'EVAL');
	 }
	 public function downloadTemplet(){
		 $type = I('get.type');
		 if($type == 'position_transfer'){
			 $file_name = "货位调拨导入模板.xls";
		 }elseif($type == 'spec_transfer'){
			 $file_name = "单品调拨导入模板.xls";
		 }
		 $file_sub_path = APP_PATH."Runtime/File/";
		 try{
			 ExcelTool::downloadTemplet($file_name,$file_sub_path);
		 } catch (BusinessLogicException $e){
			 \Think\Log::write($e->getMessage());
			 echo '对不起，模板不存在，下载失败！';
		 } catch (\Exception $e) {
			 \Think\Log::write($e->getMessage());
			 echo parent::UNKNOWN_ERROR;
		 }
	 }
	 public function spec_import_upload(){
		 if(!self::ALLOW_EXPORT){
			 $res["status"] = 1;
			 $res["msg"]   = self::EXPORT_MSG;
			 $this->ajaxReturn(json_encode($res), "EVAL");
		 }
		 //获取表格相关信息
		 $result = array('status' => 0,'data'=>array(),'msg'=>'成功');
		 $file = $_FILES["file"]["tmp_name"];
		 $name = $_FILES["file"]["name"];
		 try {
			 //将表格读取为数据
			 $excelData = UtilTool::Excel2Arr($name, $file, "SpecTransImport");
		 } catch (\Exception $e) {
			 \Think\Log::write($e->getMessage());
			 $res["status"] = 1;
			 $res["msg"]   = "文件错误，无法读取";
			 $this->ajaxReturn(json_encode($res),'EVAL');
		 }
		 try {
			 $model = D('Stock/StockTransfer');
			 $fail_result = array();
			 $data = array();
			 $merchant_no =array();
			 foreach ($excelData as $sheet) {
				 $field_name_list = $sheet[0];
				 if($field_name_list[0] != '商家编码' || $field_name_list[1] != '调出仓库' || $field_name_list[2] != '目标仓库' || $field_name_list[3] != '调入货位' || $field_name_list[4] != '调拨数量'){
					 SE('导入模板不正确，请先下载模板');
				 }
				 for ($k = 1; $k < count($sheet); $k++) {
					 $row = $sheet[ $k ];
					 //分类存储数据
					 $i                                  = 0;
					 $line = $k+1;
					 $is_full_row = 1; //1  标示为空行,需要屏蔽
					 $temp_data = array(
						 "spec_no"           		=> trim($row[$i]),//商家编码
						 'from_warehouse_name'      => trim($row[++$i]),//调出仓库
						 'to_warehouse_name'    	=> trim($row[++$i]),//目标仓库
						 'to_position_no'    		=> trim($row[++$i]),//调入货位
						 'num'    					=> trim($row[++$i]),//调拨数量
						 'line'              		=> trim($line),//行号
						 'status'            		=> 0,
						 'message'           		=>'',
						 'result'            		=>'成功'
					 );
					 /*if(isset($merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"])){
						 $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = false;
					 }else{
						 $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = true;
					 }*/
					 foreach($temp_data as $temp_key => $temp_value){
						 if(!empty($temp_value)&& $temp_key!='line'&& $temp_key!='message'&& $temp_key!='result'&& $temp_key!='status'){
							 $is_full_row = 0;
						 }
					 }
					 if($is_full_row == 0){
						 $data[] = $temp_data;
					 }
				 }
			 }
			 //筛选重复的商家编码
			 foreach($data as $key => $value)
			 {
				 if(empty($value['spec_no'])){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='商家编码不能为空';
					 $data[$key]['result'] ='失败';
				 }elseif(empty($value['from_warehouse_name'])){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='调拨仓库不能为空';
					 $data[$key]['result'] ='失败';
				 }elseif(empty($value['to_warehouse_name'])){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='目标仓库不能为空';
					 $data[$key]['result'] ='失败';
				 }elseif(empty($value['num']) || $value['num'] <= 0){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='调拨数量不能为0，负数，空值';
					 $data[$key]['result'] ='失败';
				 }elseif($value['from_warehouse_name'] == $value['to_warehouse_name']){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='调出仓库和目标仓库不能相同';
					 $data[$key]['result'] ='失败';
				 }elseif(intval($value['num']) != $value['num']){
					 $data[$key]['status'] = 1;
					 $data[$key]['message'] ='调拨数量必须是整数';
					 $data[$key]['result'] ='失败';
				 }
			 }
			 if(empty($data)){
				 E('读取导入的数据失败!');
			 }
		 } catch (\Exception $e) {
			 $msg = $e->getMessage();
			 \Think\Log::write(__CONTROLLER__.'--spec_import_upload--'.$msg);
			 $result['status'] = 1;
			 $result['msg'] =$msg;
			 $this->ajaxReturn(json_encode($result),'EVAL');
		 }
		 $sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_no` varchar(40),`from_position_id` int(11),`to_position_no` varchar(40) DEFAULT '',`to_position_id` int(11),`from_warehouse_name` varchar(64),`to_warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`num` decimal(19,4),`from_warehouse_id` smallint(6),`to_warehouse_id` smallint(6),`spec_id` int(11),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		 $model->execute($sql_drop);
		 $model->importSpecTrans($data,$result);
		 $model->execute("DELETE FROM tmp_import_detail");
		 $this->ajaxReturn(json_encode($result),'EVAL');
	 }
 }
