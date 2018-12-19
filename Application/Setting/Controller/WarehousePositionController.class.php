<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Log;
class WarehousePositionController extends BaseController{
	public function _initialize(){
			parent::_initialize();
			parent::getIDList($this->id_list,array('form','toolbar','datagrid','add','edit','add_form','edit_form','fileForm','fileDialog'),CONTROLLER_NAME);
		}

	public function getPosition(){
		$id_list=$this->id_list;
		$datagrid=array(
			"id"=>$id_list['datagrid'],
			"options"=>array(
			"url"=>U("WarehousePosition/search"),
			"toolbar"    => $id_list["toolbar"],
			"fitColumns" => false,
			"rownumbers" => true,
			"pagination" => true,
			"method"     => "post",
		),
			"fields"=>get_field("WarehousePosition",'warehouse_position'),
		);
						
		$params=array(
			'datagrid'=>array(
				'id'=>$id_list['datagrid'],
				),
			'search'=>array(
				'form_id'=>$id_list['form'],
				),
			'add'=>array(
				'id'=>$id_list['add'],
				'title'=>'新建货位',
				'url'=>U('Setting/WarehousePosition/dialogAddPosition'),
				'width'=>'300',
				'height'=>'200',
				'ismax'	 =>  false
				),
			'edit'=>array(
				'id'=>$id_list['edit'],
				'title'=>'修改货位',
				'url'=>U('Setting/WarehousePosition/dialogEditPosition'),
				'width'=>'300',
				'height'=>'200',
				'ismax'	 =>  false
				),
			);
		$list = UtilDB::getCfgList(array('warehouse'));
		$warehouse_default[0] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
		$this->assign('warehouse_array', $warehouse_array);
		$this->assign('params', json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign("id_list", $id_list);
		$this->display('show');
	}

	public function search($page = 1, $rows = 20, $search = array(), $sort = 'cwp.warehouse_id', $order = 'desc'){
		try{
			$data=D('WarehousePosition')->searchPosition($page,$rows,$search,$sort,$order);
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$data=array('total'=>0,'rows'=>array());
		}catch(BusinessLogicException $e){
			$data=array('total'=>0,'rows'=>array());
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			$data=array('total'=>0,'rows'=>array());
		}
		$this->ajaxReturn($data);
	}

	public function dialogAddPosition(){
		try{
			$position_info=array('id'=>0);
			$dialog_list=array(
				'form'=>$this->id_list['add_form']);
			$list = UtilDB::getCfgList(array('warehouse'));
			$this->assign('warehouse_array', $list['warehouse']);
			$this->assign('position_info',json_encode($position_info));
			$this->assign('dialog_list',$dialog_list);
			$this->assign('dialog_list_json',json_encode($dialog_list));
			$this->display('dialog_warehouse_position_edit');
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
	}

	public function dialogEditPosition($id){
		$id=intval($id);
		try{
			$position_info=D('WarehousePosition')->getEditPositionData($id);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->error(self::PDO_ERROR);
		}
		$list = UtilDB::getCfgList(array('warehouse'));
		$this->assign('warehouse_array', $list['warehouse']);
		$dialog_list=array('form'=>$this->id_list['edit_form']);
		$this->assign('position_info',json_encode($position_info));
		$this->assign('dialog_list',$dialog_list);
		$this->assign('dialog_list_json',json_encode($dialog_list));
		$this->display('dialog_warehouse_position_edit');
	}

	public function savePosition(){
			$arr=I('','',C('JSON_FILTER'));
			$result=array('status'=>0,'info'=>"");
			$arr['rec_id'] = $arr['id'];
			try{
				$result=D('WarehousePosition')->savePosition($arr);
			}catch(BusinessLogicException $e){
				$result=array('status'=>1,'info'=>$e->getMessage());
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result['status']=1;
				$result['info']=$e->getMessage();
			}
			$this->ajaxReturn($result);
	}

	public function getWarehousePositioninfo()
	{
		$id_list = array();

		$need_ids = array('form','toolbar','datagrid','tab_container');
		$get_info = I('get.','',C('JSON_FILTER'));
//		$prefix = $get_info['prefix'];
		$this::getIDList($id_list,$need_ids,$get_info['prefix'],'');
		$datagrid = array(
			'id'=>$id_list['datagrid'],
			'style'=>'',
			'class'=>'',
			'options'=> array(
				'title' => '',
				'url'   =>U('Setting/WarehousePosition/search', array('grid'=>'datagrid')).'?search[warehouse_id]='.$get_info['warehouse_id'].'&search[is_disabled]=0',
				'toolbar' =>"#{$id_list['toolbar']}",
				'fitColumns'=>false,
				'singleSelect'=>true,
				'ctrlSelect'=>false,
			),
			'fields'=>get_field('Setting/WarehousePosition','dialog_position')
		);
		$arr_tabs=array(
			array('id'=>$id_list['tab_container'],'url'=>U('Setting/SettingCommon/showTabsView',array('tabs'=>'position_list')).'?tab=position_spec&prefix='.$get_info['prefix'],'title'=>'货位货品信息'),
		);
		$params=array(
			'search'=>array(
				'form_id'=>$id_list['form'],
			),
			'datagrid'=>array('id'=>$id_list['datagrid']),
			'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('Setting/SettingCommon/updateTabsData')),
		);
		$this->assign("params",json_encode($params));
		$this->assign("id_list",$id_list);
		$this->assign('arr_tabs', json_encode($arr_tabs));
		$this->assign('datagrid', $datagrid);
		$this->assign('warehouse_id', $get_info['warehouse_id']);
		$this->display('dialog_warehouse_position');
	}

	//下载货位导入模板
	public function downloadTemplet(){
		$file_name = "货位导入模板.xls";
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

	public function uploadExcel(){
		//获取表格相关信息
		$file = $_FILES["file"]["tmp_name"];
		$name = $_FILES["file"]["name"];
		try{
			$sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
			$sql      = "CREATE TABLE IF NOT EXISTS `tmp_import_detail` (`rec_id` INT NOT NULL AUTO_INCREMENT,`id` SMALLINT,`status` TINYINT,`result` VARCHAR(30),`message` VARCHAR(60),PRIMARY KEY(`rec_id`))";
			$M = M();
			$M->execute($sql_drop);
			$M->execute($sql);
			$importDB = M("tmp_import_detail");
			$excelClass = new ExcelTool();
			$excelClass->checkExcelFile($name,$file);
			$excelClass->uploadFile($file,"WarehousePositionImport");
			$count = $excelClass->getExcelCount();
			//建立临时表，存储数据处理的结果
			$excelData = $excelClass->Excel2Arr($count);
			//如果tmp_import_detail表已存在，就删除并重新创建
			$res = array();
			//处理数据，将数据插入数据库并返回信息
			$WarehouseDB = D("WarehousePosition");
			foreach ($excelData as $sheet) {
				for ($k = 1; $k < count($sheet); $k++) {
					$row = $sheet[$k];
					if (UtilTool::checkArrValue($row)) continue;
					//分类存储数据
					$i = 0;
					$data["name"] = $row[$i]; //仓库名称
					$data["position_no"] = $row[$i+1]; //货位编号
					$M->startTrans();
					try{
						$WarehouseDB->importSpec($data);
						$M->commit();
					}catch (\Exception $e) {
						$M->rollback();
						$err_code = $e->getCode();
						if ($err_code == 0) {
							$err_msg = array("id" => $k + 1, "status" => $err_code, "message" => $e->getMessage(), "result" => "失败");
							$importDB->data($err_msg)->add();
						} else {
							$err_msg = array("id" => $k + 1, "status" => $err_code, "message" => "未知错误，请联系管理员", "result" => "失败");
							$importDB->data($err_msg)->add();
							Log::write($e->getMessage());
						}
					}

				}
			}

		}catch (BusinessLogicException $e){
			$res['status'] = 1;
			$res['info'] = $e->getMessage();
			$this->ajaxReturn(json_encode($res), "EVAL");
		}catch (\Exception $e){
			Log::write($e->getMessage());
			$res["status"] = 1;
			$res["info"]   = "未知错误，请联系管理员";
			$this->ajaxReturn(json_encode($res), "EVAL");
		}
		unset($data);
		try {
			$sql    = "SELECT id,status,result,message FROM tmp_import_detail";
			$result = $M->query($sql);
			if (count($result) == 0) {
				$res["status"] = 0;
				$res["info"]   = "操作成功";
			} else {
				$res["status"] = 2;
				$res["info"]   = $result;
			}
			$sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
			$M->execute($sql_drop);
		} catch (BusinessLogicException $e) {
			$res["status"] = 1;
			$res["info"] = $e->getMessage();
		}catch (\Exception $e) {
			$res["status"] = 1;
			$res["info"] = "未知错误，请联系管理员";
		}

		/*header('Con')}tent-Type:text/plain; charset=utf-8');
        exit(json_encode($res, 0));*/
		$this->ajaxReturn(json_encode($res), "EVAL");
	}


}