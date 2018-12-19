<?php
namespace Purchase\Controller;
use Think\Model;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Log;
use Common\Common\UtilDB;
class SupplierGoodsController extends BaseController {
	public function _initialize(){
			parent::_initialize();
			parent::getIDList($this->id_list,array('form','toolbar','datagrid','add','edit','add_form','edit_form','fileForm','fileDialog'),CONTROLLER_NAME);
	}
	public function show(){
		$id_list = $this->id_list;
		$datagrid = array(
			"id"=>$id_list['datagrid'],
			"options"=>array(
				"url"=>U("SupplierGoods/search"),
				"toolbar"=>$id_list['toolbar'],
				"fitColumns" => false,
				"rownumbers" => true,
				"pagination" => true,
				"method"     => "post",
				'singleSelect' => false,
				'ctrlSelect'   => true
			),
			"fields"=>get_field('SupplierGoods','supplier'),
		);
		$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'add'=>array('id'=>$id_list['add'],'url'=>U('Purchase/SupplierGoods/add'),'title'=>'添加供货商货品'),
			);
			 $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
//		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
		$provider = UtilDB::getCfgRightList(array('provider'),array('provider'=>array('is_disabled'=>0)));
		$provider_default['0'] = array('id' => 'all','name'=>'全部');
		$provider_array = array_merge($provider_default,$provider['provider']);
		$this->assign('params',json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->assign('provider',$provider_array);
		$this->display('show');
	}
	public function search($page = 1,$rows=20,$search=array(),$sort="rec_id",$order="desc"){
		try{
			$data = D('SupplierGoods')->search($page,$rows,$search,$sort,$order);
		}catch(BusinessLogicException $e){
			$data = array("total"=>0,"rows"=>array());
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$data = array("total"=>0,"rows"=>array());
			\Think\Log::write($msg);
		}
		$this->ajaxReturn($data);
	}
	public function add(){
		$data = I('','',C('JSON_FILTER'));
		$dialog = $data['dialog_id'];	
		$datagrid_id = $data['datagrid_id'];
		$this->getIDList($id_list,array('form','toolbar','datagrid','add','edit'),'','AddGoods');
		$datagrid = array(
			"id" => $id_list["datagrid"],
			"options" => array(
				"toolbar" => $id_list["toolbar"],
				"fitColumns" => false,
                "rownumbers" => true,
                "pagination" => false,
                "singleSelect"=>false,
                "ctrlSelect" => true,
			),
		"fields"=>get_field('SupplierGoods','add'),
		);
		$params = array(
			"datagrid"=>$id_list['datagrid'],
			"form"=>$id_list['form'],
			"id_list"=>$id_list,
			"dialog" => array("supplier" => $id_list["add"]),
			"prefix"=>"supplier_addgoods",
			"parent_dialog"=>$dialog,
			"parent_dg"=>$datagrid_id,
		);
		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('gt',0)));
		$provider_data = $provider['data'];
		$this->assign("params",json_encode($params));
		$this->assign("datagrid",$datagrid);
		$this->assign("id_list",$id_list);
		$this->assign("provider",$provider_data);
		$this->display("dialog_add");
	}
	public function getSupplier(){
		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('gt',0)));
		$provider_data = $provider['data'];
		$this->ajaxReturn($provider_data);
	}
	public function saveSupplier(){
		try{
			$result = array('status'=>0,'data'=>array(),'info'=>'');
			$data = I('','',C('JSON_FILTER'));
			$data  = $data['info'];
			D('Purchase/SupplierGoods')->saveSupplier($data);
			
		}catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['info'] = $msg;
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }

        $this->ajaxReturn($result);
	}
	public function remove(){
		try{
			$result = array('status'=>0,'info'=>'','data'=>array());
			$data = I('','',C('JSON_FILTER'));
			$id = $data['id'];
			foreach($id as $k=>$v){
				D('Purchase/SupplierGoods')->remove($v['id']);
			}
		}catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['info'] = $msg;
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }	
		$this->ajaxReturn($result);
	}
	public function uploadExcel(){
		
			$result = array('status'=>0,'msg'=>'成功','data'=>array());
			$file = $_FILES['file']['tmp_name'];
			$name = $_FILES['file']['name'];
		try{	
			$exceldata = UtilTool::Excel2Arr($name,$file,'StockSpecImport');	
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>'文件错误，无法读取','data'=>array());
			$this->ajaxReturn($result);
        }	
		try{
			$model = D('Purchase/SupplierGoods');
			$data = array();
			$default = array();
			foreach($exceldata as $sheet){
				$field_name_list = $sheet[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '供应商' || $field_name_list[2] != '采购价'){
					SE('导入模板不正确，请先下载模板');
				}
				for($k = 1;$k<count($sheet);$k++){
					$i = 0;
					$line = $k+1;
					$row = $sheet[$k];
					$cf = array();
					$is_isset = 1;
					$tmp_data = array(
						'spec_no' =>trim($row[$i]),
						'provider_name'=>trim($row[++$i]),
					//	'provider_goods_no'=>trim($row[++$i]),
						'price'=>trim($row[++$i]),
						'line'=>$line,
						'status'=>0,
						'result'=>'成功',
						'message'=>''
						
					);
				
					if(isset($cf["{$tmp_data['spec_no']}"]["{$tmp_data['provider_name']}"])){
						$cf["{$tmp_data['spec_no']}"]["{$tmp_data['provider_name']}"] = false;
					}else{
						$cf["{$tmp_data['spec_no']}"]["{$tmp_data['provider_name']}"] = true;
					}
					foreach($tmp_data as $key=>$value){
						if(!empty($value) && $tmp_data[$key]!='line' && $tmp_data[$key]!='status' && $tmp_data[$key]!='result'&& $tmp_data[$key]!='message'){
							$is_isset = 0;
						}
					}
					if($is_isset == 0 ){
						$data[] = $tmp_data;
					}
				}
			}
			foreach($data as $k=>$v){
				if($cf["{$v['spec_no']}"]["{$v['provider_name']}"] == false){
					$v['status'] = 1;
					$v['result'] = '失败';
					$v['message'] = '导入供应商'.$v['provider_name'].'商家编码重复';
				}
			}
			 if(empty($data)){
                E('读取导入的数据失败!');
            }
				
		}catch(\Exception $e){
			\Think\Log::write(__CONTROLLER__.'--supplieruploadexcel---'.$e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
			$this->ajaxReturn($result);
			
		}
		
		$model->execute("create temporary table if not exists `tmp_import_detail` (`rec_id` int(11) not null auto_increment,`spec_no` varchar(30),`spec_id` int(11),`provider_name` varchar(30),`provider_id` int(11),`provider_goods_no` varchar(40),`price` decimal(19,4),`line` SMALLINT,`status` TINYINT,`result` varchar(30),`message` varchar(60),primary key(`rec_id`))ENGINE = InnoDB DEFAULT CHARSET=utf8;");
		$model->uploadExcel($data,$result);
		$model->execute("delete from tmp_import_detail");
		
		 $this->ajaxReturn(json_encode($result),'EVAL');
	}
	public function downloadTemplet(){
		$file_name = "货品关联模板.xls";
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
}
