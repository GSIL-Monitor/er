<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/9/15
 * Time: 1:57 PM
 */
namespace Stock\Controller;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Common\Controller\BaseController;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Stock\Common\Fields;
use Platform\Common\ManagerFactory;

/**
 * 入库管理类
 * @package Stock\Controller
 */
class StockInManagementController extends BaseController{
    /**
     *初始化入库单管理
     */
    public function getStockInSpec(){
        //$idList = DatagridExtention::getRichDatagrid('StockIn','stockinmanagement',U("Stock/StockInManagement/loadDataByCondition"),'',array('tool_bar','add','edit','select','form','tab_container','more_content','hidden_flag','datagrid','delete','file_dialog','file_form'));
		
		
		$id_list = DatagridExtention::getIdList(array('tool_bar','add','edit','select','form','tab_container','more_content','hidden_flag','datagrid','delete','file_dialog','file_form'));
        $fields = D('Setting/UserData')->getDatagridField('StockIn','stockinmanagement');
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($fields,$checkbox);
		$datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => U("Stock/StockInManagement/loadDataByCondition"),
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>false,
				'ctrlSelect' => true,
                
                //'idField'=>'id',
            ),
            'fields' => $fields,
        );

        $arr_tabs = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order_detail")).'?prefix=stockinmanagement&tab=stockInManagementDetail&app=StockIn',"id"=>$id_list['tab_container'],"title"=>"入库单详情"),
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order")).'?prefix=stockinmanagement&tab=stockInLog',"id"=>$id_list['tab_container'],"title"=>"日志"),
        );
        $arr_tabs = json_encode($arr_tabs);


        $params['datagrid'] = array();
        $params['datagrid']['id'] = $id_list['datagrid'];
        $params['form']['id'] = 'stockin_search_form';
        $params['search']=array(
            'form_id'    =>		'stockin_search_form',
            'more_content' => $id_list['more_content'],
            'hidden_flag' => $id_list['hidden_flag'],
        );
        $params['tabs']['id'] = $id_list['tab_container'];
        $params['tabs']['url'] = U('StockCommon/showTabDatagridData');

        $this->assign("params", json_encode($params));
        $this->assign('center_container', $id_list['tab_container']);
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);
        $this->assign("datagrid_id", $id_list['datagrid']);


        $list = UtilDB::getCfgRightList(array('brand','logistics','employee','warehouse'));
        $logistics_default['0'] = array('id' => 'all', 'name' => '全部');
        $logistics_array = array_merge($logistics_default, $list['logistics']);
        $brand_default['0'] = array('id' => 'all', 'name' => '全部');
        $brand_array = array_merge($brand_default, $list['brand']);
        $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
		$employee_default['0'] = array('id' => 'all', 'name' => '全部');
        $employee_array = array_merge($employee_default, $list['employee']);
        $this->assign('brand_array', $brand_array);
        $this->assign('logistics_array',$logistics_array);
		$this->assign('employee_array', $employee_array);
		$this->assign('warehouse_array', $warehouse_array);
	    $this->display('show');
    }

    /**
     * 根据条件获取入库单
     * @param int $page
     * @param int $rows
     * @param array $search
     * @param string $sort
     * @param string $order
     */
    public function loadDataByCondition($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc') {
        $this->ajaxReturn(D('StockInOrder')->loadDataByCondition($page, $rows, $search, $sort, $order));
    }


    /**
     * 取消入库单
     * @param int $id
     */
    public function deleteStockInOrder($id = 0){
        $this->ajaxReturn(D('StockIn')->deleteStockInOrder($id));
    }

    /**
     * 提交入库单
     * @param $data
     */
    public function submit(){
        try{
            $result = array('status'=>0,'info'=>'成功','data'=>array());
            $id = I('post.id');
            D("Stock/StockInOrder")->submit($id,$result);
        }catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info']   = $e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-submit-'.$msg);
            $result['status'] = 1;
            $result['info']   = self::UNKNOWN_ERROR;
        }

        $this->ajaxReturn($result);
    }
	
	public function send(){
		 try{
			$sid = get_sid();
			$uid = get_operator_id();
			$result = array('status'=>0,'info'=>'成功','data'=>array());
			$id = I('post.id');
			$WmsManager = ManagerFactory::getManager("Wms");
			$WmsManager->manual_wms_adapter_add_other_in($sid, $uid, $id);
		 }catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info']   = $e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-submit-'.$msg);
            $result['status'] = 1;
            $result['info']   = self::UNKNOWN_ERROR;
        }

       // $this->ajaxReturn($result);
	}
	
public	function ackOk($num)
{
    echo json_encode(array('updated'=>$num));
}
	
	public function cancel_other_in(){
		 try{
			$sid = get_sid();
			$uid = get_operator_id();
			$result = array('status'=>0,'info'=>'成功','data'=>array());
			$id = I('post.id');
			\Think\Log::write('cancel_other_in--'.$id);
			$WmsManager = ManagerFactory::getManager("Wms");
			$WmsManager->manual_wms_adapter_cancel_other_in($sid, $uid, $id);
		 }catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info']   = $e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-submit-'.$msg);
            $result['status'] = 1;
            $result['info']   = self::UNKNOWN_ERROR;
        }

       // $this->ajaxReturn($result);
	}
    public function getEditInfo()
    {
        try{
            $result = array('status'=>0,'info'=>'成功','data'=>array());
            $id = I('post.id');
            $data = D('Stock/StockInOrder')->loadSelectedData($id);
            //整理数据
			$result['data']['form_data']=array(
                'src_order_type'    => $data[0]['src_order_type'],
                'warehouse_id'      => $data[0]['warehouse_id'],
                'src_order_no'      => $data[0]['src_order_no'],
                'stockin_no'        => $data[0]['stockin_no'],
                'remark'            => $data[0]['remark'],
                'logistics_id'      => $data[0]['logistics_id'],
                'logistics_no'      => $data[0]['logistics_no'],
                'post_fee'          => $data[0]['post_fee'],
                'discount'          => $data[0]['discount'],
                'src_price'         => $data[0]['goods_amount'],
                'total_price'       => $data[0]['total_price'],
                'other_fee'         => $data[0]['other_fee'],
            );
			$provider_name = D('Purchase/PurchaseOrder')->alias('po')->field('pp.provider_name')->join('LEFT JOIN purchase_provider pp ON pp.id=po.provider_id')->where(array('purchase_no'=>$data[0]['src_order_no']))->select();
            if(isset($provider_name[0]['provider_name']) && !empty($provider_name[0]['provider_name'])){
				$result['data']['form_data']['provider'] = $provider_name[0]['provider_name'];
			}
            $result['data']['detail_data']=array('total'=>count($data),'rows'=>$data);

        }catch(BusinessLogicException $e){
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-getEditInfo-'.$msg);
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    public function refundLinkSpOrder(){
        try {
            $ids = I('get.ids','',C('JSON_FILTER'));
            $idList = self::getIDList($idList,array('tool_bar','add','edit','select','form','tab_container','more_content','hidden_flag','datagrid','delete'),'refundordersporder');//'StockIn','stockinmanagement',U("Stock/StockInManagement/loadDataByCondition"),'',array('tool_bar','add','edit','select','form','tab_container','more_content','hidden_flag','datagrid','delete'));
            $fields = get_field('Stock/StockInManagement','refundlinksporder');
            $datagrid = array(
                "id"      => $idList["datagrid"],
                "options" => array(
                     "url"=>U("Stock/StockInManagement/searchRefundSpOrder")."?ids=".json_encode($ids),
                    "toolbar"    => $idList["tool_bar"],
                    "fitColumns" => false,
                    "rownumbers" => true,
                ),
                "fields"  =>$fields
            );
            $params = array(
                'datagrid'=>array(
                    'id'     =>    $idList["datagrid"],
                ),
                'search'=>array(
                    'form_id'    =>  $idList['form'],
                    'more_content' => $idList['more_content'],
                    'hidden_flag' => $idList['hidden_flag'],
                ),
                'form'  =>array(
                    'id'=>$idList["form"],
                ),
                'tabs' =>array(
                    'id' =>$idList['tab_container'],
                    'url'=>U('StockCommon/showTabDatagridData'),
                ),

            );
            $arr_tabs = array(
                array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order_detail")).'?prefix=dialogRefundSpOrder&tab=stockInManagementDetail&app=StockIn',"id"=>$idList['tab_container'],"title"=>"入库单详情"),
                array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order")).'?prefix=dialogRefundSpOrder&tab=stockInLog',"id"=>$idList['tab_container'],"title"=>"日志"),
            );
            $this->assign("params", json_encode($params));
            $this->assign('arr_tabs', json_encode($arr_tabs));
            $this->assign('datagrid', $datagrid);
            $this->assign("id_list", $idList);
            $this->assign("datagrid_id", $idList['datagrid']);


            $list = UtilDB::getCfgRightList(array('brand','logistics','employee','warehouse'));
            $logistics_default['0'] = array('id' => 'all', 'name' => '全部');
            $logistics_array = array_merge($logistics_default, $list['logistics']);
            $brand_default['0'] = array('id' => 'all', 'name' => '全部');
            $brand_array = array_merge($brand_default, $list['brand']);
            $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $list['warehouse']);
            $employee_default['0'] = array('id' => 'all', 'name' => '全部');
            $employee_array = array_merge($employee_default, $list['employee']);
            $this->assign('brand_array', $brand_array);
            $this->assign('logistics_array',$logistics_array);
            $this->assign('employee_array', $employee_array);
            $this->assign('warehouse_array', $warehouse_array);
        }catch (\Exception $e){
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_refund_sp_order');
    }
    public function searchRefundSpOrder($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc')
    {
        try{
            $ids = I('get.ids','',C('JSON_FILTER'));
            $where_spec = array('gs.spec_id'=>array('not in',$ids));
            $result = D('StockInOrder')->loadDataByCondition($page, $rows, $search, $sort, $order,'refund_sp_order',$where_spec);
        }catch (Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-searchRefundSpOrder-'.$e->getMessage());
            $result = array('total' => 0, 'rows' => array());
        }
        $this->ajaxReturn($result);
    }
	
	public function upload(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["msg"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
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
			\Think\Log::write('入库导入-'.$e->getMessage());
			$result['status'] = 1;
			$result['msg'] = '文件错误，无法读取';
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		try{
			$model = D('Stock/StockInOrder');
			$fail_result = array();
			$data = array();
			$merchant_no = array();
			foreach($exceldata as $sheet){
				$field_name_list = $sheet[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '仓库名称' || $field_name_list[2] != '入库数量' || $field_name_list[3] != '入库价' || $field_name_list[4] !='货位' ){
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
						'position_no'			=>$row[++$i],
						'line'  				=>$line,
						'status'				=>0,
						'message'				=>'成功',
						'result'				=>'',
					); 
					if(isset($merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']])){
						$merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']] = false;
					}else{
						$merchant_no[$tmp_data['spec_no']][$tmp_data['warehouse_name']] = true;
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
				}elseif($merchant_no[$data_v['spec_no']][$data_v['warehouse_name']] == false){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '导入仓库['.$data_v['warehouse_name'].']商家编码重复';
					$data[$data_k]['result'] = '失败';
				}elseif(empty($data_v['num'])){
					$data[$data_k]['status'] = 1;
					$data[$data_k]['message'] = '入库数量不能为空或者等于0';
					$data[$data_k]['result'] = '失败';
				}
				if(empty($data_v['price'])){
					$data[$data_k]['price'] = 0;
				}
			}	
			
		}catch(\Exception $e){
			 $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['msg'] =$msg;
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		$sql_drop = "create temporary table if not exists `tmp_import_detail` (`rec_id` int(11) not null auto_increment,`spec_no` varchar(40),`spec_id` int(11),`warehouse_name` varchar(40),`warehouse_id` int(11),`num` decimal(19,4),`price` decimal(19,4),`stock_num` decimal(19,4),`position_no` varchar(40),`position_id` int(11),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),primary key (`rec_id`))engine = InnoDB default charset=utf8;";
		$model->execute($sql_drop);
		$model->upload($data,$result);
		$model->execute('delete from `tmp_import_detail`');
		
		$this->ajaxReturn(json_encode($result),'EVAL');
	}
	
	public function downloadTemplet(){
        $file_name = "入库导入模板.xls";
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
            D('StockInOrder')->exportToExcel($id_list,$search,$type);
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