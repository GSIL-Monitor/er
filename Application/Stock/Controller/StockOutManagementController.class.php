<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/22
 * Time: 下午5:50
 */
namespace Stock\Controller;

use Common\Controller\BaseController;
//use Setting\Common\SettingFields;
use Think\Model;
//use Stock\Common\StockFields;
use Common\Common;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;
use Platform\Common\ManagerFactory;

class StockOutManagementController extends BaseController
{

    public function getStockOutSpec()
    {
        $idList = Common\DatagridExtention::getRichDatagrid('StockOut','StockOutManagement',U("Stock/StockOutManagement/loadDataByCondition"));
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($idList['datagrid']['fields'],$checkbox);
        $idList['datagrid']['options']['singleSelect'] = false;
        $idList['datagrid']['options']['ctrlSelect'] = true;
        $arr_tabs = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order_detail")).'?prefix=stockoutmanagement&tab=stockmanagementdetail&app=StockOut',"id"=>$idList['id_list']['tab_container'],"title"=>"出库单详情"),
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockout_order")).'?prefix=stockoutmanagement&tab=stockOutLog',"id"=>$idList['id_list']['tab_container'],"title"=>"日志"),
        );
        $arr_tabs = json_encode($arr_tabs);


        $params['datagrid'] = array();
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $params['form']['id'] = 'stockout_search_form';
        $params['search']['form_id'] = 'stockout_search_form';

        $params['tabs']['id'] = $idList['id_list']['tab_container'];
        $params['tabs']['url'] = U('StockCommon/showTabDatagridData');
        /**
         *修改出库单管理对象的时候或者修改编辑对话框的id的时候应该修改这里
         * 向对话框中的界面传递父界面的创建对象和当前对话框的id
         */
        $params['edit'] = array(
            'id'        => $idList['id_list']['edit'], 
            'url'       => U('Stock/StockOutOrder/getStockOutOrderList')."?editDialogId=".$idList['id_list']['edit']."&stockManagementObject=stockOutManagement", 
            'title'     => '出库单编辑',
            'height'    => '600',
            'width'     => '800'
        );
        
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U("StockOutManagement/loadDataByCondition");
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];

        $this->assign("params", json_encode($params));
        $this->assign('arr_tabs', $arr_tabs);
        $this->assign('tool_bar', $idList['id_list']['tool_bar']);
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);
        $this->assign("datagrid_id", $idList['id_list']['datagrid']);

        $list = UtilDB::getCfgRightList(array('logistics','employee','warehouse'));
        $warehouse_default[0] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
		$logistics_default['0'] = array('id' => 'all', 'name' => '全部');
        $logistics_array = array_merge($logistics_default, $list['logistics']);
        $employee_default['0'] = array('id' => 'all', 'name' => '全部');
        $employee_array = array_merge($employee_default, $list['employee']);
		$this->assign('warehouse_array', $warehouse_array);
        $this->assign('logistics_array',$logistics_array);
        $this->assign('employee_array', $employee_array);
        $this->display('show');
    }
    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'stockout_id', $order = 'desc'){
        $this->ajaxReturn(D('StockOutOrder')->searchStockoutList($page, $rows, $search, $sort, $order,'stockoutmanage'));
    }

    public function deleteStockOutOrder($id = 0)
    {
        $this->ajaxReturn(D('StockOutOrder')->deleteStockOutOrder($id));
    }

    public function submitStockOutOrder($id = 0)
    {
        $this->ajaxReturn(D('StockOutOrder')->submitStockOutOrder($id));

    }
	public function send(){
		 try{
			$sid = get_sid();
			$uid = get_operator_id();
			$result = array('status'=>0,'info'=>'成功','data'=>array());
			$id = I('post.id');
			$WmsManager = ManagerFactory::getManager("Wms");
			$WmsManager->manual_wms_adapter_add_other_out($sid, $uid, $id);
		 }catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info']   = $e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-submit-'.$msg);
            $result['status'] = 1;
            $result['info']   = self::UNKNOWN_ERROR;
        }

      //  $this->ajaxReturn($result);
	}
	public function cancel_other_out(){
		 try{
			$sid = get_sid();
			$uid = get_operator_id();
			$result = array('status'=>0,'info'=>'成功','data'=>array());
			$id = I('post.id');
			$WmsManager = ManagerFactory::getManager("Wms");
			$WmsManager->manual_wms_adapter_cancel_other_out($sid, $uid, $id);
		 }catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info']   = $e->getMessage();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(CONTROLLER_NAME.'-submit-'.$msg);
            $result['status'] = 1;
            $result['info']   = self::UNKNOWN_ERROR;
        }

      //  $this->ajaxReturn($result);
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
            D('StockOutOrder')->exportToExcel($id_list,$search,$type);
        }catch(BusinessLogicException $e){
            $result = array('status'=>1,'info'=>$e->getMessage());
            \Think\Log::write($e->getMessage());
        }catch(\Exception $e){
            $result = array('status'=>1,'info'=>$e->getMessage());
            \Think\Log::write($e->getMessage());
        }
        echo $result['info'];
    }
}