<?php 
namespace Stock\Controller;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Common\Controller\BaseController;
use Think\Model;
use Stock\Common\Fields;
use Common\Common;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
//盘点管理类
class StockInventoryManagementController extends BaseController{
	
	public function getInventoryManagementList(){	
		try{
		$idList['id_list'] = self::getIDList($idList['id_list'],array('file_form','tool_bar','add','edit','select','form','tab_container','hidden_flag','datagrid','delete','file_dialog'),"");
		$fields = get_field('StockInventory','stockinventorymanagement');
		$idList['datagrid'] = array(
            'id' => $idList['id_list']['datagrid'],
            'options' => array(
                'title' => '',
                'url' => U("Stock/StockInventoryManagement/loadDataByCondition"),
                'toolbar' => "#{$idList['id_list']['tool_bar']}",
                'fitColumns' => false,
                'singleSelect'=>false,
                'ctrlSelect'=>true,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
        );
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($idList['datagrid']['fields'],$checkbox);
		//$idList = Common\DatagridExtention::getRichDatagrid('StockInventory','stockinventorymanagement',U("Stock/StockInventoryManagement/loadDataByCondition"));

        $arr_tabs = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stock_pd_detail")).'?prefix=stockinvenmanage&tab=stockPdDetail&app=StockInventory',"id"=>$idList['id_list']['tab_container'],"title"=>"盘点单详情"),
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stock_pd")).'?prefix=stockinvenmanage&tab=stockPd',"id"=>$idList['id_list']['tab_container'],"title"=>"日志"),
        );
        $arr_tabs = json_encode($arr_tabs);


        $params['datagrid'] = array();
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $params['form']['id'] = 'stockinventory_search_form';
        $params['search']['form_id'] = 'stockinventory_search_form';

        $params['tabs']['id'] = $idList['id_list']['tab_container'];
        $params['tabs']['url'] = U('StockCommon/showTabDatagridData');
        /**
         *修改出库单管理对象的时候或者修改编辑对话框的id的时候应该修改这里
         * 向对话框中的界面传递父界面的创建对象和当前对话框的id
         */
        $params['edit'] = array(
            'id'        => $idList['id_list']['edit'], 
            'url'       => U('Stock/StockInventory/getStockInventoryList')."?editDialogId=".$idList['id_list']['edit']."&stockManagementObject=StockInventoryManagement&type=add", 
            'title'     => '盘点单编辑',
            'height'    => '600',
            'width'     => '800'
        );
        
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("StockInventoryManagement/loadDataByCondition");
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];

        $this->assign("params", json_encode($params));
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('tool_bar', $idList['id_list']['tool_bar']);
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
		$this->assign("edit", $params['edit']['id']);
        $this->assign("datagrid_id", $idList['id_list']['datagrid']);

        $list = UtilDB::getCfgRightList(array('logistics','employee','warehouse'));
        $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
        $employee_default['0'] = array('id' => 'all', 'name' => '全部');
        $employee_array = array_merge($employee_default, $list['employee']);
		$this->assign('warehouse_array', $warehouse_array);
        $this->assign('employee_array', $employee_array);
		} catch (\PDOException $e) {
					$msg = $e->getMessage();
					\Think\Log::write($msg);
					
				} catch (\Exception $e) {
					$msg = $e->getMessage();
					\Think\Log::write($msg);
					
				}
        $this->display('show');
	
	} 
	
	public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc'){
		try{
		$data = D('StockInventoryManagement')->searchStockinvenmanList($page, $rows, $search, $sort, $order,'stockinvenmanage');
		}
		catch(BusinessLogicException $e){
            $data=array('total'=>0,'rows'=>array());
        }
		catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$data=array('total'=>0,'rows'=>array());				
		}
		$this->ajaxReturn($data);
	}
	
	 public function deleteStockInvenOrder($ids = 0)
    {
        try{
			$result = D('StockInventory')->deleteStockInvenOrder($ids);
		}
		catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info'] = $e->getMessage();
        }
		catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$result['status'] = 1; 
			$result['info'] = self::UNKNOWN_ERROR;
		}
		$this->ajaxReturn($result);
    }
	public function submitStockInvenOrder($ids = 0,$pd_nos=0){
		try{
            $ids = explode(',',$ids);
            $pd_nos = explode(',',$pd_nos);
            $stock_spec_model = D('Stock/StockSpec');
            $err_id = 0;
            for($i = 0; $i < count($ids); $i++){
                //$msg[$pd_nos[$i]] = $stock_spec_model->submitPd($ids[$i]);
                $err_id = $pd_nos[$i];
                $info = $stock_spec_model->submitPd($ids[$i],true);
                if($info){$msg[] = array("pd_no"=>$pd_nos[$i],"info"=>$info);}
            }
		}
		catch(BusinessLogicException $e){
            $message = $e->getMessage();
            $msg[] = array("pd_no"=>$err_id,"info"=>$message);
        }
		catch (\Exception $e) {
			$message = $e->getMessage();
			\Think\Log::write($message);
            $message = self::UNKNOWN_ERROR;
            $msg[] = array("pd_no"=>$err_id,"info"=>$message);
		}
        $this->ajaxReturn($msg);
	}
	public function upload(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["msg"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $result = array(
            'status' => 0,
            'data'=>array(),
            'msg'=>'成功'
        );
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        try {
		
            //将表格读取为数据
            $excelData = UtilTool::Excel2Arr($name, $file, "StockSpecImport");
        } catch(BusinessLogicException $e){
            $res["status"] = 1;
            $res["msg"]   = $e->getMessage();
            $this->ajaxReturn(json_encode($res),'EVAL');
        } catch (\Exception $e) {
			
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
            $res["msg"]   = "文件错误，无法读取";
            $this->ajaxReturn(json_encode($res),'EVAL');
        }
        try {
			
            $model = D('Stock/StockInventoryManagement');
            $fail_result = array();
            $data = array();
            $merchant_no =array();
            foreach ($excelData as $sheet) {
				$field_name_list = $sheet[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '仓库名称' || $field_name_list[2] != '库存数量'){
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
                        'warehouse_name'    => trim($row[++$i]),//仓库名称
                        'num'               => trim($row[++$i]),//盘点数量
                        'position_no'       => trim($row[++$i]),//货位编号
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
                        }elseif($temp_key=='num'&&empty($temp_value)){
                            $temp_data[$temp_key]=0;
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
						$data[$key]['message'] ='仓库，商家编码不存在';
						$data[$key]['result'] ='失败';
				}elseif($merchant_no["{$value['warehouse_name']}"]["{$value['spec_no']}"] == false){
					if(empty($value['warehouse_name']) && !empty($value['spec_no'])){
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='仓库不存在';
						$data[$key]['result'] ='失败';
					}elseif(!empty($value['warehouse_name']) && empty($value['spec_no'])){
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='商家编码不存在';
						$data[$key]['result'] ='失败';
					}elseif(empty($value['warehouse_name']) && empty($value['spec_no'])){
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='仓库，商家编码不存在';
						$data[$key]['result'] ='失败';
					}else{
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='导入仓库['.$value['warehouse_name'].']商家编码重复';
						$data[$key]['result'] ='失败';
					}	
                }
            }
            if(empty($data)){
                E('读取导入的数据失败!');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['msg'] =$msg;
			$this->ajaxReturn(json_encode($result),'EVAL');
        }
        $sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_no` varchar(40),`position_no` varchar(40) DEFAULT '',`position_id` int(11),`warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`num` decimal(19,4),`price` decimal(19,4),`warehouse_id` smallint(6),`spec_id` int(11),`cost_price` decimal(19,4),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $model->execute($sql_drop);
        $model->importStockSpec($data,$result);
        $model->execute("DELETE FROM tmp_import_detail");




        $this->ajaxReturn(json_encode($result),'EVAL');
	}
	
	public function downloadTemplet(){
        $file_name = "盘点导入模板.xls";
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
            D('StockInventoryManagement')->exportToExcel($id_list,$search,$type);
        }catch(BusinessLogicException $e){
            $result = array('status'=>1,'info'=>$e->getMessage());
            \Think\Log::write($e->getMessage());
        }catch(\Exception $e){
            $result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
            \Think\Log::write(parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}